<?php
/**
 * 主表 [sim]
 * 
 * 关联表
 * deposit_acc_set:		+sms_number
 * deposit_set:			+sms_regex, +sms_sender[, +is_sms_notic, +is_sms_order_number]
 * user_deposit_card:	[+sms_number, +sms_key, +sms_ip]
 * 
 * [CHANGE]:
 * 
 * email_deposit_record(order):		[key->order_number, +sms_number]
 * icbc_transfer(ccb_transfer):		sms_log(银行短信记录)
 * 1. 根据玩家所分配的卡，读取卡信息以及卡所绑定的手机号，显示给用户(deposit_acc_set)
 * 2. 玩家提交充值请求后，系统写入 email_deposit_record(ccb_deposit_record): key(order_number),pay_acc_id(如果该行不支持附言)
 * 3.1. 根据number获取该number对应加密key，根据sender获取银行(deposit_set.sms_regex, deposit_set.is_sms_order_number)
 * 3.2. 解密content, 正则匹配内容，获取：{payor, payee, numbertail, amount, order_number[!is_sms_order_number(ABC)]}
 * 3.3. 写icbc_transfer(ccb_transfer)
 * 3.3. 查询email_deposit_record(ccb_deposit_record)并匹配; 成功: 更新充值记录的状态，写帐变，更新可用余额(事务);失败：记录具体原因
 * error_type: 1-附言违规，2-时间违规，3-账号违规，4-金额违规
 *
 * added receive action to default controller
 * 
 * [EDIT]
 *
 * 1. emaildeposit_confirm.html: 去掉自动刷新
 * 2. controller/emaildeposit.php: 去掉自动写充值记录，改为用户点击提交时Ajax写入，再打开窗口，[弹出提示层：已完成]
 *
 * @author Floyd
 *
 */
class controller_simmanage extends basecontroller
{
	public function actionSimList()
	{
		$aLinks = array(
			0 => array(
				'text' => "手机号码列表",
	        	'href' => "?controller=simmanage&action=simlist"
        	),
        );
        $oSim = A::Singleton('model_sim');
        $_GET['flag'] = isset($_GET['flag']) ? $_GET['flag'] : '';
        if ($_GET['flag'] == 'edit'){
        	// 编辑
        	if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
        		sysMessage("对不起，您提交的手机号码信息不正确，请核对后重新提交", 1, $aLinks);
        	}
        	$aSim = $oSim->read($_GET['id']);
        	$GLOBALS['oView']->assign( 'ur_here', '修改手机号码信息' );
        	$GLOBALS['oView']->assign( 'id', $aSim['id'] );
        	$GLOBALS['oView']->assign( 'op', $aSim['op'] );
        	$GLOBALS['oView']->assign( 'number', $aSim['number'] );
        	$GLOBALS['oView']->assign( 'key', $aSim['key'] );
        	$GLOBALS['oView']->assign( 'ip', $aSim['ip'] );
        	$GLOBALS['oView']->assign( 'enabled', $aSim['enabled'] );
        	$oSim->assignSysInfo();
        	$GLOBALS['oView']->display("simmanage_simedit.html");
        	EXIT;
        }
        // 修改状态操作
        if ($_GET['flag'] == 'set') {
        	if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
        		sysMessage("对不起，您提交的手机号码信息不正确，请核对后重新提交", 1, $aLinks);
        	}
        	if($_GET['status'] == 0)
        	{
        		$oSim->disable($_GET['id']);
        	}
        	else
        	{
        		$oSim->enable($_GET['id']);
        	}
        }
        $GLOBALS['oView']->assign( 'ur_here', '手机号码列表' );
        $GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("simmanage","addsim"), 'text'=>'增加手机号码' ) );
        $GLOBALS['oView']->assign( 'aSim', $oSim->simlist() );
        $GLOBALS['oView']->display("simmanage_simlist.html");
        EXIT;
	}

	public function actionAddSim()
	{

	}

	public function actionEditSim()
	{

	}
}
?>