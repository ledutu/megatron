<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\User;
use Validator;
use App\Model\Money;
use App\Model\Eggs;
use App\Model\EggTypes;
use App\Model\FoodTypes;
use App\Model\PoolTypes;
use App\Model\LogUser;
use App\Model\Pools;
use App\Model\Foods;
use App\Model\Golds;
use App\Model\FishTypes;
use App\Model\ItemTypes;
use App\Model\Item;
use App\Model\MoneyAction;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;

class ShopController extends Controller
{
    //
    public $feeSwap = 0;
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAllShop()
    {
        $egg_types = EggTypes::orderBy('Type')->get();
        $food_types = FoodTypes::orderBy('Type')->get();
        $pool_types = PoolTypes::orderBy('Type')->get();
        $gold_types = Golds::orderBy('Type')->get();
        $fish_types = FishTypes::orderBy('Type')->get();
        $item_types = ItemTypes::where('Active', true)->orderBy('Type')->get();
        $coral = $item_types->where('Category', 'Coral');
        $autoFeedMachine = $item_types->where('Category', 'Auto-Feed Machine');
        $seaWeed = $item_types->where('Category', 'Seaweed');
        $microscope = $item_types->where('Category', 'Microscope');
        // $piece = $item_types->where('Category', 'Piece');
        // $vitamin = $item_types->where('Category', 'Vitamin');
        $creature = $item_types->where('Category', 'Creature');
        return $this->response(200, [
            'egg_types' => $egg_types,
            'food_types' => $food_types,
            'pool_types' => $pool_types,
            'gold_types' => $gold_types,
            'fish_types' => $fish_types,
            'item_types' => [
                'coral' => $coral,
                'auto_feed_machine' => $autoFeedMachine,
                'seaweed' => $seaWeed,
                'microscope' => $microscope,
                // 'piece' => $piece,
                // 'vitamin' => $vitamin,
                'creature' => $creature,
            ]
        ]);
    }

    public function buyItem(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->response(200, [], 'User does not exits', [], false);
        }
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $request->CodeSpam)->first();

        if ($checkSpam == null) {

            return $this->response(200, [], 'Misconduct!', [], false);
        } else {
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'item_type' => 'required',
            'cash_type' => 'required',
            'skin' => 'nullable',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        if ($request->quantity < 1) {
            return $this->response(200, [], __('app.quantity_is_less_than_1'), [], false);
        }

        $user = $request->user();
        $balanceEUSD = User::getBalance($user->User_ID, 3);
        $balanceGold = User::getBalance($user->User_ID, 9);
        // return $balanceGold;

        switch ($request->type) {
            case 'egg':
                // return $this->response(200, [], 'We have sold more than 10.000 eggs', [], false); 
                return $this->buyEggOnShop($user, $request->quantity, $request->item_type, $balanceEUSD, $request->ip());

            case 'pool':
                return $this->buyPoolOnShop(
                    $user,
                    $request->quantity,
                    $request->item_type,
                    $balanceEUSD,
                    $balanceGold,
                    $request->cash_type,
                    $request->skin,
                    $request->ip()
                );

            case 'food':
                return $this->buyFoodOnShop(
                    $user,
                    $request->quantity,
                    $request->item_type,
                    $balanceEUSD,
                    $balanceGold,
                    $request->cash_type,
                    $request->ip()
                );

            case 'gold':
                return $this->buyGoldOnShop(
                    $user,
                    $request->quantity,
                    $request->item_type,
                    $balanceEUSD,
                    $balanceGold,
                    $request->ip()
                );

            case 'item':
                return $this->buyItemOnShop(
                    $user,
                    $request->quantity,
                    $request->item_type,
                    $balanceEUSD,
                    $balanceGold,
                    $request->cash_type,
                    $request->skin,
                    $request->ip()
                );

            default:
                return $this->response(200, [], __('app.no_item_found'), [], false);
        }
    }

    public function buyEggOnShop($user, $quantity, $item_type, $balanceEUSD, $ip)
    {
        $sumEggs = DB::table('eggsTemp')->where('user', $user->User_ID)->sum('amount');
        if ($sumEggs + $quantity > 10000) {
            return $this->response(200, [], __('we_have_sold_more_than_10000_eggs'), [], false);
        }

        $EggTypes = EggTypes::where('Type', $item_type)->first();

        if (!$EggTypes) {
            return $this->response(200, [], 'Type egg not exits!', [], false);
        }

        if ($quantity * $EggTypes->Price > $balanceEUSD) {
            return $this->response(200, ['balance' => $balanceEUSD], __('app.your_balance_is_not_enough'), [], false);
        }

        $eggsAmount = count(Eggs::where([
            'Owner' => $user->User_ID,
            'F' => 0,
        ])->where('Status', '!=', -1)->get()) + $quantity;

        if ($eggsAmount > 60) {
            return $this->response(200, [], __('app.cannot_buy_egg_becase_your_eggs_are_greater_than_60'), [], false);
        }

        // $update = $this->BuyEgg($user, $quantity, $EggTypes, $add);
        $update = Eggs::minusBalance($user, $quantity, $EggTypes);

        $eggs = Eggs::addEggs($quantity, $item_type, $user);

        $buyEggsType = config('utils.action.buy_eggs');
        DB::table('eggsTemp')->insert(array('user' => $user->User_ID, 'amount' => $quantity, 'datetime' => date('Y-m-d H:i:s')));
        LogUser::addLogUser($user->User_ID, $buyEggsType['action_type'], 'Buy ' . $quantity . ' Eggs', $ip, 15);
        return $this->response(200, [
            'total_egg' => count(Eggs::where([
                'Owner' => $user->User_ID,
            ])->get()),
            'eggs' => $eggs,
            'eusd' => User::getBalance($user->User_ID, 3),
            'gold' => User::getBalance($user->User_ID, 9),
        ], 'Buy egg complete!', [], true);
    }

    public function buyPoolOnShop(
        $user,
        $quantity,
        $item_type,
        $balanceEUSD,
        $balanceGold,
        $cash_type,
        $skin,
        $ip
    ) {
        $pool_types = PoolTypes::where('Type', $item_type)->first();
        if (!$pool_types) {
            return $this->response(200, [], __('app.this_pool_does_note_exist'));
        }

        if ($cash_type == 3) {
            if ($quantity * $pool_types->Price > $balanceEUSD) {
                return $this->response(200, ['balance' => $balanceEUSD], __('app.your_balance_eusd_is_not_enough'), [], false);
            }

            $buyPoolTypes = config('utils.action.buy_pool');
            LogUser::addLogUser($user->User_ID, $buyPoolTypes['action_type'], 'Buy ' . $quantity . ' Pools', $ip, 17);

            $this->calculateMoney($user, $quantity, $pool_types->Price, ' pools', 28, 3);
        } else if ($cash_type == 9) {
            if ($quantity * $pool_types->Gold > $balanceGold) {
                return $this->response(200, ['balance' => $balanceGold], __('app.your_balance_gold_is_not_enough'), [], false);
            }

            $buyPoolTypes = config('utils.action.buy_pool');
            LogUser::addLogUser($user->User_ID, $buyPoolTypes['action_type'], 'Buy ' . $quantity . ' Pools', $ip, 17);

            $this->calculateMoney($user, $quantity, $pool_types->Gold, ' pools', 28, 9);
        } else {
            return $this->response(200, [], __('app.cash_type_is_invalid'), [], false);
        }

        $pools = Pools::addPools($quantity, $item_type, $user, $skin);

        return $this->response(200, [
            'total_pool' => count(Pools::where([
                'Owner' => $user->User_ID,
            ])->get()),
            'pools' => $pools,
            'eusd' => User::getBalance($user->User_ID, 3),
            'gold' => User::getBalance($user->User_ID, 9),
        ], 'Buy pool complete!');
    }

    public function buyFoodOnShop(
        $user,
        $quantity,
        $item_type,
        $balanceEUSD,
        $balanceGold,
        $cash_type,
        $ip
    ) {
        $food_types = FoodTypes::where('Type', $item_type)->first();
        if (!$food_types) {
            return $this->response(200, [], __('app.this_food_is_not_exist'));
        }

        if ($cash_type == 3) {
            if ($quantity * $food_types->Price > $balanceEUSD) {
                return $this->response(200, ['balance' => $balanceEUSD], __('app.your_balance_eusd_is_not_enough'), [], false);
            }

            $buyFoodTypes = config('utils.action.buy_food');
            LogUser::addLogUser($user->User_ID, $buyFoodTypes['action_type'], 'Buy ' . $quantity . ' Foods Type ' . $item_type, $ip, 18);

            $this->calculateMoney($user, $quantity, $food_types->Price, ' Foods', 29, 3);
        } else if ($cash_type == 9) {
            if ($quantity * $food_types->Gold > $balanceGold) {
                return $this->response(200, ['balance' => $balanceGold], __('app.your_balance_gold_is_not_enough'), [], false);
            }

            $buyFoodTypes = config('utils.action.buy_food');
            LogUser::addLogUser($user->User_ID, $buyFoodTypes['action_type'], 'Buy ' . $quantity . ' Foods Type ' . $item_type, $ip, 18);

            $this->calculateMoney($user, $quantity, $food_types->Gold, ' Foods', 29, 9);
        } else {
            return $this->response(200, [], __('app.cash_type_is_invalid'), [], false);
        }

        $food = Foods::where('Owner', $user->User_ID)->first();
        if ($food) {
            $amount = $food_types->Amount * $quantity + $food->Amount;
            $food->Amount = $amount;
            $food->save();
        } else {
            $food_new = new Foods;
            $food_new->Amount = $food_types->Amount * $quantity;
            $food_new->Type = "1";
            $food_new->CreateAt = Carbon::now()->toDateTimeString();
            $food_new->Owner = $user->User_ID;
            $food_new->save();
        }

        return $this->response(200, [
            'total_food' => Foods::where([
                'Owner' => $user->User_ID,
            ])->sum('Amount'),
            'eusd' => User::getBalance($user->User_ID, 3),
            'gold' => User::getBalance($user->User_ID, 9),
        ], __('app.buy_food_successful'));
    }

    public function buyGoldOnShop(
        $user,
        $quantity,
        $item_type,
        $balanceEUSD,
        $balanceGold,
        $ip
    ) {
        $golds = Golds::where('ID', "$item_type")->first();
        if (!$golds) {
            return $this->response(200, [], __('app.wrong_package'), [], false);
        }
        $amount_tranfer = $golds->Price * $quantity;
        $amount_received = $golds->Gold * $quantity;
        $amountFee = $golds->Gold * $quantity * $this->feeSwap;

        $balance = User::getBalance($user->User_ID, 3);
        if ($amount_tranfer > $balance) {
            return $this->response(200, ['balance' => $balance], __('your_balance_is_not_enough'), [], false);
        }
        $arrayInsert = array(
            array(
                'Money_User' => $user->User_ID,
                'Money_USDT' => -$amount_tranfer,
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => 'Pay ' . ($amount_tranfer * 1) . ' EUSD to ' . ($amount_received * 1) . ' GOLD',
                'Money_MoneyAction' => 33,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => 3,
                'Money_CurrentAmount' => $amount_tranfer,
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_Confirm_Time' => null,
                'Money_FromAPI' => 1,
            ),
            array(
                'Money_User' => $user->User_ID,
                'Money_USDT' => $amount_received - $amountFee,
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => 'Receive ' . ($amount_received * 1) . ' GOLD from ' . $amount_tranfer . ' EUSD',
                'Money_MoneyAction' => 33,
                'Money_MoneyStatus' => 1,
                'Money_Address' => null,
                'Money_Currency' => 9,
                'Money_CurrentAmount' => $amount_received - $amountFee,
                'Money_Rate' => 1,
                'Money_Confirm' => 0,
                'Money_Confirm_Time' => null,
                'Money_FromAPI' => 1,
            )
        );
        $transferType = config('utils.action.deposit_GOLD');
        $logArray = array(
            array(
                'action' => $transferType['action_type'],
                'user' => $user->User_ID,
                'comment' => 'Pay ' . $amount_tranfer . ' EUSD to Get' . $amount_received . ' GOLD',
                'ip' => $ip,
                'datetime' => date('Y-m-d H:i:s'),
                'action_id' => 19,
            ),
            array(
                'action' => $transferType['action_type'],
                'user' => $user->User_ID,
                'comment' => 'Receive ' . $amount_received . ' GOLD from ' . $amount_tranfer . ' EUSD',
                'ip' => $ip,
                'datetime' => date('Y-m-d H:i:s'),
                'action_id' => 19,
            )
        );
        LogUser::insert($logArray);
        Money::insert($arrayInsert);

        //check commission buy gold
        Money::checkCommission($user, 12, 3, $amount_tranfer);
        Money::checkAgencyCommission($user, 33, 3, $amount_tranfer);
        $balanceGold = User::getBalance($user->User_ID, 9);
        $balanceEUSD = User::getBalance($user->User_ID, 3);
        return $this->response(200, ['gold' => $balanceGold, 'eusd' => $balanceEUSD], __('app.you_deposit_gold_successful'), [], true);
    }

    public function buyItemOnShop(
        $user,
        $quantity,
        $item_type,
        $balanceEUSD,
        $balanceGold,
        $cash_type,
        $skin,
        $ip
    ) {
        $itemType = ItemTypes::where('Type', $item_type)->first();
        if (!$itemType) return $this->response(200, [], __('app.item_is_not_exist'), [], false);

        if ($cash_type == 3) {
            if ($quantity * $itemType->Price > $balanceEUSD) {
                return $this->response(200, ['balance' => $balanceEUSD], __('app.your_balance_eusd_is_not_enough'), [], false);
            }

            $buyItemType = config('utils.action.buy_item');
            LogUser::addLogUser($user->User_ID, $buyItemType['action_type'], 'Buy ' . $quantity . ' Item Type ' . $item_type, $ip, 21);

            $this->calculateMoney($user, $quantity, $itemType->Price, ' ' . $itemType->Category . ' ' . $itemType->Name, 41, 3);
        } else if ($cash_type == 9) {
            if ($quantity * $itemType->Gold > $balanceGold) {
                return $this->response(200, ['balance' => $balanceGold], __('app.your_balance_gold_is_not_enough'), [], false);
            }

            $buyItemType = config('utils.action.buy_item');
            LogUser::addLogUser($user->User_ID, $buyItemType['action_type'], 'Buy ' . $quantity . ' Item Type ' . $item_type, $ip, 21);

            $this->calculateMoney($user, $quantity, $itemType->Gold, ' ' . $itemType->Category . ' ' . $itemType->Name, 41, 9);
        } else {
            return $this->response(200, [], __('app.cash_type_is_invalid'), [], false);
        }

        $items = [];

        for ($i = 0; $i < $quantity; $i++) {
            $item = new Item();
            $item->Type = $item_type;
            $item->Owner = $user->User_ID;
            $item->Pool = "0";
            $item->Status = 1;
            $item->PoolTime = 0;
            $item->UpdateTime = 0;
            if (isset($itemType->Data['LiveTime'])) {
                $item->LiveTime = rand($itemType->Data['LiveTime'][0], $itemType->Data['LiveTime'][1]);
                $item->FeedTime = 0;
            }
            $item->Skin = $skin ?? 0;
            $item->X = 0;
            $item->Y = 0;
            $item->ID = Item::getIDItem();
            $item->Data = [];
            array_push($items, $item);
            $item->save();
        }

        return $this->response(200, [
            'items' => $items,
            'eusd' => User::getBalance($user->User_ID, 3),
            'gold' => User::getBalance($user->User_ID, 9),
        ], __('app.buy_item_successful'));
    }

    public function calculateMoney($user, $quantity, $price, $comment, $action_id, $currency_id)
    {
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => -(float)($quantity * $price),
            'Money_USDTFee' => 0,
            'Money_Time' => time(),
            'Money_Comment' => 'Buy ' . $quantity . $comment,
            'Money_MoneyAction' => $action_id,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => $currency_id,
            'Money_CurrentAmount' => (float)($quantity * $price),
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_Confirm_Time' => null,
            'Money_FromAPI' => 1,
        );
        $insert = Money::insert($arrayInsert);
        //check commission
        if ($action_id == 28) {
            $actionCom = 13;
        } else {
            $actionCom = 9;
        }
        Money::checkCommission($user, $actionCom, $currency_id, $quantity * $price);
        Money::checkAgencyCommission($user, $action_id, $currency_id, $quantity * $price);
    }

    public function fixPool()
    {
        $eggs = Eggs::where('Pool', 0)->update([
            'Pool' => "0"
        ]);
        return $this->response(200, [], 'update finish');
    }
}
