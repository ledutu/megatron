<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Money;
use App\Model\User;

class TestController extends Controller
{
    //
  
  	public function test(Request $request){
       echo __('agency.invitation_link');
    }
  
  	public function changeLanguage(Request $request){
      \Session::put('language', $request->language);
      return redirect()->back();
    }
 
}
