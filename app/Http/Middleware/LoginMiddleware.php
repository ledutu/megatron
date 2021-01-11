<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\SubAccount;
use App\Model\User;
use App\Model\Money;
use Session;
use Illuminate\Support\Facades\URL;
use Redirect;


class LoginMiddleware
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
        if(session('user')){
            // dd(3123);
          	$user = User::find(Session('user')->User_ID);
          	if($user->User_Block == 1 && !Session('userTemp')){
                return redirect()->route('getLogout');
            }
          	if($user->User_Level != 1){
                //return redirect()->route('getLogout')->with(['flash_level'=>'error', 'flash_message'=>'The system updating!']);
            }
          	if(Session::getId() != $user->user_SessionID  && !Session('userTemp')){
                return redirect()->route('getLogout');
            }
          	$checkSpam = Money::checkSpamAction(Session('user')->User_ID);
            return $next($request);
        }
        return redirect()->route('getLogin',['redirect'=>encrypt(\Request::fullUrl())])->with(['flash_level'=>'error', 'flash_message'=>'Please Login!']);
    }
}
