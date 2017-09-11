<?php
/**
 * ClassName: PcConfigRepository
 * PC配置
 * @author  zhaojipeng<zhaojipeng@haodai.net>
 * @version 1.0
 */

namespace App\Repositories;

use App\Models\Zone;

class ZoneRepository
{
    protected $mod;

    public function __construct()
    {
        $this->mod = new Zone();
    }

    public function getZoneList()
    {
        $field = ['zone_id','zone_name'];
        $where = [
            ['Rank','=','1']
        ];

        $info = $this->mod->select($field)->where($where)->get()->toArray();
        return array_pluck($info, 'zone_name', 'zone_id');
    }

}