<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="shortcut icon" href="/favicon.ico">
	<link rel="alternate" type="text/html" media="handheld" href="http://m.hxxps.us/" title="Mobile/PDA">
	<meta name="keywords" content="porn, sex, porno, free porn, porn tube, porn videos, streaming porn, sex videos,free pussy ,pussy">
	<meta name="description" content="Free porn sex videos &amp; pussy movies. Porn hub is the ultimate xxx porn,sex and pussy tube, download sex videos or stream free xxx and free pussy movies.">
	<title><?php echo $title;?></title>
	<link rel="stylesheet" href="<?php echo base_url();?>css/common.css" type="text/css">
	<?php if($this->session->userdata('uid')){?>
	<link rel="stylesheet" href="<?php echo base_url();?>css/community.css" type="text/css">
	<?php }?>
	<!--[if lte IE 7]>
	<link rel="stylesheet" href="<?php echo base_url();?>css/ie.css" type="text/css" />
	<![endif]-->
	<script src="<?php echo base_url();?>js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo base_url();?>js/jquery-ui.js" type="text/javascript"></script>
	<script type="text/javascript">
		var $j = jQuery.noConflict();
	</script>
	<script type="text/javascript" src="<?php echo base_url();?>js/phub.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/signin.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/Silverlight.js"></script>
</head>
<body>
	<div class="ui-draggable" id="signin_container" style="display: none;">
		<div id="signin_border"></div>
		<div id="signin_background" style="background: transparent url(<?php echo base_url();?>images/signin_back_en.jpg) no-repeat scroll 0pt 0pt; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous;">
			<div class="signin_error" style="top: 84px; left: 38px; display: none;"></div>
			<p class="signin_error" style="top: 54px; left: 38px; display: none;"></p>
			<p id="signin_loggingin" style="top: 214px; left: 125px; width: 172px; text-align: center; display: none;">Logging in...</p>
			<a id="signin_forgotpassword" href="<?php echo site_url('/members/lostpwd');?>" onclick="pageTracker._trackEvent('Login Page', 'Click Lost Password');" style="top: 254px; left: 125px;">Forgot Username or Password?</a>
			<a id="signin_confirmationemail" href="<?php echo site_url('/members/resendconfirm');?>" onclick="pageTracker._trackEvent('Login Page', 'Click Resend Confirmation');" style="top: 278px; left: 125px;">Did not receive confirmation email?</a>
			<input id="signin_url" value="<?php echo site_url('login');?>" type="hidden">
			<input id="signin_redirectTo" value="/" type="hidden">
			<input id="signin_username" maxlength="18" class="signup_field" style="top: 97px; left: 132px;">
			<input id="signin_password" maxlength="40" class="signup_field" style="top: 132px; left: 132px;" type="password">
			<input id="signin_remember" value="1" style="top: 166px; left: 127px;" type="checkbox">
			<button id="signin_submit" class="signup_button" style="top: 210px; left: 125px;">Login</button>
			<button class="signup_button" style="top: 288px; left: 582px;" onclick="window.location='<?php echo site_url('register');?>'">Sign up!</button>
			<button id="signin_close" style="top: 5px; left: 830px;"></button>
		</div>
	</div>
	<div class="wrapper">
		<div class="header-wrapper">
			<div class="header<?php echo ($this->session->userdata('uid') ? '' : '02'); ?>-nf">
				<div class="logo-nf" style="background: transparent url(<?php echo base_url();?>images/pornhub_logo_en.png) no-repeat scroll 0pt 0pt; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous; width: 275px; height: 81px;">
					<a href="<?php echo base_url();?>" onclick="pageTracker._trackEvent('Header Tabs', 'Logo');">
						<span class="display-none">
							<h1>Pornhub</h1>It Makes Your Dick Bigger &amp; Gets Your Pussy Wet
						</span>
					</a>
				</div>
				<!-- TOP RIGHT MENU -->
				<div class="top-right-menu-nf">
					<a href="<?php echo base_url();?>uploader" onclick="pageTracker._trackEvent('Header Tabs', 'Upload');">Upload</a> l
					<a href="<?php echo base_url();?>blog" onclick="pageTracker._trackEvent('Header Tabs', 'Pornhub Blog');">Blog</a> l
					<a href="<?php echo base_url();?>gay" onclick="pageTracker._trackEvent('Header Tabs', 'Gay Porn');">Gay Porn</a> l
					<?php if(!$this->session->userdata('uid')){?>
					<a id="header_login_link" href="javascript:signinbox.show();" onclick="pageTracker._trackEvent('Header Tabs', 'Login');"><?php echo lang('login');?></a> l
					<a href="<?php echo base_url();?>register" onclick="pageTracker._trackEvent('Header Tabs', 'Sign Up');">Sign Up</a>
					<?php }else{?>
					<a href="/user/edit" onclick="pageTracker._trackEvent('Header Tabs', 'Settings');">Settings</a> l 
					&nbsp;Signed in as 
					<a href="/users" class="username" onclick="pageTracker._trackEvent('Header Tabs', 'Username');"><?php echo $this->session->userdata('username')?></a> l 
					<a href="<?php echo site_url('logout');?>" onclick="pageTracker._trackEvent('Header Tabs', 'Logout');"><?php echo lang('logout');?></a>
					<?php }?>
				</div>
				<div class="flag-wrapper">
					<ul>
						<li>Language</li>
						<li class="buttons-img flag uk"><a href="<?php echo base_url();?>" class="active" onclick="pageTracker._trackEvent('Language Flags', 'English');"><span>English</span></a></li>
						<li class="buttons-img flag german"><a href="http://de.pornhub.com/" onclick="pageTracker._trackEvent('Language Flags', 'German');"><span>Deutsch</span></a></li>
						<li class="buttons-img flag french"><a href="http://fr.pornhub.com/" onclick="pageTracker._trackEvent('Language Flags', 'French');"><span>Français</span></a></li>
						<li class="buttons-img flag spanish"><a href="http://es.pornhub.com/" onclick="pageTracker._trackEvent('Language Flags', 'Spanish');"><span>Español</span></a></li>
						<li class="buttons-img flag italian"><a href="http://it.pornhub.com/" onclick="pageTracker._trackEvent('Language Flags', 'Italian');"><span>Italiano</span></a></li>
						<li class="buttons-img flag portugese"><a href="http://pt.pornhub.com/" onclick="pageTracker._trackEvent('Language Flags', 'Portugese');"><span>Português</span></a></li>
					</ul>
					<div class="main-sprite language-marker" style="display: none;"></div>
				</div>
				<div class="wrapper-main-menu-nf">
					<ul>
						<li class="wide-btn-title wide-btn-title nf-home-main-menu"><a href="<?php echo base_url();?>" class="active" onclick="pageTracker._trackEvent('Header Tabs', 'Home Tab');">Home</a></li>
						<li class="wide-btn-title"><a href="<?php echo base_url();?>video" onclick="pageTracker._trackEvent('Header Tabs', 'Videos Tab');">Videos</a></li>
						<li class="wide-btn-title"><a href="<?php echo base_url();?>categories" onclick="pageTracker._trackEvent('Header Tabs', 'Categories Tab');">Categories</a></li>
						<li class="wide-btn-title"><a target="_blank" href="http://enter.pornhubpremium.com/track/NTQ1MzoyNTozNg/join" rel="nofollow" onclick="pageTracker._trackEvent('Header Tabs', 'Premium Tab');">Premium</a></li>
						<li class="wide-btn-title"><a target="_blank" href="http://mbs.pornhublive.com/xtarc/595728/437/0/?mta=338243" rel="nofollow" onclick="pageTracker._trackEvent('Header Tabs', 'Live Sex Tab');">Live Sex</a></li>
						<li class="wide-btn-title"><a target="_blank" href="http://ads.sexinyourcity.com/generator/dispatcher/ph_tab_dispatcher.php" rel="nofollow" onclick="pageTracker._trackEvent('Header Tabs', 'Real Sex Tab');">Real Sex</a></li>
						<li class="wide-btn-title"><a href="http://www.pornhub.com/community" rel="nofollow" class="" onclick="pageTracker._trackEvent('Header Tabs', 'Community Tab');">Community</a></li>
						<li class="wide-btn-title nf-search-main-menu">
							<form id="search_form" method="get" action="/video/search" onsubmit="if(document.getElementById('search_value').value=='' || document.getElementById('search_value').value=='Search...') return false;">
								<fieldset class="fs-nf">
									<input class="main-sprite search-input-nf" onblur="if(this.value=='') {this.value = 'Search...';}" onfocus="if(this.value=='Search...') {this.value = '';}" value="Search..." name="search" maxlength="75" id="search_value" type="text">
									<input value="" style="display: none;" type="submit">
									<input class="btn-search-top-menu-nf" src="<?php echo base_url();?>images/btn-search-nf.gif" value="search" type="image">
								</fieldset>
							</form>
						</li>
					</ul>
				</div>
				<?php if($this->session->userdata('uid')){?>
				<!-- ORANGE MENU -->
				<div class="secondary-menu-nf">
					<ul>
						<li class="cmty-wide-btn-title first-child-nf"><a href="/users/floyd_joe" onClick="pageTracker._trackEvent('Header Tabs', 'My Profile');">My Profile</a></li>
						<li class="cmty-wide-btn-title"><a href="/message" onClick="pageTracker._trackEvent('Header Tabs', 'Inbox');">Inbox&nbsp;(0)</a></li>
						<li class="cmty-wide-btn-title"><a href="/users/floyd_joe/videos" onClick="pageTracker._trackEvent('Header Tabs', 'My Videos');">My Videos</a></li>
						<li class="cmty-wide-btn-title"><a href="/users/floyd_joe/videos/favorites" onClick="pageTracker._trackEvent('Header Tabs', 'My Favorites');">My Favorites</a></li>
						<li class="cmty-wide-btn-title"><a href="/feeds" onClick="pageTracker._trackEvent('Header Tabs', 'Feeds');">Feeds</a></li>
						<li class="cmty-wide-btn-title"><a href="/user/friend_requests" onClick="pageTracker._trackEvent('Header Tabs', 'Requests');">Requests (<span id="RequestsCountTitle">0</span>)</a></li>
						<li class="cmty-wide-btn-title last-child-nf"><a href="/uploader" onClick="pageTracker._trackEvent('Header Tabs', 'Upload');">Upload</a></li>
					</ul>
				</div>
				<?php }?>
			</div>
		</div>
		<div class="container">