# Konfigurasi Database


## Daftar Isi

-   [Memulai Dengan SQLite](#memulai-dengan-sqlite)
-   [Menggunakan Database Lain](#menggunakan-database-lain)
-   [Mengubah Nama Koneksi Default](#mengubah-nama-koneksi-default)
-   [Menimpa Opsi PDO Default](#menimpa-opsi-pdo-default)

Secara default, Hexazor mendukung sistem database berikut:

-   MySQL
-   PostgreSQL
-   SQLite
-   SQL Server

Seluruh opsi konfigurasi database ditaruh di dalam file `config/database.php`. Silahkan buka file tersebut agar Anda mendapat sedikit gambaran tentang konfigurasi database di Hexazor.


## Memulai Dengan SQLite

[SQLite](http://sqlite.org) adalah salah satu sistem database yang bagus, konfigurasinya pun tidak terlalu ribet. Pada keadaan default, Hexazor dikonfigurasikan untuk menggunakan SQLite. Benar, tujuannya agar Anda bisa langsung mencoba Hexazor tanpa harus ribet setting sana - sini, cukup letakkan file database SQLite bernama `application.sqlite` kedalam folder `database/sqlite/` dan Anda sudah siap untuk memulai eksperimen menggunakan database.

Tentu saja, Anda boleh menamainya dengan nama selain "application", untuk melakukannya, cukup ubah opsi konfigurasi di file `config/database.php`:

```php
'driver' => 'sqlite',
//...
'database' => 'application',
```

> [!TIP]
> Hexazor akan secara otomatis menambahkan ekstensi `.sqlite` ke nama database Anda.

Jika aplikasi Anda menerima kurang dari 100.000 kunjungan per hari, SQLite cukup mampu untuk menanganinya. Tapi jika sebaliknya, silahkan gunakan MySQL atau PostgreSQL saja.


## Menggunakan Database Lain

Jika Anda menggunakan MySQL, SQL Server, atau PostgreSQL, Anda perlu mengubah opsi konfigurasi di file `config/database.php` tadi. Di dalam file tersebut, Anda dapat menemukan sampel konfigurasi untuk tiap - tiap sistem database. Cukup ubah sesuai kebutuhan Anda dan jangan lupa untuk mengubah nama koneksi defaultnya.


## Mengubah Nama Koneksi Default

Seperti yang telah Anda perhatikan, setiap koneksi database yang diatur dalam file `config/database.php` memiliki nama koneksi. Secara default, ada empat nama koneksi yang didefinisikan: `sqlite`, `mysql`, `sqlsrv`, dan `pgsql`. Anda bebas mengubah nama koneksi ini. Koneksi default dapat diatur melalui opsi "default":

```php
'default' => 'sqlite',
```

Koneksi default inilah yang akan selalu digunakan oleh [Query Builder](/database/query-builder.md). Jika Anda perlu mengubah koneksi default saat request berlangsung, silahkan gunakan method `Config::set()`:

```php
Config::set('database.default', 'nama_koneksi_baru');
```

## Menimpa Opsi PDO Default

Kelas konektor PDO (`system/Database/Connectors/Connector.php`) memiliki seperangkat definisi atribut PDO yang dapat ditimpa. Sebagai contoh, salah satu atribut defaultnya adalah memaksa nama kolom menjadi huruf kecil (`PDO::CASE_LOWER`) bahkan jika mereka didefinisikan dalam UPPERCASE atau camelCase di tabel. Oleh karena itu, di bawah atribut default, object model hasil kueri hanya dapat diakses menggunakan huruf kecil.

Contoh pengaturan sistem MySQL dengan menambahkan atribut PDO default:

```php
	'mysql' => [
	'driver'   => 'mysql',
	'host'     => 'localhost',
	'database' => 'database',
	'username' => 'root',
	'password' => '',
	'charset'  => 'utf8',
	'prefix'   => '',

	PDO::ATTR_CASE              => PDO::CASE_LOWER,
	PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
	PDO::ATTR_STRINGIFY_FETCHES => false,
	PDO::ATTR_EMULATE_PREPARES  => false,
],
```

Info lebih lanjut tentang atribut koneksi PDO dapat ditemukan di [Dokumentasi Resmi PHP PDO](http://php.net/manual/en/pdo.setattribute.php).
