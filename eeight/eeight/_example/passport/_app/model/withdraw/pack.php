<?php
/**
 * 数据包信息类
 *
 */
class model_withdraw_Pack extends model_pay_base_info {
		
	/**
	 * 编号
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 数据包所包含信息的id串
	 *
	 * @var string
	 */
	public $IdList;
	
	/**
	 * 压缩包md5码
	 *
	 * @var string
	 */
	public $GzipMd5;
	
	/**
	 * 文件md5码
	 *
	 * @var string
	 */
	public $FileMd5;
	
	/**
	 * 供用户下载使用的文件名
	 *
	 * @var string
	 */
	public $UseName;
	
	/**
	 * 生成的压缩包真实文件名
	 *
	 * @var string
	 */
	public $FileName;
	
	/**
	 * 管理员id
	 *
	 * @var int
	 */
	public $AdminId;
	
	/**
	 * 管理员名称
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
	 * 构造函数，返回指定行政区信息
	 *
	 * @version 	v1.0	2010-04-20
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
}