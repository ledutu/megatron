<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\SubAccount;
use App\Model\User;
use App\Model\Money;
use App\Model\GameBet;
use Session;
use Illuminate\Support\Facades\URL;
use Redirect;


class CheckWaitingGame
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Session('user')){
          	$checkWaiting = GameBet::where('GameBet_SubAccountUser', (int)Session('user')->User_ID)->where('GameBet_Status', 0)->first();
          	if($checkWaiting){
              	redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Please wait for the results!']);
            }
            return $next($request);
        }
        return redirect()->route('getLogin',['redirect'=>encrypt(\Request::fullUrl())])->with(['flash_level'=>'error', 'flash_message'=>'Please Login!']);
    }
}
