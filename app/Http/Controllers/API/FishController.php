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
use DateTime;
use DB;
use Mail;
use GuzzleHttp\Client;
use App\Model\Wallet;
use App\Model\GoogleAuth;
use App\Model\User;
use App\Model\userBalance;
use App\Model\Money;
use App\Model\FishTypes;
use App\Model\Fishs;
use App\Model\Eggs;
use App\Model\Pools;
use App\Model\LogUser;
use App\Model\Foods; 
use App\Model\Item; 
use App\Model\EggFailed; 

use App\Jobs\EggJobs;
use App\Jobs\FishItemJobs;
use App\Model\EggHatched;
use App\Model\Utils;
use Illuminate\Support\LazyCollection;

class FishController extends Controller{
    public function __construct(){
		$this->middleware('auth:api');
    }
    
    public function getHistoryBuyEggs(Request $request){
        $user = $request->user();
        // $result = DB::table('eggsTemp')->paginate(100);
        return $this->response(200, [
            'eusd' => User::getBalance($user->User_ID, 3),
            'gold' => User::getBalance($user->User_ID, 9),
        ]);
        
        // return $this->response(200, $result, '', [] , true); 
    }
    
    public function getAllFish(){
	    $user = Auth::user();
        $Fishs = Fishs::where([
            'Owner' => $user->User_ID, 
            'Status' => 1
        ])->get();
    
        dispatch(new FishItemJobs($user));
        // Fishs::updateCurrentFish($Fishs, $user);

        // //Check auto feeding machine
        // Item::autoFeedingMachine($user, $Fishs);

        // //Interact item with pool
        // Item::interactItemWithPool($user);

        return $this->response(200, $Fishs); 
    }
    
    public function getAllFishTypes(){
	    $user = Auth::user();
        $FishTypes = FishTypes::where('Owner', $user->User_ID)->get();
        return $this->response(200, $FishTypes, '', [], true); 
    }
    
    public function getFish(Request $req){
        
        $user = Auth::user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
        
        if($checkSpam == null){
            //khoong toonf taij
            //return $this->response(422, [], 'Misconduct!', [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
        
        
        $quantity = 1;
        $validator = Validator::make($req->all(), [
            'CodeSpam' => 'required'
        ]);

        if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }

        
        $balance = User::getBalance($user->User_ID, 3);
        
        switch($req->action){
            
            case 'active':
                $balance = User::getBalance($user->User_ID, $req->currency_id);
                
                if(!$balance){
                    return $this->response(200, [], __('app.balance_does_not_exist'), [], false);
                }
                
                /* *******************************************
                active ca
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
                $fish = Fishs::where('ID', $req->fish)->where('Owner', $user->User_ID)->first();
                if(!$fish)
                    return $this->response(200, [], __('app.fish_does_not_exist'), [], false);
                if($fish->ActiveTime !=0)
                    return $this->response(200, [], __('app.fish_has_already_activated'), [], false);
                

                $fishType = FishTypes::where('Type', $fish->Type)->first();
                if($req->currency_id != 3 && $req->currency_id != 9){
                    return $this->response(200, [], __('app.currency_does_not_exist'), [], false);
                }


                
                if($req->currency_id == 3){
                    $price = $fishType->ActiveCost;
                }
                if($req->currency_id == 9){
                    $price = $fishType->ActiveGold;
                }
                if($price > $balance){
                    return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
                }

                $update = true;                                                                            
                if($update){
                    $GrowTime = rand($fish->fishTypes->LevelUpTime[0], $fish->fishTypes->LevelUpTime[1]);
                    $ActiveTime = time();
                    $GrowTimeDecrease = 0;
                    $getCoral = Item::with('itemTypes')->where([
                        'Pool' => $fish->Pool,
                        'Owner' => $user->User_ID,
                    ])->whereHas('itemTypes', function ($query) {
                        return $query->where('Category', 'Coral');
                    })->where('Status', '!=', -1)->first();
                    if($getCoral && $GrowTimeDecrease == 0){
                        if($fish->fishTypes->Level < 3 && $getCoral->Type == "IC1"){
                            $GrowTimeDecrease = $GrowTime * $getCoral->itemTypes->Data['Effect'];
                        }elseif($fish->fishTypes->Level >= 3 && ($getCoral->Type == "IC2" || $getCoral->Type == "IC3")){
                            $GrowTimeDecrease = $GrowTime * $getCoral->itemTypes->Data['Effect'];
                        }
                    }
                    Fishs::where([
                        'Owner' => $user->User_ID,
                        'ID' => $req->fish,
                    ])->update([
                        'ActiveTime' => $ActiveTime,
                        'GrowTime' => $GrowTime,
                        'GrowTimeDecrease' => abs($GrowTimeDecrease),
                    ]);

                    if(count($fish->fishTypes['EggBreed'])){
                        $getLastedEggFish = EggFailed::where('fish_id', $fish->ID)->where('level_fish', (int) $fish->fishTypes['Level'] - 1)->where('eggs_fail', '>=', 0)->first();
                        if($getLastedEggFish){
                            $eggFailed = EggFailed::where('user', null)->where('level_fish', (int) $fish->fishTypes['Level'])->where('eggs_fail', 0)->first();
                        }else{
                            $eggFailed = EggFailed::where('user', null)->where('level_fish', (int) $fish->fishTypes['Level'])->first();
                        }
                        $eggFailed->user = $user->User_ID;
                        $eggFailed->fish_id = $fish->ID;
                        $eggFailed->save();
                    }

                    $arrayInsert = array();
                    $arrayInsert[] = array(
                        'Money_User' => (int)$user->User_ID,
                        'Money_USDT' => -$price,
                        'Money_USDTFee' => 0,
                        'Money_Time' => time(),
                        'Money_Comment' => 'Active fish ID:'.$req->fish,
                        'Money_MoneyAction' => 36,
                        'Money_MoneyStatus' => 1,
                        'Money_Address' => null,
                        'Money_Currency' => (int)$req->currency_id,
                        'Money_CurrentAmount' => $price,
                        'Money_Rate' => 1,
                        'Money_Confirm' => 0,
                        'Money_Confirm_Time' => null,
                        'Money_FromAPI' => 1,
                    );
                   
                    Money::insert($arrayInsert);
                    
                    LogUser::addLogUser($user->User_ID, 'Active Fish', 'Active Eggs ID:'.$req->egg, $req->ip(), 28);
                    //check commission
                    Money::checkCommission($user, 8, $req->currency_id, $price);
                    Money::checkAgencyCommission($user, 36, $req->currency_id, $price);
                    return $this->response(200, [
                        'fish'=>$req->fish,
                        'balance' => [
                            'EUSD' => User::getBalance($user->User_ID, 3),
                            'GOLD' => User::getBalance($user->User_ID, 9),
                        ],
                        'ActiveTime' => $ActiveTime,
                        // 'LevelUpRemain' => $GrowTimeDecrease,
                        'GrowTime' => $GrowTime  - $GrowTimeDecrease
                    ], __('app.successful_fish_activation'), [], true);
                }
                return $this->response(200, [], '', [], false);
                /* *******************************************
                ket thuc active trung
                ******************************************* */
            break;
                
        }
        
    }
    // so luong mau
    // feedtime

    public function feedFish(Request $request){

        $validator = Validator::make($request->all(), [
            'fish_id' => 'required',
            'amount' => 'required|numeric|min:1',
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }

        $user = $request->user();

        $fish = Fishs::where([
            'Owner' => $user->User_ID,
            'ID' => $request->fish_id,
        ])->first();

        $foodAmount = Foods::where('Owner', $user->User_ID)->first()->Amount;

        if($foodAmount == 0){
            return $this->response(200, [], __('app.you_have_no_food_to_feed'), [], false);
        }

        $SECCOND_DATE = 86400;

        if($fish){

            if($fish->CurrentFood <= 0 || $fish->Status == -1) return $this->response(200, [], __('app.your_fish_died'), [], false);

            //blood loss per second
            $bloodLossPerSecond = (double) ($fish->fishTypes->MaxFood / 5) / $SECCOND_DATE;

            //Time loss blood to second
            $bloodTimeLoss = (double) (time() - $fish->FeedTime)*$bloodLossPerSecond;
            
            $remainBlood = $fish->CurrentFood - $bloodTimeLoss;

            if($remainBlood < 0) {
                return $this->response(200, [], __('app.your_fish_died'), [], false);
            }

            $food = Foods::where('Owner', $user->User_ID)->first();

            $blood = $request->amount > $food->Amount? $food->Amount: $request->amount;

            $newBlood = $remainBlood + $blood;

            Fishs::where([
                'Owner' => $user->User_ID,
                'ID' => $request->fish_id,
            ])->update([
                'CurrentFood' => $newBlood > $fish->fishTypes->MaxFood ? $fish->fishTypes->MaxFood : $newBlood,
                'FeedTime' => time(),
            ]);
            
            $remainAmount = $food->Amount - $request->amount;

            // return $food;

            Foods::where('Owner', $user->User_ID)->update([
                'Amount' => $remainAmount < 0 ? 0 : $remainAmount
            ]);

            $feedFishType = config('utils.action.feed_fish');
            LogUser::addLogUser($user->User_ID, $feedFishType['action_type'], 'Feed fish '.$request->fish_id.' with '.$blood, $request->ip(), 26);

            return $this->response(200, ['fish'=>$request->fish_id, 
                'total_food' => Foods::where([
					'Owner' => $user->User_ID,
					])->sum('Amount'),
            ], 'Feed fish successful');

        } else {
            return $this->response(200, [], __('app.fish_does_not_exist'), [], false);
        }
    }

    public function autoFeedFish(Request $request){
        $user = $request->user();
        $fishs = Fishs::where('Owner', $user->User_ID)->get();
        $food = Foods::where('Owner', $user->User_ID)->first();

        $SECCOND_DATE = 86400;
        $FISH_DIE_TIME = 5 * $SECCOND_DATE;

        if(count($fishs) == 0){
            return $this->response(200, [], __('app.you_have_no_fish'), [], false);
        }

        if($food->Amount == 0){
            return $this->response(200, [], __('app.you_have_no_food_to_feed'), [], false);
        }

        foreach ($fishs as $fish) {
            //blood loss per second
            $bloodLossPerSecond = (double) ($fish->fishTypes->MaxFood / 5) / $SECCOND_DATE;

            //Time loss blood to second
            $bloodTimeLoss = (double) (time() - $fish->FeedTime)*$bloodLossPerSecond;
            
            $remainBlood = $fish->CurrentFood - $bloodTimeLoss;

            if($remainBlood < 0) {
                continue;
            }

            $food = Foods::where('Owner', $user->User_ID)->first();

            $blood = $fish->fishTypes->MaxFood - $remainBlood;

            $newBlood = $remainBlood + ($blood > $food->Amount ? $food->Amount: $blood);
        
            //Update fish
            $fish->CurrentFood = $newBlood;
            $fish->FeedTime = time();
            $fish->save();

            // Fishs::where([
            //     'Owner' => $user->User_ID,
            //     'ID' => $request->fish_id,
            // ])->update([
            //     'CurrentFood' => $newBlood,
            //     'FeedTime' => time(),
            // ]);
            
            $remainAmount = $food->Amount - $blood;
            Foods::where('Owner', $user->User_ID)->update([
                'Amount' => $remainAmount < 0 ? 0 : (int) $remainAmount
            ]);

            if($remainAmount < 0) break;
        }

        return $this->response(200, [
            'food' => Foods::where(['Owner' => $user->User_ID])->sum('Amount'),
        ], __('app.feed_fish_successful'));
    }

    public function eggBreed(Request $request){
        $validator = Validator::make($request->all(), [
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
            'Owner' => $user->User_ID,
            'ID' => $request->fish_id
        ])->first();

        if(!$fish->fishTypes->NextType){
            $fish->Status = 2;
            $fish->Pool = "0";
            $fish->save();
            return $this->response(200, [
                'fish'=>$request->fish_id,
                'eggs' => [],
                'max' => true
            ], __('Your fish is on max level'), [], false);
        }

        if($fish->ActiveTime == 0){
            return $this->response(200, [], __('app.this_fish_is_not_activated'), [], false);
        }

        if((time() - $fish->ActiveTime) < $fish->GrowTime ){
            return $this->response(200, [], __('app.cannot_egg_breed_because_time_is_not_over'), [], false);
        }

        $eggs = [];

        if(count($fish->fishTypes->EggBreed) > 0){
            $eggBreedSuccess = 0;
            $eggBreedFail = 0;
            $eggFail = EggFailed::where([
                'user' => $user->User_ID,
                'fish_id' => $fish->ID,
                'level_fish' => $fish->fishTypes['Level'],
            ])->first();
            if(!$eggFail){
                $eggBreedSuccess = $fish->fishTypes->EggBreed[0];
                $eggBreedFail = 0;
            } else {
                $eggBreedSuccess = $eggFail->eggs_success;
                $eggBreedFail = $eggFail->eggs_fail;
            }

            for ($i=0; $i < $eggBreedFail; $i++) { 
                $egg = new Eggs();
                $egg->F = $fish->F + 1;
                $egg->Pool = "0";
                $egg->PosX = 0;
                $egg->PosY = 0;
                $egg->CanHatches = false;
                $egg->HatchesTime = 0;
                $egg->ActiveTime = 0;
                $egg->Status = 1;
                $egg->BuyDate = time();
                $egg->BuyFrom = $fish->ID;
                $egg->Type = "1";
                $egg->Owner = $user->User_ID;
                $egg->ID = Eggs::RandonEggID();
                array_push($eggs, $egg->ID);
                $egg->save();
            }

            for ($i=0; $i < $eggBreedSuccess; $i++) { 
                $egg = new Eggs();
                $egg->F = $fish->F + 1;
                $egg->Pool = "0";
                $egg->PosX = 0;
                $egg->PosY = 0;
                $egg->CanHatches = true;
                $egg->HatchesTime = 0;
                $egg->ActiveTime = 0;
                $egg->Status = 1;
                $egg->BuyDate = time();
                $egg->BuyFrom = $fish->ID;
                $egg->Type = "1";
                $egg->Owner = $user->User_ID;
                $egg->ID = Eggs::RandonEggID();
                array_push($eggs, $egg->ID);
                $egg->save();
            }
        }
        
        //Đẻ trứng
        // if(count($fish->fishTypes->EggBreed) > 0){
        //     // $amount = rand($fish->fishTypes->EggBreed[0], $fish->fishTypes->EggBreed[1]);
        //     $eggBreedSuccess = 0;
        //     $eggBreedFail = 0;
        //     $eggFail = EggFailed::where([
        //         'user' => $user->User_ID,
        //         'fish_id' => $fish->ID,
        //         'level_fish' => $fish->fishTypes['Level'],
        //     ])->first();

        //     //Get min amount egg breed
        //     $amount = $fish->fishTypes->EggBreed[0];
        //     $percentEgg = rand(5, 10);
        //     $random = rand(1, 100);

        //     //Check vitamin in fish
        //     if(isset($fish->Items)){
        //         for ($i=0; $i < count($fish->Items); $i++) { 
        //             $item = Item::where('ID', $fish->Items[$i])->first();
        //             if($item->Type == 'IVB'){
        //                 $percentEgg += 20;
        //             }

        //             if($percentEgg > 60){
        //                 $percentEgg = 60;
        //             }
        //         }
        //     }

        //     if($percentEgg >= $random) $amount++;

        //     //Check amount egg breed with max egg breed
        //     if($amount > $fish->fishTypes->EggBreed[1]) $amount = $fish->fishTypes->EggBreed[1];
        //     $amount = $fish->fishTypes->EggBreed[0];
        //     $user->User_TotalEggs += $amount;
        //     $user->save();

        //     //Get egg breed date on DB
        //     $eggHatch = [];
        //     $time = time();
        //     foreach (EggHatched::where('Status', 1)->cursor() as $value) {
        //         $date = date('m/d/Y', $time);
        //         if(date('m/d/Y', $value->Date_Hatching) == $date){
        //             array_push($eggHatch, $value);
        //         }
        //     }

        //     for($i = 0; $i < $amount; $i++){
        //         $random = rand(0, 10000);
        //         $egg = new Eggs();
        //         $egg->F = $fish->F + 1;
        //         $egg->Pool = "0";
        //         $egg->PosX = 0;
        //         $egg->PosY = 0;
        //         $egg->CanHatches = $random > 9500? false: true;

        //         if(count($eggHatch) > 0){
        //             //Get egg hatch amount to compare
        //             $currentEggHatch = EggHatched::find($eggHatch[0]->ID);

        //             if($currentEggHatch->Total_Egg >= $currentEggHatch->Amount){
        //                 $egg->CanHatches = false;
        //             }

        //             if($egg->CanHatches){
        //                 EggHatched::where('ID', $eggHatch[0]->ID)->update([
        //                     'Total_Egg' => $currentEggHatch->Total_Egg + 1,
        //                 ]);
        //             }
        //         }

        //         if($eggBreedSuccess >= $eggFail->eggs_success){
        //             $egg->CanHatches = false;
        //         }

        //         if($egg->CanHatches) $eggBreedSuccess++;

        //         $egg->HatchesTime = 0;
        //         $egg->ActiveTime = 0;
        //         $egg->Status = 1;
        //         $egg->BuyDate = time();
        //         $egg->BuyFrom = $fish->ID;
        //         $egg->Type = "1";
        //         $egg->Owner = $user->User_ID;
        //         $egg->ID = Eggs::RandonEggID();
        //         array_push($eggs, $egg->ID);
        //         $egg->save();
        //     }
        // } 

        $nextType = FishTypes::where('Type', $fish->fishTypes->NextType)->first();
        Fishs::where([
            'Owner' => $user->User_ID,
            'ID' => $request->fish_id
        ])->update([
            'Type' => $fish->fishTypes->NextType,
            'ActiveTime' => $fish->fishTypes->AutoLevelUp ? time(): 0,
            'GrowTime' => $fish->fishTypes->AutoLevelUp? rand($nextType->LevelUpTime[0], $nextType->LevelUpTime[1]): 0,
            'Items' => []
        ]);
        if(count($eggs)){
            $message = __('app.egg_breed_successful');
            $eggBreedType = config('utils.action.egg_breed');
            LogUser::addLogUser($user->User_ID, $eggBreedType['action_type'], 'Egg breed fish '.$request->fish_id, $request->ip(), 27);
        }else{
            $message = __('app.upgrade_level_success');
            $eggBreedType = config('utils.action.fish_update_level');
            LogUser::addLogUser($user->User_ID, $eggBreedType['action_type'], 'Fish upgrade level '.$request->fish_id, $request->ip(), 27);
        }
        $fishInfo = Fishs::where('ID', $request->fish_id)->first();
        $LevelUpRemain = $fishInfo->ActiveTime + $fishInfo->GrowTime - time();
        $fishInfo->LevelUpRemain = $LevelUpRemain;
        $fishInfo->save();
    
        return $this->response(200, [
            'fish'=>$request->fish_id,
            'eggs' => $eggs,
            'new_fish' => $fishInfo
        ], $message);
    }
   
    public function getCurrentBlood($id, $user){
        $fish = Fishs::where([
            'Owner' => $user->User_ID,
            'ID' => $id,
        ])->first();

        $SECCOND_DATE = 100;

        if($fish){
            //blood loss per second
            $bloodLossPerSecond = (double) ($fish->fishTypes->MaxFood / 5) / $SECCOND_DATE;

            $bloodTimeLoss = (double) (time() - $fish->FeedTime)*$bloodLossPerSecond;
            $remainBlood = $fish->CurrentFood - $bloodTimeLoss;

            $fish->FeedTime = time();
            
            $fish->save();

            return $remainBlood;

        } else {
            return null;
        }
    }

    public function removeFishFromPool(Request $request){
        $validator = Validator::make($request->all(), [
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
            'Owner' => $user->User_ID,
            'ID' => $request->fish_id
        ])->first();

        if($fish->Status == -1){
            return $this->response(200, ['fish_id' => $request->fish_id], __('app.your_fish_has_already_removed'), [], false);
        }

        $remainBlood = $this->getCurrentBlood($request->fish_id, $user);

        if($remainBlood < 0 || $fish->Status == 2 || $fish->NextType == ""){
            $fish->Status = -1;
            $fish->Pool = "";
            $fish->save();
            return $this->response(200, ['fish_id' => $request->fish_id], __('app.remove_fish_from_pool_successful'));
        } 

        return $this->response(200, ['fish_id' => $request->fish_id], __('app.remove_failed'), [], false);
    }
}