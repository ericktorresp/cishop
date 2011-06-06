<?php
/**
 * 提现报表数据格式列表类
 *
 */
define("DEFAULT_PAGESIZE", 20);
class model_withdraw_WithdrawFormatList extends model_pay_base_List {
	
	/**
	 * 报表名称ID
	 *
	 * @var int
	 */
	public $PPId;
	
	/**
	 * 管理员ID
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 管理员
	 *
	 * @var string
	 */
	public $ADminName;
	
	/**
	 * 查询起始时间
	 *
	 * @var string
	 */
	public $StartTime;
	
	/**
	 * 查询结束时间
	 *
	 * @var string
	 */
	public $EndTime;
	
	/**
	 * 状态
	 *
	 * @var int
	 */
	public $Status;
	
	/**
	 * 排序条件语句
	 *
	 * @var string
	 */
	public $OrderBy;
	
	
	/**
	 * 返回提现报表数据格式列表
	 *
	 * @version 	v1.0	2010-04-14
	 * @author 		louis
	 */
	public function __construct(){
		parent::__construct('withdraw_format','wf.id',DEFAULT_PAGESIZE,' as wf');
	}
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-04-14
	 * @author 		louis
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*");
	}
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 *
	 * @version 	v1.0	2010-04-14
	 * @author 		louis
	 * 
	 * @return 		string
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if ($this->PPId > 0)									$sSelectSql .= " AND pp_id = '{$this->PPId}'";
		if ($this->AdminId > 0)									$sSelectSql .= " AND admin_id = '{$this->AdminId}'";
		if (!empty($this->ADminName))							$sSelectSql .= " AND admin_name = '{$this->ADminName}'";
		if (!empty($this->StartTime) && empty($this->EndTime))	$sSelectSql .= " AND atime > '{$this->StartTime}'";
		if (!empty($this->EndTime) && empty($this->StartTime))	$sSelectSql .= " AND atime < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime)) $sSelectSql .= " AND atime BETWEEN '{$this->StartTime}' AND 
																				'{$this->EndTime}'";
		if (isset($this->Status))								$sSelectSql .= " AND status in ({$this->Status})";
		if (!empty($this->OrderBy))								$sSelectSql .= " ORDER BY {$this->OrderBy}";
		return $sSelectSql;
	}
}