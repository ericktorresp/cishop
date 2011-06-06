<?php
/**
 * 路径: /_cli/2045_sendbonus.php
 * 功能: 高频停止销售后的奖金派送 ( * Crontab * )
 * 
 * 
 * 命令行可接受参数:
 * ~~~~~~~~~~~~~~~~~ 
 *    argv[1] = 彩种ID
 *    argv[2] = loop 否循环进行转换(限开发用)
 * 
 * 
 * @author    tom,mark
 * @version   1.2.0
 * @package   highgame
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_testchecksendtask extends basecli
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
        $iNumber = isset($this->aArgv[2]) ? intval($this->aArgv[2]) : 1; // 开奖期数

        // Step: 02 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '_id_'. $this->iLotteryId. '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            //$this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁

        for ($i=0; $i<$iNumber; $i++)
        {
            // 中奖
            $oCheckBonus = new model_checkbonus();
            if( isset($this->aArgv[2]) && strtolower($this->aArgv[2])=='loop' )
            {
                $oCheckBonus->setLoopMode(TRUE);
            }
            //sleep(1); // sleep for 7 sec. 防止与其他进程并发
            echo "[d] [".date("Y-m-d H:i:s")."] -------[ START ]-------------\n";
            $mResult = $oCheckBonus->doCheckBonus( $this->iLotteryId );

            // 派奖
            $oSendBonus = new model_sendbonus();
            if( isset($this->aArgv[2]) && strtolower($this->aArgv[2])=='loop' )
            {
                $oSendBonus->setLoopMode(TRUE);
            }
            //$oCongealtoReal->setProcessMax(1);
            //$oCongealtoReal->setSteps( 10000, 15 );  // 每处理1万条方案, 让 CLI 程序SLEEP 15秒
            //sleep(1); // sleep for 3 sec. 防止与其他进程并发
            echo "[d] [".date("Y-m-d H:i:s")."] -------[ START ]-------------\n";
            $mResult = $oSendBonus->doSendBonus( $this->iLotteryId );

            // 生成下期追号单
            //sleep(1);
            $oTaskToProject   = A::singleton("model_tasktoproject");
            $sResult = $oTaskToProject->traceToProject( $this->iLotteryId, false );
        }
        
        $sMsg = '';
        if( $mResult <= 0 )
        {
            switch( $mResult )
            {
                case -1001 : $sMsg = 'Wrong Lottery Id'; BREAK;
                case -1002 : $sMsg = 'ALL Done (All Issue)'; BREAK;
                case -1003 : $sMsg = 'Wrong IssueInfo (Issueid,Issue,code Err)'; BREAK;
                case -1004 : $sMsg = 'All Done (Single Issue)'; BREAK;
                case -1008 : $sMsg = 'Program holding! Waitting for table.IssueError Exception.'; BREAK;
                case -2001 : $sMsg = 'Transaction Start Failed.'; BREAK;
                case -2002 : $sMsg = 'Transaction RollBack Failed.'; BREAK;
                case -2003 : $sMsg = 'Transaction Commit Failed.'; BREAK;
                case -3001 : $sMsg = 'Issue Update Failed.'; BREAK;
                default :    $sMsg='Unknown ErrCode='.$mResult; BREAK;
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

$oCli = new cli_testchecksendtask(TRUE);
EXIT;
?>