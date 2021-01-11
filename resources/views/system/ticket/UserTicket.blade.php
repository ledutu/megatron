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
  .form-group textarea.form-control {
    height: auto!important;
 
  }

</style>
@endsection
@section('content')
<div class="grid grid-cols-12 gap-8">
  <div class="col-span-12 lg:col-span-4 grid grid-cols-1 gap-6 card-dashboard-mini">
    <h2 class="title text-center">{{__('support.ticket')}}</h2>
    <form action="{{route('postTicket')}}" method="post" class="grid grid-cols-1 gap-8">
      @csrf
      <div class="form-group">
        <label for="">{{__('support.subject')}}</label>
        <select name="subject" class="form-control" required>
          @foreach($subject as $s)
          <option value="{{$s->ticket_subject_id}}" class="text-black">
            {{__('ticket.'.$s->ticket_subject_name)}}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="email">{{__('support.content')}} (*) </label>
        <textarea placeholder="{{__('support.description_your_problems')}}" name="content" id="commenttextarea" cols="30" rows="5"
          class="form-control" required></textarea>
      </div>
      <div class="form-group mb-0 flex justify-center self-center items-center">
        <button type="submit" class="ladda-button btn btn-search waves-effect waves-light" data-style="slide-down">
          <span class="btn-label"><i class="fas fa-paper-plane"></i> </span>{{__('support.send')}}
        </button>
      </div>
    </form>
  </div>
  <div class="col-span-12 lg:col-span-8">
    <div class="card-dashboard-mini responsive">
      <div class="table-wrap">
        <div class="table-responsive">
          <div style="clear:both"></div>
          <table id="member-list" class=" dt-responsive table table-striped table-bordered table-responsive"
            cellspacing="0" width="100%">
            <thead>
              <tr>
              <tr>
                <th data-toggle="true">
                  {{__('support.ticket_id')}}
                </th>
                <th>
                  {{__('support.subjects')}}
                </th>
                <th data-hide="phone">
                  {{__('support.status')}}
                </th>
                <th data-hide="phone,tablet">
                  {{__('support.datetime')}}
                </th>
                <th>{{__('support.action')}}</th>
              </tr>
              </tr>
            </thead>
            <tbody>
              @foreach($ticket as $t)

              @php
              $findlastRep = DB::table('ticket')->where('ticket_ReplyID',$t->ticket_ID)->orderBy('ticket_ID',
              'DESC')->first();
              if(!$findlastRep){
              $status = __('ticket.waiting');
              $class = 'warning';
              }else{
              $getInfo = App\Model\User::whereIn('User_Level', [1,2,3])->where('User_ID',
              $findlastRep->ticket_User)->first();
              if($getInfo){
              $status = __('ticket.replied');
              $class = 'success';
              }else{
              $status = __('ticket.waiting');
              $class = 'warning';
              }
              }
              @endphp
              <tr>
                <td>{{$t->ticket_ID}}</td>

                <td>{{__('ticket.'.$t->ticket_subject_name)}}</td>
                <td>
                  <button class="btn btn-rounded btn-{{$class}}">{{$status}}</button>
                </td>
                <td>{{$t->ticket_Time}}</td>

                <td><a href="{{route('getTicketDetail',$t->ticket_ID)}}" class="btn btn-primary btn-rounded">{{__('ticket.detail')}}</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>

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