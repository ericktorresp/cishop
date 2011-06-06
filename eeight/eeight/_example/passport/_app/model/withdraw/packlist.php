<?php
/**
 * 数据包列表类
 *
 */
class model_withdraw_PackList extends model_pay_base_List {

	/**
	 * 管理员id
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 管理员名称
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
	 * 页数
	 *
	 * @var int
	 */
	public $Pages;
	
	
	/**
	 * 返回行政区列表
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 */
	public function __construct(){
		parent::__construct('report_download_info','rdi.id', DEFAULT_PAGESIZE, ' as rdi');
	}
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*", $this->Pages > 0 ? $this->Pages : 1, 'id', true);
	}
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if (!empty($this->AdminId))									$sSelectSql .= " AND admin_id = '{$this->AdminId}'";
		if (!empty($this->AdminName))								$sSelectSql .= " AND admin_name = '{$this->AdminName}'";
		if (!empty($this->StartTime) && empty($this->EndTime))		$sSelectSql .= " AND atime > '{$this->StartTime}'";
		if (!empty($this->EndTime) && empty($this->StartTime))		$sSelectSql .= " AND atime < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime)) 	$sSelectSql .= " AND atime BETWEEN '{$this->StartTime}' AND
																				'{$this->EndTime}'";
		return $sSelectSql;
	}
}