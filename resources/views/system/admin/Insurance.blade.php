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
          <label for="">#</label>
          <input class="form-control" type="text" placeholder="ID" value="{{request()->input('id')}}" name="id">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">User ID</label>
          <input class="form-control" type="text" placeholder="User ID" value="{{request()->input('user_id')}}" name="user_id">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Date From</label>
          <input type="date" id="date-picker-3" class="form-control" name="datefrom" value="{{request()->input('datefrom')}}"
            placeholder="Select Date From">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Data To</label>
          <input type="date" id="date-picker-3" class="form-control" name="dateto" value="{{request()->input('dateto')}}"
            placeholder="Select Date From">
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Status</label>
          <select  name="status" class="form-control" id="">
            <option selected value="" class="text-black"
                {{request()->input('status') == '' ? 'selected' : ''}}>
                --- Select ---</option>
            <option value="1" class="text-black"
                {{request()->input('status') == '0' ? 'selected' : ''}}>
                Waiting</option>
            <option value="1" class="text-black"
                {{request()->input('status') == '1' ? 'selected' : ''}}>
                Expired</option>
            <option value="-1" class="text-black"
                {{request()->input('status') == '-1' ? 'selected' : ''}}>
                Canceled</option>
          </select>
        </div>
        <div class="form-group col-span-2 lg:col-span-1 flex flex-col ">
          <label for="">Action</label>
          <div class="flex justify-center ">
            <button class="btn button btn-search" type="submit">Search</button>
           <button class="btn button btn-export" type="submit" name="export" value="1">export</button>
            <a class="btn button btn-cancel" href="{{ route('admin.getInsurance') }}">Reset</a>
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
			{{$getData->appends(request()->input())->links('system.layout.Pagination')}}
            <div style="clear:both"></div>
            <table id="member-list"
                  class=" dt-responsive table table-striped table-bordered table-responsive"
                  cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th data-toggle="true">
                            #
                        </th>
                        <th>
                            User ID
                        </th>
                        <th data-hide="phone">
                            Amount
                        </th>
                        <th data-hide="phone">
                            Time
                        </th>
                        <th data-hide="phone">
                            Time Limit
                        </th>
                        <th data-hide="phone,tablet">
                            Created Time
                        </th>
                        <th data-hide="phone">
                            Expired Time
                        </th>
                        <th data-hide="phone">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody>
                  	@php
                  		$status = [0=>'Waiting', 1=>'Expired', -1=>'Canceled']
                  	@endphp
                  	@foreach($getData as $v)
                  		
                  		<tr>
                           <td>{{$v->id}}</td>
                           <td>{{$v->user_id}}</td>
                           <td>{{$v->amount}}</td>
                           <td>{{$v->time}}</td>
              <td>{{$v->days??30}} Days ({{($feeInsur[$v->days] ?? 0.08)*100}}% Fee) </td>
                           <td>{{$v->created_time}}</td>
                           <td>{{$v->expired_time}}</td>
                           <td>{{$status[$v->status]}}</td>
                  		</tr>
                  
                  	@endforeach
                 
                  
                </tbody>
            </table>
      		{{$getData->appends(request()->input())->links('system.layout.Pagination')}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')

@endsection