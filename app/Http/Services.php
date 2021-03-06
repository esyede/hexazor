<?php

namespace App\Http;

defined('DS') or exit('No direct script access allowed.');

class Services
{
    /**
     * Application facades.
     *
     * @var array
     */
    public static $facades = [
        'Auth'      => 'System\Facades\Auth',
        'Cache'     => 'System\Facades\Cache',
        'Cookie'    => 'System\Facades\Cookie',
        'Console'   => 'System\Facades\Console',
        'Crypt'     => 'System\Facades\Crypt',
        'Curl'      => 'System\Facades\Curl',
        'Date'      => 'System\Facades\Date',
        'DB'        => 'System\Facades\DB',
        'Event'     => 'System\Facades\Event',
        'Form'      => 'System\Facades\Form',
        'Hash'      => 'System\Facades\Hash',
        'Html'      => 'System\Facades\Html',
        'Image'     => 'System\Facades\Image',
        'Jwt'       => 'System\Facades\Jwt',
        'Log'       => 'System\Facades\Log',
        'Mail'      => 'System\Facades\Mail',
        'Request'   => 'System\Facades\Request',
        'Response'  => 'System\Facades\Response',
        'Restful'   => 'System\Facades\Restful',
        'Session'   => 'System\Facades\Session',
        'Storage'   => 'System\Facades\Storage',
        'Validator' => 'System\Facades\Validator',
        'View'      => 'System\Facades\View',

        // ...
    ];

    /**
     * Application service providers.
     *
     * @var array
     */
    public static $providers = [
        'Auth'      => 'System\Libraries\Auth\Auth',
        'Cache'     => 'System\Libraries\Cache\Cache',
        'Cookie'    => 'System\Libraries\Cookie\Cookie',
        'Console'   => 'System\Console\Console',
        'Crypt'     => 'System\Libraries\Crypt\Crypt',
        'Curl'      => 'System\Libraries\Http\Curl',
        'Date'      => 'System\Libraries\Date\Date',
        'DB'        => 'System\Database\Database',
        'Event'     => 'System\Libraries\Event\Event',
        'Form'      => 'System\Libraries\Html\Form',
        'Hash'      => 'System\Libraries\Hash\Hash',
        'Html'      => 'System\Libraries\Html\Html',
        'Image'     => 'System\Libraries\Image\Image',
        'Jwt'       => 'System\Libraries\Http\Jwt',
        'Log'       => 'System\Libraries\Log\Log',
        'Mail'      => 'System\Libraries\Mail\Mail',
        'Request'   => 'System\Libraries\Http\Request',
        'Response'  => 'System\Libraries\Http\Response',
        'Restful'   => 'System\Libraries\Http\Restful',
        'Session'   => 'System\Libraries\Session\Session',
        'Storage'   => 'System\Libraries\Storage\Storage',
        'Validator' => 'System\Libraries\Http\Validator',
        'View'      => 'System\Libraries\View\View',

        // ...
    ];
}
