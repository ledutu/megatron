<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Storage;
use Image;
use App\Model\Notification;

use App\Model\Money;
use App\Model\User;
use App\Model\Investment;
use Illuminate\Support\Facades\Auth;
use App\Model\Ticket;
use App\Model\TicketSubject;
use Illuminate\Support\Facades\Crypt;
use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;
use hash;
use DB;
use App\Jobs\SendMail;
class GiftCodeController extends Controller{
	
	public function getPackageGiftCode(){
// 		$currency = DB::table('currency')->where('Currency_Active', 1)->get();
		$arrType = [9 => 'GOLD'];
		$listPackage = DB::table('package_giftcode')->where('Package_Status', 1)->paginate(30);
		return view('System.Admin.PackageGiftCode', compact('listPackage', 'arrType'));
	}	
	public function getGiftCode(){
// 		$currency = DB::table('currency')->where('Currency_Active', 1)->get();
		$arrType = [9 => 'GOLD'];
		$listPackage = DB::table('package_giftcode')->where('Package_Status', 1)->orderBy('Package_ID', 'ASC')->get();
		$listGiftCode = DB::table('giftcode')->leftJoin('package_giftcode', 'GiftCode_Package', 'Package_ID')->orderBy('GiftCode_ID', 'DESC')->paginate(50);
// 		dd($listGiftCode);
		return view('System.Admin.GiftCode', compact('listPackage', 'arrType', 'listGiftCode'));
	}	
	
	public function postAddGiftCode( Request $req){
		$this->validate($req, 
            [
            	'package' => 'required',
            ]
        );
        if(session('user')->User_Level != 1){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "You do not have sufficient rights to perform operations, please contact admin for permission!"]);	
		}
		$user = session('user')->User_ID;
		$package = $req->package;
		$check_package = DB::table('package_giftcode')->where('Package_ID', $package)->first();
		$quantity = $check_package->Package_Quantity;
// 		dd($user,$check_package,$quantity);
		$inserStatus = false;
		for ($i = 0; $i < $quantity; $i++) {
			$code = $this->codeRandomString();
			
			$time = time();
			$time_end = $time + strtotime("+12 week");
// 			dd($code,$time,$time_end);
			$insert = [
	            'GiftCode_Code' => $code,
	            'GiftCode_Amount' => $check_package->Package_Amount,
	            'GiftCode_Package' => $package,
	            'GiftCode_Time' => $time,
	            'GiftCode_Expiration_Time' => $time_end,
	            'GiftCode_User' => $user,
	            'GiftCode_Status' => 0,
	        ];
	        $inserStatus = DB::table('giftcode')->updateOrInsert($insert);
		}
// 		dd(1313);
        
        if ($inserStatus) {
            return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Created gift code success!"]);
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Created package gift code error!"]);
// 		dd($user,$amount,$datetime);
	}
	
	public function postAddPackageGiftCode( Request $req){
		$this->validate($req, 
            [
            	'name_giftcode' => 'required',
            	'amount' => 'required',
            	'quantity' => 'required',
            	'priceEUSD' => 'required',
            	'priceGOLD' => 'required',
            	'type' => 'required',
            ]
        );
        if(session('user')->User_Level != 1){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "You do not have sufficient rights to perform operations, please contact admin for permission!"]);	
		}
		$user = session('user')->User_ID;
		$nameGiftcode = $req->name_giftcode;
		$priceEUSD = $req->priceEUSD;
		$priceGOLD = $req->priceGOLD;
		$amount = $req->amount;
		$quantity = $req->quantity;
		$type = $req->type;
// 		dd($user,$nameGiftcode,$amount,$type);
		$insert = [
            'Package_User' => $user,
            'Package_Name' => $nameGiftcode,
            'Package_Amount' => $amount,
            'Package_Quantity' => $quantity,
            'Package_PriceEUSD' => $priceEUSD,
            'Package_PriceGOLD' => $priceGOLD,
            'Package_Type' => $type,
            'Package_Status' => 1,
        ];
        $inserStatus = DB::table('package_giftcode')->updateOrInsert($insert);
        if ($inserStatus) {
            return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Add package gift code success!"]);
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Add package gift code error!"]);
// 		dd($user,$amount,$datetime);
	}
	public function getEditPackageGiftCode( Request $req, $id){
		
        if(session('user')->User_Level != 1){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "You do not have sufficient rights to perform operations, please contact admin for permission!"]);	
		}
		$check = DB::table('package_giftcode')->where('Package_ID', $id)->first();
		if(!$check){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "ID package gift code not exit!"]);
		}
		return view('System.Admin.EditPackageGiftCode', compact('check'));
	}
	
	public function postEditPackageGiftCode( Request $req){
		
        if(session('user')->User_Level != 1){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "You do not have sufficient rights to perform operations, please contact admin for permission!"]);	
		}
		
		$user = session('user')->User_ID;
        $id = $req->id_package;
        $name = $req->name_package;
        $amount = $req->amount_package;
        $priceEUSD = $req->priceEUSD;
        $priceGOLD = $req->priceGOLD;
		$type = $req->type_package;
// 		dd($amount,$datetime);
		$check = DB::table('package_giftcode')->where('Package_ID', $id)->first();
		if(!$check){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "ID package gift code not exit!"]);
		}
		$timenow = time();
		$update = DB::table('package_giftcode')->where('Package_ID', $id)->update([
            'Package_User' => $user,
            'Package_Name' => $name,
            'Package_Amount' => $amount,
            'Package_PriceEUSD' => $priceEUSD,
            'Package_PriceGOLD' => $priceGOLD,
            'Package_Type' => $type,
            'Package_Status' => 1,
            'Updated_at' => date('Y-m-d H:i:s', $timenow),
		]);
		if($update){
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Edit package gift code success!']);
		}
		return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Edit package gift code error!']);
	}
	public function getDelPackageGiftCode( Request $req, $id){
		
        if(session('user')->User_Level != 1){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "You do not have sufficient rights to perform operations, please contact admin for permission!"]);	
		}
		
		$check = DB::table('package_giftcode')->where('Package_ID', $id)->first();
		if(!$check){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "ID package gift code not exit!"]);
		}
		$update = DB::table('package_giftcode')->where('Package_ID', $id)->update([
            'Package_Status' => -1,
		]);
		if($update){
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete package gift code success!']);
		}
		return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete package gift code error!']);
	}
	public function getDelGiftCode( Request $req, $id){
		
        if(session('user')->User_Level != 1){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "You do not have sufficient rights to perform operations, please contact admin for permission!"]);	
		}
		
		$check = DB::table('giftcode')->where('GiftCode_ID', $id)->first();
		if(!$check){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "ID gift code not exit!"]);
		}
		$update = DB::table('giftcode')->where('GiftCode_ID', $id)->update([
            'GiftCode_Status' => -1,
		]);
		if($update){
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete gift code success!']);
		}
		return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete gift code error!']);
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
