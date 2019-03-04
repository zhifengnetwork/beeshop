window.onload = function () {
	var btn = document.getElementsByClassName('btn');
	var xbtn = document.getElementById('x-btn');
	var qbtn = document.getElementById('q-btn');
	var pop = document.getElementsByClassName('popup-wrap');
	
	for (var i=0;i<btn.length;i++) {
		btn[i].onclick = function() {
			for (var i=0;i<pop.length;i++) {
				pop[i].style.display = 'block';
			}
		}
	}
	
	
	xbtn.onclick = function() {
		for (var i=0;i<pop.length;i++) {
			pop[i].style.display = 'none';
		}
	}
	
	
	qbtn.onclick = function() {
		for (var i=0;i<pop.length;i++) {
			pop[i].style.display = 'none';
		}
	}
	
	
	
}