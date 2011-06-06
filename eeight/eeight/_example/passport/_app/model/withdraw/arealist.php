<?php
/**
 * 行政区列表类
 *
 */
class model_withdraw_AreaList extends model_pay_base_List {

	/**
	 * 行政区ID
	 *
	 * @var unknown_type
	 */
	public $Id;
	
	/**
	 * 行政区所属ID
	 *
	 * @var unknown_type
	 */
	public $ParentId;
	
	/**
	 * 行政区名称
	 *
	 * @var unknown_type
	 */
	public $Name;
	
	/**
	 * 是否使用
	 *
	 * @var int
	 */
	public $Used;
	
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
		parent::__construct('pay_district','pd.id', 40, ' as pd');
	}
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*", $this->Pages > 0 ? $this->Pages : 1, 'name');
	}
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if ($this->Id > 0)									$sSelectSql .= " AND id = '{$this->Id}'";
		if ($this->ParentId >= 0)							$sSelectSql .= " AND parent_id = '{$this->ParentId}'";
		if (!empty($this->Name))							$sSelectSql .= " AND name = '{$this->Name}'";
		if (isset($this->Used))								$sSelectSql .= " AND used = $this->Used";
		return $sSelectSql;
	}
}