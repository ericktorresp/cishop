<?php
/**
 * 文件系统缓存类
 * 
 * @author frank
 * @version 1.0 utf8 2010-03-30
 */

class model_pay_base_cachefile {
	/**
	 * 缓存根路径
	 *
	 * @var string
	 */
	public $RootPath;
	public $DataKey;
	public $Key;
	public $Path;
	public $CacheFile;
	protected $PassVars = array('oDB');
	
	/**
	 * 构造函数
	 *
	 * @param string $sDataType
	 * @param string $sKey
	 */
	function __construct($sKey = ''){
		$this->RootPath = $GLOBALS['sFileCatchRoot'];
		$sClassName = get_class($this);
		$aPart = explode('_');
		$sSubPath = array_pop($aPart);
		$this->Path = $GLOBALS['sFileCatchRoot'] . DS . $sSubPath;
		if ($this->key = $sKey){
			$this->CacheFile = $this->Path . DS . $this->Key . DS . '.php';
		}
	}
	
	/**
	 * 读取缓存文件，恢复到对象中
	 *
	 * @return boolean
	 */
	function readCache(){
		if (empty($this->CacheFile) or !is_readable($this->CacheFile))	return false;
		$bSucc = false;
		if ($aData = include($sCacheFile)){
			foreach($aData as $sKey => $mInfo){
				if (in_array($sKey,$this->PassVars))	continue ;
				$this->$sKey = $mInfo;
			}
			$bSucc = true;
		}
		return $bSucc;
	}
	
	/**
	 * 写缓存文件
	 *
	 * @return boolean
	 */
	function writeCache($mObject){
		if (empty($this->CacheFile))	return false;
		$aCode = @var_export($this);
		return file_put_contents($this->CacheFile,$aCode) > 0;
	}
}