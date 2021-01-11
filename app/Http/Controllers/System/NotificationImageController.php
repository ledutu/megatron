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
class NotificationImageController extends Controller{
	
	public function getNoti(){
		$notiImage = DB::table('notification')->where('status', '!=', -1)->orderBy('id','desc')->get();
// 		dd($notiImage);
		return view('system.admin.NotificationImage', compact('notiImage'));
	}	
	public function postNoti(Request $req){
        $user = session('user');
// 		dd($user);
		$this->validate($req, 
            [
            	'notification_image' => 'required|image|mimes:jpeg,jpg,png|max:6144',
            ]
        );
        $landing = $req->landing;
        $system = $req->system;
        $promotion = $req->promotion;
        if($landing == '' && $system == '' && $promotion == ''){
	        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Please selected loaction"]);
        }
        $notificationImageExtension = $req->file('notification_image')->getClientOriginalExtension();
        // set folder and file name
        $randomNumber = uniqid();
        $notificationImageStore = "notification/notification_image_" . $user->User_ID . "_" . $randomNumber . "." . $notificationImageExtension;
        //send to Image server
        // return $passportImageSelfieStore;
        $notificationImageStatus = Storage::disk('ftp')->put($notificationImageStore, fopen($req->file('notification_image'), 'r+'));
        
        if ($notificationImageStatus) {
            $insert = [
                'image' => $notificationImageStore,
                'landing' => $landing,
                'system' => $system,
                'promotion' => $promotion,
            ];
            $inserStatus = DB::table('notification')->updateOrInsert($insert);
            if ($inserStatus) {
                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Update notification success!"]);
            }
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Update notification error!"]);

        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Update notification error!"]);

	}
	
	public function getHideNoti(Request $req, $id){
        $check_noti_image = DB::table('notification')->where('id', $id)->first();
		if($check_noti_image->status == 1){
			$updateNoti_image = DB::table('notification')->where('id', $id)->update([
				'status'=> 0
			]);
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Turn off notification Success!']);
		}else{
			
			$updateNoti_image = DB::table('notification')->where('id', $id)->update([
				'status'=> 1
			]);
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Hanging notification Success!']);
		}
       
	}
	public function getDeleteNoti(Request $req, $id){
		$noti_image = DB::table('notification');
        $check_noti_image = DB::table('notification')->where('id', $id)->first();
      
    	      

         $deleteImage_Server = Storage::disk('ftp')->delete([$check_noti_image->image]);
      
		if($check_noti_image){
			$updateDeleNoti_image = DB::table('notification')->where('id', $id)->delete();			
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete notification Success!']);
		}
		return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Delete notification Error!']);
       
	}
}
