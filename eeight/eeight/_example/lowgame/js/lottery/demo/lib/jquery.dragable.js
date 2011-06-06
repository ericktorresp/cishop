function getWindowSize() {
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	return([myWidth,myHeight]);
}
;(function($){
    $.fn.dragable = function(opts){
        opts = $.extend( {}, $.fn.dragable.defaults, opts || {} ); //根据参数初始化默认配置
        var iDiffX = 0;
    	var iDiffY = 0;
    	//this.oDragable = dragable;
    	//this.oHandler = handler;
    	if( opts.handler == null || opts.handler == "" ){
    	    opts.handler = this;
    	}
/*
    	function mouseDown(){
    	    document.body.onselectstart = function () {return false;};
    		document.body.style.userSelect = "none";
    		document.body.style.MozUserSelect = "none";
    		//var object = arguments.callee.object;
    		var event = arguments[0] || window.event;
    		iDiffX = event.clientX - $(this)[0].offsetLeft;
    		iDiffY = event.clientY - $(this)[0].offsetTop;
    		document.body.onmousemove = object.mouseMove;
    		document.body.onmouseup = object.mouseUp;
    		//object.oHandler.style.cursor = "move";
    		$(opts.handler).css({cursor:"move"});
    	}
    	this.mouseDown.object = this;
    	
    	this.oHandler.onmousedown = this.mouseDown;*/
    	
    	$(opts.handler).mousedown(function(){
    	    document.body.onselectstart = function () {return false;};
    		document.body.style.userSelect = "none";
    		document.body.style.MozUserSelect = "none";
    		var event = arguments[0] || window.event;
    		iDiffX = event.clientX - $(this)[0].offsetLeft;
    		iDiffY = event.clientY - $(this)[0].offsetTop;
    		document.body.onmousemove = $(this).mousemove();
    		document.body.onmouseup = $(this).mouseup();
    		//object.oHandler.style.cursor = "move";
    		$(opts.handler).css({cursor:"move"});
    	});
    	
    	/*
    	this.mouseMove = function() {
    		var object = arguments.callee.object;
    		var event = arguments[0] || window.event;
    		var wWidth = getWindowSize()[0];
    		if ((event.clientX - object.iDiffX) >= 0 && (event.clientX - object.iDiffX + object.oDragable.clientWidth) <= wWidth - 20){
    			//object.oDragable.style.left = event.clientX - object.iDiffX + "px";
    			$(this).css({left: event.clientX - object.iDiffX + "px"});
    		}
    		else {
    			if ((event.clientX - object.iDiffX) < 0){
    				object.oDragable.style.left = 0 + "px";
    			}
    			else {
    				object.oDragable.style.left = wWidth - object.oDragable.clientWidth - 20 + "px";
    			}
    		}
    		//obj.style.left = tempLeft + "px";
    		if ((event.clientY - object.iDiffY) >= 0){
    			object.oDragable.style.top = event.clientY - object.iDiffY + "px";
    		}
    		else {
    			object.oDragable.style.top = 0 + "px";
    		}
    		
    	};
    	//this.mouseMove.object = this;*/
    	$(this).mousemove(function(){
    		var event = arguments[0] || window.event;
    		var wWidth = getWindowSize()[0];
    		if ((event.clientX - iDiffX) >= 0 && (event.clientX - iDiffX + $(this)[0].clientWidth) <= wWidth - 20){
    			$(this).css({left: event.clientX - iDiffX + "px"});
    		}
    		else {
    			if ((event.clientX - iDiffX) < 0){
    				$(this).css({left: 0 + "px"});
    			}
    			else {
    				//object.oDragable.style.left = wWidth - object.oDragable.clientWidth - 20 + "px";
    				$(this).css({left: wWidth - $(this)[0].clientWidth - 20 + "px"});
    			}
    		}
    		if ((event.clientY - iDiffY) >= 0){
    			$(this).css({top: event.clientY - iDiffY + "px"});
    		}
    		else {
    			$(this).css({top: 0 + "px"});
    		}
    	});
    	
    	$(this).mouseup(function(){
    	    document.body.onselectstart = "";
    		document.body.style.userSelect = "";
    		document.body.style.MozUserSelect = "";
    		document.body.onmousemove = "";
    		document.body.onmouseup = "";
    		$(opts.handler).css({cursor:"move"});
    	});
    	/*
    	function mouseUp(){
    		//var object = arguments.callee.object;
    		document.body.onselectstart = "";
    		document.body.style.userSelect = "";
    		document.body.style.MozUserSelect = "";
    		document.body.onmousemove = "";
    		document.body.onmouseup = "";
    		$(opts.handler).css({cursor:"move"});//.style.cursor = "";
    	};
    	//this.mouseUp.object = this;
    	*/
    };
    $.fn.dragable.defaults = {
            handler : null,
            onMove: function() { },
            onDrop: function() { }
    };
})(jQuery);
/*
var DragAble = function (dragable,handler) {
	this.iDiffX = 0;
	this.iDiffY = 0;
	this.oDragable = dragable;
	this.oHandler = handler;
	
	this.mouseDown = function(){
		document.body.onselectstart = function () {return false;};
		document.body.style.userSelect = "none";
		document.body.style.MozUserSelect = "none";
		var object = arguments.callee.object;
		var event = arguments[0] || window.event;
		object.iDiffX = event.clientX - object.oDragable.offsetLeft;
		object.iDiffY = event.clientY - object.oDragable.offsetTop;
		document.body.onmousemove = object.mouseMove;
		document.body.onmouseup = object.mouseUp;
		object.oHandler.style.cursor = "move";
	};
	this.mouseDown.object = this;
	
	this.oHandler.onmousedown = this.mouseDown;
	
	this.mouseMove = function() {
		var object = arguments.callee.object;
		var event = arguments[0] || window.event;
		var wWidth = getWindowSize()[0];
		if ((event.clientX - object.iDiffX) >= 0 && (event.clientX - object.iDiffX + object.oDragable.clientWidth) <= wWidth - 20){
			object.oDragable.style.left = event.clientX - object.iDiffX + "px";
		}
		else {
			if ((event.clientX - object.iDiffX) < 0){
				object.oDragable.style.left = 0 + "px";
			}
			else {
				object.oDragable.style.left = wWidth - object.oDragable.clientWidth - 20 + "px";
			}
		}
		//obj.style.left = tempLeft + "px";
		if ((event.clientY - object.iDiffY) >= 0){
			object.oDragable.style.top = event.clientY - object.iDiffY + "px";
		}
		else {
			object.oDragable.style.top = 0 + "px";
		}
		
	};
	this.mouseMove.object = this;
	
	this.mouseUp = function() {
		var object = arguments.callee.object;
		document.body.onselectstart = "";
		document.body.style.userSelect = "";
		document.body.style.MozUserSelect = "";
		document.body.onmousemove = "";
		document.body.onmouseup = "";
		object.oHandler.style.cursor = "";
	};
	this.mouseUp.object = this;
};*/