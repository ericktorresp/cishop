<?php

/**
 * 
 * 路径：/_cli/am300_updatestatus.php
 * 停止转账时间，修改当天的待处理记录为挂起单
 * 
 * 
 * 命令行可接受参数: 银行代码
 * 
 * @author		louis
 * @version 	v1.0
 * @since 		2010-09-13
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



class cli_updatestatus extends basecli{
	
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
		$sEndTime = strtotime($oConfigd->getConfigs($sPrefix . 'deposit_stoptime'));	// 充值结束时间
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
		
    	// Step: 01 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            die("Error : The CLI is running\n" );
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁
		
       	// 将所有的充值申请记录状态为待处理的记录修改为挂起单
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
       	$bResult = $oEmailDeposit->updateAllProcess();
        if ($bResult === true){
        	$this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        	die("修改成功");
        } elseif (intval($bResult) > 0) {
        	$this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        	die("有 " . $bResult . "条失败的记录");
        } else {
        	$this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        	die("操作失败");
        }
        return TRUE;
    }
}

$oCli = new cli_updatestatus(TRUE);
EXIT;