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
class controller_developer extends basecontroller 
{
    /**
     * 管理员权限列表
     * url = ./?controller=developer&action=list
     * @author SAUL
     */
    function actionList()
    {
        $iId        = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval( $_GET["menuid"] ) : 0;
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
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
     * URL = ./?controller=developer&action=add
     * @author SAUL
     */
    function actionAdd()
    {
        $iMenuId    = isset($_GET["menuid"]) && is_numeric($_GET["menuid"]) ? intval($_GET["menuid"]) : 0;
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
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
        $oAdminMenu = A::singleton('model_adminmenu');
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
            default:
                sysMessage('操作成功', 0, $aLocation );
                break;
        }
    }



    /**
     * 修改菜单
     * URL = ./controller=developer&action=edit
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
        $oAdminMenu = A::singleton('model_adminmenu');
        $aAdminMenu = $oAdminMenu->adminMenu( $iMenuId );
        if( $aAdminMenu == -1 )
        {
            redirect(url("developer", "list"));
            EXIT;
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
     * URL =./?controller=developer&action=update
     * @author SAUL
     */
    function actionUpdate()
    {
        $aLocation = array( 0=>array( "text"=>"管返回理员权限列表", "href"=>url("developer","list") ));
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
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
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
     * URL = ./?controller=developer&action=disable
     * @author SAUL
     */
    function actionDisable()
    {
        $aLocation = array(0 =>array( "text"=>"返回: 管理员权限列表", "href"=>url("developer","list") ));
        $iMenuId   = (isset($_GET["menuid"])&&(is_numeric($_GET["menuid"]))) ? intval($_GET["menuid"]): 0;
        if( $iMenuId == 0 )
        {
            redirect( url( "developer", "list" ) );
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
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
     * URL = ./?controller=developer&action=enable
     * @author SAUL
     */
    function actionEnable()
    {
        $aLocation = array(0=>array( "text"=>"返回: 管理员权限列表", "href"=>url("developer","list") ));
        $iMenuId = isset($_GET["menuid"])&&(is_numeric($_GET["menuid"]))?intval($_GET["menuid"]):0;
        if( $iMenuId == 0 )
        {
            redirect(url("developer","list"));
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
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
     * URL = ./?controller=developer&action=del
     * @author SAUL
     */
    function actionDel()
    {
        $aLocation = array(0=>array( "text"=>"返回: 管理员权限列表", "href"=>url("developer","list") ));
        $iMenuId   = isset($_GET["menuid"]) && (is_numeric($_GET["menuid"])) ? intval($_GET["menuid"]) : 0;
        if( $iMenuId == 0 )
        {
            redirect( url("developer", "list") );
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
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
     * URL = ./?controller=developer&action=savesort
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
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
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
     * URL =./?controller=developer&action=enableall
     * @author SAUL
     */
    function actionEnableall()
    {
        $aLocation = array(0=>array( "text"=>"返回: 管理员权限列表", "href"=>url("developer","list") ));
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
        if($oAdminMenu->enableAll())
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
     * URL = ./?controller=developer&action=openlog
     * @author SAUL
     */
    function actionOpenlog()
    {
        $aLocation = array(0=>array( "text"=>"返回: 管理员权限列表", "href"=>url("developer","list") ));
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
        if($oAdminMenu->setLogStatus(1))
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
     * URL =./?controller=developer&action=closelog
     * @author SAUL
     */
    function actionCloselog()
    {
        $aLocation = array(0=>array( "text"=>"返回: 管理员权限列表", "href"=>url("developer","list") ));
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu = A::singleton('model_adminmenu');
        if($oAdminMenu->setLogStatus(0))
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( '操作失败', 1, $aLocation );
        }
    }



    /**
     * 用户组别管理
     * URL:./index.php?controller=developer&action=userteamlist
     */
    function actionUserteamlist()
    {
        /* @var $oUserGroup model_usergroup */
        $oUserGroup = A::singleton("model_usergroup");
        $aUserGroup = $oUserGroup->getList(array()," 1 order by `teamid` ASC");
        $GLOBALS['oView']->assign( "usergroup", $aUserGroup );
        $GLOBALS['oView']->assign( "ur_here",   "用户组别管理");
        $aLocation[0] = array("text"=>'增加用户组别',"href"=>url('developer','userteamadd'));
        $GLOBALS['oView']->assign( "actionlink" , $aLocation[0]);
        $oUserGroup->assignSysInfo();
        $GLOBALS['oView']->display("developer_userteeamlist.html");
        EXIT;
    }



    /**
     * 编辑用户组信息
     * URL:./index.php?controller=developer&action=userteamedit
     */
    function actionUserteamedit()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=>url('developer','userteamlist'));
        if(isset($_POST)&&!empty($_POST))
        { //对权限进行保存
            if(empty($_POST["groupid"])||!is_numeric($_POST["groupid"]))
            {
                sysMessage( "操作失败", 1, $aLocation);
            }
            $iGroupId = intval($_POST["groupid"]);
            $sGroupName = isset($_POST["groupname"])&&!empty($_POST["groupname"]) ? $_POST["groupname"] :"";
            if($sGroupName =="")
            {
                sysMessage( "操作失败:用户组名称不能为空.", 1, $aLocation );
            }
            if(!empty($_POST["menus"])&&!is_array($_POST["menus"]))
            {
                sysMessage( "操作失败:数据提交失败.", 1, $aLocation );
            }
            foreach( $_POST["menus"] as &$menu)
            {
                if(!is_numeric($menu))
                {
                    unset($menu);
                }
            }
            $aUserGroup = array("groupname"=>$sGroupName,"menustrs"=>join(",",$_POST["menus"]));
            /* @var $oUserGroup model_usergroup */
            $oUserGroup = A::singleton("model_usergroup");
            if($oUserGroup->update($aUserGroup,"`groupid`='".$iGroupId."'"))
            {
                sysMessage("操作成功", 0, $aLocation );
            }
            else
            {
                sysMessage("操作失败", 1, $aLocation );
            }
            
        }
        else
        {
            $iGroupId = isset($_GET["groupid"])&&is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]) : 0;
            if( $iGroupId ==0 )
            {
                sysMessage("操作失败:数据不全.", 1, $aLocation);
            }
            /* @var $oUserGroup model_usergroup */
            $oUserGroup = A::singleton("model_usergroup");
            $aUserGroup = $oUserGroup->getById($iGroupId,array());
            if( empty($aUserGroup) )
            {
                sysMessage( "操作失败,用户组不存在", 1, $aLocation);
            }
            $GLOBALS['oView']->assign( "ug",      $aUserGroup );
            $GLOBALS['oView']->assign( "menustr", json_encode(explode(",",$aUserGroup["menustrs"])) );
            /* @var $oUserMenu model_usermenu */
            $oUserMenu = A::singleton("model_usermenu");
            $aUserMenu = $oUserMenu->getList(array('menuid','parentid','parentstr','title','description'),"");
            $aMenus    = array();
            foreach( $aUserMenu as $menu )
            {
                $aMenus[$menu["parentid"]][$menu["menuid"]] = $menu;
            }
            unset($aUserMenu);
            $GLOBALS['oView']->assign( "menu",     $aMenus );
            $GLOBALS['oView']->assign( "ur_here",  "编辑用户组别" );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $oUserMenu->assignSysInfo();
            $GLOBALS['oView']->display( "developer_usermenuedit.html" );
            EXIT;
        }
    }



    /**
     * 复制用户组别
     * URL:./index.php?controller=developer&action=userteamcopy
     */
    function actionUserteamcopy()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=> url("developer","userteamlist") );
        $iGroupId = isset($_GET["groupid"])&&is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]):0;
        /* @var $oUserGroup model_usergroup */
        $oUserGroup = A::singleton("model_usergroup");
        if( $oUserGroup->usergroupcopy($iGroupId) )
        {
            sysMessage( "复制成功", 0, $aLocation );
        }
        else
        {
            sysMessage( "复制失败", 1, $aLocation );
        }
    }



    /**
     * 禁用用户组别
     * URL:./index.php?controller=developer&action=userteamdisable
     */
    function actionUserteamdisable()
    {
    $aLocation[0] = array("text"=>'用户组别管理',"href"=> url("developer","userteamlist") );
        $iGroupId = isset($_GET["groupid"])&&is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]):0;
        /* @var $oUserGroup model_usergroup */
        $oUserGroup = A::singleton("model_usergroup");
        $aUserGroup["isdisabled"] = "1";
        if( $oUserGroup->update($aUserGroup,"`groupid`='".$iGroupId."'") )
        {
            sysMessage( "禁用成功", 0, $aLocation );
        }
        else
        {
            sysMessage( "禁用失败", 1, $aLocation );
        }
    }



    /**
     * 启用用户组别
     * URL:./index.php?controller=developer&action=userteamenable
     */
    function actionUserteamenable()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=> url("developer","userteamlist") );
        $iGroupId = isset($_GET["groupid"])&&is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]):0;
        /* @var $oUserGroup model_usergroup */
        $oUserGroup = A::singleton("model_usergroup");
        $aUserGroup["isdisabled"] = "0";
        if( $oUserGroup->update($aUserGroup,"`groupid`='".$iGroupId."'") )
        {
            sysMessage( "启用用户组别成功", 0, $aLocation );
        }
        else
        {
            sysMessage( "启用用户组别失败", 1, $aLocation );
        }
    }



    /**
     * 添加用户基类组
     * URL:./index.php?controller=developer&action=Userteamadd
     */
    function actionUserteamadd()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=>url('developer','userteamlist'));
        if(isset($_POST)&&!empty($_POST))
        { //对权限进行保存
            $sGroupName = isset($_POST["groupname"])&&!empty($_POST["groupname"]) ? $_POST["groupname"] :"";
            if($sGroupName =="")
            {
                sysMessage( "操作失败:用户组名称不能为空.", 1, $aLocation );
            }
            if(!empty($_POST["menus"])&&!is_array($_POST["menus"]))
            {
                sysMessage( "操作失败:数据提交失败.", 1, $aLocation );
            }
            foreach( $_POST["menus"] as &$menu)
            {
                if(!is_numeric($menu))
                {
                    unset($menu);
                }
            }
            /* @var $oUserGroup model_usergroup */
            $oUserGroup = A::singleton("model_usergroup");
            if($oUserGroup->insertUserGroup($sGroupName, 0, join(",",$_POST["menus"]),0, 0 ))
            {
                sysMessage("增加用户组别成功",0, $aLocation);
            }
            else
            {
                sysMessage("增加用户组别失败",1, $aLocation);
            }
        }
        else
        {
            $GLOBALS['oView']->assign( "menustr", json_encode(array()) );
            /* @var $oUserMenu model_usermenu */
            $oUserMenu = A::singleton("model_usermenu");
            $aUserMenu = $oUserMenu->getList(array('menuid','parentid','parentstr','title','description'),"");
            $aMenus    = array();
            foreach( $aUserMenu as $menu )
            {
                $aMenus[$menu["parentid"]][$menu["menuid"]] = $menu;
            }
            unset($aUserMenu);
            $GLOBALS['oView']->assign( "menu",       $aMenus );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "action",     "userteamadd");
            $GLOBALS['oView']->assign( "ur_here",    "增加用户组别" );
            $oUserMenu->assignSysInfo();
            $GLOBALS['oView']->display( "developer_usermenuedit.html" );
            EXIT;
        }
    }



    /**
     * 删除用户组别
     * URL:./index.php?controller=developer&action=userteamdel
     */
    function actionUserteamdel()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=>url('developer','userteamlist'));
        if( isset($_GET["teamid"])&&is_numeric($_GET["teamid"]) )
        {
            $sWhere = "`teamid`='".$_GET["teamid"]."'";
        }
        elseif( isset($_GET["spid"])&&is_numeric($_GET["spid"]) )
        { //对基础数据的保护
            $sWhere = "(`isspecial`='".$_GET["spid"]."' or `groupid`='".$_GET["spid"]."') and (`groupid`>4)";
        }
        else
        {
            $sWhere = ""; 
        }
        if( empty($sWhere) )
        {
            sysMessage("操作失败,数据错误.", 1, $aLocation);
        }
        /* @var $oUserGroup model_usergroup */
        $oUserGroup = A::singleton("model_usergroup");
        if( $oUserGroup->delete($sWhere) )
        {
           sysMessage("操作成功", 0, $aLocation );
        }
        else
        {
            sysMessage("操作失败", 1, $aLocation );
        }
    }



    /**
     * 查看特殊组别中的总代
     * URL:./index.php?controller=developer&action=useteamUserlist
     */
    function actionUserteamuserlist()
    {
        if(isset($_POST)&&!empty($_POST))
        {
            $aLocation[0] = array("text"=>'分配用户组别',"href"=>url('developer','userteamuserlist'));
            $aUserTeam = isset($_POST['userteam']) ? $_POST['userteam'] : "";
            if(empty($aUserTeam))
            {
                sysMessage( "操作失败：数据错误",1, $aLocation );
            }
            
            $oUser = A::singleton("model_user");
            $sError = "";
            foreach ($aUserTeam as $v){ // 循环检查执行
            	if(empty($v)){
            		continue;
            	}
            	$aInfo = explode("#", $v);
            	$aUserInfo = $oUser->getUserExtentdInfo($aInfo[0]);
            	// 检查用户是否存在
            	if (empty($aUserInfo)){
            		$sError .= $aInfo[2] . ",";
            	}
            	
            	if (intval($aUserInfo['teamid']) === intval($aInfo[1])){// 未修改的用户不进行操作
            		continue;
            	}

            	if($oUser->updateUserTeam($aInfo[0],$aInfo[1]) === false)
	            {
					$sError .= $aInfo[2] . ",";
	            }
            }
            if (empty($sError)){
            	sysMessage( '操作成功.',0,$aLocation );
            } else {
            	$sError = mb_substr($sError, 0, -1, "utf-8");
            	sysMessage( "总代用户" . $sError . "操作失败", 1, $aLocation );
            }
        }
        else
        {
            /* @var $oAgent model_agent */
            $oAgent = A::singleton("model_agent");
            $aUser  = $oAgent->agentUserTeamget();
            /* @var $oUserTeam model_usergroup */
            $oUserTeam = A::singleton("model_usergroup");
            $aUserTeam = $oUserTeam->getList(array('groupname','teamid','isspecial'),"`groupid`='1' OR `isspecial`='1'");
            $GLOBALS['oView']->assign( "userteam", $aUserTeam );
            $GLOBALS['oView']->assign( "ur_here","分配用户组别" );
            $GLOBALS['oView']->assign( "agent",  $aUser );
            $oAgent->assignSysInfo();
            $GLOBALS['oView']->display("developer_userteamuserlist.html");
            EXIT;
        }
    }
}
?>