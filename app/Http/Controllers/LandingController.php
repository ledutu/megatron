<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session; 

use DB;
use App\Model\GoogleAuth;
use App\Model\Stringsession;





class LandingController extends Controller
{
	public function getIndex(){
      $noti_image = DB::table('notification')->where('status', 1)->where('landing',1)->orderBy('id','desc')->get();
      return view('landingpage.index',compact('noti_image'));
    }
	
}

