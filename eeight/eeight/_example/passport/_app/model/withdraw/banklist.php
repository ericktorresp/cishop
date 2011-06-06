<?php
/**
 * 银行信息列表类
 * 
 * @version 	1.0 	2010-03-08
 * @author 		louis
 *
 */
define("DEFAULT_PAGESIZE", 20);
class model_withdraw_BankList extends model_pay_base_List{

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
	 * 卡号长度
	 *
	 * @var string     eg:(16,19)
	 */
	public $CodeLength;
	
	/**
	 * 是否接受手工提现
	 *
	 * @var int
	 */
	public $Manual;
	
	/**
	 * 状态				0表示关闭，1表示开启
	 *
	 * @var int
	 */
	public $Status;
	
	/**
	 * 页数
	 *
	 * @var int
	 */
	public $Pages;
	
	
	/**
	 * 返回银行信息列表
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 *
	 */
	public function __construct(){
		parent::__construct('pay_bank','pb.id',DEFAULT_PAGESIZE,' as pb');
	}
	
	
	
	/**
	 * 初始化函数，返回满足条件的结果集
	 * 
	 *@version 		v1.0	2010-03-09
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function init(){
		$this->FindAll( " 1 " . $this->_createWhereSql(), "*", $this->Pages > 0 ? $this->Pages : 1, 'bank_name', true);
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
		if (!empty($this->BankName))		$sSelectSql .= " AND bank_name = '{$this->BankName}'";
		if (!empty($this->CardType))		$sSelectSql .= " AND card_type = '{$this->CardType}'";
		if (!empty($this->CodeLength))		$sSelectSql .= " AND code_length = '{$this->CodeLength}'";
		if (!empty($this->Manual))			$sSelectSql .= " AND manual_withdraw = '{$this->Manual}'";
		if (!empty($this->Status))			$sSelectSql .= " AND status in ({$this->Status})";
		return $sSelectSql;
	}
}