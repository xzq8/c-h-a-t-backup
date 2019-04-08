var i=0;
function blink(){
	document.getElementById("ftcolor").className="changecolor"+i%2;
	i++;
}
//setInterval(blink, 500);