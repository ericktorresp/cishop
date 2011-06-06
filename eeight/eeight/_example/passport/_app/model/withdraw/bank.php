<?php
/**
 * 银行信息类
 * 
 * @version 	v1.0	2010-03-08
 * @author 		louis
 *
 */
class model_withdraw_Bank extends model_pay_base_info{

	/**
	 * 银行id
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 银行名称
	 *
	 * @var string
	 */
	public $BankName;
	
	/**
	 * 卡类型
	 *
	 * @var string
	 */
	public $CardType;
	
	/**
	 * 是否支持手工提现
	 *
	 * @var tinyint
	 */
	public $Manual;
	
	/**
	 * 银行网银地地
	 *
	 * @var string
	 */
	public $Url;
	
	/**
	 * 银行logo
	 *
	 * @var logo
	 */
	public $Logo;
	
	/**
	 * 卡号长度
	 *
	 * @var string  (16,19)
	 */
	public $CodeLength;
	
	/**
	 * 银行开启状态
	 *
	 * @var int					0为关闭，1为开启
	 */
	public $Status;

	
	
	
	
	/**
	 * 获取指定银行信息
	 *
	 * @param	 int		 $Id							// 银行id
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		array  $aBankInfo						// 指定银行信息
	 */
	public function __construct($id = 0){
		parent::__construct('pay_bank');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id			= $aResult['id'];
			$this->BankName		= $aResult['bank_name'];
			$this->CardType		= $aResult['card_type'];
			$this->CodeLength 	= $aResult['code_length'];
			$this->Manual		= $aResult['manual_withdraw'];
			$this->Url			= $aResult['url'];
			$this->Logo			= $aResult['logo'];
			$this->Status		= $aResult['status'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据银行id的有无来判断是调用新增银行信息方法还是修改银行信息方法
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 *
	 * @return 		int		$iResult						// 成功则返回受影响的行数,失败则返回错误码
	 */
	public function save(){
		if($this->Id){
			// 修改指定银行信息
			return $this->_set();		
		} else {
			// 增加新的银行信息
			return $this->_add();
		}
	}
	
	
	/**
	 * 插入一条银行信息
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		mix		integer or boolean
	 */
	private function _add(){
		// 数据检查
		if ( empty($this->BankName)/* || empty($this->CardType) || empty($this->CodeLength) || empty($this->Url)*/) 
			return false;
		// 执行写入
		$iStatus = $this->Status ? 1 : 0;
		$aData = array(
			'bank_name'			=> $this->BankName,
			'card_type'			=> $this->CardType,
			'code_length'		=> $this->CodeLength,
			'manual_withdraw'	=> $this->Manual,
			'url'				=> $this->Url,
			'logo'				=> $this->Logo,
			'status'			=> $iStatus,
			'utime'			=> date('Y-m-d H:i:s')
		);
		return $this->oDB->insert( $this->Table, $aData );
	}

	
	/**
	 * 修改一条银行信息
	 * 
	 * @version 	v1.0	2010-03-08
	 * @return 		louis
	 * 
	 * @return 		int		$iResult						// 成功则返回受影响的行数,失败则返回错误码
	 */
	private function _set(){
		// 数据检查
		if ( empty($this->Id) || empty($this->BankName)/* || empty($this->CardType) || empty($this->CodeLength)
			 || empty($this->Url)*/) 
			return false;
			
		// 执行修改
		$iStatus = $this->Status ? 1 : 0;
		$aData = array(
			'bank_name'			=> $this->BankName,
			'card_type'			=> $this->CardType,
			'code_length'		=> $this->CodeLength,
			'manual_withdraw'	=> $this->Manual,
			'url'				=> $this->Url,
			'status'			=> $iStatus,
			'utime'			=> date('Y-m-d H:i:s')
		);
		if (!empty($this->Logo)){
			$aData['logo'] = $this->Logo;
		}
		return $this->oDB->update($this->Table, $aData, "id = {$this->Id}");
	}
	
	
	/**
	 * 修改银行状态
	 *
	 * @version 	v1.0	2010-03-19
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function setStatus(){
		if ( empty($this->Id) || !is_numeric($this->Id) || !is_numeric($this->Status)) 
			return false;
		$sSql = "UPDATE $this->Table SET status = 1 - status, utime='".date('Y-m-d H:i:s')."' WHERE id = {$this->Id}";
		$affected_rows = $this->oDB->query($sSql);
		return $affected_rows ? true : false;
	}

	
	/**
	 * 删除银行信息
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		mix		$mResult						//成功则返回true,失败则返回错误码
	 */
	public function erase(){
		// id检查
		if (!$this->Id) return false;
		return $this->oDB->delete( $this->Table, "id = {$this->Id}" );
	}

	
	/**
	 * 获取指定银行信息
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 *
	 * @return 		array    $aResult						// 查询结果集
	 */
	public function getBankInfo(){
		$aResult = array();
		// id检查
		if (!$this->Id) return $aResult;
		// 查询数据
		$sSql = "SELECT * FROM {$this->Table} WHERE id = {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
	
	
	
	/**
	 * 通过银行名称检查银行信息是否已经存在
	 *
	 * @version 	v1.0	2010-03-31
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function bankExists(){
		if (empty($this->BankName))		return true;
		$sSql = "SELECT * FROM {$this->Table} WHERE bank_name = '{$this->BankName}'";
		$aResult = $this->oDB->getOne( $sSql );
		return  empty($aResult) ? false : true;
	}
}	