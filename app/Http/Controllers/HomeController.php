<?php

namespace App\Http\Controllers;

use App\Facades\Wechat;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function login(Request $request)
    {
        $redirect_url = $request->input('redirect_url');
        $redirect_uri = 'http://dev.invoice.haodai.net/wechatCallback?redirect_url='.urlencode($redirect_url);
        $url = Wechat::authorize('snsapi_base', $redirect_uri);
        return redirect($url);
    }

    public function wechatCallback(Request $request)
    {
        $url = $request->input('redirect_url');
        $code = $request->input('code');
        $userInfo = Wechat::getUserBaseInfo($code);

        if(strpos($url, '?') > 0){
            $gotoUrl = $url.'&oid='.$userInfo['openid'];
        }else{
            $gotoUrl = $url.'?oid='.$userInfo['openid'];
        }

        return redirect($gotoUrl);
    }
}
