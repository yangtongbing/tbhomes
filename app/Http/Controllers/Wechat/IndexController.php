<?php
/**
 * Created by PhpStorm.
 * User: tongbing
 * Date: 17/8/18
 * Time: 14:17
 */

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\IntentUser;
use App\Models\Zone;
use App\Repositories\WechatRepository;
use App\Repositories\ZoneRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use phpspider\core\requests;
use phpspider\core\selector;
use thiagoalessio\TesseractOCR\TesseractOCR;

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
        Log::info('callback|'.var_export($data, true));
        if (empty($data) === true) {
            return 'success';
        }
        //转化xml
        $res = $this->repository->getContent($data);
        Log::info('callres|'.json_encode($res));
        if (!isset($res['Event'])) {
            $extra['Content'] = '你好';
            $this->repository->returnMsg($res['FromUserName'], $res['ToUserName'], 'text', $extra);
        } else {
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
        }

        //处理回调
        $data = $request->all();
        Log::info('callback|'.var_export($request->all(), true));
        if (empty($data) === true) {
            return 'success';
        }
        //验证签名
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

    public function getAccessToken(WechatRepository $wechatRepository, ZoneRepository $zoneRepository)
    {
        $res = $wechatRepository->getCallBackIp();
        if (!$res) {
            print_r($wechatRepository->getError());
        } else {
            print_r($res);
        }
    }

    public function pullData(ZoneRepository $zoneRepository)
    {
        set_time_limit(0);
        $file = 'http://www.mca.gov.cn/article/sj/tjbz/a/2017/201801/201801151447.html';
        \phpQuery::newDocumentFileHTML($file);
        $tr = pq('container');
        var_dump($tr);exit;
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
    }

    public function ocrCeshi()
    {

    }

    public function excel()
    {
        set_time_limit(0);
        Excel::load('./upload/15.5.23-16.1.23.xls', function ($reader) {
            $tmp = $reader->all();
            //取回对应城市
            $zoneName = [];
            foreach ($tmp as $value) {
                $value = json_decode(json_encode($value), true);
                if ($value['zone']) {
                    $zoneName[] = $value['zone'];
                }
            }

            $zone = Zone::whereIn('CN', $zoneName)->pluck('CN', 'zone_id')->toArray();

            //组装数据入库
            $cipherECB = resolve('CipherECB');
            foreach ($tmp as $value) {
                $value = json_decode(json_encode($value), true);
                if ($value['zone']) {
                    $zoneId = array_search($value['zone'], $zone);
                } else {
                    $zoneId = 0;
                }

                $temp = [
                    'name' => empty($value['name']) ? '' : $value['name'],
                    'mobile' => $cipherECB->encrypt($value['mobile']),
                    'bank_name' => empty($value['company_name']) ? '' : $value['company_name'],
                    'zone_id' => $zoneId,
                    'c_time' => strtotime($value['c_time']['date']),
                ];

                $intentUserMod = new IntentUser();
                if (!$intentUserMod->where($temp)->get()->toArray()) {
                    $intentUserMod->insert($temp);
                }
            }
        });
    }


    public function getRelation()
    {
        $url = "http://china.baixing.com/jinrongfuwu/?page=2";
        requests::set_cookie('Hm_lvt_5a727f1b4acc5725516637e03b07d3d2', 1532687850);
        requests::set_cookie('Hm_lpvt_5a727f1b4acc5725516637e03b07d3d2', time());
        requests::set_cookie('kjj_log_session_id', 15330150938403939405);
        requests::set_cookie('kjj_log_log_id', 15330150931643807553);
        requests::set_cookie('BAIDUID', '9B0846D3A1114F3D1B8856A8CB11EE43:FG=1');
        requests::set_cookie('HMACCOUNT', '9B57A23D5CEF22AE');
        requests::set_cookie('kjj_log_session_depth', rand(11,99));
        requests::set_header("Referer", "http://beijing.baixing.com");
        $html = requests::get($url);

        // 选择器规则
        $selector = "//ul[contains(@class,'list-ad-items')]//li/div";
        // 提取结果
        $result = selector::select($html, $selector);

        $temp = [];
        if ($result) {
            $html = new \simple_html_dom();
            foreach ($result as $value) {
                $html->load($value);
                $tmp = [
                    'mobile' => '',
                    'company' => '',
                    'zone' => '',
                ];
                foreach ($html->find('.media-body-title span button') as $value) {
                    $tmp['mobile'] = $value->attr['data-contact'];
                }

                if ($html->find('.ad-item-detail a')) {
                    $tmp['company'] = $html->find('.ad-item-detail a')[0]->plaintext;
                }

                $zone = $html->find('.ad-item-detail')[0]->plaintext;
                $zone = explode('-', $zone);
                $tmp['zone'] = trim($zone[0]);
                $temp[] = $tmp;
            }
        }

        if ($temp) {
            var_dump($temp);
        } else {
            Log::info('getRelationError');
        }
    }

}


