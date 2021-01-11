<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Model\User;

class Stringsession extends Model
{
	protected $table = 'string_session';
	public $timestamps = false;

	protected $fillable = ['user', 'sessionID', 'token'];

	protected $primaryKey = 'id';
	
	
}
