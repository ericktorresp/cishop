/***
 * CRIMS JSF Setup
 */
CRIMS.Config.baseUrl = 'http://crims.info/crimsui/city_files/js'; //APE JSF 
CRIMS.Config.domain = 'auto'; 
CRIMS.Config.server = 'ape.crims.info:6969'; //APE server URL

(function(){
	for (var i = 0; i < arguments.length; i++)
		CRIMS.Config.scripts.push(CRIMS.Config.baseUrl + '/Source/' + arguments[i] + '.js');
})('mootools-core', 'Core/APE', 'Core/Events', 'Core/Core', 'Pipe/Pipe', 'Pipe/PipeProxy', 'Pipe/PipeMulti', 'Pipe/PipeSingle', 'Request/Request','Request/Request.Stack', 'Request/Request.CycledStack', 'Transport/Transport.longPolling','Transport/Transport.SSE', 'Transport/Transport.XHRStreaming', 'Transport/Transport.JSONP', 'Core/Utility', 'Core/JSON');
