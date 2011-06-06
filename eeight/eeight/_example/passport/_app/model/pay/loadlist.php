<?php
/**
 *  
 * 充值详情列表类
 * 	--多条信息  显示
 * 
 * @name LoadList.php
 * @package payport
 * @version 0.1 3/9/2010
 * @author Jim
 * 
**/

class model_pay_loadlist extends model_pay_base_list 
{
	/**
	 * 内容类名
	 * @var string
	 */
	protected $InfoClsName='model_pay_loadinfo';
	/**
	 * 返回方式 object
	 * @var string
	 */
	protected $InfoType='object';
	
	protected $ExtendMode=false;
	
	private $TableName='online_load';
	
	/**
	 * 默认列表输出
	 */
	public function __construct($aParam=array(),$sTable='',$sInfoType='array',$sJoin=false){
		$this->InfoType = $sInfoType;
		$sTableAlias = $sJoin ? 'll' : '';
		!$sTable or $this->TableName = $sTable;
		parent::__construct($this->TableName,'id',$aParam['PageSize'],$sTableAlias);
		$this->_formatParam($aParam);
		extract($aParam,EXTR_PREFIX_ALL,'r');
		
		$sSqlWhere = '1';
		(!$r_LoadStartTime && !$r_LoadEndTime) or $sSqlWhere .= " AND trans_time >='$r_LoadStartTime' AND trans_time <= '$r_LoadEndTime'";
		(!$r_LostStartTime && !$r_LostEndTime) or $sSqlWhere .= " AND lost_todo_time >='$r_LostStartTime' AND lost_todo_time <= '$r_LostEndTime'";
		!$r_UserId or $sSqlWhere .= " AND user_id =$r_UserId";
		!$r_UserName or $sSqlWhere .= " AND user_name = '$r_UserName'";
		!$r_LoadType or $sSqlWhere .= " AND load_type =$r_LoadType";
		//!$r_LoadAmount or $sSqlWhere .= " AND load_amount >= '$r_LoadAmount' AND load_amount <= '$r_LoadAmount'";
		!$r_LoadCurrency or $sSqlWhere .= " AND load_currency = '$r_LoadCurrency'";
		($r_LoadStatus < 0) or $sSqlWhere .= " AND load_status = '$r_LoadStatus'";
		!$r_LostTodo or $sSqlWhere .= " AND lost_todo ='$r_LostTodo'";
		//		!$r_LostStatus  or $sSqlWhere .= " AND lost_status = '$r_LostStatus'";
		$sFields = '*';
		if ($sJoin){
			$sJoinCond = 'll.load_type = pp.id';
			$sFields = 'pp.payport_name,pp.payport_attr,ll.*';
			$this->setJoin('payport_set','pp',$sJoinCond);
		}
		$this->FindAll($sSqlWhere,$sFields,$aParam['Page'],'id',true);
	}
	
	
	/**
	 * 格式化检查查询参数
	 * @param array $aParam
	 */
	private function _formatParam(&$aParam=array()){
		if (!isset($aParam['PageSize'])) $aParam['PageSize'] = false;
		if (!isset($aParam['Page'])) $aParam['Page'] = false;
		if (!isset($aParam['UserId'])) $aParam['UserId'] = false;
		if (!isset($aParam['UserName'])) $aParam['UserName'] = false;
		if (!isset($aParam['LoadType'])) $aParam['LoadType'] = false;
		if (!isset($aParam['LoadAmount'])) $aParam['LoadAmount'] = false;
		if (!isset($aParam['LoadCurrency'])) $aParam['LoadCurrency'] = false;
		if (!isset($aParam['LoadStatus'])) $aParam['LoadStatus'] = -1;
		if (!isset($aParam['LostTodo'])) $aParam['LostTodo'] = false;
		if (!isset($aParam['LostStatus'])) $aParam['LostStatus'] = false;
		if (!isset($aParam['LoadStartTime'])) $aParam['LoadStartTime'] = false;
		if (!isset($aParam['LoadEndTime'])) $aParam['LoadEndTime'] = false;
		if (!isset($aParam['LostStartTime'])) $aParam['LostStartTime'] = false;
		if (!isset($aParam['LostEndTime'])) $aParam['LostEndTime'] = false;
		if (!isset($aParam['Desc'])) $aParam['Desc'] = false;
		if (!isset($aParam['SkipCount'])) $aParam['SkipCount'] = false;
		
			$aParam['PageSize'] or $aParam['PageSize'] = 20;// DEFAULT_PAGESIZE;
			isset($aParam['Page']) or $aParam['Page'] = 1;
			$aParam['UserId'] or $aParam['UserId'] = 0;
			$aParam['UserName'] or $aParam['UserName'] = 0;
			$aParam['LoadType'] or $aParam['LoadType'] = 0;
			$aParam['LoadAmount'] or $aParam['LoadAmount'] = 0;
			$aParam['LoadCurrency'] or $aParam['LoadCurrency'] = 0;
			(isset($aParam['LoadStatus'])&&($aParam['LoadStatus']!= -1)) or $aParam['LoadStatus'] = -1;
			$aParam['LostTodo'] or $aParam['LostTodo'] = 0;
			$aParam['LostStatus'] or $aParam['LostStatus'] = 0;
			$aParam['LoadStartTime'] or $aParam['LoadStartTime'] = 0;
			$aParam['LoadEndTime'] or $aParam['LoadEndTime'] = 0;
			$aParam['LostStartTime'] or $aParam['LostStartTime'] = 0;
			$aParam['LostEndTime'] or $aParam['LostEndTime'] = 0;
			$aParam['Desc'] or $aParam['Desc'] = True;
			$aParam['SkipCount'] or $aParam['SkipCount'] = 0;
	}
	

	/**
	 * 为结果整理附加权限
	 * @param string $sType  关键字， 1:load,2:draw,4:drawlist,8:ques,16:drawhand
	 * 
	 */
	public function finishAttr($sType=false){
	
		foreach ($this->Data AS &$aLoadData){
        	$sValue = $aLoadData['payport_attr'];
        	
        		$aLoadData['payport_attr_load'] = ($sValue & 1);
        		$aLoadData['payport_attr_draw'] = ($sValue & 2);
        		$aLoadData['payport_attr_drawlist'] = ($sValue & 4);
        		$aLoadData['payport_attr_ques'] =($sValue & 8);
        		$aLoadData['payport_attr_drawhand'] = ($sValue & 16);
        		switch ($sType){
        			case 'ques':
        				unset($aLoadData['payport_attr_load'],$aLoadData['payport_attr_draw'],$aLoadData['payport_attr_drawlist'],$aLoadData['payport_attr_drawhand']);
        				break;
        			case 'load':
        				unset($aLoadData['payport_attr_draw'],$aLoadData['payport_attr_drawlist'],$aLoadData['payport_attr_drawhand'],$aLoadData['payport_attr_ques']);
        				break;
        			case 'draw':
        				unset($aLoadData['payport_attr_load'],$aLoadData['payport_attr_drawlist'],$aLoadData['payport_attr_drawhand'],$aLoadData['payport_attr_ques']);
        				break;
        			case 'drawlist':
        				unset($aLoadData['payport_attr_load'],$aLoadData['payport_attr_draw'],$aLoadData['payport_attr_drawhand'],$aLoadData['payport_attr_ques']);
        				break;
        			case 'drawhand':
        				unset($aLoadData['payport_attr_load'],$aLoadData['payport_attr_draw'],$aLoadData['payport_attr_drawlist'],$aLoadData['payport_attr_ques']);
        				break;
        			default:
        				break;
        		}

		} //end foreach
		
	}
	
	
	/* 单独使用方法 */
	
	
	/********** Class End **********/
}