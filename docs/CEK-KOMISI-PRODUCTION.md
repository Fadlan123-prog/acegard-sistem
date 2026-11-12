# Cek data komisi di production (read-only) — sebelum deploy perbaikan komisi

Tujuan: mengetahui apakah data production sudah mengandung kasus yang diperbaiki di branch
`fix/commission-integrity` (komisi nonaktif dibayar, komisi yatim PPF, batch menggantung, dll.),
SEBELUM kode baru dideploy. Semua query di bagian A hanya `SELECT` — aman dijalankan.

## Langkah

1. **Backup dulu.** Hostinger hPanel → Databases → phpMyAdmin → pilih database → Export → Quick/SQL.
   (Atau `php artisan backup:database` bila server sudah mengaktifkannya.) Simpan file di luar server.
2. **Buka phpMyAdmin → tab SQL.** Tempel SATU query dalam satu waktu (bagian A), klik Go.
3. **Catat hasilnya** (Export → CSV, atau screenshot) untuk setiap query. Angka 0 berarti aman.
4. **Kirim hasil ke developer.** Jangan jalankan bagian B sebelum hasilnya dibaca bersama.
5. **Deploy kode** (lihat docs/DEPLOY-HOSTINGER.md), lalu `php artisan migrate --force`.
6. **Setelah deploy**, ulangi query A1–A6: hasilnya tidak boleh bertambah (perbaikan hanya mencegah kasus baru;
   data lama tidak berubah otomatis).

## A. Query pemeriksaan (SELECT saja)

```sql
-- A1. Komisi nonaktif per status. 'eligible'/'paid' berarti nonaktif tapi bisa/sudah dibayar.
SELECT status, COUNT(*) jumlah, SUM(amount) total FROM commissions WHERE is_active = 0 GROUP BY status;

-- A2. Komisi yatim PPF (invoice PPF sudah dihapus tapi komisi masih ada).
SELECT c.id, c.employees_id, c.role, c.amount, c.status
FROM commissions c LEFT JOIN ppf_invoices p ON p.id = c.ppf_invoice_id
WHERE c.ppf_invoice_id IS NOT NULL AND p.id IS NULL;

-- A3. Komisi eligible/paid pada invoice mobil yang belum lunas.
SELECT c.id, c.amount, c.status, i.invoice_number
FROM commissions c JOIN invoices i ON i.id = c.invoice_id
WHERE c.status IN ('eligible','paid') AND i.pelunasan_at IS NULL AND i.status <> 'paid';

-- A4. Komisi eligible/paid pada invoice PPF yang belum lunas.
SELECT c.id, c.amount, c.status, p.invoice_number, p.status AS status_invoice
FROM commissions c JOIN ppf_invoices p ON p.id = c.ppf_invoice_id
WHERE c.status IN ('eligible','paid') AND p.status <> 'paid';

-- A5. Pengajuan pencairan (draft) yang semua komisinya sudah dibayar lewat jalur lain (menggantung).
SELECT b.id, b.batch_number, b.total_amount, b.created_at FROM commission_payment_batches b
WHERE b.status = 'draft'
  AND NOT EXISTS (SELECT 1 FROM commissions c WHERE c.commission_payment_batch_id = b.id AND c.status = 'eligible');

-- A6. Total batch tidak sama dengan jumlah komisi di dalamnya.
SELECT b.id, b.batch_number, b.status, b.total_amount, SUM(c.amount) AS jumlah_komisi
FROM commission_payment_batches b JOIN commissions c ON c.commission_payment_batch_id = b.id
GROUP BY b.id, b.batch_number, b.status, b.total_amount
HAVING b.total_amount <> SUM(c.amount);

-- A7. Komisi admin PPF yang diterima karyawan dari cabang lain.
SELECT c.id, c.amount, e.name, e.branch_id AS cabang_karyawan, p.branch_id AS cabang_invoice
FROM commissions c JOIN employees e ON e.id = c.employees_id JOIN ppf_invoices p ON p.id = c.ppf_invoice_id
WHERE c.role = 'admin' AND e.branch_id <> p.branch_id;

-- A8. Invoice mobil berdiskon yang komisinya sudah dirilis (dasar lama = harga sebelum diskon).
SELECT COUNT(*) jumlah_invoice, SUM(discount) total_diskon
FROM invoices WHERE discount > 0 AND commission_released_at IS NOT NULL;

-- A9. Customer D/S/P + panoramic beserta bagian terpasangnya (aturan baru: 2x D/S/P hanya bila depan + panoramic saja).
SELECT cu.id, cu.card_number, cu.has_panoramic, GROUP_CONCAT(pt.name ORDER BY pt.name SEPARATOR ' | ') AS bagian
FROM customers cu LEFT JOIN customer_product cp ON cp.customer_id = cu.id LEFT JOIN parts pt ON pt.id = cp.part_id
WHERE cu.install_type = 'dsp' AND cu.has_panoramic = 1
GROUP BY cu.id, cu.card_number, cu.has_panoramic;

-- A10. ID kategori yang dipakai tarif komisi (harus: mobil 1=4K, 2=Notch, 3=Black Master; gedung 1..4 sesuai config).
SELECT 'mobil' AS jenis, id, name FROM category_products
UNION ALL SELECT 'gedung', id, name FROM category_product_building ORDER BY jenis, id;
```

## Cara membaca hasil

| Query | Hasil | Artinya / tindakan |
|---|---|---|
| A1 | ada baris `eligible` | komisi nonaktif masih bisa dibayar. Setelah deploy tidak akan terbayar lagi; periksa apakah memang harus nonaktif |
| A1 | ada baris `paid` | sudah terbayar padahal nonaktif — tinjau manual dengan finance |
| A2 | > 0 | komisi yatim; putuskan hapus / batalkan (bagian B) setelah dicek |
| A3, A4 | > 0 | komisi cair sebelum lunas; tinjau dengan finance |
| A5 | > 0 | pengajuan menggantung; tolak lewat menu Pencairan Komisi (tombol Tolak) |
| A6 | > 0 | total batch tidak cocok; tinjau sebelum disetujui |
| A7 | > 0 | admin PPF lintas cabang menerima komisi; aturan baru: hanya cabang invoice |
| A8 | angka | komisi lama berbasis harga sebelum diskon; tidak diubah otomatis, aturan baru berlaku ke rilis berikutnya |
| A9 | bagian tidak persis WINDSHIELD + SUNROOF/PANORAMIC | komisi lama mungkin 2x padahal aturan baru 1x |
| A10 | ID tidak sesuai config/commission.php | JANGAN deploy tarif sebelum config disesuaikan |

## B. Pembersihan (JANGAN dijalankan sebelum hasil A dibahas)

Template di bawah hanya contoh; setiap pembersihan harus berdasarkan daftar ID dari hasil A dan persetujuan finance.
Selalu setelah backup, di dalam transaksi, dan cek jumlah baris sebelum COMMIT.

```sql
START TRANSACTION;
-- contoh: batalkan komisi yatim PPF yang BELUM dibayar (ganti daftar ID dari hasil A2)
UPDATE commissions SET status = 'cancelled', is_active = 0 WHERE id IN (/* id dari A2 */) AND status <> 'paid';
-- cek: SELECT ROW_COUNT();  lalu COMMIT; atau ROLLBACK; bila jumlah tidak sesuai.
```
