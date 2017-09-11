<?php
/**
 * Created by PhpStorm.
 * User: tongbing
 * Date: 2017/8/3
 */

namespace App\Repositories;

use Illuminate\Support\Facades\Log;

class CallOutRepository
{

    private $apiUrl;  //api地址配置
    private $apiDomain; //接口请求地址
    private $sigParameter; //restapi验证参数(子账号)
    private $authorization; //验证信息(子账号)
    private $majorSigParameter; //restapi验证参数(主账号)
    private $majorAuthorization; //验证信息(主账号)
    private $appId; //appId
    private $host; //host


    public function __construct()
    {
        $this->appId = env('APPID');
        $this->host = env('HOST');
        if (env('APP_ENV') == 'online') {
            $this->apiDomain = 'https://' . env('HOST');
        } else {
            $this->apiDomain = 'http://' . env('HOST');
        }

        //组装验证参数
        $time = date("YmdHis");
        $this->sigParameter = strtoupper(md5(env('SUBACCOUNTSID').env('SUBACCOUNTTOKEN').$time));
        $this->authorization = base64_encode(env('SUBACCOUNTSID').':'.$time);

        //主账号验证参数
        $this->majorSigParameter = strtoupper(md5(env('ACCOUNTSID').env('AUTHTOKEN').$time));
        $this->majorAuthorization = base64_encode(env('ACCOUNTSID').':'.$time);

        /*接口地址初始化*/
        $this->apiUrl = array(
            'Enterprises_CreateUser' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/SubAccounts/'.env('SUBACCOUNTSID').'/Enterprises/createUser?sig='.$this->sigParameter,//创建企业用户
            'Enterprises_DropUser' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/SubAccounts/'.env('SUBACCOUNTSID').'/Enterprises/dropUser?sig='.$this->sigParameter,//删除用户
            'Enterprises_CreateGroup' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/SubAccounts/'.env('SUBACCOUNTSID').'/Enterprises/createGroup?sig='.$this->sigParameter,//创建技能组
            'Enterprises_DeleteGroup' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/SubAccounts/'.env('SUBACCOUNTSID').'/Enterprises/deleteGroup?sig='.$this->sigParameter,//删除技能组
            'Enterprises_AddGroupUser' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/SubAccounts/'.env('SUBACCOUNTSID').'/Enterprises/addGroupUser?sig='.$this->sigParameter,//添加用户到技能组
            'CallCenter_signIn' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/SubAccounts/'.env('SUBACCOUNTSID').'/CallCenter/signIn?sig='.$this->sigParameter,//签入
            'CallCenter_callOut' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/SubAccounts/'.env('SUBACCOUNTSID').'/CallCenter/callOut?sig='.$this->sigParameter,//拨出
            'CallCenter_changeMode' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/SubAccounts/'.env('SUBACCOUNTSID').'/CallCenter/changeMode?sig='.$this->sigParameter,//切换坐席模式
            'Applications_CreateSubAccount' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/Accounts/'.env('ACCOUNTSID').'/Applications/createSubAccount?sig='.$this->majorSigParameter,//创建子账号
            'Applications_SubAccountList' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/Accounts/'.env('ACCOUNTSID').'/Applications/subAccountList?sig='.$this->majorSigParameter,//子账号列表
            'Applications_DropSubAccount' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/Accounts/'.env('ACCOUNTSID').'/Applications/dropSubAccount?sig='.$this->majorSigParameter,//删除子账号
            'Applications_CallDetail' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/Accounts/'.env('ACCOUNTSID').'/Applications/callDetail?sig='.$this->majorSigParameter,//获取通话详情
            'Applications_CallRecordUrl' => $this->apiDomain.'/'.env('SOFTWAREVERSION')
                .'/Accounts/'.env('ACCOUNTSID').'/Applications/callRecordUrl?sig='.$this->majorSigParameter,//获取通话详情
        );
    }

    /**
     * 创建企业用户
     * @param $workNumber 销售唯一识别号
     * @param $phone 绑定的手机号
     * @param $displayName 客户端展示名称，为空展示创建时绑定的手机号
     * @return mixed
     */
    public function enterprisesCreateUser($workNumber, $phone, $displayName = '')
    {
        $data = array(
            'createUser' => array(
                'appId' => $this->appId,
                'workNumber' => $workNumber,
                'phone' => $phone,
                'displayName' => $displayName
            )
        );
        $res = $this->post($this->apiUrl['Enterprises_CreateUser'], $data);
        Log::info('enterprisesCreateUser|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 删除用户
     * @param $wordNumber 用户唯一id
     * @return mixed
     */
    public function enterprisesDropUser($workNumber)
    {
        $data = array(
            'dropUser' => array(
                'appId' => $this->appId,
                'workNumber' => $workNumber,
            )
        );
        $res = $this->post($this->apiUrl['Enterprises_DropUser'], $data);
        Log::info('enterprisesDropUser|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 签入接口
     * @param $workNumber 销售唯一识别号
     * @param $phone 坐席设备号码（手机号）
     * @return mixed
     */
    public function callcenterSignIn($workNumber, $phone)
    {
        $data = array(
            'signIn' => array(
                'appId' => $this->appId,
                'workNumber' => $workNumber,
                'deviceNumber' => $phone
            ),
        );
        $res = $this->post($this->apiUrl['CallCenter_signIn'], $data);
        Log::info('callcenterSignIn|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 拨出
     * @param $workNumber
     * @param $toPhone 要拨打的手机号
     * @param string $userData 用户数据（暂时为空）
     * @return mixed
     */
    public function callcenterCallOut($workNumber, $toPhone, $userData='')
    {
        $data = array(
            'callOut' => array(
                'appId' => $this->appId,
                'workNumber' => $workNumber,
                'to' => $toPhone,
                'userData' => $userData
            ),
        );
        $res = $this->post($this->apiUrl['CallCenter_callOut'], $data);
        Log::info('callcenterCallOut|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 创建技能组
     * @param $groupName 技能组名称
     * @return mixed
     */
    public function enterprisesCreateGroup($groupName)
    {
        $data = array(
            'createGroup' => array(
                'appId' => $this->appId,
                'groupName' => $groupName
            ),
        );
        $res = $this->post($this->apiUrl['Enterprises_CreateGroup'], $data);
        Log::info('enterprisesCreateGroup|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 删除技能组
     * @param $groupId 技能组id
     * @return mixed
     */
    public function enterprisesDeleteGroup($groupId)
    {
        $data = array(
            'deleteGroup' => array(
                'appId' => $this->appId,
                'gid' => $groupId
            ),
        );
        $res = $this->post($this->apiUrl['Enterprises_DeleteGroup'], $data);
        Log::info('enterprisesDeleteGroup|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 添加用户到技能组
     * @param $groupId
     * @param $workNumber
     * @return mixed
     */
    public function enterprisesAddGroupUser($groupId, $workNumber)
    {
        $data = array(
            'addGroupUser' => array(
                'appId' => $this->appId,
                'gid' => $groupId,
                'workNumber' => $workNumber
            ),
        );
        $res = $this->post($this->apiUrl['Enterprises_AddGroupUser'], $data);
        Log::info('enterprisesAddGroupUser|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 获取子账户列表
     * @return mixed
     */
    public function applicationsSubAccountList()
    {
        $data = array(
            'subAccountList' => array(
                'appId' => $this->appId
            )
        );
        $res = $this->post($this->apiUrl['Applications_SubAccountList'], $data, 'major');
        Log::info('applicationsSubAccountList|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 创建子账户
     * @return mixed
     */
    public function applicationsCreateSubAccount()
    {
        $data = array(
            'createSubAccount' => array(
                'appId' => $this->appId,
                'nickName' => '信贷圈（测试线）'
            )
        );
        $res = $this->post($this->apiUrl['Applications_CreateSubAccount'], $data, 'major');
        Log::info('applicationsCreateSubAccount|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 删除子账户
     * @return mixed
     */
    public function applicationsDropSubAccount()
    {
        $data = array(
            'dropSubAccount' => array(
                'appId' => $this->appId,
                'subAccountSid' => '97fc9ce26b566617d22f503cd3a9886e'
            )
        );
        $res = $this->post($this->apiUrl['Applications_DropSubAccount'], $data, 'major');
        Log::info('applicationsDropSubAccount|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 获取通话详情
     * @param $callId 通话唯一id
     * @return mixed
     */
    public function applicationsCallDetail($callId)
    {
        $data = array(
            'callDetail' => array(
                'appId' => $this->appId,
                'callId' => $callId
            )
        );
        $res = $this->post($this->apiUrl['Applications_CallDetail'], $data, 'major');
        Log::info('applicationsCallDetail|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 获取通话录音文件
     * @param $callId 通话唯一标识
     * @return mixed
     */
    public function applicationsCallRecordUrl($callId)
    {
        $data = array(
            'callRecordUrl' => array(
                'appId' => $this->appId,
                'callId' => $callId
            )
        );
        $res = $this->post($this->apiUrl['Applications_CallRecordUrl'], $data, 'major');
        Log::info('applicationsCallRecordUrl|data:' . json_encode($data) . ';res:' . $res);
        return json_decode($res, true);
    }

    /**
     * 数据请求方法
     * @param $url 请求url
     * @param $data 请求数据
     * @param string $account 用户表示，默认子账户
     * @return mixed
     */
    private function post($url, $data, $account = 'sub')
    {
        //替换成主账号验证参数
        if ($account != 'sub') {
            $this->authorization = $this->majorAuthorization;
        }

        //组装请求头
        $headerArr = array(
            'Host:'.$this->host,
            'Accept:application/json',
            'Content-Type:application/json; charset=utf-8',
            'Authorization:'.$this->authorization
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}