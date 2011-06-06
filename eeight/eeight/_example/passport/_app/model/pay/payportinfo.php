<?php
/**
 *   
 * 支付平台类
 * 	--对单条信息 	增 删 改 显示
 * 
 * @name PayportInfo.php
 * @package payport
 * @version 0.1  3/9/2010
 * @author Jim
 * 
 **/

class model_pay_payportinfo extends model_pay_base_info
{
	/**
	 * 接口ID
	 * @var int
	 */
	public $Id=NULL;
	/**
	 * 接口名称	
	 * @var string
	 */
	public $PayportName;
	/**
	 * 匿名名称(显示给客户)
	 * @var string
	 */
	public $PayportNickname;
	/**
	 * 货币单位
	 * @var INT
	 */
	public $Currency;
	/**
	 * 充值到帐时间说明
	 * @var string
	 */
	public $LoadTimeNote;
	/**
	 * 提现时间说明
	 * @var string
	 */
	public $DrawTimeNote;
	/**
	 * 单次最低充值额	
	 * @var float
	 */
	public $LoadLimitMinPer;
	/**
	 * 单次最高充值额
	 * @var float
	 */
	public $LoadLimitMaxPer;
	/**
	 * 充值手续费(每次)下限
	 * @var int
	 */
	public $LoadFeePerDown;
	/**
	 * 充值手续费(百分比)下限
	 * @var int
	 */
	public $LoadFeePercentDown;
	/**
	 * 充值手续费 上限
	 * @var int
	 */
	public $LoadFeePerUp;
	/**
	 * 充值手续费(百分比)上限
	 * @var int
	 */
	public $LoadFeePercentUp;
	/**
	 * 充值手续费计算界定金额
	 * @var int
	 */
	public $LoadFeeStep;
	
	/**
	 * 单次最低提现额
	 * @var float
	 */
	public $DrawLimitMinPer;
	/**
	 * 单次最高提现额
	 * @var float
	 */
	public $DrawLimitMaxPer;
	/**
	 * 提现按次手续费(下限)
	 * @var float
	 */
	public $DrawFeePerDown;
	/**
	 * 提现按金额手续费百分比(下限)
	 * @var float
	 */
	public $DrawFeePercentDown;
	/**
	 * 提现最低手续费
	 * @var float
	 */
	public $DrawFeeMin;
	/**
	 * 提现最高手续费
	 * @var float
	 */
	public $DrawFeeMax;
	/**
	 * 界定金额
	 * @var float
	 */
	public $DrawFeeStep;
	/**
	 * 提现按次手续费(上限)
	 * @var float
	 */
	public $DrawFeePerUp;
	/**
	 * 提现按金额手续费百分比 (上限)
	 * @var float
	 */
	public $DrawFeePercentUp;
	/**
	 * 支付接口手续费百分比(充值)
	 * @var float
	 */
	public $PlatLoadPercent;
	/**
	 * 支付接口手续费 下限(充值)
	 * @var float
	 */
	public $PlatLoadMin;
	/**
	 * 支付接口手续费 上限(充值)
	 * @var float
	 */
	public $PlatLoadMax;
	/**
	 * 支付接口手续费百分比(提现)
	 * @var float
	 */
	public $PlatDrawPercent;
	/**
	 * 支付接口手续费 下限(提现)
	 * @var float
	 */
	public $PlatDrawMin;
	/**
	 * 支付接口手续费 上限(提现)
	 * @var float
	 */
	public $PlatDrawMax;
	/**
	 * 各分账户累计流量
	 * @var int
	 */
	public $TotalBalance;
	/**
	 * 操作次数限制
	 * @var int
	 */
	public $OptLimitTimes;
	/**
	 * 接口域名
	 * @var string
	 */
	public $PayportHost;
	/**
	 * 接口充值URL
	 * @var string
	 */
	public $PayportUrlLoad;
	/**
	 * 接口提现URL
	 * @var string
	 */
	public $PayportUrlDraw;
	/**
	 * 接口查询URL
	 * @var string
	 */
	public $PayportUrlQues;
	/**
	 * 接收返回域名
	 * @var string
	 */
	public $ReceiveHost;
	/**
	 * 接收返回URL
	 * @var string
	 */
	public $ReceiveUrl;
	/**
	 * 持续等待接收返回URL
	 * @var string
	 */
	public $ReceiveUrlKeep;
	/**
	 * 是否启用
	 * @var int
	 *  (默认为0 1=启用)
	 */
	public $Status;
	/**
	 * 支付接口描述
	 * @var string
	 */
	public $PayportIntro;
	/**
	 * 该接口所要求的语言编码,依支付平台手册确定 
	 * @var string 
	 * 默认UTF-8, 
	 *  可能值 gb2312 gbk
	 */
	public $LangCode;
	/**
	 * 功能配置(程序运行用)
	 * @var int
	 */
	public $PayportAttr;
	
	// 由 PayportAttr 生成的属性
	public $PayportAttrLoad;
	public $PayportAttrDraw;
	public $PayportAttrDrawlist;
	public $PayportAttrQues;
	public $PayportAttrDrawhand;
	
	/**
	 * 手续费计算方式
	 *
	 * @var int (0,1)  0 外扣法/1内扣法
	 */
	public $PayDeduct;

	public $OptType;			// 操作方式标记, 用于减少SELECT返回数量

	private $TableName 		 = 'payport_set';	//Db table name
	
	private $PayAccountTable = 'payport_acc_set';
	
	/**
	 * 获取信息
	 * 
	 * @param int $Keys
	 * @param string $KeyMark	(PP_ID=id,PP_NAME=payport_name,PP_NICKNAME=payport_nickname)
	 * @param string $sOptType	(onlineload充值,withdraw提现)
	 * 
	 * @return array();
	 */
	public function __construct(){
		parent::__construct($this->TableName);
	}
	
	
	/**
	 * 获取数据
	 *
	 * @param int $iId
	 * @param string $sOptType (onlineload充值,withdraw提现)
	 * @param int $iStatus
	 * @param bool $bShowIntro
	 * @return unknown
	 */
	public function getPayportData($iId, $sOptType='onlineload', $iStatus='-1', $bShowIntro=false){
			if(!is_numeric($iId) ) return false;
			$this->Id = $iId;
			$this->OptType = !$this->OptType ? $sOptType : $this->OptType;
			$this->Status = $iStatus;
			
			switch ($this->OptType){
				case 'onlineload':
					// 充值时的返回字段
					$sField = 'load_limit_min_per,load_limit_max_per,load_fee_per_down,load_fee_percent_down,load_fee_step,load_fee_per_up,load_fee_percent_up,plat_load_percent,plat_load_min,plat_load_max,payport_host,payport_url_load,payport_url_draw,payport_url_ques,receive_host,receive_url,receive_url_keep,status,lang_code,payport_attr';
					break;
				case 'withdraw':
					// 提现时的返回字段
					$sField = 'draw_limit_min_per,draw_limit_max_per,draw_fee_per_down,draw_fee_percent_down,draw_fee_min,draw_fee_max,draw_fee_step,draw_fee_per_up,draw_fee_percent_up,plat_draw_percent,plat_draw_min,plat_draw_max,payport_host,payport_url_draw,payport_url_ques,receive_host,receive_url,receive_url_keep,status,lang_code,payport_attr';
					break;
				case 'query':
					// 查询时的返回字段
					$sField = 'payport_host,payport_url_load,payport_url_draw,payport_url_ques,receive_host,receive_url,receive_url_keep,status,lang_code,payport_attr';
					break;
				case 'list':
					// 用于前后台列表
					$sField = 'id,payport_name,payport_nickname,currency,load_limit_min_per,load_limit_max_per,draw_limit_min_per,draw_limit_max_per,draw_fee_step,draw_fee_per,draw_fee_percent,draw_fee_min,draw_fee_max,draw_fee_step,status,lang_code,payport_attr';
					break;
				case 'intro':
					// 用于前台显示简介
					$sField = 'id,payport_name,payport_nickname,currency,payport_intro,status';
					break;
				default:
					// 用于后台修改
					$sField = '*';
					break;	
			}
			
			
			$sAddsqlWhere = ($this->Status == '-1')  ? '' : ' and `status` = '.$this->Status;
			$sSql = 'SELECT '.$sField.' FROM `'.$this->TableName.'` WHERE `id`='.$this->Id.$sAddsqlWhere;
			$aTmpResult = $this->oDB->getOne($sSql);
			
			//TODO 未处理 \‘ 回显
			//$this->Id or $this->Id = intval($aTmpResult['id']);
			$this->PayportName 		= trim($aTmpResult['payport_name']);
			$this->PayportNickname 	= trim($aTmpResult['payport_nickname']);
			$this->Currency 		= $aTmpResult['currency'];
			$this->LoadTimeNote 	= $aTmpResult['load_time_note'];
			$this->DrawTimeNote 	= $aTmpResult['draw_time_note'];
			$this->LoadLimitMinPer 	= $aTmpResult['load_limit_min_per'];
			$this->LoadLimitMaxPer 	= $aTmpResult['load_limit_max_per'];
			$this->LoadFeePerDown 	= $aTmpResult['load_fee_per_down'];
			$this->LoadFeePercentDown 	= $aTmpResult['load_fee_percent_down'];
			$this->LoadFeeStep 		= $aTmpResult['load_fee_step'];
			$this->LoadFeePerUp 	= $aTmpResult['load_fee_per_up'];
			$this->LoadFeePercentUp = $aTmpResult['load_fee_percent_up'];
			$this->DrawLimitMinPer 	= $aTmpResult['draw_limit_min_per'];
			$this->DrawLimitMaxPer 	= $aTmpResult['draw_limit_max_per'];
			$this->DrawFeePerDown 	= $aTmpResult['draw_fee_per_down'];
			$this->DrawFeePercentDown 	= $aTmpResult['draw_fee_percent_down'];
			$this->DrawFeeMin 		= $aTmpResult['draw_fee_min'];
			$this->DrawFeeMax 		= $aTmpResult['draw_fee_max'];
			$this->DrawFeeStep 		= $aTmpResult['draw_fee_step'];
			$this->DrawFeePerUp 	= $aTmpResult['draw_fee_per_up'];
			$this->DrawFeePercentUp = $aTmpResult['draw_fee_percent_up'];
			$this->PlatLoadPercent 	= $aTmpResult['plat_load_percent'];
			$this->PlatLoadMin 	= $aTmpResult['plat_load_min'];
			$this->PlatLoadMax 	= $aTmpResult['plat_load_max'];
			$this->PlatDrawPercent = $aTmpResult['plat_draw_percent'];
			$this->PlatDrawMin 	= $aTmpResult['plat_draw_min'];
			$this->PlatDrawMax 	= $aTmpResult['plat_draw_max'];
			$this->TotalBalance 	= $aTmpResult['total_balance'];
			$this->OptLimitTimes	= $aTmpResult['opt_limit_times'];
			$this->PayportHost 		= trim($aTmpResult['payport_host']);
			$this->PayportUrlLoad 	= trim($aTmpResult['payport_url_load']);
			$this->PayportUrlDraw 	= trim($aTmpResult['payport_url_draw']);
			$this->PayportUrlQues 	= trim($aTmpResult['payport_url_ques']);
			$this->ReceiveHost 		= trim($aTmpResult['receive_host']);
			$this->ReceiveUrl 		= trim($aTmpResult['receive_url']);
			$this->ReceiveUrlKeep 	= trim($aTmpResult['receive_url_keep']);
			$this->Status 			= $aTmpResult['status'];
			$this->PayportIntro 	= $bShowIntro ? $aTmpResult['payport_intro'] : '';
			$this->LangCode 		= $aTmpResult['lang_code'];
			$this->PayportAttr 		= $aTmpResult['payport_attr'];
			// API功能属性赋值
			$this->PayportAttrLoad = ($this->PayportAttr & 1);
			$this->PayportAttrDraw = ($this->PayportAttr & 2);
			$this->PayportAttrDrawlist = ($this->PayportAttr & 4);
			$this->PayportAttrQues = ($this->PayportAttr & 8);
			$this->PayportAttrDrawhand = ($this->PayportAttr & 16);
			
	}
	
	
	/**
	 * 设置值,Update
	 * 
	 *@return bool
	 */
	public function set(){
		//整理数据
		$iPayportAttr = $this->PayportAttr ?  intval($this->PayportAttr)  : intval($this->PayportAttrDraw + $this->PayportAttrDrawlist + $this->PayportAttrLoad + $this->PayportAttrQues + $this->PayportAttrDrawhand);
		
		$this->oDB->doTransaction();
		//更新冗余 , `ads_payport_name`='".$this->PayportName."'
		$sSql = "UPDATE `payport_acc_set` SET `acc_attr`=".$iPayportAttr.", `utime`='".date('Y-m-d H:i:s')."' WHERE `ads_payport_id` =".$this->Id;
		$this->oDB->query($sSql);
		if ($this->oDB->errno() > 0 ){
			$this->oDB->doRollback();
			return false;
		}
		$aTmpDate = array(
				//'payport_name' 	=> $this->PayportName, 		//不提供程序用名称修改
				'payport_nickname' 	=> $this->PayportNickname,
				'payport_host' 		=> $this->PayportHost,
				'payport_url_load' 	=> $this->PayportUrlLoad,
				'payport_url_draw' 	=> $this->PayportUrlDraw,
				'payport_url_ques' 	=> $this->PayportUrlQues,
				'receive_host' 		=> $this->ReceiveHost,
				'receive_url' 		=> $this->ReceiveUrl,
				'receive_url_keep' 	=> $this->ReceiveUrlKeep,
				'payport_intro' 	=> $this->PayportIntro,
				'lang_code' 		=> $this->LangCode,
				'payport_attr' 		=> $this->PayportAttr,
				'utime' => date('Y-m-d H:i:s')
			);
			
		//return $this->_save($aTmpDate);
		if ( $this->checkArrayValue($aTmpDate, 
			array('payport_host', 'payport_url_load', 'payport_url_draw','payport_url_ques','receive_url_keep','lang_code') 
		) ){			
			$bResult =  $this->_save($aTmpDate);
			if ($bResult === false){
				$this->oDB->doRollback();
				return false;
			}else{
				$this->oDB->doCommit();
				return true;
			}
		}else{
			$this->oDB->doRollback();
			return false;
		}
	}
	
	
	/**
	 * 保存充提参数
	 *
	 * @return bool
	 */
	public function setlimit(){
		
		$this->oDB->doTransaction();
		//更新 payport_acc_set 表冗余字段 
		$sSql = "UPDATE `payport_acc_set` SET `acc_currency`='".$this->Currency."', `utime`='".date('Y-m-d H:i:s')."' WHERE `ads_payport_id` =".$this->Id;
		$this->oDB->query($sSql);
		if ( $this->oDB->errno() ){
			$this->oDB->doRollback();
			return false;
		}
		$aTmpDate = array(
				'currency' 				=> $this->Currency,
				'load_time_note' 		=> $this->LoadTimeNote,
				'draw_time_note' 		=> $this->DrawTimeNote,
				'load_limit_min_per' 	=> $this->LoadLimitMinPer,
				'load_limit_max_per' 	=> $this->LoadLimitMaxPer,
				'load_fee_per_down'		=> $this->LoadFeePerDown,
				'load_fee_percent_down' => $this->LoadFeePercentDown,
				'load_fee_step' 		=> $this->LoadFeeStep,
				'load_fee_per_up'		=> $this->LoadFeePerUp,
				'load_fee_percent_up' 	=> $this->LoadFeePercentUp,
				'draw_limit_min_per' 	=> $this->DrawLimitMinPer,
				'draw_limit_max_per' 	=> $this->DrawLimitMaxPer,
				'draw_fee_per_down' 	=> $this->DrawFeePerDown,
				'draw_fee_percent_down' => $this->DrawFeePercentDown,
				'draw_fee_min' 		=> $this->DrawFeeMin,
				'draw_fee_max' 		=> $this->DrawFeeMax,
				'draw_fee_step' 	=> $this->DrawFeeStep,
				'draw_fee_per_up' 	=> $this->DrawFeePerUp,
				'draw_fee_percent_up' 	=> $this->DrawFeePercentUp,
				'plat_load_percent' 	=> $this->PlatLoadPercent,
				'plat_load_min' 	=> $this->PlatLoadMin,
				'plat_load_max' 	=> $this->PlatLoadMax,
				'plat_draw_percent' => $this->PlatDrawPercent,
				'plat_draw_min' 	=> $this->PlatDrawMin,
				'plat_draw_max' 	=> $this->PlatDrawMax,
				'opt_limit_times' 	=> $this->OptLimitTimes,
				'utime' => date('Y-m-d H:i:s')
			);
		if ( $this->checkArrayValue($aTmpDate, array('currency'),false ) ){			
			$bResult =  $this->_save($aTmpDate);
			if ($bResult === false){
				$this->oDB->doRollback();
				return false;
			}else{
				$this->oDB->doCommit();
				return true;
			}
		}else{
			$this->oDB->doRollback();
			return false;
		}
	}
	
	
	/**
	 *  更新,保存数据操作
	 */
	private function _save($aTmpDate){
		if (!$aTmpDate) return false;
		$sCond = ' id = '.$this->Id;
		$this->oDB->update($this->TableName,$aTmpDate,$sCond);
		
		if ( $this->oDB->errno() > 0){
			return false;
		}else{
			$this->refTotalBalance();
			return true;
		}
		
	}
	

	/**
	 * 刷新支付账户总余额
	 * @param $sReturn 是否返回结果
	 */
	public function refTotalBalance($bReturn=false){
		$sSql = "SELECT sum(balance) AS totalbalance FROM ".$this->PayAccountTable." WHERE `ads_payport_id`=".$this->Id;
		$fTtl = $this->oDB->getOne($sSql);
		if ($fTtl['totalbalance'] != 0){
			$this->TotalBalance = $fTtl['totalbalance'];
			//更新到payport支付接口表
			$aTempData = array( 
				'total_balance' => floatval( $this->TotalBalance ),
				'utime' => date('Y-m-d H:i:s')
				);
			
			$sCond = ' id = '.$this->Id;
        	$this->oDB->update( $this->TableName, $aTempData, $sCond );
		}
		
        if ($bReturn) return ($fTtl['totalbalance'] != 0 )  ?  number_format($fTtl['totalbalance'],'2','.','')  :  0; 
        
	}
	
	/**
	 * 获取该支付接口下所有分账户ID
	 * return array
	 */
	public function getMyChlidAcc(){
		
		$sSql = 'SELECT `aid` FROM '.$this->PayAccountTable.' WHERE `ads_payport_id`='.$this->Id;
		return $this->oDB->getAll($sSql);
		
	}
	
	/**
	 * 获取数组形式结果
	 *
	 * @return array
	 */
	public function getArrayData(){
		$aTmpArray = array(
    			'id' => $this->Id,
    			'payport_name' 		=> $this->PayportName,
				'payport_nickname' 	=> $this->PayportNickname,
				'currency' 			=> $this->Currency,
				'load_time_note' 	=> $this->LoadTimeNote,
				'draw_time_note' 	=> $this->DrawTimeNote,
				'load_limit_min_per' 	=> $this->LoadLimitMinPer,
				'load_limit_max_per' 	=> $this->LoadLimitMaxPer,
				'load_fee_per_down'		=> $this->LoadFeePerDown,
				'load_fee_percent_down' => $this->LoadFeePercentDown,
				'load_fee_step' 	=> $this->LoadFeeStep,
				'load_fee_per_up' 	=> $this->LoadFeePerUp,
				'load_fee_percent_up' 	=> $this->LoadFeePercentUp,
				'draw_limit_min_per' 	=> $this->DrawLimitMinPer,
				'draw_limit_max_per' 	=> $this->DrawLimitMaxPer,
				'draw_fee_per_down' 	=> $this->DrawFeePerDown,
				'draw_fee_percent_down' => $this->DrawFeePercentDown,
				'draw_fee_min' 		=> $this->DrawFeeMin,
				'draw_fee_max' 	 	=> $this->DrawFeeMax,
				'draw_fee_step' 	=> $this->DrawFeeStep,
				'draw_fee_per_up' 	=> $this->DrawFeePerUp,
				'draw_fee_percent_up' 	=> $this->DrawFeePercentUp,
				'plat_load_percent' => $this->PlatLoadPercent,
				'plat_load_min' 	=> $this->PlatLoadMin,
				'plat_load_max' 	=> $this->PlatLoadMax,
				'plat_draw_percent' => $this->PlatDrawPercent,
				'plat_draw_min' 	=> $this->PlatDrawMin,
				'plat_draw_max' 	=> $this->PlatDrawMax,
				'total_balance' 	=> $this->TotalBalance,
				'opt_limit_times' 	=> $this->OptLimitTimes,
				'payport_host' 		=> $this->PayportHost,
				'payport_url_load' 	=> $this->PayportUrlLoad,
				'payport_url_draw' 	=> $this->PayportUrlDraw,
				'payport_url_ques' 	=> $this->PayportUrlQues,
				'receive_host' 		=> $this->ReceiveHost,
				'receive_url' 		=> $this->ReceiveUrl,
				'receive_url_keep' 	=> $this->ReceiveUrlKeep,
				'status' 			=> $this->Status,
				'payport_intro' 	=> $this->PayportIntro,
				'lang_code' 		=> $this->LangCode,
				'payport_attr_load' => ($this->PayportAttr & 1),
				'payport_attr_draw' => ($this->PayportAttr & 2),
				'payport_attr_drawlist' => ($this->PayportAttr & 4),
				'payport_attr_ques' 	=> ($this->PayportAttr & 8),
				'payport_attr_drawhand' => ($this->PayportAttr & 16)
			);
			return $aTmpArray;
	}
	
	
	/**
	 * 新加值
	 * @return int (last id)
	 */
	public function add(){
		//插入数据
		$aTempData = array( 
				'payport_name' 	=> $this->PayportName,
				'payport_nickname' 	 => $this->PayportNickname,
				'currency' 			 => $this->Currency,
				'load_time_note' 	 => $this->LoadTimeNote,
				'draw_time_note' 	 => $this->DrawTimeNote,
				'load_limit_min_per' => $this->LoadLimitMinPer,
				'load_limit_max_per' => $this->LoadLimitMaxPer,
				'load_fee_per_down'	 => $this->LoadFeePerDown,
				'load_fee_percent_down' => $this->LoadFeePercentDown,
				'load_fee_step' 	 => $this->LoadFeeStep,
				'load_fee_per_up'	 => $this->LoadFeePerUp,
				'load_fee_percent_up' => $this->LoadFeePercentUp,
				'draw_limit_min_per'  => $this->DrawLimitMinPer,
				'draw_limit_max_per'  => $this->DrawLimitMaxPer,
				'draw_fee_per_down'   => $this->DrawFeePerDown,
				'draw_fee_percent_down' => $this->DrawFeePercentDown,
				'draw_fee_min' 		=> $this->DrawFeeMin,
				'draw_fee_max' 		=> $this->DrawFeeMax,
				'draw_fee_step' 	=> $this->DrawFeeStep,
				'draw_fee_per_up' 	=> $this->DrawFeePerUp,
				'draw_fee_percent_up' => $this->DrawFeePercentUp,
				'plat_load_percent' => $this->PlatLoadPercent,
				'plat_load_min' 	=> $this->PlatLoadMin,
				'plat_load_max' 	=> $this->PlatLoadMax,
				'plat_draw_percent' => $this->PlatDrawPercent,
				'plat_draw_min' 	=> $this->PlatDrawMin,
				'plat_draw_max' 	=> $this->PlatDrawMax,
				'total_balance' 	=> $this->TotalBalance,
				'opt_limit_times' 	=> $this->OptLimitTimes,
				'payport_host' 		=> $this->PayportHost,
				'payport_url_load'  => $this->PayportUrlLoad,
				'payport_url_draw'  => $this->PayportUrlDraw,
				'payport_url_ques'  => $this->PayportUrlQues,
				'receive_host' 		=> $this->ReceiveHost,
				'receive_url' 		=> $this->ReceiveUrl,
				'receive_url_keep' 	=> $this->ReceiveUrlKeep,
				'status' 		=> $this->Status,
				'payport_intro' => $this->PayportIntro,
				'lang_code' 	=> $this->LangCode,
				'payport_attr' 	=> $this->PayportAttr,
				'utime' 		=> date('Y-m-d H:i:s')
             );
        if ( empty($this->PayportName) ){
        	return -1;
        }
        else{
        	$this->oDB->insert( $this->TableName, $aTempData );
        	if( $this->oDB->affectedRows() < 1 ){
            	return -1;
        	}
        	else{
        		return 1;
        	}
        }
        
	}
	
	
	/**
	 * 删除单条记录
	 * @return bool
	 */
	public function erase(){
		//delete by ID
		
		//事务
		$this->oDB->doTransaction();
		
		// 逻辑删除所有分账户，将所有关系取消
		$oPayAcclist = new model_pay_payaccountlist();
		$sSql = $oPayAcclist->multidelete($this->Id,'sql');
		$this->oDB->query($sSql);
		
		if ($this->oDB->errno() > 0){
				$this->oDB->doRollback();
				return false;
		}
		if ( $this->PayportName != 'mdeposit')
		{
			// 删除涉及关系
			$oPAlimit = new model_pay_payaccountlimit();
			$sSql2 = $oPAlimit->rudundance( array('del_ppid',$this->Id), 'sql' );
			$this->oDB->query($sSql2);
		
			if ($this->oDB->errno() > 0){	
				$this->oDB->doRollback();
				return false;
			}
		}
		else 
		{
			//TODO: 删除支持
		}
		
		
		//逻辑删除  只有传入2时返回sql
		$sSql3 = $this->_setStatus('2');
		$this->oDB->query($sSql3);
		
		if ($this->oDB->errno() > 0){
			$this->oDB->doRollback();
			return false;
		}else{
			$this->oDB->doCommit();
			return true;
		}
		
	}
	
	
	/**
	 * 启用
	 *@return bool
	 */
	public function enable(){
		
		return $this->_setStatus('1');
		
	}
	
	
	/**
	 * 禁用接口
	 *@return bool
	 */
	public function disable(){
		
		return $this->_setStatus('0');

	}

	
	/**
	 * 接口启用禁止 操作
	 *
	 * @param int 	$iValue
	 * @return string
	 */
	private function _setStatus($iValue){
		
		if ( (intval($iValue) == 0) || (intval($iValue) == 1) )
		{
		
			$this->oDB->doTransaction();
			if ( $this->PayportName != 'mdeposit')
			{
				// 在线充值关系表
				$oPAlimit = new model_pay_payaccountlimit();
				$sActKey = $iValue ? 're_ppid' : 'save_ppid';
				$sSql = $oPAlimit->rudundance( array($sActKey, $this->Id ),'sql');
				$this->oDB->query($sSql);
				if($this->oDB->error()){
					$this->oDB->doRollback();
					return false;
				}
			}
			else 
			{
				// EMAIL充值关系表
				//获取子账户ID列表
				$aAcc = $this->getMyChlidAcc();
				if ( count($aAcc) < 1 )
				{
					$this->oDB->doRollback();
					return FALSE;
				}
				$oDeposit = new model_deposit_companycard();
				foreach ($aAcc AS $aA)
				{
					$aTmp['accid'] = $aA['aid'];
					$aResult = FALSE;
					$aResult = $oDeposit->reduces($aTmp['accid'],$iValue,'normal');
					if ( $aResult === FALSE )
					{
						$this->oDB->doRollback();
            			return false;
					}
					$aResult = $oDeposit->reduces($aTmp['accid'],$iValue,'black');
					if ( $aResult === FALSE )
					{
						$this->oDB->doRollback();
            			return false;
					}
					$aResult = $oDeposit->reduces($aTmp['accid'],$iValue,'vip');
					if ( $aResult === FALSE )
					{
						$this->oDB->doRollback();
            			return false;
					}
				}
				
				
			}
			
			
			
			// 分账户表
			/*为0时 所有为1的改为9
			为1时 所有为9的改为1*/
			if ($iValue > 0){
				$iAccOldStatus = 9;
				$iAccStatus = 1;
			}
			else{
				$iAccOldStatus = 1;
				$iAccStatus = 9;
			}
			
			$sSql1 = 'UPDATE `payport_acc_set` SET `isenable`='.intval($iAccStatus).', `utime`=\''.date('Y-m-d H:i:s').'\' WHERE `isenable`='.intval($iAccOldStatus).' AND `ads_payport_id`='.intval($this->Id);
			$this->oDB->query($sSql1);
			
			if($this->oDB->error()){
				$this->oDB->doRollback();
				return false;
			}
			
			// 支付接口表
			$sSql2 = 'UPDATE `'.$this->TableName.'` SET `status`='.intval($iValue).', `utime`=\''.date('Y-m-d H:i:s').'\' WHERE `id`='.intval($this->Id);
			if ( intval($iValue) == 2) return $sSql2;
			$this->oDB->query($sSql2);
			
			if($this->oDB->error()){
				$this->oDB->doRollback();
				return false;
			}
			else{
				$this->oDB->doCommit();
				return true;
			}
		
		}
		
		if (intval($iValue) == 2){
			return 'UPDATE `'.$this->TableName.'` SET `status`='.intval($iValue).', `utime`=\''.date('Y-m-d H:i:s').'\' WHERE `id`='.intval($this->Id);
		}
		
		
	}
	

	/**
	 * 手续费计算
	 * 
	 * @param int $iAmount 金额
	 * @param str $sOptType 提现/充值
	 * @return array(扣除手续费之后的金额,手续费)
	 * // TODO: 待得到确定的计算公式 完善之
	 */
	/*
	$this->LoadFeePerDown
	$this->LoadFeePercentDown
	$this->LoadFeeStep
	$this->LoadFeePerUp
	$this->LoadFeePercentUp

	$this->DrawLimitMinPer
	$this->DrawLimitMaxPer
	$this->DrawFeePerDown
	$this->DrawFeePercentDown
	$this->DrawFeeMin
	$this->DrawFeeMax
	$this->DrawFeeStep
	$this->DrawFeePerUp
	$this->DrawFeePercentUp
	*/
	
	public function paymentFee($iAmount,$bAjax=false){
		
		if (!isset($this->OptType)) return array(0,0);
		
		if ($this->OptType == 'withdraw'){

			if (  intval($iAmount) <=  intval($this->DrawFeeStep) ){
				$iFee = $iAmount * $this->DrawFeePercentDown + $this->DrawFeePerDown;
			}else{
				$iFee = $iAmount * $this->DrawFeePercentUp + $this->DrawFeePerUp;
			}
			
			if ($iFee < $this->DrawFeeMin ) $iFee = $this->DrawFeeMin;
			if ($iFee > $this->DrawFeeMax ) $iFee = $this->DrawFeeMax;
			
		}elseif($this->OptType == 'onlineload'){
				
			if (  intval($iAmount) <=  intval($this->LoadFeeStep) ){
				$iFee = $iAmount * $this->LoadFeePercentDown + $this->LoadFeePerDown;
			}else{
				$iFee = $iAmount * $this->LoadFeePercentUp + $this->LoadFeePerUp;
			}
			
		}else{
			return array($iAmount,0);
		}
		$iFee = number_format($iFee, 2, '.', '');
		
		if ($this->PayDeduct){
			//内扣法
			$iRealAmount = number_format( ($iAmount - $iFee), 2, '.', '');
		}else{
			//外扣法
			$iRealAmount = number_format( ($iAmount + $iFee), 2, '.', '');
		}
		if ($bAjax){
			echo $iRealAmount.','.$iFee;
		}else{
			return array($iRealAmount,$iFee);
		}
	}
	
	
	/**
	 * 第三方支付接口 收取手续费
	 */
	public function payportFee($iAmount,$bAjax=false){
		if (!isset($this->OptType)) return false;
		$aR = array();
		if ($this->OptType == 'withdraw'){
			$iPer = $this->PlatDrawPercent;
			$iMin = $this->PlatDrawMin;
			$iMax = $this->PlatDrawMax;
			
		}elseif($this->OptType == 'onlineload'){
			$iPer = $this->PlatLoadPercent;
			$iMin = $this->PlatLoadMin;
			$iMax = $this->PlatLoadMax;
			
		}else{
			$aR = array(number_format( $iAmount, 2, '.', ''), 0);
		}
		
		// 如果设置限定有设定值 则按公式计算
		if ( ($iPer > 0) && ($iMax > 0) ){
			$iFee = $iAmount * $iPer;
			$iFee =  ($iFee < $iMin) ? $iMin : $iFee;
			$iFee =  ($iFee > $iMax) ? $iMax : $iFee;
			
			$iRealAmount = number_format( ($iAmount - $iFee), 2, '.', '');
			$iFee = number_format( $iFee, 2, '.', '');
			
			$aR = array($iRealAmount, $iFee );
		}
		
		unset($iPer,$iMax,$iMin);
		
		if ($bAjax){
			echo $iRealAmount.','.$iFee;
			unset($aR);
		}else{
			return $aR;
		}
	}
	
	/**
	 * 数字转换为中文大写
	 *
	 * @param int/fool $rmb
	 * @return string
	 */
	public function NumtoRMB($rmb){ 
   		//把数字金额转换成中文大写数字的函数
   		$rmb=str_replace(",","",$rmb); 		//格式化类似1,000,000的金额
		if (!ereg("^[0-9.]",$rmb))	return $rmb;
		$arr1 = array('零','壹','贰','叁','肆','伍','陆','柒','捌','玖');
		$arr2 = array('拾','佰','仟');
		$arr = explode(".",$rmb);
		$rmb_len=strlen($arr[0]); //整数部分
		$j=0;
		for ($i=0;$i<$rmb_len;$i++){
  			$bit=$arr[0][$rmb_len-$i-1];
  			$cn=$arr1[$bit];
  			$unit=$arr2[$j];
    		if ($i==0) {
    			$re=$cn;
    		}
    		elseif ($i==4){
    			$re=$cn."万".$re;
    			$j=0;
 			}
 			elseif ($i==8) {
    			$re=$cn."亿".$re;
    			$j=0;
 			}
 			else{
 				$j++;
 				$re=$bit==0 ? "零".$re : $cn.$unit.$re;
 			}
  		}
		if ($arr[1]){
			$arr[1][0]==0 ? $re=$re."元零" : $re=$re."元".$arr1[$arr[1][0]]."角"; //角
			$arr[1][1]==0 ? $re=$re."零分" : $re=$re.$arr1[$arr[1][1]]."分";      //分
		}

		$re=preg_replace(array("/(零)+$/","/(零)+/","/零万/","/零亿/"),array("","零","万","亿"),$re);
		$arr[1]?$re:$re.="元整";
		return $re;
   }
   
	/**
	 * 转换货币字符显示
	 *@param $sSource  原字符 (ISO4217)
	 *@param $sTag 目标字符  (cn english)  etc.big5 kroan
	 */
	public function getCurrencyStr($sSource,$sTag='cn'){
		/*
		 参考数据数组定义形式
		 iso4217 => array( number cn eng )
		 */
		$aCurrency = array(
			'USD' => array(840,'美元','US Dollar'),
			'EUR' => array(978,'欧元','euro'),
			'CAD' => array(124,'加拿大元','Canadian Dollar'),
			'CNY' => array(156,'人民币','Yuan Renminbi'),
			'HKD' => array(344,'港元','Hong Kong Dollar'),
			'AUD' => array(036,'澳大利亚元','Australian Dollar'),
			'MYR' => array(458,'马来西亚林吉特','Malaysian Ringgit'),
			'FRF' => array(250,'法国法郎','French Franc'),
			'PHP' => array(608,'菲律宾比索','Philippine Peso'),
			'THB' => array(764,'泰铢','Baht'),
			'GBP' => array(826,'英镑','Pound Sterling'),
			'JPY' => array(392,'日元','Yen'),
			'SGD' => array(702,'新加坡元','Singapore Dollar')
		);
		$aCurrencyKey = array_keys($aCurrency);
		$ikey = array_search(strtoupper($sSource),$aCurrencyKey);
		
		if ($ikey === false) return $sSource;
		
		if ($sTag == 'cn'){
			 return $aCurrency[$aCurrencyKey[$ikey]][1];			
		}elseif($sTag == 'eng'){
			 return $aCurrency[$aCurrencyKey[$ikey]][2];			
		}else{
			return $sSource;
		}
		
	}
	
	/**
	 * 获取对应币种的计量单位
	 *
	 * @param string $sSource	货币编码
	 * @param string $sTag		目标文字
	 * @return string
	 */
	public function getUnitStr($sSource,$sTag='cn'){
	
		$aCurrency = array(
			'USD' => array('元','Dollar'),
			'EUR' => array('元','euro'),
			'CAD' => array('元','Dollar'),
			'CNY' => array('元','Yuan'),
			'HKD' => array('元','Dollar'),
			'AUD' => array('元','Dollar'),
			'MYR' => array('林吉特','Ringgit'),
			'FRF' => array('法郎','Franc'),
			'PHP' => array('比索','Peso'),
			'THB' => array('泰铢','Baht'),
			'GBP' => array('镑','Pound'),
			'JPY' => array('元','Yen'),
			'SGD' => array('元','Dollar')
		);
		$aCurrencyKey = array_keys($aCurrency);
		$ikey = array_search(strtoupper($sSource),$aCurrencyKey);
		
		if ($ikey === false) return $sSource;
		
		if ($sTag == 'cn'){
			 return $aCurrency[$aCurrencyKey[$ikey]][0];			
		}elseif($sTag == 'eng'){
			 return $aCurrency[$aCurrencyKey[$ikey]][1];			
		}else{
			return $sSource;
		}
		
	}
	
	/******** 可直接使用的方法 *******/
	
	/**
	 * 获取payport ID
	 *	by name
	 * @param str 查询关键字
	 * @param str 数据表字段标记名 默认 name为payport_name
	 */
	public function getId($sKey,$sName='payport_name'){
		if (empty($sKey)) return false;
		$sSql = "SELECT `id` FROM ".$this->TableName." WHERE `".$sName."` = '".$sKey."'";
		return $this->oDB->getOne($sSql);
	}
	
	
	/**
	 * 检测数组变量，每个键值有效
	 * 	
	 * @param array $aArray 被检查的数组
	 * @param array	$aZero  本次检查中允许为空、为0的键名数组
	 */
	public function checkArrayValue($aArray,$aZero){
		
		if ( empty($aArray) && !is_array($aZero) ) return false;
		
		foreach ($aArray AS $aKey => $aVal){
        	if (  empty($aVal) && ( array_search($aKey,$aZero) === false) ){
        		return false;
        		break;
        	}
        }
        return true;
	}
	
	
   /*  class end  */
}