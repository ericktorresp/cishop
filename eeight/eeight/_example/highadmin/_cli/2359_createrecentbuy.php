<?php
/**
 * CLI FOR createRecentBuy
 * 生成代理用户每一天投注量数据的CLI
 * 命令行参数格式：2008-08-26+2008-08-29
 * 默认时间为执行当天
 * @author Mark
 */
if( !isset($argv[1]) )
{
    $sTime = date("Y-m-d",time() - 24 * 3600) . "+" . date("Y-m-d");
    $aTime = explode( '+',$sTime );
}
else
{
	$aTime = explode( '+',$argv[1] );
	$sError = "命令行参数错误，正确格式是：2008-08-26+2008-08-29";
	if( count($aTime) != 2 )
	{
		die($sError);
	}
	if($aTime[0] > $aTime[1])
	{
		die($sError);
	}
	$aStartDate = explode('-',$aTime[0]);
	if( strlen($aStartDate[0]) != 4 || strlen($aStartDate[1]) != 2 
		|| strlen($aStartDate[2]) != 2 || !checkdate($aStartDate[1],$aStartDate[2],$aStartDate[0]))
	{
		die($sError);
	}
	$aEndDate = explode('-',$aTime[1]);
	if( strlen($aEndDate[0]) != 4 || strlen($aEndDate[1]) != 2 
		|| strlen($aEndDate[2]) != 2 || !checkdate($aEndDate[1],$aEndDate[2],$aEndDate[0]))
	{
		die($sError);
	}
}
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件
class cli_createrecentbuy extends basecli
{
	protected  function _runCli()
	{
		echo "[d] [".date("Y-m-d H:i:s")."] -------[ START ]-------------\n";
		global $aTime;
		$oSale = new model_sale();
		$oSale->createRecenBuy( $aTime[0], $aTime[1] );
	}		
}
$oCli = new cli_createrecentbuy();
exit;
?>