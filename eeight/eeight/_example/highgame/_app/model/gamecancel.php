<?php
/**
 * 文件 : /_app/model/gamecancel.php
 * 功能 : 数据模型 - 系统撤单
 * 包括3种情况：官方提前开奖，开奖号码错误，官方未开奖
 * 
 * - doException()      对异常奖期进行整理 (CLI调用)
 * - cancelProjects()   撤单[系统撤单，提前开奖时的时间范围撤单]
 *
 * 一、官方提前开奖：撤消派奖（如果已经派奖） + 系统撤单（撤提前开奖时间至当期停售时间的方案）
 * 测试指标：
 * 1.对于在提前开奖时间以后买的方案project表记录，其iscancel更新为2（已撤单）;
 * 2.在issueerror表的statusrepeal字段更新为2（已完成撤单）
 * 3.帐变：每条购买记录均产生两条帐变，撤单返款和撤消返点，即买单的逆过程
 * 4.userfund用户资金表的channelbalance频道资金，availablebalance可用资金，holdbalance冻结资金的变动;
 * 注:   购买彩票的资金先行冻结（即仅改变可用资金量和冻结资金量，而频道资金暂不动
 *       集中扣款操作就专门把冻结资金按方案逐个扣除，这个过程是channelbalance和holdbalance的变动,可用余额不变，完成后，holdbalance=0, channelbalance==availablebalance
 *       因此，先撤单还是先扣款显然互不相干
 * 5.如果已经集中返点，撤单的时候要取消其上级的所有返点（这在model_gamemanage::cancelProject()方法中封装了撤消一个方案的完整操作）
 *
 * 二、开错号码
 * 目前只处理出错奖期，以后要考虑追号情况，测试情况有三种：
 * 1.不喜不忧型。事先没买中，重开号码也没中，最后结果还是没中，这些只是打酱油的，没有影响;
 * 2.先忧后喜型。事先没买中，但更改号码后中奖了，最后结果是中了，这里理应把后续的追号单都立即撤消，如果后续有中奖的当然要撤消派奖;
 * 3.先喜后忧型。事先买中了，因为是错误的号码，最后结果是没中，这时理应继续追号。把追号任务表重置0，把后续奖期均置为未扣款，未返点，未中奖判断，未派奖，未追号单转注单，让CLI再依次进行这些操作;
 *      实际上，假设有3个追号单（分别追111,222,333），分别代表3种情况，开奖的时候，原先开333，更改为222，对222来说，属于第二种类型，对333来说，属于第三种类型
 *      即往往这些情况都有，所以，先把当期的所有追号单分成这三类，再分别处理。
 * 
 * 三、官方未开奖，和第一条差不多，不同的是当期方案全部撤单
 *
 * @author     Rojer
 * @version    1.3.0
 * @package    highgame
 */

class model_gamecancel extends model_gamebase
{
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }

    /**
     * 对异常奖期进行整理 (CLI调用)
     * @return string
     * @author Rojer
     */
    public function doException()
    {
        //01: 检查 issueerror 是否有需要执行的任务.
        $oIssueError  = A::singleton("model_issueerror");
        $sCondition   = " `statuscancelbonus` NOT IN(2,9) OR `statusrepeal` NOT IN(2,9) ORDER BY `writetime` ASC";
        $aIssueError  = $oIssueError->errorGetOne( '', $sCondition );

        if( empty($aIssueError) )
        {
            return "No Mission need to Process.\n"; // 没有奖期异常的任务需要执行
        }

        $oIssueInfo = A::singleton("model_issueinfo");
        $aIssueInfo = $oIssueInfo->getItem(0, $aIssueError['issue'], $aIssueError['lotteryid']);
        $oCancelBonus = A::singleton("model_cancelbonus");
        echo date('Y-m-d H:i:s')." 处理奖期'{$aIssueInfo['issue']}'......\n";
        $sMsg = "errortype:1 =>\n";

        switch ($aIssueError['errortype'])
        {
            case '1':   // 官方提前开奖：撤消派奖（如果已经派奖） + 系统撤单（撤提前开奖时间至当期停售时间的方案）
                $iAllSuccessFlag = true;
                //1.判断是否在允许撤消派奖处理的时间范围内
                $aIssueError['writetime'] = strtotime( $aIssueError['writetime'] );
                $aIssueInfo['writetime']  = strtotime( $aIssueInfo['writetime'] );
                if( intval($aIssueInfo['writetime']) > 0 )  //如果号码已录入
                {
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
                //2.先撤消派奖,如果失败就中止处理
                $aTempData    = array(
                       'entry'     => $aIssueError['entry'],
                       'lotteryid' => $aIssueError['lotteryid'],
                       'issue'     => $aIssueError['issue'],
                       'time'      => $aIssueError['opentime'],
                     );
                if (($mResult = $oCancelBonus->doCancelBonus($aTempData)) !== true)
                {
                    // 由于资金被锁等理由导致没全部做完表示出错了，如果没有未撤的奖期也会返回true
                    $iAllSuccessFlag = false;
                }
                
                //3.撤单
                // 3.1 先改为正在撤单状态 statusrepeal=1
                if( $aIssueError['statusrepeal'] == 0 )
                {
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

                // 3.2 具体撤单 如果全部撤单成功，则更改状态为完成
                if( !$this->cancelProjects( $aIssueError )) // 全部或部分返点出错
                {
                    //echo "[".basename(__FILE__).":".__LINE__."]"."撤单失败！(lotteryid={$aIssueError['lotteryid']}, issue={$aIssueError['issue']})\n";
                    $iAllSuccessFlag = false;
                }
                
                // ok，没出任何错误:)
                if (!$iAllSuccessFlag)
                {
                    echo "提前开奖处理出错！(lotteryid={$aIssueError['lotteryid']}, issue={$aIssueError['issue']})\n";
                    return false;
                }
                
                echo "提前开奖处理成功！(lotteryid={$aIssueError['lotteryid']}, issue={$aIssueError['issue']})\n";
                $this->oDB->update( 'issueerror', array('statusrepeal' => 2), " `entry` = '".$aIssueError['entry']."' " );
                break;
            case '2':   // 录入号码错误：撤消派奖 + 开奖判断 + 重新派奖
                $sMsg = "errortype:2 =>\n";
                // 1.判断是否在允许撤消派奖处理的时间范围内
                $aIssueError['writetime'] = strtotime( $aIssueError['writetime'] );
                $aIssueInfo['writetime']  = strtotime( $aIssueInfo['writetime'] );
                if( intval($aIssueInfo['writetime']) == 0 )  //如果号码没录？显然不对
                {
                    return "$sMsg the code has never been inputted";
                }
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
                
                // 2.检测号码是否正确
                $oLottery = A::singleton("model_lottery");
                if( TRUE !== $oLottery->checkCodeFormat( $aIssueError["lotteryid"], $aIssueError["code"] ) )
                {
                    $aUpdate['statuscancelbonus'] = 9; // 撤销派奖状态=9  (忽略)
                    $aUpdate['statusrepeal']      = 9; // 系统撤单状态=9  (忽略)
                    $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
                    return $sMsg."new code is wrong\n"; // 新录入的号码规则错误
                }

                // 3.置“正在进行”标志
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

                // 4.撤消派奖
                $aTempData    = array(
                       'entry'     => $aIssueError['entry'],
                       'lotteryid' => $aIssueError['lotteryid'],
                       'issue'     => $aIssueError['issue'],
                       'code'      => $aIssueError['code'],
                     );
                if (($mResult = $oCancelBonus->doCancelBonus($aTempData)) !== true)
                {
                    // 由于资金被锁等理由导致没全部做完表示出错了，如果没有未撤的奖期也会返回true
                    return $sMsg.'doCannelBonus.Result='.$mResult."\n";
                }

                // 改动：第5,6,7,8步增加事务，防止处理中断
                if( FALSE == $this->oDB->doTransaction() )
                { // 事务处理失败
                    return $sMsg."事务处理失败\n";
                }

                // 5.更新新的号码到奖期表以及更新判断中奖和派奖为未执行
            	$sSql = " UPDATE `issueinfo` SET `code`='".$aIssueError['code']."',`statuscheckbonus`='0',`statusbonus`='0',`statussynced`='0'".
                    " WHERE `lotteryid`=".$aIssueError['lotteryid'] . " AND `issue`='".$aIssueError['issue']."'";
            	$this->oDB->query( $sSql );
            	if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
            	{
                    $this->oDB->doRollback();
            		return $sMsg."更新新的号码到奖期表以及更新判断中奖和派奖为未执行失败！sql=$sSql\n";
            	}

                // 6.更新方案表所有当期方案的中奖状态isgetprize为未判断0
                $sSql = " UPDATE `projects` SET `isgetprize`='0',`updatetime`='".date("Y-m-d H:i:s")."'".
                    " WHERE `lotteryid`=".$aIssueError['lotteryid'] . " AND `issue`='".$aIssueError['issue']."'";
            	$this->oDB->query( $sSql );
            	if( $this->oDB->errno() > 0)    // || $this->oDB->ar() == 0 
            	{
                    $this->oDB->doRollback();
            		return $sMsg."更新方案表记录出错！sql=$sSql\n";  //不可能一条都没有更新
            	}

            	// 7.更新新的号码到历史奖期表
                $sSql = " UPDATE `issuehistory` SET `code`='{$aIssueError['code']}', `misseddata`=NULL, `totalmissed`=NULL, `series`=NULL, `totalseries`=NULL".
                    " WHERE `lotteryid`='{$aIssueError['lotteryid']}' AND `issue`='".$aIssueError['issue']."'";
                $this->oDB->query( $sSql );
                if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
                {
                    $this->oDB->doRollback();
                    return $sMsg."更新新的号码到历史奖期表失败！ sql=$sSql\n";
                }

                // 8.重置包括当前出错奖期以内的以后奖期的statussynced
                $sSql = " UPDATE `issueinfo` SET `statussynced`='0'".
                    " WHERE `lotteryid`=".$aIssueError['lotteryid'] . " AND `issue`>'".$aIssueError['issue']."' LIMIT 500";
            	$this->oDB->query( $sSql );
            	if( $this->oDB->errno() > 0)
            	{
                    $this->oDB->doRollback();
            		return $sMsg."重置包括当前出错奖期以内的以后奖期的statussynced失败！sql=$sSql\n";
            	}

                if( FALSE == $this->oDB->doCommit() )
                {
                    return $sMsg."事务提交失败\n";
                }

                // 9.清空历史奖期表的号码分析数据
                $sSql = " UPDATE `issuehistory` SET `misseddata`=NULL, `totalmissed`=NULL, `series`=NULL, `totalseries`=NULL".
                    " WHERE `lotteryid`='{$aIssueError['lotteryid']}' AND `issue`>'".$aIssueError['issue']."'";
                $this->oDB->query( $sSql );
                if( $this->oDB->errno() > 0)
                {
                    return $sMsg."清空历史奖期表的号码分析数据失败！ sql=$sSql\n";
                }

                // 4.如果全部执行成功，则更改撤单状态为忽略（因为只是开错了号码，方案一直是有效的，不存在撤单过程）
                $this->oDB->update( 'issueerror', array('statuscancelbonus' => 2, 'statusrepeal' => 9), " `entry` = '".$aIssueError['entry']."' " );
                break;
            case '3':   // 官方未开奖：当期全部方案撤单
                // 1.是否有号码录入，有则退出并且更改状态为忽略
                if( $aIssueInfo['statuscode'] != 0 )
                {
                    $aUpdate['statuscancelbonus'] = 9; // 撤销派奖状态=9  (忽略)
                    $aUpdate['statusrepeal']      = 9; // 系统撤单状态=9  (忽略)
                    $this->oDB->update( 'issueerror', $aUpdate, " `entry` = '".$aIssueError['entry']."' " );
                    return $sMsg."cancel all project error: ".$aIssueInfo['issue']." has some code enter \n";
                }

                // 2.更改issueerror表的撤单状态为正在执行
                if( $aIssueError['statusrepeal'] == 0 )
                {
                    //如果第一次运行则更改
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
                
                // 3.撤单
                $mResult = $this->cancelProjects( $aIssueError );
                if( $mResult !== TRUE )
                {
                    return $sMsg." cancel project occured a error.[lottery:".$aIssueInfo['lotteryid'].", issue:".$aIssueError['issue']."]\n";
                }

                //号码验证状态为: 3 未开奖 ，真实扣款: 2 已完成 ，用户返点: 2 已完成，检查中奖状态: 2 已完成，派奖状态: 2 已完成
        		$sSql = " UPDATE `issueinfo` SET `statuscode`='3',`statusdeduct`='2',`statususerpoint`='2',
                          `statuscheckbonus`='2',`statusbonus`='2' WHERE `lotteryid`='".$aIssueError['lotteryid']."'
                          AND `issue`='".$aIssueError['issue']."'
                          AND `statuscode`='0' 
                          AND `statuscheckbonus`='0' AND `statusbonus`='0'";    // 附：低频上有附加条件：AND `statusdeduct`='0' AND `statususerpoint`='0'
        	    $this->oDB->query( $sSql );
                if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
                {
                    return $sMsg."update issueinfo.status=2 error\n";
                }
                
                // 4.如果全部执行成功，则更改状态为完成(cancelProject()方法会检查如果已经派奖先撤消，再撤单
                $this->oDB->update( 'issueerror', array('statusrepeal' => 2, 'statuscancelbonus' => 2), " `entry` = '".$aIssueError['entry']."' " );
                // statuscode
                return $sMsg.'doCannelBonus.Result='.$mResult;
                break;
            default:
                echo "不识别的操作";
                break;
        }
        return true;
    }
    
    /**
     * 撤单[系统撤单，提前开奖时的时间范围撤单]
     * 全部成功返回true
     *
     * @author  james,Rojer
     * @access  protected
     * @param   int          $iLotteryId    //彩种ID
     * @param   string       $sIssue        //奖期
     * @param   date         $dBeginDate    //开始撤单时间
     * @return  string|true
     */
    protected function cancelProjects($aIssueError)
    {
        $iLotteryId = $aIssueError['lotteryid'];
        $sIssue = $aIssueError["issue"];
        $dBeginDate = getFilterDate($aIssueError['opentime']);

        $iAffectedSuccess = 0;   //本次操作成功所影响的数据
        $iAffectedFailed  = 0;   //本次操作失败所影响的数据
        //首先获取要进行系统撤单的所有方案信息[未撤单]
        $sSql = " SELECT * FROM `projects` WHERE lotteryid='$iLotteryId' AND `issue`='$sIssue' AND `iscancel`='0' ";
        if ($dBeginDate)
        {
            $sSql .= " AND `writetime`>='$dBeginDate'";
        }
//dump($aIssueError["issue"], $sIssue, $sSql, $this->oDB->getAll( $sSql ));
        $iAllSuccessFlag = true;
        if ($aProjects = $this->oDB->getAll( $sSql ))
        {
        	//循环对每个方案进行撤单
            $oGamemanage = A::singleton("model_gamemanage");
            foreach ($aProjects as $v)
            {
                // 后台页面手动操作的话最后参数用$_SESSION['admin']，这里用0，因为CLI运行没有session，而且需求也没有要求区分是管理员撤的还是系统自动撤的
                if (($mResult = $oGamemanage->cancelProject( $v['userid'], $v['projectid'], $aIssueError['writeid'] )) !== true)
                {
                    $iAffectedFailed++;
                    echo "cancel project failed: $mResult (projectid={$v['projectid']}, lotteryid={$iLotteryId}, issue={$sIssue}, user={$v['userid']}) \n";
                    $iAllSuccessFlag = false;
                }
                else
                {
                    $iAffectedSuccess++;
                }
            }
        }
        
        return $iAllSuccessFlag;
    }
}
?>