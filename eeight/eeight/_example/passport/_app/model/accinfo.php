<?php
/**
 * 查询用户高低频账户余额总和,默认下不汇总银行大厅的账户余额值
 * 
 * @version 	v1.0	2010-02-08
 * @author 		louis
 *
 */
class model_accInfo extends basemodel 
{
	/**
	 * 将指定平台的账户余额数据汇总
	 *
	 * @param int	 	$iUserId		// 用户ID
	 * @param array  	$aPort			// 目标平台代号
	 * @param boolean 	$bIsSelf		// 是否包含部代自身账户余额
	 * @return array
	 * 
	 * @version 	v1.0	2010-02-08
	 * @author 		louis
	 * 
	 */
	function getTotalInfo( $iUserId, $aPort = "", $bIsSelf = true){
		if(empty($iUserId) || !is_numeric($iUserId)){
			return 0.00;
		}
		$aApiSendData = array();
		$aApiSendData['iUserId'] = $iUserId;
		$aApiSendData['iIsSelf'] = intval($bIsSelf);
		$aResult = array();
		$aTempPort = array();
		
		$oChannel = A::singleton("model_userchannel");
		$aChannel = $oChannel->getUserChannelList( $iUserId );
		if (empty($aChannel) || !isset($aChannel[0]) || !is_array($aChannel[0])){
			return 'error';
		}
		// 默认不包含银行大厅
		foreach ($aChannel[0] as $key => $val){
			if($key === 0) unset($aChannel[0][$key]);
		}
		
		$aTempPort = empty($aPort) ? $aChannel[0] : $aPort;
		// 循环执行API接口，获取数据
		$aResult['team_abalance'] = 0;
        $aResult['team_balance'] = 0;
        $aResult['team_congealed'] = 0;
		foreach ($aTempPort as $k => $v){
			$oChannelApi = new channelapi( $k, 'getTeamAccInfo', FALSE );
	        $oChannelApi->setTimeOut(15);            // 设置读取超时时间
	        $oChannelApi->setResultType('serial');   // 设置返回数据类型
	        $oChannelApi->sendRequest( $aApiSendData );    // 发送结果集
	        $aApiResult = $oChannelApi->getDatas();
	       	if( empty($aApiResult) || !is_array($aApiResult) || $aApiResult['status'] == 'error' )
            {//调用API获取结果失败，可能资金帐户不存在
                return 'error';
            }
        	$aResult['team_abalance'] += floatval($aApiResult['data']['team_abalance']);
        	$aResult['team_balance'] += floatval($aApiResult['data']['team_balance']);
        	$aResult['team_congealed'] += floatval($aApiResult['data']['team_congealed']);
		}
		return $aResult;
	}
}