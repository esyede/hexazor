<?php

defined('DS') or exit('No direct script access allowed.');

return [
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

    'custom' => [
        // 'key' => 'value',
    ],

    'attributes' => [
    ],
];
