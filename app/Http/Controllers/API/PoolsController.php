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
use App\Model\Fishs;
use App\Model\PoolTypes;
use App\Model\Pools;
use App\Model\LogUser;
use App\Model\Item;
use App\Model\Foods;

use App\Jobs\EggJobs;
use App\Jobs\FishItemJobs;

use Carbon\Carbon;



class PoolsController extends Controller{
    public function __construct(){
		$this->middleware('auth:api');
    }
	
	public function getAllPool(){
	    $user = Auth::user();
        $Pools = Pools::where('Owner', $user->User_ID)->orderBy('_id')->get();
        dispatch(new FishItemJobs($user));
        // $Fishs = Fishs::with('fishTypes')->where('Owner', $user->User_ID)->get();
        // //Auto feeding machine
        // Item::autoFeedingMachine($user, $Fishs);
        // //Interact item with pool
        // Item::interactItemWithPool($user);
        return $this->response(200, $Pools, '', [], true); 
    }
	
	public function getPoolList(){
	    $user = Auth::user();
        $Pools = Pools::where('Owner', $user->User_ID)->select('ID', 'Type', 'Name')->orderBy('_id')->get();
        return $this->response(200, $Pools, '', [], true); 
    }
    
    public function getAllPoolTypes(){
	    $user = Auth::user();
        $PoolTypes = PoolTypes::get();
        return $this->response(200, $PoolTypes, '', [], true); 
    }
    
    public function getPool(Request $req){
        $user = Auth::user();

        $quantity = 1;
        // $validator = Validator::make($req->all(), [
        //     'CodeSpam' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return $this->response(200, [], 'Validate wrong', $validator->errors(), false);
        // }
        switch($req->action){
            // mua trung
            case 'buy':
                $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
                
                if($checkSpam == null){
                    //khoong toonf taij
                   //return $this->response(200, [], 'Misconduct!', [], false);
                }else{
                    DB::table('string_token')->where('User', $user->User_ID)->delete();
                }
            	$balance = User::getBalance($user->User_ID, $req->currency_id);
		        if(!$balance){
		            return $this->response(200, [], __('app.balance_does_not_exist'), [], false);
		        }
                if($req->quantity && $req->quantity > 0){
                    $quantity = $req->quantity;
                }else{
                    return $this->response(200, [], __('app.please_enter_quantity'), [], false);
                }

                $PoolTypes = PoolTypes::where('Type', $req->type."")->first();
                if(!$PoolTypes){
                    return $this->response(200, [], __('app.pool_type_does_not_exist'), $validator->errors(), false);
                }
                if($req->currency_id == 3){
                    $price = $PoolTypes->Price;
                }
                if($req->currency_id == 9){
                    $price = $PoolTypes->Gold;
                }
                
                if($quantity*$price > $balance){
                    return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
                }
				
                // them trung
                //$add = Pools::AddPool($user->User_ID, $quantity, $PoolTypes->Type);
                $add = true;
                if($add){
                    // chay truc tiep
                    $update = $this->BuyPool($user, $quantity, $PoolTypes, $add, $req->currency_id, $price);
                    //jobs 
                    //dispatch(new EggJobs($user, $quantity, $PoolTypes, $add))->delay(1);
                    $buyPoolType = config('utils.action.buy_pool');
                    LogUser::addLogUser($user->User_ID, $buyPoolType['action_type'], 'Buy '.$quantity.' pool', $req->ip(), 17);
                    return $this->response(200, [], __('app.buy_pool_successful'), [], true);
                }
                
            break;
            case 'info':
            	if(!$req->pool)
                    return $this->response(200, [], __('app.pool_does_not_exist'), [], false);
                
                $pool = Pools::infoPool($req->pool);
                if($pool){
                    return $this->response(200, $pool, '', [], true);
                }
            break;
            default:
                // if($user->User_ID == 840463){
                //     $pool = Pools::with('poolType')->where('Owner', $user->User_ID)->orderBy('_id')->paginate(1);
                // }else{
                    $pool = Pools::with('poolType')->where('Owner', $user->User_ID)->orderBy('_id')->paginate(1);
                // }
                if($pool->total() == 0){
                    //create a new pool
                    $pool = new Pools();
                    $pool->Skin = 0;
                    $pool->Type = "1";
                    $pool->CreateAt = Carbon::now()->toDateTimeString();
                    $pool->Owner = $user->User_ID;
                    $pool->ID = Pools::RandomPoolID();
                    $pool->save();
                }

                dispatch(new FishItemJobs($user));

                // $Fishs = Fishs::with('fishTypes')->where('Owner', $user->User_ID)->get();

                // //Auto feeding machine
                // Item::autoFeedingMachine($user, $Fishs);
                // Fishs::updateCurrentFish($Fishs, $user);
                // Item::interactItemWithPool($user);

                // $items = Item::where([
                //     'Owner' => $user->User_ID,
                //     'Status' => 1,
                // ])->get();
        
                // for ($i=0; $i < count($items); $i++) { 
                //     if($items[$i]->itemTypes->Category == 'Creature' && $items[$i]->Pool != "0"){
                //         $items[$i]->RemainBlood = Item::getRemainBloodHippo($user, $items[$i]);
                //     }
                // }

                // //Interact item with pool
                // Item::interactItemWithPool($user);

                $returnData = array();
                foreach($pool as $v){
	                $returnData['list'][] = array(
		                'ID' => $v->ID,
		                'Type' => $v->Type,
		                'Skin' => $v->Skin,
		                'Name' => $v->poolType->Name,
		                'Child' => Pools::infoPool($v->ID),
	                );
                }
                $returnData['total_page'] = $pool->lastpage();
                $returnData['current_page'] = $pool->currentPage();
                return $this->response(200, $returnData, '', [], true);
            break;
        }
        
    }

    public function getCurrentBlood($id, $user){
        $fish = Fishs::where([
            'Owner' => $user->User_ID,
            'ID' => $id,
        ])->first();

        $SECCOND_DATE = 86400;
        $DAY_DEAD = 5;

        if($fish){
            //blood loss per second
            $bloodLossPerSecond = (double) ($fish->fishTypes->MaxFood / $DAY_DEAD) / $SECCOND_DATE;

            $bloodTimeLoss = (double) (time() - $fish->FeedTime)*$bloodLossPerSecond;
            $remainBlood = $fish->CurrentFood - $bloodTimeLoss;

            if($remainBlood <= 0 && !isset($fish->DeadTime)){
                $fish->DeadTime = time();
            }

            $fish->FeedTime = time();
            
            $fish->save();

            return $remainBlood;

        } else {
            return null;
        }
    }

    public function BuyPool($user, $quantity, $PoolTypes, $tidArray, $currency_id, $price){

        $balance = User::getBalance($user->User_ID, $currency_id);
      
        if($quantity*$price > $balance){
            Pools::where('Owner', $user->User_ID)->whereIn('tid', $tidArray)->where('status', 0)->update(['status'=>-1]);
            return false;
        }
        // tru tien nguoi choi
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => -(float)($quantity*$price),
            'Money_USDTFee' => 0,
            'Money_Time' => time(),
            'Money_Comment' => 'Buy '.$quantity.' pool',
            'Money_MoneyAction' => 28,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => $currency_id,
            'Money_CurrentAmount' => (float)($quantity*$price),
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_Confirm_Time' => null,
            'Money_FromAPI' => 1,
        );
        Money::insert($arrayInsert);
        //check commission
        Money::checkCommission($user, 9, $currency_id, $quantity*$price);
        Money::checkAgencyCommission($user, 28, $currency_id, $quantity*$price);
    }
   
}