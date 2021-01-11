<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Http\Controllers\System\CoinbaseController;
use App\Model\Eggs;
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
use App\Model\FoodTypes;
use App\Model\Foods;
use App\Model\Pools;
use App\Model\LogUser;





class FoodController extends Controller{
    public function __construct(){
		$this->middleware('auth:api');
	}
	
	public function getAllFood(){
	    $user = Auth::user();
        $Foods = Pools::where('Owner', $user->User_ID)->get();
        return $this->response(200, $Foods, '', [], true); 
    }
    
    public function getAllFoodTypes(){
	    $user = Auth::user();
        $FoodTypes = FoodTypes::where('Owner', $user->User_ID)->get();
        return $this->response(200, $FoodTypes, '', [], true); 
    }
    
    public function getFood(Request $req){
        $user = Auth::user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
        
        if($checkSpam == null){
            //khoong toonf taij
            return $this->response(422, [], 'Misconduct!', [], false);
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

        
        switch($req->action){
            case 'buy':
                $balance = User::getBalance($user->User_ID, $req->currency_id);
                if(!$balance){
                    return $this->response(200, [], __('app.balance_does_not_exist'), [], false);
                }
                /* *******************************************
                    mua trung 
                ******************************************* */
                if($req->quantity){
                    $quantity = $req->quantity;
                }

                $FoodTypes = FoodTypes::where('Type', (string)$req->type)->first();
                if(!$FoodTypes){
                    return $this->response(200, [], __('app.food_type_does_not_exist'), $validator->errors(), false);
                }

                if($req->currency_id != 3 && $req->currency_id != 9){
                    return $this->response(200, [], __('app.currency_does_not_exist'), [], false);
                }
                
                if($req->currency_id == 3){
                    $price = $FoodTypes->Price;
                }
                if($req->currency_id == 9){
                    $price = $FoodTypes->Gold;
                }
                
                if($quantity*$price > $balance){
                    return $this->response(200, ['balance'=>$balance], __('app.your_balance_is_not_enough'), [], false); 
                }

                // them thức ăn
                $add = Foods::AddFood($user->User_ID, $quantity, $FoodTypes->Type);
                if($add){
                    // chay truc tiep
                    $update = $this->BuyFood($user, $quantity, $FoodTypes, $add, $req->currency_id);

                    //jobs 
                    // dispatch(new EggJobs($user, $quantity, $FoodTypes, $add))->delay(1);
                    $buyFoodType = config('utils.action.buy_food');
                    LogUser::addLogUser($user->User_ID, $buyFoodType['action_type'], 'Buy '.$quantity.' food type '.$FoodTypes->Type, $req->ip(), 18);
                    return $this->response(200, ['balance'=>$balance-$quantity*$price, 'eggID'=>$add], __('app.buy_food_successful'), [], true);
                }
                
                
                /* *******************************************
                   ket thuc mua trung 
                ******************************************* */
            break;
            
            default:
                //$egg = Foods::where('ActiveTime', 0)->where('Owner', $user->User_ID)->select('_id', 'BuyDate', 'ID', 'status')->get();
                //return $this->response(200, $egg, '', [], true);
                
        }
        
    }

    public function BuyFood($user, $quantity, $FoodTypes, $IDArray, $currency_id){

        $balance = User::getBalance($user->User_ID, $currency_id);
        if($currency_id == 3){
            $price = $FoodTypes->Price;
        }
        if($currency_id == 9){
            $price = $FoodTypes->Gold;
        }
        
        if($quantity*$price > $balance){
            Eggs::where('Owner', $user->User_ID)->whereIn('ID', $IDArray)->where('status', 0)->update(['status'=>-1]);
            return false; 
        }
        // tru tien nguoi choi
        $update = true;
        if($update){

            // ghi log lại
            $arrayInsert = array(
                'Money_User' => $user->User_ID,
                'Money_USDT' => -(float)($quantity*$price),
                'Money_USDTFee' => 0,
                'Money_Time' => time(),
                'Money_Comment' => 'Buy '.$quantity.' food',
                'Money_MoneyAction' => 29,
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

            //check commission buy item
            Money::checkCommission($user, 9, $currency_id, $quantity*$price);
            Money::checkAgencyCommission($user, 29, $currency_id, $quantity*$price);
            
            return true;
        }
        return false;
        

    }

    

   
}