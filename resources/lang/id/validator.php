<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Validator
    |--------------------------------------------------------------------------
    |
    | Lokalisasi bahasa indonesia pesan error default untuk library Validator.
    | Beberapa rules dibawah ini mengandung beberapa versi, seperti
    | rule size (min, max, between), versi - versi ini digunakan untuk
    | menangani tipe input yang berbeda, seperti string dan file.
    |
    | List pesan error ini juga boleh diubah agar sesuai dengan kebutuhan
    | aplikasi Anda. Pesan error untuk custom validator juga dapat Anda
    | tambahkan disini.
    |
    */

    'accepted'   => 'Isian :attribute harus diterima.',
    'active_url' => 'Isian :attribute bukan URL yang valid.',
    'after'      => 'Isian :attribute harus tanggal setelah :date.',
    'alpha'      => 'Isian :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Isian :attribute hanya boleh berisi huruf, angka, dan strip.',
    'alpha_num'  => 'Isian :attribute hanya boleh berisi huruf dan angka.',
    'array'      => 'Isian :attribute harus memiliki elemen yang dipilih.',
    'before'     => 'Isian :attribute harus tanggal sebelum :date.',

    'between' => [
        'numeric' => 'Isian :attribute harus antara :min - :max.',
        'file'    => 'Isian :attribute harus antara :min - :max kilobytes.',
        'string'  => 'Isian :attribute harus antara  :min - :max karakter.',
    ],

    'confirmed'     => 'Konfirmasi :attribute tidak cocok.',
    'count'         => 'Isian :attribute harus memiliki tepat :count elemen.',
    'count_between' => 'Isian :attribute harus diantara :min dan :max elemen.',
    'count_max'     => 'Isian :attribute harus lebih kurang dari :max elemen.',
    'count_min'     => 'Isian :attribute harus paling sedikit :min elemen.',
    'different'     => 'Isian :attribute dan :other harus berbeda.',
    'email'         => 'Format isian :attribute tidak valid.',
    'exists'        => 'Isian :attribute yang dipilih tidak valid.',
    'image'         => ':attribute harus berupa gambar.',
    'in'            => 'Isian :attribute yang dipilih tidak valid.',
    'integer'       => 'Isian :attribute harus merupakan bilangan.',
    'ip'            => 'Isian :attribute harus alamat IP yang valid.',
    'regex'         => 'Format isian :attribute tidak valid.',

    'max' => [
        'numeric' => 'Isian :attribute harus kurang dari :max.',
        'file'    => 'Isian :attribute harus kurang dari :max kilobytes.',
        'string'  => 'Isian :attribute harus kurang dari :max karakter.',
    ],

    'min' => [
        'numeric' => 'Isian :attribute harus minimal :min.',
        'file'    => 'Isian :attribute harus minimal :min kilobytes.',
        'string'  => 'Isian :attribute harus minimal :min karakter.',
    ],

    'not_in'   => 'Isian :attribute yang dipilih tidak valid.',
    'numeric'  => 'Isian :attribute harus berupa angka.',
    'required' => 'Isian :attribute wajib diisi.',
    'same'     => 'Isian :attribute dan :other harus sama.',

    'size' => [
        'numeric' => 'Isian :attribute harus berukuran :size.',
        'file'    => 'Isian :attribute harus berukuran :size kilobyte.',
        'string'  => 'Isian :attribute harus berukuran :size karakter.',
    ],

    'unique' => 'Isian :attribute sudah ada sebelumnya.',
    'url'    => 'Format isian :attribute tidak valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validator Message
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan pesan validasi custom untuk atribut
    | menggunakan konvensi "attribute_rule" untuk penamaannya. Ini membantu
    | menjaga rule validasi custom Anda tetap bersih dan rapi.
    |
    | Jadi, misalkan Anda ingin menggunakan pesan validasi custom saat
    | memvalidasi bahwa atribut "email" harus unique, cukup tambahkan
    | "email_unique" ke array ini dengan pesan custom Anda.
    | Validator akan menangani sisanya!
    |
    */

    'custom' => [
        // 'attribute_rule' => 'Your custom message',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    |
    | Lokalaisasi berikut digunakan untuk mengganti place-holder ":attribute"
    | dengan sesuatu yang lebih ramah pembaca seperti "Alamat E-Mail" sebagai
    | pengganti dari "email". Pengguna Anda akan lebih terbantu dengan pesan
    | error yang lebih deskriptif.
    |
    | Kelas Validator akan secara otomatis mencoba mencari array ini untuk
    | mengganti place-holder ":attribute" dalam pesan error. Menarik kan?
    | Kami pikir Anda akan menyukainya.
    |
    */

    'attributes' => [
        //
    ],
];
