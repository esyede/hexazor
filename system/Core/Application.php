<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use App\Http\Kernel as AppHttpKernel;
use App\Http\Services as AppServices;
use System\Debugger\Debugger;
use System\Facades\Facade;
use System\Libraries\Http\Response;
use System\Support\Str;

class Application
{
    const PACKAGE = 'Hexazor';
    const VERSION = '0.9.3';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->reconfigureTimezone();
        $this->ensureAppKeyIsProvided();
        $this->reconfigureDebugger();

        $this->registerDefinedFacades();
        $this->includeComposerAutoloaderIfExists();
        $this->callAppHttpKernelBoot();

        $this->dispatchRouteDefinitions();
    }

    /**
     * Inisialisasi (muat) seluruh facade yang telah didefinisikan.
     */
    private function registerDefinedFacades()
    {
        $facades = AppServices::$facades;
        $providers = AppServices::$providers;

        Facade::setFacadeApplication(compact('facades', 'providers'));

        foreach ($facades as $facadeName => $facadeClass) {
            // gunakan bantuan class_alias untuk autoload facades (parameter ke-3)
            // ref: https://www.php.net/manual/en/function.class-alias.php
            class_alias($facadeClass, $facadeName, true);
        }
    }

    /**
     * Include composer autoloader (jika ada).
     *
     * @return bool
     */
    private function includeComposerAutoloaderIfExists()
    {
        $path = base_path('vendor/autoload.php');
        if (is_file($path) && true === Config::get('app.composer_autoload', true)) {
            $this->protectVendorDirWithHtaccess();
            require_once $path;
        }
    }

    /**
     * Proteksiksi direktori 'vendor/' milik composer dengan htaccess.
     *
     * @return bool
     */
    private function protectVendorDirWithHtaccess()
    {
        $path = base_path('vendor/.htaccess');

        if (!is_file($path)) {
            return (false !== file_put_contents($path, 'deny from all', LOCK_EX));
        }

        return true;
    }

    /**
     * Jalankan app kernel boot.
     *
     * @return bool
     */
    private function callAppHttpKernelBoot()
    {
        AppHttpKernel::boot();
    }

    /**
     * Eksekusi route yang telah didaftarkan user.
     *
     * @return bool
     */
    private function dispatchRouteDefinitions()
    {
        $this->getRouteDefinitions();
        Route::run();
    }

    /**
     * Ambil semua routes yang telah didefinisikan oleh user.
     *
     * @return mixed
     */
    private function getRouteDefinitions()
    {
        return require_once base_path('routes/web.php');
    }

    /**
     * Setel ulang debugger: terapkan config user.
     */
    private function reconfigureDebugger()
    {
        $config = Config::get('debugger');

        Debugger::$strictMode = boolval($config['strict_mode']);
        Debugger::$scream = boolval($config['scream']);

        // sengaja nggak dimunculin di config debugger
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
            $path = BASE_PATH.'config'.DS.'app.php';
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
