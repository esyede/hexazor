<?php

namespace System\Libraries\Auth\Drivers;

defined('DS') or exit('No direct script access allowed.');

use System\Facades\Cookie;
use System\Facades\Crypt;
use System\Facades\Session;
use System\Support\Str;

abstract class Driver
{
    public $user;
    public $token;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (Session::started()) {
            $this->token = Session::get($this->token());
        }

        if (is_null($this->token)) {
            $this->token = $this->recall();
        }
    }

    /**
     * Cek bahwa user belum login.
     * Method ini adaah kebalikan dari methhod 'check'.
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Cek bahwa user sudah login.
     *
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Ambil data user saat ini.
     *
     * @return mixed|null
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        return $this->user = $this->retrieve($this->token);
    }

    /**
     * Ambil user berdasarkan ID.
     *
     * @param int $id
     *
     * @return mixed
     */
    abstract public function retrieve($id);

    /**
     * Coba loginkan user.
     *
     * @param array $credentials
     *
     * @return bool
     */
    abstract public function attempt(array $credentials = []);

    /**
     * Login user dengan token (berupa user ID).
     *
     * @param string $token
     * @param bool   $remember
     *
     * @return bool
     */
    public function login($token, $remember = false)
    {
        $this->token = $token;
        $this->store($token);

        if ($remember) {
            $this->remember($token);
        }

        return true;
    }

    /**
     * Log out.
     */
    public function logout()
    {
        $this->user = null;
        Cookie::put($this->recaller(), null, -2000);
        Session::forget($this->token());
        $this->token = null;
    }

    /**
     * Simpan token user ke session.
     *
     * @param string $token
     */
    protected function store($token)
    {
        Session::put($this->token(), $token);
    }

    /**
     * Simpan session user ke cookie (untuk 'remember me').
     *
     * @param string $token
     */
    protected function remember($token)
    {
        $token = Crypt::encrypt($token.'|'.Str::random(40));
        Cookie::put($this->recaller(), $token, 157680000);
    }

    /**
     * Coba cari cookie 'remember me' milik user.
     *
     * @return string|null
     */
    protected function recall()
    {
        $cookie = Cookie::get($this->recaller());

        if (!is_null($cookie)) {
            return head(explode('|', Crypt::decrypt($cookie)));
        }
    }

    /**
     * Ambil nama key untuk cookie token.
     *
     * @return string
     */
    protected function token()
    {
        return 'auth_login';
    }

    /**
     * Ambil nama key untuk cookie 'remember me'.
     *
     * @return string
     */
    protected function recaller()
    {
        return 'auth_remember';
    }
}
