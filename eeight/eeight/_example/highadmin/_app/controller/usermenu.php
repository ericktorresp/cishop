<?php
/**
 * 文件 : /_app/controller/usermenu.php
 * 功能 : 控制器  - 用户菜单管理
 *
 * 功能:
 *    - actionAdd()          增加用户菜单
 *    - actionCloselog()     关闭所有日志
 *    - actionDel()          删除菜单
 *    - actionDisable()      禁用菜单
 *    - actionEdit()         修改菜单
 *    - actionEnable()       启用菜单
 *    - actionEnableall()    启用所有菜单
 *    - actionList()         用户权限列表
 *    - actionOpenlog()      开启所有日志
 *    - actionSavesort()     保存排序
 * 
 * 
 * @author    Mark
 * @version   1.0.0
 * @package   highadmin
 */
class controller_usermenu extends basecontroller 
{
    /**
     * 用户权限列表
     * url = ./index.php?controller=usermenu&action=list
     * @author mark
     */
    function actionList()
    {
        $iMenuId    = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval( $_GET["menuid"] ) : 0;
        /* @var $oUserMenu model_usermenu */
        $oUserMenu  = A::singleton("model_usermenu");
        $aUserMenu  = $oUserMenu->userMenuChild( $iMenuId, FALSE );
        if( $iMenuId > 0 )
        {
            $aUserMenuParent = $oUserMenu->userMenu( $iMenuId );
            $GLOBALS['oView']->assign( 'id',  $aUserMenuParent["parentid"] );
            $GLOBALS['oView']->assign( 'sid', $iMenuId );
        }
        $GLOBALS['oView']->assign( 'ur_here', '用户权限列表' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("usermenu","add"), 'text'=>'增加用户菜单' ) );
        $GLOBALS['oView']->assign( 'usermenu', $aUserMenu );
        $GLOBALS['oView']->assign( 'parentmenuid', $iMenuId );
        $oUserMenu->assignSysInfo();
        $GLOBALS['oView']->display("usermenu_list.html");
        EXIT;
    }



    /**
     * 增加前台用户菜单
     * URL = ./index.php?controller=usermenu&action=add
     * @author mark
     */
    function actionAdd()
    {
        if( empty($_POST['flag']) || $_POST['flag']!='save' )
        {
            $iMenuId   = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval($_GET["menuid"]) : 0;
            /* @var $oUseerMenu model_usermenu */
            $oUserMenu = A::singleton("model_usermenu");
            $aUserMenu = $oUserMenu->UserMenuChild( 0, TRUE );
            $aMenus    = array();
            foreach( $aUserMenu as $aValue )
            {
                $aMenus[$aValue['parentid']][$aValue['menuid']] = $aValue["title"];
            }
            /* @var $oMethod model_method */
            $oMethod    = A::singleton("model_method");
            $aMethodList   = $oMethod->methodGetList( "a.`pid`,a.`methodid`,a.`lotteryid`,a.`methodname`,b.`cnname`" );
            $aLottery   = array();
            foreach( $aMethodList as $aMethod )
            {
                $aLottery[$aMethod['lotteryid']]['title']  = $aMethod['cnname'];
                $aLottery[$aMethod['lotteryid']]['method'][$aMethod['pid']][$aMethod['methodid']] = $aMethod['methodname'];
            }
            $GLOBALS['oView']->assign("usermenus",   $aMenus);
            $GLOBALS['oView']->assign("menuparentid", $iMenuId);
            $GLOBALS['oView']->assign("methods", $aLottery);
            $GLOBALS['oView']->assign('ur_here',      '增加用户菜单');
            $GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("usermenu","list"), 'text'=>'用户权限列表' ) );
            $oUserMenu->assignSysInfo();
            $GLOBALS['oView']->display("usermenu_info.html");
            EXIT;
        }
        else
        {
            $aData                   = array();
            $aData['parentid']       = isset($_POST["parentid"]) ? intval($_POST["parentid"]) : 0;
            $aData['title']          = isset($_POST["menuname"]) ? $_POST["menuname"] : 0;
            $sTempStr                = isset($_POST['methodid']) ? $_POST['methodid'] : '0|0';
            $aTempStr                = explode("|", $sTempStr);
            $aData['lotteryid']      = intval($aTempStr[0]);
            $aData['methodid']       = isset($aTempStr[1]) ? intval($aTempStr[1]) : 0;
            $aData['description']    = isset($_POST["description"]) ? $_POST["description"] : "";
            $aData['controller']     = isset($_POST["controllername"]) ? $_POST["controllername"] : "";
            $aData['actioner']       = isset($_POST["actionname"]) ? $_POST["actionname"] : "";
            $aData['ismenu']         = isset($_POST["ismenu"]) ? intval($_POST["ismenu"]) : 0;
            $aData['islink']         = isset($_POST["islink"]) ? intval($_POST["islink"]) : 0;
            $aData['islabel']        = isset($_POST["islabel"]) ? intval($_POST["islabel"]) : 0;
            $aData['faceparameter']  = isset($_POST["faceparameter"]) ? $_POST["faceparameter"] : '';
            $aData['sort']           = isset($_POST["sort"]) ? intval($_POST["sort"]) : 0;
            $aData['actionlog']      = isset($_POST["actionlog"]) ? intval($_POST["actionlog"]) : 0;
            /* @var $oUserMenu model_usermenu */
            $oUserMenu    = A::singleton("model_usermenu");
            $aLocation    = array( 
                0 => array("text"=>"增加用户菜单","href"=>url("usermenu","add",array("menuid"=>$aData['parentid']))),
                1 => array("text"=>"用户权限列表","href"=>url("usermenu","list"))
                );
            $iFlag = $oUserMenu->userMenuAdd( $aData );  
            switch( $iFlag )
            {
                case -1:
                    sysMessage('操作失败:参数错误', 1, $aLocation );
                    break;
                case -2://检测adminMenu 是否存在
                    sysMessage('操作失败:上级菜单不存在', 1, $aLocation );         
                    break;          
                case -3://没有指定Actioner
                    sysMessage('操作失败', 1, $aLocation );
                    break;
                default:
                    sysMessage('操作成功', 0, $aLocation );
                    break;
            }
            EXIT;
        }
    }



    /**
     * 修改菜单
     * URL = ./index.php?controller=usermenu&action=edit
     * @author mark
     */
    function actionEdit()
    {
        if( empty($_POST['flag']) || $_POST['flag'] != 'save' )
        {
            $iMenuId = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval($_GET["menuid"]) : 0;
            //没有指定菜单
            if( $iMenuId == 0 )
            {
                redirect( url("usermenu", "list") );
            }
            /* @var $oUserMenu model_usermenu */
            $oUserMenu = A::singleton("model_usermenu");
            $aUserMenu = $oUserMenu->userMenu( $iMenuId );
            //指定的菜单不存在
            if( empty($aUserMenu) )
            {
                redirect(url("usermenu", "list"));
                EXIT;
            }
            /* @var $oMethod model_method */
            $oMethod    = A::singleton("model_method");
            $aMethodList   = $oMethod->methodGetList( "a.`pid`,a.`methodid`,a.`lotteryid`,a.`methodname`,b.`cnname`" );
            $aLottery   = array();
            foreach( $aMethodList as $aMethod )
            {
                $aLottery[$aMethod['lotteryid']]['title']  = $aMethod['cnname'];
                $aLottery[$aMethod['lotteryid']]['method'][$aMethod['pid']][$aMethod['methodid']] = $aMethod['methodname'];
            }
            if($aUserMenu['faceparameter'] != '')
            {
                $aUserMenu['faceparameter'] = base64_decode($aUserMenu['faceparameter']);
            }
            $GLOBALS['oView']->assign( "methods",    $aLottery );
            $GLOBALS['oView']->assign( 'usermenu',   $aUserMenu );
            $GLOBALS['oView']->assign( "ur_here",    "修改 [编号为".$iMenuId."] 菜单" );
            $GLOBALS['oView']->assign( "action",     "edit" );
            $oUserMenu->assignSysInfo();
            $GLOBALS['oView']->display("usermenu_info.html");
            EXIT;
        }
        else
        {
            $iMenuId = isset($_POST["menuid"]) && is_numeric($_POST["menuid"]) ? intval($_POST["menuid"]) : 0;
            $iParentId = isset($_POST["theparentid"]) && is_numeric($_POST["theparentid"]) ? intval($_POST["theparentid"]) : 0;
            $aLocation[0] = array( "text"=>"返回用户权限列表", "href"=>url("usermenu","list",array('menuid'=>$iParentId)) );
            if( $iMenuId <=0 )
            {
                sysMessage('操作失败', 1, $aLocation);
            }
            $aData                   = array();
            $aData['title']          = isset($_POST["menuname"]) ? $_POST["menuname"] : 0;
            $aTempStr                = isset($_POST['methodid']) ? $_POST['methodid'] : '0|0';
            $aTempStr                = explode("|", $aTempStr);
            $aData['lotteryid']      = intval($aTempStr[0]);
            $aData['methodid']       = isset($aTempStr[1]) ? intval($aTempStr[1]) : 0;
            $aData['description']    = isset($_POST["description"]) ? $_POST["description"] : "";
            $aData['controller']     = isset($_POST["controllername"]) ? $_POST["controllername"] : "";
            $aData['actioner']       = isset($_POST["actionname"]) ? $_POST["actionname"] : "";
            $aData['ismenu']         = isset($_POST["ismenu"]) ? intval($_POST["ismenu"]) : 0;
            $aData['islink']         = isset($_POST["islink"]) ? intval($_POST["islink"]) : 0;
            $aData['islabel']        = isset($_POST["islabel"]) ? intval($_POST["islabel"]) : 0;
            $aData['faceparameter']  = isset($_POST["faceparameter"]) ? $_POST["faceparameter"] : '';
            $aData['sort']           = isset($_POST["sort"]) ? intval($_POST["sort"]) : 0;
            $aData['actionlog']      = isset($_POST["actionlog"]) ? intval($_POST["actionlog"]) : 0;
            /* @var $oUserMenu model_usermenu */
            $oUserMenu = A::singleton("model_usermenu");
            $mFlag = $oUserMenu->userMenuUpdate( $iMenuId, $aData );  
            if( $mFlag == FALSE )
            {
                sysMessage('操作失败', 1, $aLocation);
            }
            sysMessage('操作成功', 0, $aLocation);
            EXIT;
        }
    }



    /**
     * 禁用菜单
     * URL = ./index.php?controller=usermenu&action=disable
     * @author mark
     */
    function actionDisable()
    {
        $aLocation[0] = array( "text"=>"返回用户权限列表", "href"=>url("usermenu","list") );
        $iMenuId   = (isset($_GET["menuid"])&&(is_numeric($_GET["menuid"]))) ? intval($_GET["menuid"]): 0;
        if( $iMenuId == 0 )
        {
            sysMessage('操作失败', 1, $aLocation);
        }
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton("model_usermenu");
        if( $oUserMenu->userMenuEnable( $iMenuId, 1 ) )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }



    /**
     * 启用菜单
     * URL = ./index.php?controller=usermenu&action=enable
     * @author mark
     */
    function actionEnable()
    {
        $aLocation[0] = array( "text"=>"返回用户权限列表", "href"=>url("usermenu","list") );
        $iMenuId   = isset($_GET["menuid"]) && (is_numeric($_GET["menuid"])) ? intval($_GET["menuid"]) : 0;
        if( $iMenuId == 0 )
        {
            sysMessage('操作失败', 1, $aLocation);
        }
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton("model_usermenu");
        if( $oUserMenu->userMenuEnable( $iMenuId, 0 ) )
        {
            sysMessage('操作成功', 0, $aLocation);
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation);
        }
    }



    /**
     * 删除菜单
     * URL = ./index.php?controller=usermenu&action=del
     * @author mark
     */
    function actionDel()
    {
        $aLocation[0] = array( "text"=>"返回用户权限列表", "href"=>url("usermenu","list") );
        $iMenuId   = isset($_GET["menuid"]) && (is_numeric($_GET["menuid"])) ? intval($_GET["menuid"]) : 0;
        if( $iMenuId == 0 )
        {
            sysMessage('操作失败', 1, $aLocation);
        }
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton("model_usermenu");
        $mResult   = $oUserMenu->UserMenuDel( $iMenuId );
        if( $mResult === TRUE )
        {
            sysMessage('操作成功', 0, $aLocation);
        }
        elseif( $mResult === -1 )
        {
            sysMessage('操作失败，有下级菜单', 1, $aLocation);
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation);
        }
    }



    /**
     * 保存菜单排序
     * URL = ./index.php?controller=usermenu&action=savesort
     * @author mark
     */
    function actionSavesort()
    {
        $aSort = isset($_POST["sort"])&&is_array($_POST["sort"]) ? daddslashes($_POST["sort"]) : "";
        if( empty($aSort) )
        {
            redirect(url("usermenu","list"));
        }
        $iParentMenuId = isset($_POST["menuid"])&&is_numeric($_POST["menuid"]) ? intval($_POST["menuid"]) : 0;
        /* @var $oUserMenu model_usermenu */
        $oUserMenu     = A::singleton("model_usermenu");
        $aLocation[0]  = array("text"=>'菜单管理',"href"=>url('usermenu','list',array('menuid'=>$iParentMenuId)));
        if( $oUserMenu->userMenuSort( $iParentMenuId, $aSort ) )
        {
            sysMessage('操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation );
        }
    }



    /**
     * 开启所有菜单功能
     * URL =./index.php?controller=usermenu&action=enableall
     * @author mark
     */
    function actionEnableall()
    {
        $aLocation[0] = array("text"=>"用户权限列表","href"=>url("usermenu","list"));
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton("model_usermenu");
        if( $oUserMenu->enableAll() )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation );
        }
    }



    /**
     * 开启所有日志
     * URL = ./index.php?controller=usermenu&action=openlog
     * @author mark
     */
    function actionOpenlog()
    {
        $aLocation[0] = array("text"=>"用户权限列表","href"=>url("usermenu","list"));
        /* @var $oUseMenu model_usermenu */
        $oUserMenu = A::singleton("model_usermenu");
        if( $oUserMenu->setLogStatus(1) )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }



    /**
     * 关闭所有日志
     * URL =./index.php?controller=usermenu&action=closelog
     * @author mark
     */
    function actionCloselog()
    {
        $aLocation[0] = array("text"=>"用户权限列表","href"=>url("usermenu","list"));
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton("model_usermenu");
        if( $oUserMenu->setLogStatus( 0 ) )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }
}
?>