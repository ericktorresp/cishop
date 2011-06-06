<?php
/**
 *  接收livechat远程数据查询 
 *  
 *  支持  
 * 		1,对提交的用户名以及ID数据进行有效性效验
 * 		2,可查询用户的详细资料
 * 
 * (后台通讯数据未加密)
 * 
 * @name 	callback_livechat.php
 * @package livecustomer
 * @version 0.1 10/26/2010			
 * @author Jim
 * 
 */

// 依据实际运行可以进行设置的定义常量
define ( 'DEBUGME', 0 ); 			// 故障调试true,将通讯日志记录至文本文件
define ( 'DEBUGDIR', realpath ( dirname ( __FILE__ ) . '/../../' ) . '/_tmp/data_livechat/' ); // 文件形式DEBUG日志记录位置
// 常量
define ( 'DONT_USE_APPLE_FRAME_MVC', true ); 		// 跳过 MVC 流程
define ( 'DONT_TRY_LOAD_SYSCONFIG_FILE', true ); 	// 跳过配置文件检测
require (realpath ( dirname ( __FILE__ ) . '/../../' ) . '/index.php'); //引入项目入口文件


// 初步检查
//	必须要有 action  username userid  md5 sess ipaddr 五项请求参数 
$action 	= $_POST['action'];
$username 	= $_POST['username'];
$userid 	= $_POST['userid'];
$md5 		= $_POST['md5'];
$sess		= $_POST['sess'];
$ipaddr		= $_POST['ipaddr'];

// $validcode = ipaddr + userid
if ( empty($action) || empty($username) || empty($userid) || empty($md5) || empty($sess) )
{
	$sMsg = 'action:'.$action.' username:'.$username.' userid:'.$userid.' md5:'.$md5.' sess:'.$sess;
	callback_output('no', $sMsg );
}

$oLiveChat = new model_livecustomer();

// 解密数据、效验数据
//
$sTmpMd5 = $oLiveChat->getMD5( array($action, $username, $userid, $sess, $ipaddr ) );
if ( $sTmpMd5 != $md5 )
{
	$sMsg = 'tmpmd5:'.$sTmpMd5.' md5:'.$md5.'action:'.$action.' username:'.$username.' userid:'.$userid.' sess:'.$sess;
	callback_output('no', $sMsg );
	
}

//根据请求 返回数据
if ( $action == 'CHECK' )
{
	
	$bCheck = $oLiveChat->checkUserinfo( array('username'=>$username, 'userid'=>$userid, 'sess'=>$sess, 'ipaddr'=>$ipaddr ) );
		
	if ( $bCheck === TRUE )
	{
		$sMsg = 'status:yes action:'.$action.' username:'.$username.' userid='.$userid;
		callback_output('yes',  $sMsg );
	}
	else 
	{
		$sMsg = ($bCheck == TRUE ) ? 'true' : 'false';
		callback_output('no', $sMsg );
	}
	
}
else if ( $action == 'GET' )
{
	$aUser = $oLiveChat->getUserinfo( $userid );
	if ( !empty($aUser) &&  count($aUser) > 0 )
	{
		$aUser['status'] = 'yes';
		$sMsg = 'status:yes action:'.$action.' username:'.$username.' userid='.$userid;
		callback_output($aUser, $sMsg );
	}
	else 
	{
		$sMsg = 'no Find user by id:'.$userid;
		callback_output('no', $sMsg );
	}
	
}
else
{
	$sMsg = 'no allow request type, by id:'.$userid;
	callback_output('no', $sMsg );
	
}
/**
 * 格式化回显
 *
 * @param string/array $sStatus	返回状态 /数组时，查询功能  status, 其他请求的值
 * @param string $msg		可记录描述
 * @param bool	 $bDebug	是否DEBUG
 * @param string $sDedugDir 记录文本日志的目录
 */

function callback_output($sStatus, $msg, $bDebug=DEBUGME, $sDebugDir=DEBUGDIR)
{
	$sLogFormat = "\n\n[ ".date('Y-m-d H:i:s')."] \n ".$msg;
	if ( !file_exists($sDebugDir) )
	{
		@mkdir ($sDebugDir,0777);
		@chmod ($sDebugDir,0777);
	}
	$sDebugFile = $sDebugDir.'track_'.date('YmdH');
	if ($bDebug)
	{
		
		$iChk = @file_put_contents($sDebugFile, $sLogFormat, FILE_APPEND);
		
	}
	
	if ( is_array($sStatus) )
	{
		echo http_build_query($sStatus);
	}
	else 
	{
		echo 'status='.$sStatus;
	}
	exit;
}


