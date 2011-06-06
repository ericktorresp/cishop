<?php
/**
 * 文件 : /_app/controller/report.php
 * 功能 : 控制器 - 报表管理
 * 
 *    - actionWithDrawel()           提现申请列表
 *    +-- actionWithdraweldel()      删除提现申请(处理)
 *    +-- actionWithdraweledit()     审核提现申请(前台)
 *    +-- actionWithdrawelupdate()   审核提现申请   (处理)
 * 
 *    - actionOrderlist()            账变列表
 *    - actionFundlist()             游戏币明细 - 自身
 *    - actionFundlistteam()         游戏币明细 - 团队
 *    - actionInoutlist()            充提报表
 * 
 *    - actionwithdrawapplylist()    提现申请列表
 *    +-- actionapplydetail()        提现明细详情
 * 	  +-- actionajaxwithdraw()		 向提现接口发起提现申请
 * 	  +-- actiondownload()			 提现申请报表下载，生成报表成功后，返回操作数组，将前台页面复选框置为不可用
 *    +-- actionPacksList()			 下载数据包(提现申请审核通过的压缩包)
 *    +-- actionMoveMoney()			 将提现申请单中，通过审核的单子更改为“提现成功”或“提现失败”时的划款操作
 *    - actionUnverifyReason()		 提现审核未通过原因列表
 *    +-- actionAddUnverifyReason()  增加审核未通过原因
 *    +-- actionEditUnverifyReason() 编辑提现审核未通过原因，删除，启用，禁用
 * 	  - actionChangePassList()		 	 审核修改密码
 * 	  +--actionVerifyPass()			 审核密码修改请求，如果通过则修改密码，如果未通过则将记录置为未通过后，用户数据不做任何修改
 * 	  - actionLoadList()			 审核充值列表
 * 	  +--actionVerifyLoad()			 审核人工充值操作
 * 	  - actionWithdrawList()		 审核提现列表
 * 	  +--actionVerifyWithdraw()		 审核人工提现操作
 * 
 * 
 * @author	   Tom
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_report extends basecontroller
{
	/**
	 * (管理员用) 查看提现列表
	 * URL = ./controller=report&action=listwithdrawel
	 * @author Tom 090518
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
    function actionWithDrawel()
	{
	    // 01, 整理搜索条件
	    $aSearch['status']     = isset($_GET['status']) ? $_GET['status'] : 0; // 默认未处理
//	    $aSearch['isforcompany']= isset($_GET['isforcompany']) ? $_GET['isforcompany'] : 1; // 默认公司受理
//	    $aSearch['isdel']      = isset($_GET['isdel'])  ? $_GET['isdel']  : 0; // 默认未删
	    $aSearch['adminname']  = isset($_GET['adminname']) ? daddslashes(trim($_GET['adminname'])) : "";
	    $aSearch['ipaddr']     = isset($_GET['ipaddr']) ? daddslashes(trim($_GET['ipaddr'])) : "";
	    $aSearch['username']   = isset($_GET['username']) ? daddslashes(trim($_GET['username'])) : "";
	    $aSearch['tproxyname'] = isset($_GET['tproxyname']) ? daddslashes(trim($_GET['tproxyname'])) : "";
	    $aSearch['adminname']  = isset($_GET['adminname']) ? daddslashes(trim($_GET['adminname'])) : "";
	    $aSearch['sdate']      = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 02:20', strtotime('-14 day') );
	    $aSearch['edate']      = isset($_GET['edate']) ? trim($_GET['edate']) : "";
	    $aSearch['sdate2']     = isset($_GET['sdate2']) ? trim($_GET['sdate2']) : "";
	    $aSearch['edate2']     = isset($_GET['edate2']) ? trim($_GET['edate2']) : "";
	    $aSearch['identity']   = isset($_GET['identity']) ? trim($_GET['identity']) : -1;
	    $aSearch['bank']   	   = isset($_GET['bank']) ? trim($_GET['bank']) : "";

	    $aSearch['sdate']      = getFilterDate( $aSearch['sdate'],  'Y-m-d H:i' );
	    $aSearch['edate']      = getFilterDate( $aSearch['edate'],  'Y-m-d H:i' );
	    $aSearch['sdate2']     = getFilterDate( $aSearch['sdate2'], 'Y-m-d H:i' );
	    $aSearch['edate2']     = getFilterDate( $aSearch['edate2'], 'Y-m-d H:i' );
	    $aHtmlValue = array();

	    // 02, WHERE 语句拼接
	    $sWhere = ' 1 '; // WHERE 条件变量声明
	    /*if( $aSearch['isforcompany'] == 1 )
	    {//公司受理
	        $sWhere .= ' AND `isforcompany`=1 ';
	    }
	    elseif( $aSearch['isforcompany'] == 2 )
	    {
	        $sWhere .= ' AND `isforcompany`=0 ';
	    }*/
	    
	    if( $aSearch['status'] != -1 )
	    { // 处理失败
	        $sWhere .= " AND `status` = '".intval($aSearch['status'])."' ";
	    }
	    $aHtmlValue['st'] = $aSearch['status'];
	    $aHtmlValue['ifc'] = $aSearch['isforcompany'];

//	    if( $aSearch['isdel'] != -1 )
//	    { // 删除状态
//	        $sWhere .= " AND `isdel` = '".intval($aSearch['isdel'])."' ";
//	    }
//	    $aHtmlValue['del'] = $aSearch['isdel'];

	    if( $aSearch['adminname'] != '' )
	    { // 管理员名搜索
	        $sWhere .= " AND `adminname` = '".$aSearch['adminname']."' ";
	        $aHtmlValue['adminname'] = stripslashes_deep($aSearch['adminname']);
	    }

	    if( $aSearch['username'] != '' )
	    { // 提现申请人搜索
	        $sWhere .= " AND c.`username` = '".$aSearch['username']."' ";
	        $aHtmlValue['username'] = stripslashes_deep($aSearch['username']);
	    }

	    if( $aSearch['tproxyname'] != '' )
	    { // 总代名搜索
	        $sWhere .= " AND d.`username` = '".$aSearch['tproxyname']."' ";
	        $aHtmlValue['tproxyname'] = stripslashes_deep($aSearch['tproxyname']);
	    }

	    if( $aSearch['ipaddr'] != '' )
	    { // 操作地址模糊搜索
	         if( strstr($aSearch['ipaddr'],'*') )
	        {
	            $sWhere .= " AND `clientip` LIKE '". str_replace( '*', '%', $aSearch['ipaddr'] ) ."' ";
	        }
	        else 
	        {
	            $sWhere .= " AND `clientip` = '".$aSearch['ipaddr']."' ";
	        }
	        $aHtmlValue['ipaddr'] = h(stripslashes_deep($aSearch['ipaddr']));
	    }

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

	    if( $aSearch['bank'] != '' )
	    { // 管理员处理时间 截止于...
	        $sWhere .= " AND a.`bankname` = '" . stripslashes_deep($aSearch['bank']) . "' ";
	        $aHtmlValue['bank']  =  stripslashes_deep($aSearch['bank']);
	    }
	    
	    
	    // 用户身份 普通
	    if( $aSearch['identity'] == 1 )
	    { // 管理员处理时间 截止于...
	        $sWhere .= " AND `identity` = 1 ";
	        $aHtmlValue['identity']  =  1;
	    }
	    
	    // 用户身份 Vip
	    if( $aSearch['identity'] == 2 )
	    { // 管理员处理时间 截止于...
	        $sWhere .= " AND `identity` = 2 ";
	        $aHtmlValue['identity']  =  2;
	    }
	    
	    // 用户身份 black
	    if( $aSearch['identity'] == 3 )
	    { // 管理员处理时间 截止于...
	        $sWhere .= " AND `identity` = 3 ";
	        $aHtmlValue['identity']  =  3;
	    }

        /* @var $oWithDrawel model_withdrawel */
		$oWithDrawel = A::singleton('model_withdrawel');
	    $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
	    $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oWithDrawel->getWithDrawelList('', $sWhere, $pn , $p); // 获取数据结果集
        
        // 确定用户身份
        /*$oCompanyCard = new model_deposit_companycard();
        foreach ($aResult['results'] as $k => $v){
        	if (intval($v['isblack']) === 1){
				$aResult['results'][$k]['identity'] = 3;
			} else if(intval($v['isvip']) === 1 && $oCompanyCard->_differTime($v['vip_expriy']) < 0){
				$aResult['results'][$k]['identity'] = 2;
			} else {
				$aResult['results'][$k]['identity'] = 1;
			}
        }*/
        // 提取提现银行列表
        $oApiBankList = new model_withdraw_ApiWDBankList();
		$oApiBankList->Status = "0,1"; // 只取可用的银行
		$oApiBankList->init();
        
		$prevOneHour = time() - 3600;
		foreach ($aResult['results'] as $k=>$v)
	    {
	    	$aResult['results'][$k]['manoperate'] = $v['status']==5 ? ($prevOneHour  > strtotime($v['pay_time']) ? 1 : 2) : 0;
	    	$v['pay_time'] = '0000-00-00 00:00:00' && $v['status'] > 0 && $aResult['results'][$k]['pay_time'] = $v['finishtime'];
	    }
		
        // 记录的状态数组
        $aStatus = array(
            0 => "<font color=#FF3366>未处理</font>",
            1 => "<font color=#c0c0c0><img TITLE='提现失败' src='./images/no.gif'></font>",
            2 => "<font color=#669900><img TITLE='提现成功' src='./images/yes.gif'></font>",
            3 => "<font color='blue'>已操作</font>",
            5 => "<font color=#500950>操作中</font>"
        );
        
        
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
	    $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("report","withdrawel"), 'text'=>'清空过滤条件' ) );
	    $GLOBALS['oView']->assign( 'banklist', $oApiBankList->Data );
        $GLOBALS['oView']->assign( 'astatus', $aStatus );
	    $oWithDrawel->assignSysInfo();
	    $GLOBALS['oView']->display("report_withdrawel.html");
		EXIT;
	}



	
	/**
	 * 出纳汇款
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-10-12
	 * @package 	passportadmin
	 * 
	 */
	public function actionDealWithdraw(){
		$aLocation = array(0=>array("text" => "提现申请列表","href" => url("report","withdrawel")));
	    $iWithDrawelId = isset($_REQUEST["id"])&&is_numeric($_REQUEST["id"]) ? intval($_REQUEST["id"]) : 0;
		if( $iWithDrawelId == 0 )
		{
		    sysMessage("提现申请ID错误", 1, $aLocation);
		    exit;
		}
		/* @var $oWithDrawel model_withdrawel */
		$oWithDrawel = A::singleton('model_withdrawel');
		$aWithDrawel = $oWithDrawel->getWithDrawelById( $iWithDrawelId );
	    if( empty($aWithDrawel) )
		{
		    sysMessage("提现申请ID不存在", 1, $aLocation);
		    exit;
		}
		
		if (intval($aWithDrawel['dealing_user_id']) > 0 && intval($_SESSION['admin']) !== intval($aWithDrawel['dealing_user_id'])){
			sysMessage("管理员 出纳 " . $aWithDrawel['dealing_user_name'] . " 正在处理这笔提款，请勿重复进行汇款操作！", 1, $aLocation);
			exit;
		}
		
		// 加锁
		if (!isset($_POST['flag']) || empty($_POST['flag'])){
			if (intval($aWithDrawel['dealing_user_id']) === 0){
				if ($oWithDrawel->lockRecord($iWithDrawelId, $_SESSION['admin'], $_SESSION['adminname']) === false){
					sysMessage("加锁失败！", 1, $aLocation);
					exit;
				}
			}
		}
		
		
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : "";
		if ($_POST['flag'] == "deal"){ // 出纳处理
			// 数据检查
			if (empty($_POST['notes']) || !is_numeric($_POST['id']) || $_POST['id'] <= 0){
				sysMessage("您提交的信息有误，请核对后重新提交！", 1, $aLocation);
				exit;
			}
			
			$aData = array();
			$aData['cashier_id'] = $_SESSION['admin'];
			$aData['cashier'] = $_SESSION['adminname'];
            $aData['pay_time'] = date("Y-m-d H:i:s", time());
			$aData['notes']	= htmlspecialchars($_POST['notes']);
			$aData['status'] = 3;
			
			$mResult = $oWithDrawel->dealWithdraw( $_POST['id'], $aData );
			if ($mResult === false){
				// 解锁
				$oWithDrawel->unLock($iWithDrawelId, $_SESSION['admin'], $_SESSION['adminname']);
				sysMessage("操作失败", 1, $aLocation);
				exit;
			} else {
				// 解锁
				$oWithDrawel->unLock($iWithDrawelId, $_SESSION['admin'], $_SESSION['adminname']);
				sysMessage("操作成功", 0, $aLocation);
				exit;
			}
		}
		
		$GLOBALS['oView']->assign( "s", $aWithDrawel );
		$GLOBALS['oView']->assign( "ur_here", "出纳汇款" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","withdrawel"), 'text'=>'提现申请列表' ) );
		$GLOBALS['oView']->display( "report_dealwithdraw.html" );
		EXIT;
	}
	
	
	
	

	/**
	 * 审核提现申请 (前台)
	 * URL = ./controller=report&action=withdraweledit&id=1
	 * @author Tom 090519
	 */
	function actionWithdraweledit()
	{
	    $aLocation = array(0=>array("text" => "提现申请列表","href" => url("report","withdrawel")));
	    $iWithDrawelId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
		if( $iWithDrawelId == 0 )
		{
		    sysMessage("提现申请ID错误", 1, $aLocation);
		}
		/* @var $oWithDrawel model_withdrawel */
		$oWithDrawel = A::singleton('model_withdrawel');
		$aWithDrawel = $oWithDrawel->getWithDrawelById( $iWithDrawelId );
	    if( empty($aWithDrawel) )
		{
		    sysMessage("提现申请ID不存在", 1, $aLocation);
		}
		// 管理员是否可以审核此条申请的标记
		/*$aWithDrawel['opcanupdate'] = 0;
		if( $aWithDrawel['adminid']==0 && $aWithDrawel['status']==0 && $aWithDrawel['finishtime']==0 
		    && $aWithDrawel['isforcompany'] == 1 )
		{
		    $aWithDrawel['opcanupdate'] = 1;
		}*/
		$GLOBALS['oView']->assign( "s", $aWithDrawel );
		$GLOBALS['oView']->assign( "ur_here", "审核提现申请" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","withdrawel"), 'text'=>'提现申请列表' ) );
		$GLOBALS['oView']->display( "report_withdrawel_info.html" );
		EXIT;
	}


	/**
	 * 审核提现申请 (处理)
	 * URL = ./controller=report&action=withdrawelupdate&id=1
	 * @author Tom 090519
	 * $_POST =>
	 *    [doaction] => set_failed | set_success
     *    [btnSubmit] => 确认
     *    [withdrawelid] => 4
	 */
	function actionWithdrawelupdate() 
	{
		// 0, 数据整理, 控制层的简单数据安全过滤
	    $aLocation = array(0=>array("text" => "提现申请列表","href" => url("report","withdrawel")));
	    if( !isset($_POST['doaction']) || !isset($_POST['withdrawelid'])
	        || ( $_POST['doaction']!='set_failed' && $_POST['doaction']!='set_success' )
		// (不检查流水号) || ( $_POST['doaction']=='set_success' && empty($_POST['bankcode']) )  
	        || intval($_POST['withdrawelid']) == 0 
	    )
	    {
	        sysMessage("数据初始错误,请检查", 1, $aLocation);
	    }
	    $iWithDrawelId = intval($_POST['withdrawelid']);
	    $sAction       = trim($_POST['doaction']);
	    $sMessage      = $sAction=='set_success' ? '' : trim($_POST['failedmsg']);

	    // 1, 控制层,对用户操作所引发的消息. 进行转发
	    /* @var $oWithDrawel model_withdrawel */
	    $oWithDrawel = A::singleton('model_withdrawel');
	    $iFlag = 0;
	    if( $sAction == 'set_success' )
		{
	        $iFlag = $oWithDrawel->setWithdrawStatus( $iWithDrawelId, 'SUCCESSED', $sMessage );
		}
		elseif( $sAction == 'set_failed' )
		{
		    $iFlag = $oWithDrawel->setWithdrawStatus( $iWithDrawelId, 'FAILED',$sMessage );
		}
		else
		{
		    sysMessage("错误的行为参数", 1, $aLocation);
		}
		if( $iFlag > 0 )
		{
		    sysMessage("操作成功", 0, $aLocation);
		}
        elseif( $iFlag == -4 )
        {
            sysMessage("操作失败, 原因: 禁止处理由非总代发起的提现申请", 1, $aLocation);
        }
	    elseif( $iFlag == -10 )
		{
		    sysMessage("操作失败, 原因: 账户资金临时被锁, 请稍后再试", 1, $aLocation);
		}
		elseif( $iFlag == -1004 )
		{
		    sysMessage("操作失败, 原因: 频道资金数据失败", 1, $aLocation);
		}
	    elseif( $iFlag == -1005 )
		{
		    sysMessage("操作失败, 原因: 用户账户锁定失败,请稍后再试", 1, $aLocation);
		}
	    elseif( $iFlag == -1007 )
		{
		    sysMessage("操作失败, 原因: 账变记录插入失败,请稍后再试", 1, $aLocation);
		}
	    elseif( $iFlag == -1008 )
		{
		    sysMessage("操作失败, 原因: 账户金额更新失败", 1, $aLocation);
		}
	    elseif( $iFlag == -1009 )
		{
		    sysMessage("操作失败, 原因: 此操作会账户资金异常(资金负数)", 1, $aLocation);
		}
		else
		{
		    sysMessage("操作失败", 1, $aLocation);
		}
	}



	/**
	 * 查看账变列表
	 * URL = ./controller=report&action=orderlist
	 * @author Tom 090601
	 * 
	 * HTML 可选搜索条件:
	 *   - 01, 账变类型        otid         ( '-1'=全部, 其他数字对应 `ordertype`.id )
	 *   - 02, 管理员id        adminid      ( 对应 adminuser.adminid )
	 *   - 03, 用户地址        clientip     ( 对应 orders.clientip )
	 *   - 04, 用户名          username     
	 *   - 05, 总代id          tproxyid     ( 总代名的 select.options 下拉框 )
	 *   - 06, 账变开始时间    sdate
	 *   - 07, 账变截止时间    edate
	 *   - 08, 金额1的运算符   af1          ( amountflag1  1=小于, 2=小于等于, 4=大于, 5=大于等于 )
	 *   - 09, 金额1的范围     amount1      ( 金额数值1 )
	 *   - 10, 金额2的运算符   af2          ( amountflag2  1=小于, 2=小于等于, 4=大于, 5=大于等于 )
	 *   - 11, 金额2的范围     amount2      ( 金额数值2 )
	 *   - 12, 每页记录数量    pn
	 *  TODO: 多频道转账的搜索
	 */
    function actionOrderlist()
	{
	    set_time_limit(240); // 4min
	    // 01, 整理搜索条件
	    $aSearch['otid']       = isset($_GET['otid']) ? $_GET['otid'] : -1; // 默认全部类型
	    $aSearch['adminid']    = isset($_GET['adminid']) ? intval($_GET['adminid']) : -1; // 默认不限
	    $aSearch['clientip']   = isset($_GET['clientip']) ? daddslashes(trim($_GET['clientip'])) : "";
	    $aSearch['pn']         = isset($_GET['pn']) && in_array( $_GET['pn'], array(25,50,75,100,150,200) ) ? intval($_GET['pn']) : 50;
	    $aSearch['orderno']    = isset($_GET['orderno']) ? daddslashes(trim($_GET['orderno'])) : "";
	    $aSearch['sdate']      = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 02:20'); // 当天账变
	    $aSearch['edate']      = isset($_GET['edate']) ? trim($_GET['edate']) : "";
	    $aSearch['tproxyid']   = isset($_GET['tproxyid']) ? intval($_GET['tproxyid']) : -1;
	    $aSearch['sel']        = isset($_GET['sel']) ? intval($_GET['sel']) : 2; // 默认用户名输入框
	    $aSearch['username']   = isset($_GET['username']) ? daddslashes(trim($_GET['username'])) : "";
	    $aSearch['included1']  = isset($_GET['included1']) ? 1 : 0;
	    $aSearch['included2']  = isset($_GET['included2']) ? 1 : 0;
	    $aSearch['included3']  = isset($_GET['included3']) ? 1 : 0; // 不含自身
	    $aSearch['af1']        = isset($_GET['af1']) ? intval($_GET['af1']) : 4;
	    $aSearch['amount1']    = isset($_GET['amount1']) ? intval($_GET['amount1']) : "";
	    $aSearch['af2']        = isset($_GET['af2']) ? intval($_GET['af2']) : 1;
	    $aSearch['amount2']    = isset($_GET['amount2']) ? intval($_GET['amount2']) : "";

	    $aSearch['sdate'] = getFilterDate( $aSearch['sdate'] );
	    $aSearch['edate'] = getFilterDate( $aSearch['edate'] );

	    $aHtmlValue = array();
	    $aHtmlValue['sel']     = $aSearch['sel'];
	    $aHtmlValue['tproxyid']= $aSearch['tproxyid'];
	    $aHtmlValue['af1'] = $aSearch['af1']; // 默认大于
	    $aHtmlValue['otid'] = $aSearch['otid'];
	    $aHtmlValue['included3']= $aSearch['included3'];

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

	    // 02, WHERE 语句拼接 ----------------------------------------------------------
	    $sWhere = ' 1 '; // WHERE 条件变量声明
	    // 0001, 索引: entry -----------------------------------------------------------
	    /* @var $oOrder model_orders */
	    $oOrder = A::singleton('model_orders');
		if( $aSearch['orderno'] != '' )
	    { // 订单号搜索
	        $aOrderNo = $oOrder->orderEnCode( $aSearch['orderno'], 'DECODE' );
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

	    // 0002, 索引 times ------------------------------------------------------------
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

	    // 0003, 索引 amount ----------------------------------------------------------
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

	    // 0004, 用户ID 部分 ----------------------------------------------------------
	    // 0004, 公司销售管理员: (读取自己相应的总代ID, 附加在 WHERE 语句中)
	    $bIsSaleAdmin = FALSE;
	    /* @var $oAdminProxy model_adminproxy */
	    $oAdminProxy  = A::singleton('model_adminproxy');
	    $bIsSaleAdmin = $oAdminProxy->isSaleAdmin($_SESSION['admin']);
	    if( $bIsSaleAdmin == TRUE )
	    {
	        $sSaleAdminUsers = $oAdminProxy->getSaleAdminUsers($_SESSION['admin']);
	        if( $sSaleAdminUsers == -1 )
	        {
	            $sWhere .= " AND 1<0 ";
	        }
	        else
	        {
	            $sWhere .= " AND ut.`lvtopid` IN ( ".daddslashes($sSaleAdminUsers)." ) ";
	        }
	    }
	    unset($oAdminProxy);

	    // 用户ID部分
	    if( $aSearch['sel'] == 1 && $aSearch['username'] != '' )
	    {
	        // 获取用户ID
	        /* @var $oUser model_user */
	        $oUser = A::singleton('model_user');
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
                if( is_numeric($v) )
                {
                    $sOtidString .= intval($v).',';
                }
            }
            if( substr($sOtidString,-1,1) == ',' )
            {
                $sOtidString = substr( $sOtidString, 0, -1 );
            }
            $sWhere .= " AND o.`ordertypeid` IN ( $sOtidString ) ";
            $_GET['otid'] = $sOtidString;
            $aHtmlValue['otid'] = $sOtidString;
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
                    break;
                }
                foreach( $aSearch['otid'] AS &$v )
                {
                    if( is_numeric($v) )
                    {
                        $sOtidString .= intval($v).',';
                    }
                }
                if( substr($sOtidString,-1,1) == ',' )
                {
                    $sOtidString = substr( $sOtidString, 0, -1 );
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

	    // 每页记录数
	    $aHtmlValue['pn'] = $aSearch['pn'];
	    $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
	    $pn = $aSearch['pn'];                                  // 分页用2
	    $aResult = $oOrder->getAdminOrderList( '*', $sWhere, $pn , $p); // 获取数据结果集

		$aHtmlValue['iCountSR']   = 0; // 总计收入
		$aHtmlValue['iCountZC']   = 0; // 总计支出
		$aHtmlValue['iCountAll']  = 0; // 当页总计资金变化
		if( !empty($aResult['affects']) && !empty($aResult['results']) && is_array($aResult['results']) )
		{ // 进行数据整理(更新订单号), 对当页数据进行小结
		    foreach( $aResult['results'] as &$v )
		    {
    		    $v['orderno'] = model_orders::orderEnCode(date("Ymd").'-'.$v['entry'], "ENCODE");
    			if( $v['signamount'] == 0 )
    			{
    				$aHtmlValue['iCountZC'] -= $v['amount'];
    			}
    			else
    			{
    				$aHtmlValue['iCountSR'] += $v['amount'];
    			}
		    }
		}
		$aHtmlValue['iCountAll'] = abs($aHtmlValue['iCountSR'])-abs($aHtmlValue['iCountZC']);
		$aHtmlValue['Ordertypeopts'] = $oOrder->getOrderType('opts',$aHtmlValue['otid']);
		// 解析管理员下拉框
		/* @var $oPassPort model_passport */
		$oPassPort        =  A::singleton('model_passport');
		$aHtmlValue['Adminidopts'] =  $oPassPort->getDistintAdminName(FALSE, $aSearch['adminid'] );
		$aHtmlValue['topproxyopts']=  $oPassPort->getTopProxyName(FALSE, $aHtmlValue['tproxyid'],
		                                                 $bIsSaleAdmin ? $_SESSION['admin'] : 0 );
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $aHtmlValue['aStat'] = array();
        if( TRUE == $oPager->isLastPage() && $oPager->getTotalPage() != 1 )
        { // 最后一页, 进行数据总体结算
            $aHtmlValue['aStat'] = $oOrder->getAdminOrderStat( $sWhere );
        }
        $GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
		$GLOBALS['oView']->assign( 's', $aHtmlValue );
	    $GLOBALS['oView']->assign( 'aUserList', $aResult['results'] ); // 数据分配
	    $GLOBALS['oView']->assign( 'ur_here', '账变列表' );
	    $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","orderlist"), 'text'=>'清空过滤条件' ) );
	    $oPassPort->assignSysInfo();
	    $GLOBALS['oView']->display("report_orderlist.html");
		EXIT;
	}



    /**
	 * 查看游戏币明细 - 团队
	 *   功能需求: 主要用于(销售)管理员查询某个总代一条树下的资金状况
	 *             同时也允许用户名搜索, 用于缩小用户范围
	 * URL = ./controller=report&action=fundlistteam
	 * @author Tom 090609
	 * 
	 * HTML 可选搜索条件:  
	 *   - 01, 用户名          username
	 *   - 02, 金额1的范围     amount1      ( 金额数值1 )
	 *   - 03, 金额2的范围     amount2      ( 金额数值2 )
	 */
    function actionFundlistTeam()
	{
	    // 01, 整理搜索条件
	    $aSearch['pn']         = isset($_GET['pn']) ? intval($_GET['pn']) : 100;
	    $aSearch['username']   = isset($_GET['username']) ? daddslashes(trim($_GET['username'])) : "";
	    $aSearch['amount1']    = isset($_GET['amount1']) ? intval($_GET['amount1']) : "";
	    $aSearch['amount2']    = isset($_GET['amount2']) ? intval($_GET['amount2']) : "";
	    $aSearch['istester']   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
	    $aSearch['isfrozen']   = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
	    $aHtmlValue = array();

	    // 02, WHERE & Having 语句拼接 ----------------------------------------------------------
	    $sWhere = ' 1 '; // WHERE 条件变量声明
	    $sHaving= ' 1 '; // HAVING 条件变量声明
	    // 0001, 索引 entry -----------------------------------------------------------
	    /* @var $oOrder model_userfund */
	    $oOrder = A::singleton('model_userfund');
	    // 0002, 索引 amount ----------------------------------------------------------
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
	    
	    if( $aSearch['istester'] != -1 )
	    {//测试账户
	        $sWhere .= " AND ut.`istester` = " . $aSearch['istester'];
	    }
	    $aHtmlValue['istester'] = $aSearch['istester'];
	    
	    if( $aSearch['isfrozen'] != -1 )
	    {//冻结账户
	        $bIsFrozen = $aSearch['isfrozen']; 
            /* @var $oUser model_user */
            $oUser = A::singleton("model_user");
            if( $bIsFrozen > 0 )//显示冻结总代,除去非冻结总代
            {
                $aTopProxyList = $oUser->getChildListID( 0, FALSE, " AND a.`isfrozen` > 0" );
            }
            elseif ( $bIsFrozen == 0 )//如果为非冻结总代,除去冻结总代
            {
                $aTopProxyList = $oUser->getChildListID( 0, FALSE, " AND a.`isfrozen` = 0" );
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
	    $aHtmlValue['isfrozen'] = $aSearch['isfrozen'];
	    // FOR 销售管理员
	    $bIsSaleAdmin = FALSE;
	    /* @var $oAdminProxy model_adminproxy */
	    $oAdminProxy  = A::singleton('model_adminproxy');
	    $bIsSaleAdmin = $oAdminProxy->isSaleAdmin($_SESSION['admin']);
	    if( $bIsSaleAdmin == TRUE )
	    {
	        $sSaleAdminUsers = $oAdminProxy->getSaleAdminUsers($_SESSION['admin']);
	        if( $sSaleAdminUsers == -1 )
	        {
	            $sWhere .= " AND 1<0 ";
	        }
	        else
	        {
	            $sWhere .= " AND ut.`lvtopid` IN ( ".daddslashes($sSaleAdminUsers)." ) ";
	        }
	    }
	    unset($oAdminProxy);

	    // 0003, 用户ID 部分 ----------------------------------------------------------
	    // 用户ID部分
	    $iUserId = 0;
	    if( $aSearch['username'] != '' )
	    { // 获取用户ID
	        /* @var $oUser model_user */
	        $oUser = A::singleton('model_user');
	        $iUserId = $oUser->getUseridByUsername( $aSearch['username'] );
	        if( $iUserId == 0 || is_array($iUserId) )
	        { // 搜索的用户名未找到, 并且不允许通配符搜索
	            $sWhere .= " AND 0 ";
	            $iUserId = 0;
	        }
	        $aHtmlValue['username'] = stripslashes_deep($aSearch['username']);
	    }

	    $aHtmlValue['now']= date("Y-m-d H:i:s"); 
	    // 获取数据结果集
	    $aResult = $oOrder->getProxyTeamFundList( $iUserId, $sWhere, ($sHaving==' 1 '?'':$sHaving) ); 
		$aHtmlValue['iCountCash']     = 0; // 总计 现金余额  A
		$aHtmlValue['iCountCredit']   = 0; // 总计 信用欠款  B
		$aHtmlValue['iCountAll']      = 0; // 总计 账户余额  C
		$aHtmlValue['iCountHold']     = 0; // 总计 冻结金额  D
		$aHtmlValue['iCountAvail']    = 0; // 总计 可用余额  E
		$aHtmlValue['iCountError1']   = 0;
		$aHtmlValue['iCountError2']   = 0;
		$aHtmlValue['iCounts']        = count($aResult);
		if( $aHtmlValue['iCounts'] > 0 )
		{ // 进行数据整理(更新订单号), 对当页数据进行小结
		    foreach( $aResult as &$v )
		    {
    		    $aHtmlValue['iCountCash']   += $v['TeamCashBalance'];     // A
    		    $aHtmlValue['iCountCredit'] += $v['TeamCredit'];          // B
    		    $aHtmlValue['iCountAll']    += $v['TeamChannelBalance'];  // C
    		    $aHtmlValue['iCountHold']   += $v['TeamHoldBalance'];     // D
    		    $aHtmlValue['iCountAvail']  += $v['TeamAvailBalance'];    // E
    		    // A + B = D + E 
    		    $v['errBalance1'] = round($v['TeamHoldBalance']+$v['TeamAvailBalance']-$v['TeamCashBalance']-$v['TeamCredit'],4);
    		    // A + B = C
    		    $v['errBalance2'] = round($v['TeamChannelBalance']-$v['TeamCashBalance']-$v['TeamCredit'],4);
    		    $aHtmlValue['iCountError1']  += $v['errBalance1'];
    		    $aHtmlValue['iCountError2']  += $v['errBalance2'];
		    }
		}
		$GLOBALS['oView']->assign( 's', $aHtmlValue );
	    $GLOBALS['oView']->assign( 'aUserList', $aResult ); // 数据分配
	    $GLOBALS['oView']->assign( 'ur_here', '游戏币明细 - 团队' );
	    $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","fundlistteam"), 'text'=>'清空过滤条件' ) );
	    $oOrder->assignSysInfo();
	    $GLOBALS['oView']->display("report_fundlistteam.html");
		EXIT;
	}



	/**
	 * 查看游戏币明细 - 自身
	 *   功能需求: 主要用于(销售)管理员查询某个总代一条树下的资金状况
	 *             同时也允许用户名搜索, 用于缩小用户范围
	 * URL = ./controller=report&action=fundlist
	 * 完成度 : 100%
	 * @author Tom 090609
	 * 
	 * HTML 可选搜索条件:  
	 *   - 01, 用户名          username
	 *   - 02, 金额1的范围     amount1      ( 金额数值1 )
	 *   - 03, 金额2的范围     amount2      ( 金额数值2 )
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

	    // 02, WHERE & Having 语句拼接 ----------------------------------------------------------
	    $sWhere = ' 1 '; // WHERE 条件变量声明
	    // 0001, 索引 entry -----------------------------------------------------------
	    /* @var $oOrder model_userfund */
	    $oOrder = A::singleton('model_userfund');
	    // 0002, 索引 amount ----------------------------------------------------------
	    if( $aSearch['amount1'] != '' && is_numeric($aSearch['amount1']) )
	    { // 单用户(不含下级) 账户余额1
	        $sWhere .= " AND uf.`channelbalance` >= '".intval($aSearch['amount1'])."' ";
	        $aHtmlValue['amount1'] = $aSearch['amount1'];
	    }

	    if( $aSearch['amount2'] != '' && is_numeric($aSearch['amount2']) )
	    { // 单用户(不含下级) 账户余额2
	        $sWhere .= " AND uf.`channelbalance` <= '".intval($aSearch['amount2'])."' ";
	        $aHtmlValue['amount2'] = $aSearch['amount2'];
	    }
        
	    if( $aSearch['istester'] != -1 )
	    {//测试账户
	        $sWhere .= " AND ut.`istester` = " . $aSearch['istester'];
	    }
	    $aHtmlValue['istester'] = $aSearch['istester'];
	    
	    if( $aSearch['isfrozen'] != -1 )
	    {//冻结账户
	        $aSearch['isfrozen'] == 0 ? $sWhere .= " AND ut.`isfrozen` = ". $aSearch['isfrozen'] :
	                                    $sWhere .= " AND ut.`isfrozen` >= ". $aSearch['isfrozen'];
	    }
	    $aHtmlValue['isfrozen'] = $aSearch['isfrozen'];
	    // FOR 销售管理员
	    $bIsSaleAdmin = FALSE;
	    /* @var $oAdminProxy model_adminproxy */
	    $oAdminProxy  = A::singleton('model_adminproxy');
	    $bIsSaleAdmin = $oAdminProxy->isSaleAdmin($_SESSION['admin']);
	    if( $bIsSaleAdmin == TRUE )
	    {
	        $sSaleAdminUsers = $oAdminProxy->getSaleAdminUsers($_SESSION['admin']);
	        if( $sSaleAdminUsers == -1 )
	        {
	            $sWhere .= " AND 1<0 ";
	        }
	        else
	        {
	            $sWhere .= " AND ut.`lvtopid` IN ( ".daddslashes($sSaleAdminUsers)." ) ";
	        }
	    }
	    unset($oAdminProxy);

	    // 0003, 用户ID 部分 ----------------------------------------------------------
	    // 用户ID部分
	    $iUserId = 0;
	    if( $aSearch['username'] != '' )
	    {
	        // 获取用户ID
	        /* @var $oUser model_user */
	        $oUser   = A::singleton('model_user');
	        $iUserId = $oUser->getUseridByUsername( $aSearch['username'] );
	        if( $iUserId == 0 || is_array($iUserId) )
	        { // 搜索的用户名未找到, 并且不允许通配符搜索
	            $sWhere .= " AND 0 ";
	            $iUserId = 0;
	        }
	        $aHtmlValue['username'] = stripslashes_deep($aSearch['username']);
	    }

	    $aHtmlValue['now']= date("Y-m-d H:i:s"); 
	    $aResult = $oOrder->getProxyFundList( $iUserId, $sWhere ); // 获取数据结果集

		$aHtmlValue['iCountCash']     = 0; // 总计 现金余额  A
		$aHtmlValue['iCountCredit']   = 0; // 总计 信用欠款  B
		$aHtmlValue['iCountAll']      = 0; // 总计 账户余额  C
		$aHtmlValue['iCountHold']     = 0; // 总计 冻结金额  D
		$aHtmlValue['iCountAvail']    = 0; // 总计 可用余额  E
		$aHtmlValue['iCountError1']   = 0;
		$aHtmlValue['iCountError2']   = 0;
		$aHtmlValue['iCounts']        = count($aResult);
		if( $aHtmlValue['iCounts'] > 0 )
		{ // 进行数据整理(更新订单号), 对当页数据进行小结
		    foreach( $aResult as &$v )
		    {
    		    $aHtmlValue['iCountCash']   += $v['TeamCashBalance'];     // A
    		    $aHtmlValue['iCountCredit'] += $v['TeamCredit'];          // B
    		    $aHtmlValue['iCountAll']    += $v['TeamChannelBalance'];  // C
    		    $aHtmlValue['iCountHold']   += $v['TeamHoldBalance'];     // D
    		    $aHtmlValue['iCountAvail']  += $v['TeamAvailBalance'];    // E
    		    // A + B = D + E 
    		    $v['errBalance1'] = round($v['TeamHoldBalance']+$v['TeamAvailBalance']-$v['TeamCashBalance']-$v['TeamCredit'],4);
    		    // A + B = C
    		    $v['errBalance2'] = round($v['TeamChannelBalance']-$v['TeamCashBalance']-$v['TeamCredit'],4);
    		    $aHtmlValue['iCountError1']  += $v['errBalance1'];
    		    $aHtmlValue['iCountError2']  += $v['errBalance2'];
		    }
		}
		$GLOBALS['oView']->assign( 's', $aHtmlValue );
	    $GLOBALS['oView']->assign( 'aUserList', $aResult ); // 数据分配
	    $GLOBALS['oView']->assign( 'ur_here', '游戏币明细 - 自身' );
	    $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","fundlist"), 'text'=>'清空过滤条件' ) );
	    $oOrder->assignSysInfo();
	    $GLOBALS['oView']->display("report_fundlist.html");
		EXIT;
	}



	/**
	 * 查看充提报表
	 *   功能需求: 按总代查看一段时间内的充提情况，用于财务 (或销售管理员?) 检查充提情况
	 * URL = ./controller=report&action=inoutlist
	 * 完成度 : 100%
	 * @author Tom 090608
	 * HTML 可选搜索条件:
	 *   - 01, 时间范围1       sdate        ( 起始时间 )
	 *   - 02, 时间范围2       edate        ( 截止时间 )
	 */
    function actionInoutlist()
	{
	    // 01, 整理搜索条件
	    $aSearch['pn']       = isset($_GET['pn']) ? intval($_GET['pn']) : 100;
	    $aSearch['sdate']    = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 02:20:00'); // 默认当天
	    $aSearch['edate']    = isset($_GET['edate']) ? trim($_GET['edate']) : date('Y-m-d 02:20:00',strtotime('+1 day'));
	    $aSearch['sdate']    = getFilterDate( $aSearch['sdate'], 'Y-m-d H:i:s' );
	    $aSearch['edate']    = getFilterDate( $aSearch['edate'], 'Y-m-d H:i:s' );
	    $aSearch['istester'] = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
	    $aSearch['isfrozen'] = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
	    $aHtmlValue = array();
	    $aHtmlValue['sdate']    = $aSearch['sdate'];
	    $aHtmlValue['edate']    = $aSearch['edate'];
	    $aHtmlValue['istester'] = $aSearch['istester'];
	    $aHtmlValue['isfrozen'] = $aSearch['isfrozen'];
	    // 销售管理员的判断
	    $bIsSaleAdmin = FALSE;
	    /* @var $oAdminProxy model_adminproxy */
	    $oAdminProxy  = A::singleton('model_adminproxy');
	    $bIsSaleAdmin = $oAdminProxy->isSaleAdmin($_SESSION['admin']);
	    $sWhere = '';
	    if( $bIsSaleAdmin == TRUE )
	    {
	        $sSaleAdminUsers = $oAdminProxy->getSaleAdminUsers($_SESSION['admin']);
	        if( $sSaleAdminUsers == -1 )
	        {
	            $sWhere .= " AND 1<0 ";
	        }
	        else
	        {
	            $sWhere .= " AND `lvtopid` IN ( ".daddslashes($sSaleAdminUsers)." ) ";
	        }
	    }
	    unset($oAdminProxy);
	    /* @var $oOrder model_orders */
	    $oOrder = A::singleton('model_orders');
	    // 获取总代资金流入流出结果集
	    $aResult= $oOrder->getTopProxyCashInOut( $aSearch['sdate'], $aSearch['edate'], $sWhere,
	                                             $aSearch['istester'], $aSearch['isfrozen'] );	                                            
		
		$aHtmlValue['iSumHandCashIn']    	= 0; // 总计 人工充值金额
		$aHtmlValue['iSumHandCashOut']   	= 0; // 总计 人工提现金额
		/* louis */
		$aHtmlValue['iSumEmailHandCashIn']  = 0; // 总计 人工充值金额 for email deposit
		/* louis */
		
		$aHtmlValue['iSumCashIn']    = 0; // 总计 充值金额
		$aHtmlValue['iSumCashOut']   = 0; // 总计 提现金额
		$aHtmlValue['iSumCashDiff']  = 0; // 总计 充提结余
		$aHtmlValue['iSumCreditIn']  = 0; // 总计 信用充值金额
		$aHtmlValue['iSumCreditOut'] = 0; // 总计 信用扣减金额
		$aHtmlValue['iSumLpIn']      = 0; // 总计 理赔充值
		$aHtmlValue['iSumLpOut']     = 0; // 总计 理赔充值
		$aHtmlValue['iCounts']       = count($aResult);
		// 4/14/2010 add
		$aHtmlValue['iSumPaymentIn'] = 0; // 总计 在线充值金额
		$aHtmlValue['iSumPaymentOut'] = 0; // 总计 在线提现金额
		$aHtmlValue['iSumPaymentFeeIn'] = 0; //总计 在线充值手续费金额
		$aHtmlValue['iSumPaymentFeeOut'] = 0; //总计 在线提现手续费金额
		if( $aHtmlValue['iCounts'] > 0 )
		{ // 进行数据整理(更新订单号), 对当页数据进行小结
		    foreach( $aResult as &$v )
		    {
    		    $aHtmlValue['iSumHandCashIn']     += $v['handcashin'];
    		    $aHtmlValue['iSumHandCashOut']    += $v['handcashout'];
    		    /* louis */
    		    $aHtmlValue['iSumEmailHandCashIn']     += $v['emailhandcashin'];
    		    /* louis */
		    	$aHtmlValue['iSumCashIn']     += $v['cashin'];
    		    $aHtmlValue['iSumCashOut']    += $v['cashout'];
    		    $aHtmlValue['iSumCreditIn']   += $v['creditin'];
    		    $aHtmlValue['iSumCreditOut']  += $v['creditout'];
    		    $aHtmlValue['iSumLpIn']       += $v['cashlpin'];
    		    $aHtmlValue['iSumLpOut']      += $v['cashlpout'];
    		    $v['cashdiff']                =  $v['cashin'] - $v['cashout'];
    		    // 4/14/2010 add
    		    $aHtmlValue['iSumPaymentIn'] += $v['cashpaymentin'];
    		    $aHtmlValue['iSumPaymentOut'] += $v['cashpaymentout'];
    		    $aHtmlValue['iSumPaymentFeeIn'] += $v['cashpaymentfeein'] - $v['cashemailandhandfeein'];
    		    $aHtmlValue['iSumPaymentFeeOut'] += $v['cashpaymentfeeout'];
		    }
		}
		$aHtmlValue['iSumCashDiff'] = $aHtmlValue['iSumCashIn'] - $aHtmlValue['iSumCashOut'];
		$GLOBALS['oView']->assign( 's', $aHtmlValue );
	    $GLOBALS['oView']->assign( 'aUserList', $aResult ); // 数据分配
	    $GLOBALS['oView']->assign( 'ur_here', '充提报表' );
	    $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","inoutlist"), 'text'=>'清空过滤条件' ) );
	    $oOrder->assignSysInfo();
	    $GLOBALS['oView']->display("report_inoutlist.html");
		exit;
	}



    /**
	 * 查看频道理赔表
	 *   功能需求: 按总代查看一段时间内的充提情况，用于财务 (或销售管理员?) 检查充提情况
	 * URL = ./controller=report&action=inoutlist
	 * @author Tom 090608
	 * HTML 可选搜索条件:
	 *   - 01, 时间范围1       sdate        ( 起始时间 )
	 *   - 02, 时间范围2       edate        ( 截止时间 )
	 */
    function actionAmends()
	{
	    // 01, 整理搜索条件
	    $aSearch['pn']       = isset($_GET['pn']) ? intval($_GET['pn']) : 100;
	    $aSearch['sdate']    = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 02:20'); // 默认当天
	    $aSearch['edate']    = isset($_GET['edate']) ? trim($_GET['edate']) : date('Y-m-d 02:20',strtotime('+1 day'));
	    $aSearch['sdate']    = getFilterDate( $aSearch['sdate'], 'Y-m-d H:i' );
	    $aSearch['edate']    = getFilterDate( $aSearch['edate'], 'Y-m-d H:i' );
	    $aSearch['istester'] = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
	    $aSearch['isfrozen'] = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
	    $aHtmlValue = array();
	    $aHtmlValue['sdate']    = $aSearch['sdate'];
	    $aHtmlValue['edate']    = $aSearch['edate'];
	    $aHtmlValue['istester'] = $aSearch['istester'];
	    $aHtmlValue['isfrozen'] = $aSearch['isfrozen'];
	    // 销售管理员的判断
	    $bIsSaleAdmin = FALSE;
	    /* @var $oAdminProxy model_adminproxy */
	    $oAdminProxy  = A::singleton('model_adminproxy');
	    $bIsSaleAdmin = $oAdminProxy->isSaleAdmin($_SESSION['admin']);
	    $sWhere = '';
	    if( $bIsSaleAdmin == TRUE )
	    {
	        $sSaleAdminUsers = $oAdminProxy->getSaleAdminUsers($_SESSION['admin']);
	        if( $sSaleAdminUsers == -1 )
	        {
	            $sWhere .= " AND 1<0 ";
	        }
	        else
	        {
	            $sWhere .= " AND `lvtopid` IN ( ".daddslashes($sSaleAdminUsers)." ) ";
	        }
	    }
	    unset($oAdminProxy);
	    if( $aSearch['istester'] != -1 )
	    {
	        $sWhere .= " AND `istester` = " . $aSearch['istester'];
	    }
	    if( $aSearch['isfrozen'] != -1 )
	    {
	        $aSearch['isfrozen'] == 0 ? $sWhere .= " AND `isfrozen` = " . $aSearch['isfrozen'] : 
	                                    $sWhere .= " AND `isfrozen` >= " . $aSearch['isfrozen'];
	    }
	    // 获取频道结果集
	    /* @var $oChannel model_channels */
	    $oChannel = A::singleton('model_channels');
	    $aChannel = $oChannel->channelGetAll('',"`pid`=0");
	    unset($oChannel);
	    
	    // 获取总代频道理赔结果集
	    /* @var $oOrder model_orders */
	    $oOrder = A::singleton('model_orders');
	    $aResult= $oOrder->getTopProxyAmends( $aSearch['sdate'], $aSearch['edate'], $sWhere . " ORDER BY username" );
        $aHtmlValue['iSumProxy'] = array();
        $aHtmlValue['iSumProxyTotal'] = 0;
        $aHtmlValue['iSumProxy'][0]   = 0;
        for( $i=0; $i<count($aChannel); $i++ )
        {
            $aHtmlValue['iSumProxy'][ $aChannel[$i]['id'] ] = 0;
        }
        //print_rr($aResult);exit;
        foreach( $aResult as $v1 )
        {
            if( !empty($v1['channel']) )
            {
                foreach( $v1['channel'] AS $k => $v )
                {
                    if( !isset($aHtmlValue['iSumProxy'][$k]) )
                    {
                        $aHtmlValue['iSumProxy'][$k] = $v;
                    }
                    else 
                    {
                        $aHtmlValue['iSumProxy'][$k] += $v;
                    }
                    if( !isset($aHtmlValue['iSumProxyTotal'][$k]) )
                    {
                        $aHtmlValue['iSumProxyTotal'] = $v;
                    }
                    else 
                    {
                        $aHtmlValue['iSumProxyTotal'] += $v;
                    }
                }
            }
        }
		$aHtmlValue['iCounts']   = count($aResult); 
		$GLOBALS['oView']->assign( 's', $aHtmlValue );
	    $GLOBALS['oView']->assign( 'aUserList', $aResult ); // 数据分配
	    $GLOBALS['oView']->assign( 'aChannelList', $aChannel ); // 数据分配
	    $GLOBALS['oView']->assign( 'ur_here', '频道理赔表' );
	    $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","amends"), 'text'=>'清空过滤条件' ) );
	    $oOrder->assignSysInfo();
	    $GLOBALS['oView']->display("report_amends.html");
		EXIT;
	}
	
	
	
    /**
	 * 查看频道转账表
	 *   功能需求: 按总代查看一段时间内的充提情况，用于财务 (或销售管理员?) 检查充提情况
	 * URL = ./controller=report&action=inoutlist
	 * @author Tom 090608
	 * HTML 可选搜索条件:
	 *   - 01, 时间范围1       sdate        ( 起始时间 )
	 *   - 02, 时间范围2       edate        ( 截止时间 )
	 */
    function actionTransition()
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
	    $aHtmlValue = array();
	    $aHtmlValue['sdate']    = $aSearch['sdate'];
	    $aHtmlValue['edate']    = $aSearch['edate'];
	    $aHtmlValue['itype']    = $aSearch['itype'];
	    $aHtmlValue['istester'] = $aSearch['istester'];
	    $aHtmlValue['isfrozen'] = $aSearch['isfrozen'];
	    // 销售管理员的判断
	    $bIsSaleAdmin = FALSE;
	    /* @var $oAdminProxy model_adminproxy */
	    $oAdminProxy  = A::singleton('model_adminproxy');
	    $bIsSaleAdmin = $oAdminProxy->isSaleAdmin($_SESSION['admin']);
	    $sWhere = '';
	    if( $bIsSaleAdmin == TRUE )
	    {
	        $sSaleAdminUsers = $oAdminProxy->getSaleAdminUsers($_SESSION['admin']);
	        if( $sSaleAdminUsers == -1 )
	        {
	            $sWhere .= " AND 1<0 ";
	        }
	        else
	        {
	            $sWhere .= " AND `lvtopid` IN ( ".daddslashes($sSaleAdminUsers)." ) ";
	        }
	    }
	    unset($oAdminProxy);
	    if( $aSearch['istester'] != -1 )
	    {
	        $sWhere .= " AND `istester` = " . $aSearch['istester'];
	    }
	    if( $aSearch['isfrozen'] != -1 )
	    {
	        $aSearch['isfrozen'] == 0 ? $sWhere .= " AND `isfrozen` = " . $aSearch['isfrozen'] : 
	                                    $sWhere .= " AND `isfrozen` >= " . $aSearch['isfrozen'];
	    }
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


	    // 获取频道结果集
	    /* @var $oChannel model_channels */
	    $oChannel = A::singleton('model_channels');
	    $aChannel = $oChannel->channelGetAll(  ' `id`,`channel` ', "`pid`=0" );
	    
	    // TODO _a高频、低频并行前期临时程序
	    //print_rr($aChannel);exit;
//	    $aChannel[] = array( 'id'=>99 , 'channel' => '高频' );
	    // 临时代码结束
	    
	    unset($oChannel);

	    /**
	     * 1, 一条SQL, 获取 : 银行转出到各个平台的数额
	     *       总代ID=1  转出到低频10元
	     *                 转出到足球15元
	     * 
	     * 2, 一条SQL, 获取 : 各个平台转入到银行的数额
	     *       总代ID=1  低频转入12元
	     *                 足球转入17元
	     * 
	     * 要求返回数组类型为:
	     *      总代ID =>  [0_1] 转出低频 [10]
	     *                 [0_2] 转出足球 [15]
	     *                 [1_0] 低频转入 [12]
	     *                 [2_0] 足球转入 [17]
	     *  
	     */

	    // 获取总代频道理赔结果集
	    /* @var $oOrder model_orders */
	    $oOrder = A::singleton('model_orders');
	    $aResult= $oOrder->getTopProxyTransition( $aSearch['sdate'], $aSearch['edate'], $sWhere . " ORDER BY username", $sTranStatusSql );
	    $iRecordCount = count($aResult);

	    $aChannelTitle = array();
	    $iCountChannel = count($aChannel);
	    for( $i=0; $i<$iCountChannel; $i++ )
        {
            $aChannelTitle[] = '银行 > '. $aChannel[$i]['channel'];
            $aChannelTitle[] = $aChannel[$i]['channel'] . ' > 银行';
        }
//        print_rr($aResult);EXIT;

 
        $aChannelTotal = array(); // 初始化数组, 所有频道*2 + 1 ( 转账结余 )
        for( $i=0; $i<($iCountChannel*2+1); $i++ )
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
	    $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","transition"), 'text'=>'清空过滤条件' ) );
	    $oOrder->assignSysInfo();
	    $GLOBALS['oView']->display("report_transition.html");
		EXIT;
	}

	
	
	/**
	 * 查看银行快照报表
	 *   功能需求: 读取 banksnapshot 表的静态数据. 显示给用户快照信息
	 *   passport.banksnapshot 表存放在一个休市,禁止转账的时间点上, 全部总代团队的信息情况
	 *      - banksnapshot.ta   现金余额 ( A )
	 *      - banksnapshot.tb   信用欠款 ( B )
	 *      - banksnapshot.tc   账户余额 ( C )
	 *      - banksnapshot.td   冻结金额 ( D )
	 *      - banksnapshot.te   可用余额 ( E )
	 * URL = ./controller=report&action=banksnapshot
	 * @author Tom 090819
	 * HTML 可选搜索条件:
	 *   - 01, 报表日期             datas
	 *   - 02, 是否包含测试账户     includetestuser        ( 预留 )
	 *   - 03, 仅查看冻结用户       includelockuser        ( 预留 )
	 */
    function actionBanksnapshot()
	{
	    // 01, 整理搜索条件
	    $aSearch['pn']         = isset($_GET['pn']) ? intval($_GET['pn']) : 100;
	    $aSearch['dates']      = isset($_GET['dates']) ? $_GET['dates'] : -1;
	    $aSearch['istester']   = isset($_GET['istester']) ? intval($_GET['istester']) : 0;
	    $aSearch['isfrozen']   = isset($_GET['isfrozen']) ? intval($_GET['isfrozen']) : 0;
	    $aHtmlValue = array();
	    $aResult = array();
	    /* @var $oBankSnapshot model_banksnapshot */
	    $oBankSnapshot = A::singleton('model_banksnapshot');
	    $aHtmlValue['dayopts'] = $oBankSnapshot->getDistintDays( FALSE, $aSearch['dates'] );

	    // 02, WHERE 语句拼接 ----------------------------------------------------------
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
    	    $aHtmlValue['dates'] = $aSearch['dates'];
    	    //$aHtmlValue['yesterday'] = date('Y-m-d', strtotime($aSearch['dates']) );
    	    $aResult = $oBankSnapshot->getSnapshotDatas( $aSearch['dates'], $sWhere . " ORDER BY username" );
	    }
	    //print_rr($aResult['data']);EXIT;
	    $aHtmlValue['istester']            = $aSearch['istester'];
	    $aHtmlValue['isfrozen']            = $aSearch['isfrozen'];
        $aHtmlValue['total']['cashin']     = 0;
        $aHtmlValue['total']['cashout']    = 0;
        $aHtmlValue['total']['tranferin']  = 0;
        $aHtmlValue['total']['tranferout'] = 0;
        $aHtmlValue['total']['cashdiff']   = 0;
        $aHtmlValue['total']['todaycash']  = 0;
        $aHtmlValue['total']['ta']         = 0;
        $aHtmlValue['total']['tb']         = 0;
        $aHtmlValue['total']['tc']         = 0;
        $aHtmlValue['total']['td']         = 0;
        $aHtmlValue['total']['te']         = 0; 
        foreach( $aResult['data'] AS $v )
        {
            $aHtmlValue['total']['cashin']     += $v['cashin'];
            $aHtmlValue['total']['cashout']    += $v['cashout'];
            $aHtmlValue['total']['tranferin']  += $v['tranferin'];
            $aHtmlValue['total']['tranferout'] += $v['tranferout'];
            $aHtmlValue['total']['cashdiff']   += $v['cashdiff'];
            $aHtmlValue['total']['todaycash']  += $v['todaycash'];
            $aHtmlValue['total']['ta']         += $v['ta'];
            $aHtmlValue['total']['tb']         += $v['tb'];
            $aHtmlValue['total']['tc']         += $v['tc'];
            $aHtmlValue['total']['td']         += $v['td'];
            $aHtmlValue['total']['te']         += $v['te']; 
        }

	    $aHtmlValue['sTimes']  = $aResult['time'];
	    $aHtmlValue['iCounts'] = count($aResult['data']);
	    /* @var $oBankSnapshot model_banksnapshot */
	    $oBankSnapshot = A::singleton('model_orders');
	    $aHtmlValue['cashinordertypeid']     = ORDER_TYPE_SJCZ.",".ORDER_TYPE_KJCZ.",".ORDER_TYPE_XYCZ.",".ORDER_TYPE_ZXCZ.",".ORDER_TYPE_RGCZ.",".ORDER_TYPE_SXFFH;
	    $aHtmlValue['cashoutordertypeid']    = ORDER_TYPE_BRTX.",".ORDER_TYPE_XJTX.",".ORDER_TYPE_BRFQTX.",".ORDER_TYPE_XJFQTX
	                                           .",".ORDER_TYPE_KJTX.",".ORDER_TYPE_ZXTXKK;
	    $aHtmlValue['tranferoutordertypeid'] = ORDER_TYPE_YHZC.",".ORDER_TYPE_ZZZC;
	    $aHtmlValue['tranferinordertypeid']  = ORDER_TYPE_ZZZR.",".ORDER_TYPE_PDXEZR.",".ORDER_TYPE_ZRYH;
	    $aHtmlValue['sdate'] = date("Y-m-d H:i:s",strtotime($aHtmlValue['sTimes'])-60*60*24);
		$GLOBALS['oView']->assign( 's', $aHtmlValue );
	    $GLOBALS['oView']->assign( 'aDataList', $aResult['data'] ); // 数据分配
	    $GLOBALS['oView']->assign( 'ur_here', '银行快照报表' );
	    $oBankSnapshot->assignSysInfo();
	    $GLOBALS['oView']->display("report_banksnapshot.html");
		EXIT;
	}



	function actionLedgersnapshot()
	{
		$oChannel = A::singleton('model_channels');
        $aChannel = $oChannel->channelGetAll(  ' `id`,`channel` ', "`pid`=0" );
        $channels = array();
        $channels[0] = "银行";
        foreach($aChannel as $i=>$v)
        {
        	$channels[$v["id"]] = $v["channel"];
        }
        // TODO _a高频、低频并行前期临时程序
        /*$channels[99] = "高频";*/
        $GLOBALS['oView']->assign( "channel",$channels );
        
		if(isset($_GET["dates"])&&!empty($_GET["dates"]))
		{
			$oDomian = new model_domains();
            $aResult = $oDomian->domainGetOne("domain"," `status`='1' limit 1 ");
            if(empty($aResult))
            {
            	sysMessage( '获取域名失败', 1 );
            }        
	        $aBank = array();
	        $sDate   = getFilterDate( $_GET["dates"], "Y-m-d" );
	        $iTest   = isset($_GET["istester"])&&is_numeric($_GET["istester"]) ? intval($_GET["istester"]) : 0;
	        $iFrozen = isset($_GET["isfrozen"])&&is_numeric($_GET["isfrozen"])? intval($_GET["isfrozen"]) : 0;
	        $aHtmlValue['dates']      = $sDate;
            $aHtmlValue['istester']   = $iTest;
            $aHtmlValue['isfrozen']   = $iFrozen;
	        foreach( $channels as $i=>$v )
	        {
		        $oChannelApi = new channelapi( $i, 'banksnapshot', TRUE );
		        $oChannelApi->setBaseDomain( $aResult["domain"] );
		        $oChannelApi->setResultType('serial');
		        $oChannelApi->sendRequest( array('dates'=>$sDate,"istester"=>$iTest,"isfrozen"=>$iFrozen ) );
		        $a = $oChannelApi->getDatas();
		        if($a["status"]!="ok")
		        {
		          unset($a);	
		        }
		        else
		        {		        
			        $aBank[$i]["total"] = $a["data"]["total"];
			        unset($a["data"]["total"]);
			        $aBank[$i]["datas"] = $a["data"];
			        $aBank[$i]["name"] = $v;
		        }
	        }
	        $Tatal = array();
	        foreach( $aBank as $i=>$v )
            {	           
			     if( $i==0 )
			     {
			     	$Tatal["datas"] = $aBank[0]["datas"];
			     	$Tatal["total"] = $aBank[0]["total"];
			     }
			     else
			     {
			     	$Tatal["total"]["ta"]           += $v["total"]["ta"];
                    $Tatal["total"]["tb"]           += $v["total"]["tb"];
                    $Tatal["total"]["tc"]           += $v["total"]["tc"];
                    $Tatal["total"]["td"]           += $v["total"]["td"];
                    $Tatal["total"]["te"]           += $v["total"]["te"];
                    $Tatal["total"]["totalbuy"]     += $v["total"]["totalbuy"];
                    $Tatal["total"]["totalpoint"]   += $v["total"]["totalpoint"];
                    $Tatal["total"]["totalbingo"]   += $v["total"]["totalbingo"];
                    $Tatal["total"]["totalbalance"] += $v["total"]["totalbalance"];
                    $Tatal["total"]["cashin"]       += $v["total"]["cashin"];
                    $Tatal["total"]["cashout"]      += $v["total"]["cashout"];
                    $Tatal["total"]["tranferin"]    += $v["total"]["tranferin"];
                    $Tatal["total"]["tranferout"]   += $v["total"]["tranferout"];
                    $Tatal["total"]["todaycash"]    += $v["total"]["todaycash"];
                    $Tatal["total"]["cashdiff"]     += $v["total"]["cashdiff"];
			     	foreach($v["datas"] as $iUserId => $vUser)
			     	{
			     		if(!isset($Tatal["datas"][$iUserId]))
			     		{
	                        $Tatal["datas"][$iUserId] = $vUser;
			     		}
			     		else
			     		{
				     		$Tatal["datas"][$iUserId]["ta"]           += $vUser["ta"];
				     		$Tatal["datas"][$iUserId]["tb"]           += $vUser["tb"];
				     		$Tatal["datas"][$iUserId]["tc"]           += $vUser["tc"];
				     		$Tatal["datas"][$iUserId]["td"]           += $vUser["td"];
				     		$Tatal["datas"][$iUserId]["te"]           += $vUser["te"];
				     		$Tatal["datas"][$iUserId]["totalbuy"]     += $vUser["totalbuy"];
				     		$Tatal["datas"][$iUserId]["totalpoint"]   += $vUser["totalpoint"];
				     		$Tatal["datas"][$iUserId]["totalbingo"]   += $vUser["totalbingo"];
				     		$Tatal["datas"][$iUserId]["totalbalance"] += $vUser["totalbalance"];
				     		$Tatal["datas"][$iUserId]["cashin"]       += $vUser["cashin"];
				     		$Tatal["datas"][$iUserId]["cashout"]      += $vUser["cashout"];
				     		$Tatal["datas"][$iUserId]["tranferin"]    += $vUser["tranferin"];
				     		$Tatal["datas"][$iUserId]["tranferout"]   += $vUser["tranferout"];
				     		$Tatal["datas"][$iUserId]["todaycash"]    += $vUser["todaycash"];
				     		$Tatal["datas"][$iUserId]["cashdiff"]     += $vUser["cashdiff"];
			     		}
			     	}
			     }
            }
            $Tatal["name"] = "总账";
	        $aBank[100] = $Tatal;
	        $GLOBALS['oView']->assign("data",$aBank);
		}
		else
		{
			$aBank = array();
			foreach($channels as $i=>$v)
			{
				$aBank[$i]["datas"] = array();
                $aBank[$i]["name"] = $v;
			}
			$aHtmlValue['dates']      = -1;
            $aHtmlValue['istester']   = 0;
            $aHtmlValue['isfrozen']   = 0;
            $aBank[100]["datas"]= array();
            $aBank[100]["name"] = "总账";
            $GLOBALS['oView']->assign("data",$aBank);
		}
		/* @var $oBankSnapshot model_banksnapshot */
        $oBankSnapshot = A::singleton('model_banksnapshot');
        $aHtmlValue['dayopts'] = $oBankSnapshot->getDistintDays( FALSE, $aHtmlValue['dates'] );
		$GLOBALS['oView']->assign( 's', $aHtmlValue );
        $GLOBALS['oView']->assign( 'ur_here', '总照快照报表' );
        $oChannel->assignSysInfo();
        $GLOBALS['oView']->display("report_ledgersnapshot.html");
        EXIT;
	}

	
	/**
	 * 在线充值 记录表
	 * @author Jim  3/12/2010
	 */
	public function actionOnlineLoadList()
	{
	    // 01, 整理搜索条件
	    $aSearch['status']     = isset($_GET['status']) ? $_GET['status'] : 0; // 默认显示支付中, 掉单3，全部-1
	    $aSearch['losttodoname']  = isset($_GET['losttodoname']) ? daddslashes(trim($_GET['losttodoname'])) : "";
	    
	    $aSearch['loadtype'] = isset($_GET['loadtype']) ? daddslashes(trim($_GET['loadtype'])) : "";
	    $aSearch['loadcurrency'] = isset($_GET['loadcurrency']) ? daddslashes(trim($_GET['loadcurrency'])) : "";
	    
	    //$aSearch['userid']   = isset($_GET['userid']) ? intval(trim($_GET['userid'])) : "";
	    $aSearch['username']   = isset($_GET['username']) ? daddslashes(trim($_GET['username'])) : "";
	    $aSearch['sdate']      = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 02:20');
	    $aSearch['edate']      = isset($_GET['edate']) ? trim($_GET['edate']) : date('Y-m-d 02:20', strtotime('+1 day'));
	    $aSearch['sdate2']     = isset($_GET['sdate2']) ? trim($_GET['sdate2']) : "";
	    $aSearch['edate2']     = isset($_GET['edate2']) ? trim($_GET['edate2']) : "";

	    //format time
	    $aSearch['sdate']      = getFilterDate( $aSearch['sdate'],  'Y-m-d H:i' );
	    $aSearch['edate']      = getFilterDate( $aSearch['edate'],  'Y-m-d H:i' );
	    $aSearch['sdate2']     = getFilterDate( $aSearch['sdate2'], 'Y-m-d H:i' );
	    $aSearch['edate2']     = getFilterDate( $aSearch['edate2'], 'Y-m-d H:i' );
	   	
	    //组装查询条件
	    $aSearch['p']  = isset($_GET['p'])  ? intval($_GET['p'])  : 1;    // 分页用1
	    $aSearch['pn'] = isset($_GET['pn']) ? intval($_GET['pn']) : 20;   // 分页用2

	    $aParam = array('PageSize' => $aSearch['pn'],
			'Page' => $aSearch['p'],
			//'UserId' => $aSearch['userid'],
			'UserName' => $aSearch['username'],
			'LoadType' => $aSearch['loadtype'],
			'LoadCurrency' => $aSearch['loadcurrency'],
			'LoadStatus' => intval($aSearch['status']),
			'LostTodo' => $aSearch['losttodoname'],
			'LoadStartTime' => $aSearch['sdate'],
			'LoadEndTime' => $aSearch['edate'],
			'LostStartTime' => $aSearch['sdate2'],
			'LostEndTime' => $aSearch['edate2']
		);
		
		$oLoadlist = new model_pay_loadlist($aParam,'','array');
		$oLoadlist->finishAttr();			// 涉及掉单处理 仅提取ques的权限
        $aLoadlistData = $oLoadlist->Data;
        
        //获取所有支付接口 以及币种 列表
        //$aPar = array('LoadStatus' => 1);
        $oPaylist = new model_pay_payaccountlist();
        $aPaylist = $oPaylist->allList(false,false);
        $aPayport = $aCurr = array();
       
        foreach ($aPaylist AS $aP){
        	if ( array_search($aP['ads_payport_id'],$aPayport) === false ) $aPayport[$aP['ads_payport_id']] = $aP['ads_payport_name'];
        	if ( array_search($aP['acc_currency'],$aCurr) === false ) $aCurr[] = $aP['acc_currency'];
        	
        }
	
        $oPager = new pages( $oLoadlist->TotalCount, $aSearch['pn'], 10);    // 分页类 new(总行数,每页多少行,显示多少个页面按钮)
        $GLOBALS['oView']->assign( 'pages', $oPager->show() ); // 分页用4
		$GLOBALS['oView']->assign( 'paylist', $aPayport );
		$GLOBALS['oView']->assign( 'paycurr', $aCurr );
		$GLOBALS['oView']->assign( 's', $aSearch );
	    $GLOBALS['oView']->assign( 'Loadlist', $aLoadlistData ); // 数据分配
	    $GLOBALS['oView']->assign( 'ur_here', '在线充值检查' );
	    $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("report","onlineloadlist"), 'text'=>'清空过滤条件' ) );
	    $oLoadlist->assignSysInfo();
	    $GLOBALS['oView']->display("report_onlineloadlist.html");
		exit;
	}
	
	
	/**
	 * 充值掉单处理
	 * @author JIm 3/12/2010
	 *
	 *  查询单状态，若成功则给用户加资金，
	 */
	public function actionOnlineLoadToQuery(){
		//组装数据
		$id=$_GET['id'];
		$sDisplayType = $_GET['st'];	//以Ajax或正常页面跳转方式htm，选择
		if ( !isset($id) || !is_numeric($id) ){
			if ($sDisplayType == 'htm'){
				sysMessage('数据错误！',1);
			}else{
				echo 104;
			}
		}
		//收集原始数据  根据不同支付接口 调用不同的API
		
		$oLoad = new model_pay_loadinfo($id,'-1');	// -1 将列出所有单子
		
		//获取接口信息
		$oPayport = new model_pay_payaccountinfo($oLoad->AccId);
		$oPayport->OptType='query';
		$aInputData['uniqueid'] =  'L'.$oLoad->Id;
		$aInputData['userid'] = $oLoad->UserId;
		
		$sPayportDataAPIClassName = 'model_pay_apidata'.strtolower($oPayport->PayportName);
		$oAPIData = new $sPayportDataAPIClassName($oPayport->Id,'query',$aInputData,0);
		// 封装数据 发送查询请求
		$aGetData = $oAPIData->send();
		$oAPIData->Model = 'query';
		$oAPIData->ReceiveType = 'XML';
		$oAPIData->ReceiveData = $aGetData;
		$oAPIData->receive();
		
		// 整理结果，判断 PaymentStatus 属性数组 进行资金处理 加钱 
		$oAPIData->paymentInfo();

		$iOpAdminid = $_SESSION['admin'];
		$oLoad->LoadStatus  =  ($oAPIData->PaymentStatus['Status'] == 'success') ? '1' : $oAPIData->PaymentStatus['Status'];
		if ($oAPIData->PaymentStatus['Status'] == 'success'){
        		// 成功，进行加钱处理
        		
        		// 充值财务处理开始 ----
        		$oUserFund = new model_userfund();
            	$sFields   = " u.`userid`,u.`username`,uf.`availablebalance`,u.`authtoparent` ";
	            $aUserinfo = $oUserFund->getFundByUser( intval($oLoad->UserId), $sFields );
            	if( empty($aUserinfo) )
            	{
                	$err_str = '客户资金帐户因其他操作锁定中。请稍后重试！';
                	$err_num = 103;
                	$oLoad->LoadStatus = '3';
            	}else{
            		//加钱 +金额
        			$sLogstr = $iOpAdminid.'从'.$oPayport->PayportName.'确认充值'.$oLoad->LoadAmount.' '.$oPayport->AccCurrency.'给'.$oLoad->UserId;
					
        			
            			//计算手续费
						$aFee = $oPayport->paymentFee($oLoad->LoadAmount);
						$sLogstr = '从'.$oPayport->PayportName.'充值'.$oLoad->LoadAmount.$oPayport->AccCurrency;
        				$sLogstrFee = '收取充值手续费'.$aFee[1].$oPayport->AccCurrency;
        				if ( !is_numeric($aFee[1]) ){
        					$oOL->saveLogs('手续费计算错误');
							$oOL->Lock('unlock');
							die;
        				}
        				//增加payaccount余额
        				if(!$oPayport->saveBalance($p03_payamount)){
        					$oOL->saveLogs('user:'.$iOpUserid.' load, 分账户余额增加失败'.$p03_payamount);
        				}
        				
        				
        			$bAddmoney = $oUserFund->systemOnlineLoadforUser(0, intval($oLoad->UserId), intval($oLoad->LoadAmount), $sLogstr);
        			if ($bAddmoney === true){
        				$err_str = '充值成功！';
        				$err_num = 888;
        				$oLoad->LoadStatus = '1';
        			}else{
        				$err_str = '充值失败！';
        				$err_num = 102;
        				$oLoad->LoadStatus = '3';
        			}
        			
            	}
        		// 充值财务处理结束 ----
        	}elseif ($oAPIData->PaymentStatus['Status'] == 2){
        		$oLoad->LoadStatus = '2';
        		$err_str = '支付单废止，无须再次操作！';
        		$err_num = 101;
        	}else{
        		$oLoad->LoadStatus = '3';
        		$err_str = '支付单继续挂起，请稍候重试！';
        		$err_num = 100;
        	}
        	
        	//$oLoad->Id = substr($oAPIData->PaymentStatus['PaymentId'],1);
        	$oLoad->RebackTime = date('Y-m-d H:i:s');
        	$oLoad->RebackNote = $oAPIData->PaymentStatus['Code'].$oAPIData->PaymentStatus['Valid'];
        	$bLoad = $oLoad->set();
        	$oAPIData->saveOptLogs(
        		array(
        			'RefID'=>$oAPIData->PaymentStatus['PaymentId'],
        			'Info'=>$oLoad->RebackNote
        		)
        	);
        	// 给出提示
            $aLocation = array(0=>array("text" => "在线充值列表","href" => url("report","onlineloadlist")));
             
           if ($sDisplayType == 'htm'){
           		sysMessage( $err_str, 1, $aLocation );
           }else{
           		echo $err_num;
           }
			
	}
	
	
	/**
	 * 提现申请列表
	 *
	 * @version 	v1.0	2010-03-12
	 * @author 		louis
	 */
	public function actionwithdrawapplylist(){
		// 获取支付接口列表
    	$oApiList = new model_pay_payportlist(array(), '', 'array');
    	
    	// 循环支付接口列表，去除重复的币种信息
    	$aMoneyType = array();
    	if (!empty($oApiList->Data)){
    		foreach ($oApiList->Data as $key => $currency){
	    		if (array_search($currency['currency'], $aMoneyType) === false){
	    			$aMoneyType[$currency['currency']] = $currency['currency'];
	    		}
	    	}
    	}
    	
    	
    	
		// 页面搜索信息
		$aHtml = array();
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$oFODetailsList = new model_withdraw_fodetailslist();
		$aHtml['status'] 		= $oFODetailsList->Status 		= isset($_GET['status']) ? intval($_GET['status']) : 1;
		$aHtml['username'] 		= $oFODetailsList->UserName 	= isset($_GET['username']) ? trim($_GET['username']) : '';
		$aHtml['operater'] 		= $oFODetailsList->Operater		= isset($_GET['operater']) ? trim($_GET['operater']) : '';
		$aHtml['no'] 			= $oFODetailsList->No			= isset($_GET['no']) ? trim($_GET['no']) : '';
		$aHtml['api_id'] 		= $oFODetailsList->ApiId		= isset($_GET['api_id']) ? intval($_GET['api_id']) : '';
		$aHtml['bank_name'] 	= $oFODetailsList->BankName		= isset($_GET['bank_name']) ? trim($_GET['bank_name']) : '';
		$aHtml['money_type'] 	= $oFODetailsList->MoneyType	= isset($_GET['money_type']) ? trim($_GET['money_type']) : '';
		$aHtml['from_no'] 		= $oFODetailsList->MinId		= isset($_GET['from_no']) ? intval($_GET['from_no']) : '';
		$aHtml['to_no'] 		= $oFODetailsList->MaxId		= isset($_GET['to_no']) ? intval($_GET['to_no']) : '';
								  $oFODetailsList->Pages		= intval($p);
		$oFODetailsList->init();
		// 计算当前面的数据统计
		$fTotal = 0;
		foreach ($oFODetailsList->Data as $k => $v){
			$fTotal += $v['amount'];
		}
		$oPager = new pages( $oFODetailsList->TotalCount, DEFAULT_PAGESIZE, 0);    // 分页用3
		
		// 提取模板列表
		$oWDReportList = new model_withdraw_WithdrawReportList();
		$oWDReportList->StartTime	= 1; // 提取可用模板
		$oWDReportList->init();
		
		
		// 获取提现审核未通过原因列表
		$oWDUnverifyReasonList = new model_withdraw_WDUnverifyReasonList();
		$oWDUnverifyReasonList->Status = 1; // 只提取启用的未通过原因
		$oWDUnverifyReasonList->init();
		
		
		$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
        $GLOBALS['oView']->assign( 'ur_here', '提现申请' );
        $GLOBALS['oView']->assign( 'aHtml', $aHtml );
        $GLOBALS['oView']->assign( 'withdrawInfo', $oFODetailsList->Data );
        $GLOBALS['oView']->assign( 'apilist', $oApiList->Data );
        $GLOBALS['oView']->assign( 'moneytype', $aMoneyType );
        $GLOBALS['oView']->assign( 'total', $fTotal );
        $GLOBALS['oView']->assign( 'unverifyreason', $oWDUnverifyReasonList->Data );
        $GLOBALS['oView']->assign( 'reportlist', $oWDReportList->Data );
        $oFODetailsList->assignSysInfo();
        $GLOBALS['oView']->display("report_withdrawapplylist.html");
        EXIT;
	}
	
	
	/**
	 * 审核提现申请操作
	 *
	 * @version 	v1.0	2010-05-10
	 * @author 		louis
	 */
	public function actionVerify(){
		if ( $_POST['flag'] == 'verify'){
			!(empty($_POST['applys']) || empty($_POST['operate']) || ($_POST['operate'] ==3 && $_POST['finalreason'] == ""))	or sysMessage("提交数据有误", 0);
			$oDealApply = new model_withdraw_dealapply($_POST['applys'], $_POST['operate'], $_POST['finalreason']);
			$aLinks = array(
				0 => array(
					'text' => '返回提现申请列表',
					'href' => '?controller=report&action=withdrawapplylist'
				)
			);
			if ($oDealApply->Error === true){
				sysMessage("操作成功", 0, $aLinks);
			} else if($oDealApply->Error == -1){
				sysMessage('系统结算时间,暂停操作', 0, $aLinks);
			} else 
				sysMessage("操作失败", 1, $aLinks);
		}
	}
	
	
	/**
	 * 提现明细详情
	 *
	 * @version 	v1.0	2010-03-12
	 * @author 		louis
	 */
	public function actionapplydetail(){
		$iId = $_GET['id'] > 0 ? $_GET['id'] : $_POST['id'];
		if (!is_numeric($iId) || intval($iId) <= 0) sysMessage("您提交的数据有误，请核对后重新提交！", 1);
		
		$aId = array(
			0 => $iId
		);
		$_POST['status'] = isset($_POST['status']) ? $_POST['status'] : '';
		if ($_POST['status'] == "pass" || $_POST['status'] == "unpass"){
			switch ($_POST['status']){
				case 'pass' :
					$iStatus = 2;
					break;
				case 'unpass' :
					$iStatus = 3;
					break;
			}
			// 通过审核
			$oDealApply = new model_withdraw_dealapply($aId, $iStatus);
			if ($oDealApply->Error === true){
				echo true;die;
			} else {
				echo false;die;
			}
		}
		// 获取在线提现申请的所有信息
		$oFODetails = new model_withdraw_fundoutdetail($iId);
		
		// 检查数据是否存在
		$oFODetails->Id > 0 or sysMessage("您提交的数据有误，请核对后重新提交！",1);
		
		$oUser = A::singleton('model_user');
		// 获取发起在线提现用的总代用户信息
		$aTopInfo = $oUser->getTopProxyId( $oFODetails->UserId, true );
		
        $GLOBALS['oView']->assign( 'ur_here', '在线提现申请详情' );
        $GLOBALS['oView']->assign( 'id', $oFODetails->Id );
        $GLOBALS['oView']->assign( 'fromuser', $oFODetails->UserName );
        $GLOBALS['oView']->assign( 'topusername', $aTopInfo['username'] );
        $GLOBALS['oView']->assign( 'totalmoney', $oFODetails->SourceMoney );
        $GLOBALS['oView']->assign( 'amount', $oFODetails->Amount );
        $GLOBALS['oView']->assign( 'charge', $oFODetails->Charge );
        $GLOBALS['oView']->assign( 'bank', $oFODetails->BankName );
        $GLOBALS['oView']->assign( 'province', $oFODetails->Province );
        $GLOBALS['oView']->assign( 'city', $oFODetails->City );
        $GLOBALS['oView']->assign( 'branch', $oFODetails->Branch );
        $GLOBALS['oView']->assign( 'account_name', $oFODetails->AccountName );
        $GLOBALS['oView']->assign( 'account', $oFODetails->Account );
        $GLOBALS['oView']->assign( 'request_time', $oFODetails->RequestTime );
        $GLOBALS['oView']->assign( 'IP', $oFODetails->IP );
        $GLOBALS['oView']->assign( 'CDNIP', $oFODetails->CDNIP );
        $GLOBALS['oView']->assign( 'operater', $oFODetails->Operater );
        $GLOBALS['oView']->assign( 'finish_time', $oFODetails->FinishTime );
        $GLOBALS['oView']->assign( 'status', $oFODetails->Status );
        $GLOBALS['oView']->assign( 'back', $_SERVER['HTTP_REFERER'] );
        $oFODetails->assignSysInfo();
        $GLOBALS['oView']->display("report_applydetail.html");
        EXIT;
	}
	
	/**
	 * 向提现接口发起提现申请（ajax）
	 *
	 * @version 	v.10	2010-03-14
	 * @author 		louis
	 * 
	 * @return 		int		信息码
	 */
	public function actionajaxwithdraw(){
		if ($_GET['paymentid']){
			// 如果为回传订单id,则通过回传订单id,获取申请提现明细id
			$oFODetails = new model_withdraw_fundoutdetail();
			$oFODetails->No = "FO" . $_GET['paymentid'];
			$aResult = $oFODetails->getInfoByOrderId();
		}
		$iPaymentId = $aResult['id'] > 0 ? $aResult['id'] : $_GET['id'];
		if (!is_numeric($iPaymentId) || intval($iPaymentId) <= 0)	echo -1;
		$sRequestTime = date("Y-m-d H:i:s",time());
		$oFODetails = new model_withdraw_fundoutdetail($iPaymentId);
		// 检查可提现次数
		if ($oFODetails->RemainTimes <= 0) echo -2;
		$iWithdrawStatus = '';
		$iOperateStatus = '';
		// 01检查提交订单在支付接口的状态,02 更改提现申请表状态等信息，03 写入划款明细表信息
		// 检查单子状态(与提现接口交互)
		$aQuery = array(
			'userid'		=> $oFODetails->UserId,
			'amount'		=> $oFODetails->Amount,
			'uniqueid'		=> $oFODetails->No
		);

		$sPayportDataAPIClassName = 'model_pay_apidata' . strtolower($oFODetails->ApiName);
		$oWithdrawStatus = new $sPayportDataAPIClassName($oFODetails->ApiId,'withdrawquery',$aQuery, 0);
		$aResult = $oWithdrawStatus->send();
		$oWithdrawStatus->ReceiveType = 'XML';
		$oWithdrawStatus->ReceiveData = $aResult;
		$oWithdrawStatus->receive();
		$oWithdrawStatus->paymentInof();
		// 如果查询结果为掉单，则重新向提现接口发起提现操作
		if ($oWithdrawStatus->PaymentStatus['Status'] == 3 || $oWithdrawStatus->PaymentStatus['Status'] == ''){ 
			$aData = array(
				'userid' 		=> $oFODetails->UserId,
				'uniqueid'		=> $oFODetails->No,
				'currency'		=> $oFODetails->MoneyType,
				'amount'		=> $oFODetails->Amount,
				'bankaccount'	=> $oFODetails->Account,
				'accountname'	=> $oFODetails->AccountName,
				'bankname'		=> $oFODetails->BankName,
				'province'		=> $oFODetails->Province,
				'city'			=> $oFODetails->City,
				'branch'		=> $oFODetails->Branch
			);
			$sPayportDataAPIClassName = 'model_pay_apidata' . strtolower($oFODetails->ApiName);
			// 接收提现操作信息
			$oPayment = new $sPayportDataAPIClassName($oFODetails->ApiId, 'withdraw', $aData);
			$aResult = $oPayment->send();
			$oPayment->ReceiveType = 'XML';
			$oPayment->ReceiveData = $aResult;
			$oPayment->receive();
			$oPayment->paymentInof();
			if ($oPayment->PaymentStatus['Status'] == 'success'){ // 成功
				$iWithdrawStatus = 5;
				$iOperateStatus = 1;
			} else if($oPayment->PaymentStatus['Status'] == 2){ // 失败
				$iWithdrawStatus = 6;
				$iOperateStatus = 0;
			} else if ($oPayment->PaymentStatus['Status'] == 3 || $oPayment->PaymentStatus['Status'] == ''){ // 处理中或者未接收到数据
				$iWithdrawStatus = 7;
				$iOperateStatus = 2;
			}
		} else if ($oWithdrawStatus->PaymentStatus['Status'] == 2){
			// 查询结果为失败，则直接将提现申请状态和划款明细状态改为失败
			$iWithdrawStatus = 6;
			$iOperateStatus = 0;
		} else if ($oWithdrawStatus->PaymentStatus['Status'] == 'success'){
			// 查询结果为成功，将提现申请状态和划款明细状态改为成功
			$iWithdrawStatus = 5;
			$iOperateStatus = 1;
		}
		// 扣减一次可提现次数
	    $oFODetail->reduceTimes();
		
		// 用户信息数组
    	$aUserInfo = array();
    	$aUserInfo['userid'] 	= $oFODetails->UserId;
    	$aUserInfo['username'] 	= $oFODetails->UserName;
    	// 只有发起提现操作返回的成功才操作用户账户，查询订单的成功状态不能操作用户账户
    	if ($oPayment->PaymentStatus['Status'] == 'success'){
			// 账变信息数组
			$aOrders = array();
	    	$aOrders['iFromUserId'] 	= $oFODetails->UserId; // (发起人) 用户id
	    	$aOrders['iToUserId'] 		= $oFODetails->UserId; // (关联人) 用户id
	    	$aOrders['fMoney'] 			= floatval($oFODetails->Amount - $oFODetails->Charge); // 账变的金额变动情况
	    	$aOrders['charge']			= $oFODetails->Charge;
	    	$aOrders['iChannelID'] 		= 0; // 发生帐变的频道ID
	    	$aOrders['iAdminId'] 		= $_SESSION['adminid']; // 管理员id
    	}
    	// 提现申请数据信息
    	$aDetail = array();
    	$aDetail['id']			= $iPaymentId;
    	$aDetail['finish_time'] = date("Y-m-d H:i:s", time());
    	$aDetail['operater']	= $_SESSION['adminname'];
    	// 如果没有状态值则表示提现接口划款已成功，但平台没有接收到，所以状态应改为成功
    	$aDetail['status'] 		= isset($iWithdrawStatus) ? $iWithdrawStatus : 5; // 提现申请状态 
    	
    	// 划款明细信息
    	$aOperate = array();
    	$aOperate['request_time']	= $sRequestTime;
    	$aOperate['finish_time']	= date("Y-m-d H:i:s", time());
    	$aOperate['operater']		= $_SESSION['adminname'];
    	$aOperate['return_code']	= $oPayment->PaymentStatus['Code']; // api接口返回的信息码
    	// 如果没有状态值则表示提现接口划款已成功，但平台没有接收到，所以状态应写入成功
    	$aOperate['status']			= isset($iOperateStatus) ? $iOperateStatus : 1; // 根据返回的信息码决定状态值
    	
	    $oOperate = new model_withdraw_withdrawoperate( $aUserInfo, $aOrders, $aDetail, $aOperate );
    	if ($oOperate->bError){
			// 成功
			echo 'success';
		} else {
			// 失败
			echo 'failed';
		}
	}
	
	
	
	/**
	 * 提现申请报表下载，生成报表成功后，返回操作数组，将前台页面复选框置为不可用
	 *
	 * @version 	v1.0	2010-03-23
	 * @author 		louis
	 * 
	 * @return 		source 
	 */
	public function actiondownload(){
		if ($_POST['flag'] != 'download' || empty($_POST['applys']) || intval($_POST['report']) <= 0)
			sysMessage("数据提交错误", 1);

		$aLinks = array(
			0 => array(
				'text' => '关闭本页面',
				'href' => 'javascript:window.close()'
			)
		);
		
		// 组合id串
		$oFODetails = new model_withdraw_fundoutdetail();
		$oFODetails->IdList = implode(',', $_POST['applys']);
		
		$sIdList = $oFODetails->downloadedInfo();
		if ($sIdList > 0){
			sysMessage("您的下载信息中，用户编号为{$sIdList}的记录已经被下载过，不能重复下载！", 1, $aLinks);
			exit;
		}
		
		if (!is_numeric($_POST['report']) || intval($_POST['report']) <= 0){
			sysMessage("您提交的模板信息有误，请核对后重新提交！", 1, $aLinks);
			exit;
		}
		
		// 提取报表格式内容
		$oWdFormatList = new model_withdraw_WithdrawFormatList();
		$oWdFormatList->PPId	= $_POST['report'];
		$oWdFormatList->Status	= 1; // 只提取可用的模板列
		$oWdFormatList->OrderBy	= " seq ASC"; // 按正序排序
		$oWdFormatList->init();
		
		// 获取模板文件名
		$oWdReport = new model_withdraw_WithdrawReport($_POST['report']);
		
		if (empty($oWdFormatList->Data)){
			sysMessage("无可用下载报表格式！", 1, $aLinks);
			exit;
		}
		
        $sFileName = md5($oFODetails->IdList);
		
        // 生成文件，打包，下载
        $oDownload = new model_withdraw_download( $sFileName . '.csv', $oWdFormatList->Data, $_POST['applys'], $oWdReport->ReportName);
        EXIT;
	}
	
	
	/**
	 * 数据包列表(提现申请审核通过的压缩包)
	 * 
	 * @version 	v1.0 	2010-03-24
	 * @author 		louis
	 */
	public function actionPacksList(){
		define("DEFAULT_PAGESIZE", 50);
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$oPackList = new model_withdraw_PackList();
		$oPackList->Pages = $p;
		$oPackList->init();
		$oPager = new pages( $oPackList->TotalCount, DEFAULT_PAGESIZE, 0);    // 分页用3
		
		$GLOBALS['oView']->assign("ur_here",   "数据包列表");
    	$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
    	$GLOBALS['oView']->assign("packlist",  $oPackList->Data);
    	$oPackList->assignSysInfo();
    	$GLOBALS['oView']->display("report_packslist.html");
    	EXIT;
	}
	
	
	/**
	 * 获取指定的数据包
	 *
	 * @version		v1.0	2010-05-04
	 * @author 		louis
	 * 
	 */
	public function actionGetPack(){
		$sPackDir = dirname(__FILE__) . '/../../_data/download/';
		if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
			sysMessage("您提交的数据包信息有误，请核对后重新提交！", 1);
		}
		$oPack = new model_withdraw_Pack($_GET['id']);
		// 检查要下载的数据包是否存在
		if (!file_exists($sPackDir . $oPack->FileName)){
			sysMessage("数据包不存在，请联系技术人员！", 1);
		}
		header("Content-Type: application/x-gzip");//根据下载文件类型可能有变化
		header("Content-Disposition: attachment; filename=" . $oPack->UseName);//文件名可改
		readfile($sPackDir . $oPack->FileName);
		exit;
	}
	
	
	
	/**
	 * 将提现申请单中，通过审核的单子更改为“提现成功”或“提现失败”时的划款操作
	 *
	 * @version 	v1.0 	2010-03-25
	 * @author 		louis
	 * 
	 * @return 		boolean
	 */
	public function actionMoveMoney(){
		if ($_POST['flag'] != 'movemoney' && empty($_POST['applys']))  sysMessage("数据错误", 1);

	    $oWithdrawOperate = new model_withdraw_withdrawoperate($_POST['applys'], $_POST['operate'], $_POST['finalreason']);
	    $aLinks = array(
			0 => array(
				'text' => '返回提现申请列表',
				'href' => '?controller=report&action=withdrawapplylist'
			)
		);
	
	    if ($oWithdrawOperate->Error === true)
			sysMessage("操作成功", 0, $aLinks);
		else if($oWithdrawOperate->Error == -1)
			sysMessage('系统结算时间,暂停操作', 0, $aLinks);
		else
			sysMessage("操作失败", 1, $aLinks);
	}

	
	
	/**
	 * 提现审核未通过原因列表
	 * 
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function actionUnverifyReason(){
		$oWdUnverifyReasonList = new model_withdraw_WDUnverifyReasonList();
		$oWdUnverifyReasonList->init();
		$GLOBALS['oView']->assign("ur_here",   "审核未通过原因列表");
		$GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("report","addunverifyreason"), 'text'=>'增加未通过原因' ) );
		$GLOBALS['oView']->assign("reasonlist",   $oWdUnverifyReasonList->Data);
		$oWdUnverifyReasonList->assignSysInfo();
    	$GLOBALS['oView']->display("report_unverifyreason.html");
    	EXIT;
	}
	
	
	/**
	 * 增加审核未通过原因
	 * 
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function actionAddUnverifyReason(){
		$oWdUnverifyReason = new model_withdraw_WDUnverifyReason();
		if ($_POST['flag'] == "add"){
			$aLinks = array(
				0 => array(
					'text' => "返回审核未通过原因列表",
					'href' => "?controller=report&action=unverifyreason"
				)
			);
			if (empty($_POST['reason']))
				sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
			// 执行添加操作
			$sReason = strip_tags(trim($_POST['reason']));
			$letters = array("\n", "\r", "\r\n");
			$fruit   = array("");
			$sReason = str_replace($letters, $fruit, $sReason);
			$oWdUnverifyReason->Reason		= $sReason;
			$oWdUnverifyReason->AdminId		= $_SESSION['admin'];
			$oWdUnverifyReason->AdminName	= $_SESSION['adminname'];
			$oWdUnverifyReason->AddTime		= date("Y-m-d H:i:s", time());
			$oWdUnverifyReason->Status		= $_POST['status'] != -1 ? $_POST['status'] : 1;
			if ($oWdUnverifyReason->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败", 1, $aLinks);
		}
		
		
		$GLOBALS['oView']->assign("ur_here",   "增加未通过原因");
		$oWdUnverifyReason->assignSysInfo();
    	$GLOBALS['oView']->display("report_addunverifyreason.html");
    	EXIT;
	}
	
	
	/**
	 * 编辑提现审核未通过原因，删除，启用，禁用
	 * 
	 * @version 	v1.0	2010-04-10
	 * @author 		louis
	 */
	public function actionEditUnverifyReason(){
		$aLinks = array(
			0 => array(
				'text' => "返回审核未通过原因列表",
				'href' => "?controller=report&action=unverifyreason"
			)
		);
		$iId = $_GET['id'] > 0 ? $_GET['id'] : $_POST['id'];
		if (!is_numeric($iId) || intval($iId) <= 0){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
		
		$oWdUnverifyReason = new model_withdraw_WDUnverifyReason($iId);
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
		// 编辑审核未通过原因
		if ($_POST['flag'] == "edit"){
			!empty($_POST['reason']) or sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
			$sReason = strip_tags(trim($_POST['reason']));
			$letters = array("\n", "\r", "\r\n");
			$fruit   = array("");
			$sReason = str_replace($letters, $fruit, $sReason);
			$oWdUnverifyReason->Reason		= $sReason;
			$oWdUnverifyReason->AdminId		= $_SESSION['admin'];
			$oWdUnverifyReason->AdminName	= $_SESSION['adminname'];
			$oWdUnverifyReason->Status		= $_POST['status'] != -1 ? $_POST['status'] : 1;
			if ($oWdUnverifyReason->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		$_GET['flag'] = isset($_GET['flag']) ? $_GET['flag'] : '';
		// 删除
		if ($_GET['flag'] == 'del'){
			$oWdUnverifyReason->Id = $iId;
			if ($oWdUnverifyReason->erase())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		// 启用，禁用
		if ($_GET['flag'] == "set"){
			$oWdUnverifyReason->Status	= intval(1 - $oWdUnverifyReason->Status);
			if ($oWdUnverifyReason->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		$GLOBALS['oView']->assign("ur_here",   "编辑未通过原因");
		$GLOBALS['oView']->assign("reason",   $oWdUnverifyReason->Reason);
		$GLOBALS['oView']->assign("status",   $oWdUnverifyReason->Status);
		$oWdUnverifyReason->assignSysInfo();
    	$GLOBALS['oView']->display("report_editunverifyreason.html");
    	EXIT;
	}
	
	
	/**
	 * 审核修改密码列表
	 *
	 * @version 	v1.0	2010-06-09
	 * @author 		louis
	 */
	public function actionChangePassList(){
		$oSecondVerify = new model_secondverify();
		// 页面搜索信息
		$aHtml = array();
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$aHtml['status'] 		= $oSecondVerify->Status 		= isset($_GET['operate']) ? intval($_GET['operate']) : 0;
		$aHtml['verify_name'] 	= $oSecondVerify->VerifyName 	= isset($_GET['operate']) ? $_GET['verify_name'] : "";
		$oSecondVerify->Id		= 1; // 修改密码
		$aResult = $oSecondVerify->getVerifyList();
		
		$oPager = new pages( count($aResult), 40, 0);    // 分页用3
		// 重新组合数据
		if (!empty($aResult)){
			$oUser = new model_user();
			foreach ($aResult as $k=>$v){
				// 获取总代信息
				$aTopProxy = $oUser->getTopProxyId($v['user_id'], true);
				$aResult[$k]['topproxy'] = $aTopProxy['username'];
				// 获取用户资金
				if ($aHtml['status'] == 0){ // 只有等待审核的记录才会去查询用户的余额
					// 其它频道
					$oChannel = A::singleton("model_userchannel");
			        $aChannel = $oChannel->getUserChannelList( $v['user_id'] );
			        if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) )
			        {//如果有其他频道
			        	$fTotal = 0.00; // 用户余额总和
			        	$iFlag = 0; // 用户标志位
			            foreach( $aChannel[0] as $av )
			            {//依次获取频道余额
			            	if ($iFlag === 1) continue;
			                $oChannelApi = new channelapi( $av['id'], 'getUserCash', FALSE );
			                $oChannelApi->setTimeOut(10);            // 设置读取超时时间
			                $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
			                $oChannelApi->sendRequest( array("iUserId" => $v['user_id']) );    // 发送结果集
			                $aAmount = $oChannelApi->getDatas();
			                if( empty($aAmount) || !is_array($aAmount) || $aAmount['status'] == 'error' )
			                {//调用API获取结果失败，可能资金帐户不存在
			                   $iFlag = 1;
			                   continue;
			                }
			                $fTotal += $aAmount['data'];
			            }
			            // 银行大厅
				        $oUserFund = new model_userfund();
				        $aBankAmount = $oUserFund->getFundByUser($v['user_id']);
			            $aResult[$k]['fTotal'] = $iFlag === 1 ? "获取资金余额失败" : $fTotal + $aBankAmount['availablebalance'];
			            $aResult[$k]['flag'] = $iFlag;
			        }
				}
				
				$aTemp = unserialize($v['data']);
				if (empty($aTemp['loginpwd']) && !empty($aTemp['securitypwd'])){
					$aResult[$k]['change_type'] = "资金密码重设";
				} else if (!empty($aTemp['loginpwd']) && empty($aTemp['securitypwd'])){
					$aResult[$k]['change_type'] = "登录密码重设";
				} else {
					$aResult[$k]['change_type'] = "资金密码与登录密码重设";
				}
//				$aResult[$k]['change_type'] = empty($aTemp['loginpwd']) ? "资金密码重设" : empty($aTemp['securitypwd']) ? "登录密码重设" : "资金密码与登录密码重设";
			}
		}
		$GLOBALS['oView']->assign("ur_here",   "审核（密码修改）");
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","changepasslist"), 'text'=>'清空查询条件' ) );
		$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
		$GLOBALS['oView']->assign("aResult",   $aResult);
		$GLOBALS['oView']->assign("aHtml",   $aHtml);
		$oSecondVerify->assignSysInfo();
    	$GLOBALS['oView']->display("report_changepasslist.html");
    	EXIT;
	}
	
	/**
	 * 审核密码修改请求，如果通过则修改密码，如果未通过则将记录置为未通过后，用户数据不做任何修改
	 *
	 */
	public function actionVerifyPass(){
		$aLinks = array(
			0 => array(
				'text' => "返回修改密码列表",
				'href' => "?controller=report&action=changepasslist"
			)
		);
		$oSecondVerify = new model_secondverify();
		// 数据检查
		$_POST['pwd'] = isset($_POST['pwd']) ? $_POST['pwd'] : "";
		$_POST['verify'] = isset($_POST['verify']) ? $_POST['verify'] : "";
		$_GET['id'] = isset($_GET['id']) ? $_GET['id'] : "";
		$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : "";
		
		// 批量审核
		if (!empty($_POST['pwd'])){
			if (!is_numeric($_POST['verify']) || $_POST['verify'] < 1 || $_POST['verify'] > 2){
				sysMessage("请先选择审核状态！", 1, $aLinks);	
			}
			$sFailed = "";
			foreach ($_POST['pwd'] as $k => $v){
				$aUserInfo = explode("#", $v);
				// 检查记录是否存在
				$oSecondVerify->Id = $aUserInfo[0];
				$mResult = $oSecondVerify->getOne();
				if ($mResult === false || empty($mResult)){
					$sFailed .= $aUserInfo[1] . ','; // 记录下记录不存在的用户名
					continue;
				}
				$oSecondVerify->Status = $_POST['verify'];
				// 提交
				$iResult = $oSecondVerify->verifyPass();
				if ($iResult <= 0){
					$sFailed .= $aUserInfo[1] . ','; // 记录下操作失败的用户名
					continue;
				}
			}
			if (!empty($sFailed)){ // 有失败的记录
				$sFailed = substr($sFailed, 0, -1);
				sysMessage("用户 {$sFailed} 审核操作失败！", 1, $aLinks);	
			} else {
				sysMessage("操作成功！", 0, $aLinks);	
			}
		}
		
		// 单个审核
		if (!empty($_GET['id']) && !empty($_GET['status'])){
			if (!is_numeric($_GET['status']) || $_GET['status'] < 1 || $_GET['status'] > 2){
				sysMessage("审核状态有误！", 1, $aLinks);	
			}
			if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
				sysMessage("数据不存在！", 1, $aLinks);	
			}
			$oSecondVerify->Id = $_GET['id'];
			$oSecondVerify->Status = $_GET['status'];
			// 提交
			$iResult = $oSecondVerify->verifyPass();
			if ($iResult <= 0){
				sysMessage("操作失败！", 1, $aLinks);	
			} else {
				sysMessage("操作成功！", 0, $aLinks);	
			}
		}
	}
	
	
	/**
	 * 审核充值列表
	 *
	 * @version 	v1.0	2010-06-10
	 * @author 		louis
	 */
	public function actionLoadList(){
		$oSecondVerify = new model_secondverify();
		// 页面搜索信息
		$aHtml = array();
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$aHtml['status'] 		= $oSecondVerify->Status 		= isset($_GET['operate']) ? intval($_GET['operate']) : 0;
		$aHtml['verify_name'] 	= $oSecondVerify->VerifyName 	= isset($_GET['operate']) ? $_GET['verify_name'] : "";
		$oSecondVerify->Id		= 2; // 人工充值
		$aResult = $oSecondVerify->getVerifyList();
		$oPager = new pages( count($aResult), 40, 0);    // 分页用3
		
		// 重新组合数据
		if (!empty($aResult)){
			$oUser = new model_user();
			$oOrders = new model_orders();
			foreach ($aResult as $k=>$v){
				// 获取总代信息
				$aTopProxy = $oUser->getTopProxyId($v['user_id'], true);
				$aResult[$k]['topproxy'] = $aTopProxy['username'];				
				$aTemp = unserialize($v['data']);
				$aResult[$k]['fmoney'] = $aTemp['fmoney'];
				switch ($aTemp['order_type']){
					case ORDER_TYPE_SJCZ :
						$aResult[$k]['type'] = "上级充值";
					break;
					case ORDER_TYPE_KJCZ :
						$aResult[$k]['type'] = "跨级充值";
					break;
					case ORDER_TYPE_LPCZ :
						$aResult[$k]['type'] = "理赔充值";
					break;
					case ORDER_TYPE_RGCZ :
						$aResult[$k]['type'] = "人工充值";
					break;
					case ORDER_TYPE_SXFFH :
						$aResult[$k]['type'] = "手续费返还";
					break;
				}
				$aResult[$k]['description'] = !empty($aTemp['description']) ? $aTemp['description'] : "";
			}
		}
//		print_rr($aResult,1,1);
		$GLOBALS['oView']->assign("ur_here",   "审核（人工充值）");
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","loadlist"), 'text'=>'清空查询条件' ) );
		$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
		$GLOBALS['oView']->assign("aResult",   $aResult);
		$GLOBALS['oView']->assign("aHtml",   $aHtml);
		$oSecondVerify->assignSysInfo();
    	$GLOBALS['oView']->display("report_loadlist.html");
    	EXIT;
	}
	
	
	/**
	 * 审核人工充值操作
	 *
	 * @version 	v1.0	2010-06-11
	 * @author 		louis
	 * 
	 */
	public function actionVerifyLoad(){
		$aLinks = array(
			0 => array(
				'text' => "返回修改密码列表",
				'href' => "?controller=report&action=loadlist"
			)
		);
		$oSecondVerify = new model_secondverify();
		// 数据检查
		$_POST['load'] = isset($_POST['load']) ? $_POST['load'] : "";
		$_POST['verify'] = isset($_POST['verify']) ? $_POST['verify'] : "";
		$_GET['id'] = isset($_GET['id']) ? $_GET['id'] : "";
		$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : "";
		
		// 批量审核
		if (!empty($_POST['load'])){
			if (!is_numeric($_POST['verify']) || $_POST['verify'] < 1 || $_POST['verify'] > 2){
				sysMessage("请先选择审核状态！", 1, $aLinks);	
			}
			$sFailed = "";
			foreach ($_POST['load'] as $k => $v){
				$aUserInfo = explode("#", $v);
				// 检查记录是否存在
				$oSecondVerify->Id = $aUserInfo[0];
				$mResult = $oSecondVerify->getOne();
				if ($mResult === false || empty($mResult)){
					$sFailed .= $aUserInfo[1] . ','; // 记录下记录不存在的用户名
					continue;
				}
				$oSecondVerify->Status = $_POST['verify'];
				// 提交
				$iResult = $oSecondVerify->verifyLoad();
				if ($iResult <= 0){
					$sFailed .= $aUserInfo[1] . ','; // 记录下操作失败的用户名
					continue;
				}
			}
			if (!empty($sFailed)){ // 有失败的记录
				$sFailed = substr($sFailed, 0, -1);
				sysMessage("用户 {$sFailed} 审核操作失败！", 1, $aLinks);
			} else {
				sysMessage("操作成功！", 0, $aLinks);	
			}
		}
		
		// 单个审核
		if (!empty($_GET['id']) && !empty($_GET['status'])){
			if (!is_numeric($_GET['status']) || $_GET['status'] < 1 || $_GET['status'] > 2){
				sysMessage("审核状态有误！", 1, $aLinks);	
			}
			if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
				sysMessage("数据不存在！", 1, $aLinks);	
			}
			$oSecondVerify->Id = $_GET['id'];
			$oSecondVerify->Status = $_GET['status'];
			// 提交
			$iResult = $oSecondVerify->verifyLoad();
			if ($iResult <= 0){
				sysMessage("操作失败！", 1, $aLinks);	
			} else {
				sysMessage("操作成功！", 0, $aLinks);	
			}
		}
	}
	
	
	
	/**
	 * 审核提现列表
	 *
	 * @version 	v1.0	2010-06-11
	 * @author 		louis
	 */
	public function actionWithdrawList(){
		$oSecondVerify = new model_secondverify();
		// 页面搜索信息
		$aHtml = array();
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$aHtml['status'] 		= $oSecondVerify->Status 		= isset($_GET['operate']) ? intval($_GET['operate']) : 0;
		$aHtml['verify_name'] 	= $oSecondVerify->VerifyName 	= isset($_GET['operate']) ? $_GET['verify_name'] : "";
		$oSecondVerify->Id		= 3; // 人工提现
		$aResult = $oSecondVerify->getVerifyList();
		$oPager = new pages( count($aResult), 40, 0);    // 分页用3
		
		// 重新组合数据
		if (!empty($aResult)){
			$oUser = new model_user();
			$oOrders = new model_orders();
			foreach ($aResult as $k=>$v){
				// 获取总代信息
				$aTopProxy = $oUser->getTopProxyId($v['user_id'], true);
				$aResult[$k]['topproxy'] = $aTopProxy['username'];				
				$aTemp = unserialize($v['data']);
				$aResult[$k]['fmoney'] = $aTemp['fmoney'];
				switch ($aTemp['order_type']){
					case ORDER_TYPE_BRTX :
						$aResult[$k]['type'] = "本人提现";
					break;
					case ORDER_TYPE_KJTX :
						$aResult[$k]['type'] = "跨级提现";
					break;
					case ORDER_TYPE_GLYKJ :
						$aResult[$k]['type'] = "管理员扣减";
					break;
				}
				$aResult[$k]['description'] = !empty($aTemp['description']) ? $aTemp['description'] : "";
			}
		}
//		print_rr($aResult,1,1);
		$GLOBALS['oView']->assign("ur_here",   "审核（人工提现）");
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("report","withdrawlist"), 'text'=>'清空查询条件' ) );
		$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
		$GLOBALS['oView']->assign("aResult",   $aResult);
		$GLOBALS['oView']->assign("aHtml",   $aHtml);
		$oSecondVerify->assignSysInfo();
    	$GLOBALS['oView']->display("report_withdrawlist.html");
    	EXIT;
	}
	
	
	
	/**
	 * 审核人工提现操作
	 *
	 * @version 	v1.0	2010-06-11
	 * @author 		louis
	 * 
	 */
	public function actionVerifyWithdraw(){
		$aLinks = array(
			0 => array(
				'text' => "返回修改密码列表",
				'href' => "?controller=report&action=withdrawlist"
			)
		);
		$oSecondVerify = new model_secondverify();
		// 数据检查
		$_POST['withdraw'] = isset($_POST['withdraw']) ? $_POST['withdraw'] : "";
		$_POST['verify'] = isset($_POST['verify']) ? $_POST['verify'] : "";
		$_GET['id'] = isset($_GET['id']) ? $_GET['id'] : "";
		$_GET['status'] = isset($_GET['status']) ? $_GET['status'] : "";
		
		// 批量审核
		if (!empty($_POST['withdraw'])){
			if (!is_numeric($_POST['verify']) || $_POST['verify'] < 1 || $_POST['verify'] > 2){
				sysMessage("请先选择审核状态！", 1, $aLinks);	
			}
			$sFailed = "";
			foreach ($_POST['withdraw'] as $k => $v){
				$aUserInfo = explode("#", $v);
				// 检查记录是否存在
				$oSecondVerify->Id = $aUserInfo[0];
				$mResult = $oSecondVerify->getOne();
				if ($mResult === false || empty($mResult)){
					$sFailed .= $aUserInfo[1] . ','; // 记录下记录不存在的用户名
					continue;
				}
				$oSecondVerify->Status = $_POST['verify'];
				// 提交
				$iResult = $oSecondVerify->verifyWithdraw();
				if ($iResult <= 0){
					$sFailed .= $aUserInfo[1] . ','; // 记录下操作失败的用户名
					continue;
				}
			}
			if (!empty($sFailed)){ // 有失败的记录
				$sFailed = substr($sFailed, 0, -1);
				sysMessage("用户 {$sFailed} 审核操作失败！", 1, $aLinks);
			} else {
				sysMessage("操作成功！", 0, $aLinks);	
			}
		}
		
		// 单个审核
		if (!empty($_GET['id']) && !empty($_GET['status'])){
			if (!is_numeric($_GET['status']) || $_GET['status'] < 1 || $_GET['status'] > 2){
				sysMessage("审核状态有误！", 1, $aLinks);	
			}
			if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
				sysMessage("数据不存在！", 1, $aLinks);	
			}
			$oSecondVerify->Id = $_GET['id'];
			$oSecondVerify->Status = $_GET['status'];
			// 提交
			$iResult = $oSecondVerify->verifyWithdraw();
			if ($iResult <= 0){
				sysMessage("操作失败！", 1, $aLinks);	
			} else {
				sysMessage("操作成功！", 0, $aLinks);	
			}
		}
	}
	
	
	
	
	
	/**
	 * 查看提现详情，成功或失败的记录
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-10-18
	 * @package 	passportadmin
	 * 
	 */
	public function actionViewWithdraw(){
		$aLocation = array(0=>array("text" => "提现申请列表","href" => url("report","withdrawel")));
		// 数据检查
		$iId = isset($_GET['id']) ? $_GET['id'] : 0;
		if (!is_numeric($iId) || $iId <= 0 ){
			sysMessage("提现申请ID错误", 1, $aLocation);
}
		
		// 数据是否存在
		$oWithDrawel = A::singleton('model_withdrawel');
		$aWithDrawel = $oWithDrawel->getWithDrawelById( $iId );
	    if( empty($aWithDrawel) )
		{
		    sysMessage("提现申请ID不存在", 1, $aLocation);
		    exit;
		}
		
		$GLOBALS['oView']->assign( "s", $aWithDrawel );
		$GLOBALS['oView']->assign( "ur_here", "查看详情" );
		$GLOBALS['oView']->display( "report_viewwithdraw.html" );
		EXIT;
	}
}
?>
