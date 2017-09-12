<?php

namespace App\Models;

use App\Repositories\OutBoundRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Company extends Model
{
    //使用软删除
    use SoftDeletes;

    //修改框架默认的created_at/updated_at
    const UPDATED_AT = 'u_time';
    const CREATED_AT = 'c_time';

    public $table = 'company';

    protected $guarded = [];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'push_config' => 'json',
        'settlement_proportion' => 'json',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    //用户被删除时，同时删除外呼号
    public static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        static::deleted(function ($model) {
            $mod = new OutBoundRepository();
            $re = $mod->dropUser($model->id, false);
            Log::info('dropUser|' . $re);
        });
    }

    public function main_account()
    {
        return $this->hasMany(SubAccount::class);
    }

    //修改框架自动维护的timestamp格式
    public function fromDateTime($value)
    {
        return strtotime(parent::fromDateTime($value));
    }
}