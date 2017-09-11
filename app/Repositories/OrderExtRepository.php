<?php
/**
 * ClassName: CompanyRepository
 * 公司信息
 * @author  yangtongbing<yangtongbing@haodai.net>
 * @version test:1.0
 */

namespace App\Repositories;



use App\Models\OrderExt;

class OrderExtRepository
{
    protected $mod;

    public function __construct()
    {
        $this->mod = new OrderExt();
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

    public function getAll($where, $field = '*')
    {
        $data = $this->mod->select($field)->where($where)->get();
        if ($data === null) {
            return false;
        } else {
            $data = $data->toArray();
        }
        return $data;
    }

    public function getOne($field, $where)
    {
        $data = $this->mod->select($field)->where($where)->first();
        if ($data === null) {
            return false;
        } else {
            $data = $data->toArray();
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
     * 获取数量
     */
    public function count($where)
    {
        $count = $this->mod->where($where)->count();
        return $count;
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