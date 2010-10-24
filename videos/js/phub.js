/*

Correctly handle PNG transparency in Win IE 5.5 & 6.
http://homepage.ntlworld.com/bobosola. Updated 18-Jan-2006.

Use in <HEAD> with DEFER keyword wrapped in conditional comments:
<!--[if lt IE 7]>
<script defer type="text/javascript" src="pngfix.js"></script>
<![endif]-->

*/

//Mootools back compability
$A = function(iterable, start, length){
	if (Browser.Engine.trident && $type(iterable) == 'collection'){
		start = start || 0;
		if (start < 0) start = iterable.length + start;
		length = length || (iterable.length - start);
		var array = [];
		for (var i = 0; i < length; i++) array[i] = iterable[start++];
		return array;
	}
	start = (start || 0) + ((start < 0) ? iterable.length : 0);
	var end = ((!$chk(length)) ? iterable.length : length) + start;
	return Array.prototype.slice.call(iterable, start, end);
};

(function(){
	var natives = [Array, Function, String, RegExp, Number];
	for (var i = 0, l = natives.length; i < l; i++) natives[i].extend = natives[i].implement;
})();

window.extend = document.extend = function(properties){
	for (var property in properties) this[property] = properties[property];
};

window[Browser.Engine.name] = window[Browser.Engine.name + Browser.Engine.version] = true;

window.ie = window.trident;
window.ie6 = window.trident4;
window.ie7 = window.trident5;

Class.empty = $empty;

//legacy .extend support

Class.prototype.extend = function(properties){
	properties.Extends = this;
	return new Class(properties);
};

Array.implement({

	copy: function(start, length){
		return $A(this, start, length);
	}

});

Array.alias({erase: 'remove', combine: 'merge'});

Function.extend({

	bindAsEventListener: function(bind, args){
		return this.create({'bind': bind, 'event': true, 'arguments': args});
	}

});

Function.empty = $empty;

Hash.alias({getKeys: 'keys', getValues: 'values', has: 'hasKey', combine: 'merge'});
var Abstract = Hash;

Element.extend = Element.implement;

Elements.extend = Elements.implement;

Element.implement({

	getFormElements: function(){
		return this.getElements('input, textarea, select');
	},

	replaceWith: function(el){
		el = $(el);
		this.parentNode.replaceChild(el, this);
		return el;
	},

	removeElements: function(){
		return this.dispose();
	}

});

Element.alias({'dispose': 'remove', 'getLast': 'getLastChild'});

Element.implement({

	getText: function(){
		return this.get('text');
	},

	setText: function(text){
		return this.set('text', text);
	},

	setHTML: function(){
		return this.set('html', arguments);
	},

	getHTML: function(){
		return this.get('html');
	},

	getHTML: function(){
		return this.get('html');
	},

	getTag: function(){
		return this.get('tag');
	}

});

Event.keys = Event.Keys;

Element.implement({

	setOpacity: function(op){
		return this.set('opacity', op);
	}

});

Object.toQueryString = Hash.toQueryString;

var XHR = new Class({

	Extends: Request,

	options: {
		update: false
	},

	initialize: function(url, options){
		this.parent(options);
		this.url = url;
	},

	request: function(data){
		return this.send(this.url, data || this.options.data);
	},

	send: function(url, data){
		if (!this.check(arguments.callee, url, data)) return this;
		return this.parent({url: url, data: data});
	},

	success: function(text, xml){
		text = this.processScripts(text);
		if (this.options.update) $(this.options.update).empty().set('html', text);
		this.onSuccess(text, xml);
	},

	failure: function(){
		this.fireEvent('failure', this.xhr);
	}

});

var Ajax = XHR;

JSON.Remote = new Class({

	options: {
		key: 'json'
	},

	Extends: Request.JSON,

	initialize: function(url, options){
		this.parent(options);
		this.onComplete = $empty;
		this.url = url;
	},

	send: function(data){
		if (!this.check(arguments.callee, data)) return this;
		return this.parent({url: this.url, data: {json: Json.encode(data)}});
	},

	failure: function(){
		this.fireEvent('failure', this.xhr);
	}

});

Fx.implement({

	custom: function(from, to){
		return this.start(from, to);
	},

	clearTimer: function(){
		return this.cancel();
	},

	stop: function(){
		return this.cancel();
	}

});

Fx.Base = Fx;

Fx.Style = function(element, property, options){
	return new Fx.Tween(element, $extend({property: property}, options));
};

Element.implement({

	effect: function(property, options){
		return new Fx.Tween(this, $extend({property: property}, options));
	}

});

Fx.Styles = Fx.Morph;

Element.implement({

	effects: function(options){
		return new Fx.Morph(this, options);
	}

});

Native.implement([Element, Document], {

	getElementsByClassName: function(className){
		return this.getElements('.' + className);
	},

	getElementsBySelector: function(selector){
		return this.getElements(selector);
	}

});

Elements.implement({

	filterByTag: function(tag){
		return this.filter(tag);
	},

	filterByClass: function(className){
		return this.filter('.' + className);
	},

	filterById: function(id){
		return this.filter('#' + id);
	},

	filterByAttribute: function(name, operator, value){
		return this.filter('[' + name + (operator || '') + (value || '') + ']');
	}

});

var $E = function(selector, filter){
	return ($(filter) || document).getElement(selector);
};

var $ES = function(selector, filter){
	return ($(filter) || document).getElements(selector);
};

var Json = JSON;

JSON.toString = JSON.encode;
JSON.evaluate = JSON.decode;

Cookie.set = function(key, value, options){
	return new Cookie(key, options).write(value);
};

Cookie.get = function(key){
	return new Cookie(key).read();
};

Cookie.remove = function(key, options){
	return new Cookie(key, options).dispose();
};

// ---- Mootools compat

var PNG = new Class({

	initialize: function(container) {
		this.arVersion = navigator.appVersion.split("MSIE");
		if (this.arVersion.length > 1)
		{

			this.version = parseFloat(this.arVersion[1]);
			if (container) this.fix(container);
		}
	},

	fix: function(container,show) {
		if ((this.version >= 5.5) && (document.body.filters))
		{

			var fiximgs = $(container).getElements('img');

			fiximgs.each(function(img,i){
				var imgName = img.src.toUpperCase()
				if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
				{
					var tmp = new Element('div').cloneEvents(img);

					var imgID = (img.id) ? "id='" + img.id + "' " : ""
					var imgClass = (img.className) ? "class=\"pngfix " + img.className + "\" " : "class=\"pngfix\" "
					var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
					var imgStyle = "display:inline-block;" + img.style.cssText
					if (img.align == "left") imgStyle = "float:left;" + imgStyle
					if (img.align == "right") imgStyle = "float:right;" + imgStyle
					if (img.getParent() && img.getParent().href) imgStyle = "cursor:hand;" + imgStyle
					var strNewHTML = "<span " + imgID + imgClass + imgTitle
					+ " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
					+ "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
					+ "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>"
					img.outerHTML = strNewHTML;
					var availableSpans = container.getElements('span.pngfix');
					if (tmp && tmp.$events) availableSpans[i].cloneEvents(tmp);
				}
			});
		}
	}

});

var pngFix;

window.addEvent('domready',function(){
	pngFix = new PNG(window.document);
});


function highlight(element, callback){
	$(element).style.backgroundColor = "#FFFF00";
	var highlighter = new Fx.Style(element, 'backgroundColor',
		{duration: 2000,
		onComplete: function() { callback(); }
		});

	highlighter.start('#FFFFFF');
}

function removeProfileItem(actionUrl, id, oldDiv, showingDiv){
	if (confirm("Are you sure you want to remove this?")){
		new Request({
			url: actionUrl,
			method: "post",
			data: {id: id},
			onComplete: function(response){
				var parent = $(oldDiv).getParent();
				$(oldDiv).remove();

				if (response != ""){
					parent.innerHTML = parent.innerHTML.replace(/<div\s+class\s*=\s*"clear"\s*>\s*<\/div>/g, "");
					parent.innerHTML += response + '<div class = "clear"></div>';
					//decNums(showingDiv, false);
				}else{
					//decNums(showingDiv, true);
				}
			}
		}).send();
	}
}

function removeItem(actionUrl, id, oldDiv, showingDiv){
	if (confirm("Are you sure you want to remove this?")){
		new Request({
			url: actionUrl,
			method: "post",
			data: {id: id},
			onComplete: function(response){
				var parent = $(oldDiv).getParent();
				$(oldDiv).remove();

				decNums(showingDiv, true);
			}
		}).send();
	}
}

var speedDial={t:-1,m:null};
function showSpeedDial(s){if($('sdSize').value==0)return;if(speedDial.m==null){speedDial.m=new Fx.Styles("speedDialMenu",{duration:200});}speedDial.m.start({opacity:(s?1.0:0.0)});}
function clearSpeedTimeout(){if(speedDial.t!=-1){clearTimeout(speedDial.t);speedDial.t=-1;}}
function setSpeedTimeout(){if(speedDial.t!=-1)clearTimeout(speedDial.t);speedDial.t=setTimeout("showSpeedDial(false)",500);}
function decNums(e,b){if(b==null)b=false;var r = /(Showing\s)([0-9]+)(\sof\s)([0-9]+)(.*)/.exec($(e).innerHTML);$(e).innerHTML=""+r[1]+(b?r[2]-1:r[2])+r[3]+(r[4]-1)+r[5];}

function incNums(e,b)
{
	if (b == null)
		b=false;
	var r = /(Showing\s)([0-9]+)(\sof\s)([0-9]+)(.*)/.exec($(e).innerHTML);

	$(e).innerHTML = "" + r[1] + (b?r[2]-0+1:r[2])+r[3]+(r[4]-0+1)+r[5];
}


//rotating thumb functions
var changing_thumbs = new Array();
function changeThumb(index, i, num_thumbs, path, premium_flag)
{
	if (isNaN(premium_flag)) premium_flag = 0;

	if (premium_flag == 0)
		imgBase = 'small';
	else
		imgBase = '0';

	if (changing_thumbs[index])
	{
		if( path.indexOf('{i}') > 0 )
			$j('#'+index).attr('src', path.replace('{i}',i) );
		else if( path.indexOf('{index}') > 0 )
			$j('#'+index).attr('src', path.replace('{index}',i) );
		else
			$j('#'+index).attr('src', path + imgBase + i + ".jpg" );

		i = i % num_thumbs;
		i++;
		changing_thumbs[index] = setTimeout("changeThumb('" + index + "'," + i + ", " + num_thumbs + ", '" + path + "'," + premium_flag + ")", 600);
	}
}



function startThumbChange(index, num_thumbs, path, premium_flag)
{
	if (isNaN(premium_flag)) premium_flag = 0;

	changing_thumbs[index] = true;
	changeThumb(index, 1, num_thumbs, path, premium_flag);
}



function endThumbChange(index, path, premium_flag, xtube_flag)
{
	clearTimeout(changing_thumbs[index]);
	if (isNaN(premium_flag)) { premium_flag = 0; }
	if (isNaN(xtube_flag)) { xtube_flag = 0; }

	if (premium_flag == 0)
		imgBase = 'small';
	else
		imgBase = '01';

	if(xtube_flag == 0) {
		document.getElementById(index).src = path + imgBase + ".jpg";
	} else {
		document.getElementById(index).src = path;
	}
}



//show more less links javascript
function showMoreLess(id, more_text, less_text)
{
	if ($(id + "_link").innerHTML == more_text)
	{
		$(id).setStyles({visibility: 'visible', display: 'inline'});
		$(id + "_link").innerHTML = less_text;
	}
	else
	{
		$(id).setStyles({visibility: 'hidden', display: 'none'});
		$(id + "_link").innerHTML = more_text;
	}
}

function GetSwfVer(){var f=-1;if(navigator.plugins!=null&&navigator.plugins.length>0){if(navigator.plugins["Shockwave Flash 2.0"]||navigator.plugins["Shockwave Flash"]){var s=navigator.plugins["Shockwave Flash 2.0"]?" 2.0":"";var l=navigator.plugins["Shockwave Flash"+s].description;var d=l.split(" ");var t=d[2].split(".");var v=t[0];    var e=t[1];var r=d[3];if(r==""){r=d[4];}if(r[0]=="d"){r=r.substring(1);}else if(r[0]=="r"){r=r.substring(1);if(r.indexOf("d")>0){r=r.substring(0,r.indexOf("d"));}}f=v+"."+e+"."+r;}}else if(navigator.userAgent.toLowerCase().indexOf("webtv/2.6")!=-1)f=4;else if(navigator.userAgent.toLowerCase().indexOf("webtv/2.5")!=-1)f=3;else if(navigator.userAgent.toLowerCase().indexOf("webtv")!=-1)f=2;else if(isIE&&isWin&&!isOpera){flashVer=ControlVersion();}return f;}var isIE=(navigator.appVersion.indexOf("MSIE")!=-1);var isWin=(navigator.appVersion.toLowerCase().indexOf("win")!=-1);var isOpera=(navigator.userAgent.indexOf("Opera")!=-1);function ControlVersion(){var v;var a;var e;try{a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");v=a.GetVariable("$version");}catch(e){}if(!v){try{a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");v="WIN 6,0,21,0";a.AllowScriptAccess="always";v=a.GetVariable("$version");}catch(e){}}if(!v){try{a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");v=a.GetVariable("$version");}catch(e){}}if(!v){try{a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");v="WIN 3,0,18,0";}catch(e){}}if(!v){try{a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");v="WIN 2,0,0,11";}catch(e){v=-1;}}return v;}




/**
 * SWFObject v1.4.4: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
 *
 * SWFObject is (c) 2006 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * **SWFObject is the SWF embed script formerly known as FlashObject. The name was changed for
 *   legal reasons.
 */
if(typeof deconcept=="undefined"){var deconcept=new Object();}
if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}
if(typeof deconcept.SWFObjectUtil=="undefined"){deconcept.SWFObjectUtil=new Object();}
deconcept.SWFObject=function(_1,id,w,h,_5,c,_7,_8,_9,_a,_b){if(!document.getElementById){return;}
this.DETECT_KEY=_b?_b:"detectflash";
this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);
this.params=new Object();
this.variables=new Object();
this.attributes=new Array();
if(_1){this.setAttribute("swf",_1);}
if(id){this.setAttribute("id",id);}
if(w){this.setAttribute("width",w);}
if(h){this.setAttribute("height",h);}
if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}
this.installedVer=deconcept.SWFObjectUtil.getPlayerVersion();
if(c){this.addParam("bgcolor",c);}
var q=_8?_8:"high";
this.addParam("quality",q);
this.setAttribute("useExpressInstall",_7);
this.setAttribute("doExpressInstall",false);
var _d=(_9)?_9:window.location;
this.setAttribute("xiRedirectUrl",_d);
this.setAttribute("redirectUrl","");
if(_a){this.setAttribute("redirectUrl",_a);}};
deconcept.SWFObject.prototype={setAttribute:function(_e,_f){
this.attributes[_e]=_f;
},getAttribute:function(_10){
return this.attributes[_10];
},addParam:function(_11,_12){
this.params[_11]=_12;
},getParams:function(){
return this.params;
},addVariable:function(_13,_14){
this.variables[_13]=_14;
},getVariable:function(_15){
return this.variables[_15];
},getVariables:function(){
return this.variables;
},getVariablePairs:function(){
var _16=new Array();
var key;
var _18=this.getVariables();
for(key in _18){_16.push(key+"="+_18[key]);}
return _16;},getSWFHTML:function(){var _19="";
if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){
if(this.getAttribute("doExpressInstall")){
this.addVariable("MMplayerType","PlugIn");}
_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\"";
_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";
var _1a=this.getParams();
for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}
var _1c=this.getVariablePairs().join("&");
if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";
}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");}
_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\">";
_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";
var _1d=this.getParams();
for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}
var _1f=this.getVariablePairs().join("&");
if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}
return _19;
},write:function(_20){
if(this.getAttribute("useExpressInstall")){
var _21=new deconcept.PlayerVersion([6,0,65]);
if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){
this.setAttribute("doExpressInstall",true);
this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));
document.title=document.title.slice(0,47)+" - Flash Player Installation";
this.addVariable("MMdoctitle",document.title);}}
if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){
var n=(typeof _20=="string")?document.getElementById(_20):_20;
n.innerHTML=this.getSWFHTML();return true;
}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}
return false;}};
deconcept.SWFObjectUtil.getPlayerVersion=function(){
var _23=new deconcept.PlayerVersion([0,0,0]);
if(navigator.plugins&&navigator.mimeTypes.length){
var x=navigator.plugins["Shockwave Flash"];
if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}
}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}
catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}
catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}
catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}
return _23;};
deconcept.PlayerVersion=function(_27){
this.major=_27[0]!=null?parseInt(_27[0]):0;
this.minor=_27[1]!=null?parseInt(_27[1]):0;
this.rev=_27[2]!=null?parseInt(_27[2]):0;
};
deconcept.PlayerVersion.prototype.versionIsValid=function(fv){
if(this.major<fv.major){return false;}
if(this.major>fv.major){return true;}
if(this.minor<fv.minor){return false;}
if(this.minor>fv.minor){return true;}
if(this.rev<fv.rev){
return false;
}return true;};
deconcept.util={getRequestParameter:function(_29){
var q=document.location.search||document.location.hash;
if(q){var _2b=q.substring(1).split("&");
for(var i=0;i<_2b.length;i++){
if(_2b[i].substring(0,_2b[i].indexOf("="))==_29){
return _2b[i].substring((_2b[i].indexOf("=")+1));}}}
return "";}};
deconcept.SWFObjectUtil.cleanupSWFs=function(){if(window.opera||!document.all){return;}
var _2d=document.getElementsByTagName("OBJECT");
for(var i=0;i<_2d.length;i++){_2d[i].style.display="none";for(var x in _2d[i]){
if(typeof _2d[i][x]=="function"){_2d[i][x]=function(){};}}}};
deconcept.SWFObjectUtil.prepUnload=function(){__flash_unloadHandler=function(){};
__flash_savedUnloadHandler=function(){};
if(typeof window.onunload=="function"){
var _30=window.onunload;
window.onunload=function(){
deconcept.SWFObjectUtil.cleanupSWFs();_30();};
}else{window.onunload=deconcept.SWFObjectUtil.cleanupSWFs;}};
if(typeof window.onbeforeunload=="function"){
var oldBeforeUnload=window.onbeforeunload;
window.onbeforeunload=function(){
deconcept.SWFObjectUtil.prepUnload();
oldBeforeUnload();};
}else{window.onbeforeunload=deconcept.SWFObjectUtil.prepUnload;}
if(Array.prototype.push==null){
Array.prototype.push=function(_31){
this[this.length]=_31;
return this.length;};}
var getQueryParamValue=deconcept.util.getRequestParameter;
var FlashObject=deconcept.SWFObject;
var SWFObject=deconcept.SWFObject;


//v1.7
// Flash Player Version Detection
// Detect Client Browser type
// Copyright 2005-2007 Adobe Systems Incorporated.  All rights reserved.
var isIE  = (navigator.appVersion.indexOf("MSIE") != -1) ? true : false;
var isWin = (navigator.appVersion.toLowerCase().indexOf("win") != -1) ? true : false;
var isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;

function ControlVersion()
{
	var version;
	var axo;
	var e;

	// NOTE : new ActiveXObject(strFoo) throws an exception if strFoo isn't in the registry

	try {
		// version will be set for 7.X or greater players
		axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
		version = axo.GetVariable("$version");
	} catch (e) {
	}

	if (!version)
	{
		try {
			// version will be set for 6.X players only
			axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");

			// installed player is some revision of 6.0
			// GetVariable("$version") crashes for versions 6.0.22 through 6.0.29,
			// so we have to be careful.

			// default to the first public version
			version = "WIN 6,0,21,0";

			// throws if AllowScripAccess does not exist (introduced in 6.0r47)
			axo.AllowScriptAccess = "always";

			// default to the first public version
			version = "WIN 6,0,21,0";

			// throws if AllowScripAccess does not exist (introduced in 6.0r47)
			axo.AllowScriptAccess = "always";

			// safe to call for 6.0r47 or greater
			version = axo.GetVariable("$version");

		} catch (e) {
		}
	}

	if (!version)
	{
		try {
			// version will be set for 2.X player
			axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
			version = "WIN 2,0,0,11";
		} catch (e) {
			version = -1;
		}
	}

	return version;
}

// JavaScript helper required to detect Flash Player PlugIn version information
function GetSwfVer(){
	// NS/Opera version >= 3 check for Flash plugin in plugin array
	var flashVer = -1;

	if (navigator.plugins != null && navigator.plugins.length > 0) {
		if (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]) {
			var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
			var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;
			var descArray = flashDescription.split(" ");
			var tempArrayMajor = descArray[2].split(".");
			var versionMajor = tempArrayMajor[0];
			var versionMinor = tempArrayMajor[1];
			var versionRevision = descArray[3];
			if (versionRevision == "") {
				versionRevision = descArray[4];
			}
			if (versionRevision[0] == "d") {
				versionRevision = versionRevision.substring(1);
			} else if (versionRevision[0] == "r") {
				versionRevision = versionRevision.substring(1);
				if (versionRevision.indexOf("d") > 0) {
					versionRevision = versionRevision.substring(0, versionRevision.indexOf("d"));
				}
			}
			var flashVer = versionMajor + "." + versionMinor + "." + versionRevision;
		}
	}
	// MSN/WebTV 2.6 supports Flash 4
	else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.6") != -1) flashVer = 4;
	// WebTV 2.5 supports Flash 3
	else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.5") != -1) flashVer = 3;
	// older WebTV supports Flash 2
	else if (navigator.userAgent.toLowerCase().indexOf("webtv") != -1) flashVer = 2;
	else if ( isIE && isWin && !isOpera ) {
		flashVer = ControlVersion();
	}
	return flashVer;
}

// When called with reqMajorVer, reqMinorVer, reqRevision returns true if that version or greater is available
function DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision)
{
	versionStr = GetSwfVer();
	if (versionStr == -1 ) {
		return false;
	} else if (versionStr != 0) {
		if(isIE && isWin && !isOpera) {
			// Given "WIN 2,0,0,11"
			tempArray         = versionStr.split(" ");     // ["WIN", "2,0,0,11"]
			tempString        = tempArray[1];            // "2,0,0,11"
			versionArray      = tempString.split(",");    // ['2', '0', '0', '11']
		} else {
			versionArray      = versionStr.split(".");
		}
		var versionMajor      = versionArray[0];
		var versionMinor      = versionArray[1];
		var versionRevision   = versionArray[2];

			// is the major.revision >= requested major.revision AND the minor version >= requested minor
		if (versionMajor > parseFloat(reqMajorVer)) {
			return true;
		} else if (versionMajor == parseFloat(reqMajorVer)) {
			if (versionMinor > parseFloat(reqMinorVer))
				return true;
			else if (versionMinor == parseFloat(reqMinorVer)) {
				if (versionRevision >= parseFloat(reqRevision))
					return true;
			}
		}
		return false;
	}
}

function AC_AddExtension(src, ext)
{
  if (src.indexOf('?') != -1)
	return src.replace(/\?/, ext+'?');
  else
	return src + ext;
}

function AC_Generateobj(objAttrs, params, embedAttrs)
{
  var str = '';
  if (isIE && isWin && !isOpera)
  {
	str += '<object ';
	for (var i in objAttrs)
	{
	  str += i + '="' + objAttrs[i] + '" ';
	}
	str += '>';
	for (var i in params)
	{
	  str += '<param name="' + i + '" value="' + params[i] + '" /> ';
	}
	str += '</object>';
  }
  else
  {
	str += '<embed ';
	for (var i in embedAttrs)
	{
	  str += i + '="' + embedAttrs[i] + '" ';
	}
	str += '> </embed>';
  }

  document.write(str);
}

function AC_FL_RunContent(){
  var ret =
	AC_GetArgs
	(  arguments, ".swf", "movie", "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
	 , "application/x-shockwave-flash"
	);
  AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
}

function AC_SW_RunContent(){
  var ret =
	AC_GetArgs
	(  arguments, ".dcr", "src", "clsid:166B1BCA-3F9C-11CF-8075-444553540000"
	 , null
	);
  AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
}

function AC_GetArgs(args, ext, srcParamName, classid, mimeType){
  var ret = new Object();
  ret.embedAttrs = new Object();
  ret.params = new Object();
  ret.objAttrs = new Object();
  for (var i=0; i < args.length; i=i+2){
	var currArg = args[i].toLowerCase();

	switch (currArg){
	  case "classid":
		break;
	  case "pluginspage":
		ret.embedAttrs[args[i]] = args[i+1];
		break;
	  case "src":
	  case "movie":
		args[i+1] = AC_AddExtension(args[i+1], ext);
		ret.embedAttrs["src"] = args[i+1];
		ret.params[srcParamName] = args[i+1];
		break;
	  case "onafterupdate":
	  case "onbeforeupdate":
	  case "onblur":
	  case "oncellchange":
	  case "onclick":
	  case "ondblclick":
	  case "ondrag":
	  case "ondragend":
	  case "ondragenter":
	  case "ondragleave":
	  case "ondragover":
	  case "ondrop":
	  case "onfinish":
	  case "onfocus":
	  case "onhelp":
	  case "onmousedown":
	  case "onmouseup":
	  case "onmouseover":
	  case "onmousemove":
	  case "onmouseout":
	  case "onkeypress":
	  case "onkeydown":
	  case "onkeyup":
	  case "onload":
	  case "onlosecapture":
	  case "onpropertychange":
	  case "onreadystatechange":
	  case "onrowsdelete":
	  case "onrowenter":
	  case "onrowexit":
	  case "onrowsinserted":
	  case "onstart":
	  case "onscroll":
	  case "onbeforeeditfocus":
	  case "onactivate":
	  case "onbeforedeactivate":
	  case "ondeactivate":
	  case "type":
	  case "codebase":
	  case "id":
		ret.objAttrs[args[i]] = args[i+1];
		break;
	  case "width":
	  case "height":
	  case "align":
	  case "vspace":
	  case "hspace":
	  case "class":
	  case "title":
	  case "accesskey":
	  case "name":
	  case "tabindex":
		ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];
		break;
	  default:
		ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
	}
  }
  ret.objAttrs["classid"] = classid;
  if (mimeType) ret.embedAttrs["type"] = mimeType;
  return ret;
}



var DragNDrop = new Class({
	initialize: function(options){
		this.indexes = options.indexes;
		this.page = 1;
		this.pages_url = options.pages_url;
		this.image_base = options.image_base;

		this.loadDraggables();
		this.loadDroppables();
	},

	loadDraggables: function(){
		$$("#draggables div.draggable").each (function (drag){
			drag.orig_x = drag.style.left;
			drag.orig_y = drag.style.top;

			var span = drag.getElementsByTagName("span")[0];

			drag.friend_id = span.innerHTML;

			/*new Drag.Move(drag, {
				droppables: $$('#droppables div.droppable')
			});

			drag.addEvent("emptydrop", function(){
				this.setStyle("left", this.orig_x);
				this.setStyle("top", this.orig_y);
			});*/
			drag.addEvent("mousedown", function(e){
				e = new Event(e).stop();

				var clone = this.clone()
					.setStyles(this.getCoordinates())
					.setStyles({"opacity": 0.8, "position": "absolute"})
					.addEvent("emptydrop", function(){ this.remove(); })
					.inject(document.body);

				clone.makeDraggable({droppables: $$('#droppables div.droppable')}).start(e);
				clone.friend_id = drag.friend_id;
				clone.real = drag;
			});
		});
	},

	loadDroppables: function(){
		var me = this;

		$$("#droppables div.droppable").each (function (drop, index){
			drop.dropPos = index;
			drop.friend_id = me.indexes[index];

			drop.addEvents({
				"over": function (el, obj){
					this.setStyle("background-color", "#333");
				},

				"leave": function (el, obj){
					this.setStyle("background-color", "#111");
				},

				"drop": function (el, obj){
					this.setStyle("background-color", "#111");

					//check the friend ID to see if its already in the list
					var used = -1;
					for (var i = 0; i < me.indexes.length; i++){
						if (i != this.dropPos && me.indexes[i] == el.friend_id){
							used = i;
							break;
						}
					}
					var old = this.innerHTML;

					/*el.style.left = el.orig_x;
					el.style.top = el.orig_y;*/

					if (used < 0){
						this.innerHTML = el.real.innerHTML;

						this.friend_id = el.friend_id;
						me.indexes[this.dropPos] = this.friend_id;
					}else{
						var otherDrop = $("droppable" + used);

						this.innerHTML = otherDrop.innerHTML;
						otherDrop.innerHTML = old;

						var old_id = this.friend_id;
						this.friend_id = me.indexes[otherDrop.dropPos];
						otherDrop.friend_id = old_id;

						me.indexes[otherDrop.dropPos] = old_id;
						me.indexes[this.dropPos] = this.friend_id;
					}

					el.remove();
				}
			});
		});
	},

	getPage: function(page){
		var me = this;
		$("draggables").innerHTML = "<img src = \"" + this.image_base + "ajax-loader.gif\" alt = \"Loading...\" />";
		new Request({
			url: this.pages_url,
			method: "get",
			data: {page: page},
			onComplete: function(response){
				$("draggables").innerHTML = response;
				me.page = page;
				me.loadDraggables();
			}
		}).send();
	},

	submit: function(){
		var str = "<form action = \"" + window.location.href +
			"\" method = \"post\" id = \"submit_form\">";
		this.indexes.each( function(item, index){
			str += "<input type = \"hidden\" name = \"index[" +
				index + "]\" value = \"" + item + "\" />";
		});
		str += "</form>";

		$("draggables").innerHTML = str;
		$("submit_form").submit();
	},

	removeSpot: function(i){
		var drop = $("droppable" + i);

		drop.friend_id = 0;
		drop.innerHTML = "";
		this.indexes[i] = 0;
	}
});

function addTagToTarget(tag, target)
{
	var current_value = target.value;
	if (current_value.indexOf(tag) == -1)
		target.value += " " + tag;
}

/**
 * Javascript class for the Rater plugin
 */

var Rater = new Class({
	initialize: function(options){
		this.id = options.id;
		this.type = options.type;
		this.submit_url = options.submit_url;
		this.rating = options.rating;
		this.num_ratings = options.num_ratings;
		this.feedback_box = options.feedback_box;
		this.object_name = options.object_name;

		this.locked = false;

		if (options.post_ajax)
			this.post_ajax = options.post_ajax;
		else
			this.post_ajax = function(){}
	},

	mouseOver: function(num){
		if (!this.locked){
			if (num == 0)
				this.feedback_box.innerHTML = "Lame";
			else if (num == 1)
				this.feedback_box.innerHTML = "Bleh";
			else if (num == 2)
				this.feedback_box.innerHTML = "Alright";
			else if (num == 3)
				this.feedback_box.innerHTML = "Good";
			else if (num == 4)
				this.feedback_box.innerHTML = "Awesome";

			for (var i = 0; i <= num; i++)
				$(this.object_name + "_rating_star_" + i).className = "star star_full";
			for (var i = num + 1; i < 5; i++)
				$(this.object_name + "_rating_star_" + i).className = "star star_empty";
		}
	},

	rate: function(num){
		if (!this.locked){
			this.locked = true;

			var me = this;
			new Request({
				url: this.submit_url,
				method: "post",
				data: {id: this.id, value: num + 1},
				onComplete: function(response){
					me.feedback_box.innerHTML = response + "<br />";
					me.post_ajax();
				}
			}).send();
		}
	},

	reset: function(){
		if (!this.locked){
			for(var i = 0; i < 5; i++){
				if (this.rating <= i + 0.1)
					$(this.object_name + "_rating_star_" + i).className = "star star_empty";
				else if (this.rating < i + 0.5)
					$(this.object_name + "_rating_star_" + i).className = "star star_half";
				else
					$(this.object_name + "_rating_star_" + i).className = "star star_full";
			}
			this.feedback_box.innerHTML = this.num_ratings + " Ratings";
		}
	}
});

/**
 * Javascript class for the ThumbsUp plugin
 */

var ThumbsUp = new Class({
	initialize: function(options){
		this.id = options.id;
		this.type = options.type;
		this.submit_url = options.submit_url;
		this.rating = options.rating;
		this.num_ratings = options.num_ratings;
		this.feedback_box = options.feedback_box;
		this.object_name = options.object_name;

		this.locked = false;

		if (options.post_ajax)
			this.post_ajax = options.post_ajax;
		else
			this.post_ajax = function(){}
	},

	rate: function(num){
		if (!this.locked){
			this.locked = true;

			var me = this;
			new Request({
				url: this.submit_url,
				method: "post",
				data: {id: this.id, value: num},
				onComplete: function(response){
					me.feedback_box.innerHTML = response + "<br />";
					me.post_ajax();
				}
			}).send();
		}
	}
});


/**
 * Javascript class for the Share plugin
 */

var Share = new Class({
	initialize: function(options){
		this.object_id = options.object_id;
		this.submit_url = options.submit_url;
		this.button = options.button;
		this.feedback_box = options.feedback_box;
		this.object_name = options.object_name;
		this.on_complete = options.on_complete;
		this.share_to = options.share_to;
		this.share_name = options.share_name;
		this.share_message = options.share_message;
	},

	send: function(){
		var me = this;
		this.button.disabled = 'disabled';
		this.feedback_box.innerHTML = '<div class="notice">Sending...</div>';
		//alert(this.submit_url);
		new Request({
			url: this.submit_url,
			method: "post",
			data: {id: this.object_id,
				to: me.share_to.value, name: me.share_name.value,
				message: me.share_message.value},
			onComplete: function() {
				me.feedback_box.innerHTML = '<div class="success">Sent successfully</div>';
				setTimeout(me.object_name + ".feedback_box.innerHTML = ''; " + me.object_name + ".button.disabled = '';", 3000);
				me.on_complete();
			}
		}).send();
	}
});

/**
 * Javascript class for the Share plugin
 */

var Flagger = new Class({
	initialize: function(options){
		this.object_id = options.object_id;
		this.submit_url = options.submit_url;
		this.num_types = options.num_types;
		this.button = options.button;
		this.feedback_box = options.feedback_box;
		this.object_name = options.object_name;
		this.on_complete = options.on_complete;
		this.is_producer = options.is_producer;
	},

	send: function(){
		var me = this;

		var selected = 0;
		for (var i = 1; i <= this.num_types; i++){
			if ($("flag_" + i).checked){
				selected = i;
				break;
			}
		}

		if(this.is_producer == undefined && ($("flag_" + selected).value == "copyright" || $("flag_" + selected).value == "copy"))
		{
			alert("Please send any copyright reports to copyright@pornhub.com. Thank you.");
			return;
		}

		if (selected == 0)
			this.feedback_box.set('html', 'Please Select a Reason');
		else
		{
			this.button.disabled = 'disabled';
			this.feedback_box.set('html', 'Posting...');
			new Request({
				url: this.submit_url,
				method: "post",
				data: {id: this.object_id,
					reason: $("flag_reason").value,
					checked: selected},
				onComplete: function() {
					me.feedback_box.set('html', 'Flagged successfully!');
					setTimeout(me.object_name + ".feedback_box.innerHTML = ''; " + me.object_name + ".button.disabled = '';", 3000);
					me.on_complete();
				}
			}).send();
		}
	}
});

var CommentController = new Class(
{
	initialize: function(options)
	{
		this.item_id = options.item_id;
		this.item_owner_id = options.item_owner_id;
		this.item_type = options.item_type;
		this.writer_id = options.writer_id;
		this.message_box = options.message_box;
		this.feedback_box = options.feedback_box;
		this.char_left_box = options.char_left_box;
		this.comment_box_prefix = options.comment_box_prefix;
		this.report_spam_box_prefix = options.report_spam_box_prefix;
		this.new_comment_box = options.new_comment_box;
		this.paging = options.paging;
		this.action = options.action;
		this.js_object = options.js_object;
		this.unique = options.unique;
		this.get_page_url = options.get_page_url;

		this.anchor = options.anchor;
		this.video_attacher = options.video_attacher;

		this.postponed_link = options.postponed_link;
		this.postponed_div = options.postponed_div;

		this.i = 1;
		this.page = 1;
	},

	show_chars_left: function()
	{
		var chars_left = 1000 - $(this.message_box).value.length;

		if (chars_left < 0)
			chars_left = 0;

		$(this.char_left_box).innerHTML = chars_left;
	},

	postComment: function(url_base)
	{
		if ($(this.message_box).value.length > 1000)
		{
			$(this.feedback_box).innerHTML = '<div class="profile-box-content-center"><div class="error">You can\'t have more than 1000 characters.</div></div>';
			return;
		}

		if ($(this.message_box).value.match(/^\s*$/))
		{
			$(this.feedback_box).innerHTML = '<div class="profile-box-content-center"><div class="error">You have to type a message.</div></div>';
			return;
		}

		$(this.feedback_box).innerHTML = '<div class="profile-box-content-center"><div class="warning">Posting...</div></div>';

		$("comment_submit_" + this.unique).disabled = true;

		var video_id = (this.video_attacher == null ? 0 : eval(this.video_attacher + ".getVideo()"));
		var rnd_id = Math.floor(Math.random() * 100000000 + 1);
		var me = this;
		var postBody = {item_id: this.item_id, writer_id: this.writer_id,
							item_owner_id: this.item_owner_id, item_type: this.item_type,
							comment: $(this.message_box).value, i: this.i, js_object: this.js_object,
							unique: this.unique, video_id: video_id, rnd_id: rnd_id};

		$(this.message_box).readOnly = true;

		new Request({
			url: url_base,
			method: 'post',
			data: postBody,
			onComplete: function (html) {
				$(me.message_box).readOnly = false;
				$("comment_submit_" + me.unique).disabled = false;

				if (me.video_attacher != null){
					eval(me.video_attacher + ".showPanel(false)");
				}

				if(html.indexOf('time_limit_spam') != -1)
				{
					$(me.feedback_box).innerHTML = '<div class="profile-box-content-center"><div class="error">Please Don\'t Spam!</div></div>';
				}
				else if(html.indexOf('spam_filter_spam') != -1)
				{
					$(me.feedback_box).innerHTML = '<div class="profile-box-content-center"><div class="error">Message Posting Failed!</div></div>';
				}
				else
				{
					if (me.page == 1)
					{
						if ($("no_comments_yet"))
							$("no_comments_yet").style.display = "none";
						$(me.new_comment_box).innerHTML = html;
						incNums("numMessages_" + me.unique, true);
						me.new_comment_box = 'new_comment_box_' + me.unique + "_" + me.i;
				   /*
						if (parseInt(video_id))
						{
							var to = new SWFObject("http://www.pornhub.com/players/pornhub_2.swf", "player", "475", "356", "8", "#000000");
							to.addParam("allowfullscreen", "true");
							to.addVariable("options", "http://www.pornhub.com/wall_player_v2.php?id=" + video_id);
							to.write("playerDiv_" + me.i);
						}
					 */
						me.i++;
					}
					else
					{
						me.getPage(1);
					}

					$(me.feedback_box).innerHTML = '<div class="profile-box-content-center"><div class="success">Message Posted Successfully</div></div>';
					$(me.message_box).value = "";
				}

				if (video_id != 0)
					eval(me.video_attacher + ".setToNull()");

				setTimeout(me.js_object + ".clearFeedbackBox()", 3000);
			}
		}).send();
	},

	getPostponed: function()
	{
		var me = this;

		var postBody = {id: this.item_id, type: this.item_type, page: 1, url: this.action, unique: this.unique};

		$(me.postponed_link).innerHTML = '<div class="profile-box-content-center"><div class="warning">Loading...</div></div>';

		$(me.postponed_link).innerHTML = '<div class="profile-box-content-center"><div class="warning">Loading...</div></div>';

		$(me.postponed_div).setStyle('display','block');

		new Request({
			url: this.get_page_url,
			method: 'post',
			data: postBody,
			onComplete: function (html)
			{
				$(me.paging).innerHTML = html;
				$(me.postponed_link).innerHTML = '';
			}
		}).send();

	},

	deleteComment: function(id, url_base)
	{
		var me = this;
		$(me.comment_box_prefix + "_" + id).innerHTML = '<div class="profile-box-content-center"><div class="warning">Deleting...</div></div>';
		new Request({
			url: url_base + "&id=" + id + "&type=" + this.item_type + "&item_id=" + this.item_id,
			method: 'post',
			data: {},
			onComplete: function (html) {
				//setTimeout(me.js_object + ".deleteCommentDiv(" + id + ")", 3000);
				decNums("numMessages_" + me.unique, true);
				setTimeout(window.location.reload(), 1000);
			}
		}).send();
	},

	deleteCommentDiv: function(comment_id) {
		$(this.comment_box_prefix + "_" + comment_id).remove();
	},

	clearFeedbackBox: function() {
		$(this.feedback_box).innerHTML = "";
	},

	reportSpam: function(id, owner_id, url_base)
	{
		var me = this;

		new Request({
			url: url_base + "&id=" + id + "&type=" + this.item_type + "&owner_id=" + owner_id,
			method: 'post',
			data: {},
			onComplete: function (html) {
				$(me.report_spam_box_prefix + id).innerHTML = "Marked as spam";
			}
		}).send();
	},

	blockUser: function(id, url_base, user_to_block_id, block_or_unblock)
	{
		if(block_or_unblock)
			if (!confirm("Are you sure you want to block this user?"))
				return false;

		var me = this;

		var postBody = {id: user_to_block_id, block: block_or_unblock};

		new Request({
			url: url_base,
			method: 'post',
			data: postBody,
			onComplete: function (html)
			{
				window.location.href = me.action;
			}
		}).send();
	},

	getPage: function(page, url_base)
	{
		var me = this;
		this.page = page;
		var postBody = {id: this.item_id, type: this.item_type, page: page, url: this.action, unique: this.unique};
		$(me.paging).innerHTML = '<div class="profile-box-content-center"><div class="warning">Loading...</div></div>';

		new Request({
			url: this.get_page_url,
			method: 'post',
			data: postBody,
			onComplete: function (html)
			{
				$(me.paging).innerHTML = html;
			}
		}).send();
	}
});

var DropDown = new Class({

	initialize: function(options){
		this.selectedItem = -1;
		this.unique = options.unique;
		this.tags_box = options.tags_box;
		this.target_url = options.target_url;

		var dd = $("dropdown_" + this.unique), cb = $("chooser_box_" + this.unique);
		dd.setStyle("left", cb.getPosition().x);
		dd.setStyle("top", cb.getPosition().y + 20);
	},

	checkItem: function(event){
		var text = $("chooser_box_" + this.unique).value;

		var key = (event.which ? event.which : event.keyCode);

		if (key == 40 && this.selectedItem < 9)
			this.selectedItem++;
		else if (key == 38 && this.selectedItem > -1)
			this.selectedItem--;

		var me = this;
		if (text.length > 2){
			new Request({
				url: this.target_url,
				method: "get",
				data: {text: text, selectedItem: this.selectedItem},
				onComplete: function(response){
					$("dropdown_" + me.unique).innerHTML = response;
					$("dropdown_" + me.unique).style.display = "block";
				}
			}).send();
		}else{
			$("dropdown_" + this.unique).style.display = "none";
		}
	},

	selectItem: function(){
		this.tags_box.innerHTML += " " +
			(this.selectedItem == -1 ? $("chooser_box_" + this.unique).value : $("selectedItem_" + this.unique).innerHTML);

		$("dropdown_" + this.unique).style.display = "none";
		$("chooser_box_" + this.unique).value = '';
		this.selectedItem = -1;
	},

	highlightItem: function(i){
		this.selectedItem = i;
		var me = this;
		new Request({
			url: this.target_url,
			method: "get",
			data: {text: $("chooser_box_" + this.unique).value, selectedItem: this.selectedItem, unique: this.unique},
			onComplete: function(response){
				$("dropdown_" + me.unique).innerHTML = response;
				$("dropdown_" + me.unique).style.display = "block";
			}
		}).send();
	}
});

// JavaScript Document

var ThumbCropper = new Class({

	initialize: function(options) {
		this.left = 10;
		this.top = 10;
		this.ratio = options.thumb_height / options.thumb_width;
		this.min_left = 0;
		this.max_right = options.max_right;
		this.min_top = 0;
		this.max_bottom = options.max_bottom;
		this.min_width = options.min_width;
		this.min_height = options.min_height;
		this.image_filter = (options.image_filter == null ? "" : options.image_filter);
		this.action = options.action;

		this.thumb_width = options.thumb_width;
		this.thumb_height = options.thumb_height;
		this.in_pic = options.in_pic;
		this.out_pic = options.out_pic;
		this.dark_pic = options.dark_pic;

		this.pre_ajax = (options.pre_ajax == null ? function(){} : options.pre_ajax);
		this.post_ajax = (options.post_ajax == null ? function(){} : options.post_ajax);

		this.resizing = false;
		this.moving = false;

		this.container = $('cropper-container');
		this.resizer = $('cropper-resizer');
		this.mover = $('cropper-mover');

		var pos = this.container.getPosition();
		var size = this.mover.getSize();
		this.container_left = pos.x;
		this.container_top = pos.y;
		this.mover_width = size.x;
		this.mover_height = size.y;
		var pos = this.mover.getPosition();
		this.mover_left = pos.x - this.container_left;
		this.mover_top = pos.y - this.container_top;

		$('cropper-container-border').addEvent("mouseenter", this.enter.bind(this));
		$('cropper-container-border').addEvent("mouseout", this.out.bind(this));
		document.addEvent("mouseup", this.mouseUp.bind(this));
		document.addEvent("mousemove", this.move.bind(this));
		this.resizer.addEvent("mousedown", this.resizeMouseDown.bind(this));
		this.mover.addEvent("mousedown", this.moveMouseDown.bind(this));

		this.x_offset = 0;
		this.y_offset = 0;
	},

	enter: function(event) {
		event = new Event(event);

	},

	out: function(event) {
		event = new Event(event);
	},

	mouseUp: function(event) {
		event = new Event(event);

		if (this.resizing)
		{
			this.resizing = false;
		}

		if (this.moving)
		{
			this.moving = false;
		}
	},

	move: function(event) {
		event = new Event(event);

		var difference = 0;

		if (this.moving)
		{
			var mouse_x = event.page.x - this.container_left;
			var mouse_y = event.page.y - this.container_top;

			if (mouse_x - this.x_offset + this.mover_width > this.max_right)
				this.mover_left = this.max_right - this.mover_width;
			else if (mouse_x - this.x_offset < this.min_left)
				this.mover_left = this.min_left;
			else
				this.mover_left = mouse_x - this.x_offset;

			if (mouse_y - this.y_offset + this.mover_height > this.max_bottom)
				this.mover_top = this.max_bottom - this.mover_height;
			else if (mouse_y - this.y_offset < this.min_top)
				this.mover_top = this.min_top;
			else
				this.mover_top = mouse_y - this.y_offset;

			//reset offset (since sometimes cursor can move when box doesn't
			this.x_offset = mouse_x - this.mover_left;
			this.y_offset = mouse_y - this.mover_top;

			this.mover.setStyle("top",this.mover_top);
			this.mover.setStyle("left",this.mover_left);
			//this.mover.setStyle("background-position", "-" + (this.mover_left + 1) + "px -" + (this.mover_top + 1) + "px;");
			this.mover.style.backgroundPosition = "-" + (this.mover_left + 1) + "px -" + (this.mover_top + 1) + "px";
			this.resizer.setStyle("top",this.mover_top + this.mover_height);
			this.resizer.setStyle("left",this.mover_left + this.mover_width);
		}
		else if (this.resizing)
		{
			difference = event.page.x - this.container_left - this.mover_left - this.mover_width;
			var do_resize = false;



			if (this.mover_left + this.mover_width + difference <= this.max_right &&
				this.mover_width + difference > this.min_width &&
				parseInt(this.mover_top + this.mover_height + (difference * this.ratio)) <= this.max_bottom &&
				parseInt(this.mover_height + (difference * this.ratio)) > this.min_height)
			{
				this.mover_width += difference;
				this.mover_height = parseInt(this.mover_width * this.ratio);

				this.mover.setStyle("width",this.mover_width - 2);        //-2 for border
				this.mover.setStyle("height",this.mover_height - 2);    //-2 for border
				this.resizer.setStyle("top",this.mover_top + this.mover_height);
				this.resizer.setStyle("left",this.mover_left + this.mover_width);

			}

		}
	},

	resizeMouseDown: function(event) {
		event = new Event(event);
		this.resizing = true;
		this.moving = false;
	},

	moveMouseDown: function(event) {
		event = new Event(event);
		this.moving = true;
		this.resizing = false;

		this.x_offset = event.page.x - this.mover_left - this.container_left;
		this.y_offset = event.page.y - this.mover_top  - this.container_top;
	},

	crop: function() {
		this.pre_ajax();

		$("cropper_msg").innerHTML = "Cropping...";

		var postBody = {in_pic: this.in_pic,
						out_pic: this.out_pic,
						left: this.mover_left,
						top: this.mover_top,
						width: this.mover_width,
						height: this.mover_height,
						thumb_width: this.thumb_width,
						thumb_height: this.thumb_height,
						image_filter: this.image_filter};
		var out_pic = this.out_pic;
		var me = this;
		new Request({
			url: this.action,
			method: 'post',
			data: postBody,
			onComplete: function(response){
				$("cropper_msg").innerHTML = "Done!";
				setTimeout("$('cropper_msg').innerHTML = '';", 3000);
				me.post_ajax(response);
			}
		}).send();
	},

	recrop: function() {
		$('cropper').setStyle("display", "block");
		$('cropper').setStyle("visibility", "visible");
		$('thumb_preview').setStyle("display", "none");
		$('thumb_preview').setStyle("visibility", "hidden");
	},

	cancel: function() {
		$('thumb_preview').setStyle("display", "block");
		$('thumb_preview').setStyle("visibility", "visible");
		$('cropper').setStyle("display", "none");
		$('cropper').setStyle("visibility", "hidden");
	}

});

/**
 * Javascript class for the Share plugin
 */

var FeedbackPoll = new Class({
	initialize: function(options){
		this.message_box = options.message_box;
		this.button = options.button;
		this.feedback_box = options.feedback_box;
		this.object_name = options.object_name;
		this.submit_url = options.submit_url;
		this.num_options = options.num_options;
		this.on_complete = options.on_complete;
		this.referral_url = options.referral_url;
		this.feedback_id = options.feedback_id;
		this.user_id = options.user_id;
		this.browsing_info = options.browsing_info;
	},

	send: function(){
		var me = this;

		var selected = 0;
		for (var i = 1; i <= this.num_options; i++){
			if ($(this.object_name + '_' + i).checked){
				selected = $(this.object_name + '_' + i).value;
				break;
			}
		}

		if (selected == 0)
			this.feedback_box.innerHTML = '<div class="error">Please Select a Reason</div>';
		else if (selected == 'other' && this.message_box.value == "")
			this.feedback_box.innerHTML = '<div class="error">Please enter a message if you choose the option "Other"</div>';
		else
		{
			this.button.disabled = 'disabled';
			this.feedback_box.innerHTML = '<div class="notice">Posting...</div>';
			new Request({
				url: this.submit_url,
				method: "post",
				data: {
					message: me.message_box.value + me.browsing_info,
					option_id: selected,
					referral_url: me.referral_url,
					feedback_id: me.feedback_id,
					user_id: me.user_id},
				onComplete: function(html) {
					if (html == "spam")
					{
						me.feedback_box.innerHTML = '<div class="error">You cannot report problems this fast! SLOW DOWN!!!</div>';
						me.button.disabled = '';
					}
					else
					{
						me.message_box.value = '';
						me.feedback_box.innerHTML = html;
						setTimeout(me.object_name + ".feedback_box.innerHTML = ''; " + me.object_name + ".button.disabled = '';", 3000);
						me.on_complete();
					}
				}
			}).send();
		}
	}
});


var SideScroller = new Class({
	initialize: function(options){
		this.divHeight = options.divHeight;
		this.divWidth = options.divWidth;
		this.numItems = options.numItems;
		this.itemWidth = options.itemWidth;
		this.scrollingDiv = options.scrollingDiv;

		this.scrollPos = 0;
		this.increments = 5;
		this.timer = 0;

		var sl = options.scrollLeft;
		var sr = options.scrollRight;

		var stop = function() { scroller.stopScroll(); return false; };

		var me = this;

		sl.addEvent("mouseup", function(){
			me.scrollingDiv.scrollLeft = me.scrollPos = 0;
		});
		sl.addEvent("mouseover", function(){
			scroller.scrollLeft();
			return false;
		});
		sl.addEvent("mouseout", stop);
		sl.style.cursor = "pointer";

		sr.addEvent("mouseup", function(){
			me.scrollPos = me.numItems * me.itemWidth - me.divWidth;
			me.scrollingDiv.scrollLeft = me.scrollPos;
		});
		sr.addEvent("mouseover", function(){
			scroller.scrollRight();
			return false;
		});
		sr.addEvent("mouseout", stop);
		sr.style.cursor = "pointer";
	},

	scrollLeft: function(){
		if (this.timer) clearTimeout(this.timer);

		this.scrollPos = Math.max(this.scrollPos - this.increments, 0);
		this.scrollingDiv.scrollLeft = this.scrollPos;

		this.timer = setTimeout("scroller.scrollLeft()", 15);
	},

	scrollRight: function(){
		if (this.timer) clearTimeout(this.timer);

		this.scrollPos = Math.min(this.scrollPos + this.increments, this.numItems * this.itemWidth - this.divWidth);
		this.scrollingDiv.scrollLeft = this.scrollPos;

		this.timer = setTimeout("scroller.scrollRight()", 15);
	},

	stopScroll: function(){
		clearTimeout(this.timer);
		this.timer = 0;
	}
});

var VideoAttacher = new Class({
	initialize: function(options){
		this.selected_id = options.selected_id;
		this.image_base = options.image_base;
		this.page_url = options.page_url;
	},

	showPanel: function(show){
		$("video_attacher_panel").style.display = (show ? "block" : "none");
	},

	getPage: function(page, type){
		this.selected_id = 0;
		$("video_attacher_content").innerHTML = "<img src = \"" + this.image_base + "ajax-loader.gif\" alt = \"\" />";
		new Request({
			url: this.page_url,
			method: "get",
			data: {page: page, type: type, selected_id: this.selected_id},
			onComplete: function(response){
				$("video_attacher_content").innerHTML = response;
			}
		}).send();
	},

	clickVideo: function(id){
		if (this.selected_id)
			$("video_box_" + this.selected_id).style.border = "1px solid #000";

		$("video_box_" + id).style.border = "1px dashed #F98E00";
		this.selected_id = id;
	},

	attachVideo: function(){
		if (this.selected_id != $("attached_video_id").value)
		{
			$("attached_video_id").value = this.selected_id;
			this.selected_id = 0;
		}
	},

	getVideo: function(){
		return this.selected_id;
	},

	setToNull: function(){
		this.selected_id = 0;
	}
});

//    NOTES - ok to delete
//    AddSite= this will be the url to the social bookmarking site for adding bookmarks
//    AddUrlVar= variable for URL
//    AddTitleVar= variable for TITLE
//    AddNote= the notes or description of the page - we're using the title for this when it's used
//    AddReturn= so far, one site requires a return url to be passed
//    AddOtherVars= some social bookmarking sites require other variables and their values to be passed - if any exist, they'll be set to this var
//    AddToMethod    = [0=direct,1=popup]

var txtVersion = "0.1";
var addtoInterval = null;
var popupWin = '';



///////////////////////////////////////////////////////////////////////////////
// Add To Bookmarks Layout Style

var addtoLayout='';                        // addtoLayout: 0=Horizonal 1 row, 1=Horizonal 2 rows, 2=Vertical with icons, 3=Vertical no icons
var addtoMethod=1;                        // addtoMethod: 0=direct link, 1=popup
var AddURL = document.location.href;    // could be set dynamically to your blog post's permalink
var AddTitle = escape(document.title);    // same here, this could be set dymaically instead of the page's current title*/

switch(addtoLayout){
	case 0:        // horizontal, 1 row
document.write('<div class="addToContent"><dl class="addTo"><dd><span title="Learn about Social Bookmarking" class="addToAbout" onclick');
document.write('="addto(0)">ADD TO:</span></dd><dd><span title="Add this page to Blink"  onclick="addto(1)"><img src="modules/bookmarks/AddTo_Blin');
document.write('k.gif" width="16" height="16" border="0" />Blink</span></dd><dd><span title="Add this page to Delicious" onclick="addto');
document.write('(2)"><img src="modules/bookmarks/AddTo_Delicious.gif" width="16" height="16" border="0" />Del.icio.us</span></dd><dd><span title="');
document.write('Add this page to Digg" onclick="addto(3)"><img src="modules/bookmarks/AddTo_Digg.gif" width="16" height="16" border="0" />Digg</spa');
document.write('n></dd><dd><span title="Add this page to Furl" onclick="addto(4)"><img src="modules/bookmarks/AddTo_Furl.gif" width="16" height="1');
document.write('6" border="0" />Furl</span></dd><dd><span title="Add this page to Google" onclick="addto(5)"><img src="modules/bookmarks/AddTo_Goo');
document.write('gle.gif" width="16" height="16" border="0" />Google</span></dd><dd><span title="Add this page to Simpy" onclick="addto(');
document.write('6)"><img src="modules/bookmarks/AddTo_Simpy.gif" width="16" height="16" border="0" />Simpy</span></dd><dd><span title="Add this pa');
document.write('ge to Spurl" onclick="addto(8)"><img src="modules/bookmarks/AddTo_Spurl.gif" width="16" height="16" border="0" />Spurl</span></dd>');
document.write('<dd><span title="Add this page to Yahoo! MyWeb" onclick="addto(7)"><img src="modules/bookmarks/AddTo_Yahoo.gif" width="16" height="');
document.write('16" border="0" />Y! MyWeb</span></dd></dl></div>');
	break;
	case 1:        // horizontal, 2 rows
document.write('<div class="addToContent"><div class="addTo2Row"><div class="addToHeader" onclick="addto(0)">ADD THIS TO YOUR SOCIAL BO');
document.write('OKMARKS</div><div class="addToFloat"><span title="Add this page to Blink"  onclick="addto(1)"><img src="modules/bookmarks/AddTo_Bl');
document.write('ink.gif" width="16" height="16" border="0" /> Blink</span><br /><span title="Add this page to Delicious" onclick="addto');
document.write('(2)"><img src="modules/bookmarks/AddTo_Delicious.gif" width="16" height="16" border="0" /> Del.icio.us</span></div><div class="add');
document.write('ToFloat"><span title="Add this page to Digg" onclick="addto(3)"><img src="modules/bookmarks/AddTo_Digg.gif" width="16" height="16" ');
document.write('border="0" /> Digg</span><br /><span title="Add this page to Furl" onclick="addto(4)"><img src="modules/bookmarks/AddTo_Furl.gif" ');
document.write('width="16" height="16" border="0" /> Furl</span></div><div class="addToFloat"><span title="Add this page to Google" onc');
document.write('lick="addto(5)"><img src="modules/bookmarks/AddTo_Google.gif" width="16" height="16" border="0" /> Google</span><br /><span title=');
document.write('"Add this page to Simpy" onclick="addto(6)"><img src="modules/bookmarks/AddTo_Simpy.gif" width="16" height="16" border="0" />Simpy<');
document.write('/span></div><div class="addToFloat"><span title="Add this page to Spurl" onclick="addto(8)"><img src="modules/bookmarks/AddTo_Spur');
document.write('l.gif" width="16" height="16" border="0" />Spurl</span><br /><span title="Add this page to Yahoo! MyWeb" onclick="addto');
document.write('(7)"><img src="modules/bookmarks/AddTo_Yahoo.gif" width="16" height="16" border="0" /> Y! MyWeb</span><br /></div></div></div>');
	break;
	case 2:        // vertical with icons
document.write('<div class="addToContent"><dl class="addToV"><dd><span title="Learn about Social Bookmarking" class="addToAbout" onclic');
document.write('k="addto(0)">ADD TO:</span></dd><dd><span title="Add this page to Blink"  onclick="addto(1)"><img src="modules/bookmarks/AddTo_Bli');
document.write('nk.gif" width="16" height="16" border="0" />Blink</span></dd><dd><span title="Add this page to Delicious" onclick="addt');
document.write('o(2)"><img src="modules/bookmarks/AddTo_Delicious.gif" width="16" height="16" border="0" />Del.icio.us</span></dd><dd><span title=');
document.write('"Add this page to Digg" onclick="addto(3)"><img src="modules/bookmarks/AddTo_Digg.gif" width="16" height="16" border="0" />Digg</sp');
document.write('an></dd><dd><span title="Add this page to Furl" onclick="addto(4)"><img src="modules/bookmarks/AddTo_Furl.gif" width="16" height="');
document.write('16" border="0" />Furl</span></dd><dd><span title="Add this page to Google" onclick="addto(5)"><img src="modules/bookmarks/AddTo_Go');
document.write('ogle.gif" width="16" height="16" border="0" />Google</span></dd><dd><span title="Add this page to Simpy" onclick="addto');
document.write('(6)"><img src="modules/bookmarks/AddTo_Simpy.gif" width="16" height="16" border="0" />Simpy</span></dd><dd><span title="Add this p');
document.write('age to Spurl" onclick="addto(8)"><img src="modules/bookmarks/AddTo_Spurl.gif" width="16" height="16" border="0" />Spurl</span></dd>');
document.write('<dd><span title="Add this page to Yahoo! MyWeb" onclick="addto(7)"><img src="modules/bookmarks/AddTo_Yahoo.gif" width="16" height=');
document.write('"16" border="0" />Y! MyWeb</span></dd></dl></div>');
	break;
	case 3:        // vertical no icons
document.write('<div class="addToContent"><dl class="addToVNoImg"><dd><span title="Learn about Social Bookmarking" class="addToAbout" o');
document.write('nclick="addto(0)">ADD TO:</span></dd><dd><span title="Add this page to Blink" onclick="addto(1)">Blink</span></dd><dd>');
document.write('<span title="Add this page to Delicious" onclick="addto(2)">Del.icio.us</span></dd><dd><span title="Add this page to Di');
document.write('gg" onclick="addto(3)">Digg</span></dd><dd><span title="Add this page to Furl" onclick="addto(4)">Furl</span></dd><dd>');
document.write('<span title="Add this page to Google" onclick="addto(5)">Google</span></dd><dd><span title="Add this page to Simpy" onc');
document.write('lick="addto(6)">Simpy</span></dd><dd><span title="Add this page to Spurl" onclick="addto(8)">Spurl</span></dd><dd><spa');
document.write('n title="Add this page to Yahoo! MyWeb" onclick="addto(7)">Y! MyWeb</span></dd></dl></div>');
	break;
	default:
}
function addtoWin(addtoFullURL)
{
	if (!popupWin.closed && popupWin.location){
		popupWin.location.href = addtoFullURL;
		var addtoInterval = setInterval("closeAddTo();",1000);
	}
	else{
		popupWin = window.open(addtoFullURL,'addtoPopUp','width=770px,height=500px,status=0,location=0,resizable=1,scrollbars=1,left=0,top=100');
		var addtoInterval = setInterval("closeAddTo();",1000);
		if (!popupWin.opener) popupWin.opener = self;
	}
	if (window.focus) {popupWin.focus()}
	return false;
}
// closes the popupWin
function closeAddTo() {
	if (!popupWin.closed && popupWin.location){
		if (popupWin.location.href == AddURL)    //if it's the same url as what was bookmarked, close the win
		popupWin.close();
	}
	else {    //if it's closed - clear the timer
		clearInterval(addtoInterval)
		return true
	}
}
//main addto function - sets the variables for each Social Bookmarking site
function addto(addsite){
	switch(addsite){
		case 0:
			var AddSite = "http://www.clip-share.com/?";
			var AddUrlVar = "url";
			var AddTitleVar = "title";
			var AddNoteVar = "";
			var AddReturnVar = "";
			var AddOtherVars = "";
			break
		case 1:    //    Blink ID:1
			var AddSite = "http://www.blinklist.com/index.php?Action=Blink/addblink.php";
			var AddUrlVar = "url";
			var AddTitleVar = "title";
			var AddNoteVar = "description";
			var AddReturnVar = "";
			var AddOtherVars = "&Action=Blink/addblink.php";
			break
		case 2:    //    Del.icio.us    ID:2 &v=3&noui=yes&jump=close
			var AddSite = "http://del.icio.us/post?";
			var AddUrlVar = "url";
			var AddTitleVar = "title";
			var AddNoteVar = "";
			var AddReturnVar = "";
			var AddOtherVars = "";
			break
		case 3:    //    Digg ID:3
			var AddSite = "http://digg.com/submit?";
			var AddUrlVar = "url";
			var AddTitleVar =  "";
			var AddNoteVar =  "";
			var AddReturnVar =  "";
			var AddOtherVars = "&phase=2";
			break
		case 4:    //    Furl ID:4
			var AddSite = "http://www.furl.net/storeIt.jsp?";
			var AddUrlVar = "u";
			var AddTitleVar = "t";
			var AddNoteVar = "";
			var AddReturnVar = "";
			var AddOtherVars = "";
			break
		case 5:    //    GOOGLE ID:5
			var AddSite = "http://fusion.google.com/add?";
			var AddUrlVar = "feedurl";
			var AddTitleVar = "";
			var AddNoteVar = "";
			var AddReturnVar = "";
			var AddOtherVars = "";
			break
		case 6:    //    Simpy ID:6
			var AddSite = "http://simpy.com/simpy/LinkAdd.do?";
			var AddUrlVar = "href";
			var AddTitleVar = "title";
			var AddNoteVar = "note";
			var AddReturnVar = "_doneURI";
			var AddOtherVars = "&v=6&src=bookmarklet";
			break
		case 7:    //    Yahoo ID: 7
			var AddSite = "http://myweb2.search.yahoo.com/myresults/bookmarklet?";
			var AddUrlVar = "u";
			var AddTitleVar = "t";
			var AddNoteVar = "";
			var AddReturnVar = "";
			var AddOtherVars = "&d=&ei=UTF-8";
			break
		case 8:    //    Spurl ID: 8     d.selection?d.selection.createRange().text:d.getSelection()
			var AddSite = "http://www.spurl.net/spurl.php?";
			var AddUrlVar = "url";
			var AddTitleVar = "title";
			var AddNoteVar = "blocked";
			var AddReturnVar = "";
			var AddOtherVars = "&v=3";
			break
		default:
	}
//    Build the URL
	var addtoFullURL = AddSite + AddUrlVar + "=" + AddURL + "&" + AddTitleVar + "=" + AddTitle + AddOtherVars ;
	if (AddNoteVar != "")
		{var addtoFullURL = addtoFullURL + "&" + AddNoteVar + "=" + AddTitle;}
	if (AddReturnVar != "")
		{var addtoFullURL = addtoFullURL + "&" + AddReturnVar + "=" + AddURL;}
//    Checking AddToMethod, to see if it opens in new window or not
	switch(addtoMethod){
		case 0:    // 0=direct link
			self.location = addtoFullURL
			break
		case 1:    // 1=popup
			addtoWin(addtoFullURL);
			break
		default:
		}
		return true;
}
//    checking across domains causes errors - this is to supress these
function handleError() {return true;}
//window.onerror = handleError;

//for grabing browsing info
var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
		this.language =  navigator.language;
		this.plugins = navigator.plugins;
		this.cookieEnabled = navigator.cookieEnabled;
		this.javaEnabled = this.javaEnabled();
		this.colordepth = window.screen.colorDepth;
		this.width = window.screen.width;
		this.height = window.screen.height;
		this.maxwidth = window.screen.availWidth;
		this.maxheight = window.screen.availHeight;
	},
	javaEnabled: function (){
			if(navigator.javaEnabled() < 1) return 'No';
			else if(navigator.javaEnabled() == 1) return 'Yes';
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)    {
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{     string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{        // for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{         // for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			   string: navigator.userAgent,
			   subString: "iPhone",
			   identity: "iPhone/iPod"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};


//***************************
//Movie box rotation code
//Tomasz Rakowski
//2/26/2009
//***************************


document.mboxThumbScripts = new Array();

function moveBoxRotator(basePath, elementName, duration) {
	this.basePath = basePath;
	this.elementName = elementName;
	this.duration = duration;
	this.stepLength = 3;
	this.element;
	this.counter = 2;
	this.step = 40;
	this.speed = 600;
	this.timeoutId;
	this.exec;
	this.orginalSource;

	this.start = function() {
		//console.log('start');
		this.originalSource = this.getElement().src;
		this.exec = 'document.mboxThumbScripts[' + this.elementName +'].rotate()';
		this.timeoutId = setInterval(this.exec, this.speed);
	}

	this.rotate = function() {
		//console.log('rotate -> ' + this.counter + ' ' + (this.duration / this.stepLength));
		if(this.counter > (this.duration / this.stepLength)) {
			this.reset();
		} else {
			var filename = String(this.counter + ".jpg");
			for(x = filename.length; x < 8; x++) {
				filename = String("0" + filename);
			}
			this.setImage(this.basePath + filename);
			this.counter = this.counter + this.step;
		}
	}

	this.reset = function() {
		//console.log('reset ' + this.counter);
		this.counter = 2;
		this.rotate();
	}

	this.stop = function() {
		//console.log('stop');
		clearInterval(this.timeoutId);
		this.getElement().src = this.originalSource;
	}

	this.getElement = function() {
		if(!this.element) {
			this.element = document.getElementById(this.elementName);
		}
		return this.element;
	}

	this.setImage = function(path) {
		//console.log(path);
		this.getElement().src = path;
	}
}

BrowserDetect.init();


//http://www.featureblend.com/license.txt
var FlashDetect=new function(){var self=this;self.installed=false;self.raw="";self.major=-1;self.minor=-1;self.revision=-1;self.revisionStr="";var activeXDetectRules=[{"name":"ShockwaveFlash.ShockwaveFlash.7","version":function(obj){return getActiveXVersion(obj);}},{"name":"ShockwaveFlash.ShockwaveFlash.6","version":function(obj){var version="6,0,21";try{obj.AllowScriptAccess="always";version=getActiveXVersion(obj);}catch(err){}
return version;}},{"name":"ShockwaveFlash.ShockwaveFlash","version":function(obj){return getActiveXVersion(obj);}}];var getActiveXVersion=function(activeXObj){var version=-1;try{version=activeXObj.GetVariable("$version");}catch(err){}
return version;};var getActiveXObject=function(name){var obj=-1;try{obj=new ActiveXObject(name);}catch(err){obj={activeXError:true};}
return obj;};var parseActiveXVersion=function(str){var versionArray=str.split(",");return{"raw":str,"major":parseInt(versionArray[0].split(" ")[1],10),"minor":parseInt(versionArray[1],10),"revision":parseInt(versionArray[2],10),"revisionStr":versionArray[2]};};var parseStandardVersion=function(str){var descParts=str.split(/ +/);var majorMinor=descParts[2].split(/\./);var revisionStr=descParts[3];return{"raw":str,"major":parseInt(majorMinor[0],10),"minor":parseInt(majorMinor[1],10),"revisionStr":revisionStr,"revision":parseRevisionStrToInt(revisionStr)};};var parseRevisionStrToInt=function(str){return parseInt(str.replace(/[a-zA-Z]/g,""),10)||self.revision;};self.majorAtLeast=function(version){return self.major>=version;};self.minorAtLeast=function(version){return self.minor>=version;};self.revisionAtLeast=function(version){return self.revision>=version;};self.versionAtLeast=function(major){var properties=[self.major,self.minor,self.revision];var len=Math.min(properties.length,arguments.length);for(i=0;i<len;i++){if(properties[i]>=arguments[i]){if(i+1<len&&properties[i]==arguments[i]){continue;}else{return true;}}else{return false;}}};self.FlashDetect=function(){if(navigator.plugins&&navigator.plugins.length>0){var type='application/x-shockwave-flash';var mimeTypes=navigator.mimeTypes;if(mimeTypes&&mimeTypes[type]&&mimeTypes[type].enabledPlugin&&mimeTypes[type].enabledPlugin.description){var version=mimeTypes[type].enabledPlugin.description;var versionObj=parseStandardVersion(version);self.raw=versionObj.raw;self.major=versionObj.major;self.minor=versionObj.minor;self.revisionStr=versionObj.revisionStr;self.revision=versionObj.revision;self.installed=true;}}else if(navigator.appVersion.indexOf("Mac")==-1&&window.execScript){var version=-1;for(var i=0;i<activeXDetectRules.length&&version==-1;i++){var obj=getActiveXObject(activeXDetectRules[i].name);if(!obj.activeXError){self.installed=true;version=activeXDetectRules[i].version(obj);if(version!=-1){var versionObj=parseActiveXVersion(version);self.raw=versionObj.raw;self.major=versionObj.major;self.minor=versionObj.minor;self.revision=versionObj.revision;self.revisionStr=versionObj.revisionStr;}}}}}();};FlashDetect.JS_RELEASE="1.0.4";



// Filters menu wraper show/hide function:
function filters_show(which) {

	$j('.jc-submenu-wrapper').css('display','none');
	$j('#toprated').css('display','none');
	$j('#mostviewedsubmenu').css('display','none');

	if( which != 'none' ) {
		$j('#'+which).css('display','block');
		$j('.jc-submenu-wrapper').css('display','block');
	}
}


// Helper function to replace a tag's CSS class.
// action is a string composed of "className [add|remove] classNameAddition" .
function replaceClass(tag, action) {
	var reg = /\s/i;
	var params = action.split(reg);
	if( params[1] == 'add' ) {
		if( $j(tag).hasClass(params[0]) ) {
			$j(tag).removeClass(params[0]);
			$j(tag).addClass(params[0]+'_'+params[2]);
		}
	} else if( params[1] == 'remove' ) {
		if( $j(tag).hasClass(params[0]+'_'+params[2]) ) {
			$j(tag).removeClass(params[0]+'_'+params[2]);
			$j(tag).addClass(params[0]);
		}
	}
}


/***
 * Lightbox/popup handling using jQuery.
 */
var lightboxCSS;

// Lightbox object.
var lightbox = {
	show: function(route,w,h,e) {
//		var scroll = document.viewport.getScrollOffsets();
		lightboxCSS = {
			'width': w+'px',
			'height': (h>0?h+'px':'auto')
		}

		$j.ajax({
			type: "POST",
			dataType: "html",
			url: route,
			success: function(data,status) {
				$j('div.lightbox_content').html(data);
				$j('div.lightbox_content').css( lightboxCSS );
				recalcLightbox();
				$j('div.lightbox_background').show();
				$j('div.lightbox_content').show();
			}
		});
	},
	hide: function() {
		$j('div.lightbox_content').hide();
		$j('div.lightbox_background').hide();
	}
}


// We need to handle this so the dimmed background works good.
function recalcLightbox(e) {
	if( $j('div.lightbox_content')[0] )
	{
		$j('div.lightbox_content').css( {
			'top': (180/*+$j(document).scrollTop()*/)+'px',
			'left': ( ($j(document).width()-$j('div.lightbox_content').width())/2) +'px'
		});
	}
}

// Add some event handlers to the window :
$j(document).ready( function(e) {
	if( $j('div.lightbox_content')[0] )
	{
		$j('div.lightbox_background').css('opacity',0.5);
		$j('div.lightbox_background').click(lightbox.hide);
		$j(window).resize(recalcLightbox);
		$j(window).scroll(recalcLightbox);
	}
});

/* JavaScript For Language Flags */
$j(document).ready( function(e) {
	if ($j('div.flag-wrapper')[0]) {
		$j('li.flag').mouseover(function() {
			$j('div.language-marker').show();
			$j('div.language-marker').css("right", -1*($j(this).position().left-32-$j('li.flag').length*32));
			$j('div.language-marker').text($j(this).find("span").text());
		});
		$j('li.flag').mouseout(function() {
			$j('div.language-marker').hide();
		});
	}
});

/* Fixing background for search input box for PS3 */
function fixPs3() {
	if (navigator.userAgent && navigator.userAgent.match(/PLAYSTATION/)) 
	{
		document.getElementById("search_value").style.height = "20px";
		document.getElementById("search_value").style.backgroundImage = "none";
		document.getElementById("search_value").style.backgroundColor = "white";
		document.getElementById("search_value").style.width = "151px";
	}
}