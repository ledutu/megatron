<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">

  <title>IG TRADE | {{__('auth.sign_up')}}</title>

  <meta property="og:title" content="IG TRADE">
  <meta property="og:url" content="https://igtrade.co/">
  <meta property="og:image" content="exchange/img/logo_mobile.png">

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.theme.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.transitions.css">

  <link rel="shortcut icon" href="exchange/img/logo_mobile.png" type="image/x-icon" />
  <link rel="apple-touch-icon" sizes="180x180" href="exchange/img/logo_mobile.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.css">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,400i,600,700">
  <link rel="stylesheet" id="css-main" href="auth/assets/css/codebase.css?v={{time()}}">
  <link data-require="sweet-alert@*" data-semver="0.4.2" rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.css" />
  <style>
    .btn.btn-hero.btn-sm,
    .btn-group-sm>.btn.btn-hero {
      padding: 10px 20px;
      margin-top: 5px;
      min-width: 120px;
    }

    .content.content-full {
      border: none;
      border-top: 3px #0d9881 solid;
      border-bottom: 2px #88c24b solid;
      background: rgba(0, 0, 0, .4);
    }

    .btn.btn-sm.btn-hero.btn-alt-primary {
      letter-spacing: 0.05rem;
      position: relative;
      background: #7cbe51;
      color: #fff;
      overflow: hidden;
      transition: 0.3s ease-in-out;
      border-radius: 0.3rem;
      border-color: #7cbe51;
      z-index: 1;
      box-shadow: 0 19px 38px rgba(0, 0, 0, 0.3), 0 15px 12px rgba(0, 0, 0, 0.22);
    }

    .btn.btn-sm.btn-hero.btn-alt-primary.outline {
      background: #fff0;
    }

    .btn.btn-sm.btn-hero.btn-alt-primary.outline:hover {
      background: #7cbe51;
    }

    .btn.btn-sm.btn-hero.btn-alt-primary i {}

    .btn.btn-sm.btn-hero.btn-alt-primary:hover i {}

    .btn.btn-sm.btn-hero.btn-alt-primary:hover {
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
      transform: scale(0.95);
    }

    .content.content-full:after {
      background: rgba(255, 255, 255, .1);
    }

    .input-group>.input-group-prepend>.btn,
    .input-group>.input-group-prepend>.input-group-text,
    .input-group>.input-group-append:not(:last-child)>.btn,
    .input-group>.input-group-append:not(:last-child)>.input-group-text,
    .input-group>.input-group-append:last-child>.btn:not(:last-child):not(.dropdown-toggle),
    .input-group>.input-group-append:last-child>.input-group-text:not(:last-child) {
      background: #57b15f;
      border-color: rgb(87 177 95);
    }

    .input-group>.form-control:not(:first-child),
    .input-group>.custom-select:not(:first-child) {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
      border-bottom-right-radius: 100px;
      border-top-right-radius: 100px;
      border-color: rgb(87 177 95);
    }

    .toast.toast-error {
      background: red;
    }

    .toast.toast-success {
      background: #4caf50;
    }

    body {
      background-color: #A3A3A3;
    }
    .swal2-popup {
      width: 24em!important;
    }
    input[readonly] {
      background: transparent!important; 
    }
  </style>
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
  (document, 'script', 'oc-chat-widget-bootstrap', '5f99fb6a4f63f3f0becda9c589e3537b'));
</script>
</head>

<body>
  <div id="page-container" class="main-content-boxed">
    <main id="main-container">

      <div class="bg-image"
        style="background:url('exchange/img/bg/bg_1.jpg?v=1');background-position: center;background-repeat: no-repeat;background-size: cover;">
        <div class="row mx-0 bg-black-op">
          <div class="hero-static col-md-3 col-xl-4 d-none d-md-flex align-items-md-end">
            <div class="p-30 invisible" data-toggle="appear">

            </div>
          </div>
          <div class="hero-static col-md-6 col-xl-4 d-flex align-items-center invisible" data-toggle="appear"
            data-class="animated flipInX">
            <div class="content content-full">
              <!-- Header-->
              <div class="px-30 py-10">
                <h1 class="h3 font-w700 mt-30 mb-20">
                  <img src="exchange/img/logo.png" width="100%">
                </h1>
              </div>

              <form class="js-validation-signin px-30" action="{{ route('postRegister') }}" method="post">
                <div class="form-group row">
                  <div class="col-12">
                    <label class="text-white" for="username">{{__('auth.email')}}</label>
                    <div class="form-material floating">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="username" name="User_Email" placeholder="{{__('auth.enter_your_email')}}">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col-12">
                    <label class="text-white" for="username">{{__('auth.password')}}</label>
                    <div class="form-material floating">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" id="password" name="password" placeholder="{{__('auth.enter_your_password')}}">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col-12">
                    <label class="text-white" for="username">{{__('auth.password_confirmation')}}</label>
                    <div class="form-material floating">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="{{__('auth.enter_your_password_confirmation')}}">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col-12">
                    <label class="text-white" for="username">{{__('auth.sponsor')}}</label>
                    <div class="form-material floating">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="sponsor" name="sponsor" placeholder="{{__('auth.enter_sponsor')}}" value="{{request()->input('ref')}}" {{request()->input('ref') ? "readonly" : ""}}>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group" style="text-align:center">
                  <a href="{{route('getLogin')}}" class="btn btn-sm btn-hero btn-alt-primary outline">
                    <i class="fa fa-arrow-left mr-10"></i> {{__('auth.back')}}
                  </a>

                  <button type="submit" class="btn btn-sm btn-hero btn-alt-primary ">
                    <i class="si si-login mr-10 fa fa-sign-in"></i> {{__('auth.sign_up')}}
                  </button>
                </div>
                @csrf
              </form>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>

  <div class="modal fade" id="notifi" role="dialog" style="z-index:9999">
    <div class="modal-dialog" style="">
      <div class="modal-header" style="background-color:#fff;border:0;padding:5px 10px;">
        <button id="close-modal" type="button" class="close " data-dismiss="modal" aria-hidden="true"
          style="color:black;opacity:1">&times;</button>
      </div>
      <div class="modal-content p-0" style="width:100%;border:0">


        <div id="testimonial-slider" class="owl-carousel" style="display:block">
         
        </div>
      </div>

    </div>
  </div>
  <script src="auth/assets/js/codebase.core.min.js"></script>
  <script type="text/javascript"
    src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/8.11.8/sweetalert2.min.js"></script>
  <script>
    $(document).ready(function () {
      @if (Session:: get('flash_level') == 'success')
    toastr.success('{{ Session::get('flash_message') }}', 'Success!', { timeOut: 3500 })
    @elseif(Session:: get('flash_level') == 'error')
    toastr.error('{{ Session::get('flash_message') }}', 'Error!', { timeOut: 3500 })
    @endif

    @if (count($errors) > 0)
      @foreach($errors -> all() as $error)
    toastr.error('{{$error}}', 'Error!', { timeOut: 3500 })
    @endforeach
    @endif
      });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      @if (request() -> input('redirect'))
        _urlParam = "{{decrypt(request()->input('redirect'))}}";
      console.log(_urlParam);
      _ref = GetURLReferrer(_urlParam, 'ref');
      // $(".url_register").attr("href", "{{route('getRegister')}}?" + _ref);
      @endif
      @if (Session:: has('otp'))
    var CSRF_TOKEN = '{{ csrf_token() }}';
    swal.fire({
      title: 'Enter Authentication',
      text: 'Please enter authentication code.',
      input: 'text',
      type: 'input',
      name: 'txtOTP',
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Submit',
      showLoaderOnConfirm: true,
      confirmButtonClass: 'btn btn-confirm',
      cancelButtonClass: 'btn btn-cancel'
    }).then(function (otp) {
      console.log(otp);
      $.ajax({
        url: "{{route('postLoginCheckOTP')}}",
        type: 'POST',
        data: { _token: CSRF_TOKEN, otp: otp.value },
        success: function (data) {
          if (data == 1) {
            @if (request() -> input('redirect'))
              location.href = "{{decrypt(request()->input('redirect'))}}";
            @else
            location.href = "{{route('getDashboard')}}";
          @endif
        }else{
          swal.fire({
            title: 'Error',
            text: "Authentication Code Is Wrong",
            type: 'error',
            confirmButtonClass: 'btn btn-confirm',
            allowOutsideClick: false
          }).then(function () {
            location.href = "{{route('getLogin')}}";
          })
        }
      }
					});
		    	});
    @endif
		});

  
    function GetURLReferrer(url, sParam) {
      var sPageURL = url;
      _stringRequestUrl = sPageURL.split('?');
      console.log(_stringRequestUrl);
      if (_stringRequestUrl.length > 1) {
        _allRequest = _stringRequestUrl[1];
        console.log(_allRequest);
        _getArrayRequest = _allRequest.split('&');
        console.log(_allRequest, _getArrayRequest);
        for (var i = 0; i < _getArrayRequest.length; i++) {
          var sParameterName = _getArrayRequest[i].split('=');
          if (sParameterName[0] == sParam) {
            return _getArrayRequest[i];
          }
        }
      }
      else {
        return false;
      }
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
  </script>
  <script src="auth/assets/js/codebase.app.min.js"></script>
  <script src="auth/assets/js/plugins/jquery-validation/jquery.validate.min.js"></script>

  <script src="auth/assets/js/pages/op_auth_signin.min.js"></script>
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
</body>

</html>