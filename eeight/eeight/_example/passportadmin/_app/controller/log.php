<?php
/**
 * 文件 : /_app/controller/log.php
 * 功能 : 控制器 - 日志管理
 * 
 * @author    Tom    090914
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_log extends basecontroller
{
    /**
     * 管理员日志列表
     * URL = ./controller=log&action=adminlog
     * @author Tom 090511 
     * 
     * HTML 可选条件
     *   - 1, 日志类型        lt        ( Log Type )
     *   - 2, 控制器名        lc        ( Log Controller )
     *   - 3, 行为器名        la        ( 我日,不是 L.A. COFFEE ! )
     *   - 4, 管理员编号      aid       ( admin id )
     *   - 5, 日志开始时间    sdate     ( start date ) 
     *   - 6, 日志截止时间    edate     ( end date )
     *   - 7, IP地址模糊      ipaddr    
     * 
     */
    function actionAdminlog()
    {
        // 01, 搜索条件整理
        $aSearch['lt']     = isset($_GET['type']) ? $_GET['type'] : "";
        $aSearch['lc']     = !empty($_GET['lc']) ? daddslashes($_GET['lc']) : "";
        $aSearch['la']     = !empty($_GET['la']) ? daddslashes($_GET['la']) : "";
        $aSearch['aid']    = isset($_GET['aid']) ? intval($_GET['aid']) : "";
        $aSearch['sdate']  = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 00:00');
        $aSearch['edate']  = isset($_GET['edate']) ? trim($_GET['edate']) : "";
        $aSearch['ipaddr'] = isset($_GET['ipaddr']) ? daddslashes($_GET['ipaddr']) : "";
        
        /* @var $oPassPort model_passport */
        $oPassPort         =  A::singleton('model_passport');
        $aHtml['lt']       =  $aSearch['lt']=='' ? -1 : $aSearch['lt'];
        $aHtml['lc']       =  $oPassPort->getDistintController( FALSE, $aSearch['lc'] );
        $aHtml['la']       =  $oPassPort->getDistintActioner(FALSE, $aSearch['la'] );
        $aHtml['aid']      =  $oPassPort->getDistintAdminName(FALSE, $aSearch['aid'] );
        $aHtml['sdate']    =  getFilterDate($aSearch['sdate'],'Y-m-d H:i');
        $aHtml['edate']    =  getFilterDate($aSearch['edate'],'Y-m-d H:i');
        $aHtml['ipaddr']   =  stripslashes_deep($aSearch['ipaddr']);
        
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aHtml['lt'] != -1 )
        {
            $sWhere .= " AND ( `typeid` = '".$aSearch['lt']."' ) ";
        }
        if( $aSearch['lc'] != '' )
        {
            $sWhere .= " AND ( `controller` = '".$aSearch['lc']."' ) ";
        }
        if( $aSearch['la'] != '' )
        {
            $sWhere .= " AND ( `actioner` = '".$aSearch['la']."' ) ";
        }
        if( $aSearch['aid'] != '' )
        {
            $sWhere .= " AND ( a.`adminid` = '".$aSearch['aid']."' ) ";
        }
        if( $aSearch['sdate'] != '' )
        {
            $sWhere .= " AND ( `times` >= '".daddslashes($aSearch['sdate'])."' ) ";
            $aSearch['sdate']  =  stripslashes_deep($aSearch['sdate']);
        }
        if( $aSearch['edate'] != '' )
        {
            $sWhere .= " AND ( `times` <= '".daddslashes($aSearch['edate'])."' ) ";
            $aSearch['edate']  =  stripslashes_deep($aSearch['edate']);
        }
        if( $aSearch['ipaddr'] != '' )
        {
            if( strstr($aSearch['ipaddr'], '*') ) // 搜索到通配符 * 号 
            {
                $tmpUsername = str_replace( '*', '%', $aSearch['ipaddr'] );
                $sWhere .= " AND ( `clientip` LIKE '".daddslashes($tmpUsername).
            					"' or `proxyip` LIKE '".daddslashes($tmpUsername)."' ) ";
            }
            else 
            {
                $sWhere .= " AND ( `clientip` = '".daddslashes($aSearch['ipaddr']).
            					"' or `proxyip` = '".daddslashes($aSearch['ipaddr'])."' ) ";
            }
            $aSearch['ipaddr']  =  stripslashes_deep($aSearch['ipaddr']);
        }
        
        // 03, 数据查询
        // 分页处理,  HTML 宏解析
        $p         = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pn        = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        $oAdminLog = new model_adminlog();
        $aResult   = $oAdminLog->getAdminLogList('*', $sWhere, $pn , $p);
        $oPager    = new pages( $aResult['affects'], $pn, 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'aLogList', $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign("ur_here", "管理员日志列表");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","adminlog"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign("s", $aHtml );
        $oAdminLog->assignSysInfo();
        $GLOBALS['oView']->display("log_adminlog.html");
        EXIT;
	}



    /**
     * 查看管理员日志详情
     * URL = ./controller=log&action=adminlogview
     * @author Tom 090511
     */
    function actionAdminlogview()
    {
        $sLocationArr = array(0=>array("text" => "管理员日志列表","href" => url("log","adminlog")));
        $id = (isset($_GET["id"])&&is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
        $oAdminLogs = new model_adminlog();
        $aResult = $oAdminLogs->getAdminLogInfo( $id );
        if( $aResult == -1 )
        {
            sysMessage("日志获取失败", 1, $sLocationArr);
        }
        $aResult['request'] = htmlspecialchars( var_export( stripslashes_deep(@unserialize($aResult['requeststring'])), TRUE ));
        $GLOBALS['oView']->assign( 's', $aResult ); // 数据分配
        $GLOBALS['oView']->assign("ur_here", "查看管理员日志详情");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","adminlog"), 'text'=>'管理员日志列表' ) );
        $GLOBALS['oView']->display("log_adminview.html");
        EXIT;
    }


    
    /**
     * 用户日志列表
     * URL = ./controller=log&action=userlog
     * @author Tom 090504
     * 
     * HTML 可选条件
     *   - 1, 日志类型        lt        ( Log Type )
     *   - 2, 用户名          ln        ( Log Username )
     *   - 5, 日志开始时间    sdate     ( start date ) 
     *   - 6, 日志截止时间    edate     ( end date )
     *   - 7, IP地址模糊      ipaddr    
     */
    function actionUserlog()
    {
        // 01, 搜索条件整理
        $aSearch['username']= !empty($_GET['username']) ? daddslashes($_GET['username']) : "";
        $aSearch['ltype']   = !empty($_GET['ltype']) ? daddslashes($_GET['ltype']) : "";
        $aSearch['sdate']   = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 00:00');
        $aSearch['edate']   = isset($_GET['edate']) ? trim($_GET['edate']) : "";
        $aSearch['ipaddr']  = isset($_GET['ipaddr']) ? daddslashes($_GET['ipaddr']) : "";
        $aHtml['username']  =  stripslashes_deep($aSearch['username']);
        $aHtml['ltype']     =  stripslashes_deep($aSearch['ltype']);
        $aHtml['sdate']     =  getFilterDate($aSearch['sdate'],"Y-m-d H:i");
        $aHtml['edate']     =  getFilterDate($aSearch['edate'],"Y-m-d H:i");
        $aHtml['ipaddr']    =  stripslashes_deep($aSearch['ipaddr']);

        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aSearch['username'] != '' )
        { // 用户名支持多个(逗号分隔), 支持模糊搜索
            $sSep = array( " ", "　", ",", "，" ); // 半角,全角逗号, 空格
            $aUserNameArray = explode( ',', trim( str_replace($sSep, ',', $aSearch['username']) ) );
            unset($sSep);
            $oUser = new model_user();
            $aUserIdArr = $oUser->getUseridByUsernameArr( $aUserNameArray );
            if( !empty($aUserIdArr) )
            {
                $sWhere .= " AND a.`userid` IN ( ". join(',',$aUserIdArr) ." ) ";
            }
            else 
            {
                $sWhere .= " AND 0 ";
            }
        }
        if( $aSearch['sdate'] != '' )
        {
            $sWhere .= " AND ( `times` >= '".daddslashes($aSearch['sdate'])."' ) ";
            $aSearch['sdate']  =  stripslashes_deep($aSearch['sdate']);
        }
        if( $aSearch['ltype'] != '' )
        {
            if( !strstr($aSearch['ltype'], '*' ) )
            {
                $sWhere .= " AND `title` = '".$aSearch['ltype']."' ";
            }
            else 
            {
                $sWhere .= " AND `title` LIKE '".str_replace( '*', '%', $aSearch['ltype'])."' ";
            }
            $aSearch['ltype']  =  stripslashes_deep($aSearch['ltype']);
        }
        if( $aSearch['edate'] != '' )
        {
            $sWhere .= " AND ( `times` <= '".daddslashes($aSearch['edate'])."' ) ";
            $aSearch['edate']  =  stripslashes_deep($aSearch['edate']);
        }
        if( $aSearch['ipaddr'] != '' )
        {
            if( strstr($aSearch['ipaddr'], '*') ) // 搜索到通配符 * 号 
            {
                $tmpUsername = str_replace( '*', '%', $aSearch['ipaddr'] );
                $sWhere .= " AND ( `clientip` LIKE '".daddslashes($tmpUsername).
            					"' or `proxyip` LIKE '".daddslashes($tmpUsername)."' ) ";
            }
            else 
            {
                $sWhere .= " AND ( `clientip` = '".daddslashes($aSearch['ipaddr']).
            					"' or `proxyip` = '".daddslashes($aSearch['ipaddr'])."' ) ";
            }
            $aSearch['ipaddr']  =  stripslashes_deep($aSearch['ipaddr']);
        }
        
        // 03, 数据查询
        $p         = isset($_GET['p'])  ? intval($_GET['p'])  : 0;
        $pn        = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        $oAdminLog = new model_adminlog();
        $aResult   = $oAdminLog->getUserLogList('*', $sWhere, $pn , $p);
        $oPager    = new pages( $aResult['affects'], $pn, 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'aLogList', $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign("ur_here","用户日志列表");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","userlog"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign("s", $aHtml );
        $oAdminLog->assignSysInfo();
        $GLOBALS['oView']->display("log_userlog.html");
        EXIT;
    }



    /**
     * 查看用户日志详情
     * URL = ./controller=log&action=userlogview
     * @author Tom 090504
     */
    function actionUserlogview()
    {
        $sLocationArr = array(0=>array("text" => "用户日志列表","href" => url("log","userlog")));
        $id = (isset($_GET["id"])&&is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
        $oAdminLogs = new model_adminlog();
        $aResult = $oAdminLogs->getUserLogInfo( $id );
        if( $aResult == -1 )
        {
            sysMessage("抱歉, 日志获取失败", 1, $sLocationArr);
        }
        $aResult['request'] = htmlspecialchars( var_export( stripslashes_deep(@unserialize($aResult['requeststring'])), TRUE ));
        $GLOBALS['oView']->assign( 's', $aResult ); // 数据分配
        $GLOBALS['oView']->assign("ur_here", "查看用户日志详情");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","userlog"), 'text'=>'用户日志列表' ) );
        $GLOBALS['oView']->display("log_userview.html");
        EXIT;
    }
    
    
    /**
     * 查看余额修正操作日志
     * 4/28/2010
     * JIM
     */
    function actionBalancelogs(){
		$aSearch['username']= !empty($_GET['username']) ? daddslashes($_GET['username']) : "";
    	$aSearch['sdate']   = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 00:00');
        $aSearch['edate']   = isset($_GET['edate']) ? trim($_GET['edate']) : "";
      
        $sWhere = ' 1 ';
    	if( $aSearch['username'] != '' )
        {
            $sWhere .= " AND `username` = '".daddslashes($aSearch['username'])."' ";
        }
        
    	if( $aSearch['payportid'] != '' )
        {
            $sWhere .= " AND `payport_id` = ".daddslashes($aSearch['payportid']);
        }
        
    	if( $aSearch['payportaccid'] != '' )
        {
            $sWhere .= " AND `payport_acc_id` = ".daddslashes($aSearch['payportaccid']);
        }
        
        if( $aSearch['sdate'] != '' )
        {
            $sWhere .= " AND ( `logtime` >= '".daddslashes($aSearch['sdate'])."' ) ";
            $aSearch['sdate']  =  stripslashes_deep($aSearch['sdate']);
        }
        if( $aSearch['edate'] != '' )
        {
            $sWhere .= " AND ( `logtime` <= '".daddslashes($aSearch['edate'])."' ) ";
            $aSearch['edate']  =  stripslashes_deep($aSearch['edate']);
        }
    	$p         = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pn        = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        $oBalanceLog = new model_adminlog();
        $aResult   = $oBalanceLog->getBanlanceChangeLogList('*', $sWhere, $pn , $p);
        $oPager    = new pages( $aResult['affects'], $pn, 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'aLogList', $aResult['results'] );
        $GLOBALS['oView']->assign("ur_here", "余额修正日志列表");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","balancelogs"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign("s", $aHtml );
        $oBalanceLog->assignSysInfo();
        $GLOBALS['oView']->display("log_balancelog.html");
        exit;
    }
    
    
	/**
     * 查看充值操作日志
     * 4/28/2010
     * JIM
     */
    function actionLoadlogs(){
		$aSearch['payment_id']= !empty($_GET['payment_id']) ? $_GET['payment_id'] : "";
		$aSearch['payment_id_str']= !empty($_GET['payment_id_str']) ? $_GET['payment_id_str'] : "";
    	$aSearch['sdate']   = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 00:00');
        $aSearch['edate']   = isset($_GET['edate']) ? trim($_GET['edate']) : "";
      	$aSearch['payment_id'] = eregi_replace('[A-Z]','', strtoupper($aSearch['payment_id']) );
      	
        $sWhere = ' 1 ';
    	if( $aSearch['payment_id'] != '' )
        {
            $sWhere .= " AND `payment_id` = '".daddslashes($aSearch['payment_id'])."' ";
        }
    	if( $aSearch['payment_id_str'] != '' )
        {
            $sWhere .= " AND `payment_id_str` = '".daddslashes($aSearch['payment_id_str'])."' ";
        }
        if( $aSearch['sdate'] != '' )
        {
            $sWhere .= " AND (`log_time` >= '".daddslashes($aSearch['sdate'])."') ";
            $aSearch['sdate']  =  stripslashes_deep($aSearch['sdate']);
        }
        if( $aSearch['edate'] != '' )
        {
            $sWhere .= " AND (`log_time` <= '".daddslashes($aSearch['edate'])."') ";
            $aSearch['edate']  =  stripslashes_deep($aSearch['edate']);
        }
    	$p         = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pn        = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        $oOnlineLoadLog = new model_adminlog();
        $aResult   = $oOnlineLoadLog->getOnlineLoadLogList('*', $sWhere, $pn , $p);
        $oPager    = new pages( $aResult['affects'], $pn, 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'aLogList', $aResult['results'] );
        $GLOBALS['oView']->assign("ur_here", "充值日志列表");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","loadlogs"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign("s", $aSearch );
        $oOnlineLoadLog->assignSysInfo();
        $GLOBALS['oView']->display("log_onlineloadlog.html");
        exit;
    }
}
?>