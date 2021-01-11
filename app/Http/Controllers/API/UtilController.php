<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\StringToken;
use Carbon\Carbon;

class UtilController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth:api', ['only' => ['codeSpam']]);
    }


    public function codeSpam(Request $request)
    {
        $token = $this->generateRandomString();
        $user = $request->user();
        $stringToken = new StringToken();
        $stringToken->ID = random_int(100000, 999999);
        $stringToken->Token = $token;
        $stringToken->User = $user->User_ID;
        $stringToken->CreateDate = Carbon::now()->toDateTimeString();
        $stringToken->save();

        return $this->response(200, ['CodeSpam'=>$token], '', true);
    }

    public function generateRandomString($length = 100)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
