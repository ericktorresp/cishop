<?php
/**
 * 文件 : /_app/controller/report.php
 * 功能 : 报表管理的所有操作
 *  
 * 类中所有的以 action 开头+首字母大写的英文, 为 "动作方法"
 * 例如 URL 访问: 
 *     http://www.xxx.com/?controller=default&action=abc
 *     default 是控制器名
 *     abc     是动作方法
 *     定义动作方法函数的命名, 规则为 action+首字母大写的全英文字符串
 *        例: 为实现上例的 /?controller=default&action=abc 中的 abc 方法
 *            需要在类中定义 actionIndex() 函数
 * 
 * 功能:
 *     -- actionBankReport      银行帐变
 *     -- actionCtReport        充提报表
 *     -- actionAccount         频道余额
 *     -- actionReportMap       统计报表
 * 
 * @author    james
 * @version   1.2.0
 * @package   passport
 */

class controller_report extends basecontroller 
{
    /**
     * 银行帐变
     */
    function actionBankReport()
    {
        $GLOBALS['oView']->display( "load.html", "loading" );
        @ob_flush();
        flush();
        $iUserId      = $_SESSION['userid'];
        $sAndWhere    = " AND o.`ordertypeid` != '13' AND o.`ordertypeid` != '28' ";  //用户不显示提现成功帐变
        //$sOrderBy     = " o.`times` DESC ";
        $sOrderBy     = " o.`entry` DESC ";
        $iAllChildren = 6;  //默认为自己
        $iHasSearch   = 1;  //是否需要用户名搜索
        $iIsTopProxy  = 0;  //是否为总代
        $sMinTime     = strtotime(date('Y-m-d'));   //默认开始时间[一天以前]
        $sMaxTime     = strtotime( date('Y-m-d') ) + 86400;   //默认最后时间[当天]
        $aSearchData  = array(
                        'username'      => '',
                        'ordertype'     => 0,
                        'ordertime_min' => '',
                        'ordertime_max' => '',
                        'range'         => '',
        );
        if( $_SESSION['usertype'] == 1 )
        {//代理
            $aRange = array(
                                '2' => '全部',
                                '6' => '自己',
                                '3' => '直接下级',
                                '4' => '所有下级'
                           );
            $oUser   = new model_user();
            if( $oUser->isTopProxy( $_SESSION['userid'] ) )
            {//判断是否为总代
                 $iIsTopProxy = 1;
            }
            unset($oUser);
        }
        elseif( $_SESSION['usertype'] == 2 )
        {//如果为总代管理员，则当前用户调整到其总代ID
            $aRange = array(
                                '2' => '全部',
                                '6' => '自己',
                                '3' => '直接下级',
                                '4' => '所有下级'
                           );
            $oUser   = new model_user();
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg("操作失败",2);
            }
            //检测是否为销售管理员
            if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
            {//是销售管理员
                    $iAllChildren = 3;
                    $aRange = array(
                                        '4'=>'全部',
                                        '3'=>'直接下级'
                                    );
                    //获取分配给该销售管理员的一代ID
                    $aProxyid = $oUser->getAdminProxyByUserId( $_SESSION['userid'] );
                    if( empty($aProxyid) )
                    {//没有分配代理则数据都为空
                        $aResult = array(
                                        'affects'   => 0,
                                        'results'   => array(),
                                        'icount'    => array('in'=>0, 'out'=>0, 'left'=>0),
                                    );
                    }
                    else 
                    {
                        $sAndWhere .= " AND ut.`lvproxyid` IN(".implode(',',$aProxyid).") ";
                        unset($aProxyid);
                    }
            }
            unset($oUser);
            $iIsTopProxy = 1; //允许查看总代的平台提现
        }
        else 
        {//会员
            $aRange = array();
            $iHasSearch = 0;
        }
        if( !empty($_GET['lntype']) && in_array($_GET['lntype'], array('brcz','sjcz','xjcz','brtx','xjtx','zxcz','zxtxkk')) )
        {//如果为快捷方式查询
            //01:范围始终保持是自己
            $iAllChildren  = 6;
            $_GET['range'] = '';
            //02:特定的帐变类型
            $_GET['ordertype'] = '';
            //03:用户名搜索无效
            $_GET['username'] = '';
            //04:根据快捷方式的不同做不同处理
            if( $_GET['lntype'] == 'sjcz' )
            {//上级充值[上级充值、理赔充值、跨级充值]
                $sAndWhere .= " AND o.`ordertypeid` IN(1,2,23) "; 
            }
        	elseif( $_GET['lntype'] == 'brcz' )
            {//本人充值[上级充值、理赔充值、跨级充值、在线充值、人工充值]
                $sAndWhere .= " AND o.`ordertypeid` IN(1,2,23,31,38) "; 
            }
            elseif( $_GET['lntype'] == 'xjcz' )
            {//下级充值[充值扣费]
                $sAndWhere .= " AND o.`ordertypeid`=4 "; 
            }
            /*elseif( $_GET['lntype'] == 'brtx' )
            {//本人提现[平台提现申请，平台提现失败、商务提现申请，商务提现失败、本人发起提现、本人提现、跨级提现]
                $sAndWhere .= " AND o.`ordertypeid` IN(5,6,8,".($iIsTopProxy == 1 ? "10,11" : "26,27").") "; 
            }*/
        	elseif( $_GET['lntype'] == 'brtx' )
            {//本人提现[平台提现申请，平台提现失败、商务提现申请，商务提现失败、本人发起提现、本人提现、跨级提现、在线提现扣款、人工提现解冻]
                $sAndWhere .= " AND o.`ordertypeid` IN(5,6,8,".($iIsTopProxy == 1 ? "10,11" : "26,27,33,37").") "; 
            }
        	elseif( $_GET['lntype'] == 'zxcz' )
            {//在线充值 add 3/19/2010
                $sAndWhere .= " AND o.`ordertypeid`=31 "; 
            }
            elseif( $_GET['lntype'] == 'zxtxkk' )
            {//在线提现扣款 add 4/15/2010
                $sAndWhere .= " AND o.`ordertypeid`=33 "; 
            }
            else
            {//下级提现[下级发起提现、下级提现,平台 提现成功(总代看)]
                $sAndWhere .= " AND o.`ordertypeid` IN(7,9".($iIsTopProxy == 1 ? ",29" : "").") "; 
            }
        }
        if( !empty($_GET['username']) )
        {
            //检测输入合法性
            if( preg_match("/[^0-9a-zA-Z]+/i",$_GET['username']) )
            {
                sysMsg("请输入合法的用户名");
            }
            $iAllChildren = 4;
            $aSearchData['username'] = $_GET['username'];
            $sAndWhere .= " AND ut.`username` like '".daddslashes($_GET['username'])."' "; 
        }
        if( !empty($_GET['range']) && is_numeric($_GET['range']) )
        {
            $iAllChildren = intval($_GET['range']);
        }
        if( !empty($aRange) )
        {
            $aSearchData['range'] = "范围:<select name='range'>";
            foreach( $aRange as $k=>$v )
            {
                $sSel = $iAllChildren==intval($k) ? "selected" : "";
                $aSearchData['range'] .= "<OPTION $sSel value=\"".$k."\">".$v."</OPTION>";
            }
            $aSearchData['range'] .= "</select>";
        }
        if( !empty($_GET['ordertype']) && is_numeric($_GET['ordertype']) )
        {
            $aSearchData['ordertype'] = $_GET['ordertype'];
            $sAndWhere .= " AND o.`ordertypeid`='".intval($_GET['ordertype'])."' "; 
        }
        if( !empty($_GET['ordertime_min']) )
        {
            if( checkDateTime($_GET['ordertime_min']) == FALSE )
            {
                sysMsg("请输入正确的日期");
            }
            $sMinTime = strtotime($_GET['ordertime_min']);
            if( $sMinTime > $sMaxTime )
            {
                $sMinTime = strtotime(date('Y-m-d'));
            }
            
        		
        }
        else
        {
        	// 6/9/2010
        		if ( date("Y-m-d H:i:s", strtotime("NOW") ) < date("Y-m-d 02:20:00") )
    	    	{
	        		$sMinTime = strtotime ( date("Y-m-d", strtotime("-1 days") ) );
        		}
        		else
        		{
            		$sMinTime = strtotime( date('Y-m-d') );
        		}
        }
        if( !empty($_GET['ordertime_max']) )
        {
            if( checkDateTime($_GET['ordertime_max']) == FALSE )
            {
                sysMsg("请输入正确的日期");
            }
            $aSearchData['ordertime_max'] = $_GET['ordertime_max'];
            if( strtotime($aSearchData['ordertime_max']) > $sMaxTime )
            {//搜索最大时间大于当前时间则调整为当天
                $aSearchData['ordertime_max'] = date("Y-m-d H:i",$sMaxTime);
            }
            else 
            {
        		$sMaxTime = strtotime( $aSearchData['ordertime_max'] );
            }
            if( $sMaxTime < $sMinTime )
            {//如果最大时间小于最小时间，则无效
                $sMaxTime = strtotime( date('Y-m-d', time()) ) + 86400;
                $aSearchData['ordertime_max'] = date( "Y-m-d H:i", $sMaxTime );
            }
            if( $sMaxTime > ($sMinTime + 8640000) )
            {//最大间隔时间为100天
                $sMaxTime = $sMinTime + 8640000;
                $aSearchData['ordertime_max'] = date( "Y-m-d H:i", $sMaxTime );
            }
            $sAndWhere .= " AND o.`times` <= '".date( "Y-m-d 02:20:00", $sMaxTime )."' ";
        }
        else 
        {
            if( $sMaxTime < $sMinTime )
            {//如果最小时间大于当前时间，则无效
                $sMinTime = $sMaxTime - 86400;
            }
            if( $sMaxTime > ($sMinTime + 8640000) )
            {//最大间隔时间为100天
                $sMinTime = $sMaxTime - 8640000;
            }
            $aSearchData['ordertime_max'] = date("Y-m-d 02:20:00",$sMaxTime);
            
       		 // 6/9/2010
        		if ( date("Y-m-d H:i:s", strtotime("NOW") ) < date("Y-m-d 02:20:00") )
    	    	{
	        		$aSearchData['ordertime_max'] = date("Y-m-d 02:20:00", ($sMaxTime - 86400) );
        		}
        		else
        		{
            		$aSearchData['ordertime_max'] = date("Y-m-d 02:20:00",$sMaxTime);
        		}
        		
        }
        $aSearchData['ordertime_min'] = date("Y-m-d 02:20:00",$sMinTime);
        
        $sAndWhere .= " AND o.`times` >= '".date("Y-m-d 02:20:00",$sMinTime)."' ";
        $oOrder = new model_orders( $GLOBALS['aSysDbServer']['report'] );
        $p  = isset($_GET['p']) ? intval($_GET['p']) : 0;
        if( isset($_GET['pn']) && in_array( intval($_GET['pn']) , array(20,40,80) ) )
        {
            $pn = intval($_GET['pn']);
        }
        else 
        {
            $pn = 20;
        }
        $cn = isset($_GET['cn']) ? intval($_GET['cn']) : 0;
        if( empty($aResult) )
        {
            $aResult = $oOrder->getUserOrderList( $iUserId, '', $sAndWhere, $sOrderBy, $cn, $pn, $p, 
                                                     $iAllChildren );
        }
        $oPager = new pages( $aResult['affects'], $pn, 0);
        if( empty($aResult['results']) )
        {
            $aResult['results'] = array();
        }
        $sWhere = $iIsTopProxy == 1 ? " AND `id` NOT IN(13,28) " : " AND `id` NOT IN(10,11,13,28,29) ";
        //获取帐变类型
        $aOrdertype = $oOrder->getOrderType( 'arr', '', $sWhere );
        $GLOBALS['oView']->assign( 'searchdata', $aSearchData );
        $GLOBALS['oView']->assign( 'hassearch', $iHasSearch );
        $GLOBALS['oView']->assign( 'icount',     $aResult['icount'] );
        $GLOBALS['oView']->assign( 'ordertypes', $aOrdertype );
        $GLOBALS['oView']->assign( 'page',       $oPager->show(2) );
        $GLOBALS['oView']->assign( 'orders',     $aResult['results'] );
        $GLOBALS['oView']->assign( 'ur_here','银行账变');
        $oOrder->assignSysInfo();
        $GLOBALS['oView']->display( "report_bankreport.html" );
        EXIT;
    }



    /**
     * 充提报表
     */
    function actionCtReport()
    {
        $iUserId = $_SESSION['userid'];
        if( empty($iUserId) )
        {
            sysMsg( "非法操作",2 );
        }
        $GLOBALS['oView']->display("load.html","loading");
        @ob_flush();
        flush();
        $aSearchData = array( 'ordertime_min' => '', 'ordertime_max' => '' );
        $sAndWhere   = "";
        $sMinTime    = strtotime( date('Y-m-d') );	//默认开始时间[一天以前]
        $sMaxTime    = strtotime( date('Y-m-d') ) + 86400;	//默认最后时间[当天]
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $oUser   = new model_user();
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg("操作失败",2);
            }
            //检测是否为销售管理员
            if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
            {//是销售管理员
                    //获取分配给该销售管理员的一代ID
                    $aProxyid = $oUser->getAdminProxyByUserId( $_SESSION['userid'] );
                    if( empty($aProxyid) )
                    {//没有分配代理则数据都为空
                        $aResult = array( 
                                          'count'  => array( 'incount' => 0, 'outcount' => 0, 'left' => 0 ),
                                          'result' => array() 
                                         );
                    }
                    else 
                    {
                        $sAndWhere .= " AND `userid` IN(".implode(',',$aProxyid).") ";
                    }
            }
            unset($oUser);
        }
        if( !empty($_GET['ordertime_min']) )
        {
            if( checkDateTime($_GET['ordertime_min']) == FALSE )
            {
                sysMsg("请输入正确的日期");
            }
            $sMinTime = strtotime($_GET['ordertime_min']);
            if( $sMinTime > $sMaxTime )
            {
                $sMinTime = strtotime(date('Y-m-d'));
            }
        }
        if( !empty($_GET['ordertime_max']) )
        {
            if( checkDateTime($_GET['ordertime_max']) == FALSE )
            {
                sysMsg("请输入正确的日期");
            }
            $aSearchData['ordertime_max'] = $_GET['ordertime_max'];
            if( strtotime($aSearchData['ordertime_max']) > $sMaxTime )
            {//搜索最大时间大于当前时间则调整为当天
                $aSearchData['ordertime_max'] = date("Y-m-d H:i",$sMaxTime);
            }
            else 
            {
                $sMaxTime = strtotime( $aSearchData['ordertime_max'] );
            }
            if( $sMaxTime < $sMinTime )
            {//如果最大时间小于最小时间，则无效
                $sMaxTime = strtotime( date('Y-m-d', time()) ) + 86400;
                $aSearchData['ordertime_max'] = date( "Y-m-d H:i", $sMaxTime );
            }
            if( $sMaxTime > ($sMinTime + 8640000) )
            {//最大间隔时间为100天
                $sMaxTime = $sMinTime + 8640000;
                $aSearchData['ordertime_max'] = date( "Y-m-d H:i", $sMaxTime );
            }
        }
        else 
        {
            if( $sMaxTime < $sMinTime )
            {//如果最小时间大于当前时间，则无效
                $sMinTime = $sMaxTime - 691200;
            }
            if( $sMaxTime > ($sMinTime + 8640000) )
            {//最大间隔时间为100天
                $sMinTime = $sMaxTime - 8640000;
                
            }
            $aSearchData['ordertime_max'] = date( "Y-m-d H:i", $sMaxTime );
        }
        $aSearchData['ordertime_min'] = date( "Y-m-d H:i", $sMinTime );
        $oOrder = new model_orders( $GLOBALS['aSysDbServer']['report'] );
        $sAndWhere .= " ORDER BY `username` ";
        if( empty($aResult) )
        {
            $aResult = $oOrder->getCashCountByUser( $iUserId, date("Y-m-d H:i:s", $sMinTime), date("Y-m-d H:i:s", $sMaxTime), 
                                                    $sAndWhere );
        }
        $GLOBALS['oView']->assign( 'searchdata', $aSearchData );
        $GLOBALS['oView']->assign( 'icount', $aResult['count'] );
        $GLOBALS['oView']->assign( 'orders', $aResult['result'] );
        $GLOBALS['oView']->assign( 'ur_here','充提报表');
        $oOrder->assignSysInfo();
        $GLOBALS['oView']->display( "report_ctreport.html" );
        EXIT;
    }



    /**
     * 频道余额
     */
    function actionAccount()
    {
        $iUserId = $_SESSION['userid'];
        if( empty($iUserId) )
        {
            sysMsg( "非法操作",2 );
        }
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $oUser   = new model_user();
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg("操作失败",2);
            }
        }
        if( empty($_POST['flag']) || $_POST['flag']!='getdata' )
        {
            //获取所有频道信息
            $oChannel = new model_userchannel();
            $aChannel = $oChannel->getUserChannelList( $iUserId );
            //获取银行余额
            $oFund    = new model_userfund();
            $aUserFund= $oFund->getFundByUser( $iUserId, "", SYS_CHANNELID, FALSE );
            
            $GLOBALS['oView']->assign( 'user', $aUserFund );
            $GLOBALS['oView']->assign( 'channels', $aChannel );
            $GLOBALS['oView']->assign( 'ur_here','频道余额');
            $oFund->assignSysInfo();
            $GLOBALS['oView']->display("report_account.html");
            EXIT;
        }
        elseif( $_POST['flag']=='getdata' )
        {
            if( empty($_POST['channelid']) || !is_numeric($_POST['channelid']) || $_POST['channelid'] < 0 )
            {
                die('error');
            }
            $iChannelId  = intval($_POST['channelid']);
            $oChannelApi = new channelapi( $iChannelId, 'getUserCash', FALSE );
            $oChannelApi->setTimeOut(15);            // 设置读取超时时间
            $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
            $oChannelApi->sendRequest( array("iUserId" => $iUserId) );    // 发送结果集
            $aResult = $oChannelApi->getDatas();
            if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
            {//调用API获取结果失败
                die("error");
            }
            die($aResult['data']);
        }
    }



    /**
     * 统计报表
     */
    function actionReportMap()
    {
        $iUserId = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $oUser   = new model_user();
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
        }
        if( empty($iUserId) )
        {
            sysMsg( "非法操作",2 );
        }
        $bIsSales    = FALSE;   //是否为总代销售管理员
        $sMinTime    = strtotime(date('Y-m-d')) - 604800;   //默认开始时间[一周以前]
        $sMaxTime    = strtotime( date('Y-m-d', time()) ) + 86400; //默认最后时间[当天]
        $aSearchData = array(
                                'ordertime_min' => '',
                                'ordertime_max' => '',
                                'ordertype'     => 'all'
                            );
        if( !empty($_GET['getdatas']) )
        {//FLASH调用
            $sAndWhere_plat = " AND ut.`userid`='".$iUserId."' ";   //平台
            $sAndWhere_child= " AND ut.`parentid`='".$iUserId."' "; //下级
        }
        else 
        {
            $sAndWhere= " AND (ut.`userid`='".$iUserId."' OR ut.`parentid`='".$iUserId."') ";//默认查询自己和下级
        }
        if( !empty($_GET['ordertype']) )
        {
            $aSearchData['ordertype'] = $_GET['ordertype'];
            if( $aSearchData['ordertype'] == 'platsave' || $aSearchData['ordertype'] == 'platdraw' )
            {//平台充值和充值
                if( !empty($_GET['getdatas']) )
                {//FLASH调用
                    $sAndWhere_plat = " AND ut.`userid`='".$iUserId."' ";   //平台
                    $sAndWhere_child= "";   //下级
                }
                else 
                {
                    $sAndWhere = " AND ut.`userid`='".$iUserId."' ";
                }
            }
            elseif( $aSearchData['ordertype'] == 'save' || $aSearchData['ordertype'] == 'draw' )
            {//下级充值
                if( !empty($_GET['getdatas']) )
                {//FLASH调用
                    $sAndWhere_plat = "";   //平台
                    $sAndWhere_child= " AND ut.`parentid`='".$iUserId."' "; //下级
                }
                else 
                {
                    $sAndWhere = " AND ut.`parentid`='".$iUserId."' ";  
                }
            }
            else
            {//默认
                if( !empty($_GET['getdatas']) )
                {//FLASH调用
                    $sAndWhere_plat = " AND ut.`userid`='".$iUserId."' ";   //平台
                    $sAndWhere_child= " AND ut.`parentid`='".$iUserId."' "; //下级
                }
                else 
                {
                    $sAndWhere= " AND (ut.`userid`='".$iUserId."' OR ut.`parentid`='".$iUserId."') ";//默认查询自己和下级
                }
            }
        }
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            //检测是否为销售管理员
            if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
            {//是销售管理员
                $bIsSales = TRUE;
                if( $aSearchData['ordertype'] == 'platsave' || $aSearchData['ordertype'] == 'platdraw' )
                {
                    $aResult = array();
                }
                else 
                {
                    //获取分配给该销售管理员的一代ID
                    $aProxyid = $oUser->getAdminProxyByUserId( $_SESSION['userid'] );
                    if( empty($aProxyid) )
                    {//没有分配代理则数据都为空
                        $aResult = array();
                    }
                    else 
                    {
                        if( !empty($_GET['getdatas']) )
                        {//FLASH调用
                            $sAndWhere_plat = "";   //平台
                            $sAndWhere_child= " AND ut.`userid` IN(".implode(',',$aProxyid).")";    //下级
                        }
                        else 
                        {
                            $sAndWhere = " AND ut.`userid` IN(".implode(',',$aProxyid).")";
                        }
                    }
                }
            }
            unset($oUser);
        }
        //获取搜索条件
        if( !empty($_GET['ordertime_min']) )
        {
            if( checkDateTime($_GET['ordertime_min']) == FALSE )
            {
                sysMsg("请输入正确的日期");
            }
            $sMinTime = strtotime($_GET['ordertime_min']);
            if( $sMinTime > $sMaxTime )
            {
                $sMinTime = strtotime(date('Y-m-d')) - 604800;
            }
        }
        if( !empty($_GET['ordertime_max']) )
        {
            if( checkDateTime($_GET['ordertime_max']) == FALSE )
            {
                sysMsg("请输入正确的日期");
            }
            $aSearchData['ordertime_max'] = $_GET['ordertime_max'];
            if( strtotime($aSearchData['ordertime_max']) > ($sMaxTime - 86400) )
            {//搜索最大时间大于当前时间则调整为当天
                $aSearchData['ordertime_max'] = date("Y-m-d", $sMaxTime - 1);
            }
            else 
            {
                $sMaxTime = strtotime( $aSearchData['ordertime_max'] ) + 86400;
            }
            if( $sMaxTime < $sMinTime )
            {//如果最大时间小于最小时间，则无效
                $sMaxTime = strtotime( date('Y-m-d',time()) ) + 86400;
                $aSearchData['ordertime_max'] = date( "Y-m-d", $sMaxTime - 1 );
            }
            if( $sMaxTime > ($sMinTime + 8640000) )
            {//最大间隔时间为100天，主要是图表显示不会太紧密
                $sMaxTime = $sMinTime + 8640000;
                $aSearchData['ordertime_max'] = date( "Y-m-d", $sMaxTime );
            }
            if( !empty($_GET['getdatas']) )
            {//FLASH调用
                $sAndWhere_plat .= " AND rt.`times` <= '".date( "Y-m-d", $sMaxTime )."' "; //平台
                $sAndWhere_child.= " AND rt.`times` <= '".date( "Y-m-d", $sMaxTime )."' "; //下级
            }
            else 
            {
                $sAndWhere .= " AND rt.`times` <= '".date( "Y-m-d", $sMaxTime )."' ";
            }
        }
        else 
        {
            if( $sMaxTime < $sMinTime )
            {//如果最小时间大于当前时间，则无效
                $sMinTime = $sMaxTime - 691200;
            }
            if( $sMaxTime > ($sMinTime + 8640000) )
            {//最大间隔时间为100天，主要是图表显示不会太紧密
                $sMinTime = $sMaxTime - 8640000;
                
            }
            $aSearchData['ordertime_max'] = date( "Y-m-d", $sMaxTime - 1 );
        }
        $aSearchData['ordertime_min'] = date( "Y-m-d", $sMinTime );
        if( !empty($_GET['getdatas']) )
        {//FLASH调用
            $sAndWhere_plat .= " AND rt.`times` > '".date( "Y-m-d", $sMinTime )."' ";  //平台
            $sAndWhere_child.= " AND rt.`times` > '".date( "Y-m-d", $sMinTime )."' ";  //下级
        }
        else 
        {
            $sAndWhere .= " AND rt.`times` > '".date( "Y-m-d", $sMinTime )."' ";       
        }
        if( !empty($_GET['getdatas']) )
        {//仅供FLASH调用
            
            $aLabelAll  = array();
            $aPlatSave  = array();  //平台充值曲线
            $aPlatDraw  = array();  //平台提现曲线
            $aSave      = array();  //下级充值曲线
            $aDraw      = array();  //下级提现曲线
            //设置横坐标[时间点]
            for( $i=$sMinTime; $i<=($sMaxTime); $i+=86400 )
            {
                $aLabelAll[] = date( 'Y/m/d', $i );
            }
            $iAllCount = count($aLabelAll);
            $oXml = new astats();
            $oXml->setChart( 'labelstep', ceil($iAllCount/10) );
            $oXml->addLabels( $aLabelAll );
            if( isset($aResult) )
            {//为空值
                for( $i=0; $i<count($aLabelAll); $i++ )
                {
                    $aPlatSave[]    = 0;
                    $aPlatDraw[]    = 0;
                    $aSave[]        = 0;
                    $aDraw[]        = 0;
                }
            }
            else 
            {
                $oOrder = new model_reportcount( $GLOBALS['aSysDbServer']['report'] );
                if( $bIsSales == TRUE )
                {//分配了代理的销售管理员
                    $temp_ar = $oOrder->getSaveAndDrawCountByDay( $sAndWhere_child );
                    $aChildResult = array();
                    foreach( $temp_ar as $v )
                    {
                        $aChildResult[date("Y/m/d",strtotime($v['day']))] = $v;
                    }
                    unset($temp_ar);
                    for( $i=0; $i<count($aLabelAll); $i++ )
                    {
                        $aPlatSave[]    = 0;    //平台充值始终为0
                        $aPlatDraw[]    = 0;    //平台提现始终为0
                        if( isset($aChildResult[$aLabelAll[$i]]) )
                        {
                            $aSave[]        = $aChildResult[$aLabelAll[$i]]['savecount'];
                            $aDraw[]        = $aChildResult[$aLabelAll[$i]]['withdrawcount'];
                        }
                        else 
                        {
                            $aSave[]        = 0;
                            $aDraw[]        = 0;
                        }
                    }
                }
                else 
                {//查看全部四条线
                    $aPlatResult = array();
                    $aChildResult= array();
                    if( $aSearchData['ordertype'] != 'save' && $aSearchData['ordertype'] != 'draw' )
                    {//平台充值和充值
                        $temp_ar = $oOrder->getSaveAndDrawCountByDay( $sAndWhere_plat );
                        foreach( $temp_ar as $v )
                        {
                            $aPlatResult[date("Y/m/d", strtotime($v['day']))] = $v;
                        }
                        unset($temp_ar);
                    }
                    if( $aSearchData['ordertype'] != 'platsave' && $aSearchData['ordertype'] != 'platdraw' )
                    {//下级充值和充值
                        $temp_ar = $oOrder->getSaveAndDrawCountByDay( $sAndWhere_child );
                        foreach( $temp_ar as $v )
                        {
                            $aChildResult[date("Y/m/d",strtotime($v['day']))] = $v;
                        }
                        unset($temp_ar);
                    }
                    for( $i=0; $i<count($aLabelAll); $i++ )
                    {
                        if( isset($aPlatResult[$aLabelAll[$i]]) )
                        {
                            $aPlatSave[]        = $aPlatResult[$aLabelAll[$i]]['savecount'];
                            $aPlatDraw[]        = $aPlatResult[$aLabelAll[$i]]['withdrawcount'];
                        }
                        else 
                        {
                            $aPlatSave[]        = 0;
                            $aPlatDraw[]        = 0;
                        }
                        if( isset($aChildResult[$aLabelAll[$i]]) )
                        {
                            $aSave[]        = $aChildResult[$aLabelAll[$i]]['savecount'];
                            $aDraw[]        = $aChildResult[$aLabelAll[$i]]['withdrawcount'];
                        }
                        else 
                        {
                            $aSave[]        = 0;
                            $aDraw[]        = 0;
                        }
                    }
                }
            }
            
            if( $aSearchData['ordertype'] == 'platsave' )
            {//平台充值
                $oXml->addData( $aPlatSave, '平台充值' );
            }
            elseif( $aSearchData['ordertype'] == 'platdraw' )
            {//平台提现
                $oXml->addData( $aPlatDraw, '平台提现', 'FF0000' );
            }
            elseif( $aSearchData['ordertype'] == 'save' )
            {//下级充值
                $oXml->addData( $aSave, '下级充值', '006600' );
            }
            elseif( $aSearchData['ordertype'] == 'draw' )
            {//下级提现
                $oXml->addData( $aDraw, '下级提现', 'FFCC00' );
            }
            else
            {//默认
                $oXml->addData( $aPlatSave, '平台充值' );
                $oXml->addData( $aPlatDraw, '平台提现', 'FF0000' );
                $oXml->addData( $aSave, '下级充值', '006600' );
                $oXml->addData( $aDraw, '下级提现', 'FFCC00' );
            }
            $oXml->display();
            EXIT;
        }
        if( !isset($aResult) )
        {//获取充值提现总额
            $oOrder = new model_reportcount( $GLOBALS['aSysDbServer']['report'] );
            $aResult = $oOrder->getSaveAndDrawCountByDay( $sAndWhere );
        }
        $flashdata = "./index.php?controller=report%26action=reportmap%26getdatas=TRUE%26ordertype=".$aSearchData['ordertype'].
                "%26ordertime_min=".$aSearchData['ordertime_min']."%26ordertime_max=".$aSearchData['ordertime_max'];
        $GLOBALS['oView']->assign( 'flashdata', $flashdata );
        $GLOBALS['oView']->assign( 'search', $aSearchData );
        $GLOBALS['oView']->assign( 'totals', $aResult );
        $GLOBALS['oView']->assign( 'ur_here','统计报表');
        $GLOBALS['oView']->display("report_reportmap.html");
        EXIT;
    }
    
    
    /**
     * 报表查询统计
     * 用户可以在passport平台上查询每一个平台的总代购费用和返点费用，中奖等数据
     * @author Mark
     * 
    */
    public function actionQuery()
    {
    	$aSaleAdmChlUser = array();
        /*获取基本数据*/
        $sDate       = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d",time()-60*60*24);
        if( strtotime($sDate) >= strtotime( date("Y-m-d",time()) ) )
        {
            sysMsg( "对不起，不能查询当天或以后的报表，只能查询今天以前的数据", 2 );
        }
        $iPid        = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
        $iAChannelId = isset($_GET['channelid']) ? intval($_GET['channelid']) : 1;
        $iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
        $iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
        $oUser   = A::singleton("model_user");
        $iSelfUserId = $iUserId;
        if( $iUserType == 2 )
        {
            //如果为总代管理员，则当前用户调整到其总代ID
            $iSelfUserId = $oUser->getTopProxyId( $iUserId );
            if( empty($iSelfUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            //检测是否为销售管理员
            if( TRUE == $oUser->IsAdminSale($iUserId) )
            {//是销售管理员
            	$aSaleAdmChlUser = $oUser->getAdminProxyByUserId($iUserId);
                if( !empty($iUserId) )
                {//检测是否在自己范围之内
                    if( FALSE == $oUser->isInAdminSale( $iPid, $iUserId) && $iPid != 0 && $iPid != $iSelfUserId )
                    {
                        sysMsg( "没有权限", 2 );
                    }
                }
            }
        }
        //检查是否有权限[必须是在自己树下的用户才能查看]
        if( FALSE == $oUser->isParent($iPid, $iSelfUserId) && $iPid != 0 && $iPid != $iSelfUserId )
        {
             sysMsg( "没有权限", 2 );
        }
        /*获取游戏频道*/
        $oChannel = new model_userchannel();
        $aChannel = $oChannel->getUserChannelList( $iSelfUserId );
        $aChannelResult = array();
        $aResult = array();
        if( isset($aChannel[0]) && is_array($aChannel[0]) )
        {
            foreach ( $aChannel[0] as $aValue )
            {
                $aChannelResult[$aValue['id']] =  $aValue['channel'];
            }
        }
        
        /*获取游戏频道报表数据*/
        $aApiSendData = array(
            "iPid"      => $iPid ? $iPid : 0,//是否是查询下级
            "iUserId"   => $iSelfUserId,//当前用户ID
            "sDate"     => $sDate//查询时间
        );
        $aApiSendData['aChildId'] = $aSaleAdmChlUser;
        //定义统计变量
        $fTotalAmount     = 0.00;
        $fTotalPoint      = 0.00;
        $fTotalRealAmount = 0.00;
        $fTotalBonus      = 0.00;
        $fTotalWin        = 0.00;
        if( $iPid == 0 ){//当前用户
        	if( $iUserType == 2 && TRUE == $oUser->IsAdminSale($iUserId)){ // 总代管理员
        		$aApiSendData['iAdminProxyId'] = $_SESSION['userid'];
        		foreach ( $aChannelResult as $iChannelId => $sChannelName )
	            {
	            	$sApi_name = "reportQuery";
	                $oChannelApi = new channelapi( $iChannelId, $sApi_name, FALSE );
	                $oChannelApi->setTimeOut(15);            // 设置读取超时时间
	                $oChannelApi->setResultType('serial');   // 设置返回数据类型
	                $oChannelApi->sendRequest( $aApiSendData );    // 发送结果集
	                $aApiResult = $oChannelApi->getDatas();
	                if( empty($aApiResult) || !is_array($aApiResult) || $aApiResult['status'] == 'error' )
	                {//调用API获取结果失败
	                    continue;
	                }
	                else
	                {
	                	$aResult[$iChannelId]['user'] = $aApiResult['data'];
	                }
	                $i = 0;
	                
	                foreach ( $aResult[$iChannelId]['user'] as $iKey => $aData )
	                {
	                    /*计算总额*/
	                    $fTotalAmount       += isset($aData["amount"]) ? $aData["amount"] : 0;
	                    $fTotalPoint        += isset($aData["point"]) ? $aData["point"]: 0;
	                    $fTotalRealAmount   += isset($aData["realamount"]) ? $aData["realamount"] : 0;
	                    $fTotalBonus        += isset($aData["bonus"]) ? $aData["bonus"] : 0;
	                    $fTotalWin          += isset($aData["win"]) ? $aData["win"] : 0;
	                    /*用户顺序*/
	                    $aResult[$iChannelId]['user'][$iKey]['order'] = $i++;
	                }
		            $aResult[$iChannelId]['usercount']   = count($aResult[$iChannelId]['user']);
	                $aResult[$iChannelId]['channelname'] = $sChannelName;
	            }
        	} else { // 总代
        		foreach ( $aChannelResult as $iChannelId => $sChannelName )
	            {
	            	$sApi_name = "reportQuery";
	                $oChannelApi = new channelapi( $iChannelId, $sApi_name, FALSE );
	                $oChannelApi->setTimeOut(15);            // 设置读取超时时间
	                $oChannelApi->setResultType('serial');   // 设置返回数据类型
	                $oChannelApi->sendRequest( $aApiSendData );    // 发送结果集
	                $aApiResult = $oChannelApi->getDatas();
	                if( empty($aApiResult) || !is_array($aApiResult) || $aApiResult['status'] == 'error' )
	                {//调用API获取结果失败
	                    continue;
	                }
	                else
	                {
	                	$aResult[$iChannelId]['user'] = $aApiResult['data'];
	                }
	                $i = 0;
		            
	            	foreach ( $aResult[$iChannelId]['user'] as $iKey => $aData )
	                {
	                    /*计算总额*/
	                    $fTotalAmount       += isset($aData["amount"]) ? $aData["amount"] : 0;
	                    $fTotalPoint        += isset($aData["point"]) ? $aData["point"]: 0;
	                    $fTotalRealAmount   += isset($aData["realamount"]) ? $aData["realamount"] : 0;
	                    $fTotalBonus        += isset($aData["bonus"]) ? $aData["bonus"] : 0;
	                    $fTotalWin          += isset($aData["win"]) ? $aData["win"] : 0;
	                    /*用户顺序*/
	                    $aResult[$iChannelId]['user'][$iKey]['order'] = $i++;
	                }
		            $aResult[$iChannelId]['usercount']   = count($aResult[$iChannelId]['user']);
	                $aResult[$iChannelId]['channelname'] = $sChannelName;
	            }
        	}
        }
        else
        {//下级 
            if( $iAChannelId == 0 )
            {
                sysMsg("请指定频道ID",1);
            }
            if( key_exists( $iAChannelId, $aChannelResult ) == FALSE )
            {
                sysMsg("频道不存在",1); 
            }
            $oChannelApi = new channelapi( $iAChannelId, 'reportQuery', FALSE );
            $oChannelApi->setTimeOut(15);            // 设置读取超时时间
            $oChannelApi->setResultType('serial');   // 设置返回数据类型
            $oChannelApi->sendRequest( $aApiSendData );    // 发送结果集
            $aApiResult = $oChannelApi->getDatas();
            if( empty($aApiResult) || !is_array($aApiResult) || $aApiResult['status'] == 'error' )
            {//调用API获取结果失败
                $aResult = array();
            }
            else
            {
                $aResult = $aApiResult['data'];
            }
            //剔除不属于销售管理员管理范围内的用户
            if( $iUserType == 2 && $iPid == $iSelfUserId && TRUE == $oUser->IsAdminSale($iUserId))
            {
                foreach ($aResult as $iKey => $aValue )
                {
                    if( FALSE == $oUser->isInAdminSale( $iKey, $iUserId) )
                    {
                       unset($aResult[$iKey]);
                    }
                }
            }
            //获取用户所属组
            foreach ( $aResult as $iKey => $aValue )
            {
                $aUserInfo = $oUser->getUserExtentdInfo($iKey);
                $aResult[$iKey]['usergroup'] = $aUserInfo['groupname'];
            }
        }
        
        /*输出视图*/
        $GLOBALS['oView']->assign( "aResult", $aResult );
        $GLOBALS['oView']->assign( "totalamount", $fTotalAmount );
        $GLOBALS['oView']->assign( "totalpoint", $fTotalPoint );
        $GLOBALS['oView']->assign( "totalrealamount", $fTotalRealAmount );
        $GLOBALS['oView']->assign( "totalbonus", $fTotalBonus );
        $GLOBALS['oView']->assign( "totalwin", $fTotalWin );
        $GLOBALS['oView']->assign( "date", $sDate );
        $GLOBALS['oView']->assign( "channelid", $iAChannelId );
        $GLOBALS['oView']->assign( "channelname", isset($aChannelResult[$iAChannelId]) ?  
                                                    $aChannelResult[$iAChannelId] : '');
        $GLOBALS['oView']->assign( "userid", $iSelfUserId );
        $GLOBALS['oView']->assign( "pid", $iPid );
        $GLOBALS['oView']->assign( "username", $_SESSION['username'] );
        $GLOBALS['oView']->assign( "usertype", $iUserType );
        $GLOBALS['oView']->assign( 'ur_here','报表查询');
        $oChannel->assignSysInfo();
    	$GLOBALS['oView']->display( "report_query.html" );
    }
    
    
    /**
     * 在passport平台下查询用户本身在各个平台下的游戏明细报表
     * @author mark
     */
    public function actionGamedetail()
    {
        /*获取基本信息*/
        $sDate       = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d",time()-60*60*24);
        if( strtotime($sDate) >= strtotime( date("Y-m-d",time()) ) )
        {
            sysMsg( "对不起，不能查询当天或以后的报表，只能查询今天以前的数据", 2 );
        }
        $iPid        	= isset($_GET['pid']) ? intval($_GET['pid']) : 0;
        $sUserName      = isset($_GET['username']) ? daddslashes($_GET['username']) : '';
        $sChannelName   = isset($_GET['channelname']) ? daddslashes($_GET['channelname']) : '';
        $iUserId        = isset($_GET['channelname']) ? intval($_GET['userid']) : 0;
        $iChannelId     = isset($_GET['channelid']) ? intval($_GET['channelid']) : 0;
        $iUserType      = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
        $oUser = A::singleton("model_user");
        $iSelfUserId = $_SESSION['userid'];
        if( $iUserType == 2 )
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
                    if( FALSE == $oUser->isInAdminSale( $iUserId, $_SESSION['userid'] ) && $iUserId != $iSelfUserId )
                    {
                        sysMsg( "没有权限", 2 );
                    }
                }
            }
        }
        //检查是否有权限[必须是在自己树下的用户才能查看]
        if( FALSE == $oUser->isParent($iUserId, $iSelfUserId) && $iUserId != $iSelfUserId )
        {
            sysMsg( "没有权限", 2 ); 
        }
        if ($iPid > 0){
        	if (TRUE == $oUser->IsAdminSale($_SESSION['userid'])){
        		$aSaleAdmChlUser = $oUser->getAdminProxyByUserId($_SESSION['userid']);
        	}
        } else {
        	$aSaleAdmChlUser = "";
        }
        /*整理发送到API的数据*/
        $aApiSendData = array(
            'iUserId' => $iUserId,
            'sDate'   => $sDate
        );
        $aApiSendData['aChildId'] = $aSaleAdmChlUser;
        /*调用API数据*/
        $oChannelApi = new channelapi( $iChannelId, 'reportGameDetail', FALSE );
        $oChannelApi->setTimeOut(15);            // 设置读取超时时间
        $oChannelApi->setResultType('serial');   // 设置返回数据类型
        $oChannelApi->sendRequest( $aApiSendData );    // 发送结果集
        $aApiResult = $oChannelApi->getDatas();
        if( empty($aApiResult) || !is_array($aApiResult) || $aApiResult['status'] == 'error' )
        {//调用API获取结果失败
            $aResult = array();
        }
        else
        {
            $aResult = $aApiResult['data'];
        }
        $aTotal = array();
        foreach( $aResult as $i=>$v )
        {
            $aTotal["sum_bonus"][$i]    = array_sum($v["sum_bonus"]);
            $aTotal["sum_prize"][$i]    = array_sum($v["sum_prize"]);
            $aTotal["real_prize"][$i]   = array_sum($v["real_prize"]);
            $aTotal["total"][$i]        = array_sum($v["total"]);
        }
        /*输出视图*/
        $GLOBALS['oView']->assign( "aResult", $aResult );
        $GLOBALS['oView']->assign( "total", $aTotal );
        $GLOBALS['oView']->assign( "date", $sDate );
        $GLOBALS['oView']->assign( "username", $sUserName );
        $GLOBALS['oView']->assign( "channelname", $sChannelName );
        $GLOBALS['oView']->assign( 'ur_here','游戏明细');
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display( "report_gamedetail.html" );
    }
}
?>