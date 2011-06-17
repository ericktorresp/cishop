<?php
/**
 * 文件：/passportadmin/_app/controller/ccbreport.php
 * 功能：建行充值相关报表
 *
 * 功能：
 * --actionAdminInsert					建行人工录入
 * --actionCCBDepositList				建行充值申请列表
 * --actionDownloadReport				下载充值报表
 * --actionUnNormalList					建行充值异常检测列表
 * --actionErrorDeal					建行充值异常处理
 * --actionLoadView						查看充值详情
 * 
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-11-19
 * @package 	passportadmin
 * 
 */
define("ICBC_LOGO", "mdeposit"); 	// 建行logo图片名称
define("CCB_LOGO", "ccb");		// 工行logo图片名称
class controller_ccbreport extends basecontroller{
	
	
	/**
	 * 建行人工录入充值信息
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-18
	 * @package 	passportadmin
	 * 
	 */
	public function actionAdminInsert(){
		$aLinks = array(
			0 => array(
				'text' => '返回建行人工录入',
				'href' => '?controller=ccbreport&action=admininsert'
			)
		);
		$oCCBDeposit = new model_ccbdeposit();
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : "";
		if ($_POST['flag'] == "manual"){
			if (empty($_POST['transfers']) && $_FILES["transferfile"] == ""){
				sysMessage("提交的数据有误", 1, $aLinks);
				exit;
			}
			
			$_POST['transfers'] = isset($_POST['transfers']) ? $_POST['transfers'] : "";
			if ($_FILES["transferfile"]['name'] != ""){
				// 加载文件上传函数
				require A_DIR . DS . 'includes' . DS . 'plugin' . DS . 'filefunc.php';
				$sPath				= PDIR_ADMIN . DS . '_data' . DS . 'transfers'; 	// 文件上传地址
				// 检查目录是否存在
				if (!file_exists($sPath)){
					@mkdir($sPath, 0777, true);
					@chdir($sPath, 0777);
				}
				$sAllowedMime 		= "text/plain";							// 允许的文件的MIME类型
		 		$sAllowedExtension 	= "txt";								// 接受的文件类型
		 		$iAllowedMinSize 	= 200;									// 上传文件的最小字节
		 		$iAllowedMaxSize 	= 204800;								// 上传文件的最大字节
		 		$aFile = saveUploadFile( $_FILES["transferfile"], $sPath, $sAllowedMime, $sAllowedExtension, $iAllowedMinSize, $iAllowedMaxSize );
		 		$aFile["code"] === 0 or sysMessage("文件上传失败", 0, $aLinks);
				if (file_exists($aFile['name'])){
					$sSource = file_get_contents($aFile['name']);
				}
			}
			
			// 高级录入
			if (!empty($_POST['transfers']) || !empty($sSource)){
				$sContent = "";
				$sContent = !empty($_POST['transfers']) ? $_POST['transfers'] : $sSource;
				// 将换行符替换
				/*$pattern  = "/\n/";
				$_POST['transfers'] = preg_replace($pattern, "##", $_POST['transfers']);*/
				
				// 正则分析高级录入的内容
				$sContent = iconv("gbk", "utf-8", $sContent);
				$pattern = "!(.*),(.*),.*,(.*),(.*),(.*),(.*),(.*),(.*)\n!Uis";
				preg_match_all($pattern, $sContent, $aMatches, PREG_SET_ORDER);
				$aContent = array();
		    	foreach($aMatches as $aMatch){
				    array_shift($aMatch);
				    $aTmp = array_map('trim',$aMatch);
				    $aContent[] = $aTmp;
				}
				
				// 取出账号，过滤数据
				if (empty($aContent)){
					sysMessage("没有可提交的数据", 1, $aLinks);
					exit;
				}
				
				$sAccount = "";
				$aAllow = array("转帐存入");
				$aResult = array();
				foreach ($aContent as $k => $v){
					if ($k == 0){
						$pattern = "/账　　号：(.*)\n.*/i";
						preg_match_all($pattern, $v[0], $aMatch, PREG_SET_ORDER);
						$sAccount = trim($aMatch[0][1]);
					} else {
						if (in_array($v[7], $aAllow)){
							$aResult[] = $v;
						}
					}
				}
				
				// 向数据库写入数据
				if (empty($sAccount) || empty($aResult)){
					sysMessage("分析数据出错", 1, $aLinks);
					exit;
				}
				
				$iPass = 0;		// 略过的记录条数
				$iSuccess = 0;	// 成功写入的记录条数
				$iFailed = 0;	// 写入失败的记录条数
				foreach ($aResult as $k => $v){
					$oCCBDeposit->Account = $sAccount;
					$oCCBDeposit->PayDate = substr($v[0], 0, 4) . "-" . substr($v[0], 4,2) . "-" . substr($v[0], -2);
					$oCCBDeposit->Area = $v[1];
					$oCCBDeposit->Amount = $v[2];
					$oCCBDeposit->Balance = $v[3];
					$odepositInfo = new model_deposit_depositaccountinfo('ccb');
					$odepositInfo->getAccountDataObj();
					// 获取充值手续费
					$odepositInfo->OptType = "onlineload";
		        	$aCharge = $odepositInfo->paymentFee($v[2]);
		        	$oCCBDeposit->Fee = $aCharge[1];
					$oCCBDeposit->HiddenAcc = $v[4];
					$oCCBDeposit->AccountName = $v[5];
					$oCCBDeposit->Currency = $v[6];
					$oCCBDeposit->Notes = $v[7];
                    $oCCBDeposit->AcceptCard = $sAccount;
					// 生成验证串
					$oCCBDeposit->Key = $oCCBDeposit->getKey();
					// 检查记录是否已存在
					$bResult = $oCCBDeposit->isExist();
					if ($bResult === true){
						$iPass++;
						continue;
					}
					if ($oCCBDeposit->adminInsert() > 0){
						$iSuccess++;
					} else {
						$iFailed++;
					}
				}
				// 删除文件
				@unlink($aFile['name']);
				sysMessage("成功执行 " . $iSuccess . " 条记录,略过 " . $iPass . " 条记录，失败 " . $iFailed . " 条记录！", 0, $aLinks);
				exit;
			}
		}
		
		$GLOBALS['oView']->assign( 'ur_here', '建行人工录入' );
        $oCCBDeposit->assignSysInfo();
        $GLOBALS['oView']->display("ccbreport_admininsert.html");
        EXIT;
	}
	
	
	
	/**
	 * 建行充值申请列表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-19
	 * @package 	passportadmin
	 * 
	 */
	public function actionCCBDepositList(){
		$iPageSize = 60;  // 每页显示条数
		$oCCBDeposit = new model_ccbdeposit();
		
		// 页面搜索信息
		$aHtml = array();
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$pn = isset($_GET['pn']) ? intval($_GET['pn']) : 0;
		$iPageSize = $aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25;  
		$aHtml['status'] 										= isset($_GET['status']) ? intval($_GET['status']) : "";
		if ($aHtml['status'] === -1){
			$oCCBDeposit->Status = 0;
		} elseif ($aHtml['status'] != "") {
			$oCCBDeposit->Status = $aHtml['status'];
		}
		
		$aHtml['username'] 		= $oCCBDeposit->UserName 		= isset($_GET['username']) ? trim($_GET['username']) : '';
		$aHtml['rid'] 			= $oCCBDeposit->Id				= isset($_GET['rid']) ? intval($_GET['rid']) > 0 ? intval($_GET['rid']) : '' : '';
		$aHtml['starttime'] 	= $oCCBDeposit->StartTime		= isset($_GET['starttime']) ? trim($_GET['starttime']) : '';
		$aHtml['endtime'] 		= $oCCBDeposit->EndTime			= isset($_GET['endtime']) ? trim($_GET['endtime']) : '';
		$aHtml['account'] 		= $oCCBDeposit->Account			= isset($_GET['account']) ? trim($_GET['account']) : '';
		$aHtml['acc_name'] 		= $oCCBDeposit->AccountName		= isset($_GET['acc_name']) ? trim($_GET['acc_name']) : '';
								  $oEmailDeposit->Pages			= intval($p);
		$oCCBDeposit->PageSize = $iPageSize; // 每页显示条数
		$aResult = $oCCBDeposit->getList();
		if (!empty($aResult)){
			// 计算当前面的数据统计
			$fTotal = 0;
			$fBankTotal = 0;
			$oPager = new pages( $oCCBDeposit->TotalCount, $iPageSize, 0);    // 分页用3
			
			// 遍历记录集，获取所属总代，银行卡财务别名等操作
			$oPayList = new model_deposit_depositaccountlist(array(),'','array');
			$aAccList = array();
			// 查询银行下分账户列表，卡号做键名
			$aAccList = $oPayList->singleList("ccb",false,'ads_payport_name','acc_bankacc');
			foreach ($aResult as $key => $val) {
				$fTotal += $val['money']; // 平台充值总额
				$fBankTotal += $val['amount']; // 银行抓取总额
				$aResult[$key]['nickname1'] = !empty($aAccList[$val['accept_card']]['acc_name']) ? $aAccList[$val['accept_card']]['acc_name'] : $val['accept_card']; // 平台记录卡号
				$aResult[$key]['nickname2'] = !empty($aAccList[$val['bank_accept_card']]['acc_name']) ? $aAccList[$val['bank_accept_card']]['acc_name'] : $val['bank_accept_card'];	// 银行抓取卡号
			}
		}
		
		$GLOBALS['oView']->assign( 'ur_here', '建行充值申请' );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("ccbreport","ccbdepositlist"), 'text'=>'清空查询条件' ) );
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
				$iSec = strtotime($aSinRecord['add_money_time']) - strtotime($aSinRecord['created']);
				//if ($iSec > 0) $aSinRecord['spendtime'] = date("H:i:s", mktime(0,0,$iSec,0,0,0) );
				if ($iSec > 0) $aSinRecord['spendtime']  = getSpendTime($iSec);
			}
			
		}
		// end:1/6/2011
		
		$GLOBALS['oView']->assign( 'loadinfo', $aResult ); 
		$GLOBALS['oView']->assign( 'ftotal', $fTotal ); 
		$GLOBALS['oView']->assign( 'fbanktotal', $fBankTotal ); 
		$GLOBALS['oView']->assign( 'aHtml', $aHtml ); 
	    $GLOBALS['oView']->assign( 'sSysAutoReflushSec', 60);
		$GLOBALS['oView']->assign( 'sSysMetaMessage', 
	    				'<META http-equiv="REFRESH" CONTENT="60" />');
        $oCCBDeposit->assignSysInfo();
        $GLOBALS['oView']->display("ccbdeposit_list.html");
        EXIT;
	}
	
	
	
	/**
	 * 下载充值报表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-21
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
		$oCCBDeposit = new model_ccbdeposit();
		// 数据检查
		$iStatus = -1;
		$status = isset($_GET['status']) ? intval($_GET['status']) : "";
		if ($status === -1){
			$iStatus = $oCCBDeposit->Status = 0;
		} else {
			$iStatus = $oCCBDeposit->Status = $status;
		}
		$sUsername		= $oCCBDeposit->UserName 		= isset($_GET['username']) ? trim($_GET['username']) : '';
		$iRid			= $oCCBDeposit->Id 			= isset($_GET['rid']) ? intval($_GET['rid']) > 0 ? intval($_GET['rid']) : '' : '';
		$sKey 			= $oCCBDeposit->Key 			= isset($_GET['key']) ? trim($_GET['key']) : '';
		$starttime 		= $oCCBDeposit->StartTime 	= isset($_GET['starttime']) ? trim($_GET['starttime']) : '';
		$endtime 		= $oCCBDeposit->EndTime 		= isset($_GET['endtime']) ? trim($_GET['endtime']) : '';
		$Account 		= $oCCBDeposit->Account 		= isset($_GET['account']) ? trim($_GET['account']) : '';
		$Acc_name 		= $oCCBDeposit->AccountName 	= isset($_GET['acc_name']) ? trim($_GET['acc_name']) : '';
		if (!is_numeric($iStatus) && empty($sUsername) && empty($iRid) && empty($sKey) && empty($starttime) && empty($endtime) &&
			empty($Account) && empty($Acc_name)){
				sysMessage("请选择下载条件", 1, $aLinks);
			}
		$aResult = $oCCBDeposit->getList();
		
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
		
		// 遍历记录集，获取所属总代，银行卡财务别名等操作
		$oPayList = new model_deposit_depositaccountlist(array(),'','array');
		$aAccList = array();
		// 查询银行下分账户列表，卡号做键名
		$aAccList = $oPayList->singleList("ccb",false,'ads_payport_name','acc_bankacc');
		foreach ($aResult as $key => $val) {
			$fTotal += $v['money'];
			$fBankTotal += $v['amount'];

			$aResult[$key]['nickname1'] = !empty($aAccList[$val['accept_card']]['acc_name']) ? $aAccList[$val['accept_card']]['acc_name'] : $val['accept_card']; // 平台记录卡号
			$aResult[$key]['nickname2'] = !empty($aAccList[$val['full_account']]['acc_name']) ? $aAccList[$val['full_account']]['acc_name'] : !empty($val['full_account']) ? $val['full_account'] : $val['hidden_account'];	// 银行抓取卡号
			
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
				$aRecord[$key]['status'] = "已退还";
			} elseif ($val['status'] == 4){
				$aRecord[$key]['status'] = "管理员处理";
			} elseif ($val['status'] == 5){
				$aRecord[$key]['status'] = "没收";
			}
			
			$aRecord[$key]['username'] = $val['user_name'];
			$aRecord[$key]['topproxy'] = $val['topproxy_name'];
			$aRecord[$key]['created'] = $val['created'];
			$aRecord[$key]['accept_account'] = $aResult[$key]['nickname1'];
			$aRecord[$key]['account'] = $val['account'];
			$aRecord[$key]['account_name'] = $val['account_name'];
			$aRecord[$key]['money'] = $val['money'];
			$aRecord[$key]['create'] = $val['create'];
			$aRecord[$key]['accept_card'] = $aResult[$key]['nickname2'];
			$aRecord[$key]['amount'] = $val['amount'];
			$aRecord[$key]['balance'] = $val['balance'];
			$aRecord[$key]['full_account'] = !empty($val['full_account']) ? $val['full_account'] : $val['hidden_account'];
			$aRecord[$key]['acc_name'] = $val['acc_name'];
			$aRecord[$key]['add_money_time'] = $val['add_money_time'];
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
		
		$aSign[4] 		= "平";
		$aTitle[4] 		= "发起时间";
		$aSign[5] 		= "台";
		$aTitle[5] 		= "收款卡";
		$aSign[6] 		= "充";
		$aTitle[6] 		= "付款卡";
		$aSign[7] 		= "值";
		$aTitle[7] 		= "付款用户名";
		$aSign[8] 		= "信息";
		$aTitle[8] 		= "金额";

		$aSign[9] 		= "银";
		$aTitle[9] 		= "抓取时间";
		$aSign[10] 		= "行";
		$aTitle[10] 	= "收款卡";
		$aSign[11] 		= "抓";
		$aTitle[11] 	= "金额";
		$aSign[12] 		= "取";
		$aTitle[12] 	= "账户余额";
		$aSign[13] 		= "信";
		$aTitle[13] 	= "付款卡";
		$aSign[14] 		= "息";
		$aTitle[14] 	= "付款户名";

		$aSign[15] 		= "";
		$aTitle[15] 	= "加游戏币时间";
		
		
		$iIdList = "";
		$iIdList = implode(',', $aRecordId);
		
		$sFileName = time() . '-' . $_SESSION['adminname'];
		
		// 生成文件，打包，下载
        $oDownload = new model_withdraw_download("ccbdownload", $sFileName . '.csv', $aRecord, $aRecordId, "建行充值申请表", "zip", $aTitle, false, $aSign);
        EXIT;
	}
	
	
	
	
	/**
	 * 建行充值异常检测列表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-07
	 * @package 	passportadmin
	 * 
	 */
	public function actionUnNormalList(){
		$iPageSize = 60;  // 每页显示条数
		$oCCBDeposit = new model_ccbdeposit();
		
		// 页面搜索信息
		$aHtml = array();
		$p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
		$pn = isset($_GET['pn']) ? intval($_GET['pn']) : 0;
		$iPageSize = $aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25;  
		
		$aHtml['status'] 		= $oCCBDeposit->Status		= isset($_GET['status']) ? intval($_GET['status']) : 1;
		$aHtml['error_type'] 	= $oCCBDeposit->ErrorType 	= isset($_GET['error_type']) ? trim($_GET['error_type']) : '';
		$aHtml['adminname'] 	= $oCCBDeposit->AdminName	= isset($_GET['adminname']) ? trim($_GET['adminname']) : '';
		$aHtml['username'] 		= $oCCBDeposit->UserName 	= isset($_GET['username']) ? trim($_GET['username']) : '';
		$aHtml['topproxy'] 		= $oCCBDeposit->TopProxy 	= isset($_GET['topproxy']) ? trim($_GET['topproxy']) : '';
		$aHtml['starttime'] 	= $oCCBDeposit->StartTime	= isset($_GET['starttime']) ? trim($_GET['starttime']) : '';
		$aHtml['endtime'] 		= $oCCBDeposit->EndTime		= isset($_GET['endtime']) ? trim($_GET['endtime']) : '';
		$aHtml['startdeal'] 	= $oEmailDeposit->StartExpire	= isset($_GET['startdeal']) ? trim($_GET['startdeal']) : '';
		$aHtml['enddeal'] 		= $oEmailDeposit->EndExpire		= isset($_GET['enddeal']) ? trim($_GET['enddeal']) : '';
		$oCCBDeposit->Pages		= intval($p);
								  
		$oCCBDeposit->PageSize = $iPageSize; // 每页显示条数
		$aResult = $oCCBDeposit->getErrorList();
//        print_rr($aResult,1,1);
		
		if (!empty($aResult)){
			// 计算当前面的数据统计
			$fPlateTotal = 0;
			$fBankTotal = 0;
            $fFee = 0;
			$oPager = new pages( $oCCBDeposit->TotalCount, $iPageSize, 0);    // 分页用3
			
			// 遍历记录集，获取所属总代，银行卡财务别名等操作
			$oUser = new model_user();
			$oPayList = new model_deposit_depositaccountlist(array(),'','array');
			// 查询银行下分账户列表，卡号做键名
			$aAccInfo = array();
			$aAccInfo = $oPayList->singleList("ccb",false,'ads_payport_name','acc_bankacc');
			foreach ($aResult as $key => $v) {
				$fPlateTotal += floatval($v['request_amount']);
				$fBankTotal += floatval($v['pay_amount']);
                $fFee += floatval($v['pay_fee']);
				
				$aResult[$key]['nickname1'] = $aAccInfo[$v['request_card']]['acc_name'];
				foreach($aAccInfo as $card=>$info)
				{
					if(substr($card,-4) == $v['get_card'])
					{
						$aResult[$key]['nickname2'] = $info['acc_name'];
						break;
					}
						
				}
//				$aResult[$key]['nickname2'] = $aAccInfo[$v['get_card']]['acc_name'];
			}
		}
		
		$GLOBALS['oView']->assign( 'ur_here', '建行异常充值检测' );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("ccbreport","unnormallist"), 'text'=>'清空查询条件' ) );
		if (!empty($aResult)){
			$GLOBALS['oView']->assign( 'pages', $oPager->show(2) ); // 分页用4
		}
		$GLOBALS['oView']->assign( 'loadinfo', $aResult ); 
		$GLOBALS['oView']->assign( 'fplatetotal', $fPlateTotal ); 
		$GLOBALS['oView']->assign( 'fbanktotal', $fBankTotal ); 
		$GLOBALS['oView']->assign( 'fee', $fFee ); 
		$GLOBALS['oView']->assign( 'aHtml', $aHtml ); 
        $oCCBDeposit->assignSysInfo();
        $GLOBALS['oView']->display("ccbreport_unnormallist.html");
        EXIT;
	}
	
	
	
	
	
	/**
	 * 建行充值异常处理
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passportadmin
	 * 
	 */
	public function actionErrorDeal(){
		$aLinks = array(
			0 => array(
				'text' => '返回异常充值检测',
				'href' => '?controller=ccbreport&action=unnormallist'
			)
		);
		// 数据检查
		if (empty($_POST['loadid']) || empty($_POST['remark']) || (intval($_POST['deal']) !== 2 && intval($_POST['deal']) !== 3 && intval($_POST['deal']) !== 4 )){
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
		
		$oCCBDeposit = new model_ccbdeposit();
		// 开始处理操作
		$mResult = $oCCBDeposit->errorDeal( $_POST['loadid'], $_POST['remark'], intval($_POST['deal']), $_SESSION['admin'], $_SESSION['adminname']);
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
	 * 查看建行充值详情
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passportadmin
	 * 
	 */
	public function actionLoadView(){
		$aLinks = array(
			0 => array(
				'text' => '返回异常充值检测',
				'href' => '?controller=ccbreport&action=unnormallist'
			)
		);
		// 数据检查
		if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
			sysMessage("您提交的数据有误", 1, $aLinks);
		}
		
		//先查询对应的抓取记录的id
		$oErrorDeposit = new model_ccbdeposit();
		$oErrorDeposit->Id = $_GET['id'];
		$aErrorInfo = $oErrorDeposit->getOneById();
		if (empty($aErrorInfo)){
			sysMessage("您请求的数据不存在", 1, $aLinks);
		}
		
		$oCCBDeposit = new model_deposit_ccbdeposit();
		// 查询数据是否存在
		$oCCBDeposit->BankRecordId = intval($aErrorInfo['transfer_id']);
		$aResult = $oCCBDeposit->getOneBankRecord();
		if (empty($aResult)){
			sysMessage("您请求的数据不存在", 1, $aLinks);
		}
		// 获取记录前的5条记录
		$oCCBDeposit->Id = intval($aErrorInfo['transfer_id']);
		$aLastFiveRecords = $oCCBDeposit->getLastRecords();
        $aLastFiveRecords = array_reverse($aLastFiveRecords);
        
		
		$GLOBALS['oView']->assign( 'ur_here', '抓取详情' );
		$GLOBALS['oView']->assign( 'bankname', "中国建行银行" );
		$GLOBALS['oView']->assign( 'request_card', $aErrorInfo['request_card'] );
		$GLOBALS['oView']->assign( 'get_card', $aErrorInfo['get_card'] );
		$GLOBALS['oView']->assign( 'recordlist', $aLastFiveRecords );
		$GLOBALS['oView']->assign( 'id', $aErrorInfo['transfer_id'] );
        $oCCBDeposit->assignSysInfo();
        $GLOBALS['oView']->display("ccbreport_loadview.html");
        EXIT;
	}
}