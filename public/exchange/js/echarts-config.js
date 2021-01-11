var amount = 0;
var ordeTime, waitTime;
var timeSV = 0;
var symbol = 'BTCUSDT';
var balane = 0;
var tab = 'today';
var coin = 'eusd';
var _s = {}
loadSound();
client.joinOrCreate("my_room", user).then(room => {

  coin = getUrlParameter('coin');

  room.onStateChange.once(function (state) {
    console.log("initial room state:", state);
  });

  // new room state
  room.onStateChange(function (state) {
    // this signal is triggered on each patch
  });

  room.onLeave(function (state) {
    location.reload();
  });


  // listen to patches coming from the server
  room.onMessage(function (message) {

    switch (message.action) {
      case 'connect':
        getHistory();
        getMyBet();
        if (message.time > 30) {
          callFunctionWaiting(60 - message.time);

        } else {

          callFunctionOrder(30 - message.time);
        }

        updateBalance(message.balance);
		
        convertStatistical();

        break;

      case 'updateBalance':
        updateBalance(message.data.balance);

        break;
      case 'main':
        timeSV = message.time;
        if (timeSV == 0) {
          $('#order tbody').html('');

          callFunctionOrder(30);
          $('.btn-buy span').html('BUY');
          $('.btn-sell span').html('SELL');
          

        }

		if(timeSV == 1){
			
          var ChartTemp = message.data[symbol];
			//ChartTemp.reverse();

          	_d = ChartTemp[ChartTemp.length - 2];
			

          _t=3;
          if(_d.open < _d.close){
              _t=1;
          }else if(_d.open > _d.close){
              _t=2;
          }

          convertStatistical(_t);
        }

        if (timeSV == 5) {
          room.send({ action: 'updateInfo', currency: getUrlParameter('coin') });
          getHistory(tab);
        }

        if (timeSV == 30) {
          callFunctionWaiting(30);
        }
		
		

        convertData(message.data.BTCUSDT, timeSV);
        convertProcess(message.bet);

        break;
      case 'notification':
        updateBalance(message.balance);

        convertProfit(message.data)
        break;
      case 'order':

        convertOrder(message.data);
        break;
      case 'alert':
        if (message.data.action == 'bet' && message.data.status) {

          updateBalance(message.data.balance);
          if (message.data.reload == 1) {
            location.reload();
          }

          if (message.data.type == 'buy') {
            $('body').addClass('buy');
            $('.buy-div span').html(message.data.amount);
            $('.btn-buy span').html(message.data.data.totalAmount);
            setTimeout(function () {
              $('body').removeClass('buy');
            }, 2000);
          } else if (message.data.type == 'sell') {
            $('body').addClass('sell');
            $('.sell-div span').html(message.data.amount);

            $('.btn-sell span').html(message.data.data.totalAmount);
            setTimeout(function () {
              $('body').removeClass('sell');
            }, 2000);
          }

        }
        break;
    }

  });
  $(function () {
    $('.bet').click(function () {
      var BetType = $(this).data('type');
      var BetAmount = $('#amount').val();
      var BetSymbol = 'BTCUSDT';
      if (timeSV >= 30) {
        console.log('timeout');
        return;
      }

      if (BetAmount == 'NaN' || parseInt(BetAmount * 1) <= 0) {
        console.log('please enter amount');
        //Swal.fire({
        //	position: 'top-end',
        //	title: 'error',
        //	text: 'Please enter amount!',
        //	width: '300px',
        //	height: '200'
        //});                                               
        return;
      }
      if (BetAmount < 1) {
        console.log('please enter amount');
        Swal.fire({
          position: 'top-center',
          title: 'error',
          text: 'Min 1$!',
          width: '300px',
          height: '200'
        });
        return;
      }

      $('.bet').attr('disabled', true);
      setTimeout(function () {
        $('.bet').attr('disabled', false);
      }, 200);

      room.send({ action: 'userBet', data: { symbol: BetSymbol, amount: parseInt(BetAmount * 1), type: BetType, currency: getUrlParameter('coin') } });
    });

    $('.history').click(function () {
      _type = $('#history-menu .active a.type').text().toUpperCase();

      getHistory(_type);

    });
    $('.history2').click(function () {
      _type = $(this).text().toUpperCase();

      getHistory(_type);

    });
  });

});

function convertProcess(_data) {

  _sell = _data[symbol].sell;
  _buy = _data[symbol].buy;
  _percenSell = (Number(_sell) * 100 / (Number(_sell) + Number(_buy))).toFixed(2);
  $('.sell-progress').css('width', _percenSell + '%');
  _sellpercen = Number(_percenSell).toFixed(2);
  $('.sell-percen').html(_sellpercen + '%');
  $('.buy-progress').css('width', (100 - _percenSell) + '%');
  _buypercen = Number(100 - _percenSell).toFixed(2);
  $('.buy-percen').html(_buypercen + '%');

}

function getMyBet() {
  if (Object.keys(_betList).length) {
    $('.btn-buy span').html(_betList[symbol].buy);
    $('.btn-sell span').html(_betList[symbol].sell);
  }
}

function getHistory(_t = 'CLOSE') {
    $.get(base_url + "/history", { status: _t }, function (data) {
        _html = '';
        _profit = 0;
        _waiting = 0;

        data.forEach(function (item) {
          	_html += '<li role="presentation" class="account-picture history-trades mt-2"><div class=" cursor-pointer grid grid-cols-5 px-3" ><div class="col-span-3 flex flex-col flex-1"><span class="balance-value">Amount: $' + Number(item.GameBet_Amount).toFixed(2) + '</span><span class="balance-value">Profit: $' + Number(item.GameBet_AmountWin).toFixed(2) + '</span><span class="balance-name">Time: ' + timeConverterv2(item.GameBet_datetime) + '</span></div><div class="col-span-2 flex flex-col justify-center self-center items-center"><img src="' + base_url + '/exchange/img/icon/BTC.png" alt=""><span class=" white balance-name text-center">BTCUSDT ' + ((item.GameBet_Currency == 99) ? '(demo)' : '(Live)') + ' </span></div></div></li>';
        });

        $('#history-menu .tab-content .active ul').html(_html);
    });
}

function convertProfit(_d) {
  if (Number(_d.amount) < 0) {
    playSound('Lose');
    $('body').addClass('lose');
    $('.lose-div span').html((_d.amount).toFixed(2));
                            
  }
  else {
    playSound('Win');
    $('body').addClass('win');
    $('.win-div span').html((_d.amount).toFixed(2));
  }

  setTimeout(function () {
    $('body').removeClass('lose');
    $('body').removeClass('win');
  }, 5000);
}

function convertStatistical(_d) {
  _d1 = { buy: 0, sell: 0 };
  _d2 = { buy: 0, sell: 0 };
  _d3 = { buy: 0, sell: 0 };
  _j = 0;
	
  

  if (typeof (_d) != "undefined") {
    statistical.push(_d);
  }
	
  if (statistical.length >= 60) {
    statistical = [];
  }
  for (i = statistical.length; i > 0; i--) {
    if (statistical[i]) {
      if (_j <= 7) {
        if (statistical[i] == 1) {
          _d1.buy++;
          _d2.buy++;
          _d3.buy++;
        } else if (statistical[i] == 2) {
          _d1.sell++;
          _d2.sell++;
          _d3.sell++;
        }
      }

      if (_j > 7 && _j <= 9) {
        if (statistical[i] == 1) {
          _d2.buy++;
          _d3.buy++;
        } else if (statistical[i] == 2) {
          _d2.sell++;
          _d3.sell++;
        }
      }

      if (_j > 12 && _j <= 21) {
        if (statistical[i] == 1) {
          _d3.buy++;
        } else if (statistical[i] == 2) {
          _d3.sell++;
        }
      }
      _j++;
    }
  }

  convertd1(_d1, 'qua_1');
  convertd2(_d2, 'qua_2');
  convertd3(_d3, 'qua_3');
  data = statistical;
  _htmlStatistical = '';

  for (_i = 0; _i < 60; _i++) {
    if (_i == 0) {
      _htmlStatistical += '<div class="col-span-1 grid grid-cols-1 gap-2">';
    }

    if (data[_i]) {
      if (data[_i] == 1) {
        _htmlStatistical += '<span class="col-span-1 buy"></span>';
      } else if (data[_i] == 2) {
        _htmlStatistical += '<span class="col-span-1 sell"></span>';
      } else {
        _htmlStatistical += '<span class="col-span-1 draw"></span>';
      }

    } else {
      _htmlStatistical += '<span class="col-span-1"></span>';
    }

    if ((_i + 1) % 6 == 0 && _i != 0) {
      _htmlStatistical += '</div><div class="col-span-1 grid grid-cols-1 gap-2">';
    }

  }

  _htmlStatistical += '</div>';

  $('#Statistical').html(_htmlStatistical);

}


function updateBalance(_b) {
  	balane = Number(_b);
  	$("#UserBalance").html('$' + (balane.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')));
	
	if(getUrlParameter('coin') == 'live'){
		$(".balance-value").html('$' + (balane.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')));
	}else{
		//$(".demo-balance").html('$' + (balane.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')));
	}
}

function selectAmount(_m) {
  amount = _m;
  if (amount == 'all') {
    $('#amount').val(parseInt(balane));
    document.getElementById("amount_2").innerHTML = "+$" + parseInt(balane);

  } else {
    var _currenAmount = $('#amount').val();
    if (_currenAmount == "") {
      _currenAmount = 0;
    } else (
      _currenAmount = parseInt(_currenAmount)
    )
    if (_currenAmount + _m < 0) {
      $('#amount').val(0);

    }
    else {
      amount = parseInt(_currenAmount) + parseInt(_m)
      $('#amount').val(parseInt(amount));
    }
    document.getElementById("amount_2").innerHTML = "+$" + parseInt(amount < 0 ? 0 : amount);

  }

}


function calculation(_t) {
  amount = $('#amount').val();
  switch (_t) {
    case '/3':
      amount = Number(amount) / 3;
      break;
    case '/2':
      amount = Number(amount) / 2;
      break;
    case '-1':
      amount = Number(amount) - 1;
      break;
    case '+1':
      amount = Number(amount) + 1;
      break;
    case '*2':
      amount = Number(amount) * 2;
      break;
    case '*3':
      amount = Number(amount) * 3;
      break;
  }
  if (amount < 1) {
    amount = 1;
  }
  $('#amount').val(parseInt(amount));
}


function convertData(_d, _t) {

  data = [];
  var today = new Date();
  var s = today.getSeconds();
  _time = toTimestamp(getTime()) + 30000;
  if (s < 30) {

    _time = toTimestamp(getTime());
  }

  _d.reverse();

  _d.forEach(function (item) {

    var timer = convertTimetoDate(_time);
    data.push([timer, item.open, item.close, item.low, item.high, item.volume]);
    _time = _time - 30000;
  });

  showChart(data.reverse(), _t);

}

function convertOrder(_d) {

  _html = '<tr class="order-' + _d.order + '"><td>' + _d.symbol + '</td><td>' + _d.user + '</td><td>' + _d.order + '</td><td>' + ((_d.amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')) + '</td><td>' + convertTimetoDate(_d.time * 1000) + '</td></tr>';
  _fullHtml = $('#order tbody').html();

  _fullHtml = _html + _fullHtml;
  $('#order tbody').html(_fullHtml);
  if (_d.user == user.subID) {
    $('.btn-' + _d.order + ' span').html(_d.amount);
  }


}

function callFunctionOrder(_t) {

  $(".timeText").html("Order");
  $('.bet').removeClass('disable');
  $(".bet").attr("disabled", false);
  // $('.main-count').addClass('CountOrder');
  // $('.main-count').removeClass('CountWaiting');
  if (_betList.length == 0) {
    $('.btn-buy span').html('BUY');
    $('.btn-sell span').html('SELL');
  }
  if (_t == 30) {
    amount = 0;
  }
  clearTimeout(waitTime);
  clearTimeout(ordeTime);
  ordeTimeTemp = _t;
  $('.main-count').html(Number(_t));

  ordeTime = setInterval(function () {
    if (_t > 0) {
      _t--;
      $('.main-count').html(Number(_t));

      ordeTimeTemp = _t;
      playSound('Tick');                           

    }
  }, 1000);
}

function callFunctionWaiting(_t) {

  $(".timeText").html("Waiting");
  $('.bet').addClass('disable');
  $(".bet").attr("disabled", true);
  // $('.main-count').addClass('CountWaiting');
  // $('.main-count').removeClass('CountOrder');
  if (_t == 30) {
    $('body').addClass('stop');

  }
  clearTimeout(waitTime);
  clearTimeout(ordeTime);
  $('.main-count').html(Number(_t));

  waitTime = setInterval(function () {
    $('body').removeClass('stop');
    if (_t > 0) {
      _t--;
      $('.main-count').html(Number(_t));

		if(_t> 10){
        	playSound('Tick');
        }


		if(_t == 10){
           	playSound('TS');         
        }
    }

  }, 1000);
}

function toTimestamp(strDate) {
  var datum = Date.parse(strDate);
  return datum;
}

function getTime() {
  var today = new Date();
  var dd = String(today.getDate()).padStart(2, '0');
  var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
  var yyyy = today.getFullYear();
  var H = today.getHours();
  var m = today.getMinutes();


  today = mm + '/' + dd + '/' + yyyy + ' ' + H + ':' + m;
  return today;
}

function convertTimetoDate(unix_timestamp) {

  var date = new Date(unix_timestamp);

  // Hours part from the timestamp
  var hours = date.getHours();
  // Minutes part from the timestamp
  var minutes = "0" + date.getMinutes();
  // Seconds part from the timestamp
  var seconds = "0" + date.getSeconds();

  // Will display time in 10:30:23 format
  var formattedTime = minutes.substr(-2) + ':' + seconds.substr(-2);
  return formattedTime;
}

function timeConverterv2(UNIX_timestamp) {
  var a = new Date(UNIX_timestamp * 1000);
  var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var sec = a.getSeconds();
  var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec;
  return time;
}

var getUrlParameter = function getUrlParameter(sParam) {
  var sPageURL = window.location.search.substring(1),
    sURLVariables = sPageURL.split('&'),
    sParameterName,
    i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');

    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
    }
  }
};