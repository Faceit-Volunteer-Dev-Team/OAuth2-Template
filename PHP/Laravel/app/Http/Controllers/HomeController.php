<?php

namespace App\Http\Controllers;

use \App\Http\Controllers\Controller as InitializatorController;
use Illuminate\Http\Request;

class HomeController extends InitializatorController
{
    public static function home(Request $request)
    {
        // I don't have to do the login check because the middleware EnsureUserIsLoggedIn is already doing it
        // Moreover, I don't need to read the session to retrieve the user, because returnView() is already doing it
        return parent::returnView('home');
    }
}
