<a id="readme"></a>

<p align="center">
  <img src="docs/_assets/images/logo.svg" alt="Hexazor"/>
</p>

<h1 align="center"> Hexazor</h1>
<p align="center">
	<img src="https://img.shields.io/github/v/release/esyede/hexazor?include_prereleases" alt="Release"/>
	<img src="https://github.styleci.io/repos/230306506/shield" alt="StyleCI"/>
	<img src="https://img.shields.io/github/languages/top/esyede/hexazor" alt="Lang"/>
	<img src="http://img.shields.io/:license-mit-blue.svg?style=flat-square" alt="MIT"/>
	<img src="https://img.shields.io/github/languages/code-size/esyede/hexazor" alt="Code"/>
	<img src="https://img.shields.io/github/issues-raw/esyede/hexazor" alt="Issue"/>
	<img src="https://img.shields.io/github/issues-closed/esyede/hexazor" alt="Closed"/>
	<img src="https://img.shields.io/github/issues-pr/esyede/hexazor" alt="Closed"/>
</p>
<p align="center">
	<a href="https://esyede.github.io/hexazor">https://esyede.github.io/hexazor</a>
</p>

<br>
<br>


## Apa itu Hexazor?

Hexazor adalah framework PHP yang kuat yang menekankan fleksibilitas dan ekspresif. Pengguna yang baru menggunakan Hexazor akan menikmati kemudahan pengembangan yang sama dengan yang ditemukan dalam kerangka kerja PHP yang paling populer saat ini.

Pengguna yang lebih berpengalaman akan mendapatkan kesempatan untuk memodulasi kode mereka dengan cara yang elegan. Fleksibilitas Hexazor akan memungkinkan organisasi Anda memperbarui dan membentuk aplikasi dari waktu ke waktu sesuai kebutuhan dan ekspresifitasnya akan memungkinkan Anda dan tim Anda untuk mengembangkan kode yang ringkas dan mudah dibaca.


## Ikhtisar Fitur

- Autoloading mengikuti standar PSR-4.
- Bisa berjalan dengan ataupun tanpa Composer.
- Routing sederhana menggunakan Closure atau Controller.
- Views dan templating.
- Abstraksi database dengan ORM dan query builder.
- Schema builder untuk pembangunan skema database.
- Migrasi database.
- Otentikasi.
- Aplikasi konsol untuk otomasi tugas
- Tersedia pustaka yang cukup banyak.
- Ukuran yang framework mungil (< 400Kb).
- Berjalan di shared hosting.
- Masih banyak lagi.

### Kebutuhan Sistem

Berikut adalah kebutuhan dasar yang diperlukan untuk dapat menjalankan Hexazor:

- PHP versi 5.4.0 atau yang lebih baru
- Ekstensi OpenSSL
- Ekstensi PDO
- Ekstensi Mbstring
- Apache Webserver


## Selayang Pandang

**Routing:**
```php
Route::prefix('frontend')->namespaces('frontend')->group(function () {
	Route::get('/', 'Home@index');
	Route::get('/info', 'Home@info');
	Route::get('/blog', 'Blog@posts');
});
```

**View**
```blade
{{-- welcome.blade.php --}}

<p>Halo {{ $name }}</p>
```


**Controller:**
```php
namespace App\Http\Controllers;

use App\Http\Controller;
use View;

class Hello extends Controller
{
	public function index()
	{
		$name = 'Budi';

		View::make('welcome', compact('name'));
	}
}
```

**Model:**
```php
$user = User::first();

$users = User::whereIn('id', [1, 2, 3])
	->orWhere('email', '=', $email)
	->take(10)
	->get();
```

## Dokumentasi

Dokumentasi resmi dari Hexazor bisa dibaca di [Halaman Dokumentasi](https://esyede.github.io/hexazor).


## Informasi Lisensi

Hexazor adalah perangkat lunak bersumber terbuka yang dilisensikan di bawah [Lisensi MIT.](http://www.opensource.org/licenses/mit-license.php)
