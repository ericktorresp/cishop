<?php
/**
 * 封锁表名称管理以及封锁表设置
 *
 */
class model_locksname extends basemodel
{
	function __construct($aDBO= array())
	{
		parent::__construct($aDBO);
	}
	
	/**
	 * 新加封锁表
	 *
	 * @param array $aLocksName
	 * @return integer
	 */
	function locksnameInsert( $aLocksName )
	{
		if(!isset($aLocksName)|| empty($aLocksName))
		{ //数据不正确
			return -1;
		}
		if(empty($aLocksName["lockname"]))
		{ //封锁表名称为空
			return -2;
		}
		$aLocks["lockname"] = daddslashes($aLocksName["lockname"]);
		if(!is_numeric($aLocksName["lotteryid"]))
		{ //彩种信息错误
			return -3;
		}
		$aLottery = $this->oDB->getOne("SELECT * FROM `lottery` where `lotteryid`='".$aLocksName["lotteryid"]."'");
		if(empty($aLottery))
		{ //彩种信息不存在
			return -4;
		}
		$aLocks["lotteryid"] = intval($aLocksName["lotteryid"]);
		if(!is_numeric($aLocksName["maxlost"]))
		{ //封锁表封锁值错误
			return -5;
		}
		$aLocks["maxlost"] = number_format($aLocksName["maxlost"],2,".","");
		$aLockExist = $this->oDB->getOne("SELECT * FROM `locksname` where `lockname`='".$aLocks["lockname"]."'");
		if(!empty($aLockExist))
		{ //封锁表名称重复
			return -6;
		}
		return $this->oDB->insert('locksname',$aLocks);
	}
	
	/**
	 * 更新封锁表
	 *
	 * @param array $aLocksName
	 * @param string $sCondition
	 * @return integer
	 */
	function locksnameUpdate( $aLocksName, $sCondition )
	{
		if(!isset($aLocksName)|| empty($aLocksName))
		{ //数据不正确
			return -1;
		}
		if(isset($aLocksName["lockname"]))
		{
			if(empty($aLocksName["lockname"]))
			{ //封锁表名称错误
				return -2;
			}
			$aLocks["lockname"] = daddslashes($aLocksName["lockname"]);
		}
		if(isset($aLocksName["lotteryid"]))
		{
			if(!is_numeric($aLocksName["lotteryid"]))
			{ // 彩种ID 错误
				return -3;
			}
			$aLottery = $this->oDB->getOne("SELECT * FROM `lottery` where `lotteryid`='".$aLocksName["lotteryid"]."'");
			if(empty($aLottery))
			{ // 彩种信息不存在
				return -4;
			}
			$aLocks["lotteryid"] = intval($aLocksName["lotteryid"]);
		}
		if(isset($aLocksName["maxlost"]))
		{
			if(!is_numeric($aLocksName["maxlost"]))
			{ //封锁值错误
				return -5;
			}
			$aLocks["maxlost"] = number_format($aLocksName["maxlost"],2,".","");
		}
		if(empty($sCondition))
		{
			$sCondition = "1";
		}
		$aLockExist = $this->oDB->getOne("SELECT * FROM `locksname` where `lockname`='".$aLocks["lockname"]."' and not( ".$sCondition." )");
		if(!empty($aLockExist))
		{ //封锁表名称重复
			return -6;
		}
		
		return $this->oDB->update('locksname',$aLocks,$sCondition);
	}
	
	/**
	 * 获取封锁表信息
	 *
	 * @param string $sField
	 * @param string $sCondition
	 * @return array
	 */
	function locksnamegetAll( $sField, $sCondition )
	{
		if(empty($sField))
		{
			$sField = "*";
		}
		if(empty($sCondition))
		{
			$sCondition = "1";
		}
		return $this->oDB->getAll("SELECT ".$sField." FROM `locksname` where ".$sCondition);
	}
	
/**
	 * 获取封锁表单个信息
	 *
	 * @param string $sField
	 * @param string $sCondition
	 * @return array
	 */
	function locksnamegetOne( $sField, $sCondition )
	{
		if( empty($sField) )
		{
			$sField = "*";
		}
		if( empty($sCondition) )
		{
			$sCondition = "1";
		}
		return $this->oDB->getOne("SELECT ".$sField." FROM `locksname` WHERE ".$sCondition);
	}



	/**
	 * 封锁彩种信息表
	 *
	 * @param string $sField
	 * @param string $sCondition
	 * @return array
	 */
	function locksLotteryGetAll($sField,$sCondition)
	{
		if(empty($sField))
		{
			$sField = "*";
		}
		if(empty($sCondition))
		{
			$sCondition = "1";
		}
		$sTableName = "`locksname` AS A left join `lottery` AS B on (A.`lotteryid`=B.`lotteryid`)";
		return $this->oDB->getAll("SELECT ".$sField." FROM ".$sTableName." where ".$sCondition);
	}



	/**
	 * 删除封锁表
	 *
	 * @param string $sCondition
	 * @return integer
	 */
	function locksLotteryDel($sCondition = '1<0')
	{
		if(empty($sCondition))
		{
			$sCondition = "1<0";
		}
		return $this->oDB->delete('locksname',$sCondition);
	}
	
}
?>