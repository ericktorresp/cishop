<?php
/**
 * 路径: /_cli/e1m_issueexception.php
 * 功能: 每分钟运行的'奖期异常处理[官方未开奖，提前开奖，开错奖]' ( * Crontab * )
 * 
 * 运行时间:
 * ~~~~~~~~~~~
 *    20:20-20:59 每分钟运行1次
 *    21:00-01:50 每分钟运行1次
 * 
 * 
 * 流程摘要:
 *     1, SQL语句检查奖期异常表 issueerror 是否有异常处理的任务
 *        如果发现有异常任务, 则抢占优先级. 使其他运行中的CLI自行终止 例:{真实扣款|集中返点|中奖判断|奖金派发..}
 *     2, 根据任务模式 [官方未开奖，提前开奖，开错奖] 对相关方案进行相关操作
 *        + 2.1  官方未开奖   :  对整期进行系统撤单 
 *        + 2.2  提前开奖     :  撤消指定一段时间内的派奖, 然后对这段时间内的方案进行撤单
 *        + 2.3  开错奖       :  撤消整期的派奖, 然后需进行重新中奖判断+派奖
 * 
 * @author    James,TOM,Rojer
 * @version   1.2.0
 * @package   highgame
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_issueException extends basecli
{
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件

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
        // Step: 01 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '.locks';
        if( file_exists( $sLocksFileName ) )
        {
            die('Error : The CLI is running' );
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁

        // Step: 02 调用模型
        $oGameCancel = new model_gamecancel();
        //$oCongealtoReal->setProcessMax(1);
        //$oCongealtoReal->setSteps( 10000, 15 );  // 每处理1万条方案, 让 CLI 程序SLEEP 15秒
        $mResult = $oGameCancel->doException();

        echo "\n[".date("Y-m-d H:i:s")."] MESSAGE= ".$mResult ."\n";
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_issueException(TRUE);
EXIT;
?>