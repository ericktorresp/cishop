<?php
class model_method extends basemodel
{
	function __construct( $aDBO = array())
	{
		parent::__construct( $aDBO );
	}



	/**
	 * 获取游戏玩法列表
	 * @author SAUL    090715
	 * @param  string  $sFields
	 * @param  string  $sCondition
	 * @param  string  $sOrderBy
	 * @param  integer $iPageRecord
	 * @param  integer $iCurrentPage
	 * @return array
	 */
	function methodGetList( $sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
	{
		if( empty($sFields) )
		{ // 默认字段
			$sFields = "*";
		}
		if( empty($sCondition) )
		{
			$sCondition = "1";
		}
		$iPageRecord  = intval( $iPageRecord );
		$sTableName ="`method` as a left join `lottery` as b on (b.`lotteryid`=a.`lotteryid`)";
		if( $iPageRecord == 0 )
		{
			return $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition);
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
	 * 增加游戏玩法
	 * @author SAUL   090715
	 * @param  array  $aOldMethod
	 * @return integer
	 */
	function methodInsert( $aOldMethod )
	{	
		if( !isset($aOldMethod) || empty($aOldMethod) )
		{
			return 0;
		}
		if(!is_numeric($aOldMethod["lotteryid"]))
		{ //彩种类型错误
			return -1;
		}
		$aMethod["lotteryid"] = intval($aOldMethod["lotteryid"]);
		if( $aMethod["lotteryid"] <=0 )
		{ //彩种类型错误
			return -1;
		}
		if( empty($aOldMethod["methodname"]) )
		{ //彩种名称为空
			return -2;
		}
		$aMethod["methodname"] = daddslashes( $aOldMethod["methodname"] );
		if( empty($aOldMethod["functionname"]) )
		{ //彩种的中奖函数名称为空
			return -3;
		}
		$aMethod["pid"] = isset($aOldMethod["pid"])&&is_numeric($aOldMethod["pid"]) ? intval($aOldMethod["pid"]):0; 
		$aMethod["functionname"] = daddslashes( $aOldMethod["functionname"] );
		$aMethod["isprizedynamic"] = isset($aOldMethod["isprizedynamic"]) ? 1: 0;
		$aMethod["locksid"] = empty($aOldMethod["locksid"]) ? 0 :intval($aOldMethod["locksid"]);
		if( empty($aOldMethod["level"]) || !is_numeric($aOldMethod["level"]) )
		{ // 奖级个数错误
			return -5;
		}
		$aMethod["level"] = intval( $aOldMethod["level"] );
		if($aMethod["level"]<=0)
		{
			return -5;
		}
		$aMethod["count"] =array();
		for($i = 1;$i<=$aMethod["level"];$i++)
		{
			if(!is_numeric($aOldMethod["count"][$i]["count"]))
			{
				return -6;
			}
			$aMethod["count"][$i]["count"] = intval($aOldMethod["count"][$i]["count"]);
			$aMethod["count"][$i]["name"] = $aOldMethod["count"][$i]["name"];
			if($aMethod["level"]>1)
			{
				$aMethod["count"][$i]["use"] = isset($aOldMethod["count"][$i]["use"])&&is_numeric($aOldMethod["count"][$i]["use"]) ? 1:0;
			}
		}
		if(isset($aOldMethod["count"]["type"])&&($aOldMethod["count"]["type"]==1))
		{
			$aMethod["count"]["type"] = 1;
		}
		else
		{
			$aMethod["count"]["type"] = 0;
		}
		if(isset($aOldMethod["count"]["isdesc"])&&($aOldMethod["count"]["isdesc"]))
		{
			$aMethod["count"]["isdesc"] = 1;
		}
		else
		{
			$aMethod["count"]["isdesc"] = 0;
		}
		$aMethod["nocount"] = serialize($aMethod["count"]);
		unset($aMethod["count"]);
		$aMethod["description"] = daddslashes( $aOldMethod["description"] );
		if($aMethod["pid"] == 0)
		{
			$aMethod["areatype"] = base64_encode(serialize(array()));
		}
		else
		{
			if(!empty($aOldMethod["areatype"]))
			{
				$aMethod["areatype"] = base64_encode( serialize($aOldMethod["areatype"]) );
			}
			else
			{
				return -4;
			}
		}
		if(!is_numeric($aOldMethod['totalmoney']))
		{
			return -7;
		}
		$aMethod['totalmoney'] = number_format($aOldMethod['totalmoney'],2,'.','');
		return $this->oDB->insert( 'method', $aMethod );		
	}



	/**
	 * 设置玩法销售状态
	 * @author SAUL 090717
	 * @param  integer $iMethodid
	 * @param  integer $iStatus
	 * @return bool
	 */
	function setMethodStatus( $iMethodid, $iStatus )
	{
		$iMethodid = is_numeric( $iMethodid ) ? intval( $iMethodid ): 0;
		if( $iMethodid==0 )
		{
			return FALSE;
		}
		$iStatus = in_array($iStatus, array( 0, 1) )? intval($iStatus):-1;
		if($iStatus == -1)
		{
			return FALSE;
		}
		$this->oDB->query("UPDATE `method` SET `isclose`='".$iStatus."' WHERE `methodid`='".$iMethodid."'");
		return ($this->oDB->ar()>0);
	}



	/**
	 * 获取某个玩法的信息
	 *
	 * @param string $sFields
	 * @param string $sCondition
	 * @return array
	 */
	function methodGetOne( $sFields='', $sCondition='' )
	{
		if(empty($sFields))
		{
			$sFields = "*";
		}
		if(empty($sCondition))
		{
			$sCondition = "1";
		}
		return $this->oDB->getOne("select ".$sFields." FROM `method` where ".$sCondition." limit 1");
	}
	
	
	
	/**
	 * 根据玩发获取相应的信息
	 *
	 * @author james   090810
	 * @param  string   $sFields   //字段，
	 * @param  string   $sCondition //条件
	 * @param  string   $sLeftJoin 左关联表
	 * @return array
	 */
	public function & methodGetInfo( $sFields='', $sCondition='', $sLeftJoin= '' )
	{
		$sFields    = empty($sFields) ? '*' : daddslashes($sFields);
		$sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
		$sTable     = " `method` AS m ".$sLeftJoin;
		$sSql       = " SELECT ".$sFields." FROM ".$sTable." ".$sCondition;
		$aResult    = $this->oDB->getDataCached($sSql);
		return $aResult;
	}
	
	
	
	/**
	 * 获取玩法列表
	 *
	 * @author james   090808
	 * @access public
	 * @param  string   $sFields
	 * @param  string   $sCondition
	 * @param  string   $sOrderBy
	 * @param  int      $iPageRecord
	 * @param  int      $iCurrentPage
	 * @return array
	 */
	function & methodOneGetList( $sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
	{
	    $sFields    = empty($sFields) ? "*" : daddslashes($sFields);
        if( $iPageRecord == 0 )
        {//不分页显示
            $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
            $sSql       = "SELECT ".$sFields." FROM `method` ".$sCondition." ".$sOrderBy;
            return $this->oDB->getAll( $sSql );
        }
        else 
        {
            $sCondition = empty($sCondition) ? "1" : $sCondition;
            return $this->oDB->getPageResult( 'method', $sFields, $sCondition, $iPageRecord, $iCurrentPage, 
                                               $sOrderBy );
        }
	}



	/**
	 * 玩法更新
	 *
	 * @param array $aOldMethod
	 * @param string $sCondition
	 */
	function methodUpdate( $aOldMethod, $sCondition ="1=0" )
	{
		if( !isset($aOldMethod) || empty($aOldMethod) )
		{
			return -1;
		}
		if(isset($aOldMethod["lotteryid"]))
		{
			if(!is_numeric($aOldMethod["lotteryid"]))
			{ //彩种类型错误
				return -2;
			}
			$aMethod["lotteryid"] = intval($aOldMethod["lotteryid"]);
			if( $aMethod["lotteryid"] <=0 )
			{ //彩种类型错误
				return -2;
			}
		}
		if( isset($aOldMethod["methodname"]) )
		{		
			if( empty($aOldMethod["methodname"]) )
			{ //彩种名称为空
				return -3;
			}
			$aMethod["methodname"] = daddslashes( $aOldMethod["methodname"] );
		}
		if(isset($aOldMethod["functionname"]))
		{
			if( empty($aOldMethod["functionname"]) )
			{ //彩种的中奖函数名称为空
				return -4;
			}
			$aMethod["functionname"] = daddslashes( $aOldMethod["functionname"] );
		}
		if(isset($aOldMethod["isprizedynamic"]))
		{
			$aMethod["isprizedynamic"] = isset($aOldMethod["isprizedynamic"]) ? 1: 0;
		}
		$aMethod["locksid"] = intval($aOldMethod["locksid"]);
		if(isset($aOldMethod["level"]))
		{
			if( empty($aOldMethod["level"]) || !is_numeric($aOldMethod["level"]) )
			{ // 奖级个数错误
				return -6;
			}
			$aMethod["level"] = intval( $aOldMethod["level"] );
			if($aMethod["level"]<=0)
			{
				return -6;
			}
			$aMethod["count"] =array();
			for($i = 1;$i<=$aMethod["level"];$i++)
			{
				if(!is_numeric($aOldMethod["count"][$i]["count"]))
				{ // 转直注数错误
					return -7;
				}
				$aMethod["count"][$i]["count"] = intval($aOldMethod["count"][$i]["count"]);
				$aMethod["count"][$i]["name"] = $aOldMethod["count"][$i]["name"];
				if(isset($aOldMethod["count"][$i]["use"]))
				{
					$aMethod["count"][$i]["use"] = 1;
				}
			}
			$aMethod["count"]["type"] = isset($aOldMethod["count"]["type"])&&is_numeric($aOldMethod["count"]["type"])?1:0;
			$aMethod["count"]["isdesc"] = isset($aOldMethod["count"]["isdesc"])&&is_numeric($aOldMethod["count"]["isdesc"])?1:0;
			$aMethod["nocount"] = serialize($aMethod["count"]);
			unset($aMethod["count"]);
		}
		if(isset($aOldMethod["description"]))
		{
			$aMethod["description"] = daddslashes( $aOldMethod["description"] );
		}
		if(isset($aOldMethod["areatype"]))
		{
			$aMethod["areatype"] = base64_encode( serialize(stripslashes_deep($aOldMethod["areatype"])) );
		}
		return $this->oDB->update( 'method', $aMethod, $sCondition );
	}
}
?>