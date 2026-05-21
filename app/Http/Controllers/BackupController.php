<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('users.view');

        $exitCode = Artisan::call('backup:run');

        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return back()->with('error', 'Backup failed. '.$output);
        }

        return back()->with('success', 'Backup completed successfully.');
    }

    public function restore(Request $request)
    {
        $this->authorize('users.view');

        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'mimes:zip', 'max:512000'],
        ]);

        $uploadedPath = $validated['backup_file']->getRealPath();
        $extractPath = storage_path('app/backup-restore/'.Str::uuid());

        File::ensureDirectoryExists($extractPath);

        try {
            $this->extractBackup($uploadedPath, $extractPath);
            $this->restoreDatabase($extractPath);
            $this->restoreStorageFiles($extractPath);
        } finally {
            File::deleteDirectory($extractPath);
        }

        return back()->with('success', 'Backup restored successfully.');
    }

    protected function extractBackup(string $uploadedPath, string $extractPath): void
    {
        $zip = new ZipArchive;

        if ($zip->open($uploadedPath) !== true) {
            throw new RuntimeException('The uploaded backup archive could not be opened.');
        }

        $zip->extractTo($extractPath);
        $zip->close();
    }

    protected function restoreDatabase(string $extractPath): void
    {
        $dump = collect(File::allFiles($extractPath))
            ->first(fn ($file) => Str::startsWith(str_replace('\\', '/', $file->getRelativePathname()), 'db-dumps/')
                && $file->getExtension() === 'sql');

        if (! $dump) {
            throw new RuntimeException('The backup archive does not contain a SQL database dump.');
        }

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Authenticated restore currently supports the MySQL database connection.');
        }

        $mysqlBinary = rtrim(env('MYSQL_RESTORE_BINARY_PATH', 'C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin'), '/\\').DIRECTORY_SEPARATOR.'mysql.exe';

        $arguments = [
            $mysqlBinary,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            $config['database'],
        ];

        if (! empty($config['password'])) {
            $arguments[] = '--password='.$config['password'];
        }

        $handle = fopen($dump->getPathname(), 'rb');
        $process = new Process($arguments);
        $process->setInput($handle);
        $process->setTimeout(null);
        $process->run();

        if (is_resource($handle)) {
            fclose($handle);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Database restore failed: '.$process->getErrorOutput());
        }
    }

    protected function restoreStorageFiles(string $extractPath): void
    {
        foreach (File::allFiles($extractPath) as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $storageMarker = '/storage/app/';

            if (Str::contains($relativePath, $storageMarker)) {
                $relativePath = Str::after($relativePath, $storageMarker);
            }

            if (! Str::startsWith($relativePath, ['public/', 'private/'])) {
                continue;
            }

            if (Str::contains($relativePath, ['..', ':'])) {
                continue;
            }

            $targetPath = storage_path('app/'.$relativePath);
            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($file->getPathname(), $targetPath);
        }
    }

    public static function latestBackup(): ?array
    {
        $disk = Storage::disk('local');
        $backupName = config('backup.backup.name');

        if (! $disk->exists($backupName)) {
            return null;
        }

        $file = collect($disk->allFiles($backupName))
            ->filter(fn ($path) => Str::endsWith($path, '.zip'))
            ->sortByDesc(fn ($path) => $disk->lastModified($path))
            ->first();

        if (! $file) {
            return null;
        }

        return [
            'path' => $file,
            'date' => date('M d, Y g:i A', $disk->lastModified($file)),
            'size' => number_format($disk->size($file) / 1024 / 1024, 1).' MB',
            'name' => basename($file),
        ];
    }
}
