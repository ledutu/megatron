<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Money;
use App\Model\Investment;
use App\Model\User;
use GuzzleHttp\Client;
use Session;
use DB;

class AjaxController extends Controller
{
	public function getAjaxUser(Request $req){
        $user = User::where('User_ID', $req->user)->orWhere('User_Email', $req->user)->first();
        if(!$user){
            return response()->json(['class'=>'danger', 'message'=>'User Is Not Found!']);
        }
        if(filter_var($req->user, FILTER_VALIDATE_EMAIL)){
            return response()->json(['class'=>'success', 'message'=>'Transfer to user ID: '.$user->User_ID.'!']);
        }else{
            return response()->json(['class'=>'success', 'message'=>'Transfer to user Email: '.$user->User_Email.'!']);
        }
    }
    public function getBalance(){
        $staistical = DB::table('statistical')->where('statistical_User', Session('user')->User_ID)->whereRaw('statistical_Time >= "'.date('Y-m-d 00:00:00', strtotime('monday this week')).'"')->first();
        $totalBet = 0;
        if($staistical){
	       $totalBet = $staistical->statistical_TotalWin + $staistical->statistical_TotalLost;
        }
        $balance = User::getBalance(Session('user')->User_ID);
        
        $user_list = User::select('User_ID', 'statistical_TotalWin', 'statistical_TotalLost', DB::raw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count(Session('user')->User_Tree, ',') . " AS f, User_Agency_Level, User_Tree"))
                        ->join('statistical', 'statistical_User', 'User_ID')
                        ->whereRaw('User_Tree LIKE "'.Session('user')->User_Tree.'%"')
						->where('User_ID','<>',Session('user')->User_ID)
						->whereRaw('statistical_Time >= "'.date('Y-m-d 00:00:00', strtotime('monday this week')).'"')
						->whereRaw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count(Session('user')->User_Tree, ',') . " <= 3")
						->orderBy('User_RegisteredDatetime','DESC')
                        ->get();
        $comf123 = 0;
     
        $balancelivecasino = Session('user')->User_BalanceLiveCasino;
       
        foreach($user_list as $v){
	        if($v->f == 1){
		        $comf123 += ($v->statistical_TotalWin + $v->statistical_TotalLost)*0.005;
	        }else if($v->f == 2){
		        $comf123 += ($v->statistical_TotalWin + $v->statistical_TotalLost)*0.003;
	        }else if($v->f == 3){
		        $comf123 += ($v->statistical_TotalWin + $v->statistical_TotalLost)*0.001;
	        }
        }

        return response()->json(array('status' => 'OK', 'USDT'=>$balance[5]*1, 'COMF'=>$comf123*1)); 


    }
    
}
