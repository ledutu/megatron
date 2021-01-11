<?php

namespace App\Model;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Carbon\Carbon;


use DB;
class GameCoin extends Eloquent
{
	protected $connection = 'mongodb';
    protected $collection = 'gamecoins';
    protected $fillable = ['_id','GameCoin_Data','GameCoin_Order', 'GameCoin_Time'];
    
    public $timestamps = true;
    
/*
    public function Action(){
	    return $this->hasMany('App\Model3\ActionName','Action_ActionID');
    }
*/
	
	
}
