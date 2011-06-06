<?php

/**
 * 用户报表名称列表类
 *
 */
define('DEFAULT_PAGESIZE', 20);
class model_withdraw_WithdrawReportList extends model_pay_base_List {
	
	/**
	 * 平台类型
	 *
	 * @var string
	 */
	public $PlatformType;
	
	/**
	 * 平台ID
	 *
	 * @var int
	 */
	public $BankId;
	
	/**
	 * apiID
	 *
	 * @var int
	 */
	public $ApiId;
	
	/**
	 * 银行名称
	 *
	 * @var string
	 */
	public $BankName;
	
	/**
	 * api名称
	 *
	 * @var string
	 */
	public $ApiName;
	
	/**
	 * 报表标题
	 *
	 * @var string
	 */
	public $ReportName;
	
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
	 * 页数
	 *
	 * @var int
	 */
	public $Pages;
	
	
	/**
	 * 返回提现报表数据格式列表
	 *
	 * @version 	v1.0	2010-04-18
	 * @author 		louis
	 */
	public function __construct(){
		parent::__construct('withdraw_report','wr.id',DEFAULT_PAGESIZE,' as wr');
	}
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-04-18
	 * @author 		louis
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*", $this->Pages > 0 ? $this->Pages : 1, 'id', true);
	}
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 *
	 * @version 	v1.0	2010-04-18
	 * @author 		louis
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if ($this->PlatformType > 0)							$sSelectSql .= " AND platform_type = '{$this->PlatformType}'";
		if ($this->BankId > 0)									$sSelectSql .= " AND bank_id = '{$this->BankId}'";
		if ($this->ApiId > 0)									$sSelectSql .= " AND api_id = '{$this->ApiId}'";
		if (isset($this->Status))								$sSelectSql .= " AND status in ({$this->Status})";
		if (!empty($this->StartTime) && empty($this->EndTime))	$sSelectSql .= " AND atime > '{$this->StartTime}'";
		if (!empty($this->EndTime) && empty($this->StartTime))	$sSelectSql .= " AND atime < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime)) $sSelectSql .= " AND atime BETWEEN '{$this->StartTime}' AND 
																				'{$this->EndTime}'";
		if (!empty($this->ApiName))								$sSelectSql .= " AND api_name like '%{$this->ApiName}%'";
		if (!empty($this->BankName))							$sSelectSql .= " AND bank_name like  '%{$this->BankName}%'";
		return $sSelectSql;
	}
}