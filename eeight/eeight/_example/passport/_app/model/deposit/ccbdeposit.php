<?php

/**
 * 路径:/_app/model/emaildeposit.php
 * 功能：email充值的相关操作
 *
 * 方法：
 * --getOneById							通过id查询记录信息
 * --getOneBankRecord					获取银行抓取信息
 * --getLastRecords						获取指定记录以前的5条记录
 * --getBankRecord						获取银行抓取记录信息
 * --getRecord							获取用户充值未处理的记录信息
 * --updateBankAndInsert				修改银行抓取记录状态并写入一条异常记录
 * --updateBankStatus					修改银行抓取记录状态
 * --insertErrorData					写入一条异常记录
 * --createParam						组成写入异常充值列表的数据
 * --getWrondStyle						获取违规类型，如果未出现违规，则返回0
 * --hidAccount							模仿建行，隐藏５位卡号
 * --unionUpdate						联合修改银行抓取记录表和充值记录表的状态
 * --updateStatus						修改充值记录的状态
 * --realLoad							充值操作
 * --insertRecord						写入充值申请记录
 * --updateAllProcess                   修改所有的待处理的记录为挂起单并写入一条异常记录(供计划任务使用）
 * --cleanErrors                        清理指定天数以前的充值异常记录,只清除处理过的记录
 * --cleanRecords                       清理指定天数之前的建行充值记录,只清理处理过的记录
 *
 *
 *
 * email_deposit_record表结构
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * id 				int(10)
 * user_id 			int(10) 		用户id
 * user_name 		varchar(50)		用户名
 * topproxy_name	varchar(50)		总代名
 * money 			decimal(14,4)	汇款金额
 * account_id 		int(10)			银行卡id
 * account 			varchar(50)		银行卡卡号
 * account_name 	varchar(50)		开户姓名
 * payacc_id		int(10)			支付接口分账户id
 * accept_card 		varchar(50)		收款卡号
 * transfer_id		int(10)			对应建行抓取记录id
 * status 			tinyint(4)		状态，０为未处理，１为成功，２为挂起，３已退款，４为管理员处理，５为没收
 * admin_id			int(10)			操作管理员id
 * admin_name 		varchar(50) 	操作管理员
 * error_type 		tinyint(4)		违规类型，１时间违规，２为付款账号违规，３为付款用户违规，４为收款账户违规， 5金额违规
 * remark 			text			管理员处理备注
 * created			datetime		记录写入时间
 * deal_time		datetimeCOMMENT	管理员处理时间
 * modified			timestamp		记录修改时间
 * add_money_time	datetime		加游戏币时间
 *
 *
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-11-22
 * @package 	passport
 *
 */
define("HIDDEN_LENGTH", 5);			// 卡号隐藏5位
define("LEAVE_LENGTH", 6);			// 隐藏卡号后还剩4位
class model_deposit_ccbdeposit extends basemodel{
	
	
	/**
	 * id
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 银行抓取记录id
	 *
	 * @var int
	 */
	public $BankRecordId;
	
	/**
	 * 抓取记录状态, 0为待处理，1为成功，2为失败，3为其它方式已处理
	 *
	 * @var int
	 */
	public $BankStatus;
	
	/**
	 * 汇款账号
	 *
	 * @var string
	 */
	public $Account;
	
	/**
	 * 隐藏了五位的卡号
	 *
	 * @var string
	 */
	public $HiddenAccount;
	
	/**
	 * 充值金额
	 *
	 * @var float
	 */
	public $Amount;
	
	/**
	 * 汇款账户名
	 *
	 * @var string
	 */
	public $AccName;
	
	/**
	 * 收款账户名
	 *
	 * @var string
	 */
	public $AcceptName;
	
	/**
	 * 收款卡号
	 *
	 * @var string
	 */
	public $AcceptCard;
	
	/**
	 * 汇款日期
	 *
	 * @var date
	 */
	public $PayDate;
	
	/**
	 * 平台记录状态  ０为未处理，１为成功，２为挂起，３已退款，４为管理员处理，５为没收
	 *
	 * @var int
	 */
	public $Status;
	
	/**
	 * 违规类型 1为时间违规，２为付款账号违规，３为付款用户违规，４为收款账户违规， 5金额违规
	 *
	 * @var int
	 */
	public $ErrorType;
	
	/**
	 * 支付接口分账户id
	 *
	 * @var int
	 */
	public $PayACCId;
    
    /**
     * 银行id
     *
     * @var  int
     */
    public $BankId;
    public $OrderNumber;
    public $NameTail;
    public $SmsNumber;
    
    
	
	/**
	 * 通过id查询记录信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-03
	 * @package 	passport
	 *
	 *
	 *
	 */
	public function getOneById(){
		$aRecord = array();
		// 数据检查
		if ( !is_numeric($this->Id) || $this->Id <= 0){
			return $aRecord;
		}
		
		$sSql = "SELECT * FROM `ccb_deposit_record` WHERE `id` = {$this->Id}";
		$aRecord = $this->oDB->getOne($sSql);
		return $aRecord;
	}
	
	public function getOneByOrderNumber(){
		$aRecord = array();
		// 数据检查
		if ( !is_numeric($this->OrderNumber) ){
			return $aRecord;
		}
		
		$sSql = "SELECT * FROM `ccb_deposit_record` WHERE `order_number` = '{$this->OrderNumber}'";
		$aRecord = $this->oDB->getOne($sSql);
		return $aRecord;
	}
	
	/**
	 * 获取银行抓取信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		array
	 *
	 */
	public function getOneBankRecord(){
		$aResult = array();
		// 数据检查
		if (!is_numeric($this->BankRecordId) || $this->BankRecordId <= 0){
			return $aResult;
		}
		$sSql = "SELECT * FROM `ccb_transfers` WHERE `id` = {$this->BankRecordId}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
	
	
	
	/**
	 * 获取指定记录以前的5条记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		array
	 *
	 */
	public function getLastRecords(){
		$aResult = array();
		// 数据检查
		if (!is_numeric($this->Id) || $this->Id <= 0){
			return $aResultl;
		}
		$sSql = "SELECT * FROM `ccb_transfers` WHERE `id` <= {$this->Id} order by id desc LIMIT 6";
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	/**
	 * 获取银行抓取记录信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		array			$aResult			// 记录集
	 *
	 */
	public function getBankRecord(){
		$sWhere = "";
		if (is_numeric($this->BankStatus))				$sWhere .= " AND `status` = {$this->BankStatus}";
		$sSql = "SELECT * FROM `ccb_transfers` WHERE 1 " . $sWhere;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	/**
	 * 获取用户充值未处理的记录信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @return 		array		$aResult
	 * @package 	passport
	 *
	 */
	public function getRecord() {
		$aResult = array();
		// 数据检查
		if (floatval($this->Amount) <= 0 || (empty($this->Account) && empty($this->HiddenAccount)) || empty($this->AccName) ||
			empty($this->AcceptName) || empty($this->AcceptCard) || empty($this->PayDate)){
			return false;
		}
		// 利用全卡号查询
		$sSql = "SELECT * FROM `ccb_deposit_record` WHERE `money` = {$this->Amount} AND `account` = '{$this->Account}' AND `account_name` = '{$this->AccName}' AND `accept_name` = '{$this->AcceptName}' AND `accept_card` = '{$this->AcceptCard}' AND `created` LIKE '{$this->PayDate}%' AND status = 0 LIMIT 1";
		$aResult = $this->oDB->getAll($sSql);
		
		// 如果不存在，则利用隐藏卡号再查询一次
		if (empty($aResult)){
			$aTemp = $this->hidAccount($this->HiddenAccount, 2);
			$sSql = "SELECT * FROM `ccb_deposit_record` WHERE `money` = {$this->Amount} AND `account` LIKE '{$aTemp[0]}%{$aTemp[1]}' AND `account_name` = '{$this->AccName}' AND `accept_name` = '{$this->AcceptName}' AND `accept_card` = '{$this->AcceptCard}' AND `created` LIKE '{$this->PayDate}%' AND status = 0 LIMIT 1";
		$aResult = $this->oDB->getAll($sSql);
		}
		return $aResult;
	}
	
	
	
	
	/**
	 * 修改银行抓取记录状态并写入一条异常记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		boolean
	 *
	 */
	public function updateBankAndInsert( $aData ){
		// 数据检查
		if (!is_array($aData) || empty($aData) || (empty($this->NameTail) && empty($this->Account)))
		{
			return false;
		}
		
		$bAdd = true; // 是否更新接口余额,true为更新，false为不更新，默认为true
		
//		$oPayPortInfo = new model_pay_payaccountinfo($this->Account, 'banknumber');
		if(!empty($this->NameTail))
		{
        	$oPayPortInfo = new model_deposit_depositaccountinfo($this->NameTail, 'nametail');
		}
		elseif(!empty($this->Account))
		{
			$oPayPortInfo = new model_deposit_depositaccountinfo($this->Account, 'banknumber');
		}
		$oPayPortInfo->GetType = true;
		if (intval($oPayPortInfo->AId) > 0)
		{
			$oPayPortInfo->getAccountDataObj();
		}
		else
		{
			$bAdd = false;
		}
		// 事务开始
		$this->oDB->doTransaction();
		
		if ($bAdd === true)
		{
			// 修改分账户余额
	    	$oPayPortInfo->DepositValue['inbalance'] = $aData['pay_amount'];
	    	$oPayPortInfo->DepositValue['ppid'] = $oPayPortInfo->PaySlotId;
	    	$oPayPortInfo->DepositValue['accid'] = $oPayPortInfo->AId;
	    	$bSaveBalance = $oPayPortInfo->saveBalanceReceive($aData['pay_amount']);
	    	
	    	if ($bSaveBalance === false)
	    	{ // 更新接口余额失败
	    		$this->oDB->doRollback(); // 事务回滚
				return false;
	    	}
		}

		$bStatus = $this->updateBankStatus();
		if ($bStatus === false)
		{
			$this->oDB->doRollback(); // 事务回滚
			return false;
		}
		
		$bInsert = $this->insertErrorData( $aData );
		if ($bInsert === false)
		{
			$this->oDB->doRollback(); // 事务回滚
			return false;
		}
		
		$this->oDB->doCommit(); // 事务提交
		return true;
	}
	
	
	
	
	/**
	 *　修改银行抓取记录状态
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		boolean
	 *
	 */
	public function updateBankStatus(){
		if (!is_numeric($this->BankStatus) || !is_numeric($this->BankRecordId) || $this->BankRecordId <= 0)			return false;
		$sSql = "UPDATE `ccb_transfers` SET `status` = {$this->BankStatus} WHERE `id` = {$this->BankRecordId} AND `status` = 0";
		$this->oDB->query($sSql);
		
		return ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0) ? false : true;
	}
	
	
	
	
	/**
	 * 写入一条异常记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-22
	 * @package 	passport
	 *
	 * @return 		boolean
	 *
	 */
	public function insertErrorData( $aData ){
		// 数据检查
		if (!is_array($aData) || empty($aData)){
			return false;
		}
		
		return $this->oDB->insert('ccb_deposit_error', $aData);
	}
	
	
	
	
	
	/**
	 * 组成写入异常充值列表的数据
	 *
	 * @param 		array		$value			// 数据内容
	 * @param 		int			$style			// 封闭类型
	 * 											   1为修改银行抓取信息为挂起状态时写入异常表的数据
	 * 											   2为修改平台充值记录为挂起状态时写入异常表的数据
	 * 											   3为同时修改平台充值记录和银行抓取记录为挂起状态时写入异常表的数据
	 * @param 		int			$error_type		// 违规类型，根据各银行表的定义有所不同
	 * @param 		int			$status			// 记录状态，根据各银行表的定义有所不同
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		array
	 *
	 */
	public function createParam($value, $style, $error_type, $status){
		$aResult = array();
		if (!is_array($value) || empty($value) || intval($style) <= 0 || !is_numeric($error_type) || !is_numeric($status)){
			return false;
		}
		
		switch ($style){
			case 1:
				$aResult['get_time'] = $value['create'];
				$aResult['pay_card'] = $value['hidden_account'];
				$aResult['pay_acc_name'] = $value['acc_name'];
				$aResult['get_card'] = $value['accept_card'];
				$aResult['pay_amount'] = $value['amount'];
				$aResult['balance'] = $value['balance'];
				$aResult['pay_fee'] = $value['fee'];
				$aResult['encode_key'] = $value['encode_key'];
				$aResult['transfer_id'] = $value['id'];
				$aResult['error_type'] = $error_type;
				$aResult['status'] = $status;
				$aResult['created'] = date("Y-m-d H:i:s", time());
				break;
			case 2:
				break;
			case 3:
				break;
			default:
				return false;
		}
		return empty($aResult) ? false : $aResult;
	}
	
	
	
	
	
	/**
	 * 获取违规类型，如果未出现违规，则返回0
	 *
	 * @param 		array			$value			// 银行抓取信息
	 * @param 		int				$id				// 平台充值申请记录id
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		int				0为未违规，1为时间违规，2为付款账号违规，3为付款户名违规，4为收款账户违规，5为金额违规
	 *
	 */
	public function getWrondStyle($value, $id){
		//  数据检查
		if (empty($value['pay_date']) ||
			empty($value['acc_name']) || 
			empty($value['accept_card']) || 
			floatval($value['amount']) <= 0){
			return false;
		}
		
		$this->Id = $id;
		$aResult = $this->getOneById();
		if (empty($aResult)){
			return false;
		}
		
		$aTemp = explode(" ", $aResult['created']);
		if ($value['pay_date']  > $aTemp[0] . " 23:59:59"){ // 时间违规
			return 1;
//		} else if(trim( $value['full_account'] ) !== trim( $aResult['account'] )) { // 付款账号违规
//			$temp = $this->hidAccount($aResult['account']);
//			if (trim( $value['hidden_account'] ) !== trim( $temp )){
//				return 2;
//			}
		} else if (trim( $value['acc_name'] ) != trim( $aResult['account_name'] )){ // 付款户名违规
			return 3;
		} else if (trim( $value['accept_card'] ) != substr(trim( $aResult['accept_card'] ),-4)){ // 收款账户违规
			return 4;
		} else if (floatval( $value['amount'] ) != floatval( $aResult['money'] )){
			return 5;
		} else {
			return 0;
		}
	}
	
	
	
	
	/**
	 * 模仿建行，隐藏５位卡号
	 *
	 * @param string $account				// 账号
	 * @param int	 $style					// 1为返回隐藏后的卡号，2为返回拆开后的数组
	 * @return string						// 隐藏后的账号
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 */
    public function hidAccount( $account, $style = 1){
    	$sFront = substr($account, 0, strlen($account) - HIDDEN_LENGTH - LEAVE_LENGTH);
    	$sLast = substr($account, 0 - LEAVE_LENGTH);
    	if ($style === 1){
    		return $sFront . str_repeat("*", HIDDEN_LENGTH) . $sLast;
    	} else if ($style === 2){
    		$aResult = array($sFront, $sLast);
    		return $aResult;
    	} else {
    		return false;
    	}
    }
    
    
    
    
    /**
	 * 联合修改银行抓取记录表和充值记录表的状态
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		boolean
	 *
	 */
	public function unionUpdate( $aData = array() ){
		if (!is_numeric($this->Status) || !is_numeric($this->BankStatus) || empty($this->Account))			return false;
		if (!is_numeric($this->Id) || $this->Id <= 0)														return false;
		if (!is_numeric($this->BankRecordId) || $this->BankRecordId <= 0)									return false;
		
		$bAdd = true; // 是否更新接口余额,true为更新，false为不更新，默认为true
		
//		$oPayPortInfo = new model_pay_payaccountinfo($this->Account, 'banknumber');
        $oPayPortInfo = new model_deposit_depositaccountinfo($this->Account, 'banknumber');
		$oPayPortInfo->GetType = true;
		if ($oPayPortInfo->AId > 0){
			$oPayPortInfo->getAccountDataObj();
		} else {
			$bAdd = false;
		}
		
		// 事务开始
		$this->oDB->doTransaction();
		
		if ($bAdd === true){
			// 修改分账户余额
	    	$oPayPortInfo->DepositValue['inbalance'] = $aData['pay_amount'];
	    	$oPayPortInfo->DepositValue['ppid'] = $oPayPortInfo->PaySlotId;
	    	$oPayPortInfo->DepositValue['accid'] = $oPayPortInfo->AId;
	    	$bSaveBalance = $oPayPortInfo->saveBalanceReceive($aData['pay_amount']);
	    	
	    	if ($bSaveBalance === false){ // 更新接口余额失败
	    		$this->oDB->doRollback(); // 事务回滚
				return false;
	    	}
		}
		
		// 先修改充值记录表
		$this->iSwitch = 1;
		$bResult = $this->updateStatus();
		if ($bResult === false){
			$this->oDB->doRollback();	// 事务回滚
			$this->iSwitch = 0;
			return false;
		}
		
		// 修改银行抓取记录表
		$sSql = "UPDATE `ccb_transfers` SET `status` = {$this->BankStatus} WHERE `id` = {$this->BankRecordId} AND `status` = 0";
		$this->oDB->query($sSql);
		if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
			$this->oDB->doRollback();	// 事务回滚
			$this->iSwitch = 0;
			return false;
		}
		
		if (intval($this->Status) === 2 && intval($this->BankStatus) === 2){ // 写入一条异常记录
			// 数据检查
			if (!is_array($aData) || empty($aData)){
				$this->oDB->doRollback();	// 事务回滚
				return false;
			}
			
			if ($this->insertErrorData( $aData ) === false){
				$this->oDB->doRollback();	// 事务回滚
				return false;
			}
		}
		$this->oDB->doCommit();	// 事务提交
		$this->iSwitch = 0;
		return true;
	}
	
	
	
	
	/**
	 * 修改充值记录的状态
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return 		boolean
	 *
	 */
	public function updateStatus(){
		// 数据检查
		if ( empty( $this->Id ) || !is_numeric($this->Status) || $this->Status < 0 ){
			return false;
		}
		$sAnd = "";
		if ($this->iSwitch === 1 && is_numeric($this->BankRecordId) && $this->BankRecordId > 0){
			$sAnd .= ",`transfer_id` = {$this->BankRecordId}";
		}
		if ($this->iSwitch === 1 && $this->ErrorType > 0){
			$sAnd .= ",`error_type` = {$this->ErrorType}";
		}
		
		if ($this->AddMoneyTime === true){
			$sAnd .= ",`add_money_time` = '" . date("Y-m-d H:i:s", time()) . "'";
		}
		
        $sSql = "UPDATE `ccb_deposit_record` SET `status` = {$this->Status}" . $sAnd . " WHERE `id` IN ({$this->Id}) AND `status` = 0";
        
        $this->oDB->query($sSql);
        if ($this->iSwitch === 1){
        	return ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0 ) ? false : true;
        }
        
        return $this->oDB->errno() > 0 ? false : true;
	}
	
	
	
	
	/**
	 * 充值操作
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 *
	 * @return
	 *
	 */
	public function realLoad( $aOrders, $aFee = array(), $recordId = 0, $acc_id = 0 ){
		// 数据检查
		if ( empty( $aOrders ) ){
			return false;
		}
		if ( $aOrders['iToUserId'] <= 0 || $aOrders['fMoney'] <= 0 || $aOrders['iOrderType'] <= 0){
			return false;
		}
		if ( !is_numeric($this->Id) || $this->Id <= 0 || !is_numeric($acc_id) || $acc_id <= 0){
			return false;
		}
		if ($recordId <= 0){
			return false;
		}
		$oUserFund = new model_userfund();
		// 锁定用户资金
    	if( FALSE == $oUserFund->switchLock($aOrders['iToUserId'], 0, TRUE) )
        {
            return false;
        }
        // 开始事务
        $this->oDB->doTransaction();
        
        // 01 写入账变
	    $oOrders   = new model_orders();
	    if ($oOrders->addOrders($aOrders) !== TRUE){
        	// 事务回滚
    		$this->oDB->doRollback();
    		$oUserFund->switchLock($aOrders['iToUserId'], 0, false);
    		return false;
    	}
    	
    	if (floatval($aFee['fMoney']) > 0){ // 手续费金额大于0，则返还手续费
    		if ($oOrders->addOrders($aFee) !== TRUE){
	        	// 事务回滚
	    		$this->oDB->doRollback();
	    		$oUserFund->switchLock($aFee['iToUserId'], 0, false);
	    		return false;
	    	}
    	}
    	
    	// 修改分账户余额
    	$oPayAccInfo = new model_deposit_depositaccountinfo($acc_id);
    	$oPayAccInfo->DepositValue['inbalance'] = $aOrders['fMoney'];
    	$oPayAccInfo->DepositValue['ppid'] = $oPayAccInfo->PaySlotId;
    	$oPayAccInfo->DepositValue['accid'] = $acc_id;
    	$oPayAccInfo->DepositValue['userid'] = $aOrders['iToUserId'];
    	$bSaveBalance = $oPayAccInfo->saveBalanceReceive($aOrders['fMoney']);
    	if ($bSaveBalance === false){ // 更新分账户余额失败
    		// 事务回滚
    		$this->oDB->doRollback();
    		$oUserFund->switchLock($aOrders['iToUserId'], 0, false);
    		return false;
    	}
    	
    	// 修改充值记录状态，改为已成功
    	$this->Status = 1; // 成功
    	$this->BankRecordId = $recordId;
    	$this->iSwitch = 1; // 打开开关
    	$this->AddMoneyTime = true;
    	$bResult = $this->updateStatus();
    	if ( $bResult === false ){
    		// 事务回滚
    		$this->oDB->doRollback();
    		$this->iSwitch = 0; // 关闭开关
    		$oUserFund->switchLock($aOrders['iToUserId'], 0, false);
    		return false;
    	}
    	
    	// 修改从银行抓取的记录表为已完成
    	$this->BankStatus = 1; // 成功
    	$this->BankRecordId = $recordId;
    	$bBankResult = $this->updateBankStatus();
    	if ($bBankResult === false){
    		// 事务回滚
    		$this->oDB->doRollback();
    		$this->iSwitch = 0; // 关闭开关
    		$oUserFund->switchLock($aOrders['iToUserId'], 0, false);
    		return false;
    	}
    	
    	
    	$this->oDB->doCommit();
    	$this->iSwitch = 0; // 关闭开关
    	$oUserFund->switchLock($aOrders['iToUserId'], 0, false);
    	return true;
	}
	
	
	
	
	/**
	 * 写入充值申请记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-23
	 * @package 	passort
	 *
	 * @return 		int				$iLastInsertId;
	 *
	 */
	public function insertRecord(){
		// 数据检查
		if ( !is_numeric( $this->UserId ) || $this->UserId <= 0 || empty( $this->UserName ) || !is_float( $this->Money )
			|| $this->Money <= 0 || empty( $this->Account ) || empty( $this->AccountName ) || !is_numeric( $this->AccountId)
			|| $this->AccountId <= 0 || !is_numeric($this->PayACCId) || $this->PayACCId <= 0 || empty($this->AcceptCard) ||
				empty($this->AcceptName)){
				return FALSE;
			}
		
		$aData = array(
			'user_id' 		=> $this->UserId,
			'user_name'		=> $this->UserName,
			'topproxy_name'	=> $this->TopProxyName,
			'money'			=> $this->Money,
			'account_id'	=> $this->AccountId,
			'account'		=> $this->Account,
			'account_name'	=> $this->AccountName,
			'payacc_id'		=> $this->PayACCId,
			'accept_name'	=> $this->AcceptName,
			'accept_card'	=> $this->AcceptCard,
			'created'		=> date("Y-m-d H:i:s", time()),
			'order_number'	=> $this->OrderNumber,
			'sms_number'	=> $this->SmsNumber,
		);
		$iLastInsertId = $this->oDB->insert( 'ccb_deposit_record', $aData );
		return $iLastInsertId > 0 ? $iLastInsertId : FALSE;
	}
    
    
    
    
    /**
	 * 修改所有的待处理的记录为挂起单并写入一条异常记录(供计划任务使用）
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-12-10
	 * @package 	passport
	 *
	 * @return 		mix					true			// 执行成功
	 * 									$iError			// 失败的条数
	 *
	 */
	public function updateAllProcess(){
		$aResult = array();
		$sSql = "SELECT * FROM `ccb_deposit_record` WHERE `status` = 0";
		$aResult = $this->oDB->getAll($sSql);
		$iError = 0;
		if (!empty($aResult)){
			foreach ($aResult as $k => $v){
				// 开始事务
				$this->oDB->doTransaction();
				$sUpdate = "UPDATE `ccb_deposit_record` SET `status` = 2 WHERE `id` = {$v['id']} AND `status` = 0";
				$this->oDB->query($sUpdate);
				if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
					$this->oDB->doRollback(); // 事务回滚
					$iError++;
					continue;
				}
				
                // 充值手续费
                $oPayPortInfo = new model_deposit_depositaccountinfo($v['payacc_id']);
                $oPayPortInfo->GetType = true;
                $oPayPortInfo->OptType = "load";
	        	$aCharge = $oPayPortInfo->paymentFee($v['money']);
                $iFee = $aCharge[1];
                if ($iFee < 0){
                    $this->oDB->doRollback(); // 事务回滚
					$iError++;
					continue;
                }
                
				// 写入一条异常记录
				$aError = array();
				$aError['request_time'] = $v['created'];
				$aError['request_card'] = $v['accept_card'];
				$aError['binding_card'] = $v['account'];
				$aError['account_name'] = $v['account_name'];
				$aError['request_amount'] = $v['money'];
				$aError['pay_fee'] = $iFee;
				$aError['record_id'] = $v['id'];
				$aError['user_id'] = $v['user_id'];
				$aError['user_name'] = $v['user_name'];
				$aError['topproxy_name'] = $v['topproxy_name'];
				$aError['error_type'] = 0;
				$aError['status'] = 1;
				$aError['created'] = date("Y-m-d H:i:s", time());
				$bResult = $this->insertErrorData( $aError );
				if ($bResult === false){
					$this->oDB->doRollback(); // 事务回滚
					$iError++;
					continue;
				}
				
				$this->oDB->doCommit(); // 事务提交
			}
			if ($iError > 0 ){
				return $iError;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
    
    
    
    
    
    
    /**
	 * 清理指定天数以前的充值异常记录,只清除处理过的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-12-12
	 * @package 	passport
	 *
	 * @return 		boolean
	 *
	 */
	public function cleanErrors(){
		// 数据检查
		if (!is_numeric($this->Days) || $this->Days <= 0 || empty($this->Status)){
			return false;
		}
		
		$sTime = strtotime(date("Y-m-d", time())) - $this->Days * 24 * 3600;
		$sDate = date("Y-m-d H:i:s", $sTime);
		
		$sSql = "DELETE FROM `ccb_deposit_error` WHERE `status` IN ($this->Status) AND `created` <= '{$sDate}' AND `status` != 1";
		$this->oDB->query($sSql);
		return $this->oDB->errno() > 0 ? false : true;
	}
    
    
    
    
    
    /**
	 * 清理指定天数之前的建行充值记录,只清理处理过的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-12-12
	 * @package 	passport
	 *
	 * @return 		boolean
	 *
	 */
	public function cleanRecords(){
		// 数据检查
		if (!is_numeric($this->Days) || $this->Days <= 0 || empty($this->Status)){
			return false;
		}
		
		$sTime = strtotime(date("Y-m-d", time())) - $this->Days * 24 * 3600;
		$sDate = date("Y-m-d H:i:s", $sTime);
		
		$sSql = "DELETE FROM `ccb_deposit_record` WHERE `status` IN ($this->Status) AND `created` <= '{$sDate}' AND `status` != 0 AND `status` != 2";
		$this->oDB->query($sSql);
		return $this->oDB->errno() > 0 ? false : true;
	}
    
    
    
    
    /**
     * 修改建行虚拟贡列表上一次抓取到的页码
     *
     * @author      louis
     * @version     v1.0
     * @since       2010-12-12
     * @package     passport
     *
     * @return      boolean
     *
     */
    public function updatePage(){
        // 数据检查
        if (intval($this->BankId) <= 0){
            return false;
        }
        $sSql = "UPDATE `vmtables` SET `last_page` = 1 WHERE `bank_id` = {$this->BankId}";
        $this->oDB->query($sSql);
        return $this->oDB->errno() > 0 ? false : true;
    }
}