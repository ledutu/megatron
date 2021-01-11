<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Storage;
use App\Model\Notification;

use App\Model\Money;
use App\Model\User;
use App\Model\LogUser;
use App\Model\Investment;
use Illuminate\Support\Facades\Auth;
use App\Model\Ticket;
use App\Model\TicketSubject;
use Illuminate\Support\Facades\Crypt;
use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;
use Hash, Image, DB, Validator;
use App\Jobs\SendMail;
use App\Http\Controllers\API\WalletController;
class GiftCodeController extends Controller{
	
    public function __construct(){
		$this->middleware('auth:api');
		$this->middleware('userPermission', ['only' => ['postBuy']]);
	}
	public function getIndex(Request $req){
		$user = $req->user();
		$listPackage = DB::table('package_giftcode')->where('Package_Status', 1)->orderBy('Package_ID', 'ASC')
						->select('Package_ID', 'Package_Name', 'Package_Amount', 'Package_Quantity', 'Package_PriceEUSD', 'Package_PriceGOLD', 'Package_Image', 'Package_Type')
						->get();
		$listGiftCodeUser = DB::table('giftcode')
							->leftJoin('package_giftcode', 'GiftCode_Package', 'Package_ID')
							->where('GiftCode_Status', '!=', -1)
							->whereRaw('(GiftCode_User = '.$user->User_ID.' OR GiftCode_User_Use ='.$user->User_ID.')')
							->orderByRaw('GiftCode_Status ASC, GiftCode_ID DESC')
							->selectRaw('
										GiftCode_ID as ID, GiftCode_Status as Status, GiftCode_User as User, GiftCode_User_Use as UserUse, GiftCode_Code as Code, 
										GiftCode_Amount as Amount, CAST( package_giftcode.Package_Type AS INT ) as Currency, GiftCode_Expiration_Time as Expire, giftcode.Updated_at as Time
										')
							->get();
		$list['Buy'] = $listGiftCodeUser->where('User', $user->User_ID);
		$list['Use'] = $listGiftCodeUser->where('UserUse', $user->User_ID);

        return $this->response(200, ['User'=>$list, 'Package'=>$listPackage], '', [], true); 
	}
	public function postUse(Request $req){
		$user = $req->user();
        $validator = Validator::make($req->all(), [
			'GiftCode' => 'required|string|regex:/^[a-zA-Z0-9]+$/u',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }
		$giftcode = trim($req->GiftCode);
		$checkGiftCode = DB::table('giftcode')
						->leftJoin('package_giftcode', 'GiftCode_Package', 'Package_ID')
						->where('GiftCode_Code', $giftcode)
						->first();
		if(!$checkGiftCode){
			return $this->response(200, [], 'Gift Code isn\'t exist!', [], false); 
		}
		if($checkGiftCode->GiftCode_Status == -1){
			return $this->response(200, [], 'Gift Code has expired!', [], false); 
		}
		if($checkGiftCode->GiftCode_Status == 1 || $checkGiftCode->GiftCode_User_Use){
			return $this->response(200, [], 'Gift Code used!', [], false); 
		}
		$ArrCurrency = DB::table('currency')->pluck('Currency_Symbol', 'Currency_ID')->toArray();
		$amount = $checkGiftCode->GiftCode_Amount;
		$currency = $checkGiftCode->Package_Type;
		// dd($ArrCurrency[$currency]);
		$arrayInsert = array(
			'Money_User' => $user->User_ID,
			'Money_USDT' => $amount,
			'Money_USDTFee' => 0,
			'Money_Time' => time(),
			'Money_Comment' => 'Use Gift Code ' .($giftcode). ' receive '.$amount. ' '.$ArrCurrency[$currency],
			'Money_MoneyAction' => 48,
			'Money_MoneyStatus' => 1,
			'Money_Address' => null,
			'Money_Currency' => $currency,
			'Money_CurrentAmount' => $amount,
			'Money_Rate' => 1,
			'Money_Confirm' => 0,
			'Money_Confirm_Time' => null,
			'Money_FromAPI' => 1,
		);
		
		$giftcodeLog = config('utils.action.use_gift_code');
		$logArray = array(
			'action'=>$giftcodeLog['action_type'],
			'user'=>$user->User_ID,
			'comment'=> 'Use Gift Code ' .($giftcode). ' receive '.$amount. ' '.$ArrCurrency[$currency],
			'ip'=>$req->ip(),
			'datetime'=>date('Y-m-d H:i:s'),
			'action_id'=>48,
		);
		$updateCode = DB::table('giftcode')
						->leftJoin('package_giftcode', 'GiftCode_Package', 'Package_ID')
						->where('GiftCode_Code', $giftcode)
						->update(['GiftCode_Status' => 1, 'GiftCode_User_Use' => $user->User_ID]);
		LogUser::insert($logArray);
		Money::insert($arrayInsert);
		$balanceGold = User::getBalance($user->User_ID, 9);
		$balanceEUSD = User::getBalance($user->User_ID, 3);
		return $this->response(200, ['Balance'=>['GOLD'=>$balanceGold, 'EUSD'=>$balanceEUSD]], __('app.you_use_gift_code_successful'), [], true); 
	}	
	
	public function postBuy( Request $req){

		$user = $req->user();
		$checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
		if ($checkSpam == null) {
			// return $this->response(200, [], 'Misconduct!', [], false);
		}else{
			DB::table('string_token')->where('User', $user->User_ID)->delete();
		}
        $validator = Validator::make($req->all(), [
			'Package' => 'required|exists:package_giftcode,Package_ID',
			'Currency' => 'required|numeric|in:3,9',
			'Amount' => 'required|numeric|in:100,500,1000',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

		// $captcha = app('App\Http\Controllers\API\WalletController')->checkCaptcha($req->token);
		// if(!$captcha){
		// 	return $this->response(200, [], 'Captcha isn\'t exist!', [], false);
		// }

		$package = $req->Package;
		$check_package = DB::table('package_giftcode')->where('Package_ID', $package)->first();
		$currency = $req->Currency;
		$balance = User::getBalance($user->User_ID, $currency);
		if($currency == 3){
			$amount = $check_package->Package_PriceEUSD;
		}else{
			return $this->response(200, [], 'Error!', [], false);
			$amount = $check_package->Package_PriceGOLD;
		}
		if($balance < $amount){
			return $this->response(200, [], __('app.your_balance_is_not_enough'), [], false);
		}
		// $quantity = $check_package->Package_Quantity;
		$totalAmount = $check_package->Package_Quantity*100;
		$amountPerCode = $req->Amount;
		$quantity = $totalAmount/$amountPerCode;
		$arrayInsert = array(
			'Money_User' => $user->User_ID,
			'Money_USDT' => -$amount,
			'Money_USDTFee' => 0,
			'Money_Time' => time(),
			'Money_Comment' => 'Buy '.$quantity.' Gift Code',
			'Money_MoneyAction' => 49,
			'Money_MoneyStatus' => 1,
			'Money_Address' => null,
			'Money_Currency' => $currency,
			'Money_CurrentAmount' => $amount,
			'Money_Rate' => 1,
			'Money_Confirm' => 0,
			'Money_Confirm_Time' => null,
			'Money_FromAPI' => 1,
		);
		$giftcode = config('utils.action.buy_gift_code');
		$logArray = array(
			'action'=>$giftcode['action_type'],
			'user'=>$user->User_ID,
			'comment'=> 'Buy '.$quantity.' Gift Code',
			'ip'=>$req->ip(),
			'datetime'=>date('Y-m-d H:i:s'),
			'action_id'=>49,
		);
		LogUser::insert($logArray);
		Money::insert($arrayInsert);
		//check commission buy gift code
		Money::checkCommission($user, 14, $currency, $amount);
		Money::checkAgencyCommission($user, 49, $currency, $amount);
		for ($i = 0; $i < $quantity; $i++) {
			$code = $this->codeRandomString();
			$time = time();
			$time_end = strtotime("+3 year");
			$insert[] = [
	            'GiftCode_Code' => $code,
	            'GiftCode_Amount' => $amountPerCode,
	            'GiftCode_Package' => $package,
	            'GiftCode_Time' => $time,
	            'GiftCode_Expiration_Time' => -1,
	            'GiftCode_User' => $user->User_ID,
	            'GiftCode_Status' => 0,
	        ];
		}
        $inserStatus = DB::table('giftcode')->insert($insert);
        if ($inserStatus) {
			$balanceGold = User::getBalance($user->User_ID, 9);
			$balanceEUSD = User::getBalance($user->User_ID, 3);
			return $this->response(200, ['Balance'=>['GOLD'=>$balanceGold, 'EUSD'=>$balanceEUSD]], __('app.buy_gift_code_successful'), [], true); 
        }
		return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
	}

	public function codeRandomString($length = 12)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $checkCode = DB::table('giftcode')->where('GiftCode_Code', $randomString)->first();
        if(!$checkCode){
	        return $randomString;
        }else{
	        return $this->codeRandomString($length);
        }
    }
}
