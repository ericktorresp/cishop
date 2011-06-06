<?php
/**
 * 路径: /_cli/2022_initlocks.php
 * 功能: 低频停止销售后的封锁表整理 ( * Crontab * )
 * 
 * 运行时间:
 * ~~~~~~~~~~~~~~~~~
 *    20:22 准时运行
 * 
 * @author    James     090817 16:31
 * @version   1.0.0
 * @package   lowgame
 */


// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

/**
 * 运行时间:  每天停止销售后 8:20 (依赖奖期信息)
 * 流程摘要:
 *     1, 对当期(当天) 封锁表 locks 进行备份, 至历史封锁表中 group by 合并后1000条入 history 表
 *     2, 使用 locksXXfuture 表的最近一期数据(即明天), 重新初始化当期封锁表 locksXX 
 *     3, 对 locksXXfuture 表, 增加一期空白的封锁记录(保证其超过最大追号期数+5)
 */
class cli_initlocks extends basecli
{
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

        // Step 02: 根据系统日期, 计算当前各个彩种的期号
        $oCommon = new model_common();
        $sSql = "SELECT `lotteryid`,`lockname` FROM `locksname` ";
        $aResult = $oCommon->commonGetAll($sSql);
        foreach( $aResult as $v )
        {
        	$this->doDataCleanUp( $v['lotteryid'], $v['lockname'] );
        }

        // Step 03: 结束后释放控制权, 删除文件
        @unlink( $sLocksFileName );
    }


    /**
     * 根据传递的封锁表名, 期号, 对封锁表进行整理
     */
    private function doDataCleanUp( $iLotteryId=0, $sLockTableName='' )
    {
        $oLocks  = new model_locks();
        $mResult = $oLocks->transferLocks( $iLotteryId, $sLockTableName );
        echo "[d] [".date("Y-m-d H:i:s")."] -------[ START ]-------------\n";
        if( $mResult !== TRUE )
        {
        	switch( $mResult )
        	{
        		case -1 : $sMsg = 'wrong lottery id';break;
        		case -2 : $sMsg = 'some issue are saling';break;
        		case -3 : $sMsg = 'get the end issue wrong(empty)';break;
        		case -4 : $sMsg = 'no trace issue';break;
        		case -5 : $sMsg = 'the data of current lockstable is failed';break;
        		case -666 : $sMsg = 'delete history data failed';break;
        		case -66 :  $sMsg = 'Insert history data failed';break;
        		case -6 : $sMsg = 'delete current locksdata failed';break;
        		case -7 : $sMsg = 'Copy Future to Current LocksTable Failed';break;
        		case -888 : $sMsg = 'delete future data error';break;
        		case -88 : $sMsg = 'Insert future data error';break;
        		case -89 : $sMsg = 'Insert sales data error';break;
        		case -8 : $sMsg = 'Update future data error';break;
        		case -9 : $sMsg = 'delete future data error';break;
        		case -55 : $sMsg = 'LockTable already Processed';break;
        		case 5011 : $sMsg = 'transaction start error';break;
        		case 5012 : $sMsg = 'rollback error';break;
        		case 5013 : $sMsg = 'commit error';break;
        		case -555 : $sMsg = 'update issue stauts error';break;
        		default : $sMsg='Unknow';break;
        	}
        	echo "[d] [" . date('Y-m-d H:i:s').'] Error: lottery:'.$iLotteryId.'=>'.$sLockTableName." \nErrno:".$mResult."=>".$sMsg."\n\n";
        	return FALSE;
        }
        echo "[d] [" . date('Y-m-d H:i:s').'] Success: lottery:'.$iLotteryId.'=>'.$sLockTableName." OK \n\n";
        return TRUE;
    }
}


$oCli = new cli_initlocks(TRUE);
EXIT;
?>