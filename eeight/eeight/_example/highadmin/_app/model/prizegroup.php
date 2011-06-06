<?php
/**
 * 数据模型: 奖金组
 * 
 * @author     Mark
 * @version    1.0.0
 * @package    highadmin
 * Tom 效验通过于 0208 16:03
 */
class model_prizegroup extends basemodel
{
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}


	/**
	 * 获取奖组信息列表
	 *
	 * @param string $sFields
	 * @param string $sCondition
	 * @param string $sOrderBy
	 * @param integer $iPageRecord
	 * @param integer $iCurrentPage
	 * @return array
	 * @author mark
	 * Tom 效验通过于 0225 14:02
	 */
	function pgGetList($sFields = '', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0)
	{
		if( empty($sFields) )
		{
			$sFields = "*";
		}
		if( empty($sCondition) )
		{
			$sCondition = " 1 ";
		}
		$iPageRecord = is_numeric($iPageRecord) ? intval($iPageRecord) : 0;
		if( $iPageRecord <= 0 )
		{
			$iPageRecord = 0;
		}
		if( $iPageRecord == 0 )
		{ // 返回: 获取结果集数组
			if( !empty($sOrderBy) )
			{
				$sOrderBy =" ORDER BY ".$sOrderBy;
			}
			return $this->oDB->getAll("SELECT ".$sFields."FROM `prizegroup` WHERE ".$sCondition. $sOrderBy);
		}
		// 返回: 默认分页数据
		return $this->oDB->getPageResult("prizegroup", $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy);
	}



	/**
	 * 增加奖金组
	 *
	 * @param array $aOldPrizeGroup
	 * @return integer
	 * @author mark
	 * Tom 效验通过于 0225 14:21
	 */
	function pgInsert( $aOldPrizeGroup )
	{
		if( !isset($aOldPrizeGroup) || empty($aOldPrizeGroup) )
		{ //数据不完整
			return 0;
		}
		if( !isset($aOldPrizeGroup["lotteryid"]) || !is_numeric($aOldPrizeGroup["lotteryid"]) )
		{ // 彩种ID错误
			return -1;
		}
		$iLotteryid = intval($aOldPrizeGroup["lotteryid"]);
		$aLottery   = $this->oDB->getOne("SELECT * FROM `lottery` WHERE `lotteryid`='".$iLotteryid."'" . ' LIMIT 1');
		if( empty($aLottery) )
		{ //彩种不存在
			return -2;
		}
		$aPrizeGroup = array();
		$aPrizeGroup["lotteryid"] = $iLotteryid;
		if( empty($aOldPrizeGroup["groupname"]) )
		{ //彩种名称不存在
			return -3;
		}
		$aPrizeGroup["title"]  = daddslashes($aOldPrizeGroup["groupname"]);
		$aPrizeGroup["status"] = 1; // 默认: 奖金组的内容需要审核
		$aMethod = $this->oDB->getAll("SELECT `methodid`,`level`,`nocount`,`totalmoney` FROM `method`"
				." WHERE `lotteryid`='".$iLotteryid."' AND `pid`='0'");
		//print_rr($aOldPrizeGroup);exit;
        //print_rr($aMethod);exit;
		foreach( $aMethod as $method )
		{ //检查方案设置
			$method["nocount"] = @unserialize($method["nocount"]);
			if( $method["level"] == 1 )
			{ // 单奖级的计算验证
				if( !isset($aOldPrizeGroup["prize"][$method["methodid"]][1]) 
				    || !is_numeric($aOldPrizeGroup["prize"][$method["methodid"]][1]) )
				{ // -4 : 奖金错误
					return -4;
				}
				$aNewPrizeLevel[$method["methodid"]]["prize"][1] 
					= number_format( $aOldPrizeGroup["prize"][$method["methodid"]][1], 2 , ".", ""); 

				if( !isset($aOldPrizeGroup["userpoint"][$method["methodid"]]) 
				    || !is_numeric($aOldPrizeGroup["userpoint"][$method["methodid"]]) )
				{ // -5 : 返点设置错误
					return -5;
				}
				$aNewPrizeLevel[$method["methodid"]]["userpoint"] 
					= number_format( $aOldPrizeGroup["userpoint"][$method["methodid"]], 3, ".", "" );

				//奖金描述(玩法->等级)
				$aNewPrizeLevel[$method["methodid"]]["description"][1]
				  = isset($aOldPrizeGroup["description"][$method["methodid"]][1])
				  	? $aOldPrizeGroup["description"][$method["methodid"]][1]
				  	: "";
				$lastpoint = ($method["totalmoney"] - $method["nocount"][1]['count']*$aNewPrizeLevel[$method["methodid"]]["prize"][1])/$method["totalmoney"] - $aNewPrizeLevel[$method["methodid"]]["userpoint"]; 
				if( $lastpoint < $aLottery["minprofit"] )
				{ // -6 : 公司最小留水计算错误
					return -6;
				}
			}
			else
			{ // 多奖金的计算验证
				for( $i=1; $i<=$method["level"]; $i++ )
				{
					if( !isset($aOldPrizeGroup["prize"][$method["methodid"]][$i]) 
					   || !is_numeric($aOldPrizeGroup["prize"][$method["methodid"]][$i]) )
					{ // 奖金错误
						return -4;
					}
					$aNewPrizeLevel[$method["methodid"]]["prize"][$i] 
						= number_format( $aOldPrizeGroup["prize"][$method["methodid"]][$i],  2, ".", ""  ); 
				}
				if( !isset($aOldPrizeGroup["userpoint"][$method["methodid"]]) 
				    || !is_numeric($aOldPrizeGroup["userpoint"][$method["methodid"]]) )
				{ // 返点设置错误
					return -5;
				}
				$aNewPrizeLevel[$method["methodid"]]["userpoint"] 
					= number_format( $aOldPrizeGroup["userpoint"][$method["methodid"]],  3, ".", ""  );
				if( isset($method['nocount']['type'])&&($method['nocount']['type']==1) )
				{ // 奖金累加型模型计算公司留水
					$moneys = 0;
					$level = $method['level'];
					for( $i=1; $i<=$level; $i++ )
					{
						$moneys = $moneys+$aNewPrizeLevel[$method["methodid"]]["prize"][$i]*$method['nocount'][$i]['count'];	
						//奖金描述
						$aNewPrizeLevel[$method["methodid"]]["description"][$i]	
					= isset($aOldPrizeGroup["description"][$method["methodid"]][$i])
						? $aOldPrizeGroup["description"][$method["methodid"]][$i]
						: "";
					}
					$lastpoint = ($method["totalmoney"] - $moneys)/$method["totalmoney"] - $aNewPrizeLevel[$method["methodid"]]["userpoint"]; 
					if($lastpoint < $aLottery["minprofit"])
					{ //公司最小留水计算错误
						return -6;	
					}
				}
				else
				{ //奖金非累加模型计算公司留水
					for( $i=1; $i<=$method["level"]; $i++ )
					{
						$lastpoint = ($method["totalmoney"] - $method["nocount"][$i]['count']*$aNewPrizeLevel[$method["methodid"]]["prize"][$i])
								/$method["totalmoney"] - $aNewPrizeLevel[$method["methodid"]]["userpoint"]; 
						if( $lastpoint < $aLottery["minprofit"] )
						{ //公司最小留水计算错误
							return -6;
						}
						//奖金描述
						$aNewPrizeLevel[$method["methodid"]]["description"][$i]	
							= isset($aOldPrizeGroup["description"][$method["methodid"]][$i])
								? $aOldPrizeGroup["description"][$method["methodid"]][$i]
								: "";
					}
				}
			}
		}
		$this->oDB->doTransaction();
		$iPrizeGroupId = $this->oDB->insert( 'prizegroup',$aPrizeGroup );
		if( $iPrizeGroupId < 0 )
		{
			$this->oDB->doRollback();
			return -7;
		}
		foreach( $aMethod as $method )
		{
			$prizelevel = array();
			for( $i=1; $i<=$method['level']; $i++ )
			{
				$prizelevel["prizegroupid"] = $iPrizeGroupId;
				$prizelevel['methodid']     = $method["methodid"];
				$prizelevel["level"]        = $i;
				$prizelevel["prize"]        = $aNewPrizeLevel[$method["methodid"]]['prize'][$i];
				$prizelevel["userpoint"]    = $aNewPrizeLevel[$method["methodid"]]["userpoint"];
				$prizelevel['isclose']      = isset($_POST["methodid"])&&in_array($method["methodid"],$_POST["methodid"])?0:1;
				$prizelevel["description"]  = $aNewPrizeLevel[$method["methodid"]]["description"][$i];
				$iResult = $this->oDB->insert('prizelevel',$prizelevel);
				if( $iResult < 0 )
				{
					$this->oDB->doRollback();
					return -8;
				}
			}
		}
		$this->oDB->doCommit();
		return $iPrizeGroupId;
	}




	/**
	 * 获取单条奖组信息
	 *
	 * @param string $sFilds
	 * @param string $sCondition
	 * @return array
	 * @author mark
	 */
	function pgGetOne($sFilds, $sCondition )
	{
		if(empty($sFilds))
		{
			$sFilds = "*";
		}
		if(empty($sCondition))
		{
			$sCondition =" 1 ";
		}
		return $this->oDB->getOne("SELECT ".$sFilds." FROM `prizegroup` WHERE ".$sCondition . ' LIMIT 1');
	}



	/**
	 * 更新一个奖金组
	 * @author mark	
	 * @param  array   $aOldPrizeGroup
	 * @param  integer $iPrizeGroupId
	 * @return integer
	 */
	function pgUpdate( $aOldPrizeGroup, $iPrizeGroupid )
	{
		if(!isset($aOldPrizeGroup)|| empty($aOldPrizeGroup))
		{ //数据不完整
			return -1;
		}
		if(!is_numeric($iPrizeGroupid))
		{ //数据不正确
			return -2;
		}
		$aPrizeGroup = $this->pgGetOne('*',"`prizegroupid`='".$iPrizeGroupid."'");
		if(empty($aPrizeGroup))
		{ //数据不存在
			return -3;
		}
		if($aPrizeGroup["lotteryid"]!=$aOldPrizeGroup["lotteryid"])
		{ //数据错误
			return -4; 
		}
		$iLotteryid = intval($aOldPrizeGroup["lotteryid"]);
		$aLottery = $this->oDB->getOne("SELECT * FROM `lottery` WHERE `lotteryid`='".$iLotteryid."'" . ' LIMIT 1');
		if(empty($aLottery))
		{ //彩种不存在
			return -5;
		}
		$aMethod = $this->oDB->getAll("SELECT `methodid`,`level`,`nocount`,`totalmoney` FROM `method` WHERE `lotteryid`='".$aPrizeGroup["lotteryid"]."' and `pid`='0'");
		foreach($aMethod as $method)
		{ //检查方案设置
			$method["nocount"] = @unserialize($method["nocount"]);			
			if($method["level"] == 1)
			{ // 单奖级的计算验证
				if(!isset($aOldPrizeGroup["prize"][$method["methodid"]][1]) 
				|| !is_numeric($aOldPrizeGroup["prize"][$method["methodid"]][1]) )
				{ // 奖金错误
					return -6;
				}
				$aNewPrizeLevel[$method["methodid"]]["prize"][1] 
					= number_format( $aOldPrizeGroup["prize"][$method["methodid"]][1], 2, ".", "" ); 
				if(!isset($aOldPrizeGroup["userpoint"][$method["methodid"]]) 
				|| !is_numeric($aOldPrizeGroup["userpoint"][$method["methodid"]]))
				{ // 返点设置错误
					return -7;
				}
				$aNewPrizeLevel[$method["methodid"]]["userpoint"] 
					= number_format( $aOldPrizeGroup["userpoint"][$method["methodid"]],  3, ".", ""  );
				$lastpoint = ($method["totalmoney"] - $method["nocount"][1]['count']*$aNewPrizeLevel[$method["methodid"]]["prize"][1])/$method["totalmoney"] - $aNewPrizeLevel[$method["methodid"]]["userpoint"]; 
				if($lastpoint < $aLottery["minprofit"])
				{ //公司最小留水计算错误
					return -8;
				}
				//奖金描述(玩法->等级)
				$aNewPrizeLevel[$method["methodid"]]["description"][1]
				  = isset($aOldPrizeGroup["description"][$method["methodid"]][1])
				  	?$aOldPrizeGroup["description"][$method["methodid"]][1]
				  	:"";
			}
			else
			{ // 多奖金的计算验证
				for($i=1;$i<=$method["level"];$i++)
				{
					if(!isset($aOldPrizeGroup["prize"][$method["methodid"]][$i]) 
					|| !is_numeric($aOldPrizeGroup["prize"][$method["methodid"]][$i]) )
					{ // 奖金错误
						return -6;
					}
					$aNewPrizeLevel[$method["methodid"]]["prize"][$i] 
						= number_format( $aOldPrizeGroup["prize"][$method["methodid"]][$i], 2, ".","" ); 
					//奖金描述(玩法->等级)
					$aNewPrizeLevel[$method["methodid"]]["description"][$i]
				  	= isset($aOldPrizeGroup["description"][$method["methodid"]][$i])
				  		?$aOldPrizeGroup["description"][$method["methodid"]][$i]
				  		:"";
				}
				if(!isset($aOldPrizeGroup["userpoint"][$method["methodid"]]) 
				|| !is_numeric($aOldPrizeGroup["userpoint"][$method["methodid"]]))
				{ // 返点设置错误
					return -7;
				}
				$aNewPrizeLevel[$method["methodid"]]["userpoint"] 
					= number_format( $aOldPrizeGroup["userpoint"][$method["methodid"]], 3, ".", "" );
				if(isset($method['nocount']['type'])&&($method['nocount']['type']==1))
				{//兼中兼得模型计算公司留水
					$moneys = 0;
					$level = $method['level'];
					for($i=1;$i<=$level;$i++)
					{
						$moneys = $moneys+$aNewPrizeLevel[$method["methodid"]]["prize"][$i]*$method['nocount'][$i]['count'];	
					}
					$lastpoint = ($method["totalmoney"] - $moneys)/$method["totalmoney"] - $aNewPrizeLevel[$method["methodid"]]["userpoint"]; 
					if($lastpoint < $aLottery["minprofit"])
					{ //公司最小留水计算错误
						return -8;
					}
				}
				else
				{//非兼中兼得模型计算公司留水
					for($i=1;$i<=$method["level"];$i++)
					{
						$lastpoint = ($method["totalmoney"] - $method["nocount"][$i]['count']*$aNewPrizeLevel[$method["methodid"]]["prize"][$i])/$method["totalmoney"] - $aNewPrizeLevel[$method["methodid"]]["userpoint"]; 
						if($lastpoint < $aLottery["minprofit"])
						{ //公司最小留水计算错误
							return -8;
						}
					}
				}
			}
		} //下面进行更新,需要注意的地方是 ：方案名称修改了,不影响审核状态
		if(empty($aOldPrizeGroup['groupname']))
		{
			return -9;
		}
		$aPrizeGroup["title"] = $aOldPrizeGroup["groupname"];
		$this->oDB->doTransaction();
		foreach($aMethod as $method)
		{
			for($i=1;$i<=$method['level'];$i++)
			{
				$sqlCheck = "SELECT `prizeid` FROM `prizelevel` WHERE `prizegroupid`='".$iPrizeGroupid."' AND"
				." `methodid`='".$method["methodid"]."' AND `level`='".$i."'" . ' LIMIT 1';
				$aRs = $this->oDB->getOne($sqlCheck);
				if(empty($aRs))
				{
					$prizelevel = array();
					$prizelevel["prizegroupid"] = $iPrizeGroupid;
					$prizelevel['methodid'] = $method["methodid"];
					$prizelevel["level"] = $i;
					$prizelevel["prize"] = $aNewPrizeLevel[$method["methodid"]]['prize'][$i];
					$prizelevel["userpoint"] = $aNewPrizeLevel[$method["methodid"]]["userpoint"];
					$prizelevel['isclose'] = isset($_POST["methodid"])&&in_array($method["methodid"],$_POST["methodid"])?0:1;
					$prizelevel["description"] =  $aNewPrizeLevel[$method["methodid"]]["description"][$i];
					$iResult = $this->oDB->insert('prizelevel',$prizelevel);
					if($iResult<=0)
					{ //插入时候失败
						$this->oDB->doRollback();
						return -10;
					}
					else
					{
						if(empty($aPrizeGroup["topproxy"]))
						{
							$aPrizeGroup['status']= 1;
						}
						else
						{
							$aPrizeGroup['status']= 3;
						}
					}
				}
				else
				{	//存在
					$prizelevel["prizegroupid"] = $iPrizeGroupid;
					$prizelevel['methodid'] = $method["methodid"];
					$prizelevel["level"] = $i;
					$prizelevel["prize"] = $aNewPrizeLevel[$method["methodid"]]['prize'][$i];
					$prizelevel["userpoint"] = $aNewPrizeLevel[$method["methodid"]]["userpoint"];
					$prizelevel['isclose'] = isset($_POST["methodid"])&&in_array($method["methodid"],$_POST["methodid"])?0:1;
					$prizelevel["description"] =  $aNewPrizeLevel[$method["methodid"]]["description"][$i];
					$iResult = $this->oDB->update('prizelevel',$prizelevel,"`prizeid`='".$aRs["prizeid"]."'");
					if($iResult>0)
					{
						if(empty($aPrizeGroup["topproxy"]))
						{
							$aPrizeGroup['status']= 1;
						}
						else
						{
							$aPrizeGroup['status']= 3;
						}
					}
					if($iResult<0)
					{ //更新数据时候失败
						$this->oDB->doRollback();
						return -11;
					}
				}
			}
		}
		$iRs = $this->oDB->update('prizegroup', $aPrizeGroup, "`prizegroupid`='".$iPrizeGroupid."'");
		if($iRs < 0)
		{ //更新奖组时失败
			$this->oDB->doRollback();
			return -12;
		}
		$this->oDB->doCommit();
		return 1;
	}



	/**
	 * 对奖组进行授权
	 *
	 * @param integer $iPrizegroup
	 * @param array $aUser
	 * @return integer
	 * @author mark
	 */
	function userAuth($iPrizegroup,$aUser)
	{
		$iPrizegroup = isset($iPrizegroup) && is_numeric($iPrizegroup) ? intval($iPrizegroup) : 0;
		if($iPrizegroup <= 0)
		{
			return -1;
		}
		foreach($aUser as $iUser=>$vUser)
		{
			if(!is_numeric($vUser))
			{
				unset($aUser[$iUser]);
			}
		}
		if(!empty($aUser))
		{
			$sUser = join(",",$aUser);
			$aUsers = $this->oDB->getAll("SELECT `userid` FROM `usertree` WHERE `userid` IN (".$sUser.") AND `parentid`='0'");
			$a = array();
			foreach($aUsers as $User)
			{
				$a[] = $User["userid"]; 
			}
			$aTUser = array_unique($a);
			$aPg["topproxy"] = join(",",$aTUser);
		}
		else
		{
			$aPg["topproxy"] ="";
		}
		$iResult = $this->oDB->update("prizegroup",$aPg,"`prizegroupid`='".$iPrizegroup."'");
		if($iResult>0)
		{
			//需要保留原始数据
		      $this->oDB->query("UPDATE `prizegroup` SET `status`='0' WHERE `prizegroupid`='".$iPrizegroup."' AND (`topproxy`='') AND (`status`='0' OR `status`='2')");
		      $this->oDB->query("UPDATE `prizegroup` SET `status`='1' WHERE `prizegroupid`='".$iPrizegroup."' AND (`topproxy`='') AND (`status`='1' OR `status`='3')");
		      $this->oDB->query("UPDATE `prizegroup` SET `status`='2' WHERE `prizegroupid`='".$iPrizegroup."' AND (`topproxy`!='') AND (`status`='0' OR `status`='2')");
		      $this->oDB->query("UPDATE `prizegroup` SET `status`='3' WHERE `prizegroupid`='".$iPrizegroup."' AND (`topproxy`!='') AND (`status`='1' OR `status`='3')");	      
		}
		return $iResult;
	}




	/**
	 * 审核奖金组同时根据奖金组同步至总代奖金组
	 *
	 * @param integeer $iPrizeGroupid
	 * @return integer
	 * @author mark
	 */
	function pgVerifity($iPrizeGroupid)
	{
		if(!is_numeric($iPrizeGroupid))
		{ //数据不正确
			return -1;
		}
		$this->oDB->query("UPDATE `prizegroup` SET `status`='4' WHERE `prizegroupid`='".$iPrizeGroupid."' AND `status` IN (1,2,3)");
		if ($this->oDB->errno()>0)
		{
		    return -3;		
		}
		$aPrizeGroup = $this->oDB->getOne("SELECT * FROM `prizegroup` WHERE `prizegroupid`='".$iPrizeGroupid."'" . ' LIMIT 1');
		if(empty($aPrizeGroup))
		{ //奖组不存在
			return -2;
		}
		if($aPrizeGroup["status"]!=4)
		{// 奖组状态不正确
			return -3;
		}
		//需要同步的用户
		$users = explode(",",$aPrizeGroup['topproxy']);
		//获取奖组信息
		$aPrizeLevel = $this->oDB->getAll("SELECT * FROM `prizelevel` WHERE `prizegroupid`='".$iPrizeGroupid."'");
		//启动事务
		$this->oDB->doTransaction();
		//更新状态
		foreach($users as $user)
		{
			if(is_numeric($user)&&intval($user)>0)
			{
				$aUserPrize = $this->oDB->getOne("SELECT * FROM `userprizegroup` WHERE `userid`='".$user."' AND `pgid`='".$iPrizeGroupid."'" . ' LIMIT 1');
				if(empty($aUserPrize))
				{//新加用户的需要插入
					//用户奖组信息表
					$aUserPrizeGroup = array();
					$aUserPrizeGroup['status'] = 0;
					$aUserPrizeGroup['lotteryid'] = $aPrizeGroup["lotteryid"];
					$aUserPrizeGroup['title'] = $aPrizeGroup['title'];
					$aUserPrizeGroup['userid'] = $user;
					$aUserPrizeGroup['pgid'] = $iPrizeGroupid;
					$iResult = $this->oDB->insert('userprizegroup',$aUserPrizeGroup);
					if($iResult === FALSE)
					{
						$this->oDB->doRollback();
						return -5;
					}
					$iUserPizegroup = $iResult;
					foreach($aPrizeLevel as $prizelevel)
					{
					    $aUserPrizeLevel = array();
						$aUserPrizeLevel['userpgid']=$iUserPizegroup;
						$aUserPrizeLevel['methodid']=$prizelevel['methodid'];
						$aUserPrizeLevel['level']= $prizelevel['level'];
						$aUserPrizeLevel['prize']=number_format($prizelevel['prize'],2,".","");
						$aUserPrizeLevel['userpoint']=number_format($prizelevel['userpoint'],3,".","");
						$aUserPrizeLevel['isclose']= 1; //新加用户奖金组信息的需要手工激活
						$aUserPrizeLevel['plid']=$prizelevel['prizeid'];
						$aUserPrizeLevel['description'] = $prizelevel["description"];
						$iResult = $this->oDB->insert('userprizelevel',$aUserPrizeLevel);
						if($iResult === FALSE)
						{
							$this->oDB->doRollback();
							return -6;
						}
					}
				}
				else
				{ //用户的需要更新
				    $aUserPrizeGroup = array();
					$aUserPrizeGroup['lotteryid'] = $aPrizeGroup["lotteryid"];
					$aUserPrizeGroup['title'] = $aPrizeGroup['title'];
					$iResult = $this->oDB->update('userprizegroup',$aUserPrizeGroup,"`userpgid`='".$aUserPrize['userpgid']."'");
					if ($iResult === FALSE)
					{
						$this->oDB->doRollback();
						return -7;
					}
					foreach($aPrizeLevel as $prizelevel)
					{
						$aUserOldPL = $this->oDB->getOne("SELECT * FROM `userprizelevel` WHERE `userpgid`='".$aUserPrize['userpgid']."' AND `plid`='".$prizelevel['prizeid']."'" . ' LIMIT 1');
						if(empty($aUserOldPL))
						{ //历史奖组不存在，需要插入
						    $aUserPrizeLevel = array();
							$aUserPrizeLevel['userpgid']=$aUserPrize['userpgid'];
							$aUserPrizeLevel['methodid']=$prizelevel['methodid'];
							$aUserPrizeLevel['level']= $prizelevel['level'];
							$aUserPrizeLevel['prize']=number_format($prizelevel['prize'],2,".","");
							$aUserPrizeLevel['userpoint']= number_format($prizelevel['userpoint'],3,".","");
							$aUserPrizeLevel['isclose']= 1; //新加用户奖金组信息的需要手工激活
							$aUserPrizeLevel['description'] = $prizelevel["description"];
							$aUserPrizeLevel['plid']=$prizelevel['prizeid'];
							$iResult = $this->oDB->insert('userprizelevel',$aUserPrizeLevel);
							if($iResult === FALSE)
							{
								$this->oDB->doRollback();
								return -6;
							}
						}
						else
						{ //有历史记录的需要更新记录
						    $aUserPrizeLevel = array();			
							$aUserPrizeLevel['userpgid']=$aUserPrize['userpgid'];
							$aUserPrizeLevel['methodid']=$prizelevel['methodid'];
							$aUserPrizeLevel['level']= $prizelevel['level'];
							$aUserPrizeLevel['prize']= number_format($prizelevel['prize'],2,".","");
							$aUserPrizeLevel['userpoint']= number_format($prizelevel['userpoint'],3,".","");
							$aUserPrizeLevel['description'] = $prizelevel["description"];
							if($prizelevel["isclose"] == 0)
							{ //目标开通组取历史值
								$aUserPrizeLevel['isclose'] = $aUserOldPL['isclose'];
							}
							else
							{ //否则为关闭
								$aUserPrizeLevel["isclose"] = 1;
							}
							 // 开通状态
							if($prizelevel['userpoint']<$aUserOldPL['userpoint'])
							{
								//返点下调的需要看一代
								$sql = "SELECT `userpoint` FROM `usermethodset` WHERE `methodid`='".$prizelevel["methodid"]."' AND "
								."`prizegroupid`='".$aUserOldPL['userpgid']."' ORDER By `userpoint` DESC" . ' LIMIT 1';
								$childPoint = $this->oDB->getOne($sql);
								if(!empty($childPoint)&&$childPoint['userpoint']>$prizelevel['userpoint'])
								{
									$this->oDB->query("UPDATE `usermethodset` SET `isclose`='1' WHERE `methodid`='".$prizelevel["methodid"]."'"
									." and `prizegroupid`='".$aUserOldPL['userpgid']."'");
									if($this->oDB->errno()>0)
									{
										$this->oDB->doRollback();
										return -8;
									}
									$aUserPrizeLevel['isclose']= 1; //新加用户奖金组信息的需要手工激活
								}
							}
							$aUserPrizeLevel['plid'] = $prizelevel['prizeid'];
							$iResult = $this->oDB->update('userprizelevel',$aUserPrizeLevel,"`prizeid`='".$aUserOldPL['prizeid']."'");
							if($iResult === FALSE)
							{
								$this->oDB->doRollback();
								return -9;
							}
						}
					}
				}
			}
		}	
		$iResult = $this->oDB->query("UPDATE `prizegroup` SET `status`='0',`topproxy`='' WHERE `prizegroupid`='".$iPrizeGroupid."' AND `status`='4'");
		if($this->oDB->errno() > 0)
		{
			$this->oDB->doRollback();
			return -10;
		}
		$this->oDB->doCommit();
		return 1;
	}
}
?>
