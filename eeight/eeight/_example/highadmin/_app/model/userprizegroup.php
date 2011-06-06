<?php
/**
 * 设置总代奖金组model
 *
 */

class model_userprizegroup extends basemodel
{
	//构造函数
	function __construct( $aDBO = array() )
	{
		parent::__construct( $aDBO );
	}
	
	
	
	/**
	 * 获取总代奖金组列表
	 *
	 * @param  string   $sFields
	 * @param  string   $sCondition
	 * @param  string   $sOrderBy
	 * @param  int      $iPageRecord
	 * @param  int      $iCurrentPage
	 * @return array   //奖金组列表信息
	 * @author mark
	 */
	function userpgGetList($sFields = '*', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0)
	{
		if( empty($sFields) )
		{
			$sFields = "*";
		}
		if( empty($sCondition) )
		{
			$sCondition = "1";
		}
		$iPageRecord = isset($iPageRecord) && is_numeric($iPageRecord) ? intval($iPageRecord) : 0;
		if($iPageRecord == 0)
		{
			if($sOrderBy != '')
			{
				$sOrderBy = " ORDER BY ".$sOrderBy;
			}
			return $this->oDB->getAll("SELECT ".$sFields." FROM `userprizegroup` WHERE ".$sCondition . $sOrderBy);
		}
		else
		{
			return $this->oDB->getPageResult('userprizegroup', $sFields, $sCondition, $iPageRecord,$iCurrentPage, $sOrderBy);
		}
	}



	/**
	 * 获取一条奖金组信息
	 *
	 * @param  string   $sField
	 * @param  string   $sCondition
	 * @return array    //一条记录集
	 * @author mark
	 */
	function userpgGetOne( $sField, $sCondition = "1" )
	{
		if(empty($sField))
		{
			$sField = "*";
		}
		if(empty($sCondition))
		{
			$sCondition = "1";
		}
		return $this->oDB->getOne("SELECT  ".$sField." FROM `userprizegroup` WHERE ".$sCondition . ' LIMIT 1');
	}



	/**
	 * 修改总代的奖金组信息
	 *
	 * @param  array    $aNewUserPrizeGroup
	 * @param  int      $iUserPrizeGroupid
	 * @return int
	 * @author mark
	 */
	function userpgUpdate( $aNewUserPrizeGroup, $iUserPrizeGroupid )
	{
		if(!isset($iUserPrizeGroupid)||!is_numeric($iUserPrizeGroupid))
		{ //数据不正确
			return -1;
		}
		if(empty($aNewUserPrizeGroup))
		{
			return -1;
		}
		$sSql = "SELECT * FROM `userprizegroup` WHERE `userpgid`='".$iUserPrizeGroupid."'" . ' LIMIT 1';
		$aOldUserGroup = $this->oDB->getOne( $sSql );
		if(empty($aOldUserGroup))
		{
			return -2;
		}
		//获取对应模板
		$sSql = "SELECT * FROM `prizegroup` WHERE `prizegroupid`='".$aOldUserGroup['pgid']."'" . ' LIMIT 1';
		$aPrizeGroup = $this->oDB->getOne( $sSql );
		if(empty($aPrizeGroup))
		{ //模板不存在
			return -3;
		}
		if($aPrizeGroup["status"]!=0)
		{ //模板未审核
			return -4;
		}
		$sSql = "SELECT `minprofit`,`mincommissiongap` FROM `lottery` WHERE `lotteryid`='".$aOldUserGroup['lotteryid']."'" . ' LIMIT 1';
		$aLottery  = $this->oDB->getOne($sSql); //公司最大留水以及上下级的最小返点差
		if(empty($aLottery))
		{ //彩种不存在
			return -5;
		}
		//所有玩法组的获取
		$sSql = "SELECT `methodid`,`level`,`nocount`,`totalmoney` FROM `method` 
		          WHERE `lotteryid`='".$aOldUserGroup["pgid"]."' and `pid`='0'";
		$aMethod = $this->oDB->getAll($sSql);
		foreach($aMethod as $i=>$v)
		{
			$aMethod[$i]["nocount"] = @unserialize($v["nocount"]);
		} //验证计算
		foreach($aMethod as $i=>$v)
		{
			if( isset($aNewUserPrizeGroup["prize"][$v["methodid"]]) 
			     &&isset($aNewUserPrizeGroup["userpoint"][$v["methodid"]]) )
			{
				if($v["level"] == 1)
				{ // 单奖级的计算验证
					if(!isset($aNewUserPrizeGroup["prize"][$v["methodid"]][1]) 
					|| !is_numeric($aNewUserPrizeGroup["prize"][$v["methodid"]][1]) )
					{ // 奖金错误
						return -6;
					}
					$aNewUserPrizeGroup["prize"][$v["methodid"]][1] 
						= number_format( $aNewUserPrizeGroup["prize"][$v["methodid"]][1], 2,".",""); 
					if(!isset($aNewUserPrizeGroup["userpoint"][$v["methodid"]]) 
					|| !is_numeric($aNewUserPrizeGroup["userpoint"][$v["methodid"]]))
					{ // 返点设置错误
						return -7;
					}
					$aNewUserPrizeGroup["description"][$v["methodid"]][1] = isset($aNewUserPrizeGroup["description"][$v["methodid"]][1])
						?$aNewUserPrizeGroup["description"][$v["methodid"]][1] : "";
					$aNewUserPrizeGroup["userpoint"][$v["methodid"]]
						= number_format( $aNewUserPrizeGroup["userpoint"][$v["methodid"]],3,".","" );
				    $lastpoint = $v["nocount"][1]['count']*$aNewUserPrizeGroup["prize"][$v["methodid"]][1];
				    $lastpoint = ( $v["totalmoney"] - $lastpoint )/$v["totalmoney"];
					$lastpoint =  $lastpoint - $aNewUserPrizeGroup["userpoint"][$v["methodid"]]; 
					if($lastpoint < $aLottery["minprofit"])
					{ //公司最小留水计算错误
						return -8;
					}
				}
				else
				{ // 多奖金的计算验证
					for($i=1;$i<=$v["level"];$i++)
					{
						if(!isset($aNewUserPrizeGroup["prize"][$v["methodid"]][$i]) 
						|| !is_numeric($aNewUserPrizeGroup["prize"][$v["methodid"]][$i]) )
						{ // 奖金错误
							return -6;
						}
						$aNewUserPrizeGroup["prize"][$v["methodid"]][$i] 
							= number_format( $aNewUserPrizeGroup["prize"][$v["methodid"]][$i],2,".",""); 
						$aNewUserPrizeGroup["description"][$v["methodid"]][$i] = isset($aNewUserPrizeGroup["description"][$v["methodid"]][$i])
						?$aNewUserPrizeGroup["description"][$v["methodid"]][$i] : "";
					}
					if(!isset($aNewUserPrizeGroup["userpoint"][$v["methodid"]]) 
					|| !is_numeric($aNewUserPrizeGroup["userpoint"][$v["methodid"]]))
					{ // 返点设置错误
						return -7;
					}
					$aNewUserPrizeGroup["userpoint"][$v["methodid"]] 
						= number_format( $aNewUserPrizeGroup["userpoint"][$v["methodid"]],  3, ".", "");
					if(isset($v['nocount']['type'])&&($v['nocount']['type']==1))
					{//奖金累加型计算公司留水
						$moneys = 0;
						$level = $v['level'];
						for($i=1;$i<=$level;$i++)
						{
							$moneys = $moneys+$aNewUserPrizeGroup["prize"][$v["methodid"]][$i]*$v['nocount'][$i]['count'];
						}
						$lastpoint = ($v["totalmoney"] - $moneys)/$v["totalmoney"] - 
						              $aNewUserPrizeGroup["userpoint"][$v["methodid"]]; 
						if($lastpoint < $aLottery["minprofit"])
						{ //公司最小留水计算错误
							return -8;
						}
					}
					else
					{//非奖金累加型模型计算公司留水
						for($i=1;$i<=$v["level"];$i++)
						{
							$lastpoint = ($v["totalmoney"] - $v["nocount"][$i]['count']*$aNewUserPrizeGroup["prize"][$v["methodid"]][$i])/$v["totalmoney"] - $aNewUserPrizeGroup["userpoint"][$v["methodid"]]; 
							if($lastpoint < $aLottery["minprofit"])
							{ //公司最小留水计算错误
								return -8;
							}
						}	
					}
				}
			}
			else
			{
				unset($aNewUserPrizeGroup["prize"][$v["methodid"]],$aNewUserPrizeGroup["userpoint"][$v["methodid"]]);
			}
		}
		$aNewUserPrizeGroup["methodid"] = isset($aNewUserPrizeGroup["methodid"])&&is_array($aNewUserPrizeGroup["methodid"])?$aNewUserPrizeGroup["methodid"]:array();
		//获取所有的原始资料
		$aOldUserPrizes = $this->oDB->getAll("SELECT * FROM `userprizelevel` WHERE `userpgid`='".$iUserPrizeGroupid."'");
		$aOldUserPrize = array();
		foreach($aOldUserPrizes as $i=>$v)
		{
			$aOldUserPrize[$v["methodid"]][$v["level"]] = array("isclose"=>$v["isclose"],"userpoint"=>$v["userpoint"]);
		}
		$aUserPrizeLevel = $this->oDB->getAll("SELECT `methodid`,`level`,`prizeid` FROM `prizelevel` WHERE `prizegroupid`='".$aNewUserPrizeGroup["pgid"]."'");
		$this->oDB->doTransaction();
		foreach($aUserPrizeLevel as $i=>$v)
		{
			$aUpdate["userpoint"] = isset($aNewUserPrizeGroup["userpoint"][$v["methodid"]])?$aNewUserPrizeGroup["userpoint"][$v["methodid"]]:0.00;
			$aUpdate["prize"] = isset($aNewUserPrizeGroup["prize"][$v["methodid"]][$v["level"]])?$aNewUserPrizeGroup["prize"][$v["methodid"]][$v["level"]]:0.00;
			$aUpdate["isclose"] = in_array($v["methodid"],$aNewUserPrizeGroup["methodid"]) ? 0 : 1;
			$aUpdate["description"] = "";
			if($aUpdate["isclose"]==0) //开启
			{ // 如果玩法没有同步给总代
				if(isset($aOldUserPrize[$v["methodid"]]))
				{
					if($aUpdate["userpoint"] <$aOldUserPrize[$v["methodid"]][$v["level"]]["userpoint"])
					{
						$aCheck = $this->oDB->getOne("SELECT `userpoint` FROM `usermethodset` WHERE `methodid`='".$v["methodid"]."' and `prizegroupid`='".$iUserPrizeGroupid."' order by `userpoint` DESC LIMIT 1");
						if(!empty($aCheck))//有下级的时候的情况
						{
							if($aCheck["userpoint"]+$aLottery["mincommissiongap"]>$aUpdate["userpoint"])
							{// 总代的返点比下级用户的最大返点+ 返点差 小
								$this->oDB->doRollback();
								return -10;
							}
						}
					}
				}
			}
			$iResult = $this->oDB->update("userprizelevel",$aUpdate,"`methodid`='".$v["methodid"]."' and `level`='".$v["level"]."' and `userpgid`='".$iUserPrizeGroupid."'");
			if($iResult === FALSE )
			{ //更新奖金组详情时候失败
				$this->oDB->doRollback();
				return -9;
			}
			unset($aUpdate);
		}
		$this->oDB->query("UPDATE `userprizegroup` SET `title`='".$aNewUserPrizeGroup["title"]."',`status`='0' WHERE `userpgid`='".$iUserPrizeGroupid."'");
		if($this->oDB->errno()>0)
		{ //
			$this->oDB->doRollback();
			return -11;
		}
		$this->oDB->doCommit();
		return 1;
	}



	/**
	 * 审核奖金组
	 *
	 * @param  array $aPgid 总代奖金组ID
	 * @return int
	 * @author mark
	 */
	function userpgVerifity( $aPgid )
	{
		foreach($aPgid as $iKey => $aValue)
		{
			if(!is_numeric($aValue))
			{
				unset($aPgid[$iKey]);
			}
		}
		if(!empty($aPgid))
		{
			$this->oDB->query("UPDATE `userprizegroup` SET `status`='1' WHERE `userpgid` IN (".join(",",$aPgid).")");
			if($this->oDB->errno() > 0)
			{
				return -1;
			}
			else
			{
				return $this->oDB->ar();
			}
		}
		else
		{
			return 0;
		}
	}



	/**
	 * 禁用总代奖金组
	 *
	 * @param integer $iPgid
	 * @return integer
	 * @author mark
	 */
	function userPrizegroupstop($iPgid)
	{
		if(!is_numeric($iPgid))
		{
			return 0;
		}
		$aUserPrize = $this->oDB->getOne("SELECT * FROM `userprizegroup` WHERE `userpgid`='".$iPgid."'" . ' LIMIT 1');
		if(empty($aUserPrize))
		{ //用户奖组没有找到
			return -1;
		}
		if($aUserPrize["status"] == 0)
		{ //用户奖组本来就是禁用
			return -2;
		}
		$this->oDB->doTransaction();
		$this->oDB->query("UPDATE `userprizelevel` SET `isclose`='1' WHERE `userpgid`='".$iPgid."'");
		if($this->oDB->error() > 0)
		{
			$this->oDB->doRollback();
			return -3;
		}
		$this->oDB->query("UPDATE `userprizegroup` SET `status`='0' WHERE `userpgid`='".$iPgid."' AND `status` != '0'"); //关闭自身
		if($this->oDB->errno() > 0)
		{
			$this->oDB->doRollback();
			return -4;
		}
		$this->oDB->doCommit();
		return 1;
	}



	/**
	 * 根据用户获取奖金组及其详细的奖金详情
	 *
	 * @author mark
	 * @access public
	 * @param  int     $iUserId //用户ID
	 * @param  boolean $bIsTop  //是否为总代
	 * @param  string  $sFields //要查询的内容
	 * @param  string  $sAndWHERE  //附加查询条件
	 */
	public function & getUserPrizeGroupList( $iUserId, $bIsTop = FALSE, $sFields = "", $sAndWHERE = "" )
	{
		$aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {
            return $aResult;
        }
        $iUserId = intval($iUserId);
        if( $bIsTop == TRUE )
        {//如果为总代则结合总代奖金组设置表
        	$sFields = empty($sFields) ? 
        	           " upg.`userpgid`,upg.`title`,upg.`lotteryid`,upl.`prize`,upl.`userpoint`,upl.`level`,
        	           m.`methodname`,m.`methodid`,m.`nocount` "
        	            : daddslashes($sFields);
        	$sSql = " SELECT ".$sFields."
        	          FROM `userprizegroup` AS upg 
        	          LEFT JOIN `userprizelevel` AS upl ON upg.`userpgid`=upl.`userpgid` 
        	          LEFT JOIN `method` AS m ON upl.`methodid`=m.`methodid` 
        	          WHERE upg.`status`='1' AND upg.`userid`='".$iUserId."' 
        	          AND upl.`isclose`='0' AND m.`isclose`='0' ".$sAndWHERE;
        }
        else 
        {//普通代理为结合用户玩法设置表
        	$sFields = empty($sFields) ? 
        	           " upg.`userpgid`,upg.`title`,upg.`lotteryid`,upl.`prize`,ums.`userpoint`,upl.`level`,
        	           m.`methodname`,m.`methodid`,m.`nocount` "
                       : daddslashes($sFields);
        	$sSql = " SELECT ".$sFields."
                      FROM `userprizegroup` AS upg 
                      LEFT JOIN `usermethodset` AS ums ON upg.`userpgid`=ums.`prizegroupid`
                      LEFT JOIN `userprizelevel` AS upl ON 
                      (upl.`userpgid`=upg.`userpgid`  AND upl.`methodid`=ums.`methodid`)
                      LEFT JOIN `method` AS m ON upl.`methodid`=m.`methodid` 
                      WHERE upg.`status`='1' AND ums.`userid`='".$iUserId."' AND ums.`isclose`='0' 
                      AND upl.`isclose`='0' AND m.`isclose`='0' ".$sAndWHERE." ";
        }
        return $this->oDB->getAll($sSql);
	}



	/**
	 * 对奖金同步状态进行检查
	 *
	 * @param  int $iPrizeGroup
	 * @return array
	 * @author mark
	 */
	function checkStatus($iPrizeGroup)
	{
		$iPrizeGroup = intval($iPrizeGroup);
		return $this->oDB->getAll("SELECT UPG.`userid` FROM "
			."`userprizelevel` AS UL"
			." LEFT JOIN `userprizegroup` AS UPG ON(UL.`userpgid`=UPG.`userpgid`)"
			." RIGHT JOIN `prizelevel` AS PL ON(UL.`plid`=PL.`prizeid`)"
			." WHERE  UPG.`pgid`='".$iPrizeGroup."'"
			." GROUP BY UL.`userpgid`,UPG.`userid`"
			." HAVING COUNT(UL.`userpgid`)=SUM(PL.`level`=UL.`level` AND PL.`prize`=UL.`prize` AND PL.`userpoint`=UL.`userpoint`)");
	}
}
?>
