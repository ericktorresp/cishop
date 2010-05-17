CRIMS.Main = new Class({
	Extends: CRIMS.Client,
	
	Implements: Options,
	
	options:
	{
		ape:
		{
			identifier: 'crimsgame'
		},
		container: null,
		map:
		{
			width: 1000,
			height: 1000
		},
		width: 800,
		height: 400,
		listener: document,
		pipe: '*crims',
		start:
		{
			x: 400-16,
			y: 200-48
		},
		pas: 2
	},
	
	initialize: function(options)
	{
		this.setOptions(options);
		this.container = $(this.options.container) || document.body;
		this.addEvent('ready', this.ready);
		this.addEvent('userJoin', this.userJoin);
		this.addEvent('userLeft', this.userLeft);
		this.addEvent('multiPipeCreate', this.multiPipeCreate);
		
		window.addEvent('domready', function(){
			this.load({
				'identifier': this.options.ape.identifier,
				'complete': this.complete.bind(this)
			});
		}.bind(this));
	},
	ready: function()
	{
		this.core.join(this.options.pipe);
	},
	multiPipeCreate: function(pipe, data)
	{
		this.pipe = pipe;
		//this.core.request.cycledStack.setTime(1000);
	},
	userJoin: function(user, pipe)
	{
		if(user.pubid == this.core.user.pubid)
		{
			this.els.prompt.fade('out');
			this.started = true;
			this.perso = this.addUnit(
				'/demos/mmo/img/0'+(user.properties.mmo_avatar)+'.png',
				user.pubid,
				null,
				null,
				user.properties['mmo_life']
			);

		}
		else if(user.properties['mmo_life'] > 0)
		{
			var x = null;
			var y = null;
			if(user.properties && user.properties['posx'])
			{
				x = Number(user.properties['posx']);
				y = Number(user.properties['posy']);
			}
			this.addUnit('/demos/mmo/img/0'+(user.properties.mmo_avatar)+'.png', user.pubid, x, y,user.properties['mmo_life']);
		}
		
	},
	userLeft: function(user, pipe)
	{
		this.removeUnit(user.pubid);
	},
	
	// LOG //
	info: function(txt, clas, delay)
	{
		delay = delay || 1000;
		var info = new Element('div', {
			text: txt,
			'class': clas
		});
		this.els.info.grab(info);

		var f = function(el)
		{
			el.fade('out');
			el.destroy.delay(delay, el);
		};
		f.delay(5000, this, info);
	},
	error: function(txt, delay)
	{
		this.info(txt, 'error', delay);
	},
	// NETWORK //

	send: function(cmd, params, addpos){
		if(this.pipe)
		{
			if(addpos)
			{
				params.pos = {
					x: this.x,
					y: this.y
				};
			}
			//console.log('Sending', this.pipe, cmd, params);
			this.pipe.request.send(cmd, params);
		}
		else
		{
			//console.log('Sending without pipe');
		}
	},
	sendStart: function()
	{
		this.send('mmo_start',
			{ dir: this.perso.dir },
			true
		);
	},
	rawStart: function(params, pipe)
	{
		if(params.data.user.pubid == this.core.user.pubid)
			return;

		var unit = this.units.get(params.data.user.pubid);
		unit.x = params.data.pos.x;
		unit.y = params.data.pos.y;
		unit.rotate(params.data.dir);
		unit.play();
	},
	rawStop: function(params, pipe)
	{
		if(params.data.user.pubid == this.core.user.pubid)
			return;

		var unit = this.units.get(params.data.user.pubid);
		unit.x = params.data.pos.x;
		unit.y = params.data.pos.y;
		unit.stop();
	},
	rawUpdate: function(params, pipe)
	{
		if(params.data.user.pubid == this.core.user.pubid)
			return;

		var unit = this.units.get(params.data.user.pubid);
		unit.x = params.data.pos.x;
		unit.y = params.data.pos.y;
		//unit.rotate(params.data.dir);
		//unit.play();
	}
});