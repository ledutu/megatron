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

  .bottom-agency {
    position: fixed;
    bottom: 0;
    right: 0;
    background: black;
    height: 90px;
    margin-left: 90px;
    left: 0;
  }

  .body-agency {
    height: calc(100vh - 300px);
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .btn-buy {
    background: linear-gradient(90deg, #F4EB25, #F4C41B) !important;
    color: black;
    font-size: larger;
    font-weight: 700;
    width: 200px;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    align-self: center;
    margin-top: 10px;
    margin-bottom: 10px;
    height: 50px;
  }

  .text-about {
    font-size: 28px;
    font-weight: bold;
  }

  .register,
  .sponsor {
    position: relative;
    height: 40px;
    margin-bottom: 20px;
  }

  .btn-copy {
    height: 35px;
    width: 100px;
    background-color: #FFF200;
    color: black;
    border-radius: 10px;
    position: absolute;
    top: 0;
    right: 2px;
    bottom: 0;
    font-size: initial;
    font-weight: 700;
    margin: auto;

  }

  .input-copy {
    background: transparent !important;
    border: #F4EB25 1px solid;
    color: white;
    padding-left: 5px;
    position: absolute;
    height: 40px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;

  }

  .sponsor {
    width: 50%;
  }

  .card-dashboard-mini label {
    font-size: 17px;
    font-weight: 700;
  }

  .tour .title {
    font-size: 17px;
    font-weight: 700;
    color: white;
  }

  .tour .content-text {
    font-size: 15px;
    font-weight: 500;
    color: white;
  }

  .mr-2 {
    margin-right: 0.5rem;
  }

  .card-dashboard-mini.icon .title,
  .card-dashboard-mini.link .title,
  .card-dashboard-mini.static .title {
    font-size: 17px;
    font-weight: 700;
    text-align: center;
  }

  .card-dashboard-mini.icon .content-detail,
  .card-dashboard-mini.static .content-detail {
    font-size: 17px;
    font-weight: 700;
    text-align: center;
  }

  .card-dashboard-mini.static {
    display: flex;
    align-items: center;
    align-self: center;
    justify-content: center;
    flex-direction: column;
  }

  .mb-2 {
    margin-bottom: 0.5rem;
  }

  .card-dashboard-mini.member {
    padding: 0;
    border-radius: 2px;
  }

  .card-dashboard-mini.icon {
    display: flex;
    /* align-items: center; */
    /* align-self: center; */
    justify-content: center;
    flex-direction: column;
  }
  .static-agency{
        display: flex;
    justify-content: space-between;
    font-size: 22px;
    border-bottom: 1px solid black;
    border: 1px solid;
    border-image-source: linear-gradient(90deg, #F4EB25, #F4C41B);
    border-image-slice: 1;
    border-left: 0;
    border-right: 0;
    border-top: 0;
    align-items: center;
	}
  @media (max-width:768px){
    .static-agency{
     flex-direction: column;
    }
  }
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
<div class="body-join grid grid-cols-1 gap-6 gap-y-20 mb-20">
  <div class="col-span-1 grid grid-cols-12">
    <div class="col-span-12 lg:col-span-4"></div>
    <div class="col-span-12 lg:col-span-4 grid grid-cols-1 gap-6 card-dashboard-mini">
      <h2 class="title text-center">{{__('promotion_insurance.submit_form_insurance')}}</h2>
      <form action="{{route('system.postPromotionInsurrance')}}" method="post" class="grid grid-cols-1 gap-8">
        @csrf
        <div class="form-group">
          <label for="">{{__('promotion_insurance.from_main_balance')}}</label>
          <input type="text" value="{{number_format($balance,2)}}" class="form-control" readonly>
        </div>
        <div class="form-group">
          <label for="">{{__('promotion_insurance.amount_insurance')}}</label>
          <input type="number" step="any" name="amount" min="500" id="insurance-amount" class="form-control" placeholder="{{__('promotion_insurance.enter_amount_insurance_live_account')}}">
        </div>
        <div class="form-group">
          <label for="">{{__('promotion_insurance.amount_fee')}}</label>
          <input type="number" step="any" name="fee" min="20" id="insurance-fee" class="form-control" placeholder="0" readonly>
        </div>
        {{--
        <div class="form-group">
          @include('system.layout.TimeInsurrance')
        </div>
        --}}
        <div class="form-group">
          <label for="">{{__('promotion_insurance.time_limit')}}</label>
          <select class="form-control" name="days" id="insurance-days">
              @foreach($feeInsur as $k => $v)
                <option value="{{$k}}" style="color:black!important;"> {{$k.""}} Days ({{$v*100}}% Fee)</option>
              @endforeach
          </select>
        </div>
        <div class="form-group mb-0 flex justify-center self-center items-center">
          <button type="submit" class="ladda-button btn btn-search waves-effect waves-light" data-style="slide-down">
            <span class="btn-label"><i class="fas fa-paper-plane"></i> </span>{{__('promotion_insurance.submit')}}
          </button>
        </div>
      </form>
    </div>
  </div>
  <div class="grid grid-cols-12">
    <div class="col-span-12 lg:col-span-1"></div>
    <div class="col-span-12 xl:col-span-10">
      <div class="card-dashboard-mini member reponsive">
        <table id="member-list" class="display reponsive datatable" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>{{__('promotion_insurance.user_id')}}</th>
              <th>{{__('promotion_insurance.amount')}}</th>
              <th>{{__('promotion_insurance.time_limit')}}</th>
              <th>{{__('promotion_insurance.start_date')}}</th>
              <th>{{__('promotion_insurance.end_date')}}</th>
              <th>{{__('promotion_insurance.status')}}</th>
              <th>{{__('promotion_insurance.action')}}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($getHistory as $v)
            <tr>
              <td>{{$v->id}}</td>
              <td>{{$v->user_id }}</td>
              <td>{{ number_format($v->amount,2) }}</td>
              <td>{{$v->days??30}} Days ({{($feeInsur[$v->days] ?? 0.08)*100}}% Fee) </td>
              <td>{{$v->created_time }}</td>
              <td>{{$v->expired_time }}</td>
              <td>
                @if($v->status == 0)
                <span class="badge badge-warning">Pending</span>
                @elseif($v->status == 1)
                <span class="badge badge-info">Expired</span>
                @else
                <span class="badge badge-danger">Canceled</span>
                @endif
              </td>
              <td>
                {{--
                @if($v->status == 0)
                <button type="button" class="btn btn-success min-width-125 d-inline-block btn-increa" onclick="ShowModal('#increa-insurrance')" data-days="{{$v->days}}" data-amount="{{$v->amount}}" data-sub="{{$v->subAccount}}" data-id="{{$v->id}}"> Increament Amount</button>
                @endif
                --}}
              </td>
            </tr>
            @endforeach
          </tbody>

        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="increa-insurrance" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content" style="
   		 background: #1a202c;
	">
      <div class="modal-body card-dashboard-mini text-white">
        <h2 class="title text-center" style="color:#fff!important;">Submit Increament Insurance</h2>
        <form action="{{route('system.postIncreaAmount')}}" method="post" class="grid grid-cols-1 gap-8">
          @csrf
          <input type="hidden" name="id" id="increa-id">
          <input type="hidden" name="days" id="increa-days">
          <div class="form-group">
            <label for="">From Main Balance</label>
            <input type="text" value="{{number_format($balance,2)}}" class="form-control" readonly>
          </div>
          <div class="form-group">
            <label for="">Amount Old</label>
            <input type="number" step="any" class="form-control" id="increa-amount-old" readonly>
          </div>
          <div class="form-group">
            <label for="">Time Limit</label>
            <input type="text"  class="form-control" id="increa-time-limit" readonly>
          </div>
          <div class="form-group">
            <label for="">Amount Increament</label>
            <input type="number" step="any" name="amount" min="1000" id="increa-amount" class="form-control" placeholder="Enter Amount">
          </div>
          <div class="form-group">
            <label for="">Amount Fee</label>
            <input type="number" step="any" name="fee" min="80" id="increa-fee" class="form-control" placeholder="Amount Fee" readonly>
          </div>
          <div class="form-group mb-0 flex justify-center self-center items-center">
            <button type="submit" class="ladda-button btn btn-search waves-effect waves-light" data-style="slide-down">
              <span class="btn-label"><i class="fas fa-paper-plane"></i> </span>{{__('promotion_insurance.submit')}}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
  
  function hideModal(){
    $('.modal').modal('hide');
  }
  function ShowModal(id){
   $(id).modal('show');
  }
  $(document).ready(function () {
    var _fee = {!! json_encode($feeInsur) !!};
    $('#increa-amount').keyup(function(){
      	updateIncreaInsurance();
    });
    function updateIncreaInsurance(){
      _amount = $('#increa-amount').val();
      _days = $('#increa-days').val();
      _amount_fee = _amount*_fee[_days];
      $('#increa-fee').val(_amount_fee);
    }
    $('#insurance-amount').keyup(function(){
      	updateInsurance();
    });
    $('#insurance-days').change(function(){
      	updateInsurance();
    });
    function updateInsurance(){
      _amount = $('#insurance-amount').val();
      _days = $('#insurance-days').val();
      _amount_fee = _amount*_fee[_days];
      
      $('#insurance-fee').val(_amount_fee);
    }
    
    $('.btn-increa').click(function(){
      _id = $(this).data('id');
      _amount = $(this).data('amount');
      _days = $(this).data('days');
      _time_limit = _days+" Days ("+(_fee[_days]*100)+"% Fee)";
      $('#increa-days').val(_days);
      $('#increa-time-limit').val(_time_limit);
      $('#increa-amount-old').val(_amount*1);
      $('#increa-id').val(_id);
    });
    $('#member-list').DataTable({
      "bPaginate": true,
      "bLengthChange": false,
      "bFilter": true,
      "searching": false,
      "bInfo": false,
      "bAutoWidth": false
    });
  });

</script>
@endsection