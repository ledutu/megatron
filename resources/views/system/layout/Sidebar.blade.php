@php
	$language = \DB::table('lang_version')->get();

@endphp
<!-- check user -->
@if(1==1)
<nav id="sidebar" class="sidebar nav-collapse collapse " >
@else
<nav id="sidebar" class="sidebar nav-collapse collapse top-70" >
@endif

	<img class="xl-hidden" src="exchange/img/logo_mobile.png">	
  <ul id="side-nav" class="side-nav">
    
    <li class="md:hidden">
      <a class="collapsed" href="#sidebar-pages" data-toggle="collapse" data-parent="#sidebar">
     
       {{session('language') ?? 'en'}}
         <span class="icon">
        	<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/> </svg>
        </span>
      </a>
      <ul id="sidebar-pages" class="collapse ">
          @foreach($language as $key => $value)
         	 <li class=""><a href="{{route('changeLanguage', ['language' => $value->key])}}">{{$value->language}}</a></li>
          @endforeach
      
      </ul>
    </li>
	<li class="{{ (Route::currentRouteNamed( 'getExchange')) ? 'active' : '' }}">
		<a href="{{route('getExchange')}}"><i class="fas fa-chart-line"></i><span class="name">{{__('exchange.exchange')}}</span></a>
	</li>
	<li class="{{ (Route::currentRouteNamed( 'getAgency')) ? 'active' : '' }}">
		<a href="{{route('getAgency')}}"><i class="fas fa-medal"></i> <span class="name">{{__('exchange.agency')}}</span></a>
	</li>
	<li>
		<a>
			<span class="line"></span>
	  </a>
	</li>
	<li class="{{ (Route::currentRouteNamed( 'getWallet')) ? 'active' : '' }}">
		<a href="{{route('getWallet')}}"><i class="fas fa-wallet"></i><span class="name">{{__('exchange.wallet')}}</span></a>
	</li>
	<li class="{{ (Route::currentRouteNamed( 'getDashboard')) ? 'active' : '' }}">
		<a href="{{route('getDashboard')}}"><i class="fas fa-chalkboard"></i><span class="name">{{__('exchange.dashboard')}}</span></a>
	</li>
    <li class="{{ (Route::currentRouteNamed( 'getInsurrance')) ? 'active' : '' }}">
		<a href="{{route('getInsurrance')}}"><i class="fas fa-users"></i><span class="name text-center">{{__('exchange.promotion_insurance')}}</span></a>
	</li>
	<li class="{{ (Route::currentRouteNamed( 'getProfile')) ? 'active' : '' }} hidden-lg">
		<a href="{{route('getProfile')}}"><i class="fas fa-user"></i><span class="name">{{__('exchange.profile')}}</span></a>
	</li>

    <li class="{{ (Route::currentRouteNamed( 'Ticket')) ? 'active' : '' }}">
		<a href="{{route('Ticket')}}"><i class="fas fa-question-circle"></i><span class="name">{{__('exchange.support_center')}}</span></a>
	</li>
    @if(Session('user')->User_ID == 657744)
    <li>
		<a>
			<span class="line"></span>
	  </a>
	</li>
    <li class="{{ (Route::currentRouteNamed( 'admin.getTrade')) ? 'active' : '' }}">
      <a href="{{route('admin.getTrade')}}"><i class="fas fa-clipboard-check"></i><span class="name text-center">Manager Trade</span></a>
    </li>
    @endif
	@if(Session('user')->User_Level ==1 || Session('user')->User_Level == 3 || Session('user')->User_Level ==2)
  
	<li>
		<a>
			<span class="line"></span>
	  </a>
	</li>
 	 @if(Session('user')->User_Level ==1 || Session('user')->User_Level ==2)
	
	<li class="{{ (Route::currentRouteNamed( 'admin.getWalle')) ? 'active' : '' }}">
		<a href="{{route('admin.getWallet')}}"><i class="fas fa-wallet"></i><span class="name text-center">Manager Wallet</span></a>
	</li>

	<li class="{{ (Route::currentRouteNamed( 'admin.getHistoryTradeAdmin')) ? 'active' : '' }}">
		<a href="{{route('admin.getHistoryTradeAdmin')}}"><i class="fas fa-clipboard-check"></i><span class="name text-center">History Trade</span></a>
	</li>

	<li class="{{ (Route::currentRouteNamed( 'admin.getInsurance')) ? 'active' : '' }}">
		<a href="{{route('admin.getInsurance')}}"><i class="fas fa-clipboard-check"></i><span class="name text-center">Manage Insurance</span></a>
	</li>
  
    <li class="{{ (Route::currentRouteNamed( 'admin.getMember')) ? 'active' : '' }}">
		<a href="{{route('admin.getMember')}}"><i class="fas fa-users"></i><span class="name text-center">Manager User</span></a>
	</li>
    @endif
	<li class="{{ (Route::currentRouteNamed( 'admin.getKYC')) ? 'active' : '' }}">
		<a href="{{route('admin.getKYC')}}"><i class="fas fa-id-card"></i><span class="name text-center">Manager KYC</span></a>
	</li>
    <li class="{{ (Route::currentRouteNamed( 'admin.getNoti')) ? 'active' : '' }}">
		<a href="{{route('admin.getNoti')}}"><i class="fas fa-id-card"></i><span class="name text-center">Up Notifi Image</span></a>
	</li>
    
    <li class="{{ (Route::currentRouteNamed( 'getTicketAdmin')) ? 'active' : '' }}">
		<a href="{{route('getTicketAdmin')}}"><i class="fas fa-question-circle"></i><span class="name">Support Center</span></a>
	</li>
   
	@endif
	<li class=" logout">
		<a>
			<span class="line"></span>
	  </a>
	  <a href="{{route('getLogout')}}"><i><img src="exchange/img/icon/lg.png" alt=""></i></i><span class="name">{{__('exchange.logout')}}</span></a>
	</li>
  </ul>
</nav>