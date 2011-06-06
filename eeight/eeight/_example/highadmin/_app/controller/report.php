<?php
/**
 * 文件 : /_app/controller/report.php
 * 功能 : 控制器 - 报表管理
 * 
 * 功能:
 * + actionOrderList        帐变管理
 * + actionSaleList         盈亏报表
 * + actionSaleDetail       游戏明细
 * + actionTransferlist     频道转账表
 * + actionFundlist         游戏币明细表(团队)
 * + actionSelfFundlist     游戏币明细表(自身)
 * + actionSnapshot         查看高频快照报表
 * + actionSinglesale       查询单期盈亏报表
 * + actionRecentbuy        代理最近投单量查询
 * 
 * 
 * @author      Tom,MARK
 * @version     1.2.0 
 * @package     highadmin
 */

class controller_report extends basecontroller
{
    /**
     * 帐变管理
     * URL:/index.php?controller=report&action=Orderlist
     * @author MARK
     */
    function actionOrderlist()
    {
        set_time_limit(240); // 限制4min
        // 01, 整理搜索条件
        $aSearch['otid']       = isset($_GET['otid'])     ? $_GET['otid'] : -1; // 默认全部类型
        $aSearch['adminid']    = isset($_GET['adminid'])  ? intval($_GET['adminid']) : -1; // 默认不限
        $aSearch['clientip']   = isset($_GET['clientip']) ? daddslashes(trim($_GET['clientip'])) : "";
        $aSearch['pn']         = isset($_GET['pn'])&&in_array($_GET['pn'],array(25,50,75,100,150,200)) ? intval($_GET['pn']) : 50;
        $aSearch['orderno']    = isset($_GET['orderno'])  ? daddslashes(trim($_GET['orderno'])) : "";
        $aSearch['sdate']      = isset($_GET['sdate'])    ? trim($_GET['sdate']) : date('Y-m-d 00:00:00'); // 当天账变
        $aSearch['edate']      = isset($_GET['edate'])    ? trim($_GET['edate']) : date('Y-m-d 23:59:59');
        $aSearch['tproxyid']   = isset($_GET['tproxyid']) ? intval($_GET['tproxyid']) : -1;
        $aSearch['sel']        = isset($_GET['sel'])      ? intval($_GET['sel']) : 2; // 默认用户名输入框
        $aSearch['username']   = isset($_GET['username']) ? daddslashes(trim($_GET['username'])) : "";
        $aSearch['included1']  = isset($_GET['included1'])? 1 : 0;
        $aSearch['included2']  = isset($_GET['included2'])? 1 : 0;
        $aSearch['included3']  = isset($_GET['included3'])? 1 : 0; // 不含自身
        $aSearch['af1']        = isset($_GET['af1'])      ? intval($_GET['af1']) : 4;
        $aSearch['amount1']    = isset($_GET['amount1'])  ? intval($_GET['amount1']) : "";
        $aSearch['af2']        = isset($_GET['af2'])      ? intval($_GET['af2']) : 1;
        $aSearch['amount2']    = isset($_GET['amount2'])  ? intval($_GET['amount2']) : "";
        $aSearch['lottery']    = isset($_GET["lottery"])&&is_numeric($_GET["lottery"]) ? intval($_GET["lottery"]) : -1;
        $aSearch['crowdid']    = isset($_GET["crowdid"])&&is_numeric($_GET["crowdid"]) ? intval($_GET["crowdid"]) : 0;
        $aSearch['pid']        = isset($_GET["pid"])&&is_numeric($_GET["pid"]) ? intval($_GET["pid"]) : 0;
        $aSearch['method']     = isset($_GET["methodid"])&&is_numeric($_GET["methodid"]) ? intval($_GET["methodid"]) : 0;
        $aSearch['modes']      = isset($_GET["modes"])&&is_numeric($_GET["modes"]) ? intval($_GET["modes"]) : 0;
        $aSearch['sdate']      = getFilterDate( $aSearch['sdate'] );
        $aSearch['edate']      = getFilterDate( $aSearch['edate'] );
        $aHtmlValue                 = array();
        $aHtmlValue['modes']        = $aSearch['modes'];
        $aHtmlValue['sel']          = $aSearch['sel'];
        $aHtmlValue['tproxyid']     = $aSearch['tproxyid'];
        $aHtmlValue['lotteryid']    = $aSearch['lottery'];
        $aHtmlValue['crowdid']      = $aSearch['crowdid'];
        $aHtmlValue['pid']          = $aSearch['pid'];
        $aHtmlValue['methodid']     = $aSearch['method'];
        $aHtmlValue['af1']          = $aSearch['af1']; // 默认大于
        $aHtmlValue['otid']         = $aSearch['otid'];
        $aHtmlValue['included3']    = $aSearch['included3'];
        if( $aHtmlValue['sel'] == 1 )
        { // HTML 数据整理, 当选择一个层进行数据搜索时, 其他层的数据使其无效化
            $aHtmlValue['included1'] = $aSearch['included1'];
            $aHtmlValue['tproxyid'] = -1;
            $aHtmlValue['included2'] = 0;
        }
        if( $aHtmlValue['sel'] == 2 )
        {
            $aHtmlValue['included2'] = $aSearch['included2'];
            $aHtmlValue['username'] = '';
            $aHtmlValue['included1'] = 0;
        }
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        // 0001, 索引: entry
        /* @var $oOrder model_orders */
        $oOrder = A::singleton("model_orders", $GLOBALS['aSysDbServer']['report']);
        if( $aSearch['orderno'] != '' )
        { // 订单号搜索
            $aOrderNo = model_projects::HighEnCode( $aSearch['orderno'], 'DECODE' );
            if( $aOrderNo != 0 )
            {
                $sWhere .= " AND o.`entry` = '".intval($aOrderNo)."' ";
            }
            else
            {
                $sWhere .= " AND o.`entry` = 0 ";
            }
            $aHtmlValue['orderno'] = stripslashes_deep($aSearch['orderno']);
        }
        // 0002, 索引 times
        if( $aSearch['sdate'] != '' )
        { // 账变时间 起始于...
            $sWhere .= " AND o.`times` >= '".daddslashes($aSearch['sdate'])."' ";
            $aHtmlValue['sdate']  =  stripslashes_deep($aSearch['sdate']);
        }
        if( $aSearch['edate'] != '' )
        { // 账变时间 截止于...
            $sWhere .= " AND o.`times` <= '".daddslashes($aSearch['edate'])."' ";
            $aHtmlValue['edate']  =  stripslashes_deep($aSearch['edate']);
        }
        // 0003, 索引 amount
        if( $aSearch['amount1'] != '' && in_array( $aSearch['af1'], array(1,2,3,4,5) ) )
        { // 资金条件范围1   1=小于, 2=小于等于, 3=等于, 4=大于, 5=大于等于
            $sFlag = '';
            switch( $aSearch['af1'] )
            {
                case 1: $sFlag = '<';  break;
                case 2: $sFlag = '<='; break;
                case 3: $sFlag = '=';  break;
                case 4: $sFlag = '>';  break;
                case 5: $sFlag = '>='; break;
            }
            $sWhere .= " AND o.`amount` $sFlag '".$aSearch['amount1']."' ";
            $aHtmlValue['amount1'] = $aSearch['amount1'];
        }

        if( $aSearch['amount2'] != '' && in_array( $aSearch['af2'], array(1,2,3,4,5) ) )
        {
            $sFlag = '';
            switch( $aSearch['af2'] )
            {
                case 1: $sFlag = '<';  break;
                case 2: $sFlag = '<='; break;
                case 3: $sFlag = '=';  break;
                case 4: $sFlag = '>';  break;
                case 5: $sFlag = '>='; break;
            }
            $sWhere .= " AND o.`amount` $sFlag '".$aSearch['amount2']."' ";
            $aHtmlValue['amount2'] = $aSearch['amount2'];
            $aHtmlValue['af2'] = $aSearch['af2'];
        }
        // 0004, 用户ID 部分 
        // 用户ID部分
        if( $aSearch['sel'] == 1 && $aSearch['username'] != '' )
        {
            // 获取用户ID
            /* @var $oUser model_user */
            $oUser = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
            $iUserId = $oUser->getUseridByUsername( $aSearch['username'] );
            if( $iUserId == 0 || is_array($iUserId) )
            { // 搜索的用户名未找到, 并且不允许通配符搜索
                $sWhere .= " AND 0 ";
            }
            else
            {
                if( $aSearch['included1'] == 1 )
                { // 包含下级
                    $sWhere .= " AND ( ut.`userid`='".$iUserId."' OR FIND_IN_SET('".$iUserId."',ut.`parenttree`) ) ";
                }
                else 
                {
                    $sWhere .= " AND ut.`userid` = '$iUserId' ";
                }
            }
            $aHtmlValue['username'] = stripslashes_deep($aSearch['username']);
        }

        if( $aSearch['sel'] == 2 && $aSearch['tproxyid'] != -1 )
        { // 总代ID搜索
            $iUserId = intval( $aSearch['tproxyid']);
            if( $aSearch['included2'] == 1 )
            { // 包含下级
                $sWhere .= " AND ( ut.`userid`='".$iUserId."' OR FIND_IN_SET('".$iUserId."',ut.`parenttree`) ) ";
            }
            else 
            {
                $sWhere .= " AND ut.`userid` = '$iUserId' ";
            }
            if( $aSearch['included3'] == 1 )
            { // 不包含自身
                $sWhere .= " AND ut.`userid` != '$iUserId' ";
            }
            $aHtmlValue['tproxyid'] = $aSearch['tproxyid'];
        }
        // 0006, 账变类型索引
        if( is_int($aSearch['otid']) && $aSearch['otid'] != -1 )
        { // URL 方式传递的单个ID
            $sWhere .= " AND o.`ordertypeid` = '".intval($aSearch['otid'])."' ";
            $aHtmlValue['otid'] = $aSearch['otid'];
            $_GET['otid'] = intval($aSearch['otid']);
        }
        if( is_array($aSearch['otid']) && !in_array( -1, $aSearch['otid'] ) )
        { // HTML 提交的账变类型搜索数组
            $sOtidString = '';
            foreach( $aSearch['otid'] AS &$v )
            {
                if( !is_numeric($v) )
                {
                    unset($v);
                }
            }
            $sOtidString = join(",", $aSearch["otid"] );
            if( !empty($sOtidString) )
            {
                $sWhere .= " AND o.`ordertypeid` IN ( $sOtidString ) ";
                $_GET['otid'] = $sOtidString;
                $aHtmlValue['otid'] = $sOtidString;
            }
        }
        if( is_string($aSearch['otid']) )
        { // URL 方式传递的 otid=1,2,3,4.. (用于分页)
            $sOtidString = '';
            if( strstr( $aSearch['otid'], ',' ) )
            {
                $aSearch['otid'] = explode(',', $aSearch['otid'] );
                if( in_array( -1, $aSearch['otid']) )
                {
                    $aHtmlValue['otid'] = -1;
                    $sOtidString = -1;
                }
                else
                {
                    foreach( $aSearch['otid'] AS &$v )
                    {
                        if( !is_numeric($v))
                        {
                            unset($v);
                        }
                    }
                    $sOtidString = join("," , $aSearch['otid']);
                }
            }
            else 
            {
                $sOtidString = intval($aSearch['otid']);
            }
            if( $sOtidString != -1 )
            {
                $sWhere .= " AND o.`ordertypeid` IN ( $sOtidString ) ";
            }
            $_GET['otid'] = $sOtidString;
            $aHtmlValue['otid'] = $sOtidString;
        }
        if( is_array($aHtmlValue['otid']) && in_array(-1, $aHtmlValue['otid']) )
        {
            $aHtmlValue['otid'] = -1;
            $_GET['otid'] = -1;
        }
        // 0007, 管理员ID 索引
        if( $aSearch['adminid'] > 0 )
        {
            $sWhere .= " AND o.`adminid` = '".intval($aSearch['adminid'])."' ";
        }
        if( $aSearch['adminid'] == -2 )
        {
            $sWhere .= " AND o.`adminid` != 0 ";
            $aHtmlValue['adminid'] = -2;
        }
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method", $GLOBALS['aSysDbServer']['report']);
        // 0008 彩种ID
        if( $aSearch["lottery"] >= 0 )
        {
            $sWhere .= " AND o.`lotteryid` = '".intval($aSearch["lottery"])."'";
            // 玩法
            if( $aSearch["lottery"]>0 )
            {
                if( $aSearch["crowdid"] > 0)
                {
                    $aMethodid = $oMethod->methodOneGetList("methodid","`crowdid` = '".$aSearch["crowdid"]."'");
                    $aAllMethodid = array();
                    foreach ($aMethodid as $atmpMethodid)
                    {
                        $aAllMethodid[] = $atmpMethodid['methodid'];
                    }
                    $sWhere .= " AND o.`methodid` IN (".implode(",",$aAllMethodid).")";
                }
                if( $aSearch["pid"] > 0)
                {
                    $aMethodid = $oMethod->methodOneGetList("methodid","`pid` = '".$aSearch["pid"]."'");
                    $aAllMethodid = array();
                    foreach ($aMethodid as $atmpMethodid)
                    {
                        $aAllMethodid[] = $atmpMethodid['methodid'];
                    }
                    $sWhere .= " AND o.`methodid` IN (".implode(",",$aAllMethodid).")";
                }
                if( $aSearch["method"] > 0)
                {
                    $sWhere .= " AND o.`methodid`='".intval($aSearch["method"])."'";
                }
            }
        }
        if( $aSearch['clientip'] != '' )
        { // 操作地址模糊搜索
            if( strstr($aSearch['clientip'],'*') )
            {
                $sWhere .= " AND o.`clientip` LIKE '". str_replace( '*', '%', $aSearch['clientip'] ) ."' ";
            }
            else
            {
                $sWhere .= " AND o.`clientip` = '".$aSearch['clientip']."' ";
            }
            $aHtmlValue['clientip'] = h(stripslashes_deep($aSearch['clientip']));
        }

        // 模式
        if($aSearch["modes"]>0)
        {
            $sWhere .= " AND o.`modes`='".intval($aSearch["modes"])."'";
        }
        
        // 每页记录数
        $aHtmlValue['pn'] = $aSearch['pn'];
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = $aSearch['pn'];                                  // 分页用2
        $iIsGetData  = isset($_GET['isgetdata']) ? intval($_GET['isgetdata']) : 0;//是否查询数据
        $aResult = $iIsGetData == 0 ? array('affects' => 0, 'results' => array()) : $oOrder->getAdminOrderList2( '*', $sWhere, $pn , $p); // 获取数据结果集
        $aOrderType = $oOrder->getOrderType('arr');

        $aHtmlValue['iCountSR']   = 0; // 总计收入
        $aHtmlValue['iCountZC']   = 0; // 总计支出
        $aHtmlValue['iCountAll']  = 0; // 当页总计资金变化
        if( !empty($aResult['affects']) && !empty($aResult['results']) && is_array($aResult['results']) )
        { // 进行数据整理(更新订单号), 对当页数据进行小结
            foreach( $aResult['results'] as &$v )
            {
                $v['signamount'] = $aOrderType[$v['ordertypeid']]['operations'];
                $v['orderno'] = model_projects::HighEnCode('O'.date("Ymd").'-'.$v['entry'], "ENCODE");
                if( $v['signamount'] == 0 )
                {
                    $aHtmlValue['iCountZC'] -= $v['amount'];
                }
                else
                {
                    $aHtmlValue['iCountSR'] += $v['amount'];
                }
                if($v["projectid"]>0)
                {
                    $v["projectid"] = model_projects::HighEnCode('D'.$v["issue"]."-".$v["projectid"],"ENCODE");
                }
            }
        }
        $aHtmlValue['iCountAll'] = abs($aHtmlValue['iCountSR'])-abs($aHtmlValue['iCountZC']);
        $aHtmlValue['Ordertypeopts'] = $oOrder->getOrderType('opts',$aHtmlValue['otid']);
        // 解析管理员下拉框
        $aLottery = array();
        $aMethods = array();
        $aMethodByCrowd = $oMethod->methodGetAllListByCrowd();
        foreach ($aMethodByCrowd as $iLotteryId => $aCrowd)
        {
            $aLottery[$iLotteryId] = $aCrowd['cnname'];
            $aMethods[$iLotteryId] = $aCrowd['crowd'];
        }
        $aTmpMehtodName = $oMethod->methodGetList("`methodid`,`methodname`");
        foreach ($aTmpMehtodName as $aMehtodNameDetail)
        {
            $aMehtodName[$aMehtodNameDetail['methodid']] = $aMehtodNameDetail['methodname'];
        }
        $GLOBALS['oView']->assign( "data_method", json_encode($aMethods) );
        $aHtmlValue['lottery'] = $aLottery;
        /* @var $oPassPort model_passport */
        $oPassPort        = A::singleton("model_passport", $GLOBALS['aSysDbServer']['report']);
        $aHtmlValue['Adminidopts'] =  $oPassPort->getDistintAdminName(FALSE, $aSearch['adminid'] );
        $aHtmlValue['topproxyopts']=  $oPassPort->getTopProxyName(FALSE, $aHtmlValue['tproxyid'], 0 );
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $aHtmlValue['aStat'] = array();
        if( TRUE == $oPager->isLastPage() && $oPager->getTotalPage() != 1 && $iIsGetData == 1)
        { // 最后一页, 进行数据总体结算
            $aHtmlValue['aStat'] = $oOrder->getAdminOrderStat( $sWhere );
        }
        $GLOBALS['oView']->assign( 'pages',     $oPager->show(2) ); // 分页用4
        $GLOBALS['oView']->assign( 's',         $aHtmlValue );
        $GLOBALS['oView']->assign( 'aUserList', $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign("modes", $GLOBALS['config']['modes']);
        $GLOBALS['oView']->assign( "aMehtodName", $aMehtodName );
        $GLOBALS['oView']->assign("json_modes",json_encode($GLOBALS['config']['modes']));
        $GLOBALS['oView']->assign( 'orderType',   $aOrderType );
        $GLOBALS['oView']->assign( 'ur_here',   '账变列表' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","orderlist"), 'text'=>'清空过滤条件' ) );
        $oPassPort->assignSysInfo();
        $GLOBALS['oView']->display("report_orderlist.html");
        EXIT;
    }



    /**
     * 盈亏报表
     * URL: ./index.php?comtroller=report&action=salelist
     * @author MARK
     */
    function actionSaleList()
    {
        //玩法
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
        $iModes  = isset($_GET['modes']) ? intval($_GET['modes']) : -1;//元角模式
        $iIsGetData  = isset($_GET['isgetdata']) ? intval($_GET['isgetdata']) : 0;//是否查询数据 
        $sHtml['modes'] = $iModes;
        $iCrowdId = isset($_GET["crowdid"])&&is_numeric($_GET["crowdid"]) ? intval($_GET["crowdid"]) : 0;
        $iPid     = isset($_GET["pid"])&&is_numeric($_GET["pid"]) ? intval($_GET["pid"]) : 0;
        $iMethod  = isset($_GET["methodid"])&&is_numeric($_GET["methodid"]) ? intval($_GET["methodid"]) : 0;
        $sHtml["crowdid"] = $iCrowdId;
        $sHtml["pid"] = $iPid;
        $sHtml["methodid"] = $iMethod;
        $sWhere = "";
        if(isset($_GET["lottery"])&&is_numeric($_GET["lottery"]))
        {
            $iLottery = intval($_GET["lottery"]);
            if($iLottery>0)
            {
                $sHtml["lottery"] = $iLottery;
                $sWhere .= " AND P.`lotteryid`='".$iLottery."'";
                if( $iCrowdId > 0)
                {
                    $aMethodid = $oMethod->methodOneGetList("methodid","`crowdid` = '".$iCrowdId."'");
                    $aAllMethodid = array();
                    foreach ($aMethodid as $atmpMethodid)
                    {
                        $aAllMethodid[] = $atmpMethodid['methodid'];
                    }
                    $sWhere .= " AND P.`methodid` IN (".implode(",",$aAllMethodid).")";
                }
                if( $iPid > 0)
                {
                    $aMethodid = $oMethod->methodOneGetList("methodid","`pid` = '".$iPid."'");
                    $aAllMethodid = array();
                    foreach ($aMethodid as $atmpMethodid)
                    {
                        $aAllMethodid[] = $atmpMethodid['methodid'];
                    }
                    $sWhere .= " AND P.`methodid` IN (".implode(",",$aAllMethodid).")";
                }
                if( $iMethod > 0)
                {
                    $sWhere .= " AND P.`methodid`='".$iMethod."'";
                }
            } 
        }
        //用户
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
        $aProxy = $oUser->getChildrenListID( 0, FALSE ); //代理获取
        $GLOBALS['oView']->assign( "proxy", $aProxy);
        $iUserId = 0;
        if( isset($_GET["userid"])&&is_numeric($_GET["userid"]) )
        {
            $iUser = intval($_GET["userid"]);
            if($iUser > 0 )
            {
                $iUserId = intval( $_GET["userid"] );
            }
        }
        if( isset($_GET["proxy"])&&is_numeric($_GET["proxy"]) )
        {
            $iProxy = intval($_GET["proxy"]);
            $sHtml["proxy"] = $iProxy;
            if( $iProxy>=0 )
            {
                $iUserId = intval( $iProxy );
            }
        }
        $sHtml["userid"] = $iUserId;
        //时间
        if(isset($_GET["starttime"])&&!empty($_GET["starttime"]))
        {
            $starttime = getFilterDate($_GET["starttime"]);
        }
        else
        {
            $starttime = date("Y-m-d 02:20:00",strtotime("-1 days"));
        }
        if( $starttime!="" )
        {
            $sHtml["starttime"] = $starttime;
            $sQueryStartTime = $starttime;
        }
        if(isset($_GET["endtime"])&&!empty($_GET["endtime"]))
        {
            $endtime = getFilterDate($_GET["endtime"]);
            
        }
        else
        {
        	$endtime = date("Y-m-d 02:20:00");
        }
        if( $endtime!="" )
        {
            $sHtml["endtime"]  = $endtime;
            $sQueryEndTime = $endtime;
        }
        if( $iModes != -1 )
        {
            $sWhere .= " AND P.`modes` = '" . $iModes ."'";
        }
        /* @var $oOrders model_orders */
        $oOrders = A::singleton("model_orders", $GLOBALS['aSysDbServer']['report']);
        if( $iUserId > 0 && $iIsGetData == 1 )
        { //自身部分这一行
            $Self = array();
            $aSelf = $oOrders->getAdminUserBonusAndPrize($iUserId, $sWhere, $sQueryStartTime, $sQueryEndTime);
            if( !empty($aSelf) )
            {
                $self["userid"]   = $iUserId;
                $Self["username"] = $aSelf["username"];
                $Self["usertype"] = $aSelf["usertype"];
                $Self["sum_bonus"] = $aSelf["bonus"];
                $Self["sum_point"] = $aSelf["point"];
                $Self["sum_prize"] = $aSelf["price"];
                $Self["sum_total"] = $Self["sum_prize"]-$Self["sum_point"]-$Self["sum_bonus"];
                $Self["real_prize"] = $Self["sum_prize"]-$Self["sum_point"];
            }
            $GLOBALS['oView']->assign("self", $Self);
        }
        $sUserWhere = "";
        //测试账户
        $bIsTester = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
        if( $bIsTester != -1 && $iUserId == 0 )
        {
            $sUserWhere .= " AND a.`istester` = " . $bIsTester;
        }
        //冻结账户
        $bIsFrozen = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
        if( $bIsFrozen != -1 && $iUserId == 0 )
        {
            if( $bIsFrozen > 0 )//显示冻结总代,除去非冻结总代
            {
                $aTopProxyList = $oUser->getChildListID( 0, " AND a.`isfrozen` > 0" );
            }
            elseif ( $bIsFrozen == 0 )//如果为非冻结总代,除去冻结总代
            {
                $aTopProxyList = $oUser->getChildListID( 0, " AND a.`isfrozen` = 0" );
            }
            $aTopProxyIdList = array();
            foreach ( $aTopProxyList as $aTopProxy )
            {
                $aTopProxyIdList[] = $aTopProxy['userid'];
            }
            $sTopProxyId = implode( ',', $aTopProxyIdList );
            if( $sTopProxyId == '' )
            {
                $sTopProxyId = '0';
            }
            $sUserWhere .= " AND a.`lvtopid` in( " . $sTopProxyId . ")";//查询在指定列表中总代数据
        }
        $aResult = $iIsGetData ==0 ? array() : $oOrders->getAdminTotalBonusAndPrize( $iUserId, $sWhere, $sUserWhere, $sQueryStartTime, $sQueryEndTime );
        $aUser = $iIsGetData ==0 ? array() : $oUser->getChildrenListID( $iUserId ,FALSE,$sUserWhere,FALSE);
        $aUsers = array();
        foreach( $aUser as $user )
        {
            $aUsers[$user["userid"]]["username"] = $user["username"]; 
            $aUsers[$user["userid"]]["usertype"] = $user["usertype"];
            $aUsers[$user["userid"]]["sum_bonus"] = 0.00;
            $aUsers[$user["userid"]]["sum_point"] = 0.00;
            $aUsers[$user["userid"]]["sum_prize"] = 0.00;
            $aUsers[$user["userid"]]["sum_total"] = 0.00;
            $aUsers[$user["userid"]]["real_prize"] = 0.00;
            if( isset($aResult[$user["userid"]]) )
            {
                $aUsers[$user["userid"]]["sum_bonus"] = $aResult[$user["userid"]]["bonus"];
                $aUsers[$user["userid"]]["sum_point"] = $aResult[$user["userid"]]["point"];
                $aUsers[$user["userid"]]["sum_prize"] = $aResult[$user["userid"]]["prize"];
                $aUsers[$user["userid"]]["sum_total"] = $aResult[$user["userid"]]["prize"]- $aResult[$user["userid"]]["point"] - $aResult[$user["userid"]]["bonus"];
                $aUsers[$user["userid"]]["real_prize"] = $aResult[$user["userid"]]["prize"]- $aResult[$user["userid"]]["point"];
            }
        }
        $aTotal["sum_bonus"] = 0;
        $aTotal["sum_point"] = 0;
        $aTotal["sum_prize"] = 0;
        $aTotal["sum_total"] = 0;
        $aTotal["real_prize"] = 0;
        if(count($aUsers)>0)
        {
            foreach( $aUsers as $k )
            {
                $aTotal["sum_bonus"] += $k["sum_bonus"];
                $aTotal["sum_point"] += $k["sum_point"];
                $aTotal["sum_prize"] += $k["sum_prize"];
                $aTotal["sum_total"] += $k["sum_total"];
                $aTotal["real_prize"] += $k["real_prize"];
            }
        }
        if(isset($Self))
        { //查询自身的加上
            $aTotal["sum_bonus"] += $Self["sum_bonus"];
            $aTotal["sum_point"] += $Self["sum_point"];
            $aTotal["sum_prize"] += $Self["sum_prize"];
            $aTotal["sum_total"] += $Self["sum_total"];
            $aTotal["real_prize"] += $Self["real_prize"];
        }
        $sHtml["isfrozen"] = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
        $sHtml["istester"] = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
        $GLOBALS['oView']->assign( "lottery",       $aLottery );
        $GLOBALS['oView']->assign( "data_method",   json_encode($aMethods) );
        $GLOBALS['oView']->assign( "aUser",         $aUsers );
        $GLOBALS['oView']->assign( "total",         $aTotal );
        $GLOBALS['oView']->assign( 's',             $sHtml );
        $GLOBALS['oView']->assign( "ur_here",       "盈亏报表" );
        $GLOBALS['oView']->assign( 'modelist', $GLOBALS['config']['modes']);
        $GLOBALS['oView']->assign( "actionlink", array("text"=>'盈亏报表',"href"=>url('report','salelist')));
        $oMethod->assignSysInfo();
        $GLOBALS['oView']->display("report_salelist.html");
        EXIT;
    }



    /**
     * 游戏明细
     * URL:./index.php?controller=report&action=saledetail
     * @author MARK
     */
    function actionSaledetail()
    {
        $aLocation[0] = array( "text"=>'关闭', "url"=>'javascript:close();' );
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
            $sHtml["starttime"] = $startTime;
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
            $sHtml["endtime"] = $endTime;
        }
        $iModes  = isset($_GET['modes']) ? intval($_GET['modes']) : -1;//元角模式 
        if( $iModes != -1 )
        {
            $sWhere .=" AND P.`modes`='".$iModes."'";
        }
        $sHtml['modes'] = $iModes;
        $iUserId = isset($_GET["userid"])&&is_numeric($_GET["userid"]) ? intval($_GET["userid"]) :0;
        $sHtml["userid"] = $iUserId;
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method", $GLOBALS['aSysDbServer']['report']);
        $aMethod = $oMethod->methodGetList("a.`methodid`,a.`lotteryid`,a.`methodname`,b.`cnname`",'a.`pid`>0','',0);
        $aLottery =array();
        foreach($aMethod as $method)
        {
            $aLottery[$method["lotteryid"]]["cnname"] = $method["cnname"];
            $aLottery[$method["lotteryid"]]["methodid"][] = $method["methodid"];
            $aLottery[$method["lotteryid"]]["method"][$method["methodid"]] = $method["methodname"];
            $aLottery[$method["lotteryid"]]["sum_bonus"][$method["methodid"]] = 0.00;
            $aLottery[$method["lotteryid"]]["sum_point"][$method["methodid"]] = 0.00;
            $aLottery[$method["lotteryid"]]["sum_prize"][$method["methodid"]] = 0.00;
            $aLottery[$method["lotteryid"]]["real_prize"][$method["methodid"]] = 0.00;
            $aLottery[$method["lotteryid"]]["total"][$method["methodid"]] = 0.00;
        }
        foreach ($aLottery as $i=>$v)
        {
            $aLottery[$i]["count"] = count($v["methodid"]);
        }
        /* @var $oOrders model_orders */
        $oOrders = A::singleton("model_orders", $GLOBALS['aSysDbServer']['report']);
        $aResult = $oOrders->getAdminTotalUserBonusByMethod($iUserId, $sWhere, FALSE, $startTime, $endTime);
        if( empty($aResult) )
        {
            sysMsg('没有权限', 1, $aLocation);
        }
        foreach( $aResult[1] as $i=>$v )
        { //用户的返点值
            if(isset($aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]]))
            {
                $aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]] += $v["summoney"];//统计全部模式 
            }
            else
            {
                $aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]] = $v["summoney"];
            }
        }
        foreach( $aResult[4] as $i=>$v )
        { //用户的返点值,在前一天购买的单子，在当天进行撤单的返点值[减去]
            if(isset($aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]]))
            {
                $aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]] -= $v["summoney"];//统计全部模式 
            }
            else
            {
                $aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]] = $v["summoney"];
            }
        }
        foreach( $aResult[5] as $i=>$v )
        { //用户的返点值,在指定时间内购买的单子，在指定时间之后进行撤单的返点值[加上]
            if(isset($aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]]))
            {
                $aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]] += $v["summoney"];//统计全部模式 
            }
            else
            {
                $aLottery[$v["lotteryid"]]["sum_point"][$v["methodid"]] = -$v["summoney"];
            }
        }
        foreach( $aResult[2] as $i=>$v )
        { //奖金
            if(isset($aLottery[$v["lotteryid"]]["sum_bonus"][$v["methodid"]]))
            {
                $aLottery[$v["lotteryid"]]["sum_bonus"][$v["methodid"]] += $v["sumbonus"];//统计全部模式
            }
            else
            {
                $aLottery[$v["lotteryid"]]["sum_bonus"][$v["methodid"]] = $v["sumbonus"];
            }
        }
        foreach( $aResult[3] as $i=>$v )
        { //购彩总金额
            if(isset($aLottery[$v["lotteryid"]]["sum_prize"][$v["methodid"]]))
            {
                $aLottery[$v["lotteryid"]]["sum_prize"][$v["methodid"]] += $v["sumprice"];//统计全部模式
            }
            else
            {
                $aLottery[$v["lotteryid"]]["sum_prize"][$v["methodid"]] = $v["sumprice"];
            }
        }
        foreach( $aLottery as $i=>$v )
        {
            foreach($v["methodid"] as $v1)
            { //计算实际购彩总金额，以及总结算
                $aLottery[$i]["real_prize"][$v1] = $aLottery[$i]["sum_prize"][$v1] - $aLottery[$i]["sum_point"][$v1];
                $aLottery[$i]["total"][$v1] = $aLottery[$i]["real_prize"][$v1] - $aLottery[$i]["sum_bonus"][$v1];
            }
        }
        $aTotal = array();
        foreach( $aLottery as $i=>$v )
        {
            $aTotal["sum_bonus"][$i] = array_sum($v["sum_bonus"]);
            $aTotal["sum_prize"][$i] = array_sum($v["sum_prize"]);
            $aTotal["real_prize"][$i] = array_sum($v["real_prize"]);
            $aTotal["total"][$i] = array_sum($v["total"]);
        }
        $GLOBALS['oView']->assign( "s",         $sHtml );
        $GLOBALS['oView']->assign( "lottery",   $aLottery  );
        $GLOBALS['oView']->assign( "total",     $aTotal );
        $GLOBALS['oView']->assign( "ur_here",   "游戏明细" );
        $GLOBALS['oView']->assign( 'modelist', $GLOBALS['config']['modes']);
        $oOrders->assignSysInfo();
        $GLOBALS['oView']->display("report_saledetail.html");
        EXIT;
    }



    /**
     * 频道转账表
     * @author MARK
     * URL: ./index.php?controller=report&action=transferlist
     */
    function actionTransferlist()
    {
    // 01, 整理搜索条件
        $aSearch['pn']       = isset($_GET['pn']) ? intval($_GET['pn']) : 100;
        $aSearch['sdate']    = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 02:20'); // 默认当天
        $aSearch['edate']    = isset($_GET['edate']) ? trim($_GET['edate']) : date('Y-m-d 02:20',strtotime('+1 day'));
        $aSearch['sdate']    = getFilterDate( $aSearch['sdate'], 'Y-m-d H:i' );
        $aSearch['edate']    = getFilterDate( $aSearch['edate'], 'Y-m-d H:i' );
        $aSearch['itype']    = isset($_GET['itype']) ? intval($_GET['itype']) : 1; // 转账状态 1=全部,2=成功,3=失败
        $aSearch['istester'] = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
        $aSearch['isfrozen'] = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
        $aHtmlValue             = array();
        $aHtmlValue['sdate']    = $aSearch['sdate'];
        $aHtmlValue['edate']    = $aSearch['edate'];
        $aHtmlValue['itype']    = $aSearch['itype'];
        $aHtmlValue['istester'] = $aSearch['istester'];
        $aHtmlValue['isfrozen'] = $aSearch['isfrozen'];
        // 销售管理员的判断
        $sWhere = '';
        $sTranStatusSql = '';
        // 状态的判断, 2==成功, 其他=失败
        if( $aSearch['itype'] == 2 )
        {
            $sTranStatusSql = ' AND `transferstatus`=2 '; 
        }
        if( $aSearch['itype'] == 3 )
        {
            $sTranStatusSql = ' AND `transferstatus`!=2 '; 
        }
        if( $aSearch['istester'] != -1 )
        {//测试账户
            $sWhere .= " AND `istester` = " . $aSearch['istester'];
        }
        if( $aSearch['isfrozen'] != -1 )
        {//冻结账户
            $aSearch['isfrozen'] == 0 ? $sWhere .= " AND `isfrozen` = " . $aSearch['isfrozen'] : 
	                                    $sWhere .= " AND `isfrozen` >= " . $aSearch['isfrozen'];
        }
        // 获取总代频道理赔结果集
        /* @var $oOrder model_orders */
        $oOrder = A::singleton("model_orders", $GLOBALS['aSysDbServer']['report']);
        $iIsGetData  = isset($_GET['isgetdata']) ? intval($_GET['isgetdata']) : 0;//是否查询数据
        $sWhere .= " ORDER BY `username`";
        $aResult= $iIsGetData == 0 ? array() : $oOrder->getTopProxyTransition( $aSearch['sdate'], $aSearch['edate'], $sWhere, $sTranStatusSql );
        $iRecordCount = count($aResult);
        $aChannelTitle = array();
        for( $i=0; $i<1; $i++ )
        {            
            $aChannelTitle[] = '银行 > 高频';
            $aChannelTitle[] = '高频 > 银行';
        } 
        $aChannelTotal = array(); // 初始化数组, 所有频道*2 + 1 ( 转账结余 )
        for( $i=0; $i<(1*2+1); $i++ )
        {
            $aChannelTotal[$i] = 0;
        }
        foreach( $aResult AS $v )
        {
            // 计算每个列的总计金额
            $j = 0;
            foreach( $v['channel'] AS $aChannelValue )
            {
                $aChannelTotal[ $j++ ] += $aChannelValue;
            }
            $aChannelTotal[$j] += $v['total'];
        }
        $aHtmlValue['iCounts']   = $iRecordCount;
        $GLOBALS['oView']->assign( 's', $aHtmlValue );
        $GLOBALS['oView']->assign( 'aUserList', $aResult ); // 数据分配, 结果集
        $GLOBALS['oView']->assign( 'aChannelTitle', $aChannelTitle ); // 数据分配, 列标题
        $GLOBALS['oView']->assign( 'aChannelTotal', $aChannelTotal ); // 数据分配, 总计
        $GLOBALS['oView']->assign( 'ur_here', '频道转账表' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","transferlist"), 'text'=>'清空过滤条件' ) );
        $oOrder->assignSysInfo();
        $GLOBALS['oView']->display("report_transferlist.html");
        EXIT;
    }



    /**
     * 游戏币明细表(团队)
     * @author MARK
     * URL:./index.php?controller=report&action=fundlist
     */
    function actionFundlist()
    {
        // 01, 整理搜索条件
        $aSearch['pn']         = isset($_GET['pn']) ? intval($_GET['pn']) : 100;
        $aSearch['username']   = isset($_GET['username']) ? daddslashes(trim($_GET['username'])) : "";
        $aSearch['amount1']    = isset($_GET['amount1']) ? intval($_GET['amount1']) : "";
        $aSearch['amount2']    = isset($_GET['amount2']) ? intval($_GET['amount2']) : "";
        $aSearch['istester']   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
	    $aSearch['isfrozen']   = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
        $aHtmlValue = array();

        // 02, WHERE & Having 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        $sHaving= ' 1 '; // HAVING 条件变量声明
        /* @var $oOrder model_userfund */
        $oOrder = A::singleton("model_userfund", $GLOBALS['aSysDbServer']['report']);
        // 0002, 索引 amount
        if( $aSearch['amount1'] != '' && is_numeric($aSearch['amount1']) )
        { // 单用户(不含下级) 账户余额1
            $sHaving .= " AND TeamChannelBalance >= '".intval($aSearch['amount1'])."' ";
            $aHtmlValue['amount1'] = $aSearch['amount1'];
        }

        if( $aSearch['amount2'] != '' && is_numeric($aSearch['amount2']) )
        { // 单用户(不含下级) 账户余额2
            $sHaving .= " AND TeamChannelBalance <= '".intval($aSearch['amount2'])."' ";
            $aHtmlValue['amount2'] = $aSearch['amount2'];
        }
        // 0003, 用户ID 部分
        // 用户ID部分
        $iUserId = 0;
        if( $aSearch['username'] != '' )
        { // 获取用户ID
            /* @var $oUser model_user */
            $oUser = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
            $iUserId = $oUser->getUseridByUsername( $aSearch['username'] );
            if( $iUserId == 0 || is_array($iUserId) )
            { // 搜索的用户名未找到, 并且不允许通配符搜索
                $sWhere .= " AND 0 ";
                $iUserId = 0;
            }
            $aHtmlValue['username'] = stripslashes_deep($aSearch['username']);
        }
        if( $aSearch['istester'] != -1 )
        {//测试账户
            $sWhere .= " AND ut.`istester` = " . $aSearch['istester'];
            $aHtmlValue['istester'] = $aSearch['istester'];
        }

        if( $aSearch['isfrozen'] != -1 )
        {//冻结账户
            $bIsFrozen = $aSearch['isfrozen']; 
            /* @var $oUser model_user */
            $oUser = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
            $aHtmlValue['isfrozen'] = $aSearch['isfrozen'];
            if( $bIsFrozen > 0 )//显示冻结总代,除去非冻结总代
            {
                $aTopProxyList = $oUser->getChildListID( 0, " AND a.`isfrozen` > 0" );
            }
            elseif ( $bIsFrozen == 0 )//如果为非冻结总代,除去冻结总代
            {
                $aTopProxyList = $oUser->getChildListID( 0, " AND a.`isfrozen` = 0" );
            }
            $aTopProxyIdList = array();
            foreach ( $aTopProxyList as $aTopProxy )
            {
                $aTopProxyIdList[] = $aTopProxy['userid'];
            }
            $sTopProxyId = implode( ',', $aTopProxyIdList );
            if( $sTopProxyId == '' )
            {
                $sTopProxyId = '0';
            }
            $sWhere .= " AND ut.`lvtopid` in( " . $sTopProxyId . ")";//查询在指定列表中总代数据
            unset($bIsFrozen);
        }
        $aHtmlValue['now']= date("Y-m-d H:i:s"); 
        // 获取数据结果集
        $aResult = $oOrder->getProxyTeamFundList( $iUserId, $sWhere, ($sHaving==' 1 '?'':$sHaving) );
        $aHtmlValue['iCountAll']      = 0; // 总计 账户余额  C
        $aHtmlValue['iCountHold']     = 0; // 总计 冻结金额  D
        $aHtmlValue['iCountAvail']    = 0; // 总计 可用余额  E
        $aHtmlValue['iCountError1']   = 0;
        $aHtmlValue['iCountError2']   = 0;
        $aHtmlValue['iCounts']        = count($aResult);
        if( $aHtmlValue['iCounts'] > 0 )
        { // 进行数据整理,对当页数据进行小结
            foreach( $aResult as &$v )
            {
                $aHtmlValue['iCountAll']    += $v['TeamChannelBalance'];  // C
                $aHtmlValue['iCountHold']   += $v['TeamHoldBalance'];     // D
                $aHtmlValue['iCountAvail']  += $v['TeamAvailBalance'];    // E
                //  B = D + E 
                $v['errBalance1'] = round($v['TeamHoldBalance']+$v['TeamAvailBalance'] - $v["TeamChannelBalance"],4);
                $aHtmlValue['iCountError1']  += $v['errBalance1'];
            }
        }
        $GLOBALS['oView']->assign( 's', $aHtmlValue );
        $GLOBALS['oView']->assign( 'aUserList', $aResult ); // 数据分配
        $GLOBALS['oView']->assign( 'ur_here', '游戏币明细 - 团队' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","fundlist"), 'text'=>'清空过滤条件' ) );
        $oOrder->assignSysInfo();
        $GLOBALS['oView']->display("report_fundlist.html");
        EXIT;
    }



    /**
     * 查看高频快照报表
     *   功能需求: 读取 snapshot 表的静态数据. 显示给用户快照信息
     *   gamelow.snapshot 表存放在一个休市,禁止转账的时间点上, 全部总代团队的信息情况
     *      - banksnapshot.tc   账户余额 ( C )
     *      - banksnapshot.td   冻结金额 ( D )
     *      - banksnapshot.te   可用余额 ( E )
     * URL = ./index.php?controller=report&action=snapshot
     * @author Tom
     * HTML 可选搜索条件:
     *   - 01, 报表日期             datas
     *   - 02, 是否包含测试账户     includetestuser        ( 预留 )
     *   - 03, 仅查看冻结用户       includelockuser        ( 预留 )
     */
    function actionSnapshot()
    {
        // 01, 整理搜索条件
        $aSearch['pn']         = isset($_GET['pn']) ? intval($_GET['pn']) : 100;
        $aSearch['dates']      = isset($_GET['dates']) ? $_GET['dates'] : -1;
        $aSearch['istester']   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
	    $aSearch['isfrozen']   = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
        $aHtmlValue = array();
        $aResult = array();
        /* @var $oBankSnapshot model_snapshot */
        $oBankSnapshot = A::singleton("model_snapshot", $GLOBALS['aSysDbServer']['report']);
        $aHtmlValue['dayopts'] = $oBankSnapshot->getDistintDays( FALSE, $aSearch['dates'] );
        // 02, WHERE 语句拼接 
        $aResult['data'] = array();  // 数据结果集
        $aResult['time'] = '';       // 更新时间

        if( $aSearch['dates'] != -1 )
        {
            $sWhere = "";
            if( $aSearch['istester'] != -1 )
            {//测试账户
                $sWhere .= " AND `istestuser` = " . $aSearch['istester'];
                
            }
            
            if( $aSearch['isfrozen'] != -1 )
            {//冻结账户
                $sWhere .= " AND `islockuser` = ". $aSearch['isfrozen'];
            }
            $sWhere .= " ORDER BY `username`";
            $aHtmlValue['dates'] = $aSearch['dates'];
            $aHtmlValue['yesterday'] = date('Y-m-d', strtotime($aSearch['dates']) );
            $aResult = $oBankSnapshot->getSnapshotDatas( $aSearch['dates'], $sWhere );
        }
        $aHtmlValue['istester']              = $aSearch['istester'];
        $aHtmlValue['isfrozen']              = $aSearch['isfrozen'];
        $aHtmlValue['total']['totalbuy']     = 0;
        $aHtmlValue['total']['totalpoint']   = 0;
        $aHtmlValue['total']['totalbingo']   = 0;
        $aHtmlValue['total']['totalbalance'] = 0;
        $aHtmlValue['total']['tranferin']    = 0;
        $aHtmlValue['total']['tranferout']   = 0;
        $aHtmlValue['total']['cashdiff']     = 0;
        $aHtmlValue['total']['todaycash']    = 0;
        $aHtmlValue['total']['tc']           = 0;
        $aHtmlValue['total']['td']           = 0;
        $aHtmlValue['total']['te']           = 0; 
        foreach( $aResult['data'] AS $v )
        {
            $aHtmlValue['total']['totalbuy']     += $v['totalbuy'];
            $aHtmlValue['total']['totalpoint']   += $v['totalpoint'];
            $aHtmlValue['total']['totalbingo']   += $v['totalbingo'];
            $aHtmlValue['total']['totalbalance'] += $v['totalbalance'];
            $aHtmlValue['total']['tranferin']    += $v['tranferin'];
            $aHtmlValue['total']['tranferout']   += $v['tranferout'];
            $aHtmlValue['total']['cashdiff']     += $v['cashdiff'];
            $aHtmlValue['total']['todaycash']    += $v['todaycash'];
            $aHtmlValue['total']['tc']           += $v['tc'];
            $aHtmlValue['total']['td']           += $v['td'];
            $aHtmlValue['total']['te']           += $v['te']; 
        }
        $aHtmlValue['sTimes']  = $aResult['time'];
        $aHtmlValue['iCounts'] = count($aResult['data']);
        /* @var $oBankSnapshot model_banksnapshot */
	    $oBankSnapshot = A::singleton('model_orders', $GLOBALS['aSysDbServer']['report']);
	    $aHtmlValue['tranferoutordertypeid'] = ORDER_TYPE_PDZC.",".ORDER_TYPE_PDXEZC;
	    $aHtmlValue['tranferinordertypeid']  = ORDER_TYPE_ZRPD;
	    $aHtmlValue['sdate'] = date("Y-m-d H:i:s",strtotime($aHtmlValue['sTimes'])-60*60*24);
        $GLOBALS['oView']->assign( 's', $aHtmlValue );
        $GLOBALS['oView']->assign( 'aDataList', $aResult['data'] ); // 数据分配
        $GLOBALS['oView']->assign( 'ur_here', '高频快照报表' );
        $oBankSnapshot->assignSysInfo();
        $GLOBALS['oView']->display("report_snapshot.html");
        EXIT;
    }



    /**
     * 查询单期盈亏报表
     * 派奖时生成每相应彩种相应奖期盈亏数据
     * 可以根据彩种，时间进行查询，可以选择是事包含测试账户
     * 可以分元角模式进行查询
     * URL:./index.php?controller=report&action=singlesale
     *	@author Mark
     */
    function actionSinglesale()
    {
        /* @var $oSale model_sale */
        $oSale = A::singleton("model_sale", $GLOBALS['aSysDbServer']['report']);
        $aSearch['includetestuser'] = isset($_GET['includetestuser']) ? intval($_GET['includetestuser']) : 0;
        $aSearch['modes']           = isset($_GET['modes']) ? intval($_GET['modes']) : -1;
        $aSearch['lotteryid']       = isset($_GET['lottery']) ? intval($_GET['lottery']) : 0;
        $aSearch['starttime']       = isset($_GET['starttime']) ? $_GET['starttime'] : date("Y-m-d 02:20:00",time());
        $aSearch['endtime']         = isset($_GET['endtime']) ? $_GET['endtime'] : date("Y-m-d 02:20:00",time()+86400);
        $pn                         = isset($_GET['pn'])? intval($_GET['pn']) : 500;// 分页用1
        $p                          = isset($_GET['p'])  ? intval($_GET['p'])  : 0;// 分页用2
        $iOrder                     = isset($_GET['order'])  ? intval($_GET['order'])  : 0;// 排序
        $aSearch['order']           = $iOrder;
        $sCondition = ' 1 ';
        if( $aSearch['lotteryid'] )//根据彩种查询报表
        {
            $sCondition .= " AND sl.`lotteryid` = '" . $aSearch['lotteryid'] . "'";
        }
        if( isset($aSearch['modes']) && $aSearch['modes'] !=-1 )//根据元角模式查询报表
        {
            $sCondition .= " AND sl.`modes` = '" . $aSearch['modes'] . "'";
        }
        if( $aSearch['starttime'] )//统计的开始时间
        {
            $sCondition .= " AND sl.`jointime` >= '" . $aSearch['starttime'] . "'";
        }
        if( $aSearch['endtime'] )//统计的结束时间
        {
            $sCondition .= " AND sl.`jointime` <= '" . $aSearch['endtime'] . "'";
        }
        if( isset($aSearch['modes']) && $aSearch['modes'] == -1 )//根据元角模式查询报表
        {
            $sFiled = " sl.`joindate`,sl.`issue`,sl.`lock`,SUM(sl.`nolock_sell`) AS `nolock_sell`,
                        SUM(sl.`nolock_bonus`) AS `nolock_bonus`,SUM(sl.`nolock_return`) AS `nolock_return`,
                        SUM(sl.`testnolock_sell`) AS `testnolock_sell`,SUM(sl.`testnolock_bonus`) AS `testnolock_bonus`,
                        SUM(sl.`testnolock_return`) AS `testnolock_return`,
                        SUM(sl.`sell`) AS `sell`,SUM(sl.`return`) AS `return`,SUM(sl.`bonus`) AS `bonus`,
                        SUM(sl.`test_sell`) AS `test_sell`,SUM(sl.`test_return`) AS `test_return`,SUM(sl.`test_bonus`) AS `test_bonus`,
                        (`sell`-`bonus`-`return`) AS saleresult,isfo.`saleend`,
                        isfo.`writetime`,isfo.`code`,l.`cnname` ";
        }
        else 
        {
            $sFiled = '';
        }
        switch ($iOrder)
        {
            case 0:
                $sOrderBy = ' ORDER BY `jointime` ';
                break;
            case 1:
                $sOrderBy = ' ORDER BY `saleresult` DESC';
                break;
            default:
                $sOrderBy = '';
                break;
        }
        $iIsGetData  = isset($_GET['isgetdata']) ? intval($_GET['isgetdata']) : 0;//是否查询数据
        $aSaleList = $iIsGetData ==0 ? array('affects' => 0, 'results' => array()) : $oSale->getSingleSale( $sFiled, $sCondition, $sOrderBy, $pn, $p, $aSearch['modes'] );
        $aSale = array();
        //定义合计值
        $fTotalPrice  = 0;
        $fTotalBonus  = 0;
        $fTotalPoint  = 0;
        $fTotalResult = 0;
        $fTotalLock   = 0;
        $fTotalNoLockWin   = 0;
        $fTotalDiff   = 0;
        foreach ( $aSaleList['results'] as $aValue )
        {
            if( $aSearch['includetestuser'] )//包含测试账户
            {
                $aValue['sell']   += $aValue['test_sell'];
                $aValue['bonus']  += $aValue['test_bonus'];
                $aValue['return'] += $aValue['test_return'];
                $aValue['nolock_sell'] += $aValue['testnolock_sell'];
                $aValue['nolock_bonus'] += $aValue['testnolock_bonus'];
                $aValue['nolock_return'] += $aValue['testnolock_return'];
            }
            $aValue['lock'] = ($aValue['sell']-$aValue['nolock_sell'])-$aValue['lock'] - ($aValue['return']-$aValue['nolock_return']);
            $aValue['saleresult'] = $aValue['sell']-$aValue['bonus'] - $aValue['return'];
            $aValue['nolock_saleresult'] = $aValue['nolock_sell']-$aValue['nolock_bonus'] - $aValue['nolock_return'];
            $aValue['diff'] =  abs($aValue['saleresult']-$aValue['nolock_saleresult']-$aValue['lock']);
            $aSale[] = $aValue;
            //合计值
            $fTotalPrice  += $aValue['sell'];
            $fTotalBonus  += $aValue['bonus'];
            $fTotalPoint  += $aValue['return'];
            $fTotalResult += $aValue['saleresult'];
            $fTotalLock   += $aValue['lock'];
            $fTotalNoLockWin += $aValue['nolock_saleresult'];
            $fTotalDiff   += $aValue['diff'];
        }
        $oPager = new pages( $aSaleList['affects'], $pn, 10 );   // 分页用3
        //获取彩种列表
        /* @var $oLottery model_lottery */
        $oLottery = A::singleton("model_lottery", $GLOBALS['aSysDbServer']['report']);
        $aLotteryList = $oLottery->lotteryGetList( '', '', '', 0 );
        $aLottery = array();
        foreach ($aLotteryList as $aValue)
        {
            $aLottery[$aValue['lotteryid']] = $aValue['cnname'];
        }
        $GLOBALS['oView']->assign( 'ur_here',     '查询单期盈亏报表');
        $GLOBALS['oView']->assign( 'lottery',         $aLottery);
        $GLOBALS['oView']->assign( 'starttime',       $aSearch['starttime']);
        $GLOBALS['oView']->assign( 'endtime',         $aSearch['endtime']);
        $GLOBALS['oView']->assign( 'lotteryid',       $aSearch['lotteryid']);
        $GLOBALS['oView']->assign( 'includetestuser', $aSearch['includetestuser']);
        $GLOBALS['oView']->assign( 'modes',           $aSearch['modes']);
        $GLOBALS['oView']->assign( 'order',           $aSearch['order']);
        $GLOBALS['oView']->assign( 'asale',       $aSale);
        $GLOBALS['oView']->assign( 'totalprice',  $fTotalPrice);
        $GLOBALS['oView']->assign( 'totalbonus',  $fTotalBonus );
        $GLOBALS['oView']->assign( 'totalpoint',  $fTotalPoint );
        $GLOBALS['oView']->assign( 'totalresult', $fTotalResult );
        $GLOBALS['oView']->assign( 'totallock',   $fTotalLock );
        $GLOBALS['oView']->assign( 'totalnolockwin',   $fTotalNoLockWin );
        $GLOBALS['oView']->assign( 'totaldiff',   $fTotalDiff );
        $GLOBALS['oView']->assign( 'pageinfo', $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'modelist', $GLOBALS['config']['modes']);
        $oLottery->assignSysInfo();
        $GLOBALS['oView']->display("report_singlesale.html");
        EXIT;
    }



    /**
     * 代理最近投注量查询
     * 每天销售结束时生成代理投注量数据
     * 根据代理名称和指定天数查询代理最近的投注量数据
     * URL: ./index.php?controller=report&action=recentbuy
     * @author Mark
     */
    function actionRecentbuy()
    {
        $sProxyName = isset($_GET['username']) ? daddslashes($_GET['username']) : '';//代理名称
        $iModes     = isset($_GET['modes']) && is_numeric($_GET['modes']) ? intval($_GET['modes']) : -1;//元角模式
        $iDayCount  = isset($_GET['daynum']) && is_numeric($_GET['daynum']) ? intval($_GET['daynum']) : 0;//指定查询天数
        /* @var $oSale model_sale */
        $oSale = A::singleton( "model_sale", $GLOBALS['aSysDbServer']['report'] );
        /* @var $oUser model_user */
        $oUser = A::singleton( "model_user", $GLOBALS['aSysDbServer']['report'] );
        $iPorxyId  = $oUser->getUseridByUsername( $sProxyName );//获取用户ID
        $aSaleList = $oSale->getRecentBuy( $iPorxyId, $iDayCount );//获取代理最近投注数据
        foreach ( $aSaleList as & $aSale )
        {
            $aSale['amount'] = unserialize($aSale['amount']);//反序列化投注量数据
            $fSaleAmont = 0;
            if( $iModes == -1 )//全部模式
            {
                foreach ($aSale['amount'] as $fAmount)
                {
                    $fSaleAmont += $fAmount; 
                }
            }
            else//指定元角模式
            {
                if( isset($aSale['amount'][$iModes]) )
                {
                    $fSaleAmont = $aSale['amount'][$iModes];
                }
            }
            $aSale['amount'] = $fSaleAmont;
        }
        $GLOBALS['oView']->assign( "arecentbuy",   $aSaleList );
        $GLOBALS['oView']->assign( "ur_here",      "代理最近投注" );
        $GLOBALS['oView']->assign( "username",     $sProxyName );
        $GLOBALS['oView']->assign( "daynum",       $iDayCount );
        $GLOBALS['oView']->assign( "modes",        $iModes );
        $GLOBALS['oView']->assign( 'modelist', $GLOBALS['config']['modes']);
        $oSale->assignSysInfo();
        $GLOBALS['oView']->display("report_recentbuy.html");
        EXIT;
    }
    
    
    
     /**
     * 游戏币明细(自身)
     * 
     * URL: ./index.php?controller=report&action=selffundlist
     * @author Mark
     */
    function actionSelfFundList()
    {
        // 01, 整理搜索条件
        $aSearch['pn']         = isset($_GET['pn']) ? intval($_GET['pn']) : 100;
        $aSearch['username']   = isset($_GET['username']) ? daddslashes(trim($_GET['username'])) : "";
        $aSearch['amount1']    = isset($_GET['amount1']) ? intval($_GET['amount1']) : "";
        $aSearch['amount2']    = isset($_GET['amount2']) ? intval($_GET['amount2']) : "";
        $aSearch['istester']   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
        $aSearch['isfrozen']   = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
        $aHtmlValue = array();

        // 02, WHERE & Having 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        /* @var $oUserFund model_userfund */
        $oUserFund = A::singleton("model_userfund", $GLOBALS['aSysDbServer']['report']);
        // 0002, 索引 amount
        if( $aSearch['amount1'] != '' && is_numeric($aSearch['amount1']) )
        { // 单用户(不含下级) 账户余额1
            $sWhere .= " AND uf.availablebalance >= '".intval($aSearch['amount1'])."' ";
            $aHtmlValue['amount1'] = $aSearch['amount1'];
        }

        if( $aSearch['amount2'] != '' && is_numeric($aSearch['amount2']) )
        { // 单用户(不含下级) 账户余额2
            $sWhere .= " AND uf.availablebalance <= '".intval($aSearch['amount2'])."' ";
            $aHtmlValue['amount2'] = $aSearch['amount2'];
        }
        // 0003, 用户ID 部分
        // 用户ID部分
        $iUserId = 0;
        if( $aSearch['username'] != '' )
        { // 获取用户ID
            /* @var $oUser model_user */
            $oUser = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
            $iUserId = $oUser->getUseridByUsername( $aSearch['username'] );
            if( $iUserId == 0 || is_array($iUserId) )
            { // 搜索的用户名未找到, 并且不允许通配符搜索
                $sWhere .= " AND 0 ";
                $iUserId = 0;
            }
            $aHtmlValue['username'] = stripslashes_deep($aSearch['username']);
        }
        if( $aSearch['istester'] != -1 )
        {//测试账户
            $sWhere .= " AND ut.`istester` = " . $aSearch['istester'];
            $aHtmlValue['istester'] = $aSearch['istester'];
        }

        if( $aSearch['isfrozen'] != -1 )
        {//冻结账户
            $bIsFrozen = $aSearch['isfrozen'];
            /* @var $oUser model_user */
            $oUser = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
            $aHtmlValue['isfrozen'] = $aSearch['isfrozen'];
            if( $bIsFrozen > 0 )//显示冻结总代,除去非冻结总代
            {
                $aTopProxyList = $oUser->getChildListID( 0, " AND a.`isfrozen` > 0" );
            }
            elseif ( $bIsFrozen == 0 )//如果为非冻结总代,除去冻结总代
            {
                $aTopProxyList = $oUser->getChildListID( 0, " AND a.`isfrozen` = 0" );
            }
            $aTopProxyIdList = array();
            foreach ( $aTopProxyList as $aTopProxy )
            {
                $aTopProxyIdList[] = $aTopProxy['userid'];
            }
            $sTopProxyId = implode( ',', $aTopProxyIdList );
            if( $sTopProxyId == '' )
            {
                $sTopProxyId = '0';
            }
            $sWhere .= " AND ut.`lvtopid` in( " . $sTopProxyId . ")";//查询在指定列表中总代数据
            unset($bIsFrozen);
        }
        $aHtmlValue['now']= date("Y-m-d H:i:s");
        // 获取数据结果集
        $aResult = $oUserFund->getProxyFundList( $iUserId, $sWhere );
        $aHtmlValue['iCountAll']      = 0; // 总计 账户余额  C
        $aHtmlValue['iCountHold']     = 0; // 总计 冻结金额  D
        $aHtmlValue['iCountAvail']    = 0; // 总计 可用余额  E
        $aHtmlValue['iCountError1']   = 0;
        $aHtmlValue['iCountError2']   = 0;
        $aHtmlValue['iCounts']        = count($aResult);
        if( $aHtmlValue['iCounts'] > 0 )
        { // 进行数据整理,对当页数据进行小结
            foreach( $aResult as &$v )
            {
                $aHtmlValue['iCountAll']    += $v['TeamChannelBalance'];  // C
                $aHtmlValue['iCountHold']   += $v['TeamHoldBalance'];     // D
                $aHtmlValue['iCountAvail']  += $v['TeamAvailBalance'];    // E
                //  B = D + E
                $v['errBalance1'] = round($v['TeamHoldBalance']+$v['TeamAvailBalance'] - $v["TeamChannelBalance"],4);
                $aHtmlValue['iCountError1']  += $v['errBalance1'];
            }
        }
        $GLOBALS['oView']->assign( 's', $aHtmlValue );
        $GLOBALS['oView']->assign( 'aUserList', $aResult ); // 数据分配
        $GLOBALS['oView']->assign( 'ur_here', '游戏币明细 - 自身' );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","fundlist"), 'text'=>'清空过滤条件' ) );
        $oUserFund->assignSysInfo();
        $GLOBALS['oView']->display("report_selffundlist.html");
        EXIT;
    }
}
?>