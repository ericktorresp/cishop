<?php
/**
 * 支付接口接受的充值银行列表类
 *
 */
define("DEFAULT_PAGESIZE", 20);
class model_withdraw_PayBankList extends model_pay_base_List {
	
	/**
	 * 支付接口ID
	 *
	 * @var int
	 */
	public $ApiId;
	
	/**
	 * 支付接口名称
	 *
	 * @var string
	 */
	public $ApiName;
	
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
	 * 查询起始时间
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
	 * 状态
	 *
	 * @var int
	 */
	public $Status;
	
	
	/**
	 * 返回api充值的银行列表
	 *
	 * @version 	v1.0	2010-04-08
	 * @author 		louis
	 */
	public function __construct(){
		parent::__construct('pay_bank_list','pbl.id',DEFAULT_PAGESIZE,' as pbl');
	}
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-04-08
	 * @author 		louis
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*", 1, "seq,utime,bank_name", true);
	}
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 * 
	 * @return 		string
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if ($this->ApiId > 0)									$sSelectSql .= " AND api_id = '{$this->ApiId}'";
		if (!empty($this->ApiName))								$sSelectSql .= " AND api_name = '{$this->ApiName}'";
		if ($this->BankId > 0)									$sSelectSql .= " AND bank_id = '{$this->BankId}'";
		if (!empty($this->BankName))							$sSelectSql .= " AND bank_name = '{$this->BankName}'";
		if (!empty($this->BankCode))							$sSelectSql .= " AND bank_code = '{$this->BankCode}'";
		if (!empty($this->StartTime) && empty($this->EndTime))	$sSelectSql .= " AND atime > '{$this->StartTime}'";
		if (!empty($this->EndTime) && empty($this->StartTime))	$sSelectSql .= " AND atime < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime)) $sSelectSql .= " AND atime BETWEEN '{$this->StartTime}' AND 
																				'{$this->EndTime}'";
		if (isset($this->Status))								$sSelectSql .= " AND status in ({$this->Status})";
		return $sSelectSql;
	}
	
	
	
	/**
	 * 获取银行logo信息
	 *
	 * @version 	v1.0	2010-04-30
	 * @author 		louis
	 */
	public function getLogo(){
		if ($this->ApiId < 0) return false;
		if ($this->ApiId > 0) $sCondition = " AND pbl.api_id = {$this->ApiId} ORDER BY pbl.seq,pbl.utime,pbl.bank_name ASC";
		$sSql = "SELECT * FROM pay_bank_list as pbl,pay_bank as pb WHERE pbl.bank_id = pb.id AND pbl.status = 1" . $sCondition;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
}