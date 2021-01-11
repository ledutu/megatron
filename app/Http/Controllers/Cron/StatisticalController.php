<?php
namespace App\Http\Controllers\Cron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Model\Money;
use App\Model\User;
use App\Model\Investment;
use App\Model\Wallet;
use App\Model\GameBet;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Value\Money as CB_Money;
use IEXBase\TronAPI\Tron;
use Carbon\Carbon;

use DB;
// Queue
use App\Jobs\SendMailJobs;
use App\Jobs\SendTelegramJobs;
use App\Jobs\PayInterestJobs;
use App\Jobs\PayInterestLotJobs;
use App\Jobs\PaySalesSystemJobs;

class StatisticalController extends Controller{
	
	public function getStatisticalLastWeek(){
		//dd('stop');
		$dateFrom = date('Y-m-d 00:00:00', strtotime('monday last week'));
		$dateTo = date('Y-m-d 00:00:00', strtotime('monday this week'));
		//$dateFrom = '2020-09-21 00:00:00';
		//$dateTo = '2020-09-28 00:00:00';
		$from = strtotime(date($dateFrom));
		$to = strtotime(date($dateTo));
		$getStatistical = DB::table('statistical')->where('statistical_Time', '>=', $dateFrom)->where('statistical_Time', '<', $dateTo)->delete();
		//dd($getStatistical);
		//dd($from, $to);
		$dateInsert = date('Y-m-d 00:00:00', strtotime('saturday last week'));
		//dd($dateFrom, $dateTo, $dateInsert);
		$bet = GameBet::where('GameBet_datetime', '>=', $from)->where('GameBet_datetime', '<', $to)->where('GameBet_Currency', 5)->get();
		//dd($bet);
		$insertArray = array();
		foreach($bet as $v){
			$win = 0;
			$loss = 0;
			$draw = 0;
			$CountWin = 0;
			$CountLost = 0;
			$CountWin = 0;
			$CountDraw = 0;
			$TotalBet = 0;
			
			if($v->GameBet_Status == 1){
				$win = abs($v->GameBet_Amount);
				$TotalBet = $v->GameBet_Amount;
				$CountWin = 1;
			}elseif($v->GameBet_Status == 2){
				$loss = abs($v->GameBet_Amount);
				$TotalBet = $v->GameBet_Amount;
				$CountLost = 1;
			}else{
				$draw = abs($v->GameBet_Amount);
				$CountDraw = 1;
			}
			
		
			if(isset($insertArray[$v->GameBet_SubAccountUser])){
				$insertArray[$v->GameBet_SubAccountUser] = array(
					'win'=>$insertArray[$v->GameBet_SubAccountUser]['win']+$win,
					'loss'=>$insertArray[$v->GameBet_SubAccountUser]['loss']+$loss,
					'draw'=>$insertArray[$v->GameBet_SubAccountUser]['loss']+$draw,
					'countdraw'=>$insertArray[$v->GameBet_SubAccountUser]['countdraw']+$CountDraw,
					'countwin'=>$insertArray[$v->GameBet_SubAccountUser]['countwin']+$CountWin,
					'countlost'=>$insertArray[$v->GameBet_SubAccountUser]['countlost']+$CountLost,
					'TotalBet'=>$insertArray[$v->GameBet_SubAccountUser]['TotalBet']+$TotalBet
				);
			}else{
				$insertArray[$v->GameBet_SubAccountUser] = array(
					'win'=>$win,
					'loss'=>$loss,
					'draw'=>$draw,
					'countdraw'=>$CountDraw,
					'countwin'=>$CountWin,
					'countlost'=>$CountLost,
					'TotalBet'=>$TotalBet
				);
			}

		}
		$aaa = array();
			foreach($insertArray as $k=>$v){
			$aaa[] = array(
							'statistical_User'=>$k,
							'statistical_TotalBet'=>$v['TotalBet'],
							'statistical_win'=>$v['countwin'],
							'statistical_Loss'=>$v['countlost'],
							'statistical_refurn'=>$v['countdraw'],
							'statistical_TotalWin'=>$v['win'],
							'statistical_TotalLost'=>$v['loss'],
							'statistical_Time'=>$dateInsert,
							'statistical_UpdateTime'=>$dateInsert,
						);
		}
		DB::table('statistical')->insert($aaa);
		dd('run statistical done');
  }
  
  public static function getStatistical(){
	  if(date('N') == 1 && date('H')<2){
		  $form = strtotime('monday last week');
		  $to = strtotime('monday this week');

		  $formStatistical = date('Y-m-d', strtotime('monday last week'));
		  $toStatistical = date('Y-m-d', strtotime('monday this week'));
	  }else{
		  $form = strtotime('monday this week');
		  $to = strtotime('monday next week');

		  $formStatistical = date('Y-m-d', strtotime('monday this week'));
		  $toStatistical = date('Y-m-d', strtotime('monday next week'));
	  }
	  
	  $game = GameBet::where('GameBet_Statistical', '!=', 1)
					  ->whereIn('GameBet_Status',[1,2,3])
        			  ->where('GameBet_Currency', 5)
					  ->where('GameBet_datetime', '>=',$form)
					  ->where('GameBet_datetime','<', $to)
					  ->get();
	  $statistical = array();
	  foreach($game as $v){
		  $k = $v->GameBet_SubAccountUser;
		  $win = 0;
		  $loss = 0;
		  $total = 0;
		  $countWin = 0;
		  $countLoss = 0;
		  $countDraw = 0;
		  if($v->GameBet_Status == 1){
			  $win = $v->GameBet_Amount;
			  $total = $v->GameBet_Amount;
			  $countWin = 1;
		  }elseif($v->GameBet_Status == 2){
			  $loss = abs($v->GameBet_AmountWin);
			  $total = $v->GameBet_Amount;
			  $countLoss = 1;
		  }else{
			  $countDraw = 1;
		  }
		  if(isset($statistical[$k])){
			  $statistical[$k] = array(
				  'win' => $statistical[$k]['win']+$win,
				  'loss' => $statistical[$k]['loss']+$loss,
				  'total' => $statistical[$k]['total']+$total,
				  'countWin' => $statistical[$k]['countWin']+$countWin,
				  'countLoss' => $statistical[$k]['countLoss']+$countLoss,
				  'countDraw' => $statistical[$k]['countDraw']+$countDraw,
			  );
		  }else{
			  $statistical[$k] = array(
				  'win' => $win,
				  'loss' => $loss,
				  'total' => $total,
				  'countWin' => $countWin,
				  'countLoss' => $countLoss,
				  'countDraw' => $countDraw,
			  );
		  }
	  }
	  
	  
	  // cập nhật lại thống kê
	  foreach($statistical as $k=>$v){
		  
		 $aaa = DB::table('statistical')->where('statistical_User', $k)
									  ->whereDate('statistical_Time', '>=', $formStatistical)
									  ->whereDate('statistical_Time', '<', $toStatistical)
									  ->first();
		  if($aaa){
			  // update lệnh thắng thua tổng đánh
			  $update = array(
				  'statistical_TotalBet' => $v['total']+$aaa->statistical_TotalBet,
				  'statistical_Win' => $v['countWin']+$aaa->statistical_Win,
				  'statistical_Loss' => $v['countLoss']+$aaa->statistical_Loss,
				  'statistical_Refurn' => $v['countDraw']+$aaa->statistical_Refurn,
				  'statistical_TotalWin' => $v['win']+$aaa->statistical_TotalWin,
				  'statistical_TotalLost' => $v['loss']+$aaa->statistical_TotalLost,
				  'statistical_UpdateTime' => date('Y-m-d H:i:s'),
			  );
			  DB::table('statistical')->where('statistical_User', $k)
									  ->whereDate('statistical_Time', '>=', $formStatistical)
									  ->whereDate('statistical_Time', '<', $toStatistical)
									  ->update($update);
		  }else{
			  // thêm mới
			  $arrayInsert = array(
				  'statistical_User' => $k,
				  'statistical_TotalBet' => $v['total'],
				  'statistical_Win' => $v['countWin'],
				  'statistical_Loss' => $v['countLoss'],
				  'statistical_Refurn' => $v['countDraw'],
				  'statistical_TotalWin' => $v['win'],
				  'statistical_TotalLost' => $v['loss'],
				  'statistical_Time' => date('Y-m-d H:i:s'),
				  'statistical_UpdateTime' => date('Y-m-d H:i:s'),
			  );
			  DB::table('statistical')->insert($arrayInsert);
		  }
		  // update lại tất cả những lệnh đã thống kê trong mongoDB
		  GameBet::where('GameBet_Statistical', 0)
					  ->whereIn('GameBet_Status',[1,2,3])
        			  ->where('GameBet_Currency', 5)
					  ->where('GameBet_datetime', '>=',$form)
					  ->where('GameBet_datetime','<', $to)
					  ->where('GameBet_SubAccountUser', $k)->update(['GameBet_Statistical'=>1]);
	  }
	  dd('stop');
  }
}