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
</style>
@endsection
@section('content')
@if(!$data['isBuyAgency'])
<div class="body-agency">
  <div class="grid grid-cols-12 flex-1 ">
    <div class="col-span-12 xl:col-span-1"></div>
    <div class="col-span-12 xl:col-span-10 grid grid-cols-12 gap-8 gap-y-10">
      <div class="col-span-12 lg:col-span-6 flex flex-col sefl-center items-center justify-center">
        <span class="text-about text-center">
          {{__('agency.buy_agency')}}
        </span>
        <form action="{{route('buyAgency')}}" method="post">
          @csrf
          <button type="submit" class="btn-buy button btn">{{__('agency.buy_now')}} $100</button>
        </form>

      </div>
      <div class="col-span-12 lg:col-span-6 card-dashboard-mini">

        <label for="">{{__('agency.register_link')}}</label>
        <div class=" register">
          <input type="text" id="linkref" class="form-control input-copy" readonly
            value="{{route('getRegister')}}?ref={{Session('user')->User_ID}}">
          <button class="btn button btn-copy" onclick="Copy_link('linkref')">{{__('agency.copy')}}</button>
        </div>
        <label for="">{{__('agency.sponsor_code')}}</label>
        <div class="sponsor ">
          <input type="text" id="sponsorcode" class="form-control input-copy" readonly
            value="{{$data['user']->User_ID}}">
          <button class="btn button btn-copy" onclick="Copy_link('sponsorcode')">{{__('agency.copy')}}</button>
        </div>

      </div>
    </div>
  </div>
</div>
@else
<div class="body-join grid grid-cols-1 gap-6 gap-y-20 mb-20">
  <div class="grid grid-cols-12 gap-8">
    <div class="col-span-12 lg:col-span-1"></div>
    <div class="col-span-12 lg:col-span-4 grid grid-cols-1 gap-8 self-center items-center">
      <div class="col-span-1">
        <div class="card-dashboard-mini link">
          <div class="title mb-2"> {{__('agency.invitation_link')}}</div>
          <div class=" register">
            <input type="text" id="linkref" class="form-control input-copy" readonly
              value="{{route('getRegister', ['ref'=>$data['user']->User_ID])}}">
            <button class="btn button btn-copy" onclick="Copy_link('linkref')">{{__('agency.copy')}}</button>
          </div>
        </div>
      </div>
      <div class="col-span-1">
        <div class="card-dashboard-mini icon">
          <div class="flex  justify-around ">
            <div class="flex-1 flex self-center items-center  justify-center" style="flex: auto;">
              <img src="{{$data['Rank']['Image']}}" style="max-width: 110px;max-height: 110px;">
  
            </div>
            <div class="flex-1   justify-center flex flex-col">
              <div class="title mb-2">{{__('agency.level_agency')}}</div>
              <div class="content-detail">{{$data['Rank']['Name']}}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-span-12 lg:col-span-6 grid grid-cols-1 gap-8 self-center items-center">
      <div class="col-span-1 grid grid-cols-1 gap-6 self-center items-center card-dashboard-mini">
          <div class="col-span-1 static-agency">
            <span>{{__('agency.f1s_total_trade')}}</span>
            <span>{{number_format($data['Total']['F1Trade'],2)}} USDT</span>
          </div>
          <div class="col-span-1 static-agency">
            <span>{{__('agency.f1s_total_agency')}}</span>
            <span>{{$data['Total']['F1Agency']}} {{__('agency.member')}}</span>
          </div>
          <div class="col-span-1 static-agency">
            <span>{{__('agency.total_commission')}} </span>
            <span>{{number_format($data['Total']['Commission'],2)}} USDT</span>
          </div>
          <div class="col-span-1 static-agency">
            <span>{{__('agency.total_profit_company')}} (0.2%)</span>
            <span>{{number_format($data['Total']['ProfitCompany'],2)}} USDT</span>
          </div>
          <div class="col-span-1 static-agency">
            <span>{{__('agency.pending_agency_commission')}}</span>
            <span>{{number_format($data['Pending']['Agency'],2)}} USDT</span>
          </div>
          <div class="col-span-1 static-agency">
            <span>{{__('agency.pending_rank_commission')}}</span>
            <span>{{number_format($data['Pending']['Rank'],2)}} USDT</span>
          </div>
    
      </div>
    </div>
  </div>

  @if(Session('user')->User_Level == 1 && 0)
  <div class="grid grid-cols-12 gap-8">
    <div class="col-span-12 lg:col-span-2"></div>
    <div class="col-span-12 lg:col-span-4">
      <div class="card-dashboard-mini icon">
        <div class="flex  justify-around ">
            <div class="flex-1 flex self-center items-center  justify-center" style="flex: auto;">
              <img src="{{$data['Rank']['Image']}}" style="max-width: 110px;max-height: 110px;">
  
            </div>
            <div class="flex-1   justify-center flex flex-col">
              <div class="title mb-2">{{__('agency.level_agency')}}</div>
              <div class="content-detail">{{__('agency.rank')}} {{$data['Rank']['Name']}}</div>
            </div>
          </div>
      </div>
    </div>
    <div class="col-span-12 lg:col-span-4">
      <div class="card-dashboard-mini link">
        <div class="title mb-2"> {{__('agency.invitation_link')}}</div>
        <div class=" register">
          <input type="text" id="linkref" class="form-control input-copy" readonly
            value="{{route('getRegister', ['ref'=>$data['user']->User_ID])}}">
          <button class="btn button btn-copy" onclick="Copy_link('linkref')">{{__('agency.copy')}}</button>
        </div>
      </div>
    </div>
  </div>
  <div class="grid grid-cols-12 gap-8">
    <div class="col-span-12 lg:col-span-3">
      <div class="card-dashboard-mini static">
        <div class="title mb-2">{{__('agency.f1s_total_trade')}}</div>
        <div class="content-detail">{{number_format($data['Total']['F1Trade'],2)}} USDT</div>
      </div>
    </div>
    <div class="col-span-12 lg:col-span-3">
      <div class="card-dashboard-mini static">
        <div class="title mb-2">{{__('agency.f1s_total_agency')}}</div>
        <div class="content-detail">{{$data['Total']['F1Agency']}}</div>
      </div>
    </div>
    <div class="col-span-12 lg:col-span-3">
      <div class="card-dashboard-mini static">
        <div class="title mb-2">{{__('agency.total_commission')}} </div>
        <div class="content-detail">{{number_format($data['Total']['Commission'],2)}} USDT</div>
      </div>
    </div>
    <div class="col-span-12 lg:col-span-3">
      <div class="card-dashboard-mini static">
        <div class="title mb-2">{{__('agency.total_profit_company')}} (0.2%)</div>
        <div class="content-detail">{{number_format($data['Total']['ProfitCompany'],2)}} USDT</div>
      </div>
    </div>
  </div>
  @else
  @endif
  <div class="grid grid-cols-12">
    <div class="col-span-12  xl:col-span-1">

    </div>
    <div class="col-span-12 xl:col-span-10">
      <div class="card-dashboard-mini member reponsive">
        <table id="member-list" class="display reponsive datatable" style="width:100%">
          <thead>
            <tr>
              <th>{{__('agency.user_id')}}</th>
              <th>{{__('agency.email')}}</th>
              <th>{{__('agency.agency_level')}}</th>
              <th>F</th>
              <th>{{__('agency.total_trade')}}</th>
              <th>{{__('agency.parent')}}</th>
            </tr>
          </thead>
          <tbody>
           	@php
              $fromDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
              $toDate = date('Y-m-d H:i:s');
            @endphp
            @foreach($data['member'] as $value)
            <tr>
              <th>{{$value->User_ID}}</th>
              <th>{{$value->User_Email}}</th>
              <th>{{$value->user_agency_level_Name}}</th>
              <th>{{$value->f}}</th>
              @php
              	$totalTrade = App\Model\GameBet::getTotalBet($value->User_ID, $fromDate, $toDate);
              @endphp
              <th>${{number_format(($totalTrade->totalBet ?? 0) , 2)}}</th>
              <th>{{$value->User_Parent}}</th>
            </tr>
            @endforeach
          </tbody>

        </table>
      </div>
    </div>
  </div>
</div>
@endif
<div class="bottom-agency grid grid-cols-12 gap-6">
  <div class="col-span-12 xl:col-span-2"></div>
  <div class="col-span-12 lg:col-span-4 xl:col-span-3  flex self-center ml-10 lg:ml-0 items-center">
    <img src="exchange/img/icon/agency.png" alt="" class="mr-2">
    <div class=" flex flex-col text-left tour">
      <span class="title">{{__('agency.invite_friends')}}</span>
      <span class="content-text">{{__('agency.invite_friends_to_register_ig_trade_through_the_link')}}</span>
    </div>
  </div>
  <div class="col-span-12 lg:col-span-4 xl:col-span-3 flex self-center ml-10  lg:ml-0 items-center">
    <img src="exchange/img/icon/agency.png" alt="" class="mr-2">
    <div class=" flex flex-col text-left tour">
      <span class="title">{{__('agency.friends_sign_up')}}</span>
      <span class="content-text">{{__('agency.friends_accept_the_invitation_complete_registration_and_play')}}</span>
    </div>
  </div>
  <div class="col-span-12 lg:col-span-4 xl:col-span-3  flex self-center ml-10 lg:ml-0 items-center">
    <img src="exchange/img/icon/agency.png" alt="" class="mr-2">
    <div class=" flex flex-col text-left tour">
      <span class="title">
        {{__('agency.get_a_corresponding_proportion_of_commission')}}</span>
      <span class="content-text">{{__('agency.easily_get_commission')}}</span>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
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

</script>
@endsection