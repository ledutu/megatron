<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Eggs;
use App\Model\Pools;
use App\Model\User;
use App\Model\LogUser;
use App\Model\Money;
use App\Model\EggTypes;
use App\Model\Item;
use Validator;


use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Value\Money as CB_Money;
use Coinbase\Wallet\Enum\Param;
use GuzzleHttp\Client as G_Client;


class TestController extends Controller
{

    public function __construct()
    {
        // $this->middleware('localization');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public static function coinbase(){
        $apiKey = '1Yi6TxXCnrHAHPn2';
        $apiSecret = 'tZibO4H3pcDTot9NvV3MkKwgIIkLuqKD';

        $configuration = Configuration::apiKey($apiKey, $apiSecret);
        $client = Client::create($configuration);

        return $client;
    }
    public function quocTest(){
	    $accountETH = $this->coinbase()->getAccount('ETH');
	    $accountBTC = $this->coinbase()->getAccount('BTC');
	    dd($accountETH, $accountBTC);
	    $transaction = Transaction::send();

		$transaction->setToBitcoinAddress('0xcd24f8e5492a172955875880e8cf4fcb1185da8e');
		$transaction->setBitcoinAmount(1.42);
		$transaction->setDescription('For being awesome!');
		//$transaction->setFee('0.0001');
		
		try {
			$account = $this->coinbase()->getAccount('ETH');
			$a = $this->coinbase()->createAccountTransaction($account, $transaction);

			dd($a);
		}
		catch (\Exception $e) {
		// Update
			dd($e->getMessage());
		}

	    
	    
	    dd('VVVV');
    }
    public function putEggFullPool(Request $request)
    {
        $pool = Pools::where('ID', $request->pool_id)->first();

        if (!$pool) return $this->response(200, [], 'Hồ không tồn tại nha bạn! Nhập đúng tên hồ dùm cái', [], false);

        if (count(Eggs::where(['Pool' => $request->pool_id, 'Owner' => $request->user_id])->get()) == $pool->poolType->MaxFish) {
            return $this->response(200, [], 'Hồ đầy rồi má', [], false);
        }

        $pool->poolType;
        $eggs = Eggs::where([
            'Owner' => $request->user_id,
            'Status' => 1,
            'Pool' => "0",
        ])->get();

        foreach ($eggs as $egg) {
            $total_egg = Eggs::where(['Pool' => $request->pool_id, 'Owner' => $request->user_id])->get();
            if (count($total_egg) == $pool->poolType->MaxFish) {
                break;
            }

            $egg->Pool = $request->pool_id;
            $egg->PosX = rand(-6000, 6000) / 1000;
            $egg->PosY = rand(-3500, -4500) / 1000;
            $egg->save();
        }

        return $this->response(200, [], 'Xong rồi đó vào check lại đi! Không thành công thì liên hệ ledutu <3');
    }

    /**
     * @param pool_id
     * @param user_id
     * @param currency_id
     */
    public function activeAllEgg(Request $request)
    {
        $pool = Pools::where('ID', $request->pool_id)->first();
        if (!$pool) return $this->response(200, [], 'Nhập đúng ID hồ dùm cái', [], false);

        $eggs = Eggs::where([
            'Pool' => $request->pool_id,
            'Status' => 1,
            'Owner' => $request->user_id,
            'ActiveTime' => 0,
        ])->get();

        $user = User::where('User_ID', $request->user_id)->first();
        $balance = User::getBalance($request->user_id, $request->currency_id);
        if (!$balance) {
            return $this->response(200, [], 'Balance không tồn tại', [], false);
        }

        foreach ($eggs as $egg) {

            $eggType = EggTypes::where('Type', $egg->Type . "")->first();

            if ($request->currency_id != 3 && $request->currency_id != 9) {
                return $this->response(200, [], 'Currency does not exits!', [], false);
            }

            if ($request->currency_id == 3) {
                $price = $eggType->ActiveCost;
            }
            if ($request->currency_id == 9) {
                $price = $eggType->ActiveGold;
            }
            if ($price > $balance) {
                return $this->response(200, ['balance' => $balance], 'Your balance is not enough', [], false);
            }

            $ActiveTime = time();
            // $HatchesTime = rand($eggType->HatchesTime[0], $eggType->HatchesTime[1]);    //test
            // if (time() >= strtotime('2020-10-16')) {
            $HatchesTime = rand($eggType->HatchesTime[0], $eggType->HatchesTime[1]);
            // } else {
            //     $HatchesTime = 99999999;
            // }
            $arrayInsert = array();
            $arrayInsert[] = array(
                'Money_User' => $request->user_id,
                'Money_USDT' => -$price,
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => 'Active Eggs ID:' . $request->egg,
                'Money_MoneyAction' => 30,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => $request->currency_id,
                'Money_CurrentAmount' => $price,
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_Confirm_Time' => null,
                'Money_FromAPI' => 1,
            );
            Money::insert($arrayInsert);

            $egg->WaitingActive = 0;
            $egg->ActiveTime = $ActiveTime;
            $egg->HatchesTime = $HatchesTime;
            $egg->save();

            // active cap cho nguoi choi
            if ($user->User_Level_Active == 0) {
                User::where('User_ID', $request->user_id)->update(['User_Level_Active' => 1]);
            }
            // len cap cho nguoi gioi thieu 
            $countLevel = User::where('User_Parent', $user->User_Parent)->whereIn('User_Level_Active', [1, 2, 3])->count('User_ID');
            $User_Level_Active = 0;
            if ($countLevel >= 2) {
                $User_Level_Active = 2;
            }
            if ($countLevel >= 5) {
                $User_Level_Active = 3;
            }
            if ($User_Level_Active) {
                User::where('User_ID', $user->User_Parent)->update(['User_Level_Active' => $User_Level_Active]);
            }
            $activeEggsType = config('utils.action.active_eggs');
            LogUser::addLogUser($request->user_id, $activeEggsType['action_type'], 'Active Eggs ID:' . $request->egg, $request->ip(), 30);

            $arrParent = explode(',', $user->User_Tree);
            $arrParent = array_reverse($arrParent);

            //check commission buy egg
            Money::checkCommission($user, 5, 3, 200);

            //check commission active egg

            Money::checkCommission($user, 6, $request->currency_id, $price);

            for ($i = 0; $i < count($arrParent); $i++) {
                $userParent = $arrParent[$i];
                User::checkLevelUser($userParent);
            }
        }

        return $this->response(200, [
            'balance' => [
                'EUSD' => User::getBalance($request->user_id, 3),
                'GOLD' => User::getBalance($request->user_id, 9),
            ],
        ], 'Active xong rồi đó');
    }

    /**
     * @param pool_id
     * @param user_id
     */
    public function removeAllEgg(Request $request)
    {
        Eggs::where([
            'Pool' => $request->pool_id,
            'Owner' => $request->user_id,
            'Status' => 1
        ])->delete();

        return $this->response(200, [], 'Xóa thành công. Nếu chưa xóa được liên hệ ledutu');
    }

    public function test(Request $request)
    {
        $user = $request->user();
        $item = Item::where('ID', 'I8520296769')->first();
        $time = time();
        $timeGrowUp = $time - $item->PoolTime;

        return $this->response(200, [
            $item->PoolTime,
            $item->Data['GrowTime'],
            $item->Data['Status'],
            $item->Status,
            $item->itemTypes->Data['HarvestDuration'],
            $item->itemTypes->Data['FeedCreating']
        ]);

        if ($timeGrowUp >= $item->Data->GrowTime && $item->Status == 1) {
            $item->Data->Status = 2;
            $item->save();
            return 0;
        }

        $remainLiveTime = $time - $item->PoolTime;
        if ($remainLiveTime >= $item->LiveTime) {
            $item->Status = -1;
            $item->save();
            return 0;
        }

        $havestTime = $time - $item->UpdateTime;
        if ($havestTime >= $item->itemTypes->Data->HarvestDuration) {
            $food = Foods::where([
                'Owner' => $user->User_ID,
            ])->first();
            $food->Amount += $item->itemTypes->Data->FeedCreating;
            $food->save();
            return $item->itemTypes->Data->FeedCreating;
        }
    }

    public function testCaptcha(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = ['secret' => '6LcavdMZAAAAAHsvWX1RxjMpPQjZBXge4bFedMIQ', 'response' => $request->token];
        $options = ['http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]];
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $response_keys = json_decode($response, true);
        if (!$response_keys['success']) {
            $this->error_codes = $response_keys['error-codes'];
        }
        return $this->response(200, ['token' => $response_keys['success']]);
    }

    public function waitingActive(Request $request){
        $lang = app()->getLocale();
        // $trans = new TranslateClient('en', app()->getLocale());
        // echo $trans->translate('Hello');
        return $this->response(200, [], __('hello'));
        // return $this->response(200, [], __('app.test'));
    }
}
