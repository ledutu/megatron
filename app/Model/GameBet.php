<?php

namespace App\Model;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Carbon\Carbon;

use App\Model\Money;


use DB;
class GameBet extends Eloquent
{
	protected $connection = 'mongodb';
    protected $collection = 'gamebets';
    protected $fillable = ['_id','GameBet_SubAccount','GameBet_SubAccountLevel','GameBet_SubAccountType','GameBet_SubAccountUser','GameBet_SubAccountEndBalance','GameBet_Type','GameBet_Symbol','GameBet_Amount','GameBet_Fund','GameBet_Status','GameBet_Log','GameBet_IB','GameBet_Notification', 'GameBet_CopyTrade', 'GameBet_datetime'];
    
    public $timestamps = true;
    
/*
    public function Action(){
	    return $this->hasMany('App\Model3\ActionName','Action_ActionID');
    }
*/
  
  	public static function setRankUser($userID, $level){
      	if($level == 0){
          	$cancelSet = DB::table('set_agency')->where('user', $userID)->where('status', 1)->update(['status'=>-1]);
          	return true;
        }elseif($level <= 7){
          	$cancelSet = DB::table('set_agency')->where('user', $userID)->where('status', 1)->update(['status'=>-1]);
          	
          	$setRank = DB::table('set_agency')
              			->insert([
                            'user' => $userID,
                            'level' => $level,
                            'datetime' => date('Y-m-d H:i:s'),
                            'status' => 1,
                        ]);
          	return true;
        }else{
          	return false;
        }
    }
  	
  	public static function checkSetRank($userID){
        $checkSetRank = DB::table('set_agency')->where('user', $userID)->where('status', 1)->orderByDesc('level')->first();
      	if(!$checkSetRank){
          	return false;
        }
        return $checkSetRank->level;
    }
  	
	public static function getRankUser($userID, $fromDate, $toDate, $isSave = 0){
      	/*
		$arrUserLevelUp = [
          	901851 => 7,
            985800 => 7,
            848631 => 7,
            387143 => 5,
            210780 => 7,
            628914 => 7,
            410416 => 5,
            961177 => 7,
            738490 => 7,
            907984 => 5,
            200663 => 7,
            620371 => 5,
            370163 => 5,
            152556 => 5,
            940914 => 7,
            881210 => 7,
            844083 => 7,
            388147 => 7,
            113808 => 5,
            645565 => 5,
            830535 => 5,
            368611 => 5,
            496282 => 7,
            808212 => 5,
            463884 => 5,
            179503 => 5,
          	731847 => 5,
            942330 => 5,
            339296 => 5,
            799476 => 5,
            841034 => 5,
            788052 => 5,
            222180 => 5,
            886135 => 5,
            438032 => 5,
            443138 => 5,
            294504 => 5,
            750745 => 5,
            444202 => 1,
            477692 => 1,
            899084 => 1,
            338029 => 1,
            450718 => 1,
            183581 => 5,
            267271 => 4,
            540083 => 5,
            999877 => 5,
            331024 => 5,
            658803 => 5,
            496688 => 4,
            128205 => 3,
            291586 => 3,
            567800 => 3,
            762728 => 5,
            562187 => 3,
            631920 => 3,
          	496688 => 4,
            128205 => 3,
            291586 => 3,
            567800 => 3,
            762728 => 5,
            562187 => 3,
            631920 => 3,
            151107 => 5,
            998575 => 5,
            502129 => 5,
            763581 => 5,
            675950 => 5,
            429429 => 4,
            670720 => 5,
            392904 => 5,
            254568 => 5,
            300861 => 5,
            612198 => 2,
            117676 => 2,
            255665 => 2,
            900795 => 2,
            885344 => 2,
            353902 => 2,
            459061 => 2,
            754273 => 2,
            201043 => 2,
            235932 => 2,
            200949 => 3,
          	194585 => 5,
          	816554 => 3,
            194585 => 5,
          	628914 => 7,
            529726 => 5,
            445555 => 4,
            388787 => 4,
            786315 => 5,
            224994 => 3,
            476500 => 3,
            163089 => 5,
            687479 => 5,
            448268 => 5,
            623302 => 5,
            575723 => 5,
            310526 => 3,
          	388104 => 5,
            410891 => 5,
            499273 => 5,
            118937 => 5,
            412897 => 5,
            556323 => 5,
            636473 => 5,
            635046 => 4,
          	203484 => 5,
          	976045 => 2,
            620740 => 2,
            203484 => 5,
          	797292 => 5,
            234422 => 5,
          	756056 => 5,
            944748 => 5,
            222008 => 3,
          	898716 => 3,
            310204 => 3,
          	701263 => 5,
          	361494 => 3,
          	288075 => 5,
            665647 => 5,
          	398378 => 4,
          	294948 => 3,
          	//test
          	//832319 => 7,
          	457167 =>7,
          	750868 =>6,
            213459 =>3,
            949577 =>0,
            346489 =>1,
            145089 =>2,
            286519 =>5,
            283603 =>5,
            390013 =>4,
            927492 =>6,
		];
		//set cấp cho từng ID
		if(isset($arrUserLevelUp[$userID])){
			$package = $arrUserLevelUp[$userID];
			return $package;
		}
        */
      	$getCheckRank = GameBet::checkSetRank($userID);
      	if($getCheckRank){
          	return $getCheckRank;
        }
      	$rankID = 0;
      	$rank = GameBet::getRank();
      	$checkBuyAgency = Money::checkBuyAgency($userID);
      	if(!$checkBuyAgency){
          	return $rankID;
        }
      	$checkInserted = DB::table('package_weekly')
							->where('package_weekly_User', $userID)
							->where('package_weekly_FromDate', '>=', $fromDate)
							->where('package_weekly_ToDate', '<=', $toDate)
							->first();
		if($checkInserted){
			$rankID = $checkInserted->package_weekly_Level;
          	return $rankID;
		}
      	
      	$volumeTradeF1 = GameBet::getVolumeTradeF1($userID, $fromDate, $toDate);
      	$getBuyAgencyF1 = GameBet::getBuyAgencyF1($userID);
      	foreach($rank as $id=>$r){
          	//ko thoả điều kiện thì return rankID
          	if( $getBuyAgencyF1 < $r['AgencyF1'] || $volumeTradeF1 < $r['TradeF1'] ){
              	break;
            }
          	$rankID = $id;
        }
        if($isSave == 1){
            $dataPackageWeek = [
              'package_weekly_User' => $userID,
              'package_weekly_FromDate' => $fromDate,
              'package_weekly_ToDate' => $toDate,
              'package_weekly_Level' => $rankID,
              'package_weekly_TotalBetF1' => $volumeTradeF1,
              'package_weekly_Status' => 1
            ];
            $insertPackage = DB::table('package_weekly')->insert($dataPackageWeek);
        }
      	return $rankID;
    }
	
    public static function getRank(){
      	$rank = [
          	0 =>['AgencyF1'=>0, 'TradeF1'=>0, 'Name'=> __('agency.member'), 'F'=>0, 'Image'=>'https://igtrade.co/exchange/img/level/0.png'],
          	1 =>['AgencyF1'=>0, 'TradeF1'=>0, 'Name'=> __('agency.rank') . ' 1', 'F'=>1, 'Image'=>'https://igtrade.co/exchange/img/level/1.png'],
          	2 =>['AgencyF1'=>3, 'TradeF1'=>1500, 'Name'=> __('agency.rank') . ' 2', 'F'=>2, 'Image'=>'https://igtrade.co/exchange/img/level/2.png'],
          	3 =>['AgencyF1'=>4, 'TradeF1'=>3500, 'Name'=> __('agency.rank') . ' 3', 'F'=>3, 'Image'=>'https://igtrade.co/exchange/img/level/3.png'],
          	4 =>['AgencyF1'=>5, 'TradeF1'=>8000, 'Name'=> __('agency.rank') . ' 4', 'F'=>4, 'Image'=>'https://igtrade.co/exchange/img/level/4.png'],
          	5 =>['AgencyF1'=>6, 'TradeF1'=>15000, 'Name'=> __('agency.rank') . ' 5', 'F'=>5, 'Image'=>'https://igtrade.co/exchange/img/level/5.png'],
          	6 =>['AgencyF1'=>7, 'TradeF1'=>30000, 'Name'=> __('agency.rank') . ' 6', 'F'=>6, 'Image'=>'https://igtrade.co/exchange/img/level/6.png'],
          	7 =>['AgencyF1'=>8, 'TradeF1'=>60000, 'Name'=> __('agency.rank') . ' 7', 'F'=>7, 'Image'=>'https://igtrade.co/exchange/img/level/7.png'],
		];
      	return $rank;
    }

	public static function getTotalBet($userID, $fromDate, $toDate){
		$getInfo = DB::table('statistical')
					->join('users', 'user_ID', 'statistical_User')
					->where('statistical_User', $userID)
					->where('statistical_Time', '>=', $fromDate)
					->where('statistical_Time', '<', $toDate)
					->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email, User_Tree')
					->groupBy('statistical_User')
					->first();
		return $getInfo;
	}

	public static function getVolumeTradeF1($userID, $fromDate, $toDate){
		$getVolumeTradeF1 = DB::table('statistical')
						->join('users', 'User_ID', 'statistical_User')
						->whereRaw("User_Parent = $userID")
						->where('statistical_Time', '>=', $fromDate)
						->where('statistical_Time', '<', $toDate)
						->where('statistical_TotalBet', '>=', 100)
						->selectRaw('(statistical_TotalBet) as totalBet, User_ID , User_Email, User_Tree')
						->groupBy('statistical_User')
						->get();
		return $getVolumeTradeF1->sum('totalBet');
	}

	public static function getBuyAgencyF1($userID){
		$getF1Active = Money::join('users','User_ID', 'Money_User')
          					->where('Money_MoneyAction', 57)
          					->where('Money_MoneyStatus', 1)
          					->where('User_Parent', $userID)
          					->select('User_ID', 'User_Parent', 'User_Tree')
          					->get();
		return $getF1Active->count();
	}
}
