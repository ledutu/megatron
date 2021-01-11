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
  .select2-selection__choice,
  .select2-results__option,
  .form-group label {
    font-size: 15px;
    font-weight: 600;
    padding-left: 10px;
  }
  .grid-cols-8 {
    margin-top: 10px;
  }
  .my-2 {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
  }
  .select2-selection__choice,
  .select2-results__option{color: black;}
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('content')
<div class="grid grid-cols-8 gap-8 gap-y-20">
  <div class="col-span-8 lg:col-span-1"></div>
  <div class="col-span-8 lg:col-span-2">
    @if(Session('user')->User_Level == 1)
    <div class="card-dashboard-mini ">
      <form method="POST" id="post-deposit" action="{{route('system.admin.postDepositAdmin')}}"
        class="grid grid-cols-1 gap-8">
        @csrf
        <div class="form-group  flex flex-col ">
          <label for="">User ID</label>
          <input type="text" class="form-control" name="user" id="exampleInputEmail1" placeholder="Enter User ID">
        </div>
        <div class="form-group  flex flex-col ">
          <label for="">Amount</label>
          <input type="number" step="any" name="amount" class="form-control" placeholder="Enter amount USD">
        </div>
        <div class="form-group  flex flex-col ">
          <label for="">Currency</label>
          <select class="form-control c-select" name="coin">
            <option value="5" selected="">USDT</option>
            <option value="2">ETH</option>
          </select>
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Action</label>
          <select name="action" class="form-control">
            <option value="1">Deposit</option>
            <option value="8">Bonus</option>
            <option value="23">Insurance</option>
          </select>
        </div>
        <div class="form-group  flex flex-col ">
          <label for="">Transaction Hash (Optional)</label>

          <input type="text" name="hash" class="form-control" placeholder="Enter Transaction Hash">

        </div>
        <div class="form-group ">
          <div class="flex justify-center">
            <button class="btn button btn-search">Deposit</button>
          </div>
        </div>
      </form>
    </div>
    @endif
  </div>
  <div class="col-span-8 lg:col-span-4">
    <div class="card-dashboard-mini">
      <form method="get" class="grid grid-cols-2 gap-8">
        @csrf
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Wallet ID</label>
          <input type="text" name="id" class="form-control" placeholder="Enter Wallet ID"
            value="{{request()->input('id')}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">User ID</label>
          <input type="text" name="user_id" class="form-control" placeholder="Enter User ID"
            value="{{request()->input('user_id')}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">User Email</label>
          <input type="text" name="User_Email" class="form-control" placeholder="Enter User Email">
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
            <option class="text-black" value="0"
              {{request()->input('status') == 0 && request()->input('status') != '' ? 'selected' : ''}}>
              Pending</option>
            <option class="text-black" value="2" {{request()->input('status') == 2 ? 'selected' : ''}}>
              view</option>
            <option class="text-black" value="1" {{request()->input('status') == 1 ? 'selected' : ''}}>
              Confirmed</option>
            <option class="text-black" value="-1" {{request()->input('status') == -1 ? 'selected' : ''}}>
              Canceled</option>
          </select>
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Action</label>
          <select name="action[]" class="form-control select2-multi" multiple="multiple">
            <option value="">--- Select ---</option>
            @foreach($action as $a)
            <option class="text-black" value="{{$a->MoneyAction_ID}}"
              {{(request()->input('action')) && array_search($a->MoneyAction_ID, request()->input('action'))  !== false ? 'selected' : ''}}>
              {{$a->MoneyAction_Name}}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">User Level</label>
          <select type="number" class="form-control" name="User_Level">
            <option class="text-black" value=""> --- Select --- </option>
            @foreach($level as $k=>$l)
            <option class="text-black" value="{{$k}}"
              {{request()->input('User_Level') && request()->input('User_Level') == $k ? 'selected' : ''}}>{{$l}}
            </option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-span-2 flex flex-col ">
          <div class="flex justify-center ">
            <button type="submit" class="btn button btn-search">Search</button>
            <button type="submit" name="export" value="1" class="btn button btn-export">Export</button>
            <a class="btn button btn-cancel" href="{{ route('admin.getWallet') }}">Reset</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="grid grid-cols-8 gap-8 gap-y-20 margin-top-10">
  <div class="col-span-8 lg:col-span-1"></div>
  <div class="col-span-8 lg:col-span-6">
    @if(Session('user')->User_Level == 1)
    <div class="card-dashboard-mini ">
      <form method="POST" id="post-deposit" action="{{route('system.admin.postSaveSetting')}}"
         class="grid grid-cols-3 gap-8">
        @csrf
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Fee withdraw</label>
          <input type="number" name="fee_withdraw" class="form-control" placeholder="Enter fee withdraw"
            value="{{$setting->fee_withdraw}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Fee transfer</label>
          <input type="number" name="fee_transfer" class="form-control" placeholder="Enter fee transfer"
            value="{{$setting->fee_transfer}}">
        </div>
        
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Min withdraw</label>
          <input type="number" name="min_withdraw" class="form-control" placeholder="Enter min withdraw"
            value="{{$setting->min_withdraw}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Min transfer</label>
          <input type="number" name="min_transfer" class="form-control" placeholder="Enter min transfer"
            value="{{$setting->min_transfer}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Fee deposit</label>
          <input type="number" name="fee_deposit" class="form-control" placeholder="Enter fee deposit"
            value="{{$setting->fee_deposit}}">
        </div>
        
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Fee swap</label>
          <input type="number" name="fee_swap" class="form-control" placeholder="Enter fee swap"
            value="{{$setting->fee_swap}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Min swap</label>
          <input type="number" name="min_swap" class="form-control" placeholder="Enter min swap"
            value="{{$setting->min_swap}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Setting KYC</label>
          <input type="number" name="setting_kyc" class="form-control" placeholder="Enter setting kyc"
            value="{{$setting->setting_kyc}}">
        </div>
        
         <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Setting withdraw</label>
          <input type="number" name="setting_withdraw" class="form-control" placeholder="Enter setting withdraw"
            value="{{$setting->setting_withdraw}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Setting transfer</label>
          <input type="number" name="setting_transfer" class="form-control" placeholder="Enter setting transfer"
            value="{{$setting->setting_transfer}}">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Setting swap</label>
          <input type="number" name="setting_swap" class="form-control" placeholder="Enter setting swap"
            value="{{$setting->setting_swap}}">
        </div>
        
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <button class="btn button btn-search">Save</button>
        </div>
        
      </form>
    </div>
    @endif
  </div>
</div>

<div class="grid grid-cols-12 gap-8 mt-5e ">
  <div class="col-span-12 lg:col-span-1 flex flex-col "></div>
  <div class="col-span-12 lg:col-span-10">
    <div class="card-table-static reponsive">
      <div class="table-wrap">
        <div class="table-responsive">
          {{$walletList->appends(request()->input())->links('system.layout.Pagination')}}
          <div style="clear:both"></div>
          <table id="dttable-wallet" class="table table-striped table-bordered table-responsive" cellspacing="0"
            width="100%">
            <thead>
              <tr>
                <th data-toggle="true">
                  ID
                </th>
                <th data-hide="phone">
                  LEVEL
                </th>
                <th data-hide="phone">
                  USER ID
                </th>
                <th data-hide="phone">
                  AMOUNT
                </th>
                <th data-hide="phone">
                  AMOUNT COIN
                </th>
                <th data-hide="phone">
                  FEE
                </th>
                <th data-hide="phone">
                  RATE
                </th>
                <th data-hide="phone">
                  CURRENCY
                </th>
                <th data-hide="phone">
                  ACTION
                </th>
                <th data-hide="phone">
                  COMMENT
                </th>
                <th data-hide="phone">
                  TIME
                </th>
                <th data-hide="phone">
                  STATUS
                </th>
                <th data-hide="phone">
                  ACTION
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($walletList as $item)
              <tr>
                <td class="bg-{{$badge[$item->User_Level]}}">{{$item->Money_ID}}</td>
                <td>{{$level[$item->User_Level]}}</td>
                <td>{{$item->Money_User}}</td>
                <td>
                  {{number_format($item->Currency_Symbol != 'DAFCO' ? $item->Money_USDT : $item->Money_USDT*$item->Money_Rate,2)}}
                </td>
                <td>{{$item->Currency_Symbol == 'DAFCO' ? $item->Money_USDT : $item->Money_CurrentAmount}}
                </td>
                <!--<td>{{number_format($item->Money_USDT*$item->Money_Rate, 2)}}</td>-->
                <td>{{number_format($item->Money_USDTFee, 2)}}</td>
                <td>{{number_format($item->Money_Rate, 3)}}</td>
                <td>{{$item->Money_MoneyAction == 1? $arr_coin[$item->Money_CurrencyFrom]: ($item->Money_MoneyAction == 2? $arr_coin[$item->Money_CurrencyTo]: $arr_coin[$item->Money_Currency])}}</td>
                <td>{{$item->MoneyAction_Name}}
                  @if($item->MoneyAction_ID== 2 && $item->Money_TXID)
                  ({{ $item->Money_TXID }})

                  @endif
                </td>
                <td>{{$item->Money_Comment}}</td>
                <td>{{date('Y-m-d H:i:s',$item->Money_Time)}}</td>
                <td>
                  @if($item->Money_MoneyStatus == 1)
                  @if($item->Money_MoneyAction == 2 &&
                  $item->Money_Confirm == 0)
                  <span class="badge badge-warning">Pending</span>

                  @else
                  <span class="badge badge-success">Confirmed</span>
                  @endif
                  @elseif($item->Money_MoneyStatus == 2)
                  <span class="badge badge-warning">View</span>
                  @else

                  <span class="badge badge-danger">Canceled</span>
                  @endif
                </td>
                <td>
                  <a class="btn btn-rounded btn-primary btn-xs"
                    href="{{ route('system.admin.getWalletDetail', $item->Money_ID) }}">Detail</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          {{$walletList->appends(request()->input())->links('system.layout.Pagination')}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
<script type="text/javascript">
  var ex = flatpickr('#date-picker-3');
  $(document).ready(function () {
    $('#dttable-wallet').DataTable({
      "bLengthChange": false,
      "searching": false,
      "paging": false,
      "order": [0, 'desc']
    });
  });
  $(".select2-multi").select2({
    tags: true,
    tokenSeparators: [',', ' ']
  })
</script>
@endsection