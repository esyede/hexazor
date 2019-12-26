# ORM Model

## Daftar Isi

-   [Pengetahuan Dasar](#pengetahuan-dasar)
-   [Asumsi Dasar](#asumsi-dasar)
-   [Mengambil Model](#mengambil-model)
-   [Agregasi](#agregasi)
-   [Insert & Update Model](#insert-amp-update-model)
-   [Relasi](#relasi)
    -   [Satu-ke-Satu](#satu-ke-satu)
    -   [Satu-ke-Banyak](#satu-ke-banyak)
    -   [Banyak-ke-Banyak](#banyak-ke-banyak)
-   [Insert Ke Model Yang Berelasi](#insert-ke-model-yang-berelasi)
    -   [Insert Ke Model Yang Berelasi (Banyak-ke-Banyak)](#insert-ke-model-yang-berelasi-banyak-ke-banyak)
-   [Bekerja Dengan Tabel Perantara (Pivot)](#bekerja-dengan-tabel-perantara-pivot)
-   [Eager Loading](#eager-loading)
-   [Constraint Pada Eager Loads](#constraint-pada-eager-loads)
-   [Setter & Getter](#setter-amp-getter)
-   [Mass-Assignment](#mass-assignment)
-   [Mengubah Model Menjadi Array](#mengubah-model-menjadi-array)
-   [Menghapus Model](#menghapus-model)

## Pengetahuan Dasar

Tentu Anda sudah tahu apa itu ORM atau [object-relational mapper](http://en.wikipedia.org/wiki/Object-relational_mapping) ini, di Hexazor, tersedia ORM modeller dengan sintaks yang ekspresifdan mudah dipahami. Secara umum, Anda akan membuat satu Model untuk tiap - tiap tabel yang Anda miliki. Model diletakkan ke dalam folder `app/Models/`. Untuk permulaan, mari kita buat sebuah model yang sederhana dahulu:

```php
// disimpan di: app/Models/User.php

namespace App\Models;
defined('DS') or exit('No direct script access allowed.');

use System\Database\ORM\Model;

class User extends Model
{
	//
}
```

Mantap! Perhatikan bahwa model kita meng-_extends_ kelas `Model` yang berada di `System\Database\ORM\Model`. Kelas inilah yang akan menyediakan seluruh fungsionalitas yang Anda butuhkan untuk mulai bekerja dengan database Anda.

> [!WARNING]
> File - file model ini **WAJIB** diletakkan di dalam folder `app/Models`.

> [!WARNING]
> Kode `defined('DS') or exit('...');` **WAJIB** ditambakan untuk mencegah akses file ini secara langsung dari web browser.

## Asumsi Dasar

Secara default, ORM Model membuat beberapa asumsi dasar tentang struktur database Anda, yaitu:

-   Setiap tabel _pasti_ memiliki primary key bernama `id`
-   Nama tabel _pasti_ berupa bentuk plural (jamak) dari nama modelnya. Misalnya, jika tabel Anda bernama `users`, maka Anda harus menamai modelnya dengan nama `User`.

Tetapi, ada kalanya Anda ingin menggunakan nama tabel selain bentuk plural dari nama model Anda, atau Anda ingin menggunakan primary key dengan nama selain 'id'. Tidak masalah, cukup tambahkan property static `$table` dan `$key` ke dalam model Anda:

```php
class User extends Model
{
	public static $table = 'pengguna';
	public static $key = 'id_pengguna';

	//
}
```

## Mengambil Model

Mengambil model menggunakan ORM Model ini sangat mudah. Cara paling dasar untuk mengambil model adalah dengan menggunakan method static `find()`. Method ini akan me-return **sebuah object model** berdasarkan primary key-nya, object ini berisi property sesuai kolom - kolom di tabel database Anda:

```php
use App\Models\User;

$user = User::find(1);

echo $user->email; // tono@gmail.com
```

Method `find()` ini akan meng-eksekusi query sql seperti berikut:

```sql
SELECT * FROM "users" WHERE "id" = 1
```

Perlu mengambil semua data di dalam tabel? Cukup gunakan method static `all()`:

```php
$users = User::all();

foreach ($users as $user) {
	echo $user->email;
}
```

Tentu saja, mengambil seluruh data dari tabel akan sangat memberatkan kinerja server Anda. Untungnya, **Setiap method yang tersedia di Query Builder juga dapat digunakan di dalam Model**. Cukup mulai query Anda dengan memanggil salah satu method static milik [Query Builder](database/query-builder.md#query-builder), dan ambil hasil querynya menggunakan method `get()` atau `first()`. Method `get()` akan mengembalikan **array berisi beberapa object model**, sedangkan method `first()` akan mengembalikan **satu buah object model**:

```php
	$user = User::where('email', '=', $email)->first();

	$user = User::whereEmail($email)->first();

	$users = User::whereIn('id', [1, 2, 3])->orWhere('email', '=', $email)->get();

	$users = User::orderBy('votes', 'desc')->take(10)->get();
```

> [!TIP]
> Jika tidak ada data yang didapat, method `first()` akan me-return **NULL**. Sedangkan method `all()` dan `get()` akan me-return **array kosong**.

## Agregasi

Perlu mengambil nilai **MIN**, **MAX**, **AVG**, **SUM**, atau **COUNT**? Cukup oper nama kolomnya:

```php
$min = User::min('id');

$max = User::max('id');

$avg = User::avg('id');

$sum = User::sum('id');

$count = User::count();
```

Tentu saja, Anda tetap bisa membatasi kueri menggunakan klausa WHERE terlebih dahulu:

```php
$count = User::where('id', '>', 10)->count();
```

## Insert & Update Model

Insert data model ke tabel Anda juga sangatlah mudah, semudah menghitung satu sampai tiga. Pertama, instansiasi modelnya. Kedua, atur propertinya. Ketiga, panggil method `save()`:

```php
$user = new User;

$user->email = 'budi@gmail.com';
$user->password = 'budi123';

$user->save();
```

Atau, Anda juga dapat menggunakan method `create()`, yang akan meng-insert data baru ke dalam database dan me-return instance model untuk data yang baru saja Anda insert, atau **FALSE** jika operasi insertnya gagal.

```php
$user = User::create(['email' => 'budi@gmail.com']);
```

Update model juga sama mudahnya. Alih-alih membuat instance model baru, Anda cukup mengambil satu data dari database. Kemudian atur propertinya, lalu simpan menggunakan method `save()`:

```php
$user = User::find(1);

$user->email = 'email_baruku@gmail.com';
$user->password = 'password_baruku';

$user->save();
```

Perlu memperbarui waktu pembuatan dan waktu update data di tabel Anda? Anda tidak perlu khawatir. Cukup tambahkan properti static `$timestamps` ke model Anda:

```php
class User extends Model
{
	public static $timestamps = true;

	//
}
```

Selanjutnya, jika belum ada, tambahkan kolom betipe tanggal dengan nama `created_at` dan `updated_at` kedalam tabel Anda. Sekarang, setiap Anda menyimpan model, kedua kolom tersebut akan diperbarui secara otomatis. Mudah bukan?

Dalam beberapa kasus, mungkin Anda perlu memperbarui kolom tanggal `updated_at` tanpa mengubah data apa pun dalam model. Cukup gunakan method `touch()`, ia akan secara otomatis memperbarui kolom tanggal `updated_at` Anda:

```php
$comment = Comment::find(1);
$comment->touch();
```

Anda juga bisa menggunakan method `timestamp()` untuk memperbarui kolom `updated_at` jika Anda tidak ingin menyimpan perubahannya secara otomatis. Perlu diingat bahwa ketika Anda mengubah data di model, method ini sudah secara otomatis dipanggil, sehinga Anda tidak perlu memanggilnya setiap kali melakukan penyimpanan data:

```php
$comment = Comment::find(1);
$comment->timestamp();
// lakukan hal lain disini, tetapi jangan mengubah data di model $comment

$comment->save();
```

> [!TIP]
> Anda bisa mengubah default timezone di file `app/Configs/app.php`.

## Relasi

Lazimnya, tabel database Anda akan ber-relasi satu sama lain. Misalnya, tabel _Order_ mungkin ber-relasi _'milik'_ ke _User_. Atau, sebuah _Post_ mungkin _'memiliki banyak'_ _Comment_. Hexazor membuat pendefinisian relasi dan pengambilan data relasi menjadi sangat sederhana dan intuitif. Hexazor mendukung tiga jenis relasi:

-   [Satu-ke-Satu](#satu-ke-satu)
-   [Satu-ke-Banyak](#satu-ke-banyak)
-   [Banyak-ke-Banyak](#banyak-ke-banyak)

Untuk mendefinisikan relasi pada model, Anda cukup membuat method yang me-return hasil dari method `hasOne()`, `hasMany()`, `belongsTo()`, atau `hasManyAndBelongsTo()`. Mari kita coba masing-masing method tersebut:

### Satu-ke-Satu

Relasi satu-ke-satu adalah bentuk relasi yang paling dasar. Misalnya, katakanlah User memiliki satu Phone. Cukup jelaskan relasi ini kedalam Model:

```php
class User extends Model
{
	public function phone()
	{
		return $this->hasOne('Phone');
	}
}
```

Perhatikan bahwa nama model relasi dioper ke method `hasOne()`. Anda sekarang dapat mengambil telepon milik user melalui method `phone()`:

```php
$phone = User::find(1)->phone()->first();
```

Mari kita periksa SQL yang dijalankan oleh statement ini. Dua statement akan dijalankan: satu untuk mengambil user dan satu lagi untuk mengambil telepon milik user:

```sql
SELECT * FROM "users" WHERE "id" = 1

SELECT * FROM "phones" WHERE "user_id" = 1
```

Perhatikan bahwa Model mengasumsikan foreign key dari relasi tersebut adalah `user_id`. Hampir seluruh foreign key di Model akan mengikuti aturan `model + _id` ini. Namun, jika Anda ingin menggunakan nama kolom yang berbeda sebagai foreign key, cukup oper namanya ke parameter ke-dua:

```php
return $this->hasOne('Phone', 'my_foreign_key');
```

Ingin langsung mengambil telepon milik user tanpa memanggil method `first()`? Tidak masalah. Cukup gunakan properti dinamis `$phone`. Model akan secara otomatis memuat relasinya untuk Anda, dan bahkan cukup dia pintar untuk mengetahui method mana yang harus dipanggil, apakah harus memanggil method `get()` (untuk relasi satu-ke-banyak) atau `first()` (untuk relasi satu-ke-satu):

```php
$phone = User::find(1)->phone;
```

Bagaimana jika Anda perlu mengambil user pemilik telepon? Karena foreign key-nya (`user_id`) ada di tabel _phone_, kita harus mendeskripsikan relasi ini menggunakan method `belongsTo()`. Masuk akal, bukan? Phone _belongs to_ User. Saat menggunakan method `belongsTo()`, nama method relasi harus sesuai dengan foreign key-nya (tanpa suffix _\_id_ dibelakangnya). Jadi, karena foreign key-nya adalah `user_id`, maka method relasi Anda harus dinamai `user()`:

```php
class Phone extends Model
{
	public function user()
	{
		return $this->belongsTo('User');
	}

}
```

Mantap! Anda sekarang dapat mengakses model User melalui model Phone menggunakan method relasi (method `user()` yang telah kita buat tadi) atau menggunakan properti dinamis:

```php
echo Phone::find(1)->user()->first()->email;

echo Phone::find(1)->user->email;
```

### Satu-ke-Banyak

Anggap sebuah Post 'memiliki banyak' Comment. Relasi ini bisa mudah didefinisikan menggunakan method `hasMany()`:

```php
class Post extends Model
{
	public function comments()
	{
		return $this->hasMany('Comment');
	}

}
```

Sekarang, cukup akses Comment milik Post melalui method relasi atau melelui property dinamis:

```php
$comments = Post::find(1)->comments()->get();

$comments = Post::find(1)->comments;
```

Kedua statement diatas akan mengeksekusi SQL berikut:

```sql
SELECT * FROM "posts" WHERE "id" = 1

SELECT * FROM "comments" WHERE "post_id" = 1
```

Ingin join tabel dengan foreign key yang berbeda? Tidak masalah. Cukup oper nama foreign key-nya ke parameter kedua:

```php
return $this->hasMany('Comment', 'my_foreign_key');
```

Anda mungkin bertanya-tanya: _"Kalau property dinamis juga me-return data hasil relasi dan lebih pendek untuk ditulis, kenapa saya harus menggunakan method relasi yang panjang dan bertele - tele ini?"_ Karena, method relasi ini sangat berguna. Method ini memungkinkan Anda untuk terus menyambung method Query Builder sebelum mengambil data hasil relasi. Lihat ini:

```php
return Post::find(1)->comments()->orderBy('votes', 'desc')->take(10)->get();
```

### Banyak-ke-Banyak

Relasi banyak-ke-banyak adalah yang paling rumit dari tiga relasi diatas. Tapi jangan khawatir, Anda bisa menyelesaikannya. Misalnya, anggap User memiliki banyak Roles, tetapi Roles juga dapat menjadi milik banyak User. Tiga tabel database harus dibuat untuk menangani relasi ini: tabel `users`, tabel `roles`, dan tabel `role_user`. Struktur untuk setiap tabel akan terlihat seperti ini:

Tabel `users`:

```sql
`id`    - INTEGER
`email` - VARCHAR
```

Tabel `roles`:

```sql
`id`    - INTEGER
`role`  - VARCHAR
```

Tabel `role_user`:

```sql
`id`      - INTEGER
`user_id` - INTEGER
`role_id` - INTEGER
```

Tabel berisi akan banyak data dan bentuknya plural (jamak). Penamaan tabel Pivot (atau tabel perantara) yang digunakan dalam relasi `hasManyAndBelongsTo()` adalah dengan menggabungkan nama singular (tunggal) dari dua model relasi yang disusun secara urut mengikuti alfabet, dan digabungkan dengan garis bawah.

Jadi jika dua tabel Anda bernama _users_ dan _roles_, maka tabel pivot Anda akan bernama _role_user_. Kenapa bisa begitu? Ingat, bentuk tunggal dari _users_ adalah _user_, sedangkan bentuk tunggal dari _roles_ adalah _role_. Lalu, _role_ dimulai dengan huruf _R_ sedangkan _user_ dimulai dengan huruf _U_. Maka, jika mengikuti alfabet menjadi _role_ + _\__ + _user_ sehingga hasil akhirnya adalah: _role_user_.

```php
class User extends Model
{
	public function roles()
	{
		return $this->hasManyAndBelongsTo('Role');
	}

}
```

Mantap! Sekarang Anda sudah bisa mengambil role milik user:

```php
$roles = User::find(1)->roles()->get();
```

Atau, seperti biasa, Anda bisa menggunakan properti dinamis:

```php
$roles = User::find(1)->roles;
```

Jika penamaan tabel Anda tidak mengikuti aturan diatas, cukup oper nama tabel ke parameter ke-dua dari method `hasManyAndBelongsTo()`:

```php
class User extends Model
{
	public function roles()
	{
		return $this->hasManyAndBelongsTo('Role', 'user_roles');
	}

}
```

Secara default, hanya beberapa field saja yang akan didapat dari query ke tabel pivot ini (yaitu dua kolom _\_id_ dan kolom timestamps (_created_at_, _deleted_at_)). Jika tabel pivot Anda mengandung kolom lain, Anda juga dapat mengambilnya menggunakan method `with()`:

```php
class User extends Model
{
	public function roles()
	{
		return $this->hasManyAndBelongsTo('Role', 'user_roles')->with('nama_kolom');
	}

}
```

## Insert Ke Model Yang Berelasi

Anggaplah Anda memiliki model **Post** yang memiliki banyak komentar. Seringkali Anda ingin meng-insert komentar baru untuk post yang diberikan. Alih-alih mengatur secara manual foreign key _post_id_ pada model Anda, Anda dapat meng-insert komentar baru dari model Post yang memilikinya. Begini contohnya:

```php
$comment = new Comment(['message' => 'Ini komentar pertama dari budi.']);

$post = Post::find(1);

$comment = $post->comments()->insert($comment);
```

Saat meng-insert model ber-relasi melalui model induknya, foreign key-nya akan secara otomatis terisi. Jadi, dalam contoh ini, `post_id` secara otomatis terisi dengan angka `1` pada komentar yang baru saja diinsert.

Ketika bekerja dengan relasi `hasMany`, Anda boleh menggunakan method `save()` untuk insert / update model yang berelasi:

```php
$comments = [
	['message' => 'Ini komentar pertama dari budi.'],
	['message' => 'Ini komentar kedua dari budi.'],
];

$post = Post::find(1);

$post->comments()->save($comments);
```

### Insert Ke Model Yang Berelasi (Banyak-ke-Banyak)

Ini bahkan lebih membantu ketika bekerja dengan relasi banyak-ke-banyak. Misalnya, asumsikan model **User** yang memiliki banyak role. Demikian juga, model **Role** mungkin juga memiliki banyak user. Jadi, tabel perantara untuk hubungan ini memiliki kolom `user_id` dan `role_id`. Sekarang, mari kita insert Role baru untuk User:

```php
$role = new Role(['title' => 'Admin']);

$user = User::find(1);

$role = $user->roles()->insert($role);
```

Sekarang, setiap kali Anda meng-insert tabel Role, tidak hanya Role yang diinsert ke dalam tabel `roles`, tetapi data di tabel pivotnya juga ikut diinsert secara otomatis. Mantap!

Namun, Anda mungkin sering hanya ingin meng-insert data baru ke tabel pivot. Misalnya, mungkin role yang ingin Anda berikan ke user sudah ada. Cukup gunakan method `attach()`:

```php
$user->roles()->attach($role_id);
```

Anda juga bisa meng-insert data untuk kolom di tabel perantara (tabel pivot), untuk melakukan ini, tambahkan variabel array kedua ke method `attach()` yang berisi data yang ingin Anda insert:

```php
$user->roles()->attach($role_id, ['expires' => $expires]);
```

Sebagai alternatif, Anda juga dapat menggunakan method `sync()`, yang menerima array ID yang akan _di-sinkronisasi_ dengan tabel perantara. Setelah operasi ini selesai, hanya ID dalam array yang akan berada di tabel perantara.

```php
$user->roles()->sync([1, 2, 3]);
```

## Bekerja Dengan Tabel Perantara (Pivot)

Seperti yang Anda ketahui, relasi banyak-ke-banyak membutuhkan adanya tabel perantara. Hexazor memberikan kemudahan untuk mengelola tabel ini. Sebagai contoh, mari kita asumsikan kita memiliki model **User** yang memiliki banyak role. Dan, juga, model **Role** yang memiliki banyak user. Jadi tabel perantaranya memiliki kolom `user_id` dan `role_id`. Kita dapat mengakses tabel pivot untuk relasinya seperti ini:

```php
$user = User::find(1);

$pivot = $user->roles()->pivot();
```

Setelah kita punya instance tabel pivotnya, kita bisa menggunakannya seperti model biasa:

```php
$pivots = $user->roles()->pivot()->get();

foreach ($pivots as $row) {
	//
}
```

Anda juga boleh mengakses row spesifik di tabel pivot yang berasosiasi dengan record yang diberikan. Sebagai contoh:

```php
$user = User::find(1);

foreach ($user->roles as $role) {
	echo $role->pivot->created_at;
}
```

Perhatikan bahwa setiap model **Role** terkait yang kita ambil secara otomatis diberikan atribut `$pivot`. Atribut ini berisi model yang mewakili data tabel perantara yang terkait dengan model.

Terkadang, Anda mungkin ingin menghapus semua data dari tabel perantara untuk model relasi yang diberikan. Misalnya, mungkin Anda ingin menghapus semua role yang milik user. Berikut cara melakukannya:

```php
$user = User::find(1);

$user->roles()->delete();
```

Mhon diperhatikan bahwa ini tidak menghapus role dari tabel "roles", tetapi hanya menghapus data dari tabel pivot yang merelasikan role dengan user yang diberikan.

## Eager Loading

Eager loading ada untuk menangani masalah N + 1 pada query. Ini masalah apa sih? Begini contohnya, anggap setiap Author memiliki Buku. Seperti biasa, kita akan menggambarkan relasinya seperti ini:

```php
class Book extends Model
{
	public function author()
	{
		return $this->belongsTo('Author');
	}

}
```

Sekarang, coba perhatikan kode berikut:

```php
$books = Book::all();

foreach ($books as $book) { // <-- perhatikan loop dari variabel $books ini
	echo $book->author->name;
}
```

Berapa banyak query yang akan dieksekusi? Baiklah, satu query akan dieksekusi untuk mengambil semua buku dari tabel. Namun, query lain akan diperlukan pada setiap Buku untuk mengambil authornya. Jika kita ingin menampilkan nama author untuk 25 buku, kita akan membutuhkan **26 query** (N + 1 kan?). Lalu, bagaimana jika kita punya 10.000 buku? atau, 2 juta buku? Lihat, query Anda akan semakin bertambah banyak. Efeknya, waktu eksekusi query Anda akan semakin lama, dan penggunaan resource server juga akan semakin besar, belum lagi jika aplikasi anda diakses oleh lebih dari 1 user, apa yang akan terjadi?

Untungnya, Anda bisa meng- eager-load model Author menggunakan method `with()`. cukup sebutkan **nama method** dari relasi yang ingin Anda eager-load:

```php
$books = Book::with('author')->get(); // 'author' == Book::author()

foreach ($books as $book) {
	echo $book->author->name;
}
```

Pada contoh diatas, **hanya dua query yang akan dieksekusi!**

```sql
SELECT * FROM "books"

SELECT * FROM "authors" WHERE "id" IN (1, 2, 3, 4, 5, ...)
```

Jelasnya, penggunaan metode eager-loading dengan bijak dapat secara dramatis meningkatkan kinerja aplikasi Anda. Pada contoh di atas, eager-load memotong waktu eksekusi query menjadi hanya separuhnya.

Perlu meng- eager-load lebih dari satu relasi? Bisa kok:

```php
$books = Book::with(['author', 'publisher'])->get();
```

> [!WARNING]
> Ketika melakukan eager-load, penggunaan method static `with()` harus selalu diletakkan di awal.

Anda bahkan bisa meng- eager-load relasi yang bersarang. Sebagai contoh, mari asumsikan model `Author`memiliki relasi `contacts`. Kita bisa meng- eager-load kedua relasi ini dari model Book dengan cara seperti ini:

```php
$books = Book::with(['author', 'author.contacts'])->get();
```

Jika Anda sering meng- eager-load model yang sama, Anda bisa menggunakan properti `$eagerloads` agar Anda tidak permu mengulang - ulang penulisan.

```php
class Book extends Model
{
	public static $eagerloads = ['author'];


	public function author()
	{
		return $this->belongsTo('Author');
	}

}
```

Properti `$eagerloads` meminta argumen yang sama dengan yang diminta method `with()`. Jadi, kode berikut akan memberikan hasil yang sama.

```php
$books = Book::all();

foreach ($books as $book) {
	echo $book->author->name;
}
```

> [!NOTE]
> Method `with()` memiliki prioritas lebih tinggi dari property `$eagerloads` sehingga ketika Anda memanggil `with()`, array dengan key yang sama di `$egerloads` akan ditimpa.

## Constraint Pada Eager Loads

Saat melakukan eager-load, terkadang Anda juga ingin menentukan kondisi sebelum operasi eager-load itu dijalankan. Mudah saja. Begini caranya:

```php
$users = User::with(['posts' => function ($query) {

	$query->where('title', 'like', '%perahu%');

}])->get();
```

Pada contoh diatas, kita melakukan eager-load posts untuk user, tetapi hanya akan dijalankan jika kolom posts.title mengandung kata "perahu".

## Getter & Setter

Setter memungkinkan Anda menangani pengisian atribut dengan metode khusus. Buat setter dengan menambahkan "set" sebagai awalan nama atribut yang diinginkan.

```php
// use Hash;

public function setPassword($password)
{
	$this->setAttribute('password', Hash::make($password));
}
```

Setelah itu, Anda dapat memanggil method setter sebagai sebuah variabel menggunakan nama method tanpa awalan `set`.

```php
// 'setPassword' menjadi 'password'
$this->password = Hash::make($userPass);
```

Getters juga sama. Mereka dapat digunakan untuk memodifikasi atribut sebelum di-return. Buat getter dengan menambahkan **get** sebagai awalan nama atribut yang diinginkan.

```php
public function getPublishedDate()
{
	return date('M j, Y', $this->getAttribute('published_at'));
}
```

Dan seperti yang bisa Anda tebak, getter juga bisa dipanggil sebagai sebuah variabel:

```php
echo $this->published_date;
```

## Mass-Assignment

Mass-assignment adalah praktik untuk mengoper array asosiatif ke method di model Anda yang kemudian mengisi atribut model dengan nilai-nilai dari array tersebut. Mass-Assignment dapat dilakukan dengan mengoper array ke konstruktor model:

```php
$user = new User([
	'username' => 'Budi Purnomo',
	'password' => Hash::make('budi123')
]);

$user->save();
```

Atau, Anda juga dapat melakukannya menggunakan method `fill()`.

```php
$user = new User;

$user->fill([
	'username' => 'Budi Purnomo',
	'password' => Hash::make('budi123')
]);

$user->save();
```

Secara default, **semua** key / value atribut akan tersimpan selama mass-assignment. Namun, dimungkinkan untuk membuat whitelist atribut yang akan disimpan. Jika ada definisi whitelist di model Anda, maka hanya atribut - atribut yang berada dalam whitelist tersebutlah yang akan disimpan selama mass-assignment.

Untuk melakukan whitelisting ini, silahkan tambahkan property static `$fillable` berisi nama - nama kolom yang ingin Anda whitelist.

```php
public static $fillable = ['email', 'password', 'name'];
```

Sebagai alternatif, Anda juga bisa mmenggunakan method `fillable()`:

```php
User::fillable(['email', 'password', 'name']);
```

> [!DANGER]
> Harap lakukan validasi secara cermat saat mass-assignment menggunakan data inputan user.
> Keteledoran dapat menyebabkan celah keamanan yang serius.

## Mengubah Model Menjadi Array

Saat membuat JSON API, Anda pasti akan sering mengkonversi model menjadi array agar mudah diserialisasi. Tentu di Hexazor, Anda juga bisa dengan mudah melakukannya.

#### Mengubah satu buah model menjadi array:

```php
return json_encode($user->toArray());
```

Method `toArray()` akan secara otomatis mwngambil **seluruh atribut** pada model Anda, termasuk juga relasinya.

Terkadang, Anda tidak ingin menampilkan satu atau beberapa atribut dari model Anda, seperti attribut password. Untuk melakukan ini, tambahkan properti `$hidden` ke model Anda:

#### Menyembunyikan atribut dari array:

```php
class User extends Model
{
	public static $hidden = ['password'];

	//
}
```

## Menghapus Model

Karena seluruh Model meng-extends kelas _System/Database/ORM/Model_, yangmana kelas tersebut juga meng-extends kelas Query Builder, menghapus model merupakan hal yang mudah dilakukan:

```php
$author->delete();
```

Tetapi, patut diperhatikan bahwa ini tidak akan menghapus model yang berelasi (contohnya, seluruh data Author yang berelasi dengan Book akan tetap ada), kecuali jika Anda telah mengkonfigurasi [foreign keys](database/schema-builder.md#foreign-key) dan memberi perintah _cascade delete_.
