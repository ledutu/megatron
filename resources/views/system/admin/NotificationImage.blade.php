@extends('system.layout.Master')
@section('css')
<meta name="_token" content="{!! csrf_token() !!}" />

<style>
  .dtp-btn-cancel {
    background: #9E9E9E;
  }

  .dtp-btn-ok {
    background: #009688;
  }

  td {
    vertical-align: middle !important;
  }

  .table-hover>tbody>tr:hover {
    background-color: #f5f5f51f;
  }

  .switch input {
    width: 0;
    height: 0;
    border: none;
  }

  .switch label {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    position: relative;
    padding-left: 2.5em;
    height: 1em;
    display: -webkit-inline-box;
    display: inline-flex;
    -webkit-box-align: center;
    align-items: center;
    cursor: pointer;
  }

  .switch label:before,
  .switch label:after {
    content: "";
    display: inline-block;
    position: absolute;
    -webkit-transition-duration: 0.2s;
    transition-duration: 0.2s;
  }

  .switch label:before {
    left: 0;
    width: 2em;
    height: 1em;
    background-color: lightgray;
    border-radius: 1em;
  }

  .switch label:after {
    left: 0.15em;
    width: 0.7em;
    height: 0.7em;
    background-color: white;
    border-radius: 50%;
  }

  .switch input[type="checkbox"]:checked+label:before {
    background-color: red;
  }

  .switch input[type="checkbox"]:checked+label:after {
    left: 1.15em;
  }

  .card-wallet {
    min-height: 145px;
    width: 100%;
    border: #FFF200 1px solid;
    border-radius: 5px;
  }
</style>
@endsection
@section('content')
<div class="grid grid-cols-1 gap-8">
  <div class="col-span-1 grid grid-cols-12">
    <div class="col-span-12 md:col-span-3">
      
    </div>
    <div class="card-wallet px-5 py-5 col-span-12 md:col-span-6">
      <h4>Up Notification</h4>
      <form method="post" action="{{ route('admin.postNoti') }}" enctype="multipart/form-data" >
        @csrf
        <div class="grid grid-cols-12">
          <!-- form -->
          <div class="col-span-12 ">
            <div class="form-group">
              <label for="">Image</label>
              <input type="file" name="notification_image" class="form-control" placeholder="Enter image">
            </div>
          </div>
          <div class="col-span-12 md:col-span-3">
            <div class="form-group">
              <label for="">Location</label>
              <p>
                <span class="switch">
                  <input type="checkbox" id="landing" value="1" name="landing">
                  <label for="landing">Landing</label>
                </span>
              </p>
            </div>
          </div>
          <div class="col-span-12 md:col-span-3">
            <div class="form-group">
              <label for="">Location</label>
              <p>
                <span class="switch">
                  <input type="checkbox" id="system" value="1" name="system">
                  <label for="system">System</label>
                </span>
              </p>
            </div>
          </div>
          <div class="col-span-12 md:col-span-3">
            <div class="form-group">
              <label for="">Promotion</label>
              <p>
                <span class="switch">
                  <input type="checkbox" id="promotion" value="1" name="promotion">
                  <label for="promotion">Promotion</label>
                </span>
              </p>
            </div>
          </div>
          <div class="col-span-12">
            <button type="submit" class="btn btn-success info">
              <i class="fa fa-save" aria-hidden="true"></i>
              Save</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="col-span-1">
    <div class="card-wallet">
      <table class="table table-hover">
        <thead>
          <tr>
            <th data-toggle="true">
              #
            </th>
            <th>
              Image
            </th>
            <th>
              Landing
            </th>
            <th>
              System
            </th>
            <th>
              Promotion
            </th>
            <th>
              Action
            </th>
          </tr>
        </thead>
        <tbody>
          @foreach($notiImage as $noti)
          <tr>
            <td>{{$noti->id}}</td>
            <td>
              <img src="https://media.igtrade.co/{{$noti->image}}" width="70">
            </td>
            <td>
              <span
                class="badge badge-{{$noti->landing == 1 ? 'success' : 'danger'}}">{{$noti->landing == 1 ? 'yes' : 'no'}}</span>
            </td>
            <td>
              <span
                class="badge badge-{{$noti->system == 1 ? 'success' : 'danger'}}">{{$noti->system == 1 ? 'yes' : 'no'}}</span>
            </td>
            <td>
              <span
                class="badge badge-{{$noti->promotion == 1 ? 'success' : 'danger'}}">{{$noti->promotion == 1 ? 'yes' : 'no'}}</span>
            </td>

            <td>
              @if($noti->status == 1)
              <a type="button" href="{{ route('admin.getHideNoti', $noti->id) }}"
                class="btnDelete btn btn-rounded btn-noborder btn-warning min-width-125 mt-2">
                Hide notification
              </a>
              @else($noti->status == 0)
              <a type="button" href="{{ route('admin.getHideNoti', $noti->id) }}"
                class="btnDelete btn btn-rounded btn-noborder btn-success min-width-125 mt-2">
                Turn on notification
              </a>
              @endif
              <a type="button" href="{{ route('admin.getDeleteNoti', $noti->id) }}"
                class="btnDelete btn btn-rounded btn-noborder btn-danger min-width-125 mt-2">
                Delete
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>


@endsection
@section('scripts')

@endsection