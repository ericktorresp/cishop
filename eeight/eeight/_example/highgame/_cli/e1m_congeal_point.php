<?php
/**
 * 路径: /_cli/2103_sendpoints.php
 * 功能: 高频停止销售后的用户返点派发程序 ( * Crontab * )
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
 *  方便测试用
SELECT * FROM `projects` WHERE userid in(200315,200316,200321,200338) && writetime>'2010-03-22 00:00:00';
SELECT * FROM `orders` WHERE fromuserid in(200315,200316,200321,200338) && actiontime>'2010-03-22 00:00:00' order by entry;
SELECT * FROM `userdiffpoints` WHERE userid IN ( 200315, 200316, 200321, 200338 ) ORDER BY `entry` DESC;
SELECT * FROM `userfund` WHERE userid IN ( 200315, 200316, 200321, 200338 );
#数字型：
#CQSSC：
update `issueinfo` set code='00100',statuscode=2 WHERE lotteryid=1 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[0]$';
update `issueinfo` set code='12345',statuscode=2 WHERE lotteryid=1 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[147]$';
update `issueinfo` set code='12358',statuscode=2 WHERE lotteryid=1 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[258]$';
update `issueinfo` set code='69369',statuscode=2 WHERE lotteryid=1 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[369]$';
#HLJSSC：
update `issueinfo` set code='00100',statuscode=2 WHERE lotteryid=2 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[0]$';
update `issueinfo` set code='12345',statuscode=2 WHERE lotteryid=2 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[147]$';
update `issueinfo` set code='12358',statuscode=2 WHERE lotteryid=2 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[258]$';
update `issueinfo` set code='69369',statuscode=2 WHERE lotteryid=2 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[369]$';
#JX-SSC：
update `issueinfo` set code='00100',statuscode=2 WHERE lotteryid=3 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[0]$';
update `issueinfo` set code='12345',statuscode=2 WHERE lotteryid=3 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[147]$';
update `issueinfo` set code='12358',statuscode=2 WHERE lotteryid=3 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[258]$';
update `issueinfo` set code='69369',statuscode=2 WHERE lotteryid=3 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[369]$';
#SSL：
update `issueinfo` set code='001',statuscode=2 WHERE lotteryid=4 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[0]$';
update `issueinfo` set code='147',statuscode=2 WHERE lotteryid=4 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[147]$';
update `issueinfo` set code='258',statuscode=2 WHERE lotteryid=4 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[258]$';
update `issueinfo` set code='369',statuscode=2 WHERE lotteryid=4 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[369]$';
#SD11Y:
update `issueinfo` set code='02 03 05 07 11',statuscode=2 WHERE lotteryid=5 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[0]$';
update `issueinfo` set code='08 03 02 10 07',statuscode=2 WHERE lotteryid=5 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[147]$';
update `issueinfo` set code='04 06 01 02 08',statuscode=2 WHERE lotteryid=5 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[258]$';
update `issueinfo` set code='05 06 07 08 09',statuscode=2 WHERE lotteryid=5 && statuscode!=2 && salestart <= NOW() && issue REGEXP '[369]$';
 * 
 *
 *
 *
 * 
 * @author    Tom,Rojer
 * @version   1.2.0
 * @package   highgame
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_congeal_point extends basecli
{
    private $iLotteryId = 0;       // 彩种ID
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件

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

        // 扣款
        $oCongealtoReal = new model_congealtoreal();
        $oCongealtoReal->setAmount(isset($this->aArgv[2]) ? $this->aArgv[2] : 1);
        $mResult = $oCongealtoReal->doCongealToReal( $this->iLotteryId );
        echo '（flag='.intval($mResult)."）\n";
        
        // 返点
        $oSendPoints = new model_sendpoints();
        $oSendPoints->setAmount(isset($this->aArgv[2]) ? $this->aArgv[2] : 1);
        $mResult = $oSendPoints->doSendPoints( $this->iLotteryId );
        echo '（flag='.intval($mResult)."）\n";
        
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_congeal_point(TRUE);
EXIT;
?>