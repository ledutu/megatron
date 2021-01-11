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
class FileDocumentController extends Controller{
	
	public function getIndex(){
		$list = DB::table('document')->where('Doc_Status', '!=', -1)->orderBy('Doc_ID','desc')->get();
		$listParent = DB::table('document')->where('Doc_Status', '!=', -1)->where('Doc_ParentID', null)->orderBy('Doc_ID','desc')->get();
		$listParentList = DB::table('document')->where('Doc_Status', '!=', -1)->where('Doc_ParentID', null)->orderBy('Doc_ID','desc')->pluck('Doc_Title', 'Doc_ID')->toArray();
// 		dd($listParentList);
		return view('System.Admin.UpDocument', compact('list', 'listParent', 'listParentList'));
	}	
	public function postFileDoc(Request $req){
        $user = session('user');
// 		dd($user);
		$this->validate($req, 
            [
            	'file_doc' => 'mimes:pdf',
            	'title' => 'required'
            ]
        );
        $title = $req->title;
        $parent = $req->parent;
        if($req->file_doc == ''){
	        $notificationImageExtension = null;
	        $notificationImageStore = null;
	        
	        $insert = [
                'Doc_File' => $notificationImageStore,
                'Doc_Title' => $title,
                'Doc_ParentID' => $parent,
                'Doc_Status' => 1,
            ];
            $inserStatus = DB::table('document')->updateOrInsert($insert);
            if ($inserStatus) {
                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Update notification success!"]);
            }
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Update notification error!"]);
        }else{
	        $notificationImageExtension = $req->file('file_doc')->getClientOriginalExtension();
//         dd($notificationImageExtension);
	        $randomNumber = uniqid();
	        // set folder and file name
	        $notificationImageStore = "Documents/file_" . $user->User_ID  . "_" . $randomNumber . "." . $notificationImageExtension;
	        //send to Image server
	        // return $passportImageSelfieStore;
	        $notificationImageStatus = Storage::disk('ftp')->put($notificationImageStore, fopen($req->file('file_doc'), 'r+'));
	        
	        if ($notificationImageStatus) {
	            $insert = [
	                'Doc_File' => config('url.media').$notificationImageStore,
	                'Doc_Title' => $title,
	                'Doc_ParentID' => $parent,
	                'Doc_Status' => 1,
	            ];
	            $inserStatus = DB::table('document')->updateOrInsert($insert);
	            if ($inserStatus) {
	                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Update notification success!"]);
	            }
	            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Update notification error!"]);
	
	        }
	        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => "Update notification error!"]);
        }
	}
	
	public function getHideFile(Request $req, $id){
        $check_noti_image = DB::table('document')->where('Doc_ID', $id)->first();
		if($check_noti_image->Doc_Status == 1){
			$updateNoti_image = DB::table('document')->where('Doc_ID', $id)->update([
				'Doc_Status'=> 0
			]);
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Turn off notification Success!']);
		}else{
			
			$updateNoti_image = DB::table('document')->where('Doc_ID', $id)->update([
				'Doc_Status'=> 1
			]);
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Hanging notification Success!']);
		}
       
	}
	public function getDeleteFile(Request $req, $id){
		$noti_image = DB::table('document');
        $check_noti_image = DB::table('document')->where('Doc_ID', $id)->first();
		if($check_noti_image){
			$updateDeleNoti_image = DB::table('document')->where('Doc_ID', $id)->delete();			
			return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Delete notification Success!']);
		}
		return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Delete notification Error!']);
       
	}
}
