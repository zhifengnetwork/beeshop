window.onload = function () {
	//	分享按钮
	var Main = document.getElementById('main');
	var Cancel = document.getElementById('cancel');
	var Share = document.getElementById('share');
	//	分享成功
	var Star = document.getElementById('star');
	var Xbtn = document.getElementById('xbtn');
	var Qbtn = document.getElementById('qbtn');
	var Info = document.getElementsByClassName('info');
	//	分享失败
	var Defeat = document.getElementById('defeat');
	var On = document.getElementById('on');
	var In = document.getElementById('in');
//	分享按钮
	Share.onclick = function() {
		Main.style.display = 'block';
	}
	Cancel.onclick = function() {
		Main.style.display = 'none';
	}
//	分享成功
	for(var i=0;i<Info.length;i++) {
		Info[i].onclick = function() {
			Star.style.display = 'block';
		}
	}
	Xbtn.onclick = function() {
		Star.style.display = 'none';
		Main.style.display = 'none';
		Defeat.style.display = 'block';
	}
	Qbtn.onclick = function() {
		Star.style.display = 'none';
		Main.style.display = 'none';
	}
//	分享失败
	In.onclick = function() {
		Defeat.style.display = 'none';
	}
	On.onclick = function() {
		Defeat.style.display = 'none';
	}
}