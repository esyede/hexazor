<?php

namespace App\Http\Controllers;

defined('DS') or exit('No direct script access allowed.');

use View;

class HomeController extends Controller
{
    public function index()
    {
        return View::make('welcome');
    }
}
