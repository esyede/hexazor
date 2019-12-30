<?php

namespace App\Http\Middleware;

defined('DS') or exit('No direct script access allowed.');

class Authenticate
{
    /**
     * Tangani request yang datang.
     *
     * @return mixed
     */
    public function handle()
    {
        return true;
    }
}
