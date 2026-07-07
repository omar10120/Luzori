<?php

namespace App\Rules;

use App\Models\Center;
use App\Models\CenterUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class GlobalEmailUnique implements ValidationRule
{
    protected $excludeId;
    protected $excludeTable;

    public function __construct($excludeId = null, $excludeTable = null)
    {
        $this->excludeId = $excludeId;
        $this->excludeTable = $excludeTable;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Store current database connection
        $currentDatabase = Config::get('database.connections.mysql.database');
        
        try {
            // Ensure we're on the main database for centers table check
            Config::set('database.connections.mysql.database', env('DB_DATABASE'));
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Check main database centers table
            $query = Center::where('email', $value);
            if ($this->excludeId && $this->excludeTable === 'centers') {
                $query->where('id', '!=', $this->excludeId);
            }
            
            if ($query->exists()) {
                $fail('The email has already been taken in centers.');
                return;
            }

            // Check only centers that already have a prepared tenant database
            $centers = Center::query()
                ->whereNotNull('database')
                ->where('is_setup', true)
                ->get();
            
            foreach ($centers as $center) {
                // If we are updating a center, skip checking its own tenant database
                // because the center and its primary center_user share the same email.
                if ($this->excludeId && $this->excludeTable === 'centers' && $center->id == $this->excludeId) {
                    continue;
                }

                try {
                    // Switch to center database
                    Config::set('database.connections.mysql.database', $center->database);
                    DB::purge('mysql');
                    DB::reconnect('mysql');

                    // Skip tenants that do not have center_users table yet
                    if (!DB::connection('mysql')->getSchemaBuilder()->hasTable('center_users')) {
                        continue;
                    }

                    // Check center_users table in this center's database
                    $query = CenterUser::where('email', $value);
                    if ($this->excludeId && $this->excludeTable === 'center_users') {
                        $query->where('id', '!=', $this->excludeId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The email has already been taken in center: ' . $center->name);
                        return;
                    }
                } catch (\Exception $e) {
                    // Skip broken/inaccessible tenant DBs; they should not block registration
                    \Log::warning("Skipping email check for center {$center->database}: " . $e->getMessage());
                }
            }
        } finally {
            // Always reset to the original database connection
            Config::set('database.connections.mysql.database', $currentDatabase);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }
    }
}
