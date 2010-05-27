
APE.MmoClient = new Class({
	Extends: APE.Client,
	Implements: Options,
	options: {
		map_id: 'map',
		listener: document,
		identifier: 'apemmo',
		pipe: 'mmo',
		startx: 384,
		starty: 200
	},
	element:null,
	map: {
		el: null,
		ground: null,
		info: null,
		x: 0,
		y: 0,
		width: 1000,
		height: 1000
	},
	ground: null,
	perso: null,
	users: new Hash(),
	keys: {
		'up': {},
		'left':{},
		'right':{},
		'down':{},
		'f2':{},
		'f3':{},
		'enter':{},
		'tab':{}
	},
	pas: 4,
	timer: null,
	pipe: null,
	interval: 50,
	walking: false,
	selected: false,
	chat: {
		visible: false,
		zone: null,
		input: null,
		btn: null,
		msgs: null,
		main: null
	},
	messages: 0,
	initialize: function(element, options){
		this.setOptions(options);
		this.map.x = this.options.startx;
		this.map.y = this.options.starty;
		window.addEvent('domready', function(){
			this.element = $(element);
			this.load({
				'domain': APE.Config.domain,
				'server': APE.Config.server,
				'identifier': this.options.identifier,
				'scripts': APE.Config.scripts,
				'complete': this.complete.bind(this)
			});
		}.bind(this));

		Ge.preload('/demos/mmo/img/skill1.png')
		Ge.preload('/demos/mmo/img/skill2.png')

		//## RAWS ##//
		this.addEvent('init', this.initPlayground);
		this.addEvent('userJoin', this.addPlayer);
		this.addEvent('userLeft', this.removePlayer);
		this.addEvent('pipeCreate', this.pipeCreate);
		this.onRaw('data', this.rawData);

		//## KEYS ##//
		this.options.listener.addEvent('keydown', this.keyDown.bind(this));
		this.options.listener.addEvent('keyup', this.keyUp.bind(this));
	},
	initPlayground: function(){
		console.log('NewCanvas');
		this.persoCanvas = new Element('canvas',{
			id: 'my-perso',
			width: 32,
			height: 48
		});
		this.perso = new Ge.Unit('/demos/mmo/img/0'+(Math.ceil(Math.random()*7))+'.png', this.persoCanvas, {id:''});
		this.map.ground = new Element('canvas', {
			id: this.options.map_id,
			width: this.map.width,
			height: this.map.height
		})
		this.map.el = new Element('div',{id:this.options.map_id});

		this.map.el.grab(this.map.ground);
		this.map.el.grab(this.persoCanvas);
		this.element.grab(this.map.el);

		this.map.info = new Element('div', {class:'info'});
		this.map.el.grab(this.map.info);

		this.core.join(this.options.pipe);

		this.map.ground.set('morph', {duration: this.interval*1.1,link:'cancel'});
		this.updateMapPos();
		this.addMonster(500,500);

		this.chat.main = new Element('div',{
			class: 'chat'
		});
		this.chat.msgs = new Element('div',{
			class:'messages'
		});
		this.chat.input = new Element('input',{
			type:'text'
		});
		this.chat.zone = new Element('div',{
			'style':'display:none'
		})
		this.chat.zone.grab(this.chat.input);
		this.chat.main.adopt(this.chat.msgs, this.chat.zone);
		
		this.map.el.grab(this.chat.main);
	},
	stopIncant: function(){
		if(this.incantTimeout){
			this.perso.stop();
			clearTimeout(this.incantTimeout);
		}
	},
	startIncant: function(perso){
		perso.rotate('s'+perso.dir);
		perso.walk();
	},
	fireSpell: function(spell){
		if(!this.selected){
			this.error('Target lost');
			return;
		}
		var dir = this.perso.dir.substr(0,1)=='s'?this.perso.dir.substr(1):this.perso.dir;
		
		this.pipe.send("{'act':'"+(spell==1?'fire':'thunder')+"','target':'"+this.selected+"'}");

		this.perso.rotate(dir);
		this.perso.stop();
		this.spellOn(spell, this.selected);
	},
	spellOn: function(spell, target){
		if(target == this.core.user.pubid){
			this.spellAt(spell, this.map.x+16, this.map.y+49);
		}else{
			var unit = this.unit.get(target);
			if(unit){
				var x = unit.x + map.x;
				var y = unit.y + map.y;
				this.spellAt(spell, x ,y);
			}
		}
	},
	spellAt: function(spell, x, y){
		new Ge.Spell(spell, x, y, this.els.canvas);
	},
	complete: function(){
		this.core.start();
	},
	tick: function(){
		if(this.walking){
			var add = this.parseDir(this.perso.dir);

			this.map.x += Math.round(add.x*this.pas*1.25);
			this.map.x	= Math.min(this.map.width-32,Math.max(0, this.map.x));
			this.map.y += Math.round(add.y*this.pas);
			this.map.y  = Math.min(this.map.height-32,Math.max(0, this.map.y));
			this.updateMapPos();
		}
		this.users.each(function(user, pubid){

			if(user.walking){
				var add  = this.parseDir(user.dir);
				user.x  += Math.round(add.x*this.pas*1.25);
				user.y  += Math.round(add.y*this.pas);

				this.moveUserTo(user, user.x+add.x, user.y+add.y);
			}

		}.bind(this));
	},
	moveUserTo: function(user, x, y){

		x = Math.max(x,0);
		y = Math.max(y,0);
		x = Math.min(x,this.map.width-32);
		y = Math.min(y,this.map.height-32);

		user.move(x, y);
	},
	parseDir: function(dir){
		var addx = 0;
		var addy = 0;
		switch(dir){
			case 'right':
				addx = 1;
				break;
			case 'left':
				addx = -1;
				break;
			case 'up':
				addy = -1;
				break;
			case 'down':
				addy = 1;
				break;
		}
		return {x:addx,y:addy};
	},
	updateMapPos: function(){
		this.map.ground.morph({
			left: -(this.map.x-384)+'px',
			top: -(this.map.y-200)+'px'
		});

		//this.map.el.setStyle('background-position', (-this.map.x)+'px '+(-this.map.y)+'px');
	},
	info: function(txt, class){
		var info = new Element('div', {
			text: txt,
			class: class
		});
		this.map.info.grab(info);

		var f = function(el){
			el.fade('out');
			el.destroy.delay(1000, el);
		}
		f.delay(5000, this, info);
	},
	error: function(txt){
		this.info(txt, 'error');
	},
	keyUp: function(ev){
		var key = ev.key;
		if(!this.keys[key]) return;
		
		this.keys[key].down = false;
		if(key == this.perso.dir){
			var newdir = '';
			var max = 0;
			for(var i in this.keys){
				if(this.keys[i].down > max){
					max = this.keys[i].down;
					newdir = i;
				}
			}
			if(newdir != ''){
				this.perso.rotate(newdir);
				this.pipe.send("{'act':'start','pos':{'x':"+this.map.x+",'y':"+this.map.y+"},'dir':'"+this.perso.dir+"'}");
			}else{
				this.perso.stop();
				this.walking = false;
				this.pipe.send("{'act':'stop','pos':{'x':"+this.map.x+",'y':"+this.map.y+"}}");
			}
		}
	},
	addMessage: function(nick, msg){

		if(++this.messages > 8){
			this.chat.msgs.getElement('div').destroy();
		}

		var line = new Element('div',{
			'html': '<span class="nick">'+nick+'</span>: '+msg
		});
		this.chat.msgs.grab(line);
	},
	keyDown: function(ev){
		var key = ev.key;
		if(!this.keys[key]) return;
		ev.stop();
		if(key == 'tab'){
			var keys = this.users.getKeys();
			if(!this.selected && keys.length > 1){
				this.selectUnit(keys[1]);
			}else{
				for(var i=0;i<keys.length;i++){
					if(keys[i] == this.selected){
						i++;
						break;
					}
				}
				if(i >= keys.length) i = 0;
				this.selectUnit(keys[i]);

			}
		}else if(key == 'enter'){
			if(this.chat.visible){
				var val = this.chat.input.get('value')
				if(val != ''){
					this.pipe.send("{'act':'chat','msg':'"+escape(val)+"'}");
					this.addMessage('me', val);
					this.chat.input.set('value','');
				}else{
					this.chat.visible = false;
					this.chat.zone.setStyle('display', 'none');
				}
			}else{
				this.chat.visible = true;
				this.chat.zone.setStyle('display', 'block');
				this.chat.input.focus();
			}
		}else if(key=='f2' || key=='f3'){
			if(!this.selected) return this.error('Please select a target');

			this.stopIncant();
			this.perso.stop();
			this.walking = false;

			switch(key){
				case 'f2':
					this.startIncant(this.perso);
					this.pipe.send("{'act':'incant','pos':{'x':"+this.map.x+",'y':"+this.map.y+"}}");
					this.incantTimeout = setTimeout(this.fireSpell.bind(this), 3000, 1);
					break;
				case 'f3':
					this.fireSpell(2);
					break;
			}
		}else if(!this.keys[key].down && this.perso.rotate(key)){
			this.stopIncant();
			this.keys[key].down = new Date().getTime();
			this.perso.walk();
			this.walking = true;
			//this.pipe.request.send('mmo_start', {pos:{x:this.map.x,y:this.map.y},dir:this.perso.dir});
			this.pipe.send("{'act':'start','pos':{'x':"+this.map.x+",'y':"+this.map.y+"},'dir':'"+this.perso.dir+"'}");
		}
	},
	removePlayer: function(user, pipe){
		if(this.selected == user.pubid){
			this.selected = false;
			this.stopIncant();
		}
		this.users.erase(user.pubid);
	},
	addPlayer: function(user, pipe){
		if(user.pubid == this.core.user.pubid) return;

		var unit = new Ge.Unit(
			'/demos/mmo/img/0'+(Math.ceil(Math.random()*7))+'.png',
			this.persoCanvas,
			{id:user.pubid, class:'unit'}
		);
		unit.addEvent('click', this.unitClick.bind(this));

		this.users.set(user.pubid, unit);
		this.moveUserTo(unit, this.options.startx, this.options.starty);
	},
	addMonster: function(x,y){
		var mstr = new Ge.Unit(
			'/demos/mmo/img/creep1.png',
			this.map.ground,
			{id:'creep1', class:'unit'}
		);
		mstr.addEvent('click', this.unitClick.bind(this));

		this.moveUserTo(mstr, x, y);
		this.users.set('creep_'+x+'_'+y, mstr);
	},
	selectUnit: function(id){
		if(this.selected){
			this.users.get(this.selected).unselect();
			this.stopIncant();
		}
		if(this.selected == id) this.selected = false;
		else{
			var user = this.users.get(id);
			user.select();
			this.selected = id;
		}
	},
	unitClick: function(ev){
		var id = ev.target.getParent().id;
		this.selectUnit(id);
	},
	pipeCreate: function(type, pipe, data){
		this.pipe = pipe;
	},
	rawData: function(raw, pipe){
		var rcv = JSON.decode(unescape(raw.data.msg));
		var user = this.users.get(raw.data.sender.pubid);
		switch(rcv.act){
			case 'start':
				this.moveUserTo(user, rcv.pos.x, rcv.pos.y);
				user.unit.rotate(rcv.dir);
				user.dir = rcv.dir;
				user.walking = true;
				user.unit.walk();
				break;
			case 'stop':
				user.walking = false;
				user.unit.stop();
				this.moveUserTo(user, rcv.pos.x, rcv.pos.y);
				break;
			case 'incant':
				this.startIncant(user.unit);
				user.walking = false;
				this.moveUserTo(user, rcv.pos.x, rcv.pos.y);
				break;
			case 'fire':
				user.unit.stop();
				user.unit.rotate(user.dir);
				if(rcv.target == this.core.user.pubid) this.info('A player make fire on you', 'fight');
				this.spellOn(1,rcv.target);
				break;
			case 'thunder':
				user.unit.stop();
				if(rcv.target == this.core.user.pubid) this.info('A player make thunder on you', 'fight');
				this.spellOn(2,rcv.target);
				break;
			case 'chat':
				this.addMessage('player', unescape(rcv.msg));
				break;
			default:
				console.log('Unknow commande', raw.data.msg, rcv);
		}
	}
});