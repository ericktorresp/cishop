<?php
/**
 * TODO _a高频、低频并行前期临时程序 
 * 
 * 路径: /_api/interfaceuserlogout.php
 * 用途: 同步登陆登出
 * 
 * 转账调度器 API 接收参数:
 *    $this->aArgv['iAction']       =  int    执行动作0：登陆，1：登出
 *    $this->aArgv['iUserId']       =  int    用户在高频的自增ID
 *    $this->aArgv['sUserName']     =  string 用户名
 *    $this->aArgv['sPassword']     =  string 登陆密码MD5加密
 * 
 * @author  james  090921 10:15
 * @version 1.0.0
 * @package passport
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);     // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE); // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class api_interfaceuserlogout extends baseapi
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
        if(    empty($this->aArgv) || !is_array($this->aArgv) 
            || empty($this->aArgv['iUserId'])   || !is_numeric($this->aArgv['iUserId'])
            || !isset($this->aArgv['iAction'])  || !in_array($this->aArgv['iAction'], array(0,1)) )
        {
            $this->makeApiResponse( FALSE, 'init Post Data Failed' );
            return FALSE;
        }
        $oUser = new model_user();
        if( $this->aArgv['iAction'] == 0 )
        {//登陆
        	if( empty($this->aArgv['sUserName']) || empty($this->aArgv['sPassword']) )
        	{
        		$this->makeApiResponse( FALSE, 'init Post Data Failed' );
                return FALSE;
        	}
        	//01: 登陆
        	$mData = $oUser->gdUserLogin($this->aArgv['iUserId'], $this->aArgv['sUserName'], $this->aArgv['sPassword']);
        	if( $mData !== TRUE )
        	{//登陆失败
	        	$sMsg = "";
	            switch( $mData )
	            {
	                case 0 : $sMsg = "wrong param"; break;
	                case -1 : $sMsg = "username or password wrong"; break;
	                case -2 : $sMsg = "user has been deleted"; break;
	                case -3 : $sMsg = "user has been frozened"; break;
	                case -4 : $sMsg = "update user login info error"; break;
	                case -5 : $sMsg = "update usersession info error"; break;
	                case -6 : $sMsg = "domain is wrong"; break;
	                default : $sMsg = "unknow error"; break;
	            }
	            $this->makeApiResponse( FALSE, $sMsg );
                return FALSE;
        	}
        	else 
        	{//登陆成功
        		$this->makeApiResponse( TRUE, 'login ok!' );
        		return TRUE;
        	}
        	
        }
        elseif( $this->aArgv['iAction'] == 1 )
        {//登出
        	$bResult = $oUser->gdUserLogout( $this->aArgv['iUserId'] );
        	if( $bResult === TRUE )
        	{//成功
        		$this->makeApiResponse( TRUE, 'logout ok!' );
                return TRUE;
        	}
        	else 
        	{
        		$this->makeApiResponse( FALSE, 'logout failed!' );
                return FALSE;
        	}
        }
        else 
        {//参数错误
        	$this->makeApiResponse( FALSE, 'init Post Data Failed' );
            return FALSE;
        }
    }
}

// 2, 为调用程序返回 '结果集'
$oApi = new api_interfaceuserlogout(TRUE);
//$oApi->ignorePostCheck();   // 单机调试, 忽略POST检查 ---------------- 生产版需注释
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>