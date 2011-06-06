<?php
/**
 * 提现申请操作类
 * 
 * 由于不能在controlle中加入事务处理，因此在此处理提现申请相关的事务操作
 * 
 * 事务开始
 * 01 写入账变
 * 02 写入提现申请
 * 事务结束
 * 
 * @version 	v1.0	2010-03-11
 * @author 		louis
 * 
 * @return 		boolean
 */
class model_withdraw_fundoutapply extends model_pay_base_info{
	
	/**
	 * 错误编号
	 *
	 * @var int
	 */
	public $iError;
	
	/**
	 * 完成提现申请操作流程
	 *
	 * @param int 		$iUserid					// 用户id
	 * @param array 	$aOrder						// 账变信息数组
	 * @param array		$aFundOut					// 提现申请信息数组
	 * 
	 * @version 	v1.0	2010-03-11
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function __construct($aUserInfo, $aOrder, $aFundOut ){
		parent::__construct();
		// 定义全局账变id，在写入账变记录时记录下账变id
    	if (!isset($_iOrderEntryOE)){
    		global $_iOrderEntryOE;
    	}    	
		// 数据检查
		if (empty($aFundOut['money']) || !is_numeric($aFundOut['money']) || empty($aFundOut['charge']) || 
			!is_numeric($aFundOut['charge']) || empty($aFundOut['bank']) || empty($aFundOut['province']) || 
			empty($aFundOut['city']) || empty($aFundOut['branch']) || empty($aFundOut['account_name']) || 
			empty($aFundOut['account']) || empty($aFundOut['api_id']) || !is_numeric($aFundOut['api_id']) || 
			empty($aFundOut['total_money'])) {
        		return $this->iError = -1;  // 基础数据错误
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
				unset($oConfigd);
				return $this->iError = -9;
			}
        	
        	$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
			$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
			$iAgentId = 0;
			
			//调用API检测其他频道是否为负可用余额
	        //获取用户所有可用频道信息
	        /* @var $oChannel model_userchannel */
	        $oUser = new model_user();
			
			if( $iUserType == 2 )
	        {
	            //如果为总代管理员，则当前用户调整到其总代ID
	            $iUserId = $oUser->getTopProxyId( $iUserId );
	            $iAgentId = $_SESSION['userid'];
	            if( empty($iUserId) || empty($iAgentId))
	            {
	                return $this->iError = -3; // 无用户ID
	            }
	        }
        	
        	// 写入提现申请，等待管理员审核与操作
        	$oUserFund = new model_userfund();
        	// 锁定用户资金
        	if( FALSE == $oUserFund->switchLock($iUserId, 0, TRUE) )
	        {
	            return $this->iError = -2; // 锁定用户账户失败
	        }
        	
        	// 事务开始
        	$this->oDB->doTransaction();
	        
	        $oChannel = A::singleton("model_userchannel");
	        $aChannel = $oChannel->getUserChannelList( $iUserId );
	        if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) )
	        {//如果有其他频道
	            foreach( $aChannel[0] as $v )
	            {//依次获取频道余额
	                $oChannelApi = new channelapi( $v['id'], 'getUserCash', FALSE );
	                $oChannelApi->setTimeOut(10);            // 设置读取超时时间
	                $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
	                $oChannelApi->sendRequest( array("iUserId" => $iUserId) );    // 发送结果集
	                $aResult = $oChannelApi->getDatas();
	                if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
	                {//调用API获取结果失败，可能资金帐户不存在
	                   $this->oDB->doRollback(); // 回滚事务
	                   $oUserFund->switchLock($iUserId, 0, false); // 用户解锁
	                   return $this->iError = -4;
	                }
	                if( floatval($aResult['data']) < 0 )
	                {//余额小于0
	                	$this->oDB->doRollback(); // 回滚事务
	                	$oUserFund->switchLock($iUserId, 0, false); // 用户解锁
	                    return $this->iError = -5;
	                }
	            }
	        }
	        
	        // 获取用户可提现金额，总代用户要加上信用余额
    		$aUserAccInfo = array();
    		$oUserFund = new model_userfund();
    		if (($_SESSION['lvtopid'] == $iUserId && $_SESSION['usertype'] == 1) || ($_SESSION['lvtopid'] == 0 && $_SESSION['usertype'] == 2)){
    			$oWithdraw = new model_withdrawel();
				$aUserAcc = $oUserFund->getProxyFundList($iUserId);
		        $aUserAccInfo['amount'] = $oWithdraw->getCreditUserMaxMoney( $iUserId, $aUserAcc[0]['TeamAvailBalance'] );
		        if ($aUserAccInfo['amount'] == "error"){
		        	$this->oDB->doRollback(); // 回滚事务
	            	$oUserFund->switchLock($iUserId, 0, false); // 用户解锁
		        	return $this->iError = -100;
		        }
    		} else {
		        $sFields   = " uf.`availablebalance`";
		        $aUserinfo = $oUserFund->getFundByUser( $iUserId, '', 0, false );
		        $aUserAccInfo['amount'] = $aUserinfo['availablebalance'];
    		}
	        if( empty($aUserAccInfo) ){
	            $this->oDB->doRollback(); // 回滚事务
	            $oUserFund->switchLock($iUserId, 0, false); // 用户解锁
	            return $this->iError = -6;
	        }
	        
	        // 通过提现接口名称查询ID，前期手工提现，只能利用提现接口名称查询ID
			$oPayPortInfo = new model_pay_payaccountinfo('myself');
			$oPayPortInfo->getAccountDataObj();
	        
			/*$oConfig = new model_config();
			if ($oConfig->getConfigs("pay_deduct_in_mode") != 1){ // 外扣
	        	$fMaxAmount = $aUserAccInfo['amount'] - $aCharge[1];
	        } else { // 内扣
	        	$fMaxAmount = $aUserAccInfo['amount'];
	        }*/
			
	        // 检查提现金额是否超出可提现金额
        	if ($aFundOut['total_money'] > $aUserAccInfo['amount'] || $aFundOut['total_money'] < $oPayPortInfo->DrawLimitMinPer || $aFundOut['total_money'] > $oPayPortInfo->DrawLimitMaxPer){
        		$this->oDB->doRollback(); // 回滚事务
        		$oUserFund->switchLock($iUserId, 0, false); // 用户解锁
        		return $this->iError = -7;
        	}
	        
	        // 01 写入账变，冻结金额
        	$oOrders   = new model_orders();
        	if ($iAgentId > 0){
        		$aOrder['iAgentId'] = $iAgentId;
        	}
        	if ($oOrders->addOrders($aOrder) === true) {
        		// 02 写入提现申请
        		$oFODetail = new model_withdraw_fundoutdetail();
        		$oFODetail->OrdersId		= $_iOrderEntryOE;
        		$oFODetail->No				= 'FO' . $_iOrderEntryOE;
        		$oFODetail->ApiId			= $aFundOut['api_id'];
        		$oFODetail->AccId			= $aFundOut['acc_id'];
        		$oFODetail->ApiName			= $aFundOut['api_name'];
        		$oFODetail->ApiNickname		= $aFundOut['api_nickname'];
        		$oFODetail->AccName			= $aFundOut['acc_name'];
        		$oFODetail->MoneyType		= $aFundOut['money_type'];
        		$oFODetail->UserId			= $iUserId;
        		$oFODetail->UserName		= $aUserInfo['username'];
        		$oFODetail->SourceMoney		= floatval($aFundOut['total_money']);
        		$oFODetail->Amount			= $aFundOut['money'];
        		$oFODetail->Charge			= $aFundOut['charge'];
        		$oFODetail->UserBankId		= $aFundOut['userbank_id'];
        		$oFODetail->BankId			= $aFundOut['bank_id'];
        		$oFODetail->BankCode		= $aFundOut['bank_code'];
        		$oFODetail->BankName		= $aFundOut['bank'];
        		$oFODetail->ProvinceId		= $aFundOut['province_id'];
        		$oFODetail->Province		= $aFundOut['province'];
        		$oFODetail->CityId			= $aFundOut['city_id'];
        		$oFODetail->City			= $aFundOut['city'];
        		$oFODetail->Branch			= $aFundOut['branch'];
        		$oFODetail->AccountName		= $aFundOut['account_name'];
        		$oFODetail->Account			= $aFundOut['account'];
        		$oFODetail->RequestTime		= date("Y-m-d H:i:s", time());
        		$oFODetail->IP				= $aFundOut['IP'];
        		$oFODetail->CDNIP			= $aFundOut['CDNIP'];
        		$iInsertDetail = $oFODetail->save();
        	}
        	
        	
        	// 事务结束
        	if (intval($iInsertDetail) > 0){
        		// 事务提交
        		$this->oDB->doCommit();
        		$oUserFund->switchLock($iUserId, 0, false); // 用户解锁
        		return $this->iError = true;
        	} else {
        		// 事务回滚
        		$this->oDB->doRollback();
        		$oUserFund->switchLock($iUserId, 0, false); // 用户解锁
        		return $this->iError = false;
        	}
	}
}