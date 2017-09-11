<?php
/**
 * ClassName: UserRepository
 * 用户
 * @author  yangtongbing<yangtongbing@haodai.net>
 * @version test:1.0
 */
namespace App\Repositories;


use App\Models\User;

class UsersRepository
{
    protected $mod;

    public function __construct(User $mod)
    {
        $this->mod = $mod;
    }

    /**
     * 列表
     */
    public function getList($where, $field='*'){
        return $this->mod->select($field)->where($where)->get()->toArray();
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
    public function update($id, $data)
    {
        $data['u_time'] = time();
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