<?php
/**
 * 路径: /_api/checkInActionUser.php
 * 用途: 不活跃用户清理，过滤掉本频道活跃的用户
 * 
 * 转账调度器 API 接收参数:
 *    $this->aArgv['lastupdatetime']       =  string 最后活跃时间
 *    $this->aArgv['mincash']              =  float  最小金额
 *    $this->aArgv['maxcash']              =  float  最大金额
 *    $this->aArgv['action']               =  string 清理动作(delete|freeze)
 *    $this->aArgv['users']                =  array  在银行大厅过滤了以后的用户ID组
 * 
 * @author      james  090910 15:15
 * @version 1.1.0
 * @package *** All Channel ***
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);     // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE); // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class api_checkinactionuser extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return bool | string
     *     - 执行失败, 则返回 : '错误字符串'
     *     - 执行成功, 则返回全等于的 TRUE 类型
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */
    public function _runProcess()
    {
        // 1, 整理 API 提交数据
        if( empty($this->aArgv) || !is_array($this->aArgv) )
        {
            $this->makeApiResponse( FALSE, 'init Post Data Failed' );
            return FALSE;
        }
        $oUserApi = new model_inactionuserclear();
        if( FALSE === ($mResult = $oUserApi->filterUsers($this->aArgv) ) )
        {
        	$this->makeApiResponse( FALSE, 'insert failed!' );
            return FALSE;
        }
        else 
        {
            $this->makeApiResponse( TRUE, $mResult );
            return TRUE;
        }
    }
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_checkinactionuser(TRUE);
//$oApi->ignorePostCheck();   // 单机调试, 忽略POST检查 ---------------- 生产版需注释
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>