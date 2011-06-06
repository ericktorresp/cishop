<?php
/**
 * 彩种信息管理
 * 
 * 功能:
 *      1.获取彩种数据[getLottery]
 *      2.插入一条彩种记录[lotteryInsert]
 *      3.获取彩种列表[lotteryMethodGetList]
 *      4.更新彩种信息[lotteryUpdate]
 *      5.更新奖期规则[updateIssueSet]
 *      6.改变游戏状态[setStatus]
 * @author     mark,Rojer
 * @version    1.1.0
 * @package    highadmin
 *
 * issueset数据结构
 * [issueset] => Array
        (
            [0] => Array
                (
                    [starttime] => 10:00:00
                    [firstendtime] => 10:10:00
                    [endtime] => 22:00:00
                    [cycle] => 10
                    [endsale] => 60
                    [inputcodetime] => 60
                    [droptime] => 30
                    [status] => 1
                )
            [1] => Array
                (
                    [starttime] => 22:00:00
                    [firstendtime] => 22:05:00
                    [endtime] => 02:00:00
                    [cycle] => 5
                    [endsale] => 0
                    [inputcodetime] => 60
                    [droptime] => 30
                    [status] => 1
                )
        )
 */

class model_lottery extends basemodel
{
    /**
     * 获取彩种数据
     * @param $sCondition 查询条件
     * @param $bIsGetOne 是否只获取一条数据
     * @return array()
     * @author Rojer
     */
    public function getItem($itemId)
    {
        $sSql = "SELECT * FROM `lottery` where lotteryid=" . intval($itemId) . ' LIMIT 1';
        $result = $this->oDB->getOne($sSql);
        if ($result)
        {
            $result['issueset'] = unserialize($result['issueset']);
            //$result['numberrule'] = unserialize($result['numberrule']);
        }

        return $result;
    }

    /**
     * 取多条记录
     * @author rojer
     */
    public function getItems($lotteryType = NULL)
    {
        $sSql = "SELECT * FROM `lottery` ";
        if ($lotteryType !== NULL)
        {
            $sSql .= ' WHERE lotterytype = '.intval($lotteryType);
        }
        $aResult = $this->oDB->getAll($sSql);
        foreach ($aResult as &$v)
        {
            $v['issueset'] = unserialize($v['issueset']);
        }
        return $aResult;
     }
     
     /**
	 * 获取一个彩种信息
	 * @author mark
	 * @param  string $sFields
	 * @param  string $sCondition
	 * @return array
	 */
	public function lotteryGetOne( $sFields = '', $sCondition = '' )
	{
		$sFields    = empty($sFields) ? '*' : daddslashes($sFields);
		$sCondition = empty($sCondition) ? '1' : $sCondition;
		$sSql       = "SELECT ".$sFields." FROM `lottery` WHERE ".$sCondition . ' LIMIT 1';
		return $this->oDB->getOne( $sSql );
	}

    /**
	 * 插入一条彩种记录
	 * @author mark
	 * @param  array  $aLotteryData
	 * @return integer
	 */
	public function lotteryInsert( $aLotteryData = array() )
	{
	    if( !isset($aLotteryData) || empty($aLotteryData) )
	    { // 数据错误
	        return 0;
	    }
	    if( $aLotteryData["cnname"] == "" )
	    { // 彩种中文名称不存在
	        return -1;
	    }
	    $aLottery["cnname"] = daddslashes( $aLotteryData["cnname"] );
	    if( $aLotteryData["enname"] == "" )
	    { // 彩种英文名称不存在
	        return -2;
	    }
	    $aLottery["enname"] = daddslashes( $aLotteryData["enname"] );
	    if( !in_array( $aLotteryData["type"], array( 0, 1, 2, 3, 4, 5 ) ) )
	    { // 彩种类型错误
	        return -3;
	    }
	    $aLottery["lotterytype"] = $aLotteryData["type"];
	    $aLottery["weekcycle"] = array_sum($aLotteryData["date"]);
	    if( $aLottery["weekcycle"] == 0 )
	    { // 彩种周期错误
	        return -4;
	    }
	    $aLottery["sorts"] = isset($aLotteryData["sorts"]) ? intval($aLotteryData["sorts"]) : 0;
	    if( empty($aLotteryData["issuerule"]) )
	    { // 彩种的奖期规则错误
	        return -5;
	    }
	    if( !is_numeric($aLotteryData["mincommissiongap"]) ||
	           $aLotteryData["mincommissiongap"] < 0 || 
	           $aLotteryData["mincommissiongap"] >= 1 )
	    { // 彩种的返点差
	        return -6;
	    }
	    $aLottery["mincommissiongap"] = number_format($aLotteryData["mincommissiongap"],2);
	    if( !is_numeric($aLotteryData["minprofit"]) || $aLotteryData["minprofit"] <= 0 )
	    { // 彩种的最小留水
	        return -7;
	    }
	    $aLottery["minprofit"] = number_format($aLotteryData["minprofit"],2);
	    $aLottery["issuerule"] = $aLotteryData["issuerule"];
	    if( !empty($aLotteryData["yearlybreakstart"]) )
	    {
	        $aLottery["yearlybreakstart"] = getFilterDate( $aLotteryData["yearlybreakstart"],"Y-m-d" );
	        if($aLottery["yearlybreakstart"] == '')
	        {
	            $aLottery["yearlybreakstart"] = "0000-00-00";
	        }
	    }
	    if(!empty($aLotteryData["yearlybreakend"]))
	    {
	        $aLottery["yearlybreakend"]   = getFilterDate( $aLotteryData["yearlybreakend"],"Y-m-d" );
	        if($aLottery["yearlybreakend"] == '')
	        {
	            $aLottery["yearlybreakend"] = "0000-00-00";
	        }
	    }
	    if(isset($aLotteryData['yearlybreakstart']) && isset($aLotteryData['yearlybreakend']))
	    {
	        if( strtotime($aLottery["yearlybreakstart"]) > strtotime($aLottery["yearlybreakend"]) )
	        {
	            return -10;
	        }
	    }
	    if(!isset($aLotteryData['issueset']) || empty($aLotteryData['issueset']))
	    { // 奖期时间设置错误
	        return -8;
	    }
	    $aLotteryData['issueset'] = $this->getSystemizedIssueSetArray($aLotteryData['issueset']);
	    if( !is_array($aLotteryData['issueset']) )
	    { // 奖期时间不符合规范. 时间点上有冲突
	    	return -9;
	    }
	if( isset($aLotteryData["retry"]) )
        {
            if( !is_numeric($aLotteryData["retry"]) )
            {
                return -11;
            }
            $aLotteryData["retry"]=(int)($aLotteryData["retry"]);
            if( !is_int($aLotteryData["retry"]) || $aLotteryData["retry"] < 0 )
            {
                return -11;
            }
            $aLottery['retry'] = $aLotteryData["retry"];
        }
        if( isset ($aLotteryData["delay"]) )
        {
            if( !is_numeric($aLotteryData["delay"]) || 
                    $aLotteryData['delay']<30 ||
                    $aLotteryData['delay']>300 )
            {
                return -12;
            }
            $aLottery['delay'] = $aLotteryData["delay"];
        }
        if( isset ($aLotteryData["pushtime"]) )
        {
            if( !is_numeric($aLotteryData["pushtime"]) )
            {
                return -13;
            }
            $aLotteryData["pushtime"]=(int)($aLotteryData["pushtime"]);
            if( !is_int($aLotteryData["pushtime"]) || $aLotteryData["pushtime"] < 0 )
            {
                return -13;
            }
            $aLottery['pushtime'] = $aLotteryData["pushtime"];
        }
	    $aLottery['issueset']    = serialize($aLotteryData['issueset']);
	    $aLottery["description"] = daddslashes($aLotteryData["description"]);
	    $aLottery["numberrule"]  = serialize($aLotteryData["norule"]);
        
	    return $this->oDB->insert( 'lottery', $aLottery );
	}
	
	
	/**
     * 获取整理过的 "奖期阶段参数" 的数组
     * 需求:
     *     1, 按时间排序
     *     2, 检查时间段交叉
     *     3, 返回
     * @author Tom
     * @param  array  $aIssueSet    经控制器整理的数组
     *     $aIssueSet[0]['starttime']         销售开售时间
     *     $aIssueSet[0]['firstendtime']      第一期销售截止时间
     *     $aIssueSet[0]['endtime']           销售结束时间
     *     $aIssueSet[0]['cycle']             销售周期 (差异值,单位:秒)
     *     $aIssueSet[0]['endsale']           停售时间 (差异值,单位:秒)
     *     $aIssueSet[0]['inputcodetime']     号码录入时间 (差异值,单位:秒)
     *     $aIssueSet[0]['droptime']          撤单时间 (差异值,单位:秒)
     *     $aIssueSet[0]['status']            有效状态  0=无效, 1=有效
     *     $aIssueSet[0]['isFirst']           是否是当天第一个销售时间段
     * 
     * @return mix 
     *     执行成功: 返回整理过的数组
     *     执行失败: 返回负数
     *        -1001: 多个时间段有交集
     */
	private function getSystemizedIssueSetArray( $aIssueSet = array() )
	{	
		$aSystemizedArray = array();
		//print_rr($aIssueSet);exit;
		/* STEP 01:  整理所有阶段周期时间
		 * $aAllDaysBeginAndEnd 记录所有阶段的时间周期, 范例:
		 *    $aAllDaysBeginAndEnd => array(
		 *      编号 =>  '10:00:00-22:00:00'
		 * );
		 */ 
		$aAllDaysBeginAndEnd = array(); 
		foreach( $aIssueSet as $v )
		{ // 对数据进行整理
			$sBeginTime = $v['starttime'];
			$sEndTime   = $this->doDateHISOperation($v['endtime'],  '-'. intval($v['cycle']) .' seconds' );
			//echo $sBeginTime . '-' . $sEndTime .'<br/>';
			$aAllDaysBeginAndEnd[] = $sBeginTime. '-' .$sEndTime;
		}
		//print_rr($aAllDaysBeginAndEnd);exit;

		/* STEP 02:  对销售周期时间进行交集检查, 例:
		 *    第一时间段   10:00:00 - 22:00:00     ( 持续12小时,周期10分钟/期 )
		 *    第二时间段   22:00:00 - 03:00:00     ( 持续 5小时,周期 5分钟/期 )
		 * 
		 * 如果增加:
		 *    第三时间段   04:00:00 - 07:00:00     ( 持续 3小时,周期 3分钟/期 )
		 *    (应允许添加, 因为时间点未冲突)
		 * 
		 * 如果增加:    
		 *    第四时间段   06:00:00 - 07:00:00     ( 持续 3小时,周期 3分钟/期 )
		 *    (禁止, 因为时间段有交集)
		 */
        $iLoopTimes = count($aAllDaysBeginAndEnd) - 1;
        //echo 'looptimes='. $iLoopTimes .'<br/>';
        for( $i=0; $i<=$iLoopTimes; $i++ )
        { // 执行循环查询周期交集
            for( $y=$i+1; $y<=$iLoopTimes; $y++ )
        	{
                if( TRUE == $this->hasCycleInterSection( $aAllDaysBeginAndEnd[$i], $aAllDaysBeginAndEnd[$y] ) )
                {
                	return -1001; // 时间有交集
                }
        	}
        }

		return $aIssueSet;
	}
	
	/**
	 * 根据彩种ID, 号码判断是否符合规则
	 * @author Tom 090904 15:26
	 * @param int $iLotteryId
	 * @param string $sCode
	 * @return BOOL  TRUE|FALSE
	 */
	public function checkCodeFormat( $iLotteryId, $sCode='' )
	{
        if (!$aRow = self::getItem($iLotteryId))
        {
            return false;
        }
        $numberRule = unserialize($aRow['numberrule']);

        if( $aRow["lotterytype"]==0 ) //数字型
        {
            // aData = Array( [len] => 5, [startno] => 0, [endno] => 9 )
            $iCounts = preg_match_all("/^([".$numberRule["startno"]."-".$numberRule["endno"]."]{".$numberRule["len"]."})$/",$sCode,$aMatches);
            if( 1 != $iCounts )
            {
                return false;
            }
        }
        elseif( $aRow["lotterytype"]==1 )   // 乐透分区型
        {
            echo "号码判断待完成";
            return false;
        }
        elseif( $aRow["lotterytype"]==2 )   // 乐透同区型
        {
            /*
                (
                    [len] => 5
                    [startno] => 01
                    [endno] => 11
                    [startrepeat] =>
                    [splen] =>
                )
             */
           $tmp = explode(' ', trim($sCode));
           if (count($tmp) == $numberRule['len'])
           {
               // 判断是否可以重复 乐透型不可以重复
               if (count(array_unique($tmp)) != count($tmp))
               {
                   return false;
               }
               if ($numberRule['endno'] > 9)
               {
                   $preZero = true;
               }
               else
               {
                   $preZero = false;
               }
               foreach ($tmp as $v)
               {
                   if (intval($v) < intval($numberRule['startno']) || intval($v) > intval($numberRule['endno']))
                   {
                       return false;
                   }
                   if ($preZero && strlen($v) != strlen($numberRule['endno']))
                   {
                       return false;
                   }
               }
           }
        }

        return true;
	}

	/**
	 * 检查2个时间周期的交集
	 * 算法描述:  使用排除法. 穷举出无交集的可能性 (需考虑跨天)
	 * 
	 * 1, 不跨天情况:
	 * ------------------------------------------------------
	 *      (有交集)   
	 *            1, 交叉 : A=> 08:00:00-22:00:00  B=> 09:00:00-23:00:00
	 *            2, 包容 : A=> 08:00:00-22:00:00  B=> 09:00:00-21:00:00
	 *      (无交集)
	 *            1, A=> 08:00:00-22:00:00  B=> 22:00:01-23:00:00
	 *   
	 * 2, 跨天情况: (单方跨天)
	 * ------------------------------------------------------
	 *      (有交集)
	 *            1, 交叉 : A=> 08:00:00-04:00:00  B=> 03:00:00-06:00:00
     *            2, 包容 : A=> 08:00:00-04:00:00  B=> 02:00:00-04:00:00
	 * 
	 * 
	 * @param string $sTimeA
	 * @param string $sTimeB
	 * @author Tom
	 * @return BOOL
	 *    返回 TRUE   =  有交集
	 *    返回 FALSE  =  无交集 
	 */
	private function hasCycleInterSection( $sTimeA='08:00:00-22:00:00', $sTimeB='22:00:00-08:00:00' )
	{
		//echo 'A= '.$sTimeA. ' --> B= ' . $sTimeB.'<br/>';
		if( TRUE==$this->isOverOneDay($sTimeA) && TRUE==$this->isOverOneDay($sTimeB) )
		{ // 2个时间段同时跨天, 必定存在交集
			return TRUE;
		}

		$aTimeExplodeed   = explode( '-', $sTimeA );
		$aTimeArrayA['a'] = explode( ':', $aTimeExplodeed[0] );
		$aTimeArrayA['b'] = explode( ':', $aTimeExplodeed[1] );

		$aTimeExplodeed   = explode( '-', $sTimeB );
		$aTimeArrayB['a'] = explode( ':', $aTimeExplodeed[0] );
        $aTimeArrayB['b'] = explode( ':', $aTimeExplodeed[1] );
		//print_rr($aTimeArrayA);exit;

        $iTimeStartA = $aTimeArrayA['a'][0].$aTimeArrayA['a'][1].$aTimeArrayA['a'][2];
        $iTimeEndA   = $aTimeArrayA['b'][0].$aTimeArrayA['b'][1].$aTimeArrayA['b'][2];
        $iTimeStartB = $aTimeArrayB['a'][0].$aTimeArrayB['a'][1].$aTimeArrayB['a'][2];
        $iTimeEndB   = $aTimeArrayB['b'][0].$aTimeArrayB['b'][1].$aTimeArrayB['b'][2];

	    if( FALSE==$this->isOverOneDay($sTimeA) && FALSE==$this->isOverOneDay($sTimeB) )
        { // 2个时间段都不跨天
            if( $iTimeStartB>=$iTimeEndA || $iTimeStartA>=$iTimeEndB )
            {
            	return FALSE;
            }
        }

		if( TRUE==$this->isOverOneDay($sTimeA) || TRUE==$this->isOverOneDay($sTimeB) )
		{ // 其中有1个时间段跨天
			if( $iTimeEndA<=$iTimeStartB && $iTimeStartA>=$iTimeEndB )
			{
				return FALSE;
			}
		}

        return TRUE; // 经过穷举后, 默认返回 "有交集"
	}
	
	
	/**
	 * 指定的时间周期是否跨天
	 *
	 * @param string $sCycletime
	 * @return BOOL
	 *    返回 TRUE   =  跨天
	 *    返回 FALSE  =  不跨天
	 */
	private function isOverOneDay( $sCycletime = '08:00:00-22:00:00' )
	{
		/* $aTimes = array(
		 *     [0] => 08:00:00
         *     [1] => 22:00:00
		 * );
		 */
		$aTimes = explode( '-', $sCycletime ); 
		$iTimeStart   =  explode( ':', $aTimes[0] );
		$iTimeStart   =  $iTimeStart[0].$iTimeStart[1].$iTimeStart[2]; 
		$iTimeEndHour   =  explode( ':', $aTimes[1] );
		$iTimeEndHour   = $iTimeEndHour[0].$iTimeEndHour[1].$iTimeEndHour[2]; 
		//echo "s= $iTimeStart => e= $iTimeEndHour <br/> ";
		return ($iTimeEndHour<$iTimeStart) ? TRUE : FALSE;
	}


	/**
	 * 对 "小时:分钟:秒" 格式时间进行运算
	 *
	 * @param string $sTime
	 * @param string $sOperationDescription
	 * @return mix
	 *     执行成功: 返回计算成功后的字符串
	 *     执行失败: 返回全等于的 FALSE
	 */
	public function doDateHISOperation( $sTime='08:00:00', $sOperationDescription='-10 min' )
	{
		if( FALSE === ($sNewTime = strtotime( "2010-01-28 $sTime $sOperationDescription")) )
		{
			return FALSE;
		}
		return (string)date( 'H:i:s', $sNewTime );
	}



	/**
	 * 获取彩种列表[单表]
	 *
	 * @author  mark 
     * @access  public
	 * @param   string  $sFields
	 * @param   string  $sCondition
	 * @param   string  $sOrderBy
	 * @param   int     $iPageRecord
	 * @param   int     $iCurrentPage
	 * @return  array //返回记录结果集
	 */
	public function lotteryGetList( $sFields = '', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0 )
	{
	    $sFields = empty($sFields) ? "*" : addslashes($sFields);
	    if( $iPageRecord == 0 )
	    {//不分页显示
	        $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
	        $sSql       = "SELECT ".$sFields." FROM `lottery` ".$sCondition." ".$sOrderBy;
	        return $this->oDB->getAll( $sSql );
	    }
	    else
	    {
	        $sCondition = empty($sCondition) ? "1" : $sCondition;
	        return $this->oDB->getPageResult( 'lottery', $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy );
	    }
	}
	
	
	/**
	 * 获取彩种列表[联合玩法]
	 * @author mark
	 * @param  string  $sFields
	 * @param  string  $sCondition
	 * @param  string  $sOrderBy
	 * @param  integer $iPageRecord
	 * @param  integer $iCurrentPage
	 */
	public function lotteryMethodGetList( $sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
	{
		if( empty($sFields) )
		{ // 默认字段
			$sFields = "a.*,SUM(b.`isclose`) AS `count_status`,COUNT(b.`methodid`) AS `count_method`";
		}
		if( empty($sCondition) )
		{
			$sCondition = "1  GROUP BY  a.`lotteryid`";
		}
		else
		{
			$sCondition = $sCondition." GROUP BY a.`lotteryid`";
		}
		$iPageRecord  = intval( $iPageRecord );
		$sTableName ="`lottery` AS a LEFT JOIN `method` AS b ON (b.`lotteryid`=a.`lotteryid`)";
		if( $iPageRecord == 0 )
		{
			return $this->oDB->getAll("SELECT ".$sFields."FROM ".$sTableName." WHERE ".$sCondition.' '.$sOrderBy);
		}
		$iCurrentPage = intval( $iCurrentPage );
		if( $iCurrentPage == 0 )
		{
			$iCurrentPage = 1;
		}
		return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord,
			 $iCurrentPage, $sOrderBy );
	}
	
	
	/**
	 * 更新彩种信息
	 *
	 * @param array $aLotteryData
	 * @param string $sWhere
	 * @return int
	 */
	public function lotteryUpdate( $aLotteryData, $sWhere )
	{
        if( !isset($aLotteryData) || empty($aLotteryData) )
		{ // 数据错误
			return -1;
		}
		if( isset($aLotteryData["cnname"]) )
		{
			if( $aLotteryData["cnname"] == "" )
			{ // 彩种中文名称不存在
				return -2;
			}
			$aLottery["cnname"] = daddslashes( $aLotteryData["cnname"] );
		}
		if( isset($aLotteryData["enname"]) )
		{
			if( $aLotteryData["enname"] == "" )
			{ // 彩种英文名称不存在
				return -3;
			}
			$aLottery["enname"] = daddslashes( $aLotteryData["enname"] );
		}
		if( isset($aLotteryData["sorts"]) )
		{
		    $aLottery["sorts"] = intval($aLotteryData["sorts"]);
		}
		if( isset($aLotteryData["type"]) )
		{ // 不能修改类型
			return -4;
		}
		if( isset($aLotteryData["mincommissiongap"]) )
		{
			if( !is_numeric($aLotteryData["mincommissiongap"]) )
			{ // 彩种的返点差
				return -7;
			}
			$aLottery["mincommissiongap"] = number_format($aLotteryData["mincommissiongap"],3 );
			if(($aLottery["mincommissiongap"] >= 1)||($aLottery["mincommissiongap"] < 0))
			{
			    return -7;
			}
		}
		if( isset($aLotteryData["minprofit"]) )
		{
			if(!is_numeric($aLotteryData["minprofit"]))
			{ // 彩种的最小留水
				return -8;
			}
			$aLottery["minprofit"] = number_format( $aLotteryData["minprofit"], 3 );
			if( ($aLottery["minprofit"] >= 1)||($aLottery["minprofit"] < 0) )
			{
			    return -8;
			}
		}
		if( isset($aLotteryData["date"]) )
		{
			$aLottery["weekcycle"] = array_sum($aLotteryData["date"]);
			if( $aLottery["weekcycle"] == 0 )
			{ // 彩种周期错误
				return -5;
			}
		}		
		if( isset($aLotteryData["issuerule"]) )
		{
			if( empty($aLotteryData["issuerule"]) )
			{ // 彩种的奖期规则错误
				return -6;
			}
			$aLottery["issuerule"] = $aLotteryData["issuerule"];
		}
		if( isset($aLotteryData["yearlybreakstart"]) )
		{
			if( !empty($aLotteryData["yearlybreakstart"]) )
			{
				$aLottery["yearlybreakstart"] = getFilterDate( $aLotteryData["yearlybreakstart"],"Y-m-d" );
				if($aLottery["yearlybreakstart"] == '')
				{
				    $aLottery["yearlybreakstart"] = "0000-00-00";
				}
			}
			else
			{
				$aLottery["yearlybreakstart"] = "0000-00-00";
			}
		}
		if( isset($aLotteryData["yearlybreakstart"]) )
		{
			if(!empty($aLotteryData["yearlybreakend"]))
			{
				$aLottery["yearlybreakend"]   = getFilterDate( $aLotteryData["yearlybreakend"],"Y-m-d" );
				if($aLottery["yearlybreakend"] == '')
				{
				    $aLottery["yearlybreakend"] = "0000-00-00";
				}
			}
			else
			{
				$aLottery["yearlybreakend"]   = "0000-00-00";
			}
		}
		if( isset($aLotteryData['yearlybreakstart']) && isset($aLotteryData['yearlybreakend']) )
	    {
	        if( strtotime($aLottery["yearlybreakstart"]) > strtotime($aLottery["yearlybreakend"]) )
	        {
	            return -11;
	        }
	    }
		if( isset($aLotteryData["description"]) )
		{
			$aLottery["description"] = daddslashes($aLotteryData["description"]);
		}
		if( isset($aLotteryData["norule"]) )
		{
			$aLottery["numberrule"]  = serialize($aLotteryData["norule"]);
		}
		if( isset($aLotteryData["adjustminprofit"]) )
		{
			if( empty($aLotteryData["adjustminprofit"]) )
			{//彩种的公司最小留水错误
				return -9;
			}
			$aLottery["adjustminprofit"] = number_format($aLotteryData["adjustminprofit"],3,".","");
		}
		if( isset($aLotteryData['issueset']) )
	    {
	        $aLotteryData['issueset'] = $this->getSystemizedIssueSetArray($aLotteryData['issueset']);
	        if( !is_array($aLotteryData['issueset']) )
	        {
	            //奖期时间设置错误
	            return -10;
	        }
	        $aLottery['issueset']    = serialize($aLotteryData['issueset']);
	    }

        if( isset($aLotteryData["retry"]) )
        {
            if( !is_numeric($aLotteryData["retry"]) )
            {
                return -12;
            }
            $aLotteryData["retry"]=(int)($aLotteryData["retry"]);
            if( !is_int($aLotteryData["retry"]) || $aLotteryData["retry"] < 0 )
            {
                return -12;
            }
            $aLottery['retry'] = $aLotteryData["retry"];
        }
        if( isset ($aLotteryData["delay"]) )
        {
            if( !is_numeric($aLotteryData["delay"]) || 
                    $aLotteryData['delay']<30 ||
                    $aLotteryData['delay']>300 )
            {
                return -13;
            }
            $aLottery['delay'] = $aLotteryData["delay"];
        }
        if( isset ($aLotteryData["pushtime"]) )
        {
            if( !is_numeric($aLotteryData["pushtime"]) )
            {
                return -14;
            }
            $aLotteryData["pushtime"]=(int)($aLotteryData["pushtime"]);
            if( !is_int($aLotteryData["pushtime"]) || $aLotteryData["pushtime"] < 0 )
            {
                return -14;
            }
            $aLottery['pushtime'] = $aLotteryData["pushtime"];
        }
        
		return $this->oDB->update('lottery', $aLottery, $sWhere );
	}



	/**
	 * 更新奖期时间规则
	 * @author mark
	 * @param  array $aIssueSet      奖期规则数据
	 * @param  array $iLotteryId     彩种ID
	 * @param  boolean $bIsCreateNow 是否立即生效
	 * @return boolean 
	 */
	public function updateIssueSet( $aIssueSet = array(), $iLotteryId = 0, $bIsCreateNow = FALSE )
	{
	    if( !isset($aIssueSet) || !is_array($aIssueSet) )
	    {
	        return FALSE;
	    }
	    $aIssueSet = $this->getSystemizedIssueSetArray($aIssueSet);
	    if( !is_array($aIssueSet) )
	    {
	        //奖期时间设置错误
	        return -2;
	    }
	    $aData['issueset'] = serialize($aIssueSet);
	    if( !isset($iLotteryId) || !is_numeric($iLotteryId) )
	    {
	        return FALSE;
	    }
	    $iLotteryId = intval($iLotteryId);
	    $sWHere =  " `lotteryid` = '" . $iLotteryId ."'";
	    $iUpdateResult = $this->oDB->update( 'lottery', $aData, $sWHere);
	    if($iUpdateResult > 0)
	    {
	        if($bIsCreateNow)
	        {
	            //立即生效，产生相应的奖期,未完成
	        }
	    }
	    if( $this->oDB->errno() > 0 )
	    {
	        return -1;
	    }
	    else 
	    {
	        return $iUpdateResult;
	    }
	}
	
	
	/**
	 * 改变游戏状态
	 * @author mark
	 * @param  integer $iLotteryId
	 * @param  integer $iStatus
	 * @return bool
	 * Tom 效验通过于 0222 17:15
	 */
	function setStatus($iLotteryId, $iStatus)
	{
		if( !is_numeric($iLotteryId) )
		{
			return FALSE;
		}
		if( !in_array($iStatus, array(0,1)) )
		{
			return FALSE;
		}
		$this->oDB->query("UPDATE `method` SET `isclose`='".intval($iStatus)."' WHERE `lotteryid`='".$iLotteryId."'");
		if( $this->oDB->errno() > 0)
		{
		    return -1;
		}
		else 
		{
		    return $this->oDB->ar();
		}
	}

    /**
	 * 根据用户获取其开通的彩种列表
	 *
	 * @author james   090804
	 * @access public
	 * @param  int     $iUserId //用户ID
	 * @param  boolean $bIsTop     //是否为总代
	 * @param  string  $sFields    //要查询的内容
	 * @return array   //返回结果集
	 */
	public function & getLotteryByUser( $iUserId, $bIsTop=FALSE, $sFields="" )
	{
		$aResult = array();
		if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
		{
			return $aResult;
		}
		$iUserId = intval($iUserId);
		$sFields = empty($sFields) ? " l.`lotteryid`,l.`cnname`,l.`enname`,l.`mincommissiongap`, l.`lotterytype`"
		                             : daddslashes($sFields);
		if( $bIsTop == TRUE )
		{//如果为总代则结合总代奖金组设置表
			$sSql = " SELECT ".$sFields." FROM `lottery` AS l
			          LEFT JOIN `userprizegroup` AS upg ON l.`lotteryid`=upg.`lotteryid`
			          WHERE upg.`status`='1' AND upg.`userid`='".$iUserId."' GROUP BY l.`lotteryid` ";
		}
		else
		{//普通代理为结合用户玩法设置表
			$sSql = " SELECT ".$sFields." FROM `lottery` AS l
                      LEFT JOIN `userprizegroup` AS upg ON l.`lotteryid`=upg.`lotteryid`
                      LEFT JOIN `usermethodset`  AS ums ON ums.`prizegroupid`=upg.`userpgid`
                      WHERE upg.`status`='1' AND ums.`userid`='".$iUserId."' AND ums.`isclose`='0'
                      GROUP BY l.`lotteryid` ";
		}
		return $this->oDB->getAll( $sSql );
	}

    /**
	 * 根据彩种ID批量更新时间
	 *
	 * @param array    $aNewLottery
	 * @param integer  $iLotteryId
	 */
	function updateLotteryTime( $aNewLottery, $iLotteryId )
	{
		$aOldLottery = $this->oDB->getOne("SELECT `dailystart`,`dailyend`,`edittime`,`canceldeadline`,`dynamicprizestart`,`dynamicprizeend` FROM `lottery` where `lotteryid`='".$iLotteryId."'" . ' LIMIT 1');
		$iStartDiff = 0;
		$iEndDiff = 0;
		if($aOldLottery["dailystart"]>$aOldLottery["dailyend"])
		{
			if($aNewLottery["dailystart"]>$aNewLottery["dailyend"])
			{ //依旧是跨天开售
				if($aNewLottery["dailystart"]<>$aOldLottery["dailystart"])
				{
					$iStartDiff = strtotime($aNewLottery["dailystart"]) - strtotime($aOldLottery["dailystart"]);
				}
			}
			else
			{
			    if($aNewLottery["dailystart"]<>$aOldLottery["dailystart"])
                {
                    $iStartDiff = strtotime($aNewLottery["dailystart"])-strtotime($aOldLottery["dailystart"])+24*60*60;
                }
			}
		}
		else
		{
		    if($aNewLottery["dailystart"]>$aNewLottery["dailyend"])
            { //改成了跨天开售
                if($aNewLottery["dailystart"]<>$aOldLottery["dailystart"])
                {
                    $iStartDiff = strtotime($aNewLottery["dailystart"]) - strtotime($aOldLottery["dailystart"])-24*60*60;
                }
            }
            else
            { //依旧是非跨天销售
                if($aNewLottery["dailystart"]<>$aOldLottery["dailystart"])
                {
                    $iStartDiff = strtotime($aNewLottery["dailystart"]) - strtotime($aOldLottery["dailystart"]);
                }
            }
		}
        if($aNewLottery["dailyend"]<>$aOldLottery["dailyend"])
        {
            $iEndDiff = strtotime($aNewLottery["dailyend"]) - strtotime($aOldLottery["dailyend"]);
        }
        $this->oDB->doTransaction();
        if($iStartDiff<>0)
        {
	        $this->oDB->query("update `issueinfo` set `salestart`=addtime(`salestart`,sec_to_time(".$iStartDiff.")) where `lotteryid`='".$iLotteryId."' and `salestart` like '%".$aOldLottery["dailystart"]."'");
			if($this->oDB->errno()>0)
			{
				$this->oDB->doRollback();
				return -1; //销售开始时间调整
			}
        }
		if($iEndDiff<>0)
		{
			$this->oDB->query("update `issueinfo` set `saleend`=addtime(`saleend`,sec_to_time(".$iEndDiff.")) where `lotteryid`='".$iLotteryId."' and `salestart` like '%".$aOldLottery["dailystart"]."'");
	        if($this->oDB->errno()>0)
	        {
	        	$this->oDB->doRollback();
	        	return -2; //销售截至时间调整
	        }
		}
		//最后撤单时间
		if($aNewLottery["canceldeadline"]<>$aOldLottery["canceldeadline"])
        {
            $iCancelDiff = strtotime($aNewLottery["canceldeadline"]) - strtotime($aOldLottery["canceldeadline"]);
            $this->oDB->query("update `issueinfo` set `canneldeadline`=addtime(`canneldeadline`,sec_to_time(".$iCancelDiff.")) where `lotteryid`='".$iLotteryId."' and `canneldeadline` like '%".$aOldLottery["canceldeadline"]."'");
            if($this->oDB->errno()>0)
            {
            	$this->oDB->doRollback();
                return -3; //销售截至时间调整
            }
        }
        if($aNewLottery["dynamicprizestart"]<>$aOldLottery["dynamicprizestart"])
        {
        	$iDynamicprizestart = strtotime($aNewLottery["dynamicprizestart"]) - strtotime($aOldLottery["dynamicprizestart"]);
            $this->oDB->query("update `issueinfo` set `dynamicprizestart`=addtime(`dynamicprizestart`,sec_to_time(".$iDynamicprizestart.")) where `lotteryid`='".$iLotteryId."' and `dynamicprizestart` like '%".$aOldLottery["dynamicprizestart"]."'");
            if($this->oDB->errno()>0)
            {
                $this->oDB->doRollback();
                return -4; //开始调价时间
            }
        }
        if($aNewLottery["dynamicprizeend"]<>$aOldLottery["dynamicprizeend"])
        {
            $iDynamicprizeend = strtotime($aNewLottery["dynamicprizeend"]) - strtotime($aOldLottery["dynamicprizeend"]);
            $this->oDB->query("update `issueinfo` set `dynamicprizeend`=addtime(`dynamicprizeend`,sec_to_time(".$iDynamicprizeend.")) where `lotteryid`='".$iLotteryId."' and `dynamicprizeend` like '%".$aOldLottery["dynamicprizeend"]."'");
            if($this->oDB->errno()>0)
            {
                $this->oDB->doRollback();
                return -5; //调价结束时间
            }
        }
		if(isset($aNewLottery["date"]))
		{
			$aNewLottery["weekcycle"] = array_sum($aNewLottery["date"]);
			unset($aNewLottery["date"]);
		}
        $this->oDB->update('lottery',$aNewLottery,"`lotteryid`='".$iLotteryId."'");
        if($this->oDB->errno()>0)
        {
        	$this->oDB->doRollback();
        	return -7; //系统记录的时间
        }
        $this->oDB->doCommit();
        return 1;
	}
}