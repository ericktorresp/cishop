<?php

/**
 * 提现审核未通过原因列表类
 *
 * @version 	v1.0	2010-04-09
 * @author 		louis
 */
define("DEFAULT_PAGESIZE", 20);
class model_withdraw_WDUnverifyReasonList extends model_pay_base_List {
	
	/**
	 * 管理员ID
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 管理员姓名
	 *
	 * @var string
	 */
	public $AdminName;
	
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
	 * 状态
	 *
	 * @var int
	 */
	public $Status;
	
	
	/**
	 * 返回审核未通过原因列表
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function __construct(){
		parent::__construct('withdraw_unverify_reason','wur.id',DEFAULT_PAGESIZE,' as wur');
	}
	
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*");
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
		if ($this->AdminId > 0)									$sSelectSql .= " AND admin_id = '{$this->ApiId}'";
		if (!empty($this->AdminName))							$sSelectSql .= " AND admin_name = '{$this->AdminName}'";
		if (!empty($this->StartTime) && empty($this->EndTime))	$sSelectSql .= " AND atime > '{$this->StartTime}'";
		if (!empty($this->EndTime) && empty($this->StartTime))	$sSelectSql .= " AND atime < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime)) $sSelectSql .= " AND atime BETWEEN '{$this->StartTime}' AND 
																				'{$this->EndTime}'";
		if (isset($this->Status))								$sSelectSql .= " AND status = {$this->Status}";
		return $sSelectSql;
	}
}