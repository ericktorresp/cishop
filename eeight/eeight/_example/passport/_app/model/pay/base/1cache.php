<?php
/**
 * Cache类，用于创建对象的文件缓存，及从缓存中恢复对象
 * 
 * @version 1.3 utf8
 * @author frank 2009-06-23 11:00
 */

class model_pay_base_cachefile {
	/**
	 * 缓存根路径
	 *
	 * @var string
	 */
	public $RootPath;
	/**
	 * 各类缓存文件的存放根路径数组
	 *
	 * @var array
	 */
	public $Paths;
	/**
	 * 各类缓存文件的路径规则数组
	 *
	 * @var array
	 */
	public $PathRules;
	/**
	 * 名称规则数组
	 *
	 * @var array
	 */
	public $NameRules;
	/**
	 * 缓存数组全局变量名
	 *
	 * @var string
	 */
	public $CacheVarName;
	
	/**
	 * 对路径、路径规则、名称规则等进行初始化
	 *
	 * @param string $sCacheVarName		缓存数组全局变量名
	 */
	function __construct($sCacheVarName = ''){
		$sCacheVarName or $sCacheVarName = 'aBasicConfig';
		$this->CacheVarName = $sCacheVarName;
		$this->RootPath = $GLOBALS[G_NICE_VAR]['CACHE_PATH']['ROOT'];
		foreach($GLOBALS[G_NICE_VAR]['CACHE_PATH'] as $sKey => $sPath){
			if ($sKey == 'ROOT')	continue;
			$this->Paths[$sKey] = $sPath;
		}
		if (key_exists('LOTTERY',$this->Paths)){
			$this->PathRules['LOTTERY'] = $this->Paths['LOTTERY'];
			$this->NameRules['LOTTERY'] = "{PATH}/{ID}.inc.php";
		}
		if (key_exists('BONUS_GROUP',$this->Paths)){
			$this->PathRules['BONUS_GROUP'] = $this->Paths['BONUS_GROUP'] . "/{LOTTERY_ID}";
			$this->NameRules['BONUS_GROUP'] = "{PATH}/{ID}.inc.php";
		}
		if (key_exists('BUY_METHOD',$this->Paths)){
			$this->PathRules['BUY_METHOD'] = $this->Paths['BUY_METHOD'] . "/{LOTTERY_ID}";
			$this->NameRules['BUY_METHOD'] = "{PATH}/{ID}.inc.php";
		}
		if (key_exists('SYS_CONFIG',$this->Paths)){
			$this->PathRules['SYS_CONFIG'] = $this->Paths['SYS_CONFIG'];
			$this->NameRules['SYS_CONFIG'] = "{PATH}/sys_config.inc.php";
		}
		if (key_exists('ISSUE_LIST',$this->Paths)){
			$this->PathRules['ISSUE_LIST'] = $this->Paths['ISSUE_LIST'];
			$this->NameRules['ISSUE_LIST'] = '{PATH}/{LOTTERY_ID}.inc.php';
		}
		if (key_exists('BONUS_CODE',$this->Paths)){
			$this->PathRules['BONUS_CODE'] = $this->Paths['BONUS_CODE'];
			$this->NameRules['BONUS_CODE'] = '{PATH}/{LOTTERY_ID}-{ISSUE}';
		}
		if (key_exists('BONUS_ISSUE',$this->Paths)){
			$this->PathRules['BONUS_ISSUE'] = $this->Paths['BONUS_ISSUE'];
			$this->NameRules['BONUS_ISSUE'] = '{PATH}/{LOTTERY_ID}';
		}
		if (key_exists('PRJ_BONUS_STR',$this->Paths)){
			$this->PathRules['PRJ_BONUS_STR'] = $this->Paths['PRJ_BONUS_STR'];
			$this->NameRules['PRJ_BONUS_STR'] = '{PATH}/{PRJ_ID}';
		}
		if (key_exists('PRJ_STATUS',$this->Paths)){
			$this->PathRules['PRJ_STATUS'] = $this->Paths['PRJ_STATUS'];
			$this->NameRules['PRJ_STATUS'] = '{PATH}/{PRJ_ID}';
		}
		if (key_exists('TASK_STATUS',$this->Paths)){
			$this->PathRules['TASK_STATUS'] = $this->Paths['TASK_STATUS'];
			$this->NameRules['TASK_STATUS'] = '{PATH}/{TASK_ID}';
		}
		if (key_exists('PRJ_STATUS',$this->Paths)){
			$this->PathRules['TASK_DETAIL'] = $this->Paths['TASK_DETAIL'];
			$this->NameRules['TASK_DETAIL'] = '{PATH}/{TASK_ID}/{ISSUE}';
		}
		if (key_exists('USER_PARENTS',$this->Paths)){
			$this->PathRules['USER_PARENTS'] = $this->Paths['USER_PARENTS'];
			$this->NameRules['USER_PARENTS'] = '{PATH}/{USER_ID}';
		}
	}
	
	/**
	 * 生成缓存文件名并返回
	 *
	 * @param string $sCacheKey			缓存类型关键字
	 * @param array $aConfig			构成文件名的各参数数组
	 * @return string					失败时返回 false
	 */
	function makeCacheFileName($sCacheKey,$aConfig = array()){
		$sPath = $this->PathRules[$sCacheKey];
		foreach($aConfig as $sKey => $mValue){
			$aSearch[] = '{' . $sKey . '}';
			$aPartOfValue = explode(',',$mValue);
			$aReplace[] = $aPartOfValue[0];
		}
		$sPath = str_replace($aSearch,$aReplace,$sPath);
//		echo "$sPath<br>";
		if (!file_exists($sPath)){
			mkdir($sPath,0777,true);
			chmod($sPath,0777);
		}
		$sName = str_replace("{PATH}",$sPath,$this->NameRules[$sCacheKey]);
		$sName = str_replace($aSearch,$aReplace,$sName);
		return $sName;
	}
	
	/**
	 * 写入缓存文件
	 *
	 * @param string $sCacheKey			缓存类型关键字
	 * @param array $aConfig			构成文件名的各参数数组
	 * @param mixed $mContents			需要缓存的变量
	 * @return integer					失败时返回 false
	 */
	function putCache($sCacheKey,$aConfig,$mContents,$bNoVarName = false){
		$sFileName = $this->makeCacheFileName($sCacheKey,$aConfig);
		if (!$bNoVarName){
			$sStr = '<?php' . "\n" . "\${$this->CacheVarName}['$sCacheKey']";
			if ($aConfig){
				$sKeyInCacheArray = implode('_',$aConfig);
				$sStr .= "['$sKeyInCacheArray']";
			}
			$sStr .= ' = ';
		}
		$sStr .= $this->getConfigValueStringForWrite($mContents);
		!$bSucc = file_put_contents($sFileName,$sStr) or @chmod($sFileName,0777);
		return $bSucc;
	}

	/**
	 * 从缓存中恢复数据并返回,供restoreSimple及restoreObject方法调用
	 *
	 * @param string $sCacheKey			缓存类型关键字
	 * @param array $aConfig			构成文件名的各参数数组
	 * @return mixed
	 * @access private
	 */
	private function & _restoreFromCache($sCacheKey,$aKeys){
		$sFileName = $this->makeCacheFileName($sCacheKey,$aKeys);
		if (!file_exists($sFileName))	return false;
		require($sFileName);
		if ($aKeys){
			foreach($aKeys as $sKey){
				$aPartOfKey = explode(',',$sKey);
				$aTmpKeys[] = $aPartOfKey[0];
			}
			$sKeyInCacheArray = implode('_',$aTmpKeys);
			return ${$this->CacheVarName}[$sCacheKey][$sKeyInCacheArray];
		}
		return ${$this->CacheVarName}[$sCacheKey];
	}

	/**
	 * 从数据缓存文件中恢复数据至指定对象中，简单变量及数组请使用restoreSimple方法
	 *
	 * @param mixed $sCacheKey		缓存组关键字
	 * @param array $aKeys			用以构成子关键字的信息数组
	 * @param array $oObject		欲恢复的对象
	 * @return boolean
	 */
	function restoreObject($sCacheKey,$aKeys, $oObject){
		if (!is_object($oObject))	return false;
		if ($mCache = $this->_restoreFromCache($sCacheKey,$aKeys)){
			foreach($mCache as $sKey => $mValue){
				$oObject->$sKey = $mValue;
			}
			return true;
		}
		return false;
	}

	/**
	 * 从数据缓存文件中恢复数据至指定变量中，不适用于对象，对象请使用restoreObject方法
	 *
	 * @param mixed $sCacheKey		缓存组关键字
	 * @param array $aKeys			用以构成子关键字的信息数组
	 * @param array $mVar			欲恢复的变量
	 * @return boolean
	 */
	function restoreSimple($sCacheKey,$aKeys,& $mVar){
		if (is_object($mVar))	return false;
		if ($mCache = $this->_restoreFromCache($sCacheKey,$aKeys)){
			$mVar = $mCache;
			return true;
		}
		return false;
	}

	/**
	 * 根据需要缓存的变量生成缓存文件的内容字符串并返回
	 *
	 * @param mixed $mValue
	 * @param string $sName			名字
	 * @return string
	 * @access private
	 */
	private function getConfigValueStringForWrite($mValue,$sName = ''){
		static $iDeep = 0;
		switch (gettype($mValue)){
			case "boolean":
    		case "integer":
		    case "double":
		    case "string":
		    case "NULL":
				strval($sName) == '' or $sStr = str_repeat(chr(9),$iDeep) . "'$sName' => ";
				$bUseQuote = (is_numeric($mValue) ? $mValue{0} == '0' : true) || $mValue == '';
				$sStr .= ($bUseQuote ? "'" : '') . $mValue . ($bUseQuote ? "'" : '');
		    	break;
		    case "array":
		    case "object":
				$aLine = array();
		    	$sStr = ($sName != '' ? "	'$sName' => " : '') . "array(\n";
		    	$iDeep++;
				foreach($mValue as $sVName => $mValue){
					$aLine[] = $this->getConfigValueStringForWrite($mValue,$sVName);
				}
				$sStr .= implode(",\n",$aLine);
				$iDeep--;
				$sStr .= "\n" . str_repeat(chr(9),$iDeep) . ")";
				$iDeep or $sStr .= ";\n";
				break;
		    case "resource":
		    case "unknown type":
		}
		return $sStr;
	}
}
// End Class