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
 * 		--backandclearData		备份并删除数据 recentbuy表		6/21/2010
 * 
 * @author  mark
 * @version 1.1.0
 * @package lowadmin
 * @since   2009/08/26
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
     * @return	array						单期盈亏报表数据
     * 
     */
    public function getSingleSale( $sFields = '', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0 )
    {
    	$sFields = empty($sFields) ? " sl.*,l.`cnname` " : addslashes($sFields);
    	$sTable  = " `singlesale` AS sl LEFT JOIN lottery AS l ON (sl.`lotteryid` = l.`lotteryid`)";
        if( $iPageRecord == 0 )
        {//不分页显示
            $sCondition = empty($sCondition) ? "" : " WHERE " . $sCondition;
            $sSql       = "SELECT $sFields FROM $sTable $sCondition $sOrderBy";
            return $this->oDB->getAll( $sSql );
        }
        else 
        {
            $sCondition = empty($sCondition) ? "1" : $sCondition;
            return $this->oDB->getPageResult( $sTable, $sFields, $sCondition, $iPageRecord, $iCurrentPage);
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
    	$sStartDay = date("Y-m-d", strtotime($sDate)) . ' 02:20:00';
    	$sEndDay = date("Y-m-d", strtotime($sDate) + 3600 * 24) . " 02:20:00";
    	//查询是否已经存在数据，如有则删除后,重新生成。
    	$sSqlHaveDate = "SELECT * FROM `singlesale` WHERE `joindate` = '$sDate' AND `lotteryid` = '$iLotteryId'";
    	$aHaveData = $this->oDB->getOne( $sSqlHaveDate );
    	if( !empty($aHaveData) )
    	{
    	    $this->oDB->delete('singlesale',"`joindate` = '$sDate' AND `lotteryid` = '$iLotteryId'");
    	}
    	/*计算真实账号销售额、奖金与返点*/
    	$sSql = "SELECT SUM( `totalprice` ) AS totalprice,
    			SUM( `bonus` ) AS totalbonus, SUM( `totalprice` * `lvtoppoint` ) AS totalreturn
				FROM `projects` as p LEFT JOIN `usertree` AS ut ON (p.`userid` = ut.`userid`) 
				WHERE `lotteryid` = '$iLotteryId' AND `issue` = '$iIssue' AND ut.`istester` = 0 AND `iscancel` = 0";
    	$aResult = $this->oDB->getOne($sSql);
    	$aData = array(
    					'joindate'	=> $sDate,
    					'lotteryid'	=> $iLotteryId,
    					'issue' 	=> $iIssue,
    					'sell'		=> isset($aResult['totalprice']) ? $aResult['totalprice'] : 0,
    					'charge'	=> 0,
    					'bonus'		=> isset($aResult['totalbonus']) ? $aResult['totalbonus'] : 0,
    					'return'	=> isset($aResult['totalreturn']) ? $aResult['totalreturn'] : 0
    				);
        /*计算真实账号大额撤单手续费用*/
        $sSql = "SELECT SUM(o.`amount`) AS amount 
                 FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid`=ut.`userid`)
                 WHERE o.`actiontime` >= '{$sStartDay}' AND o.`actiontime` <= '{$sEndDay}' 
                 AND o.`ordertypeid` = '$iOrderTypeId' AND ut.`istester` = 0 AND o.`lotteryid` = '{$iLotteryId}'";
        $aResult = $this->oDB->getOne($sSql);
        $aData['charge'] = isset($aResult['amount']) ? $aResult['amount'] : 0;
//        $aData['sell'] += $aResult['amount'];
        /*计算测试账号销售额、奖金与返点*/
    	$sSql = "SELECT SUM( `totalprice` ) AS totalprice,
    			SUM( `bonus` ) AS totalbonus, SUM( `totalprice` * `lvtoppoint` ) AS totalreturn
				FROM `projects` as p LEFT JOIN `usertree` AS ut ON (p.`userid` = ut.`userid`) 
				WHERE `lotteryid` = '$iLotteryId' AND `issue` = '$iIssue' AND ut.`istester` = 1 AND `iscancel` = 0";
    	$aResult = $this->oDB->getOne($sSql);
    	$aData['test_sell']   = isset($aResult['totalprice']) ? $aResult['totalprice'] : 0;
    	$aData['test_bonus']  = isset($aResult['totalbonus']) ? $aResult['totalbonus'] : 0;
    	$aData['test_return'] = isset($aResult['totalreturn']) ? $aResult['totalreturn'] : 0;
    	$aData['test_charge'] = 0;
    	/*计算测试账号大额撤单手续费用*/
    	$sSql = "SELECT SUM(o.`amount`) AS amount 
                 FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid`=ut.`userid`)
                 WHERE o.`actiontime` >= '{$sStartDay}' AND o.`actiontime` <= '{$sEndDay}' 
                 AND o.`ordertypeid` = '$iOrderTypeId' AND ut.`istester` = 1 AND o.`lotteryid` = '{$iLotteryId}'";
    	$aResult = $this->oDB->getOne($sSql);
    	$aData['test_charge'] = isset($aResult['amount']) ? $aResult['amount'] : 0;
//        $aData['test_sell'] += $aResult['amount'];
		$aData['jointime'] = date('Y-m-d H:i:s');
    	return $this->oDB->insert( 'singlesale', $aData );
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
    			$sEndDate 	= date("Y-m-d");
    			$sStartDate	= date("Y-m-d",strtotime($sEndDate) - ($iDayCount - 1) * 24 * 3600);
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
   		$iStartTime = strtotime($sStartDate);
   		$iEndTime 	= strtotime($sEndDate);
   		while ( $iStartTime < $iEndTime )
   		{
   			$iTempTime  = $iStartTime + 24 * 3600;
   			$sSql ="SELECT ut.`usertype`,ut.`parenttree`,ut.`username` , p.`userid` , SUM( p.`totalprice` ) AS total
   						FROM `projects` AS p LEFT JOIN `usertree` AS ut ON ( ut.`userid` = p.`userid` )
   						WHERE p.`writetime` BETWEEN '" . date("Y-m-d H:i:s",$iStartTime) 
   						. "' AND '" . date("Y-m-d H:i:s",$iTempTime) ."'
						GROUP BY p.`userid`";
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
   					isset($aAmout[$iUserId]) ? $aAmout[$iUserId] += $aValueCount['total'] : $aAmout[$iUserId] = $aValueCount['total'];
   				}
   			}
   			//增加投注量记录数据
   			foreach ( $aAmout as $iUserId => $fAmout )
   			{
   				if( $fAmout )
   				{
   					$aData = array(
   									'userid'	=> $iUserId,
   									'date'		=> date('Y-m-d',$iStartTime),
   									'amount' 	=> $fAmout,
   									'utime' => date('Y-m-d H:i:s')
   								);
   					$iDate = $aData['date'];
   				    $sSql = " SELECT * FROM `recentbuy` WHERE `userid` = '$iUserId' AND `date` = '$iDate' ";
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
	
	
	/**
	 * 代理投注量保存与删除
	 * @param integer $iDay
     * @param string  $sPath
     * @return bool
     * 
     * 涉及表 recentbuy
     * 
     * 6/21/2010
	 */
	public function backandclearData($iDay,$sPath)
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
		
    	$numCodes = $this->oDB->getOne("SELECT COUNT(id) AS `numCodes` FROM `recentbuy` "
		                        ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_recentbuy.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `recentbuy` "
		                                ." WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
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
				$sql = "INSERT INTO `recentbuy` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
				
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `recentbuy` WHERE `utime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
	}
}