<?php
/**
 * 文件：/passportadmin/_app/model/ccbdeposit.php
 * 功能：建行充值报表查询类
 *
 * --CCBAdminInsert				建行人工录入
 * --getKey						生成验证串 验证串由日期，汇款金额，账户余额，汇款卡号（隐）,汇款人姓名组成
 * --isExist					查询建行抓取信息是否已经存在
 * --getList					获取建行充值记录表中的记录
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-11-19
 * @package 	passportadmin
 * 
 */

class model_ccbdeposit extends basemodel{
	
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
	public $TopProxy;
	
	/**
	 * 查询汇款金额起始值
	 *
	 * @var float
	 */
	public $MinMoney;
	
	/**
	 * 查询汇款金额结束值
	 *
	 * @var float
	 */
	public $MaxMoney;
	
	/**
	 * 汇款时间
	 *
	 * @var datetime
	 */
	public $PayDate;
	
	/**
	 * 汇款金额
	 *
	 * @var float
	 */
	public $Amount;
	
	/**
	 * 用户银行卡id
	 *
	 * @var int
	 */
	public $AccountId;
	
	/**
	 * 用户银行账号
	 *
	 * @var unknown_type
	 */
	public $Account;
	
	/**
	 * 开户名称
	 *
	 * @var string
	 */
	public $AccountName;
	
	/**
	 * 记录状态
	 *
	 * @var int
	 */
	public $Status;
	
	/**
	 * 操作管理员id
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 管理员用户名
	 *
	 * @var string
	 */
	public $AdminName;
	
	/**
	 * 错误类型
	 *
	 * @var int
	 */
	public $ErrorType;
	
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
	 * 汇款卡地址
	 *
	 * @var string
	 */
	public $Area;
	
	/**
	 * 汇款摘要
	 *
	 * @var string
	 */
	public $Notes;
	
	/**
	 * 验证串
	 *
	 * @var string
	 */
	public $Key;
	
	/**
	 * 账户余额
	 *
	 * @var float
	 */
	public $Balance;
	
	/**
	 * 汇款卡号（隐）
	 *
	 * @var string
	 */
	public $HiddenAcc;
	
	/**
	 * 币种
	 *
	 * @var string
	 */
	public $Currency;
	
	/**
	 * 查询管理员处理时间（开始）
	 *
	 * @var datetime
	 */
	public $SDealTime;
	
	/**
	 * 查询管理员处理时间（结束）
	 *
	 * @var datetime
	 */
	public $EDealTime;
	
	/**
	 * 充值手续费
	 *
	 * @var float
	 */
	public $Fee;
    
    /**
     * 收款账号
     * 
     * @var string
     */
    public $AcceptCard;
	
	/**
	 * 建行人工录入
	 *
	 * @author 		louis
	 * @version		v1.0
	 * @since 		2010-11-19
	 * @package 	passportadmin
	 * 
	 * @return 		mix				// 成功返回记录id,失败返回false
	 * 
	 */
	public function adminInsert(){
		// 数据检查
		if (empty($this->PayDate) || empty($this->Area) || floatval($this->Amount) <= 0 || floatval($this->Balance) <= 0 ||
			empty($this->HiddenAcc) || empty($this->AccountName) || empty($this->Currency) || empty($this->Notes) || 
			floatval($this->Fee) < 0){
			return false;	
		}
		
		// 通过卡号查询接口信息
		$oModelDeposit = new model_deposit_depositaccountinfo($this->Account, "banknumber");
        if ($oModelDeposit->AId > 0 && $oModelDeposit->PaySlotId > 0){
            $oModelDeposit->getAccountDataObj();
        }
		
		$aData = array();
		$aData['pay_date'] 		= $this->PayDate;
		$aData['area']		 	= $this->Area;
		$aData['amount']		= $this->Amount;
		$aData['balance']		= $this->Balance;
		$aData['fee']			= $this->Fee;
		$aData['hidden_account']= $this->HiddenAcc;
		$aData['acc_name']		= $this->AccountName;
		$aData['currency']		= $this->Currency;
		$aData['summary']		= $this->Notes;
		$aData['encode_key']	= $this->Key;
		$aData['nickname']		= isset($oModelDeposit->AccName) ? $oModelDeposit->AccName : "unknow account";
		$aData['accept_name']	= isset($oModelDeposit->AccIdent) ? $oModelDeposit->AccIdent : "unknow account";
		$aData['accept_card']	= $this->Account;
		$aData['admin_id']		= $_SESSION['admin'];
		$aData['admin_name']	= $_SESSION['adminname'];
		$aData['create']		= date("Y-m-d H:i:s", time());
		
		return $this->oDB->insert('ccb_transfers', $aData);
	}
	
	
	/**
	 * 生成验证串 验证串由日期，汇款金额，账户余额，汇款卡号（隐）,汇款人姓名组成
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-19
	 * @package 	passportadmin
	 * 
	 * @return 		string
	 * 
	 */
	public function getKey(){
        if (empty($this->PayDate) || empty($this->Amount) || empty($this->Balance) || empty($this->HiddenAcc) || empty($this->AccountName) || empty($this->AcceptCard)){
            return false;
        }
		return md5($this->PayDate . $this->Amount . $this->Balance . $this->HiddenAcc . $this->AccountName . $this->AcceptCard);
	}
	
	
	
	/**
	 * 查询建行抓取信息是否已经存在
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-19
	 * @package 	passportadmin
	 * 
	 * @return 		boolean			// 存在返回true, 不存在返回false
	 * 
	 */
	public function isExist(){
		// 数据检查
		if (empty($this->Notes))			return true;
		
		$aResult = array();
		$sSql = "SELECT * FROM `ccb_transfers` WHERE `encode_key` = '{$this->Key}'";
		$aResult = $this->oDB->getOne($sSql);
		return empty($aResult) ? false : true;
	}
	
	
	
	/**
	 * 获取建行充值记录表中的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-19
	 * @package 	passportadmin
	 * 
	 */
	public function getList(){
		$sWhere = "";
		$aResult = array();
		
		if (!empty($this->TopProxy)){ // 如果指定了总代，则查询总代的所有下级
			$iTopId = 0;
			$oUser = new model_user();
			// 首先通过总代名称获取总代id
			$iTopId = $oUser->getUseridByUsername($this->TopProxy);
			if ($iTopId > 0){
				$aChileren = array();
				// 获取总代下的所有下级用户
				$aChileren = $oUser->getChildrenId($iTopId, TRUE);
			}
		}
		if (!empty($sChildrenId)){ // 组合查询条件
			$sChildrenId = substr($sChildrenId, 0, -1);
			$sWhere .= " AND `user_id` IN ($sChildrenId)";
		}
		
		if (is_numeric($this->Id) && $this->Id > 0)						$sWhere .= " AND cdr.`id` = {$this->Id}";
		if (is_numeric($this->UserId) && $this->UserId > 0)				$sWhere .= " AND cdr.`user_id` = {$this->UserId}";
		if (!empty($this->UserName))									$sWhere .= " AND cdr.`user_name` LIKE '%" . $this->UserName . "%'";
		if (is_numeric($this->MinMoney) && $this->MinMoney > 0 && !isset($this->MaxMoney))  $sWhere .= " AND cdr.`money` >= {$this->MinMoney}";
		if (is_numeric($this->MaxMoney) && $this->MaxMoney > 0 && !isset($this->MinMoney))  $sWhere .= " AND edr.`money` <= {$this->MaxMoney}";
		if (is_numeric($this->MinMoney) && $this->MinMoney > 0 && is_numeric($this->MaxMoney) && $this->MaxMoney > 0) 	$sWhere .= " `money` BETWEEN {$this->MinMoney} AND {$this->MaxMoney}";
		if (is_numeric($this->AccountId) && $this->AccountId > 0)		$sWhere .= " AND cdr.`account_id` = {$this->AccountId}";
		if (!empty($this->Account))										$sWhere .= " AND cdr.`account` = '{$this->Account}'";
		if (!empty($this->AccountName))									$sWhere .= " AND cdr.`account_name` = '{$this->AccountName}'";
		if (is_numeric($this->Status))									$sWhere .= " AND cdr.`status` IN ({$this->Status})";
		if (is_numeric($this->AdminId) && $this->AdminId > 0)			$sWhere .= " AND cdr.`admin_id` = {$this->AdminId}";
		if (!empty($this->AdminName))									$sWhere .= " AND cdr.`admin_name` = '{$this->AdminName}'";
		if (is_numeric($this->ErrorType) && $this->ErrorType > 0)		$sWhere .= " AND cdr.`error_type` = $this->ErrorType";
		if (!empty($this->StartTime) && empty($this->EndTime))			$sWhere .= " AND cdr.`created` >= '{$this->StartTime}'";
		if (empty($this->StartTime) && !empty($this->EndTime))			$sWhere .= " AND cdr.`created` <= '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime))			$sWhere .= " AND cdr.`created` BETWEEN '{$this->StartTime}' AND '{$this->EndTime}'";
		
//		$sCount = "SELECT COUNT(`id`) AS num FROM `email_deposit_record` AS edr WHERE 1 " . $sWhere;
		$sCount = "SELECT count(cdr.`id`) as num"
			. " FROM `ccb_deposit_record` AS cdr LEFT JOIN `ccb_transfers` AS ct"
			. " ON (cdr.`transfer_id` = ct.`id`)"
			. " WHERE 1 " . $sWhere . " ORDER BY `created` DESC";
		$aCount = $this->oDB->getOne($sCount);
		$this->TotalCount = $aCount['num'];
		
		$p = 0;
		$sLimit = "";
		$p    = (is_numeric($this->Pages) && $this->Pages>0) ? intval($this->Pages) : 1; // 默认第一页
		if ( $this->PageSize > 0 ){
			$sLimit = " LIMIT " . ($p - 1) * $this->PageSize . ',' . $this->PageSize ;
		}
		$sSql = "SELECT cdr.*,ct.`id` as `transfer_id`,ct.`amount`,ct.`pay_date`,ct.`accept_card` as bank_accept_card,ct.`create`,ct.`balance`,ct.`full_account`, ct.`hidden_account`,ct.`acc_name`"
			. " FROM `ccb_deposit_record` AS cdr LEFT JOIN `ccb_transfers` AS ct"
			. " ON (cdr.`transfer_id` = ct.`id`)"
			. " WHERE 1 " . $sWhere . " ORDER BY `created` DESC " . $sLimit;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	/**
	 * 获取建行充值异常列表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-23
	 * @package 	passportadmin
	 * 
	 * @return 		array
	 * 
	 */
	public function getErrorList(){
		$sWhere = "";
		$aResult = array();
		
		if (!empty($this->TopProxy)){ // 如果指定了总代，则查询总代的所有下级
			$iTopId = 0;
			$oUser = new model_user();
			// 首先通过总代名称获取总代id
			$iTopId = $oUser->getUseridByUsername($this->TopProxy);
			if ($iTopId > 0){
				$aChileren = array();
				// 获取总代下的所有下级用户
				$aChileren = $oUser->getChildrenId($iTopId, TRUE);
			}
		}
		if (!empty($sChildrenId)){ // 组合查询条件
			$sChildrenId = substr($sChildrenId, 0, -1);
			$sWhere .= " AND `user_id` IN ($sChildrenId)";
		}
		
		
		if (is_numeric($this->Id) && $this->Id > 0)						$sWhere .= " AND `id` = {$this->Id}";
		if (is_numeric($this->UserId) && $this->UserId > 0)				$sWhere .= " AND `user_id` = {$this->UserId}";
		if (!empty($this->UserName))									$sWhere .= " AND `user_name` LIKE '%" . $this->UserName . "%'";
		if (is_numeric($this->Status))									$sWhere .= " AND `status` IN ({$this->Status})";
		if (is_numeric($this->AdminId) && $this->AdminId > 0)			$sWhere .= " AND `admin_id` = {$this->AdminId}";
		if (!empty($this->AdminName))									$sWhere .= " AND `admin_name` = '{$this->AdminName}'";
		if (is_numeric($this->ErrorType) && $this->ErrorType > 0)		$sWhere .= " AND `error_type` = $this->ErrorType";
		if (!empty($this->StartTime) && empty($this->EndTime))			$sWhere .= " AND `created` >= '{$this->StartTime}'";
		if (empty($this->StartTime) && !empty($this->EndTime))			$sWhere .= " AND `created` <= '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime))			$sWhere .= " AND `created` BETWEEN '{$this->StartTime}' AND '{$this->EndTime}'";
		if (!empty($this->SDealTime) && empty($this->EDealTime))		$sWhere .= " AND `deal_time` >= '{$this->SDealTime}'";
		if (empty($this->SDealTime) && !empty($this->EDealTime))		$sWhere .= " AND `deal_time` <= '{$this->EDealTime}'";
		if (!empty($this->SDealTime) && !empty($this->EDealTime))		$sWhere .= " AND `deal_time` BETWEEN '{$this->SDealTime}' AND '{$this->EDealTime}'";
		
		
		$sCount = "SELECT count(`id`) as num"
			. " FROM `ccb_deposit_error`"
			. " WHERE 1 " . $sWhere . " ORDER BY `created` DESC";
		$aCount = $this->oDB->getOne($sCount);
		$this->TotalCount = $aCount['num'];
		
		$p = 0;
		$sLimit = "";
		$p    = (is_numeric($this->Pages) && $this->Pages>0) ? intval($this->Pages) : 1; // 默认第一页
		if ( $this->PageSize > 0 ){
			$sLimit = " LIMIT " . ($p - 1) * $this->PageSize . ',' . $this->PageSize ;
		}
		$sSql = "SELECT * "
			. " FROM `ccb_deposit_error`"
			. " WHERE 1 " . $sWhere . " ORDER BY `created` DESC " . $sLimit;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	/**
	 * 充值异常处理
	 *
	 * @param 		array		$aLoadId			// 记录id数组
	 * @param 		array		$aRemark			// 批注信息数组
	 * @param 		int			$iStatus			// 状态,2为已处理，3为没收,4为已退还
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passportadmin
	 * 
	 * @return 		mix			-1					// 数据检查未通过
	 * 
	 */
	public function errorDeal( $aLoadId, $aRemark, $iStatus, $iAdminId, $sAdminName ){
		// 数据检查
		if (empty($aLoadId) || empty($aRemark) || !is_numeric($iStatus) || ($iStatus !== 2 && $iStatus !== 3 && $iStatus !== 4)){
			return -1;
		}
		if (!is_numeric($iAdminId) || $iAdminId <= 0 || empty($sAdminName)){
			return -1;
		}
		$sError = "";
		foreach ($aLoadId as $val) {
			if ($aRemark[$val] == ""){
				$sError .= $val . ',';
			} else {
				// 根据record_id将充值记录表中的对应数据修改成已对应状态
				// step 01 首先通过记录查询record
				$this->Id = $val;
				$aResult = $this->getOneById();
				if (empty($aResult)){
					$sError .= $val . ',';
					continue;
				}
				
				
				// step 02 根据查询得到的key去查找充值记录表中对应记录的id
				if ($aResult['record_id'] > 0){
					$aDeposit = array();
					$oCCBDeposit = new model_deposit_ccbdeposit();
					$oCCBDeposit->Id = $aResult['record_id'];
					$aDeposit = $oCCBDeposit->getOneById();
					
					if (empty($aDeposit)){
						$sError .= $val . ',';
						continue;
					}
				}
				
				
				if ($aResult['user_id'] > 0){
					// 开始事务
					$this->oDB->doTransaction();
				}
				
				$iTmepStatus = 0;
				if (intval($iStatus) === 2){ // 没收
					$iTmepStatus = 5;
				} elseif (intval($iStatus) === 3){ // 已处理
					$iTmepStatus = 4;
				}elseif (intval($iStatus) === 4){ // 已退还
					$iTmepStatus = 3;
				}
				
				if ($aResult['user_id'] > 0){
					$sSql = "UPDATE `ccb_deposit_record` SET `admin_id` = {$iAdminId},`admin_name` = '{$sAdminName}',`status` = {$iTmepStatus},`remark` = '{$aRemark[$val]}',`deal_time` = '" . date("Y-m-d H:i:s", time()) . "' WHERE `id` = {$aDeposit['id']} AND `status` = 2";
					$this->oDB->query($sSql);
					if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
						$sError .= $val . ',';
						$this->oDB->doRollback(); // 事务回滚
						continue;
					}
				}
				
				
				$sSql2 = "UPDATE `ccb_deposit_error` SET `admin_id` = {$iAdminId},`admin_name` = '{$sAdminName}',`status` = {$iStatus},`remark` = '{$aRemark[$val]}',`deal_time` = '" . date("Y-m-d H:i:s", time()) . "' WHERE `id` = {$val} AND `status` = 1";
				$this->oDB->query($sSql2);
				if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
					$sError .= $val . ',';
					if ($aResult['user_id'] > 0){
						$this->oDB->doRollback(); // 事务回滚
					}
					continue;
				}
				
				if ($aResult['user_id'] > 0){
					$this->oDB->doCommit(); // 事务提交
				}
			}
		}
		return empty($sError) ? true : $sError;
	}
	
	
	/**
	 * 通过id查询充值异常表中的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passportamin
	 * 
	 * @return 		array
	 * 
	 */
	public function getOneById(){
		// 数据检查
		$aResult = array();
		if (!is_numeric($this->Id) || $this->Id <= 0){
			return $aResult;
		}
		
		$sSql = "SELECT * FROM `ccb_deposit_error` WHERE `id` = {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
}