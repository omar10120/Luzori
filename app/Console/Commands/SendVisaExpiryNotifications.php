<?php

namespace App\Console\Commands;

use App\Models\Worker;
use App\Services\NotifiesCenter;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendVisaExpiryNotifications extends Command
{
    use NotifiesCenter;

    protected $signature = 'workers:visa-expiry';
    protected $description = 'Send notifications for workers whose visa expires in 30 days';

    public function handle()
    {
        $this->info('Checking for visa expirations...');

        // Calculate the target date (30 days from today)
        $targetDate = Carbon::today()->addDays(30)->toDateString();

        try {
            // Find active workers whose visa expires on the target date.
            // Because your .env is pointed to the center DB, this queries the correct table.
            $workers = Worker::query()
                ->whereNull('deleted_at')
                ->whereDate('visa_end_date', $targetDate)
                ->get();
        } catch (\Exception $e) {
            $this->error('Database error: ' . $e->getMessage());
            $this->warn('Make sure your .env DB_DATABASE is set to a Center database, not the central "luzori" database.');
            return;
        }

        if ($workers->isEmpty()) {
            $this->info('No visas expiring in 30 days.');
            return;
        }

        foreach ($workers as $worker) {
            // Fire the notification. The NotifiesCenter trait automatically 
            // resolves the center based on the current active database connection.
            $this->notifyCenter(
                'worker_visa_expiry',
                [
                    'en' => [
                        'title' => 'Visa Expiry Alert',
                        'text'  => "Worker {$worker->name}'s visa is expiring in 30 days on {$worker->visa_end_date}.",
                    ],
                    'ar' => [
                        'title' => 'تنبيه انتهاء صلاحية التأشيرة',
                        'text'  => "تأشيرة الموظف {$worker->name} ستنتهي خلال 30 يوماً في {$worker->visa_end_date}.",
                    ],
                ],
                [
                    'action'      => 'worker_visa_expiry',
                    'worker_id'   => (string) $worker->id,
                    'expiry_date' => (string) $worker->visa_end_date,
                ]
            );
        }

        $this->info("Sent notifications for {$workers->count()} worker(s).");
    }
}