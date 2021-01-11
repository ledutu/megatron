<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Http\Controllers\System\CoinbaseController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

use Image;
use PragmaRX\Google2FA\Google2FA;

use DB;
use Mail;
use GuzzleHttp\Client;
use App\Model\Wallet;
use App\Model\GoogleAuth;
use App\Model\User;
use App\Model\userBalance;
use App\Model\Money;
use App\Http\Controllers\CustomClass\DESEncrypt;



class SAGameController extends Controller{

	public $md5key = "GgaIMaiNNtg";
	public $key = "g9G16nTs";
	public $secretkey = "4DFF55627653498382B72EE2B19762A1";
	public $currency = "USD";
	public $url = "http://sai-api.sa-apisvr.com/api/api.aspx";
	public $client = "https://www.sai.slgaming.net/app.aspx";
	public $language = "en_US";
	
	function test(){
		$method = 'RegUserInfo';
		$date = date('YmdHis', time());

		$username = "quoctestuser";
		$QS = "method=$method&Key=".$this->secretkey."&Time=".$date."&Username=".$username."&CurrencyType=".$this->currency;

		$s = md5($QS.$this->md5key.$date.$this->secretkey);

		$crypt = new DESEncrypt($this->key);

		$q = $crypt->encrypt($QS);
		$data = array('q' => $q, 's' => $s);


		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data)
		    )
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($this->url, false, $context);
		$xml=simplexml_load_string($result) or die("Error: Cannot create object");
		


	}
	
	function postVerifyUsername(){
		$method = 'VerifyUsername';
		$date = date('YmdHis', time());

		$username = "quoctestuser";
		$QS = "method=$method&Key=".$this->secretkey."&Time=".$date."&Username=".$username;

		$s = md5($QS.$this->md5key.$date.$this->secretkey);

		$crypt = new DESEncrypt($this->key);

		$q = $crypt->encrypt($QS);
		$data = array('q' => $q, 's' => $s);


		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data)
		    )
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($this->url, false, $context);
		dd($result);
		$xml=simplexml_load_string($result) or die("Error: Cannot create object");
	}
	
	function postLoginRequest(){
		$method = 'LoginRequest';
		$date = date('YmdHis', time());

		$username = "quoctestuser";
		$QS = "method=$method&Key=".$this->secretkey."&Time=".$date."&Username=".$username."&CurrencyType=".$this->currency;

		$s = md5($QS.$this->md5key.$date.$this->secretkey);

		$crypt = new DESEncrypt($this->key);

		$q = $crypt->encrypt($QS);
		$data = array('q' => $q, 's' => $s);


		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data)
		    )
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($this->url, false, $context);

		
		
		$xml=simplexml_load_string($result) or die("Error: Cannot create object");
		return $this->response(200, ['url' => $this->client.'?username='.$username.'&token='.$xml->Token.'&lobby=A3492'], __('app.email_does_not_exist'), [], true);
	}
	
	function postDebit(){

		$method = 'CreditBalanceDV';
		$date = date('YmdHis', time());
		
		
		$CreditAmount = (float)100;

		$username = "quoctestuser";
		$OrderId = 'IN'.date('YmdHis').$username;

		$QS = "method=$method&Key=".$this->secretkey."&Time=".$date."&Username=".$username."&CurrencyType=".$this->currency."&CreditAmount=".$CreditAmount."&OrderId=".$OrderId;


		$s = md5($QS.$this->md5key.$date.$this->secretkey);

		$crypt = new DESEncrypt($this->key);

		$q = $crypt->encrypt($QS);
		$data = array('q' => $q, 's' => $s);


		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data)
		    )
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($this->url, false, $context);

		
		
		$xml=simplexml_load_string($result) or die("Error: Cannot create object");
		var_dump($xml);exit;
		return $this->response(200, ['url' => $this->client.'?username='.$username.'&token='.$xml->Token.'&lobby=A3492'], __('app.email_does_not_exist'), [], true);
	}
	
	public function GetUserWinLost(){
		$method = 'GetAllBetDetailsDV';
		$Time = date('YmdHis', time());
		$Date = date('Y-m-d',strtotime('-1 day'));
		//$ToTime = date('Y-m-d H:i:s', strtotime('+ 1 day'));

		
		$CreditAmount = (float)100;

		$username = "quoctestuser";
		$OrderId = 'IN'.date('YmdHis').$username;
		$Type = 1;

		$QS = "method=$method&Key=".$this->secretkey."&Time=".$Time."&Username=".$username."&Date=".$Date;



		$s = md5($QS.$this->md5key.$Time.$this->secretkey);

		$crypt = new DESEncrypt($this->key);

		$q = $crypt->encrypt($QS);
		$data = array('q' => $q, 's' => $s);


		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query($data)
		    )
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($this->url, false, $context);

		
		
		$xml=simplexml_load_string($result) or die("Error: Cannot create object");
		var_dump($xml);exit;
		return $this->response(200, ['url' => $this->client.'?username='.$username.'&token='.$xml->Token.'&lobby=A3492'], __('app.email_does_not_exist'), [], true);
	}
	
}

