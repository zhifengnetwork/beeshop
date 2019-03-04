	
	function setProcess() {
		var processbar = document.getElementById("processbar");
	
		processbar.style.width = parseInt(processbar.style.width) + 1 + "%";
		processbar.innerHTML = processbar.style.width;
		if(processbar.style.width == "100%") {
			window.clearInterval(bartimer);
			// var domain = document.domain;
			var url = '/Mobile/Bee/beeIndex';
			window.location = url;
		}
	}
	var bartimer = window.setInterval(function() {
		setProcess();
	}, 30);
	window.onload = function() {
		bartimer;
	}