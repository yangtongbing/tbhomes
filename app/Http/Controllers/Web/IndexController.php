<?php
/**
 * Created by PhpStorm.
 * User: tongbing
 * Date: 17/8/18
 * Time: 14:17
 */

namespace App\Http\Controllers\Web;

use App\Models\AccountLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexPost;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Repositories\CompanyRepository;
use App\Repositories\CSubAccountRepository;
use App\Repositories\OrderExtRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;
use App\Repositories\RecordRepository;
use App\Repositories\ZoneRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    protected $user;

    private $session_key = 'webSign';

    private $path = './upload/';

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->middleware(function ($request, $next) {
            $this->user = json_decode(session($this->session_key), true);
            return $next($request);
        });
        $this->repository = $companyRepository;
    }

    //登录页面
    public function login()
    {
        Session::put($this->session_key, '');
        return view('index.login', ['title'=>'登录']);
    }

    //处理登录
    public function doLogin(IndexPost $request)
    {
        $input = $request->input();
        //校验验证码
        $BsSdk = resolve('BsSDK');
        $info = $BsSdk->verifyImageCode(9, $input['code'], $input['sid']);
        $info = json_decode($info, true);
        if ($info['code'] != 0) {
            return back()->withErrors('验证码错误');
        }

        //组装查询语句 验证账户密码
        $where = [
            'username' => $input['account']
        ];
        $data = $this->repository->getList($where, '*');
        //主账号表不存在 查询子账号表
        $cSubAccount = new CSubAccountRepository();
        if ($data['count'] == 0) {

            $subData = $cSubAccount->getList(['username' => $input['account']], '*');
            //子账号表不存在直接报错
            if ($subData['count'] > 0) {
                $subData = $subData['list'][0];
                if (md5(md5($input['password']).'partner') != $subData['password']) {
                    return back()->withErrors('密码不正确');
                } else {
                    //添加子账号标识 并查询出对应主账号信息存储
                    $subData['sub_sign'] = true;
                    $subData['mobile'] = $subData['username'];
                    $subData['sub_id'] = $subData['id'];
                    $subData['leader_name'] = $subData['name'];
                    $data = $this->repository->getList(['id' => $subData['company_id']], ['id','title','settlement_type','settlement_proportion']);
                    Session::put($this->session_key, json_encode(array_merge($subData, $data['list'][0])));
                }
                return redirect('web/account');
            } else {
                return back()->withErrors('请确认账号是否存在？');
            }
        } else {
            //验证密码 暂时注释密码验证
            $list = $data['list'][0];
            $list['sub_sign'] = false;
            $list['sub_id'] = '';
            if (md5(md5($input['password']).'partner') != $list['password']) {
                return back()->withErrors('密码不正确');
            } else {
                //查询子账户信息存储进登录信息中
                $subData = $cSubAccount->getList(['company_id' => $list['id']], ['username','seat_no']);
                if ($subData['count'] > 0) {
                    $list['sub'] = $subData['list'];
                } else {
                    $list['sub'] = [];
                }
                $this->user = $list;
                Session::put($this->session_key, json_encode($list));
            }
            return redirect('web/account');
        }
    }

    //图形验证码
    public function imgCode()
    {
        //简单防刷
        $pathInfo = pathinfo(env('APP_URL'));
        if ($_SERVER['HTTP_HOST'] != $pathInfo['basename']) {
            return $this->jsonError(9999, '禁止调用');
        }

        //调用基础服务图形验证码
        $BsSdk = resolve('BsSDK');
        $sessionId = session_id();
        if (empty($sessionId) == true) {
            $sessionId = time().rand(1000, 9999);
        }
        $info = $BsSdk->renderImageCode(9, 0, 0, 4, 900, ["fontSize"=>60], $sessionId);
        $info = json_decode($info, true);

        if ($info['code'] == 0) {
            return $this->jsonSuccess(array('code'=>$info['message'],'sid'=>$sessionId));
        } else {
            return $this->jsonError(9999, '验证码获取失败');
        }
    }

    public function mycustomer(Request $request,ZoneRepository $zoneRepository)
    {
        $query = Order::query();
        $filter['c_time_start'] = $request->input('c_time_start');
        $filter['c_time_end'] = $request->input('c_time_end');
        $filter['month_start'] = $request->input('month_start');
        $filter['month_end'] = $request->input('month_end');
        $filter['money_min'] = $request->input('money_min');
        $filter['money_max'] = $request->input('money_max');
        $filter['type'] = $request->input('type');
        $filter['zone_id'] = $request->input('zone_id');
        $filter['status'] = $request->input('status');
        if(isset($filter['c_time_start']) && !empty($filter['c_time_start'])){
            $query->where("c_time",">=",strtotime($filter['c_time_start']));
        }
        if(isset($filter['c_time_end']) && !empty($filter['c_time_end'])){
            $query->where("c_time","<",strtotime($filter['c_time_end']));
        }
        if(isset($filter['month_start']) && !empty($filter['month_start'])){
            $query->where("month",">=",$filter['month_start']);
        }
        if(isset($filter['month_end']) && !empty($filter['month_end'])){
            $query->where("month","<",$filter['month_end']);
        }
        if(isset($filter['money_min']) && !empty($filter['money_min'])){
            $query->where("month",">=",$filter['money_min']);
        }
        if(isset($filter['money_max']) && !empty($filter['money_max'])){
            $query->where("month","<",$filter['money_max']);
        }
        if(isset($filter['type']) && !empty($filter['type'])){
            $query->where("type","=",$filter['type']);
        }
        if(isset($filter['zone_id']) && !empty($filter['zone_id'])){
            $query->where("zone_id","=",$filter['zone_id']);
        }
        if(isset($filter['status']) && !empty($filter['status'])){
            $query->where("status","=",$filter['status']);
        }
        $query->where('company_id', '=', $this->user['id']);
        $zone = $zoneRepository->getZoneList();
        $lists = $query->orderBy('c_time','desc')->paginate(10);
        $data = [
            'user'=>$this->user,
            'title' => '我的客户',
            'lists'=>$lists,
            'city_list' => $zone,
            'search' => $filter,
            'audit_status' => config('field.audit_status')
        ];
        return view('index.mycustomer', $data);
    }

    /**
     * 我的账户
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function account()
    {
        return view('index.account', ['user'=>$this->user,'title' => '账户信息']);
    }

    /**
     * 我的积分
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function integral(Request $request)
    {
        //查询账号积分
        $integral = $this->repository->getOne(['amount'], ['id'=>$this->user['id']]);
        //查询条件
        $query = AccountLog::query();
        $filter['startTime'] = $request->input('startTime');
        $filter['endTime'] = $request->input('endTime');
        $filter['change_type'] = $request->input('change_type');

        if(isset($filter['startTime']) && !empty($filter['startTime'])){
            $query->where("c_time",">=",strtotime($filter['startTime']));
        }
        if(isset($filter['endTime']) && !empty($filter['endTime'])){
            $query->where("c_time","<=",strtotime($filter['endTime']));
        }
        if(isset($filter['change_type']) && !empty($filter['change_type'])){
            $query->where("change_type",'=',$filter['change_type']);
        }
        $query->where('company_id', '=', $this->user['id']);

        $lists = $query->orderBy('c_time','desc')->paginate(20);
        return view('index.integral', ['user'=>$this->user,'title' => '我的积分','lists'=>$lists,'filter'=>$filter,'integral'=>$integral['amount']]);
    }

    //获取订单信息
    public function orderEdit(Request $request)
    {
        $query = Order::query();
        $id = $request->input('oid');
        $data = $query->where('id', '=', $id)->get()->toArray();
        $orderStatusLog = OrderStatusLog::query();
        $statusLog = $orderStatusLog->where('oid', '=', $id)->orderBy('c_time', 'desc')->get()->toArray();
        foreach ($statusLog as &$value) {
            $value['order_status'] = config('field.audit_status')[$value['order_status']];
        }
        $data = $data[0];
        $preview = $this->getPreview($data['id']);
        if (!$preview) {
            $preview['initialPreview'] = [];
            $preview['initialPreviewConfig'] = [];
        }
        $data = array_merge($data, $preview, ['sub_sign' => (int)$this->user['sub_sign'], 'sub_id' => $this->user['sub_id']]);
        return $this->jsonSuccess(['data'=>$data,'statusLog'=>$statusLog], $id = $this->user['id']);
    }

    //进行订单更新操作
    public function doEdit(Request $request)
    {
        $orderRepository = new OrderRepository();
        $orderStatusLogRepository = new OrderStatusRepository();
        $recordRepository = new RecordRepository();
        $input = $request->input();

        //在指定状态下更新到店状态
        $storeStatus = [6,7,8,9,10,11];
        //判断备注是否为空，更新数据库插入状态更新记录表
        $upWhere = [
            'id' => $input['id']
        ];

        //查询当前订单记录，准备更新订单状态记录表
        $order = $orderRepository->getOne('*', $upWhere);

        //如果状态、备注、服务费、总下款金额
        if ((isset($input['audit_status']) && $order['status'] == $input['audit_status']) &&
            ((isset($input['remark']) && ($order['remark'] == $input['remark'])) || $input['remark'] == null) &&
            (isset($input['real_money']) && $input['real_money'] == $order['real_money']) &&
            (isset($input['service_charge']) && $input['service_charge'] == $order['service_charge']) &&
            (isset($input['settlement_type']) && $input['settlement_type'] == $order['settlement_type'])
        ) {

        } else {
            //判断结算方式
            if (isset($input['settlement_type']) && $input['settlement_type'] == 0) {
                return $this->jsonError('9999', '请选择结算方式');
            }

            //更新订单表
            $upData = [
                'remark' => isset($input['remark']) ? $input['remark'] : '',
                'status' => $input['audit_status'],
            ];

            $bonus = 0;

            if ($input['audit_status'] == 11) {
                //现查分成比例 根据分成比例计算对应分红
                $extra = $this->repository->getOne(['settlement_proportion'], ['id' => $this->user['id']]);
                if (empty($extra)) {
                    return $this->jsonError('9999', '请联系管理员设置分成比例');
                }
                $settlementProportion = $extra['settlement_proportion'];

                $upData['settlement_type'] = $input['settlement_type'];
                $upData['settlement_proportion'] = json_encode($settlementProportion);

                //真实下款金额
                if ((float)($input['real_money']) != 0) {
                    $upData['real_money'] = (float)$input['real_money'];
                    $bonus = bcdiv(bcmul($settlementProportion['real_money_proportion'], $upData['real_money']), 100, 2);
                } else {
                    return $this->jsonError('9999', '真实下款金额格式不正确');
                }

                //不同情况分别进行处理
                if ($input['settlement_type'] == 1) {
                    //放款金额结算
                } else if ($input['settlement_type'] == 2) {
                    //服务费结算
                    if ((float)($input['service_charge']) != 0){
                        $service_charge = (float)$input['service_charge'];
                        $bonus = bcdiv(bcmul($settlementProportion['service_charge_proportion'], $service_charge), 100, 2);
                    } else {
                        return $this->jsonError('9999', '服务费金额格式不正确');
                    }
                } else if ($input['settlement_type'] == 3) {
                    //放款金额+服务费结算
                    if ((float)($input['service_charge']) != 0) {
                        $service_charge = (float)$input['service_charge'];
                        $bonus += bcdiv(bcmul($settlementProportion['service_charge_proportion'], $service_charge), 100, 2);
                    } else {
                        return $this->jsonError('9999', '服务费金额格式不正确');
                    }
                }
                $upData['settlement_time'] = time();
            }

            $upData['bonus'] = $bonus;

            //是否到店
            if (in_array($input['audit_status'], $storeStatus)) {
                $upData['store_status'] = 1;
            }

            //状态更新日志
            $statusLog = [
                'oid' => $input['id'],
                'order_status' => $input['audit_status'],
                'remark' => isset($input['remark']) ? $input['remark'] : '',
                'c_time' => time()
            ];

            if ($bonus != 0) {
                $amount = $this->repository->deductAmount($this->user['id'], $bonus, 3, '好贷分成：' . $input['id']);
                if ($amount == false) {
                    return $this->jsonError(9999, '您的积分不足，请充值！');
                }
            }

            $res = $orderRepository->update($upWhere, $upData);
            if ($res) {
                //更新对应的通话记录s
                if (!empty($input['record_id']) && isset($input['record_id'])) {
                    $recordRepository->update(['id' => $input['record_id']], ['desc'=>$input['remark']]);
                }
                $orderStatusLogRepository->create($statusLog);
                //更新用户积分
            }
        }
        return $this->jsonSuccess([], '更新成功');
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
