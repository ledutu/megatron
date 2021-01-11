<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use App\Model\User;
use DB;
use App\Jobs\SendTelegramJobs;
class TicketController extends Controller{


	public $keyHash	 = 'DAFCOCoorgsafwva';

    public function getTicket(Request $req){
        $user = Auth::user();
        $tickets = DB::table('ticket')->join('ticket_subject', 'ticket_subject_id', 'ticket_Subject')
            ->where('ticket_User', $user->User_ID)
            ->where('ticket_ReplyID', 0)
            ->orderByDesc('ticket_ID')->get();
        $subject = DB::table('ticket_subject')->get();
        $data = [];
        $dataSubject = [];
        foreach($tickets as $ticket){
            $findlastRep = DB::table('ticket')->where('ticket_ReplyID',$ticket->ticket_ID)->orderBy('ticket_ID', 'DESC')->first();
            if(!$findlastRep){
                $status = 'Waiting';
            }else{
                $getInfo = User::whereIn('User_Level', [1,2,3])->where('User_ID', $findlastRep->ticket_User)->first();
                if($getInfo){
                    $status = 'Replied';
                }else{
                    $status = 'Waiting';;
                }
            }
	        $data[] = [
	        			'id'=>$ticket->ticket_ID,
				        'email'=>$user->User_Email,
				        'subject'=>$ticket->ticket_subject_name,
				        'content'=>$ticket->ticket_Content,
				        'date'=>$ticket->ticket_Time,
			            'count' => (DB::table('ticket')->where('ticket_ReplyID', $ticket->ticket_ID)->count())+1,
			            'status' => $status,
					];
        }
        foreach($subject as $s){
	        $dataSubject[] = ['id'=>$s->ticket_subject_id, 'name'=>$s->ticket_subject_name];
        }
        return response(array('status'=>true, 'subject'=>$dataSubject, 'data'=>$data), 200);
    }

    public function postTicket(Request $req)
    {
        $user = Auth::user();
        $data = json_decode(json_encode($req->data));
        
		if(!$req->data || $data == ''){
            return response(array('status'=>false, 'message'=>__('app.miss_data')), 200);
		}
		if(!isset($data->content) || $data->content == ''){
            return response(array('status'=>false, 'message'=>__('app.miss_content')), 200);
		}
		
        
        $replyID = 0;
        if (isset($data->replyID) && $data->replyID != 0){
            $replyID = $data->replyID;
            $subject = DB::table('ticket')->where('ticket_ID', $replyID)->select('ticket_Subject')->first();
            $subjectID = $subject->ticket_Subject;
        }else{
	        if ($data->subject) {
	            $subjectID = $data->subject;
	        }else{
                return response(array('status'=>false, 'message'=>__('app.miss_subject')), 200);
	        }
        }


        $addArray = array(
            'ticket_User' => $user->User_ID,
            'ticket_Time' => date('Y-m-d H:i:s'),
            'ticket_Subject' => $subjectID,
            'ticket_Content' => $data->content,
            'ticket_Status' => 0,
            'ticket_ReplyID' => $replyID
        );
        
        $data = DB::table('ticket')->insert([$addArray]);

        $id = DB::getPdo()->lastInsertId();
        
		//telegram 454650369
		if($user->User_Level == 0){
			$getSubject = DB::table('ticket_subject')->where('ticket_subject_id', $subjectID)->first();
			$message = "<b> NOTICE TICKET </b>\n"
					. "ID: <b>$user->User_ID</b>\n"
					. "EMAIL: <b>$user->User_Email</b>\n"
					. "SUBJECT: <b>$getSubject->ticket_subject_name</b>\n"
					. "CONTENT: <b>$req->content</b>\n"
					. "<b>Submit Ticket Time: </b>\n"
					. date('d-m-Y H:i:s',time());
                    
            
			// dispatch(new SendTelegramJobs($message, -225178958));
		}
        return response(array('status'=>true, 'message'=>__('app.please_waiting_supporter_reply'), 'id'=>$id), 200);
    }
    
    public function getTicketDetail($id){
        $tickets = DB::table('ticket')->join('users', 'User_ID', 'ticket_User')
			        ->join('ticket_subject', 'ticket_subject_id', 'ticket_Subject')
			        ->where('ticket_ID', $id)
			        ->orWhere('ticket_ReplyID', $id)
			        ->orderBy('ticket_ID')
			        ->get();
        $data = [];
        foreach($tickets as $ticket){
			$data[] = [
			'id'=>$ticket->ticket_ID,
            'email_level'=>($ticket->User_Level != 0 ? 'Supporter' : $ticket->User_Email),
            'email'=>$ticket->User_Email,
            'ticket_user'=>$ticket->ticket_User,
	        'subject'=>$ticket->ticket_subject_name,
	        'content'=>$ticket->ticket_Content,
			'date'=>$ticket->ticket_Time
	        ];
        }
        return response(array('status'=>true, 'data'=>[$data], 'id'=>$ticket->ticket_ID, 'user_id'=>$ticket->ticket_User), 200);
    }

    public function destroyTicket($id)
    {
        $changeStatus = DB::table('ticket')
            ->where('ticket_ID', $id)
            ->update(['ticket_Status' => -1]);
        if ($changeStatus) {
            return response(array('status'=>true, 'message'=>__('app.destroy_ticket_successful')));
        }
        return response(array('status'=>false, 'message'=>__('app.destroy_ticket_failed')));        
    }
}
