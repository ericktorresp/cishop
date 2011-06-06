<?php
/**
 * 检查充值记录是否有重复
 *
 * @name 		repeatload.php
 * @version 	v1.0	2010-06-05 
 * @author 		louis
 */
class model_pay_repeatload extends model_pay_base_info 
{
	
	/**
	 * 充值记录状态串，可能查询多种状态的记录，状态值用逗号分隔
	 *
	 * @var unknown_type
	 */
	public $StatusList;
	
	/**
	 * 账变类型串,可以查询多种状态的账变记录，账变类型用户逗号分隔
	 *
	 * @var unknown_type
	 */
	public $OrderTypeList;
	
	/**
	 * 查询开始时间
	 *
	 * @var datetime
	 */
	public $StartTime;
	
	/**
	 * 查询结束时间
	 *
	 * @var datetime
	 */
	public $EndTime;
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 获取重复充值记录
	 *
	 * @version 	v1.0	2010-06-05
	 * @author 		louis
	 */
	public function getRecord(){
		$aResult = array();
		$aAllResult = array();
		if (empty($this->StartTime) && empty($this->EndTime)) return $aAllResult;
		// 获取指定时间范围内的充值记录
		$aResult = $this->_getLoadList();
		if (!empty($aResult)){
			// 查询充值记录对应账变表中的记录个数，如果大于1条就返回记录
			$aAllResult = $this->_getOrderInfo($aResult);
		}
		return $aAllResult;
	}
	
	
	/**
	 * 获取指定时间内的充值记录
	 *
	 * @version 	v1.0	2010-06-05
	 * @author 		louis 
	 * 
	 * @return 		array
	 */
	private function _getLoadList(){
		$aResult = array();
		$sWhere  = "";
		
		// 组合状态查询条件
		if (!empty($this->StatusList))
		$sWhere .= " AND load_status IN ({$this->StatusList})";
		
		// 组合时间查询条件
		if (!empty($this->StartTime) && empty($this->EndTime))
			$sWhere .= " AND trans_time > '{$this->StartTime}'";
		if (empty($this->StartTime) && !empty($this->EndTime))
			$sWhere .= " AND trans_time < '{$this->EndTime}'";
		if (!empty($this->StartTime) && !empty($this->EndTime))
			$sWhere .= " AND trans_time BETWEEN '{$this->StartTime}' AND '{$this->EndTime}'";
			
		$sSql = "SELECT id,spec_name,acc_name FROM online_load WHERE 1 " . $sWhere;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	/**
	 * 查询充值记录对应账变表中的记录个数，如果大于1条就返回记录
	 *
	 * @version 	v1.0	2010-06-05
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	private function _getOrderInfo( $aLoadInfo ){
		$aAllResult = array(); // 返回结果集
		$sWhere = ""; // 查询条件
		
		// 查询指定账变类型
		if (!empty($this->OrderTypeList))
			$sWhere .= " AND ordertypeid IN ({$this->OrderTypeList})";
		// 获取记录符合条件的记录集
		foreach ($aLoadInfo as $k => $v){
			$sSql = "SELECT * FROM orders WHERE 1 {$sWhere} AND description LIKE '%[L{$v['id']}]%'";
			$aResult = $this->oDB->getAll($sSql);
			if (count($aResult) > 1){
				// 查询用户信息
				$oUser = new model_user();
				$aUserInfo = $oUser->getUserExtentdInfo($aResult[0]['fromuserid']);
				$aAllResult[$k]['userid'] = $aResult[0]['fromuserid'];
				$aAllResult[$k]['username'] = $aUserInfo['username'];
				$aAllResult[$k]['amount'] = $aResult[0]['amount'];
				$aAllResult[$k]['times'] = $aResult[0]['times'];
				$aAllResult[$k]['no'] = "L" . $v['id'];
				$aAllResult[$k]['spec_name'] = $v['spec_name'];
				$aAllResult[$k]['acc_name'] = $v['acc_name'];
				$aAllResult[$k]['count'] = count($aResult);
			}
		}
		return $aAllResult;
	}
}