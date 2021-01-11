                                      @extends('system.layout.Master')
@section('css')
<link data-require="sweet-alert@*" data-semver="0.4.2" rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<link href="exchange/css/dropify.min.css?v=1" rel="stylesheet" type="text/css">
<style>
  .nav.nav-tabs>li.active>a {
    background-color: #ffffff26;
    border: 0;
    border-bottom: 3px solid white;
    margin-bottom: 3px;
    color: #fff !important;
    font-weight: 600;
  }

  .nav.nav-tabs>li>a {
    text-transform: uppercase;
    border: 0;
    /* border-bottom: 3px solid white; */
    margin-bottom: 3px;
    color: #fff !important;
    font-weight: 600;
  }

  .d-none {
    display: none;
  }

  label {
    display: inline-block;
    max-width: 100%;
    margin-bottom: 5px;
    font-weight: 700;
    color: #fff;
  }

  .card-dashboard-static {
    min-height: 250px;
    background: rgb(35 31 32 / 0.6);
    margin: 0 2px;
    border: 1px solid #FFF200;
    width: 100%;
    margin: auto;
    border-radius: 15px;
  }

  .card-dashboard-mini {
    min-height: 150px;
    background: rgb(35 31 32 / 0.6);
    margin: 0 2px;
    border: 1px solid #FFF200;
    width: 100%;
    border-radius: 15px;
  }

  .card-table-static {
    min-height: 200px;
    background: rgb(35 31 32 / 0.6);
    margin: 0 2px;
    border: 1px solid #FFF200;
    width: 100%;
    margin: auto;
    border-radius: 2px;
  }

  .btn-search {
    background: linear-gradient(90deg, #F4EB25, #F4C41B) !important;
    color: black;
    font-size: larger;
    font-weight: 700;
    width: 150px;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    align-self: center;
    height: 35px;
    margin-left: 5px;
    margin-right: 5px;
  }

  .btn-cancel {
    background: linear-gradient(90deg, #ed3935, #db4224) !important;
    color: #fff;
    font-size: larger;
    font-weight: 700;
    width: max-content;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    align-self: center;
    height: 35px;
    margin-left: 5px;
    margin-right: 5px;
  }

  .btn-export {
    background: linear-gradient(90deg, #63ae61, #7cb772) !important;
    color: #fff;
    font-size: larger;
    font-weight: 700;
    width: 150px;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    align-self: center;
    height: 35px;
    margin-left: 5px;
    margin-right: 5px;
  }

  .form-group {
    margin-bottom: 0;
  }

  .form-group .form-control {
    height: 35px;
    background: transparent;
    border-radius: 15px;
    color: white;
    font-size: 15px;
    font-weight: 600;
  }

  .form-group label {
    font-size: 15px;
    font-weight: 600;
    padding-left: 10px;
  }

  .modal-footer,
  .modal-content {
    background: #1a202c;
  }

  .title {
    font-size: 17px;
    font-weight: 700;
    color: #fff;
    text-align: center;
  }
  .card-dashboard-mini.kyc{
    padding: 20px;
  }

  .dropify-wrapper .dropify-preview .dropify-render{
     display: flex;
    width: 100%;
    height: 100%;
    justify-content: center;
    justify-items: center;
    justify-self: center;
    align-content: center;
    align-items: center;
    align-self: center;
  }
  .dropify-wrapper .dropify-message p {
    margin: 5px 0 0;
    font-size: 22px;
}
</style>
@endsection
@section('content')
<div class="grid grid-cols-12 gap-8 items-start">
  <div class="col-span-12 lg:col-span-4 grid grid-cols-1 gap-8">
    <div class="col-span-1">
      <div class="card-dashboard-mini">
        <div class="panel-body">
          <div class="profile-box">
            <div class="profile-cover-pic">
            </div>
            <div class="profile-info text-center mb-15">
              <div class="profile-img-wrap">
                <img class="inline-block mb-10" src="exchange/img/logo_mobile.png" alt="user" style="width: 124px;" />
              </div>
              <h5 class="title block mt-10 mb-5 weight-500 capitalize-font text-white">
                {{$user->User_Email}}</h5>
              <h6 class="title block capitalize-font text-white mb-5">ID:
                <span>{{$user->User_ID}}</span></h6>
            </div>
            <div class="social-info flex justify-center">
              <button class="btn btn-search  btn-block btn-anim" data-toggle="modal" data-target="#myModal">
                <span class="btn-text">{{__('profile.change_password')}}</span>
              </button>
                <button class="btn btn-{{ $kycProfile && $kycProfile->Profile_Status==1 ? 'export' : 'cancel' }}   btn-anim ">
                {{ !$kycProfile ? __('profile.unverified_account') : $kycProfile->Profile_Status==1?__('profile.verified_account'): __('profile.account_verification_approval')}}
                </button>
            </div>
          </div>

        </div>
      </div>
    </div>
    <div class="col-span-1">
      <div class="card-dashboard-mini ">
        <div class="panel-body">
          <div class="">
            <div class="panel-heading">

              <h6 class="panel-title  text-center">
                <div class="justify-center"><img src="exchange/img/authentic.png" width="50" style="margin:auto"></div>
                <label>{{__('profile.google_authentication')}}</label>
                <label>{{__('profile.used_for_withdrawals_and_security_modifications')}}</label>
              </h6>
              <div class="flex justify-center self-center items-center">
              
                
                
      
                 <button class="btn btn-{{ ($Enable) ? 'cancel' : 'export' }} btn-block  btn-anim " data-toggle="modal"
                  data-target="#m-a-a" >
                   <span class="btn-text">{{ ($Enable) ? __('profile.enable_auth') : __('profile.disable_auth') }}</span>
                </button>
              </div>

              <div class="clearfix"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-span-12 lg:col-span-8">
    <div class="card-dashboard-mini kyc">
      <form method="post" id="post-profile" class="grid grid-cols-1 gap-8" action="{{route('system.user.PostKYC')}}" enctype="multipart/form-data">
        @csrf
        <div class="form-body overflow-hide grid grid-cols-1 gap-2">
          <div class="form-group">
            <label class="control-label mb-10" for="exampleInputuname_01"><i class="far fa-id-card"></i> {{__('profile.id_passport_number')}}</label>

            <input type="text" class="form-control" name="passport" id="passport" placeholder="{{__('profile.id_passport_number')}}"
              value="{{ $kycProfile ? $kycProfile->Profile_Passport_ID : ''}}">

          </div>
          <div class="form-group profile-box">
            <label class="control-label" for="exampleInputuname_01"><i class="fas fa-id-card"></i>
              {{__('profile.id_passport_image')}}</label>
            <br>
            <label class="control-label" for="exampleInputuname_01" style="color:#e09000!important"><i
                class="fa fa-caret-right"></i> {{__('profile.make_sure_the_image_is_full_and_clear_and_the_format_is_jpg')}}</label><br>
            <label class="control-label text-danger" for="exampleInputuname_01" style="color:#e09000!important"><i
                class="fa fa-caret-right"></i> {{__('profile.please_use_image_up_to_maximum_size')}}</label>
            <div class="profile-cover-pic">

              <div class="fileupload">
                <input onchange="readURL(this);" type="file" name="passport_image" id="passport-image"
                  class="file-upload-input dropify bg-dark"
                  data-default-file="{{ $kycProfile ? 'https://media.igtrade.co/'.$kycProfile->Profile_Passport_Image : ''}}"
                  accept="image/*" {{ $kycProfile ?' disabled="disabled"': ''}} />
              </div>
              <div class="profile-image-overlay"></div>
            </div>
          </div>
          <div class="form-group profile-box">
            <label class="control-label" for="exampleInputuname_01"><i class="fas fa-id-card"></i> {{__('profile.id_passport_image_with_selfie')}}</label>
            <br>
            <label class="control-label  text-danger" for="exampleInputuname_01"><i class="fa fa-caret-right"
                style="color:#e09000!important"> {{__('profile.make_sure_the_image_is_full_and_clear_and_the_format_is_jpg')}}</i></label><br>
            <label class="control-label text-danger" for="exampleInputuname_01" style="color:#e09000!important"><i
                class="fa fa-caret-right"></i> {{__('profile.your_face')}}</label>
            <br>
            <label class="control-label text-danger" for="exampleInputuname_01" style="color:#e09000!important"><i
                class="fa fa-caret-right"></i> {{__('profile.your_id_passport')}}</label><br>
            <label class="control-label text-danger" for="exampleInputuname_01" style="color:#e09000!important"><i
                class="fa fa-caret-right"></i> {{__('profile.please_use_image_up_to_maximum_size')}}</label>
            <div class="profile-cover-pic">
              <div class="fileupload">
                <input onchange="readURL2(this);" type="file" name="passport_image_selfie" id="passport_image_selfie"
                  class="file-upload-input dropify bg-dark"
                  data-default-file="{{ $kycProfile ? 'https://media.igtrade.co/'.$kycProfile->Profile_Passport_Image_Selfie : ''}}"
                  accept="image/*" {{ $kycProfile ? 'disabled="disabled"': ''}}  />
              </div>
              <div class="profile-image-overlay"></div>
            </div>
          </div>
        </div>
     
        @if(!$kycProfile)
        @if(Session('user')->Level==1)
           @endif
        <div class="flex justify-center text-center">
          <button type="submit" class="btn btn-search btn-icon-anim"><i class="fa fa-floppy-o"></i> {{__('profile.save')}}</button>
        </div>
     
        @endif
      </form>
    </div>
  </div>


</div>
<div id="myModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{route('postChangePassword')}}" method="post">
        @csrf

        <div class="modal-body">
          <!-- Row -->
          <div class="row">
            <div class="col-lg-12">
              <div class="">
                <div class="panel-wrapper collapse in">
                  <div class="panel-body pa-0">
                    <div class="col-sm-12 col-xs-12">
                      <div class="form-wrap">
                        <div class="form-body overflow-hide">
                          <div class="form-group">
                            <label class="mb-10 text-dark" for="exampleInputpwd_1">{{__('profile.current_password')}}</label>
                            <div class="input-group">
                              <div class="input-group-addon">
                                <i class="fas fa-user-lock"></i>
                              </div>
                              <input type="password" class="form-control" name="current_password"
                                placeholder="{{__('profile.enter_current_password')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="mb-10 text-dark" for="exampleInputpwd_1">{{__('profile.new_password')}}</label>
                            <div class="input-group">
                              <div class="input-group-addon">
                                <i class="fas fa-user-lock"></i>
                              </div>
                              <input type="password" class="form-control" name="new_password"
                                placeholder="{{__('profile.enter_new_password')}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="mb-10 text-dark" for="exampleInputpwd_1">{{__('profile.password_confirm')}}</label>
                            <div class="input-group">
                              <div class="input-group-addon">
                                <i class="fas fa-user-lock"></i>
                              </div>
                              <input type="password" class="form-control" name="password_confirm"
                                placeholder="{{__('profile.enter_password_confirm')}}">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer flex justify-center self-center">
          <button type="submit" class="btn btn-search waves-effect">
            {{__('profile.save')}}</button>
          <button type="button" class="btn btn-cancel waves-effect" data-dismiss="modal">
            {{__('profile.cancel')}}</button>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<div id="m-a-a" class="modal fade animate" data-backdrop="true">
  <div class="modal-dialog" id="animate">
    <div class="modal-content">

      <!-- Modal Body -->
      <div class="modal-body text-center">
        <form role="form" action="{{route('postAuth')}}" method="POST" style="color:black!important;"
          class="grid grid-cols-1 gap-6">
          {{csrf_field()}}
          @if(!$Enable)
          <label for=""> {{__('profile.authenticator_secret_code')}}: <b>{{ $secret }}</b></label>
         
          <img style="margin: auto;" src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={{ $inlineUrl }}&choe=UTF-8">
          @endif
          <label>{{__('profile.enter_the_verification_code_provided_by_your_authentication_app')}}</label>
          <input type="text" name="verifyCode" class="form-control text-center" id="exampleInputuname_01" placeholder="" value="">
          <div class=" flex justify-center">
            <button type="submit"
              class="btn btn-{{($Enable) ? 'cancel' : 'export'}} btn-block  btn-anim">{{ ($Enable) ? __('profile.disable') : __('profile.enable') }}</button>
          </div>
        </form>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
</div>
@endsection
@section('scripts')
<script src="exchange/js/dropify.min.js?v=3"></script>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>

  /*FileUpload Init*/
  $(document).ready(function () {
    "use strict";

    /* Basic Init*/
    $('.dropify').dropify();

    /* Translated Init*/
    $('.dropify-fr').dropify({
      messages: {
        default: 'Glissez-dÃ©posez un fichier ici ou cliquez',
        replace: 'Glissez-dÃ©posez un fichier ou cliquez pour remplacer',
        remove: 'Supprimer',
        error: 'DÃ©solÃ©, le fichier trop volumineux'
      }
    });

    /* Used events */
    //
    var drEvent = $('#input-file-events').dropify();

    drEvent.on('dropify.beforeClear', function (event, element) {
      return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
    });

    drEvent.on('dropify.afterClear', function (event, element) {
      alert('File deleted');
    });

    drEvent.on('dropify.errors', function (event, element) {
      console.log('Has Errors');
    });

    var drDestroy = $('#input-file-to-destroy').dropify();
    drDestroy = drDestroy.data('dropify')
    $('#toggleDropify').on('click', function (e) {
      e.preventDefault();
      if (drDestroy.isDropified()) {
        drDestroy.destroy();
      } else {
        drDestroy.init();
      }
    });

  });

  $('.btn-getotp').click(function () {
    _div = $(this).parent();
    $.getJSON("{{ route('system.ajax.getOTP') }}", function (data) {
      console.log(data);
      if (data.status == true) {
        $('.input-otp').removeClass('d-none');
        _div.html('<button type="submit" class="btn btn-success  mr-10" id=""><i class="fa fa-save" aria-hidden="true"></i> SAVE</button>');
        toastr.success('Please check your email to get OTP', 'Success!', { timeOut: 3500 })
      } else {
        toastr.error('Please login again to get OTP', 'Error!', { timeOut: 3500 })
      }

    });
  });

  $('#post-profile').submit(function () {
    $(this).find("button[type='submit']").prop('disabled', true);
    $('#btn-save-profile').text('Loading...');
  });
  
  var drag_and_drop_a_file_here_or_click = '{{__("profile.drag_and_drop_a_file_here_or_click")}}';
</script>
@endsection