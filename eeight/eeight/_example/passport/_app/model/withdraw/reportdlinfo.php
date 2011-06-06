<?php

/**
 * 提现报表下载记录类
 *
 * @version 	v1.0	2010-04-19
 * @author 		louis
 */
class model_withdraw_ReportDLInfo extends model_pay_base_info {
	
	/**
	 * 编号
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 报表id串
	 *
	 * @var string
	 */
	public $IdList;
	
	/**
	 * 压缩包md5串
	 *
	 * @var string
	 */
	public $GzipMd5;
	
	/**
	 * 文件md5串
	 *
	 * @var string
	 */
	public $FileMd5;
	
	/**
	 * 供用户下载时显示的文件名
	 *
	 * @var string
	 */
	public $UseName;
	
	/**
	 * 原始文件名
	 *
	 * @var string
	 */
	public $FileName;
	
	/**
	 * 管理员ID
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 管理员名
	 *
	 * @var string
	 */
	public $AdminName;
	
	/**
	 * 添加时间
	 *
	 * @var datetime
	 */
	public $AddTime;
	
	
	/**
	 * 构造函数，返回指定报表下载信息
	 *
	 * @version 	v1.0	2010-04-19
	 * @author 		louis
	 */
	public function __construct($id = 0){
		parent::__construct('report_download_info');
		if ($id > 0){
			$sSql = "select * from $this->Table where id = '$id'";
			$aResult = $this->oDB->getOne( $sSql );
			$this->Id				= $aResult['id'];
			$this->IdList			= $aResult['idlist'];
			$this->GzipMd5			= $aResult['gzip_md5'];
			$this->FileMd5			= $aResult['file_md5'];
			$this->UseName			= $aResult['use_name'];
			$this->FileName			= $aResult['file_name'];
			$this->AdminId			= $aResult['admin_id'];
			$this->AdminName		= $aResult['admin_name'];
			$this->AddTime			= $aResult['atime'];
		} else {
			$this->Id = 0;
		}
	}
	
	
	/**
	 * 根据报表下载信息id的有无，来判断是调用信息增加方法还是信息修改方法
	 *
	 * @version 	v1.0	2010-04-19
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	public function save(){
		if ($this->Id){
			return $this->_set();
		} else {
			return $this->_add();
		}
	}
	
	
	/**
	 * 增加一条报表下载信息
	 *
	 * @version 	v1.0	2010-04-19
	 * @author 		louis
	 * 
	 * @return 		integer or boolean
	 */
	private function _add(){
		if(empty($this->IdList) || empty($this->GzipMd5) || empty($this->FileMd5) || intval($this->AdminId) <= 0 ||
			empty($this->AdminName) || empty($this->UseName) || empty($this->FileName))
			return false;
		$aData = array(
			'idlist'		=> $this->IdList,
			'gzip_md5'		=> $this->GzipMd5,
			'file_md5'		=> $this->FileMd5,
			'use_name'		=> $this->UseName,
			'file_name'		=> $this->FileName,
			'admin_id'		=> $this->AdminId,
			'admin_name'	=> $this->AdminName,
			'atime' => date('Y-m-d H:i:s')
		);
		return $this->oDB->insert( $this->Table, $aData );
	}
}