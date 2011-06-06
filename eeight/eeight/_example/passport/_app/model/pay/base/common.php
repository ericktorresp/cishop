<?php
/**
 * 公共基类,负责处理系统信息
 * 
 * @author Frank
 * @version 1.0 2008-04-11 21:00
 * 
 */
//echo 'common';
class model_pay_base_common extends basemodel {
	/**
	 * 错误码
	 *
	 * @var integer
	 */
	protected $Errno;
	/**
	 * 错误信息
	 *
	 * @var string
	 */
	protected $Error;
	/**
	 * 设置错误信息
	 *
	 * @param integer $iErrno
	 * @param string $sErrMsg
	 */
	
	function __construct(){
		parent::__construct();
	}

	protected function SetError($iErrno,$sErrMsg = ''){
		$this->Errno = $iErrno;
		$this->Error = $sErrMsg ? $sErrMsg : $GLOBALS['aSysErrMsg'][$iErrno][LANGUAGE];
		return $this->Errno;
	}
	
	/**
	 * 获取错误码
	 *
	 * @return integer
	 */
	public function GetErrno(){
		return $this->Errno;
	}
	
	/**
	 * 将错误信息以JS提示框的方式显示
	 * DEBUG为TRUE时,将显示错误码
	 */
	public function ShowError(){
		$disp_msg = (DEBUG ? "$this->Errno : $this->Error" : $this->Error);
		echo "<script language='javascript'>\nalert('$disp_msg')\n</script>";
	}
		
	public function GetError(){
		return $this->Error;
	}
	
}