<?php
/**
 * PassPortAdmin 平台单入口文件
 * 
 * SESSION 数组使用情况:
 * ~~~~~~~~~~~~~~~~~~~~~
 * $_SESSION["admin"]        管理员用户ID
 * $_SESSION["adminname"]    管理员用户名
 * 
 */
@header("content-type:text/html; charset=UTF-8");
define('IN_APPLE', TRUE);
define('PAPPNAME', 'passportadmin'); // 定义应用程序名称(不可重复). 核心类 filecaches.php 的基础目录名
define('PDIR', dirname(__FILE__)); // 项目路径: D:\wwwroot\aframe\_example\passportadmin\
require realpath( PDIR."/../../library/a.php" );
define('PDIR_ADMIN', dirname(__FILE__) ); 
define('PDIR_USER', realpath(PDIR.DS."..".DS."passport") );// 和PASSPORT共有的model
// 定义版本号
// 格式:               发行版本号,   SVN 版本号,  发布日期
define('PRJ_VERSION',  '1.2.0,         1693,         2009-09-15 17:17');

// 数据库配置文件 DSN 路径[和前台一个文件], PDIR_USER . \_app\config\
define('PRJ_DSN_PATH', PDIR_USER.DS.'_app'.DS.'config'.DS );
require ( PRJ_DSN_PATH. 'dsn.php' ); 


// 多语言配置文件 LANG 路径, PDIR . \_app\language\
define('PRJ_LANG_PATH', PDIR.DS.'_app'.DS.'language'.DS ); 
require_once( PRJ_LANG_PATH. 'utf8_zhcn' . DS . 'common.php' );
header("Cache-Control: no-cache, must-revalidate");

// 控制器
// ...

// 模型
$GLOBALS['G_CLASS_FILES']['model_admingroup']    = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'admingroup.php';
$GLOBALS['G_CLASS_FILES']['model_adminlog']      = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'adminlog.php';
$GLOBALS['G_CLASS_FILES']['model_adminmenu']     = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'adminmenu.php';
$GLOBALS['G_CLASS_FILES']['model_adminnote']     = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'adminnote.php';
$GLOBALS['G_CLASS_FILES']['model_adminproxy']    = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'adminproxy.php';
$GLOBALS['G_CLASS_FILES']['model_adminuser']     = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'adminuser.php';
$GLOBALS['G_CLASS_FILES']['model_agent']         = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'agent.php';
$GLOBALS['G_CLASS_FILES']['model_banksnapshot']  = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'banksnapshot.php';
$GLOBALS['G_CLASS_FILES']['model_charts']        = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'charts.php';
$GLOBALS['G_CLASS_FILES']['model_firewall']      = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'firewall.php';
$GLOBALS['G_CLASS_FILES']['model_message']       = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'message.php';
$GLOBALS['G_CLASS_FILES']['model_passport']      = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'passport.php';
$GLOBALS['G_CLASS_FILES']['model_iplimit']       = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'iplimit.php';
$GLOBALS['G_CLASS_FILES']['model_secondverify']  = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'secondverify.php';
$GLOBALS['G_CLASS_FILES']['model_errordeal']  	 = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'errordeal.php';
$GLOBALS['G_CLASS_FILES']['model_emaildeposit']  = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'emaildeposit.php';
$GLOBALS['G_CLASS_FILES']['model_vmmanage'] 	 = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'vmmanage.php';
$GLOBALS['G_CLASS_FILES']['model_ccbdeposit']  = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'ccbdeposit.php';
$GLOBALS['G_CLASS_FILES']['model_gala']  = PDIR_ADMIN.DS.'_app'.DS.'model'.DS.'gala.php';

// PASSPORT 前台模型共用
$GLOBALS['G_CLASS_FILES']['model_bankinfo']      = PDIR_USER.DS.'_app'.DS.'model'.DS.'bankinfo.php';
$GLOBALS['G_CLASS_FILES']['channelapi']          = PDIR_USER.DS.'_app'.DS.'model'.DS.'channelapi.php';
$GLOBALS['G_CLASS_FILES']['model_channels']      = PDIR_USER.DS.'_app'.DS.'model'.DS.'channels.php';
$GLOBALS['G_CLASS_FILES']['model_domains']       = PDIR_USER.DS.'_app'.DS.'model'.DS.'domains.php';
$GLOBALS['G_CLASS_FILES']['model_notices']       = PDIR_USER.DS.'_app'.DS.'model'.DS.'notices.php';
$GLOBALS['G_CLASS_FILES']['model_user']          = PDIR_USER.DS.'_app'.DS.'model'.DS.'user.php';
$GLOBALS['G_CLASS_FILES']['model_accinfo']       = PDIR_USER.DS.'_app'.DS.'model'.DS.'accinfo.php';
$GLOBALS['G_CLASS_FILES']['model_useradminproxy']= PDIR_USER.DS.'_app'.DS.'model'.DS.'useradminproxy.php';
$GLOBALS['G_CLASS_FILES']['model_userchannel']   = PDIR_USER.DS.'_app'.DS.'model'.DS.'userchannel.php';
$GLOBALS['G_CLASS_FILES']['model_userdomain']    = PDIR_USER.DS.'_app'.DS.'model'.DS.'userdomain.php'; 
$GLOBALS['G_CLASS_FILES']['model_userfund']      = PDIR_USER.DS.'_app'.DS.'model'.DS.'userfund.php';
$GLOBALS['G_CLASS_FILES']['model_usergroup']     = PDIR_USER.DS.'_app'.DS.'model'.DS.'usergroup.php';
$GLOBALS['G_CLASS_FILES']['model_userlog']       = PDIR_USER.DS.'_app'.DS.'model'.DS.'userlog.php';
$GLOBALS['G_CLASS_FILES']['model_usermenu']      = PDIR_USER.DS.'_app'.DS.'model'.DS.'usermenu.php';
$GLOBALS['G_CLASS_FILES']['model_userunite']     = PDIR_USER.DS.'_app'.DS.'model'.DS.'userunite.php';
$GLOBALS['G_CLASS_FILES']['model_withdrawel']    = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdrawel.php';
$GLOBALS['G_CLASS_FILES']['model_orders']        = PDIR_USER.DS.'_app'.DS.'model'.DS.'orders.php';
$GLOBALS['G_CLASS_FILES']['model_config']        = PDIR_USER.DS.'_app'.DS.'model'.DS.'config.php';
$GLOBALS['G_CLASS_FILES']['model_usersession']   = PDIR_USER.DS.'_app'.DS.'model'.DS.'usersession.php';
$GLOBALS['G_CLASS_FILES']['model_activity']      = PDIR_USER.DS.'_app'.DS.'model'.DS.'activity.php';
$GLOBALS['G_CLASS_FILES']['model_activityinfo']  = PDIR_USER.DS.'_app'.DS.'model'.DS.'activityinfo.php';
$GLOBALS['G_CLASS_FILES']['model_activityuser']  = PDIR_USER.DS.'_app'.DS.'model'.DS.'activityuser.php';
$GLOBALS['G_CLASS_FILES']['model_activityanswer']= PDIR_USER.DS.'_app'.DS.'model'.DS.'activityanswer.php';
$GLOBALS['G_CLASS_FILES']['model_helps']         = PDIR_USER.DS.'_app'.DS.'model'.DS.'helps.php';
$GLOBALS['G_CLASS_FILES']['model_userskins']     = PDIR_USER.DS.'_app'.DS.'model'.DS.'userskins.php';
$GLOBALS['G_CLASS_FILES']['model_proxygroup']     = PDIR_USER.DS.'_app'.DS.'model'.DS.'proxygroup.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_info']	= PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'.DS.'base'.DS.'info.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_list']	= PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'.DS.'base'.DS.'list.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_common']	= PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'.DS.'base'.DS.'common.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_fodetailslist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'fundoutdetailslist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_fundoutdetail'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'fundoutdetail.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_banklist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'banklist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_bank'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'bank.php';
// for 后台删除前台用户自行绑定的银行卡,(无条件删除)
$GLOBALS['G_CLASS_FILES']['model_withdraw_userbank'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'userbank.php';

$GLOBALS['G_CLASS_FILES']['model_withdraw_withdrawoperate'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'withdrawoperate.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_download'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'download.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_dealapply'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'dealapply.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_uploadfile'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'uploadfile.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_withdrawreport'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'withdrawreport.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_withdrawreportlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'withdrawreportlist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_withdrawformat'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'withdrawformat.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_withdrawformatlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'withdrawformatlist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_apiwdbanklist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'apiwdbanklist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_apiwithdrawbank'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'apiwithdrawbank.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_paybanklist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'paybanklist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_paybank'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'paybank.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_wdunverifyreasonlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'wdunverifyreasonlist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_wdunverifyreason'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'wdunverifyreason.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_userbanklist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'userbanklist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_userbank'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'userbank.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_reportdlinfo'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'reportdlinfo.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_reportdlinfolist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'reportdlinfolist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_arealist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'arealist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_area'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'area.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_packlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'packlist.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_pack'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'pack.php';
$GLOBALS['G_CLASS_FILES']['model_withdraw_addreport'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'addreport.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_emaildeposit'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'deposit'.DS.'emaildeposit.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_ccbdeposit'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'deposit'.DS.'ccbdeposit.php';
// ... more ...
// 新增for在线充值
$GLOBALS['G_CLASS_FILES']['model_pay_payportinfo'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'payportinfo.php';
$GLOBALS['G_CLASS_FILES']['model_pay_payportlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'payportlist.php';
$GLOBALS['G_CLASS_FILES']['model_pay_payaccountinfo'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'payaccountinfo.php';
$GLOBALS['G_CLASS_FILES']['model_pay_payaccountlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'payaccountlist.php';
$GLOBALS['G_CLASS_FILES']['model_pay_payaccountlimit'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'payaccountlimit.php';
$GLOBALS['G_CLASS_FILES']['model_pay_loadinfo'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'loadinfo.php';
$GLOBALS['G_CLASS_FILES']['model_pay_loadlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'loadlist.php';
$GLOBALS['G_CLASS_FILES']['model_pay_loadlogs'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'loadlogs.php';
$GLOBALS['G_CLASS_FILES']['model_pay_apidataecapay'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS . 'apidataecapay.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_info'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS .'base'. DS .'info.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_list'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS .'base'. DS .'list.php';
$GLOBALS['G_CLASS_FILES']['model_pay_base_common'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS .'base'. DS .'common.php';
$GLOBALS['G_CLASS_FILES']['model_pay_repeatload'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'pay'. DS .'repeatload.php';

//for EMAIL充值
$GLOBALS['G_CLASS_FILES']['model_deposit_recordinfo'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'deposit'. DS . 'recordinfo.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_companycard'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'deposit'. DS . 'companycard.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_depositinfo'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'deposit'. DS . 'depositinfo.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_depositlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'deposit'. DS . 'depositlist.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_depositaccountinfo'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'deposit'. DS . 'depositaccountinfo.php';
$GLOBALS['G_CLASS_FILES']['model_deposit_depositaccountlist'] = PDIR_USER.DS.'_app'.DS.'model'.DS.'deposit'. DS . 'depositaccountlist.php';
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
        'class.bDevelopMode' => TRUE, 
        'class.db.bRecordProcessTime' => TRUE, // 是否记录执行 SQL 的总计时间
        /* 调度器 & 控制器 */
        'apple.default.controller' => 'controller',
		'apple.default.action' => 'action',
        /* 日志类 */
		'class.logs.sBasePath' => PDIR.DS.'_tmp'.DS.'logs'.DS , // 默认日志路径 A_DIR.DS.'tmp'.DS.'logs'.DS,
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
//print_rr( A::getIni('/') );EXIT;


/*****************************************************************
 * 02, 根据需要初始化 LOGS 对象
 ****************************************************************/
if( (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG) )
{
    $GLOBALS['oLogs'] = A::singleton('logs');//new logs( /*array('iLogType'=>0)*/ );
}


/*****************************************************************
 * 03, 初始化视图对象 $oView, 初始化SESSION, runMVC !
 ****************************************************************/
if( !defined( 'DONT_USE_APPLE_FRAME_MVC' ) )
{
    $oView = new view(
    array(
        'template_dir' => PDIR.DS.'_app'.DS.'views'.DS.'default'.DS, // 结尾不需斜线
        'compile_dir' => PDIR.DS.'_tmp'.DS.'views_compiled',
        'cache_dir' => PDIR.DS.'_tmp'.DS.'views_cached',
        //'caching' => TRUE,
        //'cache_lifetime' => 5,
        'direct_output' => FALSE, // 直接输出
        'force_compile' => FALSE, // 强制编译
        )
    );
	// 全局参数 页面行数 限制值 1/12/2010
    $sSysPageSizeMax = getConfigValue("pagesize_max_limit");
    $GLOBALS['SysPageSizeMax'] = $sSysPageSizeMax;
    $oView->assign("syspagesizemax", $sSysPageSizeMax );
    $GLOBALS['isadmin']=1;
    $oSession = new sessiondb( array( 'aDBO' => $GLOBALS['aSysDbServer']['master']) );
    A::runMVC();
}






/**
 * 系统提示信息
 * @param  string  $sMsgDetail 消息内容
 * @param  int     $sMsgType   消息类型， 0消息，1错误，2询问
 * @param  array   $aLinks     可选的链接
 */
function sysMessage( $sMsgDetail, $sMsgType=0, $aLinks=array() )
{
	$iSeconds = 8;
    if( count($aLinks)==0 )
    {
        $aLinks[0]['text'] = '返回上一页';
        $aLinks[0]['href'] = 'javascript:history.back()';
    }

    $GLOBALS['oView']->assign('ur_here',      '系统信息');
    $GLOBALS['oView']->assign('auto_redirection', '如果您不做出选择，将在 <span id="spanSeconds">'.$iSeconds.'</span> 秒后跳转到第一个链接地址。');
    $GLOBALS['oView']->assign('msg_detail',   $sMsgDetail);
    $GLOBALS['oView']->assign('msg_type',     $sMsgType);
    $GLOBALS['oView']->assign('links',        $aLinks);
    $GLOBALS['oView']->assign('default_url',  $aLinks[0]['href'] );
    $GLOBALS['oView']->display('message.html');
    EXIT;
}
?>