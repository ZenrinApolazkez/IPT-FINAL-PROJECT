<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backup-laundry-database')]
#[Description('Create a backup of the SQLite laundry database')]
class BackupLaundryDatabase extends Command
{
    public function handle(DatabaseBackupService $backupService): int
    {
        $backupName = $backupService->create();

        $this->info('Database backup created: storage/app/private/backups/'.$backupName);

        return self::SUCCESS;
    }
}
