# Deploy & operasi — Hostinger shared

## 1. Struktur direktori (wajib)
Aplikasi di LUAR web root, hanya `public/` yang dilayani web:
```
/home/<user>/acegard-app/        <- seluruh Laravel (app/, config/, vendor/, ...)
/home/<user>/public_html/        <- isi dari acegard-app/public (atau symlink / document root diarahkan ke acegard-app/public)
```
Secret GitHub: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_SERVER_DIR` (=public_html), `FTP_APP_DIR` (=/home/<user>/acegard-app).
Workflow akan GAGAL bila `FTP_APP_DIR` kosong (mencegah source terunggah ke web root).

Jika `public_html` bukan salinan `public/`, ubah `index.php` agar `require __DIR__.'/../acegard-app/vendor/autoload.php'` dan `bootstrap/app.php` mengarah ke folder aplikasi.

## 2. Sebelum merge ke `main` (merge = deploy otomatis)
1. Backup database: `php artisan backup:database`.
2. Jalankan migration SETELAH file terunggah: `php artisan migrate --force` (CI TIDAK menjalankan migration).
3. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
4. `php artisan storage:link` (sekali, buat ulang symlink untuk path server).

## 3. Cron (hPanel -> Advanced -> Cron Jobs)
```
* * * * * cd /home/<user>/acegard-app && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/<user>/acegard-app && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```
`schedule:run` menjalankan `backup:database` (02:00), `notify:scheduled` (07:00), reset saldo kesehatan (1 Jan).

## 4. Backup & restore
- Lokasi backup: `storage/app/private/backups/*.sql.gz` (di luar web root), disimpan `BACKUP_KEEP_DAYS` hari.
- WAJIB salinan di luar server: unduh berkala atau set `BACKUP_COPY_DISK` ke disk eksternal. Backup di server yang sama tidak melindungi dari server rusak.
- Backup file upload: unduh `storage/app/public` minimal mingguan.
- Target: RPO 24 jam, RTO 4 jam.
- **Uji restore (bulanan)** pada database kosong:
  `gunzip -c backup.sql.gz | mysql -u <user> -p <db_uji>` lalu cek jumlah baris tabel utama (customers, invoices, commissions).
- Rollback rilis: simpan salinan rilis sebelumnya; pulihkan DB dari backup sebelum migrate bila migration merusak data.

## 5. Pasca-deploy (smoke test)
Login tiap peran; buat customer -> invoice -> DP -> pelunasan; rilis komisi; cek halaman garansi publik; kirim email reset password; cek `storage/logs`.
