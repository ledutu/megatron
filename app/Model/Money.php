<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Model\User;
use App\Model\Investment;
use App\Model\Eggs;
use App\Model\GameBet;
use App\Model\Money;
use App\Model\Log;
use App\Model\MoneyAction;

use App\Jobs\SendTelegramJobs;
class Money extends Model
{
	protected $table = 'money';
	public $timestamps = false;

	protected $fillable = ['Money_ID', 'Money_Game', 'Money_User', 'Money_BetAction', 'Money_USDT', 'Money_USDT_Return', 'Money_USDTFee', 'Money_Time', 'Money_Comment', 'Money_MoneyAction', 'Money_MoneyStatus', 'Money_BinaryWeak', 'Money_Package', 'Money_TXID', 'Money_Address', 'Money_Currency', 'Money_Rate', 'Money_Confirm', 'Money_Active', 'Money_FromAPI'];

	protected $primaryKey = 'Money_ID';
  


  	public static function checkSpamAction($userID){
      	$checkSpam = Money::where('Money_User', $userID)->whereIn('Money_MoneyAction', [2,7,21,22,55,56,57])->where('Money_MoneyStatus', 1)->groupBy('Money_Time')->havingRaw('Count(*) > 1')->first();
      	if($checkSpam){
          	$user = User::find($userID);
          	if($user->User_Block == 1){
              	return true;
            }
          	$user->User_Block = 1;
          	$user->save();
            Log::insertLog($userID, "Block Spam Money", 0, 'User Block Spam Money');
            $message = "<b> BLOCK SPAM MONEY </b>\n"
                      . "PROJECT: <b>IG TRADE</b>\n"
                      . "ID: <b>$user->User_ID</b>\n"
                      . "NAME: <b>$user->User_Name</b>\n"
                      . "EMAIL: <b>$user->User_Email</b>\n"
                      . "<b>Submit Withdraw Time: </b>\n"
                      . date('d-m-Y H:i:s',time());
            dispatch(new SendTelegramJobs($message, -398297366));
          	return true;
        }
    }
  
	public static function getHistoryUser($userID){
		$history = Money::leftJoin('moneyaction', 'MoneyAction_ID', 'Money_MoneyAction')->leftJoin('currency', 'Currency_ID', 'Money_Currency')->where('Money_User', $userID)->where('Money_MoneyStatus', 1)->get();
		return $history;
	}

	public static function getCheckConfirm($id){
		$money = Money::where('Money_ID', $id)->whereIn('Money_MoneyAction', [2])->first();
		return $money;
	}

	public static function checkBuyAgency($userID){
		$checkBuy = Money::where('Money_MoneyAction', 57)->where('Money_MoneyStatus', 1)->where('Money_User', $userID)->first();
		if($checkBuy){
			return 1;
		}
		return 0;
	}
  
  	public static function checkRankLevel($user, $start, $end){
     	//$f1BuyAgency = Money::leftJoin('users', 'User_ID', 'Money_User')->where([
        //  'User_Parent' => $user->User_ID,
          
        //])
      
      	$dailyCondition = [
          0 => 0,
          3 => 1500,
          4 => 3500,
          5 => 8000,
          6 => 15000,
          7 => 30000,
          8 => 60000,
        ];
          
        $f1 = User::where('User_Parent', $user->User_ID)->pluck('User_ID');
      	$f1BuyAgency = Money::join('users', 'Money_User', 'User_ID')->select(['Money_User', 'User_Email'])->where([
          'User_Parent' => $user->User_ID,
          'Money_MoneyAction' => 57,
          'Money_MoneyStatus' => 1,
        ])->get();
      	$test = GameBet::whereIn('GameBet_SubAccountUser', $f1)->get();
      	$gameBet = GameBet::whereIn('GameBet_SubAccountUser', $f1)->sum('GameBet_Amount');
      
      	return $f1BuyAgency;
      	//get buy agency
      	$countAgency = 0;
      	
      
    }

	public static function checkCommissionBOAgency($user, $amount, $currency, $mondayLastWeek, $mondayThisWeek, $req){
		$arrParent = explode(',', $user->User_Tree);
		$arrParent = array_reverse($arrParent);
		$commissionArr = array(1=>0.5, 2=>0.25, 3=>0.12, 4=>0.06, 5=>0.03, 6=>0.015, 7=>0.007);
		$arrayInsert = [];
		$packageArray = GameBet::getRank();
		$action = 60;
		for($i = 1; $i <= 7; $i++){
			if(!isset($arrParent[$i])) continue;

			$checkUser = User::find($arrParent[$i]);
			if(!$checkUser) continue;

			$checkPaidDup = Money::where('Money_MoneyAction', $action)
									->where('Money_Comment', 'LIKE', "%$user->User_ID%")
              						->where('Money_User', $checkUser->User_ID)
									->first();
			if($checkPaidDup){
				continue;
			}
			$checkBuyAgency = Money::checkBuyAgency($checkUser->User_ID);
			if(!$checkBuyAgency) continue;

            //lấy gói thoả điều kiện
            $getPackageParent = GameBet::getRankUser($checkUser->User_ID, $mondayLastWeek, $mondayThisWeek, 0);
            if($getPackageParent <= 0){
              continue;
            }
            //lấy dữ liệu của gói ra 
            $dataRank = $packageArray[$getPackageParent];
            if($dataRank['F'] < $i){
              continue;
            }
          
			$commission = 0;
			$commission = (float) (($amount) * $commissionArr[$i]);
			$comment = 'Comission Buy Agency '.($commissionArr[$i]*100).'% from F'.$i.' ID:'.$user->User_ID;
			$arrayInsert[] = array(
				'Money_User' => $checkUser->User_ID,
				'Money_USDT' => $commission,
				'Money_USDTFee' => 0,
				'Money_Time' => time(),
				'Money_Comment' => $comment,
				'Money_MoneyAction' => $action,
				'Money_MoneyStatus' => 1,
				'Money_Address' => null,
				'Money_Currency' => $currency,
				'Money_CurrentAmount' => $commission,
				'Money_Rate' => 1,
				'Money_Confirm' => 0,
				'Money_Confirm_Time' => null,
				'Money_FromAPI' => 0,
			);
            echo $checkUser->User_ID.' : $'.$commission.' Comission Buy Agency '.($commissionArr[$i]*100).'% from F'.$i.' ID:'.$user->User_ID.'<br>';
		}

		if(count($arrayInsert) && $req->pay == 1){
			Money::insert($arrayInsert);
		}

		return true;
	}

	public static function feeGas(){
		$rateETH = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy('ETH');
		
		$getLastedGas = DB::table('gas')->orderByDesc('id')->first();
		if(!$getLastedGas || (time()- $getLastedGas->time >= $getLastedGas->duration)){
			$json = json_decode(file_get_contents('https://api.etherscan.io/api?module=gastracker&action=gasoracle&apikey=GMGAYV28HNBZSAHUQQD3PQDXMFGZU7BMBP'));
			$pricegas = 150;
			if($json->message == 'OK'){
				$pricegas = $json->result->FastGasPrice;
			}
		    
			$timeChange = 1800;
		    $data = [
			    'amount' => $pricegas,
			    'time' => time(),
			    'duration' => $timeChange,
		    ];
		    DB::table('gas')->insert($data);
		}else{
			$pricegas = $getLastedGas->amount;
		}
		$pricegas = $pricegas/1000000000;
		$feeGas = $pricegas*42000*$rateETH;
		$feeGas = $feeGas*1.15;
      	return $feeGas;
	}
	
	public static function checkCommission($user, $action, $currency, $amount){
		$moneyAction = MoneyAction::where('MoneyAction_ID', $action)->first();
        $arrParent = explode(',', $user->User_Tree);
        $arrParent = array_reverse($arrParent);
		//đổi % hoa hồng từ ngày 21/10
		$CommissionArr = array(1=>0.05, 2=>0.01, 3=>0.005);
		$arrayInsert = [];
		// dd($arrParent, $actionNameArray[$action]);
        for($i = 1; $i<=3; $i++){
			if(!isset($arrParent[$i])){
				continue;
			}
			if(isset($arrParent[$i])){
				$checkUser = User::find($arrParent[$i]);
				if(!$checkUser){
					continue;
				}
				$checkEggsActive = Eggs::where('Owner', $checkUser->User_ID)->where('ActiveTime', '>', 0)->where('Status', 1)->orderBy('ActiveTime')->first();
				$checkFishLive = Fishs::where('Owner', $checkUser->User_ID)->where('Status', 1)->first();
				if(!$checkEggsActive && !$checkFishLive){
					$checkHippoLive = Item::where('Owner', $checkUser->User_ID)->where('Type', 'IH')->where('Status', 1)->where('Pool', "!=", "0")->first();
					if(!$checkHippoLive){
						continue;
					}
				}
				if($i >= 2){
					$getChild = User::where('User_Parent', $checkUser->User_ID)->pluck('User_ID')->toArray();
					$checkEggsActive = Eggs::where('Owner', $checkUser->User_ID)
											->where('ActiveTime', '>', 0)
											->whereIn('Status', [1,2])
											->orderBy('ActiveTime')->first();
					$countChildEggsActive = Eggs::whereIn('Owner', $getChild)
												// ->where('ActiveTime', '>', $checkEggsActive->ActiveTime)
												->select('ID', 'Owner')
												->groupBy('Owner')
												->get()->count();
					if($i == 2){
						if($countChildEggsActive < 2){
							continue;
						}
					}elseif($i == 3){
						if($countChildEggsActive < 5){
							continue;
						}
					}else{
						continue;
					}
				}
				$Commission = 0;
				$Commission = (float)(($amount)*$CommissionArr[$i]);
				$Comment = $moneyAction->MoneyAction_Name.' from F'.$i.' ID:'.$user->User_ID;
				$arrayInsert[] = array(
					'Money_User' => $checkUser->User_ID,
					'Money_USDT' => $Commission,
					'Money_USDTFee' => 0,
					'Money_Time' => time(),
					'Money_Comment' => $Comment,
					'Money_MoneyAction' => $action,
					'Money_MoneyStatus' => 1,
					'Money_Address' => null,
					'Money_Currency' => $currency,
					'Money_CurrentAmount' => $Commission,
					'Money_Rate' => 1,
					'Money_Confirm' => 0,
					'Money_Confirm_Time' => null,
					'Money_FromAPI' => 0,
				);
			}
			
        }
		// dd($arrayInsert);
        if(count($arrayInsert)){
            Money::insert($arrayInsert);
        }
        return true;
	}

	public static function checkAgencyCommission($user, $actionPaid, $currency, $amount){
		//bỏ hoa hồng cấp bậc từ ngày 21/10
		return false;
		$moneyAction = MoneyAction::where('MoneyAction_ID', $actionPaid)->first();
        $arrParent = explode(',', $user->User_Tree);
        $arrParent = array_reverse($arrParent);
		$CommissionArr = array(1=>0.005, 2=>0.01, 3=>0.015, 3=>0.02, 3=>0.03);
		$arrayInsert = [];
		$percentCurrent = 0;
		$percentSameLevel = 0.005;
		$action = 10;
		$actionSameRank = 11;
        for($i = 1; $i<count($arrParent); $i++){
			if(!isset($arrParent[$i])){
				continue;
			}
			$checkUser = User::find($arrParent[$i]);
			if(!$checkUser){
				continue;
			}
			if(!isset($CommissionArr[$checkUser->User_Agency_Level])){
				continue;
			}
			//số % nhận được = số % package của parent - số % của user con
			$percentInterest = $CommissionArr[$checkUser->User_Agency_Level] - $percentCurrent;
			//update percent parent
			$percentCurrent = $CommissionArr[$checkUser->User_Agency_Level];
			if($percentInterest < 0){
				continue;
			}elseif($percentInterest == 0){
				if($checkUser->User_Agency_Level < 2){
					continue;
				}
				// hoa hồng đồng cấp
				if(!isset($amountInterest)){
					$amountInterest = $amount*$percentInterest;
					$amountInterest = $amount*$percentSameLevel;
				}else{
					$amountInterest = $amount*$percentSameLevel;
				}
				$Comment = 'Same Level Rank '.$checkUser->User_Agency_Level.' '.($percentSameLevel*100).'% Commission From ID:'.$arrParent[$i-1];
				$arrayInsert[] = array(
					'Money_User' => $checkUser->User_ID,
					'Money_USDT' => $amountInterest,
					'Money_USDTFee' => 0,
					'Money_Time' => time(),
					'Money_Comment' => $Comment,
					'Money_MoneyAction' => $actionSameRank,
					'Money_MoneyStatus' => 1,
					'Money_Address' => null,
					'Money_Currency' => $currency,
					'Money_CurrentAmount' => $amountInterest,
					'Money_Rate' => 1,
					'Money_Confirm' => 0,
					'Money_Confirm_Time' => null,
					'Money_FromAPI' => 0,
				);
				continue;
			}
			$amountInterest = (float)(($amount)*$percentInterest);
			$amountSameLevel = $amountInterest;
			$Comment = 'Rank '.$checkUser->User_Agency_Level.' Commission '.$moneyAction->MoneyAction_Name.' '.($percentInterest*100).'% From F'.$i.' ID:'.$user->User_ID;
			$arrayInsert[] = array(
				'Money_User' => $checkUser->User_ID,
				'Money_USDT' => $amountInterest,
				'Money_USDTFee' => 0,
				'Money_Time' => time(),
				'Money_Comment' => $Comment,
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
        }
        if(count($arrayInsert)){
            Money::insert($arrayInsert);
        }
        return true;
	}
	
	public static function checkMaxout($user_ID, $amount = 0){
		
		$checkMaxout = User::where('User_ID', $user_ID)->value('User_UnMaxout');
		if($checkMaxout == 1){
			return 999999999;
		}
		$total_invest = Investment::where('investment_User', $user_ID)->where('investment_Status', 1)->sum(DB::raw('investment_Amount*investment_Rate'));
		$percent_maxout = 3;
		//SUM COMMISSION And Interest
		$total_com = Money::where('Money_User', $user_ID)
							->whereIN('Money_MoneyAction', [4,5,6,9,13,14,16,17])
							->where('Money_MoneyStatus', 1)
							->sum('Money_USDT');
		// dd($total_invest,$total_com, $amount);
		if($total_com+$amount >= $total_invest*$percent_maxout){
			// invest income
			$getBalance = User::getBalance($user_ID, 10);
			if($getBalance >= $total_invest){
				$updateblance = User::updateBalance($user_ID, 10, -($total_invest));
				$moneyArray = array(
					'Money_User' => $user_ID,
					'Money_USDT' => -$total_invest,
					'Money_USDTFee' => 0,
					'Money_Time' => time(),
					'Money_Comment' => 'Join package $'.number_format($total_invest,2).' 300% Income',
					'Money_MoneyAction' => 15,
					'Money_MoneyStatus' => 1,
					'Money_Rate' => 1,
					'Money_CurrentAmount' => $total_invest,
					'Money_Currency' => 10
				);
				//Invest
				$invest = array(
					'investment_User' => $user_ID,
					'investment_Amount' => $total_invest,
					'investment_Rate' => 1,
					'investment_Currency' => 5,
					'investment_Time' => time(),
					'investment_Status' => 1
				);
				// thêm dữ liệu
				DB::table('investment')->insert($invest);
				DB::table('money')->insert($moneyArray);
			}
			// return $total_com+$amount - $total_invest;
		}
		return 999999999;
		// $total_invest = $total_invest * $percent_maxout;
		// return $total_invest - $total_com;
	}
	
	public static function limitWithdrawIncome($user_ID, $amount = 0){
		
		$checkMaxout = User::where('User_ID', $user_ID)->value('User_UnMaxout');
		if($checkMaxout == 1){
			return true;
		}
		$total_invest = Investment::where('investment_User', $user_ID)->where('investment_Status', 1)->sum(DB::raw('investment_Amount*investment_Rate'));
		//SUM COMMISSION And Interest
		// $total_com = Money::where('Money_User', $user_ID)
		// 					->whereIN('Money_MoneyAction', [4,5,6,9,13,14,16,17])
		// 					->where('Money_MoneyStatus', 1)
		// 					->sum('Money_USDT');
		$totalWithdrawIncome = Money::where('Money_User', $user_ID)
									->whereIN('Money_MoneyAction', [21])
									->where('Money_MoneyStatus', 1)
									->where('Money_Currency', 5)
									->sum('Money_USDT');
		if($totalWithdrawIncome+$amount <= $total_invest*2){
			return true;
		}
		return false;
		// $total_invest = $total_invest * $percent_maxout;
		// return $total_invest - $total_com;
	}

	public static function checkWithdraw($userID){
		$checkInvest = Investment::where('investment_User', $userID)->where('investment_Status', 1)->sum('investment_Amount');
		if(!$checkInvest || $checkInvest < 300){
			return ['status'=>false, 'message'=>'Your investment isn\'t enough $300'];
		}
		$checkChildInvest = Investment::join('users', 'investment_User', 'User_ID')
									->where('User_Parent', $userID)
									->where('investment_Status', 1)
									->select('User_ID')
									->groupBy('User_ID')->get()->count();
		if($checkChildInvest < 2){
			// return ['status'=>false, 'message'=>'You need to invite 2 investors to withdraw'];
		}
		return ['status'=>true];
	}
	
	//Check spam request
	public static function RandomToken()
	{
		$code = str_random(32) . '' . rand(10000000, 99999999);
		$CheckCode = DB::table('string_token')->where('Token', $code)->first();
		if (!$CheckCode) {
			//Xóa token của thằng đó đã tạo mà chưa dùng

			$minutest_30p = date('Y-m-d H:i:s',strtotime('-30 minutes', time()));

			$delete = DB::table('string_token')->where('CreateDate', '<=', $minutest_30p)->delete();

			
			//bắt đàu tạo token mới
			$createCode = DB::table('string_token')->insert([
				'Token' => $code,
				'User' => Session('user')->User_ID
			]);
			return $code;
		} else {
			return self::RandomToken();
		}
	}

	// check spam cho app
	public static function RandomTokenAPI($user)
	{
		$code = str_random(32) . '' . rand(10000000, 99999999);
		$CheckCode = DB::table('string_token')->where('Token', $code)->first();
		if (!$CheckCode) {
			//Xóa token của thằng đó đã tạo mà chưa dùng
			$minutest_30p = date('Y-m-d H:i:s',strtotime('-30 minutes', time()));

			$delete = DB::table('string_token')->where('CreateDate', '<=', $minutest_30p)
												->orWhere('User', $user)
												->delete();

			//bắt đàu tạo token mới
			$createCode = DB::table('string_token')->insert([
				'Token' => $code,
				'User' => $user
			]);
			return $code;
		} else {
			return self::RandomTokenAPI($user);
		}
	}

	public static function getCommission($user, $coinArr){
		$total = 0;
		for ($i=0; $i < count($coinArr); $i++) { 
			$commission = Money::where([
				'Money_MoneyAction' => $coinArr[$i],
				'Money_MoneyStatus' => 1,
				'Money_User' => $user->User_ID,
				'Money_Currency' => 5
				])->sum('Money_USDT');
			$total += $commission;
		}

		return $total;
	}

	static function StatisticTotal($where)
	{
		$result = Money::join('users', 'Money_User', 'User_ID')->selectRaw('
			User_BalanceUSDT, User_ID as userid,
			SUM(IF(`Money_Currency` = 8 ' . $where . ', (ROUND((`Money_USDT` - `Money_USDTFee`),8)), 0)) as BalanceDAFCO,
			SUM(IF(`Money_Currency` != 8 ' . $where . ', (ROUND((`Money_USDT` - `Money_USDTFee`),8)), 0)) as BalanceUSD,
			SUM(IF(`Money_Currency` = 10 ' . $where . ', (ROUND((`Money_USDT` - `Money_USDTFee`),8)), 0)) as BalanceMATRIX,
			
			SUM(IF(`Money_Currency` = 1 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as DepositBTC, 
			SUM(IF(`Money_Currency` = 2 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as DepositETH,
			SUM(IF(`Money_Currency` = 5 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as DepositUSD,
			SUM(IF(`Money_Currency` = 8 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as DepositDAFCO,
			SUM(IF(`Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as DepositTotal,

			SUM(IF(`Money_MoneyAction` = 2 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as WithdrawTotal,
			SUM(IF(`Money_MoneyAction` = 2 ' . $where . ', ROUND((`Money_USDTFee`),8), 0)) as WithdrawFee,
			
			SUM(IF(`Money_Currency` = 5 AND `Money_MoneyAction` = 7 AND `Money_Comment` LIKE "Give%" ' . $where . ', ROUND(`Money_USDT` - `Money_USDTFee`,8), 0)) as GiveUSD,
			SUM(IF(`Money_Currency` = 5 AND `Money_MoneyAction` = 7 AND `Money_Comment` LIKE "Transfer%" ' . $where . ', ROUND(`Money_USDT` - `Money_USDTFee`,8), 0)) as TransferUSD,
			SUM(IF(`Money_Currency` = 8 AND `Money_MoneyAction` = 7 AND `Money_Comment` LIKE "Give%" ' . $where . ', ROUND(`Money_USDT` - `Money_USDTFee`,8), 0)) as GiveDAFCO,
			SUM(IF(`Money_Currency` = 8 AND `Money_MoneyAction` = 7 AND `Money_Comment` LIKE "Transfer%" ' . $where . ', ROUND(`Money_USDT` - `Money_USDTFee`,8), 0)) as TransferDAFCO,
			
			SUM(IF(`Money_MoneyAction` = 3 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as Investment,
			SUM(IF(`Money_MoneyAction` = 8 ' . $where . ', ROUND((`Money_USDTFee`),8), 0)) as CancelInvestFee,

			SUM(IF(`Money_MoneyAction` = 4 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as Interest,
			SUM(IF(`Money_MoneyAction` = 5 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as Direct,
			SUM(IF(`Money_MoneyAction` = 6 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as Affiliate,

			SUM(IF(`Money_MoneyAction` = 10 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as BonusDeposit,
			SUM(IF(`Money_MoneyAction` = 11 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as RefundBet,
			SUM(IF(`Money_MoneyAction` = 12 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MasterCom,
			SUM(IF(`Money_MoneyAction` = 13 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MasterComSameLevel,
			
			SUM(IF(`Money_MoneyAction` = 21 AND `Money_Currency` = 10 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MatrixDeposit,
			SUM(IF(`Money_MoneyAction` = 22 AND `Money_Currency` = 10 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MatrixWithdraw,
			SUM(IF(`Money_MoneyAction` = 18 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MatrixJoin,
			SUM(IF(`Money_MoneyAction` = 25 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MatrixBonusDAFCO,
			SUM(IF(`Money_MoneyAction` = 24 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MatrixDirect,
			SUM(IF(`Money_MoneyAction` = 19 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MatrixCom,
			SUM(IF(`Money_MoneyAction` = 20 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MatrixIncome,
			SUM(IF(`Money_MoneyAction` = 23 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as MatrixReActive,
			
			( SELECT SUM(`amount`) FROM `sonix_log` join `users` on `User_ID` = `user` WHERE User_Level = 0 AND type = "debit") as TotalBet,
			( SELECT SUM(`amount`) FROM `sonix_log` join `users` on `User_ID` = `user` WHERE User_Level = 0 AND type = "credit") as BetWin,
			
			SUM(IF(`Money_MoneyAction` = 8 ' . $where . ', ROUND((`Money_USDT` - `Money_USDTFee`),8), 0)) as RefundInvestment')
			->where('Money_MoneyStatus', 1)
			->where('User_Level', 0)
			->where('User_Status', 1);
		return $result;
	}

	public static function getStatisticTotal($where)
	{
		$result = Money::join('users', 'Money_User', 'User_ID')
			->selectRaw('Money_User, 
			SUM(IF(`Money_Currency` = 3 ' . $where . ', ROUND(`Money_USDT`+`Money_USDTFee`, 8), 0)) as user_eusd,
			SUM(IF(`Money_Currency` = 9 ' . $where . ', ROUND(`Money_USDT`+`Money_USDTFee`, 8), 0)) as user_gold,
			SUM(IF(`Money_Currency` = 2 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_eth,
			SUM(IF(`Money_Currency` = 5 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_usdt,
			SUM(IF(`Money_Currency` = 4 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_rbd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 33 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 33 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_gold_eusd,
			SUM(IF(`Money_Currency` = 9 AND (`Money_MoneyAction` = 35 OR `Money_MoneyAction` = 39) ' . $where . ', ROUND(`Money_USDT`,8), 0)) as gold_reward,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 47 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as gold_reward_havest_hippo,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 35 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as gold_reward_mission_success,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 39 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as gold_reward_lucky_spin,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 48 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as use_gift_code,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 27 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_egg_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 27 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_egg_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 27 ' . $where . ', ROUND(`Money_USDT`,8), 0))/-200 as count_egg_buy,
			SUM(IF(`Money_Currency` = 9 AND (`Money_MoneyAction` = 28 OR `Money_MoneyAction` = 29) ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_items_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 29 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_food_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 29 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_food_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 30 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as active_egg_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 30 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as active_egg_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 49 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_gift_code_eusd,
			COUNT(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 49 ' . $where . ', ROUND(`Money_USDT`,8), null)) as count_buy_gift_code_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 36 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as active_fish_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 36 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as active_fish_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 28 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_pool_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 28 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_pool_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 41 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_item_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 41 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_item_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 5 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_buy_egg_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 12 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_buy_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 6 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_active_egg_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 8 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_active_grow_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 9 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_buy_item,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 14 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_buy_gift_code,
			SUM(IF(`Money_Currency` = 3 AND (`Money_MoneyAction` = 5 OR `Money_MoneyAction` = 6 OR `Money_MoneyAction` = 8 OR `Money_MoneyAction` = 9) ' . $where . ', ROUND(`Money_USDT`,8), 0)) as direct_commission,
			SUM(IF(`Money_Currency` = 3 AND (`Money_MoneyAction` = 10 OR `Money_MoneyAction` = 11) ' . $where . ', ROUND(`Money_USDT`,8), 0)) as achievement_commission,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 7 AND `Money_USDT` < 0 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as transfer_to,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 7 AND `Money_USDT` > 0 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as received_from,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 37 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_egg_from_market_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 37 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_egg_from_market_gold,
			COUNT(IF(`Money_MoneyAction` = 37 ' . $where . ', ROUND(`Money_USDT`,8), null)) as count_buy_egg_from_market,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_egg_from_market_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_egg_from_market_gold,
			COUNT(IF(`Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDT`,8), null)) as count_sell_egg_from_market,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 31 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_ag_game,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 32 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as withdraw_ag_game,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 34 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_gold_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 34 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 54 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_egg_system_eusd,
			SUM(IF(`Money_Currency` = 8 AND `Money_MoneyAction` = 54 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_egg_system_ebp,
			COUNT(IF(`Money_MoneyAction` = 54 ' . $where . ', ROUND(`Money_USDT`,8), null)) as count_sell_egg_system,
			SUM(IF(`Money_Currency` = 5 AND `Money_MoneyAction` = 2 ' . $where . ', ROUND(`Money_USDT`-`Money_USDTFee`,8), 0)) as withdraw_without_fee,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 2 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as withdraw,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 2 ' . $where . ', ROUND(`Money_USDTFee`,8), 0)) as fee_withdraw,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 7 AND `Money_USDT` < 0 ' . $where . ', ROUND(`Money_USDTFee`,8), 0)) as fee_transfer,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDTFee`,8), 0)) as fee_market_trading_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDTFee`,8), 0)) as fee_market_trading_gold
			')
			->whereIn('Money_MoneyStatus', [0,1])
			->whereIn('users.User_Level', [0, 4]);
			// ->groupBy('Money_User');
		return $result;
	}


	public static function getStatistic($where)
	{
		$result = Money::join('users', 'Money_User', 'User_ID')
			->selectRaw('Money_User, 
			SUM(IF(`Money_Currency` = 3 ' . $where . ', ROUND(`Money_USDT`+`Money_USDTFee`, 8), 0)) as user_eusd,
			SUM(IF(`Money_Currency` = 9 ' . $where . ', ROUND(`Money_USDT`+`Money_USDTFee`, 8), 0)) as user_gold,
			SUM(IF(`Money_Currency` = 2 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_eth,
			SUM(IF(`Money_Currency` = 5 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_usdt,
			SUM(IF(`Money_Currency` = 4 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_rbd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 1 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 33 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 33 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_gold_eusd,
			SUM(IF(`Money_Currency` = 9 AND (`Money_MoneyAction` = 35 OR `Money_MoneyAction` = 39) ' . $where . ', ROUND(`Money_USDT`,8), 0)) as gold_reward,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 47 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as gold_reward_havest_hippo,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 35 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as gold_reward_mission_success,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 39 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as gold_reward_lucky_spin,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 48 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as use_gift_code,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 27 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_egg_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 27 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_egg_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 27 ' . $where . ', ROUND(`Money_USDT`,8), 0))/-200 as count_egg_buy,
			SUM(IF(`Money_Currency` = 9 AND (`Money_MoneyAction` = 28 OR `Money_MoneyAction` = 29) ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_items_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 29 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_food_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 29 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_food_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 30 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as active_egg_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 30 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as active_egg_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 49 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_gift_code_eusd,
			COUNT(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 49 ' . $where . ', ROUND(`Money_USDT`,8), null)) as count_buy_gift_code_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 36 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as active_fish_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 36 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as active_fish_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 28 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_pool_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 28 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_pool_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 41 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_item_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 41 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_item_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 5 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_buy_egg_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 12 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_buy_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 6 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_active_egg_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 8 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_active_grow_eusd,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 9 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_buy_item,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 14 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as commission_buy_gift_code,
			SUM(IF(`Money_Currency` = 3 AND (`Money_MoneyAction` = 5 OR `Money_MoneyAction` = 6 OR `Money_MoneyAction` = 8 OR `Money_MoneyAction` = 9) ' . $where . ', ROUND(`Money_USDT`,8), 0)) as direct_commission,
			SUM(IF(`Money_Currency` = 3 AND (`Money_MoneyAction` = 10 OR `Money_MoneyAction` = 11) ' . $where . ', ROUND(`Money_USDT`,8), 0)) as achievement_commission,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 7 AND `Money_USDT` < 0 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as transfer_to,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 7 AND `Money_USDT` > 0 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as received_from,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 37 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_egg_from_market_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 37 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as buy_egg_from_market_gold,
			COUNT(IF(`Money_MoneyAction` = 37 ' . $where . ', ROUND(`Money_USDT`,8), null)) as count_buy_egg_from_market,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_egg_from_market_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_egg_from_market_gold,
			COUNT(IF(`Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDT`,8), null)) as count_sell_egg_from_market,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 31 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as deposit_ag_game,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 32 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as withdraw_ag_game,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 34 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_gold_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 34 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_gold,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 54 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_egg_system_eusd,
			SUM(IF(`Money_Currency` = 8 AND `Money_MoneyAction` = 54 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as sell_egg_system_ebp,
			COUNT(IF(`Money_MoneyAction` = 54 ' . $where . ', ROUND(`Money_USDT`,8), null)) as count_sell_egg_system,
			SUM(IF(`Money_Currency` = 5 AND `Money_MoneyAction` = 2 ' . $where . ', ROUND(`Money_USDT`-`Money_USDTFee`,8), 0)) as withdraw_without_fee,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 2 ' . $where . ', ROUND(`Money_USDT`,8), 0)) as withdraw,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 2 ' . $where . ', ROUND(`Money_USDTFee`,8), 0)) as fee_withdraw,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 7 AND `Money_USDT` < 0 ' . $where . ', ROUND(`Money_USDTFee`,8), 0)) as fee_transfer,
			SUM(IF(`Money_Currency` = 3 AND `Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDTFee`,8), 0)) as fee_market_trading_eusd,
			SUM(IF(`Money_Currency` = 9 AND `Money_MoneyAction` = 38 ' . $where . ', ROUND(`Money_USDTFee`,8), 0)) as fee_market_trading_gold
			')
			->whereIn('Money_MoneyStatus', [0,1])
			->whereIn('users.User_Level', [0, 4])
			->groupBy('Money_User');
		return $result;
	}
}
