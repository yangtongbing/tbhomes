<?php
/**
 * ClassName: CompanyRepository
 * 公司信息
 * @author  yangtongbing<yangtongbing@haodai.net>
 * @version test:1.0
 */

namespace App\Repositories;


use App\Models\Company;
use Illuminate\Support\Facades\Log;

class CompanyRepository
{
    protected $mod;

    private $error;

    public function __construct()
    {
        $this->mod = new Company();
    }

    public function getError()
    {
        return $this->error;
    }

    public function getPushConfig()
    {
        //redis
        $field = ['id', 'push_config', 'week_push', 'holiday_push', 'amount', 'unit_price', 'day_push_number'];
        $where = [
            ['push_status', '=', 1],

        ];
        $data = $this->mod->select($field)->where($where)->get()->toArray();
        return $data;
    }

    /**
     * 列表
     */
    public function getList($where, $field = [])
    {
        $count = $this->mod->where($where)->count();
        $data = $this->mod->select($field)->where($where)->get()->toArray();
        return [
            'count' => $count,
            'list' => $data,
        ];
    }

    /**
     * 列表
     */
    public function getAll($field = [], $contains_delete = false)
    {
        if($contains_delete){
            $data = $this->mod->withTrashed()->select($field)->get()->toArray();
        }else{
            $data = $this->mod->select($field)->get()->toArray();
        }

        return $data;
    }

    /**
     * 创建
     */
    public function create($data)
    {
        $result = $this->mod->create($data);
        return $result;
    }

    /**
     * 更新
     */
    public function update($where, $data)
    {
        $data['u_time'] = time();
        $result = $this->mod->where($where)->update($data);
        return $result;
    }

    /**
     * 删除
     */
    public function delete($id)
    {
        $result = $this->mod->where('id', '=', $id)->delete();
        return $result;
    }

    public function getOne($field, $where)
    {
        $data = $this->mod->select($field)->where($where)->first()->toArray();
        return $data;
    }

    public function increaseAmount($company_id, $amount, $type, $remark)
    {
        Log::info('increaseAmount|input|company_id:' . $company_id . '|amount:' . $amount . '|remark:' . $remark);
        $where = [
            ['id', '=', $company_id]
        ];

        //查询企业积分，判断是否够扣的
        $company_info = $this->getOne('amount', $where);

        Log::info('increaseAmount|company_info|' . json_encode($company_info));

        $re = $this->mod->where($where)->increment('amount', $amount);

        if ($re) {
            //添加交易记录
            $data = [
                'company_id' => $company_id,
                'serial_number' => date('YmdHis') . getRandomStr('6', 3),
                'type' => $type,
                'change_type' => 1,
                'amount' => $amount,
                'remainlast' => $company_info['amount'],
                'remain' => bcadd($company_info['amount'], $amount, 2),
                'remark' => $remark
            ];

            Log::info('increaseAmount|create_account_log|' . json_encode($data));

            $accountLog = new AccountLogRepository();
            $accountLog->create($data);

            return true;
        } else {
            $this->error = '充值失败';
            return false;
        }
    }

    public function deductAmount($company_id, $amount, $type, $remark)
    {
        Log::info('deductAmount|input|company_id:' . $company_id . '|amount:' . $amount . '|remark:' . $remark);
        $where = [
            ['id', '=', $company_id]
        ];

        //查询企业积分，判断是否够扣的
        $company_info = $this->getOne('amount', $where);

        Log::info('deductAmount|company_info|' . json_encode($company_info));

        if ($company_info['amount'] < $amount) {
            $this->error = '用户余额不足';
            return false;
        }

        $re = $this->mod->where($where)->decrement('amount', $amount);

        if ($re) {
            //添加交易记录
            $data = [
                'company_id' => $company_id,
                'serial_number' => date('YmdHis') . getRandomStr('6', 3),
                'type' => $type,
                'change_type' => 2,
                'amount' => $amount,
                'remainlast' => $company_info['amount'],
                'remain' => bcsub($company_info['amount'], $amount, 2),
                'remark' => $remark
            ];

            Log::info('deductAmount|create_account_log|' . json_encode($data));

            $accountLog = new AccountLogRepository();
            $accountLog->create($data);

            return true;
        } else {
            $this->error = '扣除积分失败';
            return false;
        }
    }

    public function deductPushNumber($company_id)
    {
        $company_where = [
            ['id', '=', $company_id]
        ];

        //查询企业积分，判断是否够扣的
        $company_info = $this->getOne('day_push_number', $company_where);
        Log::info('deductPushNumber|company_push_number|' . json_encode($company_info));
        $dayPushLog = new DayPushLogRepository();

        $where = [
            ['company_id', '=', $company_id],
            ['year', '=', date('Y')],
            ['month', '=', date('m')],
            ['day', '=', date('d')],
        ];

        $data = [
            'company_id' => $company_id,
            'year' => date('Y'),
            'month' => date('m'),
            'day' => date('d')
        ];
        $info = $dayPushLog->findOrCreate($data);
        //查当天的记录是否存在，如果两个值都是-1，则用company_info里的day_push_number -1,更新记录
        //如果存在，则用dayPush里的push_number -1 更新记录
        if ($info->push_number === null && $info->all_push_number === null) {
            Log::info('deductPushNumber|create_day_push_log');
            $updateData = [
                'push_number' => $company_info['day_push_number'] - 1,
                'all_push_number' => $company_info['day_push_number'],
            ];

            $re = $dayPushLog->update($where, $updateData);
            if (!$re) {
                $this->error = '推送数量更新失败';
                return false;
            }
        } elseif ($info->push_number > 0 && $info->all_push_number > 0) {
            Log::info('deductPushNumber|update_day_push_log');
            $re = $dayPushLog->decrement($where, 1);
            if (!$re) {
                $this->error = '推送数量更新失败';
                return false;
            }
        } elseif ($info->push_number == 0 && $info->all_push_number > 0) {
            Log::info('deductPushNumber|run_out_day_push_log');
            $this->error = '当日推送数量已用完';
            return false;
        }

        Log::info('deductPushNumber|return true');

        return true;
    }

}