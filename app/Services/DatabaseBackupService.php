<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use PDOException;
use RuntimeException;

class DatabaseBackupService
{
    public function directory(): string
    {
        return storage_path('app/private/backups');
    }

    public function create(): string
    {
        $databasePath = $this->databasePath();

        if (! File::exists($databasePath)) {
            throw new RuntimeException('The configured SQLite database file does not exist.');
        }

        DB::connection()->statement('PRAGMA wal_checkpoint(FULL)');
        File::ensureDirectoryExists($this->directory());

        $backupName = 'laundry_'.now()->format('Ymd_His').'_'.bin2hex(random_bytes(3)).'.sqlite';

        if (! File::copy($databasePath, $this->directory().DIRECTORY_SEPARATOR.$backupName)) {
            throw new RuntimeException('Could not write the database backup file.');
        }

        return $backupName;
    }

    public function restore(string $backupName): string
    {
        if (basename($backupName) !== $backupName || ! preg_match('/^laundry_[0-9]{8}_[0-9]{6}_[a-f0-9]{6}\.sqlite$/', $backupName)) {
            throw new RuntimeException('Choose a valid backup file from the backup folder.');
        }

        $backupPath = $this->directory().DIRECTORY_SEPARATOR.$backupName;

        if (! File::isFile($backupPath) || File::size($backupPath) === 0) {
            throw new RuntimeException('The selected backup file is missing or empty.');
        }

        try {
            $backupDatabase = new PDO('sqlite:'.$backupPath, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $integrity = $backupDatabase->query('PRAGMA quick_check')->fetchColumn();
            $backupDatabase = null;
        } catch (PDOException $exception) {
            throw new RuntimeException('The selected backup is not a readable SQLite database.', previous: $exception);
        }

        if ($integrity !== 'ok') {
            throw new RuntimeException('The selected backup did not pass SQLite integrity checks.');
        }

        $databasePath = $this->databasePath();
        $safetyBackup = $this->create();

        DB::disconnect();

        try {
            if (! File::copy($backupPath, $databasePath)) {
                throw new RuntimeException('Could not replace the current database with the selected backup.');
            }
        } finally {
            DB::reconnect();
        }

        return $safetyBackup;
    }

    private function databasePath(): string
    {
        if (config('database.default') !== 'sqlite') {
            throw new RuntimeException('Database backup and restore currently support SQLite only.');
        }

        $path = config('database.connections.sqlite.database');

        if (! is_string($path) || $path === ':memory:' || $path === '') {
            throw new RuntimeException('The configured SQLite database path cannot be backed up.');
        }

        return $path;
    }
}
