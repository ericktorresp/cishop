<?php
/**
 * 路径: /_cli/cli_testinputcode.php
 * 功能: 每五分钟运行一次:输入测试开奖号码 ( * Crontab * )
 * 仅供测试期间使用
 * 
 * 
 * 命令行可接受参数: 空
 * 
 * 
 * @author    mark
 * @version   1.1.0
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_testinputcode extends basecli
{
    private $iLotteryId = 0;       // 彩种ID  `lottery`.lotteryid
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件


    /**
     * 析构
     */
    public function __destruct()
    {
        parent::__destruct();
        if( $this->bDoUnLock == TRUE )
        {
            @unlink( $this->_getBaseFileName() . $this->iLotteryId.  '.locks' );
        }
    }
    
    /**
     * 重写基类 _runCli() 方法
     */
    protected function _runCli()
    {
        // Step: 01 初步检测 CLI 参数合法性
        if( !isset($this->aArgv[1]) || !is_numeric($this->aArgv[1]) )
        {
            $this->iLotteryId = 0;   // 彩种ID
        }
        else 
        {
            $this->iLotteryId = intval($this->aArgv[1]);   // 彩种ID
        }
        
        
        // Step: 02 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . $this->iLotteryId.  '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            die('Error : The CLI is running' );
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁

        echo "[d] [".date('Y-m-d H:i:s')." ";
        $oTestInputCode = new model_testinputcode();
		if( $oTestInputCode->doTestInputCode($this->iLotteryId) !== FALSE )
		{
		    echo "inputcode success\n";
		}
		else 
		{
		    echo "inputcode File Fail\n";
		}
        
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_testinputcode(TRUE);
EXIT;
?>