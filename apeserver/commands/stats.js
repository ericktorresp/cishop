var iChannels = 0, iUsers = 0, iPushes = 0, iStartTime;

Ape.addEvent('adduser', function(user){
        iUsers++;
});

Ape.addEvent('deluser', function(user){
        iUsers--;
});

Ape.addEvent('init', function(){
        iStartTime = Math.round(new Date().getTime() / 1000);
});

Ape.addEvent('mkchan', function(channel){
        iChannels++;
});

Ape.addEvent('rmchan', function(){
        iChannels--;
});

Ape.registerHookCmd('inlinepush', function(params, info){
        iPushes++;
});

Ape.registerCmd('stats', false, function(params, info){
        if (params.password != Ape.config('stats.conf', 'password')){
                return ["400", "BAD_PASSWORD"];
        }

        var i_now = Math.round(new Date().getTime() / 1000);
        var i_uptime = (i_now - iStartTime);
        var i_pps = (iPushes / i_uptime);

        return {
                "name": "stats",
                "data": {
                        "users": iUsers,
                        "channels": iChannels,
                        "pushes": iPushes,
                        // work-around to prevent APE from round pps to 0
                        // in displaying this, you should convert 
                        // scientific notation back to float
                        "push_per_second": (iPushes === 0 ? 0 : Math.round(i_pps * 100) + 'E-02'),
                        "uptime": i_uptime
                }
        };
});