<?php
/**
 * 奖级模型
 *
 */
class model_prizelevel extends basemodel
{
	function __construct($aDBO=array())
	{
		parent::__construct($aDBO);
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
	 */
	function prizelevelGetList($sFields = '', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0)
	{
		if(empty($sFields))
		{
			$sFields ="A.`prizeid`,A.`prizegroupid`,A.`methodid`,A.`level`,A.`prize`,A.`userpoint`,A.`isclose`,A.`description`,B.`methodname`,C.`title`";
		}
		if(empty($sCondition))
		{
			$sCondition =" 1 ";
		}
		$iPageRecord = is_numeric($iPageRecord)?intval($iPageRecord):0;
		if($iPageRecord <= 0)
		{
			$iPageRecord = 0;
		}
		$sTableName =  "`prizelevel` AS A LEFT JOIN `method` AS B ON (A.`methodid`=B.`methodid`)";
		$sTableName .= " LEFT JOIN `prizegroup` AS C ON (A.`prizegroupid`= C.`prizegroupid`)";
		if($iPageRecord == 0)
		{
			if(!empty($sOrderBy))
			{
				$sOrderBy =" ORDER BY ".$sOrderBy;
			}
			return $this->oDB->getAll("SELECT ".$sFields."FROM ".$sTableName." WHERE ".$sCondition. $sOrderBy);
		}
		return $this->oDB->getPageResult($sTableName, $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy);
	}
} 
?>