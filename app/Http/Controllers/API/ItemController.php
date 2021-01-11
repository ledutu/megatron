<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Fishs;
use App\Model\Item;
use App\Model\Foods;
use App\Model\ItemHistory;
use App\Model\LogUser;
use App\Model\Pools;
use Validator;

class ItemController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function feedHippo(Request $request){
        $validator = Validator::make($request->all(), [
            'ItemID' => 'required',
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
            'ID' => $request->ItemID,
        ])->first();
        $amount = 2;
        $foodAmount = Foods::where('Owner', $user->User_ID)->first()->Amount;
        if($foodAmount == 0 || $foodAmount < 2){
            return $this->response(200, [], __('app.you_have_no_food_to_feed'), [], false);
        }

        $SECCOND_DATE = 86400;

        if($item){
            $CurrentFood = Item::BloodHippo($item);
            if($CurrentFood <= 0 || $item->Status == -1) return $this->response(200, [], __('app.your_flashy_died'), [], false);

            $food = Foods::where('Owner', $user->User_ID)->first();

            Item::where([
                'Owner' => $user->User_ID,
                'ID' => $request->ItemID,
            ])->update([
                'FeedTime' => time(),
            ]);
            
            $remainAmount = $food->Amount - $amount;

            // return $food;

            Foods::where('Owner', $user->User_ID)->update([
                'Amount' => $remainAmount < 0 ? 0 : $remainAmount
            ]);

            $feedFishType = config('utils.action.feed_fish');
            LogUser::addLogUser($user->User_ID, $feedFishType['action_type'], 'Feed Flashy '.$request->ItemID, $request->ip(), 26);

            return $this->response(200, ['Item'=>$request->ItemID, 
                'TotalFood' => $remainAmount,
            ], __('app.feed_flashy_successful'));

        } else {
            return $this->response(200, [], __('app.your_flashy_does_not_exist'), [], false);
        }
    }
    public function index(Request $request)
    {
        //
        $user = $request->user();
        $item = Item::where('Owner', $user->User_ID)->where('Pool', "0")->where('Status', 1)->orderBy('Level')->get();
        $coral = $item->whereIn('Type', array('IC1', 'IC2', 'IC3'))->toArray();
        // foreach ($corals as $coral) {
        //     $itemNew = Item::find($coral['_id']);
        //     $itemNew->itemTypes;
        //     return $itemNew;
        // }
        $autoFeedMachine = $item->whereIn('Type', array('IA1', 'IA2', 'IA3'))->toArray();
        $seaWeed = $item->whereIn('Type', array('IS1', 'IS2', 'IS3'))->toArray();
        $microscope = $item->whereIn('Type', array('IM'))->toArray();
        // $piece = $item->whereIn('Type', array('IPE', 'IPI'))->toArray();
        // $vitamin = $item->whereIn('Type', array('IVB', 'IVG'))->toArray();
        $creature = $item->whereIn('Type', array('IH'))->toArray();
        // $hippos = [];
        // foreach($creature as $c){
        //     $c['CurrentBlood'] = Item::BloodHippo($c);
        //     $hippos[] = $c;
        // }   
        return $this->response(200, [
            'coral' => array_values($coral),
            'auto_feed_machine' => array_values($autoFeedMachine),
            'seaweed' => array_values($seaWeed),
            'microscope' => array_values($microscope),
            // 'piece' => array_values($piece),
            // 'vitamin' => array_values($vitamin),
            'creature' => array_values($creature),
        ]);
    }

    public function getFood(Request $request)
    {
        $user = $request->user();
        $items = Foods::where('Owner', $user->User_ID)->get();

        $results = [];

        foreach ($items as $value) {
            # code...
            $item = Foods::find($value->_id);
            $item->foodsType;
            array_push($results, $item);
        }

        return $this->response(200, $results);
    }

    public function putItemIntoPool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item' => 'required',
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
        $item = Item::where('ID', $request->item)->where('Owner', (string)$user->User_ID)->first();
        // dd($item);
        if (!$item || $item->Status != 1) {
            return $this->response(200, [], __('app.cannot_put_it_into_pool'), [], false);
        }
        if ($item->Pool && $item->PosX && $item->PosY) {
            return $this->response(200, [], __('app.this_item_has_been_put_into_pool'), [], false);
        }
        $pool = Pools::where([
            'Owner' => $user->User_ID,
            'ID' => $request->pool,
        ])->first();

        if (!$pool) {
            return $this->response(200, [], __('app.you_have_no_this_pool'), [], false);
        }

        $items = Item::where([
            'Pool' => $request->pool,
            'Status' => 1,
        ])->whereHas('itemTypes', function($query) use ($item){
            $query->where('Category', $item->itemTypes->Category);
        })->get();
        if (count($items) >= $item->itemTypes->Max) {
            return $this->response(200, [], __('app.this_pool_is_fool'), [], false);
        }

        $data = [];

        if ($item->itemTypes->Category == 'Seaweed') {
            if ($item->itemTypes->Data['Pool'] != $pool->poolType->Type) return $this->response(200, [], __('app.this_item_is_not_suitable_with_this_pool'), [], false);

            $data = [
                'GrowTime' => rand($item->itemTypes->Data['From'], $item->itemTypes->Data['To']),
                'Status' => 1
            ];
        }

        if ($item->itemTypes->Category == 'Coral') {
            $data = [
                'Status' => 0
            ];
        }

        if (strpos($item->itemTypes->Category, 'Coral') !== false) {
            // dd(123);
            Item::interactCoral($item, $pool, $user);
        }
        // $update = Item::where('ID', $request->item)->update([
        //     'Pool' => $request->pool,
        //     'PoolTime' => time(),
        //     'UpdateTime' => time(),
        //     'FeedTime' => time(),
        //     'X' => $request->x ?? 0,
        //     'Y' => $request->y ?? -4,
        //     'Data' => $data,
        // ]);

        $item->Pool = $request->pool;
        $item->PoolTime = time();
        $item->UpdateTime = time();
        $item->FeedTime = time();
        $item->X = $request->x ?? 0;
        $item->Y = $request->y ?? -4;
        $item->Data = $data;
        $item->save();

        $putItemIntoPool = config('utils.action.put_item_into_pool');
        LogUser::addLogUser($user->User_ID, $putItemIntoPool['action_type'], 'put ' . $request->item . ' into ' . $request->pool, $request->ip(), 31);

        ItemHistory::addHistory($user->User_ID, $request->item, 'Put into pool', time());
        return $this->response(
            200,
            [
                'pool' => $request->pool,
                'item' => $item,
                'x' => $request->x,
                'y' => $request->y
            ],
            __('app.put_item_into_pool_successful')
        );
    }

    public function removeItemFromPool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item' => 'required'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();
        //Check egg in pool or not?
        $item = Item::with('itemTypes')->where('ID', $request->item)->where('Owner', (string)$user->User_ID)->first();
        if ($item) {
            if (!$item->itemTypes->CanRemove) {
                return $this->response(200, [], __('app.this_item_cannot_remove'), [], false);
            }
            if (!$item->Pool && !$item->PosX && !$item->PosY) {
                return $this->response(200, [], __('app.this_item_has_been_removed'), [], false);
            }
            $poolID = $item->Pool;
            $update = Item::where('ID', $request->item)->update([
                'Pool' => "0",
                'X' => 0,
                'Y' => 0,
            ]);

            if ($update) {
                if (strpos($item->itemTypes->Category, 'Coral') !== false) {
                    $pool = Pools::where([
                        'Owner' => $user->User_ID,
                        'ID' => $poolID,
                    ])->first();
                    Item::interactCoral($item, $pool, $user, -1);
                }
                ItemHistory::addHistory($user->User_ID, $request->item, 'Remove from pool', time());
                return $this->response(200, ['pool' => $poolID, 'item' => $request->item], __('app.remove_item_from_pool_successful'));
            }
            return $this->response(200, [], __('remove_item_from_pool_failed'), [], false);
        } else {
            return $this->response(200, [], __('no_item_found'), [], false);
        }
    }

    public function checkSeaWeed($seaWeed, $userID)
    {
        $infoSeaWeed = Item::where([
            ['Type', 'LIKE', 'IS%'],
            ['Owner', (string)$userID],
            ['ID', (string)$seaWeed],
            ['Pool', '!=', '0'],
        ])->whereNotNull('Pool')->first();
    }

    public function getFullItem(Request $request)
    {
        $user = $request->user();

        $items = Item::where([
            'Owner' => $user->User_ID,
            'Status' => 1,
        ])->get();

        for ($i=0; $i < count($items); $i++) { 
            if($items[$i]->itemTypes->Category == 'Seaweed'){
                $items[$i]->RemainTime = Item::getRemainTimeSeaweek($items[$i]);
            } else if ($items[$i]->itemTypes->Category == 'Creature' && $items[$i]->Pool != "0"){
                $items[$i]->RemainTime = Item::getRemainTimeHippo($items[$i]);
                $items[$i]->RemainBlood = Item::getRemainBloodHippo($user, $items[$i]);
                $items[$i]->MaxFood = $items[$i]->itemTypes['Data']['FeedingTime']['FeedingFood'];
                $items[$i]->CurrentFood = Item::BloodHippo($items[$i]);
            }
        }

        return $this->response(200, ['items' => $items]);
    }

    public function havestSeaweed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item' => 'required'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $item = Item::where([
            'ID' => $request->item,
            'Owner' => $user->User_ID
        ])->first();

        if (!$item) return $this->response(200, [], __('app.no_item_found'), [], false);

        $fishReceive = Item::interactSeaweed($user, $item);

        if (!$fishReceive) {
            $time = time();
            $havest = $item->itemTypes->Data['HarvestDuration'] - ($time - $item->UpdateTime);
            return $this->response(200, [$havest], __('app.cannot_havest'), [], false);
        }

        return $this->response(200, [
            'feed_receive' => $fishReceive,
            'item' => Item::where([
                'ID' => $request->item,
                'Owner' => $user->User_ID
            ])->first(),
        ], __('app.havest_successful'));
    }

    public function usingItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item' => 'required',
            'fish_id' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $fish = Fishs::where([
            'ID' => $request->fish_id,
            'Owner' => $user->User_ID,
        ])->first();

        $item = Item::where([
            'ID' => $request->item,
            'Owner' => $user->User_ID,
            'Status' => 1,
        ])->first();

        $typeArray = array('IVG', 'IVB');

        if (!$item) return $this->response(200, [], __('app.no_item_found'), [], false);

        if (!$fish) return $this->response(200, [], __('app.no_fish_found'), [], false);

        if ($fish->Status != 1) return $this->response(200, [], __('app.your_fish_died'), [], false);

        if ($fish->ActiveTime == 0) return $this->response(200, [], __('app.this_fish_is_not_activated'), [], false);

        if (!in_array($item->Type, $typeArray)) return $this->response(200, [], __('app.your_item_is_invalid'), [], false);

        if (isset($fish->Items)) {
            //Count item
            $countItem = 0;
            for ($i = 0; $i < count($fish->Items); $i++) {
                $itemType = Item::where('ID', $fish->Items[$i])->first()->Type;
                if ($itemType == $item->Type) $countItem++;
            }

            if ($item->Type == 'IVB' && $countItem == 2) return $this->response(200, [], __('app.your_fish_eats_vitamin_egg_breed_fully'), [], false);

            if ($item->Type == 'IVG' && $countItem == 10) return $this->response(200, [], __('app.your_fish_eats_vitamin_egg_breed_fully'), [], false);
            $arr = $fish->Items;
            array_push($arr, $item->ID);
            $fish->Items = $arr;
        } else {
            $fish->Items = [$item->ID];
        }

        if ($item->Type == 'IVG') {
            $fish->GrowTimeDecrease += 86400;
        }

        $item->Status = -1;
        $item->save();

        $fish->save();

        return $this->response(200, [
            'fish_id' => $request->fish_id,
            'item_id' => $request->item
        ], __('app.feed_fish_successful'));
    }
}
