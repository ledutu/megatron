<?php
namespace App\Http\Controllers\System;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Http\Controllers\System\CoinbaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

use Image;
use PragmaRX\Google2FA\Google2FA;

use DB;
use Mail;
use GuzzleHttp\Client;
use App\Model\Wallet;
use App\Model\GoogleAuth;
use App\Model\User;
use App\Model\userBalance;
use App\Model\Money;
use App\Model\GameBet;



class GameController extends Controller{
    public $key;
    public $urlApi;
    public $agid;
    public $lang;
    public $minGold;
    public $minDeposit;
    public $minWithdraw;
    public function __construct(){
        $this->minDeposit = 1;
        $this->minWithdraw = 1;
    }
    
    public function getRefreshDemo(Request $req){
        $user = Session('user');
        $currency = 99;
        // trừ tiền người nạp
        DB::table('balance_game')->insert([
                    'user'=> $user->User_ID,
                    'action'=>'refresh',
                    'amount'=>1000,
                    'comment'=> 'Refresh Balance Demo',
                    'time'=>time(),
                    'status'=>1,
                    'currency'=>$currency,
                    ]);
        $balanceGame = User::getBalanceGame($user->User_ID, $currency);
        $json['balance']['demo'] = number_format($balanceGame,2);
        // return $this->redirectBack(__('app.deposit_battle_game_successful'), [], true);
        return $this->response(200, $json, 'Success!', [], 'success');
    }
    
    public function postDeposit(Request $req){
        $user = Session('user');
      	// if($user->User_Level != 1){
        //     return $this->redirectBack(__('app.your_balance_is_not_enough'), [], false); 
        // }
        $validator = Validator::make($req->all(), [
            'amount' => 'required|numeric|min:0',
            // 'currency' => 'required|in:3,9',
        ]);
		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
                return $this->redirectBack($value, [], 'error');
			}
        }
      
      
      	// kiểm trả user có đang bet không
      	$bet = GameBet::where('GameBet_SubAccountUser', $user->User_ID)->where('GameBet_Status',0)->first();
      	if($bet){
          	return $this->redirectBack('You are betting in the game', [], 'error');
        }
        $arrCurrency = DB::table('currency')->whereIn('Currency_ID', [5])->pluck('Currency_Symbol', 'Currency_ID')->toArray();
        $currency = 5;
        $minDeposit = $this->minDeposit;
        if($req->amount < $minDeposit){
            return $this->redirectBack('Min deposit '.$minDeposit.' '.$arrCurrency[$currency], [], 'error');
            // return $this->response(200, [], 'Min deposit '.$minDeposit.' '.$arrCurrency[$currency], [], false);  
        }
        $balance = User::getBalance($user->User_ID, $currency);
        if($req->amount > $balance){
            return $this->redirectBack(__('app.your_balance_is_not_enough'), [], 'error');
            // return $this->response(200, ['balance'=>$balance], 'Your balance is not enough', [], false); 
        }
        // trừ tiền người nạp
        $balanceGame = User::getBalanceGame($user->User_ID, $currency);
        DB::table('balance_game')->insert([
                    'user'=> $user->User_ID,
                    'action'=>'deposit',
                    'amount'=>$balanceGame+$req->amount,
                    'comment'=> 'Deposit '.$req->amount.' '.$arrCurrency[$currency],
                    'time'=>time(),
                    'status'=>1,
                    'currency'=>$currency,
                    ]);
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => -(float)($req->amount*1),
            'Money_USDTFee' => 0,
            'Money_Time' => time(),
            'Money_Comment' => 'Deposit '.(float)($req->amount*1).' '.$arrCurrency[$currency].' To Live Balance',
            'Money_MoneyAction' => 55,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => $currency,
            'Money_CurrentAmount' => (float)($req->amount*1),
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_Confirm_Time' => null,
            'Money_FromAPI' => 1,
        );
        $insert = Money::insert($arrayInsert);
        return $this->redirectBack('Deposit To Live Balance Success!', [], 'success');
        // return $this->response(200, $json, __('app.deposit_battle_game_successful'), [], true);
    }
    public function postWithdraw(Request $req){  
        $user = Session('user');
        $validator = Validator::make($req->all(), [
            'amount' => 'required|numeric|min:0',
            // 'CodeSpam' => 'required',
            // 'currency' => 'required|in:3,9'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
                return $this->redirectBack($value, [], 'error');
			}
        }
      	$bet = GameBet::where('GameBet_SubAccountUser', $user->User_ID)->where('GameBet_Status',0)->first();
      	if($bet){
          	return $this->redirectBack('You are betting in the game', [], 'error');
        }
      
        $arrCurrency = DB::table('currency')->whereIn('Currency_ID', [5])->pluck('Currency_Symbol', 'Currency_ID')->toArray();
        $currency = 5;
        $minWithdraw = $this->minWithdraw;
        if($req->amount < $minWithdraw){
            return $this->redirectBack('Min withdraw '.$minWithdraw.' '.$arrCurrency[$currency], [], 'error');
        }
        $balanceGame = User::getBalanceGame($user->User_ID, $currency);
      	if($balanceGame < $req->amount){
            return $this->redirectBack(__('app.your_balance_is_not_enough'), [], 'error');
        }
        // trừ tiền người nạp
        DB::table('balance_game')->insert([
                    'user'=> $user->User_ID,
                    'action'=>'withdraw',
                    'amount'=>$balanceGame - $req->amount,
                    'comment'=> 'Withdraw '.$req->amount.' '.$arrCurrency[$currency],
                    'time'=>time(),
                    'status'=>1,
                    'currency'=>$currency,
                    ]);
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => (float)abs($req->amount),
            'Money_USDTFee' => 0,
            'Money_Time' => time(),
            'Money_Comment' => 'Withdraw '.(float)abs($req->amount*1).' '.$arrCurrency[$currency].' From Live Balance',
            'Money_MoneyAction' => 56,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => $currency,
            'Money_CurrentAmount' => (float)abs($req->amount*1),
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_Confirm_Time' => null,
            'Money_FromAPI' => 1,
        );
        $insert = Money::insert($arrayInsert);
        return $this->redirectBack('Withdraw From Live Balance Success', [], 'success');
    }
}