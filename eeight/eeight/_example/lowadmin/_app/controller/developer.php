<?php
/**
 * 文件 : /_app/controller/developer.php
 * 功能 : 控制器  - 开发管理
 *
 * 功能:
 *    - actionAdd()          增加菜单
 *    - actionCloselog()     关闭所有日志
 *    - actionDel()          删除菜单
 *    - actionDisable()      禁用菜单
 *    - actionEdit()         修改菜单
 *    - actionEnable()       启用菜单
 *    - actionEnableall()    启用所有菜单
 *    - actionList()         管理员权限列表
 *    - actionOpenlog()      开启所有日志
 *    - actionSave()         保存菜单
 *    - actionSavesort()     保存排序
 *    - actionUpdate()       更新菜单
 * 
 * 
 * @author    saul
 * @version   1.2.0
 * @package   lowadmin
 */
class controller_developer extends basecontroller 
{
    /**
     * 管理员权限列表
     * url = ./index.php?controller=developer&action=list
     * @author SAUL
     */
    function actionList()
    {
        $iId        = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval( $_GET["menuid"] ) : 0;
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        $aAdminMenu = $oAdminMenu->adminMenuChild( $iId, FALSE );
        if( $iId >0 )
        {
            $aAdminMenuParent = $oAdminMenu->adminMenu( $iId );
            $GLOBALS['oView']->assign('id',  $aAdminMenuParent["parentid"] );
            $GLOBALS['oView']->assign('sid', $iId );
        }
        $GLOBALS['oView']->assign('ur_here',      '权限列表' );
        $GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("developer","add"), 'text'=>'增加菜单' ) );
        $GLOBALS['oView']->assign('adminmenu',    $aAdminMenu );
        $GLOBALS['oView']->assign('parentmenuid', $iId );
        $oAdminMenu->assignSysInfo();
        $GLOBALS['oView']->display("developer_list.html");
        EXIT;
    }



    /**
     * 增加菜单
     * URL = ./index.php?controller=developer&action=add
     * @author SAUL
     */
    function actionAdd()
    {
        $iMenuId    = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval($_GET["menuid"]) : 0;
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        $aAdminMenu = $oAdminMenu->adminMenuChild( 0, TRUE );
        $aMenus     = array();
        foreach( $aAdminMenu as $aValue )
        {
            $aMenus[$aValue['parentid']][$aValue['menuid']]=$aValue["title"];
        }
        $GLOBALS['oView']->assign("adminmenus",   $aMenus);
        $GLOBALS['oView']->assign("menuparentid", $iMenuId);
        $GLOBALS['oView']->assign('ur_here',      '增加菜单');
        $GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("developer","list"), 'text'=>'权限列表' ) );
        $oAdminMenu->assignSysInfo();
        $GLOBALS['oView']->display("developer_info.html");
        EXIT;
    }



    /**
     * 保存菜单
     * URL = ./?controller=developer&action=save
     * @author SAUL
     * 完成度:100%
     */
    function actionSave()
    {
        $iParentId    = isset($_POST["parentid"]) && is_numeric($_POST["parentid"]) ? intval($_POST["parentid"]) : 0;
        $sMenuName    = isset( $_POST["menuname"] )     ? daddslashes($_POST["menuname"])       : "";
        $sDescription = isset($_POST["description"])    ? daddslashes($_POST["description"])    : "";
        $sController  = isset($_POST["controllername"]) ? daddslashes($_POST["controllername"]) : "";
        $sActioner    = isset($_POST["actionname"])     ? daddslashes($_POST["actionname"])     : "";
        $iIsLink      = isset($_POST["islink"]) && is_numeric($_POST["islink"]) ? intval($_POST["islink"]) : 0;
        $iIsMenu      = isset($_POST["ismenu"]) && is_numeric($_POST["ismenu"]) ? intval($_POST["ismenu"]) : 0;
        $iSort        = isset($_POST["sort"]) && is_numeric($_POST["sort"]) ? intval($_POST["sort"]) : 0;
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu   = A::singleton("model_adminmenu");
        $aLocation    = array( 
            0 => array("text"=>"增加管理员菜单","href"=>url("developer","add",array("menuid"=>$iParentId))),
            1 => array("text"=>"管理员权限列表","href"=>url("developer","list"))
            );
        $iFlag = $oAdminMenu->adminMenuAdd($sMenuName,$iParentId,$sDescription,$sController,$sActioner,
                                $iIsMenu,$iIsLink,$iSort);  
        switch ( $iFlag )
        {
            case -1:
                sysMessage('操作失败', 1, $aLocation );
                break;
            case -2://检测adminMenu 是否存在
                sysMessage('操作失败', 1, $aLocation );
                break;
            case -3://没有指定Actioner
                sysMessage('操作失败', 1, $aLocation );
                break;
            case -4://执行失败
                sysMessage('操作失败', 1,$aLocation );
                break;
            default:
                sysMessage('操作成功', 0, $aLocation );
                break;
        }
    }



    /**
     * 修改菜单
     * URL = ./index.php?controller=developer&action=edit
     * @author SAUL
     */
    function actionEdit()
    {
        $iMenuId = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval($_GET["menuid"]) : 0;
        if( $iMenuId==0 )
        {
            redirect( url("developer", "list") );
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        $aAdminMenu = $oAdminMenu->adminMenu( $iMenuId );
        if( $aAdminMenu == -1 )
        {
            redirect(url("developer", "list"));
            exit;
        }
        $GLOBALS['oView']->assign('adminmenu', $aAdminMenu);
        $GLOBALS['oView']->assign("ur_here", "修改[编号为".$iMenuId."]菜单");
        $GLOBALS['oView']->assign("action", "update");
        $oAdminMenu->assignSysInfo();
        $GLOBALS['oView']->display("developer_info.html");
        EXIT;
    }



    /**
     * 更新菜单
     * URL =./index.php?controller=developer&action=update
     * @author SAUL
     */
    function actionUpdate()
    {
        $aLocation[0] = array( "text"=>"管返回理员权限列表", "href"=>url("developer","list") );
        $iMenuId   = isset($_POST["menuid"])&&is_numeric($_POST["menuid"]) ? intval($_POST["menuid"]) : 0;
        if( $iMenuId ==0 )
        {
            redirect(url("developer","list"));
        }
        $sMenuname      = $_POST["menuname"]      ? daddslashes($_POST["menuname"])       : "";
        $sDescription   = $_POST["description"]   ? daddslashes($_POST["description"])    : "";
        $sAction        = $_POST["actionname"]    ? daddslashes($_POST["actionname"])     : "";
        $sController    = $_POST["controllername"]? daddslashes($_POST["controllername"]) : "";
        $iIsMenu        = isset($_POST["ismenu"])&&is_numeric($_POST["ismenu"])       ? intval($_POST["ismenu"])    : 0;
        $iIsLink        = isset($_POST["islink"])&&is_numeric($_POST["islink"])       ? intval($_POST["islink"])    : 0;
        $iActionLog     = isset($_POST["actionlog"])&&is_numeric($_POST["actionlog"]) ? intval($_POST["actionlog"]) : 0;
        /* @var $oAminMenu model_adminmenu */
        $oAdminMenu    = A::singleton("model_adminmenu");
        if( $oAdminMenu->adminMenuUpdate(
                    $iMenuId, $sMenuname, $sDescription, $sController, $sAction, 
                    $iIsMenu, $iIsLink, $iActionLog))
        {
            sysMessage('操作成功', 0, $aLocation);
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation);
        }
    }



    /**
     * 禁用菜单
     * URL = ./index.php?controller=developer&action=disable
     * @author SAUL
     */
    function actionDisable()
    {
        $aLocation[0] = array( "text"=>"返回管理员权限列表", "href"=>url("developer","list") );
        $iMenuId   = (isset($_GET["menuid"])&&(is_numeric($_GET["menuid"]))) ? intval($_GET["menuid"]): 0;
        if( $iMenuId == 0 )
        {
            redirect( url( "developer", "list" ) );
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        if($oAdminMenu->adminMenuEnable( $iMenuId, 1 ))
        {
            sysMessage('操作成功', 0, $aLocation);
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation);
        }
    }



    /**
     * 启用菜单
     * URL = ./index.php?controller=developer&action=enable
     * @author SAUL
     */
    function actionEnable()
    {
        $aLocation[0] = array( "text"=>"返回管理员权限列表", "href"=>url("developer","list") );
        $iMenuId = isset($_GET["menuid"])&&(is_numeric($_GET["menuid"]))?intval($_GET["menuid"]):0;
        if( $iMenuId == 0 )
        {
            redirect(url("developer","list"));
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        if( $oAdminMenu->adminMenuEnable( $iMenuId, 0 ) )
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
     * URL = ./index.php?controller=developer&action=del
     * @author SAUL
     */
    function actionDel()
    {
        $aLocation = array(0=>array( "text"=>"返回管理员权限列表", "href"=>url("developer","list") ));
        $iMenuId   = isset($_GET["menuid"]) && (is_numeric($_GET["menuid"])) ? intval($_GET["menuid"]) : 0;
        if( $iMenuId == 0 )
        {
            redirect( url("developer", "list") );
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        if($oAdminMenu->adminMenuDel( $iMenuId ))
        {
            sysMessage('操作成功', 0, $aLocation);
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation);
        }
    }



    /**
     * 保存菜单排序
     * URL = ./index.php?controller=developer&action=savesort
     * @author SAUL
     */
    function actionSavesort()
    {
        $aSort = isset($_POST["sort"])&&is_array($_POST["sort"]) ? daddslashes($_POST["sort"]) : "";
        if( empty($aSort) )
        {
            redirect(url("developer","list"));
        }
        $iParentMenuId = isset($_POST["menuid"])&&is_numeric($_POST["menuid"]) ? intval($_POST["menuid"]) : 0;
        /* @var $oAdminMenu model_adminmenu*/
        $oAdminMenu = A::singleton("model_adminmenu");
        $aLocation[0] = array("text"=>'菜单管理',"href"=>url('developer','list',array('menuid'=>$iParentMenuId)));
        if($oAdminMenu->adminMenuSort( $iParentMenuId, $aSort ))
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
     * URL =./index.php?controller=developer&action=enableall
     * @author SAUL
     */
    function actionEnableall()
    {
        $aLocation[0] = array( "text"=>"返回管理员权限列表", "href"=>url("developer","list") );
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        if($oAdminMenu->enableAll())
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
     * URL = ./index.php?controller=developer&action=openlog
     * @author SAUL
     */
    function actionOpenlog()
    {
        $aLocation[0] = array( "text"=>"返回管理员权限列表", "href"=>url("developer","list") );
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        if( $oAdminMenu->setLogStatus(1) )
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
     * URL =./index.php?controller=developer&action=closelog
     * @author SAUL
     */
    function actionCloselog()
    {
        $aLocation[0] = array( "text"=>"返回管理员权限列表", "href"=>url("developer","list") );
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton("model_adminmenu");
        if( $oAdminMenu->setLogStatus(0) )
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