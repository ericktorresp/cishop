/* client_btn */
if(typeof(LiveAdmin)=="undefined")
{
	LiveAdmin={}
}

LiveAdmin.Init=function()
{
	this.c=LiveAdminConf;
	this.InitTrig=0;
	this.chat=null;
	this.chat_invite=null;
	this.dd_state=0;
	this.dd_obj_over=false;
	this.ie=document.all?true:false;
	this.mainframe_width=this.c.mainframe_width;
	this.mainframe_height=this.c.mainframe_height;
	this.chat_running=false;
	this.chat_running_auto=false;
	this.direct_contact=false;
	this.lv_enc=new LiveAdminEnc;
	this.c.conf_auto_invite=parseInt(this.c.conf_auto_invite);
	this.c.conf_auto_invite_delay=parseInt(this.c.conf_auto_invite_delay);
	this.c.opt_auto_invite_mode=parseInt(this.c.opt_auto_invite_mode);
	this.isIE=(navigator.appVersion.indexOf("MSIE")!=-1)?true:false;
	this.isWin=(navigator.appVersion.toLowerCase().indexOf("win")!=-1)?true:false;
	this.isOpera=(navigator.userAgent.indexOf("Opera")!=-1)?true:false;
	this.isSafari=(navigator.userAgent.indexOf("Safari")!=-1)?true:false;
	this.runCompatible=false;
	this.runPopup=false;
	this.supportFixed=true;
	if(this.isIE)
	{
		this.IEVer=navigator.appVersion.match(/MSIE\s+(\d)/)[1];
		if(this.IEVer<=6)
		{
			this.runCompatible=true
		}
	}
	if(typeof(this.c.popup_theme)!='undefined'&&this.c.popup_theme=='y')
	{
		LiveAdmin.runPopup=true
	}
	
	if(this.isSafari)
		this.InitInterval=window.setInterval("LiveAdmin.CheckInit()",this.c.conf_init_interval);
	else
		this.InitInterval=window.setInterval(function(){LiveAdmin.CheckInit()},this.c.conf_init_interval)
};

LiveAdmin.CheckInit=function()
{
	LiveAdmin.InitTrig++;
	if(LiveAdmin.InitTrig>LiveAdmin.c.conf_init_timeout)
	{
		window.clearInterval(LiveAdmin.InitInterval);
		return
	}
	if(document.getElementById(LiveAdmin.c.conf_status_image_id))
	{
		window.clearInterval(LiveAdmin.InitInterval);
		this.supportFixed=LiveAdmin.HasFixedPos();
		LiveAdmin.InitMain();
		LiveAdmin.InitStyles()
	}
};
LiveAdmin.InitMain=function()
{
	if(!document.getElementById(this.c.conf_status_image_id))
		return;
	switch(this.c.conf_online_status*1)
	{
		case 1:
			var dm=document.getElementById(this.c.conf_status_image_id);
			dm.innerHTML='<img style="cursor: pointer;border: none 0px;" id="liveadmin_status_image_'+this.c.conf_status_image_id+'" src="'+this.c.conf_online_image+'" onclick="LiveAdmin.StartChatPanel();" />';
			if(this.c.conf_auto_invite>=1)
			{
				if(this.isSafari)
					window.setTimeout("LiveAdmin.StartInvite()",this.c.conf_auto_invite_delay*1000);
				else
					window.setTimeout(function(){LiveAdmin.StartInvite()},this.c.conf_auto_invite_delay*1000)
			}
			if(this.c.conf_enable_callback=="y"||this.c.conf_enable_callback=="yi")
			{
				dm.innerHTML+='<img id="liveadmin_'+this.c.conf_status_image_id+'_callback_img_id" style="visibility: hidden;" src="'+LiveAdmin.c.conf_callback+'&rnd='+Math.random()+'" />';
				this.check_callback_trig=0;
				this.CallbackLoop()
			}
		break;
		case 0:
			var csm=' onclick="LiveAdmin.StartChatPanel();"';
			if(this.c.conf_offline_act*1==1)
				csm='';
			document.getElementById(this.c.conf_status_image_id).innerHTML='<img style="cursor: pointer;" id="liveadmin_status_image_'+this.c.conf_status_image_id+'" src="'+this.c.conf_offline_image+'" '+csm+' />';
		break;
		default:
			document.getElementById(this.c.conf_status_image_id).innerHTML='<img src="'+this.c.conf_invalid_image+'" />';
		break
	}
};

LiveAdmin.InitStyles=function()
{
	if(!this.c.conf_style_enc)
		return;
	var style_text=this.lv_enc._data_decode(this.c.conf_style_enc);
	if(!this.supportFixed)
	{
		if(LiveAdmin.IsCompat())
			comp='document.documentElement';
		else
			comp='document.body';
		var scroll_top=comp+'.scrollTop';
		var scroll_left=comp+'.scrollLeft';
		var client_height=comp+'.clientHeight';
		var client_width=comp+'.clientWidth';
		var ncss="_position:absolute;";
		ncss+="_top:expression("+scroll_top+" + ("+client_height+"/2)-(this.clientHeight/2));";
		ncss+="_left:expression("+scroll_left+" + ("+client_width+"/2)-(this.clientWidth/2));";
		style_text=style_text.replace(/lv_extra_quirks:lv_invite;/,ncss);
		style_text=style_text.replace(/lv_extra_quirks:lv_mainframe;/,ncss);
		style_text=style_text.replace(/right:\s*(\d*)px;/gi,function(str,p1,offset,s){return('_width:expression((parentElement.clientWidth-this.offsetLeft-'+p1+')+"px");')});
		style_text=style_text.replace(/bottom:\s*(\d*)px;/gi,function(str,p1,offset,s){return('_height:expression((parentElement.clientHeight-this.offsetTop-'+p1+')+"px");')})
	}
	this.all_css=document.createElement('style');
	this.all_css.setAttribute("type","text/css");
	if(this.all_css.styleSheet)
	{
		this.all_css.styleSheet.cssText=style_text
	}
	else
	{
		var all_css_txt=document.createTextNode(style_text);
		this.all_css.appendChild(all_css_txt)
	}
	var head=document.getElementsByTagName('head')[0];
	var fc=head.firstChild;
	if(head.firstChild)
		head.insertBefore(this.all_css,fc);
	else
		head.appendChild(this.all_css)
};
	
LiveAdmin.DoCallback=function()
{
	if(this.chat_running)
	{
		if(this.c.conf_lv_standalone=='y')
			this.CallbackLoop();
		return
	}
	var el=document.getElementById("liveadmin_"+this.c.conf_status_image_id+"_callback_img_id");
	if(typeof(el.width)!='undefined'&&el.width==2)
	{
		this.check_callback_trig=0;
		if(this.c.conf_lv_standalone=='y')
			el.src=LiveAdmin.c.conf_callback+'&rnd='+Math.random();
		LiveAdmin.direct_contact=true;
		if(this.c.conf_enable_callback=="yi")
			LiveAdmin.StartInvite();
		else
			LiveAdmin.StartChatPanel();
		if(this.c.conf_lv_standalone=='y')
			this.CallbackLoop();
		return
	}
	
	if(this.check_callback_trig==this.c.conf_callback_interval_s)
	{
		this.check_callback_trig=0;
		el.src=LiveAdmin.c.conf_callback+'&rnd='+Math.random()
	}
	this.check_callback_trig++;
	this.CallbackLoop()
};
LiveAdmin.CallbackLoop=function()
{
	if(this.isSafari)
		window.setTimeout("LiveAdmin.DoCallback()",this.c.conf_callback_check_s*1000);
	else
		window.setTimeout(function(){LiveAdmin.DoCallback()},this.c.conf_callback_check_s*1000)
};

LiveAdmin.AssignStyle=function(style,el)
{
	for(key in style)
	{
		eval("el.style."+key+"=\""+style[key]+"\"")
	}
};

LiveAdmin.StartChatPanel=function()
{
	this.chat_running_auto=true;
	if(this.chat_running)
		return;
	this.chat_running=true;
	if(!this.chat)
		this.chat=new LiveAdminChat;
	this.chat.Init()
};
	
LiveAdmin.StartInvite=function()
{
	if(!this.direct_contact)
	{
		if(this.c.opt_auto_invite_mode==0)
			this.AutoInviteCookieClear();
		else
			if(this.AutoInviteCookieGet()=='g')
			{
				return
			}
			this.AutoInviteCookieSet()
	}
	if(this.c.conf_auto_invite==1&&!this.direct_contact)
	{
		LiveAdmin.StartChatPanelAuto()
	}
	else
	{
		if(this.chat_running||this.chat_running_auto)
			return;
		if(!this.chat_invite)
			this.chat_invite=new LiveAdminInvite;
		this.chat_invite.ShowInvite()
	}
};

LiveAdmin.HideInvite=function(mode)
{
	if(!this.chat_invite)
		this.chat_invite=new LiveAdminInvite;
	this.chat_invite.HideInvite();
	if(this.direct_contact)
	{
		if(mode=='y')
		{
			this.StartChatPanel()
		}
		else
		{
			if(this.c.conf_lv_standalone!='y')
			{
				LiveAdmin.CleanCallBack()
			}
		}
	}
	else
	{
		if(mode=='y')
			this.StartChatPanelAuto()
	}
};

LiveAdmin.AutoInviteCookieSet=function()
{
	var expiredays=null;
	switch(this.c.opt_auto_invite_mode)
	{
		case 0:
		return;
		case 1:
			expiredays=null;
		break;
		case 2:
			expiredays=1;
		break;
		case 3:
			expiredays=7;
		break;
		case 4:
			expiredays=30;
		break;
		case 5:
			expiredays=365;
		break
	}
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie="lv_auto_invite="+escape('g')+((expiredays==null)?"":";expires="+exdate.toUTCString())
};

LiveAdmin.AutoInviteCookieClear=function()
{
	var exdate=new Date();
	exdate.setDate(exdate.getDate()-720);
	document.cookie="lv_auto_invite="+escape('g')+";expires="+exdate.toUTCString()
};
	
LiveAdmin.AutoInviteCookieGet=function()
{
	var c_name='lv_auto_invite';
	if(document.cookie.length>0)
	{
		c_start=document.cookie.indexOf(c_name+"=");
		if(c_start!=-1)
		{
			c_start=c_start+c_name.length+1;
			c_end=document.cookie.indexOf(";",c_start);
			if(c_end==-1)
				c_end=document.cookie.length;
			return unescape(document.cookie.substring(c_start,c_end))
		}
	}
	return""
};

LiveAdmin.CleanCallBack=function()
{
	var test=document.createElement('img');
	test.style.position='absolute';
	test.style.left='0px';
	test.style.top='0px';
	test.style.width='1px';
	test.style.height='1px';
	test.src=LiveAdmin.c.conf_iframe+"&rnd="+Math.random()+"&mode=clean_callback";
	document.body.appendChild(test);
	document.body.removeChild(test)
};

LiveAdmin.StartChatPanelAuto=function()
{
	if(this.chat_running_auto)
		return;
	LiveAdmin.StartChatPanel()
};
LiveAdmin.CloseChatPanel=function()
{
	this.chat_running=false;
	if(this.chat)
		this.chat.DestroyAll()
};
	
LiveAdmin.RandomString=function(len)
{
	var ks="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	var s='';
	var ds=0;
	for(var i=0;i<len+10;i++)
	{
		ds=Math.ceil(Math.random()*61);
		s=s+ks.substr(ds,1)
	}
	if(s.length==0)
	{
		for(var i=0;i<len+10;i++)
		{
			s+=("_"+Math.random())
		}
	}
	return(s.substr(0,len))
};
LiveAdmin.MDown=function(ev)
{
	if(!this.chat)
		return;
	this.dd_state=1;
	this.chat.mask_iframe.style.display='';
	if(this.ie)
	{
		this.dd_mx=event.clientX;
		this.dd_my=event.clientY
	}
	else
	{
		this.dd_mx=ev.clientX;
		this.dd_my=ev.clientY;
		return false
	}
};
LiveAdmin.MUp=function(ev)
{
	if(!this.chat)
		return;
	this.dd_state=0;
	this.chat.mask_iframe.style.display='none'
};
LiveAdmin.MMove=function(ev)
{
	if(!this.chat)
		return;
	if(this.dd_state!=1)
		return;
	var cL=parseInt(this.chat.main_frame.style.left.replace(/[a-zA-Z]/gi,''),10);
	var cT=parseInt(this.chat.main_frame.style.top.replace(/[a-zA-Z]/gi,''),10);
	if(this.ie)
	{
		this.chat.main_frame.style.left=((event.clientX-this.dd_mx)+cL)+'px';
		this.chat.main_frame.style.top=((event.clientY-this.dd_my)+cT)+'px';
		this.dd_mx=event.clientX;
		this.dd_my=event.clientY
	}
	else
	{
		this.chat.main_frame.style.left=((ev.clientX-this.dd_mx)+cL)+'px';
		this.chat.main_frame.style.top=((ev.clientY-this.dd_my)+cT)+'px';
		this.dd_mx=ev.clientX;
		this.dd_my=ev.clientY
	}
	return false
};

LiveAdmin.IsCompat=function()
{
	if(typeof(document.compatMode)!='undefined'&&document.compatMode!='BackCompat')
		return(true);
	else
		return(false)
};
LiveAdmin.HasFixedPos=function()
{
	var test=document.createElement('div');
	test.style.position='fixed';
	test.style.left='152px';
	test.style.top='18px';
	test.style.height='10px';
	test.style.width='10px';
	document.body.appendChild(test);
	var RV=true;
	if(test.offsetTop!=18||test.offsetLeft!=152)
		RV=false;
	document.body.removeChild(test);
	if(this.isOpera)
		RV=true;
	return(RV)
};

function LiveAdminChat()
{
	this.client_uniq='';
	this.allnull=function()
	{
		this.base_panel=null;
		this.back_black=null;
		this.main_frame=null;
		this.top_frame=null;
		this.close_button=null;
		this.base_iframe=null;
		this.mask_iframe=null;
		this.iframe=null
	};
	this.Init=function()
	{
		this.allnull();
		this.client_uniq=LiveAdmin.RandomString(32);
		this.lv_enc=new LiveAdminEnc;
		if(LiveAdmin.runCompatible||LiveAdmin.runPopup)
		{
			this.ShowChatFramePopup()
		}
		else
		{
			if(window.addEventListener)
				window.addEventListener('resize',function(event){LiveAdmin.chat.ResizeWindow(event)},false);
			else
				if(window.attachEvent)
					window.attachEvent('onresize',function(event){LiveAdmin.chat.ResizeWindow(event)});
			this.ShowChatFrame();
			this.ResizeWindow()
		}
	};
	this.ShowChatFramePopup=function()
	{
		var dcstr='';
		if(LiveAdmin.direct_contact)
		{
			dcstr='&dc=1'
		}
		var extra_fields=this.getExtraFields();
		var url=LiveAdmin.c.conf_iframe+"&rnd="+Math.random()+"&mode=chat&compatible=1&client_uniq="+this.client_uniq+dcstr+extra_fields;
		window.open(url,'liveadmin_chat_window',"height="+LiveAdmin.mainframe_height+",width="+LiveAdmin.mainframe_width+",status=yes,toolbar=no,menubar=no,location=no,resizable=yes,scrollbars=yes")
	};
	this.ShowChatFrame=function()
	{
		if(!this.base_panel)
			this.base_panel=document.createElement('div');
		this.SetIDClass(this.base_panel,'lv_basepanel','lv_liveadmin');
		document.body.appendChild(this.base_panel);
		if(!this.back_black)
			this.back_black=document.createElement('div');
		this.SetIDClass(this.back_black,'lv_backblack','lv_backblack');
		if(document.addEventListener)
			document.addEventListener('mousemove',function(event){return(LiveAdmin.MMove(event))},false);
		else 
			if(document.attachEvent)
				document.attachEvent('onmousemove',function(event){return(LiveAdmin.MMove(event))});
		this.base_panel.appendChild(this.back_black);
		this.ob=false;
		if(!this.main_frame)
			this.main_frame=document.createElement('div');
		this.SetIDClass(this.main_frame,'lv_mainframe','lv_mainframe');
		if(this.ob)
		{
			this.main_frame.style.position='absolute'
		}
		this.main_frame.style.width=LiveAdmin.mainframe_width+'px';
		this.main_frame.style.height=LiveAdmin.mainframe_height+'px';
		this.base_panel.appendChild(this.main_frame);
		if(!this.top_frame)
			this.top_frame=document.createElement('div');
		this.SetIDClass(this.top_frame,'lv_topframe','lv_topframe');
		if(this.ob)
		{
			this.top_frame.style.width=(LiveAdmin.c.od_w)+'px'
		}
		this.main_frame.appendChild(this.top_frame);
		if(!this.close_button)
			this.close_button=document.createElement('div');
		this.SetIDClass(this.close_button,'lv_closebutton','lv_closebutton');
		this.close_button.onclick=function()
		{
			LiveAdmin.CloseChatPanel()
		};
		this.top_frame.appendChild(this.close_button);
		if(!this.chat_title)
			this.chat_title=document.createElement('div');
		this.SetIDClass(this.chat_title,'lv_chattitle','lv_chattitle');
		this.chat_title.innerHTML=LiveAdmin.c.conf_title;
		this.top_frame.appendChild(this.chat_title);
		if(!this.chat_mcap)
			this.chat_mcap=document.createElement('div');
		this.SetIDClass(this.chat_mcap,'lv_chatmcap','lv_chatmcap');
		this.chat_mcap.onmousedown=function(ev)
		{
			return(LiveAdmin.MDown(ev))
		};
		this.chat_mcap.onmouseup=function(ev)
		{
			return(LiveAdmin.MUp(ev))
		};
		if(this.ob)
		{
			this.chat_mcap.style.width=(LiveAdmin.c.od_w-LiveAdmin.c.od_mcap_w)+'px'
		}
		this.top_frame.appendChild(this.chat_mcap);
		if(!this.base_iframe)
			this.base_iframe=document.createElement('div');
		this.SetIDClass(this.base_iframe,'lv_baseiframe','lv_baseiframe');
		if(this.ob)
		{
			this.base_iframe.style.width=(LiveAdmin.c.od_wf)+'px';
			this.base_iframe.style.height=(LiveAdmin.c.od_hf)+'px'
		}
		this.main_frame.appendChild(this.base_iframe);
		var iframe_id='lv_iframe_'+LiveAdmin.RandomString(10);
		if(!this.iframe)
			this.iframe=document.createElement('iframe');
		this.SetIDClass(this.iframe,iframe_id,'lv_iframe');
		this.iframe.frameBorder=0;
		this.iframe.border=0;
		this.iframe.scrolling='no';
		this.iframe.marginWidth=0;
		this.iframe.marginHeight=0;
		var obstr='';
		if(this.ob)
		{
			if(LiveAdmin.isIE&&LiveAdmin.IEVer>7)
				obstr='';
			else
				obstr='&ob=1'
		}
		var dcstr='';
		if(LiveAdmin.direct_contact)
		{
			LiveAdmin.direct_contact=false;
			dcstr='&dc=1'
		}
		var extra_fields=this.getExtraFields();
		this.iframe.setAttribute('src',LiveAdmin.c.conf_iframe+"&rnd="+Math.random()+"&mode=chat&client_uniq="+this.client_uniq+obstr+dcstr+extra_fields);
		this.iframe.style.width=this.base_iframe.clientWidth+'px';
		this.iframe.style.height=this.base_iframe.clientHeight+'px';
		this.base_iframe.appendChild(this.iframe);
		if(!this.mask_iframe)
			this.mask_iframe=document.createElement('div');
		this.SetIDClass(this.mask_iframe,'lv_maskiframe','lv_maskiframe');
		this.mask_iframe.style.display='none';
		if(this.ob)
		{
			this.mask_iframe.style.width=(LiveAdmin.c.od_wf)+'px';
			this.mask_iframe.style.height=(LiveAdmin.c.od_hf)+'px'
		}
		this.main_frame.appendChild(this.mask_iframe)
	};
	this.SetIDClass=function(el,id,cls)
	{
		el.setAttribute('id',id);
		el.setAttribute('class',cls);
		el.setAttribute('className',cls)
	};
	this.ResizeWindow=function(event)
	{
		if(!this.main_frame||!LiveAdmin.supportFixed)
			return;
		if(this.ob)
		{
			var pgsize=this.getPageSize();
			this.back_black.style.width=pgsize['pageWidth']+'px';
			this.back_black.style.height=pgsize['pageHeight']+'px';
			this.main_frame.style.top=((pgsize['windowHeight']/2)-(LiveAdmin.c.od_h/2))+'px';
			this.main_frame.style.left=((this.back_black.clientWidth/2)-(LiveAdmin.c.od_w/2))+'px'
		}
		else
		{
			this.main_frame.style.top=((this.back_black.clientHeight/2)-(LiveAdmin.mainframe_height/2))+'px';
			this.main_frame.style.left=((this.back_black.clientWidth/2)-(LiveAdmin.mainframe_width/2))+'px'
		}
	};
	this.DestroyAll=function()
	{
		if(this.iframe)
			this.iframe.setAttribute('src',LiveAdmin.c.conf_iframe+"&mode=close&client_uniq="+this.client_uniq+"&rnd="+Math.random());
		document.body.removeChild(this.base_panel);
		this.allnull()
	};
	this.getPageSize=function()
	{
		var xScroll,yScroll;
		if(window.innerHeight&&window.scrollMaxY)
		{
			xScroll=window.innerWidth+window.scrollMaxX;
			yScroll=window.innerHeight+window.scrollMaxY
		}
		else if(document.body.scrollHeight>document.body.offsetHeight)
		{
			xScroll=document.body.scrollWidth;
			yScroll=document.body.scrollHeight
		}
		else
		{
			xScroll=document.body.offsetWidth;
			yScroll=document.body.offsetHeight
		}
		var windowWidth,windowHeight;
		if(self.innerHeight)
		{
			if(document.documentElement.clientWidth)
			{
				windowWidth=document.documentElement.clientWidth
			}
			else
			{
				windowWidth=self.innerWidth
			}
			windowHeight=self.innerHeight
		}
		else if(document.documentElement&&document.documentElement.clientHeight)
		{
			windowWidth=document.documentElement.clientWidth;
			windowHeight=document.documentElement.clientHeight
		}
		else if(document.body)
		{
			windowWidth=document.body.clientWidth;
			windowHeight=document.body.clientHeight
		}
		if(yScroll<windowHeight)
		{
			pageHeight=windowHeight
		}
		else
		{
			pageHeight=yScroll
		}
		if(xScroll<windowWidth)
		{
			pageWidth=xScroll
		}
		else
		{
			pageWidth=windowWidth
		}
		return{'pageWidth':pageWidth,'pageHeight':pageHeight,'xScroll':xScroll,'yScroll':yScroll,'windowWidth':windowWidth,'windowHeight':windowHeight}
	};
	this.getExtraFields=function()
	{
		var extra_fields='';
		if(typeof(live_admin_extra_fields)!='undefined')
		{
			for(var i=0;i<live_admin_extra_fields.length;i++)
			{
				if(typeof(live_admin_extra_fields[i]['tag'])=='undefined'||live_admin_extra_fields[i]['tag']=='')
				{
					extra_fields+='&laef_'+this.lv_enc._data_encode(live_admin_extra_fields[i]['name'])+'='+this.lv_enc._data_encode(live_admin_extra_fields[i]['value'])
				}
				else
				{
					if(live_admin_extra_fields[i]['tag']==LiveAdmin.c.conf_tag)
						extra_fields+='&laef_'+this.lv_enc._data_encode(live_admin_extra_fields[i]['name'])+'='+this.lv_enc._data_encode(live_admin_extra_fields[i]['value'])
				}
			}
		}
		return(extra_fields)
	}
};

function LiveAdminInvite()
{
	this.Init=function()
	{
		this.el_invite=null;
		this.el_invite_img=null;
		this.el_invite_yes=null;
		this.el_invite_no=null;
		this.ob=false;
		this.chat=new LiveAdminChat;
		if(LiveAdmin.supportFixed)
		{
			if(window.addEventListener)
				window.addEventListener('resize',function(event){LiveAdmin.chat_invite.ResizeWindow(event)},false);
			else
				if(window.attachEvent)
					window.attachEvent('onresize',function(event){LiveAdmin.chat_invite.ResizeWindow(event)})
		}
	};
	
	this.ShowInvite=function()
	{
		if(!this.el_invite)
			this.el_invite=document.createElement('div');
		this.chat.SetIDClass(this.el_invite,'lv_invite','lv_invite');
		document.body.appendChild(this.el_invite);
		if(!this.el_invite_img)
			this.el_invite_img=document.createElement('div');
		this.chat.SetIDClass(this.el_invite_img,'lv_invite_img','lv_invite_img');
		this.el_invite.appendChild(this.el_invite_img);
		if(!this.el_invite_yes)
			this.el_invite_yes=document.createElement('div');
		this.chat.SetIDClass(this.el_invite_yes,'lv_yes','lv_yes');
		this.el_invite_img.appendChild(this.el_invite_yes);
		if(!this.el_invite_no)
			this.el_invite_no=document.createElement('div');
		this.chat.SetIDClass(this.el_invite_no,'lv_no','lv_no');
		this.el_invite_img.appendChild(this.el_invite_no);
		this.el_invite_yes.onclick=function()
		{
			LiveAdmin.HideInvite('y')
		};
		this.el_invite_no.onclick=function()
		{
			LiveAdmin.HideInvite('n')
		};
		this.ResizeWindow()
	};
		
	this.HideInvite=function()
	{
		if(!this.el_invite)
			return;
		this.el_invite.style.display='none'
	};
	this.ResizeWindow=function(event)
	{
		if(!this.el_invite||!LiveAdmin.supportFixed)
			return;
		var pgsize=this.chat.getPageSize();
		this.el_invite.style.top=((pgsize['windowHeight']/2)-(this.el_invite.offsetHeight/2))+'px';
		this.el_invite.style.left=((pgsize['windowWidth']/2)-(this.el_invite.offsetWidth/2))+'px'
	};
	this.Init()
};

function LiveAdminEnc()
{
	this._keyStr="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	this._data_encode=function(input)
	{
		var _keyStr="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
		var output="";
		var chr1,chr2,chr3,enc1,enc2,enc3,enc4;
		var i=0;
		input=this._utf8_encode(input);
		while(i<input.length)
		{
			chr1=input.charCodeAt(i++);
			chr2=input.charCodeAt(i++);
			chr3=input.charCodeAt(i++);
			enc1=chr1>>2;
			enc2=((chr1&3)<<4)|(chr2>>4);
			enc3=((chr2&15)<<2)|(chr3>>6);
			enc4=chr3&63;
			if(isNaN(chr2))
			{
				enc3=enc4=64
			}
			else
				if(isNaN(chr3))
				{
					enc4=64
				}
				output=output+this._keyStr.charAt(enc1)+this._keyStr.charAt(enc2)+this._keyStr.charAt(enc3)+this._keyStr.charAt(enc4)
		}
		output=output.replace(/\=/g,'_E');
		output=output.replace(/\+/g,'_P');
		output=output.replace(/\//g,'_S');
		return output
	};
	this._data_decode=function(input)
	{
		var output="";
		var chr1,chr2,chr3;
		var enc1,enc2,enc3,enc4;
		var i=0;
		input=input.replace(/_E/g,"=");
		input=input.replace(/_P/g,"+");
		input=input.replace(/_S/g,"/");
		input=input.replace(/[^A-Za-z0-9\+\/\=]/g,"");
		while(i<input.length)
		{
			enc1=this._keyStr.indexOf(input.charAt(i++));
			enc2=this._keyStr.indexOf(input.charAt(i++));
			enc3=this._keyStr.indexOf(input.charAt(i++));
			enc4=this._keyStr.indexOf(input.charAt(i++));
			chr1=(enc1<<2)|(enc2>>4);
			chr2=((enc2&15)<<4)|(enc3>>2);
			chr3=((enc3&3)<<6)|enc4;
			output=output+String.fromCharCode(chr1);
			if(enc3!=64)
			{
				output=output+String.fromCharCode(chr2)
			}
			if(enc4!=64)
			{
				output=output+String.fromCharCode(chr3)
			}
		}
		output=this._utf8_decode(output);
		return output
	};
	this._utf8_encode=function(string)
	{
		string=string.replace(/\r\n/g,"\n");
		var utftext="";
		for(var n=0;n<string.length;n++)
		{
			var c=string.charCodeAt(n);
			if(c<128)
			{
				utftext+=String.fromCharCode(c)
			}
			else 
				if((c>127)&&(c<2048))
				{
					utftext+=String.fromCharCode((c>>6)|192);
					utftext+=String.fromCharCode((c&63)|128)
				}
				else
				{
					utftext+=String.fromCharCode((c>>12)|224);
					utftext+=String.fromCharCode(((c>>6)&63)|128);
					utftext+=String.fromCharCode((c&63)|128)
				}
		}
		return utftext
	};
	this._utf8_decode=function(utftext)
	{
		var string="";
		var i=0;
		var c=c1=c2=0;
		while(i<utftext.length)
		{
			c=utftext.charCodeAt(i);
			if(c<128)
			{
				string+=String.fromCharCode(c);
				i++
			}
			else 
				if((c>191)&&(c<224))
				{
					c2=utftext.charCodeAt(i+1);
					string+=String.fromCharCode(((c&31)<<6)|(c2&63));
					i+=2
				}
				else
				{
					c2=utftext.charCodeAt(i+1);
					c3=utftext.charCodeAt(i+2);
					string+=String.fromCharCode(((c&15)<<12)|((c2&63)<<6)|(c3&63));
					i+=3
				}
		}
		return string
	}
};
