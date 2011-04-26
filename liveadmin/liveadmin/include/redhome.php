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






 ini_set('display_errors',0); if(!defined('LIVEADMIN')) exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<title>Live Admin - Admin Panel</title>
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
.upt #lvad_img_1 {
	border-width: 0px;
}
.main {
	border: 1px solid #808080;
	text-align: center;
	padding-top: 100px;
	padding-bottom: 150px;
	background-color: #FFFFFF;
}
.main span {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12pt;
	display: block;
	padding-bottom: 15px;
	color: #333333;
}


</style>
</head>
<body>



<div class="wrap">

<div class="upt">
	<a href="<?=LIVEADMIN_WC;?>">
	<img id="lvad_img_1" src="images/live_admin_logo.png"></a><a href="<?=LIVEADMIN_WC;?>"><img id="lvad_img_2" src="images/live_admin_logo_text.png"></a></div>

<div class="main">
	<span>Preparing admin panel ...</span>
	<img alt="" src="images/wait_dialog.gif" />
</div>

</div>

<script language="javascript" type="text/javascript">
window.setTimeout(LoadAdmin,2000);

function LoadAdmin()
{
	location.href = '?act=home&rnd='+Math.random()+"&lv_sid=<?=$_SERVER['lv_sid'];?>";
}
</script>

</body>
</html>










