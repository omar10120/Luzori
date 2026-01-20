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

            // Get all centers to check their databases
            $centers = Center::all();
            
            foreach ($centers as $center) {
                try {
                    // Switch to center database
                    Config::set('database.connections.mysql.database', $center->database);
                    DB::purge('mysql');
                    DB::reconnect('mysql');

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
                    // Log error but continue checking other centers
                    \Log::error("Error checking email in center {$center->database}: " . $e->getMessage());
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
