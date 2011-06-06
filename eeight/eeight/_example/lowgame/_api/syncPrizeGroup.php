<?php
/**
 * 路径: /_api/syncPrizeGroup.php
 * 用途: 开户同步上级奖金信息程序
 * 
 * @author  louis
 * @version v1.0		2010-08-23
 * @package lowgame
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);     // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE); // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class api_syncPrizeGroup extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return $this->makeApiResponse( TRUE|FALSE, 'STRING DATA' )
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */
    public function _runProcess()
    {
        // 1, 整理 API 提交数据
        if( !is_numeric($this->aArgv['iUserId']) || $this->aArgv['iUserId'] <= 0 || !is_numeric($this->aArgv['iPid'])
        	|| $this->aArgv['iPid'] <= 0 )
        {
			return $this->makeApiResponse( FALSE, 'data error' );
        }
        // 首先检查新开用户是否是操作都的直接下级(passport数据库)
        $oChannelApi = new channelapi( 0, 'checkUser', TRUE );
        $oChannelApi->setTimeOut(10);            // 设置读取超时时间
        $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
        $oChannelApi->sendRequest( array("iUserId"=>$this->aArgv['iUserId'],"iPid"=>$this->aArgv['iPid']) );    // 发送结果集
        $aResult = $oChannelApi->getDatas();
        if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
        {//调用API激活失败
            return $this->makeApiResponse( FALSE, 'Parent is Wrong' );
        }
        
        $oUser   = new model_user();
        $mResult = $oUser->syncPrizeGroup( $this->aArgv['iUserId'], $this->aArgv['iPid'] );
        if ($mResult === true){
        	return $this->makeApiResponse( TRUE, 'success' );
        } else {
        	return $this->makeApiResponse( FALSE, $mResult );
        }
    }
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_syncPrizeGroup(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;