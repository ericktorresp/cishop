<?php
/**
 * 文件 : /_cli/2charts.php
 * 功能 : CLI - 市场管理的数据生成
 * 
 * 调用方式: charts.php i(i:为 市场管理的参数)
 *
 * @author     Saul
 * @version    1.2.0
 * @package    lowadmin
 */

define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件
class cli_charts extends basecli
{
    protected  function _runCli()
    {
        if( !isset( $this->aArgv[1]) )
        {
            echo('Need More Params');
            return false;
        }
        if( !is_numeric($this->aArgv[1]) )
        {
            echo('Error Params');
            return false;
        }
        $iAction = intval($this->aArgv[1]);
        /**
         * 执行相关的数据，和 Charts表中的数据ID 一致
         */
        echo "[d] [".date('Y-m-d H:i:s')."]\n----------[ START ]------------------\n";
        echo "[d] iAction = $iAction \n\n";
        $oCharts = new model_charts();
        if( $iAction == 1 )
        { //用户总数(频道开通的用户)
            $oCharts->setChartAllUser();
            return TRUE;
        }
        elseif( $iAction == 2 )
        { //参与游戏用户数(按照游戏参与人数，计入到游戏截至日期)
            $oCharts->setchartGameUser();
            return TRUE;
        }
        elseif( $iAction == 3 )
        { // 盈利用户数(用户得有奖金的(根据帐变来计算))
            $oCharts->setGameWinUser();
            return TRUE;
        }
        elseif( $iAction == 10 )
        { // 游戏总额(截至日期,不计算撤单的)
            $oCharts->setGameMoneytotal();
            return TRUE;
        }
        elseif( $iAction == 11 )
        { // 返奖总额(中奖的金额)
            $oCharts->setGameWinMoneytotal();
            return TRUE;
        }
        elseif( $iAction == 12 )
        { //低频转出(低频转到银行的)
            $oCharts->setGameMoneyOut();
            return TRUE;
        }
        elseif( $iAction == 13 )
        { //银行转入(银行转入低频)
            $oCharts->setGameMoneyIn();
            return TRUE;
        }
        elseif( $iAction == 20 )
        { // 帐变总数(所有帐变总数)
            $oCharts->setOrdersTotal();
            return TRUE;
        }
        elseif( $iAction == 21 )
        { // 追号个数()
            $oCharts->setTaskTotal();
            return TRUE;
        }
        elseif( $iAction ==22 )
        { // 注单个数()
            $oCharts->setProjectTotal();
            return TRUE;
        }
        return false;
    }
}
$oCli = new cli_charts();
exit;
?>