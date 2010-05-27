include("framework/binary.js");

var MySQL = new Class({
	port: 3306,
	
	initialize: function (host, login, pass) {
		var socket = new Ape.sockClient(this.port, host);
		this.binary = new BinaryParser(true);
		this.state = 0;
		this.socket = socket;
		this.pass = pass;
		
		socket.onConnect = function() {
			Ape.log("Mysql Connected");
		}
		
		socket.onRead = function(data) {
			switch(this.state) {
				case 0:
					Ape.log('Data : ' + data);
					Ape.log('len : ' + data.length);
					
					var header = this.parseHeader(data.substr(0, 4));
					this.dispatch(header, data.substr(4));
					break;
				default:
					break;
			}

			//Ape.log('data : ' + binary.toShort(data.substr(0, 2)));
		}.bind(this);
		
		socket.onDisconnect = function() {
			
		}
	},
	
	parseHeader: function(packet) {
		return {'len': this.binary.toShort(packet.substr(0, 2)), 'number': this.binary.toSmall(packet.substr(2, 1))}
	},
	
	dispatch: function(header, packet) {
		Ape.log(bin2hex(packet));
		switch(header.number) {
			case 0:
				this.handshake(packet, header.len);
				break;
			default:
				break;
		}
	},
	
	handshake: function(packet, len) {
		var protocol_version = this.binary.toSmall(packet.substr(0, 1));
		var server_version = '';
		for (var i = 1; packet[i] != '\x00'; i++) {
			server_version += packet[i];
		}
		var thread_id = this.binary.toInt(packet.substr(i+1, 4));
		var scramble_buff = packet.substr(i+5, 8);
		var server_capabilities = this.binary.toWord(packet.substr(i+14, 2));
		
		var server_language = this.binary.toSmall(packet.substr(i+16, 1));
		var server_status = this.binary.toShort(packet.substr(i+17, 2));
		scramble_buff += packet.substr(i+32);
		
		var stage_1 = Ape.sha1.bin(this.pass);
		var stage_2 = Ape.sha1.bin(stage_1);
		
		var to = Ape.sha1.bin(scramble_buff.substr(0, scramble_buff.length-1) + stage_2);
		var token = Ape.xorize(to, stage_1);
				
		var tosend = '\x3a\x00\x00\x01\x05\xa6\x03\x00\x00\x00\x00\x01\x08\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x72\x6f\x6f\x74\x00\x14';
		tosend += token;
		this.socket.write(tosend);

	}

	
});