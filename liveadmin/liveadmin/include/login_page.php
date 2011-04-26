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






 ini_set('display_errors',0); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<title>Live Admin - Login</title>
<style type="text/css">


body {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	background-color: #333333;
	margin-top: 50px;
}
.wrap {
	width: 500px;
	clear: both;
	margin: 0 auto;
	background-color: #999999;
	padding: 10px;
	border: 1px solid #666666;
}
.copyright {
	width: 500px;
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
}
#login_box {
	padding-top: 10px;
}
#login_box .box_record {
	border-top-style: dotted;
	border-width: 1px;
	border-color: #C0C0C0;
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
	padding-top: 10px;
	text-align: center;
}
.form_button button {
	font-size: 14pt;
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
	height: 20px;
}
#lvad_img_1 {
	border-width: 0px;
}
</style>
</head>
<body>



<div class="wrap">

<div class="upt">
	<a href="<?=LIVEADMIN_WC;?>">
	<img id="lvad_img_1" src="images/live_admin_logo.png"></a><a href="<?=LIVEADMIN_WC;?>"><img id="lvad_img_2" src="images/live_admin_logo_text.png"></a></div>

<?php
include('login_page_e.php'); ?>
<!--  -->

</div>
<script language="javascript" type="text/javascript">
var username_dom = document.getElementById('lv_username_id');

if(typeof(username_dom)!='undefined' && typeof(username_dom.focus)=='function')
{
	username_dom.focus();
}
</script>

</body>
</html>
