# Catatan Rilis

## Daftar Isi

-   [Rilis Versi 1.0.1](#rilis-versi-101)
-   [Cara Upgrade Dari 1.0.0 Ke 1.0.1](#cara-upgrade-dari-100-ke-101)
-   [Rilis Versi 1.0.0](#rilis-versi-100)

### Cara Upgrade Dari 1.0.0 Ke 1.0.1

Ganti file dan folder berikut dengan yang baru:

-   Folder `/system`
-   File `app/Configs/mimes.php`
-   File `app/Configs/upload.php`

### Rilis Versi 1.0.1

Catatan perubahan di versi ini:

-   Ubah: Global middleware dieksekusi sebelum middleware lokal
-   Tambah: Auth library sekarang mendukung roles dan permission
-   Tambah: Tambahan file mime types
-   Perbaikan: Unable assign value by reference di PHP 7.4.0 [#123](https://github.com/issues/123)

### Rilis Versi 1.0.0

Rilis pertama.
