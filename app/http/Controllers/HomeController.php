<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use PhpVueBridge\Http\Request\Request;

class HomeController extends Controller
{

    public function index(Request $request)
    {
        return view('hola', ['hola' => '12312312312']);
    }

    public function home()
    {
        return 'asdasdas';
    }
}
