<?php
/**
 * 路径 : /_cli/dataclear.php
 * 功能 : CLI - 数据清理
 * 
 * 调用方式: dataclear.php i   (i:为日志清理类型)
 * ----------------------------------------------
     * 1、管理员日志清理
     * 2、用户日志清理
     * 3、帐变清理
     * 4、奖期清理
     * 5、方案清理
     * 6、历史封锁表清理
     * 7、最近投注清理
     * 8、单期盈亏清理
     * 9、历史奖期清理
 *
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 */

define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_dataclear extends basecli
{
    private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件
    
    /**
     * 析构函数, 程序完整执行成功或执行错误后. 删除 locks 文件
     */
    public function __destruct()
    {
        parent::__destruct();
        if( $this->bDoUnLock == TRUE )
        {
            @unlink( $this->_getBaseFileName() . $this->aArgv[1] .'.locks' );
        }
    }
    
    
    /**
     * 1、管理员日志清理
     * 2、用户日志清理
     * 3、帐变清理
     * 4、奖期清理
     * 5、方案清理
     * 6、历史封锁表清理
     * 7、最近投注清理
     * 8、单期盈亏清理
     * 9、历史奖期清理
     * 
     * @return Bool
     */
    protected function _runCli()
    {
        // Step 01: 检查参数是否正确
        if( !isset($this->aArgv[1]) )
        {
            echo('Need More Params');
            return false;
        }
        if( !is_numeric($this->aArgv[1]) )
        {
            echo('Error Params');
            return false;
        }
    	// Step 02: 在此 CLI 程序运行时, 获取独占锁. 禁止多进程同时运行
    	$sLocksFileName = $this->_getBaseFileName() . $this->aArgv[1] .'.locks';
    	if( file_exists( $sLocksFileName ) )
    	{
    		$this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
    	}
    	file_put_contents( $sLocksFileName ,"running", LOCK_EX );
    	echo "[Start][Clear ".date('Y-m-d H:i:s')."\n";
        $iAction = $this->aArgv[1];
        if ( $iAction == 1 )
        { //管理员日志清理
            $bAllowClear = $this->_nowIsAllowClear('logcleardate','logclearstarttime','logclearendtime','logclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "logcleardate", "adminlog", $iAction);
        }
        elseif( $iAction == 2 )
        { // 用户日志清理
            $bAllowClear = $this->_nowIsAllowClear('logcleardate','logclearstarttime','logclearendtime','logclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "logcleardate", "userlog", $iAction);
        }
        elseif( $iAction == 3 )
        { // 帐变清理
            $bAllowClear = $this->_nowIsAllowClear('orderscleardate','ordersclearstarttime','ordersclearendtime','ordersclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "orderscleardate", "orders", $iAction);
        }
        elseif( $iAction == 4 )
        { // 奖期清理
            $bAllowClear = $this->_nowIsAllowClear('issuecleardate','issueclearstarttime','issueclearendtime','issueclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "issuecleardate", "issue", $iAction);
        }
        elseif( $iAction == 5 )
        { // 方案清理
            $bAllowClear = $this->_nowIsAllowClear('projectcleardate','projectclearstarttime','projectclearendtime','projectclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "projectcleardate", "project", $iAction);
        }
        elseif( $iAction == 6 )
        { // 历史封锁表清理
            $bAllowClear = $this->_nowIsAllowClear('historylockcleardate','historylockclearstarttime','historylockclearendtime','historylockclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "historylockcleardate", "historylock", $iAction);
        }
        elseif( $iAction == 7 )
        { // 最近投注清理
            $bAllowClear = $this->_nowIsAllowClear('recentbuycleardate','','','recentbuyclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "recentbuycleardate", "recentbuy", $iAction);
        }
        elseif( $iAction == 8 )
        { // 单期盈亏清理
            $bAllowClear = $this->_nowIsAllowClear('singlesalecleardate','','','singlesaleclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "singlesalecleardate", "singlesale", $iAction);
        }
        elseif( $iAction == 9 )
        { // 历史奖期清理
            $bAllowClear = $this->_nowIsAllowClear('historyissuecleardate','','','historyissueclearrun');
            if( $bAllowClear !== TRUE )
            {
                $this->bDoUnLock = TRUE;
                return FALSE;
            }
            echo $this->_runClear( "historyissuecleardate", "historyissue", $iAction);
        }
        $this->bDoUnLock = TRUE;
        return TRUE;
    }
    
    
    /**
     * 检查当前时间是否可以进行清理操作 (获取一些清理参数)
     * 
     * @param string $sDate       清理天数-MYSQL.CONFIG. configkey
     * @param string $sStartTime  清理开始时间-MYSQL.CONFIG. configkey
     * @param string $sEndTime    清理结束时间-MYSQL.CONFIG. configkey
     * @param string $sIsRun      是否进行清理-MYSQL.CONFIG. configkey
     * @return string
     * 
     * @author mark
     */
    private function _nowIsAllowClear( $sDate = '', $sStartTime = '', $sEndTime = '', $sIsRun = '' )
    {
        $oConfig = new model_config();
        $configs = $oConfig->getConfigs( array($sDate, $sStartTime, $sEndTime, $sIsRun) );
        if ( $configs[$sIsRun] == 0 )
        {
            echo "This Programm is not allowed";
            return FALSE;
        }
        if( $sStartTime == '' || $sEndTime == '' )
        {
            return TRUE;
        }
        if ( $configs[$sStartTime] > $configs[$sEndTime] )
        {//跨天
            $sCurrentDate =date("Y-m-d H:i:s");
            $sStartTime = date("Y-m-d ").$configs[$sStartTime];
            $sEndTime = date("Y-m-d ").$configs[$sEndTime];
            if ( ($sCurrentDate < $sStartTime) && ($sEndTime > $sCurrentDate) )
            {
                echo "Now is not allow Programm run.";
                return FALSE;
            }
        }
        else
        {//没有跨天
            $sCurrentDate = date("H:i:s");
            if( $sCurrentDate < $configs[$sStartTime] || $sCurrentDate > $configs[$sEndTime] )
            {
                echo "Now is not allow Programm run.";
                return FALSE;
            }
        }
        return TRUE;
    }
    
    
    /**
     * 执行清理操作
     *
     * @param  string $sDate 清理天数配置健值
     * @param  string $sBackFileName 备份文件名称
     * @param  int    $iClearId  清理参数
     * @return string
     * 
     * @author mark
     */
    private function _runClear( $sDate = '', $sBackFileName = '', $iClearId = 0 )
    {
        $oDataBackClear = new model_databackclear();
        $oConfig = new model_config();
        $iDay = $sDate == '' ? -1 : $oConfig->getConfigs($sDate);
        $sFile   = date("Ymd") . "_" . $sBackFileName .".gz";
        $sPath = PDIR.DS."_data".DS.$sBackFileName.DS;
        makeDir($sPath);
        $bBackFlag = $oDataBackClear->backAndClear( $iDay, $sPath.$sFile, $iClearId );
        if($bBackFlag)
        {
            return  "[End] [".date('Y-m-d H:i:s'). " ".$sBackFileName ." had Clear\n";
        }
        else 
        {
            return  "[End] [".date('Y-m-d H:i:s'). " ". $sBackFileName ." Clear Fail\n";
        }
    }
}

$oCli = new cli_dataclear();
exit;
?>