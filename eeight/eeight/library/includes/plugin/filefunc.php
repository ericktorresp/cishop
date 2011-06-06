<?php
/**
 * 文件系统函数库
 *
 * @author frank
 * @version 2.0 2009-11-15
 */
define('___FILEFUNC',true);

/**
 * 保存上传的文件
 *
 * @param array $aFileInfo				文件信息数组,通常由$_FILES产生
 * @param string $sPath					文件保存路径
 * @param string $sAllowedMime			允许的文件的MIME类型,可以是MIME类型字符串的一部分字符,如'image'
 * @param string $sAllowedExtension		允许的文件扩展名,可以是数组或以'|'分隔的字符串
 * @param integer $iAllowedMinSize		文件的最小体积,字节
 * @param integer $iAllowedMaxSize		文件的最大体积,字节
 * @param integer $iAllowedMinWidth		(只适用于图片文件)最小宽度,像素
 * @param integer $iAllowedMaxWidth		(只适用于图片文件)最大宽度,像素
 * @return array
 * 	return[code]		错误码,为0时为成功,负数为失败
 *  return[err_msg]		错误信息
 * 	return[name]		目标文件的完整路径,含文件名,失败时为空;
 * 	return[ext]			目标文件的扩展名,失败时为空;
 */

function saveUploadFile($aFileInfo,$sPath,$sAllowedMime = '',$sAllowedExtension = '',$iAllowedMinSize = 0,$iAllowedMaxSize = 0,$iAllowedMinWidth = 0,$iAllowedMaxWidth = 0){
	extract($aFileInfo);
	// 检查文件大小是否为0
	if ($size == 0){
		return array('code' => -1,'err_msg' => '您没有上传文件');
	}
	// 检查允许的MIME类型
	if ($sAllowedMime){
		if (strpos($type,$sAllowedMime) === false)	return array('code' => -2,'err_msg' => '您上传的文件不符合类型要求');
	}
	// 扩展名检查
	$aPathInfo = pathinfo($name);
	$sExtension = strtolower($aPathInfo["extension"]);
	if ($sAllowedExtension){
		$aAllowedExtension = explode('|',$sAllowedExtension);
		if (in_array($sExtension,$aAllowedExtension) === false)
			return array('code' => -2,'err_msg' => "您上传的文件类型错误,只能上传 $sAllowedExtension 文件");
	}
	// 检查文件尺寸限制
	if ($iAllowedMinSize && ($size < $iAllowedMinSize)){
		return array('code' => -4,'err_msg' => "您上传的上传文件大小错误,不得小于 $iAllowedMinSize 字节");
	}
	if ($iAllowedMinSize && ($size < $iAllowedMinSize)){
		return array('code' => -4,'err_msg' => "您上传的上传文件大小错误,不得大于 $iAllowedMinSize 字节");
	}
	
	if ($iAllowedMinWidth || $iAllowedMaxWidth){
		list($width,$height) = getimagesize($tmp_name);
		if ($iAllowedMinWidth && ($width < $iAllowedMinWidth)){
			return array('code' => -8,'err_msg' => "您上传的图片分辨率太小，请不要上传宽度在 $iAllowedMaxWidth 像素以下的图片");
		}
		if ($iAllowedMaxWidth && ($width > $iAllowedMaxWidth)){
			return array('code' => -8,'err_msg' => "您上传的图片分辨率太大，请不要上传宽度在 $iAllowedMaxWidth 像素以上的图片");
		}
	}
	if (!file_exists($sPath) && !addDir($sPath)){
		return array('code' => -16,'err_msg' => "您的文件上传失败，目录 $sPath 创建失败");
	}

	$f_name = time() . ".$sExtension";
	$sTargetName = "$sPath/$f_name";
	mt_srand(time());
	while(file_exists($sTargetName)){
		$f_name = time() . '_' . str_pad(mt_rand(1000,9999),4,'0',STR_PAD_LEFT) . ".$sExtension";
		$sTargetName = "$sPath/$f_name";
	}
	if (!move_uploaded_file($tmp_name,$sTargetName)){
		return array('code' => -16,'err_msg' => "您的文件上传失败，请检查 $sPath 目录权限");
	}
	else{
		@chmod($sTargetName,0777);
	}
	return array('code' => 0,'err_msg' => '您的文件上传成功','name' => $sTargetName,'ext' => $sExtension);
}

/**
 * 删除指定的目录树
 *
 * @param string $sPath
 * @return boolean
 */
function removeDir($sPath){
	if (!is_writeable($sPath)) 		return false;
	if (!$rDir = opendir($sPath))	return false;
	while(($sFile = readdir($rDir)) !== false){
		if ($sFile == '.' || $sFile == '..')	continue;
		$sFullName = "$sPath/$sFile";
		if (is_dir($sFullName))
			$bSucc = removeDir($sFullName);
		else
			$bSucc = @unlink($sFullName);
		if (!$bSucc)	break;
	}
	closedir($rDir);
	!$bSucc or $bSucc = @rmdir($sPath);
	return $bSucc;
}

/**
 * 老的创建目录函数,$sPath可以是一个含文件名的路径,在此情况下,将创建此文件所在的目录
 *
 * @param string $sPath
 * @param integer $iMode
 * @param string $sOwner	属主
 * @return boolean
 */
function createDir($sPath,$iMode = 0777,$sOwner = APACHE_USER){
	if (PHP_VERSION >= 5){
		if ($iSucc = @mkdir($sPath,$iMode,true)){
		 	if (($iSucc = @chmod($sPath,$iMode)) && $sOwner)	$iSucc = chown($sPath,$sOwner);
		}		 	
		return $iSucc;
	}

	$sPath = str_replace('//','/',$sPath);
	if (substr($sPath,-1,1) == '/'){
		$sPath = substr($sPath,0,-1);
	}
	$bRoot = substr($sPath,0,1) == '/';
	$aSubPath = explode('/',$sPath);
	$iEnd = count($aSubPath);
		
	for($i = 0,$sPreviousPath = '';$i < $iEnd;$i++){
		if (($i == 0) && $bRoot)
			continue;
		$sFullPath = $sPreviousPath . ($i ? '/' : '') . $aSubPath[$i];
		if (!file_exists($sFullPath)){
			if (!mkdir($sFullPath,$iMode)){
				if (DEBUG){
					echo $sPath;
					echo "不能创建目录: $sFullPath";
				}
				return false;
			}
			else
				@chmod($sFullPath,$iMode);
		}
		$sPreviousPath = $sFullPath;
	}
	return true;
}

/**
 * 获取主文件名
 *
 * @param string $sName
 * @param boolean $bContainExtension		是否包括扩展名
 * @return string
 */
function getBasename($sName,$bContainExtension = true){
	if ($bContainExtension){
		return basename($sName);
	}
	else{
		$aPathInfo = pathinfo($sName);
		$sExt = $aPathInfo['extension'];
		return basename($sName,".$sExt");
	}
}

/**
 * 获取扩展名
 *
 * @param string $sName
 * @return string
 */
function getExtension($sName){
	$aPathInfo = pathinfo($sName);
	return $aPathInfo['extension'];
}

/**
 * 获取指定目录下的文件或子目录列表
 *
 * @param string $sPath
 * @return array
 */
function getFilesOfPath($sPath){
	if (!is_readable($sPath)) return false;
	$dir = opendir($sPath);
	$aFile = array();
	while (false !== ($file = readdir($dir))) {
		if (($file != ".") && ($file != "..")) continue;
		$aFile[] = $file;
	}
    closedir($dir);
    return $aFile;
}

/**
 * 创建目录函数
 * 创建指定的路径,并设置权限
 *
 * @param string $sPath
 * @param integer $iRight
 */
function addDir($sPath,$iRight = 0777){
	(!$bSucc = @mkdir($sPath,$iRight,true)) or $bSucc = @chmod($sPath,$iRight);
	return $bSucc;
}
