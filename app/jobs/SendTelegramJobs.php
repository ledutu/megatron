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

class SendTelegramJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $message;
    public $channel;
    public function __construct($message, $channel)
    {
        //
        $this->message = $message;
        $this->channel = $channel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    
	    $client = new \GuzzleHttp\Client(); //GuzzleHttp\Client
	    $result = json_decode($client->request('POST', 'https://adcgame.club/api/sendMessage',[
										'form_params' => [
											'channel' => $this->channel,
											'message' => $this->message
										]
									])->getBody()->getContents());
        
//         Log::insertLog($user, 'Mail Active', 'Send Email Active');
    }
}
