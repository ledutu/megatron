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
class EggsHatchingController extends Controller{
	
	public function getEggsHatching(){
		$listEgss = DB::table('eggs_hatched')->where('Status', 1)->paginate(30);
		return view('System.Admin.EggsHatching', compact('listEgss'));
	}	
	
	public function postAddEggsHatching( Request $req){
		$this->validate($req, 
            [
            	'amount_egss' => 'required',
            	'datetime' => 'required|date_format:Y/m/d',
            ]
        );
		$user = session('user')->User_ID;
		$amount = $req->amount_egss;
		$datetime = $req->datetime;
		$insert = [
            'User_ID' => $user,
            'Amount' => $amount,
            'Date_Hatching' => strtotime($datetime),
            'Status' => 1,
        ];
        $inserStatus = DB::table('eggs_hatched')->updateOrInsert($insert);
        if ($inserStatus) {
            return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Add eggs hatched success!"]);
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Add eggs hatched error!"]);
// 		dd($user,$amount,$datetime);
	}
	public function getEditEggsHatching( Request $req, $id){
		
		$check = DB::table('eggs_hatched')->where('ID', $id)->first();
		if(!$check){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "ID eggs hatching not exit!"]);
		}
		$timenow = time();
// 		dd($timenow);
		if($check->Date_Hatching < $timenow){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Time out for repairs!"]);
		}
		return view('System.Admin.EditEggsHatching', compact('check'));
	}
	
	public function postEditEggsHatching( Request $req){
/*
		$this->validate($req, 
            [
            	'amount_egss' => 'required',
            	'datetime' => 'required|date_format:Y/m/d',
            ]
        );
*/
        $id = $req->id_eggs;
        $amount = $req->amount_egss;
		$datetime = $req->datetime;
		$user = session('user')->User_ID;
// 		dd($amount,$datetime);
		$check = DB::table('eggs_hatched')->where('ID', $id)->first();
		if(!$check){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "ID eggs hatching not exit!"]);
		}
		$timenow = time();
		if($check->Date_Hatching < $timenow){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Time out for repairs!"]);
		}
		$update = DB::table('eggs_hatched')->where('ID', $id)->update([
            'Amount' => $amount,
            'User_ID' => $user,
            'Date_Hatching' => strtotime($datetime),
            'Updated_at' => date('Y-m-d H:i:s', $timenow),
		]);
		if($update){
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Updata success!']);
		}
		return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Updata error!']);
	}
	public function getDeleteEggsHatching( Request $req, $id){
		$check = DB::table('eggs_hatched')->where('ID', $id)->first();
		if(!$check){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "ID eggs hatching not exit!"]);
		}
		$update = DB::table('eggs_hatched')->where('ID', $id)->update([
            'Status' => -1,
		]);
		if($update){
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete success!']);
		}
		return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete error!']);
	}
}
