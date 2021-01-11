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

  .media,
  .content-cmt {
    margin-left: 15px;
  }

  .img-cmt {
    border-radius: 50%;
    float: left;
    margin-right: 2%;
    background: #f5a32b;
    padding: 5px;
    border: 2px #f5a827 solid;
  }

  .content-cmt {
     background: #ffffff24;
    padding: 15px 25px;
    color: white;
    border-radius: 5px;
    min-width: 300px;
    padding-left: 5%;
    font-weight: 600;
    font-size: initial;
}

  .content-title {

    background: linear-gradient(to top, #f5b61a, #f58345) !important;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 10px;
  }

  .info-user {
    background: #ed89360f;
    padding: 10px 20px;
    font-size: 18px;
    border-radius: 5px;
    width: fit-content;

  }

  .info-user span {
    color: #0a8e88;
    font-weight: 600;
  }

  .info-user small {
    color: #0a8e88;
  }

  textarea {
   
       resize: none;
    background: #0000005e!important;
    color: white!important;
    font-weight: 500;
  }
</style>
@endsection
@section('content')
<div class="grid grid-cols-12 gap-8">
  <div class="col-span-12 lg:col-span-2"></div>
  <div class="col-span-12 lg:col-span-8 grid grid-cols-1 gap-6">
    <div class="col-span-1 card-dashboard-mini grid grid-cols-1 gap-6">
      <div class="panel panel-default card-view">
        <div class="panel-heading content-title" style="margin-bottom: 1em;">
          <h3 class="text-left font-bold text-2xl text-white m-0">TICKET ID:{{$ticket[0]->ticket_ID}} </h3>
          <p class="text-left font-700 text-xl text-white m-0">{{$ticket[0]->ticket_subject_name}}</p>
        </div>
        <div class="overflow-auto chat-container" id="chat-container">
          @foreach($ticket as $t)
          <div class="media mb-4 mt-1 flex self-center items-center
            @if($t->ticket_User == Session('user')->User_ID)
                 flex-row-reverse
            @endif
          ">
            <img class="img-cmt d-flex mr-2 rounded-circle avatar-sm" src="exchange/img/userProfile.png" width="50px" alt="Generic placeholder image">
            <div class="media-body info-user">
              <span class="float-right">{{ $t->ticket_Time }}</span>
              <h6 class="m-0 font-14">From:
                {{$t->User_Level == 1 ? 'Support IGtrade' : $t->User_Level == 3 ? 'Supporter': $t->User_Email}}</h6>
              <small class="text-muted">ID: {{$t->ticket_User}}</small>
            </div>
          </div>
          <div class="flex 
              @if($t->ticket_User == Session('user')->User_ID)
                justify-end
              @else
                justify-start
              @endif
              
            ">
            <p class="content-cmt
              @if($t->ticket_User == Session('user')->User_ID)
                text-right
              @endif
              "> 
                {!! $t->ticket_Content !!}
            </p>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-span-1 card-dashboard-mini grid grid-cols-1 gap-0">
      <div class="panel panel-default card-view">
        <div class="panel-heading content-title" >
          <h3 class="text-left font-bold text-2xl text-white m-0">REPLY TICKET ID: {{$ticket[0]->ticket_ID}} </h3>
        </div>
        <form action="{{route('postTicket')}}" method="post" class="ticket-comment-form">
          @csrf
          <input type="hidden" name="subject" value="{{$ticket[0]->ticket_Subject}}">
          <input type="hidden" name="replyID" value="{{$ticket[0]->ticket_ID}}">
          <div class=" m-5">
          
            <div class="media-body">
              <div class="mb-2">

                <textarea name="content" cols="30" rows="10" class="form-control" placeholder="Enter Content"></textarea>

              </div> <!-- end reply-box -->
            </div> <!-- end media-body -->
          </div> <!-- end medi-->

          <div class="text-center">
            <button style="margin: 2%;" type="submit" class="btn btn-search btn-rounded width-sm">Send</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
var objDiv = document.getElementById("chat-container");
objDiv.scrollTop = objDiv.scrollHeight;
</script>

@endsection