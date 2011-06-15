<?php
/**
 *  银行接口分账户 信息类
 *
 *  已知ID的银行接口分账户信息 (可传入程序用名称获取信息)
 *  修改、添加、删除(逻辑)、日志
 *
 * @name 	depositAccountInfo.php
 * @package deposit
 * @version 0.1 11/16/2010
 * @author 	Jim
 *
 */

class model_deposit_depositaccountinfo extends model_deposit_depositinfo
{

	/**
	 * 银行接口 账户ID
	 * @var int
	 */
	public $AId;
			
	/**
	 * 财务使用(后台管理)识别名称
	 * @var string
	 */
	public $AccName;
	
	/**
	 * 商家ID
	 * @var string
	 */
	public $AccIdent;
	
	/**
	 * 商家KEYS
	 * @var string
	 */
	public $AccKey;

	/**
	 * 商家SITEID  兼容  工行:银行账户/电子邮件 建行:证件号码
	 * @var string
	 */
	public $AccBankAcc;

	/**
	 * 商家EMAIL
	 * @var string
	 */
	public $AccMail;

	/**
	 * 分账户功能配置
	 * @var int
	 *  即 系统使用接口的何种属性， 充值 提现 查询(掉单处理)
	 */
	public $AccAttr;

	/**
	 * 由 $this->AccAttr 记录的属性
	 * @var int
	 */
	/* 充值 */
	public $AccAttrLoad;
	/* 提现 */
	public $AccAttrDraw;
	/* 批量提现 */
	public $AccAttrDrawlist;
	/* 查询 */
	public $AccAttrQues;
	/* 手工提现 */
	public $AccAttrDrawhand;
	/**
	 * 账户所用币种
	 * @var string
	 */
	public $AccCurrency;
	
	/**
	 * 流量(余额)
	 * @var int
	 */
	public $Balance;
	/**
	 * 充值次数
	 * @var int
	 */
	public $TotalLoad;
	/**
	 * 提现次数
	 * @var int
	 */
	public $TotalDraw;
	/**
	 * 流量限制值
	 * @var float
	 */
	public $BalanceLimit;
	/**
	 * 所属支付接口ID
	 * @var int
	 */
	public $PaySlotId;

	/**
	 * 所属支付接口程序使用名称
	 * @var string
	 */
	public $PaySlotName;
	/**
	 * 回传接收域名
	 * @var string (默认为空使用payport->ReceiveHost 的信息 )
	 */
	public $AccReceiveHost;
	/**
	 * 注册时间 (账户在第三方平台注册时间)
	 * @var date
	 */
	public $RegTime;
	
	/**
	 * 有效时间截止日 (按月算)  [暂不使用]
	 * @var int
	 */
	public $ValidTime;

	/**
	 * 启用时间
	 * @var date
	 */
	public $OpenTime;

	/**
	 * 禁用或启用
	 * @var int
	 */
	public $IsEnable;
	/*
	 *  为转帐记录表增加属性,用户相关数值传递;
	 * 	array('inbalance','outbalance','bankcharge','ppid','accid')
	 */
	public $DepositValue;
	/**
	 * 数据表名
	 * @var string
	 */
	private $TableName 		 = 'deposit_acc_set';
	
	private $BalanceTable 	 = 'deposit_acc_balance';
	
	private $BalanceLogTable = 'deposit_acc_balance_logs';
	/**
	 * 获取方式 (区分前后台调用，为true时可提取被禁用的分账户信息)
	 * @var bool
	 */
	public $GetType;
	
	/**
	 * 输入参数
	 * @var array('acc_name')
	 */
	public $InputArray;
    
    /**
     * 银行卡所属地区
     *
     * @var string
     */
    public $Area;
	public $SmsNumber;
	/**
	 * 构造
	 * @param int $iPayAccId 接口分账户id
	 */
	public function __construct($iPayAccId=null,$sKey=null){
		parent::__construct();
		
		$sOptType = $this->OptType ? $this->OptType : 'account';
		
		if ( !is_null($sKey) && !empty($iPayAccId) )
		{
			switch ($sKey)
			{
				case 'accsiteid':
					$sFiledName = 'acc_bankacc';
					break;
				case 'banknumber':	//兼容工行 银行账户查询
					$sFiledName = 'acc_bankacc';
					break;
				case 'identnumber': //兼容建行 证件号码查询 其他银行可使用任意$skey代码作为 acc_bankacc 字段的查询
					$sFiledName = 'acc_bankacc';
					break;
				case 'nametail':
					$sFiledName = array('acc_ident','acc_bankacc');
					break;
				default:
					$sFiledName = NULL;
					break;
			}
			$aTmpresult = $this->getPayportIdByKey($sFiledName,$iPayAccId);
			if ($aTmpresult === FALSE)
			{
				unset($this);
				return 'Empty';
			}
			$this->AId 		 = $aTmpresult['aid'];
			$this->PaySlotId = $aTmpresult['ads_payport_id'];
			$this->getPayportData($this->PaySlotId, $sOptType='account', '-1', TRUE);
		}
		
		elseif ( is_numeric($iPayAccId) && is_null($sKey) ) {
			$this->AId 		 = $iPayAccId;
			$this->PaySlotId = $this->getPayPortId($this->AId);
			$this->getPayportData($this->PaySlotId, $sOptType='account', '-1', TRUE);
		}
		
		//如果传值是一个字符串,则为 payport_name
		elseif ( is_string($iPayAccId) && is_null($sKey) ){
			$aIID 			 = $this->getPayPortIdbyName($iPayAccId);
			$this->AId 		 = $aIID['aid'];
			$this->PaySlotId = $aIID['ads_payport_id'];
			$this->getPayportData($this->PaySlotId, $sOptType='account', '-1', TRUE);
		}
		
		
		//检查payport数据已经获取, 只在有PayAccId实例化时检查;
		if ( !empty($iPayAccId) ) {
			
			
			if (  empty($this->Id) || empty($this->SysParamPrefix)  || empty($this->PayportName)
				 )
				{
				  	 //关键信息不足 终止程序;
				  	 //die('Error:1000 Running Stop.');
				  	 unset($this);
					 return FALSE;
				}
				
		}
	}
	
	
	/**
	 * 获取单条数组形式数据
	 *
	 * @return unknown
	 */
	public function getAccountData(){
		$sSql = 'SELECT * FROM `'.$this->TableName.'` WHERE `aid`='.$this->AId;
		$aTmpResult = $this->oDB->getOne($sSql);
		$aTmpResult['payport_attr_load'] 	 = $aTmpResult['acc_attr'] & 1;
		$aTmpResult['payport_attr_draw'] 	 = $aTmpResult['acc_attr'] & 2;
		$aTmpResult['payport_attr_drawlist'] = $aTmpResult['acc_attr'] & 4;
		$aTmpResult['payport_attr_ques'] 	 = $aTmpResult['acc_attr'] & 8;
		$aTmpResult['payport_attr_drawhand'] = $aTmpResult['acc_attr'] & 16;
		$aTmpResult['acc_receive_host']  	 =  $aTmpResult['acc_receive_host'];
		$aTmpResult['payportid'] 	 	= $this->PaySlotId;
		$aTmpResult['payport_name'] 	= $this->PayportName;
		$aTmpResult['sysparam_prefix'] 	= $this->SysParamPrefix;
		$aTmpResult['payport_nickname'] = $this->PayportNickname;
		if ( ($this->GetType !== true) && ( intval($aTmpResult['isenable']) !== 1) ) return false;
		return $aTmpResult;
	}
	
	
	/**
	 * 获取对象化数据
	 */
	public function getAccountDataObj($bReturn=false){
		$aTmp = $this->getAccountData();
		
		//检查 $aTmp 有效性
		if ( empty($aTmp) ) unset($this);
		
		$this->AId = $aTmp['aid'];
		$this->AccName 		= trim($aTmp['acc_name']);
		$this->AccIdent 	= trim($aTmp['acc_ident']);
		$this->AccKey 		= trim($aTmp['acc_key']);
		$this->AccBankAcc 	= trim($aTmp['acc_bankacc']);
		$this->AccMail 		= trim($aTmp['acc_mail']);
		$this->AccAttr 		= $aTmp['acc_attr'];
		$this->AccAttrLoad  = $aTmp['payport_attr_load'];
		$this->AccAttrDraw  = $aTmp['payport_attr_draw'];
		$this->AccAttrDrawlist 	= $aTmp['payport_attr_drawlist'];
		$this->AccAttrQues 		= $aTmp['payport_attr_ques'];
		$this->AccAttrDrawhand 	= $aTmp['payport_attr_drawhand'];
		$this->AccCurrency 	= $aTmp['acc_currency'];
		$this->Balance 		= $aTmp['balance'];
		$this->BalanceLimit = $aTmp['balance_limit'];
		$this->PaySlotId 		= $aTmp['ads_payport_id'];
		$this->PaySlotName 		= $aTmp['ads_payport_name'];
		$this->AccReceiveHost 	= $aTmp['acc_receive_host'];
		$this->RegTime 		= $aTmp['reg_time'];
		$this->ValidTime 	= $aTmp['valid_time'];
		$this->OpenTime 	= $aTmp['open_time'];
		$this->IsEnable 	= $aTmp['isenable'];
		// TODO: isenable 有时变为 string ,使判断错误
		if ( ($this->GetType !== true) && ( intval($aTmp['isenable']) !== 1) ) {
			unset($this);
		}else{
			if ($bReturn) return $aTmp;
			
		}
		
	}
	
	
	/**
	 * 按payport_name 得到 aid 与 payport_id
	 *
	 */
	public function getPayPortIdbyName($sPayName){
		$sSql = "SELECT `aid`,`ads_payport_id` FROM ".$this->TableName." WHERE `ads_payport_name`='".$sPayName."'";
		$aRe  = $this->oDB->getOne($sSql);
		// 限制只有一个分账户才可使用按 程序用名称payportname 提取接口信息
		/*if ( count($aRe) > 2){
			unset($this);
			die('Error:1002 Running Stop.');
		}else{*/
			return $aRe;
		//}
	}
	
	
	/**
	 * 获取所属支付接口ID
	 *
	 */
	public function getPayPortId($iPayAccId){
		$aRe['ads_payport_id'] = '';
		$sSql = "SELECT `ads_payport_id` FROM `$this->TableName` WHERE `aid`=$iPayAccId";
		$aRe  =  $this->oDB->getOne($sSql);
		if (isset($aRe['ads_payport_id'])){
			return $aRe['ads_payport_id'];
		}else{
			return false;
		}
	}
	
	
	/**
	 * 获取指定KEY=VAL查询得到的支付接口ID payport_id 与 分账户ID  aid
	 *
	 * @param string 	$skey 数据表对应字段
	 * @param string	$sval 查询数据
	 *
	 * @return array	aid,ads_payport_id
	 */
	public function getPayportIdByKey($sKey,$sVal)
	{
		$aRe = array();
		if ( empty($sKey) || empty($sVal)) return FALSE;
		if(is_array($sKey) && is_array($sVal))
		{
			$sSql = "SELECT `aid`,`ads_payport_id` FROM `$this->TableName` WHERE `".$sKey[0]."`='".$sVal[0]."' AND `".$sKey[1]."` REGEXP '[0-9]+".$sVal[1]."' ORDER BY `aid` DESC LIMIT 1";
		}
		else
		{
			$sSql = "SELECT `aid`,`ads_payport_id` FROM `$this->TableName` WHERE `$sKey`='$sVal' ORDER BY `aid` DESC LIMIT 1";
		}
		$aRe  =  $this->oDB->getOne($sSql);
		if ( $this->oDB->errno() > 0 ) return FALSE;
		$aRe['aid'] = isset($aRe['aid']) ? $aRe['aid'] : "";
		if ( ($aRe['aid'] > 0) && ($aRe['ads_payport_id'] > 0) )
		{
			return $aRe;
		}else{
			return FALSE;
		}
	}
	
	/**
	 * 获取同一支付接口下的所有accout id
	 *
	 * @param $iId		int		id值
	 * @param $sType	string	id值的含义,(payport支付接口ID 或 account分账户ID)
	 */
	public function getPayAccIdByPayportId($iId, $sType='payport')
	{
		if ( $sType != 'payport')
		{
			//认为传入参数是accountid,获取payport id
			$iPayportid = $this->getPayPortId( intval($iId) );
			
		}
		else
		{
			$iPayportid = $iId;
			
		}
		
		$sSql = 'SELECT `aid` FROM '.$this->TableName.' WHERE `ads_payport_id`='.intval($iPayportid);
		$aResult = $this->oDB->query($sSql);
		if ( $this->oDB->errno() > 0  ||  count($aResult) < 1 )
		{
			return FALSE;
		}
		else
		{
			return $aResult;
		}
		
	}
	
	/**
	 * 设置（更新）内容
	 *
	 * @return bool
	 */
	public function set(){
		$aBranch = array();
        if (!empty($this->Area) && strpos($this->Area, "#") !== false){
            $aBranch = explode("#", $this->Area);
        }
		// 过滤不许可的字符
		if (eregi(',',$this->AccName)) return false;
		
		// 更新
		$aTempData = array();
		$aTempData = array(
				'acc_name'  => $this->AccName,
				'acc_ident' => $this->AccIdent,
				'acc_key' 	=> $this->AccKey,
				'acc_bankacc'=> $this->AccBankAcc,
				'acc_mail'  => $this->AccMail,
                'area'       => $aBranch[0],
                'branch_id'  => isset($aBranch[1]) ? $aBranch[1] : 0,
				'acc_attr'  => $this->AccAttr,
				'acc_currency' 	   => $this->AccCurrency,
				'acc_receive_host' => $this->AccReceiveHost,
				'sms_number'	=> $this->SmsNumber,
				'utime' => date('Y-m-d H:i:s')
				);
		
		
		if ( $this->checkArrayValue($aTempData, array('acc_key', 'acc_mail','area','branch_id', 'acc_attr', 'acc_currency','acc_receive_host') ) ){
			
			//生成UPDATE SQL;
			
			$sUpset = '';
			$sCond = '';
			$sCond = ' `aid`='.$this->AId;
			foreach ($aTempData AS $sKey => $sVal){
				if (!empty($sVal)) $sUpset .= "`".$sKey."`='".$sVal."',";
			}
			
			$sSql = "UPDATE ".$this->TableName." set ".substr($sUpset,0,-1)." WHERE ".$sCond;
			
			// 冗余修改SQL
			if ( ($this->InputArray['acc_name'] != $this->AccName)
				|| ($this->InputArray['acc_ident'] != $this->AccIdent)
				|| ($this->InputArray['acc_mail'] != $this->AccMail) )  {

				//事务;
				$this->oDB->doTransaction();
				
				$this->oDB->query($sSql);
				if( $this->oDB->errno() > 0 ){
					$this->oDB->doRollback();
					return false;
				}
			
				//更新
					$oDeposit = new model_deposit_companycard();
					$aUpDate = array(
						'accid' => $this->AId,
						'name'  => $this->AccIdent,
						'mail'  => $this->AccMail,
						'accname' => $this->AccName
						);
						//修改 普通卡列
					$aResult = $oDeposit->reduceUpdate('upnormal',$aUpDate);
					if ( $aResult !== TRUE )
					{
						$this->oDB->doRollback();
						return FALSE;
					}
					//修改 黑名单信息列
					$aResult = $oDeposit->reduceUpdate('upblack',$aUpDate);
					if ( $aResult !== TRUE )
					{
						$this->oDB->doRollback();
						return FALSE;
					}
					//修改 VIP信息列
					$aResult = $oDeposit->reduceUpdate('upvip',$aUpDate);
					if ( $aResult !== TRUE )
					{
						$this->oDB->doRollback();
						return FALSE;
					}
					
				$this->oDB->doCommit();
				return true;
				
			}
			else{
				// 不用事务,单条UPDATE
				$this->oDB->query($sSql);
				if( $this->oDB->errno() > 0 ){
					return false;
				}else{
					return true;
				}
			}
			
		}else{
			return false;
		}
        
	}
	
	
	/**
	 * 设置账户被开放时间
	 *	(在激活时认为开放)
	 */
	public function markOpentime(){
		$aTempData = array();
		$sCond = '';
		// 仅在第一次激活时更新
		if ($this->OpenTime <= 0){
			$aTempData = array(
				'open_time' => date('Y-m-d H:i:s'),
				'utime' 	=> date('Y-m-d H:i:s')
				);
			$sCond = ' aid = '.$this->AId;
        	return $this->oDB->update( $this->TableName, $aTempData, $sCond );
		}else{
			return true;
		}
	}
	
	
	/**
	 * 新增分账户 (默认可用)
	 *
	 *@return int lastInsertID
	 */
	public function add(){
        
        $aBranch = array();
        if (!empty($this->Area) && strpos($this->Area, "#") !== false){
            $aBranch = explode("#", $this->Area);
        }

        // 2/21/2011 added
        if ( preg_match("/[^0-9a-zA-Z@]/", $this->AccName) )        return false;
        if ( ! preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $this->AccIdent ) )   return false;
        if (  strlen($this->AccBankAcc) != 16 && strlen($this->AccBankAcc) != 19 )	return false;
        if ( $this->SrcBalance < 0 )	return false;

		$aTempData = array(
                    'acc_name'  => $this->AccName,
                    'acc_ident' => $this->AccIdent,
                    'acc_key'   => $this->AccKey,
                    'acc_bankacc' => $this->AccBankAcc,
                    'acc_mail'  => $this->AccMail,
                    'area'       => $aBranch[0],
                    'branch_id'  => isset($aBranch[1]) ? $aBranch[1] : 0,
                    'acc_attr'      => $this->AccAttr,
                    'acc_currency' => $this->AccCurrency,
                    'srcbalance' => $this->SrcBalance,
                    'inbalance'  => intval('0'),
                    'outbalance' => intval('0'),
                    'balance'    => intval('0'),
                    'balance_limit' 	=> $this->BalanceLimit,
                    'ads_payport_id' 	=> $this->PaySlotId,
                    'ads_payport_name' 	=> $this->PaySlotName,
                    'acc_receive_host' 	=> $this->AccReceiveHost,
                    'reg_time' 	=> $this->RegTime,
                    'valid_time'=> $this->ValidTime,
                    'isenable' 	=> intval('0'),
                    'utime' 	=> date('Y-m-d H:i:s'));
            if (  $this->checkArrayValue($aTempData,
			array( 'area','branch_id','acc_attr','acc_currency','srcbalance', 'inbalance', 'outbalance', 'balance','balance_limit','ads_payport_id','ads_payport_name','acc_receive_host', 'acc_receive_host', 'valid_time','isenable')
				)  )
            {
        	$this->oDB->doTransaction();
        	$result =  $this->oDB->insert( $this->TableName, $aTempData );
        	if ($result > 0){
        		// 初始化 余额 充提次数记录多线程表
			if ( $this->initBalanceTable($result, floatval($this->SrcBalance) ) ){
				$this->oDB->doCommit();
				return true;
			}else{
				$this->oDB->doRollback();
				return false;
			}
        	}else{
        		$this->oDB->doRollback();
        		return false;
        	}
        
            }else{
            	return false;
            }
        
	}


	/**
	 * 初始化余额多线程表 deposit_acc_balance
	 * @param int $iAccid
	 * @param float $fBeginAmount 初始额度(期初余额)
	 */
	private function initBalanceTable($iAccid,$fBeginAmount=0){
        	for ($i=0; $i<5; $i++){
        		
        		$iBalance =  ( ($i == 0) && ($fBeginAmount > 0) )  ?  $fBeginAmount : 0;
        		
        		$aTmp[] = array(
        			'payport_id' => $this->PaySlotId,
        			'acc_id'    => $iAccid,
        			'thread_id' => $i,
        			'balance'   => $iBalance,
        			'utime'     => date('Y-m-d H:i:s')
        		);
        	}
        	$i=0;

        	foreach ($aTmp AS $aArr){
        		if ( $this->oDB->insert( $this->BalanceTable, $aArr) ) $i++;
        	}
        	
			// 预设5线程才100%正确
        	if ($i == 5){
        		return true;
        	}else{
        		
        		return false;
        	}
	}
	
	
	/**
	 * 记录流量
	 *
	 * @param int $iAmount
	 * @param bool $sType	支付接口回调程序调用时为false,其他默认true
	 *
	 * @return bool
	 */
	public function saveBalance($iAmount=0,$sType=true){
		if (($iAmount == 0) && !is_numeric($iAmount) ) 			return false;
		// 0-4  5线程记录
        $iThreadId = intval( substr($this->oDB->getThreadId(), -1) ) % 5;
        if ( is_numeric($iThreadId) ){
        	$sSql0 = ' ';
        	// 后台余额修正不记充提次数
        	if ( $sType ){
        		// 保存Balance 不记充提次数记录
        		$sSql = "UPDATE `".$this->BalanceTable."` SET `balance`=balance+".$iAmount.", `utime`= '" . date("Y-m-d H:i:s",time()) . "' WHERE `payport_id`=".$this->PaySlotId." AND `acc_id`=".$this->AId." AND `thread_id`=".$iThreadId;
        		
        	}
        	else
        	{
        		// 保存Balance 同时增加充提次数记录
        		if ($iAmount < 0 ){
        			$sSql = "UPDATE `".$this->BalanceTable."` SET `balance`=balance".$iAmount.", `draw_time`=draw_time+1,`utime`= '" . date("Y-m-d H:i:s",time()) . "' WHERE `payport_id`=".$this->PaySlotId." AND `acc_id`=".$this->AId." AND `thread_id`=".$iThreadId;
        		}
        		else{
        			$sSql = "UPDATE `".$this->BalanceTable."` SET `balance`=balance+".$iAmount.", `load_time`=load_time+1,`utime`= '" . date("Y-m-d H:i:s",time()) . "' WHERE `payport_id`=".$this->PaySlotId." AND `acc_id`=".$this->AId." AND `thread_id`=".$iThreadId;
        		}
        		    	
        	}
        	
     		$this->oDB->doTransaction();
        	$this->oDB->query($sSql);
        	if( $this->oDB->errno() > 0 ){
        		$this->oDB->doRollback();
        		return false;
        		
        	}
        	else{
        		
        		// 记录修正操作日志,仅后台管理时
        		if ($sType){
        			
        			$sLogs = '转帐记录';
	        		if ( $this->saveBalanceLogs( array('text'=>$sLogs, 'amount'=>$iAmount ), false ) === false){
	        			$this->oDB->doRollback();
	        			return false;
	        		}
	        		
	        		//更新 $this->Balance
	        		if ( $this->getBalance() === false){
	        			$this->oDB->doRollback();
	        			return false;
	        		}
	        		
        		}
        		
        		//记录到转帐记录表
    			$oDeposit = new model_deposit_recordinfo();
    			$this->DepositValue['outbalance'] = isset($this->DepositValue['outbalance']) ? $this->DepositValue['outbalance'] : "";
    			$this->DepositValue['remark'] = isset($this->DepositValue['remark']) ? $this->DepositValue['remark'] : "";
    			$this->DepositValue['opuser'] = isset($this->DepositValue['opuser']) ? $this->DepositValue['opuser'] : "";
    			if ( $this->DepositValue['outbalance'] > 0)
    			{
    				$oDeposit->OutAmount  = floatval( $this->DepositValue['outbalance'] );
    				$oDeposit->BackCharge = floatval( $this->DepositValue['bankcharge'] );
    			}
    			else
    			{
    				$oDeposit->InAmount = floatval( $this->DepositValue['inbalance'] );
    			}
    			
    			$oDeposit->PpId   = intval($this->DepositValue['ppid']);
    			$oDeposit->AccId  = intval($this->DepositValue['accid']);
    			$oDeposit->ReMark = $this->DepositValue['remark'];
    			
    			//传入 opuser 有值时，认为是后台操作
    			if ( $this->DepositValue['opuser'] )
    			{
    				$oDeposit->IsOP   = 1;
    				$oDeposit->OpUser = $this->DepositValue['opuser'];
    			}
    			//传入 userid 有值时，认为是前台用户操作行为
    			if ( $this->DepositValue['userid'] )
    			{
    				$oDeposit->IsOP   = 0;
    				$oDeposit->UserId = $this->DepositValue['userid'];
    			}
    				
    			if ( $oDeposit->add() <= 0 )
    			{
	    			$this->oDB->doRollback();
	    			return false;
    			}
    		
        		$this->oDB->doCommit();
        		return true;
        		
        	}
        	
        }
        else{
        	
        	return false;
        }
		
	}
	
	
	/**
	 *  更新余额,仅供其他方法、程序调用
	 * 		(不含事务)
	 *
	 * @param $iAmount	涉及金额
	 *
	 * @return bool
	 */
	public function saveBalanceReceive($iAmount=0){
		if ( $iAmount <= 0 ) return false;
		$iThreadId = intval( substr($this->oDB->getThreadId(), -1) ) % 5;
		
		$sSql = "UPDATE `".$this->BalanceTable."` SET `balance`=balance+".$iAmount.",`load_time`=load_time+1,`utime`= '" . date("Y-m-d H:i:s", time()) . "' WHERE `payport_id`=".$this->PaySlotId." AND `acc_id`=".$this->AId." AND `thread_id`=".$iThreadId;
		$this->oDB->query($sSql);
		if ($this->oDB->errno() > 0){
			return false;
		}
		
		//记录到转帐记录表, 普通用户充值也当作一次转入操作(仅后台查询转帐操作记录时不显示详情)
    	$oDeposit = new model_deposit_recordinfo();
    	$oDeposit->InAmount = floatval( $this->DepositValue['inbalance'] );
    	$oDeposit->PpId 	= intval($this->DepositValue['ppid']);
    	$oDeposit->AccId 	= intval($this->DepositValue['accid']);
    	$this->DepositValue['remark'] = isset($this->DepositValue['remark']) ? $this->DepositValue['remark'] : "";
    	$oDeposit->ReMark 	= $this->DepositValue['remark'];
    			
    	//传入 opuser 有值时，认为是后台操作 (默认不应当有值:操作者名称)
    	$this->DepositValue['opuser'] = isset($this->DepositValue['opuser']) ? $this->DepositValue['opuser'] : "";
    	if ( $this->DepositValue['opuser'] )
    	{
    		$oDeposit->IsOP   = 1;
    		$oDeposit->OpUser = $this->DepositValue['opuser'];
    	}
    	$this->DepositValue['userid'] = isset($this->DepositValue['userid']) ? $this->DepositValue['userid'] : "";
    	//传入 userid 有值时，认为是前台用户操作行为
    	if ( $this->DepositValue['userid'] )
    	{
    		$oDeposit->IsOP	  = 0;
    		$oDeposit->UserId = $this->DepositValue['userid'];
    	}
    				
    	if ( $oDeposit->add() <= 0 )
    	{
	    	return false;
    	}
    	else
    	{
        	return true;
    	}
    	
        		
	}
	
	/**
	 * 记录 balance操作日志
	 *
	 * @param  array   $aLogs(text/日志内容 amount 涉及金额)
	 * @param bool $bSilent 是否安静模式记录,(默认安静)
	 */
	public function saveBalanceLogs($aLogs,$bSilent=true){
		
		if (!is_array($aLogs) ) return false;
		
		$sAdminId 	= !empty($_SESSION['admin']) 		? 	$_SESSION['admin'] 		: 	 '99999';
		$sAdminName = !empty($_SESSION['adminname']) 	? 	$_SESSION['adminname'] 	:	 'CLI';
		
		$aTmp = array(
			'userid' 	=> $sAdminId,
			'username' 	=> $sAdminName,
			'payport_id'	=> $this->PaySlotId,
			'payport_acc_id'=> $this->AId,
			'logs' 		=> mysql_escape_string( $aLogs['text'] ),
			'logtime' 	=> date('Y-m-d H:i:s'),
			'amount'	=> floatval($aLogs['amount']),
			'utime' 	=> date('Y-m-d H:i:s')
		);
		
		if ( $this->checkArrayValue($aTmp, array() ) ){
			//以安静模式或有返回
			if (!$bSilent){
				if ( $this->oDB->insert($this->BalanceLogTable,$aTmp) !== false ){
					return true;
				}else{
					return false;
				}
			}else{
				$this->oDB->insert($this->BalanceLogTable,$aTmp);
			}
			
		}
		else{
			return false;
		}
		
	}
	
	
	/**
	 * 获取余额统计,并更新到 分账户表
	 *
	 * @param  $bReturn 默认:false, 可选:true 返回合值数组 array(balance,totalload,totaldraw)
	 */
	public function getBalance($bReturn=false){
		$sSql = "SELECT sum(balance) AS totalbalance,sum(load_time) AS totalload, sum(draw_time) AS totaldraw FROM `$this->BalanceTable` WHERE `payport_id`=$this->PaySlotId AND `acc_id`=$this->AId";
		$fTtl = $this->oDB->getOne($sSql);
		$this->Balance = $fTtl['totalbalance'];
		$this->TotalLoad = $fTtl['totalload'];
		$this->TotalDraw = $fTtl['totaldraw'];
		
		//更新到分账户表相应字段
		$aTempData = array(
				'balance' 		=> floatval( $this->Balance ),
				'total_load' 	=> intval( $this->TotalLoad ),
				'total_draw' 	=> intval( $this->TotalDraw ),
				'utime' 		=> date('Y-m-d H:i:s')
				);
				
		if ($bReturn === true) return $aTempData;
			
		//统计转帐记录表，记录到分账户表 转入 转出列
        	$oDeposit = new model_deposit_recordinfo();
        	$oDeposit->PpId = intval( $this->PaySlotId );
        	$oDeposit->AccId = intval( $this->AId );
        	$aTotal = $oDeposit->getTotal();

        	if ( $aTotal['totalin'] > 0 )
        	{
        		$aTempData['inbalance'] = floatval( $aTotal['totalin'] );
        	}
        
			if ( $aTotal['totalout'] > 0 )
        	{
        		$aTempData['outbalance'] = floatval( $aTotal['totalout'] ) + floatval( $aTotal['totalcharge']);
        	}
		
		$sCond = ' aid = '.$this->AId;
        $this->oDB->update( $this->TableName, $aTempData, $sCond );
        if ($this->oDB->errno() > 0){
        	return FALSE;
        }else{
        	return TRUE;
        }
        
        
	}
	
	
	/**
	 * 启用账户
	 * (分账户设置总开关，与用户使用权限无关)
	 *
	 * @return bool
	 */
	public function enable(){
		
		//开启事务
		$this->oDB->doTransaction();
		
		// 重新激活已保留的所有账户
		//冗余 重新激活
			$oDeposit = new model_deposit_companycard();
			$aResult = $oDeposit->reduces($this->AId,1,'normal');
			if ( $aResult === FALSE )
			{
				$this->oDB->doRollback();
            	return false;
			}
			
			$aResult = $oDeposit->reduces($this->AId,1,'vip');
			if ( $aResult === FALSE )
			{
				$this->oDB->doRollback();
            	return false;
			}
			
			$aResult = $oDeposit->reduces($this->AId,1,'black');
			if ( $aResult === FALSE )
			{
				$this->oDB->doRollback();
            	return false;
			}
			
					
		// 设置分账户Opentime
		if ( $this->markOpentime() === false){
			$this->oDB->doRollback();
			return false;
		}
		
		$sSql2 = $this->_setIsEnable( intval('1'), 'sql' );
		$this->oDB->query($sSql2);
			if( $this->oDB->errno() > 0 ){
				$this->oDB->doRollback();
            	return false;
        	}else{
        		
        		$this->oDB->doCommit();
        		return true;
        	}
        	
	}
	
	
	/**
	 * 禁用账户
	 * (分账户设置总开关，与用户使用权限无关)
	 *
	 * @return bool
	 */
	public function disable(){
		//检查如已被开放，则禁用此功能
		/*if ($this->OpenTime == 0){
			return $this->_setIsEnable( intval('0') );
		}else{
			return false;
		}*/
		
		//开启事务处理
		$this->oDB->doTransaction();
		
		//禁用, 保留所有已激活的关系为9
		
			//冗余控制 (新EMAIL充值模式下，控制 user_deposit_card 用户卡关系表)
			$oDeposit = new model_deposit_companycard();
			$aResult = $oDeposit->reduces($this->AId,0,'normal');
			if ( $aResult === FALSE )
			{
				$this->oDB->doRollback();
            	return false;
			}
			
			$aResult = $oDeposit->reduces($this->AId,0,'black');
			if ( $aResult === FALSE )
			{
				$this->oDB->doRollback();
            	return false;
			}
			
			$aResult = $oDeposit->reduces($this->AId,0,'vip');
			if ( $aResult === FALSE )
			{
				$this->oDB->doRollback();
            	return false;
			}
				
		//无条件 可使用禁用
		$sSql2 = $this->_setIsEnable( intval('0'), 'sql' );
		$this->oDB->query($sSql2);
		if( $this->oDB->errno() > 0 ){
			$this->oDB->doRollback();
           	return false;
        }else{
        	$this->oDB->doCommit();
        	return true;
        }
        
	}
	
	
	/**
	 * 禁 启分账户实际操作
	 *
	 * @param int $iValue
	 * @return bool
	 */
	private function _setIsEnable($iValue,$sType=NULL){
		if ( ($this->AId) && is_numeric($iValue) ){
				// 字段 status ，作0=不启用  作1=启用  2=删除   9=保留激活状态
			$sSql = 'UPDATE `'.$this->TableName.'` SET `isenable`='.$iValue.', `utime`=\''.date('Y-m-d H:i:s').'\' WHERE `aid`='.$this->AId;
			if ($sType !== NULL) return $sSql;
			
			$this->oDB->query($sSql);
		
			if( $this->oDB->ar() && $this->oDB->errno() <= 0 )
				{
					return true;
				}else{
					return false;
				}
		}else{
			return false;
		}
	}
	
	
	/**
	 * ICONV 函数
	 *
	 * @param string $str	待转换的字符
	 * @param string $tag	转换方向,默认out为从UTF-8转为 $this->LangCode字符， 否则为从$this->LangCode 字符转换为utf-8
	 *
	 * @return string
	 */
	public function myIconv($str,$tag='out'){
		if ( strlen($str) <= 0 ) return $str;
		$this->LangCode = strtoupper($this->LangCode);

		$aStandCode = array('ASCII', 'BIG-5','BIG5','ISO-8859-1','UNICODE', 'UTF-8', 'UTF-16', 'UTF8', 'UTF16','CN-BIG5','CN-GB', 'CN','GB', 'GB2312', 'GB13000', 'GB18030', 'GBK');
		//检查 LangCode 符合标准规则
		if ( array_search($this->LangCode,$aStandCode) === false) return $str;
		
		if ( ($this->LangCode == 'UTF-8') || ($this->LangCode == 'UTF8') ) return $str;
		
		if ($tag == 'out')
		{
			return iconv('UTF-8',$this->LangCode,$str);
		}
		else
		{
			return iconv($this->LangCode,'UTF-8',$str);
		}
		
	}
	
	
	/**
	 * 删除一个分账户
	 * (仅使用逻辑删除)
	 *
	 * @return bool
	 */
	public function delete(){
		
		$this->oDB->doTransaction();
		
		//擦除已被配置的卡信息
			$oDeposit = new model_deposit_companycard();
			$aUpDate = array(
						'accname' => $this->AccName,
						'accid' => $this->AId,
						'name' => $this->AccIdent,
						'mail' => $this->AccMail
						);
						
				//修改 普通卡列
			$aResult = $oDeposit->reduceUpdate('delnormal',$aUpDate);
			if ( $aResult !== TRUE )
			{
				$this->oDB->doRollback();
				return FALSE;
			}
				//修改 黑名单信息列
			$aResult = $oDeposit->reduceUpdate('delblack',$aUpDate);
			if ( $aResult !== TRUE )
			{
				$this->oDB->doRollback();
				return FALSE;
			}
				//修改 VIP信息列
			$aResult = $oDeposit->reduceUpdate('delvip',$aUpDate);
			if ( $aResult !== TRUE )
			{
				$this->oDB->doRollback();
				return FALSE;
			}
			
				
		//删除自己 (逻辑删除)
		$sSql2 = $this->_setIsEnable( intval('2') , 'sql');
		$this->oDB->query($sSql2);
		if ($this->oDB->errno() > 0){
			$this->oDB->doRollback();
			return false;
			
		}else{
			$this->oDB->doCommit();
			return true;
			
		}
		
	}
	
	
	/*** 单独使用方法 ***/
	
	/**
	 * 检测一组ID是否有效  (未用)
	 *
	 * @param array $aId 一组account id,
	 *
	 * @return array() 处于激活状态的account id
	 */
	public function multiCheckIsEnable($aId){
		if( !is_array($aId) ) return false;
		
		foreach ($aId AS $Id){
			$sOrWhere .= ' `aid`='.$Id.' OR';
		}
		$sAndWhere = ' AND `isenable`=1';
		$sSql = 'SELECT aid FROM '.$this->TableName.' WHERE ('.substr($sOrWhere,0,-2).')'.$sAndWhere;
		$aResult = $this->oDB->getAll($sSql);
		
	}
	
	/*  class end  */
}