<?php
/**
 * 文件 : /_app/model/sendbonus.php
 * 功能 : 数据模型 - 派奖
 *
 * @author    tom,mark
 * @version   1.2.0
 * @package   highgame
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
    private $iRunTimes = 1;      // 主要用于内部测试, 遇到无方案的奖期的情况,不中断程序.继续循环执行

    private $sCode      = '';           // 开奖号码
    private $aSpecailCode = array();    //开奖号码特殊处理
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
                        . $this->iLotteryId."' AND A.`saleend`<'$sCurrentTime' ORDER BY A.`saleend` ASC";
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
        $this->aSpecailCode = $this->GetSepcailCode($this->sCode,$this->iLotteryId);//开奖号码特殊处理
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
        $aRes        = $this->oDB->getAll( "SELECT p.`lotteryid`,p.`methodid`,p.`projectid`,p.`modes`,p.`userid`, p.`taskid`, m.`functionname` "
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
            // 生成单期盈亏数据
            //$oSale = A::singleton("model_sale");
            $oSale = new model_sale();
            $oSale->createSingSale( $this->iLotteryId, $this->sIssue, $sIssueSalestart );
            if( $this->bLoopMode == TRUE || --$this->iRunTimes > 0 )
            {
                return $this->doSendBonus( $this->iLotteryId );
            }
            else
            {
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
             
        if( $this->bLoopMode == TRUE || --$this->iRunTimes > 0)
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
     *    [functionname] => ssc_zhixuan
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
                'iModesId'      => $aDatas['modes']
                //'bIgnoreMinus'  => TRUE,             // 忽略资金负数 TODO: 是否需要?
        ));
        if( TRUE !== $mFlag )
        {
            return $mFlag;
        }

        // 3, 更新 projects 状态值及 projects.bonus
        $this->oDB->query( "UPDATE `projects` SET `bonus`='$fRealBonusMoney',`prizestatus`=1,`updatetime`='".date("Y-m-d H:i:s")."',`bonustime`='".date("Y-m-d H:i:s")."' WHERE `projectid`='"
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
            // SSC 部分
            case 'ssc_q3zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zhixuan', substr($this->sCode,0,3), $iProjectId );
            }
            case 'ssc_h3zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zhixuan', substr($this->sCode,2,3), $iProjectId );
            }
            case 'ssc_q3zhixuanhezhi' :
            {
                $sCode = substr($this->sCode,0,3);
                $iCodeHz = $sCode{0}+$sCode{1}+$sCode{2};
                return $this->getRealMoneyByTagName( 'n3_zhixuanhezhi', $iCodeHz, $iProjectId  );
            }
            case 'ssc_h3zhixuanhezhi' :
            {
                $sCode = substr($this->sCode,2,3);
                $iCodeHz = $sCode{0}+$sCode{1}+$sCode{2};
                return $this->getRealMoneyByTagName( 'n3_zhixuanhezhi', $iCodeHz, $iProjectId  );
            }
            case 'ssc_q3zusan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zusan', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'ssc_h3zusan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zusan', substr($this->sCode,2,3), $iProjectId  );
            }
            case 'ssc_q3zuliu' :
            {
                return $this->getRealMoneyByTagName( 'n3_zuliu', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'ssc_h3zuliu' :
            {
                return $this->getRealMoneyByTagName( 'n3_zuliu', substr($this->sCode,2,3), $iProjectId  );
            }
            case 'ssc_q3hunhezuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_hunhezuxuan', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'ssc_h3hunhezuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_hunhezuxuan', substr($this->sCode,2,3), $iProjectId  );
            }
            case 'ssc_q3zuxuanhezhi' :
            {
                return $this->getRealMoneyByTagName( 'n3_hezhi', substr($this->sCode,0,3), $iProjectId  );
            }
            case 'ssc_h3zuxuanhezhi' :
            {
                return $this->getRealMoneyByTagName( 'n3_hezhi', substr($this->sCode,2,3), $iProjectId  );
            }
            case 'ssc_yimabudingwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_1mbudingwei', substr($this->sCode,2,3), $iProjectId  );
            }
            case 'ssc_ermabudingwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_2mbudingwei', substr($this->sCode,2,3), $iProjectId  );
            }
            case 'ssc_q2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,0,2), $iProjectId  );
            }
            case 'ssc_h2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,3,2), $iProjectId  );
            }
            case 'ssc_q2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,0,2), $iProjectId  );
            }
            case 'ssc_h2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,3,2), $iProjectId  );
            }
            case 'ssc_wanwei' :
            {
                return $this->getRealMoneyByTagName( 'n1_dingwei', substr($this->sCode,0,1), $iProjectId  );
            }
            case 'ssc_qianwei' :
            {
                return $this->getRealMoneyByTagName( 'n1_dingwei', substr($this->sCode,1,1), $iProjectId  );
            }
            case 'ssc_baiwei' :
            {
                return $this->getRealMoneyByTagName( 'n1_dingwei', substr($this->sCode,2,1), $iProjectId  );
            }
            case 'ssc_shiwei' :
            {
                return $this->getRealMoneyByTagName( 'n1_dingwei', substr($this->sCode,3,1), $iProjectId  );
            }
            case 'ssc_gewei' :
            {
                return $this->getRealMoneyByTagName( 'n1_dingwei', substr($this->sCode,4,1), $iProjectId  );
            }
            case 'ssc_q2daxiaodanshuang' :
            {
                return $this->getRealMoneyByTagName( 'n2_dxds', substr($this->sCode,0,2), $iProjectId  );
            }
            case 'ssc_h2daxiaodanshuang' :
            {
                return $this->getRealMoneyByTagName( 'n2_dxds', substr($this->sCode,3,2), $iProjectId  );
            }
             // SSL 部分
            case 'ssl_zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zhixuan', $this->sCode, $iProjectId );
            }
            case 'ssl_zhixuanhezhi' :
            {
                $sCode = $this->sCode;
                $iCodeHz = $sCode{0}+$sCode{1}+$sCode{2};
                return $this->getRealMoneyByTagName( 'n3_zhixuanhezhi', $iCodeHz, $iProjectId  );
            }
            case 'ssl_zusan' :
            {
                return $this->getRealMoneyByTagName( 'n3_zusan', $this->sCode, $iProjectId  );
            }
            case 'ssl_zuliu' :
            {
                return $this->getRealMoneyByTagName( 'n3_zuliu', $this->sCode, $iProjectId  );
            }
            case 'ssl_hunhezuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n3_hunhezuxuan', $this->sCode, $iProjectId  );
            }
            case 'ssl_zuxuanhezhi' :
            {
                return $this->getRealMoneyByTagName( 'n3_hezhi', $this->sCode, $iProjectId  );
            }
            case 'ssl_budingwei' :
            {
                return $this->getRealMoneyByTagName( 'n3_1mbudingwei', $this->sCode, $iProjectId  );
            }
            case 'ssl_q2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,0,2), $iProjectId  );
            }
            case 'ssl_h2zhixuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,1,2), $iProjectId  );
            }
            case 'ssl_q2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,0,2), $iProjectId  );
            }
            case 'ssl_h2zuxuan' :
            {
                return $this->getRealMoneyByTagName( 'n2_common', substr($this->sCode,1,2), $iProjectId  );
            }
            case 'ssl_baiwei' :
            {
                return $this->getRealMoneyByTagName( 'n1_dingwei', substr($this->sCode,0,1), $iProjectId  );
            }
            case 'ssl_shiwei' :
            {
                return $this->getRealMoneyByTagName( 'n1_dingwei', substr($this->sCode,1,1), $iProjectId  );
            }
            case 'ssl_gewei' :
            {
                return $this->getRealMoneyByTagName( 'n1_dingwei', substr($this->sCode,2,1), $iProjectId  );
            }
             // SD11Y 部分
            case 'sd11y_qszhixuan' :
            {
                $aCode = explode(" ", $this->sCode);
                $sCode = $aCode[0] . " " . $aCode[1] . " " . $aCode[2];
                return $this->getRealMoneyByTagName( 'lotto_n3_zhixuan', $sCode, $iProjectId  );
            }
            case 'sd11y_qszhuxuan' :
            {
                $aCode = explode(" ", $this->sCode);
                $sCode = $aCode[0] . " " . $aCode[1] . " " . $aCode[2];
                return $this->getRealMoneyByTagName( 'lotto_n3_zhuxuan', $sCode, $iProjectId  );
            }
            case 'sd11y_q2zhixuan' :
            {
                $aCode = explode(" ", $this->sCode);
                $sCode = $aCode[0] . " " . $aCode[1];
                return $this->getRealMoneyByTagName( 'lotto_n2_zhixuan', $sCode, $iProjectId  );
            }
            case 'sd11y_q2zhuxuan' :
            {
                $aCode = explode(" ", $this->sCode);
                $sCode = $aCode[0] . " " . $aCode[1];
                return $this->getRealMoneyByTagName( 'lotto_n2_zhuxuan', $sCode, $iProjectId  );
            }
            case 'sd11y_budingwei' :
            {
                $aCode = explode(" ", $this->sCode);
                $sCode = $aCode[0] . " " . $aCode[1] . " " . $aCode[2];
                return $this->getRealMoneyByTagName( 'lotto_budingwei', $sCode, $iProjectId  );
            }
            case 'sd11y_dingyiwei' :
            {
                $aCode = explode(" ", $this->sCode);
                return $this->getRealMoneyByTagName( 'lotto_dingweidan', $aCode[0], $iProjectId  );
            }
            case 'sd11y_dingerwei' :
            {
                $aCode = explode(" ", $this->sCode);
                return $this->getRealMoneyByTagName( 'lotto_dingweidan', $aCode[1], $iProjectId  );
            }
            case 'sd11y_dingshanwei' :
            {
                $aCode = explode(" ", $this->sCode);
                return $this->getRealMoneyByTagName( 'lotto_dingweidan', $aCode[2], $iProjectId  );
            }
            case 'sd11y_danshuang' :
            {
                return $this->getRealMoneyByTagName( 'lotto_dingdanshuang', $this->sCode, $iProjectId  );
            }
            case 'sd11y_zhongwei' :
            {
                return $this->getRealMoneyByTagName( 'lotto_zhongwei', $this->sCode, $iProjectId  );
            }
            case 'sd11y_rx1' :
            {
                return $this->getRealMoneyByTagName( 'lotto_renxuan', $this->sCode, $iProjectId, 1  );
            }
            case 'sd11y_rx2' :
            {
                return $this->getRealMoneyByTagName( 'lotto_renxuan', $this->sCode, $iProjectId, 2  );
            }
            case 'sd11y_rx3' :
            {
                return $this->getRealMoneyByTagName( 'lotto_renxuan', $this->sCode, $iProjectId, 3  );
            }
            case 'sd11y_rx4' :
            {
                return $this->getRealMoneyByTagName( 'lotto_renxuan', $this->sCode, $iProjectId, 4  );
            }
            case 'sd11y_rx5' :
            {
                return $this->getRealMoneyByTagName( 'lotto_renxuan', $this->sCode, $iProjectId, 5  );
            }
            case 'sd11y_rx6' :
            {
                return $this->getRealMoneyByTagName( 'lotto_renxuan', $this->sCode, $iProjectId, 6  );
            }
            case 'sd11y_rx7' :
            {
                return $this->getRealMoneyByTagName( 'lotto_renxuan', $this->sCode, $iProjectId, 7  );
            }
            case 'sd11y_rx8' :
            {
                return $this->getRealMoneyByTagName( 'lotto_renxuan', $this->sCode, $iProjectId, 8  );
            }
            case 'bjkl_rx1':
            {
                return $this->getRealMoneyByTagName( 'bjkl_rx1', $this->sCode, $iProjectId, 1  );
            }
            case 'bjkl_rx2':
            {
                return $this->getRealMoneyByTagName( 'bjkl_rx2', $this->sCode, $iProjectId, 2  );
            }
            case 'bjkl_rx3':
            {
                return $this->getRealMoneyByTagName( 'bjkl_rx3', $this->sCode, $iProjectId, 3  );
            }
            case 'bjkl_rx4':
            {
                return $this->getRealMoneyByTagName( 'bjkl_rx4', $this->sCode, $iProjectId, 4  );
            }
            case 'bjkl_rx5':
            {
                return $this->getRealMoneyByTagName( 'bjkl_rx5', $this->sCode, $iProjectId, 5  );
            }
            case 'bjkl_rx6':
            {
                return $this->getRealMoneyByTagName( 'bjkl_rx6', $this->sCode, $iProjectId, 6  );
            }
            case 'bjkl_rx7':
            {
                return $this->getRealMoneyByTagName( 'bjkl_rx7', $this->sCode, $iProjectId, 7  );
            }
            case 'bjkl_hedx':
            {
                return $this->getRealMoneyByTagName( 'bjkl_hedx', $this->sCode, $iProjectId );
            }
            case 'bjkl_heds':
            {
                return $this->getRealMoneyByTagName( 'bjkl_heds', $this->sCode, $iProjectId );
            }
            case 'bjkl_sxpan':
            {
                return $this->getRealMoneyByTagName( 'bjkl_sxpan', $this->sCode, $iProjectId  );
            }
            case 'bjkl_jopan':
            {
                return $this->getRealMoneyByTagName( 'bjkl_jopan', $this->sCode, $iProjectId  );
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
     * @param int    $iParam 指定参数
     * @return bool  成功返回浮点数的派奖金额, 失败则返回全等于的 FALSE
     */
    private function getRealMoneyByTagName( $sTagName='', $sCode='', $iProjectId=0, $iParam = 0 )
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
                $aNumber[0] = substr($sCode,0,1);
                $aNumber[1] = substr($sCode,1,1);
                $aNumber[2] = substr($sCode,2,1);
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
                else if($aPorject['codetype'] == 'input')
                {
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_zhixuanhezhi' :
            { // 3位数字直选和值的中奖金额判断
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_zusan' :
            { // 3位数字组三中奖金额判断
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`=1 LIMIT 1");
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
                                . " AND `level`=2 LIMIT 1");
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
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`=$iLevel LIMIT 1"); 
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
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `level`='".$iLevel."' LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n1_dingwei' :
            {
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" "
                                . " LIMIT 1");
                //print_r($aRow);exit;
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n2_common' :
            { // 2位数字通用
                if( strlen($sCode) != 2 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n2_dxds' :
            { // 3位数字. 大小单双
                if( strlen($sCode) != 2 )
                {
                    return FALSE;
                }
                static $aBSAD = array(    // 大小单双对应号码
                    '0' => array(5,6,7,8,9),  // 大
                    '1' => array(0,1,2,3,4),  // 小
                    '2' => array(1,3,5,7,9),  // 单
                    '3' => array(0,2,4,6,8)   // 双
                );
                $iFristNumber  = substr($sCode,0,1);
                $iSecondNumber = substr($sCode,1,1);
                $sFristString  = '';
                $sSecondString = '';
                foreach( $aBSAD AS $k=>$v )
                {
                    if(in_array( $iFristNumber,  $v ))
                    {
                        $aFristString[]  = $k;
                    }
                    if(in_array( $iSecondNumber,  $v ))
                    {
                        $aSecondString[] = $k;
                    }
                }
                unset( $iFristNumber, $iSecondNumber );
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode`"
                                            ." WHERE `projectid`=$iProjectId  LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aFirstExpandCode = str_split($aExpandCode[0],1);
                $aSecodExpandCode = str_split($aExpandCode[1],1);
                //求取可能中奖的注数
                $iBonusTimes = count(array_intersect($aFirstExpandCode,$aFristString)) * count(array_intersect($aSecodExpandCode,$aSecondString));
                return $this->oDB->ar() ? $aRow['prize']*$iBonusTimes : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_1mbudingwei' :
            { // 3位数字. 后三不定位玩法(1码)
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode,1);
                $aCode = array_unique($aCode);
                $iRates = count(array_intersect($aExpandCode,$aCode)); // 匹配次数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'n3_2mbudingwei' :
            { // 3位数字. 后三不定位玩法(2码)
                if( strlen($sCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode('|',$aRow['expandcode']);
                $aCode = str_split($sCode,1);
                $aCode = array_unique($aCode);
                $iRates = count(array_intersect($aExpandCode,$aCode)) == 2 ? 1 : 3; // 匹配次数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_n3_zhixuan' :
            { //乐透三位直选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 3 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }//单式
                else if($aPorject['codetype'] == 'input')
                {
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_n3_zhuxuan' :
            { //乐透三位组选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 3 )
                {
                    return FALSE;
                }
                sort($aCode);
                $sCode = implode( " ", $aCode );
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
                else if($aPorject['codetype'] == 'input')
                {//单式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_n2_zhixuan' :
            { //乐透二位直选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 2 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
                else if($aPorject['codetype'] == 'input')
                {//单式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_n2_zhuxuan' :
            { //乐透二位组选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 2 )
                {
                    return FALSE;
                }
                sort($aCode);
                $sCode = implode( " ", $aCode );
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId LIMIT 1");
                }
                else if($aPorject['codetype'] == 'input')
                {//单式
                    $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$sCode\" LIMIT 1");
                }
                else 
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_budingwei' :
            { //乐透不定位
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 3 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                if( count($aRow) == 0 )
                {
                    return FALSE;
                }
                $aExpandCode = explode("|",$aRow['expandcode']);
                $aCode = array_unique($aCode);
                $iRates = count(array_intersect($aExpandCode,$aCode)); // 匹配次数
                $fBingoMoney = floatval( $aRow['prize'] * $iRates );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
            // ---------------------------------------------------------------------------------
            case 'lotto_dingweidan' :
            { //乐透定位胆
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 1 )
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
             // ---------------------------------------------------------------------------------
            case 'lotto_dingdanshuang' :
            { //乐透定单双
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 5 )
                {
                    return FALSE;
                }
                 //统计单双个数
                $iSingleCount = 0;//单号个数
                $iDoubleCount = 0;//双号码个数
                foreach ($aCode as $sCodeValue)
                {
                    $sCodeValue%2 == 0 ? $iDoubleCount++ : $iSingleCount++;
                }
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$iSingleCount\" "
                                . " LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
             // ---------------------------------------------------------------------------------
            case 'lotto_zhongwei' :
            { //乐透猜中位
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 5 )
                {
                    return FALSE;
                }
                sort($aCode);
                $aRow = $this->oDB->getOne("SELECT `prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " AND `expandcode` REGEXP \"$aCode[2]\" "
                                . " LIMIT 1");
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
            }
             // ---------------------------------------------------------------------------------
            case 'lotto_renxuan' :
            { //乐透任选
                $aCode = explode( " ", $sCode );
                if( count($aCode) != 5 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                                . " LIMIT 1");
                if( count($aRow) == 0 || count($aPorject) == 0 )
                {
                    return FALSE;
                }
                $aCode = array_unique($aCode);
                if($aPorject['codetype'] == 'digital')
                {//复式
                    $aExpandCode = explode("|", $aRow['expandcode']);
                    $iRates = count(array_intersect($aExpandCode,$aCode)); // 匹配次数
                    if( $iRates < $iParam && $iParam <= 5)
                    {//匹配到没有中奖的单子
                        return FALSE;
                    }
                    if( $iParam > 5 && $iRates != 5)
                    {//匹配到没有中奖的单子
                        return FALSE;
                    }
                    //计算中奖倍数，
                    $iBonusTimes = 0;
                    if( $iParam <= 5 )
                    {
                        //如：任选二中二，选择的号码与中奖号码交集个数为3，则中奖倍数为:C(3,2)=3.
                        $iBonusTimes = $this->GetCombinCount( $iRates, $iParam );
                    }
                    else if(in_array($iParam,array(6,7,8)))
                    {
                        //如任选八中五:C(n-5,8-5);
                        $iBonusTimes = $this->GetCombinCount( count($aExpandCode) - 5, $iParam - 5 );
                    }
                    else
                    {
                        return FALSE;
                    }
                }
                else if($aPorject['codetype'] == 'input')
                {//单式
                    //计算中奖倍数，
                    $iBonusTimes = 0;
                    sort($aCode);
                    $iSelect = $iParam > 5 ? 5 : $iParam;
                    $aTmpCode = $this->getCombination($aCode, $iSelect);//可能中奖的组合
                    sort($aTmpCode);
                    foreach ( $aTmpCode as $sCode )
                    {
                        $sCode = trim($sCode,' ');
                        if( $iParam > 5 )
                        {
                            $sCode = str_replace(' ','[^\\|]*',$sCode);
                        }
                        $aRegExp[] = '('.$sCode.')';
                    }
                    $sRegExpTmp = implode("|", $aRegExp);
                    $tmpArray = array();
                    $iBonusTimes = preg_match_all("/$sRegExpTmp/", $aRow['expandcode'], $tmpArray);// 匹配次数
                    unset($tmpArray);
                }
                else 
                {
                    return FALSE;
                }
                //计算最终的奖金
                $fBingoMoney = floatval( $aRow['prize'] * $iBonusTimes );
                return ($this->oDB->ar() && $fBingoMoney>0) ? $fBingoMoney : FALSE;
            }
            //北京快乐八奖金发放
            /**
             * 
             * 任选玩法奖金计算方法:n所选复式号与中奖号码的交集个数，m所选号码个数,thelevelBouns对应奖级奖金
             * 任选一:C(n,1)    [n>=1]
             * 任选二:C(n,2)    [n>=2]
             * 任选三:C(n,2)*C(m-n,1)*thelevelBouns+C(n,3)*thelevelBouns   [n>=2]
             * 任选四:C(n,2)*C(m-n,2)*thelevelBouns+C(n,3)*C(m-n,1)*thelevelBouns+C(n,4)*thelevelBouns [n>=2]
             * 任选五:C(n,3)*C(m-n,2)*thelevelBouns+C(n,4)*C(m-n,1)*thelevelBouns+C(n,5)*thelevelBouns [n>=3]
             * 任选六:C(n,3)*C(m-n,3)*thelevelBouns+C(n,4)*C(m-n,2)*thelevelBouns+C(n,5)*C(m-n,1)*thelevelBouns+C(n,6)*thelevelBouns [n>=3]
             * 任选七:C(n,4)*C(m-n,3)*thelevelBouns+C(n,5)*C(m-n,2)*thelevelBouns+C(n,6)*C(m-n,1)*thelevelBouns+C(n,7)*thelevelBouns [n>=4]-----[n=1,m=8] times=1-------[n=0] times=C(n,7)五等奖
             * 
             * 以上计算方法的规律采用循环计算
             * 
             * $fBingoMoney = 0;/最终奖金
             * for($i=最小中奖号码个数[选7中0单独计算];$i<最大中奖号码个数;$i++)
             * {
             *   $iLevel = 最大中奖号码个数+1-($i > 最大中奖号码个数 ? 最大中奖号码个数 : $i);//对应奖级
             *   $iBonusTimes = Combin(所选号码与开奖号码交集个数,$i)*Combin(所选号码个数-所选号码与开奖号码交集个数,最大中奖号码个数-$i);//对应奖级中奖注数
             *   $fBingoMoney += floatval( 当前奖级对应奖金 * $iBonusTimes(中奖注数) );//对应奖级的奖金
             * }
             * 
             */
            case 'bjkl_rx1':
            case 'bjkl_rx2':
            case 'bjkl_rx3':
            case 'bjkl_rx4':
            case 'bjkl_rx5':
            case 'bjkl_rx6':
            case 'bjkl_rx7':
                $aCode = explode( " ", $sCode );
                $aCode = array_unique($aCode);
                if( count($aCode) != 20 )
                {
                    return FALSE;
                }
                $aPorject = $this->oDB->getOne("SELECT `code` FROM `projects` WHERE `projectid` = '".$iProjectId."' LIMIT 1");
                if(empty($aPorject))
                {
                    return FALSE;
                }
                $aRow = $this->oDB->getAll("SELECT `level`,`prize` FROM `expandcode` WHERE `projectid`='".$iProjectId."'");
                if(empty($aRow))
                {
                    return FALSE;
                }
                $iSelNum = intval(substr($sTagName,-1));//玩法最少选择的选择号码个数
                $aLevelCount = array(1=>1,2=>1,3=>2,4=>3,5=>3,6=>4,7=>5);//各个玩法奖级个数
                if(count($aRow) != $aLevelCount[$iSelNum])
                {
                    return FALSE;
                }
                $aLevelBonus = array();
                foreach ($aRow as $aLevel)
                {
                    $aLevelBonus[$aLevel['level']] = $aLevel['prize'];//获取各个奖级的奖金
                }
                $aProjectCode = explode("|",$aPorject['code']);
                $iInterCount = count(array_intersect($aCode,$aProjectCode));
                $iCodeCount = count($aProjectCode);
                $aMinNumCount = array(1=>1,2=>2,3=>2,4=>2,5=>3,6=>3,7=>4);//各个玩法最少中奖号码个数,7中0单独计算
                $fBingoMoney = 0.00;//最终奖金
                if( ($iSelNum == 1 && $iInterCount < 1)
                    ||(in_array($iSelNum,array(2,3,4)) && $iInterCount < 2) 
                    || (in_array($iSelNum,array(5,6)) && $iInterCount < 3)
                 )
                {
                    return FALSE;
                }
                if($iSelNum == 7 && in_array($iInterCount,array(0,1,2,3)))
                {
                    if( ($iCodeCount == 7 && in_array($iInterCount,array(1,2,3)))
                        || ($iCodeCount == 8 && in_array($iInterCount,array(2,3)))
                        || ($iCodeCount == 9 && $iInterCount == 3)
                    )
                    {
                        return FALSE;
                    }
                    $iLevel = 5;//任选七中零
                    $iBonusTimes = $iCodeCount > $iSelNum ? $this->GetCombinCount($iCodeCount-$iInterCount,$iSelNum): 1;
                    $fBingoMoney = floatval( $aLevelBonus[$iLevel] * $iBonusTimes );
                }
                else 
                {
                    //累积各个奖级下的号码奖金
                    for($i = $aMinNumCount[$iSelNum]; $i<=$iSelNum; $i++ )
                    {
                        $iLevel = $iSelNum+1-$i;//对应奖级
                        $iBonusTimes = $this->GetCombinCount($iInterCount,$i)*$this->GetCombinCount($iCodeCount-$iInterCount,$iSelNum-$i);//对应奖级中奖注数
                        $fBingoMoney += floatval( $aLevelBonus[$iLevel] * $iBonusTimes );//对应奖级的奖金
                    }
                }
                return $fBingoMoney > 0 ? $fBingoMoney : FALSE;
            case 'bjkl_hedx'://和值大小
            case 'bjkl_heds'://和值单双
            case 'bjkl_sxpan'://上下盘
            case 'bjkl_jopan'://奇偶盘
                $aPorject = $this->oDB->getOne("SELECT `codetype` FROM `projects` WHERE `projectid` = '".$iProjectId."' AND `code` REGEXP '^.*".$this->aSpecailCode[$sTagName]['code'].".*$' LIMIT 1");
                $aRow = $this->oDB->getOne("SELECT `expandcode`,`prize` FROM `expandcode` WHERE `projectid`=$iProjectId "
                . " AND `level` = '".$this->aSpecailCode[$sTagName]['level']."' LIMIT 1");
                if( count($aRow) == 0 || count($aPorject) == 0 )
                {
                    return FALSE;
                }
                return $this->oDB->ar() ? $aRow['prize'] : FALSE;
                break;
        }
        return FALSE;
    }


    public function setLoopMode( $bLoopMode=FALSE )
    {
        $this->bLoopMode = (BOOL)$bLoopMode;
    }
    /**
     * 指定循环运行的次数
     *
     * @param unknown_type $iRunTimes
     */
    public function setRunTimes( $iRunTimes = 1 )
    {
        $this->iRunTimes = $iRunTimes;
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
    
    /**
        * 计算排列组合的个数
        *
        * @author mark
        * 
        * @param integer $iBaseNumber   基数
        * @param integer $iSelectNumber 选择数
        * 
        * @return mixed
        * 
    */
    function GetCombinCount( $iBaseNumber, $iSelectNumber )
    {
        if($iSelectNumber > $iBaseNumber)
        {
            return 0;
        }
        if( $iBaseNumber == $iSelectNumber || $iSelectNumber == 0 )
        {
            return 1;//全选
        }
        if( $iSelectNumber == 1 )
        {
            return $iBaseNumber;//选一个数
        }
        $iNumerator = 1;//分子
        $iDenominator = 1;//分母
        for($i = 0; $i < $iSelectNumber; $i++)
        {
            $iNumerator *= $iBaseNumber - $i;//n*(n-1)...(n-m+1)
            $iDenominator *= $iSelectNumber - $i;//(n-m)....*2*1
        }
        return $iNumerator / $iDenominator;
    }
    
    
    /**
     * 获取指定组合的所有可能性
     * 
     * 例子：5选3
     * $aBaseArray = array('01','02','03','04','05');
     * ----getCombination($aBaseArray,3)
     * 1.初始化一个字符串：11100;--------1的个数表示需要选出的组合
     * 2.将1依次向后移动造成不同的01字符串，构成不同的组合，1全部移动到最后面，移动完成：00111.
     * 3.移动方法：每次遇到第一个10字符串时，将其变成01,在此子字符串前面的字符串进行倒序排列,后面的不变：形成一个不同的组合.
     *            如：11100->11010->10110->01110->11001->10101->01101->10011->01011->00111
     *            一共形成十个不同的组合:每一个01字符串对应一个组合---如11100对应组合01 02 03;01101对应组合02 03 05
     * 
     * 
     * @param  array $aBaseArray 基数数组
     * @param  int   $iSelectNum 选数
     * @author mark
     *
     */
    private function getCombination( $aBaseArray, $iSelectNum )
    {
        $iBaseNum = count($aBaseArray);
        if($iSelectNum > $iBaseNum)
        {
            return array();
        }
        if( $iSelectNum == 1 )
        {
            return $aBaseArray;
        }
        if( $iBaseNum == $iSelectNum )
        {
            return array(implode(' ',$aBaseArray));
        }
        $sString = '';
        $sLastString = '';
        $sTempStr = '';
        $aResult = array();
        for ($i=0; $i<$iSelectNum; $i++)
        {
            $sString .='1';
            $sLastString .='1'; 
        }
        for ($j=0; $j<$iBaseNum-$iSelectNum; $j++)
        {
            $sString .='0';
        }
        for ($k=0; $k<$iSelectNum; $k++)
        {
            $sTempStr .= $aBaseArray[$k].' ';
        }
        $aResult[] = $sTempStr;
        $sTempStr = '';
        while (substr($sString, -$iSelectNum) != $sLastString)
        {
            $aString = explode('10',$sString,2);
            $aString[0] = $this->strOrder($aString[0], TRUE);
            $sString = $aString[0].'01'.$aString[1];
            for ($k=0; $k<$iBaseNum; $k++)
            {
                if( $sString{$k} == '1' )
                {
                    $sTempStr .= $aBaseArray[$k].' ';
                }
            }
            $aResult[] = substr($sTempStr, 0, -1);
            $sTempStr = '';
        }
        return $aResult;
    }
    
    
    /**
     * 字符串排序
     * @param string $sString 需要排序的字符串
     * @return string
     * @author mark
     */
    private function strOrder( $sString = '', $bDesc = FALSE )
    {
        if( $sString == '')
        {
            return $sString;
        }
        $aString = str_split($sString);
        if($bDesc)
        {
            rsort($aString);
        }
        else
        {
            sort($aString);
        }
        return implode('',$aString);
    }
    
    /**
     * 开奖号码特殊处理
     *
     * @param string  $sCode 开奖号码
     * @param int $iLotteryId 彩种ID
     * 
     * @return  array
     * 
     */
    private function GetSepcailCode( $sCode, $iLotteryId = 1 )
    {
        $aFinalBonusCode = array();
        if( $iLotteryId == 9 )
        {
            $aCode = explode(' ', $sCode);
            if(count($aCode) != 20)
            {
                return '';
            }
            $iAddCount = 0;
            $iBigCount = 0;//大号个数
            $iSmallCount = 0;//小号个数
            $iEevnCount = 0;//偶数号个数
            $iOddCount = 0;//奇数号个数
            foreach ($aCode as $iCode)
            {
                $iCode = intval($iCode);
                $iAddCount += $iCode;
                $iCode%2 == 0 ? $iEevnCount++ : $iOddCount++;
                $iCode > 40 ? $iBigCount++ : $iSmallCount++;
            }
            if($iAddCount % 2 == 0)
            {
                $aFinalBonusCode['bjkl_heds']['code'] =1;
                $aFinalBonusCode['bjkl_heds']['level'] =2;
            }
            else 
            {
                $aFinalBonusCode['bjkl_heds']['code'] =0;
                $aFinalBonusCode['bjkl_heds']['level'] =1;
            }
            $aFinalBonusCode['bjkl_hedx']['code'] = 0;
            $aFinalBonusCode['bjkl_hedx']['level'] = 2;
            if($iAddCount < 810)
            {
                $aFinalBonusCode['bjkl_hedx']['code'] = 1;
                $aFinalBonusCode['bjkl_hedx']['level'] = 3;
            }
            if($iAddCount == 810)
            {
                $aFinalBonusCode['bjkl_hedx']['code'] = 2;
                $aFinalBonusCode['bjkl_hedx']['level'] = 1;
            }
            $aFinalBonusCode['bjkl_sxpan']['code'] = 0;
            $aFinalBonusCode['bjkl_sxpan']['level'] = 2;
            if($iBigCount > $iSmallCount)
            {
                $aFinalBonusCode['bjkl_sxpan']['code'] = 1;//下盘
                $aFinalBonusCode['bjkl_sxpan']['level'] = 3;
            }
            elseif($iBigCount == $iSmallCount)
            {
                $aFinalBonusCode['bjkl_sxpan']['code'] = 2;//和盘
                $aFinalBonusCode['bjkl_sxpan']['level'] = 1;
            }
            $aFinalBonusCode['bjkl_jopan']['code'] = 0;
            $aFinalBonusCode['bjkl_jopan']['level'] = 2;
            if($iEevnCount > $iOddCount)
            {
                $aFinalBonusCode['bjkl_jopan']['code'] = 1;//偶盘
                $aFinalBonusCode['bjkl_jopan']['level'] = 3;
            }
            elseif($iEevnCount == $iOddCount)
            {
                $aFinalBonusCode['bjkl_jopan']['code'] = 2;//和盘
                $aFinalBonusCode['bjkl_jopan']['level'] = 1;
            }
        }
        return $aFinalBonusCode;
    }
}
?>