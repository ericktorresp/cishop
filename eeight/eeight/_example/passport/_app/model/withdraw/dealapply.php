<?php
/**
 * 提现申请审核未通过或取消，解冻资金
 * 
 * 由于不能在controlle中加入事务处理，因此在此处理提现申请相关的事务操作
 * 
 * @version 	v1.0	2010-03-25
 * @author 		louis
 * 
 * @return 		boolean
 */
class model_withdraw_dealapply extends model_pay_base_info{

	/**
	 * 错误状态，成功为true,失败为false
	 *
	 * @var boolean
	 */
	public $Error;
	
	/**
	 * 返还用户冻结资金
	 * 
	 * @param 	array		$aApply			// 提现申请记录集
	 * @param 	int			$iStatus		// 状态
	 * @param 	strin		$sReason		// 审核未通过或取消时的原因
	 * 
	 * @version 	v1.0	2010-03-25
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function __construct($aApply, $iStatus, $sReason = ''){
		parent::__construct();
		// 数据检查
		if (empty($aApply) || !is_numeric($iStatus)) {
        		return false;
        }
        
        if ($iStatus == 3){
        	// 在 系统参数"禁止转账时间" 禁止提现
	    	$oConfigd = new model_config();
			$sDenyTime = $oConfigd->getConfigs('zz_forbid_time');
			$aDenyTime = explode('-',$sDenyTime);
			// 24小时值时间值，无前导0
			$sRunNow = date('G:i');
			$bRunTimeChk = true;
			if ( ($sRunNow > $aDenyTime[1]) || ($sRunNow < $aDenyTime[0]) ){
				$bRunTimeChk = false;
			}
			
			
			// 在时间上禁止提现
			if ( $bRunTimeChk === true ){
				return $this->Error = -1;
			}
        }
		
        $iCount = 0;
    	foreach ($aApply as $k => $apply ){
    		$oFODetail = new model_withdraw_fundoutdetail($apply);
    		$oUserFund = new model_userfund();
    		// 锁定用户资金
	    	if( FALSE == $oUserFund->switchLock($oFODetail->UserId, 0, TRUE) )
	        {
	            continue;
	        }
	        
	        $aResult = $oFODetail->getInfoByOrderId();
	        
    		// 首先做出检查，如果当前记录的状态不为未审核，则直接跳出循环。
    		if ($aResult['status'] != 1){
    			$oUserFund->switchLock($oFODetail->UserId, 0, false);
    			continue;
    		}
	        
	        $this->oDB->doTransaction();
	        if ($iStatus == 3){
	        	// 写入账变数据数组
	        	$aOrders = array();
	        	$aOrders['iFromUserId'] = $oFODetail->UserId; // (发起人) 用户id
	        	$aOrders['iToUserId'] = 0; // (关联人) 用户id
	        	$aOrders['iOrderType'] = 32; // 账变类型
	        	$aOrders['fMoney'] = floatval($oFODetail->Amount + $oFODetail->Charge); // 账变的金额变动情况
	        	$aOrders['sDescription'] = "提现审核未通过"; // 账变的描述
	        	$aOrders['iChannelID'] = 0; // 发生帐变的频道ID
	        	$aOrders['iAdminId'] = $_SESSION['admin']; // 管理员id
	        	// 01 写入账变，冻结金额
	        	$oOrders   = new model_orders();
	        	$bResult = $oOrders->addOrders($aOrders);
	        	
	        	// 如果未通过则给用户发短消息通知
	        	if ($bResult === TRUE){
	        		// 通知用户提现审核未通过
					$oMessage = new model_message();
			        $aNewMsg['subject'] = '订单编号：' . $oFODetail->No . '--提现申请未通过';
			        $aNewMsg['content'] = $sReason;
			        $aNewMsg['receiverid'] = $oFODetail->UserId;
	        		if ($oMessage->sendMessageToUser($aNewMsg) <= 0){
	        			// 事务回滚
		        		$this->oDB->doRollback();
		        		$oUserFund->switchLock($oFODetail->UserId, 0, false);
		        		continue;
	        		}
	        	} else {
	        		// 事务回滚
	        		$this->oDB->doRollback();
	        		$oUserFund->switchLock($oFODetail->UserId, 0, false);
	        		continue;
	        	}
	        }
	        if ($iStatus == 2) $bResult = true;
	        if ($bResult === true){
	        	// 如果为审核未通过或取消，则在解冻资金成功后改变申请状态，如果为审核成功，则直接更改状态，不用操作账户
	        	$oFODetail->VerifyAdminId   = $_SESSION['admin'];
	        	$oFODetail->Verify			= $_SESSION['adminname'];
				$oFODetail->Verify_time 	= date("Y-m-d H:i:s", time()); 
				$oFODetail->Status 			= $iStatus; // 审核状态
				$oFODetail->UnverifyComment	= $sReason;
				$oFODetail->Where			= "status = 1";
				$iResult = $oFODetail->save();
	        }
	        
	        if ($iResult){
        		// 事务提交
        		$this->oDB->doCommit();
        		$oUserFund->switchLock($oFODetail->UserId, 0, false);
        		$iCount++;
        	} else {
        		// 事务回滚
        		$this->oDB->doRollback();
        		$oUserFund->switchLock($oFODetail->UserId, 0, false);
        	}
    	}
        $this->Error = $iCount > 0 ? true : false;
	}
}