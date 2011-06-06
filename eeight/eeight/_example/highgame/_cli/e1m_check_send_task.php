<?php
/**
 * 中奖判断、派奖、追号单转注单顺序执行
 * 
 * /**
 * 路径: /_cli/check_send_task.php
 * 功能: 高频每一期停止销售后的 '中奖判断、派奖、追号单转注单顺序执行' 程序 ( * Crontab * )
 * 
 * 运行时间:
 * ~~~~~~~~~~~~~~~~~
 * 每个彩种每一期结束时运行一次
 *  
 * 命令行可接受参数:
 * ~~~~~~~~~~~~~~~~~ 
 *    argv[1] = 彩种ID
 *    argv[2] = loop 否循环进行转换(限开发用)
 * 
 * 
 * @author    mark
 * @version   1.0.0
 * @package   highgame
 * 
 */
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件
require( realpath(dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'checkbonus.php'); // 中奖判断类文件
require( realpath(dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'sendbonus.php'); // 派奖类文件
require( realpath(dirname(__FILE__)) .DIRECTORY_SEPARATOR. 'tasktoproject.php'); //追号单转注单类文件
class cli_check_send_task extends basecli 
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
            @unlink( $this->_getBaseFileName() . '_all_'. $this->iLotteryId. '.locks' );
        }
    }
    
    protected function _runCli()
    {
        // Step: 01 初步检测 CLI 参数合法性
        if( !isset($this->aArgv[1]) || !is_numeric($this->aArgv[1]) )
        {
            $this->halt('Error : Lottery ID #1001' );
        }
        $this->iLotteryId = intval($this->aArgv[1]);   // 彩种ID


        // Step: 02 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '_all_'. $this->iLotteryId. '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁
        
        $oClicheckbonus = new cli_checkbonus(TRUE);
        $oClisendbonus = new cli_sendbonus(TRUE);
        $oClitasktoproject = new cli_tasktoproject(TRUE);
        
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_check_send_task(TRUE);
EXIT;
?>