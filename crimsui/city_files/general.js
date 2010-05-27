jQuery.fn.selectbox = function(options){
	/* Default settings */
	var settings = {
		className: 'jquery-selectbox',
		animationSpeed: 50,
		listboxMaxSize: 20,
		replaceInvisible: false
	};
	var commonClass = 'jquery-custom-selectboxes-replaced';
	var listOpen = false;
	var showList = function(listObj) {
		var selectbox = listObj.parents('.' + settings.className + '');
		listObj.slideDown(settings.animationSpeed, function(){
			listOpen = true;
		});
		selectbox.addClass('selecthover');
		jQuery(document).bind('click', onBlurList);
		return listObj;
	}
	var hideList = function(listObj) {
		var selectbox = listObj.parents('.' + settings.className + '');
		listObj.slideUp(settings.animationSpeed, function(){
			listOpen = false;
			jQuery(this).parents('.' + settings.className + '').removeClass('selecthover');
		});
		jQuery(document).unbind('click', onBlurList);
		return listObj;
	}
	var onBlurList = function(e) {
		var trgt = e.target;
		var currentListElements = jQuery('.' + settings.className + '-list:visible').parent().find('*').andSelf();
		if(jQuery.inArray(trgt, currentListElements)<0 && listOpen) {
			hideList( jQuery('.' + commonClass + '-list') );
		}
		return false;
	}
	
	/* Processing settings */
	settings = jQuery.extend(settings, options || {});
	/* Wrapping all passed elements */
	return this.each(function(i) {
		var _this = jQuery(this);
		if(_this.filter(':visible').length == 0 && !settings.replaceInvisible)
			return;
		var replacement = jQuery(
			'<div class="' + settings.className + ' ' + commonClass + '">' +
				'<div id="morebnt_'+i+'" class="' + settings.className + '-moreButton" />' +
				'<div class="' + settings.className + '-list ' + commonClass + '-list" />' +
				'<span class="' + settings.className + '-currentItem" />' +
			'</div>'
		);
		jQuery('option', _this).each(function(k,v){
			var v = jQuery(v);
			var str = v.attr('title').replace(/\[/g,'<').replace(/\]/g,'>');
			var classn = v.attr('className');
			if(!str){
				str = 	v.text();
			}
			var check_option_state = false;
			check_option_state = v.attr('disabled');
			var listElement =  jQuery('<span rel="'+v.text()+'" class="' + settings.className + '-item value-'+v.val()+' item-'+k+'">' + str + '</span>');	
			listElement.click(function(){
				if(classn=='jumplink'){
					window.location = v.val();
					return;
				}else{	
					var thisListElement = jQuery(this);
					var thisReplacment = thisListElement.parents('.'+settings.className);
					var thisIndex = thisListElement[0].className.split(' ');
					for( k1 in thisIndex ) {
						if(/^item-[0-9]+$/.test(thisIndex[k1])) {
							thisIndex = parseInt(thisIndex[k1].replace('item-',''), 10);
							break;
						}
					};
					var thisValue = thisListElement[0].className.split(' ');
					for( k1 in thisValue ) {
						if(/^value-.+$/.test(thisValue[k1])) {
							thisValue = thisValue[k1].replace('value-','');
							break;
						}
					};
					if(check_option_state==false){
						var thisTitle = thisListElement.attr('rel');
						thisReplacment
							.find('.' + settings.className + '-currentItem')
							.text(thisTitle);
						thisReplacment
							.find('select')
							.val(thisValue)
							.triggerHandler('change');
						var thisSublist = thisReplacment.find('.' + settings.className + '-list');
						if(thisSublist.filter(":visible").length > 0) {
							hideList( thisSublist );
						}else{
							showList( thisSublist );
						}
					}
				}
			}).bind('mouseenter',function(){
				if(check_option_state==false){
					jQuery(this).addClass('listelementhover');
				}
			}).bind('mouseleave',function(){
				if(check_option_state==false){
					jQuery(this).removeClass('listelementhover');
				}
			});
			jQuery('.' + settings.className + '-list', replacement).append(listElement);
			if(v.filter(':selected').length > 0) {
				if(check_option_state==false){
					jQuery('.'+settings.className + '-currentItem', replacement).text(v.text());
				}
			}
		});
		replacement.find('.' + settings.className + '-currentItem, .' + settings.className + '-moreButton').click(function(){
			var thisMoreButton = jQuery(this);
			var otherLists = jQuery('.' + settings.className + '-list')
				.not(thisMoreButton.siblings('.' + settings.className + '-list'));
			hideList( otherLists );
			var thisList = thisMoreButton.siblings('.' + settings.className + '-list');
			if(thisList.filter(":visible").length > 0) {
				hideList( thisList );
			}else{
				showList( thisList );
			}
		}).bind('mouseenter',function(){
			// jQuery(this).addClass('morebuttonhover');
			$('#morebnt_'+i).addClass('morebuttonhover');
			//alert(i);
		}).bind('mouseleave',function(){
			$('#morebnt_'+i).removeClass('morebuttonhover');
		});
		_this.hide().replaceWith(replacement).appendTo(replacement);
		var thisListBox = replacement.find('.' + settings.className + '-list');
		var thisListBoxSize = thisListBox.find('.' + settings.className + '-item').length;
		if(thisListBoxSize > settings.listboxMaxSize)
			thisListBoxSize = settings.listboxMaxSize;
		if(thisListBoxSize == 0)
			thisListBoxSize = 1;	
		var thisListBoxWidth = Math.round(_this.width() + 5);
		/*
		if(jQuery.browser.safari)
			thisListBoxWidth = thisListBoxWidth * 0.94;
		//replacement.css('width', thisListBoxWidth + 'px');
		thisListBox.css({
			width: Math.round(thisListBoxWidth-5) + 'px',
			height: thisListBoxSize + 'em'
		});*/
	});
};
(function($) {
	var TIP_DIM = {
		w : 0,
		h : 0
	};
	var BLINKER_STATE = 0;
	var current = '';
	var old_menu_id  = '';
	$.fn.extend({
		ttip: {
			tooltipType:'smarttip',
			tooltipClass:'stip',
			tooltipNum: 0
		},
		initTooltip: function(settings) {
			$.ttip = $.extend({},$.ttip,settings);
			ttpContainer = $('#tt');
			var i=0;
			this.each(function() {
				this.ttip = $.extend({},$.ttip,{tooltipNum:i});
				i++;
			}).bind('mouseover', showTooltip )
			  .bind('click', clickObject );
		}
	});
	function showTooltip(event){
		var tipTitle = '';
		current = this;
		if( current.tooltipSrc ){
			tipTitle = current.tooltipSrc;
		}else{
			tipTitle = $.trim(current.title);
			if( tipTitle ){
				tipTitle = prepareTooltipContent(tipTitle, current.ttip.tooltipType);
				current.tooltipSrc = tipTitle;
				current.title = '';
				$(this).bind('mousemove', moveTooltip )
					   .bind('mouseout', hideTooltip );
			}else{
				$(this).unbind('mouseover');
				return;
			}
		}
		if(current.ttip.tooltipType=='mapObjects'){
			var classAttribute=$(this).attr("className");
			var classes=classAttribute.split(" ");
			$(this).removeClass(classes[1]);
			$(this).addClass(classes[1]+'_over'); 
			current.classNameTT = classes[1];
		}
		ttpContainer.html('<span class="'+current.ttip.tooltipClass+'" style="margin:0;padding:0;left:0;top:0;">' + tipTitle + '</span>')
					.css('visibility','visible');
		TIP_DIM.w = ttpContainer.width();
		TIP_DIM.h = ttpContainer.height();
		Locate(event);
	}
	function moveTooltip(event){
		Locate(event);
	}
	function hideTooltip(event){
		if(current.ttip.tooltipType=='mapObjects'){
			$(this).removeClass(current.classNameTT+'_over');
			$(this).addClass(current.classNameTT);
		}
		ttpContainer.html('').css('visibility','hidden');
	}
	function clickObject(event) {
		current = this;
		hideTooltip(event);
		if(current.ttip.tooltipType=='mapObjects'){
			if( current.tooltipMenu == false || current.tooltipMenu == 'undefined' || current.tooltipMenu == null ) {
				var menu_content = prepareMenuContent(current.ttip.tooltipNum);
				$(menu_content).appendTo('body');
				current.tooltipMenu = true;
				var menu_id = $("#menu_"+current.ttip.tooltipNum);
				$(".utt", menu_id).initTooltip({
					tooltipType:'avatar',
					tooltipClass:'utip'
				});

				menu_id.hover(function(e){
					$("#menu_"+current.ttip.tooltipNum+" tr").hover(function(e){
						$(this).css('background-color', '#666666');
					}, function(){
						$(this).css('background-color', '');
					});
				}, function(){
					menu_id.css("visibility", 'hidden');
				});
			}else{
				var menu_id = $("#menu_"+current.ttip.tooltipNum);
			}
			LocateMenu(event,menu_id);
			/*
			if(old_menu_id!='' && old_menu_id!="#menu_"+current.ttip.tooltipNum) {
				$(old_menu_id).css("visibility", 'hidden');
			}
			old_menu_id = "#menu_"+current.ttip.tooltipNum;
			*/
		}
	}
	function prepareMenuContent(el){
	
		var curent_object = MAP.data[el];
		var str = '';
		var str_next = '';
		var rows = '';
		var num_rows = curent_object.length;

		tdnum='';
		if(curent_object[0][0]=='m'){
			if(curent_object[0][1]) var f1 = curent_object[0][1]; else var f1 = '';
			if(curent_object[0][2]) var f2 = '<span class="attack">'+curent_object[0][2]+'</span>'; else var f2 = '';
			if(curent_object[0][6]) var f2 = '<span class="respect">'+curent_object[0][6]+'</span>'; // npcs that have respect do not have attack
			if(curent_object[0][3]) var f3 = '<br/><small>'+curent_object[0][3]+'</small>'; else var f3 = '';
			if(curent_object[0][4]) var f4 = curent_object[0][4]+'.'; else var f4 = '';
			if(MAP.map=='city'){
				if(curent_object[0][5]) var f5 = curent_object[0][5]+'/'; else var f5 = '';
			}else{
				if(curent_object[0][5]) var f5 = curent_object[0][5]+''; else var f5 = '';
			}
			for(k=1;k<num_rows;k++){
				str_next += buildNPCTableRow(curent_object[k],f4,f5);
			}
			str = '<table id="menu_'+el+'" class="cmenu" cellspacing="0" cellpadding="0">';
			str += '<tr><th colspan="'+tdnum+'" class="title">'+f1+' '+f2+' '+f3+'</th></th></tr>';
			str += str_next;
			str += '</table>';
			return str;
		}else{
			if(curent_object[0][1]) var f1 = curent_object[0][1]; else var f1 = '';
			if(curent_object[0][2]) var f2 = '<span class="attack">'+curent_object[0][2]+'</span>'; else var f2 = '';
			if(curent_object[0][3]) var f3 = '<br/><small>'+curent_object[0][3]+'</small>'; else var f3 = '';
			if(curent_object[0][4]) f2 = '<span class="'+curent_object[0][4]+'">'+curent_object[0][2]+'</span>';
			if(curent_object[0][5]=='n' && curent_object[0][3]) f3= curent_object[0][3];
			for(k=1;k<num_rows;k++){
				str_next += buildBuildingTableRow(curent_object[k]);
			}
			str = '<table id="menu_'+el+'" class="cmenu" cellspacing="0" cellpadding="0">';
			str += '<tr><th colspan="3" class="title">'+f1+' '+f2+' '+f3+'</th></th></tr>';
			str += str_next;
			str += '</table>';
			return str;
		}
	}
	function buildNPCTableRow(r,f4,f5){
		if(MAP.map=='city'){ var mmaptype = 'city/' }else{ var mmaptype=MAP.map; }
		var td = '';
		tdnum = 1;
		if(r[2]){ td += '<td><a class="st" href="/'+mmaptype+'/'+f5+f4+r[0]+'?z='+req_id+'">'+r[2]+'</a></td>'; tdnum++; }
		if(r[3]){ td += '<td><a class="ch" href="/'+mmaptype+'/'+f5+f4+r[0]+'?z='+req_id+'">'+r[3]+'</a></td>'; tdnum++; }
		if(r[4]){ td += '<td><a class="rs" href="/'+mmaptype+'/'+f5+f4+r[0]+'?z='+req_id+'">'+r[4]+'</a></td>'; tdnum++; }
		var tr = '<tr>';
		tr += '<th><a href="/'+mmaptype+'/'+f5+f4+r[0]+'?z='+req_id+'">'+r[1]+'</a></th>';
		tr += td;
		tr += '</tr>';
		return tr;
	}
	function buildBuildingTableRow(r){
		var td = '';
		var td2 = '';
		tdnum = 1;
		if(r[2]) var r2 = ' class="'+r[2]+'"'; else var r2='';
		if(r[3]) var r3 = ' onclick=\'return confirm("'+r[3]+'");\''; else var r3='';
		if(r[4]) var r4 = " title='"+r[4].replace(/\#/g,':')+"'"; else var r4='';
		if(r[8]) {
			if(r[7]) var r7 = 'class="'+r[7]+'"'; else var r7='';
			td2 = '<td ><a href="/'+r[0]+'?z='+req_id+'" '+r7+'>'+r[8]+'</a></td>'; 
		}else{
			tdnum++;
		}
		if(r[6]) {
			if(r[5]) var r5 = 'class="'+r[5]+'"'; else var r5='';
			if(tdnum!==1){ var clsp='colspan="'+tdnum+'"'}else{ var clsp=''; }
			td += '<td '+clsp+'><a href="/'+r[0]+'?z='+req_id+'" '+r5+'>'+r[6]+'</a></td>'; 
			tdnum=1;
		}else{
			tdnum++;
		}
		if(r[9]) var r9 = 'class="'+r[9]+'"'; else var r9 = '';
		var tr = '<tr>';
		if(tdnum!==1){ var clsp='colspan="'+tdnum+'"'}else{ var clsp=''; }
		tr += '<th '+clsp+' '+r9+'><a href="/'+r[0]+'?z='+req_id+'" '+r2+r3+r4+'>'+r[1]+'</a></th>';
		tr += td + td2;
		tr += '</tr>';
		return tr;
	}
	function prepareTooltipContent(title,ttype){
		var str='';
		if( ttype == 'smarttip' ){
			str = title.replace(/\[/g,'<').replace(/\]/g,'>');
			return str;
		}else if(ttype == 'item'){
			var p = title.split('#');

			if(p[3]){ var item_title = p[3];}else{ var item_title = '';}
			if(p[2]){ var unq = p[2]; }else{ var unq = ''; }
			
			if(p[1]==1){
				var item_class = 'car';
				var span_desc_class = 'life';
				var span_info_class = 'defence';
			}else if(p[1]==2){
				var item_class = 'gun';
				var span_desc_class = 'life';
				var span_info_class = 'attack';
			}else if(p[1]==3){
				var item_class = 'armour';
				var span_desc_class = 'life';
				var span_info_class = 'attack';
			}else if(p[1]==4){
				var item_class = 'hooker';
				var span_desc_class = 'life';
				var span_info_class = 'sexapeal';
			}else if(p[1]==5){
				var item_class = 'drink';
				var span_desc_class = 'toxic';
				var span_info_class = 'stamina';
			}else if(p[1]==6){
				var item_class = 'drug';
				var span_desc_class = 'toxic';
				var span_info_class = 'stamina';
			}else if(p[1]==7){
				var item_class = 'uniq';
			}else if(p[1]==8){
				var item_class = 'grenade';
			}else if(p[1]==9){
				var item_class = 'cred';
			}else{
				var item_class = '';
			}
			if(p[7]){ var item_info_nobold = '<span class="'+span_info_class+'">'+p[7]+'</span>'; }else{ var item_info_nobold = ''; }
			if(p[6] ){
				var item_info = '<span class="'+span_info_class+'">'+p[6]+'</span> '+item_info_nobold;
			}else{
				var item_info = item_info_nobold;
			}
			if(p[5]){ var item_desc_nobold = p[5]; }else{ var item_desc_nobold = ''; }
			if(p[4] ){
				var item_desc = '<span class="'+span_desc_class+'">'+p[4]+'</span> '+item_desc_nobold;
			}else{
				var item_desc = item_desc_nobold;
			}
			item_desc = item_desc+'<br/>';
		
			str = "<table><tr>";
			if(p[0]) {
				str  += "<td><img src='"+StaticServer+"/srv/"+WORLD+"/item/"+p[0]+".jpg' alt='' class='good'/></td>";
			}
			str += "<td><div style='margin:5px' class='utip'>";
			str += '<b class="'+item_class+unq+'">'+item_title+'</b><br/>';
			str += item_desc;
			str += item_info;
			if(p[8]){
				str += '<b class="life">'+p[6]+'</b> '+item_info_nobold;
			}
			if(p[9]){
				p[9] = p[9].replace(/\[/g,'<').replace(/\]/g,'>');
				str += p[9];
			}			
			str += "</div></td></tr></table>";
			return str;
		}else if(ttype == 'avatar'){
			var p = title.split(':');
			str = "<table><tr>";
			if(p[0] == 'u')	{
				var path = StaticServer+'/srv/'+WORLD+'/avt/';
				if(p[2]) str += "<td><img src='"+path+p[2]+".gif' alt='' class='avt'/></td>";

				if(p[7]){
					str += "<td><b class='"+p[8]+"'>"+p[7]+"</b>&nbsp;<b class='cc"+p[3]+"'> </b> <br/>";
				}else{ 
					str += "<td><b class='cc"+p[3]+"'>"+p[1]+"</b><br/>";
				}
				if(p[4]) str += "<b class='respect'>"+p[4]+"</b><br/>";
				if(p[5]) str += "<b class='victory'>"+p[5]+"</b><br/>";
				if(p[6]) str += "<b class='loss'>"+p[6]+"</b>";
				str += "</td>";
			}else{
				var path = StaticServer+'/srv/'+WORLD+'/gavt/';
				if(p[0]!=='g' && p[0]!=='u') str += "<td>"+p[0]+"</td>";
				if(p[1]) str += "<td><img src='"+path+p[1]+".gif' alt='' class='avt'/></td>";
				str += "<td>";
				if(p[2]) str += "<b class='respect'>"+p[2]+"</b><br/>";
				if(p[5] && p[5]!='') str += "<b class='gang'>"+p[5]+"</b><br/>";
				if(p[3]) str += "<b class='victory'>"+p[3]+"</b><br/>";
				if(p[4]) str += "<b class='loss'>"+p[4]+"</b>";
				str += "</td>";
			}
			str += "</tr></table>";
			return str;
		}else if(ttype == 'sms'){
			var p = title.split(';');
			for(i=0; i<p.length; i++){
				var kv = p[i].split(':');
				str += $.trim(kv[0])+" - <b>"+$.trim(kv[1])+"</b><br/>";
			}
			return str;
		}else if(ttype == 'tablecell'){
			var p = title.split(':');
			if(p[0]==undefined || (p[0]+'').length==0) return;
			if(p[0]) {
				str  = "<img src='"+StaticServer+"/srv/"+WORLD+"/item/"+p[0]+".jpg' alt='' class='good'/>";
			}
			if(p[1]!=null && p[1]!=undefined)
				if((p[1]+'').length>=1) {
					p[1] = p[1].split('%JS_ATK%').join("<span class='attack sicon'> </a>");
					str += "<span class='ginfo'>"+p[1]+"</span>";
			}
			return str;
		}else if(ttype == 'mapObjects'){
			var t = title.split(':');
			str = t[0];
			if(parseInt(t[1]))
			{
				var gs = (t[1] < 100)?'galert':(t[1] < 250)?'gwarn':'gok';
				var glife = Math.round(t[1]/10);
				str += "<div class='blife'><div class='gwrap'><div class='ggauge'><div class='"+gs+"' style='width:"+glife+"%'></div></div><em>"+t[1]+"</em></div></div>";
			}
			if(t[2]!=null && t[2]!=undefined) str += "<div class='top10'>"+t[2]+"</div>";
			return str;		
		}
	}
	function Locate(event){
		var d = document.getElementById('tt');
		if(!d) return;
		var pos = mouseXY(event);
		if(DIRECTION == 'rtl'){
			d.style.left = ( pos[0] > TIP_DIM.w +15 )? (pos[0]-CACHED_WINDOW.rx-TIP_DIM.w-15)+'px' : (elPosX = pos[0]-CACHED_WINDOW.rx + 15)+'px';
		}else{
			d.style.left = ( CACHED_WINDOW.w  > (pos[0]+TIP_DIM.w+15) )? pos[0]+15+'px' : (pos[0]-TIP_DIM.w - 15)+'px';
		}
		d.style.top = ( CACHED_WINDOW.h+CACHED_WINDOW.y- CACHED_WINDOW.ry < (pos[1] + 5 + TIP_DIM.h) ) ? (pos[1] + CACHED_WINDOW.ry - TIP_DIM.h-5)+'px' : (pos[1]+CACHED_WINDOW.ry+5)+'px';
	}
	function LocateMenu(event,menu_id){
		var elPosX = 0;
		var elPosY = 0;
		TIP_DIM.w = menu_id.width();
		TIP_DIM.h = menu_id.height();
		var pos = mouseXY(event);
		if( CACHED_WINDOW.h+CACHED_WINDOW.y - CACHED_WINDOW.ry < (pos[1]- CACHED_WINDOW.ry - 10 + TIP_DIM.h) ) elPosY = pos[1] + CACHED_WINDOW.ry - TIP_DIM.h+10; else elPosY = pos[1] + CACHED_WINDOW.ry-10;
		if(DIRECTION=='rtl'){
			if( pos[0] > TIP_DIM.w -10){
				elPosX = pos[0]-CACHED_WINDOW.rx-TIP_DIM.w + 10 ;
			}else{
				elPosX = pos[0]-CACHED_WINDOW.rx - 10;
			}
		}else{
			if( CACHED_WINDOW.w  > (pos[0]+TIP_DIM.w-10)){
				elPosX = pos[0] - 10;
			}else{
				elPosX = pos[0]-TIP_DIM.w+10 ;
			}
		}
		menu_id.css({   left: elPosX, 
						top:  elPosY, 
						visibility : 'visible'
		});
	}
	function mouseXY(e){
		if(CACHED_WINDOW.ie){ 
			var x=e.pageX-CACHED_WINDOW.x,y=e.pageY-CACHED_WINDOW.y;
		}else{
			var x=e.pageX,y=e.pageY;
		}
		return [x,y];
	}
	$.fn.doBlink = function() {
		BLINKER = $(this);
		if(BLINKER_STATE==0){
			BLINKER_STATE++;
			setTimeout(function(){ 
				BLINKER.doBlink();
			 }, 4000);
		}else{
			if(BLINKER_STATE++%2 == 0) {
				BLINKER.css({opacity:0.01,filter:'alpha(opacity=1)'});
				setTimeout(function(){ 
					BLINKER.doBlink();
				 }, 200);
			}
			else {
				BLINKER.css({opacity:1,filter:'alpha(opacity=100)'});
				setTimeout(function(){ 
					BLINKER.doBlink();
				 }, 500);
			}
		}
	}
})(jQuery);
function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	//console.log("readCookie " + name);
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
function createCookie(name,value,days) 
{
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; domain="+COOKIE_DOMAIN+"; path=/";
	//if(GID==105) alert(name+"="+value+expires+"; path=/");
	//console.log("createCookie " + name+"="+value+expires+"; domain="+COOKIE_DOMAIN+"; path=/");
}
function onDocClick(e) 
{
	//if(GID==105) alert('click!');
	var posx = 0;
	var posy = 0;
	if (!e) var e = window.event;
	if (e.pageX || e.pageY) 	
	{
		posx = e.pageX;
		posy = e.pageY;
	}
	else if (e.clientX || e.clientY) 	
	{
		posx = e.clientX + CACHED_WINDOW.x;
		posy = e.clientY + CACHED_WINDOW.y;
	}
	var cc = Math.floor(posy*10000 + posx*1.0);
	createCookie('clickcoords',posy*10000 + posx);
}

function keep_session() {
  $.get( "/sess_ping.php", Math.round(Math.random()*1000),
    function(data) { 
		setTimeout("keep_session()", 10*60*1000);
    }
  );
}
function mapInit(){
	$('a[class^=spot]','#map').initTooltip({
			tooltipType:'mapObjects',
			tooltipClass:''
	});
}
function smarttipInit(){
	$(".smarttip",'#bg-wall').initTooltip({
			tooltipType:'smarttip',
			tooltipClass:'stip'
	});
}
function avatarInit(){
	$("a.nad",'#bg-wall').initTooltip({
			tooltipType:'avatar',
			tooltipClass:'utip'
	});
	$("a.gang",'#bg-wall').initTooltip({
			tooltipType:'avatar',
			tooltipClass:'utip'
	});
	$("a.utt",'#bg-wall').initTooltip({
			tooltipType:'avatar',
			tooltipClass:'utip'
	});
}
function formatsmsInit(){
	$(".sms",'#bg-wall').initTooltip({
			tooltipType:'sms',
			tooltipClass:'utip'
	});
}
function tablecellInit(){
	$("td.good",'#ttp,#ttp1,#ttp2,#ttp3,#ttp4,#ttp5,#ttp6,#ttp7,#ttp8,#ttp9,#ttp10').initTooltip({
			tooltipType:'tablecell',
			tooltipClass:'gtip'
	});
	$("span.good",'#ttp,#ttp1,#ttp2,#ttp3,#ttp4,#ttp5,#ttp6,#ttp7,#ttp8,#ttp9,#ttp10').initTooltip({
			tooltipType:'tablecell',
			tooltipClass:'gtip'
	});
	$("th.good",'#ttp,#ttp1,#ttp2,#ttp3,#ttp4,#ttp5,#ttp6,#ttp7,#ttp8,#ttp9,#ttp10').initTooltip({
			tooltipType:'tablecell',
			tooltipClass:'gtip'
	});
}
function itemInit(){
	$("td.item",'#ttp,#ttp1,#ttp2,#ttp3,#ttp4,#ttp5,#ttp6,#ttp7,#ttp8,#ttp9,#ttp10').initTooltip({
			tooltipType:'item',
			tooltipClass:'utip'
	});
	$("span.item",'#ttp,#ttp1,#ttp2,#ttp3,#ttp4,#ttp5,#ttp6,#ttp7,#ttp8,#ttp9,#ttp10').initTooltip({
			tooltipType:'item',
			tooltipClass:'utip'
	});
	$("th.item",'#ttp,#ttp1,#ttp2,#ttp3,#ttp4,#ttp5,#ttp6,#ttp7,#ttp8,#ttp9,#ttp10').initTooltip({
			tooltipType:'item',
			tooltipClass:'utip'
	});
}

$(document).ready(function(){	
	$('<div id="tt" class="tooltip" style="position:absolute;z-index:20000;display:inline-block;"></div>').appendTo('body');
	refreshCachedWindow();
	// tooltip
	mapInit();
	smarttipInit();
	avatarInit();
	tablecellInit();
	formatsmsInit();
	itemInit();
	// blinker
	$('#blinker').doBlink();
	if(AUTOLOGIN) {
		setTimeout("keep_session()", 10*60*1000 );
	}
	createCookie('clickcoords',0);
	$(document).click( function(e){ 
		onDocClick(e);
	});
	setTimeout(function(){ $('#flid').css({'display':'inline'}); }, 30);
	$(document).find('.cbxmain').click(function(){
		var checked = $(this).attr("checked");
		var arr_name = this.name.replace('[all]', '[]');
		tmp = $(this).parents('table:first').find('input[type=checkbox][name^='+arr_name+']').attr({"checked":checked}); 
	});
	$(".customselect").show().selectbox();
	if(typeof(NO_COUNTER) === 'undefined'){ 
		if(typeof(COUNTER_UID)!=='undefined'){
			if(COUNTER_UID!==''){
				var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
				//Load script
				jQuery.getScript(gaJsHost + "google-analytics.com/ga.js", function()
				{
					try {
						var pageTracker = _gat._getTracker(COUNTER_UID);//"UA-9110286-1"
						pageTracker._trackPageview();
					}
					catch(err){}		
				});
			}
		}
	} 
	$(".marketWr a").click(function(e){
		$('.marketWr a').removeClass("selected");
		var cname = "#" + $(this).attr("class");
		$('#marketTable').html($(cname).html());
		$(cname).show();
		$(this).addClass("selected");
	});
	/* read qmsg */
	function bind_msg_click()
	{
		$('.qmsgo').each(function(i,n){
			$(n).click(function()
			{
				var mid = $(this).attr('mid');
				$.ajax({
				   type: "GET",
				   url: "http://" + location.host + "/qmsg/markoneread?mid=" + mid,
				   success: function(html){
					 //alert( html );
					 $('.quick-msg').children().remove();
					 $('.quick-msg').append(html);
					 bind_msg_click();
				   }
				 });
			});
		});
	}
	bind_msg_click();
	/* poll answer */
	function bind_poll_click()
	{
		$('.polla').each(function(i,n){
			$(n).click(function()
			{
				var pid = $(this).attr('pid');
				var oid = $(this).attr('oid');
				$.ajax({
				   type: "GET",
				   url: "http://" + location.host + "/poll/vote/" + pid + "/" + oid + "/poll",
				   success: function(html){
					 $('#poll').fadeOut(300, function() {
						 $('#poll').children().remove();
						 $('#poll').append(html);
						 $('#poll').fadeIn('slow');
					 bind_poll_click();
					 });
				   }
				 });
			});
		});
	}
	bind_poll_click();
	
});	
$(window).resize(function() {
  refreshCachedWindow();
});
$(window).scroll(function() {
  refreshCachedWindow();
});
function refreshCachedWindow(){
	// get cached document properties
	if($.browser.msie) CACHED_WINDOW.ie = true;
	if(CACHED_WINDOW.ie){
		CACHED_WINDOW.x = $(window).scrollLeft(),
		CACHED_WINDOW.y = $(window).scrollTop(),
		CACHED_WINDOW.w = $(window).width(),
		CACHED_WINDOW.h = $(window).height(),
		CACHED_WINDOW.rx = $(document).width() - CACHED_WINDOW.w - CACHED_WINDOW.x -20	,	
		CACHED_WINDOW.ry = CACHED_WINDOW.y		
	}else{
		CACHED_WINDOW.x = $(window).scrollLeft(),
		CACHED_WINDOW.y = $(window).scrollTop(),
		CACHED_WINDOW.w = $(window).width(),
		CACHED_WINDOW.h = $(window).height(),
		CACHED_WINDOW.rx = 0,	
		CACHED_WINDOW.ry = 0		
	}
}
var CACHED_WINDOW = {
	// cached window properties
	w : 0,
	h : 0,
	x : 0,
	y : 0,
	rx : 0,
	ry : 0,
	ie: false
}
//GOOGLE TRANSLATION
function translateText(text, to_lang, callback, obj)
{
	if(text.length > 0 && to_lang.length > 0) 
	{
		google.language.detect(text, function(result)
		{
			if(!result.error) 
			{
				lang = result.language;
				google.language.translate(text, lang, to_lang, function(result) {
					if(result.translation) {
						if(typeof(callback) == "function")
						{
							callback.call(null, result.translation, lang, to_lang, obj);
						}
					}
				});
			}
		});
	}
}

function showPromoHint(hintText)
{
	if(hintText.length > 0) {
		$('div#hint-container').append("<div class='promo-hint-container'><div id='promo-hint' class='promo-hint'>" + hintText + "</div></div>");
		$('#promo-hint').toggle();
	}
}

function openWindow(url, setArr)
{
	if(!setArr || setArr == 'undefined') {
		setArr = ['menubar=no', 'resizable=no', 'width=382', 'height=465', 'status=no', 'toolbar=no'];
	}
	
	var date = new Date();
	var windowName = 'wnd_'+date.getTime();
	
	var wndHandle = window.open(url, windowName, setArr.join(', '));
	return wndHandle;
}

try{document.execCommand("BackgroundImageCache", false, true);}catch(e){}
/* all */
function checkall(form_id, checkall_id, checkbox_array_name){
	if(!document.getElementById) return;
	var frm = document.getElementById(form_id);
	var checkall = document.getElementById(checkall_id);
	if(!(frm && checkall)) return;
	for(var i=0; i<frm.elements.length; i++)
	{
		var cbx = frm.elements[i];
		if(cbx.name == checkbox_array_name) cbx.checked = checkall.checked;
	}
}
/* msg.mod.php */
function set_recp(name){
	if(name){
		var s = document.getElementById(name);
		var d = document.getElementById('recp');
		if(s && d) d.value = s.value;
	}
	var s1 = document.getElementById('friends');
	if(s1) s1.value='';
	var s2 = document.getElementById('strangers');
	if(s2) s2.value='';
}
/* base.tpl.php */
function toggleid(id){
	var e = document.getElementById(id);
	if(e) {
		if(e.style.display == 'block') e.style.display = 'none';
		else e.style.display = 'block';
	}
}
function showid(id){
	var e = document.getElementById(id);
	if(e) e.style.display = 'block';
}
function hideid(id){
	var e = document.getElementById(id);
	if(e) e.style.display = 'none';
}
function nextid(current_id, next_id){
	hideid(current_id);
	showid(next_id);
	return false;
}
/* chat.mod.php */
function toggleChatScroll(){
	var e = document.getElementById("chat-messages");
	if(e){
		if(e.className == "chat-messages")
		{
			e.className = "chat-messages-scroll";
		}
		else e.className = "chat-messages";
	}
	return false;
}
function refreshChat(){
	var cr = document.getElementById('chat_refresher');
	cr.submit();
	setTimeout('refreshChat()',10000);
}
function onBeforeSendMessage(){
	var _msg = document.getElementById('_msg');
	var msg = document.getElementById('msg');
	if(!_msg || !msg) return;
	msg.value = _msg.value;
	_msg.value = '';
	setTimeout('focusById("_msg")',150);
	return true;
}
function focusById(id){
	var e = document.getElementById(id);
	if(e) {
		if(e.focus) e.focus();
		else if(e.setActive) e.setActive();
	}
}
function onGoToPage(){
	var pnum = document.getElementById('pnum');
	var linktpl = document.getElementById('linktpl');
	if(pnum){
		var page = parseInt(pnum.value);
		if(page == NaN || page<0) page=1;
		arr = linktpl.value.split('%p');
		document.location.href = arr.join(page);
	}
	return false;
}
function onBeforeSearch(new_search){
	var stype = document.getElementById('search[type]');
	var bprice = document.getElementById('search[b_price]');
	var tprice = document.getElementById('search[t_price]');
	var item_type = document.getElementById('item_type');
	var item = item_type.value;
	var gotopagefrm = document.getElementById('gotopage');
	if(stype || bprice || tprice){
		var stype = parseInt(stype.value);
		var bprice = parseInt(bprice.value);
		var tprice = parseInt(tprice.value);
		if(stype==NaN || stype<0) stype=0.0;
		if(bprice==NaN || bprice<0) bprice=0.0;
		if(tprice==NaN || tprice<0) tprice=0.0;
		var args = gotopagefrm.value.split('/');
		args[3]=stype+','+bprice+','+tprice;
		if(new_search){
			args[4]=0;
			result=false;
		}
		else result=true;
		document.location.href =args[0]+"/"+ args[1]+"/" +args[2]+"/" + item + "/" + args[3]+"/"+args[4];
	}
	return result;
}
/*
	Common functions
	------------------------------------------------
*/
var _BROWSER=0; // Unknown browser
var _IE=2;
var _MOZILLA=3;
var _OPERA=3;

function checkBrowser(string){
	var detect = navigator.userAgent.toLowerCase();
	place=detect.indexOf(string)+1;
	return place;
}
if(checkBrowser('msie')) _BROWSER=_IE;
else if(checkBrowser('opera')) _BROWSER=_OPERA;
//else if(checkBrowser('msie')) _BROWSER=_OPERA;

function random_int(min, max){
	var range = max-min+1;
	return (Math.floor( Math.random()*Math.pow(10,("" + range).length)) % range) + parseInt(min);
}
function toggle_display(id){
	var e = document.getElementById(id);
	if(e) e.style.display = (e.style.display == '' || e.style.display == ' ' || e.style.display == 'none') ? 'block' : 'none';
	return false;
}
function hide_msg_box(id){
	var e = document.getElementById(id);
	if(e) e.style.display = (e.style.display == '' || e.style.display == ' ' || e.style.display == 'block') ? 'none' : 'block';
	return false;
}
function confirmMove(){
	var msg_form = document.getElementById('msg');
	if(!msg_form) return false;
	else msg_form.action = '?q=msg/move/inbox';
	return true;
}
function secureConfirm(msg,secure_text){
	var default_text='';
	var ret = window.prompt(msg,default_text);
	if(ret)
	{
		secure_text = secure_text.toLowerCase();
		ret = ret.toLowerCase();

		if(ret == 'OK') ret = 'ok';
		if(secure_text == 'OK') secure_text = 'ok';
		console.log('ret '+ret);
		console.log('secure_text '+secure_text);
		return (ret==secure_text);
	}
	else return false;
}
function replacesel(oTextbox, sText){
	var isOpera = navigator.userAgent.indexOf("Opera") > -1;
	var isIE = navigator.userAgent.indexOf("MSIE") > 1 && !isOpera;
	var isMoz = navigator.userAgent.indexOf("Mozilla/5.") == 0 && !isOpera;

	oTextbox = document.getElementById(oTextbox);
	if(!oTextbox) return;

	oTextbox.focus();
	if(isIE){
		var oRange = document.selection.createRange();
		oRange.text = sText;
		oRange.collapse(true);
		oRange.select();
	}
	else //if (isMoz)
	{
		var iStart = oTextbox.selectionStart;
		oTextbox.value = oTextbox.value.substring(0, iStart) + sText + oTextbox.value.substring(oTextbox.selectionEnd, oTextbox.value.length);
		oTextbox.setSelectionRange(iStart + sText.length, iStart + sText.length);
	}
	oTextbox.focus();
}
function copyValById(from, to){
	var el_from = document.getElementById(from);
	var el_to = document.getElementById(to);
	if(el_from && el_to)
		el_to.value = el_from.value;
}
function setRadioByIdIfVal(id,editbox_id){
	var editbox = document.getElementById(editbox_id);
	if(editbox)
		if(editbox.value+0 != 0)
			setRadioById(id);
}
function setRadioById(id){
	var radio = document.getElementById(id);
	if(radio) radio.checked=1;
}
/* Shows/hides particular html element that corresponds to a radio control */
var _LAST_CLICKED_RADIO = null;
var _LAST_CLICKED_RADIO_BOX = null;
function ShowIfRadio(radio_id,element_id){
	var radio = document.getElementById(radio_id);
	var element = document.getElementById(element_id);

	if(_LAST_CLICKED_RADIO && _LAST_CLICKED_RADIO_BOX){
		_ShowIfRadio(_LAST_CLICKED_RADIO,_LAST_CLICKED_RADIO_BOX);
	}

	if(_ShowIfRadio(radio,element))
	{
		_LAST_CLICKED_RADIO = radio;
		_LAST_CLICKED_RADIO_BOX = element;
	}
}
function _ShowIfRadio(radio,element){
	if(radio && element)
	{
		if(radio.checked==1)	element.style.display = 'block';
		else					element.style.display = 'none';
		return true;
	}
	return false;
}
function trim(s){
	var l=0; var r=s.length -1;
	while(l < s.length && s[l] == ' ')
	{	l++; }
	while(r > l && s[r] == ' ')
	{	r-=1;	}
	return s.substring(l, r+1);
}
//Expand/Collapse help bar
function toggle_help(expand){
	var help_short = $('div.help-short');
	var help_long = $('div.help-long');
	if(expand==0){
		help_short.css('display','block');
		help_long.css('display','none');
	}else{
		help_short.css('display','none');
		help_long.css('display','block');
	}
	return false;
}
//Expand/Collapse extra box
function toggle_extra(expand, extra){
	var button_expand = $('#'+extra+'-expand');
	var button_hide = $('#'+extra+'-hide')
	var box = $('tr.'+extra+'-box');
	if(expand==0){
		button_expand.css('display','block');
		button_hide.css('display', 'none');
		box.css('display','none');
	}else{
		button_expand.css('display','none');
		button_hide.css('display', 'block');
		box.css('display','table-row');
	}
	return false;
}
//Countdown Clock
var clocks = new Array(),
	clocksCount = 0,
	clockIntervalHandle = null;

//-------------------------------------------------------------------------------------------------
;(function(jQuery)
{
	jQuery.fn.extend(
	{

		csets : {
				format: 'short',
				locale_days: 'days'
		},

		cClockInit: function(settings)
		{
			jQuery(this).csets = jQuery.extend(jQuery(this).csets,settings);

			jQuery(this).each(function()
			{
				var self = this;
				//Set the Daytime to work with
				self.display = jQuery(this);
				var rel_attr = jQuery(this).attr('rel');
				var rel_attr_arr = rel_attr.split(',');
				self.period = rel_attr_arr[0]*1.0;
				self.targetTime = rel_attr_arr[1]*1.0;
				//self.startTime = new Date().valueOf();
				self.startTime = new Date().valueOf();
				self.serverStartTime = TIME;
				self.timeDrift = self.serverStartTime - self.startTime;
				clocks[clocksCount] = this;
				clocksCount++;
			});
			//ъпдейтва часовниците (ай ся дай ся)
			window.setTimeout(jQuery(this).clockWatcher, 500);
		},
		clockWatcher : function()
		{
			var curTime = new Date().valueOf();

			var stopInterval = true;
			for(var i=0; i<clocksCount; ++i)
			{
				//console.log('_tick');
				//Този часовник е спрян
				var clock = clocks[i];
				if(!clock) continue;

				var passedTime =  Math.floor((curTime-clock.startTime)/1000); // в sec

				if(clock.targetTime > 0) {
					clock.remainingTime = clock.targetTime - clock.serverStartTime - passedTime;
				}
				else {
					clock.remainingTime = clock.period - passedTime; 
				}

				//make sure success hasn't been reached
				if(clock.remainingTime < 0)
				{
					//спирам да го ъпдейтвам тоя часовник
					clocks[clocksCount] = null;
					//и последно 10 - всичко става нули
					clock.display.html('00:00:00');
				}
				else {
					//дай новите стойности
					var seconds = clock.remainingTime;
					//console.log(seconds);
					var day = (Math.floor(seconds/86400));
					var hrs = (Math.floor(seconds/3600))%24;
					var min = (Math.floor(seconds/60))%60;
					var sec = (Math.floor(seconds/1))%60;

					//Малко нулички
					hrs = (hrs+'').length<2?'0'+hrs:hrs;
					min = (min+'').length<2?'0'+min:min;
					sec = (sec+'').length<2?'0'+sec:sec;

					//console.log(jQuery(this).csets.format);
					//ъпдейт на часовника
					var days='';
					if(day>0) days = day+' '+jQuery(this).csets.locale_days+' ';
					clock.display.html(days+hrs+':'+min+':'+sec);

					//след малко пак ще ъпдейтвам
					stopInterval = false;
				}
			}
			if(!stopInterval) window.setTimeout(jQuery(this).clockWatcher, 500);
			//else console.log('Stop machines!');
		}
	});

	//Init
	jQuery.fn.countdownClock = jQuery.fn.cClockInit;

})(jQuery);

/* xs terms iframe - login page */

$(document).ready(function() {	
	$('#more_terms_link').click(function(e) {
		$('<div id="boxes"><div id="termsdialog" class="window"><table width="100%" border="0" cellspacing="0" cellpadding="15"><tr><td class="l"><h2 id="termsdialog_title"></h2></td><td class="r"><a href="#" id="termsclose" class="exit-btn close"/></a></td></tr></table><iframe width="950" style="margin:0 10px;" height="550" frameborder="0" scrolling="no" id="termsdialog_content"></iframe></div><div id="mask"></div></div>').appendTo('body'); 
		// alert('aaa');						  
		//Cancel the link behavior
		e.preventDefault();
		//Get the A tag
		var hrf = $(this).attr('href');
		var title = $(this).attr('title');

		//Get the screen height and width
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
	
		//Set heigth and width to mask to fill up the whole screen
		$('#mask').css({'width':maskWidth,'height':maskHeight});
		
		//transition effect		
		$('#mask').show();	
		$('#mask').fadeTo("slow",0.8);	
	
		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();
 
 		var modalWidth = $('#termsdialog').width();
		var modalHeight = $('#termsdialog').height();

 
		//Set the popup window to center
		$('#termsdialog').css('top',  130);
		$('#termsdialog').css('left', winW/2-$('#termsdialog').width()/2);
	
		// load content
		$('#termsdialog').show();
		$("#termsdialog_content").attr('src',hrf);
		$('#termsdialog_title').html(title);
		$('#termsdialog')
				.bind('dragstart',function( event ){
						return $(event.target).is(' table tr:first');
						})
				.bind('drag',function( event ){
						$( this ).css({
								top: event.offsetY,
								left: event.offsetX
								});
						}); 
		//if close button is clicked
		$('#termsclose').click(function (e) {
			//Cancel the link behavior
			$('#mask').hide();
			$('.window').hide();
		});		
		
		//if mask is clicked
		$('#mask').click(function () {
			$(this).hide();
			$('.window').hide();
		});			
	
	});
	
});
