<?php

namespace App\Http\Controllers;

defined('DS') or exit('No direct script access allowed.');

use App\Http\Controllers\Controller;
use View, DB;

class HomeController extends Controller
{
	public function index()
	{
		dd(DB::getPdo());
		return View::make('welcome');
	}
}