<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function testfunction(){
      $balance = User::getBalance(861468, 5);
      $user = session('user');
      // return $user;
      echo $balance;
    }
    public function response($code = 200, $data = [], $message = '', $errors = [], $status = true)
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'data' => $data,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    public function redirectBack($flash_message = '', $error = [], $flash_level = 'success') {
      return redirect()->back()->with([
        'flash_message' => $flash_message,
        'flash_level' => $flash_level,
      ]);
    }

    public function route($route, $data = [], $flash_message = '', $error = [], $flash_level = 'success') {
      return redirect()->route($route)->with([
        'data' => $data,
        'flash_message' => $flash_message,
        'error' => $error,
        'flash_level' => $flash_level,
      ]);
    }

    public function view($route, $data = [], $flash_message = '', $error = [], $flash_level = 'success') {
      return view($route, [
        'data' => $data,
        'flash_message' => $flash_message,
        'error' => $error,
        'flash_level' => $flash_level,
      ]);
    }
}
