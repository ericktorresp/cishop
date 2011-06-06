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
 * @author    Saul     090914
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_usermenu extends basecontroller 
{
    /**
     * 用户前台菜单权限列表
     * url = ./?controller=usermenu&action=list
     * @author SAUL
     */
    function actionList()
    {
        $iId        = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval( $_GET["menuid"] ) : 0;
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        $aUserMenu = $oUserMenu->getList( array(), "`parentid`='".$iId."'" );
        if( $iId >0 )
        {
            $aUserMenuPar = $oUserMenu->getOne( array(), "`menuid`='".$iId."'" );
            $GLOBALS['oView']->assign('id',  $aUserMenuPar["parentid"] );
            $GLOBALS['oView']->assign('sid', $iId );
        }
        $GLOBALS['oView']->assign('ur_here',      '权限列表' );
        $GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("usermenu","add"), 'text'=>'增加菜单' ) );
        $GLOBALS['oView']->assign('usermenu',    $aUserMenu );
        $GLOBALS['oView']->assign('parentmenuid', $iId );
        $oUserMenu->assignSysInfo();
        $GLOBALS['oView']->display("usermenu_list.html");
        EXIT;
    }



    /**
     * 增加菜单
     * URL = ./?controller=usermenu&action=add
     * @author SAUL
     */
    function actionAdd()
    {
        $iMenuId    = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval($_GET["menuid"]) : 0;
        /* @var $oUserMenu model_usermenu */
        $oUserMenu  = A::singleton('model_usermenu');
        $aUserMenu  = $oUserMenu->getList( array(), " `ismenu`='1' " );
        $aMenus     = array();
        foreach( $aUserMenu as $aValue )
        {
            $aMenus[$aValue['parentid']][$aValue['menuid']]=$aValue["title"];
        }
        $GLOBALS['oView']->assign("adminmenus",   $aMenus);
        $GLOBALS['oView']->assign("menuparentid", $iMenuId);    
        $GLOBALS['oView']->assign('ur_here',      '增加菜单');
        $GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("usermenu","list"), 'text'=>'权限列表' ) );
        $oUserMenu->assignSysInfo();
        $GLOBALS['oView']->display("usermenu_info.html");
        EXIT;
    }



    /**
     * 保存菜单
     * URL = ./?controller=usermenu&action=save
     * @author SAUL
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
        /* @var $oUserMenu model_usermenu */
        $oUserMenu    = A::singleton('model_usermenu');
        $aLocation    = array( 
            0 => array("text"=>"增加前台菜单","href"=>url("usermenu","add",array("menuid"=>$iParentId))),
            1 => array("text"=>"前台菜单管理","href"=>url("usermenu","list"))
            );
        if( $iParentId==0 )
        {
        	$sParent = "";
        }
        else
        {
        	$aParentMenu = $oUserMenu->getOne(array("parentid","parentstr"),"`menuid`='".$iParentId."'");
        	if( empty($aParentMenu) )
        	{
        		sysMessage("父级菜单不存在。", 1, $aLocation );
        	}
        	if($aParentMenu["parentid"] ==0 )
        	{
        		$sParent = $iParentId;
        	}
        	else
        	{
        		$sParent = $aParentMenu["parentstr"].",".$iParentId;
        	}
        }
        $aUserMenu  = array(
            "parentid"      =>  $iParentId,
            "parentstr"     =>  $sParent,
            "title"         =>  $sMenuName,
	        "description"   =>  $sDescription,
	        "controller"    =>  $sController,
	        "actioner"      =>  $sActioner,
	        "ismenu"        =>  $iIsMenu,
            "islink"        =>  $iIsLink,
            "sort"          =>  $iSort,
            "isdisabled"    =>  0,
            "actionlog"     =>  1,
        );
        $bResult   = $oUserMenu->insert( $aUserMenu );
        if( $bResult )
        {
        	sysMessage('操作成功', 0, $aLocation );
        }
        else
        {
        	sysMessage('操作失败', 1, $aLocation );
        }
    }



    /**
     * 修改菜单
     * URL = ./controller=usermenu&action=edit
     * @author SAUL
     */
    function actionEdit()
    {
        $iMenuId = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval($_GET["menuid"]) : 0;
        if( $iMenuId==0 )
        {
            redirect( url("usermenu", "list") );
        }
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        $aAdminMenu = $oUserMenu->getOne(array(),"`menuid`='".$iMenuId."'" );
        if( $aAdminMenu == -1 )
        {
            redirect(url("usermenu", "list"));
            EXIT;
        }
        $GLOBALS['oView']->assign('adminmenu', $aAdminMenu);
        $GLOBALS['oView']->assign("ur_here", "修改[编号为".$iMenuId."]菜单");
        $GLOBALS['oView']->assign("action", "update");
        $oUserMenu->assignSysInfo();
        $GLOBALS['oView']->display("usermenu_info.html");
        EXIT;
    }



    /**
     * 更新菜单
     * URL =./?controller=usermenu&action=update
     * @author SAUL
     */
    function actionUpdate()
    {
        $aLocation[0] = array( "text"=>"管返回理员权限列表", "href"=>url("usermenu","list") );
        $iMenuId   = isset($_POST["menuid"])&&is_numeric($_POST["menuid"]) ? intval($_POST["menuid"]) : 0;
        if( $iMenuId ==0 )
        {
            redirect(url("usermenu","list"));
        }
        $sMenuName      = $_POST["menuname"]      ? daddslashes($_POST["menuname"])       : "";
        $sDescription   = $_POST["description"]   ? daddslashes($_POST["description"])    : "";
        $sAction        = $_POST["actionname"]    ? daddslashes($_POST["actionname"])     : "";
        $sController    = $_POST["controllername"]? daddslashes($_POST["controllername"]) : "";
        $iIsMenu        = isset($_POST["ismenu"])&&is_numeric($_POST["ismenu"])       ? intval($_POST["ismenu"])    : 0;
        $iIsLink        = isset($_POST["islink"])&&is_numeric($_POST["islink"])       ? intval($_POST["islink"])    : 0;
        $iActionLog     = isset($_POST["actionlog"])&&is_numeric($_POST["actionlog"]) ? intval($_POST["actionlog"]) : 0;
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        $aUserMenu = array(
            "title"         =>  $sMenuName,
            "description"   =>  $sDescription,
            "controller"    =>  $sController,
            "actioner"      =>  $sAction,
            "ismenu"        =>  $iIsMenu,
            "islink"        =>  $iIsLink,
            "actionlog"     =>  $iActionLog,
        );
        if( $oUserMenu->update($aUserMenu,"`menuid`='".$iMenuId."'"))
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
     * URL = ./?controller=usermenu&action=disable
     * @author SAUL
     */
    function actionDisable()
    {
        $aLocation[0] = array( "text"=>"返回: 管理员权限列表", "href"=>url("usermenu","list") );
        $iMenuId   = (isset($_GET["menuid"])&&(is_numeric($_GET["menuid"]))) ? intval($_GET["menuid"]): 0;
        if( $iMenuId == 0 )
        {
            redirect( url( "usermenu", "list" ) );
        }
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        if($oUserMenu->update( array('isdisabled'=>'1'), "`menuid`='".$iMenuId."'" ))
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
     * URL = ./?controller=usermenu&action=enable
     * @author SAUL
     */
    function actionEnable()
    {
        $aLocation[0] = array( "text"=>"返回: 管理员权限列表", "href"=>url("usermenu","list") );
        $iMenuId = isset($_GET["menuid"])&&(is_numeric($_GET["menuid"]))?intval($_GET["menuid"]):0;
        if( $iMenuId == 0 )
        {
            redirect(url("usermenu","list"));
        }
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        if($oUserMenu->update( array('isdisabled'=>'0'), "`menuid`='".$iMenuId."'" ))
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
     * URL = ./?controller=usermenu&action=del
     * @author SAUL
     */
    function actionDel()
    {
        $aLocation[0] = array( "text"=>"返回: 管理员权限列表", "href"=>url("usermenu","list") );
        $iMenuId   = isset($_GET["menuid"]) && (is_numeric($_GET["menuid"])) ? intval($_GET["menuid"]) : 0;
        if( $iMenuId == 0 )
        {
            redirect( url("usermenu", "list") );
        }
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        $aMenu = $oUserMenu->getList(array(),"`parentid`='".$iMenuId."'");
        if( !empty($aMenu) )
        {
        	sysMessage('含有下级菜单,不能删除', 1, $aLocation );
        }
        if($oUserMenu->delete( "`menuid`='".$iMenuId."'" ))
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
     * URL = ./?controller=usermenu&action=savesort
     * @author SAUL
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
        $oUserMenu = A::singleton('model_usermenu');
        $aLocation[0] = array("text"=>'菜单管理',"href"=>url('usermenu','list',array('menuid'=>$iParentMenuId)));
        if($oUserMenu->userMenuSort( $iParentMenuId, $aSort ))
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
     * URL =./?controller=usermenu&action=enableall
     * @author SAUL
     */
    function actionEnableall()
    {
        $aLocation[0] = array( "text"=>"返回: 管理员权限列表", "href"=>url("usermenu","list") );
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        if($oUserMenu->update(array('isdisabled'=>'0')," 1 "))
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage('操作失败', 1, $aLocation);
        }
    }



    /**
     * 开启所有日志
     * URL = ./?controller=usermenu&action=openlog
     * @author SAUL
     */
    function actionOpenlog()
    {
        $aLocation[0] = array( "text"=>"返回: 用户前台菜单列表", "href"=>url("usermenu","list") );
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        if($oUserMenu->update(array('actionlog'=>'1'),' 1 '))
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
     * URL =./?controller=usermenu&action=closelog
     * @author SAUL
     */
    function actionCloselog()
    {
        $aLocation[0] = array( "text"=>"返回: 用户前台菜单列表", "href"=>url("usermenu","list") );
        /* @var $oUserMenu model_usermenu */
        $oUserMenu = A::singleton('model_usermenu');
        if($oUserMenu->update(array('actionlog'=>'0'),' 1 '))
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