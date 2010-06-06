var street = {
	width: 614,
	height: 374
};
Ape.registerCmd("LOADMAP", true, function(params, infos) {
	Ape.log("Received LOADMAP");
	//调用py，返回 json 编码的每街区建筑，NPC，玩家数据
	//Ape.HTTPRequest('http://127.0.0.1/chat.php', data);
	var mapdata = [];
	for(var i = 1; i <= 9; i++)
	{
		var row = new Math.ceil(i/3);
		mapdata.push({
			// 'top': row == 1 ? -street.height/2 : (row == 2 ? street.height/2 : street.height/2*3),
			// 'left': i%3 == 1 ? -street.width/2 : (i%3 == 2 ? street.width/2 : street.width/2*3),
			'x': i%3 == 1 ? -1 : (i%3 == 2 ? 0 : 1),
			'y': row == 1 ? 1 : (row == 2 ? 0 : -1),
		});
	}
	infos.user.pipe.sendRaw('MAP_DATA', mapdata);
	return 1;
});