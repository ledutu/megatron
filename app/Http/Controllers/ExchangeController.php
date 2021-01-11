<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use DB;
use Redirect;

use App\Model\GameBet;
use App\Model\GameCoin;
use App\Model\subAccountBalance;
use App\Model\GameBalance;
use App\Model\User;

class ExchangeController extends Controller
{	
  	public function getStatistical(Request $req){
      	$returndata = array(
        	'Withdraw'=> 0,
          	'Deposit'=> 0,
          	'Waiting'=> 0,
          	'Profit'=> 0,
          	'Begin'=> 0,
          	'Balance'=> 0
        );
      	$coinarray = array(
        	'eusd'=>3,
          	'gold'=>9
        );

      
      	if($req->params == 'thismonth'){
          	$from = Date('Y-m-01');
          	$fromM = strtotime(Date('Y-m-01'));
          	$toM = strtotime(Date('Y-m-t'));
			
		}elseif($req->params == 'all'){
          	$from = Date('Y-01-01');
          	$fromM = strtotime(Date('Y-01-01'));
          	$toM = strtotime(Date('Y-01-01'));
			
		}else{
          	$from = Date('Y-m-d');
      		$fromM = strtotime(Date('Y-m-d'));
          	$toM = strtotime(Date('Y-m-d'));
        }
		
      	$withdraw = DB::table('balance_game')->where('time', '>=',$from)->where('user', Session('user')->User_ID)->where('action', 'withdraw')->sum('amount');

      	$returndata['Withdraw'] = round(abs($withdraw), 2);

      	$deposit = DB::table('balance_game')->where('time', '>=',$from)->where('user', Session('user')->User_ID)->where('action', 'deposit')->sum('amount');
      	$returndata['Deposit'] = round(abs($deposit), 2);
      
      	
      	$bet = GameBet::where('GameBet_SubAccountUser', Session('user')->User_ID)->where('GameBet_datetime', '<=', $fromM)->select('GameBet_Amount', 'GameBet_AmountWin', 'GameBet_Status')->get();
      	foreach($bet as $v){
          if($v->GameBet_Status == 0){
            $returndata['Waiting'] += $v->GameBet_Amount;
          }else{
            $returndata['Profit'] += $v->GameBet_AmountWin;
          }
        }
		$returndata['Profit'] = round($returndata['Profit'] , 2);
		$balance = User::getBalance(Session('user')->User_ID, $coinarray[$req->coin]);
      	$returndata['Balance'] = round($balance, 2);
      	//$begin = DB::table('statisticalSubaccount')->where('sub', Session('sub')->subAccount_ID)->wheredate('datetime', '>=', $from)->first();
		$returndata['Begin'] = round(0 , 2);
      	
      	
      	return response()->json($returndata);
   
    }
  
  	public function getHistoryV2(){
      	// lấy lịch sử game
		$minute = date('i');
		$limit = $minute-1;
		if($minute == 1){
		  $limit = 60;
		}else if($minute == 0){
		  $limit = 59;
		}
		
		$history = GameCoin::where('GameCoin_Order',0)->orderBy('GameCoin_Time', 'DESC')->skip(0)->take($limit)->get();
      	

		$symbol = 'BTCUSDT';

		$historyGame = array();
		

      
		foreach($history as $v){
			if($v->GameCoin_Data[$symbol]['open'] < $v->GameCoin_Data[$symbol]['close']){
				$historyGame[] = 1;
			}elseif($v->GameCoin_Data[$symbol]['open'] > $v->GameCoin_Data[$symbol]['close']){
				$historyGame[] = 2;
			}else{
				$historyGame[] = 3;
			}
			
		}
		$historyGame = array_reverse($historyGame);
      	return response()->json($historyGame);
    }
  	public function getHistory(Request $req){
      	$status = array(0);
      
      	if($req->status == 'CLOSE'){
      		$status = array(1,2,3);
      	}
      	$query_temp = GameBet::where('GameBet_SubAccountUser', $req->account ? $req->account : Session('user')->User_ID)->whereIn('GameBet_Status', $status);
		$list = $query_temp->orderBy('GameBet_datetime', -1)->select('GameBet_Type', 'GameBet_SubAccountEndBalance', 'GameBet_Symbol', 'GameBet_Amount', 'GameBet_AmountWin', 'GameBet_Status', 'GameBet_datetime', 'GameBet_Currency')->offset(0)->take(10)->get();
		/*if($req->params == 'today'){
          	$time = strtotime(Date('Y-m-d'));
			$list = $query_temp->where('GameBet_datetime', '>=', $time)->orderBy('GameBet_datetime', -1)->limit()->get();
		}elseif($req->params == 'thismonth'){
          	$time = strtotime(Date('Y-m-01'));
			$list = $query_temp->where('GameBet_datetime', '>=', $time)->orderBy('GameBet_datetime', -1)->get();
		}elseif($req->params == 'all'){
          	$time = strtotime(Date('Y-01-01'));
			$list = $query_temp->where('GameBet_datetime', '>=', $time)->orderBy('GameBet_datetime', -1)->get();
		}
		else {
			
		}*/

		return response()->json($list);
    }
	
	public function getExchange(Request $req){

      	if(!Session('user')){
            return redirect()->route('getLogin');
		}

      	$subID = Session('user')->User_ID;
      	$user = User::where('User_ID', Session('user')->User_ID)->first();
      	//if($user->User_Level != 1){
            //return redirect()->route('getDashboard')->with(['flash_level'=>'error', 'flash_message'=>'Exchange Is Maintenance!!!']);
        //}
      	$token = $user->user_SessionID;
		
		// lấy lịch sử game
		$minute = date('i');
		$limit = $minute-1;
		if($minute == 1){
		  $limit = 60;
		}else if($minute == 0){
		  $limit = 59;
		}
		
		$history = GameCoin::where('order',1)->orderBy('time', 'DESC')->skip(0)->take($limit)->get();
      	
		$currency = 5;
      	if($req->coin){
          	if($req->coin == 'demo'){
            	$currency = 99;  
            }else{
              	$currency = 5;  
            }
        }else{
          	return Redirect::to(route('getExchange',['coin'=>'live']));
        }

		$symbol = 'BTCUSDT';

		// $historyGame = array();
		
      	//$balance = User::getBalanceGame(Session('user')->User_ID, $currency);

		$BalanceTrade = array(
        	'demo'=>User::getBalanceGame(Session('user')->User_ID, 9),
          	'live'=>User::getBalanceGame(Session('user')->User_ID, 5)
        );

      
		foreach($history as $v){
		 	if($v->data[$symbol]['open'] < $v->data[$symbol]['close']){
		 		$historyGame[] = 1;
		 	}elseif($v->data[$symbol]['open'] > $v->data[$symbol]['close']){
		 		$historyGame[] = 2;
		}else{
				$historyGame[] = 3;
		}
			
		}
		$historyGame = array_reverse($historyGame);
		//$historyGame = [];
		$betArray = array();
		$bet = GameBet::where('GameBet_SubAccountUser', Session('user')->User_ID)->where('GameBet_Status', 0)->select('GameBet_Type', 'GameBet_Amount', 'GameBet_Symbol')->get();


		if(count($bet)){
            $betArray = array('BTCUSDT'=>array('buy'=>0,'sell'=>0));
                foreach($bet as $v){
                    if($v->GameBet_Type == 'buy'){
                    $betArray[$v->GameBet_Symbol]['buy'] += $v->GameBet_Amount;
                }else{
                    $betArray[$v->GameBet_Symbol]['sell'] += $v->GameBet_Amount;
                }
            }
        }
	
		


		return view('system.exchange.exchange', compact('historyGame', 'symbol', 'subID', 'token', 'betArray', 'currency', 'BalanceTrade', 'user'));
	

	}
	
	
}

