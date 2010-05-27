BinaryParser = function( bigEndian, allowExceptions ){
	this.bigEndian = bigEndian;
	this.allowExceptions = allowExceptions;
};
with( { p: BinaryParser.prototype } ){
	with( {p: ( p.Buffer = function( bigEndian, buffer ){ this.bigEndian = bigEndian || 0; this.buffer = []; this.setBuffer( buffer ); } ).prototype } ){
		p.setBuffer = function( data ){
			if( data ){
				for( var l, i = l = data.length, b = this.buffer = new Array( l ); i; b[l - i] = data.charCodeAt( --i ) );
				this.bigEndian && b.reverse();
			}
		};
		p.hasNeededBits = function( neededBits ){
			return this.buffer.length >= -( -neededBits >> 3 );
		};
		p.checkBuffer = function( neededBits ){
			if( !this.hasNeededBits( neededBits ) )
				throw new Error( "checkBuffer::missing bytes" );
		};
		p.readBits = function( start, length ){
			//shl fix: Henri Torgemane ~1996 (compressed by Jonas Raoni)
			function shl( a, b ){
				for( ; b--; a = ( ( a %= 0x7fffffff + 1 ) & 0x40000000 ) == 0x40000000 ? a * 2 : ( a - 0x40000000 ) * 2 + 0x7fffffff + 1 );
				return a;
			}
			if( start < 0 || length <= 0 )
				return 0;
			this.checkBuffer( start + length );
			for( var offsetLeft, offsetRight = start % 8, curByte = this.buffer.length - ( start >> 3 ) - 1, lastByte = this.buffer.length + ( -( start + length ) >> 3 ), diff = curByte - lastByte, sum = ( ( this.buffer[ curByte ] >> offsetRight ) & ( ( 1 << ( diff ? 8 - offsetRight : length ) ) - 1 ) ) + ( diff && ( offsetLeft = ( start + length ) % 8 ) ? ( this.buffer[ lastByte++ ] & ( ( 1 << offsetLeft ) - 1 ) ) << ( diff-- << 3 ) - offsetRight : 0 ); diff; sum += shl( this.buffer[ lastByte++ ], ( diff-- << 3 ) - offsetRight ) );
			return sum;
		};
	}
	p.warn = function( msg ){
		if( this.allowExceptions )
			throw new Error( msg );
		return 1;
	};
	p.decodeInt = function( data, bits, signed ){
		var b = new this.Buffer( this.bigEndian, data ), x = b.readBits( 0, bits ), max = Math.pow( 2, bits );
		return signed && x >= max / 2 ? x - max : x;
	};

	p.encodeInt = function( data, bits, signed ){
		var max = Math.pow( 2, bits );
		( data >= max || data < -( max >> 1 ) ) && this.warn( "encodeInt::overflow" ) && ( data = 0 );
		data < 0 && ( data += max );
		for( var r = []; data; r[r.length] = String.fromCharCode( data % 256 ), data = Math.floor( data / 256 ) );
		for( bits = -( -bits >> 3 ) - r.length; bits--; r[r.length] = "\0" );
		return ( this.bigEndian ? r.reverse() : r ).join( "" );
	};
	
	p.toSmall    = function( data ){ return this.decodeInt( data,  8, true  ); };
	p.fromSmall  = function( data ){ return this.encodeInt( data,  8, true  ); };
	p.toByte     = function( data ){ return this.decodeInt( data,  8, false ); };
	p.fromByte   = function( data ){ return this.encodeInt( data,  8, false ); };
	p.toShort    = function( data ){ return this.decodeInt( data, 16, true  ); };
	p.fromShort  = function( data ){ return this.encodeInt( data, 16, true  ); };
	p.toWord     = function( data ){ return this.decodeInt( data, 16, false ); };
	p.fromWord   = function( data ){ return this.encodeInt( data, 16, false ); };
	p.toInt      = function( data ){ return this.decodeInt( data, 32, true  ); };
	p.fromInt    = function( data ){ return this.encodeInt( data, 32, true  ); };
	p.toDWord    = function( data ){ return this.decodeInt( data, 32, false ); };
	p.fromDWord  = function( data ){ return this.encodeInt( data, 32, false ); };
	p.toDouble   = function( data ){ return this.decodeFloat( data, 52, 11  ); };
	p.fromDouble = function( data ){ return this.encodeFloat( data, 52, 11  ); };
}
function bin2hex (s){

    var i, f = 0, a = [];
     
    s += '';
    f = s.length;
     
    for (i = 0; i<f; i++) {
        a[i] = s.charCodeAt(i).toString(16).replace(/^([\da-f])$/,"0$1");
    }
     
    return a.join(' ');
}
