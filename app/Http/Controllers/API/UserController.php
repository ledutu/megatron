<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Crypt;

use App\Model\User;
use App\Model\Profile;
use App\Model\GoogleAuth;
use App\Model\Investment;
use App\Model\userBalance;

use App\Jobs\SendMailJobs;
use App\Mails\UserForgotPassword;
use App\Mails\UserSignUp;
use App\Model\LogUser;
use App\Model\Agency;
use App\Model\Money;

use App\Model\MUser;
use App\Model\Eggs;
use App\Model\Foods;
use App\Model\Pools;
use App\Model\Item;
use App\Model\ItemTypes;

use App\Model\ListMission;

use Carbon\Carbon;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\WalletController;

class UserController extends Controller
{
	public $key;
	public $urlApi;
	public $agid;
	public $lang;
	public function __construct()
	{
		$this->middleware('auth:api', ['except' => [
			'postLogin', 
			'postRegister',
			'resentMail', 
			'postForgetPassword', 
			'getCountries', 
			'postAddAgency',
			'loginSocial',
			'forgotAuthy'
		]]);
		$this->middleware('2fa')->only(['postLogin']);;
		$ag = config('ag');
        $this->key = $ag['key'];
		$this->urlApi = $ag['url'];
		$this->agid = $ag['agid'];
		$this->lang = $ag['lang'];
	}

	public function postLogin(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'User_Email' => 'required|email',
			'User_Password' => 'required|min:6',
			'authCode' => 'nullable'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}

		$random = rand(1, 100);
		$now = time() + $random;

		$user = User::where('User_Email', trim($request->User_Email))->first();
		$lang = app()->getLocale();
		
		// $captcha = WalletController::checkCaptcha($request->token);
		// if(!$captcha){
		// 	return $this->response(200, [], 'Captcha isn\'t exist!', [], false);
		// }

		if (!$user) {
			return $this->response(200, ['require_auth' => false], __('app.email_does_not_exist'), [], false);
		}
		if($user->User_Level != 1){
			// return $this->response(200, [], 'We are updating, please come back later!', [], false);
		}
		$credentials = [
			'User_Email' => $request->User_Email,
			'password' => $request->User_Password,
		];
		
		if (!Auth::attempt($credentials)) {
			return $this->response(200, ['require_auth' => false], __('app.login_information_is_not_valid'), [], false);
		}

		if(Auth::user()->User_Block != 0){
			return $this->response(200, [], '', [], false);
		}

		$google2fa = app('pragmarx.google2fa');
		$auth = GoogleAuth::where('google2fa_User', $user->User_ID)->first();

		if ($auth) {
			if (!$request->authCode) {
				return $this->response(200, ['require_auth' => true], __('app.please_enter_your_authentication_code'), ['auth' => false]);
			}
			$valid = $google2fa->verifyKey($auth->google2fa_Secret, $request->authCode);
			if (!$valid) {
				return $this->response(200, [], 'Code wrong', [], false);
			}
		}

		$user = $request->user();
      	// xoa token cu
      	DB::table('oauth_access_tokens')->where('user_id', $user->User_ID)->delete();

		if ($user->User_EmailActive != 1) {
			return $this->response(200, [], __('app.your_email_has_not_been_activated'), [], false);
		}

		if ($user) {
			$user->User_Log = $now;
			$user->save();
		}

		$tokenResult = $user->createToken('EGGSBOOK');
		$token = $tokenResult->token;

		// $client = new \GuzzleHttp\Client();
		// $response = $client->request('POST', 'https://test_info.eggsbook.com/save-token', [
		// 	'headers' => [
		// 		'Content-Type' => 'application/json',
		// 		'Accept' => 'application/json'
		// 	],
		// 	'json' => [
		// 		'token' => $tokenResult->accessToken,
		// 		'id' => $user->User_ID,
		// 		'server' => 'hJ5uNFemBm9CM7gEaR8iOdtbbhdi4J4E2McMrI9Sj1kuaFPPBXFaVHvjTxqE9mWy0qlYP1xFiNn',
		// 	]
		// ]);
		// $res = json_decode($response->getBody(), true);
		// if($res['status']){
			$loginType = config('utils.action.login');
			LogUser::addLogUser($user->User_ID, $loginType['action_type'], $loginType['message'], $request->ip(), 1);
	
			$token->save();
	
			return $this->response(200, [
				'token' => $tokenResult->accessToken,
				'token_type' => 'Bearer',
				'id' => $user->User_ID
				// 'expires_in' => auth()->factory()->getTTL() * 60,
			]);
		// } else {
		// 	return $this->response(200, [], 'Login fail! Please try again.', [], false);
		// }
	}


	public function getLogout(Request $request)
	{
		if (Auth::check()) {

			$accessToken = $request->user()->token();

			$user = $request->user();
			$logoutType = config('utils.action.logout');
			LogUser::addLogUser($user->User_ID, $logoutType['action_type'], $logoutType['message'], $request->ip(), 4);
			DB::table('oauth_refresh_tokens')
				->where('access_token_id', $accessToken->id)
				->update([
					'revoked' => true
				]);

			$accessToken->revoke();

			return $this->response(200, [], __('app.logout_successful'));
		} else {
			return $this->response(200, [], __('app.logout_failed'), [], false);
		}
	}

	public function postRegister(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'User_Email' => 'required|email|unique:users',
			'User_Password' => 'required|min:6',
			'User_Password_Confirm' => 'required|min:6|same:User_Password',
			//'Wallet' => 'required'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}
		// if (!$request->Wallet) {
		// 	return $this->response(200, [], 'Please install metamask extension', [], false);
		// }
		
		// if($request->Wallet){
		// 	$wallet = DB::table('users')->where('User_WalletAddress', $request->Wallet)->first();
		// 	if($wallet){
		// 		return $this->response(200, [], 'This wallet exits', [], false);
		// 	}
		// }


		$parents = 600907;
		if ($request->ponser) {
			$parents = $request->ponser;
		}

		$InfoPonser = User::where('User_ID', $parents)->first();

		if (!$InfoPonser) {
			return $this->response(200, [], __('app.sponsor_does_not_exist'), [], false);
		}

		$password = Hash::make($request->User_Password);
		$UserID = $this->RandonIDUser();

		//Tạo token cho mail
		$dataToken = array('user_id' => $UserID, 'time' => time());
		$userTree = $InfoPonser->User_Tree . ',' . $UserID;
		$token = encrypt(json_encode($dataToken));

		$level = 0;
		if (strpos($userTree, '321321') !== false) {
			$level = 5;
		}

		/**************************** 
		 * đăng ký bên AG *
		***************************/ 


		$key = $this->key;
		$url = $this->urlApi.'user_register';
        $params = [];
		$params['agid']	  		 	= $this->agid; 
		$params['username'] 	 	= $UserID;
		$params['password'] 	 	= $UserID.'abc'; 
		$params['lang']	         	= $this->lang;
	

        $params			 		 = app('App\Http\Controllers\API\AgGameController')->Signature_Genarate($params,$key);
        $paramsUrl = '';
		
		if ($params)
			foreach ($params as $key => $value)
				$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
        $url = $url . '?' . $paramsUrl;
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $response = $res->getBody(); 
		$json = json_decode($response);

		

		/***************************** 
		 * kết thúc đăng ký bên AG *
		*****************************/ 


		$user = new User();
		$user->User_ID = $UserID;
		$user->User_Email = $request->User_Email;
		$user->User_EmailActive = 0;
		$user->User_Password = $password;
		$user->User_PasswordNotHash = $request->User_Password;
		$user->User_RegisteredDatetime = date('Y-m-d H:i:s');
		$user->User_Parent = $parents;
		$user->User_Tree = $userTree;
		$user->User_Level = $level;
		$user->User_Token = $token;
		$user->User_Agency_Level = 0;
		// $user->User_WalletAddress = $request->Wallet;
		$user->User_Status = 1;
		$user->save();

		//create a new pool
		$pool = new Pools();
		$pool->Skin = 0;
		$pool->Type = "1";
		$pool->CreateAt = Carbon::now()->toDateTimeString();
		$pool->Owner = $UserID."";
		$pool->ID = Pools::RandomPoolID();
		
		$pool->save();

		//create a food
		$food = new Foods();
		$food->Amount = 0;
		$food->Type = "1";
		$food->CreateAt = Carbon::now()->toDateTimeString();
		$food->Owner = $UserID."";
		$food->save();

		// gửi mail cho member mới 
		try {
			// gửi mail thông báo
			$data = array('User_ID' => $UserID, 'User_Email' => $request->User_Email, 'token' => $token);
			//Job
			dispatch(new SendMailJobs('Active', $data, 'Active Account!', $UserID));
			// Mail::to($request->User_Email)->send(new UserSignUp($user));
		} catch (Exception $e) {
			return $this->response(200, [], __('app.incorrect_email_format'), [], false);
		}

		// return $user;
		if ($user) {
			return $this->response(200, [], __('app.registration_is_complete_please_check_your_email'));
		}
		return $this->response(200, [], __('app.registration_is_failed_please_contact_admin'), [], false);
	}

	public function resentMail(Request $req){
		$user = User::where('User_Email', $req->email)->first();
		if(!$user){
			return $this->response(200, [], __('app.email_does_not_exist'), [], false);
		}
		if($user->User_EmailActive == 1){
			return $this->response(200, [], __('app.account_has_been_activated'), [], false);
		}
		//Tạo token cho mail
		$dataToken = array('user_id' => $req->user_id, 'time' => time());
		$token = encrypt(json_encode($dataToken));
		$user->User_Token = $token;
		$user->save();
		try {
			// gửi mail thông báo
			$data = array('User_ID' => $user->User_ID, 'User_Email' => $req->email, 'token' => $token);
			//Job
			dispatch(new SendMailJobs('Active', $data, 'Active Account!', $user->User_ID));
			return $this->response(200, [], __('app.please_check_your_email'));
			// Mail::to($request->User_Email)->send(new UserSignUp($user));
		} catch (Exception $e) {
			return $this->response(200, [], __('app.incorrect_email_format'), [], false);
		}
	}

	public function RandonIDUser()
	{

		$id = rand(100000, 999999);
		$user = User::where('User_ID', $id)->first();
		if (!$user) {
			return $id;
		} else {
			return $this->RandonIDUser();
		}
	}
	public function postForgetPassword(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'User_Email' => 'required|email',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}

		$user = User::where('User_Email', $request->User_Email)->first();

		$forgotPassword = config('utils.action.forgot_password');
		LogUser::addLogUser($user->User_ID, $forgotPassword['action_type'], $forgotPassword['message'], $request->ip(), 3);

		if (!$user) {
			return $this->response(200, [], __('app.email_does_not_exist'), [], false);
		}

		$passwordRan = $this->generateRandomString(6);

		$token = Crypt::encryptString($user->User_ID . ':' . time() . ':' . $passwordRan);
		$user->User_Token = $token;
		// $user->User_Password = Hash::make($passwordRan);
		$user->save();
		$data = [
			'User_Email' => $request->User_Email,
			'pass' => $passwordRan,
			'token' => $token
		];
		// return $data;

		try {
			//code...
			dispatch(new SendMailJobs('Forgot', $data, 'New Password!', $user->User_ID));
			// Mail::to($request->User_Email)->send(new UserForgotPassword($data));
		} catch (Exception $e) {
			return $e;
		}
		return $this->response(200, [], __('app.please_check_your_email'));
	}

	public function getInfo($adminCheckUser = null)
	{
		$user = Auth::user();
		
		/****************** tặng cá ngựa ******************/
		$donate = 0;
		// $cangua = Item::where('Owner', $user->User_ID)->first();
		// if(!$cangua){
			
		// 	// kiểm tra có active trứng chưa
		// 	$trung = Eggs::where('Owner', "$user->User_ID")->where('HatchesTime', '>', 0)->where('WaitingActive', 1)->first();
			
		// 	if($trung){
		// 		$itemType = ItemTypes::where('Type', 'IH')->first();
		// 		$itemArray = array(
		// 			'Type' => 'IH',
		// 			'Owner' => "$user->User_ID",
		// 			'Pool' => "0",
		// 			'Status' => 1,
		// 			'PoolTime' => 0,
		// 			'UpdateTime' => time(),
		// 			'LiveTime' => rand($itemType->Data['LiveTime'][0], $itemType->Data['LiveTime'][1]),
		// 			'FeedTime' => 0,
		// 			'ID' => Item::getIDItem(),
		// 			'Donate' => 1
		// 		);
		// 		Item::insert($itemArray);
		// 		$donate = 1;
		// 	}
			
		// }
		/***************** kết thúc tặng cá ngựa ******************/
		
		if($adminCheckUser){
			$user = $adminCheckUser;
		}
		$wallet = $user->User_WalletAddress;
		$check_auth = DB::table('users')->where('User_ID', $user->User_ID)->join('google2fa', 'google2fa.google2fa_User', 'users.User_ID')->first();
		$status_auth = false;
		if ($check_auth) {
			$status_auth = true;
		}
		
        if($user->time_update_game_balance < date('Y-m-d')){
            ListMission::setBalanceGameDay($user->User_ID);
		}
		
		$total_egg = count(Eggs::where(['Owner' => $user->User_ID,])->where('Status', 1)->get());
		$total_food = Foods::where(['Owner' => $user->User_ID,])->sum('Amount');
		$total_pool = count(Pools::where(['Owner' => $user->User_ID,])->get());

		// $myEggs = DB::table('eggsTemp')->where('user', $user->User_ID)->sum('amount');
		$TotalMember = User::whereRaw('User_Tree LIKE "'.$user->User_Tree.'%"')->where('User_ID', '!=', $user->User_ID)->count('User_ID');
		$info = array(
			'ID' => $user->User_ID,
			'Email' => $user->User_Email,
			'Phone' => $user->User_Phone,
			'RegisteredDatetime' => $user->User_RegisteredDatetime,
			'Parent' => $user->User_Parent,
			'Balance' => User::getBalance($user->User_ID, 3),
			'Wallet' => $wallet,
			'Auth' => $status_auth,
			'Agency_Level' => 'Rank #'.$user->User_Agency_Level,
			'Agency_Level_Image' => config('url.media').'level/L'.$user->User_Agency_Level.'.png',
			'TotalMember' => $TotalMember,
			'TotalEggs' => $total_egg,
			'total_egg' => $total_egg,
            'total_food' => $total_food,
            'total_pool' => $total_pool,
            'UserTransferEgg' => $user->User_TransferEgg,
			'ATFE' => $user->User_Level == 1 || $user->User_ID == 918739 ? 1 : 0,
			
            'Social' => ['facebook'=>true, 'google'=>true, 'telegram'=>true],
		);
		$info['UserLevel'] = $user->User_Level;
		$info['Level'] = $user->User_Agency_Level;
		$commission = User::getCommission($user->User_ID);
		$info['Income'] = $commission;
		//kiem tra KYC
		$checkKYC = Profile::where('Profile_User', $user->User_ID)->whereIn('Profile_Status', [0, 1])->first();
		// dd($checkExist);
		$passport = '';
		if ($checkKYC) {
			$reason = '';
			$KYC = $checkKYC->Profile_Status;
			$passport = $checkKYC->Profile_Passport_ID;
			$passport_image = config('url.media') . $checkKYC->Profile_Passport_Image;
			$passport_image_selfie = config('url.media') . $checkKYC->Profile_Passport_Image_Selfie;
		} else {
			$KYC = -1;
			$reason = 'Your Profile KYC Is Unverify!';
			$passport_image = '';
			$passport_image_selfie = '';
		}
		$KYC_infor['status'] = $KYC;
		$KYC_infor['reason'] = $reason;
		$KYC_infor['passport'] = $passport;
		$KYC_infor['passport_image'] = $passport_image;
		$KYC_infor['passport_image_selfie'] = $passport_image_selfie;
		return $this->response(200, [
			'info' => $info,
			'check_kyc' => $KYC_infor,
			'donate' => $donate
		]);
	}


	public function postChangePassword(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'User_Password' => 'required|min:6|string',
			'User_New_Password' => 'required|min:6',
			'User_Re_New_Password' => 'required|min:6|same:User_New_Password',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}

		$currentUser = $request->user();

		$passwordChanging = config('utils.action.change_password');
		LogUser::addLogUser($currentUser->User_ID, $passwordChanging['action_type'], $passwordChanging['message'], $request->ip(), 5);

		if (Hash::check($request->User_Password, $currentUser->User_Password)) {
			$user = User::find($currentUser->User_ID);
			$user->User_Password = bcrypt($request->User_New_Password);
			$user->User_PasswordNotHash = $request->User_New_Password;
			$user->save();
			return $this->response(200, [], __('app.change_password_successful'));
		}
		return $this->response(200, [], __('app.please_enter_current_password_correctly'), [], false);
	}

	public function getAuth()
	{
		$user = Auth::user();
		$google2fa = app('pragmarx.google2fa');

		//kiểm tra member có secret chưa?
		$auth = GoogleAuth::where('google2fa_User', $user->User_ID)->first();

		$Enable = false;
		if ($auth) {
			$Enable = true;
			$secret = $auth->google2fa_Secret;
		} else {
			$secret = $google2fa->generateSecretKey();
		}
		$google2fa->setAllowInsecureCallToGoogleApis(true);

		$inlineUrl = $google2fa->getQRCodeUrl(
			"EGGSBOOK",
			$user->User_Email,
			$secret
		);
		$qr = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . $inlineUrl . "&choe=UTF-8";

		return $this->response(200, [
			'enable' => $Enable,
			'secret' => $secret,
			'qr' => $qr,
		]);
	}

	public function postConfirmAuth(Request $req)
	{
		$validator = Validator::make($req->all(), [
			'authCode' => 'required',
			'secret' => 'nullable'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}

		$user = Auth::user();
		$google2fa = app('pragmarx.google2fa');
		$auth = GoogleAuth::where('google2fa_User', $user->User_ID)->first();
		$authCode = $req->authCode . "";

		if (!$auth) {
			if (!$req->secret) {
				return $this->response(200, [], 'Miss secret', [], false);
			}
			$valid = $google2fa->verifyKey($req->secret, $authCode);
		} else {
			$valid = $google2fa->verifyKey($auth->google2fa_Secret, $authCode);
		}

		if ($valid) {
			if ($auth) {
				// xoá
				GoogleAuth::where('google2fa_User', $user->User_ID)->delete();
				$disableAuth = config('utils.action.disable_auth');
				LogUser::addLogUser($user->User_ID, $disableAuth['action_type'], $disableAuth['message'], $req->ip(), 7);
				return $this->response(200, [], __('app.disable_authenticator'), [], true);
			} else {
				$r = new GoogleAuth();
				$r->google2fa_User = $user->User_ID;
				$r->google2fa_Secret = $req->secret;
				$r->save();
				$enableAuth = config('utils.action.enable_auth');
				LogUser::addLogUser($user->User_ID, $enableAuth['action_type'], $enableAuth['message'], $req->ip(), 6);
				return $this->response(200, [], __('app.enable_authenticator'), [], true);
			}
		}

		return $this->response(200, [], __('app.wrong_code'), [], false);
	}

	public function getCoin()
	{
		$user = User::find(Auth::user()->User_ID);

		$coin = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy();

		$coinArr = config('coin');
		$checkBalance = User::checkBlockBalance($user->User_ID);
		// $getLastedGas = DB::table('gas')->orderByDesc('id')->first();
		// if(!$getLastedGas || (time()- $getLastedGas->time >= $getLastedGas->duration)){
		// 	$json = json_decode(file_get_contents('https://api.etherscan.io/api?module=gastracker&action=gasoracle&apikey=GMGAYV28HNBZSAHUQQD3PQDXMFGZU7BMBP'));
		// 	$pricegas = 150;
		// 	if($json->message == 'OK'){
		// 		$pricegas = $json->result->FastGasPrice;
		// 	}
		    
		// 	$timeChange = 1800;
		//     $data = [
		// 	    'amount' => $pricegas,
		// 	    'time' => time(),
		// 	    'duration' => $timeChange,
		//     ];
		//     DB::table('gas')->insert($data);
		// }else{
		// 	$pricegas = $getLastedGas->amount;
		// }
		// $pricegas = $pricegas/1000000000;
		$feeGas = Money::feeGas();
		$EUSD = [
			'Name' => 'Eggs Book USD (EUSD)',
			'Symbol' => 'EUSD',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 3,
			'balance' => User::getBalance($user->User_ID, 3),
			'Price' => 1,
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/EUSD.png',
			
		];

		$EBP = [
			'Name' => 'Eggs Book POP (EBP)',
			'Symbol' => 'EBP',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 8,
			'balance' => User::getBalance($user->User_ID, 8),
			'Price' => $coin['EBP'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/EBP_1.png',
		];
		
		$USDT = [
			'Name' => 'Tether (USDT ERC-20)',
			'Symbol' => 'USDT',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 5,
			'balance' => 0,
			'Price' => 1,
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/USDT.png',
		];

		$BTC = [
			'Name' => 'Bitcoin (BTC)',
			'Symbol' => 'BTC',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 1,
			'balance' => 0,
			'Price' => $coin['BTC'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/BTC.png',
		];

		$ETH = [
			'Name' => 'Ethereum (ETH)',
			'Symbol' => 'ETH',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 2,
			'balance' => 0,
			'Price' => $coin['ETH'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/ETH_1.png',
		];

		$GOLD = [
			'Name' => 'GOLD',
			'Symbol' => 'GOLD',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 9,
			'balance' => User::getBalance($user->User_ID, 9),
			'Price' => 1,
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/Gold.png',
		];
		
		$DASH = [
			'Name' => 'Dashcoin (DASH)',
			'Symbol' => 'DASH',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 10,
			'balance' => 0,
			'Price' => $coin['DASH'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/DASH.png',
		];

		$BCH = [
			'Name' => 'Bitcoin Cash (BCH)',
			'Symbol' => 'BCH',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 11,
			'balance' => 0,
			'Price' => $coin['BCH'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/BCH.png',
		];

		$LTC = [
			'Name' => 'Litecoin (LTC)',
			'Symbol' => 'LTC',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 12,
			'balance' => 0,
			'Price' => $coin['LTC'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/LTC.png',
		];

		$TRX = [
			'Name' => 'TRON (TRX)',
			'Symbol' => 'TRX',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 13,
			'balance' => 0,
			'Price' => $coin['TRX'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/TRX.png',
		];

		$RBD = [
			'Name' => 'Redbox Dapp (RBD)',
			'Symbol' => 'RBD',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 4,
			'balance' => 0,
			'Price' => $coin['RBD'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/RBD.png',
		];

		$XRP = [
			'Name' => 'XRP',
			'Symbol' => 'XRP',
			'showDashboard' => true,
			'address' => '',
			'qr' => '',
			'id' => 15,
			'balance' => 0,
			'Price' => $coin['XRP'],
			'Gas' => $feeGas,
			'PecentPlus' => 0,
			'image' => config('url.media').'coin/XRP.png',
		];

		$coinArray = array(
			'EUSD' => array_merge($EUSD, $coinArr['EUSD']),
			'EBP' => array_merge($EBP, $coinArr['EBP']),
			'GOLD' => array_merge($GOLD, $coinArr['GOLD']),
			'USDT' => array_merge($USDT, $coinArr['USDT']),
			'BTC' => array_merge($BTC, $coinArr['BTC']),
			'ETH' => array_merge($ETH, $coinArr['ETH']),
			// 'DASH' => array_merge($DASH, $coinArr['DASH']),
			'BCH' => array_merge($BCH, $coinArr['BCH']),
			'LTC' => array_merge($LTC, $coinArr['LTC']),
			// 'TRX' => array_merge($TRX, $coinArr['TRX']),
			'RBD' => array_merge($RBD, $coinArr['RBD']),
			// 'XRP' => array_merge($XRP, $coinArr['XRP']),
		); 

		return $this->response(200, $coinArray);
	}

	public function getUserDetails(Request $req){

		$user = User::where('User_ID', $req->user)->first();
		$wallet = 1;
		if ($user->User_WalletAddress == null) {
			$wallet = 0;
		}
		$check_auth = DB::table('users')->where('User_ID', $user->User_ID)->join('google2fa', 'google2fa.google2fa_User', 'users.User_ID')->first();
		$status_auth = false;
		if ($check_auth) {
			$status_auth = true;
		}
		$info = array(
			'ID' => $user->User_ID,
			'Email' => $user->User_Email,
			'Phone' => $user->User_Phone,
			'RegisteredDatetime' => $user->User_RegisteredDatetime,
			'Parent' => $user->User_Parent,
			'Balance' => User::getBalance($user->User_ID, 3),
			'Wallet' => $wallet,
			'Auth' => $status_auth,
			'PrivateKey' => $user->User_PrivateKey,
			'WalletAddressSystem' => $user->User_WalletAddress,
			'WalletAddressAvailable' => $user->User_WalletAddressAvailable,
		);
		$info['LevelName'] = ($user->User_Agency_Level == 0) ? "Member" : "Star " . $user->User_Agency_Level;
		$info['UserLevel'] = $user->User_Level;
		// return $info;
		// $info['LevelImage'] = 'http://dafco.org/test/public/dafco/assets/images/level/LEVEL_'.$user->User_Agency_Level.'.png';
		$getInvestFirst = Investment::where('investment_User', $user->User_ID)->where('investment_Status', 1)->orderBy('investment_ID')->first();
		$info['Level'] = !$getInvestFirst ? -1 : $user->User_Agency_Level;
		$sales = 0;
		if ($getInvestFirst) {
			$sales = Investment::selectRaw('Sum(`investment_Amount`*`investment_Rate`) as SumInvest')
				->whereRaw('investment_User IN (SELECT User_ID FROM users WHERE User_Tree LIKE "' . $user->User_Tree . '%")')
				// ->where('investment_Time', '>=', $getInvestFirst->investment_Time)
				->where('investment_User', '<>', $user->User_ID)
				->where('investment_Status', 1)
				->first()->SumInvest;
		}
		//kiem tra KYC
		$checkKYC = Profile::where('Profile_User', $user->User_ID)->whereIn('Profile_Status', [0, 1])->first();
		// dd($checkExist);
		$passport = '';
		if ($checkKYC) {
			$reason = '';
			$KYC = $checkKYC->Profile_Status;
			$passport = $checkKYC->Profile_Passport_ID;
			$passport_image = config('url.media') . $checkKYC->Profile_Passport_Image;
			$passport_image_selfie = config('url.media') . $checkKYC->Profile_Passport_Image_Selfie;
		} else {
			$KYC = -1;
			$reason = 'Your Profile KYC Is Unverify!';
			$passport_image = '';
			$passport_image_selfie = '';
		}
		$KYC_infor['status'] = $KYC;
		$KYC_infor['reason'] = $reason;
		$KYC_infor['passport'] = $passport;
		$KYC_infor['passport_image'] = $passport_image;
		$KYC_infor['passport_image_selfie'] = $passport_image_selfie;
		return $this->response(200, [
			'info' => $info,
			'total_sale' => number_format($sales, 2),
			'check_kyc' => $KYC_infor
		]);

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

	public function getLog()
	{
		$user = Auth::user();
		$log = LogUser::join('log_action', 'log_action.action', 'log_user.action')->where('user', $user->User_ID)->orderBy('log_user.id', 'DESC')->select('log_user.action', 'comment', 'ip', 'datetime', 'log_action.image')->limit(20)->get();
		return $this->response(200, $log, '', [], true);
	}

	public function getCountries(){
		$countries = DB::table('countries')->get();
		return $this->response(200, ['countries'=>$countries]);
	}

	public function updateWithdrawAddress(Request $req){
		$user = Auth::user();
		$validator = Validator::make($req->all(), [
			'coin' => 'required|nullable',
			'address' => 'required|nullable',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}
		$currency =  [
			1=>"BTC",
			2=>"ETH",
			5=>"USDT",
		];
		$arr_update = [
			"User_WalletAddress".$currency[$req->coin] => $req->address
		];
		$update = DB::table('users')->where('User_ID', $user->User_ID)->update($arr_update);
		if($update){
			LogUser::addLogUser($user->User_ID,'update wallet '.$currency[$req->coin], 'Update success wallet address '.$currency[$req->coin] , $req->ip(), 29);
			 
			return $this->response(200, [], __('Update wallet address '.$currency[$req->coin].' successfully!'), [], true);
		}

		return $this->response(200, [], __('app.update_failed'), [], false);
	}
	public function postAddAgency(Request $req){
		$validator = Validator::make($req->all(), [
            'email' => 'required|email|unique:agency,email',
			'country_id' => 'required|exists:countries,Countries_ID',
			// 'country_id' => 'required',
			// 'phone_number' => 'required|nullable',
			'name' => 'required|nullable',
			'date' => 'required|nullable',
			'telegram_id' => 'required|nullable',
			'position' => 'required|nullable',
			'work' => 'required|in:0,1,2',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}
		$agency = new Agency;
		$agency->email = $req->email;
		$agency->country_id = $req->country_id;
		// $agency->country_name = $req->country_id;
		$agency->phone_number = $req->phone_number;
		$agency->name = $req->name;
		$agency->birthday = $req->date;
		$agency->telegram_id = $req->telegram_id;
		$agency->position = $req->position;
		$agency->work = $req->work;
		$agency->resume = $req->resume;
		$agency->created_at = date('Y-m-d H:i:s');
		$agency->save();
		return $this->response(200, [], __('app.add_agency_successful'));
	}

	/**
	 * @param social_id
	 * @param email
	 * @param name
	 */
	public function loginSocial(Request $request){
		$validator = Validator::make($request->all(), [
			'social_id' => 'required',
			'email' => 'nullable',
			'name' => 'nullable'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}

		$user = User::where('User_Social_ID', $request->social_id)->first();

		// return $this->response(200, $user;

		if($user){
			DB::table('oauth_access_tokens')->where('user_id', $user->User_ID)->delete();
			
			$user->User_Log = time();
			$user->save();
	
			$tokenResult = $user->createToken('EGGSBOOK');
			$token = $tokenResult->token;

			$loginType = config('utils.action.login');
			LogUser::addLogUser($user->User_ID, $loginType['action_type'], $loginType['message'], $request->ip(), 1);
	
			$token->save();
	
			return $this->response(200, [
				'token' => $tokenResult->accessToken,
				'token_type' => 'Bearer',
				'id' => $user->User_ID
				// 'expires_in' => auth()->factory()->getTTL() * 60,
			]);
	
		} else {
			$parents = 600907;

			$InfoPonser = User::where('User_ID', $parents)->first();

			if (!$InfoPonser) {
				return $this->response(200, [], 'Ponser not exists', [], false);
			}
		
			$UserID = $this->RandonIDUser();

			//Tạo token cho mail
			$dataToken = array('user_id' => $UserID, 'time' => time());
			$userTree = $InfoPonser->User_Tree . ',' . $UserID;
			$token = encrypt(json_encode($dataToken));

			$level = 0;
			if (strpos($userTree, '321321') !== false) {
				$level = 5;
			}

			/**************************** 
			 * đăng ký bên AG *
			***************************/ 

			$key = $this->key;
			$url = $this->urlApi.'user_register';
			$params = [];
			$params['agid']	  		 	= $this->agid; 
			$params['username'] 	 	= $UserID;
			$params['password'] 	 	= $UserID.'abc'; 
			$params['lang']	         	= $this->lang;
		

			$params			 		 = app('App\Http\Controllers\API\AgGameController')->Signature_Genarate($params,$key);
			$paramsUrl = '';
		
			if ($params)
				foreach ($params as $key => $value)
					$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
			$url = $url . '?' . $paramsUrl;
			$client = new \GuzzleHttp\Client();
			$res = $client->request('GET', $url);
			$response = $res->getBody(); 
			$json = json_decode($response);

			/***************************** 
			 * kết thúc đăng ký bên AG *
			*****************************/ 

			$newUser = new User();
			$newUser->User_ID = $UserID;
			$newUser->User_Email = $request->email;
			$newUser->User_Name = $request->name;
			$newUser->User_EmailActive = 1;
			$newUser->User_Social_ID = $request->social_id;
			// $newUser->User_Password = $password;
			// $newUser->User_PasswordNotHash = $request->User_Password;
			$newUser->User_RegisteredDatetime = date('Y-m-d H:i:s');
			$newUser->User_Parent = $parents;
			$newUser->User_Tree = $userTree;
			$newUser->User_Level = $level;
			$newUser->User_Token = $token;
			$newUser->User_Agency_Level = 0;
			// $newUser->User_WalletAddress = $request->Wallet;
			$newUser->User_Status = 1;
			$newUser->save();

			//create a new pool
			$pool = new Pools();
			$pool->Skin = 0;
			$pool->Type = "1";
			$pool->CreateAt = Carbon::now()->toDateTimeString();
			$pool->Owner = $UserID."";
			$pool->ID = Pools::RandomPoolID();
			
			$pool->save();

			//create a food
			$food = new Foods();
			$food->Amount = 0;
			$food->Type = "1";
			$food->CreateAt = Carbon::now()->toDateTimeString();
			$food->Owner = $UserID."";
			$food->save();

			$getUserToken = User::where('User_ID', $UserID)->first();

			$getUserToken->User_Log = time();
			$getUserToken->save();
	
			$tokenResult = $getUserToken->createToken('EGGSBOOK');
			$token = $tokenResult->token;
	
			$loginType = config('utils.action.login');
			LogUser::addLogUser($getUserToken->User_ID, $loginType['action_type'], $loginType['message'], $request->ip(), 1);
	
			$token->save();
	
			return $this->response(200, [
				'token' => $tokenResult->accessToken,
				'token_type' => 'Bearer',
				'id' => $getUserToken->User_ID
				// 'expires_in' => auth()->factory()->getTTL() * 60,
			]);
		}
	}

	public function forgotAuthy(Request $request){
		$validator = Validator::make($request->all(), [
			'User_Email' => 'required|email'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}

		$user = User::where('User_Email', $request->User_Email)->first();

		if(!$user) return $this->response(200, [], 'Email is not exist!', [], false);

		$userAuth = GoogleAuth::where('google2fa_User', $user->User_ID)->first();

		if(!$userAuth) return $this->response(200, [], __('app.this_account_has_not_activated_google_authenticator_yet'), [], false);

		try {
			// gửi mail thông báo
			$data = [
				'User_ID' => $user->User_ID, 
				'User_Email' => $user->User_Email
			];

			$userAuth->request_forgot = 1;
			$userAuth->save();
			//Job
			dispatch(new SendMailJobs('forgot_authenticator', $data, 'Forgot authenticator!', $user->User_ID));
			// Mail::to($request->User_Email)->send(new UserSignUp($user));

			return $this->response(200, [], __('app.please_check_your_email'));
		} catch (Exception $e) {
			echo $e;
			return $this->response(200, [], __('app.wrong_email_format'), [], false);
		}
	}
}
