<?php
/**
 * 用户模型
 * 
 */
class model_usertree extends basemodel
{
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}
	
	function usertreegetlist()
	{
		
	}
	
	//获取总代的相关信息，需要结合用户的频道开通状态来做
	function userAgentget( $sCondition ="1" )
	{
		if(empty($sCondition))
		{
			$sCondition ="1";
		}
		return $this->oDB->getDataCached("SELECT * FROM `usertree` AS B WHERE `isdeleted` = 0 AND parentid=0 AND usertype<>2 AND ".$sCondition,10);
	}
}
?>