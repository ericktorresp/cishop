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

if(!is_callable('version_compare') || version_compare(phpversion(),'5.1.0','<'))
{
	echo "PHP ".phpversion()." detected, PHP 5.1.0 or later required.";
}
include_once('include/core.php');
include_once('include/admin.php');
$admin = new LV_Admin();
$admin->Run();
?>