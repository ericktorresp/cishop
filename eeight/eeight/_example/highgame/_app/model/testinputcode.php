<?php
/**
 * 文件 : /_app/model/model_testinputcode
 * 功能 : 数据模型 - 测试开奖号码
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highgame    
 */

class model_testinputcode extends basemodel
{
    /**
     * 更新开奖号码
     *
     * @return boolean
     */
    public function doTestInputCode( $iLotteryid = 0 )
    {
        $aUpdateCodeSql = array();
        #数字型：
        #CQSSC：
        if($iLotteryid == 1 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(1)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=1 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(1)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=1 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(1)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=1 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(1)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=1 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        #HLJSSC：
        if($iLotteryid == 2 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(2)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=2 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(2)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=2 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(2)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=2 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(2)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=2 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        if($iLotteryid == 3 || $iLotteryid == 0)
        {
            #JX-SSC：
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(3)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=3 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(3)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=3 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(3)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=3 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(3)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=3 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        #SSL：
        if($iLotteryid == 4 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(4)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=4 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(4)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=4 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(4)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=4 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(4)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=4 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        #SD11Y:
        if($iLotteryid == 5 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(5)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=5 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(5)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=5 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(5)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=5 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(5)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=5 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        #XJSSC：
        if($iLotteryid == 6 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(6)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=6 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(6)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=6 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(6)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=6 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(6)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=6 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        #JXDLC:
        if($iLotteryid == 7 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(7)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=7 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(7)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=7 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(7)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=7 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(7)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=7 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        //GD11Y
        if($iLotteryid == 8 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(8)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=8 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(8)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=8 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(8)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=8 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(8)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=8 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        //BJKL8
        if($iLotteryid == 9 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(9)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=9 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(9)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=9 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(9)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=9 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(9)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=9 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        #CQ11Y:
        if($iLotteryid == 10 || $iLotteryid == 0)
        {
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(10)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=10 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[0]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(10)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=10 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[147]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(10)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=10 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[258]$'";
            $aUpdateCodeSql[] =" UPDATE `issueinfo` SET `writetime`=NOW(),`writeid`='43',`verifytime`=NOW(),`verifyid`='44',`code`='".$this->makeRandCode(10)."',`statuscode`=2,`rank`=100,`statusfetch`=2 WHERE `lotteryid`=10 AND `statuscode`<2 AND `earliestwritetime`  <= NOW() AND `issue` REGEXP '[369]$'";
        }
        $oLottery = new model_lottery();
        $aLottery = $oLottery->lotteryGetOne( 'lotterytype', 'lotteryid='.$iLotteryid );
        if(empty($aLottery))
        {
            return FALSE;
        }
        $iLotteryType = intval($aLottery['lotterytype']);
        $oStatisticsLock = new model_statisticslock();
        switch ($iLotteryType)
        {
            case 0://数字型游戏
            $oCheckBonus = new model_checkbonus_digital();
            $oSendBonus = new model_sendbonus_digital();
            break;
            case 2://同区乐透型游戏
            $oCheckBonus = new model_checkbonus_lotto();
            $oSendBonus = new model_sendbonus_lotto();
            break;
            case 3://基诺型游戏
            $oCheckBonus = new model_checkbonus_keno();
            $oSendBonus = new model_sendbonus_keno();
            break;
            default://没有定义的游戏类型
            return FALSE;
            break;
        }
        $oTaskToProject   = new model_tasktoproject();
        foreach ($aUpdateCodeSql as $sSql)
        {
            $aSql = explode("WHERE", $sSql);
            $aCodeOne = explode(",", $sSql);
            $aCodeTwo = explode("=",$aCodeOne[4]);
            $sCode = str_replace("'","",$aCodeTwo[1]);
            $sHistorySql = " INSERT INTO `issuehistory` (`lotteryid`,`issue`,`code`,`belongdate`)
                 SELECT `lotteryid`,`issue`,'$sCode',`belongdate`  FROM `issueinfo` WHERE $aSql[1]";
            $this->oDB->query($sHistorySql);
            $sCurrentSql = "SELECT `lotteryid`,`issue` FROM `issueinfo` WHERE $aSql[1]";
            $aAllIssue = $this->oDB->getAll($sCurrentSql);
            foreach ($aAllIssue as $aIssue )
            {
                $oStatisticsLock->updateLockBonusCode($aIssue['lotteryid'],$aIssue['issue'],$sCode);
            }
            $this->oDB->query($sSql);
        }
        $sCurrentSql = " SELECT * FROM `issueinfo` WHERE (`statuscheckbonus`!= 2 OR `statusbonus`!= 2 OR
                `statustasktoproject`!= 2) AND `statuscode`=2 ";
        if($iLotteryid != 0)
        {
            $sCurrentSql .= " AND `lotteryid`='".$iLotteryid."'";
        }
        $aAllIssue = $this->oDB->getAll($sCurrentSql);
        foreach ($aAllIssue as $aIssue )
        {
            echo "[d] [".date("Y-m-d H:i:s")."] -------[ START ]-------------\n";
            $mResult = $oCheckBonus->doCheckBonus( $aIssue['lotteryid'] );
            $oCheckBonus->setLoopMode(TRUE);
            $sMsg = '';
            if( $mResult <= 0 )
            {
                switch( $mResult )
                {
                    case -1001 : $sMsg = 'Wrong Lottery Id'; BREAK;
                    case -1002 : $sMsg = 'ALL DONE (All Issue)'; BREAK;
                    case -1003 : $sMsg = 'Wrong IssueInfo'; BREAK;
                    case -1004 : $sMsg = 'ALL DONE (Single Issue)'; BREAK;
                    case -1005 : $sMsg = 'Data Init Failed!'; BREAK;
                    case -1006 : $sMsg = 'doProcess() Method Not Exists!'; BREAK;
                    case -1007 : $sMsg = 'doProcess() Update Failed!'; BREAK;
                    case -1008 : $sMsg = 'Program holding! Waitting for table.IssueError Exception.'; BREAK;
                    case -2001 : $sMsg = 'Transaction Start Failed.'; BREAK;
                    case -2002 : $sMsg = 'Transaction RollBack Failed.'; BREAK;
                    case -2003 : $sMsg = 'Transaction Commit Failed.'; BREAK;

                    case -3001 : $sMsg = 'issueinfo.statuscheckbonus Update Failed.'; BREAK;

                    default : $sMsg='Unknown ErrCode='.$mResult; BREAK;
                }
                echo "[d] ".date('Y-m-d H:i:s')." Message: $sMsg\n\n";
            }
            echo '[ ALL DONE ] Total Process Counts='.intval($mResult)."\n\n";
            echo "[d] [".date("Y-m-d H:i:s")."] -------[ START ]-------------\n";
            $mResult = $oSendBonus->doSendBonus( $aIssue['lotteryid'] );
            $oSendBonus->setRunTimes(5);
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
                    default : $sMsg='Unknown ErrCode='.$mResult; BREAK;
                }
                echo "[d] ".date('Y-m-d H:i:s')." Message: $sMsg\n";
            }
            echo '[ ALL DONE ] Total Process Project Counts='.intval($mResult)."\n";
            $sResult = $oTaskToProject->traceToProject( $aIssue['lotteryid'], TRUE , 10 );
            echo '[d] [' . date('Y-m-d H:i:s') ."] lottery[".$aIssue['lotteryid']."]=>: ".$sResult."\n";
        }
        if( $this->oDB->errno() > 0 )
        {
            return FALSE;
        }
        return TRUE;
    }


    /**
     * 生成随机号码
     *
     * @param int $iLotteryId
     * @return string
     */
    private function makeRandCode( $iLotteryId = 0 )
    {
        if( $iLotteryId == 1 || $iLotteryId == 2 || $iLotteryId == 3 || $iLotteryId == 6)
        {
            $sCode = '';
            for($i = 0; $i < 5; $i++)
            {
                $sCode .= mt_rand(0,9);
            }
            return $sCode;
        }
        elseif($iLotteryId == 4)
        {
            $sCode = '';
            for($i = 0; $i < 3; $i++)
            {
                $sCode .= mt_rand(0,9);
            }
            return $sCode;
        }
        elseif(in_array($iLotteryId,array(5, 7, 8,10)))
        {
            $aCode = array();
            while (count($aCode) != 5)
            {
                $sCode = mt_rand(1,11);
                $sCode = strlen($sCode) < 2 ? str_pad($sCode, 2, '0', STR_PAD_LEFT ) : $sCode;
                if(in_array($sCode,$aCode))
                {
                    continue;
                }
                else
                {
                    $aCode[] = $sCode;
                }
            }
            return implode(" ",$aCode);
        }
        elseif ($iLotteryId == 9)
        {
            $aCode = array();
            while (count($aCode) != 20)
            {
                $sCode = mt_rand(1,80);
                $sCode = strlen($sCode) < 2 ? str_pad($sCode, 2, '0', STR_PAD_LEFT ) : $sCode;
                if(in_array($sCode,$aCode))
                {
                    continue;
                }
                else
                {
                    $aCode[] = $sCode;
                }
            }
            sort($aCode);
            return implode(" ",$aCode);
        }
    }
}