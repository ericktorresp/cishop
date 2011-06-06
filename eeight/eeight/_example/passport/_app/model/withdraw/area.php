<?php
/**
 * 行政区类
 *
 */
class model_withdraw_Area extends model_pay_base_info {
		
	/**
	 * 编号
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 父ID
	 *
	 * @var int
	 */
	public $ParentId;
	
	/**
	 * 名称
	 *
	 * @var string
	 */
	public $Name;
	
	/**
	 * 名称1
	 *
	 * @var string
	 */
	public $Name1;
	
	/**
	 * 名称2
	 *
	 * @var string
	 */
	public $Name2;
	
	/**
	 * 名称3
	 *
	 * @var string
	 */
	public $Name3;
	
	/**
	 * 全称
	 *
	 * @var string
	 */
	public $FullName;
	
	/**
	 * 邮编
	 *
	 * @var string
	 */
	public $Zipcode;
	
	/**
	 * 区号
	 *
	 * @var string
	 */
	public $Telecode;
	
	/**
	 * 是否启用标志
	 *
	 * @var int
	 */
	public $Used;
	
	/**
	 * 排序
	 *
	 * @var int
	 */
	public $Orders;
	
	/**
	 * 构造函数，返回指定行政区信息
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 */
	public function __construct($id = 0){
		parent::__construct('pay_district');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id				= $aResult['id'];
			$this->ParentId			= $aResult['parent_id'];
			$this->Name				= $aResult['name'];
			$this->Name1			= $aResult['name1'];
			$this->Name2			= $aResult['name2'];
			$this->Name3			= $aResult['name3'];
			$this->FullName			= $aResult['fullname'];
			$this->Zipcode			= $aResult['zipcode'];
			$this->Telecode			= $aResult['telecode'];
			$this->Used				= $aResult['used'];
			$this->Orders			= $aResult['orders'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据行政区信息id的有无，来判断是调用信息增加方法还是信息修改方法
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	public function save(){
		if ($this->Id){
			return $this->_set();
		} else {
			return $this->_add();
		}
	}
	
	
	
	/**
	 * 增加一条行政区信息
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _add(){
		if(intval($this->ParentId) < 0 || empty($this->Name) || intval($this->Used) < 0)
			return false;
		$aData = array(
			'parent_id'		=> $this->ParentId,
			'name'			=> $this->Name,
			'zipcode'		=> $this->Zipcode,
			'telecode'		=> $this->Telecode,
			'used'			=> $this->Used,
			'orders'		=> $this->Orders,
		);
		if (!empty($this->Name1)) 		$aData['name1'] = $this->Name1;
		if (!empty($this->Name2)) 		$aData['name2'] = $this->Name2;
		if (!empty($this->Name3)) 		$aData['name3'] = $this->Name3;
		if (!empty($this->FullName)) 	$aData['fullname'] = $this->FullName;
		return $this->oDB->insert( $this->Table, $aData );
	}
	
	
	/**
	 * 修改一条行政区信息
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _set(){
		if (!is_numeric($this->Id) || $this->Id <= 0 || !is_numeric($this->ParentId) || $this->ParentId < 0)		return false;
		$aData = array();
		if ($this->ParentId > 0)		$aData['parent_id']		= $this->ParentId;
		if (!empty($this->Name))		$aData['name'] 			= $this->Name;
		if (!empty($this->Name1))		$aData['name1'] 		= $this->Name1;
		if (!empty($this->Name2))		$aData['name2'] 		= $this->Name2;
		if (!empty($this->Name3))		$aData['name3'] 		= $this->Name3;
		if (!empty($this->FullName))	$aData['fullname'] 		= $this->FullName;
										$aData['zipcode'] 		= $this->Zipcode;
		                                $aData['telecode'] 		= $this->Telecode;
		if (isset($this->Used))			$aData['used'] 			= $this->Used;
		                                $aData['orders'] 		= $this->Orders;
		if (empty($aData))	return false;
		$this->oDB->update($this->Table, $aData, "id = {$this->Id} AND parent_id = {$this->ParentId}");
		if ($this->oDB->errno() > 0){
			return false;
		} else {
			return true;
		}
	}
	
	
	/**
	 * 删除行政区信息
	 *
	 * @version 	v1.0		2010-05-11
	 * @author 		louis
	 * 
	 * @return 		bool
	 */
	public function erse(){
		if (!is_numeric($this->Id) || $this->Id <= 0) return false;
		return $this->oDB->delete( $this->Table, "id = {$this->Id} AND parent_id = 0" );
	}
	
	
	/**
	 * 获取指定行政区下的城市信息
	 *
	 * @param 		bool	$isCount			// 返回数据个数还是返回数据，true 返回个数, false 返回信息
	 * @version 	v1.0	2010-05-11
	 * @author 		louis
	 * 
	 * @return 		mix		array or int
	 */
	public function getCount($isCount = true){
		$aResult = array();
		if (!is_numeric($this->Id) || $this->Id <= 0){
			return $isCount ? 0 : $aResult;
		}
		$sSql = "SELECT * FROM {$this->Table} WHERE parent_id = {$this->Id}";
		$aResult = $this->oDB->getAll($sSql);
		return $isCount ? count($aResult) : $aResult;
	}
	
	
	/**
	 * 行政区是否存在
	 *
	 * @version 	v1.0	2010-05-11
	 * @author 		louis
	 * 
	 * @return 		bool
	 */
	public function areaIsExist(){
		if (!is_numeric($this->Id) || $this->Id <= 0) return false;
		$sSql = "SELECT count(id) as count FROM {$this->Table} WHERE id = {$this->Id} AND parent_id = 0";
		$iResult = $this->oDB->getOne($sSql);
		return $iResult['count'] > 0 ? true : false;
	}
	
	
	/**
	 * 通过行政区名称查看行政区是否已存在
	 *
	 * @version 	v1.0	2010-05-12
	 * @author 		louis
	 * 
	 * @return 		bool
	 */
	public function areaIsExistByName(){
		if (empty($this->Name))	return true;
		$sSql = "SELECT count(id) as count FROM {$this->Table} WHERE name = '{$this->Name}' AND parent_id = 0 AND id != {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult['count'] > 0 ? true : false;
	}
	
	
	/**
	 * 城市信息是否存在
	 *
	 * @version 	v1.0	2010-05-11
	 * @author 		louis
	 * 
	 * @return 		bool
	 */
	public function cityIsExist(){
		if (!is_numeric($this->Id) || $this->Id <= 0 || $this->ParentId <= 0 || !is_numeric($this->ParentId)) return false;
		$sSql = "SELECT count(id) as count FROM {$this->Table} WHERE id = {$this->Id} AND parent_id = {$this->ParentId}";
		$iResult = $this->oDB->getOne($sSql);
		return $iResult['count'] > 0 ? true : false;
	}
	
	
	/**
	 * 通过城市名称检查是否存在此城市
	 *
	 * @version 	v1.0	2010-05-12
	 * @author 		louis
	 * 
	 * @return 		bool
	 */
	public function cityIsExistByName(){
		if (empty($this->Name) || !is_numeric($this->ParentId) || $this->ParentId <= 0)	return true;
		$sSql = "SELECT count(id) as count FROM {$this->Table} WHERE name = '{$this->Name}' AND parent_id = {$this->ParentId} AND id != {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult['count'] > 0 ? true : false;
	}
	
	
	/**
	 * 删除指定城市信息
	 *
	 * @version 	v1.0	2010-05-11
	 * @author 		louis
	 * 
	 * @return 		bool
	 */
	public function delCity(){
		if (!is_numeric($this->Id) || $this->Id <= 0 || $this->ParentId <= 0 || !is_numeric($this->ParentId)) return false;
		return $this->oDB->delete( $this->Table, "id = {$this->Id} AND parent_id = {$this->ParentId}" );
	}
}