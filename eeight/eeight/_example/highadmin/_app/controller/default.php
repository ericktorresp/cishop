<?php
/**
 * 文件 : /_app/controller/default.php
 * 功能 : 用户登录以及管理员面首页
 * 
 * + actionIndex    用户登录以及用户登录后的首页 
 * + actionCenter   管理员登录主界面
 * + actionMenu     管理员的菜单
 * + actionStart    管理员的欢迎使用页面
 * + actionImage    登录页面的验证码
 * + actionExit     管理员退出
 * 
 *  登录成功后注册的 SESSION 变量:
 *     - $_SESSION["admin"]        =   管理员用户ID
 *     - $_SESSION["adminname"]    =   管理员用户名
 *  
 * TODO 静态缓存
 * 
 * @author      Tom,mark
 * @version    1.0.0
 * @package    highgame
 */

class controller_default extends basecontroller
{
    /**
     * 用户登录以及用户登录后的首页
     * URL = ./index.php?controller=default&action=index
     * @author Tom,mark
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
            $aLocation[0] = array( "text"=>"返回登录页面", "href"=>url("default","index") );
            $sValidateCode = isset($_SESSION["validateCode"]) ? md5(strtoupper($_SESSION["validateCode"])) : '';
            if( $sValidateCode =='' || $sValidateCode != strtolower(trim($_POST["captcha"])) )
            {
                sysMessage( '验证码不正确', 1, $aLocation );
            }
            /* @var $oAdminUser model_adminuser */
            $oAdminUser  = A::singleton("model_adminuser");
            $iAdminLogin = $oAdminUser->adminlogin( $_POST["adminname"], $_POST["adminpass"] );
            /* @var $oAdminLog model_adminlog */
            $oAdminLog   = A::singleton("model_adminlog");     		
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
                $bFlag = $oAdminUser->checkUserIsCanUseHighadmin();//检测用户是否有高频权限
                if($bFlag)
                {
                    $oAdminLog->insert( '登录成功', '成功登录平台', 'default', 'index' );
                    redirect( url("default","center"), 0, TRUE );
                }
                else 
                {
                    $oAdminLog->insert( '登录失败', '失败登录平台', 'default', 'index' );
                    unset($_SESSION);
                    session_destroy();
                    sysMessage( '你没有高频用户组别权限', 1, $aLocation );
                }
            }
            unset( $oAdminUser );
        }
        else
        { // 没有 POST 数据时, 显示用户登录界面
            if( isset($_SESSION["admin"]) && is_numeric($_SESSION["admin"]) )
            { // 存在 SESSION ID, 转至管理员欢迎页
                /* @var $oAdminUser model_adminuser */
                $oAdminUser  = A::singleton("model_adminuser");
                $bFlag = $oAdminUser->checkUserIsCanUseHighadmin();//检测用户是否有高频权限
                if($bFlag)
                {
                    redirect( url("default","center"), 0, TRUE );
                }
                else
                {
                    unset($_SESSION);
                    session_destroy();
                    $aLocation[0] = array( "text"=>"返回登录页面", "href"=>url("default","index") );
                    sysMessage( '你没有高频用户组别权限', 1, $aLocation );
                }
                unset( $oAdminUser );
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
     * URL = ./index.php?controller=default&action=center
     * @author Tom,mark
     */
    function actionCenter()
    {
        /* @var $oAdminUser model_config */
        $oConfig  = A::singleton("model_config");
        //获取管理平台域名:用于管理平台的切换链接
        $sAdminDomain = $oConfig->getConfigs('admindomain');
        if( $sAdminDomain != '' )
        {
            $aAdminDomain = explode("_", $sAdminDomain);
            $aAdminDomainResult = array();
            foreach ($aAdminDomain as $sDomain)
            {
                $aDomain = explode(",",$sDomain);
                $aAdminDomainResult[$aDomain[0]] = array( 'channelname' => $aDomain[1], 'channeltitle' => $aDomain[2] );
            }
        }
        $sGameVersion = $oConfig->getConfigs('gamevserion');//程序版本号
        $GLOBALS['oView']->assign( 'gameversion',  $sGameVersion);
        $GLOBALS['oView']->assign( 'admindomain', $aAdminDomainResult );
        $GLOBALS['oView']->assign( 'adminname', isset($_SESSION['adminname']) ? $_SESSION['adminname'] : '');
        $GLOBALS['oView']->display('default_center.html');
        EXIT;
    }
    
    
    /**
     * 管理员的菜单
     * URL = ./index.php?controller=default&action=menu
     * @author Tom
     */
    function actionMenu()
    {
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
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
            if( strpos($v["actioner"],"_") !== FALSE && $v["controller"] == 'locks')
            {
                $aActioner = explode("_",$v['actioner']);
                $aMenus[$v["parentid"]][$v["menuid"]]["action"]	= 'lockdetail';
                $aMenus[$v["parentid"]][$v["menuid"]]["param"]['lotteryid'] = intval($aActioner[1]);
                $aMenus[$v["parentid"]][$v["menuid"]]["param"]['lotteryname'] = strtoupper($aActioner[0]);
            }
        }
        unset($aAdminmenu);
        $GLOBALS['oView']->assign( 'lang', $GLOBALS['_LANG'] );
        $GLOBALS['oView']->assign( 'menus', $aMenus );
        $GLOBALS['oView']->display('default_menu.html');
        EXIT;
    }
    
    
    /**
     * 管理员的欢迎使用页面
     * URL = ./index.php?controller=default&action=Start
     * @author Tom
     */
    function actionStart()
    {
        /* @var $oAdminNote model_adminnote */
        $oAdminNote = A::singleton("model_adminnote");
        if( isset($_POST) && isset($_POST['modmessage']) && $_POST['modmessage']=='1' )
        { // 修改管理员记事本内容
            $aUserArr = array(0=>array("text" => "返回起始页","href" => url("default","start")));
            $bFlag = $oAdminNote->setAdminNote( $_SESSION["admin"], $_POST['message'] );
            if( $bFlag == TRUE )
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
        $oPassPort = A::singleton("model_passport");
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
     * URL = ./index.php?controller=default&action=image
     * @author Tom
     */
    function actionImage()
    {
        /* @var $validate validatecode */
        $validate = A::singleton("validatecode");
        $validate->setImage(array('width'=>115,'height'=>30,'type'=>'png'));
        $validate->setCode( array('characters'=>'0-9','length'=>4,'deflect'=>FALSE,'multicolor'=>FALSE) );
        $validate->setFont( array("space"=>10,"size"=>18,"left"=>10,"top"=>25,"file"=>'') ); 
        $validate->setMolestation( array("type"=>FALSE,"density"=>'fewness') );
        $validate->setBgColor( array('r'=>40,'g'=>150,'b'=>115) );
        $validate->setFgColor( array('r'=>255,'g'=>255,'b'=>255) );
        $validate->paint();
        $_SESSION['validateCode'] = $validate->getcode();
        EXIT;
    }
    
    
    /**
     * 管理员退出
     * URL = ./index.php?controller=default&action=image
     * @author Tom
     */
    function actionExit()
    {
        unset($_SESSION);
        session_destroy();
        redirect( url("default", "index") );
    }
}
?>