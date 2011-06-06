<?php
/**
 * 文件 : /_app/controller/gameinfo.php
 * 功能 : 用户游戏信息的查看
 *
 * 功能:
 *  + actionGameList    参与游戏信息
 *  + actionGamedetail  查看游戏详情
 *  + actionCancelGame  用户撤单
 *  + actionTask        用户追号记录
 *  + actionTaskDetail  用户追号详情
 *  + actionCancelTask  取消追号  
 * 
 * @author    james,saul
 * @version   1.2.0
 * @package   lowproxy
 */

class controller_gameinfo extends basecontroller 
{
    /**
     * 参与游戏信息
     * URL: ./index.php?controller=gameinfo&action=gamelist
     * @author SAUL
     */
    function actionGameList()
    { //查询下级以及自身的，不能超过自身
        /* @var $oMethod model_method */
        $oMethod   = A::singleton("model_method");
        //获取所有的玩法(非玩法组)
        $aMethod   = $oMethod->methodGetList( "a.`methodid`,a.`methodname`,a.`lotteryid`,b.`cnname`",
                    " a.`pid`>0 ", "", "", 0 ); 
        $aLottery  = array(); //彩种组
        $aMethods  = array(); //玩法组
        foreach( $aMethod as $method )
        {
            $aLottery[$method["lotteryid"]] = $method["cnname"];
            $aMethods[$method["lotteryid"]][$method["methodid"]] = $method;
        }
        $GLOBALS['oView']->assign("lottery",        $aLottery);
        $GLOBALS['oView']->assign("data_method",    json_encode($aMethods)); //方便JS 调用玩法
        //参数整理
        $sWhere = " ";
        //开始时间
        if( isset($_GET["starttime"])&&!empty($_GET["starttime"]) )
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
        	//$sStartTime = date("Y-m-d 00:00:00");  //默认为当天
            
        }
        if(!empty($sStartTime) )
        {
            $sWhere .= " AND P.`writetime`>'".$sStartTime."'";
            $sHtml["starttime"] = $sStartTime;
        }
        //结束时间
        if( isset($_GET["endtime"])&&!empty($_GET["endtime"]) )
        {
            $sEndtime = getFilterDate($_GET["endtime"]);
        }
        else
        {
	        // 6/9/2010
        	if ( date("Y-m-d H:i:s", strtotime("NOW") )  < date("Y-m-d 02:20:00") )
        	{
        		$sEndtime = getFilterDate( date("Y-m-d 02:20:00") );
        	}
        	else
        	{
            	$sEndtime = getFilterDate( date("Y-m-d 02:20:00", strtotime("+1 days") ) );
        	}
        	//$sEndtime = date( "Y-m-d 00:00:00", strtotime("+1 days") );
        }
        if( !empty($sEndtime) )
        {
            $sHtml["endtime"] = $sEndtime;
            $sWhere .= " AND P.`writetime`<='".$sEndtime."'";
        }
        /* @var $oIssue model_issueinfo */
        $oIssue = A::singleton("model_issueinfo");
        //获取奖期
        foreach( array_flip($aLottery) as $iLotteryId )
        {
            $aIssue[$iLotteryId] = $oIssue->issueGetList(" A.`issue`,DATE(A.`saleend`) AS dateend ",
                     " B.`lotteryid`='".$iLotteryId."' "
                    ." AND UNIX_TIMESTAMP(A.`salestart`)<UNIX_TIMESTAMP(now()) ",
                     " A.`saleend` DESC LIMIT 0,10", 0 );
        }
        $GLOBALS["oView"]->assign( "data_issue", json_encode($aIssue) );
        $iLotteryId = isset($_GET["lotteryid"])&&is_numeric($_GET["lotteryid"]) ? intval($_GET["lotteryid"]) : 0;
        $sHtml["lotteryid"] = $iLotteryId;
        if($sHtml["lotteryid"] >0 )
        {
            $sWhere .=" AND P.`lotteryid`='".$iLotteryId."' ";
            //玩法
            $iMethodId = isset($_GET["methodid"])&&is_numeric($_GET["methodid"]) ? intval($_GET["methodid"]) :0;
            $sHtml["methodid"] = $iMethodId;
            if( $iMethodId>0 )
            {
                $sWhere .= " AND P.`methodid`='".$iMethodId."'";
            }
            $sIssue         = isset($_GET["issue"])&&!empty($_GET["issue"])? daddslashes($_GET["issue"]):"0";
            $sHtml["issue"] = $sIssue;
            if( $sIssue!="0" )
            {
                $sWhere .= " AND P.`issue`='".$sIssue."'";
            }
        }
        else
        {
            $sHtml["methodid"]  = 0;
            $sHtml["issue"]     = 0;
        }
        //当用身份的转化
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user");
        if( intval($_SESSION["usertype"])==2 )
        {//总代管理员
            $bIsAdmin = TRUE;
            $iUserId = $oUser->getTopProxyId(intval($_SESSION["userid"]), FALSE ); //获取总代
            if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
            { //为销售
                $sUserWhere = " AND P.`lvproxyid` IN ("
                            ."SELECT `topproxyid` FROM `useradminproxy` WHERE `adminid`='"
                            .intval($_SESSION["userid"])."')";
            }
            else
            {
                $sUserWhere = " AND P.`lvtopid`='".$iUserId."'";
            }
        }
        else
        {
            $iUserId    = intval($_SESSION["userid"]);
            $bIsAdmin   = FALSE;
            $sUserWhere = " AND (FIND_IN_SET('".$iUserId."',UT.`parenttree`) OR (UT.`userid`='".$iUserId."'))";
        }
        //对include的默认
        if( $bIsAdmin )
        {
            $bInclude = TRUE;
            $sHtml["include"] = 1;
        }
        else
        {
            if($oUser->isTopProxy($iUserId))
            {
                $bInclude = TRUE;
                $sHtml["include"] = 1;   
            }
            else
            {
                $bInclude = FALSE;
                $sHtml["include"] = 0;
            }
        }
        //用户名以及是否包含(支持*号,不支持包含)
        if( isset($_GET["username"])&&!empty($_GET["username"]) )
        { //指定了用户名
            $sUserName = daddslashes( $_GET["username"] );
            if( strstr($sUserName,'*') )
            { // 支持模糊搜索
                $sWhere .= " AND UT.`username` LIKE '".str_replace("*","%",$sUserName)."'";
                $sHtml["include"] = 0; //支持*,不支持包含下级
                $iUserId = 0;
                $bInclude = FALSE;
                $sHtml["username"] = stripslashes_deep($sUserName);
            }
            else
            { //不支持模糊搜索
                $iUser = $oUser->getUseridByUsername( $sUserName ); //获取ID
                if( $iUser>0 )
                { //需要检测当前搜索到的用户 和 当前用户的关系
                    $iUserId = $iUser;
                    $sHtml["username"] = stripslashes_deep($sUserName);
                    if( isset($_GET["include"]) && intval($_GET["include"])==1 )
                    {
                        $sHtml["include"] = 1;
                        $bInclude = TRUE;
                    }
                    else
                    {
                        $sHtml["include"] = 0;
                        $bInclude = FALSE;
                    }
                }
                else
                { //用户不存在
                    $sWhere = " AND 1=0";
                }
            }
        }
        else
        {
            if(isset($_GET["include"])&&is_numeric($_GET["include"]))
            {
                $bInclude   = TRUE;
                $iUserId    = 0;
                $sHtml["include"] = 1;
            }
        }
        //下面是Code
        if( isset($_GET["projectno"])&&!empty($_GET["projectno"]) )
        {
            $sWhere = ""; //重新整理
            $iProjectNo = model_projects::ProjectEnCode( daddslashes($_GET["projectno"]), "DECODE");
            if( intval( $iProjectNo )>0 )
            {
                $sHtml["projectno"] = daddslashes($_GET["projectno"]);
                $bInclude = TRUE;
                $iUserId = 0;
                $sWhere = " AND P.`projectid`='".$iProjectNo."'";
            }
        }
        $sWhere .= $sUserWhere;
        /* @var $oProject model_projects */
        //$oProject = A::singleton("model_projects");
        $oProject = new model_projects( $GLOBALS['aSysDbServer']['report'] );
        $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ?intval($_GET["p"]):1;
        $aProjects = $oProject->projectGetResult( $iUserId, $bInclude, "", $sWhere,
                                                    "P.`projectid` DESC", 25, $iPage );
        $total["in"]  = 0.00;
        $total["out"] = 0.00;
        foreach($aProjects["results"] as $iProjectId=>&$aProject)
        {
            $aProject["projectid"] = model_projects::ProjectEnCode("D".$aProject["issue"]."-".$aProject["projectid"],"ENCODE");
            $total["in"]  = $total["in"] + $aProject["bonus"];
            $total["out"] = $total["out"]+ $aProject["totalprice"];
            //对号码进行整理
            if(strlen($aProject["code"])>20)
            {
                $str = "<a href=\"javascript:show_no('".$iProjectId."');\">详细号码</a>";
                $str .= "<div class=\"task_div\" id=\"code_".$iProjectId."\">号码详情";
                $str .= "[<a href=\"javascript:close_no('".$iProjectId."');\">关闭</a>]<br/>";
                $str .="<textarea class=\"code\" readonly=\"readonly\">";
                $code = str_replace( array("B","S","A","D","|"),array("大","小","单","双",","), $aProject["code"]);
                $str .= $code."</textarea></div>";
                $aProject["code"] =$str;
            }
            else
            {
                $aProject["code"] =str_replace( array("B","S","A","D","|"),array("大","小","单","双",","), $aProject["code"]);
            }
        }
        $GLOBALS['oView']->assign( "total",    $total );
        $GLOBALS['oView']->assign( "aProject", $aProjects["results"] );
        $oPage = new pages( $aProjects["affects"], 25 );
        $GLOBALS['oView']->assign( "pageinfo", $oPage->show(1));
        $GLOBALS['oView']->assign( "s", $sHtml);
        $GLOBALS['oView']->assign( "actionlink", array('text'=>'清空查询条件',"href"=>url('gameinfo','gamelist')));
        $GLOBALS['oView']->assign( "ur_here", "参与游戏信息" );
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_gamelist.html" );
        EXIT;
    }



    /**
     * 查看游戏详情
     * URL：./index.php?controller=gameinfo&action=gamedetail
     * @author SAUL
     */
    function actionGamedetail()
    {
        $aLocation[0]   = array("title"=>'参与游戏信息',"url"=>url('gameinfo','gamelist'));
        $iProjectId     = isset($_GET["id"])&&!empty($_GET["id"]) ? model_projects::ProjectEnCode($_GET["id"],"DECODE"):0;
        if( $iProjectId==0 )
        {
            sysMsg( '权限不足', 2, $aLocation );
        }
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user");
        if( intval($_SESSION["usertype"])==2 )
        { //总代管理员
            $iUserId = $oUser->getTopProxyId( intval($_SESSION["userid"]), FALSE ); //获取总代
            if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
            { //为销售
                $sUserWhere = " AND P.`lvproxyid` IN (SELECT `topproxyid` FROM `useradminproxy`"
                            ." WHERE `adminid`='".intval($_SESSION["userid"])."')";
            }
            else
            {
                $sUserWhere = " AND P.`lvtopid`='".$iUserId."'";
            }
        }
        else
        {
            $iUserId = intval( $_SESSION["userid"] );
            $sUserWhere = " AND (FIND_IN_SET('".intval($iUserId)."',UT.`parenttree`)"
                 ." OR (UT.`userid`='".$iUserId."'))";
        }
        $oProject = new model_projects();
        $aProject = $oProject->projectGetResult(0, FALSE,
             " P.*, L.`cnname`, M.`methodname`, UT.`username`, I.`code` as `nocode`, I.`canneldeadline`",
             "AND `projectid`='".$iProjectId."'".$sUserWhere, "", 0);
        if( empty($aProject[0]) )
        {
            sysMsg( '单子不存在', 2, $aLocation );
        }
        $iSign = 0;
        //注单编号
        if(intval($aProject[0]["taskid"])>0)
        {
            $oTask = new model_task();
            $aTask = $oTask->taskgetList(0,TRUE,"T.`taskid`,T.`beginissue`"," and T.`taskid`='".$aProject[0]["taskid"]."'","",0);
            // 获取追号单开始时间
            $aResult = $oTask->getTaskInfo($aProject[0]['taskid']);
            $aProject[0]["taskid"] = model_task::TaskEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE");	
            if ($aResult['begintime'] >= getConfigValue( 'bigorderstarttime', '00-00-00 00:00:00' )){
            	$iSign = 1;
            }
        } else {
        	$iSign = 1;
        }
        $GLOBALS['oView']->assign( "ur_here", "查看注单详情" );
        $bigmoney = getConfigValue('bigordercancel','10000'); //大额撤单底线
        if( $iSign === 1 && $aProject[0]["totalprice"] > $bigmoney )
        {
            $big = getConfigValue('bigordercancelpre', '0.01'); //大额撤单的手续费比例
            $money = $big * $aProject[0]["totalprice"];
            $GLOBALS["oView"]->assign( "need",  1 ); //需要收费
            $GLOBALS['oView']->assign( "money", $money );
        }
        if(strtotime($aProject[0]["canneldeadline"]) > time() && $aProject[0]['iscancel'] == 0 )
        { //没有撤单 && 没有过最后的撤单时间(issueinfo表)
            if( intval($_SESSION["userid"])== intval($aProject[0]["userid"]) )
            {
                $GLOBALS['oView']->assign("can", 1 ); //能否撤单
            }
        }
        else
        {
            if($aProject[0]["isgetprize"]==2)
            { //奖金详情
                $sDescription = $oProject->getProjectBonusDescription( $aProject[0] );
                $GLOBALS['oView']->assign( "description", $sDescription );
            }
        }
        //获取扩展号码详情
        $prizelevel = $oProject->getExtendCode( "*", "`projectid`='".$aProject[0]["projectid"]."'", 
                  "`isspecial` ASC", 0 );
        $aProject[0]["code"] = wordwrap(str_replace( array("B","S","A","D","|"),array("大","小","单","双",", "), $aProject[0]["code"]),100,"<br/>");
        $aProject[0]["projectid"] = model_projects::ProjectEnCode("D".$aProject[0]["issue"]."-".$aProject[0]["projectid"],"ENCODE");
        $GLOBALS['oView']->assign( "project", $aProject[0] );
        //扩展号码整理
        foreach($prizelevel as $i => $v)
        {
            $prizelevel[$i]["expandcode"] = wordwrap( str_replace(array("|","#"),array(", ","|"),$v["expandcode"]),80,"<br>");
        }
        $GLOBALS['oView']->assign( "prizelevel", $prizelevel );
        $oProject->assignSysInfo();
        $GLOBALS['oView']->display( "gameinfo_gamedetail.html" );
        EXIT;
    }



    /**
     * 用户撤单
     * URL: ./index.php?controller=gameinfo&action=cancelgame
     * @author JAMES
     */
    function actionCancelgame()
    {
        $sProjectNo   = !empty($_GET["id"]) ? $_GET["id"] : "";
        $aLocation[0] = array("title"=>'查看注单详情',"url"=>url('gameinfo', 'gamedetail', array('id'=>$sProjectNo)));
        $iProjectId   = !empty($sProjectNo) ? model_projects::ProjectEnCode($sProjectNo, "DECODE") : 0;
        if( $iProjectId == 0 )
        {
            sysMsg( '权限不足', 2, $aLocation );
        }
        /* @var $oGame model_gamemanage */
        $oGame      = A::singleton("model_gamemanage");
        $mResult    = $oGame->cancelProject( intval($_SESSION["userid"]), $iProjectId );
        if( $mResult === TRUE )
        {
            sysMsg( '撤单成功', 1, $aLocation );
        }
        else
        {
            sysMsg( $mResult, 2, $aLocation );
        }
    }



    /**
     * 追号记录
     * URL: ./index.php?controller=gameinfo&action=task
     * @author SAUL
     */
    function actionTask()
    {   //  查询自身+下级的追号记录
        //    固定参数的传递,玩法
        /* @var $oMthod model_method */
        $oMethod    = A::singleton("model_method");
        $aMethod    = $oMethod->methodGetList( "a.`methodid`,a.`methodname`,a.`lotteryid`,b.`cnname`",
                "a.`pid`>0", "","", 0 );
        $aLottery   = array();
        $aMethods   = array();
        foreach( $aMethod as $method )
        {
            $aLottery[$method["lotteryid"]] = $method["cnname"];
            $aMethods[$method["lotteryid"]][$method["methodid"]] = $method;
        }
        $GLOBALS['oView']->assign( "lottery",       $aLottery );
        $GLOBALS['oView']->assign( "data_method",   json_encode($aMethods) );
        //参数整理
        $sWhere = " ";
        //开始时间
        if( isset($_GET["starttime"])&&!empty($_GET["starttime"]) )
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
        	//$sStartTime = date("Y-m-d 00:00:00");
        }
        if( !empty($sStartTime) )
        {
            $sWhere .= " AND T.`begintime`>='".$sStartTime."'";
            $sHtml["starttime"] = $sStartTime;
        }
        //结束时间
        if( isset($_GET["endtime"])&&!empty($_GET["endtime"]) )
        {
            $sEndTime = getFilterDate($_GET["endtime"]);
        }
        else
        {
        	// 6/9/2010
        	if ( date("Y-m-d H:i:s", strtotime("NOW") )  < date("Y-m-d 02:20:00") )
        	{
        		$sEndTime = getFilterDate( date("Y-m-d 02:20:00") );
        	}
        	else
        	{
            	$sEndTime = getFilterDate( date("Y-m-d 02:20:00", strtotime("+1 days") ) );
        	}
        	//$sEndTime = date("Y-m-d 00:00:00", strtotime("+1 days") );
        }
        if( !empty($sEndTime) )
        {
            $sHtml["endtime"] = $sEndTime;
            $sWhere .= " AND T.`begintime`<'".$sEndTime."'";
        }
        /* @var $oIssue model_issueinfo */
        $oIssue     = A::singleton("model_issueinfo");
        foreach( array_flip($aLottery) as $iLotteryId )
        {
            $aIssue[$iLotteryId] = $oIssue->issueGetList(" A.`issue`,DATE(A.`saleend`) AS dateend ",
                     " B.`lotteryid`='".$iLotteryId."' "
                    ." AND UNIX_TIMESTAMP(A.`salestart`)<UNIX_TIMESTAMP(now()) ",
                     " A.`saleend` DESC LIMIT 0,10", 0 );
        }
        $GLOBALS["oView"]->assign( "data_issue", json_encode($aIssue) );
        $iLotteryId = isset($_GET["lotteryid"])&&is_numeric($_GET["lotteryid"]) ? intval($_GET["lotteryid"]) : 0;
        $sHtml["lotteryid"] = $iLotteryId;
        if( $iLotteryId>0 )
        {
            $sWhere .=" AND T.`lotteryid`='".$iLotteryId."' ";
            //玩法
            $iMethodId = isset($_GET["methodid"])&&is_numeric($_GET["methodid"]) ? intval($_GET["methodid"]): 0;
            $sHtml["methodid"] = $iMethodId;
            if( $iMethodId>0 )
            {
                $sWhere .= " AND T.`methodid`='".$iMethodId."'";
            }
            $sIssue = isset($_GET["issue"])&&!empty($_GET["issue"]) ? daddslashes($_GET["issue"]): "0";
            $sHtml["issue"] = $sIssue;
            if( $sIssue<>"0" )
            {
                $sWhere .= " AND T.`beginissue`='".$sIssue."'";
            }
        }
        else
        {
            $sHtml["methodid"] = 0;
            $sHtml["issue"] = 0;
        }
        //用户身份的转化
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user");
        if( intval($_SESSION["usertype"])==2 )
        {//销售
            $bIsAdmin   = TRUE;
            $iUserId    = $oUser->getTopProxyId( intval($_SESSION["userid"]), FALSE ); //获取总代
            if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
            { //为销售
                $sUserWhere = " AND T.`lvproxyid` in (SELECT `topproxyid` FROM `useradminproxy`"
                        ." WHERE `adminid`='".intval($_SESSION["userid"])."')";
            }
            else
            {
                $sUserWhere = " AND T.`lvtopid`='".intval($iUserId)."'";
            }
        }
        else
        {
            $iUserId = intval($_SESSION["userid"]);
            $bIsAdmin = FALSE;
            $sUserWhere = " AND (FIND_IN_SET('".$iUserId."',UT.`parenttree`)"
                            ." OR (UT.`userid`='".$iUserId."'))";
        }
        if( $bIsAdmin )
        {
            $bInclude = TRUE;
            $sHtml["include"] = 1;
        }
        else
        {
            if($oUser->isTopProxy($iUserId))
            {
                $bInclude = TRUE;
                $sHtml["include"] = 1;   
            }
            else
            {
                $bInclude = FALSE;
                $sHtml["include"] = 0;
            }
        }
        //用户名以及是否包含(支持*号,不支持包含)
        if(isset($_GET["username"])&&!empty($_GET["username"]))
        { //指定了用户名
            $sUserName = daddslashes( $_GET["username"] );
            if( strstr($sUserName,'*') )
            { // 支持模糊搜索
                $sWhere .= " AND UT.`username` LIKE '".str_replace( "*", "%", $sUserName )."'";
                $sHtml["include"] = 0; //支持*,不支持包含下级
                $iUserId = 0;
                $bInclude = FALSE;
                $sHtml["username"] = stripslashes_deep($sUserName);
            }
            else
            { //不支持模糊搜索
                $iUser = $oUser->getUseridByUsername( $sUserName ); //获取ID
                if($iUser >0)
                { //需要检测当前搜索到的用户 和 当前用户的关系
                    $iUserId = $iUser;
                    $sHtml["username"] = stripslashes_deep($sUserName);
                    if( isset($_GET["include"]) && intval($_GET["include"])==1 )
                    {
                        $sHtml["include"] = 1;
                        $bInclude = TRUE;
                    }
                    else
                    {
                        $sHtml["include"] = 0;
                        $bInclude = FALSE;
                    }
                }
                else
                { //用户不存在
                    $sWhere = " AND 1=0";
                }
            }
        }
        else
        {
            if(isset($_GET["include"])&&is_numeric($_GET["include"]))
            {
                $bInclude = TRUE;
                $iUserId = 0;
                $sHtml["include"] = 1;
            }
        }
        //下面是Code
        if( isset($_GET["taskno"])&&!empty($_GET["taskno"]) )
        {
            $iTaskId = model_task::TaskEnCode($_GET["taskno"], "DECODE" );
            if( $iTaskId>0 )
            {
                $sHtml["taskno"] = daddslashes($_GET["taskno"]);
                $sWhere = " AND T.`taskid`='".intval($iTaskId)."'";
                $iUserId = 0;
                $bInclude = TRUE;
            }            
        }
        $sWhere .= $sUserWhere;
        $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ?intval($_GET["p"]): 1;
        /* @var $oTask model_task */
        //$oTask = A::singleton("model_task");
        $oTask = new model_task( $GLOBALS['aSysDbServer']['report'] );
        $aTask = $oTask->taskgetList( $iUserId, $bInclude,"",$sWhere, "T.`taskid` DESC", 25, $iPage );
        $total["total"]  = 0.00;
        $total["finish"] = 0.00;
        $total["cancel"] = 0.00;
        foreach( $aTask["results"] as $iTaskId=>&$task )
        {
            $task["taskid"] = model_task::TaskEnCode("T".$task["beginissue"]."-".$task["taskid"],"ENCODE");
            $total["total"]  = $total["total"]  + $task["taskprice"];
            $total["finish"] = $total["finish"] + $task["finishprice"];
            $total["cancel"] = $total["cancel"] + $task["cancelprice"];
            //对号码进行整理
            if(strlen($task["codes"])>20)
            {
                $str = "<a href=\"javascript:show_no('".$iTaskId."');\">详细号码</a>";
                $str .= "<div class=\"task_div\" id=\"code_".$iTaskId."\">号码详情";
                $str .= "[<a href=\"javascript:close_no('".$iTaskId."');\">关闭</a>]<br/>";
                $str .="<textarea class=\"code\" readonly=\"readonly\">";
                $code = str_replace( array("B","S","A","D","|"),array("大","小","单","双",","), $task["codes"]);
                $str .= str_replace("|","&nbsp;",$code)."</textarea></div>";
                $task["codes"] =$str;
            }
            else
            {
                $task["codes"] =str_replace( array("B","S","A","D","|"),array("大","小","单","双",","), $task["codes"]);
            }
        }
        $GLOBALS['oView']->assign( "total", $total );
        $GLOBALS['oView']->assign( "aTask", $aTask["results"] );
        $oPage = new pages( $aTask["affects"], 25 );
        $GLOBALS['oView']->assign( "pageinfo", $oPage->show(1) );
        $GLOBALS['oView']->assign( "s", $sHtml );
        $GLOBALS['oView']->assign( "actionlink", array("text"=>'清空查询条件',"href"=>url('gameinfo','task')) );
        $GLOBALS["oView"]->assign( "ur_here", "查看追号信息" );
        $oTask->assignSysInfo();
        $GLOBALS['oView']->display("gameinfo_task.html");
        EXIT;
    }



    /**
     * 追号详情查看
     * URL: ./index.php?controller=gameinfo&action=taskdetail
     * @author SAUL
     */
    function actionTaskDetail()
    {
        $aLocation[0]   = array( "title"=>'查看追号记录', "url"=>url('gameinfo','task') );
        $iTaskId        = isset($_GET["id"])&&!empty($_GET["id"]) ? model_task::TaskEnCode($_GET["id"],"DECODE") : 0;
        if( $iTaskId==0 )
        {
            sysMsg( '没有权限', 2, $aLocation );
        }
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user");
        if( intval($_SESSION["usertype"])==2 )
        {//总代管理员
            $iUserId = $oUser->getTopProxyId( intval($_SESSION["userid"]), FALSE ); //获取总代
            if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
            { //为销售
                $sUserWhere = " AND T.`lvproxyid` IN (SELECT `topproxyid` FROM `useradminproxy` "
                                ."WHERE `adminid`='".intval($_SESSION["userid"])."')";
            }
            else
            {
                $sUserWhere = " AND T.`lvtopid`='".$iUserId."'";
            }
        }
        else
        {
            $iUserId = intval( $_SESSION["userid"] );
            $sUserWhere = " AND (FIND_IN_SET('".$iUserId."',UT.`parenttree`)"
                            ." OR (UT.`userid`='".$iUserId."'))";
        }
        /* @var $oTask model_task */
        $oTask = A::singleton("model_task");
        $aTask = $oTask->taskgetList( 0, FALSE, ""," AND T.`taskid`='".$iTaskId."'".$sUserWhere, "", 0 );	
        if( empty($aTask[0]) )
        {
            sysMsg('追号单不存在', 2, $aLocation );
        }
        if( intval($aTask[0]["userid"]) == intval($_SESSION["userid"]) )
        {
            $GLOBALS['oView']->assign("can", 1 ); //能够撤单
        }
        $aTask[0]["codes"]  = wordwrap(str_replace( array("B","S","A","D","|"),array("大","小","单","双",", "), $aTask[0]["codes"]),100,"<br/>");
        $aTask[0]["taskid"] = model_task::TaskEnCode("T".$aTask[0]["beginissue"]."-".$aTask[0]["taskid"],"ENCODE");
        $aTaskDetail        = $oTask->taskdetailGetList( $iTaskId, $aTask[0]["lotteryid"] ); 
         // 获取追号单的每期单倍价格
        $aResult = $oTask->getTaskInfo($aTaskDetail[0]['taskid']);
        // 收取手续费的最高限制
        $fBigMoney = intval( getConfigValue( 'bigordercancel', 10000 ) );
        $fBigFee   = getConfigValue('bigordercancelpre', 0.01 ); //大额撤单手续费用
        foreach( $aTaskDetail as &$aDetail )
        {
            if( $aDetail["projectid"]>0 )
            { //注单详情
                $aDetail["projectid"] = model_projects::ProjectEnCode( "D".$aDetail["issue"]."-".$aDetail["projectid"] , "ENCODE" );
            }
            // 计算每一期的投注金额
        	$fMoney = $aDetail['multiple'] * $aResult['singleprice'];
        	// 计算每一期如果终止追号，是否要收取手续费，如果要收取，应当收取多少
        	$aDetail['fee'] = ($fMoney >= $fBigMoney) ? number_format($fMoney * $fBigFee, 2, '.', '') : 0;
        }
        $need = 0; // 是否收手续费标志位
        // 根据开始收取撤大额追号单手续费的时间判断当前单是否要收手续费
        if ($aTask[0]['begintime'] >= getConfigValue( 'bigorderstarttime', '00-00-00 00:00:00' )){
        	$need = 1;
        }
        $GLOBALS["oView"]->assign( "task",          $aTask[0] );
        $GLOBALS['oView']->assign( "aTaskdetail",   $aTaskDetail );
        $GLOBALS['oView']->assign( "need", $need );
        $GLOBALS['oView']->assign( "ur_here",       "查看追号详情");
        $oTask->assignSysInfo();
        $GLOBALS['oView']->display("gameinfo_taskdetail.html");
        EXIT;
    }



    /**
     * 追号单撤单
     * URL: ./index.php?controller=gameinfo&action=canceltask
     * @author JAMES
     */
    function actionCancelTask()
    {
        $sTaskNo      = !empty($_POST["id"]) ? $_POST["id"] : "";
        $aLocation[0] = array("title"=>'查看追号详情',"url"=>url('gameinfo', 'taskdetail', array('id'=>$sTaskNo)));
        $iTaskId      = !empty($sTaskNo) ? model_projects::ProjectEnCode($sTaskNo, "DECODE") : 0;
        if( $iTaskId == 0 )
        {
            sysMsg( '权限不足', 2, $aLocation );
        }
        $aId = !empty($_POST["taskid"]) ?$_POST["taskid"]: array();
        $oGame   = new model_gamemanage();
        $mResult = $oGame->cancelTask( intval($_SESSION["userid"]), $iTaskId, $aId, '', true );
        if( $mResult === TRUE )
        {
            sysMsg( '操作成功', 1, $aLocation );
        }
        else
        {
            sysMsg( $mResult, 2, $aLocation );
        }
    }
}
?>
