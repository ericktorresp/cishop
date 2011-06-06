<?php

/**
 * 用来处理一些开奖异常情况
 * errortype: 1提前开奖 2号码录错 3未开奖
 */

class model_issueerror extends basemodel
{
    /**
     * 获取一个异常错误
     * @author Tom   090906 13:38
     * @param  string $sFields
     * @param  string $sCondition
     * @return array
     */
    public function errorGetOne( $sFields='', $sCondition='' )
    {
        $sFields    = empty($sFields) ? '*' : daddslashes($sFields);
        $sCondition = empty($sCondition) ? '1' : $sCondition;
        $sSql       = "SELECT ".$sFields." FROM `issueerror` WHERE ".$sCondition . ' LIMIT 1';
        return $this->oDB->getOne( $sSql );
    }


    /**
     * 获取最早的一条奖期异常
     *   1. 相同彩种ID,相同奖期编号,相同错误类型[撤销派奖|系统撤单]的数据只允许有一条.
     *   2. 不同异常类型的, 按写入时间先写先执行.
     * @author Tom 090906 13:38
     * @return array   如果没有获取到异常,则返回为空的数组 
     */
    public function getLastErrorRecord( $sType = 'cancelbonus' )
    {
        // 状态 (0:未开始, 1=进行中, 2=已完成, 9=被忽略)
        // 只获取未被忽略, 并且未完整执行成功的异常
        $sFields = $sType=='cancelbonus' ? 'statuscancelbonus' : 'statusrepeal';
        $sSql    = $sType=='cancelbonus' ? ' AND `type`=0 ' : ' AND `type`=1 ';
        return $this->errorGetOne( '', " `$sFields` NOT IN(2,9) $sSql ORDER BY `writetime` ASC");
    }


    /**
     * 写入 '撤销派奖' 任务请求
     * @param array $aOldIssueError
     * @return int
     * @author Rojer
     */
	function errorRecallInsert( $aOldIssueError=array())
	{
	    $aIssueError = array(); // 最终插入数据库数组  key=字段名 value=值
	    $aLocation[0] = array( "text"=>"撤销派奖", "href"=>url('draw','cancelbonus') );
	    // 1, 数据有效性判断
        /**
         * $aOldIssueError = array 
         * (
         *     [lottery] => 1        // 彩种ID
         *     [issue] => 2009239    // 奖期编号
         *     [type] => 1           // 1=提前开奖,关联 starttime.  
         *                           // 2=开奖号码错误,关联 issueno.
         *                           // 3=官方未开奖
         *     [starttime] =>        // 2009-09-13 21:43:00
         *     [issueno] => 333      // 新开奖号码
         * );
         */
	    if( !isset($aOldIssueError['lottery']) || !is_numeric($aOldIssueError["lottery"]) || !isset($aOldIssueError['type']) || !is_numeric($aOldIssueError["type"])
	       || !in_array( $aOldIssueError['type'], array(1,2,3,4) ) || empty( $aOldIssueError["issue"] )
	       || ( $aOldIssueError['type']==1 && empty($aOldIssueError['starttime']) ) || ( $aOldIssueError['type']==2 && empty($aOldIssueError['issueno'])))
	    {
	        return false;
	    }

	    $sNowTime = time();
        $aIssueError = array(
                'lotteryid' => intval($aOldIssueError["lottery"]),
                'issue' => daddslashes( $aOldIssueError["issue"] ),
                'errortype' => intval( $aOldIssueError['type'] ),
                'code' => '',   // 正确的开奖号码
            );
        $oIssueInfo = A::singleton("model_issueinfo");
        if (!$destinationIssueInfo = $oIssueInfo->getItem(0, $aOldIssueError["issue"], $aOldIssueError["lottery"]))
        {
            sysMessage('找不到目的奖期，或者还没到开奖时间！', 1, $aLocation);
        }

        $lastNoDrawIssue = $oIssueInfo->getLastNoDrawIssue($aOldIssueError["lottery"], false, false);

        if (!$lastNoDrawIssue || $lastNoDrawIssue['issue'] < $aOldIssueError["issue"])
        {
            sysMessage('不允许对未录号的奖期进行处理！', 1, $aLocation);
        }

        //$tmp = $oIssueInfo->getCurrentIssue($aIssueError["lotteryid"]);
        if( $aIssueError['errortype']==1 )  // 提前开奖, 晚于或等于这个时间的单子全部撤销
	    {
			$aIssueError["opentime"] = getFilterDate($aOldIssueError["starttime"]);
			if( '' == $aIssueError["opentime"] )
			{
			    sysMessage('时间格式错误', 1, $aLocation);
			}

            // 撤销派奖, 如果是撤销时间的, 需判断时间是否有效
            $iSaleStartTime = strtotime( $destinationIssueInfo['salestart'] );
            $iSaleEndTime   = strtotime( $destinationIssueInfo['saleend'] );
            $iOpenTime      = strtotime( $aIssueError["opentime"] );
            if( $iOpenTime <= $iSaleStartTime || $iOpenTime >= $iSaleEndTime )
            {
                sysMessage("所输入的时间不在期号为 {$destinationIssueInfo['issue']} 的开奖周期内！", 1, $aLocation);
            }
	    }
	    elseif( $aIssueError['errortype']==2 )  // 开奖号码错误
	    {
            if (!$destinationIssueInfo['code'])
            {
                sysMessage('还没有录号！', 1, $aLocation);
            }
            $oConfig = A::singleton("model_config");
            $issueexceptiontime = $oConfig->getConfigs( "issueexceptiontime" );
            if ($sNowTime - strtotime($destinationIssueInfo['salestart']) > $issueexceptiontime * 60)
            {
                sysMessage('撤消派奖时间已过，无法撤销派奖', 1, $aLocation);
            }
	        $aIssueError["code"] = daddslashes($aOldIssueError["issueno"]);
            $oLottery = A::singleton("model_lottery");
            if( TRUE !== $oLottery->checkCodeFormat( $aIssueError["lotteryid"], $aIssueError["code"] ) )
            {
                sysMessage('新录入的号码规则错误', 1, $aLocation);
            }
            
            // 重置封锁表中奖号码标记
            $oStatisticslock = A::singleton("model_statisticslock");
            $oStatisticslock->resetLockBonusCode( $aIssueError["lotteryid"], $aIssueError["issue"]);
            $oStatisticslock->updateLockBonusCode( $aIssueError["lotteryid"], $aIssueError["issue"], $aIssueError["code"] );
	    }
	    elseif( $aIssueError['errortype'] == 3 )    // 上期官方未开奖，$destinationIssueInfo应取上一期的数据
	    {
	    	if ($destinationIssueInfo['code'] || $destinationIssueInfo['statuscode'])
            {
                sysMessage('已经录号，不允许再进行“官方未开奖”的异常处理！', 1, $aLocation);
            }
	    }
	    else
	    {
	        sysMessage('处理原因错误', 1, $aLocation);
	    }

        // 增加重复数据的检测. 相同任务数据只允许一条
        if( TRUE == $this->hasSameTask( $aIssueError["lotteryid"], $aIssueError["issue"] ) )
        {
            sysMessage( '已有相同任务尚未完成', 1, $aLocation);
        }
        
        $aIssueError['oldcode']               = $destinationIssueInfo['code'];  // 旧的奖期开奖号码
	    $aIssueError['oldstatuscode']         = $destinationIssueInfo['statuscode']; // 旧的开奖奖期号码状态  0:未写入;1:写入待验证;2:已验证
	    $aIssueError['oldstatusdeduct']       = $destinationIssueInfo['statusdeduct']; // 旧的扣款状态(0:未完成;1:进行中;2:已经完成)
	    $aIssueError['oldstatususerpoint']    = $destinationIssueInfo['statususerpoint']; // 旧的返点状态(0:未开始;1:进行中;2:已完成)
	    $aIssueError['oldstatuscheckbonus']   = $destinationIssueInfo['statuscheckbonus']; // 旧的检查中奖状态(0:未开始;1:进行中;2:已经完成)
	    $aIssueError['oldstatusbonus']        = $destinationIssueInfo['statusbonus']; // 旧的返奖状态(0:未开始;1:进行中;2:已经完成)
	    $aIssueError['oldstatustasktoproject']= $destinationIssueInfo['statustasktoproject']; // 旧的追号单转注单状态(0:未开始;1:进行中;2:已经完成)
        $aIssueError["writetime"] = date('Y-m-d H:i:s', $sNowTime);
		$aIssueError["writeid"]   = intval( $_SESSION["admin"] );
		$this->oDB->insert( 'issueerror', $aIssueError );

		return $this->oDB->ar();
	}


	/**
	 * 检查是否有相同任务
	 * @author Tom 090904 16:14
	 * @param int $iLotteryId
	 * @param int $iIssue
	 * @return BOOL  TRUE=有相同任务,  FALSE=无相同任务
	 */
	public function hasSameTask( $iLotteryId, $sIssue )
	{
	    $iLotteryId = intval( $iLotteryId );
	    $sIssue     = daddslashes( $sIssue );
	    $this->oDB->query(
	       "SELECT 1 FROM `issueerror` WHERE  `lotteryid`='$iLotteryId'  AND `issue`='$sIssue' "
	       ." AND ( `statuscancelbonus` NOT IN(2,9) OR `statusrepeal` NOT IN(2,9) ) ");
	    return $this->oDB->ar() ? TRUE : FALSE ;
	}


    /**
     * 异常奖期列表获取
     * @param integer $iPageRecord 
     * @param integer $iCurrentPage 
     * @param integer $iErrorIssueId    错误奖期内容ID
     * @param boolean $bIsHavePage      是否需要分页
     * @author Tom,Rojer 2009-11-16 18:21
     */
    function issueExceptionList( $iPageRecord = 0, $iCurrentPage = 0, $iErrorIssueId = 0, $bIsHavePage = TRUE )
    {
        $iErrorIssueId = isset($iErrorIssueId) ? intval($iErrorIssueId) : 0;
    	$sFields = " ie.*, au.`adminname`, lo.`cnname` ";
    	$sCondition = " 1 ";
    	$sTableName = " `issueerror` ie LEFT JOIN `adminuser` au ON (ie.writeid=au.adminid) LEFT JOIN "
    	    ." `lottery` lo ON ( ie.`lotteryid` = lo.`lotteryid` ) ";
    	if( $bIsHavePage )
    	{
    	    $iPageRecord = is_numeric($iPageRecord) && $iPageRecord ? intval($iPageRecord) : 0;
    	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord, $iCurrentPage, ' ORDER BY ie.`entry` DESC ');
    	}
    	else 
    	{
    	    if( !isset($iErrorIssueId) || $iErrorIssueId == 0 )
    	    {
    	        return -1;
    	    }
    	    $sSql = " SELECT $sFields FROM $sTableName WHERE ie.entry = $iErrorIssueId AND $sCondition " . ' LIMIT 1';
    	    $aResult = $this->oDB->getOne( $sSql );
    	    return $aResult;
    	}
    }

}
?>