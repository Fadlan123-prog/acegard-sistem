<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Backup database MySQL ke file .sql.gz, verifikasi isinya, lalu hapus backup lama.
 *
 * Password dikirim lewat env MYSQL_PWD (bukan argumen) agar tidak muncul di daftar proses.
 * Salinan kedua ke disk lain (mis. S3/FTP) opsional lewat BACKUP_COPY_DISK.
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep= : Jumlah hari backup disimpan (default BACKUP_KEEP_DAYS atau 14)}';

    protected $description = 'Backup database ke storage/app/backups (gzip), verifikasi, dan hapus yang lama';

    public function handle(): int
    {
        $conn = config('database.connections.' . config('database.default'));

        if (($conn['driver'] ?? null) !== 'mysql') {
            $this->error('backup:database hanya mendukung koneksi mysql.');
            return self::FAILURE;
        }

        $disk = Storage::disk('local');
        $dir  = 'backups';
        $disk->makeDirectory($dir);

        $file = sprintf('%s/%s-%s.sql.gz', $dir, $conn['database'], now()->format('Ymd-His'));
        $path = $disk->path($file);
        $sql  = substr($path, 0, -3); // .sql (sementara)

        // Dua langkah (dump lalu gzip) agar tidak bergantung pada `pipefail` shell.
        $dump = new Process([
            config('backup.mysqldump_path'),
            '--single-transaction', '--quick', '--routines', '--no-tablespaces',
            '--host=' . $conn['host'], '--port=' . $conn['port'], '--user=' . $conn['username'],
            '--result-file=' . $sql,
            $conn['database'],
        ], null, ['MYSQL_PWD' => (string) $conn['password']], null, 600);
        $dump->run();

        if (! $dump->isSuccessful() || ! is_file($sql)) {
            @unlink($sql);
            $this->error('Backup gagal: ' . trim($dump->getErrorOutput()));
            return self::FAILURE;
        }

        $gzip = new Process(['gzip', '-9', '-f', $sql]);
        $gzip->run();

        if (! $gzip->isSuccessful() || ! is_file($path) || filesize($path) < 200) {
            @unlink($sql);
            @unlink($path);
            $this->error('Kompres backup gagal: ' . trim($gzip->getErrorOutput()));
            return self::FAILURE;
        }

        // Verifikasi: file gzip terbaca dan memuat struktur tabel.
        $verify = new Process(['gzip', '-t', $path]);
        $verify->run();
        $head = $verify->isSuccessful() ? (string) @shell_exec('gzip -dc ' . escapeshellarg($path) . ' | head -c 2000000 | grep -c "CREATE TABLE"') : '0';
        if ((int) trim($head) < 1) {
            $this->error('Backup tidak lolos verifikasi (tidak ada CREATE TABLE): ' . $file);
            return self::FAILURE;
        }

        @chmod($path, 0600);
        $this->info('Backup OK: ' . $file . ' (' . number_format(filesize($path) / 1024, 1) . ' KB)');

        if ($copyDisk = config('backup.copy_disk')) {
            Storage::disk($copyDisk)->put($file, fopen($path, 'r'));
            $this->info("Salinan dikirim ke disk '{$copyDisk}'.");
        }

        $keepDays = (int) ($this->option('keep') ?: config('backup.keep_days'));
        $cutoff   = now()->subDays($keepDays)->getTimestamp();
        foreach ($disk->files($dir) as $old) {
            if (str_ends_with($old, '.sql.gz') && $disk->lastModified($old) < $cutoff) {
                $disk->delete($old);
                $this->line('Hapus backup lama: ' . $old);
            }
        }

        return self::SUCCESS;
    }
}
