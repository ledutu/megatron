<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Money;
use App\Model\StringToken;
use Carbon\Carbon;
use Validator;
class RacingController extends Controller
{
    //
	public $keyHash	 = 'Rac!ng@Eggs'; 

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['only' => ['codeSpam']]);
    // }
    public function postBetRacing(Request $req){
        include(app_path() . '/functions/xxtea.php');
        // return response(base64_encode(xxtea_encrypt(json_encode(array('MatchID'=>'123', 'UserID'=>'969399', 'Amount'=>100, 'Currency'=>3)),$this->keyHash)), 200);
	    $data = json_decode(xxtea_decrypt(base64_decode($req->data),$this->keyHash));
		if(!$req->data || $data == ''){
			return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>false, 'message'=>__('app.miss_data'))),$this->keyHash)), 200);
        }
        $validator = Validator::make((array)$data, [
            'MatchID' => 'required|nullable',
            'UserID' => 'required|exists:users,User_ID',
            'Amount' => 'required|numeric|min:1',
            'Currency' => 'required|in:3,9',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
                return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>false, 'message'=>$value)),$this->keyHash)), 200);
				// return $this->response(200, [], $value, $validator->errors(), false);
			}
        }
        $action = 50;
        $matchID = $data->MatchID;
        $user = User::find($data->UserID);
        $currency = $data->Currency;
        $amount = $data->Amount;
        $balance = User::getBalance($user->User_ID, $currency);
        if($balance < $amount){
            return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>false, 'message'=>__('app.your_balance_is_not_enough'))),$this->keyHash)), 200);
        }
        $arrayInsert[] = array(
            'Money_User' => (int)$user->User_ID,
            'Money_USDT' => -$amount,
            'Money_USDTFee' => 0,
            'Money_Time' => time(),
            'Money_Comment' => "Bet Snail Racing ID: ".$matchID,
            'Money_MoneyAction' => $action,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => (int)$currency,
            'Money_CurrentAmount' => $amount,
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_TXID' => $matchID,
            'Money_FromAPI' => 1,
        );
        Money::insert($arrayInsert);
        $balance = User::getBalance($user->User_ID, $currency);
        return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>true, 'data'=>['Balance'=>$balance], 'message'=>__('app.bet_successful'))),$this->keyHash)), 200);
    }
    
    public function postBetListRacing(Request $req){
	    include(app_path() . '/functions/xxtea.php');
	    $data = json_decode(xxtea_decrypt(base64_decode($req->data),$this->keyHash));
		if(!$req->data || $data == ''){
			return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>false, 'message'=>__('app.miss_data'))),$this->keyHash)), 200);
            // return $this->response(200, [], 'Miss Data', [], false);
        }
        $validator = Validator::make((array)$data, [
            'MatchID' => 'required|nullable',
            'BetList' => 'required|nullable',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
                return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>false, 'message'=>$value)),$this->keyHash)), 200);
				// return $this->response(200, [], $value, $validator->errors(), false);
			}
        }
        // if(!isset($data->MatchID) || !$data->MatchID){
        //     return $this->response(200, [], 'Match Error!', [], false);
        // }
        $arrayBet = $data->BetList;
        $arrayResponse = [];
        $action = 50;
        $matchID = $data->MatchID;
        $arrayInsert = array();
        foreach($arrayBet as $value){
            $value = (array)$value;
            if(!isset($value['UserID'])){
                continue;
            }
            if(!isset($value['Currency']) || !isset($value['Amount'])){
                $arrayResponse['Fail'] = [$value['UserID']=>-1];
                continue;
            }
            $user = User::find($value['UserID']);
            if(!$user){
                $arrayResponse['Fail'] = [$value['UserID']=>-2];
                continue;
            }
            $currency = $value['Currency'];
            $amount = $value['Amount'];
            $balance = User::getBalance($user->User_ID, $currency);
            if($balance < $amount){
                $arrayResponse['Fail'] = [$value['UserID']=>$balance];
                continue;
            }
            $arrayInsert[] = array(
                'Money_User' => (int)$user->User_ID,
                'Money_USDT' => -$amount,
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => "Bet Snail Racing ID: ".$matchID,
                'Money_MoneyAction' => $action,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => (int)$currency,
                'Money_CurrentAmount' => $amount,
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_TXID' => $matchID,
                'Money_FromAPI' => 1,
            );
            Money::insert($arrayInsert);
            $balance = User::getBalance($user->User_ID, $currency);
            $arrayResponse['Success'] = [$value['UserID']=>$balance];
        }
        return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>true, 'data'=>$arrayResponse, 'message'=>__('app.bet_successful'))),$this->keyHash)), 200);
        // return $this->response(200, [], 'Bet List Success', [$arrayResponse], true);
    }

    public function postResultBet(Request $req){
        include(app_path() . '/functions/xxtea.php');
        // return response(base64_encode(xxtea_encrypt(json_encode(array('MatchID'=>'123', 'BetList'=>[['UserID'=>'969399', 'Amount'=>200, 'Currency'=>3]])),$this->keyHash)), 200);
	    $data = json_decode(xxtea_decrypt(base64_decode($req->data),$this->keyHash));
		if(!$req->data || $data == ''){
			return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>false, 'message'=>'Miss Data')),$this->keyHash)), 200);
            // return $this->response(200, [], 'Miss Data', [], false);
        }
        $validator = Validator::make((array)$data, [
            'MatchID' => 'required|nullable',
            'BetList' => 'required|nullable',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
                return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>false, 'message'=>$value)),$this->keyHash)), 200);
				// return $this->response(200, [], $value, $validator->errors(), false);
			}
        }
        // if(!isset($data->MatchID) || !$data->MatchID){
        //     return $this->response(200, [], 'Match Error!', [], false);
        // }
        $arrayBet = $data->BetList;
        // dd($arrayBet);
        $arrayResponse = [];
        $action = 51;
        $matchID = $data->MatchID;
        $getListBetMatchID = Money::where('Money_TXID', $matchID)->where('Money_MoneyStatus', 1)->pluck('Money_User')->toArray();
        $arrayInsert = array();
        foreach($arrayBet as $value){
            $value = (array)$value;
            // dd($value);
            if(!isset($value['UserID'])){
                continue;
            }
            if(!isset($value['Currency']) || !isset($value['Amount'])){
                $arrayResponse['Fail'] = [$value['UserID']=>-1];
                continue;
            }
            $user = User::find($value['UserID']);
            if(!$user){
                $arrayResponse['Fail'] = [$value['UserID']=>-2];
                continue;
            }
            if(array_search($user->User_ID, $getListBetMatchID)){
                $arrayResponse['Fail'] = [$value['UserID']=>-3];
                continue;
            }
            $currency = $value['Currency'];
            $amount = $value['Amount'];
            // $balance = User::getBalance($user->User_ID, $currency);
            // if($balance < $amount){
            //     $arrayResponse['Fail'] = [$value['UserID']=>$balance];
            //     continue;
            // }
            $arrayInsert[] = array(
                'Money_User' => (int)$user->User_ID,
                'Money_USDT' => $amount,
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => "Result Snail Racing ID: ".$matchID,
                'Money_MoneyAction' => $action,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => (int)$currency,
                'Money_CurrentAmount' => $amount,
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_TXID' => $matchID,
                'Money_FromAPI' => 1,
            );
            Money::insert($arrayInsert);
            $balance = User::getBalance($user->User_ID, $currency);
            $arrayResponse['Success'] = [$value['UserID']=>$balance];
        }
        return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>true, 'data'=>$arrayResponse, 'message'=>__('refund_result_successful'))),$this->keyHash)), 200);
        // return $this->response(200, [], 'Refund Bet Success', [$arrayResponse], true);
    }
}
