<?php
/**
 * 文件：/passportadmin/_app/controller/vmmanage.php
 * 功能：虚拟机管理
 *
 * 功能：
 * --actionVmList							虚拟机管理列表
 * --actionAddVm							增加虚拟机信息
 * --actionEdit								修改虚拟机信息
 * --actionDelete							删除指定记录
 * 
 * 
 * @author 		louis
 * @version 	v1.0
 * @since 		2010-09-16
 * @package 	passportadmin
 * 
 */
class controller_vmmanage extends basecontroller{
	
	/**
	 * 虚拟机管理列表
	 *
	 * @author 			louis
	 * @version 		v1.0
	 * @since 			2010-09-16
	 * @package 		passportadmin
	 * 
	 */
	public function actionVmList(){
		
		//提取系统中使用的受付银行列表
		$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$oDeposit     = new model_deposit_depositlist(array(),'','array');
    	$aDepositList = $oDeposit->getDepositArray('all');
    	$aBankIdArray = $aDepositList[0];
    	$aBanknameArr = $aDepositList[1];
    	$aBankArray	  = $aDepositList[2];

		if ( !is_numeric($iDepositbankid) || intval($iDepositbankid) <= 0 
			|| array_search( $iDepositbankid, $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			$GLOBALS['oView']->assign("ur_here",   "选择银行");
			$GLOBALS['oView']->assign("controllerstr",   'vmmanage');
			$GLOBALS['oView']->assign("actionstr",   'vmlist');
			$GLOBALS['oView']->assign("depositbanklist",   $aBankArray);
					$oDeposit->assignSysInfo();
			$GLOBALS['oView']->display("deposit_choosebank.html");
			EXIT;
		}
		
				
		$oVmManage = new model_vmmanage();
		
		$aResult = array();
		$aResult = $oVmManage->getList($iDepositbankid);
		if ( $aResult === false )
		{
			echo 'vmmanage::GetList() 未能获取数据';
			unset($oVmManage,$oDeposit);
			exit;
		}
		// 查询财务用卡名
		if (!empty($aResult)){
			foreach ($aResult as $k => $v){
				$oPayPortInfo = new model_deposit_depositaccountinfo($v['card_id']);
				$oPayPortInfo->GetType = true;
				$oPayPortInfo->getAccountDataObj();
				$aResult[$k]['nickname'] = $oPayPortInfo->AccName; // 平台充值信息中的财务使用别名
			}
		}
		
	
		$GLOBALS['oView']->assign( 'ur_here', '虚拟机管理列表 '.$aBanknameArr[$iDepositbankid] );
		$GLOBALS['oView']->assign( 'actionlink',   array( 'href'=>url("vmmanage","addvm", array('depositbankid'=>$iDepositbankid) ), 'text'=>'增加虚拟机信息' ) );
		$GLOBALS['oView']->assign( 'vminfo', $aResult );
		$GLOBALS['oView']->assign( "depositbanklist",   $aBankArray);
    	$GLOBALS['oView']->assign( "depositbankid",   $iDepositbankid);
    	$GLOBALS['oView']->assign( "depositbankname",  $aBanknameArr[$iDepositbankid] );
	    $oVmManage->assignSysInfo();
	    $GLOBALS['oView']->display("vmmanage_list.html");
	    EXIT;
    }
    
    
    
    
    
    /**
     * 增加虚拟机信息
     *
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-09-16
     * @package 	passportadmin
     * 
     */
    public function actionAddVm(){
    	
    	$aLinks = array(
			0 => array(
				'text' => '返回虚拟机管理列表',
				'href' => '?controller=vmmanage&action=vmlist&depositbankid='.$_REQUEST['depositbankid']
			)
		);
    	$oVmManage = new model_vmmanage();
    	
    	$sFlag = isset($_POST['flag']) ? $_POST['flag'] : "";
    	
    	if ($sFlag == "vm"){ // 添加
    		// 数据检查
    		if (empty($_POST['alias_name']) || empty($_POST['ip']) || !is_numeric($_POST['card_id']) 
    			|| $_POST['card_id'] <= 0 || !is_numeric($_POST['status']) || empty($_POST['vmip']))
    		{
    			sysMessage("您提交的数据有误", 1, $aLinks);
    		}
    		
    		$oVmManage->AliasNam 	= mysql_escape_string( $_POST['alias_name'] );
    		$oVmManage->VmIP 		= mysql_escape_string( $_POST['vmip'] );
    		$oVmManage->VpnIp 		= mysql_escape_string( $_POST['ip'] );
    		$oVmManage->AccId 		= intval( $_POST['card_id'] );
    		$oVmManage->BankId 		= intval( $_POST['bank_id'] );
    		$oVmManage->IsRunning 	= intval( $_POST['status'] );
    		
    		// 首先查询是否已绑定过此银行卡
    		if ( intval($_POST['status']) === 1){
    			$bResult = $oVmManage->isExists();
	    		if ($bResult === true){
	    			sysMessage("您已绑定过此银行卡！", 1, $aLinks);
	    		}
    		}
    		
    		$mResult = $oVmManage->Insert();
    		if ($mResult > 0){
    			sysMessage("添加成功！", 0, $aLinks);
    		} else {
    			sysMessage("添加失败！", 1, $aLinks);
    		}
    	}
    	
    	$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$oDeposit     = new model_deposit_depositlist(array(),'','array');
    	$aDepositList = $oDeposit->getDepositArray('all');
    	$aBankIdArray = $aDepositList[0];
//    	$aBanknameArr = $aDepositList[1];
    	$aBankArray	  = $aDepositList[2];

		if ( !is_numeric($iDepositbankid) || intval($iDepositbankid) <= 0 
			|| array_search( $iDepositbankid, $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			sysMessage( '银行ID错误',1, $aLinks);
			EXIT;
		}
		
    	
    	// 获取所有email充值分账户
    	$aAccount = array();
		$oPayPortList = new model_deposit_depositaccountlist();
		$aAccount = $oPayPortList->singleList($iDepositbankid, false, 'ads_payport_id');
    	
    	$GLOBALS['oView']->assign( 'ur_here', '增加虚拟机信息' );
    	$GLOBALS['oView']->assign( 'actionlink',   array( 'href'=>url("vmmanage","vmlist", array('depositbankid'=>$iDepositbankid) ), 'text'=>'虚拟机列表' ) );
		$GLOBALS['oView']->assign( 'acclist', $aAccount );
    	$GLOBALS['oView']->assign( "depositbanklist",   $aBankArray);
    	$GLOBALS['oView']->assign( "depositbankid",   $iDepositbankid);
	    $oVmManage->assignSysInfo();
	    $GLOBALS['oView']->display("vmmanage_add.html");
	    EXIT;
    }
    
    
    
    
    
    
    
    /**
     * 修改虚拟机信息
     *
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-09-16
     * @package 	passportadmin
     * 
     */
    public function actionEdit(){
    	$aLinks = array(
			0 => array(
				'text' => '返回虚拟机管理列表',
				'href' => '?controller=vmmanage&action=vmlist&depositbankid='.$_REQUEST['depositbankid']
			)
		);
		
    	$iDepositbankid = intval($_REQUEST['depositbankid']);
		
		$oDeposit     = new model_deposit_depositlist(array(),'','array');
    	$aDepositList = $oDeposit->getDepositArray('all');
    	$aBankIdArray = $aDepositList[0];
		$aBanknameArr = $aDepositList[1];
    	$aBankArray	  = $aDepositList[2];

		if ( !is_numeric($iDepositbankid) || intval($iDepositbankid) <= 0 
			|| array_search( $iDepositbankid, $aBankIdArray) === FALSE )
		{
			//单独显示银行选择页面
			sysMessage( '银行ID错误', 1, $aLinks);
			EXIT;
		}
		
		$iId = isset($_GET['id']) ? intval($_GET['id']) : 0;
		
    	// 数据检查
    	if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
    		sysMessage("您提交的数据有误", 1, $aLinks);
    	}
    	
    	$oVmManage = new model_vmmanage();
    	
    	$sFlag = isset($_POST['flag']) ? $_POST['flag'] : "";
    	if ($_POST['flag'] == "edit"){
    		if (empty($_POST['alias_name']) || empty($_POST['ip']) || !is_numeric($_POST['card_id']) || $_POST['card_id'] <= 0 || !is_numeric($_POST['status']) || empty($_POST['vmip'])){
	    		sysMessage("您提交的数据有误", 1, $aLinks);
	    	}
	    	$oVmManage->Id 			= $iId;
	    	$oVmManage->AliasNam 	= mysql_escape_string($_POST['alias_name']);
	    	$oVmManage->VmIP 		= mysql_escape_string($_POST['vmip']);
			$oVmManage->VpnIp 		= mysql_escape_string($_POST['ip']);
			$oVmManage->AccId 		= intval($_POST['card_id']);
			$oVmManage->BankId 		= intval($_POST['bank_id']);
			$oVmManage->IsRunning 	= intval($_POST['status']);
			
			// 首先查询要提交的信息是否已存在
			if (intval($_POST['status']) === 1){
				$oVmManage->UnSelf = true;
				$bResult = $oVmManage->isExists();
				
				if ($bResult === true){
					sysMessage("您已绑定过此银行卡！", 1, $aLinks);
				}
			}
			
			$mResult = $oVmManage->edit();
			
			if ($mResult === true){
				sysMessage("修改成功！", 0, $aLinks);
			} else {
				sysMessage("修改失败！", 1, $aLinks);
			}
    	}
    	
    	// 获取指定记录信息
    	$aResult = array();
    	$oVmManage->Id = $iId;
    	$aResult = $oVmManage->getOne();
    	if (empty($aResult)){
    		sysMessage("您访问的数据不存在！", 1, $aLinks);
    	}
    	
    	// 获取所有email充值分账户
    	$aAccount = array();
		$oPayPortList = new model_deposit_depositaccountlist();
		$aAccount = $oPayPortList->singleList($iDepositbankid, false, 'ads_payport_id');
    	
    	$GLOBALS['oView']->assign( 'ur_here', '增加虚拟机信息' );
    	$GLOBALS['oView']->assign( 'actionlink',   array( 'href'=>url("vmmanage","vmlist", array('depositbankid'=>$iDepositbankid) ), 'text'=>'虚拟机列表' ) );
		
    	$GLOBALS['oView']->assign( 'acclist', $aAccount );
    	$GLOBALS['oView']->assign( 'vm', $aResult );
    	$GLOBALS['oView']->assign("depositbanklist",   $aBankArray);
    	$GLOBALS['oView']->assign("depositbankid",   $iDepositbankid);
	    $oVmManage->assignSysInfo();
	    $GLOBALS['oView']->display("vmmanage_edit.html");
	    EXIT;
    }
    
    
    
    
    
    /**
     * 删除指定记录
     *
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-09-16
     * @package 	passportadmin
     * 
     */
    public function actionDelete(){
    	$aLinks = array(
			0 => array(
				'text' => '返回虚拟机管理列表',
				'href' => '?controller=vmmanage&action=vmlist&depositbankid='.$_REQUEST['depositbankid']
			)
		);
		
		$iId = isset($_GET['id']) ? $_GET['id'] : 0;
    	// 数据检查
    	if (!is_numeric($_GET['id']) || $_GET['id'] <= 0){
    		sysMessage("您提交的数据有误", 1, $aLinks);
    	}
    	
    	$oVmManage = new model_vmmanage();
    	
    	$oVmManage->Id = $iId;
    	$bResult = $oVmManage->delete();
    	if ($bResult === true){
    		sysMessage("删除成功！", 0, $aLinks);
    	} else {
    		sysMessage("删除失败！", 1, $aLinks);
    	}
    }
}