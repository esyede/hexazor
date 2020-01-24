<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Support\Str;

class KeyGenerate extends Command
{
    protected $signature = 'key:generate';
    protected $description = 'Set the application key';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        $appkey = Str::random(32);
        $path = base_path('config/app.php');
        $pattern = "/('application_key')\h*=>\h*\'\s?\'?.*/i";
        $subject = file_get_contents($path);
        $replacement = "'application_key' => '{$appkey}',";

        if (false !== preg_match($pattern, $subject)) {
            $subject = preg_replace($pattern, $replacement, $subject);
            if (false === @file_put_contents($path, $subject, LOCK_EX)) {
                $this->writeline('Failed to set application key.');
                exit();
            } else {
                $this->writeline("Application key set successfully.");
            }
        } else {
            $this->writeline('Failed to set application key.');
            exit();
        }
    }
}
