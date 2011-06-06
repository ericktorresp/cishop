<?php
/**
 * 路径: /_cli/530_tasktoproject.php
 * 功能: 低频开始销售后追号单转注单 ( * Crontab * )
 * 
 * 运行时间:
 * ~~~~~~~~~~~~~~~~~
 *    每五分钟运行一次或三十分钟运行一次.
 *    如：CQSSC可设置为每五分钟运行一次，SSL可设置为每三十分钟运行一次。根据彩种的频率分别设置。
 *    在游戏销售期间对当前期内的追号单进行转注单操作.
 * 
 * 流程摘要:
 * ~~~~~~~~~~~~~~~~~~
 *   1, 读取需要转的追号单.
 *   2, 如果是追中即停，则判断是否已追中，如果停止则对追号单后面的进行撤单. 
 *   3, 更新奖期表状态为已完成.
 * 
 * @author    mark
 * @version   1.0.0
 * @package   highgame
 */


class cli_tasktoproject extends basecli
{
	private $bSetLoopMode = FALSE; //是否循环执行所有未追号单转注单的
	private $iLotteryId = 0;       // 彩种ID  `lottery`.lotteryid
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件
    private $iRunTimes = 1;       // 是否循环执行所有未追号单转注单的


    /**
     * 析构
     */
    public function __destruct()
    {
        parent::__destruct();
        if( $this->bDoUnLock == TRUE )
        {
            @unlink( $this->_getBaseFileName() . '_tasktoproject_'. $this->iLotteryId. '.locks' );
        }
    }

    /**
     * 重写基类 _runCli() 方法
     */
    protected function _runCli()
    {
        // Step: 01 初步检测 CLI 参数合法性
        if( !isset($this->aArgv[1]) || !is_numeric($this->aArgv[1]) )
        {
            $this->halt('Error : Lottery ID #1001' );
        }
        $this->iLotteryId = intval($this->aArgv[1]);   // 彩种ID
        
        $sLocksFileName = $this->_getBaseFileName() . '_tasktoproject_'. $this->iLotteryId. '.locks';
        if( file_exists( $sLocksFileName ) )
        {
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX);

        // Step 02: 执行追号单转注单
        //循环运行直到所有符合期数都完成
        if( isset($this->aArgv[2]) && strtolower($this->aArgv[2]) == 'loop' )
        {
            $this->bSetLoopMode = TRUE;
        }
        //指定循环执行次数
        if( isset($this->aArgv[2]) && is_numeric($this->aArgv[2]) )
        {
            $this->iRunTimes = intval($this->aArgv[2]);
        }
        $bFlags = $this->taskToProject( $this->aArgv[1] );

        // Step 03: 结束后释放控制权，删除文件
        $this->bDoUnLock = TRUE;
        return $bFlags === TRUE ? TRUE : FALSE;
    }


    /**
     * 对指定的彩种. 转换为注单
     *
     * @param array $aLotteryIds
     * @return bool
     */
    protected function taskToProject( $iLotteryId = 0 )
    {
    	if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId == 0)
    	{
    		echo "error: lotteryid unkown";
    		return FALSE;
    	}
    	echo "[d] [".date('Y-m-d H:i:s')."]\n----------[ START ]------------------\n";
    	$oTaskToProject   = A::singleton("model_tasktoproject");
    	$sResult = $oTaskToProject->traceToProject( $iLotteryId, $this->bSetLoopMode, $this->iRunTimes );
    	echo '[d] [' . date('Y-m-d H:i:s') ."] lottery[".$iLotteryId."]=>: ".$sResult."\n";
        echo "-------------[ END ]------------------\n\n";
        return TRUE;
    }
}
?>