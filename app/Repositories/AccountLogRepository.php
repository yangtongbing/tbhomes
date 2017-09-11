<?php
/**
 * ClassName: AccountLogRepository
 * 发票类
 * @author      wanguo<wanguochao@haodai.com>
 * @version     test:1.0
 */

namespace App\Repositories;

use App\Models\AccountLog;

class AccountLogRepository
{
    protected $mod;

    public function __construct()
    {
        $this->mod = new AccountLog();
    }

    /**
     * 列表
     */
    public function getList($where, $field = '*')
    {
        $count = $this->mod->where($where)->count();
        $data = $this->mod->select($field)->where($where)->get()->toArray();
        return [
            'count' => $count,
            'list' => $data,
        ];
    }

    public function getCount($where)
    {
        $count = $this->mod->where($where)->count();
        return $count;
    }

    public function getSum($where, $field)
    {
        $sum = $this->mod->where($where)->sum($field);
        return $sum;
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

}