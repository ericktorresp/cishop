<?php
/**
 * 支付接口接受的充值银行类
 *
 */
class model_withdraw_paybank extends model_pay_base_info {
	
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
	 * 排序
	 *
	 * @var int
	 */
	public $Seq;
	
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
	 * 构造函数，返回指定银行信息
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function __construct($id = 0){
		parent::__construct('pay_bank_list');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id				= $aResult['id'];
			$this->ApiId			= $aResult['api_id'];
			$this->ApiName			= $aResult['api_name'];
			$this->BankId			= $aResult['bank_id'];
			$this->BankName			= $aResult['bank_name'];
			$this->BankCode			= $aResult['bank_code'];
			$this->Seq				= $aResult['seq'];
			$this->UpdateTime		= $aResult['utime'];
			$this->AddTime			= $aResult['atime'];
			$this->Status			= $aResult['status'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据充值银行id的有无，来判断是调用信息增加方法还是信息修改方法
	 *
	 * @version 	v1.0	2010-04-09
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
	 * 增加一条充值银行信息
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _add(){
		if(intval($this->ApiId) <= 0 || empty($this->ApiName) || intval($this->BankId) <= 0 || empty($this->BankName) || 
			empty($this->BankCode) || !isset($this->Status))
			return false;
			
		$sSql = "SELECT COUNT(id) as count FROM {$this->Table} WHERE api_id = {$this->ApiId}";
		$aResult = $this->oDB->getOne($sSql);
		$aData = array(
			'api_id'		=> $this->ApiId,
			'api_name'		=> $this->ApiName,
			'bank_id'		=> $this->BankId,
			'bank_name'		=> $this->BankName,
			'bank_code'		=> $this->BankCode,
			'seq'			=> intval($aResult['count'] + 1),
			'status'		=> $this->Status,
			'atime'			=> $this->AddTime,
			'utime' 	=> date('Y-m-d H:i:s')
		);
		return $this->oDB->insert( $this->Table, $aData );
	}
	
	
	/**
	 * 修改一条充值银行信息
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _set(){
		if (!$this->Id)		return false;
		$aData = array();
		if (!empty($this->BankCode))		$aData['bank_code']		= trim($this->BankCode);
		if (intval($this->Seq) > 0)			$aData['seq']			= intval($this->Seq);
		if (isset($this->Status))			$aData['status'] 		= $this->Status;
		if (empty($aData))	return false;
		$aData['utime'] = date('Y-m-d H:i:s');
		$this->oDB->update($this->Table, $aData, "id = {$this->Id}");
		if ($this->oDB->errno() > 0){
			return false;
		} else {
			return true;
		}
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
		$sSql = "SELECT id FROM {$this->Table} WHERE bank_id = '{$this->BankId}' AND api_id = '{$this->ApiId}'";
		$aResult = $this->oDB->getOne($sSql);
		return empty($aResult) ? false : true;
	}
	
	
	
	/**
	 * 设置排序
	 *
	 * @version 	v1.0	2010-05-06
	 * @author 		louis
	 */
	public function setSeq($aSetq, $aBankId){
		if (intval($this->ApiId) <= 0 || empty($aSetq) || empty($aBankId)) return false;
		
		// 事务开始
		$this->oDB->doTransaction();
		foreach ($aBankId as $k => $v){
			$this->Id = $v;
			$this->Seq = $aSetq[$v];
			if ($this->_set() === false){
				// 事务回滚
				$this->oDB->doRollback();
				return -1;
			}
		}
		$this->oDB->doCommit();
		return true;
	}
}