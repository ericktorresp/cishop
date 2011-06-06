<?php

/**
 * 提现报表数据格式类
 *
 */
class model_withdraw_WithdrawFormat extends model_pay_base_info {
	
	/**
	 * ID
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 报表名称ID
	 *
	 * @var int
	 */
	public $PPId;
	
	/**
	 * 排列序号
	 *
	 * @var int
	 */
	public $Seq;
	
	/**
	 * 报表头
	 *
	 * @var string
	 */
	public $Title;
	
	/**
	 * 对应其它类中的属性，主要是方便获取属性值
	 *
	 * @var string
	 */
	public $Property;
	
	/**
	 * 管理员ID
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 管理员
	 *
	 * @var string
	 */
	public $AdminName;
	
	/**
	 * 最近一次修改时间
	 *
	 * @var datetime
	 */
	public $UpdateTime;
	
	/**
	 * 添加时间
	 *
	 * @var string
	 */
	public $AddTime;
	
	/**
	 * 状态
	 *
	 * @var int
	 */
	public $Status;
	
	/**
	 * 修改信息后是否返回状态
	 *
	 * @var boolean
	 */
	public $ReturnStatus;
	
	
	/**
	 * 构造函数,获取指定用户银行信息
	 * 
	 * @version 	v1.0	2010-04-05
	 * @author 		louis
	 */
	public function __construct($id = 0){
		parent::__construct('withdraw_format');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id				= $aResult['id'];
			$this->PPId				= $aResult['pp_id'];
			$this->Seq				= $aResult['seq'];
			$this->Title			= $aResult['title'];
			$this->Property			= $aResult['property'];
			$this->AdminId			= $aResult['admin_id'];
			$this->AdminName		= $aResult['admin_name'];
			$this->Status			= $aResult['status'];
			$this->AddTime			= $aResult['atime'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据提现报表数据格式id的有无，来判断是调用信息增加方法还是信息修改方法
	 *
	 * @version 	v1.0	2010-04-05
	 * @author 		louis
	 * 
	 * @return integer or boolean
	 */
	public function save(){
		if ($this->Id) {
			return $this->_set();
		} else {
			return $this->_add();
		}
	}
	
	
	/**
	 * 增加一条提现报表数据格式列
	 * 
	 * @version 	v1.0	2010-04-05
	 * @author 		louis
	 * 
	 * @return 		integer
	 */
	private function _add(){
		if (intval($this->PPId) <= 0 || intval($this->Seq) <= 0 || empty($this->Title) || empty($this->Property) || 
			 intval($this->AdminId) <= 0 || empty($this->AdminName) || !isset($this->Status) || empty($this->AddTime))
			return false;
		$aData = array(
			'pp_id'			=> $this->PPId,
			'seq'			=> $this->Seq,
			'title'			=> $this->Title,
			'property'		=> $this->Property,
			'admin_id'		=> $this->AdminId,
			'admin_name'	=> $this->AdminName,
			'status'		=> $this->Status,
			'atime'			=> $this->AddTime,
			'utime' => date('Y-m-d H:i:s')
		);
		return $this->oDB->insert( $this->Table, $aData );
	}
	
	
	/**
	 * 修改一条提现报表数据格式列
	 *
	 * @version 	v1.0	2010-04-07
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _set(){
		if (empty($this->Id))		return false;
		$sSqlWhere = " utime='".date('Y-m-d H:i:s')."', ";
		if ($this->Seq)					$sSqlWhere .= " seq = $this->Seq,";
		if ($this->AdminId)				$sSqlWhere .= " admin_id = $this->AdminId,";
		if (!empty($this->AdminName))	$sSqlWhere .= " admin_name = '$this->AdminName',";
		if (isset($this->Status))		$sSqlWhere .= " status = 1-status,";
		if (empty($sSqlWhere))	return false;
		$sSqlWhere = substr($sSqlWhere, 1, -1);
		$this->oDB->query("UPDATE {$this->Table} SET $sSqlWhere WHERE id in ({$this->Id})");
		if ($this->oDB->errno() > 0){
			return false;
		} else {
			if ($this->ReturnStatus === true){
				$sSql = "SELECT status FROM {$this->Table} WHERE id = {$this->Id}";
				$aResult = $this->oDB->getOne($sSql);
				return $aResult['status'] + 1;
			} else {
				return true;
			}
		}
	}
	
	
	/**
	 * 删除指定报表数据列
	 *
	 * @version 	v1.0	2010-04-07
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function erase(){
		if (empty($this->Id))		return false;
		// 事务开始
        $this->oDB->doTransaction();
		if ($this->oDB->delete("{$this->Table}", "id in ({$this->Id})") > 0){
			$bResult = $this->_setSeq();
		}
		if ($bResult >= 0){
			$this->oDB->doCommit();
			return true;
		} else {
			$this->oDB->doRollback();
			return false;
		}
	}
	
	
	/**
	 * 通过报表标题查询是否已存在相同标题
	 * 
	 * @version 	v1.0	2010-04-05
	 * @author 		louis
	 *
	 * @return 		boolean
	 */
	public function contentExistsByTitle(){
		if (empty($this->Title) || intval($this->PPId) <= 0)	return true;
		$sSql = "SELECT * FROM {$this->Table} WHERE title = '{$this->Title}' AND pp_id = $this->PPId";
		$aResult = $this->oDB->getOne( $sSql );
		return empty($aResult) ? false : true;
	}
	
	
	
	/**
	 * 通过报表ID，查询报表下的内容列
	 *
	 * @version 	v1.0	2010-04-06
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function getInfoByPPId(){
		if (!$this->PPId)	return false;
		$sSql = "SELECT * FROM {$this->Table} WHERE pp_id = {$this->PPId} ORDER BY seq";
		$aResult = $this->oDB->getAll( $sSql );
		return $aResult;
	}
	
	
	/**
	 * 统计已有的报表列条数
	 *
	 * @version 	v1.0	2010-04-18
	 * @author 		louis
	 * 
	 * @return		integer
	 */
	public function countColumn(){
		if (!$this->PPId)	return false;
		$sSql = "SELECT count(id) as count FROM {$this->Table} WHERE pp_id = {$this->PPId}";
		return $this->oDB->getOne( $sSql );
	}
	
	
	/**
	 * 批量添加报表列,批量删除报表列
	 *
	 */
	public function columnAddList($aAdd = array(), $aDel = array()){
		if (empty($aAdd) && empty($aDel))	return true;
		// 数据检查
		if (!is_numeric($this->PPId) || $this->PPId <= 0 || !is_numeric($this->AdminId) || $this->AdminId <= 0 || 
			empty($this->AdminName) || !is_numeric($this->Status) || empty($this->AddTime)){
		    	return -1;
		    }
		// 事务开始
        $this->oDB->doTransaction();
        $iAdd = 0; // 添加成功的条数
        $iDel = 0; // 删除成功的条数
        
        // 删除
        if (!empty($aDel)){
        	foreach ($aDel as $k => $del){
        		$this->Id = $k;
        		if (!$this->erase()){
        			// 事务回滚
	        		$this->oDB->doRollback();
	        		return -3;
        		}
        		$iDel++;
        	}
        }
        
        $iResult = $this->_setSeq();
        
        if ($iResult < 0){
        	// 事务回滚
    		$this->oDB->doRollback();
    		return -4;
        } else {
        	$iNum = $iResult + 1;
        }
        
        // 添加
        if (!empty($aAdd)){
        	foreach($aAdd as $add){
        		$this->Seq = $iNum;
	        	$this->Title = $add['title'];
	        	$this->Property = $add['property'];
	        	if (!$this->_add()){
	        		// 事务回滚
	        		$this->oDB->doRollback();
	        		return -2;
	        	}
	        	$iNum++;
	        	$iAdd++;
	        }
        }
        
        if ($iAdd > 0 || $iDel > 0){
        	// 事务提交
        	$this->oDB->doCommit();
        	return 1;
        } else {
        	// 事务回滚
    		$this->oDB->doRollback();
    		return 0;
        }
	}
	
	
	/**
	 * 设置排序，删除记录后将记录重新排序
	 *
	 * @version 	v1.0	2010-04-30
	 * @author 		louis
	 */
	private function _setSeq(){
		 // 排序
        $oWDFormatList = new model_withdraw_WithdrawFormatList();
        $oWDFormatList->PPid = $this->PPId;
        $oWDFormatList->init();
        
        if (empty($oWDFormatList->Data))	return 0;
        $i = 1;
    	foreach ($oWDFormatList->Data as $list){
    		if ($i < 0 && $i > $oWDFormatList->TotalCount){
	    		return -4;
    		}
    		$this->Id = $list['id'];
    		$this->Seq = $i;
    		if (!$this->updateSeq()){
        		return -5;
    		}
    		$i++;
    	}
        return $oWDFormatList->TotalCount;
	}
	
	
	/**
	 * 设置排序 
	 *
	 * @version 	v1.0	2010-05-06
	 * @author 		louis
	 */
	private function  updateSeq(){
		if (intval($this->Id) <= 0 || intval($this->Seq) <= 0 ) return false;
		$sSql = "UPDATE {$this->Table} SET seq = {$this->Seq}, utime='".date('Y-m-d H:i:s')."' WHERE id = {$this->Id}";
		$this->oDB->query($sSql);
		if ($this->oDB->errno() > 0){
			return false;
		} else {
			return true;
		}
	}
}