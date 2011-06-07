<?php

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