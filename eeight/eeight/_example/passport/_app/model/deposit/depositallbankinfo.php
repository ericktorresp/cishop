<?php

/**
 * 路径:/_app/model/depositallbankinfo.php
 * 功能：主要负责充值相关的所有银行汇总性数据的获取
 * 
 * 方法：
 * --getcount				查询用户支付中订单的数量
 * 
 * 
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-11-24
 * @package 	passport
 *
 */
class model_deposit_depositallbankinfo extends basemodel{
	
	/**
	 * 用户id
	 *
	 * @var int
	 */
	public $UserId;
	
	/**
	 * 所有充值相关记录表名
	 *
	 * @var array
	 */
	public $BankList = array("email_deposit_record", "ccb_deposit_record");

	
	
	
	/**
	 * 查询用户支付中订单的数量
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 * 
	 * @return 		mix				-1					// 数据检查错误
	 * 								count($aResult)		// 数据个数
	 * 
	 */
	public function getcount(){
		// 数据检查
		if ( !is_numeric($this->UserId) || $this->UserId <= 0){
			return -1;
		}
		if (empty($this->BankList)){
			return -1;
		}
		$iTotal = 0;
		foreach ($this->BankList as $v){
			$sSql = "SELECT count(`id`) as num FROM `{$v}` WHERE `user_id` = {$this->UserId} AND `status` = 0 AND `created` LIKE '" . date("Y-m-d", time()) . "%'";
			$aResult = $this->oDB->getOne( $sSql );
			$iTotal += $aResult['num'];	
		}
		return $iTotal;
	}
}