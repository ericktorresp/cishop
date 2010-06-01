var userlist = new $H;

Ape.registerHookCmd("connect", function(params, cmd) {

	if (!$defined(params.username) || !$defined(params.password))
	{
		return 0;
	}
	if (userlist.has(params.username.toLowerCase()))
	{
		return ["007", "NICK_USED"];
	}
	if (params.username.length > 16 || params.username.test('[^a-zA-Z0-9]', 'i'))
	{
		return ["006", "BAD_NICK"];
	}
	Ape.log('nickname - '+$time());
	 cmd.user.setProperty('username', params.username);	//公开属性
	// //cmd.user.password = params.password;	//私有属性
	 cmd.user.setProperty('password', params.password);
	
	return 1;
});

Ape.addEvent('adduser', function(user) {
	userlist.set(user.getProperty('username').toLowerCase(), true);	
});

Ape.addEvent('deluser', function(user) {
	userlist.erase(user.getProperty('username').toLowerCase());
});
