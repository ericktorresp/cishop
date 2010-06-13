var street = {
	width: 614,
	height: 374
};
function http_request(url, params, callback)
{
	var request = new Http(url);
	request.set('method', 'GET');
	request.writeObject(params);
	request.getContent(function(result) {
		var ret = {};
		try
		{
			ret = eval(result);
		}
		catch(e)
		{
		};
		callback(ret);
	});	
}
Ape.registerCmd("LOADMAP", true, function(params, infos) {
	Ape.log("Received LOADMAP " + $time());
	//调用py，返回 json 编码的每街区建筑，NPC，玩家数据
	http_request('http://127.0.0.1:8000/map/', params, function(result)
	{
		infos.user.pipe.sendRaw('MAP_DATA', result);
	});
	return 1;
});