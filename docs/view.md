# View

## Daftar Isi

-   [Pengetahuan Dasar](#pengetahuan-dasar)
-   [Membuat View](#membuat-view)
-   [Meletakkan View Dalam Subfolder](#meletakkan-view-dalam-subfolder)
-   [Variabel Pada View](#variabel-pada-view)

## Pengetahuan Dasar

View adalah sajian informasi (yang mudah dimengerti) kepada user sesuai dengan instruksi dari controller. View inilah yang Anda lihat saat Anda mengakses sebuah halaman web. Hexazor juga menyediakan sebuah pustaka untuk penanganan view, mari kita simak bersama - sama.

## Membuat View

Seluruh file view disimpan di dalam folder `app/Views`. File view harus ber-ekstensi `.blade.php`:

```html
<!-- disimpan di: app/Views/home.blade.php -->
<html>
	<body>
		<p>Halo Dunia!</p>
	</body>
</html>
```

## Memanggil View

Untuk memangil view dari dalam controller, Anda perlu mengimpor [View Library](/libraries/view.md#view-library) terlebih dahulu. Kemudian, gunakan method `make()` untuk menampilkannya ke user:

```php
use View;

class Sample extends Controller
{
	public function index()
	{
		View::make('home');
	}
}
```

> [!TIP]
> Method `View::make()` inilah yang akan meng-kompilasi view Anda kedalam statement PHP yang valid. File hasil kompilasinya disimpan di folder `storage/views/`

## Meletakkan View Dalam Subfolder

Tentu saja, Anda boleh membuat subfolder didalam folder `app/Views/`. Sebagai contoh, mari kita coba membuat file view bernama `dashboard.blade.php` di dalam subfolder `app/Views/backend/` seperti ini:

```html
<!-- disimpan di: app/Views/backend/dashboard.blade.php -->
<html>
	<body>
		<p>Ini adalah view Dashboard!</p>
	</body>
</html>
```

Untuk memanggilnya, gunakan tanda `.` (titik) atau `/` (forward-slash) sebagai pemisah direktorinya:

```php
View::make('backend.dashboard');
```

## Variabel Pada View

Anda juga bisa mengoper variabel dari controller ke file view, seperti ini:

```php
$userName = 'Budi';

View::make('backend.dashboard', compact('userName'));
```

Dan untuk menampilkannya di view, cukup tulis seperti ini:

```php
<p>Halo {{ $userName }}</p>
```

> [!TIP]
> Silahkan baca lebih lanjut mengenai fitur - fitur dan cara penggunaan view di halaman [View Library](/libraries/view.md#view-library)
