@php
  $user = Session('user');
  $BalanceMain = App\Model\User::getBalance($user->User_ID, 5);
  $BalanceTrade['live'] = App\Model\User::getBalanceGame($user->User_ID, 5);
  $BalanceTrade['demo'] = App\Model\User::getBalanceGame($user->User_ID, 99);
	$language = \DB::table('lang_version')->get();

@endphp
<header class="page-header flex">
  <div class="navbar flex-1">
    <a href="{{route('getExchange')}}" class="pull-left flex-1 flex justify-start self-center">
      <img class="img-logo" src="exchange/img/logo.png?v=1">
      <img class="img-logo-mobile " src="exchange/img/logo_mobile.png">
    </a>
    <ul class="nav navbar-nav navbar-right pull-right">
    	<li class="dropdown btn-select-acount" data-menu="#account-menu">
        <a href="javascript:void(0)" title="Account" id="account" class="dropdown-toggle my--subaccount" >
          <span>
            <div id="index_balance_name"></div>
            <div id="UserBalance"></div>
          </span>
        </a>
        <ul class="dropdown-menu " id="account-menu" role="menu">
          <li role="presentation" class="account-picture live-select active">
            <a class="select_coin cursor-pointer flex" data-name="live">
              <div class="col-span-1 flex flex-col flex-1">
                <span class="d-block white white balance-name">{{__('exchange.live_account')}}</span>
                <span class="balance-value">${{number_format($BalanceTrade['live'],2)}}</span>
              </div>
            </a>
          </li>
          <li role="presentation" class="account-picture demo-select">
            <a class="select_coin cursor-pointer flex " data-name="demo">
              <div class="col-span-1 flex flex-col flex-1">
                <span class="d-block balance-name">{{__('exchange.demo_account')}}</span>

                <span class="demo-balance">${{number_format($BalanceTrade['demo'],2)}}</span>
              </div>
              <div class="col-span-1 flex  flex-1 justify-end">
                
                <button class="btn button btn-ig-1" onclick="refeshDemo()"> 
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>
            </a>
          </li>
        </ul>
      </li>
      <li class="dropdown btn-deposit-acount" data-menu="#deposit-menu">
        <a href="javascript:void(0)" title="Account" class="dropdown-toggle my--subaccount" >
          <span>
            <span class="d-block ">{{__('exchange.deposit_fast')}}</span>
          </span>
        </a>
        <ul class="dropdown-menu fast-deposit" id="deposit-menu" role="menu">
          <form action="{{route('postDepositTrade')}}" method="POST">
            @csrf
            <div class="flex flex-col my-deposit">
              <div class="flex flex-col">
                <label for="" class=" white">{{__('exchange.from_main_balance')}}</label>
                <input class="form-control amount-input" readonly value="${{number_format($BalanceMain,2)}}" />
              </div>
              <div class="flex flex-col my-deposit">
                <label for="" class=" white">{{__('exchange.deposit_amount')}}</label>
                <input class="form-control amount-input" name="amount" require />
              </div>
              <div class="flex flex-col my-deposit">
                <span class="white">{{__('exchange.minimun_deposit', ['min' => 20])}}</span>
              </div>
              <div class="flex flex-col my-deposit">
                <span class="white">*{{__('exchange.total_receive_amount')}}</span>
                <span class="font-18 white"> $0.00</span>
              </div>
              <div class="flex flex-col my-deposit">
                <button class="button btn  btn-deposit-acount">
                  {{__('exchange.deposit_now')}}
                </button>
                <span class="white">*{{__('exchange.subject_to_change')}}</span>
              </div>

            </div>
          </form>
        </ul>
      </li>

    <li class="dropdown btn-new btn-setting hidden md:flex"  data-menu="#language-menu">
         <a href="javascript:void(0)" title="Account" class="dropdown-toggle my--subaccount" >
          <span>
            <span class=" flex flex-col justify-center items-center self-center"><i class="fas fa-language"></i>
            <span>{{session('language') ?? 'en'}}</span>  
            </span>
          </span>
        </a>
          <ul class="dropdown-menu flex items-center self-center" id="language-menu"  role="menu" style="width: 20px;">
             <div class="justify-between items-center" style="padding-left: 10px">
               @foreach($language as $key => $value)
                 <a class="font-bold text-2xl" href="{{route('changeLanguage', ['language' => $value->key])}}">{{$value->language}}</a><br>
               @endforeach
            </div>
          </ul>
        </li>
      	
      <li class="dropdown btn-profile hidden md:block">
        <a href="{{route('getProfile')}}" title="Account" class=" my--subaccount" >
          <span>
            <span class=" flex flex-col justify-center items-center self-center">
             <i class="fas fa-user-shield"></i>
              <span>{{__('exchange.profile')}}</span>
            </span>
          </span>
        </a>
      </li>
       <li class="visible-1200">
        <a href="javascript:void(0)" class="btn-navbar" data-toggle="collapse" data-target=".sidebar" title="">
          <i class="fa fa-bars text-white"></i>
        </a>
      </li>
      @if(Route::currentRouteNamed( 'getExchange'))
         <li class="dropdown btn-new btn-setting"  data-menu="#setting-menu">
         <a href="javascript:void(0)" title="Account" class="dropdown-toggle my--subaccount" >
          <span>
            <span class=" flex flex-col justify-center items-center self-center"><i class="fas fa-sliders-h"></i>
            <span>{{__('exchange.setting')}}</span>  
            </span>
          </span>
        </a>
          <ul class="dropdown-menu flex items-center self-center" id="setting-menu"  role="menu" style="width: 205px;">
             <div class="flex justify-between items-center">
               <span class="flex">
                 <span class="font-bold text-2xl" >{{__('exchange.sound_effect')}}</span>
             
                 </span>
               <div class="flex">
                  <button class="flex justify-center btn-on self-center items-center" onclick="setSound('on')">
                     <i class="fas fa-volume-up "></i>
                </button>
                 <button class="flex justify-center btn-off  self-center items-center mr-2" onclick="setSound('off')">
                  <i class="fas fa-volume-mute"></i>
                </button>
               </div>
            </div>
          </ul>
        </li>
   
       <div class="close-history">x</div>
        <li class="dropdown btn-new " id="history-trads" data-menu="history-menu">
          
          <a href="javascript:void(0)" title="Account" class="dropdown-toggle my--subaccount" >
            <span>
              <span class="flex flex-col justify-center items-center self-center history">
            <i class="fas fa-history"></i>
                <span>{{__('exchange.history')}}</span>
              </span>
            </span>
          </a>
          <ul class="dropdown-menu " id="history-menu" role="menu">
            <header>
              <ul class="nav nav-tabs nav-justified flex">
                <li class="active flex-1">
                  <a href="#open-h" data-toggle="tab" class="text-white flex justify-center type history2" style="width:100%;justify-content: center;">{{__('exchange.open')}}</a>
                </li>
                <li class=" flex-1">
                  <a href="#close-h" data-toggle="tab" class="text-white flex justify-center type history2" style="width:100%;justify-content: center;">{{__('exchange.close')}}</a>
                </li>

              </ul>
            </header>
            <div class="body tab-content">
              <div id="open-h" class="tab-pane active clearfix">
                  <ul >
                    <li role="presentation" class="account-picture history-trades">
                      
                    </li>
                    
                  </ul>
              </div>
              <div id="close-h" class="tab-pane  clearfix">
                  <ul >
                    <li role="presentation" class="account-picture history-trades">
                      
                    </li>
                  
                  </ul>
              </div>
            </div>
          </ul>
        </li>
      @endif
      
    
    </ul>
  </div>
</header>