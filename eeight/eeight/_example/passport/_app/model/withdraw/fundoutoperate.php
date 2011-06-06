<?php
/**
 * 在线提现划款操作明细类
 *
 * @version 	v1.0	2010-03-08
 * @author 		louis
 * 
 */
class model_withdraw_fundoutoperate extends model_pay_base_info{
	
	/**
	 * 在线提现操作明细表id
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 对应在线提现明细表id
	 *
	 * @var int
	 */
	public $DetailsId;
	
	/**
	 * 账变编号
	 *
	 * @var string
	 */
	public $No;
	
	/**
	 * 在线提现方式id
	 *
	 * @var int
	 */
	public $ApiId;
	
	/**
	 * 在线提现方式名称
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
	 * 开户银行名称
	 *
	 * @var string
	 */
	public $BankName;
	
	/**
	 * 开户银行省份
	 *
	 * @var string
	 */
	public $Province;
	
	/**
	 * 开户银行城市
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
	 * 个人银行账号
	 *
	 * @var int
	 */
	public $Account;
	
	/**
	 * 用户发起在线提现申请时间
	 *
	 * @var datatime
	 */
	public $RequestTime;
	
	/**
	 * 此次操作结束时间
	 *
	 * @var datatime
	 */
	public $FinishTime;
	
	/**
	 * 操作管理员id
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 操作管理员
	 *
	 * @var string
	 */
	public $Operater;
	
	/**
	 * 记录ecapay返回的错误码
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
	
	
	/**
	 * 返回指定在线提现失败操作明细信息
	 *
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function __construct($id = 0){
		parent::__construct('pay_out_operate_detail');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne($sSql);
			$this->Id			= $aResult['id'];
			$this->DetailsId	= $aResult['details_id'];
			$this->No			= $aResult['no'];
			$this->ApiId		= $aResult['api_id'];
			$this->ApiName		= $aResult['api_name'];
			$this->UserId		= $aResult['user_id'];
			$this->UserName		= $aResult['user_name'];
			$this->Amount		= $aResult['amount'];
			$this->Charge		= $aResult['charge'];
			$this->BankName		= $aResult['bank_name'];
			$this->Province		= $aResult['province'];
			$this->City			= $aResult['city'];
			$this->Branch		= $aResult['branch'];
			$this->AccountName	= $aResult['account_name'];
			$this->Account		= $aResult['account'];
			$this->RequestTime	= $aResult['request_time'];
			$this->FinishTime	= $aResult['finish_time'];
			$this->AdminId		= $aResult['admin_id'];
			$this->Operater		= $aResult['operater'];
			$this->ReturnCode	= $aResult['return_code'];
			$this->Status		= $aResult['status'];
		} else {
			$this->Id = 0;
		}
	}
}