Ape.registerCmd("LOADMAP", true, function(params, infos) {
	Ape.log("Received LOADMAP");
	var msg = '';
	for(key in params)
	{
		msg += key + ' = ' + params[key]+ ' | ';
	}
	Ape.log(msg);
	Ape.log('==================');
	msg = '';
	for(key in infos)
	{
		if(key == 'client')
		{
			msg += 'client: ';
			for(kk in infos['client'])
			{
				msg += kk + ' = ' + infos['client'][kk] + ' | ';
			}
		}
		else if(key == 'user')
		{
			msg += 'user: ';
			for(kk in infos['user'])
			{
				if(kk == 'pipe')
				{
					msg += 'pipe: ';
					for(kkk in infos['user']['pipe'])
					{
						msg += kkk + ' = ' + infos['user']['pipe'][kkk] + ' | ';
					}
				}
				else if(kk == 'proxys')
				{
					
				}
				else
				{
					msg += kk + ' = ' + infos['user'][kk] + ' | ';
				}
			}
		}
		else if(key == 'subuser')
		{
			msg += 'subuser: ';
			for(kk in infos['subuser'])
			{
				msg += kk + ' = ' + infos['subuser'][kk] + ' | ';
			}
		}
		else
		{
			msg += key + ' = ' + infos[key] + ' | ';
		}
	}
	Ape.log(msg);
	//Ape.HTTPRequest('http://127.0.0.1/chat.php', data);
	var pipe = Ape.getPipe(params.pipe);
	if (!$defined(pipe))
	{
		return ["4400", "WHERESTHEPIPE"];
	}
	pipe.sendRaw('MAP_DATA', params);
	return 1;
});