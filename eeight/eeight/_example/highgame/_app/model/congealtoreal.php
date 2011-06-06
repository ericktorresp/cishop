<?php
/**
 * 文件 : /_app/model/congealtoreal.php
 * 功能 : 数据模型 - 真实扣款[仅供CLI调用]
 *
 * @author     Tom,Rojer
 * @version    1.2.0
 * @package    highgame
 */

class model_congealtoreal extends basemodel
{
    // ---------------------------------[ 属性 ]-------------------------------------------
    // 如果 $iStepCounts, $iStepSec 任何一个为 0, 则不做任何限制, 程序将不会进入 SLEEP 状态
    private $iStepCounts = 0;     // [配合] 每次处理 n 条后, PHP 睡眠 $this->iStepSec 秒
    private $iStepSec    = 0;     // [配合] 每次处理$this->$iStepCounts 条后, PHP 睡眠 n 秒

    private $iProcessMax = 0;     // 每次获取处理真实扣款的方案数, 即: Limit 数量, 0 为不限制
    private $iProcessRecord = 0;  // 本次执行更新的方案数量

    private $iAmount = 1;   // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行

    private $sIssue     = '';     // 奖期名字  issueinfo.issue
    private $sIssueId   = 0;      // 奖期编号  issueinfo.issueid
    private $iLotteryId = 0;      // 彩种ID

    // ---------------------------------[ 方法 ]-------------------------------------------
    /**
     * 根据彩种ID, 对方案进行真实扣款
     * @param int $iLotteryId
     * @return mix
     */
    public function doCongealToReal( $iLotteryId )
    {
        $this->iLotteryId = intval($iLotteryId);
        echo date('Y-m-d H:i:s')." 集中扣款......";
        // 1, 判断彩种ID 的有效性
        $oLottery   = A::singleton("model_lottery");
        if(!$oLottery->lotteryGetOne( ' `lotteryid` ', " `lotteryid` = '$this->iLotteryId' " ))
        {
            return -1001; // 彩种ID错误
        }
        unset($oLottery);

        // 2, 获取需处理的奖期信息 ( From Table.`IssueInfo` )
        //     2.1  录入开奖号码并已验证的   statuscode = 2
        //     2.2  未完整执行真实扣款的     statusdeduct != 2
        //     2.3  符合当前彩种ID的         lotteryid = $iLotteryId
        //     2.4  已停售的                 saleend < 当前时间
        //     2.5  为了按时间顺序线性执行, 取最早一期符合以上要求的  ORDER BY A.`saleend` ASC
        $oIssue       = A::singleton("model_issueinfo");
        $sCurrentTime = date( "Y-m-d H:i:s", time() );
        $sFileds      = " * ";
        $sCondition   = " A.`statuscode`=2 AND A.`statusdeduct`!=2 AND A.`lotteryid`='" . $this->iLotteryId
            . "' AND A.`saleend`<'" . $sCurrentTime . "' ORDER BY A.`saleend` ASC";
        while (1)
        {
            $aRes = $oIssue->IssueGetOne( $sFileds, $sCondition );
            if( empty($aRes) )
            {
                echo "没有需要扣款的奖期！";
                return -1002; // 未获取到需要进行真实扣款的奖期号 (所有奖期的'真实扣款'操作皆以完成)
            }
            
            if ($aRes['statustasktoproject'] != 2)
            {
                echo "彩种 {$aRes['lotteryid']} 奖期 {$aRes['issue']} 必须等追号单转注单后才能进行！";
                return -1003;
            }
            
            echo " 奖期 {$aRes['issue']} ({$aRes['salestart']}-{$aRes['saleend']})......\n";

            $this->sIssue   = $aRes['issue'];  // 需进行 '真实扣款' 的首个奖期编号
            $this->sIssueId = intval($aRes['issueid']); // 奖期表的自增ID号

            /**
             * 3, 保证异常处理的优先级高于任何其他 Cli {真实扣款|集中返点|中奖判断|奖金派发}
             * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
             *    1, 更新 projects.'返奖状态' 之前, 先检测异常表(IssueError) 数据
             *       如果有值, 则将本期 issueInfo.statuscheckbonus=2 并退出 CLI. 等待异常完整处理完毕
             */
            $this->oDB->query("SELECT 1 FROM `issueerror` WHERE `lotteryid`='$this->iLotteryId' "
                              . " AND `issue`='$this->sIssue' "
                              . " AND ( `statuscancelbonus` NOT IN(2,9) OR `statusrepeal` NOT IN(2,9) ) ");
            if( 1 == $this->oDB->ar() )
            {
                // 标记为未执行, CLI 退出 (让异常CLI优先运行)
                $this->oDB->update( "issueinfo", array( 'statusdeduct' => 0 ),  " `statusdeduct`!=2 AND `issueid`=$this->sIssueId" );
                echo "该期有异常处理任务，退出！";
                return -1008;
            }

            // 4, 获取所有尚未'真实扣款'的方案 ( From Table.`projects` )
            //     3.1  根据奖期号    $this->sIssue
            //     3.2  彩种ID        $this->iLotteryId
            //     3.3  未真实扣款的  Projects.`isdeduct` != 1
            //     3.4  未撤单的      Projects.`iscancel` = 0
            /* @var $oProjects model_projects */
            $oProjects = A::singleton("model_projects");
            $sLimit    = $this->iProcessMax==0 ? '' : ' LIMIT ' . intval($this->iProcessMax);
            $aRes      = $oProjects->projectsGetList(' `lotteryid`,`methodid`,`projectid`, `userid`, `taskid`, `totalprice`, `modes` ',
                                                      " `issue`='$this->sIssue' AND `lotteryid`='$this->iLotteryId'
                                                      AND `isdeduct`!=1 AND `iscancel`=0 $sLimit " );
            $iCounts   = count($aRes);  // 实际获取的需处理方案个数
//dump($aRes);
            //$sDebugMsg = "[d] [".date('Y-m-d H:i:s')."] Issue='$this->sIssue', LotteryId='$this->iLotteryId' ". "CliProcessLimit='".$this->iProcessMax."', GotDataCounts='$iCounts' ";
            $iAllSuccessFlag = true;
            // 5, 如果获取的结果集为空, 则表示当前奖期已全部'真实扣款'完成. 更新状态值
            if( $iCounts )
            {
                // 奖期标记设置为: 进行'真实扣款'中...
                $this->oDB->update( "issueinfo", array( 'statusdeduct' => 1 ),  " `issueid`=$this->sIssueId " );
                /**
                 * 6, [循环] 对方案进行真实扣款的原子操作, 遇到用户资金被锁等userFund错误则忽略,继续处理下一条
                 *
                 * 消息类型:
                 * ~~~~~~~~~
                 *   [n]   表示 notice 错误, 并不重要. 例: 用户资金暂时被锁, 将在下次执行再试
                 *   [w]   表示 warnning 错误, 重要!!! 例: 由本程序对用户资金账户锁定后, 无法解锁
                 *   [d]   表示 debug 消息
                 */
                for( $i=0; $i<$iCounts; $i++ )
                {
                    // 1, 试图锁用户资金
                    if(!$this->doLockUserFund( $aRes[$i]['userid'], TRUE ))
                    { // 如果锁用户资金失败, 继续处理下一条方案的真实扣款
                        echo "[line ".__LINE__."]锁用户资金失败 userid='".$aRes[$i]['userid']."' 跳过...\n";
                        $iAllSuccessFlag = false;
                        continue;
                    }

                    // 2, 开始业务流程操作
                    if( FALSE == $this->oDB->doTransaction() )
                    {
                        if (!$this->doLockUserFund( $aRes[$i]['userid'], FALSE ))
                        {
                            echo "[line ".__LINE__."]锁用户资金失败 userid='".$aRes[$i]['userid']."' 跳过...\n";
                        }
                        return -2001;
                    }
                    if( TRUE !== ($iFlag=$this->doProcess( $aRes[$i] )) )   // 业务流程执行失败
                    {
                        // 1, 显示错误信息
                        // 2, 事务回滚. 不对本次循环涉及的 '真实扣款' 做任何更改
                        // 3, 解锁用户资金账户
                        if( $iFlag < 0 )
                        { // 如果是账变相关操作失败, 显示账变返回的负数
                            echo "this->doProcess()方法失败. ProjectId='".$aRes[$i]['projectid']."' orderFlag='$iFlag' Skiped\n";
                        }
                        else
                        {
                            echo "$iFlag\n";
                        }

                        if( FALSE == $this->oDB->doRollback() )
                        { // 事务回滚发生失败. 则中断
                            if (!$this->doLockUserFund( $aRes[$i]['userid'], FALSE ))
                            {
                                echo "[line ".__LINE__."]锁用户资金失败 userid='".$aRes[$i]['userid']."' 跳过...\n";
                            }
                            return -2002;
                        }
                        // 对用户资金账户进行解锁, 失败则报错并忽略
                        if(!$this->doLockUserFund( $aRes[$i]['userid'], FALSE ) )
                        {
                            echo "[line ".__LINE__."]锁用户资金失败 userid='".$aRes[$i]['userid']."' 跳过...\n";
                        }
                        $iAllSuccessFlag = false;
                        continue;
                    }
                    else
                    { // 业务流程执行成功, 则执行业务逻辑原子操作的提交
                        if( FALSE == $this->oDB->doCommit() )
                        { // 事务提交发生失败. 则中断
                            if (!$this->doLockUserFund( $aRes[$i]['userid'], FALSE )) // 解锁
                            {
                                echo "[line ".__LINE__."]锁用户资金失败 userid='".$aRes[$i]['userid']."' 跳过...\n";
                            }
                            $iAllSuccessFlag = false;
                            return -2003;
                        }
                    }

                    $this->iProcessRecord++; // 如果成功执行一次原子操作, 则+1

                    // 3, 对用户资金进行解锁
                    if(!$this->doLockUserFund( $aRes[$i]['userid'], FALSE ) )
                    { // 如果锁用户资金失败, 跳过下面代码, 继续处理下一条更新
                        echo "[line ".__LINE__."]锁用户资金失败 userid='".$aRes[$i]['userid']."' 跳过...\n";
                        $iAllSuccessFlag = false;
                        continue;
                    }

                    // 一些参数判断
                    if( $this->iStepCounts !=0 && $this->iStepSec != 0 )
                    {
                        if( $this->iStepCounts == $i+1 )
                        {
                            echo "[d] sleep for $this->iStepSec sec\n";
                            sleep( $this->iStepSec );
                        }
                    }
                }
            }

            // 如果都没错则标记已完成
            if ($iAllSuccessFlag)
            {
                echo "OK，全部处理完毕！";
                $this->oDB->update( "issueinfo", array( 'statusdeduct' => 2 ), " `issueid`=$this->sIssueId " );
                if( 1 != $this->oDB->ar() )
                {
                    return -3001; // 更新奖期状态值失败
                }
            }
            else
            {
                echo "有部分没处理完，等待下次处理！";
            }

            if(!--$this->iAmount)
            {
                break;
            }
        }

        // 6, 返回0表示错误, 正数表示本次 CLI 执行受影响的方案数
        return $this->iProcessRecord;
    }


    /**
     * 锁用户资金
     * @param int  $iUserId
     * @param int  $bIsLocked  TRUE=锁资金, FALSE=解锁
     * @return BOOL  操作成功返回全等于的 TRUE, 否则返回FALSE
     */
    private function doLockUserFund( $iUserId, $bIsLocked=TRUE )
    {
        $iFlag = true;
        if( false == $this->oDB->doTransaction() )
        { // 事务处理失败
            $iFlag = false;
        }
        /* @var $oUserFund model_userfund */
        $oUserFund   = A::singleton('model_userfund');
        if( intval($oUserFund->switchLock( $iUserId , SYS_CHANNELID, (BOOL)$bIsLocked)) != 1 )
        {
            if( false == $this->oDB->doRollback() )
            {//回滚事务
                $iFlag = false;
            }
            $iFlag = false;
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            $iFlag = false;
        }

        return $iFlag;
    }
    
    
    /**
     * [**被嵌套在事务内**]
     *    处理业务流程操作,  冻结金额 -> 真实扣款
     * @param array  $iUserId
     * @return mix  操作成功返回全等于的 TRUE, 否则返回错误信息
     */
    private function doProcess(  $aDatas = array() )
    {
    	/* @var $oOrders model_orders */
        $oOrders  = A::singleton('model_orders');
        $mFlag = $oOrders->addOrders( array(
                'iLotteryId'    => intval($aDatas['lotteryid']),
                'iMethodId'     => intval($aDatas['methodid']),
                'iFromUserId'   => intval($aDatas['userid']),
                'iProjectId'    => intval($aDatas['projectid']),
                'iTaskId'       => intval($aDatas['taskid']),
                'iOrderType'    => ORDER_TYPE_YXKK,  // 游戏扣款
                'fMoney'        => floatval($aDatas['totalprice']),
                'iModesId'      => intval($aDatas['modes']),
                'bIgnoreMinus'	=> TRUE,
        ));

        if( TRUE !== $mFlag )
        {
            return $mFlag;
        }

        // 更新状态 orders
        $sSql = "UPDATE `projects` SET `updatetime`='".date("Y-m-d H:i:s")."',`isdeduct`=1,`deducttime`='".date('Y-m-d H:i:s')."' WHERE `projectid`='".intval($aDatas['projectid'])."' AND `isdeduct`=0 ";
        $this->oDB->query( $sSql );
        if( 1 != $this->oDB->ar() )
        {
            return 'doProcess() 更新方案表扣款字段失败！sql='.$sSql;
        }
        return TRUE;
    }



    public function setAmount( $iAmount = 1 )
    {
        $this->iAmount = $iAmount;
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