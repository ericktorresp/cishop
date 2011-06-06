<?php
/**
 * TODO _a高频、低频并行前期临时程序 
 * 
 * 路径: /_api/interfaceuser.php
 * 用途: 高频注册用户向银行大厅同时新增用户API接口
 * 
 * 转账调度器 API 接收参数:
 *    $this->aArgv['iUserId']       =  int    用户在高频的自增ID
 *    $this->aArgv['iParentId']     =  int    用户在高频原有的父ID[总代多用户时只传原始的]
 *    $this->aArgv['sUserName']     =  string 用户名
 *    $this->aArgv['sNickName']     =  string 呢称
 *    $this->aArgv['sPassword']     =  string 登陆密码MD5加密
 *    $this->aArgv['sFundPassword'] =  string 资金密码[选传]
 *    $this->aArgv['iUserType']     =  array  用户在高频的身份[4:代理，5:用户]
 * 
 * @author  james  090919 15:15
 * @version 1.0.0
 * @package passport
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);     // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE); // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class api_interfaceuser extends baseapi
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
            || !isset($this->aArgv['iParentId']) || !is_numeric($this->aArgv['iParentId'])
            || empty($this->aArgv['sUserName']) || empty($this->aArgv['sPassword']) 
            //|| empty($this->aArgv['sFundPassword']) 
            || empty($this->aArgv['iUserType']) || !in_array($this->aArgv['iUserType'], array(4,5)) )
        {
            $this->makeApiResponse( FALSE, 'init Post Data Failed' );
            return FALSE;
        }
        $this->aArgv['sFundPassword'] = empty($this->aArgv['sFundPassword']) ? "" : $this->aArgv['sFundPassword'];
        $this->aArgv['sNickName'] = empty($this->aArgv['sNickName']) ? "" : urldecode($this->aArgv['sNickName']);
        $oUserApi = new model_user();
        $mResult  = $oUserApi->gdInsertUser( $this->aArgv['iUserId'], $this->aArgv['iParentId'],
                                             $this->aArgv['sUserName'], $this->aArgv['sPassword'],
                                             $this->aArgv['sFundPassword'], $this->aArgv['iUserType'],
                                             $this->aArgv['sNickName'] );
        if( TRUE === $mResult )
        {
            $this->makeApiResponse( TRUE, 'insert OK!' );
            return TRUE;
        }
        else 
        {
        	$sMsg = "";
        	switch( $mResult )
        	{
        		case -1 : $sMsg = "wrong param"; break;
        		case -2 : $sMsg = "user isExists"; break;
        		case -3 : $sMsg = "get parent info error"; break;
        		case -4 : $sMsg = "insert users error"; break;
        		case -5 : $sMsg = "insert usertree error"; break;
        		case -6 : $sMsg = "insert usersession error"; break;
        		case -7 : $sMsg = "insert userchannel error"; break;
        		case -8 : $sMsg = "insert userfund error"; break;
        		case -9 : $sMsg = "insert topproxyset error"; break;
        		case -10 : $sMsg = "insert tempusermap error"; break;
        		case -11 : $sMsg = "active user dp error"; break;
        		default : $sMsg = "other error"; break;
        	}
            $this->makeApiResponse( FALSE, $sMsg );
            return FALSE;
        }
    }
}

// 2, 为调用程序返回 '结果集'
$oApi = new api_interfaceuser(TRUE);
//$oApi->ignorePostCheck();   // 单机调试, 忽略POST检查 ---------------- 生产版需注释
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>