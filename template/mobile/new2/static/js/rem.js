	var dw = document.documentElement.clientWidth;
    	//设置html元素font-size属性的值
    	//document.documentElement.style.fontSize = dw / 7.5 + "px";
    	
    	//假设设计稿为640px的宽    750 / 7.5 = 100px
    	document.documentElement.style.fontSize = dw / 7.5 + "px";
    	
    	//页面大小改变时触发的事件
    	window.onresize = function(){
    		var dw = document.documentElement.clientWidth;
    		document.documentElement.style.fontSize = dw / 7.5 + "px";	
   
}