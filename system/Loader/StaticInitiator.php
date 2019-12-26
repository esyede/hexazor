<?php

namespace System\Loader;

defined('DS') or exit('No direct script access allowed.');

use Closure;

class StaticInitiator
{
    /**
     * Inisialisasi mekanisme loader statis.
     *
     * @param \System\Loader\ClassLoader $loader
     */
    public static function init(ClassLoader $loader)
    {
        return Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = [
                'S' => ['System\\' => 7],
                'A' => ['App\\' => 4],
            ];

            $loader->prefixDirsPsr4 = [
                'System\\' => [ROOT_PATH.'system'],
                'App\\' => [ROOT_PATH.'app'],
            ];
        }, null, __NAMESPACE__.'\\ClassLoader');
    }
}
