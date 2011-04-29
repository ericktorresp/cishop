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










if(!defined('LIVEADMIN'))
	exit;


// Ansolute path to home folder where LiveAdmin installed
// LiveAdmin will find the path automatically, but in case
// of problems you can uncomment this line and define it.


//define('LIVEADMIN_B','/Library/WebServer/Documents/liveadmin');


// Site URL
// Internet address of LiveAdmin without tailing slash
// example: http://www.my_site_domain.com/liveadmin


define('LIVEADMIN_W','http://local.py/liveadmin');



// Database information
// Database Host - usually localhost

define('LIVEADMIN_DB_HOST','127.0.0.1');

// Database name

define('LIVEADMIN_DB_DATABASE','liveadmin');

// Username - this username should have access to
// database name

define('LIVEADMIN_DB_USER','root');

// Password - this is the password of above username

define('LIVEADMIN_DB_PASS','2908262');

// Table prefix - if you have no idea what it is simply
// set it to lvad_

define('LIVEADMIN_DB_PREFIX','lvad_');



// Do not change anything bellow this line

define('LIVEADMIN_UNIQ','wSJB4pWVMFNjJOFaWkOnWZZFSFVMSWY0MNlNVVTMlNOVFkOa6VUrdFMjlEE5mWUE');


define('LIVEADMIN_MASTER',false);
define('LIVEADMIN_SLAVE',false);
include_once(dirname(__FILE__).'/config_const.php');
?>