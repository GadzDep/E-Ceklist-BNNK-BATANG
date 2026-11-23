# E-Ceklist BNNK Batang

Aplikasi monitoring kebersihan ruangan berbasis PHP Native 8+, MySQLi, Bootstrap 5, dan JavaScript.

## Instalasi Laragon/XAMPP (instalasi baru)
1. Ekstrak folder `e-ceklist` ke `C:\laragon\www\` atau `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL.
3. Di phpMyAdmin, impor **satu file saja: `database/db_ceklist.sql`**. File ini sudah mencakup struktur tabel terbaru, seluruh ruangan dan indikator Excel, jadwal pemeriksaan, nomor polisi kendaraan, nama user, serta kolom foto dan verifikasi.
4. Sesuaikan kredensial database di `config/koneksi.php` (default Laragon/XAMPP: host `localhost`, user `root`, password kosong, database `db_ceklist`).
5. Buka `http://localhost/e-ceklist/`.

> **Peringatan:** `db_ceklist.sql` adalah skrip instalasi awal yang menghapus tabel lama sebelum membuat ulang. Jangan impor ke database project yang sudah berisi data ceklist, karena data lama akan terhapus.

## Akun demo
Semua password: `123456`
| Username | Peran |
|---|---|
| petugas | Petugas Kebersihan |
| pj_sarpras | PJ Sarpras |
| kasubbag | Kasubbag Umum |
| admin | Administrator |

## Catatan
- Upload bukti disimpan di `uploads/bukti/`; pastikan folder dapat ditulis oleh web server.
- QR code ditampilkan dengan QRCode.js CDN dan mengarah ke URL formulir ruangan.
- Untuk produksi, ganti password demo, aktifkan HTTPS, dan batasi akses direktori upload.

## Pembaruan tampilan dan pengisian ceklist
- Tampilan menggunakan tema lavender glass dengan font Poppins.
- Setiap indikator memiliki unggah foto sendiri. Pada perangkat mobile yang mendukung, kolom foto akan menawarkan kamera belakang (`capture="environment"`).
- Saat membuka jam yang sudah pernah diisi, sistem menampilkan konfirmasi dengan pilihan **Edit Ceklist** atau **Oke**. Data sebelumnya (status, foto, nama petugas, dan catatan) dimuat kembali; foto lama tetap tersimpan jika tidak diganti.
- Setelah penyimpanan berhasil, muncul notifikasi keberhasilan.
- Kolom paraf pada formulir cetak kini berupa ruang paraf, bukan nama petugas.
