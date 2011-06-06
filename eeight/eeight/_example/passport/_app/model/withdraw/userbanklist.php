<?php
/**
 * 用户卡号绑定信息列表类
 *
 */
define("DEFAULT_PAGESIZE", 20);
class model_withdraw_UserBankList extends model_pay_base_List {
	
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
	 * 邮箱地址
	 *
	 * @var string
	 */
	public $Email;
	
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
	 * 省份ID
	 *
	 * @var int
	 */
	public $ProvinceId;
	
	/**
	 * 省份
	 *
	 * @var string
	 */
	public $Province;
	
	/**
	 * 城市ID
	 *
	 * @var int
	 */
	public $CityId;
	
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
	 * 开户人姓名
	 *
	 * @var string
	 */
	public $AccountName;
	
	/**
	 * 开户账号
	 *
	 * @var string
	 */
	public $Account;
	
	/**
	 * 状态
	 *
	 * @var int
	 */
	public $Status;
	
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
	 * 返回用户卡号绑定信息列表
	 * 
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 */
	public function __construct(){
		parent::__construct('user_bank_info','ubi.id',DEFAULT_PAGESIZE,' as ubi');
	}
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 *
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*");
	}
	
	
	/**
	 * 根据用户选择的查询条件创建查询时使用的sql语句中的where条件
	 *
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 * 
	 * @return 		string
	 */
	private function _createWhereSql(){
		$sSelectSql = "";
		if ($this->UserId > 0)									$sSelectSql .= " AND user_id = '{$this->UserId}'";
		if (!empty($this->UserName))							$sSelectSql .= " AND user_name like '%{$this->UserName}%'";
		if (!empty($this->Email))								$sSelectSql .= " AND email = '{$this->Email}'";
		if (!empty($this->BankId))								$sSelectSql .= " AND bank_id = '{$this->BankId}'";
		if (!empty($this->BankName))							$sSelectSql .= " AND bank_name = '{$this->BankName}'";
		if (!empty($this->ProvinceId))							$sSelectSql .= " AND province_id = '{$this->ProvinceId}'";
		if (!empty($this->Province))							$sSelectSql .= " AND province = '{$this->Province}'";
		if (!empty($this->CityId))								$sSelectSql .= " AND city_id = '{$this->CityId}'";
		if (!empty($this->City))								$sSelectSql .= " AND city = '{$this->City}'";
		if (!empty($this->Branch))								$sSelectSql .= " AND branch = '{$this->Branch}'";
		if (!empty($this->AccountName))							$sSelectSql .= " AND account_name = '{$this->AccountName}'";
		if (!empty($this->Account))								$sSelectSql .= " AND account = '{$this->Account}'";
		if (!empty($this->StartTime) && empty($this->EndTime))	$sSelectSql .= " AND atime > '{$this->StartTime}'";
		if (!empty($this->EndTime) && empty($this->StartTime))	$sSelectSql .= " AND atime < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime)) $sSelectSql .= " AND atime BETWEEN '{$this->StartTime}' AND 
																				'{$this->EndTime}'";
		if (isset($this->Status))								$sSelectSql .= " AND status in ({$this->Status})";
		return $sSelectSql;
	}
}