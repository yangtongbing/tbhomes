<?php
/**
 * ClassName: ZoneRepository
 * 城市表
 * @author  yangtongbing<yangtongbing@haodai.net>
 * @version test:1.0
 */

namespace App\Repositories;

use App\Models\AtlasAdmin;
use App\Models\Zone;

class ZoneRepository
{
    protected $mod;

    public function __construct()
    {
        $this->mod = new Zone();
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
        $data = $this->mod->select($field)->where($where)->first();
        if ($data) {
            return $data->toArray();
        } else {
            return false;
        }
    }

    public function addAll($data)
    {
        return $this->mod->insert($data);
    }
}