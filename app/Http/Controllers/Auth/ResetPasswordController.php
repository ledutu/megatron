<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPassword;
use App\Model\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class ResetPasswordController extends Controller
{
    public function changePassword(ResetPassword $request)
    {   
        $user = session('user');
        $dbPassword = User::where('User_ID', $user->User_ID)->value('User_Password');
        if (Hash::check($request->current_password, $dbPassword)) {
            User::where('User_ID', $user->User_ID)
                ->update(['User_Password' => bcrypt($request->new_password)]);
            return redirect()->back()->with(['flash_level' => 'success', 'flash_message' => 'Password changed successful']);
        }
        return redirect()->back()->with(['flash_level' => 'error', 'flash_message' => 'Current password incorrect']);
    }
}
