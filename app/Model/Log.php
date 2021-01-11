<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Jobs\SendMailJobs;
class Log extends Model{
    protected $table = "log";

    public $timestamps = true;

    const CREATED_AT = 'Log_CreatedAt';
	const UPDATED_AT = 'Log_UpdatedAt';

    public static function insertLog($user, $action, $amount, $comment){
	    $result = new Log;
	    $result->Log_User = $user;
	    $result->Log_Action = $action;
	    $result->Log_Amount = $amount;
	    $result->Log_Comment = $comment;
	    $result->Log_Status = 1;
	    $result->save();
        return $result;
    }
    
    public static function insertLogProfit($user = '', $action, $comment){
	    DB::table('log_interest')->insert([
		    'Log_User' => $user,
		    'Log_Action' => $action,
		    'Log_Comment' => $comment,
		    'Log_CreatedAt' => date('Y-m-d H:i:s'),
		    'Log_UpdatedAt' => date('Y-m-d H:i:s'),
		    'Log_Status' => 1,
	    ]);
	    return true;
    }
    
    public static function sendMailCommission($dataSendMail){
		foreach($dataSendMail as $data){
			$user = $data['user'];
			$amount = $data['amount'];
			$action = $data['actionName'];
	        $data = array('User_ID' => $user->User_ID, 'User_Email'=> $user->User_Email, 'User_Name'=>$user->User_Name, 'amount'=>number_format($amount, 2), 'actionName' => $action);
	        dispatch(new SendMailJobs('Noti_Commission', $data, 'Notification Commission!', $user->User_ID));
		}
		return true;
    }
}
