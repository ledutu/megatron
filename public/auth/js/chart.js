google.charts.load('current', {'packages':['corechart']});


var chart, options, dataChart, dataChart2;

function convertDataChart(data){
	_time = toTimestamp(getTime())+30000;
	aaa = convertTimetoDate(_time);
	
	dataChart = [];
	_time = toTimestamp(getTime())+30000;
	data[symbol].forEach(function(element){
		var timer = convertTimetoDate(_time);
		_arrayTemp = [timer, element.high, element.open, element.close, element.low];
		_time = _time-30000;
		dataChart.push(_arrayTemp);
	});
	$('.currentPrice').html('High: '+data[symbol][0].high+' - Open: '+data[symbol][0].open+' - Close: '+data[symbol][0].close+' - Low: '+data[symbol][0].low);

	dataChart.reverse(); 
	google.charts.setOnLoadCallback(drawChart);

}

function convertDataChart2(data){
	_time = toTimestamp(getTime())+30000;
	aaa = convertTimetoDate(_time);

	dataChart2 = [];
	_time = toTimestamp(getTime())+30000;
	data[symbolnd].forEach(function(element){
		var timer = convertTimetoDate(_time);
		_arrayTemp = [timer, element.high, element.open, element.close, element.low];
		_time = _time-30000;
		dataChart2.push(_arrayTemp);
	});
	$('.currentPrice2').html('High: '+data[symbolnd][0].high+' - Open: '+data[symbolnd][0].open+' - Close: '+data[symbolnd][0].close+' - Low: '+data[symbolnd][0].low);

	dataChart2.reverse(); 

	google.charts.setOnLoadCallback(drawChart2);

}

function drawChart() {

	var data = google.visualization.arrayToDataTable(dataChart, true);
	var res = 370;
	var cWidth = '90%';
	var cRight = '5%';
    var width = $(window).width();
    $(window).resize(function() {
		if(width > 770 && width < 1400){
			res = 300;
			cWidth = '85%';
			cRight = '5%';
		}else if(width <= 770){
			res = 200;
			cWidth = '80%';
			cRight = '6%';
		}else {
			res = 370;
		}
    });
	if(width > 770 && width < 1400){
		res = 300;
		cWidth = '85%';
		cRight = '5%';
	}else if(width <= 770){
		res = 200;
		cWidth = '80%';
		cRight = '6%';
	}else {
		res = 370;
		cWidth = '90%';
		cRight = '5%';
	}
	options = {
		legend: 'none',
		colors: ['#aaaaaa'],
		textStyle: {color: 'white'},
		tooltip: {trigger: 'none',},
		chartArea:{width: cWidth, height:'75%', right: cRight},
		slantedText: true,
		slantedTextAngle: 90,
		fallingColor:{fill:'#ffffff'},
		height:res,
		backgroundColor: { fill:'transparent' },
		candlestick: {
			fallingColor: { strokeWidth: 0, fill: '#ff0000' }, // red
			risingColor: { strokeWidth: 0, fill: '#038016' }   // green
		},
		colors: ['#1b87d2']

	};

	chart = new google.visualization.CandlestickChart(document.getElementById('chart'));
	
	chart.draw(data, options);


}

function drawChart2() {
	var data = google.visualization.arrayToDataTable(dataChart2, true);
	var res = 370;
	var cWidth = '90%';
	var cRight = '5%';
    var width = $(window).width();
    $(window).resize(function() {
		if(width > 770 && width < 1400){
			res = 300;
			cWidth = '85%';
			cRight = '5%';
		}else if(width <= 770){
			res = 200;
			cWidth = '80%';
			cRight = '6%';
		}else {
			res = 370;
		}
    });
	if(width > 770 && width < 1400){
		res = 300;
		cWidth = '85%';
		cRight = '5%';
	}else if(width <= 770){
		res = 200;
		cWidth = '80%';
		cRight = '6%';
	}else {
		res = 370;
		cWidth = '90%';
		cRight = '5%';
	}
	options = {
		legend: 'none',
		colors: ['#aaaaaa'],
		textStyle: {color: 'white'},
		tooltip: {trigger: 'none',},
		chartArea:{width: cWidth, height:'75%', right: cRight},
		slantedText: true,
		slantedTextAngle: 90,
		fallingColor:{fill:'#ffffff'},
		height:res,
		backgroundColor: { fill:'transparent' },
		candlestick: {
			fallingColor: { strokeWidth: 0, fill: '#ff0000' }, // red
			risingColor: { strokeWidth: 0, fill: '#038016' }   // green
		},
		colors: ['#1b87d2']

	};

	chart = new google.visualization.CandlestickChart(document.getElementById('chart2'));
	
	chart.draw(data, options);

}

function toTimestamp(strDate){
   var datum = Date.parse(strDate);
   return datum;
}

function getTime(){
	var today = new Date();
	var dd = String(today.getDate()).padStart(2, '0');
	var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	var yyyy = today.getFullYear();
	var H = today.getHours();
	var m = today.getMinutes();
	
	today = mm + '/' + dd + '/' + yyyy + ' ' + H + ':' + m;
	return today;
}

function convertTimetoDate(unix_timestamp){
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