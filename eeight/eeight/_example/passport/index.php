<?php
/**
 * PassPort 平台单入口文件
 * 
 * SESSION 数组使用情况:
 * ~~~~~~~~~~~~~~~~~~~~~
 * $_SESSION['validateCode']    验证码
 * $_SESSION['userid']          用户ID
 * $_SESSION['username']        用户名(登陆帐号)
 * $_SESSION['usertype']        用户类型 {0:用户;1:代理;2:总代管理员}
 * $_SESSION['nickname']        昵称
 * $_SESSION['language']        语言
 * $_SESSION['userrank']        星级
 * $_SESSION['frozentype']      冻结类型 {1:不可登陆, 2:只可登陆, 3:可登陆,可充提}
 * $_SESSION['isfrozen']        是否冻结 {1:上级冻结, 2:公司冻结}
 * $_SESSION['skins']           使用模板名
 * $_SESSION['logintype']       登陆类型 {normal:普通登陆, security:资金密码登陆}
 * 
 */
@header("content-type:text/html; charset=UTF-8");
define('IN_APPLE', TRUE);
define('PAPPNAME', 'passport'); // 定义应用程序名称(不可重复). 核心类 filecaches.php 的基础目录名
define('PDIR', dirname(__FILE__)); // 项目路径: D:\wwwroot\aframe\_example\passport
require realpath( PDIR."/../../library/a.php" );
define('PDIR_ADMIN', realpath(PDIR.DS."..".DS."passportadmin") );// 和PASSPORTADMIN共有的model
define('PDIR_USER', dirname(__FILE__)); // 与 passportadmin 需保持一致, for model_userskins

// 数据库配置文件 DSN 路径, PDIR . \_app\config\
define('PRJ_DSN_PATH', PDIR.DS.'_app'.DS.'config'.DS );
require( PRJ_DSN_PATH. 'dsn.php' );

// 多语言配置文件 LANG 路径, PDIR . \_app\language\
define('PRJ_LANG_PATH', PDIR.DS.'_app'.DS.'language'.DS );
require_once( PRJ_LANG_PATH. 'utf8_zhcn' . DS . 'common.php' );
header("Cache-Control: no-cache, must-revalidate");

// 控制器默认路径
define( 'PRJ_CONTROLLER_PATH', PDIR.DS.'_app'.DS.'controller'.DS );

// 模型层默认路径
define( 'PRJ_MODEL_PATH', PDIR. DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_PAY_PATH', PDIR. DS . '_app' . DS . 'model' . DS . 'pay' . DS );
define( 'PRJ_ADMIN_MODEL_PATH', PDIR_ADMIN . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_DEPOSIT_PATH', PDIR. DS . '_app' . DS . 'model' . DS . 'deposit' . DS);

// 控制器
$GLOBALS['G_CLASS_FILES']['controller_default']		= PRJ_CONTROLLER_PATH . 'default.php';
$GLOBALS['G_CLASS_FILES']['controller_help']		= PRJ_CONTROLLER_PATH . 'help.php';
$GLOBALS['G_CLASS_FILES']['controller_report']		= PRJ_CONTROLLER_PATH . 'report.php';
$GLOBALS['G_CLASS_FILES']['controller_security']	= PRJ_CONTROLLER_PATH . 'security.php';
$GLOBALS['G_CLASS_FILES']['controller_user']		= PRJ_CONTROLLER_PATH . 'user.php';
$GLOBALS['G_CLASS_FILES']['controller_useradmin']	= PRJ_CONTROLLER_PATH . 'useradmin.php';

// 模型层
$GLOBALS['G_CLASS_FILES']['model_activity']         = PRJ_MODEL_PATH . 'activity.php';
$GLOBALS['G_CLASS_FILES']['model_activityanswer']   = PRJ_MODEL_PATH . 'activityanswer.php';
$GLOBALS['G_CLASS_FILES']['model_activityinfo']     = PRJ_MODEL_PATH . 'activityinfo.php';
$GLOBALS['G_CLASS_FILES']['model_activityuser']     = PRJ_MODEL_PATH . 'activityuser.php'; 
$GLOBALS['G_CLASS_FILES']['model_bankinfo']			= PRJ_MODEL_PATH . 'bankinfo.php';
$GLOBALS['G_CLASS_FILES']['channelapi']			    = PRJ_MODEL_PATH . 'channelapi.php';
$GLOBALS['G_CLASS_FILES']['cliapi']                 = PRJ_MODEL_PATH . 'cliapi.php';
$GLOBALS['G_CLASS_FILES']['model_channels']         = PRJ_MODEL_PATH . 'channels.php';
$GLOBALS['G_CLASS_FILES']['model_common']           = PRJ_MODEL_PATH . 'common.php';
$GLOBALS['G_CLASS_FILES']['model_config']           = PRJ_MODEL_PATH . 'config.php';
$GLOBALS['G_CLASS_FILES']['model_domains']          = PRJ_MODEL_PATH . 'domains.php';
$GLOBALS['G_CLASS_FILES']['model_helps']            = PRJ_MODEL_PATH . 'helps.php';
$GLOBALS['G_CLASS_FILES']['model_inactionuserclear']= PRJ_MODEL_PATH . 'inactionuserclear.php';
$GLOBALS['G_CLASS_FILES']['model_notices']			= PRJ_MODEL_PATH . 'notices.php';
$GLOBALS['G_CLASS_FILES']['model_orders']			= PRJ_MODEL_PATH . 'orders.php';
$GLOBALS['G_CLASS_FILES']['model_proxygroup']		= PRJ_MODEL_PATH . 'proxygroup.php';
$GLOBALS['G_CLASS_FILES']['model_reportcount']		= PRJ_MODEL_PATH . 'reportcount.php';
$GLOBALS['G_CLASS_FILES']['model_user']				= PRJ_MODEL_PATH . 'user.php';
$GLOBALS['G_CLASS_FILES']['model_useradminproxy']	= PRJ_MODEL_PATH . 'useradminproxy.php';
$GLOBALS['G_CLASS_FILES']['model_userdomain']		= PRJ_MODEL_PATH . 'userdomain.php';
$GLOBALS['G_CLASS_FILES']['model_userfund']			= PRJ_MODEL_PATH . 'userfund.php';
$GLOBALS['G_CLASS_FILES']['model_usergroup']        = PRJ_MODEL_PATH . 'usergroup.php';
$GLOBALS['G_CLASS_FILES']['model_userlog']			= PRJ_MODEL_PATH . 'userlog.php';
$GLOBALS['G_CLASS_FILES']['model_usermenu']			= PRJ_MODEL_PATH . 'usermenu.php';
$GLOBALS['G_CLASS_FILES']['model_usersession']		= PRJ_MODEL_PATH . 'usersession.php';
$GLOBALS['G_CLASS_FILES']['model_userskins']        = PRJ_MODEL_PATH . 'userskins.php';
$GLOBALS['G_CLASS_FILES']['model_userunite']		= PRJ_MODEL_PATH . 'userunite.php';
$GLOBALS['G_CLASS_FILES']['model_withdrawel']		= PRJ_MODEL_PATH . 'withdrawel.php';
$GLOBALS['G_CLASS_FILES']['model_accinfo']			= PRJ_MODEL_PATH . 'accinfo.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_fodetailslist']= PRJ_MODEL_PATH . DS . 'withdraw' . DS . 'fundoutdetailslist.php';

$GLOBALS['G_CLASS_FILES']['model_withdraw_banklist']= PRJ_MODEL_PATH . 'withdraw' . DS . 'banklist.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_list']	= PRJ_MODEL_PATH . 'pay' . DS . 'base' . DS . 'list.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_info']	= PRJ_MODEL_PATH . 'pay' . DS . 'base' . DS . 'info.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_common']	= PRJ_MODEL_PATH . 'pay' . DS . 'base' . DS . 'common.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_fundoutdetail']= PRJ_MODEL_PATH . 'withdraw' . DS . 'fundoutdetail.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_userbanklist']= PRJ_MODEL_PATH . 'withdraw' . DS . 'userbanklist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_userbank']= PRJ_MODEL_PATH . 'withdraw' . DS . 'userbank.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_apiwdbanklist']= PRJ_MODEL_PATH . 'withdraw' . DS . 'apiwdbanklist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_payaccountlimit']= PRJ_MODEL_PATH . 'withdraw' . DS . 'payaccountlimit.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_arealist'] = PRJ_MODEL_PATH . 'withdraw' . DS . 'arealist.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_emaildeposit'] = PRJ_MODEL_PATH . 'deposit' . DS . 'emaildeposit.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_ccbdeposit'] = PRJ_MODEL_PATH . 'deposit' . DS . 'ccbdeposit.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_depositlist'] = PRJ_MODEL_PATH . 'deposit' . DS . 'depositlist.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_depositallbankinfo'] = PRJ_MODEL_PATH . 'deposit' . DS . 'depositallbankinfo.php';
// 新增for在线充值
$GLOBALS['G_CLASS_FILES']['model_pay_payportinfo'] = PRJ_MODEL_PAY_PATH . 'payportinfo.php';
$GLOBALS['G_CLASS_FILES']['model_pay_payportlist'] = PRJ_MODEL_PAY_PATH . 'payportlist.php';
$GLOBALS['G_CLASS_FILES']['model_pay_loadlist'] = PRJ_MODEL_PAY_PATH . 'loadlist.php';
$GLOBALS['G_CLASS_FILES']['model_pay_loadinfo'] = PRJ_MODEL_PAY_PATH . 'loadinfo.php';
$GLOBALS['G_CLASS_FILES']['model_pay_loadlogs'] = PRJ_MODEL_PAY_PATH . 'loadlogs.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_info'] = PRJ_MODEL_PAY_PATH .'base'. DS .'info.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_list'] = PRJ_MODEL_PAY_PATH .'base'. DS .'list.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_common'] = PRJ_MODEL_PAY_PATH .'base'. DS .'common.php';


//for EMAIL充值
$GLOBALS['G_CLASS_FILES']['model_deposit_recordinfo'] = PRJ_MODEL_DEPOSIT_PATH . 'recordinfo.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_recordlist'] = PRJ_MODEL_DEPOSIT_PATH . 'recordlist.php';

// 调用 PASSPORTADMIN 拥有的模型
$GLOBALS['G_CLASS_FILES']['model_message']		= PRJ_ADMIN_MODEL_PATH . 'message.php';
$GLOBALS['G_CLASS_FILES']['model_banksnapshot'] = PRJ_ADMIN_MODEL_PATH . 'banksnapshot.php';
$GLOBALS['G_CLASS_FILES']['model_errordeal'] = PRJ_ADMIN_MODEL_PATH . 'errordeal.php';

//Live Customer模型
$GLOBALS['G_CLASS_FILES']['model_livecustomer']= PRJ_MODEL_PATH . DS . 'livecustomer.php';

// ... more ...

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
                             DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR ) )
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
	$oSession = new sessiondb( array( 'aDBO' => $GLOBALS['aSysDbServer']['master']) );
    
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
	$oView->assign("webtitle",getConfigValue("webtitle","银行大厅"));
	$oConfig = A::singleton("model_config");
	$oView->assign("sSystemImagesAndCssPath",$oConfig->getConfigs("imgserver",""));
	// 版本号 (更改需刷新 global config 文件)   5/12/2010
	$oView->assign("systemversions",getConfigValue("system_versions"));
	// 植入第三方统计代码 6/9/2010
	$oView->assign("systemstatusreport",getConfigValue("enabled_user_trail","0"));
	
	/**********************************验证码************************/
	if( isset($_GET['useValid']) && (bool)$_GET['useValid']== TRUE )
	{
		require PDIR .DS. '_app'.DS.'validate.php';
		EXIT;
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
 * 
 * @author 	james	09/05/17
 * @param 	string	$sMsg		//消息内容
 * @param 	int		$iMsgType	//消息类型：0-弹出框，1-普通消息，2-错误消息，3-询问消息,
 * @param 	string	$sTarget	//默认目标窗口	self:本窗口，parent:父窗口，top:顶窗口
 * @param 	array	$aArray		//可选的连接地址，键/值：url=>连接地址，title=>连接标题,target:目标窗口(没有_)
 */
function sysMsg( $sMsg, $iMsgType=0, $aLinks=array(), $sTarget='self' )
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
			if($aLinks[0]['url'] == 'close'){
				//<script language=JavaScript> self.opener.location.reload(); </script>
				$sStr = "<script>alert('".$sMsg."');</script>
						<script> 
							if (self.opener.opener){
								self.opener.opener.location.href = '".$aLinks[1]['url']."';
							}
							if(self.opener){
								self.opener.location.href = '".$aLinks[1]['url']."';
							}
						</script>
						<p align=center><span onClick='window.opener=null;window.close();' style='cursor:pointer'>请关闭窗口</span></p>";
			}else{
				$sStr = "<script>alert('".$sMsg."');".$aLinks[0]['target'].".location='".$aLinks[0]['url']."';</script>";
			}
		}
		echo $sStr;
		EXIT;
	}
	
    $seconds = 8;	//倒计时
    $sStr = '如果您不做出选择，将在 <span id="spanSeconds">'.$seconds.'</span> 秒后跳转到第一个链接地址。';
    $GLOBALS['oView']->assign('auto_redirection', $sStr );
    $GLOBALS['oView']->assign('msg_detail',   $sMsg );
    $GLOBALS['oView']->assign('msg_type',     $iMsgType);
    $GLOBALS['oView']->assign('links',        $aLinks);
    $GLOBALS['oView']->assign('target',       $sTarget);
    $GLOBALS['oView']->assign('default_url',  $aLinks[0]['url'] );
    $GLOBALS['oView']->display('sys_message.html');
    EXIT;
}

/**
 * 时间格式检查
 *
 * @access public
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
?>
