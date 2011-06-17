<?php
/**
 * 主表 [sim]:
 * id, op, number, key, enabled, ip, atime, amount, monthly
 *
 * @TODO deposit_acc_set_add: drop down sim list.
 * 
 * 关联表
 * deposit_acc_set:		+sms_number														[√]
 * deposit_set:			+sms_regex, +sms_sender, +is_sms_notic, +is_sms_order_number	[√]
 * user_deposit_card:	[+sms_number, +sms_key, +sms_ip]
 * ccb_deposit_record:	+sms_number, +order_number										[√]
 * ccb_transfers		+sms_number, +sms_sender										[√]
 * ccb_deposit_error:	+order_number													[√]
 *
 * 1. 根据玩家所分配的卡，读取卡信息以及卡所绑定的手机号，显示给用户
 * 2. 玩家提交充值请求后，系统写入 email_deposit_record(ccb_deposit_record): key(order_number),pay_acc_id(如果该行不支持附言)
 * 3.1. 根据number获取该number对应加密key，根据sender获取银行(deposit_set.sms_regex, deposit_set.is_sms_order_number)
 * 3.2. 解密content, 正则匹配内容，获取：{payor, payee, numbertail, amount, order_number[!is_sms_order_number(ABC)]}
 * 3.3. 写icbc_transfer(ccb_transfer)
 * 3.3. 查询email_deposit_record(ccb_deposit_record)并匹配; 成功: 更新充值记录的状态，写帐变，更新可用余额(事务);失败：记录具体原因
 * error_type: 1-订单号违规，2-时间违规，3-账号违规，4-金额违规
 *
 * added receive action to default controller
 *
 * [EDIT]
 *
 * 1. emaildeposit_confirm.html: 去掉自动刷新
 * 2. controller/emaildeposit.php: 去掉自动写充值记录，改为用户点击时Ajax写入，再打开窗口		[√]
 * 3. @TODO add window.setTimeout(写订单)?
 * 4. @TODO process.py 改为使用 urllib2, 处理超时以及无法访问问题
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
	        		if(!$aSim)	sysMessage("对不起，您提交的手机号码信息不正确，请核对后重新提交", 1, $aLinks);
	        		$GLOBALS['oView']->assign( 'ur_here', '修改手机号码信息' );
	        		$GLOBALS['oView']->assign( 'id', $aSim['id'] );
	        		$GLOBALS['oView']->assign( 'op', $aSim['op'] );
	        		$GLOBALS['oView']->assign( 'number', $aSim['number'] );
	        		$GLOBALS['oView']->assign( 'key', $aSim['key'] );
	        		$GLOBALS['oView']->assign( 'ip', $aSim['ip'] );
	        		$GLOBALS['oView']->assign( 'enabled', $aSim['enabled'] );
	        		$GLOBALS['oView']->assign( 'amount', $aSim['amount'] );
	        		$GLOBALS['oView']->assign( 'monthly', $aSim['monthly'] );
	        		$oSim->assignSysInfo();
	        		$GLOBALS['oView']->display("simmanage_edit.html");
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
	        	$GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("simmanage","add"), 'text'=>'增加手机号码' ) );
	        	$GLOBALS['oView']->assign( 'aSim', $oSim->simlist() );
	        	$GLOBALS['oView']->display("simmanage_simlist.html");
	        	EXIT;
	}

	public function actionAdd()
	{
		$sFlag =  isset($_POST['flag']) ? trim($_POST['flag']) : false;
		if(!$sFlag)
		{
			$GLOBALS['oView']->display("simmanage_add.html");
			EXIT;
		}
		else
		{
			$oSim = A::Singleton('model_sim');
			$aData = array(
				'op'		=> $_POST['op'],
				'number'	=> $_POST['number'],
				'key'		=> $_POST['key'],
				'ip'		=> $_POST['ip'],
				'amount'	=> $_POST['amount'],
				'monthly'	=> $_POST['monthly'],
			);
			$aLocation  = array(
			0 => array('text'=>'继续:增加手机号码','href'=>url('simmanage','add')),
			1 => array('text'=>'查看:受付手机号码列表','href'=>url('simmanage','simlist'))
			);
			if($oSim->add($aData))
			{
				sysMessage('成功',0,$aLocation);
			}
			else
			{
				sysMessage('增加失败',1,$aLocation);
			}
		}
	}

	public function actionUpdate()
	{
		$aLinks = array(
		0 => array(
			'text' => "手机号码列表",
        	'href' => "?controller=simmanage&action=simlist"
        	),
        );

        if (!is_numeric($_POST['id']) || intval($_POST['id']) <= 0)
        {
        	sysMessage("对不起，您提交的手机号码信息不正确，请核对后重新提交", 1, $aLinks);
       	}
       	$id = $_POST['id'];
        $oSim = A::Singleton('model_sim');
        $aSim = $oSim->read($id);
        if(!$aSim)	sysMessage("对不起，您提交的手机号码信息不正确，请核对后重新提交", 1, $aLinks);
        unset($_POST['id'], $_POST['controller'], $_POST['action']);
        if($oSim->update($id, $_POST))
        {
        	sysMessage("更新手机号码成功", 0, $aLinks);
        }
        sysMessage("更新手机号码失败", 1, $aLinks);
	}
}
?>