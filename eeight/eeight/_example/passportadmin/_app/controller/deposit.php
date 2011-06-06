<?php
/**
 * 文件:  /_app/controller/deposit.php
 * 功能: 控制器 用户＆卡 关系
 * 
 * 
 * @package	passportadmin
 * @version 0.1 9/6/2010
 * @author	Jim
 */


class controller_deposit extends basecontroller 
{
	/**
	 * VIP用户卡列表
	 * 
	 */
	public function actionVipList()
	{
		
		$aLocation = array(0 => array('text'=>'查看:VIP用户列表','href'=>url('deposit','vipuser')));
    	
		//提取系统中使用的受付银行列表
		$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$oDeposit     = new model_deposit_depositlist(array(),'','array');
    	$aDepositList = $oDeposit->getDepositArray('all');
    	$aBankIdArray = $aDepositList[0];
    	$aBanknameArr = $aDepositList[1];
    	$aBankArray	  = $aDepositList[2];

		if ( !is_numeric($iDepositbankid) || intval($iDepositbankid) <= 0 
			|| array_search( $iDepositbankid, $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			$GLOBALS['oView']->assign("ur_here",   "选择受付银行");
			$GLOBALS['oView']->assign("controllerstr",   'deposit');
			$GLOBALS['oView']->assign("actionstr",   'viplist');
			$GLOBALS['oView']->assign("depositbanklist",   $aBankArray);
					$oDeposit->assignSysInfo();
			$GLOBALS['oView']->display("deposit_choosebank.html");
			EXIT;
		}
		
		
    	$sWhere = ' `isvip`=1';
		if ( !empty($_REQUEST['username']) )
    	{
   			$sWhere .= " AND `username`='".daddslashes(trim($_REQUEST['username']))."'";
    	}
    	$iSdate = strtotime($_REQUEST['sdate']);
    	$iEdate = strtotime($_REQUEST['edate']);
    	// 2010-01-01 至 2030-01-01 时间内为合法时间
    	if ( ( 1262275200 < $iSdate ) && ( 1893427200 > $iSdate ) 
    		&& ( 1262275200 < $iEdate ) && ( 1893427200 > $iEdate ) )
    	{
    		$sWhere .= " AND `vip_expriy` BETWEEN '".$_REQUEST['sdate']."' AND '".$_REQUEST['edate']."'"; 
    	}
    	
    	$p         = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pn 	   = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $s['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25; 
        $oVipList 			= new model_deposit_companycard();
        $oVipList->BankId 	= $iDepositbankid;
        $aResult   = $oVipList->getRelationList('*', $sWhere, $s['pn'] , $p);
        
        $oUser = new model_user();
        $aTopUser = $oUser->getTopUserArray();
        
        $aDepositList = $oVipList->fillarray($aResult['results'],$aTopUser,'agentname','topagentid');

        $oPager    = new pages( $aResult['affects'], $s['pn'], 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'DepositList', $aDepositList );
        $GLOBALS['oView']->assign( 's', array('username' => $_REQUEST['username'], 'sdate'=> $_REQUEST['sdate'], 'edate'=> $_REQUEST['edate'],'pn'=>$s['pn']) );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("deposit","blacklist",array('depositbankid'=>$iDepositbankid) ), 'text'=>'黑名单用户列表' ) );
        $GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("deposit","blackuser",array('depositbankid'=>$iDepositbankid)), 'text'=>'添加黑名单用户' ) );
        $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("deposit","vipuser",array('depositbankid'=>$iDepositbankid)), 'text'=>'添加VIP用户' ) );
    	$GLOBALS['oView']->assign( "ur_here", "VIP用户列表");
    	$GLOBALS['oView']->assign( "depositbanklist", $aBankArray);
    	$GLOBALS['oView']->assign( "depositbankid", $iDepositbankid);
    	$GLOBALS['oView']->assign( "depositbankname",  $aBanknameArr[$iDepositbankid] );
        		$oVipList->assignSysInfo();
        $GLOBALS['oView']->display("deposit_uc_viplist.html");
        
	}
	
	
	/**
	 * 黑名单用户列表
	 * 
	 */
	public function actionBlackList()
	{
		$aLocation = array(
			0 => array('text'=>'查看:黑名单用户列表','href'=>url('deposit', 'blacklist')),
			);
    	
		//提取系统中使用的受付银行列表
		$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$oDeposit     = new model_deposit_depositlist(array(),'','array');
    	$aDepositList = $oDeposit->getDepositArray('all');
    	$aBankIdArray = $aDepositList[0];
    	$aBanknameArr = $aDepositList[1];
    	$aBankArray	  = $aDepositList[2];

		if ( !is_numeric($iDepositbankid) || intval($iDepositbankid) <= 0 
			|| array_search( $iDepositbankid, $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			$GLOBALS['oView']->assign("ur_here",   "选择受付银行");
			$GLOBALS['oView']->assign("controllerstr",   'deposit');
			$GLOBALS['oView']->assign("actionstr",   'blacklist');
			$GLOBALS['oView']->assign("depositbanklist",   $aBankArray);
					$oDeposit->assignSysInfo();
			$GLOBALS['oView']->display("deposit_choosebank.html");
			EXIT;
		}
		
    	$sWhere = ' `isblack`=1 ';
    	if ( !empty($_REQUEST['username']) )
    	{
   			$sWhere .= " AND `username`='".daddslashes(trim($_REQUEST['username']))."'";
    	}
    	$iSdate = strtotime($_REQUEST['sdate']);
    	$iEdate = strtotime($_REQUEST['edate']);
    	if ( ( 1262275200 < $iSdate ) && ( 1893427200 > $iSdate ) 
    		&& ( 1262275200 < $iEdate ) && ( 1893427200 > $iEdate ) )
    	{
    		$sWhere .= " AND `black_starttime` BETWEEN '".$_REQUEST['sdate']."' AND '".$_REQUEST['edate']."'"; 
    	}
    	
    	$p         = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pn        = isset($_GET['pn']) ? intval($_GET['pn']) : 30;
        $s['pn']   = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25; 
        $oVipList 			= new model_deposit_companycard();
        $oVipList->BankId 	= $iDepositbankid;
        $aResult   = $oVipList->getRelationList('*', $sWhere, $s['pn'] , $p);
        
		$oUser = new model_user();
        $aTopUser = $oUser->getTopUserArray();
        
        $aDepositList = $oVipList->fillarray($aResult['results'],$aTopUser,'agentname','topagentid');
        
        $oPager    = new pages( $aResult['affects'], $s['pn'], 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'DepositList', $aDepositList );
        $GLOBALS['oView']->assign( 's', array('username' => $_REQUEST['username'], 'sdate'=> $_REQUEST['sdate'], 'edate'=> $_REQUEST['edate'],'pn'=>$s['pn']) );
        $GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("deposit","blackuser",array('depositbankid'=>$iDepositbankid)), 'text'=>'添加黑名单用户' ) );
        $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("deposit","vipuser",array('depositbankid'=>$iDepositbankid)), 'text'=>'添加VIP用户' ) );
    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("deposit","viplist",array('depositbankid'=>$iDepositbankid) ), 'text'=>'VIP用户列表' ) );
        $GLOBALS['oView']->assign( "ur_here", "黑名单用户列表");
        $GLOBALS['oView']->assign( "depositbanklist", $aBankArray);
    	$GLOBALS['oView']->assign( "depositbankid", $iDepositbankid);
    	$GLOBALS['oView']->assign( "depositbankname",  $aBanknameArr[$iDepositbankid] );
        		$oVipList->assignSysInfo();
        $GLOBALS['oView']->display("deposit_uc_blacklist.html");
        EXIT;
	}
	
	
	/**
	 * VIP用户添加
	 */
	public function actionVipUser()
	{
		$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$aLocation = array(
						0 => array('text'=>'查看:VIP用户列表','href'=>url('deposit','viplist',array('depositbankid'=>$iDepositbankid))),
						1 => array('text'=>'继续:添加VIP用户','href'=>url('deposit','vipuser',array('depositbankid'=>$iDepositbankid)))
						);
		$aLocation2 = array(
						0 => array('text'=>'查看:黑名单用户列表','href'=>url('deposit','blacklist',array('depositbankid'=>$iDepositbankid))),
						1 => array('text'=>'继续:添加黑名单用户','href'=>url('deposit','blackuser',array('depositbankid'=>$iDepositbankid)))
						);
		if ($_REQUEST['flag'] == 'save')
		{
			
			if (!$_REQUEST['cate']){
				sysMessage('提交表单缺损',1,$aLocation);
			}
			$aLocation =  ($_REQUEST['cate'] == 'black') ? $aLocation2 : $aLocation;
			
			if (!$_REQUEST['userlist'] || (!$_REQUEST['days'] && $_REQUEST['cate'] == 'vip'))
			{
				sysMessage('提交内容不全',1,$aLocation);
				exit;
			}

			$sUserlist = $_REQUEST['userlist'];
			
			$iDay = ( $_REQUEST['days'] > 0 ) ? intval($_REQUEST['days']) : '';
					
			if ( (strlen($sUserlist) > 20) && !eregi(',', $sUserlist) )
			{
				sysMessage('提交名单可能少加分隔符号',1,$aLocation);
				exit;
			}

			if ( !is_int($iDay) && ($_REQUEST['cate'] == 'vip') ) 
			{
				sysMessage('提交的天数是无效值',1,$aLocation);
				exit;
			}
			
			$aUser = explode(',',$sUserlist);
			
			foreach ($aUser AS $aU)
			{
				if ( !eregi("[a-z0-9]+",$aU) )
				{
					sysMessage('用户名中含有不合法的字母', 1, $aLocation);
					exit;
				}
					
			}
			
			$oVip = new model_deposit_companycard();
			$sDays = '+ '.$iDay.'days';
			$fExprity = date('Y-m-d 02:20:00',strtotime($sDays));
			
			if ($_REQUEST['cate'] == 'black')
			{
				$sLogo = 'opblack';
				$sWhere = ' AND `isblack`=0';
			}
			else 
			{
				$sLogo = 'opvip';
				$sWhere = ' AND `isvip`=0';
			}

			$aValidUser = $oVip->userValidCheck($aUser,$sWhere,'username');

			// 找出 user_deposit_card 表中不存在或无须再次添加的用户,并在至少提供新增用户名单有一个成功添加后给出成功提示
			$aInvalidUser = array_diff_key(array_flip($aUser), $aValidUser);

			if ( count($aInvalidUser) > 0 )
			{
				$sTips = '(以下用户名无效或已被添加,请确认: '.implode(',', array_flip($aInvalidUser)).')';
			}
			
			$bResult = $oVip->add($sLogo, $aValidUser, $fExprity);
			if ( $bResult === true )
			{
				sysMessage('成功:添加新的名单'.$sTips,0,$aLocation);
				exit;
			}
			else 
			{
				sysMessage('失败:请检查重试',1,$aLocation);
				exit;
			}
			
		}
		else
		{
			$aText = array(
				'title' => '添加VIP用户',
				'userlisttext' => '输入VIP用户名:',
				'daystext' => '输入VIP优惠天数:',
				'viewtext' => '预览',
				'cate' => 'vip'
				);
	    	$GLOBALS['oView']->assign( 'ur_here', $aText['title'] );
    		$GLOBALS['oView']->assign( 'flag', 'save');
    		$GLOBALS['oView']->assign( 'action', 'vipuser');
    		$GLOBALS['oView']->assign( 'aText', $aText);
    		$GLOBALS['oView']->assign( 'depositbankid', $iDepositbankid);
    		$GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("deposit","blackuser",array('depositbankid'=>$iDepositbankid)), 'text'=>'添加黑名单用户' ) );
    		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("deposit","viplist",array('depositbankid'=>$iDepositbankid)), 'text'=>'VIP用户列表' ) );
    		$GLOBALS['oView']->display( 'deposit_ucs_edit.html' );
    		exit;
		}
	}
	
	
	/**
	 * BLACK用户添加
	 */
	public function actionBlackUser()
	{
		$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		// 与vip名单添加复用save功能
		$aText = array(
				'title' => '添加黑名单用户',
				'userlisttext' => '输入黑名单用户名:',
				'daystext' => '输入黑名单天数:',
				'viewtext' => '预览',
				'cate' => 'black'
				);
	    $GLOBALS['oView']->assign( 'ur_here', $aText['title'] );
    	$GLOBALS['oView']->assign( 'flag', 'save');
    	$GLOBALS['oView']->assign( 'action', 'vipuser');
    	$GLOBALS['oView']->assign( 'aText', $aText);
    	$GLOBALS['oView']->assign( 'depositbankid', $iDepositbankid);
        $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("deposit","vipuser",array('depositbankid'=>$iDepositbankid)), 'text'=>'添加VIP用户' ) );
    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("deposit","blacklist",array('depositbankid'=>$iDepositbankid)), 'text'=>'黑名单用户列表' ) );
    	$GLOBALS['oView']->display( 'deposit_ucs_edit.html' );
    	exit;
	}
	
	
	/**
	 * 删除VIP用户
	 * 
	 */
	public function actionVipDel()
	{
		$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$aLocation = array(
						0 => array('text'=>'查看:VIP用户列表','href'=>url('deposit','viplist',array('depositbankid'=>$iDepositbankid))),
						1 => array('text'=>'继续:添加VIP用户','href'=>url('deposit','vipuser',array('depositbankid'=>$iDepositbankid)))
						);

		if ( !$_REQUEST['username'] && !$_REQUEST['userid'])
		{
			sysMessage('数据不全',1,$aLocation);
			exit;
		}
		
		if ( eregi(',',$_REQUEST['username']) )
		{
			$aUserArray = explode(',',$_REQUEST['username']);
			foreach ($aUserArray AS $aU)
			{
				if (eregi('[a-z0-9]+',$aU))
				{
					$aUser[] = $aU;	
				}
			}
		}
		else 
		{
			$aUser = array(daddslashes($_REQUEST['username']));
		}
		
		$oDeposit = new model_deposit_companycard();
		
		$aValidUser = $oDeposit->userValidCheck($aUser,' AND `isvip`=1');
		
		if ( count($aValidUser) < 1)
		{
			sysMessage('提交用户名无效',1,$aLocation);
			exit;
		}

		$aResult = $oDeposit->del('opvip',$aValidUser);
		if ($aResult === TRUE)
		{
			sysMessage('成功:从VIP名单删除',0,$aLocation);
			exit;
		}
		else
		{
			sysMessage('失败:从VIP名单删除',1,$aLocation);
			exit;
		}
	}
	
	
	/**
	 * 删除VIP用户
	 * 
	 */
	public function actionBlackDel()
	{
		$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$aLocation = array(
						0 => array('text'=>'查看:黑名单用户列表','href'=>url('deposit','blacklist',array('depositbankid'=>$iDepositbankid))),
						1 => array('text'=>'继续:添加黑名单用户','href'=>url('deposit','blackuser',array('depositbankid'=>$iDepositbankid)))
						);
		if ( !$_REQUEST['username'] && !$_REQUEST['userid'])
		{
			sysMessage('数据不全',1,$aLocation);
			exit;
		}
		
		if ( eregi(',',$_REQUEST['username']) )
		{
			$aUserArray = explode(',',$_REQUEST['username']);
			foreach ($aUserArray AS $aU)
			{
				if (eregi('[a-z0-9]+',$aU))
				{
					$aUser[] = $aU;	
				}
			}
		}
		else 
		{
			$aUser = array(daddslashes($_REQUEST['username']));
		}
		
		$oDeposit = new model_deposit_companycard();
		
		$aValidUser = $oDeposit->userValidCheck($aUser,' AND `isblack`=1');
		
		if ( count($aValidUser) < 1)
		{
			sysMessage('提交用户名无效',1,$aLocation);
			exit;
		}

		$aResult = $oDeposit->del('opblack',$aValidUser);
		if ($aResult === TRUE)
		{
			sysMessage('成功:从黑名单删除',0,$aLocation);
			exit;
		}
		else
		{
			sysMessage('失败:从黑名单删除',1,$aLocation);
			exit;
		}
	}
	
	
	/* class ended*/
}

?>