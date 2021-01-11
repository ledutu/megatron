<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Eggs;
use App\Model\SMS;
use App\Model\User;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use AWS;
use Illuminate\Support\Facades\App;
use Validator;

class SMSController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function receiveOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_phone' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $checkPhone = User::where('User_Phone', $request->user_phone)->first();

        if($checkPhone) return $this->response(200, [], __('app.this_phone_has_been_verified'), [], false);

        $user = $request->user();
        $sms = SMS::where('user_phone', $request->user_phone)->first();
        if (!$sms) {
            $sms = new SMS();
        }
        $sms->user_id = $user->User_ID;
        $sms->user_phone = $request->user_phone;
        $sms->code = SMS::generateCode();

        $client = new \GuzzleHttp\Client();
        $smsConfig = config('sms');
        $data = [
            'Phone' => $request->user_phone,
            'Content' => 'Your Eggsbook App verification code is: ' . $sms->code,
            'ApiKey' => $smsConfig['API_KEY'],
            'SecretKey' => $smsConfig['SECRET_KEY'],
            'Brandname' => $smsConfig['BRAND_NAME'],
            'SmsType' => 2,
        ];
        $response = $client->request('GET', $smsConfig['BASE_URL'] . '/SendMultipleMessage_V4_get', [
            'query' => $data
        ]);
        $res = json_decode($response->getBody(), true);
        // return $this->response(200, ['res' => $res, 'sms' => $sms]);

        if ($res['CodeResult'] == "100") {
            $sms->save();
            return $this->response(200, ['sms' => $sms], __('app.please_check_your_SMS_to_get_otp_this_otp_expire_in_30_minutes'));
        } else {
            return $this->response(200, [], __('app.generate_failed'), [], false);
        }
    }

    public function confirmOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $sms = SMS::where([
            'user_id' => $user->User_ID,
            'code' => $request->code,
        ])->first();

        if (!$sms) return $this->response(200, [], __('app.otp_code_is_wrong'), [], false);

        $user->User_Phone = $sms->user_phone;
        $user->save();
        $sms->delete();

        return $this->response(200, [
            'user_phone' => $sms->user_phone,
            'status' => true,
        ], __('app.confirm_successful'));
    }

    public function getBalanceSMS(Request $request)
    {
        $smsConfig = config('sms');
        $API_KEY = $smsConfig['API_KEY'];
        $SECRET_KEY = $smsConfig['SECRET_KEY'];
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'http://rest.esms.vn/MainService.svc/json/GetBalance/' . $API_KEY . '/' . $SECRET_KEY);
        $res = json_decode($response->getBody(), true);

        return $this->response(200, ['balance' => $res]);
    }
}
