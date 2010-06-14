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

	Ape.registerHookCmd("CONNECT", function(params, cmd)
	{
		if (!$defined(params.username) || !$defined(params.password))
		{
			//未提供用户名和密码 ＝ 游客
			cmd.user.setProperty('username','guest_'+$random(100000,999999));
			cmd.user.setProperty('password','');
			return 1;
		}
		if (userlist.has(params.username.toLowerCase()))
		{
			return ["007", "NICK_USED"];
		}
		if (params.username.length > 16 || params.username.test('[^a-zA-Z0-9]', 'i'))
		{
			return ["006", "BAD_NICK"];
		}
		//http_auth("http://127.0.0.1/crimsui/chat.php", params, function(result) {
		http_auth("http://192.168.1.43/crimsui/chat.php", params, function(result) {
			Ape.log('http_auth - '+$time()+' - result: '+result);
			if (result == 1)
			{
				cmd.user.setProperty('username', params.username);
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
		userlist.set(user.getProperty('username').toLowerCase(), true);	
	});

	Ape.addEvent('deluser', function(user) {
		userlist.erase(user.getProperty('username').toLowerCase());
	});
})();
