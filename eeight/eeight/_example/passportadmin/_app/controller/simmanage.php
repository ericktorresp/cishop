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