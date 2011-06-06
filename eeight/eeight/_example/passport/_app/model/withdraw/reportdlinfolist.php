<?php

/**
 * 提现报表下载记录列表类
 *
 * @version 	v1.0	2010-04-19
 * @author 		louis
 */
define("DEFAULT_PAGESIZE", 20);
class model_withdraw_ReportDLInfoList extends model_pay_base_List {
	
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
	public $AdminName;
	
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
	 * 返回报表下载的信息列表
	 *
	 * @version 	v1.0	2010-04-19
	 * @author 		louis
	 */
	public function __construct(){
		parent::__construct('report_download_info','rdi.id',DEFAULT_PAGESIZE,' as rdi');
	}
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-04-19
	 * @author 		louis
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*");
	}
	
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 *
	 * @version 	v1.0	2010-04-19
	 * @author 		louis
	 * 
	 * @return 		string
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if ($this->AdminId > 0)									$sSelectSql .= " AND admin_id = '{$this->AdminId}'";
		if (!empty($this->AdminName))							$sSelectSql .= " AND admin_name = '{$this->AdminName}'";
		if (!empty($this->StartTime) && empty($this->EndTime))	$sSelectSql .= " AND atime > '{$this->StartTime}'";
		if (!empty($this->EndTime) && empty($this->StartTime))	$sSelectSql .= " AND atime < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime)) $sSelectSql .= " AND atime BETWEEN '{$this->StartTime}' AND 
																				'{$this->EndTime}'";
		return $sSelectSql;
	}
}