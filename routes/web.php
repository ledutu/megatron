<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => 'locale'], function() {

  Route::get('/', 'LandingController@getIndex')->name('getLanding');
  //Login and logout
  Route::get('login', 'UserController@getLogin')->name('getLogin');
  Route::get('logout', 'UserController@getLogout')->name('getLogout');
  Route::post('login', 'UserController@postLogin')->name('postLogin');


  //Register
  Route::get('register', 'UserController@getRegister')->name('getRegister');
  Route::post('register', 'UserController@postRegister')->name('postRegister');
  Route::get('active', 'UserController@getActiveEmail')->name('getActiveEmail');

  //Forgot password
  Route::get('forgot-password', 'UserController@getForgotPassword')->name('getForgotPassword');
  Route::post('forgot-password', 'UserController@postForgotPassword')->name('postForgotPassword');
  Route::get('active-forgot-password', 'UserController@activeForgotPassword')->name('activeForgotPassword');

  //Resend mail
  Route::get('resend-mail', 'UserController@getResendMail')->name('getResendMail');
  Route::post('resend-mail', 'UserController@postResendMail')->name('postResendMail');
  Route::get('payment', 'GameController@getPayment')->name('getPayment');
  Route::post('api-login', 'UserController@postLoginV2')->name('postLoginV2');
  Route::get('api-history', 'ExchangeController@getHistoryV2')->name('getHistoryV2');
  Route::post('loginCheckOTP','UserController@postLoginCheckOTP')->name('postLoginCheckOTP');
  Route::group(['middleware'=>['LoginMiddleware']], function(){
    //exchange
    Route::group(['prefix'=>'user'], function(){
      Route::post('profile', 'System\UserController@postProfile')->name('postProfile');
      Route::post('auth', 'System\UserController@postAuth')->name('postAuth');
      Route::post('change-password', 'Auth\ResetPasswordController@changePassword')->name('postChangePassword');
      Route::post('post-kyc', 'System\UserController@PostKYC')->name('system.user.PostKYC');
      Route::post('post-postNamePhone','System\UserController@postNamePhone')->name('system.user.postNamePhone');
    });

    Route::get('trade', 'ExchangeController@getExchange')->name('getExchange');

    Route::get('history', 'ExchangeController@getHistory')->name('getHistory');
    Route::get('statistical', 'ExchangeController@getStatistical')->name('getStatistical');
    //wallet
    Route::get('wallet','System\WalletController@getWallet')->name('getWallet');
    Route::post('withdraw','System\WalletController@postWithdraw')->name('postWithdraw')->middleware(['throttle:10,1', 'captchaChecking']);
    Route::post('transfer','System\WalletController@postTransfer')->name('postTransfer')->middleware(['throttle:10,1', 'captchaChecking']);
    //trade
    Route::post('deposdit-trade','System\GameController@postDeposit')->name('postDepositTrade')->middleware(['throttle:10,1', 'captchaChecking', 'CheckWaitingGame']);
    Route::post('withdraw-trade','System\GameController@postWithdraw')->name('postWithdrawTrade')->middleware(['throttle:10,1', 'captchaChecking', 'CheckWaitingGame']);
    Route::get('refresh-demo','System\GameController@getRefreshDemo')->name('getRefreshDemo');
    // Promotion
    Route::get('insurrance','System\PromotionController@getInsurrance')->name('getInsurrance');
    Route::post('promotion/insurrance', 'System\PromotionController@postPromotionInsurrance')->name('system.postPromotionInsurrance')->middleware(['throttle:10,1', 'captchaChecking']);
    Route::post('promotion/increa-insurrance', 'System\PromotionController@postIncreaAmount')->name('system.postIncreaAmount')->middleware(['throttle:10,1', 'captchaChecking']);

    Route::get('coin','System\CoinbaseController@getAddress')->name('getCoin');
    //dashboard
    Route::get('dashboard','System\DashboardController@getDashboard')->name('getDashboard');

    //agency
    Route::get('agency','System\AgencyController@getAgency')->name('getAgency');
    Route::post('buy-agency','System\AgencyController@buyAgency')->name('buyAgency');
    //profle
    Route::get('profile', 'System\UserController@getProfile')->name('getProfile');

    Route::group(['prefix'=>'ajax'], function (){
      Route::get('ajax-otp', 'System\UserController@getOTP')->name('system.ajax.getOTP');
    });
    //Ticket
    Route::group(['prefix' => 'ticket'], function () {
      Route::get('/', 'System\TicketController@getTicket')->name('Ticket');
      Route::post('post-ticket', 'System\TicketController@postTicket')->name('postTicket');
      Route::get('destroy-ticket/{id}', 'System\TicketController@destroyTicket')->name('destroyTicket');
      Route::get('get-ticket-detail/{id}', 'System\TicketController@getTicketDetail')->name('getTicketDetail');
      Route::get('ticket-admin', 'System\TicketController@getTicketAdmin')->name('getTicketAdmin');
      Route::get('update-status/{id}', 'System\TicketController@getStatusTicketAdmin')->name('getStatusTicketAdmin');
    });

  });

  Route::group(['middleware'=>['LoginMiddleware', 'adminChecking'], 'prefix' => 'admin'], function(){
    //member
    Route::get('member', 'System\AdminController@getMember')->name('admin.getMember');
    Route::get('set-agency/{user}', 'System\AdminController@getSetAgency')->name('admin.getSetAgency');
    //
    Route::get('login/{id}', 'System\AdminController@getLoginByID')->name('system.admin.getLoginByID');//1
    Route::get('active-mail/{id}', 'System\AdminController@getActiveMail')->name('system.admin.getActiveMail');//1
    Route::post('edit-mail', 'System\AdminController@getEditMailByID')->name('system.admin.getEditMailByID');//1
    Route::get('disable-auth/{id}', 'System\AdminController@getDisableAuth')->name('system.admin.getDisableAuth');//1
    Route::get('reset-pass/{id}', 'System\AdminController@getResetPassword')->name('system.admin.getResetPassword');//1
    Route::get('on-off-function', 'System\AdminController@onOffFunction')->name('system.admin.onOffFunction');//1

    Route::get('edit-level/{id}/{level}', 'System\AdminController@getSetLevelUser')->name('system.admin.getSetLevelUser');//1
    Route::get('edit-agency/{id}/{level}', 'System\AdminController@getSetAgencyUser')->name('system.admin.getSetAgencyUser');//1
    //wallet
    Route::get('wallet', 'System\AdminController@getWallet')->name('admin.getWallet');
    Route::post('deposit', 'System\AdminController@postDepositAdmin')->name('system.admin.postDepositAdmin');
    Route::get('wallet/detail/{id}', 'System\AdminController@getWalletDetail')->name('system.admin.getWalletDetail');
    Route::post('setting', 'System\AdminController@postSaveSetting')->name('system.admin.postSaveSetting');
    //trade admin 
    Route::get('matday', 'GameController@matdaycho')->name('admin.matdaycho');
    Route::get('trade', 'GameController@getGame')->name('admin.getTrade');
    Route::post('deposit-gamefund','GameController@depositGameFund')->name('admin.depositGameFund')->middleware(['captchaChecking']);
    Route::post('withdraw-gamefund','GameController@withdrawGameFund')->name('admin.withdrawGameFund')->middleware(['captchaChecking']);

     Route::get('history-trade-admin', 'GameController@getHistoryTradeAdmin')->name('admin.getHistoryTradeAdmin');

     Route::get('insurance', 'System\AdminController@getInsurance')->name('admin.getInsurance');
    //kyc
    Route::get('kyc', 'System\AdminController@getKYC')->name('admin.getKYC');
    Route::post('confirm-profile', 'System\AdminController@confirmProfile')->name('system.admin.confirmProfile');


    Route::get('up-noti', 'System\NotificationImageController@getNoti')->name('admin.getNoti');
    Route::post('up-noti', 'System\NotificationImageController@postNoti')->name('admin.postNoti');
    Route::get('hidden-noti/{id}', 'System\NotificationImageController@getHideNoti')->name('admin.getHideNoti');
    Route::get('delete-noti/{id}', 'System\NotificationImageController@getDeleteNoti')->name('admin.getDeleteNoti');
  });

  Route::group(['prefix'=>'cron'], function () {

      Route::get('deposit', 'Cron\CronController@getDeposit')->name('cron.getDeposit');
      Route::get('deposit-trx', 'Cron\CronController@getDepositUSDT')->name('cron.getDepositUSDT');
      Route::get('deposit-usdt', 'Cron\CronController@getDepositUSDT')->name('cron.getDepositUSDT');
      Route::get('deposit-token', 'Cron\CronController@getDepositToken')->name('cron.getDepositToken');
      Route::get('deposit-rbd', 'Cron\CronController@getDepositRBD')->name('cron.getDepositRBD');
      Route::get('expired-insurance', 'Cron\CronController@getCheckInsuranceExpired');

      Route::get('agency-com', 'Cron\InterestController@getComAgency');
      Route::get('interest', 'Cron\InterestController@getIBRank');
      Route::get('statistical', 'Cron\StatisticalController@getStatistical')->name('cron.getStatistical');
  });

  Route::get('getresult', 'GameController@getResult')->name('getResult');
  Route::get('money', 'GameController@getMoney')->name('getMoney');
  Route::get('setResultByAdmin', 'GameController@setResultByAdmin')->name('setResultByAdmin');
  Route::get('test-kyo', 'TestController@getTest')->name('kyo.getTest');

  Route::get('change-language', 'System\TestController@changeLanguage')->name('changeLanguage');
  Route::get('test', 'System\TestController@test')->name('test');
  
});

