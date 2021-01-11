@extends('system.layout.Master')

<style>
  .btn-success {
    background-color: #009688 !important;
    border-color: #009688 !important;
  }

  .btn-acitve {
    border-color: #4CAF50 !important;
    background-color: #4CAF50 !important;
  }

  .name-coin {
    color: white;
    font-size: 22px;
  }

  .text-warning td {
    color: #fcbe2d !important;
  }

  .clock {
    color: #fff;
    padding: 0 3px 0 15px;
    font-weight: bold;
  }

  @-webkit-keyframes yellow-fade {
    from {
      background: #F44336;
    }

    to {
      background: #fff;
    }
  }

  @-moz-keyframes yellow-fade {
    from {
      background: #F44336;
    }

    to {
      background: #fff;
    }
  }

  @keyframes yellow-fade {
    from {
      background: #F44336;
    }

    to {
      background: #fff;
    }
  }

  .classRed {
    background: #fd9d9d !important;
  }

  .fade-it {
    -webkit-animation: yellow-fade 1s ease-in-out 0s;
    -moz-animation: yellow-fade 1s ease-in-out 0s;
    -o-animation: yellow-fade 1s ease-in-out 0s;
    animation: yellow-fade 1s ease-in-out 0s;
  }

  .btn-height-50 {
    height: 50px;
  }

  .card-dashboard-mini {

    background: rgb(35 31 32 / 0.6);
    margin: 0 2px;
    border: 1px solid #FFF200;
    width: 100%;
 
 
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

  .user-with-border td {
    border-right: 1px solid white;
  }
</style>
@section('css')
@endsection
@section('content')

<div class="grid grid-cols-1 gap-10">
  <div class="col-span-1 tilt-admin font-bold text-4xl text-center mb-3 uppercase">
    Admin Game
  </div>
  <div class="col-span-1 tilt-admin font-bold text-4xl text-center mb-3 uppercase grid grid-cols-12">
    <div class=" col-span-12 xl:col-span-1"></div>
    <div class="card-dashboard-mini col-span-12 xl:col-span-10">
		<div>
            <button type="button"
                class="  btn-export items-center self-center text-2xl font-700 flex waves-light text-center p-3  my-3"
                onclick="ShowModal('#history-modal')"
               >History</button>
          
      </div>
      <table class="table table-striped table-vcenter">
        <thead>
          <tr>

            <th>ID</th>
            <th>Symbol</th>
            <th>Game fund</th>
            <th>Game real Fund</th>
            <th>Game Fund 30</th>
            <th>Game Fund Phe</th>
            <th>User</th>
            <th>Data time</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($quy as $v)
          <tr>
            <td>{{$v->GameFund_ID}}</td>
            <td>{{$v->GameFund_symbol}}</td>
            <td>{{$v->GameFund_fund}}</td>
            <td>{{$v->GameFund_fundReal}}</td>
            <td>{{$v->GameFund_fund30}}</td>
            <td>{{$v->GameFund_phe}}</td>
            <td>{{$v->GameFund_user}}</td>
            <td>{{$v->GameFund_datatime}}</td>
            <td class="flex">
              <button type="button"
                class="  btn-export items-center self-center text-2xl font-700 flex waves-light text-center p-3  my-3"
                onclick="showModalWallet('#depositGameFund','{{$v->GameFund_symbol}}')"
               >Deposit</button>
              <button type="button"
                class="btn-cancel items-center self-center text-2xl font-700 flex waves-light text-center p-3  my-3"
                onclick="showModalWallet('#withdrawGameFund','{{$v->GameFund_symbol}}')"
                >Withdraw</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
  <div class="col-span-1 grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-6 flex flex-col">

      <div class="card-dashboard-mini ">
        <h2>Bet List</h2>
        <table class="table table-striped table-vcenter" id="userBet">
          <thead>
            <tr>
              <th><input type="checkbox" name="selectall" id="checkall"></th>
              <th>Email</th>
              <th>Call</th>
              <th>Put</th>
              <th>Symbol</th>
              <th>Balance</th>
            </tr>
          </thead>
          <tbody>
				@foreach($bet as $v)
            		<tr>
                      <td><input type="checkbox" class="subName" name="name[aaa{{$v->GameBet_SubAccountUser }}]" value="1" />&nbsp</td>
                      <td>{{$v->GameBet_SubAccountUser }}</td>
                      <td>${{ ($v->GameBet_Type == 'buy') ? $v->GameBet_Amount :0 }}</td>
                      <td>${{ ($v->GameBet_Type == 'sell') ? $v->GameBet_Amount :0 }}</td>
                      <td>{{ $v->GameBet_Symbol }}</td>
                      <td>{{ $v->GameBet_SubAccountEndBalance }}</td>
            		</tr>
            	@endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="col-span-12 lg:col-span-6">
      <div class="card-dashboard-mini px-3 py-3">
        <div class="grid grid-cols-12 gap-4">
          <div class="col-span-12 md:col-span-4">
            <div class="float-left">
              <select class="form-control" name="symbol">
                <option value="BTCUSDT" {{ ($symbol == 'BTCUSDT') ? 'selected' : ''}}>BTC - USDT</option>
                <option value="ETHUSDT" {{ ($symbol == 'ETHUSDT') ? 'selected' : ''}}>ETH - USDT</option>
                <option value="LTCUSDT" {{ ($symbol == 'LTCUSDT') ? 'selected' : ''}}>LTC - USDT</option>
                <option value="EOSUSDT" {{ ($symbol == 'EOSUSDT') ? 'selected' : ''}}>EOS - USDT</option>
                <option value="BNBUSDT" {{ ($symbol == 'BNBUSDT') ? 'selected' : ''}}>BNB - USDT</option>
                <option value="BCHUSDT" {{ ($symbol == 'BCHUSDT') ? 'selected' : ''}}>BCH - USDT</option>
              </select>
            </div>
          </div>
          {{--<div class="col-span-12 md:col-span-8">
            <div class="float-right">
              <button type="button" class="btn-history btn btn-success waves-effect waves-light btn-height-50"
                onclick="matday(1)">
                <i class="fal fa-arrow-alt-circle-left"></i>
                error 1
              </button>
              <button type="button" class="btn-history btn btn-success waves-effect waves-light btn-height-50"
                onclick="matday(2)">
                <i class="fas fa-arrow-alt-to-top"></i>
                error 2
              </button>
              <button type="button" class="btn-history btn btn-success waves-effect waves-light btn-height-50"
                onclick="matday(4)">
                <i class="fas fa-sync"></i>
                error 4
              </button>

              <button type="button" class="btn-history btn btn-success waves-effect waves-light btn-height-50"
                onclick="matday(5)">
                <i class="fas fa-redo-alt"></i>
                error all
              </button>

            </div>
          </div>--}}
          <div class="col-span-12">
            <div class="grid grid-cols-12 gap-4 text-center BTCUSDT" style="margin-top:30px;">
              <div class="col-span-12 md:col-span-5">
                <p class="font-medium text-3xl text-center">Total Buy: <br> <span class="TotalBuy text-4xl">{{ $total['BTCUSDT']['buy'] }}</span></p>
              </div>
              <div class="col-span-12 md:col-span-2"></div>
              <div class="col-span-12 md:col-span-5">
                <p class="font-medium text-3xl text-center">Total Sell:<br> <span class="TotalSell text-4xl">{{ $total['BTCUSDT']['sell'] }}</span></p>
              </div>
              <div class="col-span-12 md:col-span-5 flex justify-center self-center items-center">
                <button type="button" id="btn-reset"
                  class=" button btn-rounded btn-noborder btn-export items-center self-center text-2xl font-700 flex waves-light text-center p-3 win"
                  data-type="buy">Buy</button>

              </div>
              <div class="col-span-12 md:col-span-2 flex flex-col">
                <button type="button" id="btn-report-path"
                  class=" button btn-rounded btn-noborder btn-warning waves-light text-center time  btn-lg text-2xl font-700">0</button>
                <button type="button" id="btn-report-path"
                  class=" button btn-rounded btn-noborder btn-search items-center self-center text-2xl font-700 flex waves-light text-center p-3 win mt-2"
                  data-type="draw">DRAW</button>
              </div>
              <div class="col-span-12 md:col-span-5 flex justify-center self-center items-center ">
                <button type="button" id="btn-report-path"
                  class=" button btn-rounded btn-noborder btn-cancel items-center self-center text-2xl font-700 flex waves-light text-center p-3 win"
                  data-type="sell">Sell</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="depositGameFund" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content" style="background: #1a202c;">
      <div class="modal-body">
        <form action="{{route('admin.depositGameFund')}}" method="post" class="form-transfer">
          @csrf
          <div class="form-group">
            <label for="">Amount </label>
            <input type="number" min="1" step="any" class="form-control" placeholder="Enter Amout You Need Deposit"
              name="amount">
          </div>
          <div class="form-group">
            <div class="flex justify-center self-center items-center">
              <button type="submit" class="button btn btn-export">Deposit</button>
              <button type="button" class="button btn btn-cancel" onclick="hideModal()">Cancel</button>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="withdrawGameFund" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content" style="
   		 background: #1a202c;
	">
      <div class="modal-body">
        <form action="{{route('admin.withdrawGameFund')}}" method="post" class="form-transfer">
          @csrf
          <div class="form-group">
            <label for="">Amount </label>
            <input type="number" min="1" step="any" class="form-control" placeholder="Enter Amout You Need Deposit"
              name="amount">
          </div>
          <div class="form-group">
            <div class="flex justify-center self-center items-center">
              <button type="submit" class="button btn btn-export">Withdraw</button>
              <button type="button" class="button btn btn-cancel" onclick="hideModal()">Cancel</button>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="history-modal" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="
   		 background: #1a202c;
	">
      <div class="modal-header">
            <button type="button" class="button btn btn-danger" onclick="hideModal()">x</button>
      </div>
      <div class="modal-body overflow-auto">
      <table class="table table-striped table-vcenter">
        <thead>
          <tr>

            <th>#</th>
            <th>User</th>
               <th>Action</th>
            <th>Log</th>
         
            <th>Data time</th>
          </tr>
        </thead>
        <tbody>
		@foreach($history_deposit as $v)
           <tr>
             <td>{{$v->id}}</td>
             <td>{{$v->user}}</td>
             <td>{{$v->action}}</td>
             <td>{{$v->comment}}</td>
             <td>{{$v->created_at}}</td>
          </tr>
      
          @endforeach
     
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
  function showModalWallet(ID,symbol){
    $(ID).find('form').append('<input type="hidden" name="symbol" value="'+symbol+'">');
    $(ID).modal('show');
  }
  function hideModal(){
    $('.modal').modal('hide');
  }
  function ShowModal(id){

   $(id).modal('show');
  }
</script>
<script src="exchange/js/colyseus.js"></script>
<script>
	

  var base_url = 'https://igtrade.co';
  var host = window.document.location.host.replace(/:.*/, '');
  var client = new Colyseus.Client('wss://socket.igtrade.co/');
  var user = { subID: {{ $subID }}, token: '{{$token}}', currency: 99 };

  var connect = false;
  var timer = 0;
  var exchange_url = 'https://beta.igtrade.co';
  var base_url = window.location.origin;
  var betArray = { BTCUSDT: { buy: 0, sell: 0 }, ETHUSDT: { buy: 0, sell: 0 }, LTCUSDT: { buy: 0, sell: 0 }, DASHUSDT: { buy: 0, sell: 0 }, EOSUSDT: { buy: 0, sell: 0 }, BNBUSDT: { buy: 0, sell: 0 }, BCHUSDT: { buy: 0, sell: 0 } };
  var ordeTime, waitTime;
  var symbol = 'BTCUSDT';

  client.joinOrCreate("my_room", user).then(room => {

    room.onStateChange.once(function (state) {
      console.log("initial room state:", state);
    });

    // new room state
    room.onStateChange(function (state) {
      // this signal is triggered on each patch
    });
    room.send({ action: 'connect' });

    // listen to patches coming from the server
    room.onMessage(function (message) {
      switch (message.action) {
        case 'connect':
          if (message.time > 30) {
            callFunctionWaiting(60 - message.time);

          } else {
            callFunctionOrder(30 - message.time);
          }

          break;
        case 'userList':
          userListOnline(message.data);
          break;
        case 'main':
          timer = message.time;


          if (timer == 0) {
            $('.'+symbol+' .TotalBuy').html(0);
			$('.'+symbol+' .TotalSell').html(0);
            callFunctionOrder(30 - timer);
          }

          if (timer == 30) {
            callFunctionWaiting(60 - timer);
          }

          if (timer == 1) {
            $('.TotalBuy').html('0');
            $('.TotalSell').html('0');
          }

          break;
        case 'sv-user-bet':

          userBet(message.data);

          break;

      }

    });
    $(function () {
      $('select[name=symbol]').change(function () {
        symbol = $(this).val();
        $(location).attr('href', 'https://igtrade.co/system/admin/game?symbol=' + symbol);
      });

      $('.win').click(function () {
        if (timer > 30 && timer < 43) {
          _type = $(this).data('type');
          var symbol = $("select[name=symbol]").val();
          win = 2;
          if (_type == 'buy') {
            win = 1;
          } else if (_type == 'sell') {
            win = 0;
          }
          $.get(exchange_url + "/setResultByAdmin", { user: 883397, symbol: symbol, win: win }, function (data) {
            if (data.status == true) {
              alert("OK");
            }
          });
        } else {
          console.log(timer);
          alert("only edit result in waiting (29s - 17s). Time site: exchange");
        }

      });
      $('#checkall').change(function () {
        $('.aaa111').prop('checked', this.checked);
      });

      $('.aaa111').change(function () {
        if ($('.aaa111:checked').length == $('.aaa111').length) {
          $('#checkall').prop('checked', true);
        }
        else {
          $('#checkall').prop('checked', false);
        }
      });
    });
  });

  function callFunctionOrder(_t) {

    clearTimeout(waitTime);
    clearTimeout(ordeTime);
    ordeTimeTemp = _t;
    $('.time').html(Number(_t) + ' Order');
    if (_t == 30) {
      betArray = { BTCUSDT: { buy: 0, sell: 0 }, ETHUSDT: { buy: 0, sell: 0 }, LTCUSDT: { buy: 0, sell: 0 }, DASHUSDT: { buy: 0, sell: 0 }, EOSUSDT: { buy: 0, sell: 0 }, BNBUSDT: { buy: 0, sell: 0 }, BCHUSDT: { buy: 0, sell: 0 } };
      $('.sumbet').html('$ 0');
      $('#userBet tbody').html('');
    }
    ordeTime = setInterval(function () {
      if (_t > 0) {
        $('.time').html(Number(_t) + ' Order');
        _t--;
        ordeTimeTemp = _t;


      }
    }, 1000);
  }

  function callFunctionWaiting(_t) {

    clearTimeout(waitTime);
    clearTimeout(ordeTime);
    $('.time').html(Number(_t) + ' Waiting');

    waitTime = setInterval(function () {

      if (_t > 0) {
        $('.time').html(Number(_t) + ' Waiting');
        _t--;
      }

    }, 1000);
  }
  function userListOnline(data) {
    $('.online').html(data.length);
    _html = '';
    data.forEach((item) => {
      if (item.indexOf("L") === 0) {
        _html += '<tr><td>' + item + '<td></tr>';
      }

    });
    $('#list_user').html(_html);
  }
  function userBet(data) {
	console.log(data);
    _buyAmount = 0;
    _sellAmount = 0;
    if(data.level == 0){
      	if(data){
        if (data.type == 'buy') {
          betArray[data.symbol].buy += data.amount;
          _buyAmount = Number(data.amount);
        } else {
          betArray[data.symbol].sell += data.amount;
          _sellAmount = Number(data.amount);
        }
        _html = '<tr><td><input type="checkbox" class="subName" name="name['+data.sub+']" value="1" />&nbsp</td><td>'+data.email+'</td><td>$'+_buyAmount+'</td><td>$'+_sellAmount+'</td><td>'+data.symbol+'</td><td>'+data.endBalance+'</td></tr>';
      }

      $('#userBet tbody').append(_html);

      $('.'+symbol+' .TotalBuy').html(betArray[symbol].buy);
      $('.'+symbol+' .TotalSell').html(betArray[symbol].sell);
    }
    


  }

  function SetWinByAdmin(_symbol, _type) {
    if (_type == 'buy') {
      data = { user: 999999, game: 0, symbol: _symbol, win: 1 };
    } else {
      data = { user: 999999, game: 0, symbol: _symbol, win: 0 };
    }
    $.get("https://igtrade.co/setResultByAdmin", data, function (data) {

      if (data.status == true) {
        alert(_symbol + ' set OK');
      } else {
        alert(data.msg);
      }
    });
  }

  /*function runCountdown(time){
    $('.time').html(time);
    setInterval(function(){
      if(time>60){
        time = 1; 
        betArray = {BTCUSDT:{buy:0, sell:0}, ETHUSDT:{buy:0, sell:0}, LTCUSDT:{buy:0, sell:0}, DASHUSDT:{buy:0, sell:0}, EOSUSDT:{buy:0, sell:0}, BNBUSDT:{buy:0, sell:0}, BCHUSDT:{buy:0, sell:0}};
        $('.sumbet').html('$ 0');
        $('#userBet tbody').html('');
      }
        if(time > 30){
	
        $('.time').html((60-(Number(time)-30)+1)+ ' Waiting');
      	
      }else{
        $('.time').html(((30-Number(time)+1))+' Order');
      }
    	
      time ++;
    }, 1000);
  	
  }*/


  function matday(err) {
    var sub = '';
    $('input.aaa111:checkbox:checked').each(function () {
      sub += $(this).val() + ',';
    });

    $.get("https://igtrade.co/system/admin/matday", { sub: sub, error: err }, function (data) {
      if (data.status == true) {

        alert('OK');
      }

    });
  }
</script>

@endsection