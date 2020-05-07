<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'params'    => 'array',
        'created_at' => 'datetime:Y-m-d h:i:s',
    ];

}
