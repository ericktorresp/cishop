<?php
/**
 * 文件:/_app/controller/user.php
 * 功能: 控制器 - 用户管理
 *
 * 功能:
 *     - actionAddtopproxy()       增加总代 
 *     - actionAssignskin()        分配模板
 *     - actionChangepass()        修改密码
 *     - actionConfirm()           资金操作确认页面
 *     - actionCreateaccount()     开户权限
 *     - actionCredit()            信用资金
 *     - actionDel()               用户删除
 *     - actionDrag()              中间的DRAG	
 * 	   - actionDelUserBank()		后台直接删除用户绑定银行卡		
 *     - actionEdit()              修改用户
 *     - actionFreeze()            用户冻结
 *     - actionFundunlock()        资金解锁
 *     - actionIndex()             用户中心
 *     - actionKuati()             用户跨级提现
 *     - actionList()              用户列表	
 *     - actionRankList()          用户星级
 *     - actionSave()              用户保存
 *     - actionSavecreateaccount() 保存开户权限
 *     - actionSavecredit()        用户信用(保存)
 *     - actionSavefreeze()        用户冻结(保存)
 *     - actionSavekuiti()         用户跨级提现(保存)
 *     - actionSaveStar()          用户评星(保存)
 *     - actionSavetopproxy()      增加总代(保存)
 *     - actionSavetopup()         用户充值(保存)
 *     - actionSaveunfreeze()      用户解冻(保存)
 *     - actionSavewithdraw()      用户提现(保存)
 *     - actionStar()              用户星级	
 *     - actionTeam()              用户团队
 *     - actionTopup()             用户充值	
 *     - actionUnfreeze()          用户解冻	
 *     - actionUpdatepass()        用户密码(保存)
 *     - actionView()              用户查看列表	
 *     - actionWithdraw()          用户提现
 * 
 * @author    Saul    090914
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_user extends basecontroller
{
    /**
     * 用户星级
     * URL = ./?controller=user&action=ranklist
     * @author SAUL 090517
     */
    function actionRanklist()
    {
    	$oUser   = new model_user();
    	$iAdmin  = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
    	if(!$oUser->checkAdminForUser( $iAdmin, 0 ))
    	{
    		$aUsers = $oUser->getChildListID( 0 ,FALSE ,"AND a.`lvtopid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdmin."')", "ORDER BY a.username");
    	}
    	else 
    	{
    		$aUsers = $oUser->getChildListID( 0 ,FALSE, "", "ORDER BY a.username" );
    	}
    	$iPage   = isset($_GET["p"]) && is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
    	$bAll    = FALSE;
    	$sWhere  = "";
    	$iUserId = 0;
    	if( isset($_GET["username"]) && $_GET["username"] != '' )
    	{ //先优先用户名
    		$sUserName = daddslashes(trim($_GET["username"]));
    		if( strpos("*",$sUserName) >= 0 )
    		{
    			$bAll    = TRUE;
    			$sWhere .= " AND u.`username` LIKE '".str_replace("*","%",$sUserName)."'";
    		}
    		else
    		{
    			$bAll    = TRUE;
    			$sWhere .= " AND u.`username` = '".$sUserName."'";
    		}
    		$GLOBALS['oView']->assign( "username", stripslashes_deep($sUserName) );					
    	}
    	if( $sWhere == "" )
    	{ // 没有传username参数进来
    		if( isset($_GET["agent"]) && is_numeric($_GET["agent"]) )
    		{
    			$iAgent = intval($_GET["agent"]);
    			if( $iAgent > 0 )
    			{
    				$GLOBALS["oView"]->assign( "agent", $iAgent );
    				$iUserId = $iAgent;
    			}
    		}
    	}
    	if( $iUserId == 0 )
    	{ // 用户ID整理
    		$iUserId  = isset($_GET["userid"]) && is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;	
    	}
    	if( isset($_GET["rank"]) && is_numeric($_GET["rank"]) )
    	{ // 用户星级
    		$iRank = intval($_GET["rank"]);
    		if( $iRank>0 )
    		{
    			$sWhere .= " AND u.`userrank` = '".$iRank."'";
    			$GLOBALS['oView']->assign( "rank", $iRank );
    		}
    	}
    	if( isset($_GET["sdate"]) && ( !empty($_GET["sdate"] ) ) )
    	{ // 评星开始时间
    		$sStartDate = getFilterDate($_GET["sdate"]);
    		$GLOBALS['oView']->assign("sdate",getFilterDate($sStartDate,"Y-m-d H:i"));
    		$sWhere .= " AND u.`rankcreatetime` >='".$sStartDate."'";
    	}
    	if( isset($_GET["edate"]) && ( !empty($_GET["edate"] ) ) )
    	{ // 评星结束时间 
    		$sEndDate = getFilterDate($_GET["edate"]);
    		$GLOBALS['oView']->assign( "edate", getFilterDate($sEndDate,"Y-m-d H:i") );
    		$sWhere  .=" AND u.`rankcreatetime` <='".$sEndDate."'";
    	}
    	if( isset($_GET["usdate"]) && ( !empty($_GET["usdate"] ) ) )
    	{ // 评星更新开始时间
    		$sUpdateStartDate = getFilterDate($_GET["usdate"]);
    		$GLOBALS['oView']->assign( "usdate", getFilterDate($sUpdateStartDate,"Y-m-d H:i") );
    		$sWhere  .=" AND u.`rankupdate` >='".$sUpdateStartDate."'";
    	}
    	if( isset($_GET["uedate"]) && ( !empty($_GET["uedate"] ) ) )
    	{ //评星更新结束时间 
    		$sUpdateEnddate = getFilterDate($_GET["uedate"]);
    		$GLOBALS['oView']->assign( "uedate", getFilterDate($sUpdateEnddate,"Y-m-d H:i") );
    		$sWhere  .=" AND u.`rankupdate` <='".$sUpdateEnddate."'";
    	}
    	if( !$bAll && $iUserId == 0 &&!empty($sWhere) )
    	{ // 当前的ID为0又指定了条件限制的情况，修正总代没有星级
    		$bAll = TRUE;
    	}
    	if( $bAll == TRUE )
    	{ // 修复指定了用户名的情况
    		$iUserId = 0;
    	}
    	if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
    	{
    		if( $iUserId >0 )
    		{
    			sysMessage( '您的权限不足', 1 );
    		}
    		else
    		{
    			$sWhere .= " AND ut.`lvtopid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdmin."')";
    		}
    	}
    	$aUserList = $oUser->getChildList($iUserId, 'u.`username`,u.`userid`,u.`userrank`,u.`rankcreatetime`,u.`rankupdate`,ug.`groupname`', $sWhere, 'u.username', 10, $iPage, $bAll );
    	// 当前查询到的所有用户ID
    	$aUserIds = array();
    	foreach( $aUserList['results'] as $aTmpUserList )
    	{
    		$aUserIds[] = intval($aTmpUserList["userid"]);
    	}
    	// 用户下级星级列表
    	$aUserRank  = $oUser->getRank( $aUserIds, array(1,2,3,4,5) );
    	$aUserLists = $aUserList['results'];
    	$aTemp      = array();
    	// 结果集整理，首先是用户名称
    	foreach( $aUserLists as $aUser )
    	{
    		$aTemp[ $aUser["userid"] ] = $aUser;
    	}
    	// 用户下级星级整理
    	foreach( $aUserRank as $v )
    	{
    		$aTemp[$v["parentid"]]["rank"][$v["userrank"]][] = "<a href=\"".url("user","ranklist",array("username"=>$v["username"]))."\">".$v["username"]."</a>&nbsp;";
    	}
    	unset($aUserRank);
    	// 用户下级星级个数整理
    	foreach( $aTemp as $iKey => $aValue )
    	{
    		if( isset($aValue["rank"]) )
    		{
    			foreach( $aValue["rank"] as $key => $value )
    			{
    				$aTemp[$iKey]["count"][$key] = count($value);
    			}
    		}
    	}
    	$GLOBALS['oView']->assign( "ur_here",    "用户星级");
    	$GLOBALS['oView']->assign( "users",      $aUsers);
    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","ranklist"), 'text'=>'清空过滤条件' ) );
    	$GLOBALS['oView']->assign( "s",          $aTemp);
    	$oPager = new pages($aUserList['affects'], 10, 10);
    	$GLOBALS['oView']->assign( 'pages',      $oPager->show() );
    	$oUser->assignSysInfo();
    	$GLOBALS['oView']->display("user_ranklist.html");
    	EXIT;
    }



	/**
	 * 资金解锁
	 * URL = ./?controller=user&action=fundlock
	 * @author SAUL 090517
	 */
	function actionFundunlock()
	{
		if( isset($_POST["unlock"]) && $_POST["unlock"] )
		{
			$aHref     = array(0=>array('text'=>'资金解锁','href'=>url('user','fundunlock')));
			$aEntrys   = isset($_POST["entry"]) ? $_POST["entry"] : array();
			$oUserfund = new model_userfund();
			$iFlag     = $oUserfund->fundUnlock( $aEntrys, 300 );
			if( $iFlag === -1 )
			{
				sysMessage( '没有提交数据', 1, $aHref );
			}
			elseif( $iFlag === FALSE )
			{
				sysMessage( '解锁部分失败', 1, $aHref );
			}
			else 
			{
				sysMessage( '操作成功', 0, $aHref );
			}
		}
		else
		{ //显示页面
			$oUserFund = new model_userfund();
			$aUsers    = $oUserFund->fundUnlockList();
			if( count($aUsers) > 0 )
			{
				$GLOBALS['oView']->assign( "users", $aUsers );
			}
			$GLOBALS['oView']->assign( "ur_here",   "资金解锁" );	
			$oUserFund->assignSysInfo();
			$GLOBALS['oView']->display("user_fundunlock.html");
			EXIT;
		}
	}



	/**
	 * 用户列表中的 - 评星 按钮
	 * URL = ./?controller=user&action=star
	 * @author SAUL 090517
	 */
	function actionStar()
	{
		$iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if( $iUserId == 0 )
		{
			redirect( url('user','view') );
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin , $iUserId ) )
		{
			sysMessage( '您的权限不足',  1 );
		}
		$aUser = $oUser->getUserExtentdInfo( $iUserId );
		if( $aUser['groupid']==4 || $aUser['isspecial']==4 )
		{
			sysMessage( '会员用户不参与评星', 1 );
		}
		$aUser    = $oUser->getUserInfo( $iUserId );
		$iUserPid = $oUser->getParentId( $iUserId );
		if( $iUserPid == 0 )
		{
			sysMessage( '总代用户不参与评星', 1 );
		}
		$aParentUser   = $oUser->getUserInfo( $iUserPid );
		$aParentUserEx = $oUser->getUserExtentdInfo( $iUserPid );
		$usergroup = ($aParentUserEx["isspecial"]==0) ? intval($aParentUserEx["groupid"]) : intval($aParentUserEx["isspecial"]);
		$GLOBALS['oView']->assign("user",      $aUser );
		$GLOBALS['oView']->assign("users",     $aParentUser );
		$GLOBALS['oView']->assign("userex",    $aParentUserEx );
		$GLOBALS['oView']->assign("usergroup", $usergroup );
		$GLOBALS['oView']->assign("ur_here",   "用户评星" );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_star.html");
		EXIT;
	}



	/**
	 * 用户评星(保存)
	 * URL = ./?controller=user&action=savestar
	 * @author SAUL 090517
	 */
	function actionSavestar()
	{
		$iUserId   = isset($_POST["userid"])&&is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
		if( $iUserId == 0 )
		{
			redirect( url('user','view') );
		}
		$iUserRank = isset($_POST["userrank"]) && is_numeric($_POST["userrank"]) ? intval($_POST["userrank"]) : 0;
		$oUser     = new model_user();
		$iAdmin    = isset($_SESSION["admin"]) && is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( "您的权限不足", 1 );
		}
		$iFlag     = $oUser->updateUserRank( $iUserId, $iUserRank );
		switch( $iFlag )
		{
			case -1:
				sysMessage( '操作失败: 用户不存在', 1 );
				break;
			case -2:
				sysMessage( '操作失败: 总代用户不参与评星', 1 );
				break;
			case -3:
				sysMessage( '操作失败: 用户星级不能大于上级星级', 1 );
				break;
			case -4:
				sysMessage( '操作失败: 用户星级不能小于下级星级', 1 );
				break;
			case 0:
				sysMessage('操作失败:更新数据失败.',1 );
			default:
				sysMessage( '操作成功', 0 );
				break;
		}
	}



	/**
	 * 用户保存 (用户身份以及昵称变化)
	 * URL = ./?controller=user&action=save
	 * @author saul 090517
	 */
	function actionSave()
	{
		$iUserId = isset($_POST["userid"])&&is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
		if( $iUserId == 0 )
		{
			redirect( url("user","view") );
		}
		$iChange   = isset($_POST["change"])&&is_numeric($_POST["change"]) ? intval($_POST["change"]) : 0;		
		$sUserNick = isset($_POST["nickname"]) ?daddslashes($_POST["nickname"]) : "";
		$oUser     = new model_user();
		$iAdmin    = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( "您的权限不足", 1 );
		}
		$iFlag     = $oUser->adminUpdateUserInfo( $iUserId, $sUserNick, ($iChange==1) );
		switch( $iFlag )
		{
			case 0:
				sysMessage( '操作失败:用户不存在', 1 );
				break;
			case -1:
				sysMessage( '操作失败:用户昵称格式错误', 1 );
				break;
			case -2:
				sysMessage( '操作失败', 1 );
				break;
			case -3:
				sysMessage( '操作失败:总代不能转化为会员', 1);
				break;
			case -4:
				sysMessage( '操作失败:星级代理不能转化为用户', 1 );
				break;
			case -5:
				sysMessage( '操作失败:更新用户树关系时事务回滚', 1);
				break;
			case -6:
				sysMessage( '操作失败:更新用户频道关系失败', 1);
				break;
			case -7:
				sysMessage( '修改昵称成功，更新用户关系失败', 1);
				break;
			default:
				sysMessage( '操作成功', 0, array(0=>array('text'=>'用户管理','href'=>url('user', 'view', array( 'userid'=>$iUserId ) ) ) ) );
				break;	
		}
	}



	/**
	 * 用户修改
	 * URL = ./?controller=user&action=edit
	 * @author SAUL 090517
	 */
	function actionEdit()
	{
		$iUserId    = isset($_GET["userid"])&&is_numeric($_GET["userid"])       ? intval($_GET["userid"])    : 0;
		if( $iUserId ==0 )
		{
			redirect( url( "user", "view" ) );
		}
		$oUser      = new model_user();
		$iAdmin     = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1 );
		}		
		$aUser      = $oUser->getUserExtentdInfo( $iUserId );
		$iGroup     = ($aUser["isspecial"]==0) ? intval($aUser["groupid"]): intval($aUser["isspecial"]);//用户的组ID
		$GLOBALS['oView']->assign( "ur_here",    "用户修改");
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url( "user" ,"view" , array( 'userid' => $iUserId ) ), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( "group",      $iGroup);
		$GLOBALS['oView']->assign( "user",       $aUser);
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_edit.html");
		EXIT;
	}



	/**
	 * 用户列表 [左框架]
	 * URL = ./?controller=user&action=list
	 * @author SAUL 090517
	 */
	function actionList()
	{
		$iUserId    = isset($_GET["userid"])&&is_numeric($_GET["userid"])   ? intval($_GET["userid"])    : 0;
		if( $iUserId ==0 )
		{
			$oUser  = new model_user();
			$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
			if( $oUser->checkAdminForUser( $iAdmin, 0 ) )
			{
				$aAgents = $oUser->getChildListID( 0, FALSE, "", "ORDER BY a.username"  );
				if( $aAgents !== FALSE )
				{
					$GLOBALS['oView']->assign( "users", $aAgents );
				}
				$GLOBALS['oView']->display("user_list.html");
				EXIT;
			}
			else 
			{
				$aAgents = $oUser->getChildListID(0, false, " AND a.`userid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdmin."')", "ORDER BY a.username");
				if( $aAgents !== FALSE )
				{
					$GLOBALS['oView']->assign( "users", $aAgents );
				}
				$GLOBALS['oView']->display("user_list.html");
				EXIT;
			}			
		}
		else
		{
			/**
			 * AJAX 处理用户部分
			 */
			$oUser  = new model_user();
			$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
			if(!$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
			{
				EXIT;
			}
			$aUsers = $oUser->getChildListID( $iUserId, FALSE, "", "ORDER BY a.username" );
			foreach($aUsers as $user)
			{
				echo"<div id=\"title_".$user['userid']."\">";
				if($user["childcount"]>0)
				{
					echo"<img src=\"./images/menu_plus.gif\" id=\"img_".$user["userid"]."\" onclick=\"show(".$user["userid"].");\"> ";
					echo"<a onclick=\"javascript:getchild(".$user['userid'].");\">".htmlspecialchars($user["username"])." <font color='#A0A0A0'>(".$user["childcount"].")</font></a>";
					echo"<div id=\"child_".$user['userid']."\" style=\"display:none;\" class=\"child\"></div>";
				}
				else 
				{
					echo"<img src=\"./images/menu_minus.gif\" id=\"img_".$user["userid"]."\"> ";
					echo"<a href=\"?controller=user&action=view&userid=".$user["userid"]."\" target=\"user_view\">".htmlspecialchars($user["username"])." <font color='#A0A0A0'>(".$user["childcount"].")</font></a>";
				}
				echo"</div>";
			}
			EXIT;
		}
	}



	/**
	 * 用户列表 [右框架]
	 * URL = ./?controller=user&action=view
	 * @author SAUL 090517
	 */
	function actionView()
	{
		$sWhere  = "";
		if( isset($_GET["username"]) && $_GET["username"]!='' )
		{ //用户名称			
			$sUserName = daddslashes( trim($_GET["username"]) );
			if ( strpos($sUserName,"*") >= 0 )
			{
				$sWhere .= " AND u.`username` LIKE '".str_replace("*","%",$sUserName)."'";
			}
			else
			{
				$sWhere .= " AND u.`username` = '".$sUserName."'";
			}
			$GLOBALS['oView']->assign( "username", stripslashes_deep($sUserName) );						
		}
        //用户组
		$iUserTeam = isset($_GET["team"]) && is_numeric($_GET["team"]) ? intval($_GET["team"]) : 0;
		$GLOBALS['oView']->assign( "userteam", $iUserTeam );
		if( $iUserTeam > 0 )
		{
			$sWhere .=" AND uc.`groupid`='".$iUserTeam."'";
		}
		$fMinMoney = isset($_GET["minmoney"])&&is_numeric($_GET["minmoney"]) ? doubleval($_GET["minmoney"]) : 0.00;//最小金额
		if( $fMinMoney>0.00 )
		{
			$sWhere.=" AND uf.`availablebalance`>=".$fMinMoney;
			$GLOBALS['oView']->assign( "minmoney", $fMinMoney );
		}		
		$fMaxMoney = isset($_GET["maxmoney"])&&is_numeric($_GET["maxmoney"]) ? doubleval($_GET["maxmoney"]) : 0.00;//最大金额
		if( $fMaxMoney>0.00 )
		{
			$sWhere.=" AND uf.`availablebalance`<".$fMaxMoney;
			$GLOBALS['oView']->assign( "maxmoney", $fMaxMoney );
		}
		if ( isset($_GET["rdate"]) )
		{ //注册开始时间
			$sRegIsterdate = getFilterDate($_GET["rdate"]);
			if( $sRegIsterdate<>'' )
			{
				$sWhere.=" AND u.`registertime`>'".$sRegIsterdate."'";
				$GLOBALS['oView']->assign("rdate",getFilterDate($sRegIsterdate,"Y-m-d H:i"));
			}
		}		
		if ( isset($_GET["redate"]) )
		{ //注册结束时间
			$sRegEndDate = getFilterDate($_GET["redate"]);
			if( $sRegEndDate<>'' )
			{
				$sWhere.=" AND u.`registertime`<'".$sRegEndDate."'";
				$GLOBALS['oView']->assign("redate",getFilterDate($sRegEndDate,"Y-m-d H:i"));
			}
		}
		//排序的支持
		$sOrder   = isset($_GET["order"]) ? $_GET["order"] : "";
		$sOrderBy = '';
		$sDesc    =  isset($_GET["desc"]) ? "DESC" : "ASC";
		switch( $sOrder )
		{
			case "ID":
				$sOrderBy = "u.`userid` ".$sDesc;
				break;
			case "name":
				$sOrderBy = "u.`username` ".$sDesc;	
				break;
			case "date":
				$sOrderBy = "u.`registertime` ".$sDesc;
				break;
			case "money":
				$sOrderBy = "uf.`availablebalance` ".$sDesc;
				break;	
			default:
				$sOrderBy = "u.username ";
				break;
		}
		$GLOBALS['oView']->assign( "order", $sOrder );
		$GLOBALS['oView']->assign( "desc",  $sDesc );
		$oUserGroup  = new model_usergroup();
		$aUserGroups = $oUserGroup->getList( array('groupid', 'groupname') );
		$GLOBALS['oView']->assign( "usergroup", $aUserGroups );
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		$oUser  = new model_user();
		if ( $sWhere == "" )
		{
			$iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"])     : 0;
			if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
			{
				if($iUserId == 0)
				{
					$sWhere .= " AND ut.`lvtopid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdmin."')";
				}
				else 
				{
					sysMessage( '您的权限不足', 1 );
				}
			}
			$bool = FALSE;
		}
		else 
		{
			$iUserId = 0;
			if( !$oUser->checkAdminForUser( $iAdmin, 0 ) )
			{	
				$sWhere .= " AND ut.`lvtopid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdmin."')";
			}
			$bool = TRUE;
		}
		$GLOBALS['oView']->assign( "id", $iUserId );
		$sFileds  = "u.`userid`,u.`username`,u.`registertime`,u.`authtoparent`,ut.`isfrozen`,"
				."u.`authadd`,ut.`parentid`,ut.`ocs_status`,ug.`groupname`,ug.`groupid`,ug.`isspecial`,uf.`availablebalance`,"
				."uf.`channelbalance`,uf.`cashbalance`,uf.`holdbalance`"; 		
		$iPage  = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) :1;
		$pn = isset($_GET['pn']) ? intval($_GET['pn']) : 0;
	    $aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25; 
		$GLOBALS['oView']->assign( "ahtml", $aHtml );
		
		$aUsers  = $oUser->getChildList($iUserId, $sFileds, $sWhere, $sOrderBy, $aHtml['pn'], $iPage, $bool, TRUE, 0);				
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view"), 'text'=>'清空过滤条件' ) );		
		$GLOBALS['oView']->assign( "ur_here",    "用户列表");
		if( isset($aUsers['self']) )
		{
			/** louis **/
			// 如果用户的密码在待审核状态，则不允许再修改
			// 查询用户的密码是否是在待审核状态
			$oSecondVerify = new model_secondverify();
			$oSecondVerify->UserId = $aUsers['self']['userid'];
			$aUsers['self']['status'] = $oSecondVerify->isVerify() === true ? 1 : 0;
			/** louis **/
			$GLOBALS['oView']->assign( "me",     $aUsers['self'] );
			$aUsers["affects"] = $aUsers["affects"] + 1;
		}
		/** louis **/
		// 如果用户的密码在待审核状态，则不允许再修改
		if (!empty($aUsers['results'])){
			foreach ($aUsers['results'] as $k => $v){
				// 查询用户的密码是否是在待审核状态
				$oSecondVerify = new model_secondverify();
				$oSecondVerify->UserId = $v['userid'];
				$aUsers['results'][$k]['status'] = $oSecondVerify->isVerify() === true ? 1 : 0;
			}
		}
		/** louis **/
        //用户中心部分的 link
	 	$aUserTree = $oUser->getParent($iUserId, TRUE);
	 	$GLOBALS['oView']->assign( "usertree",  $aUserTree );
		$GLOBALS['oView']->assign( "users",     $aUsers['results'] );
		$oPager   = new pages( $aUsers["affects"], $aHtml['pn'], 10 );
		$GLOBALS['oView']->assign( 'pages',     $oPager->show() );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_view.html");
		EXIT;
	}



	/**
	 * 用户资金跨提操作
	 * url = ./?controller=user&action=kuati
	 * @author SAUL
	 */
	function actionKuati()
	{
		$iUserId   = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if( $iUserId ==0 )
		{
			redirect( url( "user", "view" ) );
		}		
		$oUser     = new model_user();
		$iAdmin    = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1 );
		}
		$oUserFund = new model_userfund();
		if( $oUser->isTopProxy( $iUserId ) )
		{
			sysMessage( '总代不支持跨级提现操作', 1 );
		}
		else
		{
			$aUsersFund = $oUserFund->getFundByUser( $iUserId, 'u.`userid`,u.`username`,ut.`parentid`,uf.*', 0, FALSE );
			$fUserMax   = $aUsersFund["availablebalance"];
		}
		$GLOBALS["oView"]->assign( "userMax",    $fUserMax );		
		$oChannel  = new model_channels();
		$aChannels = $oChannel->channelGetAll('',"`pid`='0'");
		$GLOBALS['oView']->assign( "userfund",   $aUsersFund );
		$GLOBALS['oView']->assign( 'channels',   $aChannels );		
		$GLOBALS['oView']->assign( "ur_here",    "跨级提现" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view",array('userid'=>$iUserId)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( 'caption',    '跨级提现' );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->assign( "action",     "savekuati" );
		$GLOBALS['oView']->display("user_withdraw.html");
		EXIT;
	}



	/**
	 * 修改密码(更新)
	 * URL =./?controller=user&action=updatepass
	 * @author SAUL 090517
	 */
	function actionUpdatepass()
	{
		$sLoginPwd  = isset($_POST["loginpwd"])  ? $_POST["loginpwd"]  : "";
		$sLoginPwd2 = isset($_POST["loginpwd2"]) ? $_POST["loginpwd2"] : "";
		$iUserId    = isset($_POST["userid"])&&is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
		if( $iUserId ==0 )
		{
			redirect( url("user","view") );
		}
		$aLocation  = array( 0=> array( 'text' => '返回用户列表' , 'href'=> url( 'user', 'view', array( 'userid' => $iUserId ) ) ) );
		/** louis **/
		$oSecondVerify = new model_secondverify();
		$oSecondVerify->UserId = $iUserId;
		if ($oSecondVerify->isVerify() === true){
			sysMessage('用户密码处于待审核中，请先审核后才能再次修改', 1, $aLocation);
		}
		/** louis **/
		$sSecurityPwd  = isset($_POST["securitypwd"])  ? $_POST["securitypwd"]  : "";
		$sSecurityPwd2 = isset($_POST["securitypwd2"]) ? $_POST["securitypwd2"] : "";
		$oUser = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage('您的权限不足', 1, $aLocation);
		}
		if( $sLoginPwd != $sLoginPwd2 )
		{
			sysMessage("登陆密码重复不匹配", 1, $aLocation);
		}			
		if( !($oUser->checkUserPass($sLoginPwd)||empty($sLoginPwd)) )
		{
			sysMessage("登陆密码格式不正确", 1 );
		}		
		if( !($oUser->checkUserPass($sSecurityPwd)||empty($sSecurityPwd)) )
		{
			sysMessage("安全密码格式不正确", 1 );
		}
		if( $sSecurityPwd != $sSecurityPwd2 )
		{
			sysMessage("资金密码重复不匹配", 1, $aLocation);
			EXIT;
		}
		if( $sSecurityPwd==$sLoginPwd )
		{
			if( $sSecurityPwd == "" )
			{
				sysMessage("登陆密码和安全密码为空", 1, $aLocation);
			}
			else 
			{
				sysMessage("登陆密码和资金密码相等", 1, $aLocation);
			}
		}
		$aValue = array();
		$aValue['user_id'] 		= $iUserId;
		$aValue['loginpwd'] 	= isset($sLoginPwd) ? $sLoginPwd : '';
		$aValue['securitypwd'] 	= isset($sSecurityPwd) ? $sSecurityPwd : '';

		$oSecondVerify->Value 		= $aValue;
		$oSecondVerify->NickName	= "changepass";
		$mResult = $oSecondVerify->index();
		switch ($mResult){
			case -7 :
				sysMessage( '操作函数不存在', 1, $aLocation );
			break;
			case -14 :
				sysMessage( '数据不存在', 1, $aLocation );
			break;
			case -17 :
				sysMessage( '用户不存在', 1, $aLocation );
			break;
			case -15 :
				sysMessage("操作失败:登录密码不能与资金密码相同", 1, $aLocation);
			break;
			case -16 :
				sysMessage("操作失败:资金密码不能与登录密码相同", 1, $aLocation);
			break;
			case $mResult > 0 :
				sysMessage( '操作成功，请联系管理员审核后生效', 0, $aLocation );
			break;
			default:
				sysMessage( '操作失败，未知错误，请联系管理员', 1, $aLocation );
			break;
		}
		/*$iFlag = $oUser->changePassWord($iUserId, $sLoginPwd, $sSecurityPwd);
		if(  $iFlag > 0 )
		{
			sysMessage("操作成功", 0, $aLocation);
		}
		elseif( $iFlag == -1 )
		{
			sysMessage("操作失败:资金密码不能与登录密码相同", 1, $aLocation);
		}
		else
		{
		    sysMessage("操作失败:没有数据更新", 1, $aLocation);
		}*/
	}



	/**
	 * 用户列表 [包含左右的2个框架HTML]
	 * URL = ./?controller=user&action=index
	 * @author SAUL 090517
	 */
	function actionIndex()
	{
		$GLOBALS['oView']->display("user_center.html");
		EXIT;
	}



	/**
	 * 用户列表中间的DRAG条
	 * URL =./?controller=user&action=drag
	 * @author SAUL 090517
	 */
	function actionDrag()
	{
		$GLOBALS['oView']->display("user_drag.html");
		EXIT;
	}



	/**
	 * 开户权限
	 * URL = ./?controller=user&action=createaccount
	 * @author SAUL 090517
	 * 完成度:100%
	 */
	function actionCreateaccount()
	{		
		$oAgent     = new model_agent();
		$iOpenLevel = isset($_GET["open_level"])&&is_numeric($_GET["open_level"]) ? intval($_GET["open_level"]) : -2;
		$sWhere     = " AND 1 ";
		if( $iOpenLevel >= 0 )
		{
			$sWhere .= " AND b.`proxyvalue` ='".$iOpenLevel."'";
		}
		$iTopSet = isset($_GET["topset"])&&is_numeric($_GET["topset"]) ? intval($_GET["topset"]) : 0;
		if( $iTopSet == 1 )
		{
			$sWhere .= " AND a.`proxyvalue` ='1'";
		}
		elseif( $iTopSet == 2 )
		{
			$sWhere .= " AND a.`proxyvalue` ='0'";
		}
		elseif( $iTopSet == 3 )
		{
			$sWhere .= " AND c.`addcount`>0";
		}
		elseif( $iTopSet == 4 )
		{
			$sWhere .= " AND c.`addcount`=0";
		}
		$aUsers = $oAgent->userCreateAccount( $sWhere );//总代设置
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","createaccount"), 'text'=>'清空过滤条件' ) );		
		$GLOBALS["oView"]->assign( "users",      $aUsers );
		$GLOBALS["oView"]->assign( "topset",     $iTopSet );
		$GLOBALS["oView"]->assign( "top_level",  $iOpenLevel );
		$GLOBALS["oView"]->assign( "ur_here",    "开户权限" );
		$oAgent->assignSysInfo();
		$GLOBALS["oView"]->display("user_createaccount.html");
		EXIT;
	}



	/**
	 * 保存开户权限
	 * URL = ./?controller=user&action=savecreateaccount
	 * @author SAUL 090517
	 */
	function actionSavecreateaccount()
	{
		$aUser = isset($_POST["userid"])&&is_array($_POST["userid"]) ? daddslashes($_POST["userid"]) : array();
		if( empty($aUser) )
		{
			sysMessage('操作失败: 没有数据提交', 1);
		}
		$aOpenLevel = isset($_POST["open_level"])&&is_array($_POST["open_level"]) ? $_POST["open_level"] : array();
		$aNumAdd    = isset($_POST["num_add"])&&is_array($_POST["num_add"])       ? $_POST["num_add"]    : array();
		$aMumSub    = isset($_POST["num_sub"])&&is_array($_POST["num_sub"])       ? $_POST["num_sub"]    : array(); 
		$aAllow     = isset($_POST["allow"])&&is_array($_POST["allow"])           ? $_POST["allow"]      : array();
		$oAgent     = new model_agent();
		$iFlag      = $oAgent->updateCreateAccount( $aUser, $aOpenLevel, $aNumAdd, $aMumSub, $aAllow );
		if($iFlag == 1)
		{
			sysMessage('操作成功', 0);
		}
		else		
		{
			sysMessage('操作失败', 1);
		}
	}



	/**
	 * 增加总代 
	 * URL = ./?controller=user&action=addtopproxy
	 * @author SAUL 090517
	 */
	function actionAddtopproxy()
	{
		$oAdminuser = new model_adminuser();
		$aSales     = $oAdminuser->getSale();
		$oDomain    = new model_domains();
		$aDomains   = $oDomain->domainList();
		$GLOBALS["oView"]->assign( "ur_here",   "增加总代" );
		$GLOBALS["oView"]->assign( "sales",     $aSales );
		$GLOBALS['oView']->assign( "domains",   $aDomains );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href' => url( "user", "index" ), 'text' => '用户列表' ) );
		$oDomain->assignSysInfo();
		$GLOBALS["oView"]->display("user_addtopproxy.html");
		EXIT;
	}



	/**
	 * 保存增加总代
	 * URL =./?controller=user&action=savetopproxy
	 * @author SAUL 090517
	 */
	function actionSavetopproxy()
	{
		$aUser["username"]     =  isset($_POST["username"]) ? $_POST["username"] :"";
		$aUser["loginpwd"]     =  isset($_POST["userpass"]) ? $_POST["userpass"] :"";
		$aUser["securitypwd"]  =  '';
		$aUser["usertype"]     =  1;
		$aUser["nickname"]     =  isset($_POST["usernick"]) ? $_POST["usernick"] :"";
		$aUser["language"]     =  'utf8_zhcn';
		$aUser["email"]        =  "";
		//$aUser["isfrozen"]     =  0;
		//$aUser["frozentype"]   =  0;
		$aUser["authtoparent"] =  0;
		$aUser["lastip"]       =  '0.0.0.0';
		$aUser["lasttime"]     =  '1970-01-01 00:00:00';
		$aUser["registerip"]   =  getRealIp();
		$aUser["registertime"] =  date("Y-m-d H:i:s", time());
		$aUser["addcount"]     =  isset($_POST["num_add"])&&is_numeric($_POST["num_add"]) ? intval($_POST["num_add"]) : 0;//开户个数
		$aUser["authadd"]      =  1; //允许开户
		$iIsTester             =  isset($_POST["istester"]) ? intval($_POST["istester"]) : 0;
		$oUser = new model_user();
		if( !$oUser->checkUserName($aUser["username"]) )
		{
			sysMessage( "操作失败:总代帐号格式不正确", 1 );
		}
		if( !$oUser->checkUserPass($aUser["loginpwd"]) )
		{
			sysMessage( "操作失败:总代密码格式不正确", 1 );
		}
		if( !$oUser->checkNickName($aUser["nickname"]) )
		{
			sysMessage( "操作失败:总代昵称格式不正确", 1 );
		}
		// 成功返回插入的用户ID，失败返回FALSE，同名帐户存在返回-1
		$iFlag = $oUser->insertUser( $aUser, 1, 0, TRUE, $iIsTester );
		if( $iFlag === -1 )
		{
			sysMessage( '操作失败:总代的名称已经存在', 1 );
		}
		/*elseif ( $iFlag === -1001 )
		{
		    sysMessage( '低频开户成功,高频同步开户失败', 1 );
		}
		elseif ( $iFlag === -1002 )
		{
		    sysMessage( '低频开户成功,高频同步开户失败:数据冲突,ID可能被占用！',1 );
		}*/
		elseif($iFlag === FALSE)
		{
			sysMessage( '操作失败', 1 );
		}
		else 
		{	//处理总代和销售之间的关系
			$iSale = isset($_POST["sales"])&&is_numeric($_POST["sales"]) ? intval($_POST["sales"]) : 0;
			if( $iSale>0 )
			{
				$oAdminProxy = new model_adminproxy();
				$oAdminProxy->add( $iSale, $iFlag );
			}
			$oAgent = new model_agent();
			if( isset($_POST["domain"]) && is_array($_POST["domain"]) )
			{
				foreach( $_POST["domain"] as $iValue )
				{
					$oAgent->userdomainAdd( $iValue, array( $iFlag ) );
				}
			}
			//TODO _a高频、低频并行前期临时程序
			/*if( isset($GLOBALS['aSysDbServer']['gaopin']) )
			{
			     $oAgent->synUrl( $GLOBALS['aSysDbServer']['gaopin'] );
			}*/
			sysMessage( '操作成功', 0 );
		} 		
	}



	/**
	 * 用户团队
	 * url = ./?controller=user&action=team
	 * @author SAUL 090517
	 */
	function actionTeam()
	{
		$iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if( $iUserId == 0 )
		{
			redirect("user","view");
		}		
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]):0;
		if(!$oUser->checkAdminForUser( $iAdmin, $iUserId ))
		{
			sysMessage( '您的权限不足' ,1 );
		}
		$aUserInfo = $oUser->getUserInfo($iUserId);		
		$fUserBank = $oUser->getTeamBank($iUserId);
		$aUserEx   = $oUser->getUserExtentdInfo($iUserId);
		$GLOBALS['oView']->assign('ur_here',    "用户团队");
		$GLOBALS['oView']->assign('actionlink', array('href'=>url("user","view",array('userid'=>$iUserId)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign('userex',     $aUserEx);
		$GLOBALS['oView']->assign('userinfo',   $aUserInfo);
		$GLOBALS['oView']->assign('userbank',   $fUserBank);
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_team.html");
		EXIT;
	}



	/**
	 * 修改密码(前台显示)
	 * URL =./?controller=user&action=changepass
	 * @author SAUL 090517
	 */
	function actionChangepass()
	{
		$iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if($iUserId ==0 )
		{
			redirect("user","view");
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if(!$oUser->checkAdminForUser( $iAdmin,$iUserId) )
		{
			sysMessage( '您的权限不足', 1 );
		}
		$aUser  = $oUser->getUserInfo($iUserId);
		$GLOBALS['oView']->assign( 'ur_here',    "修改密码");
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view",array('userid'=>$iUserId)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( 'user',       $aUser);
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_changepass.html");
		EXIT;
	}



	/**
	 * 用户冻结
	 * URL = ./?controller=user&action=freeze
	 * @author SAUL 090517
	 */
	function actionFreeze()
	{
		$iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if( $iUserId==0 )
		{
			redirect( url("user","view") );
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage('您的权限不足', 1);
		}
		$aUser  = $oUser->getUserInfo( $iUserId );
		$GLOBALS['oView']->assign( 'ur_here',    "用户冻结" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view",array('userid'=>$iUserId)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( 'user',       $aUser );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_freeze.html");
		EXIT;
	}



	/**
	 * 用户冻结(执行)
	 * URL = ./?controller=user&action=savefreeze
	 * @author SAUL 090517
	 */
	function actionSavefreeze()
	{
		$iUserId = isset($_POST["userid"]) &&is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
		if($iUserId == 0)
		{
			redirect( url('user', 'view') );
		} 
		$iFree   = isset($_POST["free"]) && is_numeric($_POST["free"]) ? intval($_POST["free"])   : 0;
		$iFreeType = isset($_POST["freetype"])&&is_numeric($_POST["freetype"]) ? intval($_POST["freetype"]) : 0;
		if( $iFree == 0 || $iFreeType == 0 )
		{
			redirect( url('user', 'freeze', array('userid'=>$iUserId)));
		} 
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		$oUser  =  new model_user();
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1 );
		}
		if( $oUser->frozenUser( $iUserId, $iAdmin, 2, $iFreeType, ($iFree==2) ) )
		{
			sysMessage('操作成功', 0, array('0'=>array( 'text'=>'用户管理', 'href'=>url( 'user', 'view', array( 'userid' => $iUserId ) ) ) ) );
		}
		else
		{
			sysMessage('操作失败', 1, array('0'=>array( 'text'=>'用户管理', 'href'=>url( 'user', 'view', array( 'userid' => $iUserId ) ) ) ) );
		}
	}



	/**
	 * 用户解冻
	 * URL = ./?controller=user&action=unfreeze
	 * @author SAUL 090517
	 */
	function actionUnfreeze()
	{
		$iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if ( $iUserId ==0 ) 
		{
			redirect( url('user', 'view') );
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1 );
		}
		$aUser  = $oUser->getUserInfo( $iUserId );
		$GLOBALS['oView']->assign( 'ur_here',    "用户解冻" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view",array('userid'=>$iUserId)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( 'user',       $aUser );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_unfreeze.html");
		EXIT;
	}



	/**
	 * 用户解冻(执行)
	 * URL = ./?controller=user&action=saveunfreeze
	 * @author SAUL 090517
	 */
	function actionSaveunfreeze()
	{
		$iUserId = isset($_POST["userid"])&&is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
		if($iUserId == 0 )
		{
			redirect(url("user","view"));
		}
		$iUnFree = isset($_POST["unfree"])&&is_numeric($_POST["unfree"]) ? intval($_POST["unfree"]) : 0;
		if( ($iUnFree<=0)||($iUnFree>=3) )
		{
			redirect( url("user", "view") );
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( $iAdmin ==0 )
		{
			redirect( url("user", "view") );
		}
		if(!$oUser->checkAdminForUser( $iAdmin, $iUserId))
		{
			sysMessage('您的权限不足', 1);
		}		
		if($oUser->unFrozenUser( $iUserId , $iAdmin , 2, ($iUnFree==2)))
		{
			sysMessage("操作成功", 0, array(0=>array('text'=>'用户管理列表','href'=>url( 'user', 'view', array( 'userid' => $iUserId) ) ) ) );
		}
		else
		{
			sysMessage("操作失败", 1, array(0=>array('text'=>'用户管理列表','href'=>url( 'user', 'view', array( 'userid' => $iUserId) ) ) ) );
		}
	}



	/**
	 * 用户充值
	 * URL = ./?controller=user&action=topup
	 * @author SAUL 090517
	 */
	function actionTopup()
	{
		$iUserid = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if( $iUserid==0 )
		{
			redirect( url( "user", "view" ) );
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if(!$oUser->checkAdminForUser( $iAdmin, $iUserid ))
		{
			sysMessage('您的权限不足', 1 );
		}
		$oUserFund   = new model_userfund();
		$aUserFund   = $oUserFund->getFundByUser( $iUserid, 'u.`username`,u.`userid`,ut.`parentid`,uf.*', 0, FALSE );
		$aUserCredit = $oUserFund->getUserCredit( $iUserid );
		if( !empty($aUserCredit) )
		{
			$GLOBALS['oView']->assign( "credit" , $aUserCredit['proxyvalue'] );
		}				
		$oChannel    = new model_channels();
		$aChannels   = $oChannel->channelGetAll('',"`pid`=0");
		$GLOBALS['oView']->assign( "ur_here",    "用户充值" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view",array('userid'=>$iUserid)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( "userfund",   $aUserFund );
		$GLOBALS['oView']->assign( 'channels',   $aChannels );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_topup.html");
		EXIT;
	}



	/**
	 * 用户充值(处理)
	 * URL = ./?controller=user&action=savetopup
	 * @author SAUL 090517
	 */ 
	function actionSavetopup()
	{
		$iUserId      = isset($_POST["userid"])&&is_numeric($_POST["userid"])     ? intval($_POST["userid"])   : 0;
		$aLocation    = array(0=>array("text"=>'用户管理',"href"=>url("user","view",array("userid"=>$iUserId))));
		$iAdmin       = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;		
		$fMoney       = isset($_POST["money"])&&is_numeric($_POST["money"])       ? doubleval($_POST["money"]) : 0.00;
		$fFee         = isset($_POST["fee"])&&is_numeric($_POST["fee"])       	  ? doubleval($_POST["fee"]) : 0.00;
		$iIsFee       = isset($_POST["isfee"])&&is_numeric($_POST["isfee"])       ? intval($_POST["isfee"]) : 0;
		$iPayment     = isset($_POST["payment"])&&is_numeric($_POST["payment"])   ? intval($_POST["payment"])  : 0;
		$iManual      = isset($_POST["manual"])&&is_numeric($_POST["manual"])     ? intval($_POST["manual"])  : 0;
		$iChannel     = isset($_POST["channel"])&&is_numeric($_POST["channel"])   ? intval($_POST["channel"])  : 0;
		$sDescription = isset($_POST["description"])                              ? $_POST["description"]      :  "";
		
		if( ($iUserId==0) || ($fMoney==0.00) )
		{
			redirect( url("user", "view") );//用户为空的时候，已经充值为0.00的时候不执行
		}
		if( $iPayment == 0 )
		{
			$iChannel = 0;//只向银行频道加钱
		}
		// 理赔充值和人工充值不能同时选择
		if ($iPayment === 1 && $iManual === 1){
			sysMessage( '不能同时选择理赔充值和人工充值', 1, $aLocation );
		}
		
	    $oUser = new model_user();
		if(!$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1, $aLocation );
		}
		
		// 如果有手续费,必须与人工充值一并使用
		if ($iIsFee === 1){
			if ($iManual !== 1){
				sysMessage( '手续费必须与人工充值一并使用', 1, $aLocation );
				exit;
			} else { // 手续费金额不能高于100
				if ($fFee <= 0 || $fFee >= 100){
					sysMessage( '手续费金额不正确', 1, $aLocation );
					exit;
				}
			}
		}
		
		// 二次审核
		$aValue = array();
		$aValue['user_id'] = $iUserId;
		$aValue['fmoney'] = $fMoney;
		// 手续费
		$aFee = array();
		if ($iIsFee === 1 && $fFee > 0 && $fFee < 100){
			$aFee['user_id'] = $iUserId;
			$aFee['fmoney'] = $fFee;
		}
		$aUserInfo = $oUser->getUserExtentdInfo($iUserId);
		$oOrders = new model_orders();

		if (intval($iPayment) === 0 && intval($iManual) === 0){ // 给用户充值
			if ($aUserInfo['groupid'] == 1 || $aUserInfo['isspecial'] == 1){ // 总代
				$aValue['order_type']   = ORDER_TYPE_SJCZ;
	            $aValue['description'] = "上级充值:".$sDescription;
			} else {
				$aValue['order_type']   = ORDER_TYPE_KJCZ;
	            $aValue['description'] = "跨级充值:".$sDescription;
			}
		} elseif ($iManual === 1){
			$aValue['order_type']   = ORDER_TYPE_RGCZ;
	        $aValue['description'] = "人工充值:".$sDescription;
	        if ($iIsFee === 1 && $fFee > 0 && $fFee < 100){
		        $aFee['order_type']   = ORDER_TYPE_SXFFH;
		        $aFee['description'] = "手续费返还:".$sDescription;
	        }
		} else { // 理赔充值
			$aValue['order_type']   = ORDER_TYPE_LPCZ;
            $aValue['description'] = "理赔充值:".$sDescription;
		}
		$oSecondVerify = new model_secondverify();
		$oSecondVerify->NickName = "load";
		$oSecondVerify->Value = $aValue;
		$oSecondVerify->Fee = $aFee;
		$mResult = $oSecondVerify->index();
		switch ($mResult){
			case -7 :
				sysMessage( '操作函数不存在', 1, $aLocation );
			break;
			case -14 :
				sysMessage( '数据不存在', 1, $aLocation );
			break;
			case -15 :
				sysMessage( '用户不存在', 1, $aLocation );
			break;
			case $mResult > 0 :
				sysMessage( '操作成功，请联系管理员审核后生效', 0, $aLocation );
			break;
			default:
				sysMessage( '操作失败，未知错误，请联系管理员', 1, $aLocation );
			break;
		}
		
		/*if( $iPayment == 0 )
		{ // 执行总代给用户充值
			$oUserFund = new model_userfund();
			$iFlag = $oUserFund->adminToUserSaveUp( $iAdmin, $iUserId, $fMoney, '');
			switch ( $iFlag )
			{
				case -1:
					sysMessage( '提交的数据不全', 1, $aLocation );
					break;
				case -2:
					sysMessage( '用户资金帐户被锁', 1, $aLocation );
					break;
				case -3:
					sysMessage( '操作成功,但帐号被锁', 1, $aLocation );
					break;
				case -1001:
					sysMessage( '用户不存在', 1, $aLocation );
					break;
				case -1002:
					sysMessage( '账变类型ID错误', 1, $aLocation );
					break;
				case -1003:
					sysMessage( '账变金额错误, 不允许负数', 1, $aLocation );
					break;
				case -1004:
					sysMessage( '获取用户频道资金数据失败', 1, $aLocation );
					break;
				case -1005:
					sysMessage( '用户资金账户未被锁', 1, $aLocation );
					break;
				case -1006:
					sysMessage( '账变类型错误,未被程序枚举处理', 1, $aLocation );
					break;
				case -1007:
					sysMessage( '账变记录插入失败', 1, $aLocation );
					break;
				case -1008:
					sysMessage( '账户金额更新失败', 1, $aLocation );
					break;
				case -1009:
					sysMessage( '金额不正确', 1, $aLocation );
					break;
				case 1:
					sysMessage( '操作成功', 0, $aLocation );	
					break;
				default:
					sysMessage( '未知错误,请联系技术人员', 1, $aLocation );
					break;
			}
		}
		else
		{  //执行用户理赔充值
			$oUserFund = new model_userfund();
			$iFlag     = $oUserFund->adminPayMent( $iAdmin, $iUserId, $fMoney, $sDescription, $iChannel );
			switch ($iFlag)
			{
				case -1:
					sysMessage( '提交的数据不全', 1, $aLocation );
					break;
				case -2:
					sysMessage( '帐户被锁', 1, $aLocation );
					break;
				case -3:
					sysMessage( '用户理赔充值成功,但帐号被锁', 1, $aLocation );
					break;
				case -1001:
					sysMessage( '用户ID错误', 1, $aLocation );
					break;
				case -1002:
					sysMessage( '账变类型ID错误', 1, $aLocation );
					break;
				case -1003:
					sysMessage( '账变金额错误, 不允许负数', 1, $aLocation );
					break;
				case -1004:
					sysMessage( '获取用户频道资金数据失败', 1, $aLocation );
					break;
				case -1005:
					sysMessage( '用户资金账户未被锁', 1, $aLocation );
					break;
				case -1006:
					sysMessage( '账变类型错误,未被程序枚举处理', 1, $aLocation );
					break;
				case -1007:
					sysMessage( '账变记录插入失败', 1, $aLocation );
					break;
				case -1008:
					sysMessage( '账户金额更新失败', 1, $aLocation );
					break;
				case -1009:
					sysMessage( '金额不正确', 1, $aLocation );
					break;
				case 1:
					sysMessage( '操作成功', 0, $aLocation );
					break;
				default:
					sysMessage( '未知错误,请联系技术人员', 1, $aLocation );
					break;
			}
		}*/
	}



	/**
	 * 用户提现
	 * URL = ./?controller=user&action=withdraw
	 * @author SAUL 090517
	 */
	function actionWithdraw()
	{
		$iUserid = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if( $iUserid==0 )
		{
			redirect( url( "user" ,"view" ) );
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser($iAdmin, $iUserid) )
		{
			sysMessage( '您的权限不足', 1 );
		}
		$oUserFund = new model_userfund();
		if( $oUser->isTopProxy($iUserid) )
		{
			$aUserFund   = $oUserFund->getFundByUser( $iUserid, 'u.`userid`,u.`username`,ut.`parentid`,uf.*', 0, FALSE );
			$fUserTeam   = $oUser->getTeamBank($iUserid);//团队余额
			$aUserCredit = $oUserFund->getUserCredit($iUserid);//信用资金
			$GLOBALS['oView']->assign("credit",$aUserCredit['proxyvalue'] );
			$fUserMax1   = $fUserTeam - $aUserCredit["proxyvalue"];
			$fUserMax2   = $aUserFund["availablebalance"];
			$fuserMax    = min( $fUserMax1, $fUserMax2 );
		}
		else
		{
			sysMessage( '用户和代理不能进行提现,请使用跨级提现', 1 );
		}
		$GLOBALS["oView"]->assign( "userMax",    $fuserMax );		
		$oChannel  = new model_channels();
		$aChannels = $oChannel->channelGetAll('',"`pid`='0'");
		$GLOBALS['oView']->assign( "userfund",   $aUserFund );
		$GLOBALS['oView']->assign( 'channels',   $aChannels );		
		$GLOBALS['oView']->assign( "ur_here",    "用户提现" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view",array('userid'=>$iUserid) ), 'text'=>'用户列表' ) );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_withdraw.html");
		EXIT;
	}



	/**
	 * 用户提现(处理)
	 * URL =./?controller=user&action=savewithdraw
	 * @author SAUL 090517
	 */ 
	function actionSavewithdraw()
	{
		$iUserId      = isset($_POST["userid"])&&is_numeric($_POST["userid"])     ? intval($_POST["userid"])   : 0;
		$aLocation    = array( 0=>array( "text" => '用户管理', "href" => url( "user" , "view" ,array( "userid" => $iUserId ) ) ) );
		$iAdmin       = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0; 			
		$fMoney       = isset($_POST["money"])&&is_numeric($_POST["money"])       ? doubleval($_POST["money"]) : 0.00;
		$iPayment     = isset($_POST["payment"])&&is_numeric($_POST["payment"])   ? intval($_POST["payment"])  : 0;
		$iChannel     = isset($_POST["channel"])&&is_numeric($_POST["channel"])   ? intval($_POST["channel"])  : 0;
		$sDescription = isset($_POST["description"])                              ? $_POST["description"]      : "";
		$oUser        = new model_user();
	    if ( !$oUser->isTopProxy( $iUserId ) )
		{
			sysMessage( '代理和用户不支持提现操作', 1, $aLocation );
		}
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1, $aLocation );
		}
		if( ($iUserId==0) || ($fMoney<=0.00) )
		{
			redirect( url("user", "view", array( 'userid' => $iUserId ) ) );//用户为空的时候，已经充值为0.00的时候不执行
		}
		if( $iPayment ==0 )
		{
			$iChannel = 0;//只向银行频道加钱
		}
		
		// 二次审核
		$aValue = array();
		$aValue['user_id'] = $iUserId;
		$aValue['fmoney'] = $fMoney;
		$aUserInfo = $oUser->getUserExtentdInfo($iUserId);
		$oOrders = new model_orders();
		if ($iPayment == 0){ // 本人提现 or 跨级提现
			if ($aUserInfo['groupid'] == 1 || $aUserInfo['isspecial'] == 1){ // 总代
				$aValue['freeze']   = ORDER_TYPE_RGTXJD;
				$aValue['order_type']   = ORDER_TYPE_BRTX;
	            $aValue['description'] = "本人提现:".$sDescription;
			} else {
				$aValue['freeze']   = ORDER_TYPE_RGJD;
				$aValue['order_type']   = ORDER_TYPE_KJTX;
	            $aValue['description'] = "跨级提现:".$sDescription;
			}
		} else { // 管理员扣减
			$aValue['freeze']   = ORDER_TYPE_RGTXJD;
			$aValue['order_type']   = ORDER_TYPE_GLYKJ;
            $aValue['description'] = "管理员扣减:".$sDescription;
		}
		$oSecondVerify = new model_secondverify();
		$oSecondVerify->NickName = "withdraw";
		$oSecondVerify->Value = $aValue;
		$mResult = $oSecondVerify->index();
		switch ($mResult){
			case -14 :
				sysMessage( '数据错误', 1, $aLocation );
			break;
			case -15 :
				sysMessage( '用户不存在', 1, $aLocation );
			break;
			case -16 :
				sysMessage( '锁用户账户失败', 1, $aLocation );
			break;
			case -17 :
				sysMessage( '获取用户频道余额失败', 1, $aLocation );
			break;
			case -18 :
				sysMessage( '其它频道有负余额，不允许提现', 1, $aLocation );
			break;
			case -19 :
				sysMessage( '获取总代信用失败', 1, $aLocation );
			break;
			case -20 :
				sysMessage( '提现金额超出可提现金额', 1, $aLocation );
			break;
			case -21 :
				sysMessage( '冻结用户提现金额失败', 1, $aLocation );
			break;
			case -22 :
				sysMessage( '写入提现申请记录失败', 1, $aLocation );
			break;
			case $mResult > 0 :
				sysMessage( '操作成功，请联系管理员审核后生效', 0, $aLocation );
			break;
			default :
				sysMessage( '操作失败，未失错误，请联系管理员', 1, $aLocation );
			break;
		}
		/*
		$oUserFund = new model_userfund();
		if( $iPayment == 0 )
		{ // 执行管理员给用户提现
			$iFlag = $oUserFund->admintoUserWithDraw( $iAdmin, $iUserId, $fMoney, $sDescription, 0 );
			switch ($iFlag)
			{
				case -1:
					sysMessage( '数据不全', 1, $aLocation );
					break;
				case -2:
					sysMessage( '用户资金被锁中', 1, $aLocation );
					break;
				case -3:
					sysMessage( '用户余额不足', 1, $aLocation );
					break;
				case -4:
					sysMessage( '用户提现成功之后帐号被锁', 1, $aLocation );
					break;
				case -1001:
					sysMessage( '用户ID错误', 1, $aLocation );
					break;
				case -1002:
					sysMessage( '账变类型ID错误', 1, $aLocation );
					break;
				case -1003:
					sysMessage( '账变金额错误, 不允许负数', 1, $aLocation );
					break;
				case -1004:
					sysMessage( '获取用户频道资金数据失败', 1, $aLocation );
					break;
				case -1005:
					sysMessage( '用户资金账户未被锁', 1, $aLocation );
					break;
				case -1006:
					sysMessage( '账变类型错误,未被程序枚举处理', 1, $aLocation );
					break;
				case -1007:
					sysMessage( '账变记录插入失败', 1, $aLocation );
					break;
				case -1008:
					sysMessage( '账户金额更新失败', 1, $aLocation );
					break;
				case -1009:
					sysMessage( '金额不正确', 1, $aLocation );
					break;	
				case 1:
					sysMessage( '操作成功', 0, $aLocation );
					break;
				default:
					sysMessage( '未知错误,请联系技术人员', 1, $aLocation );
					break;
			}
		}
		else
		{  //执行用户理赔充值
			$iFlag = $oUserFund->admintoUserPayWithDraw( $iAdmin, $iUserId, $fMoney, $sDescription, $iChannel );
			switch ($iFlag)
			{
				case -1:
					sysMessage( '数据不全', 1, $aLocation );
					break;
				case -2:
					sysMessage( '用户资金被锁中', 1, $aLocation );
					break;
				case -3:
					sysMessage( '用户余额不足', 1, $aLocation );
					break;
				case -4:
					sysMessage( '用户理赔提现成功之后帐号被锁', 1, $aLocation );
					break;
				case -1001:
					sysMessage( '用户ID错误', 1, $aLocation );
					break;
				case -1002:
					sysMessage( '账变类型ID错误', 1, $aLocation );
					break;
				case -1003:
					sysMessage( '账变金额错误, 不允许负数', 1, $aLocation );
					break;
				case -1004:
					sysMessage( '获取用户频道资金数据失败', 1, $aLocation );
					break;
				case -1005:
					sysMessage( '用户资金账户未被锁', 1, $aLocation );
					break;
				case -1006:
					sysMessage( '账变类型错误,未被程序枚举处理', 1, $aLocation );
					break;
				case -1007:
					sysMessage( '账变记录插入失败', 1, $aLocation );
					break;
				case -1008:
					sysMessage( '账户金额更新失败', 1, $aLocation );
					break;
				case -1009:
					sysMessage( '金额不正确', 1, $aLocation );
					break;	
				case 1:
					sysMessage( '操作成功', 0, $aLocation );
					break;
				default:
					sysMessage( '未知错误,请联系技术人员', 1, $aLocation  );
					break;
			}
		}*/
	}



	/**
	 * 对资金操作的确认
	 * URL = ./?controller=user&action=confirm
	 * @author SAUL
	 */
	function actionConfirm(){
		$sNext  = isset($_GET["next"])?$_GET["next"]:"view";
		$iPayment     = isset($_POST["payment"])&&is_numeric($_POST["payment"])   ? intval($_POST["payment"])  : 0;
		$iManual      = isset($_POST["manual"])&&is_numeric($_POST["manual"])     ? intval($_POST["manual"])  : 0;
		$fmoney 	  = isset($_POST["money"])&&is_numeric($_POST["money"]) 	  ? doubleval($_POST["money"]) : 0.00;
		$fFee 		  = isset($_POST["fee"])&&is_numeric($_POST["fee"]) 		  ? doubleval($_POST["fee"]) : 0.00;
		$iIsFee 	  = isset($_POST["isfee"])&&is_numeric($_POST["isfee"]) 	  ? intval($_POST["isfee"]) : 0;
		if( $fmoney <=0 )
		{
			sysMessage( '变化资金不正确', 1 );
			exit;
		}
		
		// 理赔充值和人工充值不能同时选择
		if ($iPayment === 1 && $iManual === 1){
			sysMessage( '不能同时选择理赔充值和人工充值', 1, $aLocation );
			exit;
		}
		
		// 如果有手续费,必须与人工充值一并使用
		if ($iIsFee === 1){
			if ($iManual !== 1){
				sysMessage( '手续费必须与人工充值一并使用', 1, $aLocation );
				exit;
			} else { // 手续费金额不能高于100
				if ($fFee <= 0 || $fFee >= 100){
					sysMessage( '手续费金额不正确', 1, $aLocation );
					exit;
				}
			}
		}
		
		$iChannel = isset($_POST["channel"]) ? $_POST["channel"] : 0;
		$sDescription = isset($_POST["description"]) ? $_POST["description"] : "";
		$GLOBALS['oView']->assign( "next",    $sNext );
		$GLOBALS['oView']->assign( "money",   $fmoney );
		$GLOBALS['oView']->assign( "fee",   $fFee );
		$GLOBALS['oView']->assign( "channel", $iChannel );
		$GLOBALS['oView']->assign( "description", daddslashes($sDescription) );
		if( $sNext == "savetopup" ) //相关充值
		{
			if( isset($_POST["payment"]) )
			{
				$sTitle   = "理赔充值";
				$sTitle2  = "理赔资金";
				$sTitle3  = "理赔理由";
				$iPayMent = is_numeric($_POST["payment"])?intval($_POST["payment"]) : 0;
				$GLOBALS['oView']->assign( "payment", $iPayMent );
			} elseif (isset($_POST["manual"])){
				$sTitle   = "人工充值";
				$sTitle2  = "充值资金";
				$sTitle3  = "充值理由";
				$iMoney = is_numeric($_POST["manual"])?intval($_POST["manual"]) : 0;
				$GLOBALS['oView']->assign( "manual", $iMoney );
				if (isset($_POST["isfee"])){
					$sTitleFee2  = "手续费金额";
					$iIsFee = is_numeric($_POST["isfee"])?intval($_POST["isfee"]) : 0;
					$GLOBALS['oView']->assign( "isfee", $iIsFee );
				}
			}
			else 
			{
				$sTitle   = "用户充值";
				$sTitle2  = "充值资金";
				$sTitle3  = "充值备注";
			}
		}
		elseif( $sNext == "savewithdraw" )//总代提现
		{
			if( isset($_POST["payment"]) )
			{
				$sTitle   = "理赔扣款";
				$sTitle2  = "理赔资金";
				$sTitle3  = "理赔理由";
				$iPayMent = is_numeric($_POST["payment"]) ? intval($_POST["payment"]) : 0;
				$GLOBALS['oView']->assign( "payment", $iPayMent );
			}
			else 
			{
				$sTitle   = "用户提现";
				$sTitle2  = "提现资金";
				$sTitle3  = "提现备注";
			}
		}
		elseif( $sNext == "savecredit" )//信用欠款操作
		{
			$credit = isset($_POST["credit"]) ? $_POST["credit"]: "";
			if( $credit == "add" )
			{
				$sTitle   = "信用充值";
				$sTitle2  = "充值信用";
				$sTitle3  = "充值理由";
			}
			elseif( $credit == "sub" )
			{
				$sTitle   = "信用扣减";
				$sTitle2  = "扣减信用";
				$sTitle3  = "提现备注";
			}
			else
			{
				sysMessage( '操作失败', 1 );
			}
			$GLOBALS['oView']->assign( "credit", $credit );
		}
		elseif ( $sNext=="savekuati" )//跨提确认
		{
			if( isset($_POST["payment"]) )
			{
				$sTitle   = "理赔跨级扣款";
				$sTitle2  = "理赔资金";
				$sTitle3  = "理赔理由";
				$iPayMent = is_numeric($_POST["payment"]) ? intval($_POST["payment"]) : 0;
				$GLOBALS['oView']->assign("payment",$iPayMent);
			}
			else
			{
				$sTitle   = "用户跨级提现";
				$sTitle2  = "跨级提现资金";
				$sTitle3  = "跨级提现备注";
			}			
		}
		else 
		{
			sysMessage( '操作失败', 1 );
		}
		$iUserId     = isset($_POST["userid"])&&is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
		if( $iUserId==0 )
		{
			redirect( url( "user", "view" ) );
		}
		$oUserfund   = new model_userfund();
		$aUser       = $oUserfund->getFundbyUser( $iUserId, 'u.*,uf.*', 0, FALSE );
		$fUserCredit = $oUserfund->getUserCredit( $iUserId );
		if( !empty($fUserCredit) )
		{
			$GLOBALS['oView']->assign('userCredit', $fUserCredit['proxyvalue'] );
		}
		$GLOBALS['oView']->assign( "ur_here", $sTitle );
		$GLOBALS['oView']->assign( "title2",  $sTitle2 );
		$GLOBALS['oView']->assign( "title3",  $sTitle3 );
		$GLOBALS['oView']->assign( "titlefee2",  $sTitleFee2 );
		$GLOBALS['oView']->assign( "user",    $aUser );
		$GLOBALS['oView']->display("user_confirm.html");
		EXIT;
	}



	/**
	 * 对代理和用户进行跨提处理
	 * URL = ./?controller=user&action=savekuati
	 * @author SAUL
	 */
	function actionSavekuati()
	{
		$iUserId      = isset($_POST["userid"])&&is_numeric($_POST["userid"])     ? intval($_POST["userid"])   : 0;
		$iAdmin       = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;		
		$fMoney       = isset($_POST["money"])&&is_numeric($_POST["money"])       ? doubleval($_POST["money"]) : 0.00;
		$iPayMent     = isset($_POST["payment"])&&is_numeric($_POST["payment"])   ? intval($_POST["payment"])  : 0;
		$iChannel     = isset($_POST["channel"])&&is_numeric($_POST["channel"])   ? intval($_POST["channel"])  : 0;
		$sDescription = isset($_POST["description"])                              ? $_POST["description"]      : "";
		$aLocation    = array(0=>array("text"=>'用户管理',"href"=>url("user","view",array("userid"=>$iUserId))));
	    $oUser        = new model_user();
		if( !$oUser->checkAdminForUser($iAdmin,$iUserId) )
		{
			sysMessage( '您的权限不足', 1, $aLocation );
		}
		if( $oUser->isTopProxy( $iUserId ) )
		{
			sysMessage( '总代不支持跨级提现操作', 1, $aLocation );
		}
		if( ( $iUserId==0 ) || ( $fMoney<=0.00 ) )
		{
			redirect( url( "user", "view", array('userid' => $iUserId) ) );//用户为空的时候，已经充值小于0.00的时候不执行
		}
		if( $iPayMent==0 )
		{
			$iChannel = 0;//只向银行频道加钱
		}		
		// 二次审核
		$aValue = array();
		$aValue['user_id'] = $iUserId;
		$aValue['fmoney'] = $fMoney;
		$aUserInfo = $oUser->getUserExtentdInfo($iUserId);
		$oOrders = new model_orders();
		if ($iPayMent == 0){ // 本人提现 or 跨级提现
			if ($aUserInfo['groupid'] == 1 || $aUserInfo['isspecial'] == 1){ // 总代
				$aValue['freeze']   = ORDER_TYPE_RGTXJD;
				$aValue['order_type']   = ORDER_TYPE_BRTX;
	            $aValue['description'] = "本人提现:".$sDescription;
			} else {
				$aValue['freeze']   = ORDER_TYPE_RGTXJD;
				$aValue['order_type']   = ORDER_TYPE_KJTX;
	            $aValue['description'] = "跨级提现:".$sDescription;
			}
		} else { // 管理员扣减
			$aValue['freeze']   = ORDER_TYPE_RGTXJD;
			$aValue['order_type']   = ORDER_TYPE_GLYKJ;
            $aValue['description'] = "管理员扣减:".$sDescription;
		}
		$oSecondVerify = new model_secondverify();
		$oSecondVerify->NickName = "withdraw";
		$oSecondVerify->Value = $aValue;
		$mResult = $oSecondVerify->index();
		switch ($mResult){
			case -14 :
				sysMessage( '数据错误', 1, $aLocation );
			break;
			case -15 :
				sysMessage( '用户不存在', 1, $aLocation );
			break;
			case -16 :
				sysMessage( '锁用户账户失败', 1, $aLocation );
			break;
			case -17 :
				sysMessage( '获取用户频道余额失败', 1, $aLocation );
			break;
			case -18 :
				sysMessage( '其它频道有负余额，不允许提现', 1, $aLocation );
			break;
			case -19 :
				sysMessage( '获取总代信用失败', 1, $aLocation );
			break;
			case -20 :
				sysMessage( '提现金额超出可提现金额', 1, $aLocation );
			break;
			case -21 :
				sysMessage( '冻结用户提现金额失败', 1, $aLocation );
			break;
			case -22 :
				sysMessage( '写入提现申请记录失败', 1, $aLocation );
			break;
			case $mResult > 0 :
				sysMessage( '操作成功，请联系管理员审核后生效', 0, $aLocation );
			break;
			default :
				sysMessage( '操作失败，未失错误，请联系管理员', 1, $aLocation );
			break;
		}
		/*$oUserFund = new model_userfund();
		if($iPayMent == 0 )
		{ //执行管理员给用户提现
			$iFlag = $oUserFund->admintoUserWithDraw( $iAdmin, $iUserId, $fMoney, $sDescription, 0 );
			switch ( $iFlag )
			{
				case -1:
					sysMessage( '数据不全', 1, $aLocation );
					break;
				case -2:
					sysMessage( '用户资金被锁中', 1, $aLocation );
					break;
				case -3:
					sysMessage( '用户余额不足', 1, $aLocation );
					break;
				case -4:
					sysMessage( '用户提现成功之后帐号被锁', 1, $aLocation );
					break;
				case -1001:
					sysMessage( '用户ID错误', 1, $aLocation );
					break;
				case -1002:
					sysMessage( '账变类型ID错误', 1, $aLocation );
					break;
				case -1003:
					sysMessage( '账变金额错误, 不允许负数', 1, $aLocation );
					break;
				case -1004:
					sysMessage( '获取用户频道资金数据失败', 1, $aLocation );
					break;
				case -1005:
					sysMessage( '用户资金账户未被锁', 1, $aLocation );
					break;
				case -1006:
					sysMessage( '账变类型错误,未被程序枚举处理', 1, $aLocation );
					break;
				case -1007:
					sysMessage( '账变记录插入失败', 1, $aLocation );
					break;
				case -1008:
					sysMessage( '账户金额更新失败', 1, $aLocation );
					break;
				case -1009:
					sysMessage( '金额不正确', 1, $aLocation );
					break;	
				case 1:
					sysMessage( '操作成功', 0, $aLocation );
					break;
				default:
					sysMessage( '未知错误,请联系技术人员', 1, $aLocation );
					break;
			}
		}
		else
		{ //执行用户理赔充值
			$iFlag = $oUserFund->admintoUserPayWithDraw( $iAdmin, $iUserId, $fMoney, $sDescription, $iChannel );
			switch ( $iFlag )
			{
				case -1:
					sysMessage( '数据不全', 1, $aLocation );
					break;
				case -2:
					sysMessage( '用户资金被锁中', 1, $aLocation );
					break;
				case -3:
					sysMessage( '用户余额不足', 1, $aLocation );
					break;
				case -4:
					sysMessage( '用户理赔提现成功之后帐号被锁', 1, $aLocation );
					break;
				case -1001:
					sysMessage( '用户ID错误', 1, $aLocation );
					break;
				case -1002:
					sysMessage( '账变类型ID错误', 1, $aLocation );
					break;
				case -1003:
					sysMessage( '账变金额错误, 不允许负数', 1, $aLocation );
					break;
				case -1004:
					sysMessage( '获取用户频道资金数据失败', 1, $aLocation );
					break;
				case -1005:
					sysMessage( '用户资金账户未被锁', 1, $aLocation );
					break;
				case -1006:
					sysMessage( '账变类型错误,未被程序枚举处理', 1, $aLocation );
					break;
				case -1007:
					sysMessage( '账变记录插入失败', 1, $aLocation );
					break;
				case -1008:
					sysMessage( '账户金额更新失败', 1, $aLocation );
					break;
				case -1009:
					sysMessage( '金额不正确', 1, $aLocation );
					break;	
				case 1:
					sysMessage( '操作成功', 0, $aLocation );
					break;
				default:
					sysMessage( '未知错误,请联系技术人员', 1, $aLocation );
					break;
			}
		}*/
	}



	/**
	 * 总代信用欠款处理
	 * URL = ./?controller=user&action=credit
	 * @author SAUL 090527
	 */
	function actionCredit()
	{
		$iUserId     = isset($_GET["userid"])&&is_numeric($_GET["userid"])       ? intval($_GET["userid"])    : 0;
		$iAdmin      = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( $iUserId == 0 )
		{
			redirect( url( "user", "view" ) );
		}
		$oUser       = new model_user();		
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1 );
		}
		if( !$oUser->isTopProxy($iUserId) )
		{
			redirect( url( "user", "view", array( "userid" => $iUserId ) ) );
		}
		$oUserFund   = new model_userfund();
		$aUserFund   = $oUserFund->getFundByUser( $iUserId, 'u.`userid`,u.`username`,uf.*', 0, FALSE );
		$aUserCredit = $oUserFund->getUserCredit( $iUserId );
		$fUserMax    = min( $aUserCredit["proxyvalue"], $aUserFund["availablebalance"] ); 
		$GLOBALS['oView']->assign( "ur_here",    "用户信用欠款" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view",array('userid'=>$iUserId)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( "userMax",    $fUserMax );
		$GLOBALS['oView']->assign( "userfund",   $aUserFund );
		$GLOBALS['oView']->assign( "usercredit", $aUserCredit );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_credit.html");
		EXIT;
	}



	/**
	 * 执行信用增加或者减少(执行)
	 * URL = ./?controller=user&action=savecredit
	 * @author SAUL 090517
	 */
	function actionSavecredit()
	{
		$iUserId   = isset($_POST["userid"])&&is_numeric($_POST["userid"])     ? intval($_POST["userid"])   : 0;
		$iAdmin    = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		$oUser     = new model_user();
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1 );
		}
		$sCredit   = isset($_POST["credit"]) ? daddslashes($_POST["credit"]) : "";
		$fMoney    = isset($_POST["money"])&&is_numeric($_POST["money"])       ? doubleval($_POST["money"]) : 0.00;
		if($iUserId == 0)
		{
			redirect( url("user","view") );
		}
		if( !in_array( $sCredit, array('add','sub') ) )
		{
			redirect( url( "user", "view", array( 'userid' => $iUserId ) ) );
		}
		if( $fMoney<=0.00 )
		{
			redirect( url( "user", "credit", array( 'userid' => $iUserId ) ) );
		}
		$oUserFund  = new model_userfund();		
		$iFlag = $oUserFund->changeUserCredit( $iAdmin, $iUserId, $fMoney, $sCredit );
		$aLocation1 = array( 0=>array( 'text'=>'用户管理', 'href'=>url( 'user', 'view' ) ) );
		$aLocation2 = array( 0=>array( 'text'=>'用户管理', 'href'=>url( 'user', 'view', array( 'userid', $iUserId) ) ) );
		switch( $iFlag )
		{
			case 0:
				sysMessage( '非法参数', 1, $aLocation1 );
				break;
			case -1:
				sysMessage( '用户不是总代', 1, $aLocation1 );
				break;
			case -2:
				redirect( url( "user", "view", array( 'userid' => $iUserId ) ) );
				break;
			case -3:
				sysMessage( '帐户锁定中' , 1, $aLocation2 );
				break;
			case -4:
				sysMessage( '帐户出现问题', 1, $aLocation2 );
				break;
			case -5:
				sysMessage( '用户信用资金不够扣减', 1, $aLocation2 );
				break;
			case -8:
				sysMessage( '更新用户信用时候失败', 1, $aLocation1 );
				break;
			case -9:
				sysMessage( '更新用户信用成功后,用户解锁失败', 1, $aLocation1 );
				break;
			case -1001:
				sysMessage( '用户ID错误', 1, $aLocation2 );
				break;
			case -1002:
				sysMessage( '账变类型ID错误', 1, $aLocation1 );
				break;
			case -1003:
				sysMessage( '账变金额错误, 不允许负数', 1, $aLocation2 );
				break;
			case -1004:
				sysMessage( '获取用户频道资金数据失败', 1, $aLocation2 );
				break;
			case -1005:
				sysMessage( '用户资金账户未被锁', 1, $aLocation2 );
				break;
			case -1006:
				sysMessage( '账变类型错误,未被程序枚举处理', 1, $aLocation2 );
				break;
			case -1007:
				sysMessage( '账变记录插入失败', 1, $aLocation2 );
				break;
			case -1008:
				sysMessage( '账户金额更新失败', 1, $aLocation2 );
				break;
			case -1009:
				sysMessage( '金额不正确', 1, $aLocation2);
				break;
			case 1:
				sysMessage( '更新用户信用成功', 0, $aLocation1 );
				break;
			default:
				sysMessage( '未知错误,请联系技术人员', 1, $aLocation1 );
				break;
		}
	}



	/**
	 * 分配模板
	 * URL = ./?controller=user&action=assignskin
	 * @author Tom
	 */
	function actionAssignskin()
	{
	    $aLocation  = array( 0=>array( 'text' => '分配模板' , 'href'=> url( 'user', 'assignskin')));
	    
	    if( !empty($_POST) && isset($_POST['act']) && $_POST['act']=='TRUE' )
	    { // 处理 "执行分配" 
	        if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sysMessage("未选择数据", 1 );
            }

            // 2, 可用模板数组
            $oUserSkin = new model_userskins();
	        $aAvailableSkin = $oUserSkin->getDistintSkins();
            $aUpdateAgent = array();  // 整理过的, 总代<=>域名 关系数组
            /**
             * $aUpdateAgent = arrary(
             *     'userid' => 35,
             *     'skin'   => 'default' ....
             * )
             */
            foreach( $_POST['checkboxes'] AS $iUserid )
            {
                if( is_numeric($iUserid) && $iUserid >0 && isset($_POST['skins'][$iUserid]) 
                    && in_array( $_POST['skins'][$iUserid], $aAvailableSkin ) )
                {
                    $aUpdateAgent[] = array( 'userid'=>intval($iUserid), 'skins'=> $_POST['skins'][$iUserid] );
                }
            }
            if( empty($aUpdateAgent) )
            {
                sysMessage("提交数据无效", 1 );
            }

            if( $oUserSkin->updateUserSkinRelation($aUpdateAgent) )
            {
                sysMessage("操作成功", 0, $aLocation );
            }
            else 
            {
                sysMessage("操作失败", 0, $aLocation );
            }
            EXIT;
	    }


		// 1, 读取所有总代, 组合结果集, 显示页面
        $oUserSkin = new model_userskins();
        $aUserSkinList = $oUserSkin->getTopProxyResult();
        foreach( $aUserSkinList AS $k=>$v )
        {
            $aUserSkinList[$k]['opts'] = $oUserSkin->getDistintSkins(FALSE, $v['skins'] );
        }
		$GLOBALS['oView']->assign( "ur_here",    "分配模板" );
		$GLOBALS['oView']->assign( "aDataList",    $aUserSkinList );
		$GLOBALS['oView']->display("user_assignskin.html");
		EXIT;
	}



	/**
	 * 删除用户
	 * URL = ./?controller=user&action=del
	 * @author
	 */
	function actionDel()
	{
		die("未完成:用户删除,当前操作受API的影响。");
	}



	/**
	 * 用户过滤器列表
	 * URL = ./?controller=user&action=userfilterlist
	 *    - 显示每个管理员增加自己命名的 "缓存用户过滤结果"
	 *    - 实现对缓存结果集进行条件搜索 
	 * 完成度:0%
	 */
	function actionUserfilterlist()
	{
	    die(" 1, 用户过滤 - 列表 - 待完成 (二期)");
	}



	/**
	 * 增加用户过滤器
	 * URL = ./?controller=user&action=userfilteradd
	 *   - 根据管理员指定的各种搜索条件, 通过 API 实现跨服务器用户过滤
	 *   - 获取用户 ID 结果集后, 简单提示符合条件规则的用户的总数count() 
	 *   - 提示管理员为本次结果集命名(写入缓存表) 
	 * 完成度:0%
	 */
	function actionUserfilteradd()
	{
	    die(" 2, 用户过滤 - 列表 - 待完成 (二期)");
	}



	/**
	 * 用户过滤器详情
	 * URL = ./?controller=user&action=userfilterdetail
	 *    - 允许查看已经存在的, 由管理员命名的缓存结果集详情
	 *    - 根据缓存表中记录的用户id, 获取用户相关资料(显示的相关字段待定)
	 * 完成度:0%
	 */
	function actionUserfilterdetail()
	{
	    die("3, 用户过滤 - 详情 - 待完成 (二期)");
	}



	/**
	 * 删除用户过滤器
	 * URL = ./?controller=user&action=userfilterlist
	 * 完成度:0%
	 */
	function actionUserfilterdel()
	{
	    die("4, 用户过滤 - 删除 - 待完成 (二期)");
	}
	
	
	/**
	 * 授权在线充提权限
	 * 3/15/2010 Jim
	 */
	public function actionAuthPayment()
	{		
		 
		$oAgent     = new model_agent();
		$iPaymentLevel = isset($_GET['payment_level'])&&is_numeric($_GET['payment_level']) ? intval($_GET['payment_level']) : -2;
		$iTopSet = isset($_GET['topset'])&&is_numeric($_GET['topset']) ? intval($_GET['topset']) : 0;
	
		
		$aWhere = array( $iPaymentLevel, ($iTopSet-1) );
		$aUsers = $oAgent->userAuthPayment( $aWhere );//获取总代设置

		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","authpayment"), 'text'=>'清空过滤条件' ) );		
		$GLOBALS["oView"]->assign( "users",      $aUsers );
		$GLOBALS["oView"]->assign( "topset",     $iTopSet );
		$GLOBALS["oView"]->assign( "payment_level",  $iPaymentLevel );
		$GLOBALS["oView"]->assign( "ur_here",    "在线充提权限" );
		$oAgent->assignSysInfo();
		$GLOBALS["oView"]->display("user_authpayment.html");
		unset($oAgent);
		exit;
	}
	
	
	/**
	 * 保存 在线充提用户授权
	 * 3/15/2010 JIm
	 */
	public function actionSaveAuthPayment()
	{
		$aLocation  = array( 0=>array( 'text' => '授权在线充值' , 'href'=> url( 'user', 'authpayment')));
		
		$aUser = isset($_POST["userid"])&&is_array($_POST["userid"]) ? daddslashes($_POST["userid"]) : array();
		if( empty($aUser) ){
			sysMessage('失败:没有数据提交', 1);
		}
		$aPaymentLevel = isset($_POST["payment_level"])&&is_array($_POST["payment_level"]) ? $_POST["payment_level"] : array();
		$aAllow     = isset($_POST["allow"])&&is_array($_POST["allow"])  ?  $_POST["allow"]   :  array();

		$oAgent     = new model_agent();
		$iFlag      = $oAgent->updateAuthPayment($aUser, $aPaymentLevel, $aAllow );

		if($iFlag == 1)
		{
			sysMessage('操作成功', 0, $aLocation);
			unset($oAgent);
			exit;
		}
		else		
		{
			sysMessage('操作失败', 1, $aLocation);
			unset($oAgent);
			exit;
		}
	}
	
	
	/**
	 * 授权 银行充值 层级权限
	 * 	11/17/2010 Jim
	 */
	public function actionAuthDepositLv()
	{		
		 
		$oAgent     = new model_agent();
		$iPaymentLevel = isset($_GET['payment_level'])&&is_numeric($_GET['payment_level']) ? intval($_GET['payment_level']) : -2;
		$iTopSet = isset($_GET['topset'])&&is_numeric($_GET['topset']) ? intval($_GET['topset']) : 0;
		
		//提取系统中使用的受付银行列表
		$oDeposit		= new model_deposit_depositlist(array(),'','array');
    	$aDepositList 	= $oDeposit->getDepositArray('all');
		$aBankIdArray 	= $aDepositList[0];
		$aBankName		= $aDepositList[1];
		$aBankArray		= $aDepositList[2];
		
		$iDepositbankid = intval($_GET['depositbankid']);
		if (  !is_numeric($iDepositbankid) || $iDepositbankid <= 0 
			|| array_search( $iDepositbankid, $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			$GLOBALS['oView']->assign("ur_here",   "选择操作银行");
			$GLOBALS['oView']->assign("controllerstr", 'user');
			$GLOBALS['oView']->assign("actionstr", 'authdepositlv');
			$GLOBALS['oView']->assign("depositbanklist",   $aBankArray);
					$oDeposit->assignSysInfo();
			$GLOBALS['oView']->display("deposit_choosebank.html");
			EXIT;
		}
		
		$aWhere = array( $iPaymentLevel, ($iTopSet-1) );
		$aUsers = $oAgent->userAuthDepositLv( $aWhere, $iDepositbankid);//获取总代设置
		
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","authdepositlv"), 'text'=>'清空过滤条件' ) );		
		$GLOBALS["oView"]->assign( "users",      $aUsers );
		$GLOBALS["oView"]->assign( "topset",     $iTopSet );
		$GLOBALS["oView"]->assign( "payment_level",  $iPaymentLevel );
		$GLOBALS["oView"]->assign( "depositbanklist",  $aBankArray );
		$GLOBALS["oView"]->assign( "depositbankid",  $iDepositbankid );
		$GLOBALS["oView"]->assign( "depositbankname", $aBankName[$iDepositbankid] );
		$GLOBALS["oView"]->assign( "ur_here",    "快速充值层级权限" );
				  $oAgent->assignSysInfo();
		$GLOBALS["oView"]->display("user_authdepositlv.html");
		unset($oAgent,$oDeposit);
		exit;
	}
	
	
	/**
	 * 保存 快速充提层级权限
	 * 11/17/2010 JIm
	 */
	public function actionSaveAuthDepositLv()
	{
		$iDepositbankid = intval($_POST['depositbankid']);
		
		$aLocation  = array( 0=>array( 'text' => '快速充值层级权限' , 'href'=> url( 'user', 'authdepositlv', array('depositbankid'=>$iDepositbankid) )));
		
		//提取系统中使用的受付银行列表
		$oDeposit		= new model_deposit_depositlist(array(),'','array');
    	$aDepositList 	= $oDeposit->getDepositArray('all');
		$aBankIdArray 	= $aDepositList[0];
		
		
		if (  !is_numeric($iDepositbankid) || $iDepositbankid <= 0 
			|| array_search( $iDepositbankid, $aBankIdArray) === FALSE )
		{
			sysMessage('操作失败', 1, $aLocation);
			unset($oDeposit);
			EXIT;
		}
		
		$aUser = isset($_POST["userid"])&&is_array($_POST["userid"]) ? daddslashes($_POST["userid"]) : array();
		if( empty($aUser) ){
			sysMessage('失败:没有数据提交', 1);
		}
		$aPaymentLevel = isset($_POST["payment_level"])&&is_array($_POST["payment_level"]) ? $_POST["payment_level"] : array();
		$aAllow     = isset($_POST["allow"])&&is_array($_POST["allow"])  ?  $_POST["allow"]   :  array();

		$oAgent     = new model_agent();
		$iFlag      = $oAgent->updateAuthDepositLv($aUser, $aPaymentLevel, $aAllow, $iDepositbankid );

		if($iFlag == 1)
		{
			sysMessage('操作成功', 0, $aLocation);
			unset($oAgent,$oDeposit);
			exit;
		}
		else		
		{
			sysMessage('操作失败2', 1, $aLocation);
			unset($oAgent,$oDeposit);
			exit;
		}
	}
	
	
	/**
	 * 查看用户绑定的银行卡信息
	 *
	 * @version 	v1.0	2010-04-14
	 * @author 		louis
	 */
	public function actionBinding(){
		$_GET['userid'] or sysMessage("您提交的用户数据错误，请核对后重新提交！", 0);	
		
		// 获取用户在使用的银行卡绑定信息
		$oUserBankList = new model_withdraw_UserBankList();
		$oUserBankList->UserId	= $_GET['userid'];
		$oUserBankList->Status	= 1;
		$oUserBankList->init();
		$aAvailableBank = $oUserBankList->Data;
		
		$oFODetail = new model_withdraw_fundoutdetail();
        $oFODetail->Digit = 4; // 只显示四位卡号
        foreach ($aAvailableBank as $k => $value){
    		// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
    		$oFODetail->Account = $value['account'];
    		$aAvailableBank[$k]['account'] = $oFODetail->hiddenAccount();
    	}
		
		// 获取用户以前绑定过的信息记录
		$oUserBankList->Status	= "0,2";
		$oUserBankList->init();
		$aUnavailableBank = $oUserBankList->Data;
		
        foreach ($aUnavailableBank as $k => $value){
    		// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
    		$oFODetail->Account = $value['account'];
    		$aUnavailableBank[$k]['account'] = $oFODetail->hiddenAccount();
    	}
		$GLOBALS["oView"]->assign( "ur_here",    "用户绑定信息" );
		$GLOBALS["oView"]->assign( "available_bank",    $aAvailableBank );
		$GLOBALS["oView"]->assign( "unavailable_bank",    $aUnavailableBank );
		$oUserBankList->assignSysInfo();
		$GLOBALS["oView"]->display("user_binding.html");
		EXIT;
	}
	
	
	/**
	 *  后台管理直接删除用户绑定的银行卡
	 * 
	 *  11/18/2010
	 * 
	 *  jIM
	 */
	public function actionDelUserBank(){
		
		$iCardId = intval( $_GET['cardid']);
		$iUserId = intval( $_GET['userid']);
		
		$aLocation  = array( 0=>array( 'text' => '用户绑定信息' , 'href' => url( 'user', 'binding', array( 'userid' => $iUserId ) ) ) );
		
		$_GET['cardid'] or sysMessage("您提交的银行卡数据错误，请核对后重新提交！", 0);	
		
		if ( $iCardId <= 0 || $iUserId <= 0 )
		{
			sysMessage('提交数据有误', 1, $aLocation);
			exit;
		}
		$oUserBank = new model_withdraw_UserBank($iCardId);
		// 检查 即将删除账户无未完成交易记录
		$oFODetail = new model_withdraw_fundoutdetail( $iCardId );
    	$oFODetail->UserId = $iUserId;
    	$oFODetail->Account = $oUserBank->Account;
    	$oFODetail->Status	   = "0,3"; // 待审核记录
    	if ( $oFODetail->countUncheck() > 0 )
    	{
    		sysMessage("该账户有等待审核的提现记录, 暂不能删除！", 1, $aLocation);
    		unset($oFODetail);
    		exit;
    	}
    	
    	// 锁定用户资金
		$oUserFund = new model_userfund();
	    if( FALSE == $oUserFund->switchLock($iUserId, 0, TRUE) )
		{
			sysMessage("用户资金账户因其它操作而被锁，请稍后重试！", 1, $aLocation); // 锁定用户账户失败
		}
		
		// 改变银行卡状态 (逻辑删除)
	    $oUserBank = new model_withdraw_UserBank( $iCardId );
	    // 物理删除
		//$oUserBank->Status	= 2;
		if ( $oUserBank->erase() )
		{
			$oUserFund->switchLock($iUserId, 0, false); // 用户资金账户解锁
			sysMessage("删除成功", 0, $aLocation);
			unset($oFODetail, $oUserBank);
		} else {
			$oUserFund->switchLock($iUserId, 0, false); // 用户资金账户解锁
			sysMessage("删除失败", 1, $aLocation);
			unset($oFODetail, $oUserBank);
		}
		EXIT;
		
	}
	
	
	/**
	 * 总代管理员列表
	 *
	 * @version 	v1.0	2010-05-30
	 * @author 		louis
	 */
	public function actionAdminProxyList(){
		$iUserId = isset($_GET['userid']) ? $_GET['userid'] : '';
		if (!is_numeric($iUserId) || $iUserId <= 0) sysMessage("您提交的用户数据错误，请核对后重新提交！", 0);
			
		$oUser = new model_user();
		// 检查用户是否为总代
		if ($oUser->isTopProxy($iUserId) === false) sysMessage("此用户不是总代！", 0);
		
		// 获取总代信息
		$aTopProxy = $oUser->getUserExtentdInfo($iUserId);
		
		// 获取用户的管理员分组列表
		$aResult = array();
		$aGroupIdList = array();
        $oGroup = new model_proxygroup();
        $aData  = $oGroup->getListByUser( $iUserId );
        if (!empty($aData)){
        	foreach ($aData as $v){
        		$aGroupIdList[$v['groupid']]['groupid'] = $v['groupid'];
        		$aGroupIdList[$v['groupid']]['groupname'] = $v['groupname'];
        		// 获取分组下的用户列表
        		$aResult = $oUser->getAdminList($iUserId, " AND pg.`groupid` = {$v['groupid']}");
        		$aGroupIdList[$v['groupid']]['admin'] = $aResult;
        	}
        }
        
		$GLOBALS["oView"]->assign( "ur_here",    "总代管理员列表" );
		$GLOBALS["oView"]->assign( "grouplist",   $aGroupIdList );
		$GLOBALS["oView"]->assign( "topproxy",    $aTopProxy['username'] );
		$oUser->assignSysInfo();
		$GLOBALS["oView"]->display("user_adminproxylist.html");
		EXIT;
	}
	
	
	/**
	 * 开启在线客户服务功能
	 * URL = ./?controller=user&action=ocsopen
	 */
	function actionOCSOpen()
	{
		$iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if( $iUserId==0 )
		{
			redirect( url("user","view") );
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage('您的权限不足', 1);
		}
		$aUser  = $oUser->getUserInfo( $iUserId );
		$GLOBALS['oView']->assign( 'ur_here',    "开启在线客户服务功能" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user", "view", array('userid'=>$iUserId)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( 'user',       $aUser );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_ocsopen.html");
		EXIT;
	}


	/**
	 * 开启在线客户服务功能(执行)
	 * URL = ./?controller=user&action=saveocsopen
	 */
	function actionSaveOCSOpen()
	{
		$iUserId = isset($_POST["userid"]) &&is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
		if($iUserId == 0)
		{
			redirect( url('user', 'view') );
		} 
		$iOCSOpen   = isset($_POST["ocsopen"]) && is_numeric($_POST["ocsopen"]) ? intval($_POST["ocsopen"])   : 0;
		if( $iOCSOpen == 0 )
		{
			redirect( url('user', 'OCSOpen', array('userid'=>$iUserId)));
		}
		$bIncludeTree = $iOCSOpen == 2 ? true : false;
		
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		$oUser  =  new model_user();
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1 );
		}          
		if( $oUser->OCSStatus( $iUserId, $iAdmin, 2, 1, $bIncludeTree ) === TRUE )
		{
			sysMessage('操作成功', 0, array('0'=>array( 'text'=>'用户管理', 'href'=>url( 'user', 'view', array( 'userid' => $iUserId ) ) ) ) );
		}
		else
		{
			sysMessage('操作失败', 1, array('0'=>array( 'text'=>'用户管理', 'href'=>url( 'user', 'view', array( 'userid' => $iUserId ) ) ) ) );
		}
	}


	/**
	 * 关闭在线客户服务功能
	 * URL = ./?controller=user&action=ocsclose
	 */
	function actionOCSClose()
	{
		$iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : 0;
		if ( $iUserId ==0 ) 
		{
			redirect( url('user', 'view') );
		}
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( !$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
		{
			sysMessage( '您的权限不足', 1 );
		}
		$aUser  = $oUser->getUserInfo( $iUserId );
		$GLOBALS['oView']->assign( 'ur_here',    "关闭在线客户服务功能" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("user","view",array('userid'=>$iUserId)), 'text'=>'用户列表' ) );
		$GLOBALS['oView']->assign( 'user',       $aUser );
		$oUser->assignSysInfo();
		$GLOBALS['oView']->display("user_ocsclose.html");
		EXIT;
	}


	/**
	 * 关闭在线客户服务功能(执行)
	 * URL = ./?controller=user&action=saveocsclose
	 */
	function actionSaveOCSClose()
	{
		$iUserId = isset($_POST["userid"])&&is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
		if($iUserId == 0 )
		{
			redirect(url("user","view"));
		}
		$iOCSClose = isset($_POST["ocsclose"])&&is_numeric($_POST["ocsclose"]) ? intval($_POST["ocsclose"]) : 0;
		if( ($iOCSClose<=0)||($iOCSClose>=3) )
		{
			redirect( url("user", "view") );
		}
		
		$bIncludeTree = $iOCSClose == 2 ? true : false;
		
		$oUser  = new model_user();
		$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
		if( $iAdmin == 0 )
		{
			redirect( url("user", "view") );
		}

		if(!$oUser->checkAdminForUser( $iAdmin, $iUserId))
		{
			sysMessage('您的权限不足', 1);
		}

		if( $oUser->OCSStatus( $iUserId, $iAdmin, 2, 0, $bIncludeTree ) === TRUE )
		{
			sysMessage("操作成功", 0, array(0=>array('text'=>'用户管理列表','href'=>url( 'user', 'view', array( 'userid' => $iUserId) ) ) ) );
		}
		else
		{
			sysMessage("操作失败", 1, array(0=>array('text'=>'用户管理列表','href'=>url( 'user', 'view', array( 'userid' => $iUserId) ) ) ) );
		}
	}
	
	
}
?>