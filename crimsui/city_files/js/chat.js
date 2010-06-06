/**
 * 2010/6/1 16:33
 * 初始加入9+1个频道： crims，9个街区
 * createChat 之后，默认 “loading...”
 * createUser 时，判断是当前用户则载入9个街区地图
 * 
 **/

CRIMS.Crims = new Class({
	Extends: CRIMS.Client, 
	Implements: Options,
	options:{
		container: null,
		logs_limit: 10,
		current_street: {
			x: 0,
			y: 0
		},
		center_point: {
			x: 0,
			y: 0
		},
		street: {
			width: 614,
			height: 374,
			total: 9
		},
		main_channel: 'crims',
		identifier: 'crims',
		debug: true
	},
	initialize: function(options){
		this.setOptions(options);
		this.els = {};
		this.currentPipe = null;
		this.logging = true;

		this.onRaw('MAP_DATA', this.setMap);
		this.onRaw('data', this.rawData);
		this.onCmd('LOADMAP', function(data,pipe){
			this.debug(data,'[LOAD MAP]');
		});
		this.onCmd('send', this.cmdSend);
		
		this.onError('004', this.reset);
		this.onError('006', this.promptName);
		this.onError('007', this.promptName);
		this.onError('206', this.promptName);

		this.addEvent('load', this.start);
		this.addEvent('ready', this.createChat);
		this.addEvent('uniPipeCreate', this.setPipeName);
		this.addEvent('uniPipeCreate', this.createPipe);
		this.addEvent('multiPipeCreate', this.createPipe);
		this.addEvent('userJoin', this.createUser);
		this.addEvent('userLeft', this.deleteUser);
		if(this.options.debug)
		{
			this.els.debug = {};
			this.els.debug.div = new Element(
				'div', {'class': 'ape_debug', 'id': 'debug', 'text': '调试信息: '}
			).inject($(document.body));
			//this.addEvent('onCmd', function(cmd){this.debug(cmd,'[SEND]')});
			//this.addEvent('onRaw', function(args){this.debug(args.raw,'[RECEIVE]')});
		}
		window.addEvent('domready', function(){
			this.load({
				'identifier': this.options.identifier,
				//装载街区数+crims = 10 channels?
				'channel': ['street_0_0',this.options.main_channel]
			});
		}.bind(this));
	},
	debug: function(obj,type)
	{
		if(this.options.debug)
		{
			new Element(
				'div', {'html': '<font color=red><b>'+type+'</b></font> '+JSON.encode(obj)}
			).inject(this.els.debug.div);
		}
	},
	promptName: function(errorRaw)
	{
		this.els.namePrompt = {};
		this.els.namePrompt.div = new Element(
			'form', {'class':'ape_name_prompt','text':'用户名: '}
		).inject(this.options.container);
		this.els.namePrompt.div.addEvent('submit',function(ev)
		{
			ev.stop();
			this.options.username = this.els.namePrompt.username.get('value');
			this.options.password = this.els.namePrompt.password.get('value');
			this.els.namePrompt.div.dispose();
			this.start()
		}.bindWithEvent(this));
		this.els.namePrompt.username = new Element('input',{'class':'text'}).inject(this.els.namePrompt.div);
		new Element('span',{'text':'密码: '}).inject(this.els.namePrompt.div);
		this.els.namePrompt.password = new Element('input',{'type':'password','class':'text'}).inject(this.els.namePrompt.div);
		new Element('input',{'class':'submit','type':'submit','value':'GO!'}).inject(this.els.namePrompt.div);
		var error;
		if (errorRaw)
		{
			if (errorRaw.data.code == 007)
			{
				error = '这个昵称已经被使用';
			}
			if (errorRaw.data.code == 006)
			{
				error = '非法昵称，必须包含 a-z 0-9 字符';
			}
			if(errorRaw.data.code == 206)
			{
				error = '登录失败';
			}
			if (error)
			{
				new Element('div', {'styles':{'padding-top': 5, 'font-weight': 'bold'},'text': error}).inject(this.els.namePrompt.div);
			}
		}
	},
	_calculateWH: function()
	{
		var b=window.getSize().y;
		var c=$('header').getCoordinates();
		b-=(c.bottom-c.top);
		if(Browser.Engine.gecko||Browser.Engine.trident)
		{
			b+=$('footer').getStyle('padding-top').toInt();
		}
		var a=window.getSize().x-(Browser.Engine.webkit?15:0)-1;
		return{width:a,height:b}
	},
	start: function()
	{
		// If name is not set & it's not a session restore ask user for his nickname
		//if((!this.options.username || !this.options.password) && !this.core.options.restore)
		//{
			//this.promptName();
		//}
		//else
		//{
			var opt = {'sendStack': false, 'request': 'stack'};

			this.core.start({'username':this.options.username||'guest'+$random(1,99999),'password':this.options.password||''}, opt);

			if (this.core.options.restore)
			{
				this.core.getSession('currentPipe', function(resp)
				{
					this.setCurrentPipe(resp.data.sessions.currentPipe);
				}.bind(this), opt);
			}
			this.core.request.stack.send();
		//}
	},
	setPipeName: function(pipe, options)
	{
		if (options.name)
		{
			pipe.name = options.name;
			return;
		}
		if (options.from)
		{
			pipe.name = options.from.properties.name;
		}
		else
		{
			pipe.name = options.pipe.properties.name;
		}
	},
	getCurrentPipe: function()
	{
		return this.currentPipe;
	},
	setCurrentPipe: function(pubid, save)
	{
		save = !save;
		if (this.currentPipe)
		{
			this.currentPipe.els.tab.addClass('unactive');
			this.currentPipe.els.container.addClass('ape_none');
		}
		this.currentPipe = this.core.getPipe(pubid);
		this.currentPipe.els.tab.removeClass('new_message');
		this.currentPipe.els.tab.removeClass('unactive');
		this.currentPipe.els.container.removeClass('ape_none');
		this.scrollMsg(this.currentPipe);
		if (save)
		{
			this.core.setSession({'currentPipe':this.currentPipe.getPubid()});
		}
		return this.currentPipe;
	},
	cmdSend: function(data, pipe)
	{
		this.writeMessage(pipe, data.msg, this.core.user);
	},
	rawData: function(raw, pipe)
	{
		this.writeMessage(pipe, raw.data.msg, raw.data.from);
	},
	parseMessage: function(message)
	{
		return decodeURIComponent(message);
	},
	notify: function(pipe)
	{
		pipe.els.tab.addClass('new_message');
	},
	scrollMsg: function(pipe)
	{
		var scrollSize = pipe.els.message.getScrollSize();
		pipe.els.message.scrollTo(0,scrollSize.y);
	},
	writeMessage: function(pipe, message, from)
	{
		//Append message to last message
		if(pipe.lastMsg && pipe.lastMsg.from.pubid == from.pubid)
		{
			var cnt = pipe.lastMsg.el;
		}
		else
		{//Create new one
			//Create message container
			var msg = new Element('div',{'class':'ape_message_container'});
			var cnt = new Element('div',{'class':'msg_top'}).inject(msg);
			if (from)
			{
				new Element('div',{'class':'ape_user','text':from.properties.username}).inject(msg,'top');
			}
			new Element('div',{'class':'msg_bot'}).inject(msg);
			msg.inject(pipe.els.message);
		}
		new Element('div',{
			'text':this.parseMessage(message),
			'class':'ape_message'
		}).inject(cnt);

		this.scrollMsg(pipe);

		pipe.lastMsg = {from:from,el:cnt};

		//notify 
		if(this.getCurrentPipe().getPubid()!=pipe.getPubid())
		{
			this.notify(pipe);
		}
	},
	createUser: function(user, pipe)
	{
		//加入在线用户列表 DOM
		user.el = new Element('div',{
			'class':'ape_user'
		}).inject(pipe.els.users);
		new Element('a',{
				'text':user.properties.username,
				'href':'javascript:void(0)',
				'events': 
				{
					'click': function(ev,user)
					{
						this.core.getPipe(user.pubid);
						this.setCurrentPipe(user.pubid);
					}.bindWithEvent(this,[user])
				}
		}).inject(user.el,'inside');
	},
	deleteUser: function(user, pipe)
	{
		user.el.dispose();
	},
	createPipe: function(pipe, options)
	{
		var tmp;
		//Define some pipe variables to handle logging and pipe elements
		pipe.els = {};
		pipe.logs = new Array();
		//Container
		pipe.els.container = new Element('div',{
			'class':'ape_pipe ape_none '
		}).inject(this.els.pipeContainer);
		//Message container
		pipe.els.message = new Element('div',{'class':'ape_messages'}).inject(pipe.els.container,'inside');
		//If pipe has a users list 
		if(pipe.users)
		{
			pipe.els.usersRight = new Element('div',{
				'class':'users_right'
			}).inject(pipe.els.container);
			pipe.els.users = new Element('div',{
				'class':'ape_users_list'
		    }).inject(pipe.els.usersRight);;
		}
		//Add tab
		pipe.els.tab = new Element('div',{
			'class':'ape_tab unactive'
		}).inject(this.els.tabs);
		tmp = new Element('a',{
			'text':pipe.name,
			'href':'javascript:void(0)',
			'events':{
				'click':function(pipe)
				{
						this.setCurrentPipe(pipe.getPubid())
				}.bind(this,[pipe])
			}
		}).inject(pipe.els.tab);
		//Hide other pipe and show this one
		this.setCurrentPipe(pipe.getPubid());
	},
	createChat: function()
	{
		this.els.pipeContainer = new Element('div',{'id':'map'});
		var WH = this._calculateWH();
		this.els.pipeContainer.setStyles({
			position: 'relative',
			left: '0px',
			top: '0px',
			width: WH.width<980?980:WH.width,
			height: WH.height<376?376:WH.height,
			overflow:'hidden',
			cursor: 'pointer',
			outline: 'none'
		}).setProperties({
			tabindex: '2',
			hidefocus: true
		});

		this.els.map = new Element('div',{
			'id': 'dragable', 
			'style': '-moz-user-select: none;z-index: 0;'
		});
		this.els.map.setStyles({
			position: 'absolute',
			width: this.options.street.width*3,
			height: this.options.street.height*3
		}).inject(this.els.pipeContainer);

		this.els.pipeContainer.inject(this.options.container);


		this.els.more = new Element('div',{'id':'more'}).inject(this.options.container,'after');
		this.els.tabs = new Element('div',{'id':'tabbox_container'}).inject(this.els.more);
		this.els.sendboxContainer = new Element('div',{'id':'ape_sendbox_container'}).inject(this.els.more);

		this.els.sendBox = new Element('div',{'id':'ape_sendbox'}).inject(this.els.sendboxContainer,'bottom');
		this.els.sendboxForm = new Element('form',{
			'events':{
				'submit':function(ev)
				{
					ev.stop();
					var val = this.els.sendbox.get('value');
					if(val!='')
					{
						this.getCurrentPipe().send(val);
						this.els.sendbox.set('value','');
					}
				}.bindWithEvent(this)
			}
		}).inject(this.els.sendBox);
		this.els.sendbox = new Element('input',{
			'type':'text',
			'id':'sendbox_input',
			'autocomplete':'off'
		}).inject(this.els.sendboxForm);
		this.els.send_button = new Element('input',{
			'type':'submit',
			'id':'sendbox_button',
			'value':''
		}).inject(this.els.sendboxForm);

		this.loadMap();
	},
	loadMap: function()
	{
		this.core.request.send('LOADMAP',{x:0,y:0,z:0});
	},
	setMap: function(raw, pipe)
	{
		this.debug(raw,'[MAP]');
		var WH = this._calculateWH();
		for(var d=0;d<raw.data.length; d++)
		{
			rows = Math.ceil((d+1)/3);
			colums = (d+1)%3;
			switch(rows)
			{
				case 1:
					p_top = parseInt(WH.height/2-this.options.street.height/2*3);
					break;
				case 2:
					p_top = parseInt(WH.height/2-this.options.street.height/2);
					break;
				case 3:
					p_top = parseInt(WH.height/2+this.options.street.height/2);
					break;
			}
			switch(colums)
			{
				case 1:
					p_left = parseInt(WH.width/2-this.options.street.width/2*3);
					break;
				case 2:
					p_left = parseInt(WH.width/2-this.options.street.width/2);
					break;
				case 0:
					p_left = parseInt(WH.width/2+this.options.street.width/2);
					break;
			}
			new Element('div',{
				'id': raw.data[d].x + '~' + raw.data[d].y,
				'class': 'city-map'
			}).setStyles({
				'top': p_top,
				'left': p_left,
				'-moz-user-select': 'none',
				'position':'absolute'
			}).inject(this.els.map);
		}
	},
	reset: function()
	{
		this.core.clearSession();
		if(this.els.pipeContainer)
		{
			this.els.pipeContainer.dispose();
			this.els.more.dispose();
		}
		this.core.initialize(this.core.options);
	}
});
