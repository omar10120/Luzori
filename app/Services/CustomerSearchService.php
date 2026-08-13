<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class CustomerSearchService
{
    public function listForSelect(?int $selectedId = null, int $limit = 50): Collection
    {
        $users = User::query()
            ->with('media')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($selectedId && !$users->contains('id', $selectedId)) {
            $selected = User::with('media')->find($selectedId);
            if ($selected) {
                $users->prepend($selected);
            }
        }

        return $users;
    }

    public function search(string $term, int $page = 1, int $perPage = 20): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 1) {
            return [
                'results' => [],
                'pagination' => ['more' => false],
            ];
        }

        $like = '%' . $term . '%';
        $digits = preg_replace('/\D+/', '', $term);

        $query = User::query()
            ->with('media')
            ->where(function ($q) use ($like, $digits) {
                $q->where('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    ->orWhereRaw("CONCAT(IFNULL(first_name, ''), ' ', IFNULL(last_name, '')) LIKE ?", [$like])
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhere('phone', 'LIKE', $like)
                    ->orWhereRaw("CONCAT(IFNULL(country_code, ''), IFNULL(phone, '')) LIKE ?", [$like]);

                if ($digits !== '') {
                    $q->orWhere('phone', 'LIKE', '%' . $digits . '%')
                        ->orWhereRaw(
                            "CONCAT(IFNULL(REPLACE(country_code, '+', ''), ''), IFNULL(phone, '')) LIKE ?",
                            ['%' . $digits . '%']
                        );
                }
            })
            ->orderBy('first_name');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'results' => $paginator->getCollection()->map(fn (User $user) => $this->map($user))->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ];
    }

    public function find(int $id): array
    {
        return $this->map(User::with('media')->findOrFail($id));
    }

    public function map(User $user): array
    {
        $phone = $user->phone ?? '';
        $fullPhone = $user->full_phone ?? $phone;
        $image = $user->getFirstMediaUrl(class_basename($user)) ?: asset('assets/img/avatars/1.png');

        return [
            'id' => $user->id,
            'text' => trim($user->name . ' - ' . ($fullPhone ?: ($user->email ?? ''))),
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $phone,
            'full_phone' => $fullPhone,
            'country_code' => $user->country_code,
            'image' => $image,
            'branch_id' => $user->branch_id,
        ];
    }
}
