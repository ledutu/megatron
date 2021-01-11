<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Value\Money as CB_Money;
use Coinbase\Wallet\Enum\Param;
use DB;

use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;

use PayusAPI\Http\Client as PayusClient;
use PayusAPI\Resources\Payus;

use GuzzleHttp\Client as G_Client;

use App\Model\Wallet;
class CoinbaseController extends Controller{
	public $access_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHBfaWQiOiI1ZGM1MzNhZWQ0NWMwNDJmZTdhY2FlYWQiLCJhcGlfa2V5IjoiWlczTjlLRjVRR00zTks0TkZNTktKQTlMVjZGTFNLNkk3RiIsInVzZXJfaWQiOiI1ZGM1MzI0ZWQ0NWMwNDJmZTdhY2FlODYiLCJpYXQiOjE1NzMyMDQ5MTN9.RdPKuEYcurqtQpNBE38lxTdDqXgbjOZqBNYexRBRVQI';
    
	public static function coinbase(){
        $apiKey = 'nbZclTlYvz5mkhNN';
        $apiSecret = 'AUHqsUV0lyLHd9H7RMWtIVhwDpYOOWhG';

        $configuration = Configuration::apiKey($apiKey, $apiSecret);
        $client = Client::create($configuration);

        return $client;
    }

	public function Payus(){
		$access_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHBfaWQiOiI1ZGM1MzNhZWQ0NWMwNDJmZTdhY2FlYWQiLCJhcGlfa2V5IjoiWlczTjlLRjVRR00zTks0TkZNTktKQTlMVjZGTFNLNkk3RiIsInVzZXJfaWQiOiI1ZGM1MzI0ZWQ0NWMwNDJmZTdhY2FlODYiLCJpYXQiOjE1NzMyMDQ5MTN9.RdPKuEYcurqtQpNBE38lxTdDqXgbjOZqBNYexRBRVQI';
		
	    $client = new PayusClient(['access_token' => $access_token]);
		$payus = new Payus($client);
		
		return $payus;
	}
	
    public static function coinRateBuy($system = null){
	    if($system == 'ETH' || $system == 'BTC' || $system == 'TRX'){
			// $coin[$system] = json_decode(file_get_contents('https://api.binance.com/api/v1/ticker/price?symbol='.$system.'USDT'))->price;
			$getLastedPrice = DB::table('rate')->where('rate_Symbol', $system)->orderByDesc('rate_ID')->first();
			if(!$getLastedPrice || (time()- $getLastedPrice->rate_Time >= $getLastedPrice->rate_Duration)){
				$timeChange = rand(120, 300);
                $price = json_decode(file_get_contents('https://api.binance.com/api/v1/ticker/price?symbol='.$system.'USDT'))->price;
			    $data = [
				    'rate_Amount' => $price,
				    'rate_Time' => time(),
                    'rate_Symbol' => $system,
                    'rate_Duration' => $timeChange,
				    'rate_Log' => 'From Admin',
			    ];
			    DB::table('rate')->insert($data);
			}else{
				$price = $getLastedPrice->rate_Amount;
			}
			$coin[$system] = $price;
	    }elseif($system == 'DASH' || $system == 'EOS' || $system == 'LTC' || $system == 'BCH' || $system == 'XRP'){
			// $coin[$system] = self::coinbase()->getBuyPrice($system.'-USD')->getAmount();
			$getLastedPrice = DB::table('rate')->where('rate_Symbol', $system)->orderByDesc('rate_ID')->first();
			if(!$getLastedPrice || (time()- $getLastedPrice->rate_Time >= $getLastedPrice->rate_Duration)){
				$timeChange = rand(120, 300);
                $price = self::coinbase()->getBuyPrice($system.'-USD')->getAmount();
			    $data = [
				    'rate_Amount' => $price,
				    'rate_Time' => time(),
                    'rate_Symbol' => $system,
                    'rate_Duration' => $timeChange,
				    'rate_Log' => 'From Admin',
			    ];
			    DB::table('rate')->insert($data);
			}else{
				$price = $getLastedPrice->rate_Amount;
			}
			$coin[$system] = $price;
		}elseif($system == 'RBD'){
			/*
			$tokenPrice = DB::table('changes')->where('Changes_Time', '>=', date('Y-m-d H:i:00'))->whereRaw('MINUTE(Changes_Time) = '.date('i'))->orderBy('Changes_Time', 'DESC')->first();
			if(!$tokenPrice){
				$ticker = json_decode(file_get_contents('https://coinsbit.io/api/v1/public/ticker?market=RBD_USDT'));
				if(isset($ticker->result)){
					$priceRBD = ($ticker->result->bid+$ticker->result->ask)/2;
				    // $priceRBD = ($ticker->result->ask);
					$coin['RBD'] = $priceRBD;
					$data = ['Changes_Price'=>$coin['RBD'], 'Changes_Time'=>date('Y-m-d H:i:s'), 'Changes_Status'=>1, 'Log' => 'coinRateBuy!' ];
				}else{
					$getPrice = DB::table('changes')->orderByDesc('Changes_Time')->first();
					$data = ['Changes_Price'=>$getPrice->Changes_Price, 'Changes_Time'=>date('Y-m-d H:i:s'), 'Changes_Status'=>1, 'Log' => 'coinRateBuy!' ];
				}
				DB::table('changes')->insert($data);
				
			}else{
				$coin['RBD'] = $tokenPrice->Changes_Price;
			}
			*/
			$getLastedPrice = DB::table('rate')->where('rate_Symbol', $system)->orderByDesc('rate_ID')->first();
			if(!$getLastedPrice || (time()- $getLastedPrice->rate_Time >= $getLastedPrice->rate_Duration)){
				$price = rand(80000, 110000)/10000000;
				$timeChange = rand(600, 900);
				$data = [
					'rate_Amount' => $price,
					'rate_Time' => time(),
                    'rate_Symbol' => $system,
					'rate_Log' => 'From Admin',
					'rate_Duration' => $timeChange,
				];
				DB::table('rate')->insert($data);
			}else{
				$price = $getLastedPrice->rate_Amount;
			}
			$coin['RBD'] = $price;
		}elseif($system == 'EBP'){
			$price = DB::table('changes')->orderBy('Changes_ID', 'DESC')->first();
			$coin['EBP'] = $price->Changes_Price;
		}else{
		    // $coin['BTC'] = self::coinbase()->getBuyPrice('BTC-USD')->getAmount();
			// $coin['ETH'] = self::coinbase()->getBuyPrice('ETH-USD')->getAmount();
			$listCoins = DB::table('currency')->whereIn('Currency_ID', [1,2,4,8,10,11,12,13,14,15])->get();
			$coin = [];
			foreach($listCoins as $listcoin){
				$coin[$listcoin->Currency_Symbol] = self::coinRateBuy($listcoin->Currency_Symbol);
			}
			// $coin['BTC'] = json_decode(file_get_contents('https://api.binance.com/api/v1/ticker/price?symbol=BTCUSDT'))->price;
		    // $coin['ETH'] = json_decode(file_get_contents('https://api.binance.com/api/v1/ticker/price?symbol=ETHUSDT'))->price;
			// $price = DB::table('changes')->orderBy('Changes_ID', 'DESC')->first();
			// $coin['EBP'] = $price->Changes_Price;

	    }
	   
		$coin['USDT'] = 1;
		$coin['EUSD'] = 1;
		$coin['USD'] = 1;

	    if($system){
		    return $coin[$system];
		}
		
	    return $coin;
    }
    
    public static function getAccountTransactions($symbol){
	    $account = self::coinbase()->getAccount($symbol);
	    
        $transactions = self::coinbase()->getAccountTransactions($account);

	    return $transactions;
    }
    
    public static function getAccountDeposit($symbol){
	    $account = self::coinbase()->getAccount($symbol);
	    $transactions = self::coinbase()->getAccountDeposit($account);
	    return $transactions;
    }
    
    public function getCoinbase(Request $req){
		
		if(!$req->Coin){
		    $coin = 'BTC';
	    }else{
		    $coin = $req->Coin;
	    }
	    $account = $this->coinbase()->getAccount($coin);
	    $balance = $account->getbalance()->getamount();

		
        $transactions = $this->coinbase()->getAccountTransactions($account, [
		]);
		
		$excel = array();
		$i = 0;
		foreach($transactions as $v){
			if($i==0){
				$plus = 0;
			}else{
				$plus = $transactions[$i-1]->getamount()->getamount();
			}
			if($v->getdescription() != null){
				$getdescription = $v->getdescription();
				
			}else{
				$getdescription = 'User Deposit';
			}
			array_push($excel, array(
				$i+1,
				$v->getcreatedAt()->format('Y-m-d H:i:s'),
				number_format($balance + $plus, 8),
				$v->getamount()->getamount(),
				$v->getnetwork()->gethash(),
				$getdescription
			));
			$i++;
		}
		if(Input::get('export')){
			if(Session('user')->User_Level != 1 && Session('user')->User_Level != 2){
				dd('stop');
			}
			$history = $excel;

			$listHistory = array();
			
			//xuất excel
			$listHistoryExcel[] = array('ID','Time', 'Balance', 'Amount','Description','Transaction ID');
			$i = 1;
			
			foreach ($history as $d)
			{
				$listHistoryExcel[$i][0] = $d[0];
				$listHistoryExcel[$i][1] = $d[1];
				$listHistoryExcel[$i][2] = $d[2];
				$listHistoryExcel[$i][3] = $d[3];
				$listHistoryExcel[$i][4] = $d[5];
				$listHistoryExcel[$i][5] = $d[4];
				$i++;
			}
			Excel::create('Transaction-'.$coin.''.date('YmdHis'), function($excel) use ($listHistoryExcel, $coin) {
				$excel->setTitle('Transaction-'.$coin.''.date('YmdHis'));
				$excel->setCreator('Transaction-'.$coin.''.date('YmdHis'))->setCompany('SBANK');
				$excel->setDescription('Transaction-'.$coin.''.date('YmdHis'));
				$excel->sheet('sheet1', function ($sheet) use ($listHistoryExcel) {
					$sheet->fromArray($listHistoryExcel, null, 'A1', false, false);
				});
			})->download('xls');
		}
		return view('System.Admin.Admin-Coinbase');
	}
	
	public static function createAddress($coin, $user){
	    switch ($coin) {
			case 1:
				$account = self::coinbase()->getAccount('BTC');
	            $address = new Address([
	            	'name' => 'New Address BTC of ID:'.$user
	            ]);
	            $info = self::coinbase()->createAccountAddress($account, $address);
	
	            $btcAddress = $info->getaddress();
	
	            $addressArray = array(
					'name'=>'BTC',
					'address'=>$btcAddress,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl=bitcoin:'.$btcAddress.'&choe=UTF-8'
				);
		        break;
		    case 2:
		        // eth
		        $account = self::coinbase()->getAccount('ETH');
				$address = new Address([
	            	'name' => 'New Address ETH of ID:'.$user
				]);
	            $info = self::coinbase()->createAccountAddress($account, $address);
	
	            $ethAddress = $info->getaddress();
	            $addressArray = array(
					'name'=>'ETH',
					'address'=>$ethAddress,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$ethAddress.'&choe=UTF-8'
				);
		        
		        break;
			case 4:
				// rbd
				$client = new \GuzzleHttp\Client(); 
				$res = $client->request('GET', 'https://coinbase.rezxcvbnm.co/public/address?key=JOf9HkPAPEJelIrOmMdIPwW2IzoIvimQ1Qy2jp01bksxr3dE1x');
				
				$json = json_decode($res->getBody());
				$addressArray = array(
					'name'=>'RBD (ERC-20)',
					'address'=>$json->address,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$json->address.'&choe=UTF-8'
				);
				
				break;
			case 10:
				// dash
				$account = self::coinbase()->getAccount('DASH');
				$address = new Address([
					'name' => 'New Address DASH of ID:'.$user
				]);
				$info = self::coinbase()->createAccountAddress($account, $address);
	
				$dashAddress = $info->getaddress();
				$addressArray = array(
					'name'=>'DASH',
					'address'=>$dashAddress,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$dashAddress.'&choe=UTF-8'
				);
				
				break;
			case 11:
				// BCH
				$account = self::coinbase()->getAccount('BCH');
				$address = new Address([
					'name' => 'New Address BCH of ID:'.$user
				]);
				$info = self::coinbase()->createAccountAddress($account, $address);
	
				$bchAddress = $info->getaddress();
				$addressArray = array(
					'name'=>'BCH',
					'address'=>$bchAddress,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$bchAddress.'&choe=UTF-8'
				);
				
				break;
			case 12:
				// ltc
				$account = self::coinbase()->getAccount('LTC');
				$address = new Address([
					'name' => 'New Address LTC of ID:'.$user
				]);
				$info = self::coinbase()->createAccountAddress($account, $address);
	
				$ltcAddress = $info->getaddress();
				$addressArray = array(
					'name'=>'LTC',
					'address'=>$ltcAddress,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$ltcAddress.'&choe=UTF-8'
				);
				
				break;
			case 14:
				// EOS
				$account = self::coinbase()->getAccount('EOS');
				$address = new Address([
					'name' => 'New Address EOS of ID:'.$user
				]);
				$info = self::coinbase()->createAccountAddress($account, $address);
	
				$eosAddress = $info->getaddress();
				$addressArray = array(
					'name'=>'EOS',
					'address'=>$eosAddress,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$eosAddress.'&choe=UTF-8'
				);
				
				break;
			case 15:
				// XRP
				$account = self::coinbase()->getAccount('XRP');
				$address = new Address([
					'name' => 'New Address XRP of ID:'.$user
				]);
				$info = self::coinbase()->createAccountAddress($account, $address);
	
				$xrpAddress = $info->getaddress();
				$addressArray = array(
					'name'=>'XRP',
					'address'=>$xrpAddress,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$xrpAddress.'&choe=UTF-8'
				);
				
				break;
			case 5:
				
				// usdt
				$client = new \GuzzleHttp\Client(); 
				$res = $client->request('GET', 'https://coinbase.rezxcvbnm.co/public/address?key=JOf9HkPAPEJelIrOmMdIPwW2IzoIvimQ1Qy2jp01bksxr3dE1x');
				
				$json = json_decode($res->getBody());
				$addressArray = array(
					'name'=>'USDT (ERC-20)',
					'address'=>$json->address,
					'Qr'=>'https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl='.$json->address.'&choe=UTF-8'
				);
				
				break;
		    
		}
		if(isset($addressArray)){
			return $addressArray;
		}
		return false;
	}
	
	public function createAddressUSDT(){
		
		$key = 'mj2ndXGskiNGB2inDprZ2i9AsnegdFPwxrlf0flkyCnVCzk3mp';
		$content = file_get_contents("https://tech.rezxcvbnm.co/public/address?key=$key");
		return $content;
	}
}
