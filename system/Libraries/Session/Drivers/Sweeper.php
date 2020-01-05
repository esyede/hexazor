<?php

namespace System\Libraries\Session\Drivers;

defined('DS') or exit('No direct script access allowed.');

interface Sweeper
{
    /**
     * Hapus seluruh session yang telah kadaluwarsa.
     *
     * @param  int $expiration
     *
     * @return void
     */
    public function sweep($expiration);
}
