CRIMS.Map = new Class({
	
	Extends: CRIMS.Client,
	
	Implements: Options,
	
	options: 
	{
		container: 'map'
	},
	initialize: function(options)
	{
		this.setOptions(options);
		this.container = $(this.options.container) || document.body;
		this._init_map();
		this._make_dragable();
		this.onRaw('postmsg', this.onMsg);
		this.addEvent('load',this.start);
	},
	start: function(core)
	{
		this.core.start({'name': $time().toString()});
	},
	onMsg: function(raw)
	{
		new Element('div', {
			'class': 'message',
			html: decodeURIComponent(raw.data.user)+' said: '+decodeURIComponent(raw.data.message)
		}).inject(this.container);
	},
	_get_width_height:function()
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
	_init_map: function()
	{
		var c=this._get_width_height();
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
	},
	_make_dragable: function()
	{
		new Drag.Move($("dragable"), { 
			onDrop: function(element, droppable, event)
			{
				/**
				 * here capture the map position
				 */
				console.log('element', element);
				console.log('droppable', droppable);
				console.log('event', event);
			}
		});
	}
});
