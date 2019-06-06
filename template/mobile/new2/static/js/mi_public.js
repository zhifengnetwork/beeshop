/*进入页面重新加载*/
window.onpageshow=function(e){
    if(e.persisted) {
        window.location.reload() 
    }
};
/*后台=> 传个状态,和背景音频*/
$.ajax({
	type: "post",
	url: "/mobile/bee/type",
	async: true,
	data: null,
	success: function(req){
		console.log(req);
		console.log($.parseJSON(req)['music_type']);
		var judge = $.parseJSON(req)['music_type'];
		/*2是开启，1是关闭*/
		if(judge == 2){
			$('#musicAudio source').attr('src','/template/mobile/new2/static/images/imge/gequ.mp3');
			var audioId = document.getElementById('musicAudio');
			audioId.play();								
			console.log('音乐开启');
										
		}else {
			$('#musicAudio source').attr('src','')
			console.log('音乐关闭');
		}
	},
	error: function(){
		console.log('请求错误');
	},
});


/**
 * 背景音乐
 * --创建页面监听，等待微信端页面加载完毕 触发音频播放
 * **/
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
/**
 * --创建触摸监听，当浏览器打开页面时，触摸屏幕触发事件，进行音频播放
 **/
document.addEventListener('touchstart', function () {
    function audioAutoPlay() {
        var audio = document.getElementById('musicAudio');
            audio.play();
    }
    audioAutoPlay();
});