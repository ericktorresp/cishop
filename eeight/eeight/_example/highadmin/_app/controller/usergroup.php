<?php
/**
 * 文件 : /_app/controller/usergroup.php
 * 功能 : 控制器 - 用户组别管理
 * 
 *    - actionList()        用户组别列表
 *    - actionAssign()      分配用户组别
 *    - actionEdit()        编辑用户组信息
 *    - actionCopy()        复制用户组别
 *    - actionDisable()     禁用用户组别
 *    - actionEnable()      启用用户组别
 *    - actionAdd()         增加用户组别
 *    - actionDelete()      删除用户组别
 * 
 * @author	    Mark
 * @version    1.0
 * @package    highadmin
 */

class controller_usergroup extends basecontroller
{
    /**
     * 用户组别列表
     * URL = ./index.php?controller=usergroup&action=list
     * @author Mark
     */
    public function actionList()
    {
        /* @var $oUserGroup model_usergroup */
        $oUserGroup = A::singleton("model_usergroup");
        $aUserGroup = $oUserGroup->getUserGroupList( "*", 1, '', " ORDER BY `teamid`");
        $GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("usergroup","add"), 'text'=>'增加用户组别' ) );
        $GLOBALS['oView']->assign( 'ur_here', '用户组别管理' );
        $GLOBALS['oView']->assign( 'aUserGroup', $aUserGroup );
        $oUserGroup->assignSysInfo();
        $GLOBALS['oView']->display("usergroup_list.html");
        EXIT;
    }

    
    
    /**
     * 分配用户组别
     * URL = ./index.php?controller=usergroup&action=assign
     * @author Mark
     */
    public function actionAssign()
    {
        $aLocation[0] = array( "text"=>'分配用户组别', "href"=>url('usergroup','assign') );
        /* @var $oUserGroup model_usergroup */
        $oUserGroup = A::singleton("model_usergroup");
        if( empty($_POST) )
        {
            $aUserGroup = $oUserGroup->getUserGroupList("`teamid`,`groupid`,`groupname`,`isspecial`", '1', ' GROUP BY `teamid`');
            /* @var $oUser model_user */
            $oUser = A::singleton("model_user");
            $aUser = $oUser->getChildrenListID( 0, FALSE );
            foreach ($aUser as & $aUserDetail )
            {
                $aGroupMessage = $oUserGroup->getUserGroup($aUserDetail['userid']);
                if(!empty($aGroupMessage) && isset($aGroupMessage['groupname']))
                {
                    $aUserDetail['groupname'] = $aGroupMessage['groupname'];
                    $aUserDetail['isspecial'] = $aGroupMessage['isspecial'];
                }
            }
            $GLOBALS['oView']->assign( 'ur_here', '分配用户组别' );
            $GLOBALS['oView']->assign( 'usergroup', $aUserGroup );
            $GLOBALS['oView']->assign( 'topuser', $aUser );
            $oUserGroup->assignSysInfo();
            $GLOBALS['oView']->display("usergroup_assign.html");
            EXIT;
        }
        else
        {
            $iUserId = isset($_POST['userid']) && $_POST['userid'] != '' ? intval($_POST['userid']) : 0;
            $iGroupId = isset($_POST['userteam']) && $_POST['userteam'] != '' ? $_POST['userteam'] : "";
            $bResult = $oUserGroup->assignUserGroup( $iUserId, $iGroupId );
            if($bResult)
            {
                sysMessage( '操作成功', 0, $aLocation );
            }
            else
            {
                sysMessage( '操作失败', 1, $aLocation );
            }
        }
    }



    /**
     * 编辑用户组信息
     * URL = ./index.php?controller=usergroup&action=edit
     * @author Mark
     */
    function actionEdit()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=>url('usergroup','list'));
        if( isset($_POST) && !empty($_POST) )
        { //对权限进行保存
            if( empty($_POST["groupid"]) || !is_numeric($_POST["groupid"]) )
            {
                sysMessage( "操作失败", 1, $aLocation);
            }
            $iGroupId = intval($_POST["groupid"]);
            $sGroupName = isset($_POST["groupname"]) && !empty($_POST["groupname"]) ? $_POST["groupname"] : "";
            if($sGroupName == "")
            {
                sysMessage( "操作失败:用户组名称不能为空.", 1, $aLocation );
            }
            if(!empty($_POST["menus"]) && !is_array($_POST["menus"]))
            {
                sysMessage( "操作失败:数据提交失败.", 1, $aLocation );
            }
            foreach( $_POST["menu"] as & $iMenu)
            {
                if(!is_numeric($iMenu))
                {
                    unset($iMenu);
                }
            }
            $aUserGroup = array("groupname"=>$sGroupName, "menustrs"=>join(",", $_POST["menu"]));
            /* @var $oUserGroup model_usergroup */
            $oUserGroup = A::singleton("model_usergroup");
            if($oUserGroup->update( $aUserGroup, "`groupid`='".$iGroupId."'"))
            {
                sysMessage("操作成功", 0, $aLocation );
            }
            else
            {
                sysMessage("操作失败:没有数据更新", 1, $aLocation );
            }
        }
        else
        {
            $iGroupId = isset($_GET["groupid"])&&is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]) : 0;
            if( $iGroupId == 0 )
            {
                sysMessage("操作失败:数据不全.", 1, $aLocation);
            }
            /* @var $oUserGroup model_usergroup */
            $oUserGroup = A::singleton("model_usergroup");
            $aUserGroup = $oUserGroup->getUserGroupList( "*", "`groupid` = '" . $iGroupId ."'");
            if( empty($aUserGroup) )
            {
                sysMessage( "操作失败,用户组不存在", 1, $aLocation);
            }
            
            /* @var $oUserMenu model_usermenu */
            $oUserMenu = A::singleton("model_usermenu");
            $aUserMenu = $oUserMenu->userMenuChild( 0, TRUE );
            $aMenuLevel = array();
            foreach ($aUserMenu as $aMenu )
            {
                $aMenuLevel[$aMenu['parentid']][$aMenu['menuid']] = $aMenu;
            }
            unset($aUserMenu);
            $GLOBALS['oView']->assign( "groupname", $aUserGroup[0]['groupname'] );
            $GLOBALS['oView']->assign( "groupid", $aUserGroup[0]['groupid'] );
            $GLOBALS['oView']->assign( "menustr", json_encode(explode(",",$aUserGroup[0]["menustrs"])) );
            $GLOBALS['oView']->assign( "menu",$aMenuLevel );
            $GLOBALS['oView']->assign( "ur_here",  "修改用户组别" );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "action",     "edit");
            $oUserMenu->assignSysInfo();
            $GLOBALS['oView']->display( "usergroup_info.html" );
            EXIT;
        }
    }



    /**
     * 复制用户组别
     * URL = ./index.php?controller=usergroup&action=copy
     * @author Mark
     */
    function actionCopy()
    {
        $aLocation[0] = array("text" => '用户组别管理',"href" => url("usergroup","list") );
        $iGroupId = isset($_GET["groupid"]) && is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]) : 0;
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
     * URL = ./index.php?controller=usergroup&action=disable
     * @author Mark
     */
    function actionDisable()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=> url("usergroup","list") );
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
     * URL = ./index.php?controller=usergroup&action=enable
     * @author Mark
     */
    function actionEnable()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=> url("usergroup","list") );
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
     * 增加用户组别
     * URL = ./index.php?controller=usergroup&action=add
     * @author Mark
     */
    function actionAdd()
    {
        $aLocation[0] = array("text"=>'用户组别管理',"href"=>url('usergroup','list'));
        if(isset($_POST)&&!empty($_POST))
        { //对权限进行保存
           $aGroupData = array();
           if( !isset($_POST['groupname']) || $_POST['groupname'] == '')
           {
               sysMessage('用户组别名称不能为空', 1, $aLocation);
           }
           if( !isset($_POST['menu']) || !is_array($_POST['menu']) )
           {
               sysMessage('用户组别权限不正确', 1, $aLocation);
           }
           $aGroupData['groupname'] = $_POST['groupname'];
           $aGroupData['menu']      = $_POST['menu'];
           /* @var $oUserGroup model_usergroup */
           $oUserGroup = A::singleton("model_usergroup");
           $mResult = $oUserGroup->addGroup( $aGroupData );
           if( $mResult === -1 )
           {
               sysMessage('操作失败：数据不完整', 1, $aLocation);
           }
           elseif ( $mResult === -2 )
           {
               sysMessage('操作失败：用户组名称不能为空', 1, $aLocation);
           }
           elseif ( $mResult === -3 )
           {
               sysMessage('操作失败：权限数据不正确', 1, $aLocation);
           }
           elseif ( $mResult === -4 )
           {
               sysMessage('操作失败：已经有相同的用户组存在，或者名称相同，或者权限相同', 1, $aLocation);
           }
           elseif ( $mResult === -5 )
           {
               sysMessage('操作失败：插入用户数据失败', 1, $aLocation);
           }
           elseif ( $mResult === -6 )
           {
               sysMessage('操作失败：对用户特殊权限复制', 1, $aLocation);
           }
           else
           {
               sysMessage('操作成功：添加成功', 0, $aLocation);
           }
        }
        else
        {
            /* @var $oUserMenu model_usermenu */
            $oUserMenu = A::singleton("model_usermenu");
            $aUserMenu = $oUserMenu->userMenuChild( 0, TRUE );
            $aMenuLevel = array();
            foreach ($aUserMenu as $aMenu )
            {
                $aMenuLevel[$aMenu['parentid']][$aMenu['menuid']] = $aMenu;
            }
            unset($aUserMenu);
            $GLOBALS['oView']->assign( "menu", $aMenuLevel );
            $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
            $GLOBALS['oView']->assign( "action",     "add");
            $GLOBALS['oView']->assign( "ur_here",    "增加用户组别" );
            $oUserMenu->assignSysInfo();
            $GLOBALS['oView']->display( "usergroup_info.html" );
            EXIT;
        }
    }



    /**
     * 删除用户组别
     * URL = ./index.php?controller=usergroup&action=delete
     * @author Mark
     */
    function actionDelete()
    {
        $aLocation[0] = array("text"=>'用户组别管理', "href"=>url('usergroup','list'));
        if( isset($_GET["teamid"]) && is_numeric($_GET["teamid"]) )
        {
            $sWhere = "`teamid`='" . $_GET["teamid"] . "'";
        }
        elseif( isset($_GET["spid"]) && is_numeric($_GET["spid"]) )
        { //对基础数据的保护
            $sWhere = "(`isspecial`='" . $_GET["spid"] . "' OR `groupid`='" .$_GET["spid"]. "') AND (`groupid`>4)";
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
}