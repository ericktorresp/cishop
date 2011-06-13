<?php
/**
 * 高频投注平台, 单入口文件
 */
@header("content-type:text/html; charset=UTF-8");
define('IN_APPLE', TRUE);
define('PAPPNAME', 'highgame'); // 定义应用程序名称(不可重复). 核心类 filecaches.php 的基础目录名
define('PDIR', dirname(__FILE__)); // 项目路径: D:\wwwroot\aframe\_example\lowgame\
require realpath( PDIR."/../../library/a.php" );

define('PDIR_PASSPORT_ADMIN', realpath(PDIR.DS."..".DS."passportadmin") );
define('PDIR_PASSPORT_USER', realpath(PDIR.DS."..".DS."passport") );
define('PDIR_LOW_USER', realpath(PDIR.DS."..".DS."lowgame") );
define('PDIR_LOW_PROXY', realpath(PDIR.DS."..".DS."lowproxy") );
define('PDIR_LOW_ADMIN', realpath(PDIR.DS."..".DS."lowadmin") );

define('PDIR_HIGH_GAME', realpath(PDIR.DS."..".DS."highgame") );
define('PDIR_HIGH_ADMIN', realpath(PDIR.DS."..".DS."highadmin") );

// 数据库配置文件 DSN 路径, PDIR . \_app\config\
define('PRJ_DSN_PATH', PDIR_HIGH_GAME.DS.'_app'.DS.'config'.DS );
require ( PRJ_DSN_PATH. 'dsn.php' ); 

// 多语言配置文件 LANG 路径, PDIR . \_app\language\
define('PRJ_LANG_PATH', PDIR.DS.'_app'.DS.'language'.DS ); 
require_once( PRJ_LANG_PATH. 'utf8_zhcn' . DS . 'common.php' );



// 控制器默认路径
define( 'PRJ_SELF_CONTROLLER_PATH', PDIR.DS.'_app'.DS.'controller'.DS );

//模型层默认路径
define( 'PRJ_MODEL_PASSPORTADMIN_PATH', PDIR_PASSPORT_ADMIN . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_PASSPORT_PATH', PDIR_PASSPORT_USER . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_LOWUSER_PATH', PDIR_LOW_USER . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_LOWPROXY_PATH', PDIR_LOW_PROXY . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_LOWADMIN_PATH', PDIR_LOW_ADMIN . DS . '_app' . DS . 'model' . DS );

define( 'PRJ_MODEL_HIGHGAME_PATH', PDIR_HIGH_GAME . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_HIGHADMIN_PATH', PDIR_HIGH_ADMIN . DS . '_app' . DS . 'model' . DS );


//  Model,Controller 类名=>路径 对应关系数组
$GLOBALS['G_CLASS_FILES']['controller_channels']    = PRJ_SELF_CONTROLLER_PATH . 'channels.php' ;
$GLOBALS['G_CLASS_FILES']['controller_default']		= PRJ_SELF_CONTROLLER_PATH . 'default.php' ;
$GLOBALS['G_CLASS_FILES']['controller_game']        = PRJ_SELF_CONTROLLER_PATH . 'game.php' ;
$GLOBALS['G_CLASS_FILES']['controller_gameinfo']    = PRJ_SELF_CONTROLLER_PATH . 'gameinfo.php' ;
$GLOBALS['G_CLASS_FILES']['controller_help']		= PRJ_SELF_CONTROLLER_PATH . 'help.php' ;
$GLOBALS['G_CLASS_FILES']['controller_report']		= PRJ_SELF_CONTROLLER_PATH . 'report.php' ;
$GLOBALS['G_CLASS_FILES']['controller_security']	= PRJ_SELF_CONTROLLER_PATH . 'security.php' ;
$GLOBALS['G_CLASS_FILES']['controller_user']        = PRJ_SELF_CONTROLLER_PATH . 'user.php' ;
$GLOBALS['G_CLASS_FILES']['controller_useradmin']   = PRJ_SELF_CONTROLLER_PATH . 'useradmin.php' ;

//模型层(来源 PassPort 前台)
$GLOBALS['G_CLASS_FILES']['channelapi']			    = PRJ_MODEL_PASSPORT_PATH . 'channelapi.php' ;
$GLOBALS['G_CLASS_FILES']['cliapi']                 = PRJ_MODEL_PASSPORT_PATH . 'cliapi.php' ;
$GLOBALS['G_CLASS_FILES']['model_transferdispatcher']= PRJ_MODEL_PASSPORT_PATH . 'transferdispatcher.php' ;
$GLOBALS['G_CLASS_FILES']['model_channels']         = PRJ_MODEL_PASSPORT_PATH . 'channels.php' ;
$GLOBALS['G_CLASS_FILES']['model_common']           = PRJ_MODEL_PASSPORT_PATH . 'common.php' ;

//模型层(来源highgame)
$GLOBALS['G_CLASS_FILES']['model_cancelbonus']      = PRJ_MODEL_HIGHGAME_PATH . 'cancelbonus.php' ;
$GLOBALS['G_CLASS_FILES']['model_checkbonus']       = PRJ_MODEL_HIGHGAME_PATH . 'checkbonus.php' ;
$GLOBALS['G_CLASS_FILES']['model_congealtoreal']    = PRJ_MODEL_HIGHGAME_PATH . 'congealtoreal.php' ;
$GLOBALS['G_CLASS_FILES']['model_gamebase']         = PRJ_MODEL_HIGHGAME_PATH . 'gamebase.php' ;
$GLOBALS['G_CLASS_FILES']['model_gamecancel']       = PRJ_MODEL_HIGHGAME_PATH . 'gamecancel.php' ;
$GLOBALS['G_CLASS_FILES']['model_gamemanage']       = PRJ_MODEL_HIGHGAME_PATH . 'gamemanage.php' ;
$GLOBALS['G_CLASS_FILES']['model_gameplay']         = PRJ_MODEL_HIGHGAME_PATH . 'gameplay.php' ;
$GLOBALS['G_CLASS_FILES']['model_interface']        = PRJ_MODEL_HIGHGAME_PATH . 'interface.php' ;
$GLOBALS['G_CLASS_FILES']['model_issuehistory']     = PRJ_MODEL_HIGHGAME_PATH . 'issuehistory.php' ;
$GLOBALS['G_CLASS_FILES']['model_notices']          = PRJ_MODEL_HIGHGAME_PATH . 'notices.php' ;
$GLOBALS['G_CLASS_FILES']['model_orders']           = PRJ_MODEL_HIGHGAME_PATH . 'orders.php' ;
$GLOBALS['G_CLASS_FILES']['model_projects']         = PRJ_MODEL_HIGHGAME_PATH . 'projects.php' ;
$GLOBALS['G_CLASS_FILES']['model_proxygroup']       = PRJ_MODEL_HIGHGAME_PATH . 'proxygroup.php' ;
$GLOBALS['G_CLASS_FILES']['model_sendbonus']        = PRJ_MODEL_HIGHGAME_PATH . 'sendbonus.php' ;
$GLOBALS['G_CLASS_FILES']['model_sendpoints']       = PRJ_MODEL_HIGHGAME_PATH . 'sendpoints.php' ;
$GLOBALS['G_CLASS_FILES']['model_task']             = PRJ_MODEL_HIGHGAME_PATH . 'task.php' ;
$GLOBALS['G_CLASS_FILES']['model_tasktoproject']    = PRJ_MODEL_HIGHGAME_PATH . 'tasktoproject.php' ;
$GLOBALS['G_CLASS_FILES']['model_user']             = PRJ_MODEL_HIGHGAME_PATH . 'user.php' ;
$GLOBALS['G_CLASS_FILES']['model_useradminproxy']   = PRJ_MODEL_HIGHGAME_PATH . 'useradminproxy.php' ;
$GLOBALS['G_CLASS_FILES']['model_userchannel']      = PRJ_MODEL_HIGHGAME_PATH . 'userchannel.php' ;
$GLOBALS['G_CLASS_FILES']['model_userfund']         = PRJ_MODEL_HIGHGAME_PATH . 'userfund.php' ;
$GLOBALS['G_CLASS_FILES']['model_usermethodset']    = PRJ_MODEL_HIGHGAME_PATH . 'usermethodset.php' ;
$GLOBALS['G_CLASS_FILES']['model_usersession']      = PRJ_MODEL_HIGHGAME_PATH . 'usersession.php' ;
$GLOBALS['G_CLASS_FILES']['model_checkbonus_digital'] = PRJ_MODEL_HIGHGAME_PATH . 'checkbonus' . DS .'digital.php';
$GLOBALS['G_CLASS_FILES']['model_checkbonus_lotto'] = PRJ_MODEL_HIGHGAME_PATH . 'checkbonus' . DS .'lotto.php';
$GLOBALS['G_CLASS_FILES']['model_checkbonus_keno'] = PRJ_MODEL_HIGHGAME_PATH . 'checkbonus' . DS .'keno.php';
//模型层(来源highadmin )
$GLOBALS['G_CLASS_FILES']['model_config']           = PRJ_MODEL_HIGHADMIN_PATH . "config.php";
$GLOBALS['G_CLASS_FILES']['model_usermenu']         = PRJ_MODEL_HIGHADMIN_PATH . 'usermenu.php' ;
$GLOBALS['G_CLASS_FILES']['model_snapshot']         = PRJ_MODEL_HIGHADMIN_PATH .'snapshot.php';
$GLOBALS['G_CLASS_FILES']['model_lottery']          = PRJ_MODEL_HIGHADMIN_PATH .'lottery.php';
$GLOBALS['G_CLASS_FILES']['model_issueinfo']        = PRJ_MODEL_HIGHADMIN_PATH .'issueinfo.php';
$GLOBALS['G_CLASS_FILES']['model_method']           = PRJ_MODEL_HIGHADMIN_PATH .'method.php';
$GLOBALS['G_CLASS_FILES']['model_locks']            = PRJ_MODEL_HIGHADMIN_PATH .'locks.php';
$GLOBALS['G_CLASS_FILES']['model_statisticslock']   = PRJ_MODEL_HIGHADMIN_PATH.'statisticslock.php';
$GLOBALS['G_CLASS_FILES']['model_sale']             = PRJ_MODEL_HIGHADMIN_PATH .'sale.php';
$GLOBALS['G_CLASS_FILES']['model_issueerror']       = PRJ_MODEL_HIGHADMIN_PATH .'issueerror.php';
$GLOBALS['G_CLASS_FILES']['model_userprizegroup']   = PRJ_MODEL_HIGHADMIN_PATH .'userprizegroup.php';
$GLOBALS['G_CLASS_FILES']['model_userprizelevel']   = PRJ_MODEL_HIGHADMIN_PATH .'userprizelevel.php';
$GLOBALS['G_CLASS_FILES']['model_helps']            = PRJ_MODEL_HIGHADMIN_PATH . 'helps.php' ;

// 类自动搜索路径
A::import( PDIR .DS. '_app' );
A::setDispatcher('authdispatcher');

//载入网站配置文件
if( !defined("DONT_TRY_LOAD_SYSCONFIG_FILE") )
{
    if( !file_exists(PRJ_DSN_PATH. 'global_config.php') )
    {
        $oConfig = A::singleton("model_config");
        if( TRUE !== $oConfig->getConfigFile( realpath(dirname(__FILE__) . '/').DIRECTORY_SEPARATOR."_app".
                             DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR, SYS_CHANNELID ) )
        {
            die("global_config.php NOT FOUND!");
        }
    }
    include_once( PRJ_DSN_PATH. 'global_config.php' );
}

/*****************************************************************
 * 01, 设置属于项目的全局核心参数
 ****************************************************************/
A::replaceIni(
    array( 
        /* 全局设置 */
        'class.bDevelopMode' => FALSE, 
        'class.db.bRecordProcessTime' => TRUE, // 是否记录执行 SQL 的总计时间
        /* 调度器 & 控制器 */
        'apple.default.controller' => 'controller',
		'apple.default.action' => 'action',
        /* 日志类 */
		'class.logs.sBasePath' => PDIR.DS.'_tmp'.DS.'logs'.DS,  // 默认日志路径 A_DIR.DS.'tmp'.DS.'logs'.DS
        'class.logs.iMaxLogFileSize' => 5242880, // 日志最大尺寸. 1024*1024*5
        /* 错误处理 */
		'error' => array(
			'trigger_error' =>  117  /* 日志全开 =117 */
//                APPLE_ON_ERROR_CONTINUE
//                | APPLE_ON_ERROR_REPORT
//                | APPLE_ON_ERROR_TRACE
//                | APPLE_ON_ERROR_LOG
//                | APPLE_LOGS_SQL_TO_FILE, 
        ),
    )
);



/*****************************************************************
 * 02, 根据需要初始化 LOGS 对象
 ****************************************************************/
if( (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG) )
{
    $GLOBALS['oLogs'] = A::singleton('logs');//new logs( /*array('iLogType'=>0)*/ );
}

if( !defined( 'DONT_USE_APPLE_FRAME_MVC' ) )
{
	/**TODO：并行期间解决$_REQUEST和$_COOKIE冲突的问题*/
    $_REQUEST = array_merge( $_GET, $_POST );
    /*****************************************************************
	 * 03, 初始化session
	 ****************************************************************/
	$oSession = new sessiondb( array( 'aDBO' => $GLOBALS['aSysDbServer']['session']) );
	//print_rr($_SESSION);exit;
	/*****************************************************************
	 * 04, 初始化视图对象 $oView
	 ****************************************************************/
	$sSkinName = !empty($_SESSION['skins']) ? $_SESSION['skins'] : 'new';	// 10/18/2010
	$oView = new view(
	    array(
	        'template_dir'         => PDIR.DS.'_app'.DS.'views'.DS. $sSkinName .DS, // 结尾不需斜线
	    	'template_dir_default' => PDIR.DS.'_app'.DS.'views'.DS. 'default' .DS, // 默认目录
	        'compile_dir' => PDIR.DS.'_tmp'.DS.'views_compiled'.DS. $sSkinName,
	        'cache_dir' => PDIR.DS.'_tmp'.DS.'views_cached',
	        'caching' => FALSE,
	        'cache_lifetime' => 30,
	        'direct_output' => FALSE, // 直接输出
	        'force_compile' => FALSE, // 强制编译
	    )
	);
	
	//初始化一些全局配置
	$oView->assign("webtitle",getConfigValue("webtitle","高频游戏平台"));
	$oConfig = A::singleton("model_config");
    $oView->assign("sSystemImagesAndCssPath",$oConfig->getConfigs('imgserver'));
    // 植入第三方统计代码 6/9/2010
	$oView->assign("systemstatusreport",getConfigValue("enabled_user_trail","0"));
	/**********************************验证码************************/
	if( isset($_GET['useValid']) && (bool)$_GET['useValid']== TRUE )
	{
		require PDIR .DS. '_app'.DS.'validate.php';
		exit;
	}
	/*****************************************************************
	 * 05, runMVC !
	 ****************************************************************/
	A::runMVC();
}




/*****************************************************************
* 				06 passport全局函数
****************************************************************/

/**
 * 系统提示信息
 * @author 	james	09/05/17
 * @param 	string	$sMsg		//消息内容
 * @param 	int		$iMsgType	//消息类型：0-弹出框，1-普通消息，2-错误消息，3-询问消息,
 * @param 	string	$sTarget	//默认目标窗口	self:本窗口，parent:父窗口，top:顶窗口
 * @param 	array	$aArray		//可选的连接地址，键/值：url=>连接地址，title=>连接标题,target:目标窗口(没有_)
 */
function sysMsg( $sMsg, $iMsgType=0, $aLinks=array(), $sTarget='self', $bIsInside=TRUE )
{
	switch( $sTarget )
	{
		case 'top': 	$sTarget='top'; break;
		case 'parent': 	$sTarget='parent'; break;
		default:		$sTarget='self'; break; 
	}
	if( empty($aLinks) )
    {
        $aLinks[0]['title'] = '返回上一页';
        $aLinks[0]['url'] = 'javascript:history.back()';
        $aLinks[0]['target'] = $sTarget;
    }
    else 
    {
    	foreach( $aLinks as &$v )
    	{
    		if( empty($v['url']) )
    		{
    			$v['url'] = "javascript:history.back()";
    		}
    		if( empty($v['title']) )
    		{
    			$v['title'] = "返回上一页";
    		}
    		if( empty($v['target']) )
    		{
    			$v['target'] = $sTarget;
    		}
    	}
    }
	if( $iMsgType == 0 )
	{//JS弹出框
		if( empty($sMsg) )
		{//直接跳转，不弹出信息
			$sStr = "<script>".$aLinks[0]['target'].".location='".$aLinks[0]['url']."';</script>";
		}
		else
		{
			$sStr = "<script>alert('".$sMsg."');".$aLinks[0]['target'].".location='".$aLinks[0]['url']."';</script>";
		}
		echo $sStr;
		exit;
	}
	
    $seconds = 8;	//倒计时
    $sStr = '如果您不做出选择，将在 <span id="spanSeconds">'.$seconds.'</span> 秒后跳转到第一个链接地址。';
    $GLOBALS['oView']->assign('auto_redirection', $sStr );
    $GLOBALS['oView']->assign('msg_detail',   $sMsg );
    $GLOBALS['oView']->assign('msg_type',     $iMsgType);
    $GLOBALS['oView']->assign('links',        $aLinks);
    $GLOBALS['oView']->assign('target',       $sTarget);
    $GLOBALS['oView']->assign('default_url',  $aLinks[0]['url'] );
    if( $bIsInside )
    {
        $GLOBALS['oView']->display('message.html');
    }
    else
    {
        $GLOBALS['oView']->display('sys_message.html');
    }
    exit;
}

/**
 * 时间格式检查
 * @author james	09/06/10
 * @param string $sDateTime
 * @return boolean	//正确返回TRUE，错误返回FALSE
 */
function checkDateTime( $sDateTime )
{
	$sDateTime = trim( $sDateTime );
	if( empty($sDateTime) )
	{
		return TRUE;
	}
	$aTimes = explode( " ",$sDateTime );
	if( empty($aTimes[1]) )
	{
		$aTimes[1] = "00:00:00";
	}
	$aMicTime = explode( ":",$aTimes[1] );
	$aMicTime[0] = empty($aMicTime[0]) ? 0 : intval($aMicTime[0]);
	$aMicTime[1] = empty($aMicTime[1]) ? 0 : intval($aMicTime[1]);
	$aMicTime[2] = empty($aMicTime[2]) ? 0 : intval($aMicTime[2]);
	if( strpos($aTimes[0],"-")!==FALSE )
	{//格式为2009-06-10 00:00:00
		$aDate = explode( "-", $aTimes[0] );
	}
	elseif( strpos($aTimes[0],"/")!==FALSE )
	{// 2009/06/10 00:00:00
		$aDate = explode( "/", $aTimes[0] );
	}
	else 
	{//20090610 00:00:00
		$aDate[0] = substr( $aTimes[0],0,4 );
		$aDate[1] = substr( $aTimes[0],5,2 );
		$aDate[2] = substr( $aTimes[0],6,2 );
	}
	$aDate[0] = isset( $aDate[0] ) ? $aDate[0] : 0;
    $aDate[1] = isset( $aDate[1] ) ? $aDate[1] : 0;
    $aDate[2] = isset( $aDate[2] ) ? $aDate[2] : 0;
	if( count($aDate) != 3 )
	{
		return FALSE;
	}
	$aNewTime = getdate( strtotime($sDateTime) );
	if( $aNewTime['year']==intval($aDate[0]) && $aNewTime['mon']==intval($aDate[1])  
	    && $aNewTime['mday']==intval($aDate[2]) && $aNewTime['hours']==intval($aMicTime[0]) 
	    && $aNewTime['minutes']==intval($aMicTime[1]) && $aNewTime['seconds']==intval($aMicTime[2]) 
	  )
	{
	  	return TRUE;
	}
	else 
	{
		return FALSE;
	}
}


/**
 * 获取AJAX反馈消息
 *
 * @param string $sStats    //状态，
 * @param string $mMsg      //返回内容
 * @return serialize
 */
function ajaxMsg( $sStats, $mData )
{
	$aResult = array( "stats"=>$sStats, "data"=>$mData );
	$aResult = json_encode($aResult);
	return $aResult;
}
?>