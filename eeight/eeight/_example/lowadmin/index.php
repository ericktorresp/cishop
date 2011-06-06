<?php
/**
 * 低频后台管理平台, 单入口文件
 */
@header("content-type:text/html; charset=UTF-8");
define('IN_APPLE', TRUE);
define('PAPPNAME', 'gamelowadmin'); // 定义应用程序名称(不可重复). 核心类 filecaches.php 的基础目录名
define('PDIR', dirname(__FILE__)); // 项目路径: D:\wwwroot\aframe\_example\lowadmin\
require realpath( PDIR."/../../library/a.php" );

define('PDIR_PASSPORT_ADMIN', realpath(PDIR.DS."..".DS."passportadmin") );
define('PDIR_PASSPORT_USER', realpath(PDIR.DS."..".DS."passport") );
define('PDIR_LOW_USER', realpath(PDIR.DS."..".DS."lowgame") );
define('PDIR_LOW_PROXY', realpath(PDIR.DS."..".DS."lowproxy") );
define('PDIR_LOW_ADMIN', realpath(PDIR.DS."..".DS."lowadmin") );

// 定义版本号
// 格式:               发行版本号,   SVN 版本号,  发布日期
define('PRJ_VERSION',  '1.2.0,         1652,         2009-09-14 16:00');

// 数据库配置文件 DSN 路径, PDIR . \_app\config\
define('PRJ_DSN_PATH', PDIR_LOW_USER.DS.'_app'.DS.'config'.DS );
require ( PRJ_DSN_PATH. 'dsn.php' ); 


define('PRJ_LANG_PATH', PDIR.DS.'_app'.DS.'language'.DS ); 
require_once( PRJ_LANG_PATH. 'utf8_zhcn' . DS . 'common.php' );



// 控制器默认路径
define( 'PRJ_CONTROLLER_PATH', PDIR.DS.'_app'.DS.'controller'.DS );
//模型层默认路径
define( 'PRJ_MODEL_PASSPORTADMIN_PATH', PDIR_PASSPORT_ADMIN . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_PASSPORT_PATH', PDIR_PASSPORT_USER . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_LOWUSER_PATH', PDIR_LOW_USER . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_LOWPROXY_PATH', PDIR_LOW_PROXY . DS . '_app' . DS . 'model' . DS );
define( 'PRJ_MODEL_LOWADMIN_PATH', PDIR_LOW_ADMIN . DS . '_app' . DS . 'model' . DS );

//  Controller 类名=>路径 对应关系数组
$GLOBALS['G_CLASS_FILES']['controller_admingroup']  = PRJ_CONTROLLER_PATH . "admingroup.php";
$GLOBALS['G_CLASS_FILES']['controller_adminuser']   = PRJ_CONTROLLER_PATH . "adminuser.php";
$GLOBALS['G_CLASS_FILES']['controller_config']      = PRJ_CONTROLLER_PATH . "config.php";
$GLOBALS['G_CLASS_FILES']['controller_data']        = PRJ_CONTROLLER_PATH . "data.php";
$GLOBALS['G_CLASS_FILES']['controller_default']     = PRJ_CONTROLLER_PATH . "default.php";
$GLOBALS['G_CLASS_FILES']['controller_developer']   = PRJ_CONTROLLER_PATH . "developer.php";
$GLOBALS['G_CLASS_FILES']['controller_draw']        = PRJ_CONTROLLER_PATH . "draw.php";
$GLOBALS['G_CLASS_FILES']['controller_gameinfo']    = PRJ_CONTROLLER_PATH . "gameinfo.php";
$GLOBALS['G_CLASS_FILES']['controller_locks']       = PRJ_CONTROLLER_PATH . "locks.php";
$GLOBALS['G_CLASS_FILES']['controller_log']         = PRJ_CONTROLLER_PATH . "log.php";
$GLOBALS['G_CLASS_FILES']['controller_marketmgr']   = PRJ_CONTROLLER_PATH . "marketmgr.php";
$GLOBALS['G_CLASS_FILES']['controller_notice']      = PRJ_CONTROLLER_PATH . "notice.php";
$GLOBALS['G_CLASS_FILES']['controller_report']      = PRJ_CONTROLLER_PATH . "report.php";
$GLOBALS['G_CLASS_FILES']['controller_user']        = PRJ_CONTROLLER_PATH . "user.php";
$GLOBALS['G_CLASS_FILES']['controller_usergames']   = PRJ_CONTROLLER_PATH . "usergames.php";
$GLOBALS['G_CLASS_FILES']['controller_usermenu']    = PRJ_CONTROLLER_PATH . "usermenu.php";
//模型层(LOWADMIN平台)
$GLOBALS['G_CLASS_FILES']['model_usermenu']         = PRJ_MODEL_LOWADMIN_PATH . 'usermenu.php';
$GLOBALS['G_CLASS_FILES']['model_charts']           = PRJ_MODEL_LOWADMIN_PATH . 'charts.php';
$GLOBALS['G_CLASS_FILES']['model_snapshot']         = PRJ_MODEL_LOWADMIN_PATH .'snapshot.php';
//模型层(LOWPROXY平台)
$GLOBALS['G_CLASS_FILES']['model_user']             = PRJ_MODEL_LOWPROXY_PATH . "user.php";
$GLOBALS['G_CLASS_FILES']['model_userfund']         = PRJ_MODEL_LOWPROXY_PATH . "userfund.php";
$GLOBALS['G_CLASS_FILES']['model_usermethodset']    = PRJ_MODEL_LOWPROXY_PATH . "usermethodset.php";
//模型层(LOWUSER平台)
$GLOBALS['G_CLASS_FILES']['model_projects']         = PRJ_MODEL_LOWUSER_PATH . "projects.php";
$GLOBALS['G_CLASS_FILES']['model_locks']            = PRJ_MODEL_LOWUSER_PATH . "locks.php";
$GLOBALS['G_CLASS_FILES']['model_orders']           = PRJ_MODEL_LOWUSER_PATH . "orders.php";
$GLOBALS['G_CLASS_FILES']['model_task']             = PRJ_MODEL_LOWUSER_PATH . "task.php";
$GLOBALS['G_CLASS_FILES']['model_gamebase']         = PRJ_MODEL_LOWUSER_PATH . 'gamebase.php' ;
$GLOBALS['G_CLASS_FILES']['model_gamemanage']       = PRJ_MODEL_LOWUSER_PATH . 'gamemanage.php' ;
$GLOBALS['G_CLASS_FILES']['model_gameplay']         = PRJ_MODEL_LOWUSER_PATH . "gameplay.php";
$GLOBALS['G_CLASS_FILES']['model_config']           = PRJ_MODEL_LOWUSER_PATH . "config.php";
//模型层(PASSPORT平台)
$GLOBALS['G_CLASS_FILES']['model_config']       = PRJ_MODEL_PASSPORT_PATH . 'config.php';
$GLOBALS['G_CLASS_FILES']['model_usersession']  = PRJ_MODEL_PASSPORT_PATH . 'usersession.php';
$GLOBALS['G_CLASS_FILES']['model_helps']        = PRJ_MODEL_PASSPORT_PATH . 'helps.php' ;
$GLOBALS['G_CLASS_FILES']['model_userlog']      = PRJ_MODEL_PASSPORT_PATH . 'userlog.php';
$GLOBALS['G_CLASS_FILES']['model_userskins']    = PRJ_MODEL_PASSPORT_PATH . 'userskins.php';
$GLOBALS['G_CLASS_FILES']['model_domains']      = PRJ_MODEL_PASSPORT_PATH . 'domains.php';
$GLOBALS['G_CLASS_FILES']['channelapi']         = PRJ_MODEL_PASSPORT_PATH . 'channelapi.php';
//模型层(PASSPORTADMIN平台)
$GLOBALS['G_CLASS_FILES']['model_adminmenu']    = PRJ_MODEL_PASSPORTADMIN_PATH."adminmenu.php";
$GLOBALS['G_CLASS_FILES']['model_adminuser']    = PRJ_MODEL_PASSPORTADMIN_PATH."adminuser.php";
$GLOBALS['G_CLASS_FILES']['model_admingroup']   = PRJ_MODEL_PASSPORTADMIN_PATH."admingroup.php";
$GLOBALS['G_CLASS_FILES']['model_adminlog']     = PRJ_MODEL_PASSPORTADMIN_PATH."adminlog.php";
$GLOBALS['G_CLASS_FILES']['model_adminnote']    = PRJ_MODEL_PASSPORTADMIN_PATH."adminnote.php";
$GLOBALS['G_CLASS_FILES']['model_passport']     = PRJ_MODEL_PASSPORTADMIN_PATH."passport.php";
$GLOBALS['G_CLASS_FILES']['model_iplimit']      = PRJ_MODEL_PASSPORTADMIN_PATH.'iplimit.php';

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
    /*****************************************************************
     * 03, 初始化视图对象 $oView
     ****************************************************************/
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

    /*****************************************************************
     * 04, 初始化session
     ****************************************************************/
    $GLOBALS['isadmin']=1;
    $oSession = new sessiondb( array( 'aDBO' => $GLOBALS['aSysDbServer']['master']) );

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
*   06 passport全局函数
****************************************************************/

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
