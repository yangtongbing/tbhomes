<?php

namespace App\Repositories;

use App\Models\Record;

class OutBoundRepository
{
    private $error_msg;

    private $suffix = 'credit_manager_';

    private $suffix_sub = 'credit_manager_sub_';

    public function __construct()
    {
        $this->repository = new CallOutRepository();
        $this->company = new CompanyRepository();
        $this->sub_account = new CSubAccountRepository();
    }


    public function callOut($input = [])
    {
        $cipherECB = resolve('CipherECB');
        if (empty($input['id'] == true)) {
            $this->error_msg = 'id不能为空';
            return false;
        }
        if (!empty($input['mobile'] == true)) {
            $mobile = $cipherECB->decrypt($input['mobile']);
            if (!$this->isMobile($mobile)) {
                $this->error_msg = '手机号格式不正确';
                return false;
            }
        } else {
            $this->error_msg = '手机号不能为空';
            return false;
        }
        if (empty($input['source'] == true)) {
            $this->error_msg = '来源不能为空';
            return false;
        }
        $type = isset($input['type']) ? $input['type'] : 0;

        //判断是否是子账号登录
        if ($input['sub_sign']) {
            $cSubAccount = new CSubAccountRepository();
            $company = $cSubAccount->getOne(['seat_no'], ['id' => $input['sub_id']]);
            $uniqueKey = $this->suffix_sub . $input['sub_id'];
        } else {
            $company = $this->company->getOne(['seat_no','id'], ['id' => $input['id']]);
            $uniqueKey = $this->suffix . $input['id'];
        }

        if (empty($company['seat_no']) == true) {
            $this->error_msg = '请确认您是否已被分配座机号？';
            return false;
        }

        $callOut = $this->repository->callcenterCallOut($uniqueKey, $mobile);

        if ($callOut['resp']['respCode'] == 0) {
            //成功记录日志  销售拨打记录
            $arr = array(
                'company_id' => $input['id'],
                'seat_no' => $company['seat_no'],
                'source_id' => $input['source'],
                'type' => $type,
                'call_id' => $callOut['resp']['callOut']['callId'],
                'c_time' => strtotime($callOut['resp']['callOut']['createTime']),
            );

            $callOutMod = Record::query();
            $res = $callOutMod->insertGetId($arr);
            if ($res) {
                return $res;
            } else {
                $this->error_msg = '呼出成功，日志记录失败';
                return false;
            }
        } elseif ($callOut['resp']['respCode'] == 102501) {
            $this->repository->callcenterSignIn($uniqueKey, '');
            $this->error_msg = '呼出失败，请重试';
            return false;
        } else {
            $this->error_msg = '呼出失败';
            return false;
        }
    }

    /**
     * 创建用户
     * @param $id 用户id
     * @param $mobile 用户手机号
     * @param string $leader_name 用户姓名
     * @param bool $is_sub 是否子账号
     * @return bool 返回信息
     */
    public function createUser($id, $mobile, $leader_name = '', $is_sub = false)
    {
        if (empty($id) == true) {
            $this->error_msg = 'id不能为空';
            return false;
        }

        if (empty($mobile) == true) {
            $this->error_msg = '机构手机号不能为空';
            return false;
        }

        if ($is_sub) {
            $suffix = $this->suffix_sub;
        } else {
            $suffix = $this->suffix;
        }

        $createUser = $this->repository->enterprisesCreateUser($suffix . $id, $mobile, $leader_name);
        //创建成功更新坐席号、初始密码
        if ($createUser['resp']['respCode'] == 0) {
            $tr_cno = $createUser['resp']['createUser']['number'];
            $upWhere = array('id' => $id);
            $upData = array('seat_no' => $tr_cno, 'seat_password' => '123456');
            if ($is_sub) {
                $result = $this->sub_account->update($upWhere, $upData);
            } else {
                $result = $this->company->update($upWhere, $upData);
            }
            if ($result == false) {
                $this->error_msg = '数据保存失败';
                return false;
            } else {
                return true;
            }
        } else {
            $this->error_msg = '创建失败';
            return false;
        }
    }

    /**
     * 删除用户座机号
     * @param Request $request
     * @return array
     */
    public function dropUser($id, $is_sub = false)
    {
        if (empty($id) == true) {
            $this->error_msg = 'id不能为空';
            return false;
        }

        if ($is_sub) {
            $suffix = $this->suffix_sub;
        } else {
            $suffix = $this->suffix;
        }

        $dropUser = $this->repository->enterprisesDropUser($suffix . $id);
        //删除成功更新坐席号、初始密码
        if ($dropUser['resp']['respCode'] == 0) {
            $upWhere = array('id' => $id);
            $upData = array('seat_no' => 0, 'seat_password' => '');
            if ($is_sub) {
                $result = $this->sub_account->update($upWhere, $upData);
            } else {
                $result = $this->company->update($upWhere, $upData);
            }

            if ($result == false) {
                $this->error_msg = '删除失败';
                return false;
            } else {
                return true;
            }
        } else {
            $this->error_msg = '删除失败';
            return false;
        }
    }

    /**
     * 签入接口
     * @param Request $request
     */
    public function signIn($id)
    {
        if (!empty($id) == true) {
            $where['id'] = intval($id);
        } else {
            exit('结束了');
        }
        $company = $this->company->getList($where, '*');
        //签入voip模式号码使用空
        foreach ($company['list'] as $value) {
            $this->repository->callcenterSignIn($this->suffix . $value['id'], '');
        }
    }

    public function isMobile($mobile = '')
    {
        if (preg_match ( '/^(1(3|4|5|7|8)[0-9])\d{8}$/', $mobile )) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getError()
    {
        return $this->error_msg;
    }
}