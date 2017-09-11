<?php
/**
 * Created by PhpStorm.
 * User: zhaojipeng
 * Date: 17/7/24
 * Time: 16:02
 */

namespace App\Facades;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Facade;

class Http extends Facade
{
    public static function getFacadeAccessor()
    {
        return new Client();
    }
}