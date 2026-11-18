# Deploy ke Domainesia (cPanel) dengan FileZilla

Panduan untuk deploy manual tanpa CI. Cocok untuk masa uji coba (trial). Perintah yang menurut kamu butuh SSH
(composer, artisan) ditangani dengan dua cara: **dibangun di komputer lokal** (composer) dan **cron sekali jalan di cPanel** (artisan).

> Jangan pernah mengunggah file `.env` lokal, folder `.git`, atau `tests`. Paket rilis dari skrip di bawah sudah mengecualikannya.

## A. Persiapan satu kali

1. **cPanel → MultiPHP Manager**: pilih PHP **8.2 atau 8.3** untuk domain.
   **MultiPHP INI Editor**: `memory_limit` ≥ 256M, `upload_max_filesize` ≥ 16M, `max_execution_time` ≥ 120.
   **Select PHP Version → Extensions**: aktifkan `gd`, `zip`, `mbstring`, `intl`, `pdo_mysql`, `fileinfo`, `bcmath`, `exif`, `opcache`.
2. **cPanel → MySQL® Databases**: buat database, buat user, tambahkan user ke database dengan *ALL PRIVILEGES*.
   Catat nama database, user, dan password (nama biasanya berawalan nama akun, mis. `akun_acegard`).
3. **Document Root**: aplikasi diletakkan di `/home/<akun>/acegard-app` (BUKAN di `public_html`), dan domain diarahkan ke folder `public`:
   - **cPanel → Domains → Manage** → *Document Root* = `acegard-app/public`.
   - Jika Document Root tidak bisa diubah: biarkan `public_html`, lalu isi `public_html` dengan isi folder `public` aplikasi dan ubah dua jalur di `public_html/index.php`
     menjadi `/home/<akun>/acegard-app/vendor/autoload.php` dan `/home/<akun>/acegard-app/bootstrap/app.php`.
4. **SSL**: cPanel → *SSL/TLS Status* → *Run AutoSSL* sampai domain berstatus aman, lalu aktifkan *Force HTTPS Redirect*.

## B. Membuat paket rilis (di komputer lokal)

```bash
./scripts/build-release.sh
```
Hasil: `build/acegard-app-YYYYMMDD-HHMM.zip` (sekitar 35 MB) — berisi kode + `vendor` production + aset panel, tanpa `.env`, upload, dan log.

Untuk **deploy pertama** (atau bila gambar situs publik berubah) tambahkan `--assets`:
```bash
./scripts/build-release.sh --assets
```
yang juga membuat `build/acegard-assets-….zip` (sekitar 165 MB; gambar situs publik/promosi). Aset ini besar dan jarang berubah,
jadi pada rilis berikutnya cukup mengunggah zip kode saja.
Menggunakan satu file zip jauh lebih cepat dan lebih aman daripada mengunggah ribuan file satu per satu lewat FileZilla.

## C. Mengunggah dengan FileZilla

1. Host `ftp.domainmu.com` (atau IP server), username & password FTP dari cPanel → *FTP Accounts*, port 21 (atau SFTP 22 bila tersedia).
2. Unggah **satu file zip** ke `/home/<akun>/` (bukan `public_html`).
3. **cPanel → File Manager** → klik kanan zip → *Extract* ke `/home/<akun>/`. Hasilnya folder `acegard-app`.
3b. Hanya deploy pertama: unggah `acegard-assets-….zip` ke `/home/<akun>/acegard-app/public/` lalu *Extract* di sana (folder `assets/promotion` dan `assets/frontend`).
4. Buka folder `acegard-app`, salin `.env.production.example` menjadi `.env`, lalu *Edit* dan isi:
   ```
   APP_NAME=Acegard
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domainmu.com
   APP_TIMEZONE=Asia/Jakarta
   APP_KEY=            # isi lewat langkah D.1
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=<nama database>
   DB_USERNAME=<user database>
   DB_PASSWORD=<password>
   SESSION_DRIVER=database
   SESSION_SECURE_COOKIE=true
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   MAIL_...            # SMTP: pakai email domain dari cPanel (Email Accounts)
   ```
5. Atur izin (File Manager → klik kanan → *Change Permissions*): folder `storage` dan `bootstrap/cache` = **775** (termasuk isinya).

## D. Menjalankan perintah artisan tanpa SSH (cron sekali jalan)

cPanel → *Cron Jobs* → tambah cron dengan waktu "dalam 2 menit", pantau hasilnya di file `acegard-app/storage/logs/deploy.log`,
lalu **hapus cron-nya** setelah selesai. Ganti `<akun>` dan path PHP (lihat *Select PHP Version* — biasanya `/usr/local/bin/php`
atau `/opt/cpanel/ea-php83/root/usr/bin/php`):

1. Kunci aplikasi (hanya sekali, bila `APP_KEY` kosong): lalu salin hasilnya ke `.env` bagian `APP_KEY=`
   ```
   cd /home/<akun>/acegard-app && /usr/local/bin/php artisan key:generate --show >> storage/logs/deploy.log 2>&1
   ```
2. Tautan penyimpanan, migrasi, dan cache:
   ```
   cd /home/<akun>/acegard-app && /usr/local/bin/php artisan storage:link >> storage/logs/deploy.log 2>&1 ; /usr/local/bin/php artisan migrate --force >> storage/logs/deploy.log 2>&1 ; /usr/local/bin/php artisan config:cache >> storage/logs/deploy.log 2>&1 ; /usr/local/bin/php artisan route:cache >> storage/logs/deploy.log 2>&1 ; /usr/local/bin/php artisan view:cache >> storage/logs/deploy.log 2>&1
   ```
   Jika `storage:link` gagal (symlink dibatasi), buat symlink lewat File Manager: *+ Symlink* dari `/home/<akun>/acegard-app/storage/app/public` ke `public/storage`.

**Data awal:** pilih salah satu.
- *Database kosong*: migrasi di atas membuat tabel; buat akun superadmin lewat seeder yang disepakati (jangan memakai akun demo).
- *Bawa data dari lokal*: di phpMyAdmin lokal ekspor database (Export → SQL), lalu di cPanel → phpMyAdmin pilih database → Import.
  Jika memakai impor, jalankan migrasi tetap aman (hanya menambah yang belum ada). Bersihkan akun demo sebelum dipakai.

## E. Cron permanen (aplikasi berjalan)

cPanel → Cron Jobs (setiap menit, `* * * * *`):
```
cd /home/<akun>/acegard-app && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
cd /home/<akun>/acegard-app && /usr/local/bin/php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```
`schedule:run` menjalankan backup database harian (02:00) dan notifikasi terjadwal. Aktifkan juga **cPanel → JetBackup / Backup** dan
unduh salinan database ke komputer secara berkala (backup di server yang sama tidak melindungi bila server rusak).

## F. Uji setelah deploy (smoke test)
1. `https://domainmu.com/login` terbuka tanpa peringatan SSL.
2. Login tiap peran (superadmin, admin, finance, marketing, teknisi).
3. Buat quotation → SO → customer → invoice → DP → pelunasan → rilis komisi.
4. Teknisi: terima dan selesaikan order dengan foto; foto muncul di halaman QC.
5. Cek halaman garansi publik, dan buka `storage/logs` bila ada error.
6. Pastikan `https://domainmu.com/.env` dan `https://domainmu.com/storage/logs/laravel.log` **tidak** bisa dibuka (harus 403/404).

## G. Memperbarui versi (setiap rilis berikutnya)
1. Lokal: `./scripts/build-release.sh`, unggah zip baru via FileZilla.
2. cPanel File Manager: **ubah nama** `acegard-app` → `acegard-app-lama`, ekstrak zip baru (jadi `acegard-app` baru).
3. Salin dari folder lama: `.env`, dan isi `storage/app/public` serta `storage/app/private` (foto dan backup).
4. Jalankan cron sekali jalan langkah D.2 (migrasi + cache).
5. Uji cepat (F). Jika bermasalah: kembalikan nama folder lama (rollback) dan pulihkan database dari backup bila migrasi sudah berjalan.

## H. Yang perlu diperhatikan
- File `.env` berisi rahasia: izin 640/600, jangan dimasukkan ke zip rilis.
- Foto absensi dan foto pekerjaan tersimpan di `storage/app`; ikut dibackup (unduh berkala).
- Folder lama `public/storage/checkin-photos` (jika ada dari versi sebelumnya) harus dipindah/dihapus.
- Cek ID kategori produk sebelum tarif komisi dipakai (lihat `docs/CEK-KOMISI-PRODUCTION.md`, query A10).
