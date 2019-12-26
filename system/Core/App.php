<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use System\Debugger\Debugger;
use System\Facades\Facade;
use System\Libraries\Http\Response;
use System\Support\Str;

class App
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->reconfigureTimezone();
        $this->ensureAppKeyIsProvided();
        $this->reconfigureDebugger();

        $this->initApplications();
        $this->initComposer();

        Import::file('routes/web.php');
        Router::run();
    }

    /**
     * Inisialisasi (muat) seluruh facade yang telah didefinisikan.
     */
    private function initApplications()
    {
        $services = Import::config('services');
        Facade::setFacadeApplication($services);

        foreach ($services['facades'] as $key => $value) {
            // gunakan bantuan class_alias untuk autoload facades (parameter ke-3)
            // ref: https://www.php.net/manual/en/function.class-alias.php
            class_alias($value, $key, true);
        }
    }

    /**
     * Include composer autoloader (jika ada).
     *
     * @return bool
     */
    private function initComposer()
    {
        $path = root_path('vendor/autoload.php');
        if (is_file($path) && true == Config::get('app.composer_autoload')) {
            Import::file($path);
        }
    }

    /**
     * Setel ulang debugger: terapkan config user.
     */
    private function reconfigureDebugger()
    {
        $config = Config::get('debugger');
        Debugger::$strictMode = boolval($config['strict_mode']);
        Debugger::$scream = boolval($config['scream']);

        Debugger::$logSeverity = 0;
        Debugger::$onFatalError = [];

        Debugger::$showBar = boolval($config['show_debugbar']);
        Debugger::$showLocation = boolval($config['show_location']);
        Debugger::$maxDepth = intval($config['max_depth']);
        Debugger::$maxLen = intval($config['max_length']);
        Debugger::$email = strval($config['email']);
    }

    /**
     * Setel ulang timezone: terapkan config user.
     *
     * @return void
     */
    private function reconfigureTimezone()
    {
        $timezone = Config::get('app.default_timezone', 'UTC');
        date_default_timezone_set($timezone);
    }

    /**
     * Pastikan application key sudah diisi.
     */
    private function ensureAppKeyIsProvided()
    {
        $appkey = Config::get('app.application_key');
        if (blank($appkey) || mb_strlen($appkey) < 32) {
            $appkey = Str::random(32);
            $path = ROOT_PATH.'config'.DS.'app.php';
            $pattern = "/('application_key')\h*=>\h*\'\s?\'?.*/i";
            $subject = file_get_contents($path);
            $replacement = "'application_key' => '{$appkey}',";

            $success = true;
            if (false !== preg_match($pattern, $subject)) {
                $subject = preg_replace($pattern, $replacement, $subject);
                if (false === @file_put_contents($path, $subject, LOCK_EX)) {
                    $success = false;
                }
            } else {
                $success = false;
            }

            if (!$success) {
                $body = 'The [application_key] config not set correctly, ';
                $body .= 'It needs to be at least 32 characters long.<br>';
                $body .= 'Use this console command to auto-generate it:<br>';
                $body .= '<code>php hexazor key:generate</code><br><br>';
                $body .= 'Or you can manually copy-paste this randomly ';
                $body .= 'generated key into your [application_key] config:<br>';
                $body .= '<code>'.Str::random(32).'</code>';

                $response = new Response();
                $response->status(500)->body($body)->send();
                exit();
            }
        }
    }
}
