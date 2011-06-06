<?php
/**
 * 文件 : /_app/controller/admingroup.php
 * 功能 : 控制器 - 组别管理
 * 
 *    - actionList()        组别列表
 *    - actionEdit()        修改组别信息(前台)
 *    - actionUpdate()      修改组别信息   (处理)
 *    - actionAdd()         增加组别(前台)
 *    - actionCopy()        复制组别(前台)
 *    - actionSave()        保存组别信息   (处理)
 *    - actionDel()         删除组别       (处理)
 * 
 * @author	   Tom
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_admingroup extends basecontroller
{
	/**
	 * 组别列表
	 * URL = ./controller=admingroup&action=list
	 * @author Tom
	 */
    function actionList()
    {
        /* @var $oAdminTeams model_admingroup */
        $oAdminTeams     = A::singleton('model_admingroup');
        $aAdminGroupList = $oAdminTeams->getAdminGroupList( 0, 0, FALSE );
        $GLOBALS['oView']->assign('aAdminGroup', $aAdminGroupList );
        $GLOBALS['oView']->assign('count', count($aAdminGroupList) );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("admingroup","add"), 'text'=>'增加组别' ) );
        $GLOBALS['oView']->assign( 'ur_here', '组别列表' );
        $oAdminTeams->assignSysInfo();
        $GLOBALS['oView']->display("admingroup_list.html");
        EXIT;
    }



	/**
	 * 修改组别 (前台)
	 * URL = ./controller=admingroup&action=edit&groupid=1
	 * @author Tom
	 */
	function actionEdit()
	{
        $aLocation = array(0=>array("text" => "返回: 组别列表", "href" => url("admingroup","list")));
        $id = is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]) : 0;
        if( $id==0 )
        {
            sysMessage("组别ID错误", 1, $aLocation);
        }
        /* @var $oAdminGroup model_admingroup */
        $oAdminGroup = A::singleton('model_admingroup');
        $aAdminGroup = $oAdminGroup->admingroup( $id );
        if( $aAdminGroup == -1 )
        {
            sysMessage("无效的组别ID", 1, $aLocation);
        }
        /* @var $oAdminMenu model_adminmenu */
        $oAdminMenu  = A::singleton('model_adminmenu');
        $aAdminMenus = $oAdminMenu->adminMenuChild( 0, TRUE ); // 获取所有菜单
        unset($oAdminMenu); // 就近释放
        /* @var $oAdminGroup model_admingroup */
        $oAdminGroup = A::singleton('model_admingroup');
        $aAdminGroup = $oAdminGroup->admingroup( $id ); // 获取组别所有权限菜单 [menustrs]
        $aAdminMenuRight = explode( ",", $aAdminGroup["menustrs"] );
        $aMenus = array();
        foreach( $aAdminMenus as $v )
        {
        	$aMenus[$v["parentid"]][$v["menuid"]]["menuid"] = $v["menuid"];
        	$aMenus[$v["parentid"]][$v["menuid"]]["title"]  = $v["title"];
        	$aMenus[$v["parentid"]][$v["menuid"]]["desc"]   = $v["description"];
        	$aMenus[$v["parentid"]][$v["menuid"]]["check"]  = in_array( $v["menuid"],$aAdminMenuRight );
        }
        foreach( $aMenus as $key => $value )
        {
        	$a[$key] = count($value);
        }
        unset($a[0]);
        $GLOBALS['oView']->assign( 'menus', $aMenus );
        $GLOBALS['oView']->assign( 'counts', $a );
        $GLOBALS['oView']->assign( 'form_action', 'update' );
        $aAdminGroup['parentstring'] = $oAdminGroup->getAdminGroupList(0,$aAdminGroup['parentid'],TRUE);
        $GLOBALS['oView']->assign( 'admingroup', $aAdminGroup );
        $GLOBALS['oView']->assign( 'ur_here', "修改组别&nbsp; (".$aAdminGroup['groupname'].
        								",ID:".$aAdminGroup['groupid'].')' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("admingroup","list"), 'text'=>'组别列表' ) );
        $oAdminGroup->assignSysInfo();
        $GLOBALS['oView']->display("admingroup_info.html");
        EXIT;
	}



	/**
	 * 修改组别 (执行)
	 * URL = ./controller=admingroup&action=update
	 * @author Tom
	 */
	function actionUpdate()
	{
        $aLocation = array(0=>array("text" => "返回: 组别列表","href" => url("admingroup","list")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        /* 批量操作 */
        if( !empty($_POST['form_action']) && empty($_POST['menustrs']) && 
            ( $_POST['form_action']=='add_group'      // 增加新组别
            || $_POST['form_action']=='bat_enable'    // 组别批量启用
            || $_POST['form_action']=='bat_disable'   // 组别批量禁用
            || $_POST['form_action']=='bat_enableall' // 启用全部组别
            ))
        {
            if( $_POST['form_action'] == 'add_group' ) // 增加新组别
            {
                redirect( url('admingroup','add') );
            }
            elseif( $_POST['form_action'] == 'bat_enableall' ) // 组别批量启用
            {
                $oAdminGroup  = new model_admingroup();
                if( $oAdminGroup->EnableAll() )
                {
                    sysMessage("操作成功", 0, $aLocation );
                }
                else
                {
                    sysMessage("操作失败", 1, $aLocation );
                }
            }
            elseif( $_POST['form_action'] == 'bat_enable' ) // 组别批量启用
            {
                if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
                {
                    sysMessage("未选择数据", 1);
                }
                $oAdminGroup  = new model_admingroup();
                if( $oAdminGroup->batchStatusSet( $_POST['checkboxes'], 0 ) )
                {
                    sysMessage("操作成功",0, $aLocation );
                }
                else 
                {
                    sysMessage("操作失败",1, $aLocation );
                }
            }
            elseif( $_POST['form_action'] == 'bat_disable' ) // 组别批量禁用
            {
                if( !isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
                {
                    sysMessage("未选择数据", 1, $aLocation );
                }
                $oAdminGroup  = new model_admingroup();
                if( $oAdminGroup->batchStatusSet( $_POST['checkboxes'], 1 ) )
                {
                    sysMessage("操作成功",0, $aLocation );
                }
                else
                {
                    sysMessage("操作失败",1, $aLocation );
                }
            }
            else
            {
                 // 以后更多的操作
            }
            EXIT;
	    }
        if( empty($_POST['menustrs']) )
        {
            sysMessage( "'组别菜单权限'未分配, 请检查", 1);
        }
        $iGroupid = is_numeric($_POST["groupid"]) ? intval($_POST["groupid"]) : 0;
        $oAdminGroup = new model_admingroup();
        $aAdminGroup = $oAdminGroup->admingroup($iGroupid);
        if( $aAdminGroup == -1 )
        {
            sysMessage("无效的组别ID", 1, $aLocation);
        }
        $iFlag = $oAdminGroup->update( $iGroupid, $_POST );
        if( $iFlag === TRUE )
        {
            sysMessage("操作成功", 0, $aLocation);
        }
        if( $iFlag == -100 )
        {
            sysMessage("操作失败, 不允许组别移动至自己", 1, $aLocation);
        }
        elseif( $iFlag == -101 )
        {
            sysMessage("操作失败, 不允许组别移动至自己的下级", 1, $aLocation);
        }
        elseif( $iFlag == -200 )
        {
            sysMessage("操作失败, 数据库事务出错", 1, $aLocation);
        }
        else
        {
            sysMessage("操作失败, 请与技术部联系", 1, $aLocation);
        }
    }



    /**
	 * 复制组别 (前台)
	 * URL = ./?controller=admingroup&action=copy&groupid=1
	 * @author Tom 090515
	 */
	function actionCopy()
	{
	    $aLocation = array(0=>array("text" => "返回: 组别列表","href" => url("admingroup","list")));
		$id = is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]) : 0;
		if( $id==0 )
		{
		    sysMessage("组别ID错误", 1, $aLocation);
		}
		$oAdminGroup = new model_admingroup();
		$aAdminGroup = $oAdminGroup->admingroup( $id );
		if( $aAdminGroup == -1 )
		{
		    sysMessage("无效的组别ID", 1, $aLocation);
		}
		$oAdminMenu = new model_adminmenu();
		$aAdminMenus = $oAdminMenu->adminMenuChild( 0, TRUE ); // 获取所有菜单
		unset($oAdminMenu); // 就近释放
		$oAdminGroup = new model_admingroup();
		$aAdminGroup = $oAdminGroup->admingroup( $id ); // 获取组别所有权限菜单 [menustrs]
		$aAdminMenuRight = explode( ",", $aAdminGroup["menustrs"] );
		$aMenus = array();
		foreach( $aAdminMenus as $v )
		{
			$aMenus[$v["parentid"]][$v["menuid"]]["menuid"] = $v["menuid"];
			$aMenus[$v["parentid"]][$v["menuid"]]["title"]  = $v["title"];
			$aMenus[$v["parentid"]][$v["menuid"]]["check"]  = in_array( $v["menuid"],$aAdminMenuRight );
		}
		foreach( $aMenus as $key => $value )
		{
			$a[$key] = count($value);
		}
		unset($a[0]);
        $GLOBALS['oView']->assign( 'menus', $aMenus );
		$GLOBALS['oView']->assign( 'counts', $a );
		$GLOBALS['oView']->assign( 'form_action', 'save' );
		$aAdminGroup['parentstring'] = $oAdminGroup->getAdminGroupList(0,$aAdminGroup['parentid'],TRUE);
        $GLOBALS['oView']->assign( 'admingroup', $aAdminGroup );
        $GLOBALS['oView']->assign( 'ur_here', "复制组别&nbsp; - 复制于: (".
                            $aAdminGroup['groupname'].",ID:".$aAdminGroup['groupid'].')' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("admingroup","list"), 'text'=>'组别列表' ) );
		$GLOBALS['oView']->display("admingroup_info.html");
		EXIT;
	}
	


	/**
	 * 复制组别 (执行)
	 * URL = ./controller=admingroup&action=save
	 * @author Tom 090515 10:04
	 */
    function actionSave()
    {
        $aLocation = array(0=>array("text" => "返回: 组别列表","href" => url("admingroup","list")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        if( empty($_POST['menustrs']) )
        {
            sysMessage("'组别菜单权限'未分配, 请检查", 1);
        }
        $oAdminGroup = new model_admingroup();
    	$iFlag = $oAdminGroup->insert( $_POST );
        if( $iFlag > 0 )
    	{
    	    sysMessage("操作成功", 0, $aLocation);
    	}
    	else
    	{
    	    sysMessage("操作失败", 1, $aLocation);
    	}
    }



	/**
	 * 删除组别(执行)
	 * URL = ./controller=admingroup&action=del
	 * @author Tom 090515 10:04
	 */
    function actionDel()
    {
        $aLocation = array(0=>array( "text" => "返回: 组别列表","href" => url("admingroup","list")) );
        $id = is_numeric($_GET["groupid"]) ? intval($_GET["groupid"]) : 0;
        if( $id == 0 )
        {
        	sysMessage( "组别ID错误", 1, $aLocation );
        }
        $oAdminGroup = new model_admingroup();
        $iFlag = $oAdminGroup->delete($id);
        if( $iFlag === TRUE )
        {
        	sysMessage("操作成功", 0, $aLocation );
        }
        elseif( $iFlag == -1 )
        {
            sysMessage("操作失败, 原因: 组别ID不存在", 1, $aLocation );
        }
        elseif( $iFlag == -2  )
        {
            sysMessage("操作失败, 原因: 分组中含有子分组, 请先删除其子分组", 1, $aLocation );
        }
        elseif( $iFlag == -3 )
        {
            sysMessage("操作失败, 原因: 分组中含有管理员, 请先删除或转移管理员", 1, $aLocation );
        }
        else
        {
        	sysMessage("操作失败,请与技术部联系", 1, $aLocation );
        }
    }



    /**
     * 增加组别(前台修改页面)
     * URL = ./controller=admingroup&action=add
     * @author Tom 090515 10:05
     */
    function actionAdd()
    {
        $iParentId = isset($_GET['groupid']) ? intval($_GET['groupid']) : 0;
        $oAdminMenu = new model_adminmenu();
        $aAdminMenus = $oAdminMenu->adminMenuChild( 0, TRUE ); // 获取所有菜单
        unset($oAdminMenu); // 就近释放
        $aMenus = array();
        foreach( $aAdminMenus as $v )
        {
        	$aMenus[$v["parentid"]][$v["menuid"]]["menuid"] = $v["menuid"];
        	$aMenus[$v["parentid"]][$v["menuid"]]["title"]  = $v["title"];
        }
        foreach( $aMenus as $key => $value )
        {
        	$a[$key] = count($value);
        }
        unset($a[0]);
        $GLOBALS['oView']->assign( 'menus', $aMenus );
        $GLOBALS['oView']->assign('counts', $a );
        $oAdminGroup = new model_admingroup();
        $aAdminGroup['parentstring'] = $oAdminGroup->getAdminGroupList(0,$iParentId,TRUE);
        $aAdminGroup['sort'] = 500;  // 增加新组时, 默认排序为 500 
        $GLOBALS['oView']->assign('form_action', 'save' );
        $GLOBALS['oView']->assign( 'admingroup', $aAdminGroup );
        $GLOBALS['oView']->assign( 'ur_here', $iParentId ? '增加子组别' : '增加组别' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("admingroup","list"), 'text'=>'组别列表' ) );
        $GLOBALS['oView']->display("admingroup_info.html");
        EXIT;
    }
}
?>