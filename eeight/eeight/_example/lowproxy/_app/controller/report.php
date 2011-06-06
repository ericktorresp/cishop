<?php
/**
 * 文件 : /_app/controller/report.php
 * 功能 : 报表管理
 *
 * 功能:
 * + actionUserPoint    返点总额
 * + actionOrders       帐变管理
 * + actionList         报表查询
 * + actionReportdetail 游戏明细   
 * 
 * @author    SAUL,MARK
 * @version   1.2.0
 * @package   lowproxy
 */

class controller_report extends basecontroller 
{
    /**
     * 返点总额
     * URL: ./index.php?controller=report&action=userpoint
     * 查询代理在在指定时间里自己及所有下级的返点总额
     * 当天没有开奖查询不到
     * @author Mark
     */
    function actionUserPoint()
    {
        /* @var $oOrder model_orders */
        $oOrder = A::singleton("model_orders");
        //指定查询时间
        if ( date("Y-m-d H:i:s", strtotime("NOW") ) < date("Y-m-d 02:20:00") )
       	{
       		$sDefaultTime = date('Y-m-d', strtotime('-1 day') );
       	}
       	else 
       	{
        	$sDefaultTime = date('Y-m-d',time());
       	}
        $sSearchDate = isset($_GET['searchdate']) && !empty($_GET['searchdate']) ? $_GET['searchdate'] : $sDefaultTime;
        //检查时间是否合法
        list($sYear, $sMonth, $sDay) = explode('-',$sSearchDate);
        if( !is_numeric($sYear) || !is_numeric($sMonth) || !is_numeric($sDay) || strlen($sYear) !=4
            || strlen($sMonth) != 2 || strlen($sDay) != 2 || !checkdate($sMonth,$sDay,$sYear) )
        {
            sysMsg("时间的格式不正确,正确格式：2008-08-02", 2);
        }
	        // 	6/9/2010
        	$sSearchDate2 = date("Y-m-d", date("U", strtotime("$sSearchDate 02:20:00")) + 86400 );
        	$sCondition  = " (`times` BETWEEN '$sSearchDate 02:20:00' AND '$sSearchDate2 02:20:00')";
        //获取返点总额
        $oUser = A::singleton("model_user");
        
        if( $_SESSION['usertype'] == 2 && TRUE == $oUser->IsAdminSale(intval($_SESSION['userid']))){
        	$aUserPoint = $oOrder->getAdminProxyPoint( intval($_SESSION['userid']) ,$oUser->getTopProxyId($_SESSION['userid']),"AND (p.`writetime` BETWEEN '$sSearchDate 00:00:00' AND '$sSearchDate 23:59:59')", true );
        } else {
        	$aUserPoint = $oOrder->getProxyTotalPoint( intval($_SESSION['userid']), $sCondition );
        }
        //没有指定代理
        if( empty($aUserPoint) )
        {
            sysMsg("没有指定代理", 2);
        }
        $GLOBALS['oView']->assign( "username",   daddslashes($_SESSION['username']) );
        $GLOBALS['oView']->assign( "userpoint",  $aUserPoint );
        $GLOBALS['oView']->assign( "searchdate", $sSearchDate );
        $GLOBALS['oView']->assign( "ur_here",    "返点总额报表" );
        $oOrder->assignSysInfo();
        $GLOBALS['oView']->display('report_userpoint.html');
        EXIT;
    }



    /**
     * 账变管理
     * URL: ./index.php?controller=report&action=orders
     * @author SAUL
     */
    function actionOrders()
    { //查看自身以及下级
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        //玩法
        $aMethod = $oMethod->methodGetList( "a.`methodid`,a.`methodname`,a.`lotteryid`,b.`cnname`",
            "a.`pid`>0", "", "", 0 );
        $aLottery = array();
        $aMethods = array();
        foreach( $aMethod as $method )
        {
            $aLottery[$method["lotteryid"]] = $method["cnname"];
            $aMethods[$method["lotteryid"]][$method["methodid"]] = $method;
        }
        /* @var $oIssue model_issueinfo */
        $oIssue = A::singleton("model_issueinfo");
        $aIssue = array();
        foreach( array_flip($aLottery) as $iLotteryId )
        {
            $aIssue[$iLotteryId] = $oIssue->issueGetList(" A.`issue`,DATE(A.`saleend`) AS dateend ",
                     " B.`lotteryid`='".$iLotteryId."' "
                    ." AND UNIX_TIMESTAMP(A.`salestart`)<UNIX_TIMESTAMP(now()) ",
                     " A.`saleend` DESC LIMIT 0,10", 0 );
        }
        //参数整理
        $sWhere =" ";
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
            $sWhere .= " AND O.`times` >'".$sStartTime."'";
            $sHtml["starttime"] = $sStartTime;
        }
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
        	//$sEndTime = date("Y-m-d 00:00:00", strtotime("+1 days"));
        }
        if( !empty($sEndTime) )
        {
            $sWhere .= " AND O.`times`<'".$sEndTime."'";
            $sHtml["endtime"] = $sEndTime;
        }
        if( isset($_GET["lotteryid"])&&is_numeric($_GET["lotteryid"]) )
        {
            $iLotteryId = intval($_GET["lotteryid"]);
            if( $iLotteryId>0 )
            {
                $sHtml["lotteryid"] = $iLotteryId;
                $sWhere .=" AND O.`lotteryid`='".$iLotteryId."'";
                if( isset($_GET["methodid"])&&is_numeric($_GET["methodid"]) )
                {
                    $iMethodId = intval($_GET["methodid"]);
                    if( $iMethodId>0 )
                    {
                        $sHtml["methodid"] = $iMethodId;
                        $sWhere .=" AND O.`methodid`='".$iMethodId."'";
                    }
                }
                else
                {
                    $sHtml["methodid"] = 0;
                }
                if( isset($_GET["issue"])&&!empty($_GET["issue"]) )
                {
                    $sIssue = $_GET["issue"];
                    if( $sIssue<>"0" )
                    {
                        $sHtml["issue"] = $sIssue;
                        $sWhere .=" AND P.`issue`='".$sIssue."'";
                    }
                }
                else
                {
                    $sHtml["issue"] = "0";
                }
            }
            else
            {
                $sHtml["lotteryid"] = 0;
                $sHtml["methodid"]  = 0;
                $sHtml["issue"]     = "0";
            }
        }
        else
        {
            $sHtml["lotteryid"] = 0;
            $sHtml["methodid"]  = 0;
            $sHtml["issue"]     = "0";
        }
        //当前身份的转化
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user");
        if( intval($_SESSION["usertype"])==2 )
        {//总代管理员
            $bIsAdmin = TRUE;
            $iUserId = $oUser->getTopProxyId( intval($_SESSION["userid"]), FALSE ); //获取总代
            if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
            { //为销售
                $sUserWhere = " AND UT.`lvproxyid` in (SELECT `topproxyid` FROM `useradminproxy`"
                    ." WHERE `adminid`='".intval($_SESSION["userid"])."')";
            }
            else
            {
                $sUserWhere = " AND UT.`lvtopid`='".intval($iUserId)."'";
            }
        }
        else
        {
            $iUserId = intval($_SESSION["userid"]);
            $bIsAdmin = FALSE;
            $sUserWhere = " AND (find_in_set('".$iUserId."',UT.`parenttree`)"
                        ." OR (UT.`userid`='".$iUserId."'))";
        }
        if( $bIsAdmin )
        {
            $bInclude = TRUE;
        }
        else
        {
            $bInclude = FALSE;
        }
        //用户名以及是否包含(支持*号,不支持包含)
        if( isset($_GET["username"])&&!empty($_GET["username"]) )
        { //指定了用户名
            $sUserName = daddslashes( $_GET["username"] );
            if( strstr($sUserName,'*') )
            { // 支持模糊搜索
                $sWhere .= " AND UT.`username` LIKE '".str_replace("*","%",$sUserName)."'";
                $sHtml["include"] = 0; //支持*,不支持包含下级
                $iUserId  = 0;
                $bInclude = FALSE;
                $sHtml["username"] = stripslashes_deep($sUserName);
            }
            else
            { //不支持模糊搜索
                $iUser = $oUser->getUseridByUsername($sUserName); //获取ID
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
            if( isset($_GET["include"])&&is_numeric($_GET["include"]) )
            {
                $bInclude = TRUE;
                $iUserId = 0;
                $sHtml["include"] = 1;
            }
        }
        //类型
        /* @var $oOrders model_orders */
        // $oOrders = A::singleton("model_orders");
        $oOrders = new model_orders( $GLOBALS['aSysDbServer']['report'] );
        $aType   = $oOrders->getOrderType( "arr", "", " AND `displayforuser`='1'" );
        $GLOBALS['oView']->assign( "type", $aType );
        if( isset($_GET["ordertype"])&&is_numeric($_GET["ordertype"]) )
        {
            $iOrderType = intval($_GET["ordertype"]);
            if( $iOrderType>0 )
            {
                $sHtml["ordertype"] = $iOrderType;
                $sWhere .= " AND O.`ordertypeid`='".$iOrderType."'";
            }
        }
        //下面是进行编号处理
        if( isset($_GET["type"])&&is_numeric($_GET["type"]) )
        {
            $type = intval( $_GET["type"] );
            $sHtml["type"] = $type;
            if( $type==1 )
            {
                if( isset($_GET["code"])&&!empty($_GET["code"]) )
                {
                    $iCode = model_projects::ProjectEnCode( $_GET["code"], "DECODE" );
                    if( $iCode>0 )
                    {
                        $sHtml["code"] = stripslashes_deep( $_GET["code"] );
                        $sWhere = " AND O.`projectid`='".$iCode."' ";
                        $iUserId = 0;
                        $bInclude = TRUE;
                    }
                }
            }
            elseif( $type==2 )
            {
                if( isset($_GET["code"])&&!empty($_GET["code"]) )
                {
                    $iCode = model_task::TaskEnCode($_GET["code"],"DECODE");
                    if( $iCode >0 )
                    {
                        $sHtml["code"] = stripslashes_deep( $_GET["code"] );
                        $sWhere = " AND O.`taskid`='".$iCode."' ";
                        $iUserId = 0;
                        $bInclude = TRUE;
                    }
                }
            }
            elseif( $type==3 )
            {
                if(isset($_GET["code"])&&!empty($_GET["code"]))
                {
                    $iCode = model_orders::orderEnCode($_GET["code"],"DECODE");
                    if( $iCode>0 )
                    {
                        $sHtml["code"] = stripslashes_deep( $_GET["code"] );
                        $sWhere = " AND O.`entry`='".$iCode."' ";
                        $iUserId = 0;
                        $bInclude = TRUE;
                    }
                }
            }
        }
        $iPage = isset($_GET["p"])&&is_numeric($_GET["p"]) ? intval($_GET["p"]) :1;
        $sWhere .= $sUserWhere;
        $aOrders = $oOrders->userOrderList( $iUserId, $bInclude, "", $sWhere,
                                " ORDER BY O.`entry` DESC", 25, $iPage );
        $fPageTotal = 0.00;
        foreach($aOrders["results"] as &$orders)
        {
            $fPageTotal += $orders["operations"] == 0 ? -$orders['amount'] : $orders['amount'];
            $orders["entry"] = model_orders::orderEnCode("O".date("Ymd",strtotime($orders["times"]))."-".$orders["entry"],"ENCODE");
            if( intval($orders["projectid"])>0 )
            {
                $orders["projectid"] = model_projects::ProjectEnCode("D".$orders["issue"]."-".$orders["projectid"],"ENCODE");
            }
        }
        //实例化
        $GLOBALS["oView"]->assign( "aOrders",    $aOrders["results"] );
        $GLOBALS['oView']->assign( "pagetotal", $fPageTotal );
        $GLOBALS["oView"]->assign( "data_issue", json_encode($aIssue) );
        $GLOBALS['oView']->assign( "lottery",    $aLottery );
        $GLOBALS['oView']->assign( "s",          $sHtml );
        $oPage = new pages($aOrders["affects"],25);
        $GLOBALS['oView']->assign( "pageinfo",    $oPage->show(1) );
        $GLOBALS['oView']->assign( "data_method", json_encode($aMethods) );
        $GLOBALS['oView']->assign( "actionlink",  array('text'=>'清空搜索条件','href'=>url('report','orders')));
        $GLOBALS['oView']->assign( "ur_here",     "帐变列表" );
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display("report_orders.html");
        EXIT;
    }



    /**
     * 报表查询
     * URL: ./index.php?controller=report&action=list
     * @author SAUL
     */
    function actionList()
    {
        $aLocation[0] = array("text"=>'清空查询条件',"href"=>url('report','list'));
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetList("a.`methodid`,a.`methodname`,b.`lotteryid`,b.`cnname`",
                " a.`pid`>0",'',0);
        $aLottery = array();
        $aMethods = array();
        foreach($aMethod as $method)
        {
            $aLottery[$method["lotteryid"]] = $method["cnname"];
            $aMethods[$method["lotteryid"]][$method["methodid"]] = $method["methodname"];
        }
        $GLOBALS['oView']->assign( "aLottery",      $aLottery );
        $GLOBALS['oView']->assign( "data_method",   json_encode($aMethods) );
        //参数整理
        $sWhere = "";
        $sUserWhere = "";
        if( isset($_GET["starttime"])&&!empty($_GET["starttime"]) )
        {
            $sStartTime = getFilterDate( $_GET["starttime"] );
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
        	//$sStartTime = date("Y-m-d 02:20:00");
        }
        if( $sStartTime<>"" )
        {
            $sHtml["starttime"] = $sStartTime;
            $sWhere .=" AND P.`writetime`>='".$sStartTime."'";
        }
        if( isset($_GET["endtime"])&&!empty($_GET["endtime"]) )
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
        	//$sEndTime = date("Y-m-d 02:20:00",strtotime("+1 days"));
        }
        if( !empty($sEndTime) )
        {
            $sHtml["endtime"]    = $sEndTime;
            $sWhere             .= " AND P.`writetime`<='".$sEndTime."'";
        }
        $iLotteryId = isset($_GET["lottery"])&&is_numeric($_GET["lottery"]) ? intval($_GET["lottery"]) : 0;
        if( $iLotteryId>0 )
        {
            $sHtml["lottery"] = $iLotteryId;
            $sWhere     .= " AND P.`lotteryid`='".$iLotteryId."'";
            $iMethodId   = isset($_GET["method"])&&is_numeric($_GET["method"]) ? intval($_GET["method"]) : 0;
            if( $iMethodId>0 )
            {
                $sHtml["method"] = $iMethodId;
                $sWhere         .= " AND P.`methodid`='".$iMethodId."'";
            }
            else
            {
                $sHtml["method"] = 0;
            }
        }
        else
        {
            $sHtml["lottery"] = 0;
            $sHtml["method"] = 0;
        }
        $GLOBALS['oView']->assign("ur_here", "报表查询" );
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user");
        /* @var $oOrders model_orders */
//        $oOrders = A::singleton("model_orders");
        $oOrders = new model_orders($GLOBALS['aSysDbServer']['report']);
        if( intval($_SESSION["usertype"])==2 )
        {//管理员
            $iUserId = $oUser->getTopProxyId(intval($_SESSION["userid"]), FALSE ); //获取总代
            if( $oUser->IsAdminSale( intval($_SESSION["userid"]) ) )
            { //为销售
                $sUserWhere = " AND a.`lvproxyid` IN (SELECT `topproxyid` FROM `useradminproxy`"
                                ." WHERE `adminid`='".$_SESSION["userid"]."')";
            }
            else
            {
                $sUserWhere = " AND a.`lvtopid`='".$iUserId."'";
            }
        }
        else
        {
            $iUserId    = intval($_SESSION["userid"]);
            $sUserWhere = " AND (FIND_IN_SET('".$iUserId."',a.`parenttree`) OR (a.`userid`='".$iUserId."'))";
        }
        //代理
        $aProxy = $oUser->getChildrenListID( intval($iUserId), FALSE, $sUserWhere, FALSE );
        $GLOBALS['oView']->assign( "proxy", $aProxy );
        $iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) : intval($iUserId);
        if( isset($_GET["proxyid"])&&!empty($_GET["proxyid"]) )
        {
            if( intval($_GET["proxyid"])>0 )
            {
                $iUserId = intval($_GET["proxyid"]);
                $sHtml["proxyid"] = $iUserId;
            }
        }
        if( !$oUser->isTopProxy($iUserId) )
        {//非总代查询
            $Self   = array();
            $aSelf  = $oOrders->getUserBonusAndPrize( $iUserId, $sWhere, false );
            if( !empty($aSelf) )
            {
                $Self["username"]   = $aSelf["username"];
                $Self["usertype"]   = $aSelf["usertype"];
                $Self["sum_bonus"]  = $aSelf["bonus"];
                $Self["sum_point"]  = $aSelf["point"];
                $Self["sum_prize"]  = $aSelf["price"];
                $Self["sum_total"]  = $Self["sum_prize"]-$Self["sum_point"]-$Self["sum_bonus"];
                $Self["real_prize"] = $Self["sum_prize"]-$Self["sum_point"];	
            }
            $GLOBALS['oView']->assign( "self",$Self );
        }
        if( $oUser->isTopProxy($_SESSION["userid"]) )
        {//销售管理员
            $sHtml["show"]  = 1;
            /* @var $oUserAdmin model_useradminproxy */
            $oUserAdmin     = A::singleton("model_useradminproxy");
            $aUserAdmin     = $oUserAdmin->getAdminSaleList( $_SESSION["userid"] );        	
            $GLOBALS['oView']->assign( 'adminProxy', $aUserAdmin );
            if( isset($_GET["proxyadmin"])&&is_numeric($_GET["proxyadmin"]) )
            { //增加查询条件(用户的)
                $sHtml["proxyadmin"] = intval( $_GET["proxyadmin"] );
                if( $sHtml["proxyadmin"]>0 )
                {
                    $sUserWhere .= " AND a.`lvproxyid` in "
                    ."(SELECT `topproxyid` FROM `useradminproxy` "
                    ."where `adminid`='".$_GET["proxyadmin"]."')";
                }
            }
        }
        $GLOBALS['oView']->assign( "s", $sHtml );
        $aUser = $oUser->getChildrenListID( $iUserId, FALSE, $sUserWhere, FALSE );
        $aUsers = array();
        foreach($aUser as $user)
        {
            $aUsers[$user["userid"]]["username"] = $user["username"]; 
            $aUsers[$user["userid"]]["usertype"] = $user["usertype"];
            $aUsers[$user["userid"]]["sum_bonus"] = 0.00;
            $aUsers[$user["userid"]]["sum_point"] = 0.00;
            $aUsers[$user["userid"]]["sum_prize"] = 0.00;
            $aUsers[$user["userid"]]["sum_total"] = 0.00;
            $aUsers[$user["userid"]]["real_prize"] = 0.00;
        }
        //代购返点费用
        $aResult = $oOrders->getTotalBonusAndPrize($iUserId, $sWhere, $sUserWhere, false);
        foreach( $aResult as $i=>$v )
        {
            if( isset($aUsers[$i]) )
            {
                $aUsers[$i]["sum_point"] = $v["point"];
                $aUsers[$i]["sum_bonus"] = $v["bonus"];
                $aUsers[$i]["sum_prize"] = $v["prize"];
            }
        }
        //合计
        $aTotal = array(
            "sum_prize"     =>  0.00,
            "real_prize"    =>  0.00,
            "sum_point"     =>  0.00,
            "sum_bonus"     =>  0.00,
            "sum_total"     =>  0.00
        );
        foreach( $aUsers as &$user )
        {
            $user["real_prize"] = $user["sum_prize"] - $user["sum_point"];
            $user["sum_total"] = $user["sum_prize"] - $user["sum_point"] - $user["sum_bonus"];
            $aTotal["sum_prize"] = $aTotal["sum_prize"] + $user["sum_prize"];
            $aTotal["real_prize"] = $aTotal["real_prize"] + $user["real_prize"];
            $aTotal["sum_point"] = $aTotal["sum_point"] + $user["sum_point"];
            $aTotal["sum_bonus"] = $aTotal["sum_bonus"] + $user["sum_bonus"];
            $aTotal["sum_total"] = $aTotal["sum_total"] + $user["sum_total"];
        }
        if( isset($Self) )
        {
            $aTotal["sum_prize"] = $aTotal["sum_prize"] + $Self["sum_prize"];
            $aTotal["real_prize"] = $aTotal["real_prize"] + $Self["real_prize"];
            $aTotal["sum_point"] = $aTotal["sum_point"] + $Self["sum_point"];
            $aTotal["sum_bonus"] = $aTotal["sum_bonus"] + $Self["sum_bonus"];
            $aTotal["sum_total"] = $aTotal["sum_total"] + $Self["sum_total"];
        }
        $GLOBALS['oView']->assign( "total", $aTotal );
        $GLOBALS['oView']->assign( 'aUsers', $aUsers );
        $GLOBALS['oView']->assign( "actionlink", $aLocation[0] );
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display("report_list.html");
        EXIT;
    }




    /**
     * 查看一个用户团队的游戏明细
     * @author SAUL
     * URL:./index.php?controller=report&action=reportdetail 
     */
    function actionReportdetail()
    {
        $aLocation[0] = array("text"=>'关闭',"url"=>'javascript:close();');
        $sWhere = "";
        if( isset($_GET["starttime"])&&!empty($_GET["starttime"]) )
        {
            $startTime = getFilterDate($_GET["starttime"]);
        }
        else
        {
            $startTime = date("Y-m-d 00:00:00");
        }
        if( $startTime!="" )
        {
            $sWhere .=" AND P.`writetime`>='".$startTime."'";
        }
        if( isset($_GET["endtime"])&&!empty($_GET["endtime"]) )
        {
            $endTime = getFilterDate($_GET["endtime"]);
        }
        else
        {
            $endTime = date("Y-m-d 00:00:00",strtotime("+1 days"));
        }
        if( $endTime!="" )
        {
            $sWhere .=" AND P.`writetime`<='".$endTime."'";
        }
        $s["starttime"] = $startTime;
        $s["endtime"]   = $endTime;
        $iUserId        = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) :0;
        $s["userid"]    = $iUserId;
        $oUser   = A::singleton("model_user");
        $iSelfUserId = $_SESSION['userid'];
        if( $_SESSION['usertype'] == 2 )
        {
            //如果为总代管理员，则当前用户调整到其总代ID
            $iSelfUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iSelfUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            //检测是否为销售管理员
            if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
            {//是销售管理员
                if( !empty($iUserId) )
                {//检测是否在自己范围之内
                    if( FALSE == $oUser->isInAdminSale( $iUserId, $_SESSION['userid']) )
                    {
                        sysMsg( "没有权限", 2 );
                    }
                }
            }
        }
        //检查是否有权限[必须是在自己树下的用户才能查看]
        if( FALSE == $oUser->isParent($iUserId, $iSelfUserId) )
        {
            sysMsg( "没有权限", 2 );
        }
        /* @var $oMethod model_method */
        $oMethod        = A::singleton("model_method");
        $aMethod        = $oMethod->methodGetList( "a.`methodid`,a.`lotteryid`,a.`methodname`,b.`cnname`",
                "a.`pid`>0", "", 0 );
        $aLottery =array();
        foreach( $aMethod as $method )
        {
            $aLottery[$method["lotteryid"]]["cnname"]                           = $method["cnname"];
            $aLottery[$method["lotteryid"]]["methodid"][]                       = $method["methodid"];
            $aLottery[$method["lotteryid"]]["method"][$method["methodid"]]      = $method["methodname"];
            $aLottery[$method["lotteryid"]]["sum_bonus"][$method["methodid"]]   = 0.00;
            $aLottery[$method["lotteryid"]]["sum_point"][$method["methodid"]]   = 0.00;
            $aLottery[$method["lotteryid"]]["sum_prize"][$method["methodid"]]   = 0.00;
            $aLottery[$method["lotteryid"]]["real_prize"][$method["methodid"]]  = 0.00;
            $aLottery[$method["lotteryid"]]["total"][$method["methodid"]]       = 0.00;
        }
        foreach( $aLottery as $i=>$v )
        {
            $aLottery[$i]["count"] = count($v["methodid"]);
        }
        /* @var $oOrders model_orders */
        $oOrders = A::singleton("model_orders");
        $aResult = $oOrders->getTotalUserBonusByMethod( $iUserId, $sWhere, true, '', false );
        if( empty($aResult) )
        {
            sysMsg( "没有权限", 1, $aLocation );
        }
        foreach( $aResult[1] as $v )
        { // 返点总额
            $aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]] = $v["summoney"];
        }
        foreach( $aResult[2] as $v )
        { //奖金总额 + 购彩金额
            $aLottery[$v["lotteryid"]]["sum_bonus"][$v["methodid"]] = $v["sumbonus"];
            $aLottery[$v["lotteryid"]]["sum_prize"][$v["methodid"]] = $v["sumprice"];
        }
        foreach($aLottery as $i=>$v)
        {
            foreach($v["methodid"] as $v1)
            {
                $aLottery[$i]["real_prize"][$v1]    = $aLottery[$i]["sum_prize"][$v1]  - $aLottery[$i]["sum_point"][$v1];
                $aLottery[$i]["total"][$v1]         = $aLottery[$i]["real_prize"][$v1] - $aLottery[$i]["sum_bonus"][$v1];
            }
        }
        $aTotal = array();
        foreach( $aLottery as $i=>$v )
        {
            $aTotal["sum_bonus"][$i]    = array_sum($v["sum_bonus"]);
            $aTotal["sum_prize"][$i]    = array_sum($v["sum_prize"]);
            $aTotal["real_prize"][$i]   = array_sum($v["real_prize"]);
            $aTotal["total"][$i]        = array_sum($v["total"]);
        }
        $GLOBALS['oView']->assign( "s",         $s );
        $GLOBALS['oView']->assign( "lottery",   $aLottery );
        $GLOBALS['oView']->assign( "total",     $aTotal );
        $GLOBALS['oView']->assign( "ur_here",   "游戏明细" );
        $oOrders->assignSysInfo();
        $GLOBALS['oView']->display("report_reportdetail.html");
        EXIT;
    }
}
?>