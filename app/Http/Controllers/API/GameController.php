<?php
namespace App\Http\Controllers\API;

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



class GameController extends Controller{
    public $key;
    public $urlApi;
    public $agid;
    public $lang;
    public $minGold;
    public $minDeposit;
    public $minWithdraw;
    public function __construct(){
        $this->middleware('auth:api');
        $this->middleware('userPermission', ['only' => ['postDeposit', 'postWithdraw', 'getUrlGame']]);
        $this->minGold = 150;
        $this->minEUSD = 10;
        $this->minDeposit = 20;
        $this->minWithdraw = 150;
    }
  
    public function getBalanceGame(Request $req){
      	$user = Auth::user();
        $balance['EUSD'] = User::getBalanceGame($user->User_ID, 3);
        $balance['GOLD'] = User::getBalanceGame($user->User_ID, 9);

        return $this->response(200, ['Balance'=>$balance], '', [], true);
    }
  
    public function getGameList(){
        $user = Auth::user();
      	$show = 0;
		if($user->User_Level == 1){
          	$show = 1;
		}
        $gameList = [
          	[
                'game_image_url'=>'https://media.eggsbook.com/battle-game/bo.png',
            	'game_display_name' => 'Binary Option',
            	'game_name' => 'Binary Option',
            	'game_show' => $show,
            	'game_play' => 'https://exchange.eggsbook.com',
            ],
          	[
                'game_image_url'=>'https://media.eggsbook.com/battle-game/sedie.png',
            	'game_display_name' => 'Sedie',
            	'game_name' => 'Sedie',
            	'game_show' => $show,
            	'game_play' => 'https://play-sedie.eggsbook.com',
            ],
          	[
                'game_image_url'=>'https://media.eggsbook.com/battle-game/snail-racing.png',
            	'game_display_name' => 'Snail Racing',
            	'game_name' => 'Snail Racing',
            	'game_show' => $show,
            	'game_play' => 'https://play-snail-racing.eggsbook.com',
            ],
        ];
        $list['list'] = (object)$gameList;
        $list['title'] = 'Battle';
        return $this->response(200, $list, '', [], true);
    }
    
    public function postDeposit(Request $req){
        $user = Auth::user();
      	if($user->User_Level != 1){
            return $this->response(200, [], 'Coming Soon', [], false);
        }
        $validator = Validator::make($req->all(), [
            'amount' => 'required|numeric|min:0',
            'CodeSpam' => 'required',
            'currency' => 'required|in:3,9',
        ]);
		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }
		$captcha = app('App\Http\Controllers\API\WalletController')->checkCaptcha($req->token);
		if(!$captcha){
			//return $this->response(200, [], 'Captcha isn\'t exist!', [], false);
		}
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
        if ($checkSpam == null) {
            //khoong toonf taij
            // return $this->response(200, [], 'Misconduct!', [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
        $arrCurrency = DB::table('currency')->whereIn('Currency_ID', [3,9])->pluck('Currency_Symbol', 'Currency_ID')->toArray();
        $currency = $req->currency;
        if($currency == 3){
            $minDeposit = $this->minEUSD;
        }else{
            $minDeposit = $this->minGold;
        }
        if($req->amount < $minDeposit){
            return $this->response(200, [], 'Min deposit '.$minDeposit.' '.$arrCurrency[$currency], [], false);  
        }
        $balance = User::getBalance($user->User_ID, $currency);
        if($req->amount > $balance){
            return $this->response(200, ['balance'=>$balance], 'Your balance is not enough', [], false); 
        }
        $json['status'] = 'OK';
        if($json['status'] == 'OK'){
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
                'Money_Comment' => 'Deposit '.(float)($req->amount*1).' '.$arrCurrency[$currency].' to balance game',
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
            $balanceGOLD = User::getBalance($user->User_ID, 9);
            $balanceEUSD = User::getBalance($user->User_ID, 3);
            $json['Balance']['EUSD'] = $balanceEUSD;
            $json['Balance']['GOLD'] = $balanceGOLD;
            $json['Balance']['Game'][$arrCurrency[$currency]] = $balanceGame+$req->amount;
            return $this->response(200, $json, __('app.deposit_battle_game_successful'), [], true);
        }else{
            return $this->response(200, $json, __('app.deposit_battle_game_failed'), [], false);
        }
    }
    public function postWithdraw(Request $req){   
        $user = Auth::user();
      	if($user->User_Level != 1){
            return $this->response(200, [], __('Coming Soon'), [], false);
        }
        $validator = Validator::make($req->all(), [
            'amount' => 'required|numeric|min:0',
            'CodeSpam' => 'required',
            'currency' => 'required|in:3,9'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }
		$captcha = app('App\Http\Controllers\API\WalletController')->checkCaptcha($req->token);
		if(!$captcha){
			//return $this->response(200, [], 'Captcha isn\'t exist!', [], false);
		}

        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();

        if ($checkSpam == null) {
            //khoong toonf taij
            return $this->response(200, [], __('app.misconduct!'), [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
       
        $arrCurrency = DB::table('currency')->whereIn('Currency_ID', [3,9])->pluck('Currency_Symbol', 'Currency_ID')->toArray();
        $currency = $req->currency;
        if($currency == 3){
            $minWithdraw = $this->minEUSD;
        }else{
            $minWithdraw = $this->minGold;
        }
        if($req->amount < $minWithdraw){
            return $this->response(200, [], 'Min withdraw '.$minWithdraw.' '.$arrCurrency[$currency], [], false);  
        }
        $balanceGame = User::getBalanceGame($user->User_ID, $currency);
      	if($balanceGame < $req->amount){
          	return $this->response(200, ['BalanceGame'=>$balanceGame], __('app.your_balance_is_not_enough'), [], false); 
        }

        $json['status'] = 'OK';
        if($json['status'] == 'OK'){
            // trừ tiền người nạp
			DB::table('balance_game')->insert([
                        'user'=> $user->User_ID,
                        'action'=>'deposit',
                        'amount'=>$balanceGame - $req->amount,
                        'comment'=> 'Deposit '.$req->amount.' '.$arrCurrency[$currency],
                        'time'=>time(),
                        'status'=>1,
                        'currency'=>$currency,
                        ]);
            $arrayInsert = array(
                'Money_User' => $user->User_ID,
                'Money_USDT' => (float)abs($req->amount),
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => 'Withdraw '.(float)abs($req->amount*1).' '.$arrCurrency[$currency].' to balance game',
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
            $balanceGOLD = User::getBalance($user->User_ID, 9);
            $balanceEUSD = User::getBalance($user->User_ID, 3);
            $json['Balance']['EUSD'] = $balanceEUSD;
            $json['Balance']['GOLD'] = $balanceGOLD;
            $json['Balance']['Game'][$arrCurrency[$currency]] = $balanceGame-$req->amount;
            return $this->response(200, $json, __('app.withdraw_battle_game_successful'), [], true);
        }else{
            return $this->response(200, $json, __('app.withdraw_battle_game_failed'), [], false);
        }
    }
}