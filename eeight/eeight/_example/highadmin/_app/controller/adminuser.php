<?php
/**
 * 文件 : /_app/controller/adminuser.php
 * 功能 : 控制器 - 管理员管理
 * 
 *   - actionList()         管理员列表
 *   - actionAdd()          增加新管理员(前台)
 *   - actionDel()          删除管理员  (执行)
 *   - actionProxy()        分配总代    (前台)
 *   - actionSaveProxy()    保存总代和销售之间的关系()
 *   - actionChangePass()   管理员修改自身登陆密码(前台)
 *   - actionShowlog()      显示管理员操作日志
 *   - actionEdit()         修改管理员
 *   - actionUpdate()       更新管理员
 * 
 * @author	    Mark, Tom
 * @version    1.2.0
 * @package    highadmin
 */

class controller_adminuser extends basecontroller
{
    /**
     * 管理员用户列表
     * URL = ./index.php?controller=adminuser&action=list
     * @author Mark, Tom
     */
    function actionList()
    {
        $iGroupId  = isset($_GET['groupid'])&&is_numeric($_GET["groupid"]) ? intval($_GET['groupid']) : 0;
        $sUserName = isset($_GET['username']) ? $_GET['username'] : '';
        $sWhere = ' 1 ';
        $aHtmlValue = array(); // 用于解析 HTML 中, 搜索条件的宏
        /* @var $oAdminUser model_adminuser */
        $oAdminUser = A::singleton('model_adminuser');
        if( $iGroupId != 0 )
        {
            $sWhere .= " AND ( a.`groupid` = $iGroupId OR FIND_IN_SET( ".$iGroupId.", b.`parentstr`) ) ";
            $aHtmlValue['groupid'] =  $iGroupId;
        }
        if( $sUserName != '' )
        {
            if( strstr($sUserName, '*') ) // 搜索到通配符 * 号 
            {
                $tmpUsername = str_replace( '*', '%', $sUserName );
                $sWhere .= " AND `adminname` LIKE '".$oAdminUser->getDB()->es($tmpUsername)."' ";
            }
            else 
            {
                $sWhere .= " AND `adminname` = '".$oAdminUser->getDB()->es($sUserName)."' ";
            }
            $aHtmlValue['username'] =  stripslashes_deep($sUserName);
        }
        /* @var $oAdminTeams model_admingroup */
        $oAdminTeams       = A::singleton('model_admingroup');
        $aAdminGroupList   = $oAdminTeams->getAdminGroupList(0,$iGroupId,TRUE);
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oAdminUser->getAdminList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages',       $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aHtmlValue',  $aHtmlValue );
        $GLOBALS['oView']->assign( 'aUserList',   $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign( 'aAdminGroup', $aAdminGroupList );
        $GLOBALS['oView']->assign( 'ur_here',     '管理员列表' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("adminuser","add"), 'text'=>'增加管理员' ) );
        $oAdminUser->assignSysInfo();
        $GLOBALS['oView']->display("adminuser_list.html");
        EXIT;
    }



    /**
     * 增加管理员
     * URL = ./index.php?controller=adminuser&action=add
     * @author Mark, Tom
     */
    function actionAdd()
    {
        if( empty($_POST["saveadmin"]) )
        {
            /* @var $oAdminTeams model_admingroup */
            $oAdminTeams     = A::singleton("model_admingroup");
            $aAdminGroupList = $oAdminTeams->getAdminGroupList( 0, 0, TRUE );
            $GLOBALS['oView']->assign('aAdminGroup', $aAdminGroupList );
            $GLOBALS['oView']->assign('ur_here','增加管理员');
            $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("adminuser","list"), 'text'=>'管理员列表' ) );
            $oAdminTeams->assignSysInfo();
            $GLOBALS['oView']->display("adminuser_add.html");
            EXIT;
        }
        else
        {
            $aLocation  = array(0=>array("text" => "继续增加管理员","href" => url("adminuser","add")),
                          1=>array("text" => "管理员列表管理","href" => url("adminuser","list")));
            $sAdminuser = isset($_POST["adminuser"]) ? $_POST["adminuser"] : "";
            $iAdminTeam = isset($_POST["adminteam"]) ? $_POST["adminteam"] : "";
            $sAdminnick = isset($_POST["adminnick"]) ? $_POST["adminnick"] : "";
            $sAdminpass = isset($_POST["adminpass"]) ? $_POST["adminpass"] : "";
            $sAdminlang = isset($_POST["adminlang"]) ? $_POST["adminlang"] : "utf8_zhcn";
            /* @var $oAdminUser model_adminuser */
            $oAdminUser = A::singleton("model_adminuser");
            $iResult    = $oAdminUser->addAdmin( $sAdminuser,$sAdminnick,$iAdminTeam,$sAdminpass,$sAdminlang );
            switch( $iResult )
            {
                case -1:
                    sysMessage("操作失败, 参数不全", 1, $aLocation);
                    break;
                case -2:
                    sysMessage("操作失败, 非法的管理员组", 1, $aLocation);
                    break;
                case -3:
                    sysMessage("操作失败, 管理员名称已经存在", 1, $aLocation);
                    break;
                case -4:
                    sysMessage("操作失败, 管理员昵称已经存在", 1, $aLocation);
                    break;
                case -5:
                    sysMessage("操作失败, 管理员用户名不符合规则", 1, $aLocation);
                    break;
                case -6:
                    sysMessage("操作失败, 管理员密码不符合规则", 1, $aLocation);
                    break;
                default:
                    sysMessage("操作成功", 0, $aLocation);
                    break;
            }
        }
    }



    /**
     * 删除管理员 (执行)
     * URL = ./index.php?controller=adminuser&action=del&userid=1
     * @author Mark, Tom
     */
    function actionDel()
    {
        $aLocation[0] = array("text" => "返回管理员列表","href" => url("adminuser","list"));
        $iUserId      = isset($_GET['userid']) ? intval($_GET['userid']) : 0;
        if( $iUserId == 0 )
        {
            sysMessage("无效的管理员ID", 1, $aLocation);
        }
        /* @var $oAdminUser model_adminuser */
        $oAdminUser   = A::singleton("model_adminuser");
        $iFlag        = $oAdminUser->userdel( $iUserId );
        if( $iFlag === TRUE )
        {
            sysMessage("操作成功, 管理员已被删除", 0, $aLocation );
        }
        elseif( $iFlag == -1 )
        {
            sysMessage("操作失败, 原因: 管理员ID不存在", 1, $aLocation );
        }
        else
        {
            sysMessage("操作失败", 1, $aLocation );
        }
    }



    /**
     * 管理员修改自身密码
     * URL = ./index.php?controller=adminuser&action=changepass
     * @author Mark, Tom
     */
    function actionChangePass()
    {
        if( empty($_POST["changepass"]) )
        {
            $GLOBALS['oView']->assign( 'ur_here','修改密码' );
            $GLOBALS['oView']->assign( 'lang',$GLOBALS['_LANG'] );
            $GLOBALS['oView']->display( "adminuser_changepass.html" );
            EXIT;
        }
        else
        {
            $aLocation[0] = array("text" => "继续修改密码","href" => url("adminuser","changepass"));
            $iAdminid  = intval( $_SESSION["admin"] );
            $sOldPass  = isset( $_POST["oldpass"] ) ? $_POST["oldpass"]  : '';
            $sNewPass  = isset( $_POST["newpass"] ) ? $_POST["newpass"]  : '';
            $sNewPass2 = isset( $_POST["newpass2"]) ? $_POST["newpass2"] : '';
            if( $sOldPass == $sNewPass )
            {
                sysMessage( "操作失败, 原因: 新密码与旧密码相同", 1, $aLocation );
            }
            if( $sNewPass != $sNewPass2 )
            {
                sysMessage( "操作失败, 原因: 两次输入的密码不相同", 1, $aLocation );
            }
            if( FALSE == model_user::checkUserPass($sNewPass) )
            {//检查密码合法性
                sysMessage( "操作失败, 原因: 新密码不符合规则", 1, $aLocation );
            }
            /* @var $oAdminUser model_adminuser */
            $oAdminUser = A::singleton("model_adminuser");
            if( $oAdminUser->changeSelfpass( $iAdminid, $sOldPass, $sNewPass ) == 1 )
            {
                sysMessage( "操作成功, 密码已修改", 0, $aLocation );
            }
            else
            {
                sysMessage( "操作失败, 请检查您的输入",1 , $aLocation );
            }
        }
    }



	/**
     * 对管理员进行修改
     * URL = ./index.php?controller=adminuser&action=edit
     * @author Mark, Tom
     */
    function actionEdit()
    {
        $aLocation[0] = array("text" => "返回管理员列表", "href"=>url("adminuser","list"));
        if( !empty($_POST['form_action']) && 
           (  $_POST['form_action']=='bat_enable'    // 批量解锁
           || $_POST['form_action']=='bat_disable'   // 批量锁定
           )
        )
        {
            /* @var $oAdminUser model_adminuser*/
            $oAdminUser = A::singleton("model_adminuser");
            switch ( $_POST['form_action'] )
            {
                case 'bat_enable' : // 批量解锁
                    if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
                    {
                        sysMessage( "未选择数据", 1, $aLocation );
                    }
                    if( $oAdminUser->batchStatusSet( $_POST['checkboxes'], 0 ) )
                    {
                        sysMessage( "操作成功",0, $aLocation );
                    }
                    else
                    {
                        sysMessage( "操作失败",1, $aLocation );
                    }
                    break;
                case 'bat_disable' : // 批量锁定
                    if( !isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
                    {
                        sysMessage( "未选择数据", 1, $aLocation );
                    }
                    if( $oAdminUser->batchStatusSet( $_POST['checkboxes'], 1 ) )
                    {
                        sysMessage( "操作成功",0, $aLocation );
                    }
                    else 
                    {
                        sysMessage( "操作失败",1, $aLocation );
                    }
                    break;
                default :
                    break;
            }
            EXIT;
        }
        //修改用户以及用户的特殊权限
        $iAdminId = (isset($_GET["adminid"])&&is_numeric($_GET["adminid"])) ? intval($_GET["adminid"]) : 0; 
        /* @var $oAdminUser model_adminuser */
        $oAdminUser = A::singleton("model_adminuser");
        $aUser = $oAdminUser->admin( $iAdminId );
        if($aUser == -1)
        {
             redirect( url('adminuser','list') );
        }
        $aUsermenus= array();
        $aUsermenu = explode( ",", $aUser["menustrs"] );
        foreach($aUsermenu as $value)
        {
            $aUsermenus[$value] = true;
        }
        /* @var $oAdminGroup model_admingroup */
        $oAdminGroup = A::singleton("model_admingroup");
        $aAdminGroup = $oAdminGroup->admingroup( $aUser["groupid"] );
        if( $aAdminGroup == -1 )
        {
            sysMessage( "无效的组别ID", 1, $aLocation );
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu  = A::singleton("model_adminmenu");
        $aAdminMenus = $oAdminMenu->adminMenuChild( 0, TRUE ); // 获取所有菜单
        unset( $oAdminMenu ); // 就近释放
        $aAdminMenuRight = explode( ",", $aAdminGroup["menustrs"] );
        $aMenus      = array();
        foreach( $aAdminMenus as $v )
        {
            $aMenus[$v["parentid"]][$v["menuid"]]["menuid"] = $v["menuid"];
            $aMenus[$v["parentid"]][$v["menuid"]]["title"]  = $v["title"];
            $aMenus[$v["parentid"]][$v["menuid"]]["desc"]   = $v["description"];
            $aMenus[$v["parentid"]][$v["menuid"]]["check"]  = in_array( $v["menuid"], $aAdminMenuRight );
        }
        $a           = array();
        foreach( $aMenus as $key => $value )
        {
            $a[$key] = count($value);
        }
        unset( $a[0] );
        $GLOBALS['oView']->assign( 'menus',      $aMenus );
        $GLOBALS['oView']->assign( 'counts',     $a );	
        $GLOBALS['oView']->assign( 'usermenus',  $aUsermenus );
        $aAdminGroupList = $oAdminGroup->getAdminGroupList( 0, $aUser["groupid"], TRUE );
        $GLOBALS['oView']->assign( 'aAdminGroup', $aAdminGroupList );
        $GLOBALS["oView"]->assign( "user",        $aUser  );
        $GLOBALS["oView"]->assign( "ur_here",     "修改管理员 [". $aUser['adminname'] ."]" );
        $GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("adminuser","list"), 'text'=>'管理员列表' ) );
        $GLOBALS["oView"]->display( "adminuser_info.html" );
        EXIT;
    }



    /**
     * 管理员更新
     * URL = ./index.php?controller=adminuser&action=update
     *  - 允许修改管理员时, 密码留空.(即: 密码保持不变)
     * @author Mark, Tom
     */
    function actionUpdate()
    {
        $aLocation[0] = array("text" => "返回管理员列表","href" => url("adminuser","list"));
        $iAdminid     = (isset($_POST["adminid"])&&is_numeric($_POST["adminid"])) ? intval($_POST["adminid"]) : 0;
        if( $iAdminid ==0 )
        {
            redirect( url('adminuser','list') );
        }
        $aUser["groupid"]   = isset($_POST["groupid"])   ? $_POST["groupid"]   : 0;
        $aUser["adminname"] = isset($_POST["adminuser"]) ? $_POST["adminuser"] : "";
        $aUser["adminpass"] = isset($_POST["adminpass"]) ? $_POST["adminpass"] : "";
        $aUser["adminnick"] = isset($_POST["adminnick"]) ? $_POST["adminnick"] : "";
        $aUser["adminlang"] = isset($_POST["adminlang"]) ? $_POST["adminlang"] : "utf8_zhcn";
        $aUser["islocked"]  = isset($_POST["islocked"])  ? $_POST["islocked"]  : 0;
        $aUser["groupid"]   = isset($_POST["groupid"])   ? $_POST["groupid"]   : 0;
        $aMenu              = isset($_POST["menustrs"])  ? $_POST["menustrs"] : array();
        $sAdminpass         = isset($_POST["adminpass"]) ? $_POST["adminpass"]  : "";
        $sAdminpass2        = isset($_POST["adminpass2"])? $_POST["adminpass2"] : "";
        if( !empty($sAdminpass) && $sAdminpass != $sAdminpass2 )
        {
            sysMessage("操作失败, 密码两次不一致", 1, $aLocation);
        }
        if( !empty($sAdminpass) && FALSE == model_user::checkUserPass($sAdminpass) )
        {//检查密码合法性
            sysMessage( "操作失败, 原因: 新密码不符合规则", 1, $aLocation);
        }
        /* @var $oAdminUser model_adminuser */
        $oAdminUser = A::singleton("model_adminuser");
        $iResult = $oAdminUser->adminUpdate( $iAdminid, $aUser, $aMenu );
        switch( $iResult )
        {
            case -1:
                sysMessage("操作失败, 参数出错 #1", 1, $aLocation);
                break;
            case -2:
                sysMessage("操作失败, 管理员不存在", 1, $aLocation);
                break;
            case -3:
                sysMessage("操作失败, 管理员的帐号不能为空", 1, $aLocation);
                break;
            case -100:
                sysMessage("操作失败, 组别获取失败", 1, $aLocation);
                break;
            default:
                sysMessage("操作成功", 0, $aLocation);
        }
    }
}
?>