<?php

namespace App\Http\Controllers\API;

use App\Model\Investment;
use App\Model\Money;
use App\Model\Log;
use App\Model\User;
use App\Model\GoogleAuth;
use GuzzleHttp\Client;
use http\Env\Response;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class InvestmentController extends Controller
{
    public function postInvestment(Request $req)
    {   
        //check spam
        $data = json_decode(json_encode($req->data));
        $user = Auth::user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $data->CodeSpam)->first();
        if($checkSpam == null){
            //khoong toonf taij
            return response()->json(['status'=>false, 'message'=>'Misconduct!']);
            // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Misconduct!']);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }   
        // $client = new Client();
		// $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
		// 		'form_params' => [
		// 				'secret' => '6Ld_4bQZAAAAAF5L2KwCLDUfTSVC_W0_Kodw6BI2',
		// 				'response' => $req->input('g-recaptcha-response'),
		// 		]
		// ]);
		// $checkrole = json_decode($response->getBody())->success;
		// if($checkrole == false){
        //     return response(array('status'=>false, 'mgs' => 'Catcha does not exist'), 200);
        // }
   
        // $user = User::find(Session('user')->User_ID);
        // $req->validate([
        //     // 'currency' => 'required',
        //     'amount' => 'required|numeric|min:0|in:100,300,500,1000,3000,5000',
        // ]);
        $google2fa = app('pragmarx.google2fa');
        // $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
        // if(!$AuthUser){
        //     return response(array('status'=>false, 'message'=> 'User Unable Authenticator'), 200);
        //     // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'User Unable Authenticator']);
        // }
        // $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $data->otp);
        // if(!$valid){
        //     return response(array('status'=>false, 'message'=> 'Wrong code'), 200);
        //     // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Wrong code']);
        // }
        if(!isset($data->amount)){
            return response()->json(array('status'=>false, 'message'=>__('Please enter your amount')), 200);
        }
        if($data->amount <= 0){
            return response()->json(array('status'=>false, 'message'=>__('Please enter your amount')), 200);
        }
        if (!filter_var($data->amount, FILTER_VALIDATE_FLOAT)) {
            return response(array('status'=>false, 'message'=>__('Amount is\'t number')), 200);
        }
        $arrCoin = [ 5 => 'USDT'];
        //Check cố định amount
        if($data->amount < 100){
            return response(array('status'=>false, 'message'=>__('Min package amount 100$')), 200);
            // return response()->json(['status'=>false, 'message'=>'Min package amount 100$!']);
            // return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Min package amount 100$']);
        }
        $data->currency = 5;
        if(!isset($arrCoin[$data->currency])){
            return response(array('status'=>false, 'message'=>__('Invalid currency!')), 200);
            // return response()->json(['status'=>false, 'message'=>'Invalid currency!']);
            // return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Invalid currency']);
        }
        //RATE
        $rate = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy();
        //Balance
		$balance = User::getBalance($user->User_ID, $data->currency);
        //currency == 5(USDT)
        $amount = $data->amount;
        if($amount > $balance){
            return response(array('status'=>false, 'message'=>__('Your balance is not enough')), 200);
            // return response()->json(['status'=>false, 'message'=>'Your balance is not enough!']);
            // return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Your balance is not enough']);
        }
        
        if($data->amount < 100){
            return response(array('status'=>false, 'message'=>__('Min package $100!')), 200);
            // return response()->json(['status'=>false, 'message'=>'Min package $100!!']);
            // return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Min package $100!']);
        }
        
        //Get RATE balance
        $name_coin = $arrCoin[$data->currency];

		$updateblance = User::updateBalance($user->User_ID, $data->currency, -($amount));
        //Trừ tiền 
	    $moneyArray = array(
		    'Money_User' => $user->User_ID,
		    'Money_USDT' => -$amount,
		    'Money_USDTFee' => 0,
		    'Money_Time' => time(),
			'Money_Comment' => 'Invesment '.$amount.' '.$arrCoin[$data->currency],
			'Money_MoneyAction' => 3,
			'Money_MoneyStatus' => 1,
            'Money_Rate' => $rate[$name_coin],
            'Money_CurrentAmount' => $amount,
			'Money_Currency' => $data->currency
        );
        //Invest
        $invest = array(
		    'investment_User' => $user->User_ID,
            'investment_Amount' => $amount,
		    'investment_Rate' => 1,
		    'investment_Currency' => $data->currency,
		    'investment_Time' => time(),
		    'investment_Status' => 1
	    );
	    // thêm dữ liệu
	    DB::table('investment')->insert($invest);
        DB::table('money')->insert($moneyArray);
		// if($user->User_MatrixStatus == 0){
		//     //xét cây Matrix 3 chân
		//     $parentID = 123123;
		//     $getIDANDNode = app('App\Http\Controllers\API\MatrixController')->getUserIDMissBranch($parentID);
		//     if(!$getIDANDNode){
        //         return response(array('status'=>false, 'message'=>'Sponsor ID does not exist'), 200);
		// 	    // return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Sponsor ID does not exist']);
		//     }
		//     $presenterID = $getIDANDNode['user_id'];
		//     $presenter = User::find($presenterID);
		//     $node = $getIDANDNode['node_miss'];
		    
		//     $user->User_MatrixParent = $parentID;
		//     $user->User_MatrixTree = $presenter->User_MatrixTree.','.$user->User_ID;
		//     $user->User_PositionMatrixTree = $node;
		//     $user->User_MatrixStatus = 1;
		//     $user->User_MatrixTimeJoin = date('Y-m-d H:i:s');
		//     $user->save();
        //     $this->checkMatrixCom($user);
		// }
		if(!$user->User_DateInvest){
			$user->User_DateInvest = date('Y-m-d H:i:s');
		    $user->save();
		}
        //checkDirectCom
        $this->checkDirectCom($user, $amount);
        
        $this->checkAgencyLevel($user);
        // $this->BonusPoint($user, $rate, $amount);
        $balance = User::getBalance1($user->User_ID);
        return response(array('status'=>true, 'message'=>__('Investment complete'), 'balance'=>$balance), 200);
        // return redirect()->route('system.getInvestment')->with(['flash_level'=>'success','flash_message'=>'Investment complete']);  
    }
    
    public function checkDirectCom($user, $amountUSD){
		if($user->User_Level == 4){
            return false;
        }
	    $percent = 0.05;
        $currency = 13;
        $action = 5;
        $rate = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy('USD');
        $user = User::find($user->User_ID);
        
        $parent = User::where('User_ID', $user->User_Parent)->first();
        if(!$parent || $parent->User_Block == 1){
            return false;
        }
        
        $getInvestUser = Investment::where('investment_User', $parent->User_ID)->where('investment_Status', '<>', -1)->orderBy('investment_ID')->first();
        if(!$getInvestUser){
            Log::insertLogProfit($parent->User_ID,'Commission Direct', 'User ID: '.$parent->User_ID.' Dont Have Investment');
            return false;
        }
        $amountCom = $amountUSD * $percent;
        $amountCom = $amountCom / $rate;
        $maxOut = '';
        // $checkMaxOut = Money::checkMaxOut($parent->User_ID, $amountCom);
        // if($checkMaxOut < $amountCom){
	    //     if($checkMaxOut > 0){
        //         $maxOut = 'Maxout';
		//         $amountCom = $checkMaxOut;
	    //     }else{
        //         Log::insertLogProfit($parent->User_ID,'Commission Direct', 'User ID: '.$parent->User_ID.' Maxout');
		//         return false;
	    //     }
        // }
        // dd($amountCom, $checkMaxOut);
        $money = new Money();
        $money->Money_User = $parent->User_ID;
        $money->Money_USDT = $amountCom;
        $money->Money_Time = time();
        $money->Money_Comment = 'Direct Commission From User ID: '.$user->User_ID." ".$maxOut;
        $money->Money_Currency = $currency;
        $money->Money_MoneyAction = $action;
        $money->Money_Address = '';
        $money->Money_Rate = $rate;
        $money->Money_MoneyStatus = 1;
        $money->save();
        //Update Balance
        $updateblance = User::updateBalance($parent->User_ID, $currency, $amountCom);
        // $checkMaxOut = Money::checkMaxOut($parent->User_ID, $amountCom);
		// $this->checkIndirectCom($parent, $amountCom);

    }

    public function checkAgencyLevel($user){
        $arr_agency = [
            1=>['min'=>20000, 'max'=>50000],
            2=>['min'=>50000, 'max'=>100000],
            3=>['min'=>100000, 'max'=>999999999]
        ];
        $arrParent = explode(',', $user->User_Tree);
        $arrParent = array_reverse($arrParent);
        for($i = 1; $i<=10; $i++){
	        if(!isset($arrParent[$i])){
		        continue;
            }
            $parentTree = User::find($arrParent[$i]);
            if(!$parentTree){
                continue;
            }
            $getInvestUser = Investment::where('investment_User', $parentTree->User_ID)->where('investment_Status', '<>', -1)->orderBy('investment_ID')->first();
            if(!$getInvestUser){
	            continue;
            }
            $agency_level = $this->getAgencyLevel($parentTree, $arr_agency, $getInvestUser->investment_Time);
            if($agency_level == 0 || $parentTree->User_Agency_Level == $agency_level){
                continue;
            }
            
            $update_agency_level = User::where('User_ID', $parentTree->User_ID)->update(['User_Agency_Level'=>$agency_level]);
            Log::insertLogProfit($parentTree->User_ID,'Update agency level', 'User ID: '.$parentTree->User_ID.' update agency level '.$agency_level); 

            
        }
        return true;
                          
    }
    public static function getAgencyLevel($user, $arr_agency, $timeInvest){
	    $total_invest = Investment::join('users', 'investment_User', 'User_ID')
                                    ->where('User_Tree', 'like', $user->User_Tree.',%')
                                    ->where('investment_Time', '>=', $timeInvest)
                                    ->where('investment_Status', 1)
                                    ->sum('investment_Amount');
	    foreach($arr_agency as $i => $v){
		    if($total_invest >= $v['min'] && $total_invest < $v['max']){
			    return $i;
		    }
	    }
	    return 0;
    }
    
    public function checkIndirectCom($user, $amountCom){
	    // hoa hồng gián tiếp
		$percent = 0.5;
		$currency = 10;
		$rateCurrency = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy('USD');
		$action = 6;
        $arrParent = explode(',', $user->User_Tree);
        $arrParent = array_reverse($arrParent);
        $arrFActive = [
        	5=>['min'=>100, 'max'=>300],
        	6=>['min'=>300, 'max'=>500],
        	7=>['min'=>500, 'max'=>1000],
        	8=>['min'=>1000, 'max'=>3000],
        	9=>['min'=>3000, 'max'=>5000],
        	10=>['min'=>5000, 'max'=>9999999999],
        ];
        for($i = 1; $i<=10; $i++){
	        if(!isset($arrParent[$i])){
		        continue;
	        }
            $parentTree = User::find($arrParent[$i]);
            if(!$parentTree){
	            Log::insertLogProfit($parentTree->User_ID,'Commission Indirect', 'User ID: '.$parentTree->User_ID.' Not Found');
                continue;
            }
            
            $getInvestUser = Investment::where('investment_User', $parentTree->User_ID)->where('investment_Status', '<>', -1)->orderBy('investment_ID')->first();
            if(!$getInvestUser){
	            Log::insertLogProfit($parentTree->User_ID,'Commission Indirect', 'User ID: '.$parentTree->User_ID.' Dont Have Investment');
	            continue;
            }
            $FActive = $this->getFActive($parentTree->User_ID, $arrFActive);
            
            if($FActive < $i){
	            Log::insertLogProfit($parentTree->User_ID,'Commission Indirect', 'User ID: '.$parentTree->User_ID.' Dont Enough Invest To Get F'.$i);
	            continue;
            }
			$amountCom = $amountCom*$percent;
			
            $maxOut = '';
            // $checkMaxOut = Money::checkMaxOut($parentTree->User_ID, $amountCom);
            // if($checkMaxOut < $amountCom){
            //     if($checkMaxOut > 0){
            //         $maxOut = 'Maxout';
            //         $amountCom = $checkMaxOut;
            //     }else{
            //         Log::insertLogProfit($parentTree->User_ID,'Commission Indirect', 'User ID: '.$parentTree->User_ID.' Maxout');
            //         continue;
            //     }
            // }
            
	        $money = new Money();
	        $money->Money_User = $parentTree->User_ID;
	        $money->Money_USDT = $amountCom;
	        $money->Money_Time = time();
	        $money->Money_Comment = 'Indirect Commission From F'.$i.' User ID: '.$user->User_ID.' ('.($percent*100).'%)'." ".$maxOut;
	        $money->Money_Currency = $currency;
	        $money->Money_MoneyAction = $action;
	        $money->Money_Address = '';
	        $money->Money_Rate = $rateCurrency;
	        $money->Money_MoneyStatus = 1;
	        $money->save();
	        //Update Balance
            $updateblance = User::updateBalance($parentTree->User_ID, $currency, $amountCom);
            $checkMaxOut = Money::checkMaxOut($parentTree->User_ID, $amountCom);
        }
	    return true;

    }
    
    public static function getFActive($userID, $arrFActive){
	    $totalInvest = Investment::where('investment_User', $userID)->where('investment_Status', 1)->sum('investment_Amount');
	    foreach($arrFActive as $i => $v){
		    if($totalInvest >= $v['min'] && $totalInvest < $v['max']){
			    return $i;
		    }
	    }
	    return 0;
    }
    
    public function checkMatrixCom($user){
	    
		if($user->User_Level == 4){
            return false;
        }
	    // hoa hồng cộng hưởng
		$amountCom = 3;
		$currency = 10;
		$rateCurrency = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy('USD');
		$action = 19;
        $arrParent = explode(',', $user->User_MatrixTree);
        $arrParent = array_reverse($arrParent);
        // $arrFActive = [
        // 	5=>['min'=>100, 'max'=>300],
        // 	6=>['min'=>300, 'max'=>500],
        // 	7=>['min'=>500, 'max'=>1000],
        // 	8=>['min'=>1000, 'max'=>3000],
        // 	9=>['min'=>3000, 'max'=>5000],
        // 	10=>['min'=>5000, 'max'=>9999999999],
        // ];
        for($i = 1; $i<=10; $i++){
	        
	        if(!isset($arrParent[$i])){
		        continue;
	        }
            $parentTree = User::find($arrParent[$i]);
            if(!$parentTree){
	            Log::insertLogProfit($parentTree->User_ID,'Commission Matrix', 'User ID: '.$parentTree->User_ID.' Not Found');
                continue;
            }
            
            $getInvestUser = Investment::where('investment_User', $parentTree->User_ID)->where('investment_Status', '<>', -1)->orderBy('investment_ID')->first();
            if(!$getInvestUser){
	            Log::insertLogProfit($parentTree->User_ID,'Commission Matrix', 'User ID: '.$parentTree->User_ID.' Dont Have Investment');
	            continue;
            }
            // $FActive = $this->getFActive($parentTree->User_ID, $arrFActive);
            
            // if($FActive < $i){
	        //     Log::insertLogProfit($parentTree->User_ID,'Commission Matrix', 'User ID: '.$parentTree->User_ID.' Dont Enough Invest To Get F'.$i);
	        //     continue;
            // }
			
	        $money = new Money();
	        $money->Money_User = $parentTree->User_ID;
	        $money->Money_USDT = $amountCom;
	        $money->Money_Time = time();
	        $money->Money_Comment = 'Matrix Commission From Level '.$i.' User ID: '.$user->User_ID;
	        $money->Money_Currency = $currency;
	        $money->Money_MoneyAction = $action;
	        $money->Money_Address = '';
	        $money->Money_Rate = $rateCurrency;
	        $money->Money_MoneyStatus = 1;
	        $money->save();
	        //Update Balance
			$updateblance = User::updateBalance($parentTree->User_ID, $currency, $amountCom);
            $checkMaxOut = Money::checkMaxOut($parentTree->User_ID, $amountCom);
        }
    }

    public function getHistoryInvestment()
    {
        $RandomToken = Money::RandomToken();
        $user = session('user');
        $history_invest = Investment::join('currency', 'Currency_ID' ,'investment_Currency')
        ->where('investment_User', $user->User_ID )
        ->where('investment_Status', '<>', -1)
        ->orderBy('investment_ID', 'DESC')->paginate(15);
    
        return view('System.History.Investment-History', compact('history_invest', 'RandomToken'));
    }
    
    //get investment statistic before invest
    public function postInvestmentStatistic(Request $request) {
        if (!$request->investment_amount || !is_numeric($request->investment_amount) || $request->investment_amount < 0) {
            return response()->json(['status' => 'error', 'message' => 'Investment amount invalid'], 200);
        }

        $user =  session('user');
        $coinAmount = Money::getBalance()->TRUST;
        if ($request->investment_amount > $coinAmount) {
            return response()->json(['status' => 'error', 'message' => 'Not enough coin'], 200);
        }
        if ($request->investment_amount < 1000) {
            $beforeInvestmentAmount = User::join('investment', 'User_ID', 'investment.investment_User')
                ->select('investment.investment_Amount')
                ->where('investment_Status', 1)
                ->where('User_ID', $user->User_ID)
                ->sum('investment.investment_Amount');
            if ($beforeInvestmentAmount < 1000) {
                return response()->json(['status' => 'error', 'message' => 'Minimum investment of 1,000'], 200);
            }

        }
        $moneyRate = $this->getHttp("https://trustexc.com/api/ticker")[0]->price_usd;
        $investmentStatisticData = [
            'investment_amount_statistic' => $request->investment_amount,
            'investment_total_profit' => $request->investment_amount * 1.5,
            'investment_interest_daily' => $request->investment_amount * 1.5 * 0.004,
            'investment_money_rate' => $moneyRate
        ];
        return response()->json(['data' => $investmentStatisticData, 'status' => 'success'], 200);

    }
    protected function getHttp($url)
    {
        $client = new Client();
        $response = $client->get($url);
        return json_decode($response->getBody());
    }

    public function checkToLevel($userID)
    {
        $userTree = User::where('User_ID', $userID)->value('User_Tree');
        $usersArray = explode(",", $userTree);
        $usersArray = array_reverse($usersArray);
//         unset($usersArray[0]);
        foreach ($usersArray as $user) {
			$checkInvest = Investment::where('investment_User', $user)->where('investment_Status', 1)->first();
			if(!$checkInvest){
				continue;
			}
            $this->checkLevel($user);
        }

    }

    public function checkLevel($userID) {


        $list_tree = User::where('User_ID', $userID)->value('User_Tree');

        $total_sales_branch = User::join('investment', 'investment_User', 'User_ID')->where('User_Tree', 'LIKE', $list_tree.',%')->where('investment_Status', 1)->sum(DB::raw('investment_Amount*investment_Rate'));
        if($total_sales_branch < 200000){
        	return false;
        }
        $userAgencyLevel = 1;
        $getF1 = User::where('User_Parent', $userID)->get();
        $agency_level_S1 = 0;
        $agency_level_S2 = 0;
        $agency_level_S3 = 0;
        $agency_level_S4 = 0;
        $agency_level_S5 = 0;
        $agency_level_S6 = 0;
        $agency_level_S7 = 0;
        foreach($getF1 as $user){
	        $treeF1 = $user->User_Tree;
	        $getChildAgency = User::where('User_Tree', 'LIKE', $treeF1.'%')->where('User_Agency_Level', '>=', 1)->get();
	        $getAgency2 = $getChildAgency->where('User_Agency_Level', '>=', 2);
	        $getAgency3 = $getChildAgency->where('User_Agency_Level', '>=', 3);
	        $getAgency4 = $getChildAgency->where('User_Agency_Level', '>=', 4);
	        $getAgency5 = $getChildAgency->where('User_Agency_Level', '>=', 5);
	        $getAgency6 = $getChildAgency->where('User_Agency_Level', '>=', 6);
	        $getAgency7 = $getChildAgency->where('User_Agency_Level', '>=', 7);
	        if(count($getChildAgency)){
		        $agency_level_S1 ++;
	        }
	        if(count($getAgency2)){
		        $agency_level_S2 ++;
	        }
	        if(count($getAgency3)){
		        $agency_level_S3 ++;
	        }
	        if(count($getAgency4)){
		        $agency_level_S4 ++;
	        }
	        if(count($getAgency5)){
		        $agency_level_S5 ++;
	        }
	        if(count($getAgency6)){
		        $agency_level_S6 ++;
	        }
	        if(count($getAgency7)){
		        $agency_level_S7 ++;
	        }
        }
//         $getChildAgency = User::where('User_Tree', 'LIKE', $list_tree.',%')->where('User_Agency_Level', '>=', 1)->get();
//         $agency_level_S1 = $getChildAgency->count();
        if($agency_level_S1 >= 3){
            $userAgencyLevel = 2;
        }
//         $agency_level_S2 = $getChildAgency->where('User_Agency_Level', '>=', 2)->count();
        if($agency_level_S2 >= 3){
            $userAgencyLevel = 3;
        }

//         $agency_level_S3 = $getChildAgency->where('User_Agency_Level', '>=', 3)->count();
        if($agency_level_S3 >= 3){
            $userAgencyLevel = 4;
        }
//         $agency_level_S4 = $getChildAgency->where('User_Agency_Level', '>=', 4)->count();
        if($agency_level_S4 >= 3){
            $userAgencyLevel = 5;
        }
//         $agency_level_S5 = $getChildAgency->where('User_Agency_Level', '>=', 5)->count();
        if($agency_level_S5 >= 3){
            $userAgencyLevel = 6;
        }
//         $agency_level_S6 = $getChildAgency->where('User_Agency_Level', '>=', 6)->count();
        if($agency_level_S6 >= 3){
            $userAgencyLevel = 7;
        }
//         $agency_level_S7 = $getChildAgency->where('User_Agency_Level', '>=', 7)->count();
        if($agency_level_S7 >= 3){
            $userAgencyLevel = 8;
        }
		
        //update level
        $levelCurrent = User::where('User_ID', $userID)->value('User_Agency_Level');
//         dd($getF1, $getChildAgency, $agency_level_S1, $levelCurrent, $userAgencyLevel);
        if($userAgencyLevel != $levelCurrent){

	        Log::insertLog($userID, 'Level Up Affiliate', '000000',  "Up to Level $userAgencyLevel");
	        User::where('User_ID', $userID)->update(['User_Agency_Level'=> $userAgencyLevel]);
		}
    }

    
    public function getTreeInvest($userID, $timeCheck) {
        $userTree = User::where('User_ID', $userID)->value('User_Tree');
        $amount = Investment::join('users', 'Investment_User', 'users.User_ID')
            ->where('users.User_Tree', 'like', "$userTree,%")
            ->where('investment_Time', '>=', $timeCheck)
            ->where('investment_Status', 1)
            ->selectRaw('investment_Amount*investment_Rate as a')->get()->sum('a');
        return $amount;

    }

    public function postActionRefund(Request $req, $id){
        //check spam
        $checkSpam = DB::table('string_token')->where('User', Session('user')->User_ID)->where('Token', $req->CodeSpam)->first();
        
        
        if($checkSpam == null){
            //khoong toonf taij
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Misconduct!']);
        }
        else{
            DB::table('string_token')->where('User', Session('user')->User_ID)->delete();
        }
        

        //refund
        $refund = Investment::where('investment_ID', $id)->where('investment_User', Session('user')->User_ID)->where('investment_Status', 0)->first();
        if(!$refund){
            return redirect()->back()->with(['flash_level'=>'error','flash_message'=>'Investment Null']);
        }
        //cập nhật status
        $refund->investment_Status = 2;
        $refund->save();
        //Package Time
        $fee_refund = DB::table('package_time')->where('time_Month', $refund->investment_Package_Time)->value('time_Fee');
        if(!$fee_refund){
            return redirect()->back()->with(['flash_level'=>'error','flash_message'=>'Error Error']);
        }
        //Rate
        $rateBMG = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy('BMG');
        //Tiến hành rút gốc
        //Cộng tiền
        $moneyArray = array(
		    'Money_User' => $refund->investment_User,
		    'Money_USDT' => $refund->investment_Amount,
		    'Money_USDTFee' => ($refund->investment_Amount * $fee_refund),
		    'Money_Time' => time(),
			'Money_Comment' => 'Refund Investment '.$refund->investment_Amount. ' USDT '.$refund->investment_Package_Time.' Months',
			'Money_MoneyAction' => 5,
			'Money_MoneyStatus' => 1,
            'Money_Rate' => $rateBMG,
            'Money_CurrentAmount' => $refund->investment_Amount,
			'Money_Currency' => $refund->investment_Currency
        );
        DB::table('money')->insert($moneyArray);
        return redirect()->back()->with(['flash_level'=>'success','flash_message'=>'Refund Investment Success!']);
    }
    public function postActionReinvestment(Request $req, $id){
        //check spam
        $checkSpam = DB::table('string_token')->where('User', Session('user')->User_ID)->where('Token', $req->CodeSpam)->first();
        
        
        if($checkSpam == null){
            //khoong toonf taij
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Misconduct!']);
        }
        else{
            DB::table('string_token')->where('User', Session('user')->User_ID)->delete();
        }


        $re_Invest = Investment::where('investment_ID', $id)->where('investment_User', Session('user')->User_ID)->where('investment_Status', 0)->first();
        
        if(!$re_Invest){
            return redirect()->back()->with(['flash_level'=>'error','flash_message'=>'Investment Null']);
        }
        //Rate
        $rateBMG = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy('BMG');
        //cập nhật status
        $re_Invest->investment_Status = 1;
		$re_Invest->investment_ReInvest = 1;
        $re_Invest->investment_TimeOld = $re_Invest->investment_Time;
        $re_Invest->investment_Time = time();
        $re_Invest->save();
        return redirect()->back()->with(['flash_level'=>'success','flash_message'=>'ReInvestment Success!']);
    }
    

}