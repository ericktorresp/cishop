<?php
/**
 * 支付接口 分账户列表类
 * 
 * 
 * 列出某一支付接口所有的分账户列表
 * 列出所有分账户列表
 * 
 * @name	PayAccountList.php
 * @package payport
 * @version 0.1 3/31/2010
 * @author	Jim
 * 
 */
class model_pay_payaccountlist extends model_pay_payportlist  
{
	/**
	 * 支付接口ID  
	 * @var int
	 */
	public $PayportId;
	
	
	private $TableName = 'payport_acc_set';
	
	/**
	 *
	 */
	public function __construct($aPayportId=array(),$sPos=NULL){
		if (   ( is_array($aPayportId) || is_numeric($aPayportId)  ) 
				&& (!empty($aPayportId) ) 
			){
			$aParam = array('IdArray' => $aPayportId);	
		}else{
			$aParam = array();
		}
		parent::__construct($aParam,'','array',false,$sPos);
	}
	
	
	/**
	 * 列出某一给定支付接口ID下面所有的分账户信息列表
	 *
	 * @param int/array $iPPid  		支付接口ID  为 array时为传入一组id, id类型由第三参数决定
	 * @param bool 		$bIsEnable		是否只是已激活的分账户
	 * @param string	$sParam			用到WHERE子句的数据表字段名
	 * @param string	$sPos			返回的数组以哪个数据为键名,默认:aid
	 * 
	 * @return array() 所有分账户数据信息数组
	 */
	public function singleList($iPPid=false, $bIsEnable=true, $sParam=NULL, $sPos=NULL){
		$sSqlWhere = '1';
		$sKeyname  = $sParam ? $sParam : 'aid'; 
		if ( is_array($iPPid)  && count($iPPid) >= 1 ){
			//兼容以数组形式提交的银行卡号 acc_siteid 查询一个列表
			if ( $sKeyname == 'acc_siteid')
			{
				$aSiteId 	= array_values($iPPid);
				$sSqlWhere .= " AND (";
				$sORSql 	= '';
				$ii			= 0;
				foreach ($aSiteId AS  $sSiteId)
				{
					if ( strlen($sSiteId) > 2 )
					{
						if ( $ii > 0 ) $sORSql .= ' OR ';
					 	$sORSql .= "`acc_siteid` = '$sSiteId'";
						$ii++;
					}
				}
				unset($ii);
				$sSqlWhere .= $sORSql; //implode(' OR ',"`acc_siteid` = '$sSiteId'");
				$sSqlWhere .= ")";
				
			}
			else 
			{
				//默认的数组形式Aid
				$aAccId 	= array_values($iPPid);
				$sSqlWhere .= " AND `".$sKeyname."` IN (". implode(',',$aAccId) . ") ";
			}
		}
		elseif( is_string($iPPid) )
		{
			!$iPPid or $sSqlWhere .= " AND `$sKeyname`= '".$iPPid."'";
		}
		else{
			!$iPPid or $sSqlWhere .= " AND `ads_payport_id`= '".$iPPid."'";
		}
		
		!$bIsEnable or $sSqlWhere .= " AND `isenable`=1";
		$sFields  = is_numeric($iPPid) ? 'aid,acc_name,acc_attr,acc_currency,srcbalance,inbalance,outbalance,balance,balance_limit,ads_payport_id,reg_time,valid_time,open_time,isenable' : '*';
		
		$sSql 	 = "SELECT ".$sFields." FROM ".$this->TableName." WHERE ".$sSqlWhere;
		$aReTemp = $this->oDB->getAll($sSql);
		
		if ( $sPos == NULL ){
			return  $aReTemp;
		}else{
			//重组返回数组,以指定 $sPos字段名 做为新数组的KEY
			$aNewTemp = array();
			foreach ($aReTemp AS $aArr){
				if ( array_key_exists($sPos, $aArr) ) $aNewTemp[ $aArr[$sPos] ] = $aArr;
			}
			return $aNewTemp;
		}
	}
	
	
	/**
	 * 传入ID数组,传回所有的列表资料 (前台调用)
	 *
	 * @param array $aId
	 * @param bool	$bIsenable	是否激活标记
	 * @param string $sParam  	标记数组为何种字段
	 * @param string $sPos		返回数组中特殊要求的KEY定义
	 * 
	 * @return 依 $this->singleList() 确定
	 */
	public function multiList($aId,$bIsenable=true,$sParam='aid',$sPos='aid'){
		return $this->singleList($aId,$bIsenable,$sParam,$sPos);
	}
	

	/**
	 * 返回含支付接口与分账户的完整信息
	 *
	 * @param array $aId 		ID数组
	 * @param bool	$bIsenable	是否带接口介绍信息
	 * @param array $sParam 	$aId搜索字段名
	 * @param array $sPos		返回数组的KEY值
	 * @param int 	$iPri		权限参数: 充值1 提现2 批量提现4 查询8 人工提现16
	 * 
	 * @return  array
	 */
	public function specialList($aId,$bIsenable=true,$sParam='aid',$sPos='aid',$iPri=NULL){
		//组装 array payport 与 payaccount,依对应关系
		$aPPTmp = $this->Data;
		$aPATmp = $this->multiList($aId,$bIsenable,$sParam,$sPos);
		
		//循环Payacoount
		$aNewTempArr = array();
		foreach ($aPATmp AS $aPA){
			if ( array_key_exists($aPA['ads_payport_id'], $aPPTmp )){
				//获取币种中文显示
				unset($sCurr,$aCurrencyCN);
				$sCurr =  (($aPA['acc_currency'] != $aPPTmp[$aPA['ads_payport_id']]['currency'] ) 
					&& (strlen($aPA['acc_currency'])==3) )
					? $aPA['acc_currency']
					: $aPPTmp[$aPA['ads_payport_id']]['currency'];
				$aCurrencyCN['currencycn'] = $this->converCurrencyStr( strtoupper($sCurr),'cn');
				
				if ($iPri != NULL ){
					//如有带API功能参数,检查分账户权限优先支付接口权限
					$iAttr = ($aPA['acc_attr'] != $aPPTmp[$aPA['ads_payport_id']]['payport_attr'] )
							?  intval($aPA['acc_attr'])
							:  intval($aPPTmp[$aPA['ads_payport_id']]['payport_attr']);
					if ($iAttr & $iPri ){ 
						$aNewTempArr[$aPA['ads_payport_id']] = array_merge( $aPA, $aPPTmp[$aPA['ads_payport_id']],$aCurrencyCN );
						//排序
						ksort($aNewTempArr[$aPA['ads_payport_id']]);
					}
				}else{
					//默认无权限检查方式
					$aNewTempArr[$aPA['ads_payport_id']] = array_merge( $aPA, $aPPTmp[$aPA['ads_payport_id']],$aCurrencyCN );
					ksort($aNewTempArr[$aPA['ads_payport_id']]);
				}
				
				
			}
				
		}
		return $aNewTempArr;
	}
	
	
	/**
	 * 列出所有分账户信息列表
	 *
	 * @return array() 所有信息
	 */
	public function allList($bIsEnable=true, $bFields=false){
		$sSqlWhere = '1';

		$sFields1 = '*';
		$sFields2 = 'aid,acc_name,acc_currency,ads_payport_id,ads_payport_name,isenable';
		$sFields  =  $bFields ? $sFields2 : $sFields1;
		
		!$bIsEnable or $sSqlWhere .= " AND `isenable`=1";
		$sOrderby = ' Order by `acc_name` ASC';
		
		$sSql = "SELECT ".$sFields." FROM ".$this->TableName." WHERE ".$sSqlWhere.$sOrderby;
		return $this->oDB->getAll($sSql);
		
	}
	
	
	/**
	 * 用于满足 支付接口后台管理的 禁用功能
	 *
	 * @param int $iPPid
	 * @param int $iIsenable
	 * @return bool
	 */
	public function disable($iPPid,$iIsenable=0){
		// 将已激活的分账户状态置为9,   未激活、已删除的不处理

		$sSql = "UPDATE `".$this->TableName."` SET `isenable`=".$iIsenable.", `utime`='".date('Y-m-d H:i:s')."' WHERE `ads_payport_id`=".$iPPid." AND `isenable`=1";
		$re1  = $this->oDB->query($sSql);
	   	//关系表中
       	$oPayAccLimit = new model_pay_payaccountlimit();
       	$re2 = $oPayAccLimit->disable($iPPid);
        
       	//不严格的返回值
        return ($re1 || $re2);
	}
	
	
	/**
	 * 逻辑删除 同接口的分账户
	 *
	 * @param int $iPPid
	 */
	public function multidelete($iPPid,$sType){
		if (!is_numeric($iPPid)) return false;
		$sSql = "UPDATE `".$this->TableName."` SET `isenable`=2, `utime`='".date('Y-m-d H:i:s')."' WHERE `ads_payport_id`=".$iPPid;
		if ($sType == 'sql'){
			return $sSql;
		}else{
			return $this->oDB->query($sSql);
		}
	}
	
	
	/**
	 * 用于满足 支付接口后台管理的 禁用功能
	 *
	 * @param int $iPPid
	 * @param int $iIsenable
	 * @return bool
	 */
	public function enable($iPPid,$iIsenable=0){
		// 分账户表  将保留激活状态9的分账户重新激活
		$sSql = "UPDATE `".$this->TableName."` SET `isenable`=1, `utime`='".date('Y-m-d H:i:s')."' WHERE `ads_payport_id`=".$iPPid." AND `isenable`=".$iIsenable;
		$re1  = $this->oDB->query($sSql);
		
	   	// 关系表 将保留的激活9 重新激活 
       	$oPayAccLimits = new model_pay_payaccountlimit();
       	$re2 = $oPayAccLimits->rudundance( array('re_ppid',$iPPid) );
        
        return ($re1 || $re2);
	}
	
	
	/*  class end  */
}