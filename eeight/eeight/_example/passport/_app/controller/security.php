<?php
/**
 * 文件 : /_app/controller/security.php
 * 功能 : 安全中心的一些操作
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
 *    -- actionCheckPass        资金密码检查
 *    -- actionSetSecurity      设置资金密码
 *    -- actionChangeLoginPass      更改登陆密码或者资金密码
 *    -- actionAuthToParent     更改提现授权
 *    -- actionWithdraw         我要提现
 *    -- actionPlatWithdraw     平台提现
 *    -- actionBusinessWithdraw 商务提现
 *    -- actionUserDraw		    (总代用) 查看平台提现列表
 *    -- actionUserDrawEdit	    (总代用)处理平台提现
 *    -- actionSetUnite         合帐设置
 *    -- actionSentUnite        帐间互转
 *    -- actionTransfer         频道转帐
 *    -- actionFundOut		    在线提现
 *    -- actionApiList		    在线提现支付接口列表
 *    -- actionOnlineWithdraw   立即提现操作
 *    -- actionUserBankInfo     安全中心，卡号绑定
 *    -- actionAddUserBank      绑定银行卡信息
 *    -- actionViewUserBankInfo 查看指定的用户绑定银行信息
 *    -- actionRebinding        重新绑定银行卡，将旧银行卡置为删除
 *    -- actionDelUserBank      删除指定用户银行信息
 *    -- actionWithdrawList     提现历史列表
 *    -- actionViewWithdraw     查看用户指定的提现信息
 *    -- actionCancelWithdraw   取消提现申请
 * 
 * @author    james
 * @version   1.2.0
 * @package   passport
 * 
 * 
 * patch: YH 20100209-01
 * 1/9/2010 
 * 删除 security::ChangeLoginPass 控制器
 * 将原 security::ChangePass 改名为 ChangeLoginPass 
 * 相关链接并作相应更改
 */

class controller_security extends basecontroller 
{
    /**
     * 资金密码检查
     */
    function actionCheckPass( $sController = 'security', $sAction = 'checkpass', $bWriteSion = false, $sNewUrl = "" )
    {
        if( isset($_SESSION['setsecurity']) && $_SESSION['setsecurity']=='yes' )
        {
            $this->actionSetSecurity( $sController,$sAction );
        }
        //生成随即码
        $_SESSION['checkcode'] = rand( 1, 1000 );
        if( empty($_POST['flag']) || $_POST['flag'] != 'check' )
        {
            $oUser = new model_user();
            $GLOBALS['oView']->assign( "nextcontroller", $sController );
            $GLOBALS['oView']->assign( "nextaction", $sAction );
            $GLOBALS['oView']->assign( "writesion", $bWriteSion );
            $GLOBALS['oView']->assign( "newurl", $sNewUrl );
            $GLOBALS['oView']->assign( 'ur_here','资金密码检查');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "security_checkpass.html" );
            EXIT;
        }
        else
        {
            if( empty($_POST['nextcon']) || empty($_POST['nextact']) )
            {
                sysMsg("非法操作",2);
            }
            if( empty($_POST['secpass']) )
            {
                sysMsg("请输入资金密码");
            }
            $oUser = new model_user();
            if( FALSE == $oUser->checkSecurityPass( $_SESSION['userid'], $_POST['secpass'] ) )
            {
                sysMsg("资金密码错误",2);
            }
            if ($_POST['writesion'] == true){ // 向session中写入登录session与ip值
                //　写入sessionkey
                $iTempUserId = $_SESSION['userid'];
                $aResult = array();
                $oUserSion = new model_usersession();
                $aResult = $oUserSion->getOneSessionKey($iTempUserId);
                $aResult['sessionkey'] = isset($aResult['sessionkey']) ? $aResult['sessionkey'] : "";
                $_SESSION[$iTempUserId.'_sion'] = $aResult['sessionkey'];
                // 写入ip
                $oUser = new model_user();
                $aUser = $oUser->getUserInfo($iTempUserId);
                $_SESSION[$iTempUserId.'_ip'] = $aUser['lastip'];
            }
            if (empty($_POST['newurl'])){
                redirect( url($_POST['nextcon'], $_POST['nextact'], array('check'=>$_SESSION['checkcode'])) );
            } else {
                redirect( $_POST['newurl'] );
            }
        }
    }



    /**
     * 设置资金密码
     */
    function actionSetSecurity( $sController = 'security', $sAction = 'setsecurity' )
    {
        if( empty($_SESSION['setsecurity']) || $_SESSION['setsecurity']!='yes' )
        {
            sysMsg("操作错误",2);
        }
        if( empty($_POST['flag']) || $_POST['flag']!='insert' )
        {
            $oUser = new model_user();
            $GLOBALS['oView']->assign( "nextcontroller", $sController );
            $GLOBALS['oView']->assign( "nextaction", $sAction );
            $GLOBALS['oView']->assign( 'ur_here','设置资金密码');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "security_setsecurity.html", "setsecurity" );
            EXIT;
        }
        else
        {
            if( empty($_POST['secpass']) )
            {//资料不完整
                sysMsg("请填写资金密码");
            }
            if( FALSE == model_user::checkUserPass($_POST['secpass']) )
            {//检查密码合法性
                sysMsg("资金密码不合法");
            }
            $oUser = new model_user();
            $mResult = $oUser->changePassWord( $_SESSION['userid'], '', $_POST['secpass'] );
            if( empty($mResult) )
            {
                sysMsg("资金密码修改失败，新密码可能和原来密码一样",2);
            }
            if( $mResult == -1 )
            {//登陆密码和资金密码相同
                sysMsg("资金密码不能和登陆密码一样!",2);
            }
            $_SESSION['setsecurity'] = ''; // 1/19/2010
            $aLinks = array( 
                         array('url'=> url($_POST['nextcon'],$_POST['nextact']) )
                         );
            sysMsg( "资金密码设置成功", 1, $aLinks );
        }
    }



    /**
     * 更改登陆密码或者资金密码
     */
    function actionChangeLoginPass()
    {
        if( !isset($_POST['flag']) || $_POST['flag']!='changepass' )
        {
        	//修改密码界面
            $oUser = new model_user();
            $aUserInfo = $oUser->getUserInfo( $_SESSION['userid'], array('loginpwd','securitypwd') );
            $GLOBALS['oView']->assign( 'loginout_url', url('default','logout'));
            $GLOBALS['oView']->assign( 'ur_here','修改密码');
            $GLOBALS['oView']->assign( 'securitypwd',$aUserInfo['securitypwd']);
            $GLOBALS['oView']->assign( 'displayflag',$_SESSION['logintype']);
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "security_changepass.html", "changepass" );
            exit();
        }
        else
        {
            if( empty($_POST['newpass']) || empty($_POST['confirm_newpass']) )
            {//资料不完整
                sysMsg("请填写新密码和确认密码");
            }
            if( $_POST['newpass'] != $_POST['confirm_newpass'] )
            {
                sysMsg("新密码和确认密码不一致");
            }
            if( FALSE == model_user::checkUserPass($_POST['newpass']) )
            {//检查密码合法性
                sysMsg("密码不合法");
            }
            $oUser     = new model_user();
            $aUserInfo = $oUser->getUserInfo( $_SESSION['userid'], array('loginpwd','securitypwd') );
            if( isset($_POST['changetype']) && $_POST['changetype']=='loginpass' )
            {//修改登陆密码
            	if ($_SESSION['logintype'] == 'security'){
            	//资金密码修改登录密码
            		$mResult = $oUser->changePassWord( $_SESSION['userid'], $_POST['newpass'] );
                	if( empty($mResult) )
                	{
                 	   sysMsg( "登陆密码修改失败，新密码可能和原来密码一样", 2 );
                	}
                	if( $mResult == -1 )
	                {//登陆密码和资金密码相同
                    	sysMsg("登陆密码不能和资金密码一样!", 2);
                	}
                	$aLinks = array( array('url'=> url('security','changeloginpass', array('check'=>$_REQUEST['check'])),
                                        'title'=>'修改密码') );
            	    sysMsg("登陆密码修改成功", 1, $aLinks);
            	}
            	else 
            	{
            	//正常修改密码
            		if( empty($_POST['oldpass']) )
	            	{
	                	sysMsg("请输入原始密码");
	            	}
	            	$sOldPWD   = md5($_POST['oldpass']);
                	if( $sOldPWD != $aUserInfo['loginpwd'] )
                	{
	                    sysMsg("原始密码错误", 2);
    	            }
        	        $mResult = $oUser->changePassWord( $_SESSION['userid'], $_POST['newpass'] );
            	    if( empty($mResult) )
                	{
                    	sysMsg( "登陆密码修改失败，新密码可能和原来密码一样", 2 );
                	}
                	if( $mResult == -1 )
                	{//登陆密码和资金密码相同
                    	sysMsg("登陆密码不能和资金密码一样!", 2);
                	}
	                // 	1/9/2010 changepass
    	            $aLinks = array( array('url'=> url('security','changeloginpass', 
                					array('check'=>$_REQUEST['check'])),
                                        'title'=>'修改密码') );
        	        sysMsg("登陆密码修改成功", 1, $aLinks);
            	}
            }
            elseif( isset($_POST['changetype']) && $_POST['changetype']=='secpass' )
            {//修改资金密码
            	$_SESSION['setsecurity'] = 'yes'; 	// 1/9/2010 YH20100209-01
            	if( $aUserInfo['securitypwd'] != "" )
            	{
            		if( empty($_POST['oldpass']) )
	                {
	                	$_SESSION['setsecurity'] = '';
	                    sysMsg("请输入原始密码");
	                }
	                $sOldPWD   = md5($_POST['oldpass']);
	            	if( $sOldPWD != $aUserInfo['securitypwd'] )
	                {
	                	$_SESSION['setsecurity'] = '';
	                    sysMsg("原始密码错误", 2);
	                }
            	}
                $mResult = $oUser->changePassWord( $_SESSION['userid'], '', $_POST['newpass'] );
                if( empty($mResult) )
                {
                	$_SESSION['setsecurity'] = '';
                    sysMsg("资金密码修改失败，新密码可能和原来密码一样",2);
                }
                elseif( $mResult == -1 )
                {//登陆密码和资金密码相同
                	$_SESSION['setsecurity'] = '';
                    sysMsg("资金密码不能和登陆密码一样!",2);
                }
                else
                {
                	$aLinks = array( 
                            array('url'=>url('security','changeloginpass',array('check'=>$_REQUEST['check'])),
                                        'title'=>'修改密码') );
                	$_SESSION['setsecurity'] = ''; // 1/9/2010 YH20100209-01 资金密码设置即时生效
                	sysMsg("资金密码修改成功", 1, $aLinks);
                }
            }
            else
            {
            	$_SESSION['setsecurity'] = '';
                sysMsg( "非法提交", 2 );
            }
            
        }
        
    }
    
    
    // 待删除控制器  功能合并入 旧名称ChangePass 新名称 ChangeLoginPass  1/9/2010
    // 系统删除一个控制器 ChangePass
    //资金密码登陆修改登陆密码
    function actionChangeLoginPassOLD()
    {
        if( !isset($_POST['flag']) || $_POST['flag']!='changepass' )
        {//修改密码界面
            $oUser = new model_user();
            $GLOBALS['oView']->assign( 'loginout_url', url('default','logout'));
            $GLOBALS['oView']->assign( 'ur_here','修改登陆密码');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "security_changeloginpass.html", "changeloginpass" );
            exit();
        }
        else
        {
            if( empty($_POST['newpass']) || empty($_POST['confirm_newpass']) )
            {//资料不完整
                sysMsg("请填写新密码和确认密码");
            }
            if( $_POST['newpass'] != $_POST['confirm_newpass'] )
            {
                sysMsg("新密码和确认密码不一致");
            }
            if( FALSE == model_user::checkUserPass($_POST['newpass']) )
            {//检查密码合法性
                sysMsg("密码不合法");
            }
            $oUser     = new model_user();
            if( isset($_POST['changetype']) && $_POST['changetype']=='loginpass' )
            {//修改登陆密码
                $mResult = $oUser->changePassWord( $_SESSION['userid'], $_POST['newpass'] );
                if( empty($mResult) )
                {
                    sysMsg( "登陆密码修改失败，新密码可能和原来密码一样", 2 );
                }
                if( $mResult == -1 )
                {//登陆密码和资金密码相同
                    sysMsg("登陆密码不能和资金密码一样!", 2);
                }
                $aLinks = array( array('url'=> url('security','changeloginpass', array('check'=>$_REQUEST['check'])),
                                        'title'=>'修改密码') );
                sysMsg("登陆密码修改成功", 1, $aLinks);
            }
            elseif( isset($_POST['changetype']) && $_POST['changetype']=='secpass' )
            {//修改资金密码
                $mResult = $oUser->changePassWord( $_SESSION['userid'], '', $_POST['newpass'] );
                if( empty($mResult) )
                {
                    sysMsg("资金密码修改失败，新密码可能和原来密码一样",2);
                }
                if( $mResult == -1 )
                {//登陆密码和资金密码相同
                    sysMsg("资金密码不能和登陆密码一样!",2);
                }
                $aLinks = array( 
                            array('url'=>url('security','changeloginpass',array('check'=>$_REQUEST['check'])),
                                        'title'=>'修改密码') );
                sysMsg("资金密码修改成功", 1, $aLinks);
            }
            else
            {
                sysMsg( "非法提交", 2 );
            }
        }
    }



    /**
     * 更改提现授权
     */
    function actionAuthToParent()
    {
        if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
        {
            $this->actionCheckPass( 'security', 'authtoparent' );
            EXIT;
        }
        if( !isset($_POST['flag']) || $_POST['flag']!='update' )
        {//界面
            $oUser     = new model_user();
            $aUserinfo = $oUser->getUserInfo( $_SESSION['userid'], array('authtoparent') );
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( "auth",  $aUserinfo['authtoparent'] );
            $GLOBALS['oView']->assign( 'ur_here','更改提现授权');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "security_authtoparent.html" );
            exit();
        }
        else
        {
            if( empty($_POST['default']) )
            {
                sysMsg("非法操作",2);
            }
            if( empty($_POST['drawtype']) )
            {
                sysMsg("请选择提现方式");
            }
            if( $_POST['drawtype'] == $_POST['default'] )
            {//没有做修改
                sysMsg("修改成功",1);
            }
            $oUser = new model_user();
            if( $_POST['drawtype']=='up' )
            {//授权上级
                $mResult = $oUser->updateUser( $_SESSION['userid'], array('authtoparent'=>1) );
            }
            else
            {
                $mResult = $oUser->updateUser( $_SESSION['userid'], array('authtoparent'=>0) );
            }
            if( empty($mResult) )
            {
                sysMsg( "修改失败", 2 );
            }
            $aLinks = array( array('url'=>url('security','authtoparent',array('check'=>$_REQUEST['check'])),
                                        'title'=>'提现授权') );
            sysMsg( "修改成功", 1, $aLinks );
        }
    }



    /**
     * 我要提现
     */
    function actionWithdraw()
    {
    	$iUserId       = $_SESSION['userid'];
    	$oUser         = new model_user();
    	//如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId  = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        
        // 如果是总代或总代管理员则跳转到 商务提现 页面
        if ($oUser->isTopProxy($iUserId) === true){
        	redirect("/?controller=security&action=businesswithdraw&check=" . $_REQUEST['check']);
        }
        
        
    	
        //检测用户是否有权限使用
        $oUserFund = new model_userfund();
        $sFields   = " u.`userid`,u.`username`,uf.`availablebalance`,u.`authtoparent` ";
        $aUserinfo = $oUserFund->getFundByUser( $_SESSION['userid'], $sFields );
        if( empty($aUserinfo) )
        {
            sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 1 );
        }
        if( $aUserinfo['authtoparent'] == 1 )
        {
            sysMsg( "您已授权为上级提现，不能使用此功能", 1 );
        }
        
        //TODO：并行期充提分期上代码
        $oUser         = A::singleton("model_user");//new model_user();
        //获取操作用户的总代
        $aTempTopUser = $oUser->getTopProxyId( $_SESSION['userid'], TRUE );
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
        
        $iParentId = $oUser->getParentId( $_SESSION['userid'] );//获取上级ID
        if( empty($iParentId) )
        {//总代不提供此功能
            sysMsg( "获取上级信息失败", 2 );
        }
        
        if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
        {
            $this->actionCheckPass( 'security', 'withdraw' );
            EXIT;
        }
        $fAvailableBalance = floor($aUserinfo['availablebalance']*100) / 100;
        if( !isset($_POST['flag']) || ($_POST['flag']!='withdraw' && $_POST['flag']!='confirm') )
        {//提现界面
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( "user",  $aUserinfo );
            $GLOBALS['oView']->assign( "availablebalance",  $fAvailableBalance );
            $GLOBALS['oView']->assign( 'ur_here','我要提现');
            $oUserFund->assignSysInfo();
            $GLOBALS['oView']->display( "security_withdraw.html" );
            EXIT;
        }
        elseif( $_POST['flag'] == 'withdraw' )
        {//确认页面
            if( empty($_POST['money']) || !is_numeric($_POST['money']) )
            {
                sysMsg("请填写提现金额");
            }
            $fMoney = floor(floatval($_POST['money'])*100) / 100;
            //判断是否为一代，如果为一代则做提现最低金额处理
            if( intval($iParentId) == intval($aTempTopUser['userid']) )
            {//如果父ID==总代ID，则为一代
                $fLimitMoney = floor(floatval(getConfigValue("sysyddrawlimit",3000))*100) / 100;
                if( $fMoney < $fLimitMoney )
                {
                    sysMsg("提现不能少于规定的游戏币");
                }
            }
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( 'money', $fMoney );
            $GLOBALS['oView']->assign( 'user',  $aUserinfo );
            $GLOBALS['oView']->assign( "availablebalance",  $fAvailableBalance );
            $GLOBALS['oView']->assign( 'ur_here','我要提现确认');
            $oUserFund->assignSysInfo();
            $GLOBALS['oView']->display( "security_withdraw2.html" );
            EXIT;
        }
        elseif( $_POST['flag']=='confirm' )
        {
            if( empty($_POST['money']) || !is_numeric($_POST['money']) )
            {
                $aLinks=array(array('url'=>url('security','withdraw',array('check'=>$_REQUEST['check'])),
                                        'title'=>'我要提现') );
                sysMsg( "非法提交", 2, $aLinks );
            }
             $fMoney = floor(floatval($_POST['money'])*100) / 100;
            //判断是否为一代，如果为一代则做提现最低金额处理
            if( intval($iParentId) == intval($aTempTopUser['userid']) )
            {//如果父ID==总代ID，则为一代
                $fLimitMoney = floor(floatval(getConfigValue("sysyddrawlimit",3000))*100) / 100;
                if( $fMoney < $fLimitMoney )
                {
                    sysMsg("提现不能少于规定的游戏币");
                }
            }
            //进行提现
            $result = $oUserFund->withdrawToUp( $_SESSION['userid'], $iParentId, $fMoney, 2 );
            if( FALSE == $result )
            {
                sysMsg( "发起提现失败", 2 );
            }
            elseif( $result === -1 )
            {
                sysMsg( "您的资金帐户或者上级资金帐户被其他操作占用,请稍后再试", 2 );
            }
            elseif( $result === -1009 )
            {
                sysMsg( "对不起，提现金额超出了可用余额", 2 );
            }
            elseif( $result === -3 )
            {
                $aLinks=array(array('url'=>url('security','withdraw',array('check'=>$_REQUEST['check'])),
                                        'title'=>'我要提现') );
                sysMsg( "发起提现成功,但是资金帐户意外被锁，请联系管理员", 1, $aLinks );
            }
            elseif( $result === TRUE )
            {
                $aLinks =array(array('url'=>url('security','withdraw',array('check'=>$_REQUEST['check'])),
                                        'title'=>'我要提现') );
                sysMsg( "发起提现成功", 1, $aLinks );
            }
            else
            {
                sysMsg( "发起提现失败", 2 );
            }
        }
    }



    /**
     * 平台提现
     */
    function actionPlatWithdraw()
    {
        if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
        {
            $this->actionCheckPass('security','platwithdraw');
            EXIT;
        }
        
        
        $oUser         = new model_user();
        $iUserId       = $_SESSION['userid'];
        $iAgentId      = 0;	//初始化总代管理员操作ID
        $iPlatMinMoney = floor(floatval(getConfigValue('config_platmin',3000))*100) / 100; //平台最低提现金额
        $iPlatMaxMoney = floor(floatval(getConfigValue('config_platmax',3000))*100) / 100; //平台最高提现金额
        $iIsForCompany = 0; //0总代受理，1公司受理
        
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iAgentId = $iUserId;
            $iUserId  = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            $iParentId = $iUserId;
        }
        else 
        {//获取总代ID
            $iParentId = $oUser->getTopProxyId( $iUserId );
        }
        /*if( $iParentId == $iUserId )
        {//总代没有权限使用平台提现，只能使用商务提现
        	sysMsg( "没有权限", 2 );
        }*/
        
        // 如果是总代或总代管理员则跳转到 商务提现 页面
        if ($oUser->isTopProxy($iUserId) === true){
        	redirect("/?controller=security&action=businesswithdraw&check=" . $_REQUEST['check']);
        }
        
        // 获取用户绑定的银行卡信息
    	$oUserBankList = new model_withdraw_UserBankList();
    	$oUserBankList->UserId = $iUserId;
    	$oUserBankList->Status = 1;
    	$oUserBankList->init();
    	if (empty($oUserBankList->Data)){
    		$aLinks = array(
				0 => array(
    				'title' => "卡号绑定页面",
    				'url'	=> "?controller=security&action=adduserbank"
				)
			);    	
    		sysMsg( "您尚未绑定银行卡，请先进行卡号绑定！", 2, $aLinks, 'self' );
    	}
        
        //TODO：并行期平台提现分期上代码
        //获取操作用户的总代
        $aTempTopUser = $oUser->getTopProxyId( $iUserId, TRUE );
        if( empty($aTempTopUser) )
        {//获取数据失败则退出
            sysMsg( "没有权限", 2 );
        }
        $oConfig      = new model_config();
        /*$sAllowCashCT = $oConfig->getConfigs( 'allowPTcashTX' );
        $sAllowCashCT = empty($sAllowCashCT) ? "" : $sAllowCashCT;
        $aAllowedTopUser = explode( ",", $sAllowCashCT );
        if( !in_array( $aTempTopUser['username'], $aAllowedTopUser ) )
        {//不在指定总代范围内
            sysMsg( "没有权限", 2 );
        }*/
        //获取是否为总代受理
        $sSyszdAcceptPTTX = $oConfig->getConfigs( 'syszdAcceptPTTX' );
        $sSyszdAcceptPTTX = empty($sSyszdAcceptPTTX) ? "" : $sSyszdAcceptPTTX;
        $aSyszdAcceptPTTX = explode( ",", $sSyszdAcceptPTTX );
        if( !in_array( $aTempTopUser['username'], $aSyszdAcceptPTTX ) )
        {//不在指定总代范围内
            $iIsForCompany = 1;
        }
//        unset($oConfig,$sAllowCashCT,$aAllowedTopUser,$sSyszdAcceptPTTX,$aSyszdAcceptPTTX);
        unset($oConfig,$sSyszdAcceptPTTX,$aSyszdAcceptPTTX);
        
        
        $oUserFund = A::singleton("model_userfund");//new model_userfund();
        $aUserinfo = $oUserFund->getFundByUser( $iUserId, '', 0, TRUE, TRUE );
        if( empty($aUserinfo) )
        {
            sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2 );
        }
        
        $oWithdraw = new model_withdrawel();
        $iPlatTimes = getConfigValue('config_plattimes', 3); //平台最高提现交数（每天）
        $iCount = $oWithdraw->getCountByUser($iUserId);
        if ($iCount >= $iPlatTimes){
        	$aLinks=array(array('url'=>url( 'security', 'withdraw', array('check'=>$_REQUEST['check']) ),
                                        'title'=>'平台提现') );
        	sysMsg( "您今天已没有可用提现次数", 2, $aLinks);
        }
        $fAvailableBalance = floor($aUserinfo['availablebalance']*100) / 100;
        if( empty($_POST['flag']) || ($_POST['flag']!='withdraw' && $_POST['flag']!='confirm') )
        {
            // 获取用户绑定的银行卡信息
        	$oUserBankList = new model_withdraw_UserBankList();
        	$oUserBankList->UserId = $iUserId;
        	$oUserBankList->Status = 1;
        	$oUserBankList->init();
        	
        	$oFODetail = new model_withdraw_fundoutdetail();
        	$oFODetail->Digit = 4; // 只显示四位卡号
        	foreach ($oUserBankList->Data as $k => $value){
	    		// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
	    		$oFODetail->Account = $value['account'];
	    		$oUserBankList->Data[$k]['account'] = $oFODetail->hiddenAccount();
    		}
    		
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( "user",  $aUserinfo );
            $GLOBALS['oView']->assign( "availablebalance",  $fAvailableBalance );
            $GLOBALS['oView']->assign( "banks", $oUserBankList->Data );
            $GLOBALS['oView']->assign( 'min_money', $iPlatMinMoney );
            $GLOBALS['oView']->assign( 'max_money', $iPlatMaxMoney );
            $GLOBALS['oView']->assign( 'count', $iCount );
            $GLOBALS['oView']->assign( 'times', $iPlatTimes );
            $GLOBALS['oView']->assign( 'ur_here','平台提现');
            $oUserBankList->assignSysInfo();
            $GLOBALS['oView']->display( "security_platwithdraw.html" );
            exit();
        }
        elseif( $_POST['flag'] == 'withdraw' )
        {//确认页面
            if( empty($_POST['money']) || !is_numeric($_POST['money']) || !is_numeric($_POST['bankinfo']) || 
            	$_POST['bankinfo'] <= 0)
            {
                sysMsg( "请填写完整的资料" );
            }
            $aData['money'] = floatval( $_POST['money'] );
            if( $aData['money'] < $iPlatMinMoney )
            {
                sysMsg( "提现金额不能低于最低平台提现金额" );
            }
             if( $aData['money'] > $iPlatMaxMoney )
            {
                sysMsg( "提现金额不能高于最高平台提现金额" );
            }
            
            if( $aData['money'] > $fAvailableBalance )
            {
                sysMsg( "提现金额不能高于最大可用余额" );
            }
            
            // 获取用户选择的银行卡信息
            // 获取用户银行信息,如果用户选择的银行卡中用户ID不是本人则给出错误提示，并返回
        	intval($_POST['bankinfo']) >= 0 or sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	$oUserBank = new model_withdraw_UserBank($_POST['bankinfo']);
        	if ($oUserBank->UserId != $iUserId || $oUserBank->Status != 1){
        		sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	}
	        if(time()-strtotime($oUserBank->AddTime)<3600*intval(getConfigValue('bind_time_limit',2)))
			{
				sysMsg("银行卡新绑定".getConfigValue('bind_time_limit',2)."小时内不允许提款！", 2);
			}	
        	// 卡号只显示后四位
        	$oFODetail = new model_withdraw_fundoutdetail();
        	$oFODetail->Digit = 4; // 只显示四位卡号
        	$oFODetail->Account = $oUserBank->Account;
        	$sHiddenAccount = $oFODetail->hiddenAccount();
            
            $aData['bankname'] = $oUserBank->BankName;
            $aData['province'] = $oUserBank->Province;
            $aData['bankcity'] = $oUserBank->City;
            /*if( mb_strlen($_POST['truename'],"UTF-8") < 2 || mb_strlen($_POST['truename'],"UTF-8") > 4 )
            {
                sysMsg( "请填写正确的开户人姓名" );
            }*/
            $aData['truename'] = $oUserBank->AccountName;
            if( !preg_match("/^[0-9]{16,19}$/",$oUserBank->Account) )
            {
                sysMsg( "请填写正确的个人银行帐号" );
            }
            $aData['bankno'] = $sHiddenAccount;
            $aData['cardid'] = $oUserBank->Id;
            /*$confirm_bankno  = $_POST['confirm_bankno'];
            if( $aData['bankno'] != $confirm_bankno )
            {
                sysMsg( "两次输入的个人银行帐号不相同，请确认" );
            }*/
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( 'datas', $aData );
            $GLOBALS['oView']->assign( 'user',  $aUserinfo );
            $GLOBALS['oView']->assign( "availablebalance",  $fAvailableBalance );
            $GLOBALS['oView']->assign( 'tempaccount',  $oFODetail->hiddenAccount() );
            $GLOBALS['oView']->assign( 'ur_here','平台提现确认');
            $oUserFund->assignSysInfo();
            $GLOBALS['oView']->display( "security_platwithdraw2.html" );
            EXIT;
        }
        elseif( $_POST['flag'] == 'confirm' )
        {
            if( empty($_POST['money']) || !is_numeric($_POST['money']) || !is_numeric($_POST['cardid']) || 
            	$_POST['cardid'] <= 0)
            {
                sysMsg( "非法提交", 2 );
            }
            $fMoney = floatval( $_POST['money'] );
            if( $fMoney < $iPlatMinMoney )
            {
                sysMsg( "提现金额不能低于最低平台提现金额", 2 );
            }
            if( $fMoney > $iPlatMaxMoney )
            {
                sysMsg( "提现金额不能高于最高平台提现金额", 2 );
            }
            if( $fMoney > $fAvailableBalance )
            {
                sysMsg( "提现金额不能高于最大可用余额" );
            }
            
            // 获取用户银行信息,如果用户选择的银行卡中用户ID不是本人则给出错误提示，并返回
        	intval($_POST['cardid']) >= 0 or sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	$oUserBank = new model_withdraw_UserBank($_POST['cardid']);
        	if ($oUserBank->UserId != $iUserId || $oUserBank->Status != 1){
        		sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	}
            
            $sBankName = $oUserBank->BankName;
            $iBankId = $oUserBank->BankId;
            $sBankProvince = $oUserBank->Province;
            $sBankCity = $oUserBank->City;
            /*if( mb_strlen($_POST['truename'],"UTF-8") < 2 || mb_strlen($_POST['truename'],"UTF-8") > 4 )
            {
                sysMsg( "请填写正确的开户人姓名", 2 );
            }*/
            $sTrueName = $oUserBank->AccountName;
            if( !preg_match("/^[0-9]{16,19}$/", $oUserBank->Account) )
            {
                sysMsg( "请填写正确的个人银行帐号", 2 );
            }
            $sBankNo   = $oUserBank->Account;
            $result    = $oWithdraw->withdrawelAdd( $iParentId, $iUserId, $iBankId, $sBankName, $sBankNo, $sBankProvince, $sBankCity, 
                                         $sTrueName, $fMoney, $aUserinfo['username']." 发起平台提现", $iAgentId, $iIsForCompany );
            if( $result === -3 )
            {
                sysMsg( "对不起，您的资金帐户被其他操作占用", 1 );
            }
            elseif( $result === -1009 )
            {
                sysMsg( "对不起，提现金额超出了可用余额", 2 );
            }
            elseif( $result > 2 )
            {
                sysMsg( "对不起，提现金额超出了可用余额，您的最大提现金额为".($result-1000), 2 );
            }
            elseif( $result === -6 )
            {//获取其他可用余额失败
                sysMsg( "请求超时，请重试", 2 );
            }
            elseif( $result === -7 )
            {//其他频道有负余额
                sysMsg( "其他频道有负余额，请先转帐将其补平", 2 );
            }
            elseif( $result === -41 )
            {
                $aLinks=array(array('url'=>url('security','platwithdraw',array('check'=>$_REQUEST['check'])),
                                        'title'=>'平台提现') );
                sysMsg( "平台提现申请失败,您的资金帐户意外被锁，请联系管理员", 2, $aLinks );
            }
            elseif( $result === -5 )
            {
                $aLinks=array(array('url'=>url('security','platwithdraw',array('check'=>$_REQUEST['check'])),
                                        'title'=>'平台提现') );
                sysMsg( "平台提现申请成功,但是资金帐户意外被锁，请联系管理员", 1, $aLinks );
            }
            elseif( $result === TRUE ) 
            {
                $aLinks=array(array('url'=>url( 'security', 'platwithdraw', array('check'=>$_REQUEST['check']) ),
                                        'title'=>'平台提现') );
                sysMsg( "平台提现申请成功", 1, $aLinks );
            }
            else 
            {
                sysMsg( "平台提现申请失败", 2 );
            }
        }
    }



    /**
     * 商务提现
     */
    function actionBusinessWithdraw()
    {
        if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
        {
            $this->actionCheckPass('security','businesswithdraw');
            EXIT;
        }
        $oUser         = new model_user();
        $iUserId       = $_SESSION['userid'];
        $iAgentId      = 0; //初始化总代管理员操作ID
        $iPlatMinMoney = round( floatval(getConfigValue('config_buinessplatmin',100000)), 2 ); //平台最低提现金额
        $iIsForCompany = 1;
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iAgentId = $iUserId;
            $iUserId  = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            $iParentId = $iUserId;
        }
        else 
        {//获取总代ID
            $iParentId = $oUser->getTopProxyId( $iUserId );
        }
        if( $iParentId != $iUserId )
        {//普代没有权限使用商务提现，只能使用平台提现
            sysMsg( "没有权限", 2 );
        }
        
        //TODO：并行期平台提现分期上代码
        //获取操作用户的总代
        $aTempTopUser = $oUser->getTopProxyId( $iUserId, TRUE );
        if( empty($aTempTopUser) )
        {//获取数据失败则退出
            sysMsg( "没有权限", 2 );
        }
        
        // 获取用户绑定的银行卡信息
    	$oUserBankList = new model_withdraw_UserBankList();
    	$oUserBankList->UserId = $iUserId;
    	$oUserBankList->Status = 1;
    	$oUserBankList->init();
    	if (empty($oUserBankList->Data)){
    		$aLinks = array(
				0 => array(
    				'title' => "卡号绑定页面",
    				'url'	=> "?controller=security&action=adduserbank"
				)
			);    	
    		sysMsg( "您尚未绑定银行卡，请先进行卡号绑定！", 2, $aLinks, 'self' );
    	}
        /*$oConfig      = new model_config();
        $sAllowCashCT = $oConfig->getConfigs( 'allowPTcashTX' );
        $sAllowCashCT = empty($sAllowCashCT) ? "" : $sAllowCashCT;
        $aAllowedTopUser = explode( ",", $sAllowCashCT );
        if( !in_array( $aTempTopUser['username'], $aAllowedTopUser ) )
        {//不在指定总代范围内
            sysMsg( "没有权限", 2 );
        }
        unset($oConfig,$sAllowCashCT,$aAllowedTopUser);*/
        
        $oUserFund = A::singleton("model_userfund");//new model_userfund();
        $aUserinfo = $oUserFund->getFundByUser( $iUserId, '', 0, TRUE, TRUE );
//        print_rr($aUserinfo,true,true);
//		更改开始 1/8/2010;
//		解决2010-1-5总代商务提现,无法提到正常的可提最大金额 问题
		
		$oWithdraw = new model_withdrawel();
		$aUserAcc = $oUserFund->getProxyFundList($iUserId);
        $aUserinfo['availablebalance'] = $oWithdraw->getCreditUserMaxMoney( $iUserId, $aUserAcc[0]['TeamAvailBalance'] );
        if ($aUserinfo['availablebalance'] == "error"){
        	sysMsg( "获取其它频道余额失败，请稍后重试", 2 );
        }
        
//		更改结束
        if( empty($aUserinfo) )
        {
            sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2 );
        }
        if( empty($_POST['flag']) || ($_POST['flag']!='withdraw' && $_POST['flag']!='confirm') )
        {
            //获取银行列表
//        	$oDraw     = new model_withdrawel();
//          $aBankList = $oDraw->getBankList();
//			$aBankList = $oWithdraw->getBankList();
			// 获取用户绑定的银行卡信息
        	$oUserBankList = new model_withdraw_UserBankList();
        	$oUserBankList->UserId = $iUserId;
        	$oUserBankList->Status = 1;
        	$oUserBankList->init();
        	
        	$oFODetail = new model_withdraw_fundoutdetail();
        	$oFODetail->Digit = 4; // 只显示四位卡号
        	foreach ($oUserBankList->Data as $k => $value){
	    		// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
	    		$oFODetail->Account = $value['account'];
	    		$oUserBankList->Data[$k]['account'] = $oFODetail->hiddenAccount();
    		}
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( "user",  $aUserinfo );
            $GLOBALS['oView']->assign( "banks", $oUserBankList->Data );
            $GLOBALS['oView']->assign( 'min_money', $iPlatMinMoney );
            $GLOBALS['oView']->assign( 'ur_here','商务提现');
            $oUserBankList->assignSysInfo();
            $GLOBALS['oView']->display( "security_businesswithdraw.html" );
            exit();
        }
        elseif( $_POST['flag'] == 'withdraw' )
        {//确认页面
            if( empty($_POST['money']) || !is_numeric($_POST['money']) || !is_numeric($_POST['bankinfo']) || 
            	$_POST['bankinfo'] <= 0)
            {
                sysMsg( "请填写完整的资料" );
            }
            $aData['money'] = floatval( $_POST['money'] );
            if( $aData['money'] < $iPlatMinMoney )
            {
                sysMsg( "提现金额不能低于最低商务提现金额" );
            }
            
            // 获取用户银行信息,如果用户选择的银行卡中用户ID不是本人则给出错误提示，并返回
        	intval($_POST['bankinfo']) >= 0 or sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	$oUserBank = new model_withdraw_UserBank($_POST['bankinfo']);
        	if ($oUserBank->UserId != $iUserId || $oUserBank->Status != 1){
        		sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	}
	        if(time()-strtotime($oUserBank->AddTime)<3600*intval(getConfigValue('bind_time_limit',2)))
			{
				sysMsg("银行卡新绑定".getConfigValue('bind_time_limit',2)."小时内不允许提款！", 2);
			}			
        	// 卡号只显示后四位
        	$oFODetail = new model_withdraw_fundoutdetail();
        	$oFODetail->Digit = 4; // 只显示四位卡号
        	$oFODetail->Account = $oUserBank->Account;
        	$sHiddenAccount = $oFODetail->hiddenAccount();
            
            $aData['bankname'] = $oUserBank->BankName;
            $aData['province'] = $oUserBank->Province;
            $aData['bankcity'] = $oUserBank->City;
            /*if( mb_strlen($_POST['truename'],"UTF-8") < 2 || mb_strlen($_POST['truename'],"UTF-8") > 4 )
            {
                sysMsg( "请填写正确的开户人姓名" );
            }*/
            $aData['truename'] = $oUserBank->AccountName;
            if( !preg_match("/^[0-9]{16,19}$/",$oUserBank->Account) )
            {
                sysMsg( "请填写正确的个人银行帐号" );
            }
            $aData['bankno'] = $sHiddenAccount;
            $aData['cardid'] = $oUserBank->Id;
            /*$confirm_bankno  = $_POST['confirm_bankno'];
            if( $aData['bankno'] != $confirm_bankno )
            {
                sysMsg( "两次输入的个人银行帐号不相同，请确认" );
            }*/
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( 'datas', $aData );
            $GLOBALS['oView']->assign( 'user',  $aUserinfo );
            $GLOBALS['oView']->assign( 'tempaccount',  $oFODetail->hiddenAccount() );
            $GLOBALS['oView']->assign( 'ur_here','商务提现确认');
            $oUserFund->assignSysInfo();
            $GLOBALS['oView']->display( "security_businesswithdraw2.html" );
            EXIT;
        }
        elseif( $_POST['flag'] == 'confirm' )
        {
            if( empty($_POST['money']) || !is_numeric($_POST['money']) || !is_numeric($_POST['cardid']) || 
            	$_POST['cardid'] <= 0 )
            {
                sysMsg( "非法提交", 2 );
            }
            $fMoney = floatval( $_POST['money'] );
            if( $fMoney < $iPlatMinMoney )
            {
                sysMsg( "提现金额不能低于最低商务提现金额", 2 );
            }
            
            // 获取用户银行信息,如果用户选择的银行卡中用户ID不是本人则给出错误提示，并返回
        	intval($_POST['cardid']) >= 0 or sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	$oUserBank = new model_withdraw_UserBank($_POST['cardid']);
        	if ($oUserBank->UserId != $iUserId || $oUserBank->Status != 1){
        		sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	}
            
            $sBankName = $oUserBank->BankName;
            $iBankId = $oUserBank->BankId;
            $sProvince = $oUserBank->Province;
            $sBankCity = $oUserBank->City;
            /*if( mb_strlen($_POST['truename'],"UTF-8") < 2 || mb_strlen($_POST['truename'],"UTF-8") > 4 )
            {
                sysMsg( "请填写正确的开户人姓名", 2 );
            }*/
            $sTrueName = $oUserBank->AccountName;
            if( !preg_match("/^[0-9]{16,19}$/", $oUserBank->Account) )
            {
                sysMsg( "请填写正确的个人银行帐号", 2 );
            }
            $sBankNo   = $oUserBank->Account;
//            $oWithdraw = new model_withdrawel();
            $result    = $oWithdraw->withdrawelAdd( $iParentId, $iUserId, $iBankId, $sBankName, $sBankNo, $sProvince, $sBankCity, 
                                         $sTrueName, $fMoney, $aUserinfo['username']." 发起商务提现", $iAgentId, $iIsForCompany );
            if( $result === -3 )
            {
                sysMsg( "对不起，您的资金帐户被其他操作占用", 1 );
            }
            elseif( $result === -1009 )
            {
                sysMsg( "对不起，提现金额超出了可用余额", 2 );
            }
            elseif( $result > 2 )
            {
            	//($result-1000)
                sysMsg( "对不起，提现金额超出了可用余额，您的最大提现金额为 ".round( ($result - 1000), 2), 2) ;
            }
            elseif( $result === -6 )
            {//获取其他可用余额失败
                sysMsg( "请求超时，请重试", 2 );
            }
            elseif( $result === -7 )
            {//其他频道有负余额
                sysMsg( "其他频道有负余额，请先转帐将其补平", 2 );
            }
            elseif( $result === -41 )
            {
                $aLinks=array(array('url'=>url('security','businesswithdraw',array('check'=>$_REQUEST['check'])),
                                        'title'=>'商务提现') );
                sysMsg( "商务提现申请失败,您的资金帐户意外被锁，请联系管理员", 2, $aLinks );
            }
            elseif( $result === -5 )
            {
                $aLinks=array(array('url'=>url('security','businesswithdraw',array('check'=>$_REQUEST['check'])),
                                        'title'=>'商务提现') );
                sysMsg( "商务提现申请成功,但是资金帐户意外被锁，请联系管理员", 1, $aLinks );
            }
            elseif( $result === TRUE ) 
            {
                $aLinks=array(array('url'=>url( 'security', 'businesswithdraw', array('check'=>$_REQUEST['check']) ),
                                        'title'=>'商务提现') );
                sysMsg( "商务提现申请成功", 1, $aLinks );
            }
            else 
            {
                sysMsg( "商务提现申请失败", 2 );
            }
        }
    }
    
    
    
    /**
     * (总代用) 查看平台提现列表
     * @author James 091125
     * 
     * HTML 可选搜索条件:
     *   - 01, 提现处理状态    status    ( ''=全部, 0=未处理(默认), 1=失败, 2=成功 )
     *   - 02, 逻辑删除状态    isdel     ( ''=全部, 0=未删除, 1=已逻辑删除 )
     *   - 03, 处理管理员      adminname 
     *   - 04, 用户地址        ipaddr    
     *   - 05, 用户名          username
     *   - 06, 总代名          tproxyname
     *   - 07, 提现发起时间    sdate
     *   - 08, 提现截止时间    edate
     *   - 09, 处理开始时间    sdate2
     *   - 10, 处理截止时间    edate2
     */
    function actionUserDraw()
    {
        $oUser         = new model_user();
        $iUserId       = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId  = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            $iParentId = $iUserId;
        }
        else 
        {//获取总代ID
            $iParentId = $oUser->getTopProxyId( $iUserId );
        }
        if( $iParentId != $iUserId )
        {//只有总代和总代管理员能使用
            sysMsg( "没有权限", 2 );
        }
        
        //TODO：并行期平台提现分期上代码
        //获取操作用户的总代
        $aTempTopUser = $oUser->getTopProxyId( $iUserId, TRUE );
        if( empty($aTempTopUser) )
        {//获取数据失败则退出
            sysMsg( "没有权限", 2 );
        }
        $oConfig      = new model_config();
        /*$sAllowCashCT = $oConfig->getConfigs( 'allowPTcashTX' );
        $sAllowCashCT = empty($sAllowCashCT) ? "" : $sAllowCashCT;
        $aAllowedTopUser = explode( ",", $sAllowCashCT );
        if( !in_array( $aTempTopUser['username'], $aAllowedTopUser ) )
        {//不在指定总代范围内
            sysMsg( "没有权限", 2 );
        }*/
        //获取是否接受总代受理
        $sSyszdAcceptPTTX = $oConfig->getConfigs( 'syszdAcceptPTTX' );
        $sSyszdAcceptPTTX = empty($sSyszdAcceptPTTX) ? "" : $sSyszdAcceptPTTX;
        $aSyszdAcceptPTTX = explode( ",", $sSyszdAcceptPTTX );
        if( !in_array( $aTempTopUser['username'], $aSyszdAcceptPTTX ) )
        {//不在指定总代范围内
            sysMsg( "没有权限", 2 );
        }
//        unset($oConfig,$sAllowCashCT,$aAllowedTopUser,$sSyszdAcceptPTTX,$aSyszdAcceptPTTX);
        unset($oConfig,$sSyszdAcceptPTTX,$aSyszdAcceptPTTX);
        
        unset($oUser,$iParentId);
        // 01, 整理搜索条件
        $aSearch['status']     = isset($_GET['status']) ? $_GET['status'] : 0; // 默认未处理
//      $aSearch['isdel']      = isset($_GET['isdel'])  ? $_GET['isdel']  : 0; // 默认未删
//        $aSearch['adminname']  = isset($_GET['adminname']) ? daddslashes(trim($_GET['adminname'])) : "";
//        $aSearch['ipaddr']     = isset($_GET['ipaddr']) ? daddslashes(trim($_GET['ipaddr'])) : "";
        $aSearch['username']   = isset($_GET['username']) ? daddslashes(trim($_GET['username'])) : "";
//        $aSearch['tproxyname'] = isset($_GET['tproxyname']) ? daddslashes(trim($_GET['tproxyname'])) : "";
//        $aSearch['adminname']  = isset($_GET['adminname']) ? daddslashes(trim($_GET['adminname'])) : "";
        $aSearch['sdate']      = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 02:20', strtotime('-14 day') );
        $aSearch['edate']      = isset($_GET['edate']) ? trim($_GET['edate']) : "";
        $aSearch['sdate2']     = isset($_GET['sdate2']) ? trim($_GET['sdate2']) : "";
        $aSearch['edate2']     = isset($_GET['edate2']) ? trim($_GET['edate2']) : "";

        $aSearch['sdate']      = getFilterDate( $aSearch['sdate'],  'Y-m-d H:i' );
        $aSearch['edate']      = getFilterDate( $aSearch['edate'],  'Y-m-d H:i' );
        $aSearch['sdate2']     = getFilterDate( $aSearch['sdate2'], 'Y-m-d H:i' );
        $aSearch['edate2']     = getFilterDate( $aSearch['edate2'], 'Y-m-d H:i' );
        $aHtmlValue = array();

        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        $sWhere .= " AND `isforcompany`=0 AND a.`topproxyid`='".$iUserId."' ";
        if( $aSearch['status'] != -1 )
        { // 处理失败
            $sWhere .= " AND `status` = '".intval($aSearch['status'])."' ";
        }
        $aHtmlValue['st'] = $aSearch['status'];

//      if( $aSearch['isdel'] != -1 )
//      { // 删除状态
//          $sWhere .= " AND `isdel` = '".intval($aSearch['isdel'])."' ";
//      }
//      $aHtmlValue['del'] = $aSearch['isdel'];

//        if( $aSearch['adminname'] != '' )
//        { // 管理员名搜索
//            $sWhere .= " AND `adminname` = '".$aSearch['adminname']."' ";
//            $aHtmlValue['adminname'] = stripslashes_deep($aSearch['adminname']);
//        }

        if( $aSearch['username'] != '' )
        { // 提现申请人搜索
            $sWhere .= " AND c.`username` = '".$aSearch['username']."' ";
            $aHtmlValue['username'] = stripslashes_deep($aSearch['username']);
        }

//        if( $aSearch['tproxyname'] != '' )
//        { // 总代名搜索
//            $sWhere .= " AND d.`username` = '".$aSearch['tproxyname']."' ";
//            $aHtmlValue['tproxyname'] = stripslashes_deep($aSearch['tproxyname']);
//        }

//        if( $aSearch['ipaddr'] != '' )
//        { // 操作地址模糊搜索
//             if( strstr($aSearch['ipaddr'],'*') )
//            {
//                $sWhere .= " AND `clientip` LIKE '". str_replace( '*', '%', $aSearch['ipaddr'] ) ."' ";
//            }
//            else 
//            {
//                $sWhere .= " AND `clientip` = '".$aSearch['ipaddr']."' ";
//            }
//            $aHtmlValue['ipaddr'] = h(stripslashes_deep($aSearch['ipaddr']));
//        }

        if( $aSearch['sdate'] != '' )
        { // 提现发起时间 起始于...
            $sWhere .= " AND ( `accepttime` >= '".daddslashes($aSearch['sdate'])."' ) ";
            $aHtmlValue['sdate']  =  stripslashes_deep($aSearch['sdate']);
        }
        if( $aSearch['edate'] != '' )
        { // 提现发起时间 截止于...
            $sWhere .= " AND ( `accepttime` <= '".daddslashes($aSearch['edate'])."' ) ";
            $aHtmlValue['edate']  =  stripslashes_deep($aSearch['edate']);
        }
        if( $aSearch['sdate2'] != '' )
        { // 管理员处理时间 起始于...
            $sWhere .= " AND ( `finishtime` >= '".daddslashes($aSearch['sdate2'])."' ) ";
            $aHtmlValue['sdate2']  =  stripslashes_deep($aSearch['sdate2']);
        }
        if( $aSearch['edate2'] != '' )
        { // 管理员处理时间 截止于...
            $sWhere .= " AND ( `finishtime`!=0 AND `finishtime` <= '".daddslashes($aSearch['edate2'])."' ) ";
            $aHtmlValue['edate2']  =  stripslashes_deep($aSearch['edate2']);
        }

        /* @var $oWithDrawel model_withdrawel */
        $oWithDrawel = A::singleton('model_withdrawel');
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oWithDrawel->getUserWithDrawelList('', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages', $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 's', $aHtmlValue );
        $GLOBALS['oView']->assign( 'aUserList', $aResult['results'] ); // 数据分配
        $iAutoReflushSec = 90;
        $GLOBALS['oView']->assign( 'ur_here', '提现受理' );
        $GLOBALS['oView']->assign( 'sSysTopMessage', $aResult['affects']);
        $GLOBALS['oView']->assign( 'sSysAutoReflushSec', $iAutoReflushSec);
        $GLOBALS['oView']->assign( 'sSysMetaMessage', 
                        '<META http-equiv="REFRESH" CONTENT="'.$iAutoReflushSec.'" />'); // 自动刷新 for 财务
        //$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("report","withdrawel"), 'text'=>'清空过滤条件' ) );
        $oWithDrawel->assignSysInfo();
        $GLOBALS['oView']->display("security_userdraw.html");
        EXIT;
    }

    
    
    /*
     * (总代用)处理平台提现
     */
    function actionUserDrawEdit()
    {
        $oUser         = new model_user();
        $iUserId       = $_SESSION['userid'];
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId  = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
            $iParentId = $iUserId;
        }
        else 
        {//获取总代ID
            $iParentId = $oUser->getTopProxyId( $iUserId );
        }
        if( $iParentId != $iUserId )
        {//只有总代和总代管理员能使用
            sysMsg( "没有权限", 2 );
        }
        
        //TODO：并行期平台提现分期上代码
        //获取操作用户的总代
        $aTempTopUser = $oUser->getTopProxyId( $iUserId, TRUE );
        if( empty($aTempTopUser) )
        {//获取数据失败则退出
            sysMsg( "没有权限", 2 );
        }
        $oConfig      = new model_config();
        /*$sAllowCashCT = $oConfig->getConfigs( 'allowPTcashTX' );
        $sAllowCashCT = empty($sAllowCashCT) ? "" : $sAllowCashCT;
        $aAllowedTopUser = explode( ",", $sAllowCashCT );
        if( !in_array( $aTempTopUser['username'], $aAllowedTopUser ) )
        {//不在指定总代范围内
            sysMsg( "没有权限", 2 );
        }*/
        //获取是否接受总代受理
        $sSyszdAcceptPTTX = $oConfig->getConfigs( 'syszdAcceptPTTX' );
        $sSyszdAcceptPTTX = empty($sSyszdAcceptPTTX) ? "" : $sSyszdAcceptPTTX;
        $aSyszdAcceptPTTX = explode( ",", $sSyszdAcceptPTTX );
        if( !in_array( $aTempTopUser['username'], $aSyszdAcceptPTTX ) )
        {//不在指定总代范围内
            sysMsg( "没有权限", 2 );
        }
//        unset($oConfig,$sAllowCashCT,$aAllowedTopUser,$sSyszdAcceptPTTX,$aSyszdAcceptPTTX);
        unset($oConfig,$sSyszdAcceptPTTX,$aSyszdAcceptPTTX);
        
        unset($oUser,$iParentId);
    	$aLocation = array(0=>array("title" => "提现申请列表","url" => url("security","userdraw")));
        $iWithDrawelId = isset($_REQUEST["id"])&&is_numeric($_REQUEST["id"]) ? intval($_REQUEST["id"]) : 0;
        if( $iWithDrawelId == 0 )
        {
            sysMsg("提现申请ID错误", 2, $aLocation);
        }
        /* @var $oWithDrawel model_withdrawel */
        $oWithDrawel = A::singleton('model_withdrawel');
        if( !isset($_POST['flag']) || $_POST['flag']!='withdraw' )
        {//提现信息
        	$aWithDrawel = $oWithDrawel->getUserWithDrawelById( $iWithDrawelId, $iUserId );
	        if( empty($aWithDrawel) )
	        {
	            sysMsg("没有权限", 2, $aLocation);
	        }
	        // 管理员是否可以审核此条申请的标记
	        $aWithDrawel['opcanupdate'] = 0;
	        if( $aWithDrawel['adminid']==0 && $aWithDrawel['status']==0 && $aWithDrawel['finishtime']==0 )
	        {
	            $aWithDrawel['opcanupdate'] = 1;
	        }
	        $GLOBALS['oView']->assign( "s", $aWithDrawel );
	        $GLOBALS['oView']->assign( "ur_here", "审核提现申请" );
//	        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("security","userdraw"), 'text'=>'提现申请列表' ) );
	        $GLOBALS['oView']->display( "security_userdrawedit.html" );
	        EXIT;
        }
        else 
        {
	        // 0, 数据整理, 控制层的简单数据安全过滤
	        if( !isset($_POST['doaction']) || ( $_POST['doaction']!='set_failed' && $_POST['doaction']!='set_success' )
	        // (不检查流水号) || ( $_POST['doaction']=='set_success' && empty($_POST['bankcode']) )  
	        //    || intval($_POST['withdrawelid']) == 0 
	        )
	        {
	            sysMsg("数据初始错误,请检查", 2, $aLocation);
	        }
	        $sAction       = trim($_POST['doaction']);
	        $sMessage      = $sAction=='set_success' ? '' : trim($_POST['failedmsg']);
	
	        // 1, 控制层,对用户操作所引发的消息. 进行转发
	        /* @var $oWithDrawel model_withdrawel */
	        $oWithDrawel = A::singleton('model_withdrawel');
	        $iFlag = 0;
	        if( $sAction == 'set_success' )
	        {
	            $iFlag = $oWithDrawel->setWithdrawStatusByUser( $iWithDrawelId, 'SUCCESSED', $sMessage, $iUserId );
	        }
	        elseif( $sAction == 'set_failed' )
	        {
	            $iFlag = $oWithDrawel->setWithdrawStatusByUser( $iWithDrawelId, 'FAILED',$sMessage, $iUserId );
	        }
	        else
	        {
	            sysMsg("错误的行为参数", 2, $aLocation);
	        }
	        if( $iFlag > 0 )
	        {
	            sysMsg("操作成功", 1, $aLocation);
	        }
            elseif( $iFlag == -2 )
            {
                sysMsg("没有权限", 2, $aLocation);
            }
	        elseif( $iFlag == -4 )
	        {
	            sysMsg("没有权限", 2, $aLocation);
	        }
	        elseif( $iFlag == -10 )
	        {
	            sysMsg("操作失败, 原因: 账户资金临时被锁, 请稍后再试", 2, $aLocation);
	        }
	        elseif( $iFlag == -1004 )
	        {
	            sysMsg("操作失败, 原因: 频道资金数据失败", 2, $aLocation);
	        }
	        elseif( $iFlag == -1005 )
	        {
	            sysMsg("操作失败, 原因: 用户账户锁定失败,请稍后再试", 2, $aLocation);
	        }
	        elseif( $iFlag == -1007 )
	        {
	            sysMsg("操作失败, 原因: 账变记录插入失败,请稍后再试", 2, $aLocation);
	        }
	        elseif( $iFlag == -1008 )
	        {
	            sysMsg("操作失败, 原因: 账户金额更新失败", 2, $aLocation);
	        }
	        elseif( $iFlag == -1009 )
	        {
	            sysMsg("操作失败", 2, $aLocation);
	        }
	        else
	        {
	            sysMsg("操作失败", 2, $aLocation);
	        }
        }
    }


    /**
     * 合帐设置
     */
    function actionSetUnite()
    {
        $iUserId = $_SESSION['userid'];
        $oUser   = new model_user();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "对不起，您没有权限", 2 );
            }
        }
        //判断用户是否为总代，如果不为总代则没有权限
        if( FALSE == $oUser->isTopProxy( $iUserId ) )
        {
            sysMsg( "对不起，您没有权限", 2 );
        }
        if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
        {
            $this->actionCheckPass( 'security','setunite' );
            EXIT;
        }
        if( empty($_POST['flag']) || $_POST['flag'] == 'list' )
        {
            //获取总代下所有合帐
            $oUnite  = new model_userunite();
            $sSign = FALSE;
            if( $_SESSION['usertype'] == 2 && TRUE == $oUser->IsAdminSale($_SESSION['userid'])){
            	$sSign = TRUE;
            } else {
            	$sSign = FALSE;
            }
            $aUnites = $oUnite->getListById( $iUserId, $sSign );
            
            // 如果是总代销售管理员，则只能看到合账组中包含分配给自己用户的合账组。
            if (!empty($aUnites)){
            	if( $_SESSION['usertype'] == 2 && TRUE == $oUser->IsAdminSale($_SESSION['userid'])){
	            	$aSelfTaem =  $oUser->getAdminTeam($_SESSION['userid']);
	            	foreach ($aUnites as $k => $v){
	            		// 检查总代销售管理员是否能删除合账组
	            		if ($v['adminid'] == $_SESSION['userid']){
	            			$aUnites[$k]['delrights'] = 1;
	            		} else {
	            			$aUnites[$k]['delrights'] = 0;
	            		}
	            		
	            		// 检查总代销售管理员是否能够查看
	            		$aResult = array_intersect($v['user'], $aSelfTaem);
	            		if (empty($aResult)){
	            			unset($aUnites[$k]);
	            		}
	            	}
	            	
	            	foreach ($aUnites as $k => $v){
		            	$aUnites[$k]['user'] = implode(',', $v['user']);
		            }
	            }
            }
            
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( "unites", $aUnites );
            $GLOBALS['oView']->assign( 'ur_here','合账设置');
            $oUnite->assignSysInfo();
            $GLOBALS['oView']->display( "security_unitelist.html" );
            exit();
        }/*************************增加合帐*******************************/
        elseif( $_POST['flag'] == 'insert' )
        {
            //获取总代下所有的未设置合帐的一代列表
            $oUnite    = new model_userunite();
            $aUserInfo = $oUnite->getProxyList( $iUserId );
            //剔除不属于销售管理员管理范围内的用户
            if( $_SESSION['usertype'] == 2 && TRUE == $oUser->IsAdminSale($_SESSION['userid']))
            {
                foreach ($aUserInfo as $k => $v )
                {
                    if( FALSE == $oUser->isInAdminSale( $v['userid'], $_SESSION['userid']) )
                    {
                       unset($aUserInfo[$k]);
                    }
                }
            }
            $GLOBALS['oView']->assign( "check", $_REQUEST['check'] );
            $GLOBALS['oView']->assign( "users", $aUserInfo );
            $GLOBALS['oView']->display( "security_setunite.html" );
            exit();
        }
        elseif( $_POST['flag'] == 'setunite' )
        {
            if( empty($_POST['aliasname']) )
            {
                sysMsg( "请填写合并帐户别名" );
            }
            if( !preg_match("/^[0-9a-zA-Z]{4,20}$/i",$_POST['aliasname']) )
            {
                sysMsg( "请填写正确的合并帐户别名" );
            }
            if( empty($_POST['users']) || !is_array($_POST['users']) || count($_POST['users']) < 2 )
            {
                sysMsg( "请选择两个及两个以上要合并的帐户" );
            }
            $oUnite = new model_userunite();
            $iAdminId = 0;
            if ($_SESSION['usertype'] == 2){
            	$iAdminId = $_SESSION['userid']	;
            }
           	//剔除不属于销售管理员管理范围内的用户
           	$sTempUser = "";
            if( $_SESSION['usertype'] == 2 && TRUE == $oUser->IsAdminSale($_SESSION['userid']))
            {
                foreach ($_POST['users'] as $k => $v )
                {
                    if( FALSE == $oUser->isInAdminSale( $v, $_SESSION['userid']) )
                    {
                       $aResult = $oUser->getUserExtentdInfo($v);
                       $sTempUser .= $aResult['username'] . ',';
                    }
                }
            }
            if (!empty($sTempUser)){
            	$aLinks=array(array('url'=>url('security','setunite',array('check'=>$_REQUEST['check'])),
                                        'title'=>'合帐列表') );
            	$sTempUser = mb_substr($sTempUser, 0, -1, "utf-8");
            	sysMsg( "用户{$sTempUser}已不是您的下级用户", 2, $aLinks );
            }
            $result = $oUnite->insert( $iUserId, $_POST['aliasname'], $_POST['users'], $iAdminId );
            if( $result == FALSE )
            {
                sysMsg( "设置失败", 2 );
            }
            elseif( $result === -1 )
            {
                sysMsg( "合帐别名重复" );
            }
            elseif( $result === -2 )
            {
                sysMsg( "选择合帐的其中一些用户已在其他合帐中", 2 );
            }
            else
            {
                $aLinks=array(array('url'=>url('security','setunite',array('check'=>$_REQUEST['check'])),
                                        'title'=>'合帐列表') );
                sysMsg( "设置合帐成功", 1, $aLinks );
            }
        }/**********************************删除一个合帐*****************************/
        elseif( $_POST['flag'] == 'delete' )
        {
            if( empty($_POST['unid']) )
            {
                sysMsg( "请选择要删除的合帐" );
            }
            $oUnite = new model_userunite();
            
            // 总代销售管理员
            if( $_SESSION['usertype'] == 2 && TRUE == $oUser->IsAdminSale($_SESSION['userid'])){
            	$aResult = $oUnite->getOne("adminid", " proxyid = {$iUserId} AND aliasname = '{$_POST['unid']}'");
            	if ($aResult['adminid'] != $_SESSION['userid']){
            		sysMsg( "不能删除不属于自己的合账组" );
            	}
            }
            
            if( FALSE == ($oUnite->deleteById($iUserId, $_POST['unid'])) )
            {
                sysMsg( "删除合帐失败", 2 );
            }
            $aLinks=array(array('url'=>url('security','setunite',array('check'=>$_REQUEST['check'])),
                                        'title'=>'合帐列表') );
            sysMsg( "删除成功", 1, $aLinks );
        }/******************************修改一个合帐*******************************/
        else 
        {
            $aLinks=array(array('url'=>url('security','setunite',array('check'=>$_REQUEST['check'])),
                                        'title'=>'合帐列表') );
            sysMsg( "非法操作", 2, $aLinks );
        }
    }



    /**
     * 帐间互转
     */
    function actionSentUnite()
    {
        $iUserId  = $_SESSION['userid'];
        $iAgentId = 0;	//初始化总代管理员ID
        $oUnite   = new model_userunite();
        //如果为总代管理员，则当前用户调整到其总代ID
        if( $_SESSION['usertype'] == 2 )
        {
            $iAgentId = $iUserId;
            $oUser    = A::singleton("model_user");
            $iUserId  = $oUser->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
                sysMsg( "对不起，您没有权限", 2 );
            }
        }
        if( FALSE == $oUnite->isInUnite($iUserId) )
        {//检测是否在一个合帐设置中
            sysMsg( "对不起，您没有权限", 2 );
        }
        /////////////////////////////////////////////////////////
        if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
        {
            $this->actionCheckPass( 'security', 'sentunite' );
            EXIT;
        }
        //获取资金帐户信息
        $oUserFund = new model_userfund();
        $aUserInfo = $oUserFund->getFundByUser( $_SESSION['userid'] );
        if( empty($aUserInfo) )
        {
            sysMsg( "您的资金帐户被其他操作占用,请稍后再试", 1 );
        }
        if( empty($_POST['flag']) || ($_POST['flag']!= 'insert' && $_POST['flag']!= 'confirm')  )
        {
            //获取合帐的信息
            $aUnites = $oUnite->getUserUnite($iUserId);
            if( empty($aUnites) )
            {
                sysMsg("对不起，您没有权限",2);
            }
            $GLOBALS['oView']->assign( "user",   $aUserInfo );
            $GLOBALS['oView']->assign( "unites", $aUnites );
            $GLOBALS['oView']->assign( "check",  $_REQUEST['check'] );
            $GLOBALS['oView']->display( "security_sentunite.html" );
            EXIT;
        }
        elseif( $_POST['flag'] == 'insert' )
        {
            if( empty($_POST['sentto']) || !is_numeric($_POST['sentto']) )
            {
                sysMsg( "请选择要转入的帐号" );
            }
            if( empty($_POST['money']) || !is_numeric($_POST['money']) )
            {
                sysMsg( "请输入要转出的金额" );
            }
            $iSentTo = intval( $_POST['sentto'] );
            $fMoney  = floatval( $_POST['money'] );
            if( FALSE == $oUnite->isUnite( $iUserId, $iSentTo ) )
            {
                sysMsg( "操作失败，转出帐户和您不在同一个合帐中", 2 );
            }
            //获取转入者信息
            $oUser       = A::singleton( "model_user" );
            $aSentToData = $oUser->getUserInfo( $iSentTo, array('userid','username') );
            if( empty($aSentToData) )
            {
                sysMsg( "要转入的帐号不存在或者已删除", 2 );
            }
            $GLOBALS['oView']->assign( "user",   $aUserInfo );
            $GLOBALS['oView']->assign( "sentto", $aSentToData );
            $GLOBALS['oView']->assign( "money",  $fMoney );
            $GLOBALS['oView']->assign( "check",  $_REQUEST['check'] );
            $GLOBALS['oView']->assign( 'ur_here','账间互转');
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "security_sentunite2.html" );
            EXIT;
        }
        elseif( $_POST['flag'] == 'confirm' )
        {
            if( empty($_POST['sentto']) || !is_numeric($_POST['sentto']) )
            {
                sysMsg( "非法操作", 2 );
            }
            if( empty($_POST['money']) || !is_numeric($_POST['money']) )
            {
                sysMsg( "非法操作", 2 );
            }
            $iSentTo = intval( $_POST['sentto'] );
            $fMoney  = floatval( $_POST['money'] );
            if( FALSE == $oUnite->isUnite($iUserId, $iSentTo) )
            {
                sysMsg( "操作失败，转出帐户和您不在同一个合帐中", 2 );
            }
            $oUserFund = new model_userfund();
            $result    = $oUserFund->transferByUnite( $iUserId, $iSentTo, $fMoney, $iAgentId );
            if( $result === -1 )
            {
                sysMsg( "转出者资金帐户或者转入者资金帐户被其他操作占用", 1 );
            }
            elseif( $result === -1009 )
            {
                sysMsg( "对不起，转出金额超出了可用余额", 2 );
            }
            elseif( $result === -3 )
            {
                $aLinks=array(array('url' => url('security', 'sentunite', array('check' => $_REQUEST['check'])),
                                        'title' => '帐间互转') );
                sysMsg( "转帐成功,但是资金帐户意外被锁，请联系管理员", 1, $aLinks );
            }
            elseif( $result === TRUE ) 
            {
                $aLinks=array(array('url' => url('security', 'sentunite', array('check' => $_REQUEST['check'])),
                                        'title' => '帐间互转') );
                sysMsg( "转帐成功", 1, $aLinks );
            }
            else 
            {
                sysMsg( "转帐失败", 2 );
            }
        }
    }



    /**
     * 频道转帐, 显示转行界面
     * @author Tom
     */
    function actionTransfer()
    {
        /**
         * 1, 根据 URL $_GET['currentChannelId'] 获取当前频道ID, 默认为 0 (银行)
         * Post => 
         *     [toChannelId] => 1
         *     [fmoney] => 751294
         *     [submit] => 提交
         *     SYS_CHANNELID => 当前频道ID
         */ 
        if( !empty($_POST['flag']) && $_POST['flag'] == 'doTranfer' )
        { // 处理转账过程
            // 1.1 简单判断
            if( !isset($_POST['toChannelId']) || !isset($_POST['fmoney'])
                || !is_numeric($_POST['toChannelId']) || !is_numeric($_POST['fmoney']) ||
                $_POST['fmoney'] <= 0 )
            {
                sysMsg( "转账相关数据不符合规范", 2 );
            }
            $oUserFund = new model_userfund();
            $sFields   = " u.`userid`,u.`username`,uf.`availablebalance`,u.`authtoparent` ";
//            patch 2/9/10 
            $oUser  = A::singleton("model_user");
            $iOpUserid = $_SESSION['usertype'] == 2  ?  $oUser->getTopProxyId( $_SESSION['userid'] )  : $_SESSION['userid'];
//            end patch
            $aUserinfo = $oUserFund->getFundByUser( intval($iOpUserid) , $sFields );
            if( empty($aUserinfo) )
            {
                sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2 );
            }
            if( $aUserinfo['availablebalance']<=0 || $_POST['fmoney']>$aUserinfo['availablebalance'] )
            {
                sysMsg( "您的帐户资金不足，无法完成转账", 2 );
            }
            // 2.1 初始化数据, 并启用 "转账调度器"  model_transferdispatcher()
            $aTranfer['iUserId']         = intval( $iOpUserid );
            $aTranfer['iFromChannelId']  = intval( SYS_CHANNELID ); // 银行平台默认 0
            $aTranfer['iToChannelId']    = intval( $_POST['toChannelId'] );
            $aTranfer['fMoney']          = floatval( $_POST['fmoney'] );
            $aTranfer['sMethod']         = 'USER_TRAN'; // 用户转账

                        
            // 2.2 进行资金密码检查
            /* @var $oUserFund model_userfund */
            $oUserFund = A::singleton("model_userfund");
            $sFields   = " ut.`userid`,ut.`username`,uf.`availablebalance` ";
//            patch 2/9/10 
            $oUser  = A::singleton("model_user");
            $iOpUserid = $_SESSION['usertype'] == 2  ?  $oUser->getTopProxyId( $_SESSION['userid'] )  : $_SESSION['userid'];
//            end patch
            $aUserinfo = $oUserFund->getFundByUser( intval($iOpUserid), $sFields );
            if( empty($aUserinfo) )
            {
                sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2 );
            }
            if( $aUserinfo['availablebalance']<=0 || $_POST['fmoney']>$aUserinfo['availablebalance'] )
            {
                sysMsg( "您的帐户资金不足，无法完成转账", 2 );
            }
            
            // 强制为低频ID  2/21/2010
			$aTranfer['iUserId'] = intval( $iOpUserid ); 
            // 2.3 调用 PASSPORT平台 API.转账调度器
           
            $oChannelApi = new channelapi( 0, 'channelTransitionDispatcher', TRUE );
            $oChannelApi->setTimeOut(15);            // 整个转账过程的超时时间, 可能需要微调
            $oChannelApi->sendRequest( $aTranfer );  // 发送转账请求给调度器
            $mAnswers    = $oChannelApi->getDatas();    // 获取转账 API 返回的结果
            if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
            {
                // 需要进行日志记录, 成功执行第一个API事务, 执行第二个失败
                $sErrorMsg = isset($mAnswers['data']) ? $mAnswers['data'] : '';
                sysMsg( "抱歉, 转账失败.请留意您的账变信息\\n Develop Debug [$sErrorMsg]", 2 );
            }
            else 
            {
                $aLocation  = array( 0=>array( "title" => "继续: 频道转账", "url" => url( 'security', 'transfer' ) ));
                sysMsg( "您的转账操作已完成", 1, $aLocation );
            }
        }
        elseif(!empty($_POST['flag']) && $_POST['flag'] == 'showTranfer')
        {
        	/**
        	 * 确认页面
        	 */
            if( !isset($_POST['toChannelId']) || !isset($_POST['fmoney'])
                || !is_numeric($_POST['toChannelId']) || !is_numeric($_POST['fmoney']) )
            {
                sysMsg( "转账相关数据不符合规范", 2 );
            }
        	$oUserFund = new model_userfund();
            $sFields   = " u.`userid`,u.`username`,uf.`availablebalance`,u.`authtoparent` ";
//            patch 2/9/10 
            $oUser  = A::singleton("model_user");
            $iOpUserid = $_SESSION['usertype'] == 2  ?  $oUser->getTopProxyId( $_SESSION['userid'] )  : $_SESSION['userid'];
//            end patch
            $aUserinfo = $oUserFund->getFundByUser( intval($iOpUserid), $sFields );
            if( empty($aUserinfo) )
            {
                sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2 );
            }

        	if( $aUserinfo['availablebalance']<=0 || $_POST['fmoney']>$aUserinfo['availablebalance'] )
            {
            	sysMsg( "您的帐户资金不足，无法完成转账", 2 );
            }

            $oChannel = new model_channels();
            $aChannel = $oChannel->channelGetAll( ' `id`, `channel` ', ' `isdisabled`=0 AND `pid`=0 ' );
            $aChannelData = array();
            if( SYS_CHANNELID == 0 )
            { // 如果为银行平台, 则显示其他所有开放的频道
                foreach( $aChannel AS $v )
                {
                    $aChannelData[ $v['id'] ] = $v['channel'];
                }
                //TODO_a高频、低频并行前期临时程序
                // 5/7/2010 取消高频list
                //$aChannelData[99] = "高频";
            }
            else
            {
                $aChannelData[] = '银行大厅';
            }            
            if( empty($aChannelData) ) 
            {
                sysMsg( "转账平台未开启, 请稍后再试", 2 );
            }
            $GLOBALS['oView']->assign( "s",            $_POST );
            $GLOBALS['oView']->assign( "aChannelData", $aChannelData );
            $GLOBALS['oView']->assign( "user",         $aUserinfo );
            $GLOBALS['oView']->assign( 'ur_here','频道转账');
            $oChannel->assignSysInfo();
        	$GLOBALS['oView']->display("security_transfer2.html");
        	EXIT;
        }

        /**
         * 平台转账 (显示页) 
         *   1, 数据收集整理, 正确性效验
         *   2, 根据 SYS_CHANNELID 初始化频道列表 ( SELECT BOX )
         */
        if( empty($_POST) )
        { // 显示转账界面
        	// 客服部 2009-10-30 要求, 取消平台转账时的资金密码验证 by Tom
        	/*
            if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
            {
                $this->actionCheckPass( 'security', 'transfer' );
                EXIT;
            }
            */

            $oUserFund = new model_userfund();
            $sFields   = " u.`userid`,u.`username`,uf.`availablebalance`,u.`authtoparent` ";
//            patch 2/9/10 
            $oUser  = A::singleton("model_user");
            $iOpUserid = $_SESSION['usertype'] == 2 ?  $oUser->getTopProxyId( $_SESSION['userid'] )  : $_SESSION['userid'];
//            end patch
            $aUserinfo = $oUserFund->getFundByUser( intval($iOpUserid), $sFields );
            if( empty($aUserinfo) )
            {
                sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2 );
            }

            if( $aUserinfo['availablebalance'] <= 0 )
            {
            	$aUserinfo['availablebalance'] = 0;
            }
            // 对4位精度的金额进行2位精度取整, 不进行四舍五入
            $aUserinfo['availablebalance'] = floor($aUserinfo['availablebalance']*100) / 100;

            $oChannel = new model_channels();
            $aChannel = $oChannel->channelGetAll( ' `id`, `channel` ', ' `isdisabled`=0 AND `pid`=0 ' );
            $aChannelData = array();
            if( SYS_CHANNELID == 0 )
            { // 如果为银行平台, 则显示其他所有开放的频道
                foreach( $aChannel AS $v )
                {
                    $aChannelData[ $v['id'] ] = $v['channel'];   
                }
                //TODO_a高频、低频并行前期临时程序 
                // 5/7/2010 取消高频list
                //$aChannelData[99] = "高频";
            }
            else
            {
                $aChannelData[] = '银行大厅';
            }
            
            if( empty($aChannelData) ) 
            {
                sysMsg( "转账平台未开启, 请稍后再试", 2 );
            }
            $GLOBALS['oView']->assign( "aChannelData", $aChannelData );
            $GLOBALS['oView']->assign( "user",         $aUserinfo );
            $GLOBALS['oView']->assign( 'ur_here','频道转账');
            $oChannel->assignSysInfo();
            $GLOBALS['oView']->display( "security_transfer.html" );
            EXIT;
        }
        EXIT; // 未捕获的操作
    }
    
    
    /**
     * 在线充值（用户个人历史）
     * 4/15/2010
     */
    function actionOnlineLoadHistory(){

    	//	检查用户是否有权限使用在线支付 
   		$oUser = A::singleton('model_user');
		$iUserId = intval($_SESSION['userid']);
    	
        
   		if ($oUser->checkAuthUserPayment($iUserId) === false){
        	sysMsg( "没有权限", 0, array( 0=>array( "title" => "版本信息", "url" => url( 'help', 'version' ) )) );
        	exit;
        }
        
    	$aLinks  = array( 0=>array( "title" => "在线充值", "url" => url( 'security', 'onlineload' ) ));

    	// 总代管理员使用总代ID查询
    	if( $_SESSION['usertype'] == 2 ) {
    		$iUserId = $oUser->getTopProxyId( $iUserId );
    		
			if( empty($iUserId) ){
				sysMsg( "系统忙,稍候重试", 0, $aLinks );
				unset($oUser);
				exit;
			}
			
        }
        
    	$aSearch['sdate']      = isset($_GET['sdate']) ? daddslashes(trim($_GET['sdate'])) : date('Y-m-d 02:20');
	    $aSearch['edate']      = isset($_GET['edate']) ? daddslashes(trim($_GET['edate'])) : date('Y-m-d 02:20', strtotime('+1 day'));
	    
	    $aSearch['sdate']      = getFilterDate( $aSearch['sdate'],  'Y-m-d H:i' );
	    $aSearch['edate']      = getFilterDate( $aSearch['edate'],  'Y-m-d H:i' );
	    
    	$aSearch['p']  = isset($_GET['p'])  ? intval($_GET['p'])  : 1;    // 分页用1
	    $aSearch['pn'] = isset($_GET['pn']) ? intval($_GET['pn']) : 15;   // 分页用2
		$aParam = array('PageSize' => $aSearch['pn'],
			'Page' => $aSearch['p'],
			'UserId' => $iUserId,
			'LoadStartTime' => $aSearch['sdate'],
			'LoadEndTime' => $aSearch['edate'],
			'LostStatus' => intval('-1')
		);

		$oPP = new model_pay_payportinfo();
		
        $oLoadHistory = new model_pay_loadlist($aParam,'','array');
    	$aLoadList = $oLoadHistory->Data;
    	foreach ($aLoadList AS &$aLL){
    		$aLL['load_currency'] = $oPP->getCurrencyStr($aLL['load_currency']);
    		//$aLL['load_amount'] =  str_replace('.0000','',$aLL['load_amount']);
   
    	}
    	
    	$oPager = new pages( $oLoadHistory->TotalCount, $aSearch['pn'], 2);
    	
    	$GLOBALS['oView']->assign( 'ur_here','在线充值 >> 充值历史');
    	$GLOBALS['oView']->assign( 'pages', $oPager->show() );
    	$GLOBALS['oView']->assign( 's', $aSearch );
        $GLOBALS['oView']->assign( 'Loadlist',$aLoadList);
        $oLoadHistory->assignSysInfo();
        $GLOBALS['oView']->display( "security_onlineload_history.html", "onlineload" );
        unset($oPP,$oLoadHistory,$oPager,$oUser);
        exit;
    }
    
    
    /**
     * 在线充值支付 
     * 3/12/2010 Jim
     * 
     */
	function actionOnlineLoad()
    {
		$oUser = A::singleton('model_user');
    	$iUserId = intval($_SESSION['userid']);
    	// array(一代ID,总代ID) ,如果一代未绑定分账户,则获取总代的
		$aUserProxyId = ( isset($_SESSION['lvproxyid']) && isset($_SESSION['lvtopid']) )  
							? array( intval($_SESSION['lvproxyid']), intval($_SESSION['lvtopid']) )  : 0;
        
    	$aLinks1  = array( 0=>array( "title" => "版本信息", "url" => url( 'help', 'version' ) ));
		
    	// 在 系统参数"禁止转账时间" 禁止充值
    	$oConfigd = new model_config();
		$sDenyTime = $oConfigd->getConfigs('zz_forbid_time');
		$aDenyTime = explode('-',$sDenyTime);
		// 24小时值时间值，无前导0
		$sRunNow = date('G:i');
		$bRunTimeChk = true;
		if ( ($sRunNow > $aDenyTime[1]) || ($sRunNow < $aDenyTime[0]) ){
			$bRunTimeChk = false;
		}
		// 系统参数 "连续充值最小间隔 (秒)"
		$iPayMinBetween = $oConfigd->getConfigs('pay_min_between_seconds');
		if ( ($iPayMinBetween < 0) ||  !is_numeric($iPayMinBetween) ) $iPayMinBetween = 0;
		
    	if ($aUserProxyId == 0){
        	sysMsg( "数据不足", 0, $aLinks1 );
        	unset($oUser,$oConfigd);
	        exit;
        }
        
    	// 总代管理员
    	if( $_SESSION['usertype'] == 2 ) {
    		$iUserId = $oUser->getTopProxyId( $iUserId );
    		
			if( empty($iUserId) ){
				sysMsg( "ID错误", 0, $aLinks1 );
				unset($oUser,$oConfigd);
				exit;
			}
			
			$aUserProxyId = array( intval('0'), intval($iUserId) );
        }
        
        // 检查用户是否有权限使用在线支付 
        if ($oUser->checkAuthUserPayment($iUserId) === false){
        	sysMsg( "没有权限", 0, $aLinks1 );
        	unset($oUser,$oConfigd);
        	exit;
        }
        $_GET['flag'] = !isset($_GET['flag']) ? '' : $_GET['flag'];
        if (!isset($_REQUEST["ReturnData"]) ) $_REQUEST["ReturnData"]=0;
        
    	if( ($_REQUEST["ReturnData"] == 0) && $_GET['flag']!='sent' && $_GET['flag'] != 'startload' ) // || !$_REQUEST["Data"])
        {
        	//---充值(第一步)： 选择支付接口,即充值方式

	        // 在时间上禁止充值
    		/*if ( $bRunTimeChk === true ){
				sysMsg('系统结算时间,暂停充值', 0, $aLinks1);
				unset($oUser,$oConfigd);
				exit;
			}*/
        	
        	//获取该用户可用 PayAccount  
        	//区别前台调用  $this->MGR=false (默认) 且有UserId
        	$oValidPayAccount = new model_pay_payaccountlimit();
        	$oValidPayAccount->UserId = $aUserProxyId;
        	$aValidPayAccount = $oValidPayAccount->validAccList();
        	
        	
        	if ( empty($aValidPayAccount) ){
        		 sysMsg( "没有可用的充值方式", 0, $aLinks1 );
        		 unset($oValidPayAccount,$aValidPayAccount);
        		 exit;
        	}
        	
        	// 生成用于查询的 已被绑定给用户的各有效ID数组
        	$aValidPayAccountList=$aValidPayportList=$aVPayAccLi=array(); 
        	foreach ($aValidPayAccount AS $aArray){
        		$aValidPayAccountList[] = $aArray['pp_acc_id'];
        		$aValidPayportList[] = $aArray['ppid'];
        		//仅支持一对一关系(一用户只绑定同一接口下同一账户),直接获取该用户某接口下绑定的pay accound id
        		$aVPayAccLi[$aArray['ppid']] = $aArray['pp_acc_id'];	
        	}
			
        	// 银行列表实例 
        	//$oBanklist = new model_withdraw_paybankList();
        	
        	//获取所有有效支付接口ID 列表数据
        	$oPayport = new model_pay_payaccountlist($aValidPayportList,'id'); 
        	// 最后一个参数说明： 权限参数  充值1 提现2 批量提现4 查询8 人工提现16
        	$aMyPayport = $oPayport->specialList($aValidPayAccountList,true,'aid','aid',1);
        	
			//生成该用户可用的payport信息列表
			/*$aMyPayport = array();
			foreach ($aVPayAccLi AS $iKey => $iVal){
				$aTmp = array();
				/ * print_rr($aPayportList[$iKey]);
				print_rr($aPayportList[$iVal]);* /
				/ * * /
				echo '<br> Key:'.$iKey.' Val:'.$iVal;
				$aTmp =  array_merge( $aPayportList[$iKey], $aPayAccountList[$iVal]);
				$aMyPayport[] = $aTmp;
			}
			print_rr($aMyPayport);*/
        	//生成该用户可用的payport信息列表
        	/*foreach ($aPayportList AS $iKey => &$aPayL){
					//没有充值功能,不显示
        			if ( ($aPayL['payport_attr'] & 1) != 1 ){
        				unset($aPayportList[$iKey]);
        			}else{
        			
        			// 将匹配的payaccount id加入到payport信息列表数组中
        			$aPayL['payaccountid'] 	= $aVPayAccLi[$aPayL['id']];
        			$aPayL['currencycn'] 	= $oPayport->converCurrencyStr($aPayL['currency']);
        			$aPayL['currencyunit'] 	= $oPayport->converUnitStr($aPayL['currency']);
        			
        			// 过滤干扰Javascript显示的字符  
        			$aPayL['payport_intro'] = json_encode( 
        							str_replace("\"","", nl2br($aPayL['payport_intro'] ) ) 
        							);

        			//TODO: 获取接口银行列表;
        			$oBanklist->ApiId = $aPayL['id'];
    				$oBanklist->Status=1;
    	        	$oBanklist->Init();
        	    	$aBanklist = $oBanklist->Data;
            		if ( (count($aBanklist) > 0) && !empty($aBanklist) ) {
           				foreach ($aBanklist AS $aAi){
           					$sBank[] = array( 
           								'bankname' => $aAi['bank_name'], 
           								'banklogo' => urlencode($aAi['logo']) 
           								); 
           				}
           				$aPayL['payport_banklist'] = json_encode($sBank);
                	}else{
            			$aPayL['payport_banklist'] = '';
            			sysMsg( "没有合适的银行列表", 0, $aLinks1 );
        		 		unset($oValidPayAccount,$oBanklist,$oPayport,$aValidPayAccount);
        		 		exit;
            		}
            		
        			} // end if & 1

        	}*/

        	
        	unset($oValidPayAccount,$oPayport,$oBanklist);
        	unset($aValidPayAccountList,$aValidPayAccount);
        	$GLOBALS['oView']->assign( 'ur_here','在线充值 >> 充值方式列表');
        	$GLOBALS['oView']->assign( 'Payportlist',$aMyPayport);
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "security_onlineload_list.html", "onlineload" );
            exit;
        }
        elseif( isset($_GET['flag']) && $_GET['flag'] == 'startload')
        {
        	//---充值(第二步)：用户录入金额
        	
        	// 在时间上禁止充值
    		if ( $bRunTimeChk === true ){
				sysMsg('系统结算时间,暂停充值', 0, $aLinks1);
				unset($oUser,$oConfigd);
				exit;
			}
			
			// 连续两次充值时间控制
			$iLastLoadTime = $_SESSION['LastLoadTime']  ?  intval($_SESSION['LastLoadTime'])  :  0;
			if ( ( intval(date('U')) - $iLastLoadTime) < $iPayMinBetween  ){
				sysMsg('两次充值操作请间隔30秒', 0, $aLinks1);
				unset($oUser,$oConfigd);
				exit;
			}
			
			
        	$iTranstype = ( isset($_REQUEST['transtype']) && is_numeric($_REQUEST['transtype']) )  ?  intval($_REQUEST['transtype']) : 0;
        	
        	if ($iTranstype <= 0){
        		unset($oUser);
            	sysMsg("充值方式选择有误",2);
            	exit;
        	}

        	//检查用户是否有权使用PayAccount支付接口分账户 (关系是否绑定并激活)
        	if ($oUser->checkUserPayportAccount($iTranstype, $aUserProxyId) === false){
        		sysMsg("没有合适的充值方式",0);
        		unset($oUser);
            	exit; 
            	
        	}
        	
        	//提取系统设置 手续费计算方式
        	$oConfig = new model_config();
        	$iFeeCountType = $oConfig->getConfigs('pay_deduct_in_mode');
        	$sNotices = $oConfig->getConfigs('pay_notice_load');
        	
        	$_SESSION['paydeductpass'] = $iFeeCountType;
        	
            $oPayport = new model_pay_payaccountinfo($iTranstype);
            $oPayport->getAccountDataObj();
            
            	
            //检查该分账户是否具备API充值功能,否则转为原始充值
            if ($oPayport->AccAttrLoad == 0){
            		//TODO: to old load type
            	echo '(非支付接口工作方式)';
            	exit;
            }
            
            
            //充值流程下一步所需的基本信息,用于用户操作界面上 计算手续费 显示 等
            $aPayportinfo = array(
            	'minamount' => intval($oPayport->LoadLimitMinPer),
            	'maxamount' => intval($oPayport->LoadLimitMaxPer),
            	'feedown' 	=> floatval($oPayport->LoadFeePerDown),
            	'feeperdown' => floatval($oPayport->LoadFeePercentDown),
            	'feestep' 	=> floatval($oPayport->LoadFeeStep),
            	'feeup' 	=> floatval($oPayport->LoadFeePerUp),
            	'feeperup' 	=> floatval($oPayport->LoadFeePercentUp),
            	'transtype' => intval($oPayport->AId),
            	'feetype' 	=> $iFeeCountType ? intval($iFeeCountType) : 0,
            	'limitlen'	=> (strlen( $oPayport->LoadLimitMaxPer ) - 2),
            	'optnotice'	=>	$sNotices
            );
   			$aPayportinfo['currency'] = $oPayport->AccCurrency ? $oPayport->AccCurrency : $oPayport->Currency;
   			$aPayportinfo['currencycn'] = $oPayport->AccCurrency 
   											? $oPayport->getCurrencyStr( $oPayport->AccCurrency , 'cn' ) 
   											: $oPayport->getCurrencyStr( $oPayport->Currency , 'cn' );
   											
            //获取可用银行列表
            $oBanklist = new model_withdraw_paybankList();
            $oBanklist->Status=1;
            $oBanklist->ApiId = $oPayport->PaySlotId;
            $aBanklist = $oBanklist->getLogo();
			if ( empty($aBanklist) ){
				sysMsg("没有合适的银行",0);
        		unset($oUser,$oBanklist,$oPayport);
            	exit; 
			}
			
            unset($oBanklist,$oPayport);
        	$GLOBALS['oView']->assign( 'loginout_url', url('default','logout'));
            $GLOBALS['oView']->assign( 'ur_here','在线充值 >> 录入金额');
            $GLOBALS['oView']->assign( 'username',$_SESSION['username']);
            $GLOBALS['oView']->assign( 'Banklist',$aBanklist);
            $GLOBALS['oView']->assign( 'Payportinfo',$aPayportinfo);
            $oUser->assignSysInfo();
            $GLOBALS['oView']->display( "security_onlineload.html", "onlineload" );
            exit;
            
        }
        elseif($_REQUEST["ReturnData"] && !isset($_REQUEST['flag']) )
        {
        	//---充值(第四步)： 接收返回 显示返回信息页面
        	
        	$aLinks  = array( 0=>array( "title" => "在线充值", "url" => url( 'security', 'onlineload' ) ));
        	$aCloseLink = array( 0 => array('url' => 'close'),1 => array('url' => 'index.php?controller=report&action=bankreport') );
        
        	// 检查用户是否有权限在线充值  正常情况，客户应该仍然处于在线状态
        	//$oUser  = A::singleton("model_user");
      		//$oUser  = model_user();
      		// 解析编码的Payport名称 (此名称对应payport程序用名)
      		$sPayport = strtolower( base64_decode( base64_decode($_REQUEST['PPN']) ) );
        	//定义合法的Payport名, 'yeepay', 'ecapay'
        	$aVP =  array('1topay');
        	
      		//处理返回数据,
      		//检查 PPN合法性
        	if (array_search($sPayport,$aVP) !== false)
        	{
      			//接收从receive程序提交的返回字串
      			//为了安全 系统receive程序独立于系统，由其接收数据之后，打包所有数据到 ReturnData 变量中，传递回A进行解析处理 
      			// 一个标准的receive 传回两个参数 ReturnData PPN
      			
        		//  1topay 处理结束后传递 RST参数 成功值为success
      			if ($sPayport == '1topay')
      			{
      				$sRST = base64_decode($_GET['RST']);
        		
        			if ( ($sRST == '1') || ($sRST == 'success') )
        			{
        				sysMsg( '充值成功！请查看您的银行帐变', 0, $aCloseLink );
        				exit;
        			}
        			else
        			{
        				sysMsg( '充值失败！请稍候,系统将自动处理', 0, $aCloseLink );
        				exit;
        			}
        			exit;
        		}
        		
        		
      		}
      		else
      		{
        		// 没有匹配的接口
        		sysMsg('严重错误的请求！', 0, $aCloseLink);
        		exit;
        	}
      		
        	
            
        }
        elseif( isset($_GET['flag']) && $_GET['flag'] == 'sent')
        {
        	//---充值(第三步)： 整理并提交充值请求
        	
        	// 在时间上禁止充值
    		if ( $bRunTimeChk === true ){
				sysMsg('系统结算时间,暂停充值', 0, $aLinks1);
				unset($oUser,$oConfigd);
				exit;
			}
        	
			// 连续两次充值时间控制
			$iLastLoadTime = $_SESSION['LastLoadTime']  ?  intval($_SESSION['LastLoadTime'])  :  0;
			if ( ( intval(date('U')) - $iLastLoadTime) < $iPayMinBetween  ){
				sysMsg('两次充值操作请间隔30秒', 0, $aLinks1);
				unset($oUser,$oConfigd);
				exit;
			}
			$_SESSION['LastLoadTime'] = date('U');
			
			
        	$iTranstype = ( isset($_REQUEST['transtype']) && is_numeric($_REQUEST['transtype']) )  ?  intval($_REQUEST['transtype']) : false;
        	
        	$aCloseLink2 = array( 0 => array('url' => 'close'),1 => array('url' => 'index.php?controller=security&action=onlineload&flag=startload&transtype='.$_GET['transtype']) );
        	$sTips = ', (确定关闭本窗口,重试一次)';
        	
        	if ($iTranstype <= 0){
            	sysMsg("充值方式有误".$sTips, 0, $aCloseLink2 );
            	exit;
            }

            //检查该PayAccount支付接口分账户，用户是否有权使用(是否绑定并激活)
        	if ($oUser->checkUserPayportAccount($iTranstype,$aUserProxyId) === false){
        		unset($oUser);
        		sysMsg("没有合适的充值方式",0, $aCloseLink2);
        		unset($oUser);
            	exit;
        	}
        	
            if ( empty($_REQUEST['loadamount']) || (!is_numeric($_REQUEST['loadamount'])) ) {
            	sysMsg("充值金额有误".$sTips, 0, $aCloseLink2);
            	unset($oUser);
            	exit;
            }
 			
            // GET银行代码  银行代码 只应有数字字母与-符号 不应超过10位长
            $sBankcode = $_REQUEST['bankcode'];
        	if ( !isset($sBankcode) || preg_match("/[^A-Z0-9\-]/",$sBankcode) || ( strlen($sBankcode) > 10) ) {
             	sysMsg("银行代码有误".$sTips, 0, $aCloseLink2 );
             	unset($oUser);
             	exit;
            }
            
            
            //整理 充值金额
            $iLoadamount =  number_format( floatval($_GET['loadamount']) ,2,'.',''); 
            
        	//整理数据,发出请求
        	if ( isset($iTranstype) && isset($iLoadamount) ){
        		//记录用户当前使用域名
        		$_SESSION['domain'] = $_SERVER['HTTP_HOST'];

        		//提取系统设置 手续费计算方式
        		$oConfig = new model_config();
        		$iFeeCountType = intval( $oConfig->getConfigs('pay_deduct_in_mode') );
        		//$iFeeCountType = 1 为内扣法收取手续费
        		
        		//获取支付接口信息(包含此处用到的分账户信息)
        		$oPayport = new model_pay_payaccountinfo($iTranstype);
            	$oPayport->getAccountDataObj();
            	
			
            	//检查实例化数据有效性;
            	if ( empty($oPayport->PaySlotId) || empty($oPayport->Currency) || empty($oPayport->PaySlotName) 
            		|| empty($oPayport->PayportAttr) || empty($oPayport->AccName) )
            	{
            		sysMsg('银行网络忙,请稍候重试!', 0, $aCloseLink2);
            		unset($oPayport);
                	exit;
            	}
            	
        		//获取合法银行列表 检查银行代码正确性
           	 	$oBanklist = new model_withdraw_paybankList();
            	$oBanklist->Status=1;
            	$oBanklist->ApiId = $oPayport->PaySlotId;
            	$aBanklist = $oBanklist->getLogo();
				if ( empty($aBanklist) ){
					sysMsg("没有合适的银行",0);
        			unset($oUser,$oBanklist,$oPayport);
            		exit; 
				}
				$bChkBankcode = false;
				foreach ($aBanklist AS $aBl){
					if ($sBankcode == $aBl['bank_code']){
						$bChkBankcode = true;
						break;
					}
				}
				if ($bChkBankcode === false){
					sysMsg("银行代码错误",0);
        			unset($oUser,$oBanklist,$oPayport);
            		exit; 
				}
            	
				//检查充值金额范围
            	if ( ($iLoadamount < $oPayport->LoadLimitMinPer)
            	|| ($iLoadamount > $oPayport->LoadLimitMaxPer) ){
            		sysMsg('充值金额超限,请重试!', 0, $aCloseLink2);
            		unset($oPayport);
                	exit;
            	}
            	
            	// 检查手续费扣取方式是否改变
            	if ($_SESSION['paydeductpass'] != $iFeeCountType){
            		sysMsg('手续费扣取方法已经改变,为避免争议,请重新开始充值', 0, $aCloseLink2);
            		unset($oPayport);
            		exit;
            	}
            	//手续费
            	$oPayport->PayDeduct = $iFeeCountType ? intval($iFeeCountType) : 0;
            	$oPayport->OptType = 'onlineload';
            	$aLoadFee = $oPayport->paymentFee($iLoadamount);
				$sTransTime = date('Y-m-d H:i:s');
				//获取所属总代用户名
				$aTopInfo = $oUser->getTopProxyId($iUserId,true);
				if ( ($aTopInfo === FALSE) || empty($aTopInfo['username']) ){
					sysMsg('系统忙,请稍候再试一次 01', 0, $aCloseLink2);
					unset($oPayport,$oUser);
					exit;
				}
				
            	// 充值历史记录 数据赋值
        		$oLoadRecord = new model_pay_loadinfo();
        		$oLoadRecord->UserId = $iUserId;
        		$oLoadRecord->UserName = $_SESSION['username'];
                $oLoadRecord->LoadType = $oPayport->PaySlotId;
                $oLoadRecord->LoadAmount = $iLoadamount;
                $oLoadRecord->LoadFee = floatval($aLoadFee[1]);
                $oLoadRecord->FeeType = intval($oPayport->PayDeduct);
                $oLoadRecord->LoadCurrency = strlen($oPayport->AccCurrency) >= 3 ? $oPayport->AccCurrency : $oPayport->Currency;
                $oLoadRecord->TransTime = $sTransTime;
                $oLoadRecord->PayName = $oPayport->PaySlotName;
                $oLoadRecord->PayAttr = $oPayport->PayportAttr;
                $oLoadRecord->AccId = $iTranstype;
                $oLoadRecord->AccName = $oPayport->AccName;
                // 组装ID替代字串
                $sSpecName = $oLoadRecord->makeSpecName($aTopInfo['username']);
                if (strlen($sSpecName) != 12){
                	sysMsg('系统忙,请稍候再试一次 02', 0, $aCloseLink2);
					unset($oPayport,$oUser,$oLoadRecord);
					exit;
                }
                $oLoadRecord->SpecName = $sSpecName;
                
                // 初始化充值历史记录表 以 L+(last insert id) 作为充值单号
                unset($sUniqueid);
                $iInsertLastId = $oLoadRecord->initLoadRecord($oPayport->AccSiteId);
                //判断记录是否成功
                if ($iInsertLastId > 0){
                	$sUniqueid = $iInsertLastId;
					//$sUniqueid = 'L'.$iInsertLastId;
                }else{
                	sysMsg('系统忙,请稍候再试', 0, $aCloseLink2);
                	unset($oLoadRecord,$oPayport);
                	exit;
                }
                
                //根据手续费计算方式 计算实际向银行发起金额
                if (intval($iFeeCountType) > 0){
                	//内扣法
                	$iSendLoadamount = $iLoadamount;
                	if ($iLoadamount < floatval($aLoadFee[1]) ){
                		sysMsg('所有费用将成为手续费', 0, $aCloseLink2);
                		unset($oLoadRecord,$oPayport);
                		exit;
                	}
                }else{
                	//外扣法
                	$iSendLoadamount = $iLoadamount + floatval($aLoadFee[1]);
                }
                
                // 支付接口数据封装类参数数组
                $aInput = array('userid' => $iUserId,		//用户ID
        			'amount' => $iSendLoadamount,			//实际发起金额
        			'uniqueid' => $sUniqueid,				//唯一订单号
                	'uniqueidstr' => $sSpecName,				//发往第三方平台的替代字串
              	  	'transtime' => $sTransTime,				//订单时间戳
                	'bankcode' => $sBankcode 				//银行代码
                	);
                
                // 支付请求数据整理 根据支付接口程序用名称 数据封装类 
                $aValidClsAddName = array('1topay');
                
                if ( array_search($oPayport->PayportName, $aValidClsAddName) === false){
                	unset($oLoadRecord,$oPayport,$oUser);
                	sysMsg('不支持的数据请求方式', 0, $aCloseLink);
                	exit;
                }
                
                $sPayportDataAPIClassName = 'model_pay_apidata'.strtolower($oPayport->PayportName);
        		$oPayment = new $sPayportDataAPIClassName($iTranstype, 'payment', $aInput, 0);
        		$oPayment->Model = 'payment';
        		if ($oPayment === false){
        			sysMsg('载入错误'.$sTips, 0, $aCloseLink);
        			exit;
        		}
        		
        		$oPayment->send();
        		exit;
        	}else{
        		sysMsg('请求数据错误'.$sTips, 0, $aCloseLink2);
        		exit;
        	}
         	
        }
        else 
        {
        	//--- 不设默认请求方式
        	unset($oUser);
        	echo '错误请求';
        	exit;
        }
        
    }
    
    
    /**
     * 在线提现支付接口列表
     * @version 	v1.0	2010-03-09
     * @author 		louis
     *
     */
    function actionApiList(){    	
    	$oUser = new model_user();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		if( $iUserType == 2 )
        {
            //如果为总代管理员，则当前用户调整到其总代ID
            $iUserId = $oUser->getTopProxyId( $iUserId );
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        
		// 权限检查
		
    	if ($oUser->checkAuthUserPayment($iUserId) === false){
    		sysMsg("对不起，您没有操作权限！", 2);
    	}
		
    	// 资金密码检查
    	if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
        {
            $this->actionCheckPass('security','apilist');
            EXIT;
        }
    	
    	// 获取用户每天提现次数
		$oConfig = new model_config();
		
		// 获取用户当天发起提现申请的次数，用户取消的记录不纳入
		$oFODetailList = new model_withdraw_fodetailslist();
		$oFODetailList->UserId = $iUserId;
		$oFODetailList->StartTime = date("Y-m-d", time()) . " 00:00:00";
		$oFODetailList->UnStatus = 4;  // 用户自行取消的不纳入
		$oFODetailList->init();
		
		/*if (count($oFODetailList->Data) >= $oConfig->getConfigs('withdrawtimes'))
			sysMsg("对不起，您今天已没有可用提现次数！", 1, $aLinks, 'top');
*/
    	
        
    	/*//获取该用户可用 PayAccount
    	$oValidPayAccount = new model_pay_payaccountlimit();
    	$oValidPayAccount->UserId = $_SESSION['lvproxyid'] > 0 ? $_SESSION['lvproxyid'] : $_SESSION['lvtopid'];
    	$aValidPayAccount = $oValidPayAccount->validAccList();
    	
    	
    	foreach ($aValidPayAccount as $key => $account){
    		$aValidPayAccountList[] = $account['ppid'];
    	}
    	
    	
    	//获取所有 接口的信息数据
    	$oPayport   = new model_pay_payportlist(array(),'','array');
		$aPayportList = $oPayport->Data;
		
		//生成该用户可用的payport信息列表
    	$aValidPayList = array();
    	foreach ($aPayportList AS &$aPayL){
    		$iGetKey = array_search($aPayL['id'],$aValidPayAccountList);
    		
    		if ($iGetKey !== false ){
    			 //将匹配的payaccount id加入到payport信息列表数组中
    			$aPayL['payaccountid'] = $aValidPayAccount[$iGetKey]['pp_acc_id'];
    			//处理换行符 Javascript将显示内容
    			$aPayL['payport_intro'] = str_replace("\n","",$aPayL['payport_intro']);
    			$aValidPayList[] = $aPayL;
    		}
    	}*/
    	
    	/*// 如果一个提现接口都没有，则提示用户现在不接受在线提现操作
    	if (count($aValidPayList) == 0){
    		sysMsg("对不起！暂时不接受在线提现操作！", 1);
    	}
    	
    	if (count($aValidPayList) == 1 && $aValidPayList[0]['payport_name'] == "self"){*/
    	// 判断用户是否达到当天的最大提现限制，如果达到则跳转到提现历史页面，否则跳转到提现页面
    	
    	// 检查用户是否具有提现权限
    	$oUser = new model_user();
    	
    	if (count($oFODetailList->Data) >= $oConfig->getConfigs('withdrawtimes') || !$oUser->checkAuthUserPayment($iUserId)){
    		redirect("?controller=security&action=withdrawlist");
    	} else{
    		redirect("?controller=security&action=onlinewithdraw");
    	}
    		
    	/*} else {
    		$GLOBALS['oView']->assign( 'ur_here', '在线提现');
	    	$GLOBALS['oView']->assign( 'apiList', $aValidPayList);
	    	$oValidPayAccount->assignSysInfo();
	        $GLOBALS['oView']->display( "security_apilist.html" );
	        EXIT;
    	}*/
    }
    
    
    /**
     * 立即提现操作
     * 
     * @version 	v1.0	2010-03-09
     * @author 		louis
     */
    function actionOnlineWithdraw(){
    	$aLinks = array(
			0 => array(
				'title' => "返回提现记录页面",
				'url'	=> "?controller=security&action=withdrawlist"
			)
		);
		
		// 在 系统参数"禁止转账时间" 禁止提现
    	$oConfigd = new model_config();
		$sDenyTime = $oConfigd->getConfigs('zz_forbid_time');
		$aDenyTime = explode('-',$sDenyTime);
		// 24小时值时间值，无前导0
		$sRunNow = date('G:i');
		$bRunTimeChk = true;
		if ( ($sRunNow > $aDenyTime[1]) || ($sRunNow < $aDenyTime[0]) ){
			$bRunTimeChk = false;
		}
		
		
		// 在时间上禁止提现
		if ( $bRunTimeChk === true ){
			sysMsg('系统结算时间,暂停提现', 1, $aLinks);
			exit;
		}
		
		$oUser = new model_user();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		if( $iUserType == 2 )
        {
            //如果为总代管理员，则当前用户调整到其总代ID
            $iUserId = $oUser->getTopProxyId( $iUserId );
            $aResult = $oUser->getUserExtentdInfo($iUserId);
            $sUserName = $aResult['username'];
            // 用户名调整为总代名称
            
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        } else {
        	$sUserName = $_SESSION['username'];
        }
        
		// 检查用户是否具有提现权限
    	if (!$oUser->checkAuthUserPayment($iUserId)){
    		sysMsg("对不起，您没有操作权限！", 2, $aLinks, 'self');
    	}
    	$oConfig = new model_config();
    	
    	// 通过提现接口名称查询ID，前期手工提现，只能利用提现接口名称查询ID
		$oPayPortInfo = new model_pay_payaccountinfo('myself');
		$oPayPortInfo->getAccountDataObj();
    	
    	// 获取用户每天提现次数
		$oUserFund = new model_userfund();
		// 获取用户当天已经成功发起提现申请的次数
		$oFODetailList = new model_withdraw_fodetailslist();
		$oFODetailList->UserId = $iUserId;
		$oFODetailList->StartTime = date("Y-m-d", time()) . " 00:00:00";
		$oFODetailList->UnStatus = 4;  // 用户自行取消的不纳入
		$oFODetailList->init();
		$iTodayWithdrawTimes = $oFODetailList->TotalCount;
				
		if ($iTodayWithdrawTimes >= $oConfig->getConfigs('withdrawtimes'))
			sysMsg("对不起，您今天已没有可用提现次数！", 2, $aLinks, 'self');

        if( empty($_POST['flag']) || $_POST['flag']!='confirm' && $_POST['flag']!='withdraw' )
        {
        	
        	// 获取用户绑定的银行卡信息
        	$oUserBankList = new model_withdraw_UserBankList();
        	$oUserBankList->UserId = $iUserId;
        	$oUserBankList->Status = 1;
        	$oUserBankList->init();
        	
        	if (empty($oUserBankList->Data)){
        		$aLinks = array(
    				0 => array(
        				'title' => "卡号绑定页面",
        				'url'	=> "?controller=security&action=adduserbank"
    				)
    			);    	
        		sysMsg( "您尚未绑定银行卡，请先进行卡号绑定！", 2, $aLinks, 'self' );
        	}
        	
        	$oFODetail = new model_withdraw_fundoutdetail();
        	$oFODetail->Digit = 4; // 只显示四位卡号
        	foreach ($oUserBankList->Data as $k => $value){
	    		// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
	    		$oFODetail->Account = $value['account'];
	    		$oUserBankList->Data[$k]['account'] = $oFODetail->hiddenAccount();
    		}
    		
    		
    		// 获取用户可提现金额
    		$aUserAccInfo = array();
    		$oUserFund = new model_userfund();

    		// 总代和总代管理员
    		if (($_SESSION['lvtopid'] == $iUserId && $_SESSION['usertype'] == 1) || ($_SESSION['lvtopid'] == 0 && $_SESSION['usertype'] == 2)){
    			$oWithdraw = new model_withdrawel();
				$aUserAcc = $oUserFund->getProxyFundList($iUserId);
		        $aUserAccInfo['amount'] = $oWithdraw->getCreditUserMaxMoney( $iUserId, $aUserAcc[0]['TeamAvailBalance'] );
		        if ($aUserAccInfo['amount'] == "error"){
		        	sysMsg( "获取其它频道余额失败，请稍后重试", 2 );
		        }
    		} else {
		        $sFields   = " uf.`availablebalance`";
		        $aUserinfo = $oUserFund->getFundByUser( $iUserId, '', 0, false );
		        $aUserAccInfo['amount'] = $aUserinfo['availablebalance'];
    		}
    		
			
	        if( empty($aUserAccInfo) ){
	            sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2 );
	        }
	        
	        // 判断手续费是为内扣法，还是外扣法，1为内扣法，0为外扣法
	        if ($oConfig->getConfigs("pay_deduct_in_mode") != 1){
	        	// 获取外扣法下，用户最大可提现金额
	    		$oPayPortInfo->OptType = "withdraw";
	    		$oPayPortInfo->PayDeduct = $oConfig->getConfigs("pay_deduct_in_mode");
	        	$aCharge = $oPayPortInfo->paymentFee($aUserAccInfo['amount']);
	        	$fMaxAmount = $aUserAccInfo['amount'] - $aCharge[1];
	        	
	        } else {
	        	$fMaxAmount = $aUserAccInfo['amount'];
	        }
    		
    		/*// 通过api_id查询api支付接口信息和币种信息
        	$oPayInfo = new model_pay_payaccountinfo($aPayPortInfo['id']);
        	$aPayInfos = $oPayInfo->getAccountData();*/
	    	$GLOBALS['oView']->assign( 'ur_here', '提现申请');
	    	$GLOBALS['oView']->assign( 'username', $sUserName);
	    	$GLOBALS['oView']->assign( 'api_id', $oPayPortInfo->Id);
	    	$GLOBALS['oView']->assign( 'api_nickname', $oPayPortInfo->PayportNickname);
	    	$GLOBALS['oView']->assign( 'amount', $aUserAccInfo['amount']);
	    	$GLOBALS['oView']->assign( 'maxlen', strlen($aUserAccInfo['amount']));
	    	$GLOBALS['oView']->assign( 'maxamount', min($fMaxAmount, $oPayPortInfo->DrawLimitMaxPer));
	    	$GLOBALS['oView']->assign( 'pay_deduct_in_mode', $oConfig->getConfigs("pay_deduct_in_mode"));
	    	$GLOBALS['oView']->assign( 'banklist', $oUserBankList->Data);
	    	$GLOBALS['oView']->assign( 'withdrawtimes', $oConfig->getConfigs("withdrawtimes"));
	    	$GLOBALS['oView']->assign( 'todaywithdrawtimes', $iTodayWithdrawTimes);
	    	$GLOBALS['oView']->assign( 'drawfeeperdown', $oPayPortInfo->DrawFeePerDown); // 提现按次手续费(下限)
	    	$GLOBALS['oView']->assign( 'drawfeepercentdown', $oPayPortInfo->DrawFeePercentDown); // 提现按金额手续费百分比(下限)
	    	$GLOBALS['oView']->assign( 'drawfeemin', $oPayPortInfo->DrawFeeMin); // 提现最低手续费
	    	$GLOBALS['oView']->assign( 'drawfeemax', $oPayPortInfo->DrawFeeMax); // 提现最高手续费
	    	$GLOBALS['oView']->assign( 'drawfeestep', $oPayPortInfo->DrawFeeStep); // 界定金额
	    	$GLOBALS['oView']->assign( 'drawfeeperup', $oPayPortInfo->DrawFeePerUp); // 提现按次手续费(上限)
	    	$GLOBALS['oView']->assign( 'drawfeepercentup', $oPayPortInfo->DrawFeePercentUp); // 提现按金额手续费百分比 (上限)
	    	$GLOBALS['oView']->assign( 'drawlimitminper', $oPayPortInfo->DrawLimitMinPer); // 单次最低提现额
	    	$GLOBALS['oView']->assign( 'drawlimitmaxper', $oPayPortInfo->DrawLimitMaxPer); // 单次最高提现额
	    	$GLOBALS['oView']->assign( 'notice', $oConfigd->getConfigs('pay_notice_withdraw')); // 提现到账时间说明
	    	$oUserFund->assignSysInfo();
	        $GLOBALS['oView']->display( "security_onlinewithdraw.html" );
        } else if ($_POST['flag'] == 'confirm'){
        	$fTempMoney = number_format($_POST['real_money'], 2, '.', '');
        	$_POST['charge'] = isset($_POST['charge']) ? $_POST['charge'] : '';
        	// 数据检查
        	if ($fTempMoney <= 0 || !is_numeric($fTempMoney) || empty($_POST['api_id']) || 
        		$_POST['charge'] < 0 || empty($_POST['api_nickname']) || !is_numeric($_POST['bankinfo'])) {
        			 sysMsg( "您提交的数据有误，请核对后重新提交！", 2 );
        	}
//        	$fTempMoney = number_format($_POST['real_money'], 2, '.', '');
        	
        	// 获取用户可提现金额，总代用户要加上信用余额
    		$aUserAccInfo = array();
    		$oUserFund = new model_userfund();

    		if (($_SESSION['lvtopid'] == $iUserId && $_SESSION['usertype'] == 1) || ($_SESSION['lvtopid'] == 0 && $_SESSION['usertype'] == 2)){
    			$oWithdraw = new model_withdrawel();
				$aUserAcc = $oUserFund->getProxyFundList($iUserId);
		        $aUserAccInfo['amount'] = $oWithdraw->getCreditUserMaxMoney( $iUserId, $aUserAcc[0]['TeamAvailBalance'] );
		        if ($aUserAccInfo['amount'] == "error"){
		        	sysMsg( "获取其它频道余额失败，请稍后重试", 2 );
		        }
    		} else {
		        $sFields   = " uf.`availablebalance`";
		        $aUserinfo = $oUserFund->getFundByUser( $iUserId, '', 0, false );
		        $aUserAccInfo['amount'] = $aUserinfo['availablebalance'];
    		}
	        if( empty($aUserAccInfo) ){
	            sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2 );
	        }
	        
	        // 获取手续费
        	/*$oApiData = new model_pay_payaccountinfo($aPayPortInfo['id']);
        	$oApiData->OptType = 'withdraw';*/
        	$oPayPortInfo->OptType = "withdraw";
        	$oPayPortInfo->PayDeduct = $oConfig->getConfigs("pay_deduct_in_mode");
        	$aCharge = $oPayPortInfo->paymentFee($fTempMoney);
        	
	        if ($oConfig->getConfigs("pay_deduct_in_mode") != 1){ // 外扣
	        	$fTotalMoney = $aCharge[0]; // 实扣金额
	        	$fWithdraw = $aCharge[0] - $aCharge[1]; // 提现金额
	        	$fMoney  = $aCharge[0] - $aCharge[1]; // 到账金额
	        } else { // 内扣
	        	$fTotalMoney = $fTempMoney; // 实扣金额
	        	$fWithdraw = $fTempMoney; // 提现金额
	        	$fMoney = $aCharge[0]; // 到账金额
	        }
	        
        	// 检查提现金额是否超出可提现金额
        	if ($fTotalMoney > $aUserAccInfo['amount'] || $fTotalMoney < $oPayPortInfo->DrawLimitMinPer || $fTotalMoney > $oPayPortInfo->DrawLimitMaxPer){
        		sysMsg( "提现金额超出可提现金额范围", 2 );
        	}
        	
        	if ($fTotalMoney < $oPayPortInfo->DrawLimitMinPer){
        		sysMsg( "提现金额不能低于最低提现金额", 2 );
        	}
        	
        	if ($fTotalMoney > $oPayPortInfo->DrawLimitMaxPer){
        		sysMsg( "提现金额不能高于最高提现金额", 2 );
        	}
        	
        	if ($oConfig->getConfigs("pay_deduct_in_mode") == 1){ // 内扣
	    		// 如果提现金额（扣除手续费后）小于手续费
	        	if ($fTotalMoney <= $aCharge[1] || $aCharge[0] <= 0){
	        		sysMsg("提现金额为" . $fTempMoney . "元，手续费为" . $aCharge[1] . "元，提现金额小于手续费，请调整您的提现金额后重新提交", 2);
	        	}
        	}
        	
        	
        	// 获取用户银行信息,如果用户选择的银行卡中用户ID不是本人则给出错误提示，并返回
        	intval($_POST['bankinfo']) >= 0 or sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	$oUserBank = new model_withdraw_UserBank($_POST['bankinfo']);
        	if ($oUserBank->UserId != $iUserId || $oUserBank->Status != 1){
        		sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	}
			
        	// 卡号只显示后四位
        	$oFODetail = new model_withdraw_fundoutdetail();
        	$oFODetail->Digit = 4; // 只显示四位卡号
        	$oFODetail->Account = $oUserBank->Account;
    		
        	
        	$GLOBALS['oView']->assign( 'ur_here', '提现申请确认');
        	$GLOBALS['oView']->assign( 'username', $sUserName);
        	$GLOBALS['oView']->assign( 'api_id', $oPayPortInfo->Id);
        	$GLOBALS['oView']->assign( 'api_nickname', $oPayPortInfo->PayportNickname);
        	$GLOBALS['oView']->assign( 'availablebalance', $aUserAccInfo['amount']);
        	$GLOBALS['oView']->assign( 'totalmoney', $fTotalMoney);
        	$GLOBALS['oView']->assign( 'withdraw', $fWithdraw);
        	$GLOBALS['oView']->assign( 'money', $fMoney);
        	$GLOBALS['oView']->assign( 'charge', $aCharge[1]);
        	$GLOBALS['oView']->assign( 'bank', $oUserBank->BankName);
        	$GLOBALS['oView']->assign( 'province', $oUserBank->Province);
        	$GLOBALS['oView']->assign( 'city', $oUserBank->City);
        	$GLOBALS['oView']->assign( 'branch', $oUserBank->Branch);
        	$GLOBALS['oView']->assign( 'account_name', $oUserBank->AccountName);
        	$GLOBALS['oView']->assign( 'account', $oFODetail->hiddenAccount());
        	$GLOBALS['oView']->assign( 'bankinfoid', $_POST['bankinfo']);
	    	$oUserFund->assignSysInfo();
	        $GLOBALS['oView']->display( "security_confirmwithdraw.html" );
	        EXIT;
        } else if ($_POST['flag'] == 'withdraw'){
        	// 数据检查
        	$fTempMoney = number_format($_POST['real_money'], 2, '.', '');
        	if (empty($iUserId) || !is_numeric($iUserId) || $fTempMoney <= 0 || !is_numeric($fTempMoney) || 
        		!is_numeric($_POST['api_id']) || !is_numeric($_POST['bankinfoid']) || empty($_POST['api_nickname'])) {
        			$aLinks = array(
        				0 => array(
	        				'title' => "返回提现申请页面",
	        				'url'	=> "?controller=security&action=onlinewithdraw&id=" . $_POST['api_id']
        				)
        			);    			
        			 sysMsg( "请填写完整的资料", 2, $aLinks, 'self' );
        	}
//        	$fTempMoney = number_format($_POST['real_money'], 2, '.', '');
        	// 通过api_id查询api支付接口信息和币种信息
        	/*$oPayInfo = new model_pay_payaccountinfo($aPayInfo['id']);
        	$aPayInfo = $oPayInfo->getAccountData();*/
        	
        	// 检查手续费计算是否正确(预留)
        	/*$oApiData = new model_pay_payaccountinfo($_POST['acc_id']);
        	$oApiData->OptType = "withdraw";*/
        	$oPayPortInfo->OptType = "withdraw";
        	$oPayPortInfo->PayDeduct = $oConfig->getConfigs("pay_deduct_in_mode");
        	$aCharge = $oPayPortInfo->paymentFee($fTempMoney);
        	
        	if ($oConfig->getConfigs("pay_deduct_in_mode") != 1){
        		$fSourceMoney = $aCharge[0];
	        	$fMoney = $aCharge[0] - $aCharge[1];
	        } else {
	        	$fSourceMoney = $fTempMoney;
	        	$fMoney = $aCharge[0];
	        }
        	
        	// 获取用户银行信息,如果用户选择的银行卡中用户ID不是本人则给出错误提示，并返回
        	$_POST['bankinfo'] = isset($_POST['bankinfo']) ? $_POST['bankinfo'] : '';
        	intval($_POST['bankinfo']) >= 0 or sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	$oUserBank = new model_withdraw_UserBank($_POST['bankinfoid']);
        	if ($oUserBank->UserId != $iUserId || $oUserBank->Status != 1){
        		sysMsg("您提交的银行信息有误，请核对后重新提交！", 2);
        	}
        	
        	// 获取用户绑定的银行卡对应的银行代码
        	$oApiWithdrawBank = new model_withdraw_ApiWithdrawBank();
        	$oApiWithdrawBank->BankId = $oUserBank->BankId;
        	$oApiWithdrawBank->Status = 1;
        	$aResult = $oApiWithdrawBank->getInfoByBankId();
        	
        	if (empty($aResult['bank_code'])){
        		sysMsg("您选择的银行卡,本平台已不支持，请选择其它银行卡进行提现！", 2);
        	}
        	
        	
        	// 用户信息数组
        	$aUserInfo = array();
        	$aUserInfo['username'] = $sUserName;
        	// 写入账变数据数组
        	$oOrders   = new model_orders();
        	$aOrders = array();
        	$aOrders['iFromUserId'] = $iUserId; // (发起人) 用户id
        	$aOrders['iToUserId'] = 0; // (关联人) 用户id
        	$aOrders['iOrderType'] = ORDER_TYPE_ZXTX; // 账变类型
        	$aOrders['fMoney'] = $fSourceMoney; // 账变的金额变动情况
        	$aOrders['sDescription'] = '提现申请'; // 账变的描述
        	$aOrders['iChannelID'] = 0; // 发生帐变的频道ID
        	$aOrders['iAdminId'] = 0; // 管理员id
        	// 提现申请数据数组
        	$aFundOut = array();
    		$aFundOut['api_id']			= $oPayPortInfo->Id;
    		$aFundOut['acc_id']			= $oPayPortInfo->AId > 0 ? $oPayPortInfo->AId : 0;
    		$aFundOut['api_name']		= $oPayPortInfo->PayportName;
    		$aFundOut['api_nickname']	= $oPayPortInfo->PayportNickname;
    		$aFundOut['acc_name']		= $oPayPortInfo->AccName;
    		$aFundOut['money_type']		= $oPayPortInfo->AccCurrency != "" ? $oPayPortInfo->AccCurrency : $oPayPortInfo->Currency;
    		$aFundOut['total_money']	= floatval($fSourceMoney);
    		$aFundOut['money']			= $fMoney;
    		$aFundOut['charge']			= $aCharge[1];
    		$aFundOut['bank_id']		= $oUserBank->Id;
    		$aFundOut['bank_code']		= $aResult['bank_code'];
    		$aFundOut['bank']			= $oUserBank->BankName;
    		$aFundOut['province_id']	= $oUserBank->ProvinceId;
    		$aFundOut['province']		= $oUserBank->Province;
    		$aFundOut['city_id']		= $oUserBank->CityId;
    		$aFundOut['city']			= $oUserBank->City;
    		$aFundOut['branch']			= $oUserBank->Branch;
    		$aFundOut['account_name']	= $oUserBank->AccountName;
    		$aFundOut['account']		= $oUserBank->Account;
    		$aFundOut['userbank_id']	= $oUserBank->Id;
    		$aFundOut['IP']				= getRealIP();
    		$aFundOut['CDNIP']			= getRealIP();
    		$oApply = new model_withdraw_fundoutapply( $aUserInfo, $aOrders, $aFundOut );
    		$aLinks = array(
				0 => array(
					'title' => "返回提现记录页面",
					'url'	=> "?controller=security&action=withdrawlist"
				)
			);
			
    		if ($oApply->iError > 0){
    			sysMsg( "提现申请已成功！", 1, $aLinks, 'self' );
    		} elseif ($oApply->iError == -1) {
    			sysMsg( "您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self' );
    		} elseif ($oApply->iError == -3) {
    			sysMsg( "您的用户信息有误，请核对后重新提交！", 2, $aLinks, 'self' );
    		} elseif ($oApply->iError == -2) {
    			sysMsg( "您的资金帐户因为其他操作被锁定，请稍后重试", 2, $aLinks, 'self' );
    		} elseif ($oApply->iError == -4) {
    			sysMsg( "获取您的其它频道余额失败，请稍后重试！", 2, $aLinks, 'self' );
    		} elseif ($oApply->iError == -5) {
    			sysMsg( "您的其它频道有负余额，请先转账将其填平！", 2, $aLinks, 'self' );
    		} elseif ($oApply->iError == -6) {
    			sysMsg( "提现失败", 2, $aLinks, 'self' );
    		} elseif ($oApply->iError == -7) {
    			sysMsg( "您提交的提现金额超出了提现金额范围！", 2, $aLinks, 'self' );
    		} elseif ($oApply->iError == -9){
    			sysMsg( "系统结算时间,暂停提现！", 1, $aLinks, 'self' );
    		} elseif ($oApply->iError == -100){
    			sysMsg( "获取您的可提现金额失败，请稍后重试！", 2, $aLinks, 'self' );
    		} else {
    			sysMsg( "提现失败", 2, $aLinks, 'self' );
    		}
        }
    }
    

    
    /**
     *  安全中心，卡号绑定
     * 
     * @version 	v1.0	2010-04-10
     * @author 		louis
     *
     */
    public function actionUserBankInfo(){
        // 注销掉新卡绑定前置效验，内置检查参数
        $_SESSION['iBandingCheck'] = 0;

    	// 总代管理员不允许卡号绑定
    	if ($_SESSION['usertype'] == 2){
    		sysMsg("您没有操作权限！", 2);
    	}
    	// 资金密码检查
    	if( empty($_REQUEST['check']) || $_REQUEST['check'] != $_SESSION['checkcode'] )
        {
            $this->actionCheckPass('security','userbankinfo');
            EXIT;
        }
    	$oUserBankList = new model_withdraw_UserBankList();
    	$oUserBankList->Status = 1; // 只提取可用银行信息
    	$oUserBankList->UserId = $_SESSION['userid']; // 只提取可用银行信息
    	$oUserBankList->init();
    	// 如果没有绑定过银行卡则直接跳转到银行卡绑定页面
    	if ($oUserBankList->TotalCount == 0){
    		redirect( url("security","adduserbank"), 0);
            EXIT;
    	}
    	
    	// 获取系统配置信息，得到用户可绑定银行卡数量。
    	$oConfig = new model_config();
    	$iNum = $oConfig->getConfigs("kahaobangding");
    	
    	
    	$oFODetail = new model_withdraw_fundoutdetail();
        $oFODetail->Digit = 4; // 只显示四位卡号
    	foreach ($oUserBankList->Data as $k => $value){
    		//// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
    		$oFODetail->Account = $value['account'];
    		$oUserBankList->Data[$k]['account'] = $oFODetail->hiddenAccount();
    	}
    	$GLOBALS['oView']->assign( 'ur_here', '卡号绑定列表');
    	/*if ($oUserBankList->TotalCount < $iNum){
    		$GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("security","adduserbank"), 'text'=>'增加银行卡绑定' ) );
    	}*/
    	$GLOBALS['oView']->assign( 'banklist', $oUserBankList->Data);
    	$GLOBALS['oView']->assign( 'num', $iNum);
    	$GLOBALS['oView']->assign( 'binded', $oUserBankList->TotalCount);
    	$oUserBankList->assignSysInfo();
        $GLOBALS['oView']->display( "security_userbankinfo.html" );
        EXIT;
    }
    
    
    /**
     * 绑定银行卡信息
     *
     * @version 	v1.0	2010-04-11
     * @author 		louis
     */
    public function actionAddUserBank(){
    	// 总代管理员不允许卡号绑定
    	if ($_SESSION['usertype'] == 2){
    		sysMsg("您没有操作权限！", 2);
    	}
		
    	// 修改php.ini文件中[mbstring]模块信息，使得php下和js下，判断中、英文字符长度相同。
    	@ini_set("mbstring.language", "Neutral");
    	@ini_set("mbstring.internal_encoding", "UTF-8");
    	/*@ini_set("mbstring.http_input", "UTF-8");
    	@ini_set("mbstring.http_output", "UTF-8");
    	@ini_set("mbstring.encoding_translation", "On");
    	@ini_set("mbstring.detect_order", "UTF-8");
    	@ini_set("mbstring.substitute_character", "none");*/
        
    	$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
    	
        // 2/22/2011 新卡绑定效验
        if ( $_SESSION['iBandingCheck'] != 1)
        {
            $aThisLink = array(array('url'=>url( 'security', 'adduserbank' ), 'title'=>'卡号绑定') );

            if ( $_POST['flag'] == 'newbinding' && $_POST['id'] > 0 )
            {
                // 效验卡号 
                $oUserBankCard = new model_withdraw_UserBank( $_POST['id'] );
                if ( $oUserBankCard->Account === $_POST['account'] 
                        && $oUserBankCard->UserId == $_SESSION['userid']
                        && $oUserBankCard->AccountName == $_POST['account_name'] )
                {
                    $_SESSION['iBandingCheck'] = 1;
                    @header ("Location: index.php?controller=security&action=adduserbank");
                    exit;
                }
                else
                {
                    $_SESSION['iBandingCheck'] = 0;
                    sysMsg('输入的卡号信息不符',2,$aThisLink);
                    exit;
                }
            }
            else
            {
                // 随机抽取一个卡号
                $oRandomCard = new model_withdraw_UserBank();
                $aRandomCard = $oRandomCard->getRandomCardByUser( $_SESSION['userid'] );
                if ( $aRandomCard == FALSE )
                {
                    $_SESSION['iBandingCheck'] = 1;
                    @header ("location: index.php?controller=security&action=adduserbank");
                    exit;
                }
                $GLOBALS['oView']->assign( 'ur_here', '新绑卡校验');
                $GLOBALS['oView']->assign( 'account', $aRandomCard);
                $oRandomCard->assignSysInfo();
                $GLOBALS['oView']->display( 'security_newbindingcheck.html' );
                EXIT;
            }

            
        }

    	// 检查呢称是否重复(ajax)
    	if ($_POST['flag'] == "checkname"){
    		// 检查别名是否符合规则
    		if (mb_strlen($_POST['nickname']) > 4){
    			die("-1");
    		} else {
    			if (preg_match("/[\<\>\~\!\@\#\$\%\^\&\*\-\+\=\|\\\'\"\?\,\.\/\[\]\{\}\(\)]{1,}/", $_POST['nickname'])){
    				die("-1");
    			}
    		}
    		
    		$oUserBank = new model_withdraw_UserBank();
    		$oUserBank->Nickname = $_POST['nickname'];
    		$oUserBank->UserId	 = $_SESSION['userid'];
    		$bResult = $oUserBank->infoExistsByNickname();
    		echo $bResult;die;
    	}
    	
    	// 获取对应城市列表
    	if ($_POST['flag'] == "getCity"){
    		$_POST['province'] or die("-1");
    		// 获取城市列表
	    	$oAreaList = new model_withdraw_AreaList();
	    	$oAreaList->ParentId = intval($_POST['province']);
	    	$oAreaList->init();
	    	$sCity = "<option value=''>请选择</option>";
	    	foreach ($oAreaList->Data as $city){
	    		$sCity .= "<option value='{$city['id']}#{$city['name']}'>" . $city['name'] . "</option>";
	    	}
	    	echo $sCity;die;
    	}

    	
    	$aLinks=array(array('url'=>url( 'security', 'userbankinfo', array('check'=>$_SESSION['checkcode']) ),
                                        'title'=>'卡号绑定') );

        if ($_POST['flag'] == "add" || $_POST['flag'] == "confirm"){ 
	        
	    	// 获取用户已绑定的银行卡数量
	    	$oUserBank = new model_withdraw_UserBank();
	    	$oUserBank->UserId = $_SESSION['userid'];
	    	$iUserdCard = $oUserBank->getCount();
	    	
	    	// 获取系统配置信息，得到用户可绑定银行卡数量。
	    	$oConfig = new model_config();
	    	$iMaxCard = $oConfig->getConfigs("kahaobangding");
	    	if ($iUserdCard >= $iMaxCard)
	    		sysMsg("您已绑定了" . $iUserdCard . "张银行卡，每个账户最多只能绑定" . $iMaxCard . "张银行卡！", 1, $aLinks, 'self');
        }
        
    	if ($_POST['flag'] == "add" || $_POST['flag'] == "reset"){ // 信息填写页
    		// 数据检查
    		if (empty($_POST['bank']) || empty($_POST['province']) || empty($_POST['city'])
    		 	|| empty($_POST['branch']) || empty($_POST['account_name']) || empty($_POST['account']))
    			sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
    		if (mb_strlen($_POST['nickname']) > 4 || preg_match("/[\<\>\~\!\@\#\$\%\^\&\*\-\+\=\|\\\'\"\?\,\.\/\[\]\{\}\(\)]{1,}/", $_POST['nickname']))
    			sysMsg("您提交的别名不正确，请核对后重新提交！", 2, $aLinks, 'self');
    		if (mb_strlen($_POST['branch']) > 20 || preg_match("/[\<\>\~\!\@\#\$\%\^\&\*\-\+\=\|\\\'\"\?\,\.\/\[\]\{\}\(\)]{1,}/", $_POST['nickname']))
    			sysMsg("您提交的支行名称不正确，请核对后重新提交！", 2, $aLinks, 'self');
    		if (mb_strlen($_POST['account_name']) > 10 || preg_match("/[\<\>\~\!\@\#\$\%\^\&\*\-\+\=\|\\\'\"\?\,\.\/\[\]\{\}\(\)]{1,}/", $_POST['nickname']))
    			sysMsg("您提交的开户人姓名不正确，请核对后重新提交！", 2, $aLinks, 'self');
    		if (!preg_match("/^\d{16}$|^\d{19}$/", $_POST['account']))
    			sysMsg("您提交的银行卡号格式不正确，请核对后重新提交！", 2, $aLinks, 'self');
    			
    		$oUserBank = new model_withdraw_UserBank();
    		
    		// 检查别名是否使用过
    		if ($_POST['nickname'] != ""){
    			$oUserBank->Nickname = $_POST['nickname'];
	    		$oUserBank->UserId   = $_SESSION['userid'];
	    		if ($oUserBank->infoExistsByNickname()){
	    			sysMsg("您提交的别名已被您使用过，请核对后重新提交！", 2);
	    		}
    		}
    		
    		$aLink=array(array('url'=>url( 'security', 'adduserbank', array('check'=>$_SESSION['checkcode']) ),
                                        'title'=>'卡号绑定') );
    		// 检查卡号是否绑定过
    		$oUserBank->UserId = $_SESSION['userid'];
    		$oUserBank->Account = $_POST['account'];
    		if ($oUserBank->accountIsExists()){
    			sysMsg("此银行卡已被绑定，请换其他卡进行绑定。", 2, $aLink, 'self');
    		}
    		
    		$GLOBALS['oView']->assign( 'ur_here', '卡号绑定信息确认');
    		$GLOBALS['oView']->assign( 'nickname', $_POST['nickname']);
    		$aBank = explode("#", $_POST['bank']);
    		$GLOBALS['oView']->assign( 'bank_id', $aBank[0]);
    		$GLOBALS['oView']->assign( 'bank', $aBank[1]);
    		$aProvince = explode("#", $_POST['province']);
    		$GLOBALS['oView']->assign( 'province_id', $aProvince[0]);
    		$GLOBALS['oView']->assign( 'province', $aProvince[1]);
    		$aCity = explode("#", $_POST['city']);
    		$GLOBALS['oView']->assign( 'city_id', $aCity[0]);
    		$GLOBALS['oView']->assign( 'city', $aCity[1]);
    		$GLOBALS['oView']->assign( 'branch', $_POST['branch']);
    		$GLOBALS['oView']->assign( 'account_name', $_POST['account_name']);
    		$GLOBALS['oView']->assign( 'account', $_POST['account']);
    		$GLOBALS['oView']->assign( 'oldid', $_POST['oldid']);
    		// 修改标志位，因为增加和重新绑定的标志为不同。
    		if ($_POST['flag'] == "add"){
    			$GLOBALS['oView']->assign( 'flag', "confirm");
    		} else {
    			$GLOBALS['oView']->assign( 'flag', "confirmset");
    		}
	    	$oUserBank->assignSysInfo();
	        $GLOBALS['oView']->display( "security_confirmuserbank.html" );
	        EXIT;
    	} else if($_POST['flag'] == "confirm" || $_POST['flag'] == "confirmset") { // 确认提交页
    		// 数据检查
    		if (intval($_POST['bank_id']) <= 0 || empty($_POST['bank']) || 
    			intval($_POST['province_id']) < 0 || empty($_POST['province']) || intval($_POST['city_id']) < 0 || 
    			empty($_POST['city']) || empty($_POST['branch']) || empty($_POST['account_name']) || 
    			empty($_POST['account']))
    			sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
    		
    		// 检查提交银行信息有效(数据库存在)
    		$oAPIWDBank = new model_withdraw_apiwithdrawbank();
    		$oAPIWDBank->BankId = intval ( $_POST['bank_id'] );
    		$aWDBank = $oAPIWDBank->getInfoByBankId();
    		
    		if ( empty($aWDBank['bank_code']) )
    		{
    			sysMsg("请选择平台支持的银行", 2, $aLinks, 'self');
    			exit;
    		}
//    		print_rr( $aWDBank, 1, 1);
    		if (mb_strlen($_POST['nickname']) > 4 || preg_match("/[\<\>\~\!\@\#\$\%\^\&\*\-\+\=\|\\\'\"\?\,\.\/\[\]\{\}\(\)]{1,}/", $_POST['nickname']))
    			sysMsg("您提交的别名不正确，请核对后重新提交！", 2, $aLinks, 'self');
    		if (mb_strlen($_POST['branch']) > 20 || preg_match("/[\<\>\~\!\@\#\$\%\^\&\*\-\+\=\|\\\'\"\?\,\.\/\[\]\{\}\(\)]{1,}/", $_POST['nickname']))
    			sysMsg("您提交的支行名称不正确，请核对后重新提交！", 2, $aLinks, 'self');
    		if (mb_strlen($_POST['account_name']) > 10 || preg_match("/[\<\>\~\!\@\#\$\%\^\&\*\-\+\=\|\\\'\"\?\,\.\/\[\]\{\}\(\)]{1,}/", $_POST['nickname']))
    			sysMsg("您提交的开户人姓名不正确，请核对后重新提交！", 2, $aLinks, 'self');
    		if (!preg_match("/^\d{16}$|^\d{19}$/", $_POST['account']))
    			sysMsg("您提交的银行卡号格式不正确，请核对后重新提交！", 2, $aLinks, 'self');
    			
    		// 确认后提交
    		$oUser = new model_user();
    		$aUserInfo = $oUser->getUserInfo($_SESSION['userid']);
    		$oUserBank = new model_withdraw_UserBank();
    		
    		// 获取系统参数，卡号唯一性要求的银行ID (withdraw_bank_list 表中ID)
    		/*$oConfig = new model_config();
	    	$sOnlys = $oConfig->getConfigs("kahaobangding_only");
	    	$aOnlyReqBankId = array();
	    	if ( preg_match ('/,/i', $sOnlys ) )
	    	{
	    		$aOnlyReqBankId =  explode( ',' , $sOnlys);
	    	}
	    	else
	    	{
	    		$aOnlyReqBankId = array($sOnlys);
	    	}*/
    		//启用账户唯一性判断
    		//if ( !empty( $aOnlyReqBankId ) && in_array( intval($_POST['bank_id']), $aOnlyReqBankId ) )
    		if ( strtolower( $aWDBank['bank_code'] ) == 'ccb') 
    		{
    			// 检查银行卡唯一性
    			if ( !$oUserBank->getMySQLNameLock( $_POST['account'] ) )
    			{
    				sysMsg( '您提交的银行卡号已处于操作中,请稍候重试',2, $aLinks, 'self');
    				exit;
    			}
 
    			if ( $oUserBank->checkAccountOnly( $_SESSION['userid'], $_POST['account']) )
    			{
    				sysMsg( '此银行卡已被绑定，请换其他卡进行绑定。', 2, $aLinks, 'self');
    				exit;
    			}
    			
    		}
    		$oUserFund = new model_userfund();
        	// 锁定用户资金
        	if( FALSE == $oUserFund->switchLock($_SESSION['userid'], 0, TRUE) )
	        {
	            sysMsg("您的资金账户可能因其它操作而被锁，请稍后重试！", 2, $aLinks, 'self'); // 锁定用户账户失败
	        }
	        
    		// 检查用户是否已添加过此别名
    		if ($_POST['nickname'] != ""){
    			$oUserBank->Nickname = $_POST['nickname'];
	    		$oUserBank->UserId   = $_SESSION['userid'];
	    		if ($oUserBank->infoExistsByNickname()){
	    			$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
	    			sysMsg("您提交的别名已被您使用过，请核对后重新提交！", 2, $aLinks, 'self');
	    		}
    		}
    		
    		$aLink=array(array('url'=>url( 'security', 'adduserbank', array('check'=>$_SESSION['checkcode']) ),
                                        'title'=>'卡号绑定') );
			// 检查卡号是否绑定过
    		$oUserBank->UserId = $_SESSION['userid'];
    		$oUserBank->Account = $_POST['account'];
    		if($oUserBank->accountIsExists()){
    			$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
    			sysMsg("此银行卡已被绑定，请换其他卡进行绑定。", 2, $aLink, 'self');
    		}
    		
    		if ($_POST['flag'] == "confirmset"){
				if (intval($_POST['oldid']) < 0){
					$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
    				sysMsg("您提交的旧卡信息有误，请核对后重新提交！", 2, $aLinks, 'self');
				}
    			// 检查旧卡是否存在
    			$oUserBank = new model_withdraw_UserBank($_POST['oldid']);
    			if ($oUserBank->Id != $_POST['oldid']){
    				$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
    				sysMsg("您提交的旧卡信息有误，请核对后重新提交！", 2, $aLinks, 'self');
    			}
    			// 检查旧卡是否是操作用户的
    			if ($oUserBank->UserId != $_SESSION['userid']){
    				$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
    				sysMsg("您提交的旧卡信息有误，请核对后重新提交！", 2, $aLinks, 'self');
    			}
    		}
    		
    		
    		$oUserBank->Nickname	= $_POST['nickname'];
    		$oUserBank->UserId	= $_SESSION['userid'];
    		$oUserBank->UserName	= $_SESSION['username'];
    		$oUserBank->Email	= $aUserInfo['email'];
    		$oUserBank->BankId	= $_POST['bank_id'];
    		$oUserBank->BankName	= $_POST['bank'];
    		$oUserBank->ProvinceId	= $_POST['province_id'];
    		$oUserBank->Province	= $_POST['province'];
    		$oUserBank->CityId	= $_POST['city_id'];
    		$oUserBank->City	= $_POST['city'];
    		$oUserBank->Branch	= $_POST['branch'];
    		$oUserBank->AccountName	= $_POST['account_name'];
    		$oUserBank->Account	= $_POST['account'];
    		$oUserBank->AddTime	= date("Y-m-d H:i:s", time());
    		/*if ($iInsertLastid = $oUserBank->save()){
    			if ($_POST['flag'] == "confirmset"){
    				// 如果为重新绑定操作，添加新卡成功后，将旧卡置为删除
    				$oUserBank = new model_withdraw_UserBank($_POST['oldid']);
    				$oUserBank->Status		= 2;
    				$oUserBank->save();
    			}
    			sysMsg("操作成功", 1, $aLinks, 'self');
    		}
    		else 
    			sysMsg("操作失败！", 1, $aLinks, 'self');*/
    		if ($_POST['flag'] == "confirmset"){
    			// 如果为重新绑定操作，添加新卡成功后，将旧卡置为删除，采用事务进行
    			if ($oUserBank->rebinding($_POST['oldid'])){
    				$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
                                $_SESSION['iBandingCheck'] = 0; //注销前置检查内置参数
    				sysMsg("操作成功", 1, $aLinks, 'self');
    			} else {
    				$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
    				sysMsg("操作失败！", 2, $aLinks, 'self');
    			}
    		} else {
    			$iResult = $oUserBank->save();
    			if ($iResult > 0){
    				$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
                                $_SESSION['iBandingCheck'] = 0; //注销前置检查内置参数
    				sysMsg("操作成功", 1, $aLinks, 'self');
    			} else if($iResult == -1){
    				$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
                                $_SESSION['iBandingCheck'] = 0; //注销前置检查内置参数
    				sysMsg("您已达到卡号绑定数量上限！", 2, $aLinks, 'self');
    			} else {
    				$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
    				sysMsg("操作失败！", 2, $aLinks, 'self');
    			}
    		}
    	}
    	
    	// 获取提现接受的银行列表
    	$oWdBankList = new model_withdraw_ApiWDBankList();
    	$oWdBankList->Status = 1; // 只提取可用银行列表
    	$oWdBankList->init();
    	
    	// 获取行政区列表
    	$oAreaList = new model_withdraw_AreaList();
    	$oAreaList->ParentId = 0;
    	$oAreaList->Used = 1;
    	$oAreaList->init();
    	
    	$GLOBALS['oView']->assign( 'ur_here', '卡号绑定');
    	$GLOBALS['oView']->assign( 'banklist', $oWdBankList->Data);
    	$GLOBALS['oView']->assign( 'provincelist', $oAreaList->Data);
    	$GLOBALS['oView']->assign( 'flag', "add");
    	$oWdBankList->assignSysInfo();
        $GLOBALS['oView']->display( "security_adduserbank.html" );
        EXIT;
    }
    
    
    
    /**
     * 重新绑定银行卡，将旧银行卡置为删除
     *
     * @version 	v1.0	2010-04-13
     * @author 		louis
     */
    public function actionRebinding(){
    	// 总代管理员不允许卡号绑定
    	if ($_SESSION['usertype'] == 2){
    		sysMsg("您没有操作权限！", 2);
    	}
    	$aLinks=array(array('url'=>url( 'security', 'userbankinfo', array('check'=>$_SESSION['checkcode']) ),
                                        'title'=>'卡号绑定') );
        if (!is_numeric($_REQUEST['id']) || intval($_REQUEST['id']) <= 0){
			sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
		}
       	$oUserBank = new model_withdraw_UserBank($_REQUEST['id']);
       	// 只能操作自己的银行卡
       	$oUserBank->UserId == $_SESSION['userid'] or sysMsg( "非法操作", 2, $aLinks, 'self');
    	// 检查用户填写的旧银行卡信息是否正确
    	$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
    	if ($_POST['flag'] == "verify"){
    		// 数据检查
    		if (empty($_POST['account_name']) || empty($_POST['account']))
    			sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
    		
    		if ($oUserBank->Id != $_REQUEST['id'] || $oUserBank->AccountName != $_POST['account_name'] || $oUserBank->Account !== $_POST['account']){
    			sysMsg("您填写的旧卡号信息错误，请核对后重新提交！", 2, $aLinks, 'self');
    		} else {
                $_SESSION['iBandingCheck'] = 1;
				// 获取提现接受的银行列表
		    	$oWdBankList = new model_withdraw_ApiWDBankList();
		    	$oWdBankList->Status = 1; // 只提取可用银行列表
		    	$oWdBankList->init();
		    	
		    	// 获取行政区列表
		    	$oAreaList = new model_withdraw_AreaList();
		    	$oAreaList->ParentId = 0;
		    	$oAreaList->Used = 1;
		    	$oAreaList->init();
		    	
		    	
		    	$GLOBALS['oView']->assign( 'ur_here', '重新绑定');
		    	$GLOBALS['oView']->assign( 'banklist', $oWdBankList->Data);
		    	$GLOBALS['oView']->assign( 'oldid', $oUserBank->Id);
		    	$GLOBALS['oView']->assign( 'provincelist', $oAreaList->Data);
		    	$GLOBALS['oView']->assign( 'flag', "reset");
		    	$GLOBALS['oView']->assign( 'check', $_SESSION['checkcode']);
		    	$oWdBankList->assignSysInfo();
		        $GLOBALS['oView']->display( "security_adduserbank.html" );
		        EXIT;
    		}
    	}
    	
    	// 若要重新绑定银行卡信息，需要先验证
        if ($_GET['flag'] == "reset"){
        	// 检查是否有等待审核的提现记录，如果有不允许重新绑定
        	intval($_REQUEST['id']) > 0 or sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
	        
        	$oFODetail = new model_withdraw_fundoutdetail();
        	$oFODetail->UserId = $oUserBank->UserId;
        	$oFODetail->Account = $oUserBank->Account;
        	$oFODetail->Status	   = "0,3"; // 待审核记录
        	if ($oFODetail->countUncheck() > 0){
        		sysMsg("您现在有提现申请等待审核，请在状态改变后重新绑定银行卡！", 2, $aLinks, 'self');
        	}
        	
        	$oFODetail = new model_withdraw_fundoutdetail();
    		$oFODetail->Digit = 4; // 只显示四位卡号
			$oFODetail->Account = $oUserBank->Account;
        	// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
			$oUserBank->Account = $oFODetail->hiddenAccount();
			
        	$GLOBALS['oView']->assign( 'ur_here', '重新绑定检查');
	    	$GLOBALS['oView']->assign( 'nickname', $oUserBank->Nickname);
	    	$GLOBALS['oView']->assign( 'account', $oUserBank->Account);
	    	$GLOBALS['oView']->assign( 'id', $oUserBank->Id);
	    	$GLOBALS['oView']->assign( 'flag', "verify");
	    	$GLOBALS['oView']->assign( 'action', "rebinding");
	    	$oUserBank->assignSysInfo();
	        $GLOBALS['oView']->display( "security_rebinding.html" );
	        EXIT;
    	}
    }
    
    
    /**
     * 删除指定用户银行信息
     * 
     * @version 	v1.0	2010-04-13
     * @author 		louis
     */
    public function actionDelUserBank(){
    	// 总代管理员不允许卡号绑定
    	if ($_SESSION['usertype'] == 2){
    		sysMsg("您没有操作权限！", 2);
    	}
    	$aLinks=array(array('url'=>url( 'security', 'userbankinfo', array('check'=>$_SESSION['checkcode']) ),
                                        'title'=>'卡号绑定') );
        // 数据检查
        if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
			sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
		}
    	
    	$oUserBank = new model_withdraw_UserBank($_GET['id']);
    	// 只能操作自己的银行卡
    	$oUserBank->UserId == $_SESSION['userid'] or sysMsg( "非法操作", 2, $aLinks, 'self');
    	
        
    	// 检查是否有等待审核的提现记录，如果有不允许删除
    	intval($_GET['id']) > 0 or sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
    	$oFODetail = new model_withdraw_fundoutdetail();
    	$oFODetail->UserId = $oUserBank->UserId;
    	$oFODetail->Account = $oUserBank->Account;
    	$oFODetail->Status	   = "0,3"; // 等审核记录
    	if ($oFODetail->countUncheck() > 0){
    		sysMsg("您有等待审核的提现记录，暂时不能删除！", 2, $aLinks, 'self');
    	}
    	
    	// 删除绑定操作
    	if ($_POST['flag'] == "del"){
    		$oUserFund = new model_userfund();
    		// 检查银行账号与密码
    		if (empty($_POST['account_name']) || empty($_POST['account']) || intval($_POST['id']) <= 0){
    			sysMsg("您提交的信息有误，请核对后重新提交！", 2, $aLinks, 'self');
    			exit;
    		}
    		$oUserBank = new model_withdraw_UserBank($_POST['id']);
    		
    		// 物理删除绑定银行卡
    		if ( $oUserBank->UserId != $_SESSION['userid'] )
	    	{
	    		sysMsg("这不是您自己的银行卡，不能删除", 2, $aLinks, 'self');
	    		exit;
	    	}
	    	
    		if ($_POST['account_name'] !== $oUserBank->AccountName || $_POST['account'] !== $oUserBank->Account){
    			sysMsg("银行账号或开户人姓名不正确！", 2, $aLinks, 'self');
    			exit;
    		}


	    	// 锁定用户资金
	    	if( FALSE == $oUserFund->switchLock($_SESSION['userid'], 0, TRUE) )
	        {
	            sysMsg("您的资金账户可能因其它操作而被锁，请稍后重试！", 2, $aLinks, 'self'); // 锁定用户账户失败
	            exit;
	        }
	        
    		//$oUserBank->Status	= 2;
    		// 物理删除绑定银行卡
	    	if ( $oUserBank->erase() ){
	    		$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
	    		sysMsg("操作成功", 1, $aLinks, 'self');
	    		exit;
	    	} else {
	    		$oUserFund->switchLock($_SESSION['userid'], 0, false); // 用户资金账户解锁
	    		sysMsg("操作失败", 2, $aLinks, 'self');
	    		exit;
	    	}
    	}
    	
		
		$oFODetail = new model_withdraw_fundoutdetail();
        $oFODetail->Digit = 4; // 只显示四位卡号
    	// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
    	$oFODetail->Account = $oUserBank->Account;
		$sAccount = $oFODetail->hiddenAccount();
    	
    	$GLOBALS['oView']->assign( 'ur_here', '删除绑定检查');
    	$GLOBALS['oView']->assign( 'nickname', $oUserBank->Nickname);
    	$GLOBALS['oView']->assign( 'account', $sAccount);
    	$GLOBALS['oView']->assign( 'id', $oUserBank->Id);
    	$GLOBALS['oView']->assign( 'flag', "del");
    	$GLOBALS['oView']->assign( 'action', "deluserbank");
    	$oUserBank->assignSysInfo();
        $GLOBALS['oView']->display( "security_rebinding.html" );
        EXIT;
    }
    
    
    /**
     * 提现历史列表
     *
     * @version 	v1.0	2010-04-13
     * @author 		louis
     */
    public function actionWithdrawList(){
    	$oUser = new model_user();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		if( $iUserType == 2 )
        {
            //如果为总代管理员，则当前用户调整到其总代ID
            $iUserId = $oUser->getTopProxyId( $iUserId );
            // 用户名调整为总代名称
            
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
    	/*// 数据检查
        if (!is_numeric($_GET['api_id']) || intval($_GET['api_id']) <= 0){
			sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
		}*/
    	// 默认查询时间
    	$tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
    	$today     = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
    	$sMinTime     = date("Y-m-d H:i:s", $today);
        $sMaxTime     = date("Y-m-d H:i:s", $tomorrow);
        
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		
        $aHtml = array();
        // 组合查询条件
    	$oFODetailsList = new model_withdraw_fodetailslist();
    	$oFODetailsList->UserId	= $iUserId;
    	$aHtml['time_min'] 	= $oFODetailsList->StartTime = !empty($_GET['time_min']) ? daddslashes($_GET['time_min']) : $sMinTime;
    	$aHtml['time_max'] 	= $oFODetailsList->EndTime 	 = !empty($_GET['time_max']) ? daddslashes($_GET['time_max']) : $sMaxTime;
    	$aHtml['api_id'] 	= $oFODetailsList->ApiId	 = isset($_GET['api_id']) ? intval($_GET['api_id']) : '';
    						  $oFODetailsList->Pages	 = intval($p);
    	$oFODetailsList->init();
    	
    	/*//获取该用户可用 PayAccount
    	$oValidPayAccount = new model_pay_payaccountlimit();
    	$oValidPayAccount->UserId = $_SESSION['lvproxyid'] > 0 ? $_SESSION['lvproxyid'] : $_SESSION['lvtopid'];
    	$aValidPayAccount = $oValidPayAccount->validAccList();
    	
    	if (!empty($aValidPayAccount)){
    		foreach ($aValidPayAccount as $key => $account){
    			$aValidPayAccountList[] = $account['ppid'];
    		}
    	}*/
    	
    	
    	//获取所有 接口的信息数据
    	$oPayport   = new model_pay_payportlist(array(),'','array');
		$aPayportList = $oPayport->Data;
		
		//生成该用户可用的payport信息列表
    	$aValidPayList = array();
    	if (!empty($aValidPayAccount)){
	    	foreach ($aPayportList AS &$aPayL){
	    		$iGetKey = array_search($aPayL['id'],$aValidPayAccountList);
	    		
	    		if ($iGetKey !== false ){
	    			 //将匹配的payaccount id加入到payport信息列表数组中
	    			$aPayL['payaccountid'] = $aValidPayAccount[$iGetKey]['pp_acc_id'];
	    			//处理换行符 Javascript将显示内容
	    			$aPayL['payport_intro'] = str_replace("\n","",$aPayL['payport_intro']);
	    			$aValidPayList[] = $aPayL;
	    		}
	    	}
    	}
    	
    	
    	// 计算当前页的数据统计
    	$fTotal = 0;
		$fChargeTotal = 0;
		$fAmountTotal = 0;
    	if (!empty($oFODetailsList->Data)){
    		foreach ($oFODetailsList->Data as $k => $v){
				$fTotal += $v['source_money'];
				$fChargeTotal += $v['charge'];
				$fAmountTotal += $v['amount'];
			}
    	}
		
		
		$oPager = new pages( $oFODetailsList->TotalCount, DEFAULT_PAGESIZE, 0);    // 分页用3
    	
    	$GLOBALS['oView']->assign( 'ur_here', '提现记录');
    	$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
    	$GLOBALS['oView']->assign( 'withdrawlist', $oFODetailsList->Data);
    	$GLOBALS['oView']->assign( 'time_min', $aHtml['time_min']);
    	$GLOBALS['oView']->assign( 'time_max', $aHtml['time_max']);
    	$GLOBALS['oView']->assign( 'apilist', $aValidPayList);
    	$GLOBALS['oView']->assign( 'api_id', $aHtml['api_id']);
    	$GLOBALS['oView']->assign( 'total', $fTotal);
    	$GLOBALS['oView']->assign( 'chargetotal', $fChargeTotal);
    	$GLOBALS['oView']->assign( 'amounttotal', $fAmountTotal);
    	$GLOBALS['oView']->assign( 'check', $_SESSION['checkcode']);
    	$oFODetailsList->assignSysInfo();
        $GLOBALS['oView']->display( "security_withdrawlist.html" );
        EXIT;
    }
    
    
    /**
     * 查看用户指定的提现信息
     *
     * @version 	v1.0	2010-04-14
     * @author 		louis
     */
    public function actionViewWithdraw(){
    	if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
			sysMsg("您提交的数据有误，请核对后重新提交！", 2);
		}
		
		$oUser = new model_user();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		if( $iUserType == 2 )
        {
            //如果为总代管理员，则当前用户调整到其总代ID
            $iUserId = $oUser->getTopProxyId( $iUserId );
            // 用户名调整为总代名称
            
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        
    	$oFODetail = new model_withdraw_fundoutdetail($_GET['id']);
    	if ($oFODetail->UserId != $iUserId){
    		sysMsg("您提交的数据有误，请核对后重新提交！", 2);
    	}
    	// 只显示卡号后4位，其余均用*代替,截取后4位卡号，并在前面补上相应的*号
    	$oFODetail->Digit = 4; // 只显示四位卡号
		$oFODetail->Account = $oFODetail->hiddenAccount();
    	$GLOBALS['oView']->assign( 'ur_here', '查看明细');
    	$GLOBALS['oView']->assign( 'id', $oFODetail->Id);
    	$GLOBALS['oView']->assign( 'no', $oFODetail->No);
    	$GLOBALS['oView']->assign( 'api_name', $oFODetail->ApiNickname);
    	$GLOBALS['oView']->assign( 'money_type', $oFODetail->MoneyType);
    	$GLOBALS['oView']->assign( 'user_name', $oFODetail->UserName);
    	$GLOBALS['oView']->assign( 'source_money', $oFODetail->SourceMoney);
    	$GLOBALS['oView']->assign( 'charge', $oFODetail->Charge);
    	$GLOBALS['oView']->assign( 'amount', $oFODetail->Amount);
    	$GLOBALS['oView']->assign( 'bank_name', $oFODetail->BankName);
    	$GLOBALS['oView']->assign( 'province', $oFODetail->Province);
    	$GLOBALS['oView']->assign( 'city', $oFODetail->City);
    	$GLOBALS['oView']->assign( 'branch', $oFODetail->City);
    	$GLOBALS['oView']->assign( 'account_name', $oFODetail->AccountName);
    	$GLOBALS['oView']->assign( 'account', $oFODetail->Account);
    	$GLOBALS['oView']->assign( 'request_time', $oFODetail->RequestTime);
    	$GLOBALS['oView']->assign( 'status', $oFODetail->Status);
    	$GLOBALS['oView']->assign( 'check', $_SESSION['checkcode']);
    	$oFODetail->assignSysInfo();
        $GLOBALS['oView']->display( "security_viewwithdraw.html" );
        EXIT;
    }
    
    
    /**
     * 取消提现申请
     *
     * @version 	v1.0	2010-04-14
     * @author 		louis
     */
    public function actionCancelWithdraw(){
    	// 在 系统参数"禁止转账时间" 禁止提现
    	$oConfigd = new model_config();
		$sDenyTime = $oConfigd->getConfigs('zz_forbid_time');
		$aDenyTime = explode('-',$sDenyTime);
		// 24小时值时间值，无前导0
		$sRunNow = date('G:i');
		$bRunTimeChk = true;
		if ( ($sRunNow > $aDenyTime[1]) || ($sRunNow < $aDenyTime[0]) ){
			$bRunTimeChk = false;
		}
		
		
		// 在时间上禁止提现
		if ( $bRunTimeChk === true ){
			if ($_POST['flag'] != "ajax"){
				sysMsg('系统结算时间,暂停提现操作', 1, $aLinks1);
				unset($oConfigd);
				exit;
			} else {
				echo -1;die;
			}
		}
		
		
    	$aLinks=array(array('url'=>url( 'security', 'viewwithdraw', array('id'=>$_REQUEST['id']) )));
    	if (!is_numeric($_REQUEST['id']) || intval($_REQUEST['id']) <= 0){
			sysMsg("您提交的数据有误，请核对后重新提交！", 2, $aLinks, 'self');
		}
    	
    	$oUser = new model_user();
		$iUserType   = isset($_SESSION['usertype']) ? intval($_SESSION['usertype']) : 0;
		$iUserId     = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
		$iAgentId = 0;
		if( $iUserType == 2 )
        {
            //如果为总代管理员，则当前用户调整到其总代ID
            $iUserId = $oUser->getTopProxyId( $iUserId );
            $iAgentId = $_SESSION['userid'];
            if( empty($iUserId) )
            {
                sysMsg( "操作失败", 2 );
            }
        }
        
        $oFODetail = new model_withdraw_fundoutdetail($_REQUEST['id']);
    	$oFODetail->Status = 4; // 设置为取消状态
    	$oFODetail->Where = "status = 1";
        // 检查用户要取消的提现申请是否有权限
    	if ($oFODetail->UserId != $iUserId){
    		if ($_POST['flag'] != "ajax"){
    			sysMsg("您提交的数据有误，请核对后重新提交！", 1, $aLinks, 'self');
    		} else {
    			echo false;die;
    		}
   		}
        
   		// 取消操作
    	$bResult = $oFODetail->CancelWithdraw($iAgentId);
    	if ($_POST['flag'] != "ajax"){
    		if ($bResult)
	    		sysMsg("操作成功", 1, $aLinks, 'self');
	    	else 
	    		sysMsg("操作失败", 1, $aLinks, 'self');
    	} else{
    		echo $bResult;die;
    	}
    }
}