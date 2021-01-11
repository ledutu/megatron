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
use App\Model\EggTypes;
use App\Model\Eggs;
use App\Model\Pools;
use App\Model\LogUser;
use App\Model\Item;
use App\Model\ItemTypes;
use App\Model\ItemHistory;
use App\Model\Log;
use App\Model\EggbookTest;

use App\Model\Fishs;
use App\Model\FishTypes;
use DateTime;

use App\Jobs\EggJobs;




class EggController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['autoActiveEgg']);
        $this->middleware('userPermission', ['only' => ['scanningEgg']]);
    }

    public function getTransferEgg(Request $req){
        $user = $req->user();
        if(!$user->User_TransferEgg){
            return $this->response(200, [], __('app.permission_denied'), [], false);
        }
        $eggs = Log::where('Log_User', $user->User_ID)->where('Log_Action', 'Transfer Eggs');
        if (Input::get('user_give')) {
            $eggs->where('Log_Comment', 'like', "%".Input::get('user_give')."%");
        }
        if (Input::get('eggs_id')) {
            $eggs->where('Log_Comment', 'like', "%".Input::get('eggs_id')."%");
        }
        if (Input::get('from') && !Input::get('to')) {
            $from = strtotime(Input::get('from'));
            $eggs->where('Log_CreatedAt', '>=', date('Y-m-d H:i:s', $from));
        }
        if (Input::get('to') && !Input::get('from')) {
            $to = strtotime('+1 day', strtotime(date('Y-m-d', strtotime(Input::get('to')))));
            $eggs->where('Log_CreatedAt', '<', date('Y-m-d H:i:s', $to));
        }
        if (Input::get('to') && Input::get('from')) {
            $from = strtotime(Input::get('from'));
            $to = strtotime('+1 day', strtotime(date('Y-m-d', strtotime(Input::get('to')))));
            $eggs->whereBetween('Log_CreatedAt', [date('Y-m-d H:i:s', $from), date('Y-m-d H:i:s', $to)]);
        }
        $eggs = $eggs->paginate(25);
        $balance['Egg'] = User::getBalanceTransferEggs($user->User_ID);
        return $this->response(200, ['List'=>$eggs, 'Balance'=>$balance], '', [], true);
    }

    public function postTransferEgg(Request $req){
        $validator = Validator::make($req->all(), [
            'User' => 'required|nullable|string',
            'Otp' => 'required|nullable|string',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }
        $user = $req->user();
        if(!$user->User_TransferEgg){
            return $this->response(200, [], __('app.permission_denied'), [], false);
        }
        $userGive = User::where('User_ID', $req->User)->orWhere('User_Email', $req->User)->first();
        if(!$userGive){
            return $this->response(200, [], __('app.user_give_egg_is_not_found'), [], false);
        }
        $google2fa = app('pragmarx.google2fa');
        $AuthUser = GoogleAuth::select('google2fa_Secret')->where('google2fa_User', $user->User_ID)->first();
        if(!$AuthUser){
            return $this->response(200, [], __('app.please_enable_authentication_code'), [], false);
        }
        $valid = $google2fa->verifyKey($AuthUser->google2fa_Secret, $req->Otp);
        if(!$valid){
            return $this->response(200, [], __('app.authentication_code_is_wrong'), [], false);
        }
        if($req->Quantity && $req->Quantity > 1){
            //transfer nhiều trứng 1 lần
            $quantity = (int)$req->Quantity;
            $listEggs = Eggs::where('Owner', (string)$user->User_ID)->where('Status', 1)->where('ActiveTime', 0)->where('Pool', "0")->limit($quantity)->get();
            if($listEggs->count() < $quantity){
                return $this->response(200, [], __('app.your_egg_is_not_enough'), [], false);
            }
            $eggID = [];
            foreach($listEggs as $egg){
                $egg->Owner = $userGive->User_ID;
                $egg->BuyFrom = "Give From ".$user->User_ID;
                $egg->Pool = "0";
                $egg->save();
                ItemHistory::addHistory($userGive->User_ID, $egg->ID, 'Give Egg :'.$egg->ID.' From '.$user->User_ID, time());
                $eggID[] = $egg->ID;
            }
            $listEggsID = implode(", ",$eggID);
            Log::insertLog($user->User_ID, 'Transfer Eggs', $quantity, "Transfer List Egg: ".$listEggsID." To User ID: ".$userGive->User_ID);
            $balance['Egg'] = User::getBalanceTransferEggs($user->User_ID);
            return $this->response(200, ['Balance'=>$balance], __('Transfer Eggs: '.$listEggsID.' Successful!'), [], true);
        }else{
            //transfer 1 trứng 1 lần
            $checkEggs = Eggs::where('Owner', (string)$user->User_ID)->where('Status', 1)->where('ActiveTime', 0)->where('Pool', "0");
            if($req->egg){
                $checkEggs = $checkEggs->where('ID', $req->egg);
            }
            $checkEggs = $checkEggs->first();
            // $checkEggs = Eggs::where('Owner', (string)$user->User_ID)->where('Status', 1)->where('ActiveTime', 0)->first();
            if(!$checkEggs){
                return $this->response(200, [], __('app.your_egg_is_not_enough'), [], false);
            }
            $checkEggs->Owner = $userGive->User_ID;
            $checkEggs->BuyFrom = "Give From ".$user->User_ID;
            $checkEggs->Pool = "0";
            $checkEggs->save();
            ItemHistory::addHistory($userGive->User_ID, $checkEggs->ID, 'Give Egg :'.$checkEggs->ID.' From '.$user->User_ID, time());
            Log::insertLog($user->User_ID, 'Transfer Eggs', 1, "Transfer Egg: ".$checkEggs->ID." To User ID: ".$userGive->User_ID);
            $balance['Egg'] = User::getBalanceTransferEggs($user->User_ID);
            return $this->response(200, ['Balance'=>$balance], __('Transfer Eggs: '.$checkEggs->ID.' Successful!'), [], true);
        }
    }

    public function getHistoryBuyEggs()
    {
        $result = DB::table('eggsTemp')->paginate(100);

        return $this->response(200, $result, '', [], true);
    }

    public function getStaticEggs(Request $req)
    {
        $user = Auth::user();
        if ($user) {
            $Eggs = Eggs::whereIn('Status', [1, 2])->select('ID', 'Owner', 'ActiveTime', 'BuyDate', 'Pool')->orderBy('ActiveTime', 1)->get();
            $EggUnActive = $Eggs->where('ActiveTime', 0)->count();
            $EggActivated = $Eggs->where('ActiveTime', '>=', 0)->count();
            if ($Eggs) {
                return $this->response(200, ['UnActivated' => $EggUnActive, 'Activated' => $EggActivated], '', [], true);
            }
        }
        return $this->response(200, [], '', [], false);
    }

    public function getEggsWaiting(Request $req)
    {
        $user = Auth::user();
        if ($user) {
            $EggsWaiting = Eggs::where('WaitingActive', 1)->select('ID', 'Owner', 'ActiveTime', 'BuyDate', 'Pool')->orderBy('ActiveTime', 1)->limit(30)->get();
            if ($EggsWaiting) {
                return $this->response(200, $EggsWaiting, '', [], true);
            }
        }
        return $this->response(200, [], '', [], false);
    }

    public function getAllEggs()
    {
        $user = Auth::user();
        $Eggs = Eggs::where('Owner', $user->User_ID)->where('Status', 1)->get();
        $result = [];
        foreach ($Eggs as $value) {
            if ($value->ActiveTime) {
                $value->RemainTime = ($value->HatchesTime - $value->HatchesTimeDecrease + $value->ActiveTime) - time();
            } else {
                $value->RemainTime = 0;
            }
            array_push($result, $value);
        }

        return $this->response(200, $Eggs, '', [], true);
    }

    public function getAllEggsType()
    {
        $user = Auth::user();
        $EggTypes = EggTypes::get();
        return $this->response(200, $EggTypes, '', [], true);
    }

    /**
     * @param CodeSpam
     * @param action (active)
     * @param currency_id
     * @param egg
     */
    public function getEgg(Request $req)
    {

        $user = Auth::user();
        if ($req->action) {
            $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
            if ($checkSpam == null) {
                //khoong toonf taij
                // return $this->response(422, [], 'Misconduct!', [], false);
            } else {
                DB::table('string_token')->where('User', $user->User_ID)->delete();
            }
        }
        $quantity = 1;

        $balance = User::getBalance($user->User_ID, 3);

        switch ($req->action) {
            case 'buy':
                /* *******************************************
                    mua trung 
                ******************************************* */
                // $sumEggs = DB::table('eggsTemp')->sum('amount');
                // if($sumEggs+$req->quantity > 10000){
                //     return $this->response(200, [], 'We have sold more than 10.000 eggs', [], false); 
                // }
                    return $this->response(200, [], __('app.we_have_sold_more_than_10000_eggs'), [], false); 

                if ($req->quantity) {
                    $quantity = $req->quantity;
                }
                if ($quantity < 1) {
                    return $this->response(200, [], __('app.number_of_eggs_is_less_than_1'), [], false);
                }
                $EggTypes = EggTypes::where('Type', $req->type . "")->first();
                if (!$EggTypes) {
                    return $this->response(200, [], __('app.egg_type_does_not_exist'), $validator->errors(), false);
                }

                if ($quantity * $EggTypes->Price > $balance) {
                    return $this->response(200, ['balance' => $balance], __('app.your_balance_is_not_enough'), [], false);
                }

                $eggsAmount = count(Eggs::where([
                    'Owner' => $user->User_ID,
                    'F' => 0,
                ])->where('Status', '!=', -1)->get()) + $req->quantity;

                if ($eggsAmount > 60) {
                    return $this->response(200, [], __('app.cannot_buy_egg_becase_your_eggs_are_greater_than_60'), [], false);
                }

                // them trung
                // chay truc tiep
                $update = $this->BuyEgg($user, $quantity, $EggTypes);

                $keyServer = config('security.server');
                // if($req->Server && $req->Server == $keyServer){
                for ($i = 0; $i < $quantity; $i++) {
                    $egg = new Eggs();
                    $egg->F = 0;
                    $egg->Pool = "0";
                    $egg->PosX = 0;
                    $egg->PosY = 0;
                    // $egg->Percent = 10000;
                    $egg->CanHatches = true;
                    $egg->HatchesTime = 0;
                    $egg->ActiveTime = 0;
                    $egg->Status = 1;
                    $egg->BuyDate = time();
                    $egg->BuyFrom = 'EggsBook.com';
                    $egg->Type = $req->type;
                    $egg->Owner = $user->User_ID;
                    $egg->ID = Eggs::RandonEggID();
                    $egg->save();
                }

                // }

                //jobs 
                // dispatch(new EggJobs($user, $quantity, $EggTypes, $add))->delay(1);
                $buyEggsType = config('utils.action.buy_eggs');
                DB::table('eggsTemp')->insert(array('user' => $user->User_ID, 'amount' => $quantity, 'datetime' => date('Y-m-d H:i:s')));
                LogUser::addLogUser($user->User_ID, $buyEggsType['action_type'], 'Buy ' . $quantity . ' Eggs ' . $egg->ID, $req->ip(), 15);
                return $this->response(200, ['balance' => array('EUSD' => $balance - $quantity * $EggTypes->Price)], __('app.buy_egg_complete'), [], true);
                /* *******************************************
                   ket thuc mua trung 
                ******************************************* */
            case 'active':
                $balance = User::getBalance($user->User_ID, $req->currency_id);
                if (!$balance) {
                    return $this->response(200, [], __('app.balance_does_not_exist'), [], false);
                }
                /* *******************************************
                active trung
                ******************************************* */
                $validator = Validator::make($req->all(), [
                    'currency_id' => 'required'
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $value) {
                        // return $error;
                        return $this->response(200, [], $value, $validator->errors(), false);
                    }
                }
        
                $egg = Eggs::with('eggsTypes')->where('ID', $req->egg)->where('Owner', $user->User_ID)->first();
                if (!$egg)
                    return $this->response(200, [], __('app.egg_does_not_exist'), [], false);

                if ($egg->ActiveTime > 0)
                    return $this->response(200, [], __('app.egg_has_been_activated'), [], false);

                if (!$egg->CanHatches && (!$egg->Percent || $egg->Percent != 10000)) {
                    $egg->Status = -1;
                    $egg->save();
                    return $this->response(200, ['egg' => false], __('app.cannot_activate'), [], true);
                }

                // $pool = Pools::where('ID', $egg->Pool)->first();
                // if(!$pool)
                //     return $this->response(200, [], 'Pool not exits!', [], false);

                $checkPool = Pools::checkPool($egg->Pool);
                if ($checkPool >= 2) {
                    return $this->response(200, [], __('app.the_pool_is_full'), [], false);
                }

                $eggType = $egg->eggsTypes;
                if ($req->currency_id != 3 && $req->currency_id != 9) {
                    return $this->response(200, [], __('app.currency_does_not_exist'), [], false);
                }

                if ($req->currency_id == 3) {
                    $price = $eggType->ActiveCost;
                }
                if ($req->currency_id == 9) {
                    $price = $eggType->ActiveGold;
                }
                if ($price > $balance) {
                    return $this->response(200, ['balance' => $balance], __('app.your_balance_is_not_enough'), [], false);
                }

                $ActiveTime = time();
                // $HatchesTime = rand($eggType->HatchesTime[0], $eggType->HatchesTime[1]);    //test
                // if (time() >= strtotime('2020-10-16')) {
                $HatchesTime = rand($eggType->HatchesTime[0], $eggType->HatchesTime[1]);
                // } else {
                //     $HatchesTime = 99999999;
                // }
                $update = true;
                if ($update) {
                    $arrayInsert = array();
                    $arrayInsert[] = array(
                        'Money_User' => $user->User_ID,
                        'Money_USDT' => -$price,
                        'Money_USDTFee' => 0,
                        'Money_Time' => time(),
                        'Money_Comment' => 'Active Eggs ID:' . $req->egg,
                        'Money_MoneyAction' => 30,
                        'Money_MoneyStatus' => 1,
                        'Money_Address' => null,
                        'Money_Currency' => $req->currency_id,
                        'Money_CurrentAmount' => $price,
                        'Money_Rate' => 1,
                        'Money_Confirm' => 0,
                        'Money_Confirm_Time' => null,
                        'Money_FromAPI' => 1,
                    );
                    Money::insert($arrayInsert);
                    $HatchesTimeDecrease = 0;
                    $getCoral = Item::with('itemTypes')->where([
                        'Pool' => $egg->Pool,
                        'Owner' => $user->User_ID,
                    ])->whereHas('itemTypes', function ($query) {
                        return $query->where('Category', 'Coral');
                    })->where('Status', '!=', -1)->first();
                    if ($getCoral) {
                        // dd($eggType, $getCoral);
                        if ($eggType->Level < 3 && $getCoral->Type == "IC1") {
                            $HatchesTimeDecrease = $HatchesTime * $getCoral->itemTypes->Data['Effect'];
                        } elseif ($eggType->Level >= 3 && ($getCoral->Type == "IC2" || $getCoral->Type == "IC3")) {
                            $HatchesTimeDecrease = $HatchesTime * $getCoral->itemTypes->Data['Effect'];
                        }
                    }
                    // if (time() >= strtotime('2020-10-16')) {
                        $egg = Eggs::where('ID', $req->egg)->update([
                            'HatchesTime' => $HatchesTime,
                            'ActiveTime' => $ActiveTime,
                            'HatchesTimeDecrease' => $HatchesTimeDecrease,
                        ]);
                    // } else {
                    //     $egg = Eggs::where('ID', $req->egg)->update([
                    //         'WaitingActive' => 1,
                    //         'ActiveTime' => $ActiveTime,
                    //         'HatchesTime' => $HatchesTime,
                    //         'HatchesTimeDecrease' => $HatchesTimeDecrease,
                    //     ]);
                    // }

                    // active cap cho nguoi choi
                    if ($user->User_Level_Active == 0) {
                        User::where('User_ID', $user->User_ID)->update(['User_Level_Active' => 1]);
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
                    LogUser::addLogUser($user->User_ID, $activeEggsType['action_type'], 'Active Eggs ID:' . $req->egg, $req->ip(), 16);

                    $arrParent = explode(',', $user->User_Tree);
                    $arrParent = array_reverse($arrParent);

                    //check commission buy egg
                    Money::checkCommission($user, 5, 3, 200);
                    //check commission active egg
                    Money::checkCommission($user, 6, $req->currency_id, $price);
                    for ($i = 0; $i < count($arrParent); $i++) {
                        $userParent = $arrParent[$i];
                        User::checkLevelUser($userParent);
                    }
                    //check commission buy egg
                    Money::checkAgencyCommission($user, 27, 3, 200);
                    //check commission active egg
                    Money::checkAgencyCommission($user, 30, $req->currency_id, $price);

                    $donate = 0;
                    // tặng cá ngựa
                    // $cangua = Item::where('Owner', $user->User_ID)->where('Donate', 1)->first();
                    // if (!$cangua) {
                    //     $itemType = ItemTypes::where('Type', 'IH')->first();
                    //     $itemArray = array(
                    //         'Type' => 'IH',
                    //         'Owner' => "$user->User_ID",
                    //         'Pool' => "0",
                    //         'Status' => 1,
                    //         'PoolTime' => 0,
                    //         'UpdateTime' => time(),
                    //         'LiveTime' => rand($itemType->Data['LiveTime'][0], $itemType->Data['LiveTime'][1]),
                    //         'FeedTime' => 0,
                    //         'ID' => Item::getIDItem(),
                    //         'Donate' => 1
                    //     );
                    //     Item::insert($itemArray);
                    //     $donate = 1;
                    // }

                    return $this->response(200, [
                        'balance' => [
                            'EUSD' => User::getBalance($user->User_ID, 3),
                            'GOLD' => User::getBalance($user->User_ID, 9),
                        ],
                        'HatchesTime' => $HatchesTime,
                        'ActiveTime' => $ActiveTime,
                        'egg' => true,
                        'donate' => $donate,
                        // 'WaitingActive' => time() >= strtotime('2020-10-16') ? 1 : 0,
                        'WaitingActive' => 0,
                    ], __('app.successful_egg_activation'), [], true);
                }
                return $this->response(200, [], '', [], false);
                /* *******************************************
                ket thuc active trung
                ******************************************* */
                break;
            default:
                $egg = Eggs::where(['Owner' => $user->User_ID, 'Status' => 1, 'Pool' => "0"])->select('_id', 'BuyDate', 'ID', 'Type', 'Name', 'status')->get();
                return $this->response(200, $egg, '', [], true);
        }
    }

    public function BuyEgg($user, $quantity, $EggTypes)
    {

        $balance = User::getBalance($user->User_ID, 3);
        if ($quantity < 1) {
            return false;
        }

        if ($quantity * $EggTypes->Price > $balance) {
            //Eggs::where('Owner', $user->User_ID)->whereIn('ID', $IDArray)->where('status', 0)->update(['status'=>-1]);
            return false;
        }
        // tru tien nguoi choi
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => -(float)($quantity * $EggTypes->Price),
            'Money_USDTFee' => 0,
            'Money_Time' => time(),
            'Money_Comment' => 'Buy ' . $quantity . ' Eggs',
            'Money_MoneyAction' => 27,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => 3,
            'Money_CurrentAmount' => (float)($quantity * $EggTypes->Price),
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_Confirm_Time' => null,
            'Money_FromAPI' => 1,
        );
        $insert = Money::insert($arrayInsert);
        if ($insert) {
            return true;
        }
    }

    public function putEggIntoPool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'egg' => 'required',
            'pool' => 'required',
            'x' => 'nullable',
            'y' => 'nullable',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }


        $user = $request->user();
        //Check egg in pool or not?
        $egg = Eggs::where('ID', $request->egg)->where('Owner', (string)$user->User_ID)->first();

        if ($egg->Status != 1) {
            return $this->response(200, [], __('app.cannot_put_it_into_pool'), [], false);
        }

        if ($egg->Pool || $egg->PosX || $egg->PosY) {
            return $this->response(200, [], __('app.this_egg_has_been_put'), [], false);
        }
        $pool = Pools::where([
            'Owner' => $user->User_ID,
            'ID' => $request->pool,
        ])->first();

        if (!$pool) {
            return $this->response(200, [], __('app.you_have_no_this_pool'), [], false);
        }

        $eggs = Eggs::where([
            'Pool' => $request->pool,
            'Status' => 1,
        ])->get();

        $fishs = Fishs::where([
            'Pool' => $request->pool,
            'Status' => 1,
        ])->get();

        if ((count($eggs) + count($fishs)) >= $pool->poolType->Max) {
            return $this->response(200, [], __('app.this_pool_has_been_fulled'), [], false);
        }

        Eggs::where('ID', $request->egg)->update([
            'Pool' => $request->pool,
            'PosX' => $request->x ? $request->x : 0,
            'PosY' => $request->y ? $request->y : -4,
        ]);

        return $this->response(200, ['pool' => $request->pool, 'egg' => $request->egg, 'x' => $request->x, 'y' => $request->y], __('app.put_egg_into_pool_successful'));
    }

    public function removeEggFromPool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'egg' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $egg = Eggs::where([
            'ID' => $request->egg,
            'Owner' => $user->User_ID
        ])->first();
        if ($egg) {

            if ($egg->HatchesTime > 0 && $egg->ActiveTime != 0) {
                return $this->response(200, [], __('app.cannot_remove_because_this_egg_has_been_activated'), [], false);
            }

            if (!$egg->Pool && !$egg->PosX && !$egg->PosY) {
                return $this->response(200, [], __('this_egg_has_been_removed'), [], false);
            }

            Eggs::where('ID', $request->egg)->update([
                'Pool' => "0",
                'PosX' => 0,
                'PosY' => 0,
            ]);

            return $this->response(200, ['egg' => $request->egg], __('app.remove_egg_from_pool_successful'));
        } else {
            return $this->response(200, [], __('app.no_egg_found'), [], false);
        }
    }

    public function openEgg(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'egg' => 'required',
            'CodeSpam' => 'required',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $request->CodeSpam)->first();

        if ($checkSpam == null) {
            //khoong toonf taij
            return $this->response(422, [], __('app.misconduct'), [], false);
        } else {
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }

        $egg = Eggs::where([
            'ID' => $request->egg,
            'Owner' => $user->User_ID
        ])->first();

        if ($egg) {
            if ($egg->Status == 2) {
                return $this->response(200, [], __('app.this_egg_has_been_opened'), [], false);
            }
            $newFish = [];
            if ($egg->ActiveTime && $egg->Pool && (time() - $egg->ActiveTime >= $egg->HatchesTime - $egg->HatchesTimeDecrease)) {
                $updateEgg = Eggs::where([
                    'ID' => $request->egg,
                    'Owner' => $user->User_ID
                ])->update([
                    'Status' => 2,
                ]);
                if ($updateEgg) {
                    $dataFish = [
                        'F' => $egg->F,
                        'Pool' => $egg->Pool,
                        'Born' => time(),
                        'Type' => 'B1',
                        'CurrentFood' => 10,
                        'FeedTime' => time(),
                        'GrowTime' => 0,
                        'ActiveTime' => 0,
                        'Status' => 1,
                        'Owner' => $user->User_ID,
                        'ID' => Fishs::getIDFish(),
                        'From' => $egg->ID,
                    ];
                    $newFish = $dataFish;
                    Fishs::insert($dataFish);
                }

                $pool = Pools::with('poolType')->where('ID', $egg->Pool)->get();
                $returnData = array();
                foreach ($pool as $v) {
                    $returnData[] = array(
                        'ID' => $v->ID,
                        'Type' => $v->Type,
                        'Skin' => $v->Skin,
                        'Name' => $v->poolType->Name,
                        'Child' => Pools::infoPool($v->ID),
                    );
                }
                return $this->response(200, [
                    'returnData' => $returnData,
                    'newFish' => $newFish,
                ], __('app.successful_egg_open'), [], true);
            }
            return $this->response(200, ['egg' => $request->egg], __('app.the_egg_is_not_time_for_to_hatch_or_was_hatched'), [], false);
        } else {
            return $this->response(200, [], __('app.no_egg_found'), [], false);
        }
    }

    public function scanningEgg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'egg_id' => 'required'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $item = Item::where([
            'Owner' => $user->User_ID,
            'Type' => 'IM',
        ])->first();

        if (!$item) return $this->response(200, [], __('app.you_have_no_item_to_scan'), [], false);

        $egg = Eggs::where('ID', $request->egg_id)->first();
        $eggCanHatches = true;

        if (!$egg) return $this->response(200, [], __('app.no_egg_found'), [], false);

        if (isset($egg->CanHatches) && !$egg->CanHatches) {
            $eggCanHatches = false;
        }

        $item->delete();

        return $this->response(200, ['egg' => $eggCanHatches]);
    }

    public function activeManyEgg(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'currency_id' => 'required',
            'egg_array' => 'required'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $balance = User::getBalance($user->User_ID, $request->currency_id);
        if (!$balance) {
            return $this->response(200, [], __('app.balance_does_not_exist'), [], false);
        }

        $request->egg_array = json_decode($request->egg_array, true);
        // dd($request->egg_array);
        for ($i = 0; $i < count($request->egg_array); $i++) {
            # code...

            $egg = Eggs::where('ID', $request->egg_array[$i])->where('Owner', $user->User_ID)->first();
            if (!$egg) return $this->response(200, [], __('app.no_egg_found'), [], false);

            if ($egg->ActiveTime) return $this->response(200, [], __('This egg ' . $request->egg_array[$i] . ' has been activated!'), [], false);

            if (!$egg->CanHatches && (!$egg->Percent || $egg->Percent != 10000)) {
                $egg->Status = -1;
                $egg->save();
                continue;
            }

            $eggType = EggTypes::where('Type', $egg->Type . "")->first();
            if ($request->currency_id != 3 && $request->currency_id != 9) {
                return $this->response(200, [], __('app.currency_does_not_exist'), [], false);
            }

            if ($request->currency_id == 3) {
                $price = $eggType->ActiveCost;
            }
            if ($request->currency_id == 9) {
                $price = $eggType->ActiveGold;
            }
            if ($price > $balance) {
                return $this->response(200, ['balance' => $balance], __('app.your_balance_is_not_enough'), [], false);
            }

            $ActiveTime = time();
            // $HatchesTime = rand($eggType->HatchesTime[0], $eggType->HatchesTime[1]);    //test
            // if (time() >= strtotime('2020-10-16')) {
            $HatchesTime = rand($eggType->HatchesTime[0], $eggType->HatchesTime[1]);
            // } else {
            //     $HatchesTime = 99999999;
            // }
            $update = true;
            if ($update) {
                $arrayInsert = array();
                $arrayInsert[] = array(
                    'Money_User' => $user->User_ID,
                    'Money_USDT' => -$price,
                    'Money_USDTFee' => 0,
                    'Money_Time' => time(),
                    'Money_Comment' => 'Active Eggs ID:' . $request->egg_array[$i],
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
                if (time() >= strtotime('2020-10-16')) {
                    $egg->HatchesTime = $HatchesTime;
                    $egg->ActiveTime = $ActiveTime;
                    $egg->save();
                } else {
                    $egg->WaitingActive = 1;
                    $egg->HatchesTime = $HatchesTime;
                    $egg->ActiveTime = $ActiveTime;
                    $egg->save();
                }

                // active cap cho nguoi choi
                if ($user->User_Level_Active == 0) {
                    User::where('User_ID', $user->User_ID)->update(['User_Level_Active' => 1]);
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
                LogUser::addLogUser($user->User_ID, $activeEggsType['action_type'], 'Active Eggs ID:' . $request->egg_array[$i], $request->ip(), 16);

                $arrParent = explode(',', $user->User_Tree);
                $arrParent = array_reverse($arrParent);

                //check commission buy egg
                Money::checkCommission($user, 5, 3, 200);
                //check commission active egg
                Money::checkCommission($user, 6, $request->currency_id, $price);
                for ($j = 0; $j < count($arrParent); $j++) {
                    $userParent = $arrParent[$j];
                    User::checkLevelUser($userParent);
                }
                //check commission buy egg
                Money::checkAgencyCommission($user, 27, 3, 200);
                //check commission active egg
                Money::checkAgencyCommission($user, 30, $request->currency_id, $price);
            }
        }
        return $this->response(200, [
            'balance' => [
                'EUSD' => User::getBalance($user->User_ID, 3),
                'GOLD' => User::getBalance($user->User_ID, 9),
            ],
        ], __('Successful egg activation'));
    }

    /**
     * @param string egg_array
     * @param string pool
     */
    public function putManyEggIntoPool(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'egg_array' => 'required',
            'pool' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $request->egg_array = json_decode($request->egg_array, true);
        //Check egg in pool or not?
        for ($i = 0; $i < count($request->egg_array); $i++) {
            # code...
            $egg = Eggs::where('ID', $request->egg_array[$i])->where('Owner', (string)$user->User_ID)->first();

            if ($egg->Status != 1) {
                return $this->response(200, [], __('Cannot put ' . $request->egg_array[$i] . ' into pool!'), [], false);
            }

            if ($egg->Pool || $egg->PosX || $egg->PosY) {
                return $this->response(200, [], __('This egg ' . $request->egg_array[$i] . ' has been put'), [], false);
            }
            $pool = Pools::where([
                'Owner' => $user->User_ID,
                'ID' => $request->pool,
            ])->first();

            if (!$pool) {
                return $this->response(200, [], __('app.you_have_no_this_pool'), [], false);
            }

            $eggs = Eggs::where([
                'Pool' => $request->pool,
                'Status' => 1,
            ])->get();

            $fishs = Fishs::where([
                'Pool' => $request->pool,
                'Status' => 1,
            ])->get();

            if ((count($eggs) + count($fishs)) >= $pool->poolType->Max) {
                return $this->response(200, [], __('app.this_pool_is_full'));
            }

            Eggs::where('ID', $request->egg_array[$i])->update([
                'Pool' => $request->pool,
                'PosX' => rand(-3500, 4500) / 1000,
                'PosY' => rand(-3500, -4500) / 1000,
            ]);
        }

        return $this->response(200, ['egg_array' => $request->egg_array], __('app.put_egg_into_pool_successful'));
    }

    public function removeManyEggFromPool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'egg_array' => 'required',
            'pool' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $request->egg_array = json_decode($request->egg_array, true);

        for ($i = 0; $i < count($request->egg_array); $i++) {
            $egg = Eggs::where([
                'ID' => $request->egg_array[$i],
                'Owner' => $user->User_ID
            ])->first();
            if ($egg) {

                if ($egg->HatchesTime > 0 && $egg->ActiveTime != 0)
                    return $this->response(200, [], __('This egg ' . $request->egg_array[$i] . ' has been activated!'), [], false);

                if (!$egg->Pool && !$egg->PosX && !$egg->PosY)
                    return $this->response(200, [], __('Cannot activate egg ' . $request->egg_array[$i]), [], false);

                Eggs::where('ID', $request->egg_array[$i])->update([
                    'Pool' => "0",
                    'PosX' => 0,
                    'PosY' => 0,
                ]);
            }
        }

        return $this->response(200, ['egg_array' => $request->egg_array], __('app.remove_egg_from_pool_successful'));
    }

    public function autoActiveEgg(Request $request){
        $eggs = EggbookTest::where([
            'WaitingActive' => 1,
            'Status' => 1,
        ])->get();

        foreach ($eggs as $egg) {
            if($egg->ActiveTime <= 1603324800){
                $egg->ActiveTime = 1603324800;
            }
            // $egg->Date = Date('d/m/Y', $egg->ActiveTime);
            // echo $egg->Date . '-';
            $egg->WaitingActive = 0;
            $egg->save();
        }

        return $this->response(200, [
            'egg' => $eggs,
            'total' => count($eggs),
        ], 'finish');
    }

    /**
     * 
     */
    public function eggWillActive(Request $request)
    {
        $eggs = Eggs::where([
            'WaitingActive' => 1,
            'Status' => 1
        ])->orderBy('BuyDate', 'ASC')->get();

        $egg_results = [];
        $egg_fakes = [];

        foreach ($eggs as $egg) {
            $user = User::where('User_ID', $egg->Owner)->first();
            if($user && $user->User_Level == 0){
                array_push($egg_results, $egg);
            } else {
                array_push($egg_fakes, $egg);
            }
        }

        $results = array_merge($egg_results, $egg_fakes);

        return $this->response(200, [
            'eggs' => $results,
            'total_eggs' => count($results),
        ]);
    }
}
