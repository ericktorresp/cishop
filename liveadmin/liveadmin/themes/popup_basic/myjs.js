
LiveAdmin.myjs = {};

LiveAdmin.myjs.resize = function(event)
{
	if(this.resize_sema)
		return;

	this.resize_sema = true;

	var pgsize = this.getPageSize();

	var el = document.getElementById('liveadmin');
	if(el && el.style)
	{
		if(pgsize['windowHeight']>300)
		{
			var toset = pgsize['windowHeight']-21;


			if(!el.style.height || el.style.height!=toset+'px')
			{
				el.style.height = (toset)+'px';
				var el2 = document.getElementById('message_id');
				if(el2 && el2.style)
				{
					if(this.resizeChatScreenTimeOut!=-1)
						window.clearTimeout(this.resizeChatScreenTimeOut);
					el2.style.height = '2px';
					this.resizeChatScreenTimeOut = window.setTimeout(LiveAdmin.myjs.resizeChatScreen,100);
				}
			}
		}
	}


	this.resize_sema = false;
};

LiveAdmin.myjs.resizeChatScreen = function()
{
	var el2 = document.getElementById('message_id');
	var toset = document.getElementById('message_id_td_id').offsetHeight-20;

	if(toset<50)
		toset = 50;

	el2.style.height = (toset)+'px';
	this.resizeChatScreenTimeOut = -1;
};

LiveAdmin.onAfterShowChatScreen = function()
{
	LiveAdmin.myjs.resizeChatScreen();
};

LiveAdmin.myjs.getPageSize = function()
{
	var xScroll, yScroll;

	if (window.innerHeight && window.scrollMaxY) {
		xScroll = window.innerWidth + window.scrollMaxX;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else {
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight;

	if (self.innerHeight) {
		if(document.documentElement.clientWidth){
			windowWidth = document.documentElement.clientWidth;
		} else {
			windowWidth = self.innerWidth;
		}
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) {
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) {
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else {
		pageHeight = yScroll;
	}

	if(xScroll < windowWidth){
		pageWidth = xScroll;
	} else {
		pageWidth = windowWidth;
	}
	return {'pageWidth':pageWidth,'pageHeight':pageHeight,'xScroll':xScroll,'yScroll':yScroll,'windowWidth':windowWidth,'windowHeight':windowHeight};
};

LiveAdmin.myjs.getElementHeight = function(el)
{
	var windowHeight=0;
	if (el.innerHeight)
	{
		windowHeight = el.innerHeight;
	}
	else if (el.clientHeight)
	{
		windowHeight = el.clientHeight;
	}
	return(windowHeight);
};

LiveAdmin.myjs.Initialize = function()
{

	this.resize_sema = false;
	this.resizeChatScreenTimeOut = -1;

	if (window.addEventListener)
		window.addEventListener('resize',function(event) { LiveAdmin.myjs.resize(event); },false);
	else if(window.attachEvent)
		window.attachEvent('onresize',function(event) { LiveAdmin.myjs.resize(event); });


	this.resize(null);

};

