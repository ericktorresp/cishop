<?php

/**
 * 文件：/_app/controller/emaildeposit.php
 * 功能：Email充值相关操作
 *
 * 类中所有的以 action 开头+首字母大写的英文, 为 "动作方法"
 * 例如 URL 访问:
 * 		http://www.xxx.com/?controller=default&action=abc
 * 		default 是控制器名
 * 		abc     是动作方法
 * 		定义动作方法函数的命名, 规则为 action+首字母大写的全英文字符串
 * 			例: 为实现上例的 /?controller=default&action=abc 中的 abc 方法
 * 				需要在类中定义 actionIndex() 函数
 *
 *
 * 方法：
 * --actionEmailLoad			我要充值界面
 * --actionLoadList				充值历史界面
 * +---actionView				查看指定充值记录
 * +---actionCancelLoad			取消充值申请
 *
 *
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-09-02
 * @package 	passport
 *
 */
define("ICBC", "mail");					// 工行接口名称
define("CCB", "ccb");					// 建行接口名称
define("ICBC_BANK_ID", 7);				// 建行id
class controller_emaildeposit extends basecontroller{

	/**
	 * 我要充值界面
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-02
	 * @package 	passport
	 *
	 */
	public function actionEmailLoad(){
		$aLinks = array(
			0 => array(
					'title' => "返回上一页",
			//				'url'	=> "?controller=default&action=main"
			)
		);
		// 判断是否需要资金密码检查
		$oEmailDeposit = new model_deposit_emaildeposit();
		if ($oEmailDeposit->securityCheck() === false){
			// 资金密码检查
			$oSecurityCon = new controller_security();
			$oSecurityCon->actionCheckPass('emaildeposit','emailload', true);
			EXIT;
		}

		$oUser = new model_user();
		$oConfigd = new model_config();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$sUserName   = isset($_SESSION['username']) ? $_SESSION['username'] : "";
		// 如果为总代管理员
		if( $iUserType == 2 )
		{
			//如果为总代管理员，则当前用户调整到其总代ID
			$iUserId = $oUser->getTopProxyId( $iUserId );
			$aResult = $oUser->getUserExtentdInfo($iUserId);
			$sUserName = $aResult['username'];
			if( empty($iUserId) )
			{
				sysMsg( "操作失败", 2);
			}
		}

		$oDepositAllBank = new model_deposit_depositallbankinfo();

		// 检查用户是有绑定银行卡
		$oUserBankCard = new model_withdraw_UserBank();
		$oUserBankCard->UserId = $iUserId;
		if ($oUserBankCard->getCount() <= 0)
		{
			$aTempLinks = array(
				0 => array(
	                        'title' => "卡号绑定页面",
	                        'url'	=> "?controller=security&action=userbankinfo&check=" . $_SESSION['checkcode']
				)
			);
			sysMsg( "您尚未绑定银行卡，请先进行卡号绑定！", 2, $aTempLinks);
		}

		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : "";
		if ($_POST['flag'] == "load")
		{
			// 数据检查
			if (intval($_POST['bid']) <= 0)
			{
				if($_POST['flag'] == "load") sysMsg("参数提交错误！", 2, $aLinks);
			}
			$oPayport = new model_deposit_depositinfo();
			$oPayport->getPayportData($_POST['bid'],"ccb", 1);
			$aResult = $oPayport->getArrayData();
			$sPrifix = $aResult['sysparam_prefix'];
				
			if (empty($sPrifix))
			{
				if($_POST['flag'] == "load") sysMsg("参数提交错误！", 2, $aLinks);
				if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"参数提交错误！")));
			}
				
			// 如果是建行，必须选定建行卡
			$iBankId = isset($_POST['bank']) ? $_POST['bank'] : "";
			if ($sPrifix == CCB && $iBankId <= 0)
			{
				if($_POST['flag'] == "load") sysMsg("请选择绑定的银行卡！", 2, $aLinks);
				if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"请选择绑定的银行卡！")));
			}
			$oUserBank = new model_withdraw_UserBank($iBankId);
			// 检查用户绑定卡重复性
			$oWithdrawBank = new model_withdraw_ApiWithdrawBank();
			$oWithdrawBank->Account = $oUserBank->Account;
			if ($oWithdrawBank->bankExistByCard() === 1 && $sPrifix == CCB)
			{
				if($_POST['flag'] == "load") sysMsg("您选择的建行卡重复绑定，请选择另一张！", 2, $aLinks);
				if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"您选择的建行卡重复绑定，请选择另一张！")));
				exit;
			}
				
			// 检查系统开关
			if (intval($oConfigd->getConfigs($sPrifix . 'deposit_turnauto')) === 0)
			{
				if($_POST['flag'] == "load") sysMsg("您没有操作权限！", 2, $aLinks);
				if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"您没有操作权限！")));
				exit;
			}
				
			// 在 系统参数"禁止充值时间" 禁止充值
			$sStartTime = strtotime($oConfigd->getConfigs($sPrifix . 'deposit_starttime')); // 充值开始时间
			$sEndTime = strtotime($oConfigd->getConfigs($sPrifix . 'deposit_stoptime'));	  // 充值结束时间
			$sRunNow = strtotime(date('G:i')); // 当前时间

			// 获取充值延迟周期
			$sCycle = $oConfigd->getConfigs($sPrifix . 'deposit_cycletime');
			if (!empty($sCycle))
			{
				$aCycle = explode("|", $sCycle);
				if (in_array(date("l"), $aCycle))
				{
					$sStartTime += intval($oConfigd->getConfigs($sPrifix . 'deposit_delaytime')) * 60;
				}
			}

			if ($sStartTime > $sEndTime)
			{ // 开始时间大于结束时间，说明已跨天
				if ($sRunNow >= $sEndTime && $sRunNow <= $sStartTime)
				{
					if($_POST['flag'] == "load") sysMsg('系统结算时间,暂停充值', 2, $aLinks);
					if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"系统结算时间,暂停充值")));
					exit;
				}
			}
			else
			{
				if ($sRunNow <= $sStartTime || $sRunNow >= $sEndTime)
				{
					if($_POST['flag'] == "load") sysMsg('系统结算时间,暂停充值', 2, $aLinks);
					if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"系统结算时间,暂停充值")));
					exit;
				}
			}

			$fMoney = floor($_POST['amount']*100) / 100;
			if ( $fMoney <= 0 )
			{
				if($_POST['flag'] == "load") sysMsg("您输入的充值金额有误！", 2, $aLinks);
				if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"您输入的充值金额有误！")));
			}

			// 根据用户身份取出用户的充值最大最小金额
			$oCompanyCard = new model_deposit_companycard();
			$oCompanyCard->UserId = $iUserId;
			$oCompanyCard->BankId = $_POST['bid'];
			$oCompanyCard->getCard("check");

			$fLoadMax = 0;
			$fLoadMin = 0;
			if (intval($oCompanyCard->UserType) === 2)
			{ // black
				$fLoadMax = $oConfigd->getConfigs($sPrifix . 'deposit_blacklimitmax');
				$fLoadMin = $oConfigd->getConfigs($sPrifix . 'deposit_blacklimitmin');
				// 检查权限
			}
			else if (intval($oCompanyCard->UserType) === 1)
			{ // vip
				$fLoadMax = $oConfigd->getConfigs($sPrifix . 'deposit_viplimitmax');
				$fLoadMin = $oConfigd->getConfigs($sPrifix . 'deposit_viplimitmin');
			}
			else
			{ // normal
				$fLoadMax = $oConfigd->getConfigs($sPrifix . 'deposit_limitmax');
				$fLoadMin = $oConfigd->getConfigs($sPrifix . 'deposit_limitmin');
			}

			// 检查充值数据是否超出限额
			if ($fMoney > $fLoadMax || $fMoney < $fLoadMin)
			{
				if($_POST['flag'] == "load") sysMsg("您输入的充值超出充值限额！", 2, $aLinks);
				if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"您输入的充值超出充值限额！")));
			}

			// 检查用户当天支付中的订单数量是否到达系统上限
			$oDepositAllBank->UserId = $iUserId;
			$iCount = $oDepositAllBank->getcount();
			if ( $iCount >= $oConfigd->getConfigs('maildeposit_times') )
			{
				if($_POST['flag'] == "load") sysMsg("对不起，您今天的充值次数已达到充值上限！", 2, $aLinks);
				if($_POST['flag'] == 'deposit') die(json_encode(array('status'=>'error','msg'=>"对不起，您今天的充值次数已达到充值上限！")));
			}
			unset($oEmailDeposit);

			// 通过公司卡id，获取公司分配给用户的卡号，与开卡人姓名
			$aBankInfo = array();
			$oPayPortInfo = new model_deposit_depositaccountinfo($oCompanyCard->PayCardId);
			if ($oPayPortInfo->AId > 0)
			{
				$aBankInfo = $oPayPortInfo->getAccountData();
			}
				
			// 记录下总代名称
			$sTopproxy_name = "";
			$aTopProxy = $oUser->getTopProxyId($iUserId,true);
			if ($aTopProxy['userid'] == $iUserId)
			{
				$sTopproxy_name = $sUserName;
			}
			else
			{
				$sTopproxy_name = $aTopProxy['username'];
			}
			switch ($sPrifix)
			{
				case ICBC:
					$GLOBALS['oView']->assign( 'email', $aBankInfo['acc_mail']);
					//$GLOBALS['oView']->assign( 'flashtime', $oConfigd->getConfigs($sPrifix . 'deposit_reftime'));
					$GLOBALS['oView']->assign( 'loadstep', "工商");
					$GLOBALS['oView']->assign( 'shortname', "工行");
					$GLOBALS['oView']->assign( 'bankname', "icbc");
					//$GLOBALS['oView']->assign( 'sms_number', $aBankInfo['sms_number'] ); //短信通知手机号码
					break;
				case CCB:
					$GLOBALS['oView']->assign( 'account', $aBankInfo['acc_bankacc']);
					$GLOBALS['oView']->assign( 'sms_number', $aBankInfo['sms_number'] ); //短信通知手机号码
					$GLOBALS['oView']->assign( 'bankname', "ccb");
					$GLOBALS['oView']->assign( 'loadstep', "建设");
					$GLOBALS['oView']->assign( 'shortname', "建行");
					break;
				default:
					sysMsg("参数提交错误！", 2, $aLinks);
			}
			$GLOBALS['oView']->assign( 'ur_here', '充值确认');
			$GLOBALS['oView']->assign( 'acc_name', $aBankInfo['acc_ident']);
			$GLOBALS['oView']->assign( 'amount', $fMoney);
			$GLOBALS['oView']->assign( 'key',  self::generateOrserNumber());
			$GLOBALS['oView']->assign( 'bank_url', $oConfigd->getConfigs($sPrifix . 'deposit_icbcurl'));
			$GLOBALS['oView']->assign( 'help_url', $oConfigd->getConfigs($sPrifix . 'deposit_loadstep'));
			$oUser->assignSysInfo();
			$GLOBALS['oView']->display( "emaildeposit_confirm.html" );
			EXIT;
		}
		/**
		 * 增加点击“充值”按钮 ajax 提交，写入充值记录表，并开新窗口跳转到相应网银登录界面
		 *
		 * [ICBC]
		 * $oEmailDeposit = new model_deposit_emaildeposit();
		 * $oEmailDeposit->AccountId		= $oCompanyCard->PayCardId; // 支付接口id
		 * $oEmailDeposit->Account			= $aBankInfo['acc_bankacc']; // 收款卡账号
		 * $oEmailDeposit->AccountName		= $aBankInfo['acc_ident'];	// 收款卡账户名
		 *
		 * [CCB]
		 * $oEmailDeposit = new model_deposit_ccbdeposit();
		 * $oEmailDeposit->AccountId		= $iBankId; // 绑定卡id
		 * $oEmailDeposit->Account			= $oUserBank->Account; // 汇款卡账号
		 * $oEmailDeposit->AccountName		= $oUserBank->AccountName;	// 汇款卡账户名
		 * $oEmailDeposit->PayACCId			= $oCompanyCard->PayCardId; // 支付接口id
		 * $oEmailDeposit->AcceptCard		= $aBankInfo['acc_bankacc']; // 收款卡账号
		 * $oEmailDeposit->AcceptName		= $aBankInfo['acc_ident'];	// 收款卡账户名
		 *
		 * $oEmailDeposit->UserId 			= $iUserId;
		 * $oEmailDeposit->UserName			= $sUserName;
		 * $oEmailDeposit->TopProxyName		= $sTopproxy_name;
		 * $oEmailDeposit->Money			= $fMoney;
		 *
		 * $iLastId = $oEmailDeposit->insertRecord();
		 */
		else if($_POST['flag'] == 'deposit')
		{
			// 数据检查
			if (intval($_POST['bid']) <= 0)
			{
				die(json_encode(array('status'=>'error','msg'=>"参数提交错误！")));
			}
			$oPayport = new model_deposit_depositinfo();
			$oPayport->getPayportData($_POST['bid'],"ccb", 1);
			$aResult = $oPayport->getArrayData();
			$sPrifix = $aResult['sysparam_prefix'];
				
			if (empty($sPrifix))
			{
				die(json_encode(array('status'=>'error','msg'=>"参数提交错误！")));
			}
				
			// 如果是建行，必须选定建行卡
			$iBankId = isset($_POST['bank']) ? $_POST['bank'] : "";
			if ($sPrifix == CCB && $iBankId <= 0)
			{
				die(json_encode(array('status'=>'error','msg'=>"请选择绑定的银行卡！")));
			}
			$oUserBank = new model_withdraw_UserBank($iBankId);
			// 检查用户绑定卡重复性
			$oWithdrawBank = new model_withdraw_ApiWithdrawBank();
			$oWithdrawBank->Account = $oUserBank->Account;
			if ($oWithdrawBank->bankExistByCard() === 1 && $sPrifix == CCB)
			{
				die(json_encode(array('status'=>'error','msg'=>"您选择的建行卡重复绑定，请选择另一张！")));
			}
				
			// 检查系统开关
			if (intval($oConfigd->getConfigs($sPrifix . 'deposit_turnauto')) === 0)
			{
				die(json_encode(array('status'=>'error','msg'=>"您没有操作权限！")));
			}
			// 在 系统参数"禁止充值时间" 禁止充值
			$sStartTime = strtotime($oConfigd->getConfigs($sPrifix . 'deposit_starttime')); // 充值开始时间
			$sEndTime = strtotime($oConfigd->getConfigs($sPrifix . 'deposit_stoptime'));	  // 充值结束时间
			$sRunNow = strtotime(date('G:i')); // 当前时间

			// 获取充值延迟周期
			$sCycle = $oConfigd->getConfigs($sPrifix . 'deposit_cycletime');
			if (!empty($sCycle))
			{
				$aCycle = explode("|", $sCycle);
				if (in_array(date("l"), $aCycle))
				{
					$sStartTime += intval($oConfigd->getConfigs($sPrifix . 'deposit_delaytime')) * 60;
				}
			}

			if ($sStartTime > $sEndTime)
			{ // 开始时间大于结束时间，说明已跨天
				if ($sRunNow >= $sEndTime && $sRunNow <= $sStartTime)
				{
					die(json_encode(array('status'=>'error','msg'=>"系统结算时间,暂停充值")));
				}
			}
			else
			{
				if ($sRunNow <= $sStartTime || $sRunNow >= $sEndTime)
				{
					die(json_encode(array('status'=>'error','msg'=>"系统结算时间,暂停充值")));
				}
			}

			$fMoney = floor($_POST['amount']*100) / 100;
			if ( $fMoney <= 0 )
			{
				die(json_encode(array('status'=>'error','msg'=>"您输入的充值金额有误！")));
			}

			// 根据用户身份取出用户的充值最大最小金额
			$oCompanyCard = new model_deposit_companycard();
			$oCompanyCard->UserId = $iUserId;
			$oCompanyCard->BankId = $_POST['bid'];
			$oCompanyCard->getCard("check");

			$fLoadMax = 0;
			$fLoadMin = 0;
			if (intval($oCompanyCard->UserType) === 2)
			{ // black
				$fLoadMax = $oConfigd->getConfigs($sPrifix . 'deposit_blacklimitmax');
				$fLoadMin = $oConfigd->getConfigs($sPrifix . 'deposit_blacklimitmin');
				// 检查权限
			}
			else if (intval($oCompanyCard->UserType) === 1)
			{ // vip
				$fLoadMax = $oConfigd->getConfigs($sPrifix . 'deposit_viplimitmax');
				$fLoadMin = $oConfigd->getConfigs($sPrifix . 'deposit_viplimitmin');
			}
			else
			{ // normal
				$fLoadMax = $oConfigd->getConfigs($sPrifix . 'deposit_limitmax');
				$fLoadMin = $oConfigd->getConfigs($sPrifix . 'deposit_limitmin');
			}

			// 检查充值数据是否超出限额
			if ($fMoney > $fLoadMax || $fMoney < $fLoadMin)
			{
				die(json_encode(array('status'=>'error','msg'=>"您输入的充值超出充值限额！")));
			}

			// 检查用户当天支付中的订单数量是否到达系统上限
			$oDepositAllBank->UserId = $iUserId;
			$iCount = $oDepositAllBank->getcount();
			if ( $iCount >= $oConfigd->getConfigs('maildeposit_times') )
			{
				die(json_encode(array('status'=>'error','msg'=>"对不起，您今天的充值次数已达到充值上限！")));
			}
			unset($oEmailDeposit);
			
			// 通过公司卡id，获取公司分配给用户的卡号，与开卡人姓名
			$aBankInfo = array();
			$oPayPortInfo = new model_deposit_depositaccountinfo($oCompanyCard->PayCardId);
			if ($oPayPortInfo->AId > 0)
			{
				$aBankInfo = $oPayPortInfo->getAccountData();
			}
				
			// 记录下总代名称
			$sTopproxy_name = "";
			$aTopProxy = $oUser->getTopProxyId($iUserId,true);
			if ($aTopProxy['userid'] == $iUserId)
			{
				$sTopproxy_name = $sUserName;
			}
			else
			{
				$sTopproxy_name = $aTopProxy['username'];
			}
			switch ($sPrifix)
			{
				case ICBC:
					$oEmailDeposit = new model_deposit_emaildeposit();
		 			$oEmailDeposit->AccountId		= $oCompanyCard->PayCardId; // 支付接口id
		 			$oEmailDeposit->Account			= $aBankInfo['acc_bankacc']; // 收款卡账号
		 			$oEmailDeposit->AccountName		= $aBankInfo['acc_ident'];	// 收款卡账户名
		 			$oEmailDeposit->Key				= $_POST['order_number'];
					break;
				case CCB:
					$oEmailDeposit = new model_deposit_ccbdeposit();
		 			$oEmailDeposit->AccountId		= $iBankId; // 绑定卡id
		 			$oEmailDeposit->Account			= $oUserBank->Account; // 汇款卡账号
		 			$oEmailDeposit->AccountName		= $oUserBank->AccountName;	// 汇款卡账户名
		 			$oEmailDeposit->PayACCId		= $oCompanyCard->PayCardId; // 支付接口id
		 			$oEmailDeposit->AcceptCard		= $aBankInfo['acc_bankacc']; // 收款卡账号
		 			$oEmailDeposit->AcceptName		= $aBankInfo['acc_ident'];	// 收款卡账户名
		 			$oEmailDeposit->OrderNumber		= $_POST['order_number'];
		 			$oEmailDeposit->SmsNumber		= $_POST['sms_number'];
					break;
				default:
					die(json_encode(array('status'=>'error','msg'=>"参数提交错误")));
			}
			$oEmailDeposit->UserId = $iUserId;
		 	$oEmailDeposit->UserName = $sUserName;
		 	$oEmailDeposit->TopProxyName = $sTopproxy_name;
		 	$oEmailDeposit->Money = $fMoney;

		 	$iLastId = $oEmailDeposit->insertRecord();
		 	if($iLastId > 0)
		 	{
				die(json_encode(array('status'=>'ok')));
		 	}
		 	else
		 	{
		 		die(json_encode(array('status'=>'error','msg'=>'系统正忙，请稍候重试！')));
		 	}
			EXIT;
		}

		// 取出所有充值接口列表
		$oPayport     = new model_deposit_depositlist(array('LoadStatus' => 1),'','array');
		$aPayportList = $oPayport->Data;
		if (empty($aPayportList)){
			sysMsg("对不起，您没有操作权限！", 2, $aLinks);
		}

		// 循环银行列表
		$oCompanyCard = new model_deposit_companycard();
		$oCompanyCard->UserId = $iUserId;
		//    	print_rr($aPayportList,1,1);
		 
		$aBankInfo = array();
		foreach ($aPayportList as $k => $v){
			$oCompanyCard->BankId = $v['id'];
			$bResult = $oCompanyCard->getCard();
			if ($bResult === false){ // 分账户关闭
				continue;
			}
			// 检查用户是否具有权限
			if (!$oUser->checkDepositAllow($iUserId, $v['id'])){
				continue;
			}


			// 检查系统开关
			if (intval($oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_turnauto')) === 0){
				continue;
			}
				
			// 在 系统参数"禁止充值时间" 禁止充值
			$sStartTime = strtotime($oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_starttime')); // 充值开始时间
			$sEndTime = strtotime($oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_stoptime'));	  // 充值结束时间
			$sRunNow = strtotime(date('G:i')); // 当前时间
				
			// 获取充值延迟周期
			$sCycle = $oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_cycletime');
			if (!empty($sCycle)){
				$aCycle = explode("|", $sCycle);
				if (in_array(date("l"), $aCycle)){
					$sStartTime += intval($oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_delaytime')) * 60;
				}
			}
				
			if ($sStartTime > $sEndTime){ // 开始时间大于结束时间，说明已跨天
				if ($sRunNow >= $sEndTime && $sRunNow <= $sStartTime){
					continue;
				}
			} else {
				if ($sRunNow <= $sStartTime || $sRunNow >= $sEndTime){
					continue;
				}
			}
			 
			if (intval($oCompanyCard->UserType) === 2){ // black
				$aBankInfo[$v['payport_name']]['loadmax'] = $oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_blacklimitmax');
				$aBankInfo[$v['payport_name']]['loadmin'] = $oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_blacklimitmin');
				// 检查权限
			} else if (intval($oCompanyCard->UserType) === 1){ // vip
				$aBankInfo[$v['payport_name']]['loadmax'] = $oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_viplimitmax');
				$aBankInfo[$v['payport_name']]['loadmin'] = $oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_viplimitmin');
			} else { // normal
				$aBankInfo[$v['payport_name']]['loadmax'] = $oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_limitmax');
				$aBankInfo[$v['payport_name']]['loadmin'] = $oConfigd->getConfigs($v['sysparam_prefix'] . 'deposit_limitmin');
			}
			 
			// 银行id
			$aBankInfo[$v['payport_name']]['id'] = $v['id'];
			 
			// 银行logo
			$aBankInfo[$v['payport_name']]['logo'] = $v['sysparam_prefix'];
		}
		 
		if (empty($aBankInfo)){
			sysMsg("对不起，您没有操作权限！", 2, $aLinks);
		}

		// 检查用户当天支付中的订单数量是否到达系统上限
		$oDepositAllBank->UserId = $iUserId;
		$iCount = $oDepositAllBank->getcount();
		if ( $iCount >= $oConfigd->getConfigs('maildeposit_times') ){
			sysMsg("对不起，您今天的充值次数已达到充值上限！", 2, $aLinks);
		}


		// 用户建行银行卡绑定信息
		$oUserBankList = new model_withdraw_UserBankList();
		$oUserBankList->Status = 1; // 只提取可用银行信息
		$oUserBankList->UserId = $iUserId; // 只提取可用银行信息
		//    	$oUserBankList->BankId = ICBC_BANK_ID; // 建行
		$oUserBankList->init();
		$oFODetail = new model_withdraw_fundoutdetail();
		$oFODetail->Digit = 4; // 只显示四位卡号
		//检查用户建行卡是否还有其它账户绑定
		$oWithdrawBank = new model_withdraw_ApiWithdrawBank();
		//        $oWithdrawBank->UserId = $iUserId;
		$aBankExist = array();
		$iCountExist = 0; // 用户绑定的银行卡重复的个数
		$aBankList = array();
		$aHaveExist = array(); // 有重复绑定卡号的数组
		$aNoExist = array(); // 无重复绑定卡号的数组
		$aAllBankList = array();
		$iIcbc = 0; // 建行银行卡的个数
		foreach ($oUserBankList->Data as $k => $value){
			//// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
			$oFODetail->Account = $value['account'];
			$oUserBankList->Data[$k]['account'] = $oFODetail->hiddenAccount();
			$oWithdrawBank->Account = $value['account'];
			$iTemp = 0;
			$iTemp = $oWithdrawBank->bankExistByCard();
			if (intval($value['bank_id']) === ICBC_BANK_ID){
				if ($iTemp == 1){
					$oUserBankList->Data[$k]['isexist'] = 1;
					$iCountExist++;
					$aHaveExist[$k] = $value;
					$aHaveExist[$k]['hiddenaccount'] = $oUserBankList->Data[$k]['account'];
				} else {
					$oUserBankList->Data[$k]['isexist'] = 0;
					$aNoExist[$k] = $value;
					$aNoExist[$k]['hiddenaccount'] = $oUserBankList->Data[$k]['account'];
				}
				$aIsExist[$value['id']] = $iTemp;
				$iIcbc++;
			}
			$aAllBankList[$k] = $value;
			$aAllBankList[$k]['hiddenaccount'] = $oUserBankList->Data[$k]['account'];
		}
		if (!empty($aHaveExist) && !empty($aNoExist)){
			$aBankList = array_merge($aNoExist, $aHaveExist);
		} else {
			$aBankList = !empty($aNoExist) ? $aNoExist : $aHaveExist;
		}

		 
		// 检查用户绑定的银行卡是否全部重复
		$iAllExist = 0;
		if ($iCountExist == count($oUserBankList->Data) && count($oUserBankList->Data) > 0){
			$iAllExist = 1;
		}

		$GLOBALS['oView']->assign( 'ur_here', '我要充值');
		$GLOBALS['oView']->assign( 'bankinfo', $aBankInfo);
		$GLOBALS['oView']->assign( 'abankinfo', json_encode($aBankInfo));
		$GLOBALS['oView']->assign( 'banklist', $aBankList);
		$GLOBALS['oView']->assign( 'ishave', $iIcbc > 0 ? 1 : 0);
		$GLOBALS['oView']->assign( 'isexist', json_encode($aIsExist));
		$GLOBALS['oView']->assign( 'allexist', $iAllExist);
		$GLOBALS['oView']->assign( 'allbanklist', $aAllBankList);
		$oEmailDeposit->assignSysInfo();
		$GLOBALS['oView']->display( "emaildeposit_load.html" );
		EXIT;
	}



	/**
	 * 生成订单号
	 */
	public static function generateOrserNumber() {
		return date('YmdHis') . substr(array_pop(explode('.', microtime())), 0, 6);
		
	}

	/**
	 * 充值历史界面
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-03
	 * @package 	passport
	 *
	 */
	/*public function actionEmailDepositList(){
		$oUser = new model_user();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		// 如果为总代管理员，直接提示无权限
		if( $iUserType == 2 )
		{
		//如果为总代管理员，则当前用户调整到其总代ID
		$iUserId = $oUser->getTopProxyId( $iUserId );
		if( empty($iUserId) )
		{
		sysMsg( "操作失败", 2 )
		}
		}

		// 默认查询时间
		$tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
		$today     = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		$sMinTime     = date("Y-m-d H:i:s", $today);
		$sMaxTime     = date("Y-m-d H:i:s", $tomorrow);

		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$pn = 50;

		$oEmailDeposit = new model_deposit_emaildeposit();
		$oEmailDeposit->UserId = $iUserId;
		$oEmailDeposit->Status = "0,1,2,3,4,5";


		$aHtml = array();

		$aHtml['time_min'] 	= $oEmailDeposit->StartTime = !empty($_GET['time_min']) ? daddslashes($_GET['time_min']) : $sMinTime;
		$aHtml['time_max'] 	= $oEmailDeposit->EndTime 	= !empty($_GET['time_max']) ? daddslashes($_GET['time_max']) : $sMaxTime;

		$aResult = $oEmailDeposit->getAllById( $p, $pn );
		$fTotal = 0.00;
		if ( !empty( $aResult ) ){
		foreach ( $aResult as $k => $v ){
		$fTotal += $v['money'];
		}
		}
		$oPager = new pages( $oEmailDeposit->Total, $pn, 0);    // 分页用3
		$GLOBALS['oView']->assign( 'ur_here', '充值历史');
		$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
		$GLOBALS['oView']->assign( 'loadinfo', $aResult);
		$GLOBALS['oView']->assign( 'total', $fTotal);
		$GLOBALS['oView']->assign( 'time_min', $aHtml['time_min']);
		$GLOBALS['oView']->assign( 'time_max', $aHtml['time_max']);
		$oEmailDeposit->assignSysInfo();
		$GLOBALS['oView']->display( "emaildeposit_list.html" );
		EXIT;
		}*/





	/**
	 * 查看指定充值记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-03
	 * @package 	passport
	 *
	 */
	/*public function actionView(){
		$aLinks = array(
		0 => array(
		'title' => "返回充值记录页面",
		'url'	=> "?controller=emaildeposit&action=emaildepositlist"
		)
		);

		$oUser = new model_user();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		// 如果为总代管理员，直接提示无权限
		if( $iUserType == 2 )
		{
		//如果为总代管理员，则当前用户调整到其总代ID
		$iUserId = $oUser->getTopProxyId( $iUserId );
		if( empty($iUserId) )
		{
		sysMsg( "操作失败", 2 )
		}
		}


		$iId = isset($_GET['id']) ? $_GET['id'] : "";
		// 简单数据检查
		if ( !is_numeric($iId) || $iId <= 0){
		sysMsg("您访问的记录不存在！", 2, $aLinks);
		}

		// 检查记录是否存在
		$aResult = array();
		$oEmailDeposit = new model_deposit_emaildeposit();
		$oEmailDeposit->Id = $iId;
		$aResult = $oEmailDeposit->getOneById();
		if (empty($aResult)){
		sysMsg("您访问的记录不存在！", 2, $aLinks);
		}

		// 检查用户操作的记录是否是自己的
		if (intval($iUserId) !== intval($aResult['user_id'])){
		sysMsg("非法请求！", 2, $aLinks);
		}


		$oConfigd = new model_config();
		$aResult['expire'] = date("Y-m-d H:i:s", strtotime($aResult['created']) + $oConfigd->getConfigs('maildeposit_reftime') * 60);

		$GLOBALS['oView']->assign( 'ur_here', '查看详情');
		$GLOBALS['oView']->assign( 'result', $aResult);
		$oEmailDeposit->assignSysInfo();
		$GLOBALS['oView']->display( "emaildeposit_view.html" );
		EXIT;
		}*/






	/**
	 * 取消充值申请
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-13
	 * @package 	passport
	 *
	 */
	/*public function actionCancelLoad(){
		$aLinks = array(
		0 => array(
		'title' => "返回充值记录页面",
		'url'	=> "?controller=emaildeposit&action=emaildepositlist"
		)
		);

		$oUser = new model_user();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		// 如果为总代管理员，直接提示无权限
		if( $iUserType == 2 )
		{
		//如果为总代管理员，则当前用户调整到其总代ID
		$iUserId = $oUser->getTopProxyId( $iUserId );
		if( empty($iUserId) )
		{
		sysMsg( "操作失败", 2 )
		}
		}

		$iId = isset($_GET['id']) ? $_GET['id'] : "";
		// 简单数据检查
		if ( !is_numeric($iId) || $iId <= 0){
		sysMsg("您访问的记录不存在！", 2, $aLinks);
		}

		// 检查记录是否存在
		$aResult = array();
		$oEmailDeposit = new model_deposit_emaildeposit();
		$oEmailDeposit->Id = $iId;
		$aResult = $oEmailDeposit->getOneById();
		if (empty($aResult)){
		sysMsg("您访问的记录不存在！", 2, $aLinks);
		}

		// 检查申请是否过期
		$oConfigd = new model_config();
		$iExpire = 0;
		$iExpire = strtotime($aResult['created']) + $oConfigd->getConfigs('maildeposit_reftime') * 60;
		if ($iExpire < time()){
		sysMsg("充值申请记录已过期，不能取消！", 2, $aLinks);
		}

		// 取消
		$oEmailDeposit->Status = 3; // 取消
		$oEmailDeposit->iSwitch = 1;

		// 检查用户操作的记录是否是自己的
		if (intval($iUserId) !== intval($aResult['user_id'])){
		sysMsg("非法请求！", 2, $aLinks);
		}

		$oUserFund = new model_userfund();
		// 锁定用户资金
		if( FALSE == $oUserFund->switchLock($iUserId, 0, TRUE) )
		{
		sysMsg("你的账号可能因为其它操作被锁定，请稍候重试！", 2, $aLinks);
		}
		// 修改状态
		$bResult = $oEmailDeposit->updateStatus();
		if ($bResult === true){
		$oUserFund->switchLock($iUserId, 0, false);
		sysMsg("操作成功！", 1, $aLinks);
		} else {
		$oUserFund->switchLock($iUserId, 0, false);
		sysMsg("操作失败！", 2, $aLinks);
		}
		}*/
}