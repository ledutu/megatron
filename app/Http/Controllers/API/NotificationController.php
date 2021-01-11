<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\LogAdmin;
use App\Model\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator, DB;
class NotificationController extends Controller
{
    public function __construct(){
		$this->middleware('auth:api', ['except' => ['getNotification', 'getBlog', 'postAddBlog', 'postUpdateBlog', 'getNotiPost']]);
    }
    public function getNotiPost(){
        $notiPost = DB::table('post_notifications')->where('status', 1)->orderBy('order','asc')->get();
        return $this->response(200, $notiPost);		
    }
    public function postNotification(Request $req)
    {
        $user = Auth::user();
        if($user->User_Level != 1){
            return $this->response(200, [], 'Please contact admin!', [], false);
        }
        if (!$req->hasFile('notification_image')) {
            return $this->response(200, [], 'Miss notification image!', [], false);
        }
        if (!$req->landing && !$req->system) {
            return $this->response(200, [], 'Miss type!', [], false);
        }
        $landing = 0;
        $system = 0;
        if($req->landing){
            $landing = 1;
        }
        if($req->system){
            $system = 1;
        }
        $notificationImageExtension = $req->file('notification_image')->getClientOriginalExtension();
        // set folder and file name
        $randomNumber = uniqid();
        $notificationImageStore = "notification/notification_image_" . $user->User_ID . "_" . $randomNumber . "." . $notificationImageExtension;
        //send to Image server
        // return $passportImageSelfieStore;
        $notificationImageStatus = Storage::disk('ftp')->put($notificationImageStore, fopen($req->file('notification_image'), 'r+'));

        if ($notificationImageStatus) {
            $inserStatus = new Notification;
            $inserStatus->image = config('url.media').$notificationImageStore;
            $inserStatus->landing = $landing;
            $inserStatus->system = $system;
            $inserStatus->save();
            if ($inserStatus) {
                LogAdmin::addLogAdmin($user->User_ID, 'Up Notification image', 'Up Notification image by: '.$user->User_ID);
                return $this->response(200, [], __('app.update_notification_noted'));
            }
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
        return $this->response(200, [], __('app.update_notification_error'), [], false);
    }

    public function getNotification(Request $req){
        if(!$req->type){
            return $this->response(200, [], __('app.please_enter_type'), [], false);
        }
        $notification = Notification::orderByDesc('id')->get();
        if($req->type == 1){
            $notification = Notification::where('landing', 1)->where('status', 1)->orderByDesc('id')->get();
        }if($req->type == 2){
            $notification = Notification::where('system', 1)->where('status', 1)->orderByDesc('id')->get();
        }
        if($notification){
            // $image = config('url.media').$notification->image;
            return $this->response(200, $notification);
        }
    }
    public function postUpdateNotification(Request $req){
        $user = Auth::user();
        if ($user->User_Level != 1) {
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
        if ($req->action == 1) {
            $update_notification = Notification::where('id', $req->id)->update(['status' => 0]);
            if ($update_notification) {
                LogAdmin::addLogAdmin($user->User_ID, 'Hide notification image', 'Hide notification image by ID: ' . $user->User_ID);
                return $this->response(200, [], __('app.hide_image_notification_successful'));
            }
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
        if ($req->action == -1) {
            $notification = Notification::where('id', $req->id)->first();

            $deleteImage_Server = Storage::disk('ftp')->delete([$notification->image]);
            // $deleteImage_Server = true;
            if ($deleteImage_Server) {
                $notification->delete();
				LogAdmin::addLogAdmin($user->User_ID, 'Delete notification image', 'Delete notification image by ID: ' . $user->User_ID);
                return $this->response(200, [], __('app.delete_image_notification_successful'));
            }
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
    }

    public function getBlog(Request $req){
        $limit = 10;
        if($req->limit){
            $limit = $req->limit;
        }
        $blogs = DB::table('blog')->select('id', 'title', 'content', 'banner')->where('status', 1);
        if($req->id){
            $blogs = $blogs->where('id', $req->id)->first();
            return $this->response(200, $blogs, 'Success!', true);
        }
        $blogs = $blogs->paginate($limit);
        // var_dump($blogs);exit;
        $data['list'] = $blogs->items();
        $data['total_page'] = $blogs->lastPage();
        $data['current_page'] = $blogs->currentPage();
        $data['total'] = $blogs->total();
        return $this->response(200, $data, __('app.success'), true);
    }

    public function postAddBlog(Request $req){
        $validator = Validator::make($req->all(), [
            'content' => 'required',
            'title' => 'required|string',
            'description' => 'required|string',
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }
        $user = Session('user') ?? $req->user();
        $notificationImageExtension = $req->file('banner')->getClientOriginalExtension();
        // set folder and file name
        $randomNumber = uniqid();
        $bannerImageStore = "blog/blog_image_" . $user->User_ID . "_" . $randomNumber . "." . $notificationImageExtension;
        $bannerImageStatus = Storage::disk('ftp')->put($bannerImageStore, fopen($req->file('banner'), 'r+'));
        if(!$bannerImageStatus){
            return $this->response(200, [], __('app.insert_banner_error'), [], false);
        }
        $dataInsert = [
            'user' => $user->User_ID,
            'banner' => $bannerImageStore,
            'title' => $req->title,
            'description' => $req->description,
            'content' => $req->content,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => 1,
        ];
        $insertBlog = DB::table('blog')->insert($dataInsert);
        if($insertBlog){
            return $this->response(200, [], __('app.insert_blog_successful'), [], true);
        }else{
            return $this->response(200, [], __('app.insert_blog_failed_please_try_again'), [], false);
        }
    }

    public function postUpdateBlog(Request $req){
        $validator = Validator::make($req->all(), [
            'id' => 'required|exists:blog,id',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }
        $user = Session('user') ?? $req->user();
        $dataUpdate = [];
        $getBlog = DB::Table('blog')->where('id', $req->id)->first();
        if($req->file('banner')){
            $deleteImage_Server = Storage::disk('ftp')->delete([$getBlog->banner]);
            $notificationImageExtension = $req->file('banner')->getClientOriginalExtension();
            // set folder and file name
            $randomNumber = uniqid();
            $bannerImageStore = "blog/blog_image_" . $user->User_ID . "_" . $randomNumber . "." . $notificationImageExtension;
            $bannerImageStatus = Storage::disk('ftp')->put($bannerImageStore, fopen($req->file('banner'), 'r+'));
        
            if(!$bannerImageStatus){
                return $this->response(200, [], __('app.insert_banner_failed'), [], false);
            }
            $dataUpdate['banner'] = $bannerImageStore;
        }
        if($req->title){
            $dataUpdate['title'] = $req->title;
        }
        if($req->description){
            $dataUpdate['description'] = $req->description;
        }
        if($req->content){
            $dataUpdate['content'] = $req->content;
        }
        $dataUpdate['updated_at'] = date('Y-m-d H:i:s');
        $insertBlog = DB::table('blog')->where('id', $req->id)->update($dataUpdate);
        if($insertBlog){
            return $this->response(200, [], __('app.update_blog_successful'), [], true);
        }else{
            return $this->response(200, [], __('app.update_blog_failed_please_try_again'), [], false);
        }
    }
}
