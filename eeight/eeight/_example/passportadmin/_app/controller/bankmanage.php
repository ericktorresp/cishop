<?php
/**
 * 文件 : /_app/controller/bankmanage.php
 * 功能 : 控制器 - 在线充提银行管理
 * 
 * 
 *    - actionbanklist()				在线提现接受银行列表,编辑，删除，启/禁用
 * 	  +-- actionAddBankInfo()   		增加银行信息
 * 
 *    - actionWithdrawReportList()		提现报表格式列表
 *    +-- actionWithdrawFormat()    	报表内容列表
 *    +-- actionWithdrawReportEdit()	编辑提现报表名称信息
 *    +-- actionAddReport()				添加一份提现报表
 * 
 *    - actionWithdrawBankList()		提现银行列表
 *    +-- actionAddWithdrawBank()		增加提现银行
 *    +-- actionEditWithdrawBank()		编辑提现银行，删除，启用，禁用
 * 
 *    - actionPayBankList()				平台充值接受的银行列表
 *    +-- actionAddPayBank()			增加平台充值接受的银行
 *    +-- actionEditPayBank()			编辑充值银行，删除，启用，禁用
 * 
 *    --actionUserAndCard()				email充值，用户与银行卡关系列表
 *    +--actionList()					用户列表 [左框架]
 * 	  +--actionView()					分卡时，用户列表，右框架
 *    +--actionDealCard()				分配银行卡
 *    +--actionUpdateStatus()			修改用户卡状态，启用或禁用
 * 
 *    --actionInitInstead()             批量替换卡前的检查
 * 
 * @author	  louis
 * @version   v1.0		2010-04-22
 * @package   passportadmin
 */
class controller_bankmanage extends basecontroller {
	
	
	/**
	 * 在线提现接受银行列表,编辑，删除，启/禁用
	 *
	 * @version 	v1.0		2010-03-16
	 * @author 		louis
	 * 
	 * @return 		array
	 */
	public function actionbanklist(){
		$aLinks = array(
				0 => array(
					'text' => "提现银行列表",
	        		'href' => "?controller=bankmanage&action=banklist"
				),
		);
		$_GET['flag'] = isset($_GET['flag']) ? $_GET['flag'] : '';
		// 银行信息修改页面
		if ($_GET['flag'] == 'edit'){
			// 编辑
			if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
				sysMessage("对不起，您提交的银行信息不正确，请核对后重新提交", 1, $aLinks);
			}
			
			$oBank = new model_withdraw_Bank($_GET['id']);
			$GLOBALS['oView']->assign( 'ur_here', '修改提现银行信息' );
			$GLOBALS['oView']->assign( 'id', $oBank->Id );
			$GLOBALS['oView']->assign( 'bank_name', $oBank->BankName );
			$GLOBALS['oView']->assign( 'card_type', $oBank->CardType );
			$GLOBALS['oView']->assign( 'code_length', $oBank->CodeLength );
			$GLOBALS['oView']->assign( 'url', $oBank->Url );
			$GLOBALS['oView']->assign( 'logo', DS . 'images' . DS . 'bank' . DS . $oBank->Logo);
			$GLOBALS['oView']->assign( 'manual', $oBank->Manual );
			$GLOBALS['oView']->assign( 'status', $oBank->Status );
	        $oBank->assignSysInfo();
	        $GLOBALS['oView']->display("bankmanage_bankedit.html");
	        EXIT;
		}
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
		// 银行信息修改操作
		if ($_POST['flag'] == 'doEdit') {
			if (!is_numeric($_POST['id']) || intval($_POST['id']) <= 0){
				sysMessage("对不起，您提交的银行信息不正确，请核对后重新提交", 1, $aLinks);
			}
			$oBank = new model_withdraw_Bank($_POST['id']);
			if ($_FILES["logo"] != ""){
				// 加载文件上传函数
				require A_DIR . DS . 'includes' . DS . 'plugin' . DS . 'filefunc.php';
				$sPath				= PDIR_USER . DS . 'images' . DS . 'bank'; 	// 图片上传地址
				$sAllowedMime 		= "image";								// 允许的文件的MIME类型
		 		$sAllowedExtension 	= "png|jpg|ico|jpeg|gif";				// 接受的图片类型
		 		$iAllowedMinSize 	= 1024;									// 上传文件的最小字节
		 		$iAllowedMaxSize 	= 204800;								// 上传文件的最大字节
		 		$iAllowedMinWidth 	= 50;									// 最小宽度,像素
		 		$iAllowedMaxWidth 	= 300;									// 最大宽度,像素
		 		$aResult = saveUploadFile( $_FILES["logo"], $sPath, $sAllowedMime, $sAllowedExtension, $iAllowedMinSize, $iAllowedMaxSize, $iAllowedMinWidth, $iAllowedMaxWidth );
		 		$aResult["code"] === 0 or sysMessage($aResult["err_msg"], 0, $aLinks);
		 		$sExtension = pathinfo($aResult["name"]);
		 		// 新文件名
		 		$sNewFilename = $oBank->Id . '.' . $sExtension["extension"];
		 		// 修改上传图片的名称为银行ID
		 		$bRename = rename($aResult['name'], $sPath . DS . $sNewFilename);
		 		if ($bRename){
		 			// 删除原有文件
		 			@unlink($aResult['name']);
		 		}
		 		// 上传成功后，更改图片访问路径，如http://www.passportadmin.php/images/test.jpg
				$sImgUrl = $sNewFilename;
			}
			$oBank->BankName		= $_POST['bank_name'];
			$oBank->CardType		= $_POST['card_type'];
			$oBank->CodeLength		= $_POST['code_length'];
			$oBank->Url				= $_POST['url'];
			$oBank->Logo			= $sImgUrl;
			$oBank->Manual			= $_POST['manual'];
			$oBank->Status			= $_POST['status'];
			$bResult = $oBank->save();
			
			$bResult === false or sysMessage("修改成功", 0, $aLinks );
		}
		// 删除操作
		if ($_GET['flag'] == 'delete'){
			// 删除
			if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
				sysMessage("对不起，您提交的银行信息不正确，请核对后重新提交", 1, $aLinks);
			}
			$oBank = new model_withdraw_Bank($_GET['id']);
			$bResult = $oBank->erase();
		}
		// 修改状态操作
		if ($_GET['flag'] == 'set') {
			if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
				sysMessage("对不起，您提交的银行信息不正确，请核对后重新提交", 1, $aLinks);
			}
			$oBank = new model_withdraw_Bank($_GET['id']);
			$oBank->setStatus();
		}
		$oBankList = new model_withdraw_BankList();
		$oBankList->init();
        $GLOBALS['oView']->assign( 'ur_here', '银行列表' );
        $GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("bankmanage","addbankinfo"), 'text'=>'增加银行' ) );
        $GLOBALS['oView']->assign( 'aBank', $oBankList->Data );
        $oBankList->assignSysInfo();
        $GLOBALS['oView']->display("bankmanage_banklist.html");
        EXIT;
	}
	
	
	
	/**
	 * 增加银行信息
	 *
	 * @version 		v1.0 		2010-03-22
	 * @author 			louis
	 * 
	 * @return 			boolean
	 */
	public function actionAddBankInfo(){
		$aLinks = array(
				0 => array(
					'text' => '返回银行列表',
					'href' => '?controller=bankmanage&action=banklist'
				)
			);
		$oBank = new model_withdraw_Bank();
		if ($_POST['flag'] == 'add'){
			if (empty($_POST['bank_name']))
				sysMessage("对不起，您提交的银行信息不正确，请核对后重新提交", 1, $aLinks);
			// 首先检查是否已存在需要提交的银行信息
			$oBank->BankName = trim($_POST['bank_name']);
			$bResult = $oBank->bankExists();
			!$bResult or sysMessage("您已经添加过'" . $oBank->BankName ."'啦，请添加其它银行", 1, $aLinks);
			if ($_FILES["logo"] != ""){
				// 加载文件上传函数
				require A_DIR . DS . 'includes' . DS . 'plugin' . DS . 'filefunc.php';
				$sPath				= PDIR_USER . DS . 'images' . DS . 'bank'; 	// 图片上传地址
				$sAllowedMime 		= "image";								// 允许的文件的MIME类型
		 		$sAllowedExtension 	= "png|jpg|ico|jpeg|gif";					// 接受的图片类型
		 		$iAllowedMinSize 	= 1024;									// 上传文件的最小字节
		 		$iAllowedMaxSize 	= 204800;								// 上传文件的最大字节
		 		$iAllowedMinWidth 	= 50;									// 最小宽度,像素
		 		$iAllowedMaxWidth 	= 300;									// 最大宽度,像素
		 		$aResult = saveUploadFile( $_FILES["logo"], $sPath, $sAllowedMime, $sAllowedExtension, $iAllowedMinSize, $iAllowedMaxSize, $iAllowedMinWidth, $iAllowedMaxWidth );
		 		$aResult["code"] === 0 or sysMessage($aResult["err_msg"], 0, $aLinks);
				// 上传成功后，更改图片访问路径，如http://www.passportadmin.php/images/test.jpg
				$sImgUrl = basename($aResult['name']);
			}
			
			$oBank->BankName 		= $_POST['bank_name'];
			$oBank->CardType		= $_POST['card_type'];
			$oBank->CodeLength		= $_POST['code_length'];
			$oBank->Manual			= $_POST['manual'];
			$oBank->Url				= $_POST['url'];
			$oBank->Logo			= $sImgUrl;
			$oBank->Status			= $_POST['status'];
			
			$iResult = $oBank->save();
			if ($iResult) {
				if ($_FILES["logo"] != ""){
					// 修改上传的图片名称为银行ID
					$sExtension = pathinfo($aResult["name"]);
					$bRename = rename($aResult['name'], $sPath . DS . $iResult . '.' . $sExtension["extension"]);
					if ($bRename){
						// 修改数据库中的logo地址
						// 重新替换图片访问路径
						$sImgUrl = $iResult . '.' . $sExtension["extension"];
						$oBank->Id = $iResult;
						$oBank->Logo = urlencode($sImgUrl);
						$oBank->save();
					}
				}
				sysMessage("操作成功", 0, $aLinks);
			} else {
				sysMessage("操作失败", 1, $aLinks);
			}
		}
		$GLOBALS['oView']->assign( 'ur_here', '增加银行' );
		$GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("bankmanage","addbankinfo"), 'text'=>'增加银行' ) );
        $oBank->assignSysInfo();
        $GLOBALS['oView']->display("bankmanage_addBankInfo.html");
        EXIT;
	}
	
	
	
	/**
	 * 提现报表格式列表
	 *
	 */
	public function actionWithdrawReportList(){
		$oWDReportList = new model_withdraw_WithdrawReportList();
		$oWDReportList->Status = "0,1";
		$oWDReportList->init();
		$GLOBALS['oView']->assign("ur_here",   "提现报表列表");
		$GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("bankmanage","addreport"), 'text'=>'增加报表' ) );
		$GLOBALS['oView']->assign("reportlist",   $oWDReportList->Data);
		$oWDReportList->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_withdrawreportlist.html");
    	EXIT;
	}
	
	
	
	/**
	 * 报表内容列表
	 *
	 * @version 	v1.0	2010-04-06
	 * @author 		louis
	 */
	public function actionWithdrawFormat(){
		$aLinks = array(
			0 => array(
				'text' => "返回银行提现报表列表",
				'href' => "?controller=bankmanage&action=withdrawreportlist"
			)
		);
		$_GET['id'] = isset($_GET['id']) ? $_GET['id'] : 0;
		$_POST['id'] = isset($_POST['id']) ? $_POST['id'] : 0;
		$iReportId = $_GET['id'] > 0 ? $_GET['id'] : $_POST['id'];
		
		$oWDFormat = new model_withdraw_WithdrawFormat();
		
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
		// 删除指定报表列
		if ($_POST['flag'] == 'del'){
			// 数据检查
			if (!is_numeric($iReportId) || $iReportId <= 0){
				die('-1');
			}
			$oWDFormat->Id = $iReportId;
			echo $oWDFormat->erase();die;
		}
		
		// 修改状态
		if ($_POST['flag'] == "change"){
			if (!is_numeric($iReportId) || $iReportId <= 0){
				die('-1');
			}
			$oWDFormat->Id				= $iReportId;
			$oWDFormat->AdminId			= $_SESSION['admin'];
			$oWDFormat->AdminName		= $_SESSION['adminname'];
			$oWDFormat->Status 			= true;
			$oWDFormat->ReturnStatus	= true;
			$iResult = $oWDFormat->save();
			if($iResult > 0){
				echo $iResult - 1;die;
			} else {
				echo -1;die;
			}
		}
		
		if (!is_numeric($iReportId) || $iReportId <= 0){
			sysMessage("提交数据有误，请核对后重新提交！", 1, $aLinks);
		}
		
		// 添加报表列
		if ($_POST['flag'] == "add"){
			!empty($_POST['content']) or sysMessage("提交数据有误，请核对后重新提交！", 1, $aLinks);
			// 获取报表已有内容列
			$oWDFormatList = new model_withdraw_WithdrawFormatList();
			$oWDFormatList->PPId = $iReportId;
			$oWDFormatList->init();
			$aFormatList = $oWDFormatList->Data;
			
			$aFormat = array();
			foreach ($aFormatList as $formatList){
				$aFormat[$formatList['id']] = $formatList['property'];
			}
			
			// 组合成方便操作的形式
			$aAddColumn = array(); // 需要添加的报表列
			foreach ($_POST['content'] as $k => $content){
				$aTemp = explode("#", $content);
				if ($key = array_search($aTemp[1], $aFormat)){
					unset($aFormat[$key]);
				} else {
					$aAddColumn[$k]['title'] = $aTemp[0];
					$aAddColumn[$k]['property'] = $aTemp[1];
				}
			}
			
			$oWDFormat->PPId 		= $iReportId;
			$oWDFormat->AdminId 	= $_SESSION['admin'];
			$oWDFormat->AdminName 	= $_SESSION['adminname'];
			$oWDFormat->Status 		= 1;
			$oWDFormat->AddTime 	= date("Y-m-d H:i:s", time());
			if ($oWDFormat->columnAddList($aAddColumn, $aFormat) > 0)
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
			exit;
		}
		
		
		// 批量删除
		if ($_POST['flag'] == "formdel"){
			if (empty($_POST['row'])){
				sysMessage("提交数据有误，请核对后重新提交！", 1, $aLinks);
			}
			$iIdList = implode(",", $_POST['row']);
			$oWDFormat->Id = $iIdList;
			if ($oWDFormat->erase())
				sysMessage("操作成功！", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		
		// 批量更改状态
		if ($_POST['flag'] == "formset"){
			if (empty($_POST['row'])){
				sysMessage("提交数据有误，请核对后重新提交！", 1, $aLinks);
			}
			$iIdList = implode(",", $_POST['row']);
			$oWDFormat->Id		= $iIdList;
			$oWDFormat->Status	= true;
			if ($oWDFormat->save())
				sysMessage("操作成功！", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		
	 	// 排序
	 	if ($_POST['flag'] == "set"){
	 		if (empty($_POST['row']) || empty($_POST['row']) || empty($_POST['seq'])){
				sysMessage("提交数据有误，请核对后重新提交！", 1, $aLinks);
			}
			
			foreach ($_POST['row'] as $k => $v){
				$oWDFormat->Id = $v;
				$oWDFormat->Seq = $_POST['seq'][$v];
				if (!$oWDFormat->save()){
					sysMessage("操作失败！", 1, $aLinks);
				}
			}
			sysMessage("操作成功！", 0, $aLinks);
	 	}
		
		$oWDReport = new model_withdraw_WithdrawReport($iReportId);
		if ($oWDReport->Id <= 0){
			sysMessage("您请求的报表不存在！", 1, $aLinks);
		}
		$oFODetails = new model_withdraw_fundoutdetail();
		$aContent = $oFODetails->getReportContent();
		
		// 取出报表中的报表列
		$oWDFormat->PPId = $iReportId;
		$aResult = $oWDFormat->getInfoByPPId();
		$aProperty = array();
		if (!empty($aResult)){
			foreach ($aResult as $v){
				$aProperty[] = $v['property'];
			}
		}
		
		if (!empty($aContent)){
			foreach ($aContent as $k => $content){
				if (in_array($content['property'], $aProperty)){
					$aContent[$k]['checked'] = 1;
				}
			}
		}
		
		$GLOBALS['oView']->assign("ur_here",   "报表格式内容");
		$GLOBALS['oView']->assign("id",   $oWDReport->Id);
		$GLOBALS['oView']->assign("report_name",   $oWDReport->ReportName);
		$GLOBALS['oView']->assign("admin_name",   $oWDReport->AdminName);
		$GLOBALS['oView']->assign("atime",   $oWDReport->AddTime);
		$GLOBALS['oView']->assign("utime",   $oWDReport->UpdateTime);
		$GLOBALS['oView']->assign("report_content",   $aResult);
		$GLOBALS['oView']->assign("withdrawinfo", $aContent);
		$oWDFormat->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_withdrawformat.html");
    	EXIT;
	}
	
	
	
	/**
	 * 编辑提现报表名称信息
	 *
	 * @version 	v1.0	2010-04-06
	 * @author 		louis
	 */
	public function actionWithdrawReportEdit(){
		$aLinks = array(
			0 => array(
				'text' => "返回银行提现报表",
				'href' => "?controller=bankmanage&action=withdrawreportlist"
			)
		);
		$iReportId = $_GET['id'] > 0 ? $_GET['id'] : $_POST['id'];
		if (!is_numeric($iReportId) || intval($iReportId) <= 0){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
		// 编辑
		if ($_POST['flag'] == "edit"){
			if(empty($_POST['report_name']) || empty($_POST['charset_type']) || !isset($_POST['status']))
				sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
			$oWDReport = new model_withdraw_WithdrawReport($iReportId);
			if ($oWDReport->Id <= 0 ){
				sysMessage("您提交的报表信息不存在，请核对后重新提交！", 1, $aLinks);
			}
			$oWDReport->ReportName = $_POST['report_name'];
			if ($oWDReport->reportExistsByName()){
				sysMessage("您填写的报表名称已存在！", 1, $aLinks);
			}
			$oWDReport->Charset    = $_POST['charset_type'];
			$oWDReport->Status     = $_POST['status'];
			if ($oWDReport->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		$_GET['flag'] = isset($_GET['flag']) ? $_GET['flag'] : '';
		// 删除
		if ($_GET['flag'] == "del"){
			$oWDReport = new model_withdraw_WithdrawReport($iReportId);
			$oWDReport->Status     = 2;
			if ($oWDReport->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		// 修改状态
		if ($_GET['flag'] == "set"){
			$oWDReport = new model_withdraw_WithdrawReport($iReportId);
			$oWDReport->Status     = intval(1 - $oWDReport->Status);
			if ($oWDReport->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		
		$oWDReport = new model_withdraw_WithdrawReport($iReportId);
		if ($oWDReport->Id <= 0){
			sysMessage("您请求的信息不存在！", 1, $aLinks);
		}
		// 取出报表中的报表列
		$oWDFormat = new model_withdraw_WithdrawFormat();
		$oWDFormat->PPId = $oWDReport->Id;
		$aResult = $oWDFormat->getInfoByPPId();
		$GLOBALS['oView']->assign("ur_here",   "编辑提现报表");
		$GLOBALS['oView']->assign("report_name",   $oWDReport->ReportName);
		$GLOBALS['oView']->assign("charset",   $oWDReport->Charset);
		$GLOBALS['oView']->assign("status",   $oWDReport->Status);
		$GLOBALS['oView']->assign("report_content",   $aResult);
		$GLOBALS['oView']->assign("colnum",   count($aResult));
		$GLOBALS['oView']->assign("id",   $oWDReport->Id);
		$oWDReport->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_withdrawreportedit.html");
    	EXIT;
	}
	
	
	/**
	 * 添加一份提现报表
	 *
	 */
	public function actionAddReport(){
		if ($_POST['flag'] == "add"){
			if($_POST['platform_type'] <= 0 || $_POST['platform_name'] <= 0 || empty($_POST['report_name']) || 
				empty($_POST['charset_type']) || !isset($_POST['status']))
				sysMessage("您提交的数据不完整，请核对后再提交！", 1);
			$aLinks = array(
				0 => array(
					'text' => "返回提现报表列表",
					'href' => "?controller=bankmanage&action=withdrawreportlist"
				)
			);
			$aInfo = array();
			$aInfo['report_name'] 	= trim($_POST['report_name']);
			$aInfo['platform_type'] = intval($_POST['platform_type']);
			$aInfo['platform_name'] = intval($_POST['platform_name']);
			$aInfo['charset_type'] 	= trim($_POST['charset_type']);
			$aInfo['admin'] 		= $_SESSION['admin'];
			$aInfo['adminname'] 	= $_SESSION['adminname'];
			$aInfo['status'] 		= intval($_POST['status']);
			$oAddReport = new model_withdraw_AddReport($aInfo, $_POST['content']);
			if ($oAddReport->Error === true)
				sysMessage("操作成功！", 0, $aLinks);
			else if ($oAddReport->Error == -3){
				sysMessage("您已添加过此报表啦！", 1, $aLinks);
			} else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		$oWdReport = new model_withdraw_WithdrawReport();
		// 获取支付接口列表
		$oApiList = new model_pay_payportlist(array(), '', 'array');
		// 获取银行列表
		$oBankList = new model_withdraw_BankList();
		$oBankList->Status = 1;
		$oBankList->init();
		// 支付接口下拉框
		$sApiSelect = "";
		$sApiSelect .= "<select name='platform_name'>";
		foreach ($oApiList->Data as $api){
			$sApiSelect .= "<option value='" . $api['id'] . "'>" . $api['payport_nickname'] . "</option>";
		}
		$sApiSelect .= "</select>";
		// 银行平台下拉框
		$sBankSelect = "";
		$sBankSelect .= "<select name='platform_name'>";
		foreach ($oBankList->Data as $bank) {
			$sBankSelect .= "<option value='" . $bank['id'] . "'>" . $bank['bank_name'] . "</option>";
		}
		$sBankSelect .= "</select>";
		$oFODetails = new model_withdraw_fundoutdetail();
		$GLOBALS['oView']->assign("ur_here",   "增加报表");
		$GLOBALS['oView']->assign( 'apiList', $sApiSelect);
		$GLOBALS['oView']->assign( 'bankList', $sBankSelect);
		$GLOBALS['oView']->assign( 'reportcontent', $oFODetails->getReportContent());
		$oWdReport->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_addreport.html");
    	EXIT;
	}
	
	
	
	/**
	 * 提现银行列表
	 * 
	 * @version 	v1.0	2010-04-07
	 * @author 		louis
	 */
	public function actionWithdrawBankList(){
		$oApiBankList = new model_withdraw_ApiWDBankList();
		$oApiBankList->Status = "0,1"; // 只取可用的银行
		$oApiBankList->init();
		$GLOBALS['oView']->assign("ur_here",   "提现银行列表");
		$GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("bankmanage","addwithdrawbank"), 'text'=>'增加提现银行' ) );
		$GLOBALS['oView']->assign("banklist",  $oApiBankList->Data);
		$oApiBankList->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_withdrawbanklist.html");
    	EXIT;
	}
	
	
	
	/**
	 * 增加提现银行
	 *
	 * @version 	v1.0	2010-04-07
	 * @author 		louis	 
	 */
	public function actionAddWithdrawBank(){
		// 获取支付接口列表
    	$oApiList = new model_pay_payportlist(array(), '', 'array');
    	// 获取银行列表
    	$oBankList = new model_withdraw_BankList();
    	$oBankList->Status = 1; // 只取可用的银行
    	$oBankList->init();
		if ($_POST['flag'] == "add"){
			$aLinks = array(
				0 => array(
					'text' => "返回提现银行列表",
					'href' => "?controller=bankmanage&action=withdrawbanklist"
				)
			);
			// 数据检查
			if (empty($_POST['api_name']) || empty($_POST['bank_name']) || empty($_POST['bank_code']) || !isset($_POST['status']))
				sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
			$oWithdrawBank = new model_withdraw_ApiWithdrawBank();
			$aApi = explode("#", $_POST['api_name']);
			$aBank = explode("#", $_POST['bank_name']);
			$oWithdrawBank->ApiId		= $aApi[0];
			$oWithdrawBank->ApiName 	= $aApi[1];
			$oWithdrawBank->BankId		= $aBank[0];
			$oWithdrawBank->BankName	= $aBank[1];
			$oWithdrawBank->BankCode	= $_POST['bank_code'];
			$oWithdrawBank->Status		= $_POST['status'] != -1 ? $_POST['status'] : 1;
			$oWithdrawBank->AddTime		= date("Y-m-d H:i:s", time());
			
			// 首先检查银行是否已经存在
			!$oWithdrawBank->bankExists() or sysMessage("您已添加过些银行啦！", 1, $aLinks);
			
			if ($oWithdrawBank->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		$GLOBALS['oView']->assign("ur_here",   "增加提现银行");
		$GLOBALS['oView']->assign("apilist",   $oApiList->Data);
		$GLOBALS['oView']->assign("banklist",   $oBankList->Data);
		$oBankList->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_addwithdrawbank.html");
    	EXIT;
	}
	
	
	
	/**
	 * 编辑提现银行，删除，启用，禁用
	 * 
	 * @version 	v1.0	2010-04-07
	 * @author 		louis
	 */
	public function actionEditWithdrawBank(){
		$aLinks = array(
			0 => array(
				'text' => "返回提现银行列表",
				'href' => "?controller=bankmanage&action=withdrawbanklist"
			)
		);
		$iId = $_GET['id'] > 0 ? $_GET['id'] : $_POST['id'];
		if (!is_numeric($iId) || $iId <= 0){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
		
		$oWithdrawBank = new model_withdraw_ApiWithdrawBank($iId);
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
		// 编辑
		if ($_POST['flag'] == "edit"){
			$oWithdrawBank->BankCode	= $_POST['bank_code'];
			$oWithdrawBank->Status		= $_POST['status'] != -1 ? $_POST['status'] : 1;
			if($oWithdrawBank->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败", 1, $aLinks);
		}
		$_GET['flag'] = isset($_GET['flag']) ? $_GET['flag'] : '';
		// 删除
		if ($_GET['flag'] == "del"){
			$oWithdrawBank->Id	= $iId;
			$oWithdrawBank->Status = 2; // 设置为逻辑删除
			if ($oWithdrawBank->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		// 启用/禁用
		if ($_GET['flag'] == "set"){
			$oWithdrawBank->Id	= $iId;
			$oWithdrawBank->Status = intval(1 - $oWithdrawBank->Status);
			if ($oWithdrawBank->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		$GLOBALS['oView']->assign("ur_here",   "编辑提现银行");
		$GLOBALS['oView']->assign("id",   $oWithdrawBank->Id);
		$GLOBALS['oView']->assign("api_name",   $oWithdrawBank->ApiName);
		$GLOBALS['oView']->assign("bank_name",   $oWithdrawBank->BankName);
		$GLOBALS['oView']->assign("bank_code",   $oWithdrawBank->BankCode);
		$GLOBALS['oView']->assign("status",   $oWithdrawBank->Status);
		$oWithdrawBank->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_editwithdrawbank.html");
    	EXIT;
	}
	
	
	/**
	 * 平台充值接受的银行列表
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function actionPayBankList(){
		$oPayBankList = new model_withdraw_PayBankList();
		$oPayBankList->Status = "0,1"; // 只取可用的银行
		$oPayBankList->init();
		$GLOBALS['oView']->assign("ur_here",   "充值银行列表");
		$GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("bankmanage","addpaybank"), 'text'=>'增加充值银行' ) );
		$GLOBALS['oView']->assign("banklist",  $oPayBankList->Data);
		$oPayBankList->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_paybanklist.html");
    	EXIT;
	}
	
	
	
	/**
	 * 增加平台充值接受的银行
	 *
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function actionAddPayBank(){
		// 获取支付接口列表
    	$oApiList = new model_pay_payportlist(array(), '', 'array');
    	// 获取银行列表
    	$oBankList = new model_withdraw_BankList();
    	$oBankList->Status = 1; // 只取可用的银行
    	$oBankList->init();
    	if ($_POST['flag'] == "add"){
			$aLinks = array(
				0 => array(
					'text' => "返回充值银行列表",
					'href' => "?controller=bankmanage&action=paybanklist"
				)
			);
			// 数据检查
			if (empty($_POST['api_name']) || empty($_POST['bank_name']) || empty($_POST['bank_code']) || !isset($_POST['status']))
				sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
			$oPayBank = new model_withdraw_paybank();
			$aApi = explode("#", $_POST['api_name']);
			$aBank = explode("#", $_POST['bank_name']);
			$oPayBank->ApiId		= $aApi[0];
			$oPayBank->ApiName 		= $aApi[1];
			$oPayBank->BankId		= $aBank[0];
			$oPayBank->BankName		= $aBank[1];
			$oPayBank->BankCode		= $_POST['bank_code'];
			$oPayBank->Status		= $_POST['status'] != -1 ? $_POST['status'] : 1;
			$oPayBank->AddTime		= date("Y-m-d H:i:s", time());
			
			// 首先检查银行是否已经存在
			!$oPayBank->bankExists() or sysMessage("您已添加过些银行啦！", 1, $aLinks);
			
			if ($oPayBank->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		$GLOBALS['oView']->assign("ur_here",   "增加充值银行");
		$GLOBALS['oView']->assign("apilist",   $oApiList->Data);
		$GLOBALS['oView']->assign("banklist",   $oBankList->Data);
		$oApiList->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_addpaybank.html");
    	EXIT;
	}
	
	
	
	/**
	 * 编辑充值银行，删除，启用，禁用
	 * 
	 * @version 	v1.0	2010-04-09
	 * @author 		louis
	 */
	public function actionEditPayBank(){
		$aLinks = array(
			0 => array(
				'text' => "返回充值银行列表",
				'href' => "?controller=bankmanage&action=paybanklist"
			)
		);
		$iId = $_GET['id'] > 0 ? $_GET['id'] : $_POST['id'];
		if (!is_numeric($iId) || $iId <= 0){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
		
		$oPayBank = new model_withdraw_paybank($iId);
		$_POST['flag'] = isset($_POST['flag']) ? $_POST['flag'] : '';
		// 编辑
		if ($_POST['flag'] == "edit"){
			if (!is_numeric($_POST['seq']) || $_POST['seq'] <= 0 || empty($_POST['bank_code'])){
				sysMessage("您提交的数据有误请核对后重新提交！", 1, $aLinks);
			}
			$oPayBank->BankCode		= $_POST['bank_code'];
			$oPayBank->Seq			= $_POST['seq'];
			$oPayBank->Status		= $_POST['status'] != -1 ? $_POST['status'] : 1;
			if($oPayBank->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败", 1, $aLinks);
		}
		$_GET['flag'] = isset($_GET['flag']) ? $_GET['flag'] : '';
		// 删除
		if ($_GET['flag'] == "del"){
			$oPayBank->Id	= $iId;
			$oPayBank->Status = 2; // 设置为逻辑删除
			if ($oPayBank->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 0, $aLinks);
		}
		// 启用/禁用
		if ($_GET['flag'] == "set"){
			$oPayBank->Id		= $iId;
			$oPayBank->Status 	= intval(1 - $oPayBank->Status);
			if ($oPayBank->save())
				sysMessage("操作成功", 0, $aLinks);
			else 
				sysMessage("操作失败！", 1, $aLinks);
		}
		$GLOBALS['oView']->assign("ur_here",   "编辑提现银行");
		$GLOBALS['oView']->assign("id",   $oPayBank->Id);
		$GLOBALS['oView']->assign("api_name",   $oPayBank->ApiName);
		$GLOBALS['oView']->assign("bank_name",   $oPayBank->BankName);
		$GLOBALS['oView']->assign("bank_code",   $oPayBank->BankCode);
		$GLOBALS['oView']->assign("seq",   $oPayBank->Seq);
		$GLOBALS['oView']->assign("status",   $oPayBank->Status);
		$oPayBank->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_editpaybank.html");
    	EXIT;
	}
	
	
	/**
	 * 指定支付接口下的银行列表
	 * 
	 * @version 	v1.0	2010-05-06
	 * @author 		louis
	 *
	 */
	public function actionPayPortBankList(){
		$aLinks = array(
			0 => array(
				'text' => "返回充值银行列表",
				'href' => "?controller=bankmanage&action=paybanklist"
			)
		);
		if (intval($_GET['id']) <= 0 || !is_numeric($_GET['id'])){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
		
		// 查询对应支付接口名称
		$oApi = new model_deposit_depositinfo();
		$oApi->getPayportData($_GET['id'], 'intro');
		if (empty($oApi->PayportName)){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
		
		$oPayBankList = new model_withdraw_PayBankList();
		$oPayBankList->ApiId = $_GET['id'];
		$oPayBankList->Status = 1;
		$oPayBankList->init();
		
		$GLOBALS['oView']->assign("ur_here",   "充值银行排序");
		$GLOBALS['oView']->assign("id",   $oPayBankList->ApiId);
		$GLOBALS['oView']->assign("banklist",   $oPayBankList->Data);
		$oPayBankList->assignSysInfo();
    	$GLOBALS['oView']->display("bankmanage_payportbanklist.html");
    	EXIT;
	}
	
	
	/**
	 * 设置银行排序
	 * 
	 * @version 	v1.0	2010-05-06
	 * @author 		louis
	 */
	public function actionPayBankSetSeq(){
		if (intval($_POST['id']) <= 0 || !is_numeric($_POST['id']) || empty($_POST['seq']) || empty($_POST['bankid'])){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1);
		}
		
		$oPayBank = new model_withdraw_paybank();
		$oPayBank->ApiId = $_POST['id'];
		$bResult = $oPayBank->setSeq($_POST['seq'], $_POST['bankid']);
		if ($bResult === true){
			sysMessage("操作成功！", 0);
		} else {
			sysMessage("操作失败！", 1);
		}
	}
	
	
	
	
	
	/**
	 * email充值，用户与银行卡关系列表
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-10
	 * @package 	passportadmin
	 * 
	 */
	public function actionUserAndCard(){
		$GLOBALS['oView']->display("bankmanage_center.html");
		EXIT;
	}
	
	
	
	/**
	 * 用户列表 [左框架]
	 * 
	 * URL = ./?controller=bankmanage&action=list
	 * @author		louis
	 * @version 	v1.0
	 * @since 		2010-09-10
	 * @package 	passportadmin
	 * 
	 */
	function actionList()
	{
		$iUserId    = isset($_GET["userid"])&&is_numeric($_GET["userid"])   ? intval($_GET["userid"])    : 0;
		if( $iUserId ==0 )
		{
			$oUser  = new model_user();
			$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
			$oCompanCard = new model_deposit_companycard();
			if( $oUser->checkAdminForUser( $iAdmin, 0 ) )
			{
				$aAgents = $oCompanCard->getChildListID( 0, FALSE, "", "ORDER BY a.username"  );
				if( $aAgents !== FALSE )
				{
					$GLOBALS['oView']->assign( "users", $aAgents );
				}
				$GLOBALS['oView']->display("bankmanage_userlist.html");
				EXIT;
			}
			else 
			{
				$aAgents = $oCompanCard->getChildListID(0, false, " AND a.`userid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdmin."')", "ORDER BY a.username");
				if( $aAgents !== FALSE )
				{
					$GLOBALS['oView']->assign( "users", $aAgents );
				}
				$GLOBALS['oView']->display("bankmanage_userlist.html");
				EXIT;
			}			
		}
		else
		{
			/**
			 * AJAX 处理用户部分
			 */
			$oUser  = new model_user();
			$iAdmin = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
			$oCompanCard = new model_deposit_companycard();
			if(!$oUser->checkAdminForUser( $iAdmin, $iUserId ) )
			{
				EXIT;
			}
			$aUsers = $oCompanCard->getChildListID( $iUserId, FALSE, "", "ORDER BY a.username" );
			foreach($aUsers as $user)
			{
				echo"<div id=\"title_".$user['userid']."\">";
				if($user["childcount"]>0)
				{
					echo"<img src=\"./images/menu_plus.gif\" id=\"img_".$user["userid"]."\" onclick=\"show(".$user["userid"].");\"> ";
					echo"<a onclick=\"javascript:getchild(".$user['userid'].");\">".htmlspecialchars($user["username"])." <font color='#A0A0A0'>(".$user["childcount"].")</font></a>";
					echo"<div id=\"child_".$user['userid']."\" style=\"display:none;\" class=\"child\"></div>";
				}
				else 
				{
					echo"<img src=\"./images/menu_minus.gif\" id=\"img_".$user["userid"]."\"> ";
					echo"<a href=\"?controller=bankmanage&action=view&userid=".$user["userid"]."\" target=\"user_view\">".htmlspecialchars($user["username"])." <font color='#A0A0A0'>(".$user["childcount"].")</font></a>";
				}
				echo"</div>";
			}
			EXIT;
		}
	}
	
	
	
	
	
	
	/**
	 * 分卡时，用户列表，右框架
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-10
	 * @package 	passportadmin
	 * 
	 */
	public function actionView(){
		
		//提取系统中使用的受付银行列表
		$oDeposit		= new model_deposit_depositlist(array(),'','array');
    	$aDepositList 	= $oDeposit->getDepositArray('all');
		$aBankIdArray 	= $aDepositList[0];
		$aBanknameArray = $aDepositList[1];
		$aBankArray 	= $aDepositList[2];
			
		if (  !isset(  $_GET['depositbankid'] )  || !is_numeric($_GET['depositbankid']) 
			|| intval( $_GET['depositbankid'] ) <= 0 
			|| array_search( intval( $_GET['depositbankid']), $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			$GLOBALS['oView']->assign("ur_here",   "选择受付银行");
			$GLOBALS['oView']->assign("controllerstr", 'bankmanage');
			$GLOBALS['oView']->assign("actionstr", 'view');
			$GLOBALS['oView']->assign("useridstr",  intval($_GET['userid']));
			$GLOBALS['oView']->assign("depositbanklist",   $aBankArray);
					$oDeposit->assignSysInfo();
			$GLOBALS['oView']->display("deposit_choosebank.html");
			EXIT;
		}
		$iDepositbankid = intval($_GET['depositbankid']);
		
		$oCompanyCard = new model_deposit_companycard();
		$oCompanyCard->BankId = $iDepositbankid;
		$iUserId = isset($_GET['userid']) ? intval($_GET['userid']) : 0;
		$aResult = array();
		
		$_GET['flag'] = isset($_GET['flag']) ? $_GET['flag'] : "";
		
		$aHtml = array();
		$aSearch = array();
		$aResult = array();
		$aAll = array();
		$iIdentity = 0;
		$aHtml['username'] = $aSearch['username'] = isset($_GET['username']) ? trim($_GET['username']) : "";
		$aHtml['identity'] = $iIdentity = isset($_GET['identity']) ? intval($_GET['identity']) : "";
		$aHtml['card'] = isset($_GET['card']) ? trim($_GET['card']) : "";
		
		
		// 如果没有传入userid,则取出所有总代列表
		if ($iUserId === 0 && $aHtml['username'] == "" && $aHtml['identity'] == "" && $aHtml['card'] == ""){
			$p = isset($_GET['p']) ? intval($_GET['p']) : 0;
			//$pn = 50;
			$pn = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        	$aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 50; 
			$aAll = $oCompanyCard->getRelationList('*', " `bankid`=$iDepositbankid AND `user_level` = 0  AND `user_type` = 1 AND `agentid` = 0", $aHtml['pn'], $p, " ORDER BY `username` ASC");
			$oPager    = new pages( $aAll['affects'], $aHtml['pn'], 10);
			$aResult = $aAll['results'];
			if (!empty($aResult)){
				foreach ($aResult as $k => $v){
					if (intval($v['isblack']) === 1){
						$aResult[$k]['identity'] = 1;
						$aResult[$k]['current_card'] = $v['black_accname'];
					} else if(intval($v['isvip']) === 1 && $oCompanyCard->_differTime($v['vip_expriy']) < 0){
						$aResult[$k]['identity'] = 2;
						$aResult[$k]['current_card'] = $v['vip_accname'];
					} else {
						$aResult[$k]['identity'] = 3;
						$aResult[$k]['current_card'] = $v['accname'];
					}
				}
			}
		} else { // 获取用户直接下级
			if ($iUserId > 0){
				$p = isset($_GET['p']) ? intval($_GET['p']) : 0;
				//$pn = 50;
				$pn = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        		$aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 50; 
				$aAll = $oCompanyCard->getRelationList("*", " `bankid`=$iDepositbankid AND `agentid` = {$iUserId}", $aHtml['pn'], $p, " ORDER BY `username` ASC");
				$oPager    = new pages( $aAll['affects'], $aHtml['pn'], 10);
				$aResult = $aAll['results'];
				if (!empty($aResult)){
					foreach ($aResult as $k => $v){
						if (intval($v['isblack']) === 1){
							$aResult[$k]['identity'] = 1;
							$aResult[$k]['current_card'] = $v['black_accname'];
						} else if(intval($v['isvip']) === 1 && $oCompanyCard->_differTime($v['vip_expriy']) < 0){
							$aResult[$k]['identity'] = 2;
							$aResult[$k]['current_card'] = $v['vip_accname'];
						} else {
							$aResult[$k]['identity'] = 3;
							$aResult[$k]['current_card'] = $v['accname'];
						}
					}
				}
			}
		}
		
		if ($_GET['flag'] == "search"){ // 搜索
			$aSearchResult = array();
			$sWhere = " `bankid`=$iDepositbankid ";
			if (!empty($_GET['username'])){
				$sWhere .= " AND `username` = '{$_GET['username']}'";
			}
			if (intval($_GET['identity']) == 1){ // VIP用户
				$sWhere .= " AND `isvip` = 1 AND `isblack` = 0 AND `vip_expriy` > '" . date("Y-m-d H:i:s", time()) . "'";
			}
			if (intval($_GET['identity']) == 2){ // 黑名单用户
				$sWhere .= "  AND `isblack` = 1 ";
			}
			if (intval($_GET['identity']) == 3){ // 普通用户
				$sWhere .= "  AND `isblack` = 0 AND `isvip` = 0 ";
			}
			if (!empty($_GET['card'])){
				$sWhere .= " AND ((`black_accname` = '{$_GET['card']}' AND `isblack` = 1) OR (`isblack` = 0 AND `isvip` = 1 AND `vip_expriy` > '" . date("Y-m-d H:i:s", time()) . "' AND `vip_accname` = '{$_GET['card']}') OR (`isblack` = 0 AND `isvip` = 0 AND `accname` = '{$_GET['card']}'))";
			}
			$p = isset($_GET['p']) ? intval($_GET['p']) : 0;
			//$pn = 50;
			$pn = isset($_GET['pn']) ? intval($_GET['pn']) : 50;
        	$aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 50; 
			$aSearchResult = $oCompanyCard->getRelationList("*", $sWhere, $aHtml['pn'], $p, " ORDER BY `user_level` ASC,`username` ASC");
			$oPager    = new pages( $aSearchResult['affects'], $aHtml['pn'], 10);
			$aResult = $aSearchResult['results'];
			
			if (!empty($aResult)){
				foreach ($aResult as $k => $v){
					if (intval($v['isblack']) === 1){
						$aResult[$k]['identity'] = 1;
						$aResult[$k]['current_card'] = $v['black_accname'];
					} else if(intval($v['isvip']) === 1 && $oCompanyCard->_differTime($v['vip_expriy']) < 0){
						$aResult[$k]['identity'] = 2;
						$aResult[$k]['current_card'] = $v['vip_accname'];
					} else {
						$aResult[$k]['identity'] = 3;
						$aResult[$k]['current_card'] = $v['accname'];
					}
				}
			}
		}
		
		// 获取可用银行卡
		$aAccount = array();
		$oPayPortList = new model_deposit_depositaccountlist();
		$aAccount = $oPayPortList->multiList($iDepositbankid,true,'ads_payport_id');
		
		$GLOBALS['oView']->assign("ur_here",   "分配银行卡");
		$GLOBALS['oView']->assign("userinfo",   $aResult);
		$GLOBALS['oView']->assign("ahtml",   $aHtml);
		$GLOBALS['oView']->assign("account",   $aAccount);
		$GLOBALS['oView']->assign("pages",   $oPager->show());
		$GLOBALS['oView']->assign("id",   $aResult[0]['userid']);
		$GLOBALS['oView']->assign("depositbanklist",   $aBankArray);
		$GLOBALS['oView']->assign("depositbankid",   $iDepositbankid );
		$GLOBALS['oView']->assign("depositbankname",  $aBanknameArray[$iDepositbankid] );
		$oCompanyCard->assignSysInfo();
		$GLOBALS['oView']->display("bankmanage_view.html");
		EXIT;
	}
	
	
	
	
	/**
	 * 分配银行卡
	 *
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-10
	 * @package 	passportadmin
	 * 
	 */
	public function actionDealCard(){
		$aLinks = array(
			0 => array(
				'text' => "返回分配银行卡列表",
				'href' => "?controller=bankmanage&action=view&depositbankid={$_POST['depositbankid']}"
			)
		);
		$_POST['newcard'] = isset($_POST['newcard']) ? array_filter($_POST['newcard']) : "";
		if (empty($_POST['newcard'])){
			sysMessage("请选择用户！", 1, $aLinks);
		}
		
		// 提取系统中使用的受付银行列表
		$oDeposit     = new model_deposit_depositlist(array(),'','array');
    	$aBankIdArray = $oDeposit->getDepositArray();
		
		if ( !isset($_POST['depositbankid']) || !is_numeric($_POST['depositbankid']) 
			|| intval($_POST['depositbankid']) <= 0 
			|| array_search( intval( $_POST['depositbankid']), $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			sysMessage("请选择受付银行", 1, $aLinks);
			EXIT;
		}
		$iDepositbankid = intval($_POST['depositbankid']);
		
		$aAccount = array();
		$aResult = array();
		$aTempAccount =array();
		$oPayPortList = new model_deposit_depositaccountlist();
		$aResult = $oPayPortList->multiList($iDepositbankid, false,'ads_payport_id');
		//		$aResult = $oPayPortList->singleList('mdeposit', false, 'ads_payport_name');
		if (!empty($aResult)){
			foreach ($aResult as $key => $val){
				$aTempAccount[$val['aid']] = $val;
			}
		}
		
		
		
		$aUserid = array();
		$sUserId = "";
		$aUserInfo = array();
		$aUserid = array_keys($_POST['newcard']);
		$sUserId = implode(',', $aUserid);
		// 组合数据
		$oCompanyCard = new model_deposit_companycard();
		$oCompanyCard->BankId = $iDepositbankid;
		$aUserInfo = $oCompanyCard->getRelationList("*", " `userid` IN ({$sUserId}) ", count($aUserid));
		
		
		// 循环用户当前分配银行卡信息数组
		$aInsert = array();
		$sNow = date("Y-m-d H:i:s");
		foreach ($aUserInfo['results'] as $k => $v){
			$aInsert[$k]['depositbankid'] = $iDepositbankid;
			
			if (intval($v['isblack']) === 1 ){
				$aInsert[$k]['logo'] = "finblack";
				$aInsert[$k]['userid'] = $v['userid'];
				
				$aInsert[$k]['black_accname'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_name'];
				$aInsert[$k]['black_deposit_name'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_ident'];
				$aInsert[$k]['black_deposit_mail'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_mail'];
				$aInsert[$k]['black_payacc_id'] = $_POST['newcard'][$v['userid']];
			}elseif (intval($v['isvip']) === 1 && $v['vip_expriy'] > $sNow &&  intval($v['isblack']) === 0){
				$aInsert[$k]['logo'] = "finvip";
				$aInsert[$k]['userid'] = $v['userid'];
				$aInsert[$k]['vip_accname'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_name'];
				$aInsert[$k]['vip_deposit_name'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_ident'];
				$aInsert[$k]['vip_deposit_mail'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_mail'];
				$aInsert[$k]['vip_payacc_id'] = $_POST['newcard'][$v['userid']];
			} else {
				$aInsert[$k]['logo'] = "finnormal";
				$aInsert[$k]['userid'] = $v['userid'];
				$aInsert[$k]['accname'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_name'];
				$aInsert[$k]['deposit_name'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_ident'];
				$aInsert[$k]['deposit_mail'] = $aTempAccount[$_POST['newcard'][$v['userid']]]['acc_mail'];
				$aInsert[$k]['payacc_id'] = $_POST['newcard'][$v['userid']];
			}
		}
		
		
		if (!empty($aInsert)){
			$bResult = $oCompanyCard->add('', $aInsert);
			if ($bResult === true){
				sysMessage("操作成功！", 0, $aLinks);
			} else {
				sysMessage("操作失败！", 1, $aLinks);
			}
		}
	}
	
	
	
	
	
	/**
	 * 修改用户卡状态，启用或禁用
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-09-13
	 * @package 	passportadmin
	 * 
	 */
	public function actionUpdateStatus(){
		$aLinks = array(
			0 => array(
				'text' => "返回分配银行卡列表",
				'href' => "?controller=bankmanage&action=view&depositbankid={$_REQUEST['depositbankid']}"
			)
		);
		
		// 提取系统中使用的受付银行卡列表
		$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$oDeposit     = new model_deposit_depositlist(array(),'','array');
    	$aBankIdArray = $oDeposit->getDepositArray();
		
		if ( !is_numeric($iDepositbankid) || intval($iDepositbankid) <= 0 
			|| array_search( $iDepositbankid, $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			sysMessage("请选择受付银行", 1, $aLinks);
			EXIT;
		}
		
		
		// 数据检查
		$aUser = array();
		$sFlag = "";
		$_POST['user'] = isset($_POST['user']) ? $_POST['user'] : "";
		if (!empty($_POST['user'])){
			$aUser = $_POST['user'];
		} elseif(intval($_GET['userid']) > 0){
			$aUser[0] = $_GET['userid'];
		}
		$sFlag = isset($_POST['flag']) ? trim($_POST['flag']) : $_GET['flag'];
		if (empty($aUser)){
			sysMessage("请选择用户！", 1, $aLinks);
		}
		
		$aResult = array();
		$oCompanyCard = new model_deposit_companycard();
		foreach ($aUser as $k => $v){
			$aTemp = array();
			$oCompanyCard->UserId = $v;
			$oCompanyCard->BankId = $iDepositbankid;
			$aTemp = $oCompanyCard->getCard('get');
			$aResult[$k]['userid'] = $aTemp['userid'];
			$aResult[$k]['deposit_name'] = $aTemp['deposit_name'];
		}
		if (empty($aResult)){
			sysMessage("操作失败！", 1, $aLinks);
		} else {
			if ($sFlag == "senable"){
				$bResult = $oCompanyCard->enable($aResult);
			} elseif ($sFlag == "sdisable"){
				$bResult = $oCompanyCard->disable($aResult);
			}
		}

		if ($bResult === true){
			sysMessage("操作成功！", 0, $aLinks);
		} else {
			sysMessage("操作失败！", 1, $aLinks);
		}
	}
    
    
    
    /**
     * 批量替换卡前的检查
     * 
     * @author      louis
     * @version     v1.0
     * @since       2011-01-18
     * 
     * @return      array
     */
    public function actionInitInstead(){
        $Id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $iBankId = isset($_POST['bankid']) ? intval($_POST['bankid']) : 0;
        // 数据检查
        if ($Id <= 0 || $iBankId <= 0){ // 数据错误
            echo -1;
        }
        $aResult = array();
        $oCompanyCard = new model_deposit_companycard();
        $oCompanyCard->BankId = $iBankId;
        $oCompanyCard->PayAccId = $Id;
        
        $aResult = $oCompanyCard->initInsteadCard();
        
        echo $aResult === false ? -1 : json_encode($aResult);
    }
    
    
    /**
     * 批量替换银行卡
     * 
     * @author      louis
     * @version     v1.0
     * @since       2011-01-21
     * 
     */
    public function actionBatchChangeCard(){
        $aLinks = array(
			0 => array(
				'text' => "返回分配银行卡列表",
				'href' => "?controller=bankmanage&action=view&depositbankid={$_REQUEST['depositbankid']}"
			)
		);
        // 数据检查
        $iOldCard = isset($_POST['oldcard']) ? intval($_POST['oldcard']) : 0;
        $iNewCard = isset($_POST['newinsteadcard']) ? intval($_POST['newinsteadcard']) : 0;
        $iDepositBankId = isset($_POST['depositbankid']) ? intval($_POST['depositbankid']) : 0;
        
        if ($iOldCard <= 0 || $iNewCard <= 0 || $iDepositBankId <= 0){
            sysMessage("您提交的数据有误！", 1, $aLinks);
        }
        
        $oCompanyCard = new model_deposit_companycard();
        $mResult = $oCompanyCard->batchChangeCard($iOldCard, $iNewCard, $iDepositBankId);
        
        if ($mResult === true){
            sysMessage("操作成功！", 0, $aLinks);
        } else if ($mResult === -1){
            sysMessage("出现异常，请联系管理员！", 1, $aLinks);
        } else {
            sysMessage("操作失败！", 1, $aLinks);
        }
    }
    
    /**
     * 账号反查，难过用户绑定的银行卡，查询用户信息
     * 
     * @author      louis
     * @version     v1.0
     * @since       2010-12-21
     * @package     passportadmin
     * 
     */
    public function actionGetUserByCard(){
        $oUserBank = new model_withdraw_UserBank();
        if ($_GET['flag'] == "search"){
            $aHtml = array();
            $aHtml['account'] = isset($_GET['account']) ? $_GET['account'] : "";
            // 数据检查
            if (empty($_GET['account'])){
                sysMessage("请填写银行账号！", 1, $aLinks);
                exit;
            }
            
            $oUserBank->Account = $_GET['account'];
            $aResult = $oUserBank->getUserByCard();
            $GLOBALS['oView']->assign("ur_here",   "账号反查");
            $GLOBALS['oView']->assign("ahtml",   $aHtml);
            $GLOBALS['oView']->assign("userlist",   $aResult);
            $oUserBank->assignSysInfo();
            $GLOBALS['oView']->display("bankmanage_getuserbycard.html");
            EXIT;
        }
        $GLOBALS['oView']->assign("ur_here",   "账号反查");
		$oUserBank->assignSysInfo();
		$GLOBALS['oView']->display("bankmanage_getuserbycard.html");
		EXIT;
    }
}