<?php
/**
 * 文件: /_app/model/lottery.php
 * 彩种模型
 *
 */
class model_lottery extends basemodel
{
	public function __construct( $aDBO = array() )
	{
		parent::__construct( $aDBO );
	}
	
	/**
	 * 插入一条彩种记录
	 * @author SAUL   090714
	 * @param  array  $aOldLottery
	 * @return integer
	 */
	public function lotteryInsert( $aOldLottery )
	{
		if( !isset($aOldLottery) || empty($aOldLottery) )
		{ // 数据错误
			return 0;
		}
		if( $aOldLottery["cnname"] == "" )
		{ // 彩种中文名称不存在
			return -1;
		}
		$aLottery["cnname"] = daddslashes( $aOldLottery["cnname"] );
		if( $aOldLottery["enname"] == "" )
		{ // 彩种英文名称不存在
			return -2;
		}		
		$aLottery["enname"] = daddslashes( $aOldLottery["enname"] );
		if( !in_array( $aOldLottery["type"], array( 0, 1, 2 ) ) )
		{ // 彩种类型错误
			return -3;
		}
		$aLottery["lotterytype"] = $aOldLottery["type"];
		$aLottery["dailystart"]  = getFilterDate( $aOldLottery["dailystart"], "H:i:s" );
		$aLottery["dailyend"]    = getFilterDate( $aOldLottery["dailyend"],   "H:i:s" );
		$aLottery["edittime"]    = getFilterDate( $aOldLottery["edittime"],   "H:i:s" );
		$aLottery["canceldeadline"] = getFilterDate($aOldLottery["canceldeadline"],"H:i:s");
		$aLottery["weekcycle"] = array_sum($aOldLottery["date"]);
		if( $aLottery["weekcycle"] == 0 )
		{ // 彩种周期错误
			return -4;
		}
		if( empty($aOldLottery["issuerule"]) )
		{ // 彩种的奖期规则错误
			return -5;
		}
		if(!is_numeric($aOldLottery["mincommissiongap"]) || 
		          $aOldLottery["mincommissiongap"] < 0 || $aOldLottery["mincommissiongap"] >= 1)
		{ // 彩种的返点差
			return -6;	
		}
		$aLottery["mincommissiongap"] = number_format($aOldLottery["mincommissiongap"],2);
		if(!is_numeric($aOldLottery["minprofit"]))
		{ // 彩种的最小留水
			return -7;
		}
		$aLottery["minprofit"] = number_format($aOldLottery["minprofit"],2);
		$aLottery["issuerule"] = $aOldLottery["issuerule"];
		if(!empty($aOldLottery["yearlybreakstart"]))
		{
			$aLottery["yearlybreakstart"] = getFilterDate( $aOldLottery["yearlybreakstart"],"Y-m-d" );	
		}
		if(!empty($aOldLottery["yearlybreakend"]))
		{
			$aLottery["yearlybreakend"]   = getFilterDate( $aOldLottery["yearlybreakend"],"Y-m-d" );
		}
		if(empty($aOldLottery["adjustminprofit"]))
		{ //限极上调奖金公司留水
			return -8;
		}
		$aLottery["adjustminprofit"] = number_format($aOldLottery["adjustminprofit"],3,".","");
		if(empty($aOldLottery["adjustmaxpercent"]))
		{ //极限下调奖金返奖率
			return -9;
		}
		$aLottery["adjustmaxpercent"] = number_format($aOldLottery["adjustmaxpercent"],3,".","");
		$aLottery["description"] = daddslashes($aOldLottery["description"]);
		$aLottery["numberrule"]  = serialize($aOldLottery["norule"]);
		$aLottery["dynamicprizestart"] = getFilterDate( $aOldLottery["dynamicprizestart"], "H:i:s" );
		$aLottery["dynamicprizeend"]   = getFilterDate( $aOldLottery["dynamicprizeend"],   "H:i:s" );
		return $this->oDB->insert( 'lottery', $aLottery );
	}



	/**
	 * 获取一个彩种信息
	 * @author SAUL   090714
	 * @param  string $sFields
	 * @param  string $sCondition
	 * @return array
	 */
	public function lotteryGetOne( $sFields='', $sCondition='' )
	{
		$sFields    = empty($sFields) ? '*' : daddslashes($sFields);
		$sCondition = empty($sCondition) ? '1' : $sCondition;
		$sSql       = "SELECT ".$sFields." FROM `lottery` WHERE ".$sCondition;
		return $this->oDB->getOne( $sSql );
	}



	/**
	 * 获取彩种列表[单表]
	 *
	 * @author  james 09/07/27
     * @access  public
	 * @param   string  $sFields
	 * @param   string  $sCondition
	 * @param   string  $sOrderBy
	 * @param   int     $iPageRecord
	 * @param   int     $iCurrentPage
	 * @return  array //返回记录结果集
	 */
	public function lotteryGetList( $sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
	{
	    $sFields = empty($sFields) ? "*" : addslashes($sFields);
        if( $iPageRecord == 0 )
        {//不分页显示
            $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
            $sSql       = "SELECT ".$sFields." FROM `lottery` ".$sCondition." ".$sOrderBy;
            return $this->oDB->getAll( $sSql );
        }
        else 
        {
            $sCondition = empty($sCondition) ? "1" : $sCondition;
            return $this->oDB->getPageResult( 'lottery', $sFields, $sCondition, $iPageRecord, $iCurrentPage, 
                                               $sOrderBy );
        }
	}



	/**
	 * 获取彩种列表[联合玩法]
	 * @author SAUL 090714
	 * @param  string  $sFields
	 * @param  string  $sCondition
	 * @param  string  $sOrderBy
	 * @param  integer $iPageRecord
	 * @param  integer $iCurrentPage
	 */
	public function lotteryMethodGetList( $sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
	{
		if( empty($sFields) )
		{ // 默认字段
			$sFields = "a.*,sum(b.`isclose`) as `count_status`,count(b.`methodid`) as `count_method`";
		}
		if( empty($sCondition) )
		{
			$sCondition = "1  group by  a.`lotteryid`";
		}
		else
		{
			$sCondition = $sCondition." group by a.`lotteryid`";
		}
		$iPageRecord  = intval( $iPageRecord );
		$sTableName ="`lottery` as a left join `method` as b on (b.`lotteryid`=a.`lotteryid`)";
		if( $iPageRecord == 0 )
		{
			return $this->oDB->getAll("SELECT ".$sFields."FROM ".$sTableName." WHERE ".$sCondition);
		}
		$iCurrentPage = intval( $iCurrentPage );
		if( $iCurrentPage == 0 )
		{
			$iCurrentPage = 1;
		}
		return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord,
			 $iCurrentPage, $sOrderBy );
	}



	/**
	 * 更新彩种信息
	 *
	 * @param array $aOldLottery
	 * @param string $sWhere
	 * @return int
	 */
	public function lotteryUpdate( $aOldLottery, $sWhere )
	{
		if( !isset($aOldLottery) || empty($aOldLottery) )
		{ // 数据错误
			return -1;
		}
		if(isset($aOldLottery["cnname"]))
		{
			if( $aOldLottery["cnname"] == "" )
			{ // 彩种中文名称不存在
				return -2;
			}		
			$aLottery["cnname"] = daddslashes( $aOldLottery["cnname"] );
		}
		if(isset($aOldLottery["enname"]))
		{
			if( $aOldLottery["enname"] == "" )
			{ // 彩种英文名称不存在
				return -3;
			}		
			$aLottery["enname"] = daddslashes( $aOldLottery["enname"] );
		}
		if(isset($aOldLottery["type"]))
		{ // 不能修改类型
			return -4;
		}
		if(isset($aOldLottery["mincommissiongap"]))
		{
			if(!is_numeric($aOldLottery["mincommissiongap"]))
			{ // 彩种的返点差
				return -7;	
			}
			$aLottery["mincommissiongap"] = number_format($aOldLottery["mincommissiongap"],3 );
		}
		if(($aLottery["mincommissiongap"]>=1)||($aLottery["mincommissiongap"]<0))
		{
			return -7;
		}
		if(isset($aOldLottery["minprofit"]))
		{		
			if(!is_numeric($aOldLottery["minprofit"]))
			{ // 彩种的最小留水
				return -8;
			}
			$aLottery["minprofit"] = number_format( $aOldLottery["minprofit"], 3 );
		}
	    if(($aLottery["minprofit"]>=1)||($aLottery["minprofit"]<0))
        {
            return -8;
        }
		if(isset($aOldLottery["dailystart"]))
		{
			$aLottery["dailystart"]  = getFilterDate( $aOldLottery["dailystart"], "H:i:s" );
		}
		if(isset($aOldLottery["dailyend"]))
		{
			$aLottery["dailyend"]    = getFilterDate( $aOldLottery["dailyend"],   "H:i:s" );
		}
		if(isset($aOldLottery["edittime"]))
		{
			$aLottery["edittime"]    = getFilterDate( $aOldLottery["edittime"],   "H:i:s" );
		}
		if(isset($aOldLottery["canceldeadline"]))
		{
			$aLottery["canceldeadline"] = getFilterDate($aOldLottery["canceldeadline"],"H:i:s");
		}
		if(isset($aOldLottery["date"]))
		{
			$aLottery["weekcycle"] = array_sum($aOldLottery["date"]);
			if( $aLottery["weekcycle"] == 0 )
			{ // 彩种周期错误
				return -5;
			}
		}		
		if(isset($aOldLottery["issuerule"]))
		{
			if( empty($aOldLottery["issuerule"]) )
			{ // 彩种的奖期规则错误
				return -6;
			}
			$aLottery["issuerule"] = $aOldLottery["issuerule"];
		}
		if(isset($aOldLottery["yearlybreakstart"]))
		{
			if(!empty($aOldLottery["yearlybreakstart"]))
			{
				$aLottery["yearlybreakstart"] = getFilterDate( $aOldLottery["yearlybreakstart"],"Y-m-d" );	
			}
			else
			{
				$aLottery["yearlybreakstart"] = "NULL";
			}
		}
		if(isset($aOldLottery["yearlybreakstart"]))
		{
			if(!empty($aOldLottery["yearlybreakend"]))
			{
				$aLottery["yearlybreakend"]   = getFilterDate( $aOldLottery["yearlybreakend"],"Y-m-d" );
			}
			else
			{
				$aLottery["yearlybreakend"]   = "NULL";
			}
		}		
		if(isset($aOldLottery["description"]))
		{
			$aLottery["description"] = daddslashes($aOldLottery["description"]);
		}
		if(isset($aOldLottery["norule"]))
		{
			$aLottery["numberrule"]  = serialize($aOldLottery["norule"]);
		}
		if(isset($aOldLottery["dynamicprizestart"]))
		{
			$aLottery["dynamicprizestart"] = getFilterDate( $aOldLottery["dynamicprizestart"], "H:i:s" );
		}
		if(isset($aOldLottery["dynamicprizeend"]))
		{
			$aLottery["dynamicprizeend"]   = getFilterDate( $aOldLottery["dynamicprizeend"],   "H:i:s" );
		}
		if(isset($aOldLottery["adjustminprofit"]))
		{
			if(empty($aOldLottery["adjustminprofit"]))
			{//彩种的公司最小留水错误
				return -9;
			}
			$aLottery["adjustminprofit"] = number_format($aOldLottery["adjustminprofit"],3,".","");
		}
		if(isset($aOldLottery["adjustmaxpercent"]))
		{
			if(empty($aOldLottery["adjustmaxpercent"]))
			{ //极限奖金下调
				return -10;
			}
			$aLottery["adjustmaxpercent"] = number_format($aOldLottery["adjustmaxpercent"],3,".","");
		}
		return $this->oDB->update('lottery', $aLottery, $sWhere );
	}



	/**
	 * 改变游戏状态
	 * @author SAUL    090714
	 * @param  integer $iLotteryId
	 * @param  integer $iStatus
	 * @return bool
	 */
	function setStatus($iLotteryId, $iStatus)
	{
		if(!is_numeric($iLotteryId))
		{
			return FALSE;
		}
		if(!in_array( $iStatus, array(0,1) ))
		{
			return FALSE;
		}
		$this->oDB->query("update `method` set `isclose`='".intval($iStatus)."' where `lotteryid`='".$iLotteryId."'");
		return $this->oDB->ar()>0;
	}
	
	
	/**
	 * 根据彩种ID, 号码判断是否符合规则
	 * @author Tom 090904 15:26
	 * @param int $iLotteryId
	 * @param string $sCode
	 * @return BOOL  TRUE|FALSE
	 */
	public function checkCodeFormat( $iLotteryId, $sCode='' )
	{
        $aRow = $this->lotteryGetOne( '`numberrule`,`lotterytype`', " `lotteryid`='".intval($iLotteryId)."' " );
        if( !empty($aRow) && isset($aRow["numberrule"]) )
        {
            $aMatches = array();
            $aData = @unserialize($aRow["numberrule"]);
            if( $aRow["lotterytype"]==0 ) 
            { //数字型
            // aData = Array( [len] => 5, [startno] => 0, [endno] => 9 )
                $iCounts = preg_match_all("/^([".$aData["startno"]."-".$aData["endno"]."]{".$aData["len"]."})$/",$sCode,$aMatches);
                if( 1 == $iCounts )
                { //号码格式正确
                    return TRUE;
                }
            }
            elseif( $aRow["lotterytype"]==1 )
            { //TODO 乐透同区型
               return FALSE;
            }
            elseif( $aRow["lotterytype"]==2 )
            { //TODO 乐透分区型
                return FALSE;
            }
        }
        return FALSE;
	}
	

	/**
	 * 根据用户获取其开通的彩种列表
	 * 
	 * @author james   090804
	 * @access public
	 * @param  int     $iUserId //用户ID
	 * @param  boolean $bIsTop     //是否为总代
	 * @param  string  $sFields    //要查询的内容
	 * @return array   //返回结果集
	 */
	public function & getLotteryByUser( $iUserId, $bIsTop=FALSE, $sFields="" )
	{
		$aResult = array();
		if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
		{
			return $aResult;
		}
		$iUserId = intval($iUserId);
		$sFields = empty($sFields) ? " l.`lotteryid`,l.`cnname`,l.`enname`,l.`mincommissiongap` " 
		                             : daddslashes($sFields);
		if( $bIsTop == TRUE )
		{//如果为总代则结合总代奖金组设置表
			$sSql = " SELECT ".$sFields." FROM `lottery` AS l 
			          LEFT JOIN `userprizegroup` AS upg ON l.`lotteryid`=upg.`lotteryid` 
			          WHERE upg.`status`='1' AND upg.`userid`='".$iUserId."' GROUP BY l.`lotteryid` ";
		}
		else 
		{//普通代理为结合用户玩法设置表
			$sSql = " SELECT ".$sFields." FROM `lottery` AS l 
                      LEFT JOIN `userprizegroup` AS upg ON l.`lotteryid`=upg.`lotteryid`
                      LEFT JOIN `usermethodset`  AS ums ON ums.`prizegroupid`=upg.`userpgid`
                      WHERE upg.`status`='1' AND ums.`userid`='".$iUserId."' AND ums.`isclose`='0' 
                      GROUP BY l.`lotteryid` ";
		}
		return $this->oDB->getAll( $sSql );
	}



	/**
	 * 根据彩种ID批量更新时间
	 *
	 * @param array    $aNewLottery
	 * @param integer  $iLotteryId
	 */
	function updateLotteryTime( $aNewLottery, $iLotteryId )
	{
		$aOldLottery = $this->oDB->getOne("SELECT `dailystart`,`dailyend`,`edittime`,`canceldeadline`,`dynamicprizestart`,`dynamicprizeend` FROM `lottery` where `lotteryid`='".$iLotteryId."'");
		$iStartDiff = 0;
		$iEndDiff = 0;
		if($aOldLottery["dailystart"]>$aOldLottery["dailyend"])
		{
			if($aNewLottery["dailystart"]>$aNewLottery["dailyend"])
			{ //依旧是跨天开售
				if($aNewLottery["dailystart"]<>$aOldLottery["dailystart"])
				{
					$iStartDiff = strtotime($aNewLottery["dailystart"]) - strtotime($aOldLottery["dailystart"]);
				}
			}
			else
			{
			    if($aNewLottery["dailystart"]<>$aOldLottery["dailystart"])
                {
                    $iStartDiff = strtotime($aNewLottery["dailystart"])-strtotime($aOldLottery["dailystart"])+24*60*60;
                }
			}
		}
		else
		{
		    if($aNewLottery["dailystart"]>$aNewLottery["dailyend"])
            { //改成了跨天开售
                if($aNewLottery["dailystart"]<>$aOldLottery["dailystart"])
                {
                    $iStartDiff = strtotime($aNewLottery["dailystart"]) - strtotime($aOldLottery["dailystart"])-24*60*60;
                }
            }
            else
            { //依旧是非跨天销售
                if($aNewLottery["dailystart"]<>$aOldLottery["dailystart"])
                {
                    $iStartDiff = strtotime($aNewLottery["dailystart"]) - strtotime($aOldLottery["dailystart"]);
                }
            }
		}
        if($aNewLottery["dailyend"]<>$aOldLottery["dailyend"])
        {
            $iEndDiff = strtotime($aNewLottery["dailyend"]) - strtotime($aOldLottery["dailyend"]);
        }
        $this->oDB->doTransaction();
        if($iStartDiff<>0)
        {
	        $this->oDB->query("update `issueinfo` set `salestart`=addtime(`salestart`,sec_to_time(".$iStartDiff.")) where `lotteryid`='".$iLotteryId."' and `salestart` like '%".$aOldLottery["dailystart"]."'");
			if($this->oDB->errno()>0)
			{
				$this->oDB->doRollback();
				return -1; //销售开始时间调整
			}
        }
		if($iEndDiff<>0)
		{
			$this->oDB->query("update `issueinfo` set `saleend`=addtime(`saleend`,sec_to_time(".$iEndDiff.")) where `lotteryid`='".$iLotteryId."' and `salestart` like '%".$aOldLottery["dailystart"]."'");
	        if($this->oDB->errno()>0)
	        {
	        	$this->oDB->doRollback();
	        	return -2; //销售截至时间调整
	        }
		}
		//最后撤单时间
		if($aNewLottery["canceldeadline"]<>$aOldLottery["canceldeadline"])
        {
            $iCancelDiff = strtotime($aNewLottery["canceldeadline"]) - strtotime($aOldLottery["canceldeadline"]);
            $this->oDB->query("update `issueinfo` set `canneldeadline`=addtime(`canneldeadline`,sec_to_time(".$iCancelDiff.")) where `lotteryid`='".$iLotteryId."' and `canneldeadline` like '%".$aOldLottery["canceldeadline"]."'");
            if($this->oDB->errno()>0)
            {
            	$this->oDB->doRollback();
                return -3; //销售截至时间调整
            }
        }
        if($aNewLottery["dynamicprizestart"]<>$aOldLottery["dynamicprizestart"])
        {
        	$iDynamicprizestart = strtotime($aNewLottery["dynamicprizestart"]) - strtotime($aOldLottery["dynamicprizestart"]);
            $this->oDB->query("update `issueinfo` set `dynamicprizestart`=addtime(`dynamicprizestart`,sec_to_time(".$iDynamicprizestart.")) where `lotteryid`='".$iLotteryId."' and `dynamicprizestart` like '%".$aOldLottery["dynamicprizestart"]."'");
            if($this->oDB->errno()>0)
            {
                $this->oDB->doRollback();
                return -4; //开始调价时间
            }
        }
        if($aNewLottery["dynamicprizeend"]<>$aOldLottery["dynamicprizeend"])
        {
            $iDynamicprizeend = strtotime($aNewLottery["dynamicprizeend"]) - strtotime($aOldLottery["dynamicprizeend"]);
            $this->oDB->query("update `issueinfo` set `dynamicprizeend`=addtime(`dynamicprizeend`,sec_to_time(".$iDynamicprizeend.")) where `lotteryid`='".$iLotteryId."' and `dynamicprizeend` like '%".$aOldLottery["dynamicprizeend"]."'");
            if($this->oDB->errno()>0)
            {
                $this->oDB->doRollback();
                return -5; //调价结束时间
            }
        }
		if(isset($aNewLottery["date"]))
		{
			$aNewLottery["weekcycle"] = array_sum($aNewLottery["date"]);
			unset($aNewLottery["date"]);
		}
        $this->oDB->update('lottery',$aNewLottery,"`lotteryid`='".$iLotteryId."'");
        if($this->oDB->errno()>0)
        {
        	$this->oDB->doRollback();
        	return -7; //系统记录的时间
        }
        $this->oDB->doCommit();
        return 1;
	}
}
?>
