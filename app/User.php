<?php

namespace App;

defined('DS') or exit('No direct script access allowed.');

use System\Database\ORM\Model;

class User extends Model
{
    public static $timestamps = true;
    public static $hidden = ['password', 'remember_token'];

    // ...
}
