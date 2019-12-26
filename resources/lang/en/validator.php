<?php

defined('DS') or exit('No direct script access allowed.');

return [
    'accepted'   => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after'      => 'The :attribute must be a date after :date.',
    'alpha'      => 'The :attribute may only contain letters.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'  => 'The :attribute may only contain letters and numbers.',
    'array'      => 'The :attribute must have selected elements.',
    'before'     => 'The :attribute must be a date before :date.',
    'between'    => [
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

    'custom' => [
        // 'key' => 'value',
    ],

    'attributes' => [
    ],
];
