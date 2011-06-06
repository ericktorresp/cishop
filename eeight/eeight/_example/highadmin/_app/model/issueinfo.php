<?php

class model_issueinfo extends basemodel
{
    function __construct( $aDBO=array() )
    {
        parent::__construct($aDBO);
    }

    /**
     * [通用方法]得到单条记录
     * @param <int> $itemId
     * @return <array>
     * @author Rojer
     * `issueid`='4829' and `issue`='100129029' and `lotteryid`='1'
     */
    public function getItem($issueId = 0, $issue = '', $lotteryId = 0)
    {
        if (empty($issueId) && empty($issue) && empty($lotteryId))
        {
            sysMessage('无效的参数', 1);
        }
        $sSql = "SELECT * FROM `issueinfo` WHERE 1";
        if ($issueId > 0)
        {
            $sSql .= " AND issueid=" . intval($issueId);
        }
        if ($issue != '')
        {
            $sSql .= " AND issue='$issue'";
        }
        if ($lotteryId > 0)
        {
            $sSql .= " AND lotteryid=".intval($lotteryId);
        }
        $result = $this->oDB->getOne($sSql . ' LIMIT 1');

        return $result;
    }

    /**
     * 常用方法：得到时间段奖期列表
     * @param <type> $lotteryId
     * @param <type> $belongDate
     * @param <type> $orderBy
     * @param <type> $start
     * @param <type> $amount
     * @return <type>
     * @author  Rojer
     */
    function getItems($lotteryId = 0, $belongDate = '', $saleStartDate1 = 0, $saleStartDate2 = 0, $saleEndDate1 = 0, $saleEndDate2 = 0, $orderBy = '', $start = 0, $amount = -1)
    {
        $sql = "SELECT * FROM `issueinfo` WHERE 1";
        if ($lotteryId > 0)
        {
            $sql .= " AND lotteryid = ".intval($lotteryId);
        }
        if (is_array($belongDate) && count($belongDate) == 2)
        {
            $sql .= " AND belongdate >='{$belongDate[0]}' AND belongDate <= '{$belongDate[1]}'";
        }
        elseif ($belongDate != '')
        {
            $sql .= " AND belongdate = '$belongDate'";
        }

        if ($saleStartDate1 > 0)
        {
            $sql .= " AND salestart > '".date('Y-m-d H:i:s', $saleStartDate1)."'";
        }
        if ($saleStartDate2 > 0)
        {
            $sql .= " AND salestart < '".date('Y-m-d H:i:s', $saleStartDate2)."'";
        }

        if ($saleEndDate1 > 0)
        {
            $sql .= " AND saleend > '".date('Y-m-d H:i:s', $saleEndDate1)."'";
        }
        if ($saleEndDate2 > 0)
        {
            $sql .= " AND saleend < '".date('Y-m-d H:i:s', $saleEndDate2)."'";
        }
        
        if ($orderBy)
        {
            $sql .= " ORDER BY $orderBy";
        }
        if ($start)
        {
            $sql .= " LIMIT $start, $amount";
        }
        elseif ($amount > 0)
        {
            $sql .= " LIMIT $amount";
        }

        return $this->oDB->getAll($sql);
    }
    
    function getItemsByIssue($lotteryId, $issues)
    {
        if ($lotteryId <= 0 || !is_array($issues))
        {
            sysMessage('无效的参数', 1);
        }
        $sql = "SELECT * FROM `issueinfo` WHERE lotteryid=$lotteryId AND issue in('".implode("','", $issues)."')"." LIMIT ".count($issues);
        $result = array();
        foreach ($this->oDB->getAll($sql) as $v)
        {
            $result[$v['issue']] = $v;
        }
        
        return $result;
    }
    
    /*
    function getDrawItems($lotteryId, $startDate, $endDate, $limit = 50)
    {
        if ($lotteryId <= 0)
        {
            sysMessage('无效的奖期ID', 1);
        }
        $sSql = "SELECT * FROM `issueinfo` WHERE lotteryid=$lotteryId AND verifytime >= '".date('Y-m-d H:i:s', $startDate)."' AND verifytime <= '".date('Y-m-d H:i:s', $endDate)."' LIMIT $limit";
logdump($sSql);
        return $this->oDB->getAll($sSql);
    }
     *
     */

    /**
     * todo:得到列表
     * @param <type> $sFields
     * @param <type> $sCondition
     * @param <type> $sOrderBy
     * @param <type> $iPageRecord
     * @param <type> $iCurrentPage
     * @return <type>
     * @author Rojer
     */
    function issueGetList($sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0)
    {
        if( empty($sFields) )
        {
            $sFields = "*";
        }
        if( empty($sCondition) )
        {
            $sCondition = " 1 ";
        }
        $iPageRecord = is_numeric($iPageRecord) && $iPageRecord ? intval($iPageRecord) : 0;
        $sTableName = "`issueinfo` AS A LEFT JOIN `lottery` AS B on (A.`lotteryid`=B.`lotteryid`)";
        if( $iPageRecord==0 )
        {
            if( !empty($sOrderBy) )
            {
                $sOrderBy =" order by ".$sOrderBy;
            }
            return $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition. $sOrderBy);
        }
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy );
    }

    function issueGetList2($sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0)
    {
        if( empty($sFields) )
        {
            $sFields = "*";
        }
        if( empty($sCondition) )
        {
            $sCondition = " 1 ";
        }
        $iPageRecord = is_numeric($iPageRecord) && $iPageRecord ? intval($iPageRecord) : 0;
        $sTableName = "`issueinfo`";
        if( $iPageRecord==0 )
        {
            if( !empty($sOrderBy) )
            {
                $sOrderBy =" order by ".$sOrderBy;
            }
            return $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition. $sOrderBy);
        }
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy );
    }

    /**
     * 得到每天的奖期数 用于生成奖期的时候判断
     * @param <type> $lotteryId
     * @param <type> $startDate
     * @param <type> $endDate
     * @return <array>
     * @author Rojer
     * Tom 效验通过于 0222 16:11
     */
    public function getDayIssues($lotteryId, $startDate=0, $endDate=0 )
    {
        if( $lotteryId <= 0 )
        {
            sysMessage('彩种ID参数无效', 1);
        }
        $sSql = 'SELECT belongdate,count(*) AS count FROM `issueinfo` WHERE lotteryid='.intval($lotteryId);
        if( $startDate )
        {
            $sSql .= ' AND belongdate >='.date('Y-m-d', $startDate);
        }
        if( $endDate )
        {
            $sSql .= ' AND belongdate <='.date('Y-m-d', $endDate);
        }
        $sSql   .=  ' GROUP BY belongdate ORDER BY belongdate ASC';
        $tmp     =  $this->oDB->getAll($sSql);
        $result  =  array();
        foreach( $tmp as $v )
        {
            $result[$v['belongdate']] = $v['count'];
        }
        return $result;
    }


    /**
     * 得到当前奖期
     * @param <type> $issue
     * @return <type>
     * @author Rojer
     */
    public function getCurrentIssue($lotteryId, $date = 'CURRENT_TIME')
    {
        if ($date == 'CURRENT_TIME')
        {
            $date = time();
        }
        $date = date('Y-m-d H:i:s', $date);
        $sSql = "SELECT * FROM `issueinfo` WHERE lotteryid = ".intval($lotteryId).
                " AND `salestart` <= '$date' AND `saleend` >= '$date'" . ' LIMIT 1';

        return $this->oDB->getOne($sSql);
    }

    /**
     * 获取追号期内容
     *
     * @param unknown_type $iLotteryId
     */
    public function getTaskIssue( $iLotteryId, $sField='*' )
    {
        $aResult = array( "today"=>"", "tomorrow"=>"" );
        if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId<=0 )
        {
            return $aResult;
        }
        $iLotteryId = intval($iLotteryId);
        $sField     = daddslashes( $sField );
        $sToday     = date("Y-m-d");
        $sTomorrow  = date("Y-m-d",time()+86400);
        if( $iLotteryId ==6 && time() >= strtotime(date("Y-m-d 00:00:00")) && time() < strtotime(date("Y-m-d 03:00:00")) )
        {
            $sToday = date("Y-m-d",time()-86000);
            $sTomorrow = date("Y-m-d");
        }
        $sSql       = " SELECT ".$sField." FROM `issueinfo` WHERE lotteryid = ".$iLotteryId.
                      " AND `saleend` >= '".date("Y-m-d H:i:s")."' AND `statuscode`=0 ";
        $aResult['today']    = $this->oDB->getAll( $sSql." AND `belongdate`='".$sToday."'" );
        $aResult['tomorrow'] = $this->oDB->getAll( $sSql." AND `belongdate`='".$sTomorrow."'" );
        return $aResult;
    }

    /**
     * 得到最近应该开奖的奖期
     * @param <type> $lotteryId
     * @return <type>
     */
    public function getLastIssue($lotteryId)
    {
        $sSql = 'SELECT * FROM `issueinfo` WHERE lotteryid = '.intval($lotteryId)." AND earliestwritetime < '".date('Y-m-d H:i:s')."'".
            " AND belongdate IN('".date('Y-m-d', strtotime("-1 day"))."','".date('Y-m-d')."')"." ORDER BY issueid DESC LIMIT 1";
        if (!$aResult = $this->oDB->getOne($sSql))
        {
            return array();
        }

        return $aResult;
    }

    /**
     * 返回最近一期没有开奖的奖期, $onlyCompactIssue为真时，不取隔间奖期
     * @param <int> $lotteryId
     * @param <bool> $belowCurrentIssue
     * @return <array>
     * @author  Rojer
     */
    public function getLastNoDrawIssue($lotteryId, $onlyCompactIssue = false, $bIsCheckTime=true)
    {
        if ($lotteryId <=0)
        {
            return array();
        }
        
        $sSql = 'SELECT * FROM `issueinfo` WHERE lotteryid = '.intval($lotteryId).' AND statuscode < 2 ORDER BY issueid ASC' . ' LIMIT 1';
        if (!$aResult = $this->oDB->getOne($sSql))
        {
            return array();
        }
        if( $bIsCheckTime )
        {
            // 如果正在销售期，返回空
            if (date('Y-m-d H:i:s') < $aResult['earliestwritetime'])
            {
                return array();
            }
        }
        
        if ($onlyCompactIssue)
        {
            if ($currentIssue = self::getCurrentIssue($lotteryId))
            {
                if (self::getItems($lotteryId, '', 0, 0, strtotime($aResult['earliestwritetime']), strtotime($currentIssue['saleend'])))
                {
                    echo "已经隔了一期，返回空";
                    return array();
                }
            }
            else
            {
                return array();
            }
        }
        
        return $aResult;
    }

    /**
     * 奖期批量生成
     * @param <int> $lotteryId
     * @param <string> $firstIssue
     * @param <timestamp> $startDate
     * @param <timestamp> $endDate
     * @return <int>
     * @author Rojer
     * Tom 效验通过于 0222 17:26
     */
    public function generalIssue($lotteryId, $firstIssue, $startDate, $endDate)
    {
        $startDate = strtotime(date('Y-m-d', $startDate));
        $endDate   = strtotime(date('Y-m-d', $endDate));
        if( $startDate > $endDate || $endDate-$startDate > 86400*365 )
        {
            sysMessage('日期范围不合法！', 1);
        }
        $oLottery     = A::singleton("model_lottery");
        if( !$lottery = $oLottery->getItem($lotteryId) )
        {
            sysMessage('找不到彩种信息！', 1);
        }

        // 判断是否需要起始期号
        if (strpos($lottery['issuerule'], 'd') === false)
        {
            if (!$firstIssue)
            {
                sysMessage('没有天数的奖期规则必须指定起始期号！', 1);
            }
            if (!self::checkIssueRule($firstIssue, $lottery['issuerule']))
            {
                sysMessage('请正确输入起始奖期！', 1);
            }
        }

        $tmp  = reset($lottery['issueset']);
        $tmp2 = end($lottery['issueset']);
        $tmp2 = time2second($tmp2['endtime']);
        self::deleteItemByDate($lotteryId, $startDate);
        /**
         *
         * CQSSC:   100121054       ymd[n3]
         * JX-SSC:  20100121-036    Ymd-[n3]
         * HLJSSC:  0016571         [n7]
         * SSL:     20100121-11     Ymd-[n2]
         * SD11Y:   10012131        ymd[n2]
         * 格式符： y,m,d的值分别为0,1,0，0表示清零，1表示不清零
         */
        $rules = self::analyze($lottery['issuerule']);
        $totalCounter = 0;
        $curIssueNumber = intval(substr($firstIssue, 0-$rules['n']));// 获取期号，一般在最后几位
        for($i=$startDate; $i<$endDate; $i+=86400)
        {
            // 星期几？休市？
            $whatDayIsToday = date('w', $i);
            !$whatDayIsToday ? $whatDayIsToday += 7 : NULL;
            if (!(pow(2, $whatDayIsToday-1) & $lottery['weekcycle']))   // 星期天是2*6=64...
            {
                continue;
            }
            if ($i >= strtotime($lottery['yearlybreakstart']) && $i <= strtotime($lottery['yearlybreakend']))
            {
                continue;
            }

            $belongDate = date('Y-m-d', $i);    // 属于哪天的奖期
            $sample = $rules['sample'];
            // 先替换日期大部
            if ($rules['ymd'])
            {
                $sample = preg_replace('`([ymd]+)`ie', "date('\\1', $i)", $sample);
            }
            // 得到当前期号$curIssue
            if ($rules['n'])
            {
            // 如果按天清零，或者按年清零的时候跨年了，则数字部分从头开始
                if (!$rules['d'] || (!$rules['y'] && date('Y', $i) > date('Y', $startDate)))
                {
                    $curIssueNumber = 1;
                }
            }
            // 开始生成
            /*
             *     [0] => Array
                (
                    [starttime] => 05:00:00
                    [endtime] => 09:58:30
                    [cycle] => 10
                    [endsale] => 60
                    [inputcodetime] => 120
                    [droptime] => 60
                )
             */

            foreach ($lottery['issueset'] as $v)
            {
                if (!$v['status'])
                {
                    continue;
                }
                $startTime = time2second($v['starttime']);
                $endTime = time2second($v['endtime']);
                $isFirst = 0;
                if ($endTime < $startTime)
                {
                    $endTime += 86400;
                }

                for ($j=$startTime; $j<=$endTime-$v['cycle'];)
                {
                    $curIssueStartTime = date('Y-m-d H:i:s', $i+$j-$v['endsale']);
                    if (!$isFirst)
                    {
                        $curIssueEndTimeStamp = $i + time2second($v['firstendtime']);
                    }
                    else
                    {
                        $curIssueEndTimeStamp = $i+$j+$v['cycle'];
                    }
                    $curIssueEndTime = date('Y-m-d H:i:s', $curIssueEndTimeStamp-$v['endsale']);
                    $curDropTime = date('Y-m-d H:i:s', $curIssueEndTimeStamp-$v['droptime']);
                    $curInputCodeTime = date('Y-m-d H:i:s', $curIssueEndTimeStamp+$v['inputcodetime']);
                    $finalIssue = str_replace("[n{$rules['n']}]", str_pad($curIssueNumber, $rules['n'], '0', STR_PAD_LEFT), $sample);

                    // 写入
                    if (!self::addItem($lotteryId, $belongDate, $finalIssue, $curIssueStartTime, $curIssueEndTime, $curDropTime, $curInputCodeTime))
                    {
                        sysMessage("添加失败！($lotteryId, $belongDate, $finalIssue, $curIssueStartTime, $curIssueEndTime, $curDropTime)", 1);
                        return false;
                    }
                    if (!$isFirst)
                    {
                        $j = time2second($v['firstendtime']);
                    }
                    else
                    {
                        $j+=$v['cycle'];
                    }
                    $isFirst++;
                    $curIssueNumber++;
                    $totalCounter++;
                }
            }
        }

        return $totalCounter;
    }

    /**
     * 添加奖期
     * @param <int> $lotteryId
     * @param <string> $issue
     * @param <string> $salestart
     * @param <string> $saleend
     * @param <string> $droptime
     * @return <int>
     * @author Rojer
     */
    public function addItem($lotteryId, $belongDate, $issue, $salestart, $saleend, $droptime, $inputCodeTime)
    {
        if (empty($lotteryId) || empty($belongDate) || empty($issue) || empty($salestart) || empty($saleend) || empty($droptime) || empty($inputCodeTime))
        {
            sysMessage("无效的参数", 1);
        }
        $data = array(
            'lotteryid' => $lotteryId,
            'belongDate' => $belongDate,
            'issue' => $issue,
            'salestart' => $salestart,
            'saleend' => $saleend,
            'canneldeadline' => $droptime,
            'earliestwritetime' => $inputCodeTime,
        );
        // "REPLACE INTO issueinfo (`lotteryid`, `issue`, `salestart`, `saleend`, `canneldeadline`) VALUES('$lotteryId','$issue','$salestart','$saleend','$droptime');"
        $this->oDB->insert( 'issueinfo', $data );
        return $this->oDB->ar();
    }

     /**
	 * 删除奖期
	 *    只能删除未录入号码的奖期
	 *    或官方未开的奖期
	 * @param string $issueId
	 * @return bool or integer
	 * Tom 效验通过于 0222 14:55
	 *     可以考虑用数组的方式传递 issueid 从而提高执行效率
	 *     当前在 gameinfo->actionDeleteissue() 中批量删除50个记录,则需执行50条SQL..
	 */
	function deleteItem( $issueId )
	{
		if( $issueId <= 0 )
		{
			sysMessage('操作失败：无效的奖期ID', 1);
		}
        $sSql = "DELETE FROM issueinfo WHERE issueid=$issueId AND ((`statuscode`=0) OR (`statuscode`='2' AND `statusbonus`='2' AND `statususerpoint`='2' AND `statusdeduct`='2'))";
		$this->oDB->query($sSql);
        return $this->oDB->ar();
	}



    /**
     * 删除日期之后的奖期
     * @param <type> $salestart
     * @return <type> 
     * @author Rojer
     */
    public function deleteItemByDate($lotteryId, $belongdate = 0, $salestart = 0)
    {
        if ($lotteryId <= 0)
        {
            sysMessage('彩种ID非法', 1);
        }
        if (!$belongdate && !$salestart)
        {
            sysMessage('必须指定时间', 1);
        }
        $sql = 'DELETE FROM issueinfo WHERE lotteryid = '.intval($lotteryId);
        if ($belongdate)
        {
            $sql .= ' AND belongdate >= "'.date('Y-m-d', $belongdate). '"';
        }
        if ($salestart)
        {
            $sql .= ' AND salestart >= "'.date('Y-m-d H:i:s', $salestart). '"';
        }

        $this->oDB->query($sql);
        return $this->oDB->ar();
    }

    // 这里不能套用updateItem()
    public function delayIssueTime($lotteryId, $startIssue, $endIssue, $second)
    {
        // 开过号的明显不能动
        if ($startIssue >= $endIssue )
        {
            sysMessage( "结束奖期不能不大于开始奖期", 1);
        }
        // 如果不在开售期暂不判断
        if ($currentIssue = self::getCurrentIssue($lotteryId))
        {
            if ($startIssue <= $currentIssue['issue'])
            {
                sysMessage('开始奖期必须在当前期以后', 1);
            }
        }
        
        $sql = "UPDATE `issueinfo` SET salestart=ADDDATE(salestart, INTERVAL $second SECOND), saleend=ADDDATE(saleend, INTERVAL $second SECOND), ".
            " canneldeadline=ADDDATE(canneldeadline, INTERVAL $second SECOND), earliestwritetime=ADDDATE(earliestwritetime, INTERVAL $second SECOND)";
        $sql .= " WHERE issue >='$startIssue' AND issue <='$endIssue'";
        $this->oDB->query($sql);

        return $this->oDB->ar();
    }

    // 基本方法，由于底层封装的问题，不能用mysql函数
    public function updateItem($issueId, $data)
    {
        return $this->oDB->update("issueinfo", $data, "issueid=".intval($issueId));
    }

    /**
	 * 更新奖期的相关时间
	 *
	 * @param array $aOldIssue
	 * @param string $sCondition
     * @author Rojer
	 */
	function issueUpdateTime($iIssueId, $aOldIssue )
	{
		if(!isset($aOldIssue) || empty($aOldIssue))
		{
			return -1;
		}
		if(getFilterDate($aOldIssue["salestart"])!=$aOldIssue["salestart"])
		{
			return -2;
		}
		$aIssue["salestart"] = getFilterDate($aOldIssue["salestart"]);
		if(getFilterDate($aOldIssue["saleend"])!=$aOldIssue["saleend"])
		{
			return -3;
		}
		$aIssue["saleend"] = getFilterDate($aOldIssue["saleend"]);
		if(getFilterDate($aOldIssue["canneldeadline"])!=$aOldIssue["canneldeadline"])
		{
			return -4;
		}
		$aIssue["canneldeadline"] = getFilterDate($aOldIssue["canneldeadline"]);

        if (!($aIssue["salestart"] < $aIssue["canneldeadline"] && $aIssue["canneldeadline"] < $aIssue["saleend"]))
        {
            return -5;
        }

		if(empty($sCondition))
		{
			return -7;
		}
		return self::updateItem($iIssueId, $aIssue);
	}

    /**
	 * 录入号码或者是验证号码
	 * @author Rojer 100204
	 * @param string $sCode
	 * @param string $sCondition
	 * @return integer
	 */
	function drawNumber($iLotteryId, $sIssue, $sCode, $iRank, $iAdminId)
	{
		if ($iLotteryId<=0 || !$sIssue || !$sCode || $iRank <= 0 || $iAdminId <= 0)
		{
			sysMessage('Invalid argument', 1);
		}

        //彩种不存在
        $oLottery = A::singleton("model_lottery");
        if(!$aLottery = $oLottery->getItem($iLotteryId))
        {
            sysMessage( 'Error: non-exist lottery', 1);
        }

		if(!$issueInfo = self::getItem(0, $sIssue, $iLotteryId))
		{
			sysMessage( 'Error: non-exist issue', 1);
		}

		if($issueInfo["statuscode"] ==2)
		{
			sysMessage( 'Error: the code status had been verified', 1); //操作失败:号码状态为已验证.
		}

        if (!$lastNoDrawIssue = self::getLastNoDrawIssue($iLotteryId))
        {
            sysMessage( 'Error: Its not to the lottery time', 1); //操作失败:还没到开奖时间！
        }

        if (date('Y-m-d H:i:s') < $lastNoDrawIssue['earliestwritetime'])
        {
            sysMessage( 'Error: cannot encode ahead of time', 1); //操作失败:不能提前录号！
        }

        // 号码规则不对，肯定出错
        $aLottery["numberrule"] = @unserialize($aLottery["numberrule"]);
        if($aLottery["lotterytype"]==0) // 数字型
        {
            $matches =array();
            preg_match("/^[".$aLottery["numberrule"]["startno"]."-".$aLottery["numberrule"]["endno"]."]{".$aLottery["numberrule"]["len"]."}$/",$sCode,$matches);
            if(empty($matches))
            {
                sysMessage( 'Error: winning number must be 5 data', 1); //操作失败:号码格式不正确.
            }
        }
        elseif ($aLottery["lotterytype"]==2)    // 乐透同区型, 允许录号不加前导0，这里自动加上
        {
            $tmpArray = explode(' ', $sCode);
            if (count($tmpArray) != 5)
            {
                sysMessage( 'Error: winning number must be 5 data', 1); //操作失败:号码格式不正确.
            }
            $tmpArray2 = array();
            for ($i=0; $i<count($tmpArray); $i++)
            {
                $tmp = intval($tmpArray[$i]);
                if ($tmp < 1 || $tmp > 11)
                {
                    sysMessage( 'Error: winning number must be 5 data', 1); //操作失败:号码格式不正确.
                }
                $tmpArray2[] = str_pad($tmp, 2, '0', STR_PAD_LEFT);
            }
            $sCode = implode(' ', $tmpArray2);
        }
        elseif ($aLottery["lotterytype"]==3)    // 基诺型, 允许录号不加前导0，这里自动加上
        {
             $tmpArray = explode(' ', $sCode);
            if (count($tmpArray) != 20)
            {
                sysMessage( 'Error: winning number must be 20 data', 1); //操作失败:号码格式不正确.
            }
            $tmpArray2 = array();
            for ($i=0; $i<count($tmpArray); $i++)
            {
                $tmp = intval($tmpArray[$i]);
                if ($tmp < 1 || $tmp > 80)
                {
                    sysMessage( 'Error: winning number must be in the range[1-80] data', 1); //操作失败:号码格式不正确.
                }
                $tmpArray2[] = str_pad($tmp, 2, '0', STR_PAD_LEFT);
            }
            $sCode = implode(' ', $tmpArray2);
        }
        else
        {
            // 可以添加其他彩种的判断
            sysMessage( 'Error: It don\'t support the verification for this lottery', 1); //操作失败:对其他类型彩种的号码验证，暂不支持.
        }
        
        $iRank = $iRank + $issueInfo['rank'];
        $oConfig    = A::singleton("model_config");
        $aConfig = $oConfig->getConfigs( array("least_score", "person_score") );
        
        // 添加事务
        if( FALSE == $this->oDB->doTransaction())
        {
            sysMessage( 'Error: cannot start transaction', 1);
        }

		if($issueInfo["statuscode"] == 1)
		{
            //验证成功，需要检测两个身份是否重合
            if($issueInfo["writeid"] == intval($iAdminId))
            { //管理员为同一个人
                sysMessage( 'Error: you cannot encode and verify the same issue', 1); //操作失败:一个人不能同时录入号码和审核号码.
            }
            $aNewIssue = array('code' =>$sCode);
			if($sCode == $issueInfo["code"])
			{
				//更新相关的数据
				$aNewIssue['statuscode'] = 2;
				$aNewIssue["verifytime"]  =date("Y-m-d H:i:s");
				$aNewIssue["verifyid"] = intval($iAdminId);
                $aNewIssue["rank"] = $iRank;
				if(!$this->oDB->update('issueinfo', $aNewIssue, "lotteryid=$iLotteryId AND issue='$sIssue' AND `statuscode`='1'"))
				{ //更新失败
					sysMessage( 'Error:update failed', 1); //操作失败:更新失败.
				}
			}
			else
			{
                // 号码不一致属严重错误，重置0
                $iRank = 0; // 重要
                $aNewIssue["code"] = '';
                $aNewIssue["writetime"] ="0000-00-00 00:00:00";
                $aNewIssue["writeid"] = 0;
                $aNewIssue["statuscode"] = 0;
                $aNewIssue["rank"] = 0;
				$iResult = $this->oDB->update('issueinfo',$aNewIssue,"lotteryid=$iLotteryId AND issue='$sIssue' AND `statuscode`='1'");
				if($iResult === FALSE)
				{
					sysMessage( 'Error: DB update failed.', 1);   //操作失败:号码审核不正确,更新失败.
				}
				else
				{
                    // 提交事务
                    if (FALSE == $this->oDB->doCommit())
                    {
                        sysMessage( 'Error: commit transaction failed', 1);
                    }
					sysMessage( 'Data entries  don\'t match,  data encoded are cancelled ,please try again', 1);    // 号码审核不正确,需要重新输入号码.
				}
			}
		}
		else
		{
            //首次录入号码
			$aNewIssue["code"] = $sCode;
			$aNewIssue["writetime"] =date("Y-m-d H:i:s");
			$aNewIssue["writeid"] = intval($iAdminId);
            $aNewIssue["rank"] = $iRank;
            // 达到分值，直接开奖
            if ($iRank >= $aConfig['least_score'])
            {
                $aNewIssue["statuscode"] = 2;
            }
            else
            {
                $aNewIssue["statuscode"] = 1;
            }
            
			if(!$this->oDB->update("issueinfo",$aNewIssue,"lotteryid=$iLotteryId AND issue='$sIssue' AND `statuscode`='0'"))
			{
				sysMessage( '操作失败:录入号码失败.', 1);
			}
		}

        // 现在只看权重, 成功写开奖历史表issuehistory，不够返回权重值
        if ($iRank >= $aConfig['least_score'])
        {
            $flag = $this->oDB->query("insert into `issuehistory` (`lotteryid`,`issue`,`code`,`belongdate`) value('".
                $issueInfo["lotteryid"]."','".$issueInfo["issue"]."','".$sCode."','".$issueInfo["belongdate"]."')");
            if (!$flag)
            {
                if (!$this->oDB->doRollback())
                {
                    sysMessage( 'Error: rollback transaction failed', 1);
                }
                sysMessage( 'Error: update history failed', 1);
            }
            $oStatisticsLock = new model_statisticslock();
            $flag = $oStatisticsLock->updateLockBonusCode($issueInfo['lotteryid'], $issueInfo['issue'], $sCode);
            if (!$flag)
            {
                if (!$this->oDB->doRollback())
                {
                    sysMessage( 'Error: rollback transaction failed', 1);
                }
                sysMessage( 'Error: updateLockBonusCode failed', 1);
            }

            // 提交事务
            if (FALSE == $this->oDB->doCommit())
            {
                sysMessage( 'Error: commit transaction failed', 1);
            }
            
            return true;
        }

        // 提交事务
        if (FALSE == $this->oDB->doCommit())
        {
            sysMessage( 'Error: commit transaction failed', 1);
        }
        
        return $iRank;
	}

    /**
	 * 获取单个奖期
	 *
	 * @author james
     * @access public
	 * @param string $sField
	 * @param string $sCondition
	 * @return array
	 */
    function & IssueGetOne( $sField='', $sCondition='', $sLeftJoin='' )
    {
        $sField = empty($sField) ? '*' : daddslashes($sField);
        $sCondition = empty($sCondition) ? '' : ' WHERE '.$sCondition;
        $sTable = "`issueinfo` AS A ";
        return $this->oDB->getOne("SELECT ".$sField." FROM ".$sTable." ".$sLeftJoin." ".$sCondition . ' LIMIT 1');
    }
    
    /**
     * 更新奖期是否同步到封锁表的状态
     * @param int $iLotteyId 彩种ID
     * @param string $sWhere 更新条件
     * @return mixed
     * 
     * @author mark
     *
     */
    public function updateIssueStatusLocks($iLotteyId = 0, $sWhere = '1', $iStatusLocks = 0 )
    {
        if(!isset($iLotteyId) || $iLotteyId == 0 || $iStatusLocks == 0)
        {
            return FALSE;
        }
        $aData = array('statuslocks' => $iStatusLocks);
        return $this->oDB->update('issueinfo', $aData, "`lotteryid`='".$iLotteyId."' AND " . $sWhere );
    }

    /**
     * 检查奖期格式是否正确
     * @param <String> $sIssue
     * @param <String> $sIssuerule
     * @return <Boolean>
     * @author Rojer
     */
    public function checkIssueRule($sIssue, $sIssuerule)
    {
        if (!preg_match('`^\w+[\w-]+$`', $sIssue, $match))
        {
            return false;
        }

        $result =self::analyze($sIssuerule);
        if (strlen($sIssue) != $result['length'])
        {
            return false;
        }

        $pattern = preg_replace(array('`^[yY][md]*`i', '`\[(n)(\d+)\]`'), array("\\d{{$result['ymd_length']}}", "\\d{{$result['n']}}"), $result['sample']);
        if (!preg_match("`^{$pattern}$`i", $sIssue))
        {
            return false;
        }

        if ($result['ymd'] && $result['ymd_length'])
        {
            preg_match("`^\d{{$result['ymd_length']}}`i", $sIssue, $match);
            $date = date('Y-m-d', strtotime($match[0]));
            if ($date < '2010-01-01' || $date > '2038-01-19')
            {
                return false;
            }
        }
        
        return true;
    }

    /**
     * 私有方法 分析奖期规则
     * @param <string> $issuerule
     * @return <array>
     * @author Rojer
     */
    private function analyze($issuerule)
    {
        $tmp = explode('|', $issuerule);
        $result['sample'] = $tmp[0];
        $result['ymd'] = '';
        $result['n'] = 0;
        preg_match_all('`\[(n)(\d+)\]`', $tmp[0], $matches);
        if ($matches[1])
        {
            $result['n'] = $matches[2][0];
        }
        
        // must be ahead if exist date
        if (preg_match('`^[yY][md]*`i', $tmp[0], $match))
        {
            $result['ymd'] = $match[0];
        }

        $result['ymd_length'] = strlen(date($result['ymd']));
        $result['length'] = $result['n'];
        if ($result['ymd'])
        {
            $result['length'] += $result['ymd_length'];
        }
        $result['length'] += strlen(preg_replace(array('`^[yY][md]*`i', '`\[(n)(\d+)\]`i'), '', $result['sample']));

        $tmp3 = explode(',', $tmp[1]);
        $result['y'] = $tmp3[0] ? true : false;
        $result['m'] = $tmp3[1] ? true : false;
        $result['d'] = $tmp3[2] ? true : false;

        return $result;
    }
    
}

// 以下应放到公用函数库
function time2second($str)
{
    $tmp = explode(':', $str);
    return $tmp[0] * 3600 + $tmp[1] * 60 + $tmp[2];
}

function second2time($second)
{
    $result['hour'] = intval($second / 3600);
    $second -= $result['hour'] * 3600;
    $result['minute'] = intval($second / 60);
    $result['second'] = $second - $result['minute'] * 60;

    return $result['hour'] . ':' . $result['minute'] . ':' . $result['second'];
}

?>