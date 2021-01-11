@extends('system.layout.Master')
@section('css')
<style>
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
    height: 150px;
    background: rgb(35 31 32 / 0.6);
    margin: 0 2px;
    border: 1px solid #FFF200;
    width: 100%;
    border-radius: 15px;
  }

  .card-table-static {
    background: rgb(35 31 32 / 0.6);
    margin: 0 2px;
    border: 1px solid #FFF200;
    width: 100%;
    margin: auto;
    border-radius: 2px;
  }

  #winlose {
    height: 150px;
    width: 150px;
    margin: auto;
  }

  .mb-5 {
    margin-bottom: 2.25rem;
  }

  .mt-5 {
    margin-top: 2.25rem;
  }

  .summary .title,
  .card-dashboard-static div .title {
    font-size: 17px;
    font-weight: 600;
    text-align: center;
    margin: auto;
    display: flex;
    justify-content: center;
    flex-direction: column;
  }

  .card-dashboard-mini div .title {
    font-size: 17px;
    font-weight: 600;
    text-align: left;
    display: flex;
    justify-content: flex-start;

  }

  .detail .span-circle {
    height: 20px;
    width: 20px;
    border: 5px solid;
    border-radius: 50%;
    margin-right: 5px;
  }

  .detail-tatic .detail:nth-child(1) .span-circle {
    border-color: #00b04f;
  }

  .detail-tatic .detail:nth-child(2) .span-circle {
    border-color: #ff0000;
  }

  .detail-tatic .detail:nth-child(3) .span-circle {
    border-color: #ffbf00;
  }

  .detail {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    align-self: center;
    margin: 10px 0px;
  }

  .detail .span-text {
    font-size: 15px;
    font-weight: 700;

  }

  .summary .progress_summary {
    margin-top: 15px;
    width: 100%;
    height: 40px;
    position: relative;
  }

 

  .summary .progress_summary .up {
    position: absolute;
    bottom: 0;
    left: 0;
  }

  .summary .progress_summary .down {
    position: absolute;
    bottom: 0;
    right: 0;
  }

  .summary .progress_summary .down span,
  .summary .progress_summary .up span {
    font-size: 19px;
    font-weight: 800;
  }

  .summary .progress_summary .down span.percent {
    color: #ff0000;

  }

  .summary .progress_summary .up span.percent {
    color: #00b04f;
  }
  .header-tran{
    justify-content: space-between;
    align-self: center;
    align-items: center;
    align-content: center;
    justify-items: center;
    justify-self: center;
  }
  .btn-search{
    background: linear-gradient(90deg, #F4EB25, #F4C41B) !important;
    color: black;
    font-size: larger;
    font-weight: 700;
    width: max-content;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    align-self: center;

  }
  .pick-date{
    border-top: none;
    border-left: none;
    background: #eeeeee1f;
    margin-right: 5px;
    font-size: 12px;
    font-weight: 700;
    color: white;
  }
  
.progress2 {
  border-radius: 30px;
  background-color: #fff;
}

.progress-bar2 {
  height: 11px;
  border-radius: 30px;
  transition: 0.4s linear;
  transition-property: width, background-color;
}

.progress-moved .progress-bar2 {
  background-color: #f3c623;
  background: #f3c623;
    box-shadow: 0 0 40px #f3c623;
}


.loader {
  --p: 0;
  animation: p 5s steps(100) infinite;
  counter-reset: p var(--p);
  font-size: 2.1em;
  position: absolute;
  bottom: 45px;
  left: 325px;
  color: #f3c623;
}




</style>

@endsection
@section('content')
<div class="grid grid-cols-2 gap-8 gap-y-20">
  <div class="col-span-2 grid grid-cols-12">
    <div class="col-span-12 xl:col-span-1"></div>
    <div class="grid grid-cols-2 gap-8 col-span-12 xl:col-span-10">
      <div class="col-span-2 lg:col-span-1">
        <div class="card-dashboard-static grid grid-cols-2 gap-0">
          <div class="col-span-2 grid grid-cols-2 gap-4">
            <div class="col-span-2 md:col-span-1">
              <div id="winlose" class="mb-5"></div>
            </div>
            <div class="col-span-2 md:col-span-1 detail-tatic self-center items-center">
              <div class="detail">
                <div class="span-circle"></div>
                <div class="span-text">{{__('dashboard.total_trade_win')}}</div>
              </div>
              <div class="detail">
                <div class="span-circle"></div>
                <div class="span-text">{{__('dashboard.total_trade_lose')}}</div>
              </div>
              <div class="detail">
                <div class="span-circle"></div>
                <div class="span-text">{{__('dashboard.total_trade_draw')}}</div>
              </div>
            </div>


          </div>
          <div class="col-span-2 grid grid-cols-2 gap-4">
            <div class="col-span-1 border-r-2">
              <span class="title">
                <span>{{__('dashboard.win_rate')}}</span>
                <span> {{$data['win_rate']}}%</span>
              </span>
            </div>
            <div class="col-span-1">
              <span class="title">
                <span>{{__('dashboard.total_trade')}}</span>
                <span> {{$data['total_amount_trade']}}</span>
              </span>
            </div>
          </div>

        </div>
      </div>
      <div class="col-span-2 lg:col-span-1 grid grid-cols-2 gap-4">
        <div class="col-span-2 lg:col-span-1">
          <div class="card-dashboard-mini grid grid-cols-3">
            <div class="col-span-1 flex flex-col self-center items-center">
              <img src="exchange/img/icon/das-1.png" alt="" srcset="">
            </div>
            <div class="col-span-2 flex flex-col self-center ">
              <span class="title">{{__('dashboard.net_profit')}}</span>
              <span class="title">$ {{$data['net_profit']}}</span>
            </div>
          </div>
        </div>
        <div class="col-span-2 lg:col-span-1">
          <div class="card-dashboard-mini grid grid-cols-3">
            <div class="col-span-1 flex flex-col self-center items-center">
              <img src="exchange/img/icon/das-2.png" alt="" srcset="">
            </div>
            <div class="col-span-2 flex flex-col  self-center">
              <span class="title">{{__('dashboard.total_income')}}</span>
              <span class="title">$ {{$data['total_income']}}</span>
            </div>
          </div>
        </div>
        <div class="col-span-2 summary">
          <div class="title">{{__('dashboard.transaction_summary')}}</div>
          <div class="progress_summary">
             <div class="progress2 progress-moved">
       			 <div class="progress-bar2" style="width:{{$data['up_percent']}}%"></div>
       			 <div class="loader" ></div>
     		 </div>
            <div class="up">
              <span class="text">{{__('dashboard.up')}}</span>
              <span class="percent">{{$data['up_percent']}}%</span>
            </div>
            <div class="down">
              <span class="text">{{__('dashboard.down')}}</span>
              <span class="percent">{{$data['down_percent']}}%</span>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <div class="col-span-2 grid grid-cols-12">
    <div class="col-span-12 xl:col-span-1"></div>
    <div class="col-span-12 xl:col-span-10 grid grid-cols-1">
      <div class="col-span-1 ">
        <div class="flex header-tran">
          <div class="float-left ">
            <h2 >{{__('dashboard.history_transaction')}}</h2>
          </div>
        
          <div class="seach-date float-right ">
            <form action="{{route('getDashboard')}}" method="get" class="flex self-center items-center">
              @csrf
              <input id="flatpickr" class="form-control text-white pick-date" name="from" placeholder="{{__('dashboard.select_date_from')}}">
              <input id="flatpickr-2" class="form-control text-white pick-date" name="to" placeholder="{{__('dashboard.select_date_to')}}">
              <button class="btn-search btn button">{{__('dashboard.search')}}</button>
            </form>
     
          </div>
        </div>
      </div>
      <div class="col-span-1">
        <div class="card-table-static reponsive">
          <table id="transaction" class="display reponsive datatable" style="width:100%">
            <thead>
              <tr>
                <th>{{__('dashboard.order_id')}}</th>
                <th>{{__('dashboard.asset')}}</th>
                <th>{{__('dashboard.datetime')}}</th>
                <th>{{__('dashboard.option_type')}}</th>
                <th>{{__('dashboard.log')}}</th>
                <th>{{__('dashboard.trade_amount')}}</th>
                <th>{{__('dashboard.payout')}}</th>
              </tr>
            </thead>
            <tbody>
                @foreach($data['gameBet'] as $item)
                  <tr>	
                      <td>{{$item->_id}}</td>
                      <td>{{$item->GameBet_SubAccountEndBalance+0}}</td>
                      <td>{{date('Y-m-d H:i:s',$item->GameBet_datetime)}}</td>
                      <td>{{$item->GameBet_Type}}</td>
                      <td>{{$item->GameBet_Log}}</td>
                      <td>{{$item->GameBet_Amount}}</td>
                      <td>{{$item->GameBet_AmountWin}}</td>

                      <!-- <td>
                        @if(($item->Money_MoneyAction == 17 || $item->Money_MoneyAction == 20) && $item->Money_Confirm == 0)
                          <a href="{{route('system.getResendMailConfirm', $item->Money_ID)}}"><button type="button" class="btn btn-rounded btn-primary btn-xs">Choose Payment</button></a>
                        @endif
                    </td> -->

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
<script src="exchange/js/echarts.js?v=3"></script>
<script type="text/javascript">
var example = flatpickr('#flatpickr');
var example_2 = flatpickr('#flatpickr-2');
  $(document).ready(function () {
    $('#transaction').DataTable({
      "bPaginate": true,
      "bLengthChange": false,
      "bFilter": true,
      "searching": false,
      "bInfo": false,
      "bAutoWidth": false,
      "order": [[ 0, "desc" ]]
    });

  });



  var colorPalette = ['#00b04f', '#ff0000', '#ffbf00'];
  var defaul_null = ['#ffbf00'];
  var data_value = [{ value: {{$data['total_trade']['Win']}}, name: 'Win' },
  { value: {{$data['total_trade']['Lose']}}, name: 'Loser' },
  { value: {{$data['total_trade']['Draw']}}, name: 'Draw' },]
  var data_null = [{ value: 0, name: 'Win ' },]
  var option = {
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    series: [
      {
        name: 'Trade Stat',
        type: 'pie',
        color: colorPalette,
        radius: ['60%', '80%'],
        avoidLabelOverlap: false,
        label: {
          show: false,
          position: 'center'
        },
        emphasis: {
          label: {
            show: true,
            fontSize: '15',
            fontWeight: 'bold'
          }
        },
        labelLine: {
          show: false
        },
        data: data_value
      },

    ]
  };
  var winlose_container = document.getElementById('winlose');
  var winlose = echarts.init(winlose_container);
  winlose.setOption(option);

  window.onresize = function () {
    winlose.resize();
  }
</script>
@endsection