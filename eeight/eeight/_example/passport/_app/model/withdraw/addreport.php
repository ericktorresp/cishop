<?php
/**
 * 增加提现报表操作
 * 
 * 
 * @version 	v1.0	2010-05-06
 * @author 		louis
 * 
 * @return 		boolean
 */
class model_withdraw_AddReport extends model_pay_base_info{
	
	/**
	 * 错误编号
	 *
	 * @var max
	 */
	public $Error;
	
	/**
	 * 完成提现申请操作流程
	 *
	 * @param 	array		$aInfo			// 所有操作数据
	 * $aInfo['report_name']				// 报表名称
	 * $aInfo['platform_type']				// 平台类型 1为第三方平台，2为银行平台
	 * $aInfo['platform_name']				// 平台id
	 * $aInfo['charset_type']				// 平台接受的编码
	 * $aInfo['admin']						// 操作管理员id
	 * $aInfo['adminname']					// 操作管理员
	 * $aInfo['status']						// 报表状态,报表内容列状态
	 * 
	 * @version 	v1.0	2010-05-06
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function __construct( $aInfo, $aContent ){
		parent::__construct();
		// 数据检查
		if (empty($aInfo['report_name']) || empty($aInfo['platform_type']) || intval($aInfo['platform_name']) <= 0 ||
			empty($aInfo['charset_type']) || intval($aInfo['admin']) <= 0 || empty($aInfo['adminname']) || 
			!isset($aInfo['status'])){
				return $this->Error = -1;
			}
		// 事务开始
		$this->oDB->doTransaction();
		$oWithdrawReport = new model_withdraw_WithdrawReport();
		// 首先检查是否存在同名报表，如果存在则提示用户，并返回
		$oWithdrawReport->ReportName	= trim($aInfo['report_name']);
		$bResult = $oWithdrawReport->reportExistsByName();
		if ($bResult === true){
			// 事务回滚
			$this->oDB->doRollback();
			return $this->Error = -3;
		}
		$oWithdrawReport->PlatformType	= intval($aInfo['platform_type']);
		$oWithdrawReport->PlatformId	= intval($aInfo['platform_name']);
		if (intval($aInfo['platform_type']) == 1){
			// 查询对应支付接口名称
			$oApi = new model_pay_payportinfo();
			$oApi->getPayportData(intval($aInfo['platform_name']), 'intro');
			$oWithdrawReport->PlatformName = $oApi->PayportName;
		} else {
			// 查询对应银行名称
			$oBank = new model_withdraw_Bank(intval($aInfo['platform_name']));
			$oWithdrawReport->PlatformName = $oBank->BankName;
		}
		$oWithdrawReport->Charset		= trim($aInfo['charset_type']);
		$oWithdrawReport->AdminId		= $aInfo['admin'];
		$oWithdrawReport->AdminName		= $aInfo['adminname'];
		$oWithdrawReport->Status		= intval($aInfo['status']);
		$iLastId = $oWithdrawReport->save();
		if ($iLastId > 0){
			if (!empty($aContent)){
				// 向报表格式表中写入数据
				$oWithdrawFormat = new model_withdraw_WithdrawFormat();
				foreach ($aContent as $key => $value) {
					$aTempContent = explode('#', $value);
					$oWithdrawFormat->PPId		= $iLastId;
					$oWithdrawFormat->Seq		= $key + 1;
					$oWithdrawFormat->Title		= trim($aTempContent[0]);
					$oWithdrawFormat->Property	= trim($aTempContent[1]);
					$oWithdrawFormat->AdminId	= $aInfo['admin'];
					$oWithdrawFormat->AdminName	= trim($aInfo['adminname']);
					$oWithdrawFormat->Status	= $aInfo['status'];
					$oWithdrawFormat->AddTime	= date("Y-m-d H:i:s", time());
					// 先检查标题列是否已存在
					$bExists = $oWithdrawFormat->contentExistsByTitle();
					if ($bExists)	continue;
					$iFormatResult = $oWithdrawFormat->save();
					if ($iFormatResult <= 0){
						// 事务回滚
						$this->oDB->doRollback();
						return $this->Error = -2;
					}
				}
			}
			// 事务提交
			$this->oDB->doCommit();
			return $this->Error = true;
		} else 
			// 事务回滚
			$this->oDB->doRollback();
			return $this->Error = false;
	}
}