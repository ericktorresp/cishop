<?php

/**
 * 用户报表名称类
 *
 */
class model_withdraw_WithdrawReport extends model_pay_base_info {
	
	/**
	 * ID
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 平台类型
	 *
	 * @var int
	 */
	public $PlatformType;
	
	/**
	 * 平台ID
	 *
	 * @var int
	 */
	public $PlatformId;
	
	/**
	 * 平台名称
	 *
	 * @var string
	 */
	public $PlatformName;
	
	/**
	 * 报表标题
	 *
	 * @var string
	 */
	public $ReportName;
	
	/**
	 * 银行接受的字符编码
	 *
	 * @var string
	 */
	public $Charset;
	
	/**
	 * 操作管理员ID
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 操作管理员
	 *
	 * @var string
	 */
	public $AdminName;
	
	/**
	 * 最后一次修改时间
	 *
	 * @var datetime
	 */
	public $UpdateTime;
	
	/**
	 * 添加时间
	 *
	 * @var datetime
	 */
	public $AddTime;
	
	/**
	 * 状态
	 *
	 * @var int
	 */
	public $Status;
	
	
	
	/**
	 * 构造函数,获取指定提现报表标题
	 *
	 * @version 	v1.0	2010-04-02
	 * @author 		louis
	 * 
	 * @return 		propety
	 */
	public function __construct($id = 0){
		parent::__construct('withdraw_report');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id 			= $aResult['id'];
			$this->PlatformType = $aResult['platform_type'];
			$this->PlatformId	= $aResult['bank_id'] > 0 ? $aResult['bank_id'] : $aResult['api_id'];
			$this->PlatformName	= !empty($aResult['bank_name']) ? $aResult['bank_name'] : $aResult['api_name'];
			$this->ReportName	= $aResult['report_name'];
			$this->Charset		= $aResult['charset'];
			$this->AdminId		= $aResult['admin_id'];
			$this->AdminName	= $aResult['admin_name'];
			$this->UpdateTime	= $aResult['utime'];
			$this->AddTime		= $aResult['atime'];
			$this->Status		= $aResult['status'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据提现基础信息id的有无，来判断是调用信息增加方法还是信息修改方法
	 *
	 * @version 	v1.0	2010-04-05
	 * @author 		louis
	 * 
	 * @return integer or boolean
	 */
	public function save(){
		if ($this->Id){
			return $this->_set();
		} else {
			return $this->_add();
		}
	}
	
	
	/**
	 * 增加一条报告格式名称数据
	 *
	 * @version 	v1.0	2010-04-05
	 * @author 		louis
	 * 
	 * @return 		integer
	 */
	private function _add(){
		// 数据检查
		if ( $this->PlatformType <= 0 || $this->PlatformId <= 0 || empty($this->PlatformName) || empty($this->ReportName) || 
			empty($this->Charset) || !isset($this->Status) || intval($this->AdminId) <= 0 || empty($this->AdminName))
			return false;
		// 执行写入
		$aData = array(
			'platform_type'		=> $this->PlatformType,
			'report_name'		=> $this->ReportName,
			'charset'			=> $this->Charset,
			'admin_id'			=> $this->AdminId,
			'admin_name'		=> $this->AdminName,
			'status'			=> $this->Status,
			'utime' => date('Y-m-d H:i:s'),
			'atime'				=> date("Y-m-d H:i:s", time())
			
		);
		if ($this->PlatformType == 1){
			$aData['api_id'] 	= $this->PlatformId;
			$aData['api_name']	= $this->PlatformName;
		} else {
			$aData['bank_id'] 	= $this->PlatformId;
			$aData['bank_name']	= $this->PlatformName;
		}
		return $this->oDB->insert( $this->Table, $aData );
	}
	
	
	/**
	 * 修改一条报告格式名称数据
	 *
	 * @version 	v1.0	2010-04-06
	 * @author 		louis
	 * 
	 * @return 		integer
	 */
	private function _set(){
		if (!$this->Id)		return false;
		$aData = array();
		if (!empty($this->ReportName))	$aData['report_name']	= trim($this->ReportName);
		if (!empty($this->Charset))		$aData['charset'] 		= trim($this->Charset);
		if ($this->AdminId)				$aData['admin_Id'] 		= $this->AdminId;
		if (!empty($this->AdminName))	$aData['admin_name'] 	= $this->AdminName;
		if (isset($this->Status))		$aData['status'] 		= $this->Status;
		if (empty($aData))	return false;
		$aData['utime'] = date('Y-m-d H:i:s');
		return $this->oDB->update($this->Table, $aData, "id = {$this->Id}");
	}
	
	
	/**
	 * 通过报表名称查询报表是否已经存在
	 * 
	 * @version 	v1.0	2010-04-05
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function reportExistsByName(){
		if (empty($this->ReportName))	return true;
		$sSql = "SELECT * FROM {$this->Table} WHERE report_name = '{$this->ReportName}' AND status != 2 AND id != {$this->Id}";
		$aResult = $this->oDB->getOne( $sSql );
		return empty($aResult) ? false : true;
	}
}