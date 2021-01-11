<!DOCTYPE html>
<html>
<head>
    <title>IG Trade</title>
  	<base href="{{asset('')}}">
  	<meta property="og:title" content="IG Trade">
    <meta property="og:url" content="http://igtrade.co/">
    <meta property="og:image" content="exchange/img/icon/logo_icon.png">
   	<meta name="viewport" content="width=device-width, minimum-scale=1.0">
    <link rel="shortcut icon" href="exchange/img/icon/logo_icon.png">

    
    @include('system.layout.Maincss')
  	<style>
  		.select_coin .demo-balance{
          font-size:16px;
          color:#fff;
          font-weight:500;
        }
      .loader-wrap {
          position: fixed;
          left: 0;
          right: 0;
          top: 0;
          bottom: 0;
          text-align: center;
          opacity: 1;
          -webkit-transition: opacity .2s ease-out;
          -o-transition: opacity .2s ease-out;
          transition: opacity .2s ease-out;
          display: flex;
          justify-content: center;
          align-items: center;
          align-self: center;
          align-content: center;
          background: #0000008f;
          margin: 0!important;
          z-index: 0;
      }
      #sidebar.collapse.in{
        height: auto!important;
      }
      .page-header .nav>li>a.my--subaccount span{
        font-size: 11px;
      }
      
  	</style>
    @yield('css')
</head>

<body class="exchane">
  @include('system.layout.Sidebar')
  <div class="wrap exchange-dashboard">
        @include('system.layout.Topbar')
      <div class="content container customer">
          <!-- <h2 class="page-title">BTCUSDT <small>Exchange</small></h2> -->
          @yield('content')
      </div>
      <div class="loader-wrap hiding hide">
          <i class="fas fa-circle-notch fa-spin" style="font-size: 45px;"></i>
      </div>
  </div>
  @include('system.layout.Mainscripts')
  @yield('scripts')
</body>

</html>