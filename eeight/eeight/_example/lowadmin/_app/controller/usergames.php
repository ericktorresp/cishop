<?php
/**
 * 文件 : /_app/controller/usergames.php
 * 功能 : 控制器 - 用户游戏记录
 * 
 * 功能:
 * + actionGameinfo
 * + actionTraceinfo
 * + actionProjectdetail
 * + actionProjectcancel
 * + actionTaskdetail
 * + actionCancelTask
 *
 * @author      JAMES, SAUL
 * @version     1.2.0 
 * @package     lowadmin
 */

class controller_usergames extends basecontroller
{
    /**
     * 用户参与的游戏记录
     * @author SAUL
     * URL = ./index.php?controller=usergames&action=gameinfo
     */
    function actionGameinfo()
    {
        if(isset($_POST)&&!empty($_POST))
        {
            $sWhere = "";
            //最早日期
            if(isset($_POST['starttime'])&&!empty($_POST['starttime']))
            {
                $sStartTime = getFilterDate($_POST["starttime"]);
                if( $sStartTime != "" )
                {
                    $sWhere .= "AND P.`writetime`>='".$sStartTime."'";
                }
            }
            //结束日期
            if(isset($_POST['endtime'])&&!empty($_POST["endtime"]))
            {
                $sEndTime = getFilterDate($_POST["endtime"]);
                if( $sEndTime!="" )
                {
                    $sWhere .="AND P.`writetime`<='".$sEndTime."'";
                }
            }
            //彩种
            $iLotteryId = isset($_POST["lotteryid"])&&is_numeric($_POST["lotteryid"]) ? intval($_POST["lotteryid"]): 0; 
            if( $iLotteryId>0 )
            {
                $sWhere .=" AND P.`lotteryid`='".$iLotteryId."'";
                //玩法  + 彩种期数
                $iMethodid = isset($_POST["methodid"])&&is_numeric($_POST["methodid"]) ? intval($_POST["methodid"]): 0;
                if( $iMethodid > 0 )
                {
                    $sWhere .=" AND P.`methodid`='".$iMethodid."'";
                }
                $sIssueId = isset($_POST["issueid"])? daddslashes($_POST["issueid"]) : "-1";
                if($sIssueId != '-1')
                {
                    $sWhere .=" AND P.`issue`='".$sIssueId."'";
                }
            }
            $iUserType = isset($_POST["type"])&&is_numeric($_POST["type"]) ? intval($_POST["type"]) : 0;
            if( $iUserType==1 )
            { //总代
                $iUserId = intval($_POST["proxyid"]);
                $bAllChild = true;
            }
            else if( $iUserType==2 )
            {   //用户以及下级//还是已经指定了总代
                if( isset($_POST["username"])&&!empty($_POST["username"]) )
                { // 对用户 中 “*”的支持
                    $sUsername = $_POST["username"]; 
                    if( strpos($sUsername,"*")===FALSE )
                    { //不包含用户“*”
                        /* @var $oUser model_user */
                        //$oUser = A::singleton("model_user");
                        $oUser = new model_user($GLOBALS['aSysDbServer']['report']);
                        $iUserId = $oUser->getUseridByUsername($sUsername);
                        if( $iUserId ==0 )
                        {
                            echo json_encode(array("affects"=>0));
                            EXIT;
                        }
                        $include =isset($_POST["include"])&&is_numeric($_POST["include"])? intval($_POST["include"]) : 0;
                        $bAllChild =( $include == 1 ); 
                    }
                    else
                    { //支持用户“*”
                        $iUserId   = 0;
                        $bAllChild = FALSE;
                        $sWhere   .= " AND UT.`username` like '".str_replace("*","%",$sUsername)."'";
                    }
                }
                else
                {
                    $iUserId   = 0;
                    $bAllChild = TRUE;
                }
            }
            //注单编号
            if(isset($_POST["projectno"])&&!empty($_POST["projectno"]))
            {
                //对注单编号进行解码
                $iUserId    = 0;
                $bAllChild  = TRUE;
                $sWhere     = " AND P.`projectid`='".model_projects::ProjectEnCode($_POST["projectno"],"DECODE")."'";
            }
            /* @var $oProject model_projects */
            //$oProject = A::singleton("model_projects");
            $oProject = new model_projects($GLOBALS['aSysDbServer']['report']);
            $iPage = isset($_POST["page"])&&is_numeric($_POST["page"])?intval($_POST["page"]):1;
            $pn = isset($_POST['pn']) ? intval($_POST['pn']) : 25;
        	$searchpn = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25;
						
            $aProject = $oProject->projectGetResult( $iUserId, $bAllChild, "", $sWhere, 
                             "P.`projectid` DESC", $searchpn, $iPage );
            foreach ($aProject["results"] as &$project)
            {
                $project["projectid"] = $oProject->ProjectEnCode("D".$project['issue']."-".$project['projectid'],"ENCODE");
            }
            echo json_encode($aProject);
            EXIT;
        }
        else
        {
        	$pn = isset($_POST['pn']) ? intval($_POST['pn']) : 25;
        	$searchpn = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25; 
						
            /* @var $oMethod model_method */
            //$oMethod = A::singleton("model_method");
            $oMethod = new model_method($GLOBALS['aSysDbServer']['report']);
            $aMethod = $oMethod->methodGetList("a.`methodname`,a.`methodid`,b.`cnname`,b.`lotteryid`",
                         "a.`pid`>0", '', 0);
            $aLottery = array();
            $aMethods = array();
            foreach($aMethod as $method)
            {
                $aLottery[$method["lotteryid"]] = $method["cnname"];
                $aMethods[$method["lotteryid"]][] = array(
                    "methodid"=>$method["methodid"],
                    "methodname"=>$method["methodname"]
                );
            }
            $GLOBALS['oView']->assign( "lottery",     $aLottery );
            $GLOBALS['oView']->assign( "data_method", json_encode($aMethods) );
            /* @var $oIssue model_issueinfo */
            //$oIssue = A::singleton("model_issueinfo");
            $oIssue = new model_issueinfo($GLOBALS['aSysDbServer']['report']);
            $aIssue = array();
            foreach( $aLottery as $iLotteryid=>$aTemp )
            {
               $aIssue[$iLotteryid] = $oIssue->issueGetList(" A.`issue`,DATE(A.`saleend`) AS dateend ",
                                                            " B.`lotteryid`='".$iLotteryid."' "
                                                           ." AND UNIX_TIMESTAMP(A.`salestart`)<UNIX_TIMESTAMP(now()) ",
                                                            " A.`saleend` DESC LIMIT 0,10", 0 );
            }
            /* @var $oUser model_usertree */
            //$oUser = A::singleton("model_usertree");
            $oUser = new model_usertree($GLOBALS['aSysDbServer']['report']);
            $aUser = $oUser->userAgentget();
            $GLOBALS['oView']->assign("topproxy",$aUser);
            $GLOBALS['oView']->assign("searchpn", $searchpn);
            $GLOBALS['oView']->assign("data_issue",json_encode($aIssue));
            $GLOBALS["oView"]->assign("ur_here","参与游戏信息");
            $oMethod->assignSysInfo();
            $GLOBALS['oView']->display("usergames_gameinfo.html");
            EXIT;
        }
    }



    /**
     * 用户参与的追号记录
     * @author SAUL
     * URL = ./index.php?controller=usergame&action=traceinfo
     */
    function actionTraceinfo()
    {
        if( isset($_POST)&&!empty($_POST) )
        {
            $sWhere    = "";
            $startTime = "";
            if(isset($_POST["starttime"])&&!empty($_POST["starttime"]))
            {
                $startTime = getFilterDate($_POST["starttime"],"Y-m-d H:i:s");
            }
            $endTime = "";
            if(isset($_POST["endtime"])&&!empty($_POST["endtime"]))
            {
                $endTime = getFilterDate($_POST["endtime"],"Y-m-d H:i:s");
            }
            if(!empty($startTime) && !empty($endTime))
            {
                $sWhere .= " AND T.`begintime` between '".$startTime."' and '".$endTime."'";
            }
            elseif(!empty($startTime))
            {
                $sWhere .= " AND T.`begintime`>'".$startTime."'"; 
            }
            elseif(!empty($endTime))
            {
                $sWhere .= " AND T.`begintime`<'".$endTime."'";
            }
            $iLottery = isset($_POST["lotteryid"])&&is_numeric($_POST["lotteryid"])?intval($_POST["lotteryid"]):0;
            if( $iLottery>0 )
            {
                $sWhere .=" AND T.`lotteryid`='".$iLottery."'";
                $iMethod = isset($_POST["methodid"])&&is_numeric($_POST["methodid"]) ? intval($_POST["methodid"]):0;
                if( $iMethod>0 )
                {
                    $sWhere .=" AND T.`methodid`='".$iMethod."'";
                }
                $sIssue = isset($_POST["issueid"])?daddslashes($_POST["issueid"]):"-1";
                if( $sIssue !="-1" )
                {
                    $sWhere .=" AND T.`beginissue`='".$sIssue."'";
                }
            }
            $iStatus = isset($_POST["taskstatus"])&&is_numeric($_POST["taskstatus"])?intval($_POST["taskstatus"]):-1;
            if( $iStatus >-1 )
            {
                $sWhere .= " AND T.`status`='".$iStatus."'";
            }
            //用户整理
            $iUserId = 0;
            $bAllChild = TRUE;
            $iType = isset($_POST["type"])&&is_numeric($_POST["type"])?intval($_POST["type"]):0;
            if($iType==1)
            { //总代ID
                $iProxy = isset($_POST["proxy"])&&is_numeric($_POST["proxy"])?intval($_POST["proxy"]):0;
                if($iProxy>0)
                { //总代>0,强制默认为总代包含所有下级
                    $iUserId = $iProxy;
                    $bAllChild = TRUE;
                }
                else
                {
                    $iUserId = 0;
                    $bAllChild = TRUE;
                }
            }
            elseif($iType==2)
            { //用户名称,需要手工指定
                if(isset($_POST["username"])&&!empty($_POST["username"]))
                { // 对用户 中 “*”的支持
                    $sUsername = $_POST["username"]; 
                    if(strpos($sUsername,"*")===FALSE)
                    { //不包含用户“*”
                        /* @var $oUser model_user */
                        //$oUser = A::singleton("model_user");
                        $oUser = new model_user($GLOBALS['aSysDbServer']['report']);
                        $iUserId = $oUser->getUseridByUsername($sUsername);
                        if($iUserId == 0 )
                        {
                            echo json_encode(array("affects"=>0));
                            exit;
                        }
                        $include =isset($_POST["include"])&&is_numeric($_POST["include"])?intval($_POST["include"]):0;
                        $bAllChild = ($include ==1 );
                    }
                    else
                    { //支持用户“*”
                        $iUserId = 0;
                        $bAllChild =FALSE;
                        $sWhere .=" AND UT.`username` like '".str_replace("*","%",$sUsername)."'";
                    }
                }
                else
                {
                    $iUserId = 0;
                    $bAllChild = TRUE;
                }
            }
            //单子ID号码整理
            if( isset($_POST["taskid"])&&!empty($_POST["taskid"]))
            {
                $taskid = model_task::TaskEnCode($_POST["taskid"],"DECODE");
                $iUserId = 0;
                $bAllChild = TRUE;
                $sWhere = " AND T.`taskid`='".$taskid."'";
            }
            //分页的页面支持
            /* @var $oTask model_task */
            //$oTask = A::singleton("model_task");
            $oTask = new model_task($GLOBALS['aSysDbServer']['report']);
            $iPage = isset($_POST["page"])&&is_numeric($_POST["page"])?intval($_POST["page"]):1;
            $pn = isset($_POST['pn']) ? intval($_POST['pn']) : 25;
        	$searchpn = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25;
            $aTask = $oTask->taskgetList($iUserId, $bAllChild, "", $sWhere, " T.`taskid` DESC",
                              $searchpn, $iPage);
            foreach($aTask["results"] as $iTaskid=>$task)
            {
                $aTask["results"][$iTaskid]["taskid"] = model_task::TaskEnCode("T".$task["beginissue"]."-".$task["taskid"],"ENCODE");
            }
            echo json_encode($aTask);
            EXIT;
        }
        else
        {
        	$pn = isset($_POST['pn']) ? intval($_POST['pn']) : 25;
        	$searchpn = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25; 
						
            /* @var $oMethod model_method */
            //$oMethod = A::singleton("model_method");
            $oMethod = new model_method($GLOBALS['aSysDbServer']['report']);
            $aMethod = $oMethod->methodGetList("a.`methodname`,a.`methodid`,b.`cnname`,b.`lotteryid`","a.`pid`>0",'',0);
            $aLottery = array();
            $amethods = array();
            foreach($aMethod as $method)
            {
                $aLottery[$method["lotteryid"]] = $method["cnname"];
                $amethods[$method["lotteryid"]][] = array(
                    "methodid"=>$method["methodid"],
                    "methodname"=>$method["methodname"]
                );
            }
            $GLOBALS['oView']->assign("lottery",$aLottery);
            $GLOBALS['oView']->assign("data_method",json_encode($amethods));
            /* @var $oIssue model_issueinfo */
            //$oIssue = A::singleton("model_issueinfo");
            $oIssue = new model_issueinfo($GLOBALS['aSysDbServer']['report']);
	        $aIssue = array();
	        foreach( $aLottery as $iLottery=>$temp )
	        {
	            $aIssue[$iLottery] = $oIssue->issueGetList( " A.`issue`,date(A.`saleend`) AS dateend ",
	                                                        " B.`lotteryid`='".$iLottery."'"
	                                                       ." AND UNIX_TIMESTAMP(A.`salestart`)<UNIX_TIMESTAMP(now()) ",
	                                                        " A.`saleend` DESC limit 0,10", 0 );
	        }
            $GLOBALS['oView']->assign("data_issue",json_encode($aIssue));
            $GLOBALS["oView"]->assign("ur_here","查看追号记录");
            $GLOBALS['oView']->assign("searchpn", $searchpn);
            /* @var $oUser model_user */
            //$oUser = A::singleton("model_user");
            $oUser = new model_user($GLOBALS['aSysDbServer']['report']);
            $aUser = $oUser->getChildList(0,"ut.`userid`,ut.`username`"," AND ut.`parentid`='0'",'',$searchpn);
            $GLOBALS["oView"]->assign("aUser",$aUser["results"]);
            $oMethod->assignSysInfo();
            $GLOBALS['oView']->display("usergames_traceinfo.html");
            EXIT;
        }
    }



    /**
     * 方案详情
     * @author SAUL
     * URL:./index.php?controller=usergames&action=projectdetail
     */
    function actionProjectdetail()
    {
        $aLocation[0] = array("text"=>'关闭',"href"=>'javascript:self.close();');
        if( isset($_GET["id"])&&!empty($_GET["id"]) )
        {
            $iProjectId = model_projects::ProjectEnCode($_GET["id"],"DECODE");
            if( $iProjectId==0 )
            {
                sysMessage('方案不存在',1, $aLocation );
            }
            /* @var $oProjects model_projects */
            $oProjects = A::singleton("model_projects");
            $aProjects = $oProjects->projectGetResult(0, true, "", " and P.`projectid`='".$iProjectId."'","",0);
            if(empty($aProjects))
            {
                sysMessage('方案不存在', 1, $aLocation );
            }
	        //注单编号
	        if(intval($aProjects[0]["taskid"])>0)
	        {
	            $oTask = new model_task();
	            $aTask = $oTask->taskgetList(0,TRUE,"T.`taskid`,T.`beginissue`"," and T.`taskid`='".$aProjects[0]["taskid"]."'","",0);
	            $aProjects[0]["taskid"] = model_task::TaskEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE");  
	        }
            $aProjects[0]["code"] = wordwrap(str_replace( array("B","S","A","D","|"),array("大","小","单","双",", "), $aProjects[0]["code"]),100,"<br/>");
            $aProjects[0]["projectid"] = $oProjects->ProjectEnCode("D".$aProjects[0]["issue"]."-".$aProjects[0]["projectid"],"ENCODE");
            $GLOBALS['oView']->assign("ur_here","查看注单详情");
            $GLOBALS['oView']->assign("aProject",$aProjects[0]);
            
            //获取扩展号码 2009-11-19 17:18 By tom
			$sCondition          = " `projectid`='".$iProjectId."' ";
			$aPrizelevel         = $oProjects->getExtendCode( "*", $sCondition, "`isspecial` ASC", 0 );
			
			foreach($aPrizelevel as &$prizelevel)
			{
				$prizelevel["singleprize"] = $prizelevel["prize"] / $prizelevel["codetimes"];
			    $prizelevel["expandcode"] = wordwrap( str_replace(array("|","#"), array(", ","|"), 
			                                          $prizelevel["expandcode"]), 100, "<br>" );
			}
			$GLOBALS['oView']->assign("prizelevel",$aPrizelevel);
			
            $oProjects->assignSysInfo();
            $GLOBALS['oView']->display('usergames_projectdetail.html');
            EXIT;
        }
        else
        {
            sysMessage( '方案不存在', 1, $aLocation );
        }
    }



    /**
     * 后台撤单
     * @author JAMES
     * URL:./index.php?controller=usergames&action=projectcancel
     */
    function actionProjectcancel()
    {
        $sProjectNo   = !empty($_GET["id"]) ? $_GET["id"] : "";
        $aLocation[0] = array("text"=>'查看方案详情',"href"=>url('usergames', 'projectdetail', array('id'=>$sProjectNo)));
        $iProjectId   = !empty($sProjectNo) ? model_projects::ProjectEnCode($sProjectNo, "DECODE") : 0;
        if( $iProjectId == 0 )
        {
            sysMessage( '操作错误', 1, $aLocation );
        }
        if( empty($_GET['uid']) || !is_numeric($_GET['uid']) )
        {
            sysMessage( '操作错误', 1, $aLocation );
        }
        $iUserId = intval($_GET['uid']);
        /* @var $oGame model_gamemanage */
        $oGame = A::singleton("model_gamemanage");
        $mResult = $oGame->cancelProject( $iUserId, $iProjectId, $_SESSION['admin'] );
        if( $mResult === TRUE )
        {
            sysMessage( '撤单成功', 0, $aLocation );
        }
        else
        {
            sysMessage( $mResult, 1, $aLocation );
        }
    }



    /**
     * 追号详情
     * @author SAUL
     * URL: ./index.php?controller=usergame&action=taskdetail
     */
    function actionTaskdetail()
    {
        $aLocation[0] = array("text"=>"关闭","href"=>'javascript:close();');
        $sTaskId = isset($_GET["id"])&&!empty($_GET["id"]) ? daddslashes($_GET["id"]) : "";
        if( $sTaskId=="" )
        {
            sysMessage( '参数错误', 1, $aLocation );
        }
        /* @var $oTask model_task */
        $oTask = A::singleton("model_task");
        $iTaskId = $oTask->TaskEnCode( $sTaskId, "DECODE" );
        if( $iTaskId==0 )
        {
            sysMessage( '参数错误', 1, $aLocation );
        }
        $aTask = $oTask->taskgetList(0, TRUE, "", " AND T.`taskid`='".$iTaskId."'", "", 0);	
        if( empty($aTask[0]) )
        {
            sysMessage( '追号单不存在', 1, $aLocation);
        }
        $aTask[0]["codes"]  = wordwrap(str_replace( array("B","S","A","D","|"),array("大","小","单","双",", "), $aTask[0]["codes"]),100,"<br/>");
        $aTask[0]["taskid"] = model_task::TaskEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE");
        $aTaskDetail = $oTask->taskdetailGetList( $iTaskId, $aTask[0]["lotteryid"] ); 
        foreach( $aTaskDetail as &$aDetail )
        {
            if( $aDetail["projectid"]>0 )
            { //注单详情
                $aDetail["projectid"] = model_projects::ProjectEnCode( "D".$aDetail["issue"]."-".$aDetail["projectid"] , "ENCODE" );
            }
        }
        $GLOBALS["oView"]->assign("task", $aTask[0] );
        $GLOBALS['oView']->assign("aTaskdetail",$aTaskDetail);
        $GLOBALS['oView']->assign("ur_here","查看追号详情");
        $oTask->assignSysInfo();
        $GLOBALS['oView']->display("usergames_taskdetail.html");
        EXIT;
    }



    /**
     * 追号单撤单
     * @author JAMES
     * URL: ./index.php?controller=usergames&action=canceltask
     */
    function actionCancelTask()
    {
        $sTaskNo      = !empty($_POST["id"]) ? $_POST["id"] : "";
        $aLocation[0] = array("text"=>'查看追号详情',"href"=>url('usergames', 'taskdetail', array('id'=>$sTaskNo)));
        $iTaskId      = !empty($sTaskNo) ? model_projects::ProjectEnCode($sTaskNo, "DECODE") : 0;
        if( $iTaskId == 0 )
        {
            sysMessage( '权限不足', 1, $aLocation );
        }
        $aId = !empty($_POST["taskid"]) ?$_POST["taskid"]: array();
        if( empty($_POST['uid']) || !is_numeric($_POST['uid']) )
        {
            sysMessage( '操作错误', 1, $aLocation );
        }
        $iUserId = intval($_POST['uid']);
        /* @var $oGame model_gamemanage */
        $oGame   = A::singleton("model_gamemanage");
        $mResult = $oGame->cancelTask( $iUserId, $iTaskId, $aId, $_SESSION['admin'], true );
        if( $mResult === TRUE )
        {
            sysMessage( '操作成功', 0, $aLocation );
        }
        else
        {
            sysMessage( $mResult, 1, $aLocation );
        }
    }
}
?>
