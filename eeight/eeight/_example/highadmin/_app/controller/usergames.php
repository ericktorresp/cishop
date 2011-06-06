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
 * @author      JAMES, SAUL, Rojer
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
            //默认搜索的游戏时间显示为当天02点00分00秒至次日02点00分00秒。
            //最早日期
            $sStartTime = isset($_POST['starttime'])&&!empty($_POST['starttime']) ? 
                    getFilterDate($_POST["starttime"],"Y-m-d H:i:s") : date("Y-m-d 02:00:00", time() );
            if( $sStartTime != "" )
            {
                $sWhere .= "AND P.`writetime`>='".$sStartTime."'";
            }
            //结束日期
            $sEndTime = isset($_POST['endtime'])&&!empty($_POST['endtime']) ? 
                    getFilterDate($_POST["endtime"],"Y-m-d H:i:s") : date("Y-m-d 02:00:00", time() + 86400 );
            if( $sEndTime != "" )
            {
                $sWhere .="AND P.`writetime`<='".$sEndTime."'";
            }
            //彩种
            $iLotteryId = isset($_POST["lotteryid"])&&is_numeric($_POST["lotteryid"]) ? intval($_POST["lotteryid"]): 0; 
            if( $iLotteryId>0 )
            {
                $sWhere .=" AND P.`lotteryid`='".$iLotteryId."'";
                //玩法  + 彩种期数
                $iCrowdId = isset($_POST["crowdid"])&&is_numeric($_POST["crowdid"]) ? intval($_POST["crowdid"]): 0;
                $iPid = isset($_POST["pid"])&&is_numeric($_POST["pid"]) ? intval($_POST["pid"]): 0;
                $iMethodId = isset($_POST["methodid"])&&is_numeric($_POST["methodid"]) ? intval($_POST["methodid"]): 0;
                //按玩法群查询
                if( $iCrowdId > 0 )
                {
                    $sWhere .=" AND M.`crowdid`='".$iCrowdId."'";
                }
                //按玩法组查询
                if( $iPid > 0 )
                {
                    $sWhere .=" AND M.`pid`='".$iPid."'";
                }
                //按玩法查询
                if( $iMethodId > 0 )
                {
                    $sWhere .=" AND M.`methodid`='".$iMethodId."'";
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
                        $oUser = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
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

            // 模式
            if(isset($_POST["modes"]) && $_POST["modes"]>0)
            {
                $sWhere .= " AND P.`modes`='".intval($_POST["modes"])."'";
            }

            //注单编号
            if(isset($_POST["projectno"])&&!empty($_POST["projectno"]))
            {
                //对注单编号进行解码
                $iUserId    = 0;
                $bAllChild  = TRUE;
                $sWhere     .= " AND P.`projectid`='".model_projects::HighEnCode($_POST["projectno"],"DECODE")."'";
            }

            $oProject = A::singleton("model_projects", $GLOBALS['aSysDbServer']['report']);
            $iPage = isset($_POST["page"])&&is_numeric($_POST["page"])?intval($_POST["page"]):1;
            $aProject = $oProject->projectGetResult( $iUserId, $bAllChild, "", $sWhere, 
                             "P.`projectid` DESC", 25, $iPage );
            foreach ($aProject["results"] as &$project)
            {
                $project['code'] = model_projects::AddslasCode($project['code'],$project['methodid']);
                //对号码进行整理
                if( strlen( $project["code"] ) > 40 )
                {
                    $str            = "";
                    $sTempCode      = "";
                    $sProjectCode   = "";
                    $aCodeDetail    = explode(",", $project["code"]);
                    $iCodeLen = strlen($aCodeDetail[0]) + 1;//单个号码长度
                    $iRowCodeLen = intval(40/$iCodeLen)*$iCodeLen;//一行的号码最大长度
                    foreach ( $aCodeDetail as $sCode )
                    {
                        $sTempCode .= $sCode .",";
                        $sProjectCode .= $sCode .",";
                        if( strlen($sTempCode) >= $iRowCodeLen )
                        {
                            $sProjectCode = substr($sProjectCode, 0,-1);
                            $sProjectCode .= "\r\n";
                            $sTempCode = "";
                        }
                    }
                    $sProjectCode = substr($sProjectCode, 0,-1);
                    $str .= $sProjectCode;
                    $project["code"] =$str;
                }
                $project["encodeprojectid"] = model_projects::HighEnCode("D".$project['issue']."-".$project['projectid'],"ENCODE");
                if( $project['codetype'] == 'input' && strpos($project['methodname'],'混合') === FALSE )
                {
                    $project['methodname'] = $project['methodname'].'[单式]';
                }
            }           
            echo json_encode($aProject);
            EXIT;
        }
        else
        {
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method", $GLOBALS['aSysDbServer']['report']);
            $aLottery = array();
            $aMethods = array();
            $aMethodByCrowd = $oMethod->methodGetAllListByCrowd();
            foreach ($aMethodByCrowd as $iLotteryId => $aCrowd)
            {
                $aLottery[$iLotteryId] = $aCrowd['cnname'];
                $aMethods[$iLotteryId] = $aCrowd['crowd'];
            }
            $GLOBALS['oView']->assign( "lottery",     $aLottery );
            $GLOBALS['oView']->assign( "data_method", json_encode($aMethods) );
            /* @var $oIssue model_issueinfo */
            $oIssue = A::singleton("model_issueinfo", $GLOBALS['aSysDbServer']['report']);
            $aIssue = array();
            $issueList = $oIssue->getItems(0, date("Y-m-d"), 0, 0, 0, time(), 'saleend DESC');
            foreach ($issueList as $v)
            {
                $aIssue[$v['lotteryid']][] = array('issue' => $v['issue'], 'lotteryid' => $v['issue'], 'dateend' => $v['belongdate']);
            }
            /* @var $oUser model_usertree */
            $oUser = A::singleton("model_usertree", $GLOBALS['aSysDbServer']['report']);
            $aUser = $oUser->userAgentget(" 1 ORDER BY `username`");
            $GLOBALS['oView']->assign("topproxy",$aUser);
            $GLOBALS['oView']->assign("data_issue",json_encode($aIssue));
            $GLOBALS['oView']->assign("modes",$GLOBALS['config']['modes']);
            $GLOBALS['oView']->assign("json_modes",json_encode($GLOBALS['config']['modes']));
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
            //默认搜索的游戏时间显示为当天02点00分00秒至次日02点00分00秒。
            //最早日期
            $startTime = isset($_POST['starttime'])&&!empty($_POST['starttime']) ? 
                    getFilterDate($_POST["starttime"],"Y-m-d H:i:s") : date("Y-m-d 02:00:00", time() );
            //结束日期
            $endTime = isset($_POST['endtime'])&&!empty($_POST['endtime']) ? 
                    getFilterDate($_POST["endtime"],"Y-m-d H:i:s") : date("Y-m-d 02:00:00", time() + 86400 );
                    
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
                //玩法  + 彩种期数
                $iCrowdId = isset($_POST["crowdid"])&&is_numeric($_POST["crowdid"]) ? intval($_POST["crowdid"]): 0;
                $iPid = isset($_POST["pid"])&&is_numeric($_POST["pid"]) ? intval($_POST["pid"]): 0;
                $iMethodId = isset($_POST["methodid"])&&is_numeric($_POST["methodid"]) ? intval($_POST["methodid"]): 0;
                //按玩法群查询
                if( $iCrowdId > 0 )
                {
                    $sWhere .=" AND M.`crowdid`='".$iCrowdId."'";
                }
                //按玩法组查询
                if( $iPid > 0 )
                {
                    $sWhere .=" AND M.`pid`='".$iPid."'";
                }
                //按玩法查询
                if( $iMethodId > 0 )
                {
                    $sWhere .=" AND M.`methodid`='".$iMethodId."'";
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
                        $oUser = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
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

            // 模式
            if(isset($_POST["modes"]) && $_POST["modes"]>0)
            {
                $sWhere .= " AND T.`modes`='".intval($_POST["modes"])."'";
            }

            //单子ID号码整理
            if( isset($_POST["taskid"])&&!empty($_POST["taskid"]))
            {
                $taskid = model_projects::HighEnCode($_POST["taskid"],"DECODE");
                $iUserId = 0;
                $bAllChild = TRUE;
                $sWhere .= " AND T.`taskid`='".$taskid."'";  // 这里没用.
            }

            $oTask = A::singleton("model_task", $GLOBALS['aSysDbServer']['report']);
            $iPage = isset($_POST["page"])&&is_numeric($_POST["page"])?intval($_POST["page"]):1;
            $aTask = $oTask->taskgetList($iUserId, $bAllChild, "", $sWhere, " T.`taskid` DESC",
                              25, $iPage);
            foreach($aTask["results"] as $iTaskid=>$task)
            {
                $aTask["results"][$iTaskid]["encodetaskid"] = model_projects::HighEnCode("T".$task["beginissue"]."-".$task["taskid"],"ENCODE");
                $aTask["results"][$iTaskid]['codes'] = model_projects::AddslasCode($task['codes'],$task['methodid']);
                //对号码进行整理
                if( strlen( $aTask["results"][$iTaskid]['codes'] ) > 40 )
                {
                    $str            = "";
                    $sTempCode      = "";
                    $sProjectCode   = "";
                    $aCodeDetail    = explode(",", $aTask["results"][$iTaskid]['codes']);
                    $iCodeLen = strlen($aCodeDetail[0]) + 1;//单个号码长度
                    $iRowCodeLen = intval(40/$iCodeLen)*$iCodeLen;//一行的号码最大长度
                    foreach ( $aCodeDetail as $sCode )
                    {
                        $sTempCode .= $sCode .",";
                        $sProjectCode .= $sCode .",";
                        if( strlen($sTempCode) >= $iRowCodeLen )
                        {
                            $sProjectCode = substr($sProjectCode, 0,-1);
                            $sProjectCode .= "\r\n";
                            $sTempCode = "";
                        }
                    }
                    $sProjectCode = substr($sProjectCode, 0,-1);
                    $str .= $sProjectCode;
                    $aTask["results"][$iTaskid]['codes'] =$str;
                }
                if( $aTask["results"][$iTaskid]['codetype'] == 'input' && strpos($aTask["results"][$iTaskid]['methodname'],'混合') === FALSE )
                {
                    $aTask["results"][$iTaskid]['methodname'] = $aTask["results"][$iTaskid]['methodname'].'[单式]';
                }
            }
            echo json_encode($aTask);
            EXIT;
        }
        else
        {
            /* @var $oMethod model_method */
            $oMethod = A::singleton("model_method", $GLOBALS['aSysDbServer']['report']);
            $aLottery = array();
            $aMethods = array();
            $aMethodByCrowd = $oMethod->methodGetAllListByCrowd();
            foreach ($aMethodByCrowd as $iLotteryId => $aCrowd)
            {
                $aLottery[$iLotteryId] = $aCrowd['cnname'];
                $aMethods[$iLotteryId] = $aCrowd['crowd'];
            }
            $GLOBALS['oView']->assign("lottery",$aLottery);
            $GLOBALS['oView']->assign("data_method",json_encode($aMethods));
            /* @var $oIssue model_issueinfo */
            $oIssue = A::singleton("model_issueinfo", $GLOBALS['aSysDbServer']['report']);
	        $aIssue = array();
	        $sCurrentDate = date("Y-m-d", time());
            foreach( $aLottery as $iLotteryid=>$aTemp )
            {
               $aIssue[$iLotteryid] = $oIssue->issueGetList(" A.`issue`,DATE(A.`saleend`) AS dateend ",
                                                            " B.`lotteryid`='".$iLotteryid."' "
                                                           ." AND A.`saleend` REGEXP '$sCurrentDate' AND A.`salestart` < now()",
                                                            " A.`saleend` DESC ", 0 );
            }
            $GLOBALS['oView']->assign("data_issue",json_encode($aIssue));
            $GLOBALS["oView"]->assign("ur_here","查看追号记录");
            /* @var $oUser model_usertree */
            $oUser = A::singleton("model_usertree", $GLOBALS['aSysDbServer']['report']);
            $aUser = $oUser->userAgentget(" 1 ORDER BY `username`");
            $GLOBALS["oView"]->assign("aUser",$aUser);
            $GLOBALS['oView']->assign("modes",$GLOBALS['config']['modes']);
            $GLOBALS['oView']->assign("json_modes",json_encode($GLOBALS['config']['modes']));
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
            $iProjectId = model_projects::HighEnCode($_GET["id"],"DECODE");
            if( $iProjectId==0 )
            {
                sysMessage('方案不存在',1, $aLocation );
            }
            /* @var $oProjects model_projects */
            $oProjects = A::singleton("model_projects", $GLOBALS['aSysDbServer']['report']);
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
	            $aProjects[0]["taskid"] = model_projects::HighEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE");
	        }
	        if( $aProjects[0]['codetype'] == 'input' && strpos($aProjects[0]['methodname'],'混合') === FALSE )
	        {
	            $aProjects[0]['methodname'] = $aProjects[0]['methodname'].'[单式]';
	        }
	        $aProjects[0]['code'] = model_projects::AddslasCode($aProjects[0]['code'],$aProjects[0]['methodid']);
            $aProjects[0]["code"] = wordwrap( $aProjects[0]["code"], 100, "<br />", TRUE );
            $aProjects[0]["projectid"] = model_projects::HighEnCode("D".$aProjects[0]["issue"]."-".$aProjects[0]["projectid"],"ENCODE");
            $GLOBALS['oView']->assign("ur_here","查看注单详情");
            $GLOBALS['oView']->assign("aProject",$aProjects[0]);
            
            //获取扩展号码 2009-11-19 17:18 By tom
			$sCondition          = " `projectid`='".$iProjectId."' ";
			$aPrizelevel         = $oProjects->getExtendCode( "*", $sCondition, "`level` ASC", 0 );
			$aPrizelevelDesc = unserialize( $aProjects[0]['nocount'] );
			//获取中奖详情
			/* @var $oGetPrize model_getprize */
            $oGetPrize = A::singleton("model_getprize", $GLOBALS['aSysDbServer']['report']);
            if($aProjects[0]['isgetprize'] == 1 && $aProjects[0]['prizestatus'] == 1)
            {
                $aProjectPrize = $oGetPrize->getProjectPrize($iProjectId,$aProjects[0]['methodid'],$aPrizelevel,$aProjects[0]['nocode']);
                if(!empty($aProjectPrize))
                {
                    $GLOBALS['oView']->assign('projectprize', $aProjectPrize);
                }
            }
			foreach($aPrizelevel as &$prizelevel)
			{
			    $prizelevel["leveldesc"] = $aPrizelevelDesc[$prizelevel['level']]['name'];
				$prizelevel["singleprize"] = $prizelevel["prize"] / $prizelevel["codetimes"];
				$prizelevel["expandcode"] = model_projects::AddslasCode($prizelevel["expandcode"],$aProjects[0]['methodid']);
			    $prizelevel["expandcode"] = wordwrap( str_replace(array("|","#"), array(", ","|"), 
			                                          $prizelevel["expandcode"]), 100, "<br>", TRUE );
			}
			if($aProjects[0]['lotterytype'] == 3 && $aProjects[0]['codetype'] == 'dxds' && $aProjects[0]['nocode'] != '' )
			{//基诺趣味型玩法
			    $aCode = explode(" ",$aProjects[0]['nocode']);//开奖号码
			    $iAddCount = 0;
			    $iBigCount = 0;//大号个数
			    $iSmallCount = 0;//小号个数
			    $iEevnCount = 0;//偶数号个数
			    $iOddCount = 0;//奇数号个数
			    foreach ($aCode as $iCode)
			    {
			        $iCode = intval($iCode);
			        $iAddCount += $iCode;
			        $iCode%2 == 0 ? $iEevnCount++ : $iOddCount++;
			        $iCode > 40 ? $iBigCount++ : $iSmallCount++;
			    }
			    if($iAddCount % 2 == 0)
			    {
			        $aFinalBonusCode['bjkl_heds'] ='双';
			    }
			    else
			    {
			        $aFinalBonusCode['bjkl_heds'] ='单';
			    }
			    $aFinalBonusCode['bjkl_hedx'] = '大';
			    if($iAddCount < 810)
			    {
			        $aFinalBonusCode['bjkl_hedx'] = '小';
			    }
			    if($iAddCount == 810)
			    {
			        $aFinalBonusCode['bjkl_hedx'] = '和';
			    }
			    $aFinalBonusCode['bjkl_sxpan'] = '上';
			    if($iBigCount > $iSmallCount)
			    {
			        $aFinalBonusCode['bjkl_sxpan'] = '下';//下盘
			    }
			    elseif($iBigCount == $iSmallCount)
			    {
			        $aFinalBonusCode['bjkl_sxpan'] = '中';//中盘
			    }
			    $aFinalBonusCode['bjkl_jopan'] = '奇';
			    if($iEevnCount > $iOddCount)
			    {
			        $aFinalBonusCode['bjkl_jopan'] = '偶';//偶盘
			    }
			    elseif($iEevnCount == $iOddCount)
			    {
			        $aFinalBonusCode['bjkl_jopan'] = '和';//和盘
			    }
			    $sNoHePan = '和值='.$iAddCount.'('.$aFinalBonusCode['bjkl_hedx'].','.$aFinalBonusCode['bjkl_heds'].')<br>';
			    $sNoHePan .= '盘面=('.$aFinalBonusCode['bjkl_sxpan'].','.$aFinalBonusCode['bjkl_jopan'].')';
			    $GLOBALS['oView']->assign("nohepan",$sNoHePan);
			}
			if($aProjects[0]['lotterytype'] == 3 && $aProjects[0]['nocode'] != '' 
			     && $aProjects[0]['codetype'] == 'digital' && $aProjects[0]['bonus'] > 0)
            {//基诺任选型玩法
               $aProjectCode = explode(",",$aProjects[0]['code']);//用户购买号码
               $aCode = explode(" ",$aProjects[0]['nocode']);//开奖号码 
               $aSameCode = array_intersect($aProjectCode, $aCode);//中奖号码
               $GLOBALS['oView']->assign("samecode", implode( " ",$aSameCode));
               $iSelNum = intval(substr($aProjects[0]['functionname'],-1));//玩法最少选择的选择号码个数
               $aLevelCount = array(1=>1,2=>1,3=>2,4=>3,5=>3,6=>4,7=>5);//各个玩法奖级个数
               $aLevelBonus = array();
               foreach ($aPrizelevel as $aLevel)
               {
                   $aLevelBonus[$aLevel['level']] = $aLevel['prize'];//获取各个奖级的奖金
                   $aLevelTimes[$aLevel['level']] = $aLevel['codetimes'];//获取各个奖级的奖金
               }
               $iInterCount = count($aSameCode);
               $iCodeCount = count($aProjectCode);
               $aMinNumCount = array(1=>1,2=>2,3=>2,4=>2,5=>3,6=>3,7=>4);//各个玩法最少中奖号码个数,7中0单独计算
               $aRealPrize = array();
               $iTotalCount = 0;
               $fTotalPrize = 0.00;
               if($iSelNum == 7 && in_array($iInterCount,array(0,1,2,3)))
               {
                   $iLevel = 5;//任选七中零
                   $iBonusTimes = $iCodeCount > $iSelNum ? $this->GetCombinCount($iCodeCount-$iInterCount,$iSelNum): 1;
                   if($iBonusTimes > 0)
                   {
                       $aRealPrize[$iLevel]["leveldesc"] = $aPrizelevelDesc[$iLevel]['name'];
                       $aRealPrize[$iLevel]['level'] = $iLevel;
                       $aRealPrize[$iLevel]['nocount'] = $iBonusTimes;
                       $aRealPrize[$iLevel]['singleprize'] = $aLevelBonus[$iLevel]/$aLevelTimes[$iLevel];
                       $aRealPrize[$iLevel]['codetimes'] = $aLevelTimes[$iLevel];
                       $aRealPrize[$iLevel]['prize'] = floatval( $aLevelBonus[$iLevel] * $iBonusTimes );
                       $iTotalCount += $iBonusTimes;
                       $fTotalPrize += $aRealPrize[$iLevel]['prize'];
                   }
               }
               else
               {
                   for($i = $aMinNumCount[$iSelNum]; $i<=$iSelNum; $i++ )
                   {
                       $iLevel = $iSelNum+1-$i;//对应奖级
                       $iBonusTimes = $this->GetCombinCount($iInterCount,$i)*$this->GetCombinCount($iCodeCount-$iInterCount,$iSelNum-$i);//对应奖级中奖注数
                       if($iBonusTimes > 0)
                       {
                           $aRealPrize[$iLevel]["leveldesc"] = $aPrizelevelDesc[$iLevel]['name'];
                           $aRealPrize[$iLevel]['level'] = $iLevel;
                           $aRealPrize[$iLevel]['nocount'] = $iBonusTimes;
                           $aRealPrize[$iLevel]['singleprize'] = $aLevelBonus[$iLevel]/$aLevelTimes[$iLevel];
                           $aRealPrize[$iLevel]['codetimes'] = $aLevelTimes[$iLevel];
                           $aRealPrize[$iLevel]['prize'] = floatval( $aLevelBonus[$iLevel] * $iBonusTimes );
                           $iTotalCount += $iBonusTimes;
                           $fTotalPrize += $aRealPrize[$iLevel]['prize'];
                       }
                   }
               }
               ksort($aRealPrize);
               $GLOBALS['oView']->assign("realprize",$aRealPrize);
               $GLOBALS['oView']->assign("totalcount",$iTotalCount);
               $GLOBALS['oView']->assign("totalprize",$fTotalPrize);
            }
			$GLOBALS['oView']->assign("prizelevel",$aPrizelevel);
			$GLOBALS['oView']->assign("levelcount",count($aPrizelevel));
            $GLOBALS['oView']->assign("modes", $GLOBALS['config']['modes']);
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
        * 计算排列组合的个数
        *
        * @author mark
        * 
        * @param integer $iBaseNumber   基数
        * @param integer $iSelectNumber 选择数
        * 
        * @return mixed
        * 
    */
    function GetCombinCount( $iBaseNumber, $iSelectNumber )
    {
        if($iSelectNumber > $iBaseNumber)
        {
            return 0;
        }
        if( $iBaseNumber == $iSelectNumber || $iSelectNumber == 0 )
        {
            return 1;//全选
        }
        if( $iSelectNumber == 1 )
        {
            return $iBaseNumber;//选一个数
        }
        $iNumerator = 1;//分子
        $iDenominator = 1;//分母
        for($i = 0; $i < $iSelectNumber; $i++)
        {
            $iNumerator *= $iBaseNumber - $i;//n*(n-1)...(n-m+1)
            $iDenominator *= $iSelectNumber - $i;//(n-m)....*2*1
        }
        return $iNumerator / $iDenominator;
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
        $iProjectId   = !empty($sProjectNo) ? model_projects::HighEnCode($sProjectNo, "DECODE") : 0;
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
        $oTask = A::singleton("model_task", $GLOBALS['aSysDbServer']['report']);
        $iTaskId = model_projects::HighEnCode( $sTaskId, "DECODE" );
        if( $iTaskId==0 )
        {
            sysMessage( '参数错误', 1, $aLocation );
        }
        $aTask = $oTask->taskgetList(0, TRUE, "", " AND T.`taskid`='".$iTaskId."'", "", 0);	
        if( empty($aTask[0]) )
        {
            sysMessage( '追号单不存在', 1, $aLocation);
        }
        if( $aTask[0]['codetype'] == 'input' && strpos($aTask[0]['methodname'],'混合') === FALSE )
        {
            $aTask[0]['methodname'] = $aTask[0]['methodname'].'[单式]';
        }
        $aTask[0]['codes'] = model_projects::AddslasCode($aTask[0]['codes'],$aTask[0]['methodid']);
        $aTask[0]["codes"]  = wordwrap($aTask[0]["codes"], 100 , "<br/>", TRUE);
        $aTask[0]["taskid"] = model_projects::HighEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE");
        $aTaskDetail = $oTask->taskdetailGetList( $iTaskId, $aTask[0]["lotteryid"] ); 
        foreach( $aTaskDetail as &$aDetail )
        {
            if( $aDetail["projectid"]>0 )
            { //注单详情
                $aDetail["projectid"] = model_projects::HighEnCode( "D".$aDetail["issue"]."-".$aDetail["projectid"] , "ENCODE" );
            }
        }
        $GLOBALS["oView"]->assign("task", $aTask[0] );
        $GLOBALS['oView']->assign("aTaskdetail",$aTaskDetail);
        $GLOBALS['oView']->assign("modes",$GLOBALS['config']['modes']);
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
        $iTaskId      = !empty($sTaskNo) ? model_projects::HighEnCode($sTaskNo, "DECODE") : 0;
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
        $mResult = $oGame->cancelTask( $iUserId, $iTaskId, $aId, $_SESSION['admin'] );
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
