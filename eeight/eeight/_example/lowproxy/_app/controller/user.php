<?php
/**
 * 文件 : /_app/controller/user.php
 * 功能 : 用户信息的一些基本控制器
 *  
 * 
 * 功能:
 *    -- actionAddUser         增加用户
 *    -- actionUserList        用户列表（大厅)
 *    -- actionSaveUp          用户充值
 *    -- actionUpEditUser      上级修改下级用户
 *    -- actionUpUpdatePass    上级修改下级密码
 *    -- actionUpUserTeam      上级查看下级的团队余额
 *    -- actionFrozenUse       冻结用户
 *    -- actionUnFrozenUser    解冻用户
 *    -- actionUpWithdraw      用户提现（上级操作下级提现）
 *    -- actionDistribute      我的团队
 *    -- actionChangeName      帐户信息--修改呢称
 * 
 * @author    james
 * @version   1.2.0
 * @package   lowproxy
 */

class controller_user extends basecontroller 
{
    /* @var $oView view */
    /**
     * 用户列表
     * URL:./index.php?controller=user&action=list
     * @author JAMES
     */
    function actionList()
    {
        if( empty($_GET['frame']) || ($_GET['frame']!='menu' && $_GET['frame']!='show' && $_GET['frame']!='drag' ) )
        {
            $GLOBALS['oView']->display( "user_list_main.html", "userlistmain" );
            EXIT;
        }
        elseif( $_GET['frame'] == 'menu' )
        { // 动态加载左侧用户列表[直接下级全部显示|非直接下级只显示开通的]
            if( !empty($_GET['uid']) && is_numeric($_GET['uid']) )
            {
                $iUid = $_GET['uid'];
            }
            else
            {
                $iUid = 0;
            }
            $sAndWhere  = "";
            $aData      = "";
            /* @var $oUser model_user */
            $oUser      = A::singleton("model_user");
            $iUserId    = intval( $_SESSION['userid'] );
            // 如果为总代管理员，则当前用户调整到其总代ID
            if( $_SESSION['usertype'] == 2 )
            {
                $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
                if( empty($iUserId) )
                {
                    if( !empty($iUid) )
                    {//ajax调用
                        echo "{error:'fail'}";
                    }
                    else 
                    {//直接显示
                        echo "获取列表失败";
                    }
                    EXIT;
                }
                // 检测是否为销售管理员
                if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
                { // 是销售管理员
                    if( !empty($iUid) )
                    { // 检测是否在自己范围之内
                        if( FALSE == $oUser->isInAdminSale($iUid, $_SESSION['userid']) )
                        {
                            echo "{error:'empty'}";
                            EXIT;
                        }
                    }
                    else
                    {
                        // 获取分配给该销售管理员的一代ID
                        $aProxyid = $oUser->getAdminProxyByUserId( $_SESSION['userid'] );
                        if( empty($aProxyid) )
                        { // 没有分配代理则数据都为空
                            $aData = "empty";
                        }
                        else 
                        {
                            $sAndWhere .= " AND a.`userid` IN(".implode(',',$aProxyid).")";
                        }
                    }
                }
            }
            if( $aData=='empty' )
            {
                $aData = '';
            }
            else
            {
                if( empty($iUid) )
                {
                    $aData = $oUser->getChildListID( $iUserId, $sAndWhere." ORDER BY a.`username` ", TRUE );
                }
                else 
                {
                    $aData = $oUser->getChildrenListID( $iUid, FALSE, $sAndWhere, TRUE );
                }
                if( empty($aData) )
                {
                    $aData = '';
                }
            }
            if( !empty($iUid) )
            { // 如果为AJAX加载
                if( empty($aData) )
                {
                    echo "{error:'empty'}";
                }
                else
                {
                    $aTempData['result'] = $aData;
                    $aTempData['error'] = 0;
                    echo json_encode($aTempData);
                }
                EXIT;
            }
            $GLOBALS['oView']->assign('users',$aData);
            $GLOBALS['oView']->display("user_userlist_menu.html");
            EXIT;
        }
        elseif( $_GET['frame'] == 'drag' ) 
        {//收缩标签
            $GLOBALS['oView']->display( "default_drag.html", "userlistdrag" );
            EXIT;
        }
        elseif( $_GET['frame'] == 'show' )
        {
            $GLOBALS['oView']->display( "load.html", "loading" );
            @ob_flush();
            flush();
            $oUser        = new model_user();
            $iUserId      = $_SESSION['userid'];
            $sAndWhere    = '';
            $sOrderBy     = ' ut.`username` ';
            $bAllChildren = FALSE;
            $bSelf        = FALSE;
            // 如果为总代管理员，则当前用户调整到其总代ID
            if( $_SESSION['usertype'] == 2 )
            {
                $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
                if( empty($iUserId) )
                {
                    sysMsg( "操作失败", 2 );
                }
            }
            if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) || $_REQUEST['uid']==$iUserId ) 
            {
                $iUid  = 0;
                $bSelf = FALSE;
            }
            else 
            {
                $iUid = intval($_REQUEST['uid']);
                // 检查传入的用户是否为自己下级，不是自己下级则没有权限查看
                if( FALSE == $oUser->isParent($iUid,$iUserId) )
                {
                    sysMsg( "没有权限", 2 );
                }
                $bSelf = TRUE;
            }
            if( $_SESSION['usertype'] == 2 )
            {
                // 检测是否为销售管理员
                if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
                { // 是销售管理员
                    if( !empty($iUid) )
                    { // 检测是否在自己范围之内
                        if( FALSE == $oUser->isInAdminSale($iUid, $_SESSION['userid']) )
                        {
                            sysMsg( "没有权限", 2 );
                        }
                    }
                    else
                    {
                        // 获取分配给该销售管理员的一代ID
                        $aProxyid = $oUser->getAdminProxyByUserId( $_SESSION['userid'] );
                        if( empty($aProxyid) )
                        { // 没有分配代理则数据都为空
                            $aResult = array(
                                            'affects' =>0,
                                            'results' =>array()
                                        );
                        }
                        else 
                        {
                            $sAndWhere .= " AND ut.`lvproxyid` IN(".implode(',',$aProxyid).")";
                        }
                    }
                }
            }
            $GLOBALS['oView']->assign( 'curruser', $iUserId );
            $iSearchId = empty($iUid) ? $iUserId : $iUid;
            // 搜索处理
            $aFields = array(
                                'username'      => "",
                                'usergroup'     => 0,
                                'bank_min'      => "",
                                'bank_max'      => "",
                                'sortby'        => "",
                                'sortbymax'     => 0
            );
			
            /* Task No.YW-YH-201001-005add  start */
          
            if ( empty($_GET['username']) && !empty($_GET['usergroup']) )
            {
            	sysMsg( "搜索条件不足" );
            }
           	if ( !empty($_GET['username']) || ( empty($_GET['usergroup']) && !empty($_GET['username']) )  || ( is_numeric($_GET['usergroup']) && !empty($_GET['username']) ) )
            { 
            	// 用户名和用户组 组合
                $aFields['username'] 	= $_GET['username'];
                $aFields['usergroup'] 	= $_GET['usergroup'];
                // 检测输入合法性
                if( preg_match("/[^0-9a-zA-Z]+/i", trim($_GET['username'])) )
                {
                    sysMsg( "请输入合法的用户名" );
                }
                $sAndWhere .= " AND ut.`username` like '%".daddslashes($_GET['username'])."%' ";
                if( intval($_GET['usergroup']) == 1 )
                {
                    $sAndWhere .= " AND ut.`usertype`='1' ";
                }
                if( intval($_GET['usergroup']) == 2 )
                {
                    $sAndWhere .= " AND ut.`usertype`='0' ";
                }
                $bAllChildren = TRUE;
            }
			/* Task No.YW-YH-201001-005add end */
            
            if( !empty($_GET['bank_min']) && is_numeric($_GET['bank_min']) )
            { // 银行最低余额
                $sAndWhere          .= " AND uf.`availablebalance` >= '".floatval($_GET['bank_min'])."' ";
                $bAllChildren        = TRUE;
                $aFields['bank_min'] = $_GET['bank_min'];
            }
            if( !empty($_GET['bank_max']) && is_numeric($_GET['bank_max']) )
            { // 银行最高余额
                $sAndWhere          .= " AND uf.`availablebalance` <= '".floatval($_GET['bank_max'])."' ";
                $bAllChildren        = TRUE;
                $aFields['bank_max'] = $_GET['bank_max'];
            }
            if( !empty($_GET['sortby']) )
            { // 排序
                if( $_GET['sortby'] == 'username' )
                {
                    $sOrderBy = " ut.`username` ";
                }
                elseif( $_GET['sortby'] == 'bank' )
                {
                    $sOrderBy = " uf.`availablebalance` ";
                }
                $aFields['sortby'] = $_GET['sortby'];
            }
            if( !empty($_GET['sortbymax']) && $_GET['sortbymax'] == 1 )
            { // 排序是从大到小还是从小到大
                if(empty($sOrderBy))
                {
                    $sOrderBy .= " ut.`userid` DESC ";
                }
                else 
                {
                    $sOrderBy .= " DESC ";
                }
            }
            
            $aFields['sortbymax'] = empty($_GET['sortbymax']) ? 0 : 1;
            // 分页处理
            $p = isset($_GET['p']) ? intval($_GET['p']) : 0;
            if( isset($_GET['pn']) && in_array( intval($_GET['pn']) , array(20,40,80) ) )
            {
                $pn = intval($_GET['pn']);
            }
            else 
            {
                $pn = 20;
            }

            if( empty($aResult) )
            {
            	
                if( $bSelf == FALSE && $bAllChildren == FALSE )
                {
                    $aResult = $oUser->getChildList( $iSearchId, '', $sAndWhere, $sOrderBy, $pn, $p, $bAllChildren );
                }
                else 
                {
                    $aResult = $oUser->getChildrenList( $iSearchId, '', $sAndWhere, $sOrderBy, $pn, $p, 
                                                    $bAllChildren, $bSelf, $iUserId );
                }
                
            	//$aResult = $oUser->getChildList( $iSearchId, '', $sAndWhere, $sOrderBy, $pn, $p, $bAllChildren );
                
            }
            $oPager = new pages( $aResult['affects'], $pn, 0);
            $aPageData = array(
                                'totalresult'   => $aResult['affects'],
                                'nowpage'       => $oPager->get('iCurrentPage'),
                                'totalpage'     => $oPager->get('iTotalPages'),
                                'perpage'       => $oPager->get('iPerPageCount'),
                                'first'         => $oPager->getFirstPageMsg(),
                                'pre'           => $oPager->getPrePageMsg(),
                                'next'          => $oPager->getNextPageMsg(),
                                'last'          => $oPager->getLastPageMsg(),
                                //'select'        => $oPager->getHtmlInputBox(),
                                'turn'          => $oPager->getPageBar()
                        );
            $aTempUserLevel = array( "一", "二", "三", "四", "五", "六", "七", "八", "九", "十" );
            if( $bSelf )
            {
                if( $aResult['self']['usertype'] == 1 )
                {
                    $sTempStr = preg_replace("/^[\\d,]*".$iUserId."[,]?/i", "", $aResult['self']['parenttree'], 1);
                    if( empty($sTempStr) )
                    {
                        $aResult['self']['groupname'] = "一级代理";
                    }
                    else 
                    {
                        $aTempArr = explode( ",", $sTempStr );
                        $aResult['self']['groupname'] = isset($aTempUserLevel[count($aTempArr)]) ? 
                                                    $aTempUserLevel[count($aTempArr)]."级代理" : "代理用户";
                    }
                }
                else 
                {
                    $aResult['self']['groupname'] = "会员用户";
                }
                $GLOBALS['oView']->assign( 'selfdata', $aResult['self'] );
            }
            //用户数据处理
            foreach( $aResult['results'] as & $v )
            {
                if( $v['usertype'] == 1 )
                {
                    $sTempStr = preg_replace("/^[\\d,]*".$iUserId."[,]?/i", "", $v['parenttree'],1);
                    if( empty($sTempStr) )
                    {
                        $v['groupname'] = "一级代理";
                        continue;
                    }
                    $aTempArr = explode( ",", $sTempStr );
                    $v['groupname'] = isset($aTempUserLevel[count($aTempArr)]) ? 
                                      $aTempUserLevel[count($aTempArr)]."级代理" : "代理用户";
                }
                else
                {
                    $v['groupname'] = "会员用户";
                }
            }
            $GLOBALS['oView']->assign( 'searchdata',    $aFields );
            $GLOBALS['oView']->assign( 'page',          $aPageData );
            $GLOBALS['oView']->assign( 'users',         $aResult['results'] );
            $GLOBALS['oView']->assign( 'userid',        $iUserId );
            $GLOBALS['oView']->assign( 'sys_header_title', TRUE );//不显示标题
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display("user_userlist_show.html");
            EXIT;
        }
    }



    /**
     * 上级修改下级用户
     * URL:./index.php?controller=user&action=upEdituser
     * @author JAMES
     */
    function actionUpEditUser()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            if( isset($_POST['flag']) && $_POST['flag']=='method' )
            {//如果AJAX调用则报错
                die("error");
            }
            if( isset($_POST['flag']) && $_POST['flag']=='insert' )
            {//如果AJAX调用则报错
                die("数据错误，请刷新页面重试");
            }
            $aLink[0] = array('url'=>url('user','list',array('frame'=>'show')),'title'=>'用户列表');
            sysMsg( "非法操作", 2, $aLink );
        }
        $iUid    = intval($_REQUEST['uid']);//要设置的用户ID
        $oUser   = new model_user();
        $iUserId = $_SESSION['userid']; //操作者ID
        $iPid    = $oUser->getParentId( $iUid );//等同于操作者ID
        $aLink[0]= array('url'=>url('user','list',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表');
        // 如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                if( isset($_POST['flag']) && $_POST['flag']=='method' )
                {//如果AJAX调用则报错
                    die("error");
                }
                if( isset($_POST['flag']) && $_POST['flag']=='insert' )
                {//如果AJAX调用则报错
                    die("数据错误，请刷新页面重试");
                }
               sysMsg( "操作失败", 2, $aLink );
            }
        }
        if( $iPid != $iUserId )
        {//如果不是直接上级，则不允许操作
            if( isset($_POST['flag']) && $_POST['flag']=='method' )
            {//如果AJAX调用则报错
                die("error");
            }
            if( isset($_POST['flag']) && $_POST['flag']=='insert' )
            {//如果AJAX调用则报错
                die("没有操作权限");
            }
            sysMsg( "没有操作权限", 2, $aLink );
        }
        if( !isset($_POST['flag']) || ($_POST['flag']!='insert' && $_POST['flag']!='method') )
        {
            if( !empty($_REQUEST['lotteryid']) && is_numeric($_REQUEST['lotteryid']) )
            {//默认显示的彩种
                $iLotteryId = intval($_REQUEST['lotteryid']) > 0 ? intval($_REQUEST['lotteryid']) : 0;
            }
            else 
            {
                $iLotteryId = 0;
            }
            $bIsTop = $oUser->isTopProxy( $iUserId );
            //获取操作者彩种信息
            /* @var @oLottery model_lottery */
            $oLottery       = A::singleton("model_lottery");
            $aLotteryData   = $oLottery->getLotteryByUser( $iUserId, $bIsTop );
            if( empty($aLotteryData) )
            {//没有任何彩种
                sysMsg( "操作失败", 2, $aLink );
            }
            //获取被操作用户信息
            $aUserData = $oUser->getUsersProfile( "ut.`username`,ut.`nickname`", '', " AND ut.`userid`='".$iUid."'" );
            $aUserData['limitbons'] = getConfigValue( 'limitbonus', 100000 );
            $aUserData['userid']    = $iUid;
            $aUserData['usertype']  = $bIsTop ? 1 : 2;//总代为1，普代为2
            $GLOBALS['oView']->assign( 'lotteryid', $iLotteryId );
            $GLOBALS['oView']->assign( 'lotterys',  $aLotteryData );
            $GLOBALS['oView']->assign( 'user',      $aUserData );
            $GLOBALS['oView']->assign( 'ur_here',   '用户修改' );
            $GLOBALS['oView']->assign( 'actionlink',  
                    array( 'href'=>url('user','list',array('frame'=>'show','uid'=>$iPid)), 'text'=>'用户列表' ) );
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_upedituser.html" );
            EXIT;
        }
        elseif( $_POST['flag'] == 'method' )
        {
            if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) )
            {//参数错误
                die("error");
            }
            $iLotteryId  = intval($_POST['lotteryid']);
            //获取操作者用户在此彩种下的奖金组信息
            $bIsTop      = $oUser->isTopProxy($iUserId);
            /* @var $oUserPrize model_userprizegroup */
            $oUserPrize  = A::singleton("model_userprizegroup");
            $aUserPrizes = $oUserPrize->getUserPrizeGroupList( $iUserId, $bIsTop, "", 
                                         " AND upg.`lotteryid`='".$iLotteryId."' " );
            if( empty($aUserPrizes) )
            {//如果没有任何信息
                die("empty");
            }
            $aPrizeData = array();
            foreach( $aUserPrizes as $v )
            {
                $v['userpoint'] *= 100;
                $v['nocount']    = unserialize($v['nocount']);
                $aPrizeData[$v['userpgid']]['pgid']     = $v['userpgid'];
                $aPrizeData[$v['userpgid']]['title']    = $v['title'];
                $aPrizeData[$v['userpgid']]['method'][$v['methodid']][] = $v;
            }
            $aUserPrizes = array();
            foreach( $aPrizeData as $v )
            {
                $aUserPrizes[] = $v;
            }
            //获取被操作者在此彩种下的返点设置
            /* @var $oUserMethodSet model_usermethodset */
            $oUserMethodSet = A::singleton("model_usermethodset");
            $sFields = "ums.`methodid`,ums.`userpoint`,ums.`isclose`,ums.`prizegroupid`";
            $aUserSet = $oUserMethodSet->getUserSet( $iUid, $iLotteryId, $sFields, 
                                                        " AND upg.`status`='1' AND ums.`isclose`='0' " );
            $aPrizeData = array();
            foreach( $aUserSet as $v )
            {
                $v['userpoint'] *= 100;
                $aPrizeData[$v['prizegroupid']]['pgid']     = $v['prizegroupid'];
                $aPrizeData[$v['prizegroupid']]['method'][] = $v;
            }
            $aUserSet = array();
            foreach( $aPrizeData as $v )
            {
                $aUserSet[] = $v;
            }
            //获取被操作者的直接下级中，每个玩法的返点的最大设置
            $aUserChildSet = $oUserMethodSet->getUserChildMaxSet( $iUid, $iLotteryId );
            $aPrizeData = array();
            foreach( $aUserChildSet as $v )
            {
                $v['maxuserpoint'] *= 100;
                $aPrizeData[$v['methodid']] = $v['maxuserpoint'];
            }
            $aUserChildSet = $aPrizeData;
            $aPrizeData = array( 'prizes'=>$aUserPrizes, 'userset'=>$aUserSet, 'childset'=>$aUserChildSet );
            $aPrizeData = array($aPrizeData);
            echo json_encode($aPrizeData);
            EXIT;
        }
        elseif( $_POST['flag'] == 'insert' )
        {
            if( empty($_POST['lotteryid']) || !is_numeric($_POST['lotteryid']) )
            {//参数错误
                die("数据错误，请刷新页面重试");
            }
            $iLotteryId  = intval($_POST['lotteryid']);
            if( empty($_POST['prizegroup']) || !is_numeric($_POST['prizegroup']) )
            {//参数错误
                die("请选择奖金组");
            }
            $iPrizeGroup = intval($_POST['prizegroup']);
            $aMethodIds  = array();//初始化设置
            if( !empty($_POST['method']) && is_array($_POST['method']) )
            {//如果有设置
                foreach( $_POST['method'] as $v )
                {
                    $iMethodId = intval($v);
                    if( !isset($_POST['point_'.$iMethodId]) || !isset($_POST['maxpoint_'.$iMethodId])
                        || !is_numeric($_POST['point_'.$iMethodId]) || !is_numeric($_POST['maxpoint_'.$iMethodId])
                        || $_POST['point_'.$iMethodId] < 0 || $_POST['maxpoint_'.$iMethodId] < 0 )
                    {
                        die("返点设置错误，请检查");
                    }
                    $iPoint    = round(floatval($_POST['point_'.$iMethodId]), 1)/100;
                    $iMaxPoint = round(floatval($_POST['maxpoint_'.$iMethodId]), 1)/100;
                    $iMinPoint = round(floatval($_POST['minpoint_'.$iMethodId]), 1)/100;
                    if( $iPoint > $iMaxPoint || $iPoint < $iMinPoint )
                    {//返点超出最大值
                        die("返点设置错误，请检查");
                    }
                    $aMethodIds[] = array(
                                       'userid'       => $iUid,
                                       'methodid'     => $iMethodId,
                                       'prizegroupid' => $iPrizeGroup,
                                       'userpoint'    => $iPoint,
                                       'isclose'      => 0
                                    );
                }
            }
            /* @var $oUserMethodSet model_usermethodset */
            $oUserMethodSet = A::singleton("model_usermethodset");
            $aResult = $oUserMethodSet->setUserPoint( $iUid, $iLotteryId, $aMethodIds );
            if( $aResult === -11 )
            {
                die("返点设置不合理，返点设置低于了该用户对其下级设置");
            }
            elseif( $aResult == FALSE )
            {
                die("操作失败，请重试");
            }
            else
            {
                die("true");
            }
        }
        else
        {
            EXIT;
        }
    }



    /**
     * 上级查看下级的团队余额
     * URL:./index.php?controller=user&action=userteam
     * @author JAMES
     */
    function actionUserTeam()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            $aLink = array( array('url'=>url('user','list',array('frame'=>'show')),'title'=>'用户列表') );
            sysMsg( "非法操作", 2, $aLink );
        }
        $oUser   = A::singleton("model_user");
        $iUid    = intval($_REQUEST['uid']);
        $iPid    = $oUser->getParentId( $iUid );
        $aLink   = array( array('url'=>url('user','list',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表') );
        $iUserId = $_SESSION['userid'];
        if( $_SESSION['usertype'] == 2 )
        {
            //如果为总代管理员，则当前用户调整到其总代ID
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2, $aLink );
            }
            //检测是否为销售管理员
            if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
            {//是销售管理员
                if( !empty($iUid) )
                {//检测是否在自己范围之内
                    if( FALSE == $oUser->isInAdminSale( $iUid, $_SESSION['userid']) )
                    {
                        sysMsg( "没有权限", 2 );
                    }
                }
            }
        }
        //检查是否有权限[必须是在自己树下的用户才能查看]
        if( FALSE == $oUser->isParent($iUid, $iUserId) )
        {
            sysMsg( "操作失败", 2, $aLink );
        }
        //获取团队余额
        $aUserInfo  = $oUser->getUsersProfile( "ut.`username`,ut.`nickname`", '',
                        " AND ut.`userid`='".$iUid."'" );
        $mTeamMoney = $oUser->getTeamBank( $iUid );
        if( $mTeamMoney === FALSE )
        {//获取失败
            $mTeamMoney = "获取数据失败";
        }
        $GLOBALS['oView']->assign( 'user',      $aUserInfo );
        $GLOBALS['oView']->assign( 'teammoney', $mTeamMoney );
        $GLOBALS['oView']->assign( 'ur_here',   '团队余额' );
        $GLOBALS['oView']->assign( 'actionlink',  
                    array( 'href'=>url('user','list',array('frame'=>'show','uid'=>$iPid)), 'text'=>'用户列表' ) );
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display( "user_userteam.html" );
        EXIT;
    }



    /**
     * 我的团队
     * URL:./index.php?controller=user&action=team
     * @author JAMES
     */
    function actionTeam()
    {
        $iUserId   = $_SESSION['userid'];
        $sAndWhere = '';
        /* @var $oUser model_user */
        $oUser = A::singleton("model_user");
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            //检测是否为销售管理员
            if( TRUE == $oUser->IsAdminSale( $_SESSION['userid'] ) )
            {//是销售管理员
                //获取分配给该销售管理员的一代ID
                $aProxyid = $oUser->getAdminProxyByUserId( $_SESSION['userid'] );
                if( empty($aProxyid) )
                {//没有分配代理则数据都为空
                    $fTeamMoney = 0.0000;
                }
                else 
                {
                    $iUserId = 0;
                    $sAndWhere = " AND ut.`lvproxyid` IN(" .implode(',',$aProxyid). ")";
                }
            }
        }
        //获取团队余额
        if( !isset($fTeamMoney) )
        {
            $fTeamMoney = $oUser->getTeamBank( $iUserId, $sAndWhere );
            if( $fTeamMoney === FALSE )
            {//获取失败
                $fTeamMoney = "获取数据失败";
            }
        }
        $GLOBALS['oView']->assign( 'username',  $_SESSION['username'] );
        $GLOBALS['oView']->assign( 'nickname',  $_SESSION['nickname'] );
        $GLOBALS['oView']->assign( 'teammoney', $fTeamMoney );
        $GLOBALS['oView']->assign( 'ur_here',     '我的团队' );
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display( "user_team.html" );
        EXIT;
    }



    /**
     * 用户信息查看
     * URL:./index.php?controller=user&action=userinfo
     * @author SAUL
     */
    function actionUserinfo()
    {
        $iUserId = isset($_GET["uid"])&&is_numeric($_GET["uid"]) ? intval($_GET["uid"]) : 0;
        if( $iUserId==0 )
        {
            sysMsg('操作失败:数据错误.',1 );
        }
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
        $aLocation[0] = array("text"=>"用户列表",
            "href"=>url('user','list',array('frame'=>'show','uid'=>$iUserId)));
        $GLOBALS['oView']->assign( "ur_here",   "用户详情" );
        $GLOBALS['oView']->assign( "actionlink",$aLocation[0] );
        /* @var $oUserFund model_userfund */
        $oUserFund = A::singleton("model_userfund");
        $aUserFund = $oUserFund->getFundByUser( $iUserId, '*', 1, FALSE );
        if( empty($aUserFund) )
        {
            sysMsg('操作失败:用户没有激活.', 1, $aLocation);
        }
        $GLOBALS['oView']->assign( 'userinfo',   $aUserFund );
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetList("a.`methodid`,a.`lotteryid`,a.`methodname`,"
                ."a.`level`,a.`nocount`,b.`cnname`", "a.`pid`='0'", "", 0 );
        foreach($aMethod as $i=>&$method )
        {
            $aNocount = @unserialize( $method["nocount"] );
            for( $j=1; $j<=$method["level"]; $j++ )
            {
                $method["name"][$j] = $aNocount[$j]["name"];
            }
            unset($method["nocount"]);
            $aLottery[$method["lotteryid"]]     = $method["cnname"];
            $aMethods[$method["lotteryid"]][]   = $aMethod[$i];
        }
        unset($aMethod);
        foreach($aMethods as $i=>$v)
        {
            $aMethod[$i] = json_encode($v);
        }
        $GLOBALS["oView"]->assign( "aLottery",  $aLottery );//彩种信息
        $GLOBALS["oView"]->assign( "aMethod",   $aMethod );
        $aPg = array();
        $aTempPg = array();
        $aPL = array();
        $aTemp = array_keys($aLottery);
        $iLotteryId = intval($_GET['ltid']) > 0 ? intval($_GET['ltid']) : $aTemp[0];
        if($aUserFund["parentid"]==0)
        {//总代
            /* @var $oUserPg model_userprizegroup */
            $oUserPg = A::singleton("model_userprizegroup");
            $aUserPg = $oUserPg->getUserPrizeGroupList( $iUserId, true, 'upg.*,upl.*' );
            foreach ($aUserPg as $i=>$k)
            {
                if (intval($k["lotteryid"]) === $iLotteryId){
                    $aPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
                }
                $aTempPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
                $aPL[$k["userpgid"]][$k["methodid"]][$k["level"]] = array(
                    "userpoint"=>number_format($k["userpoint"]*100,1),
                    "bonus"=>number_format($k["prize"],2,".",","),
                    "status"=>$k["isclose"]
                );
            }
            $GLOBALS['oView']->assign( "show",  0 );
            $GLOBALS['oView']->assign( "aPg",   $aPg );
            $GLOBALS["oView"]->assign( "aPL",   json_encode($aPL)  );
        }
        else
        { //非总代数据
            /* @var $oUserMethodSet model_usermethodset */
            $oUserMethodSet = A::singleton("model_usermethodset");
            $aUserMethodSet = $oUserMethodSet->getUserMethodPoint($iUserId,
                "m.lotteryid,upl.`level`,upl.`prize`,upl.`userpgid`,ums.*,UPG.title", "" );
            foreach ($aUserMethodSet as $i=>$k)
            {
                if (intval($k["lotteryid"]) === $iLotteryId){
                    $aPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"]; 
                }
                $aTempPg[$k["lotteryid"]][$k["userpgid"]] = $k["title"];
                $aPL[$k["userpgid"]][$k["methodid"]][$k["level"]] = array(
                    "userpoint" => number_format( $k["userpoint"]*100, 1 ),
                    "bonus"     => number_format( $k["prize"],2, ".", "," ),
                    "status"    => $k["isclose"]
                );
            }
            $GLOBALS['oView']->assign( "show",  1 );
            $GLOBALS['oView']->assign( "aPg",   $aPg );
            $GLOBALS["oView"]->assign( "aPL",   json_encode($aPL) );
        }
        $sNewUrl = "/lowproxy/?controller=user&action=userinfo&uid=" . $iUserId;
        //奖金限额获取
        $fBigMoney = getConfigValue( 'limitbonus', '100000' );
        $GLOBALS['oView']->assign( 'bigMoney',  $fBigMoney );
        $GLOBALS['oView']->assign('lotid',$iLotteryId);
        $GLOBALS['oView']->assign('newurl',$sNewUrl);
        $oUserFund->assignSysInfo();
        $GLOBALS['oView']->display("user_userinfo.html");
        EXIT;
    }
}
?>