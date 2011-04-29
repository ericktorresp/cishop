/* client_chat */
LiveAdmin.MessageLoopBreak=false;
LiveAdmin.RespGet=function(resp)
{
	resp3="   "+resp+"   ";
	var resp2=resp3.match(/<--START-->(.*)<--END-->/mi);
	if(resp2)
	{
		resp2=resp2[1];
		return(resp2)
	}
	return(resp)
};
LiveAdmin.RespToArray=function(resp)
{
	var ar=new Array();
	var resp2=LiveAdmin.RespGet(resp);
	var ar1=resp2.split('|');
	for(key in ar1)
	{
		var st1=ar1[key];
		if(typeof(st1)=='string')
		{
			var ar2=st1.split(',');
			ar[ar2[0]]=ar2[1]
		}
	}
	return(ar)
};

LiveAdmin.AddMessageToView=function(em,s,clss)
{
	LiveAdmin.message_div_index++;
	if(clss!='')
		clss=' class="'+clss+'"';
	document.getElementById('message_id').innerHTML+='<div'+clss+'><em>'+em+'</em><span>'+s+'</span></div><span class="message_end" id=msg_idf_'+LiveAdmin.message_div_index+'></span>';
	
	for(i=0;i<100;i++)
	{
		document.getElementById("msg_idf_"+LiveAdmin.message_div_index).scrollIntoView(false)
	}
};

LiveAdmin.Hide=function(id)
{
	if(LiveAdmin.Get(id).style)
		LiveAdmin.Get(id).style.display='none'
};
LiveAdmin.Show=function(id)
{
	if(LiveAdmin.Get(id).style)
		LiveAdmin.Get(id).style.display=''
};
LiveAdmin.Text=function(id,text)
{
	LiveAdmin.Get(id).innerHTML=text
};

LiveAdmin.SendText=function()
{
	var text=LiveAdmin.Get('text_id').value;
	LiveAdmin.Get('text_id').value='';
	LiveAdmin.TextKeyUp();
	LiveAdminQueue.AddText(text);
	return false
};
LiveAdmin.Get=function(id)
{
	if(document.getElementById(id))
		return(document.getElementById(id));
	return(new Object())
};

LiveAdmin.SetWH=function(id,w,h)
{
	var dd=LiveAdmin.Get(id);
	if(w!=''&&dd.style)
		dd.style.width=w+'px';
	if(h!=''&&dd.style)
		dd.style.height=h+'px'
};
LiveAdmin.HasFixedPos=function()
{
	var isOpera=(navigator.userAgent.indexOf("Opera")!=-1)?true:false;
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
	if(isOpera)
		RV=true;return(RV)
};

LiveAdmin.TextKeyUp=function()
{
	if(typeof(document.getElementById('text_id').value)!='undefined')
	{
		var text=document.getElementById('text_id').value;
		if(text.length>0)
		{
			if(LiveAdmin.text_id_client_type!=true)
			{
				LiveAdmin.text_id_client_type=true;
				LiveAdmin.SendControlMessage(LiveAdmin.CTL_CLIENT_TYPE_ON)
			}
		}
		else
		{
			if(LiveAdmin.text_id_client_type==true)
			{
				LiveAdmin.text_id_client_type=false;
				LiveAdmin.SendControlMessage(LiveAdmin.CTL_CLIENT_TYPE_OFF)
			}
		}
	}
};

LiveAdmin.TextKeyDown=function(ev)
{
	if(!ev||typeof(ev)=='undefined')
		return;
	if(!ev.shiftKey&&!ev.ctrlKey&&!ev.altKey)
	{
		if(ev.keyCode==13)
		{
			ev.returnValue=false;
			LiveAdmin.SendText();
			return false
		}
	}
};
LiveAdmin.SendControlMessage=function(msg)
{
	LiveAdminJax({'parameters':{'mode':'control','message':msg,'key':LiveAdmin.site_key,'client_uniq':LiveAdmin.client_uniq,'server_uniq':LiveAdmin.server_uniq,'rnd':Math.random()},'timeout':10,'onSuccess':function(req){},'onError':function(req){}})
};

LiveAdmin.CloseEvent=function()
{
	try
	{
		new Ajax.Request(LiveAdmin.conf_chat_url,{parameters:{'mode':'close','key':LiveAdmin.site_key,'client_uniq':LiveAdmin.client_uniq,'rnd':Math.random()},asynchronous:false,method:'get',onSuccess:function(req){},onFailure:function(req){}})
	}
	catch(e)
	{
	}
	return true
};

LiveAdmin.Initialize=function()
{
	LiveAdmin.last_check_msg='';
	if(LiveAdmin.ob)
	{
		var w=LiveAdmin.mainframe_width;
		var h=LiveAdmin.mainframe_height;
		if(typeof(LiveAdmin.mainframe_width_od)!='undefined')
			w=LiveAdmin.mainframe_width_od;
		if(typeof(LiveAdmin.mainframe_height_od)!='undefined')
			h=LiveAdmin.mainframe_height_od;
		LiveAdmin.SetWH('liveadmin',w,h);
		LiveAdmin.SetWH('chat_getinfo_screen',w,h-15);
		LiveAdmin.SetWH('hid_01',w-15,h-75);
		LiveAdmin.SetWH('hid_02',w-5,'');
		LiveAdmin.SetWH('hid_03',w-30,'');
		LiveAdmin.SetWH('chat_init_screen',w,h-15);
		LiveAdmin.SetWH('hid_11',w-15,h-28);
		LiveAdmin.SetWH('hid_12',w-20,'');
		LiveAdmin.SetWH('chat_screen',w,h-15);
		LiveAdmin.SetWH('hid_21',w-15,h-105);
		LiveAdmin.SetWH('hid_22',w-5,'');
		LiveAdmin.SetWH('hid_23',w-5,'');
		LiveAdmin.SetWH('hid_24',w-14,h-26);
		LiveAdmin.SetWH('message_id',w-17,h-106);
		LiveAdmin.SetWH('message_title_id',w-17,'');
		LiveAdmin.SetWH('message_onhold_id',w-23,'');
		LiveAdmin.SetWH('message_close_id',w-23,'');
		LiveAdmin.SetWH('chat_take_message_screen',w,h-15);
		LiveAdmin.SetWH('hid_31',w-15,h-75);
		LiveAdmin.SetWH('hid_32',w-5,'');
		LiveAdmin.SetWH('hid_33',w-30,'');
		LiveAdmin.SetWH('chat_take_message_wait_screen',w,h-15);
		LiveAdmin.SetWH('hid_41',w-15,h-28);
		LiveAdmin.SetWH('hid_42',w-20,'');
		LiveAdmin.SetWH('chat_take_message_done_screen',w,h-15);
		LiveAdmin.SetWH('hid_51',w-15,h-28);
		LiveAdmin.SetWH('hid_52',w-20,'');
		LiveAdmin.SetWH('chat_take_busy_message_screen',w,h-15);
		LiveAdmin.SetWH('hid_61',w-15,h-28);
		LiveAdmin.SetWH('hid_62',w-20,'');
		LiveAdmin.SetWH('chat_error_screen',w,h-15);
		LiveAdmin.SetWH('hid_71',w-15,h-28);
		LiveAdmin.SetWH('hid_72',w-20,'');
		LiveAdmin.SetWH('chat_load_screen',w,h-15);
		LiveAdmin.SetWH('hid_81',w-15,h-28);
		LiveAdmin.SetWH('hid_82',w-20,'')
	}
	LiveAdmin.HideAll();
	LiveAdmin.Show('chat_load_screen');
	LiveAdmin.Hide('message_onhold_id');
	LiveAdmin.Hide('message_close_id');
	LiveAdmin.FindRepCancelled=false;
	LiveAdminJax({
		'parameters':{'mode':'init','key':LiveAdmin.site_key,'client_uniq':LiveAdmin.client_uniq,'rnd':Math.random()},
		'timeout':10,
		'onSuccess':function(req){
			LiveAdmin.CheckInitStatus(req.responseText)
		},
		'onError':function(req){
			LiveAdmin.ShowError('Error!\n'+req.statusText)
		}
	});
	LiveAdmin.LoadSound()
};
LiveAdmin.ShowError=function(s)
{
	if(s=='')
		s=LiveAdmin.texts_A100600+"!\n"+LiveAdmin.texts_A100700;
	s=s.replace(/\n/g,'<br/>');
	LiveAdmin.HideAll();
	LiveAdmin.Show('chat_error_screen');
	LiveAdmin.Text('chat_error_text_id',s)
};
LiveAdmin.CheckInitStatus=function(resp)
{
	ar=LiveAdmin.RespToArray(resp);
	if(ar['init_status']==1)
	{
		LiveAdmin.HideAll();
		LiveAdmin.Show('chat_getinfo_screen');
		if(LiveAdmin.texts_A100100&&LiveAdmin.texts_A100100.length>0)
		{
			LiveAdmin.Text('welcome_screen_text_id',LiveAdmin.texts_A100100);
			LiveAdmin.Show('welcome_screen_text_id')
		}
		LiveAdmin.Text('chat_find_rep_text_id',LiveAdmin.texts_A100200);
		LiveAdmin.Text('chat_find_rep_icon_id','<img src="'+LiveAdmin.theme_loadicon+'" />');
		if(LiveAdmin.texts_A100300&&LiveAdmin.texts_A100300.length>0)
		{
			LiveAdmin.Text('no_answer_text_id',LiveAdmin.texts_A100300);
			LiveAdmin.Show('no_answer_text_id')
		}
		LiveAdmin.Hide('title_onhold');
		LiveAdmin.Hide('title_admintype');
		for(var id=0;id<10;id++)
		{
			if(id==0)
				iid='';
			else
				iid='_'+id;
			if(document.getElementById('affiliate_link_id'+iid))
			{
				var alink=document.getElementById('affiliate_link_id'+iid);
				if(typeof(alink.href)!='undefined')
				{
					alink.style.display='none';
					alink.href=LiveAdmin.conf_affiliate_link;
					if(LiveAdmin.conf_show_affiliate_link=='yes')
						alink.style.display=''
				}
			}
		}
		if(ar['user_blocked']=='yes')
		{
			LiveAdmin.ShowBusyMessage();
			return
		}
		if(LiveAdmin.conf_online_status==0)
		{
			switch(LiveAdmin.conf_offline_act)
			{
				case 0:
				break;
				case 1:
				case 3:
					LiveAdmin.ShowBusyMessage();
				break;
				case 2:
					LiveAdmin.TakeMessage();
				break
			}
		}
		if(LiveAdmin.dc=="y")
		{
			LiveAdmin.PostUserInfo();
			return
		}
		/* add check client_nickname here
		 * then determine show nickname form or derictory show loading
		 * if there is client_nickname, directory call PostUserInfo() else just show chat_getinfo_screen
		 */
		if(LiveAdmin.client_nickname)
		{
			LiveAdmin.PostUserInfo();
			return;
		}
	}
};
LiveAdmin.FindRep=function()
{
	if(LiveAdmin.FindRepCancelled)
		return;
	LiveAdminJax({
		'parameters':{'mode':'find_rep','key':LiveAdmin.site_key,'client_uniq':LiveAdmin.client_uniq,'rnd':Math.random()},
		'timeout':10,
		'onSuccess':function(req)
		{
			LiveAdmin.CheckFindStatus(req.responseText)
		},
		'onError':function(req)
		{
			LiveAdmin.ShowError()
		}
	})
};
	
LiveAdmin.CancelFindingRep=function()
{
	LiveAdmin.FindRepCancelled=true;
	switch(LiveAdmin.conf_no_answer_act)
	{
		case 0:
			LiveAdmin.UpdateClientFlag(2);
			LiveAdmin.TakeMessage();
		break;
		case 1:
			LiveAdmin.UpdateClientFlag(5);
			LiveAdmin.ShowBusyMessage();
		break
	}
};
LiveAdmin.UpdateClientFlag=function(flag)
{
	LiveAdminJax({
		'parameters':{'mode':'update_flag','key':LiveAdmin.site_key,'client_uniq':LiveAdmin.client_uniq,'flag':flag,'rnd':Math.random()},
		'timeout':10,
		'onSuccess':function(req){},
		'onError':function(req){}
	})
};
LiveAdmin.CheckFindStatus=function(resp)
{
	if(LiveAdmin.FindRepCancelled)
		return;
	var ar=LiveAdmin.RespToArray(resp);
	LiveAdmin.trig_find_rep=0;
	switch(ar['find_status']*1)
	{
		case 1:
			if(typeof(LiveAdmin.onBeforeShowChatScreen)=='function')
				LiveAdmin.onBeforeShowChatScreen();
			LiveAdmin.server_uniq=ar['server_uniq'];
			LiveAdmin.last_check_msg=ar['last_check'];
			LiveAdmin.server_nickname=ar['server_nickname'];
			LiveAdmin.server_pic=ar['server_pic'];
			LiveAdmin.server_userid=ar['server_userid'];
			LiveAdmin.text_id_client_type=false;
			LiveAdmin.HideAll();
			LiveAdmin.Show('chat_screen');
			document.getElementById('text_id').focus();
			temp_st=LiveAdmin.texts_A100800;
			LiveAdmin.Text('title_text',temp_st.replace(/\%s/,LiveAdmin.server_nickname));
			LiveAdmin.Hide('title_pic_large_img_id');
			if(LiveAdmin.server_pic=='on')
			{
				document.getElementById('title_text').className='title_text_with_pic';
				document.getElementById('title_pic').src=LiveAdmin.conf_chat_url+'?mode=pic&size=small&id='+LiveAdmin.server_userid+'&key='+LiveAdmin.site_key+'&rnd='+Math.random();
				document.getElementById('title_pic_large_img_id').src=LiveAdmin.conf_chat_url+'?mode=pic&size=large&id='+LiveAdmin.server_userid+'&key='+LiveAdmin.site_key+'&rnd='+Math.random();
				LiveAdmin.Show('title_pic')
			}
			else
			{
				LiveAdmin.Hide('title_pic')
			}
			document.getElementById('message_id').innerHTML='';
			LiveAdminQueue.init();
			LiveAdmin.MessageLoopBreak=false;
			LiveAdmin.MessageLoop();
			if(typeof(LiveAdmin.onAfterShowChatScreen)=='function')
				LiveAdmin.onAfterShowChatScreen();
		break;
		case 2:
			LiveAdmin.TakeMessage();
		break;
		case 3:
			LiveAdmin.ShowBusyMessage();
		break;
		default:
			window.setTimeout(LiveAdmin.FindRep,2000);
		break
	}
};
LiveAdmin.TPON=function()
{
	LiveAdmin.Show('title_pic_large_img_id')
};
LiveAdmin.TPOFF=function()
{
	LiveAdmin.Hide('title_pic_large_img_id')
};

LiveAdmin.MessageLoop=function()
{
	if(LiveAdmin.MessageLoopBreak)
		return;
	LiveAdminJax({
		'parameters':{'mode':'message_loop','key':LiveAdmin.site_key,'client_uniq':LiveAdmin.client_uniq,'server_uniq':LiveAdmin.server_uniq,'last_check':LiveAdmin.last_check_msg,'rnd':Math.random()},
		'timeout':1,
		'onSuccess':function(req)
		{
			LiveAdmin.UpdateMessages(req.responseText)
		},
		'onError':function(req)
		{
			window.setTimeout(LiveAdmin.MessageLoop,3000)
		}
	})
};

LiveAdmin.UpdateMessages=function(resp)
{
	var ar=LiveAdmin.RespToArray(resp);
	if(ar['ctl_status']==1)
	{
		LiveAdmin.last_check_msg=ar['ctl_last_check'];
		for(var key in ar)
		{
			if(key.length>0)
			{
				switch(key.substr(0,6))
				{
					case'msg_1_':
					var mdc=Base64.liveadmin_decode(ar[key]);
					var server_ncm=LiveAdmin.server_nickname;
					if(mdc.substr(0,3)=='~u~')
					{
						var server_ncm=mdc.substr(3);
						server_ncm=server_ncm.replace(/~.*$/gi,'');
						mdc=mdc.substr(3).replace(/^[^~]*~/gi,'')
					}
					LiveAdmin.AddMessageToView(server_ncm+" >",mdc,'s2c');
					LiveAdmin.PlaySound(1);
					break;
					case'msg_2_':
					LiveAdmin.AddMessageToView(LiveAdmin.client_nickname+" >",Base64.liveadmin_decode(ar[key]),'c2s');
					break;
					case'msg_4_':
					LiveAdmin.AddMessageToView(LiveAdmin.texts_A102100+" >",LiveAdmin.TranslateText(Base64.liveadmin_decode(ar[key])),'sys');
					break;
					case'msg_6_':
					LiveAdmin.ProcessControlMessage(Base64.liveadmin_decode(ar[key]));
					break
				}
			}
		}
		var flag=ar['ctl_client_flag']*1;
		if(flag==6)
		{
			LiveAdmin.Hide('message_form_id');
			LiveAdmin.Show('message_onhold_id')
		}
		else if(flag==101)
		{
			LiveAdmin.Hide('message_form_id');
			LiveAdmin.Show('message_close_id');
			LiveAdmin.MessageLoopBreak=true
		}
		else
		{
			LiveAdmin.Hide('message_onhold_id');
			LiveAdmin.Hide('message_close_id');
			LiveAdmin.Show('message_form_id')
		}
	}
	window.setTimeout(LiveAdmin.MessageLoop,3000)
};
LiveAdmin.TranslateText=function(m)
{
	if(m.search(/<s5lang/gi)!=-1||m.search(/&lt;s5lang/gi)!=-1)
	{
		m=m.replace(/<s5lang>([^<]*)<\/s5lang>/gi,function(str,p1,offset,s){if(typeof(LiveAdmin['texts_'+p1])!='undefined')return(LiveAdmin['texts_'+p1]);else return(p1)});
		m=m.replace(/&lt;s5lang&gt;([^&]*)&lt;\/s5lang&gt;/gi,function(str,p1,offset,s){if(typeof(LiveAdmin['texts_'+p1])!='undefined')return(LiveAdmin['texts_'+p1]);else return(p1)})
	}
	return(m)
};
LiveAdmin.ProcessControlMessage=function(m)
{
	switch(m)
	{
		case LiveAdmin.CTL_ADMIN_TYPE_ON:
		LiveAdmin.Show('title_admintype');
		break;
		case LiveAdmin.CTL_ADMIN_TYPE_OFF:
		LiveAdmin.Hide('title_admintype');
		break;
		case LiveAdmin.CTL_TRANSFER_DEPT:
		LiveAdmin.FindRepCancelled=false;
		LiveAdmin.MessageLoopBreak=true;
		LiveAdmin.HideAll();
		LiveAdmin.Show('chat_init_screen');
		LiveAdmin.FindRep();
		break
	}
};
LiveAdmin.PostUserInfo=function()
{
	LiveAdmin.HideAll();
	LiveAdmin.Show('chat_init_screen');
	if(LiveAdmin.client_nickname=='')
	{
		LiveAdmin.client_nickname=document.getElementById('client_nickname_id').value;
		if(LiveAdmin.client_nickname=='')
			LiveAdmin.client_nickname=LiveAdmin.texts_A100900;
	}

	var params={
		'mode':'post_user_info',
		'key':LiveAdmin.site_key,
		'client_uniq':LiveAdmin.client_uniq,
		'client_nickname':LiveAdmin.client_nickname,
		'dc':LiveAdmin.dc,
		'rnd':Math.random()
	};
	for(key in LiveAdmin.extra_fields)
	{
		if(document.getElementById('cinfo_'+LiveAdmin.extra_fields[key]+'_id'))
		{
			params['cinfo_'+LiveAdmin.extra_fields[key]]=Base64.liveadmin_encode(document.getElementById('cinfo_'+LiveAdmin.extra_fields[key]+'_id').value)
		}
	}
	if(typeof(LiveAdmin.conf_laef)!='undefined')
	{
		params['laef']=LiveAdmin.conf_laef
	}
	LiveAdminJax({
		'parameters':params,
		'timeout':10,
		'onSuccess':function(req){
			LiveAdmin.PostUserInfoProcess(req.responseText)
		},
		'onError':function(req){
			LiveAdmin.ShowError()
		}
	});
	return false
};
LiveAdmin.PostUserInfoProcess=function(resp)
{
	var ar=LiveAdmin.RespToArray(resp);
	if(ar['post_user_info_status']==1)
	{
		LiveAdmin.HideAll();
		LiveAdmin.Show('chat_init_screen');
		LiveAdmin.FindRep()
	}
};
LiveAdmin.TakeMessage=function()
{
	if(typeof(LiveAdmin.onBeforeShowTakeMessage)=='function')
		LiveAdmin.onBeforeShowTakeMessage();
	LiveAdmin.HideAll();
	LiveAdmin.Show('chat_take_message_screen');
	document.getElementById('tm_name_id').value=document.getElementById('client_nickname_id').value;
	if(typeof(LiveAdmin.onAfterShowTakeMessage)=='function')
		LiveAdmin.onAfterShowTakeMessage()
};
LiveAdmin.TakeMessagePost=function()
{
	LiveAdmin.HideAll();
	LiveAdmin.Text('chat_take_message_wait_text_id',LiveAdmin.texts_A101000);
	LiveAdmin.Show('chat_take_message_wait_screen');
	var tm_name=document.getElementById('tm_name_id').value;
	var tm_email=document.getElementById('tm_email_id').value;
	var tm_text=document.getElementById('tm_text_id').value;
	LiveAdminJax({
		'parameters':{'mode':'post_take_message','key':LiveAdmin.site_key,'client_uniq':LiveAdmin.client_uniq,'tm_name':tm_name,'tm_email':tm_email,'tm_text':tm_text,'rnd':Math.random()},
		'timeout':10,
		'onSuccess':function(req){
			LiveAdmin.TakeMessagePostProcess(req.responseText)
		},
		'onError':function(req){
			LiveAdmin.ShowError()
		}
	})
};
LiveAdmin.TakeMessagePostProcess=function(resp)
{
	var ar=LiveAdmin.RespToArray(resp);
	LiveAdmin.HideAll();
	if(ar['post_take_message_status']==1)
	{
		LiveAdmin.Show('chat_take_message_done_screen');
		LiveAdmin.Text('chat_take_message_done_text_id',LiveAdmin.texts_A101100)
	}
	else
	{
		LiveAdmin.Show('chat_take_message_done_screen');
		LiveAdmin.Text('chat_take_message_done_text_id',LiveAdmin.texts_A101200)
	}
};
LiveAdmin.ShowBusyMessage=function()
{
	if(typeof(LiveAdmin.onBeforeShowBusyMessage)=='function')
		LiveAdmin.onBeforeShowBusyMessage();
	LiveAdmin.HideAll();
	LiveAdmin.Text('chat_take_busy_message_text_id',LiveAdmin.texts_A100400);
	LiveAdmin.Show('chat_take_busy_message_screen');
	if(typeof(LiveAdmin.onAfterShowBusyMessage)=='function')
		LiveAdmin.onAfterShowBusyMessage()
};
LiveAdmin.HideAll=function()
{
	LiveAdmin.Hide('chat_init_screen');
	LiveAdmin.Hide('chat_take_message_wait_screen');
	LiveAdmin.Hide('chat_take_message_done_screen');
	LiveAdmin.Hide('chat_take_message_screen');
	LiveAdmin.Hide('chat_take_message_wait_screen');
	LiveAdmin.Hide('chat_getinfo_screen');
	LiveAdmin.Hide('chat_screen');
	LiveAdmin.Hide('chat_load_screen')
};
LiveAdmin.LoadSound=function()
{
	if(LiveAdmin.conf_soundflash=='')
		return;
	if(!LiveAdminFlash.DetectFlashVer(6,0,0)&&LiveAdmin.flash_install==0)
		return;
	LiveAdminFlash.AC_FL_RunContent('codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0','width','0','height','0','src',LiveAdmin.conf_soundflash,'quality','high','pluginspage','http://www.adobe.com/go/getflashplayer','align','middle','play','true','loop','true','scale','showall','wmode','window','devicefont','false','id','lv_sound_flash','bgcolor','#ffffff','name','lv_sound_app','menu','false','allowFullScreen','false','allowScriptAccess','always','movie',LiveAdmin.conf_soundflash,'salign','')
};
LiveAdmin.PlaySound=function(num)
{
	if(LiveAdmin.conf_soundflash!=''&&document.getElementById('lv_sound_flash')&&typeof(document.getElementById('lv_sound_flash').SetVariable)!='undefined')
	{
		document.getElementById('lv_sound_flash').SetVariable("_root.sound_start_"+num,"on")
	}
};
var LiveAdminQueue=
{
	queue:new Array(),
	AddText:function(o){
		LiveAdminQueue.queue.push(o)
	},
	init:function(){
		window.setTimeout(LiveAdminQueue.run,200)
	},
	run:function(){
		if(LiveAdminQueue.queue.length==0)
			LiveAdminQueue.init();
		else
		{
			LiveAdminQueue.init();
			var c=LiveAdminQueue.queue.shift();
			var lv_param={
				'mode':'message',
				'message':c,
				'key':LiveAdmin.site_key,
				'client_uniq':LiveAdmin.client_uniq,
				'server_uniq':LiveAdmin.server_uniq,
				'rnd':Math.random()
			};
			new Ajax.Request(LiveAdmin.conf_chat_url,{parameters:lv_param,onSuccess:function(req){LiveAdminQueue.init()},onFailure:function(req){LiveAdminQueue.add(lv_param['message']);LiveAdminQueue.init()},onException:function(req){LiveAdminQueue.add(lv_param['message']);LiveAdminQueue.init()}})
		}
	}
};
function LiveAdminJax(args)
{
	var Jax=new Object();
	Jax.xp=this;
	Jax.get=function(){
		Jax.params=args['parameters'];
		Jax.onsuccess=args['onSuccess'];
		Jax.onerror=args['onError'];
		Jax.timeout=args['timeout'];
		if(typeof(Jax.onerror)!="function")
		{
			Jax.onerror=function(req){}
		}
		if(typeof(Jax.onsuccess)!="function")
		{
			Jax.onsuccess=function(req){}
		}
		Jax.trig=0;
		Jax.init()
	};
	Jax.init=function()
	{
		new Ajax.Request(LiveAdmin.conf_chat_url,{
			parameters:Jax.params,
			onSuccess:function(req){Jax.onsuccess(req);Jax.xp.clean()},
			onFailure:function(req){Jax.trig++;if(Jax.trig>Jax.timeout){Jax.onerror(req);Jax.xp.clean()}else{Jax.init_delay()}}
		})
	};
	Jax.init_delay=function(){
		window.setTimeout(this.init,1000)
	};
	clean=function(){
		delete Jax
	};
	Jax.get()
};
var Base64={
	_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	encode:function(input){
		var output="";
		var chr1,chr2,chr3,enc1,enc2,enc3,enc4;
		var i=0;
		input=Base64._utf8_encode(input);
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
			else if(isNaN(chr3))
			{
				enc4=64
			}
			output=output+this._keyStr.charAt(enc1)+this._keyStr.charAt(enc2)+this._keyStr.charAt(enc3)+this._keyStr.charAt(enc4)
		}
		return output
	},
	liveadmin_encode:function(input)
	{
		var t1;
		t1=Base64.encode(input).toString();
		t1=t1.replace(/\=/g,'_E');
		t1=t1.replace(/\+/g,'_P');
		t1=t1.replace(/\//g,'_S');
		return(t1)
	},
	decode:function(input)
	{
		var output="";
		var chr1,chr2,chr3;
		var enc1,enc2,enc3,enc4;
		var i=0;
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
		output=Base64._utf8_decode(output);
		return output
	},
	liveadmin_decode:function(input)
	{
		var t1;
		t1=input;
		t1=t1.replace(/_E/g,"=");
		t1=t1.replace(/_P/g,"+");
		t1=t1.replace(/_S/g,"/");
		t1=Base64.decode(t1);
		return(t1)
	},
	_utf8_encode:function(string)
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
			else if((c>127)&&(c<2048))
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
	},
	_utf8_decode:function(utftext)
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
			else if((c>191)&&(c<224))
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
