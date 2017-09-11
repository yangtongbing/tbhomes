<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AccountLog extends Model
{
    
    public $table = 'account_log';

    //修改框架默认的created_at/updated_at
    const CREATED_AT = 'c_time';
    const UPDATED_AT = 'c_time';

    //protected $guarded = [];

    protected $fillable = ['id','company_id','serial_number','type','change_type','amount','remainlast','remain','remark','c_time'];

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
