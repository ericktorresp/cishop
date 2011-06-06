/**
*   Jquery插件之漂浮可移动窗口
*   依赖JQuery开发，作为其插件形式应用,Jquery要求在1.2.3以上版本
*
*
*
*/
;(function($){
//start closure
	
if (/1\.(0|1|2)\.(0|1|2)/.test($.fn.jquery) || /^1.1/.test($.fn.jquery)) {
    alert('requires jQuery v1.2.3 or later!  You are using v' + $.fn.jquery);
    return;
}

var setExpr = (function() {
	if (!$.browser.msie) return false;
    var div = document.createElement('div');
    try { div.style.setExpression('width','0+0'); }
    catch(e) { return false; }
    return true;
})();

$.windowUI = function(opts){ installWindow(window,opts); };
$.unwindowUI = function(opts){ removeWindow(window,opts); };
$.alert = function(opts){   //模拟警告窗口
    if( typeof(opts) == "string" ){
        opts = {message:opts};
    }
    opts  = $.extend( {}, $.alert.defaults, opts || {} ); //根据参数初始化默认配置
    html  = '<div id="james_windowUI_alert" class="'+opts.alertCss+'"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="left" class="'+opts.titleCss+'" align="center">';
    html += '<div style="padding:0;padding-left:5px;font-size:13px;text-transform:capitalize;font-weight:bold;color:#FFF;float:left;height:20px;line-height:20px;">'+opts.title+'</div>';
    html += '<a href="#" id="james_windowUI_alert_close" class="'+opts.closeCss+'"><em style="display:none;">[X]</em></a></td></tr>';
    html += '<tr><td><div id="james_windowUI_alert_content" class="'+opts.contentCss+'">'+opts.message+'</div></td></tr></table></div>';
    $html = $(html);
    $.windowUI({
        message : $html,
        mainCss: {width : '300px','max-width':'400px'},
        overlayCSS:{backgroundColor: '#FFFFFF',opacity:0.0,cursor:'default'}
    });
    $("#james_windowUI_alert_close").click(function(){
        $.unwindowUI();
    });
    $(document).keypress(function(e){
        if( e.keyCode==27 ){
            $.unwindowUI();
        }
    });
    return false;
};
$.alert.defaults = {
    message : null,   //警告内容
    alertCss : 'james_windowUI_alert',  //警告框外框样式
    titleCss : 'james_windowUI_alert_title',    //标题样式
    closeCss : 'james_windowUI_alert_close',    //关闭按钮样式
    contentCss: 'james_windowUI_alert_content', //内容样式
    title   : '温馨提示：'
    
};

$.confirm = function(msg,callback_yes,callback_no,text_yes,text_no){//模拟询问窗口
    var args  = arguments;
    if( args.length < 3 ){
        return false;
    }
    var opts  = $.confirm.defaults; //根据参数初始化默认配置
    opts.message = args[0];
    opts.button.textyes = args[3] ? args[3] : opts.button.textyes;
    opts.button.textno  = args[4] ? args[4] : opts.button.textno;
    html  = '<div id="james_windowUI_confirm" class="'+opts.confirmCSS+'"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="left" id="james_windowUI_confirm_title" class="'+opts.titleCss+'" align="center">';
    html += '<div style="padding:0;padding-left:5px;font-size:13px;text-transform:capitalize;font-weight:bold;color:#FFF;float:left;height:20px;line-height:20px;">'+opts.title+'</div>';
    html += '<a href="#" id="james_windowUI_confirm_close" class="'+opts.closeCss+'"><em style="display:none;">[X]</em></a></td></tr>';
    html += '<tr><td><div id="james_windowUI_confirm_content" class="'+opts.contentCss+'"><p style="margin:0px;padding:5px;">'+opts.message+'</p><center><button class="'+opts.button.css+'" id="james_windowUI_confirm_yes">'+opts.button.textyes+'</button>&nbsp;<button id="james_windowUI_confirm_no" class="'+opts.button.css+'">'+opts.button.textno+'</button></center></div></td></tr></table></div>';
    $html = $(html);
    $.windowUI({
        message : $html,
        mainCss: {width : '300px','max-width':'400px'},
        overlayCSS:{backgroundColor: '#FFFFFF',opacity:0.0,cursor:'default'}
    });
    $("#james_windowUI_confirm_close").click(function(){
        $.unwindowUI();
        eval(args[2]);
    });
    $("#james_windowUI_confirm_yes").focus().click(function(){
        $.unwindowUI();
        eval(args[1]);
    });
    $("#james_windowUI_confirm_no").click(function(){
        $.unwindowUI();
        eval(args[2]);
    });
    $(document).keypress(function(e){
        if( e.keyCode==27 ){
            $.unwindowUI();
            eval(args[2]);
        }
    });
    $("#james_windowUI_confirm").Drags({handler: '#james_windowUI_confirm_title'});
}
$.confirm.defaults = {
    message    : null,     //询问内容
    title      : '提示：',
    confirmCSS : 'james_windowUI_confirm',             //外框样式
    titleCss   : 'james_windowUI_confirm_title',       //标题样式
    closeCss   : 'james_windowUI_confirm_close',       //关闭按钮样式
    button  : {  //是否按钮样式
                 textyes : '确定',
                 textno  : '取消',
                 css     : 'james_windowUI_confirm_button'
                },       
    contentCss : 'james_windowUI_confirm_content'        //内容框样式
    
};

$.windowUI.defaults = {//插件默认设置
    //弹出窗口显示的消息,如果没有则为NULL,可以为JQUERY对象
    message : null,
    mainCss : {
             width : '300px',
             padding: 0,
             margin:  0,
             top: '40%',
             left: '35%',
             textAlign: 'center',
             border: '1px #000000 solid',
             backgroundColor : '#FFFFFF'
            },
    baseZindex : 2000,   //初始层级
    
    overlayCSS:  {
        backgroundColor: '#509f52',
        opacity:          0.3,
        cursor:          'default'
    },
    forceIframe: false,
    iframeSrc : /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank',
    showOverly: true,   //是否显示遮罩层
    centerX   : true,   //弹出框是否横向居中
    centerY   : true,   //弹出框是否纵向居中
    fadeIn    : 400,    //弹出窗口弹出动画效果
    fadeOut   : 400,    //弹出窗口关闭动画效果
    test: ''
    
}

/****************************************私有属性和函数***********************************************/
var ie6 = $.browser.msie && /MSIE 6.0/.test(navigator.userAgent);
pageBlock = null;   //页面是否被锁定
var pageBlockEls = [];  //页面被锁定的元素集合,用于多窗口显示

function installWindow(el,opts){ //生成弹出窗口
    var full = ( el == window );
    var msg = opts && opts.message !== undefined ? opts.message : undefined;
    opts = $.extend( {}, $.windowUI.defaults, opts || {} ); //根据参数初始化默认配置
    msg = msg === undefined ? opts.message : msg;
    
    if( full && pageBlock ){//如果是全页面弹出,则先解除前面已经存在的锁定
        removeWindow(window,{});
    }
    
    //生成DIV,总共用三个层来完成显示效果,兼容多平台
    var z = opts.baseZindex;
    var ly_frame  = ($.browser.msie || opts.forceIframe) ? 
                    $('<iframe id="james_windowUI" style="z-index:'+ (z++) +';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="'+opts.iframeSrc+'"></iframe>')
        : $('<div id="james_windowUI" style="display:none"></div>');
    var ly_buttom = $('<div id="james_windowUI_back" style="z-index:'+(z++)+';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0;position:absolute;"></div>');
    var ly_top    = $('<div id="james_windowUI_mainbox" style="z-index:'+ z +';display:none;position:absolute"></div>');
    
    if( $.browser.msie || opts.forceIframe ){
        ly_frame.css('opacity',0.0);
    }
    ly_buttom.css(opts.overlayCSS);//背景样式
    ly_buttom.css( 'position', 'absolute' );
    if( msg ){
        ly_top.css(opts.mainCss);//浮动框样式
        ly_top.css("position", full ? "fixed" : "absolute");
    }
    
    $([ly_frame[0],ly_buttom[0],ly_top[0]]).appendTo(full ? 'body' : el);
    var expr = $.browser.msie && ($.browser.version < 8 || !$.support.boxModel) && (!$.support.boxModel || $('object,embed', full ? null : el).length > 0);
    
    //调整
    if( full && $.support.boxModel  ){//调整页面高度
        $('html,body').css('height','100%');
    }
    if( msg ){
        ly_top.append(msg);
    }
    $.each([ly_frame,ly_buttom,ly_top],function(i,o){
        if( i < 2 ){//遮罩层
            o.height( full ? $(document).height() : $(el).height() );
            o.width ( full ? $(document).width() : $(el).width() );
        }else
        {
            if( opts.centerX ){//横向居中
                o.css("left", full ? ($(document).width()/2-o.width()/2)+"px" : ($(el).width()/2-o.width()/2)+"px" );
            }
            if( opts.centerY ){//纵向居中
                o.css("top", full ? ($(document).height()/2-o.height()/2)+"px" : ($(el).height()/2-o.height()/2)+"px" );
            }
        }
    });
    
    if( ($.browser.msie || opts.forceIframe) && opts.showOverly ){
        $("#james_windowUI").show();
    }  
    if( opts.showOverly ){
        $("#james_windowUI_back").show();
    }
    if( msg && opts.showOverly ){
        if( opts.fadeIn ){
            $("#james_windowUI_mainbox").fadeIn(opts.fadeIn);
        }else{
            $("#james_windowUI_mainbox").show();
        }
    }
    
    if( full ){
        pageBlock = ly_top[0];
    }
    
}

function removeWindow(el,opts){//关闭弹出窗口
    opts = $.extend( {}, $.windowUI.defaults, opts || {} ); //根据参数初始化默认配置
    var full = ( el == window );
    box = full ? $('body').children().filter("[id='james_windowUI_mainbox']") : $("[id='james_windowUI_mainbox']", el);
    if( opts.fadeOut ){
        $.each(box, function(i,n){
            $(n).fadeOut(opts.fadeOut);
        });
    }
    els = full ? $('body').children().filter("[id^='james_windowUI']") : $("[id^='james_windowUI']", el);
    $.each(els,function(i,o){
        if (this.parentNode){
            this.parentNode.removeChild(this);
        }
    });
}



})(jQuery);