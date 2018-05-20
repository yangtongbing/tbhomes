<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    //修改框架默认的created_at/updated_at
    const UPDATED_AT = 'u_time';
    const CREATED_AT = 'c_time';

    public $table = 'zone';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    //不用将要操作的字段全部写出来，
    protected $guarded = [];

    //修改框架自动维护的timestamp格式
    public function fromDateTime($value)
    {
        return strtotime(parent::fromDateTime($value));
    }
}
