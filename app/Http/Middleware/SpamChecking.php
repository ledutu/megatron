<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class SpamChecking
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
        $user = session('user');
        $checkSpam = DB::table('string_token')->where('User', $user->User_ID)->where('Token', $request->CodeSpam)->first();
        
        if($checkSpam == null){
            //khoong toonf taij
            // return $this->response(200, [], 'Misconduct', [], false);
            return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'You cannot spam']);
            // return redirect()->back()->with(['flash_level'=>'error', 'flash_message'=>'Misconduct!']);
        }
        else{
            DB::table('string_token')->where('User', $user->User_ID)->delete();
            return $next($request);
        }
    }
}
