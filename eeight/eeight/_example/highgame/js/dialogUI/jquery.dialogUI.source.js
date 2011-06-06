/*
* dialogUI[copy windows warning window,and confirm window]
*
* version: 1.0.0 (01/21/2010)
* @ jQuery v1.3 or later ,suggest use 1.4
*
* Copyright 2010 James [ jameskerr2009[at]gmail.com ] 
*  
*/
;(function($){
    //check the version, need 1.3 or later , suggest use 1.4
    if (/^1.2/.test($.fn.jquery) || /^1.1/.test($.fn.jquery)) {
    	alert('requires jQuery v1.3 or later!  You are using v' + $.fn.jquery);
    	return;
    }
    
    $.blockUI_lang = {
            button_sure   : "确定",
            button_cancel : "取消",
            button_upload : "载入文件",
            button_uploading : "正在载入....",
            button_uploadend : "完成",
            title_warn : "温馨提示",
            title_confirm : "温馨提示",
            title_upload  : "Ajax Upload",
            desc_updefaultmsg : "请选择你要载入的文件",
            desc_uploaderror  : "文件格式错误，只支持[%str%]类型的文件",
            desc_uploadingerror : "载入文件失败,请重试",
            img_dir : '../dialogUI/' //该JS里自带的图片文件的路径
    };
    
    //define the dialog function
    $.blockUI = function(opts){//block whole document
        blockUI_install(window,opts);
    };
    $.unblockUI = function(opts){//unlock whole docuemnt
        blockUI_remove(window,opts);
    };
    $.fn.block = function(opts){//block the elements
        return this.unblock({fadeOut: 0}).each(function(){
    		if ($.css(this,'position') == 'static'){
    			this.style.position = 'relative';
    		}
    		if ($.browser.msie){
    			this.style.zoom = 1; // force 'hasLayout'
    		}
    		blockUI_install(this, opts);
    	});
    };
    $.fn.unblock = function(opts){//unblock the elements
        return this.each(function(){
    		blockUI_remove(this, opts);
    	});
    };
    $.alert = function( msg, title, onclose, width ){//simulate the window`s alert
        title = (title && title.length > 1) ? title : $.blockUI_lang.title_warn; //title
        msg   = msg ? msg : undefined;     //warning content
        onclose = (onclose && typeof(onclose) == 'function') ? onclose : function(){}; //callback function when the dialog close
        width = parseInt(width,10);
        width = isNaN(width) ? 0 : width;//the width about alert box
        if( msg == undefined ){
            return;
        }
        if( typeof(msg) == 'string' ){
            msg = msg.replace(/\n/g,"<br />").replace(/\r/g,"<br />");
        }
        var html = blockUI_frame_insert({
                        cl_box:'block_alert',
                        width : width,
                        isbottom:true,
                        title:title,
                        msg:msg,
                        bt_text:'<input type="button" value="'+$.blockUI_lang.button_sure+'" id="alert_close_button" />'
                   });
        $html = $(html);
        $.blockUI({
    		message: $html, fadeInTime: 0, fadeOutTime: 0,
    		overlayCSS : {backgroundColor: '#FFFFFF',opacity: 0.2}
    	});
    	$("#block_close",$html).add($("#alert_close_button",$html)).click(function(){
    	    $.unblockUI({fadeInTime: 0, fadeOutTime: 0,onUnblock: onclose});
    	});
    	$("#JS_blockPage").DragDrop({handler:"#block_draghandler"});
    };
    $.confirm = function( msg, onYes, onNo, title, width ){//simulate the window`s confirm
        title = (title && title.length > 1) ? title : $.blockUI_lang.title_confirm; //title
        msg   = msg ? msg : undefined;     //confirm content
        onYes = (onYes && typeof(onYes) == 'function') ? onYes : function(){}; //callback function when select yes
        onNo  = (onNo && typeof(onNo) == 'function') ? onNo : function(){}; //callback function when select no
        width = parseInt(width,10);
        width = isNaN(width) ? 0 : width;//the width about confirm box
        if( msg == undefined ){
            return;
        }
        if( typeof(msg) == 'string' ){
            msg = msg.replace(/\n/g,"<br />").replace(/\r/g,"<br />");
        }
        var html = blockUI_frame_insert({
                        cl_box:'block_confirm',
                        width : width,
                        isbottom:true,
                        title:title,
                        msg:msg,
                        bt_text:'<input type="button" value="'+$.blockUI_lang.button_sure+'" id="confirm_yes" style="margin-right:20px;" /><input type="button" value="'+$.blockUI_lang.button_cancel+'" id="confirm_no" />'
                   });
        $html = $(html);
        $.blockUI({
    		message: $html, fadeInTime: 0, fadeOutTime: 0,
    		overlayCSS : {backgroundColor: '#FFFFFF',opacity: 0.2}
    	});
    	$("#block_close",$html).add($("#confirm_no",$html)).click(function(){
    	    $.unblockUI({fadeInTime: 0, fadeOutTime: 0,onUnblock: onNo});
    	});
    	$("#confirm_yes",$html).click(function(){
    	    $.unblockUI({fadeInTime: 0, fadeOutTime: 0,onUnblock: onYes});
    	});
    	$("#JS_blockPage").DragDrop({handler:"#block_draghandler"});
    };
    $.ajaxUploadUI = function(opts){ //ajax upload or load file
        var ps = {
                    title    : $.blockUI_lang.title_upload, //the title
                    message  : $.blockUI_lang.desc_updefaultmsg,  //the description of the upload
                    filetype : ['txt','csv','gif','jpg','png'], //allow to upload filetype
                    loadhtml : 'loading.....', //when the uploading,the html shows
                    loadok   : '<img src="'+$.blockUI_lang.img_dir+'ok.png" />&nbsp;load has already ok..',
                    inputfile: 'ajaxUploadFile', //the element`s name about input file
                    onfinish : function(){}, //when finished upload callback
                    url      : '',
                    dataType : 'text'
        };
        opts = $.extend({}, ps, opts || {});//get the user param and default param
        var message = opts.message;
        if( message && message!=null ){
            message += '<br />';
        }else{
            message = '';
        }
        message = '<form action="'+opts.url+'" id="block_ajaxUploadForm" method="POST" enctype="multipart/form-data" target="block_ajaxUploadIframe"><div id="block_ajaxUploadArea">'+message+'<input type="file" name="'+opts.inputfile+'" id="block_ajaxUploadFile" size="40"></div></form><div id="block_ajaxUploading">'+opts.loadhtml+'</div><div id="block_ajaxUploadError"></div><iframe name="block_ajaxUploadIframe" id="block_ajaxUploadIframe" style="width:0px; height:0px;display:none;"></iframe>';
        var html = blockUI_frame_insert({
                        cl_box:'block_ajaxUpload',
                        isbottom:true,
                        title:opts.title,
                        msg:message,
                        bt_text:'<input type="button" value="'+$.blockUI_lang.button_upload+'" id="block_ajaxConfirm" />'
                   });
        $html = $(html);
        $.blockUI({
    		message: $html, fadeInTime: 0, overlayCSS : {backgroundColor: '#FFFFFF',opacity: 0.2}
    	});
    	$("#block_close",$html).click(function(){
    	    $.unblockUI({fadeOutTime: 0});
    	});
    	$("#JS_blockPage").DragDrop({handler:"#block_draghandler"});
    	
    	//submit the form and return the text
    	s = $.extend({}, $.ajaxSettings, opts);   //the setting of the jquery ajax
    	var xml = {};   //the data of request back
        var requestDone = false;    //is the request has done
    	
    	$("#block_ajaxConfirm").click(function(){
    	    filepath = $("#block_ajaxUploadFile").val();
    	    if( filepath == "" && filepath == null || filepath.length < 1 ){//there is no file been selected
    	        return;
    	    }
    	    //check the file type whether allowed upload
    	    filetype = filepath.substr(filepath.lastIndexOf(".")+1).toLowerCase();
            if( $.inArray(filetype,opts.filetype) == -1  ){
                showError($.blockUI_lang.desc_uploaderror.replace("%str%",opts.filetype.join(", ")));
                return false;
            }
            uploadStart();
            if( s.timeout > 0 ){
                setTimeout(function(){// Check to see if the request is still happening
                    if( !requestDone ){ uploadCallBack( "timeout" ); }
                }, s.timeout);
            }
            try{
    			var form = $('#block_ajaxUploadForm');
    			$(form).attr('method', 'POST');
                if(form.encoding){
                    form.encoding = 'multipart/form-data';				
                }else{				
                    form.enctype = 'multipart/form-data';
                }			
                $(form).submit();
            }catch(e){
                uploadError($.blockUI_lang.desc_uploadingerror);
                $.handleError(s, xml, 'error', e);
            }
            if(window.attachEvent){
                document.getElementById("block_ajaxUploadIframe").attachEvent('onload', uploadCallBack);    
            }else{
                document.getElementById("block_ajaxUploadIframe").addEventListener('load', uploadCallBack, false);
            }
            return {abort: function () {}};
    	});
    	
    	
    	function showError(msg){//show error message
    	    $("#block_ajaxUploadError").html(msg).show().delay(3000).fadeOut(400);
    	};
    	function uploadError(msg){//show the error when the uploading
            $("#block_ajaxConfirm").val($.blockUI_lang.button_upload).attr("disabled",false);//change the style of the button
    	    $("#block_ajaxUploading").hide(); //hide the loading div
    	    $("#block_ajaxUploadArea").show(); //show the upload form
    	    showError(msg); //show the error message
    	};
    	function uploadStart(){//call the defined function before upload
    	    requestDone = false;
    	    $("#block_ajaxConfirm").val($.blockUI_lang.button_uploading).attr("disabled",true);//change the style of the button
    	    $("#block_ajaxUploadArea").hide();
            $("#block_ajaxUploading").show();
            if( s.global && !$.active++ ){//call the global ajaxStart function
    			$.event.trigger( "ajaxStart" );
    		}
    	};
    	function uploadCallBack( isTimeout ){//the callback function when the request has done
            var iframe = document.getElementById("block_ajaxUploadIframe");
            try{//get the back data
    			if(iframe.contentWindow){
    				 xml.responseText = iframe.contentWindow.document.body ? iframe.contentWindow.document.body.innerHTML : null;
                	 xml.responseXML = iframe.contentWindow.document.XMLDocument ? iframe.contentWindow.document.XMLDocument : iframe.contentWindow.document;
    			}else if(iframe.contentDocument){
    				 xml.responseText = iframe.contentDocument.document.body ? iframe.contentDocument.document.body.innerHTML : null;
                	 xml.responseXML = iframe.contentDocument.document.XMLDocument ? iframe.contentDocument.document.XMLDocument : iframe.contentDocument.document;
    			}
            }catch(e){
                uploadError($.blockUI_lang.desc_uploadingerror);
    			$.handleError(s, xml, null, e);
    		}
    		if( xml || isTimeout == "timeout"){	//if there is data back or is time out of the request
                requestDone = true;
                var status;
                try{
                    status = isTimeout != "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified
                    if( status != "error" ){// process the data (runs the xml through httpData regardless of callback)
                        var data = uploadHttpData( xml, s.dataType );
                        $("#block_ajaxUploading").html(opts.loadok);
                        //when finished changed style and chang event
                        $("#block_ajaxConfirm").val($.blockUI_lang.button_uploadend).attr("disabled",false).die("click").click(function(){
                            $.unblockUI({fadeOutTime: 0, onUnblock: opts.onfinish});
                        });
                        $("#block_close",$html).die("click").click(function(){
                    	    $.unblockUI({fadeOutTime: 0, onUnblock: opts.onfinish});
                    	});
                        if( s.success ){   // If a local callback was specified, fire it and pass it the data
                            s.success( data, status );
                        }
                        if( s.global ){ // Fire the global callback
                            $.event.trigger( "ajaxSuccess", [xml, s] );
                        }
                    }else{
                        uploadError($.blockUI_lang.desc_uploadingerror);
                        $.handleError(s, xml, status);
                    }
                }catch(e){
                    uploadError($.blockUI_lang.desc_uploadingerror);
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
                if (/^[\],:{}\s]*$/.test(data.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@")
					.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]")
					.replace(/(?:^|:|,)(?:\s*\[)+/g, ""))){
					// Try to use the native JSON parser first
					if ( window.JSON && window.JSON.parse ) {
						data = window.JSON.parse( data );
					} else {
						data = (new Function("return " + data))();
					}
				}else{
					throw "Invalid JSON: " + data;
				}
            }
    	    //alert($('param', data).each(function(){alert($(this).attr('value'));}));
            return data;
        }
    };
    
    //insert framework about alert,confirm,ask and some like that
    function blockUI_frame_insert( opts ){
        var ps = {
                   cl_box: 'block_box',            //the css of the framework box[table]
                   cl_title: 'block_title',        //the css of the title[td]
                   cl_close: 'block_title_close',  //the css of the close[td]
                   cl_c_box: 'block_content_box',  //the css of the content box[td]
                   cl_content: 'block_content',    //the css of the content div[div]
                   cl_bottom: 'block_bottom',      //the css of the bottom[td]
                   isbottom: false,                //is need bottom :true or false
                   title: 'tip',                   //the text of the title
                   msg  : '',                      //the text of the msg content
                   bt_text : '',                   //the bottom html
                   width : 0                       //the width of the box
            };
        opts = $.extend({}, ps, opts || {});//get the user param and default param
        var html = '<table class="'+opts.cl_box+'" '+(opts.width > 0 ? 'style="width:'+opts.width+'px"' : '')+' cellpadding="0" cellspacing="0"><tr id="block_draghandler"><td class="'+opts.cl_title+'">'+opts.title+'</td><td class="'+opts.cl_title+' '+opts.cl_close+'"><a href="javascript:" class="'+opts.cl_close+'" id="block_close"><img src="'+$.blockUI_lang.img_dir+'close.gif" /></a></td></tr><tr><td colspan="2" class="'+opts.cl_c_box+'"><div class="'+opts.cl_content+'" id="block_content">'+opts.msg+'</div></td></tr>';
        if( opts.isbottom ){//if need bottom then add it
            html += '<tr><td colspan="2" class="'+opts.cl_bottom+'" id="block_bottom">'+opts.bt_text+'</td></tr></table>';
        }else{
            html += '</table>';
        }
        return html;
    }
    
    //default param about blockUI,unblockUI,fn.block,fn.unblock
    $.blockUI.defaults = {
        // message displayed when blocking (use null for no message)
        message:  '<h1>Please wait...</h1>',
        // z-index for the blocking overlay
        baseZ: 2000,
        // fadeIn time in millis; set to 0 to disable fadeIn on block
        fadeInTime:  200,
        // fadeOut time in millis; set to 0 to disable fadeOut on unblock
        fadeOutTime:  400,
        // time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock
	    timeout: 0,
	    // styles for the overlay
    	overlayCSS:{
    		backgroundColor: '#CCCCCC',
    		opacity:	  	 0.6,
    		cursor:		  	 'default'
    	},
        // set these to true to have the message automatically centered
    	centerX: true, // <-- only effects element blocking (page block controlled via css above)
    	centerY: true,
        // disable if you don't want to show the overlay
	    showOverlay: true,
	    // if true, focus will be placed in the first available input field when
        focusInput: true,
        // callback method invoked when unblocking has completed; the callback is
        // passed the element that has been unblocked (which is the window object for page
        // blocks) and the options that were passed to the unblock call:
        //     onUnblock(element, options)
        onUnblock: null,
        // don't ask; if you really must know:
        // http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493
	    quirksmodeOffsetHack: 4
    };
    
    $.blockUI.version = '1.0.0';  //the version of the plug
    
    $.blockUI.params  = {//the params of the whole plug
                          pageBlock : null,  //has the full page blocked
                          pageBlockEls : []  //all the elements which has been blocked in whole page
                        }; 
                        
    var mode    = document.documentMode || 0;
    var setExpr = $.browser.msie && (($.browser.version < 8 && !mode) || mode < 8);
    var ie6     = $.browser.msie && /MSIE 6.0/.test(navigator.userAgent) && !mode;
	var expr    = setExpr && (!$.boxModel || $('object,embed', full ? null : el).length > 0);
    
    function blockUI_install(el,opts){//the public function to install the overlay and window
        var full = el == window ? true : false; //is full screen to block
        var msgcontent = (opts && opts.message !== undefined) ? opts.message : $.blockUI.defaults.message;//get msg content
        opts = $.extend({}, $.blockUI.defaults, opts || {});//get the user param and default param
        
        // remove the current block (if there is one)
        if( full && $.blockUI.params.pageBlock ){
            blockUI_remove( window, {fadeOut:0} );
        }
        
        // if an existing element is being used as the blocking content then we capture
    	// its current place in the DOM (and current display style) so we can restore
    	// it when we unblock
    	if( msgcontent && typeof(msgcontent) != 'string' && (msgcontent.parentNode || msgcontent.jquery) ){
    		var node = msgcontent.jquery ? msgcontent[0] : msgcontent;
    		var data = {};
    		$(el).data('blockUI.history', data);
    		data.el = node;
    		data.parent = node.parentNode;
    		data.display = node.style.display;
    		data.position = node.style.position;
    		if( data.parent ){
    			data.parent.removeChild(node);
    		}
    	}
        
        var z = opts.baseZ;
        // blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
        // layer1 is the iframe layer which is used to suppress bleed through of underlying content
        // layer2 is the overlay layer which has opacity and a wait cursor (by default)
        // layer3 is the message content that is displayed while blocking
        var layer1 = $.browser.msie ? $('<iframe class="blockUI" id="JS_blockUI" style="z-index:'+ (z++) +';"></iframe>') 
                                    : $('<div class="blockUI" id="JS_blockUI" style="z-index:'+ (z++) +';"></div>');
        var layer2 = $('<div class="blockOverlay" id="JS_blockOverlay" style="z-index:'+ (z++) +';"></div>');
        var layer3 = full ? $('<div class="blockMsg" id="JS_blockPage" style="z-index:'+z+';position:fixed"></div>')
                          : $('<div class="blockMsg" id="JS_blockElement" style="z-index:'+z+';position:absolute"></div>');
        // style the overlay
        layer2.css(opts.overlayCSS).css('position', full ? 'fixed' : 'absolute');
        // make iframe layer transparent in IE
        if ($.browser.msie || opts.forceIframe){
            layer1.css('opacity',0.0);
        }
        //$([layer1[0],layer2[0],Layer3[0]]).appendTo(full ? 'body' : el);
        var layers = [layer1,layer2,layer3];
        var $par = full ? $('body') : $(el);
    	$.each(layers, function() {
    		this.appendTo($par);
    	});
        if( ie6 || expr ){
            // give body 100% height
    		if( full && $.support.boxModel ){
    			$('html,body').css('height','100%');
    		}
    		// fix ie6 issue when blocked element has a border width
            if( (ie6 || !$.boxModel) && !full ){
                var t = sz(el,'borderTopWidth');
                var l = sz(el,'borderLeftWidth');
                var fixT = t ? '(0 - '+t+')' : 0;
                var fixL = l ? '(0 - '+l+')' : 0;
            }
            // simulate fixed position
    		$.each([layer1,layer2,layer3], function(i,o) {
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
    				var top = 0;//(opts.css && opts.css.top) ? parseInt(opts.css.top) : 0;
    				var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + '+top+') + "px"';
    				s.setExpression('top',expression);
    			}
    		});
        }
        
        // show the message
    	if( msgcontent ){
    		layer3.append(msgcontent);
    		if( msgcontent.jquery || msgcontent.nodeType ){
    			$(msgcontent).show();
    		}
    	}
    	
    	if( $.browser.msie && opts.showOverlay ){
		    layer1.show(); // opacity is zero
		}
		//show layer
		if( opts.fadeInTime ){
    		if( opts.showOverlay ){
    			layer2.fadeIn(opts.fadeInTime);
    		}
    		if( msgcontent ){
    			layer3.fadeIn(opts.fadeInTime);
    		}
    	}
    	else{
    		if( opts.showOverlay ){
    			layer2.show();
    		}
    		if( msgcontent ){
    			layer3.show();
    		}
    	}
    	
    	if( full ){
    	    fullCenter(layer3[0]);
            $.blockUI.params.pageBlock = layer3[0];
            $.blockUI.params.pageBlockEls = $(':input:enabled:visible',layer3[0]);
            if( opts.focusInput ){
                setTimeout(focus, 20);
            }
        }else{
            center(layer3[0], opts.centerX, opts.centerY);
        }
        
        if( opts.timeout ){// auto-unblock
    		var to = setTimeout(function(){
    			full ? $.unblockUI(opts) : $(el).unblock(opts);
    		}, opts.timeout);
    		$(el).data('blockUI.timeout', to);
    	}
    };
    
    function blockUI_remove(el,opts){//public function of remove the block and window
        var full = el == window ? true : false; //is full screen to block
        var $el  = $(el);
        var data = $el.data('blockUI.history');
	    var to   = $el.data('blockUI.timeout');
	    opts = $.extend({}, $.blockUI.defaults, opts || {});//get the user param and default param
	    if( to ){//clear timeout if there is timeout set
    		clearTimeout(to);
    		$el.removeData('blockUI.timeout');
    	}
    	var els = full ? $('body').children().filter("[id^='JS_block']") : $("[id^='JS_block']", el);
    	if( full ){//remove the value of the param on the whole plug
		    $.blockUI.params.pageBlock = $.blockUI.params.pageBlockEls = null;
	    }
	    if( opts.fadeOutTime ){
            els.fadeOut( opts.fadeOutTime );
            setTimeout(function(){ reset(els,data,opts,el); }, opts.fadeOut);
        }
        else{
            reset(els, data, opts, el);
        }
    };
    
    function reset(els,data,opts,el){// move blocking element back into the DOM where it started
    	els.each(function(i,o){// remove via DOM calls so we don't lose event handlers
    		if( this.parentNode ){
    			this.parentNode.removeChild(this);
    		}
    	});
    	if( data && data.el ){
    		data.el.style.display  = data.display;
    		data.el.style.position = data.position;
    		if( data.parent ){
    			data.parent.appendChild( data.el );
    		}
    		$(el).removeData('blockUI.history');
    	}
    	if( typeof(opts.onUnblock) == 'function' ){
    		opts.onUnblock(el,opts);
    	}
    };
    
    function sz(el, p){//get the css value about integer
    	return parseInt($.css(el,p))||0;
    };
    function center(el, x, y){//make element center
        var p = el.parentNode, s = el.style;
        var l = ((p.offsetWidth - el.offsetWidth)/2) - sz(p,'borderLeftWidth');
        var t = ((p.offsetHeight - el.offsetHeight)/2) - sz(p,'borderTopWidth');
        if (x) s.left = l > 0 ? (l+'px') : '0';
        if (y) s.top  = t > 0 ? (t+'px') : '0';
    };
    function focus(back) {//focus on window input
    	if( !$.blockUI.params.pageBlockEls ){
    		return;
    	}
    	var e = $.blockUI.params.pageBlockEls[back===true ? $.blockUI.params.pageBlockEls.length-1 : 0];
    	if( e ){
    		e.focus();
    	}
    };
    function fullCenter(el){//make the element`s position in the center of screen
        var topset  = $(window).height()/2-$(el).height()/2+ (ie6 ? document.documentElement.scrollTop : 0);
        var leftset = $(window).width()/2 - $(el).width()/2+ (ie6 ? document.documentElement.scrollLeft : 0);
        $(el).css({left:leftset+"px",top:topset+"px"});
    };
    function limitHeight(el,h,type){ //limit hight of element, if out of heiht,then the style change to type
        if($.inArray(type,['auto','hidden','inherit','scroll','visible']) == -1){
            type = 'auto';
        }
        if( $(el).height() > h ){
            $(el).css({height:h,'overflow':type,'overflow-x':'hidden'});
        }
    };
    
    //点后弹出，位置在点击元素的下方
    $.fn.openFloat = function(msg,css){
        if( $('#JS_openFloat',$(this).parent()).length >0 ){
            $(this).closeFloat();
            return;
        }
        var offset = $(this).offset();
        var left = offset.left;
        var top  = offset.top+$(this).height();
        //alert('left:'+left+',top:'+top);
        var $ly = $('<div id="JS_openFloat" style="display:none;position:absolute;z-index:200;left:'+left+'px;top:'+top+'px"></div>');
        $ly.addClass(css);
        $ly.append(msg);
        if( msg.jquery || msg.nodeType ){
			$(msg).show();
		}
		$ly.appendTo($(this).parent());
		$ly.show('normal');
    };
    $.fn.closeFloat = function(){
        $('#JS_openFloat',$(this).parent()).remove();
    }
    
})(jQuery);