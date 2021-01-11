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
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Account;

use Image;
use PragmaRX\Google2FA\Google2FA;

use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;
use App\Jobs\SendMailJobs;
use App\Model\User;
use App\Model\Money;
use App\Model\GoogleAuth;
use App\Model\Wallet;
use App\Model\Investment;
use App\Model\Eggs;
use App\Model\Markets;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Mail;

class HistoryController extends Controller
{
    public $fee_sell_egg_system = 0.05;
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function postHistoryWallet(Request $req){
        $user = $req->user();
        // var_dump($user);exit;
        $limit = 20;
        $money = DB::table('money')
                    ->leftjoin('moneyaction', 'Money_MoneyAction', 'MoneyAction_ID')
                    ->leftjoin('currency', 'Money_Currency', 'Currency_ID')
                    ->where('Money_MoneyStatus', 1)
                    ->where('Money_User', $user->User_ID)
                    ->orderByDesc('Money_ID');
        if($req->id){
            $money = $money->where('Money_ID', $req->id);
        }
        if($req->amount){
            $money = $money->where('Money_MoneyUSDT', 'LIKE', '%'.$req->amount.'%');
        }
        if($req->action){
            $action = $req->action;
            // var_dump($action);exit;
            $money = $money->whereRaw('Money_MoneyAction in ('.$action.')');
        }
        if($req->from){
            $from = strtotime($req->from);
            $money = $money->where('Money_Time', '>=', $from);
        }
        if($req->to){
            $to = strtotime($req->to);
            $money = $money->where('Money_Time', '<=', $to);
        }
        // $page = 1;
        // if($req->page){
        //     $page = $req->page;
        // }
		// Paginator::currentPageResolver(function () use ($page) {
	    //     return $page;
	    // });
        $money = $money->paginate($limit);
        
        $list = [];
        for($i = 0; $i < count($money); $i++){
            $status = '';
            if($money[$i]->Money_MoneyStatus == 1){
                $status = 'Active';
            }
            if($money[$i]->Money_MoneyStatus == 2){
                $status = 'Waiting';
            }
            if($money[$i]->Money_MoneyStatus == -1){
                $status = 'Cancel';
            }
            $list[$i] = [
                'id'=> $money[$i]->Money_ID,
                'Amount' => $money[$i]->Money_MoneyAction == 2 ? ($money[$i]->Money_USDT + $money[$i]->Money_USDTFee)*1 : $money[$i]->Money_USDT*1,
                'Fee'=> $money[$i]->Money_USDTFee*1,
                'Rate'=>$money[$i]->Money_Rate*1,
                'Currency'=> $money[$i]->Currency_Name,
                'Action'=> $money[$i]->MoneyAction_Name,
                'comment'=> $money[$i]->Money_Comment,
                'Time' => date('Y-m-d H:i:s',$money[$i]->Money_Time),
                'Status' => $status
            ];
        }
        // $list = $money->items();
        $data = ['history'=>$list, 'current_page'=>$money->currentPage(), 'total_page'=>$money->lastPage() ];
        return response(array('status'=>true, 'data'=>$data), 200);
    }

    public function getStatistical()
    {
        $where = '';
        // if (Input::get('from')) {
        //     $from = strtotime(date('Y-m-d', strtotime(Input::get('from'))));
        //     $where .= ' AND Money_Time >= ' . $from;
        // }
        // if (Input::get('to')) {
        //     $to = strtotime('+1 day', strtotime(date('Y-m-d', strtotime(Input::get('to')))));
        //     $where .= ' AND Money_Time < ' . $to;
        // }
        $Statistic = Money::getStatistic($where);

        // $Total = Money::StatisticTotal($where);
        // // dd($Statistic->get(),$Total->get());
        // if (Input::get('User_ID')) {
        //     $Statistic = $Statistic->where('Money_User', Input::get('User_ID'));
        // }

        // if (Input::get('User_Level') != null) {
        //     $Statistic = $Statistic->where('User_Level', Input::get('User_Level'));
        // }

        // $Statistic = $Statistic->paginate(15);
        // $Total = $Total->get()[0];
        // $static_total = User::totalBalance();
        // $eggs= Eggs::/*where('BuyFrom','<>','EggsBook.com')->where('BuyFrom','<>',NULL)->*/where('ID', 'E3863963864')->get();
        $markets  = Markets::where('Status', 1)->get();
        $Statistic = $Statistic->get();
        //số trứng mua + tổng tiền chi
        foreach($Statistic as $st){
            $count_eggs_buy = 0;
            $total_price_buy_eggs = 0;
            foreach($markets as $mak){
                if($mak['Sold'][0]['user'] == $st['Money_User']){
                    $count_eggs_buy++;
                    $total_price_buy_eggs = $total_price_buy_eggs + $mak['Price'];
                }
            }
            $st['count_eggs_buy'] = $count_eggs_buy; 
            $st['total_price_buy_eggs'] = $total_price_buy_eggs; 
        }
        //Số trứng bán + tổng tiền nhận
        foreach($Statistic as $st){
            $count_eggs_sell = 0;
            $total_price_sell_eggs = 0;
            foreach($markets as $mak){
                if($mak['UserSell'] == $st['Money_User']){
                    $count_eggs_sell++;
                    $total_price_sell_eggs = $total_price_sell_eggs + $mak['Price'];
                }
            }
            $st['count_eggs_sell'] = $count_eggs_sell; 
            $st['total_price_sell_eggs'] = $total_price_sell_eggs; 
        }
        //Số trứng bán cho hệ thống + tiền nhận (đã trừ phí) + Phí thu hồi trứng
        foreach($Statistic as $st){
            $count_eggs_sell_system = 0;
            $total_price_sell_eggs_system = 0;
            foreach($markets as $mak){
                if($mak['UserSell'] == $st['Money_User'] && $mak['Sold'][0]['user'] == 'eggsbook'){
                    $count_eggs_sell_system++;
                    $total_price_sell_eggs_system = $total_price_sell_eggs_system + $mak['Price'];
                }
            }
            $st['count_eggs_sell_system'] = $count_eggs_sell_system; 
            $st['total_price_sell_eggs_system'] = $total_price_sell_eggs_system*(1-$this->fee_sell_egg_system); 
            $st['total_fee_price_sell_eggs_system'] = $total_price_sell_eggs_system*$this->fee_sell_egg_system; 
        }
        //Số trứng nở + số trứng hư
        foreach($Statistic as $st){
            $count_open_eggs = Eggs::where('Owner', $st['Money_User'])->where('Status', 2)->count();
            $count_bad_eggs = Eggs::where('Owner', $st['Money_User'])->where('Status', -1)->count();
        
            $st['count_open_eggs'] = $count_open_eggs; 
            $st['count_bad_eggs'] = $count_bad_eggs; 
        }
      
        return $this->response(200, ['statistic'=>$Statistic, $markets]);
    }
}
