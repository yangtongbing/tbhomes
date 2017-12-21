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
    const url = 'https://api.weixin.qq.com/cgi-bin/';

    private $appID;
    private $appsecret;
    private $token;
    private $error; //错误信息

    private $url = [
        'getcallbackip' => self::url . 'getcallbackip',
    ];

    public function __construct()
    {
        $this->appID = config('wechat.app_id');
        $this->appsecret = config('wechat.app_secret');
        $this->token = config('wechat.wechat_token');
    }

    public function getCallBackIp()
    {
        $res = $this->post($this->url['getcallbackip']);
        if ($res['']) {

        }
    }

    public function post($url, $data=[])
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }
        $res = vcurl($url . '?access_token=' . $accessToken, $data);
        Log::info('wechat|res:' . $res);
        return json_decode($res, true);
    }

    public function getAccessToken()
    {
        $key = 'wechat_access_token';
        if (Redis::get($key)) {
            return Redis::get($key);
        } else {
            $data = vcurl(self::url . 'token?grant_type=client_credential&appid=' .
                $this->appID . '&secret=' . $this->appsecret);
            Log::info('wechat|res:' . $data);

            //解析返回的数据 出错直接返回对应的错误
            $data = json_decode($data, true);
            if ($data == null || array_key_exists('errcode', $data)) {
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