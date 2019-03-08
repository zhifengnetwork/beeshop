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
$.ajax({
	type: "post",
	url: "/mobile/bee/music_type",
	async: true,
	data: {
		type: 0,
	},
	success: function(req){
		console.log(req);
		console.log($.parseJSON(req));
		var judge = $.parseJSON(req)['music_type'];
		if(judge == 1){
			strAudio += '<audio id="musicAudio" autoplay="autoplay" loop="loop">';
					strAudio += '<source src="/template/mobile/new2/static/images/imge/gequ.mp3" type="audio/mpeg">';
				strAudio += '</audio>';
			$('body').append(strAudio);
		}else {
			console.log('音乐关闭');
		}
	},
	error: function(){
		console.log('请求错误');
	},
});



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