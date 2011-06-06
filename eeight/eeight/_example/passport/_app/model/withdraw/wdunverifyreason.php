<?php

/**
 * 提现申请审核未通过原因类
 *
 * @version 	v1.0	2010-04-16
 * @author 		louis
 */
class model_withdraw_WDUnverifyReason extends model_pay_base_info {
	
	
	/**
	 * 编号
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 未通过审核原因
	 *
	 * @var string
	 */
	public $Reason;
	
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
	 * 最近一次更新时间
	 *
	 * @var datetime
	 */
	public $UpdateTime;
	
	/**
	 * 添加时间
	 *
	 * @var datetime
	 */
	public $AddTime;
	
	/**
	 * 状态
	 *
	 * @var int
	 */
	public $Status;
	
	
	
	/**
	 * 构造函数,获取指定未通过审核原因
	 * 
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function __construct($id = 0){
		parent::__construct('withdraw_unverify_reason');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id				= $aResult['id'];
			$this->Reason			= $aResult['reason'];
			$this->AdminId			= $aResult['admin_id'];
			$this->AdminName		= $aResult['admin_name'];
			$this->UpdateTime		= $aResult['utime'];
			$this->AddTime			= $aResult['atime'];
			$this->Status			= $aResult['status'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据未通过审核原因id的有无，来判断是调用信息增加方法还是信息修改方法
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		loui
	 * 
	 * @return 	int or boolean
	 */
	public function save(){
		if ($this->Id){
			return $this->_set();
		} else {
			return $this->_add();
		}
	}
	
	
	
	/**
	 * 增加一条未通过审核原因
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _add(){
		if(empty($this->Reason) || intval($this->AdminId) <= 0 || empty($this->AdminName) || empty($this->AddTime) || 
			!isset($this->Status))
			return false;
		$aData = array(
			'reason'		=> $this->Reason,
			'admin_id'		=> $this->AdminId,
			'admin_name'	=> $this->AdminName,
			'utime' 	=> date('Y-m-d H:i:s'),
			'atime'			=> $this->AddTime,
			'status'		=> $this->Status
		);
		return $this->oDB->insert( $this->Table, $aData );
	}
	
	
	/**
	 * 修改一条未通过审核原因
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _set(){
		if (!$this->Id)		return false;
		$aData = array();
		if (!empty($this->Reason))		$aData['reason']		= trim($this->Reason);
		if (intval($this->AdminId) > 0)	$aData['admin_id']		= intval($this->AdminId);
		if (!empty($this->AdminName))	$aData['admin_name']	= intval($this->AdminName);
		if (isset($this->Status))		$aData['status'] 		= $this->Status;
		if (empty($aData))	return false;
		$aData['utime'] = date('Y-m-d H:i:s');
		return $this->oDB->update($this->Table, $aData, "id = {$this->Id}");
	}
	
	
	/**
	 * 删除指定提现审核未通过原因
	 *
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	public function erase(){
		if (!$this->Id)	return false;
		return $this->oDB->delete($this->Table, "id = {$this->Id}");
	}
}