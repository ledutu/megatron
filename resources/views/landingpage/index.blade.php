<!DOCTYPE html>
@php
  $user = Session('user');
	$language = \DB::table('lang_version')->get();

@endphp
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>
   IG Trade
  </title>
  <base href="{{asset('/landingpage')}}/">
  <link rel="apple-touch-icon" sizes="57x57" href="assets/global/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="assets/global/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="assets/global/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/global/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="assets/global/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="assets/global/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="assets/global/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="assets/global/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/global/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="assets/global/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/global/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="assets/global/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/global/favicon-16x16.png">

  <meta name="msapplication-TileImage" content="assets/global/ms-icon-144x144.png">


  <meta name="description" content="" />
  <meta name="keywords" content="" />
  <meta name="author" content="" />
  <link rel="stylesheet" href="assets/css/tailwind.min.css" />
  <link rel="stylesheet" href="assets/css/style.css?v=2" />
  <link rel="stylesheet" href="assets/css/carousel_sc2.css">
  <!--Replace with your tailwind.css once created-->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
  <link rel="stylesheet" href="assets/css/fonts/font-awesome/css/all.css">
  <!-- Define your gradient here - use online tools to find a gradient matching your branding-->
  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.css">
  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.theme.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.transitions.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
  <!-- Start Alexa Certify Javascript -->
<script type="text/javascript">
_atrk_opts = { atrk_acct:"vaigt1zDGU20kU", domain:"igtrade.co",dynamic: true};
(function() { var as = document.createElement('script'); as.type = 'text/javascript'; as.async = true; as.src = "https://certify-js.alexametrics.com/atrk.js"; var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(as, s); })();
</script>
<noscript><img src="https://certify.alexametrics.com/atrk.gif?account=vaigt1zDGU20kU" style="display:none" height="1" width="1" alt="" /></noscript>
<!-- End Alexa Certify Javascript -->
<script>
  (function(d, s, id, t) {
    if (d.getElementById(id)) return;
    var js, fjs = d.getElementsByTagName(s)[0];
    js = d.createElement(s);
    js.id = id;
    js.src = 'https://widget.oncustomer.asia/js/index.js?lang=en&token=' + t;
    fjs.parentNode.insertBefore(js, fjs);}
  (document, 'script', 'oc-chat-widget-bootstrap', '60c5a4e5a4e6d4942392a5779a1fb454'));
</script>
  
  <style>
    .blocker{
      z-index:1090;
      padding: 0!important;
    }
    .modal a.close-modal{
       
        right: 0!important;
        margin: auto!important;
        top: 0!important;
    }
    #notifi  .owl-carousel .owl-item,
    #notifi .owl-carousel .owl-wrapper{
          width: 100%!important;
    	padding: 5px 5px;
    }
    #notifi{
          padding: 0!important;
      	width:100%!important;
    }
    #header .dropdown:hover .dropdown-menu {
  display: block;
}
  </style>
</head>

<body class="leading-normal tracking-normal text-white relative" style="font-family: 'Source Sans Pro', sans-serif;">
  <!--Nav-->
  <nav id="header" class=" fixed flex items-center self-center w-full z-30 top-0 text-white">
    <div class="w-full flex  items-center justify-between self-center mt-0 py-2">
      <div class="logo">
        <img src="assets/img/logo.png" alt="">
      </div>
      <div class="menu flex justify-center self-center items-center">
        <div class="z-20">
          <a class="btn-auth-2" href="{{route('getLogin')}}">
            <span>{{__('landing_page.Sign in')}}</span>
          </a>
        </div>
        <div class="z-20">
          <a class="btn-auth-1" href="{{route('getRegister')}}">
            <span> {{__('landing_page.Sign up')}}</span>
          </a>
        </div>
        <div class="dropdown inline-block relative">
          <button class="bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
            <span class="mr-1">{{session('language') ?? 'en'}}</span>
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/> </svg>
          </button>
         @if(request()->input('test') == 1)
         @endif
          <ul class="dropdown-menu absolute hidden text-gray-700 pt-1">
            @foreach($language as $key => $value)
               <li class=""><a class="rounded-t bg-gray-200 hover:bg-gray-400 py-2 px-4 block whitespace-no-wrap"  href="{{route('changeLanguage', ['language' => $value->key])}}">{{$value->language}}</a></li>
             @endforeach
          </ul>
        </div>
      </div>
      

    </div>

  </nav>
  <!--Hero-->
  <section id="section-1" class="flex self-center items-center justify-center">
    <div class="container mx-auto  h-full ">
      <div class="grid grid-cols-12 gap-6 self-center items-center justify-center h-full">
        <div class="col-span-12 lg:col-span-5 flex flex-col items-center">
          <div class="title-sc1 text-white font-bold text-8xl">IG Trade </div>
          <div class="sub-title-sc1 text-white font-500  text-2xl mt-4 text-center">{{__('landing_page.PERFECT TRADING PLATFORM')}}
            <br> {{__('landing_page.Give you a prosperous life')}}
          </div>
          <a href="{{route('getRegister')}}" class="btn button btn-auth-1 font-bold text-2xl uppercase lg:mt-7 mt-5 px-10 py-3">{{__('landing_page.Trade now')}}</a>
        </div>
        <div class="col-span-12 lg:col-span-7 hidden lg:block ">
          <img src="assets/img/mockup/sc1.png?v=1" alt="">
        </div>
      </div>
    </div>
  </section>
  <section id="section-2" class="flex self-center items-center justify-center">
    <div class="container  mx-auto  h-full ">
      <div class="grid grid-cols-5 gap-6">
        
        <div class="col-span-5 lg:col-span-2  self-center justify-center items-center pt-20">
            <div class="card-video">
              <div class="video-container">
                <div class="yt-video" id="player"></div>
              </div>
              <div class="text-container grid grid-cols-1 gap-4 px-4 pb-5 pt-1">
                <div class="title-footer text-white text-2xl font-700 text-left">
                  {{__('landing_page.IG TRADE – COMPREHENSIVE TRADING PLATFORM')}}
                </div>
                <div class="sub-title-footer text-white text-lg font-500 text-justify">
                  {{__('landing_page.IG Trade is a financial trading platform that allows traders to earn profit by predicting the rate of cryptocurrency which will increase or reduce at a certain period of time.')}}
                </div>
              </div>
            </div>
        </div>
        <div class="col-span-5 lg:col-span-3 grid grid-cols-1">
          <div class="col-span-1 self-center justify-center items-center">
            <h2 class="text-center text-white font-600 text-4xl">{{__('landing_page.SOME SPECIAL FEATURES')}}</h2>
          </div>
          <div class="col-span-1  services">
         
            <div class="circle--slider">
              <div class="rotate--circle">
                <ul class="circle--rotate" id="circle--rotate">
                  <li class="block">
                    <div class="icon flex justify-center items-center self-center"><i class="fas fa-rocket-launch"></i><span>
                      </span></div>
                  </li>
                  <li class="block">
                    <div class="icon flex justify-center items-center self-center"><i class="far fa-analytics"></i><span>
                      </span></div>
                  </li>
                  <li class="block">
                    <div class="icon flex justify-center items-center self-center"><i class="far fa-university"></i><span>
                      </span></div>
                  </li>
  
                </ul>
                <div class="animate-wrapper">
                  <div class="animate">
                    <div class="animate-img">
                      <div class="animate-img__in" data-image="assets/img/mockup/sc2_1.png">
                        <div class="animate-more">
                          <div class="animate-title">
                            <div class="title-content capitalize text-center font-medium text-3xl">{{__('landing_page.Friendly interface')}}
                            </div>
                            <div class="content-content capitalize font-600 text-xl text-center px-4 py-3">{{__('landing_page.Simple operation helps you to learn and earn Money quickly')}}.</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="animate">
                    <div class="animate-img">
                      <div class="animate-img__in" data-image="assets/img/mockup/sc2_2.png">
                        <div class="animate-more">
                          <div class="animate-title">
                            <div class="title-content capitalize  text-center font-medium text-3xl">{{__('landing_page.Try it out on demo account')}}</div>
                            <div class="content-content capitalize font-600 text-xl text-center px-4 py-3">
                              {{__('landing_page.Inproving trading skill and earn money after complete forming your trading strategy')}}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="animate">
                    <div class="animate-img">
                      <div class="animate-img__in" data-image="assets/img/mockup/sc2_3.png">
                        <div class="animate-more">
                          <div class="animate-title">
                            <div class="title-content capitalize  text-center font-medium text-3xl">{{__('landing_page.Quickly liquidation of many cryptocurrencies')}}</div>
                            <div class="content-content font-600 capitalize text-xl text-center px-4 py-3">
                           	 {{__('landing_page.Quick deposit and withdraw by many means: ETH, BTC and many different cryptocurrencies')}}.
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- <div class="prev"><span>PREV</span></div>
                  <div class="next"><span>NEXT</span></div> -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section id="section-3" class="flex self-center items-center justify-center">
    <div class="container  mx-auto  h-full ">
      <div class="grid grid-cols-12 gap-6 self-center items-center">
        <div class="col-span-12 xl:col-span-5  px-5 lg:px-auto">
          <div class="font-bold md:text-5xl text-center xl:text-left text-3xl text-white capitalize">{{__('landing_page.IG TRADE – SOLUTIONS FOR TRADERS')}}</div>
          <div class="font-500 text-2xl text-center xl:text-left text-white capitalize mt-3">{{__('landing_page.Join With Us And Become A BEST TRADER Now! You Will Make Predictions About The Price’s Trend Of Asset. Whether The Price Goes Up And Down, You Still Will Have A Chance To Earn Up To 95% Of Your Amount.')}}</div>
          <a class="btn button btn-auth-1 mx-auto xl:mx-0  font-bold text-2xl uppercase lg:mt-7 mt-5 px-10 py-3" href="{{route('getRegister')}}">{{__('landing_page.Trade now')}}</a>
        </div>
        <div class="col-span-12 xl:col-span-7 gap-x-0 gap-y-12 grid grid-cols-2">
          <div class="col-span-2 sm:col-span-1">
            <div class="card-sc3">
              <img src="assets/img/mockup/sc3_1.png" alt="" srcset="">
              <div class="font-bold text-3xl uppercase text-white mt-3">
                {{__('landing_page.THE WORLD\'S MOST TRANSPARENCY PLATFORM')}}
              </div>
            </div>
          </div>
          <div class="col-span-2 sm:col-span-1">
             <div class="card-sc3">
               <img src="assets/img/mockup/sc3_2.png" alt="" srcset="">
               <div class="font-bold text-3xl uppercase text-white mt-3">
                {{__('landing_page.THE PLATFORM IS NOT AFFECTED BY THE MARKET')}}
               </div>
             </div>
          </div>
          <div class="col-span-2 sm:col-span-1">
             <div class="card-sc3">
               <img src="assets/img/mockup/sc3_3.png" alt="" srcset="">
               <div class="font-bold text-3xl uppercase text-white mt-3">
                {{__('landing_page.QUICK AND SECURE LIQUIDITY')}}
               </div>
             </div>
          </div>
          <div class="col-span-2 sm:col-span-1">
             <div class="card-sc3">
               <img src="assets/img/mockup/sc3_4.png" alt="" srcset="">
               <div class="font-bold text-3xl uppercase text-white mt-3">
                {{__('landing_page.SYNC ALL YOUR DEVICES')}}
               </div>
             </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section id="section-4" class="flex self-center items-center justify-center">
    <div class="container mx-auto  h-full  ">
      <div class="grid grid-cols-1 gap-6">
        <div class="col-span-1 self-center justify-center items-center">
          <h2 class="text-center text-white font-600 text-4xl mb-10">{{__('landing_page.TYPE OF CRYPTOCURENCY THAT WE ACCEPT')}} </h2>
        </div>
        <div class="col-span-1">
          <div class="swiper-container">
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <img src="assets/img/mockup/USDT.png" alt="" srcset="">
              </div>
              <div class="swiper-slide">
                <img src="assets/img/mockup/BTC.png" alt="" srcset="">
              </div>
              <div class="swiper-slide">
                <img src="assets/img/mockup/ETH.png" alt="" srcset="">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section id="section-5" class="flex self-center items-center justify-center">
    <div class="container mx-auto  h-full  ">
      <div class="grid grid-cols-1 gap-6">
        <div class="col-span-1 self-center justify-center items-center">
          <h2 class="text-center text-white font-600 text-4xl mb-10">{{__('landing_page.TRUSTED BY INVESTORS')}} </h2>
        </div>
        <div class="col-span-1">
          <div class="swiper-container">
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <div class="content-sc5 grid grid-cols-12 gap-6">
                  <div class="col-span-5 flex items-center sefl-center justify-start">
                    <img src="assets/img/mockup/Matt Johnson.jpg" alt="">
                  </div>
                  <div class="col-span-7 px-5 flex flex-col self-center items-center justify-start">
                    <div class="title-5 font-medium text-2xl text-white">Matt Johnson</div>
                    <div class="sc5-content capitalize">“{{__('landing_page.I love it! The interface is Friendly and easy to trade')}}”
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="content-sc5 grid grid-cols-12 gap-6">
                  <div class="col-span-5 flex items-center sefl-center justify-start">
                    <img src="assets/img/mockup/Ashish Khatri.jpg" alt="">
                  </div>
                  <div class="col-span-7 px-5  flex flex-col self-center items-center justify-start">
                    <div class="title-5 font-medium text-2xl text-white">Ashish Khatri
                    </div>
                    <div class="sc5-content capitalize">“{{__('landing_page.invest in a simple way with low cost')}}”
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="content-sc5 grid grid-cols-12 gap-6">
                  <div class="col-span-5 flex items-center sefl-center justify-start">
                    <img src="assets/img/mockup/Brian Murphy.jpg" alt="">
                  </div>
                  <div class="col-span-7 px-5  flex flex-col self-center items-center justify-start">
                    <div class="title-5 font-medium text-2xl text-white">Brian Murphy
                    </div>
                    <div class="sc5-content capitalize">“{{__('landing_page.I learned alot about financial investing from Ig trade')}}”
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="content-sc5 grid grid-cols-12 gap-6">
                  <div class="col-span-5 flex items-center sefl-center justify-start">
                    <img src="assets/img/mockup/Amanda Ricci.jpg" alt="">
                  </div>
                  <div class="col-span-7 px-5  flex flex-col self-center items-center justify-start">
                    <div class="title-5 font-medium text-2xl text-white">Amanda Ricci
                    </div>
                    <div class="sc5-content capitalize">“{{__('landing_page.Deposit and withdraw is simple and fast')}} ”
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!--Footer-->
  <footer id="footer" class="bg-white">
    <div class="container  mx-auto px-8">
      <div class="grid grid-cols-1 gap-8 py-10">
        <div class="grid grid-cols-7 gap-12 self-center justify-center">
          <div class="col-span-7 lg:col-span-3 grid grid-cols-1 gap-4 px-5">
            <div class="title-footer text-white text-2xl font-700 text-left">
              {{__('landing_page.IG TRADE – COMPREHENSIVE TRADING PLATFORM')}}
            </div>
            <div class="sub-title-footer text-white text-lg font-500 text-justify">
              {{__('landing_page.IG Trade is a financial trading platform that allows traders to earn profit by predicting the rate of cryptocurrency which will increase or reduce at a certain period of time.')}}
            </div>
          </div>
          <div class="col-span-7 sm:col-span-3 lg:col-span-2 px-3">
            <h2 class="font-700 text-3xl">{{__('landing_page.MENU')}}</h2>
            <ul>
              <li class="font-600 text-xl mb-2 underline">
                <a href="#section-2">{{__('landing_page.Special Features')}}</a>
              </li>
              <li class="font-600 text-xl mb-2 underline">
                <a href="#section-3">{{__('landing_page.Benefit Of IG Trading Platform')}} </a>
              </li>
              <li class="font-600 text-xl mb-2 underline">
                <a href="#section-4">{{__('landing_page.Type Of Cryptocurency')}}</a>
              </li>
              <li class="font-600 text-xl mb-2 underline">
                <a href="#section-5">{{__('landing_page.Review')}}</a>
              </li>
            </ul>
          </div>
          <div class="col-span-7 sm:col-span-3 lg:col-span-2 px-3">
            <h2 class="font-700 text-3xl">{{__('landing_page.Social')}}</h2>
            <ul>
              <li class="text-xl font-700 mb-2">
                <a href="https://t.me/igtradegroup"><i class="fab fa-telegram"></i> {{__('landing_page.Telegram Group')}}</a>
              </li>
              <!-- <li class="text-xl font-700 mb-2">
                <a href=""><i class="fab fa-telegram"></i> Telegram Gro</a>
              </li> -->
              <li class="text-xl font-700 mb-2">
                <a href="https://www.youtube.com/channel/UCwPcOyF4AKIMuVaoasZQ1Hw/?guided_help_flow=5"><i class="fab fa-youtube"></i> {{__('landing_page.YouTube Channel')}}</a>
              </li>
              <li class="text-xl font-700 mb-2">
                <a href="https://www.facebook.com/IG-Trade-102719088331757"><i class="fab fa-facebook"></i> {{__('landing_page.FaceBook FanPage')}}</a>
              </li>
            </ul>
          </div>
        </div>
        <div class="grid grid-cols-12 gap-6 aboutus ">
          <div class="col-span-12 lg:col-span-2"></div>
          <div class="col-span-12 lg:col-span-10">
            <div class="warning-footer text-base font-500 text-white mt-2">
             	{{__('landing_page.Risk Warning: Trading and investing in digital options involves significant level of risk and is not suitable and/or appropriate for all clients. Please make sure you carefully consider your investment objectives, level of experience and risk appetite before buying or selling any digital asset. You should be aware of and fully understand all the risks associated with trading and investing in digital assets, you should not invest funds you cannot afford to lose. You are granted limited non-exclusive rights to use the IP contained in this site for personal, non-commercial, non-transferable use only in relation to the services offered on the site.')}}
            </div>
          </div>
        </div>
      </div>
    </div>

  </footer>
   <div class="modal fade" id="notifi" role="dialog" style="z-index:9999">
    
      <div class="modal-content p-0" style="width:100%;border:0">


        <div id="testimonial-slider" class="owl-carousel" style="display:block">
          
           @foreach($noti_image as $v)
          <div class="testimonial">
            <img src="https://media.igtrade.co/{{$v->image}}" alt="" srcset="" width="100%">
          </div>
          @endforeach 
        </div>
      </div>

   
  </div>
  
  <script src="assets/css/fonts/font-awesome/js/all.js"></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
  <script src="https://unpkg.com/swiper/swiper-bundle.js"></script>
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script type="text/javascript"
    src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.js"></script>
  <!-- circle carousel -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
  <script src="assets/js/carrousel_sc2.js"></script>
  <script src="assets/js/app.js?v={{time()}}"></script>
  <script>
  (function(d, s, id, t) {
    if (d.getElementById(id)) return;
    var js, fjs = d.getElementsByTagName(s)[0];
    js = d.createElement(s);
    js.id = id;
    js.src = 'https://widget.oncustomer.asia/js/index.js?lang=en&token=' + t;
    fjs.parentNode.insertBefore(js, fjs);}
  (document, 'script', 'oc-chat-widget-bootstrap', '60c5a4e5a4e6d4942392a5779a1fb454'));
</script>
    <script>

   
      // 2. This code loads the IFrame Player API code asynchronously.
      var tag = document.createElement('script');

      tag.src = "https://www.youtube.com/iframe_api";
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

      // 3. This function creates an <iframe> (and YouTube player)
      //    after the API code downloads.
      var player;
      function onYouTubeIframeAPIReady() {
        player = new YT.Player('player', {
          width: '100%',
    	height: '100%',
          videoId: '7nidn2xVfRk',
          events: {
            //'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
          },
           playerVars: {
            'autoplay': 0,
            'playsinline': 1,
            'controls': 1,
            'fs': 0,
            'iv_load_policy': 3,
            'rel': 0,
            'showinfo': 1,

          },

        });
      }

      // 4. The API will call this function when the video player is ready.
      function onPlayerReady(event) {
        event.target.playVideo();
      }

      // 5. The API calls this function when the player's state changes.
      //    The function indicates that when playing a video (state=1),
      //    the player should play for six seconds and then stop.
      var done = false;
      function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.PLAYING && !done) {
          setTimeout(stopVideo, 6000);
          done = true;
        }
      }
      function stopVideo() {
        player.stopVideo();
      }
    </script>
  <script type="text/javascript">
    
    $("#testimonial-slider").owlCarousel({
      items: 1,
      itemsDesktop: [1000, 1],
      itemsDesktopSmall: [979, 1],
      itemsTablet: [769, 1],
      pagination: true,
      transitionStyle: "backSlide",
      autoplay: true
    });
 
    @if($noti_image->count() > 0)
      $(document).ready(function () {
          $("#notifi").modal({
          escapeClose: true,
          clickClose: false,
          showClose: true
        });
      
      })
    @endif
  </script>
</body>

</html>