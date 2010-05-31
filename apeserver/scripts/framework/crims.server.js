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
	//调用py，返回 json 编码的每街区建筑，NPC，玩家数据
	//Ape.HTTPRequest('http://127.0.0.1/chat.php', data);
	var pipe = Ape.getPipe(params.pipe);
	if (!$defined(pipe))
	{
		return ["109", "UNKNOWN_PIPE"];
	}
	pipe.sendRaw('MAP_DATA', {x:0,y:0,z:1});
	return 1;
});