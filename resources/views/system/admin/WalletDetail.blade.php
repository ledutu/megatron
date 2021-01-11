@extends('system.layout.Master')
@section('css')
<style>
.form-control{
  color:white;
  font-size:15px;
  font-weight: 700;
}
</style>
@endsection
@section('content')
<div class="grid grid-cols-12 gap-8">
  <div class="md:col-span-4">
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="fa fa-user" aria-hidden="true"></i>
              ID</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Money_ID }}">
      </div>
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="fa fa-users" aria-hidden="true"></i>
              User ID</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Money_User }}">
      </div>
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="fa fa-envelope" aria-hidden="true"></i>
              Email</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->User_Email }}">
      </div>
      <div class="form-group">
          <label class="control-label mb-10"><i
                  class="mdi mdi-radioactive" aria-hidden="true"></i>
              Action</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->MoneyAction_Name }}">
      </div>
  </div>
  <div class="md:col-span-4">
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="mdi mdi-timer" aria-hidden="true"></i>
              Time</label>
          <input type="text" class="form-control" readonly=""
              value="{{ date('Y/m/d H:i:s', $detail->Money_Time) }}">
      </div>
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="icon-diamond" aria-hidden="true"></i>
              Amount</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Money_USDT }}">
      </div>
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="icon-diamond" aria-hidden="true"></i>
              Currency</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Currency_Name }}">
      </div>
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="icon-diamond" aria-hidden="true"></i>
              Amount Coin</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Money_CurrentAmount }}">
      </div>
      <div class="form-group">
          <label class="control-label mb-10 text-left">- Fee</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Money_USDTFee }}">
      </div>
  </div>
  <div class="md:col-span-4">
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="mdi mdi-comment" aria-hidden="true"></i>
              Comment</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Money_Comment }}">
      </div>
      <div class="form-group">
          <label class="control-label mb-10"><i
                  class="mdi mdi-emoticon-excited-outline"
                  aria-hidden="true"></i>
              Status</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Money_MoneyStatus == 1 ? ($detail->Money_MoneyAction == 2 && $detail->Money_Confirm == 0 ? "Processing" : "Success") : ($detail->Money_MoneyStatus == -1 ? "Canceled" : "View") }}">
      </div>
      @if($detail->Money_MoneyAction == 2)
      <div class="form-group">
          <label class="control-label mb-10 text-left"><i
                  class="mdi mdi-pencil-outline"
                  aria-hidden="true"></i>
              Address</label>
          <input type="text" class="form-control" readonly=""
              value="{{ $detail->Money_Address }}">
      </div>
      @endif
      <div class="seprator-block"></div>
      @if($detail->Money_MoneyAction == 2 &&
      (Session('user')->User_Level == 1 || Session('user')->User_Level == 2) && $detail->Money_Confirm == 0
      )
      <form method="GET" action="" id="confirm-wallet">
          <div class="form-actions mt-10">
              <input type="hidden" name="confirm" id="input-confirm" value="">
              <button type="button" class="btn btn-success mr-10 btn-confirm" data-confirm="1" data-coin="{{$detail->Money_Currency}}">
                <i class="fa fa-check-square-o" aria-hidden="true"></i> Confirm
              </button>
              <button type="button" name="confirm" class="btn btn-danger mr-10 btn-confirm" data-confirm="-1" data-coin="{{$detail->Money_Currency}}">
                <i class="fa fa-flus" aria-hidden="true"></i> Cancel
              </button>
              @if($detail->Money_Currency == 5 || $detail->Money_Currency == 8)
              <button type="button" name="confirm" value="1" class="btn btn-info btn-confirm"
                data-confirm="1"
                data-coin="{{$detail->Money_Currency}}" 
                data-address="{{ $detail->Money_Address }}" 
                data-id="{{ $detail->Money_ID }}" 
                data-amount="{{ $detail->Money_CurrentAmount }}">Send Token</button>
              @else
              @endif
              <button type="button" class="btn btn-warning mr-10 btn-confirm" data-confirm="2" data-coin="{{$detail->Money_Currency}}">
                <i class="fa fa-check-square-o" aria-hidden="true"></i> Success
              </button>
          </div>
      </form>
      @endif
  </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/gh/ethereum/web3.js@1.0.0-beta.34/dist/web3.min.js"></script>
<script>
	var base_url = window.location.origin + "/";
	
	let minABI = [
	  // transfer
	  {
	    "constant": false,
	    "inputs": [
	      {
	        "name": "_to",
	        "type": "address"
	      },
	      {
	        "name": "_value",
	        "type": "uint256"
	      }
	    ],
	    "name": "transfer",
	    "outputs": [
	      {
	        "name": "",
	        "type": "bool"
	      }
	    ],
	    "type": "function"
	  }
	];// Get ERC20 Token contract instance
	
window.addEventListener("load", async () => {
	
    if (typeof Web3 !== "undefined") {
        window.web3 = new Web3(ethereum);
        try {
            await ethereum.enable();
            var accounts = await web3.eth.getAccounts();
            balance = await web3.eth.getBalance(accounts[0]);
            
            balanceRESERVE = await web3.eth.getBalance('0x54b2a900F1E2c19c229367de0fa44bc24CA9390B');
            balanceRESERVE = balanceRESERVE/1000000000000000000;
			$('#RESERVEPOOL').html(balanceRESERVE+' ETH');
            walletAddress = accounts[0];
            var option = { from: accounts[0] };
            myContract = new web3.eth.Contract(minABI, contractAddress);
        } catch (error) {
            //
        }
    } else {
        console.log("No web3? You should consider trying MetaMask!");
    }
  	
	$('.btn-confirm').click(function(e){
		e.preventDefault();
		_confirm = $(this).data('confirm');
		_coin = $(this).data('coin');
        //if(_coin == 5){
        walletAddress = "0x3118df0C362Bc58B24124c31d7EDB36C89C4FacF";
        tokenAddress = "0xdac17f958d2ee523a2206206994597c13d831ec7";
        _decimal = 'mwei';
        _address = $(this).data('address');
        _amount = Math.abs($(this).data('amount')).toFixed(6);
		
		if(_confirm == 1){
			if(_coin == 5 || _coin == 8){
				console.log(_amount);
				let contract = new web3.eth.Contract(minABI, tokenAddress);// calculate ERC20 token amount
				var amount = _amount;
				var tokens = web3.utils.toWei(amount.toString(), _decimal);
				// call transfer function
				contract.methods.transfer(_address, tokens).send({from: walletAddress}).on('transactionHash', function(hash){
					if(hash){
						$('#input-confirm').val(1);
						$('#confirm-wallet').submit();				
					}
				});
			}else{
				$('#input-confirm').val(1);
				$('#confirm-wallet').submit();
			}
		}else if(_confirm == 2){
			$('#input-confirm').val(2);
			$('#confirm-wallet').submit();
		}
		else{
			$('#input-confirm').val(-1);
			$('#confirm-wallet').submit();
		}
	});


});
</script>


@endsection