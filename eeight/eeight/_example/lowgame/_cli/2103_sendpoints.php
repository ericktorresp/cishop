<?php
/**
 * 路径: /_cli/2103_sendpoints.php
 * 功能: 低频停止销售后的用户返点派发程序 ( * Crontab * )
 * 
 * 运行时间:
 * ~~~~~~~~~~~~~~~~~
 *    21:00-01:59 每3分钟运行1次 PHP SLEEP FOR 12 secs
 * 
 * 
 * 命令行可接受参数:
 * ~~~~~~~~~~~~~~~~~ 
 *    argv[1] = 彩种ID
 *    argv[2] = loop 否循环进行转换(限开发用)
 * 
 * 
 * @author    Tom     090908 10:17
 * @version   1.2.0
 * @package   lowgame
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_sendpoints extends basecli
{
    private $iLotteryId = 0;       // 彩种ID
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


        // Step: 02 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '_id_'. $this->iLotteryId. '.locks';
        if( file_exists( $sLocksFileName ) )
        {
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁


        // Step: 03 调用模型
        $oSendPoints = new model_sendpoints();
        if( isset($this->aArgv[2]) && strtolower($this->aArgv[2])=='loop' )
        {
            $oSendPoints->setLoopMode(TRUE);
        }
        //$oSendPoints->setProcessMax(1);
        //$oSendPoints->setSteps( 10000, 15 );  // 每处理1万条返点信息, 让 CLI 程序SLEEP 15秒
        sleep(12); // sleep for 12 sec. 防止与其他进程并发
        echo "[d] [".date("Y-m-d H:i:s")."] -------[ START ]-------------\n";
        $mResult = $oSendPoints->doSendPoints( $this->iLotteryId );
        $sMsg = '';
        if( $mResult <= 0 )
        {
            switch( $mResult )
            {
                case -1001 : $sMsg = 'Wrong Lottery Id'; BREAK;
                case -1002 : $sMsg = 'ALL DONE (All Issue)'; BREAK;
                case -1003 : $sMsg = 'ALL DONE (Single Issue)'; BREAK;
                case -2001 : $sMsg = 'Transaction Start Failed.'; BREAK;
                case -2002 : $sMsg = 'Transaction RollBack Failed.'; BREAK;
                case -2003 : $sMsg = 'Transaction Commit Failed.'; BREAK;
                case -3001 : $sMsg = 'Issue Update Failed.'; BREAK;
                default : $sMsg='Unknown ErrCode='.$mResult; BREAK;
            }
            echo "[d] ".date('Y-m-d H:i:s')." Message: $sMsg\n";
            $this->bDoUnLock = TRUE;
            return FALSE;
        }

        echo '[ ALL DONE ] Total Process Project Counts='.intval($mResult)."\n";
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_sendpoints(TRUE);
EXIT;
?>