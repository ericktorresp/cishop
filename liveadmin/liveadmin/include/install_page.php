<?php

/***************************************************************
* Live Admin Standalone
* Copyright 2008-2011 Dayana Networks Ltd.
* All rights reserved, Live Admin  is  protected  by  Canada and
* International copyright laws. Unauthorized use or distribution
* of  Live Admin  is  strictly  prohibited,  violators  will  be
* prosecuted. To  obtain  a license for using Live Admin, please
* register at http://www.liveadmin.net/register.php
*
* For more information please refer to Live Admin official site:
*    http://www.liveadmin.net
*
* Translation service provided by Google Inc.
***************************************************************/






 ?>
<%sec-start header%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<title>Live Admin - Installer</title>
<style type="text/css">


body {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	background-color: #333333;
	margin-top: 50px;
}
.wrap {
	width: 600px;
	clear: both;
	margin: 0 auto;
	background-color: #999999;
	padding: 10px;
	border: 1px solid #666666;
}
.copyright {
	width: 600px;
	clear: both;
	margin: 0 auto;
	padding-top: 5px;
	color: #454545;
	text-align: center;
}
.copyright a {
	color: #454545;
	text-decoration: none;
}
.temp {
	border: 1px solid #808080;
	text-align: left;
}
.text_2 {
	background-color: #FFFFFF;
	padding: 10px;
}
.upt {
	background-position: 0px -180px;
	border-style: solid solid none solid;
	border-width: 1px;
	border-color: #666666;
	background-color: #2647A0;
	padding: 10px;
	background-image: url('images/lv_sprite.png');
	background-repeat: repeat-x;
}
.upt #lvad_img_2 {
	border-width: 0px;
	margin-left: 10px;
}
h3 {
	padding: 0px;
	margin: 10px 0px 10px 0px;
}
.hl {
	height: 1px;
}
form {
	padding: 0px;
	margin: 0px;
	display: inline;
}
form button {
	margin-left: 10px;
}
#login_box {
	padding-top: 10px;
}
#login_box .box_record {
	padding-top: 10px;
}
.input_table {
	font-family: Arial, Helvetica, sans-serif;
}
.input_table th {
	font-size: 14pt;
	padding: 5px;
	text-align: right;
}
.input_table td {
	padding: 5px;
}
.input_table td input {
	font-size: 14pt;
}
.form_button {
	border-top-style: dotted;
	border-width: 1px;
	border-color: #C0C0C0;
	margin-top: 10px;
	padding-top: 15px;
	text-align: right;
	padding-right: 10px;
}
.form_button button {
	font-size: 12pt;
	font-family: Arial, Helvetica, sans-serif;
}
.text_bf {
	padding: 5px;
}
.text_bf .h {
	height: 10px;
}
.text_bf .b {
	border: 1px dashed #990000;
	padding: 1px;
	background-color: #FFEAEA;
}
.text_bf .box_header {
	padding: 3px;
	background-color: #990000;
	color: #FFFFFF;
	font-weight: bold;
}
.text_bf .box_record {
	padding: 10px;
}
.text_cf {
	padding: 5px;
}
.text_cf .h {
	height: 10px;
}
.text_cf .b {
	border: 1px dashed #009933;
	padding: 1px;
	background-color: #DFFFDF;
}
.text_cf .box_header {
	padding: 3px;
	background-color: #009933;
	color: #FFFFFF;
	font-weight: bold;
}
.text_cf .box_record {
	padding: 10px;
}
.fr {
	height: 5px;
}
#lvad_img_1 {
	border-width: 0px;
}
.hline {
	border-top-style: dotted;
	border-width: 1px;
	border-color: #C0C0C0;
	height: 10px;
	margin-top: 10px;
}

.system_req span
{
width: 150px;
display: block;
float: left;
clear: left;
}

.system_req .clear
{
clear: both;
padding-bottom:5px;
padding-top:5px;
height: 1px;
}

.system_req p
{
padding: 0px;
margin: 0px;
color: #006600;
padding-left: 150px;
display: block;
}

.system_req em
{
padding: 0px;
margin: 0px;
font-style: normal;
color: #990000;
font-weight: bold;
padding-left: 150px;
display: block;
}
.input_form {
	padding: 20px 20px 10px 20px;
}
.input_form em {
	display: block;
	width: 150px;
	clear: left;
	float: left;
	font-style: normal;
}
.input_form p {
	padding: 0px;
	margin: 0px;
	display: block;
}
.input_form .clear {
	clear: both;
	height: 5px;
	border-top-style: dotted;
	border-width: 1px;
	border-color: #C0C0C0;
	margin-top: 5px;
}
.input_form span {
	padding: 0px 0px 0px 150px;
	margin: 0px;
	display: block;
	color: #808080;
	font-style: italic;
	font-size: 9pt;
}


.red {
	color: #FF0000;
}


.finished a {
	text-decoration: none;
	font-weight: bold;
	color: #006699;
	display: block;
	padding-left: 20px;
}
.finished a:hover {
	font-weight: bold;
	color: #990000;
	text-decoration: underline;
}


.installed {
	font-size: 11pt;
}


</style>
</head>
<body>

<div class="wrap">

<div class="upt">
	<img id="lvad_img_1" src="images/live_admin_logo.png"><img id="lvad_img_2" src="images/live_admin_logo_text.png"></div>

<%sec-end header%>


<%sec-start error%>
<div class="text_bf">
	<div class="h"></div>
	<div class="b">

	<div class="box_header">Error</div>
	<div class="box_record"><%value error_text%></div>

	</div>
	<div class="f"></div>
</div>
<%sec-end error%>



<%sec-start step_001%>

<div class="temp">
				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Installation</h3>
						<div class="hline"></div>
						<div>Welcome to LiveAdmin installer. This wizard will
							guide you through the installation process.<br><br>
							<br><br><br><br></div>



<div class="box" id="login_box">
	<div class="box_record">
	<form method="post" action="?act=install&step=1_5">

		<div class="form_button"><button name="Abutton1" type="submit">Next</button></div>

	</form>
	</div>
</div>








						</div></div>
					</div>
					<div class="fl"><div class="fr"></div>
					</div>
				</div>
</div>
<%sec-end step_001%>

<%sec-start step_002%>

<div class="temp">
				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Installation - System Requirements</h3>
						<div class="hline"></div>
						<div>If any of the following items marked in red color your host does not meet the minimum
							system requirements and in most cases LiveAdmin will
							not work properly. Although you may still continue
							the installation.</div>
						<div class="hline"></div>
<div class="system_req">
<%value system_req_info%>
</div>

<div class="box" id="login_box">
	<div class="box_record">
	<form method="post" action="?act=install&step=2_5">

		<div class="form_button"><button name="Abutton1" type="submit">Next</button></div>

	</form>
	</div>
</div>


						</div></div>
					</div>
					<div class="fl"><div class="fr"></div>
					</div>
				</div>
</div>
<%sec-end step_002%>


<%sec-start step_003%>

<div class="temp">
				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Installation - Configuration file - Set Write
						Permission</h3>
						<div class="hline"></div>
						<div>As I checked, configuration file is <span class="red">not writeable</span>.
							You need to set the proper permission to:<br><br>
							<strong>include/config.php</strong><br><br>so that I can write to
							this file. If you are using FTP set the file to be
							world writable. If you have a shell change directory
							to where LiveAdmin files installed, then use this
							command:<br><br><strong>chmod 0777 include/config.php</strong><br>
							<br>Then hit the 'Retry' button to check again.</div>
						<div class="hline"></div>

<div class="box" id="login_box">
	<div class="box_record">
	<form method="post" action="?act=install&step=3_5">

		<div class="form_button"><button name="Abutton1" type="submit">Retry</button></div>

	</form>
	</div>
</div>


						</div></div>
					</div>
					<div class="fl"><div class="fr"></div>
					</div>
				</div>
</div>
<%sec-end step_003%>


<%sec-start step_004%>

<div class="temp">
	<form method="post" action="?act=install&step=4_5">
				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Installation - Configuration file</h3>
						<div class="hline"></div>
						<div>Please key in login information for MySQL database:<br></div>
<%value error%>

<div class="input_form">
<em>Database Host:</em>
<p><input type="text" name="lv_db_host" value="<%value lv_db_host%>"></p>
<span>Host name of MySQL server, this is usually localhost</span>
<div class="clear"></div>

<em>Database Name:</em>
<p><input type="text" name="lv_db_name" value="<%value lv_db_name%>"></p>
<span>Name of MySQL database</span>
<div class="clear"></div>

<em>Database Username:</em>
<p><input type="text" name="lv_db_user" value="<%value lv_db_user%>"></p>
<span>This username should have access to above database</span>
<div class="clear"></div>

<em>Database Password:</em>
<p><input type="text" name="lv_db_pass" value="<%value lv_db_pass%>"></p>
<span>Password of database username</span>
<div class="clear"></div>

<em>Table prefix:</em>
<p><input type="text" name="lv_db_prefix" value="<%value lv_db_prefix%>"></p>
<span>If you are installing multiple copies of LiveAdmin in same database change this, otherwise simply leave the default value.</span>
<div class="clear"></div>


</div>

									Information regarding your web site and
									path:

<div class="input_form">
<em>Absolute Path:</em>
<p><input type="text" size="40" name="lv_path" value="<%value lv_path%>"></p>
<span>Absolute path to the location where LiveAdmin installed</span>
<div class="clear"></div>

<em>Web Address:</em>
<p><input type="text" size="40" name="lv_url" value="<%value lv_url%>"></p>
<span>Complete URL of LiveAdmin installation</span>
<div class="clear"></div>


</div>



<div class="box" id="login_box">
	<div class="box_record">

		<div class="form_button"><button name="Abutton1" type="submit">Next</button></div>

	</div>
</div>








						</div></div>
					</div>
					<div class="fl"><div class="fr"></div>
					</div>
				</div>
	</form>
</div>
<%sec-end step_004%>


<%sec-start step_005%>

<div class="temp">
				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Installation - Configuration file - Remove Write Permission</h3>
						<div class="hline"></div>
						<div>Configuration file has modified. It's still 							<span class="red">writeable</span> and any one at
							the server can write to it. For security reasons
							please make this file readable only.<br><br>Location
							of config.php file is:<br><br>
							<strong>include/config.php</strong><br><br>And you
							need to change the permission of this file to be
							readable by web server or PHP process. If you have a
							shell change directory to where LiveAdmin files installed, then use this
							command:<br><br><strong>chmod 0644 include/config.php</strong><br>
							<br>Then hit the 'Retry' button to check again and
							continue the installation.<br><br>Note that
							depending on your server's configuration, read only
							permission might be different from what I've
							suggested.<br><br>In some platforms there will be no 
							(or very hard) way to make a file unwritable by web 
							server who runs PHP. In this case you may still 
							continue the installation at your own risk by 'Skip 
							this step'.</div>

<div class="box" id="login_box">
	<div class="box_record">
		<div class="form_button">
			<form method="post" action="?act=install&step=5_7">
				<button name="Abutton1" type="submit">Skip this step</button>
			</form>
			<form method="post" action="?act=install&step=5_5">
				<button name="Abutton1" type="submit">Retry</button>
			</form>
		</div>
	</div>
</div>


						</div></div>
					</div>
					<div class="fl"><div class="fr"></div>
					</div>
				</div>
</div>
<%sec-end step_005%>


<%sec-start step_006%>

<div class="temp">
	<form method="post" action="?act=install&step=6_5">
				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Installation - Site and User information</h3>
						<div class="hline"></div>
						<div>Please site information, these can be changed later
							in Admin Panel:<br></div>
<%value error%>

<div class="input_form">
<em>Company or Site name:</em>
<p><input type="text" name="lv_company" value="<%value lv_company%>"></p>
<span>Name of your company or web site</span>
<div class="clear"></div>

<em>General Email:</em>
<p><input type="text" name="lv_general_email" value="<%value lv_general_email%>"></p>
<span>This is the general contact email address for company or website </span>&nbsp;<div class="clear"></div>

</div>

									Login information for default root user, you
									will use it to login to admin panel for the
									first time:

<div class="input_form">
<em>Username:</em>
<p><input type="text" size="40" name="lv_username" value="<%value lv_username%>"></p>
<span>This is the username of an account with root privileges. Should be alpha
	numeric (under score allowed) and between 5 to 50 charcters.</span>
<div class="clear"></div>

<em>Password:</em>
<p><input type="text" size="40" name="lv_password" value="<%value lv_password%>"></p>
<span>Password for the above username, 5 to 50 chacters</span>
<div class="clear"></div>

<em>First Name:</em>
<p><input type="text" size="40" name="lv_firstname" value="<%value lv_firstname%>"></p>
<span>Your first name, this will be set as the nickname as well, but can be changed later</span>
<div class="clear"></div>

<em>Last Name:</em>
<p><input type="text" size="40" name="lv_lastname" value="<%value lv_lastname%>"></p>
<span>Your last name</span>
<div class="clear"></div>

<em>Personal Email:</em>
<p><input type="text" size="40" name="lv_email" value="<%value lv_email%>"></p>
<span>This email is your personal address and will be used to send chat transcripts etc.</span>
<div class="clear"></div>



</div>



<div class="box" id="login_box">
	<div class="box_record">

		<div class="form_button"><button name="Abutton1" type="submit">Next</button></div>

	</div>
</div>








						</div></div>
					</div>
					<div class="fl"><div class="fr"></div>
					</div>
				</div>
	</form>
</div>
<%sec-end step_006%>


<%sec-start step_007%>

<div class="temp">
				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Installation - Finished</h3>
						<div class="hline"></div>
						<div class="finished">All done. You may now login to admin panel at:<br>
							<br><a href="<%value login_address%>"><%value login_address%></a><br><br>
							<br></div>



<div class="box" id="login_box">
	<div class="box_record">
	<form method="post" action="?act=install&step=7_5">

		<div class="form_button"><button name="Abutton1" type="submit">Finish</button></div>

	</form>
	</div>
</div>

						</div></div>
					</div>
					<div class="fl"><div class="fr"></div>
					</div>
				</div>
</div>
<%sec-end step_007%>


<%sec-start step_100%>

<div class="temp">
				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Installation</h3>
						<div class="hline"></div>
						<div class="installed">I have no idea why you are here,
							it seems LiveAdmin has already installed. If you get
							here from login screen, then probably
							LiveAdmin has not installed properly. Please
							remove the installation folder and drop all tables
							in database, then try to upload the files and
							install again.<br>
							<br><br><br><br><br><br><br>
							<br></div>



						</div></div>
					</div>
					<div class="fl"><div class="fr"></div>
					</div>
				</div>
</div>
<%sec-end step_100%>



<%sec-start footer%>
</div>
<div class="copyright">&copy; 2008-<%value cyear%> <a href="http://www.liveadmin.net" target="_blank">Live Admin, Live chat customer support system</a>, All rights reserved.</div>
</body>
</html>
<%sec-end footer%>
