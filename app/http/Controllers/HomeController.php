<?php

namespace app\App\Http\Controllers;

use app\Core\Request\Request;

class HomeController extends Controller
{

    public function index(Request $request)
    {
        return view('index', ['hola' => '12312312312']);
    }

    public function home()
    {
        return 'asdasdas';
    }
}


?>