function http_request(url, params, callback)
{
	var request = new Http(url);
	request.set('method', 'GET');
	request.getContent(function(result) {
		var ret = {};
		try
		{
			ret = JSON.parse(result);
		}
		catch(e)
		{
		};
		callback(ret);
	});	
}
var streetMap = new $H;
var CRIMES_Server = new Class({
	//Properties
	channel: 'crims',
	street: {
		width: 614,
		height: 374
	},
	channels: new Hash(),
	django: 'http://127.0.0.1:8888',
	//Init
	initialize: function()
	{
		Ape.log('[Module] Crimes started !');
		this.registerEvents();
		this.registerCommands();
	},
	//Functions
	registerEvents: function()
	{
		Ape.addEvent('init', this.init.bind(this));
		// Ape.addEvent('mkchan', this.mkchan.bind(this));
		// Ape.addEvent('beforeJoin', this.beforeJoin.bind(this));
		// Ape.addEvent('afterJoin', this.afterJoin.bind(this));
	},
	registerCommands: function()
	{
		Ape.registerCmd('LOADMAP', true, this.loadMap.bind(this));
	},
	//Events/Commands functions
	init: function()
	{
		var global_channel = Ape.mkChan(this.channel);
		http_request(this.django+'/map/', {}, function(result)
		{
			for(var i=0; i<result.length; i++)
			{
				var n = 'street'+result[i].owner;
				if(!$defined(Ape.getChannelByName(n)))
				{
					var channel = Ape.mkChan(n);
					channel.x = result[i].x;
					channel.y = result[i].y;
					channel.buildings = result[i].buildings||[];
					channel.NPC = result[i].NPC||[];
					streetMap.set(result[i].x+'~'+result[i].y, n);
					Ape.log('init: '+channel.getProperty('name')+', total buildings: '+channel.buildings.length+', total NPC: '+channel.NPC.length+', total users: '+channel.userslist.getLength());
				}
			}
		});
	},
	loadMap: function(params, infos)
	{
		//1. 首先在 APE channel 查找是否已经存在，如果存在，组织成 array 返回给 JSF；
		//2. 如果全部不存在，调用 django 返回列表，APE mkchan()；
		//3. 如果部分不存在，调用 django 
		Ape.log('LOADMAP');
		var tmp = params.x+'~'+params.y;
		Ape.log(tmp);
		var result = [];
		if(streetMap.get(tmp))
		{
			var c = Ape.getChannelByName(streetMap.get(tmp));
			infos.user.join(c);
			result.push(c);
		}
		Ape.log(result.length);
		infos.user.pipe.sendRaw('MAP_DATA', result);
	}
});

new CRIMES_Server();