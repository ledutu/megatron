<?php

namespace App\Http\Middleware;

use Closure;

class CaptchaChecking
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
        $url = config('utils.captcha_url');
        $data = ['secret' => config('utils.secret_key'), 'response' => $request->token_v3];
        $options = ['http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]];
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $response_keys = json_decode($response, true);
        if(!$response_keys['success']){
            return redirect()->back()->with(['message'=>'Error! Please try again!', 'status'=>false]);
        }

        return $next($request);
    }
}
