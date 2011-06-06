<?php
/**
 * 下载类
 * 01 根据传入数据生成指定文件类型
 * 02 将生成的文件压缩
 * 03 压缩成功后将临时文件删除
 * 04 提供下载
 * 
 * @version 	v1.0	2010-03-23
 * @author 		louis
 */
define('DOWNLOAD_DIR', dirname(__FILE__) . '/../../../../passportadmin/_data/download/');
define('DOWNLOAD_URL', "/_data/download/");
define('EDOWNLOAD_DIR', dirname(__FILE__) . '/../../../../passportadmin/_data/');
define('EDOWNLOAD_URL', "/_data/");
//require_once(PDIR_USER.DS.'_app'.DS.'model'.DS.'withdraw'.DS.'reportdlinfo.php');
class model_withdraw_download extends model_pay_base_info{
	
	
	/**
	 * 文件名
	 *
	 * @var string
	 */
	public $FileName;
	
	/**
	 * 报表内容
	 *
	 * @var array
	 */
	public $Value;
	
	/**
	 * 记录集id
	 *
	 * @var array
	 */
	public $RecordId;
	
	/**
	 * 模板文件名
	 *
	 * @var string
	 */
	public $ReportName;
	
	/**
	 * 压缩文件类型
	 *
	 * @var string
	 */
	public $ZipType;
	
	/**
	 * 生成的报表文件扩展名
	 *
	 * @var string
	 */
	public $FileExtend = ".csv";
	
	/**
	 * 去除扩展名后原文件名，供函数内部使用
	 *
	 * @var string
	 */
	private $TempName;
	
	/**
	 * 生成的报表数据是否需要去查询
	 *
	 * @var boolean
	 */
	private $Select;
	
	/**
	 * 使用自己传入的记录时，文档的标题头
	 *
	 * @var array
	 */
	private $Title;
	
	/**
	 * 报表的总表头，“平台充值信息”，“银行查询信息”等
	 *
	 * @var array
	 */
	private $Sign;
	
	/**
	 * 上传文件夹名
	 *
	 * @var string
	 */
	public $Directory;
	
	/**
	 * 构造函数
	 *
	 * @param string 		$directory		 上传文件夹名
	 * @param string 		$fileName		 文件名
	 * @param array 		$aValue			 内容
	 * @param array 		$aRecordId		 记录id集
	 * @param string 		$reportName		 模板文件名
	 * @param string 		$zipType		 压缩文件类型
	 * @param boolean 		$bSelect		 是否去查询数据，默认为true,根据$aRecordId查询相应数据，如果为false,则利用$aValue传入的数据
	 * 
	 * @version 		v1.0 	2010-03-23
	 * @author 			louis
	 */
	public function __construct($directory, $fileName, $aValue, $aRecordId = array(), $reportName, $zipType = 'zip', $aTitle = array(), $bSelect = TRUE, $aSign = array() ){
		parent::__construct('');
		if (empty($fileName) || empty($aValue)) 	return false;
		$this->FileName 	= trim($fileName);
		$this->Value		= $aValue;
		$this->RecordId		= $aRecordId;
		$this->ReportName	= $reportName . '-' . date("ymd-His", time());
		$this->ZipType		= trim($zipType);
		$this->TempName 	= substr($this->FileName, 0, -(strlen($this->FileName) - strrpos($this->FileName, '.')));
		$this->Select 		= $bSelect;
		$this->Title		= $aTitle;
		$this->Sign			= $aSign;
		$this->Directory 	= $directory;
		$this->init();
		return true;
	}
	
	
	/**
	 * 生成下载文件主程序
	 *
	 * @version 	v1.0	2010-03-23
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	private function init(){
		// 首先检查存在文件的文件夹是否存在
		if ($this->Select === true ){
			if (!file_exists(DOWNLOAD_DIR)){
				mkdir( DOWNLOAD_DIR, 0777 , true);
				chmod( DOWNLOAD_DIR, 0777);
			}
		} else {
			if (!file_exists(EDOWNLOAD_DIR . $this->Directory . "/")){
				mkdir( EDOWNLOAD_DIR . $this->Directory . "/", 0777 , true);
				chmod( EDOWNLOAD_DIR . $this->Directory . "/", 0777);
			}
		}
		
		// 检查目标文件是否存在，如果存在则删除
		if ($this->Select === true){
			if (file_exists(DOWNLOAD_DIR . $this->ReportName . $this->FileExtend)){
				@unlink(DOWNLOAD_DIR . $this->ReportName . $this->FileExtend);
			}
		} else {
			if (file_exists(EDOWNLOAD_DIR . $this->Directory . "/" . $this->ReportName . $this->FileExtend)){
				@unlink(EDOWNLOAD_DIR . $this->Directory . "/" . $this->ReportName . $this->FileExtend);
			}
		}
		// 检查目标压缩包是否存在，如果存在则删除
		if ($this->Select === true){
			if (file_exists(DOWNLOAD_DIR . $this->ReportName . "." . $this->ZipType)){
				@unlink(DOWNLOAD_DIR . $this->ReportName . '.' . $this->ZipType);
			}
		} else {
			if (file_exists(EDOWNLOAD_DIR . $this->Directory . "/" . $this->ReportName . "." . $this->ZipType)){
				@unlink(EDOWNLOAD_DIR . $this->Directory . "/" . $this->ReportName . '.' . $this->ZipType);
			}
		}
		
		// 生成临时文件
		if (!($sFileMd5 = $this->makeFile()))	return false;
		// 将生成的临时文件压缩
		if (!($sGzipMd5 = $this->createRar()))  return false;
		
		if ($this->Select === true){ // 需要查询数据
			// 事务开始
	        $this->oDB->doTransaction();
			// 向下载报表记录表中写入记录
			$oReportInfo = new model_withdraw_ReportDLInfo();
			$oReportInfo->IdList 	= implode(',', $this->RecordId);
			$oReportInfo->FileMd5	= $sFileMd5;
			$oReportInfo->GzipMd5	= $sGzipMd5;
			$oReportInfo->UseName	= $this->ReportName . '.' . $this->ZipType;
			$oReportInfo->FileName	= $this->TempName . '.' . $this->ZipType;
			$oReportInfo->AdminId	= $_SESSION['admin'];
			$oReportInfo->AdminName	= $_SESSION['adminname'];
			if ($oReportInfo->save()){
				// 组合id串
				$oFODetails = new model_withdraw_fundoutdetail();
				$oFODetails->IdList = implode(',', $this->RecordId);
				$iResult = $oFODetails->setDownloadStatus();
				if ($iResult != count($this->RecordId)){
					// 事务回滚
					$this->oDB->doRollback();
					return -2;
				} else {
					$this->oDB->doCommit();
					// 下载
					$this->getFile();
				}
			} else {
				// 事务回滚
	        	$this->oDB->doRollback();
				return -1;
			}
		} else { // 利用传入的数据
			$oReportInfo = new model_withdraw_ReportDLInfo();
			$oReportInfo->IdList 	= implode(',', $this->RecordId);
			$oReportInfo->FileMd5	= $sFileMd5;
			$oReportInfo->GzipMd5	= $sGzipMd5;
			$oReportInfo->UseName	= $this->ReportName . '.' . $this->ZipType;
			$oReportInfo->FileName	= $this->TempName . '.' . $this->ZipType;
			$oReportInfo->AdminId	= $_SESSION['admin'];
			$oReportInfo->AdminName	= $_SESSION['adminname'];
			if ($oReportInfo->save()){
				$this->getFile();
			}
		}
	}
	
	/**
	 * 生成临时文件
	 *
	 * @version 		v1.0	2010-03-23
	 * @author 			louis
	 * 
	 * @return 			boolean
	 */
	private function makeFile(){
		// 循环显示标题
		$sContent = "";
		$sManualContent = "";
		if ($this->Select === true){
			foreach ($this->Value as $k => $value){
				$sContent .= $value['title'] . ",";
			}
		} else { // 不查询
			foreach ($this->Sign as $a => $b){
				$sContent .= $b . ",";
			}
			$sContent = substr($sContent, 0, -1);
			$sContent .= "\r\n";
			foreach ($this->Title as $k => $value){
				$sContent .= $value . ",";
			}
		}
		
		$sContent = substr($sContent, 0, -1);
		$sContent .= "\r\n";
		
		
		$sManualContent = $sContent;
		if ($this->Select === true){
			// 循环显示内容,银行提现用
			foreach ($this->RecordId as $key => $Id){ // 循环记录集
				$oFODetail = new model_withdraw_fundoutdetail($Id);
				foreach ($this->Value as $k => $value){
					$sContent .= $oFODetail->$value['property'] . ",";
					// 手工对账用，银行账号前加上”'“，显示完整的卡号
					if ($value['property'] == "Account"){
						$sSign = "'";
					} else {
						$sSign = "";
					}
					$sManualContent .= $sSign . $oFODetail->$value['property'] . ",";
				}
				// 银行提现用
				$sContent = substr($sContent, 0, -1);
				$sContent .= "\r\n";
				// 手工对账用
				$sManualContent = substr($sManualContent, 0, -1);
				$sManualContent .= "\r\n";
			}
		} else {
			foreach ($this->Value as $key => $values){ // 循环记录集
				foreach ($values as $c => $d){
					$sContent .= $d . ",";
					// 手工对账用，银行账号前加上”'“，显示完整的卡号
					if ($c == "money" || $c == "amount" || $c == "created" || $c == "pay_date" || $c == "full_account" || 
						$c == "account" || $c == "create" || $c == "balance" || $c == "accept_card"){
						$sSign = "'";
					} else {
						$sSign = "";
					}
					$sManualContent .= $sSign . $d . ",";
				}
				// 银行提现用
				$sContent = substr($sContent, 0, -1);
				$sContent .= "\r\n";
				// 手工对账用
				$sManualContent = substr($sManualContent, 0, -1);
				$sManualContent .= "\r\n";
			}
		}
		
		
//		$sContent .= "总金额：" . $this->Value['total_amount'] . "," . "总手续费：" . $this->Value['total_charge'] . "\r\n";
		// 内容转码
		$sContent = iconv('utf-8', 'gbk', $sContent);
		$sManualContent = iconv('utf-8', 'gbk', $sManualContent);
		// 去除内容中的逗号
		
		// IE下需要先转换一下文件名编码
//		if (preg_match('/MSIE/',$_SERVER['HTTP_USER_AGENT'])) {
        	$sReportName = iconv('utf-8', 'gbk', $this->ReportName);
		/*} else {
			$sReportName = $this->ReportName;
		}*/
		if ($this->Select === true){
			if (file_put_contents(DOWNLOAD_DIR . 'bank-' . $sReportName . $this->FileExtend, $sContent) === false)	return false;
		} else {
			if (file_put_contents(EDOWNLOAD_DIR . $this->Directory . "/" . 'bank-' . $sReportName . $this->FileExtend, $sContent) === false)	return false;
		}
		if ($this->Select === true ){
			if (file_put_contents(DOWNLOAD_DIR . 'manual-' . $sReportName . $this->FileExtend, $sManualContent) === false)	return false;
		} else {
			if (file_put_contents(EDOWNLOAD_DIR . $this->Directory . "/" . 'manual-' . $sReportName . $this->FileExtend, $sManualContent) === false)	return false;
		}
		if ($this->Select === true ){
			@chmod(DOWNLOAD_DIR . 'bank-' . $sReportName . $this->FileExtend, 0777);
			@chmod(DOWNLOAD_DIR . 'manual-' . $sReportName . $this->FileExtend, 0777);
		} else {
			@chmod(EDOWNLOAD_DIR  . $this->Directory . "/". 'bank-' . $sReportName . $this->FileExtend, 0777);
			@chmod(EDOWNLOAD_DIR . $this->Directory . "/" . 'manual-' . $sReportName . $this->FileExtend, 0777);
		}
		
		// 获取生成的文件的md5码
//		fclose($fp);
		if ($this->Select === true ){
			return md5_file(DOWNLOAD_DIR . 'bank-' . $sReportName . $this->FileExtend);
		} else {
			return md5_file(EDOWNLOAD_DIR . $this->Directory . "/" . 'bank-' . $sReportName . $this->FileExtend);
		}
	}
	
	
	/**
	 * 将文件压缩，成功后将原有文件删除
	 *
	 * @version		v1.0 	2010-03-24
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	private function createRar(){
		// IE下需要先转换一下文件名编码
//		if (preg_match('/MSIE/',$_SERVER['HTTP_USER_AGENT'])) {
        	$sReportName = iconv('utf-8', 'gbk', $this->ReportName);
		/*} else {
			$sReportName = $this->ReportName;
		}*/
		if ($this->Select === true ){
			chdir( DOWNLOAD_DIR );
		} else {
			chdir( EDOWNLOAD_DIR . $this->Directory . "/" );
		}
		
//		file_put_contents("/work/website/test/test",$this->TempName, FILE_APPEND);
		passthru("zip -m " . $this->TempName . "." . $this->ZipType . "  " . 'bank-' . $sReportName . $this->FileExtend . " " . 'manual-' . $sReportName . $this->FileExtend , $bResult);
		if($bResult != 0){
			return false;
		} else {
			@chmod($this->TempName . "." . $this->ZipType, 0777);
			return md5_file($this->TempName . "." . $this->ZipType);
		}
	}
	
	
	/**
	 * 下载
	 *
	 * @version 	v1.0	2010-03-24
	 * @author 		louis
	 */
	private function getFile(){
		// IE下需要先转换一下文件名编码
//		if (preg_match('/MSIE/',$_SERVER['HTTP_USER_AGENT'])) {
        	$this->ReportName = iconv('utf-8', 'gbk', $this->ReportName);
//		}
		header("Content-Type: application/x-gzip");//根据下载文件类型可能有变化
		header("Content-Disposition: attachment; filename=" . $this->ReportName . '.' . $this->ZipType);//文件名可改
		if ($this->Select === true ){
			readfile(DOWNLOAD_DIR . $this->TempName . "." . $this->ZipType);
		} else {
			readfile(EDOWNLOAD_DIR . $this->Directory . "/" . $this->TempName . "." . $this->ZipType);
		}
		
		exit;
	}
}