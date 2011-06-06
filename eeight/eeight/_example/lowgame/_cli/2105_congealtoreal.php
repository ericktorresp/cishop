<?php
/**
 * 路径: /_cli/2105_congealtoreal.php
 * 功能: 低频停止销售后的冻结金额转为真实扣款 ( * Crontab * )
 * 
 * 运行时间:
 * ~~~~~~~~~~~~~~~~~
 *    21:00-01:59 每2分钟运行1次 PHP SLEEP FOR 15 secs
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

class cli_congealtoreal extends basecli
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
        $oCongealtoReal = new model_congealtoreal();
        if( isset($this->aArgv[2]) && strtolower($this->aArgv[2])=='loop' )
        {
            $oCongealtoReal->setLoopMode(TRUE); // 递归
        }
        //$oCongealtoReal->setProcessMax(1);
        //$oCongealtoReal->setSteps( 10000, 30 );  // 每处理1万条方案, 让 CLI 程序SLEEP 30秒
        sleep(15); // sleep for 15 sec. 防止与其他进程并发
        echo "[d] [".date('Y-m-d H:i:s')."]\n----------[ START ]------------------\n";
        $mResult = $oCongealtoReal->doCongealToReal( $this->iLotteryId );
        $sMsg = '';
        if( $mResult <= 0 )
        {
            switch( $mResult )
            {
                case -1001 : $sMsg = 'Wrong Lottery Id'; BREAK;
                case -1002 : $sMsg = 'ALL Done (All Issue)'; BREAK;
                case -1003 : $sMsg = 'All Done (Single Issue)'; BREAK;
                case -1008 : $sMsg = 'Program holding! Waitting for table.IssueError Exception.'; BREAK;
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

$oCli = new cli_congealtoreal(TRUE);
EXIT;
?>