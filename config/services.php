<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Facades
    |--------------------------------------------------------------------------
    |
    | Facades.
    |
    */

    'facades' => [
        'Auth' => 'System\Facades\Auth',
        'Cache' => 'System\Facades\Cache',
        'Cookie' => 'System\Facades\Cookie',
        'Config' => 'System\Facades\Config',
        'Console' => 'System\Facades\Console',
        'Crypt' => 'System\Facades\Crypt',
        'Curl' => 'System\Facades\Curl',
        'Date' => 'System\Facades\Date',
        'DB' => 'System\Facades\DB',
        'Event' => 'System\Facades\Event',
        'Form' => 'System\Facades\Form',
        'Hash' => 'System\Facades\Hash',
        'Html' => 'System\Facades\Html',
        'Import' => 'System\Facades\Import',
        'Image' => 'System\Facades\Image',
        'Jwt' => 'System\Facades\Jwt',
        'Log' => 'System\Facades\Log',
        'Mail' => 'System\Facades\Mail',
        'Request' => 'System\Facades\Request',
        'Response' => 'System\Facades\Response',
        'Restful' => 'System\Facades\Restful',
        'Session' => 'System\Facades\Session',
        'Storage' => 'System\Facades\Storage',
        'Validator' => 'System\Facades\Validator',
        'View' => 'System\Facades\View',

        // User's Facades goes here..
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Provider
    |--------------------------------------------------------------------------
    |
    | Services.
    |
    */

    'providers' => [
        'Auth' => 'System\Libraries\Auth\Auth',
        'Cache' => 'System\Libraries\Cache\Cache',
        'Config' => 'System\Core\Config',
        'Cookie' => 'System\Libraries\Cookie\Cookie',
        'Console' => 'System\Console\Console',
        'Crypt' => 'System\Libraries\Crypt\Crypt',
        'Curl' => 'System\Libraries\Http\Curl',
        'Date' => 'System\Libraries\Date\Date',
        'DB' => 'System\Database\Database',
        'Event' => 'System\Libraries\Event\Event',
        'Form' => 'System\Libraries\Html\Form',
        'Hash' => 'System\Libraries\Hash\Hash',
        'Html' => 'System\Libraries\Html\Html',
        'Image' => 'System\Libraries\Image\Image',
        'Import' => 'System\Core\Import',
        'Jwt' => 'System\Libraries\Http\Jwt',
        'Log' => 'System\Libraries\Log\Log',
        'Mail' => 'System\Libraries\Mail\Mail',
        'Request' => 'System\Libraries\Http\Request',
        'Response' => 'System\Libraries\Http\Response',
        'Restful' => 'System\Libraries\Http\Restful',
        'Session' => 'System\Libraries\Session\Session',
        'Storage' => 'System\Libraries\Storage\Storage',
        'Validator' => 'System\Libraries\Http\Validator',
        'View' => 'System\Libraries\View\View',

        // User's Libraries goes here..
    ],
];
