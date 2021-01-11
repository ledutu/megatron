<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Http\Controllers\System\CoinbaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

use Image;
use PragmaRX\Google2FA\Google2FA;

use DB;
use Mail;
use GuzzleHttp\Client;
use App\Model\Wallet;
use App\Model\GoogleAuth;
use App\Model\User;
use App\Model\userBalance;
use App\Model\Money;



class AgGameController extends Controller{
    public $key;
    public $urlApi;
    public $agid;
    public $lang;
    public $minGold;
    public $minDeposit;
    public $minWithdraw;
    public function __construct(){
        $this->middleware('auth:api');
        $this->middleware('userPermission', ['only' => ['postDeposit', 'postWithdraw', 'getUrlGame']]);
        $ag = config('ag');
        $this->key = $ag['key'];
        $this->urlApi = $ag['url'];
        $this->agid = $ag['agid'];
        $this->lang = $ag['lang'];
        $this->minGold = 150;
        $this->minDeposit = 20;
        $this->minWithdraw = 150;
    }

    public function getUserTransferHistory(){
        $user = Auth::user();
        $key = $this->key;
		$url = $this->urlApi.'user_transfer_history';
        $params = [];
		$params['agid']	  		 	= $this->agid; 
        $params['username'] 	 	= $user->User_ID;
        $params['start_date'] 	 	= date('Y-m-d H:i:s', strtotime('-1 day'));
        $params['end_date'] 	 	= date('Y-m-d H:i:s');
        $params			 		 = $this->Signature_Genarate($params,$key);

        $paramsUrl = '';
		
		if ($params)
			foreach ($params as $key => $value)
				$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
        $url = $url . '?' . $paramsUrl;
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $response = $res->getBody(); 
        $json = json_decode($response, true);
        if($json['status'] == 'OK'){
            
            return $this->response(200, $json, '', [], true);
        }else{
            return $this->response(200, $json, '', [], false);
        }
    }

    public function gethistory(){
        $user = Auth::user();
        $key = $this->key;
		$url = $this->urlApi.'user_game_history';
        $params = [];
		$params['agid']	  		 	= $this->agid; 
        $params['username'] 	 	= $user->User_ID;
        $params['start_date'] 	 	= date('Y-m-d H:i:s');
        $params['end_date'] 	 	= date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $params			 		 = $this->Signature_Genarate($params,$key);

        $paramsUrl = '';
		
		if ($params)
			foreach ($params as $key => $value)
				$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
        $url = $url . '?' . $paramsUrl;
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $response = $res->getBody(); 
        $json = json_decode($response, true);
        if($json['status'] == 'OK'){
            
            return $this->response(200, $json, '', [], true);
        }else{
            return $this->response(200, $json, '', [], false);
        }
    }
    
    public function postDeposit(Request $req){
        $user = Auth::user();
        $validator = Validator::make($req->all(), [
            'amount' => 'required|numeric',
            'CodeSpam' => 'required',
        ]);
        

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }

        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();

        if ($checkSpam == null) {
            //khoong toonf taij
            return $this->response(200, [], __('app.misconduct'), [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
        $minDeposit = $this->minDeposit;
        $balance = User::getBalance($user->User_ID, 9);
        if((int)$req->amount < $minDeposit){
            return $this->response(200, [], __('Min deposit is '.$minDeposit.' gold'), [], false);  
        }
        if((int)$req->amount > $balance){
            return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
        }

        $key = $this->key;
		$url = $this->urlApi.'user_transfer';
        $params = [];
		$params['agid']	  		 	= $this->agid; 
        $params['username'] 	 	= $user->User_ID;
        $params['amount'] 	 	= (float)$req->amount/100;
        $params['orderid'] 	 	= time();
        

        $params			 		 = $this->Signature_Genarate($params,$key);
        $paramsUrl = '';
		
		if ($params)
			foreach ($params as $key => $value)
				$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
        $url = $url . '?' . $paramsUrl;
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $response = $res->getBody(); 
        $json = json_decode($response, true);
        if($json['status'] == 'OK'){
            // trừ tiền người nạp
            $arrayInsert = array(
                'Money_User' => $user->User_ID,
                'Money_USDT' => -(float)($req->amount*1),
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => 'Deposit '.(float)($req->amount*1).' gold to balance game',
                'Money_MoneyAction' => 31,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => 9,
                'Money_CurrentAmount' => (float)($req->amount*1),
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_Confirm_Time' => null,
                'Money_FromAPI' => 1,
            );
            $insert = Money::insert($arrayInsert);
            $balance = User::getBalance($user->User_ID, 9);
            $json['GOLD'] = $balance;
            return $this->response(200, $json, __('app.deposit_mini_game_successful'), [], true);
        }else{
            return $this->response(200, $json, __('app.deposit_mini_game_failed'), [], false);
        }
    }
    public function postWithdraw(Request $req){   
        $user = Auth::user();
        $validator = Validator::make($req->all(), [
            'amount' => 'required|numeric',
            'CodeSpam' => 'required',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }
        if((int)$req->amount < $this->minWithdraw){
            return $this->response(200, [], __('Min withdraw is '.$this->minWithdraw.' gold'), [], false);  
        }

        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();

        if ($checkSpam == null) {
            //khoong toonf taij
            return $this->response(200, [], __('app.misconduct'), [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
       
        $key = $this->key;
		$url = $this->urlApi.'user_transfer';
        $params = [];
		$params['agid']	  		 	= $this->agid; 
        $params['username'] 	 	= $user->User_ID;
        $params['amount'] 	 	= -(float)abs($req->amount/100);
        $params['orderid'] 	 	= time();
        

        $params			 		 = $this->Signature_Genarate($params,$key);
        $paramsUrl = '';
		
		if ($params)
			foreach ($params as $key => $value)
				$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
        $url = $url . '?' . $paramsUrl;
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $response = $res->getBody(); 
        $json = json_decode($response, true);
        if($json['status'] == 'OK'){
            // trừ tiền người nạp
            $arrayInsert = array(
                'Money_User' => $user->User_ID,
                'Money_USDT' => (float)abs($req->amount),
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => 'Withdraw '.(float)abs($req->amount*1).' gold from balance game',
                'Money_MoneyAction' => 32,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => 9,
                'Money_CurrentAmount' => (float)abs($req->amount*1),
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_Confirm_Time' => null,
                'Money_FromAPI' => 1,
            );
            $insert = Money::insert($arrayInsert);
            $balance = User::getBalance($user->User_ID, 9);
            $json['GOLD'] = $balance;
            return $this->response(200, $json, __('app.withdraw_mini_game_successful'), [], true);
        }else{
            return $this->response(200, $json, __('app.withdraw_mini_game_failed'), [], false);
        }
    }

    public function getGameList(){
        $gameList = DB::table('agGameList')->where('game_show', 1)->get();
        $list[0]['list'] = $gameList->where('game_typeWeb', 'slot');
        $list[0]['title'] = 'Slot';
        $list[1]['list'] = $gameList->where('game_typeWeb', 'online_casino');
        $list[1]['title'] = 'Online Casino';
        $list[2]['list'] = $gameList->where('game_typeWeb', 'fishing');
        $list[2]['title'] = 'Fishing';
        return $this->response(200, $list, '', [], true);
        // $key = $this->key;
		// $url = $this->urlApi.'user_game_list';
        // $params = [];
		// $params['agid']	  	= $this->agid; 
        // $params			 	 = $this->Signature_Genarate($params,$key);
        // $paramsUrl = '';
		
		// if ($params)
		// 	foreach ($params as $key => $value)
		// 		$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
        // $url = $url . '?' . $paramsUrl;
        // $client = new \GuzzleHttp\Client();
        // $res = $client->request('GET', $url);
        // $response = $res->getBody(); 
        // $json = json_decode($response, true);
        // if($json['status'] == 'OK'){
        //     $insert = array();
        //     foreach($json['list'] as $v){
        //         $insert[] = array(
        //             'game_code' => $v['game_code'],
        //             'game_name' => $v['game_name'],
        //             'game_type' => $v['game_type'],
        //             'game_h5' => $v['game_h5'],
        //             'game_jackpot' => $v['game_jackpot'],
        //             'game_image_url' => $v['game_image_url'],
        //             'game_display_name' => $v['game_display_name']['english'],
        //             'game_play' => 0,
        //         );
        //     }
        //     DB::table('agGameList')->insert($insert);
        //     return $this->response(200, $insert, '', [], true);

        // }else{
        //     return $this->response(200, $json, '', [], false);
        // }
    }

    public function getBalance(){
        $json = $this->balanceGame();
        if($json['status'] == 'OK'){
            $json['rate'] = 1;
            return $this->response(200, $json, '', [], true);
        }else{
            return $this1->response(200, $json, '', [], false);
        }
        
    }

    public function balanceGame(){
        $user = Session('user') ?? Auth::user();
        $key = $this->key;
		$url = $this->urlApi.'user_detail';
        $params = [];
		$params['agid']	  		 	= $this->agid; 
		$params['username'] 	 	= $user->User_ID;
	

        $params			 		 = $this->Signature_Genarate($params,$key);
        $paramsUrl = '';
		
		if ($params)
			foreach ($params as $key => $value)
				$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
        $url = $url . '?' . $paramsUrl;
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $response = $res->getBody(); 
        $json = json_decode($response, true);
        return $json;
    }

    public function getUrlGame(Request $req){
        $user = Auth::user();
        $key = $this->key;
		$url = $this->urlApi.'user_play_game';
        $params = [];
		$params['agid']	  		 	= $this->agid; 
		$params['username'] 	 	= $user->User_ID;
        $params['game_code'] 	 	= $req->code;
        $params['game_support'] 	= null; 
        $params['lang'] 	 	    = $this->lang; 
        $params['game_back_url'] 	= 'https://system.eggsbook.com/dashboard'; 
        $params			 		 = $this->Signature_Genarate($params,$key);
        $paramsUrl = '';
		
		if ($params)
			foreach ($params as $key => $value)
				$paramsUrl .= (!empty($paramsUrl) ? "&" : "") . rawurlencode($key) . "=" . rawurlencode($value);
		
        $url = $url . '?' . $paramsUrl;
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);
        $response = $res->getBody(); 
        $json = json_decode($response, true);
        if($json['status'] == 'OK'){
            
            return $this->response(200, $json, '', [], true);
        }else{
            return $this->response(200, $json, '', [], false);
        }
        
    }

    public static function Signature_Genarate($Params,$privateKey = false){
    	if(!empty($Params['signature']))
    	{
        	unset($Params['signature']);
    	}
        ksort($Params);

        if(isset($_GET['debug']) && $_GET['debug'] ==1)
            echo implode("", $Params) . $privateKey;

   	 	$Params['signature'] = sha1(implode("", $Params) . $privateKey);
        return $Params;
    }
    
	public static function Signature_Verify($Params , $privateKey = false){
    	if(!is_array($Params) || !$privateKey)
    	{
    		return false;	
    	}
    	
    	$CSignature = '';
    	if(!empty($Params['signature']))
    	{
        	$CSignature = $Params['signature'];
        	unset($Params['signature']);
    	}
    	
        ksort($Params);
    	$Signature = sha1(implode("", $Params) . $privateKey);
        return ($Signature === $CSignature) ? true : false;
    }

   
}