var debugUser = new $H;
var oldApeLog = Ape.log;
Ape.log = function() {
	oldApeLog.run(arguments);
	var args = arguments;
	debugUser.each(function(user) {
			var msg = [];
			for (var i = 0; i < args.length; i++) {
				oldApeLog(args[i]);
				msg.push(args[i]);
			}
			user.pipe.sendRaw('debug', {'msg': msg});
	});
}
Ape.registerHookCmd('connect', function(params, cmd) {
	if (params.sendDebug == 1) {
		cmd.user.sendDebug = true;
	}
	return 1;
});

Ape.addEvent('addUser', function(user) {
	if (user.sendDebug) {
		debugUser.set(user.getProperty('sessid'), user);
	}
});

Ape.addEvent('deluser', function(user) {
	debugUser.erase(user.getProperty('sessid'));
});

// (function() {
//  Ape.log('test');
// }).periodical(500);
