<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class SubAccount extends Model
{
    protected $table = "subAccount";
    
    protected $fillable = ['subAccount_ID','subAccount_User', 'subAccount_Type', 'subAccount_Password', 'subAccount_Balance', 'subAccount_RegisterDay', 'subAccount_LastLogin', 'subAccount_Status'];

	public $timestamps = false;
	
	//protected $primaryKey = 'subAccount_ID';
	

  
	public static function checkAccount($user){

	    $result = subAccount::where('subAccount_ID', $user)->first();
        return $result;
    }
    
    public static function insertSucAccount($arrayData){

	    $result = subAccount::insert($arrayData);
        return $result;
    }
    
    public static function getSucAccount($user){
	    
	    $data = subAccount::where('subAccount_User', $user)->where('subAccount_Status', '<>', -1)->get();

	    return $data;
    }
    
    public static function changeStatusSucAccount($user, $id, $status = 0){
	    $data = subAccount::where('subAccount_User', $user)->where('subAccount_ID', $id)->update(['subAccount_Status'=>$status]);

	    return $data;
    }
    
    
    public static function depositBalance($amount, $sub){
	    $sub = subAccount::where('subAccount_ID', $sub)->first();
	    $newAmount = $sub->subAccount_Balance + $amount;
		$newDeposit = $sub->subAccount_Deposit + $amount;
		
	    $data = subAccount::where('subAccount_ID', $sub->subAccount_ID)->update(['subAccount_Balance'=> $newAmount, 'subAccount_Deposit'=>$newDeposit]);

	    return $data;
    }
    
    public static function withdrawBalance($amount, $sub){
	    $sub = subAccount::where('subAccount_ID', $sub)->first();
	    $newAmount = $sub->subAccount_Balance - $amount;
		$newDeposit = $sub->subAccount_Withdraw + $amount;
		
	    $data = subAccount::where('subAccount_ID', $sub->subAccount_ID)->update(['subAccount_Balance'=> $newAmount, 'subAccount_Withdraw'=>$newDeposit]);

	    return $data;
    }
}
