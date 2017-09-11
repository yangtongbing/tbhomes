<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcConfig extends Model
{
    public $table = 'pc_config';

    public $timestamps = false;

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
}
