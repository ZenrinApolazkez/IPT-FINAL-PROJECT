<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index(DatabaseBackupService $backupService): View
    {
        $directory = $backupService->directory();
        $backups = File::isDirectory($directory)
            ? collect(File::files($directory))
                ->filter(fn (\SplFileInfo $file): bool => Str::startsWith($file->getFilename(), 'laundry_') && $file->getExtension() === 'sqlite')
                ->sortByDesc(fn (\SplFileInfo $file): int => $file->getMTime())
                ->map(fn (\SplFileInfo $file): array => [
                    'file' => $file,
                    'size' => $this->formatFileSize($file->getSize()),
                ])
                ->values()
            : collect();

        return view('admin.backups', ['backups' => $backups]);
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = (float) $bytes;

        foreach ($units as $unit) {
            if ($size < 1024 || $unit === 'TB') {
                return number_format($size, $unit === 'B' ? 0 : 1).' '.$unit;
            }

            $size /= 1024;
        }

        return number_format($size, 1).' TB';
    }

    public function store(DatabaseBackupService $backupService): RedirectResponse
    {
        $backupName = $backupService->create();

        return back()->with('status', 'Database backup created: '.$backupName);
    }

    public function download(string $backup): BinaryFileResponse
    {
        return response()->download($this->pathForBackup($backup));
    }

    public function destroy(string $backup): RedirectResponse
    {
        $path = $this->pathForBackup($backup);

        if (! File::delete($path)) {
            throw new RuntimeException('Could not delete the selected database backup.');
        }

        return back()->with('status', 'Database backup deleted: '.$backup);
    }

    private function pathForBackup(string $backup): string
    {
        abort_unless(
            basename($backup) === $backup && preg_match('/^laundry_[0-9]{8}_[0-9]{6}_[a-f0-9]{6}\.sqlite$/', $backup),
            404,
        );

        $path = storage_path('app/private/backups/'.$backup);
        abort_unless(File::isFile($path), 404);

        return $path;
    }
}
