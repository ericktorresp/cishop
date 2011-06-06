<?php
/**
 * 文件 : /_app/controller/useradmin.php
 * 功能 : 总代管理员的一些控制器
 *  
 * 类中所有的以 action 开头+首字母大写的英文, 为 "动作方法"
 * 例如 URL 访问: 
 *     http://www.xxx.com/?controller=default&action=abc
 *     default 是控制器名
 *     abc     是动作方法
 *     定义动作方法函数的命名, 规则为 action+首字母大写的全英文字符串
 *        例: 为实现上例的 /?controller=default&action=abc 中的 abc 方法
 *            需要在类中定义 actionIndex() 函数
 *
 * 功能:
 *    -- actionGroupList       总代管理员分组列表
 *    -- actionUpdateGroup     修改管理员分组
 *    -- actionDeleteGroup     删除管理组
 *    -- actionAddGroup        增加管理分组
 *    -- actionRightsGroup     修改组别权限
 *    -- actionAdminList       总代管理员列表
 *    -- actionAdminAdd        增加管理员
 *    -- actionAdminName       修改管理员呢称
 *    -- actionAdminGroup      修改管理员所属分组
 *    -- actionAdminPass       总代修改管理员密码
 *    -- actionAdminDelete     删除管理员
 *    -- actionAdminProxy      给总代销售管理员分配代理
 *    -- actionUpdatePass      总代管理员修改自己的密码
 * 
 * @author    james
 * @version   1.1.0
 * @package   passport
 * 
 */

class controller_useradmin extends basecontroller 
{
    /**
     * 总代管理员分组列表
     */
    function actionGroupList()
    {
        $iUserId = $_SESSION['userid'];
        // 如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $oUser   = new model_user();
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            unset($oUser);
        }
        // 获取用户的管理员分组列表
        $oGroup = new model_proxygroup();
        $aData  = $oGroup->getListByUser( $iUserId );
        $GLOBALS['oView']->assign( 'groups', $aData );
        $GLOBALS['oView']->assign( 'ur_here','组别列表');
        $oGroup->assignSysInfo();
        $GLOBALS['oView']->display( 'useradmin_grouplist.html' );
        exit;
    }



    /**
     * 修改管理员分组
     */
    function actionUpdateGroup()
    {
        if( empty($_REQUEST['gid']) )
        { // 没有参数则返回上一页
            sysMsg( "非法操作", 2 );
        }
        $iGroupid = intval($_REQUEST['gid']);
        $iUserId  = $_SESSION['userid'];
        // 如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $oUser   = new model_user();
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            unset($oUser);
        }
        $oGroup   = new model_proxygroup();
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
//   temp_louis         $aGroup   = $oGroup->getById( $iGroupid, array('groupname') );
			$aGroup   = $oGroup->getById( $iGroupid, array('p.groupname') );
            $GLOBALS['oView']->assign( 'groupid', $iGroupid );
            $GLOBALS['oView']->assign( 'groupname', $aGroup['groupname'] );
            $GLOBALS['oView']->assign( 'ur_here','修改组别');
            $oGroup->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_updategroup.html' );
            exit;
        }
    }



    /**
     * 删除管理组
     */
    function actionDeleteGroup()
    {
        $iUserId = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $oUser   = new model_user();
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            unset($oUser);
        }
        if( isset($_GET['gid']) && is_numeric($_GET['gid']) )
        {//单个删除
            $oGroup  = new model_proxygroup();
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
            $aLink = array( array('url'=>url('useradmin','grouplist'),
                                        'title'=>'管理员分组列表') );
            sysMsg( "删除成功", 1, $aLink );
        }
        if( isset($_POST['flag']) && $_POST['flag']=='delete' )
        {//批量删除
            if( empty($_POST['select_rows']) || !is_array($_POST['select_rows']) )
            {
                redirect( url('useradmin', 'grouplist') );
                exit;
            }
            $oGroup  = new model_proxygroup();
            if( TRUE !== $oGroup->isPermitProxy( $_POST['select_rows'], $iUserId ) )
            {
                sysMsg( "没有权限", 2 );
            }
            $mResult = $oGroup->deleteByUser( $iUserId, implode(',', $_POST['select_rows']) );
            if( empty($mResult) )
            {
                sysMsg( "删除失败，系统默认组不能删除", 2 );
            }
            elseif( $mResult === -1 )
            {
                sysMsg( "删除失败，所选组下还有用户，请先转移用户到其他组", 2 );
            }
            $aLink = array( array('url'=>url('useradmin','grouplist'),
                                        'title'=>'管理员分组列表') );
            sysMsg( "删除成功", 1, $aLink );
        }
        redirect( url('useradmin', 'grouplist') );
        exit;
    }



    /**
     * 增加管理分组
     */
    function actionAddGroup()
    {
        if( empty($_POST['flag']) || $_POST['flag']!='insert' )
        {
            $oUser   = new model_user();
            $GLOBALS['oView']->assign( 'ur_here','增加组别');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_addgroup.html', 'proxygroupadd' );
            exit;
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
                $oUser   = new model_user();
                $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
                if( empty($iUserId) )
                {
                    sysMsg( "操作失败", 2 );
                }
                unset($oUser);
            }
            $oGroup  = new model_proxygroup();
            $mResult = $oGroup->insert( $_POST['groupname'], $iUserId, '', 100, '', true );
            if( empty($mResult) )
            {
                sysMsg( "操作失败", 2 );
            }
            $aLink = array( array('url' => url( 'useradmin', 'grouplist' ),
                                        'title' => '管理员分组列表') );
            sysMsg( "操作成功", 1, $aLink );
        }
    }



    /**
     * 修改组别权限
     */
    function actionRightsGroup()
    {
        define( 'USER_ADMIN_BANK',     0x0001 ); // 可看总代银行余额
        define( 'USER_ADMIN_STAR',     0x0002 ); // 可看总代星级
        define( 'USER_ADMIN_ALLOWADD', 0x0004 ); // 可看总代开户数额

        if( empty($_REQUEST['gid']) )
        {//没有参数则返回上一页
            sysMsg( "操作失败", 2 );
        }
        $iUserId = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $oUser   = new model_user();
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            unset($oUser);
        }
        $iGroupid   = intval($_REQUEST['gid']);
        $oGroup     = new model_proxygroup();
        if( TRUE !== $oGroup->isPermitProxy( $iGroupid, $iUserId ) )
        {
            sysMsg( "没有权限", 2 );
        }
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            //获取总代自己所有的权限菜单列表
            $oMenus = new model_usermenu();
            $aData  = $oMenus->getUserMenus( $iUserId, 1, 0, FALSE );
            if( empty($aData) )
            {//获取菜单失败
                sysMsg( "获取权限菜单出错", 2 );
            }
            
            $aToData     = array();
            $aMiddleData = array();
            $aLastData   = array();
            $iTempCount  = count($aData) - 1;
            for( $i=0; $i<$iTempCount; $i++ )
            {
                if( empty($aData[$i]['parentid']) && $aData[$i]['menuid']!=1 )
                {
                    $aToData[$aData[$i]['menuid']] = $aData[$i];
                }
                elseif( array_key_exists($aData[$i]['parentid'], $aToData) )
                {
                    $aMiddleData[$aData[$i]['parentid']][$aData[$i]['menuid']] = $aData[$i];
                }
                else
                {
                    $aLastData[$aData[$i]['parentid']][] = $aData[$i];
                }
            }
            unset($aData);
            $GLOBALS['oView']->assign( 'data1', $aToData );
            $GLOBALS['oView']->assign( 'data2', $aMiddleData );
            $GLOBALS['oView']->assign( 'data3', $aLastData );
            //获取该组的已经有的权限
// temp_louis           $aData  = $oGroup->getById( $iGroupid, array( 'menustrs', 'issales', 'viewrights', 'groupname' ) );
            $aData  = $oGroup->getById( $iGroupid, array( 'a.menustrs', 'p.issales', 'p.viewrights', 'p.groupname' ) );
            if( empty($aData) )
            {
                sysMsg( "操作错误", 2 );
            }
            if( empty($aData['menustrs']) )
            {
                $aMenus = array();
            }
            else
            {
                $temp_menus = explode( ',', $aData['menustrs'] );
                foreach( $temp_menus as $v )
                {
                    $aMenus[$v] = "1";
                }
                unset($temp_menus);
            }
            /*if( $aData['issales'] == 1 )
            {//指定为销售
                $aMenus['other'] = 1;
            }*/
            if( $aData['viewrights'] > 0 )
            {
                $aMenus['other'] = 1;
            }
            if( $aData['viewrights'] & USER_ADMIN_BANK )
            {
                $aMenus['isbank'] = 1;
            }
            if( $aData['viewrights'] & USER_ADMIN_STAR )
            {
                $aMenus['isstar'] = 1;
            }
            if( $aData['viewrights'] & USER_ADMIN_ALLOWADD )
            {
                $aMenus['isadd'] = 1;
            }
            //$aMenus['issales'] = $aData['issales'];
            $GLOBALS['oView']->assign( 'menus', $aMenus );
            $GLOBALS['oView']->assign( 'groupid', $iGroupid );
            $GLOBALS['oView']->assign( 'groupname', $aData['groupname'] );
            $GLOBALS['oView']->assign( 'ur_here','修改组别权限');
            $oGroup->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_rightsgroup.html' );
            exit;
        }
        else 
        {//修改权限进数据库
            if( empty($_POST['menustr']) || !is_array($_POST['menustr']) )
            {
                $aMenustrs = '';
            }
            else
            {
                $aMenustrs = implode( ',', $_POST['menustr'] );
            }
            //$iIsSales    = empty($_POST['issale']) ? 0 : 1;
            $iIsSales = 0;
            $iIsBank     = empty($_POST['isbank']) ? 0 : 1;
            $iIsStar     = empty($_POST['isstar']) ? 0 : 2;
            $iIsAdd	     = empty($_POST['isadd'])  ? 0 : 4;
            $iViewRigths = $iIsBank+$iIsStar+$iIsAdd;
            $mResult = $oGroup->updateByUser( $iUserId, $iGroupid, 
                                    array('menustrs'=>$aMenustrs,'issales'=>$iIsSales,'viewrights'=>$iViewRigths) );
            if( empty($mResult) )
            {//修改失败
                sysMsg( "修改失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('useradmin','grouplist'),
                                        'title'=>'管理员分组列表') );
                sysMsg( "修改成功", 1, $aLink );
            }
        }
    }



    /**
     * 总代管理员列表
     */
    function actionAdminList()
    {
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
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
        $aData  = $oUser->getAdminList( $iUserId, " ORDER BY u.`username` ASC " );
        $GLOBALS['oView']->assign( 'users', $aData );
        $GLOBALS['oView']->assign( 'ur_here','管理员列表');
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display( 'useradmin_adminlist.html' );
        exit;
    }



    /**
     * 增加管理员
     */
    function actionAdminAdd()
    {
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        if( empty($_POST['flag']) || $_POST['flag']!='insert' )
        {
            $oGroup = new model_proxygroup();
            $aData  = $oGroup->getListByUser( $iUserId );
            $GLOBALS['oView']->assign( 'groups', $aData );
            $GLOBALS['oView']->assign( 'ur_here','增加用户');
            $oGroup->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_adminadd.html' );
            exit;
        }
        else 
        {
            //数据检查
            if( empty($_POST['groupid']) || !is_numeric($_POST['groupid']) )
            {
                sysMsg( "请选择管理员组别" );
            }
            if( empty($_POST['username']) || empty($_POST['nickname']) 
                || empty($_POST['userpass']) || empty($_POST['confimpass']) )
            {
                sysMsg( "请把资料填写完整" );
            }
            if( $_POST['userpass']!=$_POST['confimpass'] )
            {
                sysMsg( "密码和确认密码不一样" );
            }
            //用户名，密码，呢称规则检查
            if( FALSE == model_user::checkUserName($_POST['username']) )
            {//检查用户名合法性
                sysMsg( "登陆帐户名不合法" );
            }
            if( FALSE == model_user::checkUserPass($_POST['userpass']) )
            {//检查密码合法性
                sysMsg( "登陆密码不合法" );
            }
            if( FALSE == model_user::checkNickName($_POST['nickname']) )
            {//检查呢称合法性
                sysMsg( "昵称不合法" );
            }
            $aData = array(
                            'username'      => $_POST['username'],
                            'loginpwd'      => $_POST['userpass'],
                            'nickname'      => $_POST['nickname'],
                            'usertype'      => 2
                );
            $mResult = $oUser->insertUser( $aData, intval($_POST['groupid']), $iUserId, FALSE  );
            if( empty($mResult) )
            {
                sysMsg( "增加管理员失败", 2 );
            }
            if( $mResult == -1 )
            {
                sysMsg( "该帐户名已经存在", 2 );
            }
            $aLink = array( array('url' => url( 'useradmin', 'adminlist' ),
                                        'title' => '管理员列表') );
            sysMsg( "增加管理员成功", 1, $aLink );
        }
    }



    /**
     * 修改管理员呢称
     */
    function actionAdminName()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {//没有参数则返回上一页
            sysMsg( "操作失败", 2 );
        }
        $uid     = intval($_REQUEST['uid']);
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        if( TRUE !== $oUser->isParent( $uid, $iUserId ) )
        {
            sysMsg( "没有权限", 2 );
        }
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            if( FALSE == ($aData=$oUser->getUserExtentdInfo($uid, 2)) )
            {
                sysMsg( "操作失败，连接超时", 2 );
            }
            $oUser->assignSysInfo();
            unset($oUser);
            $GLOBALS['oView']->assign( 'data', $aData );
            $GLOBALS['oView']->assign( 'ur_here','修改管理员昵称');
            $GLOBALS['oView']->display( 'useradmin_adminname.html' );
            exit;
        }
        else 
        {
            if( empty($_POST['nickname']) )
            {
                sysMsg("昵称不能为空");
            }
            if( FALSE == model_user::checkNickName($_POST['nickname']) )
            {//检查昵称合法性
                sysMsg( "昵称不合法" );
            }
            $nickname = $_POST['nickname'];
            $mResult  = $oUser->updateUser( $uid, array('nickname'=>$nickname) );
            if( empty($mResult) )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink = array( array('url' => url( 'useradmin', 'adminlist' ),
                                        'title' => '管理员列表') );
                sysMsg( "操作成功", 1, $aLink );
            }
        }
    }



    /**
     * 修改管理员所属分组
     */
    function actionAdminGroup()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {//没有参数则返回上一页
            sysMsg("操作失败",2);
        }
        $uid     = intval($_REQUEST['uid']);
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        if( TRUE !== $oUser->isParent( $uid, $iUserId ) )
        {
            sysMsg( "没有权限", 2 );
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
            $oGroup = new model_proxygroup();
            if( FALSE == ($groupdata = $oGroup->getListByUser( $iUserId )) )
            {
                sysMsg( "操作失败，连接超时", 2 );
            }
            $GLOBALS['oView']->assign( 'groups', $groupdata );
            $GLOBALS['oView']->assign( 'ur_here','修改管理员分组');
            $oGroup->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_admingroup.html' );
            exit;
        }
        else 
        {
            if( empty($_POST['groupid']) || !is_numeric($_POST['groupid']) )
            {
                sysMsg( "请选择用户组" );
            }
            $groupid = intval($_POST['groupid']);
            $oGroup  = new model_proxygroup();
	        if( TRUE !== $oGroup->isPermitProxy( $groupid, $iUserId ) )
	        {
	            sysMsg( "没有权限", 2 );
	        }
            $channel = new model_userchannel();
            /** temp_louis **/
            $iIdList = "";
            $oChannel = A::singleton("model_userchannel");
	        $aChannel = $oChannel->getUserChannelList( $uid );
	        if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) ){
	        	foreach ($aChannel[0] as $k => $v){
		        	$iIdList .= $v['id'] . ',';
		        }
	        }
	        $iIdList = substr(SYS_CHANNELID . ',' . $iIdList, 0, -1);
            /** temp_louis **/
            $mResult = $channel->update(array('groupid'=>$groupid), " `userid`='".$uid."'");
            if( empty($mResult) )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('useradmin','adminlist'),
                                        'title'=>'管理员列表') );
                sysMsg( "操作成功", 1, $aLink );
            }
        }
    }



    /**
     * 总代修改管理员密码
     */
    function actionAdminPass()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {//没有参数则返回上一页
            sysMsg( "操作失败", 2 );
        }
        $uid     = intval($_REQUEST['uid']);
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        if( FALSE == (bool)$oUser->isParent($uid, $iUserId) )
        {
            sysMsg( "没有操作权限", 2 );
        }
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            if( FALSE == ($aData=$oUser->getUserExtentdInfo($uid, 2)) )
            {
                sysMsg( "操作失败，连接超时", 2 );
            }
            $oUser->assignSysInfo();
            unset($oUser);
            $GLOBALS['oView']->assign( 'data', $aData );
            $GLOBALS['oView']->assign( 'ur_here','修改管理员密码');          
            $GLOBALS['oView']->display( 'useradmin_adminpass.html' );
            exit;
        }
        else 
        {
            if( empty($_POST['userpass']) || empty($_POST['confimpass']) )
            {
                sysMsg( "密码和确认密码不能为空" );
            }
            if( $_POST['userpass'] != $_POST['confimpass'] )
            {
                sysMsg( "密码和确认密码不相同" );
            }
            if( FALSE == model_user::checkUserPass($_POST['userpass']) )
            {//检查密码合法性
                sysMsg( "登陆密码不合法" );
            }
            $userpass = md5( $_POST['userpass'] );
            $mResult = $oUser->updateUser( $uid, array('loginpwd'=>$userpass) );
            if( $mResult === FALSE )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('useradmin','adminlist'),
                                        'title'=>'管理员列表') );
                sysMsg( "操作成功", 1, $aLink );
            }
        }
    }



    /**
     * 删除管理员
     */
    function actionAdminDelete()
    {
        //获取总代ID
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        //unset($oUser);
        if( isset($_GET['uid']) && is_numeric($_GET['uid']) )
        {//单个删除
            $uid     = $_GET['uid'];
	        if( FALSE == (bool)$oUser->isParent($uid, $iUserId) )
	        {
	            sysMsg( "没有操作权限", 2 );
	        }
            $mResult = $oUser->deleteUser( $uid, 2 );
            if( empty($mResult) )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('useradmin','adminlist'),
                                        'title'=>'管理员列表') );
                sysMsg( "操作成功", 1, $aLink );
            }
        }
        if( isset($_POST['flag']) && $_POST['flag']=='delete' )
        {//批量删除
            if( empty($_POST['select_rows']) || !is_array($_POST['select_rows']) )
            {
                redirect( url('useradmin','adminlist') );
                exit;
            }
            foreach( $_POST['select_rows'] as $v )
            {
	            if( FALSE == (bool)$oUser->isParent(intval($v), $iUserId) )
	            {
	                sysMsg( "没有操作权限", 2 );
	            }
            }
            $mResult = $oUser->deleteUser( $_POST['select_rows'], 2 );
            if( empty($mResult) )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('useradmin','adminlist'),
                                        'title'=>'管理员列表') );
                sysMsg( "操作成功", 1, $aLink );
            }
        }
        redirect( url('useradmin','adminlist') );
        exit;
    }



    /**
     * 给总代销售管理员分配代理
     */
    function actionAdminProxy()
    {
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
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
            $oUserAdmin = new model_useradminproxy();
            $auseradmin = $oUserAdmin->getAdminSaleList( $iUserId, "", " ORDER BY ut.username" );
            $aproxy     = $oUserAdmin->getAdminProxyList( $iUserId, "", " ORDER BY ut.username" );
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
            $GLOBALS['oView']->assign( 'proxys', $aproxy );
            $GLOBALS['oView']->assign( 'ur_here','分配代理');
            $oUserAdmin->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_amdinproxy.html' );
            exit;
        }
        else
        {
            if( !is_numeric($_POST['adminuser']) )
            {
                sysMsg( "非法操作", 2 );
            }
            $adminuser = intval($_POST['adminuser']);
            // 2/23/2010 YH20100223-02            
            if(( FALSE == (bool)$oUser->isParent($adminuser, $iUserId) ) && ($adminuser != 0) )
            {
                sysMsg( "没有操作权限", 2 );
            }
            if( empty($_POST['select_rows']) )
            {
                sysMsg( "操作失败1", 2 );
            }
            $proxys     = $_POST['select_rows'];
            foreach( $proxys as $v )
            {
	            if( FALSE == (bool)$oUser->isParent($v, $iUserId) )
	            {
	                sysMsg( "没有操作权限", 2 );
	            }
            }
            $oUserAdmin = new model_useradminproxy();
            if( FALSE == ($oUserAdmin->insert($adminuser, $proxys)) )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('useradmin','adminproxy'),
                                        'title'=>'分配代理') );
                sysMsg( "操作成功", 1, $aLink );
            }
        }
    }



    /**
     * 总代管理员修改自己的密码
     */
    function actionUpdatePass()
    {
        $uid = intval($_SESSION['userid']);
        if( $_SESSION['usertype'] != 2 )
        {
            sysMsg( "没有操作权限", 2 );
        }
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            $oUser = new model_user();
            if( FALSE == ($aData=$oUser->getUserExtentdInfo($uid, $_SESSION['usertype'])) )
            {
                sysMsg( "操作失败，连接超时", 2 );
            }
            unset($oUser);
            $GLOBALS['oView']->assign( 'data', $aData );
            $GLOBALS['oView']->assign( 'ur_here','修改自己的密码');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( 'useradmin_updatepass.html' );
            exit;
        }
        else 
        {
            if( empty($_POST['userpass']) || empty($_POST['confimpass']) )
            {
                sysMsg( "密码和确认密码不能为空" );
            }
            if( $_POST['userpass'] != $_POST['confimpass'] )
            {
                sysMsg( "密码和确认密码不相同" );
            }
            if( FALSE == model_user::checkUserPass($_POST['userpass']) )
            {//检查密码合法性
                sysMsg( "登陆密码不合法" );
            }
            $userpass = md5( $_POST['userpass'] );
            $mResult  = $oUser->updateUser( $uid, array('loginpwd'=>$userpass) );
            if( $mResult === FALSE )
            {
                sysMsg( "操作失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('useradmin','updatepass'),
                                        'title'=>'修改密码') );
                sysMsg( "操作成功", 1, $aLink );
            }
        }
    }
}
?>