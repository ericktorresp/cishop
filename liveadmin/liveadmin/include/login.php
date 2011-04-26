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






 if(!defined('LIVEADMIN')) exit; $ErrorStr = ''; $InfoStr = ''; if($_SERVER['act']=='do_login') { $user = $_REQUEST['lv_username']; $pass = $_REQUEST['lv_password']; DoLogin($user,$pass); } if($_SERVER['act']=='logout') { DoLogout(); } if(!isset($_REQUEST['e'])) $_REQUEST['e'] = ''; if(LIVEADMIN_STANDALONE) { include('login_page.php'); } else { header('Location: '.LIVEADMIN_MASTER_URL_LOGIN.'?e='.$_REQUEST['e']); exit; } ?>