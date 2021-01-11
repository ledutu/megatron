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
class PostNotificationController extends Controller{
	
	public function getNotiPost(){
		$notiPost = DB::table('post_notifications')->whereNotIn('status', [-1])->orderBy('order','asc')->get();
		// dd($notiPost);
		return view('System.Admin.PostsNotification', compact('notiPost'));
    }	
    public function upNotiPost(Request $req){
    }
	public function postNotiPost(Request $req){
        $user = session('user');
// 		dd($user);
		$this->validate($req, 
            [
                // 'noti_title_vn' => 'required|max:255',
                'noti_title_en' => 'required|min:5|max:150',
                // 'noti_title_cn' => 'required|max:255',
                // 'noti_title_kr' => 'required|max:255',
                // 'noti_title_ru' => 'required|max:255',
                // 'noti_title_es' => 'required|max:255',
                // 'noti_content_vn' => 'required|max:1000',
                'order' => 'required',
                'noti_content_en' => 'required|min:20|max:350',
                // 'noti_content_cn' => 'required|max:1000',
                // 'noti_content_kr' => 'required|max:1000',
                // 'noti_content_ru' => 'required|max:1000',
                // 'noti_content_es' => 'required|max:1000',
            ]
        );
        $titlevn = $req->noti_title_vn;
        $titleen = $req->noti_title_en;
        $titlecn = $req->noti_title_cn;
        $titlekr = $req->noti_title_kr;
        $titleru = $req->noti_title_ru;
        $titlees = $req->noti_title_es;
        $vn = $req->noti_content_vn;
        $en = $req->noti_content_en;
        $cn = $req->noti_content_cn;
        $kr = $req->noti_content_kr;
        $ru = $req->noti_content_ru;
        $es = $req->noti_content_es;
        if($vn == '' && $en == '' && $cn == ''&& $kr == ''&& $ru == ''&& $es == ''){
	        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "please fiel full input"]);
        }
        
     
        if(isset($req->order)){
            $order =  $req->order;
        }else{
            $order =  rand(1, 100);
        }
        
        $update_order = DB::table('post_notifications')->where('order', $order)->first();
        
        if($update_order){
            $add_order = DB::table('post_notifications')->where('order', $order)->update(['order' => ($update_order->order + 1)]);
        }
        $insert = [
            'vi_title' => $titlevn,
            'en_title' => $titleen,
            'cn_title' => $titlecn,
            'kr_title' => $titlekr,
            'ru_title' => $titleru,
            'es_title' => $titlees,
            'vi_content' => $vn,
            'en_content' => $en,
            'cn_content' => $cn,
            'kr_content' => $kr,
            'ru_content' => $ru,
            'es_content' => $es,
            'order' => $order,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $inserStatus = DB::table('post_notifications')->updateOrInsert($insert);
        if ($inserStatus) {
            return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Update notification success!"]);
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Update notification error!"]);

	}
	
	public function getHideNotiPosts(Request $req, $id){
        $check_noti_image = DB::table('post_notifications')->where('id', $id)->first();
		if($check_noti_image->status == 1){
			$updateNoti_image = DB::table('post_notifications')->where('id', $id)->update([
				'status'=> 0
			]);
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Turn off notification Success!']);
		}else{
			
			$updateNoti_image = DB::table('post_notifications')->where('id', $id)->update([
				'status'=> 1
			]);
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Hanging notification Success!']);
		}
       
	}
	public function getDeleteNotiPosts(Request $req, $id){
		$noti_image = DB::table('post_notifications');
        $check_noti_image = DB::table('post_notifications')->where('id', $id)->first();
      


      
		if($check_noti_image){
			$updateDeleNoti_image = DB::table('post_notifications')->where('id', $id)->update([
				'status'=> -1
			]);			
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete notification Success!']);
		}
		return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Delete notification Error!']);
       
	}
}
