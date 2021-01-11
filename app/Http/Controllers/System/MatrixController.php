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
use App\Model\Money;
use App\Model\Investment;

use App\Jobs\SendTelegramJobs;
class MatrixController extends Controller
{
    public function getMatrix(Request $req){
		$RandomToken = Money::RandomToken();
		$userID = $req->userID ?? 123123;
	    $user = User::find($userID);
// 	    $parentTreeMatrix = User::whereRaw('User_ID In ("'.$user->User_Tree.'")')->whereNotNull('User_MatrixTree')->orderByDesc('User_RegisteredDatetime')->first();
// 	    if(!$parentTreeMatrix){
// 		    $parentTreeMatrix = User::find(Session('user')->User_ID);
			$parentTreeMatrix = $user;
// 	    }
//dd($user);
	    $list = array(
			'id' => $parentTreeMatrix->User_ID,
            'name' => '',
            'title' => $parentTreeMatrix->User_ID,
            'children' => $this->buildTree($parentTreeMatrix->User_ID),
            'className' => 'node-tree '.strtoupper($parentTreeMatrix->User_Name),
        );
        $list = json_encode($list);
        $info = User::getMatrixInfo($user);
		$arrPercent = [1=>0.2, 2=>0.03, 3=>0.03, 4=>0.03, 5=>0.03, 6=>0.02, 7=>0.02, 8=>0.02, 9=>0.02, 10=>0.02];
		$getLastedActive = Money::select('Money_Time')
								->where('Money_User', $user->User_ID)
								->whereIn('Money_MoneyAction', [18,23])
								->where('Money_MoneyStatus', 1)
								->orderByDesc('Money_Time')
								->value('Money_Time');
	    return view('System.Matrix.Index',compact('user', 'list', 'info', 'arrPercent', 'getLastedActive', 'RandomToken'));
    }
    
    public function postDepositMatrix(Request $req){
        //check spam
        $checkSpam = DB::table('string_token')->where('User', Session('user')->User_ID)->where('Token', $req->CodeSpam)->first();
        
        if($checkSpam == null){
            //khoong toonf taij
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Misconduct!']);
        }
        else{
            DB::table('string_token')->where('User', Session('user')->User_ID)->delete();
        }

	    $this->validate($req, [
		    'amount' => 'required|numeric|nullable|min:0'
	    ]);
	    $user = User::find(Session('user')->User_ID);
	    $amount = $req->amount;
	    if($amount <= 0){
		    return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Deposit amount incorrect!']);
	    }
	    $currencyFrom = 5;
	    $currencyTo = 10;
	    $balance = User::getBalance($user->User_ID, $currencyFrom);
	    if(!$balance || $amount > $balance){
		    return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Your balance isn\'t enough!']);
	    }
	    $updateBalance = User::updateBalance($user->User_ID, $currencyFrom, -$amount);
	    $updateBalance = User::updateBalance($user->User_ID, $currencyTo, $amount);
	    $moneyArray[] = array(
		    'Money_User' => $user->User_ID,
		    'Money_USDT' => -$amount,
		    'Money_USDTFee' => 0,
		    'Money_Time' => time(),
			'Money_Comment' => 'Deposit To Matrix System',
			'Money_MoneyAction' => 21,
			'Money_MoneyStatus' => 1,
            'Money_Rate' => 1,
            'Money_CurrentAmount' => $amount,
			'Money_Currency' => $currencyFrom
        );
        $moneyArray[] = array(
		    'Money_User' => $user->User_ID,
		    'Money_USDT' => $amount,
		    'Money_USDTFee' => 0,
		    'Money_Time' => time(),
			'Money_Comment' => 'Deposit To Matrix System',
			'Money_MoneyAction' => 21,
			'Money_MoneyStatus' => 1,
            'Money_Rate' => 1,
            'Money_CurrentAmount' => $amount,
			'Money_Currency' => $currencyTo
        );
        $insertMoney = Money::insert($moneyArray);
        return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Deposit '.$amount.' USDT Success!']);
    }
    
    public function postWithdrawMatrix(Request $req){
        //check spam
        $checkSpam = DB::table('string_token')->where('User', Session('user')->User_ID)->where('Token', $req->CodeSpam)->first();
        
        
        if($checkSpam == null){
            //khoong toonf taij
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Misconduct!']);
        }
        else{
            DB::table('string_token')->where('User', Session('user')->User_ID)->delete();
        }

	    $this->validate($req, [
		    'amount' => 'required|numeric|nullable|min:0'
	    ]);
	    $user = User::find(Session('user')->User_ID);
	    $amount = $req->amount;
	    if($amount <= 0){
		    return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Withdraw amount incorrect!']);
	    }
	    $currencyFrom = 10;
	    $currencyTo = 5;
	    $minBalanceMatrix = 50;
	    $balance = User::getBalance($user->User_ID, $currencyFrom);
/*
	    if(!$balance || $balance <= $minBalanceMatrix){
		    return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Your retained system $50 to reinvest matrix!']);
	    }
*/
	    if($amount > $balance){
		    return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Your balance isn\'t enough!']);
	    }
	    $updateBalance = User::updateBalance($user->User_ID, $currencyFrom, -$amount);
	    $updateBalance = User::updateBalance($user->User_ID, $currencyTo, $amount);
	    $moneyArray[] = array(
		    'Money_User' => $user->User_ID,
		    'Money_USDT' => -$amount,
		    'Money_USDTFee' => 0,
		    'Money_Time' => time(),
			'Money_Comment' => 'Withdraw To Main Balance',
			'Money_MoneyAction' => 22,
			'Money_MoneyStatus' => 1,
            'Money_Rate' => 1,
            'Money_CurrentAmount' => $amount,
			'Money_Currency' => $currencyFrom
        );
        $moneyArray[] = array(
		    'Money_User' => $user->User_ID,
		    'Money_USDT' => $amount,
		    'Money_USDTFee' => 0,
		    'Money_Time' => time(),
			'Money_Comment' => 'Withdraw From Matrix System',
			'Money_MoneyAction' => 22,
			'Money_MoneyStatus' => 1,
            'Money_Rate' => 1,
            'Money_CurrentAmount' => $amount,
			'Money_Currency' => $currencyTo
        );
        $insertMoney = Money::insert($moneyArray);
        return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Withdraw '.$amount.' USDT Success!']);
    }
    
    public function postJoinTree(Request $req){
        //check spam
        $checkSpam = DB::table('string_token')->where('User', Session('user')->User_ID)->where('Token', $req->CodeSpam)->first();
        
        
        if($checkSpam == null){
            //khoong toonf taij
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Misconduct!']);
        }
        else{
            DB::table('string_token')->where('User', Session('user')->User_ID)->delete();
        }

	    $userID = Session('user')->User_ID;
/*
	    if($req->userID){
		    $userID = $req->userID;
	    }
*/
// 	    dd(Session('user')->User_ID, $userID, $req->parent);
	    $user = User::find($userID);
		if($user->User_MatrixTree){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'You have joined the matrix system!']);
		}
	    if($req->parent){
			$parentID = $req->parent;
	    }else{
			$parentID = 'DAF1213687';
			if($user->User_Level == 5){
				$parentID = 'DAF3177604';
			}
	    }
	    $coinBalance = 5;
	    $amount = 50;
	    $balance = User::getBalance($user->User_ID, $coinBalance);
	    if(!$balance || $amount > $balance){
		    return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Your balance isn\'t enough!']);
	    }
	    $parent = User::find($parentID);
	    if(!$parent->User_MatrixTree){
			return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Your referrer has not yet joined the matrix system!']);
		}
	    //xét cây Matrix 3 chân
	    $getIDANDNode = $this->getUserIDMissBranch($parentID);
	    if(!$getIDANDNode){
		    return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Sponsor ID does not exist']);
	    }
// 	    dd($getIDANDNode);
	    $presenterID = $getIDANDNode['user_id'];
	    $presenter = User::find($presenterID);
	    $node = $getIDANDNode['node_miss'];
	    
	    $updateBalance = User::updateBalance($user->User_ID, $coinBalance, -$amount);
	    
	    if($updateBalance != $user->User_BalanceUSDT){
		    $moneyArray[] = array(
			    'Money_User' => $user->User_ID,
			    'Money_USDT' => -$amount,
			    'Money_USDTFee' => 0,
			    'Money_Time' => time(),
				'Money_Comment' => 'Join Matrix System',
				'Money_MoneyAction' => 18,
				'Money_MoneyStatus' => 1,
	            'Money_Rate' => 1,
	            'Money_CurrentAmount' => $amount,
				'Money_Currency' => $coinBalance
	        );
	        //Bonus DAFCO
	        $coinToken = 8;
	        $amountBonus = 10;
	        $rateDAFCO = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy('DAFCO');
		    $updateBalance = User::updateBalance($user->User_ID, $coinToken, $amountBonus);
		    
		    $moneyArray[] = array(
			    'Money_User' => $user->User_ID,
			    'Money_USDT' => $amountBonus,
			    'Money_USDTFee' => 0,
			    'Money_Time' => time(),
				'Money_Comment' => 'Bonus 10 DAFCO Matrix',
				'Money_MoneyAction' => 25,
				'Money_MoneyStatus' => 1,
	            'Money_Rate' => $rateDAFCO,
	            'Money_CurrentAmount' => $amountBonus,
				'Money_Currency' => $coinToken,
	        );
	        //insert Money
	        $insertMoney = Money::insert($moneyArray);
		    
		    $user->User_MatrixParent = $parentID;
		    $user->User_MatrixTree = $presenter->User_MatrixTree.','.$user->User_ID;
		    $user->User_PositionMatrixTree = $node;
		    $user->User_MatrixStatus = 1;
		    $user->User_MatrixTimeJoin = date('Y-m-d H:i:s');
		    $user->save();
		    
	        $checkDirectCommission = $this->directMatrixCommission($user, $amount);
		    
	        $checkCommission = $this->commissionMatrix($user, $amount);
	        
	        return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Join Matrix System Success!']);
	    }
        
	    return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Join Matrix System Error!']);
    }
    
    // lấy ID và Node bị thiếu của chân
    function getUserIDMissBranch($parentID){
	    $user = User::find($parentID);
	    if(!$user){
		    return false;
	    }
	    $user_list = User::select('Profile_Status','User_ID', 'User_Email', 'User_RegisteredDatetime', 'User_Parent', DB::raw("(CHAR_LENGTH(User_MatrixTree)-CHAR_LENGTH(REPLACE(User_MatrixTree, ',', '')))-" . substr_count($user->User_MatrixTree, ',') . " AS f, User_Agency_Level, User_MatrixTree, User_PositionMatrixTree, User_MatrixParent"))
                        ->leftJoin('profile', 'Profile_User', 'User_ID')
                        ->whereRaw('User_MatrixTree LIKE "'.$user->User_MatrixTree.'%"')
						->where('User_ID','<>',$user->User_ID)
						->orderByRaw('f ASC , User_PositionMatrixTree ASC, User_MatrixTimeJoin ASC')
                        ->get();
// 		dd($user_list);
		$maxF = $this->getMaxFInTree($user_list) + 1;
		
		$FMissBranch = $this->getFMissBranch($user_list, $maxF);
// 		dd($FMissBranch);
		//nếu user chưa đủ 3 chân thì trả về id đó để gắn vào
		if($FMissBranch < 1){
			$getF = $user_list->where('f', 1);
			$nodeMiss = $getF->count();
			if($nodeMiss >= 3){
				return ['user_id'=>$parentID, 'node_miss'=>$nodeMiss];
			}
			return ['user_id'=>$parentID, 'node_miss'=>$nodeMiss];
		}
		if($FMissBranch == 1){
			$getListFCurrent = User::where('User_ID', $parentID)->get();
			$resultIDAndNode = $this->getIDAndNode($getListFCurrent);
		}else{
			$FParent = $FMissBranch - 1;
			$getListFCurrent = $user_list->where('f', $FParent);
			$resultIDAndNode = $this->getIDAndNode($getListFCurrent);
		}
		return $resultIDAndNode;
    }
    // lấy ID và Node bị thiếu của chân
    function getIDAndNode($getListFCurrent){
	    $UserID = '';
	    $checkChildDirect = 3;
		foreach($getListFCurrent as $listFMiss){
			$getChild = User::select('User_Email', 'User_Name','User_ID', 'User_MatrixTree', 'User_PositionMatrixTree')->whereRaw("User_MatrixTree LIKE CONCAT('$listFMiss->User_ID', ',', User_ID) OR User_MatrixTree LIKE CONCAT('%', '$listFMiss->User_ID', ',', User_ID)")->get();
// 			dd($getChild);
			$countChild = $getChild->count();
			if($countChild == 0){
				return ['user_id'=>$listFMiss->User_ID, 'node_miss'=>0]; 
			}
			if($countChild < $checkChildDirect){
				$UserID = $listFMiss->User_ID;
				$checkChildDirect = $countChild;
			}
		}
		return ['user_id'=>$UserID, 'node_miss'=>$countChild];
    }
    //lấy F thiếu chân
    function getFMissBranch($user_list, $maxF){
// 	    dd($user_list, $maxF);
		for($i = 1; $i <= $maxF; $i++){
			$maximunChildF = pow(3,$i);
			$countChildF = $user_list->where('f', $i)->count();
// 			dd($maximunChildF, $countChildF);
			if($countChildF == $maximunChildF){
				//nếu tầng đó = giới hạn thì kiểm tra tiếp tục tầng tiếp theo
				continue;
			}elseif($countChildF > $maximunChildF){
				//nếu số lượng child ở tầng đó lớn hơn giới hạn thì thông báo admin
				//thêm log báo lỗi cho admin
// 				Log::insertLog();
			    return false;
			}
			//nếu tầng nào thiếu chân return tầng đó
			return $i;
		}
    }
    //lấy F lớn nhất hiện tại của ID
    function getMaxFInTree($user_list){
	    $arrUserList = $user_list->toArray();
	    $reverseArray = array_reverse($arrUserList);
	    //nếu ko có user list thì trả về tầng thứ 1 nếu có thì trả về f cuối cùng
	    if(!$reverseArray){
		    return 0;
	    }
	    return $reverseArray[0]['f'];
    }
    
    public function directMatrixCommission($user, $amount){
		$getInfo = User::find($user->User_MatrixParent);
	    if(!$getInfo || $getInfo->User_MatrixStatus != 1){
			return false;
		}
		$currency = 10;
		$percent = 0.3;
		$amountCom = $amount*$percent;
		$matrixCom = array(
			'Money_User' => $getInfo->User_ID,
			'Money_USDT' => $amountCom,
			'Money_USDTFee' => 0,
			'Money_Time' => time(),
			'Money_Comment' => 'Matrix Direct Commission '.($percent*100).'% From User ID: '.$user->User_ID.'',
			'Money_MoneyAction' => 24,
			'Money_MoneyStatus' => 1,
			'Money_Currency' => $currency,
			'Money_Rate' => 1
		);
		$money = Money::insert($matrixCom);
		//update Balance
		$updateblance = User::updateBalance($getInfo->User_ID, $currency, $amountCom);
		$dataSendMail[] = ['user'=>$getInfo, 'amount' => $amountCom, 'actionName'=>'Matrix Direct Commission'];
		Log::sendMailCommission($dataSendMail);
		return true;
    }
    
    public function commissionMatrix($user, $amount){
	    
    	$arrParent = explode(',', $user->User_MatrixTree);
		$arrParent = array_reverse($arrParent);
/*
		if($user->User_ID == 'DAF5398930'){
			dd($user, $arrParent);
		}
*/
		$arrPercent = [1=>0.2, 2=>0.03, 3=>0.03, 4=>0.03, 5=>0.03, 6=>0.02, 7=>0.02, 8=>0.02, 9=>0.02, 10=>0.02];
		$currency = 10;
		$rateCurrency = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy('USD');
		$dataSendMail = [];
		for($i=1;$i<10;$i++){
			if(!isset($arrParent[$i])){
				continue;
			}
			$getInfo = User::find($arrParent[$i]);
			if(!$getInfo || $getInfo->User_MatrixStatus != 1){
				continue;
			}
			//thêm điều kiện số lượng F1 tương ứng với số tầng được hưởng
			//thêm vào đây
			if($i >= 4){
				$countDirect = User::where('User_MatrixParent', $getInfo->User_ID)->select('User_ID', 'User_MatrixParent')->get()->count();
				if($countDirect < $i){
					continue;
				}
			}
			//
			$percent = $arrPercent[$i];
			$amountCom = $amount*$percent;
			$matrixCom = array(
				'Money_User' => $getInfo->User_ID,
				'Money_USDT' => $amountCom,
				'Money_USDTFee' => 0,
				'Money_Time' => time(),
				'Money_Comment' => 'Matrix Commission '.($percent*100).'% From F'.$i.' User ID: '.$user->User_ID.'',
				'Money_MoneyAction' => 19,
				'Money_MoneyStatus' => 1,
				'Money_Currency' => $currency,
				'Money_Rate' => $rateCurrency
			);
			$money = Money::insert($matrixCom);
			//update Balance
			$updateblance = User::updateBalance($getInfo->User_ID, $currency, $amountCom);
			
			$dataSendMail[] = ['user'=>$getInfo, 'amount' => $amountCom, 'actionName'=>'Matrix Commission'];
			
			$this->incomeMatrixCommission($getInfo, $amountCom);
		}
		Log::sendMailCommission($dataSendMail);
    }
    
    public function incomeMatrixCommission($user, $amount){
		$getInfo = User::find($user->User_MatrixParent);
	    if(!$getInfo || $getInfo->User_MatrixStatus != 1){
			return false;
		}
		$currency = 10;
		$percent = 0.1;
		$amountCom = $amount*$percent;
		$matrixCom = array(
			'Money_User' => $getInfo->User_ID,
			'Money_USDT' => $amountCom,
			'Money_USDTFee' => 0,
			'Money_Time' => time(),
			'Money_Comment' => 'Matrix Income Commission '.($percent*100).'% From User ID: '.$user->User_ID.'',
			'Money_MoneyAction' => 20,
			'Money_MoneyStatus' => 1,
			'Money_Currency' => $currency,
			'Money_Rate' => 1
		);
		$money = Money::insert($matrixCom);
		//update Balance
		$updateblance = User::updateBalance($getInfo->User_ID, $currency, $amountCom);
		$dataSendMail[] = ['user'=>$getInfo, 'amount' => $amountCom, 'actionName'=>'Matrix Income Commission'];
		Log::sendMailCommission($dataSendMail);
		return true;
    }
    
    public function getTree(Request $req){

        $user = Session('user');
        $list = array(
			'id' => $user->User_ID,
            'name' => '',
            'title' => $user->User_ID,
            'children' => $this->buildTree($user->User_ID),
            'className' => 'node-tree '.strtoupper($user->User_Name),
        );
        $list = json_encode($list);
        return view('Member.Members-Tree',compact('list'));
    }
    
    function buildTree($idparent, $idRootTemp = null, $barnch = null) {
        $build = User::select('User_Email', 'User_Name','User_ID', 'User_MatrixTree', 'User_PositionMatrixTree')->whereRaw("User_MatrixTree LIKE CONCAT('$idparent', ',', User_ID) OR User_MatrixTree LIKE CONCAT('%', '$idparent', ',', User_ID)")->orderBy('User_PositionMatrixTree')->get();
        $child = array();
        if(count($build) > 0){
            for($i=0;$i<3;$i++){
                if(isset($build[$i])){
                    $child[] = array(
                        'id' => $build[$i]->User_ID,
                        'name' => '',
                        'title' => $build[$i]->User_ID,
                        'className' => 'node-tree '.strtoupper($build[$i]->User_Name),
                        'children' => $this->buildTree($build[$i]->User_ID, $build[$i]->User_ID, 0),
                    );
/*
                    if($build[$i]->User_IsRight == 0){
                        $child[] = array(
                            'id' => $build[$i]->User_ID,
                            'name' => '',
                            'title' => $build[$i]->User_ID,
                            'className' => 'node-tree '.strtoupper($build[$i]->User_Name),
                            'children' => $this->buildTree($build[$i]->User_ID, $build[$i]->User_ID, 0),
                        );
                        if(count($build) < 2){
                            $child[] = array(
                                'id' => 'a'.(int)$build[$i]->User_ID.''.rand(1,99),
                                'name' => 'a'.(int)$build[$i]->User_ID.''.rand(1,99),
                                'title' => 'a'.(int)$build[$i]->User_ID.''.rand(1,99),
                                'className' => 'node-empty right'
                            );
                        }
                        
                    }
                    if($build[$i]->User_IsRight == 1){
                        if(count($build) <2){
                            $child[] = array(
                                'id' => 'a'.(int)$build[$i]->User_ID.''.rand(1,99),
                                'name' => 'a'.(int)$build[$i]->User_ID.''.rand(1,99),
                                'title' => 'a'.(int)$build[$i]->User_ID.''.rand(1,99),
                                'className' => 'node-empty left'
                            );
                        }
    
                        $child[] = array(
                            'id' => $build[$i]->User_ID,
                            'name' => '',
                            'title' => $build[$i]->User_ID,
                            'className' => 'node-tree '.strtoupper($build[$i]->User_Name),
                            'children' => $this->buildTree($build[$i]->User_ID, $build[$i]->User_ID),
                        );
                    }
*/
                }
            }
        }
/*
        else{
            $child[] = array(
                'id' => 'a'.(int)$idRootTemp.''.rand(1,99),
                'name' => 'a'.(int)$idRootTemp.''.rand(1,99),
                'title' => 'a'.(int)$idRootTemp.''.rand(1,99),
                'className' => 'node-empty left'
            );
            $child[] = array(
                'id' => 'a'.(int)$idRootTemp.''.rand(1,99),
                'name' => 'a'.(int)$idRootTemp.''.rand(1,99),
                'title' => 'a'.(int)$idRootTemp.''.rand(1,99),
                'className' => 'node-empty right'
            );
        }
*/
        return $child;
    }
}