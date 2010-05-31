/**
 * 2010/5/31 13:52
 * @todo 保证页面装载后只载入一次地图，目前当有新用户进来就重新请求一次。
 * @todo 地图数据返回格式：一个街区一个数组，包含座标，建筑位置、可用菜单，NPC位置、可用菜单
 * useage: 
 * load(Client js) -> start(CONNECT TO SERVER) -> ready(CONNECTED)
 */
CRIMS.Crims = new Class({
	Extends: CRIMS.Client, 
	Implements: Options,
	options:{
		container: null,
		logs_limit: 10,
		current_position: {
			x: 0,
			y: 0
		},
		pic_size: {
			width: 614,
			height: 374
		},
		identifier: 'crims',
		channel: 'crims',
		debug: true
	},

	initialize: function(options)
	{
		//Ge.preload('city_files/city/citymap.gif');
		//Ge.preload('city_files/map/objects.gif');
		
		this.setOptions(options);
		this.els = {};
		this.currentPipe = null;
		this.logging = true;

		this.onRaw('data', this.rawData);
		this.onCmd('send', this.cmdSend);
		this.onCmd('LOADMAP', this.cmdMap);
		this.onRaw('MAP_DATA', this.rawMap);
		
		this.onError('004', this.reset);					//BAD_SESSID
		this.onError('006', this.promptName);	//BAD_NICK
		this.onError('007', this.promptName);	//NICK_USED
		this.onError('206', this.promptName);	//LOGIN_FAILD

		this.addEvent('load', this.start);
		this.addEvent('ready', this.createMap);
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
		}
	},
	
	debug: function(obj,type)
	{
		if(this.options.debug)
		{
			new Element(
				'div', {'text': type+' '+JSON.encode(obj)}
			).inject(this.els.debug.div);
		}
	},

	calculateWH: function()
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
	
	promptName: function(errorRaw)
	{
		this.els.namePrompt = {};
		this.els.namePrompt.div = new Element(
			'form', {'class':'ape_name_prompt','text':'选择用户名: '}
		).inject(this.options.container);
		this.els.namePrompt.div.addEvent('submit',function(ev)
		{
			ev.stop();
			this.options.name = this.els.namePrompt.username.get('value');
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

	start: function()
	{
		var opt = {'sendStack': false, 'request': 'stack'};
		this.core.start({}, opt);
		if (this.core.options.restore)
		{
			this.core.getSession('currentPipe', function(resp)
			{
				this.setCurrentPipe(resp.data.sessions.currentPipe);
			}.bind(this), opt);
		}
		this.core.request.stack.send();
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
			pipe.name = options.from.properties.username;
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
		this.currentPipe = this.core.getPipe(pubid);
		if (save)
		{
			this.core.setSession({'currentPipe':this.currentPipe.getPubid()});
		}
		return this.currentPipe;
	},

	cmdSend: function(data, pipe)
	{
		this.debug(data,'[SEND]');
		this.writeMessage(pipe, data.msg, this.core.user);
	},

	rawData: function(raw, pipe)
	{
		this.debug(raw,'[RECEIVE]')
		this.writeMessage(pipe, raw.data.msg, raw.data.from);
	},

	parseMessage: function(message)
	{
		return decodeURIComponent(message);
	},

	createUser: function(user, pipe)
	{
		// this.core.getPipe(user.pubid);
		//载入地图
		pipe.request.send('LOADMAP',{x:this.options.current_position.x,y:this.options.current_position.y});
	},

	deleteUser: function(user, pipe)
	{
		//user.el.dispose();
	},

	createPipe: function(pipe, options)
	{
		this.setCurrentPipe(pipe.getPubid());
	},

	createMap: function()
	{
		var WH = this.calculateWH();
		this.els.mapContainer = new Element('div',{'id':'map'});
		this.els.mapContainer.setStyles({
			position: 'relative',
			left: '0px',
			top: '0px',
			width: WH.width<980?980:WH.width,
			height: WH.height<376?376:WH.height,
			overflow:'hidden',
			cursor: 'pointer'
		}).setProperty('tabindex','2');
		
		this.els.map = new Element('div',{
			'id': 'dragable', 
			'style': '-moz-user-select: none;z-index: 0;'
		});
		this.els.map.setStyles({
			position: 'absolute',
			width: this.options.pic_size.width*3,
			height: this.options.pic_size.height*3
		});
		this.els.map.inject(this.els.mapContainer);
		this.els.mapContainer.inject(this.options.container);
	},

	cmdMap: function(data,pipe)
	{
		this.debug(data, '[LOAD MAP]');
	},

	rawMap: function(raw, pipe)
	{
		this.debug(raw.data,'[RECEIVE MAP]')
	},

	reset: function()
	{
		this.core.clearSession();
		this.core.initialize(this.core.options);
	}
});
