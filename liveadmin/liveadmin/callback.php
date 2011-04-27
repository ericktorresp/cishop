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

include_once('include/core.php');
$blank = array ( '1px'=>"47494638396101000100800000FFFFFFFFFFFF21F904000700FF002C00000000010001000002024401003B", '2px'=>"47494638396102000100800000FFFFFFFFFFFF21F904000700FF002C0000000002000100000202040A003B" );
header("Content-Type: image/gif");
if(GetVisitorCallback()==0)
{
	$ret = $blank['1px'];
}
else
{
	$ret = $blank['2px'];
}
for($i=0;$i<strlen($ret);$i+=2)
{
	print chr(hexdec($ret[$i].$ret[$i+1]));
}
?>