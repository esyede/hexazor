# Panduan Kontribusi


## Daftar Isi

-   [Melaporkan Kutu](#melaporkan-kutu)
-   [Celah Keamanan](#celah-keamanan)
-   [Aturan Penulisan Kode](#aturan-penulisan-kode)
-   [Style CI](#style-ci)


### Melaporkan Kutu

Untuk mendorong kolaborasi aktif, Hexazor sangat menganjurkan pull request. Namun, jika Anda mengajukan laporan bug, masalah Anda harus berisi judul dan deskripsi masalah yang jelas. Anda juga harus memberikan informasi yang relevan sebanyak mungkin dan contoh kode yang menunjukkan masalah.

Pastikan juga bahwa bug yang Anda kirimkan belum dikirimkan oleh orang lain sebelumnya. Gunakan fitur pencarian di Github untuk memeriksanya.

Tujuan dari laporan bug adalah untuk memudahkan Anda sendiri - dan orang lain - untuk mereplikasi bug dan merumuskan perbaikan. Ingat, laporan bug dibuat dengan harapan bahwa orang lain dengan masalah yang sama akan dapat berkolaborasi dengan Anda untuk menyelesaikannya. Jangan berharap bahwa laporan bug akan secara otomatis diselesaikan atau orang lain akan ikut untuk memperbaikinya. Membuat laporan bug berfungsi untuk membantu diri Anda dan orang lain mulai di jalur memperbaiki masalah. Kode sumber Hexazor dikelola di Github, maka silahkan kunjungi tatutan berikut untuk melihat-lihat:

-   [Halaman Proyek](https://github.com/esyede/hexazor)
-   [Laporan Kutu](https://github.com/esyede/hexazor/issues)


### Celah Keamanan

Jika Anda menemukan celah keamanan pada Hexazor, silakan kirim email ke Suyadi di suyadi.1992@gmail.com. Semua celah keamanan akan sesegera mungkin kami tangani.


### Aturan Penulisan Kode

Hexazor mengikuti standar penuliasan kode yang [Direkomendasikan oleh StyleCI](https://docs.styleci.io/presets#recommended) dan standar autoloading [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md). Silahkan ikuti standarisasi tersebut untuk pengiriman pull request agar kode Anda tetap rapih dan mudah dimengerti pengembang lain.


### Dokumentasi Kode

Di bawah ini adalah contoh dari blok dokumentasi Hexazor yang valid. Perhatikan bahwa atribut `@param` selalu lurus dengan `@return` yang setelahnya mengandung dua spasi, begitu juga dengan tipe argumen dan nama variabel:

```php
/**
 * Mulai operasi schema pada tabel
 *
 * @param string   $table
 * @param \Closure $callback
 *
 * @return void
 */
public static function table($table, Closure $callback)
{
    //
}
```


# Style CI

Jangan khawatir jika penulisan kode Anda tidak sempurna! StyleCI akan secara otomatis menggabungkan perbaikan gaya penulisan apapun ke dalam repositori Hexazor setelah pull request diterima. Ini memungkinkan kami untuk fokus pada konten kontribusi dan bukan gaya kode.
