<?php
/**
 * 文件 : /_app/model/sendbonus.php
 * 功能 : 数据模型 - 派奖
 *
 * @author    Tom     090908 13:05
 * @version   1.2.0
 * @package   lowgame
 */

class model_sendbonus extends basemodel
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
     * 根据彩种ID, 对中奖方案进行奖金派送
     * @param int $iLotteryId
     * @return mix
     */
    public function doSendBonus( $iLotteryId )
    {
        $this->iLotteryId = intval($iLotteryId);

        // 1, 判断彩种ID 的有效性
        $oLottery   = A::singleton('model_lottery');
        $aRes       = $oLottery->lotteryGetOne( ' `lotteryid` ', " `lotteryid` = '$this->iLotteryId' " );
        if( empty($aRes) )
        {
            return -1001; // 彩种ID错误
        }
        unset($aRes);

        // 2, 获取需处理的奖期信息 ( From Table.`IssueInfo` )
        //     2.1  开奖号码已验证的         issueinfo.statuscode = 2
        //     2.2  完整执行中奖判断的       issueinfo.statuscheckbonus = 2
        //     2.3  未完整执行奖金派发的     issueinfo.statusbonus != 2
        //     2.4  符合当前彩种ID的         issueinfo.lotteryid = $iLotteryId
        //     2.5  已停售的                 issueinfo.saleend < 当前时间
        //     2.6  为了按时间顺序线性执行, 取最早一期符合以上要求的  ORDER BY A.`saleend` ASC
        $oIssue       = A::singleton('model_issueinfo');
        $sCurrentTime = date( "Y-m-d H:i:s", time() );
        $sFileds      = " A.`issueid`, A.`issue`, A.`code`, A.`salestart` ";
        $sCondition   = " A.`statuscode`=2 AND A.`statuscheckbonus`=2 AND A.`statusbonus`!=2 AND A.`lotteryid`='"
                        . $this->iLotteryId."' AND A.`saleend`<'$sCurrentTime' ORDER BY A.`saleend` ASC LIMIT 1 ";
        $aRes         = $oIssue->IssueGetOne( $sFileds, $sCondition );
        unset($oIssue);
        if( empty($aRes) )
        {
            if( 0 != $this->iProcessRecord )
            {
                echo '[d] Total Processed(projects): '.$this->iProcessRecord."\n";
            }
            return -1002; // 未获取到需要进行奖金派送的奖期号 (所有奖金派送皆以完成)
        }
        $this->sIssue   = $aRes['issue'];            // 需进行 '中奖判断' 的首个奖期编号
        $this->iIssueId = intval($aRes['issueid']);  // 奖期表的自增ID号
        $this->sCode    = $aRes['code'];             // 奖期的开奖号码
        $sIssueSalestart= date('Y-m-d', strtotime($aRes['salestart']));

        if( empty($this->sIssue) || empty($this->iIssueId) || 0==strlen($this->sCode) )
        {
            return -1003; // 数据无效
        }
        unset($aRes);


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
            $this->oDB->update( "issueinfo", array( 'statusbonus' => 0 ), 
                        " `statusbonus`!=2 AND `issueid`=$this->iIssueId" );
            return -1008;
        }


        // 4, 获取所有已中奖, 并且尚未执行 '奖金派送' 的当期方案
        //     3.1  根据奖期号 $sIssue 查询方案表 projects
        //     3.2  '中奖状态' 状态为:    '中奖'  projects.`isgetprize`   = 1
        //     3.3  '奖金派送' 状态为: 非 '已派'  projects.`prizestatus` != 1 
        $sLimit      = $this->iProcessMax==0 ? '' : ' LIMIT '.intval($this->iProcessMax);
        $aRes        = $this->oDB->getAll( "SELECT p.`lotteryid`,p.`methodid`,p.`projectid`,p.`userid`, p.`taskid`, m.`functionname` "
                        . " FROM `projects` p LEFT JOIN `method` m ON(p.`methodid`=m.`methodid`) " 
                        . " WHERE p.`issue`='$this->sIssue' AND p.`lotteryid`='$this->iLotteryId' AND "
                        . " p.`iscancel`=0 AND p.`isgetprize`=1 AND p.`prizestatus`!=1 $sLimit ");
        $iCounts     = count($aRes);  // 实际获取的需处理方案个数
        $sDebugMsg = "[d] [".date('Y-m-d H:i:s')."] Issue='$this->sIssue', LotteryId='$this->iLotteryId' CliProcessLimit='".$this->iProcessMax."', GotDataCounts='$iCounts' ";
        echo $sDebugMsg."\n";



        // 5, 如果获取的结果集为空, 则表示当前奖期已全部'奖金派送'完成. 更新状态值
        if( 0 == $iCounts )
        { // 奖期标记设置为: 已经完成奖金派发
            $iAffected = $this->oDB->update( "issueinfo", array( 'statusbonus' => 2 ), 
                    " `statuscheckbonus`=2 AND `statusbonus`!=2 AND `issueid`=$this->iIssueId LIMIT 1"  );
            if( 1 != $iAffected )
            {
                return -3001; // 更新奖期状态值失败 (可能判断中奖未完成执行)
            }
            if( $this->bLoopMode == TRUE )
            {
                return $this->doSendBonus( $this->iLotteryId );
            }
            else
            {
                // 生成单期盈亏数据
                //$oSale = A::singleton("model_sale");
                $oSale = new model_sale();
                $oSale->createSingSale( $this->iLotteryId, $this->sIssue, $sIssueSalestart );
                return -1004; // 当前奖期'奖金派送'已经全部完成
            }
        }
        else 
        { // 奖期标记设置为: 进行'奖金派送'中
            $this->oDB->update( "issueinfo", array( 'statusbonus' => 1 ),  " `issueid`=$this->iIssueId " );
        }
        unset( $iAffected );


        /** 
         * 6, [循环] 对方案进行'奖金派送'的原子操作, 遇到用户资金被锁等userFund错误则忽略,继续处理下一条
         *       - 1, 写 '奖金派送' 的账变 (同时对用户资金进行操作)
         *       - 2, 对方案表 Projects 的关联数据 更新其  
         *             - projects.prizestatus=1  (奖金已派发)
         *             - projects.bonus=xxxx     (更新方案实际获得的奖金值)
         * 消息类型:
         * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
         *   [d]   表示 debug 消息
         *   [n]   表示 notice 错误, 并不重要. 例: 用户资金暂时被锁, 将在下次执行再试
         *   [w]   表示 warnning 错误, 重要!!! 例: 由本程序对用户资金账户锁定后, 无法解锁
        */
        for( $i=0; $i<$iCounts; $i++ )
        {
            // 1, 试图锁用户资金
            if( TRUE !== $this->doLockUserFund( $aRes[$i]['userid'], TRUE ) )
            { // 如果锁用户资金失败, 继续处理下一条方案
                echo "[n] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [Lock] uid='".$aRes[$i]['userid']."' Ignored\n";
                continue;
            }

            // 2, 开始业务流程操作
            //   2.1  事务执行[开始,提交,回滚]遇到的失败, 返回负数, 中断整个CLI程序
            //   2.2  业务流程处理失败, 则解锁, 继续处理下一条方案
            if( FALSE == $this->oDB->doTransaction() ) 
            {
                $this->doLockUserFund( $aRes[$i]['userid'], FALSE ); // 解锁
                return -2001;
            }
            if( TRUE !== ($iFlag=$this->doProcess( $aRes[$i] )) )
            { // 业务流程执行失败
                // 1, 显示错误信息
                // 2, 事务回滚. 不对本次循环涉及的数据表(table)做任何更改
                // 3, 解锁用户资金账户
                if( $iFlag < 0 )
                { // 1, 如果是账变相关操作失败, 显示其返回的负数助于调试
                    echo "[w] [".date('Y-m-d H:i:s')."] AddOrders Failed. ProjectsId='".$aRes[$i]['projectid']."' orderFlag='$iFlag' Ignored\n";
                }
                if( FALSE == $this->oDB->doRollback() )
                { // 2, 事务回滚发生失败. 则中断
                    $this->doLockUserFund( $aRes[$i]['userid'], FALSE ); // 解锁
                    return -2002;
                }
                if( FALSE == $this->doLockUserFund( $aRes[$i]['userid'], FALSE ) )
                { // 3, 对用户资金账户进行解锁, 失败则报错并忽略
                    echo "[w] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [UnLock] uid='".$aRes[$i]['userid']."' Ignored\n";
                }
                continue;
            }
            else
            { // 业务流程执行成功, 则执行业务逻辑原子操作的提交
                if( FALSE == $this->oDB->doCommit() ) 
                { // 事务提交发生失败. 则中断CLI程序运行
                    $this->doLockUserFund( $aRes[$i]['userid'], FALSE ); // 解锁
                    return -2003;
                }
            }

            $this->iProcessRecord += 1; // 如果成功执行一次原子操作, 则+1

            // 3, 对用户资金进行解锁
            if( TRUE !== $this->doLockUserFund( $aRes[$i]['userid'], FALSE ) )
            { // 如果锁用户资金失败, 跳过下面代码, 继续处理下一条更新
                echo "[w] [".date('Y-m-d H:i:s')."] LockUserFund Failed. [UnLock] uid='".$aRes[$i]['userid']."' Ignored\n";
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
            return $this->doSendBonus( $this->iLotteryId );
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
     *    处理业务流程操作,  奖金派送
     * $aDatas = array(
     *    [lotteryid] => 1
     *    [methodid] => 9
     *    [projectid] => 11
     *    [userid] => 31
     *    [taskid] => 0
     *    [functionname] => 3d_zhixuan
     * );
     * @param array  $iUserId
     * @return mix  操作成功返回全等于的 TRUE, 否则返回错误信息
     */
    private function doProcess( $aDatas = array() )
    {
        // 1, 获取用户真实奖金.
        if( FALSE === ($fRealBonusMoney = $this->getMoney( $aDatas['projectid'], $aDatas['functionname'] ) ))
        {
            return FALSE;
        }

        // 2, 写入账变 + 奖金派发
        $fRealBonusMoney = floatval($fRealBonusMoney);
        $oOrders         = A::singleton('model_orders');
        $mFlag = $oOrders->addOrders( array(
                'iLotteryId'    => intval($aDatas['lotteryid']),
                'iMethodId'     => intval($aDatas['methodid']),
                'iFromUserId'   => intval($aDatas['userid']),
                'iProjectId'    => intval($aDatas['projectid']),
                'iTaskId'       => intval($aDatas['taskid']),
                'iOrderType'    => ORDER_TYPE_JJPS,  // 奖金派送
                'fMoney'        => $fRealBonusMoney,
                //'bIgnoreMinus'  => TRUE,             // 忽略资金负数 TODO: 是否需要?
        ));
        if( TRUE !== $mFlag )
        {
            return $mFlag;
        }

        // 3, 更新 projects 状态值及 projects.bonus
        $this->oDB->query( "UPDATE `projects` SET `bonus`='$fRealBonusMoney', `prizestatus`=1, `updatetime`='".date('Y-m-d H:i:s')."' WHERE `projectid`='"
                    . intval($aDatas['projectid'])."' AND `prizestatus`=0 ");
        if( 1 != $this->oDB->ar() || $this->oDB->errno() )
        {
            return FALSE;
        }
        return TRUE;
    }



    /**
     * 根据方案编号ID, 获取方案真实中奖金额
     * 将相同的派奖判断形式, 使用相同的 TagName. 便于增加新玩法的派奖
     * 
     * @param int      $iProjectId
     * @param string   $sFunctionName
     * @return mix  成功:返回浮点数的金额.  失败:全等于的FALSE
     */
    private function getMoney( $iProjectId, $sFunctionName='' )
    {
        if( empty($iProjectId) || empty($sFunctionName) )
        {
            return FALSE;
        }
        // 解析函数名. 相同派奖算法, 使用相同的 TagName 为参数的 getRealMoneyByTagName() 函数
        switch( $sFunctionName )
        {
            // 3D 部分
            case '3d_zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zhixuan', $this->sCode, $iProjectId );
            }
            case '3d_zhixuanhezhi' :
            {
                return $this->getRealMoneyByTagName( 'n3_zhixuan', $this->sCode, $iProjectId  );
            }
            case '3d_tongxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_tongxuan', $this->sCode, $iProjectId  );
            }
            case '3d_zusan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zusan', $this->sCode, $iProjectId  );
            }
            case '3d_zuliu' :
            {
                return $this->getRealMoneyByTagName( 'n3_zuliu', $this->sCode, $iProjectId  );
            }
            case '3d_hunhezuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_hunhezuxuan', $this->sCode, $iProjectId  );
            }
            case '3d_zuxuanhezhi' :
            {
                return $this->getRealMoneyByTagName( 'n3_hezhi', $this->sCode, $iProjectId  );
            }
            case '3d_yimabudingwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_1mbudingwei', $this->sCode, $iProjectId  );
            }
            case '3d_ermabudingwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_2mbudingwei', $this->sCode, $iProjectId  );
            }
            case '3d_q2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', $this->sCode, $iProjectId  );
            }
            case '3d_h2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', $this->sCode, $iProjectId  );
            }
            case '3d_q2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', $this->sCode, $iProjectId  );
            }
            case '3d_h2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', $this->sCode, $iProjectId  );
            }
            case '3d_baiwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', $this->sCode, $iProjectId  );
            }
            case '3d_shiwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', $this->sCode, $iProjectId  );
            }
            case '3d_gewei' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', $this->sCode, $iProjectId  );
            }
            case '3d_q2daxiaodanshuang' :
            {
                return $this->getRealMoneyByTagName( 'n2_q2dxds', $this->sCode, $iProjectId  );
            }
            case '3d_h2daxiaodanshuang' :
            {
                return $this->getRealMoneyByTagName( 'n2_h2dxds', $this->sCode, $iProjectId  );
            }

            // p5-p3 部分
            case 'p3_zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zhixuan', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_zhixuanhezhi' :
            {
                return $this->getRealMoneyByTagName( 'n3_zhixuan', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_tongxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_tongxuan', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_zusan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zusan', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_zuliu' :
            {
                return $this->getRealMoneyByTagName( 'n3_zuliu', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_hunhezuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_hunhezuxuan', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_zuxuanhezhi' :
            {
                return $this->getRealMoneyByTagName( 'n3_hezhi', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_yimabudingwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_1mbudingwei', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_ermabudingwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_2mbudingwei', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_q2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_h2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_q2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_h2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p5_wanwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p5_qianwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p5_baiwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_common', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p5_shiwei' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,3,2), $iProjectId  );
            }
            case 'p5_gewei' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,3,2), $iProjectId  );
            }
            case 'p3_q2daxiaodanshuang' :
            {
                return $this->getRealMoneyByTagName( 'n2_q2dxds', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p3_h2daxiaodanshuang' :
            {
                return $this->getRealMoneyByTagName( 'n2_h2dxds', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'p5_h2daxiaodanshuang' :
            {
                return $this->getRealMoneyByTagName( 'n2_p5h2dxds', substr($this->sCode,3,2), $iProjectId  );
            }
            case 'p5_h2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,3,2), $iProjectId  );
            }
            case 'p5_h2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,3,2), $iProjectId  );
            }
            default:
            {
                echo '[d] unknown FunctionName='.$sFunctionName."\n";
                return FALSE;
            }
        }
    }


    /**
     * 根据 TagName 获取真实中奖金额
     * @param string $sTagName
     * @param string $sCode
     * @param int    $iProjectId
     * @return bool  成功返回浮点数的派奖金额, 失败则返回全等于的 FALSE
     */
    private function getRealMoneyByTagName( $sTagName='', $sCode='', $iProjectId=0 )
    {
        if( empty($sTagName) || 0==strlen($sCode) || empty($iProjectId) )
        {
            return FALSE;
        }

        switch ( $sTagName )
        {
            // ---------------------------------------------------------------------------------
            case 'n3_zhixuan' :
            { // 3位数字直选的中奖金额判断
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" ORDER BY `isspecial` DESC LIMIT 1 ");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_tongxuan' :
            { // 3位数字通选的中奖金额判断
                // 通选在数据库 expandcode 表中, 不剔除相同号码. 奖金存储的是单倍
                // 需在 PHP 中进行遍历, 对奖金进行累加
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber_1 = substr($sCode,0,1);
                $aNumber_2 = substr($sCode,1,1);
                $aNumber_3 = substr($sCode,2,1);
                $aTmpMatchArray = array();  // for 匹配数组
                $iRates = 1;                // 匹配次数
                $fBingoMoney = 0.00;        // 中奖金额
                // ----------------------------------------------
                // 获取中奖单
                $sRegExp3 = $aNumber_1.'[0-9]{2}|[0-9]{1}'.$aNumber_2.'[0-9]{1}|[0-9]{2}'.$aNumber_3;
                $aRow = $this->oDB->getAll("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                     . " AND `expandcode` REGEXP \"$sRegExp3\" ORDER BY `level` ASC "); // 正序排
                //print_r($aRow);exit;
                if( 3 != count($aRow) )
                {
                    return FALSE;
                }
                $fFinalBonus = 0.00;            // 用户最终奖金
                $sUserExpandCode = $aRow[0]['expandcode']; // 用户方案: 所有通选号码(有重复:例 155|155|156)
                $fPrize_1 = $aRow[0]['prize'];  // 用户方案: 1等奖奖金 (单倍)
                $fPrize_2 = $aRow[1]['prize'];  // 用户方案: 2等奖奖金 (单倍)
                $fPrize_3 = $aRow[2]['prize'];  // 用户方案: 3等奖奖金 (单倍)

                // 计算 1 等奖奖金
                $iCountPrize1 = 0;  // 1等奖数量
                $iCountPrize1 = preg_match_all( "/($sCode)/", $sUserExpandCode, $aTmpMatchArray );
                $fFinalBonus += $iCountPrize1 * $fPrize_1;

                // 计算 2等奖奖金
                $iCountPrize2 = 0;  // 2等奖数量
                $sRegExp2 = $aNumber_1 . $aNumber_2 . "[0-9]{1}|".
                            $aNumber_1 . "[0-9]{1}" . $aNumber_3."|".
                            "[0-9]{1}" . $aNumber_2 . $aNumber_3;
                $iCountPrize2 = preg_match_all( "/($sRegExp2)/", $sUserExpandCode, $aTmpMatchArray );
                $fFinalBonus += ($iCountPrize2-$iCountPrize1) * $fPrize_2;

                // 计算 3等奖奖金
                $iCountPrize3 = 0;  // 3等奖数量
                $sRegExp3 = $aNumber_1.'[0-9]{2}|[0-9]{1}'.$aNumber_2.'[0-9]{1}|[0-9]{2}'.$aNumber_3;
                $iCountPrize3 = preg_match_all( "/($sRegExp3)/", $sUserExpandCode, $aTmpMatchArray );
                $fFinalBonus += ($iCountPrize3-$iCountPrize2) * $fPrize_3;

                if( $fFinalBonus>0 )
                {
                    return $fFinalBonus;
                }
                // 未匹配,则返回错误
                return FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_zusan' :
            { // 3位数字组三中奖金额判断
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`=1 AND ( `expandcode` REGEXP \"$sCode\" OR `isspecial`=0 ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 ");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_zuliu' :
            { // 3位数字组六中奖金额判断
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`=2 AND ( `expandcode` REGEXP \"$sCode\" OR `isspecial`=0 ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 ");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_hunhezuxuan' :
            { // 3位数字混合组选
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                sort($aNumber);
                $sCodeSorted = $aNumber[0] . $aNumber[1] . $aNumber[2];
                $aNumber = array_unique($aNumber);
                $iLevel = 1;
                if( 2 == count($aNumber) )
                { // 当前期为组3号
                    $iLevel = 1;
                }
                elseif( 3 == count($aNumber) )
                {
                    $iLevel = 2;
                }
                // 1, 取未变价的 isspecial = 0 并且号码符合开奖号码根据正则表达式规则的
                // 2, 取变价的 isspecial = 1  并且号码严格匹配开奖号码顺序的
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`=$iLevel AND ( `isspecial`=0 AND `expandcode` REGEXP \"$sCodeSorted\" "
                                . " OR ( `isspecial`=1 AND `expandcode` REGEXP \"$sCode\"  ) ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 "); 
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_hezhi' :
            { // 3位数字组选和值
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                sort($aNumber);
                $iCodeSum = $aNumber[0] + $aNumber[1] + $aNumber[2];
                $aNumber = array_unique($aNumber);
                $iLevel = 1;
                if( 2 == count($aNumber) )
                { // 当前期为组3号
                    $iLevel = 1;
                }
                elseif( 3 == count($aNumber) )
                {
                    $iLevel = 2;
                }
                // 1, 取未变价的 isspecial = 0 并且号码符合开奖号码根据正则表达式规则的
                // 2, 取变价的 isspecial = 1  并且号码严格匹配开奖号码顺序的
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . ' AND `level`='.$iLevel.' AND ( `isspecial`=0 AND `expandcode` '
                                . ' REGEXP "(^'.$iCodeSum.'$)|(^'.$iCodeSum.'\\\|)|(\\\|'.$iCodeSum.'\\\|)|(\\\|'.$iCodeSum.'$)" '
                                . " OR ( `isspecial`=1 AND `expandcode` REGEXP \"$sCode\"  ) ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 ");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_common' :
            { // 3位数字通用
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND ( `expandcode` REGEXP \"$sCode\" OR `isspecial`=0 ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 ");
                //print_r($aRow);exit;
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            case 'n2_common' :
            { // 2位数字通用
                if( strlen($sCode) != 2 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND ( `expandcode` REGEXP \"$sCode\" OR `isspecial`=0 ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 ");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            // and expandcode REGEXP "";
            case 'n2_q2dxds' :
            { // 3位数字. 前2大小单双
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber_1 = substr($sCode,0,1);
                $aNumber_2 = substr($sCode,1,1);
                $sRegExp   = '[0-9]*'.$aNumber_1.'[0-9]*#[0-9]*'.$aNumber_2.'[0-9]*';
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND ( `isspecial`=0 AND `expandcode` REGEXP \"$sRegExp\" "
                                . " OR ( `isspecial`=1 AND `expandcode` REGEXP \"$sCode\"  ) ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 ");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n2_h2dxds' :
            { // 3位数字. 后2大小单双
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aNumber_1 = substr($sCode,1,1);
                $aNumber_2 = substr($sCode,2,1);
                $sRegExp   = '[0-9]*'.$aNumber_1.'[0-9]*#[0-9]*'.$aNumber_2.'[0-9]*';
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND ( `isspecial`=0 AND `expandcode` REGEXP \"$sRegExp\" "
                                . " OR ( `isspecial`=1 AND `expandcode` REGEXP \"$sCode\"  ) ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 ");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n2_p5h2dxds' :
            { // 3位数字. 排列5后2大小单双
                if( strlen($sCode) != 2 )
                {
                    return FALSE;
                }
                $aNumber_1 = substr($sCode,0,1);
                $aNumber_2 = substr($sCode,1,1);
                $sRegExp   = '[0-9]*'.$aNumber_1.'[0-9]*#[0-9]*'.$aNumber_2.'[0-9]*';
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND ( `isspecial`=0 AND `expandcode` REGEXP \"$sRegExp\" "
                                . " OR ( `isspecial`=1 AND `expandcode` REGEXP \"$sCode\"  ) ) "
                                . " ORDER BY `isspecial` DESC LIMIT 1 ");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_1mbudingwei' :
            { // 3位数字. 二码不定位玩法(1码)
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND ( `expandcode` REGEXP \"$sCode\" OR `isspecial`=0 ) "
                                . " ORDER BY `isspecial` ASC LIMIT 2");
                // 1, 可能返回的结果集为2行
                //  $aRow = Array(
                //      [0] => Array ( [prize] => 6.6000 )   // 不变价
                //      [1] => Array ( [prize] => 6.2000 )   // 变价
                //  )
                $aTmpMatchArray = array();  // for 匹配数组
                $iRates = 1;                // 匹配次数
                $fBingoMoney = 0.00;        // 中奖金额
                if( 1 == count($aRow) )
                { // 中了 '不变价' 的一码不定位
                    $iRates = preg_match_all( "/([$sCode])/", $aRow[0]['expandcode'], $aTmpMatchArray );
                    if( empty($iRates) )
                    { // 匹配0次不中奖
                        return FALSE;
                    }
                    $fBingoMoney = floatval( $aRow[0]['prize'] * $iRates );
                }
                elseif( 2 == count($aRow) )
                { // 中了 '变价' 的一码不定位
                    $iRates = preg_match_all( "/([$sCode])/", $aRow[0]['expandcode'], $aTmpMatchArray );
                    if( empty($iRates) )
                    { // 匹配0次不中奖
                        return FALSE;
                    }
                    $fBingoMoney = floatval( $aRow[1]['prize'] * $iRates );
                }
                else
                {
                    return FALSE;
                }
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_2mbudingwei' :
            { // 3位数字. 二码不定位玩法(2码)
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND ( `expandcode` REGEXP \"$sCode\" OR `isspecial`=0 ) "
                                . " ORDER BY `isspecial` ASC LIMIT 2");
                // 1, 可能返回的结果集为2行
                //  $aRow = Array(
                //      [0] => Array ( [prize] => 6.6000 )   // 不变价
                //      [1] => Array ( [prize] => 6.2000 )   // 变价
                //  )
                $aTmpMatchArray = array();  // for 匹配数组
                $iRates = 1;                // 匹配次数
                $fBingoMoney = 0.00;        // 中奖金额
                if( 1 == count($aRow) )
                { // 中了 '不变价' 的2码不定位
                    $iRates = preg_match_all( "/([$sCode])/", $aRow[0]['expandcode'], $aTmpMatchArray );
                    if( empty($iRates) )
                    { // 匹配0次不中奖
                        return FALSE;
                    }
                    $iRates = $iRates==2 ? 1 : 3; // 匹配2次, 中1倍奖金, 否则3倍奖金
                    $fBingoMoney = floatval( $aRow[0]['prize'] * $iRates );
                }
                elseif( 2 == count($aRow) )
                { // 中了 '变价' 的2码不定位
                    $iRates = preg_match_all( "/([$sCode])/", $aRow[0]['expandcode'], $aTmpMatchArray );
                    if( empty($iRates) )
                    { // 匹配0次不中奖
                        return FALSE;
                    }
                    $iRates = $iRates==2 ? 1 : 3; // 匹配2次, 中1倍奖金, 否则3倍奖金
                    $fBingoMoney = floatval( $aRow[1]['prize'] * $iRates );
                }
                else
                {
                    return FALSE;
                }
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
        }
        return FALSE;
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