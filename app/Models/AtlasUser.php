<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtlasUser extends Model
{
    //使用软删除
    use SoftDeletes;

    public $table = 'atlas_user';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    //不用将要操作的字段全部写出来，
    protected $guarded = [];

}
