# Model

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

Misalnya, mungkin Anda sedang membuat mesin blog. Anda mungkin ingin memiliki model "Post". User mungkin ingin mengomentari post sehingga Anda juga akan memiliki model "Comment". Jika user akan berkomentar maka kita juga akan membutuhkan model "User". Sudah dapat idenya?

## Aturan Penulisan Model

Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequuntur ad, odio architecto ipsa debitis eveniet totam itaque reprehenderit officia tenetur, odit corrupti fugiat accusamus quisquam? Cumque, odio, quam! Dolore, minus.

## Membuat Model

Sekarang, mari kita coba membuat model pertama kita:

```php
namespace App\Models;
defined('DS') or exit('No direct script access allowed.');

use System\Database\ORM\Model;


class User extends Model
{
	//
}
```

Seperti yang dapat Anda lihat, secara default kelas model meng-extends kelas `System\Database\ORM\Model`, hal ini memberi banyak keuntungan untuk Anda.

> [!TIP]
> Pembahasan detail mengenai kelas ini dapat Anda temukan di halaman [ORM Model](/database/orm-model.md#orm-model)

## Meletakkan Model Dalam Subfolder

Lorem ipsum dolor sit amet, consectetur adipisicing elit. Reprehenderit asperiores itaque, voluptates iste autem laudantium, nemo eaque ipsum dolor sequi facere, saepe expedita. Quis esse labore distinctio odio nemo nisi!
