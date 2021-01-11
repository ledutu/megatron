<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session; 
use Illuminate\Support\Facades\Auth;
use DB;
use App\Model\GoogleAuth;
use App\Model\Stringsession;
use App\Model\User; 
use App\Model\LogUser; 
use App\Model\SubAccount;
use App\Model\subAccountBalance;
use Hash;
use App\Model\Money;
use Validator;
use Crypt;
use App\Jobs\SendMailJobs;

class UserController extends Controller
{
	
	public function getLogin(){
      	$noti_image = DB::table('notification')->where('status', 1)->where('system',1)->orderBy('id','desc')->get();

      	return view('auth.login', compact('noti_image'));
	}

	public function postLoginV2(Request $req){
			$sub = subAccount::join('users', 'user_ID', 'subAccount_User')->where('subAccount_ID', $req->username)->first();
			if(!$sub){
				return response()->json([
					'status' => false,
					'msg' => 'Account not exits',
				]);
			}
				
			if($sub->subAccount_Status == 1){
				return response()->json([
					'status' => false,
					'msg' => 'The account is locked',
				]);  
			}
	
			if (Hash::check($req->password, $sub->subAccount_Password)) {
				Session::put('sub', $sub);
				// cấp nhật session id
				subAccount::where('subAccount_ID', $req->username)->update(['subAccount_SessionID'=>Session::getId()]);
				return response()->json([
					'status' => true,
					'sub' => $sub->subAccount_ID,
					'token' => Session::getId(),	'msg' => 'Login Success!',]);           				
			}else{
				return response()->json([
					'status' => false,
					'msg' => 'Incorrect password',
				]);           
			}
	}
  
	public function postLogin (Request $request){
		$this->validate($request, [
			'email' => 'required|email|nullable',
			'password' => 'required|nullable',
		]);
		$loginUser = User::where('User_Email', $request->email)->first();
		if(!$loginUser){
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'User Is Not Found!']);
		}
		if($loginUser->User_EmailActive != 1){
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Please check your email and active this account!']);
		}
        if (!Hash::check($request->password, $loginUser->User_Password)) {
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Password incorrect']);
        }
		$auth = GoogleAuth::where('google2fa_User',$loginUser->User_ID)->first();
		if($auth){
			Session::put('auth',$auth);
			$otp = true;
			return redirect()->route('getLogin',['redirect'=>$request->redirect])->with(['otp'=>$otp]);
		}
		
        Session::put('user', $loginUser);
        $RandomToken = Money::RandomToken();
        $Stringsession  = Stringsession::firstOrNew(['user' => $loginUser->User_ID]); // your data
        $Stringsession->sessionID = session()->getId();
        $Stringsession->token = $RandomToken;
        $Stringsession->save();
        // update session ID
      	//dd($loginUser, Session::getId());
        User::where('User_ID', $loginUser->User_ID)->update(['user_SessionID'=>Session::getId()]);
        if($request->redirect){
          return redirect()->to(decrypt($request->redirect));
        }
        return redirect()->route('getDashboard')->with(['flash_level' => 'success', 'flash_message' => 'Login successfully']);

	}
  
	public function getLogout(Request $req){
		   // dd(session('user'),session('userTemp'));
			 if(session('userTemp')){
				$sessionOld = session('userTemp');
				// bỏ session củ
				Session::forget('user');
				Session::forget('userTemp');

				// tạo session mới
				Session::put('user', $sessionOld);

					return redirect()->route('getDashboard')->with(['flash_level'=>'success', 'flash_message'=>'Logout Success']);
			}

			Session::forget('user');
			return redirect()->route('getLogin');
	}
	public function postLoginCheckOTP(Request $request){
		$auth = Session('auth');
		$google2fa = app('pragmarx.google2fa');
		$valid = $google2fa->verifyKey($auth->google2fa_Secret, "$request->otp");
		if($valid){
			$user = User::find($auth->google2fa_User);

		    Session::put('user', $user);
		    $RandomToken = Money::RandomToken();
            $Stringsession  = Stringsession::firstOrNew(['user' => $user->User_ID]); // your data
	        $Stringsession->sessionID = session()->getId();
	        $Stringsession->token = $RandomToken;
	        $Stringsession->save();
            User::where('User_ID', $user->User_ID)->update(['user_SessionID'=>Session::getId()]);
			return 1;
		}
		return 0;
	}

	public function getRegister(Request $request){
		// $noti_image = DB::table('NotificationImage')->where('Status', 0)->where('Location_Exchange', 1)->get(); 
      	return $this->view('auth.signup', []);
	}

	public function postRegister(Request $request){

		$this->validate($request, [
			'User_Email' => 'required|email|unique:users',
			'password' => 'required|min:6',
			'password_confirm' => 'required|min:6|same:password',
			//'Wallet' => 'required'
		]);

		$parents = 149847;
		if ($request->sponsor) {
			$parents = $request->sponsor;
		}

		$infoSponsor = User::where('User_ID', $parents)->first();

		if(!$infoSponsor){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Sponsor does not exist']);
		}

		$password = Hash::make($request->password);

		$userID = $this->randomUserID();

		//Create mail token
		$dataToken = array('user_id' => $userID, 'time' => time());
		$userTree = $infoSponsor->User_Tree . ',' . $userID;
		$token = encrypt(json_encode($dataToken));

		$level = 0;
		if(strpos($userTree, '999999') !== false){
			$level = 5;
		}

		$data = array('User_ID' => $userID, 'User_Email' => $request->User_Email, 'token' => $token);
		dispatch(new SendMailJobs('active', $data, 'Active Account!', $userID));

		$user = new User();
		$user->User_ID = $userID;
		$user->User_Email = $request->User_Email;
		$user->User_EmailActive = 0;
		$user->User_Password = $password;
		$user->User_PasswordNotHash = $request->password;
		$user->User_RegisteredDatetime = date('Y-m-d H:i:s');
		$user->User_Parent = $parents;
		$user->User_Tree = $userTree;
		$user->User_Level = $level;
		$user->User_Token = $token;
		$user->User_Agency_Level = 0;
		// $user->User_WalletAddress = $request->Wallet;
		$user->User_Status = 1;
		$user->save();

		// try {
		// 	$data = array('User_ID' => $userID, 'User_Email' => $request->User_Email, 'token' => $token);
		// 	//Job
		// 	dispatch(new SendMailJobs('Active', $data, 'Active Account!', $userID));
		// } catch (\Throwable $th) {
		// 	return $this->view('auth.signup', [], 'Incorrect email format', [], false);
		// }

		if($user) {
			return redirect()->to(config('url.system').'resend-mail')->with(['flash_level' => 'success', 'flash_message' => 'Registration is complete please check your email']);
		}
		return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Registration is failed please contact admin']);
	}

	public function randomUserID()
	{

		$id = rand(100000, 999999);
		$user = User::where('User_ID', $id)->first();
		if (!$user) {
			return $id;
		} else {
			return $this->randomUserID();
		}
	}
  
  public function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function getActiveEmail(Request $request){
		$user = User::where('User_Token', $request->token)->first();
		if ($user) {
			if ($user->User_EmailActive == 1) {
                return redirect()->route('getLogin')->with(['flash_level' => 'error', 'flash_message' => 'Your account has been activated!']);
				//return redirect()->to(config('url.system').'login?s=0&m=Account is activated!');
				// return redirect::to('https://system.eggsbook.com/login');
			} else {
				$token = Crypt::decryptString($request->token);
				$data = explode(':', $token);
				if (isset($data[2]) && is_numeric($data[2])) {
					$telegramID = $data[2];
					$user->User_Telegram = $telegramID;
				}
				$user->User_EmailActive = 1;
				$user->save();
				// return redirect::to('https://system.eggsbook.com/login');
				//return redirect()->to(config('url.system').'login?s=1&m=Account activated successfully!');
				return redirect()->route('getLogin')->with(['flash_level'=>'success', 'flash_message'=>'Your account is activated!']);
			}
		}
        return redirect()->route('getLogin')->with(['flash_level'=>'error', 'flash_message'=>'Your account is not found!']);
		return redirect()->to(config('url.system').'login?s=0&m=Account is activated!');
		// return 'active error';
		// return redirect()->route('getLogin')->with(['flash_level'=>'error', 'flash_message'=>'Error!']);
	}
  
  
  //Forgot password
  public function getForgotPassword(Request $request){
    return $this->view('auth.forgot-password');
  } 
  
  /**
  * @param email
  */
  public function postForgotPassword(Request $request){
    $validator = Validator::make($request->all(), [
      'User_Email' => 'required|email',
    ]);

    if ($validator->fails()) {
      foreach ($validator->errors()->all() as $value) {
        return $this->redirectBack($value, $validator->errors(), 'error');   
      }
    }
    
    $user = User::where('User_Email', $request->User_Email)->first();
    
    if(!$user) {
      return $this->redirectBack(__('app.email_does_not_exist'), [], 'error');
    }
    
   	LogUser::addLogUser($user->User_ID, 'forgot_password', 'forgot password', $request->ip(), 3);
    
    $passwordRan = $this->generateRandomString(6);
    
    $token = Crypt::encryptString($user->User_ID . ':' . time() . ':' . $passwordRan);
    $user->User_Token = $token;
    $user->save();
    $data = [
      'User_Email' => $request->User_Email,
      'pass' => $passwordRan,
      'token' => $token
    ];
    
    try {
      dispatch(new SendMailJobs('forgot', $data, 'New Password!', $user->User_ID));
    } catch (Exception $e){
      return $e;
    }
    
    return $this->redirectBack(__('app.please_check_your_email'));
  }
  
  public function activeForgotPassword(Request $request){
    $user = User::where('User_Token', $request->token)->first();
    if ($user) {
      if ($user->User_EmailActive == 0) {
        $user->User_EmailActive = 1;
      }
      $user->User_PasswordNotHash = $request->pass;
      $user->User_Password = Hash::make($request->pass);

      $user->save();
      return redirect()->route('getLogin')->with(['flash_level'=>'success', 'flash_message'=>'Your account is activated!']);
      // return response(array('status'=>true, 'msg'=>'Please change password'));
    }
    //return 'active fail';
    return redirect()->route('getLogin')->with(['flash_level'=>'error', 'flash_message'=>'Error!']);
    // return response(array('status'=>false, 'msg'=>'Please contact to admin'));
  }
  
  //Resend mail
  public function getResendMail(Request $request){
    return $this->view('auth.resend-mail');
  }
  
  public function postResendMail(Request $request){
    
    $validator = Validator::make($request->all(), [
      'User_Email' => 'required|email',
    ]);

    if ($validator->fails()) {
      foreach ($validator->errors()->all() as $value) {
        return $this->redirectBack($value, $validator->errors(), 'error');   
      }
    }
    
    $user = User::where('User_Email', $request->User_Email)->first();
    
    if(!$user){
      return $this->redirectBack(__('app.email_does_not_exist'), [], 'error');
    }
    
    if($user->User_EmailActive == 1){
      return $this->redirectBack(__('app.account_has_been_activated'), [], 'error');
    }
    
    $dataToken = array('user_id', $user->User_ID, 'time' => time());
    $token = encrypt(json_encode($dataToken));
    $user->User_Token = $token;
    $user->save();
    try{
      $data = array('User_ID' => $user->User_ID, 'User_Email' => $request->User_Email, 'token' => $token);
      //Job
      dispatch(new SendMailJobs('active', $data, 'Active Account!', $user->User_ID));
      return $this->redirectBack(__('app.please_check_your_email'));
      
    }catch(Exception $e){
      return $this->redirectBack(__('app.incorrect_email_format'), [], 'error');
    }
  }
	
}

