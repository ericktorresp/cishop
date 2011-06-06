<?php
/**
 * 抓号任务
 *
 * /* test CQSSC网址
        $sources = array(
            //array('url' => 'http://video.shishicai.cn/Assist/BonusVideo.aspx?lt=4'),
            //array('url' => 'http://www.500wan.com/pages/info/ssc/'),
            //array('url' => 'http://lottery.sports.sohu.com/open/ssc.shtml'),
            //array('url' => 'http://ssc.starlott.com'),
            );
       // test HLJSSC网址
        $sources = array(
            //array('url' => 'http://video.shishicai.cn/Assist/BonusVideo.aspx?lt=21'),
            array('url' => 'http://ssc.caishijie.com/index.shtml?source=ssc.vodone.com'),
            );
        /* test JX-SSC网址
        $sources = array(
            array('url' => 'http://video.shishicai.cn/Assist/BonusVideo.aspx?lt=5'),
            //array('url' => 'http://www.2caipiao.com/jxssc/index.jhtml'),
            );
        /* test XJSSC网址
        $sources = array(
            //array('url' => 'http://video.shishicai.cn/Assist/BonusVideo.aspx?lt=17'),
            array('url' => 'http://www.xjflcp.com/openprize.do'),
            );
         */
        /* test SSL 网址
        $sources = array(
            array('url' => 'http://video.shishicai.cn/Assist/BonusVideo.aspx?lt=6'),
            //array('url' => 'http://lottery.sports.sohu.com/open/ssl.shtml'),
            //array('url' => 'http://ssl.starlott.com'),
            );
        /* test JX-11Y网址
        $sources = array(
            array('url' => 'http://video.shishicai.cn/Assist/BonusVideo.aspx?lt=23'),
            array('url' => 'http://jk.trade.500wan.com/pages/trade/dlc/'),
            );
 *
 *
 * @author    Rojer
 * @version   1.0.0
 * @package   highadmin
 *
 */
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件
error_reporting(E_ALL ^ E_NOTICE);
$oView = new view();

class cli_fetchnumber extends basecli
{
    const CLI_ADMIN_ID = 255;
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件
    private $iLotteryId = 0;       // 彩种ID  `lottery`.lotteryid
    private $_tmpIssueInfo;
    private $_debug = 0;
    /**
     * 析构
     */
    public function __destruct()
    {
        parent::__destruct();
        if ($this->bDoUnLock)
        {
            @unlink( $this->_getBaseFileName() . '_lotteryid_'. $this->iLotteryId. '.locks' );
            if (!$this->_debug)
            {
                $oIssueInfo  = A::singleton("model_issueinfo");
                $oIssueInfo->updateItem($this->_tmpIssueInfo['issueid'], array('statusfetch' => 2));
            }
        }
    }

    protected function _runCli()
    {
        //set_time_limit(1200);
        // Step: 01 初步检测 CLI 参数合法性
        if( !isset($this->aArgv[1]) || !is_numeric($this->aArgv[1]) )
        {
            $this->halt('Error : Lottery ID #1001' );
        }
        echo "\nCRON:".date('Y-m-d H:i:s')."\n";
        $this->iLotteryId = intval($this->aArgv[1]);
        $this->_debug = isset($this->aArgv[2]) ? intval($this->aArgv[2]) : 0;
        if ($this->_debug)
        {
            echo "*** Debug mode ***\n";
        }
        $oLottery = A::singleton("model_lottery");
        if( $this->aArgv[1] == "all" )   // 彩种ID
        {
            $lotteries = $oLottery->getItems();
        }
        else
        {
            $lotteries = array( $oLottery->getItem($this->iLotteryId) );
        }

        // Step: 02 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '_lotteryid_'. $this->iLotteryId. '.locks';
        if( file_exists( $sLocksFileName ) )
        {
            $stat = stat($sLocksFileName);
            // 防止进程死锁
            if( (time() - $stat['mtime']) > 1800 )
            {
                echo "文件被锁超过1800秒，被强制删除";
                @unlink($sLocksFileName);
            }
            else
            {
                $this->halt( '[' . date('Y-m-d H:i:s') .'] The CLI is running'."\n");
            }
        }
        $this->bDoUnLock = true;
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁

        // 每个彩种作为一个CLI独立运行
        $oIssueInfo  = A::singleton("model_issueinfo");
        $oDrawsource = A::singleton("model_drawsource");
        $oConfig     = A::singleton("model_config");
        $config      = $oConfig->getConfigs( array("least_score", "person_score", "delay_issues") );

        foreach( $lotteries as $lottery )
        {
            // 找出最近没有开奖的奖期（statuscode<2），
            // 预计是否能抓（比如当天的首期还没开奖，开奖开表是空的，这也不要紧，因为没取到数据不会返回致命错误）
            $lastIssue = $oIssueInfo->getLastIssue($lottery['lotteryid']);
            if (!$this->_debug)
            {
                if( !$lastNoDrawIssue = $oIssueInfo->getLastNoDrawIssue($lottery['lotteryid'], FALSE, FALSE) )
                {
                    echo "[".date('Y-m-d H:i:s')."]当前期正在销售，程序返回";
                    continue;
                }
                //======================   edit by james ========================
                $aPushtime = $oLottery->getItem($lottery['lotteryid']);
                $iPushtime = $aPushtime['pushtime'];
                $iPushtime = $iPushtime > 0 ? $iPushtime : 0;
                if( date('Y-m-d H:i:s') < $lastNoDrawIssue['saleend'] )
                {
                    echo "[".date('Y-m-d H:i:s')."]当前期正在销售，程序返回";
                    continue;
                }
                $tmpJamesT = strtotime($lastNoDrawIssue['earliestwritetime']) + $iPushtime;//allow run time
                $tmpJamesS = time();
                if( $tmpJamesS < $tmpJamesT )//early than run time
                {
                    if( ($tmpJamesT - $tmpJamesS) > 60 )
                    {//out of 60 seconds
                        echo "[".date('Y-m-d H:i:s')."]当前期完成销售，但是未到抓号时间，程序返回";
                        continue;
                    }
                    sleep($tmpJamesT - $tmpJamesS);
                }
                //-==================== end =====================================
                /*
                if ($lastIssue['statuscode'] >= 2)
                {
                    echo "上一期已经开完奖了[{$lastIssue['salestart']}-{$lastIssue['saleend']}，写号时间
                          {$lastIssue['writetime']}]，程序返回";
                    continue;
                }
                 */

                // 如果非最近销售完的一期。肯定是前面的几期没抓到，这期肯定也不会抓到，所以直接写历史表并返回
                if ($lastNoDrawIssue['issue'] != $lastIssue['issue'])
                {
                    /*
                    echo "[".date("Y-m-d H:i:s"). "] {$lottery['cnname']}\t最近没开奖的奖期{$lastNoDrawIssue['issue']} ".
                         "不是最近已售完奖期 {$lastIssue['issue']}[{$lastIssue['salestart']}-{$lastIssue['saleend']}]，".
                         "等待手工处理延迟奖期，程序返回";
                    $sources = $oDrawsource->getItems($lottery['lotteryid']);
                    foreach ($sources as $v)
                    {
                        $oDrawsource->addHistory($lottery['lotteryid'], $expectedIssue, $v['id'], $v['rank'], 31, '', 
                                                 0, 0, date('Y-m-d H:i:s'));
                    }
                     *
                     */
                    echo "[".date('Y-m-d H:i:s')."]注意：彩种{$lottery['cnname']} 奖期{$lastNoDrawIssue['issue']}".
                         "[{$lastNoDrawIssue['salestart']}-".substr($lastNoDrawIssue['saleend'], 11)."]不是紧临的上一期".
                         "{$lastIssue['issue']}[{$lastIssue['salestart']}-".substr($lastIssue['saleend'], 11)."]\n";

                    $delayIssuesNumber = $lastIssue['issueid'] - $lastNoDrawIssue['issueid'];
                    if( $delayIssuesNumber > $config['delay_issues'] )
                    {
                        // 1         2         3         4
                        // |---------|---------|---
                        // |---------|---------|---------|---
                        echo "{$lastNoDrawIssue['issue']}已经延后了 {$delayIssuesNumber} 期（{$lastIssue['issue']}），".
                             "已经超过配置设定值{$config['delay_issues']}，要求手工开奖，程序退出\n";
                        continue;
                    }
                }
            }
            else
            {
                $lastNoDrawIssue = $lastIssue;
            }

            $this->_tmpIssueInfo = $expectedIssueInfo = $lastNoDrawIssue;
            
            if (!$this->_debug)
            {
                if( $expectedIssueInfo['statusfetch'] == 1 )
                {
                    echo "因为程序已经在运行，这里直接退出。一般这里不会执行到，因为开始的时候有locks文件锁住了的\n";
                    continue;
                }
                elseif( $expectedIssueInfo['statusfetch'] == 2 )
                {
                    echo "程序已结束本期的抓取任务，rank={$expectedIssueInfo['rank']}，程序拒绝再次运行，".
                         "请尽快手工开奖第{$expectedIssueInfo['issue']}期！\n";
                    //echo __FILE__."::".__LINE__." 为调试方便，这里暂不退出\n";
                    continue;
                }
            }

            // 剩下的就是上一期还没开奖，程序也没跑过的，现在就开始跑
            if( !empty($this->aArgv[3]) && $this->aArgv[3] != 'default' )
            {
                 $expectedIssue = $this->aArgv[3];
            }
            else
            {
                $expectedIssue = $expectedIssueInfo['issue'];
            }
            $expectedDate = $expectedIssueInfo['belongdate'];

            // 只可能有两种情况：rank=0 或 0<rank<100
            // 该期只要程序跑过($expectedIssueInfo['statusfetch'] > 0)就不应该再跑了，
            //因此其实只要一个if($issueInfo['statusfetch'] > 0) continue;即可
            /*
            if ($issueInfo['rank'] > 0 && $issueInfo['rank'] < $config['least_score'])
            {
                echo "[".date("Y-m-d H:i:s"). "] {$lottery['cnname']}\t奖期 {$issueInfo['issue']}\t".
                     "之前执行过自动抓号（rank={$issueInfo['rank']}），不可以再次抓号！等待手工开奖！";
                continue;
            }
            elseif ($issueInfo['statusfetch'] > 0 && $debug)
            {
                echo "该期已经进行过抓取操作，但没有取到号码，程序不再抓取，等待手工处理！";
                continue;
            }
            else
            {
                die('非预期结果');
            }
             * 
             */
            
            // 设置状态
            if (!$this->_debug)
            {
                $oIssueInfo->updateItem($expectedIssueInfo['issueid'], array('statusfetch' => 1));
            }
            //
            // $expectedIssue;
            // select from drawhistory where lotteryid=1 && issue=$expectedIssue && retrytime < 6
            // 抓号，入库，完毕。录号可由本次程序运行生命期来判断
            /**
             * 奖期表增加一抓号状态，0未开始1正在2延迟3结束
             * fetchhistory结构：
             * historyid lotteryid issue sourceid number retry date
             * select from fetchhistory结构 where lotteryid=1 && issue=$expectedIssue && number!=''
             * 如果全部相同
             */
            
            try
            {
                $t1 = microtime(true);
                echo "[".date('Y-m-d H:i:s')."] 彩种 {$lottery['cnname']} 第 {$expectedIssue} 期".
                     "[{$lastNoDrawIssue['salestart']}-{$lastNoDrawIssue['saleend']}]".
                     "抓取工作开始 ********************************\n";
                $result = $oDrawsource->fetchDrawNumber($lottery, $expectedDate, $expectedIssue, $this->_debug);
                $t2 = microtime(true);
                //return array('number' => $tmp['number'], 'rank' => $rank);
                echo "#################################################################\n";
                echo "# 彩种\t\t奖期\t\t号码\t\t权值\t耗时\t#" . "\n";
                echo "# {$lottery['cnname']}\t\t{$expectedIssue}\t{$result['number']}".
                     (strlen($result['number']) < 8 ? "\t" : "")."\t{$result['rank']}\t" . round($t2-$t1, 3) . "\t#\n";
                echo "#################################################################\n";
                if ($result['rank'] >= $config['least_score'])
                {
                    echo "权值 {$result['rank']} >= {$config['least_score']}，达到分值，将直接开号！\n";
                }
                else
                {
                    /* 分值不够 */
                    echo "权值 {$result['rank']} < {$config['least_score']}，未达到分值，等待手工开号！\n";
                }

                if (!$this->_debug)
                {
                    $oIssueInfo->drawNumber($lottery['lotteryid'], $expectedIssue, $result['number'], $result['rank'],
                                        self::CLI_ADMIN_ID);
                }
            }
            catch (Exception $e)
            {
                switch(substr($e->getCode(), 0, 1))
                {
                    case 1:
                        echo "Error exception(errcode=".$e->getCode()."):".$e->getMessage(). " [".date('Y-m-d H:i:s')."]";
                        break;
                    case 2:
                        echo "Warning exception(errcode=".$e->getCode()."):".$e->getMessage(). " [".date('Y-m-d H:i:s')."]";
                        break;
                    case 3:
                        echo "Notice exception(errcode=".$e->getCode()."):".$e->getMessage(). " [".date('Y-m-d H:i:s')."]";
                        break;
                    default:
                        echo "Unknown exception(errcode=".$e->getCode()."):".$e->getMessage(). " [".date('Y-m-d H:i:s')."]";
                        break;
                }
                /* 异常 */
            }
            echo "\nEnd of ".date('Y-m-d H:i:s')." 抓取工作结束 *************************************\n\n\n\n";
        }

        
        echo "\n";
        return TRUE;
    }
}

$oCli = new cli_fetchnumber(TRUE);
/*
$oDrawsource = A::singleton("model_drawsource");
$lottery = array('lotteryid' => 7, 'cnname' => 'JX-11Y');
$result = $oDrawsource->fetchDrawNumber($lottery);
$result = $oDrawsource->fetchDrawNumber($lottery, '20100604-49');
 *
 */
EXIT;
?>