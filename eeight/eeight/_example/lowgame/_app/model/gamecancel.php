<?php
/**
 * 文件 : /_app/model/gamecancel.php
 * 功能 : 数据模型 - 系统撤单
 * 
 * - doException()      对异常奖期进行整理 (CLI调用)
 * - cancelProjects()   撤单[系统撤单，提前开奖时的时间范围撤单]
 *
 * @author     james  090915
 * @version    1.2.0
 * @package    lowgame
 */

class model_gamecancel extends model_gamebase
{
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }


    /**
     * 对异常奖期进行整理 (CLI调用)
     * CLI 调用流程
     *    1, e1m_issueException.php::_runCli() 中调用本方法 doException()
     *    2, 
     * @return string
     */
    public function doException()
    {
        //01: 检查 issueerror 是否有需要执行的任务.
        /* @var $oIssueError model_issueerror */
        $oIssueError  = A::singleton("model_issueerror");
        $sCondition   = " `statuscancelbonus` NOT IN(2,9) OR `statusrepeal` NOT IN(2,9) ORDER BY `writetime` ASC LIMIT 1 ";
        $aIssueError  = $oIssueError->errorGetOne( '', $sCondition );
        if( empty($aIssueError) )
        {
            return "No Mission need to Process.\n"; // 没有奖期异常的任务需要执行
        }

        //02: 检查异常的彩种当前是否在销售期, 如果为销售期, 则禁止本CLI的运行
        /* @var $oIssueInfo model_issueinfo */
        $oIssueInfo   = A::singleton("model_issueinfo");
        $aRes         = $oIssueInfo->getCurrentIssue( $aIssueError['lotteryid'] );
        if( $this->oDB->errno() > 0 )
        {
        	return "Get Current Issue Failed\n";    //读取信息错误
        }
        if( !empty($aRes) )
        {
            return $aRes['issue']." Is Saling. this Program Has been Stoped\n"; // 当前正在销售期, 禁止执行系统撤单
        }
        unset( $aRes );


        //03:  [容错并忽略] 防止跨期进行系统撤单操作 (仅可以撤销最后一期) 
        // 根据彩种ID, 获取离当前时间最接近并且已经截止销售的奖期
        // 判断当前时间刚结束的彩种/销售期, 是否与 issueError 表获取的奖期完全一致 (数据完整性和有效性检查)
        $sNowDate    = date("Y-m-d H:i:s");
        $aUpdate     = array();
        $aIssueInfo  = $this->oDB->getOne( "SELECT * FROM `issueinfo` WHERE `lotteryid`='".$aIssueError['lotteryid']
                                           ."' AND `saleend` < '$sNowDate' ORDER BY `saleend` DESC LIMIT 1 " );
        if( $aIssueInfo['issue'] != $aIssueError['issue'] )
        {
            $aUpdate['statuscancelbonus'] = 9; // 撤销派奖状态=9  (忽略)
            $aUpdate['statusrepeal']      = 9; // 系统撤单状态=9  (忽略)
            $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
            return $aIssueError['issue']." be ignored \n"; // 撤销派奖的奖期号无效. 忽略执行
        }


        //04: 根据撤销派奖错误类型 issueError.errortype { 1:官方提前开奖; 2:录入号码错误; 3:官方未开奖; }
        //    执行不同的业务流程
        if( $aIssueError['errortype'] == 1 )
        { // 官方提前开奖 {根据时间对部分方案执行撤销派奖, 系统撤单}
        	$sMsg = "errortype:1 =>\n";
        	//判断是否在允许处理的时间范围内
        	$aIssueError['writetime'] = strtotime( $aIssueError['writetime'] );
        	$aIssueInfo['writetime']  = strtotime( $aIssueInfo['writetime'] );
        	if( intval($aIssueInfo['writetime']) > 0 )
        	{//如果号码已录入
        		/* @var $oConfig model_config */
	            $oConfig = A::singleton("model_config");
	            $iTempLimitMinute = $oConfig->getConfigs( "issueexceptiontime" );
	            $iTempLimitMinute = empty($iTempLimitMinute) ? 60 : intval($iTempLimitMinute);
	            if( $aIssueError['writetime'] > ($aIssueInfo['writetime'] + ($iTempLimitMinute*60)) )
	            {//时间已过
	                $aUpdate['statuscancelbonus'] = 9; // 撤销派奖状态=9  (忽略)
	                $aUpdate['statusrepeal']      = 9; // 系统撤单状态=9  (忽略)
	                $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
	                return $sMsg."The Time is passed, Ignored\n"; // 处理时间已过，忽略
	            }
        	}
        	//04 01: 检查撤销派奖的时间是否有效 (是否在该奖期的销售时间内)
            $iSaleStartTime = strtotime( $aIssueInfo['salestart'] );
            $iSaleEndTime   = strtotime( $aIssueInfo['saleend'] );
            $iOpenTime      = strtotime( $aIssueError["opentime"] );
            if( $iOpenTime < $iSaleStartTime || $iOpenTime > $iSaleEndTime )
            { // 数据库中输入的错误开奖时间无效. 忽略此条数据
                $aUpdate['statuscancelbonus'] = 9; // 撤销派奖状态=9  (忽略)
                $aUpdate['statusrepeal']      = 9; // 系统撤单状态=9  (忽略)
                $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
                return $sMsg."The opentime is not Corrent, Ignored\n"; // 官方提前开奖的时间无效
            }
            unset( $iSaleStartTime, $iSaleEndTime, $iOpenTime);

            //04 02: 首先检测撤消派奖程序是否执行完，如果未完则先执行撤消派奖
            if( $aIssueError['statuscancelbonus'] == 0 || $aIssueError['statuscancelbonus'] == 1 )
            {
            	//04 02 01: 检测扣款、返点、派奖，必须是在未执行或者执行完以后才执行该操作，不然必须等待
            	if( ($aIssueInfo['statusdeduct'] != 0 && $aIssueInfo['statusdeduct'] != 2)
            	    || ($aIssueInfo['statususerpoint'] != 0 && $aIssueInfo['statususerpoint'] != 2)
            	    || ($aIssueInfo['statusbonus'] != 0 && $aIssueInfo['statusbonus'] != 2) )
            	{
            		while( TRUE )
            		{//等待可以操作后才继续操作
            			$sSql = " SELECT * FROM `issueinfo` WHERE `lotteryid`='".$aIssueError["lotteryid"]."'
            			          AND `issue`='".$aIssueError["issue"]."' 
                                  AND (`statusdeduct`='0' OR `statusdeduct`='2')
		                          AND (`statususerpoint`='0' OR `statususerpoint`='2')
		                          AND (`statusbonus`='0' OR `statusbonus`='2')
            			          LIMIT 1 ";
            			$aIssueInfo = $this->oDB->getOne($sSql);
            			if( !empty($aIssueInfo) )
            			{
            				break;
            			}
            			sleep(5);
            		}
            	}
            	//04 02 02： 如果第一次运行则更改状态值为正在执行 
            	if( $aIssueError['statuscancelbonus'] == 0 )
            	{
	            	$sSql = " UPDATE `issueerror` AS A LEFT JOIN `issueinfo` AS B 
	                          ON (A.`lotteryid`=B.`lotteryid` AND A.`issue`=B.`issue`) 
	                          SET A.`statuscancelbonus`='1' 
	                          WHERE A.`statuscancelbonus`='0' AND A.`entry` = '".$aIssueError['entry']."' 
	                          AND (B.`statusdeduct`='0' OR B.`statusdeduct`='2')
	                          AND (B.`statususerpoint`='0' OR B.`statususerpoint`='2')
	                          AND (B.`statusbonus`='0' OR B.`statusbonus`='2') ";
	                $this->oDB->query($sSql);
	                if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
	                {
	                    return $sMsg."update issueerror.statuscancelbonus=1 failed\n";
	                }
            	}
            	//04 02 03: 执行撤消派奖操作
            	/* @var $oCancelBonus model_cancelbonus */
            	$oCancelBonus = A::singleton("model_cancelbonus");
            	$aTempData    = array(
            	                   'entry'     => $aIssueError['entry'],
            	                   'lotteryid' => $aIssueError['lotteryid'],
            	                   'issue'     => $aIssueError['issue'],
            	                   'time'      => $aIssueError['opentime']
            	                 );
            	$mResult = $oCancelBonus->doCancelBonus( $aTempData );
            	if( $mResult === TRUE )
            	{//执行成功以后更新撤消派奖状态为完成
            		$aUpdate['statuscancelbonus'] = 2; // 撤销派奖状态=2  (完成)
	                $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
	                if( $this->oDB->errno() > 0 )
	                {
	                	return $sMsg."update issueerror.statuscancelbonus=2 failed\n";
	                }
            	}
            	else
            	{
            		return $sMsg.'doCannelBonus.Result='.$mResult."\n";
            	}
            }
            
            //04 03: 执行完撤消派奖以后再执行撤单
            $sSql = " SELECT * FROM `issueerror` WHERE `entry` = '".$aIssueError['entry']."' LIMIT 1 ";
            $aNowIssueError = $this->oDB->getOne( $sSql );
            if( empty($aNowIssueError) )
            {
            	return $sMsg."get issueerror info failed[entry:".$aIssueError['entry']."]\n";
            }
            if( $aNowIssueError['statuscancelbonus'] != 2 )
            {
            	return $sMsg." cancel bonus is not finish, wait for it finished\n";
            }
            if( $aNowIssueError['statusrepeal'] == 0 )
            {//如果第一次运行则更改
                $sSql = "UPDATE `issueerror` AS A 
                         LEFT JOIN `issueinfo` AS B ON (A.`issue`=B.`issue` AND A.`lotteryid`=B.`lotteryid`)
                         SET A.`statusrepeal`='1' 
                         WHERE A.`statusrepeal`='0' AND A.`entry` = '".$aIssueError['entry']."'
                         AND (B.`statusdeduct`='0' OR B.`statusdeduct`='2')
                         AND (B.`statususerpoint`='0' OR B.`statususerpoint`='2')
                         AND (B.`statusbonus`='0' OR B.`statusbonus`='2')";
                $this->oDB->query( $sSql );
                if( $this->oDB->ar() != 1 )
                {
                    return $sMsg."update issueerror.statusrepeal=1[entry:".$aIssueError['entry']."] error \n";
                }
            }
            $mResult = $this->cancelProjects( $aIssueError["lotteryid"], $aIssueError["issue"], $aIssueError['opentime'] );
            if( $mResult === TRUE )
            {//如果全部执行成功，则更改状态为完成
                $aUpdate['statuscancelbonus'] = 2; // 撤销派奖状态=2  (完成)
                $aUpdate['statusrepeal']      = 2; // 系统撤单状态=2  (完成)
                $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
                if( $this->oDB->errno() > 0 )
                {
                	return $sMsg."update issueerror.statusrepeal=2 failed\n";
                }
                return $sMsg." cancel success:[lottery:".$aIssueInfo['lotteryid'].", issue:".$aIssueError['issue']."]\n";
            }
            return $sMsg.$mResult;
        }
        elseif( $aIssueError['errortype'] == 2 )
        {// 录入号码错误  {重新写号, 撤销派奖}
        	$sMsg = "errortype:2 =>\n";
            //判断是否在允许处理的时间范围内
            $aIssueError['writetime'] = strtotime( $aIssueError['writetime'] );
            $aIssueInfo['writetime']  = strtotime( $aIssueInfo['writetime'] );
            if( intval($aIssueInfo['writetime']) > 0 )
            {//如果号码已录入
                /* @var $oConfig model_config */
                $oConfig = A::singleton("model_config");
                $iTempLimitMinute = $oConfig->getConfigs( "issueexceptiontime" );
                $iTempLimitMinute = empty($iTempLimitMinute) ? 60 : intval($iTempLimitMinute);
                if( $aIssueError['writetime'] > ($aIssueInfo['writetime'] + ($iTempLimitMinute*60)) )
                {//时间已过
                    $aUpdate['statuscancelbonus'] = 9; // 撤销派奖状态=9  (忽略)
                    $aUpdate['statusrepeal']      = 9; // 系统撤单状态=9  (忽略)
                    $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
                    return $sMsg."The Time is passed, Ignored\n"; // 处理时间已过，忽略
                }
            }
        	//检测号码是否正确
        	/* @var $oLottery model_lottery */
            $oLottery = A::singleton("model_lottery");
            if( TRUE !== $oLottery->checkCodeFormat( $aIssueError["lotteryid"], $aIssueError["code"] ) )
            {
            	//忽略此异常
            	$aUpdate['statuscancelbonus'] = 9; // 撤销派奖状态=9  (忽略)
                $aUpdate['statusrepeal']      = 9; // 系统撤单状态=9  (忽略)
                $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
                return $sMsg."new code is wrong\n"; // 新录入的号码规则错误
            }
            //04 02 01: 检测扣款、返点、判断中奖、派奖，必须是在未执行或者执行完以后才执行该操作，不然必须等待
            if(    ($aIssueInfo['statusdeduct'] != 0     && $aIssueInfo['statusdeduct'] != 2)
                || ($aIssueInfo['statususerpoint'] != 0  && $aIssueInfo['statususerpoint'] != 2)
                || ($aIssueInfo['statusbonus'] != 0      && $aIssueInfo['statusbonus'] != 2)
                || ($aIssueInfo['statuscheckbonus'] != 0 && $aIssueInfo['statuscheckbonus'] != 2) )
            {
                while( TRUE )
                {//等待可以操作后才继续操作
                    $sSql = " SELECT * FROM `issueinfo` WHERE `lotteryid`='".$aIssueError["lotteryid"]."'
                              AND `issue`='".$aIssueError["issue"]."' 
                              AND (`statusdeduct`='0' OR `statusdeduct`='2')
                              AND (`statususerpoint`='0' OR `statususerpoint`='2')
                              AND (`statusbonus`='0' OR `statusbonus`='2')
                              AND (`statuscheckbonus`='0' OR `statuscheckbonus`='2')
                              LIMIT 1 ";
                    $aIssueInfo = $this->oDB->getOne($sSql);
                    if( !empty($aIssueInfo) )
                    {
                        break;
                    }
                    sleep(5);
                }
            }
            //04 02 02： 如果第一次运行则更改状态值为正在执行 
            if( $aIssueError['statuscancelbonus'] == 0 )
            {
                $sSql = " UPDATE `issueerror` AS A LEFT JOIN `issueinfo` AS B 
                          ON (A.`lotteryid`=B.`lotteryid` AND A.`issue`=B.`issue`) 
                          SET A.`statuscancelbonus`='1' 
                          WHERE A.`statuscancelbonus`='0' AND A.`entry` = '".$aIssueError['entry']."' 
                          AND (B.`statusdeduct`='0' OR B.`statusdeduct`='2')
                          AND (B.`statususerpoint`='0' OR B.`statususerpoint`='2')
                          AND (B.`statusbonus`='0' OR B.`statusbonus`='2')
                          AND (B.`statuscheckbonus`='0' OR B.`statuscheckbonus`='2') ";
                $this->oDB->query($sSql);
                if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
                {
                    return $sMsg."update issueerror.statuscancelbonus=1 failed\n";
                }
            }
            //04 02 03: 执行撤消派奖操作
            /* @var $oCancelBonus model_cancelbonus */
            $oCancelBonus = A::singleton("model_cancelbonus");
            $aTempData    = array(
                               'entry'     => $aIssueError['entry'],
                               'lotteryid' => $aIssueError['lotteryid'],
                               'issue'     => $aIssueError['issue'],
                               'code'      => $aIssueError['code']
                             );
            $mResult = $oCancelBonus->doCancelBonus( $aTempData );
            if( $mResult === TRUE )
            {//执行成功以后更新撤消派奖状态为完成
            	//更新新的号码到奖期表以及更新判断中奖和派奖为未执行
            	$sSql = " UPDATE `issueinfo` SET `code`='".$aIssueError['code']."',
                            `statuscheckbonus`='0',`statusbonus`='0' 
                          WHERE `lotteryid`='".$aIssueError['lotteryid']."' 
            	          AND `issue`='".$aIssueError['issue']."'
            	          AND (`statusdeduct`='0' OR `statusdeduct`='2')
                          AND (`statususerpoint`='0' OR `statususerpoint`='2')
                          AND (`statusbonus`='0' OR `statusbonus`='2')
                          AND (`statuscheckbonus`='0' OR `statuscheckbonus`='2')";
            	$this->oDB->query( $sSql );
            	if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
            	{
            		return $sMsg."update issueinfo.code,statuscheckbonus,statusbonus error\n";
            	}
            	//更新新的号码到历史奖期表
                $sSql = " UPDATE `issuehistory` SET `code`='".$aIssueError['code']."' 
                          WHERE `lotteryid`='".$aIssueError['lotteryid']."' 
                          AND `issue`='".$aIssueError['issue']."' ";
                $this->oDB->query( $sSql );
                if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
                {
                    return $sMsg." update issuehistory.code error\n";
                }
                
                $aUpdate['statuscancelbonus'] = 2; // 撤销派奖状态=2  (完成)
                $aUpdate['statusrepeal']      = 2; // 系统撤单状态=2  (完成)
                $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
                if( $this->oDB->errno() > 0 )
                {
                    return $sMsg."update issueerror.statuscancelbonus=2 failed\n";
                }
                return $sMsg." cancel bonus with 'wrong code' successed ".
                        "[lottery:".$aIssueInfo['lotteryid'].", issue:".$aIssueError['issue']."] \n";
            }
            else
            {
                return $sMsg.'doCannelBonus.Result='.$mResult."\n";
            }
            
        }
        elseif( $aIssueError['errortype'] == 3 )
        {//官方未开奖
        	$sMsg = "errortype:3 =>\n";
        	//04 01： 是否有号码录入，有则退出并且更改状态为忽略
        	if( $aIssueInfo['statuscode'] != 0 )
        	{
        		$aUpdate['statuscancelbonus'] = 9; // 撤销派奖状态=9  (忽略)
        		$aUpdate['statusrepeal']      = 9; // 系统撤单状态=9  (忽略)
                $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
        		return $sMsg."cancel all project error: ".$aIssueInfo['issue']." has some code enter \n";
        	}
        	//04 02：更改issueerror表的撤单状态为正在执行
        	if( $aIssueError['statusrepeal'] == 0 )
        	{//如果第一次运行则更改
        		$sSql = "UPDATE `issueerror` AS A 
        		         LEFT JOIN `issueinfo` AS B ON (A.`issue`=B.`issue` AND A.`lotteryid`=B.`lotteryid`)
        		         SET A.`statusrepeal`='1' 
        		         WHERE A.`statusrepeal`='0' AND B.`statuscode`='0' AND A.`entry` = '".$aIssueError['entry']."'";
        		$this->oDB->query( $sSql );
        		if( $this->oDB->ar() != 1 )
        		{
        			return $sMsg."update issueerror.statusrepeal[entry:".$aIssueError['entry']."] error \n";
        		}
        	}
        	$mResult = $this->cancelProjects( $aIssueError["lotteryid"], $aIssueError["issue"] );
        	if( $mResult === TRUE )
        	{//如果全部执行成功，则更改状态为完成
        		//更新奖期的相关状态为完成[支持后续CLI运行]
        		//号码验证状态为: 3 未开奖 ，真实扣款: 2 已完成 ，用户返点: 2 已完成，检查中奖状态: 2 已完成，派奖状态: 2 已完成
        		$sSql = " UPDATE `issueinfo` SET `statuscode`='3',`statusdeduct`='2',`statususerpoint`='2',
                          `statuscheckbonus`='2',`statusbonus`='2' WHERE `lotteryid`='".$aIssueError['lotteryid']."' 
                          AND `issue`='".$aIssueError['issue']."'
                          AND `statuscode`='0' AND `statusdeduct`='0' AND `statususerpoint`='0' 
                          AND `statuscheckbonus`='0' AND `statusbonus`='0'  ";
        	    $this->oDB->query( $sSql );
                if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
                {
                    return $sMsg."update issueinfo.status=2 error\n";
                }
        		$aUpdate['statuscancelbonus'] = 2; // 撤销派奖状态=2  (完成)
                $aUpdate['statusrepeal']      = 2; // 系统撤单状态=2  (完成)
                $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
                return $sMsg." cancel all project success: ".
                       "[lottery:".$aIssueInfo['lotteryid'].", issue:".$aIssueError['issue']."] \n";
        	}
        	return $sMsg.'doCannelBonus.Result='.$mResult;
        }
        else
        {
        	return "unkonw issueError.errortype \n";
        }
    }
    
    
    
    /**
     * 撤单[系统撤单，提前开奖时的时间范围撤单]
     *
     * @author  james 090906
     * @access  protected
     * @param   int          $iLotteryId    //彩种ID
     * @param   string       $sIssue        //奖期
     * @param   date         $dBeginDate    //开始撤单时间
     * @return  string
     */
    protected function cancelProjects( $iLotteryId, $sIssue, $dBeginDate='' )
    {
    	$iAffectedSuccess = 0;   //本次操作成功所影响的数据
        $iAffectedFailed  = 0;   //本次操作失败所影响的数据
        $sMsg    = "[cancelProjects] \n";           //错误信息
        if( empty($iLotteryId) || empty($sIssue) || !is_numeric($iLotteryId) )
        {//参数错误
            return $sMsg." wrong param\n";
        }
        $iLotteryId = intval($iLotteryId);
        $sIssue     = daddslashes($sIssue);
        if( !empty($dBeginDate) )
        {
        	$dBeginDate = getFilterDate($dBeginDate);
        }
        
        //01：首先获取要进行系统撤单的所有方案信息[未撤单]
        $sSql = " SELECT p.*, m.`locksid`,l.`lockname`
                  FROM `projects` AS p 
                  LEFT JOIN `method` AS m ON (p.`lotteryid`=m.`lotteryid` AND p.`methodid`=m.`methodid`)
                  LEFT JOIN `locksname` AS l ON m.`locksid`=l.`locksid`
                  WHERE p.`lotteryid`='".$iLotteryId."' AND p.`issue`='".$sIssue."' 
                  ".( empty($dBeginDate) ? "" : " AND p.`writetime`>='".$dBeginDate."'" )." 
                  AND p.`iscancel`='0' ";
        $aProjects = $this->oDB->getAll( $sSql );
        if( $this->oDB->errno() > 0 )
        {//数据库操作
        	return $sMsg."get projects error\n";
        }
        if( empty($aProjects) )
        {
        	//echo 'action ok[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']';
        	return TRUE;
        }
        
        //02： 获取该彩种封锁表以及位置
        $sSql    = "SELECT `lockname` FROM `locksname` WHERE `lotteryid`='".$iLotteryId."' ";
        $aResult = $this->oDB->getAll( $sSql );
        if( $this->oDB->errno() > 0 )
        {
        	return $sMsg."get lockcname error[success:".$iAffectedSuccess.",failed:".$iAffectedFailed."]\n";
        }
        $aLockName = array();
        foreach( $aResult as $v )
        {
        	$sSql = " SELECT `issue` FROM `".$v['lockname']."history` 
                          WHERE `issue`='".$sIssue."' LIMIT 1";
            if( $this->oDB->errno() > 0 )
            {//数据错误
                return $sMsg."get locks location error(locksname:".$v['lockname'].")".
                       "[success:".$iAffectedSuccess.",failed:".$iAffectedFailed."]\n";
            }
            if( $this->oDB->ar() > 0 )
            {//已到了历史表
            	$aLockName[$v['lockname']] = $v['lockname']."history";
            }
            else
            {//现在的封锁表
            	$aLockName[$v['lockname']] = $v['lockname'];
            }
        }
        /* @var $oUserFund model_userfund */
        $oUserFund  = A::singleton('model_userfund'); //用户资金模型
        //02: 循环对每个方案进行撤单
        foreach( $aProjects as $aGameInfo )
        {
        	//02 00: 判断是否派奖，遇到派奖的则直接返回，等待撤消派奖完成
        	if( $aGameInfo['prizestatus'] != 0 )
        	{
        		$iAffectedFailed += 1;
        		return $sMsg."[rotjectid:".$aGameInfo['projectid']."] : has sended prize, wait cancel prize\n".
        		             '[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
        	}
        	$aLocksData = array();  //封锁表更新数据
	        $aSaleData  = array();  //销量表数据
	        $aOrdersData= array();  //资金、帐变数据
	        $aStatusData= array();  //更改状态数据
	        //02 01: 更新封锁表数据
	        if( $aGameInfo['locksid'] > 0 && !empty($aGameInfo['lockname']) && !empty($dBeginDate) )
	        {//如果有封锁并且是撤单某一时间段的方案则构建更新封锁表数据
	            //02 01 00: 获取号码扩展表数据
	            $sSql = "SELECT `prize`,`expandcode`,`isspecial`,`level` FROM `expandcode` 
	                     WHERE `projectid`='".$aGameInfo['projectid']."' ";
	            $aExpandData = $this->oDB->getAll($sSql);
	            if( empty($aExpandData) )
	            {//数据错误
	            	$iAffectedFailed += 1;
	                $sMsg .= "get expandcode error(projectid:".$aGameInfo['projectid'].")\n";
	                continue;
	            }
	            //02 01 01: 根据号码扩展获取封锁值更新条件
	            $aTempArr = $this->getUpdateLocksConditions( $aGameInfo['methodid'], $aExpandData );
	            if( empty($aTempArr) )
	            {//数据错误
	            	$iAffectedFailed += 1;
	                $sMsg .= "get locks condition error(projectid:".$aGameInfo['projectid'].")\n";
	                continue;
	            }
	            //02 01 02: 判断该使用历史封锁表还是当前封锁表
	            $sCurrentLocks = $aGameInfo['lockname'];
	            $sHistoryLocks = "";
	            if( !isset($aLockName[$aGameInfo['lockname']]) )
	            {
	            	$sMsg .= "get locks location error(projectid:".$aGameInfo['projectid'].")\n";
                    continue;
	            }
                if( $aLockName[$aGameInfo['lockname']] == $aGameInfo['lockname']."history" )
                {//已到了历史封锁表
                	$sCurrentLocks = "";
                    $sHistoryLocks = $aGameInfo['lockname']."history";
                }
	            // 02 01 03: 获取封锁表更新条件
	            $aLocksData = array();
	            $iThreadId  = intval($this->oDB->getThreadId()) % 5;//五个线程
	            foreach( $aTempArr as $v )
	            {
	                if( empty($sCurrentLocks) )
	                {//历史封锁表
	                    $aLocksData[] = "UPDATE `".$sHistoryLocks."` SET `prizes`=`prizes`-".$v['prizes']." 
	                                     WHERE `issue`='".$aGameInfo['issue']."' AND ".$v['condition'];
	                }
	                else 
	                {//当前封锁表
	                    $aLocksData[] = "UPDATE `".$sCurrentLocks."` SET `prizes`=`prizes`-".$v['prizes']." 
	                                     WHERE `issue`='".$aGameInfo['issue']."' AND ".$v['condition']." 
	                                     AND `threadid`='".$iThreadId."' ";
	                }
	            }
	        }
	        if( !empty($dBeginDate) )
	        {
	        	//02 02： 更新销量表数据
	            $aSaleData[] = array(
	                                   'issue'     => $aGameInfo['issue'],
	                                   'lotteryid' => $aGameInfo['lotteryid'],
	                                   'TFWLname'  => $aGameInfo['lockname'], //封锁表名称
	                                   'moneys'    => (-1) * ($aGameInfo['totalprice'] * (1-$aGameInfo['lvtoppoint']))
	                                );
	        }
	        
	                                
	        //02 03: 资金、帐变数据
	        //02 03 01: 撤单返款数据
	        if( $aGameInfo['isdeduct'] == 1 )
	        {//已完成真实扣款
	            $aOrdersData['fk'] = array(
	                                        'iLotteryId'   => $aGameInfo['lotteryid'],
	                                        'iMethodId'    => $aGameInfo['methodid'],
	                                        'iTaskId'      => $aGameInfo['taskid'],
	                                        'iProjectId'   => $aGameInfo['projectid'],
	                                        'iFromUserId'  => $aGameInfo['userid'],
	                                        'iOrderType'   => 99, //撤单返款[特殊的已完成真实扣款的]
	                                        'fMoney'       => $aGameInfo['totalprice'],
	                                        'sDescription' => '撤单返款',      //帐变描述
	                                        'iAdminId'     => 0,
	                                        'iChannelID'   => SYS_CHANNELID
	                                    );
	            //更新方案表数据
	            $aStatusData[] = array(
	                                    'sql'      => "UPDATE `projects` SET `iscancel`='2', `updatetime`='".date('Y-m-d H:i:s')."' WHERE `isdeduct`='1' 
	                                                   AND `iscancel`='0' AND `prizestatus`='0'
	                                                   AND `projectid`='".$aGameInfo['projectid']."' 
	                                                   AND `userid`='".$aGameInfo['userid']."' ",
	                                    'affected' => 1   );
	        }
	        else
	        {//未完成真实扣款
	            $aOrdersData['fk'] = array(
	                                               'iLotteryId'   => $aGameInfo['lotteryid'],
	                                               'iMethodId'    => $aGameInfo['methodid'],
	                                               'iTaskId'      => $aGameInfo['taskid'],
	                                               'iProjectId'   => $aGameInfo['projectid'],
	                                               'iFromUserId'  => $aGameInfo['userid'],
	                                               'iOrderType'   => 9, //撤单返款
	                                               'fMoney'       => $aGameInfo['totalprice'],
	                                               'sDescription' => '撤单返款',      //帐变描述
	                                               'iAdminId'     => 0,
	                                               'iChannelID'   => SYS_CHANNELID
	                                           );
	            //更新方案表数据
	            $aStatusData[] = array(
	                                    'sql'      => "UPDATE `projects` SET `iscancel`='2',`updatetime`='".date('Y-m-d H:i:s')."' WHERE `isdeduct`='0' 
	                                                   AND `iscancel`='0' AND `prizestatus`='0'
	                                                   AND `projectid`='".$aGameInfo['projectid']."' 
                                                       AND `userid`='".$aGameInfo['userid']."' ",
	                                    'affected' => 1   );
	        }
	     
	        //02 03 03: 获取该方案的所有返点数据
	        $sSql       = " SELECT `userid`,`diffmoney`,`diffpoint` FROM `userdiffpoints` WHERE 
	                        `projectid`='".$aGameInfo['projectid']."' AND `status`='1' AND `cancelstatus`='0' ";
	        $aDiffPoint = $this->oDB->getAll($sSql);
	        if( $this->oDB->errno() > 0 )
	        {
	        	$iAffectedFailed += 1;
	            $sMsg .= "get userdiff error(projectid:".$aGameInfo['projectid'].")\n";
                continue;
	        }
	        $aLockFundUsers   = array();
	        $aLockFundUsers[] = $aGameInfo['userid'];
	        if( !empty($aDiffPoint) )
	        {//如果有返点数据
	            foreach( $aDiffPoint as $v )
	            {
	                $aLockFundUsers[] = $v['userid'];
	                $aOrdersData[]    = array(
	                                    'iLotteryId'   => $aGameInfo['lotteryid'],
	                                    'iMethodId'    => $aGameInfo['methodid'],
	                                    'iTaskId'      => $aGameInfo['taskid'],
	                                    'iProjectId'   => $aGameInfo['projectid'],
	                                    'iFromUserId'  => $v['userid'],
	                                    'iToUserId'    => $v['userid'] == $aGameInfo['userid'] ? 0 : $aGameInfo['userid'],
	                                    'iOrderType'   => 11, //撤销返点
	                                    'fMoney'       => $v['diffmoney'],
	                                    'sDescription' => '撤销返点',      //帐变描述
	                                    'iAdminId'     => 0,
	                                    'iChannelID'   => SYS_CHANNELID
	                                           );
	            }
	        }
	        //02 03 04: 更改状态数据
	        //02 03 04 01: 追号表数据更改
	        if( $aGameInfo['taskid'] > 0 )
	        {//
	            $aStatusData[] = array(//追号表
	                   'sql'      => "UPDATE `tasks` SET `finishedcount`=`finishedcount`-1,
	                              `cancelcount`=`cancelcount`+1,`finishprice`=`finishprice`-".$aGameInfo['totalprice'].",
	                               `cancelprice`=`cancelprice`+".$aGameInfo['totalprice'].", `updatetime`='".date('Y-m-d H:i:s')."'  
	                               WHERE `taskid`='".$aGameInfo['taskid']."' AND `userid`='".$aGameInfo['userid']."' ",
	                   'affected' => 1   );
	            $aStatusData[] = array(//追号详情表
	                   'sql'      => "UPDATE `taskdetails` SET `status`='2' WHERE `taskid`='".$aGameInfo['taskid']."' 
	                                  AND `projectid`='".$aGameInfo['projectid']."' AND `status`='1'",
	                   'affected' => 1   );
	        }
	        //02 03 04 02: 返点表数据修改
	        $aStatusData[] = array(
	                          'sql' => "UPDATE `userdiffpoints` SET `status`='2', 
	                                   `cancelstatus`='3' WHERE `projectid`='".$aGameInfo['projectid']."' AND `status`!='2'
	                                   AND `cancelstatus`='0'" );
	        
	        /**
	         * 03: 开始写入数据 ========================================================
	         */
	        $aLockFundUsers = array_unique($aLockFundUsers);
	        $sLockFundUsers = implode(",",$aLockFundUsers);
	        //03 01: 锁用户资金表[开始锁资金事务处理]---------------
	        if( FALSE == $this->oDB->doTransaction() )
	        {//事务处理失败
	        	$iAffectedFailed += 1;
	            return $sMsg.'system error:#5011[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
	        }
	        if( intval($oUserFund->switchLock($sLockFundUsers, SYS_CHANNELID, TRUE)) != count($aLockFundUsers) )
	        {
	        	$iAffectedFailed += 1;
	            if( FALSE == $this->oDB->doRollback() )
	            {//回滚事务
	                return $sMsg.'system error:#5012[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
	            }
	            $sMsg .= "userfund lock failed(userid:".$sLockFundUsers.")\n";
                continue;
	        }
	        if( FALSE == $this->oDB->doCommit() )
	        {//事务提交失败
	        	$iAffectedFailed += 1;
	            return $sMsg.'system error:#5013[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
	        }
	        //03 02: [开始投单流程事务处理]------------
	        if( FALSE == $this->oDB->doTransaction() )
	        {//事务处理失败
	        	$iAffectedFailed += 1;
	            $oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );//解锁资金表
	            return $sMsg.'system error:#5011[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
	        }
	        $mResult = $this->cancelUpdateData( $aSaleData, $aOrdersData, $aStatusData, $aLocksData );
	        if( $mResult === -33 )
	        {//资金不够
	        	$iAffectedFailed += 1;
	            if( FALSE == $this->oDB->doRollback() )
	            {//回滚事务
	            	$oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );//解锁资金表
	                return $sMsg.'system error:#5012[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
	            }
	            $oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );//解锁资金表
	            $sMsg .= "user have not enough money(userid:".$sLockFundUsers.")\n";
                continue;
	        }
	        elseif( $mResult !== TRUE )
	        {
	        	$iAffectedFailed += 1;
	            if( FALSE == $this->oDB->doRollback() )
	            {//回滚事务
	            	$oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );//解锁资金表
	                return $sMsg.'system error:#5012[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
	            }
	            $oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );//解锁资金表
	            $sMsg .= "cancelUpdateData(fun) error#".$mResult."(projectid:".$aGameInfo['projectid'].")\n";
                continue;
	        }
	        //03 03: 提交投单流程事务处理[结束] -----------------------
	        if( FALSE == $this->oDB->doCommit() )
	        {//事务提交失败
	        	$iAffectedFailed += 1;
	        	$oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );
	            return $sMsg.'system error:#5013[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
	        }
	        //03 04: 解锁资金表 -------------------------------------
	        $oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );
	        $iAffectedSuccess  += 1;
        }
        if( empty($dBeginDate) && $iAffectedFailed == 0 )
        {//如果是撤消一期的则直接更新销量表和封锁表的所有值为0
        	//更新销量表
        	$sSql = "UPDATE `sales` SET `moneys`='0' WHERE `lotteryid`='".$iLotteryId."' AND `issue`='".$sIssue."'";
        	$this->oDB->query( $sSql );
        	if( $this->oDB->errno() > 0 )
        	{
        		return $sMsg.'update sales error[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
        	}
        	foreach( $aLockName as $v )
        	{
        		$sSql = "UPDATE ".$v." SET `prizes`='0' WHERE `issue`='".$sIssue."'";
        		$this->oDB->query( $sSql );
	        	if( $this->oDB->errno() > 0 )
	            {
	                return $sMsg.'update locks('.$v.') error '.
	                             '[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
	            }
        	}
        }
        if( $iAffectedFailed == 0 )
        {
        	return TRUE;
        }
        else
        {
        	return $sMsg.'action ok[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
        }
    }
}
?>