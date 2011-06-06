<?php
/**
 * 文件 : /_app/model/snapshot.php
 * 功能 : 模型 - 低频快照
 * 
 * @author    Tom    090915
 * @version   1.2.0
 * @package   passportadmin
 */

class model_snapshot extends basemodel 
{
    // 构造函数
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}


	/**
	 * 执行银行快照主流程
	 * @author Tom 090819 14:30
	 * @return 执行成功, 则返回全等于的 TRUE
	 *          执行失败, 则返回负数 AS ErrCode
	 */
	public function doSnapshot()
	{
        // 1, 检查当天快照数据是否已生成, 并且符合基本规范 ----------------------------------------
	    $sNowDate = date( 'Ymd', strtotime("-1 days") );
	    $aData    = $this->oDB->getOne("SELECT COUNT(DISTINCT(`userid`)) AS TOMCOUNT FROM `snapshot` WHERE `days`='$sNowDate' ");
	    if( isset($aData['TOMCOUNT']) && $aData['TOMCOUNT'] > 0  )
	    {
	        return '-1001 (already Done!)'; // 快照数据已生成完毕, 无需继续执行
	    }
	    unset($aData);
	    // TODO: 是否需要继续判断生成快照的行记录数? (算上冻结用户,测试账户,可能每个总代4条记录

	    // 2, 获取用户资金表的总数, 与之后插入临时表中的记录数相比
	    $aRes = $this->oDB->getOne("SELECT COUNT(`entry`) AS TOMCOUNT FROM `userfund` ");
	    $iUserFundCount = $aRes['TOMCOUNT'];
	    unset($aRes);


        // 3, 建立临时表, 将当前一刻的用户资金快照进表
        $this->oDB->query("DROP TABLE IF EXISTS `userfund_history_".$sNowDate."` ");
        $this->oDB->query("CREATE TABLE `userfund_history_".$sNowDate."` LIKE `userfund` ");
        // 3.1 表锁定 历史表为'写锁'. 用户资金表为'写锁'(实际不写入, 只为获取更高优先级的锁)
        $this->oDB->query("LOCK TABLE `userfund_history_".$sNowDate."` WRITE, `userfund` WRITE;");
        // 3.2 开始事务, 任何失败将解锁表, 并返回错误CODE, 中断程序执行.
        if( FALSE === $this->oDB->doTransaction() )
        { 
            $this->oDB->query("UNLOCK TABLES"); // 显式解锁
            return -3012; 
        }
	    $this->oDB->query("INSERT INTO `userfund_history_".$sNowDate."` ( SELECT * FROM `userfund`) ");
	    if( $this->oDB->ar() != $iUserFundCount )
	    { // 如果临时快照表的用户资金数量, 与原始表不同. 则中断程序执行
	        $this->oDB->query("DROP TABLE IF EXISTS `userfund_history_".$sNowDate."` ");
	        $this->oDB->query("UNLOCK TABLES"); // 显式解锁
	        return -1002; // 快照数据不完整, 等待下次重新启动
	    }
        if( FALSE === $this->oDB->doCommit() )
        { 
            $this->oDB->query("UNLOCK TABLES"); // 显式解锁
            return -3013; 
        }
	    // 明确提交事务后, 解锁表
        $this->oDB->query("UNLOCK TABLES");
	    unset($iUserFundCount);

	    
	    // 4, 数据初始化 ( $iOrderEndTime 账变查询结束时间, 同样也是快照发生时间, 结束时间-1秒,防止重复计算 )
	    $iOrderEndTime   = date('Y-m-d H:i:s', time()); 
	    $iOrderStartTime = 0;  // 账变查询开始时间


	    // 5,  获取上一次快照产生的时间, 作为: 账变查询的起始时间, 第一次运行默认为 24小时前.
	    $iYesterday = time()-172800;  // 172800 = 86400*2 = 24*60*60*2 = 2天
	    $sYesterday = date( "Y-m-d", $iYesterday );
	    $aData = $this->oDB->getOne("SELECT `snapshottime` FROM `snapshot` WHERE `days`='$sYesterday' LIMIT 1 ");
	    $iOrderStartTime = empty($aData['snapshottime']) ? date('Y-m-d H:i:s',$iYesterday) : ( date( 'Y-m-d H:i:s', strtotime($aData['snapshottime'])+1 ) );


	    // 6, 获取前日快照 '团队现金余额' 推送入数组 => $aTopProxyYesterday
	    $aYesterdayData = $this->oDB->getAll("SELECT `userid`,`tc` AS `yesterdaytc` FROM `snapshot` WHERE `days`='$sYesterday' " );
        $aTopProxyYesterday = array(); 
	    foreach( $aYesterdayData AS $v )
	    {
	        $aTopProxyYesterday[ $v['userid'] ] = $v['yesterdaytc'];
	    }
	    unset( $sYesterday, $iYesterday, $aData, $aYesterdayData );


	    /**
	     * 7, 获取总代团队余额
	     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	     * $aTopProxyArr = Array ...
	     *     [0] => Array
         *     (
         *         [userid] => 1
         *         [username] => zdkent
         *         [TeamChannelBalance] => 180000.000
         *         [TeamHoldBalance] => 50000.0000
         *         [TeamAvailBalance] => 130000.0000
         *     )
	     * 
	     *    TeamChannelBalance = 团队现金余额 ( C )
	     *    TeamHoldBalance    = 团队现金余额 ( D )
	     *    TeamAvailBalance   = 团队现金余额 ( E )
	     */
	    $aTopProxyArr = $this->oDB->getAll("SELECT u.`userid`,u.`username`, "
                    	. " sum( uf.channelbalance ) as TeamChannelBalance, "
                    	. " sum( uf.holdbalance ) as TeamHoldBalance, "
                    	. " sum( uf.availablebalance ) as TeamAvailBalance "
                        . " FROM `usertree` ut force index( idx_usertree ) LEFT JOIN `userfund_history_".$sNowDate."` uf ON ( uf.`userid` = ut.`userid` AND ut.`isdeleted`=0 AND ut.`usertype` < 2 and uf.`channelid`=1 ) " 
	                    . " LEFT JOIN `usertree` u on ut.`lvtopid` = u.`userid` " 
                        . " WHERE u.`isdeleted`=0 AND ut.`lvtopid` != 0 AND ( FIND_IN_SET( ut.`parentid`, ut.`parenttree` ) OR ut.`parentid` = 0 ) GROUP BY ut.`lvtopid`" 
        );
        $iTopProxyCounts = count($aTopProxyArr); // 总代数量, 用于 FOR 循环
        //echo '[Debug] order s='.$iOrderStartTime.'  e='.$iOrderEndTime."\n";
        //$iOrderStartTime = '2008-01-01 12:12:12';  // Just for debug, COMMENT ME!
	    //$iOrderEndTime   = date('Y-m-d H:i:s');    // Just for debug, COMMENT ME!


	    // 5, 获取冲提 A='总代购费', B='返点总额', C='中奖总额'  推送至数组 $aTopProxyArr
        $oOrder           = new model_orders();
        $oProjects        = new model_projects();
	    $aTeam_a   = $oProjects->getTopProxyData( $iOrderStartTime, $iOrderEndTime, 'buy' ); // 总代购费
	    $aTeam_b   = $oProjects->getTopProxyData( $iOrderStartTime, $iOrderEndTime, 'point' ); // 返点总额
	    $aTeam_c   = $oProjects->getTopProxyData( $iOrderStartTime, $iOrderEndTime, 'bingo' ); // 中奖总额
	    // 获取撤单手续费
	    $aCancel   = $oOrder->getTopProxyData($iOrderStartTime, $iOrderEndTime, 'cancel' ); // 撤单手续费

	    // $aTeam_a - $aTeam_b - $aTeam_c   = 总结算  (总结算正数为公司盈利, 负数为公司输.)
	    // 只获取转账状态为 '成功' 的账变SUM
	    $aTranferIn    = $oOrder->getTopProxyData($iOrderStartTime, $iOrderEndTime, 'tranferin',  ' AND `transferstatus`=2 ' );
        $aTranferOut   = $oOrder->getTopProxyData($iOrderStartTime, $iOrderEndTime, 'tranferout', ' AND `transferstatus`=2 ' );
        unset($oOrder,$oProjects);
	    $aTopProxyTeam_a     = array();  // 临时数组, 总代团队的 '总代购费' (自己+下级)
	    $aTopProxyTeam_b     = array();  // 临时数组, 总代团队的 '返点总额' (自己+下级)
	    $aTopProxyTeam_c     = array();  // 临时数组, 总代团队的 '中奖总额' (自己+下级)
	    $aTopProxyTranferIn  = array();  // 临时数组, 总代团队的 '转入低频' (自己+下级) 仅转账成功
        $aTopProxyTranferOut = array();  // 临时数组, 总代团队的 '低频转出' (自己+下级) 仅转账成功
        $aTopProxyCancel     = array();  // 临时数组, 总代团队的 '低频转出' (自己+下级) 仅转账成功

	    // 1, 总代购费  $aTopProxyTeam_a
        foreach( $aTeam_a AS $v )
        {
            if( isset($aTopProxyTeam_a[ $v['lvtopid'] ]) )
            {
                $aTopProxyTeam_a[ $v['lvtopid'] ] += $v['TOMSUM'];
            }
            else 
            {
                $aTopProxyTeam_a[ $v['lvtopid'] ] = $v['TOMSUM'];
            }
        }
        // 2, 返点总额  $aTopProxyTeam_b
        foreach( $aTeam_b AS $v )
        {
            if( isset($aTopProxyTeam_b[ $v['lvtopid'] ]) )
            {
                $aTopProxyTeam_b[ $v['lvtopid'] ] += $v['TOMSUM'];
            }
            else 
            {
                $aTopProxyTeam_b[ $v['lvtopid'] ] = $v['TOMSUM'];
            }
        }
    	// 3, 中奖总额  $aTopProxyTeam_c
        foreach( $aTeam_c AS $v )
        {
            if( isset($aTopProxyTeam_c[ $v['lvtopid'] ]) )
            {
                $aTopProxyTeam_c[ $v['lvtopid'] ] += $v['TOMSUM'];
            }
            else 
            {
                $aTopProxyTeam_c[ $v['lvtopid'] ] = $v['TOMSUM'];
            }
        }
        // ---------------------------------------------------------------------------
        // 累加转入低频(所有下级) to  $aTopProxyTranferIn
        foreach( $aTranferIn AS $aIn )
        {
            if( isset($aTopProxyTranferIn[ $aIn['lvtopid'] ]) )
            {
                $aTopProxyTranferIn[ $aIn['lvtopid'] ] += $aIn['TOMSUM'];
            }
            else 
            {
                $aTopProxyTranferIn[ $aIn['lvtopid'] ] = $aIn['TOMSUM'];
            }
        }
        // 累加低频转出(所有下级) to  $aTopProxyTranferOut
	    foreach( $aTranferOut AS $aOut )
        {
            if( isset($aTopProxyTranferOut[ $aOut['lvtopid'] ]) )
            {
                $aTopProxyTranferOut[ $aOut['lvtopid'] ] += $aOut['TOMSUM'];
            }
            else 
            {
                $aTopProxyTranferOut[ $aOut['lvtopid'] ] = $aOut['TOMSUM'];
            }
        }
        // ---------------------------------------------------------------------------
        // 累加所有下级的撤单手续费
        foreach( $aCancel AS $aOut )
        {
            if( isset($aTopProxyCancel[ $aOut['lvtopid'] ]) )
            {
                $aTopProxyCancel[ $aOut['lvtopid'] ] += $aOut['TOMSUM'];
            }
            else 
            {
                $aTopProxyCancel[ $aOut['lvtopid'] ] = $aOut['TOMSUM'];
            }
        }
        // ---------------------------------------------------------------------------

        // 推送至数组 $aTopProxyArr
        for( $i=0; $i<$iTopProxyCounts; $i++ )
        {
            // 1, 总代购费
            if( !isset( $aTopProxyArr[$i]['totalbuy'] ) )
            {
                $aTopProxyArr[$i]['totalbuy'] = 0;
            }
            if( isset($aTopProxyTeam_a[ $aTopProxyArr[$i]['userid'] ]) )
            {
            	if( isset($aTopProxyCancel[ $aTopProxyArr[$i]['userid'] ]) )
            	{ // 如果存在撤单手续费
            		$aTopProxyArr[$i]['totalbuy'] = 
            		      $aTopProxyTeam_a[ $aTopProxyArr[$i]['userid'] ] + 
            		      $aTopProxyCancel[ $aTopProxyArr[$i]['userid'] ];
            	}
            	else 
            	{
                    $aTopProxyArr[$i]['totalbuy'] = $aTopProxyTeam_a[ $aTopProxyArr[$i]['userid'] ];
            	}
            }
            // 2, 返点总额
            if( !isset( $aTopProxyArr[$i]['totalpoint'] ) )
            {
                $aTopProxyArr[$i]['totalpoint'] = 0;
            }
            if( isset($aTopProxyTeam_b[ $aTopProxyArr[$i]['userid'] ]) )
            {
                $aTopProxyArr[$i]['totalpoint'] = $aTopProxyTeam_b[ $aTopProxyArr[$i]['userid'] ];
            }
            // 3, 中奖总额
            if( !isset( $aTopProxyArr[$i]['totalbingo'] ) )
            {
                $aTopProxyArr[$i]['totalbingo'] = 0;
            }
            if( isset($aTopProxyTeam_c[ $aTopProxyArr[$i]['userid'] ]) )
            {
                $aTopProxyArr[$i]['totalbingo'] = $aTopProxyTeam_c[ $aTopProxyArr[$i]['userid'] ];
            }
            // 4, 总结算
            $aTopProxyArr[$i]['totalbalance'] = 
                $aTopProxyArr[$i]['totalbuy'] - $aTopProxyArr[$i]['totalpoint'] - $aTopProxyArr[$i]['totalbingo'];

            // 5, 转入低频
            if( !isset( $aTopProxyArr[$i]['tranferin'] ) )
            {
                $aTopProxyArr[$i]['tranferin'] = 0;
            }
            if( isset($aTopProxyTranferIn[ $aTopProxyArr[$i]['userid'] ]) )
            {
                $aTopProxyArr[$i]['tranferin'] = $aTopProxyTranferIn[ $aTopProxyArr[$i]['userid'] ];
            }
            // 6, 低频转出
            if( !isset( $aTopProxyArr[$i]['tranferout'] ) )
            {
                $aTopProxyArr[$i]['tranferout'] = 0;
            }
            if( isset($aTopProxyTranferOut[ $aTopProxyArr[$i]['userid'] ]) )
            {
                $aTopProxyArr[$i]['tranferout'] = $aTopProxyTranferOut[ $aTopProxyArr[$i]['userid'] ];
            }
            // 7, 转账结余
            $aTopProxyArr[$i]['tranferdiff'] = 
                $aTopProxyArr[$i]['tranferin'] - $aTopProxyArr[$i]['tranferout'];

            // 8, 子频道特有的整理, 由于总代资金账户未激活导致的数据为 NULL
            if( empty($aTopProxyArr[$i]['TeamChannelBalance']) )
            {
                $aTopProxyArr[$i]['TeamChannelBalance'] = 0;
            }
            if( empty($aTopProxyArr[$i]['TeamHoldBalance']) )
            {
                $aTopProxyArr[$i]['TeamHoldBalance'] = 0;
            }
            if( empty($aTopProxyArr[$i]['TeamAvailBalance']) )
            {
                $aTopProxyArr[$i]['TeamAvailBalance'] = 0;
            }

            // 8, 差帐
            // 公式:   1, 前日快照中 总代团队(现金余额A) + (总结算) + 转账结余 = 应有金额
            //         2, 今天休市后金额 TeamCashBalance - 应有金额 = 差帐
            //        PS: 总结算 = 总代购费 - 返点总额 - 中奖总额 
            if( !isset( $aTopProxyArr[$i]['diff'] ) )
            {
                $aTopProxyArr[$i]['diff'] = 0;
            }

            if( isset($aTopProxyYesterday[ $aTopProxyArr[$i]['userid'] ]) )
            {
                // 实有 - 应有(前日资金-总结算+转账结余)
                $aTopProxyArr[$i]['diff'] = 
                                        $aTopProxyArr[$i]['TeamChannelBalance'] - (
                                        $aTopProxyYesterday[ $aTopProxyArr[$i]['userid'] ]
                                        - $aTopProxyArr[$i]['totalbalance']   // 总结算
                                        + $aTopProxyArr[$i]['tranferdiff'] ); // 转账结余
            }
            else
            {
                $aTopProxyArr[$i]['diff'] = 0; // 昨天数据不存在
            }

            // 6, 当日(快照时) 总代团队现金
            $aTopProxyArr[$i]['todaycash'] = $aTopProxyArr[$i]['TeamChannelBalance'];
            // 7.记录总代类型，是否是测试账号或是冻结账号
            $sSql = " SELECT `istester`,`isfrozen` FROM `usertree` WHERE `userid` = '" . $aTopProxyArr[$i]['userid'] ."'";
            $aUserType = $this->oDB->getOne( $sSql );
            $aTopProxyArr[$i]['islockuser'] = $aUserType['isfrozen'] != 0 ? 1 : 0;
            $aTopProxyArr[$i]['istestuser'] = $aUserType['istester'];

        }

        unset(  $aTeam_a, $aTeam_b, $aTeam_c, 
                $aTopProxyTranferIn,$aTopProxyTranferOut,
                $aTranferIn,$aTranferOut,$aTopProxyYesterday);


	    // 7, 整理所有数据, 写入快照表 snapshot.  snapshottime=$iOrderEndTime
	    if( TRUE == $this->doInsertData( $aTopProxyArr, $iOrderEndTime ) )
	    {
	        $iHistoryDays = 5;  // 删除N天前的历史资金快照临时表
	        $this->oDB->query("DROP TABLE IF EXISTS `userfund_history_"
	               .date('Ymd', strtotime("-$iHistoryDays days" ) )."` ");
	        if( $this->oDB->errno() )
	        {
	            return -1005; // 删除快照临时表出错
	        }
	        return TRUE;
	    }
	    else
	    {
	        return -1004; // 快照数据插入失败
	    }
	}


	/**
	 * 快照数据插入
	 * @param array $aData
	 * @return BOOL
	 */
	private function doInsertData( &$aData=array(), $sSnapshotTime = '' )
	{
	    if( empty($aData) || $sSnapshotTime=='' )
	    {
	        return FALSE;
	    }
	    $sCurrTimex = date('Y-m-d H:i:s');
	    $sDays = date( 'Ymd', strtotime("-1 days") );
	    $sSql =  'INSERT INTO `snapshot`( `days`, `userid`, `username` ,`islockuser`,`istestuser`, '
	    		. ' `tc`, `td`, `te`, '
	    		. ' `totalbuy`, `totalpoint`,`totalbingo`,`totalbalance`, `tranferdiff`, '
	    		. ' `tranferin`, `tranferout`, `snapshottime`, `cashdiff`,`todaycash`,`lastupdatetime` ) VALUES ';

	    $iCounts = count($aData);
	    for( $i=0; $i<$iCounts; $i++ )
	    {
	        $sSeg = ($i+1)!=$iCounts ? ',' : ';';
	        $sSql .= " ( '$sDays', '".$aData[$i]['userid']."', '".$aData[$i]['username']."', '"
	                .$aData[$i]['islockuser'] . "','" . $aData[$i]['istestuser']."','"
	                .$aData[$i]['TeamChannelBalance']
	                ."', '".$aData[$i]['TeamHoldBalance']."', '".$aData[$i]['TeamAvailBalance']."', '"
	                .$aData[$i]['totalbuy']."', '".$aData[$i]['totalpoint']."', '"
	                .$aData[$i]['totalbingo']."', '".$aData[$i]['totalbalance']."', '"
	                .$aData[$i]['tranferdiff']."', '"
	                .$aData[$i]['tranferin']."', '".$aData[$i]['tranferout']
	                ."', '$sSnapshotTime', '".$aData[$i]['diff']."', '".$aData[$i]['todaycash']."', '".$sCurrTimex."' ) " . $sSeg ."\n";
	    }
	    unset($aData);
	    if( TRUE !== $this->oDB->doTransaction() ) { return -1102; }
	    $this->oDB->query($sSql);
	    if( $this->oDB->ar() == $iCounts )
	    {
	        if( TRUE === $this->oDB->doCommit() )
	        {
	            return TRUE;
	        }
	        else 
	        { // 事务提交失败
	            return -1103;
	        }
	    }
	    else 
	    {
	        $this->oDB->doRollback();
	        return -1104; // 数据插入数量不正确
	    }
	}



	/**
	 * 根据条件, 获取快照数据结果集
	 * @return array
	 */
    public function getSnapshotDatas( $sDate='', $sWhere = '' )
    {
        $aReturn = array();
        $sWhereToday = " AND `days`='". daddslashes($sDate) . "' $sWhere "; 
        // 1, 获取充提,转账,当日余额,差帐金额
        $aResToday = $this->oDB->getAll( "SELECT * FROM `snapshot` WHERE 1 $sWhereToday ");

        // 2, 获取5个资金数据
        $sWhereYesterday = " AND `days`='". date( 'Y-m-d', strtotime($sDate)-86400 ) . "' $sWhere ";
        $aResYesterday = $this->oDB->getAll( "SELECT `userid`,`tc`,`td`,`te` FROM `snapshot` WHERE 1 $sWhereYesterday ");
        $aResYesterdayNew = array();

        // 3, 整理 $aResYesterday 结果集
        foreach( $aResYesterday AS $v )
        {
            $aResYesterdayNew[ $v['userid'] ] = array(
                    'tc'  =>  $v['tc'],
                    'td'  =>  $v['td'],
                    'te'  =>  $v['te'],
                );
        }
        unset($sWhere,$sWhereToday,$sWhereYesterday);

        // 4, 数组合并返回
        for( $i=0; $i<count($aResToday); $i++ )
        {
            $aReturn[$i] = array(
                        'userid'      => $aResToday[$i]['userid'],
            			'username'    => $aResToday[$i]['username'],
                        'totalbuy'      => $aResToday[$i]['totalbuy'],
                        'totalpoint'     => $aResToday[$i]['totalpoint'],
                        'totalbingo'     => $aResToday[$i]['totalbingo'],
                        'totalbalance'     => $aResToday[$i]['totalbalance'],
                        'tranferin'   => $aResToday[$i]['tranferin'],
                        'tranferout'  => $aResToday[$i]['tranferout'],
                        'tranferdiff'  => $aResToday[$i]['tranferdiff'],
                        'cashdiff'    => $aResToday[$i]['cashdiff'],
                        'todaycash'   => $aResToday[$i]['todaycash'],
            			'times'       => $aResToday[$i]['snapshottime'],
                        'tc'  =>  isset($aResYesterdayNew[ $aResToday[$i]['userid'] ]['tc']) ? $aResYesterdayNew[ $aResToday[$i]['userid'] ]['tc'] : 0 ,
                        'td'  =>  isset($aResYesterdayNew[ $aResToday[$i]['userid'] ]['td']) ? $aResYesterdayNew[ $aResToday[$i]['userid'] ]['td'] : 0 ,
                        'te'  =>  isset($aResYesterdayNew[ $aResToday[$i]['userid'] ]['te']) ? $aResYesterdayNew[ $aResToday[$i]['userid'] ]['te'] : 0 ,
                );
        }
        $aReturnDatas['data'] = array();
        $aReturnDatas['time'] = isset($aReturn[0]['times']) ? $aReturn[0]['times'] : '----';
        $aReturnDatas['data'] = $aReturn;
        return $aReturnDatas;
    }

    
	/**
	 * 获取可以访问的快照
	 * @author Tom 090820
	 * @return mix
	 */
	public function getDistintDays( $bReturnArray = TRUE, $sSelected = '' )
	{
	    $aTmpArray = $this->oDB->getAll("SELECT DISTINCT `days` FROM `snapshot` ORDER BY `days` DESC ");
	    $aReturn = '';
	    if( $bReturnArray == TRUE )
	    {
            foreach( $aTmpArray as $k => $v )
    	    {
    	        $aReturn[$k] = $v['days'];
    	    }
            return $aReturn;
	    }
	    else
	    {
	        foreach( $aTmpArray as $k => $v )
	        {
	            $sSel = $sSelected==$v['days'] ? 'selected' : '';
	            $aReturn .= "<OPTION $sSel value=\"".$v['days']."\">".$v['days']."</OPTION>";
	        }
	        return $aReturn;
	    }
	}

	
	
	/**
	 * 检查快照状态
	 *  根据传递进的日期参数, 读取当天的差帐金额是否在合理范围内
	 *    1 = 无差帐
	 *    2 = 有差帐, 但在允许范围内
	 *    3 = 有差帐, 在范围外, 报警
	 * @param string $sDate
	 * @return int
	 */
    public function checkSnapshotDatas( $sDate='' )
    {
        /**
         * 1, 获取相关参数
         *    - task_bankss_money1    [快照] 自动对账报警最小差额 (单笔金额)
         *    - task_bankss_money2    [快照] 自动对账报警最小差额 (总计金额)
         */
        $oConfig = new model_config();
        $aConfig = $oConfig->getConfigs( array('task_bankss_money1', 'task_bankss_money2' ) );
        //print_rr($aConfig);exit;

        $dSum  = 0.00;
        $iFlag  = 1;
        // 2, 获取充提,转账,当日余额,差帐金额
        $aRes = $this->oDB->getAll( "SELECT `userid`, `cashdiff` FROM `snapshot` WHERE `days`='". daddslashes($sDate) . "' ");
        foreach( $aRes AS $v )
        {
            if( abs($v['cashdiff']) > 0 )
            { // 存在差帐, FLAG = 2, 但不返回
                $iFlag = 2;
            } 
            if( abs($v['cashdiff']) >= abs($aConfig['task_bankss_money1']) )
            { // 单笔超过或等于限额, 直接返回
                $iFlag = 3;
                return $iFlag;
            }
            // 累加总计差帐
            $dSum += abs($v['cashdiff']);
        }

        if( $dSum == 0 )
        { // 无差帐
            return 1;
        }

        if( abs($dSum) >= abs($aConfig['task_bankss_money2'])  )
        { // 有差帐, 但在范围内
            $iFlag = 3;
        }
        else 
        {
            $iFlag = 2;
        }
        return $iFlag;
    }
    
    
	/**
	 * 数据快照表 清理
	 * @param integer $iDay
     * @param string  $sPath
     * @return bool
     * 
     * 涉及表 snapshot
     * 
     * 6/21/2010
	 */
	public function backandclear($iDay,$sPath)
	{
		if( !is_numeric($iDay) ) 
		{
			return FALSE;
		}
		$iDay = intval($iDay);
		
		if( $iDay < 5 )
		{
			$iDay = 5;
		}
		$sDay = date("Ymd");
		
    	$numCodes = $this->oDB->getOne("SELECT COUNT(entry) AS `numCodes` FROM `snapshot` "
		                        ." WHERE `lastupdatetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_snapshot.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `snapshot` "
		                                ." WHERE `lastupdatetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
		                                ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `snapshot` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `snapshot` WHERE `lastupdatetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
	}

}
?>