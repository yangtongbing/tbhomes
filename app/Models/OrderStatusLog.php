<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    //修改框架默认的created_at
    const CREATED_AT = 'c_time';

    public $table = 'order_status_log';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $fillable = ['id','oid','order_status','remark','c_time'];

    //修改框架自动维护的timestamp格式
    public function fromDateTime($value)
    {
        return strtotime(parent::fromDateTime($value));
    }

    public function setUpdatedAtAttribute($value)
    {
        // to Disable updated_at
    }
}
