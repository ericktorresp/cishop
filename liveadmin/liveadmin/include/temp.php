<?php

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<s5lang_base>direction</s5lang_base>">



<head>

<meta http-equiv="Content-Language" content="<s5lang_base>Content-Language</s5lang_base>" />
<meta http-equiv="Content-Type" content="text/html; charset=<s5lang_base>charset</s5lang_base>" />
<title><?=$_SERVER['LIVEADMIN_TITLE'];?></title>
<? /* ?>
<link rel="stylesheet" type="text/css" href="style.php?dir=<s5lang_base>direction</s5lang_base>&ver=<?=GetAdminPanelStyleVersion('<s5lang_base>direction</s5lang_base>');?>" />
<? */ ?>
<style type="text/css">
.liveadmin_loading_div
{
	font-family: Arial, Helvetica, sans-serif;
	margin-top: 50px;
}
.liveadmin_loading_div .liveadmin_loading_out
{
	width: 500px;
	border: 1px solid #666666;
	background-color: #999999;
	padding: 10px;
	margin: 0 auto;
}
.liveadmin_loading_div .liveadmin_loading_in
{
	border: 1px solid #808080;
	text-align: center;
	padding: 0px;
	background-color: #EEEEEE;
}
.liveadmin_loading_div .liveadmin_loading_top
{
	background-color: #FFFFF8;
	border-bottom-style: solid;
	border-width: 1px;
	border-color: #808080;
	padding-top: 20px;
	padding-bottom: 20px;
	font-size: 12pt;
	font-weight: bold;
	color: #444444;
}
.liveadmin_loading_div span {
	font-size: 10pt;
	display: block;
	padding-bottom: 15px;
	color: #333333;
	padding-top: 50px;
}
.liveadmin_loading_div em {
	font-size: 9pt;
	display: block;
	padding-top: 15px;
	padding-bottom: 50px;
	color: #666666;
	font-style: normal;
}

</style>

<script language="javascript" type="text/javascript">

function Liveadmin_Set_Loading_text(s)
{
	if(typeof(document.getElementById('liveadmin_loading_text_id'))!='undefined')
		document.getElementById('liveadmin_loading_text_id').innerHTML = s;
};
</script>

</head>

<?php
if(LIVEADMIN_STANDALONE && LIVEADMIN_LITE)
{
	$ver_string = lv_version_string(true);
	$ext_logo = '<img class="exlogo" src="images/lite.png" />';
}
else
{
	$ver_string = lv_version_string(false);
	$ext_logo = '';
}
?>

<body class="yui-skin-sam" id="liveadmin">

<div class="liveadmin_loading_div" id="liveadmin_loading_div_id">
	<center>
		<div class="liveadmin_loading_out">
			<div class="liveadmin_loading_in">
				<div class="liveadmin_loading_top"><?=$ver_string;?></div>
				<span>Preparing admin panel ...</span>
				<img alt="" src="images/wait_dialog.gif" />
				<em id="liveadmin_loading_text_id">Loading...</em>
			</div>
		</div>
	</center>
</div>


<div id="liveadmin_real_div_id" style="visibility: hidden;">

<script language="javascript" type="text/javascript">
Liveadmin_Set_Loading_text('Loading style sheets...');

(function() {
var lvscss = document.createElement('link');
lvscss.type = 'text/css';
lvscss.rel = 'stylesheet';
lvscss.href = "style.php?dir=<s5lang_base>direction</s5lang_base>&ver=<?=GetAdminPanelStyleVersion('<s5lang_base>direction</s5lang_base>');?>";
(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(lvscss);
})();
</script>

<script language="javascript" type="text/javascript">
Liveadmin_Set_Loading_text('Loading language files...');
<?
	$LV_CONF = $_SERVER['LIVEADMIN_CONF'];
	$TL = $LV_CONF['TL'];
	unset($LV_CONF['TL']);
	$TR = $LV_CONF;
?>

var LiveAdminConf = {<? print liveadmin_implode($TR,'','',':','"','"',',');?>};
LiveAdminConf.TL = {<? print liveadmin_implode($TL,'','',':','"','"',',');?>};

Liveadmin_Set_Loading_text('Loading java scripts...');

</script>

<script language="javascript" type="text/javascript" src="js.php?ver=<?=GetAdminPanelJavaScriptVersion();?>"></script>

<script language="javascript" type="text/javascript">
Liveadmin_Set_Loading_text('Loading main panels...');

<?php
foreach($_SERVER['LIVEADMIN_CHAT_PANEL'] as $a=>$v)
{
	print 'contents_chat_panel_'.$a.'= Base64.liveadmin_decode("'.$v.'");'."\n";
}
?>
/*---NO-LITE-START---*/
Liveadmin_Set_Loading_text('Loading google maps api...');
/*---NO-LITE-END---*/
</script>

<? /*---NO-LITE-START---*/ ?>
<script language="javascript" type="text/javascript" src="http://maps.google.com/maps?file=api&v=2&sensor=false&key=<?=lv_get_google_maps_api();?>"></script>
<? /*---NO-LITE-END---*/ ?>








<!--START- SOUND OBJECT -->
<div style="position:absolute;width:0px;height:0px;padding:0px;margin:0px;top:0px;left:0px;" id="sound_object_container_id"></div>
<!--END- SOUND OBJECT -->

<span style="position:absolute;padding:0px;margin:0px;top:0px;left:0px; visibility: hidden; display: none;" id="cck_container_id"></span>

<div id="lv_top1"><img class="icon" src="images/live_admin_logo.png" /><img class="text" src="images/live_admin_logo_text.png" /><?=$ext_logo;?><em><?=$_SERVER['sinfo']['company'];?><br/><s5lang>Welcome</s5lang> <?=$_SERVER['uinfo']['nickname'];?></em></div>

<div id="lv_bottom1"><div class="copyright">&copy; Copyright 2008-<?=date("Y");?> Live Admin, Live chat customer support system, All rights reserved.</div><div class="lv_buttom_info" id="lv_buttom_info_id"><?=lv_version_string();?></div><div class="lv_buttom_clear"></div></div>

<div id="left1">

	<div id="ap_panels">

		<div class="links" id="links_bar_id">
			<div class="link_bar">

				<input type="button" id="config_button_id" name="ConfigButton" value="<s5lang>Configuration</s5lang>">
				<input type="button" id="logout_button_id" name="LogoutButton" value="<s5lang>Logout</s5lang>">
				<input type="button" id="online_button_id" name="OnlineButton" value="<s5lang>Online</s5lang>"/>
			</div>
		</div>


		<div id="notes_id" style="display:none">
			<div class="notes_td">
				<div class="notes" id="lv_notes_id">
				</div>
			</div>
		</div>



		<div class="waiting_clients">
			<div id="lv_left_tabs" class="yui-navset">
			</div>
		</div>

	</div>

</div>



<div id="center1"><div id="lv_tabs" class="yui-navset"></div></div>

</div>

<script language="javascript" type="text/javascript">
Liveadmin_Set_Loading_text('All done.');

document.getElementById('liveadmin_loading_div_id').style.display = 'none';
document.getElementById('liveadmin_real_div_id').style.visibility = 'visible';

</script>


</body>
</html>