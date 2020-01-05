<?php

namespace App\Http\Controllers;

defined('DS') or exit('No direct script access allowed.');

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }
}
