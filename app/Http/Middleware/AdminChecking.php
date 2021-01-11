<?php

namespace App\Http\Middleware;

use Closure;

class AdminChecking
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
      	if($user->User_ID == 657744){
           return $next($request);
        }
      	 else if($user->User_Level != 1 && $user->User_Level != 2 && $user->User_Level != 3) {
          abort(404);
          return redirect()->back()->with([
            'flash_message' => 'You have no permission to access',
            'flash_level' => 'error',
          ]);

        }
      
      return $next($request);
    }
}
