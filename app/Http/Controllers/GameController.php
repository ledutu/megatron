<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session; 
use Illuminate\Support\Facades\Auth;
use DB;
use Excel;

use App\Model\User;
use App\Model\subAccount;
use App\Model\GameBet;
use App\Model\GameCoin;
use App\Model\Chartdata;
use Illuminate\Support\Facades\Validator;
use App\Exports\TradeExport;

class GameController extends Controller{
	
	
	public function getGame(Request $req){
      	if(Session('user')->User_ID != 657744){
         	return redirect()->route('getDashboard');
        }
      	//$subAdmin = 'DIVKH999999';
      	//$info = subAccount::where('subAccount_ID', $subAdmin)->first();
      	$subID = Session('user')->User_ID;
      	$user = User::where('User_ID', Session('user')->User_ID)->first();
      	$token = $user->user_SessionID;

      	if(!$req->symbol){
        	return redirect()->route('admin.getTrade', ['symbol'=>'BTCUSDT']);
        }
      	$symbol = $req->symbol;
		$total = array(
			'BTCUSDT' => array('buy'=>0,'sell'=>0),
			'ETHUSDT' => array('buy'=>0,'sell'=>0),
			'TLCUSDT' => array('buy'=>0,'sell'=>0),
			'EOSUSDT' => array('buy'=>0,'sell'=>0),
			'BNBUSDT' => array('buy'=>0,'sell'=>0),
			'BCHUSDT' => array('buy'=>0,'sell'=>0)
		);
			
		$bet = GameBet::where('GameBet_Status', 0)->where('GameBet_SubAccountLevel',0)->where('GameBet_Currency',5)->orderBy('created_at', 'ASC')->get();


      	$quy = DB::table('GameFund')->where('GameFund_symbol','BTCUSDT')->get();
      
      	$history_deposit = DB::table('log_admin')->orderBy('created_at', 'desc')->get();
      
		foreach($bet as $v){
			if($v->GameBet_Type == 'buy'){
				$total[$v->GameBet_Symbol]['buy'] += $v->GameBet_Amount*1;
			}elseif($v->GameBet_Type == 'sell'){
				$total[$v->GameBet_Symbol]['sell'] += $v->GameBet_Amount*1;
			}
		}
      
    
		return view('system.admin.Trade', compact('bet', 'total', 'symbol', 'info','subID', 'token','quy','history_deposit'));
	}
	
	public function matdaycho(Request $req){
		

		$explode = explode(',', $req->sub);


		foreach($explode as $v){
			if($v){
				// kiểm tra user này có tồn tại hay ko
				$sub = User::where('User_ID', $v)->first();

				if($sub){
					$time = strtotime(date('Y-m-d H:i'));

					
					
					$insertArray = array(
						'matday_Sub' => $sub->User_ID,
						'matday_error' => (int)$req->error,
						'matday_Status' => 0,
						'matday_Datetime' => time()
					);

					DB::table('matday')->insert($insertArray);
				}
			}
		}
		return response()->json(['status'=>true]);
	}
  
	public function getResult(Request $req){
      	
		$fund = DB::table('GameFund')->where('GameFund_symbol', 'BTCUSDT')->first();
		$symbol = array(
			'BCHUSDT' => array(
				'put' => 0,
				'call' => 0
			),
			'BNBUSDT' => array(
				'put' => 0,
				'call' => 0
			),
			'EOSUSDT' => array(
				'put' => 0,
				'call' => 0
			),
			'DASHUSDT' => array(
				'put' => 0,
				'call' => 0
			),
			'LTCUSDT' => array(
				'put' => 0,
				'call' => 0
			),
			'ETHUSDT' => array(
				'put' => 0,
				'call' => 0
			),
			'BTCUSDT' => array(
				'put' => 0,
				'call' => 0
			)
		);
		
		$betList = GameBet::where('GameBet_Status', 0)
          					->where('GameBet_Currency', 5)
							->whereIn('GameBet_SubAccountLevel', [0])
							->get();
		
		$total = 0;
		foreach($betList as $v){
          	$amounttemp = $v->GameBet_Amount;
			$total += $amounttemp;
			if($v->GameBet_Type == 'buy'){

				$symbol[$v->GameBet_Symbol]['call'] += $amounttemp;
			}else{

				$symbol[$v->GameBet_Symbol]['put'] += $amounttemp;
			}
			
		}

		$A = 0;
		foreach($symbol as $v){
			$A += abs($v['call'] - $v['put']);
		}
		

		$B = $fund->GameFund_fundReal-100;

		$rand = rand(0,99);
		
		if($A < $B){
			// kết qủa tự nhiên
			
			return response()->json(['status' => true, 'Fund'=>$fund->GameFund_fundReal, 'A'=>$A, 'B'=>$B]);
		}else{

			// lấy những cặp tiền đang đánh
			$sql = GameBet::select('GameBet_Symbol')->where('GameBet_Status', 0)->groupBy('GameBet_Symbol')->get();

			
			foreach($sql as $v){

				$game = Chartdata::where('symbol', $v->GameBet_Symbol)->where('status', 0)->first();
				if(!$game){
					if($symbol[$v->GameBet_Symbol]['call'] < $symbol[$v->GameBet_Symbol]['put']){
						// call thắng
						//echo '<pre>symbol '.$v->GameBet_Symbol.' call Win </pre>';
						if($rand<=50){
							$closeFinal = self::setResult('AminQ', $v->GameBet_Symbol, 1);	
						}else{
							$closeFinal = self::setResult('AminQ', $v->GameBet_Symbol, 1);	
						}
						
					}else{
						// put thắng
						//echo '<pre>symbol '.$v->GameBet_Symbol.' put Win </pre>';
						if($rand<=50){
							$closeFinal = self::setResult('AminQ', $v->GameBet_Symbol, 0);	
						}else{
							$closeFinal = self::setResult('AminQ', $v->GameBet_Symbol, 0);
						}
						
					}
				}
				
				 
			}
			return response()->json(['status' => true, 'Fund'=>$fund->GameFund_fundReal, 'A'=>$A, 'B'=>$B ]);
			
		}
		
		
	}
	
	public function setResultByAdmin(Request $req){
		//dd(file_get_contents('http://localhost:8888/exchangefibo/public/getresult?symbol=BTCUSDT&game=21300'));
		$user = DB::table('users')->where('user_ID', $req->user)->first();
		/*if($user->User_Level != 5){
			return response()->json(['status' => false]);
		}*/
		$game = Chartdata::where('symbol', $req->symbol)->where('status', 0)->first();
		
		
		
		if(!$game){
			if($req->win == 1){
				
				$closeFinal = self::setResult($req->user, $req->symbol, 1);
			}else{
				$closeFinal = self::setResult($req->user, $req->symbol, 0);
			}
			return response()->json(['status' => true,'point' => $closeFinal]);
		}
		return response()->json(['status' => false]);
	}
	
	public function setResult($user, $symbol, $win){
		
		// lấy lịch sử cuối cũng của nến
		$result = GameCoin::where('order', 0)->orderBy('time', 'DESC')->first();


		$close = $result->data[$symbol]['close'];


		$closePecent = self::random_float(10,20);
 
		$point = ($close * $closePecent)/1000;
		if($win == 1){
			if($symbol == 'BTCUSDT'){
				$closeFinal = $close+(rand(20, 40)/10);
			}else{
				$closeFinal = $close+$point;
			}
			$arrayInsert = array(
				'close' => $closeFinal,
				'open' => $result->data[$symbol]['close'],
				'symbol' => $symbol,
				'status' => 0,
              	'time'=>time(),
			);
			if(Chartdata::addLog($arrayInsert)){
				return $closeFinal;
			}
		}elseif($win == 0){
			if($symbol == 'BTCUSDT'){
				$closeFinal = $close-(rand(20, 40)/10);
			}else{
				$closeFinal = $close-$point;
			}
			$arrayInsert = array(
				'close' => $closeFinal,
				'open' => $result->data[$symbol]['close'],
				'symbol' => $symbol,
				'status' => 0,
              	'time'=>time(),
			);
			if(Chartdata::addLog($arrayInsert)){
				return $closeFinal;
			}
			
		}else{
			$closeFinal = $close;
			$arrayInsert = array(
				'close' => $closeFinal,
				'open' => $result->data[$symbol]['close'],
				'symbol' => $symbol,
				'status' => 0,
              	'time'=>time(),
			);
          	
			if(Chartdata::insert($arrayInsert)){
				return $closeFinal;
			}
			
		}
	
		
		
	}
	
	public function getPayment(Request $req){
      	$bet = GameBet::where('GameBet_Status', 0)->orderBy('GameBet_datetime', 'DESC')->get();

        foreach($bet as $v){

			$match = GameCoin::where('GameCoin_MathID', $v->GameBet_MathID)->first();
          	if($match){

            	$status = self::Payment($match->GameCoin_Data, $v);	  

                if($status){
                  	dd($status);
					GameBet::where('GameBet_Status', 0)->where('_id', new \MongoDB\BSON\ObjectID($v->_id))->update(['GameBet_Status'=>$status['status'], 'GameBet_AmountWin'=>$status['amount']]);
                }
              	
              	

            }
          	
        }	
      	return 1;
      
      	
   
    }
  
  	public function Payment($match, $bet){
      	$dataMatch = $match[$bet->GameBet_Symbol];
      	if($dataMatch['open']<$dataMatch['close']){
        	// ra xanh.
          	if($bet->GameBet_Type ==  'buy'){
            	// win
              	return array('status'=> 1, 'amount'=>$bet->GameBet_Amount*0.95);
            }else{
              	// lose
              	return array('status'=> 2, 'amount'=>-$bet->GameBet_Amount);
              	
            }
      	}else if($dataMatch['open']>$dataMatch['close']){
        	// ra đỏ.
          	if($bet->GameBet_Type ==  'sell'){
            	// win
              	return array('status'=> 1, 'amount'=>$bet->GameBet_Amount*0.95);
            }else{
              	// lose
              	return array('status'=> 2, 'amount'=>-$bet->GameBet_Amount);
            }
      	}else{
        	// ra hoà.
          	return array('status'=> 3, 'amount'=>0);
      	}
     	
      
    }
	
	function getCLosePoint($point){
		$closeFinal = $point + ($point * self::random_float(0,10))/100;
		return round($closeFinal, 2);
	}
	
	function random_float($min,$max) {
		$randomFloat = rand($min, $max) / 100;
		return $randomFloat+0.01;

	}
  
  
	public function depositGameFund(Request $req){
      
      $user = Session('user');
      
      $validator = Validator::make($req->all(), [
        'amount' => 'required|Numeric|min:0',
        'symbol' => 'required'
      ]);
      
      if ($validator->fails()) {
        foreach ($validator->errors()->all() as $value) {
          return $this->redirectBack($value, [], 'error');
        }
      }

     
      if($user->User_ID != 657744){
         return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Permission Denied!']);
      }
      
      $FundBalance  = DB::table('GameFund')->where('GameFund_symbol',$req->symbol)->first();
      
      if(!$FundBalance){
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Contacts Admin!']);
      }
      
      $realBalance = $FundBalance->GameFund_fundReal + $req->amount;
   
      
     $updateBalacne = DB::table('GameFund')->where('GameFund_symbol',$req->symbol)->update(['GameFund_fundReal' => $realBalance]);
      
      $insertArray =  array(
        'user'=>$user->User_ID,
        'action'=>'Deposit GameFund',
        'comment'=>'User '.$user->User_ID.' Deposit '.$req->amount.' To Real Game Fund',
        'created_at'=>date('Y-m-d H:i:s'),  
      );
      
      DB::table('log_admin')->insert($insertArray);
      
      return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Deposit Success!'.$req->amount.' $']);
     
    }
  	public function withdrawGameFund(Request $req){
        $user = Session('user');

        $validator = Validator::make($req->all(), [
          'amount' => 'required|Numeric|min:0',
          'symbol' => 'required'
        ]);

        if ($validator->fails()) {
          foreach ($validator->errors()->all() as $value) {
            return $this->redirectBack($value, [], 'error');
          }
        }


        if($user->User_ID != 657744){
           return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Permission Denied!']);
        }

        $FundBalance  = DB::table('GameFund')->where('GameFund_symbol',$req->symbol)->first();

        if(!$FundBalance){
          return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Contacts Admin!']);
        }

      	$realBalance = $FundBalance->GameFund_fundReal - $req->amount;
      
      	if($realBalance <= 0 ){
            return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Game Fund Real Equal To 0 !']);
        }
      
       $updateBalacne = DB::table('GameFund')->where('GameFund_symbol',$req->symbol)->update(['GameFund_fundReal' => $realBalance]);

        $insertArray =  array(
          'user'=>$user->User_ID,
          'action'=>'Withdraw GameFund',
          'comment'=>'User '.$user->User_ID.' withdraw '.$req->amount.' from Real Game Fund',
          'created_at'=>date('Y-m-d H:i:s'),  
        );

        DB::table('log_admin')->insert($insertArray);

        return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Withdraw Success!'.$req->amount.' $']);
      
    }
  	
    public function getHistoryTradeAdmin(Request $req){
      
      	
      	$betList = GameBet::where('GameBet_Currency',5)->where('GameBet_SubAccountLevel', 0);
      
        if ($req->UserID) {
          $betList = $betList->where('GameBet_SubAccountUser', (int) $req->UserID);
        }
      
      	if($req->status){
          //lose
            if($req->status < 0){
				$betList = $betList->where('GameBet_AmountWin','<',0);
            }
          //win
            if($req->status > 0 ){
				$betList = $betList->where('GameBet_AmountWin','>',0);
            }
       
        }
      
      	if($req->typegame){
			$betList = $betList->where('GameBet_Type', $req->typegame);
        }

        if ($req->datefrom and $req->dateto) {
          $betList = $betList->where('GameBet_datetime', '>=', strtotime($req->datefrom))
            ->where('GameBet_datetime', '<', strtotime($req->dateto) + 86400);
        }
        if ($req->datefrom and !$req->dateto) {
          $betList = $betList->where('GameBet_datetime', '>=', strtotime($req->datefrom));
        }
        if (!$req->datefrom and $req->dateto) {
          $betList = $betList->where('GameBet_datetime', '<', strtotime($req->dateto) + 86400);
        }
		
        if ($req->export) {
         	
   			ob_end_clean();
            ob_start();
            return Excel::download(new TradeExport($betList->orderBy('GameBet_datetime', 'desc')->get()), 'TradeHistory.xlsx');
          
   
        }
      	
     	$betList = $betList->orderBy('GameBet_datetime', 'desc')->paginate(50);
      
	 	return view('system.admin.HistoryTrade',compact('betList'));
      
    }
}
