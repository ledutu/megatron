<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

use App\Model\Money;
use App\Model\User;
use App\Model\Package;
use App\Model\Log;

use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;
use GuzzleHttp\Client as GuzzleClient;
use Hash;
use DB;

use App\Jobs\SendTelegramJobs;
class PromotionController extends Controller{
  
  	public $feeInsur = [30=>0.08, 7=>0.02];
  	public $days = 30;
  
  	public function getInsurrance(Request $req){
      	$user = Session('user');
      	$balance = User::getBalance($user->User_ID, 5);
		$getHistory = DB::table('promotion_sub')->where('user_id', $user->User_ID)->orderByDesc('id')->get();
      	$totalInsur = $getHistory->where('status', 0)->sum('amount');
      	$feeInsur = $this->feeInsur;
      	//dd($feeInsur);
      	$days = $this->days;
        return view('system.promotion.index', compact('getHistory', 'balance', 'totalInsur', 'feeInsur'));
    }
  
  	public function postIncreaAmount(Request $req){
		$user = User::find(Session('user')->User_ID);
        $this->validate($req, [
            'id' => 'required|exists:promotion_sub,id',
            'amount' => 'required|numeric|min:0',
          	'days' => 'required|numeric|in:7,30',
		]);
      	$getProInsur = DB::table('promotion_sub')->where('id', $req->id)->first();
      	if(!$getProInsur){
            return $this->redirectBack('Insurrance Not Found!', [], 'error');
        }
      	if($getProInsur->status != 0){
            return $this->redirectBack('Please submit another request insurrance!', [], 'error');
        }
      	$amountOld = $getProInsur->amount;
      	$amount = $req->amount;
		//amount am
		if(!$req->amount || $req->amount <= 0){
            return $this->redirectBack('Amount USD Invalid!', [], 'error');
        }
      	$minInsurrance = 1000;
      	if($req->amount < $minInsurrance){
            return $this->redirectBack('Min Increa Insurrance $'.$minInsurrance.'!', [], 'error');
        }
      	$fee = $this->feeInsur;
      	$days = $req->days;
      	$feeInsur = $fee[$days];
        //check balance
        $balance = User::getBalance($user->User_ID, 5);
      	$calDay = ((strtotime($getProInsur->expired_time) - time())/86400);
        //Tính toán phí
        $amountFee = $amount*($feeInsur/$days)*$calDay;
        //check m\amount balance
        if($amountFee > $balance){
            return $this->redirectBack('Your balance is not enough!', [], 'error');
		}
        // lưu lịch sử
        $arrayInsert = array(
          'Money_User' => $user->User_ID,
          'Money_USDT' => -($amountFee),
          'Money_USDTFee' => 0,
          'Money_Time' => time(),
          'Money_Comment' => 'Increament Insurrance Time '.$req->time.' $'.$req->amount.' Fee $'.$amountFee,
          'Money_MoneyAction' => 21,
          'Money_MoneyStatus' => 1,
          'Money_Currency' => 5,
          'Money_CurrentAmount' => $amountFee,
          'Money_Rate' => 1,
          'Money_Confirm' => 0,
          'Money_Confirm_Time' => null,
          'Money_FromAPI' => 1,
        );
      	$dataInsr = [
          	'user_id' => $user->User_ID,
          	'amount' => $amountOld+$amount,
          	'time' => $getProInsur->time,
          	'days' => $$getProInsur->days,
          	'created_time' => date('Y-m-d H:i:s'),
          	'expired_time' => $getProInsur->expired_time,
          	'balance' => $balance,
        ];
      	$insertPromo = DB::table('promotion_sub')->where('id', $getProInsur->id)->update(['status'=>-1]);
        $id = Money::insertGetId($arrayInsert);
      	$insertPromo = DB::table('promotion_sub')->insert($dataInsr);
		//trừ tiền thăngf chuyển
		$packageName = DB::table('package')->where('package_ID', $user->user_Agency_Level)->value('package_Name');
        $message = "<b> NOTICE INCREAMENT INSURRANCE</b>\n"
          . "ID: <b>$user->User_ID</b>\n"
          . "NAME: <b>$user->User_Name</b>\n"
          . "EMAIL: <b>$user->User_Email</b>\n"
          . "AMOUNT: <b>$$amount</b>\n"
          . "TIME: <b>$getProInsur->time</b>\n"
          . "CONTENT: <b>Increament Insurrance $$req->amount Fee $$amountFee</b>\n"
          . "<b>Submit Time: </b>\n"
          . date('d-m-Y H:i:s',time());
		//dd($message);
       	dispatch(new SendTelegramJobs($message, -393919269));
      
        return $this->redirectBack("Increament Insurrance $ $req->amount Fee $$amountFee Success!", [], 'success');
		return redirect()->back()->with(['flash_level'=>'success', 'flash_message'=>"Increament Insurrance $ $req->amount Fee $$amountFee Success!"]);
    }
  	
	public function postPromotionInsurrance(Request $req){
		//return redirect()->back()->with(['flash_level'=>'error','flash_message'=>'Please come back later!']);

		$user = User::find(Session('user')->User_ID);
		
        $this->validate($req, [
            'amount' => 'required|numeric|min:0',
            //'time' => 'required|string',
          	'days' => 'required|numeric|in:7,30',
		]);
		
        //ID người nhận
      	$amount = $req->amount;
		//amount am
		if(!$req->amount || $req->amount <= 0){
            return $this->redirectBack('Amount USD Invalid', [], 'error');
        }
        //check balance
        $balance = User::getBalance($user->User_ID, 5);
        //Tính toán phí
      	$fee = $this->feeInsur;
      	$days = $req->days;
      	$feeInsur = $fee[$days];
        $amountFee = $feeInsur*$amount;
        //check m\amount balance
        if($amountFee > $balance){
            return $this->redirectBack('Your balance is not enough!', [], 'error');
		}
      	$minInsurrance = 500;
      	if($req->amount < $minInsurrance){
            return $this->redirectBack('Min Buy Insurrance $'.$minInsurrance.'!', [], 'error');
        }
        // lưu lịch sử
        $arrayInsert = array(
          'Money_User' => $user->User_ID,
          'Money_USDT' => -($amountFee),
          'Money_USDTFee' => 0,
          'Money_Time' => time(),
          'Money_Comment' => 'Buy Promotion Insurrance Time '.$req->time.' $'.$req->amount.' Fee $'.$amountFee,
          'Money_MoneyAction' => 21,
          'Money_MoneyStatus' => 1,
          'Money_Currency' => 5,
          'Money_CurrentAmount' => $amountFee,
          'Money_Rate' => 1,
          'Money_Confirm' => 0,
          'Money_Confirm_Time' => null,
          'Money_FromAPI' => 1,
        );
      	$dataInsr = [
          	'user_id' => $user->User_ID,
          	'amount' => $amount,
          	'time' => $req->time,
          	'days' => $days,
          	'created_time' => date('Y-m-d H:i:s'),
          	'expired_time' => date('Y-m-d H:i:s', strtotime('+'.$days.' days')),
          	'balance' => $balance,
        ];
        $id = Money::insertGetId($arrayInsert);
      	$cancelOld = DB::table('promotion_sub')->where('user_id', $user->User_ID)->where('status', 0)->update(['status'=>-1]);
      	$insertPromo = DB::table('promotion_sub')->insert($dataInsr);
      
        $message = "<b> NOTICE PROMOTION INSURRANCE</b>\n"
          . "ID: <b>$user->User_ID</b>\n"
          . "NAME: <b>$user->User_Name</b>\n"
          . "EMAIL: <b>$user->User_Email</b>\n"
          . "AMOUNT: <b>$$amount</b>\n"
          . "TIME: <b>$req->time</b>\n"
          . "CONTENT: <b>Buy Insurrance $$req->amount Fee $$amountFee</b>\n"
          . "<b>Submit Time: </b>\n"
          . date('d-m-Y H:i:s',time());
		//dd($message);
       	dispatch(new SendTelegramJobs($message, -393919269));
      
        return $this->redirectBack("Buy Insurrance $ $req->amount Fee $$amountFee Success!", [], 'success');
	}
  
}
