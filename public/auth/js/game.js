_ParamsSymbol = GetURLParameter('symbol');
_Params = _ParamsSymbol.split('-');
symbol = _Params[0];
symbolnd = _Params[1];
var connect = false;
var mysetInterval;
var time, timeSV, amount = 0;
var ordeTime, waitTime;


if(_betList){
	if(_betList[symbol]){
		if(_betList[symbol].buy){
			$('.BS'+symbol+' .myBuy').html('$ '+Number(_betList[symbol].buy).toFixed(2));
		}
		
		if(_betList[symbol].sell){
			$('.BS'+symbol+' .mySell').html('$ '+Number(_betList[symbol].sell).toFixed(2));
		}
	}
	if(_betList[symbolnd]){
		if(_betList[symbolnd].buy){
			$('.BS'+symbolnd+' .myBuy').html('$ '+Number(_betList[symbolnd].buy).toFixed(2));
		}
		
		if(_betList[symbolnd].sell){
			$('.BS'+symbolnd+' .mySell').html('$ '+Number(_betList[symbolnd].sell).toFixed(2));
		}
	}
}

client.joinOrCreate("my_room",user).then(room => {
	
	room.onStateChange.once(function(state) {
		console.log("initial room state:", state);
	});
	
	// new room state
	room.onStateChange(function(state) {
		// this signal is triggered on each patch
	});
	
	room.onLeave(function(state) {
		location.reload();	
	});
	
	// connect
	room.send({ action: 'connect'});
	
	// listen to patches coming from the server
	room.onMessage(function(message){
		switch (message.action){
			case 'connect':

				if(message.time>30){
					callFunctionOrder(message.time-31);
				}else{
					callFunctionWaiting(message.time);
				}
			break;
			case 'sv-payment':
				ShowNotification(message.data);
			break;
			case 'updateBalance':

				updatebalance(message.data.balance*1);
			break;
			case 'alert':
				if(message.data.action == 'bet'){
					console.log(message.data);
					if(message.data.reload == 1){
						location.reload();
					}
					updatebalance(message.data.data.balance);
					updateMybet(message.data.data);
					
				}
			break;
			case 'main':

				if(connect == false){
					$( ".divCenter" ).remove();
					connect = true;
				}
				timeSV = message.time;

				
				if(connect){
					
				}
				if(timeSV == 60){
					callFunctionOrder(timeSV-30);
				}
				if(timeSV == 30){
					callFunctionWaiting(timeSV);
				}
				
				
				
				
				if(message.time == 1){
					var x = getCookie('bet');
					if (x) {
					    room.send({ action: 'payment'});
					}
					
				}
				
				if(message.time == 5){
					room.send({ action: 'updateInfo'});
				}
				
				convertDataChart(message.data.chart);
				convertDataChart2(message.data.chart);
				
				if(message.data.statistical){

					if(_history[symbol].length >= 60){
						_history[symbol] = [message.data.statistical[symbol]];
						if(symbol!=symbolnd){
							_history[symbolnd] = [message.data.statistical[symbolnd]];
						}
						

					}else{
						
						_history[symbol].push(message.data.statistical[symbol]);
						if(symbol!=symbolnd){
							_history[symbolnd].push(message.data.statistical[symbolnd]);
						}
						
					}

					showStatistical(_history[symbol], 'tableHistory');
					showStatistical(_history[symbolnd], 'tableHistory2');
				}
				if(message.data.pieChart){
					DrawPieChart(message.data.pieChart, symbol)
					DrawPieChart2(message.data.pieChart, symbolnd)
				}
			break;
			case 'sv-notification':
				ShowNotification(message.data[0]);
			break;
			case 'sv-logout':
				window.location="https://exchange.premiumtrade.global/logout";
			case 'logout':

				window.location="https://exchange.premiumtrade.global/logout";
			break;
		}
		
	});
	$(function() {
		$('.amountBet').keyup(function(){
			_amount = $(this).val();
			$('.amountBet').val(_amount);
		});
		$('.bet').click(function(){
			
			_type = $(this).data('type');		
			_amount = $('.amountBet').val();
	
			_parent = $(this).parent();
			_symbol = $(this).data('symbol');
			_parent = $(this).parent();		
			if(timeSV >= 30){
				Swal.fire({
					position: 'top-end',
					title: 'error',
					text: 'Time out!',
					width: '300px',
					height: '200'
				});
				return;
			}
			if(_amount*1 < 2){
				Swal.fire({
					position: 'top-end',
					title: 'error',
					text: 'Minimum bet is 2$!',
					width: '300px',
					height: '200'
				});
				return;
			}
			setCookie('bet', true, 1);
			$('.bet').attr('disabled', true);
			setTimeout(function(){
			    $('.bet').attr('disabled', false);
			}, 200);
            
			room.send({ action: 'userBet', data: {symbol: _symbol, amount: _amount*1, type:_type}});
			
		});
		
		$('.boxImgcoin a').click(function(){
			_tem = $(this).data('amount');
            if(_tem > balance){
				amount = balance;
			}else{
				amount += Number(_tem);
			}
			
			$('.amountBet').val(amount);
		});
		
		$('.clearAmount').click(function(){
			amount = 0;
			$('.amountBet').val(amount);
		});
	});

});
function callFunctionOrder(_t){
	if(_t == 30){


		$('.myBuy').html('$ 0');
		$('.mySell').html('$ 0');
		$('body').addClass('start');
		playSound('Start');
	}
	clearTimeout(waitTime);
	

	
	$('.watting').html('<div class="waitingImg"><p style="color: #fff!important;font-size:18px;text-transform: uppercase;"><span style="color: #067a03;font-size: 20px;border: 2px #067a03 solid;display: inline-block;background:#000;">'+Number(_t)+'</span> Order</p></div>');
	playSound('Tick');
	ordeTime = setInterval(function(){
        if(_t<29){
			$('body').removeClass('win');
            $('body').removeClass('lose');
            $('body').removeClass('start');
		}
		
		if(_t>0){
			$('.watting').html('<div class="waitingImg"><p style="color: #fff!important;font-size:18px;text-transform: uppercase;"><span style="color: #067a03;font-size: 20px;border: 2px #067a03 solid;display: inline-block;background:#000;">'+Number(_t-1)+'</span> Order</p></div>');
		}
		
		_t--;

		playSound('Tick');
	}, 1000);
}

function callFunctionWaiting(_t){
	if(_t == 30){
		$('body').addClass('stop');
		playSound('Stop');
	}
	
	clearTimeout(ordeTime);
	
	
	
	$('body').removeClass('stop');
	$('.watting').html('<div class="waitingImg"><p style="color: red!important;font-size:18px;text-transform: uppercase;"><span style="color: red;font-size: 20px;display: inline-block;background:#000;">'+Number(_t)+'</span> Waiting</p></div>');
	playSound('Tick');
	waitTime = setInterval(function(){
		$('body').removeClass('stop');
		if(_t>0){
			$('.watting').html('<div class="waitingImg"><p style="color: red!important;font-size:18px;text-transform: uppercase;"><span style="color: red;font-size: 20px;display: inline-block;background:#000;">'+Number(_t-1)+'</span> Waiting</p></div>');
		}
		
		playSound('Tick');
		_t--;
	}, 1000);
}
function updatebalance(abc){


	$('#myBalance').html('$ '+(abc.toFixed(2)*1));
	balance = abc.toFixed(2)*1;
	
}
function updateMybet(data){

	if(data.type == 'buy'){
		_class = 'myBuy';
	}else{
		_class = 'mySell';
	}
	$('.BS'+data.symbol+' .'+_class).html('$'+(data.totalAmount).toFixed(2));
}


showStatistical(_history[symbol], 'tableHistory');
showStatistical(_history[symbolnd], 'tableHistory2');


function ShowNotification(data){
		setCookie('bet', false, 1);
	    if(data.status==1){
			playSound('Win');
		    $('body').addClass('win');
			$('.profit .box-body').html(' $'+(data.amount).toFixed(2));
	    }
	    if(data.status==2){;
			playSound('Lose');
		    $('body').addClass('lose');
		    $('.profit .box-body').html(' $'+Math.abs((data.amount).toFixed(2)));
	    }

    }

function showStatistical(data, _class){
	_htmlStatistical = '';

	for(_i = 0; _i < 60; _i++){
		if(_i == 0){
			_htmlStatistical += '<div class="col10">';
		}
		
		if(data[_i]){
			if(data[_i] == 1){
				_htmlStatistical += '<span class="up"></span>';	
			}else if(data[_i] == 2){
				_htmlStatistical += '<span class="down"></span>';	
			}else{
				_htmlStatistical += '<span class="draw"></span>';	
			}
			
		}else{
			_htmlStatistical += '<span class=""></span>';
		}
		
		if((_i+1)%6 == 0 && _i != 0){
			_htmlStatistical += '</div><div class="col10">';
		}
		
	}

	_htmlStatistical += '</div>';

	$('.'+_class).html(_htmlStatistical);

}


function currencyFormat(num) {

	return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

}


function GetURLParameter(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++){
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
}

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
function eraseCookie(name) {   
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}