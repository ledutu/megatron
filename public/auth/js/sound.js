var Sound = ["Tick"];
var soundOff = false;

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

function setCookie(name,value,days) {
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days*24*60*60*1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

checkSound = getCookie('soundOn');

if(checkSound && checkSound == -1){
	$(".sound_on").css("display", "none"); 
	$(".sound_off").css("display", "block"); 
}else{
	setCookie('soundOn',1,1);
}
$(".sound_on_off").click(function(){
	if(checkSound == 1){
		checkSound = -1;
		soundOff = true;
		$(".sound_on").css("display", "none"); 
		$(".sound_off").css("display", "block"); 
	}else {
		checkSound = 1;
		soundOff = false;
		$(".sound_on").css("display", "block");
		$(".sound_off").css("display", "none");
	}
	loadSound();
	setCookie('soundOn',checkSound,1);
	
});
		
function loadSound() {
	var audioPath = "sound/";
	var sounds = [
		{id:"Tick", src:"tick.mp3"},
		{id:"Stop", src:"stop.mp3"},
		{id:"Win", src:"win1.mp3"},
		{id:"Lose", src:"lose-game.mp3"},
		{id:"Start", src:"start.mp3"}
	];

	createjs.Sound.addEventListener("fileload", playSound);
	createjs.Sound.registerSounds(sounds, audioPath);
}
function playSound(id){
	if(checkSound==1){
		createjs.Sound.play(id);
	}
}