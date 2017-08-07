<?php
/**
 * Created by PhpStorm.
 * User: 12807
 * Date: 2017/6/8
 * Time: 18:21
 */

namespace App\Http\Controllers;

use App\Http\Requests\Request;

class UserController extends Controller{
    public function show()
    {
        exit("123");
    }

    public function shows(Request $request)
    {
        echo "<pre>";
        var_dump($request->input());
    }
}