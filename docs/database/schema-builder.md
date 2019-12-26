# Schema Builder

## Daftar Isi

-   [Pengetahuan Dasar](#pengetahuan-dasar)
-   [Membuat & Menghapus Tabel](#membuat-amp-menghapus-tabel)
    -   [Menghapus Tabel Dari Database](#menghapus-tabel-dari-database)
    -   [Menghapus Tabel Dari Sebuah Koneksi](#menghapus-tabel-dari-sebuah-koneksi)
-   [Menambahkan Kolom](#menambahkan-kolom)
-   [Menghapus Kolom](#menghapus-kolom)
-   [Menambahkan Index](#menambahkan-index)
-   [Menghapus Index](#menghapus-index)
-   [Foreign Key](#foreign-key)

# Pengetahuan Dasar

Schema Builder menyediakan sekumpulan method untuk membuat, memodifikasi dan menghapus tabel di database Anda. Dengan sintaks yang sederhana, Anda dapat mengelola skema tabel tanpa harus susah payah menulis SQL mentah yang rentan menimbulkan kesalahan.

> [!TIP]
> Baca juga fitur penanganan migrasi di halaman [Migrasi Database](/database/migrasi.md#migrasi)

# Membuat & Menghapus Tabel

Seperti yang sudah dijelaskan, kelas `Schema` (_system/Database/Schema.php_) digunakan untuk operasi membuat, memodifikasi dan menghapus tabel. Mari kita langsung lihat contohnya.

### Membuat Tabel Sederhana

```php
use System\Database\Schema;

Schema::create('users', function ($table)
{
    $table->increments('id');
});
```

Mari kita bahas contoh diatas:

Method `create()` memberi tahu Schema builder bahwa ini merupakan tabel baru, maka tabelnya harus dibuat.

Di parameter ke-dua, kita mengoper sebuah Closure yang menerima instance kelas `Table` (_system/Database/Schema/Table.php_). Menggunakan objek kelas Table tersebut, kita bisa memodifikasi ataupun menghapus kolom dan index didalam tabel.

### Menghapus Tabel Dari Database:

```php
Schema::drop('users');
```

### Menghapus Tabel Dari Sebuah Koneksi:

```php
Schema::drop('users', 'nama_koneksi');
```

Terkadang Anda mungkin ingin menentukan koneksi database mana yang harus digunakan si Schema untuk menjalankan operasinya. Begini caranya:

```php
Schema::create('users', function ($table)
{
    $table->on('nama_koneksi');
});
```

# Menambahkan Kolom

Kelas Table menyediakan beberapa perintah agar Anda dapat menambahkan kolom tanpa perlu susah - payah menggunakan SQL mentah. Mari kita lihat perintah - perintahya:

| Perintah                           | Deskripsi                                                  |
| ---------------------------------- | ---------------------------------------------------------- |
| `$table->increments('id');`        | Tambahkan tabel auto-increment ke tabel                    |
| `$table->string('email');`         | Tambahkan kolom VARCHAR                                    |
| `$table->string('name', 100);`     | Tambahkan kolom VARCHAR dengan panjang maksimal            |
| `$table->integer('votes');`        | Tambahkan kolom INTEGER                                    |
| `$table->float('amount');`         | Tambahkan kolom FLOAT                                      |
| `$table->decimal('amount', 5, 2);` | Tambahkan kolom DECIMAL dengan presisi dan skala           |
| `$table->boolean('confirmed');`    | Tambahkan kolom BOOLEAN                                    |
| `$table->date('created_at');`      | Tambahkan kolom DATE                                       |
| `$table->timestamp('added_on');`   | Tambahkan kolom TIMESTAMP                                  |
| `$table->timestamps();`            | Tambahkan kolom DATE bernama `created_at` dan `updated_at` |
| `$table->text('description');`     | Tambahkan kolom TEXT                                       |
| `$table->blob('data');`            | Tambahkan kolom BLOB                                       |
| `->nullable()`                     | Ijinkan kolom diisi dengan NULL                            |
| `->default($value)`                | Beri default value untuk kolom                             |
| `->unsigned()`                     | Tambahkan UNSIGNED ke sebuah kolom INTEGER                 |

> [!WARNING]
> Di semua RDBMS, setiap kolom yang bertipe **boolean** akan selalu diubah secara otomatis menjadi **Small Integer**.

**Berikut adalah contoh cara membuat tabel dan menambahkan kolom:**

```php
Schema::table('users', function ($table)
{
    $table->create();

    $table->increments('id');
    $table->string('username');
    $table->string('email');
    $table->string('phone')->nullable();
    $table->text('about');
    $table->timestamps();
});
```

### Menghapus Kolom

Untuk menghapus sebuah kolom, gunakan cara ini:

```php
$table->dropColumn('username');
```

Sedangkan untuk menghapus beberapa kolom sekaligus, gunakan cara ini:

```php
$table->dropColumn(['username', 'email', 'phone']);
```

## Menambahkan Index

Schema mendukung beberapa jenis index. Ada 2 cara untuk menambahkan index. Setiap jenis index mempunyai methodnya sendiri - sendiri. Akan tetapi, Anda juga dapat mendefinisikan index secara langsung dengan menyambungkan method indexing dengan method untuk penambahan kolom. Mari kita lihat:

```php
$table->string('email')->unique();
```

Namun jika Anda lebih suka menambahkan index secara terpisah, Anda bisa menuliskannya dengan cara ini:

| Perintah                               | Deskripsi                   |
| -------------------------------------- | --------------------------- |
| `$table->primary('id');`               | Menambahkan primary key     |
| `$table->primary(['fname', 'lname']);` | Menambahkan composite key   |
| `$table->unique('email');`             | Menambahkan unique index    |
| `$table->fulltext('description');`     | Menambahkan full-text index |
| `$table->index('state');`              | Menambahkan index sandar    |

## Menghapus Index

Untuk menghapus index, Anda hanya perlu menyebutkan nama indexnya saja. Hexazor memberikan nama yang mudah diingat untuk semua index. Cukup gabungkan **nama tabel** dan **nama kolom dalam index**, lalu tambahkan _tipe index_ di bagian akhir. Mari kita lihat beberapa contohnya:

| Perintah                                                | Deskripsi                                  |
| ------------------------------------------------------- | ------------------------------------------ |
| `$table->dropPrimary('users_id_primary');`              | Hapus primary key dari tabel "users"       |
| `$table->dropUnique('users_email_unique');`             | Hapus unique index dari tabel "users"      |
| `$table->dropFulltext('profile_description_fulltext');` | Hapus full-text index dari tabel "profile" |
| `$table->dropIndex('geo_state_index');`                 | Hapus index standar dari tabel "geo"       |

> [!TIP]
> Ingat! tata cara penamaan index: nama tabel + kolom index + tipe index, gabungkan dengan underscore.

## Foreign Key

Anda juga bisa menambahkan foreign key pada tabel menggunakan Schema. Contohnya, anggap Anda mempunyai kolom `user_id` pada tabel `posts`, dan tabel tersebut mer-reference kolom `id` pada tabel `users`. Maka, sintaksnya jadi seperti ini:

```php
$table->foreign('user_id')->references('id')->on('users');
```

Anda juga boleh menambahkan opsi _on delete_ dan _on update_ pada foreign key:

```php
$table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

$table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
```

Untuk menghapus foreign key pun juga sangat mudah. Secara default, aturan penamaan foreign key ini mengikuti [aturan yang sama](#menghapus-index) seperti index - index lain yang dibuat menggunakan Schema. Contohnya seperti ini:

```php
$table->dropForeign('posts_user_id_foreign');
```

> [!DANGER]
> Patut diingat bahwa kolom yang di-reference dalam foreign key hampir pasti merupakan auto-increment, maka secara otomatis tipenya adalah **Unsigned Integer**. Harap pastikan untuk membuat kolom foreign key dengan method `unsigned()` karena kedua kolomnya harus mempunyai tipe yang sama, dan juga, engine di kedua tabel harus di-set ke `InnoDB`, dan tabel yang di-reference harus dibuat **SEBELUM** si tabel foreign key.

```php
$table->engine = 'InnoDB';

$table->integer('user_id')->unsigned();
```
