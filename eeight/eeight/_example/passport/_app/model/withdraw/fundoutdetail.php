<?php
/**
 * 在线提现明细类
 * 
 * @version 	v1.0	2010-03-08
 * @author 		louis
 *
 */

define("REMAIN_TIMES", 10); // 管理员可发起的操作提现操作次数
class model_withdraw_fundoutdetail extends model_pay_base_info{
	
	/**
	 * 在线提现明细id
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * id串或单个id
	 *
	 * @var string
	 */
	public $IdList;
	
	/**
	 * 对应orders表的id
	 *
	 * @var int
	 */
	public $OrdersId;
	
	/**
	 * 明细编号
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
	 * 分账户ID
	 *
	 * @var int
	 */
	public $AccId;
	
	/**
	 * 在线提现方式名称
	 *
	 * @var string
	 */
	public $ApiName;
	
	/**
	 * API别名
	 *
	 * @var string
	 */
	public $ApiNickname;
	
	/**
	 * 分账户名称
	 *
	 * @var string
	 */
	public $AccName;
	
	/**
	 * 币种
	 *
	 * @var string
	 */
	public $MoneyType;
	
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
	 * 提现原始金额
	 *
	 * @var float
	 */
	public $SourceMoney;
	
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
	 * 用户银行卡信息ID
	 *
	 * @var int
	 */
	public $UserBankId;
	
	/**
	 * 银行ID
	 *
	 * @var int
	 */
	public $BankId;
	
	/**
	 * 开户银行名称
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
	 * 省份ID
	 *
	 * @var int
	 */
	public $ProvinceId;
	
	/**
	 * 开户银行省份
	 *
	 * @var string
	 */
	public $Province;
	
	/**
	 * 城市ID
	 *
	 * @var string
	 */
	public $CityId;
	
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
	 * @var datetime
	 */
	public $RequestTime;
	
	/**
	 * 提现申请结束时间
	 *
	 * @var datetime
	 */
	public $FinishTime;
	
	/**
	 * 管理员id
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
	 * 审核管理员id
	 *
	 * @var int
	 */
	public $VerifyAdminId;
	
	/**
	 * 审核操作管理员
	 *
	 * @var string
	 */
	public $Verify;
	
	/**
	 * 审核操作时间
	 *
	 * @var datatime
	 */
	public $Verify_time;
	
	/**
	 * 审核未通过原因
	 *
	 * @var string
	 */
	public $UnverifyComment;
	
	/**
	 * 用户IP
	 *
	 * @var string
	 */
	public $IP;
	
	/**
	 * CDNIP
	 *
	 * @var string
	 */
	public $CDNIP;
	
	/**
	 * 账变状态
	 *
	 * @var int
	 */
	public $Status;
	
	/**
	 * 是否被下载过
	 *
	 * @var int
	 */
	public $DLStatus;
	
	
	/**
	 * 剩余可操作次数
	 *
	 * @var int
	 */
	public $RemainTimes;
	
	/**
	 * 显示卡号的位数
	 *
	 * @var int
	 */
	public $Digit;
	
	/**
	 * sql语句的where条件
	 *
	 * @var string
	 */
	public $Where;
	
	
	
	
	/**
	 * 返回指定在线提现明细信息
	 *
	 * @param int $id						// 在线提现明细id
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		array	$aResult		// 结果集数组
	 */
	public function __construct($id = 0){
		parent::__construct('pay_out_details');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id				= $aResult['id'];
			$this->OrdersId			= $aResult['orders_id'];
			$this->No				= $aResult['no'];
			$this->ApiId			= $aResult['api_id'];
			$this->AccId			= $aResult['acc_id'];
			$this->ApiName			= $aResult['api_name'];
			$this->ApiNickname		= $aResult['api_nickname'];
			$this->AccName			= $aResult['acc_name'];
			$this->MoneyType		= $aResult['money_type'];
			$this->UserId			= $aResult['user_id'];
			$this->UserName			= $aResult['user_name'];
			$this->SourceMoney		= $aResult['source_money'];
			$this->Amount			= $aResult['amount'];
			$this->Charge			= $aResult['charge'];
			$this->UserBankId		= $aResult['userbank_id'];
			$this->BankId			= $aResult['bank_id'];
			$this->BankName			= $aResult['bank_name'];
			$this->BankCode			= $aResult['bank_code'];
			$this->ProvinceId		= $aResult['province_id'];
			$this->Province			= $aResult['province'];
			$this->CityId			= $aResult['city_id'];
			$this->City				= $aResult['city'];
			$this->Branch			= $aResult['branch'];
			$this->AccountName		= $aResult['account_name'];
			$this->Account			= $aResult['account'];
			$this->AccountName		= $aResult['account_name'];
			$this->RequestTime		= $aResult['request_time'];
			$this->FinishTime		= $aResult['finish_time'];
			$this->AdminId			= $aResult['admin_id'];
			$this->Operater			= $aResult['operater'];
			$this->VerifyAdminId	= $aResult['verify_admin_id'];
			$this->Verify			= $aResult['verify'];
			$this->Verify_time		= $aResult['verify_time'];
			$this->IP				= $aResult['IP'];
			$this->CDNIP			= $aResult['CDNIP'];
			$this->Status			= $aResult['status'];
			$this->DLStatus			= $aResult['download_status'];
			$this->RemainTimes		= $aResult['remain_times'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据明细id的有无来判断是调用新增明细方法还是修改明细信息方法
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		mix		int	or boolean
	 */
	public function save(){
		if($this->Id){
			// 更改在线提现明细状态
			return $this->_set();		
		} else {
			// 写入在线提现明细信息
			return $this->_add();
		}
	}
	
	
	
	/**
	 * 写入在线提现明细信息
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 *@return 		mix		integer or boolean
	 */
	private function _add(){
		if (empty($this->OrdersId) || empty($this->No) || empty($this->ApiId) || empty($this->ApiName) || empty($this->UserId)
			|| empty($this->UserName) || empty($this->Amount) || !is_numeric($this->Amount) || empty($this->Charge) ||
			!is_numeric($this->Charge) || empty($this->BankName) || empty($this->Province) || empty($this->City) ||
			empty($this->Branch) || empty($this->AccountName) || empty($this->Account) || empty($this->MoneyType)
			|| intval($this->BankId) <= 0 || empty($this->BankCode) || intval($this->ProvinceId) < 0 || 
			intval($this->CityId) < 0 || intval($this->UserBankId) <= 0){
			return false;
		}
		$aData = array(
			'orders_id'		=> $this->OrdersId,
			'no'			=> $this->No,
			'api_id'		=> $this->ApiId,
			'acc_id'		=> $this->AccId,
			'api_name'		=> $this->ApiName,
			'api_nickname'	=> $this->ApiNickname,
			'acc_name'		=> $this->AccName,
			'money_type'	=> $this->MoneyType,
			'user_id'		=> $this->UserId,
			'user_name'		=> $this->UserName,
			'source_money'	=> $this->SourceMoney,
			'amount'		=> $this->Amount,
			'charge'		=> $this->Charge,
			'userbank_id'	=> $this->UserBankId,
			'bank_id'		=> $this->BankId,
			'bank_name'		=> $this->BankName,
			'bank_code'		=> $this->BankCode,
			'province_id'	=> $this->ProvinceId,
			'province'		=> $this->Province,
			'city_id'		=> $this->CityId,
			'city'			=> $this->City,
			'branch'		=> $this->Branch,
			'account_name'	=> $this->AccountName,
			'account'		=> $this->Account,
			'request_time'	=> $this->RequestTime,
			'IP'			=> $this->IP,
			'CDNIP'			=> $this->CDNIP,
			'remain_times'	=> REMAIN_TIMES,
			'utime' => date('Y-m-d H:i:s')
		);
		return $this->oDB->insert( $this->Table, $aData );
	}
	
	
	/**
	 * 更改在线提现明细信息
	 *
	 * @version 	v1.0	2010-03-09
	 * @author 		louis
	 * 
	 * @return 		mix 	integer or boolean
	 */
	private function _set(){
		if (!$this->Id) return false;
		$aData = array();
		if (!empty($this->FinishTime))			$aData['finish_time'] 		= $this->FinishTime;
		if (intval($this->AdminId) > 0)			$aData['admin_id'] 			= $this->AdminId;
		if (!empty($this->Operater))			$aData['operater'] 			= $this->Operater;
		if (intval($this->VerifyAdminId) > 0)	$aData['verify_admin_id'] 	= $this->VerifyAdminId;
		if (!empty($this->Verify))				$aData['verify'] 			= $this->Verify;
		if (!empty($this->Verify_time))			$aData['verify_time'] 		= $this->Verify_time;
		if (!empty($this->UnverifyComment))		$aData['unverify_comment']	= $this->UnverifyComment;
		if (!empty($this->Status))				$aData['status'] 			= $this->Status;
		if (!empty($this->Where))				$sWhere = " AND {$this->Where}";
		if (empty($aData)) 		return false;
		$aData['utime'] = date('Y-m-d H:i:s');
		return $this->oDB->update($this->Table, $aData, "id = {$this->Id}" . $sWhere);
	}
	
	
	
	/**
	 * 更改在线提现明细状态
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 *
	 * @return 		mix		integer or boolean	
	 */
	public function setStatus(){
		if (!$this->Id)		return false;
		$aData = array(
			'status' => $this->Status,
			'utime'	 => date('Y-m-d H:i:s')
		);
		if (!empty($this->Where))	$sWhere = " AND {$this->Where}";
		return $this->oDB->update($this->Table, $aData, "id = {$this->Id}" . $sWhere);
	}
	
	
	/**
	 * 检查传入的id串中是否有已经下载过的记录
	 * 
	 * @version 	v1.0	2010-04-20
	 * @author 		louis
	 * 
	 * @return 		string
	 */
	public function downloadedInfo(){
		if (empty($this->IdList)) return true;
		$sSql = "SELECT id FROM {$this->Table} WHERE id in ({$this->IdList}) AND download_status = 1";
		$aResult = $this->oDB->getOne($sSql);
		return !empty($aResult) ? implode(',', $aResult) : -1;
	}
	
	
	/**
	 * 写入在线提现划款操作明细信息
	 * 
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 *
	 * @return 		integer
	 */
	public function addOperateInfo(){
		// 数据检查
		if (empty($this->OrdersId) || !is_numeric($this->OrdersId) || empty($this->No) || empty($this->ApiId) ||
			!is_numeric($this->ApiId) || empty($this->ApiName) || empty($this->Amount) || empty($this->Charge)
			|| empty($this->Province) || empty($this->City) || empty($this->Branch) || 
			empty($this->AccountName) || empty($this->Account) || empty($this->Operater) || empty($this->Status)
			|| intval($this->BankId) <= 0 || empty($this->BankCode) || intval($this->ProvinceId) < 0 || 
			intval($this->CityId) < 0 || intval($this->UserBankId) <= 0 || !is_numeric($this->AdminId) || $this->AdminId <= 0){
				return false;
			}
		$aData = array(
			'details_id' 	=> $this->Id,
			'no'			=> $this->No,
			'api_id'		=> $this->ApiId,
			'acc_id'		=> $this->AccId,
			'api_name'		=> $this->ApiName,
			'api_nickname'	=> $this->ApiNickname,
			'acc_name'		=> $this->AccName,
			'money_type'	=> $this->MoneyType,
			'user_id'		=> $this->UserId,
			'user_name'		=> $this->UserName,
			'source_money'	=> $this->SourceMoney,
			'amount'		=> $this->Amount,
			'charge'		=> $this->Charge,
			'userbank_id'	=> $this->UserBankId,
			'bank_id'		=> $this->BankId,
			'bank_name'		=> $this->BankName,
			'bank_code'		=> $this->BankCode,
			'province_id'	=> $this->ProvinceId,
			'province'		=> $this->Province,
			'city_id'		=> $this->CityId,
			'city'			=> $this->City,
			'branch'		=> $this->Branch,
			'account_name'	=> $this->AccountName,
			'account'		=> $this->Account,
			'request_time'	=> $this->RequestTime,
			'finish_time'	=> $this->FinishTime,
			'admin_id'		=> $this->AdminId,
			'operater'		=> $this->Operater,
			'return_code'	=> $this->ReturnCode,
			'status'		=> $this->Status,
			'utime' => date('Y-m-d H:i:s')
		);
		return $this->oDB->insert( "pay_out_operate_detail", $aData );
	}
	
	
	/**
	 * 减少在线提现申请的可执行次数
	 *
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		integer
	 */
	public function reduceTimes(){
		// 数据检查，并且发起提现次数应该大于0
		if (intval($this->Id) <= 0 || $this->RemainTimes <= 0)	return false;
		$bResult = $this->oDB->query("UPDATE {$this->Table} SET remain_times = remain_times - 1, utime='".date('Y-m-d H:i:s')."' WHERE id = {$this->Id} AND remain_times > 0");
		// 更新剩余发起提现次数
		!$bResult or $this->RemainTimes = $this->RemainTimes - 1;
		return $bResult ? true : false;
	}
	
	
	/**
	 * 获取对应提现申请的划款操作信息
	 * 可能为一对多
	 *
	 * @version 	v1.0	2010-03-08
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function getOperateDetails(){
		if (!$this->Id)		return false;
		$sSql = "SELECT * FROM pay_out_operate_detail WHERE details_id = {$this->Id}";
		$aResult = $this->oDB->getOne( $sSql );
		return !empty($aResult) ?  $aResult : false;
	}
	
	
	/**
	 * 通过对应orders表的id查询提现申请信息
	 *
	 * @version 	v1.0	2010-03-18
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function getInfoByOrderId(){
		if (!$this->No)	return false;
		$sSql = "select id,status from $this->Table where no = '{$this->No}'";
		$aResult = $this->oDB->getOne( $sSql );
		return $aResult;
	}
	
	/**
	 * 返回指定id的提现申请记录信息，接受单个id或id串
	 *
	 * @version 		v1.0	2010-03-23
	 * @author 			louis
	 * 
	 * @return 			array
	 */
	public function getInfoById(){
		if (empty($this->IdList))		return false;
		$sSql = "SELECT * FROM $this->Table WHERE id in ({$this->IdList})";
		$aResult = $this->oDB->getAll( $sSql );
		return $aResult;
	}
	
	
	
	/**
	 * 将下载过的记录置为已下载
	 * 
	 * @version 	v1.0		2010-03-24
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function setDownloadStatus(){
		if (empty($this->IdList))		return false;
		$aData = array(
			'status' 			=> 7,
			'download_status' 	=> 1,
			'utime' => date('Y-m-d H:i:s')
		);
		return $bResult = $this->oDB->update($this->Table, $aData, "id in ({$this->IdList}) AND status = 2 AND download_status = 0");
	}
	
	
	/**
	 * 查询用户提现申请中，等待审核的记录条数
	 *
	 * @version 	v2.0	2010-12-07
	 * @author 		louis
	 * 
	 * @return 		int
	 */
	public function countUncheck(){
		// 数据检查
		if (intval($this->UserId) <= 0 || !isset($this->Status) || empty($this->Account))	return false;
		$sSql = "SELECT count(`entry`) as num FROM `withdrawel` WHERE userid = {$this->UserId} AND status IN ({$this->Status}) AND `bankcard` = '{$this->Account}'";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult['num'];
	}
	
	
	/**
	 * 将银行卡号隐藏，只显示后四位
	 *
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 */
	public function hiddenAccount(){
		if (!is_numeric($this->Digit) || empty($this->Account))	return false;
		return str_repeat("*",strlen($this->Account) - $this->Digit) . substr($this->Account, -$this->Digit, $this->Digit);
	}
	
	
	/**
	 * 用户取消经线提现申请
	 *
	 * @param 		int		$iAgentId		// 总代管理员id
	 * @version 	v1.0	2010-04-28
	 * @author 		louis
	 */
	public function CancelWithdraw($iAgentId = 0){
		if (!$this->Id)	return false;
		$oUserFund = new model_userfund();
    	// 锁定用户资金
    	if( FALSE == $oUserFund->switchLock($this->UserId, 0, TRUE) )
        {
            return $this->iError = -2; // 锁定用户账户失败
        }
        
        // 事务开始
        $this->oDB->doTransaction();
        
        $oOrders   = new model_orders();
        // 写入账变数据数组
    	$aOrders = array();
    	$aOrders['iFromUserId'] = $this->UserId; // (发起人) 用户id
    	$aOrders['iToUserId'] = 0; // (关联人) 用户id
    	if ($iAgentId > 0){
    		$aOrders['iAgentId'] = $iAgentId; // 总代管理员id
    	}
    	$aOrders['iOrderType'] = ORDER_TYPE_ZXTXJD; // 账变类型
    	$aOrders['fMoney'] = $this->SourceMoney; // 账变的金额变动情况
    	$aOrders['sDescription'] = '在线提现解冻'; // 账变的描述
    	$aOrders['iChannelID'] = 0; // 发生帐变的频道ID
    	$aOrders['iAdminId'] = 0; // 管理员id
    	
    	if ($oOrders->addOrders($aOrders) === true) {
    		if ($this->setStatus()){
    			// 事务提交
        		$this->oDB->doCommit();
        		$oUserFund->switchLock($this->UserId, 0, false); // 用户解锁
        		return true;
    		} else {
    			// 事务回滚
	    		$this->oDB->doRollback();
	    		$oUserFund->switchLock($this->UserId, 0, false); // 用户解锁
	    		return false;
    		}
    	} else {
    		// 事务回滚
    		$this->oDB->doRollback();
    		$oUserFund->switchLock($this->UserId, 0, false); // 用户解锁
    		return false;
    	}
	}
	
	
	
	/**
	 * 第三方支付平台或银行后台手工提现所需报表格式
	 * 
	 * @version 	v1.0	2010-04-05
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function getReportContent(){
		// 报表内容数组
		$aReportContent = array(
			0 => array(
				'title' 	=> "提现明细ID(Withdrawal No.)",
				'property'	=> "Id"
			),
			1 => array(
				'title'		=> "账变ID",
				'property'	=> "OrdersId"
			),
			2 => array(
				'title'		=> "提现明细编号(Transaction No.)",
				'property'	=> "No"
			),
			3 => array(
				'title'		=> "第三方支付平台ID",
				'property'	=> "ApiId"
			),
			4 => array(
				'title'		=> "分账户ID",
				'property'	=> "AccId"
			),
			5 => array(
				'title'		=> "第三方支付平台名称",
				'property'	=> "ApiName"
			),
			6 => array(
				'title'		=> "第三方支付平台昵称",
				'property'	=> "ApiNickname"
			),
			7 => array(
				'title'		=> "分账户名称",
				'property'	=> "AccName"
			),
			8 => array(
				'title'		=> "币种(Currency)",
				'property'	=> "MoneyType"
			),
			9 => array(
				'title'		=> "用户ID(Login ID)",
				'property'	=> "UserId"
			),
			10 => array(
				'title'		=> "用户名称(User Name)",
				'property'	=> "UserName"
			),
			11 => array(
				'title'		=> "提现金额(Withdrawal Amount)",
				'property'	=> "SourceMoney"
			),
			12 => array(
				'title'		=> "银行ID",
				'property'	=> "BankId"
			),
			13 => array(
				'title'		=> "银行名称(Bank)",
				'property'	=> "BankName"
			),
			14 => array(
				'title'		=> "银行代码",
				'property'	=> "BankCode"
			),
			15 => array(
				'title'		=> "提现手续费(Bank Charge)",
				'property'	=> "Charge"
			),
			16 => array(
				'title'		=> "提现净额(Net Withdrawal Amount)",
				'property'	=> "Amount"
			),
			17 => array(
				'title'		=> "省份(Provice)",
				'property'	=> "Province"
			),
			18 => array(
				'title'		=> "城市(City)",
				'property'	=> "City"
			),
			19 => array(
				'title'		=> "支行名称(Branch)",
				'property'	=> "Branch"
			),
			20 => array(
				'title'		=> "开户人姓名(Beneficiary Name)",
				'property'	=> "AccountName"
			),
			21 => array(
				'title'		=> "银行账号(Beneficiary Account)",
				'property'	=> "Account"
			),
			22 => array(
				'title'		=> "用户发起提现时间(Withdrawal Submitted)",
				'property'	=> "RequestTime"
			),
			23 => array(
				'title'		=> "提现结束时间(Withdrawal Successful)",
				'property'	=> "FinishTime"
			),
			24 => array(
				'title'		=> "操作管理员(Operator)",
				'property'	=> "Operater"
			),
			25 => array(
				'title'		=> "审核管理员(Approver)",
				'property'	=> "Verify"
			),
			26 => array(
				'title'		=> "审核操作时间(Approve)",
				'property'	=> "Verify_time"
			),
			27 => array(
				'title'		=> "未审核通过原因(Rejected Reason)",
				'property'	=> "UnverifyComment"
			),
			28 => array(
				'title'		=> "用户IP(Login IP)",
				'property'	=> "IP"
			),
			29 => array(
				'title'		=> "CDNIP",
				'property'	=> "CDNIP"
			),
			30 => array(
				'title'		=> "状态(Status)",
				'property'	=> "Status"
			),
			31 => array(
				'title'		=> "是否被下载过",
				'property'	=> "DLStatus"
			),
			32 => array(
				'title'		=> "剩余操作次数",
				'property'	=> "RemainTimes"
			),
		);	
		return $aReportContent;
	} 
}