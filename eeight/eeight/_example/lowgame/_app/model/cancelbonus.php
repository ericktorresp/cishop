<?php
/**
 * 文件 : /_app/model/cancelbonus.php
 * 功能 : 数据模型 - 撤销派奖
 *
 * - doCancelBonus()            撤销派奖
 * - doProcessCancelBonus()     撤消派奖内部调用[**被嵌套在事务内**]
 * - doLockUserFund()           锁用户资金
 * - setLoopMode()              设置是否循环运行
 * - setSteps()                 设置每次处理 n 条手，睡眠多少秒
 * - setProcessMax()            设置运行时 一次处理的条数
 * 
 * @author    Tom     090914
 * @version   1.2.0
 * @package   lowgame
 */

class model_cancelbonus extends basemodel
{
    // ---------------------------------[ 属性 ]-------------------------------------------
    // 如果 $iStepCounts, $iStepSec 任何一个为 0, 则不做任何限制, 程序将不会进入 SLEEP 状态
    private $iStepCounts = 0;     // [配合] 每次处理 n 条后, PHP 睡眠 $this->iStepSec 秒
    private $iStepSec    = 0;     // [配合] 每次处理$this->$iStepCounts 条后, PHP 睡眠 n 秒

    private $iProcessMax = 0;     // 每次获取处理真实扣款的方案数, 即: Limit 数量, 0 为不限制
    private $iProcessRecord = 0;  // 本次执行更新的方案数量

    private $bLoopMode = FALSE;   // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行 

    private $sCode      = '';     // 开奖号码
    private $sIssue     = '';     // 奖期名字  issueinfo.issue
    private $iIssueId   = 0;      // 奖期编号  issueinfo.issueid
    private $iLotteryId = 0;      // 彩种ID



    // ---------------------------------[ 方法 ]-------------------------------------------
    /**
     * 撤销派奖
     * @param array $aType
     * @return int  正数表示已撤销派奖的执行数量, 负数表示错误
     *    $aData['entry']     = 0             异常任务编号, IssueError.entry
     *    $aData['lotteryid'] = 0             彩种ID
     *    $aData['issue']     = 2009239       奖期编号
     * 二选一的传递
     *    $aData['time'] = 2009-09-09 15:25   根据时间撤销
     *    $aData['code'] = xxx                撤销整期
     * 
     * 返回值 string 直接返回消息 ，成功执行返回TRUE
     */
    public function doCancelBonus( $aData = array() )
    {
    	$iAffectedSuccess = 0;                            //本次操作成功所影响的数据
        $iAffectedFailed  = 0;                            //本次操作失败所影响的数据
    	$sMsg             = "[doCancelBonus] ";           //错误信息
        // 1, 参数有效性&完整性检查
        if( empty($aData['entry']) || empty($aData['lotteryid']) || empty($aData['issue']) 
            || (empty($aData['time']) && empty($aData['code']) ) )
        {
            return $sMsg." wrong param\n";
        }

        // 2, 更新 IssueError 状态值, 撤销返奖进行中(抢占优先级, 使其他工作中的CLI及时中断)
        $this->oDB->update( "issueerror", array( 'statuscancelbonus' => 1 ), " `entry`='".$aData['entry']."' " );
        if( $this->oDB->errno() > 0 )
        { // 更新 IssueError 状态值失败 (抢占优先级更新失败)
            return $sMsg." update issueerror sql failed[success:".$iAffectedSuccess.",failed:".$iAffectedFailed."]\n"; 
        }


        // 3, 检查 IssueInfo '扣款状态','返点状态','中奖判断','奖金派发' 4个状态
        //    如果任意一个进程进行中, 则返回错误等待下次调用
        $this->oDB->getOne("SELECT 1 FROM `issueinfo` WHERE `lotteryid`='".$aData['lotteryid']
                . "' AND `issue`='".$aData['issue']."' "
                . " AND ( `statusdeduct`=1 OR `statususerpoint`=1 OR `statuscheckbonus`=1 OR `statusbonus`=1 ) ");
        if( $this->oDB->ar() )
        { // 有其他 CLI 程序正在进行中, 未中断
            return $sMsg."Issue some status is running[success:".$iAffectedSuccess.",failed:".$iAffectedFailed."]\n";
        }


        // 4, 获取需要撤销派奖的方案结果集
        $aCancelProjects = array(); // 结果集
        if( !empty($aData['time']) )
        { // 根据时间进行撤销派奖. 获取该奖期某时间点以后, 所有已派奖的方案
            $aCancelProjects = $this->oDB->getAll("SELECT `projectid`,`userid`,`taskid`,`lotteryid`, "
                        . " `methodid`,`bonus` FROM `projects` WHERE `issue`='".$aData['issue']
                        ."'  AND `lotteryid`='".$aData['lotteryid']
                        . "' AND `prizestatus`=1 AND `writetime` >='".$aData['time'] . "' ");
        }
        elseif( !empty($aData['code']) )
        { // 整期全撤, 获取该奖期所有已派奖的方案
            $aCancelProjects = $this->oDB->getAll("SELECT `projectid`,`userid`,`taskid`,`lotteryid`, "
                        . " `methodid`,`bonus` FROM `projects` WHERE `issue`='".$aData['issue']
                        ."'  AND `lotteryid`='".$aData['lotteryid']
                        . "' AND `prizestatus`=1 ");
        }
        else
        {// 撤销派奖类型错误
            return $sMsg." wrong param type[success:".$iAffectedSuccess.",failed:".$iAffectedFailed."]\n"; 
        }


        // 5, 判断是否已完整执行全部的 '撤销派奖',
        //     5.1   未完成则继续处理 
        //     5.2   完成则更新 IssueError.statuscancelbonus=2
        $iCounts = count($aCancelProjects);
        if( 0 == $iCounts )
        {// 所有方案全部完成 '撤销派奖' 则返回全等于的 TRUE 值
            return TRUE;
        }


        // 6, 对所有符合要求的方案进行撤销派奖(奖金扣回). 遇到资金锁定则忽略并等待下一次执行
        //    奖金扣回后, 将方案
        //        1. projects.`isgetprize` = 0    (方案未中奖) 
        //        2. projects.`prizestatus` = 0   (方案未派奖) 
        //        3. Projects.`bonus`=0           (方案的奖金设置为0)
        for( $i=0; $i<$iCounts; $i++ )
        { // 循环进行
        // 1, 试图锁用户资金
            if( TRUE !== $this->doLockUserFund( $aCancelProjects[$i]['userid'], TRUE ) )
            { // 如果锁用户资金失败, 继续处理下一条方案
            	$iAffectedFailed += 1;
                $sMsg .= "[n] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [Lock] uid='".
                         $aCancelProjects[$i]['userid']."' Ignored\n";
                continue;
            }

            // 2, 开始业务流程操作
            //   2.1  事务执行[开始,提交,回滚]遇到的失败, 返回负数, 中断整个CLI程序
            //   2.2  业务流程处理失败, 则解锁, 继续处理下一条方案
            if( FALSE == $this->oDB->doTransaction() ) 
            {
            	$iAffectedFailed += 1;
                $this->doLockUserFund( $aCancelProjects[$i]['userid'], FALSE ); // 解锁
                return $sMsg.'system error:#5011'."[success:".$iAffectedSuccess.",failed:".$iAffectedFailed."]\n";
            }
            if( TRUE !== ($iFlag=$this->doProcessCancelBonus( $aCancelProjects[$i] )) )
            { // 业务流程执行失败
                // 1, 显示错误信息
                // 2, 事务回滚. 不对本次循环涉及的数据表(table)做任何更改
                // 3, 解锁用户资金账户
                $iAffectedFailed += 1;
                if( $iFlag < 0 )
                { // 1, 如果是账变相关操作失败, 显示其返回的负数助于调试
                    $sMsg .= "[w] [".date('Y-m-d H:i:s')."] AddOrders Failed. ProjectsId='".
                             $aCancelProjects[$i]['projectid']."' orderFlag='$iFlag' Ignored\n";
                }
                if( FALSE == $this->oDB->doRollback() )
                { // 2, 事务回滚发生失败. 则中断
                    return $sMsg.'system error:#5012[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
                }
                if( FALSE == $this->doLockUserFund( $aCancelProjects[$i]['userid'], FALSE ) )
                { // 3, 对用户资金账户进行解锁, 失败则报错并忽略
                    $sMsg .= "[w] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [UnLock] uid='".
                              $aCancelProjects[$i]['userid']."' Ignored\n";
                }
                continue;
            }
            else
            { // 业务流程执行成功, 则执行业务逻辑原子操作的提交
                if( FALSE == $this->oDB->doCommit() ) 
                { // 事务提交发生失败. 则中断CLI程序运行
                    $this->doLockUserFund( $aCancelProjects[$i]['userid'], FALSE ); // 解锁
                    $iAffectedFailed += 1;
                    return $sMsg.'system error:#5013[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
                }
                 
            }
            
            // 成功处理 '撤销派奖' 数+1

            // 3, 对用户资金进行解锁
            if( TRUE !== $this->doLockUserFund( $aCancelProjects[$i]['userid'], FALSE ) )
            { // 如果锁用户资金失败, 跳过下面代码, 继续处理下一条更新
            	$iAffectedFailed += 1;
                $sMsg .= "[w] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [UnLock] uid='".
                         $aCancelProjects[$i]['userid']."' Ignored\n";
                continue;
            }
            $iAffectedSuccess += 1; //执行成功
        }
        if( $iAffectedFailed > 0 )
        {
            return 'action ok[success:'.$iAffectedSuccess.',failed:'.$iAffectedFailed.']'."\n";
        }
        else
        {
        	return TRUE;
        }
    }


    /**
     * [**被嵌套在事务内**]  
     *    处理业务流程操作,  撤销派奖. 需忽略资金负数
     * $aDatas = array(
     *    [lotteryid] => 1
     *    [methodid] => 9
     *    [projectid] => 11
     *    [userid] => 31
     *    [taskid] => 0
     *    [bonus] => 奖金
     * );
     * @param array  $iUserId
     * @return mix  操作成功返回全等于的 TRUE, 否则返回错误信息
     */
    private function doProcessCancelBonus( $aDatas = array() )
    {
        // 1, 写入撤销派奖的账变
        $fRealBonusMoney = floatval($aDatas['bonus']);
        /* @var $oOrders model_orders */
        $oOrders         = A::singleton('model_orders');
        $mFlag = $oOrders->addOrders( array(
                'iLotteryId'    => intval($aDatas['lotteryid']),
                'iMethodId'     => intval($aDatas['methodid']),
                'iFromUserId'   => intval($aDatas['userid']),
                'iProjectId'    => intval($aDatas['projectid']),
                'iTaskId'       => intval($aDatas['taskid']),
                'iOrderType'    => ORDER_TYPE_CXPJ,  // 撤销派奖
                'fMoney'        => $fRealBonusMoney,
                'bIgnoreMinus'  => TRUE,             // 忽略资金负数
        ) );
        if( TRUE !== $mFlag )
        {
            return $mFlag;
        }

        // 3, 更新 projects 状态值及 projects.bonus 
        $this->oDB->query( "UPDATE `projects` SET `bonus`=0, `isgetprize`=0, `prizestatus`=0, `updatetime`='".date('Y-m-d H:i:s')."' WHERE `projectid`='"
                           . intval($aDatas['projectid'])."' AND `isgetprize`='1' AND `prizestatus`='1' " );
        if( 1 != $this->oDB->ar() || $this->oDB->errno() )
        {
            return FALSE;
        }
        return TRUE;
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
         /* @var $oUserFund model_userfund */
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