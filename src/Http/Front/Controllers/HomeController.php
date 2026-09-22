<?php

namespace Chuoke\Blog\Http\Front\Controllers;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('blog::home');
    }
}
