<?php

namespace App\Http\Controllers\System;

use App\Http\Requests\PersonalInfo;
use App\Http\Requests\Register;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Session;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use PragmaRX\Google2FA\Google2FA;

use App\Model\GoogleAuth;
use App\Model\User;
use App\Model\Profile;
use App\Model\Log;
use App\Model\Wallet;
use App\Model\Investment;
use App\Jobs\SendMailJobs;
use App\Jobs\SendTelegramJobs;
class UserController extends Controller
{
    public function getProfile(){
        $user = session('user');
        $trustWalletAddress = DB::table('address')
            ->where('Address_User', $user->User_ID)
            ->where('Address_Currency', 1)
            ->value('Address_Address');

        $google2fa = app('pragmarx.google2fa');
        //kiểm tra member có secret chưa?
        $auth = GoogleAuth::where('google2fa_User',$user->User_ID)->first();

        $Enable = false;
        if($auth == null){
            $secret = $google2fa->generateSecretKey();
            Session::put('Auth', $secret);
        }else{
            $Enable = true;
            $secret = $auth->User_Auth;
        }
        //$google2fa->setAllowInsecureCallToGoogleApis(true);

        $inlineUrl = $google2fa->getQRCodeUrl(
            "IGTRADE",
            $user->User_Email,
            $secret
        );
        $user = User::where('User_ID', $user->User_ID)->first();
        $kycProfile = Profile::where('Profile_User', $user->User_ID)->orderByDesc('Profile_ID')->first();
        return view('system.member.Profile', compact('user', 'trustWalletAddress','inlineUrl', 'secret', 'Enable', 'kycProfile'));
    }

    public function postAuth(Request $req){
        $user = Session('user');
        $google2fa = app('pragmarx.google2fa');
        $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
        $authCode = null;
        if(Session('Auth')){
            $authCode =  Session('Auth');
        }else{
            $authCode = $AuthUser->google2fa_Secret;
        }
        if(!$req->verifyCode){
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=> __('auth.please_enter_verification_code')]);
        }
        $valid = $google2fa->verifyKey($authCode, $req->verifyCode);

        if($valid){
            //kiểm tra member có secret chưa?
            $auth = GoogleAuth::where('google2fa_User',$user->User_ID)->first();

            if($auth){
                // xoá
                GoogleAuth::where('google2fa_User',$user->User_ID)->delete();
                return redirect()->route('getProfile')->with(['flash_level'=>'success', 'flash_message'=>__('auth.disable_authenticator')]);
            }else{
                // Insert bảng google2fa
                $r = new GoogleAuth();
                $r->google2fa_User = $user->User_ID;
                $r->google2fa_Secret = Session('Auth');
                $r->save();
                return redirect()->route('getProfile')->with(['flash_level'=>'success', 'flash_message'=>__('auth.enable_aauthenticator')]);
            }

        }else{
            return redirect()->route('getProfile')->with(['flash_level'=>'error', 'flash_message'=>__('auth.wrong_code')]);
        }
    }
    
    public function getOTP(Request $req){
		$user = User::find(Session('user')->User_ID);
		if(!$user){
			return response()->json(['status'=>false]);			
		}
		$otp = rand(100000, 999999);
		$user->User_OTP = $otp;
		$user->save();
        //dữ liệu gửi sang mailtemplate
        $data = array('User_ID' => $user->User_ID, 'User_Email'=> $user->User_Email, 'otp' => $otp, 'token'=>123123);
        //Job
        dispatch(new SendMailJobs('GetOTP', $data, 'Request Get OTP!', $user->User_ID));
		return response()->json(['status'=>true]);
    }

    public function postProfile(Request $req)
    {
        $google2fa = app('pragmarx.google2fa');
	    $this->validate($req, [
		    'address' => 'required',
		    'otp' => 'required'
	    ]);

	    $user = User::find(Session('user')->User_ID);
/*
        $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
        if(!$AuthUser){
		    return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'User Unable Authenticator']);
        }
        $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $req->otp);

        if(!$valid){
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Wrong code']);
        }
*/
        if($req->otp != $user->User_OTP){
	        return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>__('auth.otp_is_wrong')]);
        }
 	    $address = $req->address;
		// hàm chuyển ví thành hexAddress
/*
	    $hexAddress = Wallet::base58check2HexString($address);
  		$client = new Client();
	    $response = $client->request('POST', 'http://174.138.27.227:8190/wallet/validateaddress', [
		    'json'    => ['address' => $hexAddress],
	    ])->getBody()->getContents();
	    $data = json_decode($response);
	    if(!$data->result || $data->result === false){
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Wallet Address DAFCO Is Wrong Format!']);
	    }
*/
	    $user->User_OTP = '';
	    $user->User_WalletAddress = $req->address;
	    // $user->User_CoinCommission = $req->coin;
	    $user->save();
		Session::put('user', $user);
        return redirect()->back()->with(['flash_level'=>'success', 'flash_message'=>__('auth.change_information_success')]);
    }

    

    public function PostKYC(Request $request)
    {
        

        $request->validate([
            'passport' => 'required|unique:profile,Profile_Passport_ID',
            'passport_image' => 'required|image|mimes:jpeg,jpg,bmp,png,gif',
            'passport_image_selfie' =>'required|image|mimes:jpeg,jpg,bmp,png,gif'
        ]);
        $user = session('user');
        $checkExist = Profile::where('Profile_User', $user->User_ID)->whereIn('Profile_Status', [0,1] )->first();
        if ($checkExist) {
            return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => __('auth.submit_successfully')]);
        }
        $check_passport = DB::table('profile')->where('Profile_Passport_ID', $request->passport)->first();
        if ($check_passport) {
            return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => __('auth.password_is_exist')]);
        }

        $passportID = $request->passport;
        //get file extension
        $passportImageExtension = $request->file('passport_image')->getClientOriginalExtension();
        $passportImageSelfieExtension = $request->file('passport_image_selfie')->getClientOriginalExtension();

            
         //set folder and file name
         $randomNumber = uniqid();
         $passportImageStore = "users/".$user->User_ID."/profile/passport_image_".$user->User_ID."_".$randomNumber.".".$passportImageExtension;
         $passportImageSelfieStore = "users/".$user->User_ID."/profile/passport_image_selfie_".$user->User_ID."_".$randomNumber.".".$passportImageSelfieExtension;
        
         //send to Image server
        $passportImageStatus =Storage::disk('ftp')->put($passportImageStore, fopen($request->file('passport_image'), 'r+'));
        $passportImageSelfieStatus =Storage::disk('ftp')->put($passportImageSelfieStore, fopen($request->file('passport_image_selfie'), 'r+'));
        
        if ($passportImageStatus and $passportImageSelfieStatus) {
            $insertProfileData = [
                'Profile_User' => $user->User_ID,
                'Profile_Passport_ID' => $passportID,
                'Profile_Passport_Image' => $passportImageStore,
                'Profile_Passport_Image_Selfie' => $passportImageSelfieStore,
                'Profile_Time' => date('Y-m-d H:i:s')
            ];
        
            $inserStatus = Profile::create($insertProfileData);
            if ($inserStatus) {
				//Gửi telegram thông báo lệh hoa hồng
				// $message = $user->User_ID. " Post KYC\n"
				// 				. "<b>User ID: </b>\n"
				// 				. "$user->User_ID\n"
				// 				. "<b>Email: </b>\n"
				// 				. "$user->User_Email\n"
				// 				. "<b>POST KYC Time: </b>\n"
				// 				. date('d-m-Y H:i:s',time());

				// dispatch(new SendTelegramJobs($message, -364563312));
                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => __('auth.update_profile_note')]);
            }
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => __('auth.please_contact_admin')]);

        }

        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => __('auth.update_profile_error')]);

    }

    public function postNamePhone(Request $request){
        $google2fa = app('pragmarx.google2fa');
        $request->validate([
            'full_name' => 'required|min:6|regex:/[a-zA-Z\s]/',
            'phone_number' => 'required|numeric',
            'otp' => 'required'
        ]);
    
        $user = session('user');
       
        $checkExist = Profile::where('Profile_User', $user->User_ID)->where('Profile_Status',1)->first();
        //get user
      
        $user_update = User::where('User_ID',$user->User_ID)->first();
          //check profile
      
        if($checkExist && $user_update->User_Phone && $user_update->User_FullNameUser){
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>__('auth.you_cannot_change_your_profile_after_KYC_completed')]);
        }
     
        //check otp
        $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
      
        if(!$AuthUser){
		    return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>__('auth.user_disable_authenticator')]);
        }
   
        $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $request->otp);
        
        if(!$valid){
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>__('auth.wrong_code')]);
        }
      
       
        //update user
        // dd($user_update);
        if($user_update){
          $user_update->User_Phone = $request->phone_number;
          $user_update->User_FullName = $request->full_name;

          $user_update->save();
          return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => __('auth.update_profile_success')]);
        }
        
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => __('auth.update_profile_error')]);
    }
    public function getAjaxSaleUser(Request $req){
        
		$userID = $req->User_ID;
        $result = User::where('User_ID', $userID)->select('User_ID', 'User_Email', 'User_Tree')->firstorfail();
        
		$branch  = User::select('User_Email','User_ID', 'User_Tree')->whereRaw("( User_Tree LIKE CONCAT($userID,',',User_ID) OR User_Tree LIKE CONCAT('%,' , $userID, ',' ,User_ID) )")->GET();
    
        $total_sale =User::join('investment', 'investment_User', 'User_ID')->where('User_Tree', 'LIKE', '%'.$userID.'%')->where('investment_Status',1)->where('User_ID','!=',$userID)->sum(DB::raw('investment_Amount * investment_Rate'));
        $total_invest = User::join('investment', 'investment_User','User_ID')->where('User_ID',$userID)->sum(DB::raw('investment_Amount'));

		if($result){
			return response()->json([
				'status' => 200,
				'infor' => $result,
                'sales' => $total_sale,
                'invest'=> $total_invest
			], 200);
        }
    }

    public function getList()
    {

	    $user = User::where('User_ID', Session::get('user')->User_ID)->first();
	    $user_list = User::select('Profile_Status','User_ID', 'User_Email','User_Phone','User_FullName', 'User_RegisteredDatetime', 'User_Parent', DB::raw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " AS f, User_Agency_Level, User_Tree"))
                        ->leftJoin('profile', 'Profile_User', 'User_ID')
                        ->whereRaw('User_Tree LIKE "'.$user->User_Tree.'%"')
						->where('User_ID','<>',$user->User_ID)
						->orderBy('User_RegisteredDatetime','DESC')
                        ->get();
                        
        foreach($user_list as $v){
            $v->aaa = DB::table('investment')
                    ->where('investment_User', $v->User_ID)
                    ->where('investment_Status', 1)
                    ->sum(DB::raw('investment_Amount * investment_Rate'));
                    
            $v->total_invest_branch = User::join('investment', 'investment_User', 'User_ID')->where('User_Tree', 'LIKE', $v->User_Tree.'%')->sum(DB::raw('investment_Amount * investment_Rate'));
        }
        $total_invest_root = User::join('investment', 'investment_User', 'User_ID')->where('User_Tree', 'LIKE', $user->User_Tree.'%')->sum(DB::raw('investment_Amount * investment_Rate'));
	    return view('System.Members.Members-List', compact('user_list', 'total_invest_root'));
    }

    public function getTree(Request $request)
    {
        if (!$request->userID) {
            $userID = session('user')->User_ID;
        }
        // $usersTreeList = [];
        // $user = User::Where('User_Tree', 'like', "$userID")
        //     ->orWhere('User_Tree', 'like', "%$userID")
        //     ->select('User_ID as id', 'User_Parent as pid','User_Email as email' )
        //     ->first()->toArray();

        // $user['level'] = 'Parent';
        // $user['img'] = "system/images/user.png";
        // array_push($usersTreeList, $user);
        // $this->getUsersTreeList($userID, $usersTreeList);

        $user = Session('user');
        // dd($user);
        $list = array(
			'id' => $user->User_ID,
            'name' =>  $user->User_Email,
            'title' => $user->User_ID,
            'children' => $this->buildTree($user->User_ID),
            'className' => 'node-tree '.strtoupper($user->user_Name),
        );
  
        $list = json_encode($list);
     

        return view('System.Members.Members-Tree', compact('list'));
    }
    function buildTree($idparent, $idRootTemp = null, $barnch = null) {

       
        $build = User::select('User_Email', 'User_Name','User_ID', 'User_Tree')
//                     ->whereRaw('User_Parent LIKE "%'.$idparent.'%"')
                    ->where('User_Parent',$idparent)->GET();
        $child = array();
        if(count($build) > 0){
	        for($i=0;$i<count($build);$i++){
                if(isset($build[$i])){
	                $child[] = array(
                        'id' => $build[$i]->User_ID,
                        'name' => $build[$i]->User_Email,
                        'title' => $build[$i]->User_ID,
                        'className' => 'node-tree '.strtoupper($build[$i]->User_Name),
                        'children' => $this->buildTree($build[$i]->User_ID),
                    );
                }
            }
        }
        return $child;
    }
    
    // public function getTree(Request $req){

    //     // if($req->userEmail){
    //     //     $user = User::where('User_Email', $req->userEmail)->first();
    //     // }else {
    //     //     $user = User::where('User_Email', Session('user')->User_Email)->first();
    //     // }
    //     // if($req->userID){
    //     //     $user = User::where('User_ID', $req->userID)->first();
    //     // }
    //     // else {
    //     //     $user = User::where('User_ID', Session('user')->User_ID)->first();
    //     // }
    //     $user = User::where('User_ID', Session('user')->User_ID)->first();
    //     // Truy vấn để lấy danh sách tất cả children của user đang đăng nhập
    //     $user_list = User::select('User_ID', 'User_Tree', 'User_Email', 'User_Parent', DB::raw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " AS f"))
    //                 ->whereRaw('User_Tree LIKE "'.$user->User_Tree.'%"')
    //                 ->where('User_ID','<>',$user->User_ID)
    //                 ->where('User_Tree','<>',$user->User_Tree)
    //                 ->orderBy('User_Tree','ASC')
    //                 ->get();
    //     foreach($user_list as $u){
    //         $salesSelf = 0;
    //         $totalSalesTree = 0;
    //         $self = Investment::where('investment_User', $u->User_ID)
    //                             ->where('investment_Status', 1)
    //                             ->selectRaw('SUM(`investment_Amount`*`investment_Rate`) as Sale')
    //                             ->groupBy('investment_User')
    //                             ->first();
                                
    //         $totalTree = Investment::join('users', 'investment_User', 'User_ID')
    //                             ->where('investment_User', '<>', $u->User_ID)
    //                             ->whereRaw('User_Tree LIKE "'.$u->User_Tree.'%"')
    //                             ->where('investment_Status', 1)
    //                             ->selectRaw('SUM(`investment_Amount`*`investment_Rate`) as Sale, investment_User')
    //                             ->groupBy('investment_User')
    //                             ->get();
                                
    //         $u->self = 0;
    //         if($self){
    //             $salesSelf = number_format((float)$self->Sale,2);
    //         }
    //         $u->totalTree = 0;
    //         if(count($totalTree)){
    //             foreach($totalTree as $t){
    //                 $totalSalesTree += round($t->Sale,2);
    //             }
    //         }
            
    //         $u->self = $salesSelf;
    //         $u->totalTree = $totalSalesTree;
    //     }
	// 	$user_list = $user_list->toArray();
    //     $Children = array();
    //     $Children = $this->buildTree($user_list,$user->User_ID);
    //     $Children = json_encode($Children);
    //     return view('System.Members.Members-Tree',compact('Children'));
    // }

    // function buildTree(array $elements, $parentId) {
    //     $branch = array();

    //     foreach ($elements as $element) {
    //         // $element['text'] = $element['User_ID'] . " ( " . $element['User_Email'] . " ) ";

	// 		$element['text'] = "F".$element['f'].": ".$element['User_ID'] . " ( " . $element['User_Email'] . " ) [$".$element['self']." / $".$element['totalTree']."]";
	// 		if ($element['User_Parent'] == $parentId){
	// 			$nodes = $this->buildTree($elements, $element['User_ID']);
	// 			if ($nodes) {
	// 				$element['nodes'] = $nodes;
	// 			}

	// 			$branch[] = $element;
	// 		}
    //     }
    //     return $branch;
    // }
    
}
