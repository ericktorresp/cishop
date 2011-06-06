<?php
class model_userprizelevel extends basemodel
{
	function __construct( $aDBO=array())
	{
		parent::__construct( $aDBO );
	}



	/**
	 * 获取用户的奖金组信息
	 * @author mark
	 * @param  string  $sField
	 * @param  string  $sCondition
	 * @param  string  $sOrderBy
	 * @param  integer $iPageRecord
	 * @param  integer $icurrPage
	 * @return array
	 */
	function userPglevelGetList( $sField = "", $sCondition = "", $sOrderBy = "", $iPageRecord = 0, $icurrPage = 0)
	{
		$sTableName =" `userprizelevel` AS A LEFT JOIN `method` AS B ON (A.`methodid`=B.`methodid`) ";
		if(empty($sField))
		{
			$sField = "*";
		}
		if(empty($sCondition))
		{
			$sCondition = "1";
		}
		$sOrderBy    = empty($sOrderBy) ? "" : "ORDER BY ".$sOrderBy;
 		$iPageRecord = is_numeric($iPageRecord) ? intval($iPageRecord):0;
		if($iPageRecord == 0)
		{
			return $this->oDB->getAll("SELECT ".$sField." FROM ".$sTableName." WHERE ".$sCondition);
		}
		return $this->oDB->getPageResult($sTableName,$sField,$sCondition,$iPageRecord,$icurrPage,$sOrderBy);
	}
}
?>