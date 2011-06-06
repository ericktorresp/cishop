<?php
/**
 * 路径: /_cli/0430_createlock.php
 * 功能: 创建封锁表及生成初始数据模型
 *       彩种ID作为参数：按彩种进行封锁数据的初始化
 *       
 * 
truncate table lock_cqssc_budingwei;
truncate table lock_cqssc_daxiaodanshuang;
truncate table lock_cqssc_dingweidan;
truncate table lock_cqssc_erma;
truncate table lock_cqssc_hszhixuan;
truncate table lock_cqssc_hszhuxuan;
truncate table lock_cqssc_qszhixuan;
truncate table lock_cqssc_qszhuxuan;

 * @author    mark,Rojer
 * @version   1.0.0
 * @package   highadmin
 */
if( !isset($argv[1]) )
{
    echo 'please input the lotteryid.';
    die();
}
$iLotteryId = intval($argv[1]);
if( $iLotteryId <= 0 || !is_numeric($iLotteryId))
{
    echo 'this lotteyid is not correct.';
    die();
}
//初始化配置
define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class cli_createlock extends basecli
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
            @unlink( $this->_getBaseFileName() . '_id_'. $this->aArgv[1]. '.locks' );
        }
    }

    /**
     * 重写基类 _runCli() 方法, 程序主流程
     */
    protected function _runCli()
    {
       //彩种ID
       global $iLotteryId;
       
       //检查是否已有相同CLI在运行中
       $sLocksFileName = $this->_getBaseFileName() . '_id_'. $iLotteryId. '.locks';
       if( file_exists( $sLocksFileName ) )
       { // 如果有运行的就终止本个CLI
           $this->halt( '[d] [' . date('Y-m-d H:i:s') .'] The CLI is running');
       }
       file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁
       
       //需要生成的奖期
       $sSartDay = date("Y-m-d", time());
       $sEndDay  = date("Y-m-d", time() + 86400);
       $oIssueInfo = new model_issueinfo();
       $aIssue = $oIssueInfo->getItems($iLotteryId, array($sSartDay, $sEndDay));
       $aTodayIssue = $oIssueInfo->getItems($iLotteryId, $sSartDay);//今天的奖期
       $aTomorrowIssue = $oIssueInfo->getItems($iLotteryId, $sEndDay);//明天的奖期
       if(empty($aIssue) || empty($aTodayIssue) || empty($aTomorrowIssue) || count($aTodayIssue) != count($aTomorrowIssue) )
       {
           echo "this issue message is not exist!\n";
           $this->bDoUnLock = TRUE;
           return FALSE;
       }
       //一天的奖期个数
       $iOneDayIssueCount = intval(count($aIssue)/2);
       //两天的奖期个数
       $iTwoDayIssueCount = count($aIssue);
       //第二天的奖期
       $aSecondDayIssue = array_slice( $aIssue, $iOneDayIssueCount );
       //获取各个玩法封锁表名和封锁初始化函数名
       $oMethod = new model_method();
       $aMethod = $oMethod->methodGetList( "`methodid`,`lockname`,`initlockfunc`", "a.`lotteryid`='".$iLotteryId."' AND a.`pid`!=0");
       $oCreateLock = new model_createlock();
       $oLock = new model_locks();
       //获取彩种信息
       $oLottery = new model_lottery();
       $aLottery = $oLottery->lotteryGetOne('`numberrule`,`lotterytype`'," `lotteryid` = '" .$iLotteryId. "'");
       $aNumberRule = unserialize($aLottery['numberrule']);//号码规则
       //创建封锁表并初始化封锁数据
       foreach ($aMethod as $aMehtodDetail)
       {
           $sLockTableName = $aMehtodDetail['lockname'];
           $sInitFun       = $aMehtodDetail['initlockfunc'];
           $iMethodId      = $aMehtodDetail['methodid'];
           $aIniFun = explode( ',', $sInitFun );
           if( count($aIniFun) > 1)
           {
               $aNumberRule['specialvalue'] = $aIniFun[1];
               $sInitFun = $aIniFun[0];
           }
           if( $sInitFun == '' || $sLockTableName == '' || !method_exists($oCreateLock, $sInitFun) )
           {
               continue;
           }
           $bFlag = $oLock->lockTableTransfer($sLockTableName, $aSecondDayIssue);
           echo "locktable".$sLockTableName;
           echo $bFlag ? "transfer success\n" : "transfer fail\n";
           echo "*******************************************\n";
           $iCheckData = $oLock->checkCurrentDayLockData( $sLockTableName, $iMethodId);
           if( $iCheckData == $iTwoDayIssueCount )
           {
               //如果存在了两天的数据不操作封锁表
               $aTmpIssue = array();
           }
           elseif( $iCheckData == $iOneDayIssueCount )
           {
               //如果存在了当天的数据，只插入第二天的数据
               $aTmpIssue = $aSecondDayIssue;
           }
           else 
           {    //如果不存在数据，第一次执行插入两天的数据
               $aTmpIssue = $aIssue;
           }
           if(!empty($aIssue))
           {
               $t1 = microtime(true);
               $ar1 = $oCreateLock->$sInitFun( $sLockTableName, $aTmpIssue, $aNumberRule, $iMethodId );
               $t2 = microtime(true) - $t1;
           }
           $ar1 = isset($ar1) ? $ar1 : 0;
           $t2  = isset($t2) ?  $t2 : 0;
           echo "locktable: $sLockTableName initdata $ar1 datas,use $t2 seconds\n";
           unset($ar1);
           unset($t2);
           unset($aNumberRule['specialvalue']);
           // 一个玩法的一些奖期的销量初始值
           $t1 = microtime(true);
           $ar2 = $oCreateLock->initSales($iLotteryId, $aIssue, $sLockTableName);
           $t3 = microtime(true) - $t1;
           echo "saletable initdata $ar2 datas,use $t3 seconds\n";
           unset($aTmpIssue);
       }
       $sWhereStatusLocks = " (`belongdate` BETWEEN '".$sSartDay."' AND '".$sEndDay."') ";
       $bStatusLocks = $oIssueInfo->updateIssueStatusLocks($iLotteryId, $sWhereStatusLocks, 2);
       echo "createlockdata success update issue $bStatusLocks datas!\n";
       $this->bDoUnLock = TRUE;
    }
    
}
$oCli = new cli_createlock(TRUE); // 生产版本可以考虑改为 FALSE, 关闭调试
EXIT;
?>