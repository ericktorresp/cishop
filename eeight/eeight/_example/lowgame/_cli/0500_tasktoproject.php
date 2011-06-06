<?php
/**
 * 路径: /_cli/0500_tasktoproject.php
 * 功能: 低频开始销售后追号单转注单 ( * Crontab * )
 * 
 * 运行时间:
 * ~~~~~~~~~~~~~~~~~
 *    05:00-07:00 每5分钟运行1次
 * 
 * 流程摘要:
 * ~~~~~~~~~~~~~~~~~~
 *   1, 读取需要转的追号单
 *   2, 如果是追中即停，则判断是否已追中，如果停止则对追号单后面的进行撤单 
 *   3, 更新奖期表状态为已完成
 * 
 * @author    James     090818 16:31
 * @version   1.0.0
 * @package   lowgame
 */


// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_tasktoproject extends basecli
{
	private $bSetLoopMode = FALSE; //是否循环执行所有未追号单转注单的

    /**
     * 重写基类 _runCli() 方法
     */
    protected function _runCli()
    {
        // Step 01: 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '.locks';
        if( file_exists( $sLocksFileName ) )
        {
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX);

        // Step 02: 获取彩种信息
        $oCommon = new model_common();
        $sSql = "SELECT `lotteryid` FROM `lottery` ";
        $aResult = $oCommon->commonGetAll($sSql);
        if( isset($this->aArgv[1]) && strtolower($this->aArgv[1])=='loop' )
        {
            $this->bSetLoopMode = TRUE;
        }
        $bFlags = $this->traceToProject( $aResult );

        // Step 03: 结束后释放控制权，删除文件
        @unlink( $sLocksFileName );
        return $bFlags===TRUE ? TRUE : FALSE;
    }


    /**
     * 对各个彩种的追号单进行遍历. 转换为注单
     *
     * @param array $aLotteryIds
     * @return bool
     */
    protected function traceToProject( $aLotteryIds=array() )
    {
    	if( empty($aLotteryIds) || !is_array($aLotteryIds) )
    	{
    		echo "error: lotteryid unkown";
    		return FALSE;
    	}
    	echo "[d] [".date('Y-m-d H:i:s')."]\n----------[ START ]------------------\n";
    	$oTask   = A::singleton("model_task");
        foreach( $aLotteryIds as $v )
        {
            $sResult = $oTask->traceToProject( $v['lotteryid'], $this->bSetLoopMode );
            echo '[d] [' . date('Y-m-d H:i:s') ."] lottery[".$v['lotteryid']."]=>: ".$sResult."\n";
        }
        echo "-------------[ END ]------------------\n\n";
        return TRUE;
    }
}


$oCli = new cli_tasktoproject(TRUE);
EXIT;
?>