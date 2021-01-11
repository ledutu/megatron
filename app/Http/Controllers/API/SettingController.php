<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth;
use Validator,Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use App\Model\Eggs;

use DB;

class SettingController extends Controller{

	
	public $keyHash	 = 'DAFCOCoorgsafwva'; 
	
    
    public function getSetting(Request $req){
		// $totalEgg = DB::table('eggsTemp')->sum('amount');
		$total = Eggs::count();
		$minDepositGame = app('App\Http\Controllers\API\AgGameController')->minDeposit;
		$minWithdrawGame = app('App\Http\Controllers\API\AgGameController')->minWithdraw;
		$minBattleGameEUSD = app('App\Http\Controllers\API\GameController')->minEUSD;
		$minBattleGameGOLD = app('App\Http\Controllers\API\GameController')->minGold;
		//$minBattleGameEUSD = 10;
		//$minBattleGameGOLD = 150;
	    $arrayReturn = array(
		    'version' => 1,
		    'facebook' => 'http://bit.ly/eggs-book',
		    'telegram' => 'https://t.me/eggsbookgroup1',
			'telegram_group' => 'https://t.me/eggsbookchannel1',
			'youtube' => 'http://bit.ly/eggs-book-project',
			'twitter' => 'https://twitter.com/EggsbookC',
			'download' => 'https://eggsbook.com/',
			'price_egg' => 200,
			'fee_sell_egg' => 0.05,
			'sell_gold_rate' => 150,
			'array_rate_gold' => ["2000" => 200, "9000" => 180, "15000" => 150],
			'sell_gold_min' => 2000,
			'min_deposit_game' => $minDepositGame,
			'min_withdraw_game' => $minWithdrawGame,
			'version_lang' => 1.5,
			'battle_game' => [
              	'min_deposit_eusd' => $minBattleGameEUSD,
              	'min_deposit_gold' => $minBattleGameGOLD,
              	'min_withdraw_eusd' => $minBattleGameEUSD,
              	'min_withdraw_gold' => $minBattleGameGOLD,
            ],
			'total' => 10000-$total, // số lượng còn lại có thể mua
			'remainEgg' => 10000 - $total < 0 ? 0 : (10000 - $total),
		);
		return $this->response(200, ['data'=>$arrayReturn]);
	    
    }
    
    public function getSlide(){
		$slide = array(
			array(
				'img'=>'https://dafco.org/bg-app.jpg',
				'link'=>null
			),
			array(
				'img'=>'https://dafco.org/assets/images/envi/hst-1.jpg',
				'link'=>null
			),
			array(
				'img'=>'https://dafco.org/assets/images/envi/hst-2.jpg',
				'link'=>null
			),
			array(
				'img'=>'https://dafco.org/assets/images/envi/hst-3.jpg',
				'link'=>null
			),
			array(
				'img'=>'https://dafco.org/assets/images/envi/hst-4.jpg',
				'link'=>null
			),
			array(
				'img'=>'https://dafco.org/assets/images/envi/hst-5.jpg',
				'link'=>null
			)
			
		);
		return response(array('status'=>true, 'data'=>$slide), 200);
	    
    }
    
    public function getListCoin(){
	    include(app_path() . '/functions/xxtea.php');
	    // cập nhật giá token bên app
		$tokenPrice = DB::table('changes')->where('Changes_Time', date('Y-m-d'))->where('Changes_Hour', '<=', date('H'))->orderBy('Changes_Hour', 'DESC')->first();
		if(!$tokenPrice){
			$getPrice = DB::table('changes')->where('Changes_Time', '<', date('Y-m-d'))->orderByDesc('Changes_Time')->first();
			$data = ['Changes_Price'=>$getPrice->Changes_Price, 'Changes_Time'=>date('Y-m-d'), 'Changes_Status'=>1 ];
			DB::table('changes')->insert($data);
			$tokenPrice = DB::table('changes')->where('Changes_Time', date('Y-m-d'))->first();
		}
		//end
	    $data = $this->getAPI(7200);
	    $i = 0;
	    $arrUSDT = [];
	    foreach($data as $key=>$v){
		    if(strpos($v['symbol'], 'USDT')){
			    $arrUSDT[] = $v['symbol'];
			    $symbol = str_replace('USDT', '', $v['symbol']);
				$img = "http://bin.bnbstatic.com/static/images/home/coin-logo/BNB.png";
				$coinArr[$symbol] = array(
									'Name'=>$symbol,
									'Symbol'=>$symbol,
									'icon'=> $img,
									'Price'=>  $v['price'],
									'PecentPlus'=> 0
								);
				$i++;
				if($i == 15){
					break;
				}
		    }
	    }
		return response(base64_encode(xxtea_encrypt(json_encode(array('status'=>true, 'data'=>$coinArr)),$this->keyHash)), 200);
	    
    }
    public function getAPI($second = 0){
	    $jsonString = file_get_contents('https://www.binance.com/api/v3/ticker/price');
		$data = json_decode($jsonString, true);
		$result = $data;
		return $result;
	    $result = '';
	    $continue = 0;
	    $fileList = glob('json/*');
	    $findExist = array_search("json/api.json",$fileList);
	    if($findExist === false){
		    $jsonString = file_get_contents('https://api.coinmarketcap.com/v2/ticker');
			$data = json_decode($jsonString, true);
			$result = $data;
			$data['time'] = time();
			$newJsonString = json_encode($data, JSON_PRETTY_PRINT);
			file_put_contents(public_path('json/api.json'), stripslashes($newJsonString));
			$continue = 1;
	    }elseif($findExist !== false || $continue == 1){

			$contentRead = file_get_contents(public_path('json/api.json'));
			$dataRead = json_decode($contentRead, true);
			$curTime = (int)(time() - $dataRead->time);

			if($curTime > $second){
				$jsonString = file_get_contents('https://api.coinmarketcap.com/v2/ticker');
				$dataRead = json_decode($jsonString);
				$dataRead->time = time();
				$newJsonString = json_encode($dataRead, JSON_PRETTY_PRINT);
				file_put_contents(public_path('json/api.json'), stripslashes($newJsonString));
				$result = $newJsonString->data;
			}else {
				$result = $dataRead->data;
			}
	    }
	    return $result;
  	}
  	

}