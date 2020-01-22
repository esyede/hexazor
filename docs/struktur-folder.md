# Struktur Folder

Struktur folder default Hexazor dimaksudkan untuk memberikan titik permulaan yang baik untuk aplikasi Anda. Penerapan struktur folder ini dimaksudkan agar aplikasi Anda tertata rapi dan teratur.

Secara default, struktur folder dalam aplikasi Anda akan terlihat seperti berikut ini:

```
├───app
│   ├───Console
│   │    └───Commands
│   └───Http
│   │    ├───Controllers   // tempat controller anda disimpan
│   │    ├───Middleware    // tempat middleware anda disimpan
│   │    └───Listeners     // tempat listener anda disimpan
│   │    ├───Kernel.php    // tempat middleware dan post-boot didefinisikan
│   │    └───Services.php  // daftar definisi provider dan facades
│   └───User.php           // model user, seluruh model anda juga akan berada disini
│
├───config                 // berisi file - file konfigurasi framework
├───database
│   ├───migrations         // berisi file - file migrasi database
│   ├───seeds              // berisi file seeder database
│   └───sqlite             // berisi file database untuk driver sqlite
├───resources
│   ├───lang               // berisi daftar language files untuk aplikasi
│   └───views              // letakkan file view anda disini
├───routes                 // berisi file - file definisi routes
├───storage                // storage internal sistem dan aplikasi anda
└───system
│   ├───Console            // berisi file - file aplikasi konsol
│   ├───Core               // berisi file - file inti untuk startup framework
│   ├───Database           // berisi kelas - kelas database
│   ├───Debugger           // berisi kelas - kelas debugger
│   ├───Facades            // berisi kelas - kelas facades
│   ├───Libraries          // berisi kelas - kelas library bawaan framework
│   ├───Loader             // berisi kelas - kelas autoloader
│   └───Support            // berisi kelas - kelas dukungan
│
├───bootstrap.php          // bootstrap script framework
├───constants.php          // konstanta framework
├───hexazor                // CLI tool untuk otomasi tugas
└───index.php              // entry point untuk aplikasi anda
```