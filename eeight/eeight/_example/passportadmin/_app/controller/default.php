<?php
/**
 * 文件 : /_app/controller/default.php
 * 功能 : 用户登录以及管理员面首页
 * 
 * TODO: 对静态页面进行缓存支持,提高效率
 * 
 * @author	    Tom   090914
 * @version    1.2.0
 * @package    passportadmin
 */
class controller_default extends basecontroller
{
    /**
     * 用户登录以及用户登录后的首页
     * URL = ./?controller=default&action=index
     * @author Tom 090515 10:36
     */
    function actionIndex()
    {
        if( getConfigValue( 'islimitip', 'no' ) == 'yes' )//是否启用IP访问控制
        {
            /* @var $oIPLimit model_iplimit */
            $oIPLimit = A::singleton('model_iplimit');
            $sRealIP = getRealIP();
            if( !$oIPLimit->checkIP($sRealIP) )//检测用户当前IP是否在信任列表中
            {
                @header("Location: http://www.google.com/");
            }
        }
    	if( !empty($_POST["adminlogin"]) )
    	{ // 当有 Post 数据时, 检测用户合法性
    	    $aLocation = array(0=>array( "text"=>"返回登录页面", "href"=>url("default","index") ));
	    	$sValidateCode = isset($_SESSION["validateCode"]) ? md5(strtoupper($_SESSION["validateCode"])) : '';
	    	if( $sValidateCode =='' || $sValidateCode != strtolower( trim($_POST["captcha"]) ) )
	    	{
	    	    sysMessage( '验证码不正确', 1, $aLocation );
	    	}
	    	/* @var $oAdminUser model_adminuser */
    		$oAdminUser  = A::singleton('model_adminuser');
    		$iAdminLogin = $oAdminUser->adminlogin( $_POST["adminname"], $_POST["adminpass"] );
    		unset( $oAdminUser );
    		/* @var $oAdminLog model_adminlog */
    		$oAdminLog = A::singleton('model_adminlog');
    		if( $iAdminLogin == -1 )
    		{
    		    sysMessage( '缺少参数', 1, $aLocation );
    		}
    		elseif( $iAdminLogin == -2 )
    		{
    			$oAdminLog->insert( '用户登录失败','用户登录失败, 用户不存在', 'default', 'index');
    		    sysMessage( '用户不存在或密码错误', 1, $aLocation );
    		}
    		elseif($iAdminLogin == -3 )
    		{
    			$oAdminLog->insert('用户登录失败','用户登录失败，用户被锁定','default','index');
    		    sysMessage( '用户已被锁定', 1, $aLocation );
    		}
    		elseif($iAdminLogin == -4)
    		{
    			$oAdminLog->insert('用户登录失败','用户登录失败，用户组不存在','default','index');
    		    sysMessage( '用户组不存在', 1, $aLocation );
    		}
    		elseif($iAdminLogin == -5)
    		{
    			$oAdminLog->insert('用户登录失败','用户登录失败，用户组被锁定','default','index');
    		    sysMessage( '用户组被锁定', 1, $aLocation );
    		}
    		elseif( $iAdminLogin == -6 )
    		{
    			$oAdminLog->insert('用户登录失败','用户登录失败，更新session key 失败','default','index');
    		    sysMessage( '更新资料失败', 1, $aLocation );
    		}
    		else
    		{
      			unset( $_SESSION["validateCode"] );    			
    			$_SESSION["admin"] = $iAdminLogin["adminid"];
    			$_SESSION["adminname"] = $iAdminLogin["adminname"];
    			if( isset($_POST["remember"]) && ($_POST["remember"]==1) )
    			{
    				@setcookie( "adminname", $iAdminLogin["adminname"] );
    			}
    			$oAdminLog->insert( '登录成功', '成功登录平台', 'default', 'index' );
    			redirect( url("default","center"), 0, TRUE );
    		}
    	}
    	else
    	{ // 没有 POST 数据时, 显示用户登录界面
    	    if( isset($_SESSION["admin"]) && is_numeric($_SESSION["admin"]) )
    	    { // 存在 SESSION ID, 转至管理员欢迎页
    	    	redirect( url("default","center"), 0, TRUE );
    	    }
    	    else
    	    {
    	    	$adminname = isset($_COOKIE["adminname"]) ? $_COOKIE["adminname"] : "";
    	    	$GLOBALS['oView']->assign( 'adminname', $adminname );
    	    	$GLOBALS['oView']->assign( 'lang', $GLOBALS['_LANG'] );
    			$GLOBALS['oView']->display( 'default_login.html' );
    			EXIT;
    	    }
    	}
    }



    /**
     * 管理员登录主界面
     * URL = ./?controller=default&action=center
     * @author Tom 090515 10:42
     */
    function actionCenter()
    {
        $GLOBALS['oView']->display('default_center.html');
    	EXIT;
    }



    /**
     * 左侧菜单部分折叠条
     * URL = controller=default&action=drag
     * @author Tom 090515 10:42
     */
    function actionDrag()
    {
    	$GLOBALS['oView']->display('default_drag.html');
    	EXIT;
    }



    /**
     * 管理员首页的顶部
     * URL = controller=default&action=top
     * @author Tom 090515 10:42
     * 完成:100%
     */
    function actionTop()
    {
        $GLOBALS['oView']->assign( 'adminname', isset($_SESSION['adminname']) ? $_SESSION['adminname'] : '');
    	$GLOBALS['oView']->display('default_top.html');
    	EXIT;
    }



    /**
     * 管理员的菜单
     * URL = controller=default&action=menu
     * @author Tom 090515 10:43
     */
    function actionMenu()
    {
        /* @var $oAdminMenu model_adminmenu */
    	$oAdminMenu = A::singleton('model_adminmenu');
    	$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
    	if ( $iAdmin <= 0 )
    	{
    		unset($_SESSION);
    		session_destroy();
    		redirect(url('default','index'));
    		EXIT;
    	}
    	$aAdminmenu = $oAdminMenu->getUserMenu( $iAdmin );
    	if( $aAdminmenu == FALSE )
    	{
    		unset($_SESSION);
    		session_destroy();
    		redirect(url('default','index'));
    		EXIT;
    	}
    	$aMenus = array();
    	foreach( $aAdminmenu as $v )
    	{
    		$aMenus[$v["parentid"]][$v["menuid"]]["title"]		=	$v["title"];
    		$aMenus[$v["parentid"]][$v["menuid"]]["controller"]	=	$v["controller"];
    		$aMenus[$v["parentid"]][$v["menuid"]]["action"]		=	$v["actioner"];
    	}
    	unset($aAdminmenu);
    	$GLOBALS['oView']->assign( 'lang', $GLOBALS['_LANG'] );
    	$GLOBALS['oView']->assign( 'menus', $aMenus );
    	$GLOBALS['oView']->display('default_menu.html');
    	EXIT;
    }



    /**
     * 管理员的欢迎使用页面
     * URL = controller=default&action=Start
     * @author Tom 090515 10:43
     * 完成: 95% 
     */
    function actionStart()
    {
        /* @var $oAdminNote model_adminnote */
        $oAdminNote = A::singleton('model_adminnote');
        if( isset($_POST) && isset($_POST['modmessage']) && $_POST['modmessage']=='1' )
        { // 修改管理员记事本内容
            $aUserArr = array(0=>array("text" => "返回起始页","href" => url("default","start")));
            $oFlag = $oAdminNote->setAdminNote( $_SESSION["admin"], $_POST['message'] );
            if( $oFlag == TRUE )
            {
                sysMessage("操作成功", 0, $aUserArr );
            }
            else 
            {
                sysMessage("操作失败", 1, $aUserArr );
            }
        }
        // 获取管理员记事本内容
        $sOpNotices = isset($_SESSION["admin"]) ? $oAdminNote->getAdminNote( $_SESSION["admin"] ) : '';
        if( $sOpNotices == -1 )
        {
            $sOpNotices = '';
        }
        $aWarning = array();

        /* 系统信息 */
        $sys_info['os']         = PHP_OS;
        $sys_info['php_ver']    = PHP_VERSION;
        $sys_info['web_server'] = $_SERVER['SERVER_SOFTWARE'];
        /* @var $oPassPort model_passport */
        $oPassPort = A::singleton('model_passport');
        $sys_info['project_ver']= $oPassPort->getVersion('all');
        $GLOBALS['oView']->assign('opNotice',  $sOpNotices);
        $GLOBALS['oView']->assign('sys_info',  $sys_info);
        $GLOBALS['oView']->assign('warning_arr', $aWarning );
        $GLOBALS["oView"]->assign("ur_here","欢迎登录");
        $oPassPort->assignSysInfo();
    	$GLOBALS['oView']->display('default_start.html');
    	EXIT;
    }


    /**
     * 登录页面的验证码
     * URL = controller=default&action=image
     * @author Tom 090515 10:44
     */
    function actionImage()
    {
		require A_DIR.DS.'includes'.DS.'plugin'.DS.'validate.php';
		EXIT;
    }


    /**
     * 管理员退出
     * URL = controller=default&action=image
     * @author Tom 090515 10:44
     */
    function actionExit()
    {
    	unset($_SESSION);
    	session_destroy();
    	redirect( url("default", "index") );
    }
}
?>