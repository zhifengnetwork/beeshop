//	商店
	var shop = document.getElementById('shop');
	var sList = document.getElementById('storeList');
	var None = document.getElementById('none');
	
	var xbtn = document.getElementsByClassName('x-btn');
	var qbtn = document.getElementsByClassName('q-btn');
	var Tbn = document.getElementById('qbtn');
 	
	var tank = document.getElementById('tank');
	
	var Of = document.getElementsByClassName('of');
	
	//蜂王幼峰
	var yf = document.getElementById('yf');
	var star = document.getElementById('star');
	//雄蜂
	var xf = document.getElementById('xf');
	/**雄蜂兑换**/
	var droneChange = document.getElementById('droneChange');
	var drone = document.getElementById('drone');
	var droneChangeAlert = document.getElementById('droneChangeAlert');
	//蜂王浆
	var The = document.getElementById('the');
	var qee = document.getElementById('qee');
	//蜜糖兑换框
	var geo = document.getElementById('geo');
	var mi = document.getElementById('mi');
	//露水兑换框
	var Lu = document.getElementById('lu');
	var Sui = document.getElementById('sui');
	//阳光值兑换框
	var Sun = document.getElementById('sun');
	var Rey = document.getElementById('rey');	
	
	None.onclick = function() {
		sList.style.display = 'none';
	}
	
	//蜂王幼峰
	yf.onclick = function() {
		star.style.display = 'block';
		sList.style.display = 'none';
	}
	//雄蜂-购买
	xf.onclick = function() {
		drone.style.display = 'block';
		sList.style.display = 'none';
	}
	//雄蜂-兑换
	droneChange.onclick = function(){
		console.log('雄蜂-兑换');
		droneChangeAlert.style.display = 'block';
		sList.style.display = 'none';
		
	}
	//蜂王浆
	The.onclick = function() {
		qee.style.display = 'block';
		sList.style.display = 'none';
	}
	//蜜糖兑换框
	geo.onclick = function() {
		mi.style.display = 'block';
		sList.style.display = 'none';
	}
	//露水兑换框
	Lu.onclick = function() {
		Sui.style.display = 'block';
		sList.style.display = 'none';
	}
	//阳光值兑换框
	Sun.onclick = function() {
		Rey.style.display = 'block';
		sList.style.display = 'none';
	}	
	
	    shop.onclick = function() {
	    	sList.style.display = 'block';
	    }

		//购买幼蜂
		Tbn.onclick = function() {

        	$('#cart4_form').submit();
		}

	    for(var i=0;i<xbtn.length;i++) {
	    	xbtn[i].onclick = function() {
	    		for(var i=0;i<Of.length;i++) {
	    			Of[i].style.display = 'none';
	    		}
	    	}
	    }

	    for(var i=0;i<qbtn.length;i++) {
	    	qbtn[i].addEventListener('click',function() {
	    		for(var i=0;i<Of.length;i++) {
	    			Of[i].style.display = 'none';
	    			
	    		}
	    	})
	    }
	    
		
//	提示框
	var hintWrap = document.getElementById('hint-wrap');
	var xtn = document.getElementById('x-tn');
	var qtn = document.getElementById('q-tn');
	
//	提示框1
	var hintWrap01 = document.getElementById('hint-wrap_1');
	var xtn01 = document.getElementById('x-tn_1');
	var qtn01 = document.getElementById('q-tn_1');
	var tank01 = document.getElementById('tank_1');
//	提示框2
	var hintWrap02 = document.getElementById('hint-wrap_2');
	var xtn02 = document.getElementById('x-tn_2');				
	var qtn02 = document.getElementById('q-tn_2');			
	var tank02 = document.getElementById('tank_2');
	
		xtn.onclick = function() {
	    	hintWrap.style.display = 'none';
	    }
		
		xtn01.onclick = function() {
	    	hintWrap01.style.display = 'none';
	    }
		xtn02.onclick = function() {
	    	hintWrap02.style.display = 'none';
	    }
		tank02.onclick = function() {
	    	hintWrap02.style.display = 'block';
	    }
		qtn02.onclick = function() {
	    	hintWrap02.style.display = 'none';
	    }
		// 喂养
		qtn.addEventListener('click',function() {
			$.ajax({
				type : 'get',
				url : '/index.php?m=Mobile&c=BeeShop&a=bee_feed',
				dataType : 'json',
				success : function(data){
					if(data.status == 1){
						hintWrap.style.display = 'none';
						tank01.style.display = 'block';
						tank.style.display = 'none';
						layer.msg(data.msg);
					}else{
						layer.msg(data.msg);
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					layer.msg('网络异常，请稍后重试');
				}
			})
		})
		
		tank.onclick = function() {
			hintWrap.style.display = 'block';
		}
		tank01.onclick = function() {
			hintWrap01.style.display = 'block';
		}
		// 交配
		qtn01.addEventListener('click',function() {
			$.ajax({
				type : 'get',
				url : '/index.php?m=Mobile&c=BeeShop&a=bee_mating',
				dataType : 'json',
				success : function(data){
					if(data.status == 1){
						hintWrap01.style.display = 'none';
						tank02.style.display = 'block';
						layer.msg(data.msg);
					}else{
						hintWrap01.style.display = 'none';
						layer.msg(data.msg);
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					layer.msg('网络异常，请稍后重试');
				}
			})

		})
		
		
		
$(document).ready(function(){
	//转盘添加
	$(".shows").on("click", function() {
		$(".content").show(1000);
		$(".bgColor").show()
	})
	$(".bgColor").on("click", function() {
		$(this).hide()
		$(".newShow").animate({
			left: "-80%"
		})
		$(".content").hide(500);
		$(".activity").animate({
			right:"-86%"
		})
	})
	
	//左滑出
	$(".extend").on("click", function() {
		$(".imgs").append("<img src='imges/ct.png'/>")
	})
	//slideDown
	$(".next").on("click", function() {
		$(".newShow").animate({
			left: "0"
		}, 500)
		$(".bgColor").show()
	})
	
	//右滑出
	$(".hot").on("click", function() {
		$(".activity").animate({
			right: "0"
		})
		$(".bgColor").show()
	})
	$(".back").on("click", function() {
		$(".activity").animate({
			right: "-86%"
		})
		$(".bgColor").hide()
	})
	//背影
	$('.view').on('click', function() {
		$('.event').hide();
		$('.view').hide();
	})
	$('.hot_1').on('click', function() {
		$('.event').show();
		$('.view').show();
	})
	//打扫(引用公共的弹框)
	$('#sweep').on('click',function(){
		$('.tk').show();
	})
	//守卫(引用公共的弹框)
	$('#guardBut').on('click',function(){
		$('.guardWrap').show();
	})
	//弹框（一行文字）-确认按钮
	$('.publicConfirmBut').on('click',function(){
		$('.publicWrap').hide();
	})
	//弹框（一行文字）-关闭按钮
	$('.publicCancelBut').on('click',function(){
		$('.publicWrap').hide();
	})
	
	
	//		公告弹框
		$('.t_Xbtn').on('click',function(){
			$('.t_Wrap').hide();
			$('.t_view').hide();
			$('.bgColor').hide();
			
		})
		$('.t_Qbtn').on('click',function(){
			$('.t_Wrap').hide();
			$('.t_view').hide();
		})
		$('.notice').on('click',function(){
			$('.t_Wrap').show();
			$('.t_view').show();
			$('.newShow').animate({
				left:"-80%"
				
			});
			$('.bgColor').hide();
		})
})	