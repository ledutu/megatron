@extends('system.layout.Master')
@section('css')
<style>
  .card-dashboard-mini {

    background: rgb(35 31 32 / 0.6);
    margin: 0 2px;
    border: 1px solid #FFF200;
    width: 100%;
    border-radius: 15px;
    padding: 10px;
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

  .mt-5e {
    margin-top: 5em;
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
</style>
@endsection
@section('content')
<div class="grid grid-cols-8 gap-8 gap-y-20">
  <div class="col-span-8 lg:col-span-2"></div>
  <div class="col-span-8 lg:col-span-4">
    <div class="card-dashboard-mini">
      <form method="get" class="grid grid-cols-2 gap-8">
        @csrf
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">User ID</label>
          <input type="text" name="UserID" class="form-control" placeholder="Enter User ID"
            value="{{request()->input('UserID')}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">User Email</label>
          <input type="text" class="form-control" name="Email" placeholder="Enter Email"
            value="{{request()->input('Email')}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Created Date</label>
          <input id="date-picker-3" class="form-control" name="datefrom" alue="{{request()->input('datefrom')}}"
            placeholder="Select Date From">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Date From</label>
          <input id="date-picker-3" class="form-control" name="datefrom" value="{{request()->input('datefrom')}}"
            placeholder="Select Date From">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Data To</label>
          <input id="date-picker-3" class="form-control" name="dateto" value="{{request()->input('dateto')}}"
            placeholder="Select Date From">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Status</label>
          <select name="status" class="form-control">
            <option class="text-black" value="">--- Select ---</option>
            <option class="text-black" value="0" {{request()->input('status') == '0' ? 'selected' : ''}}>
              Pedding
            </option>
            <option class="text-black" value="1" {{request()->input('status') == '1' ? 'selected' : ''}}>

              Confirmed</option>
            <option class="text-black" value="-1" {{request()->input('status') == '-1' ? 'selected' : ''}}>
              Error
            </option>
          </select>
        </div>
        <div class="form-group col-span-2 lg:col-span-2 flex flex-col ">
          <div class="flex justify-center ">
            <button type="submit" class="btn button btn-search">Search</button>
            <a class="btn button btn-cancel" href="{{ route('admin.getKYC') }}">Reset</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="grid grid-cols-8 gap-8 mt-5e ">
  <div class="col-span-8 lg:col-span-1 flex flex-col "></div>
  <div class="col-span-8 lg:col-span-6">
    <div class="card-table-static reponsive">
      <div class="table-wrap">
        <div class="table-responsive">
          {{$profileList->appends(request()->input())->links('system.layout.Pagination')}}
          <div style="clear:both"></div>
          <table id="profileList" class="dt-responsive table table-striped table-bordered table-responsive"
            cellspacing="0" width="100%">
            <thead>
              <tr>
                <th>ID</th>
                <th>Profile User</th>
                <th>Email</th>
                <th>Passport ID</th>
                <th>Update time</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($profileList as $item)
              <tr>
                <td>{{$item->Profile_ID}}</td>
                <td>{{$item->Profile_User}}</td>
                <td>{{$item->User_Email}}</td>
                <td>{{$item->Profile_Passport_ID}}</td>
                <td>{{$item->Profile_Time}}</td>
                <td id="list-profile-action-{{$item->Profile_ID}}">
                  @if($item->Profile_Status == 0)
                  <button data-id="{{$item->Profile_ID}}" type="button"
                    class="view-detail btn btn-success btn-rounded waves-effect waves-light" data-toggle="modal"
                    data-target="#profile_info">Detail</button>
                  @elseif($item->Profile_Status == 1)
                  <label class="bagde bagde-success">

                    <span class="badge badge-info">Confirmed</span>
                  </label>
                  @else
                  <label class="bagde bagde-light">Cancel</label>

                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          {{$profileList->appends(request()->input())->links('system.layout.Pagination')}}
        </div>
      </div>
    </div>
  </div>
</div>
<div id="profile_info" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
    
      <div class="modal-body">
        <form>
          @csrf
          <div class="row mb-10">
            <div class="col-md-12 col-lg-12" style="margin: auto;">
              <div class="form-wrap">
                <div class="form-body overflow-hide">
                  <div class="form-group">
                    <label class="control-label " for="exampleInputuname_01" style="color: #0088ce">ID/Passport
                      Number</label>
                    <div class="input-group">
                      <div class="input-group-addon btn-success"><i class="fa fa-user"></i></div>
                      <input type="text" class="form-control " name="passport_id" id="modal-passport-id" placeholder=""
                        value="" readonly>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 col-lg-6">
              <div class="form-wrap">
                <div class="form-body overflow-hide">
                  <div class="form-group mb-30">
                    <label class="control-label  text-left" style="color: #0088ce">ID/Passport</label>
                    <div class="panel panel-default card-view">
                      <div class="panel-wrapper collapse in">
                        <div class="panel-body">
                          <img src="" width="100%" id="img-passport">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-6">
              <div class="form-wrap">
                <div class="form-body overflow-hide">
                  <div class="form-group mb-30">
                    <label class="control-label  text-left" style="color: #0088ce">Selfie</label>
                    <div class="panel panel-default card-view">
                      <div class="panel-wrapper collapse in">
                        <div class="panel-body">
                          <img src="" width="100%" id="img-passport-selfie">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer flex justify-center" id="div-modal-footer">
        <button type="button" class="btn btn-search" id="profile-accept">Accept</button>
        <button type="button" class="btn btn-cancel" id="profile-disagree">Disagree</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
      </div>
    </div>

  </div>
</div>
@endsection
@section('scripts')

<script type="text/javascript">
   var token = '{{ csrf_token() }}';
  var ex = flatpickr('#date-picker-3');
  $(document).ready(function () {
    $('#member-list').DataTable({
      "bPaginate": true,
      "bLengthChange": false,
      "bFilter": true,
      "searching": false,
      "bInfo": false,
      "bAutoWidth": false
    });
  });
  $(document).ready(function () {

    $('table').on('click', '.view-detail', function () {
      _info = $(this).attr('data-id');
      console.log(_info);
      _dataList = @json($profileList);
      _inforPerson = jQuery.grep(_dataList.data, function (obj) {
        if (obj.Profile_ID == _info) {
          return obj;
        }
        //                 return obj.Profile_ID == _info;
      });
      _serverImage = "https://media.igtrade.co/";
      $('#modal-passport-id').val(_inforPerson[0].Profile_Passport_ID);
      $('#modal-passport-full-name').val(_inforPerson[0].User_FullName);
      $('#modal-passport-phone').val(_inforPerson[0].User_Phone);

      $('#profile-accept').attr('data-value', _info);
      $('#profile-disagree').attr('data-value', _info);

      $('#img-passport').attr('src', _serverImage + _inforPerson[0].Profile_Passport_Image);
      $('#img-passport-selfie').attr('src', _serverImage + _inforPerson[0].Profile_Passport_Image_Selfie);
    });
    $('#profile-accept').click(function () {

      profileID = $(this).attr('data-value');
      if (profileID) {
        $.ajax({
          url: '{{ route('system.admin.confirmProfile') }}',
          type: "POST",
          dataType: "json",
          data: { _token: token, id: profileID, action: 1 },
          success: function (data) {
            if (data.status == 'success') {
              $('#list-profile-action-' + profileID).html("<label class=\"bagde bagde-success\"><span class=\"badge badge-info\">Confirmed</span></label>");
              $('#profile_info').modal('hide');
              toastr.success('Success', 'Success!', { timeOut: 3500 });
            } else {
              $('#profile_info').modal('hide');
              toastr.error('Error', 'Error!', { timeOut: 3500 });
            }
          }
        });
      }
    });
    $('#profile-disagree').click(function () {
      // profile-disagree
      $(this).prop('disabled', true);
      $(this).text('Loading...')
      profileID = $(this).attr('data-value');
      if (profileID) {
        $.ajax({
          url: '{{ route('system.admin.confirmProfile') }}',
          type: "POST",
          dataType: "json",
          data: { _token: token, id: profileID, action: -1 },
          success: function (data) {
            if (data.status == 'success') {
              $('#list-profile-action-' + profileID).html("<span class=\"badge badge-danger\">Cancel</span>");
              $('#profile_info').modal('hide');
              toastr.success(data.message, 'Success!', { timeOut: 3500 });
            } else {
              $('#profile_info').modal('hide');
              toastr.error(data.message, 'Error!', { timeOut: 3500 });
            }
          }
        });
      }
    });
  });
</script>
@endsection