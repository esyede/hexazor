<?php

namespace System\Libraries\Auth\Drivers;

defined('DS') or exit('No direct script access allowed.');

use System\Core\Config;
use System\Facades\Hash;

class ModelDriver extends Driver
{
    /**
     * Ambil user saat ini.
     *
     * @param int   $id
     * @param mixed $token
     *
     * @return mixed|null
     */
    public function retrieve($token)
    {
        if (false !== filter_var($token, FILTER_VALIDATE_INT)) {
            return $this->model()->find($token);
        } elseif (is_object($token) && '\App\Models\User' == get_class($token)) {
            return $token;
        }
    }

    /**
     * Coba loginkan user.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function attempt(array $credentials = [])
    {
        $user = $this->model()->where(function ($query) use ($credentials) {
            $username = Config::get('auth.username', 'email');
            $password = Config::get('auth.password', 'password');
            $remember = Config::get('auth.remember', 'remember');

            $query->where($username, '=', $credentials[$username]);
            $columns = array_except($credentials, [$username, $password, $remember]);

            foreach ($columns as $column => $val) {
                $query->where($column, '=', $val);
            }
        })->first();

        $password = $credentials['password'];
        $passfield = Config::get('auth.password', 'password');
        $remember = Config::get('auth.remember', 'remember');

        if (!is_null($user) && Hash::check($password, $user->{$passfield})) {
            return $this->login($user->getKey(), array_get($credentials, $remember));
        }

        return false;
    }

    /**
     * Ambil instance model baru.
     *
     * @return object
     */
    protected function model()
    {
        return new \App\Models\User();
    }
}
