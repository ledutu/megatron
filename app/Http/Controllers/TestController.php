<?php

namespace App\Http\Controllers;

use App\Model\User;
use App\Model\Money;
use App\Model\GameBet;
use Illuminate\Http\Request;
use Artisan;
use DB;

use App\Jobs\SendMailVNJobs;
class TestController extends Controller
{
    
    public $feeWithdraw = 0.02;
    public $feeTransfer = 0;
    public $feeSwap = 0;
    
    //
    public function getTest(Request $req){
      	abort(404);
      	$arr = [];
      	$coin = 5;
      	$action = 23;
      	$hash = '';
      	$priceCoin = 1;
      	foreach($arr as $userID=>$amount){
          	
          	$cmt = 'Deposit '.$amount.' USDT Insurance';
          	
            $money = new Money();
            $money->Money_User = $userID;
            $money->Money_USDT = $amount;
            $money->Money_Time = time();
            $money->Money_Comment = $cmt;
            $money->Money_Currency = 5;
            $money->Money_CurrencyFrom = $coin;
            $money->Money_MoneyAction = $action;
            $money->Money_Address = $hash;
            $money->Money_CurrentAmount = ($coin != 5 ? $amount / $priceCoin : $amount);
            $money->Money_Rate = $priceCoin;
            $money->Money_MoneyStatus = 1;
            //$money->save();
        }
        dd($arr);
      	$userID = 620740;
      	$coin = 5;
		$balancetemp = 0;
		$time = 0;
      	
		$userBalance = DB::table('balance_game')->where('user', $userID)->where('currency', $coin)->orderByDesc('time')->first();
		if($userBalance){
			$balancetemp += $userBalance->amount;
			$time = $userBalance->time;
		}
        
		$money = GameBet::where('GameBet_SubAccountUser', (int)$userID)->where('GameBet_Currency', (int)$coin)->where('GameBet_datetime', '>=', (int)$time)->sum('GameBet_AmountWin');

		$balancetemp += $money*1;
      	dd($balancetemp, time());
      	$user = User::find(566578);
	    $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday last week'));
	    // $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('2019-11-01'));
	    // thứ 2 tuần này
	    $mondayThisWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
 	    //$mondayThisWeek = date('Y-m-d H:i:s');
        $getPackageParent = GameBet::getRankUser($user->User_ID, $mondayLastWeek, $mondayThisWeek, 0);
      	dd($getPackageParent, time());
    }
    public function clearCache(Request $req){
		//Clear route cache
		if($req->route){
		    $exitCode = Artisan::call('route:cache');
		    return 'Routes cache cleared';
		}
		
		 //Clear config cache:
		if($req->config){
		    $exitCode = Artisan::call('config:cache');
		    return 'Config cache cleared';
		}
		
		// Clear application cache:
		if($req->cache){
		    $exitCode = Artisan::call('cache:clear');
		    return 'Application cache cleared';
		}
		
		 // Clear view cache:
		if($req->view){
		    $exitCode = Artisan::call('view:cache');
		    return 'View cache cleared';
		}
    }
}
