<?php
/**
 * 路径: /_api/checkUserFundLock.php
 * 用途: 检查用户资金是否被锁
 * 
 * 转账调度器 API 接收参数:
 *    $this->aArgv['iUserId']       =  用户ID
 * 
 * @author 	    tom  090813 16:34
 * @version	1.0.0
 * @package	*** All Channel ***
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件



class api_checkUserFundLock extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return $this->makeApiResponse( TRUE|FALSE, 'STRING DATA' )
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */
    public function _runProcess()
    {
        // -------------------- 更新转出频道, 转入频道 '转账账变' 的状态值 ------------------
        if( !empty($this->aArgv['iUserId']) && is_numeric( $this->aArgv['iUserId']) )
        {
            // 初始模型层, 检查资金是否被锁
            $oUser = new model_userfund();
            $aArray= $oUser->getFundByUser( $this->aArgv['iUserId'],' 1 ', SYS_CHANNELID, TRUE );
			if( empty($aArray ) )
			{
				return $this->makeApiResponse( FALSE, 'Error: Locked' );
			}
			else 
			{
			    return $this->makeApiResponse( TRUE, 'successed! oked' );
			}
        }
        else
        {
            return $this->makeApiResponse( FALSE, 'data error' );
        }
    }

}


// 2, 为调用程序返回 '结果集'
$oApi = new api_checkUserFundLock(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>