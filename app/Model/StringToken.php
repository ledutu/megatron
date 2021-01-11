<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StringToken extends Model
{
    //
    protected $table = 'string_token';

	public $timestamps = false;

	protected $fillable = ['ID', 'Token', 'User', 'Status', 'CreateDate'];

	protected $primaryKey = 'ID';
}
