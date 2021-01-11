<?php
namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\System\CoinbaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Excel;

use App\Model\User;
use App\Model\Log;
use App\Model\Money;
use App\Model\Profile;
use App\Model\GoogleAuth;
use App\Model\LogAdmin;
use App\Model\GameBet;

use App\Exports\WalletExport;
use App\Exports\WalletTempExport;
use App\Exports\UserExport;
use App\Model\Stringsession;
use App\jobs\SendMailJobs;
class AdminController extends Controller
{
  
  	public function __construct(){
    	$this->middleware('adminChecking');
  	}
  
  	public function getInsurance(Request $req){
		$getData = DB::table('promotion_sub');
      	if($req->user_id){
          	$getData = $getData->where('user_id', $req->user_id);
        }
      	if($req->id){
          	$getData = $getData->where('id', $req->id);
        }
      	if($req->status && $req->status !== ''){
          	$getData = $getData->where('status', $req->status);
        }
        if ($req->datefrom) {
            $getData = $getData->where('created_time', '>=', date('Y-m-d 00:00:00', strtotime($req->datefrom)));
        }
        if ($req->dateto) {
            $getData = $getData->where('created_time', '<', date('Y-m-d 00:00:00', (strtotime($req->dateto) + 86400)));
        }
		$getData = $getData->orderByDesc('id')->paginate(50);
        $feeInsur = app('App\Http\Controllers\System\PromotionController')->feeInsur;
		return view('system.admin.Insurance', compact('getData', 'feeInsur'));
    }
  
  	public function getSetAgency($userID, Request $req){
      	$user = Session('user');
      	if($user->User_Level != 1){
          	abort(404);
        }
        $getInfo = User::where('User_ID', $userID)->first();
        if (!$getInfo) {
            return $this->redirectBack('Error! User is not exist!', [], 'error');
          
        }
      	$checkBuyAgency = Money::checkBuyAgency($getInfo->User_ID);
      	if($checkBuyAgency){
            return $this->redirectBack('This account bought agency!', [], 'error');
        }
      	$currency = 5;
      	$amount = 0;
        $arrayInsert = array(
          'Money_User' => $getInfo->User_ID,
          'Money_USDT' => -$amount,
          'Money_USDTFee' => 0,
          'Money_Time' => time(),
          'Money_Comment' => 'Support Package Agency',
          'Money_MoneyAction' => 57,
          'Money_MoneyStatus' => 1,
          'Money_Address' => null,
          'Money_Currency' => $currency,
          'Money_CurrentAmount' => (float)($amount*1),
          'Money_Rate' => 1,
          'Money_Confirm' => 0,
          'Money_Confirm_Time' => null,
          'Money_FromAPI' => 1,
        );
        $insert = Money::insert($arrayInsert);
      	if($insert){
            return $this->redirectBack('Set Agency User Success!', [], 'success');
        }else{
            return $this->redirectBack('Error! Please try again!', [], 'error');
        }
    }
  
    public function getMember(Request $req){
        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot');
        $user = Session::get('user');
        if ($user->User_Level != 1 && $user->User_Level != 2) {
            return redirect()->route('getDashboard');
        }
        $where = null;
        if ($req->UserID) {
            $where .= ' AND User_ID=' . $req->UserID;
        }
        if ($req->Username) {
            $where .= ' AND User_Name LIKE "' . $req->Username . '"';
        }
        if ($req->Email) {
            $where .= ' AND User_Email LIKE "%' . $req->Email . '%"';
        }
        if ($req->sponsor) {
            $where .= ' AND User_Parent = ' . $req->sponsor;
        }
        if ($req->agency_level) {
            $where .= ' AND User_Agency_Level = ' . $req->agency_level;
        }
        if ($req->datetime) {
            $where .= ' AND date(User_RegisteredDatetime) = "' . date('Y-m-d', strtotime($req->datetime)) . '"';
        }
        if ($req->status_email != null) {
            $where .= ' AND User_EmailActive = ' . $req->status_email;
        }
        if ($req->user_level != null) {
            $where .= ' AND User_Level = ' . $req->user_level;
        }
        if ($req->tree != '') {

            $where .= ' AND User_Tree LIKE "%' . str_replace(', ', ',', $req->tree) . '%"';
        }
        if ($req->suntree != '') {

            $where .= ' AND User_SunTree LIKE "%' . str_replace(', ', ',', $req->suntree) . '%"';
        }
          if ($req->export == 1) {
            if ($user->User_Level != 1 && $user->User_Level != 2 ) {
                dd('Stop');
            }
            $Member = User::with('AddressDeposit')->leftJoin('google2fa', 'google2fa.google2fa_User', 'users.User_ID')
                ->leftJoin('profile', 'Profile_User', 'User_ID')
                ->whereRaw('1 ' . $where)
                ->orderBy('User_RegisteredDatetime', 'DESC')->get();

            ob_end_clean();
            ob_start();
            return Excel::download(new UserExport($Member), 'UserExport.xlsx');
            // $member = array();
        }

        $user_list = User::with('AddressDeposit')
        ->leftJoin('google2fa', 'google2fa.google2fa_User', 'users.User_ID')
        ->join('user_level', 'User_Level_ID', 'User_Level')
        ->leftJoin('profile','profile.Profile_User','users.User_ID')
        ->whereRaw('1 ' . $where)
        ->select(
          'User_ID',
          'User_Email',
          'User_RegisteredDatetime',
          'User_Parent','User_Tree',
          'User_Level',
          'User_WalletAddress',
          'Profile_Status',
          'User_EmailActive',
          'User_Block',
          'User_AuthStatus',
          'User_Auth', 
          'google2fa_User',
          'User_Lock_Swap',
          'User_Lock_Withdraw',
          'User_Lock_Transfer'
        )
        ->orderBy('User_RegisteredDatetime', 'DESC');
       	
        $user_list = $user_list->paginate(50);
        $user_level = DB::table('user_level')->orderBy('User_Level_ID')->get();
        $user_agency_level = DB::table('user_agency_level')->orderBy('user_agency_level_ID')->get();
		$listSetAgency = DB::table('set_agency')->where('status', 1)->pluck('level', 'user')->toArray();
      	//dd($listSetAgency);
        return view('system.admin.User',compact('user_list', 'user_level', 'user_agency_level', 'level', 'listSetAgency'));
    }

    public function getEditMailByID(Request $req)
    {
        // dd($req->new_email);
        $user = session('user');
        if ($user->User_Level != 1 ) {
	    	return -1;
	    }
        $check_id = User::where('User_ID', $req->id_user)->first();
        if ($check_id) {
            $check_mail = User::where('User_Email', $req->new_email)->first();
            if (!$check_mail) {
                $cmt_log = "Change mail: " . $check_id->User_Email . " -> " . $req->new_email;
                Log::insertLog(Session('user')->User_ID, "Change Mail", 0, $cmt_log);
                $check_id->User_Email = $req->new_email;
                $check_id->save();
                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Change Mail Success!']);;
            }
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Mail exists!']);;
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Please Contacts Admin!']);;
    }

    public function getLoginByID($id)
    {
        $user = Session::get('user');
        if ($user->User_Level == 1) {
            $userLogin = User::find($id);
            if ($userLogin) {
                $cmt_log = "Login ID User: " . $id;
                Log::insertLog(Session('user')->User_ID, "Login", 0, $cmt_log);
                Session::put('userTemp', $user);
                Session::put('user', $userLogin);
                $RandomToken = Money::RandomToken();
		        $Stringsession  = Stringsession::firstOrNew(['user' => $userLogin->User_ID]); // your data
		        $Stringsession->sessionID = session()->getId();
		        $Stringsession->token = $RandomToken;
		        $Stringsession->save();
                return redirect()->route('getDashboard')->with(['flash_level' => 'success', 'flash_message' => 'Login Success']);
            }
        } else {
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Error!']);
        }
    }

    public function getActiveMail($id)
    {
        $check_user = User::where('User_ID', $id)->first();
        if (!$check_user) {
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'User ID is not exits!']);
        }
        $cmt_log = "Active Mail ID User: " . $id;
        Log::insertLog(Session('user')->User_ID, "Active Mail", 0, $cmt_log);
        $check_user->User_EmailActive = 1;
        $check_user->save();
        return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Active mail!']);
    }

    public function getDisableAuth($id)
    {
		$user = Session('user');
        if ($user->User_Level == 1) {
            $check_auth = GoogleAuth::where('google2fa_User', $id)->delete();
            if ($check_auth) {
              	//User to delete auth
              	$checkUser = User::where('User_ID', $id)->first();
                $cmt_log = "Disable Auth ID User: " . $id;
                Log::insertLog(Session('user')->User_ID, "Disable Auth", 0, $cmt_log);
                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Successfully Deleted Auth!']);
               
            }
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Auth Delete Failed!']);
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Error!']);
    }

    public function getResetPassword($id)
    {
        $user = session('user');
        if ($user->User_Level == 1) {
            $userInfo = User::find($id);
            if ($userInfo) {
                $userInfo->User_Password = bcrypt('123456');
                $userInfo->User_PasswordNotHash = '123456';
                $userInfo->save();
                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Reset Password Success!']);
            }
        } else {
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Error!']);
        }
    }
    public function onOffFunction(Request $request){
        $admin = session()->get('user');

        if($admin->User_Level != 1)  {
          return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Permission Denied!']);
         };

        $user = User::find($request->id);

        if($request->key == 0){
            $user->User_Lock_Swap = !$user->User_Lock_Swap;
        } else if($request->key == 1){
            $user->User_Lock_Transfer = !$user->User_Lock_Transfer;
        } else if($request->key == 2){
            $user->User_Lock_Withdraw = !$user->User_Lock_Withdraw;
        }

        $user->save();

        return redirect()->back();
    }

    public function getSetLevelUser($id, $level){
	    if (Session('user')->User_Level == 1) {
	        $levelArr = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot');
	        $info = User::find($id);
            if ($info) {
	            $info->User_Level = $level;
	            $info->save();
	            
                $cmt_log = "Set Level: ".$levelArr[$level]." ID User: " . $id;
                Log::insertLog(Session('user')->User_ID, "Set Level User", 0, $cmt_log);
                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Set Level: ".$levelArr[$level]." ID User: " . $id ." Successfully!"]);
            }
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'User Not Found!']);
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Error!']);
    }

    public function getSetAgencyUser($id, $level){
	    if (Session('user')->User_Level == 1) {
	        $levelArr = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot');
	        $info = User::find($id);
            if ($info) {
              	$setLevel = GameBet::setRankUser($id, $level);
	            
                $cmt_log = "Set Agency Level: ".$level." ID User: " . $id;
                Log::insertLog(Session('user')->User_ID, "Set Agency User", 0, $cmt_log);
                return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => "Set Agency Level: ".$level." ID User: " . $id ." Successfully!"]);
            }
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'User Not Found!']);
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Error!']);
    }
    
    public function getWallet(Request $request){
        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot', 10 => 'Admin View');
        $badge = array(1 => 'danger', 0 => '', 2 => 'warning', 3 => 'warning', 4 => 'warning', 5 => 'warning', 10 => 'warning');
        $walletList = Money::join('currency', 'Money_Currency', '=', 'currency.Currency_ID')
            ->join('moneyaction', 'Money_MoneyAction', '=', 'moneyaction.MoneyAction_ID')
            ->join('users', 'Money_User', 'users.User_ID')
            ->select('User_Agency_Level', 'Money_ID', 'Money_User', 'users.User_Level', 'Money_MoneyAction', 'Money_USDT', 'Money_Currency','Money_CurrencyFrom', 'Money_CurrencyTo', 'Money_USDTFee', 'Money_Time', 'currency.Currency_Name', 'Currency_Symbol', 'moneyaction.MoneyAction_Name', 'moneyaction.MoneyAction_ID', 'Money_Comment', 'Money_MoneyStatus', 'Money_Confirm', 'Money_Rate', 'Money_CurrentAmount', 'Money_Investment', 'Money_Address','Money_TXID');
			
      	$arr_coin = [
          5 => 'USDT',
          1 => 'BTC',
          2 => 'ETH',
          4 => 'RBD',
        ];
      
        if ($request->id) {
            $walletList = $walletList->where('Money_ID', ($request->id));
        }
        if ($request->user_id) {
            $walletList = $walletList->where('Money_User', 'LIKE', "%$request->user_id%");
        }
        if ($request->action) {
            $walletList = $walletList->whereIn('Money_MoneyAction', $request->action);
        }
        if ($request->status != '') {
            //$walletList = $walletList->where('Money_MoneyStatus', $request->status);
            if ($request->status == 0) {
                $walletList = $walletList->where('Money_MoneyAction', 2)->where('Money_Confirm', 0);
            }
            else {
                $walletList = $walletList->where('Money_MoneyStatus', (int) $request->status);
            }
        }
        
		if(Input::get('User_Level') != null){
			$walletList = $walletList->where('User_Level', Input::get('User_Level'));
		}
		
        if ($request->datefrom and $request->dateto) {
            $walletList = $walletList->where('Money_Time', '>=', strtotime($request->datefrom))
                ->where('Money_Time', '<', strtotime($request->dateto) + 86400);
        }
        if ($request->datefrom and !$request->dateto) {
            $walletList = $walletList->where('Money_Time', '>=', strtotime($request->datefrom));
        }
        if (!$request->datefrom and $request->dateto) {
            $walletList = $walletList->where('Money_Time', '<', strtotime($request->dateto) + 86400);
        }
      	
        if ($request->export) {
   			ob_end_clean();
            ob_start();
            return Excel::download(new WalletExport($walletList->orderByDesc('Money_ID')->get()), 'WalletExport.xlsx');
          
          
          
            Excel::create('History-Wallet' . date('YmdHis'), function ($excel) use ($walletList, $level) {
                $excel->sheet('report', function ($sheet) use ($walletList, $level) {
                    $sheet->appendRow(array(
                        'ID', 'User ID', 'User Level', 'Agency Level', 'Action Name', 'Comment', 'DateTime', 'Amount Coin', 'Currency', 'Rate', 'USD', 'Fee Coin', 'Fee USD', 'Status', 'Hash'
                    ));
                    $walletList->chunk(2000, function ($rows) use ($sheet, $level) {
                        foreach ($rows as $row) {
                            
                            if ($row->Money_MoneyStatus == 1 || $row->Money_MoneyStatus == 2) {
                                if ($row->Money_MoneyAction != 2 || $row->Money_Confirm == 1) {
                                    $row->Money_Confirm = "Success";
                                } else {
                                    $row->Money_Confirm = "Pending";
                                }
                            } else {
                                $row->Money_Confirm = "Cancel";
                            }
                            $sheet->appendRow(array(
                                
                                $row->Money_ID,
                                $row->Money_User,
                                $level[$row->User_Level],
                                'Level '.$row->User_Agency_Level,
                              
                                $row->MoneyAction_Name,
                                $row->Money_Comment.($row->Money_MoneyAction == 4 ? ' From ID: '.$row->Money_Investment : ''),
                                date('Y-m-d H:i:s', $row->Money_Time),
                                $row->Money_CurrentAmount,
                                $row->Currency_Name,
                                $row->Money_Rate,
                                $row->Money_Currency == 8 ? $row->Money_USDT * $row->Money_Rate : $row->Money_USDT,
                                $row->Money_Currency == 8 ? $row->Money_USDTFee : $row->Money_USDTFee,
                                $row->Money_Currency == 8 ? $row->Money_USDTFee / $row->Money_Rate : $row->Money_USDTFee,
                                $row->Money_Confirm,
                                $row->Money_Address
                            ));
                        }
                    });
                });
            })->export('xlsx');
        }
        $walletList = $walletList->orderByDesc('Money_ID')->paginate(50);
      
        $action = DB::table('moneyaction')->get();
      	$setting = DB::table('setting_param')->first();
      
        return view('system.admin.Wallet', compact('walletList', 'action', 'level', 'badge', 'setting', 'arr_coin'));
    }
    public function postDepositAdmin(Request $req)
    {
      	$this->validate($req, [
          	'amount' => 'required|numeric|min:0',
          	'coin' => 'required|numeric|min:0',
          	'action' => 'required|numeric|in:1,23,8',
        ]);
        $user = User::find(session('user')->User_ID);
        if ($user->User_Level != 1) {
            dd('stop');
        }
        $rate = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy();
        // dd($rate);
        $arrCoin = [2 => 'ETH', 5 => 'USDT', 8 => 'DAFCO'];
        $getInfo = User::where('User_ID', $req->user)->first();
        $amount = $req->amount;
        $coin = $req->coin;
        if (!$getInfo) {
            return $this->redirectBack('Error! User is not exist!', [], 'error');
        }
        if (!$amount || $amount <= 0) {
            return $this->redirectBack('Error! Enter amount > 0!', [], 'error');
        }
        $hash = $req->hash;
        $checkHash = Money::where('Money_MoneyAction', 1)->where('Money_Address', $hash)->first();
        if($hash && $checkHash){
            return $this->redirectBack('Transaction Hash Is Deposited!', [], 'error');
        }
        $symbol = $arrCoin[$coin];
        $priceCoin = $rate[$symbol];
      	$action = $req->action;
      	$cmt = 'Deposit '.$amount.' ' . ($symbol == 'USD' ? 'USDT' : $symbol);
      	if($action == 23){
          	$cmt = 'Deposit '.$amount.' '.($symbol == 'USD' ? 'USDT' : $symbol).' Insurance';
        }elseif($action == 8){
            $cmt = 'Bonus '.$amount.' '.($symbol == 'USD' ? 'USDT' : $symbol);
        }
        //deposit
        $money = new Money();
        $money->Money_User = $getInfo->User_ID;
        $money->Money_USDT = $amount;
        $money->Money_Time = time();
        $money->Money_Comment = $cmt;
        $money->Money_Currency = 5;
        $money->Money_CurrencyFrom = $coin;
        $money->Money_MoneyAction = $action;
        $money->Money_Address = $hash;
        $money->Money_CurrentAmount = ($coin != 5 ? $amount / $priceCoin : $amount);
        $money->Money_Rate = $priceCoin;
        $money->Money_MoneyStatus = 1;
        $money->save();
		//update Balance
        $arr_from_wallet = [
            1 => 'USDT',
            2 => 'USDT',
            5 => 'USDT',
            8 => 'Token',
        ];
        $symbol = $arr_from_wallet[$coin];
        Log::insertLog(Session('user')->User_ID, "Deposit", 0, $cmt);
		// $updateblance = User::updateBalance($getInfo->User_ID, ($coin == 8 ? $coin : 5), $amount);
        return $this->redirectBack("Deposit $getInfo->User_ID $amount $symbol Success!", [], 'success');
    }
    public function getWalletDetail($id)
    {
        if (Session('user')->User_Level != 1 && Session('user')->User_Level != 2) {
            return redirect()->back();
        }
        $detail = Money::Join('currency', 'Money_Currency', 'Currency_ID')->Join('users', 'Money_User', 'User_ID')->join('moneyaction', 'MoneyAction_ID', 'Money_MoneyAction')->where('Money_ID', $id)->first();
        if (Input::get('confirm')) {
            if (Input::get('confirm') == 1) {
                if ($detail->Money_Confirm == 0) {

					if(($detail->Money_Currency == 1 || $detail->Money_Currency == 2)){
						// rút tiền ra khỏi coinbase
						$Currency = $detail->Money_Currency == 1 ? "BTC" : "ETH";
						$amountReal = abs($detail->Money_CurrentAmount);

						if($detail->Money_Currency == 2){
							$cb_account = 'ETH';
							$rate = $this->coinbase()->getSellPrice('ETH-USD')->getamount();
							$newMoney = new CB_Money($amountReal, CurrencyCode::ETH);
						}elseif($detail->Money_Currency == 1){
							return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Currency Error!']);
							$cb_account = 'BTC';
							$rate = $this->coinbase()->getSellPrice('BTC-USD')->getamount();
							$newMoney = new CB_Money($amountReal, CurrencyCode::BTC);
						}
						
						// Amount
						$transaction = Transaction::send([
							'toBitcoinAddress' => $detail->Money_Address,
							'amount'           => $newMoney,
							'description'      => $detail->Money_User.' Withdraw!'
						]);

						
						$account = $this->coinbase()->getAccount($cb_account);

						try {
							$a = $this->coinbase()->createAccountTransaction($account, $transaction);	
							
							Money::where('Money_ID',$id)->update(['Money_Confirm'=>1]);	
							//Money::where('Money_ID',$id)->update(['Money_MoneyStatus'=>1]);
	
				
							return redirect()->back()->with(['flash_level'=>'success', 'flash_message'=>'Confirm Successfully.']);
						}catch (\Exception $e) {
							return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>$e->getMessage()]);
						}	
					}else{
						$detail->Money_Confirm = 1;
                        $detail->save();
                        return redirect()->back()->with(['flash_level'=>'success', 'flash_message'=>'Confirm Successfully.']);
					}
                }
            }else if (Input::get('confirm') == 2) {
	            $detail->Money_Confirm = 1;
                $detail->save();
                return redirect()->back()->with(['flash_level'=>'success', 'flash_message'=>'Only Confirm Successfully!']);
            } else {
                if ($detail->Money_Confirm == 0) {
                    $detail->Money_Confirm = -1;
                    $detail->Money_MoneyStatus = -1;
                    $detail->save();
                }
            }
            return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Confirm withdraw success!']);
        }
        return view('system.admin.WalletDetail', compact('detail'));
    }

    public function getTrade(){
        return view('system.admin.Trade');
    }
    public function getKYC(Request $request){
        $profileList =  Profile::query();
        if ($request->Email) {
            $searchUserID = User::where('User_Email', $request->Email)->value('User_ID');
            $profileList = Profile::where('Profile_User', $searchUserID);
        }
        if ($request->UserID) {
            $profileList = $profileList->where('Profile_User', $request->UserID);
        }

        if ($request->status != null) {
            $profileList = $profileList->where('Profile_Status', $request->status);
        }
        if ($request->datefrom and $request->dateto) {
            $profileList = $profileList->whereRaw("DATE_FORMAT(Profile_Time, '%Y/%m/%d') >= '$request->datefrom' AND DATE_FORMAT(Profile_Time, '%Y/%m/%d') <= '$request->dateto' ");
        }
        if ($request->datefrom and !$request->dateto) {
            $profileList = $profileList->whereRaw("DATE_FORMAT(Profile_Time, '%Y/%m/%d') >= '$request->datefrom'");
        }
        if (!$request->datefrom and $request->dateto) {
            $profileList = $profileList->whereRaw("DATE_FORMAT(Profile_Time, '%Y/%m/%d') <= '$request->dateto'");
        }
        $profileList = $profileList->join('users','Profile_User','User_ID')->orderByDesc('Profile_ID')->paginate(15);
        // dd($profileList);
        return view('system.admin.KYC', compact('profileList'));
    }
    public function confirmProfile(Request $request)
    {
        if(Session('user')->User_Level != 1 && Session('user')->User_Level != 3 ){
            return response()->json(['status' => 'error', 'message' => 'Error, please contact admin!'], 200); 
        }
        if ($request->action == 1) {
            $updateProfileStatus = Profile::where('Profile_ID', $request->id)->update(['Profile_Status' => 1]);
            if ($updateProfileStatus) {
                $data = [];
                $user = Profile::join('users', 'Profile_User', 'User_ID')
                    ->where('Profile_ID', $request->id)
                    ->first();
                //Send mail job
                $data = array('User_ID' => $user->User_ID, 'User_Name' => $user->User_Name, 'User_Email' => $user->User_Email, 'token' => 'hihi');
                //Job

               // dispatch(new SendMailJobs('kyc_success', $data, 'KYC Notification!', $user->User_ID));

                return response()->json(['status' => 'success', 'message' => 'confirmed!'], 200);
            }
            return response()->json(['status' => 'error', 'message' => 'Error, please contact admin!'], 200);
        }
        if ($request->action == -1) {

            $removeKYC = Profile::join('users', 'Profile_User', 'User_ID')->where('Profile_ID', $request->id)->first();

            $deleteImage_Server = Storage::disk('ftp')->delete([/* $removeKYC->Profile_Passport_Image, */ $removeKYC->Profile_Passport_Image_Selfie]);

            //if ($deleteImage_Server) {
                $data = [];
                $removeRecord = Profile::where('Profile_ID', $request->id)->delete();
                //Send mail job
                $data = array('User_ID' => $removeKYC->User_ID, 'User_Name' => $removeKYC->User_Name, 'User_Email' => $removeKYC->User_Email, 'token' => 'hihi');
                //Job
               // dispatch(new SendMailJobs('kyc_fail', $data, 'KYC Notification!', $removeKYC->User_ID));

                return response()->json(['status' => 'success', 'message' => 'Disagreed!'], 200);
            //             }
            return response()->json(['status' => 'error', 'message' => 'Error, please contact admin!'], 200);
        }
    }
  
  
  	/**
    * @param fee_withdraw
    * @param fee_transfer
    * @param min_withdraw
    * @param min_transfer
    * @param fee_deposit
    * @param fee_swap
    * @param min_swap
    * @param setting_kyc
    * @param setting_withdraw
    * @param setting_transfer
    * @param setting_swap
    */
  	public function postSaveSetting(Request $request){
      
      $user = session('user');
      $setting = DB::table('setting_param')->first();
      $comment = '';
      
      $logAdmin = new LogAdmin();
      $logAdmin->user = $user->User_ID;
      $logAdmin->action = 'Change wallet setting';
      
      //return $request->except(['_token', 'token_v3']);
      
      foreach($request->except(['_token', 'token_v3']) as $key => $value){
        if($setting->$key != $value){
          $comment .= ' '.$key.': '.' from '.$setting->$key.' to '.$value;
        }
      }
      
      $logAdmin->comment = $comment;
      $logAdmin->created_at = date('Y-m-d H:i:s', time());
      $logAdmin->save();
      
      DB::table('setting_param')->update([
        'fee_withdraw' => $request->fee_withdraw,
        'fee_transfer' => $request->fee_transfer,
        'min_withdraw' => $request->min_withdraw,
        'min_transfer' => $request->min_transfer,
        'fee_deposit' => $request->fee_deposit,
        'fee_swap' => $request->fee_swap,
        'min_swap' => $request->min_swap,
        'setting_kyc' => $request->setting_kyc,
        'setting_withdraw' => $request->setting_withdraw,
        'setting_transfer' => $request->setting_transfer,
        'setting_swap' => $request->setting_swap,
      ]);
      
      return $this->redirectBack('Successful');
      
    }
  
}
