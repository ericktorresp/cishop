<?php

/**
 * 路径:/_app/model/emaildeposit.php
 * 功能：email充值的相关操作
 * 
 * 方法：
 * --getRecord				获取用户充值未处理的记录信息
 * --getKey					通过网银获取的附言中得到充值唯一key
 * --updateStatus			修改充值记录的状态
 * --insertRecord			写入充值申请记录
 * --getAllById				获取指定用户的指定状态充值记录
 * --getOneById				通过id查询记录信息
 * --realLoad				充值操作
 * --getBankRecord			获取银行抓取记录信息
 * --updateBankStatus		修改银行抓取记录状态
 * --unionUpdate			联合修改银行抓取记录表和充值记录表的状态
 * --getOneBankRecord		获取银行抓取信息
 * --updateAllProcess		修改所有的待处理的记录为挂起单并写入一条异常记录(供计划任务使用）
 * --insertErrorData		写入一条异常记录
 * --updateStatusAndInsert	修改平台充值记录状态并写入一条异常记录
 * --updateBankAndInsert	修改银行抓取记录状态并写入一条异常记录
 * --cleanRecords			清理指定天数之前的email充值记录,只清理处理过的记录
 * --createParam			组成写入异常充值列表的数据
 * --getWrondStyle			获取违规类型，如果未出现违规，则返回0
 * --securityCheck          检查当前用户是否是第一次发起充值请求，或者是重新登录，或更换了ip地址
 * 
 * 
 * email_deposit_record表结构
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * id					int						
 * user_id				int						用户id
 * user_name			varchar(50)				用户名
 * topproxy_name		varchar(50)				总代用户名
 * money				decimal(14,4)			充值金额
 * key					varchar(50)				充值唯一key
 * account_id			int(10)					公司分账号id
 * account				varchar(50)				公司账号
 * account_name			varchar(50)				公司账户名
 * transfer_id			int(10)					银行信息记录id
 * status				tinyint(1)				状态，０为未处理，１为成功，２为挂起(手工处理)
 * admin_id				int						操作管理员id
 * admin_name			varchar(50)				操作管理员名
 * error_type			tinyint					违规类型，１为附言违规，２为时间违规，３为账号违规，４为金额违规
 * remark				text					处理备注
 * created				datetime				添加时间
 * overtime				datetime				充值过期时间
 * deal_time			datetime				管理员处理时间
 * modified				timestamp				最后修改时间
 * add_money_time		datetime				加游戏币时间
 * 
 * 
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-09-02
 * @package 	passport
 *
 */

class model_deposit_emaildeposit extends basemodel{
	
	/**
	 * id
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 用户id
	 *
	 * @var int
	 */
	public $UserId;
	
	/**
	 * 用户名
	 *
	 * @var string
	 */
	public $UserName;
	
	/**
	 * 总代用户名
	 *
	 * @var string
	 */
	public $TopProxyName;
	
	/**
	 * 充值金额
	 *
	 * @var float
	 */
	public $Money;
	
	/**
	 * 充值唯一key
	 *
	 * @var string
	 */
	public $Key;
	
	/**
	 * 分账户id
	 *
	 * @var int
	 */
	public $AccountId;
	
	/**
	 * 公司账号
	 *
	 * @var string
	 */
	public $Account;
	
	/**
	 * 公司账户名
	 *
	 * @var string
	 */
	public $AccountName;
	
	/**
	 * 操作管理员id
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 操作管理员名称
	 *
	 * @var string
	 */
	public $AdminName;
	
	/**
	 * 记录添加时间
	 *
	 * @var datetime
	 */
	public $Created;
	
	/**
	 * 记录过期时间
	 *
	 * @var datetime
	 */
	public $OverTime;
	
	/**
	 * 记录最后修改时间
	 *
	 * @var datetime
	 */
	public $Modified;
	
	/**
	 * 充值附言
	 *
	 * @var string
	 */
	public $Note;
	
	/**
	 * 记录状态
	 *
	 * @var int			//０为未处理，１为成功，２为挂起，３为用户取消
	 */
	public $Status;
	
	/**
	 * 查询开始时间
	 *
	 * @var datetime
	 */
	public $StartTime;
	
	/**
	 * 查询结束时间
	 *
	 * @var datetime
	 */
	public $EndTime;
	
	/**
	 * 记录总数
	 *
	 * @var int
	 */
	public $Total;
	
	/**
	 * 银行抓取信息表记录的状态
	 *
	 * @var int
	 */
	public $BankStatus;
	
	/**
	 * 银行抓取信息记录id
	 *
	 * @var int
	 */
	public $BankRecordId;
	
	/**
	 * 修改充值记录状态时，是否写入银行抓取信息的id的开关,０为不写入，１为写入
	 *
	 * @var int 
	 */
	public $iSwitch = 0;
	
	/**
	 * 违规类型 １为附言违规，２为时间违规，３为账号违规，４为金额违规
	 *
	 * @var int
	 */
	public $ErrorType;
	
	/**
	 * 清理数据的天数
	 *
	 * @var int
	 */
	public $Days;
	
	/**
	 * 加游戏币时间
	 *
	 * @var boolean					// true为添加，false为不添加
	 */
	public $AddMoneyTime;
	
	
	
	/**
	 * 获取用户充值未处理的记录信息
	 * 
	 * @author 		louis
	 * @version 	v1.0	
	 * @since 		2010-09-02
	 * @return 		array		$aResult
	 * @package 	passport
	 * 
	 */
	public function getRecord() {
		$aResult = array();
		if (empty($this->Key))		return $aResult;
		$sSql = "SELECT * FROM `email_deposit_record` WHERE `key` = '{$this->Key}'";
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	/**
	 * 通过网银获取的附言中得到充值唯一key
	 * 
	 * @param 	string		$sNote		// 网银附言
	 * 
	 * @author 		louis
	 * @version 	v1.0	
	 * @since 		2010-09-02
	 * @package 	passport
	 * 
	 * @return 		$sKey				充值唯一key
	 * 
	 */
	public function getKey(){
		$sKey = "";
		$sNote = "";
		if ( empty($this->Note) )			return $sKey;
		$sNote = htmlspecialchars( $this->Note, ENT_QUOTES );
		$sNote = addslashes( $sNote );
		preg_match("/w(.{6})w/", $sNote, $matches);
		$sKey = !empty($matches[1]) ? strtolower($matches[1]) : "";
		return $sKey;
	}
	
	
	
	
	/**
	 * 修改充值记录的状态
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-02
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
		
        $sSql = "UPDATE `email_deposit_record` SET `status` = {$this->Status}" . $sAnd . " WHERE `id` IN ({$this->Id}) AND `status` = 0";
        
        $this->oDB->query($sSql);
        if ($this->iSwitch === 1){
        	return ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0 ) ? false : true;
        }
        
        return $this->oDB->errno() > 0 ? false : true;
	}
	
	
	
	
	/**
	 * 写入充值申请记录
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-02
	 * @package 	passort
	 * 
	 * @return 		int				$iLastInsertId;
	 * 
	 */
	public function insertRecord(){
		// 数据检查
		if ( !is_numeric( $this->UserId ) || $this->UserId <= 0 || empty( $this->UserName ) || !is_float( $this->Money )
			|| $this->Money <= 0 || empty( $this->Account ) || empty( $this->AccountName ) || !is_numeric( $this->AccountId) 
			|| $this->AccountId <= 0 ){
				return FALSE;
			}
//		$this->Key = $this->_getKey();
		
		$oConfigd = new model_config();
		$aData = array(
			'user_id' 		=> $this->UserId,
			'user_name'		=> $this->UserName,
			'topproxy_name'	=> $this->TopProxyName,
			'money'			=> $this->Money,
			'key'			=> $this->Key,
			'account_id'	=> $this->AccountId,
			'account'		=> $this->Account,
			'account_name'	=> $this->AccountName,
			'remark'		=> "",
			'created'		=> date("Y-m-d H:i:s", time()),
			'over_time'		=> date("Y-m-d H:i:s", time() + $oConfigd->getConfigs('maildeposit_eachtime') * 60)
		);
		$iLastInsertId = $this->oDB->insert( 'email_deposit_record', $aData );
		return $iLastInsertId > 0 ? $iLastInsertId : FALSE;
	}
	
	
	
	/**
	 * 获取充值唯一key,如果与已有key重复，则重新获取一个
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-02
	 * @package 	passport
	 * 
	 * @return 		string			$sKey
	 * 
	 */
	private function _getKey(){
//		$iRand = mt_rand( 1,10000 );
		$iRand = microtime(true);
		$sMD5 = md5( $iRand );
		$iRand = mt_rand( 0,26 );
		$sKey = substr( $sMD5, $iRand, 6 );
		$this->Key = $sKey;
		$aResult = $this->getRecord();
		if ( count($aResult) >= 1){
			return $this->_getKey();
		} else {
			return $sKey;
		}
	}
	
	
	
	
	
	/**
	 * 获取指定用户的指定状态充值记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-03
	 * @package 	passport
	 * 		
	 */
	public function getAllById( $p, $pn ){
		$aResult = array();
		$this->Status = isset($this->Status) ? $this->Status : 0;
		if ( !is_numeric($this->UserId) || $this->UserId <= 0 ){
			return $aResult;
		}
		$sWhere = "";
		if ( !empty( $this->StartTime ) && empty( $this->EndTime ) ){
			$sWhere .= " AND `created` >= '{$this->StartTime}'";
		}
		if ( empty( $this->StartTime ) && !empty( $this->EndTime ) ){
			$sWhere .= " AND `created` <= '{$this->EndTime}'";
		}
		if ( !empty( $this->StartTime ) && !empty( $this->EndTime ) ){
			$sWhere .= " AND `created` BETWEEN '{$this->StartTime}' AND '{$this->EndTime}'";
		}
		
		$sNumSql = "SELECT count(id) as num FROM `email_deposit_record` WHERE `user_id` = {$this->UserId} AND `status` IN ({$this->Status})" . $sWhere;
		$aCount = $this->oDB->getOne($sNumSql);
		$this->Total = $aCount['num'];
		
		$p    = (is_numeric($p) && $p>0) ? intval($p) : 1; // 默认第一页
		if ( $pn > 0 ){
			$sLimit = " LIMIT " . ($p - 1) * $pn . ',' . $pn ;
		}
		$sSql = "SELECT * FROM `email_deposit_record` WHERE `user_id` = {$this->UserId} AND `status` IN ({$this->Status})" . $sWhere . " ORDER BY `created` DESC " . $sLimit;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
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
		$aResult = array();
		// 数据检查
		if ( !is_numeric($this->Id) || $this->Id <= 0){
			return $aResult;
		}
		
		$sSql = "SELECT * FROM `email_deposit_record` WHERE `id` = {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
	
	
	
	
	
	/**
	 * 充值操作
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-03
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
	 * 查询用户支付中订单的数量
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-06
	 * @package 	passport
	 * 
	 * @return 		mix				-1					// 数据检查错误
	 * 								count($aResult)		// 数据个数
	 * 
	 */
	/*public function getcount(){
		// 数据检查
		if ( !is_numeric($this->UserId) || $this->UserId <= 0){
			return -1;
		}
		$sSql = "SELECT count(`id`) as num FROM `email_deposit_record` WHERE `user_id` = {$this->UserId} AND `status` = 0 AND `created` LIKE '" . date("Y-m-d", time()) . "%'";
		$aResult = $this->oDB->getOne( $sSql );
		return $aResult['num'];
	}*/
	
	
	
	
	/**
	 * 获取银行抓取记录信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-07
	 * @package 	passport
	 * 
	 * @return 		array			$aResult			// 记录集
	 * 
	 */
	public function getBankRecord(){
		$sWhere = "";
		if (is_numeric($this->BankStatus))				$sWhere .= " AND `status` = {$this->BankStatus}";
		$sSql = "SELECT * FROM `icbc_transfers` WHERE 1 " . $sWhere;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	
	
	/**
	 *　修改银行抓取记录状态
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-07
	 * @package 	passport
	 * 
	 * @return 		boolean			
	 * 
	 */
	public function updateBankStatus(){
		if (!is_numeric($this->BankStatus) || !is_numeric($this->BankRecordId) || $this->BankRecordId <= 0)			return false;
		$sSql = "UPDATE `icbc_transfers` SET `status` = {$this->BankStatus} WHERE `transfer_id` = {$this->BankRecordId} AND `status` = 0";
		$this->oDB->query($sSql);
		
		return ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0) ? false : true;
	}
	
	
	
	
	
	/**
	 * 联合修改银行抓取记录表和充值记录表的状态
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-07
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
		$sSql = "UPDATE `icbc_transfers` SET `status` = {$this->BankStatus} WHERE `transfer_id` = {$this->BankRecordId} AND `status` = 0";
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
	 * 获取银行抓取信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-09
	 * @package 	passport
	 * 
	 * @return 		array
	 * 
	 */
	public function getOneBankRecord(){
		$aResult = array();
		// 数据检查
		if (!is_numeric($this->BankRecordId) || $this->BankRecordId <= 0){
			return $aResultl;
		}
		$sSql = "SELECT * FROM `icbc_transfers` WHERE `transfer_id` = {$this->BankRecordId}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
    
    
    
    
    
    /**
	 * 通过附言获取银行抓取信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2011-01-18
	 * @package 	passport
	 * 
	 * @return 		array
	 * 
	 */
	public function getBankRecordByKey(){
		$aResult = array();
		// 数据检查
		if (!is_numeric($this->BankRecordId) || $this->BankRecordId <= 0 || empty($this->Key)){
			return $aResultl;
		}
		$sSql = "SELECT `pay_date` FROM `icbc_transfers` WHERE `notes` LIKE '%" . $this->Key . "%' AND `transfer_id` != '{$this->BankRecordId}'";
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	
	/**
	 * 修改所有的待处理的记录为挂起单并写入一条异常记录(供计划任务使用）
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-13
	 * @package 	passport
	 * 
	 * @return 		mix					true			// 执行成功
	 * 									$iError			// 失败的条数
	 * 
	 */
	public function updateAllProcess(){
		$aResult = array();
		$sSql = "SELECT * FROM `email_deposit_record` WHERE `status` = 0";
		$aResult = $this->oDB->getAll($sSql);
		$iError = 0;
		if (!empty($aResult)){
			foreach ($aResult as $k => $v){
				// 开始事务
				$this->oDB->doTransaction();
				$sUpdate = "UPDATE `email_deposit_record` SET `status` = 2 WHERE `id` = {$v['id']} AND `status` = 0";
				$this->oDB->query($sUpdate);
				if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
					$this->oDB->doRollback(); // 事务回滚
					$iError++;
					continue;
				}
				
				// 写入一条异常记录
				$aError = array();
				$aError['request_time'] = $v['created'];
				$aError['request_card'] = $v['account'];
				$aError['request_amount'] = $v['money'];
				$aError['request_key'] = $v['key'];
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
		
		return $this->oDB->insert('email_deposit_error', $aData);
	}
	
	
	
	
	
	
	
	/**
	 * 修改平台充值记录状态并写入一条异常记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-22
	 * @package 	passport
	 * 
	 * @return 		boolean
	 * 
	 */
	public function updateStatusAndInsert( $aData ){
		// 数据检查
		if (!is_array($aData) || empty($aData)){
			return false;
		}
		
		// 事务开始
		$this->oDB->doTransaction();

		$bStatus = $this->updateStatus();
		if ($bStatus === false){
			$this->oDB->doRollback(); // 事务回滚
			return false;
		}
		
		$bInsert = $this->insertErrorData( $aData );
		if ($bInsert === false){
			$this->oDB->doRollback(); // 事务回滚
			return false;
		}
		
		$this->oDB->doCommit(); // 事务提交
		return true;
	}
	
	
	
	
	
	
	
	/**
	 * 修改银行抓取记录状态并写入一条异常记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-22
	 * @package 	passport
	 * 
	 * @return 		boolean
	 * 
	 */
	public function updateBankAndInsert( $aData ){
		// 数据检查
		if (!is_array($aData) || empty($aData) || empty($this->Account)){
			return false;
		}
		
		$bAdd = true; // 是否更新接口余额,true为更新，false为不更新，默认为true
		
//		$oPayPortInfo = new model_pay_payaccountinfo($this->Account, 'banknumber');
        $oPayPortInfo = new model_deposit_depositaccountinfo($this->Account, 'banknumber');
		$oPayPortInfo->GetType = true;
		if (intval($oPayPortInfo->AId) > 0){
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

		$bStatus = $this->updateBankStatus();
		if ($bStatus === false){
			$this->oDB->doRollback(); // 事务回滚
			return false;
		}
		
		$bInsert = $this->insertErrorData( $aData );
		if ($bInsert === false){
			$this->oDB->doRollback(); // 事务回滚
			return false;
		}
		
		$this->oDB->doCommit(); // 事务提交
		return true;
	}
	
	
	
	
	
	/**
	 * 清理指定天数之前的email充值记录,只清理处理过的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-28
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
		
		$sSql = "DELETE FROM `email_deposit_record` WHERE `status` IN ($this->Status) AND `created` <= '{$sDate}' AND `status` != 0 AND `status` != 2";
		$this->oDB->query($sSql);
		return $this->oDB->errno() > 0 ? false : true;
	}
	
	
	
	
	
	
	
	/**
	 * 清理指定天数以前的充值异常记录,只清除处理过的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-28
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
		
		$sSql = "DELETE FROM `email_deposit_error` WHERE `status` IN ($this->Status) AND `created` <= '{$sDate}' AND `status` != 1";
		$this->oDB->query($sSql);
		return $this->oDB->errno() > 0 ? false : true;
	}
	
	
	
	
	/**
	 * 组成写入异常充值列表的数据
	 * 
	 * @param 		array		$value			// 抓取数据内容
	 * @param 		array		$content		// 平台数据内容
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
	public function createParam($value, $style, $error_type, $status, $content = array()){
		$aResult = array();
		if (!is_array($value) || empty($value) || intval($style) <= 0 || !is_numeric($error_type) || !is_numeric($status)){
			return false;
		}
		
		switch ($style){
			case 1:
				$aResult['pay_time'] = $value['pay_date'];
				$aResult['pay_card'] = $value['accept_card_num'];
				$aResult['pay_amount'] = $value['amount'];
				$aResult['pay_fee'] = $value['fee'];
				$aResult['pay_key'] = $value['notes'];
				$aResult['transfer_id'] = $value['transfer_id'];
				$aResult['error_type'] = $error_type;
				$aResult['status'] = $status;
				$aResult['created'] = date("Y-m-d H:i:s", time());
				break;
			case 2:
				$aResult['request_time'] = $content['created'];
				$aResult['request_card'] = $content['account'];
				$aResult['request_amount'] = $content['money'];
				$aResult['request_key'] = $content['key'];
				$aResult['pay_time'] = $value['pay_date'];
				$aResult['pay_card'] = $value['accept_card_num'];
				$aResult['pay_amount'] = $value['amount'];
				$aResult['pay_fee'] = $value['fee'];
				$aResult['pay_key'] = $value['notes'];
				$aResult['transfer_id'] = $value['transfer_id'];
				$aResult['user_id'] = $content['user_id'];
				$aResult['user_name'] = $content['user_name'];
				$aResult['topproxy_name'] = $content['topproxy_name'];
				$aResult['error_type'] = $error_type;
				$aResult['status'] = $status;
				$aResult['created'] = date("Y-m-d H:i:s", time());
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
	 * @return 		int				0为未违规，1为附言违规，2为时间违规，3为账号违规，4为金额违规
	 * 
	 */
	public function getWrondStyle($value, $id){
		//  数据检查
		if (empty($value['pay_date']) || empty($value['accept_card_num']) || floatval($value['amount']) <= 0){
			return false;
		}
		
		$this->Id = $id;
		$aResult = $this->getOneById();
		if (empty($aResult)){
			return false;
		}
		
		if ($value['pay_date']  > $aResult['over_time']){ // 时间违规
			return 2;
		} else if(trim( $value['accept_card_num'] ) !== trim( $aResult['account'] )) { // 账号违规
			return 3;
		} else if (floatval( $value['amount'] ) != floatval( $aResult['money'] )){ // 金额违规
			return 4;
		} else {
			return 0;
		}
	}
    
    
    
    /**
     * 检查当前用户是否是第一次发起充值请求，或者是重新登录，或更换了ip地址。
     * 
     * @author      louis
     * @since       2011-02-13
     * @package     passport
     * @version     v1.0
     * 
     * @return      boolean 
     * 
     */
    public function securityCheck(){
        $iUserId = $_SESSION['userid'];
        if (!isset($_SESSION[$iUserId . '_sion']) || !isset($_SESSION[$iUserId . '_ip'])){
            return false;
        } else { // 如果存在，则需要逐一检查
            $aResult = array();
            $oUserSion = new model_usersession();
            $aResult = $oUserSion->getOneSessionKey($iUserId);
            $aResult['sessionkey'] = isset($aResult['sessionkey']) ? $aResult['sessionkey'] : "";
            // sessionkey不符
            if ($_SESSION[$iUserId . '_sion'] != $aResult['sessionkey']){
                return false;
            }
            
            // 与上次登陆ip不符
            $oUser = new model_user();
            $aUser = $oUser->getUserInfo($iUserId);
            if ($_SESSION[$iUserId . '_ip'] != $aUser['lastip']){
                return false;
            }
        }
    }
}