<?php
/**
 * 路径: /_api/reportquery.php
 * 用途: passport查询高频报表 (请求接收处理方)
 *
 * 
 * @author  mark
 * @version 1.0
 * @package highgame
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件
class api_reportQuery extends baseapi
{
    public function _runProcess()
    {
        if( !isset($this->aArgv['iUserId'])
            || empty($this->aArgv['iUserId']) 
            || $this->aArgv['iUserId'] == 0
            || !isset($this->aArgv['sDate']) 
            || !isset($this->aArgv['iPid'])
           )
        {
            return $this->makeApiResponse( FALSE, '数据初始错误!' );
        }
        $oOrder  = new model_orders();
        $oConfig = new model_config();
        $aSnapshotTime = explode('-',$oConfig->getConfigs('kz_allow_time'));
        $sSnapshotStartTime = $this->aArgv['sDate'] . " " . $aSnapshotTime[0];
        $sStartTime = date("Y-m-d H:i:s", strtotime($sSnapshotStartTime));
        $sEndTime   = date("Y-m-d H:i:s", strtotime($sSnapshotStartTime) + 86400);
        $aResult = array();
        if( $this->aArgv['iPid'] == 0 )
        {
        	$this->aArgv['iAdminProxyId'] = isset($this->aArgv['iAdminProxyId']) ? $this->aArgv['iAdminProxyId'] : 0;
        	if (is_numeric($this->aArgv['iAdminProxyId']) && $this->aArgv['iAdminProxyId'] > 0){
        		//查询总代管理员的数据统计
        		$iUserId = $this->aArgv['iUserId'];
        		$aResult = $oOrder->getAdminProxyPoint( $this->aArgv['iAdminProxyId'], $this->aArgv['iUserId'],
	                                      " AND (p.`writetime` BETWEEN '$sStartTime' AND '$sEndTime')");
	            if( empty($aResult) )
	            {
	                return $this->makeApiResponse(TRUE, $aResult );//没有数据
	            }
	            $aResult[$iUserId]['amount'] = isset($aResult[$iUserId]['amount']) ? $aResult[$iUserId]['amount'] : 0;
	            $aResult[$iUserId]['point'] = isset($aResult[$iUserId]['point']) ? $aResult[$iUserId]['point'] : 0;
	            $aResult[$iUserId]['bonus'] = isset($aResult[$iUserId]['bonus']) ? $aResult[$iUserId]['bonus'] : 0;
	            $aResult[$iUserId]['realamount'] = $aResult[$iUserId]['amount']-$aResult[$iUserId]['point'];
	            $aResult[$iUserId]['win']        = $aResult[$iUserId]['realamount']-$aResult[$iUserId]['bonus'];
        	} else {
        		//查询用户自己的所有下级数据总和
	            $iUserId = $this->aArgv['iUserId'];
	            $aResult[$iUserId] = $oOrder->getReportData( $this->aArgv['iUserId'],
	                                      " AND (p.`writetime` BETWEEN '$sStartTime' AND '$sEndTime')");
	            if( empty($aResult) )
	            {
	                return $this->makeApiResponse(TRUE, $aResult );//没有数据
	            }
	            $aResult[$iUserId]['realamount'] = $aResult[$iUserId]['amount']-$aResult[$iUserId]['point'];
	            $aResult[$iUserId]['win']        = $aResult[$iUserId]['realamount']-$aResult[$iUserId]['bonus'];
        	}
        }
        else 
        {//查询下级用户
            $aReportData = $oOrder->getReportData( $this->aArgv['iPid'],
                " AND (p.`writetime` BETWEEN '$sStartTime' AND '$sEndTime')", TRUE );
            $oUser = new model_user();
            $aUserList = $oUser->getChildListID( $this->aArgv['iPid'] );
            foreach ( $aUserList as $aUser )
            {
                $iUserId = $aUser['userid'];
                if( isset($aReportData[$iUserId]) )
                {
                    if( !isset($aReportData[$iUserId]['point']) )
                    {
                        $aReportData[$iUserId]['point'] = 0.00;
                    }
                    $aReportData[$iUserId]['realamount'] = $aReportData[$iUserId]['amount']-$aReportData[$iUserId]['point'];
                    $aReportData[$iUserId]['win']        = $aReportData[$iUserId]['realamount']-$aReportData[$iUserId]['bonus'];
                    $aResult[$iUserId] = $aReportData[$iUserId];
                }
                else
                {
                    $aResult[$iUserId]['amount']     = 0.00;
                    $aResult[$iUserId]['realamount'] = 0.00;
                    $aResult[$iUserId]['bonus']      = 0.00;
                    $aResult[$iUserId]['point']      = 0.00;
                    $aResult[$iUserId]['wind']       = 0.00;
                }
                $aResult[$iUserId]['username'] = $aUser['username'];
            }
        }
        return $this->makeApiResponse(TRUE, $aResult );
    }
}
// 2, 为调用程序返回 '结果集'
$oApi = new api_reportQuery(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>