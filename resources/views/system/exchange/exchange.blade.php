@extends('system.layout.Master')
@section('css')
<style>

  body .wrap.exchange-dashboard {
    background-image: url(exchange/img/bg/bg_1.jpg?v=1)!important;
  }
    
  .btn-on {
    border: solid 1px #F4EB25 !important;
    color: #fff;
    font-size: larger;
    font-weight: 700;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    align-self: center;
    margin-left:5px;
    padding:3px;
    border-radius:5px;
  }

  .btn-off {
    color: #fff;
    font-size: larger;
    font-weight: 700;
    border: solid 1px #ed3935 !important;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    align-self: center;
    margin-left:5px;
    padding:3px;
    border-radius:5px;
  }
   .card-dashboard-mini {
    height: 100%;
    background: rgb(35 31 32 / 0.6);
    border: 1px solid #FFF200;
    width: 100%;
    border-radius: 5px;
    min-height: 150px;
    padding: 0px;
    max-height: 240px;
  }
	@media(min-width:1280px){
      .order-ls{
        display:none!important;
      }
    }
  @media(max-width:1024px){
    .card-dashboard-mini table >tbody>tr>td{
      font-size:11px;
      
    }
     .card-dashboard-mini table >thead>tr>th{
      font-size:12px;
      
    }
  }
  #setting-menu{
    padding-left:10px;
    padding-right:5px;
  }
  #language-menu{
  	min-width: 90px;
  }
  @media(max-width:767px){
  	.widget-tabs .nav>li a{
      font-size: 12px;
    }
    .page-header .navbar .nav>li>.dropdown-menu.pull-right, .page-header .navbar .pull-right>li>.dropdown-menu{
      left: 0;
    }
    #setting-menu,#language-menu{
      left: auto!important;
      right: 0;
      
    }
    .widget>header{
      max-height: 40px;
    }
    .analysic {
        height: auto;
    }
  }
</style>


@endsection
@section('content')

<div class="grid grid-cols-12 gap-4">
  <div class="xl:col-span-10 lg:col-span-9 xs:col-span-12 md:col-span-12 col-span-12 p-0 m-0 ">
    <section class="widget widget-chart p-0">
      <div id="chart-panel">
        <div class="sell-div  ">
          <p class="animate__bounceIn">{{__('exchange.sell')}}</p>
          <span class="animate__bounceIn"></span>
        </div>
        <div class="buy-div  ">
          <p class="animate__bounceIn">{{__('exchange.buy')}}</p>
          <span class="animate__bounceIn"></span>
        </div>
        <div class="profit-div  ">
          <p class="animate__bounceIn">{{__('exchange.profit')}}</p>
          <span class="animate__bounceIn"></span>
        </div>
        <div class="win-div  ">
          <p class="animate__bounceIn">{{__('exchange.win')}}</p>
          <span class="animate__bounceIn"></span>
        </div>
        <div class="lose-div  ">
          <p class="animate__bounceIn">{{__('exchange.lose')}}</p>
          <span class="animate__bounceIn"></span>
        </div>
        <div id="MainCharts" style="height:100%;width: 100%;"></div>
      </div>
    </section>
    <section
      class="widget widget-tabs xl:col-span-12 lg:col-span-12 md:col-span-12  sm:col-span-12 col-span-12 row-start-1 xl:row-start-1	grid grid-cols-1 gap-4 analysic">
   
        <header>
          <ul class="nav nav-tabs nav-justified tab-de">
            <li class="active flex-1">
              <a href="#Indicators" data-toggle="tab" class="text-white">{{__('exchange.indicators')}}</a>
            </li>
            <li class=" flex-1 resul">
              <a href="#Results" data-toggle="tab" class="text-white">{{__('exchange.last_results')}}</a>
            </li>
            <li class="flex-1 order-ls">
              <a href="#order-ls" data-toggle="tab" class="text-white xl:hidden">{{__('exchange.order_list')}}</a>
            </li>
          </ul>
        </header>
        <div class="body tab-content">
          <div id="Indicators" class="tab-pane active clearfix " style="height: 100%;">
            <div class="grid grid-cols-12 gap-4 self-center items-center" style="height: 100%;">
            <div class="col-span-12 lg:col-span-2"></div>
              <div class="col-span-12 lg:col-span-8 grid grid-cols-12 self-center justify-center items-center bg-transparent">
                <div class="col-span-4 relative">
                  <div class="title-gua">
                    {{__('exchange.oscillators')}}
                  </div>
                  <div class="min-gua " id="gua_1"></div>
                  <div class="label-detail flex justify-center self-center gua-detail-div qua_1">
                    <span span class="text-sell">
                      <span>{{__('exchange.sell')}}</span>
                      <span class="value-sell"> 0</span>
                    </span>

                    <span class="text-buy">
                      <span>{{__('exchange.buy')}}</span>
                      <span class="value-buy"> 0</span>
                    </span>
                  </div>
                </div>
                <div class="col-span-4 relative">
                  <div class="title-gua">
                    {{__('exchange.moving_averages')}}
                  </div>
                  <div class="min-gua " id="gua_3"></div>
                  <div class="label-detail flex justify-center self-center gua-detail-div qua_3">
                    <span span class="text-sell">
                      <span>{{__('exchange.sell')}}</span>
                      <span class="value-sell"> 0</span>
                    </span>

                    <span class="text-buy">
                      <span>{{__('exchange.buy')}}</span>
                      <span class="value-buy"> 0</span>
                    </span>
                  </div>
                </div>
                <div class="col-span-4 relative">
                  <div class="title-gua">
                    {{__('exchange.summary')}}
                  </div>
                  <div class="min-gua " id="gua_2"></div>
                  <div class="label-detail flex justify-center self-center gua-detail-div qua_2">
                    <span span class="text-sell">
                      <span>{{__('exchange.sell')}}</span>
                      <span class="value-sell"> 0</span>
                    </span>

                    <span class="text-buy">
                      <span>{{__('exchange.buy')}}</span>
                      <span class="value-buy"> 0</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div id="Results" class="tab-pane  ">
            <div class="grid grid-cols-12">
              <div class="col-span-12 xl:col-span-7 grid grid-cols-12">
                <div
                  class=" water-circle xl:col-span-6 col-span-8  grid grid-cols-11 lg:my-10 col-span-1 lg:mb-5 gap-0 justify-center"
                  id="Statistical">
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>         
                  <div class="col-span-1"></div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                  <div class="col-span-1 grid grid-cols-1 gap-2">
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                    <span class="col-span-1"></span>
                  </div>
                </div>
                <div class="xl:col-span-6 col-span-4 flex flex-col items-center self-center">
                  <span class=" btn button-buysell up-water" 
                        style="
                        text-align: center!important;
                        font-size: 21px;
                        font-weight: 700;
                        background: #6abd45; "
                        >
                    10 UP
                  </span>
                  <span class="btn button-buysell  down-water"
                        style="
                        text-align: center!important;
                        font-size: 21px;
                        font-weight: 700;
                        background: #ed2224; "
                        >
                    10 DOWN 
                  </span>
                </div>
              </div>
              <div class="col-span-12 xl:col-span-5 hidden xl:block">
                <div class="table-responsive card-dashboard-mini">
                  <table class="table table-hover" id="order-ds">
                    <thead>
                      <tr>
                        <th>{{__('agency.user_id')}}</th>
                        <th>{{__('exchange.coins')}}</th>
                        <th>{{__('exchange.order')}}</th>
                        <th>{{__('exchange.amount')}}</th>
                        <th>{{__('exchange.time')}}</th>  
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div id="order-ls" class="tab-pane  ">
            <div class=" grid grid-cols-12">
              <div class="table-responsive card-dashboard-mini col-span-12">
                <table class="table table-hover" id="order-mb">
                  <thead>
                    <tr>
                      <th>{{__('exchange.coins')}}</th>
                      <th>{{__('exchange.order')}}</th>
                      <th>{{__('exchange.amount')}}</th>
                      <th>{{__('exchange.time')}}</th>  
                      <th>{{__('exchange.live')}}</th>
                    </tr>
                  </thead>
     			 <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
     
      

    </section>
  </div>
  <div class="xl:col-span-2 lg:col-span-3  xs:col-span-12 md:col-span-12  col-span-12 ">
    <div class="right-global flex  flex-col gap-4 widget-left px-3">
      <div class="col-span-12 mt-5 lg:block flex">
        <label for="" class="hidden lg:block">{{__('exchange.amount')}}:</label>
        <button class="btn btn-updown hidden-lg" onclick="selectAmount(-5)"><i class="fas fa-minus"></i></button>
        <input id="amount" type="number" class="form-control amount-input flex-1" step="5" name="amount" />
        <button class="btn btn-updown hidden-lg" onclick="selectAmount(5)"><i class="fas fa-plus"></i> </button>
      </div>
      <div class=" col-span-12  pl-2 pr-2 hidden lg:block">
        <div class="price-button">
          <button class="button-price" onclick="selectAmount(5)">+5</button>
          <button class="button-price" onclick="selectAmount(10)">+10</button>
          <button class="button-price" onclick="selectAmount(20)">+20</button>
          <button class="button-price" onclick="selectAmount(50)">+50</button>
          <button class="button-price last" onclick="selectAmount(100)">+100</button>
          <button class="button-price last" onclick="selectAmount('all')">All</button>
        </div>

      </div>
      <div class="col-span-12 grid col-span-1 profit-show">
        <label for="">{{__('exchange.profit')}}</label>
        <span class="profit-1">95% <span class="profit-2" id="amount_2">+$0</span></span>
      </div>
      <div class="col-span-12  col-span-1 hidden lg:grid">
        <label for="">{{__('exchange.traders_sentiments')}}</label>
        <div class="flex relative mb-10">

          <span class="buy-progress"></span>
          <span class="sell-progress"></span>
          <span class="buy-percen">55%</span>
          <span class="sell-percen">45%</span>
        </div>
      </div>
      <div class="col-span-12 ">
        <div class="buysell-button">
          <div class="btn-group">
            <button class=" btn button-buysell btn-buy bet" type="button" data-type="buy">
              <span>{{__('exchange.buy')}}</span>
            </button>
            <div class="count-down">
              <span>
                <small class="font-1 timeText"></small>
                <p class="main-count m-0">
                  00</p>
              </span>
            </div>
            <button class="btn button-buysell btn-sell bet" type="button" data-type="sell">
              <span>{{__('exchange.sell')}}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection
@section('scripts')
<script src="exchange/js/echarts.min.js?v=3"></script>
<!--<script src="exchange/js/index.js"></script>-->
<script type="text/javascript" src="https://code.createjs.com/1.0.0/soundjs.min.js"></script>

<script src="exchange/js/colyseus.js"></script>
<script>
  var base_url = 'https://igtrade.co';
  var host = window.document.location.host.replace(/:.*/, '');
  var client = new Colyseus.Client('wss://socket.igtrade.co/');
  var user = { subID: {{ $subID }}, token: '{{$token}}', currency: {{$currency}}, level:{{$user->User_Level }}, email:'{{ $user->User_Email }}' };
  var statistical = {!! json_encode($historyGame)!!};
  @if (count($betArray))
    var _betList = {!! json_encode($betArray)!!};
  @else
  var _betList = {};
  @endif
  $(document).ready(function () {
    _coin = window.location.search;
    if (_coin == '?coin=gold') {
      $('.img_coin').html('<img src="exchange/img/gold.png">');
    } else {
      $('.img_coin').html('<img src="exchange/img/eusd.png">');
    }
  });
  $('.select_coin').click(function () {
    var _name = $(this).data('name');
    window.location.search = 'coin=' + _name + '';
  });
  function loadSound() {
	
	var audioPath = "exchange/sound/";
	var sounds = [
		{id:"TS", src:"tenSecond.mp3"},
      	{id:"Tick", src:"ting.mp3"},
        {id:"Win", src:"win.mp3"},
        {id:"Lose", src:"lose.mp3"},
	
	];

		createjs.Sound.addEventListener("fileload", playSound);
      	createjs.Sound.registerSounds(sounds, audioPath);
  }
  function playSound(id){
     var soundOnOff = localStorage.getItem('soundEffect');
    	if(!soundOnOff){
          soundOnOff = 'on';
        }
    
        if(soundOnOff && soundOnOff == 'on'){
           createjs.Sound.play(id);
        }



	}
  
  var buyLang = '{{__("exchange.buy")}}'
  var sellLange = '{{__("exchange.sell")}}'
  var orderLange = '{{__("exchange.order")}}'
  var waitingLange = '{{__("exchange.waiting")}}'
  var amountLange = '{{__("exchange.amount")}}'
  var profitLange = '{{__("exchange.profit")}}'
  var timeLange = '{{__("exchange.time")}}'
  
  var strong_buy = '{{__("exchange.strong_buy")}}'
  var strong_sell = '{{__("exchange.strong_sell")}}'
  var neutral = '{{__("exchange.neutral")}}'
  
</script>

<script language="javascript">

window.onload = function(e){ 
   loadSound();
  
  var getSoundStatus = localStorage.getItem('soundEffect');
  	if(getSoundStatus){
       setBTNSOUND(getSoundStatus)
    }
    else{
       setBTNSOUND('on');
    }
}
</script>

<script src="exchange/js/convertEchart.js?v=<?=time()?>"></script>

<script src="exchange/js/newchart.js?v=<?=time()?>"></script>



<script src="exchange/js/cloneecharts-config.js?v=<?=time()?>"></script>

<!-- <script src="exchange/js/echarts-config.js?v=<?=time()?>"></script> -->




@endsection