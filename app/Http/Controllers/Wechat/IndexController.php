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
use App\Repositories\ZoneRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Overtrue\Pinyin\Pinyin;

class IndexController extends Controller
{
    private $token;

    public function __construct(WechatRepository $wechatRepository)
    {
        $this->token = config('wechat.wechat_token');
        $this->repository = $wechatRepository;
    }

    public function callback(Request $request)
    {
        //处理回调
        $data = $request->getContent();
        Log::info('callback|'.$data);
        if (!$data) {
            return 'success';
        }
        //转化xml
        $res = $this->repository->getContent($data);
        Log::info('callres|'.json_encode($res));
        if ($res['Event'] == 'CLICK') {
            $extra[] = [
                'title' => '第一次测试',
                'description' => '测试抱着激动的心情',
                'picurl' => config('app.url') . '/img/pretend.jpg',
                'url' => config('app.url') . '/img/pretend.jpg',
            ];
            $this->repository->returnMsg($res['FromUserName'], $res['ToUserName'], 'news', $extra);
        } elseif ($res['Event'] == 'CLICK') {
            $extra['Content'] = '你好';
            $this->repository->returnMsg($res['FromUserName'], $res['ToUserName'], 'text', $extra);
        } else {

        }
        return 'success';

//        //验证签名
//        $signature = $request->input('signature');
//        $echostr = $request->input('echostr');
//        $timestamp = $request->input('timestamp');
//        $nonce = $request->input('nonce');
//        $tmp = [$this->token, $timestamp, $nonce];
//        sort($tmp, SORT_STRING);
//        $sha1Str = sha1(implode($tmp));
//        if ($sha1Str == $signature) {
//            return $echostr;
//        } else {
//            return false;
//        }
    }

    public function getAccessToken(WechatRepository $wechatRepository, ZoneRepository $zoneRepository)
    {
        set_time_limit(0);
        $file = 'http://www.mca.gov.cn/article/sj/tjbz/a/2017/201801/201801151447.html';
        \phpQuery::newDocumentFileHTML($file);
        $tr = pq('tr');
        $i = 0;
        $j = 1;
        foreach ($tr as $value) {
            if ($value->getAttribute('style') == 'mso-height-source:userset;height:14.25pt') {
                //获取对应的标签内容
                $data = [
                    'zone_id' => pq('.xl7026226:eq('.$i.')')->html(),
                    'zone_name' => pq('.xl7026226:eq('.$j.')')->html()
                ];

                if (substr($data['zone_id'], 2) == '0000') {
                    $rank = 0;
                    $pid = $data['zone_id'];
                } elseif (substr($data['zone_id'], 4) == '00') {
                    $rank = 1;
                    $pid = substr($data['zone_id'], 0, 2).'0000';
                } else {
                    $pid = substr($data['zone_id'], 0, 4).'00';
                    $rank = 2;
                }

                $data['rank'] = $rank;
                $data['pid'] = $pid;
                $data['c_time'] = time();
                $where = [
                    'zone_id' => $data['zone_id']
                ];

                $exists = $zoneRepository->getList($where);
                if ($exists['count'] == 0) {
                    $zoneRepository->create($data);
                    unset($data);
                }

                $i+=2;
                $j+=2;
            }
        }

        exit('成功');
//        echo "<pre>";
//        var_dump($data);exit;
        $res = $wechatRepository->menuCreate();
        if (!$res) {
            print_r($wechatRepository->getError());
        } else {
            print_r($res);
        }
    }
}
