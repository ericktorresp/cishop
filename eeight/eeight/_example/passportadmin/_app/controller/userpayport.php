<?php
/**
 * 文件 : /_app/controller/userpayport.php
 * 功能 : 控制器 - 用户与支付接口关系配置、管理
 * 
 * 
 * @package	passportadmin
 * @version 0.1 4/8/2010
 * @author	Jim
 */
class controller_userpayport extends basecontroller 
{
	
	/**
	 * 查看关系对应图,并可单独点击至修改界面
	 *
	 */
	public function actionList(){
		$aLocat = array(0 => array('text'=>'查看:支付接口列表','href'=>url('userpayport','list')));
		
		
		$oPayAccLimit = new model_pay_payaccountlimit();
		//列表等级
		
		$ilv = isset($_GET['lv']) ? intval($_GET['lv']) : 0;
		if ( ($ilv != 0) && ($ilv != 1) && ($ilv != 99) && ($ilv != 999) ){
			sysMessage('失败:用户属性有误',0,$aLocat);
			exit;
		}
		//总代ID
		$iUser = isset($_GET['user']) ? intval($_GET['user']) : NULL;
		//查询账户 查询所有的
		$oPayAccLimit->MGR=true;
		// 按账户查询 分账户列表
    	$aAllllll = $oPayAccLimit->validAccList();
		// 绑定关系 归属为前台权限范围
		$oPayAccLimit->MGR=false;
		if ( ($ilv > 0) && (is_numeric($iUser) ) ){
			$aTopUser = $oPayAccLimit->allProxyUserList($iUser);
			// 提取关系表中已被设置过的账户（含未激活的关系）
			$aSeted = $oPayAccLimit->getSetedList($iUser);
			$aTopSeted = $oPayAccLimit->getTopSeted($iUser);
			
			$aHtmlStr = array('name'=> $_GET['username'], 'lvstr'=>'一代','userid'=>$iUser);
			$oPayAccLimit->UserId = $iUser;
			
			//重整总代已设置的数组排列  此时 为其总代ID
			foreach ($aTopSeted AS $aSO){
    			$aOTH[$aSO['user_id']]['ppArr'][$aSO['ppid']]['accArr'][$aSO['pp_acc_id']]['isextend'] = 1;
    			//将总代关系中的accid赋值为extend，供后续使用
    			$aOTH[$aSO['user_id']]['ppArr'][$aSO['ppid']]['accArr'][$aSO['pp_acc_id']]['extform'] = $aSO['pp_acc_id'];
    		}
		}else{
			// 所有总代
			$aTopUser = $oPayAccLimit->allTopUserList();
			// 提取关系表中已被设置过的分账户（含未激活的关系）
			$aSeted = $oPayAccLimit->getSetedList();
			// 提取所有一代设置过的分账户（含未激活的关系）
			$aProxySeted = $oPayAccLimit->getProxySeted();
			// 整理输出到VIEW (此序列号应与 $sSetPAcc 一致，才能disable)
			$sProxySeted = '';
			foreach ($aProxySeted AS $aArrr)
				$sProxySeted .=  $aArrr['ppid'].'_'.$aArrr['user_level'].'_'.$aArrr['pp_acc_id'].',';
			
			$aHtmlStr = array('name'=> null, 'lvstr'=>'总代','userid'=>0);
			//重整一代已设置的数组  此时 user_level为其总代ID
			foreach ($aProxySeted AS $aSO){
    			$aOTH[$aSO['user_level']]['ppArr'][$aSO['ppid']]['accArr'][$aSO['pp_acc_id']]['isshow'] = 1;
    		}
		}
		
		//重整已设置的用户数组排列
    	foreach ($aSeted AS $aSA){
    		$aSET[$aSA['user_id']]['ppArr'][$aSA['ppid']]['accArr'][$aSA['pp_acc_id']]['isshow'] = $aSA['isactive'];
    	}
		
    	// 按账户查询 分账户列表
		$aAccountlist = array();
		foreach ($aAllllll AS $aArr){
			$aAccountlist[$aArr['aid']]['accname'] = $aArr['acc_name'];
			$aAccountlist[$aArr['aid']]['isenable'] = $aArr['isenable'];
		}
		asort($aAccountlist);
		
		
		// 增加索引字符
		$aUserIndex = array();
		foreach ($aTopUser AS &$aU){
			 $sUI = strtoupper(substr($aU['username'],0,1));
			 $aU['username_index'] = $sUI;
			 $aUserIndex[] = $sUI;
		}
		$aUserIndex = array_unique($aUserIndex);
		
		 //单个用户时显示
    	if ($ilv == 99) $aHtmlStr['lvstr'] = '';
    	
    	//组装页面显示数组
    	$oPayAll = new model_pay_payaccountlist();
    	$aPayAccs = $oPayAll->allList(1,1);

    	$r = 0;
    	//循环用户
    	foreach ($aTopUser AS $aUser){
    		// 以接口分组,列分账户 L2
    		$i = 0;
    		foreach ($aPayAccs AS $aPl){
    			$aAL2[$aPl['ads_payport_id']]['ppname'] = $aPl['ads_payport_name'];
    			$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['aid'] = $aPl['aid'];
    			$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['accname'] = $aPl['acc_name'];
    			$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isdel'] = 0;  //$aSET[$aSA['user_id']]['ppArr'][$aSA['ppid']]['accArr'][$aSA['pp_acc_id']]['isactive']
			
    			//区别 总代与一代 
    			if ($ilv == 0){
    				// 总代时直接显示自己; isshow --inchlid(isvalid)
    				$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isshow'] 
    					= @$aSET[$aUser['userid']]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isshow'];
    			
    			}else{
    				// 一代时区分自有isshow 及继承 isextend
    				unset($iIsShow,$iExtId);
    				$iIsShow = @$aSET[$aUser['userid']]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isshow'];
    				$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isshow'] = $iIsShow;
    				// 标记接口处于 继承权限状态
    				$aAL2[$aPl['ads_payport_id']]['ppunextend'] = '';
    				$aAL2[$aPl['ads_payport_id']]['ppunextend'] = ($aAL2[$aPl['ads_payport_id']]['ppunextend'] > 0) ? 1 : $iIsShow;
    				
    				//inextend 
    				if ( $iIsShow == 1){
    					// 一代自己有设置
    					$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isextend'] = 0;
    					$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['extform'] = '';
    				}else{
    					// 一代无设置、总代有
    					// 继承状态
    					$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isextend'] = 1;
    					$iExtId = @$aOTH[$iUser]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['extform'];
    					if ($iExtId > 0)
    					{
    						$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['extform'] = $aPl['acc_name'];
    					}
    					
    					
    				}
    				
    				
    			}
    		
    			$aPAYPORT[$aPl['ads_payport_id']] = $aPl['ads_payport_name'];
    			$i++;
    		}
    		//L1
    			
    		$AAA[$aUser['userid']]['ppArr'] = $aAL2;
    		unset($aAL2);
    		$AAA[$aUser['userid']]['username'] = $aUser['username'];
    		$AAA[$aUser['userid']]['usernameindex'] = strtoupper(substr($aUser['username'],0,1));
    		$AAA[$aUser['userid']]['userlevel'] = ($ilv == 0) ? 0 : 1; 
    		
    		$r ++;
    	} 
    	
		unset($aPayAccs,$aSET,$aOTH,$aAl);
    	unset($oPayAll);
    	
		$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("userpayport","edit"), 'text'=>'分配支付账户' ) );
    	if ($iUser > 0) $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("userpayport","list"), 'text'=>'查看总代关系表' ) );
    	$GLOBALS['oView']->assign( "ur_here", "用户与分账户对应关系" );
    	$GLOBALS['oView']->assign( "htmlstr", $aHtmlStr );
    	$GLOBALS['oView']->assign( "acclist", $aAccountlist );
    	$GLOBALS['oView']->assign( "TA", array( 'cols'=>$i, 'colsth'=> $i+2, 'rows'=> $r));
    	$GLOBALS['oView']->assign( "AAA", $AAA);
    	$GLOBALS['oView']->assign( "PAYPORT", $aPAYPORT);
    	$GLOBALS['oView']->assign( "userindex", $aUserIndex);
    	$oPayAccLimit->assignSysInfo();
    	$GLOBALS['oView']->display("userpayport_view.html");
		
	}
	
	
	
	/**
	 * 显示  某分账户与用户的关联关系 (搜索)
	 *
	 */
	public function actionViewAcc(){
		$aLocation  = array(0 => array('text'=>'查看关系表','href'=>url('userpayport','list')));
		
		$iAccId = $_REQUEST['accid'] ? intval($_REQUEST['accid']) : 0;
		if ( ($iAccId <= 0) || empty($iAccId) ) {
			sysMessage('失败:没用选定分账户',0,$aLocation);
		}
		
		$oPayAccLimit = new model_pay_payaccountlimit();
		$oPayAccLimit->AccId = $iAccId;
		//查看关系,设为后台权限，使关闭的账户依然可以显示
		$oPayAccLimit->MGR=true;
		$aUserlist = $oPayAccLimit->validUserList();
		
		// 供搜索的所有分账户
		$aAl = $oPayAccLimit->validAccList();
		$aAcclist = array();
		foreach ($aAl AS $aArr){
			$aAcclist[$aArr['aid']]['accname'] = $aArr['acc_name'];
			$aAcclist[$aArr['aid']]['isenable'] = $aArr['isenable'];
		}
		asort($aAcclist);
		$aHtmlStr =  array('accid'=>$iAccId, 'accname'=> $aAcclist[$iAccId]['accname']);
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("userpayport","edit"), 'text'=>'分配支付账户' ) );
		$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("userpayport","list"), 'text'=>'查看关系列表' ) );
    	$GLOBALS['oView']->assign( "ur_here", "分账户与用户对应关系" );
    	$GLOBALS['oView']->assign( "htmlstr", $aHtmlStr );
    	$GLOBALS['oView']->assign( "acclist", $aAcclist );
    	$GLOBALS['oView']->assign( "userlist", $aUserlist );
    	$oPayAccLimit->assignSysInfo();
    	$GLOBALS['oView']->display("userpayport_viewacc.html");
	}
	
	
	/**
	 * 操作界面  
	 *  显示支付接口分账户与用户(总代、一代)列表
	 *
	 */
	public function actionEdit(){
		
		//获取休市时间，只许可在休市时间修改;
    	$aLocat = array(0 => array('text'=>'查看:支付接口列表','href'=>url('userpayport','list')));
		/*$oConfig = new model_config();
		$sResetTime = $oConfig->getConfigs('xiushishijian');
		$aResetTime = explode('-',$sResetTime);
		// 24小时值，无前导0
		$sNow = date('G:i');
		if ( ($sNow > $aResetTime[1]) || ($sNow < $aResetTime[0]) ){
			sysMessage('非休市时间,禁用编辑',0,$aLocat);
			exit;
		}*/
		
		$oPayAccLimit = new model_pay_payaccountlimit();
		//列表等级
		$ilv = isset($_GET['lv']) ? intval($_GET['lv']) : 0;
		if ( ($ilv != 0) && ($ilv != 1) && ($ilv != 99) && ($ilv != 999) ){
			sysMessage('失败:用户属性有误',0,$aLocat);
			exit;
		}
		//总代ID
		$iUser = isset($_GET['user']) ? intval($_GET['user']) : NULL;
		// 绑定关系 归属为前台权限范围
		$oPayAccLimit->MGR=False;
		if ( ($ilv > 0) && (is_numeric($iUser) ) ){
			$aTopUser = $oPayAccLimit->allProxyUserList($iUser);
			// 提取关系表中已被设置过的账户（含未激活的关系）
			$aSeted = $oPayAccLimit->getSetedList($iUser);
			$aTopSeted = $oPayAccLimit->getTopSeted($iUser);
			
			$aHtmlStr = array('name'=> $_GET['username'], 'lvstr'=>'一代','userid'=>$iUser);
			$oPayAccLimit->UserId = $iUser;
			
			//重整总代已设置的数组排列  此时 为其总代ID
			foreach ($aTopSeted AS $aSO){
    			//$aOTH[$aSO['userid']]['ppArr'][$aSO['ppid']]['accArr'][$aSO['pp_acc_id']]['isvalid'] = 1;
    			$aOTH[$aSO['user_id']]['ppArr'][$aSO['ppid']]['accArr'][$aSO['pp_acc_id']]['isvalid'] = 1;
    		}
    		
		}else{
			// 所有总代
			$aTopUser = $oPayAccLimit->allTopUserList();
			// 提取关系表中已被设置过的分账户（含未激活的关系）
			$aSeted = $oPayAccLimit->getSetedList();
			// 提取所有一代设置过的分账户（含未激活的关系）
			$aProxySeted = $oPayAccLimit->getProxySeted();
			// 整理输出到VIEW (此序列号应与 $sSetPAcc 一致，才能disable)
			$sProxySeted = '';
			foreach ($aProxySeted AS $aArrr)
				$sProxySeted .=  $aArrr['ppid'].'_'.$aArrr['user_level'].'_'.$aArrr['pp_acc_id'].',';
			
			$aHtmlStr = array('name'=> null, 'lvstr'=>'总代','userid'=>0);
			//重整一代已设置的数组排列  此时 user_level为其总代ID
			foreach ($aProxySeted AS $aSO){
    			$aOTH[$aSO['user_level']]['ppArr'][$aSO['ppid']]['accArr'][$aSO['pp_acc_id']]['isvalid'] = 1;
    		}
		}
		
		//重整已设置的用户数组排列
    	foreach ($aSeted AS $aSA){
    		$aSET[$aSA['user_id']]['ppArr'][$aSA['ppid']]['accArr'][$aSA['pp_acc_id']]['isactive'] = $aSA['isactive'];
    		//if ($ilv > 0 ) $aSET[$aSA['user_id']]['ppArr'][$aSA['ppid']]['accArr'][$aSA['pp_acc_id']]['invalid'] = 1;
    	}

    	// 增加索引字符
		$aUserIndex = array();
		foreach ($aTopUser AS &$aU){
			 $sUI = strtoupper(substr($aU['username'],0,1));
			 $aU['username_index'] = $sUI;
			 $aUserIndex[] = $sUI;
		}
		$aUserIndex = array_unique($aUserIndex);
		
		
		//单个用户时显示
    	if ($ilv == 99) $aHtmlStr['lvstr'] = '';
    	
    	//组装页面显示数组
    	/**
		$A = array(
			[userid0]
				'ppArr'=>array(
					[ppid0]
						'ppname'
						'accArr' => array(
								[aid] => aid
								[accname] => acc_name
								[isactive] => isactive 是否激活
								[isvalid] => valid 是否可用/下级上级没有使用
								[isdel] => 0
									)
					[ppid1]
					[ppid2]
					)
				'username'
				'userlevel'
			[userid1]
			[userid2]	
			)

		PHP:
			USERS:			$A[$userid]
			PAYPORTS:		$A[$userid][ppArr]
			PAYACCOUNTS:	$A[$userid][ppArr][$ppid][accArr]
							$A[$userid][ppArr][$ppid][accArr][$aid][isactive]
		JS:
			
    	 **/
		
    	$oPayAll = new model_pay_payaccountlist();
    	$aPayAccs = $oPayAll->allList(1,1);

    	$r = 0;
    	$i = 1;
    	$AAA = $aPAYPORT = array();
    	//循环用户
    	foreach ($aTopUser AS $aUser){
    		// 以接口分组,列分账户 L2
    		$i = 0;
    		foreach ($aPayAccs AS $aPl){
    			$aAL2[$aPl['ads_payport_id']]['ppname'] = $aPl['ads_payport_name'];
    			$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['aid'] = $aPl['aid'];
    			$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['accname'] = $aPl['acc_name'];
    			$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isdel'] = 0;  //$aSET[$aSA['user_id']]['ppArr'][$aSA['ppid']]['accArr'][$aSA['pp_acc_id']]['isactive']
			
    			//区别 总代与一代 赋值
    			if ($ilv == 0){
    				$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isactive'] 
    					= @$aSET[$aUser['userid']]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isactive'];

    				$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isvalid'] 
    					= @$aOTH[$aUser['userid']]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isvalid'];
    			
    			}else{
    				unset($iInvalid);
    				//invalid
    				if ( @$aSET[$aUser['userid']]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isactive'] == 1) 
    				{
    					$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isactive'] = 1;
    				}
    				else
    				{
    					$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isactive'] = 0;
    				}
    				//isactive
    				/*if ( isset($aOTH[$iUser]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isvalid'])
    					 && ($aOTH[$iUser]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isvalid'] == 1) )*/
    				if ( @$aOTH[$iUser]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isvalid'] == 1) 
    				{
    					 	
    					$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isvalid'] = 1;
    					//$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isactive'] = $aSET[$iUser]['ppArr'][$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isactive'];
    				}
    				else
    				{
    					$aAL2[$aPl['ads_payport_id']]['accArr'][$aPl['aid']]['isvalid'] = 0;
    				}
       				
    			}
    		
    			$aPAYPORT[$aPl['ads_payport_id']] = $aPl['ads_payport_name'];
    			$i++;
    		}
    		//L1
    		$AAA[$aUser['userid']]['ppArr'] = $aAL2;
    		unset($aAL2);
    		$AAA[$aUser['userid']]['username'] = $aUser['username'];
    		$AAA[$aUser['userid']]['usernameindex'] = strtoupper(substr($aUser['username'],0,1));
    		$AAA[$aUser['userid']]['userlevel'] = ($ilv == 0) ? 0 : 1; 
    		
    		$r ++;
    	}

    	unset($aPayAccs,$aSET,$aOTH);
    	unset($oPayAll);
    	//赋值是否激活 $aAAA[$userid][$ppid][accArr][$aid][isactive]

    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("userpayport","list"), 'text'=>'查看关系列表' ) );
    	if ($ilv > 0) $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("userpayport","edit"), 'text'=>'分配总代支付账户' ) );
    	$GLOBALS['oView']->assign( "ur_here", $aHtmlStr['lvstr']."分配支付账户");
    	$GLOBALS['oView']->assign( "htmlstr", $aHtmlStr);
    	$GLOBALS['oView']->assign( "TA", array( 'cols'=>$i, 'colsth'=> $i+2, 'rows'=> $r));
    	$GLOBALS['oView']->assign( "AAA", $AAA);
    	$GLOBALS['oView']->assign( "PAYPORT", $aPAYPORT);
    	$GLOBALS['oView']->assign( "userindex", $aUserIndex);
    	$oPayAccLimit->assignSysInfo();
    	$GLOBALS['oView']->display("userpayport_list.html");
    	exit;
		
	}
	
	
	public function actionSave(){

		$aLocation  = array(0 => array('text'=>'继续:分配支付账户','href'=>url('userpayport','edit')),
		1 => array('text'=>'查看关系表','href'=>url('userpayport','list')));
		
		//获取所有有效的分账户,避免越权绑定
		$oPayAccLimit = new model_pay_payaccountlimit();
		$oPayAccLimit->MGR=false;
		$aAllValid = $oPayAccLimit->validAccList();
		foreach ($aAllValid AS $aAV){
			@$aChkValid[$aAV['aid']] .= $aAV['aid'];
		}
		
		if (!$_REQUEST['users'] || !$_REQUEST['payports'] || !$_REQUEST['accids']){
			unset($oPayAccLimit);
			sysMessage('失败:提交数据不完整', 0, $aLocation);
			exit;
		}
		
		$ilvuser = $_REQUEST['lvuser'] ? intval($_REQUEST['lvuser']) : 0;
		
		$aUsers = $_REQUEST['users'];
		$aPayports = $_REQUEST['payports'];
		$aAccids = $_REQUEST['accids'];
		$aDels = @$_REQUEST['dels'];
		
		// 提取关系表中已被设置过的分账户（含未激活的关系）  一对多有效
		/*$aSeted = $oPayAccLimit->getSetedList();
		foreach ($aSeted AS $aUS){
			$aAlready[$aUS['user_id']][$aUS['ppid']][$aUS['pp_acc_id']] = $aUS['isactive'];
		}*/
		
		$aTmp = $aTmpDel = array();
		
		// 循环被勾选操作的用户ID
		foreach ($aUsers AS $aUr){
			$U = explode('|||',$aUr);
			$iUserid = $U[0] ? intval($U[0]) : 0;
			$sUsername = $U[1] ? daddslashes(trim($U[1])) : '';
			if ($iUserid == 0) next($aUsers);
			//获取对某一用户无效的accid
			$aAllValiable = $oPayAccLimit->valiableList($iUserid,$ilvuser);
			if ($aAllValiable === false) {
				sysMessage('失败:分账户与用户关系设置', 0, $aLocation);
				unset($oPayAccLimit,$aAllValid,$aUsers,$aPayports,$aAccids,$aDels);
				exit;
			}
			// 循环支付接口
			foreach ($aPayports AS $aPkey => $aPval){
				$iAcc = $aAccids[$iUserid][$aPkey];
				$aAcc = explode('|||',$iAcc);
				$iAccid = isset($aAcc[0]) ? intval($aAcc[0]) : 0;
				$sAccname = isset($aAcc[1]) ? daddslashes(trim($aAcc[1])) : '';
				if ($iAccid == 0) next($aPayports);
				
				// 效验Accid是否仍然开启  
				//TODO 效验ppid userid组合是否已存在 (即是否需要先删除已有关系再增加新的关系；或直接一步修改完成)
				
				if ( array_search($iAccid,$aChkValid) !== false ){
					// 组装有效数组 (删除/增改)
					if ($aDels[$iUserid][$aPkey] == 1){
						$aTmpDel[] = array(
							'userid' => intval($iUserid),
							'ppid' => intval($aPkey),
							'accid' => intval($iAccid)
						);
					}else{
						
						if ( array_search($iAccid,$aAllValiable) === false){
							$aTmp[] = array(
								'userid' => intval($iUserid),
								'username' => daddslashes($sUsername),
								'ppid' => intval($aPkey),
								'ppname' => daddslashes($aPval), 
								'accid' => intval($iAccid), 
								'accname' => daddslashes($sAccname),
								'isactive' => 1 
							);
						}
					}
					
				}//end 效验判断
				
			} //end 内层 foreach
								
		}// end foreach
	
		$oPayAccLimit = new model_pay_payaccountlimit();
		$oPayAccLimit->AccSetData = $aTmp;
		//对单个用户操作时赋值
		$iUser = $_REQUEST['lvuser'] ? intval($_REQUEST['lvuser']) : 0;
		$sUsername = $_REQUEST['lvusername'] ? daddslashes(trim($_REQUEST['lvusername'])) : '';
			
			// 进行删除操作
			if ( count($aTmpDel) > 0){
				$bReturn1 = false;
				$bReturn1 =	$oPayAccLimit->deleteLimit($aTmpDel);
			}else{
				$bReturn1 = true;
			}
			
			// 更新 增加 操作
			if ( count($aTmp) > 0 ){
				$bReturn2 = false;
				$bReturn2 = $oPayAccLimit->setLimitList($iUser,$sUsername);
			}else{
				$bReturn2 = true;
			}
			
			if ( $bReturn1 && $bReturn2 ){
					unset($oPayAccLimit,$aAllValid,$aUsers,$aPayports,$aAccids,$aDels);
     				sysMessage('成功:分账户与用户关系设置', 0, $aLocation);
     					
			}else{
				
				if ( (count($aTmpDel) <= 0) && (count($aTmp) <= 0) ){
					unset($oPayAccLimit,$aAllValid,$aUsers,$aPayports,$aAccids,$aDels);
					sysMessage('失败:没有发生合理的修改', 0, $aLocation);
				}else{
					unset($oPayAccLimit,$aAllValid,$aUsers,$aPayports,$aAccids,$aDels);
					sysMessage('失败:分账户与用户关系设置', 0, $aLocation);
				}
				
			}
				
	}
	
	/****** end class ******/
}