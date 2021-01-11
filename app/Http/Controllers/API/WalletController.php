<?php

namespace App\Http\Controllers\API;

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

use App\Jobs\WalletJobs;
use App\Model\Investment;
use App\Model\LogUser;
use App\Model\Profile;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Jobs\SendMailJobs;
use App\Jobs\SendTelegramJobs;
class Walletcontroller extends Controller
{

    public $feeWithdraw = 0.02;
    public $feeTransfer = 0;
    public $feeSwap = 0;
    public $addressDepositEBP;
    
    
    public function __construct(){
        $this->middleware('auth:api');
        $this->middleware('userPermission', ['only' => ['postTransfer', 'postWithdraw']]);
        $this->feeWithdraw = config('coin.EUSD.WithdrawFee');
        $this->feeTransfer = config('coin.EUSD.TransferFee');
        $this->addressDepositEBP = config('coin.EBP.addressDeposit');
	}
    
    public function getDeposit(Request $req){
        $addressArray = array(1=>'BTC', 2=>'ETH', 3=>'EUSD', 4=>'RBD', 5=>'USDT', 8=>'EBP', 10=>'DASH', 11=>'BCH', 12=>'LTC', 13=>'TRX', 14=>'EOS', 15=>'XRP');
		$user = Auth::user();
		// kiểm tra user này có ví chưa
		$coin = $req->coin;
		if(!isset($addressArray[$coin])){
			return $this->response(200, [], __('app.coin_does_not_exist'), [], true);
        }
        $symbol2 = $addressArray[$coin];
        if($coin == 8){
            $Qr = 'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$this->addressDepositEBP.'&choe=UTF-8';
            $returnData = array('symbol'=>$addressArray[$coin], 'address'=>$this->addressDepositEBP, 'Qr'=>$Qr, 'symbol2'=>$symbol2);
            return $this->response(200, $returnData, '', [], true);
        }else{
            if($coin == 3){
                $coin = 5;
            }
            $address = Wallet::checkWallet($user->User_ID, $coin);
    
            if($address){
                if($coin == 1){
                    $Qr = 'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl=bitcoin:'.$address->Address_Address.'&choe=UTF-8';
                }else{
                    $Qr = 'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$address->Address_Address.'&choe=UTF-8';
                }
                $returnData = array('symbol'=>$addressArray[$coin], 'address'=>$address->Address_Address, 'Qr'=>$Qr, 'symbol2'=>$symbol2);
                return $this->response(200, $returnData, '', [], true);
            }
        }
		$createAddress = app('App\Http\Controllers\API\CoinbaseController')->createAddress($coin, $user->User_ID);
		if($createAddress){
			// lưu vào db
			$arrayInsert = array(
				'Address_Currency' => $coin,
				'Address_Address' => $createAddress['address'],
				'Address_User' => $user->User_ID,
				'Address_PrivateKey' => null,
				'Address_HexAddress' => null,
				'Address_CreateAt' => date('Y-m-d H:i:s'),
				'Address_UpdateAt' => date('Y-m-d H:i:s'),
				'Address_IsUse' => 1,
				'Address_Comment' => null
			);
            Wallet::insert($arrayInsert);
            if($coin == 1){
				$Qr = 'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl=bitcoin:'.$createAddress['address'].'&choe=UTF-8';
			}else{
				$Qr = 'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$createAddress['address'].'&choe=UTF-8';
			}
            $returnData = array('symbol'=>$addressArray[$coin], 'address'=>$createAddress['address'], 'Qr'=>$Qr);
			return $this->response(200, $returnData, '', [], true);
		}
        $returnData = array('symbol'=>$addressArray[$coin], 'address'=>'We are updating', 'Qr'=>'');
        return $this->response(200, $returnData, '', [], true);
        // $createAddress = app('App\Http\Controllers\API\CoinbaseController')->createAddress($coin, $user->User_ID);
        // if ($createAddress) {
        //     // lưu vào db
        //     $arrayInsert = array(
        //         'Address_Currency' => $coin,
        //         'Address_Address' => $createAddress['address'],
        //         'Address_User' => $user->User_ID,
        //         'Address_PrivateKey' => null,
        //         'Address_HexAddress' => null,
        //         'Address_CreateAt' => date('Y-m-d H:i:s'),
        //         'Address_UpdateAt' => date('Y-m-d H:i:s'),
        //         'Address_IsUse' => 1,
        //         'Address_Comment' => null
        //     );
        //     Wallet::insert($arrayInsert);
        //     return $this->response(200, $createAddress, '', [], false);
        // }
    }

    public function postTransfer(Request $req){
        $user = Auth::user();

        if ($user) {


            $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();

            if ($checkSpam == null) {
                
                return $this->response(200, [], __('app.misconduct'), [], false);
            }else{
                DB::table('string_token')->where('User', $user->User_ID)->delete();
            }

            $check_custom = $user->User_Level;
            if($check_custom == 4){
               //  return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Your account can\'t use this function!"]);
               	// return $this->response(200, [], 'Your account can\'t use this function!', [], false);
            }

            $validator = Validator::make($req->all(), [
				'user' => 'required|exists:users,User_ID',
				'amount' => 'required|Numeric|min:0',
                'currency' => 'required|string',
                'token' => 'required|string'
			]);
	
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $value) {
                    return $this->response(200, [], $value, $validator->errors(), false);
                }
            }

            if($user->User_Lock_Transfer) return $this->response(200, [], __('app.error'), [], false);

            $captcha = $this->checkCaptcha($req->token);
            // return $this->response(200, [], $req->token, [], false);
            if(!$captcha){
                return $this->response(200, [], __('app.captcha_does_not_exist'), [], false);
            }

            //Bảo mật
            $checkProfile = DB::table('profile')->where('Profile_User', $user->User_ID)->where('Profile_Status', 1)->first();
    
            if(!$checkProfile || $checkProfile->Profile_Status != 1){
               return $this->response(200, [], __('app.our_profile_kyc_is_unverify'), [], false);
            }
            $google2fa = app('pragmarx.google2fa');
            $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
            
            if(!$AuthUser){
	            return $this->response(200, [], __('app.user_is_not_authenticated'), [], false);
            }
            $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $req->otp);
            if(!$valid){
                return $this->response(200, [], __('app.wrong_code'), [], false);
            }
            
            if($req->amount <= 0){
	            return $this->response(200, [], __('app.amount_usd_is_invalid'), [], false);
            }

            //ID người nhận
            $transferUserID  = $req->user;
            //Check User tồn tại được nhận tiền có tồn tại không???
            $checkUser = User::where('User_ID', $transferUserID)->first();
            if (!$checkUser) {
                //ngươi nhận không tồn tại
                return $this->response(200, [], __('app.the_user_id_is_not_valid'), [], false);
            }

            //Check Array Coin
            $currency = strtoupper($req->currency);
            if($currency != 'EUSD'){
	            return $this->response(200, [], __('app.invalid_currency'), [], false);
            }
            // trừ tiền người chuyển
            $amountFee = $req->amount* $this->feeTransfer;
            $arr_coin = [
                'EUSD' => 3,
                'USDT' => 5,
                'EBP' => 8,
            ];
            $balance = User::getBalance($user->User_ID, $arr_coin[$req->currency]);
            $amountFee = $req->amount* $this->feeTransfer;
            if(($req->amount+$amountFee) > $balance){
                return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
            }
            $arrayInsert = array(
                array(
                    'Money_User' => $user->User_ID,
                    'Money_USDT' => -$req->amount,
                    'Money_USDTFee' => $amountFee,
                    'Money_Time' => time(),
                    'Money_Comment' => 'Transfer $' . ($req->amount*1). ' to ID:'.$transferUserID,
                    'Money_MoneyAction' => 7,
                    'Money_MoneyStatus' => 1,
                    'Money_Address' => null,
                    'Money_Currency' => $arr_coin[$req->currency],
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
                    'Money_Currency' => $arr_coin[$req->currency],
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
            $checkBalance = User::checkBlockBalance($user->User_ID);
           
            return $this->response(200, ['balance'=>[$req->currency=>(float)($balance-$req->amount)], 'transferUserID'=>(int)$transferUserID], __('You transfer to ID: '.$transferUserID.' complete'), [], true); 

 
        }  
    }

    public function postWithdraw(Request $req){
        $user = $req->user();
        //check spam
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
        
        if($checkSpam == null){
            //khoong toonf taij
            return $this->response(200, [], 'Misconduct', [], false);
            // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Misconduct!']);
        }
        else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
        $validator = Validator::make($req->all(), [
            'address' => 'required|string|min:1|nullable',
            // 'otp' => 'required|numeric|nullable',
            'amount' => 'required|numeric|min:0|nullable',
            'coin_from' => 'required|string|nullable',
            'coin_to' => 'required|string|nullable',
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        if($user->User_Lock_Withdraw) return $this->response(200, [], __('app.error'), [], false);

        $captcha = $this->checkCaptcha($req->token);

        if(!$captcha){
            return $this->response(200, [], __('app.captcha_does_not_exist'), [], false);
        }
		
        $user = User::where('User_ID', $user->User_ID)->first();
        $check_custom = $user->User_Level;
        if($check_custom == 4){
            return $this->response(200, [], __('app.your_account_cannot_use_this_function'), [], false);
        }

        //Bảo mật
		$checkProfile = Profile::where('Profile_User', $user->User_ID)->first();

		if(!$checkProfile || $checkProfile->Profile_Status != 1){
            return $this->response(200, [], __('app.your_profile_kyc_is_unverify'), [], false);
		}
		$google2fa = app('pragmarx.google2fa');
        $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
        if(!$AuthUser){
            return $this->response(200, [], __('app.user_is_not_authenticated'), [], false);
        }
        $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $req->otp);
        if(!$valid){
            return $this->response(200, [], __('app.wrong_code'), [], false);
        }
	    
		if(!$req->amount || $req->amount <= 0){
            return $this->response(200, [], __('app.amount_usd_is_invalid'), [], false);
		} 
		//sỐ TIỀN MUỐN RÚT
        $amount = $req->amount;
        $minWithdraw = config('coin.EUSD.WithdrawMin');;
        if($amount < $minWithdraw){
            return $this->response(200, [], __('Min Withdraw $'.$minWithdraw), [], false);
        }
        //loại coin mà nó muốn nhận khi rút
        $symbol_from = strtoupper($req->coin_from);
        //Rút từ ví nào
        $symbol_to = strtoupper($req->coin_to);
        //Loại balance hiện có
        $arr_coin = [
            'EUSD' => 3,
            'USDT' => 5,
            'EBP' => 8,
        ];
        if(!isset($arr_coin[$symbol_from])){
            return $this->response(200, [], __('app.currency_balance_is_invalid'), [], false);
        }
        $coin_from_id = $arr_coin[$symbol_from];
        // echo $coin_from_id;exit;
        if($coin_from_id != 3){
            return $this->response(200, [], __('app.currency_balance_is_invalid'), [], false);
        }
        if(!isset($arr_coin[$symbol_to])){
            return $this->response(200, [], __('app.currency_withdraw_is_invalid'), [], false);
        }
        $coin_to_id = $arr_coin[$symbol_to];
		
        //Các loại rate
        $CurrentAmount_Temp = 0;
        if($coin_from_id == 3){
            if($coin_to_id == 5){
	            
            }
            else{
                return $this->response(200, [], __('app.error_of_withdrawal'), [], false);
            }
        }else{
            if($coin_from_id == 8){
                if($coin_to_id == 8){
                    return $this->response(200, [], __('app.error_of_withdrawal'), [], false);
                }
                else{
                    //ERorr wwhen currency == BTC or ETH
                    return $this->response(200, [], __('app.error_of_withdrawal'), [], false);
                }
            }
        }
        //Balance
        $balance = User::getBalance($user->User_ID, 3);
        $amountFee = $amount * ($this->feeWithdraw/100);
        // if($amountFee < 3){
        //     $amountFee = 3;
        // }
        $amountFee += Money::feeGas();
        $amountFee = round($amountFee, 6);
        if(($amount) > $balance){
            return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
        }
        //kiểm tra có lệnh rút nào chưa
        $withdraw = Money::where('Money_MoneyAction', 2)->where('Money_MoneyStatus', 0)->where('Money_User', $user->User_ID)->first();
        if($withdraw){
            return $this->response(200, ['balance'=>$balance], __('app.please_wait_for_the_withdrawal_to_be_approved'), [], false); 
        }
        $address = $req->address;
        $rate = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy($symbol_to);
        // lưu lịch sử
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => -$amount+$amountFee,
            'Money_USDTFee' => -$amountFee,
            'Money_Time' => time(),
            'Money_Comment' => 'Withdraw ' . ($amount*1) . ' ' . $symbol_from,
            'Money_MoneyAction' => 2,
            'Money_MoneyStatus' => 1,
            'Money_Address' => $address,
            'Money_Currency' => $coin_from_id,
            'Money_CurrentAmount' => ($amount - $amountFee) / $rate,
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
        
        dispatch(new SendTelegramJobs($message, -451958528));

        // Log
        $withdraw = config('utils.action.withdraw');
        LogUser::addLogUser($user->User_ID, $withdraw['action_type'], $withdraw['message'].' '.(float)$amount. ' ' . $symbol_from.' to wallet: '.$user->User_WalletAddress, $req->ip(), 10);
        $balance = User::getBalance($user->User_ID, 3);
        return $this->response(200, ['balance'=>array($symbol_from=>(float)$balance), 'wallet'=>$user->User_WalletAddress], __('You withdraw '.$amount.' complete ' . $symbol_from), [], true); 
        
		//chi tự động ở đây 
		// if($user->User_Level == 0 || $user->User_Level == 1){
		// 	$req = new Request;
		// 	$payWallet = Money::where('Money_ID', $money->Money_ID)->get();
		// 	$payStatus = app('App\Http\Controllers\System\AutoPayController')->pay_money($user, $payWallet, $req);
		// }
    }

    public function postWithdrawBackup(Request $req)
    {
      	$symbol = array('BTC'=>1, 'ETH'=>2, 'USDT'=>5, 'EBP'=>8, 'EUSD'=>5);
        $user = $req->user();
        if ($user) {

            $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();

            if ($checkSpam == null) {
                //khoong toonf taij
                return $this->response(200, [], 'Misconduct!', [], false);
            }else{
                //DB::table('string_token')->where('User', $user->User_ID)->delete();
            }

            $check_custom = $user->User_Level;
            if($check_custom == 4){
               //  return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Your account can\'t use this function!"]);
               	return $this->response(200, [], __('app.your_account_cannot_use_this_function'), [], false);
            }

            $validator = Validator::make($req->all(), [
	            'wallet' => 'required',
				'amount' => 'required|Numeric',
				'currency' => 'required',
			]);
	
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $value) {
                    return $this->response(200, [], $value, $validator->errors(), false);
                }
            }
            
            
            //Bảo mật
            $checkProfile = DB::table('profile')->where('Profile_User', $user->User_ID)->where('Profile_Status', 1)->first();
    
            if(!$checkProfile || $checkProfile->Profile_Status != 1){
            //    return $this->response(200, [], 'Profile KYC Is Unverify!', [], false);
            }
            $google2fa = app('pragmarx.google2fa');
            $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
            
            if(!$AuthUser){
	            return $this->response(200, [], __('app.user_is_not_authenticated'), [], false);
            }
            $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $req->otp);
            if(!$valid){
                return $this->response(200, [], __('app.wrong_code'), [], false);
            }
            
            if($req->amount <= 0){
	            return $this->response(200, [], __('app.amount_usd_is_invalid'), [], false);
            }

            //Check Array Coin
            if($symbol[$req->currency]!=5){
	            return $this->response(200, [], __('app.invalid_currency'), [], false);
            }

            //Balance
            $balance = User::getBalance($user->User_ID, $req->currency);
            $amountFee = $req->amount* $this->feeWithdraw;
            if(($req->amount+$amountFee) > $balance){
	        	return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
            }
            //kiểm tra có lệnh rút nào chưa
            $withdraw = Money::where('Money_MoneyAction', 2)->where('Money_MoneyStatus', 0)->where('Money_User', $user->User_ID)->first();
            if($withdraw){
	            return $this->response(200, ['balance'=>$balance], __('app.please_wait_for_the_withdrawal_to_be_approved'), [], false); 
            }
            $rate = 0.1;
            // lưu lịch sử
            $arrayInsert = array(
                'Money_User' => $user->User_ID,
                'Money_USDT' => -$req->amount,
                'Money_USDTFee' => -$amountFee,
                'Money_Time' => time(),
                'Money_Comment' => 'Withdraw $' . $req->amount,
                'Money_MoneyAction' => 2,
                'Money_MoneyStatus' => 0,
                'Money_Address' => $req->wallet,
                'Money_Currency' => 8,
                'Money_CurrentAmount' => ($req->amount - $amountFee) / $rate,
                'Money_Rate' => $rate,
                'Money_Confirm' => 0,
                'Money_Confirm_Time' => null,
                'Money_FromAPI' => 1,
            );
            $id = Money::insertGetId($arrayInsert);
            
            // gọi jobs
            dispatch(new WalletJobs($id, $user->User_ID))->delay(1);
            
            // Log
            $withdraw = config('utils.action.withdraw');
            LogUser::addLogUser($user->User_ID, $withdraw['action_type'], $withdraw['message'].'$'.(float)$req->amount.' to wallet:'.$req->wallet, $req->ip(), 10);


            return $this->response(200, ['balance'=>(float)$balance-($req->amount+$amountFee), 'wallet'=>$req->wallet], __('You withdraw $'.$req->amount.' successful'), [], true); 
             
        }
        
    }

    public static function checkCaptcha($token){
        $url = config('utils.captcha_url');
        $data = ['secret' => config('utils.secret_key'), 'response' => $token];
        $options = ['http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]];
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $response_keys = json_decode($response, true);

        return $response_keys['success'];
    }
}
