<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Crypt;

use App\Model\User;
use App\Model\Profile;
use App\Model\GoogleAuth;
use App\Model\Investment;
use App\Model\LogUser;
use App\Model\Money;
use App\Model\userBalance;
use App\Model\Golds;
use App\Model\Eggs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

use App\Http\Controllers\API\WalletController;
class GoldController extends Controller
{
    public $feeSwap = 0;
	public $keyHash	 = 'gu5pU24FREhiy'; 
    public function __construct()
	{
		$this->middleware('auth:api', ['only' => ['postDepositGold','postWithdrawGold']]);

	}
    public function postDepositGold(Request $req)
    {
        $user = Auth::user();
        // $golds = Golds::where('ID', "$req->id")->first();
        // return $golds;
        if ($user) {


            $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();

            if ($checkSpam == null) {
                
                return $this->response(200, [], 'Misconduct!', [], false);
            }else{
                DB::table('string_token')->where('User', $user->User_ID)->delete();
            }

            $check_custom = $user->User_Level;
            if($check_custom == 4){
               //  return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Your account can\'t use this function!"]);
               	return $this->response(200, [], __('app.your_account_cannot_use_this_function'), [], false);
            }

            $validator = Validator::make($req->all(), [
				'id' => 'required',
				'amount' => 'required|Numeric|min:0',
			]);
	
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $value) {
                    return $this->response(200, [], $value, $validator->errors(), false);
                }
            }

            // trừ tiền người chuyển
            $golds = Golds::where('ID', "$req->id")->first();
            if(!$golds){
                return $this->response(200, [], __('app.wrong_package'), [], false);
            }
            $amount_tranfer = $golds->Price*$req->amount;
            $amount_received = $golds->Gold*$req->amount;
            $amountFee = $golds->Gold*$req->amount*$this->feeSwap;

            $balance = User::getBalance($user->User_ID, 3);
            if($amount_tranfer > $balance){
                return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
            }
            $rate = $amount_tranfer / $amount_received;
            $arrayInsert = array(
                array(
                    'Money_User' => $user->User_ID,
                    'Money_USDT' => -$amount_tranfer,
                    'Money_USDTFee' => 0,
                    'Money_Time' => time(),
                    'Money_Comment' => 'Pay ' . ($amount_tranfer*1). ' EUSD to '. ($amount_received*1) .' GOLD',
                    'Money_MoneyAction' => 33,
                    'Money_MoneyStatus' => 1,
                    'Money_Address' => null,
                    'Money_Currency' => 3,
                    'Money_CurrentAmount' => $amount_tranfer,
                    'Money_Rate' => 1,
                    'Money_Confirm' => 0,
                    'Money_Confirm_Time' => null,
                    'Money_FromAPI' => 1,
                ),
                array(
                    'Money_User' => $user->User_ID,
                    'Money_USDT' => $amount_received-$amountFee,
                    'Money_USDTFee' => 0,
                    'Money_Time' => time(),
                    'Money_Comment' => 'Receive ' .($amount_received*1). ' GOLD from '.$amount_tranfer. ' EUSD',
                    'Money_MoneyAction' => 33,
                    'Money_MoneyStatus' => 1,
                    'Money_Address' => null,
                    'Money_Currency' => 9,
                    'Money_CurrentAmount' => $amount_received-$amountFee,
                    'Money_Rate' => $rate,
                    'Money_Confirm' => 0,
                    'Money_Confirm_Time' => null,
                    'Money_FromAPI' => 1,
                )
            );
            $transferType = config('utils.action.deposit_GOLD');
            $logArray = array(
                array(
                    'action'=>$transferType['action_type'],
                    'user'=>$user->User_ID,
                    'comment'=>'Pay ' . $amount_tranfer. ' EUSD to Get'.$amount_received. ' GOLD',
                    'ip'=>$req->ip(),
                    'datetime'=>date('Y-m-d H:i:s'),
                    'action_id'=>19,
                ),
                array(
                    'action'=>$transferType['action_type'],
                    'user'=>$user->User_ID,
                    'comment'=>'Receive ' . $amount_received. ' GOLD from '.$amount_tranfer.' EUSD',
                    'ip'=>$req->ip(),
                    'datetime'=>date('Y-m-d H:i:s'),
                    'action_id'=>19,
                )
            );
            LogUser::insert($logArray);
            Money::insert($arrayInsert);
            //check commission buy gold
            Money::checkCommission($user, 12, 3, $amount_tranfer);
            Money::checkAgencyCommission($user, 33, 3, $amount_tranfer);
            $balanceGold = User::getBalance($user->User_ID, 9);
            $balanceEUSD = User::getBalance($user->User_ID, 3);
            return $this->response(200, ['balance'=>['GOLD'=>$balanceGold, 'EUSD'=>$balanceEUSD]], __('app.you_deposit_gold_successful'), [], true); 

 
        }  
    }

    //Capcha V3
    public function postWithdrawGold(Request $req)
    {
	    include(app_path() . '/functions/xxtea.php');
        $user = Auth::user();
        // $golds = Golds::where('ID', "$req->id")->first();
        // return $golds;
       
        if ($user) {
            $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();

            if ($checkSpam == null) {
                
                return $this->response(200, [], 'Misconduct!', [], false);
            }else{
                DB::table('string_token')->where('User', $user->User_ID)->delete();
            }

            $check_custom = $user->User_Level;
            if($check_custom == 4){
               //  return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Your account can\'t use this function!"]);
               	return $this->response(200, [], __('app.your_account_cannot_use_this_function'), [], false);
            }

            $validator = Validator::make($req->all(), [
				'amount' => 'required|numeric|min:2000',
			]);
	
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $value) {
                    // return $error;
                    return $this->response(200, [], $value, $validator->errors(), false);
                }
            }
            if($req->Server){
                $data = xxtea_decrypt(base64_decode($req->Server),$this->keyHash);
                if($data != $req->CodeSpam){
                    return $this->response(200, [], 'Misconduct!', [], false);
                }
            }else{
                $captcha = WalletController::checkCaptcha($req->token);
                if(!$captcha){
                    return $this->response(200, [], __('app.captcha_does_not_exist'), [], false);
                }
            }
            
            //Bảo mật
            $checkProfile = DB::table('profile')->where('Profile_User', $user->User_ID)->where('Profile_Status', 1)->first();
    
            if(!$checkProfile || $checkProfile->Profile_Status != 1){
               return $this->response(200, [], __('app.our_profile_kyc_is_unverify'), [], false);
            }
            // $google2fa = app('pragmarx.google2fa');
            // $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
            
            // if(!$AuthUser){
	        //     return $this->response(200, [], 'User Unable Authenticator', [], false);
            // }
            // $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $req->otp);
            // if(!$valid){
            //     return $this->response(200, [], 'Wrong code', [], false);
            // }

            // trừ tiền người chuyển

            //check verify phone
            // if(!$user->User_Phone) return $this->response(200, [], 'You have not verified phone number yet!', [], false);

            $money = Money::where([
                'Money_User' => $user->User_ID,
                'Money_MoneyAction' => 34,
            ])->get();

            if(count($money) > 1){
                $users = User::join('profile', 'users.User_ID', '=', 'profile.Profile_User')->where([
                    'users.User_Parent' => $user->User_ID,
                    'profile.Profile_Status' => 1,
                ])->limit(2)->get();
                
                if(count($users) < 2) return $this->response(200, [], __('app.you_have_not_member_kyc_enough_to_withdraw_gold'), [], false);
            }

            if(count($money) > 2){
                $egg = Eggs::where([
                    'Owner' => $user->User_ID,
                    'Status' => 1,
                ])->where('ActiveTime', '!=', 0)->limit(1)->first();

                if(!$egg) return $this->response(200, [], __('app.you_have_no_activated_eggs_yet'), [], false);
            }

            $rate = 200;
            if($req->amount >= 9000) $rate = 180;
            if($req->amount >= 15000) $rate = 150;
            $amount_received = $req->amount/$rate;
            $amount_tranfer = $req->amount;
            $amountFee = $req->amount*$this->feeSwap;
            if($req->amount % $rate != 0){
                return $this->response(200, [], 'Amount wrong!', [], false); 
            }

            $balance = User::getBalance($user->User_ID, 9);
            if($amount_tranfer > $balance){
                return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
            }
            $arrayInsert = array(
                array(
                    'Money_User' => $user->User_ID,
                    'Money_USDT' => -($amount_tranfer+$amountFee),
                    'Money_USDTFee' => 0,
                    'Money_Time' => time(),
                    'Money_Comment' => 'Sell ' . ($amount_tranfer*1). ' GOLD to Get'. ($amount_received*1) .' EUSD',
                    'Money_MoneyAction' => 34,
                    'Money_MoneyStatus' => 1,
                    'Money_Address' => null,
                    'Money_Currency' => 9,
                    'Money_CurrentAmount' => $amount_tranfer+$amountFee,
                    'Money_Rate' => 1/150,
                    'Money_Confirm' => 0,
                    'Money_Confirm_Time' => null,
                    'Money_FromAPI' => 1,
                ),
                array(
                    'Money_User' => $user->User_ID,
                    'Money_USDT' => $amount_received,
                    'Money_USDTFee' => 0,
                    'Money_Time' => time(),
                    'Money_Comment' => 'Receive ' .($amount_received*1). ' EUSD from '.$amount_tranfer. ' GOLD',
                    'Money_MoneyAction' => 34,
                    'Money_MoneyStatus' => 1,
                    'Money_Address' => null,
                    'Money_Currency' => 3,
                    'Money_CurrentAmount' => $amount_received,
                    'Money_Rate' => 1,
                    'Money_Confirm' => 0,
                    'Money_Confirm_Time' => null,
                    'Money_FromAPI' => 1,
                )
            );
            $transferType = config('utils.action.withdraw_GOLD');
            $logArray = array(
                array(
                    'action'=>$transferType['action_type'],
                    'user'=>$user->User_ID,
                    'comment'=>'Sell ' . $amount_received. ' EUSD to Get '.$amount_tranfer. ' GOLD',
                    'ip'=>$req->ip(),
                    'datetime'=>date('Y-m-d H:i:s'),
                    'action_id'=>20,
                ),
                array(
                    'action'=>$transferType['action_type'],
                    'user'=>$user->User_ID,
                    'comment'=>'Receive ' . $amount_received. ' EUSD from '.$amount_tranfer.' EUSD',
                    'ip'=>$req->ip(),
                    'datetime'=>date('Y-m-d H:i:s'),                    
                    'action_id'=>20,
                )
            );
            LogUser::insert($logArray);
            Money::insert($arrayInsert);
           
            $balanceGold = User::getBalance($user->User_ID, 9);
            $balanceEUSD = User::getBalance($user->User_ID, 3);
            return $this->response(200, ['balance'=>['GOLD'=>$balanceGold, 'EUSD'=>$balanceEUSD]], __('app.you_withdraw_gold_successful'), [], true); 
            // return $this->response(200, [], 'You withdraw GOLD complete', [], true); 

 
        }  
        return $this->response(200, [], __('app.no_user_found'), [], false); 
    }
}
