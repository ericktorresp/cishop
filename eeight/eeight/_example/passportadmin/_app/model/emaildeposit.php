<?php

/**
 * 文件：/passportadmin/_app/model/emaildeposit.php
 * 功能：email充值报表查询类
 *
 * --getList					获取email充值记录表中的记录
 * --errorDeal					充值异常处理
 * --getLastRecord				获取指定管理员手工录入记录
 * --adminInsert				手工录入查款记录
 * --getErrorList				获取异常列表
 * --getOneById					通过id查询充值异常表中的记录
 * --hiddenKey                  只显示附言的最后两位，其它用*代替
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
 * over_time			datetime				充值过期时间
 * deal_time			datetime				管理员处理时间
 * modified				timestamp				最后修改时间
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-09-06
 * @package 	passportadmin
 * 
 */
define("KEY_LEAVE_LENGTH", 2);          // 显示的位数
class model_emaildeposit extends basemodel{
	
	/**
	 * 记录id
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
	 * 所属总代名称
	 *
	 * @var string
	 */
	public $TopProxy;
	
	/**
	 * 查询金额最大值
	 *
	 * @var float
	 */
	public $MinMoney;
	
	/**
	 * 查询金额最小值
	 *
	 * @var float
	 */
	public $MaxMoney;
	
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
	 * 银行账号
	 *
	 * @var string
	 */
	public $Account;
	
	/**
	 * 开户名称
	 *
	 * @var string
	 */
	public $AccountName;
	
	/**
	 * 状态，０为未处理，１为成功，２为挂起，３为用户取消
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
	 * 操作管理员名称
	 *
	 * @var string
	 */
	public $AdminName;
	
	/**
	 * 未自动充值原因  １为附言违规，２为时间违规，３为账号违规，４为金额违规
	 *
	 * @var int
	 */
	public $ErrorType;
	
	/**
	 * 查询充值开始时间
	 *
	 * @var datetime
	 */
	public $StartTime;
	
	/**
	 * 查询充值结束时间
	 *
	 * @var datetime
	 */
	public $EndTime;
	
	/**
	 * 查询过期时间（开始）
	 *
	 * @var datetime
	 */
	public $StartExpire;
	
	/**
	 * 查询过期时间（结束）
	 *
	 * @var datetime
	 */
	public $EndExpire;
	
	/**
	 * 管理员处理时间（开始）
	 *
	 * @var datetime
	 */
	public $SDealTime;
	
	/**
	 * 管理员处理时间（结束）
	 *
	 * @var datetime
	 */
	public $EDealTime;
	
	/**
	 * 页数
	 *
	 * @var int
	 */
	public $Pages;
	
	/**
	 * 记录条数
	 *
	 * @var int
	 */
	public $TotalCount = 0;
	
	/**
	 * 每页条数
	 *
	 * @var int
	 */
	public $PageSize;
	
	/**
	 * 收款卡账号
	 *
	 * @var string
	 */
	public $AcceptNum;
	
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
	 * 手工录入附言
	 *
	 * @var string
	 */
	public $Notes;
	
	/**
	 * 人工充值手续费
	 *
	 * @var int
	 */
	public $Fee;
	
	
	
	
	
	/**
	 * 获取email充值记录表中的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-06
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
				/*if (!empty($aChileren)){
					$sChildrenId = "";
					foreach ($aChileren as $k => $v){
						$sChildrenId .= $v['userid'] . ',';
					}
				}*/
			}
		}
		if (!empty($sChildrenId)){ // 组合查询条件
			$sChildrenId = substr($sChildrenId, 0, -1);
			$sWhere .= " AND `user_id` IN ($sChildrenId)";
		}
		
		if (is_numeric($this->Id) && $this->Id > 0)						$sWhere .= " AND edr.`id` = {$this->Id}";
		if (is_numeric($this->UserId) && $this->UserId > 0)				$sWhere .= " AND edr.`user_id` = {$this->UserId}";
		if (!empty($this->UserName))									$sWhere .= " AND edr.`user_name` LIKE '%" . $this->UserName . "%'";
		if (is_numeric($this->MinMoney) && $this->MinMoney > 0 && !isset($this->MaxMoney))  $sWhere .= " AND edr.`money` >= {$this->MinMoney}";
		if (is_numeric($this->MaxMoney) && $this->MaxMoney > 0 && !isset($this->MinMoney))  $sWhere .= " AND edr.`money` <= {$this->MaxMoney}";
		if (is_numeric($this->MinMoney) && $this->MinMoney > 0 && is_numeric($this->MaxMoney) && $this->MaxMoney > 0) 	$sWhere .= " `money` BETWEEN {$this->MinMoney} AND {$this->MaxMoney}";
		if (!empty($this->Key))											$sWhere .= " AND edr.`key` = '{$this->Key}'";
		if (is_numeric($this->AccountId) && $this->AccountId > 0)		$sWhere .= " AND edr.`account_id` = {$this->AccountId}";
		if (!empty($this->Account))										$sWhere .= " AND edr.`account` = '{$this->Account}'";
		if (!empty($this->AccountName))									$sWhere .= " AND edr.`account_name` = '{$this->AccountName}'";
		if (is_numeric($this->Status))									$sWhere .= " AND edr.`status` IN ({$this->Status})";
		if (is_numeric($this->AdminId) && $this->AdminId > 0)			$sWhere .= " AND edr.`admin_id` = {$this->AdminId}";
		if (!empty($this->AdminName))									$sWhere .= " AND edr.`admin_name` = '{$this->AdminName}'";
		if (is_numeric($this->ErrorType) && $this->ErrorType > 0)		$sWhere .= " AND edr.`error_type` = $this->ErrorType";
		if (!empty($this->StartTime) && empty($this->EndTime))			$sWhere .= " AND edr.`created` >= '{$this->StartTime}'";
		if (empty($this->StartTime) && !empty($this->EndTime))			$sWhere .= " AND edr.`created` <= '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime))			$sWhere .= " AND edr.`created` BETWEEN '{$this->StartTime}' AND '{$this->EndTime}'";
		if (!empty($this->StartExpire) && empty($this->EndExpire))		$sWhere .= " AND edr.`over_time` >= '{$this->StartExpire}' AND it.`pay_date` >= '{$this->StartExpire}'";
		if (empty($this->StartExpire) && !empty($this->EndExpire))		$sWhere .= " AND edr.`over_time` <= '{$this->EndExpire}' AND it.`pay_date` <= '{$this->EndExpire}'";
		if (!empty($this->StartExpire) && !empty($this->EndExpire))		$sWhere .= " AND edr.`over_time` BETWEEN '{$this->StartExpire}' AND '{$this->EndExpire}' AND it.`pay_date` BETWEEN '{$this->StartExpire}' AND '{$this->EndExpire}'";
		if (!empty($this->SDealTime) && empty($this->EDealTime))		$sWhere .= " AND edr.`deal_time` >= '{$this->SDealTime}'";
		if (empty($this->SDealTime) && !empty($this->EDealTime))		$sWhere .= " AND edr.`deal_time` <= '{$this->EDealTime}'";
		if (!empty($this->SDealTime) && !empty($this->EDealTime))		$sWhere .= " AND edr.`deal_time` BETWEEN '{$this->SDealTime}' AND '{$this->EDealTime}'";
		
//		$sCount = "SELECT COUNT(`id`) AS num FROM `email_deposit_record` AS edr WHERE 1 " . $sWhere;
		$sCount = "SELECT count(edr.`id`) as num"
			. " FROM `email_deposit_record` AS edr LEFT JOIN `icbc_transfers` AS it"
			. " ON (edr.`transfer_id` = it.`transfer_id`)"
			. " WHERE 1 " . $sWhere . " ORDER BY `created` DESC";
		$aCount = $this->oDB->getOne($sCount);
		$this->TotalCount = $aCount['num'];
		
		$p = 0;
		$sLimit = "";
		$p    = (is_numeric($this->Pages) && $this->Pages>0) ? intval($this->Pages) : 1; // 默认第一页
		if ( $this->PageSize > 0 ){
			$sLimit = " LIMIT " . ($p - 1) * $this->PageSize . ',' . $this->PageSize ;
		}
		$sSql = "SELECT edr.*,it.`transfer_id`,it.`amount`,it.`pay_date`,it.`accept_card_num`,it.`notes`,it.`fee`"
			. " FROM `email_deposit_record` AS edr LEFT JOIN `icbc_transfers` AS it"
			. " ON (edr.`transfer_id` = it.`transfer_id`)"
			. " WHERE 1 " . $sWhere . " ORDER BY `created` DESC " . $sLimit;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	
	
	/**
	 * 充值异常处理
	 *
	 * @param 		array		$aLoadId			// 记录id数组
	 * @param 		array		$aRemark			// 批注信息数组
	 * @param 		int			$iStatus			// 状态,4为已处理，5为没收
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-09
	 * @package 	passportadmin
	 * 
	 * @return 		mix			-1					// 数据检查未通过
	 * 
	 */
	public function errorDeal( $aLoadId, $aRemark, $iStatus, $iAdminId, $sAdminName ){
		// 数据检查
		if (empty($aLoadId) || empty($aRemark) || !is_numeric($iStatus) || ($iStatus !== 4 && $iStatus !== 5)){
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
				// 根据key去查询充值记录表中的id，将充值记录表中的对应数据修改成已对应状态
				// step 01 首先通过记录查询key
				$this->Id = $val;
				$aResult = $this->getOneById();
				if (empty($aResult)){
					$sError .= $val . ',';
					continue;
				}
				
				// step 02 根据查询得到的key去查找充值记录表中对应记录的id
				if ($aResult['user_id'] > 0){
					$aDeposit = array();
					$oEmailDeposit = new model_deposit_emaildeposit();
					$oEmailDeposit->Key = $aResult['request_key'];
					$aDeposit = $oEmailDeposit->getRecord();
					
					if (count($aDeposit) !== 1){
						$sError .= $val . ',';
						continue;
					}
				}
				
				if ($aResult['user_id'] > 0){
					// 开始事务
					$this->oDB->doTransaction();
				}
				
				if ($aResult['user_id'] > 0){
					$sSql = "UPDATE `email_deposit_record` SET `admin_id` = {$iAdminId},`admin_name` = '{$sAdminName}',`status` = {$iStatus},`remark` = '{$aRemark[$val]}',`deal_time` = '" . date("Y-m-d H:i:s", time()) . "' WHERE `id` = {$aDeposit[0]['id']} AND `status` = 2";
					$this->oDB->query($sSql);
					if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
						$sError .= $val . ',';
						$this->oDB->doRollback(); // 事务回滚
						continue;
					}
				}
				
				$iTmepStatus = 0;
				if (intval($iStatus) === 4){
					$iTmepStatus = 3;
				} elseif (intval($iStatus) === 5){
					$iTmepStatus = 2;
				}
				$sSql2 = "UPDATE `email_deposit_error` SET `admin_id` = {$iAdminId},`admin_name` = '{$sAdminName}',`status` = {$iTmepStatus},`remark` = '{$aRemark[$val]}',`deal_time` = '" . date("Y-m-d H:i:s", time()) . "' WHERE `id` = {$val} AND `status` = 1";
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
	 * 获取指定管理员手工录入记录
	 * 
	 * @param 		int			$iAdminId		// 管理员id
	 * @param 		string		$ASC			// 排序方式，默认为倒序
	 * @param 		int			$iNum			// 查询记录条数
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-09
	 * @package 	passportadmin
	 * 
	 * @return 		array				
	 *
	 */
	public function getLastRecord( $iAdminId, $ASC = 'desc', $iNum = 10){
		$aResult = array();
		if (!is_numeric($iAdminId) || $iAdminId <= 0){
			return $aResult;
		}
		$sSql = "SELECT * FROM `icbc_transfers` WHERE `admin_id` = {$iAdminId} ORDER BY `transfer_id` " . $ASC . " LIMIT " . $iNum;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	
	/**
	 * 手工录入查款记录
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-09
	 * @package 	passportadmin
	 * 
	 * @return 		int					0		// 数据检查错误或写入失败
	 * 									$iLastId	// 成功记录id
	 *
	 */
	public function adminInsert(){
		// 数据检查
		if (empty($this->AcceptNum) || empty($this->PayDate) || floatval($this->Amount) <= 0 || intval($this->AdminId) <= 0 || empty($this->AccountName)){
			return false;
		}
		
		$aData = array();
		$aData['name'] = $this->AccountName;
		$aData['card_num'] = isset($this->Account) ? $this->Account : 0;
		$aData['area'] = "";
		$aData['amount'] = floatval($this->Amount);
		$aData['fee'] = floatval($this->Fee);
		$aData['notes'] = trim($this->Notes);
		$aData['accept_name'] = "";
		$aData['accept_card_num'] = $this->AcceptNum;
		$aData['accept_area'] = "";
		$aData['pay_date'] = $this->PayDate;
		$aData['admin_id'] = $this->AdminId;
		$aData['status'] = 0;
		$aData['date'] = date("Y-m-d H:i:s", time());
		
        $sSql = "INSERT IGNORE INTO `icbc_transfers` (`name`,`card_num`,`area`,`amount`,`fee`,`notes`,`accept_name`,`accept_card_num`,`accept_area`,`pay_date`,`admin_id`,`status`,`date`) VALUES('{$aData['name']}','{$aData['card_num']}','{$aData['area']}',{$aData['amount']}, {$aData['fee']}, '{$aData['notes']}', '{$aData['accept_name']}', '{$aData['accept_card_num']}', '{$aData['accept_area']}','{$aData['pay_date']}', {$aData['admin_id']}, {$aData['status']}, '{$aData['date']}')";
        $mResult = $this->oDB->query($sSql);
        $iLastId = $this->oDB->insertId();
        
        return $iLastId > 0 ? $iLastId : 0;
	}
	
	
	
	
	
	/**
	 * 获取工行充值异常列表
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
				/*if (!empty($aChileren)){
					$sChildrenId = "";
					foreach ($aChileren as $k => $v){
						$sChildrenId .= $v['userid'] . ',';
					}
				}*/
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
			. " FROM `email_deposit_error`"
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
			. " FROM `email_deposit_error`"
			. " WHERE 1 " . $sWhere . " ORDER BY `created` DESC " . $sLimit;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	
	
	
	
	/**
	 * 通过id查询充值异常表中的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-23
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
		
		$sSql = "SELECT * FROM `email_deposit_error` WHERE `id` = {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
    
    
    
    /**
	 * 只显示附言的最后两位，其它用*代替
	 *
	 * @param string $key                   // 附言
	 * @return string						// 隐藏后的附言
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2011-02-21
	 * @package 	passportadmin
	 * 
	 */
    public function hiddenKey( $key){
    	$sLast = mb_substr($key, 0 - KEY_LEAVE_LENGTH, KEY_LEAVE_LENGTH, 'utf8');
    	return str_repeat("*", strlen($key) - KEY_LEAVE_LENGTH) . $sLast;
    }
}