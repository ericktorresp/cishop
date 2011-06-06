<?php
/**
 * 文件 : /_app/model/issuehistory.php
 * 功能 : 数据模型 - 集中处理奖期历史
 *
 * @author     Floyd
 * @version    1.0.0
 * @package    highgame
 * @since      2010-02-25
 */

class model_issuehistory extends basemodel
{
    // ---------------------------------[ 属性 ]-------------------------------------------
    // 如果 $iStepCounts, $iStepSec 任何一个为 0, 则不做任何限制, 程序将不会进入 SLEEP 状态
    private $iStepCounts = 0;     // [配合] 每次处理 n 条后, PHP 睡眠 $this->iStepSec 秒
    private $iStepSec    = 0;     // [配合] 每次处理$this->$iStepCounts 条后, PHP 睡眠 n 秒

    private $iProcessMax = 0;     // 每次获取处理真实扣款的方案数, 即: Limit 数量, 0 为不限制
    private $iProcessRecord = 0;  // 本次执行更新的方案数量

    private $bLoopMode = FALSE;   // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行 
    
    private $sIssue     = '';     // 奖期号  issueinfo.issue
    private $aCode   = array();      // 中奖号
    private $iLotteryId = 0;      // 彩种ID

    // ---------------------------------[ 方法 ]-------------------------------------------
    /**
     * 根据彩种ID, 对奖期历史更新
     * @param int $iLotteryId
     * @return mix
     */
    public function doUpdateHistory( $iLotteryId )
    {
        $this->iLotteryId = intval($iLotteryId);

        // 1, 判断彩种ID 的有效性
        $oLottery   = A::singleton("model_lottery");
        $aRes = $oLottery->lotteryGetOne( ' `lotteryid` ,`numberrule`', " `lotteryid` = '".$this->iLotteryId."' " );
        if( empty($aRes) )
        {
            return -1001; // 彩种ID错误
        }

        $aValidNumString   = unserialize( $aRes['numberrule'] );
        unset($aRes,$oLottery);
        
        $sSql = 'SELECT code,issue,issueid FROM issuehistory WHERE (misseddata IS NULL OR misseddata=\'\') AND lotteryid=' . 
                $this->iLotteryId . ' ORDER BY issueid ASC LIMIT 1';
        $aRes = $this->oDB->getOne( $sSql );
        if( empty($aRes) )
        {
            return -1002; // 未获取到需要进行更新的奖期
        }
        $this->sIssue   = $aRes['issue'];  // 需进行同步的首个奖期编号
        echo "[d] [".date("Y-m-d H:i:s")."] -------[ Issue #".$this->sIssue." START ]-------------\n";
        if( strpos( $aRes['code'],' ' ) )
        {
            $this->aCode = split( ' ',$aRes['code'] );
            foreach( $this->aCode as $k=>$aw )
            {
                $this->aCode[$k] = intval($aw);
            }
        }
        else 
        {
            $this->aCode = str_split($aRes['code'],1);//拆分中奖号码每一位
        }

        /**
         * 3, 获取issuehistory表内上期记录，以计算本期遗漏数据
         */
        $aMissed = array();
        $aTotalmissed = array();
        $tmpTimes = array_count_values( $this->aCode );
        $aSeries = array();
        $aTotalseries = array();
        $sSql = 'SELECT * FROM issuehistory WHERE issueid<'.$aRes['issueid'].
                ' AND lotteryid='.$this->iLotteryId.' AND misseddata IS NOT NULL AND misseddata != \'\' ORDER BY issueid DESC LIMIT 1';
        $aHistory = $this->oDB->getOne( $sSql );
        if( !$aHistory )
        {
            // 无上期数据
            $tmpKey = range( $aValidNumString['startno'], $aValidNumString['endno'] );
            $aTotalmissed = array_fill_keys( $tmpKey, array('times'=>0,'missed'=>0) );
            $aTotalseries = array_fill_keys( $tmpKey, 0 );
            $aMissed = array_fill( 0, $aValidNumString['len'], array_fill_keys( $tmpKey, 0 ) );
            $aSeries = array_fill( 0, $aValidNumString['len'], array_fill_keys( $tmpKey, 0 ) );
            unset( $tmpKey );
        }
        else
        {
            $aMissed = @unserialize( $aHistory['misseddata'] );
            $aTotalmissed = @unserialize( $aHistory['totalmissed'] );
            $aSeries = @unserialize( $aHistory['series'] );
            $aTotalseries = @unserialize( $aHistory['totalseries'] );
        }

        //不分位最大遗漏 不分位最大连出
        foreach( $aTotalmissed AS $d => $m )
        {
            if( array_key_exists( $d, $tmpTimes ) )
            {
                $aTotalmissed[$d]['missed'] = 0;
                $aTotalmissed[$d]['times'] = $tmpTimes[$d];
                //不分位最大连出
                $aTotalseries[$d] = $aTotalseries[$d]+1;
            }
            else
            {
                $aTotalmissed[$d]['missed'] += 1;
                $aTotalmissed[$d]['times'] = 0;
                //不分位最大连出
                $aTotalseries[$d] = 0;
            }
        }
        //分位最大遗漏 分位最大连出
        foreach( $aMissed AS $k=>$v )
        {
            foreach( $v AS $digit=>$missed )
            {
                if( $digit == $this->aCode[$k] )
                {
                    $aMissed[$k][$digit] = 0;
                    //分位连出
                    $aSeries[$k][$digit] = $aSeries[$k][$digit]+1;
                }
                else 
                {
                    $aMissed[$k][$digit] = $missed+1;
                    //分位连出
                    $aSeries[$k][$digit] = 0;
                }
            }
        }
        /*print('Missed: ' . "\n");
        print_r($aMissed);
        print('Total missed: ' . "\n");
        print_r($aTotalmissed);
        print('Series: ' . "\n");
        print_r($aSeries);
        print('Total Series: ' . "\n");
        print_r($aTotalseries);
        die;*/
        $sMisseddata = serialize( $aMissed );
        $sTotalMissed = serialize( $aTotalmissed );
        $sSeries = serialize( $aSeries );
        $sTotalSeries = serialize( $aTotalseries );
        
        unset( $aMissed, $aTotalmissed, $aSeries, $aTotalseries );
        //4. 更新issuehistory表、issueinfo表，tranaction
        $aData = array(
            'misseddata'=>$sMisseddata,
            'totalmissed'=>$sTotalMissed,
            'series'=>$sSeries,
            'totalseries'=>$sTotalSeries
        );
        
        if( FALSE == $this->oDB->doTransaction() )
        {
            return -2001;
        }
        $this->oDB->update( 'issuehistory', 
                            $aData, 
                            "`lotteryid` = " . $this->iLotteryId . " AND `issue` = '" . $this->sIssue . "'" );
        if($this->oDB->errno() > 0)
        {
            $this->oDB->doRollback();
            return -2002;
        }
        $this->oDB->update( 'issueinfo', 
                            array('statussynced'=>1), 
                            "issue = '" . $this->sIssue . "' AND lotteryid=".$this->iLotteryId );
        if($this->oDB->errno() > 0)
        {
            $this->oDB->doRollback();
            return -2002;
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return -2003;
        }
        $this->iProcessRecord += 1;
        unset( $sMisseddata, $sTotalMissed, $sSeries, $sTotalSeries, $aData );
        if( $this->bLoopMode == TRUE )
        {
            return $this->doUpdateHistory( $this->iLotteryId ); // 递归
        }

        // 6, 返回负数表示错误, 正数表示本次 CLI 执行受影响的方案数
        return $this->iProcessRecord;
    }

    public function setLoopMode( $bLoopMode=FALSE )
    {
        $this->bLoopMode = (BOOL)$bLoopMode;
    }

    public function setSteps( $iStepCounts=0, $iStepSec=0 )
    {
        $this->iStepCounts = intval($iStepCounts);
        $this->iStepSec    = intval($iStepSec);
    }

    public function setProcessMax( $iNumber=0 )
    {
        $this->iProcessMax = intval($iNumber);
    }
}
?>