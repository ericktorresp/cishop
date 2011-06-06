<?php
/**
 * 文件 : /_app/controller/user.php
 * 功能 : 用户信息的一些基本控制器
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
 * @package   passport
 */

class controller_user extends basecontroller 
{
    /**
     * 增加用户
     */
    function actionAddUser()
    {
        $oUser   = new model_user();
        $iUserId = intval($_SESSION['userid']);
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        $sFiled    = " u.`addcount`,u.`authadd`,ut.`istester` ";
        $sAndWhere = " AND ut.`isdeleted`='0' AND ut.`userid`='" .$iUserId. "' AND ut.`usertype`='1' ";
        $aSelf     = $oUser->getUsersProfile( $sFiled, '', $sAndWhere, FALSE );
        if( $aSelf['authadd'] != 1 )
        {
            sysMsg( "没有权限", 2 );
        }
        if( $aSelf['addcount'] < 1 )
        {
            sysMsg( "没有可开用户数额", 2 );
        }
        if( empty($_POST['flag']) || $_POST['flag']!='insert' )
        {
            $GLOBALS['oView']->assign( 'ur_here','增加用户');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_adduser.html", "adduser" );
            exit;
        }
        else
        {
            if( empty($_POST['username']) || empty($_POST['userpass']) || empty($_POST['nickname']) )
            {
                sysMsg( "请填写完整资料" );
            }
            //用户名，密码，呢称规则检查
            if( FALSE == model_user::checkUserName($_POST['username']) )
            {//检查用户名合法性
                sysMsg( "登陆帐户名不合法" );
            }
            if( FALSE == model_user::checkUserPass($_POST['userpass']) )
            {//检查密码合法性
                sysMsg( "登陆密码不合法" );
            }
            if( FALSE == model_user::checkNickName($_POST['nickname']) )
            {//检查昵称合法性
                sysMsg( "昵称不合法" );
            }
            $aData              = array();
            $aData['username']  = $_POST['username'];
            $aData['loginpwd']  = $_POST['userpass'];
            $aData['nickname']  = $_POST['nickname'];
            if( empty($_POST['usertype']) )
            { // 会员
                $aData['usertype'] = 0;
            }
            else
            { // 代理
                $aData['usertype'] = 1;
            }
            // 获取用户组列--支持特殊组
            $oGroup  = new model_usergroup();
            $aGroups = $oGroup->getGroupID( $iUserId );
            if( empty($aGroups) )
            { // 获取组失败
                 sysMsg( "分配用户级别操作失败", 2 );
            }
            
            // 如果是开的代理，则判断是一级代理还是普通代理
            if( $aData['usertype'] == 1 )
            {
                if( $oUser->isTopProxy($iUserId) )
                { // 如果为总代操作，并且开的是代理，则为一级代理
                    $iGroupId = $aGroups[1]; //一代的组ID
                }
                else 
                {
                    $iGroupId = $aGroups[2]; //普代的组ID
                }
            }
            else 
            {
                $iGroupId = $aGroups[3];    //普通用户
            }
            $mResult = $oUser->insertUser( $aData, $iGroupId, $iUserId, TRUE, $aSelf['istester'] );
            if( $mResult == -1 )
            {
                 sysMsg( "该帐户已经存在，请重新输入", 2 );
            }
            /*elseif ( $mResult === -1001 )
            {
                sysMsg( '低频开户成功,高频同步开户失败', 2 );
            }
            elseif ( $mResult === -1002 )
            {
                sysMessage( '低频开户成功,高频同步开户失败:数据冲突,ID可能被占用！', 2 );
            }*/
            elseif( empty($mResult) )
            {
                sysMsg( "操作失败", 2 );
            }
            $aLink = array( array('url'=>url('user','adduser'),  'title'=>'增加用户'),
                            array('url'=>url('user','userlist'), 'title'=>'用户列表') );
            // 开始同步上级奖金组
        	// 总代开一级代理时，仍采用现有方式，只有开出的不是一级代理的情况下，才会同步奖金组信息
            if( $mResult > 0 && $oUser->isTopProxy($iUserId) === false )
            { 
                // 获取上级用户开通的频道
                $oChannel = A::singleton("model_userchannel");
        		$aChannel = $oChannel->getUserChannelList( $iUserId );
        		if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) ){
        			$aTranfer = array();
        			$aTranfer['iUserId'] = $mResult; // 新开用户id
        			$aTranfer['iPid'] = $iUserId; // 操作者id
        			$sErrorChannel = "";
        			foreach ($aChannel[0] as $k => $v){ // 循环调用开通频道的API程序，实现奖金组同步
        				$oChannelApi = new channelapi( $v['id'], 'syncPrizeGroup', TRUE );
			            $oChannelApi->setTimeOut(15);            // 整个转账过程的超时时间, 可能需要微调
			            $oChannelApi->sendRequest( $aTranfer );  // 发送转账请求给调度器
			            $mAnswers = $oChannelApi->getDatas();    // 获取转账 API 返回的结果
			            if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
			            {
			                if ($v['id'] == 1){
			                	$sErrorChannel .= "低频,";
			                }
			                if ($v['id'] == 4){
			                	$sErrorChannel .= "高频,";
			                }
			                // 记录下失败的操作，等待后续处理
			                $oErrorDeal = new model_errordeal();
			                $oErrorDeal->channel = $v['id'];
			                $oErrorDeal->parentId = $aTranfer['iPid'];
			                $oErrorDeal->parentName = $_SESSION['username'];
			                $oErrorDeal->childId = $aTranfer['iUserId'];
			                $oErrorDeal->childName = $aData['username'];
			                $bResult = $oErrorDeal->insertErrorInfo();
			            }
        			}
        			if (empty($sErrorChannel)){
        				sysMsg( "操作成功 请转至频道设置返点", 1, $aLink );
        			} else {
        				sysMsg( "抱歉, " . mb_substr($sErrorChannel, 0, -1, 'utf-8') . "频道设置用户'奖金'和'返点'失败，请在相应频道中手工开通游戏并设置'返点'，或者等待管理员为您处理！", 2 );
        			}
        		} else {
        			sysMsg( "操作成功 请在相应频道中设置'资金'和'返点'", 1, $aLink );
        		}
            } else {
            	sysMsg( "操作成功 请在相应频道中设置'资金'和'返点'", 1, $aLink );
            }
        }
    }



    /**
     * 用户列表（大厅)
     */
    function actionUserList()
    {
        if( empty($_GET['frame']) || ($_GET['frame']!='menu' && $_GET['frame']!='show') )
        {
            $GLOBALS['oView']->display( "user_userlist_main.html", "userlistmain" );
            exit;
        }
        elseif( $_GET['frame'] == 'menu' )
        { // 动态加载左侧用户列表
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
            $oUser      = new model_user();
            $iUserId    = $_SESSION['userid'];
            // 如果为总代管理员，则当前用户调整到其总代ID
            if( $_SESSION['usertype'] == 2 )
            {
                $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
                if( empty($iUserId) )
                {
                    if( !empty($iUid) )
                    {
                        echo "{error:'fail'}";
                    }
                    else 
                    {
                        echo "获取列表失败";
                    }
                    exit;
                }
                // 检测是否为销售管理员
                if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
                { // 是销售管理员
                    if( !empty($iUid) )
                    { // 检测是否在自己范围之内
                        if( FALSE == $oUser->isInAdminSale($iUid, $_SESSION['userid']) )
                        {
                            echo "{error:'empty'}";
                            exit;
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
                $iUserId = empty($iUid) ? $iUserId : $iUid;
                $aData = $oUser->getChildListID( $iUserId, FALSE, $sAndWhere, " ORDER BY a.`username` " );
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
                exit;
            }
            $GLOBALS['oView']->assign('users',$aData);
            $GLOBALS['oView']->display("user_userlist_menu.html");
            exit;
        }
        elseif( $_GET['frame'] == 'show' )
        {
            $GLOBALS['oView']->display( "load.html", "loading" );
            @ob_flush();
            flush();
            $oUser        = new model_user();
            $iUserId      = $_SESSION['userid'];
            $sAndWhere    = '';
            $sOrderBy     = ' ut.`username` ASC ';
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
            //获取用户的分组
            /*$oGroup = new model_usergroup();
            $aGroups = $oGroup->getGroupByUser($iUserId);
            $GLOBALS['oView']->assign('groups',$aGroups);
            */
            $GLOBALS['oView']->assign( 'curruser', $iUserId );
            $iSearchId = empty($iUid) ? $iUserId : $iUid;
            // 搜索处理
            $aFields = array(
                                'username'      => "",
                                'usergroup'     => 0,
                                'bank_min'      => "",
                                'bank_max'      => "",
                                'regtime_min'   => "",
                                'regtime_max'   => "",
                                'sortby'        => "",
                                'sortbymax'     => 0
            );
           /* Task No.YW-YH-201001-005  start */
            if ( empty($_GET['username']) && !empty($_GET['usergroup']) )
            {
            	sysMsg( "搜索条件不足" );
            }
           	if ( !empty($_GET['username']) || ( empty($_GET['usergroup']) && !empty($_GET['username']) )  || ( is_numeric($_GET['usergroup']) && !empty($_GET['username']) ) )
            { // 用户名和用户组组合搜索
                $aFields['username'] = $_GET['username'];
                $aFields['usergroup'] = $_GET['usergroup'];
                // 检测输入合法性
                if( preg_match("/[^0-9a-zA-Z\*]+/i", trim($_GET['username'])) )
                {
                    sysMsg( "请输入合法的用户名" );
                }
                $_GET['username'] = trim(str_replace("*", "%", daddslashes($_GET['username'])));
                $sAndWhere       .= " AND u.`username` like '".$_GET['username']."' ";
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
			/* Task No.YW-YH-201001-005  end */
            /*if( !empty($_GET['usergroup']) && is_numeric($_GET['usergroup']) )
            { // 用户组
                if( intval($_GET['usergroup']) == 1 )
                {
                    $sAndWhere .= " AND ut.`usertype`='1' ";
                }
                else 
                {
                    $sAndWhere .= " AND ut.`usertype`='0' ";
                }
                $bAllChildren = TRUE;
                $aFields['usergroup'] = $_GET['usergroup'];
            }*/
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
            if( !empty($_GET['regtime_min']) )
            { // 注册开始时间
                if( checkDateTime($_GET['regtime_min']) == FALSE )
                {
                    sysMsg( "请输入正确的日期" );
                }
                $sAndWhere .= "AND u.`registertime` >= '".date("Y-m-d H:i:s",strtotime($_GET['regtime_min']))."'";
                $bAllChildren           = TRUE;
                $aFields['regtime_min'] = $_GET['regtime_min'];
            }
            if( !empty($_GET['regtime_max']) )
            { // 注册结束时间
                if( checkDateTime($_GET['regtime_max']) == FALSE )
                {
                    sysMsg( "请输入正确的日期" );
                }
                $sAndWhere .= " AND u.`registertime` <= '".date("Y-m-d H:i:s",strtotime($_GET['regtime_max']))."' ";
                $bAllChildren           = TRUE;
                $aFields['regtime_max'] = $_GET['regtime_max'];
            }
            if( !empty($_GET['sortby']) )
            { // 排序
                if( $_GET['sortby'] == 'bank' )
                {
                    $sOrderBy = " uf.`availablebalance` ";
                }
                elseif( $_GET['sortby'] == 'regtime' )
                {
                    $sOrderBy = " u.`registertime` ";
                }
                else
                {
                	$sOrderBy = " ut.`username` ";
                }
                $aFields['sortby'] = $_GET['sortby'];
                if( $aFields['sortby'] != 'default' )
                {
                    $bAllChildren = TRUE;
                }
            }
            if( !empty($_GET['sortbymax']) && $_GET['sortbymax'] == 1 )
            { // 排序是从大到小还是从小到大
                if(empty($sOrderBy))
                {
                    $sOrderBy .= " ut.`username` DESC ";
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
                $aResult = $oUser->getChildList( $iSearchId, '', $sAndWhere, $sOrderBy, $pn, $p, 
                                                    $bAllChildren, $bSelf, $iUserId );
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
            
      
            //获取在线客服菜单显示定义
            $oLiveChat = A::singleton("model_livecustomer"); //new model_livecustomer();
            //检查操作用户是否具备 在线客服菜单权限 检查操作用户是否已打开在线客服功能
            $bLiveChatPower = $oLiveChat->checkMenuPermisson();
     
            //读取系统参数 是否显示在线客服客户控制菜单
            $oConfig = A::singleton("model_config");
            $iUsrCtrlLiveChatShow = $oConfig->getConfigs('livechat_userctrl_on');
            
            //读取用户是否有 OCS开关菜单权限 模板显示
            $oUserMenu 		= new model_usersession();
        	$bCanCloseOCS 	= $oUserMenu->checkMenuAccess( $_SESSION['userid'], 'user', 'closeocs' );
        	$bCanOpenOCS 	= $oUserMenu->checkMenuAccess( $_SESSION['userid'], 'user', 'openocs' );
             
            $GLOBALS['oView']->assign( 'searchdata', $aFields );
            $GLOBALS['oView']->assign( 'page', $aPageData );
            $GLOBALS['oView']->assign( 'livechatpower', $bLiveChatPower );
            $GLOBALS['oView']->assign( 'scancloseocs', $bCanCloseOCS );
            $GLOBALS['oView']->assign( 'scanopenocs', $bCanOpenOCS );
            $GLOBALS['oView']->assign( 'livechatmenuon', $iUsrCtrlLiveChatShow );
            $GLOBALS['oView']->assign( 'users', $aResult['results'] );
            $GLOBALS['oView']->assign( 'ur_here','用户列表');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display("user_userlist_show.html");
            exit;
        }
    }



    /**
     * 用户充值
     */
    function actionSaveUp()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            sysMsg("非法操作",2);
        }
        
        // 判断是否需要资金密码检查
        $oEmailDeposit = new model_deposit_emaildeposit();
        if ($oEmailDeposit->securityCheck() === false){
            // 资金密码检查
            $oSecurityCon = new controller_security();
            $oSecurityCon->actionCheckPass('user','saveup', true, '/?controller=user&action=saveup&uid=' . $_REQUEST['uid']);
            EXIT;
        }
        
        $iUid       = intval($_REQUEST['uid']);
        $iUserId    = intval($_SESSION['userid']);
        $iAgentId   = 0;    //总代管理员ID初始
        /* @var $oUser model_user */
        $oUser    = A::singleton("model_user");
        // 如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iAgentId = $iUserId;
            $iUserId  = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg("操作失败",2);
            }
        }
        //检验被充值用户是否为充值用户的下级
        if( FALSE == $oUser->isParent($iUid, $iUserId) )
        {
            sysMsg( "操作失败", 2 );
        }
        
        //TODO：并行期充提分期上代码
        //获取操作用户的总代
        $aTempTopUser = $oUser->getTopProxyId( $iUserId, TRUE );
        if( empty($aTempTopUser) )
        {//获取数据失败则退出
            sysMsg( "没有权限", 2 );
        }
        /*$oConfig      = new model_config();
        $sAllowCashCT = $oConfig->getConfigs( 'allowcashct' );
        $sAllowCashCT = empty($sAllowCashCT) ? "" : $sAllowCashCT;
        $aAllowedTopUser = explode( ",", $sAllowCashCT );
        if( !in_array( $aTempTopUser['username'], $aAllowedTopUser ) )
        {//不在指定总代范围内
            sysMsg( "没有权限", 2 );
        }
        unset($oConfig,$sAllowCashCT,$aAllowedTopUser);*/
        
        
        if( empty($_POST['flag']) || ($_POST['flag']!='insert' && $_POST['flag']!='confirm' ) )
        {
            $oUserFund = new model_userfund();
            // 获取自身的帐户资金信息
            $aOwnFund = $oUserFund->getFundByUser( $iUserId );
            if( empty($aOwnFund) )
            {
                sysMsg( "您的资金帐户被其他操作锁定，请稍后再试", 1 );
            }
            $aUserFund = $oUserFund->getFundByUser( $iUid );
            if( empty($aUserFund) )
            {
                sysMsg( "被充值用户的资金帐户被其他操作锁定，请稍后再试", 1 );
            }
            $GLOBALS['oView']->assign( 'userid', $iUid );
            $GLOBALS['oView']->assign( 'ownfund', $aOwnFund );
            $GLOBALS['oView']->assign( 'userfund', $aUserFund );
            $GLOBALS['oView']->assign( 'ur_here','用户充值');
            $oUserFund->assignSysInfo();
            $GLOBALS['oView']->display( "user_usersaveup.html" );
            exit;
        }
        elseif( $_POST['flag'] == 'insert' )
        { // 初次提交
            if( empty($_POST['money']) || !is_numeric($_POST['money']) )
            {
                sysMsg("请输入正确的充值金额");
            }
            //取整数
            $fSaveMoney = intval($_POST['money']);//round(floatval($_POST['money']), 2);
            //判断是否为总代做不同的充值金额限制
            if( $iUserId == $aTempTopUser['userid'] )
            {//当前用户等于其总代的ID，则为总代
                $iLimitMoney = intval(getConfigValue("syszdsavelimit", 3000));
            }
            else
            {//非总代执行充值的限制
                $iLimitMoney = intval(getConfigValue("sysproxysavelimit", 10));
            }
            if( $fSaveMoney < $iLimitMoney )
            {//如果充值金额没有达到要求，则退出
                sysMsg("充值金额不能少于规定的游戏币");
            }
            // 读取被充值用户的基本信息
            /* @var $oUser model_user */
            $oUser = A::singleton( "model_user" );
            $aData = $oUser->getUserInfo( $iUid, array('userid','username') );
            if( empty($aData) )
            {
                sysMsg("连接超时，请重试",1);
            }
            $GLOBALS['oView']->assign( 'user', $aData );
            $GLOBALS['oView']->assign( 'money', $fSaveMoney );
            $GLOBALS['oView']->display( "user_usersaveup2.html" );
            exit;
        }
        elseif( $_POST['flag'] == 'confirm' )
        { // 确认提交
            // 获取上级ID
            /* @var $oUser model_user */
            $oUser  = A::singleton("model_user");
            $iPid   = $oUser->getParentId( $iUid );
            unset( $oUser );
            $aLink = array( array('url'=>url('user','userlist',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表') );
            if( empty($_POST['money']) || !is_numeric($_POST['money']) )
            {
                sysMsg( "非法操作", 2, $aLink );
            }
            $fMoney    = intval($_POST['money']);//round( floatval($_POST['money']), 2 );
            //判断是否为总代做不同的充值金额限制
            if( $iUserId == $aTempTopUser['userid'] )
            {//当前用户等于其总代的ID，则为总代
                $iLimitMoney = intval(getConfigValue("syszdsavelimit", 3000));
            }
            else
            {//非总代执行充值的限制
                $iLimitMoney = intval(getConfigValue("sysproxysavelimit", 10));
            }
            if( $fMoney < $iLimitMoney )
            {//如果充值金额没有达到要求，则退出
                sysMsg( "充值金额不能少于规定的游戏币" );
            }
            //进行充值
            $oUserFund = new model_userfund();
            $mResult   = $oUserFund->saveUp( $iUserId, $iUid, $fMoney, $iAgentId );
            if( FALSE == $mResult )
            {
                sysMsg( "充值失败", 2, $aLink );
            }
            elseif( $mResult === -1 )
            {
                sysMsg( "您的资金帐户或者用户资金帐户被其他操作占用，请稍后再试", 2, $aLink );
            }
            elseif( $mResult === -1009 )
            {
                sysMsg( "对不起，您的余额不足", 2, $aLink );
            }
            elseif( $mResult === -3 )
            {
                sysMsg( "充值成功,但是资金帐户意外被锁，请联系管理员", 1, $aLink );
            }
            elseif( $mResult === TRUE )
            {
                sysMsg( "充值成功", 1, $aLink );
            }
            else
            {
                sysMsg( "充值失败", 2, $aLink );
            }
        }
    }



    /**
     * 上级修改下级用户
     */
    function actionUpEditUser()
    {
    	$aLink = array( array('url'=>url('user','userlist',array('frame'=>'show')),'title'=>'用户列表') );
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            sysMsg( "非法操作", 2, $aLink );
        }
        $iUid    = intval($_REQUEST['uid']);
        $oUser   = A::singleton("model_user");
        $iUserId = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2, $aLink );
            }
        }
        //检查是否有权限[必须是在自己树下的用户才能修改]
        if( FALSE == $oUser->isParent($iUid,$iUserId) )
        {
            sysMsg( "操作失败", 2, $aLink );
        }
        $iPid   = $oUser->getParentId( $iUid );
        $aLink  = array( array('url'=>url('user','userlist',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表') );
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            //获取用户信息
            $aUserInfo = $oUser->getUserExtentdInfo( $iUid );
            if( FALSE == $aUserInfo )
            {
                sysMsg( "用户信息获取失败", 2, $aLink );
            }
            if( $aUserInfo['isspecial'] == 0 )
            {
                $aUserInfo['extendgroupid'] = $aUserInfo['groupid'];
            }
            else 
            {
                $aUserInfo['extendgroupid'] = $aUserInfo['isspecial'];
            }
            $GLOBALS['oView']->assign( 'user', $aUserInfo );
            $GLOBALS['oView']->assign( 'ur_here','修改用户');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_upedituser.html" );
            exit;
        }
        else
        {
            if( empty($_POST['extendgroupid']) || !is_numeric($_POST['extendgroupid'])
                //|| empty($_POST['usertype']) || !is_numeric($_POST['usertype']) 
               )
            {
                sysMsg( "用户信息获取失败", 2, $aLink );
            }
            if( empty($_POST['nickname']) || FALSE == model_user::checkNickName($_POST['nickname']) )
            {//检查昵称合法性
                sysMsg( "昵称不合法" );
            }
            $sOldNickName   = empty($_POST['oldnickname']) ? '' : $_POST['oldnickname'];
            $sNickName      = $_POST['nickname'];
            $iExtendGroupId = intval( $_POST['extendgroupid'] );
            $iUserType      = $iExtendGroupId;//intval( $_POST['usertype'] );
            if( $iExtendGroupId == $iUserType && $sOldNickName == $sNickName )
            {//如果没有做任何改动则直接成功返回
                sysMsg( "操作成功", 1, $aLink );
            }
            if( $iExtendGroupId != $iUserType )
            {//做了级别修改
                $aData = array();
                if( $iUserType == 4 )
                {//会员
                    $aData['usertype'] = 0;
                }
                else
                {//代理
                    $aData['usertype'] = 1;
                }
                //获取用户组列--支持特殊组
                $oGroup  = new model_usergroup();
                $aGroups = $oGroup->getGroupID( $iUserId );
                if( empty($aGroups) )
                {//获取组失败
                    sysMsg( "分配用户级别操作失败", 2 );
                }
                
                if( empty($aGroups[$iUserType-1]) )
                {
                    sysMsg( "分配用户级别操作失败", 2 );
                }
                //如果是开的代理，则判断是一级代理还是普通代理
                if( $aData['usertype'] == 1 )
                {
                    if( $oUser->isTopProxy($iUserId) )
                    {//如果为总代操作，并且开的是代理，则为一级代理
                        $iGroupId = $aGroups[1]; //一代的组ID
                    }
                    else 
                    {
                        $iGroupId = $aGroups[2]; //普代的组ID
                    }
                }
                else 
                {
                    $iGroupId = $aGroups[3];    //普通用户
                }
                $mResult = $oUser->updateUserLevel( $iUid, $aData['usertype'], $iGroupId );
                if( $mResult === FALSE )
                {
                    sysMsg( "修改用户信息失败", 2 );
                }
                if( $mResult === -1 )
                {
                    sysMsg( "该用户下还有下级用户，不能调整到用户", 2 );
                }
            }
            if( $sOldNickName != $sNickName )
            {
                $mResult = $oUser->updateUser( $iUid, array('nickname'=>$sNickName) );
                if( empty($mResult) )
                {
                    sysMsg( "修改用户信息失败", 2 );
                }
            }
            sysMsg( "操作成功", 1, $aLink );
        }
    }



    /**
     * 上级修改下级密码
     */
    function actionUpUpdatePass()
    {
        $aLink = array( array('url'=>url('user','userlist',array('frame'=>'show')),'title'=>'用户列表') );
        sysMsg( "非法操作", 2, $aLink ); //限制使用
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            sysMsg( "非法操作", 2, $aLink );
        }
        $iUid    = intval($_REQUEST['uid']);
        /* @var $oUser model_user */
        $oUser   = A::singleton( "model_user" );
        $iUserId = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2, $aLink );
            }
        }
        //检查是否有权限[必须是在自己树下的用户才能修改]
        if( FALSE == $oUser->isParent($iUid, $iUserId) )
        {
            sysMsg( "操作失败", 2, $aLink );
        }
        $iPid   = $oUser->getParentId( $iUid );
        $aLink  = array( array('url'=>url('user','userlist',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表') );
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            //获取用户信息
            $aUserInfo = $oUser->getUserInfo( $iUid, array('userid','username','nickname') );
            if( FALSE == $aUserInfo )
            {
                sysMsg( "用户信息获取失败", 2, $aLink );
            }
            $GLOBALS['oView']->assign( 'user', $aUserInfo );
            $GLOBALS['oView']->assign( 'ur_here','上级修改下级密码');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_upupdatepass.html" );
            exit;
        }
        else
        {
            if( empty($_POST['userpass']) )
            {
                sysMsg( "请输入密码" );
            }
            if( FALSE == model_user::checkUserPass($_POST['userpass']) )
            {//检查密码合法性
                sysMsg( "登陆密码不合法" );
            }
            $mResult = $oUser->changePassWord( $iUid, $_POST['userpass'] );
            if( $mResult > 0 )
            {
                sysMsg( "修改密码成功", 1, $aLink );
            }
            else 
            {
                sysMsg( "修改密码失败", 2, $aLink );
            }
        }
    }



    /**
     * 上级查看下级的团队余额
     */
    function actionUpUserTeam()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            $aLink = array( array('url'=>url('user','userlist',array('frame'=>'show')),'title'=>'用户列表') );
            sysMsg( "非法操作", 2, $aLink );
        }
        /* @var $oUser model_user */
        $oUser   = A::singleton("model_user");
        $iUid    = intval($_REQUEST['uid']);
        $iPid    = $oUser->getParentId( $iUid );
        $aLink   = array(array('url'=>url('user','userlist',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表'));
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
        $aUserInfo  = $oUser->getUserInfo( $iUid, array('username','addcount') );
        $mTeamMoney = $oUser->getTeamBank( $iUid );
        if( $mTeamMoney === FALSE )
        {//获取失败
            $mTeamMoney = "获取数据失败";
        }
        $GLOBALS['oView']->assign( 'user', $aUserInfo );
        $GLOBALS['oView']->assign( 'teammoney', $mTeamMoney );
        $GLOBALS['oView']->assign( 'ur_here','团队余额');
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display( "user_upuserteam.html" );
        exit;
    }



    /**
     * 冻结用户
     */
    function actionFrozenUser()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            $aLink = array( array('url'=>url('user','userlist',array('frame'=>'show')),'title'=>'用户列表') );
            sysMsg( "非法操作", 2, $aLink );
        }
        $iUid   = intval($_REQUEST['uid']);
        /* @var $oUser model_user */
        $oUser   = A::singleton("model_user");
        $iUserId = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg("操作失败");
            }
        }
        if( !$oUser->isParent($iUid, $iUserId) )
        {
           sysMsg( "对不起，你没有权限", 2 );
        }
        $iPid   = $oUser->getParentId( $iUid );
        $aLink  = array( array('url'=>url('user','userlist',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表') );
        if( empty($_POST['flag']) || $_POST['flag']!="update" )
        {
            $aUserInfo = $oUser->getUserInfo( $iUid, array('username','userid') );
            $GLOBALS['oView']->assign( 'user', $aUserInfo );
            $GLOBALS['oView']->assign( 'ur_here','冻结用户');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_frozenuser.html", "frozenuser" );
            exit;
        }
        else
        {
            if( empty($_POST['frozentype']) )
            {
                sysMsg( "请选择冻结类型" );
            }
            if( $_POST['frozentype'] == 'all' )
            {//冻结所有
                $mResult = $oUser->frozenUser( $iUid, $iUserId, 1, 1, TRUE );
            }
            else 
            {
                $mResult = $oUser->frozenUser( $iUid, $iUserId );
            }
            if( empty($mResult) )
            {
                sysMsg( "操作失败" );
            }
            if( $mResult === -1 )
            {
                sysMsg( "对不起，你没有权限", 2, $aLink );
            }
            sysMsg( "冻结成功", 1, $aLink );
        }
    }



    /**
     * 解冻用户
     */
    function actionUnFrozenUser()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            $aLink = array( array('url'=>url('user','userlist',array('frame'=>'show')),'title'=>'用户列表') );
            sysMsg( "非法操作", 2, $aLink );
        }
        $iUid   = intval( $_REQUEST['uid'] );
        $iUserId = $_SESSION['userid'];
        /* @var $oUser model_user */
        $oUser  = A::singleton("model_user");
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败" );
            }
        }
        if( !$oUser->isParent($iUid, $iUserId) )
        {
           sysMsg( "对不起，你没有权限", 2 );
        }
        $iPid   = $oUser->getParentId( $iUid );
        $aLink  = array( array('url'=>url('user','userlist',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表') );
        if( empty($_POST['flag']) || $_POST['flag']!="update" )
        {
            $aUserInfo = $oUser->getUserInfo( $iUid, array('username', 'userid') );
            $GLOBALS['oView']->assign( 'user', $aUserInfo );
            $GLOBALS['oView']->assign( 'ur_here','解冻用户');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_unfrozenuser.html", "unfrozenuser" );
            exit;
        }
        else
        {
            if( empty($_POST['frozentype']) )
            {
                sysMsg( "请选择冻结类型" );
            }
//            $iUserId = $_SESSION['userid'];
            if( $_POST['frozentype'] == 'all' )
            {//解冻所有
                $mResult = $oUser->unFrozenUser( $iUid, $iUserId, 1, TRUE );
            }
            else 
            {
                $mResult = $oUser->unFrozenUser( $iUid, $iUserId );
            }
            if( empty($mResult) )
            {
                sysMsg( "操作失败" );
            }
            if( $mResult === -1 )
            {
                sysMsg( "对不起，你没有权限", 2, $aLink );
            }
            sysMsg("解冻成功", 1, $aLink );
        }
    }



    /**
     * 用户提现（上级操作下级提现）
     */
    function actionUpWithdraw()
    {
        if( empty($_REQUEST['uid']) || !is_numeric($_REQUEST['uid']) )
        {
            $aLink = array( array('url'=>url('user','userlist',array('frame'=>'show')),'title'=>'用户列表') );
            sysMsg( "非法操作", 2, $aLink );
        }
        $iUid      = intval($_REQUEST['uid']);
        $iUserId   = intval($_SESSION['userid']);
        $iAgentId  = 0;	//总代管理员ID初始
        /* @var $oUser model_user */
        $oUser     = A::singleton("model_user");
        $iPid      = $oUser->getParentId( $iUid );
        $aLink     = array( array('url'=>url('user','userlist',array('frame'=>'show','uid'=>$iPid)),'title'=>'用户列表') );
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iAgentId   = $iUserId;
            $iUserId    = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2, $aLink );
            }
        }
        
        //TODO：并行期充提分期上代码
        //获取操作用户的总代
        $aTempTopUser = $oUser->getTopProxyId( $iUserId, TRUE );
        if( empty($aTempTopUser) )
        {//获取数据失败则退出
            sysMsg( "没有权限", 2 );
        }
        /*$oConfig      = new model_config();
        $sAllowCashCT = $oConfig->getConfigs( 'allowcashct' );
        $sAllowCashCT = empty($sAllowCashCT) ? "" : $sAllowCashCT;
        $aAllowedTopUser = explode( ",", $sAllowCashCT );
        if( !in_array( $aTempTopUser['username'], $aAllowedTopUser ) )
        {//不在指定总代范围内
            sysMsg( "没有权限", 2 );
        }
        unset($oConfig,$sAllowCashCT,$aAllowedTopUser);*/
        
        
        //判断是否有提现操作权限
        if( empty($iPid) || intval($iPid) != $iUserId )
        {
            sysMsg( "对不起，你没有权限", 2, $aLink );
        }
        //判断用户是否授权
        $oUserFund = new model_userfund();
        $sFields   = " u.`userid`,u.`username`,uf.`availablebalance`,u.`authtoparent`,ut.`frozentype` ";
        $aUserInfo = $oUserFund->getFundByUser( $iUid, $sFields );
        if( empty($aUserInfo) || intval($aUserInfo['authtoparent']) < 1 )
        {//没有授权或者被其他占用
            sysMsg( "没有被授权，或者用户资金帐户被其他操作占用", 2, $aLink );
        }
        if( intval($aUserInfo['frozentype']) > 0 && intval($aUserInfo['frozentype']) < 3 )
        {
            sysMsg( "用户帐户已被冻结", 2, $aLink );
        }
        if( empty($_POST['flag']) || ($_POST['flag']!='withdraw' && $_POST['flag']!='confirm') )
        {
            $GLOBALS['oView']->assign( 'user', $aUserInfo );
            $GLOBALS['oView']->display( "user_upwithdraw.html" );
            exit;
        }
        elseif( $_POST['flag']=='withdraw' )
        {//确认页面
            if( empty($_POST['money']) || !is_numeric($_POST['money']) )
            {
                sysMsg("请填写提现金额");
            }
            $fMoney = round( floatval($_POST['money']), 2 );
            //判断是否为一代，如果为一代则做最低提现金额限制
            if( intval($iPid) == intval($aTempTopUser['userid']) )
            {//如果父ID==总代ID，则为一代
                $fLimitMoney = round(floatval(getConfigValue("sysyddrawlimit",3000)), 2);
                if( $fMoney < $fLimitMoney )
                {
                    sysMsg("提现不能少于规定的游戏币");
                }
            }
            $GLOBALS['oView']->assign( 'money', $fMoney );
            $GLOBALS['oView']->assign( 'user', $aUserInfo );
            $GLOBALS['oView']->assign( 'ur_here','用户提现');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_upwithdraw2.html" );
            exit;
        }
        elseif( $_POST['flag']=='confirm' )
        {//最后提交
            if( empty($_POST['money']) || !is_numeric($_POST['money']) )
            {
                sysMsg( "非法提交", 2, $aLink );
            }
            $fMoney = round( floatval($_POST['money']), 2 );
            //判断是否为一代，如果为一代则做最低提现金额限制
            if( intval($iPid) == intval($aTempTopUser['userid']) )
            {//如果父ID==总代ID，则为一代
                $fLimitMoney = round(floatval(getConfigValue("sysyddrawlimit",3000)), 2);
                if( $fMoney < $fLimitMoney )
                {
                    sysMsg("提现不能少于规定的游戏币");
                }
            }
            //进行提现
            $oUserFund = new model_userfund();
            $mResult = $oUserFund->withdrawToUp( $iUid, $iUserId, $fMoney, 1, $iAgentId );
            if( FALSE == $mResult )
            {
                sysMsg( "提现失败", 2, $aLink );
            }
            elseif( $mResult === -1 )
            {
                sysMsg( "您的资金帐户或者用户资金帐户被其他操作占用，请稍后再试", 2, $aLink );
            }
            elseif( $mResult === -1009 )
            {
                sysMsg( "对不起，提现金额超出了可用余额", 2 );
            }
            elseif( $mResult === -3 )
            {
                sysMsg( "提现成功,但是资金帐户意外被锁，请联系管理员", 1, $aLink );
            }
            elseif( $mResult === TRUE )
            {
                sysMsg( "提现成功", 1, $aLink );
            }
            else
            {
                sysMsg( "提现失败", 2, $aLink );
            }
        }
    }



    /**
     * 开户管理
     */
    function actionDistribute()
    {
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        elseif( $_SESSION['usertype'] == 0 )
        {
            sysMsg( "没有权限", 2 );
        }
        if( empty($_POST['flag']) || $_POST['flag']!= "distribute" )
        {
            //获取直接下级的用户列表，用户数
            $sFiled     = " ut.`userid`,ut.`username`,u.`addcount` ";
            $sAndWhere  = " AND ut.`isdeleted`='0' AND ut.`parentid`='".$iUserId."' AND ut.`usertype`='1' ORDER BY ut.username";
            $sAndWhere2 = " AND ut.`isdeleted`='0' AND ut.`userid`='".$iUserId."' AND ut.`usertype`='1'  ORDER BY ut.username";
            $aResult    = $oUser->getUsersProfile( $sFiled, '', $sAndWhere, TRUE );
            $aSelf      = $oUser->getUsersProfile( $sFiled, '', $sAndWhere2, FALSE );
            
            //剔除不属于销售管理员管理范围内的用户
            if( $_SESSION['usertype'] == 2 && TRUE == $oUser->IsAdminSale($_SESSION['userid']))
            {
                foreach ($aResult as $k => $v )
                {
                    if( FALSE == $oUser->isInAdminSale( $v['userid'], $_SESSION['userid']) )
                    {
                       unset($aResult[$k]);
                    }
                }
            }
            
            $aTempArr   = array();
            foreach( $aResult as $v )
            {
                $aTempArr[] = $v['userid'];
            }
            $aTempArr = $oUser->getChildrenAddCount( $aTempArr );
            foreach( $aResult as & $v )
            {
                if( isset($aTempArr[$v['userid']]) )
                {
                    $v['childaddcount'] = $aTempArr[$v['userid']];
                }
                else
                {
                    $v['childaddcount'] = 0;
                }
            }
            $GLOBALS['oView']->assign( 'selfdata', $aSelf );
            $GLOBALS['oView']->assign( 'users', $aResult );
            $GLOBALS['oView']->assign( 'userscount', count($aResult) );
            $GLOBALS['oView']->assign( 'ur_here','开户管理');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_distribute.html" );
            exit();
        }
        else
        {
            if( empty($_POST['select_rows']) || !is_array($_POST['select_rows']) )
            {
                sysMsg("请选择要分配的用户",2);
            }
            $aData  = array();
            $iCount = 0;
            foreach( $_POST['select_rows'] as $v )
            {
                $iTemp_value = 0;
                if( isset($_POST['addcount_'.intval($v)]) && is_numeric($_POST['addcount_'.intval($v)]) 
                        && intval($_POST['addcount_'.intval($v)]) > 0 )
                {
                    $iTemp_value = intval($_POST['addcount_'.intval($v)]);
                }
                $aData[] = array( 
                                    'userid'=>intval($v),
                                    'addnumber'=>$iTemp_value, 
                            );
                $iCount += $iTemp_value;
            }
            //剔除不属于销售管理员管理范围内的用户
            if( $_SESSION['usertype'] == 2 && TRUE == $oUser->IsAdminSale($_SESSION['userid']))
            {
                foreach ($aData as $k => $v )
                {
                    if( FALSE == $oUser->isInAdminSale( $v['userid'], $_SESSION['userid']) )
                    {
                       $aResult = $oUser->getUserExtentdInfo($v['userid']);
                       $sTempUser .= $aResult['username'] . ',';
                    }
                }
            }
            if (!empty($sTempUser)){
            	$sTempUser = mb_substr($sTempUser, 0, -1, "utf-8");
            	sysMsg( "用户{$sTempUser}已不是您的下级用户", 2 );
            }
            $mResult = $oUser->distributeUser( $iUserId,$aData,$iCount );
            if( $mResult === -3 )
            {
                sysMsg( "分配的用户数额超过了可以分配的数额", 2 );
            }
            elseif( $mResult === TRUE )
            {
                sysMsg( "操作成功", 1, array(array('url'=>url('user','distribute'))) );
            }
            else
            {
                sysMsg( "操作失败", 2 );
            }
        }
        
    }



    /**
     * 我的团队
     */
    function actionUserTeam()
    {
        $iUserId   = $_SESSION['userid'];
        $sAndWhere = '';
        $oUser = new model_user();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            //检测是否为销售管理员
            if( TRUE == $oUser->IsAdminSale($_SESSION['userid']) )
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
        $GLOBALS['oView']->assign( 'userid', $_SESSION['username'] );
        $GLOBALS['oView']->assign( 'username', $_SESSION['nickname'] );
        $GLOBALS['oView']->assign( 'teammoney', $fTeamMoney );
        $GLOBALS['oView']->assign( 'ur_here','我的团队');
        $oUser->assignSysInfo();
        $GLOBALS['oView']->display( "user_userteam.html" );
        exit;
    }



    /**
     * 帐户信息--修改呢称
     */
    function actionChangeName()
    {
        if( empty($_POST['flag']) || $_POST['flag']!='update' )
        {
            $oUser   = new model_user();
            $GLOBALS['oView']->assign( 'nickname', $_SESSION['nickname'] );
            $GLOBALS['oView']->assign( 'ur_here','修改昵称');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "user_changename.html" );
            exit;
        }
        else
        {
            if( empty($_POST['nickname']) )
            {
                sysMsg( "请输入昵称" );
            }
            if( FALSE == model_user::checkNickName($_POST['nickname']) )
            {//检查昵称合法性
                sysMsg( "昵称不合法" );
            }
            if( $_POST['nickname'] == $_SESSION['nickname'] )
            {
                $aLink = array( array('url'=>url('user','changename'),'title'=>'修改昵称') );
                sysMsg( "修改成功", 1, $aLink );
            }
            $_SESSION['nickname'] = stripslashes_deep($_POST['nickname']);
            $sNickName            = $_POST['nickname'];
            $oUser   = new model_user();
            $mResult = $oUser->updateUser( $_SESSION['userid'], array('nickname'=>$sNickName) );
            if( empty($mResult) )
            {
                sysMsg( "修改昵称失败", 2 );
            }
            else
            {
                $aLink = array( array('url'=>url('user','changename'),'title'=>'修改昵称') );
                sysMsg( "修改成功", 1, $aLink );
            }
        }
    }
    
    
	/**
	 * 开启在线客户服务功能(执行)  直接下级
	 * URL = ./?controller=user&action=openocs
	 */
	function actionOpenOCS()
	{
		$aLink = array( array('url'=>url('user','userlist',array('frame'=>'show')),'title'=>'用户列表') );
		 
		$iBeDoUserId = isset($_GET["uid"]) &&is_numeric($_GET["uid"]) ? intval($_GET["uid"]) : 0;
		if($iBeDoUserId == 0)
		{
			sysMsg( '无效的用户ID', 2, $aLink);
		}
		//权限检查 读取系统参数 是否显示在线客服客户控制菜单
		$oConfig = A::singleton("model_config");
		$iUsrCtrlLiveChatShow = $oConfig->getConfigs('livechat_userctrl_on');
		if ( $iUsrCtrlLiveChatShow != 1)
		{
			sysMsg( '权限不足', 2, $aLink); 
		}
		
		$oUser     =  A::singleton("model_user"); 
		$iActionId = intval($_SESSION["userid"]);
		//如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iActionId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iActionId) )
            {
                sysMsg( "操作失败", 2, $aLink );
            }
        }
		
		//$iBeDoUserId 被操作的用户
		//$iActionId	操作者ID
		// param 3, 引用权限 1:前台用户 2:后台管理员
		// param 4, 调整目标状态 1:开启 0:关闭
		if( $oUser->OCSStatus( $iBeDoUserId, $iActionId, 1,  1, false ) === TRUE )
		{
			sysMsg('操作成功', 1, $aLink );
		}
		else
		{
			sysMsg('操作失败', 2, $aLink );
		}
	}
	
	
	/**
	 * 关闭在线客户服务功能(执行) 直接下级 
	 * URL = ./?controller=user&action=closeocs
	 */
	function actionCloseOCS()
	{
		$aLink = array( array('url'=>url('user','userlist',array('frame'=>'show')),'title'=>'用户列表') );
		 
		$iBeDoUserId = isset($_GET["uid"]) &&is_numeric($_GET["uid"]) ? intval($_GET["uid"]) : 0;
		if($iBeDoUserId == 0)
		{
			sysMsg( '无效的用户ID', 2, $aLink);
		}
		
		//权限检查 读取系统参数 是否显示在线客服客户控制菜单
		$oConfig = A::singleton("model_config");
		$iUsrCtrlLiveChatShow = $oConfig->getConfigs('livechat_userctrl_on');
		if ( $iUsrCtrlLiveChatShow != 1)
		{
			sysMsg( '权限不足', 2, $aLink); 
		}
		
		$oUser     =  A::singleton("model_user"); 
		$iActionId = intval($_SESSION["userid"]);
		//如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iActionId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iActionId) )
            {
                sysMsg( "操作失败", 2, $aLink );
            }
        }
		
		//$iBeDoUserId 被操作的用户
		//$iActionId	操作者ID
		// param 3, 引用权限 1:前台用户 2:后台管理员
		// param 4, 调整目标状态 1:开启 0:关闭
		if( $oUser->OCSStatus( $iBeDoUserId, $iActionId, 1, 0, false ) === TRUE )
		{
			sysMsg('操作成功', 1, $aLink );
		}
		else
		{
			sysMsg('操作失败', 2, $aLink );
		}
	}
}
?>