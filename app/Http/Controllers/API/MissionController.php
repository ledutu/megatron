<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ListMission;
use App\Model\LogMission;
use App\Model\LogUser;
use App\Model\Mission;
use App\Model\Money;
use App\Model\User;
use App\Model\Fishs;
use App\Model\FishTypes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Facades\Validator;

class MissionController extends Controller
{
    public function __construct(){
		$this->middleware('auth:api');
    }
    
    public function getListReward(){
        $arr_gold = [
            0 => '10 Gold',
            1 => '500 Gold',
            2 => '20 Gold',
            3 => '400 Gold',
            4 => 'You lose',
            5 => '30 Gold',
            6 => '300 Gold',
            7 => '40 Gold',
            8 => '200 Gold',
            9 => '50 Gold',
            10 => '100 Gold',
            11 => 'Try again'
        ];
        return $this->response(200, [$arr_gold]);
    }

    public function postLuckySpin(Request $req){
        $user = Auth::user();
        $arr_gold = [
            10 => 0,
            500 => 1,
            20 => 2,
            400 => 3,
            -1 => 4,
            30 => 5,
            300 => 6,
            40 => 7,
            200 => 8,
            50 => 9,
            100 => 10,
            0 => 11
        ];
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();
        
        if($checkSpam == null){
            return $this->response(422, [], 'Misconduct!', [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
        $mission_id = 10;
        $check_mission = ListMission::where(['user_id' => $user->User_ID, 'mission_id' => $mission_id])->where('status', '<>', 0)->first();
        if($check_mission && $check_mission->status == -1){
            return $this->response(200, [], __('app.the_task_is_performed_only_once_per_day'), [], false);
        }
        if(!$check_mission){
            ListMission::insertMission($mission_id, $user->User_ID);
        }
        $gold = ListMission::playLuckySpin();
        if($gold == -1){
            ListMission::where(['user_id' => $user->User_ID, 'mission_id' => $mission_id])->where('status', '<>', 0)->update(['mission_progress' => 1, 'get_reward' => 1]);
            return $this->response(200, ['gold'=>$arr_gold[$gold]]);
        }
        if($gold == 0){
            return $this->response(200, ['gold'=>$arr_gold[$gold]]);
        }
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => $gold,
            'Money_USDTFee' => 0,
            'Money_Time' => time(),
            'Money_Comment' => 'Play game lucky spin',
            'Money_MoneyAction' => 39,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => 9,
            'Money_CurrentAmount' => $gold,
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_Confirm_Time' => null,
            'Money_FromAPI' => 1,
        );
        $update_balance = Money::insert($arrayInsert);
        if($update_balance){
            ListMission::updateMission($user->User_ID,$mission_id, -1);
            ListMission::where('mission_id', $mission_id)->where('user_id', $user->User_ID)->where('status', '<>', 0)->update(['get_reward' => 1, 'mission_progress' => 1]);
            LogMission::addLogMission($user->User_ID, 'Mission Success', 'Play game lucky spin');
            return $this->response(200, ['gold'=>$arr_gold[$gold]]);
        }
        return $this->response(200, [], __('app.game_failed_please_contact_admin'), [], false);
    }

    public function getCheckLuckySpin(){
        $user = Auth::user();
        $mission_id = 10;
        $check_mission = ListMission::where(['user_id' => $user->User_ID, 'mission_id' => $mission_id])->where('status', '<>', 0)->first();
        if($check_mission && $check_mission->status == -1){
            return $this->response(200, ['status'=>false]);
        }
        if(!$check_mission){
            ListMission::insertMission($mission_id, $user->User_ID);
        }
        $this->listMission();
        return $this->response(200, ['status'=>true]);
    }

    public function listMission(){
        $user = Auth::user();
        //check mission old
        $daily_check_old = ListMission::listMissionType(2);
        if($daily_check_old->count() > 0){
            foreach($daily_check_old as $dlo){
                $check_mission_old = ListMission::checkDailyMission($dlo->id);
                if($check_mission_old){
                    ListMission::updateMission($user->User_ID, $dlo->mission_id, 0);
                }
            }
        }
        
        //check mission exists
        $mission = Mission::where('status', 1)->get();
        foreach($mission as $ms){
            $check_mission_exits = ListMission::checkMissionExits($user->User_ID, $ms->id);
            if(!$check_mission_exits){
                ListMission::insertMission($ms->id, $user->User_ID);
            }
        }

        //check mission double
        $list_mission_check_double = ListMission::listMissionType();
        $arr_check = [];
        foreach($list_mission_check_double as $lmcd){
            if (in_array( $lmcd->mission_id, $arr_check)){
                ListMission::where('id', $lmcd->id)->delete();
            }
            $arr_check[] = $lmcd->mission_id;
        }
        //check mission login
        ListMission::updateMission($user->User_ID, 14, -1);
        ListMission::where(['user_id' => $user->User_ID, 'mission_id' => 14])->where('status', '<>', 0)->update(['mission_progress' => 1]);

        //get balance game
        // ListMission::setBalanceGame($user->User_ID);
        // if($user->time_update_game_balance < date('Y-m-d')){
        //     ListMission::setBalanceGameDay($user->User_ID);
        // }
        //check balance game mission play game
        $balance = app('App\Http\Controllers\API\AgGameController')->balanceGame();
        $amountBalance = $balance['balance']; 
        // $amountBalance = 0; 
        // - (-100) - (50) + 50 
        $deposit_game = Money::where('Money_User', $user->User_ID)->where('Money_MoneyAction', 31)->where('Money_Currency', 9)->where('Money_MoneyStatus', 1)->where('Money_Time', '>=', strtotime('today'))->sum('Money_USDT');
        $withdraw_game = Money::where('Money_User', $user->User_ID)->where('Money_MoneyAction', 32)->where('Money_Currency', 9)->where('Money_MoneyStatus', 1)->where('Money_Time', '>=', strtotime('today'))->sum('Money_USDT');
        $balance_check = (-$deposit_game - $withdraw_game)/100 + $user->user_balance_game_day;
        // if($user->User_ID == 446580){
        //     return [$balance_check, intval($amountBalance)*100];
        // }
        if(($amountBalance) != $balance_check){
            $check_mission_finish = ListMission::checkMissionFinish($user->User_ID, 11);
            if(!$check_mission_finish){
                $update_mission = ListMission::updateMission($user->User_ID, 11, -1);
                ListMission::where(['user_id' => $user->User_ID, 'mission_id' => 11])->where('status', '<>', 0)->update(['mission_progress' => 1]);
                if($update_mission){
                    LogMission::addLogMission($user->User_ID, 'Mission Success', 'Play game shooting fish');
                }
            }
        }
        //check mission invite member
        $this->checkInviteMember($user->User_ID);

        //check mission KYC
        $mission_KYC = ListMission::checkMissionKYC($user->User_ID);
        if($mission_KYC){
            $update_mission = ListMission::updateMission($user->User_ID, 1, -1);
            ListMission::where(['user_id' => $user->User_ID, 'mission_id' => 1])->where('status', '<>', 0)->update(['mission_progress' => 1]);
            if($update_mission){
                LogMission::addLogMission($user->User_ID, 'Mission Success', 'Mission KYC success');
            }
        }
        //check mission level fish
        $this->checkMissionLevelFish();
        //check mission count fish
        $count_fish = $this->checkMissionCountFish();
        //check mission count eggs
        $count_eggs = $this->checkMissionCountEggs();

        $associated = ListMission::listMissionType(1);
        $daily = ListMission::listMissionType(2);
        
        $count_invite_member = ListMission::checkInviteMember($user->User_ID);

        // for ($i = 0; $i < count($associated); $i++) {
        //     $associated[$i]->mission = __($associated[$i]->mission);
        //     $associated[$i]->description = __($associated[$i]->description);
        // }

        // for ($i = 0; $i < count($daily); $i++) {
        //     $daily[$i]->mission = __($daily[$i]->mission);
        //     $daily[$i]->description = __($daily[$i]->description);
        // }

        // echo __('hello');

        return $this->response(200, ['associated'=>$associated,'daily'=>$daily,'count_invite_member'=>$count_invite_member, 'count_fish'=>$count_fish, 'count_eggs'=>$count_eggs]);
    }

    public function checkInviteMember($user_id){
        $count_member = ListMission::checkInviteMember($user_id);
        $arr_invite = [
            50 => 9,
            20 => 8,
            10 => 7,
            5 => 6,
            2 => 5   
        ];
        $list_mission = ListMission::getMission($user_id, $arr_invite);
        foreach($list_mission as $item){
            ListMission::where('id', $item->id)->update(['mission_progress'=>$count_member]);
        }
        foreach($arr_invite as $k => $v){
            if($count_member >= $k){
                $check_mission = ListMission::checkMissionFinish($user_id, $v);
                if(!$check_mission){
                    ListMission::updateMission($user_id, $v, -1);
                    LogMission::addLogMission($user_id, 'Mission Success', 'Invite '.$k.' member');
                }
            }
        }
    }

    public function postReward(Request $req){
        $user = $req->user();
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $req->CodeSpam)->first();

        if ($checkSpam == null) {
            
            return $this->response(200, [], 'Misconduct!', [], false);
        }else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
        }
        $mission_id = $req->id;
        $reward = ListMission::getReward($mission_id, $user->User_ID);
        if(!$reward){
            return $this->response(200, [], 'Reward fail!', [], false);
        }
        $arrayInsert = array(
            'Money_User' => $user->User_ID,
            'Money_USDT' => $reward,
            'Money_USDTFee' => 0,
            'Money_Time' => time(),
            'Money_Comment' => 'Reward gold mission',
            'Money_MoneyAction' => 35,
            'Money_MoneyStatus' => 1,
            'Money_Address' => null,
            'Money_Currency' => 9,
            'Money_CurrentAmount' => $reward,
            'Money_Rate' => 1,
            'Money_Confirm' => 0,
            'Money_Confirm_Time' => null,
            'Money_FromAPI' => 1,
        );
        $update_balance = Money::insert($arrayInsert);
        if($update_balance){
            $balance_gold = User::getBalance($user->User_ID, 9);
            ListMission::updateReward($user->User_ID,$mission_id);
            LogMission::addLogMission($user->User_ID, 'Mission Success', 'Reward gold mission');
            return $this->response(200, ['reward'=>$reward, 'balance_gold'=>$balance_gold], __('app.reward_gold_successful'));
        }
        return $this->response(200, [], 'Fail!', [], false);
    }

    public function postAddMission(Request $req){
        $user = Auth::user();
        if($user->User_Level != 1){
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
        $validator = Validator::make($req->all(), [
            'mission' => 'required',
            'description' => 'required',
            'mission_url' => 'required',
            'gold' => 'required',
            'mission_type' => 'required'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $add_mission = Mission::addMission($req->mission, $req->description, $req->mission_url, $req->gold, $req->mission_type);
        if($add_mission){
            return $this->response(200, [], __('app.add_mission_successful'));
        }
        return $this->response(200, [], __('app.add_mission_failed'), [], false);
    }
    
    public function postEditMission(Request $req){
        $user = Auth::user();
        if($user->User_Level != 1){
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
        $validator = Validator::make($req->all(), [
            'id' => 'required',
            'mission' => 'required',
            'description' => 'required',
            'mission_url' => 'required',
            'gold' => 'required',
            'mission_type' => 'required'
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $edit_mission = Mission::editMission($req->id, $req->mission, $req->description, $req->mission_url, $req->gold, $req->mission_type);
        if($edit_mission){
            return $this->response(200, [], __('app.edit_mission_successful'));
        }
        return $this->response(200, [], __('app.edit_mission_failed'), [], false);
    }

    public function postCheckMissionViewYoutube(){
        $user = Auth::user();
        $check_mission = ListMission::checkMissionFinish($user->User_ID, 3);
        if($check_mission){
            return $this->response(200, [], __('app.the_task_can_only_be_performed_once'), [], false);
        }
        ListMission::updateMission($user->User_ID, 3, -1);
        return $this->response(200, [], __('app.mission_successful'));
    }

    public function checkMissionLevelFish(){
        $user = Auth::user();
        $list_fish = Fishs::getFish($user->User_ID);
        $arr_level = [
            2 => 18, 
            3 => 19, 
            4 => 20
        ];
        if(!$list_fish){
            return false;
        }
        $level_max = 0;
        foreach($list_fish as $lf){
            $level = Fishs::checkLevelFish($lf);
            if($level > $level_max){
                $level_max = $level;
            }
            foreach($arr_level as $k => $al){
                if($level >= $k){
                    $check_mission = ListMission::checkMissionFinish($user->User_ID, $al);
                    if(!$check_mission){
                        ListMission::updateMission($user->User_ID, $al, -1);
                    }
                }
            }
        }
        $list_mission = ListMission::getMission($user->User_ID, $arr_level);
        foreach($list_mission as $item){
            ListMission::where('id', $item->id)->update(['mission_progress'=>$level_max]);
        }
        return $level_max;
    }

    public function checkMissionCountFish(){
        $user = Auth::user();
        $count_fish = Fishs::countFish($user->User_ID);
        $arr_count_fish = [
            2 => 21,
            3 => 22,
            5 => 23,
            7 => 24,
            15 => 25
        ];
        $list_mission = ListMission::getMission($user->User_ID, $arr_count_fish);
        foreach($list_mission as $item){
            ListMission::where('id', $item->id)->update(['mission_progress'=>$count_fish]);
        }
        foreach($arr_count_fish as $k => $cf){
            if($count_fish >= $k){
                $check_mission = ListMission::checkMissionFinish($user->User_ID, $cf);
                if(!$check_mission){
                    ListMission::updateMission($user->User_ID, $cf, -1);
                }
            }
        }
        return $count_fish;
    }
    public function checkMissionCountEggs(){
        $user = Auth::user();
        $arr_count_eggs = [
            2 => 26,
            4 => 27
        ];
        $list_mission = ListMission::getMission($user->User_ID, $arr_count_eggs);
        foreach($list_mission as $item){
            ListMission::where('id', $item->id)->update(['mission_progress'=>$user->User_TotalEggs]);
        }
        foreach($arr_count_eggs as $k => $ce){
            if($user->User_TotalEggs >= $k){
                $check_mission = ListMission::checkMissionFinish($user->User_ID, $ce);
                if(!$check_mission){
                    ListMission::updateMission($user->User_ID, $ce, -1);
                }
            }
        }
        return $user->User_TotalEggs;
    }
}
