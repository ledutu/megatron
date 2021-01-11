<?php

namespace App\Http\Controllers\Cron;

use App\Model\Money;
use App\Model\User;
use App\Model\Wallet;
use App\Model\Log;
use App\Model\Investment;
use App\Model\GameBet;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use DB;

use App\Jobs\SendTelegramJobs;
use App\Jobs\CommissionResonanceJobs;
class InterestController extends Controller
{
  	public function getSales(Request $req){
      	//$totalTrade = 
    }
  	public function getComAgency(Request $req){
      	//get user 
		$mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday last week'));
		//$mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
		// $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('2019-11-01'));
		// thứ 2 tuần này
		$mondayThisWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
		//$mondayThisWeek = date('Y-m-d H:i:s');
		//lấy những user có đánh tuần trước
		$start = (strtotime($mondayLastWeek));
		$end = (strtotime($mondayThisWeek));
		$action = 60;
		$currency = 5;
		//lấy những user có đánh tuần trước
		$getUserBet = Money::join('users', 'User_ID', 'Money_User')
          					->where('Money_MoneyAction', 57)
          					->where('Money_USDT', '!=', 0)
          					->where('Money_Time', '>=', $start)
          					->where('Money_Time', '<', $end)
          					->where('Money_MoneyStatus', 1)
                            //->where('User_Tree', 'LIKE', '%457167%')
          					//->where('Money_User', 969399)
          					->get();
      	foreach($getUserBet as $item){
          	//dd($getUserBet, $item);
          	$amount = abs($item->Money_USDT);
            Money::checkCommissionBOAgency($item, $amount, $currency, $mondayLastWeek, $mondayThisWeek, $req);
        }
      	dd('check pay agency commission success');
    }
  
  	public function getIBRank(Request $req){
        $thisDay = date('N');
		if($thisDay != "1"){
			//dd('interest trade only pay on monday');
		}
		//get user 
	    $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday last week'));
	   	//$mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
	    // $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('2019-11-01'));
	    // thứ 2 tuần này
	    $mondayThisWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
 	    //$mondayThisWeek = date('Y-m-d H:i:s');
	    //lấy những user có đánh tuần trước
		$getUserBet = DB::table('statistical')
						->join('users', 'User_ID', 'statistical_User')
						//->where('User_Tree', 'LIKE', '%457167%')
						//->where('statistical_User', 945099)
						->where('statistical_Time', '>=', $mondayLastWeek)
						->where('statistical_Time', '<', $mondayThisWeek)
						->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email , User_Tree')
						->groupBy('statistical_User')
						->get();
 		//dd($getUserBet);
      	$timeToday = strtotime($mondayThisWeek);
		$action = 61;
      	$currency = 5;
		$packageArray = GameBet::getRank();
      	$arrPercent = [ 0=>0, 1=>0.01, 2=>0.005, 3=>0.003, 4=>0.0015, 5=>0.0007, 6=>0.00035, 7=>0.00015 ];
      	foreach($getUserBet as $item){
			$checkPaidDup = Money::where('Money_MoneyAction', $action)
									->where('Money_Comment', 'LIKE', "%$item->User_ID%")
									->where('Money_Time', '>=', $timeToday)
									->first();
			if($checkPaidDup){
				continue;
			}
          	$total_play_game = $item->totalBet;
	        $userTree = $item->User_Tree;
	        $usersArray = explode(',', $userTree);
	        $usersArray = array_reverse($usersArray);
			//% đã nhận được của parent
			$percentCurrent = 0;
			//chạy từ F1-F8
			for($i=1; $i<=7; $i++){
				if(!isset($usersArray[$i])){
					continue;
				}
				$info_parent = User::find($usersArray[$i]);
				if(!$info_parent){
					continue;
				}
				//lấy gói thoả điều kiện
				$getPackageParent = GameBet::getRankUser($usersArray[$i], $mondayLastWeek, $mondayThisWeek, 1);
				if($getPackageParent <= 0){
					continue;
				}
				//lấy dữ liệu của gói ra 
				$dataRank = $packageArray[$getPackageParent];
				if($dataRank['F'] < $i){
					continue;
				}
				$percentInterest = $arrPercent[$i];
				if($percentInterest <= 0){
					continue;
				}
				
				$amountInterest = $total_play_game*$percentInterest;
	            //save
                $arrayInsert[] = array(
                  'Money_User' => $info_parent->User_ID,
                  'Money_USDT' => $amountInterest,
                  'Money_USDTFee' => 0,
                  'Money_Time' => time(),
                  'Money_Comment' => 'Weekly Trade Commission $'.($total_play_game+0).' '.($percentInterest*100).'% From F'.$i.': '.$item->User_ID,
                  'Money_MoneyAction' => $action,
                  'Money_MoneyStatus' => 1,
                  'Money_Address' => null,
                  'Money_Currency' => $currency,
                  'Money_CurrentAmount' => $amountInterest,
                  'Money_Rate' => 1,
                  'Money_Confirm' => 0,
                  'Money_Confirm_Time' => null,
                  'Money_FromAPI' => 0,
                );
	            echo $info_parent->User_ID.' : $'.$amountInterest.' Weekly Trade Commission $'.($total_play_game+0).' '.($percentInterest*100).'% From F'.$i.': '.$item->User_ID.'<br>';

				continue;
			}
        }
        if($req->pay == 1){
          	$insert = Money::insert($arrayInsert);
        }
    }
  
    function floorp($val, $precision){
	    $mult = pow(10, $precision); // Can be cached in lookup table        
	    return floor($val * $mult) / $mult;
	}
	
}

