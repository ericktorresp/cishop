<?php
/**
 * 在线提现明细列表类
 * 
 * @version 	v1.0	2010-03-08
 * @author 		louis
 * 
 */
define("DEFAULT_PAGESIZE", 100);
class model_withdraw_fodetailslist extends model_pay_base_List{
	
	/**
	 * 明细id
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 查询时最小明细编号
	 *
	 * @var int
	 */
	public $MinId;
	
	/**
	 * 查询时最大明细编号
	 *
	 * @var int
	 */
	public $MaxId;
	
	/**
	 * 明细编号
	 *
	 * @var string
	 */
	public $No;
	
	/**
	 * 在线提现方式id
	 *
	 * @var int
	 */
	public $ApiId;
	
	/**
	 * 分账户ID
	 *
	 * @var int
	 */
	public $AccId;
	
	/**
	 * 在线提现方式名称
	 *
	 * @var string
	 */
	public $ApiName;
	
	/**
	 * 分账户名称
	 *
	 * @var string
	 */
	public $AccName;
	
	/**
	 * 用户银行卡信息ID
	 *
	 * @var int
	 */
	public $UserBankId;
	
	/**
	 * 银行ID
	 *
	 * @var int
	 */
	public $BankId;
	
	/**
	 * 银行名称
	 *
	 * @var string
	 */
	public $BankName;
	
	/**
	 * 银行代码
	 *
	 * @var string
	 */
	public $BankCode;
	
	/**
	 * 币种
	 *
	 * @var string
	 */
	public $MoneyType;
	
	
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
	 * 操作管理员
	 *
	 * @var string
	 */
	public $Operater;
	
	/**
	 * 账变状态
	 *
	 * @var int
	 */
	public $Status;
	
	/**
	 * 非状态值  status != 1
	 *
	 * @var int
	 */
	public $UnStatus;
	
	/**
	 * 当前页数
	 *
	 * @var int
	 */
	public $Pages;
	
	/**
	 * 返回默认状态下在线提现明细列表信息或者组合查询条件后，调用getOrdersList()方法
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		array 	$aResult			// 查询结果集数组
	 */
	public function __construct(){
		parent::__construct('pay_out_details','pd.id',DEFAULT_PAGESIZE,'as pd');
	}

	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		array			// 结果集数组
	 */
	public function init(){
		$this->FindAll(" 1 " . $this->_createWhereSql() , "*", $this->Pages > 0 ? $this->Pages : 1, 'id', true);
	}
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		string	$sSql				// 复合查询条件的sql语句
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if (intval($this->MinId) > 0 && intval($this->MaxId) > 0)	$sSelectSql .= " AND id BETWEEN {$this->MinId} AND {$this->MaxId}";
		if (intval($this->MinId) > 0 && intval($this->MaxId) == 0)	$sSelectSql .= " AND id >= {$this->MinId}";
		if (intval($this->MinId) == 0 && intval($this->MaxId) > 0)	$sSelectSql .= " AND id <= {$this->MaxId}";
		if (!empty($this->No))										$sSelectSql .= " AND no = '{$this->No}'";
		if (intval($this->ApiId) > 0)   							$sSelectSql .= " AND api_id = {$this->ApiId}";
		if (intval($this->AccId) > 0)   							$sSelectSql .= " AND acc_id = {$this->AccId}";
		if (!empty($this->ApiName))									$sSelectSql .= " AND api_name = '{$this->ApiName}'";
		if (!empty($this->AccName))									$sSelectSql .= " AND acc_name = '{$this->AccName}'";
		if (intval($this->UserBankId) > 0)							$sSelectSql .= " AND userbank_id = {$this->UserBankId}";
		if (intval($this->BankId) > 0)								$sSelectSql .= " AND bank_id = {$this->BankId}";
		if (!empty($this->BankName))								$sSelectSql .= " AND bank_name like '%{$this->BankName}%'";
		if (!empty($this->BankCode))								$sSelectSql .= " AND bank_name  = '{$this->BankCode}'";
		if (!empty($this->MoneyType))								$sSelectSql .= " AND money_type = '{$this->MoneyType}'";
		if (intval($this->UserId) > 0)								$sSelectSql .= " AND user_id = {$this->UserId}";
		if (!empty($this->UserName))								$sSelectSql .= " AND user_name like '%{$this->UserName}%'";
		if (!empty($this->StartTime) && empty($this->EndTime))		$sSelectSql .= " AND request_time > '{$this->StartTime}'";
		if (!empty($this->EndTime) && empty($this->StartTime))		$sSelectSql .= " AND request_time < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime)) 	$sSelectSql .= " AND request_time BETWEEN '{$this->StartTime}' AND
																				'{$this->EndTime}'";
		if (!empty($this->Operater))								$sSelectSql	.= " AND operater = '{$this->Operater}'";
		if (!empty($this->Status))									$sSelectSql .= " AND status = {$this->Status}";
		if (!empty($this->UnStatus))								$sSelectSql .= " AND status != {$this->UnStatus}";
		return $sSelectSql;
	}
}