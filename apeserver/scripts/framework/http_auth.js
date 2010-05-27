var userlist = new $H;
(function(){
	function http_auth(url, params, callback)
	{
		var request = new Http(url);
		request.set('method', 'POST');
		request.writeObject(params);
		request.getContent(function(result) {
			var ret = {};
			try { ret = JSON.parse(result); } catch(e){};
		
			callback(ret);
		});	
	}

	Ape.registerHookCmd("CONNECT", function(params, cmd) {
		if (!$defined(params.name) || !$defined(params.password))
		{
			return 0;
		}
		if (userlist.has(params.name.toLowerCase()))
		{
			return ["007", "NICK_USED"];
		}
		if (params.name.length > 16 || params.name.test('[^a-zA-Z0-9]', 'i'))
		{
			return ["006", "BAD_NICK"];
		}
		http_auth("http://127.0.0.1/crimsui/chat.php", params, function(result) {
			Ape.log('http_auth - '+$time()+' - result: '+result);
			if (result == 1)
			{
				cmd.user.setProperty('name', params.name);
				cmd.user.setProperty('password', params.password);
				Ape.addUser(cmd.user);
			}
			else
			{
				cmd.sendResponse("ERR", {"code":"206",'value':'LOGIN_FAILD'});
			}
		});
	
		return -1;
	});
	Ape.addEvent('adduser', function(user) {
		userlist.set(user.getProperty('name').toLowerCase(), true);	
	});

	Ape.addEvent('deluser', function(user) {
		userlist.erase(user.getProperty('name').toLowerCase());
	});
})();
