<?php
/**
 * 路径: /_api/getTeamAccInfo.php
 * 用途: 获取用户团队账户信息
 * 
 * 转账调度器 API 接收参数:
 *    $this->aArgv['iUserId']       =  用户ID
 *    $this->aArgv['iIsSelf']       =  是否包含用户自身账户信息
 * 
 * @author 	    louis
 * @version		v1.0 	2010-02-08
 * 
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件



class api_getTeamAccInfo extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return $this->makeApiResponse( TRUE|FALSE, 'STRING DATA' )
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */
    public function _runProcess()
    {
        // -------------------- 更新转出频道, 转入频道 '转账账变' 的状态值 ------------------
        if( !empty($this->aArgv['iUserId']) && is_numeric( $this->aArgv['iUserId']) 
        	|| !empty($this->aArgv['iIsSelf']) && is_numeric($this->aArgv['iIsSelf']))
        {
        	// 检查用户是否存在
        	$oUser = A::singleton("model_user");
        	$aResult = $oUser->getUsersProfile('', '', " AND userid={$this->aArgv['iUserId']}");
        	if(empty($aResult['username'])) return $this->makeApiResponse( FALSE, 'illegitimate user' );
        	// 获取用户账户信息
            $oUserFund = A::singleton("model_userfund");
            $aTeamInfo = array();
            $aTeamInfo = $oUserFund->getProxyTeamFundList($this->aArgv['iUserId']);
            $aSelfInfo = array();
            $aData = array();
            if (empty($aTeamInfo)){
            	return $this->makeApiResponse( FALSE, 'data error' );
            }

            if(!$this->aArgv['iIsSelf']){
            	$aSelfInfo = $oUserFund->getProxyFundList($this->aArgv['iUserId']);
            	if (!empty($aSelfInfo)){
            		$aData['team_abalance'] = floatval($aTeamInfo[0]['TeamAvailBalance']) - floatval($aSelfInfo[0]['TeamAvailBalance']);
		            $aData['team_balance'] = floatval($aTeamInfo[0]['TeamChannelBalance']) - floatval($aSelfInfo[0]['TeamChannelBalance']);
		            $aData['team_congealed'] = floatval($aTeamInfo[0]['TeamHoldBalance']) - floatval($aSelfInfo[0]['TeamHoldBalance']);
            	} else {
            		return $this->makeApiResponse( FALSE, 'user is not exist' );
            	}
            } else {
            	$aData['team_abalance'] = floatval($aTeamInfo[0]['TeamAvailBalance']);
            	$aData['team_balance'] = floatval($aTeamInfo[0]['TeamChannelBalance']);
            	$aData['team_congealed'] = floatval($aTeamInfo[0]['TeamHoldBalance']);
            }
            return $this->makeApiResponse( TRUE, $aData );
        }
        else
        {
            return $this->makeApiResponse( FALSE, 'data error' );
        }
    }
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_getTeamAccInfo(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;