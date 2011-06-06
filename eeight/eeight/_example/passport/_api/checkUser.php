<?php
/**
 * 路径: /_api/checkUser.php
 * 用途: 检查用户的上下级关系
 * 
 * @author  louis
 * @version v1.0		2010-08-30
 * @package passport
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);     // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE); // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class api_checkUser extends baseapi
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
        // 首先检查新开用户是否是操作都的直接下级
        $oUser   = new model_user();
        $iPid    = $oUser->getParentId( $this->aArgv['iUserId'] );//获取新开用户的直接上级id
        if ($iPid != $this->aArgv['iPid'] ){ // 操作者不是新开用户的直接上级
        	return $this->makeApiResponse( FALSE, 'parent id is wrong' );
        } else {
        	return $this->makeApiResponse( TRUE, 'success' );
        }
    }
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_checkUser(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;