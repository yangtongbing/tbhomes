<?php
/**
 * Created by PhpStorm.
 * User: zhaojipeng
 * Date: 17/5/27
 * Time: 16:12
 */

namespace App\Repositories;


use Illuminate\Support\Facades\Cache;
use App\Facades\Http;
use LaLit\Array2XML;
use LaLit\XML2Array;

class Wechat
{
    const TOKEN = 'A6ovLloGP6939IKgCENEcF2piDW8KZukz';
    const APPID = 'wx42a260918b548f92';
    const APPSECRET = '35a6160821b9d570e31cb38a119f1fdd';
    const WX_HOST = 'https://api.weixin.qq.com/cgi-bin/';

    private $access_token = '';
    public $color = '#173177';
    private $error = '';

    public function __construct()
    {
//        $this->access_token = $this->getAccessToken();

//        if ($this->access_token === false) {
//            echo $this->getError();
//            exit;
//        }
    }

    /**
     * @param string $type
     * @param string $url
     * @return string
     */
    public function authorize($type = '', $url = '')
    {
        $query = [
            'appid' => self::APPID,
            'redirect_uri' => $url,
            'response_type' => 'code',
            'scope' => $type,
            'state' => 'STATE#wechat_redirect'
        ];

        $query_str = http_build_query($query);
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?'.$query_str;
        return $url;
    }

    public function getUserBaseInfo($code = ''){
        $query = [
            'appid' => self::APPID,
            'secret' => self::APPSECRET,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];

        $query_str = http_build_query($query);

        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?'.$query_str;

        $re = Http::get($url);

        //处理请求结果
        if ($re->getStatusCode() != 200) {
            $this->error = '请求失败|http code:' . $re->getStatusCode();
            return false;
        }

        $re = json_decode($re->getBody(), true);

        if ($re === false) {
            $this->error = '数据解析失败';
            return false;
        }

        return $re;

    }

    public function getError()
    {
        return $this->error;
    }

    public function getMsgContent($xml)
    {
        $data = XML2Array::createArray($xml);
        $returnData = [
            'ToUserName' => $data['xml']['ToUserName']['@cdata'],
            'FromUserName' => $data['xml']['FromUserName']['@cdata'],
            'CreateTime' => $data['xml']['CreateTime'],
            'MsgType' => $data['xml']['MsgType']['@cdata'],
            'MsgId' => $data['xml']['MsgId'],
        ];

        if ($returnData['MsgType'] == 'text') {
            $returnData['Content'] = $data['xml']['Content']['@cdata'];
        }

        return $returnData;
    }

    public function getMenu()
    {
        $re = $this->_query('menu/get');

        if ($re === false) {
            return false;
        }

        return $re;
    }

    public function setMenu($menu = [])
    {
        if (empty($menu) === true) {
            $this->error = '菜单项不能为空';
            return false;
        }

        $re = $this->_query('menu/create', true, $menu);

        if ($re === false) {
            return false;
        }

        return true;
    }


    /**
     * 响应用户消息,返回图文消息
     * @param $open_id
     * @param $form_id
     * @param $msg
     */
    public function replyMessage($type = 'text', $open_id, $form_id, $msg)
    {
        $data = [
            'ToUserName' => ['@cdata' => $open_id],
            'FromUserName' => ['@cdata' => $form_id],
            'CreateTime' => time(),
        ];


        switch ($type) {
            case 'text':
                $data['MsgType'] = ['@cdata' => 'text'];
                $data['Content'] = ['@cdata' => $msg];
                break;
            case 'news':
                $data['MsgType'] = ['@cdata' => 'news'];
                $data['ArticleCount'] = count($msg);

                foreach ($msg as $item) {
                    $data['Articles']['item'][] = [
                        'Title' => ['@cdata' => $item['title']],
                        'Description' => ['@cdata' => $item['desc']],
                        'PicUrl' => ['@cdata' => $item['pic_url']],
                        'Url' => ['@cdata' => $item['url']]
                    ];
                }

                break;
        }

        $xmlObj = Array2XML::createXML('xml', $data);
        $xml = $xmlObj->saveXML();
        echo $xml;
        exit;
    }

    public function getTemplateList()
    {
        $re = $this->_query('template/get_all_private_template');

        if ($re === false) {
            return false;
        }

        return $re['template_list'];
    }


    /**
     * 发送模板消息
     * @param string $open_id
     * @param string $template_id
     * @param array $data
     * @return bool
     *
     * $data 的格式为
     * $data = [
     *     'first' => '恭喜你购买成功!',
     *     'keyword1' => '13100131000',
     *     'keyword2' => '520.00',
     *     'keyword3' => '银座大厦',
     *     'remark' => '欢迎再次购买！'
     * ];
     *
     */
    public function sendTemplateMsg($open_id = '', $template_id = '', $data = [])
    {

        $msg = [
            'touser' => $open_id,
            'template_id' => $template_id,
        ];

        foreach ($data as $key => $value) {
            $msg['data'][$key] = ['value' => $value, 'color' => $this->color];
        }

        $re = $this->_query('message/template/send', true, $msg);

        if ($re === false) {
            return false;
        }

        return true;
    }


    private function _query($url = '', $is_post = false, $data = [])
    {
        $url = self::WX_HOST . $url . '?access_token=' . $this->access_token;

        //请求类型
        if ($is_post === false) {
            $re = Http::get($url);
        } else {

            $data = json_encode($data, JSON_UNESCAPED_UNICODE);

            $header = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ];

            $re = self::curl($url, $data, true, 'UTF-8', '', $header);
        }

        //处理请求结果
        if ($re->getStatusCode() != 200) {
            $this->error = '请求失败|http code:' . $re->getStatusCode();
            return false;
        }

        $re = json_decode($re->getBody(), true);

        if ($re === false) {
            $this->error = '数据解析失败';
            return false;
        }

        if (isset($re['errcode']) === true && $re['errcode'] != 0) {
            $this->error = '请求失败|' . $re['errcode'] . ':' . $re['errmsg'];
            return false;
        }

        return $re;
    }

    private function getAccessToken()
    {
        if (!Cache::has('access_token')) {
            $url = self::WX_HOST . 'token?grant_type=client_credential&appid=' . self::APPID . '&secret=' . self::APPSECRET;
            $re = Http::get($url);
            if ($re->getStatusCode() == 200) {
                $re = json_decode($re->getBody(), true);

                if (!empty($re['access_token'])) {
                    Cache::put('access_token', $re['access_token'], $re['expires_in']);
                    $access_token = $re['access_token'];
                } else {
                    $this->error = 'access_token获取失败|' . $re['errcode'] . ':' . $re['errmsg'];
                    return false;
                }
            } else {
                $this->error = 'access_token获取失败|http code:' . $re->getStatusCode();
                return false;
            }
        } else {
            $access_token = Cache::get('access_token');
        }

        return $access_token;
    }
}