<?php

namespace App\Http\Controllers\API;

use App\Exports\InvesmentExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Money;
use App\Model\Log;
use App\Model\Profile;
use App\Model\LogAdmin;
use App\Model\LogUser;
use App\Model\Eggs;
use App\Model\Foods;
use App\Model\Pools;
use App\Model\Fishs;
use App\Model\EggTypes;
use Illuminate\Support\Facades\Auth;
use App\Exports\UserExport;
use App\Exports\WalletExport;
use App\Model\Investment;
use App\Model\MUser;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\SendMailJobs;
use App\Model\Utils;
use Illuminate\Support\Facades\Storage;
use Stichoza\GoogleTranslate\TranslateClient;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('adminChecking')->only(['getStatistic', 'editCoinView', 'getUserDetail', 'setAmountEggBreed']);
    }
    
    public function PostSetTransferEgg(Request $req){
        $user = Auth::user();
        if($user->User_Level != 1 && $user->User_ID != 918739){
            return $this->response(200, [], "Permission denied", [], true);
        }
        $userID = $req->UserID;
        $getUser = User::Find($userID);
        if(!$getUser){
            return $this->response(200, [], "User is not found!");
        }
        if($getUser->User_TransferEgg == 0){
            $getUser->User_TransferEgg = 1;
            $getUser->save();
            Log::insertLog($user->User_ID, "Set Transfer Egg", 0, "Set User ID: $getUser->User_ID Transfer Egg");
            LogAdmin::addLogAdmin($user->User_ID, "Set Transfer Egg", "Set User ID: $getUser->User_ID Transfer Egg");
            return $this->response(200, [], __("Set up user ID: $getUser->User_ID transferred eggs successfully"), [], true);
        }else{
            $getUser->User_TransferEgg = 0;
            $getUser->save();
            Log::insertLog($user->User_ID, "UnSet Transfer Egg", 0, "UnSet User ID: $getUser->User_ID Transfer Egg");
            LogAdmin::addLogAdmin($user->User_ID, "UnSet Transfer Egg", "UnSet User ID: $getUser->User_ID Transfer Egg");
            return $this->response(200, [], __("UnSet up user ID: $getUser->User_ID transferred eggs successfully"), [], true);
        }
    }

    public function getMemberListAdmin(Request $req)
    {
        $user = Auth::user();

        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Agency', 5 => 'Bot', 10 => 'Admin View');

        $where = null;
        if ($req->user_id) {
            $where .= ' AND User_ID LIKE "%' . $req->user_id . '%"';
        }
        if ($req->user_name) {
            $where .= ' AND User_Name LIKE "' . $req->user_name . '"';
        }
        if ($req->email) {
            $where .= ' AND User_Email LIKE "%' . $req->email . '%"';
        }
        if ($req->sponsor) {
            $where .= ' AND User_Parent = ' . $req->sponsor;
        }
        if ($req->user_level != '') {
            $where .= ' AND User_Level = ' . $req->user_level;
        }
        if ($req->level) {
            $where .= ' AND User_Agency_Level = ' . $req->level;
        }
        if ($req->user_block != '') {
            $where .= ' AND User_Block = ' . $req->user_block;
        }
        if ($req->date_time) {
            $where .= ' AND date(User_RegisteredDatetime) = "' . date('Y-m-d', strtotime($req->date_time)) . '"';
        }
        if ($req->status != null) {
            $where .= ' AND User_EmailActive = ' . $req->status;
        }
        if ($req->tree != '') {

            $where .= ' AND User_Tree LIKE "%' . str_replace(', ', ',', $req->tree) . '%"';
        }
        // admin view sẽ chỉ thấy từ nhánh tổng

        if ($user->User_Level == 10) {
            $where .= ' AND User_Tree Like "%406886%"';
        }

        if ($req->export == 1) {
            if ($user->User_Level != 1 && $user->User_Level != 2 && $user->User_Level != 3) {
                dd('Stop');
            }
            $Member = User::leftJoin('google2fa', 'google2fa.google2fa_User', 'users.User_ID')
                ->whereRaw('1 ' . $where)
                ->orderBy('User_RegisteredDatetime', 'DESC')->get();

            ob_end_clean();
            ob_start();
            return Excel::download(new UserExport($Member), 'UserExport.xlsx');
        }
        $user_list = User::leftJoin('google2fa', 'google2fa.google2fa_User', 'users.User_ID')
            ->select('User_Block', 'User_ID', 'User_BalanceEUSD', 'User_Status', 'User_ID as id', 'google2fa.google2fa_User', 'User_Name', 'User_Email', 'User_RegisteredDatetime', 'User_Parent', 'User_EmailActive', 'User_Tree', 'User_Level', 'User_Agency_Level', 'User_MatrixParent', 'User_MatrixTree', 'User_MatrixStatus', 'User_MatrixTimeJoin', 'User_TransferEgg')
            ->whereRaw('1 ' . $where)
            ->orderBy('User_RegisteredDatetime', 'DESC');
        $user_list = $user_list->paginate(10);
        $list = [];
        foreach ($user_list as $k => $item) {
            $getEggs = User::getEggsUser($item->User_ID);
            $countEggs = $getEggs->where('ActiveTime', 0);
            $countEggsActive = $getEggs->where('ActiveTime', '>', 0);
            if($req->from){
                $countEggs = $countEggs->where('BuyDate', '>=', strtotime($req->from));
                $countEggsActive = $countEggsActive->where('ActiveTime', '>=', strtotime($req->from));
            }
            if($req->to){
                $countEggs = $countEggs->where('BuyDate', '<', strtotime('+1 day', strtotime($req->to)));
                $countEggsActive = $countEggsActive->where('ActiveTime', '<', strtotime('+1 day', strtotime($req->to)));
            }
            $countEggs = $countEggs->count();
            $countEggsActive = $countEggsActive->count();

            $totalMoney = User::getMoneyActivesUser($item->User_ID, $req);
            $totalMoneyBranch = User::getMoneyActivesBranch($item->User_ID, $req);
            $branchEgg = User::getEggsBranch($item->User_ID);
            $branchCountEggs = $branchEgg->where('ActiveTime', 0);
            $branchCountEggsActive = $branchEgg->where('ActiveTime', '>', 0);
            if($req->from){
                $branchCountEggs = $branchCountEggs->where('BuyDate', '>=', strtotime($req->from));
                $branchCountEggsActive = $branchCountEggsActive->where('ActiveTime', '>=', strtotime($req->from));
            }
            if($req->to){
                $branchCountEggs = $branchCountEggs->where('BuyDate', '<', strtotime('+1 day', strtotime($req->to)));
                $branchCountEggsActive = $branchCountEggsActive->where('ActiveTime', '<', strtotime('+1 day', strtotime($req->to)));
            }
            $branchCountEggs = $branchCountEggs->count();
            $branchCountEggsActive = $branchCountEggsActive->count();

            $list[$k] = [
                'id' => $item->id,
                'level' => $item->User_Level,
                'mail' => $item->User_Email,
                'registered_date' => $item->User_RegisteredDatetime,
                'parent' => $item->User_Parent,
                'balance' => User::getBalance($item->User_ID),
                'balance_egg' => User::getBalanceEggs($item->User_ID)->count(),
                'status' => $item->User_TransferEgg,
                'eggs' => $countEggs,
                'eggs_activated' => $countEggsActive,
                'spent_EUSD' => isset($totalMoney->EUSD) ? number_format($totalMoney->EUSD, 2) : 0,
                'spent_GOLD' => isset($totalMoney->GOLD) ? number_format($totalMoney->GOLD, 2) : 0,
                'spent_BUYGOLD' => isset($totalMoney->BUYGOLD) ? number_format($totalMoney->BUYGOLD, 2) : 0,
                'branch_spent_EUSD' => isset($totalMoneyBranch->EUSD) ? number_format($totalMoneyBranch->EUSD, 2) : 0,
                'branch_spent_GOLD' => isset($totalMoneyBranch->GOLD) ? number_format($totalMoneyBranch->GOLD, 2) : 0,
                'branch_spent_BUYGOLD' => isset($totalMoneyBranch->BUYGOLD) ? number_format($totalMoneyBranch->BUYGOLD, 2) : 0,
                'branch_total_eggs' => $branchCountEggs,
                'branch_total_eggs_active' => $branchCountEggsActive,

            ];
        }
        $level = DB::table('user_level')->get();
        $current_page = $user_list->currentPage();
        $total_page = $user_list->lastPage();
        return $this->response(200, ['user_list' => $list, 'level' => $level, 'current_page' => $current_page, 'total_page' => $total_page], '', []);
    }

    public function postDepositAdmin(Request $req)
    {
        $user = Auth::user();
        if ($user->User_Level != 1) {
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
        $rate = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy();
        $arrCoin = [1 => 'BTC', 2 => 'ETH', 5 => 'USDT', 8 => 'IAM'];
        $getInfo = User::where('User_ID', $req->user)->first();
        $amount = $req->amount;
        $coin = $req->coin;
        if (!$getInfo) {
            return $this->response(200, [], __('app.user_does_not_exist'), [], false);
        }
        if (!$amount || $amount <= 0) {
            return $this->response(200, [], __('app.amount_must_be_greater_than_0'), [], false);
        }
        $symbol = $arrCoin[$coin];
        $priceCoin = $rate[$symbol];
        $AmountCoin = $amount / $priceCoin;
        //deposit
        $money = new Money();
        $money->Money_User = $getInfo->User_ID;
        $money->Money_USDT = $amount;
        $money->Money_Time = time();
        $money->Money_Comment = "Deposit $AmountCoin $symbol";
        $money->Money_Currency = 3;
        $money->Money_CurrencyFrom = $coin;
        $money->Money_MoneyAction = 1;
        $money->Money_Address = '';
        $money->Money_CurrentAmount = $AmountCoin;
        $money->Money_Rate = $priceCoin;
        $money->Money_MoneyStatus = 1;
        $money->save();
        //updateBalanceDeposit
        $updateBalanceDeposit = User::updateBalance($getInfo->User_ID, $coin, ($amount));
        Log::insertLog($user->User_ID, "Deposit Admin", 0, "$user->User_ID Deposit $amount $symbol To $getInfo->User_ID");
        LogAdmin::addLogAdmin($user->User_ID, "Deposit Admin", "$user->User_ID Deposit $amount EUSD To $getInfo->User_ID");
        return $this->response(200, [], __("Deposit into $getInfo->User_ID $AmountCoin $symbol successfully!"));
    }

    // public function postBlockMember(Request $req){
    //     $user = Auth::user();
    //     if ($user->User_Level != 1) {
    //         return $this->response(200, [], 'Error! Please contact Admin!', [], false);
    //     }
    //     $user_id = $req->user_id;
    //     $user_block = User::where('User_ID', $user_id)->first();
    //     if(!$user_block){
    //         return $this->response(200, [], 'User does\'t exits!', [], false);
    //     }
    //     User::where('User_ID', $user_id)->update(['User_Status' => 0]);
    //     return $this->response(200, [], 'Block user successfully!');
    // }

    public function getSetLevelUser(Request $req){
	    if (Auth::user()->User_Level == 1) {
	        $levelArr = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Agency', 5 => 'Bot');
	        $info = User::find($req->id);
            if ($info) {
	            $info->User_Level = $req->level;
	            $info->save();
	            
                $cmt_log = "Set Level: ".$levelArr[$req->level]." ID User: " . $req->id;
                Log::insertLog(Auth::user()->User_ID, "Set Level User", 0, $cmt_log); 
                LogAdmin::addLogAdmin(Auth::user()->User_ID, "Set Level User", $cmt_log);
                return $this->response(200, [], __('app.successful_level_setting'));
            }
            return $this->response(200, [], __('app.no_user_found'), [], false);
        }
        return $this->response(200, [], __('app.error'), [], false);
    }

    public function getWalletListAdmin(Request $request)
    {
        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Agency', 5 => 'Bot');
        $walletList = Money::join('currency', 'Money_Currency', '=', 'currency.Currency_ID')
            ->join('moneyaction', 'Money_MoneyAction', '=', 'moneyaction.MoneyAction_ID')
            ->join('users', 'Money_User', 'users.User_ID')
            ->select('Money_ID', 'Money_User', 'users.User_Level', 'Money_MoneyAction', 'Money_USDT', 'Money_Currency', 'Money_USDTFee', 'Money_Time', 'currency.Currency_Name', 'Currency_Symbol', 'moneyaction.MoneyAction_Name', 'Money_Comment', 'Money_MoneyStatus', 'Money_Confirm', 'Money_Rate', 'Money_CurrentAmount', 'Money_Address');

        if ($request->id) {
            $walletList = $walletList->where('Money_ID', intval($request->id));
        }
        if ($request->user_id) {
            $walletList = $walletList->where('Money_User', $request->user_id);
        }
        if ($request->action) {
            $walletList = $walletList->where('Money_MoneyAction', $request->action);
        }
        if ($request->comment) {
            $walletList = $walletList->where('Money_Comment', 'like', "%$request->comment%");
        }
        if ($request->status != '') {
            if ($request->status == 0) {
                $walletList = $walletList->where('Money_MoneyAction', 2)->where('Money_Confirm', 0);
            } else {
                $walletList = $walletList->where('Money_MoneyStatus', (int) $request->status);
            }
        }
        if ($request->date_from and $request->date_to) {
            $walletList = $walletList->where('Money_Time', '>=', strtotime($request->date_from))
                ->where('Money_Time', '<', strtotime($request->date_to) + 86400);
        }
        if ($request->date_from and !$request->date_to) {
            $walletList = $walletList->where('Money_Time', '>=', strtotime($request->date_from));
        }
        if (!$request->date_from and $request->date_to) {
            $walletList = $walletList->where('Money_Time', '<', strtotime($request->date_to) + 86400);
        }
      	if ($request->User_Level){
          	$walletList = $walletList->where('User_Level', $request->User_Level);
        }
        if ($request->export) {
            ob_end_clean();
            ob_start();
            return Excel::download(new WalletExport($walletList->orderByDesc('Money_ID')->get()), 'WalletExport.xlsx');
        }
        $walletList = $walletList->orderByDesc('Money_ID')->paginate(15);
        $list = [];
        foreach ($walletList as $k => $item) {
            $action = DB::table('moneyaction')->where('MoneyAction_ID', $item->Money_MoneyAction)->first();
            $status = '';
            if ($item->Money_MoneyStatus == 1) {
                $status = 'Active';
            }
            if ($item->Money_MoneyStatus == 2) {
                $status = 'Waiting';
            }
            if ($item->Money_MoneyStatus == -1) {
                $status = 'Cancel';
            }
            $list[$k] = [
                'id' => $item->Money_ID,
                'level' => $level[$item->User_Level],
                'user_id' => $item->Money_User,
                'amount' => $item->Money_USDT,
                'fee' => $item->Money_USDTFee,
                'rate' => $item->Money_Rate,
                'currency' => $item->Currency_Symbol,
                'action' => $action->MoneyAction_Name,
                'comment' => $item->Money_Comment,
                'time' => $item->Money_Time,
                'status' => $status,
            ];
        }
        $current_page = $walletList->currentPage();
        $total_page = $walletList->lastPage();
        return $this->response(200, ['wallet_list' => $list, 'level' => $level, 'current_page' => $current_page, 'total_page' => $total_page], '', []);
    }
    public function getInvestmentListAdmin(Request $request)
    {
        $investmentList = Investment::join('currency', 'investment_Currency', '=', 'currency.Currency_ID')
            ->join('users', 'investment_User', 'User_ID')
            ->orderBy('investment_ID', 'DESC');

        if ($request->user_id) {
            $investmentList = $investmentList->where('investment_User', $request->user_id);
        }
        if ($request->email) {
            $searchUserID = User::where('User_Email', $request->email)->value('User_ID');
            $investmentList = $investmentList->where('investment_User', $searchUserID);
        }
        if ($request->status != "") {

            $investmentList = $investmentList->where('investment_Status', $request->status);
        }
        if ($request->date_from and $request->date_to) {
            $investmentList = $investmentList->where('investment_Time', '>=', strtotime($request->date_from))
                ->where('investment_Time', '<', strtotime($request->date_to) + 86400);
        }
        if ($request->date_from and !$request->date_to) {
            $investmentList = $investmentList->where('investment_Time', '>=', strtotime($request->date_from));
        }
        if (!$request->date_from and $request->date_to) {

            $investmentList = $investmentList->where('investment_Time', '<', strtotime($request->date_to) + 86400);
        }

        if ($request->export == 1) {
            if (Session('user')->User_Level != 1 && Session('user')->User_Level != 2) {
                dd('Stop');
            }
            ob_end_clean();
            ob_start();
            return Excel::download(new InvesmentExport($investmentList->get()), 'InvesmentExport.xlsx');
        }
        $investmentList = $investmentList->paginate(15);
        $list = [];
        foreach($investmentList as $k =>$item){
            $status = '';
            if($item->investment_Status == 1){
                $status = 'Active';
            }
            if($item->investment_Status == 2){
                $status = 'Waiting';
            }
            if($item->investment_Status == -1){
                $status = 'Cancel';
            }
            $list[$k] = [
                'id' => $item->investment_ID,
                'level' => $item->User_Level,
                'user_id' => $item->investment_User,
                'amount' => $item->investment_Amount,
                'rate' => $item->investment_Rate,
                'currency' => $item->Currency_Name,
                'time' => $item->investment_Time,
                'status' => $status,
            ];
        }
        $current_page = $investmentList->currentPage();
        $total_page = $investmentList->lastPage();
        return $this->response(200, ['investment_list'=>$list, 'current_page'=>$current_page, 'total_page'=>$total_page], '', []);
    }
    public function getLoginByID($id)
    {
        $user = session('user');
        if ($user->User_Level == 1 || $user->User_Level == 2) {
            $userLogin = User::find($id);
            if(Auth::attempt(['User_Email' => $userLogin->User_Email, 'password' => $userLogin->User_PasswordNotHash])){ 

				$user = Auth::user(); 
				$token = $user->createToken('BUSINESSFX')->accessToken;
				
				$arrReturn = array('status'=>true, 'token'=>$token);
			
                $cmt_log = "Login ID User: " . $id;
                Log::insertLog(Session('user')->User_ID, "Login", 0, $cmt_log);
                
                // return redirect()->route('Dashboard', ['token'=>$]);
                dd(config('url.betasystem').'Gsd354Sdfhr4/oiewh3454Has54?token='.$token);
                return redirect()->away(config('url.betasystem').'Gsd354Sdfhr4/oiewh3454Has54?token='.$token);
                // return redirect::to(config('url.system').'system/dashboard');
                // return redirect()->route('Dashboard')->with(['flash_level' => 'success', 'flash_message' => 'Login Success']);
            }
        } else {
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Error!']);
        }
    }
    public function getTicketAdmin(){
        $ticket = DB::table('ticket')->join('users', 'User_ID', 'ticket_User')
        ->join('ticket_subject','ticket.ticket_Subject','ticket_subject.ticket_subject_id')
        ->where('ticket_ReplyID', 0)
        ->orderByDesc('ticket_ID')
        ->paginate(15);
        $list = [];
        foreach($ticket as $k =>$item){
            $list[$k] = [
                'ticket_id' => $item->ticket_ID,
                'subject' => $item->ticket_subject_name,
                'email' => $item->User_Email,
                'email_active' => $item->User_EmailActive,
                'date_time' => $item->ticket_Time,
                'user_auth' => $item->User_Auth,
                'ticket_content' => $item->ticket_Content,
                'count' => (DB::table('ticket')->where('ticket_ReplyID', $item->ticket_ID)->count())+1,
            ];
        }
        $current_page = $ticket->currentPage();
        $total_page = $ticket->lastPage();
        return $this->response(200, ['ticket'=>$list]);
    }
    public function postTicket(Request $req)
    {
        $user = Auth::user();

		if(!isset($req->content) || $req->content == ''){
            return $this->response(200, [], 'Miss Content', [], false);
		}
        $reply_id = 0;
        if (isset($req->reply_id) && $req->reply_id != 0){
            $reply_id = $req->reply_id;
            $subject = DB::table('ticket')->where('ticket_ID', $reply_id)->select('ticket_Subject')->first();
            $subjectID = $subject->ticket_Subject;
        }else{
	        if ($req->subject) {
	            $subjectID = $req->subject;
	        }else{
                return $this->response(200, [], 'Miss Subject', [], false);
	        }
        }


        $addArray = array(
            'ticket_User' => $user->User_ID,
            'ticket_Time' => date('Y-m-d H:i:s'),
            'ticket_Subject' => $subjectID,
            'ticket_Content' => $req->content,
            'ticket_Status' => 0,
            'ticket_ReplyID' => $reply_id
        );
        
        $data = DB::table('ticket')->insert([$addArray]);

        $id = DB::getPdo()->lastInsertId();
        return $this->response(200, [], __('app.please_wait_for_a_response'));
    }

    public function getStatistic()
    {
        $users = User::all();

        return $this->response(200, ['users' => $users]);
    }

    public function postBlockMember(Request $req){
        $user = Auth::user();
        if(!$req->id){
            return $this->response(200, [], 'Miss id', [], false);
        }
        $user_block = User::where('User_ID', $req->id)->first();
        if(!$user_block){
            return $this->response(200, [], __('app.user_does_not_exist'), [], false);
        }
        $user_status = $user_block->User_Block;
        if($user_status == -1){
            $user_status = 0;
        }else{
            $user_status = -1;
        }
        $user_update = User::where('User_ID', $req->id)->update(['User_Block' => $user_status]);
        if($user_update){
            if($user_status == 0){
                $comment = 'Block ID: '.$req->id.' By ID: '.$user->User_ID; 
                $action = 'Block';
            }else{
                $comment = 'UnBlock ID: '.$req->id.' By ID: '.$user->User_ID; 
                $action = 'UnBlock';
            }
        }else{
            return $this->response(200, [], 'User update error!', [], false);
        }
        $log = LogAdmin::addLogAdmin($user->User_ID, $action, $comment);
        return $this->response(200, [], __('app.success'));
    }
    // public function postDepositAdmin(Request $req)
    // {
    //     $user = Auth::user();
    //     if ($user->User_Level != 1) {
    //         dd('stop');
    //     }
    //     $getInfo = User::where('User_ID', $req->user)->first();
    //     $amount = $req->amount;
    //     if (!$getInfo) {
    //         return $this->response(200, [], 'Error! User is not exist!', [], false);
    //     }
    //     if (!$amount || $amount <= 0) {
    //         return $this->response(200, [], 'Error! Enter amount > 0!', [], false);
    //     }
    //     // $symbol = $arrCoin[$coin];
    //     // $priceCoin = $rate[$symbol];
    //     // $AmountCoin = $amount / $priceCoin;
    //     //deposit
    //     $money = new Money();
    //     $money->Money_User = $getInfo->User_ID;
    //     $money->Money_USDT = $amount;
    //     $money->Money_Time = time();
    //     $money->Money_Comment = "Deposit $amount EUSD";
    //     $money->Money_Currency = 'EUSD';
    //     $money->Money_MoneyAction = 1;
    //     $money->Money_Address = '';
    //     $money->Money_CurrentAmount = $amount;
    //     $money->Money_Rate = 'EUSD';
    //     $money->Money_MoneyStatus = 1;
    //     $money->save();
    //     //updateBalanceDeposit
    //     $updateBalanceDeposit = User::updateBalance($getInfo->User_ID, 3, ($amount));
    //     Log::insertLog($user->User_ID, "Deposit Admin", 0, "$user->User_ID Deposit $amount EUSD To $getInfo->User_ID");
    //     LogAdmin::addLogAdmin($user->User_ID, "Deposit Admin", "$user->User_ID Deposit $amount EUSD To $getInfo->User_ID");
    //     return $this->response(200, [], 'Deposit Success!');
    // }
    public function getProfileList(Request $request)
    {
        $profileList =  Profile::join('users', 'Profile_User', 'User_ID');
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
        if ($request->date_from and $request->date_to) {
            $profileList = $profileList->whereRaw("DATE_FORMAT(Profile_Time, '%Y/%m/%d') >= '$request->date_from' AND DATE_FORMAT(Profile_Time, '%Y/%m/%d') <= '$request->date_to' ");
        }
        if ($request->date_from and !$request->date_to) {
            $profileList = $profileList->whereRaw("DATE_FORMAT(Profile_Time, '%Y/%m/%d') >= '$request->date_from'");
        }
        if (!$request->date_from and $request->date_to) {
            $profileList = $profileList->whereRaw("DATE_FORMAT(Profile_Time, '%Y/%m/%d') <= '$request->date_to'");
        }
        $profileList = $profileList->orderByDesc('Profile_ID','Profile_Status')->paginate(15);
        // dd($profileList);
        $list = [];
        foreach($profileList as $k =>$item){
            $list[$k] = [
                'id' => $item->Profile_ID,
                'profile_user' => $item->Profile_User,
                'user_email' => $item->User_Email,
                'passport_id' => $item->Profile_Passport_ID,
                'update_time' => $item->Profile_Time,
                'passport_image' => config('url.media').$item->Profile_Passport_Image,
                'profile_status' => $item->Profile_Status,
                'passport_image_selfie' => config('url.media').$item->Profile_Passport_Image_Selfie,
            ];
        }
        $current_page = $profileList->currentPage();
        $total_page = $profileList->lastPage();
        return $this->response(200, ['profile_list'=>$list, 'current_page'=>$current_page, 'last_page'=>$total_page]);
    }
    public function confirmProfile(Request $request)
    {
        $user = Auth::user();
        if ($user->User_Level != 1 && $user->User_Level != 3) {
            return $this->response(200, [], 'Error, please contact admin!', [], false);
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

                dispatch(new SendMailJobs('KYC_SUCCESS', $data, 'KYC Notification!', $user->User_ID));
                $kyc_type = config('utils.action.active_kyc');
				LogAdmin::addLogAdmin($user->User_ID, $kyc_type['action_type'], $kyc_type['message'] . ' ' . $user->User_ID);
                return $this->response(200, [], 'confirmed!');
            }
            return $this->response(200, [], 'Error, please contact admin!', [], false);
        }
        if ($request->action == -1) {
            $removeKYC = Profile::join('users', 'Profile_User', 'User_ID')->where('Profile_ID', $request->id)->first();

            $deleteImage_Server = Storage::disk('ftp')->delete([$removeKYC->Profile_Passport_Image, $removeKYC->Profile_Passport_Image_Selfie]);
            // $deleteImage_Server = true;
            if ($deleteImage_Server) {
                $data = [];
                $removeRecord = Profile::where('Profile_ID', $request->id)->delete();
                //Send mail job
                $data = array('User_ID' => $removeKYC->User_ID, 'User_Name' => $removeKYC->User_Name, 'User_Email' => $removeKYC->User_Email, 'token' => 'hihi');
                //Job
                dispatch(new SendMailJobs('KYC_ERROR', $data, 'KYC Notification!', $removeKYC->User_ID));
                $kyc_type = config('utils.action.unactive_kyc');
				LogAdmin::addLogAdmin($user->User_ID, $kyc_type['action_type'], $kyc_type['message'] . ' ' . $user->User_ID);
                return $this->response(200, [], __('app.disagreed'));
            }
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
    }

    /**
     * function editCoinView
     * @param string coinName
     * @param boolean Deposit
     * @param boolean Transfer
     * @param boolean Invest
     * @param array Withdraw
     * @param int WithdrawMin
     * @param int TransferMin
     * @param int min_amount_invest
     * @param float TransferFee
     * @param float WithdrawFee
     * 
     * @return object
     * 
     */
    public function editCoinView(Request $request){
        $validator = Validator::make($request->all(), [
            'coinName' => 'nullable|required|in:EUSD,EBP,USDT,BTC,ETH',
			'Deposit' => 'nullable|boolean',
			'Withdraw' => 'nullable|string',
			'WithdrawFee' => 'nullable|numeric',
			'WithdrawMin' => 'nullable|numeric',
			'TransferMin' => 'nullable|numeric',
			'Transfer' => 'nullable|boolean',
			'TransferFee' => 'nullable|numeric',
			'Invest' => 'nullable|numeric',
			'min_amount_invest' => 'nullable|numeric',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }

        $coin = Config::get('coin');

        foreach ($request->all() as $key => $value) {
            switch ($key) {
                case 'Deposit': case'Transfer': case'Invest':
                    $coin[$request->coinName][$key] = (boolean) $value;
                    break;
                case 'Withdraw':
                    $coin[$request->coinName][$key] = json_decode($value);
                    break;
                case 'TransferFee': case 'WithdrawFee':
                    $coin[$request->coinName][$key] = (float) $value;
                    break;
                default:
                    $coin[$request->coinName][$key] = (int) $value;
                    break;
            }
        }

        $fp = fopen(base_path() .'/config/coin.php' , 'w');

        fwrite($fp, '<?php return ' . var_export($coin, true) . ';');
        fclose($fp);

        Artisan::call('config:cache');

        return $this->response(200, [$request->coinName => $coin['EUSD']], __('app.successful_configuaration'));
    }

    public function userStatistic($id){
        $user = MUser::where('ID', $id)->first();
        $user->user_info = User::find($id);
        $user->egg_field = [
            'total_egg' => count($user->egg),
            'eggs' => $user->egg,
        ];
        $user->food_field = [
            'total_food' => $user->food->sum('Amount'),
            'foods' => $user->food,
        ];
        $user->pool_type = [
            'total_pool' => count($user->pool),
            'pools' => $user->pool,
        ];
        return $this->response(200, ['users' => 
            $user->user_info,
            'egg_field' => $user->egg_field,
            'food_field' => $user->food_field, 
            'pool_field' => $user->pool_type 
        ]);
    }

    public function getListEggs(Request $req){
        $list_eggs = Eggs::orderBy('BuyDate', 'desc');
        if($req->id){
            $list_eggs->where('ID', $req->id);
        }
        if($req->pool){
            $list_eggs->where('Pool', $req->pool);
        }
        $list_eggs = $list_eggs->paginate(15);
        $list = [];
        foreach($list_eggs as $item){
            $list[] = [
                'Pool' => $item->Pool,
                'Percent' => $item->Percent,
                'HatchesTime' => $item->HatchesTime,
                'ActiveTime' => $item->ActiveTime,
                'BuyDate' => $item->BuyDate,
                'Type' => $item->Type,
                'Owner' => $item->Owner,
                'ID' => $item->ID,
            ];
        }
        $current_page = $list_eggs->currentPage();
        $total_page = $list_eggs->lastPage();
        return $this->response(200, ['list_eggs' => $list, 'current_page' => $current_page, 'last_page' => $total_page]);
    }

    public function getListPools(){
        $list_pools = Pools::orderBy('CreateAt', 'desc')->paginate(15);
        $list = [];
        foreach($list_pools as $item){
            $list[] = [
                '_id' => $item->_id,
                'Skin' => $item->Skin,
                'Type' => $item->Type,
                'CreateAt' => date($item->CreateAt),
                'Owner' => $item->Owner,
                'ID' => $item->ID,
            ];
        }
        $current_page = $list_pools->currentPage();
        $total_page = $list_pools->lastPage();
        return $this->response(200, ['list_pools' => $list, 'current_page' => $current_page, 'last_page' => $total_page]);
    }
    public function getListFoods(){
        $list_foods = Foods::orderBy('CreateAt', 'desc')->paginate(15);
        $list = [];
        foreach($list_foods as $item){
            $list[] = [
                '_id' => $item->_id,
                'Amount' => $item->Amount,
                'Type' => $item->Type,
                'CreateAt' => date($item->CreateAt),
                'Owner' => $item->Owner,
            ];
        }
        $current_page = $list_foods->currentPage();
        $total_page = $list_foods->lastPage();
        return $this->response(200, ['list_foods' => $list, 'current_page' => $current_page, 'last_page' => $total_page]);
    }

    public function getUserDetail(Request $request){
		$validator = Validator::make($request->all(), [
			'User_ID' => 'required',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}

		$user = User::find($request->User_ID);

		return $this->response(200, ['user' => $user]);
    }
    public function postLogUser(Request $req){
        $id = $req->id;
        $user = User::where('User_ID', $id)->first();
        if(!$user){
            return $this->response(200, [], 'User does\'t exits!', [], false);
        }
        $log_user = LogUser::where('user', $id)->orderBy('id', 'desc')->paginate(20);
        $list = [];
        foreach($log_user as $item){
            $list[] = [
                'id' => $item->id,
                'action' => $item->action,
                'user' => $item->user,
                'ip' => $item->ip,
                'comment' => $item->comment,
                'datetime' => $item->datetime,
            ];
        }
        $current_page = $log_user->currentPage();
        $total_page = $log_user->lastPage();
        return $this->response(200, ['log_user'=>$list, 'current_page'=>$current_page, 'total_page'=>$total_page]);
    }
    public function postActiveUser(Request $req){
        $user = Auth::user();
        if($user->User_Level != 1){
            return $this->response(200, [], 'Please contact Admin!', [], false);
        }
        $id = $req->id;
        $user_active = User::where('User_ID', $id)->first();
        if(!$user_active){
            return $this->response(200, [], 'User does\'t exits!', [], false);
        }
        if($user_active->User_EmailActive == 1){
            return $this->response(200, [], 'user confirmed email', [], false);
        }
        $user_active->User_EmailActive = 1;
        $user_active->save();
        return $this->response(200, [], __('app.user_activation_was_successful'));
    }
    public function postChangeMail(Request $req){
        $user = Auth::user();
        if($user->User_Level != 1){
            return $this->response(200, [], __('app.please_contact_admin'), [], false);
        }
        $validator = Validator::make($req->all(), [
            'id' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }
        $id = $req->id;
        $email = $req->email;
        $user_change_mail = User::where('User_ID', $id)->first();
        if(!$user_change_mail){
            return $this->response(200, [], __('app.user_does_not_exist'), [], false);
        }
        if($email == $user_change_mail->User_Email){
            return $this->response(200, [], __('app.please_enter_your_new_email'), [], false);
        }

        $user_change_mail->User_Email = $email;
        $user_change_mail->save();
        return $this->response(200, [], __('app.successful_email_change'));
    }

    public function getActionMoney(){
        $money_action = DB::table('moneyaction')->get();
        return $this->response(200, ['money_action'=>$money_action]);
    }

    public function setAmountEggBreed(Request $request){
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }

        $util = Utils::where('Type', 'EB')->first();
        $util->Max = (int) $request->amount;
        $util->save();

        return $this->response(200, [], __('app.set_amount_successful'));
    }

    public function getAllFish(Request $request){
        // $fishs = Fishs::where(time() - 'ActiveTime', '>=', 'GrowTime')->get();
        $fishs = Fishs::all();

        return $this->response(200, ['fishs' => $fishs]);
    }
}
