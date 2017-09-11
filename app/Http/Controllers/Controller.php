<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 成功回调
     * @param $message
     * @return array
     */
    protected function ajaxSuccess($message)
    {
        $data = array(
            'code' => 0,
            'message' => $message
        );
        return $data;
    }

    /**
     * 错误处理
     * @param $code
     * @param $message
     * @return array
     */
    protected function ajaxError($code, $message)
    {
        $data = array(
            'code' => $code,
            'message' => $message
        );
        return $data;
    }


    /**
     * @param $code 错误码
     * @param $message 错误消息提示
     * @return array
     */
    public function jsonError($code, $message, $details=[])
    {
        $data = array(
            'code' => $code,
            'msg' => $message,
            'details' => $details
        );
        return $data;
    }


    /**
     * @param array $data
     * @param string $message
     * @return array
     */
    public function jsonSuccess($data = [], $message = 'success')
    {
        $data = array(
            'code' => 0,
            'msg' => $message,
            'details' => $data
        );
        return $data;
    }
}
