<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    //修改框架默认的created_at/updated_at
    const UPDATED_AT = 'u_time';
    const CREATED_AT = 'c_time';

    public $table = 'record';

    protected $guarded = [];

    protected $casts = [
        'push_config' => 'json',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    //修改框架自动维护的timestamp格式
    public function fromDateTime($value)
    {
        return strtotime(parent::fromDateTime($value));
    }
}
