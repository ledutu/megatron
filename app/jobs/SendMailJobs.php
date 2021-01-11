<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Model\User;
use App\Model\Log;
use DB;

use Mail;

class SendMailJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
 
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $template;
    public $data;
    public $tittle;

    public $userID;

    public function __construct($template, $data = [], $tittle, $userID)
    {
        
        $data['url_web'] = 'https://igtrade.co/';
        
        $this->template = $template;
        $this->data = $data;
        $this->tittle = $tittle;
        $this->userID = $userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    $template = $this->template;
        $data = $this->data;
        $tittle = $this->tittle;
        $userID = $this->userID;
        


        //prosess
        $templateMail = 'Mail.'.$template;
        Mail::send($templateMail, $data, function($msg) use ($data, $tittle){
            $msg->from('no-reply@igtrade.co','IGTrade');
            
            $msg->to($data['User_Email'])->subject($tittle);
        });

        $amountUSD = 0;
        if(isset($data['amountUSD'])){
            $amountUSD = $data['amountUSD'];
            echo $amountUSD;

        }
        

        $log = Log::insertLog($userID, $tittle, $amountUSD, $tittle);
    }
}
