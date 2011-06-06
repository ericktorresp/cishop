/**
*   Jquery插件之漂浮可移动弹出窗口,模拟了window警告,询问窗口,
*   依赖JQuery开发，作为其插件形式应用,Jquery要求在1.2.3以上版本
*/

;(function($){
//start closure

if (/1\.(0|1|2)\.(0|1|2)/.test($.fn.jquery) || /^1.1/.test($.fn.jquery)) {
    alert('requires jQuery v1.2.3 or later!  You are using v' + $.fn.jquery);
    return;
}

var blockUI_lang = {
	t1  : "温馨提示：",
	t2  : "提示：",
	t3  : "确定",
	t4  : "取消",
	t5  : "选择文件",
	t6  : "提交失败，请关闭本窗口再试",
	t7  : "完成",	
	t8  : "文件非法，允许文件格式为：",
	t9  : "载入文件",
	t10 : "载入",
	t11 : "正在载入...."
};

$.fn._fadeIn = $.fn.fadeIn;

var setExpr = (function() {
	if (!$.browser.msie) return false;
    var div = document.createElement('div');
    try { div.style.setExpression('width','0+0'); }
    catch(e) { return false; }
    return true;
})();
/*************************************************/
$.blockUI   = function(opts) { install(window, opts); };
$.unblockUI = function(opts) { remove(window, opts); };
$.fn.block  = function(opts) { // plugin method for blocking element content
    return this.unblock({ fadeOut: 0 }).each(function() {
        if ($.css(this,'position') == 'static'){
            this.style.position = 'relative';
        }
        if ($.browser.msie){
            this.style.zoom = 1; // force 'hasLayout'
        }
        install(this, opts);
    });
};
$.fn.unblock  = function(opts) {// plugin method for unblocking element content
    return this.each(function() {
        remove(this, opts);
    });
};

$.alert = function(opts){   //模拟警告窗口
    if( typeof(opts) == "string" ){
        opts = {message:opts};
    }
    opts  = $.extend( {}, $.alert.defaults, opts || {} ); //根据参数初始化默认配置
    html  = '<div id="james_dialogUI_alert" class="'+opts.alertCss+'"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="left" class="'+opts.titleCss+'" id="james_dialogUI_title" align="center">';
    html += '<div style="padding:0;padding-left:5px;font-size:13px;text-transform:capitalize;font-weight:bold;color:#FFF;float:left;height:20px;line-height:20px;">'+opts.title+'</div></td>';
    html += '<td style="width:20px;" align="right"><a href="javascript:" id="james_dialogUI_alert_close" class="'+opts.closeCss+'"><em style="display:none;">[X]</em></a></td></tr>';
    html += '<tr><td colspan="2"><div id="james_dialogUI_alert_content" class="'+opts.contentCss+'">'+opts.message+'</div></td></tr>';
    html += '<tr><td colspan="2"><input type="button" class="'+opts.buttonCss+'" id="james_dialogUI_alert_close1" value="确定"></td></tr></table></div>';
    $html = $(html);
    $.blockUI({
        message : $html,
        css: {width : opts.width, border: 0},
        overlayCSS:{backgroundColor: '#FFFFFF',opacity:0.6,cursor:'default'}
    });
    $("#james_dialogUI_alert_content").html("").css({width:$("#james_dialogUI_alert_content").width(),'overflow':'auto','word-break':'break-all'}).html(opts.message);
    limitHeight( $("#james_dialogUI_alert_content"),$(window).height()/2,'scroll' );
    fullCenter( $(".blockMsg") );
    $("#james_dialogUI_alert_close").click(function(){
        $.unblockUI({fadeOut:opts.fadeOut});
        opts.onclose();
    });
    $("#james_dialogUI_alert_close1").click(function(){
        $.unblockUI({fadeOut:opts.fadeOut});
        opts.onclose();
    });
    if( opts.keyCode && opts.keyCode != null ){
        $(document).keypress(function(e){
            if( e.keyCode==opts.keyCode ){
                $.unblockUI({fadeOut:opts.fadeOut});
                opts.onclose();
            }
        });
    }
    return false;
};
$.alert.defaults = {
    message  : null,   //警告内容
    width    : '300px', //警告框长度
    onclose  : function(){},    //关闭时执行的回调函数
    alertCss : 'james_dialogUI_alert',  //警告框外框样式
    titleCss : 'james_dialogUI_alert_title',    //标题样式
    closeCss : 'james_dialogUI_alert_close',    //关闭按钮样式
    contentCss: 'james_dialogUI_alert_content', //内容样式
    title   : blockUI_lang.t1,
    keyCode : 27,    //关闭窗口的快捷键 
    fadeOut : 300   //关闭窗口时动态效果
    
};

$.confirm = function(opts){//模拟询问窗口
    //var args  = arguments;
    //var isclick = false;    //是否点击了是或者否
    //var isyes   = false;     //选择的是或者否
    //if( args.length < 3 ){
    //    return false;
    //}
    //var opts  = $.confirm.defaults; //根据参数初始化默认配置
    opts  = $.extend( {}, $.confirm.defaults, opts || {} ); //根据参数初始化默认配置
    var times;
    // louis
    var temp = opts.message.split("#");
    var seconds = "";
	if (temp[1] == 1){
		var secs = 3;
		var wait = secs * 1000;
		for(i = 1; i <= secs; i++) {
			window.setTimeout("$.confirm.update(" + i + ")", i * 1000);
		}
		window.setTimeout("$.confirm.timer()", wait);
		times = window.setTimeout("$.confirm.change()");
		seconds = '('+secs+')';
		var test = "disabled"; 
	}
	// louis
    //opts.message = args[0];
    html  = '<div id="james_dialogUI_confirm" class="'+opts.confirmCSS+'"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="left" id="james_dialogUI_confirm_title" class="'+opts.titleCss+'" align="center">';
    html += '<div style="padding:0;padding-left:5px;font-size:13px;text-transform:capitalize;font-weight:bold;color:#FFF;float:left;height:20px;line-height:20px;">'+opts.title+'</div></td>';
    html += '<td style="width:20px;" align="right"><a href="javascript:" id="james_dialogUI_confirm_close" class="'+opts.closeCss+'"><em style="display:none;">[X]</em></a></td></tr>';
    html += '<tr><td colspan="2"><div id="james_dialogUI_confirm_content" class="'+opts.contentCss+'"><p style="margin:5px;padding:5px;">'+temp[0]+'</p><center><button class="'+opts.buttonCss+'" id="james_dialogUI_confirm_yes" '+test+' >'+opts.textyes+seconds+'</button>　　　<button id="james_dialogUI_confirm_no" class="'+opts.buttonCss+'">'+opts.textno+'</button></center></div></td></tr></table></div>';
    $html = $(html);
    $.blockUI({
        message : $html,
        css: {width : opts.width,'max-width':'800px',border: 0},
        overlayCSS:{backgroundColor: '#FFFFFF',opacity:0.6,cursor:'default'}
    });
    limitHeight( $("#james_dialogUI_confirm_content").children("p"),$(window).height()/2,'scroll' );
    fullCenter( $(".blockMsg") );
    $("#james_dialogUI_confirm_close").click(function(){
        $.unblockUI({fadeOut:0});
        //isclick = true;
        opts.funno();
        //eval(args[2]);
    });
    $("#james_dialogUI_confirm_yes").focus().click(function(){
        $.unblockUI({fadeOut:0});
        opts.funyes();
        //isclick = true;
        //isyes   = true;
        //eval(args[1]);
    });
    $("#james_dialogUI_confirm_no").click(function(){
        $.unblockUI({fadeOut:0});
        clearTimeout(times);
        //isclick = true;
        opts.funno();
        //eval(args[2]);
    });
    $(document).keypress(function(e){
        if( e.keyCode==27 ){
            $.unblockUI({fadeOut:0});
            //isclick = true;
            opts.funno();
            //eval(args[2]);
        }
    });
    $("#james_dialogUI_confirm").jamesDrag({handler: '#james_dialogUI_confirm_title'});
    //return isyes;
    // louis
	$.confirm.update = function(num, value) {
		  var secs = 3;
		  var wait = secs * 1000;
		  $("#james_dialogUI_confirm_yes").attr("disabled",true); 
	      if(num == (wait/1000)) {
	              $("#james_dialogUI_confirm_yes").html(opts.textyes);
	      } else {
	          printnr = (wait / 1000)-num;
	          $("#james_dialogUI_confirm_yes").html(opts.textyes + "(" + printnr + ")");
	      }
	}
	$.confirm.timer = function(){
		$("#james_dialogUI_confirm_yes").attr("disabled",false); 
	    $("#james_dialogUI_confirm_yes").val(opts.textyes);
	}
	var node  = document.getElementById("notice_color");//#33FF33
	$.confirm.change = function(){
		if (node != null){
			str = node.style.color.toLowerCase();
		  	node.style.color = str == "#ff1100" || str == "rgb(255, 17, 0)" ? "#000000" : "#FF1100"; 
		  	times = window.setTimeout("$.confirm.change()",1000);
		}
	}
	// louis
}

$.confirm.defaults = {
    message    : null,     //询问内容
    title      : blockUI_lang.t2,
    width      : '300px',
    funyes     : function(){},  //点击YES调用的回调函数
    funno      : function(){},  //点击NO调用的回调函数
    confirmCSS : 'james_dialogUI_confirm',             //外框样式
    titleCss   : 'james_dialogUI_confirm_title',       //标题样式
    closeCss   : 'james_dialogUI_confirm_close',       //关闭按钮样式
    textyes    : blockUI_lang.t3,
    textno     : blockUI_lang.t4,
    buttonCss  : 'james_dialogUI_confirm_button',       
    contentCss : 'james_dialogUI_confirm_content'        //内容框样式
    
};

$.ajaxUploadUI = function(opts){    //文件上传/载入文件
    opts  = $.extend( {}, $.ajaxUploadUI.defaults, opts || {} ); //根据参数初始化默认配置
    opts.keyCode = null;
    if( opts.message && opts.message!=null ){
        opts.message += '<br />';
    }else{
        opts.message = '';
    }
    opts.fadeOut = 0;
    opts.message = '<form action="'+opts.url+'" method="POST" name="james_uploadUI" target="james_uploadUI_iframe" id="james_uploadUI" enctype="multipart/form-data" style="margin:0;padding:5px 0 10px 0;">'+opts.message+'<input type="file" size="20" name="'+opts.inputfile+'" id="james_uploadUI_file" value="'+blockUI_lang.t5+'">  <button type="button" id="james_uploadUI_submit" class="'+opts.button.css+'" disabled>'+opts.button.text+'</button></form><span style="display:none;text-align:center;" id="james_uploadUI_loading"><center>'+opts.loadtext+'<br /><img src="../dilogUI/'+opts.loadimg+'" align="absmiddle"></center></span><span id="james_uploadUI_error" style="color:red;"></span><iframe name="james_uploadUI_iframe" id="james_uploadUI_iframe" style="width:0px; height:0px;display:none;"></iframe>';
    $.alert(opts);
    //$("#james_dialogUI_alert").Drags({handler: '#james_dialogUI_title'});
    $("#james_uploadUI_file").change(function(){
        if( $("#james_uploadUI_file").val() != "" ){
            $("#james_uploadUI_submit").attr("disabled",false);
        }else{
            $("#james_uploadUI_submit").attr("disabled",true);
        }
    });
    /*****************************************AJAX已步提交***************************/
    s = $.extend({}, $.ajaxSettings, opts);   //AJAX设置
    var xml = {};   //请求返回的数据
    var requestDone = false;    //是否完成请求
    function uploadCallBack(isTimeout){//请求完成时的回调函数
        var iframe = document.getElementById("james_uploadUI_iframe");
        try{//获取返回数据
			if(iframe.contentWindow){
				 xml.responseText = iframe.contentWindow.document.body ? iframe.contentWindow.document.body.innerHTML : null;
            	 xml.responseXML = iframe.contentWindow.document.XMLDocument ? iframe.contentWindow.document.XMLDocument : iframe.contentWindow.document;
			}else if(iframe.contentDocument){
				 xml.responseText = iframe.contentDocument.document.body ? iframe.contentDocument.document.body.innerHTML : null;
            	 xml.responseXML = iframe.contentDocument.document.XMLDocument ? iframe.contentDocument.document.XMLDocument : iframe.contentDocument.document;
			}
        }catch(e){
            uploadError(blockUI_lang.t6);
			$.handleError(s, xml, null, e);
		}
		if( xml || isTimeout == "timeout"){	//如果返回数据或者请求超时
            requestDone = true;
            var status;
            try{
                status = isTimeout != "timeout" ? "success" : "error";
                // Make sure that the request was successful or notmodified
                if( status != "error" ){// process the data (runs the xml through httpData regardless of callback)
                    var data = uploadHttpData( xml, s.dataType );
                    $("#james_uploadUI_loading").hide();
                    $("#james_uploadUI_error").empty();
                    $("<center><button id='james_uploadUI_finish' class='"+opts.button.css+"'>"+blockUI_lang.t7+"</button></center>").appendTo("#james_uploadUI_error");
                    $("#james_uploadUI_finish").click(function(){
                        $.unblockUI({fadeOut:opts.fadeOut});
                    });
                    if( s.success ){   // If a local callback was specified, fire it and pass it the data
                        s.success( data, status );
                    }
                    if( s.global ){ // Fire the global callback
                        $.event.trigger( "ajaxSuccess", [xml, s] );
                    }
                }else{
                    uploadError(blockUI_lang.t6);
                    $.handleError(s, xml, status);
                }
            }catch(e){
                uploadError(blockUI_lang.t6);
                status = "error";
                $.handleError(s, xml, status, e);
            }
            if( s.global ){ // The request was completed
                $.event.trigger( "ajaxComplete", [xml, s] );
            }
            if( s.global && ! --$.active ){ // Handle the global AJAX counter
                $.event.trigger( "ajaxStop" );
            }
            if( s.complete ){
                s.complete(xml, status);
            }
            $(iframe).unbind();
            xml = null;
        }
    };
    function uploadHttpData( xml, type ){ //AJAX数据格式
        var data = !type;
        data = (type == "xml" || data) ? xml.responseXML : xml.responseText;
        if( type == "script" ){    // If the type is "script", eval it in global context
            $.globalEval( data );
        }
        if( type == "json" ){  // Get the JavaScript object, if JSON is used.
            eval( "data = " + data );
        }
        if( type == "html" ){  // evaluate scripts within html
            //$("<div>").html(data);//.evalScripts();
        }
	    //alert($('param', data).each(function(){alert($(this).attr('value'));}));
        return data;
    }
    function showError(msg){    //显示错误提示
        $("#james_uploadUI_error").html(msg).fadeIn(0);
        setTimeout(function(){$("#james_uploadUI_error").fadeOut(400);},2000);
    }
    function uploadStart(){//开始上传文件前的处理内容
        requestDone = false;
        $("#james_uploadUI").hide();
        $("#james_uploadUI_loading").show();
        if( s.global && !$.active++ ){//调用全局AJAXSTAR函数
			$.event.trigger( "ajaxStart" );
		}
    }
    function uploadError(msg){  //上传文件中出错后的处理
        $("#james_uploadUI_loading").hide();
        $("#james_uploadUI").show();
        showError(msg);
    }
    /*******************************************************************************/
    $("#james_uploadUI_submit").click(function(){
        filepath = $("#james_uploadUI_file").val();
        if( filepath != "" && filepath != null  ){//选择了文件
            //检验文件是否允许上传
            filetype = filepath.substr(filepath.lastIndexOf(".")+1).toLowerCase();
            if( $.inArray(filetype,opts.filetype) == -1  ){
                showError(filetype+blockUI_lang.t8+opts.filetype.join(","));
                return false;
            }
            uploadStart();
            if( s.timeout > 0 ){
                setTimeout(function(){// Check to see if the request is still happening
                    if( !requestDone ){ uploadCallBack( "timeout" ); }
                }, s.timeout);
            }
            try{
    			var form = $('#james_uploadUI');
    			$(form).attr('method', 'POST');
                if(form.encoding){
                    form.encoding = 'multipart/form-data';				
                }else{				
                    form.enctype = 'multipart/form-data';
                }			
                $(form).submit();
    
            }catch(e){
                uploadError(blockUI_lang.t6);
                status = 'error';
                $.handleError(s, xml, status, e);
            }
            if(window.attachEvent){
                document.getElementById("james_uploadUI_iframe").attachEvent('onload', uploadCallBack);    
            }else{
                document.getElementById("james_uploadUI_iframe").addEventListener('load', uploadCallBack, false);
            }
            return {abort: function () {}};	
        }
    });
};
$.ajaxUploadUI.defaults ={  //文件上传默认设置
    url       : '',    //处理上传文件的URL
    dataType  : 'html', //回传数据格式
    filetype  : ['txt','csv','gif','jpg','png'],  //允许上传的文件格式
    inputfile : 'james_uploadUI_file',//上传表单中文件上传元素的name
    success   : function(data){}, //上传成功后的回调函数
    error     : function(data){}, //失败时的回调函数
    timeout   : 2400000,    //超时默认时间，40分钟，设置为0时则不做超时检查
    message   : null,   //上传说明
    alertCss  : 'james_dialogUI_upload',  //文件上传框外框样式
    titleCss  : 'james_dialogUI_upload_title',    //标题样式
    closeCss  : 'james_dialogUI_upload_close',    //关闭按钮样式
    contentCss: 'james_dialogUI_upload_content', //内容样式
    title     : blockUI_lang.t9, //标题
    width     : '350px',    //弹出框宽度
    button    :{
                css: '',//提交按钮样式
                text:blockUI_lang.t10//按钮文字
                },
    loadimg   : 'lib/loading.gif',   //上传文件中等待图片
    loadtext  : blockUI_lang.t11       //上传文件中等待文字
};
/********************************************************************************************************************/

$.blockUI.defaults = {
    // message displayed when blocking (use null for no message)
    message:  '<h1>Please wait...</h1>',

    // styles for the message when blocking; if you wish to disable
    // these and use an external stylesheet then do this in your code:
    // $.blockUI.defaults.css = {};
    css: {
        padding:        0,
        margin:         0,
        width:          '30%',
        top:            '40%',
        left:           '35%',
        textAlign:      'center',
        color:          '#000',
        border:         '1px #000000 solid',
        backgroundColor:'#FFFFFF'
    },

    // styles for the overlay
    overlayCSS:  {
        backgroundColor: '#509f52',
        opacity:          0.3,
        cursor:          'default'
    },
	
	// IE issues: 'about:blank' fails on HTTPS and javascript:false is s-l-o-w
	// (hat tip to Jorge H. N. de Vasconcelos)
	iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank',

	// force usage of iframe in non-IE browsers (handy for blocking applets)
	forceIframe: false,

    // z-index for the blocking overlay
    baseZ: 2000,

    // set these to true to have the message automatically centered
    centerX: true, // <-- only effects element blocking (page block controlled via css above)
    centerY: true,

    // allow body element to be stetched in ie6; this makes blocking look better
    // on "short" pages.  disable if you wish to prevent changes to the body height
    allowBodyStretch: true,

	// enable if you want key and mouse events to be disabled for content that is blocked
	bindEvents: true,

    // be default blockUI will supress tab navigation from leaving blocking content
    // (if bindEvents is true)
    constrainTabKey: true,

    // fadeIn time in millis; set to 0 to disable fadeIn on block
    fadeIn:  200,

    // fadeOut time in millis; set to 0 to disable fadeOut on unblock
    fadeOut:  400,

	// time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock
	timeout: 0,

	// disable if you don't want to show the overlay
	showOverlay: true,

    // if true, focus will be placed in the first available input field when
    // page blocking
    focusInput: true,

    // suppresses the use of overlay styles on FF/Linux (due to performance issues with opacity)
    applyPlatformOpacityRules: true,

    // callback method invoked when unblocking has completed; the callback is
    // passed the element that has been unblocked (which is the window object for page
    // blocks) and the options that were passed to the unblock call:
    //     onUnblock(element, options)
    onUnblock: null,

    // don't ask; if you really must know: http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493
    quirksmodeOffsetHack: 4
};

/****************************公用函数调用************************************/
$.blockUI.version = '1.0.0';

var ie6 = $.browser.msie && /MSIE 6.0/.test(navigator.userAgent);
var pageBlock = null;
var pageBlockEls = [];

function install(el, opts) {
    var full = (el == window);
    var msg  = opts && opts.message !== undefined ? opts.message : undefined;
    opts     = $.extend({}, $.blockUI.defaults, opts || {});
    opts.overlayCSS = $.extend({}, $.blockUI.defaults.overlayCSS, opts.overlayCSS || {});
    var css = $.extend({}, $.blockUI.defaults.css, opts.css || {});
    msg = msg === undefined ? opts.message : msg;

    // remove the current block (if there is one)
    if(full && pageBlock){
        remove(window, {fadeOut:0});
    }
    // if an existing element is being used as the blocking content then we capture
    // its current place in the DOM (and current display style) so we can restore
    // it when we unblock
    if (msg && typeof msg != 'string' && (msg.parentNode || msg.jquery)) {
        var node = msg.jquery ? msg[0] : msg;
        var data = {};
        $(el).data('blockUI.history', data);
        data.el = node;
        data.parent = node.parentNode;
        data.display = node.style.display;
        data.position = node.style.position;
		if (data.parent)
			data.parent.removeChild(node);
    }

    var z = opts.baseZ;

    // blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
    // layer1 is the iframe layer which is used to supress bleed through of underlying content
    // layer2 is the overlay layer which has opacity and a wait cursor (by default)
    // layer3 is the message content that is displayed while blocking

    var lyr1 = ($.browser.msie || opts.forceIframe) 
    	? $('<iframe class="blockUI" style="z-index:'+ (z++) +';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="'+opts.iframeSrc+'"></iframe>')
        : $('<div class="blockUI" style="display:none"></div>');    //压制根本性内容
    var lyr2 = $('<div class="blockUI blockOverlay" style="z-index:'+ (z++) +';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>'); //遮照层，等待反应层
    var lyr3 = full ? $('<div class="blockUI blockMsg blockPage" style="z-index:'+z+';display:none;position:fixed"></div>')
                    : $('<div class="blockUI blockMsg blockElement" style="z-index:'+z+';display:none;position:absolute"></div>');//弹出窗口层

    // if we have a message, style it
    if( msg ){
        lyr3.css(css);
    }

    // style the overlay
    if( !opts.applyPlatformOpacityRules || !($.browser.mozilla && /Linux/.test(navigator.platform)) ){
        lyr2.css(opts.overlayCSS);
    }
    lyr2.css('position', full ? 'fixed' : 'absolute');

    // make iframe layer transparent in IE
    if ($.browser.msie || opts.forceIframe)
        lyr1.css('opacity',0.0);

    $([lyr1[0],lyr2[0],lyr3[0]]).appendTo(full ? 'body' : el);

    // ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
    var expr = $.browser.msie && ($.browser.version < 8 || !$.boxModel) && (!$.boxModel || $('object,embed', full ? null : el).length > 0);
    if( ie6 || (expr && setExpr) ){
        // give body 100% height
        if (full && opts.allowBodyStretch && $.boxModel)
            $('html,body').css('height','100%');
        // fix ie6 issue when blocked element has a border width
        if( (ie6 || !$.boxModel) && !full ){
            var t = sz(el,'borderTopWidth'), l = sz(el,'borderLeftWidth');
            var fixT = t ? '(0 - '+t+')' : 0;
            var fixL = l ? '(0 - '+l+')' : 0;
        }

        // simulate fixed position
        $.each([lyr1,lyr2,lyr3], function(i,o){
            var s = o[0].style;
            s.position = 'absolute';
            if (i < 2) {
                full ? s.setExpression('height','Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.boxModel?0:'+opts.quirksmodeOffsetHack+') + "px"')
                     : s.setExpression('height','this.parentNode.offsetHeight + "px"');
                full ? s.setExpression('width','jQuery.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"')
                     : s.setExpression('width','this.parentNode.offsetWidth + "px"');
                if (fixL) s.setExpression('left', fixL);
                if (fixT) s.setExpression('top', fixT);
            }
            else if (opts.centerY) {
                if (full) s.setExpression('top','(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"');
                s.marginTop = 0;
            }
			else if (!opts.centerY && full) {
				var top = (opts.css && opts.css.top) ? parseInt(opts.css.top) : 0;
				var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + '+top+') + "px"';
                s.setExpression('top',expression);
			}
        });
    }

    // show the message
	if (msg) {
		lyr3.append(msg);
		if (msg.jquery || msg.nodeType){
			$(msg).show();
		}
	}

	if( ($.browser.msie || opts.forceIframe) && opts.showOverlay ){
		lyr1.show(); // opacity is zero
	}
	if(opts.fadeIn){
		if(opts.showOverlay){
			lyr2._fadeIn(opts.fadeIn);
		}
		if(msg){
			lyr3.fadeIn(opts.fadeIn);
		}
	}
	else {
		if(opts.showOverlay){
			lyr2.show();
		}
		if(msg){
			lyr3.show();
		}
	}

    // bind key and mouse events
    bind(1, el, opts);

    if(full){
        pageBlock = lyr3[0];
        pageBlockEls = $(':input:enabled:visible',pageBlock);
        if(opts.focusInput){
            setTimeout(focus, 20);
        }
    }else{
        center(lyr3[0], opts.centerX, opts.centerY);
    }

	if (opts.timeout) {
		// auto-unblock
		var to = setTimeout(function() {
			full ? $.unblockUI(opts) : $(el).unblock(opts);
		}, opts.timeout);
		$(el).data('blockUI.timeout', to);
	}
};

// remove the block
function remove(el, opts) {
    var full = el == window;
	var $el = $(el);
    var data = $el.data('blockUI.history');
	var to = $el.data('blockUI.timeout');
	if (to) {
		clearTimeout(to);
		$el.removeData('blockUI.timeout');
	}
    opts = $.extend({}, $.blockUI.defaults, opts || {});
    bind(0, el, opts); // unbind events
    var els = full ? $('body').children().filter('.blockUI') : $('.blockUI', el);

    if (full)
        pageBlock = pageBlockEls = null;

    if (opts.fadeOut) {
        els.fadeOut(opts.fadeOut);
        setTimeout(function() { reset(els,data,opts,el); }, opts.fadeOut);
    }
    else
        reset(els, data, opts, el);
};

// move blocking element back into the DOM where it started
function reset(els,data,opts,el) {
    els.each(function(i,o) {
        // remove via DOM calls so we don't lose event handlers
        if (this.parentNode)
            this.parentNode.removeChild(this);
    });

    if (data && data.el) {
        data.el.style.display = data.display;
        data.el.style.position = data.position;
		if (data.parent)
			data.parent.appendChild(data.el);
        $(data.el).removeData('blockUI.history');
    }

    if (typeof opts.onUnblock == 'function')
        opts.onUnblock(el,opts);
};

// bind/unbind the handler
function bind(b, el, opts) {
    var full = el == window, $el = $(el);

    // don't bother unbinding if there is nothing to unbind
    if (!b && (full && !pageBlock || !full && !$el.data('blockUI.isBlocked')))
        return;
    if (!full)
        $el.data('blockUI.isBlocked', b);

	// don't bind events when overlay is not in use or if bindEvents is false
    if (!opts.bindEvents || (b && !opts.showOverlay)) 
		return;

    // bind anchors and inputs for mouse and key events
    var events = 'mousedown mouseup keydown keypress';
    b ? $(document).bind(events, opts, handler) : $(document).unbind(events, handler);

// former impl...
//    var $e = $('a,:input');
//    b ? $e.bind(events, opts, handler) : $e.unbind(events, handler);
};

// event handler to suppress keyboard/mouse events when blocking
function handler(e) {
    // allow tab navigation (conditionally)
    if (e.keyCode && e.keyCode == 9) {
        if (pageBlock && e.data.constrainTabKey) {
            var els = pageBlockEls;
            var fwd = !e.shiftKey && e.target == els[els.length-1];
            var back = e.shiftKey && e.target == els[0];
            if (fwd || back) {
                setTimeout(function(){focus(back)},10);
                return false;
            }
        }
    }
    // allow events within the message content
    if ($(e.target).parents('div.blockMsg').length > 0)
        return true;

    // allow events for content that is not being blocked
    return $(e.target).parents().children().filter('div.blockUI').length == 0;
};

function focus(back) {
    if (!pageBlockEls)
        return;
    var e = pageBlockEls[back===true ? pageBlockEls.length-1 : 0];
    if (e)
        e.focus();
};

function center(el, x, y) {
    var p = el.parentNode, s = el.style;
    var l = ((p.offsetWidth - el.offsetWidth)/2) - sz(p,'borderLeftWidth');
    var t = ((p.offsetHeight - el.offsetHeight)/2) - sz(p,'borderTopWidth');
    if (x) s.left = l > 0 ? (l+'px') : '0';
    if (y) s.top  = t > 0 ? (t+'px') : '0';
};

function sz(el, p) {
    return parseInt($.css(el,p))||0;
};

function fullCenter(el){//使元素在屏幕中央
    var topset  = $(window).height()/2-$(el).height()/2+ (ie6 ? document.documentElement.scrollTop : 0);
    var leftset = $(window).width()/2 - $(el).width()/2+ (ie6 ? document.documentElement.scrollLeft : 0);
    $(el).css({left:leftset+"px",top:topset+"px"});
}
function limitHeight(el,h,type){ //规定元素最高高度，超过则按指定样式显示
    if($.inArray(type,['auto','hidden','inherit','scroll','visible']) == -1){
        type = 'auto';
    }
    if( $(el).height() > h ){
        $(el).css({height:h,'overflow':type,'overflow-x':'hidden'});
    }
}

//end closure
})(jQuery);