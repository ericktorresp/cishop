<?php
/**
 *  充值回调本站业务流程
 * 
 * 	  帐变、更新支付单记录、更新分账户余额 
 *  
 * @name ReceivePayment.php
 * @package payment
 * @version 0.1 5/21/2010
 * @author Jim
 * 
 */

class model_pay_receivepayment extends model_pay_base_info {
	
	/**
	 * 支付单ID
	 * @var int
	 */
	public $LoadId;
	/**
	 * 回调接收到的数据
	 *  以数组固定键名形式提供: 本站支付单ID，第三方支付平台产生的ID，支付用户，支付金额，第三方平台返回状态
	 * @var array(loadid,platid,userid,amount,status)
	 * 
	 */
	public $ReceiveData;
	
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 *  第三方支付平台充值回调处理
	 *  	事务包装: 帐变、加分账户余额、更新支付历史记录表
	 */
	public function runProcess(){
		
		if (!is_numeric($this->LoadId) || empty($this->ReceiveData) ) return -2001;
		
		parse_str($this->ReceiveData);
		
		$iLoadId 		= $this->ReceiveData['loadid'];
		$iPlatformId 	= $this->ReceiveData['platid'];
		$iPaymentUserid = $this->ReceiveData['userid'];
		$fPayAmount 	= $this->ReceiveData['amount'];
		$iPaymentStatus = $this->ReceiveData['status'];
		
		if ( empty($iLoadId) || empty($iPlatformId) || ($iPaymentUserid <= 0) || ($fPayAmount <= 0)  || empty($iPaymentStatus) ) return -2002;
		
		$oOL = new model_pay_loadinfo ( intval ( $this->LoadId ), -1 );
		if ( $oOL->AccId <= 0 ) return -2003;
		
		$oPayport = new model_pay_payaccountinfo ( $oOL->AccId );
		$oPayport->GetType = true;
		$oPayport->OptType = 'onlineload';
		$oPayport->getAccountDataObj();
		
		// 开始事务
		$this->oDB->doTransaction();
	
		// 加锁资金账户
		$oUserFund = new model_userfund();
		$sFields = " u.`userid`,u.`username`,uf.`availablebalance`,u.`authtoparent` ";
		$aUserinfo = $oUserFund->getFundByUser ( $iPaymentUserid, $sFields );
		if (empty ( $aUserinfo )) 
		{
				// 充值失败,资金账户无法加锁
				$this->oDB->doRollback();
				return -2004;
				
		}
		else
		{
				// 计算手续费
				$fRealAmount = ($oPayport->PayDeduct) ?  $oOL->LoadAmount : floatval ( $fPayAmount );
				$sLogstr = '[L' . $this->LoadId . ']从' . $oOL->PayName . '充值' . $fRealAmount . $oPayport->AccCurrency;
				$sLogstrFee = '收取充值手续费' . $oOL->LoadFee . $oPayport->AccCurrency;
				if (! is_numeric ( $oOL->LoadFee )) 
				{
					$this->oDB->doRollback();
					
					$oOL->saveLogs ( $this->LoadId .':'. $oOL->SpecName . ':手续费获取错误' );
					return -2005;
				}
				// 帐变
				$bAddmoney = $oUserFund->systemOnlineLoadforUser ( 0, $iPaymentUserid, $fRealAmount, $sLogstr, $oOL->LoadFee, $sLogstrFee );
				if ($bAddmoney === true) 
				{
					$sErrStr = '充值成功';
					$oOL->LoadStatus = 1;
				} 
				elseif ($bAddmoney === -1) 
				{
					$sErrStr = '充值失败,金额不符 01';
					$oOL->LoadStatus = 3;
				} 
				elseif ($bAddmoney === -2) 
				{
					$sErrStr = '充值失败,资金账户被锁';
					$oOL->LoadStatus = 0; 	// 可再次接受回调
				} 
				elseif ($bAddmoney === -3) 
				{
					$sErrStr = '充值成功,资金账户解锁失败';
					$oOL->LoadStatus = 1;
				} 
				elseif ($bAddmoney === -4) 
				{
					$sErrStr = '充值失败,且资金账户解锁失败';
					$oOL->LoadStatus = 0; 	// 可再次接受回调
				} 
				elseif ($bAddmoney === -5) 
				{
					$sErrStr = '充值失败,资金账户已解锁';
					$oOL->LoadStatus = 0; 	// 可再次接受回调
				} 
				else 
				{
					$sErrStr = '充值失败,未知原因 10';
					$oOL->LoadStatus = 3;
				}
				
				// 根据帐变处理返回值处理
				if ( ($bAddmoney === true) || ($bAddmoney === -3) )
				{
					// 计算支付接口手续费,分账户余额不包括支付接口收取的手续费
					$aPaltformFee = $oPayport->payportFee ( floatval ( $fPayAmount ), false );
					// 存余额
					if (! $oPayport->saveBalanceReceive ( floatval($aPaltformFee[0]) ) )
					{
						$this->oDB->doRollback();
						
						$oOL->saveLogs ( $this->LoadId .':'. $oOL->SpecName . ':[用户]' . $iPaymentUserid . ' 分账户余额增加失败' . $aPaltformFee[0] );
						return -2006;
					}
					
					// 更新支付单数据
					$oOL->TransID = $iPlatformId;
					$oOL->ValidationKey = $sign;
					$oOL->Id = $this->LoadId;
					$oOL->RebackTime = date ( "Y-m-d H:i:s" );
					$oOL->RebackNote = 'L'.$this->LoadId . ':' . $iPaymentStatus;

					if ( $oOL->set() ) 
					{
						// 执行事务
						$this->oDB->doCommit();
						
						$oOL->saveLogs ( $this->LoadId .':'. $oOL->SpecName . ':' . $sErrStr );
						// 成功
						return true;
					
					}
					else
					{
						$this->oDB->doRollback();
						
						$oOL->saveLogs ( $this->LoadId .':'. $oOL->SpecName . ':支付虽成功,状态更新失败,撤消' );
						return -2007;
					}
				
		
				}
				else
				{
					$this->oDB->doRollback();
					
					$oOL->saveLogs ( $this->LoadId .':'. $oOL->SpecName . ':充值失败 '.$sErrStr );
					return -2008;
				}
				
		}
		
	}

}