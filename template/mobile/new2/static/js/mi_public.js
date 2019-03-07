////弹框（一行文字）-确认按钮
//$('.publicConfirmBut').on('click',function(){
//	$('.publicWrap').hide();
//})
////弹框（一行文字）-关闭按钮
//$('.publicCancelBut').on('click',function(){
//	$('.publicWrap').hide();
//})

/*动态 添加背景音乐*/
var strAudio = '';
/*后台=> 传个状态,和背景音频*/
var judge = 1;
if(judge == 1){
	strAudio += '<audio id="musicAudio" autoplay="autoplay" loop="loop">';
			strAudio += '<source src="/template/mobile/new2/static/images/imge/gequ.mp3" type="audio/mpeg">';
		strAudio += '</audio>';
	$('body').append(strAudio);
}

/*背景音乐*/
document.addEventListener('DOMContentLoaded', function() {
	function audioAutoPlay() {
		var audio = document.getElementById('musicAudio');
		audio.play();
		document.addEventListener("WeixinJSBridgeReady", function() {
			audio.play();
		}, false);
	}
	audioAutoPlay();
}); 