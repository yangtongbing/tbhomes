<?php
/**
 * Created by PhpStorm.
 * User: yangtongbing
 * Date: 17/7/24
 * Time: 16:02
 */

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Wechat extends Facade
{
    public static function getFacadeAccessor()
    {
        return new \App\Repositories\WechatRepository();
    }
}