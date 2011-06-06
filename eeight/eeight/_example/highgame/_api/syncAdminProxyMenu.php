<?php
/**
 * 路径: /_api/activeUserChannel.php
 * 用途: 开户同步激活高频调用的 API 接口, 开通用户的频道[仅限于二级代理以下的级别]
 * 
 * @author  james  090804
 * @version 1.0.0
 * @package passport
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);     // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE); // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class api_syncAdminProxyMenu extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return $this->makeApiResponse( TRUE|FALSE, 'STRING DATA' )
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * 根据传递的用户ID, 激活用户在 db.passport.userchannel 的数据 
     */
    public function _runProcess()
    {
        // 1, 整理 API 提交数据
		$iGroupId = 0;
        if( !empty($this->aArgv['groupid']) )
        {
			if( !is_numeric($this->aArgv['groupid']))
			{
				return $this->makeApiResponse( FALSE, 'groupid is invalid.' );
			}
			$iGroupId    = intval( $this->aArgv['groupid'] );      // 组ID
        }
		if(!$this->aArgv['userid'])
		{
            return $this->makeApiResponse( FALSE, 'userid is invalid.' );
		}
		$iUserId    = intval( $this->aArgv['userid'] );      // 组ID
		if( !$this->aArgv['newgroupid'])
        {
            return $this->makeApiResponse( FALSE, 'newgroupid is invalid.' );
        }
		$newGroupId = intval($this->aArgv['newgroupid']);
        // 2, 数据写入
        $oAdminProxyMenu = new model_adminproxymenu();
		$aValue = array('groupid'=>$newGroupId);
		if($iGroupId)
		{
			$aMenu = $oAdminProxyMenu->get($iGroupId);
			$aValue['menustrs'] = $aMenu['menustrs'];
		}
		else
		{
			$aValue['menustrs'] = '';
		}
		$aValue['ownerid'] = $iUserId;
		$mResult = $oAdminProxyMenu->insert( $aValue );
        if( TRUE === $mResult )
        {
			return $this->makeApiResponse( TRUE, 'success' );
        }
        else
        {
			return $this->makeApiResponse( FALSE, 'error' );
        }
    }
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_syncAdminProxyMenu(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>