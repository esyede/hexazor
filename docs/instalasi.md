# Instalasi Dan Konfigurasi

## Daftar Isi

- [Kebutuhan Sistem](#kebutuhan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi Server](#konfigurasi-server)

### Kebutuhan Sistem

Berikut adalah kebutuhan dasar yang diperlukan untuk dapat menjalankan Hexazor:

- PHP versi 5.4.0 atau yang lebih baru
- Ekstensi OpenSSL
- Ekstensi PDO
- Ekstensi Mbstring
- Apache Webserver

Anda boleh menggunakan websever lain seperti _Litespeed_, _Nginx_, _Lighttpd_, _Hiawatha_ atau yang lainnya, namun kami tidak menyertakan file konfigurasinya, hal ini karena keterbatasan pengetahuan kami tentang webserver lain selain Apache.

Tentu saja, semua kebutuhan diatas sudah dipenuhi oleh webserver stack standar yang sering Anda gunakan, sebut saja _XAMPP_, _Uniform Server_, ataupun _USB Webserver_. Silahkan pilih salah satu, Anda dapat mengunduhnya secara gratis melalui tautan berikut:

- [XAMPP](https://www.apachefriends.org/) - Mulai versi [1.8.2](http://sourceforge.net/projects/xampp/files/XAMPP%20Windows/1.8.2/) sampai versi yang [terbaru](https://www.apachefriends.org/download.html)
- [Uniform Server](https://www.uniformserver.com/) mulai versi [ZeroXI](https://sourceforge.net/projects/miniserver/files/Uniform%20Server%20ZeroXI/) (PHP 5.4+) sampai versi yang [terbaru](https://sourceforge.net/projects/miniserver/files/)
- USB Webserver
  - Versi [8.6](http://www.usbwebserver.net/downloads/USBWebserver%20v8.6.zip) - PHP 5.4
  - Versi [8.6.1](https://usbwebserver.yura.mk.ua/usbwebserver_v8.6.1.zip) - PHP 5.6
  - Versi [8.6.2](https://usbwebserver.yura.mk.ua/usbwebserver_v8.6.2.zip) - PHP 7.1 (versi terakhir)
- [Laragon](https://laragon.org/)
- Dan lain - lain.

Bahkan, Anda juga dapat menginstall PHP dan semua ekstensi yang dibutuhkan diatas melalui terminal, lalu gunakan perintah berikut untuk mengakses Hexazor melalui webserver bawaan PHP:

```bash
php hexazor serve
```

Intinya, pilihan ada ditangan Anda. Gunakan webserver stack apapun yang cocok dengan Anda.

### Instalasi

Langkah - langkah instalasi Hexazor sangatlah mudah:

1. [Unduh](https://github.com/esyede/single-blade/releases/latest) Hexazor versi yang terbaru
2. Ekstrak berkas unduhan tersebut ke webserver Anda
3. Ubah nama file `sample.htaccess` menjadi `.htaccess` (jika Anda belum punya file .htaccess)
4. **Selesai!**. Silahkan buka lewat web browser.

### Konfigurasi Server

Secara default, dalam setiap paket instalasi Hexazor sudah disertakan 2 buah file konfigurasi server, yaitu file _.htaccess_ yang berisi konfigurasi URL rewrite untuk Apache webserver, dan satu file lain bernama _sample.htaccess_ yang merupakan salinan dari file .htaccess tadi.

Jika Anda tidak memiliki file tersebut, silahkan buat sebuah file di root webserver Anda dengan nama _.htaccess_ lalu salin kode berikut kedalamnya:

```apacheconf
<IfModule mod_rewrite.c>
  Options +FollowSymLinks
  Options -MultiViews
  RewriteEngine On

  # cegah akses ke file - file framework
  RewriteRule ^(app|config|database|resources|routes|storage|system|vendor) - [F]

  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

Jika kode diatas tidak berjalan, silahkan gunakan kode ini:

```apacheconf
Options +FollowSymLinks
Options -MultiViews
RewriteEngine on

# cegah akses ke file - file framework
RewriteRule ^(app|config|database|resources|routes|storage|system|vendor) - [F]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule . index.php [L]
```

> [!NOTE]
> Setiap webserver memiliki metode berbeda dalam menangani HTTP rewrite, dan mungkin akan membutuhkan kode .htaccess yang berbeda pula.
