<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\System\CoinbaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

use Image;
use PragmaRX\Google2FA\Google2FA;

use Mail;
use GuzzleHttp\Client;
use App\Model\Wallet;
use App\Model\GoogleAuth;
use App\Model\User;
use App\Model\userBalance;
use App\Model\Money;
use App\Model\LogUser;
use App\Model\Profile;
use App\Model\GameBet;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Jobs\WalletJobs;
use App\Jobs\SendMailJobs;
use App\Jobs\SendTelegramJobs;
class AgencyController extends Controller
{

  public function __contructor(){
    // $this->middleware('spamChecking', ['only' => ['postWithdraw']]);
    $this->middleware('captchaChecking', ['only' => ['buyAgency']]);
  }

  public function getAgency(Request $req){
    
    $user = session('user');
    $isBuyAgency = Money::checkBuyAgency($user->User_ID);
    $memberList = User::getMemberList($user, $req, 0);
    $totalCommission = Money::getCommission($user, [60, 61]);
    $fromDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
    $toDate = date('Y-m-d H:i:s');
    $volumeTradeF1 = GameBet::getVolumeTradeF1($user->User_ID, $fromDate, $toDate);
    $getBuyAgencyF1 = GameBet::getBuyAgencyF1($user->User_ID);
    
    $rankPackage = GameBet::getRank();
    $rankLevel = GameBet::getRankUser($user->User_ID, $fromDate, $toDate);
      	$pending['Agency'] = $this->getPendingAgency($user, $fromDate, $toDate);
      	$pending['Rank'] = $this->getPendingRank($user, $fromDate, $toDate);
    $rankUser = $rankPackage[$rankLevel];
    $profitCompany = 0;
    $total['Commission'] = $totalCommission;
    $total['F1Trade'] = $volumeTradeF1;
    $total['F1Agency'] = $getBuyAgencyF1;
    $total['ProfitCompany'] = $profitCompany;
    return $this->view('system.agency.index', [
      'isBuyAgency' => $isBuyAgency,
      'user' => $user,
      'Rank' => $rankUser,
      'member' => $memberList,
      'Total' => $total,
      'Pending' => $pending,
    ]);
  }

  /**
   * @param token
   */
  public function buyAgency(Request $requet){
    $user = session('user');
    $isBuyAgency = Money::checkBuyAgency($user->User_ID);

    if($isBuyAgency){
      return $this->route('getAgency', [], 'You bought package agency!', [], 'error');
    }

    $amount = 100;
    $arrCurrency = DB::table('currency')->whereIn('Currency_ID', [3, 9])->pluck('Currency_Symbol', 'Currency_ID')->toArray();
    $currency = 5;
    $balance = User::getBalance($user->User_ID, $currency);
    if($amount > $balance){
      return $this->route('getAgency', [], __('agency.your_balance_is_insufficient'), [], 'error');
    }
    $json['status'] = 'OK';
    if($json['status'] == 'OK'){
      //Minus user balance
      $arrayInsert = array(
        'Money_User' => $user->User_ID,
        'Money_USDT' => -(float)($amount*1),
        'Money_USDTFee' => 0,
        'Money_Time' => time(),
        'Money_Comment' => 'Buy Package Agency $'.$amount,
        'Money_MoneyAction' => 57,
        'Money_MoneyStatus' => 1,
        'Money_Address' => null,
        'Money_Currency' => $currency,
        'Money_CurrentAmount' => (float)($amount*1),
        'Money_Rate' => 1,
        'Money_Confirm' => 0,
        'Money_Confirm_Time' => null,
        'Money_FromAPI' => 1,
      );
      $insert = Money::insert($arrayInsert);
      if($insert){
        //Money::checkCommissionBOAgency($user, $amount, $currency);
      }

      $balanceUSDT = User::getBalance($user->User_ID, 5);
      $json['Balance']['USDT'] = $balanceUSDT;
      
      $data = array('User_ID' => $user->User_ID, 'User_Email' => $user->User_Email, 'token' => '123456');
      dispatch(new SendMailJobs('agency', $data, 'Buy agency!', $user->User_ID));

      return $this->route('getAgency', [], __('agency.buying_agency_successfully'));
    }

    return $this->route('getAgency', [], __('agency.failed_please_try_again'), [], 'error');
  }
  
  public function getPendingAgency($user, $from, $to){
      $mondayLastWeek = $from;
      //$mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
      // $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('2019-11-01'));
      // thứ 2 tuần này
      $mondayThisWeek = $to;
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
                        ->where('User_Tree', 'LIKE', $user->User_Tree.',%')
                        ->get();
      $total = 0;
      foreach($getUserBet as $item){
          //dd($getUserBet, $item);
          $amount = abs($item->Money_USDT);
          $arrParent = explode(',', $item->User_Tree);
          $arrParent = array_reverse($arrParent);
          $commissionArr = array(1=>0.5, 2=>0.25, 3=>0.12, 4=>0.06, 5=>0.03, 6=>0.015, 7=>0.007);
          $packageArray = GameBet::getRank();
          $action = 60;
          for($i = 1; $i <= 7; $i++){
              if(!isset($arrParent[$i])) continue;
              if($arrParent[$i] != $user->User_ID){
                continue;
              }
              $checkUser = User::find($arrParent[$i]);
              if(!$checkUser) continue;

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
              if($user->User_ID == $checkUser->User_ID){
                  $total+=$commission;
              }
          }
      }
   	  return $total;
  }
  
  public function getPendingRank($user, $from, $to){
      $mondayLastWeek = $from;
      //$mondayLastWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
      // $mondayLastWeek = date('Y-m-d 00:00:00', strtotime('2019-11-01'));
      // thứ 2 tuần này
      $mondayThisWeek = $to;
      //$mondayThisWeek = date('Y-m-d H:i:s');
      //lấy những user có đánh tuần trước
      $start = (strtotime($mondayLastWeek));
      $end = (strtotime($mondayThisWeek));
      $action = 60;
      $currency = 5;
   	  $total = 0;
      //lấy những user có đánh tuần trước
      $getUserBet = DB::table('statistical')
						->join('users', 'User_ID', 'statistical_User')
						->where('User_Tree', 'LIKE', $user->User_Tree.',%')
						//->where('statistical_User', 945099)
						->where('statistical_Time', '>=', $mondayLastWeek)
						->where('statistical_Time', '<', $mondayThisWeek)
						->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email , User_Tree')
						->groupBy('statistical_User')
						->get();
    	if($user->User_Level == 1){
            //dd($getUserBet);
        }
      	$timeToday = strtotime($mondayThisWeek);
		$action = 61;
      	$currency = 5;
		$packageArray = GameBet::getRank();
      	$arrPercent = [ 0=>0, 1=>0.01, 2=>0.005, 3=>0.003, 4=>0.0015, 5=>0.0007, 6=>0.00035, 7=>0.00015 ];
      	foreach($getUserBet as $item){
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
              	if($usersArray[$i] != $user->User_ID){
                  	continue;
                }
				$info_parent = User::find($usersArray[$i]);
				if(!$info_parent){
					continue;
				}
				//lấy gói thoả điều kiện
				$getPackageParent = GameBet::getRankUser($usersArray[$i], $mondayLastWeek, $mondayThisWeek, 0);
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
              	if($user->User_ID == $info_parent->User_ID){
                    $total += $amountInterest;
                }

				continue;
			}
        }
   	  return $total;
  }
}
