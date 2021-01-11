<?php

namespace App\Model;


use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'User_Name', 'User_Password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'User_Password'
    ];
    
    protected $primaryKey = 'User_ID';
    
    public $timestamps = false;
    
  	public function AddressDeposit()
	{
		return $this->hasMany('App\Model\Wallet', 'Address_User')->orderBy('Address_Currency');
	}
	
	public static function checkAccountByUser($u){
		$user = User::where('User_Name', $u)->first();
		if(!$user){
			return null;
		}
		return $user;
	}
	
	public static function checkAccountByEmail($e){
		$user = User::where('User_Email', $e)->first();
		if(!$user){
			return null;
		}
		return $user;
	}
  
  	public static function getBalance($userID, $coin = 3){
      
		$balancetemp = 0;
		$time = 0;
		$userBalance = DB::table('userBalance')->where('user', $userID)->where('currency', $coin)->first();
		
		if($userBalance){
			$balancetemp += $userBalance->balance;
			$time = strtotime($userBalance->update_at);
		}
		
		$money = Money::where('Money_User', $userID)->whereIn('Money_MoneyStatus', [0,1])->where('Money_Time', '>', $time)->where('Money_Currency', $coin);
		
		$money = $money->selectRaw('COALESCE(SUM(`Money_USDT`+`Money_USDTFee`), 0) as total')->first();
		
		$balancetemp += $money->total*1;
		
		return $balancetemp*1;
	}

	public static function getBalanceGame($userID, $coin = 5){
		$balancetemp = 0;
		$time = 0;
		$userBalance = DB::table('balance_game')->where('user', $userID)->where('currency', $coin)->orderByDesc('time')->first();
		
		if($userBalance){
			$balancetemp += $userBalance->amount;
			$time = $userBalance->time;
		}
		$money = GameBet::where('GameBet_SubAccountUser', (int)$userID)->where('GameBet_Currency', (int)$coin)->where('GameBet_datetime', '>=', (int)$time)->sum('GameBet_AmountWin');

		$balancetemp += $money*1;
		
		return $balancetemp*1;
	}

	public static function getMemberList($user, $request = null, $limit = 20)
    {
        $user_list = User::select('user_agency_level_Name', 'Profile_Status', 'User_ID', 'User_Email', 'User_Phone', 'User_FullName', 'User_RegisteredDatetime', 'User_Parent', DB::raw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " AS f, User_Agency_Level, User_Tree"))
            ->leftJoin('profile', 'Profile_User', 'User_ID')
            ->leftJoin('user_agency_level', 'User_Agency_Level', 'user_agency_level_ID')
            ->whereRaw('User_Tree LIKE "' . $user->User_Tree . '%"')
            ->where('User_ID', '<>', $user->User_ID)
            ->orderBy('User_RegisteredDatetime', 'DESC');
        if( isset($request) ){
            if($request->user_id){
                $user_list = $user_list->where('User_ID', $request->user_id);
            }
            if($request->user_email){
                $user_list = $user_list->where('User_Email', 'LIKE', "%$request->user_email%");
            }
            if($request->user_f){
                $user_list = $user_list->whereRaw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " = ". $request->user_f);
            }
        }
        if($limit){
            $user_list = $user_list->paginate($limit);
        }else{
            $user_list = $user_list->get();
        }
        return $user_list;
    }
    
}

