<?php
/**
 * ClassName: ShopRepository
 * 商户信息
 * @version test:1.0
 */

namespace App\Repositories;

use App\Models\DayPushLog;

class DayPushLogRepository
{
    protected $mod;

    public function __construct()
    {
        $this->mod = new DayPushLog();
    }

    public function findOrCreate($data)
    {
        return $this->mod->firstOrCreate($data);
    }

    public function decrement($where, $number){
        $re = $this->mod->where($where)->decrement('push_number', $number);
        return $re;
    }

    public function getOne($field, $where)
    {
        $data = $this->mod->select($field)->where($where)->first();
        if($data === null){
            return false;
        }else{
            $data = $data->toArray();
        }
        return $data;
    }

    /**
     * 列表
     * @param $where
     * @param string $field
     * @return mixed
     */
    public function getList($where, $field = '*')
    {
        return $this->mod->select($field)->where($where)->get()->toArray();
    }

    /**
     * 创建
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        $result = $this->mod->create($data);
        return $result;
    }

    /**
     * 更新
     * @param $where
     * @param $data
     * @return mixed
     */
    public function update($where, $data)
    {
        $data['u_time'] = time();
        $result = $this->mod->where($where)->update($data);
        return $result;
    }

    /**
     * 删除
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $result = $this->mod->where('id', '=', $id)->delete();
        return $result;
    }
}