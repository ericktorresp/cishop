<?php
/**
 * 真实扣款操作
 * 
 * 事务开始
 * 先解冻金额，然后真实扣款，定入账变
 * 改变提现申请状态等信息
 * 写入划款明细操作
 * 事务结束
 */
class model_withdraw_withdrawoperate extends model_pay_base_info{
	
	/**
	 * 错误状态，成功为true,失败为false
	 *
	 * @var boolean
	 */
	public $Error;
	
	/**
	 * 平台更改提现成功或提现失败，或者是直接从银行回传值后再更改
	 *
	 * @param 	array 		$aApply				// 记录集
	 * @param 	int 		$iStatus			// 状态码
	 * @param 	string 		$sReason			// 提现失败原因
	 * @param 	boolean		$bIfOrder			// 是否需要写入账变信息，向银行发起查询状态时，如果交易单已经成功，那么不再加入账变信息，只是修												   改平台申请提现状态
	 * @param 	int			$iReturnCode		// 如果是直接向银行发起提现，记录下银行返回的信息码
	 * 
	 * @version 	v1.0	2010-03-25
	 * @author 		louis
	 * 
	 * @return 			boolean
	 */
	public function __construct($aApply, $iStatus, $sReason = '', $iReturnCode = '', $bIfOrder = true){
		parent::__construct();
		// 数据检查
		if (empty($aApply) || !is_numeric($iStatus) || ($iStatus == 6 && empty($sReason))) {
        		return false;
        }
        
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
        
        $iCount = 0;
        $iRequestTime = date("Y-m-d H:i:s", time());
    	foreach ($aApply as $k => $apply ){
    		$oFODetail = new model_withdraw_fundoutdetail($apply);
    		// 事务开始
    		$oUserFund = new model_userfund();
    		// 锁定用户资金
	    	if( FALSE == $oUserFund->switchLock($oFODetail->UserId, 0, TRUE) )
	        {
	            continue;
	        }
	        
	        $aResult = $oFODetail->getInfoByOrderId();
	        
	        // 首先做出检查，如果当前记录的状态不为处理中，则直接跳出循环。
    		if ($aResult['status'] != 7){
    			$oUserFund->switchLock($oFODetail->UserId, 0, false);
    			continue;
    		}
    		
    		
	        $this->oDB->doTransaction();
	        
	        /*// 判断剩余发起提现次数是否用完，如果提现次数已用完，则直接将提现明细状态改为失败。
//	        $oFODetail->Status			= ($oFODetail->RemainTimes > 0 ) ? $iStatus : 6 ;
	        if ($oFODetail->RemainTimes > 0){
	        	$oFODetail->Status = $iStatus;
	        	$oFODetail->UnverifyComment = trim($sReason);
	        } else {
	        	$oFODetail->Status = 6;
	        	$oFODetail->UnverifyComment = "可操作次数已用完";
	        }*/
//	        if ($oFODetail->Status == 6) $oFODetail->UnverifyComment = trim($sReason);
//	        $oFODetail->Where 			= "status = 7";
	        /*if (!$oFODetail->save()) {
	        	// 事务回滚
	    		$this->oDB->doRollback();
	    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
	    		continue;
	        }*/
	        
	        if ($bIfOrder){
	        	// 01 写入账变
	        	$oOrders   = new model_orders();
	        	 // 01-01 先解除冻结金额
	        	 // 账变信息数组
				$aOrders = array();
		    	$aOrders['iFromUserId'] 	= $oFODetail->UserId; // (发起人) 用户id
		    	$aOrders['iToUserId'] 		= 0; // (关联人) 用户id
		    	$aOrders['fMoney'] 			= floatval($oFODetail->Amount + $oFODetail->Charge); // 账变的金额变动情况
		    	$aOrders['iChannelID'] 		= 0; // 发生帐变的频道ID
		    	$aOrders['iAdminId'] 		= $_SESSION['admin']; // 管理员id
		        $aOrders['iOrderType'] 		= ORDER_TYPE_ZXTXJD; // 账变类型
		        $aOrders['sDescription'] 	= '提现解冻'; // 账变的描述
		        if ($oOrders->addOrders($aOrders) !== TRUE){
		        	// 事务回滚
		    		$this->oDB->doRollback();
		    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
		    		continue;
	        	}

	        	if ($iStatus == 5){ // 只有提现成功才会真实扣款，失败则解冻后停止资金操作
	        		// 02-02 真实扣款(写入两条账变，一条提现金额，一条手续费)
		        	// 扣取提现金额（不含手续费）
			    	$aOrders['fMoney'] 			= floatval($oFODetail->Amount); // 账变的金额变动情况
			        $aOrders['iOrderType'] 		= ORDER_TYPE_ZXTXKK; // 账变类型
			        $aOrders['sDescription'] 	= '提现扣款'; // 账变的描述
			        if ($oOrders->addOrders($aOrders) !== TRUE){
			        	// 事务回滚
			    		$this->oDB->doRollback();
			    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
			    		continue;
		        	}
		        	// 扣取手续费
		        	if ($oFODetail->Charge > 0){ // 手续费大于0时，才显示扣取手续费账变
		        		$aOrders['fMoney'] 			= floatval($oFODetail->Charge); // 账变的金额变动情况
				        $aOrders['iOrderType'] 		= ORDER_TYPE_ZXTXSF; // 账变类型
				        $aOrders['sDescription'] 	= '提现扣款手续费'; // 账变的描述
				        if ($oOrders->addOrders($aOrders) !== TRUE){
				        	// 事务回滚
				    		$this->oDB->doRollback();
				    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
				    		continue;
			        	}
		        	}
	        	}
	        }
	        
	        
	        // 扣减一次可提现次数
	        if (!$oFODetail->reduceTimes()){
	        	// 事务回滚
	    		$this->oDB->doRollback();
	    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
	    		continue;
	        }
	        
	        // 03 更改申请提现状态
	        $oFODetail->FinishTime		= date("Y-m-d H:i:s", time());
	        /*if (!empty($aDetail['operater'])){
	        	$oFODetail->Operater		= $aDetail['operater'];
	        }*/
	        $oFODetail->AdminId			= $_SESSION['admin'];
	        $oFODetail->Operater		= $_SESSION['adminname'];
	        /*// 判断此次操作后，剩余发起提现次数是否用完，如果提现次数已用完，则直接将提现明细状态改为失败。
	        $oFODetail->Status			= ($oFODetail->RemainTimes > 0 ) ? $iStatus : 6 ;
	        if ($oFODetail->Status == 6) $oFODetail->UnverifyComment = trim($sReason);
	        $oFODetail->Where 			= "status = 7";
	        if (!$oFODetail->save()) {
	        	// 事务回滚
	    		$this->oDB->doRollback();
	    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
	    		continue;
	        }*/
	        $oFODetail->Status			= $iStatus;
	        if ($iStatus == 6) $oFODetail->UnverifyComment = trim($sReason);
	        $oFODetail->Where 			= "status = 7";
	        
	        if (!$oFODetail->save()) {
	        	// 事务回滚
	    		$this->oDB->doRollback();
	    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
	    		continue;
	        }
	        
	        
	        // 04 写入划款操作明细
	        $oFODetail->RequestTime		= $iRequestTime;
	        $oFODetail->FinishTime		= date("Y-m-d H:i:s", time());
	        $oFODetail->AdminId			= $_SESSION['admin'];
	        $oFODetail->Operater		= $_SESSION['adminname'];
	        $oFODetail->ReturnCode		= intval($iReturnCode);
	        $oFODetail->Status			= $oFODetail->Status;
	        $iLastId = $oFODetail->addOperateInfo();
	        
	        // 通知用户提现结果
			$oMessage = new model_message();
	        if (!$iLastId){
	        	// 事务回滚
	    		$this->oDB->doRollback();
	    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
	    		continue;
	        } else {
	        	if ($iStatus == 5){
	        		$aNewMsg['subject'] = '订单编号：' . $oFODetail->No . '--在线提现成功';
					$aNewMsg['content'] = "在线提现成功";
	        	} else {
	        		$aNewMsg['subject'] = '订单编号：' . $oFODetail->No . '--在线提现失败';
					$aNewMsg['content'] = $oFODetail->UnverifyComment;
	        	}
	        	$aNewMsg['receiverid'] = $oFODetail->UserId;
	        	if ($oMessage->sendMessageToUser($aNewMsg) > 0){
	    			// 事务提交
		    		$this->oDB->doCommit();
		    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
		    		$iCount++;
	    		} else {
	    			// 事务回滚
		    		$this->oDB->doRollback();
		    		$oUserFund->switchLock($oFODetail->UserId, 0, false);
		    		continue;
	    		}
	        }
    	}
    	$this->Error = $iCount > 0 ? true : false;
	}
}