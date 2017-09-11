<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayPushLog extends Model
{
    public $table = 'day_push_log';

    //修改框架默认的created_at/updated_at
    const UPDATED_AT = 'u_time';
    const CREATED_AT = 'c_time';


    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    //修改框架自动维护的timestamp格式
    public function fromDateTime($value)
    {
        return strtotime(parent::fromDateTime($value));
    }
}
