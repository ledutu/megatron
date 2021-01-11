<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Http\Controllers\System\CoinbaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use MongoDB\BSON\ObjectID;

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
use App\Model\EggTypes; 
use App\Model\Eggs;
use App\Model\Pools;
use App\Model\LogUser;
use App\Model\Markets;


use App\Jobs\SendTelegramJobs;
use App\Jobs\EggJobs;

class MarketController extends Controller{
    public $feeTrade = 0.005;
    public $eggPrice = 200;
    public function __construct(){
		$this->middleware('auth:api');
		$this->middleware('userPermission', ['only' => ['postCancel', 'postSell', 'postBuy']]);
    }
	/**
	 * function getList
	 * @param 
	 */
    public function postList(Request $req){
	    
	    //return $this->response(200, $list);
	    // $sort = 1;
	    // if($req->sort=='desc'){
		//     $sort = -1;
	    // }
		$list = Markets::where('Status', 0)->whereNotNull('UserSell');
		if($req->currency == 3){
			$list = $list->where('PriceGold', 0);	
			if($req->price == -1 || $req->price == 1){
				$list = $list->orderBy('PriceEUSD', $req->price);
			}
		}elseif($req->currency == 9){
			$list = $list->where('PriceEUSD', 0);
			if($req->price == -1 || $req->price == 1){
				$list = $list->orderBy('PriceGold', $req->price);
			}
		}
		if($req->user){
			$list = $list->where('UserSell', $req->user);	
		}
		if($req->DateTime){
			$list = $list->orderBy('DateTime', $req->DateTime);
		}
		// foreach ($req->except('page') as $key => $value) {
		// 	if($value != ""){
		// 		if($key == 'DateTime'){
		// 			$list = $list->orderBy($key, $value);
		// 		}else {
		// 			$list = $list->orderBy($key, $value);
		// 			if($key == 'currency'){
		// 				if($req->currency == 3){
		// 					$list = $list->where('PriceGold', 0);	
		// 					if($req->price){

		// 						$list = $list->orderBy('PriceEUSD', $req->price);
		// 					}else{
		// 						$list = $list->orderBy('PriceEUSD', 'DESC');
		// 					}
		// 				}else{
		// 					$list = $list->where('PriceEUSD', 0);	
		// 					if($req->price){
		// 						$list = $list->orderBy('PriceGold', $req->price);
		// 					}else{
		// 						$list = $list->orderBy('PriceGold', 'DESC');
		// 					}
		// 				}
		// 			}
					
		// 			if($key == 'user'){
		// 				$list = $list->where('UserSell', $req->user);	
		// 			}
		// 		} 
		// 	}
		// }
		$list = $list->paginate(12);

		$returnData = array(
			'current_page'=>$list->currentPage(),
			'last_page'=>$list->lastPage(),
			'total'=>$list->total(),
			'data'=>array(),
		);
		foreach($list as $v){
			$returnData['data'][] = array(
				'_id' => $v->_id,
				'Item' => $v->Item,
				'Sold' => $v->Sold,
				'Cancel' => $v->Cancel,
				'PriceEUSD' => $v->PriceEUSD,
				'PriceGold' => $v->PriceGold,
				'Type' => $v->Type,
				'Password' => ($v->Password) ? true : false,
				'UserSell' => $v->UserSell,
				'Status' => $v->Status,
				'DateTime' => $v->DateTime
			);
		}
		
		
		
		return $this->response(200, $returnData);

    }
    
    public function postListBuy(Request $req){
		$list = Markets::where('Status', 0)->whereNotNull('UserBuy');
		if($req->currency == 3){
			$list = $list->where('PriceGold', 0);	
			if($req->price == -1 || $req->price == 1){
				$list = $list->orderBy('PriceEUSD', $req->price);
			}
		}elseif($req->currency == 9){
			$list = $list->where('PriceEUSD', 0);
			if($req->price == -1 || $req->price == 1){
				$list = $list->orderBy('PriceGold', $req->price);
			}
		}
		if($req->user){
			$list = $list->where('UserBuy', $req->user);	
		}
		if($req->DateTime){
			$list = $list->orderBy('DateTime', $req->DateTime);
		}
		$list = $list->paginate(12);

		$returnData = array(
			'CurrentPage'=>$list->currentPage(),
			'LastPage'=>$list->lastPage(),
			'Total'=>$list->total(),
			'List'=>array(),
		);
		foreach($list as $v){
			$returnData['List'][] = array(
				'_id' => $v->_id,
				'Item' => $v->Item,
				'Sold' => $v->Sold,
				'Cancel' => $v->Cancel,
				'PriceEUSD' => $v->PriceEUSD,
				'PriceGold' => $v->PriceGold,
				'Type' => $v->Type,
				'Password' => ($v->Password) ? true : false,
				'UserBuy' => $v->UserBuy,
				'Status' => $v->Status,
				'DateTime' => $v->DateTime
			);
		}
		return $this->response(200, $returnData);

    }
    public function postMyList(){
	    $user = Auth::user();
	    if($user){
		    $list = Markets::where('Status', 0)->where('UserSell', $user->User_ID.'')->select('_id', 'Item', 'Type', 'PriceEUSD', 'PriceGold', 'UserSell', 'DateTime')->orderBy('DateTime', -1)->paginate(12);
			$listBuy = Markets::where('Status', 0)->where('UserBuy', $user->User_ID.'')->select('_id', 'Item', 'Type', 'PriceEUSD', 'PriceGold', 'UserBuy', 'DateTime')->orderBy('DateTime', -1)->paginate(12);
			$listAll = $list->merge($listBuy);
			$returnData = array(
				'CurrentPage'=>$list->currentPage() > $listBuy->currentPage() ? $list->currentPage() : $listBuy->currentPage(),
				'LastPage'=>$list->lastPage() > $listBuy->lastPage() ? $list->lastPage() : $listBuy->lastPage(),
				'Total'=>$list->total() > $listBuy->total() ? $list->total() : $listBuy->total(),
				'data'=>$listAll,
			);
			return $this->response(200, $returnData, '', [], true); 
	    }
	   
    }
    
    public function postCancel(Request $req){
	    $user = Auth::user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
        
        if($checkSpam == null){
            //không tồn tại
            //return $this->response(422, [], 'Misconduct!', [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }

        // kiểm tra lệnh martket có tồn tại ko
        $market = Markets::where('_id', new ObjectID($req->id))->where('UserSell', $user->User_ID)->first();

        if(!$market){
	        return $this->response(200, [], __('app.market_does_not_exist'), [], false);
        }

        if(count($market->Item)==0){
	        return $this->response(200, [], __('app.market_does_not_exist'), [], false);
        }
        
        $CancelArray = array();
        foreach($market->Item as $v){
            $CancelArray[] = $v;
        }

        $transaction = Markets::where('_id', new ObjectID($req->id))->update(['Item'=>array(), 'Cancel'=>$CancelArray, 'Status'=>2]);
        if($transaction){
	        foreach($CancelArray as $v){
		        $egg = Eggs::where('ID', $v)->where('Pool', null)->where('Owner', $user->User_ID.'')->first();
		        if($egg){
			       $update = Eggs::where('ID', $v)->where('Owner', $user->User_ID.'')->update(['Pool'=>'0']);
		        }
		        
	        }
        }
        return $this->response(200, ['Transaction'=>$market->_id], __('app.cancel_market_complete'), [], true); 
        
        
    }

    public function postSell(Request $req){
        $user = Auth::user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
        
        if($checkSpam == null){
            //khoong toonf taij
            return $this->response(422, [], __('app.misconduct'), [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
        $publisher = 0;
        $validator = Validator::make($req->all(), [
            'id' => 'required',
            'type' => 'required',
            'priceEUSD' => 'required',
            'priceGold' => 'required',
        ]);
		$password = null;
		$statusTransaction = 'Public';
        if($req->password){
	    	$password = $req->password;
			$statusTransaction = 'Private';
        }

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }
        if($user){
	        if($req->publisher){
				// return $this->response(200, [], 'Coming Soon!', [], false);
				$publisher = 1;
				$priceEUSD = $this->eggPrice;
				$priceGold = 0;
			}else{
				
				$priceEUSD = 0;
				if($req->priceEUSD){
					$priceEUSD = $req->priceEUSD;
					if(!$priceEUSD || ($priceEUSD < 100 || $priceEUSD > 500)){
						return $this->response(200, [], __('app.price_sell_min_100_max_500'), false);
					}
					$currency = 3;
					$price = $req->priceEUSD;
				}
				$priceGold = 0;
				if($req->priceGold){
					$priceGold = $req->priceGold;
					if(!$priceGold || ($priceGold < 11000 || $priceGold > 55000)){
						return $this->response(200, [], __('app.price_sell_min_11000_gold_max_55000_gold'), false);
					}
					$currency = 9;
					$price = $req->priceGold;
				}
              
			}

            $idexplode = explode(',', $req->id);
            $idArray = array();
            switch($req->type){
                case 'egg':
                    foreach($idexplode as $v){
                        if(trim($v)){
                            $item = Eggs::getItem($v, $user->User_ID);
                            if($item){
								if(!$item->CanHatches && $publisher){
									$item->Owner = 'EggsBook.com';
									$item->save();
									return $this->response(200, ['egg'=>false], __('app.your_egg_cannot_hatch'), true);
								}
	                           	if($item->Status == 1 && $item->Pool !== null){
	                                $idArray[] = $v;
	                                // cập nhật trạng thái trứng đang đăng bán
									Eggs::where('ID', $v)->update(['Pool'=>null]);
	                            } 
                            }
                            
                        }
                    }
                break;
            }
            
            if(count($idArray) == 0){
                return $this->response(200, [], __('app.item_does_not_exist'), [], false);
            }
            // tạo transaction giao dich
            if($publisher){
	            $SoldArray = array();
	            foreach($idArray as $v){
		            $SoldArray[] = array(
		            					'id'=>$v,
		            					'user'=>'eggsbook',
		            					'time'=>time(),
		            					'balance'=>0,
		            					);
	            }
	            
	            $data = array(
	                'Item' => array(),
					'Sold' => $SoldArray,
	                'Type'=>$req->type,
	                'UserSell'=>$user->User_ID,
	                'PriceEUSD'=> $priceEUSD,
					'PriceGold'=> $priceGold,
	                'UserBalanceSell'=>User::getBalance($user->User_ID, 3),
	                'Status'=>1,
	                'Password'=>$password,
	                'DateTime'=>time(),
	            );
				$transaction = Markets::CreateMarket($data);
				// $feeTrade = $this->feeTrade*$priceEUSD;
				$feeTrade = 0.05*$priceEUSD;
	            if($transaction){
		            // cộng tiền cho người bán trứng
		            $arrayInsert = array(
						                'Money_User' => $user->User_ID,
						                'Money_USDT' => (double)$priceEUSD*count($SoldArray),
						                'Money_USDTFee' => -$feeTrade,
						                'Money_Time' => time(),
						                'Money_Comment' => 'Sell egg to Eggsbook',
						                'Money_MoneyAction' => 54,
						                'Money_MoneyStatus' => 1,
						                'Money_Address' => null,
						                'Money_Currency' => 3,
						                'Money_CurrentAmount' => (double)$priceEUSD*count($SoldArray),
						                'Money_Rate' => 1,
						                'Money_Confirm' => 0,
						                'Money_Confirm_Time' => null,
						                'Money_FromAPI' => 1,
						            );
		            Money::insert($arrayInsert);
		            
		            // cập nhật trứng cho nhà phát hành
		            foreach($SoldArray as $v){
			            Eggs::where('ID', $v['id'])->update(['Owner'=>'Eggsbook', 'Pool'=>'0']);
		            }
		            
		            $message = "$user->User_ID Sell egg to Eggsbook.com\n"
								. "<b>User ID: </b> "
								. "$user->User_ID\n"
								. "<b>Email: </b> "
								. "$user->User_Email\n"
								. "<b>Amount: </b> "
								. count($SoldArray)." Egg\n"
								. "<b>Price EUSD: </b> "
								. (double)$priceEUSD*count($SoldArray)." EUSD\n"
								. "<b>Status: </b> "
								. $statusTransaction."\n"
								. "<b>Time: </b>\n"
								. date('d-m-Y H:i:s',time());
									
					// dispatch(new SendTelegramJobs($message, -325794634));

		            return $this->response(200, ['id'=>$idArray, 'PriceEUSD'=>(double)$priceEUSD - $feeTrade, 'PriceGold'=>(double)$priceGold, 'transaction'=>$transaction], __('app.sell_eggs_complete'), [], true); 
	            }

            }else{
				$balance = User::getBalance($user->User_ID, $currency);
				/*** kiểm tra khớp lệnh ***/
				// if(!$password){
				// 	$checkOrder = $this->checkOrder($user, $currency, $price, $req->type, 'sell');
				// 	// dd($checkOrder);
				// 	if($checkOrder['status']){
				// 		$userBuy = User::find($checkOrder['order']->UserBuy);
				// 		if($userBuy){
				// 			$balanceBuy = User::getBalance($userBuy->User_ID, $currency);
				// 			$confirmOrder = $this->ConfirmOrderSell($user, $currency, $checkOrder['order'], $balanceBuy, $req->id);
				// 			if($confirmOrder){
				// 				return $this->response(200, ['checkOrder' => $checkOrder], 'Confirm sell egg! Please check your wallet', [], true);
				// 				// return $this->response(200, [], 'Buy egg false!', [], true);
				// 			}
				// 		}
				// 	}
				// }
				/*** kiểm tra khớp lệnh ***/
	          	$data = array(
	                'Item' => $idArray,
	                'Sold' => array(),
	                'Type'=>$req->type,
	                'UserSell'=>$user->User_ID,
	                'PriceEUSD'=> (double)$priceEUSD,
					'PriceGold'=> (double)$priceGold,
	                'UserBalanceSell'=>$balance,
	                'Status'=>0,
	                'Password'=>$password,
	                'DateTime'=>time(),
	            );  
	            $transaction = Markets::CreateMarket($data);
	            if($transaction){
		            $message = "$user->User_ID Sell egg to MARKET\n"
								. "<b>User ID: </b> "
								. "$user->User_ID\n"
								. "<b>Email: </b> "
								. "$user->User_Email\n"
								. "<b>Amount: </b> "
								. count($idArray)." Egg\n"
								. "<b>Price EUSD: </b> "
								. (double)$priceEUSD*count($idArray)." EUSD\n"
								. "<b>Price GOLD: </b> "
								. (double)$priceGold*count($idArray)." GOLD\n"
								. "<b>Status: </b> "
								. $statusTransaction."\n"
								. "<b>Time: </b>\n"
								. date('d-m-Y H:i:s',time());
									
					// dispatch(new SendTelegramJobs($message, -325794634));
		            return $this->response(200, ['id'=>$idArray, 'PriceEUSD'=>(double)$priceEUSD, 'PriceGold'=>(double)$priceGold, 'transaction'=>$transaction], __('app.sell_eggs_complete'), [], true); 
	            }
            }
        }
    }
    
    public function postBuy(Request $req){
        
        $user = Auth::user();
        if ($user) {
			// usleep(rand(0, 1000000));
	        
			$checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
			
			if($checkSpam == null){
				//khoong toonf taij
				return $this->response(422, [], 'Misconduct!', [], false);
			}else{
				DB::table('string_token')->where('User', $user->User_ID)->delete();
			}
			$validator = Validator::make($req->all(), [
	            'id' => 'required',
	            'amount' => 'required|gte:1',
	            'currency_id' => 'required',
	        ]);
	        
	        if ($validator->fails()) {
	            foreach ($validator->errors()->all() as $value) {
	                return $this->response(200, [], $value, $validator->errors(), false);
	            }
	        }
	        
	        // kiểm tra transaction
            if(strlen($req->id) != 24 && ctype_xdigit($req->id)===true){
				return $this->response(200, [], __('app.transaction_does_not_exist'), [], false);
			}

            $transaction = Markets::where('_id', new ObjectID($req->id))->first();

            if(!$transaction){
	            return $this->response(200, [], __('app.transaction_does_not_exist'), [], false);
            }
            
            if($transaction->UserSell == $user->User_ID){
	            return $this->response(200, [], __('app.you_cannot_buy_your_own_order'), [], false);
            }
	        
	        if($req->currency_id != 3 && $req->currency_id != 9){
                return $this->response(200, [], __('app.currency_does_not_exist'), [], false);
            }else{
	            if($req->currency_id == 3){
		            $Price = $transaction->PriceEUSD;
	            }else{
		            $Price = $transaction->PriceGold;
	            }
            }
			if(isset($transaction->Password) && $transaction->Password){
				if($req->Password != $transaction->Password){
					return $this->response(200, [], __('app.transaction_password_is_wrong'), [], false);
				}
			}

            if($req->amount > count($transaction->Item)){
	            return $this->response(200, [], 'amount < '.count($transaction->Item), [], false);
            }
            
            // kiểm tra balance người mua
            $balance = User::getBalance($user->User_ID, $req->currency_id);
            
            if($balance < ($Price*$req->amount)){
	            return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
            }
            
            $SoldArray = array();
            $ItemArray = $transaction->Item;
            for($i=0; $i<(int)$req->amount;$i++){
	            $SoldArray[] = array(
	            					'id'=>$transaction->Item[$i],
	            					'user'=>$user->User_ID,
	            					'time'=>time(),
	            					'balance'=>$balance,
	            					);
	            unset($ItemArray[$i]);
            }
            $status = 0;
            if(count($ItemArray) == 0){
	            // khớp toàn bộ lệnh
	            $status = 1;
            }
            // trừ tiền người mua
            $arrayInsert = array();
            $logArray = array();
            foreach($SoldArray as $v){
				$checkEggsExist = Eggs::where('ID', $v['id'])->where('Owner', $transaction->UserSell)->first();
				if(!$checkEggsExist){
					$update = Markets::where('_id', new ObjectID($req->id))->update(['Log'=>'Item ID Not Found in UserSell', 'Status'=>-1]);
					return $this->response(200, ['Transaction'=>$transaction->_id], __('app.buy_eggs_failed_user_buy_do_not_have_egg'), [], false);
				}
	            $feeTrade = $Price*$this->feeTrade;
				$arrayInsert[] = array(
						                'Money_User' => $transaction->UserSell,
						                'Money_USDT' => $Price,
						                'Money_USDTFee' => -$feeTrade,
						                'Money_Time' => time(),
						                'Money_Comment' => 'Sell egg ID:'.$v['id'].' to user ID:'.$user->User_ID,
						                'Money_MoneyAction' => 38,
						                'Money_MoneyStatus' => 1,
						                'Money_Address' => null,
						                'Money_Currency' => $req->currency_id,
						                'Money_CurrentAmount' => $transaction->Price,
						                'Money_Rate' => 1,
						                'Money_Confirm' => 0,
						                'Money_Confirm_Time' => null,
						                'Money_FromAPI' => 1,
						            );
						            
				$arrayInsert[] = array(
						                'Money_User' => $user->User_ID,
						                'Money_USDT' => -$Price,
						                'Money_USDTFee' => 0,
						                'Money_Time' => time(),
						                'Money_Comment' => 'Buy egg ID:'.$v['id'].' to user ID:'.$transaction->UserSell,
						                'Money_MoneyAction' => 37,
						                'Money_MoneyStatus' => 1,
						                'Money_Address' => null,
						                'Money_Currency' => $req->currency_id,
						                'Money_CurrentAmount' => $transaction->Price,
						                'Money_Rate' => 1,
						                'Money_Confirm' => 0,
						                'Money_Confirm_Time' => null,
						                'Money_FromAPI' => 1,
						            );
				$logArray[] = array(
			                    'action'=>'Buy in market',
			                    'user'=>$user->User_ID,
			                    'comment'=>'Sell egg ID:'.$v['id'].' to user ID:'.$transaction->UserSell,
								'ip'=>$req->ip(),
								'datetime'=>date('Y-m-d H:i:s'),                    
                    			'action_id'=>30,
			                );
			                
			    Eggs::where('ID', $v['id'])->update(['Owner'=>$user->User_ID, 'Pool'=>'0']);
			    $message = "$user->User_ID Buy egg on MARKET\n"
								. "<b>User ID: </b> "
								. "$user->User_ID\n"
								. "<b>Email: </b> "
								. "$user->User_Email\n"
								. "<b>Amount: </b> "
								. count($SoldArray)." Egg\n"
								. "<b>Egg ID: </b> "
								. $v['id']."\n"
								. "<b>From user: </b> "
								. $transaction->UserSell."\n"
								. "<b>Price: </b> "
								. $Price." ".(($req->currency_id==3) ? 'EUSD' : 'GOLD')."\n"
								. "<b>Time: </b>\n"
								. date('d-m-Y H:i:s',time());
									
					// dispatch(new SendTelegramJobs($message, -325794634));
            }
            Money::insert($arrayInsert);
            LogUser::insert($logArray);
            // cập nhật giao dịch
            $update = Markets::where('_id', new ObjectID($req->id))->update(['Item'=>$ItemArray, 'Sold'=>$SoldArray, 'Status'=>$status]);
            
            return $this->response(200, ['Transaction'=>$transaction->_id], __('app.buy_egg_complete'), [], true);
 
        }  
	}
	
	public function ConfirmOrderSell($user, $currency_id, $order, $userBalance, $eggID){
		//Add egg into eggs table
		$egg = Eggs::where('ID', $eggID)->first();
		if(!$egg){
			return false;
		}
		$egg->Owner = $order->UserBuy;
		$egg->BuyFrom = 'Market User: '.$user->User_ID;
		$egg->Pool = "0";
		$soldInfo = [
			'id' => $egg->ID,
			'user' => $order->UserBuy,
			'time' => time(),
			'balance' => $userBalance,	
		];
		// dd($order, $soldInfo);
		$SoldArray = $order->Sold;
		$SoldArray[] = $soldInfo;
		$order->Sold = $SoldArray;
		$order->Item = [];
		$order->Status = 1;
		$UserBuy = User::find($order->UserBuy);
		if($currency_id == 3){
			$price = $order->PriceEUSD;
		}else{
			$price = $order->PriceGold;
		}
		$fee = $price * $this->feeTrade;
		//add money sell user
		$this->calculateMoney($user, $price, 'Sell Egg ID: '.$soldInfo['id'].' to user: '.$user->User_ID.' in market', 38, $currency_id, $fee);
		$order->save();
		$egg->save();	
		// } else if($currency_id == 9) {
		// 	$this->calculateMoney($user, $order->PriceGold, 'Bought '.$soldInfo['id'].' in market', 37, 9);	
		// 	$this->calculateMoney($userSell, -$order->PriceEUSD, 'Sold '.$soldInfo['id'].' in market', 38, 9);		
		// } else{
		// 	return false;
		// }
		return true;
	}
	
	public function postHistory(Request $request){
		$user = $request->user();
		$market = Markets::where('UserSell', $user->User_ID)->orWhere('UserBuy', $user->User_ID)->orWhere('Sold.user', $user->User_ID)->orderBy('DateTime', -1)->paginate(12);
		return $this->response(200, ['history' => $market]);
	}
	
	/**
	 * @param string type (ex: egg)
	 * @param string order_type (ex: buy/sell)
	 * @param float price 
	 * @param int currency_id
	 * @param string password
	 */
	public function postOrder(Request $request){
		$validator = Validator::make($request->all(), [
			'Type' => 'required',
			// 'OrderType' => 'required',
			'Price' => 'required|numeric|min:100',
			'Currency' => 'required|numeric|in:3,9',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}
		$user = $request->user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $request->CodeSpam)->first();
        if($checkSpam == null){
            //khoong toonf taij
            return $this->response(200, [], __('app.misconduct'), [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
		if($request->Type != 'egg'){
			return $this->response(200, [], __('app.type_error'), [], false);
		}
		$currency = $request->Currency;
		if($currency == 9){
			if($request->Price < 11000){
				return $this->response(200, [], __('app.price_gold_min_11000_gold'), [], false);
			}
		}else{
			if($request->Price > 500){
				return $this->response(200, [], __('app.price_eusd_max_500_eusd'), [], false);
			}
		}
		$balance = User::getBalance($user->User_ID, $currency);
		// $balanceEUSD = User::getBalance($user->User_ID, 3);
		// $balanceGold = User::getBalance($user->User_ID, 9);
		
		// $userBalance = [
		// 	'3' => $balanceEUSD,
		// 	'9' => $balanceGold,
		// ];
		
		if($request->Price > $balance) return $this->response(200, [], __('app.your_balance_is_not_enough'), [], false);
		// $checkOrder = $this->checkOrder($user, $currency, $request->Price, $request->Type);
		// dd($checkOrder);
		$checkOrder['status'] = false;
		if($checkOrder['status']){
			// $confirmOrder = $this->ConfirmOrderBuy($user, $currency, $checkOrder['order'], $balance);
			// if(!$confirmOrder){
			// 	return $this->response(200, [], 'Buy egg false! Please try again!', [], true);
			// }
			// return $this->response(200, [
			// 	'Transaction' => $checkOrder['order']->_id,
			// 	'status' => true
			// ], 'Confirm buy egg! Please check your storage!', [], true);
		} else {
			$priceEUSD = 0;
			$priceGold = 0;
			if($currency == 3){
				$priceEUSD = $request->Price;
			}else{
				$priceGold = $request->Price;
			}
			//tạo lệnh đặt mua trên Market
			$data = array(
				'Item' => array(),
				'Sold' => array(),
				'Cancel' => array(),
				'Type'=>$request->Type,
				'UserBuy'=>$user->User_ID,
				'PriceEUSD'=> (double)$priceEUSD,
				'PriceGold'=> (double)$priceGold,
				'UserBalanceBuy'=>$balance,
				'Status'=>0,
				'Password'=>null,
				'DateTime'=>time(),
			);  
			$transaction = Markets::insertGetId($data);
			$transaction = (array)$transaction;
			$id = ($transaction['oid']) ?? null;
			if($id){
				$data['_id'] = $id;
				$this->calculateMoney($user, -$request->Price, 'Order buy egg in market', 53, $currency);	
				$logArray[] = array(
							'action'=>'Order buy egg in market',
							'user'=>$user->User_ID,
							'comment'=>'Order buy egg in market',
							'ip'=>$request->ip(),
							'datetime'=>date('Y-m-d H:i:s'),                 
							'action_id'=>53,
						);
				LogUser::insert($logArray);
				// $message = "$user->User_ID Buy egg on MARKET\n"
				// 			. "<b>User ID: </b> "
				// 			. "$user->User_ID\n"
				// 			. "<b>Email: </b> "
				// 			. "$user->User_Email\n"
				// 			. "<b>Amount: </b> "
				// 			. count($SoldArray)." Egg\n"
				// 			. "<b>Egg ID: </b> "
				// 			. $v['id']."\n"
				// 			. "<b>From user: </b> "
				// 			. $transaction->UserSell."\n"
				// 			. "<b>Price: </b> "
				// 			. $Price." ".(($req->currency_id==3) ? 'EUSD' : 'GOLD')."\n"
				// 			. "<b>Time: </b>\n"
				// 			. date('d-m-Y H:i:s',time());
								
				// dispatch(new SendTelegramJobs($message, -325794634));
				return $this->response(200, ['Transaction' => 
				$data, 
				'Time'=>date("Y-m-d H:i:s"),
				'status' => false
			], 'Order buy in market success!', [], true);
			}
			return $this->response(200, [], __('app.error_please_try_again'), [], false);
		}	
	}
	
	/**
	 * @param object user
	 * @param int currency_id
	 * @param float price
	 * @param string type
	*/
	public function checkOrder($user, $currency_id, $price, $type, $TypeMarket = 'buy'){
		$order = Markets::where([
			'Status' => 0,
			'Type' => $type,
			'Password' => null,
		]);
		if($TypeMarket == 'buy'){
			$order = $order->where('UserSell', '!=', $user->User_ID);
		}else{
			$order = $order->where('UserBuy', '!=', $user->User_ID);
		}
		if($currency_id == 3){
			$order = $order->where('PriceEUSD', (double) $price)->first();
		} else if($currency_id == 9) {
			$order = $order->where('PriceGold', (double) $price)->first();
		}
		return [
			'status' => $order != null,
			'order' => $order,
		];
	}
	
	public function ConfirmOrderBuy($user, $currency_id, $order, $userBalance){
		//Add egg into eggs table
		$egg = Eggs::where('ID', $order->Item[0])->first();
		if(!$egg){
			return false;
		}
		$egg->Owner = $user->User_ID;
		$egg->BuyFrom = 'Market User: '.$order->UserSell;
		$egg->Pool = "0";
		$soldInfo = [
			'id' => $order->Item[0],
			'user' => $user->User_ID,
			'time' => time(),
			'balance' => $userBalance,	
		];
		// dd($order, $soldInfo);
		$SoldArray = $order->Sold;
		$SoldArray[] = $soldInfo;
		$order->Sold = $SoldArray;
		$order->Item = [];
		$order->Status = 1;
		$userSell = User::find($order->UserSell);
		if($currency_id == 3){
			$price = $order->PriceEUSD;
		}else{
			$price = $order->PriceGold;
		}
		$fee = $price * $this->feeTrade;
		//Minus money buy user
		$this->calculateMoney($user, -$price, 'Buy Egg ID: '.$soldInfo['id'].' from user: '.$userSell->User_ID.' in market', 37, $currency_id);
		//add money sell user
		$this->calculateMoney($userSell, $price, 'Sell Egg ID: '.$soldInfo['id'].' to user: '.$user->User_ID.' in market', 38, $currency_id, $fee);
		$order->save();
		$egg->save();	
		// } else if($currency_id == 9) {
		// 	$this->calculateMoney($user, $order->PriceGold, 'Bought '.$soldInfo['id'].' in market', 37, 9);	
		// 	$this->calculateMoney($userSell, -$order->PriceEUSD, 'Sold '.$soldInfo['id'].' in market', 38, 9);		
		// } else{
		// 	return false;
		// }
		return true;
	}

	public function postConfirmOrder(Request $request){
		$validator = Validator::make($request->all(), [
			'ID' => 'required',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}
		$user = $request->user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $request->CodeSpam)->first();
        if($checkSpam == null){
            //khoong toonf taij
            return $this->response(422, [], 'Misconduct!', [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
		// kiểm tra transaction
		if(strlen($request->ID) != 24 && ctype_xdigit($request->ID)===true){
			return $this->response(200, [], __('app.transaction_does_not_exist'), [], false);
		}

		$transaction = Markets::where('_id', new ObjectID($request->ID))->first();

		if(!$transaction){
			return $this->response(200, [], __('app.transaction_does_not_exist'), [], false);
		}
		if($transaction->Status != 0){
			return $this->response(200, [], __('app.transaction_is_confirmed'), [], false);
		}
		$getEggUser = Eggs::where('Owner', $user->User_ID)->where('Status', 1)->where('ActiveTime', 0)->where('Pool', "0")->first();
		if(!$getEggUser){
			return $this->response(200, [], __('app.you_dont_have_egg_enough'), [], false);
		}
		$UserBuy = User::find($transaction->UserBuy);
		if(!$UserBuy){
			return $this->response(200, [], __('app.user_buy_error_please_sell_to_another_order'), [], false);
		}
		if($user->User_ID == $UserBuy->User_ID){
			return $this->response(200, [], __('app.please_sell_to_another_order_in_market'), [], false);
		}
		$getEggUser->Owner = $UserBuy->User_ID;
		if($transaction->PriceEUSD > 0){
			$currency = 3;
			$price = $transaction->PriceEUSD;
		}else{
			$currency = 9;
			$price = $transaction->PriceGold;
		}
		$userBalance = User::getBalance($user->User_ID, $currency);
		$soldInfo[] = [
			'id' => $getEggUser->ID,
			'user' => $user->User_ID,
			'time' => time(),
			'balance' => $userBalance,	
		];
		$transaction->Sold = $soldInfo;
		$transaction->Item = [];
		$transaction->Status = 1;
		//add money sell user
		$fee = $price * $this->feeTrade;
		$this->calculateMoney($user, $price, 'Sell Egg ID: '.$getEggUser->ID.' for User: '.$UserBuy->User_ID.' in market', 38, $currency, $fee);
		$transaction->save();
		$getEggUser->save();

		return $this->response(200, ['EggID'=>$getEggUser->ID, 'Transaction'=>$transaction->_id], __('app.sell_eggs_complete'), [], true);
	}

	public function postCancelOrder(Request $request){
		$validator = Validator::make($request->all(), [
			'ID' => 'required',
		]);
		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}
		$user = $request->user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $request->CodeSpam)->first();
        if($checkSpam == null){
            //khoong toonf taij
            return $this->response(422, [], 'Misconduct!', [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
		// kiểm tra transaction
		if(strlen($request->ID) != 24 && ctype_xdigit($request->ID)===true){
			return $this->response(200, [], __('app.transaction_does_not_exist'), [], false);
		}

		$transaction = Markets::where('_id', new ObjectID($request->ID))->first();

		if(!$transaction){
			return $this->response(200, [], __('app.transaction_does_not_exist'), [], false);
		}
		if($transaction->Status != 0){
			return $this->response(200, [], __('app.transaction_is_confirmed'), [], false);
		}
		if($user->User_ID != $transaction->UserBuy){
			return $this->response(200, [], __('app.transaction_error'), [], false);
		}
		$action = 52;
		if($transaction->PriceEUSD > 0){
			$currency = 3;
			$price = $transaction->PriceEUSD;
		}else{
			$currency = 9;
			$price = $transaction->PriceGold;
		}
		$this->calculateMoney($user, $price, __('app.cancel_order_buy_egg_in_market'), $action, $currency);
		//cancel leenhj
		$transaction->Status = -1;
		$transaction->save();
		return $this->response(200, ['Transaction'=>$transaction->_id], __('app.cancel_order_buy_egg_successful'), [], true);
	}
	 
	public function calculateMoney($user, $price, $comment, $action_id, $currency_id, $fee = 0)
    {
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => (double)$price,
            'Money_USDTFee' => -$fee,
            'Money_Time' => time(),
            'Money_Comment' => $comment,
            'Money_MoneyAction' => $action_id,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => $currency_id,
            'Money_CurrentAmount' => (double)$price,
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_Confirm_Time' => null,
            'Money_FromAPI' => 1,
        );
        $insert = Money::insert($arrayInsert);
        //check commission
    }
}