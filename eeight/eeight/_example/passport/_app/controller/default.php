<?php
/**
 * 文件 : /_app/controller/default.php
 * 功能 : 默认的控制器处理
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
 *     -- actionIndex          默认执行方法
 *     -- actionLogin          用户登陆
 *     -- actionLogOut         退出登陆
 *     -- actionMain           银行大厅主框架
 *     -- actionUserMsg        用户消息
 *     -- actionWelcome        欢迎界面
 *     -- actionActivityList   用户活动列表
 *     --
 *
 * @author    james
 * @version   1.2.0
 * @package   passport
 *
 */

class controller_default extends basecontroller
{
	/**
	 * 默认执行方法
	 */
	function actionIndex()
	{
		if( empty($_SESSION['userid']) )
		{
			//TODO:高低频并行COOKIE同步登陆
			if( !empty($_COOKIE['iUserId']) && !empty($_COOKIE['sUserName']) && !empty($_COOKIE['sPassword']) )
			{
				$oUser   = A::singleton('model_user');
				$mResult = $oUser->gdUserLogin( $_COOKIE['iUserId'], $_COOKIE['sUserName'], $_COOKIE['sPassword'] );
				if( $mResult === TRUE )
				{
					//echo "同步登陆成功";
					if( !empty($_REQUEST['tzurl']) )
					{
						$sTempUrl = $_REQUEST['tzurl'] == 'yx' ? '/lowgame/' : '/lowproxy/';
						header("location: ".$sTempUrl);
						exit;
					}
					redirect( url("default","main"), 0, TRUE );
				}
				else
				{
					//echo "同步登陆失败";
					header("location: /game/");
					exit;
				}
			}

			//$sUrl  = empty($_COOKIE['_GPCURL_']) ? "http://www.google.com" : "http://".$_COOKIE['_GPCURL_']."/logout.php";
			//header("location: /game/");
			redirect( url("default","login"), 0, TRUE );
			EXIT;
		}
		redirect( url("default","main"), 0, TRUE );
	}



	/**
	 * 1, 显示用户登陆界面
	 * 2, 处理用户登陆      ( if post data exists )
	 */
	function actionLogin()
	{
		//$sUrl  = empty($_COOKIE['_GPCURL_']) ? "http://www.google.com" : "http://".$_COOKIE['_GPCURL_']."/logout.php";
		//header("location: /game/");exit;
		if( !isset($_POST['flag']) || $_POST['flag']!='login' )
		{ // 登陆界面
			$GLOBALS['oView']->assign( 'usevalid', mt_rand(1,9) );
			$GLOBALS['oView']->display( "default_login.html", 'login' );
			EXIT;
		}
		else
		{ // 登陆过程

			// 5/31/2010  $_POST['validcode'] = md5($_SESSION['validateCode'])
			if( empty($_POST['validcode']) || empty($_SESSION['validateCode'])
			|| strtolower($_POST['validcode']) != strtolower( md5($_SESSION['validateCode']) ) )
			{
				sysMsg( "验证码错误!" ); // TODO: 生产版本需取消此行. FOR 维护部压力测试
			}
			if( empty($_POST['username']) || empty($_POST['loginpass']) )
			{
				sysMsg( "用户名和密码不能为空" );
			}
			/* @var $oUser model_user */
			$oUser = A::singleton('model_user');


			// 根据用户登陆名, 检查是否符合域名检测
			if( FALSE === $oUser->checkUserDomain( $_POST['username'], isset($_SERVER['HTTP_HOST']) ?
			$_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] ) )
			{ // 使用了非自身总代的域名, 或用户名不存在, 则程序中断
				@header("Location: http://www.google.com/");
				EXIT;
			}
			// 普通登陆
			$mData = $oUser->userLogin( $_POST['username'], $_POST['loginpass'] );
			if( $mData == FALSE || $mData < -3 )
			{
				sysMsg( "登陆失败, 请重新登陆", 2 );
			}
			if( $mData == -1 )
			{ // 错误的用户名和密码
				sysMsg( "用户名和密码错误, 请重新登陆", 2 );
			}
			if( $mData == -2 )
			{ // 用户已被删除
				sysMsg( "用户名不存在或者已被删除", 2 );
			}
			if( $mData == -3 )
			{ // 已被冻结，不能登陆
				sysMsg( "用户已被冻结, 请联系管理员", 2 );
			}

			// 写登陆日志
			/* @var $oUserLog model_userlog */
			$oUserLog = A::singleton('model_userlog');
			$aLogdata = array(
                            'userid'        => $mData['userid'],
                            'controller'    => 'user',
                            'actioner'      => 'login',
                            'title'         => '用户 ['.$mData['username'].'] 成功登陆',
                            'content'       => '用户 ['.$mData['username'].'] 成功登陆'
                            );
                            $oUserLog->insert( $aLogdata );
                            unset($oUserLog);
                             
                            // 5/31/2010 新密码  md5(md5($_SESSION['validateCode']).$_POST['loginpass'] )
                            // md5($_POST['validcode'].$_POST['loginpass'])
                            if( md5(md5($_SESSION['validateCode']).$mData['loginpwd']) == $_POST['loginpass'] )
                            //if( $mData['loginpwd'] == md5($_POST['loginpass']) )
                            { // 正常登陆
                            	// 获取皮肤界面 Skins
                            	/* @var $oUserSkin model_userskins */
                            	$aTranfer = array('iUserId'=>$mData['userid']);
                            	$oUserSkin = new channelapi( 0, 'getUserTemplate', FALSE );
                            	$oUserSkin->setTimeOut(15);           // 整个转账过程的超时时间, 可能需要微调
                            	$oUserSkin->sendRequest( $aTranfer );  // 发送转账请求给调度器
                            	$mAnswers = $oUserSkin->getDatas();   // 获取转账 API 返回的结果
                            	if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
                            	{
                            		// 10/18/2010 $_SESSION['skins'] = 'default';
                            		$_SESSION['skins'] = 'new';
                            	}
                            	else
                            	{
                            		$_SESSION['skins'] = $mAnswers['data']['skin'];
                            	}
                            	unset($oUserSkin);
                            	$_SESSION['logintype'] = 'normal';
                            	if( $mData['lastip'] != getRealIP() && $mData['lastip'] != '0.0.0.0' )
                            	{ // 最后一次登陆IP和本次不同
                            		$aLinks = array();
                            		$sMsg = "您本次登陆的IP和上次不同\\n您上次登陆的时间是\\n" . $mData['lasttime'];
                            		//                    $aLinks[0] = array( 'url'=> url('default', 'declare') );
                            		$aLinks[0] = array( 'url'=> url('default', 'main') );
                            		sysMsg( $sMsg, 0, $aLinks, 'top' );
                            	}

                            	//                redirect( url('default', 'declare') );
                            	redirect( url('default', 'main') );
                            	EXIT;
                            }
                            // 5/31/2010
                            if( md5(md5($_SESSION['validateCode']).$mData['securitypwd']) == $_POST['loginpass'] )
                            //if( $mData['securitypwd'] == md5($_POST['loginpass']) )
                            {//资金密码登陆
                            	// 获取皮肤界面 Skins
                            	/* @var $oUserSkin model_userskins */
                            	$aTranfer = array('iUserId'=>$mData['userid']);
                            	$oUserSkin = new channelapi( 0, 'getUserTemplate', FALSE );
                            	$oUserSkin->setTimeOut(15);           // 整个转账过程的超时时间, 可能需要微调
                            	$oUserSkin->sendRequest( $aTranfer );  // 发送转账请求给调度器
                            	$mAnswers = $oUserSkin->getDatas();   // 获取转账 API 返回的结果
                            	if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
                            	{
                            		// 10/18/2010 $_SESSION['skins'] = 'default';
                            		$_SESSION['skins'] = 'new';
                            	}
                            	else
                            	{
                            		$_SESSION['skins'] = $mAnswers['data']['skin'];
                            	}
                            	unset($oUserSkin);

                            	$_SESSION['logintype'] = 'security';
                            	if( $mData['lastip'] != getRealIP() && $mData['lastip'] != '0.0.0.0' )
                            	{//最后一次登陆IP和本次不同
                            		$aLinks = array();
                            		$sMsg = "你本次登陆的IP和上次不同\\n您上次登陆的时间是\\n" . $mData['lasttime'];
                            		$aLinks[0] = array( 'url'=> url('security', 'changeloginpass') );
                            		sysMsg( $sMsg, 0, $aLinks, 'top' );
                            	}
                            	redirect( url('security', 'changeloginpass') );
                            	exit;

                            }

		}

	}


	// 用户登出&注销
	function actionLogOut()
	{
		//$sUrl  = empty($_COOKIE['_GPCURL_']) ? "http://www.google.com" : "http://".$_COOKIE['_GPCURL_']."/logout.php";
		$oUser = new model_user();
		$oUser->loginOut();
		setcookie( 'iUserId', '', 0 );
		setcookie( 'sUserName', '', 0 );
		setcookie( 'sPassword', '', 0 );
		//header("location: ".$sUrl);
		//exit;
		redirect( url('default', 'login') );
	}



	//免责任申明
	function actionDeclare()
	{
		if( !isset($_POST['flag']) || $_POST['flag']!='login' )
		{ // 登陆界面
			$GLOBALS['oView']->display( "default_declare.html", 'declare' );
			EXIT;
		}
		else
		{
			if( $_SESSION['logintype'] == "security" )
			{//资金密码登陆
				redirect( url('security', 'changeloginpass') );
				EXIT;
			}
			else
			{
				redirect( url('default', 'main') );
				EXIT;
			}
		}
	}

	// 银行大厅框架
	function actionMain()
	{
		// 1, 获取用户频道信息
		/* @var $oChannel model_userchannel */
		$oChannel = A::singleton('model_userchannel');
		$aChannel = $oChannel->getUserChannelList( $_SESSION['userid'] );
		$iGroupId = 4; // 用户
		if( $_SESSION['usertype'] == 1 )
		{
			$iGroupId = 3; // 代理
		}
		//unset($oChannel);

		// 2, 获取用户信息
		/* @var $oUser model_user */
		$oUser = A::singleton('model_user');

		if( $oUser->isTopProxy($_SESSION['userid']) || $_SESSION['usertype']==2 )
		{ // 总代不能查看星级, 不参与评星
			$iGroupId = 1; // 总代或者总代管理员
		}
		foreach( $aChannel as $kk => $chanel )
		{
			foreach( $chanel as $k => $v )
			{
				if( !in_array( $iGroupId, explode(",", $v['usergroups']) ) )
				{
					unset($aChannel[$kk][$k]);
				}
			}
		}

		$oNotice = A::singleton('model_notices');
		$aResult = $oNotice->getOne( 0, " `subject` " );
		$GLOBALS['oView']->assign( 'notice', $aResult );
		$GLOBALS['oView']->assign( 'channel',    $aChannel );
		//$GLOBALS['oView']->assign( 'gpdomain', $aGPdomain );
		$GLOBALS['oView']->assign( 'nickname',   $_SESSION['nickname'] );
		$GLOBALS['oView']->assign( 'istester',   $_SESSION['istester'] );
		$GLOBALS['oView']->assign( 'username',   $_SESSION['username'] );
		$GLOBALS['oView']->assign( 'usertypeflag',   $_SESSION['usertype'] );
		$GLOBALS['oView']->assign( 'groupid',    $iGroupId );
		$GLOBALS['oView']->assign( 'lang', $GLOBALS['_LANG'] );
		$GLOBALS['oView']->display( 'default_main.html' );
		EXIT;
	}



	// 左侧菜单
	function actionUserMenu()
	{
		if( !empty($_POST['flag']) && $_POST['flag'] == "getmoney" )
		{//刷新用户可用余额信息
			$iUserId = intval($_SESSION['userid']);
			/* @var $oUser model_user */
			$oUser   = A::singleton("model_user");
			// 如果为总代管理员，则当前用户调整到其总代ID
			if( $_SESSION['usertype'] == 2 )
			{
				$iUserId = $oUser->getTopProxyId( $iUserId );
				if( empty($iUserId) )
				{
					echo "error";
					exit;
				}
			}
			$aUserInfo = $oUser->getUserLeftInfo( $_SESSION['userid'], $_SESSION['usertype'] );
			if( empty($aUserInfo) )
			{
				echo "error";
				exit;
			}
			unset($oUser);
			//            $oUserFund = new model_userfund();
			//            $mResult = $oUserFund->getUserAvailableBalance( $iUserId );
			//            if( FALSE === $mResult )
			//            {
			//                echo "error";
			//                exit;
			//            }
			//            $aUserInfo['money'] = $mResult;
			echo json_encode($aUserInfo);
			exit;
		}
		define( 'USER_ADMIN_BANK',     0x0001 ); // 可看总代银行余额
		define( 'USER_ADMIN_STAR',     0x0002 ); // 可看总代星级
		define( 'USER_ADMIN_ALLOWADD', 0x0004 ); // 可看开户数额
		// 2, 获取用户信息
		/* @var $oUser model_user */
		$oUser = A::singleton('model_user');
		$aUserInfo = $oUser->getUserLeftInfo( $_SESSION['userid'], $_SESSION['usertype'] );
		if( empty($aUserInfo) )
		{
			$aLinks = array( array('url'=> url('default', 'login') ) );
			sysMsg( "载入个人信息失败, 请重新登陆", 0, $aLinks, 'top' );
		}

		//	在线客服处理 (屏蔽主菜单在线客服显示 帮助中心/在线客服)
		// 读取配置的在线客服菜单ID
		$oConfigi  = new model_config();
		$iCutMenuid = 0;
		$iMenuid  = $oConfigi->getConfigs('livechat_menuid');
		if ( intval($iMenuid) > 0)
		{
			//去除在线客服菜单
			$iCutMenuid = $iMenuid;
		}

		// 3, 获取用户菜单权限
		/* @var $oMenus model_usermenu */
		$oMenus     = A::singleton('model_usermenu');
		$aMenuData  = $oMenus->getUserMenus( $_SESSION['userid'], $_SESSION['usertype'] );
		$iView      = isset( $aMenuData['viewrights'] ) ? $aMenuData['viewrights'] : 7;
		$iView      = $_SESSION['usertype'] == 0 ? 3 : $iView;
		$aTopMenus  = array();
		$aSonMenus  = array();
		if( isset( $aMenuData['viewrights'] ) )
		{
			unset( $aMenuData['viewrights'] );
		}
		$bLoad = false;
		$bWithdraw = false;
		foreach( $aMenuData as $k => $v )
		{
			// 不显示在线客服菜单
			if ( $v['menuid'] == $iCutMenuid && $iCutMenuid > 0 ) continue;
				
			if( intval($v['parentid']) == 0 )
			{ // 顶级菜单
				$aTopMenus[$v['menuid']] = $v;
			}
			elseif( array_key_exists($v['parentid'],$aTopMenus) )
			{ // 二级菜单
				$aSonMenus[$v['parentid']][] = $v;
				// 检查用户是否有在线提现权限
				if ($v['controller'] == "security" && $v['actioner'] == "platwithdraw"){
					$bWithdraw = true;
				}

				// 检查用户是否有email充值权限
				if ($v['controller'] == "emaildeposit" && $v['actioner'] == "emailload"){
					$bLoad = true;
				}
			}
			unset($aMenuData[$k]);
		}
		// 用户不能查看
		if( $_SESSION['usertype']!=0 )
		{
			$GLOBALS['oView']->assign( 'istop', 1 );
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
		if( $iView & USER_ADMIN_ALLOWADD )
		{
			$aView['useradd'] = 1;
		}
		if( $oUser->isTopProxy($_SESSION['userid']) || $_SESSION['usertype']==2 )
		{ // 总代不能查看星级, 不参与评星
			unset($aView['star']);
		}
		unset( $iView );

		$iUserId = intval($_SESSION['userid']);
		/* @var $oUser model_user */
		$oUser   = A::singleton("model_user");
		// 如果为总代管理员，则当前用户调整到其总代ID
		if( $_SESSION['usertype'] == 2 )
		{
			$iUserId = $oUser->getTopProxyId( $iUserId );
			if( empty($iUserId) )
			{
				echo "error";
				exit;
			}
		}

		$GLOBALS['oView']->assign( 'viewrights', $aView );
		$GLOBALS['oView']->assign( 'userinfo',   $aUserInfo );
		$GLOBALS['oView']->assign( 'username',   $_SESSION['username'] );
		$GLOBALS['oView']->assign( 'nickname',   $_SESSION['nickname'] );
		$GLOBALS['oView']->assign( 'topmenus',   $aTopMenus );
		$GLOBALS['oView']->assign( 'sonmenus',   $aSonMenus );
		$GLOBALS['oView']->assign( 'bwithdraw',   $bWithdraw );
		$GLOBALS['oView']->assign( 'bload',   $bLoad );
		$GLOBALS['oView']->assign( 'lang', $GLOBALS['_LANG'] );
		$GLOBALS['oView']->display( 'default_menus.html' );
		EXIT;
	}



	/**
	 * 用户短消息
	 */
	function actionUserMsg()
	{
		/* @var $oMsg model_message */
		$oMsg = A::singleton('model_message');
		if( empty($_GET['mid']) || !is_numeric($_GET['mid']) || intval($_GET['mid']) < 1 )
		{ // 消息列表
			$sAndWhere = " AND l.`deltime` IS NULL AND l.`readtime` IS NULL ORDER BY c.`sendtime` DESC ";
			$aMsgData  = $oMsg->getUserMessageList( $_SESSION['userid'], '', $sAndWhere );
			$GLOBALS['oView']->assign( 'msgs', $aMsgData );
			$GLOBALS['oView']->display( "default_messagelist.html" );
			EXIT;
		}
		else
		{ // 读取消息
			$iMid = intval( $_GET['mid'] );
			$sAndWhere = " AND l.`deltime` IS NULL AND l.`readtime` IS NULL ";
			$aMsgData  = $oMsg->getOneUserMessage( $iMid, $_SESSION['userid'], '', $sAndWhere );
			// 设置消息为以读
			$oMsg->setIsReaded( $iMid );
			$GLOBALS['oView']->assign( 'msg', $aMsgData );
			$GLOBALS['oView']->display( "default_message.html" );
			EXIT;
		}
	}



	/**
	 * 用户登陆后的欢迎界面 (公告)
	 */
	function actionWelcome()
	{
		/* @var $oNotice model_notices */
		$oNotice = A::singleton('model_notices');
		$aResult = $oNotice->getOne( 0, " `subject`,`content`,`sendtime` " );
		$GLOBALS['oView']->assign( 'notice', $aResult );
		$GLOBALS['oView']->display( "default_welcome.html" );
		EXIT;
	}

	// 用户活动列表
	function actionActivityList()
	{
		$this->actionActivity();
	}

	// 用户活动
	function actionActivity()
	{
		if( !empty($_POST['flag']) && $_POST['flag'] == "answer" )
		{ // 问题提交
			$iUserId     = ( isset($_SESSION["userid"]) && is_numeric($_SESSION["userid"]) ) ?
			intval($_SESSION["userid"]) : 0;
			$iActivityId = ( isset($_POST["actid"]) && is_numeric($_POST["actid"]) ) ? intval($_POST["actid"]) : 0;
			if( $iUserId == 0 || $iActivityId == 0 )
			{
				redirect( url('default', 'activity', array('id'=>$iActivityId)) );
			}
			$oActivityAnswer = new model_activityanswer();
			$iResult         = $oActivityAnswer->activityAnswerInsert( $iActivityId, $iUserId, $_POST );
			$aLinks[0]       = array( 'url'=> url('default', 'main') );
			switch ($iResult)
			{
				case -1:
					sysMsg( "非法操作", 2, $aLinks , 'self' );
					break;
				case -2:
					sysMsg( "非法操作", 2, $aLinks, 'self' );
					break;
				case -3:
					sysMsg( "非法操作", 2, $aLinks, 'self' );
					break;
				case -4:
					sysMsg( "活动尚未开始", 2, $aLinks, 'self' );
					break;
				case -5:
					sysMsg( "活动已经结束", 2, $aLinks, 'self' );
					break;
				case -6:
					sysMsg( "无权参加本次活动", 2, $aLinks, 'self');
					break;
				case -7:
					sysMsg( '您已经答题,请不要重复答题', 2, $aLinks, 'self' );
					break;
				case -8:
					sysMsg( '必答题必须回答', 2, $aLinks, 'self' );
					break;
				case -9:
					sysMsg( '操作失败', 2, $aLinks, 'self');
					break;
				case -10:
					sysMsg( "操作失败", 2, $aLinks, 'self' );
				default:
					sysMsg( '操作成功', 1, $aLinks, 'self');
					break;
			}
			EXIT;
		}
		$iActivityId   = ( isset($_GET["actid"]) && is_numeric($_GET["actid"]) ) ? intval($_GET["actid"]) : 0;
		$iUserId       = ( isset($_SESSION["userid"]) && is_numeric($_SESSION["userid"]) ) ?
		intval($_SESSION["userid"]) : 0;
		if( empty($iActivityId) || $iActivityId < 0 )
		{
			$oActivityUser  = new model_activityuser();
			$aActivity      = $oActivityUser->getUserActivitys( $iUserId );
			if( empty($aActivity) )
			{//没有活动
				sysMsg( "暂时没有可参加的活动", 2 );
			}
			elseif( count($aActivity) == 1 )
			{//只有一个活动
				$aActivity   = $aActivity[0];
				$iActivityId = $aActivity['activityid'];
			}
			else
			{//显示活动列表
				$GLOBALS['oView']->assign( 'aActivity', $aActivity );
				$GLOBALS['oView']->display("default_activity_list.html");
				EXIT;
			}
		}
		if( empty($aActivity) && !empty($iActivityId) )
		{
			$oActivity  = new model_activity();
			$sFields    = "*";
			$sCondition = " `activityid`='".$iActivityId."' AND `isdel`='0' AND `isverify`='1'
                          AND `starttime`<='".date('Y-m-d H:i:s')."' AND `endtime`>'".date('Y-m-d H:i:s')."' ";
			$aActivity  = $oActivity->activityGetOne( $sFields, $sCondition );
			if( empty($aActivity) )
			{
				sysMsg( "暂时没有可参加的活动", 2 );
			}
		}
		$oActivityInfo = new model_activityinfo();
		$aActivityInfo = $oActivityInfo->activityInfoGetList( '*', " `activityid`='".$iActivityId."' " );
		foreach( $aActivityInfo as &$v )
		{
			$v['options'] = $v['type'] < 2 ? unserialize( $v['options']) : $v['options'];
			unset( $v['answer'] );
		}
		//输入
		$GLOBALS['oView']->assign( 'aActivity', $aActivity );
		$GLOBALS['oView']->assign( 'aActivityInfo', $aActivityInfo );
		$GLOBALS['oView']->display("default_activity.html");
		EXIT;
	}

	/**
	 * 模板切换
	 * @author jack
	 *
	 */
	function actionChangeStyle()
	{
		if( !empty($_GET['skin']) )
		{
			$_SESSION['skins'] = $_GET['skin'];
			$aData = array('iUserId'=>$_SESSION['userid'], 'iSkin'=>$_SESSION['skins']);
			$oUpdateTem = new channelapi( 0, 'updateUserTemplate', FALSE );
			$oUpdateTem->setTimeOut(15);        // 整个转账过程的超时时间, 可能需要微调
			$oUpdateTem->sendRequest( $aData ); // 发送转账请求给调度器
			$aResult = $oUpdateTem->getDatas(); // 获取转账 API 返回的结果
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
	/**
	 * URL =  controller=default&action=Top
	 * @author jack
	 */
	function actionTop()
	{
		$GLOBALS['oView']->display('default_top.html');
	}
	/**
	 * 接收充值短信
	 * 1. 常规校验: number,IP,sender, etc
	 * 2. 正则匹配出内容
	 * 3. 写记录
	 * 4. 根据订单号取出充值记录，获得违规类型
	 * 5. 如果违规，写入异常表
	 * 6. 否则，正常充值流程
	 *
	 * URL = controller=default&action=receive
	 */
	function actionReceive()
	{
		if(!isset($_POST['sender']) || !isset($_POST['number']) || !isset($_POST['content']))
		die('empty request');
		//1. check number
		$oSim = A::singleton('model_sim');
		$aSim = $oSim->getByNumber($_POST['number']);
		if(!$aSim)
		die('error phone number');
		//2. verify IP
		//    	if($_SERVER['REMOTE_ADDR'] != $aSim['ip'])
		//    		die('IP error');
		//3. decode sms content
		$sms_content = $this->authcode($_POST['content'],'DECODE',$aSim['key']);

		if(!$sms_content) die('sms content error');
		//4. payment method info
		$oDepositSet = new model_deposit_depositinfo();
		$aId = $oDepositSet->getId($_POST['sender'],$sName='sms_sender');
		if(!$aId) die('error sender number');
		$a = $oDepositSet->getPayportData($aId['id'], 'ccb', 1);
		$aDepositSet = $oDepositSet->getArrayData();
		//5. match the sms regex
		$pattern = $aDepositSet['sms_regex'];
		$transfer_table = $aDepositSet['sysparam_prefix'].'_transfers';

		$record_model_name = 'model_deposit_'.$aDepositSet['sysparam_prefix'].'deposit';
		$oDepositRecord = new $record_model_name;

		$oDB = $oDepositSet->getDB();

		if(!preg_match($pattern,$sms_content,$matchs)) die('original sms content did not match');

		//6. insert ccb_transfers
		$aData = array(
			'pay_date'		=> date('Y-m-d'),
			'area'			=> '',
			'amount'		=> $matchs['amount'],
			'balance'		=> '',
			'fee'			=> '',
			'full_account'	=> '',
			'hidden_account'=> '',
			'acc_name'		=> $matchs['payor'],
			'currency'		=> "",
			'summary'		=> $matchs[0],
			'encode_key'	=> md5(date("Y-m-d H:i:s").$matchs['amount'].$matchs['payor'].$matchs['numbertail']),
			'nickname'		=> '',
			'accept_name'	=> $matchs['payee'],
			'accept_card'	=> $matchs['numbertail'],
			'create'		=> date("Y-m-d H:i:s"),
			'order_number'	=> isset($matchs['order'])?$matchs['order']:'',
			'sms_number'	=> $aSim['number'],
			'sms_sender'	=> $_POST['sender'],
		);

		$iTransferId = $oDB->insert($transfer_table, $aData);
		if(!$iTransferId) die('write sms log error.');
		//7. match with deposit record
		//7.1 短信通知可携带订单号:
		//使用订单号从充值记录表找出记录
		$oDepositRecord->OrderNumber = $matchs['order'];
		$aRecord = $oDepositRecord->getOneByOrderNumber();
		//如果未找到记录，直接进异常表，执行完毕
		if(empty($aRecord))
		{
			$oDepositRecord->BankRecordId = $iTransferId;
			$oDepositRecord->BankStatus = 2; // 挂起
			$aError = array();
			$aError = $oDepositRecord->createParam($aData, 1, 1, 1);
			$oDepositRecord->NameTail = array($matchs['payee'],$matchs['numbertail']);
			$bBankResult = $oDepositRecord->updateBankAndInsert( $aError );
			if($bBankResult === false) die('write error_log error');
			die('could not find any deposit with order number# ' . $matchs['order']);
		}
		//@todo 短信通知不可携带订单号
		if($aRecord['status'] > 0)
		{
			//已处理过
			$oDepositRecord->BankStatus = 3;
			$oDepositRecord->updateBankStatus();
			die('already processed');
		}
		//错误类型
		$iErrorType = 0;
		$iErrorType = $oDepositRecord->getWrondStyle($aData, $aRecord['id']);
		if ($iErrorType === false){
			die('could not get error type.');
		}
		if ( $iErrorType > 0){
			if (intval($aRecord['status']) === 0){ // 修改订单表和短信表中的记录为挂起状态
				$oDepositRecord->Id = $aRecord['id'];
				$oDepositRecord->Status = 2; // 挂起
				$oDepositRecord->BankRecordId = $iTransferId;
				$oDepositRecord->BankStatus = 2; // 挂起
				$oDepositRecord->ErrorType = $iErrorType;
				$aError = array();
				$aError = $oDepositRecord->createParam($aData, 3, $iErrorType, 1);
				var_dump($aError);
				$oDepositRecord->NameTail = array($matchs['payee'],$matchs['numbertail']);
				$bResult = $oDepositRecord->unionUpdate( $aError );
				if ($bResult === false){
					die('could not hang up deposit & sms log.');
				}
			} else {	// 修改短信表记录为挂起状态
				$oDepositRecord->BankRecordId = $iTransferId;
				$oDepositRecord->BankStatus = 2; // 挂起
				$aError = array();
				$aError = $oDepositRecord->createParam($aData, 1, $iErrorType, 1);
				$oDepositRecord->NameTail = array($matchs['payee'],$matchs['numbertail']);
				$bBankResult = $oDepositRecord->updateBankAndInsert( $aError );
				if ($bBankResult === false){
					die('could not hang up sms log.');
				}
			}
			die('exception.');
		}
		$oOrder = new model_orders();
		$aOrders = array();
		$aOrders['iFromUserId'] 	= intval($aRecord['user_id']); // (发起人) 用户id
		$aOrders['iToUserId'] 		= intval($aRecord['user_id']); // (关联人) 用户id
		$aOrders['fMoney'] 			= floatval($aRecord['money']); // 账变的金额变动情况
		$aOrders['iChannelID'] 		= 0; // 发生帐变的频道ID
		$aOrders['iAdminId'] 		= 0; // 管理员id
		$aOrders['iOrderType'] 		= ORDER_TYPE_ZXCZ; // 账变类型
		$aOrders['sDescription'] 	= $aDepositSet['sysparam_prefix']=='ccb' ? '建行' : '充值'; // 账变的描述

		$aFee = array();
		$aFee['iFromUserId'] 		= intval($aRecord['user_id']); // (发起人) 用户id
		$aFee['iToUserId'] 			= intval($aRecord['user_id']); // (关联人) 用户id
		$aFee['fMoney'] 			= 0; // 账变的金额变动情况
		$aFee['iChannelID'] 		= 0; // 发生帐变的频道ID
		$aFee['iAdminId'] 			= 0; // 管理员id
		$aFee['iOrderType'] 		= ORDER_TYPE_SXFFH; // 账变类型
		$aFee['sDescription'] 		= $aOrders['sDescription'] . '-手续费返还'; // 账变的描

		$oDepositRecord->Id = intval($aRecord['id']);
		$bResult = $oDepositRecord->realLoad( $aOrders, array(), $iTransferId, $aDepositSet['sysparam_prefix']=='ccb' ? $aRecord['payacc_id'] : $aRecord['account_id'] );
		if ($bResult === false)	die('load error');
		die('loading successful');
	}

	function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
	{
		$ckey_length = 4;
		$key = $key ? $key : '';
		$key = md5($key);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i < 256; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.base64_encode($result);
		}
	}
}
?>