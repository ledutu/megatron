<?php

namespace App\Model;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Carbon\Carbon;


use DB;
class LogAdmin extends Model
{
    protected $table = 'log_admin';
    
    public $timestamps = false;
    

	public static function addLog($data = array()){

	    $log = new LogAdmin();
	    $log->adminLogGame_Close = $data['adminLogGame_Close'];
	    $log->adminLogGame_Open = $data['adminLogGame_Open'];
	    $log->adminLogGame_Type = $data['adminLogGame_Type'];
	    $log->adminLogGame_User = $data['adminLogGame_User'];
	    $log->adminLogGame_Status = $data['adminLogGame_Status'];
	    $log->adminLogGame_Log = $data['adminLogGame_Log'];
		return $log->save();
		
    }
	
}
