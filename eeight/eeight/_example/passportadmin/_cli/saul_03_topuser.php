<?php
/**
 * 路径: /_cli/topuser.php
 * 功能: 更新总代帐号使用状态(从高频运行迁移用户)
 * 
 * @author    saul
 * @version   1.0.0
 * @package   passportadmin
 */


// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class cli_topuser extends basecli
{
    /**
     *   更新总代帐号状态
     * @return bool
     */
    protected function _runCli()
    {
    	$oDB = new db( $GLOBALS["aSysDbServer"]["master"] );
    	$oDB->query("UPDATE `tempusermap` SET `status`='1' where `gpuserid`=`dpuserid`");
    	$oDB->query("UPDATE `tempusermap` SET `status`='0' where `gpuserid`!=`dpuserid`");
    	return TRUE;
    }
}
$oCli = new cli_topuser(TRUE);
EXIT;
?>