<?php
/**
 * 在线提现划款操作列表类
 * 
 * @version 	v1.0	2010-03-08
 * @author 		louis
 *
 */
class model_withdraw_fundoutoperateList extends model_pay_base_List{
	
	/**
	 * 对应在线提现明细表id
	 *
	 * @var int
	 */
	public $DetailsId;
	
	/**
	 * 划款明细编号
	 *
	 * @var string
	 */
	public $No;
	
	/**
	 * 在线提现接口id
	 *
	 * @var int
	 */
	public $ApiId;
	
	/**
	 * 在线提现接口名称
	 *
	 * @var string
	 */
	public $ApiName;
	
	/**
	 * 用户id
	 *
	 * @var int
	 */
	public $UserId;
	
	/**
	 * 用户名
	 *
	 * @var string
	 */
	public $UserName;
	
	/**
	 * 提现金额
	 *
	 * @var float
	 */
	public $Amount;
	
	/**
	 * 提现手续费
	 *
	 * @var float
	 */
	public $Charge;
	
	/**
	 * 银行名称
	 *
	 * @var string
	 */
	public $BankName;
	
	/**
	 * 省份
	 *
	 * @var string
	 */
	public $Province;
	
	/**
	 * 城市
	 *
	 * @var string
	 */
	public $City;
	
	/**
	 * 支行名称
	 *
	 * @var string
	 */
	public $Branch;
	
	/**
	 * 银行账号
	 *
	 * @var bigint
	 */
	public $Account;
	
	/**
	 * 请求发起时间
	 *
	 * @var datetime
	 */
	public $RequestTime;
	
	/**
	 * 提现结束时间
	 *
	 * @var datetime
	 */
	public $FinishTime;
	
	/**
	 * 操作管理员
	 *
	 * @var string
	 */
	public $Operater;
	
	/**
	 * 返回错误码
	 *
	 * @var int
	 */
	public $ReturnCode;
	
	/**
	 * 状态
	 *
	 * @var int
	 */
	public $Status;
	
	public function __construct($id = 0){
		parent::__construct('pay_out_details','pd.id',DEFAULT_PAGESIZE,'as pd');
	}
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-03-09
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function init(){
		$this->FindAll("1" . $this->_createWhereSql() , "*");
	}
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 *
	 * @version 	v1.0	2010-03-09
	 * @author 		louis
	 * 
	 * @return 		string
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if (!empty($this->DetailsId))		$sSelectSql .= " AND details_id = '{$this->DetailsId}'";
		if (!empty($this->No))   			$sSelectSql .= " AND no = '{$this->No}'";
		if (!empty($this->ApiId))			$sSelectSql .= " AND api_id = {$this->ApiId}";
		if (!empty($this->ApiName))			$sSelectSql .= " AND api_name = '{$this->ApiName}'";
		if (!empty($this->UserId))			$sSelectSql .= " AND user_id = {$this->UserId}";
		if (!empty($this->UserName))		$sSelectSql .= " AND user_name = '{$this->UserName}'";
		if (!empty($this->Amount))   		$sSelectSql .= " AND amount = {$this->Amount}";
		if (!empty($this->Charge))			$sSelectSql .= " AND charge = {$this->Charge}";
		if (!empty($this->BankName))		$sSelectSql .= " AND bank_name = '{$this->BankName}'";
		if (!empty($this->Province))		$sSelectSql .= " AND province = '{$this->Province}'";
		if (!empty($this->City))			$sSelectSql .= " AND city = '{$this->City}'";
		if (!empty($this->Branch))			$sSelectSql .= " AND branch = '{$this->Branch}'";
		if (!empty($this->Account))			$sSelectSql .= " AND account = {$this->Account}";
		if (!empty($this->RequestTime))		$sSelectSql .= " AND request_time = {$this->RequestTime}";
		if (!empty($this->FinishTime))		$sSelectSql .= " AND finish_time = {$this->FinishTime}";
		if (!empty($this->Operater))		$sSelectSql .= " AND operater = '{$this->Operater}'";
		if (!empty($this->ReturnCode))		$sSelectSql .= " AND return_code = {$this->ReturnCode}";
		if (!empty($this->Status))			$sSelectSql .= " AND status = {$this->Status}";
		return $sSelectSql;
	}
}