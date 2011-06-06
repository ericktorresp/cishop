<?php
/**
 * 路径: /_example/lowgame/_cli/create_locks_data.php
 * 功能: 生成封锁表数据
 * 
 * @author    jeson
 * @version   1.0.0
 * @package   lowgame
 */
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require (realpath(dirname(__FILE__) . '/../') . DIRECTORY_SEPARATOR . 'index.php'); // 引入项目入口文件
class cli_initlocks extends basecli
{
    protected function _runCli()
    {
        //检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '.locks';
        if (file_exists($sLocksFileName)) {
            $this->halt('[d] [' . date('Y-m-d H:i:s') . '] The CLI is running');
        }
        file_put_contents($sLocksFileName, "running", LOCK_EX);
        
        // 参数检查 $this->aArgv[1]为生成的总奖期数,$this->aArgv[2]起始奖期
        $iStartIssue = isset($this->aArgv[2]) ? intval($this->aArgv[2]) : 0;
        $iTotalIssue = isset($this->aArgv[1]) ? intval($this->aArgv[1]) : 21;
        if (!is_numeric($iTotalIssue) || $iTotalIssue <= 0){
            @unlink($sLocksFileName);
            die("params are wrong!\n");
        }
        
        $oCommon = new model_common();
        $oLocks = new model_locks();
        
        if ($iStartIssue <= 0){
            //奖期
            $sNowTime = date("Y-m-d H:i:s");
            $sSql = "select * from issueinfo where lotteryid = 1 and salestart < '$sNowTime' order by `salestart` DESC limit 1";
            $aResult = $oCommon->commonGetOne($sSql);
            $iStartIssue = $aResult['issue'];
        }
        $iFutureStartIssue = $iStartIssue + 1;
        $iFutureEndIssue = $iStartIssue + $iTotalIssue;
        
        // 生成封锁表数据， 需要执行前清空相关奖期的封锁数据
        $sSql = "SELECT `locksid`,`lotteryid`,`lockname` FROM `locksname` ";
        $aResult = $oCommon->commonGetAll($sSql);
        
        //生成当期封锁
        foreach ($aResult as $v) {
            $iThread = 5;
            //长度
            switch ($v['locksid']) {
                case 1:
                    $iNumLen = 3;
                    break;
                case 2:
                    $iNumLen = 3;
                    break;
                case 3:
                    $iNumLen = 2;
                    break;
                default:
                    break;
            }
            //生成指定期数的封锁数据
            if (false === $oLocks->crateLocksData($iStartIssue, $iNumLen, $iThread, $v['lockname'])){
                @unlink($sLocksFileName);
                die("table:" . $v['lockname'] . " issue:" . $i . "\n");
            }
            $sFuture = $v['lockname'] . 'future';
            for ($i = $iFutureStartIssue; $i < $iFutureEndIssue; $i++) {
                if (false === $oLocks->crateLocksData($i, $iNumLen, $iThread, $sFuture)){
                    @unlink($sLocksFileName);
                    die("table:" . $v['lockname'] . " issue:" . $i . "\n");
                }
            }
        }
        //生成销量表
        $oLocks->createSalesData(1, $iStartIssue, $iFutureEndIssue);
        $oLocks->createSalesData(2, $iStartIssue, $iFutureEndIssue);
        @unlink($sLocksFileName);
    }
}
$oCli = new cli_initlocks(TRUE);
EXIT();
?>
