<script src="exchange/lib/jquery/dist/jquery.min.js"></script>
<script src="exchange/lib/bootstrap-sass/assets/javascripts/bootstrap.min.js"></script>
<script src="exchange/js/settings.js?v=10"></script>
<!--<script src="exchange/lib/slimScroll/jquery.slimscroll.min.js"></script>
<script src="exchange/lib/jquery.sparkline/index.js"></script> -->

<!-- <script src="exchange/lib/backbone/backbone.js"></script>
<script src="exchange/lib/backbone.localStorage/build/backbone.localStorage.min.js"></script>
<script src="exchange/js/app.js?v=1"></script>
<script src="exchange/lib/jquery-pjax/jquery.pjax.js"></script>
-->
<!-- <script src="exchange/lib/d3/d3.min.js"></script> -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/sweetalert2@9/dist/sweetalert2.min.js"></script>
<!--<script src="exchange/lib/nvd3/build/nv.d3.min.js"></script> -->
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.jqueryui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" integrity="sha512-F5QTlBqZlvuBEs9LQPqc1iZv2UMxcVXezbHzomzS6Df4MZMClge/8+gXrKw2fl5ysdk4rWjR0vKS7NNkfymaBQ==" crossorigin="anonymous"></script>
<script type="text/javascript">
  function refeshDemo() { 
    $.get('{{route("getRefreshDemo")}}', function (response) {
      $('.demo-balance').html(response.data.balance.demo);
    });
  }
  $(".nav.navbar-nav li.dropdown").on('click',function (e) {

    if( $(this).hasClass('open')){
      $(".nav.navbar-nav li.dropdown").removeClass('open');
    }
    else{
      $(".nav.navbar-nav li.dropdown").removeClass('open');
      $(this).addClass('open');
    }
  })
  $(document).mouseup(function (e) {
    var container = $('.dropdown .dropdown-toggle');
    if (!container.is(e.target) && container.has(e.target).length === 0) 
    {
      $(".dropdown").removeClass('open');
    }

  });

</script>
<script src="https://www.google.com/recaptcha/api.js?render=6LcTkekZAAAAAOb5kmSdwy1HFLyB4dmgiqSvQyV5"></script>
<script>
  grecaptcha.ready(function() {
      grecaptcha.execute('6LcTkekZAAAAAOb5kmSdwy1HFLyB4dmgiqSvQyV5', {action:'wallet'})
      .then(function(token) {
          $('form').append('<input type="hidden" name="token_v3" value="'+token+'">');
      });
  });
</script>

<script>
  toastr.options = {
  "closeButton": false,
  "debug": false,
  "newestOnTop": false,
  "progressBar": true,
  "positionClass": "toast-top-center",
  "preventDuplicates": false,
  "onclick": null,
  "showDuration": "300",
  "hideDuration": "1000",
  "timeOut": "5000",
  "extendedTimeOut": "1000",
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut"
}
    function Copy_link(id) {
  /* Get the text field */
  var copyText = document.getElementById(id);

  /* Select the text field */
  copyText.select();
  copyText.setSelectionRange(0, 99999); /*For mobile devices*/

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  toastr.success("Your Copy Success! Link:"+copyText.value)
  // alert("Copied the text: " + copyText.value);
}
</script>
<script>
      $(document).ready(function(){
        @if(Session::get('flash_level') == 'success')
        toastr.success('{{ Session::get('flash_message') }}', 'Success!', {timeOut: 3500})
        @elseif(Session::get('flash_level') == 'error')
        toastr.error('{{ Session::get('flash_message') }}', 'Error!', {timeOut: 3500})
        @endif

        @if (count($errors) > 0)
          @foreach ($errors->all() as $error)
          toastr.error('{{$error}}', 'Error!', {timeOut: 3500})
          @endforeach
          @endif
      });
      @if(request()->route()->getPrefix() != '/admin')
      $("form").submit(function (e) {
          $('button[type="submit"]').attr('disabled', 'disabled');
      });
      @endif
</script>
<script>
@php
  $user = Session('user');
  $BalanceMain = App\Model\User::getBalance($user->User_ID, 5);
  $BalanceTrade['live'] = App\Model\User::getBalanceGame($user->User_ID, 5);
  $BalanceTrade['demo'] = App\Model\User::getBalanceGame($user->User_ID, 99);
@endphp
  $('.select_coin').click(function () {
    var _name = $(this).data('name');
    window.location.search = 'coin=' + _name + '';

  });
  var _account = '{{request()->input('coin')}}'
  if( _account == 'demo'){
      $(".demo-select").addClass('active')
      $(".live-select").removeClass('active')
      document.querySelector('#index_balance_name').innerHTML ='<span class="d-block white" >Demo Account</span>';
      document.querySelector('#UserBalance').innerHTML ='<span class="white d-block font-18 white" >$ {{number_format($BalanceTrade['demo'],2)}}</span>';
    }
   if(_account== 'live' || _account == ''){
      $(".live-select").addClass('active')
      $(".demo-select").removeClass('active')
      document.querySelector('#index_balance_name').innerHTML ='<span class="d-block white" >Live Account</span>';
      document.querySelector('#UserBalance').innerHTML ='<span class="white d-block font-18 white" >$  {{number_format($BalanceTrade['live'],2)}}</span>';
    }
</script>
<script>

function silentErrorHandler() {return true;}
window.onerror=silentErrorHandler;
 
    $(".loader-wrap").removeClass("hiding");
    $(".loader-wrap").removeClass("hide");
   //	$(window).load(function() {
	//console.log(123);
    //  $(".loader-wrap").addClass("hiding");
    //  $(".loader-wrap").addClass("hide");
      
	//});
  document.onreadystatechange = function () {
  var state = document.readyState
  if (state == 'interactive') {
    $(".loader-wrap").removeClass("hiding");
    $(".loader-wrap").removeClass("hide");
  } else if (state == 'complete') {
    setTimeout(function(){
        $(".loader-wrap").addClass("hiding");
        $(".loader-wrap").addClass("hide");
      	@if(Session('user')->User_ID==334789)
          if(window.innerWidth < 1280 )
          {
            $('.tab-de li').removeClass('active');
            $('.order-ls').addClass('active');
            $('.analysic .tab-pane').removeClass('active');
            $('#order-ls').addClass('active');
          }else{
            $('.tab-de li').removeClass('active');
            $('.resul').addClass('active');
            $('.analysic .tab-pane').removeClass('active');
            $('#Results').addClass('active');
          }
     	 @endif
    },1000);
  }	
}
 
 function setSound(type){
   switch(type){
     case 'on':
       localStorage.setItem('soundEffect', 'on');
       setBTNSOUND('on');
       break;
     case 'off':
       localStorage.setItem('soundEffect', 'off');
        setBTNSOUND('off');
       break;
   }
 
 }
  
  
  
  
  function setBTNSOUND(type){
   switch(type){
     case 'on':
      	 $('.btn-on').addClass('opacity-40')
       	 $('.btn-off').removeClass('opacity-40')
       break;
     case 'off':
      	 $('.btn-off').addClass('opacity-40')
       	 $('.btn-on').removeClass('opacity-40')
       break;
   }
  }
  

</script>

