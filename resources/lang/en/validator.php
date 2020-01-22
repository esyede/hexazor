<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Validator
    |--------------------------------------------------------------------------
    |
    | Lokalisasi bahasa inggris pesan error default untuk library Validator.
    | Beberapa rules dibawah ini mengandung beberapa versi, seperti
    | rule size (min, max, between), versi - versi ini digunakan untuk
    | menangani tipe input yang berbeda, seperti string dan file.
    |
    | List pesan error ini juga boleh diubah agar sesuai dengan kebutuhan
    | aplikasi Anda. Pesan error untuk custom validator juga dapat Anda
    | tambahkan disini.
    |
    */

    'accepted'   => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after'      => 'The :attribute must be a date after :date.',
    'alpha'      => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'  => 'The :attribute may only contain letters and numbers.',
    'array'      => 'The :attribute must have selected elements.',
    'before'     => 'The :attribute must be a date before :date.',

    'between' => [
        'numeric' => 'The :attribute must be between :min - :max.',
        'file'    => 'The :attribute must be between :min - :max kilobytes.',
        'string'  => 'The :attribute must be between :min - :max characters.',
    ],

    'confirmed'     => 'The :attribute confirmation does not match.',
    'count'         => 'The :attribute must have exactly :count selected elements.',
    'count_between' => 'The :attribute must have between :min and :max selected elements.',
    'count_max'     => 'The :attribute must have less than :max selected elements.',
    'count_min'     => 'The :attribute must have at least :min selected elements.',
    'date_format'   => 'The :attribute must have a valid date format.',
    'different'     => 'The :attribute and :other must be different.',
    'email'         => 'The :attribute format is invalid.',
    'exists'        => 'The selected :attribute is invalid.',
    'image'         => 'The :attribute must be an image.',
    'in'            => 'The selected :attribute is invalid.',
    'integer'       => 'The :attribute must be an integer.',
    'ip'            => 'The :attribute must be a valid IP address.',
    'regex'         => 'The :attribute format is invalid.',

    'max' => [
        'numeric' => 'The :attribute must be less than :max.',
        'file'    => 'The :attribute must be less than :max kilobytes.',
        'string'  => 'The :attribute must be less than :max characters.',
    ],

    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
    ],

    'not_in'        => 'The selected :attribute is invalid.',
    'numeric'       => 'The :attribute must be a number.',
    'required'      => 'The :attribute field is required.',
    'required_with' => 'The :attribute field is required with :field',
    'same'          => 'The :attribute and :other must match.',

    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobyte.',
        'string'  => 'The :attribute must be :size characters.',
    ],

    'unique' => 'The :attribute has already been taken.',
    'url'    => 'The :attribute format is invalid.',

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
