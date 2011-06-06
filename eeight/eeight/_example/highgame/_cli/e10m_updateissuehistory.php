<?php
/**
 * 路径: /_cli/e10m_updateissuehistory.php
 * 功能: 开奖后更新issuehistory
 * 
 * 
 *    argv[1] = 彩种ID
 *    argv[2] = loop 否循环进行转换(限开发用)
 * 
 * 
 * @author    Floyd     2010-02-21
 * @version   1.0.0
 * @package   highgame
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_updateissuehistory extends basecli
{
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件
    private $iLotteryId = 0;

    /**
     * 析构
     */
    public function __destruct()
    {
        parent::__destruct();
        if( $this->bDoUnLock == TRUE )
        {
            @unlink( $this->_getBaseFileName() . '_' . $this->iLotteryId . '.locks' );
        }
    }
    
    /**
     * 重写基类 _runCli() 方法
     */
    protected function _runCli()
    {
        if( !isset($this->aArgv[1]) || !is_numeric($this->aArgv[1]) )
        {
            $this->halt('Error : Lottery ID #1001' );
        }
        $this->iLotteryId = intval($this->aArgv[1]);   // 彩种ID
        
        // Step: 01 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '_' . $this->iLotteryId . '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            die('Error : The CLI is running' );
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁
        
        $oIssueHistory = new model_issuehistory();
        if( isset($this->aArgv[2]) && strtolower($this->aArgv[2])=='loop' )
        {
            $oIssueHistory->setLoopMode(TRUE);
        }
        
        sleep(3); // sleep for 3 sec. 防止与其他进程并发
        echo "[d] [".date("Y-m-d H:i:s")."] -------[ Lottery #".$this->iLotteryId." START ]-------------\n";
        
        $mResult = $oIssueHistory->doUpdateHistory( $this->iLotteryId );
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
                default : $sMsg = 'Unknown ErrCode='.$mResult; BREAK;
            }
            echo "[d] ".date('Y-m-d H:i:s')." Message: $sMsg\n";
            $this->bDoUnLock = TRUE;
            return FALSE;
        }
        echo "[d] ".date('Y-m-d H:i:s')." UPDATED: $mResult Issues.\n";
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_updateissuehistory(TRUE);
EXIT;
?>