<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\PersonalInfo;
use App\Http\Requests\Register;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Session;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

use App\Model\GoogleAuth;
use App\Model\User;
use App\Model\Profile;
use App\Model\Log;
use App\Model\Wallet;
use App\Model\Investment;
use App\Jobs\SendMailJobs;
use App\Jobs\SendTelegramJobs;
use App\Mails\UserSignUp;
use App\Model\Eggs;
use App\Model\LogUser;
use App\Model\MUser;
use App\Model\Pools;
use App\Model\Fishs;
use App\Model\Foods;
use Exception;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['only' => ['memberList', 'memberTree', 'postAddMember', 'postKYC', 'getMemberDetail', 'getChildTotalEgg1', 'getBalance']]);
    }
    
	public function checkEmail(Request $req){
		$user = User::where('User_ID', $req->id)->first();
		if($user){
			return $this->response(200, [], $user->User_Email, [], true);
		}
    }
    
    public function getBalance(){
		$user = User::find(Auth::user()->User_ID);
		$checkBalance = User::checkBlockBalance($user->User_ID);
		$EUSD = [
			'Name' => 'Eggs Book USD (EUSD)',
			'Symbol' => 'EUSD',
			'id' => 3,
			'balance' => User::getBalance($user->User_ID, 3),
			'image' => config('url.media').'coin/EUSD.png',
		];
		$EBP = [
			'Name' => 'Eggs Book POP (EBP)',
			'Symbol' => 'EBP',
			'id' => 8,
			'balance' => User::getBalance($user->User_ID, 8),
			'image' => config('url.media').'coin/EBP_1.png',
		];
		$GOLD = [
			'Name' => 'GOLD',
			'Symbol' => 'GOLD',
			'id' => 9,
			'balance' => User::getBalance($user->User_ID, 9),
			'image' => config('url.media').'coin/Gold.png',
		];
		
		$total_egg = count(Eggs::where(['Owner' => $user->User_ID,])->where(['Status' => 1,])->get());
		$total_food = Foods::where(['Owner' => $user->User_ID,])->sum('Amount');
		$total_pool = count(Pools::where(['Owner' => $user->User_ID,])->where(['Status' => 1,])->get());
		// $myEggs = DB::table('eggsTemp')->where('user', $user->User_ID)->sum('amount');
		$info = array(
			'TotalEggs' => $total_egg,
			'total_egg' => $total_egg,
            'total_food' => $total_food,
            'total_pool' => $total_pool
        );
        
		$coinArray = array(
			'EUSD' => $EUSD,
			'EBP' => $EBP,
			'GOLD' => $GOLD,
			'TotalEggs' => $total_egg,
			'total_egg' => $total_egg,
            'total_food' => $total_food,
            'total_pool' => $total_pool
		); 
		return $this->response(200, $coinArray);
    }
	
    public function postKYC(Request $data)
    {
        $user = $data->user();

        $validator = Validator::make($data->all(), [
            'passport' => 'required',
            'passport_image' => 'required|image|mimes:png,jpg,jpeg',
            'passport_image_selfie' => 'required|image|mimes:png,jpg,jpeg'
        ]);

		if ($validator->fails()) {
			foreach ($validator->errors()->all() as $value) {
				// return $error;
				return $this->response(200, [], $value, $validator->errors(), false);
			}
        }

        
        $check_passport = DB::table('profile')->where('Profile_Passport_ID', $data->passport)->first();
        if ($check_passport) {
            return response(array('status' => false, 'message' => __('app.the_passport_has_already_been_taken')), 200);
        }
        $checkExist = Profile::where('Profile_User', $user->User_ID)->whereIn('Profile_Status', [0, 1])->first();
        if ($checkExist) {
            return response(array('status' => false, 'message' => __('app.requested_kyc_please_waiting_confirm')), 200);
        }
        $passportID = $data->passport;
        //get file extension
        $passportImageExtension = $data->file('passport_image')->getClientOriginalExtension();
        $passportImageSelfieExtension = $data->file('passport_image_selfie')->getClientOriginalExtension();

        // set folder and file name
        $randomNumber = uniqid();
        $passportImageStore = "users/" . $user->User_ID . "/profile/passport_image_" . $user->User_ID . "_" . $randomNumber . "." . $passportImageExtension;
        $passportImageSelfieStore = "users/" . $user->User_ID . "/profile/passport_image_selfie_" . $user->User_ID . "_" . $randomNumber . "." . $passportImageSelfieExtension;
        //send to Image server
        // return $passportImageSelfieStore;
        $passportImageStatus = Storage::disk('ftp')->put($passportImageStore, fopen($data->file('passport_image'), 'r+'));
        $passportImageSelfieStatus = Storage::disk('ftp')->put($passportImageSelfieStore, fopen($data->file('passport_image_selfie'), 'r+'));

        if ($passportImageStatus and $passportImageSelfieStatus) {
            $insertProfileData = [
                'Profile_User' => $user->User_ID,
                'Profile_Passport_ID' => $passportID,
                'Profile_Passport_Image' => $passportImageStore,
                'Profile_Passport_Image_Selfie' => $passportImageSelfieStore,
                'Profile_Time' => date('Y-m-d H:i:s')
            ];
            $inserStatus = Profile::create($insertProfileData);
            if ($inserStatus) {
                $kyc_type = config('utils.action.post_kyc');
                LogUser::addLogUser($user->User_ID, $kyc_type['action_type'], $kyc_type['message'], $data->ip(), 11);
                //Gửi telegram thông báo lệh hoa hồng
                // $message = $user->User_ID. " Post KYC\n"
                // 				. "<b>User ID: </b>\n"
                // 				. "$user->User_ID\n"
                // 				. "<b>Email: </b>\n"
                // 				. "$user->User_Email\n"
                // 				. "<b>POST KYC Time: </b>\n"
                // 				. date('d-m-Y H:i:s',time());

                // dispatch(new SendTelegramJobs($message, -364563312));
                //kiem tra KYC
                $checkKYC = Profile::where('Profile_User', $user->User_ID)->whereIn('Profile_Status', [0, 1])->first();
                if ($checkKYC) {
                    $reason = '';
                    $KYC = $checkKYC->Profile_Status;
                    $passport_image = config('url.media') . $checkKYC->Profile_Passport_Image;
                    $passport_image_selfie = config('url.media') . $checkKYC->Profile_Passport_Image_Selfie;
                } else {
                    $KYC = -1;
                    $reason = 'Your Profile KYC Is Unverify!';
                    $passport_image = '';
                    $passport_image_selfie = '';
                }
                $KYC_infor['status'] = $KYC;
                $KYC_infor['reason'] = $reason;
                $KYC_infor['passport'] = $passportID;
                $KYC_infor['passport_image'] = $passport_image;
                $KYC_infor['passport_image_selfie'] = $passport_image_selfie;
                return $this->response(200, ['check_kyc' => $KYC_infor], __('app.update_profile_noted'));
            }
            return $this->response(200, [], __('app.error_please_contact_admin'), [], false);
        }
        return $this->response(200, [], __('app.update_profile_error'), [], false);
    }

    public function memberList(Request $request)
    {
        $user = $request->user();
        $user_list = $this->getList($user, $request);
        $list = [];
        $total_spent_EUSD = 0;
        $total_spent_GOLD = 0;
        for ($i = 0; $i < count($user_list); $i++) {
            $getEggs = User::getEggsUser($user_list[$i]->User_ID);
            $countEggs = $getEggs->where('ActiveTime', 0);
            $countEggsActive = $getEggs->where('ActiveTime', '>', 0);
            if($request->from){
                $countEggs = $countEggs->where('BuyDate', '>=', strtotime($request->from));
                $countEggsActive = $countEggsActive->where('ActiveTime', '>=', strtotime($request->from));
            }
            if($request->to){
                $countEggs = $countEggs->where('BuyDate', '<', strtotime('+1 day', strtotime($request->to)));
                $countEggsActive = $countEggsActive->where('ActiveTime', '<', strtotime('+1 day', strtotime($request->to)));
            }
            $countEggs = $countEggs->count();
            $countEggsActive = $countEggsActive->count();
            $totalMoney = User::getMoneyActivesUser($user_list[$i]->User_ID, $request);
            $list[$i] = [
                'User_ID' => $user_list[$i]->User_ID,
                'Email' => $user_list[$i]->User_Email,
                'Parent' => $user_list[$i]->User_Parent,
                'Created_Date' => $user_list[$i]->User_RegisteredDatetime,
                'total_child_egg' => $this->getChildTotalEgg($user_list[$i]),
                'Eggs' => $countEggs,
                'EggsActive' => $countEggsActive,
                'Spent_EUSD' => isset($totalMoney->EUSD) ? number_format($totalMoney->EUSD, 2) : 0,
                'Spent_GOLD' => isset($totalMoney->GOLD) ? number_format($totalMoney->GOLD, 2) : 0,
                'Spent_Buy_GOLD' => isset($totalMoney->BUYGOLD) ? number_format($totalMoney->BUYGOLD, 2) : 0,
                'F' => $user_list[$i]->f,
                'Level' => 'Member',
            ];
            $total_spent_EUSD += $totalMoney->EUSD;
            $total_spent_GOLD += $totalMoney->GOLD;
        }
        $totalMoneyBranch = User::getMoneyActivesBranch($user->User_ID, $request);
        $branchEgg = User::getEggsBranch($user->User_ID);
        $branchCountEggs = $branchEgg->where('ActiveTime', 0);
        $branchCountEggsActive = $branchEgg->where('ActiveTime', '>', 0);
        if($request->from){
            $branchCountEggs = $branchCountEggs->where('BuyDate', '>=', strtotime($request->from));
            $branchCountEggsActive = $branchCountEggsActive->where('ActiveTime', '>=', strtotime($request->from));
        }
        if($request->to){
            $branchCountEggs = $branchCountEggs->where('BuyDate', '<', strtotime('+1 day', strtotime($request->to)));
            $branchCountEggsActive = $branchCountEggsActive->where('ActiveTime', '<', strtotime('+1 day', strtotime($request->to)));
        }
        $branchCountEggs = $branchCountEggs->count();
        $branchCountEggsActive = $branchCountEggsActive->count();

        $current_page = $user_list->currentPage();
        $total_page = $user_list->lastPage();
        $total_member = $user_list->total();
        $static = $this->getStaticMember($user);
        $f1 = $static->where('f', 1)->count();
        $f2 = $static->where('f', 2)->count();
        $f3 = $static->where('f', 3)->count();
        $total_eggs = User::getTotalEggActiveBranch($user->User_ID);
        $total_eggs = count($total_eggs) > 1 ? count($total_eggs) - 1 : 0;
        return $this->response(200, [
            'list' => $list,
            'current_page' => $current_page,
            'total_page' => $total_page,
            'total_member' => $total_member,
            'total_spent_eusd' => abs($total_spent_EUSD),
            'total_spent_gold' => abs($total_spent_GOLD),
            'total_eggs' => $total_eggs,
            'f1' => $f1,
            'f2' => $f2,
            'f3' => $f3,
            'branch' => [
                'spent_EUSD' => isset($totalMoneyBranch->EUSD) ? number_format($totalMoneyBranch->EUSD, 2) : 0,
                'spent_GOLD' => isset($totalMoneyBranch->GOLD) ? number_format($totalMoneyBranch->GOLD, 2) : 0,
                'BuyGOLD' => isset($totalMoneyBranch->BUYGOLD) ? number_format($totalMoneyBranch->BUYGOLD, 2) : 0,
                'total_eggs' => $branchCountEggs,
                'total_eggs_active' => $branchCountEggsActive,
            ],
        ]);
    }

    public function getChildTotalEgg($user){
        $user_list = User::select('user_agency_level_Name', 'Profile_Status', 'User_ID', 'User_Email', 'User_Phone', 'User_FullName', 'User_RegisteredDatetime', 'User_Parent', DB::raw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " AS f, User_Agency_Level, User_Tree"))
            ->leftJoin('profile', 'Profile_User', 'User_ID')
            ->leftJoin('user_agency_level', 'User_Agency_Level', 'user_agency_level_ID')
            ->whereRaw('User_Tree LIKE "' . $user->User_Tree . '%"')
            ->where('User_ID', '<>', $user->User_ID)
            ->orderBy('User_RegisteredDatetime', 'DESC')->get();
        $total_child_egg = 0;

        for ($i = 0; $i < count($user_list); $i++) {
            $total_child_egg += count(Eggs::where([
                'Owner' => $user_list[$i]->User_ID,
                'Status' => 1,
            ])->get());
        }

        return $total_child_egg;
    } 

    public function getChildTotalEgg1(Request $request){
        $user = $request->user();
        $user_list = User::select('user_agency_level_Name', 'Profile_Status', 'User_ID', 'User_Email', 'User_Phone', 'User_FullName', 'User_RegisteredDatetime', 'User_Parent', DB::raw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " AS f, User_Agency_Level, User_Tree"))
            ->leftJoin('profile', 'Profile_User', 'User_ID')
            ->join('user_agency_level', 'User_Agency_Level', 'user_agency_level_ID')
            ->whereRaw('User_Tree LIKE "' . $user->User_Tree . '%"')
            ->where('User_ID', '<>', $user->User_ID)
            ->orderBy('User_RegisteredDatetime', 'DESC')->get();

        $total_child_egg = 0;

        for ($i = 0; $i < count($user_list); $i++) {
            $total_child_egg += count(Eggs::where([
                'Owner' => $user_list[$i]->User_ID,
                'Status' => 1,
            ])->get());
        }

        return $this->response(200, ['test' => $total_child_egg]);
    } 


    public function memberTree(Request $request)
    {
        $user = $request->user();
        $user_list = $this->getTree($user);
        return $this->response(200, ['trees' => $user_list]);
    }

    public function getList($user, $request = null, $limit = 20)
    {
        $user_list = User::select('user_agency_level_Name', 'Profile_Status', 'User_ID', 'User_Email', 'User_Phone', 'User_FullName', 'User_RegisteredDatetime', 'User_Parent', DB::raw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " AS f, User_Agency_Level, User_Tree"))
            ->leftJoin('profile', 'Profile_User', 'User_ID')
            ->leftJoin('user_agency_level', 'User_Agency_Level', 'user_agency_level_ID')
            ->whereRaw('User_Tree LIKE "' . $user->User_Tree . '%"')
            ->where('User_ID', '<>', $user->User_ID)
            ->orderBy('User_RegisteredDatetime', 'DESC');
        if( isset($request) ){
            if($request->user_id){
                $user_list = $user_list->where('User_ID', $request->user_id);
            }
            if($request->user_email){
                $user_list = $user_list->where('User_Email', 'LIKE', "%$request->user_email%");
            }
            if($request->user_f){
                $user_list = $user_list->whereRaw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " = ". $request->user_f);
            }
        }
        if($limit){
            $user_list = $user_list->paginate($limit);
        }else{
            $user_list = $user_list->get();
        }
        return $user_list;
    }

    public function getStaticMember($user)
    {
        $user_list = User::select('User_ID', 'User_Email', DB::raw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " AS f"))
            ->whereRaw("(CHAR_LENGTH(User_Tree)-CHAR_LENGTH(REPLACE(User_Tree, ',', '')))-" . substr_count($user->User_Tree, ',') . " <= 3")
            ->whereRaw('User_Tree LIKE "' . $user->User_Tree . ',%"')
            ->get();
        return $user_list;
    }

    public function getTree($user)
    {
        if (!isset($user->user_id)) {
            $userID = $user->User_ID;
        } else {
            $userID = $user->user_id;
            $user = User::find($userID);
        }
        $amount_investment = DB::table('investment')
            ->where('investment_User', $userID)
            ->where('investment_Status', 1)
            ->sum(DB::raw('investment_Amount * investment_Rate'));
        $total_invest_branch = User::join('investment', 'investment_User', 'User_ID')->where('User_Tree', 'LIKE', $user->User_Tree . ',%')->sum(DB::raw('investment_Amount * investment_Rate'));
        $list = array(
            'id' => $userID,
            'name' =>  $user->User_Email,
            'title' => $userID,
            'amount_investment' => number_format($amount_investment, 2, ',', ''),
            'Sales' => number_format($total_invest_branch, 2, ',', ''),
            'children' => $this->buildTree($userID),
            'className' => 'node-tree ' . strtoupper($user->user_Name),
        );
        return $list;
    }

    function buildTree($idparent, $idRootTemp = null, $barnch = null)
    {

        $build = User::select('User_Email', 'User_Name', 'User_ID', 'User_Tree')
            ->where('User_Parent', $idparent)->GET();
        $child = array();
        if (count($build) > 0) {
            for ($i = 0; $i < count($build); $i++) {
                if (isset($build[$i])) {
                    $amount_investment = DB::table('investment')
                        ->where('investment_User', $build[$i]->User_ID)
                        ->where('investment_Status', 1)
                        ->sum(DB::raw('investment_Amount * investment_Rate'));
                    $total_invest_branch = User::join('investment', 'investment_User', 'User_ID')->where('User_Tree', 'LIKE', $build[$i]->User_Tree . ',%')->sum(DB::raw('investment_Amount * investment_Rate'));
                    $child[] = array(
                        'id' => $build[$i]->User_ID,
                        'name' => $build[$i]->User_Email,
                        'title' => $build[$i]->User_ID,
                        'amount_investment' => number_format($amount_investment, 2, ',', ''),
                        'Sales' => number_format($total_invest_branch, 2, ',', ''),
                        'className' => 'node-tree ' . strtoupper($build[$i]->User_Name),
                        'children' => $this->buildTree($build[$i]->User_ID),
                    );
                }
            }
        }
        return $child;
    }

    public function postAddMember(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'User_Email' => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();


        $sponsor = $user->User_ID;
        $sponserInfo = User::where('User_ID', $sponsor)->first();
        $userID = $this->RandomIDUser();

        $addMember = config('utils.action.add_member');
        LogUser::addLogUser($user->User_ID, $addMember['action_type'], 'Invite ' . $userID . ' join', $request->ip(), 8);

        $userTree = $sponserInfo->User_Tree . "," . $userID;

        $password = $this->generateRandomString(10);
        // $token = Crypt::encryptString($request->User_Email . ':' . time());
        $token = Crypt::encryptString($request->User_Email . ':' . time() . ':' . $password);

        $currentUser = new User();
        $currentUser->User_ID = $userID;
        $currentUser->User_Email = $request->User_Email;
        $currentUser->User_EmailActive = 0;
        $currentUser->User_Password = bcrypt($password);
        $currentUser->User_PasswordNotHash = $password;
        $currentUser->User_RegisteredDatetime = date('Y-m-d H:i:s');
        $currentUser->User_Parent = $sponsor;
        $currentUser->User_Tree = $userTree;
        $currentUser->User_Level = 0;
        $currentUser->User_Token = $token;
        $currentUser->User_Agency_Level = 0;
        $currentUser->User_Status = 1;

        // $currentUser->save();

        if (!$currentUser) {
            return $this->response(200, [], __('app.there_is_an_error_please_contact_admin'), [], false);
        }
        try {
            // gửi mail thông báo
            // $data = array('User_ID' => $userID, 'User_Email' => $request->User_Email, 'token' => $token);
            $data = [
                'User_Email' => $currentUser->User_Email,
                'User_ID' => $currentUser->User_ID,
                'User_Token' => $currentUser->User_Token,
                'User_Parent' => $currentUser->User_Parent,
                'User_Tree' => $currentUser->User_Tree,
                'User_PasswordNotHash' => $currentUser->User_PasswordNotHash,
            ];
            dispatch(new SendMailJobs('add-user', $data, 'Active Account Member!', $user->User_ID));
            // dispatch(new SendMailJobs('Active', $data, 'Active Account!', $userID));
            // Mail::to($currentUser->User_Email)->send(new UserSignUp($currentUser));
        } catch (Exception $e) {
            return $this->response(200, [], 'Wrong email format', [], false);
        }
        return $this->response(200, ['user' => $currentUser], __('app.registration_successful_please_check_your_email_to_confirm'));
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function RandomIDUser()
    {
        $id = rand(100000, 999999);
        //TẠO RA ID RANĐOM
        $user = User::where('User_ID', $id)->first();
        //KIỂM TRA ID RANDOM ĐÃ CÓ TRONG USER CHƯA
        if (!$user) {
            return $id;
        } else {
            return $this->RandomIDUser();
        }
    }

    public function getMemberDetail($id)
    {
        $user = User::where('User_ID', $id)->first();
        $total_egg = count(Eggs::where(['Owner' => $id,])->get());
		$total_food = Foods::where(['Owner' => $id,])->sum('Amount');
		$total_pool = count(Pools::where(['Owner' => $id,])->get());

        return $this->response(200, [
            'email' => $user? $user->User_Email: null,
            'total_egg' => $total_egg,
            'total_food' => $total_food,
            'total_pool' => $total_pool,
        ]);
    }
}
