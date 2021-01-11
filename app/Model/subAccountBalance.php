<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
use App\Model\GameBet;

class subAccountBalance extends Model{
    protected $table = "subAccountBalanceTemp";
    
    protected $fillable = ['id','sub', 'balance', 'datetime'];

	public $timestamps = false;
	
	public static function getBalance($sub){
		$balanceSub = 0;
		$subinfo = DB::table('subAccount')->where('subAccount_ID', $sub)->first();

		$balanceCheck = DB::table('subAccountBalanceTemp')->where('sub', $sub)->orderBy('id', 'DESC')->first();
		

		if(!$balanceCheck){
			$balanceSub += 0;
			
		}else{
				
			$bet = GameBet::where('GameBet_SubAccount', $subinfo->subAccount_ID)
							->where('GameBet_SubAccountUser', $subinfo->subAccount_User)
							->whereIn('GameBet_Status', [0,1,2,3])
							->where('GameBet_datetime', '>=', $balanceCheck->time)
							->sum('GameBet_AmountWin');
			$balanceSub = $balanceCheck->balance+$bet;

		}
		return $balanceSub;
	}
	
	
}
