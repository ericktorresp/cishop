<?php
/**
 * 文件 : /_app/model/banksnapshot.php
 * 功能 : 模型 - 银行快照
 * 
 * @author    Tom    090915
 * @version   1.2.0
 * @package   passportadmin
 */

class model_banksnapshot extends basemodel 
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
	    $aData    = $this->oDB->getOne("SELECT COUNT(DISTINCT(`userid`)) AS TOMCOUNT FROM `banksnapshot` WHERE `days`='$sNowDate' ");
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
	    $aData = $this->oDB->getOne("SELECT `snapshottime` FROM `banksnapshot` WHERE `days`='$sYesterday' LIMIT 1 ");
	    $iOrderStartTime = empty($aData['snapshottime']) ? date('Y-m-d H:i:s',$iYesterday) : ( date( 'Y-m-d H:i:s', strtotime($aData['snapshottime'])+1 ) );


	    // 6, 获取前日快照 '团队现金余额' 推送入数组 => $aTopProxyYesterday
	    $aYesterdayData = $this->oDB->getAll("SELECT `userid`,`ta` AS `yesterdayta` FROM `banksnapshot` WHERE `days`='$sYesterday' " );
        $aTopProxyYesterday = array(); 
	    foreach( $aYesterdayData AS $v )
	    {
	        $aTopProxyYesterday[ $v['userid'] ] = $v['yesterdayta'];
	    }
	    unset( $sYesterday, $iYesterday, $aData, $aYesterdayData );


	    /**
	     * 7, 获取总代团队余额 (当天拍照时的实际值)
	     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	     * $aTopProxyArr = Array ...
	     *     [0] => Array
         *     (
         *         [userid] => 1
         *         [username] => zdkent
         *         [TeamCashBalance] => 50000.0000
         *         [TeamCredit] => 130000
         *         [TeamChannelBalance] => 180000.000
         *         [TeamHoldBalance] => 50000.0000
         *         [TeamAvailBalance] => 130000.0000
         *     )
	     * 
	     *    TeamCashBalance    = 团队现金余额 ( A )
	     *    TeamCredit         = 团队信用欠款 ( B )
	     *    TeamChannelBalance = 团队现金余额 ( C )
	     *    TeamHoldBalance    = 团队现金余额 ( D )
	     *    TeamAvailBalance   = 团队现金余额 ( E )
	     */
	    $aTopProxyArr = $this->oDB->getAll("SELECT u.`userid`,u.`username`, "
	                    . " sum( uf.cashbalance ) as TeamCashBalance, " 
	                    . " t.`proxyvalue` AS TeamCredit, " 
                    	. " sum( uf.channelbalance ) as TeamChannelBalance, "
                    	. " sum( uf.holdbalance ) as TeamHoldBalance, "
                    	. " sum( uf.availablebalance ) as TeamAvailBalance "
                        . " FROM `usertree` ut force index( idx_usertree ) LEFT JOIN `userfund_history_".$sNowDate."` uf ON ( uf.`userid` = ut.`userid` AND ut.`isdeleted`=0 AND ut.`usertype` < 2 and uf.`channelid`=0 ) " 
	                    . " LEFT JOIN `usertree` u on ut.`lvtopid` = u.`userid` LEFT JOIN `topproxyset` t ON ( u.`userid` = t.`userid` AND t.`proxykey` = 'credit' ) " 
                        . " WHERE u.`isdeleted`=0 AND ut.`lvtopid` != 0 AND ( FIND_IN_SET( ut.`parentid`, ut.`parenttree` ) OR ut.`parentid` = 0 ) GROUP BY ut.`lvtopid`" 
        );
        $iTopProxyCounts = count($aTopProxyArr); // 总代数量, 用于 FOR 循环
        //echo '[Debug] order s='.$iOrderStartTime.'  e='.$iOrderEndTime."\n";
        //$iOrderStartTime = '2008-01-01 12:12:12';  // Just for debug, COMMENT ME!
	    //$iOrderEndTime   = date('Y-m-d H:i:s');    // Just for debug, COMMENT ME!


	    // 5, 获取冲提 (资金流入流出), 推送至数组 $aTopProxyArr
        $oOrder        = new model_orders();
	    $aSelfCashIn   = $oOrder->getTopProxyCash( $iOrderStartTime, $iOrderEndTime, 'allin' );
	    $aSelfCashOut  = $oOrder->getTopProxyCash( $iOrderStartTime, $iOrderEndTime, 'allout' );
	    $aChildCashIn  = $oOrder->getTopProxyChildCash( $iOrderStartTime, $iOrderEndTime, 'in' );
	    $aChildCashOut = $oOrder->getTopProxyChildCash( $iOrderStartTime, $iOrderEndTime, 'out' );
	    // 获取在线充提和手续费 5/5/2010
	    $aPayIn = $oOrder->getTopProxyCashPayment( $iOrderStartTime, $iOrderEndTime, 'in' );
	    $aPayOut = $oOrder->getTopProxyCashPayment( $iOrderStartTime, $iOrderEndTime, 'out' );
	    $aFeeIn = $oOrder->getTopProxyCashPayment( $iOrderStartTime, $iOrderEndTime, 'feein' );
	    $aHandFeeIn = $oOrder->getTopProxyCashPayment( $iOrderStartTime, $iOrderEndTime, 'emailandhandfeein' );
	    $aFeeOut = $oOrder->getTopProxyCashPayment( $iOrderStartTime, $iOrderEndTime, 'feeout' );
	    
	    // 只获取转账状态为 '成功' 的账变SUM
	    $aTranferIn    = $oOrder->getTopProxyTransitionResult($iOrderStartTime, $iOrderEndTime, 'in',  ' AND `transferstatus`=2 ' );
        $aTranferOut   = $oOrder->getTopProxyTransitionResult($iOrderStartTime, $iOrderEndTime, 'out', ' AND `transferstatus`=2 ' );
        unset($oOrder);

	    $aTopProxyCashIn     = array();  // 临时数组, 总代团队的资金流入 (自己+下级)
	    $aTopProxyCashOut    = array();  // 临时数组, 总代团队的资金流出 (自己+下级)
	    $aTopProxyTranferIn  = array();  // 临时数组, 总代团队的转入银行 (自己+下级) 仅转账成功
        $aTopProxyTranferOut = array();  // 临时数组, 总代团队的银行转出 (自己+下级) 仅转账成功
		
		
        
	    // 1, 累加充值(总代自身) to  $aTopProxyCashIn
        foreach( $aSelfCashIn AS $aCashIn )
        {
            if( isset($aTopProxyCashIn[ $aCashIn['fromuserid'] ]) )
            {
                $aTopProxyCashIn[ $aCashIn['fromuserid'] ] += $aCashIn['TOMSUM'];
            }
            else 
            {
                $aTopProxyCashIn[ $aCashIn['fromuserid'] ] = $aCashIn['TOMSUM'];
            }
        }
        // 2, 累加充值(所有下级) to  $aTopProxyCashIn
	    foreach( $aChildCashIn AS $aCashIn )
        {
            if( isset($aTopProxyCashIn[ $aCashIn['lvtopid'] ]) )
            {
                $aTopProxyCashIn[ $aCashIn['lvtopid'] ] += $aCashIn['TOMSUM'];
            }
            else
            {
                $aTopProxyCashIn[ $aCashIn['lvtopid'] ] = $aCashIn['TOMSUM'];
            }
        }
        // 累加提现(总代自身) to  $aTopProxyCashOut
	    foreach( $aSelfCashOut AS $aCashOut )
        {
            if( isset($aTopProxyCashOut[ $aCashOut['fromuserid'] ]) )
            {
                $aTopProxyCashOut[ $aCashOut['fromuserid'] ] += $aCashOut['TOMSUM'];
            }
            else
            {
                $aTopProxyCashOut[ $aCashOut['fromuserid'] ] = $aCashOut['TOMSUM'];
            }
        }
        // 累加提现(所有下级) to  $aTopProxyCashOut
	    foreach( $aChildCashOut AS $aCashOut )
        {
            if( isset($aTopProxyCashOut[ $aCashOut['lvtopid'] ]) )
            {
                $aTopProxyCashOut[ $aCashOut['lvtopid'] ] += $aCashOut['TOMSUM'];
            }
            else 
            {
                $aTopProxyCashOut[ $aCashOut['lvtopid'] ] = $aCashOut['TOMSUM'];
            }
        }
        // 累加转入银行(所有下级) to  $aTopProxyTranferIn
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
        // 累加银行转出(所有下级) to  $aTopProxyTranferOut
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
		
		// 计算充值到总额 5/5/2010
		foreach( $aPayIn AS $aPIn )
        {
            if( isset($aTopProxyCashIn[ $aPIn['lvtopid'] ]) )
            {
                $aTopProxyCashIn[ $aPIn['lvtopid'] ] += $aPIn['TOMSUM'];
            }else{
                $aTopProxyCashIn[ $aPIn['lvtopid'] ] = $aPIn['TOMSUM'];
            }
        }
		// 计算提现到总额 5/5/2010
		foreach( $aPayOut AS $aPOut )
        {
            if( isset($aTopProxyCashOut[ $aPOut['lvtopid'] ]) )
            {
                $aTopProxyCashOut[ $aPOut['lvtopid'] ] += $aPOut['TOMSUM'];
            }else{
                $aTopProxyCashOut[ $aPOut['lvtopid'] ] = $aPOut['TOMSUM'];
            }
        }
        
		// 计算手续费到充值总额 5/5/2010
		foreach( $aFeeIn AS $aFIn )
        {
            if( isset($aTopProxyCashIn[ $aFIn['lvtopid'] ]) )
            {
                $aTopProxyCashIn[ $aFIn['lvtopid'] ] -= $aFIn['TOMSUM'];
            }else{
                $aTopProxyCashIn[ $aFIn['lvtopid'] ] = 0-$aFIn['TOMSUM'];
            }
        }
        
        // 计算email充值和人工充值手续费到充值总额 5/5/2010
		foreach( $aHandFeeIn AS $aHFIn )
        {
            if( isset($aTopProxyCashIn[ $aHFIn['lvtopid'] ]) )
            {
                $aTopProxyCashIn[ $aHFIn['lvtopid'] ] += $aHFIn['TOMSUM'];
            }else{
                $aTopProxyCashIn[ $aHFIn['lvtopid'] ] += $aHFIn['TOMSUM'];
            }
        }
        
		// 计算手续费到提现总额 5/5/2010
		foreach( $aFeeOut AS $aFOut )
        {
            if( isset($aTopProxyCashOut[ $aFOut['lvtopid'] ]) )
            {
                $aTopProxyCashOut[ $aFOut['lvtopid'] ] += $aFOut['TOMSUM'];
            }else{
                $aTopProxyCashOut[ $aFOut['lvtopid'] ] = $aFOut['TOMSUM'];
            }
        }

        // 推送至数组 $aTopProxyArr
        for( $i=0; $i<$iTopProxyCounts; $i++ )
        {
            // 1, 充值
            if( !isset( $aTopProxyArr[$i]['cashin'] ) )
            {
                $aTopProxyArr[$i]['cashin'] = 0;
            }
            if( isset($aTopProxyCashIn[ $aTopProxyArr[$i]['userid'] ]) )
            {
                $aTopProxyArr[$i]['cashin'] += $aTopProxyCashIn[ $aTopProxyArr[$i]['userid'] ];
            }
            // 2, 提现
            if( !isset( $aTopProxyArr[$i]['cashout'] ) )
            {
                $aTopProxyArr[$i]['cashout'] = 0;
            }
            if( isset($aTopProxyCashOut[ $aTopProxyArr[$i]['userid'] ]) )
            {
                $aTopProxyArr[$i]['cashout'] += $aTopProxyCashOut[ $aTopProxyArr[$i]['userid'] ];
            }
            // 3, 转入银行
            if( !isset( $aTopProxyArr[$i]['tranferin'] ) )
            {
                $aTopProxyArr[$i]['tranferin'] = 0;
            }
            if( isset($aTopProxyTranferIn[ $aTopProxyArr[$i]['userid'] ]) )
            {
                $aTopProxyArr[$i]['tranferin'] += $aTopProxyTranferIn[ $aTopProxyArr[$i]['userid'] ];
            }
            // 4, 银行转出
            if( !isset( $aTopProxyArr[$i]['tranferout'] ) )
            {
                $aTopProxyArr[$i]['tranferout'] = 0;
            }
            if( isset($aTopProxyTranferOut[ $aTopProxyArr[$i]['userid'] ]) )
            {
                $aTopProxyArr[$i]['tranferout'] += $aTopProxyTranferOut[ $aTopProxyArr[$i]['userid'] ];
            }

            // 5, 差帐
            // 公式:   1, 前日快照中 总代团队(现金余额A) + 冲提结余 + 转账结余 = 应有金额
            //         2, 今天休市后金额 TeamCashBalance - 应有金额 = 差帐
            if( !isset( $aTopProxyArr[$i]['diff'] ) )
            {
                $aTopProxyArr[$i]['diff'] = 0;
            }

            if( isset($aTopProxyYesterday[ $aTopProxyArr[$i]['userid'] ]) )
            {
            	// 实有 - 应有(前日资金-总结算+转账结余)
                $aTopProxyArr[$i]['diff'] = 
                                        $aTopProxyArr[$i]['TeamCashBalance'] - (
                                        $aTopProxyYesterday[ $aTopProxyArr[$i]['userid'] ]
                                        + ( $aTopProxyArr[$i]['cashin'] - $aTopProxyArr[$i]['cashout'] ) 
                                        + ( $aTopProxyArr[$i]['tranferin'] - $aTopProxyArr[$i]['tranferout']) );
            }
            else
            {
                $aTopProxyArr[$i]['diff'] = 0; // 昨天数据不存在
            }

            // 6, 当日(快照时) 总代团队现金
            $aTopProxyArr[$i]['todaycash'] = $aTopProxyArr[$i]['TeamCashBalance'];
            
            // 7.记录总代类型，是否是测试账号或是冻结账号
            $sSql = " SELECT `istester`,`isfrozen` FROM `usertree` WHERE `userid` = '" . $aTopProxyArr[$i]['userid'] ."'";
            $aUserType = $this->oDB->getOne( $sSql );
            $aTopProxyArr[$i]['islockuser'] = $aUserType['isfrozen'] != 0 ? 1 : 0;
            $aTopProxyArr[$i]['istestuser'] = $aUserType['istester'];

        }
        unset(  $aTopProxyCashOut, $aTopProxyCashIn, $aSelfCashIn, $aSelfCashOut, 
                $aChildCashIn, $aChildCashOut,$aTopProxyTranferIn,$aTopProxyTranferOut,
                $aTranferIn,$aTranferOut,$aTopProxyYesterday);


	    // 7, 整理所有数据, 写入快照表 banksnapshot.  snapshottime=$iOrderEndTime
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
	    $sDays = date( 'Ymd', strtotime("-1 days") );
	    $sSql =  'INSERT INTO `banksnapshot`( `days`, `userid`, `username`,`islockuser`,`istestuser`, '
	    		. ' `ta`, `tb`, `tc`, `td`, `te`, '
	    		. ' `cashin`, `cashout`, `tranferin`, `tranferout`, `snapshottime`, `cashdiff`,`todaycash`,`lastupdatetime` ) VALUES ';

	    $iCounts = count($aData);
	    for( $i=0; $i<$iCounts; $i++ )
	    {
	        $sSeg = ($i+1)!=$iCounts ? ',' : ';';
	        $sSql .= " ( '$sDays', '".$aData[$i]['userid']."', '".$aData[$i]['username']."', '"
	                .$aData[$i]['islockuser'] . "','" . $aData[$i]['istestuser']."','"
	                .$aData[$i]['TeamCashBalance']."', '".$aData[$i]['TeamCredit']."', '"
	                .$aData[$i]['TeamChannelBalance']
	                ."', '".$aData[$i]['TeamHoldBalance']."', '".$aData[$i]['TeamAvailBalance']."', '"
	                .$aData[$i]['cashin']."', '".$aData[$i]['cashout']."', '"
	                .$aData[$i]['tranferin']."', '".$aData[$i]['tranferout']
	                ."', '$sSnapshotTime', '".$aData[$i]['diff']."', '".$aData[$i]['todaycash']."', '".date('Y-m-d H:i:s')."' ) " . $sSeg ."\n";
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
        $aResToday = $this->oDB->getAll( 
        	"SELECT `userid`,`username`,`cashin`,`cashout`,`tranferin`,`tranferout`, ".
        	"`cashdiff`,`todaycash`, `snapshottime` FROM `banksnapshot` WHERE 1 $sWhereToday ");

        // 2, 获取前日5个资金数据
        $sWhereYesterday = " AND `days`='". date( 'Y-m-d', strtotime($sDate)-86400 ) . "' $sWhere ";
        $aResYesterday = $this->oDB->getAll( 
                "SELECT `userid`,`ta`,`tb`,`tc`,`td`,`te` FROM `banksnapshot` WHERE 1 $sWhereYesterday ");
        $aResYesterdayNew = array();

        // 3, 整理 $aResYesterday 结果集
        foreach( $aResYesterday AS $v )
        {
            $aResYesterdayNew[ $v['userid'] ] = array(
                    'ta'  =>  $v['ta'],
                    'tb'  =>  $v['tb'],
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
                        'cashin'      => $aResToday[$i]['cashin'],
                        'cashout'     => $aResToday[$i]['cashout'],
                        'tranferin'   => $aResToday[$i]['tranferin'],
                        'tranferout'  => $aResToday[$i]['tranferout'],
                        'cashdiff'    => $aResToday[$i]['cashdiff'],
                        'todaycash'   => $aResToday[$i]['todaycash'],
            			'times'       => $aResToday[$i]['snapshottime'],
                        'ta'  =>  isset($aResYesterdayNew[ $aResToday[$i]['userid'] ]['ta']) ? $aResYesterdayNew[ $aResToday[$i]['userid'] ]['ta'] : 0 ,
                        'tb'  =>  isset($aResYesterdayNew[ $aResToday[$i]['userid'] ]['tb']) ? $aResYesterdayNew[ $aResToday[$i]['userid'] ]['tb'] : 0 ,
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
	    $aTmpArray = $this->oDB->getAll("SELECT DISTINCT `days` FROM `banksnapshot` ORDER BY `days` DESC ");
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
        $aRes = $this->oDB->getAll( "SELECT `userid`, `cashdiff` FROM `banksnapshot` WHERE `days`='". daddslashes($sDate) . "' ");
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
	 * 保存与删除 统计报表历史数据
	 * @param integer $iDay
     * @param string  $sPath
     * @return bool
     * 
     * 涉及表 banksnapshot
     * 
     * 6/22/2010
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
		
    	$numCodes = $this->oDB->getOne("SELECT COUNT(entry) AS `numCodes` FROM `banksnapshot` "
		                        ." WHERE `lastupdatetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_banksnapshot.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `banksnapshot` "
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
				$sql = "INSERT INTO `banksnapshot` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `banksnapshot` WHERE `lastupdatetime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
	}
}
?>