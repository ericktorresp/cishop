<?php
/**
 * 文件 : /_app/controller/log.php
 * 功能 : 控制器  - 日志管理
 *
 * 功能:
 *  + actionAdminlog        管理员日志
 *  + actionAdminlogview    管理员日志详情
 *  + actionUserlog         用户日志
 *  + actionUserlogview     用户日志详情
 * 
 * @author    TOM
 * @version   1.2.0
 * @package   highgame
 */

class controller_log extends basecontroller
{
    /**
     * 管理员日志列表
     * URL = ./index.php?controller=log&action=adminlog
     * @author Tom
     * 
     * HTML 可选条件
     *   - 1, 日志类型        lt        ( Log Type )
     *   - 2, 控制器名        lc        ( Log Controller )
     *   - 3, 行为器名        la        ( Log Action )
     *   - 4, 管理员编号      aid       ( admin id )
     *   - 5, 日志开始时间    sdate     ( start date ) 
     *   - 6, 日志截止时间    edate     ( end date )
     *   - 7, IP地址模糊      ipaddr    
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
        /* @var $oPassPort model_passport*/
        $oPassPort         =  A::singleton("model_passport", $GLOBALS['aSysDbServer']['report']);
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
                                "' OR `proxyip` LIKE '".daddslashes($tmpUsername)."' ) ";
            }
            else 
            {
                $sWhere .= " AND ( `clientip` = '".daddslashes($aSearch['ipaddr']).
                                "' OR `proxyip` = '".daddslashes($aSearch['ipaddr'])."' ) ";
            }
            $aSearch['ipaddr']  =  stripslashes_deep($aSearch['ipaddr']);
        }

        // 03, 数据查询
        // 分页处理,  HTML 宏解析
        $p         = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pn        = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        /* @var $oAdminLog model_adminlog */
        $oAdminLog = A::singleton("model_adminlog", $GLOBALS['aSysDbServer']['report']);
        $aResult   = $oAdminLog->getAdminLogList('*', $sWhere, $pn , $p);
        $oPager    = new pages( $aResult['affects'], $pn, 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'aLogList', $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign("ur_here","管理员日志列表");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","adminlog"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign("s", $aHtml );
        $oAdminLog->assignSysInfo();
        $GLOBALS['oView']->display("log_adminlog.html");
        EXIT;
    }


    /**
     * 查看管理员日志详情
     * URL = ./controller=log&action=adminlogview
     * @author Tom
     */
    function actionAdminlogview()
    {
        $aLocation[0] = array("text" => "返回管理员日志列表","href" => url("log","adminlog"));
        $id = (isset($_GET["id"])&&is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
        /* @var $oAdminLogs model_adminlog */
        $oAdminLogs = A::singleton("model_adminlog", $GLOBALS['aSysDbServer']['report']);
        $aResult = $oAdminLogs->getAdminLogInfo( $id );
        if( $aResult == -1 )
        {
            sysMessage("日志获取失败", 1, $aLocation);
        }
        $aResult['request'] = htmlspecialchars( var_export( stripslashes_deep(@unserialize($aResult['requeststring'])), TRUE ));
        $GLOBALS['oView']->assign( 's', $aResult ); // 数据分配
        $GLOBALS['oView']->assign("ur_here","查看管理员日志详情");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","adminlog"), 'text'=>'管理员日志列表' ) );
        $GLOBALS['oView']->display("log_adminview.html");
        EXIT;
    }



    /**
     * 用户日志列表
     * URL = ./controller=log&action=userlog
     * @author Tom
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
            /* @var $oUser model_user */
            $oUser      = A::singleton("model_user", $GLOBALS['aSysDbServer']['report']);
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
                                "' OR `proxyip` LIKE '".daddslashes($tmpUsername)."' ) ";
            }
            else 
            {
                $sWhere .= " AND ( `clientip` = '".daddslashes($aSearch['ipaddr']).
                                "' OR `proxyip` = '".daddslashes($aSearch['ipaddr'])."' ) ";
            }
            $aSearch['ipaddr']  =  stripslashes_deep($aSearch['ipaddr']);
        }

        // 03, 数据查询
        $p         = isset($_GET['p'])  ? intval($_GET['p'])  : 0;
        $pn        = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        /* @var $oAdminLog model_adminlog */
        $oAdminLog = A::singleton("model_adminlog", $GLOBALS['aSysDbServer']['report']);
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
     * @author Tom
     */
    function actionUserlogview()
    {
        $aLocation[0] = array("text" => "返回用户日志列表","href" => url("log","userlog"));
        $id = (isset($_GET["id"])&&is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
        /* @var $oAdminLogs model_adminlog */
        $oAdminLogs = A::singleton("model_adminlog", $GLOBALS['aSysDbServer']['report']);
        $aResult = $oAdminLogs->getUserLogInfo( $id );
        if( $aResult == -1 )
        {
            sysMessage("抱歉, 日志获取失败。", 1, $aLocation);
        }
        $aResult['request'] = htmlspecialchars( var_export( stripslashes_deep(@unserialize($aResult['requeststring'])), TRUE ));
        $GLOBALS['oView']->assign( 's', $aResult ); // 数据分配
        $GLOBALS['oView']->assign("ur_here","查看用户日志详情");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("log","userlog"), 'text'=>'用户日志列表' ) );
        $GLOBALS['oView']->display("log_userview.html");
        EXIT;
    }
}
?>