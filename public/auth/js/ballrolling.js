var ballanimation;
var z=0;
var scaleObj=0.1;
var ctx2 = canvasDraw2.getContext('2d');
var plate = new Image();
plate.src = 'img/plate.png';

function rollingTheBall(){
	var y=378;
	var	amplitudeRight = 458;
	var	amplitudeLeft=298;
	enableAutoplay(shake);
	rollLeft();	
	setTimeout(function(){
		
	},2000)
	
	function rollRight(){
		ctx2.clearRect(0,0,canvasDraw2.width,canvasDraw2.height);
		y+=20;
		ctx2.drawImage(board,0,0);
		ctx2.drawImage(ball,y,0);
		ctx2.drawImage(bet,330,board.height+10)
		ctx2.drawImage(chip,280,board.height+45);
		ctx2.fillText(moneyBet, 545, board.height+30);
		for(var i = 1;i<=25;i++){
			if(choose[i]!=-1){
				if(moneyForEachBet[i]!=0){
					ctx2.drawImage(brightBoard,buttonRow[i].x1,buttonRow[i].y1,buttonRow[i].x2,buttonRow[i].y2,buttonRow[i].x1,buttonRow[i].y1,buttonRow[i].x2,buttonRow[i].y2);
				}
			}
		}

		ballanimation = requestAnimationFrame(rollRight);
		if(y>=amplitudeRight){
			cancelAnimationFrame(ballanimation);
			if(amplitudeRight!=378){
				amplitudeRight-=10;
				ballanimation = requestAnimationFrame(rollLeft);
			}
			else{
				canvasDraw2.style.display = 'none';
				canvasDraw.style.display = 'block';
				textContent=2;
				showTextOnShaker(textContent,0);
			}
		}
	}

	function rollLeft(){
		ctx2.clearRect(0,0,canvasDraw2.width,canvasDraw2.height);
		y-=20;
		ctx2.drawImage(board,0,0);
		ctx2.drawImage(ball,y,0);
		ctx2.drawImage(bet,330,board.height+10)
		ctx2.drawImage(chip,280,board.height+45);
		ctx2.font = "15px serif";
		ctx2.fillStyle = "#FFF";
		ctx2.textAlign = "right";
		ctx2.fillText(moneyBet, 545, board.height+30);
		for(var i = 1;i<=25;i++){
			if(choose[i]!=-1){
				if(moneyForEachBet[i]!=0){
					ctx2.drawImage(brightBoard,buttonRow[i].x1,buttonRow[i].y1,buttonRow[i].x2,buttonRow[i].y2,buttonRow[i].x1,buttonRow[i].y1,buttonRow[i].x2,buttonRow[i].y2);
				}
			}
		}

		ballanimation = requestAnimationFrame(rollLeft);
		if(y<=amplitudeLeft){
			cancelAnimationFrame(ballanimation);
			if(amplitudeLeft!=378){
				amplitudeLeft+=10;
				ballanimation = requestAnimationFrame(rollRight);
			}
			else{
				canvasDraw2.style.display = 'none';
				canvasDraw.style.display = 'block';
				textContent=2;
				showTextOnShaker(textContent,0);
			}
		}
	}
}
var ballanimation;
var z=0;
var scaleObj=0.1;
var ctx2 = canvasDraw2.getContext('2d');
var plate = new Image();
plate.src = 'img/plate.png';

function rollingTheBall(){
	var y=378;
	var	amplitudeRight = 458;
	var	amplitudeLeft=298;
	enableAutoplay(shake);
	rollLeft();	
	setTimeout(function(){
		
	},2000)
	
	function rollRight(){
		ctx2.clearRect(0,0,canvasDraw2.width,canvasDraw2.height);
		y+=20;
		ctx2.drawImage(board,0,0);
		ctx2.drawImage(ball,y,0);
		ctx2.drawImage(bet,330,board.height+10)
		ctx2.drawImage(chip,280,board.height+45);
		ctx2.fillText(moneyBet, 545, board.height+30);
		for(var i = 1;i<=25;i++){
			if(choose[i]!=-1){
				if(moneyForEachBet[i]!=0){
					ctx2.drawImage(brightBoard,buttonRow[i].x1,buttonRow[i].y1,buttonRow[i].x2,buttonRow[i].y2,buttonRow[i].x1,buttonRow[i].y1,buttonRow[i].x2,buttonRow[i].y2);
				}
			}
		}

		ballanimation = requestAnimationFrame(rollRight);
		if(y>=amplitudeRight){
			cancelAnimationFrame(ballanimation);
			if(amplitudeRight!=378){
				amplitudeRight-=10;
				ballanimation = requestAnimationFrame(rollLeft);
			}
			else{
				canvasDraw2.style.display = 'none';
				canvasDraw.style.display = 'block';
				textContent=2;
				showTextOnShaker(textContent,0);
			}
		}
	}

	function rollLeft(){
		ctx2.clearRect(0,0,canvasDraw2.width,canvasDraw2.height);
		y-=20;
		ctx2.drawImage(board,0,0);
		ctx2.drawImage(ball,y,0);
		ctx2.drawImage(bet,330,board.height+10)
		ctx2.drawImage(chip,280,board.height+45);
		ctx2.font = "15px serif";
		ctx2.fillStyle = "#FFF";
		ctx2.textAlign = "right";
		ctx2.fillText(moneyBet, 545, board.height+30);
		for(var i = 1;i<=25;i++){
			if(choose[i]!=-1){
				if(moneyForEachBet[i]!=0){
					ctx2.drawImage(brightBoard,buttonRow[i].x1,buttonRow[i].y1,buttonRow[i].x2,buttonRow[i].y2,buttonRow[i].x1,buttonRow[i].y1,buttonRow[i].x2,buttonRow[i].y2);
				}
			}
		}

		ballanimation = requestAnimationFrame(rollLeft);
		if(y<=amplitudeLeft){
			cancelAnimationFrame(ballanimation);
			if(amplitudeLeft!=378){
				amplitudeLeft+=10;
				ballanimation = requestAnimationFrame(rollRight);
			}
			else{
				canvasDraw2.style.display = 'none';
				canvasDraw.style.display = 'block';
				textContent=2;
				showTextOnShaker(textContent,0);
			}
		}
	}
}
