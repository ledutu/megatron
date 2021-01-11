@extends('system.layout.Master')
@section('css')
<style>
  .card-dashboard-mini {
    min-height: 150px;
    background: rgb(35 31 32 / 0.6);
    margin: 0 2px;
    border: 1px solid #FFF200;
    width: 100%;
    border-radius: 15px;
    padding: 20px;
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
</style>
@endsection
@section('content')
<div class="grid grid-cols-12 gap-8">
  <div class="col-span-12 lg:col-span-2"></div>
  <div class="col-span-12 lg:col-span-8">
    <div class="card-dashboard-mini responsive">
      <div class="table-wrap">
        <div class="table-responsive">
          {{$ticket->appends(request()->input())->links('system.layout.Pagination')}}

          <div style="clear:both"></div>
          <table id="member-list" class=" dt-responsive table table-striped table-bordered table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th>
                  Ticket ID
                </th>
                <th>
                  Subjects
                </th>
                <th>
                  Email
                </th>
                <th data-hide="phone">
                  Status
                </th>
                <th data-hide="phone,tablet">
                  DateTime
                </th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($ticket as $t)
              @php
              $hide = 1;
              $checkHideStatus =
              DB::table('ticket')->where('ticket_ID',$t->ticket_ID)->where('ticket_Status',-1)->first();
              if($checkHideStatus){
              $hide = 0;
              $messNum = 0;
              $status = 'Hidden';
              $class = 'light';
              }
              else {
              $findlastRep =
              DB::table('ticket')->where('ticket_ReplyID',$t->ticket_ID)->orderBy('ticket_ID',
              'DESC')->first();
              $messNum = 0;
              $getComment = [];
              if(!$findlastRep){
              $getComment =
              DB::table('ticket')->Where('ticket_ID',$t->ticket_ID)->where('ticket_Status',0)->get();
              $messNum = count($getComment);
              $status = 'Waiting';
              $class = 'warning';

              }else{
              $getInfo = App\Model\User::whereIn('User_Level',
              [1,2,3])->where('User_ID',$findlastRep->ticket_User)->first();

              if($getInfo){
              $messNum = 0;
              $status = 'Replied';
              $class = 'success';
              }else{
              $keyItem = 1;
              $getListReplyed =
              DB::table('ticket')->Where('ticket_ReplyID',$t->ticket_ID)->orderBy('ticket_ID',
              'DESC')->get();
              foreach ($getListReplyed as $item) {
              $findUserAdmin = App\Model\User::where('User_Level',
              1)->where('User_ID',$item->ticket_User)->first();
              if(!$findUserAdmin){
              //don't User Admin
              $messNum = $keyItem;
              $keyItem++;
              }
              else {
              //is User Admin
              break;
              }
              }
              $status = 'Waiting';
              $class = 'warning';
              }
              }
              }
              @endphp
              <tr>
                <td>{{$t->ticket_ID}}</td>
                <td>{{$t->ticket_subject_name ? $t->ticket_subject_name : ''}}
                </td>
                <td>{{$t->User_Email}}</td>
                <td>
                  <span class="label label-rounded label-{{$class}}">{{$status}}</span>
                </td>
                <td>{{$t->ticket_Time}}</td>
                <td>
                  <a href="{{route('getTicketDetail',$t->ticket_ID)}}" class="btn btn-primary btn-rounded">Details <span class="badge badge-danger">{{$messNum}}</span></a>
                  <a onclick="javascript:return confirm('ID: #{{$t->ticket_ID}} Are you sure?');" href="{{route('getStatusTicketAdmin',$t->ticket_ID)}}" class="btn btn-danger btn-rounded" style="width:auto;">{{ $hide == 1 ? 'Allow Hidden' : 'Been Hidden'}}</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          {{$ticket->appends(request()->input())->links('system.layout.Pagination')}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    $('#member-list').DataTable({
      "bLengthChange": false,
      "searching": false,
      "paging": false,
       "order": [[ 0, "desc" ]]
    });
  });
</script>

@endsection