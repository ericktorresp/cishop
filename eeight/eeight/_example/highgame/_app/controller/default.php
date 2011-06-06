<?php
/**
 * 文件 : /_app/controller/default.php
 * 功能 : 控制器 - 游戏平台登陆后主体框架
 * 
 *  - actionIndex()     主体框架载入
 *  - actionMenu()      左侧菜单部分
 *  - actionStart()     欢迎引导界面（公告）
 * 
 *  登录成功后注册的 SESSION 变量:
 *     - $_SESSION["userid"]      =   用户ID
 *     - $_SESSION["username"]    =   用户名
 *  
 * TODO: 对静态页面进行缓存支持,提高效率
 * 
 * @author     Floyd
 * @version    1.0.0
 * @package    highgame
 */

class controller_default extends basecontroller
{
    /**
     * 代理平台主窗口
     * URL = ./?controller=default&action=index
     */
    function actionIndex()
    {
    	//获取用户频道信息
        $oChannel = new model_userchannel();
        $aChannel = $oChannel->getUserChannelList( $_SESSION['userid'] );
        $iGroupId = 4;//用户
        if( $_SESSION['usertype'] == 1 )
        {
            $iGroupId = 3;//代理
        }
        $oUser = new model_user();
        if( $oUser->isTopProxy($_SESSION['userid']) || $_SESSION['usertype']==2 )
        {
            $iGroupId = 1;//总代或者总代管理员
        }
        foreach( $aChannel as $kk=>$chanel )
        {
            foreach( $chanel as $k=>$v )
            {
                if( !in_array( $iGroupId, explode(",",$v['usergroups']) ) )
                {
                    unset($aChannel[$kk][$k]);
                }
            }
        }

        $oNotice = new model_notices();
        $aResult = $oNotice->noticesgetOne( 0, " `subject` ", SYS_CHANNELID );
        $oConfig = new model_config();
        $sGameVersion = $oConfig->getConfigs('gamevserion');//程序版本号
        $GLOBALS['oView']->assign( 'gameversion',  $sGameVersion);
        $GLOBALS['oView']->assign( 'notice', $aResult );
        $GLOBALS['oView']->assign( 'channelid', SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'groupid',    $iGroupId );
        $GLOBALS['oView']->assign( 'username', isset($_SESSION['nickname']) ? $_SESSION['nickname'] : '');
        $GLOBALS['oView']->assign( 'istester',   $_SESSION['istester'] );
        $GLOBALS['oView']->assign( 'channel', $aChannel );
        $GLOBALS['oView']->display( 'default_center.html' );
        exit;
    }



    /**
     * 管理员的菜单
     * URL = controller=default&action=menu
     * @author james 090515 10:43
     */
    function actionMenu()
    {
    	if( !empty($_POST['flag']) && $_POST['flag'] == "getmoney" )
    	{//刷新用户可用余额信息
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
    		$oUserFund = new model_userfund();
    		$mResult = $oUserFund->getUserAvailableBalance( $iUserId );
    		if( FALSE === $mResult )
    		{
    			echo "error";
    			exit;
    		}
    		echo $mResult;
    		exit;
    	}
    	define( 'USER_ADMIN_BANK',     0x0001 ); // 可看银行余额
        define( 'USER_ADMIN_STAR',     0x0002 ); // 可看星级
        $oUserMenu = new model_usermenu();
        $iUserId   = intval($_SESSION['userid']);
        $iUserType = intval($_SESSION['usertype']);

        $aUserMenu = $oUserMenu->getUserMenus( $iUserId, $iUserType, 0, " AND `ismenu`='1' " );

        if( empty($aUserMenu) )
        {
            sysMsg( "没有操作权限", 0, "", 'top' );
        }
        $iView     = isset( $aUserMenu['viewrights'] ) ? $aUserMenu['viewrights'] : 3;
        $aUserMenu = $aUserMenu['menus'];
        //获取用户信息
        $oUser     = new model_user();
        $aUserData = $oUser->getUserLeftInfo( $iUserId, $iUserType, " ut.`parentid`,ut.`userrank`,uf.`availablebalance` ");
        if( empty($aUserData) )
        {
        	sysMsg( "频道没有激活", 0, "", 'top' );
        }
        $aView = array();
        if( $iView & USER_ADMIN_BANK )
        {
            $aView['bank'] = 1;
        }
        if( $iView & USER_ADMIN_STAR )
        {
            $aView['star'] = 1;
        }
        $aUserData['nickname'] = $_SESSION['nickname'];
        $GLOBALS['oView']->assign( 'lang', $GLOBALS['_LANG'] );
        $GLOBALS['oView']->assign( 'viewrights', $aView );
        $GLOBALS['oView']->assign( 'menus', $aUserMenu );
        $GLOBALS['oView']->assign( 'user', $aUserData );
        $GLOBALS['oView']->display('default_menu.html');
        exit;
    }



    /**
     * 管理员的欢迎使用页面
     * URL = c
     * @author james 090515 10:43
     */
    function actionStart()
    {
        $oNotice = new model_notices();
        $aResult = $oNotice->noticesgetOne( 0, " `subject`,`content`,`sendtime` ", SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'notice', $aResult );
        $GLOBALS["oView"]->assign("ur_here","欢迎登录");
        $oNotice->assignSysInfo();
        $GLOBALS['oView']->display('default_start.html');
        exit;
    }
    
    /**
     * URL =  controller=default&action=Top
     * @author jack 
     */
    function actionTop()
    {
        //获取用户频道信息
        $oChannel = new model_userchannel();
        $aChannel = $oChannel->getUserChannelList( $_SESSION['userid'] );
        $iGroupId = 4;//用户
        if( $_SESSION['usertype'] == 1 )
        {
            $iGroupId = 3;//代理
        }
        $oUser = new model_user();
        if( $oUser->isTopProxy($_SESSION['userid']) || $_SESSION['usertype']==2 )
        {
            $iGroupId = 1;//总代或者总代管理员
        }
        foreach( $aChannel as $kk=>$chanel )
        {
            foreach( $chanel as $k=>$v )
            {
                if( !in_array( $iGroupId, explode(",",$v['usergroups']) ) )
                {
                    unset($aChannel[$kk][$k]);
                }
            }
        }


    	$GLOBALS['oView']->assign( 'istester',   $_SESSION['istester'] );
        $GLOBALS['oView']->assign( 'channel', $aChannel );
    	$GLOBALS['oView']->display('default_top.html');
    }
    
    /**
     * 模板切换 利用api更新用户设置的模板风格
     * @author jack
     *
     */
    function actionChangeStyle()
    {
        if( !empty($_GET['skin']) )
        {
            $_SESSION['skins'] = $_GET['skin'];
            $aData = array('iUserId'=>$_SESSION['userid'], 'iSkin'=>$_SESSION['skins']);
            $oUpdateTem = new channelapi( 0, 'updateUserTemplate', TRUE );
            $oUpdateTem->setTimeOut(15);        // 整个过程的超时时间, 可能需要微调
            $oUpdateTem->sendRequest( $aData ); // 发送请求给调度器
            $aResult = $oUpdateTem->getDatas(); // 获取 API 返回的结果
            if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
            {
                sysMsg( "initapi error", 0 );
            }
            else
            {
                sysMsg( "", 0, array(array('title'=>'返回','url'=>'./')), 'top');
            }
        }
        sysMsg( 'param wrong', 2 );
    }
}
?>