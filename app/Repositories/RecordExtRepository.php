<?php
/**
 * ClassName: RecordExtRepository
 * 录音管理(拓展信息)获取通话时长、下载通话录音文件
 * @author  yangtongbing<yangtongbing@haodai.net>
 * @version test:1.0
 */

namespace App\Repositories;

use App\Models\Record;
use Illuminate\Support\Facades\Log;
use Mockery\CountValidator\Exception;

class RecordExtRepository
{
    protected $mod;

    //最外层路径
    private $path = __DIR__.'/../../public/upload/callout/';

    /**
     * 双方状态对应关系
     */
    protected $status = array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        100 => 4,
    );

    public function __construct()
    {
        $this->mod = new Record();
        $this->repository = new CallOutRepository();
        if (!is_dir($this->path)) {
            mkdir($this->path, 0775, true);
        }
    }

    public function callDetail()
    {
        $data = $this->mod->whereIn('status', [0,2])->limit(100)->get()->toArray();
        if (count($data) == 0) {
            Log::info('callDetail:' . '暂时没有要处理的数据');exit;
        }

        foreach ($data as $value) {
            //获取通话详情
            $detail = $this->repository->applicationsCallDetail($value['call_id']);
            $url = false;
            if ($detail['resp']['respCode'] == 0) {
                $callDetail = $detail['resp']['callDetail'];
                if (isset($callDetail['SubDetails']) && !empty($callDetail['SubDetails'])) {
                    foreach ($callDetail['SubDetails'] as $val) {
                        if ($val['called'] != $value['seat_no']){
                            $callDetail = $val;
                        }
                    }
                }

                $upWhere = array(
                    'id' => $value['id']
                );
                $upData = array(
                    'status' => $this->status[$callDetail['status']],
                    'hangup_time' => strtotime($callDetail['hangupTime'])
                );
                //通话状态不是未接通计算对应通话时间
                if ($callDetail['status'] != 1) {
                    $url = true;
                    $upData['duration'] = strtotime($callDetail['hangupTime']) - strtotime($callDetail['establishTime']);
                }
            } else {
                Log::info('callDetailError:' . 'callId:'.$value['call_id'].';错误码:'.$detail['resp']['respCode']);
                continue;
            }

            //获取通话url
            if ($url === true) {
                $recordUrl = $this->repository->applicationsCallRecordUrl($value['call_id']);
                if ($recordUrl['resp']['respCode'] == 0) {
                    $upData['record_url'] = $recordUrl['resp']['callRecordUrl']['url'];
                } else {
                    Log::info('recordUrlError' . 'callId:'.$value['call_id'].';错误码:'.$recordUrl['resp']['respCode']);
                }
            }

            //更新结果记录日志
            $res = $this->mod->where($upWhere)->update($upData);
            Log::info('callDetailUpRes' . 'id:' . $value['id'] . ';更新结果：' . var_export($res, true));
        }
        $this->downloadFile($this->path);
    }

    public function downloadFile($path = '')
    {
        //查询是否有未下载文件的数据
        $where = [
            ['record_url', '<>', ''],
            ['record_status', '=', 0]
        ];

        $data = $this->mod->where($where)->select(['id','type','call_id','record_url'])->get()->toArray();

        if (count($data) == 0) {
            Log::info('downloadFile->暂时没有要下载的文件');exit;
        }

        foreach ($data as $value) {
            //要保存到数据库中的文件名
            $filePath = date('Y') . '/' . $value['type'] . '/' . date('m') . '/';
            //判断路径是否存在并创建
            if (!is_dir($path . $filePath)) {
                mkdir($path . $filePath, 0775, true);
            }
            $filePath .= $value['call_id'] . '.mp3';
            //要填充的文件名
            $fileName = $path . $filePath;
            //取回文件流，填充进对应的文件
            $str = $this->vcurl($value['record_url']);
            if (empty($str) || $str == false) {
                Log::info('downloadFile->call_id:链接可能已经过期');
            } else {
                try {
                    $file = file_put_contents($fileName, $str);
                } catch(Exception $e) {
                    Log::info('downloadFile->' . $e->getMessage());
                }

                $upWhere = [
                    'id' => $value['id']
                ];

                $upData = [
                    'record_file' => $filePath,
                    'record_status' => 1
                ];

                //更新结果记录日志
                $res = $this->mod->where($upWhere)->update($upData);
                Log::info('downloadFile|' . 'id:' . $value['id'] . ';更新结果：' . var_export($res, true) . ';文件写入状态：' . var_export($file, true));
            }
        }
    }

    /**
     * curl请求方法
     * @param $url
     * @param string $post
     * @param bool $with_ssl
     * @return bool|mixed
     */
    public function vcurl($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3000);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);

        if (curl_errno($curl)) {
            return false;
        }
        curl_close($curl);
        return $res;
    }
}