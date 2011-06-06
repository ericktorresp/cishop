<?php
/**
 * 路径: /_api/reportquery.php
 * 用途: passport查询低频报表游戏明细 (请求接收处理方)
 *
 * 
 * @author  mark
 * @version 1.0
 * @package lowgame
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件
class api_reportGameDetail extends baseapi
{
    public function _runProcess()
    {
        if( !isset($this->aArgv['iUserId'])
            || empty($this->aArgv['iUserId']) 
            || $this->aArgv['iUserId'] == 0
            || !isset($this->aArgv['sDate']) 
           )
        {
            return $this->makeApiResponse( FALSE, '数据初始错误!' );
        }
        $oConfig = new model_config();
        $aSnapshotTime = explode('-',$oConfig->getConfigs('kz_allow_time'));
        $sSnapshotStartTime = $this->aArgv['sDate'] . " " . $aSnapshotTime[0];
        $sStartTime = date("Y-m-d H:i:s", strtotime($sSnapshotStartTime));
        $sEndTime   = date("Y-m-d H:i:s", strtotime($sSnapshotStartTime) + 86400);
        /* @var $oMethod model_method */
        $oMethod        = A::singleton("model_method");
        $aMethod        = $oMethod->methodGetList( "a.`methodid`,a.`lotteryid`,a.`methodname`,b.`cnname`","a.`pid`>0", "", 0 );
        $aLottery =array();
        foreach( $aMethod as $method )
        {
            $aLottery[$method["lotteryid"]]["cnname"]                           = $method["cnname"];
            $aLottery[$method["lotteryid"]]["methodid"][]                       = $method["methodid"];
            $aLottery[$method["lotteryid"]]["method"][$method["methodid"]]      = $method["methodname"];
            $aLottery[$method["lotteryid"]]["sum_bonus"][$method["methodid"]]   = 0.00;
            $aLottery[$method["lotteryid"]]["sum_point"][$method["methodid"]]   = 0.00;
            $aLottery[$method["lotteryid"]]["sum_prize"][$method["methodid"]]   = 0.00;
            $aLottery[$method["lotteryid"]]["real_prize"][$method["methodid"]]  = 0.00;
            $aLottery[$method["lotteryid"]]["total"][$method["methodid"]]       = 0.00;
        }
        foreach( $aLottery as $i=>$v )
        {
            $aLottery[$i]["count"] = count($v["methodid"]);
        }
        /* @var $oOrders model_orders */
        $oOrders = A::singleton("model_orders");
        $aResult = $oOrders->getTotalUserBonusByMethod( $this->aArgv['iUserId'], 
                                " AND (P.`writetime` BETWEEN '$sStartTime' AND '$sEndTime')", FALSE, '', false );
        if( empty($aResult) )
        {
            $this->makeApiResponse(TRUE,array());
        }
        foreach( $aResult[1] as $v )
        { // 返点总额
            $aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]] = $v["summoney"];
        }
        foreach( $aResult[2] as $v )
        { //奖金总额 + 购彩金额
            $aLottery[$v["lotteryid"]]["sum_bonus"][$v["methodid"]] = $v["sumbonus"];
            $aLottery[$v["lotteryid"]]["sum_prize"][$v["methodid"]] = $v["sumprice"];
        }
        foreach($aLottery as $i=>$v)
        {
            foreach($v["methodid"] as $v1)
            {
                $aLottery[$i]["real_prize"][$v1]    = $aLottery[$i]["sum_prize"][$v1]  - $aLottery[$i]["sum_point"][$v1];
                $aLottery[$i]["total"][$v1]         = $aLottery[$i]["real_prize"][$v1] - $aLottery[$i]["sum_bonus"][$v1];
            }
        }
        return $this->makeApiResponse(TRUE, $aLottery );
    }
}
// 2, 为调用程序返回 '结果集'
$oApi = new api_reportGameDetail(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>