<?php
/**
 * 文件 : /_cli/2020_projects.php
 * 功能 : CLI - 方案备份
 * 
 * 调用方式: 2020_project.php i(i:为 彩种ID)
 *
 * @author     Saul
 * @version    1.2.0
 * @package    lowadmin
 */

define('DONT_USE_APPLE_FRAME_MVC', TRUE); // 跳过 MVC 流程
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件
class cli_project extends basecli
{
    protected function _runCli()
    {
        if( !isset($this->aArgv[1]) )
        { //没有指定参数
            echo('Need More Params');
            return FALSE;
        }
        if( !is_numeric($this->aArgv[1]) )
        { //参数错误
            echo('Error Params');
            return FALSE;
        }
        $iLotteryid = $this->aArgv[1];
        if($iLotteryid > 0 )
        { //生成方案备份
            //读取奖期,判断
            $oIssue = new model_issueinfo();
            $aIssue = $oIssue->IssueGetOne("", "`saleend`<now() and `code`='' and `statuscode`='0' and `lotteryid`='".$iLotteryid."' order by `saleend` DESC",'');
            if(!empty($aIssue))
            {
            	// TODO: 这里需要对备份异常进行返回值跟踪. 并记录执行情况 By Tom
                $oProject = new model_projects();
                $oProject->Projectback(    $iLotteryid, $aIssue["issue"], PDIR.DS."_data".DS."projects".DS.$iLotteryid."_".$aIssue["issue"].".gz");
                $oProject->ExtendCodeBack( $iLotteryid, $aIssue["issue"], PDIR.DS."_data".DS."codes".DS.$iLotteryid."_".$aIssue["issue"].".gz");
                $oProject->UserDiffback(   $iLotteryid, $aIssue["issue"], PDIR.DS."_data".DS."userdiff".DS.$iLotteryid."_".$aIssue["issue"].".gz");
                echo "[d] [".date('Y-m-d H:i:s')."] Successed.\n";
                return TRUE;
            }
            else
            {
            	echo "[d] [".date('Y-m-d H:i:s')."] Error.\n";
                return FALSE;
            }
        }
        echo "[d] [".date('Y-m-d H:i:s')."] Error LotteryId ID='$iLotteryid'\n";
        return FALSE;
    }
}
$oCli = new cli_project(TRUE);
?>