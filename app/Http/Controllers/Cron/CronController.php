<?php
namespace App\Http\Controllers\Cron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Model\Money;
use App\Model\User;
use App\Model\Investment;
use App\Model\Wallet;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Value\Money as CB_Money;
use IEXBase\TronAPI\Tron;
use Carbon\Carbon;

use DB;
// Queue
use App\Jobs\SendMailJobs;
use App\Jobs\SendTelegramJobs;
use App\Jobs\PayInterestJobs;
use App\Jobs\PayInterestLotJobs;
use App\Jobs\PaySalesSystemJobs;

class CronController extends Controller{
	public $feeDeposit = 0;

    public function createAddressUSDT(){

        $key = 'qeDpvuDjsndqeQhHA2hatgkWpat2VkTDbzdF1xtvcRqcNRtnZL';
        $content = file_get_contents("https://coinbase.rezxcvbnm.co/public/address?key=$key");
        return $content;
    }
  
  	public function getCheckInsuranceExpired(Request $req){
      	$getInsur = DB::table('promotion_sub')->where('status', 0)->get();
      	$datetime = date('Y-m-d H:i:s');
      	$arrExpired = [];
      	foreach($getInsur as $g){
          	if($g->expired_time <= $datetime){
              	$arrExpired[] = $g->id;
            }
        }
      	//dd($arrExpired);
      	if(count($arrExpired)){
          	$update = DB::table('promotion_sub')->where('status', 0)->whereIn('id', $arrExpired)->update(['status'=>1]);
        }
    }
		
    public function getPaySalesSystem(Request $req){
		$todayThisMonth = (int)date('j');
		if($todayThisMonth != '05'){
			// dd('today is '.$todayThisMonth);
		}
		$firstDayLastMonth = strtotime('today first day of last month');
		$lastDayThisMonth = strtotime('today first day of this month');
		// dd(date('Y-m-d H:i:s', $firstDayLastMonth), date('Y-m-d H:i:s', $lastDayThisMonth));
		$investment = Investment::leftJoin('users', 'User_ID', 'investment_User')
								->where('investment_Status', 1)
								->where('investment_Time', '>=', $firstDayLastMonth)
								->where('investment_Time', '<', $lastDayThisMonth)
								// ->where('investment_User', 999999)
								->selectRaw('investment_Amount , investment_Time, investment_User, investment_ID, User_Tree, User_ID')
								->paginate(100);
		// InterestController::getPaySalesSystem($investment);
        // dd($investment,123);
        dispatch(new PaySalesSystemJobs($investment));
        $page = $investment->currentPage();
        $lastPage = $investment->lastPage();
        if($page < $lastPage){
            // sleep(0.1);
            return view('Cron.Interest',compact('page'));
            return redirect()->route('system.cron.getProfits',['page'=>$page+1]);
        }
        
        dd('paying interest, please check after 5 minutes!');
    }

    public function getPayInterestLot(Request $req){

		$investment = Investment::leftJoin('users', 'User_ID', 'investment_User')
								->where('investment_Status', 1)
								// ->where('investment_User', 999999)
								->selectRaw('SUM(investment_Amount) as investment_Amount , MIN(investment_Time) as investment_Time, investment_User, investment_ID')
								->groupBy('investment_User')
								->paginate(10000);
		// dd($investment);
		$today = date('Y-m-d');
		$sales = DB::table('lot_sales')->where('lot_Date', $today)->value('lot_Sales');
		$amountLot = $sales / 100000;
		// dd($amountLot);
		if(!$amountLot || !is_numeric($amountLot) || $amountLot <= 0){
			dd('Please Update Amount LOT');
		}
		// dd($investment, 33);
        InterestController::getProfitsLot($investment,$amountLot);
        // dispatch(new PayInterestLotJobs($investment, $amountLot));
        // $page = $investment->currentPage();
        // $lastPage = $investment->lastPage();
        // if($page < $lastPage){
        //     // sleep(0.1);
        //     return view('Cron.Interest',compact('page'));
        //     return redirect()->route('system.cron.getProfitsLot',['page'=>$page+1]);
        // }
        
        dd('paying interest, please check after 5 minutes!');
    }

    public function getPayInterest(Request $req){

		$investment = Investment::leftJoin('users', 'User_ID', 'investment_User')
								->where('investment_Status', 1)
								// ->where('investment_User', 601094)
								->selectRaw('SUM(investment_Amount) as investment_Amount , MIN(investment_Time) as investment_Time, investment_User, investment_ID, User_Tree, User_ID')
								->groupBy('investment_User')
								->paginate(100);
        // InterestController::getProfits($investment);
        // dd($investment);
        dispatch(new PayInterestJobs($investment));
        $page = $investment->currentPage();
        $lastPage = $investment->lastPage();
        if($page < $lastPage){
            // sleep(0.1);
            return view('Cron.Interest',compact('page'));
            return redirect()->route('system.cron.getProfits',['page'=>$page+1]);
        }
        
        dd('paying interest, please check after 5 minutes!');
    }
    
    public function getDepositBCH(Request $req){
	    
	    $coin = DB::table('currency')->where('Currency_Symbol', $req->coin)->first();
	    if(!$coin){
		    dd('coin not exit');
	    }
	    $symbol = $coin->Currency_Symbol;
	    $blockcypher = 'https://api.blockcypher.com/v1/'.strtolower($symbol).'/main/txs/';
		$api = "https://explorer.api.bitcoin.com/bch/v1/tx/";

	    // $rate = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy();
	    
	    $transactions = app('App\Http\Controllers\System\CoinbaseController')->getAccountTransactions($symbol);
	    // dd($symbol);
	    // $priceCoin = $rate[$symbol];
	    $priceCoin = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy($coin->Currency_Symbol);
	    // $tokenPrice = $rate['SKC'];
        foreach($transactions as $v){
	        if($v->getamount()->getamount() > 0){
				$hash = Money::where('Money_Address', $v->getnetwork()->gethash())->first();
				if(!$hash){
					$transactionHash = $v->getnetwork()->gethash();
					$client = new \GuzzleHttp\Client();
					$res = $client->request('GET', $api.$transactionHash);
					$response = $res->getBody(); 
					$json = json_decode($response);
					// dd($json, $v);
					foreach($json->vout as $trans){
						$addArray = array();
						foreach($trans->scriptPubKey->addresses as $j){
							$addArray[] = $j;	
						}
						$address = Wallet::select('Address_User')->whereIn('Address_Address', $addArray)->first();
						if($address){
							$amount = $trans->value;
							$money = new Money();
							$money->Money_User = $address->Address_User;
							$money->Money_USDT = $amount*$priceCoin;
							$money->Money_Time = time();
							$money->Money_Comment = 'Deposit '.($amount+0).' '.$symbol;
							$money->Money_Currency = 5;
							$money->Money_CurrencyFrom = $coin->Currency_ID;
							$money->Money_MoneyAction = 1;
							$money->Money_TXID = $trans->spentTxId;
							$money->Money_Address = $v->getnetwork()->gethash();
							$money->Money_CurrentAmount = $amount;
							$money->Money_Rate = $priceCoin;
							$money->Money_MoneyStatus = 1;
							$money->save();	
							// $updatebalance = User::updateBalance($address->Address_User, 5, $amount*$priceCoin);

							$user = User::find($address->Address_User);
							$message = "$user->User_Email Deposit $amount $symbol\n"
									. "<b>User ID: </b> "
									. "$user->User_ID\n"
									. "<b>Email: </b> "
									. "$user->User_Email\n"
									. "<b>Amount: </b> "
									. $amount." $symbol\n"
									. "<b>Amount USD: </b> "
									. ($amount*$priceCoin)." USDT\n"
									. "<b>Rate: </b> "
									. "$ $priceCoin \n"
									. "<b>Submit Deposit Time: </b>\n"
									. date('d-m-Y H:i:s',time());
										
							dispatch(new SendTelegramJobs($message, -408631932));
						}
					}
					
				}   
		    }
        }
		echo 'check deposit success';exit;
    }
    
    public function getDeposit(Request $req){
	    if($req->coin == "BCH"){
			$this->getDepositBCH($req);
			dd('check deposit BCH done');
		}
	    $coin = DB::table('currency')->where('Currency_Symbol', $req->coin)->first();
	    if(!$coin){
		    dd('coin not exit');
	    }
	    $symbol = $coin->Currency_Symbol;
	    $blockcypher = 'https://api.blockcypher.com/v1/'.strtolower($symbol).'/main/txs/';

	    $rate = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy();
	    
	    $transactions = app('App\Http\Controllers\System\CoinbaseController')->getAccountTransactions($symbol);
	    //dd($transactions);
	    $priceCoin = $rate[$symbol];
	    // $tokenPrice = $rate['SKC'];
        foreach($transactions as $v){
	        if($v->getamount()->getamount() > 0){
				$hash = Money::where('Money_Address', $v->getnetwork()->gethash())->first();
				
				if(!$hash){
					$transactionHash = $coin->Currency_ID == 2 ? "0x".$v->getnetwork()->gethash() : $v->getnetwork()->gethash();
					// dd($transactionHash);
					$client = new \GuzzleHttp\Client();
					$res = $client->request('GET', $blockcypher.$transactionHash);
					$response = $res->getBody(); 
					$json = json_decode($response);
					
					$addArray = array();
					
					foreach($json->addresses as $j){
						if($coin->Currency_Symbol == 'ETH'){
							$addArray[] = '0x'.$j;	
						}else{
							$addArray[] = $j;	
						}
					}
					
					$address = Wallet::select('Address_User')->whereIn('Address_Address', $addArray)->first();

					if($address){
                        $amount = $v->getamount()->getamount();

						$money = new Money();
						$money->Money_User = $address->Address_User;
						$money->Money_USDT = $amount*$priceCoin;
						$money->Money_Time = time();
						$money->Money_Comment = 'Deposit '.($amount+0).' '.$symbol;
						$money->Money_Currency = 5;
						$money->Money_CurrencyFrom = $coin->Currency_ID;
						$money->Money_MoneyAction = 1;
						$money->Money_Address = $v->getnetwork()->gethash();
						$money->Money_CurrentAmount = $amount;
						$money->Money_Rate = $priceCoin;
						$money->Money_MoneyStatus = 1;
						$money->save();	
                        $updatebalance = User::updateBalance($address->Address_User, 5, $amount*$priceCoin);

						$user = User::find($address->Address_User);
						$message = "$user->User_Email Deposit $amount $symbol\n"
								. "<b>User ID: </b> "
								. "$user->User_ID\n"
								. "<b>Email: </b> "
								. "$user->User_Email\n"
								. "<b>Amount: </b> "
								. $amount." $symbol\n"
								. "<b>Amount USD: </b> "
								. ($amount*$priceCoin)." USDT\n"
								. "<b>Rate: </b> "
								. "$ $priceCoin \n"
								. "<b>Submit Deposit Time: </b>\n"
								. date('d-m-Y H:i:s',time());
									
						dispatch(new SendTelegramJobs($message, -408631932));
					}
					
				}   
		    }
        }
		echo 'check deposit success';exit;
    }
	
	public function getDepositUSDTWithAddress($address){
		if(!$address){
          	dd('done');
        }
		$contractAddress = '0xdac17f958d2ee523a2206206994597c13d831ec7';
		$apiKey = 'GMGAYV28HNBZSAHUQQD3PQDXMFGZU7BMBP';
		$client = new \GuzzleHttp\Client(); //GuzzleHttp\Client
		$getTransactions = json_decode($client->request('GET', 'https://api.etherscan.io/api?module=account&action=tokentx&contractaddress='.$contractAddress.'&address='.$address.'&offset=5000&page=1&sort=desc&apikey='.$apiKey)->getBody()->getContents());

		$address = DB::table('address')->select('Address_Address', 'Address_User')->where('Address_Currency', 5)->pluck('Address_Address')->toArray();


		foreach($getTransactions->result as $v){
			if(array_search($v->to, $address) !== false) {

				$hash = DB::table('money')->where('Money_Address', $v->hash)->first();
				if(!$hash){

					$user = DB::table('address')->join('users', 'Address_User', 'User_ID')->where('Address_Address', $v->to)->where('Address_Currency', 5)->first();
					if(!$user){
						continue;
					}
					$value = filter_var($v->value/1000000, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
					$amountFee = $value * $this->feeDeposit;
					//cộng tiền
					$money = new Money();
					$money->Money_User = $user->Address_User;
					$money->Money_USDT = $value - $amountFee;
					$money->Money_USDTFee = $amountFee;
					$money->Money_Time = time();
					$money->Money_Comment = 'Deposit '.$value.' USDT';
					$money->Money_Currency = 5;
					$money->Money_CurrencyFrom = 5;
					$money->Money_MoneyAction = 1;
					$money->Money_Address = $v->hash;
					$money->Money_CurrentAmount = $value;
					$money->Money_Rate = 1;
					$money->Money_MoneyStatus = 1;
                    $money->save();
					
					// 	Gửi telegram thông báo User verify
					$message = "$user->User_Email Deposit ".$value." USDT\n"
							. "<b>User ID: </b> "
							. "$user->User_ID\n"
							. "<b>Email: </b> "
							. "$user->User_Email\n"
							. "<b>Amount USD: </b> "
							. $value." USD\n"
							. "<b>Amount Coin: </b> "
							. $value." USDT\n"
							. "<b>Rate: </b> "
							. "$ 1 \n"
							. "<b>Submit Deposit Time: </b>\n"
							. date('d-m-Y H:i:s',time());
								
			        dispatch(new SendTelegramJobs($message, -408631932));
				}
			}
		}
		dd('stop');
		
	
		
	}
  
	public function getDepositUSDT(Request $req){
      	if($req->address){
          	$this->getDepositUSDTWithAddress($req->address);
          	dd('check deposit address done');
        }
		// $address = DB::table('address')->where('Address_Currency', 5)->get();
		$contractAddress = '0xdac17f958d2ee523a2206206994597c13d831ec7';
		$apiKey = 'GMGAYV28HNBZSAHUQQD3PQDXMFGZU7BMBP';
		$client = new \GuzzleHttp\Client(); //GuzzleHttp\Client
		$getTransactions = json_decode($client->request('GET', 'https://api.etherscan.io/api?module=account&action=tokentx&contractaddress='.$contractAddress.'&offset=5000&page=1&sort=desc&apikey='.$apiKey)->getBody()->getContents());

		$address = DB::table('address')->select('Address_Address', 'Address_User')->where('Address_Currency', 5)->pluck('Address_Address')->toArray();

		foreach($getTransactions->result as $v){
			if(array_search($v->to, $address) !== false) {

				$hash = DB::table('money')->where('Money_Address', $v->hash)->first();
				if(!$hash){
					$user = DB::table('address')->join('users', 'Address_User', 'User_ID')->where('Address_Address', $v->to)->where('Address_Currency', 5)->first();
                  	if(!$user){
                      	continue;
                    }

					$value = filter_var($v->value/1000000, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
					$amountFee = $value * $this->feeDeposit;
					//cộng tiền
					$money = new Money();
					$money->Money_User = $user->Address_User;
					$money->Money_USDT = $value - $amountFee;
					$money->Money_USDTFee = $amountFee;
					$money->Money_Time = time();
					$money->Money_Comment = 'Deposit '.$value.' USDT';
					$money->Money_Currency = 5;
					$money->Money_CurrencyFrom = 5;
					$money->Money_MoneyAction = 1;
					$money->Money_Address = $v->hash;
					$money->Money_CurrentAmount = $value;
					$money->Money_Rate = 1;
					$money->Money_MoneyStatus = 1;
                    $money->save();
                    
                    //$updatebalance = User::updateBalance($user->Address_User, 5, $value - $amountFee);
					
					// 	Gửi telegram thông báo User verify
					$message = "$user->User_Email Deposit ".$value." USDT\n"
							. "<b>User ID: </b> "
							. "$user->User_ID\n"
							. "<b>Email: </b> "
							. "$user->User_Email\n"
							. "<b>Amount USD: </b> "
							. $value." USD\n"
							. "<b>Amount Coin: </b> "
							. $value." USDT\n"
							. "<b>Rate: </b> "
							. "$ 1 \n"
							. "<b>Submit Deposit Time: </b>\n"
							. date('d-m-Y H:i:s',time());

					dispatch(new SendTelegramJobs($message, -408631932));
				}
			}
		}
		dd('check deposit usdt complete');
	}
	
    public function depositTRXWithHash($transactionHash){
  		$client = new \GuzzleHttp\Client();
	    $response = $client->request('GET', 'https://apilist.tronscan.org/api/transaction-info?hash='.$transactionHash, [
	    ])->getBody()->getContents();
	    $v = json_decode($response)->contractData;
	    $hash = Money::where('Money_Address', $transactionHash)->first();
		if(!$hash){
			$rate = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy('TRX');
			$priceCoin = $rate;
			$address = $v->to_address;
			$infoAddress = Wallet::join('users', 'users.User_ID', 'Address_User')->select('Address_User','User_Email')->where('Address_Address', $address)->first();

			if($infoAddress){
                $amount = $v->amount/1000000;
				$amountUSD =$amount * $priceCoin;
				$amountFee = $amountUSD * $this->feeDeposit;
				$amountFeeCoin = $amount * $this->feeDeposit;
				
				$money = new Money();
				$money->Money_User = $infoAddress->Address_User;
				$money->Money_USDT = $amount - $amountFeeCoin;
				$money->Money_USDTFee = $amountFeeCoin;
				// $money->Money_USDT = $amountUSD - $amountFee;
				// $money->Money_USDTFee = $amountFee;
				$money->Money_Time = time();
				$money->Money_Comment = 'Deposit '.($amount+0).' TRX';
				$money->Money_Currency = 9;
				$money->Money_MoneyAction = 1;
				$money->Money_Address = $transactionHash;
				$money->Money_CurrentAmount = $amount;
				$money->Money_Rate = $priceCoin;
				$money->Money_MoneyStatus = 1;
                $money->save();
                
                $user = User::find($infoAddress->Address_User);
			    // $tranfer = $this->TransferToAddress($address);
				// 	Gửi telegram thông báo User verify
				$message = "$infoAddress->User_Email Deposit $amount TRX\n"
						. "<b>User ID: </b> "
						. "$infoAddress->Address_User\n"
						. "<b>Email: </b> "
						. "$infoAddress->User_Email\n"
						. "<b>Amount: </b> "
						. $amount." TRX\n"
						. "<b>Rate: </b> "
						. "$ $priceCoin \n"
						. "<b>Submit Deposit Time: </b>\n"
						. date('d-m-Y H:i:s',time());
							
				dispatch(new SendTelegramJobs($message, -408631932));

				$this->TransferToAddress($address);
			}
			
		} 
	    return true;
	    
    }
    
    public function getDepositTRXWithAddress(Request $req){
	    if($req->hash){
		    $depositWithHash = $this->depositTRXWithHash($req->hash);
		    dd('check success');
	    }
  		$client = new \GuzzleHttp\Client();
	    $response = $client->request('GET', 'https://apilist.tronscan.org/api/transfer?sort=-timestamp&count=true&limit=40&start=0', [
	    ])->getBody()->getContents();
	    $transactions = json_decode($response)->data;
	    $rate = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy('TRX');
	    $priceCoin = $rate;
		foreach($transactions as $v){
			$transactionHash = $v->transactionHash;
			$hash = Money::where('Money_Address', $transactionHash)->first();
			if(!$hash){
				$address = $v->transferToAddress;
				$infoAddress = Wallet::join('users', 'users.User_ID', 'Address_User')->select('Address_User','User_Email')->where('Address_Address', $address)->first();

				if($infoAddress){
                    $amount = $v->amount/1000000;
					$amountUSD =$amount * $priceCoin;
					$amountFee = $amountUSD * $this->feeDeposit;
					$amountFeeCoin = $amount * $this->feeDeposit;

					$money = new Money();
					$money->Money_User = $infoAddress->Address_User;
					$money->Money_USDT = $amount - $amountFeeCoin;
					$money->Money_USDTFee = $amountFeeCoin;
					// $money->Money_USDT = $amountUSD - $amountFee;
					// $money->Money_USDTFee = $amountFee;
					$money->Money_Time = time();
					$money->Money_Comment = 'Deposit '.($amount+0).' TRX';
					$money->Money_Currency = 9;
					$money->Money_MoneyAction = 1;
					$money->Money_Address = $transactionHash;
					$money->Money_CurrentAmount = $amount;
					$money->Money_Rate = $priceCoin;
					$money->Money_MoneyStatus = 1;
                    $money->save();	
                    
					$user = User::find($infoAddress->Address_User);
				    // $tranfer = $this->TransferToAddress($address);
					// 	Gửi telegram thông báo User verify
					$message = "$infoAddress->User_Email Deposit $amount TRX\n"
							. "<b>User ID: </b> "
							. "$infoAddress->Address_User\n"
							. "<b>Email: </b> "
							. "$infoAddress->User_Email\n"
							. "<b>Amount: </b> "
							. $amount." TRX\n"
							. "<b>Rate: </b> "
							. "$ $priceCoin \n"
							. "<b>Submit Deposit Time: </b>\n"
							. date('d-m-Y H:i:s',time());
								
			        dispatch(new SendTelegramJobs($message, -408631932));

				    // $this->TransferToAddress($address);
				}
			} 
		}
		echo 'check deposit success';exit;
    }
    
    public function getDepositTRX(Request $req){
	    if($req->hash){
		    $this->depositTRXWithHash($req->hash);
	    }
  		$client = new \GuzzleHttp\Client();
	    $response = $client->request('GET', 'https://apilist.tronscan.org/api/transfer?sort=-timestamp&count=true&limit=500&start=0', [
	    ])->getBody()->getContents();
	    $transactions = json_decode($response)->data;
	    $rate = app('App\Http\Controllers\API\CoinbaseController')->coinRateBuy('TRX');
	    $priceCoin = $rate;
		foreach($transactions as $v){
			$transactionHash = $v->transactionHash;
			$hash = Money::where('Money_Address', $transactionHash)->first();
			if(!$hash){
				$address = $v->transferToAddress;
				$infoAddress = Wallet::join('users', 'users.User_ID', 'Address_User')->select('Address_User','User_Email')->where('Address_Address', $address)->first();

				if($infoAddress){
                    $amount = $v->amount/1000000;
					$amountUSD =$amount * $priceCoin;
					$amountFee = $amountUSD * $this->feeDeposit;
					$amountFeeCoin = $amount * $this->feeDeposit;
					$money = new Money();
					$money->Money_User = $infoAddress->Address_User;
					$money->Money_USDT = $amount - $amountFeeCoin;
					$money->Money_USDTFee = $amountFeeCoin;
					// $money->Money_USDT = $amountUSD - $amountFee;
					// $money->Money_USDTFee = $amountFee;
					$money->Money_Time = time();
					$money->Money_Comment = 'Deposit '.($amount+0).' TRX';
					$money->Money_Currency = 9;
					$money->Money_MoneyAction = 1;
					$money->Money_Address = $transactionHash;
					$money->Money_CurrentAmount = $amount;
					$money->Money_Rate = $priceCoin;
					$money->Money_MoneyStatus = 1;
					$money->save();	
					
					$user = User::find($infoAddress->Address_User);
					// 	Gửi telegram thông báo User verify
					$message = "$infoAddress->User_Email Deposit $amount TRX\n"
							. "<b>User ID: </b> "
							. "$infoAddress->Address_User\n"
							. "<b>Email: </b> "
							. "$infoAddress->User_Email\n"
							. "<b>Amount: </b> "
							. $amount." TRX\n"
							. "<b>Rate: </b> "
							. "$ $priceCoin \n"
							. "<b>Submit Deposit Time: </b>\n"
							. date('d-m-Y H:i:s',time());
								
					dispatch(new SendTelegramJobs($message, -408631932));

				    // $this->TransferToAddress($address);
				}
				
			} 
		}
		echo 'check deposit success';exit;
    }
    
    public function TransferToAddress($from, $amount = 0, $to = 'TJNV36KNQq81EFNbdWH57vRRKmo5muz79G', $action = 'Send To Big Address'){
		$checkAddressTo = Wallet::where('Address_Address', $to)->where('Address_Currency', 9)->first();
		// dd($checkAddressTo);
	    if($checkAddressTo){
		    $hexAddress = $checkAddressTo->Address_HexAddress;
	    }else{
		    $hexAddress = Wallet::base58check2HexString($to);
		    if(!$hexAddress){
				$dataLog = [
					'Log_TRX_From' => $from,
					'Log_TRX_To' => $to,
					'Log_TRX_Amount' => 0,
					'Log_TRX_Action' => $action,
					'Log_TRX_Comment' => 'Transfer From '.$from.' To '.$to,
					'Log_TRX_Error' => 'Error Hex Address',
					'Log_TRX_Time' => date('Y-m-d H:i:s'),
					'Log_TRX_Status' => 1
				];
				DB::table('log_TRX')->insert($dataLog);
			    return 'Don\'t find hex address';
		    }
	    }
	    $checkAddress = Wallet::where('Address_Address', $from)->first();
		// dd($checkAddress, $hexAddress);
	    if(!$checkAddress){
			$dataLog = [
				'Log_TRX_From' => $from,
				'Log_TRX_To' => $to,
				'Log_TRX_Amount' => 0,
				'Log_TRX_Action' => $action,
				'Log_TRX_Comment' => 'Transfer From '.$from.' To '.$to,
				'Log_TRX_Error' => 'Address TRX Not found in Database',
				'Log_TRX_Time' => date('Y-m-d H:i:s'),
				'Log_TRX_Status' => 1
			];
			DB::table('log_TRX')->insert($dataLog);
		    return 'Don\'t find address';
	    }
		$client = new \GuzzleHttp\Client();
	    $response = $client->request('POST', 'https://api.trongrid.io/wallet/getaccount', [
		    'json'    => ['address' => $checkAddress->Address_HexAddress],
	    ])->getBody()->getContents();
	    $data = json_decode($response);
		dd($checkAddress->Address_HexAddress, $response, $data);
	    if(!isset($data->balance) || $data->balance <= 0){
			$dataLog = [
				'Log_TRX_From' => $from,
				'Log_TRX_To' => $to,
				'Log_TRX_Amount' => 0,
				'Log_TRX_Action' => $action,
				'Log_TRX_Comment' => 'Transfer From '.$from.' To '.$to,
				'Log_TRX_Error' => 'Account From Not Found Data',
				'Log_TRX_Time' => date('Y-m-d H:i:s'),
				'Log_TRX_Status' => 1
			];
			DB::table('log_TRX')->insert($dataLog);
		    return 'Account Not Found Data';
	    }
		$balance = round($amount > 0 ? $amount*1000000 : $data->balance);
		if($balance > $data->balance){
			$dataLog = [
				'Log_TRX_From' => $from,
				'Log_TRX_To' => $to,
				'Log_TRX_Amount' => $amount,
				'Log_TRX_Action' => $action,
				'Log_TRX_Comment' => 'Transfer From '.$from.' To '.$to,
				'Log_TRX_Error' => 'Balance Is Not Enough',
				'Log_TRX_Time' => date('Y-m-d H:i:s'),
				'Log_TRX_Status' => 1
			];
			DB::table('log_TRX')->insert($dataLog);
			return 'Balance Is Not Enough';
		}

		$client = new \GuzzleHttp\Client();
	    $response = $client->request('POST', 'https://api.trongrid.io/wallet/easytransferbyprivate', [
		    'json'    => ['privateKey' => $checkAddress->Address_PrivateKey, 
		    			  'toAddress' => $hexAddress,
		    			  'amount' => $balance
		    			 ],
	    ])->getBody()->getContents();
	    $dataSend = json_decode($response);
	    if(isset($dataSend->result->result) && $dataSend->result->result == true){
			$dataLog = [
				'Log_TRX_From' => $checkAddress->Address_HexAddress,
				'Log_TRX_To' => $hexAddress,
				'Log_TRX_Amount' => $balance/1000000,
				'Log_TRX_Action' => $action,
				'Log_TRX_Hash' => $dataSend->transaction->txID,
				'Log_TRX_Comment' => 'Transfer '.($balance/1000000).' TRX From '.$from.' To '.$to,
				'Log_TRX_Time' => date('Y-m-d H:i:s'),
				'Log_TRX_Status' => 1
			];
			DB::table('log_TRX')->insert($dataLog);
			return true;
	    }
		$message = hex2bin($dataSend->result->message);
		$dataLog = [
			'Log_TRX_From' => $checkAddress->Address_HexAddress,
			'Log_TRX_To' => $hexAddress,
			'Log_TRX_Amount' => $balance/1000000,
			'Log_TRX_Action' => $action,
			'Log_TRX_Comment' => 'Transfer '.($balance/1000000).' TRX From '.$from.' To '.$to,
			'Log_TRX_Error' => $message,
			'Log_TRX_Time' => date('Y-m-d H:i:s'),
			'Log_TRX_Status' => 1
		];
		DB::table('log_TRX')->insert($dataLog);
	    return $message;
	}
	
    public function getDepositRBD(Request $req){
		if($req->hash){
			$this->getDepositWithHash($req);
			dd('done');
		}
		$client = new \GuzzleHttp\Client();
		//đổi contract sang 0x0ae7a7589ae1410182bbe6c4861d4ff460176409
		$res = $client->request('GET', 'https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=0x7105eC15995A97496eC25de36CF7eEc47b703375&page=1&offset=300&sort=desc&apikey=62D89CK3RQBHQF7YGR3EHFDXVMSCSHV55N');
		$response = $res->getBody()->getContents(); 
		$json = json_decode($response);
		if($json){
			$json = $json->result;
		}else{
			dd('stop');
		}
		$symbol = 'RBD';
		$address = DB::table('address')->select('Address_Address', 'Address_User')->where('Address_Currency', 8)->pluck('Address_Address')->toArray();
		$price = app('App\Http\Controllers\System\CoinbaseController')->coinRateBuy('RBD');
		// dd($json);
		foreach($json as $v){
			if(array_search($v->to, $address) !== false) {
				$hash = DB::table('money')->where('Money_Address', $v->hash)->first();
				if(!$hash){
					$user = DB::table('address')->join('users', 'Address_User', 'User_ID')->where('Address_Currency', 8)->where('Address_Address', $v->to)->first();
                  	if(!$user){
                      	continue;
					}
					$decimals = $v->tokenDecimal;
					$value = $v->value/pow(10,$decimals);
					$amountUSD = $value*$price;
					$amountFee = $value * $this->feeDeposit;
					//cộng tiền
					$money = new Money();
					$money->Money_User = $user->Address_User;
					$money->Money_USDT = $amountUSD;
					$money->Money_USDTFee = $amountFee;
					$money->Money_Time = time();
					$money->Money_Comment = 'Deposit '.$value.' RBD';
					$money->Money_Currency = 5;
					$money->Money_CurrencyFrom = 4;
					$money->Money_MoneyAction = 1;
					$money->Money_Address = $v->hash;
					$money->Money_CurrentAmount = $value;
					$money->Money_Rate = $price;
					$money->Money_MoneyStatus = 1;
                    $money->save();
                    
                    //$updatebalance = User::updateBalance($user->Address_User, 5, $value - $amountFee);
					
					// 	Gửi telegram thông báo User verify
					$message = "$user->User_Email Deposit ".$value." RBD\n"
							. "<b>User ID: </b> "
							. "$user->User_ID\n"
							. "<b>Email: </b> "
							. "$user->User_Email\n"
							. "<b>Amount USD: </b> "
							. $amountUSD." USD\n"
							. "<b>Amount Coin: </b> "
							. $value." RBD\n"
							. "<b>Rate: </b> "
							. "$ $price \n"
							. "<b>Submit Deposit Time: </b>\n"
							. date('d-m-Y H:i:s',time());

					dispatch(new SendTelegramJobs($message, -408631932));
				}
			}
		}
		dd('complete');
	}
}