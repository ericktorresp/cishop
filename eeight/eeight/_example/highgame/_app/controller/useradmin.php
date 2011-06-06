<?php
/**
 * 文件 : /_app/controller/useradmin.php
 * 功能 : 总代管理员的一些控制器
 *  
 *
 * 功能:
 *    -- actionGroupList       总代管理员分组列表
 *    -- actionUpdateGroup     修改管理员分组
 *    -- actionDeleteGroup     删除管理组
 *    -- actionAddGroup        增加管理分组
 *    -- actionRightsGroup     修改组别权限
 *    -- actionAdminList       总代管理员列表
 *    -- actionAdminGroup      修改管理员所属分组
 *    -- actionAdminProxy      给总代销售管理员分配代理
 *    -- actionUpdatePass      总代管理员修改自己的密码
 * 
 * @author    james
 * @version   1.0.0
 * @package   highgame
 */

class controller_useradmin extends basecontroller 
{
    /**
     * 总代管理员分组列表
     * URL:./index.php?controller=useradmin&action=groupList
     * @author JAMES
     */
    function actionGroupList()
    {
        $iUserId = intval($_SESSION['userid']);
        // 如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            /* @var $oUser model_user */
            $oUser   = A::singleton("model_user");
            $iUserId = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            unset($oUser);
        }
        // 获取用户的管理员分组列表
        /* @var $oGroup model_proxygroup */
        $oGroup = A::singleton("model_proxygroup");
        $aData  = $oGroup->getListByUser( $iUserId );
        $GLOBALS['oView']->assign( 'groups',     $aData );
        $GLOBALS['oView']->assign( 'ur_here',    '组别列表' );
        //$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("useradmin","addgroup"), 'text'=>'增加组别' ) );
        $oGroup->assignSysInfo();
        $GLOBALS['oView']->display( 'useradmin_grouplist.html' );
        EXIT;
    }



    /**
     * 修改管理员分组
     * URL:./index.php?controller=useradmin&action=updategroup
     * @author JAMES
     */
    function actionUpdateGroup()
    {
        if( empty($_REQUEST['gid']) )
        { // 没有参数则返回上一页
            sysMsg( "非法操作", 2 );
        }
        $iGroupid	= intval($_REQUEST['gid']);
        $iUserId = $_SESSION['userid'];
        // 如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            /* @var $oUser model_user */
            $oUser   = A::singleton("model_user");
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            unset($oUser);
        }
        /* @var $oGroup model_proxygroup */
        $oGroup   = A::singleton("model_proxygroup");
        if( TRUE !== $oGroup->isPermitProxy( $iGroupid, $iUserId ) )
        {
            sysMsg( "没有权限", 2 );
        }
        
        if( isset($_POST['flag']) && $_POST['flag']=='update' )
        { // 修改组
            if( empty($_POST['newname']) )
            {
                sysMsg( "请填写组别名称" );
            }
            if( !model_user::checkNickName($_POST['newname']) )
            {
                sysMsg( "请填写合法的组别名称" );
            }
            $sNewName = $_POST['newname'];
            $mResult  = $oGroup->updateByUser( $iUserId, $iGroupid, array('groupname'=>$sNewName) );
            if( empty($mResult) )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('useradmin','grouplist'),
                                        'title'=>'管理员分组列表') );
                sysMsg( "操作成功", 1, $aLink );
            }
        }
        else
        {
            $aGroup   = $oGroup->getById( $iGroupid, array('groupname') );
            $GLOBALS['oView']->assign( 'groupid',   $iGroupid );
            $GLOBALS['oView']->assign( 'groupname', $aGroup['groupname'] );
            $GLOBALS['oView']->assign( 'ur_here',   '修改组别' );
            $GLOBALS['oView']->assign( 'actionlink', array('href'=>url("useradmin","grouplist"), 'text'=>'组别列表'));
            $GLOBALS['oView']->display( 'useradmin_updategroup.html' );
            EXIT;
        }
    }



    /**
     * 删除管理组
     * URL:./index.php?controller=useradmin&action=deletegroup
     * @author JAMES
     */
    function actionDeleteGroup()
    {
        $iUserId = intval( $_SESSION['userid'] );
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            /* @var $oUser model_user */
            $oUser   = A::singleton("model_user");
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            unset($oUser);
        }
        if( isset($_GET['gid']) && is_numeric($_GET['gid']) )
        {//单个删除
            /* @var $oGroup model_proxygroup */
            $oGroup  = A::singleton("model_proxygroup");
            if( TRUE !== $oGroup->isPermitProxy( $_GET['gid'], $iUserId ) )
            {
                sysMsg( "没有权限", 2 );
            }
            $mResult = $oGroup->deleteByUser( $iUserId, $_GET['gid'] );
            if( empty($mResult) )
            {
                sysMsg( "删除失败，系统默认组不能删除", 2 );
            }
            elseif( $mResult === -1 )
            {
                sysMsg( "删除失败，该组下还有用户，请先转移用户到其他组", 2 );
            }
            $aLink[0] = array('url'=>url('useradmin','grouplist'),'title'=>'管理员分组列表');
            sysMsg( "删除成功", 1, $aLink );
        }
        if( isset($_POST['flag']) && $_POST['flag']=='delete' )
        {//批量删除
            if( empty($_POST['select_rows']) || !is_array($_POST['select_rows']) )
            {
                redirect( url('useradmin','grouplist') );
                EXIT;
            }
            /* @var $oGroup model_proxygroup */
            $oGroup  = A::singleton("model_proxygroup");
            if( TRUE !== $oGroup->isPermitProxy( $_POST['select_rows'], $iUserId ) )
            {
                sysMsg( "没有权限", 2 );
            }
            $mResult = $oGroup->deleteByUser( $iUserId, implode(',',$_POST['select_rows']) );
            if( empty($mResult) )
            {
                sysMsg( "删除失败，系统默认组不能删除", 2 );
            }
            elseif( $mResult === -1 )
            {
                sysMsg( "删除失败，所选组下还有用户，请先转移用户到其他组", 2 );
            }
            $aLink[0] = array('url'=>url('useradmin','grouplist'),'title'=>'管理员分组列表');
            sysMsg( "删除成功", 1, $aLink );
        }
        redirect( url('useradmin','grouplist') );
        EXIT;
    }



    /**
     * 增加管理分组
     * URL:./index.php?controller=useradmin&action=addgroup
     * @author JAMES
     */
    function actionAddGroup()
    {
        if( empty($_POST['flag']) || $_POST['flag']!='insert' )
        {
            $GLOBALS['oView']->assign( 'ur_here',    '增加组别' );
            $GLOBALS['oView']->assign( 'actionlink', array('href'=>url("useradmin","grouplist"), 'text'=>'组别列表'));
            $GLOBALS['oView']->display('useradmin_addgroup.html','proxygroupadd');
            EXIT;
        }
        else
        {
            if( empty($_POST['groupname']) )
            {
                sysMsg("请填写组名");
            }
            if( !model_user::checkNickName($_POST['groupname']) )
            {
                sysMsg("请填写合法的组别名称");
            }
            $iUserId = $_SESSION['userid'];
            //如果为总代管理员，则当前用户调整到其总代ID
            if( $_SESSION['usertype'] == 2 )
            {
                /* @var $oUser model_user */
                $oUser   = A::singleton("model_user");
                $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
                if( empty($iUserId) )
                {
                    sysMsg( "操作失败", 2 );
                }
                unset($oUser);
            }
            /* @var $oGroup model_proxygroup */
            $oGroup  = A::singleton("model_proxygroup");
            $mResult = $oGroup->proxyGroupInsert( $_POST['groupname'], $iUserId, '', 3 );
            if( empty($mResult) )
            {
                sysMsg( "操作失败", 2 );
            }
            $aLink[0] = array('url'=>url('useradmin','grouplist'),'title'=>'管理员分组列表');
            sysMsg( "操作成功", 1, $aLink );
        }
    }



    /**
     * 修改组别权限
     * URL:./index.php?controller=useradmin&action=rightsgroup
     * @author JAMES
     */
    function actionRightsGroup()
    {
        define( 'USER_ADMIN_BANK',     0x0001 ); // 可看总代银行余额
        define( 'USER_ADMIN_STAR',     0x0002 ); // 可看总代星级

        if( empty($_REQUEST['gid']) )
        {//没有参数则返回上一页
            sysMsg( "操作失败", 2 );
        }
        $iUserId = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            /* @var $oUser model_user */
            $oUser   = A::singleton("model_user");
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            unset($oUser);
        }
        $iGroupid	= intval($_REQUEST['gid']);
        /* @var $oGroup model_proxygroup */
        $oGroup = A::singleton("model_proxygroup");
        if( TRUE !== $oGroup->isPermitProxy( $iGroupid, $iUserId ) )
        {
            sysMsg( "没有权限", 2 );
        }
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            //获取总代自己所有的权限菜单列表
            /* @var $oMenus model_usermenu */
            $oMenus = A::singleton("model_usermenu");
            $aData  = $oMenus->getUserMenus( $iUserId, 1, 1, ' AND (ismenu=1 OR islabel=1 OR islink=1)' );
            if( empty($aData) || empty($aData['menus']) )
            {//获取菜单失败
                sysMsg( "获取权限菜单出错", 2 );
            }
            $aMenus  = empty($aData['menus']) ? array() : $aData['menus'];
            $aCounts = array();
            foreach( $aMenus as $k=>$v )
            {
                $aCounts[$k] = count($v);
            }
            //获取该组的已经有的权限
			$oAdminProxyMenu = A::singleton('model_adminproxymenu');
            $aData  = $oAdminProxyMenu->getById( $iGroupid );
            if( !$aData )
            {
				if($oAdminProxyMenu->insert(array('groupid'=>$iGroupid,'menustrs'=>'','ownerid'=>$iUserId)))
				{
					$aData  = $oAdminProxyMenu->getById( $iGroupid );
				}
				else
				{
					sysMsg( "操作错误", 2 );
				}
            }
            if( empty($aData['menustrs']) )
            {
                $aGroupMenus = array();
            }
            else
            {
                $temp_menus = explode( ',', $aData['menustrs'] );
                foreach( $temp_menus as $v )
                {
                    $aGroupMenus[$v] = "1";
                }
                unset($temp_menus);
            }
            if( $aData['issales'] == 1 )
            {//指定为销售
                $aGroupMenus['other'] = 1;
            }
            if( $aData['viewrights'] > 0 )
            {
                $aGroupMenus['other'] = 1;
            }
            if( $aData['viewrights'] & USER_ADMIN_BANK )
            {
                $aGroupMenus['isbank'] = 1;
            }
            if( $aData['viewrights'] & USER_ADMIN_STAR )
            {
                $aGroupMenus['isstar'] = 1;
            }
            $aGroupMenus['issales'] = $aData['issales'];
            $GLOBALS['oView']->assign( 'menus',     $aMenus );
            $GLOBALS['oView']->assign( 'counts',    $aCounts );
            $GLOBALS['oView']->assign( 'groupmenus',$aGroupMenus );
            $GLOBALS['oView']->assign( 'groupid',   $iGroupid );
            $GLOBALS['oView']->assign( 'groupname', $aData['groupname'] );
            $GLOBALS['oView']->assign( 'ur_here',   '组别权限' );
            $GLOBALS['oView']->assign( 'actionlink',array('href'=>url("useradmin","grouplist"), 'text'=>'组别列表'));
            $oGroup->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_rightsgroup.html' );
            EXIT;
        }
        else 
        {//修改权限进数据库
            if( empty($_POST['menustrs']) || !is_array($_POST['menustrs']) )
            {
                $aMenustrs = '';
            }
            else
            {
                $aMenustrs = implode( ',', $_POST['menustrs'] );
            }
            //$iIsSales    = empty($_POST['issale']) ? 0 : 1;
            //$iIsBank     = empty($_POST['isbank']) ? 0 : 1;
            //$iIsStar     = empty($_POST['isstar']) ? 0 : 2;
            //$iViewRigths = $iIsBank+$iIsStar;
            $mResult     = $oGroup->updateByUser( $iUserId, $iGroupid, 
                   array('menustrs'=>$aMenustrs/*,'issales'=>$iIsSales,'viewrights'=>$iViewRigths*/) );
            if( empty($mResult) )
            {//修改失败
                sysMsg( "修改失败", 2 );
            }
            else
            {
                $aLink[0] = array('url'=>url('useradmin','grouplist'),'title'=>'管理员分组列表');
                sysMsg( "修改成功", 1, $aLink );
            }
        }
    }



    /**
     * 总代管理员列表
     * URL:./index.php?controller=useradmin&action=adminlist
     * @author JAMES
     */
    function actionAdminList()
    {
        $iUserId = $_SESSION['userid'];
        /* @var $oUser model_user */
        $oUser   = A::singleton("model_user");
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        //获取总代的管理员列表
        $aData	= $oUser->getAdminList( $iUserId, " ORDER BY ut.`username` ASC " );
        $GLOBALS['oView']->assign( 'users', $aData );
        $GLOBALS['oView']->assign( 'ur_here',     '管理员列表' );
        $GLOBALS['oView']->display( 'useradmin_adminlist.html' );
        EXIT;
    }



    /**
     * 修改管理员所属分组
     * URL:./index.php?controller=useradmin&action=admingroup
     * @author JAMES
     */
    function actionAdminGroup()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {//没有参数则返回上一页
            sysMsg("操作失败",2);
        }
        $uid     = intval($_REQUEST['uid']);
        $iUserId = $_SESSION['userid'];
        /* @var $oUser model_user */
        $oUser   = A::singleton("model_user");
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        if( FALSE == (bool)$oUser->isParent($uid,$iUserId) )
        {
            echo "<script>alert('没有操作权限');history.back(-1);</script>";
            exit;
        }
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            //获取用户信息
            if( FALSE == ($aData=$oUser->getUserExtentdInfo($uid, 2)) )
            {
                sysMsg( "操作失败，连接超时", 2 );
            }
            unset($oUser);
            $GLOBALS['oView']->assign( 'data', $aData );
            //获取总代分组
            /* @var $oGroup model_proxygroup */
            $oGroup = A::singleton("model_proxygroup");
            if( FALSE == ($groupdata = $oGroup->getListByUser( $iUserId )) )
            {
                sysMsg( "操作失败，连接超时", 2 );
            }
            $GLOBALS['oView']->assign( 'groups',    $groupdata );
            $GLOBALS['oView']->assign( 'ur_here',   '修改组别' );
            $GLOBALS['oView']->assign( 'actionlink',array('href'=>url("useradmin","adminlist"),'text'=>'管理员列表'));
            $oGroup->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_admingroup.html' );
            EXIT;
        }
        else 
        {
            if( !isset($_POST['groupid']) || !is_numeric($_POST['groupid']) )
            {
                sysMsg( "请选择用户组" );
            }
            $groupid = intval($_POST['groupid']);
            /* @var $oGroup model_proxygroup */
            $oGroup = A::singleton("model_proxygroup");
            if( TRUE !== $oGroup->isPermitProxy( $groupid, $iUserId ) )
            {
                sysMsg( "没有权限", 2 );
            }
            /* @var $channel model_userchannel */
            $channel = A::singleton("model_userchannel");
            $bResult = $channel->updateGroupSet( $uid, array('groupid'=>$groupid) );
            if( $bResult ==  FALSE )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink[0] = array('url'=>url('useradmin','adminlist'), 'title'=>'管理员列表');
                sysMsg( "操作成功", 1, $aLink );
            }
        }
    }



    /**
     * 给总代销售管理员分配代理
     * URL:./index.php?controller=useradmin&action=adminproxy
     * @author JAMES
     */
   /**
	function actionAdminProxy()
    {
        $iUserId = $_SESSION['userid'];
        $oUser   = A::singleton("model_user");
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            //获取总代销售管理员列表
            $oUserAdmin = A::singleton("model_useradminproxy"); 
            $auseradmin = $oUserAdmin->getAdminSaleList( $iUserId );
            $aproxy		= $oUserAdmin->getAdminProxyList( $iUserId );
            if( !empty($aproxy) )
            {
                foreach( $aproxy as &$v )
                {
                    if( !empty($v['adminid']) && isset($auseradmin[$v['adminid']]) )
                    {
                        $v['adminname'] = $auseradmin[$v['adminid']]['username'];
                    }
                    else 
                    {
                        $v['adminname'] = "未分配";
                    }
                }
            }
            $GLOBALS['oView']->assign( 'useradmins', $auseradmin );
            $GLOBALS['oView']->assign( 'proxys',     $aproxy );
            $GLOBALS['oView']->assign( 'ur_here',    '分配代理 ' );
            $oUserAdmin->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_amdinproxy.html' );
            EXIT;
        }
        else
        {
            if( !is_numeric($_POST['adminuser']) )
            {
                sysMsg( "非法操作", 2 );
            }
            $adminuser = intval($_POST['adminuser']);
            if( FALSE == (bool)$oUser->isParent($adminuser, $iUserId) )
            {
                sysMsg( "没有操作权限", 2 );
            }
            if( empty($_POST['select_rows']) )
            {
                sysMsg( "操作失败", 2 );
            }
            $proxys     = $_POST['select_rows'];
            foreach( $proxys as $v )
            {
                if( FALSE == (bool)$oUser->isParent($v, $iUserId) )
                {
                    sysMsg( "没有操作权限", 2 );
                }
            }
            $oUserAdmin = A::singleton("model_useradminproxy");
            if( FALSE == ($oUserAdmin->insert($adminuser, $proxys)) )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink[0] = array('url'=>url('useradmin','adminproxy'),'title'=>'分配代理');
                sysMsg( "操作成功", 1, $aLink );
            }
        }
    }
     */
}
?>