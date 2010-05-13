/*
	GMapsOverlay v1.0
	by André Fiedler (http://www.visualdrugs.net) - GNU license.
*/

var GMapsOverlay = {

	init: function(options){
		this.options = Object.extend({
			resizeDuration: 400,
			resizeTransition: Fx.Transitions.sineInOut,
			width: 250,
			height: 250,
			animateCaption: true
		}, options || {});
		
		if(!GBrowserIsCompatible()) return false;
		
		this.geocoder = new GClientGeocoder();

		this.anchors = [];
		$each(document.links, function(el){
			if (el.rel && el.rel.test(/^gmapsoverlay/i)){
				el.onclick = this.click.pass(el, this);
				this.anchors.push(el);
			}
		}, this);
		this.eventKeyDown = this.keyboardListener.bindAsEventListener(this);
		this.eventPosition = this.position.bind(this);

		this.overlay = new Element('div').setProperty('id', 'gmOverlay').injectInside(document.body);

		this.center = new Element('div').setProperty('id', 'gmCenter').setStyles({
																				 
																				 width: this.options.width+'px', 
																				 height: this.options.height+'px', 
																				 marginLeft: '-'+(this.options.width/2)+'px'
																				 }).injectInside(document.body);
		this.maplayer = new Element('div').setProperty('id', 'gmMap').injectInside(this.center);
		
		this.map = new GMap2(this.maplayer);
		this.map.addControl(new GLargeMapControl());
		this.map.addControl(new GMapTypeControl());
		
		this.bottomContainer = new Element('div').setProperty('id', 'gmBottomContainer').setStyle('display', 'none').injectInside(document.body);
		this.bottom = new Element('div').setProperty('id', 'gmBottom').injectInside(this.bottomContainer);
		new Element('a').setProperties({id: 'gmCloseLink', href: '#'}).injectInside(this.bottom).onclick = this.overlay.onclick = this.close.bind(this);
		this.caption = new Element('div').setProperty('id', 'gmCaption').injectInside(this.bottom);
		new Element('div').setStyle('clear', 'both').injectInside(this.bottom);
		
		this.center.style.display = 'none';
		
		var nextEffect = this.nextEffect.bind(this);
		this.fx = {
			overlay: this.overlay.effect('opacity', {duration: 500, fps:100}).hide(),
			resize: this.center.effects({duration: this.options.resizeDuration, transition: this.options.resizeTransition, onComplete: nextEffect}),
			maplayer: this.maplayer.effect('opacity', {duration: 500, onComplete: nextEffect}),
			bottom: this.bottom.effect('margin-top', {duration: 400, onComplete: nextEffect})
		};
		
	},

	click: function(link){
		return this.show(link.href);
	},

	show: function(link){
		this.link = link;
		this.position();
		this.setup(true);
		this.top = window.getScrollTop() + (window.getHeight() / 15);
		this.center.setStyles({top: this.top+'px', display: ''});
		this.fx.overlay.start(0.8);
		return this.changeLink();
	},

	position: function(){
		this.overlay.setStyles({top: window.getScrollTop()+'px', height: window.getHeight()+'px'});
	},

	setup: function(open){
		var elements = $A(document.getElementsByTagName('object'));
		if (window.ie) elements.extend(document.getElementsByTagName('select'));
		elements.each(function(el){ el.style.visibility = open ? 'hidden' : ''; });
		var fn = open ? 'addEvent' : 'removeEvent';
		window[fn]('scroll', this.eventPosition)[fn]('resize', this.eventPosition);
		document[fn]('keydown', this.eventKeyDown);
		this.step = 0;
	},

	keyboardListener: function(event){
		this.close();
	},

	changeLink: function(){
		this.step = 1;
		this.bottomContainer.style.display = 'none';
		this.fx.maplayer.hide();
		this.center.className = 'gmLoading';
		this.showAddress(decodeURI(this.link.substring(this.link.indexOf('q=')+2)));
		this.nextEffect();
		return false;
	},

	nextEffect: function(){
		switch (this.step++){
		case 1:
			this.center.className = '';

			this.caption.setHTML("<a href=\""+this.link+"\" target=\"_blank\">Zur Google-Maps Seite</a>");
			if (this.center.clientHeight != this.maplayer.offsetHeight){
				this.fx.resize.start({height: this.maplayer.offsetHeight, width: this.maplayer.offsetWidth, marginLeft: -this.maplayer.offsetWidth/2});
				break;
			}
			this.step++;
		case 2:
			this.bottomContainer.setStyles({top: (this.top + this.center.clientHeight)+'px', height: '0px', marginLeft: this.center.style.marginLeft, width: this.center.clientWidth+'px', display: ''});
			this.fx.maplayer.start(1);
			break;
		case 3:
			if (this.options.animateCaption){
				this.fx.bottom.set(-this.bottom.offsetHeight);
				this.bottomContainer.style.height = '';
				this.fx.bottom.start(0);
				break;
			}
			this.bottomContainer.style.height = '';
		case 4:
			this.step = 0;
		}
	},

	close: function(){
		if (this.step < 0) return;
		this.step = -1;
		for (var f in this.fx) this.fx[f].stop();
		this.center.style.display = this.bottomContainer.style.display = 'none';
		this.fx.overlay.chain(this.setup.pass(false, this)).start(0);
		return false;
	},
	
	showAddress: function(address){
		this.geocoder.getLatLng(
		address,
		function(point){
			if(point){
				this.map.setCenter(point, 15);
				var marker = new GMarker(point);
				this.map.addOverlay(marker);
				marker.openInfoWindowHtml(address);
			}
			else
			{
				this.close();
				alert('Diese Adresse konnte nicht gefunden werden');
			}
		}.bind(this));
	}
};
window.addEvent('domready', GMapsOverlay.init.bind(GMapsOverlay));