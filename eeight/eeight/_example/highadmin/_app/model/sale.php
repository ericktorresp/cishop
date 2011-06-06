<?php
/**
 * 文件: /_app/model/sale.php
 * 销售报表数据模型
 * 功能：
 *      获取销售报表数据
 *      --getSingleSale         获取单期盈亏报表数据
 * 		--createSingSale		生成单期盈亏报表数据,数据表singlesale
 *      --getRecentBuy          获取代理最近投注量数据
 * 		--createRecenBuy		生成代理最近投注量数据,数据表recentbuy
 * 
 * @author  mark
 * @version 1.1.0
 * @package highadmin
 */
class model_sale extends basemodel {
	 /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    /**
     * 获取单期盈亏报表数据
     *
     * @param	string	$sFields			查询字段
     * @param	string	$sCondition			查询条件
     * @param	string	$sOrderBy			排序方式
     * @param	int		$iPageRecord		页面记录数
     * @param	int		$iCurrentPage		当前页面
     * @param   int     $iModes             元角模式
     * @return	array						单期盈亏报表数据
     * 
     */
    public function getSingleSale( $sFields = '', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0, $iModes = 0 )
    {
    	$sFields = empty($sFields) ? " sl.*,(sl.`sell`-sl.`bonus`-sl.`return`) AS saleresult,
    	       isfo.`saleend`,isfo.`writetime`,isfo.`code`,l.`cnname` " : addslashes($sFields);
    	$sTable  = " `singlesale` AS sl LEFT JOIN lottery AS l ON (sl.`lotteryid` = l.`lotteryid`)
    	        LEFT JOIN `issueinfo` AS isfo ON(sl.`issue` = isfo.`issue` AND sl.`lotteryid` = isfo.`lotteryid`)";
        if( $iPageRecord == 0 )
        {//不分页显示
            $sCondition = empty($sCondition) ? "" : " WHERE " . $sCondition;
            $sSql       = "SELECT $sFields FROM $sTable $sCondition $sOrderBy";
            return $this->oDB->getAll( $sSql );
        }
        else 
        {
            $sCondition = empty($sCondition) ? "1" : $sCondition;
            $sCountSql = "SELECT count(DISTINCT sl.`issue`,sl.`lotteryid`) AS TOMCOUNT FROM ".$sTable. " WHERE " . $sCondition;
            if( $iModes == -1 )
            {
                $sCondition .= " GROUP BY sl.`lotteryid`,sl.`issue` ";//统计全部模式盈亏值
            }
            return $this->oDB->getPageResult( $sTable, $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy, '', $sCountSql);
        }
    }
    
    
    /**
     * 生成单期盈亏报表数据
     * @param $iLotteryId	彩种Id
     * @param $iIssue		奖期
     * @param $sDate		统计时间,当期开始日期
     */
    public function createSingSale( $iLotteryId = 0, $iIssue = 0, $sDate = '' )
    {
        $iOrderTypeId = 10;//大额撤单手续费用
    	if( !isset($iLotteryId) || empty($iLotteryId) )
    	{
    		die("请选择彩种");
    	}
    	if( !isset($iIssue) || empty($iIssue) )
    	{
    		die("请输入奖期");
    	}
    	if( !isset($sDate) || empty($sDate) )
    	{
    		die("请输入统计日期");
    	}
    	//查询是否已经存在数据，如有则删除后,重新生成。
    	$sSqlHaveDate = "SELECT * FROM `singlesale` WHERE `joindate` = '$sDate' AND `lotteryid` = '$iLotteryId' AND `issue`='$iIssue'" . ' LIMIT 1';
    	$aHaveData = $this->oDB->getOne( $sSqlHaveDate );
    	if( !empty($aHaveData) )
    	{
    	    $this->oDB->delete('singlesale',"`joindate` = '$sDate' AND `lotteryid` = '$iLotteryId' AND `issue`='$iIssue'");
    	}
    	/*计算真实账号销售额、奖金与返点*/
    	$sSql = "SELECT SUM( `totalprice` ) AS totalprice,
    			SUM( `bonus` ) AS totalbonus, SUM( `totalprice` * `lvtoppoint` ) AS totalreturn,p.`modes` 
				FROM `projects` as p LEFT JOIN `usertree` AS ut ON (p.`userid` = ut.`userid`) 
				WHERE `lotteryid` = '$iLotteryId' AND `issue` = '$iIssue' AND ut.`istester` = 0 AND `iscancel` = 0 
				GROUP BY p.`modes`";
    	$aAllResult = $this->oDB->getAll($sSql);
    	foreach ( $aAllResult as $aResult )
    	{
    	    $aData[$aResult['modes']] = array(
    					'sell'		=> isset($aResult['totalprice']) ? $aResult['totalprice'] : 0,
    					'bonus'		=> isset($aResult['totalbonus']) ? $aResult['totalbonus'] : 0,
    					'return'	=> isset($aResult['totalreturn']) ? $aResult['totalreturn'] : 0,
    				);
    	}
    	/*计算真实账号未入封锁玩法的销售额、奖金与返点*/
    	$sSql = "SELECT SUM( `totalprice` ) AS totalprice,
    			SUM( `bonus` ) AS totalbonus, SUM( `totalprice` * `lvtoppoint` ) AS totalreturn,p.`modes` 
				FROM `projects` as p LEFT JOIN `usertree` AS ut ON (p.`userid` = ut.`userid`) 
				LEFT JOIN `method` AS m ON(p.`methodid`=m.`methodid`)
				WHERE p.`lotteryid` = '$iLotteryId' AND `issue` = '$iIssue' AND ut.`istester` = 0 AND `iscancel` = 0 AND m.`islock` = 0
				GROUP BY p.`modes`";
    	$aAllResult = $this->oDB->getAll($sSql);
    	foreach ( $aAllResult as $aResult )
    	{
    	    $aData[$aResult['modes']]['nolock_sell']    = isset($aResult['totalprice']) ? $aResult['totalprice'] : 0;
    	    $aData[$aResult['modes']]['nolock_bonus']   = isset($aResult['totalbonus']) ? $aResult['totalbonus'] : 0;
    	    $aData[$aResult['modes']]['nolock_return']	= isset($aResult['totalreturn']) ? $aResult['totalreturn'] : 0;
    	}
    	unset($tmpSell);
    	unset($tmpbonus);
    	unset($tmpreturn);
        /*计算真实账号大额撤单手续费用*/
        $sSql = "SELECT SUM(o.`amount`) AS amount,p.`modes` 
                 FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid`=ut.`userid`)
                 LEFT JOIN `projects` as p ON(p.`projectid` = o.`projectid`)
                 WHERE o.`ordertypeid` = '$iOrderTypeId' AND ut.`istester` = 0 AND
                 p.`lotteryid`='$iLotteryId' AND p.`issue` = '$iIssue' 
                 GROUP BY p.`modes`";
        $aAllResult = $this->oDB->getAll($sSql);
        foreach ($aAllResult as $aResult )
        {
            $aData[$aResult['modes']]['sell'] += $aResult['amount'];
        }
        /*计算测试账号销售额、奖金与返点*/
    	$sSql = "SELECT SUM( `totalprice` ) AS totalprice,
    			SUM( `bonus` ) AS totalbonus, SUM( `totalprice` * `lvtoppoint` ) AS totalreturn,p.`modes` 
				FROM `projects` as p LEFT JOIN `usertree` AS ut ON (p.`userid` = ut.`userid`) 
				WHERE `lotteryid` = '$iLotteryId' AND `issue` = '$iIssue' AND ut.`istester` = 1 AND `iscancel` = 0 
				GROUP BY p.`modes`";
    	$aAllResult = $this->oDB->getAll($sSql);
    	foreach ($aAllResult as $aResult )
        {
            $aData[$aResult['modes']]['test_sell']   = isset($aResult['totalprice']) ? $aResult['totalprice'] : 0;
            $aData[$aResult['modes']]['test_bonus']  = isset($aResult['totalbonus']) ? $aResult['totalbonus'] : 0;
            $aData[$aResult['modes']]['test_return'] = isset($aResult['totalreturn']) ? $aResult['totalreturn'] : 0;
        }
        /*计算测试账号未入封锁玩法的销售额、奖金与返点*/
    	$sSql = "SELECT SUM( `totalprice` ) AS totalprice,
    			SUM( `bonus` ) AS totalbonus, SUM( `totalprice` * `lvtoppoint` ) AS totalreturn,p.`modes` 
				FROM `projects` as p LEFT JOIN `usertree` AS ut ON (p.`userid` = ut.`userid`) 
				LEFT JOIN `method` AS m ON(p.`methodid`=m.`methodid`)
				WHERE p.`lotteryid` = '$iLotteryId' AND `issue` = '$iIssue' AND ut.`istester` = 1 AND `iscancel` = 0 AND m.`islock` = 0
				GROUP BY p.`modes`";
    	$aAllResult = $this->oDB->getAll($sSql);
    	foreach ( $aAllResult as $aResult )
    	{
    	    $aData[$aResult['modes']]['testnolock_sell']    = isset($aResult['totalprice']) ? $aResult['totalprice'] : 0;
    	    $aData[$aResult['modes']]['testnolock_bonus']   = isset($aResult['totalbonus']) ? $aResult['totalbonus'] : 0;
    	    $aData[$aResult['modes']]['testnolock_return']	= isset($aResult['totalreturn']) ? $aResult['totalreturn'] : 0;
    	}
    	unset($tmpSell);
    	unset($tmpbonus);
    	unset($tmpreturn);
    	/*计算测试账号大额撤单手续费用*/
    	$sSql = "SELECT SUM(o.`amount`) AS amount,p.`modes` 
                 FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid`=ut.`userid`)
                 LEFT JOIN `projects` as p ON(p.`projectid` = o.`projectid`)
                 WHERE o.`ordertypeid` = '$iOrderTypeId' AND ut.`istester` = 1 AND
                 p.`lotteryid`='$iLotteryId' AND p.`issue` = '$iIssue' 
                 GROUP BY p.`modes`";
    	$aAllResult = $this->oDB->getAll($sSql);
        foreach ($aAllResult as $aResult )
        {
            $aData[$aResult['modes']]['test_sell'] += $aResult['amount'];
        }
        $oStatisticsLock = new model_statisticslock();
        $aLockData['lock'] = $oStatisticsLock->getLotteryTotalLock( $iLotteryId, $iIssue );
        if( isset($aData) && !empty($aData) && is_array($aData) )
        {
            //分模式进行插入单期盈亏报表数据
            $this->oDB->doTransaction();
            foreach ($aData as $iModes => $aSigleSaleData )
            {
                $aSigleSaleData['lock'] = $aLockData['lock'];
                $aSigleSaleData['joindate'] = $sDate;
                $aSigleSaleData['lotteryid'] = $iLotteryId;
                $aSigleSaleData['issue'] = $iIssue;
                $aSigleSaleData['modes'] = $iModes;
                $aSigleSaleData['jointime'] = date("Y-m-d H:i:s");
                $this->oDB->insert( 'singlesale', $aSigleSaleData );
                if($this->oDB->errno() > 0)
                {
                    $this->oDB->doRollback();
                    return FALSE;
                }
            }
            $this->oDB->doCommit();
        }
        else 
        {
            $aData = array(
    					'joindate'	=> $sDate,
    					'lotteryid'	=> $iLotteryId,
    					'issue' 	=> $iIssue,
    					'jointime'  => date("Y-m-d H:i:s")
    				);
            $this->oDB->insert( 'singlesale', $aData );
        }
    	return TRUE;
    }
    
    
    /**
     * 获取代理最近投注量
     * 
     * 查询指定代理在指定天数内的投注量
     * @param int $sProxyId		查询的代理名称
     * @param int $iDayCount	查询的天数	
     *
     * @return array
     */
    public function getRecentBuy( $sProxyId = 0, $iDayCount = 0 )
    {
    	$sStartDate = '';
    	$sEndDate	= '';
    	$sCondition = " 1 ";
    	if( $sProxyId )
    	{
    		$sCondition .= " AND `userid` = '$sProxyId' ";
    		//获取查询时间范围
    		if( $iDayCount )
    		{
    			$sEndDate 	= date("Y-m-d", time()-86400);
    			$sStartDate	= date("Y-m-d", strtotime($sEndDate) - ($iDayCount - 1) * 24 * 3600);
    		}
    		else//没有指定查询时间时，返回空数组
    		{
    			return array();
    		}
    		if( $sStartDate && $sEndDate )
    		{
    			$sCondition .= " AND (`date` BETWEEN '$sStartDate' AND '$sEndDate') ";
    		}
    		$sSql = "SELECT * FROM `recentbuy` WHERE $sCondition";
    		return $this->oDB->getAll( $sSql );
    	}
    	else//没有指定代理时,返回空数组
    	{
    		return array();
    	}
    	
    }
    
    
    /**
     * 生成代理最近投注量数据
     * 不可重复生成相同数据
     * 
     * @param $sStartDate	生成数据开始时间
     * @param $sEndDate		生成数据结束时间
     *
     */
	public function createRecenBuy( $sStartDate, $sEndDate )
 	{
   		$this->oDB->doTransaction();//开始事务
   		$bSucc = TRUE;
   		$oConfig = new model_config();
        $aSnapshotTime = explode('-',$oConfig->getConfigs('kz_allow_time'));
        $sStartDate = $sStartDate . " " . $aSnapshotTime[0];
        $sEndDate   = $sEndDate . " " . $aSnapshotTime[0];
   		$iStartTime = strtotime($sStartDate);
   		$iEndTime 	= strtotime($sEndDate);
   		while ( $iStartTime < $iEndTime )
   		{
   			$iTempTime  = $iStartTime + 24 * 3600;
   			$sSql ="SELECT ut.`usertype`,ut.`parenttree`,ut.`username` , p.`modes`,p.`userid` , SUM( p.`totalprice` ) AS total
   						FROM `projects` AS p LEFT JOIN `usertree` AS ut ON ( ut.`userid` = p.`userid` )
   						WHERE p.`iscancel`='0' AND p.`deducttime` BETWEEN '" . date("Y-m-d H:i:s",$iStartTime) 
   						. "' AND '" . date("Y-m-d H:i:s",$iTempTime) ."'
						GROUP BY p.`userid`,p.`modes`";
   			$aUserAmount = $this->oDB->getAll( $sSql );//获取投注量
   			//统计所有与投注相关的代理投注量
   			$aAmout = array();
   			foreach ( $aUserAmount as $aValueCount )
   			{
   				$bIsHaveSelf = $aValueCount['usertype'] == 1 ? ',' . $aValueCount['userid'] : '';//是否是代理
   				$sUser = $aValueCount['parenttree'] . $bIsHaveSelf;
   				$aUser = explode(',',$sUser);
   				foreach ( $aUser as $iUserId )
   				{
   					isset($aAmout[$iUserId][$aValueCount['modes']]) ? $aAmout[$iUserId][$aValueCount['modes']] += $aValueCount['total']
   					 : $aAmout[$iUserId][$aValueCount['modes']] = $aValueCount['total'];
   				}
   			}
   			//增加投注量记录数据
   			foreach ( $aAmout as $iUserId => $aTmpAmout )
   			{
   				if( $aTmpAmout )
   				{
   					$aData = array(
   									'userid'	=> $iUserId,
   									'date'		=> date('Y-m-d',$iStartTime),
   									'amount' 	=> serialize($aTmpAmout),
   									'utime'     => date("Y-m-d H:i:s")
   								);
   					$iDate = $aData['date'];
   				    $sSql = " SELECT * FROM `recentbuy` WHERE `userid` = '$iUserId' AND `date` = '$iDate' " . ' LIMIT 1';
   			        $aResult = $this->oDB->getOne( $sSql );
   			        if( !empty($aResult) )
   			        {
   			            $this->oDB->delete('recentbuy',"userid = '$iUserId' AND date = '$iDate' ");
   			        }
   					if( !$bSucc = $this->oDB->insert( 'recentbuy', $aData ) )//增加统计的投注量数据
   					{
   						$this->oDB->doRollback();//增加失败事务回滚
   						break 2;
   					}
   				}
   			}
   			$iStartTime += 24 * 3600;
   		}
   		if( $bSucc )
   		{
   			$this->oDB->doCommit();//事务提交
   			echo "[d] [".date("Y-m-d H:i:s")."] Successed! \n\n";
   		}
	}
}