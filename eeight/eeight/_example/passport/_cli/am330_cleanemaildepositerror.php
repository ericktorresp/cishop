<?php

/**
 * 
 * 路径：/_cli/am300_cleanemaildepositerror.php
 * 停止转账时间，删除指定天数以前的异常充值记录
 * 
 * 
 * 命令行可接受参数: 银行代码
 * 
 * @author		louis
 * @version 	v1.0
 * @since 		2010-09-28
 * @package 	passport
 * 
 */

@ini_set( "display_errors", TRUE);
error_reporting(E_ALL);
set_time_limit(10000);
define('DONT_USE_APPLE_FRAME_MVC', TRUE);
define("ICBC", "mail");
define("CCB", "ccb");
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php');



class cli_cleanEmailDepositError extends basecli{
	
	private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件
	
	/**
     * 析构
     */
    public function __destruct()
    {
        parent::__destruct();
        if( $this->bDoUnLock == TRUE )
        {
            @unlink( $this->_getBaseFileName() . '.locks' );
        }
    }
    
    
    /**
     * 重写基类 _runCli() 方法
     */
    protected function _runCli()
    {
        // 检查参数
    	$sBank = isset($this->aArgv[1]) ? $this->aArgv[1] : "";
    	if (empty($sBank)){
    		die("have no bank info");
    	}
        
        $sPrefix =  "";
    	switch ($sBank){
    		case ICBC:
    			$sPrefix = "mail";
    		break;
    		case CCB:
    			$sPrefix = "ccb";
    		break;
    		default:
    			die("have no bank info");
    			
    	}
    	// 检查充值时间
    	$oConfigd = new model_config();
		$sStartTime = strtotime($oConfigd->getConfigs($sPrefix . 'deposit_starttime')); // 充值开始时间
		$sEndTime = strtotime($oConfigd->getConfigs($sPrefix . 'deposit_stoptime'));	  // 充值结束时间
		$sRunNow = strtotime(date('G:i')); // 当前时间
		if ($sStartTime > $sEndTime){ // 开始时间大于结束时间，说明已跨天
			if ($sRunNow <= $sEndTime && $sRunNow >= $sStartTime){
				die("This time is forbidden");
		}
		} else {
			if ($sRunNow >= $sStartTime && $sRunNow <= $sEndTime){
				die("This time is forbidden");
			}
		}
		
		$iDays = $oConfigd->getConfigs($sPrefix . 'deposit_cleandays'); // 清理天数
		
		if (intval($iDays) <= 0){
			die("The format of days is wrong\n");
		}
		
    	// Step: 01 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            die("Error : The CLI is running\n" );
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁
		
       	switch ($sBank){
			case ICBC:
       	$oEmailDeposit = new model_deposit_emaildeposit();
			break;
			case CCB:
				$oEmailDeposit = new model_deposit_ccbdeposit();
			break;
			default:
				die("have no bank info");
		}
       	$oEmailDeposit->Days = $iDays;
       	$oEmailDeposit->Status = "2,3";
       	$bResult = $oEmailDeposit->cleanErrors();
       	if ($bResult === false){
       		$this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
       		die("have warning\n");
       	} else {
       		$this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
       		die("clean is finish\n");
       	}
    }
}

$oCli = new cli_cleanEmailDepositError(TRUE);
EXIT;