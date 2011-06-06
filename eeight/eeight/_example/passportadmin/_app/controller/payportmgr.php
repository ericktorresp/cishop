<?php
/**
 * 文件 : /_app/controller/payportmgr.php
 * 功能 : 控制器 - 支付接口配置
 * 
 * 
 * @package	passportadmin
 * @version 0.1 3/10/2010
 * @author	Jim
 */
class controller_payportmgr extends basecontroller 
{
	
    	
    /**
     * 查看接口列表
     * URL = ./?controller=payportmgr&action=list
     */
    public function actionList()
    {
    	
    	/**
		 * 自定义函数,  整理数值中的 ".0000 .00"
		 */
    	function cutAfterZero(&$str,$key){
    		if (is_numeric($str)){
   	 			$str = str_replace('.00', '', number_format($str,2,'.',''));
    		}else{
    			return $str;
    		}
    	}
    	
    	$oPayport     = new model_pay_payportlist(array(),'','array');
    	$aPayportList = $oPayport->Data;
    	
    	foreach ($aPayportList AS &$aPL){
    		//获取配属权限各值
    		$aPL['payport_attr_load']		= ($aPL['payport_attr'] & 1);
    		$aPL['payport_attr_draw']		= ($aPL['payport_attr'] & 2);
    		$aPL['payport_attr_drawlist'] 	= ($aPL['payport_attr'] & 4);
    		$aPL['payport_attr_ques'] 	  	= ($aPL['payport_attr'] & 8);
    		$aPL['payport_attr_drawhand'] 	= ($aPL['payport_attr'] & 16);
    		array_walk($aPL, 'cutAfterZero');
    		$aPL['load_fee_percent_up']		*= 100;
    		$aPL['load_fee_percent_down'] 	*= 100;
    		$aPL['draw_fee_percent_up'] 	*= 100;
    		$aPL['draw_fee_percent_down'] 	*= 100;

    	}

    	$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("payportmgr","add"), 'text'=>'增加支付接口' ) );
    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    	$GLOBALS['oView']->assign( "ur_here", "支付接口列表");
    	$GLOBALS['oView']->assign( "PayportList", $aPayportList);
    			$oPayport->assignSysInfo();
    	$GLOBALS['oView']->display("payport_list.html");
    	exit;
    }
	
    
    /**
     * 查看单个接口明细
     * URL = ./?controller=payportmgr&action=detail
     */
    public function actionDetail()
    {
    	$aLocat = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    	
    	$iPayportId = (isset($_GET["id"]) && is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
		$oPayport   = new model_pay_payportinfo();
		$oPayport->getPayportData($iPayportId,'','-1',true);
		
		$aPayportInfo = stripslashes_deep($oPayport->getArrayData());
		
    	if ( empty($aPayportInfo['payport_name']) ){
				sysMessage('失败:数据读取错误',1,$aLocat);
				unset($oPayport);
				exit;
		}
		if ( !isset($aPayportInfo['draw_fee_percent']) ) $aPayportInfo['draw_fee_percent'] = 0;
    	$aPayportInfo['draw_fee_percent'] = $aPayportInfo['draw_fee_percent']*100;
    	$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("payportmgr","add"), 'text'=>'增加支付接口' ) );
    	$GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    	$GLOBALS['oView']->assign( "ur_here", "支付接口详情");
    	$GLOBALS['oView']->assign( "PayportDetail", $aPayportInfo);
    			$oPayport->assignSysInfo();
    	$GLOBALS['oView']->display("payport_detail.html");
    	exit;
    }
	
    
	/**
     * 修改接口基本信息
     * URL = ./?controller=payportmgr&action=edit
     */
    public function actionEdit()
    {
    	//获取休市时间，只许可在休市时间修改;
    	$aLocat = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
		$oConfig = new model_config();
		$iPayportEdit = $oConfig->getConfigs('payport_edit');
		if ($iPayportEdit > 0){	
			$sResetTime = $oConfig->getConfigs('xiushishijian');
			$aResetTime = explode('-',$sResetTime);
			// 24小时值，无前导0
			$sNow = date('G:i');
			if ( ($sNow > $aResetTime[1]) || ($sNow < $aResetTime[0]) ){
				unset($oConfig);
				sysMessage('非休市时间,禁用编辑',1,$aLocat);
				exit;
			}
		}
		$sFlag =  isset($_POST['flag']) ? trim($_POST['flag']) : false;
    	if ($sFlag == false){
    		$iPayportId = (isset($_GET["id"]) && is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
			$oPayport   = new model_pay_payportinfo();
			$oPayport->getPayportData($iPayportId,'','-1',true);
			$aPayportInfo = stripslashes_deep($oPayport->getArrayData());
			
			if ( empty($aPayportInfo['payport_name']) ){
				sysMessage('失败:数据读取错误',1,$aLocat);
				unset($oPayport,$oConfig);
				exit;
			}
			
			require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
	    	$editor = new FCKeditor( 'payport_intro' );
        	$editor->BasePath   = './js/fckeditor/';
        	$editor->Width      = '100%';
        	$editor->Height     = '420';
        	$editor->Value      = $aPayportInfo['payport_intro'];
        	$FCKeditor = $editor->CreateHtml();
        	$GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        
	    	$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("payportmgr","add"), 'text'=>'增加支付接口' ) );
    		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    		$GLOBALS['oView']->assign( "ur_here", "编辑支付接口");
    		$GLOBALS['oView']->assign( "PayportDetail", $aPayportInfo);
    		$GLOBALS['oView']->assign( "action", 'edit');
    		$GLOBALS['oView']->assign( "flag", 'update');
    				$oPayport->assignSysInfo();
    		$GLOBALS['oView']->display("payport_edit.html");
    		exit;
    	}
    	elseif ($sFlag == 'update'){
    		//整理数据 mysql_real_escape_string
    		$iPayportId = (isset($_POST["id"]) && is_numeric($_POST["id"])) ? intval($_POST["id"]) : 0;
			$oPayport   = new model_pay_payportinfo();
			
				if ( !isset($_POST['payport_attr_load']) ) 		$_POST['payport_attr_load'] = 0;
    			if ( !isset($_POST['payport_attr_draw']) ) 		$_POST['payport_attr_draw'] = 0;
    			if ( !isset($_POST['payport_attr_ques']) ) 		$_POST['payport_attr_ques'] = 0;
    			if ( !isset($_POST['payport_attr_drawlist']) ) 	$_POST['payport_attr_drawlist'] = 0;
    			if ( !isset($_POST['payport_attr_drawhand']) ) 	$_POST['payport_attr_drawhand'] = 0;
    			if ( !isset($_POST['status']) ) 				$_POST['status'] = 0;
    			
				$oPayport->Id 				= $iPayportId;
    			$oPayport->PayportName 		= daddslashes($_POST['payport_name']);
				$oPayport->PayportNickname 	= daddslashes($_POST['payport_nickname']);
				$oPayport->PayportHost 		= daddslashes($_POST['payport_host']);
				$oPayport->PayportUrlLoad 	= daddslashes($_POST['payport_url_load']);
				$oPayport->PayportUrlDraw 	= daddslashes($_POST['payport_url_draw']);
				$oPayport->PayportUrlQues 	= daddslashes($_POST['payport_url_ques']);
				$oPayport->ReceiveHost 		= daddslashes($_POST['receive_host']);
				$oPayport->ReceiveUrl 		= daddslashes($_POST['receive_url']);
				$oPayport->ReceiveUrlKeep 	= daddslashes($_POST['receive_url_keep']);
				$oPayport->Status 			= daddslashes($_POST['status']);
				$oPayport->PayportIntro 	= daddslashes($_POST['payport_intro']);
				$oPayport->LangCode 		= daddslashes($_POST['lang_code']);
				$oPayport->PayportAttr 		= intval($_POST['payport_attr_load'] + $_POST['payport_attr_draw'] + $_POST['payport_attr_ques'] + $_POST['payport_attr_drawlist'] + $_POST['payport_attr_drawhand']);
				
				$aLocation  = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
				
				//各项填写限制
				if ( ($_POST['payport_attr_drawhand'] > 0) && ( ($_POST['payport_attr_draw'] > 0) || ($_POST['payport_attr_drawlist'] > 0) ) ) {
					unset($oPayport,$iPayportId);
					sysMessage('不支持这样矛盾的选择',1,$aLocation);
					exit;
				}
				
				if ($oPayport->set()){
    				//保存 修改结果 自动禁用该接口
					if($oPayport->disable()){
    					sysMessage('成功,编辑接口参数',0,$aLocation);
    				}else{
    					sysMessage('成功:编辑接口参数,失败:禁用该接口',1,$aLocation);
    				}
				}else{
					sysMessage('保存失败',1,$aLocation);
				}
    	}
    	else{
    		sysMessage('What‘s your want?');
    	}
    }
    
    /**
     * 修改充提参数信息
     *
     */
	public function actionSetLimit()
    {
    	//获取休市时间，只许可在休市时间修改;
    	$aLocat = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
		$oConfig = new model_config();
		$iPayportEdit = $oConfig->getConfigs('payport_edit');
		if ($iPayportEdit > 0){	
			$sResetTime = $oConfig->getConfigs('xiushishijian');
			$aResetTime = explode('-',$sResetTime);
			// 24小时值，无前导0
			$sNow 		= date('G:i');
			if ( ($sNow > $aResetTime[1]) || ($sNow < $aResetTime[0]) ){
				sysMessage('非休市时间,禁用编辑',1,$aLocat);
				exit;
			}
		}
		$sFlag =  isset($_POST['flag']) ? trim($_POST['flag']) : false;
		
    	if ($sFlag == false){
    		$iPayportId 	= (isset($_GET["id"]) && is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
			$oPayport   	= new model_pay_payportinfo();
			$oPayport->getPayportData($iPayportId,'','-1',true);
			$aPayportInfo 	= stripslashes_deep( $oPayport->getArrayData() );
			
    		if ( empty($oPayport->Id) ){
				sysMessage('失败:数据读取错误',0,$aLocat);
				unset($oPayport,$oConfig);
				exit;
			}
			
	    	$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("payportmgr","add"), 'text'=>'增加支付接口' ) );
    		$GLOBALS['oView']->assign( 'actionlink',  array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    		$GLOBALS['oView']->assign( "ur_here", "编辑支付接口充提参数");
    		$GLOBALS['oView']->assign( "PayportDetail", $aPayportInfo);
    		$GLOBALS['oView']->assign( "action", 'setlimit');
    		$GLOBALS['oView']->assign( "flag", 'update');
    				$oPayport->assignSysInfo();
    		$GLOBALS['oView']->display("payport_edit_limit.html");
    		exit;
    	}
    	elseif ($sFlag == 'update'){
    		//整理数据
    		$iPayportId = (isset($_POST["id"]) && is_numeric($_POST["id"])) ? intval($_POST["id"]) : 0;
			$oPayport   = new model_pay_payportinfo();
				$oPayport->Id 				= $iPayportId;
				$oPayport->Currency 		= daddslashes( strtoupper($_POST['currency']) );
				$oPayport->LoadTimeNote 	= daddslashes( $_POST['load_time_note'] );
				$oPayport->DrawTimeNote 	= daddslashes( $_POST['draw_time_note'] );
    			$oPayport->LoadLimitMinPer 	= daddslashes( str_replace(',','',$_POST['load_limit_min_per']) );
				$oPayport->LoadLimitMaxPer 	= daddslashes( str_replace(',','',$_POST['load_limit_max_per']) );
				$oPayport->LoadFeePerDown 	= daddslashes( str_replace(',','',$_POST['load_fee_per_down']) );
				$oPayport->LoadFeePercentDown = daddslashes( $_POST['load_fee_percent_down'] );
				$oPayport->LoadFeeStep 		= daddslashes( str_replace(',','',$_POST['load_fee_step']) );
				$oPayport->LoadFeePerUp 	= daddslashes( str_replace(',','',$_POST['load_fee_per_up']) );
				$oPayport->LoadFeePercentUp = daddslashes( $_POST['load_fee_percent_up'] );
				$oPayport->DrawLimitMinPer 	= daddslashes( str_replace(',','',$_POST['draw_limit_min_per']) );
				$oPayport->DrawLimitMaxPer 	= daddslashes( str_replace(',','',$_POST['draw_limit_max_per']) );
				$oPayport->DrawFeePerDown 	= daddslashes( str_replace(',','',$_POST['draw_fee_per_down']) );
				$oPayport->DrawFeePercentDown = daddslashes( $_POST['draw_fee_percent_down'] );
				$oPayport->DrawFeeMin 		= daddslashes( str_replace(',','',$_POST['draw_fee_min']) );
				$oPayport->DrawFeeMax 		= daddslashes( str_replace(',','',$_POST['draw_fee_max']) );
				$oPayport->DrawFeeStep 		= daddslashes( str_replace(',','',$_POST['draw_fee_step']) );
				$oPayport->DrawFeePerUp 	= daddslashes( str_replace(',','',$_POST['draw_fee_per_up']) );
				$oPayport->DrawFeePercentUp = daddslashes( $_POST['draw_fee_percent_up'] );
				$oPayport->PlatLoadPercent 	= daddslashes( $_POST['plat_load_percent'] );
				$oPayport->PlatLoadMin 		=  daddslashes( str_replace(',','',$_POST['plat_load_min']) );
				$oPayport->PlatLoadMax 		=  daddslashes( str_replace(',','',$_POST['plat_load_max']) );
				$oPayport->PlatDrawPercent 	= daddslashes( $_POST['plat_draw_percent'] );
				$oPayport->PlatDrawMin 		=  daddslashes( str_replace(',','',$_POST['plat_draw_min']) );
				$oPayport->PlatDrawMax 		=  daddslashes( str_replace(',','',$_POST['plat_draw_max']) );
				$oPayport->OptLimitTimes 	= intval($_POST['opt_limit_times']);
				
				$aLocation  = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
				if ($oPayport->setlimit()){
    				//保存 修改结果
    			 	 sysMessage('成功,编辑充提参数',0,$aLocation);
				}else{
					sysMessage('保存失败',1,$aLocation);
				}
    	}
    	else{
    		sysMessage('What‘s your want?');
    	}
    }
    
    
	/**
     * 增加单个接口
     * URL = ./?controller=payportmgr&action=add
     */
    public function actionAdd()
    {
    	$sFlag =  isset($_POST['flag']) ? trim($_POST['flag']) : false;
    	if($sFlag == false){
    		//显示界面
    		require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
	    	$editor = new FCKeditor( 'payport_intro' );
        	$editor->BasePath   = './js/fckeditor/';
        	$editor->Width      = '100%';
        	$editor->Height     = '420';
        	$editor->Value      = '';
        	$FCKeditor = $editor->CreateHtml();
        	$GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
    		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    		$GLOBALS['oView']->assign( "ur_here", "新增支付接口");
			$GLOBALS['oView']->assign( "action", 'add');
			$GLOBALS['oView']->assign( "flag", 'save');
    		$GLOBALS['oView']->display("payport_edit.html");
    		exit;
    	}
    	elseif ($sFlag == 'save'){
    		$oPayport   = new model_pay_payportinfo();
    
    		if ( !isset($_POST['payport_attr_load']) ) 		$_POST['payport_attr_load'] = 0;
    		if ( !isset($_POST['payport_attr_draw']) ) 		$_POST['payport_attr_draw'] = 0;
    		if ( !isset($_POST['payport_attr_ques']) ) 		$_POST['payport_attr_ques'] = 0;
    		if ( !isset($_POST['payport_attr_drawlist']) ) 	$_POST['payport_attr_drawlist'] = 0;
    		if ( !isset($_POST['payport_attr_drawhand']) ) 	$_POST['payport_attr_drawhand'] = 0;
    		
			$iPayport_attr = $_POST['payport_attr_load'] + $_POST['payport_attr_draw'] + $_POST['payport_attr_ques'] + $_POST['payport_attr_drawlist'] + $_POST['payport_attr_drawhand'];
	    		//保存数据
    			$oPayport->PayportName 		= daddslashes( $_POST['payport_name'] );
				$oPayport->PayportNickname 	= daddslashes( $_POST['payport_nickname'] );
				$oPayport->Currency 		= daddslashes( strtoupper($_POST['currency']) );
				$oPayport->LoadTimeNote 	= daddslashes( $_POST['load_time_note'] );
				$oPayport->DrawTimeNote 	= daddslashes( $_POST['draw_time_note'] );
				$oPayport->LoadLimitMinPer	= daddslashes( $_POST['load_limit_min_per'] );
				$oPayport->LoadLimitMaxPer 	= daddslashes( $_POST['load_limit_max_per'] );
				$oPayport->LoadFeePerUp 	= daddslashes( $_POST['load_fee_per_up'] );
				$oPayport->LoadFeePercentUp = daddslashes( $_POST['load_fee_percent_up'] );
				$oPayport->LoadFeeStep 		= daddslashes( $_POST['load_fee_step'] );
				$oPayport->LoadFeePerDown 	= daddslashes( $_POST['load_fee_per_down'] );
				$oPayport->LoadFeePercentDown = daddslashes( $_POST['load_fee_percent_down'] );
				$oPayport->DrawLimitMinPer 	= daddslashes( $_POST['draw_limit_min_per'] );
				$oPayport->DrawLimitMaxPer 	= daddslashes( $_POST['draw_limit_max_per'] );
				$oPayport->DrawFeePerUp 	= daddslashes( $_POST['draw_fee_per_up'] );
				$oPayport->DrawFeePercentUp = daddslashes( $_POST['draw_fee_percent_up'] );
				$oPayport->DrawFeeMin 		= daddslashes( $_POST['draw_fee_min'] );
				$oPayport->DrawFeeMax 		= daddslashes( $_POST['draw_fee_max'] );
				$oPayport->DrawFeeStep 		= daddslashes( $_POST['draw_fee_step'] );
				$oPayport->DrawFeePerDown 	= daddslashes( $_POST['draw_fee_per_down'] );
				$oPayport->DrawFeePercentDown = daddslashes( $_POST['draw_fee_percent_down'] );
				$oPayport->PlatLoadPercent 	= daddslashes( $_POST['plat_load_percent'] );
				$oPayport->PlatLoadMin 		= daddslashes( $_POST['plat_load_min'] );
				$oPayport->PlatLoadMax 		= daddslashes( $_POST['plat_load_max'] );
				$oPayport->PlatDrawPercent 	= daddslashes( $_POST['plat_draw_percent'] );
				$oPayport->PlatDrawMin 		= daddslashes( $_POST['plat_draw_min'] );
				$oPayport->PlatDrawMax 		= daddslashes( $_POST['plat_draw_max'] );
				$oPayport->TotalBalance 	= daddslashes( $_POST['total_balance'] );
				$oPayport->OptLimitTimes 	= intval($_POST['opt_limit_times']);
				$oPayport->PayportHost 		= daddslashes( $_POST['payport_host'] );
				$oPayport->PayportUrlLoad 	= daddslashes( $_POST['payport_url_load'] );
				$oPayport->PayportUrlDraw 	= daddslashes( $_POST['payport_url_draw'] );
				$oPayport->PayportUrlQues 	= daddslashes( $_POST['payport_url_ques'] );
				$oPayport->ReceiveHost 		= daddslashes( $_POST['receive_host'] );
				$oPayport->ReceiveUrl 		= daddslashes( $_POST['receive_url'] );
				$oPayport->ReceiveUrlKeep 	= daddslashes( $_POST['receive_url_keep'] );
				$oPayport->Status 			= intval($_POST['status']);
				$oPayport->PayportIntro 	= daddslashes( $_POST['payport_intro'] );
				$oPayport->LangCode 		= daddslashes( $_POST['lang_code'] );
				$oPayport->PayportAttr 		= intval($iPayport_attr);
				
			$aLocation  = array(
				0 => array('text'=>'继续:增加支付接口','href'=>url('payportmgr','add')),
    			1 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list'))
    			);
				if ($oPayport->add()){
    			   	sysMessage('成功,增加支付接口参数',0,$aLocation);
				}else{
					sysMessage('增加失败',1,$aLocation);
				}
    	}
    	else{
    		sysMessage('What‘s your want?');	
    	}
    }
    
    
	/**
	 * 禁用接口
	 * 
	 *
	 */
    public function actionDisable(){
    	$aLocation  = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    	
    	$iPayportId = (isset($_GET["id"]) && is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
    	
    	if ( !is_numeric($iPayportId) || ($iPayportId <= 0) ){
    			sysMessage('失败:ID丢失',1,$aLocation);
    			exit;
    	}
    	
		$oPayport   = new model_pay_payportinfo();
		$oPayport->getPayportData($iPayportId,'account','-1');

			if ($oPayport->disable()){
    			   	sysMessage('成功:禁用接口',0,$aLocation);
				}else{
					sysMessage('失败:禁用接口,稍候重试',1,$aLocation);
				}
    }
    
    
	/**
	 * 启用接口
	 * 
	 *
	 */
    public function actionEnable(){
    	$aLocation  = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    	
    	$iPayportId = (isset($_GET["id"]) && is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
    	
    	if ( !is_numeric($iPayportId) || ($iPayportId <= 0) ){
    			sysMessage('失败:ID丢失',1,$aLocation);
    			exit;
    	}
    	
		$oPayport   = new model_pay_payportinfo();
		$oPayport->getPayportData($iPayportId,'account','-1');
		$oPayport->Id = $iPayportId;
    	
				if ($oPayport->enable()){
    			   	sysMessage('成功:启用接口',0,$aLocation);
				}else{
					sysMessage('失败:启用接口,稍候重试',1,$aLocation);
				}
    }
    
    
    /**
     * 删除接口 (逻辑删除)
     *
     */
    public function actionDelete(){
    	$aLocation  = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    	
    	$iPayportId = (isset($_GET["id"]) && is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
    	
    	if ( !is_numeric($iPayportId) || ($iPayportId <= 0) ){
    			sysMessage('失败:ID丢失',1,$aLocation);
    			exit;
    	}
    	// TODO 是否判断 接口激活状态不许可？
    		
		$oPayport   = new model_pay_payportinfo();
		$oPayport->Id = $iPayportId;

			if ($oPayport->erase()){
    			   	sysMessage('成功:删除接口',0,$aLocation);
				}else{
					sysMessage('失败:删除接口,稍候重试',1,$aLocation);
				}
    }
    
    /*************************************  余额修正 (EMAIL充值 转帐记录) ********************************************/
    
    public function actionBalanceEdit(){
    	$aLocation  = array(
    		0 => array('text' => '关闭本页面','href' => 'javascript:window.close()'),
    		1 => array('text' => '支付接口列表','href'=>url('payportmgr','list') ),
    		2 => array('text' => '继续:追加转帐记录','href'=>url('payportmgr','balanceedit') ) 
    	);
    	$sFlag =  isset($_REQUEST['flag']) ? trim($_REQUEST['flag']) : false;
    	if ( !$sFlag ) {
    		$iGetPayaccountid 	= isset($_REQUEST['payaccountid']) ? intval($_REQUEST['payaccountid']) : false;
    		$iPayAccId 			= $iGetPayaccountid ? $iGetPayaccountid : intval($_SESSION['accidforadd']);
    	
    		if ( !is_numeric($iPayAccId) || ($iPayAccId <= 0) ){
    			sysMessage('失败:ID丢失',1,$aLocation);
    			exit;
    		}
    	
    		$_SESSION['accidforadd'] = $iPayAccId;
    		
    		$oPayAcc = new model_pay_payaccountinfo($iPayAccId);
    		$oPayAcc->GetType = true;
    		
    		// 刷新分账户余额值
    		$aPayAcc = $oPayAcc->getAccountDataObj(true);
    		$oPayAcc->getBalance();
    		
    		if ( empty($aPayAcc['payportid']) || empty($aPayAcc['payport_name'] ) ){
				sysMessage('失败:数据读取错误',1,$aLocation);
				unset($oPayAcc);
    			exit;
    		}

    		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    		$GLOBALS['oView']->assign( "ur_here", "追加转账");
			$GLOBALS['oView']->assign( "action", 'balanceedit');
			$GLOBALS['oView']->assign( "PayAccountDetail", $aPayAcc);
    		$GLOBALS['oView']->display("payport_acc_balance.html");
    		exit;
    	}elseif($sFlag == 'save'){
    		// 优先取转出值，否则使用转入值 ^[0-9]+(\.[0-9]{1,2})?$
    		if (!empty($_REQUEST['balanceout']) && !empty($_REQUEST['balancein']) )
    		{
    			sysMessage('不可同时提交转入与转出值',1,$aLocation);
    			exit;
    		}
    		if ( !eregi("^[0-9]+(\.[0-9]{1,2})?$",$_REQUEST['balanceout']) && !empty($_REQUEST['balanceout'])  ){
    			sysMessage('提交转出值无效',1,$aLocation);
    			exit;
    		}
    		if (  !eregi("^[0-9]+(\.[0-9]{1,2})?$",$_REQUEST['balancein']) && !empty($_REQUEST['balancein'])  ) {
    			sysMessage('提交转入值无效',1,$aLocation);
    			exit;
    		}
    		if (  !eregi("^[0-9]+(\.[0-9]{1,2})?$",$_REQUEST['bankcharge']) && !empty($_REQUEST['bankcharge'])  ) {
    			sysMessage('提交银行手续费无效',1,$aLocation);
    			exit;
    		}
    		if ( strlen($_REQUEST['remarks']) > 50 )
    		{
    			sysMessage('备注内容太长', 1, $aLocation);
    			exit;
    		}
    		$iNewBalance =  $_REQUEST['balanceout'] ? floatval(0- floatval($_REQUEST['balanceout']) - floatval($_REQUEST['bankcharge'])) : floatval($_REQUEST['balancein']);
    		
    		if ( empty($iNewBalance) || !eregi("[0-9\.]",$iNewBalance) ) {
    			sysMessage('设置金额无效',1,$aLocation);
    			exit;
    		}

    		$iPayAccId = intval($_REQUEST['id']);
    		if (!$iPayAccId || !is_numeric($iPayAccId)) {
    			sysMessage('失败：传入ID无效',1,$aLocation);
    			exit;
    		}
    		
    		
    		$oPayAcc = new model_pay_payaccountinfo($iPayAccId);
    		$oPayAcc->GetType=true;
    		
    		// 刷新分账户余额值
    		$aPayAcc = $oPayAcc->getAccountDataObj(true);
    		$aTmp = $oPayAcc->getBalance(true);
    		if ( ( $aTmp['balance'] - floatval($_REQUEST['balanceout']) - floatval($_REQUEST['bankcharge']) < 0 ) && ($_REQUEST['balanceout'] > 0) )
    		{
    			sysMessage('转帐记录无效，银行余额将为负',1, $aLocation);
    			exit;
    		}
    		
    		$iUserid = $_SESSION['userid'] ? $_SESSION['userid'] : false;
    		$sOpuser = $_SESSION['adminname'] ? $_SESSION['adminname'] : false;
    		$oPayAcc->DepositValue = array(
    				'inbalance'	=> floatval($_REQUEST['balancein']),
    				'outbalance'=> floatval($_REQUEST['balanceout']),
    				'bankcharge'=> floatval($_REQUEST['bankcharge']),
    				'ppid' 		=> $oPayAcc->PaySlotId,
    				'accid' 	=> $iPayAccId,
    				'userid' 	=> $iUserid,
    				'opuser' 	=> $sOpuser,
    				'remark' 	=> daddslashes( $_REQUEST['remarks'] )
    				);
    	
    		$ttmmpp = $oPayAcc->saveBalance($iNewBalance);
    	
	   		if($ttmmpp){
    			sysMessage('成功:记录转帐金额',0,$aLocation);
    		}else{
    			
    			sysMessage('失败:记录转帐',1,$aLocation);
    		}
    		
    	}elseif($sFlag == 'refrence'){
    		//刷新余额  AJAX调用
    		$iPayAccId = intval($_REQUEST['id']);
    		if (!$iPayAccId || !is_numeric($iPayAccId)) {
    			echo 0;
    			exit;
    		}
    		$oPayport = new model_pay_payportinfo();
    		$oPayport->getPayportData($iPayAccId);
    		
    		// 刷新其下各个分账户的余额
    		$aRe = $oPayport->getMyChlidAcc();

    		foreach ($aRe AS $aId){
				$oChlidPayAcc = new model_pay_payaccountinfo( intval($aId['aid']) );
				$oChlidPayAcc->getBalance();
				unset($oChlidPayAcc);
			}
			
			// 刷新支付接口总余额
			$result = $oPayport->refTotalBalance(true);
    		if($result){
    			$result = number_format($result,2,'.','');
    			echo str_replace('.00','',$result);
    		}else{
    			echo 0;
    		}
    		
    	}else{
    		sysMessage('what is your want?',1,$aLocation);
    	}
    }
    
    /*************************************  分账户管理部分  ***********************************************/
    
    /**
     * 新增分账户
     *
     */
    public function actionAccAdd(){
    	
    	$aLocation  = array(0 => array('text'=>'继续:增加支付接口','href'=>url('payportmgr','accadd')),
    		1 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    	
    	$sFlag =  isset($_POST['flag']) ? trim($_POST['flag']) : false;
    	if($sFlag == false){
    		//显示界面 PayportIdforAdd
    		$iPayportId = (isset($_REQUEST["payportid"]) && is_numeric($_REQUEST["payportid"])) ? intval($_REQUEST["payportid"]) : intval($_SESSION['PayportIdforAdd']);
    		if ( !is_numeric($iPayportId) || ($iPayportId <= 0) ){
    			sysMessage('失败:ID丢失',1,$aLocation);
    			exit;
    		}
    		//为连续增加保留 payportid
			$_SESSION['PayportIdforAdd'] = $iPayportId;
				
				
    		$oPayport = new model_pay_payportinfo();
    		$oPayport->getPayportData($iPayportId,'account');
    		$aPayAccountDetail = array(
    			'payportid' 		=> $iPayportId,
    			'payport_name' 		=> $oPayport->PayportName,
    			'payport_nickname' 	=> $oPayport->PayportNickname,
    			'payport_attr_load' => $oPayport->PayportAttrLoad,
    			'payport_attr_draw' => $oPayport->PayportAttrDraw,
    			'payport_attr_drawlist' => $oPayport->PayportAttrDrawlist,
    			'payport_attr_drawhand' => $oPayport->PayportAttrDrawhand,
    			'payport_attr_ques' => $oPayport->PayportAttrQues,
    			'acc_currency' 		=> $oPayport->Currency
    		);
    		if ( empty($oPayport->PayportName) ){
    			sysMessage('失败:名称丢失',1,$aLocation);
    			unset($oPayport,$aPayAccountDetail);
    			exit;
    		}

    		$iIsMailDeposit = ( strtolower($aPayAccountDetail['payport_name']) == 'mdeposit') ? 1 : 0;
    		
    		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    		$GLOBALS['oView']->assign( "ur_here", "新增接口分账户");
    		$GLOBALS['oView']->assign( "PayAccountDetail", $aPayAccountDetail);
			$GLOBALS['oView']->assign( "action", 'accadd');
			$GLOBALS['oView']->assign( "flag", 'save');
			$GLOBALS['oView']->assign( "ismaildeposit", $iIsMailDeposit);
    		$GLOBALS['oView']->display("payport_acc_edit.html");
    		exit;
    	}
    	elseif ($sFlag == 'save'){
    			$oPayAccount   = new model_pay_payaccountinfo();
    			
    			if ( empty($_POST['ads_payport_id']) || empty($_POST['ads_payport_name'])  || empty($_POST['acc_name'])  
    			|| empty($_POST['acc_ident']) || empty($_POST['acc_currency']) ){
    				sysMessage('失败:提交数据不完整',1,$aLocation);
    				exit;
    			}
    			
    			if ( empty($_POST['acc_key']) && empty($_POST['acc_mail']) ){
    			 	sysMessage('失败:提交数据不完整(KEY或MAIL必须填写一项)',1,$aLocation);
    				exit;
    			}
    			
    			if ( !isset($_POST['payport_attr_load']) ) $_POST['payport_attr_load'] = 0;
    			if ( !isset($_POST['payport_attr_draw']) ) $_POST['payport_attr_draw'] = 0;
    			if ( !isset($_POST['payport_attr_ques']) ) $_POST['payport_attr_ques'] = 0;
    			if ( !isset($_POST['payport_attr_drawlist']) ) $_POST['payport_attr_drawlist'] = 0;
    			if ( !isset($_POST['payport_attr_drawhand']) ) $_POST['payport_attr_drawhand'] = 0;
    			
    			if (  ( $_POST['payport_attr_draw'] || $_POST['payport_attr_drawlist'] ) && $_POST['payport_attr_drawhand'] ){
    				sysMessage('失败:矛盾的设置',0,$aLocation);
    				exit;
    			}
    	
				//保存数据
				$iAccAttr = $_POST['payport_attr_load'] + $_POST['payport_attr_draw'] + $_POST['payport_attr_drawlist'] + $_POST['payport_attr_ques'] + $_POST['payport_attr_drawhand'];
				 
    			$oPayAccount->PaySlotId 	= daddslashes( $_POST['ads_payport_id'] );
    			$oPayAccount->PaySlotName 	= daddslashes( $_POST['ads_payport_name'] );
    			$oPayAccount->AccName 		= daddslashes( $_POST['acc_name'] );
    			$oPayAccount->AccIdent 		= daddslashes( $_POST['acc_ident'] );
    			$oPayAccount->AccKey 		= daddslashes( $_POST['acc_key'] );
    			$oPayAccount->AccSiteId 	= daddslashes( $_POST['acc_siteid'] );
    			$oPayAccount->AccMail 		= daddslashes( $_POST['acc_mail'] );
    			$oPayAccount->AccCurrency 	= daddslashes( strtoupper($_POST['acc_currency']) );
    			$oPayAccount->SrcBalance 	= daddslashes( $_POST['srcbalance'] );
    			$oPayAccount->BalanceLimit 	= daddslashes( $_POST['balance_limit'] );
    			$oPayAccount->AccReceiveHost = daddslashes( $_POST['acc_receive_host'] );
    			$oPayAccount->RegTime 		= daddslashes( $_POST['reg_time'] );
    			$oPayAccount->ValidTime 	= daddslashes( $_POST['valid_time'] );
				$oPayAccount->AccAttr 		= $iAccAttr;
				
							
				$bReAdd = $oPayAccount->add();
				if ($bReAdd){
    			   	sysMessage('成功:增加接口分账户',0,$aLocation);
				}else{
					sysMessage('失败:增加接口分账户',1,$aLocation);
				}
				
    	}
    	else{
    		sysMessage('What‘s your want?');	
    	}
    }
    
    
    /**
     * 查看分账户信息
     *
     */
	public function actionAccView(){
		$aLocation  = array(0 => array('text'=>'继续:增加支付接口','href'=>url('payportmgr','accadd')),
    		1 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    
    		$iPayAccountId = (isset($_GET['payaccountid']) && is_numeric($_GET['payaccountid'])) ? intval($_GET['payaccountid']) : 0;
    		$oPayAccount = new model_pay_payaccountinfo($iPayAccountId);
    		$oPayAccount->GetType=true;
    		$aPatAccount = stripslashes_deep( $oPayAccount->getAccountData() );
			
			if ( empty($oPayAccount->Id) || empty($oPayAccount->Currency) || empty($oPayAccount->PayportName) )
			{
				sysMessage('失败:数据读取错误',1,$aLocation);
				unset($oPayAccount);
    			exit;
			}
			
    		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url('payportmgr','list'), 'text'=>'支付接口列表' ) );
    		$GLOBALS['oView']->assign( 'ur_here', '查看接口分账户');
    		$GLOBALS['oView']->assign( 'PayAccountDetail', $aPatAccount);
    		$GLOBALS['oView']->assign( 'action', 'accedit' );
       			 $oPayAccount->assignSysInfo();
    		$GLOBALS['oView']->display( 'payport_acc_view.html' );
    		exit;
    }
    
    
    /**
     * 管理分账户
     *
     */
    public function actionAccEdit(){
    	
    	//获取休市时间，只许可在休市时间修改;
    	$aLocat = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
		$oConfig = new model_config();
		$iPayportEdit = $oConfig->getConfigs('payport_edit');
		if ($iPayportEdit > 0){	
			$sResetTime = $oConfig->getConfigs('xiushishijian');
			$aResetTime = explode('-',$sResetTime);
			// 24小时值，无前导0
			$sNow = date('G:i');
			if ( ($sNow > $aResetTime[1]) || ($sNow < $aResetTime[0]) ){
				sysMessage('非休市时间,禁用编辑',1,$aLocat);
				exit;
			}
		}
		
		$aLocation  = array(0 => array('text'=>'继续:增加支付接口','href'=>url('payportmgr','accadd')),
    						1 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    	$sFlag =  isset($_POST['flag']) ? trim($_POST['flag']) : false;	
    	
    	if ($sFlag == false){
    		$iPayAccountId = (isset($_GET['payaccountid']) && is_numeric($_GET['payaccountid'])) ? intval($_GET['payaccountid']) : 0;
    		$oPayAccount   = new model_pay_payaccountinfo($iPayAccountId);
    		$oPayAccount->GetType=true;
    		$aPatAccount   = stripslashes_deep( $oPayAccount->getAccountData() );
			if ( empty($aPatAccount['acc_ident']) && empty($aPatAccount['acc_key']) && empty($aPatAccount['acc_siteid']) && empty($aPatAccount['acc_mail']) ){
				sysMessage('失败:数据读取错误',1,$aLocat);
				exit;
			}
			//兼容EMAIL充值项目, 标记值 mdeposit
			$iIsMailDeposit = ( strtolower($aPatAccount['payport_name']) == 'mdeposit') ? 1 : 0;
			
    		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    		$GLOBALS['oView']->assign( "ur_here", "编辑接口分账户");
    		$GLOBALS['oView']->assign( "PayAccountDetail", $aPatAccount);
    		$GLOBALS['oView']->assign( "action", 'accedit');
    		$GLOBALS['oView']->assign( "flag", 'update');
    		$GLOBALS['oView']->assign( "ismaildeposit", $iIsMailDeposit);
    			 $oPayAccount->assignSysInfo();
    		$GLOBALS['oView']->display("payport_acc_edit.html");
    		exit;
    	}
    	elseif ($sFlag == 'update'){
    		$iPayAccountId = (isset($_POST["id"]) && is_numeric($_POST["id"])) ?  intval($_POST["id"]) :  0;
    		
    		if ( !is_numeric($iPayAccountId) || ($iPayAccountId <= 0) ){
    			sysMessage('失败:ID丢失',1,$aLocation);
    			exit;
    		}
			$oPayAcc   = new model_pay_payaccountinfo($iPayAccountId);
	
				if ( !isset($_POST['payport_attr_load']) ) $_POST['payport_attr_load'] = 0;
    			if ( !isset($_POST['payport_attr_draw']) ) $_POST['payport_attr_draw'] = 0;
    			if ( !isset($_POST['payport_attr_ques']) ) $_POST['payport_attr_ques'] = 0;
    			if ( !isset($_POST['payport_attr_drawlist']) ) $_POST['payport_attr_drawlist'] = 0;
    			if ( !isset($_POST['payport_attr_drawhand']) ) $_POST['payport_attr_drawhand'] = 0;
    			
				$oPayAcc->AId 		= $iPayAccountId;
				$oPayAcc->AccName 	= daddslashes( $_POST['acc_name'] );
				$oPayAcc->AccIdent 	= daddslashes( $_POST['acc_ident'] );
				$oPayAcc->AccKey 	= daddslashes( $_POST['acc_key'] );
				$oPayAcc->AccSiteId = daddslashes( $_POST['acc_siteid'] );
				$oPayAcc->AccMail 	= daddslashes( $_POST['acc_mail'] );
				$oPayAcc->AccAttr 	= intval($_POST['payport_attr_load'] + $_POST['payport_attr_draw'] 
										+ $_POST['payport_attr_ques'] + $_POST['payport_attr_drawlist'] 
										+ $_POST['payport_attr_drawhand']);
				$oPayAcc->AccCurrency 		= daddslashes( strtoupper( $_POST['acc_currency'] ) );
				$oPayAcc->AccReceiveHost 	= daddslashes( $_POST['acc_receive_host'] );
				$oPayAcc->BalanceLimit 		= daddslashes( str_replace(',','', $_POST['balance_limit']) );
				$oPayAcc->ValidTime 		= daddslashes( $_POST['valid_time'] );
				
				$aLocation  = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
				
				$oPayAcc->InputArray = array(
						'acc_name'  => $_POST['old_acc_name'],
						'acc_ident' => $_POST['old_acc_ident'],
						'acc_mail'  => $_POST['old_acc_mail']
						); 
				if ($oPayAcc->set()){
    				sysMessage('成功,编辑分账户参数',0,$aLocation);
				}else{
					sysMessage('保存失败',1,$aLocation);
				}
    	}
    	else{
    		sysMessage('What‘s your want?');
    	}
    }
    
    /**
     * 激活或禁用分账户
     * (接口非激活时，不可激活分账户)
     */
    public function actionAccActive(){
    	
    	$aLocation  = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    	$optModel = $_REQUEST['opt'];
    	if ( !($optModel) ){
    			sysMessage('失败:没有操作模式',1,$aLocation);
    			exit;
    	}
    	
    	$iPayAccountId = (isset($_GET['payaccountid']) && is_numeric($_GET['payaccountid'])) ? intval($_GET['payaccountid']) : 0;
    	if ( !is_numeric($iPayAccountId) || ($iPayAccountId <= 0) ){
    			sysMessage('失败:ID丢失',1,$aLocation);
    			exit;
    	}
    	$oPayAcc = new model_pay_payaccountinfo($iPayAccountId);

    	if ( empty($oPayAcc->Id) || empty($oPayAcc->Currency) || empty($oPayAcc->PayportName) )
			{
				sysMessage('失败:数据读取错误',1,$aLocation);
				unset($oPayAcc);
    			exit;
			}
			
    	if ($optModel == 'active'){
    		//当支付接口禁用时，不可启用分账户
    		if ($oPayAcc->Status == '1'){
    			$bReturn = $oPayAcc->enable();
    			$sMsg = '启用分账户';
    		}else{
    			$bReturn = false;
    			$sMsg = '支付接口关闭中,不可启用所辖分账户';
    		}
    	}else{
    		//当接口处于禁用，不可禁用分账户
    		$oPayport = new model_pay_payportinfo();
    		$oPayport->getPayportData($oPayAcc->PaySlotId,'intro');
    		if ( intval($oPayport->Status) === intval(1) ){
    			$bReturn = $oPayAcc->disable();
    			$sMsg = '关闭分账户';
    		}else{
    			$bReturn = false;
    			$sMsg = '所属支付接口处于非启用状态,不可操作';
    		}
    	}
    	
    		if ($bReturn){
    				sysMessage('成功:'.$sMsg,0,$aLocation);
			}else{
					sysMessage('失败:'.$sMsg,1,$aLocation);
			}
    }
    
    /**
     * 逻辑删除以个分账户
     *  (账户被激活时不可删除)
     *
     */
    public function actionAccDelete(){
    	$iPayAccountId = (isset($_REQUEST["payaccountid"]) && is_numeric($_REQUEST["payaccountid"])) ? intval($_REQUEST["payaccountid"]) : 0;
    	$aLocation  = array(0 => array('text'=>'查看:支付接口列表','href'=>url('payportmgr','list')));
    	
    	if ($iPayAccountId == 0) {
    		sysMessage('失败:ID丢失', 1, $aLocation);
    		exit;
    	}
			$oPayAcc = new model_pay_payaccountinfo($iPayAccountId);
			
			if ( empty($oPayAcc->Id) || empty($oPayAcc->Currency) || empty($oPayAcc->PayportName) )
			{
				sysMessage('失败:数据读取错误', 1, $aLocation);
				unset($oPayAcc);
    			exit;
			}
			
			if ($oPayAcc->IsEnable != 1){
				if ($oPayAcc->delete()){
    				sysMessage('成功:删除分账户', 0, $aLocation);
				}
				else{
					sysMessage('失败', 1, $aLocation);
				}
			}
			else{
				sysMessage('失败:此分账户已被激活,不能删除', 1, $aLocation);
			}
			
    }
    
    
    /**
     * 分账户列表
     *  (已知 payport id)
     *  -- 默认输出AJAX JASON格式
     */
	public function actionAccList()
    {
    	if (!isset($_POST['id'])) $_POST['id'] = false;
    	if (!isset($_POST['ajax'])) $_POST['ajax'] = false;
    	if (!isset($_POST['json'])) $_POST['json'] = false;
    	
    	$iPPId = $_POST['id'] ? intval($_POST['id']) : intval($_GET['id']);
    	$bAjax = $_POST['ajax'] ? true : false;
    	$bJason= $_POST['json'] ? true : false;
    	if ( !is_numeric($iPPId) || (empty($iPPId)) ){
    		echo false;
    	}
    	
    	$oPayAccount = new model_pay_payaccountlist();
    	$aPayAccount = $oPayAccount->singleList($iPPId,0);
    	if ($bAjax === true){
    		if ( count($aPayAccount) >= 1){
    			//array to string
    			$sPA = '';
    			$aPattren = array('/,/','/.0000/','/0000-00-00 00:00:00/');
    			$aReplace = array('，','','');
    			
    			if ($bJason === false){
    				foreach ($aPayAccount AS $aPay){
						//    	$aPay = preg_replace($aPattren,$aReplace,$aPay);
    					$sPA .= $aPay['aid'].','.$aPay['acc_name'].','.$aPay['acc_attr'].','.$aPay['acc_currency'].','.str_replace('.00','', number_format($aPay['balance'],2,'.','') ).','.str_replace('.00','', number_format($aPay['balance_limit'],2,'.','') ).','.str_replace('0000-00-00','--',substr($aPay['reg_time'],0,10)).','.str_replace('0000-00-00','--',substr($aPay['open_time'],0,10)).','.$aPay['isenable'].','
    						.str_replace('.00','', number_format($aPay['srcbalance'],2,'.','') ).','.str_replace('.00','', number_format($aPay['inbalance'],2,'.','') ).','.str_replace('.00','', number_format($aPay['outbalance'],2,'.','') ).',';
    				}
    				echo substr($sPA,0,-1);
    			}
    			else{
    				echo json_encode($aPayAccount);	
    			}
    			
    		}
    		else{
    			
    			echo false;
    		}
    		
    	}
    	else{
    		return $aPayAccount;
    	}
    }
    
    
    
    
	/**
     * 查看 转帐记录 列表
     * URL = ./?controller=payportmgr&action=transferrecord
     */
    public function actionTransferRecord()
    {
    	
    	$sWhere = ' `isop`=1 ';
    	if ( $_REQUEST['payaccountid'] > 0)
    		$sWhere .= ' AND accid='.intval($_REQUEST['payaccountid']);
    		
    	$p         = isset($_GET['p']) ? intval($_GET['p']) : 0;
        $pn        = isset($_GET['pn']) ? intval($_GET['pn']) : 20;
        $oDepositList = new model_deposit_recordinfo();
        $aResult   = $oDepositList->getList('*', $sWhere, $pn , $p);
        $oPager    = new pages( $aResult['affects'], $pn, 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'DepositList', $aResult['results'] );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("payportmgr","list"), 'text'=>'支付接口列表' ) );
    	$GLOBALS['oView']->assign( "ur_here", "转帐记录列表");
        	$oDepositList->assignSysInfo();
        $GLOBALS['oView']->display("deposit_record_list.html");
        
    	exit;
    }
    
    /* end class */
}
?>