# Model


## Daftar Isi
-   [Pengetahuan Dasar](#pengetahuan-dasar)
-   [Aturan Penulisan Model](#aturan-penulisan-model)
-   [Membuat Model](#membuat-model)
-   [Meletakkan Model Dalam Subfolder](#meletakkan-model-dalam-subfolder)


## Pengetahuan Dasar

Model adalah jantung dari aplikasi Anda. Logika aplikasi Anda (controller / route) dan view (html) hanyalah media yang digunakan user untuk berinteraksi dengan model Anda. Jenis logika paling umum yang terdapat dalam suatu model adalah [Business Logic](http://en.wikipedia.org/wiki/Business_logic).

Beberapa contoh fungsi yang akan ada dalam suatu model antara lain:

-   Interaksi Database
-   File I / O
-   Interaksi dengan Web Service

Misalnya, mungkin Anda sedang membuat mesin blog. Anda mungkin ingin memiliki model "Post". User mungkin ingin mengomentari postingan, sehingga Anda juga akan memiliki model "Comment". Jika user akan berkomentar maka kita juga akan membutuhkan model "User". Sudah dapat idenya?


## Aturan Penulisan Model

Di Hexazor, diterapkan beberapa aturan untuk penulisan model. Hal ini bertujuan agar model yang Anda buat tertata rapih dan teratur. Aturan penulisannya seperti berikut:

  - Model disimpan di folder `app/`
  - Setelah deklarasi namespace, wajib menambahkan kode `defined('DS') or exit(...);`
  - Untuk penamaan model disarankan mengikuti aturan plural-singular, yaitu jika nama tabel di database berbentuk plural (misalnya `users`) maka nama modelnya menjadi `User`.


## Membuat Model

Sekarang, mari kita coba membuat model pertama kita:

```php
namespace App;

defined('DS') or exit('No direct script access allowed.');

use System\Database\ORM\Model;


class User extends Model
{
	//
}
```

Seperti yang dapat Anda lihat, secara default seluruh model akan meng-extends kelas `System\Database\ORM\Model`, hal ini memberi banyak keuntungan untuk Anda.

> [!TIP]
> Pembahasan detail mengenai kelas ini dapat Anda temukan di halaman [ORM Model](/database/orm-model.md#orm-model)


## Meletakkan Model Dalam Subfolder

Anda boleh menaruh model kedalam subfolder, ini berguna ketika jumlah model Anda sudah terlalu banyak dan perlu di organisasi ulang.

```php
// disimpan di: app/Purchasing/Payment.php

namespace App\Purchasing;

defined('DS') or exit('No direct script access allowed.');

use System\Database\ORM\Model;


class Payment extends Model
{
	//
}
```