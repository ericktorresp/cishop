<?php
/**
 * 路径: /_api/activeUserFund.php
 * 用途: API 激活'低频游戏平台' 用户资金账户.
 * 
 * 
 * @author 	    tom  090804
 * @version	1.0.0
 * @package	low
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);     // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE); // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class api_activeUserFund extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return bool | string
     *     - 执行失败, 则返回 : '错误字符串'
     *     - 执行成功, 则返回全等于的 TRUE 类型
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * 根据POST传递的总代ID, 初始化用户在低频频道中的 userFund 等相关数据
     */
    public function _runProcess()
    {
        // 1, 整理 API 提交数据
        if( empty($this->aArgv['iTopProxyId']) || !is_numeric($this->aArgv['iTopProxyId']) )
        {
            $this->makeApiResponse( FALSE, 'init Post Data Failed' );
            return FALSE;
        }

        $iTopProxyId  = intval( $this->aArgv['iTopProxyId'] );  // 总代ID
        //$this->makeApiResponse( FALSE, '总代ID='.$iTopProxyId ); return FALSE;

        $oUserApi = new model_interface();
        if( TRUE == $oUserApi->api_activeUserFund( $iTopProxyId ) )
        {
            $this->makeApiResponse( TRUE, 'insert successed!' );
            return TRUE;
        }
        else 
        {
            $this->makeApiResponse( FALSE, 'insert failed!' );
            return FALSE;
        }
    }
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_activeUserFund(TRUE);
//$oApi->ignorePostCheck();   // 单机调试, 忽略POST检查 ---------------- 生产版需注释
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>