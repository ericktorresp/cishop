<?php
/**
 * 路径: /_cli/banksnapshot.php
 * 功能: 执行需依赖一些环境变量
 *       $oConfig->getConfigs('zz_forbid_time') :  休市时间 
 *       $oConfig->getConfigs('kz_allow_time')  :  快照允许启动时间
 *       在每天休市的时间段内(例: 4:10-4:50), 由系统自动执行此程序(计划任务)
 * 
 * @author    Tom    090915
 * @version   1.2.0
 * @package   passportadmin
 */

// init
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class cli_banksnapshot extends basecli
{
    /**
     * 析构函数, 程序完整执行成功或执行错误后. 删除 locks 文件
     */
    public function __destruct()
    {
        parent::__destruct();
        @unlink( $this->_getBaseFileName() . '.locks' );
    }


    /**
     * 重写基类 _runCli() 方法, 程序主流程
     */
    protected function _runCli()
    {
        // Step 01: 检查当前时间是否允许快照程序启动
        if( FALSE === $this->_nowIsAllowCashSnapshot() )
        {
            $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] Time Not Allowed!');
        }


    	// Step 02: 在此 CLI 程序运行时, 获取独占锁. 禁止多进程同时运行
    	$sLocksFileName = $this->_getBaseFileName() . '.locks';
    	if( file_exists( $sLocksFileName ) )
    	{
    		$this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
    	}
    	file_put_contents( $sLocksFileName ,"running", LOCK_EX );


        // Step 03: 执行快照业务流程
        $oBankSnapShot = new model_banksnapshot();
        $bProcessFlags = $oBankSnapShot->doSnapshot();

        @unlink( $sLocksFileName );
        if( $bProcessFlags === TRUE )
        {
            echo "[d] [".date('Y-m-d H:i:s')."] ALL DONE!\n";
            return TRUE;
        }

        $this->halt( "[w] [".date('Y-m-d H:i:s')."] ErrCode: $bProcessFlags" );
    }


	/**
     * 检查当前时间是否可以进行转账 (SYS_CONFIG 设置每天转账关闭时间)
     *   依赖参数: MYSQL.CONFIG. configkey = kz_allow_time 
     * @return BOOL
     */
    private function _nowIsAllowCashSnapshot()
    {
        $oConfig = new model_config();
        $aConfigValue = $oConfig->getConfigs( array('kz_allow_time') ); // 允许快照启用的时间段
        $aTime = @explode( '-', $aConfigValue['kz_allow_time'] );
        list( $iBeginHour, $iBeginMinute ) = @explode(":", $aTime[0]);
        list( $iEndHour, $iEndMinute ) = @explode(":", $aTime[1]);

        //echo "$iBeginHour - $iBeginMinute - $iEndHour - $iEndMinute \n"; 4-10-4-50
        if( !is_numeric($iBeginHour) || !is_numeric($iBeginMinute) 
            || !is_numeric($iEndHour) || !is_numeric($iEndMinute) )
        {
            return FALSE;
        }

        /* 算法:  开始的小时数,大于结束的小时数,则按跨天处理
         *    例1:   4:00-13:00   正常不跨天, 每天 早上4点至下午1点
         *    例2:   23:00-4:00   跨天处理, 每天凌晨 23:00 至第二天凌晨4点
         */ 

        $iNowTime  = time(); // 当前时间点的 时间戳
        if( $iBeginHour > $iEndHour )
        { // 处理跨天
            //echo '跨天<br/>';
            $iNowTime =  date("Hi"); // 00:21 表示为 0021
            if( intval($iNowTime) > intval($iBeginHour.$iBeginMinute)
                ||  $iNowTime < $iEndHour.$iEndMinute
            )
            {
                return TRUE;
            }
            else 
            {
                return FALSE;
            }
        }
        else 
        { // 处理不跨天
            //echo '不跨天<br/>';
            $iNowTime =  date("Hi"); // 00:21 表示为 0021
            if( intval($iNowTime) > intval($iBeginHour.$iBeginMinute)
                &&  $iNowTime < $iEndHour.$iEndMinute  )
            {
                return TRUE;
            }
            else 
            {
                return FALSE;
            }
        }
    }
}


$oCli = new cli_banksnapshot(TRUE); // 生产版本可以考虑改为 FALSE, 关闭调试
EXIT;
?>