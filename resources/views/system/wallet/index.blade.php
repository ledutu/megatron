@extends('system.layout.Master')
@section('css')
<style>
  .content {
    padding: 10px 0 0 10px;
    padding-top: 75px;
    padding-bottom: 10vh;
  }

  .head {
    height: 225px;

  }

  .head .main-balance {
    height: 150px;
    background: #00000096;
    margin: 0 2px;
    border: 1px solid #FFF200;
  }

  .ml-2 {
    margin-left: 0.5rem;
  }

  .head .title-balance {
    font-size: 17px;
    font-weight: 800;
  }

  .head .value-balance {
    font-size: 45px;
    font-weight: 700;
  }

  .head .value-balance span {
    font-size: 20px;
  }

  .head .tab-select {
    height: 75px;
  }

  .wallet-tabs>li.active>a,
  .wallet-tabs>li.active>a:hover,
  .wallet-tabs>li.active>a:focus {
    background-color: transparent;
    border-bottom: #FFF200 3px solid;
    border-radius: 2px;
  }

  .wallet-tabs>li>a {
    border-bottom: 2px solid #fff;
  }

  .wallet-tabs>li {
    width: 140px;
  }

  .wallet-tabs>li>a {
    font-size: 13px;
    font-weight: 600;
  }

  .card-wallet {
    min-height: 145px;
    width: 100%;
    border: #FFF200 1px solid;
    border-radius: 5px;
  }

  .mt-25 {
    margin-top: 2.5rem;
  }

  .card-wallet .head-wallet {
    height: 80px
  }

  .card-wallet .body-wallet {
    border-top: #FFF200 2px solid;
    height: 65px
  }

  .card-wallet .body-wallet button>svg {
    font-size: 25px;
    color: #FFF200;
    margin-right: 5px;
  }

  .card-wallet .body-wallet button>span {
    font-size: 15px;
    font-weight: 800;
  }

  .name-div span {
    font-size: 15px;
    font-weight: 700;
  }

  .value-div span {
    font-size: 17px;
    font-weight: 700;
  }

  .button-copy,
  .modal-sub li>a,
  .modal-action li>a {
    background: linear-gradient(90deg, #F4EB25, #F4C41B) !important;
    color: black;
    font-size: larger;
    font-weight: 700;
    width: 95%;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
    align-self: center;
    margin-top: 10px;
    margin-bottom: 10px;


  }

  #qr-img,
  .button-copy {
    width: 300px;
  }

  #addressWallet {
    max-width: 300px;
    margin: auto;
  }

  .modal-sub li.active>a,
  .modal-action li.active>a {
    box-shadow: -2px 1px 4px 1px black;
    /* color: #313131; */
  }

  #walletModal .modal-body {
    padding: 0;
  }

  .modal-sub li,
  .modal-action li {
    flex: 1;
    display: flex;
    justify-content: center;
  }

  .modal-content {
    background: #1a202c;
  }
	.form-transfer,
  .form-withdraw {
    padding: 10px 15px;
  }
.form-transfer label,
  .form-withdraw label {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
  }

  #subwallet input,
  #walletModal input {
    font-size: 13px;
    font-weight: 700;
  }
.btn-transfer,
  .btn-withdraw {
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
    margin-top: 10px;
    margin-bottom: 10px;
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
    margin-top: 10px;
    margin-bottom: 10px;
    margin: 10px;
  }
</style>
@endsection
@section('content')
<div class="head">
  <div class="main-balance grid grid-cols-4 sefl-center items-center">
    <div class="col-span-4 md:col-span-1">
    </div>
    <div class="col-span-4 md:col-span-3 pl-2">
      <p class="title-balance">{{__('wallet.main_balance')}}</p>
      <p class="value-balance flex ml-3 self-baseline items-baseline ">{{number_format($data['userBalance'],2)}}
        <span>USDT</span></p>

    </div>
  </div>
  <div class="tab-select grid grid-cols-12 sefl-center items-center">
    <div class="col-span-4 md:col-span-2"></div>
    <header class="col-span-12 md:col-span-8">
      <ul class="nav nav-tabs flex  wallet-tabs">
        <li class="active ">
          <a href="#main-wallet" data-toggle="tab" class="text-white">{{__('wallet.main_wallet')}}</a>
        </li>
        <li class="">
          <a href="#sub-wallet" data-toggle="tab" class="text-white">{{__('wallet.exchange_wallet')}}</a>
        </li>

      </ul>
    </header>
  </div>
</div>
<div class="body tab-content">
  <div id="main-wallet" class="tab-pane active clearfix">
    <div class="grid grid-cols-12">
      <div class="col-span-12 lg:col-span-2"></div>
      <div class="col-span-12 lg:col-span-8 grid grid-cols-2 gap-6">
        <div class="col-span-2 lg:col-span-1">
          <div class="card-wallet">
            <div class="head-wallet grid grid-cols-2">
              <div class="col-span-2 md:col-span-1 name-div  flex flex-col self-center items-center">
                <img src="exchange/img/icon/logo_icon.png" width="35" alt="">
                <span> {{__('wallet.main_balance')}}</span>

              </div>
              <div class="col-span-2 md:col-span-1 value-div flex self-center items-center justify-center">
                <span> {{number_format($data['userBalance'],2)}} USDT</span>

              </div>
            </div>
            <div class="body-wallet grid-cols-3 grid">

              <div class="col-span-3 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModal(5,3,'only')">
                  <i class="fas fa-exchange-alt"></i>
                  <span>{{__('wallet.transfer')}}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-span-2 lg:col-span-1">
          <div class="card-wallet">
            <div class="head-wallet grid grid-cols-2">
              <div class="col-span-1 name-div  flex flex-col self-center items-center">
                <img src="exchange/img/icon/USDT.png" width="35" alt="">
                <span> Tether (USDT)</span>
              </div>
              <div class="col-span-1 value-div flex self-center items-center flex-col">
                <span>{{__('wallet.price')}}:</span>
                <span> {{number_format($data['rate']['USDT'],2)}} USDT</span>

              </div>
            </div>
            <div class="body-wallet grid-cols-2 grid">
              <div class="col-span-1 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModal(5,1)">
                  <i class="fas fa-university"></i>
                  <span>{{__('wallet.deposit')}}</span>
                </button>
              </div>
              <div class="col-span-1 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModal(5,2)">
                  <i class="fas fa-money-check"></i>
                  <span>{{__('wallet.withdraw')}}</span>
                </button>
              </div>

            </div>
          </div>
        </div>
         {{-- <div class="col-span-2 lg:col-span-1">
      
          <div class="card-wallet">
            <div class="head-wallet grid grid-cols-2">
              <div class="col-span-1 name-div  flex flex-col self-center items-center">
                <img src="exchange/img/icon/BTC.png" width="35" alt="">
                <span> Bitcoin (BTC)</span>

              </div>
              <div class="col-span-1 value-div flex self-center items-center flex-col">
                <span>{{__('wallet.price')}}:</span>
                <span> {{number_format($data['rate']['BTC'],2)}} USDT</span>

              </div>
            </div>
            <div class="body-wallet grid-cols-2 grid">
              <div class="col-span-1 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModal(1,1)">
               <i class="fas fa-university"></i>
                  <span>{{__('wallet.deposit')}}</span>
                </button>
              </div>
              <div class="col-span-1 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModal(1,2)">
                  <i class="fas fa-money-check"></i>
                  <span>{{__('wallet.withdraw')}}</span>
                </button>
              </div>

            </div>
          </div>
       
        </div> --}}
        <div class="col-span-2 lg:col-span-1">
          <div class="card-wallet">
            <div class="head-wallet grid grid-cols-2">
              <div class="col-span-1 name-div  flex flex-col self-center items-center">
                <img src="exchange/img/icon/ETH.png" width="35" alt="">
                <span> Ethereum (ETH)</span>

              </div>
              <div class="col-span-1 value-div flex self-center items-center flex-col">
                <span>{{__('wallet.price')}}:</span>
                <span> {{number_format($data['rate']['ETH'],2)}} USDT</span>

              </div>
            </div>
            <div class="body-wallet grid-cols-2 grid">
              <div class="col-span-1 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModal(2,1)">
                 <i class="fas fa-university"></i>
                  <span>{{__('wallet.deposit')}}</span>
                </button>
              </div>
              <div class="col-span-1 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModal(2,2)">
                  <i class="fas fa-money-check"></i>
                  <span>{{__('wallet.withdraw')}}</span>
                </button>
              </div>

            </div>
          </div>
        </div>
        <div class="col-span-2 mt-25">
          <div class="card-wallet reponsive">
            <table id="wallet-main" class="display reponsive datatable" style="width:100%">
              <thead>
                <tr>
                  <th>#</th>
                  <th><span>{{__('wallet.amount')}}</span></th>
                  <th>{{__('wallet.fee')}}</th>
                  <th>{{__('wallet.rate')}}</th>
                  <th>{{__('wallet.currency')}}</th>
                  <th>{{__('wallet.action')}}</th>
                  <th>{{__('wallet.comment')}}</th>
                  <th>{{__('wallet.time')}}</th>
                  <th>{{__('wallet.status')}}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($data['history'] as $item)
                <tr>
                  <td>{{ $item->Money_ID}}</td>
                  <td>{{ $item->Money_USDT+0}}</td>
                  <td>{{ $item->Money_USDTFee+0}}</td>
                  <td>{{number_format($item->Money_Rate, 2)}}</td>
                  <td>{{$item->Currency_Symbol}}</td>
                  <td>{{$item->MoneyAction_Name}}</td>
                  <td>{{$item->Money_Comment}}</td>
                  <td>{{date('Y-m-d H:i:s',$item->Money_Time)}}</td>
                  <td>
                    <span class="badge badge-success">Confirmed</span>
                  </td>
                  <!--
                      <td>
                        @if(($item->Money_MoneyAction == 17 || $item->Money_MoneyAction == 20) && $item->Money_Confirm == 0)
                          <a href="{{route('system.getResendMailConfirm', $item->Money_ID)}}"><button type="button" class="btn btn-rounded btn-primary btn-xs">Choose Payment</button></a>
                        @endif
                    </td>
-->
                </tr>
                @endforeach
              </tbody>

            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="sub-wallet" class="tab-pane  clearfix">
    <div class="grid grid-cols-12">
      <div class="col-span-12 lg:col-span-2"></div>
      <div class="col-span-12 lg:col-span-8 grid grid-cols-2 gap-6">
        <div class="col-span-2 lg:col-span-1">
          <div class="card-wallet">
            <div class="head-wallet grid grid-cols-2">
              <div class="col-span-2 md:col-span-1  name-div  flex flex-col self-center items-center justify-center">
                <img src="exchange/img/icon/logo_icon.png" width="35" alt="">
                <span> {{__('profile.live_balance_trade')}}</span>

              </div>
              <div class="col-span-2 md:col-span-1  md:col-span-1 value-div flex self-center items-center justify-center">
                <span> {{number_format($data['balance_trade']['live'],2)}} USDT</span>

              </div>
            </div>
            <div class="body-wallet grid-cols-2 grid">
              <div class="col-span-1 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModalSub(1)">
                  <i class="fas fa-university"></i>
                  <span>{{__('wallet.deposit')}}</span>
                </button>
              </div>
              <div class="col-span--1 justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center" onclick="showModalSub(2)">
                  <i class="fas fa-money-check"></i>
                  <span>{{__('wallet.withdraw')}}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-span-2 lg:col-span-1">
          <div class="card-wallet">
            <div class="head-wallet grid grid-cols-2">
              <div class="col-span-2 md:col-span-1 name-div  flex flex-col self-center items-center">
                <img src="exchange/img/icon/logo_icon.png" width="35" alt="">
                <span> {{__('profile.demo_balance_trade')}}</span>
              </div>
              <div class="col-span-2 md:col-span-1 value-div flex self-center items-center justify-center">
                <span> <span class="demo-balance">{{number_format($data['balance_trade']['demo'],2)}}</span> USDT</span>

              </div>
            </div>
            <div class="body-wallet grid-cols-3 grid">
              <div class="col-span-3  justify-center flex self-center items-center">
                <button class="flex justify-center  self-center items-center"
                  onclick="refeshDemo()"
                  >
                    <i class="fas fa-sync-alt"></i>
                    <span>{{__('wallet.refresh')}}</span>
                  </button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-span-2 mt-25">
          <div class="card-wallet reponsive">
            <table id="wallet-sub" class="display reponsive datatable" style="width:100%">
              <thead>
                <tr>
                  <th>#</th>
                  <th>{{__('wallet.comment')}}</th>
                  <th>{{__('wallet.action')}}</th>
                  <th>{{__('wallet.time')}}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($data['historyTrade'] as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->comment }}</td>
                  <td>{{$item->action}}</td>
                  <td>{{date('Y-m-d H:i:s',$item->time )}}</td>
                </tr>
                @endforeach
              </tbody>

            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="walletModal" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-body">
          <header>
            <ul class="nav nav-tabs flex modal-action">
              <li class="active deposit">
                <a href="#deposit" data-toggle="tab" class="text-black">{{__('wallet.deposit')}}</a>
              </li>
              <li class="withdraw">
                <a href="#withdraw" data-toggle="tab" class="text-black">{{__('wallet.withdraw')}}</a>
              </li>
              <li class="transfer active">
                <a href="#transfer" data-toggle="tab" class="text-black">{{__('wallet.transfer')}}</a>
              </li>
            </ul>
          </header>
          <div class="body tab-content">
            <div id="deposit" class="tab-pane active clearfix">
              <div class="body-deposit flex flex-col justify-center self-center items-center">
                <img src="" alt="" id="qr-img">
                <input id="addressWallet" type="text" class="form-control " readonly>
                <button class="button btn button-copy" onclick="Copy_link('addressWallet')">{{__('profile.copy_address')}}</button>
              </div>
            </div>
            <div id="withdraw" class="tab-pane  clearfix">
              <form action="{{route('postWithdraw')}}" method="POST" class="form-withdraw">
                @csrf
                <div class="form-group">
                  <label>{{__('profile.from')}}:</label>
                  <input type="text" readonly class="form-control text-white" value="Main Balance">
                </div>
                <div class="form-group">
                  <label for="">{{__('profile.from_amoun')}} <span class="CoinFrom"></span>:</label>
                  <input type="number" min="50" step="any" id="amount-from" name="amount" class="form-control">
                </div>
                <div class="form-group">
                  <label for="">{{__('profile.to_coin')}}:</label>
                  <input type="text" readonly id="CoinTo" name="to" class="form-control text-white">
                  <input type="hidden" readonly id="coin_to" name="coin_to">
                </div>
                <div class="form-group">
                  <label for="">{{__('wallet.fee')}}: <span class="Fee"></span></label>
                  <input type="text" id="fee" readonly class="form-control text-white">
                </div>
                <div class="form-group">
                  <label for="">{{__('profile.actually_received')}}: <span class="CoinTo"></span>:</label>
                  <input type="text" id="amount-to" readonly class="form-control text-white">
                </div>
                <div class="form-group">
                  <label for="">{{__('profile.wallet_address')}}: <span class="Fee"></span></label>
                  <input type="text" name="address" id="addresswallet" class="form-control">
                </div>

                <div class="form-group">
                  <label for="">{{__('profile.google_authenticator_code')}}</label>
                  <input type="number" name="auth" id="g2a" class="form-control">
                </div>
                <div class="form-group">
                  <div class="flex justify-center self-center items-center">
                    <button type="submit" class="button btn btn-withdraw">{{__('wallet.withdraw')}}</button>
                    <button type="button" class="button btn btn-cancel" onclick="hideModal()">{{__('profile.cancel')}}</button>
                  </div>
                </div>
              </form>
            </div>
            <div id="transfer" class="tab-pane active clearfix">
              <form action="{{route('postTransfer')}}" method="post" class="form-transfer">
                @csrf
                <div class="form-group">
                  <label for="">{{__('profile.user_received')}}</label>
                  <input type="number" name="user" id="" class="form-control" placeholder="{{__('profile.enter_user_id_receive')}}">
                </div>
                <div class="form-group">
                  <label for="">{{__('profile.amount_send')}}</label>
                  <input type="number" min="1" step="any" class="form-control" placeholder="{{__('profile.enter_amount_you_need_to_send')}}"
                    name="amount">
                </div>
                <div class="form-group">
                  <label for="">{{__('profile.google_authenticator_code')}}</label>
                  <input type="text"  class="form-control" name="otp" placeholder="{{__('profile.enter_your_code')}}"
                    name="amount">
                </div>
                <div class="form-group">
                  <label for="">{{__('wallet.fee')}}</label>
                  <input type="number" readonly class="form-control" value="">
                </div>
                <div class="form-group">
                  <div class="flex justify-center self-center items-center">
                    <button type="submit" class="button btn btn-transfer">{{__('auth.send')}}</button>
                    <button type="button" class="button btn btn-cancel" onclick="hideModal()">{{__('profile.cancel')}}</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="subwallet" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-body">
          <header>
            <ul class="nav nav-tabs flex modal-sub">
              <li class="active deposit">
                <a href="#sub-deposit" data-toggle="tab" class="text-black">{{__('wallet.deposit')}}</a>
              </li>
              <li class="withdraw">
                <a href="#sub-withdraw" data-toggle="tab" class="text-black">{{__('wallet.withdraw')}}</a>
              </li>
            </ul>
          </header>
          <div class="body tab-content">
            <div id="sub-deposit" class="tab-pane active clearfix">
              <form action="{{route('postDepositTrade')}}" method="post" class="form-withdraw">
                @csrf
                <div class="form-group">
                  <label for="">{{__('profile.from_main_balance')}}</label>
                  <input type="text" value="{{number_format($data['userBalance'],2)}}" class="form-control" readonly>
                </div>
                <div class="form-group">
                  <label for="">{{__('profile.amount_to_trade_balance')}}</label>
                  <input type="number" step="any" name="amount" min="1" class="form-control" placeholder="{{__('profile.enter_amount_you_need_to_send')}}"
                    name="amount">
                </div>
                <!-- <div class="form-group">
                  <label for="">Fee</label>
                  <input type="number" readonly class="form-control" value="">
                </div> -->
                <div class="form-group">
                  <div class="flex justify-center self-center items-center">
                    <button type="submit" class="button btn btn-withdraw">{{__('wallet.deposit')}}</button>
                    <button type="button" class="button btn btn-cancel" onclick="hideModal()">{{__('profile.cancel')}}</button>
                  </div>
                </div>
              </form>
            </div>
            <div id="sub-withdraw" class="tab-pane  clearfix">
              <form action="{{route('postWithdrawTrade')}}" method="post" class="form-withdraw">
                @csrf
                <div class="form-group">
                  <label for="">{{__('profile.from_balance_trade')}}</label>
                  <input type="text" value="{{number_format($data['balance_trade']['live'],2)}}" class="form-control" placeholder="Enter User ID Recive">
                </div>
                <div class="form-group">
                  <label for="">{{__('profile.amount_to_main_balance')}}</label>
                  <input type="number" step="any" name="amount" min="1" class="form-control" placeholder="{{__('profile.enter_amount_you_need_to_send')}}"
                    name="amount">
                </div>
                <!-- <div class="form-group">
                  <label for="">Fee</label>
                  <input type="number" readonly class="form-control" value="">
                </div> -->
                <div class="form-group">
                  <div class="flex justify-center self-center items-center">
                    <button type="submit" class="button btn btn-withdraw">{{__('wallet.withdraw')}}</button>
                    <button type="button" class="button btn btn-cancel" onclick="hideModal()">{{__('profile.cancel')}}</button>
                  </div>
                </div>
              </form>
            </div>

          </div>



        </div>
      </div>
    </div>
  </div>

</div>

@endsection
@section('scripts')
<script>
  $(document).ready(function () {
    $('#wallet-main').DataTable({
      "bPaginate": true,
      "bLengthChange": false,
      "bFilter": true,
      "searching": false,
      "bInfo": false,
      "bAutoWidth": false,
       "order": [[ 0, "desc" ]]
    });
    $('#wallet-sub').DataTable({
      "bPaginate": true,
      "bLengthChange": false,
      "bFilter": true,
      "searching": false,
      "bInfo": false,
      "bAutoWidth": false,
       "order": [[ 0, "desc" ]]
    });
  });
</script>
<script type='text/javascript'>
  //show modal
  function showModalSub(action) {
    $('#subwallet').modal("show");
    //1 deposit
    //2 withdraw
    $('.modal-sub .withdraw').removeClass('active');
    $('.modal-sub .deposit').removeClass('active');
    $("#sub-withdraw").removeClass('active');
    $("#sub-deposit").removeClass('active');
    switch (action) {
      case 1:
        console.log(12312312)
        $('.modal-sub .deposit').addClass('active');
        $('.modal-action .deposit').addClass('active');
        $("#sub-deposit").addClass('active');
        break;
      case 2:
        $('.modal-sub .withdraw').addClass('active');
        $('.modal-action .withdraw').addClass('active');
        $("#sub-withdraw").addClass('active');
        break;
    }
  }
  function showModal(coinId, action, only) {
    //1 deposit
    //2 withdraw
    //3 transfer
    //only set transfer
    //switch tab
    //remove active
    $('.modal-action .withdraw').removeClass('active');
    $("#withdraw").removeClass('active');
    $('.modal-action .deposit').removeClass('active');
    $("#deposit").removeClass('active');
    $('.modal-action .transfer').removeClass("active");
    $("#transfer").removeClass('active')
    //set active
    if (action == 1) {
      $('.modal-action .deposit').addClass('active');
      $("#deposit").addClass('active');
      $.get('{{route("getCoin")}}?coin=' + coinId, function (data) {
        $('#deposit #addressWallet').val(data.address);
        $('#deposit #qr-img').attr('src', data.Qr);
      });
    } else if (action == 2) {
      $("#withdraw").addClass('active');
      $('.modal-action .withdraw').addClass('active');
    } else {
      $('.modal-action .transfer').addClass("active");
      $("#transfer").addClass('active')
    }
    //show modal
    $('#walletModal').modal("show");

    // change label
    document.querySelector('.modal-action .deposit a').innerHTML = (coinId == 1 ? '{{__("wallet.deposit")}} Bitcoin' : coinId == 2 ? '{{__("wallet.deposit")}} Ethereum' : '{{__("wallet.deposit")}} Tether');
    document.querySelector('.modal-action .withdraw a').innerHTML = (coinId == 1 ? '{{__("wallet.withdraw")}} Bitcoin' : coinId == 2 ? '{{__("wallet.withdraw")}} Ethereum' : '{{__("wallet.withdraw")}} Tether');
    document.querySelector('.modal-action .transfer a').innerHTML = '{{__("wallet.transfer")}}';

    //action 
    switch (only) {
      case 'only':
        $('.modal-action .transfer').removeClass('hidden');
        $("#transfer").removeClass('hidden');
        $('.modal-action .deposit').addClass('hidden');
        $('.modal-action .withdraw').addClass('hidden');
        $("#deposit").addClass('hidden');
        break;
      default:
        $('.modal-action .deposit').removeClass('hidden');
        $("#deposit").removeClass('hidden');
        $('.modal-action .withdraw').removeClass('hidden');
        $('.modal-action .transfer').addClass('hidden');
        $("#transfer").addClass('hidden');
        break
    }
    //to coin
    var realvalue = 0;
    var arr_Rate = {2:'{{$data['rate']['ETH']}}', 5:'{{$data['rate']['USDT']}}'}
    var price = arr_Rate[coinId];
    var fee = {{$data['fee']['Withdraw']}};
    var from = 0;
    $("#coin_to").val(coinId)
    switch (coinId) {
      case 1:
        $("#CoinTo").val("Bitcoin (BTC)")
        $("#amount-from").keyup(function () {
          from = $("#amount-from").val();
          _amoutFee = from * fee;
          realvalue = (from - _amoutFee) / price;
          $("#amount-to").val(realvalue + " BTC");
          $("#fee").val(_amoutFee + " USDT");
        });
        break;
      case 2:
        $("#CoinTo").val("Ethereum (ETH)")
        $("#amount-from").keyup(function () {
          from = $("#amount-from").val();
          _amoutFee = from * fee;
          realvalue = (from - _amoutFee) / price;
          $("#amount-to").val(realvalue + " ETH");
          $("#fee").val(_amoutFee + " USDT");
        });
        break;
      case 5:
        $("#CoinTo").val("Tether (USDT)")
        $("#amount-from").keyup(function () {
          from = $("#amount-from").val();
          _amoutFee = from * fee;
          realvalue = (from - _amoutFee) / price;
          $("#amount-to").val(realvalue + " USDT");
          $("#fee").val(_amoutFee + " USDT");
        });
        break
    }
  }
  //remove all behind close
  $(document).ready(function () {
    $("#walletModal").on('hide.bs.modal', function () {
      $("#amount-from").val("");
      $("#amount-to").val("");
      $("#fee").val("");
      $("#addresswallet").val("");
      $("#g2a").val("");
    });
  })
  //hide
  function hideModal() {
    $(".modal").modal('hide')
  }

</script>
@endsection