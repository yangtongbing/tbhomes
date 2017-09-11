<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public $table = 'users';

    //修改框架默认的created_at/updated_at
    const UPDATED_AT = 'u_time';
    const CREATED_AT = 'c_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
