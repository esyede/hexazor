# Controller

## Daftar Isi

-   [Pengetahuan Dasar](#pengetahuan-dasar)
-   [Aturan Penulisan Controller](#aturan-penulisan-controller)
-   [Membuat Controller](#membuat-controller)
-   [Parameter Controller](#parameter-controller)
-   [Meletakkan Controller Dalam Subfolder](#meletakkan-controller-dalam-subfolder)
-   [Memanggil Middleware](#memanggil-middleware)
-   [Memanggil Model](#memanggil-model)
-   [Memanggil Library](#memanggil-library)

## Pengetahuan Dasar

Controller adalah kelas yang menangani request pengunjung di aplikasi Anda. Alih-alih mendefinisikan semua logika penanganan request Anda sebagai Closure dalam file _routes.php_, Anda mungkin ingin mengatur perilaku ini menggunakan kelas Controller. Controller dapat mengelompokkan logika penanganan request terkait ke dalam satu kelas.

Hexazor mengikuti aturan [PSR-4](https://www.php-fig.org/psr/psr-4/) untuk melakukan autoloading, dengan begitu Anda dapat meletakkan controller dimanapun yang Anda mau. Meskipun begitu, kami sangat menyarankan agar controller disimpan di folder `app/Controllers` agar struktur folder Anda tetap rapi dan teratur.

Secara default, Hexazor menyertakan 2 buah controller didalam untuk Anda, yaitu sebuah controller bernama `Controller` yang difungsikan sebagai controller induk, dan satu lagi `Home` controller untuk menjalankan halaman pembuka. Bukalah file tersebut agar mendapat sedikit gambaran tentang cara penulisan controller di Hexazor.

## Aturan Penulisan Controller

Hexazor menerapkan beberapa aturan dasar untuk penulisan controller:

-   Controller harus diletakkan di dalam folder `app/Controllers`.
-   Nama file controller harus sama dengan nama kelas didalamnya.
-   Nama controller harus ditulis menggunakan pola StudlyCase.
-   Satu file controller hanya boleh diisi dengan satu kelas.

## Membuat Controller

Sekarang, mari kita coba membuat controller pertama kita:

```php
// disimpan di: app/Controllers/Hello.php

namespace App\Http\Controllers;

use App\Http\Controller;


class Hello extends Controller
{
	public function index()
	{
		echo 'Halo dunia! Ini adalah controller pertamaku!';
	}
}
```

Mantap! sekarang mari kita daftarkan controller kita ini. Buka file`app/Configs/routes.php` dan tambahkan kode berikut:

```php
Route::get('/hello', 'Hello@index');
```

> [!TIP]
> Untuk mempermudah pembuatan controller, Anda juga dapat memanfaatkan perintah `make:controller` di dalam [hexazor console](/console/make.md#make-controller).

## Parameter Controller

Anda juga boleh mengirim parameter ke method di kelas controller:

```php
class Hello extends Controller
{
	public function user($userId)
	{
		echo 'Hai! user id kamu adalah: #'.$userId;
	}
}
```

Dan untuk pendaftaran rutenya menjadi seperti ini:

```php
Route::get('/user/(\d+)', 'Hello@user');
```

## Meletakkan Controller Dalam Subfolder

Anda juga boleh meletakkan file controller kedalam subfolder. Pada contoh dibawah ini, kita akan coba membuat sebuah controller bernama `Dashboard` di dalam subfolder `app/Controllers/Backend/`:

```php
// disimpan di: app/Controllers/Backend/Dashboard.php

namespace App\Http\Controllers\Backend;

use App\Http\Controller;


class Dashboard extends Controller
{
	public function index()
	{
		echo 'Pesan ini datang dari file app/Controllers/Backend/Dashboard.php';
	}
}
```

> [!TIP]
> Perhatikan deklarasi namespace kita berubah mengikuti stuktur foldernya: `namespace App\Http\Controllers\Backend;`

Dan untuk definisi rutenya menjadi:

```php
Route::namespaces('backend')->group(function () {
	Route::get('/dashboard', 'Dashboard@index');
});
```

## Memanggil Middleware

Selain mendefinisikan middleware melalui file _routes.php_, Anda juga bisa memanggil middleware melalui controller:

```php
$this->middleware('auth');
```

> [!NOTE]
> Command konsol `hexazor route:list` tidak akan menampilkan middleware yang didaftarkan melalui cara ini.

## Memanggil Model

Anda juga dapat memanggil model dari controller. Cara pemanggilannya cukup menggunakan keyword `use`, seperti halnya memanggil kelas biasa di php:

```php
use App\Models\User;


// User::all()
```

## Memanggil Library

Sedangkan untuk memanggil library, Anda cukup memanggil nama kelasnya saja, tanpa perlu menulis namespacenya secara lengkap:

```php
use Date;


// Date::now();
```

Mudah bukan? Cara ini dimungkinkan karena Hexazor menerapkan mekanisme [Facades](/facades/index.md) untuk autoloading library.
