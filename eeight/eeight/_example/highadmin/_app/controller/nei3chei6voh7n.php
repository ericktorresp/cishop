<?php
/**
 * 文件 : /_app/controller/viewdraw.php
 *
 * @author    TOM, SAUL, Rojer
 * @version   1.3.0
 * @package   highadmin
 */
class controller_nei3chei6voh7n extends basecontroller
{
    static $lotteryTypes = array(
                    '0' => '数字型',
                    '1' => '乐透分区型（蓝红球）',
                    '2' => '乐透同区型',
                    '3' => '基诺型',
                    '4' => '排列型',
                    '5' => '分组型',
                );
    /**
     * 数字型开奖号码录入
     * URL: ./index.php?controller=draw&action=shuzi
     * @author SAUL,Rojer
     */
    function actionPaif1bai9fey1eef1iphooghohrauwiemioz8ke6vieshae6jo()
    {
        $aLotteryType = isset($_GET['lotteryType']) ? $_GET['lotteryType'] : 0;
        $iLotteryId = isset($_GET['lotteryId']) ? $_GET['lotteryId'] : 0;
        $oLottery = A::singleton("model_lottery");
        $aLotteries = self::array_spec_key($oLottery->getItems($aLotteryType), 'lotteryid');
        // 得到lotteryId
        if (!$iLotteryId)
        {
            $aLottery = reset($aLotteries);
            $iLotteryId = $aLottery['lotteryid'];
        }
        else
        {
            $aLottery = $aLotteries[$iLotteryId];
        }
        $aLocation[0] = array( "text"=>'数字型开奖',"href"=>url('draw','shuzi', array('lotteryId'=>$iLotteryId)) );
        /* @var $oIssueInfo model_issue */
        $oIssueInfo = A::singleton("model_issueinfo");

        // 当前奖期
        //$currentIssue = $oIssueInfo->getCurrentIssue($iLotteryId);
        // 得到未开奖的奖期
        $lastNoDrawIssue = $oIssueInfo->getLastNoDrawIssue($iLotteryId);
        /*
        // 得到所在当天的所有奖期
        $tmp = end($aLottery['issueset']);
        $tmp = time2second($tmp['endtime']);
        if ( $tmp < 36000)
        {
            $tmp += 86400;
        }
        $tmp = strtotime(date('Y-m-d')) + $tmp;
         */

        $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`saleend`<='".date("Y-m-d H:i:s")."'";
        $iPageRecord = isset($_GET["pn"])&&is_numeric($_GET["pn"]) ? intval($_GET["pn"]) : 25;
        $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $aIssue = $oIssueInfo->issueGetList("A.*,B.`cnname`", $sWhere, "order By A.`saleend` DESC",
                    $iPageRecord, $iPage );
        $aPartOfIssue = array_slice($aIssue["results"], 0, 10);

        $oPage = new pages( $aIssue['affects'], $iPageRecord );
        $GLOBALS['oView']->assign( "aLotteries",  $aLotteries );
        $GLOBALS['oView']->assign( "aLottery",  $aLottery );
        $GLOBALS['oView']->assign( "lastNoDrawIssue",  $lastNoDrawIssue );
        $GLOBALS['oView']->assign( "lotteryType",  $aLotteryType );
        $GLOBALS['oView']->assign( "lotteryId",  $iLotteryId );
        $GLOBALS['oView']->assign( "ur_here",   "数字型开奖" );
        $GLOBALS['oView']->assign( "aIssue",    $aIssue["results"] );
        $GLOBALS['oView']->assign( "aPartOfIssue",    $aPartOfIssue );
        $GLOBALS['oView']->assign( "pageinfo",  $oPage->show(1) );
        $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
        $GLOBALS['oView']->assign( "action",    "paif1bai9fey1eef1iphooghohrauwiemioz8ke6vieshae6jo" );
        $oIssueInfo->assignSysInfo();
        $GLOBALS['oView']->display("nei3chei6voh7n.html");
        EXIT;
    }

    /**
     * 乐透同区型开奖
     * @author Rojer
     */
    public function actionZoo6ain0zaavohgheingeisopho4ahkuve8eet3yeimuiv4mi4()
    {
        $aLotteryType = isset($_GET['lotteryType']) ? $_GET['lotteryType'] : 2;
        $iLotteryId = isset($_GET['lotteryId']) ? $_GET['lotteryId'] : 0;
        $oLottery = A::singleton("model_lottery");
        $aLotteries = self::array_spec_key($oLottery->getItems($aLotteryType), 'lotteryid');
        // 得到lotteryId
        if (!$iLotteryId)
        {
            $aLottery = reset($aLotteries);
            $iLotteryId = $aLottery['lotteryid'];
        }
        else
        {
            $aLottery = $aLotteries[$iLotteryId];
        }
        $aLocation[0] = array( "text"=>'乐透同区型开奖',"href"=>url('draw','letouarea') );
        /* @var $oIssueInfo model_issue */
        $oIssueInfo = A::singleton("model_issueinfo");

        // 当前奖期
        //$currentIssue = $oIssueInfo->getCurrentIssue($iLotteryId);
        // 得到未开奖的奖期
        $lastNoDrawIssue = $oIssueInfo->getLastNoDrawIssue($iLotteryId);
        /*
        // 得到所在当天的所有奖期
        $tmp = end($aLottery['issueset']);
        $tmp = time2second($tmp['endtime']);
        if ( $tmp < 36000)
        {
            $tmp += 86400;
        }
        $tmp = strtotime(date('Y-m-d')) + $tmp;
         */

        $sWhere = " A.`lotteryid`='".$iLotteryId."' AND  A.`saleend`<='".date("Y-m-d H:i:s")."'";
        $iPageRecord = isset($_GET["pn"])&&is_numeric($_GET["pn"]) ? intval($_GET["pn"]) : 25;
        $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $aIssue = $oIssueInfo->issueGetList("A.*,B.`cnname`", $sWhere, "order By A.`saleend` DESC",
                    $iPageRecord, $iPage );
        $aPartOfIssue = array_slice($aIssue["results"], 0, 10);

        $oPage = new pages( $aIssue['affects'], $iPageRecord );
        $GLOBALS['oView']->assign( "aLotteries",  $aLotteries );
        $GLOBALS['oView']->assign( "aLottery",  $aLottery );
        $GLOBALS['oView']->assign( "lastNoDrawIssue",  $lastNoDrawIssue );
        $GLOBALS['oView']->assign( "lotteryType",  $aLotteryType );
        $GLOBALS['oView']->assign( "lotteryId",  $iLotteryId );
        $GLOBALS['oView']->assign( "ur_here",   "乐透同区型开奖" );
        $GLOBALS['oView']->assign( "aIssue",    $aIssue["results"] );
        $GLOBALS['oView']->assign( "aPartOfIssue",    $aPartOfIssue );
        $GLOBALS['oView']->assign( "pageinfo",  $oPage->show(1) );
        $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
        $GLOBALS['oView']->assign( "action",    "zoo6ain0zaavohgheingeisopho4ahkuve8eet3yeimuiv4mi4" );
        $oIssueInfo->assignSysInfo();
        $GLOBALS['oView']->display("nei3chei6voh7n.html");
        EXIT;
    }

    /**
     * 乐透分区型开奖
     * @author Rojer
     */
    public function actionLetoudiffarea()
    {
        echo '乐透分区型开奖待续。。。';
        die();
    }


    // 以下应放到公用函数库
    static private function array_spec_key($array, $key, $unset_key = false)
    {
        if (empty($array) || !is_array($array))
        {
            return array();
        }

        $new_array = array();
        foreach ($array AS $value)
        {
            if (!isset($value[$key]))
            {
                continue;
            }
            $value_key = $value[$key];
            if ($unset_key === true)
            {
                unset($value[$key]);
            }
            $new_array[$value_key] = $value;
        }

        return $new_array;
    }

    // 以下应放到公用函数库
    function time2second($str)
    {
        $tmp = explode(':', $str);
        return $tmp[0] * 3600 + $tmp[1] * 60 + $tmp[2];
    }

    function second2time($second)
    {
        $result['hour'] = intval($second / 3600);
        $second -= $result['hour'] * 3600;
        $result['minute'] = intval($second / 60);
        $result['second'] = $second - $result['minute'] * 60;

        return $result['hour'] . ':' . $result['minute'] . ':' . $result['second'];
    }
}
?>