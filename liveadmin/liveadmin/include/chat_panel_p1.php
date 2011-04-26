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

<%sec-start chat_panel_p1%>

<div id="chat_panel_p1">
<form style="padding: 0px; margin: 0px" id="form_id_-chat_id-" onsubmit="return(SendText('-chat_id-'));">
	<table style="width: 100%">
		<tr>
			<td id="chat_panel_p1_text">
				<div>
					<textarea name="chat_text" id="text_id_-chat_id-" cols="20" rows="2"></textarea>
					<div id="chat_panel_p1_status_bar"><div class="chat_panel_p1_status_bar"><span class="chat_panel_p1_notes" id="chat_panel_p1_status_bar_id_-chat_id-"></span></div></div>
				</div>
			</td>

			<td id="chat_panel_p1_button">
				<input type="button" id="chat_panel_p1_button_id_-chat_id-" name="SendButton" value="Send">
				<input type="button" id="chat_panel_p1_onhold_id_-chat_id-" name="OnholdButton" value="On Hold">
				<input type="button" id="chat_panel_p1_draft_id_-chat_id-" name="DraftButton" value="Draft">

				<input type="button" id="chat_panel_p1_transfer_id_-chat_id-" name="TransferButton" value="Transfer">

				<input type="button" id="chat_panel_p1_close_id_-chat_id-" name="CloseButton" value="Close">
			</td>
		</tr>
	</table>
</form>
</div>

<%sec-end chat_panel_p1%>


<%sec-start chat_panel_p1_OLD%>

<div id="chat_panel_p1">
<form style="padding: 0px; margin: 0px" id="form_id_-chat_id-" onsubmit="return(SendText('-chat_id-'));">
	<table style="width: 100%">
		<tr>
			<td id="chat_panel_p1_text">
				<textarea name="chat_text" id="text_id_-chat_id-" cols="20" rows="2"></textarea>
			</td>

			<td id="chat_panel_p1_button">
				<span id="chat_panel_p1_button_id_-chat_id-" class="yui-button yui-push-button">
					<span class="first-child">
						<input name="SendBtn1" type="submit" value="Send"/>
					</span>
				</span>
				<div class="hline"></div>
				<span id="chat_panel_p1_onhold_id_-chat_id-" class="yui-button yui-push-button">
					<span class="first-child">
						<input name="SendBtn1" type="button" value="On Hold"/>
					</span>
				</span>
				<span id="chat_panel_p1_draft_id_-chat_id-" class="yui-button yui-push-button">
					<span class="first-child">
						<input name="DraftBtn1" type="button" value="Draft"/>
					</span>
				</span>
				<span id="chat_panel_p1_close_id_-chat_id-" class="yui-button yui-push-button">
					<span class="first-child">
						<input name="SendBtn1" type="button" value="Close"/>
					</span>
				</span>
			</td>
		</tr>
		<tr>
			<td id="chat_panel_p1_status_bar"><div class="chat_panel_p1_status_bar"><span class="chat_panel_p1_notes" id="chat_panel_p1_status_bar_id_-chat_id-"></span></div></td>
		</tr>
	</table>
</form>
</div>

<%sec-end chat_panel_p1_OLD%>










<%sec-start chat_panel_config_0%>

<div id="chat_panel_config_1_id">
	<div class="chat_panel_config_item" id="chat_panel_config_item_0">
		<img src="images/pref_install.png" />
		<div class="chat_panel_config_title"><s5lang>Installation Guide</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Instructions for installing the chat panel on your web site</s5lang></div>
	</div>

	<div class="chat_panel_config_item" id="chat_panel_config_item_1">
		<img src="images/pref_admin_side.png" />
		<div class="chat_panel_config_title"><s5lang>Admin Panel Preferences</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Preferences and configuration of admin panel (this panel)</s5lang></div>
	</div>

	<div class="chat_panel_config_item" id="chat_panel_config_item_2">
		<img src="images/pref_company.png" />
		<div class="chat_panel_config_title"><s5lang>Site Information</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>General information about your site or corporate</s5lang></div>
	</div>

	<div class="chat_panel_config_item" id="chat_panel_config_item_3">
		<img src="images/pref_client_side.png" />
		<div class="chat_panel_config_title"><s5lang>Chat Panel</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Preferences and configuration of client side chat panel</s5lang></div>
	</div>

	<div class="chat_panel_config_item" id="chat_panel_config_item_4">
		<img src="images/pref_users.png" />
		<div class="chat_panel_config_title"><s5lang>Agents</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>System agents, passwords, access levels etc.</s5lang></div>
	</div>


	<div class="chat_panel_config_item" id="chat_panel_config_item_401">
		<img src="images/pref_depts.png" />
		<div class="chat_panel_config_title"><s5lang>Departments</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Define departments or regions and assign agents to each department</s5lang></div>
	</div>


	<div class="chat_panel_config_item" id="chat_panel_config_item_5">
		<img src="images/pref_profile.png" />
		<div class="chat_panel_config_title"><s5lang>Profile</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Personal information and localization.</s5lang></div>
	</div>

	<div class="chat_panel_config_item" id="chat_panel_config_item_6">
		<img src="images/pref_chpass.png" />
		<div class="chat_panel_config_title"><s5lang>Change Password</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Change password of your account.</s5lang></div>
	</div>





	<div class="chat_panel_config_item" id="chat_panel_config_item_9">
		<img src="images/pref_reports.png" />
		<div class="chat_panel_config_title"><s5lang>Statistics</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Statistic reports and charts of system usage.</s5lang></div>
	</div>


</div>


<%sec-end chat_panel_config_0%>


<%sec-start chat_panel_config_1%>

<div id="chat_panel_config_1_id">
	<div class="chat_panel_config_item" id="chat_panel_config_item_1">
		<img src="images/pref_admin_side.png" />
		<div class="chat_panel_config_title"><s5lang>Admin Panel Preferences</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Preferences and configuration of admin panel (this panel)</s5lang></div>
	</div>

	<div class="chat_panel_config_item" id="chat_panel_config_item_5">
		<img src="images/pref_profile.png" />
		<div class="chat_panel_config_title"><s5lang>Profile</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Personal information and localization.</s5lang></div>
	</div>

	<div class="chat_panel_config_item" id="chat_panel_config_item_6">
		<img src="images/pref_chpass.png" />
		<div class="chat_panel_config_title"><s5lang>Change Password</s5lang></div>
		<div class="chat_panel_config_desc"><s5lang>Change password of your account.</s5lang></div>
	</div>
</div>


<%sec-end chat_panel_config_1%>





<%sec-start chat_panel_install_guide%>

<div class="lv_dialog">

<s5lang>To install Live Admin on a page follow these two simple steps</s5lang>:
<div class="hline"></div>
<strong><s5lang>Step 1</s5lang>:</strong><br><s5lang>Add this line of code to your page. It's better to place it between</s5lang> &lt;head&gt; <s5lang>and</s5lang> &lt;/head&gt;, <s5lang>but if not possible place it where ever you like.</s5lang>
<div class="clear"></div>
<div class="fix_height_div_box" dir="ltr">
&lt;script type="text/javascript"&gt;
  (function() {
	var lvs = document.createElement('script'); lvs.type = 'text/javascript'; lvs.async = true;
	lvs.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'client.liveadmin.net/liveadmin.php?key=XX-SITEKEY-XX';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(lvs);
  })();
&lt;/script&gt;
</div>
<div class="hline"></div>
<strong><s5lang>Step 2</s5lang>:</strong><br>
<s5lang>Add this code where you want the clickable status image to be displayed</s5lang>:
<div class="fix_height_div_box" dir="ltr">&lt;span id='liveadmin'&gt;&lt;/span&gt;</div>




<div class="hline"></div>
</div>

<%sec-end chat_panel_install_guide%>

<%sec-start chat_panel_install_guide_stl%>

<div class="lv_dialog">
<div class="lv_dialog_fix_height">

<s5lang>To install Live Admin on a page follow these two simple steps</s5lang>:
<br>
<br>
<div class="hline"></div>
<strong><s5lang>Step 1</s5lang>:</strong><br><s5lang>Add this line of code to your page. It's better to place it between</s5lang> &lt;head&gt; <s5lang>and</s5lang> &lt;/head&gt;, <s5lang>but if not possible place it where ever you like.</s5lang>
<div class="clear"></div>
<div class="fix_height_div_box" dir="ltr">&lt;script language="javascript" type="text/javascript" src="XX-SITEURL-XX?key=XX-SITEKEY-XX"&gt;&lt;/script&gt;</div>
<div class="hline"></div>
<strong><s5lang>Step 2</s5lang>:</strong><br>
<s5lang>Add this code where you want the clickable status image to be displayed</s5lang>:
<div class="fix_height_div_box" dir="ltr">&lt;span id='liveadmin'&gt;&lt;/span&gt;</div>




</div>
<div class="hline"></div>
</div>

<%sec-end chat_panel_install_guide_stl%>
