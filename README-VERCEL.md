# SIM Imam Rawatib — Paket Vercel Container + MySQL cloud

**Status:** Disiapkan berdasarkan source ZIP versi Laragon. Sudah diperiksa sintaks PHP, **belum** diuji secara langsung di akun Vercel atau database cloud. Deployment ini menggunakan **FrankenPHP + Docker**, sesuai pendekatan resmi: https://vercel.com/kb/guide/deploy-php-on-vercel-with-docker . Tidak cukup hanya mengunggah file PHP ke Vercel.

## 1. Siapkan MySQL cloud yang dapat diakses Vercel

Database di komputer Laragon tidak otomatis tersedia untuk Vercel. Buat **MySQL 8 / MariaDB kompatibel** di penyedia database eksternal yang menawarkan koneksi aman dari Internet (contoh: managed MySQL pilihan Anda). Buat database, misalnya `sim_imam_rawatib_lengkap` atau gunakan nama yang diberikan penyedia. Impor **database/import_remote_mysql.sql** ke *database yang dipilih*. SQL ini sengaja tidak menggunakan CREATE DATABASE/USE untuk kompatibilitas penyedia yang membatasi izin. SQL mencakup tabel `app_sessions` dan data demo 150 jadwal November 2026.

> Jangan membuka port MySQL 3306 milik server rumah atau Laragon ke Internet hanya untuk memakai Vercel. Utamakan MySQL terkelola dengan TLS, pembatasan jaringan, dan backup. Jika penyedia membutuhkan sertifikat CA, paket membutuhkan penyesuaian file CA dan pengaturan `DB_SSL_CA_PATH`.

## 2. Deploy dari repository GitHub ke Vercel

1. Ekstrak paket ini. Upload **isi folder** `SIM_Imam_Rawatib_Vercel_Docker` ke *root* repository GitHub; jangan upload hanya ZIP atau meletakkan seluruh proyek di subfolder jika Vercel Root Directory masih root.
2. Pastikan `Dockerfile`, `Caddyfile`, `config.php`, dan `index.php` semua berada di root yang sama.
3. Di Vercel buka project yang mengarah ke `simimamrawatib.vercel.app`, Settings → Build and Deployment → Framework Preset **Container** (atau biarkan pendeteksian otomatis dari `Dockerfile` jika tersedia); pastikan Root Directory benar.
4. Settings → Environment Variables → buat variabel berikut untuk **Production**:

| Variable | Isi contoh (jangan menyalin kredensial contoh) |
|---|---|
| `DB_HOST` | hostname publik MySQL dari penyedia |
| `DB_PORT` | 3306 atau port khusus penyedia |
| `DB_NAME` | nama database Anda |
| `DB_USER` | user database terpisah dengan izin yang diperlukan |
| `DB_PASS` | password kuat database |
| `ADMIN_SETUP_KEY` | frasa rahasia panjang dan acak untuk pendaftaran admin pertama |

5. Redeploy project. Kunjungi `https://simimamrawatib.vercel.app/`. Jika database sudah siap, halaman akan mengarah ke `/setup.php`. Masukkan `ADMIN_SETUP_KEY` lalu buat nama, email, dan password admin pertama. Setelah akun ada, form setup akan tertutup. Tidak ada kredensial admin bawaan.
6. Buat akun imam lewat menu Pengguna masjid. Setelah dipakai, hapus `ADMIN_SETUP_KEY` dari variabel environment jika tidak lagi diperlukan.

## 3. Troubleshooting

- **Cek runtime PHP saja:** buka `https://simimamrawatib.vercel.app/health.php`; harus muncul `{"status":"php_ready"}`. Ini tidak menguji MySQL.
- **404 NOT_FOUND:** pada Vercel Deployments → deployment terbaru → Output cek ada function/container route. Pastikan Framework Preset Container dan root repository benar.
- **502 startup:** periksa Runtime Logs, pastikan Dockerfile dan Caddyfile ikut terdeploy.
- **503 Database:** nilai environment MySQL salah, cloud DB belum diimpor, koneksi jaringan tidak diizinkan, atau TLS perlu diatur.
- **MySQL 'table app_sessions doesn't exist':** impor `database/import_remote_mysql.sql` ke database yang sama persis seperti `DB_NAME`.
- **Setup 503 ADMIN_SETUP_KEY:** tambahkan Environment Variable dan redeploy.
- **Akun langsung logout:** sesi harus memakai `SESSION_STORE=database` dan tabel `app_sessions` ada; login melalui HTTPS.
- **CSS 404:** cek `assets/style.css` ikut masuk image dan `BASE_PATH` kosong seperti paket ini.

## 4. Catatan keamanan & keterbatasan

- Aplikasi ini belum diuji dengan Vercel cloud nyata; kelayakan paket harus dibuktikan dengan pengujian login, jadwal, rekap, transaksi, dan akses antarperan.
- Sebagian hosting managed MySQL membutuhkan konfigurasi CA atau TLS khusus. Jangan menonaktifkan verifikasi sertifikat hanya demi meloloskan koneksi.
- `lib/`, `partials/`, `database/`, dan `config.php` diblokir lewat Caddyfile. Pastikan kebijakan tersebut tetap diterapkan.
- Sesi telah dialihkan ke tabel MySQL karena storage fungsi Vercel tidak persisten.
- PHP Docker dan remote MySQL bisa dikenai biaya sesuai penyedia dan penggunaan.
- Nilai bisyaroh dan jadwal contoh bukan jadwal resmi; tinjau sebelum digunakan.
- Untuk deployment publik pertama, gunakan akun dan kredensial baru, dan tinjau keamanan sebelum mengoperasikan data nyata.

Referensi resmi: https://vercel.com/kb/guide/deploy-php-on-vercel-with-docker
