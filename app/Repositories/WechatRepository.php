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
    //配置请求url
    private $url = [
        'getCallBackIp' => self::url . 'getcallbackip',
        'menuCreate' => self::url . 'menu/create',
        'menuGet' => self::url . 'menu/get',
    ];

    public function __construct()
    {
        $this->appID = config('wechat.app_id');
        $this->appsecret = config('wechat.app_secret');
        $this->token = config('wechat.wechat_token');
    }

    //自定义菜单
    public function menuCreate()
    {
        $data = [
            'button' => [
                [
                    'type' => 'click',
                    'name' => '介绍',
                    'key' => 'afafaf',
                ],
                [
                    'name' => '菜单',
                    'sub_button' => [
                        [
                            'type' => 'view',
                            'name' => '获取姓名',
                            'url' => 'http://www.baidu.com',
                        ],
                        [
                            'type' => 'click',
                            'name' => '赞一下',
                            'key' => 'dfdfdf',
                        ]
                    ],
                ],
                [
                    'name' => '扫一扫',
                    'sub_button' => [
                        [
                            "type" => "scancode_waitmsg",
                            "name" => "扫码带提示",
                            "key" => "rselfmenu_0_0",
                            "sub_button" => [ ]
                        ],
                        [
                            "type" => "scancode_push",
                            "name" => "扫码推事件",
                            "key" => "rselfmenu_0_0",
                            "sub_button" => [ ]
                        ]
                    ],
                ],
            ]
        ];
        $res = $this->post(__FUNCTION__, $this->url['menuCreate'], json_encode($data, JSON_UNESCAPED_UNICODE));
        return $res;
    }

    //获取当前菜单
    public function menuGet()
    {
        $res = $this->post(__FUNCTION__, $this->url['menuGet']);
        return $res;
    }

    //获取回调ip地址
    public function getCallBackIp()
    {
        $res = $this->post(__FUNCTION__, $this->url['getCallBackIp']);
        return $res;
    }

    //发送请求方法
    public function post($action_name = '', $url, $data=[])
    {
        if (empty($action_name)) {
            return false;
        }
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }
        $res = vcurl($url . '?access_token=' . $accessToken, $data, true);
        Log::info($action_name . '|res:' . $res);
        return json_decode($res, true);
    }

    //获取token
    public function getAccessToken()
    {
        $key = 'wechat_access_token';
        if (Redis::get($key)) {
            return Redis::get($key);
        } else {
            $data = vcurl(self::url . 'token?grant_type=client_credential&appid=' .
                $this->appID . '&secret=' . $this->appsecret, '', true);
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

    public function getContent($xml = '')
    {
        $data = XML2Array::createArray($xml);
        $return = [
            'ToUserName' => $data['xml']['ToUserName']['@cdata'],
            'FromUserName' => $data['xml']['FromUserName']['@cdata'],
            'CreateTime' => $data['xml']['CreateTime'],
            'MsgType' => $data['xml']['MsgType']['@cdata'],
            'Event' => $data['xml']['Event']['@cdata'],
            'EventKey' => $data['xml']['EventKey']['@cdata'],
        ];
        //点击菜单跳转链接时的事件推送
        if ($return['Event'] == 'VIEW') {
            $return['MenuId'] = $data['xml']['MenuId'];
        }

        //scancode_push扫码推事件的事件推送
        if ($return['Event'] == 'scancode_push') {
            $return['ScanCodeInfo'] = [
                'ScanType' => $data['xml']['ScanCodeInfo']['ScanType']['@cdata'],
                'ScanResult' => $data['xml']['ScanCodeInfo']['ScanResult']['@cdata'],
            ];
        }
        return $return;
    }

    public function returnMsg($toUserName, $fromUserName, $msgType, $extra = [])
    {
        if (empty($toUserName) || empty($fromUserName) || empty($msgType) || empty($extra)) {
            return false;
        }

        $data = [
            'ToUserName' => ['@cdata' => $toUserName],
            'FromUserName' => ['@cdata' => $fromUserName],
            'CreateTime' => time(),
            'MsgType' => ['@cdata' => $msgType],
        ];

        if ($msgType == 'news') {
            $data['ArticleCount'] = count($extra);
            foreach ($extra as $value) {
                $data['Articles']['item'][] = [
                    'Title' => ['@cdata' => $value['title']],
                    'Description' => ['@cdata' => $value['description']],
                    'PicUrl' => ['@cdata' => $value['picurl']],
                    'Url' => ['@cdata' => $value['url']],
                ];
            }
        } elseif ($msgType == 'text') {
            foreach ($extra as $key => $value) {
                $data[$key] = ['@cdata' => $value];
            }
        } else {

        }

        Log::info(json_encode($data));
        $xmlObj = Array2XML::createXML('xml', $data);
        $xml = $xmlObj->saveXML();
        Log::info($xml);
        echo $xml;
        exit;
    }

    public function getError()
    {
        return $this->error;
    }
}