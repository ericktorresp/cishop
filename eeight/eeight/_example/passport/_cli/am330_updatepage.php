<?php

/**
 * 
 * 路径：/_cli/am300_updatepage.php
 * 停止转账时间，修改建行虚拟贡列表上一次抓取到的页码
 * 
 * 
 * 命令行可接受参数: 空
 * 
 * @author		louis
 * @version 	v1.0
 * @since 		2010-12-12
 * @package 	passport
 * 
 */

@ini_set( "display_errors", TRUE);
error_reporting(E_ALL);
set_time_limit(10000);
define('DONT_USE_APPLE_FRAME_MVC', TRUE);
define("BANKID", 9);
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php');



class cli_cleanEmailDepositRecord extends basecli{
	
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
    	// 检查充值时间
    	$oConfigd = new model_config();
		$sStartTime = strtotime($oConfigd->getConfigs('ccbdeposit_starttime')); // 充值开始时间
		$sEndTime = strtotime($oConfigd->getConfigs('ccbdeposit_stoptime'));	  // 充值结束时间
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
		
		$oEmailDeposit = new model_deposit_ccbdeposit();
        $oEmailDeposit->BankId = BANKID;
       	$bResult = $oEmailDeposit->updatePage();
       	if ($bResult === false){
       		$this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
       		die("have warning\n");
       	} else {
       		$this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
       		die("update is finish\n");
       	}
    }
}

$oCli = new cli_cleanEmailDepositRecord(TRUE);
EXIT;