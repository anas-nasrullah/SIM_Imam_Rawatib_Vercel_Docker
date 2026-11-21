**UNTUK VERCEL: BACA `README-VERCEL.md` TERLEBIH DAHULU.**

# SIM IMAM RAWATIB – versi lengkap mengikuti dua screenshot

Proyek PHP Native + MySQL (PDO) yang bisa diinstal ke Laragon, tanpa Composer, Node.js, atau layanan eksternal. Antarmuka terdiri atas **pengurus desktop** dan **imam mobile**. Desain berdasarkan dua screenshot, bukan pembacaan seluruh frame Figma: detail lain di luar dua screenshot mungkin belum tercakup.

## Instalasi Laragon (Windows)

1. Ekstrak folder `SIM_Imam_Rawatib_Lengkap` ke `C:\laragon\www\` sehingga ada file `C:\laragon\www\SIM_Imam_Rawatib_Lengkap\index.php`.
2. Start Apache dan MySQL di Laragon.
3. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database/sim_imam_rawatib_lengkap.sql` → **Go**. Import membuat database `sim_imam_rawatib_lengkap` dan 150 jadwal **contoh** November 2026.
4. Jika user/password MySQL berbeda, ubah `DB_HOST`, `DB_USER`, `DB_PASS` pada `config.php`. Default Laragon sering memakai `root` tanpa password.
5. Buka **http://localhost/SIM_Imam_Rawatib_Lengkap/**. Pertama kali akan diarahkan ke `setup.php` untuk membuat akun **admin pertama** menggunakan email dan password sendiri (minimal 10 karakter).
6. Login sebagai admin → menu **Pengguna masjid** → buat akun untuk imam, pilih peran Imam, lalu tautkan dengan data imam yang sesuai. Login akun imam akan langsung membuka tampilan mobile, baik di HP maupun browser komputer.

Jika mengganti nama folder, sesuaikan `BASE_PATH` dalam `config.php`. Minimal PHP 8.1 dengan `pdo_mysql` dan `mbstring`, MySQL 8 / MariaDB 10.4+. Tidak diperlukan internet saat menjalankan aplikasi.

## Halaman dan fitur

**Pengurus:**
- `dashboard.php` – Ringkasan pengurus, jadwal hari terpilih, status kesediaan dan absensi, rekap bisyaroh, aturan nominal.
- `kalender.php` – Kalender bulan penuh 5 sholat per hari, jumlah penugasan per imam, filter bulan, menuju edit jadwal.
- `jadwal.php` – Buat jadwal otomatis bulanan, tambah/edit/hapus penugasan, ubah imam, jam, tandai tugas ekstra.
- `imam.php` – Tambah/edit/nonaktifkan data imam.
- `kehadiran.php` – Pengurus mengisi status hadir/izin/tidak hadir.
- `rekap.php` – Ringkasan dasar/tambahan/potongan/pembayaran, penyesuaian manual, cetak dan CSV.
- `pengaturan.php` – Nama masjid, bisyaroh dasar, tarif tugas tambahan, potongan tidak bisa, jam default.
- `pengguna.php` – Admin buat akun peran admin/pengurus/imam, tautkan imam, reset password, aktif/nonaktif akun.
- `profil.php` – Nama dan password sendiri.

**Imam (mobile):**
- `imam_home.php` – Salam, bisyaroh berjalan, pertanyaan konfirmasi jadwal, daftar sholat hari ini.
- `imam_konfirmasi.php` – Jawab "Ya, saya bisa" atau "Tidak bisa", catatan, penjelasan dampak bisyaroh.
- `imam_jadwal.php` – Jadwal masjid per tanggal, kalender pilih tanggal, navigasi hari, konfirmasi tugas sendiri.
- `imam_bisyaroh.php` – Bisyaroh berjalan, detail tambahan/potongan, sudah dibayar, sisa, riwayat.
- `imam_profil.php` – Profil akun, ubah password, logout.

## Aturan bisnis

- Satu waktu sholat untuk satu tanggal memiliki satu imam di masjid ini; jadwal November 2026 memiliki 30×5 = **150 slot**.
- Kesediaan (`pending` / `bisa` / `tidak_bisa`) adalah jawaban imam; **tidak** otomatis menjadi kehadiran. Kehadiran (`belum` / `hadir` / `izin` / `tidak_hadir`) dicatat pengurus.
- **Bisyaroh berjalan** = honor dasar bulanan + (jumlah tugas ekstra yang disanggupi × nominal tambahan) + tambahan manual − (jumlah tugas yang ditolak × potongan per tugas) − potongan manual. Minimal hasil Rp0. Setelah pembayaran, sisa = maksimum(0, total berjalan − pembayaran tercatat). Perubahan pengaturan berlaku pada kalkulasi bulan lain maupun bulan berjalan (belum ada snapshot per bulan).
- Aturan default dari screenshot: honor dasar **Rp1.000.000/ imam/ bulan**, tarif tambahan dan potongan awal **Rp0** sampai pengurus menyepakati nilainya. Peraturan final masjid perlu dipastikan.
- Jika pengurus **mengganti imam yang ditugaskan**, kesediaan dan absensi pada slot tersebut direset agar tidak terbawa ke imam pengganti. Jika imam sebelumnya menyatakan **tidak bisa**, penolakan terdahulu tersimpan dalam `schedule_refusals`, agar potongannya tetap terhitung. Penugasan yang sudah memiliki absensi perlu dikoreksi dulu sebelum dialihkan. Untuk audit tindakan tersedia `audit_logs`.
- Jam salat dalam data contoh (`04:06`, `11:41`, `15:02`, `17:53`, `19:06` WIB) hanya mengikuti tampilan contoh. Tidak ada sinkronisasi waktu adzan resmi.
- Laporan CSV diberi perlindungan formula spreadsheet. Semua formulir mutasi memakai CSRF, login memakai password_hash(), sesi regenerasi dan role check, query PDO prepared statement.

## Batasan dan langkah sebelum produksi

- Belum dilakukan pengujian langsung dengan MySQL/Laragon milik pengguna dan belum diaudit untuk pemakaian produksi.
- Screenshot adalah rujukan visual; struktur halaman lain di luar dua screenshot belum diketahui. Desain diperlihatkan kembali sebagai CSS responsif dengan dominasi hijau-putih; bukan konversi pixel-perfect file Figma.
- **Jangan** langsung digunakan untuk pembayaran resmi sebelum pengurus menyetujui kebijakan tarif, potongan, tutup buku, dan memeriksa semua angka.
- Untuk produksi: siapkan HTTPS, backup database, proteksi akses dan backup, kebijakan pengelolaan kredensial, tes alur/permissions, dan audit transaksi lebih lanjut.
- Data imam dan jadwal November 2026 yang diimpor merupakan **contoh/dummy** dari screenshot, bukan pengumuman jadwal resmi masjid.
- Untuk mulai benar-benar kosong: hapus dua blok INSERT contoh `imams` dan `schedules` di bagian akhir SQL sebelum impor; pengaturan tetap dapat diimpor.
