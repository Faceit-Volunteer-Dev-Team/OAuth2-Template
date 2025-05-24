<?php

namespace App\Http\Controllers;

use \App\Http\Controllers\Controller as InitializatorController;
use Illuminate\Http\Request;

class OauthController extends InitializatorController
{
    public static function login(Request $request)
    {
        // If the user is already logged in, redirect to the home page
        if ($request->session()->has('user')) {
            return redirect()->route('home');
        }

        // If the user is not logged in, redirect to the login page
        return parent::returnView('login');
    }

    public static function doLogin(Request $request)
    {
        $code = $request->input('code');

        // Getting the token from the code
        $response = \App\Services\UserService::getToken($code);
        if (! $response || $response->errcode != 0) {
            $basic_error = 'An error occured while trying to login. You may try again later.';
            if (env('APP_DEBUG')) {
                $basic_error .= ' ' . $response->message;
            }

            return parent::returnView('login', [
                'errors' => $basic_error
            ]);
        }

        $user = $response->data->player;

        $request->session()->put('user', $user);

        return redirect()->route('home');
    }

    public static function logout(Request $request)
    {
        $request->session()->forget('user');

        return redirect()->route('login')->with('notice', 'Logged out');
    }
}
