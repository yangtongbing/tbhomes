<?php
/**
 * Created by PhpStorm.
 * User: tongbing
 * Date: 17/8/18
 * Time: 14:17
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexPost;
use App\Repositories\CompanyRepository;
use App\Repositories\OrderExtRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    protected $user;

    private $session_key = 'webSign';

    private $path = './upload/';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = json_decode(session($this->session_key), true);
            return $next($request);
        });
    }

    //登录页面
    public function login()
    {
        echo 'success';exit;
        Session::put($this->session_key, '');
        return view('index.login', ['title' => '登录']);
    }

    //处理登录
    public function doLogin(IndexPost $request)
    {
        $input = $request->input();
        if (captcha_check($input['captcha']) === false) {
            return back()->withErrors('验证码错误');
        }
    }

    //图形验证码
    public function imgCode()
    {
        //简单防刷
        $pathInfo = pathinfo(env('APP_URL'));
        if ($_SERVER['HTTP_HOST'] != $pathInfo['basename']) {
            return $this->jsonError(9999, '禁止使用图形验证码');
        }
        return $this->jsonSuccess(array('code'=>captcha_src()));
    }

    /**
     * 上传文件
     * @param Request $request
     * @return array
     */
    public function uploadFile(Request $request)
    {
        //参数校验
        $orderId = $request->input('order_id');
        $file = $request->file('file');

        if (count($file) == 0) {
            return array('error' => '没有图片，请检查');
        }

        if (empty($orderId)) {
            return array('error' => '没有找到对应订单');
        }

        $orderExt = new OrderExtRepository();
        $orderExists = $orderExt->getOne('*', ['oid' => $orderId]);

        $path = 'certificate/' . $orderId . '/';
        //判断图片是不是已经上传5张了
        if (is_dir($this->path . $path)) {
            $dir = scandir($this->path . $path);
            if (count($dir) - 2 >= 5) {
                return array('error' => '最多只能上传5张图片');
            }
        }

        //循环上传图片
        foreach ($file as $value) {
            // 文件是否上传成功
            if ($value->isValid()) {
                // 获取文件相关信息
                $ext = $value->getClientOriginalExtension();     // 扩展名
                $realPath = $value->getRealPath();   //临时文件的绝对路径
                // 上传文件 指定上二级目录
                $filename = $path . time() . '-' . rand(1000, 9999) . '.' . $ext;
                // 使用我们新建的uploads本地存储空间（目录）
                $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));
                if ($bool) {
                    //更新数据库
                    if (empty($orderExists)) {
                        $res = $orderExt->create(['oid'=>$orderId,'c_time'=>time(),'img_path'=>json_encode([$filename])]);
                    } else {
                        $imgPath = json_decode($orderExists['img_path'], true);
                        $imgPath[] = $filename;
                        $res = $orderExt->update(['oid'=>$orderId], ['img_path' => json_encode($imgPath)]);
                    }
                    //获取预览图
                    $return = $this->getPreview($orderId, true);
                    if ($res) {
                        return $return;
                    } else {
                        return array('error' => '上传失败');
                    }

                } else {
                    return array('error' => '上传失败');
                }
            }
        }
    }

    //统一组装bootstrap fileinput预览图方法
    public function getPreview($oid, $single = false)
    {
        //取出对应的订单拓展信息
        $orderExt = new OrderExtRepository();
        $data = $orderExt->getOne('*', ['oid' => $oid]);
        if (empty($data)) {
            return false;
        }

        $imgPath = $data['img_path'];
        if (empty($imgPath)) {
            return false;
        }

        $imgPath = json_decode($imgPath, true);
        $initialPreview = [];
        $initialPreviewConfig = [];
        foreach ($imgPath as $value) {
            $file = $this->path  . $value;
            $handle = fopen($file,"r");
            //获取文件的统计信息
            $fstat = fstat($handle);
            $initialPreview[] = "<img width='100%' src='".ltrim($file, '.')."'/>";
            $initialPreviewConfig[] = (object)[
                    'caption' => basename($file),
                    'size' => $fstat['size'],
                    'url' => '/web/deleteImg',
                    'key' => $oid . '&&' . $value
            ];
        }

        if ($single) {
            $initialPreview = [end($initialPreview)];
            $initialPreviewConfig = [end($initialPreviewConfig)];
        }

        return [
            'initialPreview' => $initialPreview,
            'initialPreviewConfig' => $initialPreviewConfig
        ];
    }

    //删除图片
    public function deleteImg(Request $request)
    {

        $post = $request->input();
        $post = explode('&&', $post['key']);

        //取出对应的订单拓展信息
        $orderExt = new OrderExtRepository();
        $data = $orderExt->getOne('*', ['oid' => $post[0]]);
        if (empty($data) || empty($data['img_path'])) {
            return array('error'=>'请确认该订单下是否存在图片？');
        }

        $imgPath = json_decode($data['img_path'], true);
        $key = array_search($post[1], $imgPath);
        unset($imgPath[$key]);
        $imgPath = array_values($imgPath);
        $res = $orderExt->update(['oid' => $post[0]], ['img_path' => json_encode($imgPath)]);
        if ($res) {
            unlink($this->path . $post[1]);
            return array('success' => '删除成功');
        }
    }
}
