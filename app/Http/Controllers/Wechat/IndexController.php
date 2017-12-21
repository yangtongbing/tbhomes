<?php
/**
 * Created by PhpStorm.
 * User: tongbing
 * Date: 17/8/18
 * Time: 14:17
 */

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Repositories\WechatRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    private $token;

    public function __construct()
    {
        $this->token = config('wechat.wechat_token');
    }


    public function checkStatus(Request $request)
    {
        $signature = $request->input('signature');
        $echostr = $request->input('echostr');
        $timestamp = $request->input('timestamp');
        $nonce = $request->input('nonce');
        $tmp = [$this->token, $timestamp, $nonce];
        sort($tmp, SORT_STRING);
        $sha1Str = sha1(implode($tmp));
        if ($sha1Str == $signature) {
            return $echostr;
        } else {
            return false;
        }
    }

    public function getAccessToken(WechatRepository $wechatRepository)
    {
        $res = $wechatRepository->getAccessToken();
        if (!$res) {
            var_dump($wechatRepository->getError());
        } else {
            var_dump($res);
        }
    }
}
