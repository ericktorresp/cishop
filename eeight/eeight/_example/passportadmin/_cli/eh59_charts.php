<?php
/**
 * 路径: /_cli/eh59_charts.php
 * 功能: 图表数据计算程序 ( * Crontab * )

 * 由操作系统调用的 charts.php 支持的命令行参数: cid
 * @author tom
 */


/***********************************************************/
/* 初始化 ( Init )                                         */
/***********************************************************/
if( !isset($argv[1]) )
{
    die('Need More Params');
}

if( !is_numeric($argv[1]) )
{
    die('Error Params');
}

define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class cli_charts extends basecli
{
    /**
     * 重写基类 _runCli() 方法
     */
    protected function _runCli()
    {
    	/**
         * $iChartId 对应 chartdatas.chartid :
         *   1  =  用户总数
         *   2  =  活跃用户数
         *  10  =  充值总额
         *  11  =  提现总额
         *  20  =  总账变数量
         *  21  =  充值账变数量
         *  22  =  提现账变数量
         */
        $iChartId = intval($this->aArgv[1]);
        echo "[d] [".date('Y-m-d H:i:s')."]\n----------[ START ]------------------\n";
        echo "[d] iAction = $iChartId \n\n";
        $oChart = new model_charts();
        //$oChart->tomDummyInsert(22);exit;
        
        if( $iChartId === 1 )
        { // 更新-当天用户总数 
            $oChart->setChartAllUser();
            return TRUE;
        }
        
        if( $iChartId === 2 )
        { // 更新-当天活跃用户总数 
            $oChart->setChartActiveUser();
            return TRUE;
        }
        
        if( $iChartId === 10 )
        { // 更新-充值总额
            $oChart->setChartMoneyIn();
            return TRUE;
        }
        
        if( $iChartId === 11 )
        { // 更新-提现总额
            $oChart->setChartMoneyOut();
            return TRUE;
        }
        
        if( $iChartId === 20 )
        { // 更新-总账变个数
            $oChart->setChartOrderAllCount();
            return TRUE;
        }
        
        if( $iChartId === 21 )
        { // 更新-充值账变个数
            $oChart->setChartOrderMoneyInCount();
            return TRUE;
        }
        
        if( $iChartId === 22 )
        { // 更新-提现账变个数
            $oChart->setChartOrderMoneyOutCount();
            return TRUE;
        }
        
        return FALSE;
    }
}


$oCli = new cli_charts(TRUE);
exit;
?>