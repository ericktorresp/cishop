<?php

/***************************************************************
 * Live Admin Standalone
 * Copyright 2008-2011 Dayana Networks Ltd.
 * All rights reserved,
 Live Admin  is  protected  by  Canada and
 * International copyright laws. Unauthorized use or distribution
 * of  Live Admin  is  strictly  prohibited,
 violators  will  be
 * prosecuted. To  obtain  a license for using Live Admin,
 please
 * register at http://www.liveadmin.net/register.php
 *
 * For more information please refer to Live Admin official site:
 *    http://www.liveadmin.net
 *
 * Translation service provided by Google Inc.
 ***************************************************************/
if(!defined('LIVEADMIN')) exit;
class LV_Dialog
{
	private $lv_admin;
	function LV_Dialog(&$lv_admin_in)
	{
		$this->lv_admin = $lv_admin_in;
	}
	function GetDialogsStruct()
	{
		include_once('lang.php');
		$lang = new LV_Lang($_SERVER['sinfo']['language']);
		$chat_window_texts = array();
		if($_SERVER['sinfo']['chat_window_options']=='') $chat_window_options = array();
		else $chat_window_options = unserialize($_SERVER['sinfo']['chat_window_options']);
		$fields = unserialize($_SERVER['sinfo']['extra_fields']);
		$field = array();
		if(isset($_REQUEST['fieldid']) && $_REQUEST['fieldid']!='')
		{
			foreach($fields as $a=>$v)
			{
				if(md5($v['name'])==$_REQUEST['fieldid'])
				{
					$field = $v;
					break;
				}
			}
		}
		$site_news_dialog = array();
		if(isset($_REQUEST['dialog_action']))
		{
			switch($_REQUEST['dialog_action'])
			{
				case 'get_news_feed': if(is_object($this->lv_admin))
				{
					$site_news_dialog = $this->lv_admin->GetSiteNewsByID($_REQUEST['newsid']);
				}
				break;
			}
		}
		$department = array();
		if(isset($_REQUEST['depid']) && $_REQUEST['depid']!='')
		{
			if(isset($chat_window_options['departments'][$_REQUEST['depid']]))
			{
				$department = $chat_window_options['departments'][$_REQUEST['depid']];
			}
		}
		if(!isset($field['name'])) $field['name'] = "";
		if(!isset($_REQUEST['client_uniq'])) $_REQUEST['client_uniq'] = "";
		if(!isset($_REQUEST['server_uniq'])) $_REQUEST['server_uniq'] = "";
		$d = array();
		$d['dialog_adminside'] = array (
			'title' => 'Admin Panel Preference',
			'post' => 'index.php?act=update_admin_panel_pref',
			'width' => '600px',
			'fields'=> array (
				'language' => array (
					'name'=>'language',
					'type'=>'select',
					'label'=>'Language',
					'title'=>'Language of Admin interface',
					'label_for'=>'language',
					'multiple'=>'',
					'options'=>$lang->GetLanguageList(),
					'selected'=>$_SERVER['uinfo']['language']
		),
				'time_zone' => array (
					'name'=>'time_zone',
					'type'=>'select',
					'label'=>'Time Zone',
					'title'=>'Your personal time zone, will be used in chat conversions, transcripts and admin panel.',
					'label_for'=>'time_zone',
					'multiple'=>'',
					'options'=>$this->GetTimeZones(),
					'selected'=>$_SERVER['uinfo']['time_zone']
		),
				'hline1' => array ( 'type'=>'hline',),
				'sound_new_client' => array (
					'name'=>'sound_new_client',
					'type'=>'select',
					'title'=>'This sound will be played when a new client asks for help',
					'label'=>'New client sound',
					'label_for'=>'sound_new_client',
					'multiple'=>'',
					'options'=>$this->GetRings(),
					'add_element_right'=>'<span class="play_sound_button"><button name="dialog_adminside_play_new_client_sound_name" id="dialog_adminside_play_new_client_sound_id">Play</button></span>',
					'selected'=>$_SERVER['uinfo']['sound_new_client']
		),
				'sound_new_message' => array (
					'name'=>'sound_new_message',
					'type'=>'select',
					'title'=>'This sound will be played when client send message in chat session',
					'label'=>'New message sound',
					'label_for'=>'sound_new_message',
					'multiple'=>'',
					'options'=>$this->GetRings(),
					'add_element_right'=>'<span class="play_sound_button"><button name="dialog_adminside_play_new_msg_sound_name" id="dialog_adminside_play_new_msg_sound_id">Play</button></span>',
					'selected'=>$_SERVER['uinfo']['sound_new_message']
		),
				'hline2' => array ( 'type'=>'hline',),
				'send_init_1' => array (
					'name'=>'send_init_1',
					'type'=>'text',
					'title'=>'When you accept a chat session this and following two strings will be sent to client automatically',
					'label'=>'Initial Chat Msg 1',
					'label_for'=>'send_init_1',
					'size'=>'50',
					'text'=>$_SERVER['uinfo']['send_init_1']
		),
				'send_init_2' => array (
					'name'=>'send_init_2',
					'type'=>'text',
					'title'=>'Refer to Initial Chat Msg 1',
					'label'=>'Initial Chat Msg 2',
					'label_for'=>'send_init_2',
					'size'=>'50',
					'text'=>$_SERVER['uinfo']['send_init_2']
		),
				'send_init_3' => array (
					'name'=>'send_init_3',
					'type'=>'text',
					'title'=>'Refer to Initial Chat Msg 1',
					'label'=>'Initial Chat Msg 3',
					'label_for'=>'send_init_3',
					'size'=>'50',
					'text'=>$_SERVER['uinfo']['send_init_3']
		),
				'hline3' => array ('type'=>'hline',),
				'auto_link' => array (
					'name'=>'auto_link',
					'type'=>'select',
					'title'=>'This will make internet addresses in chat session to be clickable at client side',
					'label'=>'Auto link generator',
					'label_for'=>'auto_link',
					'options'=>array (
						'0' => 'No',
						'1' => 'Yes'
						),
					'selected'=>$_SERVER['uinfo']['auto_link']
						),
				'show_missed_calls' => array (
						'name'=>'show_missed_calls',
						'type'=>'select',
						'title'=>'This will show missed calls in waiting clients list',
						'label'=>'Show Missed Calls',
						'label_for'=>'show_missed_calls',
						'options'=>array (
							'0' => 'Do not show',
							'1' => 'Show in last 24 hours',
							'2' => 'Show in last 2 days',
							'3' => 'Show in last 3 days',
							'4' => 'Show in last 4 days',
							'5' => 'Show in last 5 days',
							'6' => 'Show in last 6 days',
							'7' => 'Show in last 7 days'
							),
						'selected'=>$_SERVER['uinfo']['show_missed_calls']
							),
				'check_update' => array (
							'name'=>'check_update',
							'type'=>'select',
							'title'=>'This will check for available updates everytime you login',
							'label'=>'Check for updates',
							'label_for'=>'check_update',
							'options'=>array (
								'0' => 'No',
								'1' => 'Yes'
								),
							'selected'=>$_SERVER['uinfo']['check_update'],
							'show_if'=>$_SERVER['uinfo']['access_level'].'::0'
							),
				'hline4' => array ('type'=>'hline',)
							)
							);
							$d['dialog_clientside'] = array (
			'title' => 'Site Information',
			'post' => 'index.php?act=update_site_panel_pref',
			'width' => '500px',
			'fields'=> array (
				'site_key' => array (
					'name'=>'site_key',
					'type'=>'span',
					'title'=>'Unique Key for your site,
					this will be used in code that need to put on site pages.',
					'label'=>'Site Key',
					'label_for'=>'site_key',
					'text'=>$_SERVER['sinfo']['key']
							),
			'hline0' => array ('type'=>'hline',),
			'company' => array (
				'name'=>'company',
				'type'=>'text',
				'title'=>'Company or Site name,
				will be used in chat client',
				'label'=>'Company Name',
				'label_for'=>'company',
				'size'=>'50',
				'text'=>$_SERVER['sinfo']['company']
							),
			'general_email' => array (
				'name'=>'general_email',
				'type'=>'text',
				'title'=>'General email address of company',
				'label'=>'General Email',
				'label_for'=>'general_email',
				'size'=>'50',
				'text'=>$_SERVER['sinfo']['general_email']
							),
			'hline1' => array ( 'type'=>'hline', ),
			'address' => array (
				'name'=>'address',
				'type'=>'text',
				'title'=>'Optional company or site address',
				'label'=>'Company Address',
				'label_for'=>'address',
				'size'=>'50',
				'text'=>$_SERVER['sinfo']['address']
							),
			'city' => array (
				'name'=>'city',
				'type'=>'text',
				'title'=>'City of Company or site',
				'label'=>'City',
				'label_for'=>'city',
				'size'=>'30',
				'text'=>$_SERVER['sinfo']['city']
							),
			'state' => array (
				'name'=>'state',
				'type'=>'select',
				'title'=>'State of Company or site',
				'label'=>'State',
				'label_for'=>'state',
				'options' => GetStates(),
				'selected'=>$_SERVER['sinfo']['state']
							),
			'country' => array (
				'name'=>'country',
				'type'=>'select',
				'title'=>'Country of Company or site',
				'label'=>'Country',
				'label_for'=>'country',
				'options' => GetCountries(),
				'selected'=>$_SERVER['sinfo']['country']
							),
			'postal_code' => array (
				'name'=>'postal_code',
				'type'=>'text',
				'title'=>'Postal or Zip code of Company or site',
				'label'=>'Zip or Postal Code',
				'label_for'=>'postal_code',
				'size'=>'20',
				'text'=>$_SERVER['sinfo']['postal_code']
							),
			'hline2' => array ( 'type'=>'hline', ),
			'time_zone' => array (
				'name'=>'time_zone',
				'type'=>'select',
				'label'=>'Global Time Zone',
				'title'=>'Time zone of your site,
				this will be the default time zone for all agents at the time of creation.',
				'label_for'=>'time_zone',
				'multiple'=>'',
				'options'=>$this->GetTimeZones(),
				'selected'=>$_SERVER['sinfo']['time_zone']
							),
			'hline3' => array ( 'type'=>'hline' ),
			'manage_news_button' => array (
				'name'=>'manage_news_button',
				'type'=>'button',
				'title'=>'Publish news to admin panel,
				it will be displayed to all agents',
				'label'=>'News',
				'label_for'=>'manage_news_button',
				'text'=>'Manage News Feeds',
				'id'=>'config_manage_news_button_id'
				),
			'hline4' => array (
				'type'=>'hline',
				'show_if'=>((LIVEADMIN_STANDALONE)?'1':'0').'::1'
				),
			'google_map_api_key_title' => array (
				'name'=>'google_map_api_key_title',
				'type'=>'parag',
				'label'=>'Google Maps Api requires each client to have their own API key,
				you can get one at the following address,
				then enter it here to hide the startup warning,',
				'label2'=>'<br><a target="_blank" href="http://code.google.com/apis/maps/signup.html">http://code.google.com/apis/maps/signup.html</a><br>',
				'show_if'=>((LIVEADMIN_STANDALONE)?'1':'0').'::1'
				),
			'chat_window_optionsX_Xgoogle_map_api_key' => array (
				'name'=>'chat_window_optionsX_Xgoogle_map_api_key',
				'type'=>'text',
				'title'=>'Your own Google Maps Api key',
				'label'=>'Google Maps Api Key',
				'label_for'=>'chat_window_optionsX_Xgoogle_map_api_key',
				'size'=>'50',
				'text'=>ArrayMember($chat_window_options,'google_map_api_key',''),
				'no_database'=>true,
				'show_if'=>((LIVEADMIN_STANDALONE)?'1':'0').'::1'
				),
			'hline5' => array ( 'type'=>'hline', )
				)
				);
				$d['dialog_profile'] = array ( 'title' => 'Profile',
				'post' => 'index.php?act=update_profile_pref',
				'width' => '500px',
				'fields'=> array ( 'nickname' => array ( 'name'=>'nickname',
				'type'=>'text',
				'title'=>'Your nick name,
				this will be shown in client side',
				'label'=>'Nick Name',
				'label_for'=>'nickname',
				'size'=>'50',
				'text'=>$_SERVER['uinfo']['nickname'] ),
				'hline1' => array ( 'type'=>'hline',
				),
				'firstname' => array ( 'name'=>'firstname',
				'type'=>'text',
				'title'=>'Your first name',
				'label'=>'First Name',
				'label_for'=>'firstname',
				'size'=>'50',
				'text'=>$_SERVER['uinfo']['firstname'] ),
				'lastname' => array ( 'name'=>'lastname',
				'type'=>'text',
				'title'=>'Your last name',
				'label'=>'Last Name',
				'label_for'=>'lastname',
				'size'=>'50',
				'text'=>$_SERVER['uinfo']['lastname'] ),
				'email' => array ( 'name'=>'email',
				'type'=>'text',
				'title'=>'Your email address',
				'label'=>'Email',
				'label_for'=>'email',
				'size'=>'50',
				'text'=>$_SERVER['uinfo']['email'] ),
				'hline2' => array ( 'type'=>'hline',
				),
				'pic' => array ( 'name'=>'pic',
				'type'=>'button',
				'title'=>'Your photo to be shown in client side',
				'label'=>'Photo',
				'label_for'=>'pic',
				'id'=>'config_profile_pic_button_id',
				'text'=>'Manage Photo' ),
				'hline3' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_chpass'] = array ( 'title' => 'Change Password',
				'post' => 'index.php?act=update_password_pref',
				'width' => '450px',
				'fields'=> array ( 'current_password' => array ( 'name'=>'current_password',
				'type'=>'password',
				'title'=>'Your current password',
				'label'=>'Current Password',
				'label_for'=>'current_password',
				'size'=>'30',
				'text'=>'' ),
				'new_password' => array ( 'name'=>'new_password',
				'type'=>'password',
				'title'=>'New password should be at least 5 characters long',
				'label'=>'New Password',
				'label_for'=>'new_password',
				'size'=>'30',
				'text'=>'' ),
				'new_password_c' => array ( 'name'=>'new_password_c',
				'type'=>'password',
				'title'=>'To confirm please key in the new password again. It must be the same as new password',
				'label'=>'New Password (confirm)',
				'label_for'=>'new_password_c',
				'size'=>'30',
				'text'=>'' ),
				'hline1' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_chatpanel'] = array ( 'title' => 'Chat Panel',
				'post' => 'index.php?act=update_chat_panel_pref',
				'width' => '600px',
				'fix_height'=> true,
				'fields'=> array ( 'language' => array ( 'name'=>'language',
				'type'=>'select',
				'title'=>'Language of client side chat panel',
				'label'=>'Language',
				'label_for'=>'language',
				'multiple'=>'',
				'options'=>$lang->GetLanguageList(),
				'selected'=>$_SERVER['sinfo']['language'] ),
				'hline0' => array ( 'type'=>'hline',
				),
				'theme2' => array ( 'name'=>'theme2',
				'type'=>'button',
				'label'=>'Theme',
				'label_for'=>'theme2',
				'title'=>'Main theme of client side chat panel',
				'text'=>LIVEADMIN_WT.','.$_SERVER['sinfo']['theme'],
				'eltitle'=>'',
				'id'=>'config_theme_button_id',
				'no_database'=>true ),
				'theme' => array ( 'name'=>'theme',
				'type'=>'text',
				'label'=>'Theme',
				'label_for'=>'theme',
				'text'=>$_SERVER['sinfo']['theme'] ),
				'chat_window_optionsX_Xtheme_custom_css' => array ( 'name'=>'chat_window_optionsX_Xtheme_custom_css',
				'type'=>'text',
				'title'=>'URL of a style sheet for customizing the chat panel. If assigned this CSS will be loaded after style sheet of the theme. This feature is not available while account is in trial mode.',
				'label'=>'Custom CSS URL',
				'label_for'=>'chat_window_optionsX_Xtheme_custom_css',
				'size'=>'50',
				'text'=>ArrayMember($chat_window_options,'theme_custom_css',''),
				'no_database'=>true ),
				'hline0_1' => array ( 'type'=>'hline',
				),
				'on_off_theme2' => array ( 'name'=>'on_off_theme2',
				'type'=>'button',
				'label'=>'Status Image Theme',
				'label_for'=>'on_off_theme2',
				'title'=>'Theme of status indicator,
				will be used in web pages to show if an agent is online or not. Set to custom to use your own images.',
				'text'=>LIVEADMIN_WOF.','.$_SERVER['sinfo']['on_off_theme'],
				'eltitle'=>'',
				'id'=>'config_on_off_theme_button_id',
				'no_database'=>true ),
				'on_off_theme' => array ( 'name'=>'on_off_theme',
				'type'=>'text',
				'label'=>'Status Image Theme',
				'label_for'=>'on_off_theme',
				'text'=>$_SERVER['sinfo']['on_off_theme'] ),
				'offline_image' => array ( 'name'=>'offline_image',
				'type'=>'text',
				'title'=>'When Status Image Theme is custom,
				this will be full URL of an image to be shown on site when all agents are offline',
				'label'=>'Offline Image URL',
				'label_for'=>'offline_image',
				'size'=>'70',
				'text'=>$_SERVER['sinfo']['offline_image'] ),
				'online_image' => array ( 'name'=>'online_image',
				'type'=>'text',
				'title'=>'When Status Image Theme is custom,
				this will be full URL of an image to be shown on site when at least one agent is online',
				'label'=>'Online Image URL',
				'label_for'=>'online_image',
				'size'=>'70',
				'text'=>$_SERVER['sinfo']['online_image'] ),
				'hline1' => array ( 'type'=>'hline',
				),
				'strings_button' => array ( 'name'=>'strings_button',
				'type'=>'button',
				'title'=>'All strings used in client side can be customized here',
				'label'=>'Strings',
				'label_for'=>'strings_button',
				'text'=>'Manage Strings',
				'id'=>'config_strings_button_id' ),
				'fields_button' => array ( 'name'=>'fields_button',
				'type'=>'button',
				'title'=>'If you wish to get more information from client before chat started define them here',
				'label'=>'Extra Fields',
				'label_for'=>'fields_button',
				'text'=>'Manage Extra Fields',
				'id'=>'config_fields_button_id' ),
				'blocked_clients_button' => array ( 'name'=>'blocked_clients_button',
				'type'=>'button',
				'title'=>'If a client blocked by an agent,
				this section helps you to remove the restrictions',
				'label'=>'Blocked Clients',
				'label_for'=>'blocked_clients_button',
				'text'=>'Manage Blocked Clients',
				'id'=>'config_blocked_clients_button_id' ),
				'hline2' => array ( 'type'=>'hline',
				),
				'wait_find_rep_s' => array ( 'name'=>'wait_find_rep_s',
				'type'=>'text',
				'title'=>'When a client asks for chat you have this amount of seconds to answer,
				when time passed If No Answer (option bellow) will be applied',
				'label'=>'Seconds wait for agent',
				'label_for'=>'wait_find_rep_s',
				'size'=>'10',
				'text'=>$_SERVER['sinfo']['wait_find_rep_s'] ),
				'no_answer_act' => array ( 'name'=>'no_answer_act',
				'type'=>'select',
				'title'=>'If no agent answer the client in above option this action will be taken',
				'label'=>'If no answer',
				'label_for'=>'no_answer_act',
				'multiple'=>'',
				'options'=>array ( '0'=>'<s5lang>Take a Message</s5lang>',
				'1'=>'<s5lang>Show a note to try again later</s5lang>' ),
				'selected'=>$_SERVER['sinfo']['no_answer_act'] ),
				'no_answer_email' => array ( 'name'=>'no_answer_email',
				'type'=>'text',
				'title'=>'Email address to be used when client sent to leave a message screen',
				'label'=>'Leave message email',
				'label_for'=>'no_answer_email',
				'size'=>'50',
				'text'=>$_SERVER['sinfo']['no_answer_email'] ),
				'no_answer_email_type' => array ( 'name'=>'no_answer_email_type',
				'type'=>'select',
				'title'=>'Message will be send in this format',
				'label'=>'Email format',
				'label_for'=>'no_answer_email_type',
				'multiple'=>'',
				'options'=>array ( '0'=>'Text',
				'1'=>'HTML' ),
				'selected'=>$_SERVER['sinfo']['no_answer_email_type'] ),
				'offline_act' => array ( 'name'=>'offline_act',
				'type'=>'select',
				'title'=>'When all agents are offline and a client click on chat button this action will be taken',
				'label'=>'Offline action',
				'label_for'=>'offline_act',
				'multiple'=>'',
				'options'=>array ( '0' => '<s5lang>Assume online and look for an agent</s5lang>',
				'1' => '<s5lang>Do not allow client to open chat screen</s5lang>',
				'2' => '<s5lang>Send directly to take message</s5lang>',
				'3' => '<s5lang>Show client a busy notice</s5lang>' ),
				'selected'=>$_SERVER['sinfo']['offline_act'] ),
				'hline3' => array ( 'type'=>'hline',
				),
				'flash_install' => array ( 'name'=>'flash_install',
				'type'=>'select',
				'title'=>'Sound system needs Macromedia or Adobe flash to be installed,
				If client does not have flash player this option allows them to install it',
				'label'=>'Flash installation',
				'label_for'=>'flash_install',
				'multiple'=>'',
				'options'=>array ( '0' => '<s5lang>Do not install flash (no sound)</s5lang>',
				'1' => '<s5lang>Prompt to install flash if not installed</s5lang>' ),
				'selected'=>$_SERVER['sinfo']['flash_install'] ),
				'sound_new_message' => array ( 'name'=>'sound_new_message',
				'type'=>'select',
				'title'=>'At client side this sound will be played when you send a message',
				'label'=>'New message sound',
				'label_for'=>'sound_new_message',
				'multiple'=>'',
				'options'=>$this->GetRings(),
				'add_element_right'=>'<span class="play_sound_button"><button name="dialog_chatpanel_play_new_msg_sound_name" id="dialog_chatpanel_play_new_msg_sound_id">Play</button></span>',
				'selected'=>$_SERVER['sinfo']['sound_new_message'] ),
				'hline4' => array ( 'type'=>'hline',
				),
				'auto_invite' => array ( 'name'=>'auto_invite',
				'type'=>'select',
				'title'=>'If enabled visitors will be invited to chat automatically',
				'label'=>'Auto invite',
				'label_for'=>'auto_invite',
				'multiple'=>'',
				'options'=>array ( '0' => '<s5lang>Disabled</s5lang>',
				'1' => '<s5lang>Enabled without invite message</s5lang>',
				'2' => '<s5lang>Enabled with invite message</s5lang>' ),
				'selected'=>$_SERVER['sinfo']['auto_invite'] ),
				'auto_invite_delay' => array ( 'name'=>'auto_invite_delay',
				'type'=>'text',
				'title'=>'If auto invite is enabled,
				visitors will be invited to chat after this amount of idle time in seconds on a page,
				0 will open the chat panel immediately.',
				'label'=>'Auto invite delay',
				'label_for'=>'auto_invite_delay',
				'size'=>'50',
				'text'=>$_SERVER['sinfo']['auto_invite_delay'] ),
				'chat_window_optionsX_Xauto_invite_mode' => array ( 'name'=>'chat_window_optionsX_Xauto_invite_mode',
				'type'=>'select',
				'title'=>'If auto invite is enabled,
				do the following for the same user who invited once',
				'label'=>'Auto Invite mode',
				'label_for'=>'chat_window_optionsX_Xauto_invite_mode',
				'multiple'=>'',
				'options'=>array ( '0' => '<s5lang>Invite on every visit</s5lang>',
				'1' => '<s5lang>Do not invite again for the session</s5lang>',
				'2' => '<s5lang>Do not invite again for 1 day</s5lang>',
				'3' => '<s5lang>Do not invite again for 7 days</s5lang>',
				'4' => '<s5lang>Do not invite again for 30 days</s5lang>',
				'5' => '<s5lang>Do not invite again for 1 year</s5lang>' ),
				'selected'=>ArrayMember($chat_window_options,'auto_invite_mode','0'),
				'no_database'=>true ),
				'enable_callback' => array ( 'name'=>'enable_callback',
				'type'=>'select',
				'title'=>'When enabled agents and roots can invite a visitor to chat. This may put some extra load on your site,
				therefor disabled by default.',
				'label'=>'Enable Manual Invite',
				'label_for'=>'enable_callback',
				'multiple'=>'',
				'options'=>array ( '0' => '<s5lang>Disabled</s5lang>',
				'1' => '<s5lang>Enabled without invite message</s5lang>',
				'2' => '<s5lang>Enabled with invite message</s5lang>' ),
				'selected'=>$_SERVER['sinfo']['enable_callback'] ),
				'invite_image_button' => array ( 'name'=>'invite_image_button',
				'type'=>'button',
				'title'=>'Here you can customize the invitation dialog box.',
				'label'=>'Invite Dialog',
				'label_for'=>'invite_image_button',
				'text'=>'Customize Invite Dialog Box',
				'id'=>'config_invite_image_button_id' ),
				'hline5' => array ( 'type'=>'hline',
				),
				'show_affiliate_link' => array ( 'name'=>'show_affiliate_link',
				'type'=>'select',
				'title'=>'When enabled affiliate link will be shown in chat panel,
				this feature cannot be turn off in trial mode.',
				'label'=>'Affiliate Link',
				'label_for'=>'show_affiliate_link',
				'multiple'=>'',
				'options'=>array ( '0' => '<s5lang>Do not show the link</s5lang>',
				'1' => '<s5lang>Show affiliate link in chat panel</s5lang>' ),
				'selected'=>$_SERVER['sinfo']['show_affiliate_link'],
				'trial_mode_value'=>1 ),
				'hline6' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_invite_image'] = array ( 'title' => 'Invite Dialog',
				'post' => 'index.php?act=update_invite_dialog',
				'width' => '500px',
				'fields'=> array ( 'dlg_title' => array ( 'name'=>'dlg_title',
				'type'=>'parag',
				'label'=>'If you invite a client manually or automatically,
				a default invite box with yes and no buttons will appear. Here you can customize that box.' ),
				'hline0' => array ( 'type'=>'hline',
				),
				'chat_window_optionsX_Xinvite_image_url' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_url',
				'type'=>'text',
				'title'=>'Complete internet address of an image representing the invite dialog box',
				'label'=>'Image URL',
				'label_for'=>'chat_window_optionsX_Xinvite_image_url',
				'size'=>'50',
				'text'=>ArrayMember($chat_window_options,'invite_image_url',''),
				),
				'chat_window_optionsX_Xinvite_image_width' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_width',
				'type'=>'text',
				'title'=>'Width of the invite image in pixel,
				this must be a number',
				'label'=>'Image Width',
				'label_for'=>'chat_window_optionsX_Xinvite_image_width',
				'size'=>'10',
				'text'=>ArrayMember($chat_window_options,'invite_image_width',''),
				),
				'chat_window_optionsX_Xinvite_image_height' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_height',
				'type'=>'text',
				'title'=>'Height of the invite image in pixel,
				this must be a number',
				'label'=>'Image Height',
				'label_for'=>'chat_window_optionsX_Xinvite_image_height',
				'size'=>'10',
				'text'=>ArrayMember($chat_window_options,'invite_image_height',''),
				),
				'hline1' => array ( 'type'=>'hline',
				),
				'chat_window_optionsX_Xinvite_image_yes_x' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_yes_x',
				'type'=>'text',
				'title'=>'Position of the Yes button in invite image',
				'label'=>'Yes Button X',
				'label_for'=>'chat_window_optionsX_Xinvite_image_yes_x',
				'size'=>'10',
				'text'=>ArrayMember($chat_window_options,'invite_image_yes_x',''),
				),
				'chat_window_optionsX_Xinvite_image_yes_y' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_yes_y',
				'type'=>'text',
				'title'=>'Position of the Yes button in invite image',
				'label'=>'Yes Button Y',
				'label_for'=>'chat_window_optionsX_Xinvite_image_yes_y',
				'size'=>'10',
				'text'=>ArrayMember($chat_window_options,'invite_image_yes_y',''),
				),
				'chat_window_optionsX_Xinvite_image_yes_w' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_yes_w',
				'type'=>'text',
				'title'=>'Width of the Yes button in invite image',
				'label'=>'Yes Button Width',
				'label_for'=>'chat_window_optionsX_Xinvite_image_yes_w',
				'size'=>'10',
				'text'=>ArrayMember($chat_window_options,'invite_image_yes_w',''),
				),
				'chat_window_optionsX_Xinvite_image_yes_h' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_yes_h',
				'type'=>'text',
				'title'=>'Height of the Yes button in invite image',
				'label'=>'Yes Button Height',
				'label_for'=>'chat_window_optionsX_Xinvite_image_yes_h',
				'size'=>'10',
				'text'=>ArrayMember($chat_window_options,'invite_image_yes_h',''),
				),
				'hline2' => array ( 'type'=>'hline',
				),
				'chat_window_optionsX_Xinvite_image_no_x' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_no_x',
				'type'=>'text',
				'title'=>'Position of the NO button in invite image',
				'label'=>'No Button X',
					'label_for'=>'chat_window_optionsX_Xinvite_image_no_x',
					'size'=>'10',
					'text'=>ArrayMember($chat_window_options,'invite_image_no_x',''),
				),
					'chat_window_optionsX_Xinvite_image_no_y' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_no_y',
					'type'=>'text',
					'title'=>'Position of the NO button in invite image',
					'label'=>'No Button Y',
					'label_for'=>'chat_window_optionsX_Xinvite_image_no_y',
					'size'=>'10',
					'text'=>ArrayMember($chat_window_options,'invite_image_no_y',''),
				),
					'chat_window_optionsX_Xinvite_image_no_w' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_no_w',
					'type'=>'text',
					'title'=>'Width of the NO button in invite image',
					'label'=>'No Button Width',
					'label_for'=>'chat_window_optionsX_Xinvite_image_no_w',
					'size'=>'10',
					'text'=>ArrayMember($chat_window_options,'invite_image_no_w',''),
				),
					'chat_window_optionsX_Xinvite_image_no_h' => array ( 'name'=>'chat_window_optionsX_Xinvite_image_no_h',
					'type'=>'text',
					'title'=>'Height of the NO button in invite image',
					'label'=>'No Button Height',
					'label_for'=>'chat_window_optionsX_Xinvite_image_no_h',
					'size'=>'10',
					'text'=>ArrayMember($chat_window_options,'invite_image_no_h',''),
				),
					'hline3' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_agents_add'] = array ( 'title' => 'Add Agent',
					'post' => 'index.php?act=update_agents_add_pref',
					'width' => '500px',
					'fields'=> array ( 'username' => array ( 'name'=>'username',
					'type'=>'text',
					'title'=>'Username of agent,
					will be used for login',
					'label'=>'User Name',
					'label_for'=>'username',
					'size'=>'50',
					'text'=>'' ),
					'new_password' => array ( 'name'=>'new_password',
					'type'=>'password',
					'title'=>'Password should be at least 5 characters long',
					'label'=>'Password',
					'label_for'=>'new_password',
					'size'=>'50',
					'text'=>'' ),
					'new_password_c' => array ( 'name'=>'new_password_c',
					'type'=>'password',
					'title'=>'Key in the password again',
					'label'=>'Password (confirm)',
					'label_for'=>'new_password_c',
					'size'=>'50',
					'text'=>'' ),
					'nickname' => array ( 'name'=>'nickname',
					'type'=>'text',
					'title'=>'Nickname of agent,
					this will be shown in client chat panel in chat session',
					'label'=>'Nick Name',
					'label_for'=>'nickname',
					'size'=>'50',
					'text'=>'' ),
					'hline1' => array ( 'type'=>'hline',
				),
					'firstname' => array ( 'name'=>'firstname',
					'type'=>'text',
					'title'=>'First name of agent',
					'label'=>'First Name',
					'label_for'=>'firstname',
					'size'=>'50',
					'text'=>'' ),
					'lastname' => array ( 'name'=>'lastname',
					'type'=>'text',
					'title'=>'Last name of agent',
					'label'=>'Last Name',
					'label_for'=>'lastname',
					'size'=>'50',
					'text'=>'' ),
					'email' => array ( 'name'=>'email',
					'type'=>'text',
					'title'=>'Email address of agent',
					'label'=>'Email',
					'label_for'=>'email',
					'size'=>'50',
					'text'=>'' ),
					'hline2' => array ( 'type'=>'hline',
				),
					'access_level' => array ( 'name'=>'access_level',
					'type'=>'select',
					'title'=>'Root access gives all privileges while Agent can only customize the admin panel',
					'label'=>'Access Level',
					'label_for'=>'access_level',
					'options' => array ( '0' => 'Root',
					'1' => 'Agent' ),
					'selected'=>'1' ),
					'account_status' => array ( 'name'=>'ac_status',
					'type'=>'select',
					'title'=>'By default is agent active or suspended,
					a suspended agent can not login to admin panel',
					'label'=>'Account Status',
					'label_for'=>'ac_status',
					'options' => array ( '0' => 'Suspended',
					'1' => 'Active' ),
					'selected'=>'1' ),
					'time_zone' => array ( 'name'=>'time_zone',
					'type'=>'select',
					'label'=>'Time Zone',
					'title'=>'Time zone of agent,
					will be used in chat conversions,
					transcripts and admin panel.',
					'label_for'=>'time_zone',
					'multiple'=>'',
					'options'=>$this->GetTimeZones(),
					'selected'=>$_SERVER['sinfo']['time_zone'] ),
					'can_see_agents' => array ( 'name'=>'can_see_agents',
					'type'=>'select',
					'title'=>'Allow this agent to see other online agents,
					this option is set to Visible for all root agents',
					'label'=>'Other Agents',
					'label_for'=>'can_see_agents',
					'options' => array ( '0' => 'Hide',
					'1' => 'Visible' ),
					'selected'=>'0' ),
					'can_see_visitors' => array ( 'name'=>'can_see_visitors',
					'type'=>'select',
					'title'=>'Allow this agent to see site visitors,
					this option is set to Visible for all root agents',
					'label'=>'Site Visitors',
					'label_for'=>'can_see_visitors',
					'options' => array ( '0' => 'Hide',
					'1' => 'Visible' ),
					'selected'=>'0' ),
					'hide_news' => array ( 'name'=>'hide_news',
					'type'=>'select',
					'title'=>'Allow this agent to see the news tab,
					all root agents already has access to news tab',
					'label'=>'Site News',
					'label_for'=>'hide_news',
					'options' => array ( '1' => 'Hide',
					'0' => 'Visible' ),
					'selected'=>'0' ),
					'hline3' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_agents_edit'] = array ( 'title' => 'Edit Agent',
					'post' => 'index.php?act=update_agents_edit_pref',
					'width' => '500px',
					'__GET_USER__' => 'userid',
					'fields'=> array ( 'userid' => array ( 'name'=>'userid',
					'type'=>'hidden',
					'text'=>'__GET_USER__userid' ),
					'username' => array ( 'name'=>'username',
					'type'=>'text',
					'title'=>'Username of agent,
					will be used for login',
					'label'=>'User Name',
					'label_for'=>'username',
					'size'=>'50',
					'text'=>'__GET_USER__username' ),
					'new_password' => array ( 'name'=>'new_password',
					'title'=>'Password should be at least 5 characters long',
					'type'=>'password',
					'label'=>'Password',
					'label_for'=>'new_password',
					'size'=>'50',
					'text'=>'xxxxxx' ),
					'new_password_c' => array ( 'name'=>'new_password_c',
					'type'=>'password',
					'title'=>'Key in the password again',
					'label'=>'Password (confirm)',
					'label_for'=>'new_password_c',
					'size'=>'50',
					'text'=>'xxxxxx' ),
					'nickname' => array ( 'name'=>'nickname',
					'type'=>'text',
					'label'=>'Nick Name',
					'title'=>'Nickname of agent,
					this will be shown in client chat panel in chat session',
					'label_for'=>'nickname',
					'size'=>'50',
					'text'=>'__GET_USER__nickname' ),
					'hline1' => array ( 'type'=>'hline',
				),
					'firstname' => array ( 'name'=>'firstname',
					'type'=>'text',
					'title'=>'First name of agent',
					'label'=>'First Name',
					'label_for'=>'firstname',
					'size'=>'50',
					'text'=>'__GET_USER__firstname' ),
					'lastname' => array ( 'name'=>'lastname',
					'type'=>'text',
					'title'=>'Last name of agent',
					'label'=>'Last Name',
					'label_for'=>'lastname',
					'size'=>'50',
					'text'=>'__GET_USER__lastname' ),
					'email' => array ( 'name'=>'email',
					'type'=>'text',
					'title'=>'Email address of agent',
					'label'=>'Email',
					'label_for'=>'email',
					'size'=>'50',
					'text'=>'__GET_USER__email' ),
					'hline2' => array ( 'type'=>'hline',
				),
					'access_level' => array ( 'name'=>'access_level',
					'type'=>'select',
					'title'=>'Root access gives all privileges while Agent can only customize the admin panel',
					'label'=>'Access Level',
					'label_for'=>'access_level',
					'options' => array ( '0' => 'Root',
					'1' => 'Agent' ),
					'selected'=>'__GET_USER__access_level' ),
					'ac_status' => array ( 'name'=>'ac_status',
					'type'=>'select',
					'title'=>'By default is agent active or suspended,
					a suspended agent can not login to admin panel',
					'label'=>'Account Status',
					'label_for'=>'ac_status',
					'options' => array ( '0' => 'Suspended',
					'1' => 'Active' ),
					'selected'=>'__GET_USER__ac_status' ),
					'time_zone' => array ( 'name'=>'time_zone',
					'type'=>'select',
					'label'=>'Time Zone',
					'title'=>'Time zone of agent,
					will be used in chat conversions,
					transcripts and admin panel.',
					'label_for'=>'time_zone',
					'multiple'=>'',
					'options'=>$this->GetTimeZones(),
					'selected'=>'__GET_USER__time_zone' ),
					'can_see_agents' => array ( 'name'=>'can_see_agents',
					'type'=>'select',
					'title'=>'Allow this agent to see other online agents,
					this option is set to Visible for all root agents',
					'label'=>'Other Agents',
					'label_for'=>'can_see_agents',
					'options' => array ( '0' => 'Hide',
					'1' => 'Visible' ),
					'selected'=>'__GET_USER__can_see_agents' ),
					'can_see_visitors' => array ( 'name'=>'can_see_visitors',
					'type'=>'select',
					'title'=>'Allow this agent to see site visitors,
					this option is set to Visible for all root agents',
					'label'=>'Site Visitors',
					'label_for'=>'can_see_visitors',
					'options' => array ( '0' => 'Hide',
					'1' => 'Visible' ),
					'selected'=>'__GET_USER__can_see_visitors' ),
					'hide_news' => array ( 'name'=>'hide_news',
					'type'=>'select',
					'title'=>'Allow this agent to see the news tab,
					all root agents already has access to news tab',
					'label'=>'Site News',
					'label_for'=>'hide_news',
					'options' => array ( '1' => 'Hide',
					'0' => 'Visible' ),
					'selected'=>'__GET_USER__hide_news' ),
					'hline3' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_fields_add_text'] = array ( 'title' => 'Add new text field',
					'post' => 'index.php?act=update_fields_add_pref&type=text',
					'width' => '500px',
					'fields'=> array ( 'name' => array ( 'name'=>'name',
					'type'=>'text',
					'title'=>'This should be a unique field name,
					Only letters,
					digits and underscore allowed.',
					'label'=>'Unique Field Name',
					'label_for'=>'name',
					'size'=>'50',
					'text'=>'' ),
					'label' => array ( 'name'=>'label',
					'type'=>'text',
					'title'=>'Label of field,
					this will be shown to client',
					'label'=>'Field Label',
					'label_for'=>'label',
					'size'=>'50',
					'text'=>'' ),
					'default' => array ( 'name'=>'default',
					'type'=>'text',
					'title'=>'Default value for field',
					'label'=>'Field Default Value',
					'label_for'=>'default',
					'size'=>'50',
					'text'=>'' ),
					'hline1' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_fields_edit_text'] = array ( 'title' => 'Edit text field',
					'post' => 'index.php?act=update_fields_edit_pref&type=text&old_name='.(isset($field['name'])?$field['name']:''),
					'width' => '500px',
					'fields'=> array ( 'name' => array ( 'name'=>'name',
					'type'=>'text',
					'title'=>'This should be a unique field name,
					Only letters,
					digits and underscore allowed.',
					'label'=>'Unique Field Name',
					'label_for'=>'name',
					'size'=>'50',
					'text'=>(isset($field['name']))?$field['name']:'' ),
					'label' => array ( 'name'=>'label',
					'type'=>'text',
					'title'=>'Label of field,
					this will be shown to client',
					'label'=>'Field Label',
					'label_for'=>'label',
					'size'=>'50',
					'text'=>(isset($field['label']))?$field['label']:'' ),
					'default' => array ( 'name'=>'default',
					'type'=>'text',
					'title'=>'Default value for field',
					'label'=>'Field Default Value',
					'label_for'=>'default',
					'size'=>'50',
					'text'=>(isset($field['default']))?$field['default']:'' ),
					'hline1' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_fields_add_select'] = array ( 'title' => 'Add new select field',
					'post' => 'index.php?act=update_fields_add_pref&type=select',
					'width' => '500px',
					'fields'=> array ( 'name' => array ( 'name'=>'name',
					'type'=>'text',
					'label'=>'Unique Field Name',
					'title'=>'This should be a unique field name,
					Only letters,
					digits and underscore allowed.',
					'label_for'=>'name',
					'size'=>'50',
					'text'=>'' ),
					'label' => array ( 'name'=>'label',
					'type'=>'text',
					'title'=>'Label of field,
					this will be shown to client',
					'label'=>'Field Label',
					'label_for'=>'label',
					'size'=>'50',
					'text'=>'' ),
					'options' => array ( 'name'=>'options',
					'type'=>'text',
					'title'=>'A comma separated list of options,
					i.e. <em>Option 1,
					Option 2,...</em><br>If value is different than display then use colon,
					i.e. <em>opt1:Option 1,
					opt2:Option2,
					...</em>',
					'label'=>'Field Options',
					'label_for'=>'options',
					'size'=>'50',
					'text'=>'' ),
					'default' => array ( 'name'=>'default',
					'type'=>'text',
					'title'=>'This will be selected by default',
					'label'=>'Field Default Option',
					'label_for'=>'default',
					'size'=>'50',
					'text'=>'' ),
					'hline1' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_fields_edit_select'] = array ( 'title' => 'Edit select field',
					'post' => 'index.php?act=update_fields_edit_pref&type=select&old_name='.$field['name'],
					'width' => '500px',
					'fields'=> array ( 'name' => array ( 'name'=>'name',
					'type'=>'text',
					'title'=>'This should be a unique field name,
					Only letters,
					digits and underscore allowed.',
					'label'=>'Unique Field Name',
					'label_for'=>'name',
					'size'=>'50',
					'text'=>(isset($field['name']))?$field['name']:'' ),
					'label' => array ( 'name'=>'label',
					'type'=>'text',
					'title'=>'Label of field,
					this will be shown to client',
					'label'=>'Field Label',
					'label_for'=>'label',
					'size'=>'50',
					'text'=>(isset($field['label']))?$field['label']:'' ),
					'options' => array ( 'name'=>'options',
					'type'=>'text',
					'title'=>'A comma separated list of options,
					i.e. <em>Option 1,
					Option 2,...</em><br>If value is different than display then use colon,
					i.e. <em>opt1:Option 1,
					opt2:Option2,
					...</em>',
					'label'=>'Field Options',
					'label_for'=>'options',
					'size'=>'50',
					'text'=>(isset($field['options']))?$field['options']:'' ),
					'default' => array ( 'name'=>'default',
					'type'=>'text',
					'title'=>'This will be selected by default',
					'label'=>'Field Default Option',
					'label_for'=>'default',
					'size'=>'50',
					'text'=>(isset($field['default']))?$field['default']:'' ),
					'hline1' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_news_add'] = array ( 'title' => 'Add news feed',
					'post' => 'index.php?act=update_news_add_pref',
					'width' => '500px',
					'__GET_FORM_DATE__' => time(),
					'fields'=> array ( 'date' => array ( 'name' =>'date',
					'type' =>'hidden',
					'text' =>'',
					'id' =>'dialog_news_add_date_hidden_id' ),
					'date_button' => array ( 'name'=>'date_button',
					'type'=>'button',
					'title'=>'Date of news feed',
					'label'=>'Date',
					'label_for'=>'date_button',
					'text'=>'date',
					'id'=>'config_news_date_button_id' ),
					'hline_under_date' => array ( 'type'=>'hline',
				),
					'title' => array ( 'name'=>'title',
					'type'=>'text',
					'title'=>'Title of news feed',
					'label'=>'Feed Title',
					'label_for'=>'title',
					'size'=>'50',
					'text'=>'' ),
					'link' => array ( 'name'=>'link',
					'type'=>'text',
					'title'=>'External link of the news feed,
					this is optional',
					'label'=>'Feed External Link',
					'label_for'=>'link',
					'size'=>'50',
					'text'=>'http://' ),
					'text' => array ( 'name'=>'text',
					'type'=>'textarea',
					'title'=>'Text body of news feed',
					'label'=>'News Text',
					'label_for'=>'text',
					'cols'=>'50',
					'rows'=>'10',
					'size'=>'99%',
					'text'=>'' ),
					'active' => array ( 'name'=>'active',
					'type'=>'select',
					'title'=>'Active feeds will be shown in news section of all agents.',
					'label'=>'Active',
					'label_for'=>'active',
					'options' => array ( '1' => 'Yes',
					'0' => 'No' ),
					'selected'=>'1' ),
					'sticky' => array ( 'name'=>'sticky',
					'type'=>'select',
					'title'=>'Sticky feeds will be shown on top of all feeds in news page.',
					'label'=>'Sticky',
					'label_for'=>'sticky',
					'options' => array ( '1' => 'Yes',
					'0' => 'No' ),
					'selected'=>'0' ),
					'hline1' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_news_edit'] = array ( 'title' => 'Edit news feed',
					'post' => 'index.php?act=update_news_edit_pref&mode=multi&newsid='.(ArrayMember($_REQUEST,'newsid')),
					'width' => '500px',
					'__GET_FORM_DATE__' => ArrayMember($site_news_dialog,'n_date'),
					'fields'=> array ( 'date' => array ( 'name' =>'date',
					'type' =>'hidden',
					'text' =>'',
					'id' =>'dialog_news_edit_date_hidden_id' ),
					'date_button' => array ( 'name'=>'date_button',
					'type'=>'button',
					'title'=>'Date of news feed',
					'label'=>'Date',
					'label_for'=>'date_button',
					'text'=>'date',
					'id'=>'config_news_date_button_id' ),
					'hline_under_date' => array ( 'type'=>'hline',
				),
					'title' => array ( 'name'=>'title',
					'type'=>'text',
					'title'=>'Title of news feed',
					'label'=>'Title',
					'label_for'=>'title',
					'size'=>'50',
					'text'=>ArrayMember($site_news_dialog,'n_title') ),
					'link' => array ( 'name'=>'link',
					'type'=>'text',
					'title'=>'External link of the news feed,
					this is optional',
					'label'=>'Feed External Link',
					'label_for'=>'link',
					'size'=>'50',
					'text'=>ArrayMember($site_news_dialog,'n_link') ),
					'text' => array ( 'name'=>'text',
					'type'=>'textarea',
					'title'=>'Text body of news feed',
					'label'=>'News Body',
					'label_for'=>'text',
					'cols'=>'50',
					'rows'=>'8',
					'size'=>'99%',
					'text'=>ArrayMember($site_news_dialog,'n_text') ),
					'active' => array ( 'name'=>'active',
					'type'=>'select',
					'title'=>'Active feeds will be shown in news section of all agents.',
					'label'=>'Active',
					'label_for'=>'active',
					'options' => array ( '1' => 'Yes',
					'0' => 'No' ),
					'selected'=>ArrayMember($site_news_dialog,'n_active','0') ),
					'sticky' => array ( 'name'=>'sticky',
					'type'=>'select',
					'title'=>'Sticky feeds will be shown on top of all feeds in news page.',
					'label'=>'Sticky',
					'label_for'=>'sticky',
					'options' => array ( '1' => 'Yes',
					'0' => 'No' ),
					'selected'=>ArrayMember($site_news_dialog,'n_sticky','0') ),
					'hline1' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_departments_add'] = array ( 'title' => 'Add new department',
					'post' => 'index.php?act=update_departments_add_pref',
					'__GET_AGENTS_DEPT__' => 'usersall',
					'fields'=> array ( 'name' => array ( 'name'=>'name',
					'type'=>'text',
					'title'=>'This should be a unique department name,
					this will be shown to clients when they start the chat session.',
					'label'=>'Department Name',
					'label_for'=>'name',
					'size'=>'50',
					'text'=>'' ),
					'hline1' => array ( 'type'=>'hline',
				),
					'agents_list_span' => array ( 'name'=>'agents_list_span',
					'type'=>'parag',
					'label'=>'Agents included in this department:',
					'label2'=>'' ),
					'agents_list_dept_div' => array ( 'name'=>'agents_list_dept_div',
					'type'=>'div',
					'label'=>'',
					'label2'=>'' ),
					'agents_list_added' => array ( 'name'=>'agents_list_added',
					'type'=>'hidden',
					'text'=>'' ),
					'hline2' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_departments_edit'] = array ( 'title' => 'Edit department',
					'post' => 'index.php?act=update_departments_edit_pref&depid='.ArrayMember($_REQUEST,'depid'),
					'__GET_AGENTS_DEPT__' => 'depid',
					'fields'=> array ( 'name' => array ( 'name'=>'name',
					'type'=>'text',
					'title'=>'This should be a unique department name,
					this will be shown to clients when they start the chat session.',
					'label'=>'Department Name',
					'label_for'=>'name',
					'size'=>'50',
					'text'=>ArrayMember($department,'name','') ),
					'hline1' => array ( 'type'=>'hline',
				),
					'agents_list_span' => array ( 'name'=>'agents_list_span',
					'type'=>'parag',
					'label'=>'Agents included in this department:',
					'label2'=>'' ),
					'agents_list_dept_div' => array ( 'name'=>'agents_list_dept_div',
					'type'=>'div',
					'label'=>'',
					'label2'=>'' ),
					'agents_list_added' => array ( 'name'=>'agents_list_added',
					'type'=>'hidden',
					'text'=>'' ),
					'hline2' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_homesendmail'] = array ( 'title' => 'Send chat log to email',
					'post' => 'index.php?act=send_log_by_email&chatid='.(isset($_REQUEST['chatid'])?$_REQUEST['chatid']:''),
					'width' => '500px',
					'fields'=> array ( 'sname' => array ( 'name'=>'sname',
					'type'=>'text',
					'title'=>'Name of the sender (your name)',
					'label'=>'Sender Name',
					'label_for'=>'sname',
					'size'=>'50',
					'text'=>$_SERVER['uinfo']['nickname'] ),
					'semail' => array ( 'name'=>'semail',
					'type'=>'text',
					'title'=>'Email address of the sender (your email)',
					'label'=>'Sender Email',
					'label_for'=>'semail',
					'size'=>'50',
					'text'=>$_SERVER['uinfo']['email'] ),
					'hline1' => array ( 'type'=>'hline',
				),
					'rname' => array ( 'name'=>'rname',
					'type'=>'text',
					'title'=>'Name of the receiver',
					'label'=>'Receiver Name',
					'label_for'=>'rname',
					'size'=>'50',
					'text'=>'' ),
					'remail' => array ( 'name'=>'remail',
					'type'=>'text',
					'title'=>'Email address of the receiver',
					'label'=>'Receiver Email',
					'label_for'=>'remail',
					'size'=>'50',
					'text'=>'' ),
					'hline2' => array ( 'type'=>'hline',
				),
					'subject' => array ( 'name'=>'subject',
					'type'=>'text',
					'title'=>'Subject of email',
					'label'=>'Subject',
					'label_for'=>'subject',
					'size'=>'50',
					'text'=>'Chat transcript' ),
					'message' => array ( 'name'=>'message',
					'type'=>'textarea',
					'title'=>'Optional message to be send by email',
					'label'=>'Optional Message',
					'label_for'=>'message',
					'cols'=>'50',
					'rows'=>'8',
					'size'=>'100%',
					'text'=>(isset($_REQUEST['chat_count'])?('Transcript of '.$_REQUEST['chat_count']." message(s) attached to this email.\n\nRegards\n".$_SERVER['uinfo']['nickname']."\n\n".$this->GetCompanyLine()):'') ),
					'hline3' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_confirm_block_access'] = array ( 'title' => 'Block Access Confirmation',
					'post' => 'index.php?act=block_access&client_uniq='.$_REQUEST['client_uniq'].'&server_uniq='.$_REQUEST['server_uniq'],
					'width' => '400px',
					'fields'=> array ( 'dlg_title' => array ( 'name'=>'dlg_title',
					'type'=>'parag',
					'label'=>'Conversion will be closed and this client will not be able to open another session. Are you sure?' ),
					'hline1' => array ( 'type'=>'hline',
				),
					'period_hours' => array ( 'name'=>'period_hours',
					'type'=>'select',
					'title'=>'How long this client should be blocked',
					'label'=>'Block Period',
					'label_for'=>'period_hours',
					'options' => array ( '1' => 'One hour',
					'2' => '2 hours',
					'4' => '4 hours',
					'8' => '8 hours',
					'12' => '12 hours',
					'24' => '24 hours',
					'48' => '48 hours',
					'168' => '7 days',
					'336' => '14 days',
					'720' => '30 days',
					'1440' => '60 days',
					'2160' => '90 days',
					'4320' => '180 days',
					'8760' => '365 days' ),
					'selected'=>'48' ),
					'hline2' => array ( 'type'=>'hline',
				) ) );
				$d['dialog_edit_block_access'] = array ( 'title' => 'Extend Block Access',
					'post' => 'index.php?act=blocked_clients_modify&mode=set_period&bid='.ArrayMember($_REQUEST,'bid'),
					'width' => '400px',
					'fields'=> array ( 'period_hours' => array ( 'name'=>'period_hours',
					'type'=>'select',
					'title'=>'How long this client should be blocked',
					'label'=>'Block Period',
					'label_for'=>'period_hours',
					'options' => array ( '1' => 'One hour',
					'2' => '2 hours',
					'4' => '4 hours',
					'8' => '8 hours',
					'12' => '12 hours',
					'24' => '24 hours',
					'48' => '48 hours',
					'168' => '7 days',
					'336' => '14 days',
					'720' => '30 days',
					'1440' => '60 days',
					'2160' => '90 days',
					'4320' => '180 days',
					'8760' => '365 days' ),
					'selected'=>'48' ),
					'hline2' => array ( 'type'=>'hline',
				) ) );
				if(LIVEADMIN_STANDALONE)
				{
					$d['dialog_register'] = array ( 'title' => 'Register',
					'post' => 'index.php?act=register',
					'buttons' => array('register_submit','cancel'),
					'width' => '600px',
					'fields'=> array ( 'dlg_title' => array ( 'name'=>'dlg_title',
					'type'=>'parag',
					'label'=>'If you have obtained a valid registration key please enter it in Registration Code section below and submit. Otherwise you can get a registration code at this address:',
					'label2'=>'<br/><br/><a target="_blank" href="https://www.liveadmin.net/register.php">https://www.liveadmin.net/register.php</a><br/>' ),
					'hline0' => array ( 'type'=>'hline',
					),
					'site_key' => array ( 'name'=>'site_key',
					'type'=>'span',
					'title'=>'Unique Key for your site,
					this will be used in code that need to put on site pages.',
					'label'=>'Site Key',
					'label_for'=>'site_key',
					'text'=>$_SERVER['sinfo']['key'] ),
					'register_code' => array ( 'name'=>'register_code',
					'type'=>'text',
					'title'=>'Registration code is case insensitive and contains letters only,
					do not type any space or hyphen.',
					'label'=>'Registration Code',
					'label_for'=>'register_code',
					'size'=>'50',
					'text'=>Lic2Text($_SERVER['sinfo']['license']) ),
					'hline2' => array ( 'type'=>'hline',
					),
					) );
					$d['dialog_upgrade_ftp'] = array ( 'title' => 'LiveAdmin Upgrade',
					'post' => 'index.php?act=do_upgrade',
					'width' => '600px',
					'buttons' => array('upgrade_submit','cancel','upgrade_back','close'),
					'alternate_1' => '<div class="lv_dialog" style="display:none;" id="lv_dialog_dialog_upgrade_ftp_alt_1"><img src="images/wait_dialog.gif"/></div>',
					'alternate_2' => '<div class="lv_dialog" style="display:none;" id="lv_dialog_dialog_upgrade_ftp_alt_2"><div id="lv_dialog_dialog_upgrade_ftp_status_text"></div><div class="hline"></div></div>',
					'__CACHE__' => 'FTP_INFO',
					'fields'=> array ( 'dlg_title' => array ( 'name'=>'dlg_title',
					'type'=>'parag',
					'label'=>'Please key in the FTP login information to where LiveAdmin installed. For security reasons I will not save the password:' ),
					'hline0' => array ( 'type'=>'hline',
					),
					'ftp_host' => array ( 'name'=>'ftp_host',
					'type'=>'text',
					'title'=>'FTP address of the site,
					connection will be local,
					so in most cases ftp host is localhost',
					'label'=>'FTP Host',
					'label_for'=>'ftp_host',
					'size'=>'50',
					'text'=>'__CACHE__:ftp_host:localhost' ),
					'ftp_port' => array ( 'name'=>'ftp_port',
					'type'=>'text',
					'title'=>'In most cases this should be 21',
					'label'=>'FTP Port',
					'label_for'=>'ftp_port',
					'size'=>'50',
					'text'=>'__CACHE__:ftp_port:21' ),
					'ftp_user' => array ( 'name'=>'ftp_user',
					'type'=>'text',
					'title'=>'Username of FTP account',
					'label'=>'FTP Username',
					'label_for'=>'ftp_user',
					'size'=>'50',
					'text'=>'__CACHE__:ftp_user:' ),
					'ftp_pass' => array ( 'name'=>'ftp_pass',
					'type'=>'password',
					'title'=>'Password of FTP account',
					'label'=>'FTP Password',
					'label_for'=>'ftp_pass',
					'size'=>'50',
					'text'=>'' ),
					'ftp_folder' => array ( 'name'=>'ftp_folder',
					'type'=>'text',
					'title'=>'When I logged into FTP server,
					I will look for LiveAdmin installation in this folder',
					'label'=>'FTP Folder',
					'label_for'=>'ftp_folder',
					'size'=>'50',
					'text'=>'__CACHE__:ftp_folder:' ),
					'hline2' => array ( 'type'=>'hline',
					),
					'report_errors' => array ( 'name'=>'report_errors',
					'type'=>'select',
					'title'=>'If you set this to send errors,
					an anonymous report will be sent to LiveAdmin to help them fix possible problems',
					'label'=>'Report Errors',
					'label_for'=>'report_errors',
					'options' => array ( 'y' => 'Report error to LiveAdmin developers',
					'n' => 'Do not send any reports' ),
					'selected'=>'__CACHE__:report_errors:y' ),
					'hline3' => array ( 'type'=>'hline',
					) ) );
				}
				return($d);
	}
	function GetDialogsHtml($dialogs)
	{
		$RV = array();
		$ttp = array();
		foreach($dialogs as $d_id => $d_ar)
		{
			$uinfo=false;
			$CACHE_INFO = false;
			if(isset($d_ar['__GET_USER__']) && $d_ar['__GET_USER__']!='')
			{
				$uinfo = GetUserInfo($d_ar['__GET_USER__'],$_REQUEST['userid']);
			}
			if(ArrayMember($d_ar,'__CACHE__','')!='')
			{
				include_once('cache.php');
				$cache = new LV_Cache();
				$CACHE_INFO = $cache->GetCache(ArrayMember($d_ar,'__CACHE__',''),5*365*24*3600);
			}
			$d = '<div class="lv_dialog" id="lv_dialog_'.$d_id.'">';
			if(strpos($d_ar['post'],'?')!==false) $d_ar['post'] .= '&lv_sid='.$_SERVER['lv_sid'];
			else $d_ar['post'] .= '?lv_sid='.$_SERVER['lv_sid'];
			$d .= '<form method="POST" action="'.$d_ar['post'].'">';
			if(isset($d_ar['fix_height']) && $d_ar['fix_height'])
			{
				$d .= '<div class="lv_dialog_fix_height" id="lv_dialog_fix_height_id_'.$d_id.'">';
			}
			foreach($d_ar['fields'] as $f_id=>$f_ar)
			{
				if(ArrayMember($f_ar,'show_if','')!='')
				{
					list($show_if_1,$show_if_2) = explode("::",$f_ar['show_if'],2);
					if($show_if_1!=$show_if_2) continue;
				}
				if(!isset($f_ar['multiple'])) $f_ar['multiple'] = '';
				foreach($f_ar as $a=>$v)
				{
					if(is_string($v) && $uinfo!==false && substr($v,0,12)=='__GET_USER__')
					{
						$f_ar[$a] = $uinfo[substr($v,12)];
					}
					if(is_string($v) && substr($v,0,9)=='__CACHE__')
					{
						$cache_p = explode(":",$v,3);
						if($CACHE_INFO!==false && count($cache_p)>=3)
						{
							$f_ar[$a] = ArrayMember($CACHE_INFO,$cache_p[1],$cache_p[2]);
						}
						else
						{
							$f_ar[$a] = $cache_p[2];
						}
					}
				}
				$script = '';
				if(isset($f_ar['script'])) $script = $f_ar['script'];
				$title = '';
				if(isset($f_ar['title']))
				{
					$title = 'title="<'.'s5lang>'.$f_ar['title'].'<'.'/s5lang>"';
					$ttp[] = $f_ar['name'];
				}
				switch($f_ar['type'])
				{
					case 'select': $d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<label for="'.$f_ar['name'].'_id" '.$title.' id="'.$f_ar['name'].'_lbl_id" ><'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>:</label>';
					if(isset($f_ar['add_element_left']) && $f_ar['add_element_left']!='') $d .= $f_ar['add_element_left'];
					$d .= '<select '.$f_ar['multiple'].' name="'.$f_ar['name'].'" id="'.$f_ar['name'].'_id" '.$script.' >';
					foreach($f_ar['options'] as $a=>$v)
					{
						$selected = '';
						if($a==$f_ar['selected']) $selected = 'selected';
						$d .= '<option '.$selected.' value="'.$a.'">'.$v.'</option>';
					}
					$d .= '</select>';
					if(isset($f_ar['add_element_right']) && $f_ar['add_element_right']!='') $d .= $f_ar['add_element_right'];
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'radio': $d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<label for="'.$f_ar['name'].'_id" '.$title.' id="'.$f_ar['name'].'_lbl_id"><'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>:</label>';
					foreach($f_ar['options'] as $a=>$v)
					{
						$selected = '';
						if($a==$f_ar['selected']) $selected = 'checked';
						$d .= '<input type="radio" name="'.$f_ar['name'].'" id="'.$f_ar['name'].'_id" value="'.$a.'" '.$selected.' '.$script.' /> '.$v;
					}
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'text': $size = '';
					if(isset($f_ar['size']) && $f_ar['size']!='')
					{
						if(strpos($f_ar['size'],'%')!==false) $size = 'style="width:'.$f_ar['size'].'"';
						else $size = 'size="'.$f_ar['size'].'"';
					}
					$d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<label for="'.$f_ar['name'].'_id" '.$title.' id="'.$f_ar['name'].'_lbl_id"><'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>:</label>';
					$d .= '<input type="textbox" name="'.$f_ar['name'].'" id="'.$f_ar['name'].'_id" value="'.$f_ar['text'].'" '.$size.' '.$script.' /> ';
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'textarea': $size = '';
					if(!isset($f_ar['cols']) || $f_ar['cols']=='') $f_ar['cols'] = 50;
					if(!isset($f_ar['rows']) || $f_ar['rows']=='') $f_ar['rows'] = 5;
					$style = '';
					if(isset($f_ar['size']) && strpos($f_ar['size'],'%')!==false) $style = 'style="width:'.$f_ar['size'].'"';
					$d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<label for="'.$f_ar['name'].'_id" '.$title.' id="'.$f_ar['name'].'_lbl_id"><'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>:</label>';
					$d .= '<textarea name="'.$f_ar['name'].'" id="'.$f_ar['name'].'_id" cols="'.$f_ar['cols'].'" rows="'.$f_ar['rows'].'" '.$style.' '.$script.' >'.$f_ar['text'].'</textarea>';
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'password': $size = (isset($f_ar['size']) && $f_ar['size']!='')?'size="'.$f_ar['size'].'"':'';
					$d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<label for="'.$f_ar['name'].'_id" '.$title.' id="'.$f_ar['name'].'_lbl_id"><'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>:</label>';
					$d .= '<input type="password" name="'.$f_ar['name'].'" id="'.$f_ar['name'].'_id" value="'.$f_ar['text'].'" '.$size.' '.$script.' /> ';
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'hline': $d .= '<div class="hline"></div>';
					break;
					case 'button': $eltitle = '';
					if(isset($f_ar['eltitle']))
					{
						$eltitle = 'title="'.$f_ar['eltitle'].'"';
					}
					$d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<label for="'.$f_ar['name'].'_id" '.$title.' id="'.$f_ar['name'].'_lbl_id"><'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>:</label>';
					$d .= '<input type="button" name="'.$f_ar['name'].'" id="'.$f_ar['id'].'" value="'.$f_ar['text'].'" '.$eltitle.' '.$script.' /> ';
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'hidden': if(isset($f_ar['id']) && $f_ar['id']!='') $d .= '<input type="hidden" id="'.$f_ar['id'].'" name="'.$f_ar['name'].'" value="'.$f_ar['text'].'" /> ';
					else $d .= '<input type="hidden" id="'.$f_ar['name'].'_hidden_id" name="'.$f_ar['name'].'" value="'.$f_ar['text'].'" /> ';
					break;
					case 'p': $d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<label for="'.$f_ar['name'].'_id" '.$title.' id="'.$f_ar['name'].'_lbl_id"><'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>:</label>';
					$d .= '<p id="'.$f_ar['name'].'_id" '.$script.' >'.$f_ar['text'].'</p>';
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'span': $d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<label for="'.$f_ar['name'].'_id" '.$title.' id="'.$f_ar['name'].'_lbl_id"><'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>:</label>';
					$d .= '<span id="'.$f_ar['name'].'_id" '.$script.' >'.$f_ar['text'].'</span>';
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'parag': $d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '<'.'s5lang>'.$f_ar['label'].'<'.'/s5lang>';
					$d .= ArrayMember($f_ar,'label2','');
					$d .= '<div class="clear"></div>';
					$d .= '</div>';
					break;
					case 'div': $d .= '<div id="'.$f_ar['name'].'_div_id">';
					$d .= '</div>';
					break;
				}
			}
			if(isset($d_ar['fix_height']) && $d_ar['fix_height'])
			{
				$d .= '</div>';
			}
			$d .= '</form></div>';
			foreach($d_ar as $a=>$v)
			{
				if(substr($a,0,10)=='alternate_')
				{
					$d .= $v;
				}
			}
			$RV[$d_id]['header'] = '<'.'s5lang>'.$d_ar['title'].'<'.'/s5lang>';
			$RV[$d_id]['body'] = $d;
			$RV[$d_id]['width'] = $d_ar['width'];
			$RV[$d_id]['tooltip'] = $ttp;
			if(isset($d_ar['__GET_AGENTS_DEPT__']) && $d_ar['__GET_AGENTS_DEPT__']!='' && is_object($this->lv_admin))
			{
				$RV[$d_id]['agents_list_dept'] = $this->lv_admin->GetAgentsDept($d_id);
			}
			if(isset($d_ar['__GET_FORM_DATE__']) && is_numeric($d_ar['__GET_FORM_DATE__']))
			{
				$RV[$d_id]['current_date'] = array ( 'd' => lv_date('d',$d_ar['__GET_FORM_DATE__'],$_SERVER['uinfo']['time_zone']),
'm' => lv_date('m',$d_ar['__GET_FORM_DATE__'],$_SERVER['uinfo']['time_zone']),
'y' => lv_date('Y',$d_ar['__GET_FORM_DATE__'],$_SERVER['uinfo']['time_zone']),
'm3' => lv_date('M',$d_ar['__GET_FORM_DATE__'],$_SERVER['uinfo']['time_zone']),
				);
			}
			if(isset($d_ar['buttons'])) $RV[$d_id]['buttons'] = $d_ar['buttons'];
			for($i=0;$i<10;$i++)
			{
				if(isset($d_ar['cparam'.$i])) $RV[$d_id]['cparam'.$i] = $d_ar['cparam'.$i];
			}
		}
		return($RV);
	}
	function GetDialogStrings()
	{
		include_once('lang.php');
		$lang = new LV_Lang($_SERVER['sinfo']['language']);
		$lang_str_ar = $lang->GetAllStrings();
		$local = unserialize($_SERVER['sinfo']['chat_window_texts']);
		$d = '<div class="lv_dialog">';
		$d .= '<form method="POST" action="index.php?act=update_strings_pref&lv_sid='.$_SERVER['lv_sid'].'">';
		$d .= '<div class="lv_dialog_fix_height">';
		foreach($lang_str_ar as $a=>$v)
		{
			if(substr($a,0,1)!='A') continue;
			$id = 'texts_'.$a;
			if(isset($local[$a]))
			{
				$selected = '';
				$disabled = '';
				$text = $local[$a];
			}
			else
			{
				$selected = 'checked';
				$disabled = 'disabled';
				$text = $v;
			}
			$d .= '<input type="checkbox" name="'.$id.'_cb" id="'.$id.'_cb_id" value="ON" '.$selected.' onclick=\'dialog_strings_click("'.$id.'");\' />';
			$d .= '<input type="textbox" name="'.$id.'_text" id="'.$id.'_text_id" value="'.$text.'" style="width:90%" '.$disabled.' /> ';
			$d .= '<div class="clear"></div>';
		}
		$d .= '</div>';
		$d .= '<div class="hline"></div>';
		$d .= '</form>';
		$d .= '</div>';
		$RV = array();
		$RV['header'] = '<s5lang>Client Panel Strings</s5lang>';
		$RV['body'] = $d;
		$RV['width'] = '700px';
		return($RV);
	}
	function GetDialogUsers()
	{
		$ulist = GetUsersList($_SERVER['sinfo']['siteid']);
		$d = '<div class="lv_dialog">';
		$d .= '<form method="POST" action="index.php?act=update_userlist_pref&lv_sid='.$_SERVER['lv_sid'].'">';
		$d .= '<div class="lv_dialog_fix_height">';
		$d .= '</div>';
		$d .= '<div class="hline"></div>';
		$d .= '</form>';
		$d .= '</div>';
		$RV = array();
		$RV['header'] = '<s5lang>Users</s5lang>';
		$RV['body'] = $d;
		$RV['width'] = '700px';
		return($RV);
	}
	function GetDialogInstallation()
	{
		if(LIVEADMIN_STANDALONE)
		{
			$d = $this->lv_admin->GetContentsPart(LIVEADMIN_FC.'/chat_panel_p1.php','chat_panel_install_guide_stl');
		}
		$d = str_replace('XX-SITEKEY-XX',$_SERVER['sinfo']['key'],$d);
		$d = str_replace('XX-SITEURL-XX',LIVEADMIN_CLIENT_BASE_URL,$d);
		$RV = array();
		$RV['header'] = '<s5lang>Installation Guide</s5lang>';
		$RV['body'] = $d;
		$RV['width'] = '650px';
		$RV['buttons'] = array('close');
		return($RV);
	}
	function GetRings()
	{
		$dir = LIVEADMIN_R;
		$RV = array('--none--'=>'-- None --');
		if (is_dir($dir))
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if(substr($file,-4)=='.mp3')
					{
						$RV[$file] = ucwords(strtolower(str_replace('_',' ',substr($file,0,-4))));
					}
				}
				closedir($dh);
			}
		}
		asort($RV);
		return($RV);
	}
	function GetThemes()
	{
		$dir = LIVEADMIN_T;
		$RV = array('default'=>'Default');
		if (is_dir($dir))
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if(is_dir(LIVEADMIN_T.'/'.$file) && is_file(LIVEADMIN_T.'/'.$file.'/setting.php') && $file!='default' && substr($file,0,1)!='_')
					{
						$RV[$file] = ucwords(strtolower(str_replace('_',' ',$file)));
					}
				}
				closedir($dh);
			}
		}
		asort($RV);
		return($RV);
	}
	function GetStatusThemes()
	{
		$dir = LIVEADMIN_FOF;
		$res = array();
		if (is_dir($dir))
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if(preg_match('/^tm_(\d\d)_on\.png/',$file,$m))
					{
						$res['tm_'.$m[1]] = 'Theme '.$m[1];
					}
				}
				closedir($dh);
			}
		}
		asort($res);
		$RV = array('custom'=>'<s5lang>Custom Images</s5lang>');
		foreach($res as $a=>$v)
		{
			$RV[$a] = $v;
		}
		return($RV);
	}
	function GetCompanyLine()
	{
		$s = '';
		$s .= $_SERVER['sinfo']['company']."\n";
		$s .= $_SERVER['sinfo']['address']."\n";
		$s .= $_SERVER['sinfo']['city'].',
'.$_SERVER['sinfo']['state'].',
'.$_SERVER['sinfo']['postal_code']."\n";
		$s .= $_SERVER['sinfo']['general_email']."\n";
		return($s);
	}
	function GetTimeZones()
	{
		$TZ = array ( '-660' =>'-11:00 MIT Midway Islands Time',
'-600' =>'-10:00 HST Hawaii Standard Time',
'-570' =>'-09:00 AST Alaska Standard Time',
'-480' =>'-08:00 PST Pacific Standard Time',
'-420' =>'-07:00 MST Mountain Standard Time',
'-360' =>'-06:00 CST Central Standard Time',
'-300' =>'-05:00 EST Eastern Standard Time',
'-240' =>'-04:00 PRT Puerto Rico Time',
'-210' =>'-03:30 CNT Canada Newfoundland Time',
'-180' =>'-03:00 BET Brazil Eastern Time',
'-60' =>'-01:00 CAT Central African Time',
'0' =>' 00:00 GMT Greenwich Mean Time',
'60' =>'+01:00 ECT European Central Time',
'120' =>'+02:00 EET Eastern European Time',
'180' =>'+03:00 EAT Eastern African Time',
'210' =>'+03:30 MET Middle East Time',
'240' =>'+04:00 NET Near East Time',
'300' =>'+05:00 PLT Pakistan Lahore Time',
'330' =>'+05:30 IST India Standard Time',
'360' =>'+06:00 BST Bangladesh Standard Time',
'420' =>'+07:00 VST Vietnam Standard Time',
'480' =>'+08:00 CTT China Taiwan Time',
'540' =>'+09:00 JST Japan Standard Time',
'570' =>'+09:30 ACT Australia Central Time',
'600' =>'+10:00 AET Australia Eastern Time',
'660' =>'+11:00 SST Solomon Standard Time',
'720' =>'+12:00 NST New Zealand Standard Time' );
		return($TZ);
	}
}
?>