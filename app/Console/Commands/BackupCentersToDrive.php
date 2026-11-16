<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveBackupService;
use Illuminate\Console\Command;

class BackupCentersToDrive extends Command
{
    protected $signature = 'centers:drive-backup';

    protected $description = 'Dump every center database and keep the last three daily copies on Google Drive';

    public function handle(GoogleDriveBackupService $drive): int
    {
        if (!$drive->connected()) {
            $this->error('Google Drive is not connected.');

            return self::FAILURE;
        }

        foreach ($drive->backupCenters() as $line) {
            $this->line($line);
        }

        return self::SUCCESS;
    }
}
