<?php
/**
 * 文件 : /_app/model/sendpoints.php
 * 功能 : 数据模型 - 集中返点
 *
 * @author     Tom
 * @version    1.1.0
 * @package    lowgame
 * @since      090908 10:57
 */

class model_sendpoints extends basemodel
{
    // ---------------------------------[ 属性 ]-------------------------------------------
    // 如果 $iStepCounts, $iStepSec 任何一个为 0, 则不做任何限制, 程序将不会进入 SLEEP 状态
    private $iStepCounts = 0;     // [配合] 每次处理 n 条后, PHP 睡眠 $this->iStepSec 秒
    private $iStepSec    = 0;     // [配合] 每次处理$this->$iStepCounts 条后, PHP 睡眠 n 秒

    private $iProcessMax = 0;     // 每次获取处理真实扣款的方案数, 即: Limit 数量, 0 为不限制
    private $iProcessRecord = 0;  // 本次执行更新的方案数量

    private $bLoopMode = FALSE;   // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行 
    
    private $sIssue     = '';     // 奖期名字  issueinfo.issue
    private $sIssueId   = 0;      // 奖期编号  issueinfo.issueid
    private $iLotteryId = 0;      // 彩种ID

    // ---------------------------------[ 方法 ]-------------------------------------------
    /**
     * 根据彩种ID, 对方案进行集中返点
     * @param int $iLotteryId
     * @return mix
     */
    public function doSendPoints( $iLotteryId )
    {
        $this->iLotteryId = intval($iLotteryId);

        // 1, 判断彩种ID 的有效性
        $oLottery   = A::singleton("model_lottery");
        $aRes = $oLottery->lotteryGetOne( ' `lotteryid` ', " `lotteryid` = '$this->iLotteryId' " );
        if( empty($aRes) )
        {
            return -1001; // 彩种ID错误
        }
        unset($aRes,$oLottery);


        // 2, 获取需处理的奖期信息 ( From Table.`IssueInfo` )
        //     2.1  录入开奖号码并已验证的   statuscode = 2
        //     2.2  未完整执行集中返点的     statususerpoint != 2
        //     2.3  符合当前彩种ID的         lotteryid = $iLotteryId
        //     2.4  已停售的                 saleend < 当前时间
        //     2.5  为了按时间顺序线性执行, 取最早一期符合以上要求的  ORDER BY A.`saleend` ASC
        $oIssue       = A::singleton("model_issueinfo");
        $sCurrentTime = date( "Y-m-d H:i:s", time() );
        $sFileds      = " A.`issueid`, A.`issue` ";
        $sCondition   = " A.`statuscode`=2 AND A.`statususerpoint`!=2 AND A.`lotteryid`='". $this->iLotteryId
                        . "' AND A.`saleend`<'" . $sCurrentTime . "' ORDER BY A.`saleend` ASC LIMIT 1 ";
        $aRes         = $oIssue->IssueGetOne( $sFileds, $sCondition );
        unset($oIssue);
        if( empty($aRes) )
        {
            return -1002; // 未获取到需要进行集中返点的奖期号 (所有奖期的'集中返点'皆以完成)
        }
        $this->sIssue   = $aRes['issue'];  // 需进行 '集中返点' 的首个奖期编号
        $this->sIssueId = intval($aRes['issueid']); // 奖期表的自增ID号
        
        
        /**
         * 3, 保证异常处理的优先级高于任何其他 Cli {真实扣款|集中返点|中奖判断|奖金派发}
         * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
         *    1, 更新 projects.'返奖状态' 之前, 先检测异常表(IssueError) 数据
         *       如果有值, 则将本期 issueInfo.statuscheckbonus=2 并退出 CLI. 等待异常完整处理完毕
         */
        $this->oDB->query("SELECT 1 FROM `issueerror` WHERE `lotteryid`='$this->iLotteryId' "
                          . " AND `issue`='$this->sIssue' "
                          . " AND ( `statuscancelbonus` NOT IN (2,9) OR `statusrepeal` NOT IN (2,9) ) ");
        if( 1 == $this->oDB->ar() )
        {
            // 标记为未执行, CLI 退出 (让异常CLI优先运行)
            $this->oDB->update( "issueinfo", array( 'statususerpoint' => 0 ), 
                        " `statususerpoint`!=2 AND `issueid`=$this->sIssueId" );
            return -1008;
        }


        // 4, 获取所有尚未'集中返点'的当期方案
        //     3.1  根据奖期号 $sIssue 查询方案表 `Projects` left join 返点表 `userdiffpoints`
        //     3.2  返点状态为: '未返'  userdiffpoints.`status`=0, && cancelstatus=0
        //     3.3  未撤单的   projects.iscancel = 0
        $sLimit      = $this->iProcessMax==0 ? '' : ' LIMIT '.intval($this->iProcessMax);
        $aRes        = $this->oDB->getAll( "SELECT `lotteryid`, `methodid`, p.`projectid` AS projectid, "
                        . " p.`userid` as `buyuserid`, udp.`userid` as userid, `totalprice`, `taskid`, "
                        . " udp.`entry` as pointentry, udp.`diffmoney` "
                        . " FROM `projects` p LEFT JOIN `userdiffpoints` udp ON( p.`projectid`=udp.`projectid` ) "
                        . " WHERE `lotteryid`='$this->iLotteryId' AND udp.`status`=0 "
                        . " AND udp.`cancelstatus`=0 AND `issue`='$this->sIssue' AND p.`iscancel`=0 $sLimit " );
        $iCounts     = count($aRes);  // 实际获取的需处理集中返点个数
        $sDebugMsg = "[d] [".date('Y-m-d H:i:s')."] Issue='$this->sIssue', LotteryId='$this->iLotteryId' CliProcessLimit='".$this->iProcessMax."', GotDataCounts='$iCounts' ";
        echo $sDebugMsg."\n";


        // 5, 如果获取的结果集为空, 则表示当前奖期已全部'集中返点'完成. 更新状态值
        if( 0 == $iCounts )
        {
            $this->oDB->update( "issueinfo", array( 'statususerpoint' => 2 ), "`issueid`=$this->sIssueId" );
            if( 1 != $this->oDB->ar() )
            {
                return -3001; // 更新奖期状态值失败
            }
            if( $this->bLoopMode == TRUE )
            {
                return $this->doSendPoints( $this->iLotteryId ); // 递归
            }
            else
            {
                return -1003; // 当前奖期 '集中返点' 已经全部完成
            }
        }
        else 
        { // 奖期标记设置为: 进行'集中返点'中...
            $this->oDB->update( "issueinfo", array( 'statususerpoint' => 1 ),  " `issueid`=$this->sIssueId " );

        }


        /** 
         * 6, [循环] 对集中返点进行的原子操作, 遇到用户资金被锁|账变错误| userFund 错误则忽略,继续处理下一条
         *     4.1  根据当前类中的属性, 进行遍历处理 (事务)
         *         - 1, 写 '销售返点' 的账变 (同时对用户资金进行操作)
         *         - 2, 对返点表 userdiffpoints 的关联数据 更新其 userdiffpoints.`status`=1 (已返款)
         * 消息类型
         *   [n]   表示 notice 错误, 并不重要. 例: 用户资金暂时被锁, 将在下次执行再试
         *   [w]   表示 warnning 错误, 重要!!! 例: 由本程序对用户资金账户锁定后, 无法解锁
         *   [d]   表示 debug 消息
        */
        for( $i=0; $i<$iCounts; $i++ )
        {
            // 1, 试图锁用户资金
            if( TRUE !== $this->doLockUserFund( $aRes[$i]['userid'], TRUE ) )
            { // 如果锁用户资金失败, 继续处理下一条方案的真实扣款
                echo "[n] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [Lock] uid='".$aRes[$i]['userid']."' Skiped\n";
                continue;
            }

            // 2, 开始业务流程操作
            //   2.1  事务执行[开始,提交,回滚]的失败, 返回负数, 中断循环
            //   2.2  业务流程处理失败, 则解锁, 继续执行下一条
            if( FALSE == $this->oDB->doTransaction() )
            {
                $this->doLockUserFund( $aRes[$i]['userid'], FALSE ); // 解锁
                return -2001;
            }
            if( TRUE !== ($iFlag=$this->doProcess( $aRes[$i] )) )
            { // 业务流程执行失败
                // 1, 显示错误信息
                // 2, 事务回滚. 不对本次循环涉及的 '集中返点' 信息做任何更改
                // 3, 解锁用户资金账户
                if( $iFlag < 0 )
                { // 如果是账变相关操作失败, 显示账变返回的负数
                    echo "[w] [".date('Y-m-d H:i:s')."] AddOrders Failed. ProjectsId='".$aRes[$i]['projectid']."' orderFlag='$iFlag' Skiped\n";
                }
                if( FALSE == $this->oDB->doRollback() )
                { // 事务回滚发生失败. 则中断
                    $this->doLockUserFund( $aRes[$i]['userid'], FALSE ); // 解锁
                    return -2002;
                }
                // 对用户资金账户进行解锁, 失败则报错并忽略
                if( FALSE == $this->doLockUserFund( $aRes[$i]['userid'], FALSE ) )
                {
                    echo "[w] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [UnLock] uid='".$aRes[$i]['userid']."' Skiped\n";
                }
                continue;
            }
            else 
            { // 业务流程执行成功, 则执行业务逻辑原子操作的提交
                if( FALSE == $this->oDB->doCommit() ) 
                { // 事务提交发生失败. 则中断
                    $this->doLockUserFund( $aRes[$i]['userid'], FALSE ); // 解锁
                    return -2003;
                }
            }

            $this->iProcessRecord += 1; // 如果成功执行一次原子操作, 则+1

            // 3, 对用户资金进行解锁
            if( TRUE !== $this->doLockUserFund( $aRes[$i]['userid'], FALSE ) )
            { // 如果锁用户资金失败, 跳过下面代码, 继续处理下一条更新
                echo "[w] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [UnLock] uid='".$aRes[$i]['userid']."' Skiped\n";
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

        if( $this->bLoopMode == TRUE )
        {
            return $this->doSendPoints( $this->iLotteryId ); // 递归
        }

        // 6, 返回负数表示错误, 正数表示本次 CLI 执行受影响的方案数
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
        if( FALSE == $this->oDB->doTransaction() )
        { // 事务处理失败
            return FALSE;
        }
        $oUserFund   = A::singleton('model_userfund');
        if( intval($oUserFund->switchLock( $iUserId , SYS_CHANNELID, (BOOL)$bIsLocked)) != 1 )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return FALSE;
            }
            return FALSE;
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return FALSE;
        }
        return TRUE;
    }


    /**
     * [**被嵌套在事务内**]
     *    处理业务流程操作,  冻结金额 -> 真实扣款
     * @param array  $iUserId
     * @return mix  操作成功返回全等于的 TRUE, 否则返回错误信息
     */
    private function doProcess(  $aDatas = array() )
    {
        $oOrders  = A::singleton('model_orders');
        $mFlag = $oOrders->addOrders( array(
                'iLotteryId'    => intval($aDatas['lotteryid']),
                'iMethodId'     => intval($aDatas['methodid']),
                'iFromUserId'   => intval($aDatas['userid']),
                'iToUserId'     => intval($aDatas['buyuserid']),
                'iProjectId'    => intval($aDatas['projectid']),
                'iTaskId'       => intval($aDatas['taskid']),
                'iOrderType'    => ORDER_TYPE_XSFD,  // 销售返点
                'fMoney'        => floatval($aDatas['diffmoney']),
        ));
        if( TRUE !== $mFlag )
        {
            return $mFlag;
        }

        // 更新状态 userdiffpoints
        $this->oDB->query( "UPDATE `userdiffpoints` SET `status`=1 WHERE `entry`='"
                    . intval($aDatas['pointentry'])."' AND `status`=0 AND `cancelstatus`=0");
        if( 1 != $this->oDB->ar() )
        {
            return FALSE;
        }
        return TRUE;
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