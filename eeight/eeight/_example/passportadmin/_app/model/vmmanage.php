<?php

/**
 * 文件：/passportadmin/_app/model/vmmanage.php
 * 功能：虚拟机相关管理
 *
 * --getList							获取虚拟机列表
 * --Insert								写入虚拟机管理列表
 * --edit								修改虚拟机信息
 * --getOne								获取指定记录信息
 * --isExists							查询是否已绑定了此银行卡
 * 
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-09-16
 * @package 	passportadmin
 * 
 */
class model_vmmanage extends basemodel{
	
	/**
	 * 编号
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 *  受付银行ID，账户所属的银行
	 * @var int
	 */
	public $BankId;
	
	/**
	 * 虚拟机别名
	 *
	 * @var string
	 */
	public $AliasNam;
	
	/**
	 * 虚拟机ip
	 *
	 * @var string
	 */
	public $VmIP;
	
	/**
	 * 虚拟机ip
	 *
	 * @var string
	 */
	public $VpnIp;
	
	/**
	 * 对应分账户id
	 *
	 * @var int
	 */
	public $AccId;
	
	/**
	 * 运行状态，1为运行，0为禁止
	 *
	 * @var int
	 */
	public $IsRunning;
	
	/**
	 * 是否包含自己
	 *
	 * @var boolean
	 */
	public $UnSelf;
	
	/**
	 * 获取虚拟机列表
	 *
	 * @author 			louis
	 * @version 		v1.0
	 * @since 			2010-09-16
	 * @package 		passportadmin
	 * 
	 * 
	 * @param int $iBankId 受付银行ID
	 * @return 			array
	 * 
	 */
	public function getList($iBankId=false){
		
		if ( $iBankId != false && !is_numeric($iBankId) ) return FALSE;
		
		$aResult = array();
		$sWhere = "";
		
		if ( isset($this->IsRunning) ){
			$sWhere .= "  AND `is_running` = {$this->IsRunning}";
		}

		!$iBankId && $iBankId != 999999 or $sWhere .= " AND `bank_id`=". intval($iBankId);
		
		$aResult = array();
		$sSql = "SELECT * FROM `vmtables` WHERE 1" . $sWhere;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	
	
	
	/**
	 * 写入虚拟机管理列表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-16
	 * @package 	passportadmin
	 * 
	 * @return 		mix					> 0			// 成功
	 * 									false		// 失败
	 * 
	 */
	public function Insert(){
		// 数据检查
		if (empty($this->AliasNam) || !is_numeric($this->AccId) || $this->AccId <= 0 || empty($this->VpnIp) || !is_numeric($this->IsRunning)){
			return false;
		}
		
		$aData = array();
		$aData['alias_name']		= $this->AliasNam;
		$aData['card_id']			= $this->AccId;
		$aData['bank_id']			= $this->BankId;
		$aData['ip']				= $this->VmIP;
		$aData['vpn_ip']			= $this->VpnIp;
		$aData['is_running']		= $this->IsRunning;
		$aData['dse_session_id']	= "";
		$aData['cookie']			= "";
		$aData['errno']				= 0;
		$aData['params']			= "";
		$aData['create_date']		= "0000-00-00 00:00:00";
		
		return $this->oDB->insert('vmtables', $aData);
	}
	
	
	/**
	 * 修改虚拟机信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-16
	 * @package 	passportadmin
	 * 
	 * @return 		boolean			true		// 成功
	 * 								false		// 失败
	 * 
	 */
	public function edit(){
		// 数据检查
		if (!is_numeric($this->Id) || $this->Id <= 0){
			return false;
		}
		if (empty($this->AliasNam) || !is_numeric($this->AccId) || $this->AccId <= 0 || empty($this->VpnIp) || !is_numeric($this->IsRunning)){
			return false;
		}
		
		$aData = array();
		$aData['alias_name']		= $this->AliasNam;
		$aData['card_id']			= $this->AccId;
		$aData['bank_id']			= $this->BankId;
		$aData['ip']				= $this->VmIP;
		$aData['vpn_ip']			= $this->VpnIp;
		$aData['is_running']		= $this->IsRunning;
		$this->oDB->update('vmtables', $aData, " vm_id = {$this->Id}");
		if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
			return false;
		} else {
			return true;
		}
	}
	
	
	
	/**
	 * 获取指定记录信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-16
	 * @package 	passportadmin
	 * 
	 * @return 		array
	 * 
	 */
	public function getOne(){
		$aResult = array();
		// 数据检查
		if (!is_numeric($this->Id) || $this->Id <= 0){
			return $aResult;
		}
		
		$sSql = "SELECT * FROM `vmtables` WHERE `vm_id` = {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
	
	
	
	
	/**
	 * 查询是否已绑定了此银行卡
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-16
	 * @package 	passportadmin
	 * 
	 * @return 		boolean			true    // 存在
	 * 								false   // 不存在
	 * 
	 */
	public function isExists(){
		$aResult = array();
		// 数据检查
		if (!is_numeric($this->AccId) || $this->AccId <= 0){
			return true;
		}
		
		$sSelf = "";
		if ($this->UnSelf === true){
			$sSelf .= " AND `vm_id` != {$this->Id}";
		}
		
		$sSql = "SELECT * FROM `vmtables` WHERE `card_id` = {$this->AccId} AND is_running = 1" . $sSelf;
		$aResult = $this->oDB->getAll($sSql);
		return empty($aResult) ? flase : true;
	}
	
	
	
	
	/**
	 * 删除虚拟机信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-16
	 * @package 	passportadmin
	 * 
	 * @return 		boolean
	 * 
	 */
	public function delete(){
		// 数据检查
		if (!is_numeric($this->Id) || $this->Id <= 0){
			return false;
		}
		
		$this->oDB->delete('vmtables', "`vm_id` = {$this->Id}");
		if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
			return false;
		} else {
			return true;
		}
	}
}