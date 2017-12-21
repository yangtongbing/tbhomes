<?php
/**
 * ClassName: PcConfigRepository
 * PC配置
 * @author  yangtongbing<yangtongbing@haodai.net>
 * @version 1.0
 */

namespace App\Repositories;

use App\Models\PcConfig;

class PcConfigRepository
{
    protected $mod;

    public function __construct(PcConfig $mod)
    {
        $this->mod = $mod;
    }

    /**
     * 列表
     */
    public function getList($where, $field = '*')
    {
        return $this->mod->select($field)->where($where)->get()->toArray();
    }

    public function getOne($where)
    {
        return $this->mod->where($where)->first();
    }

    /**
     * 创建
     */
    public function create($data)
    {
        $result = $this->mod->create($data);
        return $result;
    }

    public function updateOrCreate($data)
    {
        $result = $this->mod->updateOrCreate($data);
        return $result;
    }

    /**
     * 更新
     */
    public function update($id, $data)
    {
        $result = $this->mod->where('id', '=', $id)->update($data);
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