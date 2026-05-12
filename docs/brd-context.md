# BRD Context - Sistem Absensi QR & Smart Class

Sumber acuan utama:
`C:\Users\HYPE-R\Downloads\BRD Sistem Absensi QR & Smart Class SMA.pdf`

Dokumen ini merangkum konteks BRD agar menjadi pedoman implementasi project.

## Tujuan Bisnis

Membangun sistem sekolah yang menggabungkan:

- Absensi digital berbasis QR Code.
- Approval akun manual oleh superadmin.
- Distribusi materi dan tugas per kelas.

## Modul Utama

1. Manajemen pengguna dan approval akun.
2. Absensi QR Code.
3. Classroom/LMS lite.

## Role dan Hak Akses

### Superadmin

- Approval akun user.
- Manajemen user.
- Manajemen kelas.
- Scan absensi.
- Monitoring kehadiran real-time.

### Guru

- Scan absensi.
- Monitoring kehadiran real-time.
- Upload materi.
- Membuat tugas.

### Siswa

- Melihat QR Code pribadi.
- Download materi.
- Upload tugas.

## Kebutuhan Fungsional Inti

- `FR-01` User yang belum di-approve tidak boleh mengakses sistem setelah login.
- `FR-02` QR Code siswa harus tergenerate otomatis setelah akun siswa aktif.
- `FR-03` Dashboard guru harus menampilkan daftar siswa yang sudah scan dan belum absen secara real-time.
- `FR-04` Sistem harus mendukung reset password via email.

## Kebutuhan Non-Fungsional

- Data sensitif harus dienkripsi.
- Route harus dilindungi middleware.
- UI harus responsif, terutama untuk alur scan absensi.
- Sistem harus siap menangani trafik tinggi saat jam masuk sekolah.

## User Journey

### Pendaftaran

Siswa/Guru daftar -> status pending -> superadmin review -> approve/reject.

### Absensi

Siswa login -> buka QR pribadi -> guru/admin scan -> status kehadiran tercatat otomatis.

### Belajar

Siswa masuk kelas -> unduh materi -> unggah tugas sebelum deadline.

## Implikasi Implementasi

Project ini minimal membutuhkan domain data berikut:

- users
- roles
- approval status
- kelas
- keanggotaan kelas
- qr identity siswa
- sesi/riwayat absensi
- materi kelas
- tugas
- submission tugas

## Aturan Produk yang Perlu Dijaga

- Registrasi siswa dan guru harus dipisah.
- Dashboard harus berbeda berdasarkan role.
- Akses halaman harus dikontrol berdasarkan role dan status approval.
- QR siswa adalah identitas resmi untuk absensi.
- Scan dilakukan oleh guru/admin, bukan self check-in siswa.
- Fitur classroom hanya bisa diakses sesuai kelas masing-masing.

## Catatan Untuk Pengembangan Berikutnya

- Saat membangun schema database, role dan approval status adalah fondasi pertama.
- Middleware `auth` saja belum cukup; perlu middleware role dan approval.
- Flow registrasi default harus diubah agar tidak langsung aktif penuh setelah daftar.
- Dashboard default perlu dipecah menjadi dashboard superadmin, guru, dan siswa.
