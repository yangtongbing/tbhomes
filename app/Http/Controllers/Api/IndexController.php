<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 2017/6/5
 * Time: 上午11:30
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\DayPushLogRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OutBoundRepository;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller
{

    public function index(Request $request, CompanyRepository $company, OrderRepository $orderRepository)
    {
        $order = $request->input('order');
        $rules = $company->getPushConfig();
        Log::info('apply|'.$order);

        $order = json_decode($order, true);

        $orderWhere = [
            ['order_id', '=', $order['order_id']]
        ];

        $info =  $orderRepository->getOne(['id'],$orderWhere);
        if($info){
            Log::info('repeat order|id:'.$info['id']);
            return ['code' => 1002, 'msg' => '订单已存在'];
        }

        $id = $this->filter($order, $rules);
        Log::info('filter|id:'.$id);

        if($id == false){
            return ['code' => 1001, 'msg' => '未匹配到机构'];
        }

        $re = $this->buy($orderRepository, $company, $order, $id);
        return $re;
    }

    private function filter($order, $rules)
    {
        $dayPushLog = new DayPushLogRepository();

        //检测是否符合规则
        foreach ($rules as $item) {
            if ($item['amount'] < $item['unit_price']) {
                continue;
            }

            $where = [
                ['company_id', '=', $item['id']],
                ['year', '=', date('Y')],
                ['month', '=', date('m')],
                ['day', '=', date('d')],
            ];

            $pushInfo = $dayPushLog->getOne(['push_number'], $where);

            if(!$pushInfo && $item['day_push_number'] <= 0){
                continue;
            }

            if($pushInfo && $pushInfo['push_number'] <= 0){
                continue;
            }

            if ($order['money'] < $item['push_config']['money_min'] || ($order['money'] > $item['push_config']['money_max'] && $item['push_config']['money_max'] != 0)) {
                continue;
            }

            if ($order['age'] < $item['push_config']['age_min'] || ($order['age'] > $item['push_config']['age_max'] && $item['push_config']['age_max'] != 0)) {
                continue;
            }

            if ($order['salary_bank_public'] < $item['push_config']['salary_bank_public_min'] || ($order['salary_bank_public'] > $item['push_config']['salary_bank_public_max'] && $item['push_config']['salary_bank_public_max'] != 0)) {
                continue;
            }

            if ($order['work_license'] < $item['push_config']['work_license_min'] || ($order['work_license'] > $item['push_config']['work_license_max'] && $item['push_config']['work_license_max'] != 0)) {
                continue;
            }

            if (!in_array($order['zone_id'], $item['push_config']['zone_id']) && empty($item['push_config']['zone_id']) != true) {
                continue;
            }

            if ($order['salary_bank_private'] != $item['push_config']['salary_bank_private'] && $item['push_config']['salary_bank_private'] != 0) {
                continue;
            }

            if ($order['profession'] != $item['push_config']['profession'] && $item['push_config']['profession'] != 0) {
                continue;
            }

            $necessary_status = true;
            foreach ($item['push_config']['necessary'] as $val) {
                switch ($val){
                    case 'is_fund':
                        if($order['is_fund'] == 2){
                            $necessary_status = false;
                        }
                        break;
                    case 'is_security':
                        if($order['is_security'] == 2){
                            $necessary_status = false;
                        }
                        break;
                    case 'credit_card':
                        if($order['credit_card_new'] == 2){
                            $necessary_status = false;
                        }
                        break;
                    case 'house_type':
                        if($order['house_type_new'] == 1){
                            $necessary_status = false;
                        }
                        break;
                    case 'car_type':
                        if(in_array($order['car_type_new'], [1,4])){
                            $necessary_status = false;
                        }
                        break;
                    case 'is_buy_insurance':
                        if(isset($order['is_buy_insurance']) === false || $order['is_buy_insurance'] == 1){
                            $necessary_status = false;
                        }
                        break;
                }
            }

            if($necessary_status === false){
                continue;
            }

            $optional_status = false;
            foreach ($item['push_config']['optional'] as $val) {
                switch ($val){
                    case 'is_fund':
                        if($order['is_fund'] == 1){
                            $optional_status = true;
                        }
                        break;
                    case 'is_security':
                        if($order['is_security'] == 1){
                            $optional_status = true;
                        }
                        break;
                    case 'credit_card':
                        if($order['credit_card_new'] != 2){
                            $optional_status = true;
                        }
                        break;
                    case 'house_type':
                        if($order['house_type_new'] != 1){
                            $optional_status = true;
                        }
                        break;
                    case 'car_type':
                        if(!in_array($order['car_type_new'], [1,4])){
                            $optional_status = true;
                        }
                        break;
                    case 'is_buy_insurance':
                        if(isset($order['is_buy_insurance']) === true && $order['is_buy_insurance'] == 2){
                            $optional_status = true;
                        }
                        break;
                }

                if($optional_status === true){
                    break;
                }
            }

            if($optional_status === false && empty($item['push_config']['optional']) === false){
                continue;
            }

            //检测当天是否可推送
            $week = date('w');
            if(in_array($week, [0,6])){
                continue;
            }

            $holiday_config = config(date('Y'));
            if(in_array(date('m-d'), $holiday_config)){
                continue;
            }

            //返回企业ID
            return $item['id'];
        }

        return false;
    }

    private function buy($orderMod, $companyMod, $order, $company_id)
    {
        $cipherECB = resolve('CipherECB');

        //订单入库
        $orderData = [
            'username' => $order['username'],
            'mobile' => $cipherECB->encrypt($order['mobile']),
            'iden_card' => $cipherECB->encrypt($order['iden_card']),
            'zone_id' => $order['zone_id'],
            'money' => $order['money'],
            'month' => $order['month'],
            'use_company' => $order['use_company'],
            'marriage' => $order['marriage'],
            'credit_card' => $order['credit_card_new'],
            'house_type' => $order['house_type_new'],
            'house_pledge' => $order['house_pledge'],
            'house_price' => $order['house_price'],
            'house_leixing' => $order['house_leixing'],
            'car_type' => $order['car_type_new'],
            'car_pledge' => $order['car_pledge'],
            'car_price' => $order['car_price'],
            'profession' => $order['profession'],
            'manage_year' => $order['manage_year'],
            'manage_address' => $order['manage_address'],
            'work_license' => $order['work_license'],
            'salary_bank_private' => $order['salary_bank_private'],
            'salary_bank_public' => $order['salary_bank_public'],
            'is_fund' => $order['is_fund'],
            'is_security' => $order['is_security'],
            'type' => $order['type'],
            'industry' => $order['industry'],
            'from_ip' => $order['from_ip'],
            'order_id' => $order['order_id'],
            'age' => $order['age']
        ];

        if(isset($order['is_buy_insurance']) === true){
            $orderData['is_buy_insurance'] = $order['is_buy_insurance'];
        }

        if(isset($order['insurance_time']) === true){
            $orderData['insurance_time'] = $order['insurance_time'];
        }

        if(isset($order['insurance_value']) === true){
            $orderData['insurance_value'] = $order['insurance_value'];
        }

        if(isset($order['insurance_company']) === true){
            $orderData['insurance_company'] = $order['insurance_company'];
        }

        Log::info('create_order|'.json_encode($orderData));
        $orderRe = $orderMod->create($orderData);


        //扣可推送次数
        $re = $companyMod->deductPushNumber($company_id);
        if($re === false){
            return ['code' => 9999, 'msg' => $companyMod->getError()];
        }

        //扣积分
        $where = [
            ['id', '=', $company_id]
        ];
        $info = $companyMod->getOne('unit_price', $where);
        $re = $companyMod->deductAmount($company_id, $info['unit_price'], 1, '推送订单，订单号：'.$orderRe->id);
        if($re === false){
            return ['code' => 9999, 'msg' => $companyMod->getError()];
        }

        $orderMod->update([['id', '=', $orderRe->id]], ['company_id' => $company_id]);
        return ['code' => 1000, 'data' => ['company_id' => $company_id]];
    }

    public function createUser(Request $request)
    {
        //参数非空校验
        $id = $request->input('id');
        $mobile = $request->input('mobile');
        $leader_name = $request->input('leader_name');
        $sub_sign = $request->input('sub_sign');
        if (empty($id) == true) {
            return ['code' => 1000, 'data' => 'success'];
        }

        if (empty($mobile) == true) {
            return ['code' => 1000, 'data' => 'success'];
        }

        if (empty($leader_name) == true) {
            return ['code' => 1000, 'data' => 'success'];
        }

        if (empty($sub_sign) == true) {
            return ['code' => 1000, 'data' => 'success'];
        }

        //1是主账号
        if ($sub_sign == 1) {
            $sub_sign = false;
        } else {
            $sub_sign = true;
        }
        //创建用户
        $repository = new OutBoundRepository();
        $res = $repository->createUser($id, $mobile, $leader_name, $sub_sign);
        if ($res) {
            return ['code' => 1000, 'data' => '创建成功'];
        } else {
            var_dump($repository->getError());
        }
    }

    public function dropUser(Request $request)
    {
        //参数校验
        $id = $request->input('id');
        $sub_sign = $request->input('sub_sign');
        if (empty($sub_sign) == true) {
            return ['code' => 1000, 'data' => 'success'];
        }
        if (empty($id) == true) {
            return ['code' => 1000, 'data' => 'success'];
        }
        //子账号标识
        if ($sub_sign == 1) {
            $sub_sign = false;
        } else {
            $sub_sign = true;
        }
        $repository = new OutBoundRepository();
        $res = $repository->dropUser($id, $sub_sign);
        if ($res) {
            return ['code' => 1000, 'data' => '删除成功'];
        } else {
            var_dump($repository->getError());
        }
    }
}