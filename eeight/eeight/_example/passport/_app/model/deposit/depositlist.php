<?php
/**
 *
 * 支付银行列表类
 * 	--对多条信息 显示
 * 
 * @name	depositList.php
 * @package deposit
 * @version 0.1 11/16/2010
 * @author	Jim
 * 
**/
//if(!defined('DEFAULT_PAGESIZE')) define('DEFAULT_PAGESIZE',20);

class model_deposit_depositlist extends model_pay_base_list  
{
	
	protected $InfoClsName	= 'model_deposit_depositinfo';	
	protected $InfoType		= 'object';
	protected $ExtendMode	= false;
	
	private $TableName='deposit_set';
	
	/**
	 * 默认数据列表
	 * @param string $ReturnType
	 * @return array();
	 */
	public function __construct($aParam=array(),$sTable='',$sInfoType='object',$bIntro=false,$sPos=NULL){
		$this->InfoType = $sInfoType;
		if ($sTable) $this->TableName = $sTable;
		parent::__construct($this->TableName,'id',20);
		//if (!isset($aParam['IdArray'])) $aParam['IdArray'] = 0;
		if ( count($aParam['IdArray']) > 0 ) {
			$aParam['IdArray'] = array_unique($aParam['IdArray']);
			$sArrayIdStr = '';
			foreach ( $aParam['IdArray'] AS $aId){
				if ( is_numeric($aId) ) $sArrayIdStr .= $aId.',';
			}
			$sArrayIdStr = substr($sArrayIdStr,0,-1);
		}
		$this->_formatParam($aParam);
		extract($aParam,EXTR_PREFIX_ALL,'r');
		$sSqlWhere = '1';
		!$r_LoadStatus or $sSqlWhere .= " AND `status`='$r_LoadStatus'";
		!$r_IdArray or $sSqlWhere .= " AND FIND_IN_SET (`id`,'".$sArrayIdStr."')";
		
		//$sFields = 'id,payport_name,payport_nickname,load_time_note,draw_time_note,load_limit_min_per,load_limit_max_per,load_fee_step,draw_limit_min_per,draw_limit_max_per,draw_fee_per,draw_fee_percent,draw_fee_min,draw_fee_max,draw_fee_step,total_balance,opt_limit_times,status,payport_attr';
		$sFields = '*';
		//if ($bIntro === true ) $sFields .= ',payport_intro';
		
		$sSql = "SELECT ".$sFields." FROM ".$this->TableName." WHERE ".$sSqlWhere;
		$this->FindAll($sSqlWhere,$sFields);
		//$this->Data[] = new $this->InfoClsName($aData[$this->KeyField],$this->ExtraInfo);
		if ($sPos != NULL){
			
			$aTmpResult = $this->Data;
			//重组返回数组,以指定 $sPos字段名 做为新数组的KEY
			$aNewTemp = array();
			foreach ($aTmpResult AS $aArr){
				if ( array_key_exists($sPos, $aArr) ) $aNewTemp[ $aArr[$sPos] ] = $aArr;
			}
			$this->Data = $aNewTemp;
		}
	}
	
	
	/**
	 * 格式化查询条件
	 *@return array()
	 */
	private function _formatParam(&$aParam=array()){

		if (!isset($aParam['PageSize'])) $aParam['PageSize'] = false;
		if (!isset($aParam['Page'])) $aParam['Page'] = false;
		if (!isset($aParam['LoadStatus'])) $aParam['LoadStatus'] = false;
		if (!isset($aParam['Desc'])) $aParam['Desc'] = false;
		if (!isset($aParam['IdArray'])) $aParam['IdArray'] = false;
		
			$aParam['PageSize'] or $aParam['PageSize'] = DEFAULT_PAGESIZE;
			isset($aParam['Page']) or $aParam['Page'] = 1;
			$aParam['LoadStatus'] or $aParam['LoadStatus'] = 0;
			$aParam['Desc'] or $aParam['Desc'] = false;
			$aParam['IdArray'] or $aParam['IdArray'] = false;
	}
	
	/**
	 * 提取系统中使用的受付银行
	 * 根据参数返回需要的序列
	 * 
	 */
	public function getDepositArray($sType='id')
	{
		$aDepositBankList = $this->Data;
		foreach ( $aDepositBankList AS $iK => $sV )
		{
			$aBankArray[$iK]['bankid'] = $sV['id'];
			$aBankArray[$iK]['bankname'] = $sV['payport_nickname'];
			$aBankIdArray[] = $sV['id'];
			$aBanknameArray[$sV['id']] = $sV['payport_nickname'];
			$aBankPortArray[$sV['id']] = $sV['payport_name'];
		}
		
		if ( $sType == 'id')
		{
			// 返回满足条件的id组成的数组
			return $aBankIdArray;
		}
		else if ( $sType == 'port')
		{
			return $aBankPortArray;
		}
		else
		{
			// 返回前述组装的三个数组
			return array($aBankIdArray, $aBanknameArray, $aBankArray,$aBankPortArray);
		}
		
	}
	
	/**
	 * 转换货币字符 编码->文字
	 *
	 * @param $s 	编码
	 * @param $tag	目标文字
	 * @return string
	 */
	public function converCurrencyStr($s,$tag='cn'){
		$oP = new model_deposit_depositinfo();
		return $oP->getCurrencyStr($s,$tag);
	}
	/**
	 * 货币单位 编码->文字
	 *
	 * @param $s 	编码
	 * @param $tag	目标文字
	 * @return string
	 */
	public function converUnitStr($s,$tag='cn'){
		$oP = new model_deposit_depositinfo();
		return $oP->getUnitStr($s,$tag);
	}

	/*  class end  */
}