<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected static function returnView($view, $data = [])
    {
        return view($view, array_merge([
            "user" => \Request::session()->get("user"),
        ], $data));
    }
}