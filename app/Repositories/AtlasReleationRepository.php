<?php
/**
 * ClassName: CompanyRepository
 * 公司信息
 * @author  yangtongbing<yangtongbing@haodai.net>
 * @version test:1.0
 */

namespace App\Repositories;


use App\Models\AtlasReleation;

class AtlasReleationRepository
{
    protected $mod;

    private $error;

    public function __construct()
    {
        $this->mod = new AtlasReleation();
    }

    public function getError()
    {
        return $this->error;
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
     * 创建
     */
    public function create($data)
    {
        $result = $this->mod->insertGetId($data);
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
}