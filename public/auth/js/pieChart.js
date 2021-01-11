var ctx = document.getElementById('myDoughnutChart').getContext('2d');
var setValueChart = [
    50, 50
];
var myDoughnutChart = new Chart(ctx, {

	type: 'doughnut',

	// The data for our dataset
	data: {
		labels: ['buy', 'sell'],
		datasets: [{
			label: '',
			backgroundColor: ['#038016','#ff0000'],
			borderColor: ['#038016','#ff0000'],
			data: setValueChart
		}]
	},


	options: {
		legend: {
			display: false
		}
	}
});
//long
var ctx = document.getElementById('myDoughnutChart2').getContext('2d');
var setValueChart = [
    50, 50
];
var myDoughnutChart2 = new Chart(ctx, {

	type: 'doughnut',

	// The data for our dataset
	data: {
		labels: ['buy', 'sell'],
		datasets: [{
			label: '',
			backgroundColor: ['#038016','#ff0000'],
			borderColor: ['#038016','#ff0000'],
			data: setValueChart
		}]
	},


	options: {
		legend: {
			display: false
		}
	}
});

function DrawPieChart(data, symbol){

	$('.piChart1 .TotalBuy1').html(currencyFormat(data[symbol].buy[1]));
	$('.piChart1 .TotalSell1').html(currencyFormat(data[symbol].sell[1]));

	let total_count = parseFloat(data[symbol].buy[0]) + parseFloat(data[symbol].sell[0]);
	let calcbuy = isNaN(parseFloat(data[symbol].buy[0]) / total_count * 100) ? 50 : parseFloat(data[symbol].buy[0]) / total_count * 100;
	let calcsell = isNaN(parseFloat(data[symbol].sell[0]) / total_count * 100) ? 50 : parseFloat(data[symbol].sell[0]) / total_count * 100;
	myDoughnutChart.data.datasets[0].data[0] = calcbuy;
	myDoughnutChart.data.datasets[0].data[1] = calcsell;
	myDoughnutChart.update();
}

function DrawPieChart2(data, symbol){

	$('.piChart2 .TotalBuy2').html(currencyFormat(data[symbol].buy[1]));
	$('.piChart2 .TotalSell2').html(currencyFormat(data[symbol].sell[1]));

	let total_count = parseFloat(data[symbol].buy[0]) + parseFloat(data[symbol].sell[0]);
	let calcbuy = isNaN(parseFloat(data[symbol].buy[0]) / total_count * 100) ? 50 : parseFloat(data[symbol].buy[0]) / total_count * 100;
	let calcsell = isNaN(parseFloat(data[symbol].sell[0]) / total_count * 100) ? 50 : parseFloat(data[symbol].sell[0]) / total_count * 100;
	myDoughnutChart2.data.datasets[0].data[0] = calcbuy;
	myDoughnutChart2.data.datasets[0].data[1] = calcsell;
	myDoughnutChart2.update();
}

function currencyFormat(num) {
	return '$ ' + num.toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}