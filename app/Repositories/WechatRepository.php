<?php
/**
 * Created by PhpStorm.
 * User: yangtongbing
 * Date: 17/11/9
 * Time: 16:21
 */

namespace App\Repositories;


use App\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use LaLit\Array2XML;
use LaLit\XML2Array;

class WechatRepository
{
    const appID = 'wx97bb97b4ce15fbae';
    const appsecret = 'e63f75bdf07dee13cddab1c3347673f0';
    const token = 'MGM2OGEyYTliODJhMjYwYTUwYjUyNDlk';
    const url = 'https://api.weixin.qq.com/cgi-bin/';

    private $error; //错误信息

    public function __construct()
    {

    }

    public function getAccessToken()
    {
        $key = 'wechat_access_token';
        if (Redis::get($key)) {
            return Redis::get($key);
        } else {
            $data = vcurl(self::url . 'token?grant_type=client_credential&appid=' .
                self::appID . '&secret=' . self::appsecret);
            Log::info('wechat|res:' . $data);

            //解析返回的数据 出错直接返回对应的错误
            $data = json_decode($data, true);
            if (array_key_exists('errcode', $data)) {
                $this->error = 'access_token获取失败';
                return false;
            } else {
                Redis::set($key, $data['access_token']);
                Redis::expire($key, $data['expires_in']);
                return $data['access_token'];
            }
        }
    }

    public function getError()
    {
        return $this->error;
    }
}