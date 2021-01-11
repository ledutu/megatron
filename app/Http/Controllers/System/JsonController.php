<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Money;
use App\Model\Investment;
use App\Model\User;
use App\Model\GameStatistical;
use DB;
use GuzzleHttp\Client;

class JsonController extends Controller{
	
	public static function getListGame(Request $req){
		// lấy danh sách loại game
		$TypeGame = DB::table('sox_game')->where('Status', 1)->where('Type', '!=', 'SportsBook')->select('System', 'Type')->groupBy('System')->get();
		$gameList = DB::table('sox_game');
		$Type = 'Blackjack';
		$System = 'ezugi';
		if($req->System){
			// lấy danh sách game
			$System = $req->System;
			$gameList = $gameList->where('Status', 1)->where('System', $System)->select('_id', 'gameName', 'ImageURL');
			}
		if($req->Type and $req->Type == 'All')	{
				$gameList =	$gameList->where('Status', 1)->select('_id', 'gameName', 'ImageURL');
			}	
		if($req->Type and $req->Type !== 'All') {
			$Type = $req->Type;
			$gameList = $gameList->where('Status', 1)->where('Type', $Type)->select('_id', 'gameName', 'ImageURL');
		}
		if(!$req->System and !$req->Type)
	    {
				$gameList = $gameList->where('Status', 1)->where('System', $System)->select('_id', 'gameName', 'ImageURL');
			}
		
			$gameList =	$gameList->get();
	    
	    if($TypeGame){
		    $return['status'] = 'OK';
			$return['System'] = $TypeGame;
			$return['GameList'] = $gameList;
			return response()->json($return);
	    }
	    dd($gameList);
		
	}
	
	public static function getBalanceLottery(){
		$user = User::find(Session('user')->User_ID);

		$TID = uniqid();
	    $pwd = config('sonix.pwd');
	    $ip = config('sonix.ip');
	    $key = config('sonix.key');
		$urlAPI = config('sonix.urlAPI');
		$system = 'Lottery';
		$hash = md5('User/GetBalance/'.$ip.'/'.$TID.'/'.$key.'/'.$system.'/'.$user->User_Name.'/'.$pwd);
	    $api = $urlAPI.'game/user_balance/'.$key.'?tid='.$TID.'&login='.$user->User_Name.'&system='.$system.'&hash='.$hash;
 
		
	    
	    $client = new \GuzzleHttp\Client();
		$response = $client->request('GET', $api);

		$data = $response->getBody(true)->getContents();
		$dataExplode = explode(',', $data);
		if($dataExplode[0] == 1){
			$return['status'] = 'OK';
			$return['balance'] = $dataExplode[1];
			return response()->json($return);
		}
	}
	
	public static function getBalanceSportsbook(){
		$user = User::find(Session('user')->User_ID);

		$TID = uniqid();
	    $pwd = config('sonix.pwd');
	    $ip = config('sonix.ip');
	    $key = config('sonix.key');
		$urlAPI = config('sonix.urlAPI');
		$system = 'Sportsbook';
		$hash = md5('User/GetBalance/'.$ip.'/'.$TID.'/'.$key.'/'.$system.'/'.$user->User_Name.'/'.$pwd);
	    $api = $urlAPI.'game/user_balance/'.$key.'?tid='.$TID.'&login='.$user->User_Name.'&system='.$system.'&hash='.$hash;
 
		
	    
	    $client = new \GuzzleHttp\Client();
		$response = $client->request('GET', $api);

		$data = $response->getBody(true)->getContents();
		$dataExplode = explode(',', $data);
		if($dataExplode[0] == 1){
			$return['status'] = 'OK';
			$return['balance'] = $dataExplode[1];
			return response()->json($return);
		}
		
	}
	
    public static function getHistoryGame(Request $req){
        $history = DB::table('sonix_log')->where('user', Session('user')->User_ID)->select('amount', 'type', 'center', 'seqPlay', 'datetime')->orderBy('id', 'DESC')->paginate(10);
        return response()->json($history);
    }
    
    public static function getStatistical(){
	    $user = User::find(Session('user')->User_ID);
	    $response['status'] = 'OK';
	    $response['active'] = 0;
	    if($user->User_EmailActive){
		    $response['active'] = 1;
	    }
	    $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
	    $current = date('Y-m-d H:i:s');
	    
	    $response['User_Master'] = GameStatistical::getMaster($user->User_ID, $monday, $current);
	    $response['User_TotalDeposit'] = $user->User_TotalDeposit*1;
	    $response['User_TotalWithdraw'] = $user->User_TotalWithdraw*1;
	    
	    
// 		$f1 = User::getF1($user->User_ID);
		$countChild = User::getFMember($user->User_ID, 3);
	    $response['NumberF'] = $countChild[0];
			//total volumes
			$totalvolumes = User::TotalVolumeF($user->User_ID, 3);
			$response['VolumeF'] = $totalvolumes;
			
	    $statistical = DB::table('statistical')->where('statistical_User', $user->User_ID)->whereRaw('statistical_Time >= "'.date('Y-m-d 00:00:00', strtotime('monday this week')).'"')->first();
	    $response['total_amount_attack'] = 0;
	    $response['total_win'] = 0;
	    $response['total_lose'] = 0;
	    if($statistical){
		   	$response['total_amount_attack'] = $statistical->statistical_TotalBet*1;
		    $response['total_win'] = $statistical->statistical_TotalWin*1;
		    $response['total_lose'] = $statistical->statistical_TotalLost*1; 
	    }
	    
		// system
		$betList = User::selectRaw("SUM(`statistical_TotalBet`) as TotalBet, SUM(`statistical_TotalWin`) as TotalWin, SUM(`statistical_TotalLost`) as TotalLost")
                        ->join('statistical', 'statistical_User', 'User_ID')
                        ->whereRaw('User_Tree LIKE "'.Session('user')->User_Tree.'%"')
						->where('User_ID','<>',Session('user')->User_ID)
						->whereRaw('statistical_Time >= "'.date('Y-m-d 00:00:00', strtotime('monday this week')).'"')
						->whereRaw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count(Session('user')->User_Tree, ',') . " <= 3")
						->orderBy('User_RegisteredDatetime','DESC')
                        ->first();
//                         ->toSql();dd($betList);
		
		$response['system_total_amount_attack'] = 0;
	    $response['system_total_win'] = 0;
	    $response['system_total_lose'] = 0;
	    $response['system_profit'] = 0;
	    if($betList){
			$response['system_total_amount_attack'] = $betList->TotalBet*1;
		    $response['system_total_win'] = $betList->TotalWin*1;
		    $response['system_total_lose'] = $betList->TotalLost*1;
		    if($betList->TotalLost - $betList->TotalWin > 0){
			    switch ($response['User_Master']){
				    case 1:
				    	$response['system_profit'] = (($betList->TotalLost - $betList->TotalWin)*0.05)*1;
				    	break;
				    case 2:
				    	$response['system_profit'] = (($betList->TotalLost - $betList->TotalWin)*0.03)*1;
				    	break;
				    case 3:
				    	$response['system_profit'] = (($betList->TotalLost - $betList->TotalWin)*0.01)*1;
				    	break;
			    }
		    }
	    }
	    
	    
		$invest = Investment::where('investment_User', Session('user')->User_ID)->where('investment_Status', 1)->selectRaw('SUM(`investment_Amount`*`investment_Rate`) as invest')->first();
		$response['total_invest'] = 0;
		if($invest){
			$response['total_invest'] = round($invest->invest, 4)*1;
		}
		
		$totalInvest = Investment::where('investment_Status', 1)->whereRaw('investment_User IN (SELECT `User_ID` FROM `users` WHERE `User_Tree` LIKE "'.Session('user')->User_Tree.'%")')->selectRaw('SUM(`investment_Amount`*`investment_Rate`) as invest')->where('investment_User', '!=', Session('user')->User_ID)->first();
		$response['total_nember_invest'] = 0;

		if($totalInvest->invest!=null){
			$response['total_nember_invest'] = $totalInvest->invest*1;
		}
		
		$response['Agency_Level'] = $user->User_Agency_Level;
		$response['Bonus_Deposit'] = self::checkBonusDeposit($user->User_ID);
		$response['Total_Refund'] = self::refundBet($user->User_ID);
		$response['Total_Master'] = self::masterCommission($user->User_ID);
	    return response()->json($response);
	    dd($f1);
    }
    
    public static function checkBonusDeposit($userID, $from = null, $to = null){
	    
	    if($from){
		    $mondayLastWeek = $from;
	    }else{
		    $mondayLastWeek = strtotime('monday this week');
	    }
	    if($to){
		    $mondayThisWeek = $to;
	    }else{
		    $mondayThisWeek = time();
	    }
	    
		$parent = User::join('profile', 'Profile_User', 'User_ID')->where('Profile_Status', 1)->where('User_ID',$userID)->first();
		if(!$parent){
			return false;
		}
		//check Bet User 500$
		$getBetUser = DB::table('statistical')
						->join('users', 'User_ID', 'statistical_User')
						->where('statistical_User', $parent->User_ID)
						->where('statistical_Time', '>=', date('Y-m-d 00:00:00', $mondayLastWeek))
						->where('statistical_Time', '<', date('Y-m-d 23:59:59', $mondayThisWeek))
						->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email , User_Name, User_Tree')
						->value('totalBet');
		if($getBetUser < 500){
			return false;
		}
		//lấy user deposit đủ 50$
		$getChildDeposit = Money::join('users', 'User_ID', 'Money_User')
								->where('User_Parent', $parent->User_ID)
								->where('Money_MoneyAction', 1)
								->where('Money_Time', '>=', $mondayLastWeek)
								->where('Money_Time', '<', $mondayThisWeek)
								->selectRaw('SUM(`Money_USDT`) as amount, User_ID, User_Parent')
								->groupBy('Money_User')
								->having('amount', '>=', '50')
								->pluck('User_ID')->toArray();
// 									->get();
		$countChildDeposit = count($getChildDeposit);
		if($countChildDeposit < 10){
			return false;
		}
		//đếm người bet đủ 500$
		$getChildBet = DB::table('statistical')
						->join('users', 'User_ID', 'statistical_User')
						->whereIn('statistical_User', $getChildDeposit)
						->where('statistical_Time', '>=', date('Y-m-d 00:00:00', $mondayLastWeek))
						->where('statistical_Time', '<', date('Y-m-d 23:59:59', $mondayThisWeek))
						->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email , User_Name, User_Tree')
						->groupBy('statistical_User')
						->having('totalBet', '>=', 500)
						->get()->count();

		if($getChildBet < 10){
			return false;
		}	
		//kiểm tra KYC
		$checkKYC = Profile::whereIn('Profile_User', $getChildDeposit)->where('Profile_Status', 1)->select('Profile_User')->get();
		if(count($checkKYC) < 10){
			return false;
		}
		return true;
    }
	
	public static function refundBet($userID, $from = null, $to = null){
		//get user 
	    if($from){
		    $mondayLastWeek = $from;
	    }else{
		    $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
	    }
	    if($to){
		    $mondayThisWeek = $to;
	    }else{
		    $mondayThisWeek = date('Y-m-d H:i:s');
	    }
	    //lấy những user có đánh tuần 
		$getUserBet = DB::table('statistical')
						->join('users', 'User_ID', 'statistical_User')
						->where('User_Tree', 'LIKE', '%'.$userID.'%')
						->where('statistical_User', '!=', "$userID")
// 						->where('statistical_User', "DAF9550472")
						->where('statistical_Time', '>=', $mondayLastWeek)
						->where('statistical_Time', '<', $mondayThisWeek)
						->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email , User_Name, User_Tree')
						->groupBy('statistical_User')
						->get();
		//chưa chặn cron chạy
		$arrPercent = [0=>0.0025, 1=>0.005, 2=>0.003, 3=>0.001];
		$totalRefund = 0;
		foreach($getUserBet as $item){
			$total_play_game = $item->totalBet;
	        $userTree = $item->User_Tree;
	        $usersArray = explode(',', $userTree);
	        $usersArray = array_reverse($usersArray);
			//chạy từ F1-F8
			for($i=0; $i<=3; $i++){
				//lấy gói thoả điều kiện
				if(!isset($usersArray[$i])){
					continue;
				}
				
				$info_parent = User::find($usersArray[$i]);
				if(!$info_parent){
					continue;
				}
					
				$parentBet = DB::table('statistical')
								->join('users', 'User_ID', 'statistical_User')
								->where('statistical_User', $info_parent->User_ID)
								->where('statistical_Time', '>=', $mondayLastWeek)
								->where('statistical_Time', '<', $mondayThisWeek)
								->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email , User_Name, User_Tree')
								->orderByDesc('statistical_ID')
								->first();
								
				if(!isset($parentBet) || $parentBet->totalBet < 500){
					continue;
				}
				$percentRefund = $arrPercent[$i];
				$amountRefund = $total_play_game*$percentRefund;
	            if($usersArray[$i] == $userID){
		            $totalRefund += $amountRefund;
	            }
				continue;
			}
		}
		return $totalRefund;
	}
	
	
	public static function masterCommission($userID, $from = null, $to = null){
	    if($from){
		    $mondayLastWeek = $from;
	    }else{
		    $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
	    }
	    if($to){
		    $mondayThisWeek = $to;
	    }else{
		    $mondayThisWeek = date('Y-m-d H:i:s');
	    }
	    //lấy những user có đánh tuần 
		$getUserBet = DB::table('statistical')
						->join('users', 'User_ID', 'statistical_User')
						->where('User_Tree', 'LIKE', '%'.$userID.'%')
						->where('statistical_User', '!=', "$userID")
// 						->where('statistical_User', "DAF9550472")
						->where('statistical_Time', '>=', $mondayLastWeek)
						->where('statistical_Time', '<', $mondayThisWeek)
						->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email , User_Name, User_Tree')
						->groupBy('statistical_User')
						->get();
						
		$packageArray = [
			1 => ['name'=>'MASTER 1', 'percent'=>0.01, 'f'=>1, 'image'=>'assets/images/user.png'],
			2 => ['name'=>'MASTER 2', 'percent'=>0.03, 'f'=>2, 'image'=>'assets/images/level/1.png'],
			3 => ['name'=>'MASTER 3', 'percent'=>0.05, 'f'=>3, 'image'=>'assets/images/level/2.png'],
		];
		$totalMaster = 0;
		//chưa chặn cron chạy
		foreach($getUserBet as $item){
			$total_play_game = $item->totalBet;
			
	        $userTree = $item->User_Tree;
	        $usersArray = explode(',', $userTree);
	        $usersArray = array_reverse($usersArray);
			//% đã nhận được của parent
			$percentCurrent = 0;
			//chạy từ F1-F8
			for($i=1; $i<count($usersArray); $i++){
				//lấy gói thoả điều kiện
				$getPackageParent = GameStatistical::getMaster($usersArray[$i], $mondayLastWeek, $mondayThisWeek);
				if($getPackageParent <= 0){
					continue;
				}
				$info_parent = User::find($usersArray[$i]);
				if(!$info_parent){
					continue;
				}
				//lấy dữ liệu của gói ra 
				$dataInterest = $packageArray[$getPackageParent];
				
				//số % nhận được = số % package của parent - số % của user con
				$percentInterest = $dataInterest['percent'] - $percentCurrent;
				//update percent parent
				$percentCurrent = $dataInterest['percent'];
				//thấp cấp hơn => ko trả
				if($percentInterest < 0){
					continue;
				}elseif($percentInterest == 0){
					// hoa hồng đồng cấp
					$percentSameLevel = 0.1;
					if(!isset($amountInterest)){
						$amountInterest = $total_play_game*$percentInterest;
						$amountInterest = $amountInterest*$percentSameLevel;
					}else{
						$amountInterest = $amountInterest*$percentSameLevel;
					}

		            if($usersArray[$i] == $userID){
			            $totalMaster += $amountInterest;
		            }
					continue;
				}
				
				$amountInterest = $total_play_game*$percentInterest;
				$amountSameLevel = $amountInterest;

	            if($usersArray[$i] == $userID){
		            $totalMaster += $amountInterest;
	            }
			}
		}
		return $totalMaster;
	}
	
}
