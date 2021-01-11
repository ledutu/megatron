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
    height:35px;
    margin-left:5px;
    margin-right:5px;
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
    height:35px;
    margin-left:5px;
    margin-right:5px;
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
    height:35px;
    margin-left:5px;
    margin-right:5px;
  }

  .form-group {
    margin-bottom: 0;
  }
  .form-group .form-control{
    height: 35px;
    background: transparent;
    border-radius: 15px;
    color: white;
    font-size: 15px;
    font-weight: 600;
  }
  .form-group label{
    font-size: 15px;
    font-weight: 600;
    padding-left: 10px;
  }
  .user-with-border td{
    border-right:1px solid white;
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
          <input class="form-control" type="text" placeholder="User ID" value="{{request()->input('UserID')}}" name="UserID">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col " >
          <label for="">User Email</label>
          <input class="form-control" type="text" placeholder="Email" value="{{request()->input('Email')}}" name="Email">                                            
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Created Date</label>
          <input id="date-picker-3" class="form-control" name="datetime"
            value="{{request()->input('datetime')}}"  placeholder="Select Date From">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Sponsor</label>
          <input class="form-control" type="text"
                placeholder="Sponsor"
                value="{{request()->input('sponsor')}}"
                name="sponsor">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Tree</label>
          <input  class="form-control" type="text"
                  placeholder="Tree"
                  value="{{request()->input('tree')}}" name="tree">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Status</label>
          <select  name="status" class="form-control" id="">
            <option selected value="" class="text-black"
                {{request()->input('status') == '' ? 'selected' : ''}}>
                --- Select ---</option>
            <option value="1" class="text-black"
                {{request()->input('status') == '1' ? 'selected' : ''}}>
                Active</option>
            <option value="0" class="text-black"
                {{request()->input('status') == '0' ? 'selected' : ''}}>
                Not Active</option>
          </select>
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">User Level</label>
          <select type="number" class="form-control" name="user_level">
            <option class="text-black" value=""
            {{request()->input('user_level') == '' ? 'selected' : ''}}>--Select--</option>
            <option class="text-black" value="0"
            {{request()->input('user_level') == '0' ? 'selected' : ''}}>Member</option>
            <option class="text-black" value="1"
            {{request()->input('user_level') == '1' ? 'selected' : ''}}>Admin</option>
            <option class="text-black" value="2"
            {{request()->input('user_level') == '2' ? 'selected' : ''}}>Finance</option>
            <option class="text-black" value="3"
            {{request()->input('user_level') == '3' ? 'selected' : ''}}>Support</option>
            <option class="text-black" value="4"
            {{request()->input('user_level') == '4' ? 'selected' : ''}}>Customer</option>
            <option class="text-black" value="5"
            {{request()->input('user_level') == '5' ? 'selected' : ''}}>Bot</option>
          </select>
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Action</label>
          <div class="flex justify-center ">
            <button class="btn button btn-search" type="submit">Search</button>
            <button class="btn button btn-export" type="submit" name="export" value="1">export</button>
            <a class="btn button btn-cancel" href="{{ route('admin.getMember') }}">Reset</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="grid grid-cols-8 gap-8 mt-5e ">
  <div class="col-span-8">
    <div class="card-table-static reponsive">
      <div class="table-wrap">
        <div class="table-responsive">
            {{$user_list->appends(request()->input())->links('system.layout.Pagination')}}
            <div style="clear:both"></div>
            <table id="member-list"
                  class=" dt-responsive table table-striped table-bordered table-responsive"
                  cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th data-toggle="true">
                            ID
                        </th>
                        <th >
                          LEVEL
                        </th>
                        <th >
                          RANK
                        </th>
                        <th>
                            MAIL
                        </th>
                        <th data-hide="phone">
                            REGISTERED DATE
                        </th>
                        <th data-hide="phone">
                            PARENT
                        </th>
                        <th data-hide="phone,tablet">
                            TREE
                        </th>
                        <th data-hide="phone,tablet">
                          ADDRESS
                        </th>
                        <th data-hide="phone,tablet">
                            STATUS
                        </th>
                        <th data-hide="phone">
                            AUTH
                        </th>
                       <!--  <th data-hide="phone">
                            REQUEST FORGOT G2A
                        </th> -->
                        <th data-hide="phone">
                            ACTION
                        </th>
                        <th data-hide="phone">
                            FUNCTION
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                      $arr_address = [ 1 => 'BTC', 2 => 'ETH', 5 => 'USDT', 8 => 'DAFCO' ];
                    @endphp
                      @foreach($user_list as $v)
                    @php
                    $address = DB::table('address')->where('Address_User', $v->User_ID)->where('Address_IsUse', 0)->get();
                    
                    @endphp
                    <tr>
                        <td><p>{{ $v->User_ID }}</p></td>
                        <td >
                          <select class="text-black form-control"  onchange="{{ Session('user')->User_Level != 10 ? 'location=this.value' : '' }}" style= "width:100px">
                          @foreach($level as $k=>$l)
                          <option class="text-black" value="{{route('system.admin.getSetLevelUser', [$v->User_ID, $k])}}" {{$k == $v->User_Level ? "selected" : ""}}>{{$l}}</option>
                          @endforeach
                          </select>
                        </td>
                        <td >
                          <select class="text-black form-control"  onchange="{{ Session('user')->User_Level == 1 ? 'location=this.value' : '' }}" style= "width:100px">
                          @for( $a = 0; $a < 8; $a++ )
                          <option class="text-black" value="{{route('system.admin.getSetAgencyUser', [$v->User_ID, $a])}}" {{isset($listSetAgency[$v->User_ID]) && $a == $listSetAgency[$v->User_ID] ? "selected" : ""}}>RANK {{$a}}</option>
                          @endfor
                          </select>
                        </td>
                        <td>
                            <i class="d-block" style="display: block; font-weight: bold;">{{ $v->User_ID }}</i> 
                            <span id="input-email-{{$v->User_ID}}" >{{$v->User_Email}}</span>
                            @if(Session('user')->User_Level != 10)
                            <div id="action-email-{{$v->User_ID}}"
                                style="float:right">
                                <a data-id_user='{{$v->User_ID}}'
                                    href="javascript:void(0)"
                                    class="btn-edit-mail btn btn-warning btn-xs waves-effect waves-light"><i
                                        class="fa fa-edit"> </i></a>
                            </div>
                            @endif
                        </td>
                        <td>{{ $v->User_RegisteredDatetime }}</td>
                        <td>{{ $v->User_Parent }}</td>
                        <td width="200px">
                            <div style="overflow:auto;width:300px!important;height:60px">
                                {{ str_replace(',',', ', $v->User_Tree) }}</div>
                        </td>
                        <td>
                          <ul>
                          @foreach($address as $a)
                            <li style="list-style:decimal">
                              <span class="text-danger">{{$arr_address[$a->Address_Currency]}}: </span>
                              
                              <br> {{$a->Address_Address}}
                            </li>
                          @endforeach
                          </ul>
                        </td>
                        <td>
                            @if($v->User_EmailActive == 0)
                            <span class="badge badge-danger r-3 blink">Not
                                Active</span>
                            @else
                            <span class="badge badge-success r-3">Active</span>
                            @endif
                            @php
                            $enableKYC = App\Model\Profile::where('Profile_User',
                            $v->User_ID)->where('Profile_Status', 1)->first();
                            @endphp
                            @if(isset($enableKYC))
                            <span class="badge badge-success r-3">Verification
                                turned on</span>
                            @else
                            <span class="badge badge-danger r-3">Verification not
                                enabled</span>
                            @endif
                        </td>
                        <td>
                            @if($v->google2fa_User)
                            <a href="{{ route('system.admin.getDisableAuth', $v->User_ID) }}"
                                class="btn btn-danger btn-xs waves-effect waves-light"><i
                                    class="fa fa-trash"> </i>Delete</a>
                            @else
                            <span
                                class="btn btn-secondary btn-xs waves-effect waves-light"><i
                                    class="fa fa-ban"> </i>None</span>
                                    
                            @endif
                        </td>
                        <!-- <td>
                            @if($v->request_forgot) 
                                <span class="badge badge-danger r-3">Request forgot</span>
                            @endif
                        </td> -->
                        <td>
                            @if(Session('user')->User_Level != 10)
                            <a href="{{ route('system.admin.getLoginByID', $v->User_ID) }}"
                                class="bt-loginID btn btn-primary btn-xs waves-effect waves-light"
                                data-toggle="tooltip" title="Login"><i
                                    class="fa fa-sign-in"> </i>Login</a>
                            @endif
                            @if($v->User_EmailActive == 0)
                            <a href="{{ route('system.admin.getActiveMail', $v->User_ID) }}"
                                class="bt-loginID btn btn-success btn-xs waves-effect waves-light"
                                data-toggle="tooltip"><i
                                    class="fa fa-check"> </i>Active</a>
                            @endif
                            <a href="{{ route('system.admin.getResetPassword', $v->User_ID) }}"
                                class="bt-loginID btn btn-warning btn-xs waves-effect waves-light"
                                data-toggle="tooltip" title="Reset Password"><i
                                    class="fa fa-sign-in"> </i>Reset Password</a>
                          	@php
                          		$checkAgency = App\Model\Money::checkBuyAgency($v->User_ID);
                          	@endphp
                          	@if(!$checkAgency)
                            <a href="{{ route('admin.getSetAgency', $v->User_ID) }}"
                                class="bt-loginID btn btn-info btn-xs waves-effect waves-light"
                                data-toggle="tooltip" title="Set Agency"><i
                                    class="fa fa-sign-in"> </i>Set Agency</a>
                          	@endif
                        </td>
                        <td>
                            <a href="{{ route('system.admin.onOffFunction', [
                                    'id' => $v->User_ID,
                                    'key' => 0
                                ]) }}"
                                class="bt-loginID {{$v->User_Lock_Swap == 1? 'btn-danger': 'btn-success'}} btn-xs waves-effect waves-light"
                                data-toggle="tooltip" title="Login" onclick="return confirm('Are you sure to delete?');"> Swap</a>
                            <a href="{{ route('system.admin.onOffFunction', [
                                'id' => $v->User_ID,
                                'key' => 1
                            ]) }}"
                                class="bt-loginID {{$v->User_Lock_Transfer == 1? 'btn-danger': 'btn-success'}} btn-xs waves-effect waves-light"
                                data-toggle="tooltip" title="Login" onclick="return confirm('Are you sure to delete?');"> Transfer</a>
                            <a href="{{ route('system.admin.onOffFunction', [
                                'id' => $v->User_ID,
                                'key' => 2
                            ]) }}"
                                class="bt-loginID {{$v->User_Lock_Withdraw == 1? 'btn-danger': 'btn-success'}} btn-xs waves-effect waves-light"
                                data-toggle="tooltip" title="Login" onclick="return confirm('Are you sure to delete?');"> Withdraw</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{$user_list->appends(request()->input())->links('system.layout.Pagination')}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')

<script  type="text/javascript">
  var ex = flatpickr('#date-picker-3');
  $(document).ready(function() {
    $('#member-list').DataTable({
        "bLengthChange": false,
        "searching": false,
        "paging": false
        });
  });
  $(document).ready(function() {
        $('.bt-loginID').click(function(e) {
            if ($('.bt-loginID').hasClass("disabled")) {
                event.preventDefault();
            }
            $('.bt-loginID').addClass("disabled");
        });
        var arr_email = [];
        $('#member-list').on('click', '.btn-edit-mail', function(){
            let id_user = $(this).data('id_user');
            var html_edit_mail = "<input id=\"input-mail-"+id_user+"\" type=\"text\" class=\"edit-email-input\" style=\"color:#000000\" value=\""+$('#input-email-'+id_user).text()+"\">";
            arr_email[id_user] = $('#input-email-'+id_user).text();
            let html_action_mail = "<a data-id_user='"+id_user+"' href=\"javascript:void(0)\" class=\"btn-disable-mail btn btn-warning btn-xs waves-effect waves-light\"><i class=\"fa fa-edit\"> </i></a>  <a data-id_user='"+id_user+"' href=\"javascript:void(0)\" class=\"btn-save-mail btn btn-success btn-xs waves-effect waves-light\"><i class=\"fa fa-save\"> </i></a>"
            $('#action-email-'+id_user).html(html_action_mail);
            $('#input-email-'+id_user).html(html_edit_mail);
        });
        $('#member-list').on('click', '.btn-disable-mail', function(){
            let id_user = $(this).data('id_user');
            let html_edit_mail =  arr_email[id_user];
            let html_action_mail = "<a data-id_user='"+id_user+"' href=\"javascript:void(0)\" class=\"btn-edit-mail btn btn-warning btn-xs waves-effect waves-light\"><i class=\"fa fa-edit\"> </i></a>"
            $('#action-email-'+id_user).html(html_action_mail);
            $('#input-email-'+id_user).html(html_edit_mail);
        });
        $('#member-list').on('click', '.btn-save-mail', function(){
            let id_user = $(this).data('id_user');
            let html_edit_mail = $('#input-mail-'+id_user).val();
            let html_action_mail = "<a data-id_user='"+id_user+"' href=\"javascript:void(0)\" class=\"btn-edit-mail btn btn-warning btn-xs waves-effect waves-light\"><i class=\"fa fa-edit\"> </i></a>"
            $('#action-email-'+id_user).html(html_action_mail);
            $.ajax({
                url : '{{ route('system.admin.getEditMailByID') }}',
                type : "POST", 
                dataType:"json", 
                data : { 
                    _token: "{{ csrf_token() }}",
                    id_user : id_user,
                    new_email : html_edit_mail
                },
                success : function (result){
                    if(!result){
                        html_edit_mail = arr_email[id_user];  
                        toastr.error('Email Already Exists', 'Error!', {timeOut: 3500});
                    }
                    else{   
                        if(result == -1){
                            html_edit_mail = arr_email[id_user];
                            toastr.error('ID Does Not Exist', 'Error!', {timeOut: 3500});
                        }
                        else{
                            html_edit_mail = $('#input-mail-'+id_user).val();
                            toastr.success('Updated Email', 'Success!', {timeOut: 3500});
                        }
                    }
                    $('#input-email-'+id_user).html(html_edit_mail);
                }
            });
        });
    });
</script>
@endsection