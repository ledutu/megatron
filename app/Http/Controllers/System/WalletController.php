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
// use PragmaRX\Google2FA\Google2FA;
use Google2FA;

use Mail;
use GuzzleHttp\Client;
use App\Model\Wallet;
use App\Model\GoogleAuth;
use App\Model\User;
use App\Model\userBalance;
use App\Model\Money;

use App\Jobs\WalletJobs;

use App\Model\LogUser;
use App\Model\Profile;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Jobs\SendMailJobs;
use App\Jobs\SendTelegramJobs;
class Walletcontroller extends Controller
{
  //public static $setting = DB::table('setting_param')->first();
  public $feeWithdraw = 0;
  public $feeTransfer = 0;
  public $setting;

  public function __construct(){
    //$this->middleware('spamChecking', ['only' => ['postWithdraw']]);
    $this->middleware('captchaChecking', ['only' => ['postWithdraw']]);
    $this->setting = DB::table('setting_param')->first();
    //$this->feeWithdraw = 5;
    $this->feeWithdraw = $this->setting->fee_withdraw;
    $this->feeTransfer = $this->setting->fee_transfer;
    
  }

  public function getWallet(Request $request){
    // echo 'Le Duc Tung';
    $user = Session('user');
    $request->coin = 5;
    $rate = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy();
    $history = Money::getHistoryUser($user->User_ID);
    $BalanceTrade['live'] = User::getBalanceGame($user->User_ID, 5);
    $BalanceTrade['demo'] = User::getBalanceGame($user->User_ID, 99);
	$fee['Withdraw'] = $this->setting->fee_withdraw;
    $fee['Transfer'] = $this->setting->fee_transfer;
	if($user->User_ID == 832319){
      	//dd($fee);
    }
    $historyTrade = DB::table('balance_game')->where('user', $user->User_ID)->where('currency', 5)->get();
    //return $this->feeWithdraw;
    
    return $this->view('system.wallet.index', [
      'user' => $user,
      'rate' => $rate,
      'history' => $history,
      'historyTrade' => $historyTrade,
      'balance_trade' => $BalanceTrade,
      'userBalance' => User::getBalance($user->User_ID, 5),
      'fee' => $fee,
    ]);
  }

  /**
   * @param amount
   * @param to
   * @param address
   * @param auth
   * @param token_v3
   */
  public function postWithdraw(Request $request) {
    //dd($request);
    $user = session('user');
    $setting = $this->setting;
    $this->feeWithdraw = $this->setting->fee_withdraw;
    // return $user;
    // return $request->all();
    
    if(!$setting->setting_withdraw){
      return $this->route('getWallet', [], 'Error', [], 'error');
    }

    if($user->User_Lock_Withdraw) {
      return $this->route('getWallet', [], 'Error', [], 'error');
      // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Error', 'status' => false]);
    }

    $user = User::where('User_ID', $user->User_ID)->first();
    $check_custom = $user->User_Level;

    if($check_custom == 4 || $check_custom == 5){
      return $this->route('getWallet', [], __('app.your_account_cannot_use_this_function'), [], 'error');
      // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>__('app.your_account_cannot_use_this_function'), 'status' => false]);
    }

    //Security
    $checkProfile = Profile::where('Profile_User', $user->User_ID)->first();

    if(!$checkProfile || $checkProfile->Profile_Status != 1){
      return $this->route('getWallet', [], __('app.your_profile_kyc_is_unverify'), [], 'error');
      // return redirect()->back()->with([
      //   'flash_level'=>'error', 
      //   'flash_message'=>__('app.your_profile_kyc_is_unverify'), 
      //   'status' => false
      // ]);
    }

    //Check google authentication
    $google2fa = app('pragmarx.google2fa');
    $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
    if(!$AuthUser){
      return $this->route('getWallet', [], __('app.user_is_not_authenticated'), [], 'error');
    }
    $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $request->auth);
    if(!$valid){
      return $this->route('getWallet', [], __('app.wrong_code'), [], 'error');
    }

    if(!$request->amount || $request->amount <= 0){
      return $this->route('getWallet', [], __('app.amount_usd_is_invalid'), [], 'error');
    } 

    //Withdraw amount
    $amount = $request->amount;
    $minWithdraw = 20;
    if($amount < $minWithdraw){
      return $this->route('getWallet', [], __('app.min_withdraw',['min' => $minWithdraw]), [], 'error');
      // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Min withdraw less than '.$minWithdraw]);
    }


    $coin_from_id = 5;
    $coin_to_id = $request->coin_to;

    $arr_coin = [
      5 => 'USDT',
      1 => 'BTC',
      2 => 'ETH'
    ];

    if(!isset($arr_coin[$coin_to_id])){
      return $this->route('getWallet', [], __('app.currency_error'), [], 'error');
      // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Currency balance is invalid']);
    }
    $symbol_from = $arr_coin[$coin_from_id];
    $symbol_to = $arr_coin[$coin_to_id];

    $balance = User::getBalance($user->User_ID, $coin_from_id);
    $amountFee = $amount * $this->feeWithdraw;
    // $amountFee += Money::feeGas();
    $amountFee = round($amountFee, 6);
    if($amount > $balance){
      return $this->route('getWallet', [], __('app.balance_not_enough'), [], 'error');
      // return $this->route('Your balance is not enough', [], false);
      // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=> 'Your balance is not enough']);
    }

    $address = $request->address;
    $rate = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy($symbol_to);
    $amountTo = ($amount - $amountFee) / $rate;
    // lưu lịch sử
    $arrayInsert = array(
      'Money_User' => $user->User_ID,
      'Money_USDT' => -($amount-$amountFee),
      'Money_USDTFee' => -$amountFee,
      'Money_Time' => time(),
      'Money_Comment' => 'Withdraw '.$amountTo.' '.$symbol_to.' From ' . ($amount*1) . ' ' . $symbol_from . ' To Address: '.$address,
      'Money_MoneyAction' => 2,
      'Money_MoneyStatus' => 1,
      'Money_Address' => $address,
      'Money_Currency' => $coin_from_id,
      'Money_CurrentAmount' => $amountTo,
      'Money_CurrencyFrom' => $coin_from_id,
      'Money_CurrencyTo' => $coin_to_id,
      'Money_Rate' => $rate,
      'Money_Confirm' => 0,
      'Money_Confirm_Time' => null,
      'Money_FromAPI' => 1,
    );
    $id = Money::insertGetId($arrayInsert);
    // gọi jobs
    // dispatch(new WalletJobs($id, $user->User_ID))->delay(1);

    $message = "<b> $symbol_to WITHDRAW </b>\n"
              . "ID: <b>$user->User_ID</b>\n"
              . "EMAIL: <b>$user->User_Email</b>\n"
              . "WALLET: <b>$address</b>\n"
              . "RATE: <b>$ $rate</b>\n"
              . "COIN AMOUNT: <b>".(($amount - $amountFee) / $rate)." $symbol_to</b>\n"
              . "USD AMOUNT: <b>$ ".($amount)."</b>\n"
              . "Fee AMOUNT: <b>$ ".($amountFee)."</b>\n"
              . "<b>Submit Withdraw Time: </b>\n"
              . date('d-m-Y H:i:s',time());

    dispatch(new SendTelegramJobs($message, -419982153));

    $withdraw = config('utils.action.withdraw');
    LogUser::addLogUser($user->User_ID, $withdraw['action_type'], $withdraw['message'].' '.(float)$amountTo.' '.$symbol_to.' From ' . ($amount*1) . ' ' . $symbol_from . 'To Address: '.$address, $request->ip(), 10);
    //return $this->route('getWallet', [], 'You withdraw amount '.$amount.' '.$symbol_from.' complete', 'success');
    return $this->route('getWallet', [], __('wallet.you_withdrawal_is_complete'), 'success');
  }

  public function postTransfer(Request $req){
    //dd($req);
    $user = session('user');
    $setting = $this->setting;
    $this->feeTransfer = $this->setting->fee_transfer;
      
    if(!$setting->setting_transfer){
        return $this->redirectBack('Error', [], 'error');
    }

    if ($user) {
        

        $check_custom = $user->User_Level;
        if($check_custom == 4 || $check_custom == 5){
          return $this->redirectBack(__('wallet.your_account_cannot_use_this_function'), [], 'error');
           //  return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Your account can\'t use this function!"]);
             // return $this->response(200, [], 'Your account can\'t use this function!', [], false);
        }

        $validator = Validator::make($req->all(), [
            'user' => 'required|exists:users,User_ID',
            'amount' => 'required|Numeric|min:0',
            // 'currency' => 'required|string|in:5',
            // 'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->redirectBack($value, [], 'error');
            }
        }

        if($user->User_Lock_Transfer) return $this->redirectBack(__('app.error'), [], 'error');

        //Bảo mật
        $checkProfile = DB::table('profile')->where('Profile_User', $user->User_ID)->where('Profile_Status', 1)->first();

        if(!$checkProfile || $checkProfile->Profile_Status != 1){
           return $this->redirectBack(__('app.our_profile_kyc_is_unverify'), [], 'error');
        }
        $google2fa = app('pragmarx.google2fa');
        $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
        
        if(!$AuthUser){
          return $this->redirectBack( __('app.user_is_not_authenticated'), [], 'error');
        }
        $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $req->otp);
        if(!$valid){
            return $this->redirectBack(__('app.wrong_code'), [], 'error');
        }
        
        if($req->amount <= 0){
          return $this->redirectBack(__('app.amount_usd_is_invalid'), [], 'error');
        }

        //ID người nhận
        $transferUserID  = $req->user;
        //Check User tồn tại được nhận tiền có tồn tại không???
        $checkUser = User::where('User_ID', $transferUserID)->first();
        if (!$checkUser) {
            //ngươi nhận không tồn tại
            return $this->redirectBack( __('app.the_user_id_is_not_valid'), [], 'error');
        }
        $req->currency = 5;
        //Check Array Coin
        $currency = strtoupper($req->currency);
        if($currency != 5){
          return $this->redirectBack(__('app.invalid_currency'), [], 'error');
        }
        // trừ tiền người chuyển
        $amountFee = $req->amount* $this->feeTransfer;
        $arr_coin = [
          5 => 'USDT',
        ];
        $balance = User::getBalance($user->User_ID, $currency);
        $amountFee = $req->amount* $this->feeTransfer;
        if(($req->amount+$amountFee) > $balance){
            return $this->redirectBack(__('app.your_balance_is_not_enough'), [], 'error'); 
        }
        $arrayInsert = array(
            array(
                'Money_User' => $user->User_ID,
                'Money_USDT' => -($req->amount-$amountFee),
                'Money_USDTFee' => -$amountFee,
                'Money_Time' => time(),
                'Money_Comment' => 'Transfer $' . ($req->amount*1). ' to ID:'.$transferUserID,
                'Money_MoneyAction' => 7,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => $currency,
                'Money_CurrentAmount' => $req->amount-$amountFee,
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_Confirm_Time' => null,
                'Money_FromAPI' => 1,
            ),
            array(
                'Money_User' => $transferUserID,
                'Money_USDT' => $req->amount-$amountFee,
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => 'Receive $' .($req->amount*1). ' from ID:'.$user->User_ID,
                'Money_MoneyAction' => 7,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => $currency,
                'Money_CurrentAmount' => $req->amount-$amountFee,
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_Confirm_Time' => null,
                'Money_FromAPI' => 1,
            )
        );
        $transferType = config('utils.action.transfer');
        $logArray = array(
            array(
                'action'=>$transferType['action_type'],
                'user'=>$user->User_ID,
                'comment'=>'Transfer $' . $req->amount. ' to ID:'.$transferUserID,
                'ip'=>$req->ip(),
                'datetime'=>date('Y-m-d H:i:s'),                    
                'action_id'=>9,
            ),
            array(
                'action'=>$transferType['action_type'],
                'user'=>$transferUserID,
                'comment'=>'Receive $' . $req->amount. ' to ID:'.$user->User_ID,
                'ip'=>$req->ip(),
                'datetime'=>date('Y-m-d H:i:s'),                    
                'action_id'=>9,
            )
        );
        LogUser::insert($logArray);
        Money::insert($arrayInsert);
        return $this->redirectBack(__('wallet.you_transfer_to_id_has_completed'), 'success'); 


    }  
  }
}
