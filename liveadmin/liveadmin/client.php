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






 include_once('include/core.php'); include_once('include/client.php'); if(!isset($_REQUEST['key'])) $_REQUEST['key'] = ''; $in_key = $_REQUEST['key']; $in_siteid = Key2ID($in_key); $client = new LV_Client($in_siteid); $client->Run(); ?>