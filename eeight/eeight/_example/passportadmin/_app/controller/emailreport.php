<?php
/**
 * 文件：/passportadmin/_app/controller/emailreport.php
 * 功能：E-mail充值相关报表
 *
 * 功能：
 * --actionEmailDepositList					工行充值申请列表
 * --actionUnNormalList						email充值异常检测列表
 * --actionDownloadReport					下载充值报表
 * --actionErrorDeal						充值异常处理
 * --actionView								查看充值详情
 * --actionAdminInsert						工行人工录入充值信息
 * --chooseLoadMethod						选择充值申请方式
 * --actionChooseUnNormalList				充值异常检测银行选择列表
 * 
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-09-06
 * @package 	passportadmin
 * 
 */
define("ICBC_LOGO", "mdeposit"); 	// 建行logo图片名称
define("CCB_LOGO", "ccb");          // 工行logo图片名称
define("BANK_KEY_ERROR", 1);        // 附言违规,银行抓取的信息中的附言违规
class controller_emailreport extends basecontroller{
	
	
	/**
	 * email充值申请列表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-06
	 * @package 	passportadmin
	 * 
	 */
	public function actionEmailDepositList(){
		$iPageSize = 60;  // 每页显示条数
		$oEmailDeposit = new model_emaildeposit();
        $oIcbcDeposit = new model_deposit_emaildeposit();
		
		// 页面搜索信息
		$aHtml = array();
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$pn = isset($_GET['pn']) ? intval($_GET['pn']) : 0;
		$iPageSize = $aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25;  
		$aHtml['status'] 										= isset($_GET['status']) ? intval($_GET['status']) : "";
		if ($aHtml['status'] === -1){
			$oEmailDeposit->Status = 0;
		} elseif ($aHtml['status'] != "") {
			$oEmailDeposit->Status = $aHtml['status'];
		}
		
		$aHtml['username'] 		= $oEmailDeposit->UserName 		= isset($_GET['username']) ? trim($_GET['username']) : '';
		$aHtml['rid'] 			= $oEmailDeposit->Id			= isset($_GET['rid']) ? intval($_GET['rid']) > 0 ? intval($_GET['rid']) : '' : '';
		$aHtml['key'] 			= isset($_GET['key']) ? trim($_GET['key']) : '';
        $oIcbcDeposit->Note     = trim($_GET['key']);
        $oEmailDeposit->Key		= $oIcbcDeposit->getKey();
		$aHtml['starttime'] 	= $oEmailDeposit->StartTime		= isset($_GET['starttime']) ? trim($_GET['starttime']) : '';
		$aHtml['endtime'] 		= $oEmailDeposit->EndTime		= isset($_GET['endtime']) ? trim($_GET['endtime']) : '';
		$aHtml['startexpire'] 	= $oEmailDeposit->StartExpire	= isset($_GET['startexpire']) ? trim($_GET['startexpire']) : '';
		$aHtml['endexpire'] 	= $oEmailDeposit->EndExpire		= isset($_GET['endexpire']) ? trim($_GET['endexpire']) : '';
								  $oEmailDeposit->Pages			= intval($p);
		$oEmailDeposit->PageSize = $iPageSize; // 每页显示条数
		$aResult = $oEmailDeposit->getList();
		if (!empty($aResult)){
			// 计算当前面的数据统计
			$fTotal = 0;
			$fBankTotal = 0;
			$fFee = 0;
			$oPager = new pages( $oEmailDeposit->TotalCount, $iPageSize, 0);    // 分页用3
			
			// 遍历记录集，获取所属总代，银行卡财务别名等操作
			$oUser = new model_user();
			$oPayList = new model_deposit_depositaccountlist(array(),'','array');
			// 查询银行下分账户列表，卡号做键名
			$aAccInfo = array();
			$aAccInfo = $oPayList->singleList("mdeposit",false,'ads_payport_name','acc_bankacc');
			foreach ($aResult as $key => $val) {
				$fTotal += $val['money'];
				$fBankTotal += $val['amount'];
				$fFee += $val['fee'];

				$aResult[$key]['nickname1'] = $aAccInfo[$val['account']]['acc_name'];
				$aResult[$key]['nickname2'] = $aAccInfo[$val['accept_card_num']]['acc_name'];
				}
			}
		
		$GLOBALS['oView']->assign( 'ur_here', '工行充值申请' );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("emailreport","emaildepositlist"), 'text'=>'清空查询条件' ) );
		if (!empty($aResult)){
			$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
		}
		
		// 计算耗时 added:1/6/2011
		function getSpendTime( $sec ){
			$d = floor($sec / 86400);
			$tmp = $sec % 86400;
			$h = floor($sec / 3600);
			$tmp %= 3600;
			$m = floor($tmp /60);
			$s = $tmp % 60;
			return $h. ":".$m. ":".$s; 
		} 
		
		foreach ( $aResult AS &$aSinRecord)
		{
			if ( $aSinRecord['add_money_time'] > 0 )
			{
				$iSec = strtotime($aSinRecord['add_money_time']) - strtotime($aSinRecord['pay_date']);
				//if ($iSec > 0) $aSinRecord['spendtime'] = date("H:i:s", mktime(0,0,$iSec,0,0,0) );
				if ($iSec > 0) $aSinRecord['spendtime']  = getSpendTime($iSec);
			}
			
		}
		// end:1/6/2011
		
		$GLOBALS['oView']->assign( 'loadinfo', $aResult ); 
		$GLOBALS['oView']->assign( 'ftotal', $fTotal ); 
		$GLOBALS['oView']->assign( 'fbanktotal', $fBankTotal ); 
		$GLOBALS['oView']->assign( 'fee', $fFee ); 
		$GLOBALS['oView']->assign( 'aHtml', $aHtml ); 
	    $GLOBALS['oView']->assign( 'sSysAutoReflushSec', 60);
		$GLOBALS['oView']->assign( 'sSysMetaMessage', 
	    				'<META http-equiv="REFRESH" CONTENT="60" />');
        $oEmailDeposit->assignSysInfo();
        $GLOBALS['oView']->display("emaildeposit_list.html");
        EXIT;
	}
	
	
	
	
	/**
	 * email充值异常检测列表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-07
	 * @package 	passportadmin
	 * 
	 */
	public function actionUnNormalList(){
		$iPageSize = 60;  // 每页显示条数
		$oEmailDeposit = new model_emaildeposit();
		
		// 页面搜索信息
		$aHtml = array();
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$pn = isset($_GET['pn']) ? intval($_GET['pn']) : 0;
		$iPageSize = $aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25;  
		
		$aHtml['status'] 		= $oEmailDeposit->Status		= isset($_GET['status']) ? intval($_GET['status']) : 1;
		$aHtml['error_type'] 	= $oEmailDeposit->ErrorType 	= isset($_GET['error_type']) ? trim($_GET['error_type']) : '';
		$aHtml['adminname'] 	= $oEmailDeposit->AdminName		= isset($_GET['adminname']) ? trim($_GET['adminname']) : '';
		$aHtml['username'] 		= $oEmailDeposit->UserName 		= isset($_GET['username']) ? trim($_GET['username']) : '';
		$aHtml['topproxy'] 		= $oEmailDeposit->TopProxy 		= isset($_GET['topproxy']) ? trim($_GET['topproxy']) : '';
		$aHtml['starttime'] 	= $oEmailDeposit->StartTime		= isset($_GET['starttime']) ? trim($_GET['starttime']) : '';
		$aHtml['endtime'] 		= $oEmailDeposit->EndTime		= isset($_GET['endtime']) ? trim($_GET['endtime']) : '';
		$aHtml['startdeal'] 	= $oEmailDeposit->StartExpire	= isset($_GET['startdeal']) ? trim($_GET['startdeal']) : '';
		$aHtml['enddeal'] 		= $oEmailDeposit->EndExpire		= isset($_GET['enddeal']) ? trim($_GET['enddeal']) : '';
								  $oEmailDeposit->Pages			= intval($p);
								  
		$oEmailDeposit->PageSize = $iPageSize; // 每页显示条数
		$aResult = $oEmailDeposit->getErrorList();
		
		if (!empty($aResult)){
			// 计算当前面的数据统计
			$fPlateTotal = 0;
			$fBankTotal = 0;
			$fFee = 0;
			$oPager = new pages( $oEmailDeposit->TotalCount, $iPageSize, 0);    // 分页用3
			
			// 遍历记录集，获取所属总代，银行卡财务别名等操作
			$oUser = new model_user();
			$oPayList = new model_deposit_depositaccountlist(array(),'','array');
			// 查询银行下分账户列表，卡号做键名
			$aAccInfo = array();
			$aAccInfo = $oPayList->singleList("mdeposit",false,'ads_payport_name','acc_bankacc');
			foreach ($aResult as $key => $val) {
				$fPlateTotal += floatval($val['request_amount']);
				$fBankTotal += floatval($val['pay_amount']);
				$fFee += floatval($val['pay_fee']);

				$aResult[$key]['nickname1'] = $aAccInfo[$val['request_card']]['acc_name'];
				$aResult[$key]['nickname2'] = $aAccInfo[$val['pay_card']]['acc_name'];
                
                // 如果是附言违规，则只显示附言的最后两位，其它用*代替
                if (intval($aResult[$key]['error_type']) === BANK_KEY_ERROR && !empty($aResult[$key]['pay_key'])){
                    $aResult[$key]['pay_key'] = $oEmailDeposit->hiddenKey($aResult[$key]['pay_key']);
                }
			}
		}
		
		$GLOBALS['oView']->assign( 'ur_here', '异常充值检测' );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("emailreport","unnormallist"), 'text'=>'清空查询条件' ) );
		if (!empty($aResult)){
			$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
		}
		$GLOBALS['oView']->assign( 'loadinfo', $aResult ); 
		$GLOBALS['oView']->assign( 'fplatetotal', $fPlateTotal ); 
		$GLOBALS['oView']->assign( 'fbanktotal', $fBankTotal ); 
		$GLOBALS['oView']->assign( 'fee', $fFee ); 
		$GLOBALS['oView']->assign( 'aHtml', $aHtml ); 
        $oEmailDeposit->assignSysInfo();
        $GLOBALS['oView']->display("email_unnormallist.html");
        EXIT;
	}
	
	
	
	
	/**
	 * 下载充值报表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-08
	 * @package 	passportadmin
	 * 
	 */
	public function actionDownloadReport(){
		$aLinks = array(
			0 => array(
				'text' => '关闭本页面',
				'href' => 'javascript:window.close()'
			)
		);
		$oEmailDeposit = new model_emaildeposit();
		// 数据检查
		$iStatus = -1;
		$status = isset($_GET['status']) ? intval($_GET['status']) : "";
		if ($status === -1){
			$iStatus = $oEmailDeposit->Status = 0;
		} else {
			$iStatus = $oEmailDeposit->Status = $status;
		}
		$sUsername		= $oEmailDeposit->UserName 		= isset($_GET['username']) ? trim($_GET['username']) : '';
		$iRid			= $oEmailDeposit->Id 			= isset($_GET['rid']) ? intval($_GET['rid']) > 0 ? intval($_GET['rid']) : '' : '';
		$sKey 			= $oEmailDeposit->Key 			= isset($_GET['key']) ? trim($_GET['key']) : '';
		$starttime 		= $oEmailDeposit->StartTime 	= isset($_GET['starttime']) ? trim($_GET['starttime']) : '';
		$endtime 		= $oEmailDeposit->EndTime 		= isset($_GET['endtime']) ? trim($_GET['endtime']) : '';
		$startexpire 	= $oEmailDeposit->StartExpire 	= isset($_GET['startexpire']) ? trim($_GET['startexpire']) : '';
		$endexpire 		= $oEmailDeposit->EndExpire 	= isset($_GET['endexpire']) ? trim($_GET['endexpire']) : '';
		if (!is_numeric($iStatus) && empty($sUsername) && empty($iRid) && empty($sKey) && empty($starttime) && empty($endtime) &&
			empty($startexpire) && empty($endexpire)){
				sysMessage("请选择下载条件", 1, $aLinks);
			}
		$aResult = $oEmailDeposit->getList();
		
		if (empty($aResult)){
			sysMessage("没有符合查询条件的数据", 1, $aLinks);
		}
		
		// 计算当前面的数据统计
		$fTotal = 0;
		$fBankTotal = 0;
		
		// 遍历记录集，获取所属总代，银行卡财务别名等操作
		$oUser = new model_user();
		$aRecordId = array();
		$aRecord = array();
		$aTitle = array();
		$aSign = array();
		$oPayList = new model_pay_payaccountlist(array(),'','array');
		// 查询银行下分账户列表，卡号做键名
		$aAccInfo = array();
		$aAccInfo = $oPayList->singleList("mdeposit",false,'acc_siteid','acc_siteid');
		foreach ($aResult as $key => $val) {
			$fTotal += $v['money'];
			$fBankTotal += $v['amount'];

			$aResult[$key]['nickname1'] = $aAccInfo[$val['account']]['acc_name'];
			$aResult[$key]['nickname2'] = $aAccInfo[$val['accept_card_num']]['acc_name'];
			
			$aRecordId[] = $val['id'];
			
			// 组合结果数组
			$aRecord[$key]['ID'] = $val['id'];
			if ($val['status'] == 0){
				$aRecord[$key]['status'] = "支付中";
			} elseif ($val['status'] == 1){
				$aRecord[$key]['status'] = "支付成功";
			} elseif ($val['status'] == 2){
				$aRecord[$key]['status'] = "挂起";
			} elseif ($val['status'] == 3){
				$aRecord[$key]['status'] = "用户取消";
			} elseif ($val['status'] == 4){
				$aRecord[$key]['status'] = "管理员处理";
			} elseif ($val['status'] == 5){
				$aRecord[$key]['status'] = "没收";
			}
			
			$aRecord[$key]['username'] = $val['user_name'];
			$aRecord[$key]['topproxy'] = $val['topproxy_name'];
			$aRecord[$key]['created'] = $val['created'];
			$aRecord[$key]['account'] = $aResult[$key]['nickname1'];
			$aRecord[$key]['money'] = $val['money'];
			$aRecord[$key]['key'] = $val['key'];
			$aRecord[$key]['pay_date'] = $val['pay_date'];
			$aRecord[$key]['accept_card_num'] = $aResult[$key]['nickname2'];
			$aRecord[$key]['amount'] = $val['amount'];
			$aRecord[$key]['notes'] = $val['notes'];
		}
		
		// 组合标题数组，一定要与结果数组对位
		$aSign[0] 		= "";
		$aTitle[0] 		= "ID";
		$aSign[1] 		= "";
		$aTitle[1] 		= "状态";
		$aSign[2] 		= "";
		$aTitle[2] 		= "用户";
		$aSign[3] 		= "";
		$aTitle[3] 		= "所属总代";
		$aSign[4] 		= "平台";
		$aTitle[4] 		= "时间";
		$aSign[5] 		= "充值";
		$aTitle[5] 		= "收款卡";
		$aSign[6] 		= "信";
		$aTitle[6] 		= "金额";
		$aSign[7] 		= "息";
		$aTitle[7] 		= "附言";
		$aSign[8] 		= "银行";
		$aTitle[8] 		= "时间";
		$aSign[9] 		= "查询";
		$aTitle[9] 		= "收款卡";
		$aSign[10] 		= "信";
		$aTitle[10] 	= "金额";
		$aSign[11] 		= "息";
		$aTitle[11] 	= "附言";
		
		
		$iIdList = "";
		$iIdList = implode(',', $aRecordId);
		
		$sFileName = time() . '-' . $_SESSION['adminname'];
		
		// 生成文件，打包，下载
        $oDownload = new model_withdraw_download("emaildownload", $sFileName . '.csv', $aRecord, $aRecordId, "E-mail充值申请表", "zip", $aTitle, false, $aSign);
        EXIT;
	}
	
	
	
	
	/**
	 * 充值异常处理
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-09
	 * @package 	passportadmin
	 * 
	 */
	public function actionErrorDeal(){
		$aLinks = array(
			0 => array(
				'text' => '返回异常充值检测',
				'href' => '?controller=emailreport&action=unnormallist'
			)
		);
		// 数据检查
		if (empty($_POST['loadid']) || empty($_POST['remark']) || (intval($_POST['deal']) !== 4 && intval($_POST['deal']) !== 5 )){
			sysMessage("您提交的数据有误", 1, $aLinks);
		}
		
		// 检测提交的信息是否全部填写了批注信息，如果有，不打断流程，记录下来继续执行，全部执行完成后再提示管理员
		$sNoRemark = "";
		foreach ($_POST['loadid'] as $k => $v){
			if ($_POST['remark'][$v] == ""){
				$sNoRemark .= $v . ',';
			}
		}
		if (!empty($sNoRemark)){
			$sNoRemark = substr($sNoRemark, 0, -1);
			sysMessage("记录编号为 " . $sNoRemark . "的记录未填写批注", 1, $aLinks);
		}
		
		$oEmailDeposit = new model_emaildeposit();
		// 开始处理操作
		$mResult = $oEmailDeposit->errorDeal( $_POST['loadid'], $_POST['remark'], intval($_POST['deal']), $_SESSION['admin'], $_SESSION['adminname']);
		if ($mResult === -1){
			sysMessage("您提交的数据有误", 1, $aLinks);
		}
		if ($mResult === true){
			sysMessage("操作成功", 0, $aLinks);
		} else {
			$mResult = substr($mResult, 0, -1);
			sysMessage("记录编号为 " . $mResult . " 的记录操作失败", 1, $aLinks);
		}
	}
	
	
	
	
	/**
	 * 查看充值详情
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-09
	 * @package 	passportadmin
	 * 
	 */
	public function actionLoadView(){
		$aLinks = array(
			0 => array(
				'text' => '返回异常充值检测',
				'href' => '?controller=emailreport&action=unnormallist'
			)
		);
		// 数据检查
		if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
			sysMessage("您提交的数据有误", 1, $aLinks);
		}
		
		$oEmailDeposit = new model_deposit_emaildeposit();
		
		// 查询数据是否存在
		$oEmailDeposit->BankRecordId = intval($_GET['id']);
		$aResult = $oEmailDeposit->getOneBankRecord();
		if (empty($aResult)){
			sysMessage("您请求的数据不存在", 1, $aLinks);
		}
		
		$oPayPortInfo = new model_pay_payaccountinfo($aResult['accept_card_num'], 'banknumber');
		$oPayPortInfo->GetType = true;
		if ($oPayPortInfo->AId > 0){
			$oPayPortInfo->getAccountDataObj();
		}
		$aResult['nickname'] = $oPayPortInfo->AccName; // 平台充值信息中的财务使用别名		
		
		$GLOBALS['oView']->assign( 'ur_here', '抓取详情' );
		$GLOBALS['oView']->assign( 'info', $aResult );
		$GLOBALS['oView']->assign( 'id', $_GET['id'] );
        $oEmailDeposit->assignSysInfo();
        $GLOBALS['oView']->display("email_loadview.html");
        EXIT;
	}
	
	
	
	
	/**
	 * 人工录入充值信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-09
	 * @package 	passportadmin
	 * 
	 */
	public function actionAdminInsert(){
		$aLinks = array(
			0 => array(
				'text' => '返回人工录入查款记录',
				'href' => '?controller=emailreport&action=admininsert'
			)
		);
		$oEmailDeposit = new model_emaildeposit();
		
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : "";
		if ($_POST['flag'] == "manual"){
            $sAccountName = isset($_POST['accountname']) ? $_POST['accountname'] : "";
            $sCardNum = isset($_POST['card_num']) ? $_POST['card_num'] : "";
			$_POST['accept_card_num'] = isset($_POST['accept_card_num']) ? $_POST['accept_card_num'] : "";
			$_POST['pay_date'] = isset($_POST['pay_date']) ? $_POST['pay_date'] : "";
			$_POST['amount'] = isset($_POST['amount']) ? $_POST['amount'] : 0.00;
			$fFee = isset($_POST['fee']) ? floatval($_POST['fee']) : 0.00;
			// 数据检查
			if (empty($_POST['accept_card_num']) || empty($_POST['pay_date']) || floatval($_POST['amount']) <= 0 || empty($sAccountName)){
				sysMessage("您提交的数据有误", 1, $aLinks);
			}
			
			$temp = preg_match("/^[0-9]{4}(\-)[0-9]{1,2}(\\1)[0-9]{1,2}(|\s+[0-9]{1,2}(:[0-9]{1,2}){0,2})$/",$_POST['pay_date']);
			if ($temp === 0){
				sysMessage("您提交的数据有误", 1, $aLinks);
			}
			
			// 如果有手续费，就必须复合条件
			if ($fFee < 0 || $fFee >= 100){
				sysMessage("您提交的手续费有误", 1, $aLinks);
			}
			
			// 首先查询数据库中是否存在相同key,如果存在，不准输入
			/*$sKey = "";
			$aOldResult = array();
			$oPEmailDeposit = new model_deposit_emaildeposit();
			$oPEmailDeposit->Note = trim($_POST['notes']);
			$sKey = $oPEmailDeposit->getKey();
			if ($sKey != ""){
				$oPEmailDeposit->Key = $sKey;
				$aOldResult = $oPEmailDeposit->getRecord();
				if (!empty($aOldResult)){
					sysMessage("您已提交过些附言！", 1, $aLinks);
				}
			}*/
			
			
			// 写入人工查款记录
            $oEmailDeposit->Account = $sCardNum;
            $oEmailDeposit->AccountName = $sAccountName;
			$oEmailDeposit->AcceptNum = $_POST['accept_card_num'];
			$oEmailDeposit->PayDate = $_POST['pay_date'];
			$oEmailDeposit->Amount = floatval($_POST['amount']);
			$oEmailDeposit->Fee = $fFee;
			$oEmailDeposit->Notes = trim($_POST['notes']);
			$oEmailDeposit->AdminId = $_SESSION['admin'];
			$mResult = $oEmailDeposit->adminInsert();
			if ($mResult > 0){
				sysMessage("操作成功", 0, $aLinks);
			} else if ($mResult === 0) {
				sysMessage("本条汇款已处理", 1, $aLinks);
			} else {
                sysMessage("操作失败", 1, $aLinks);
            }
		}
		
		// 查询自己录入的最新的10条记录
		$aResult = $oEmailDeposit->getLastRecord($_SESSION['admin'], 'DESC');
		
		$GLOBALS['oView']->assign( 'ur_here', '工行人工录入' );
		$GLOBALS['oView']->assign( 'loadinfo', $aResult );
        $oEmailDeposit->assignSysInfo();
        $GLOBALS['oView']->display("emailreport_admininsert.html");
        EXIT;
	}
	
	
	
	/**
	 * 选择充值申请方式
	 *
	 * @author 	louis
	 * @version v1.0
	 * @since 	2010-11-18
	 * @package passportadmin
	 * 
	 */
	public function actionChooseLoadMethod(){
		$oEmailDeposit = new model_emaildeposit();
		$GLOBALS['oView']->assign( 'ur_here', '我要充值申请' );
		$GLOBALS['oView']->assign( 'icbc', ICBC_LOGO . ".jpg");
		$GLOBALS['oView']->assign( 'ccb', CCB_LOGO . ".jpg");
		$GLOBALS['oView']->assign( 'icbcaction', "./?controller=emailreport&action=admininsert");
		$GLOBALS['oView']->assign( 'ccbaction', "./?controller=ccbreport&action=admininsert");
        $oEmailDeposit->assignSysInfo();
        $GLOBALS['oView']->display("emailreport_chooseloadmethod.html");
        EXIT;
}
	
	
	
	/**
	 * 充值异常检测银行选择列表
	 *
	 * @author 	louis
	 * @version v1.0
	 * @since 	2010-11-22
	 * @package passportadmin
	 * 
	 */
	public function actionChooseUnNormalList(){
		$oEmailDeposit = new model_emaildeposit();
		$GLOBALS['oView']->assign( 'ur_here', '充值异常检测' );
		$GLOBALS['oView']->assign( 'icbc', ICBC_LOGO . ".jpg");
		$GLOBALS['oView']->assign( 'ccb', CCB_LOGO . ".jpg");
		$GLOBALS['oView']->assign( 'icbcaction', "./?controller=emailreport&action=unnormallist");
		$GLOBALS['oView']->assign( 'ccbaction', "./?controller=ccbreport&action=unnormallist");
        $oEmailDeposit->assignSysInfo();
        $GLOBALS['oView']->display("emailreport_chooseloadmethod.html");
        EXIT;
	}
}