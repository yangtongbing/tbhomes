<?php
/**
 * ClassName: RecordRepository
 * 录音管理
 * @author  yangtongbing<yangtongbing@haodai.net>
 * @version test:1.0
 */

namespace App\Repositories;


use App\Models\Record;

class RecordRepository
{
    protected $mod;

    public function __construct()
    {
        $this->mod = new Record();
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

    /**
     * 更新
     */
    public function update($where, $data)
    {
        $data['u_time'] = time();
        $result = $this->mod->where($where)->update($data);
        return $result;
    }
}