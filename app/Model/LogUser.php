<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LogUser extends Model
{
    protected $table = "log_user";
    public $timestamps = false;

    public static function addLogUser($user, $action, $comment, $ip, $action_id){
        $log_user = new LogUser;
        $log_user->action = $action;
        $log_user->user = $user;
        $log_user->comment = $comment;
        $log_user->ip = $ip;
        $log_user->action_id = $action_id;
        $log_user->datetime = date('Y-m-d H:i:s', time());
        $log_user->save();
        return $log_user;
    }
}
