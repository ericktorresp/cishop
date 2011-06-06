<?php
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
        $sSql       = "SELECT ".$sFields." FROM `issueerror` WHERE ".$sCondition;
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
        return $this->errorGetOne( '', " `$sFields` NOT IN(2,9) $sSql ORDER BY `writetime` ASC LIMIT 1 ");
    }


    /**
     * 写入 '撤销派奖' 任务请求
     * @param array $aOldIssueError
     * @return int
     */
	function errorRecallInsert( $aOldIssueError=array() )
	{
	    $aIssueError = array(); // 最终插入数据库数组  key=字段名 value=值
	    // 1, 数据有效性判断
        /**
         * $aOldIssueError = array 
         * (
         *     [lottery] => 1        // 彩种ID
         *     [issue] => 2009239    // 奖期编号
         *     [type] => 1           // 1=提前开奖,关联 starttime.  
         *                           // 2=开奖号码错误,关联 issueno.
         *     [starttime] =>        // 2009-09-13 21:43:00
         *     [issueno] => 333      // 新开奖号码
         * );
         */
	    if( !isset($aOldIssueError['lottery']) || !is_numeric($aOldIssueError["lottery"])
	       || !isset($aOldIssueError['type']) || !is_numeric($aOldIssueError["type"])
	       || !in_array( $aOldIssueError['type'], array(1,2,3,4) )
	       || empty( $aOldIssueError["issue"] )
	       || ( $aOldIssueError['type']==1 && empty($aOldIssueError['starttime']) )
	       || ( $aOldIssueError['type']==2 && empty($aOldIssueError['issueno'])  )
	    )
	    {
	        return -1; // 提交数据错误,请仔细检查
	    }

	    // 2, 数据整理
	    $sNowTime                 = date('Y-m-d H:i:s');
	    $aIssueError["lotteryid"] = intval($aOldIssueError["lottery"]);
	    $aIssueError["issue"]     = daddslashes( $aOldIssueError["issue"] );
	    $aIssueError['errortype'] = intval( $aOldIssueError['type'] ); // 1=提前开奖  2=开奖号码错误
	    //$aIssueError["opentime"]  = '';  // 撤销派奖的起始时间  e.g: 2009-09-13 20:15:00
	    $aIssueError["code"]      = '';  // 新的开奖号码  e.g: 333
	    if( $aIssueError['errortype']==1 )
	    { // 提前开奖, 晚于或等于这个时间的单子全部撤销
			$aIssueError["opentime"] = getFilterDate($aOldIssueError["starttime"]);
			if( '' == $aIssueError["opentime"] )
			{
			    return -3; // 时间格式错误
			}
            if( FALSE === $this->_nowIsAllowException() )
			{
                return -20; // 当前时间禁止使用 '系统撤单' 功能
			}
	    }
	    elseif( $aIssueError['errortype']==2 )
	    { // 开奖号码错误
	        $aIssueError["code"] = daddslashes($aOldIssueError["issueno"]);
	    }
	    elseif( $aIssueError['errortype'] == 3 )
	    {// 官方未开奖
	       if( FALSE === $this->_nowIsAllowException() )
            {
                return -20; // 当前时间禁止使用 '系统撤单' 功能
            }
	    	//$aIssueError['type'] = 1;
	    }
	    else 
	    {
	        return -2; // 处理原因错误
	    }

	    //print_rr($aIssueError);exit;
	    // 对提交数据进行检查. 彩种ID. 时间. 号码是否有效
	    $oIssue = new model_issueinfo();
	    $aDatas = $oIssue->issueGetList(
	           " A.`issue`,A.`code`,A.`statuscode`,A.`statusdeduct`,A.`statususerpoint`,A.`writetime`, "
	           ." A.`statuscheckbonus`, A.`statusbonus`, A.`statustasktoproject`, A.`salestart`, A.`saleend` ",
                " A.`lotteryid`='".$aIssueError["lotteryid"]."' AND A.`saleend` < '".date('Y-m-d H:i:s')."' ",
                " A.saleend DESC limit 0,1" );
	    //print_rr($aDatas);exit;
	    if( 1 != $this->oDB->ar() )
	    {
	        return -4;  // 奖期数据获取错误
	    }
	    
	    //检测期数是否符合
	    if( $aDatas[0]['issue'] != $aIssueError["issue"] )
	    {
	    	return -4;
	    }
	    die('tomdebug01');
	    
	    //检测是否在允许的时间范围内
	    $aDatas[0]['writetime'] = strtotime( $aDatas[0]['writetime'] );
	    $iTempNowTime = strtotime( $sNowTime );
	    if( intval($aDatas[0]['writetime']) > 0 )
	    {//如果号码已录入
	        /* @var $oConfig model_config */
            $oConfig = A::singleton("model_config");
            $iTempLimitMinute = $oConfig->getConfigs( "issueexceptiontime" );
            $iTempLimitMinute = empty($iTempLimitMinute) ? 60 : intval($iTempLimitMinute);
            if( $iTempNowTime > ($aDatas[0]['writetime'] + ($iTempLimitMinute*60)) )
            {//时间已过
                return -9;
            }
	    }
	    
	    $aNextSale = $oIssue->IssueGetOne( " A.`salestart` "," A.`lotteryid`='".$aIssueError["lotteryid"].
	           "' AND A.`salestart` > '".$aDatas[0]['saleend']."' ORDER BY A.saleend ASC limit 0,1 ");
	    // $aNextSale['salestart']  下期开售时间
	    $iNextSaleStartTime = strtotime( $aNextSale['salestart'] );
	    if( time() > $iNextSaleStartTime )
	    {
	        return -8;  // 撤销期的下一期已经开售, 无法撤销派奖
	    }
	    unset($iNextSaleStartTime);
	    
	    

	    $aIssueError['oldcode']               = $aDatas[0]['code'];  // 旧的奖期开奖号码
	    $aIssueError['oldstatuscode']         = $aDatas[0]['statuscode']; // 旧的开奖奖期号码状态  0:未写入;1:写入待验证;2:已验证
	    $aIssueError['oldstatusdeduct']       = $aDatas[0]['statusdeduct']; // 旧的扣款状态(0:未完成;1:进行中;2:已经完成)
	    $aIssueError['oldstatususerpoint']    = $aDatas[0]['statususerpoint']; // 旧的返点状态(0:未开始;1:进行中;2:已完成)
	    $aIssueError['oldstatuscheckbonus']   = $aDatas[0]['statuscheckbonus']; // 旧的检查中奖状态(0:未开始;1:进行中;2:已经完成)
	    $aIssueError['oldstatusbonus']        = $aDatas[0]['statusbonus']; // 旧的返奖状态(0:未开始;1:进行中;2:已经完成)
	    $aIssueError['oldstatustasktoproject']= $aDatas[0]['statustasktoproject']; // 旧的追号单转注单状态(0:未开始;1:进行中;2:已经完成)
	    //print_rr($aIssueError);exit;

        if( $aIssueError['errortype']==1 )
        { // 撤销派奖, 如果是撤销时间的, 需判断时间是否有效
            $iSaleStartTime = strtotime( $aDatas[0]['salestart'] );
            $iSaleEndTime   = strtotime( $aDatas[0]['saleend'] );
            $iOpenTime      = strtotime( $aIssueError["opentime"] );
            if( $iOpenTime <= $iSaleStartTime || $iOpenTime >= $iSaleEndTime )
            {
                return -5; // 输入官方提前开奖的时间无效
            }
        }
	    
	   if( $aIssueError['errortype']==2 )
        { // 撤销派奖, 如果是更正号码录入的. 需判断号码规则
             $oLottery = new model_lottery();
             if( TRUE !== $oLottery->checkCodeFormat( $aIssueError["lotteryid"], $aIssueError["code"] ) )
             {
                 return -6; // 新录入的号码规则错误
             }
             unset($oLottery);
        }

        // 增加重复数据的检测. 相同任务数据只允许一条
        // 检查是否有相同的, 尚未完成的任务.     ( issueerror.type | lotteryid | issue   )
        //                              (0=撤销派奖 1=系统撤单)类型     彩种id    奖期
        if( TRUE == $this->hasSameTask( $aIssueError["lotteryid"], $aIssueError["issue"] ) )
        {
            return -7; // 已有相同任务尚未完成
        }

        
		$aIssueError["writetime"] = $sNowTime;
		$aIssueError["writeid"]   = intval( $_SESSION["admin"] );
		$this->oDB->insert( 'issueerror', $aIssueError );
		if( $this->oDB->ar() === 1 )
		{
			return TRUE;
		}
		return FALSE;
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
     * @author Tom 2009-11-16 18:21
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
    	    $sSql = " SELECT $sFields FROM $sTableName WHERE ie.entry = $iErrorIssueId AND $sCondition ";
    	    $aResult = $this->oDB->getOne( $sSql );
    	    return $aResult;
    	}
    }


    /**
     * 检查奖期异常处理, 是否在允许的时间段内
     * 仅用于判断 3D 和 P5 这种非跨天的彩种的禁止系统撤单时间范围
     * @return BOOL  TRUE=时间允许,  FALSE=时间禁止
     */
    private function _nowIsAllowException()
    {
        $oConfig = new model_config();
        $aConfigValue = $oConfig->getConfigs( array('cd_3dp5_repealtimerange') ); // 系统禁止转账时间段
        print_rr($aConfigValue);
        $aTime = @explode( '-', $aConfigValue['cd_3dp5_repealtimerange'] );
        list( $iBeginHour, $iBeginMinute ) = @explode(":", $aTime[0]);
        list( $iEndHour, $iEndMinute ) = @explode(":", $aTime[1]);

        //echo "$iBeginHour - $iBeginMinute - $iEndHour - $iEndMinute <br/>";
        if( !is_numeric($iBeginHour) || !is_numeric($iBeginMinute) 
            || !is_numeric($iEndHour) || !is_numeric($iEndMinute) )
        {
            return FALSE;
        }

        /* 算法:  开始的小时数,大于结束的小时数,则按跨天处理
         *    例1:   4:00-13:00   正常不跨天, 每天 早上4点至下午1点
         *    例2:   23:00-4:00   跨天处理, 每天凌晨 23:00 至第二天凌晨4点
         */ 

        $iNowTime  = time(); // 当前时间点的 时间戳
        if( $iBeginHour > $iEndHour )
        { // 处理跨天
            //echo '跨天<br/>';
            $iNowTime =  date("Hi"); // 00:21 表示为 0021
            if( intval($iNowTime) > intval($iBeginHour.$iBeginMinute)
                ||  $iNowTime < $iEndHour.$iEndMinute
            )
            {
                return TRUE;
            }
            else 
            {
                return FALSE;
            }
        }
        else 
        { // 处理不跨天
            //echo '不跨天<br/>';
            $iNowTime =  date("Hi"); // 00:21 表示为 0021
            if( intval($iNowTime) > intval($iBeginHour.$iBeginMinute)
                &&  $iNowTime < $iEndHour.$iEndMinute  )
            {
                return TRUE;
            }
            else 
            {
                return FALSE;
            }
        }
    }
}
?>