<?php
//#!D:\worksspace\php\php.exe -q
/**
 * 用户充提统计缓存数据表[图表]
 * 
 * 路径: /_cli/eh59_reportcount.php
 * 
 * 由操作系统调用的 reportcount.php 支持的命令行可选参数: date[日期:2009-06-08 13:14:00]
 * 
 * @author james 2009-06-10
 * @package cli
 * @version 1.0.0
 */

@ini_set( "display_errors", TRUE);
error_reporting(E_ALL);
set_time_limit(10000);
// 1, 安全过滤 ----------------------------------------------------------
if( !empty($_GET) || !empty($_POST) || !empty($_REQUEST) )
{
    die('Error');  // 禁止网页 URL 形式调用
}

// 2, 初始化
define('DONT_USE_APPLE_FRAME_MVC', TRUE);
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php');
//$sDate = isset($argv[1]) ? $argv[1] : date("Y-m-d H:i:s");	//初始化参数
echo "[d] [".date('Y-m-d H:i:s')." ";
echo getTimeDiff( getMicrotime() - $GLOBALS['G_APPLE_LOADED_TIME'] )." -> ";
$oReport = new model_reportcount();
$bResult = $oReport->getTopProxyInOutCount();
echo getTimeDiff( getMicrotime() - $GLOBALS['G_APPLE_LOADED_TIME'] ) ." | ";
die(" Finished\n");
?>