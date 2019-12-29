# Routing


## Daftar Isi

-   [Dasar - Dasar Routing](#dasar-dasar-routing)
-   [Closure Routing](#closure-routing)
-   [Tipe Method Routing](#tipe-method-routing)
-   [Controller Routing](#controller-routing)
-   [Parameter Dalam Routing](#parameter-dalam-routing)
-   [Route Grouping](#route-grouping)
-   [Route Namespacing](#route-namespacing)
-   [Route Middleware](#route-middleware)
-   [Domain Routing](#domain-routing)
-   [IP Address Routing](#ip-address-routing)
-   [SSL Enforcement](#ssl-enforcement)
-   [Route Naming](#route-naming)


## Dasar - Dasar Routing

Di Hexazor, pendefinisian rute sangatlah sederhana dan fleksibel. Seluruh definisi rute disimpan dalam file `routes/web.php`. Silahkan buka file tersebut agar Anda mendapat gambaran bagaimana pendefinisian rute di Hexazor dilakukan.


## Closure Routing

Di Hexazor, operasi closure routing dilakukan dengan memanggil method rute sesuai dengan request method yang diinginkan, lalu mengoper URI dan Closure kedalamnya:

```php
Route::get('/', function () {
	return 'Halo dunia!';
});
```


## Tipe Method Routing

Rute dikonfigurasikan mengikuti penamaan HTTP request method:

```php
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
Route::patch($uri, $callback);
Route::head($uri, $callback);
```

Jika Anda perlu mendefinisikan rute menggunakan lebih dari satu request method, gunakan method `match()` seperti berikut:

```php
Route::match(['GET', 'POST'], '/profile', function () {
	// code
});
```


## Controller Routing

Anda juga bisa menggunakan controller sebagai callback untuk menangani rute:

```php
Route::get('/profil', 'Users@profile');
```


## Parameter Dalam Routing

Ketika mendefinisikan rute, Anda juga boleh mengirim parameter ke closure ataupun controller method Anda:

```php
Route::get('/user/{param}', function ($param) {
	echo 'Parameter yang dikirim adalah: '.$param;
});
```
Gunakan method `where()` jika Anda ingin mem-filter datanya menggunakan regular expression:

```php
Route::get('/user/{name}', function ($name) {
	echo 'Halo, '.$name;
})->where(['name' => '([A-Za-z]+)']);


Route::get('/blog/{categoryId}/detail/{postId}', 'Blog@post')
	->where([
		'categoryId' => '(\d+)',
		'postId' => '(\d+)',
	]);
```

Lalu Anda tinggal menangkap parameternya dari dalam method controller:

```php
class Blog extends Controller
{
	public function post($categoryId, $postId)
	{
		echo 'ID Kategori: #'.$categoryId.', ID Post: #'.$postId;
	}
}
```


## Route Grouping

Anda juga dapat mengelompokkan rute kedalam sebuah grup. Ini dilakukan agar Anda tidak perlu mengulang - ulang penulisan rute untuk menangani url dengan prefix yang sama:

```php
Route::prefix('frontend')->group(function () {
	Route::get('/', 'Home@index');     // site.com/frontend
	Route::get('/home', 'Home@index'); // site.com/frontend/home
	Route::get('/blog', 'Blog@posts'); // site.com/frontend/blog
});

Route::prefix('backend')->group(function () {
	Route::get('/', 'Dashboard@index');             // site.com/backend
	Route::get('/dashboard', 'Dashboard@index');    // site.com/backend/dashboard
	Route::get('/dashboard/posts', 'Posts@index');  // site.com/backend/dashboard/posts
});
```

Keuntungan lain dari route grouping adalah bahwa halaman yang menggunakan namespace atau middleware yang sama dapat dikelompokkan menjadi satu. Dengan cara ini, namespace dan middleware tidak perlu didefinisikan secara terpisah untuk setiap rute, cukup tulis di grupnya saja:

```php
// site.com/frontend/...   -->  App\Http\Controllers\Frontend\...
Route::prefix('frontend')->namespaces('frontend')->group(function () {
	Route::get('/', 'Home@index');
	Route::get('/info', 'Home@info');
	Route::get('/blog', 'Blog@posts');
});

// site.com/backend/...   -->  App\Http\Controllers\Backend\... dengan middleware 'auth' dan 'verified'
Route::prefix('backend')->namespaces('backend')->middleware(['auth', 'verified'])->group(function () {
	Route::get('/', 'Dashboard@index');
	Route::get('/dashboard', 'Dashboard@index');
	Route::get('/posts', 'Posts@index');
});
```


## Route Namespacing

Seperti halnya contoh diatas, namespace dapat didefinisikan secara terpisah untuk setiap rute dan grup rute. Dengan demikian, Anda tidak perlu menulis lokasi controller secara utuh bersama namepacenya, cukup tulis class dan methodnya nya saja.

```php
Route::namespaces('frontend')->group(function () {
	Route::get('/', 'Home@index'); // App\Http\Controllers\Frontend\Home@index
});
```

Namespace bisa dipanggil tanpa grouping:

```php
Route::namespaces('frontend')->get('/dashboard', 'Dashboard@index');
```

> [!NOTE]
> Nama methodnya adalah `namespaces()` ya! dibuat plural karena di PHP 5.4 keyword `namespace` tidak bisa dipakai untuk nama method.


## Route Middleware

Middleware juga dapat didefinisikan secara terpisah untuk setiap rute dan grup rute. Middleware akan dijalankan sebelum halaman yang ditargetkan oleh rute ini diproses. Berikut ini adalah contoh penggunaan middleware `auth` di dalam rute:

```php
Route::middleware('auth')->group(function () {
	Route::get('/dashboard', 'Dashboard@index');
});

Route::middleware(['auth', 'verified'])->group(function () {
	// ..
});
```

Anda juga boleh menggunakan beberapa middleware sekaligus:

```php
Route::middleware(['auth', 'verified'])->group(function () {
	Route::get('/dashboard', 'Dashboard@index');
});
```

Middleware juga dapat dipanggil tanpa grouping:

```php
Route::middleware(['auth'])->get('/dashboard', 'Dashboard@index');
```


## Domain Routing

Anda boleh membatasi akses domain saat routing. Halaman yang ditargetkan oleh rute ini hanya akan dijalankan jika berada di bawah domain yang ditentukan, jika sebaliknya, halaman 404 akan ditampilkan.

```php
Route::domain('api.site.com')->namespaces('api')->group(function () {
	Route::get('/', 'Home@index');      // http://api.site.com/
	Route::get('/login', 'Auth@login'); // http://api.site.com/login
});
```


## IP Address Routing

Setiap rute dan grup rute juga dapat dibatasi dengan ip. Halaman yang ditargetkan oleh rute ini hanya menanggapi permintaan dari alamat ip yang ditentukan. Jika tidak, semua permintaan juga dialihkan ke halaman 404.

```php
Route::ip('192.168.123.123')->namespaces('api')->group(function () {
	Route::get('/', 'Home@index');      // http://192.168.123.123/
	Route::get('/login', 'Auth@login'); // http://192.168.123.123/login
});

Route::ip(['192.168.123.123', '192.168.123.456'])->namespaces('api')->group(function () {
	// http://192.168.123.123/ atau, http://192.168.123.456/
	Route::get('/', 'Home@index');

	// http://192.168.123.123/login atau, http://192.168.123.456/login
	Route::get('/login', 'Auth@login');
});
```


## SSL Enforcement

Untuk setiap rute dan grup rute, dapat ditambahkan aturan SSL. Halaman yang ditargetkan oleh rute ini hanya menanggapi permintaan yang aman yang datang dengan SSL (https). Jika tidak, semua permintaan dialihkan ke halaman 404.

```php
Route::ssl()->namespaces('api')->group(function () {
	Route::get('/', 'Home@index');      // https://api.site.com/
	Route::get('/login', 'Auth@login'); // https://api.site.com/login
});
```


## Route Naming

Penamaan dapat dilakukan untuk setiap rute. Dengan demikian Anda dapat dengan mudah memanggil route yang besangkutan, terutama saat menggunakan helper [route()](/helpers.md#route).

```php
Route::get('/', 'Home@index')->name('homepage');
Route::post('/contact', 'Contact@send')->name('contact.send');
```
