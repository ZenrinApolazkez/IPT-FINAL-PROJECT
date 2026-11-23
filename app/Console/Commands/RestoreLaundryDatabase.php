<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('app:restore-laundry-database {backup : Backup filename from storage/app/private/backups}')]
#[Description('Restore a SQLite laundry database backup after explicit confirmation')]
class RestoreLaundryDatabase extends Command
{
    public function handle(DatabaseBackupService $backupService): int
    {
        if ($this->confirm('Restoring overwrites the current database. A safety backup will be created first. Continue?', false) === false) {
            $this->warn('Restore cancelled.');

            return self::SUCCESS;
        }

        if ($this->ask('Type RESTORE to confirm database replacement') !== 'RESTORE') {
            $this->warn('Restore cancelled because the confirmation did not match.');

            return self::SUCCESS;
        }

        try {
            $safetyBackup = $backupService->restore((string) $this->argument('backup'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Database restored. The previous database was saved as '.$safetyBackup.'.');

        return self::SUCCESS;
    }
}
