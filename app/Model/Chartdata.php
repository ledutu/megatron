<?php

namespace App\Model;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Carbon\Carbon;


use DB;
class Chartdata extends Eloquent
{
	protected $connection = 'mongodb';
    protected $collection = 'chartdatas';
    protected $fillable = ['_id','close','open','symbol','status', 'time'];
    
    public $timestamps = false;
    
	public static function addLog($data){
		$insert = Chartdata::insert($data);
     	return $insert;
    }
	
	
}
