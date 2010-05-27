/*
 * CRIMS MAP
 */
function _get_width_height()
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
}
CRIMS.MAP = new Class({
	Extends: CRIMS.Client,
	Implements: Options,
	options: 
	{
		identifier: 'crimsmap',
		pipe: '*crims',
		container: 'map',
		width: 614,
		height: 374
	},
	els: {},
	initialize: function(target)
	{
		this.els.target = target;
		this.container = $(this.options.container) || document.body;
		window.addEvent('domready', function(){
			this.load({
				identifier: this.options.identifier,
				channel: this.options.pipe
			});
			this._init_map();
			this._make_dragable();
		}.bind(this));
		window.addEvent('resize', function(){
			this._resize_map();
		}.bind(this));
		this.addEvent('load',this.start);
		this.addEvent('userJoin', this.userJoin);
		this.addEvent('userLeft', this.userLeft);
		this.addEvent('multiPipeCreate', this.multiPipeCreate);
	},
	start: function()
	{
		if (this.core.options.restore)
		{
			this.core.start();
		}
		else
		{
			this.core.start({'name':prompt('name?')});
		}
		//this.core.start({'name': $time().toString()});
	},
	userJoin: function(user, pipe){
		//if(user.pubid == this.core.user.pubid)
		//{
			//this.els.prompt.fade('out');
			//this.started = true;
			//this.perso = this.addUnit(
				//'/demos/mmo/img/0'+(user.properties.mmo_avatar)+'.png',
				//user.pubid,
				//null,
				//null,
				//user.properties['mmo_life']
			//);

		//}
		//else if(user.properties['mmo_life'] > 0)
		//{
			//var x = null
			//var y = null;
			//if(user.properties && user.properties['posx'])
			//{
				//x = Number(user.properties['posx']);
				//y = Number(user.properties['posy']);
			//}
			//this.addUnit('/demos/mmo/img/0'+(user.properties.mmo_avatar)+'.png', user.pubid, x, y,user.properties['mmo_life']);
		//}
	},
	userLeft: function(user, pipe){
		//this.removeUnit(user.pubid);
	},
	multiPipeCreate: function(pipe, data){
		this.pipe = pipe;
	},
	_init_map: function()
	{
		/**
		 * 这里应该由服务器传递回来。在加载完毕之前，只出现进度条
		 * 
		 */
		var c=_get_width_height();
		var b=c.height;
		var a=c.width;

		$(this.container).setStyles({
			position: 'relative',
			left: '0px',
			top: '0px',
			width: a<980?980:a,
			height: b<376?376:b,
			overflow:'hidden'/*,
			cursor: 'pointer'*/
		}).setProperty('tabindex','2');
		$('dragable').setStyles({
			width: this.options.width*3,
			height: this.options.height*3,
			//left: -(this.options.width/2)
		});
	},
	_resize_map: function()
	{
		var c=_get_width_height();
		var b=c.height;
		var a=c.width;

		$(this.container).setStyles({
			width: a<980?980:a,
			height: b<376?376:b
		});
	},
	_make_dragable: function()
	{
		new Drag.Move($("dragable"), { 
			onDrop: function()
			{
				/**
				 * here capture the map position
				 * 左、上侧计算：$('dragable').getElements('div')[0].getPosition('map');
				 * 右、下侧计算：$('dragable').getElements('div')[$('dragable').getElements('div').length-1].getPosition('map');
				 * 地图预先载入，只需要装载座标对应用户的建筑等信息，座标服务器端计算
				 */
				var divs = $('dragable').getChildren('div');
				var tl = divs[0].getPosition('map');
				var rb = $('dragable').getCoordinates('map');
				var c = _get_width_height();
				var orientation = '';
				var coodr = '';
				if(tl.x>0)
				{
					orientation += 'left';
					coodr = divs[0].id;
				}
				if(tl.y>0)
				{
					orientation += 'top';
					coodr = divs[0].id;
				}
				if(rb.right<c.width)
				{
					orientation += 'right';
					coodr = divs[8].id;
				}
				if(rb.bottom<c.height)
				{
					orientation += 'bottom';
					coodr = divs[8].id;
				}
				console.log(orientation,coodr);
			}
		});
	}
});
