<?php
/**
 * 路径: /_api/banksnapshot.php
 * 用途: 总账快照报表处理 (请求接收处理方)
 * 
 * 注意: 此文件(banksnapshot.php) 在每个平台中都存在
 *       应使资金调用API方法统一 : $oUserFund->apiFundTransition()
 *       并且此文件更新, 要对每个平台的 /_api/channelTransition.php 进行同步更新
 *
 * 
 * 转账调度器 API 接收参数:
 * 
 * @author  saul
 * @version 1.2.0
 * @package *** All Channel ***
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class api_banksnapshot extends baseapi
{
    public function _runProcess()
    {
    	if( !empty($this->aArgv['dates'])
             && !empty($this->aArgv['istester']) && !is_numeric( $this->aArgv['istester'])
             && !empty($this->aArgv['isfrozen']) && !is_numeric( $this->aArgv['isfrozen'])
        )
        {
        	return $this->makeApiResponse(FALSE, "参数错误" );
        }
        $date       = getFilterDate( $this->aArgv["dates"], "Y-m-d" );
        $iTest      = intval( $this->aArgv["istester"] );
        $iFrozen    = intval( $this->aArgv["isfrozen"] );
    	if( getStaticCache("banksnapshot_".SYS_CHANNELID."_".$date) )
    	{
    		$aData = getStaticCache("banksnapshot_".SYS_CHANNELID."_".$date);
    	}
    	else
    	{ //生成对象，整理对象
    		$oBankSnapShot = new model_banksnapshot();
    		/**
    		 * 帐号类型[是否为tester,冻结状态]
    		 */
    		$aBankSnapShot[0][0] = $oBankSnapShot->getSnapshotDatas( $date, " AND `istestuser`='0' AND `islockuser`='0'" );
    		$aBankSnapShot[0][1] = $oBankSnapShot->getSnapshotDatas( $date, " AND `istestuser`='0' AND `islockuser`='1'" );
    		$aBankSnapShot[1][0] = $oBankSnapShot->getSnapshotDatas( $date, " AND `istestuser`='1' AND `islockuser`='0'" );
            $aBankSnapShot[1][1] = $oBankSnapShot->getSnapshotDatas( $date, " AND `istestuser`='1' AND `islockuser`='1'" );
    		$aData = array();
            foreach($aBankSnapShot as $iTestId=>$vTest)
    		{
    			foreach( $vTest as $iFrozenId => $vFrozen)
    			{
    				foreach($vFrozen["data"] as $v )
    				{
    					$aData[$v["userid"]][$iTestId][$iFrozenId] = $v;
    				}
    			}
    		}
            setStaticCache("banksnapshot_".SYS_CHANNELID."_".$date, $aData );
    	}
        $aResult = array();
        foreach( $aData as $iUserId => $data )
        {            
            $aTemp = array();
            if( $iTest!==-1 )
            {//非测试帐号
                if( $iFrozen!=-1 )
                { //分冻结状态获取用户
                    $aTemp[0] = $iTest*2+$iFrozen; 
                }
                else
                { //不考虑冻结状态的
                    $aTemp[0] = $iTest*2;
                    $aTemp[1] = $iTest*2+1;
                }
            }
            elseif( $iTest==-1 )
            { //测试不作为条件
                if( $iFrozen!==-1 )
                { //考虑冻结状态
                    $aTemp[0] = $iFrozen;
                    $aTemp[1] = $iFrozen+2;
                }
                else
                {
                    $aTemp[0] = 0;
                    $aTemp[1] = 1;
                    $aTemp[2] = 2;
                    $aTemp[3] = 3;
                }
            }
            foreach($aTemp as $v)
            {
                $i = floor($v/2); 
                $t = $v%2;
                
                if( isset( $data[$i][$t]) )
                {
	                if( !isset($aResult[$iUserId]))
	                {
	                    $aResult[$iUserId] = array(
	                        "totalbuy" => 0.0000,
	                        "totalpoint" => 0.0000,
	                        "totalbingo" => 0.0000,
	                        "totalbalance" => 0.0000,
	                        "cashin" => 0.0000,
	                        "cashout" => 0.0000,
	                        "tranferin" => 0.0000,
	                        "tranferout" => 0.0000,
	                        "cashdiff" => 0.0000,
	                        "todaycash" => 0.0000,
	                        "ta" => 0.0000,
	                        "tb" => 0.0000,
	                        "tc" => 0,
	                        "td" => 0,
	                        "te" => 0,
	                        "username"=>""          
	                    );
	                }
                	$aResult[$iUserId]["username"]      =  $data[$i][$t]["username"];
                    $aResult[$iUserId]["cashin"]        += $data[$i][$t]["cashin"];
                    $aResult[$iUserId]["cashout"]       += $data[$i][$t]["cashout"];
                    $aResult[$iUserId]["tranferin"]     += $data[$i][$t]["tranferin"];
                    $aResult[$iUserId]["tranferout"]    += $data[$i][$t]["tranferout"];
                    $aResult[$iUserId]["cashdiff"]      += $data[$i][$t]["cashdiff"];
                    $aResult[$iUserId]["todaycash"]     += $data[$i][$t]["todaycash"];
                    $aResult[$iUserId]["ta"]            += $data[$i][$t]["ta"];
                    $aResult[$iUserId]["tb"]            += $data[$i][$t]["tb"];
                    $aResult[$iUserId]["tc"]            += $data[$i][$t]["tc"];
                    $aResult[$iUserId]["td"]            += $data[$i][$t]["td"];
                    $aResult[$iUserId]["te"]            += $data[$i][$t]["te"];
                }
            }
        }
        $Total = array(
           "totalbuy"       => 0.0000,
           "totalpoint"     => 0.0000,
           "totalbingo"     => 0.0000,
           "totalbalance"   => 0.0000,
           "tranferin"      => 0.0000,
           "tranferout"     => 0.0000,
           "cashin"         => 0.0000,
           "cashout"        => 0.0000,
           "cashdiff"       => 0.0000,
           "todaycash"      => 0.0000,
           "ta"             => 0,
           "tb"             => 0,
           "tc"             => 0,
           "td"             => 0,
           "te"             => 0,              
        );
        foreach($aResult as $v)
        {
            $Total["totalbuy"]    += $v["totalbuy"];
            $Total["totalpoint"]  += $v["totalpoint"];
            $Total["totalbingo"]  += $v["totalbingo"];
            $Total["totalbalance"]+= $v["totalbalance"];
            $Total["tranferin"]   += $v["tranferin"];
            $Total["tranferout"]  += $v["tranferout"];
            $Total["cashin"]      += $v["cashin"];
            $Total["cashout"]     += $v["cashout"];
            $Total["cashdiff"]    += $v["cashdiff"];
            $Total["todaycash"]   += $v["todaycash"];
            $Total["ta"]          += $v["tc"];
            $Total["tc"]          += $v["tc"];
            $Total["td"]          += $v["td"];
            $Total["te"]          += $v["te"];
        }
        $aResult["total"] = $Total; 
    	return $this->makeApiResponse(TRUE, $aResult );
    }
}
// 2, 为调用程序返回 '结果集'
$oApi = new api_banksnapshot(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>