<?php
/**
 * api提现的银行类
 *
 */
class model_withdraw_ApiWithdrawBank extends model_pay_base_info{

	/**
	 * ID
	 *
	 * @var int
	 */
	public $Id;		
	
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
	 * 最近一次修改时间
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
	 * 银行卡号
	 *
	 * @var string
	 */
	public $Account;
	
	/**
	 * 卡用户id
	 *
	 * @var int
	 */
	public $UserId;
	
	
	/**
	 * 构造函数,获取指定提现银行信息
	 * 
	 * @version 	v1.0	2010-04-07
	 * @author 		louis
	 */
	public function __construct($id = 0){
		parent::__construct('withdraw_bank_list');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id				= $aResult['id'];
			$this->ApiId			= $aResult['api_id'];
			$this->ApiName			= $aResult['api_name'];
			$this->BankId			= $aResult['bank_id'];
			$this->BankName			= $aResult['bank_name'];
			$this->BankCode			= $aResult['bank_code'];
			$this->UpdateTime		= $aResult['utime'];
			$this->AddTime			= $aResult['atime'];
			$this->Status			= $aResult['status'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	
	/**
	 * 根据api提现的银行id的有无，来判断是调用信息增加方法还是信息修改方法
	 *
	 * @version 	v1.0	2010-04-07
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
	 * 增加一条用户api提现的银行信息
	 *
	 * @version 	v1.0	2010-04-07
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _add(){
		if(intval($this->ApiId) <= 0 || empty($this->ApiName) || intval($this->BankId) <= 0 || empty($this->BankName) || 
			empty($this->BankCode) || !isset($this->Status))
			return false;
		$aData = array(
			'api_id'		=> $this->ApiId,
			'api_name'		=> $this->ApiName,
			'bank_id'		=> $this->BankId,
			'bank_name'		=> $this->BankName,
			'bank_code'		=> $this->BankCode,
			'status'		=> $this->Status,
			'atime'			=> $this->AddTime,
			'utime' => date('Y-m-d H:i:s')
		);
		return $this->oDB->insert( $this->Table, $aData );
	}
	
	
	/**
	 * 修改一条用户api提现的银行信息
	 *
	 * @version 	v1.0	2010-04-08
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _set(){
		if (!$this->Id)		return false;
		$aData = array();
		if (!empty($this->BankCode))	$aData['bank_code']		= trim($this->BankCode);
		if (isset($this->Status))		$aData['status'] 		= $this->Status;
		if (empty($aData))	return false;
		$aData['utime'] = date('Y-m-d H:i:s');
		return $this->oDB->update($this->Table, $aData, "id = {$this->Id}");
	}
	
	
	/**
	 * 通过银行名称判断银行信息是否存在
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function bankExists(){
		if (empty($this->BankName) || empty($this->ApiName))		return true;
		$sSql = "SELECT id FROM {$this->Table} WHERE bank_name = '{$this->BankName}' AND api_name = '{$this->ApiName}'";
		$aResult = $this->oDB->getOne($sSql);
		return empty($aResult) ? false : true;
	}
	
	
	/**
	 * 获取指定银行信息
	 *
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function getInfoByBankId(){
		if (intval($this->BankId) <= 0)		return false;
		if (isset($this->Status)) $sCondition = " AND status = {$this->Status}";
		$sSql = "SELECT * FROM {$this->Table} WHERE bank_id = {$this->BankId}" . $sCondition;
		return $this->oDB->getOne($sSql);
	}
	
	
	
	/**
	 * 平台内检查是否存在相同卡号绑定的情况
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-23
	 * @package 	passport
	 * 
	 * @return 		boolean				// 返回0表示不存在，返回1表示存在
	 * 
	 */
	public function bankExistByCard(){
		if (empty($this->Account))		return 1;
		$sSql = "SELECT * FROM user_bank_info WHERE `account` = '{$this->Account}' AND `status` = 1";
		$aResult = $this->oDB->getAll($sSql);
		return count($aResult) > 1 ? 1 : 0;
}
}