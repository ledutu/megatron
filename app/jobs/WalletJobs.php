<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Model\User;
use App\Model\Money;
use App\Model\userBalance;
use DB;


class WalletJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
 
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $id;
    public $userID;

    public function __construct($id, $userID){
        $this->id = $id;
        $this->userID = $userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    $id = $this->id;
        $userID = $this->userID;
        $money = Money::where('Money_ID', $id)->where('Money_MoneyStatus', 0)->where('Money_MoneyAction', 2)->where('Money_User', $userID)->first();
        if($money){
	        $balance = User::getBalance($userID, 3);
	        if(abs($money->Money_USDT)+abs($money->Money_USDTFee) > $balance){
		        $update = Money::where('Money_ID', $id)->where('Money_MoneyStatus', 0)->where('Money_MoneyAction', 2)->where('Money_User', $userID)->update(['Money_MoneyStatus'=>2]);
	        }else{
		        $item = userBalance::firstOrNew(array('user' => $userID));
				$item->balance -= (float)(abs($money->Money_USDT));
				$item->update_at = date('Y-m-d H:i:s');
				if($item->save()){
					$update = Money::where('Money_ID', $id)->where('Money_MoneyStatus', 0)->where('Money_MoneyAction', 2)->where('Money_User', $userID)->update(['Money_MoneyStatus'=>1]);
				}
	        }
        }
    }
}
