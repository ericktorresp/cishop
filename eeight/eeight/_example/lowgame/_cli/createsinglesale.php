<?php
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); 

class cli_createsinglesale extends basecli
{
    private $iLotteryId = 0;       // 彩种ID
    private $sIssue = '';	//奖期
    private $sStarttime = '';	//日期
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件


    /**
     * 析构
     */
    public function __destruct()
    {
        parent::__destruct();
        if( $this->bDoUnLock == TRUE )
        {
            @unlink( $this->_getBaseFileName() . '_id_'. $this->iLotteryId. '.locks' );
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
            $this->halt('Error : Lottery ID #1001' );
        }
        $this->iLotteryId = intval($this->aArgv[1]);   // 彩种ID
		$this->sIssue = $this->aArgv[2];	//奖期
		$this->sStarttime = $this->aArgv[3];	//日期

        // Step: 02 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '_id_'. $this->iLotteryId. '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁


        // Step: 03 调用模型
        $oSale = new model_sale();
//         if( isset($this->aArgv[2]) && strtolower($this->aArgv[2])=='loop' )
//         {
//             $oSendBonus->setLoopMode(TRUE);
//         }
        sleep(3); // sleep for 3 sec. 防止与其他进程并发
        echo "[d] [".date("Y-m-d H:i:s")."] -------[ START ]-------------\n";
        $mResult = $oSale->createSingSale( $this->iLotteryId, $this->sIssue, $this->sStarttime );
        $sMsg = '';
        if( $mResult === FALSE )
        {
            echo "[d] ".date('Y-m-d H:i:s')." Message: Error\n";
            $this->bDoUnLock = TRUE;
            return FALSE;
        }

        echo '[ ALL DONE ]'."\n";
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_createsinglesale(TRUE);
EXIT;
?>