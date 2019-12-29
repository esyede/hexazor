# Query Builder

## Daftar Isi

-   [Pengetahuan Dasar](#pengetahuan-dasar)
-   [Mengambil Data](#mengambil-data)
-   [Membangun Klausa Where](#membangun-klausa-where)
-   [Where Bersarang](#where-bersarang)
-   [Where Dinamis](#where-dinamis)
-   [Join Tabel](#join-tabel)
-   [Sorting Data](#sorting-data)
-   [Grouping Data](#grouping-data)
-   [Skip & Take](#limit)
-   [Agregasi](#agregasi)
-   [Ekspresi SQL Mentah](#ekspresi-sql-mentah)
-   [Insert Data](#insert-data)
-   [Update Data](#update-data)
-   [Delete Data](#delete-data)


## Pengetahuan Dasar

Query Builder adalah kelas yang tersedia untuk membangun kueri SQL dan bekerja dengan database Anda. Semua perintah disiapkan menggunakan [prepared satatement](https://www.php.net/manual/en/pdo.prepare.php) sehingga otomatis terlindung dari serangan SQL Injection.

Anda bisa memulai query builder dengan memanggil method `table()` pada facade `DB`. Cukup sebutkan nama tabel yang ingin dioperasikan:

```php
// use DB;

$query = DB::table('users');
```

Sekarang anda memiliki akses query builder untuk tabel "users". Dengan query builder, Anda bisa melakukan operasi - opreasi umum seperti select, insert, update, atau delete data dari tabel.


## Mengambil Data

#### Select semua data dari tabel:

```php
$users = DB::table('users')->get();
```

> [!TIP]
> Method `get()` mereturn **array berisi sekumpulan object** dengan properti sesuai dengan kolom di tabel Anda.

#### Select hanya satu data dari tabel:

```php
$user = DB::table('users')->first();
```

#### Select hanya satu data berdasarkan primary key:

```php
$user = DB::table('users')->find($id);
```

> [!TIP]
> Jika tidak ada data yang ditemukan, method `first()` akan mereturn **NULL**. Sedangkan method `get()` method akan mereturn **array kosong**.

#### Select satu kolom dari tabel:

```php
$email = DB::table('users')->where('id', '=', 1)->only('email');
```

#### Select beberapa kolom dari database:

```php
$user = DB::table('users')->get(['id', 'email as user_email']);
```

#### Select data berdasarkan list kolom yang diberikan:

```php
$users = DB::table('users')->take(10)->lists('email', 'id');
```

> [!TIP]
> Parameter kedua di method `lists()` sifatnya opsional

#### Select distinct dari database:

```php
$user = DB::table('users')->distinct()->get();
```


## Membangun Klausa Where

### where dan orWhere

Tersedia beberapa method untuk membantu Anda dalam pembangunan klausa where. Method paling dasar yang dapat anda coba adalah `where()` dan `orWhere()`. Berikut cara penggunaanya:

```php
return DB::table('users')
	->where('id', '=', 1)
	->orWhere('email', '=', 'budi@gmail.com')
	->first();
```

Tentu saja, Tidak hanya operator "sama dengan", Anda juga boleh menggunakan operator lain:

```php
return DB::table('users')
	->where('id', '>', 1)
	->orWhere('name', 'LIKE', '%Budi%')
	->first();
```

Seperti yang bisa Anda bayangkan, secara default method `where()` akan ditambahkan ke susunan query menggunakan kondisi `AND`, sedangkan method `orWhere()` akan menggunakan kondisi `OR`.


### whereIn, whereNotIn, orWhereIn, dan orWhereNotIn

Kelompok method `whereIn()` memungkinkan Anda untuk dengan mudah membangun query pencarian pada data array:

```php
DB::table('users')->whereIn('id', [1, 2, 3])->get();

DB::table('users')->whereNotIn('id', [1, 2, 3])->get();

DB::table('users')
	->where('email', '=', 'budi@gmail.com')
	->orWhereIn('id', [1, 2, 3])
	->get();

DB::table('users')
	->where('email', '=', 'budi@gmail.com')
	->orWhereNotIn('id', [1, 2, 3])
	->get();
```


### whereNull, whereNotNull, orWhereNull, dan orWhereNotNull

Kelompok method `whereNull()` membuat pengecekan nilai NULL menjadi sangat mudah:

```php
return DB::table('users')->whereNull('updated_at')->get();

return DB::table('users')->whereNotNull('updated_at')->get();

return DB::table('users')
	->where('email', '=', 'budi@gmail.com')
	->orWhereNull('updated_at')
	->get();

return DB::table('users')
	->where('email', '=', 'budi@gmail.com')
	->orWhereNotNull('updated_at')
	->get();
```


### whereBetween, whereNotBetween, orWhereBetween, dan orWhereNotBetween

Kelompok method `whereBetween()` membuat pengecekan `BETWEEN` antara rentang nilai menjadi sangat mudah:

```php
return DB::table('users')->whereBetween($column, $min, $max)->get();

return DB::table('users')->whereBetween('updated_at', '2016-10-21', '2019-01-01')->get();

return DB::table('users')->whereNotBetween('updated_at', '2016-10-21', '2019-01-01')->get();

return DB::table('users')
	->where('email', '=', 'budi@gmail.com')
	->orWhereBetween('updated_at', '2016-10-21', '2019-01-01')
	->get();

return DB::table('users')
	->where('email', '=', 'budi@gmail.com')
	->orWhereNotBetween('updated_at', '2016-10-21', '2019-01-01')
	->get();
```


## Where Bersarang

Dimasa mendatang, Anda mungkin perlu mengelompokkan potongan - potongan klausa WHERE kedalam tanda kurung. Untuk melakukannya, Anda hanya perlu mengoper **Closure** sebagai parameter ke method `where()` ataupun `orWhere()`:

```php
$users = DB::table('users')
	->where('id', '=', 1)
	->orWhere(function ($query) {

		$query->where('age', '>', 25);
		$query->where('votes', '>', 100);

	})
	->get();
```

Contoh diatas akan menghasilkan query sebagai berikut:

```sql
SELECT * FROM "users" WHERE "id" = ? OR ("age" > ? AND "votes" > ?)
```


## Where Dinamis

Method where dinamis dapat meningkatkan kemudahan dalam membaca kodingan Anda. Anda pun bisa dengan mudah melakukannya:

```php
$user = DB::table('users')->whereEmail('budi@gmail.com')->first();

$user = DB::table('users')->whereEmailAndPassword('budi@gmail.com', Hash::make('budi123'));

$user = DB::table('users')->whereIdOrName(1, 'Budi');
```


## Join Tabel

Perlu join tabel? Silahkan gunakan method `join()` atau `leftJoin()`:

```php
DB::table('users')
	->join('phone', 'users.id', '=', 'phone.user_id')
	->get(['users.email', 'phone.number']);
```

Dimana nama tabel yang ingin Anda join dioper ke parameter pertama. Sedangkan 3 parameter setelahnya digunakan untuk menambahkan klausa `ON` pada query join.

Setelah bisa menggunakan method `join()`, Anda otomatis mampu menggunakan method `leftJoin()` karena urutan parameternya sama persis:

```php
DB::table('users')
	->leftJoin('phone', 'users.id', '=', 'phone.user_id')
	->get(['users.email', 'phone.number']);
```

Anda juga boleh memberikan lebih dari satu kondisi ke klausa `ON` dengan cara mengoper **Closure** ke parameter kedua:

```php
DB::table('users')
	->join('phone', function ($join) {

		$join->on('users.id', '=', 'phone.user_id');
		$join->orOn('users.id', '=', 'phone.contact_id');

	})
	->get(['users.email', 'phone.number']);
```


## Ordering Data

Anda dapat dengan mudah melakukan ordering / mengurutkan data hasil query menggunakan method `orderBy()`. Cukup taruh nama kolom di parameter pertama dan tipe pengurutannya (`desc` atau `asc`) ke parameter kedua:

```php
return DB::table('users')->orderBy('email', 'desc')->get();
```

Tentu saja, Anda boleh melakukan pengurutan kolom sebanyak yang Anda mau:

```php
return DB::table('users')
	->orderBy('email', 'desc')
	->orderBy('name', 'asc')
	->get();
```


## Grouping Data

Anda dapat dengan mudah melakukan grouping / pengelompokan data menggunakan method `groupBy()`:

```php
return DB::table('users')->groupBy('email')->get();
```


## Skip & Take

Jika Anda ingin me-`LIMIT` jumlah data hasil query, silahkan gunakan method `take()`:

```php
return DB::table('users')->take(10)->get();
```

Sedangkan untuk mengatur `OFFSET`, silahkan gunakan method `skip()`:

```php
return DB::table('users')->skip(10)->get();
```


## Agregasi

Perlu mengambil nilai `MIN`, `MAX`, `AVG`, `SUM`, atau `COUNT`? Cukup oper nama kolomnya:

```php
$min = DB::table('users')->min('age');

$max = DB::table('users')->max('weight');

$avg = DB::table('users')->avg('salary');

$sum = DB::table('users')->sum('votes');

$count = DB::table('users')->count();
```

Tentu saja, Anda juga bisa membatasi querynya terlebih dahulu menggunakan klausa WHERE:

```php
$count = DB::table('users')->where('id', '>', 10)->count();
```


## Ekspresi SQL Mentah

Terkadang Anda mungkin perlu menginsert nilai kolom menggunakan fungsi native SQL seperti `NOW()`. Tetapi, secara default, Query Builder akan secara otomatis meng-quote dan meng-escape value yang Anda oper padanya menggunakan parameter binding (untuk pencegahan sql injection). Untuk mem-bypass fitur ini, gunakan metode `raw()`, seperti ini:

```php
DB::table('users')->update(['updated_at' => DB::raw('NOW()')]);
```

> [!DANGER]
> Gunakan `DB::raw()` hanya jika Anda sudah tidak punya opsi lain ketika menggunakan Query Builder. Hal ini karena SQL yang diinject langsung ke database sangat rentan akan SQL Injection. Terlebih jika datanya berasal dari inputan user.

Method `raw()` akan memerintahkan Query Builder untuk meng-inject SQL mentah Anda secara langsung kedalam susunan query, tanpa menggunakan parameter binding. Contoh kasus penggunaan `DB::raw()` ini misalnya, Anda perlu meng-increment kolom _votes_:

```php
DB::table('users')->update(['votes' => DB::raw('votes + 1')]);
```

Tetapi tentu saja, telah disediakan juga method yang lebih mudah untuk melakukan operasi _increment_ dan _decrement_ ini:

```php
DB::table('users')->increment('votes');

DB::table('users')->decrement('votes');
```


#### Manual Escape

Seperti yang sudah dijelaskan diatas, penggunaan `DB::raw()` sangatlah rentan terhadap serangan SQL Injection, oleh karena itu, disediakan method bantuan untuk meng-escape value pada potongan query mentah Anda:

```php
// $name = Request::post('name');

return DB::raw('SELECT * FROM users WHERE name='.DB::escape($name))->get();
```


## Insert Data

Method `insert()` mengharapkan data array. Method ini akan me-return `TRUE` atau `FALSE`, yang mengindikasikan suskses atau tidaknya operasi insert Anda:

```php
DB::table('users')->insert(['email' => 'budi@gmail.com']);
```

Perlu insert data yang ID-nya auto-increment? Silahkan gunakan method `insertGetId()`, method ini akan meng-insert data lalu mereturn ID yang telah Anda insert tadi:

```php
$id = DB::table('users')->insertGetId(['email' => 'budi@gmail.com']);
```

> [!WARNING]
> Method `insertGetId()` mewajibkan kolom auto-increment Anda bernama `id`.


## Update Data

Untuk mengupdate data, cukup oper array asosiatif ke method `update()` seperti berikut:

```php
$data = [
	'email' => 'budi.baru@gmail.com'
	'name'  => 'Budi Purnomo'
];

$affected = DB::table('users')->update($data);
```

Tentu saja, jika Anda hanya ingin mengupdate beberapa kolom saja, Anda bisa menambahkan klausa WHERE sebelum memanggil method update():

```php
$data = [
	'email' => 'budi.baru@gmail.com'
	'name'  => 'Budi Purnomo'
];

$affected = DB::table('users')
	->where('id', '=', 1)
	->update($data);
```


## Delete Data

Sedangkan jika Anda ingin menghapus data, cukup panggil method `delete()`:

```php
$affected = DB::table('users')->where('id', '=', 1)->delete();
```

Ingin cara cepat menghapus data berdasarkan ID? Tidak masalah. Langsung saja oper ID-nya seperti berikut:

```php
$affected = DB::table('users')->delete(1);
```
