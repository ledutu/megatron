<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class userBalance extends Model
{
    protected $table = "userBalance";

    protected $fillable = ['user','balance', 'create_at', 'update_at'];

    public $timestamps = true;

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

	
}
