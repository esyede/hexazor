# Struktur Folder

Struktur folder default Hexazor dimaksudkan untuk memberikan titik permulaan yang baik untuk aplikasi Anda. Penerapan struktur folder ini dimaksudkan agar aplikasi Anda tertata rapi dan teratur.

Secara default, struktur folder dalam aplikasi Anda akan terlihat seperti berikut ini:

```
├───app
│   ├───Configs            // berisi file - file konfigurasi
│   ├───Controllers        // berisi kelas - kelas kontroller
│   ├───Helpers            // berisi file - file helper
│   ├───Languages          // berisi file - file bahasa
│   ├───Listeners          // berisi kelas - kelas listener (dibuat oleh konsol)
│   ├───Middlewares        // berisi kelas - kelas middleware (dibuat oleh konsol)
│   ├───Models             // berisi kelas - kelas model
│   └───Views              // berisi file - file view
│
├───storage
│   ├───cache              // berisi file cache umum dan cache curl
│   ├───databases
│   │    ├───migrations    // berisi file migrasi database (dibuat oleh konsol)
│   │    ├───sqlite        // berisi file database sqlite
│   │    └───seeders       // berisi file seeder untuk database (dibuat oleh konsol)
│   ├───logs               // berisi file log umum dan file error dari debugger
│   ├───uploads            // berisi file hasil upload
│   └───views              // berisi file cache hasil rendering view
│
└───system
    ├───Console            // berisi file - file aplikasi konsol
    ├───Core               // berisi file - file inti untuk startup framework
    ├───Database           // berisi kelas - kelas database
    ├───Debugger           // berisi kelas - kelas debugger
    ├───Facades            // berisi kelas - kelas facades
    ├───Languages          // berisi file - file bahasa bawaan framework
    ├───Libraries          // berisi kelas - kelas library bawaan framework
    ├───Loader             // berisi kelas - kelas autoloader
    └───Support            // berisi kelas - kelas dukungan
```
