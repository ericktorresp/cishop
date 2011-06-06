<?php
/**
 * 文件 : /_app/controller/report.php
 * 功能 : 控制器 - 报表管理
 *
 * 功能:
 *
 * + actionList 频道帐变
 * 
 * @author    saul  090914
 * @version   1.2.0
 * @package   lowgame  
 */
class controller_report extends basecontroller 
{
    /**
    * 频道帐变
    * URL:index.php?controller=report&action=list
    * @author: SAUL
    */
    function actionList()
    {
        $oMethod  = new model_method();
        //玩法
        $aMethod  = $oMethod->methodGetList( " a.`methodid`,a.`methodname`,a.`lotteryid`,b.`cnname` ",
                                             " a.`pid`>0 ", "", "", 0 );
        $aLottery = array();
        $aMethods = array();
        foreach( $aMethod as $method )
        {
            $aLottery[$method["lotteryid"]]                      = $method["cnname"];
            $aMethods[$method["lotteryid"]][$method["methodid"]] = $method;
        }
        $oIssue = new model_issueinfo();
        foreach( $aLottery as $iLottery=>$lottery )
        {
            $aIssue[$iLottery] = $oIssue->issueGetList(" A.`issue`,date(A.`saleend`) AS dateend ",
                                                       " B.`lotteryid`='".$iLottery."'"
                                                      ." AND UNIX_TIMESTAMP(A.`salestart`)<UNIX_TIMESTAMP(now()) ",
                                                       " A.`saleend` DESC LIMIT 0,10 ", 0 );
        }
        //参数整理
        $sWhere ="";
        if( isset($_GET["starttime"]) && !empty($_GET["starttime"]) )
        {
            $sStartTime = getFilterDate($_GET["starttime"]);
        }
        else
        {
        	// 6/9/2010
        	if ( date("Y-m-d H:i:s", strtotime("NOW") ) < date("Y-m-d 02:20:00") )
        	{
        		$sStartTime = getFilterDate( date("Y-m-d 02:20:00", strtotime("-1 days")  ) );
        	}
        	else
        	{
            	$sStartTime = getFilterDate( date("Y-m-d 02:20:00") );
        	}
        }
        if( $sStartTime != "" )
        {
            $sWhere .= " AND O.`times` >'".$sStartTime."'";
        }
        $aHtml["starttime"] = $sStartTime;
        if( isset($_GET["endtime"]) && !empty($_GET["endtime"]) )
        {
            $sEndTime = getFilterDate($_GET["endtime"]);
        }
        else
        {
        	// 6/9/2010
        	if ( date("Y-m-d H:i:s", strtotime("NOW") ) < date("Y-m-d 02:20:00") )
        	{
        		$sEndTime = getFilterDate( date("Y-m-d 02:20:00"  ) );
        	}
        	else
        	{
            	$sEndTime = getFilterDate( date("Y-m-d 02:20:00",strtotime("+1 days")) );
        	}
        }
        if( $sEndTime != "" )
        {
            $sWhere          .= " AND O.`times`<'".$sEndTime."'";
            $aHtml["endtime"] = $sEndTime;
        }
        if( isset($_GET["lotteryid"]) && is_numeric($_GET["lotteryid"]) )
        {
            $iLotteryId = $_GET["lotteryid"];
            if( $iLotteryId>0 )
            {
                $aHtml["lotteryid"] = $iLotteryId;
                $sWhere            .= " AND O.`lotteryid`='".$iLotteryId."' ";
                if( isset($_GET["methodid"]) && is_numeric($_GET["methodid"]) )
                {
                    $iMethodId = $_GET["methodid"];
                    if( $iMethodId > 0 )
                    {
                        $aHtml["methodid"]   = $iMethodId;
                        $sWhere             .= " AND O.`methodid`='".$iMethodId."' ";
                    }
                }
                if( isset($_GET["issue"]) )
                {
                    $sIssue = $_GET["issue"];
                    if($sIssue != "0")
                    {
                        $aHtml["issue"]  = $sIssue;
                        $sWhere         .= " AND P.`issue`='".$sIssue."' ";
                    }
                }
            }
        }
        //帐变类型
        $oOrders = new model_orders( $GLOBALS['aSysDbServer']['report'] );
        $aType   = $oOrders->getOrderType( "arr", "", " AND `displayforuser`='1'" );
        $GLOBALS['oView']->assign("type", $aType);
        if( isset($_GET["ordertype"]) && is_numeric($_GET["ordertype"]) )
        {
            $iOrderType = intval( $_GET["ordertype"] );
            if( $iOrderType > 0 )
            {
                $aHtml["ordertype"]  = $iOrderType;
                $sWhere             .= " AND O.`ordertypeid`='".$iOrderType."' ";
            }
        }
        
        //下面是进行编号处理
        if( isset($_GET["type"]) && is_numeric($_GET["type"]) )
        {
            $iType          = intval($_GET["type"]);
            $aHtml["type"]  = $iType;
            if( $iType == 1 )
            {
                if( isset($_GET["code"]) && !empty($_GET["code"]) )
                {
                    $iCode = model_projects::ProjectEnCode( $_GET["code"], "DECODE" );
                    if( $iCode > 0 )
                    {
                        $aHtml["code"]  = stripslashes_deep($_GET["code"]);
                        $sWhere         = " AND O.`projectid`='".$iCode."' ";
                    }
                }
            }
            elseif( $iType == 2 )
            {
                if( isset($_GET["code"]) && !empty($_GET["code"]) )
                {
                    $iCode = model_task::TaskEnCode( $_GET["code"], "DECODE" );
                    if( $iCode > 0 )
                    {
                        $aHtml["code"]  = stripslashes_deep($_GET["code"]);
                        $sWhere         = " AND O.`taskid`='".$iCode."' ";
                    }
                }
            }
            elseif( $iType == 3 )
            {
                if( isset($_GET["code"]) && !empty($_GET["code"]) )
                {
                    $iCode = model_orders::orderEnCode( $_GET["code"], "DECODE" );
                    if( $iCode > 0 )
                    {
                        $aHtml["code"]  = stripslashes_deep($_GET["code"]);
                        $sWhere         = " AND O.`entry`='".$iCode."' ";
                    }
                }
            }
        }
        $sWhere  .= " AND O.`fromuserid`='".intval($_SESSION["userid"])."' "; //限制为自身用户
        $iPage    = isset($_GET["p"]) && is_numeric($_GET["p"]) ? intval($_GET["p"]) : 1;
        $aOrders  = $oOrders->userOrderList( 0, TRUE, "", $sWhere, " ORDER BY O.`entry` DESC", 25, $iPage );
        $fPageTotal = 0.00;
        foreach( $aOrders["results"] as & $orders )
        {
            $fPageTotal += $orders["operations"] == 0 ? -$orders['amount'] : $orders['amount'];
            $orders["entry"] = model_orders::orderEnCode( "O".date("Ymd",strtotime($orders["times"])).
                                                          "-".$orders["entry"], "ENCODE" );
            if( intval($orders["projectid"]) > 0 )
            {
                $orders["projectid"] = model_projects::ProjectEnCode( "D".$orders["issue"]."-".
                                                                      $orders["projectid"], "ENCODE" );
            }
        }
        $GLOBALS["oView"]->assign( "aOrders",    $aOrders["results"] );
        $GLOBALS['oView']->assign( "pagetotal", $fPageTotal );
        $GLOBALS["oView"]->assign( "data_issue", json_encode($aIssue) );
        $GLOBALS['oView']->assign( "lottery",    $aLottery );
        $GLOBALS['oView']->assign( "s",          $aHtml );
        $oPage = new pages($aOrders["affects"],25);
        $GLOBALS['oView']->assign( "pageinfo",   $oPage->show(1) );
        $GLOBALS['oView']->assign( "data_method",json_encode($aMethods));
        $GLOBALS['oView']->assign( "actionlink", array('text'=>'清空搜索条件','href'=>url('report','list')));
        $GLOBALS['oView']->assign( "ur_here",    "频道帐变");
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display("report_list.html");
        EXIT;
    }
}
?>
