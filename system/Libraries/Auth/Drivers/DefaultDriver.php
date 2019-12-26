<?php

namespace System\Libraries\Auth\Drivers;

defined('DS') or exit('No direct script access allowed.');

use System\Core\Config;
use System\Database\Database;
use System\Facades\Hash;

class DefaultDriver extends Driver
{
    /**
     * Ambil user saat ini.
     *
     * @param int $id
     *
     * @return mixed|null
     */
    public function retrieve($id)
    {
        if (false !== filter_var($id, FILTER_VALIDATE_INT)) {
            return Database::table(Config::get('auth.table'))->find($id);
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
        $config = Config::get('auth');
        $user = $this->getUser($credentials);
        $password = $credentials['password'];
        $passfield = Config::get('auth.password', 'password');
        $remember = Config::get('auth.remember', 'remember');

        if (!is_null($user) && Hash::check($password, $user->{$passfield})) {
            return $this->login($user->id, array_get($credentials, $remember));
        }

        return false;
    }

    /**
     * Ambil data user dari database.
     *
     * @param array $credentials
     *
     * @return mixed
     */
    protected function getUser(array $credentials)
    {
        return Database::table('users')->where(function ($query) use ($credentials) {
            $username = Config::get('auth.username', 'email');
            $passfield = Config::get('auth.password', 'password');
            $remember = Config::get('auth.remember', 'remember');

            $query->where($username, '=', $credentials[$username]);
            $columns = array_except($credentials, [$username, $passfield, $remember]);

            foreach ($columns as $column => $val) {
                $query->where($column, '=', $val);
            }
        })->first();
    }
}
