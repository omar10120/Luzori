<?php

namespace App\Services;

use App\Models\CenterUser;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\NotifiesCenter;   
use Illuminate\Support\Facades\Log;         


use App\Models\Center;
class CenterUserService
{
    use NotifiesCenter; 
    public function all()
    {
        return CenterUser::withTrashed()->get();
    }

    public function find($id)
    {
        return CenterUser::withTrashed()->find($id);
    }

    public function add($request)
    {
        DB::beginTransaction();
        $centerUser = CenterUser::create($request);
        if (isset($request['image'])) {
            $centerUser->addMedia($request['image'])->toMediaCollection('CenterUser');
        }
        if (isset($request['primary_image'])) {
            $images = is_array($request['primary_image']) ? $request['primary_image'] : [$request['primary_image']];
            foreach ($images as $img) {
                $centerUser->addMedia($img)->toMediaCollection('PrimaryImage');
            }
        }
        if (isset($request['role'])) {
            // Get the role by ID to assign it properly
            $role = \Spatie\Permission\Models\Role::findById($request['role'], 'center_api');
            $centerUser->assignRole($role);
        }
        DB::commit();
        return $centerUser;
    }

    public function edit($request)
    {
        DB::beginTransaction();
        try {
            $centerUser = CenterUser::withTrashed()->find($request['id']);

            $oldSnapshot = [
                'name'              => (string) ($centerUser->name ?? ''),
                'email'             => (string) ($centerUser->email ?? ''),
                'country_code'      => (string) ($centerUser->country_code ?? ''),
                'phone'             => (string) ($centerUser->phone ?? ''),
                'currency'          => (string) ($centerUser->currency ?? ''),
                'bank_name'         => (string) ($centerUser->bank_name ?? ''),
                'role_id'           => (int) ($centerUser->roles()->first()?->id ?? 0),
                'status_web'        => (int) ($centerUser->statusWeb ?? 1),
                'password_provided' => !empty($request['password'] ?? null),
            ];

            if (isset($request['image'])) {
                $centerUser->clearMediaCollection('CenterUser');
                $centerUser->addMedia($request['image'])->toMediaCollection('CenterUser');
            }

            if (!empty($request['delete_primary_images'])) {
                $mediaIds = array_filter(explode(',', $request['delete_primary_images']));
                foreach ($mediaIds as $mediaId) {
                    $media = $centerUser->media()->where('id', $mediaId)->first();
                    if ($media) {
                        $media->delete();
                    }
                }
            }

            if (isset($request['primary_image'])) {
                $images = is_array($request['primary_image']) ? $request['primary_image'] : [$request['primary_image']];
                foreach ($images as $img) {
                    $centerUser->addMedia($img)->toMediaCollection('PrimaryImage');
                }
            }

            if (!isset($request['password'])) {
                unset($request['password']);
            }

            $oldEmail = $centerUser->email;
            $centerUser->update($request);
            $this->syncMainCenterProfile($centerUser, $oldEmail, $request);

            if (isset($request['role'])) {
                $centerUser->roles()->detach();
                $role = \Spatie\Permission\Models\Role::findById($request['role'], 'center_api');
                $centerUser->assignRole($role);
            }

            DB::commit();

            // === Notify AFTER commit (best-effort, never blocks the response) ===
            $this->notifyCenterUserEdited($centerUser->fresh(), $oldSnapshot);

            return $centerUser;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CenterUser edit failed', [
                'user_id' => $request['id'] ?? null,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Mirror profile fields onto the central centers row when this user's
     * email belongs to the active center (main database).
     */
    private function syncMainCenterProfile(CenterUser $centerUser, ?string $oldEmail, array $request): void
    {
        if (!$oldEmail) {
            return;
        }

        $database = Config::get('database.connections.mysql.database');
        $center = Center::query()
            ->where('database', $database)
            ->where('email', $oldEmail)
            ->first();

        if (!$center) {
            return;
        }

        $payload = [];
        foreach (['name', 'email', 'country_code', 'phone', 'currency', 'bank_name'] as $field) {
            if (array_key_exists($field, $request)) {
                $payload[$field] = $request[$field];
            }
        }

        if (!empty($request['password'])) {
            $payload['password'] = $request['password'];
        }

        if ($payload === []) {
            return;
        }

        $center->update($payload);
    }

    public function changeLanguage($language_id, $id)
    {
        $centerUser = CenterUser::find($id);
        $centerUser->update([
            'language_id' => $language_id,
        ]);
        return $centerUser;
    }

    public function changePassword($request, $id)
    {
        $centerUser = CenterUser::find($id);

        if (!Hash::check($request['current_password'], $centerUser->password)) {
            return NULL;
        }

        $centerUser->update([
            'password' => $request['password'],
        ]);
        return $centerUser;
    }

    public function delete($id)
    {
        $centerUser = CenterUser::withTrashed()->find($id);
        $centerUser->tokens()->delete();
        $centerUser->fcmTokens()->delete();
        $centerUser->delete();
        return $centerUser;
    }

    public function changeStatusWeb($id)
    {
        $centerUser = CenterUser::find($id);
        if ($centerUser->statusWeb == 1) {
            $centerUser->update([
                'statusWeb' => 0
            ]);
        } else {
            $centerUser->update([
                'statusWeb' => 1
            ]);
        }
        return $centerUser;
    }

    private function notifyCenterUserEdited(CenterUser $centerUser, array $old): string
    {
        try {
            // ---- 1. Self-edit gate (configurable) ----
            $actorId  = auth('center_user')->id();
            $isSelf   = $actorId !== null && (int) $actorId === (int) $centerUser->id;
            $skipSelf = (bool) config('notifications.app', false);

            Log::debug('notifyCenterUserEdited: start', [
                'center_user_id' => $centerUser->id,
                'actor_id'       => $actorId,
                'is_self'        => $isSelf,
                'skip_self'      => $skipSelf,
                'db'             => config('database.connections.mysql.database'),
            ]);

            if ($isSelf && $skipSelf) {
                Log::info('notifyCenterUserEdited: skipped (self-edit)', [
                    'center_user_id' => $centerUser->id,
                ]);
                return 'skipped_self';
            }

            // ---- 2. Diff check ----
            $infoFields = ['name', 'email', 'country_code', 'phone', 'currency', 'bank_name'];
            $changedInfo = [];
            foreach ($infoFields as $field) {
                if ((string) ($old[$field] ?? '') !== (string) ($centerUser->{$field} ?? '')) {
                    $changedInfo[] = $field;
                }
            }

            $oldRoleId   = (int) ($old['role_id'] ?? 0);
            $newRoleId   = (int) ($centerUser->roles()->first()?->id ?? 0);
            $roleChanged = $oldRoleId !== $newRoleId;

            $passwordChanged = !empty($old['password_provided']);

            $oldStatusWeb   = (int) ($old['status_web'] ?? 1);
            $newStatusWeb   = (int) ($centerUser->statusWeb ?? 1);
            $statusChanged  = $oldStatusWeb !== $newStatusWeb;

            Log::debug('notifyCenterUserEdited: diff', [
                'center_user_id'   => $centerUser->id,
                'changed_info'     => $changedInfo,
                'role_changed'     => $roleChanged,
                'password_changed' => $passwordChanged,
                'status_changed'   => $statusChanged,
            ]);

            if (empty($changedInfo) && !$roleChanged && !$passwordChanged && !$statusChanged) {
                Log::info('notifyCenterUserEdited: skipped (nothing changed)', [
                    'center_user_id' => $centerUser->id,
                ]);
                return 'skipped_unchanged';
            }

            // ---- 3. Build message ----
            $displayName = trim((string) ($centerUser->name ?? '')) ?: 'the user';

            $enParts = [];
            $arParts = [];
            $actions = [];

            if ($statusChanged) {
                $enParts[] = 'Account status changed to ' . ($newStatusWeb === 1 ? 'enabled' : 'disabled') . '.';
                $arParts[] = 'تم تغيير حالة الحساب إلى ' . ($newStatusWeb === 1 ? 'مفعل' : 'معطل') . '.';
                $actions[] = 'status_changed';
            }
            if ($roleChanged) {
                $enParts[] = 'Role and permissions were updated.';
                $arParts[] = 'تم تحديث الدور والصلاحيات.';
                $actions[] = 'role_changed';
            }
            if ($passwordChanged) {
                $enParts[] = 'Password was reset.';
                $arParts[] = 'تم إعادة تعيين كلمة المرور.';
                $actions[] = 'password_changed';
            }
            if (!empty($changedInfo)) {
                $labels = [
                    'name'         => ['en' => 'name',      'ar' => 'الاسم'],
                    'email'        => ['en' => 'email',     'ar' => 'البريد الإلكتروني'],
                    'country_code' => ['en' => 'country',   'ar' => 'الدولة'],
                    'phone'        => ['en' => 'phone',     'ar' => 'الهاتف'],
                    'currency'     => ['en' => 'currency',  'ar' => 'العملة'],
                    'bank_name'    => ['en' => 'bank name', 'ar' => 'اسم البنك'],
                ];
                $enFields = [];
                $arFields = [];
                foreach ($changedInfo as $f) {
                    $enFields[] = $labels[$f]['en'];
                    $arFields[] = $labels[$f]['ar'];
                }
                $enParts[] = 'Profile fields updated: ' . implode(', ', $enFields) . '.';
                $arParts[] = 'تم تحديث حقول الملف الشخصي: ' . implode('، ', $arFields) . '.';
                $actions[] = 'info_changed';
            }

            // ---- 4. Resolve the center robustly ----
            // Primary: what the trait uses (works when mysql is on the tenant DB).
            // Fallback: find it explicitly by the current tenant DB name — this
            // bypasses any config drift in `database.connections.mysql.database`.
            $center = Center::where('database', config('database.connections.mysql.database'))->first();
            if (!$center) {
                Log::warning('notifyCenterUserEdited: primary center lookup failed', [
                    'db' => config('database.connections.mysql.database'),
                ]);
                return 'skipped_no_center';
            }

            Log::info('notifyCenterUserEdited: dispatching', [
                'center_user_id' => $centerUser->id,
                'center_id'      => $center->id,
                'actions'        => $actions,
                'changed_fields' => $changedInfo,
            ]);

            // ---- 5. Dispatch via the service, bypassing the trait's silent catch ----
            // We call AppNotificationService directly here so that any failure
            // surfaces in the logs immediately instead of being swallowed by
            // the trait's internal try/catch. This makes debugging deterministic.
            $notification = app(\App\Services\AppNotificationService::class)->notifyCenterEvent(
                $center,
                'system',
                [
                    'en' => [
                        'title' => 'Center User Updated',
                        'text'  => 'User "' . $displayName . '" — ' . implode(' ', $enParts),
                    ],
                    'ar' => [
                        'title' => 'تم تحديث مستخدم المركز',
                        'text'  => 'المستخدم "' . $displayName . '" — ' . implode(' ', $arParts),
                    ],
                ],
                [
                    'action'         => 'center_user_edited',
                    'user_id'        => (string) $centerUser->id,
                    'changed_fields' => implode(',', $changedInfo),
                    'changes'        => implode(',', $actions),
                ]
            );

            if ($notification === null) {
                Log::warning('notifyCenterUserEdited: notification returned null', [
                    'center_id'      => $center->id,
                    'center_user_id' => $centerUser->id,
                ]);
                return 'failed';
            }

            Log::info('notifyCenterUserEdited: sent', [
                'center_id'       => $center->id,
                'notification_id' => $notification->id,
                'center_user_id'  => $centerUser->id,
            ]);

            return 'sent';
        } catch (\Throwable $e) {
            Log::error('notifyCenterUserEdited: exception', [
                'center_user_id' => $centerUser->id ?? null,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);
            return 'failed';
        }
    }
}
