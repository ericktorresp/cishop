/***
 * Signin Box JavaScript code
 * Separated from phub.js due to caching issues.
 */
var signinbox = {
	hideTimeout: 0,
	inputFocused: false,
	containerWidth: 889,
	show: function() {
		if (navigator.userAgent) {
			if (navigator.userAgent.match(/PLAYSTATION/) ) {
				document.location.assign("http://"+location.hostname+"/community");
			}
		}
		if( $j('div#signin_container').css('display') != 'block' && $j('a#header_login_link')[0] )
		{
			$j('div#signin_container').css( {
				'top': (32+$j(document).scrollTop()<170?170:(32+$j(document).scrollTop()))+'px',
				'left': ( ($j(document).width()-this.containerWidth)/2) +'px'
			});
			if( $j.support.opacity )
				$j('div#signin_container').fadeIn(400);
			else
				$j('div#signin_container').show();
			this.hideTimeout = 600;
			setTimeout('signinbox.hideTimeout=0;',this.hideTimeout);
		}
	},
	hide: function() {
		if( this.hideTimeout )
			return false;
		if( $j('div#signin_container').css('display') != 'none' )
		{
			if( $j.support.opacity )
				$j('div#signin_container').fadeOut(500);
			else
				$j('div#signin_container').hide();

			$j('div#signin_background a').css('font-size','1em');
			$j('.signin_error').hide();
			$j('input#signin_username').val('');
			$j('input#signin_password').val('');
			$j('input#signin_redirectTo').val('');
		}
	},
	submit: function() {
		$j('button#signin_submit').hide();
		$j('p#signin_loggingin').show();
		$j.post($j('input#signin_url').val(), {
			username: $j('input#signin_username').val(),
			password: $j('input#signin_password').val(),
			remember_me: $j('input#signin_remember').val(),
			redirectTo: $j('input#signin_redirectTo').val()
		}, signinbox.handleLogin );
	},
	handleLogin: function(data,status) {
		if( data.indexOf("//www.pornhubpremium.com") > 0 ) {
			if( document.location.pathname == "/" )
				document.location.assign(data);
			else
				document.location.reload();
		} else {
			if( data.indexOf("/") == 0 ) {
				document.location.assign(data);
			} else {
				signinbox.show();
				var x = parseInt($j('div#signin_container').css('left'));
				var y = parseInt($j('div#signin_container').css('top'));
				for(var i=1;i<10;i++)
					setTimeout(function() {
						$j('div#signin_container').css('left',x-5+(Math.random()*11));
						$j('div#signin_container').css('top',y-4+(Math.random()*9));
					},20*i);
				setTimeout(function() {
					$j('div#signin_container').css('left',x);
					$j('div#signin_container').css('top',y);
				},210);

				if( data == 2 ) {
					$j('p.signin_error').text('Email not confirmed yet!');
					$j('.signin_error').show();
					$j('button#signin_submit').show();
					$j('p#signin_loggingin').hide();
					$j('a#signin_forgotpassword').css('font-size','1em');
					$j('a#signin_confirmationemail').css('font-size','1.25em');
				}
				else {
					$j('p.signin_error').text('Invalid username/password!');
					$j('.signin_error').show();
					$j('button#signin_submit').show();
					$j('p#signin_loggingin').hide();
					$j('a#signin_forgotpassword').css('font-size','1.25em');
					$j('a#signin_confirmationemail').css('font-size','1em');
				}
			}
		}
	}
};

// We need to handle this so the dimmed background works good.
function recalcSigninBox(e) {
	if( $j('div#signin_container')[0] )
	{
		if( $j('div#signin_container').css('display') != 'none' )
			$j('div#signin_container').css( {
				'top': (32+$j(document).scrollTop()<170?170:(32+$j(document).scrollTop()))+'px',
				'left': ( ($j(document).width()-signinbox.containerWidth)/2) +'px'
			});
	}
}

$j(document).ready( function(e) {
	if( $j('div#signin_container')[0] )
	{
		$j(window).resize(recalcSigninBox);
		$j(window).scroll(recalcSigninBox);
		$j("input").focus(function() {
			signinbox.inputFocused = true;
		});
		$j("input").blur(function() {
			signinbox.inputFocused = false;
		});

		$j(document).click(function(e) {
			if( $j('div#signin_container').css('display') != 'none' )
			{
				var pos = $j('div#signin_container').offset();
				if( ( e.pageX < pos.left || e.pageX > pos.left+signinbox.containerWidth ||
					e.pageY < pos.top  || e.pageY > pos.top +$j('div#signin_container').height() ) && !signinbox.inputFocused )
						signinbox.hide();
			}
		});
		$j(document).keypress(function(e) {
			if( e.keyCode == 27)
				signinbox.hide();
		});
		$j('input#signin_username').keypress(function(e) {
			if( e.which == 13 && $j('input#signin_password').val().length > 0 )
				$j('button#signin_submit').click();
			else if( e.which == 13 && $j('input#signin_password').val().length < 1 )
				$j('input#signin_password').focus();
		});
		$j('button#signin_submit').click(signinbox.submit);
		$j('input#signin_password').keypress(function(e) {
			if( e.which == 13 )
				$j('button#signin_submit').click();
		});
		$j('button#signin_close').click(signinbox.hide);
		if( $j.support.opacity )
			$j('div#signin_container').draggable({
				cursor: 'move',
				handle: 'div#signin_border',
				opacity: 0.85
			});
		else
			$j('div#signin_container').draggable({
				cursor: 'move',
				handle: 'div#signin_border'
				});
		if( document.location.search.match(/showSigninBox=true/i) && $j('a#header_login_link')[0] )
			signinbox.show();
	}
});