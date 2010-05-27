var userlist = new $H;

Ape.registerHookCmd("connect", function(params, cmd) {

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
	Ape.log('nickname - '+$time());
	// cmd.user.setProperty('name', params.name);	//公开属性
	// //cmd.user.password = params.password;	//私有属性
	// cmd.user.setProperty('password', params.password);
	
	return 1;
});

Ape.addEvent('adduser', function(user) {
	userlist.set(user.getProperty('name').toLowerCase(), true);	
});

Ape.addEvent('deluser', function(user) {
	userlist.erase(user.getProperty('name').toLowerCase());
});
