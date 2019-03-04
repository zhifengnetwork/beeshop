window.onload = function () {
	var Cai = document.getElementById('cai');
	var Star = document.getElementsByClassName('star');
	var Work = document.getElementById('star');
	var Xbtn = document.getElementsByClassName('x-btn');
	var Qbtn = document.getElementById('qbtn');
	var Zbtn = document.getElementById('zbtn');
	var Sur = document.getElementById('sur');
	var Sur01 = document.getElementById('sur_1');
	var SpyOn = document.getElementById('spyOn');
	var Scout = document.getElementById('scout');
	
	
	Cai.onclick = function() {
		Work.style.display = 'block';
	}
	SpyOn.onclick = function() {
		Scout.style.display = 'block';
	}
	
	for(var i=0;i<Xbtn.length;i++) {
		Xbtn[i].onclick = function() {
			for(var i=0;i<Star.length;i++) {
				Star[i].style.display = 'none';
			}
		}
	}
	Qbtn.onclick = function() {
		for(var i=0;i<Star.length;i++) {
			Star[i].style.display = 'none';
		}
		Sur.style.display = 'block';
		Cai.style.display = 'none';
	}
	
	Zbtn.onclick = function() {
		for(var i=0;i<Star.length;i++) {
			Star[i].style.display = 'none';
		}
		Sur01.style.display = 'block';
		SpyOn.style.display = 'none';
	}
	
}