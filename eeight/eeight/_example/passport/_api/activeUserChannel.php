<?php
/**
 * 路径: /_api/activeUserChannel.php
 * 用途: 供 (其他频道, 例:低频 ) 调用的 API 接口, 开通用户的频道
 * 
 * @author 	    tom  090804
 * @version	1.0.0
 * @package	passport
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);     // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE); // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class api_activeUserChannel extends baseapi
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
        if( empty($this->aArgv['userid']) || empty($this->aArgv['channelid']) || 
                !is_numeric($this->aArgv['userid']) || !is_numeric($this->aArgv['channelid']) )
        {
            return $this->makeApiResponse( FALSE, 'init Post Data Failed' );
        }

        $iUserId    = intval( $this->aArgv['userid'] );      // 用户ID
        $iChannelId = intval( $this->aArgv['channelid'] );   // 频道ID

        // 2, 数据写入
        $oUserChannel = new model_userchannel();
        if( TRUE == $oUserChannel->apiInsert( $iUserId, $iChannelId ) )
        {
            return $this->makeApiResponse( TRUE, 'insert successed!' );
        }
        else
        {
            return $this->makeApiResponse( FALSE, 'insert failed!' );
        }
    }
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_activeUserChannel(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>