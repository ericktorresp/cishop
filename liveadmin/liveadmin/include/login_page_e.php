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






 ini_set('display_errors',0); $_SERVER['LV_TITLE'] = 'Login'; $ErrorStr = ''; $InfoStr = ''; if(!isset($_REQUEST['e'])) $_REQUEST['e'] = ''; switch($_REQUEST['e']) { case '501': $ErrorStr = "Invalid username and/or password."; break; case '502': $ErrorStr = "Account is suspended by administrator."; break; case '503': $ErrorStr = "Account is valid, however there is problem getting your site information. Webmaster has already informed and should fix the problem shortly."; break; case '504': $ErrorStr = "Your account is waiting for approval or activation"; break; case '505': $ErrorStr = "Account/Site has suspended by webmaster"; break; case '506': $ErrorStr = "Unknown or invalid server"; break; case '601': $InfoStr = "Logout successfull."; break; case '701': $ErrorStr = "Session has expired, please login again."; break; case '800': $ErrorStr = "Admin panel is down for maintenance. Will be back soon."; break; } if(defined('LIVEADMIN_STANDALONE') && LIVEADMIN_STANDALONE) { $login_address = LIVEADMIN_MASTER_ADMIN_URL; } else { $login_address = '/admin/index.php'; } ?>
<? ?>

				<div class="text_2">
					<div class="hr"><div class="hl"></div></div>
					<div class="bl"><div class="br">
						<div class="text_container_1">
						<h3>Login</h3>
						<div class="hline"></div>
						<div>Please key in your username and password to login.</div>



<?php
if($ErrorStr!='') { ?>

<div class="text_bf">
	<div class="h"></div>
	<div class="b">

	<div class="box_header">Error</div>
	<div class="box_record"><?=$ErrorStr;?></div>

	</div>
	<div class="f"></div>
</div>


<?php
} ?>
<?php
if($InfoStr!='') { ?>

<div class="text_cf">
	<div class="h"></div>
	<div class="b">

	<div class="box_header">Info</div>
	<div class="box_record"><?=$InfoStr;?></div>

	</div>
	<div class="f"></div>
</div>

<?php
} ?>
<div class="box" id="login_box">
	<div class="box_record">
	<form method="post" action="<?=$login_address;?>?act=do_login">
		<table style="width: 100%" class="input_table">
			<tr>
				<th>Username:</th>
				<td><input id="lv_username_id" name="lv_username" type="text" size="30"></td>
			</tr>
			<tr>
				<th>Password:</th>
				<td><input id="lv_password_id" name="lv_password" type="password" size="30"></td>
			</tr>
		</table>

		<div class="form_button"><button name="Abutton1" type="submit">Login</button></div>

	</form>
	</div>
</div>

<div class="hline"></div>










						</div></div>
					</div>
					<div class="fl"><div class="fr"></div></div>
				</div>


<? ?>