

var prediv = document.createElement("div");
prediv.setAttribute("id", "preloadMessage");
var prespan = document.createElement("span");
prespan.setAttribute("id", "preloadMessagetext");
var pretext = document.createTextNode("Seite wird geladen...");
prespan.appendChild(pretext);
prediv.appendChild(prespan);
document.getElementById("MySchedMainW").appendChild(prediv);


// Created by: Simon Willison | http://simon.incutio.com/


function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function () {
			if (oldonload) {
				oldonload();
			}
			func();
		}
	}
}

addLoadEvent(function () {
	document.getElementById("preloadMessage").style.display = "none";
});