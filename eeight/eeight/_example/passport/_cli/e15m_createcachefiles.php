<?php
/**
 * 生成本地缓存文件
 * 
 * 路径: /_cli/e15m_createcachefiles.php
 * 
 * 由操作系统调用的 createcachefiles.php 支持的命令行可选参数: table，多个table用空格隔开
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
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php');
echo "[d] [".date('Y-m-d H:i:s')." ";
$oConfig = new model_config();
if( $oConfig->getConfigFile( realpath(dirname(__FILE__) . '/../').DIRECTORY_SEPARATOR."_app".
                             DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR ) )
{
	die("Done\n");
}
else 
{
	die("Write file fail\n");
}
?>