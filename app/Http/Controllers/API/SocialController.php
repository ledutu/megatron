<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\SocialUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Google_Client;
use Google_Service_YouTube;
use App\Model\ListMission;

class SocialController extends Controller
{
    public function __construct(){
		$this->middleware('auth:api');
	}
    public function getLinkLogin(){ 
        if (!session_id()) {
            session_start();
        }
        
        $fb = new \Facebook\Facebook([
            'app_id' => '757868474790074',
            'app_secret' => 'fba5a9cb7cec94cb2c20670d175a8b3b',
            'default_graph_version' => 'v2.10',
            'persistent_data_handler' => 'session'
        ]);
        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['email','pages_manage_posts']; // Optional permissions
        $loginUrl = $helper->getLoginUrl('https://apibeta.eggsbook.com/public/api/v1/get-login-facebook', $permissions);

        $google_client = new Google_Client();
        $google_client->setClientId('509340483525-p15b5d0pu962addt8f2eggosbnh75frj.apps.googleusercontent.com');
        $google_client->setClientSecret('MXycNoIdf-fBnsYNsDf2Cy3B');
        $google_client->setRedirectUri('https://apibeta.eggsbook.com/public/api/v1/social/post-login-google');
        $google_client->addScope('email');
        $google_client->addScope('profile');
        $url_google = $google_client->createAuthUrl();

        return $this->response(200, ['link_facebook'=>$loginUrl, 'link_google'=>$url_google]);
    }
    public function postLoginFacebook(Request $req){
        $user = Auth::user();
        $validator = Validator::make($req->all(), [
			'id' => 'required',
			'name' => 'required'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
		}
        $facebook_id = $req->id;
        $name = $req->name;
        $check_social_user = SocialUser::where('facebook_id',$facebook_id)->first();
        if($check_social_user){
            return $this->response(200, [], 'Fails!', [], false);
        }
        $insert_social_user = new SocialUser(); 
        $insert_social_user->name = $name;
        $insert_social_user->facebook_id = $facebook_id;
        $insert_social_user->user_id = $user->User_ID;
        $insert_social_user->save();
        if($insert_social_user){
            return $this->response(200, ['connect_facebook'=>true], __('app.insert_social_user_successful'));
        }
        return $this->response(200, [], 'Fails!', [], false);
    }
    public function postLoginGoogle(Request $req){
        $user = Auth::user();
        // $google_client = new Google_Client();
        // $google_client->setClientId('509340483525-p15b5d0pu962addt8f2eggosbnh75frj.apps.googleusercontent.com');
        // $google_client->setClientSecret('MXycNoIdf-fBnsYNsDf2Cy3B');
        // $google_client->setRedirectUri('https://apibeta.eggsbook.com/public/api/v1/social/post-login-google');
        $validator = Validator::make($req->all(), [
			'google_id' => 'required',
			'asset_token' => 'required'
		]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }
        $google_id = $req->google_id;
        // return $google_id;
        $asset_token = $req->asset_token;
        $check_social_user = SocialUser::where('google_id',$google_id)->first();
        if($check_social_user){
            $check_social_user->asset_token = $asset_token;
            $check_social_user->save();
            return $this->response(200, ['connect_google'=>true], __('app.insert_social_user_successful'));
        }
        $insert_social_user = new SocialUser(); 
        $insert_social_user->google_id = $req->google_id;
        $insert_social_user->asset_token = $asset_token;
        $insert_social_user->user_id = $user->User_ID;
        $insert_social_user->save();
        if($insert_social_user){
            return $this->response(200, ['connect_google'=>true], __('app.insert_social_user_successful'));
        }
        return $this->response(200, [], 'Fails!', [], false);
    }

    public function checkSubscribeYoutobe(Request $req){
        $user = Auth::user();
        $google_client = new Google_Client();
        $google_client->setAuthConfig(public_path('client_secret.json'));
        $social_user = SocialUser::where('user_id',$user->User_ID)->first();

        $google_client->setAccessToken($social_user->asset_token);

        $service = new Google_Service_YouTube($google_client);

        $queryParams = [
            'forChannelId' => 'UCSq2BfXJHUfO3xraQtTyiCA',
            'mine' => true
        ];
        
        $response = $service->subscriptions->listSubscriptions('snippet,contentDetails', $queryParams);

        $check_like = count($response->items);
        if($check_like > 0){
            ListMission::updateMission($user->User_ID,4 , -1);
            ListMission::where('mission_id', 4)->where('user_id', $user->User_ID)->where('status', '<>', 0)->update(['mission_progress' => 1]);
            return $this->response(200, ['like_youtobe'=>true]);
        }
        return $this->response(200, ['like_youtobe'=>false], __('app.you_have_not_subscribed_to_the_youtube_channel'), [], false);
    }
}
