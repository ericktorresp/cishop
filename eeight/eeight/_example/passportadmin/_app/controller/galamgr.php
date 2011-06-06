<?php


class controller_galamgr extends basecontroller 
{
	/**
	 * 列表
	 *
	 */
	function actionList()
	{
		$oGala = new model_gala();
		$oGala->Page 	 = isset($_GET['p']) ? intval($_GET['p']) : 1;
		$oGala->PageSize = isset($_GET['pn']) ? intval($_GET['pn']) : 20;
		$oGala->OrderBy  = isset($_GET['orderby']) ? daddslashes($_GET['orderby']) : '';
		$aList = $oGala->getlist();
		$oPage = new pages($aList['affects'], $oGala->PageSize, 5);
		
		foreach ( $aList['results'] AS &$aL )
		{
			$aL['day'] = intval( substr($aL['day'],0,2) ).'月'.intval( substr($aL['day'],2,2) ).'日';
			if ($aL['day2'] != '0000') $aL['day2'] = intval( substr($aL['day2'],0,2) ).'月'.intval( substr($aL['day2'],2,2) ).'日';
		}
		$GLOBALS['oView']->assign( 'ur_here', '节日列表');
		$GLOBALS['oView']->assign( 'pages', $oPage->show() );
        $GLOBALS['oView']->assign( 'aList', $aList['results'] );
        $GLOBALS['oView']->assign( 'orderby', $_GET['orderby'] );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url('galamgr','add'), 'text'=>'添加节日' ) );
        	$oGala->assignSysInfo();
        $GLOBALS['oView']->display('galamgr_list.html');
        unset($oGala);
        EXIT;
	}
	
	/**
	 * 添加
	 *
	 */
	function actionAdd()
	{
		$aLink =  array(
				0=>array('text' => '添加节日', 'href' => url('galamgr','add')),
				1=>array('text' => '节日列表', 'href' => url('galamgr','list'))
				);
		if ( $_POST )
		{
			if ( empty($_REQUEST['gala']) || empty($_REQUEST['month'])
				|| empty($_REQUEST['day']) || empty($_REQUEST['comment']) )
			{
				sysMessage('失败:提交数据不足',1, $aLink);
				exit;
			}
			$iDay  = $_REQUEST['day'];
			$iDay2 = isset($_REQUEST['day2']) ? $_REQUEST['day2'] : 0;

			$oGala = new model_gala();
			$oGala->Mode=1;
			// upload image
			if ( $_FILES['img'] && $_FILES['img']['size'] > 100 )
			{
				$oGala->UploadTmp = $_FILES['img'];
				
				if ( $oGala->upload() )
				{
					$sMsg = '图片上传成功';
				}
				else 
				{
					$sMsg = '图片上传失败';
				}
				
				$oGala->ImgUrl	= !empty($oGala->SourceFile) ? basename( $oGala->SourceFile ) : '';
			
			}			
			else 
			{
				$sMsg = '没有上传图片';
			}
			$oGala->Gala	= daddslashes( $_REQUEST['gala'] );
			$oGala->Day		= $oGala->zerofill($_REQUEST['month']) . $oGala->zerofill($_REQUEST['day']);
			if ( intval($_REQUEST['month2']) > 0 && intval($_REQUEST['day2']) > 0 )
			{
				$oGala->Day2	= $oGala->zerofill($_REQUEST['month2']) . $oGala->zerofill($_REQUEST['day2'] );
				if ( $oGala->Day >= $oGala->Day2 )
				{
					sysMessage('失败:添加节日 (日期段不符合逻辑)', 1, $aLink);
					unset($oGala);
					exit;
				}
			}
			$oGala->Comment	= daddslashes( $_REQUEST['comment']);
			$oGala->Status = 1;
			if ( $oGala->add() === TRUE )
			{
				sysMessage('成功:添加节日 ('.$sMsg.')' , 0, $aLink);
				unset($oGala);
				exit;
			}
			else 
			{
				sysMessage('失败:添加节日 ('.$oGala->ErrorInfo.')', 1, $aLink);
				unset($oGala);
				exit;
			}
		}
		else 
		{
			$oGala = new model_gala();
			$GLOBALS['oView']->assign( 'ur_here', '添加节日');
			$GLOBALS['oView']->assign( 'flag', 'add' );
			$GLOBALS['oView']->assign( 'month', $oGala->makeCalader('mon') );
			$GLOBALS['oView']->assign( 'day',  $oGala->makeCalader('day') );
        	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url('galamgr','list'), 'text'=>'节日列表' ) );
			$GLOBALS['oView']->display('galamgr_add.html');
			unset($oGala);
			exit;
		}
		
	}
	
	/**
	 * 编辑
	 *   (替换图片,修改信息等)
	 */
	function actionEdit()
	{
		$aLink =  array(
				1=>array('text' => '编辑节日', 'href' => url('galamgr','edit', array('id'=>$_REQUEST['id']) )),
				0=>array('text' => '节日列表', 'href' => url('galamgr','list'))
				);
		if ( $_POST )
		{
			if ( empty($_REQUEST['gala']) || empty($_REQUEST['month'])
				|| empty($_REQUEST['day']) || empty($_REQUEST['comment']) )
			{
				sysMessage('失败:提交数据不足',1, $aLink);
				exit;
			}
		
			$oGala = new model_gala();
			$oGala->Id		= intval( $_REQUEST['id']);
			$oGala->Mode	= 1;
			// upload image
			if ( $_FILES['img'] && $_FILES['img']['size'] > 100 )
			{
				$oGala->UploadTmp = $_FILES['img'];
				
				if ( $oGala->upload() )
				{
					$sMsg = '图片上传成功';
				}
				else 
				{
					$sMsg = '图片上传失败';
				}
				
				$oGala->ImgUrl	= !empty($oGala->SourceFile) ? basename( $oGala->SourceFile ) : '';
			
			}			
			else 
			{
				$sMsg = '没有上传图片';
			}
			$oGala->Gala	= daddslashes( $_REQUEST['gala'] );
			$oGala->Day		= $oGala->zerofill($_REQUEST['month']) . $oGala->zerofill($_REQUEST['day']);
			if ( intval($_REQUEST['month2']) >= 0 && intval($_REQUEST['day2']) >= 0 )
			{
				$oGala->Day2	= $oGala->zerofill($_REQUEST['month2']) . $oGala->zerofill($_REQUEST['day2']);
				if ( $oGala->Day >= $oGala->Day2 && intval($_REQUEST['month2']) > 0 )
				{
					sysMessage('失败:编辑节日 (日期段不符合逻辑)', 1, $aLink);
					unset($oGala);
					exit;
				}
			}
			$oGala->Comment	= daddslashes( $_REQUEST['comment']);
			
			if ( $oGala->update() === TRUE )
			{
				sysMessage('成功:编辑节日 ('.$sMsg.')' , 0, $aLink);
				unset($oGala);
				exit;
			}
			else 
			{
				sysMessage('失败:编辑节日 ('.$oGala->ErrorInfo.')', 1, $aLink);
				unset($oGala);
				exit;
			}
		}
		else 
		{
			$iGalaId = intval($_GET['id']);
			if ( $iGalaId < 1)
			{
				sysMessage('请求数据不足', 1 , $aLink	);
				exit;
			}
			$oGala 		= new model_gala();
			$oGala->Id 	= $iGalaId;
			$oGala->Mode= 1;
			$aResult 	= $oGala->getgala();
			$iDay 		= $aResult['day'];
			if ( strlen($aResult['day2']) > 2) $iDay2 = $aResult['day2'];
			$GLOBALS['oView']->assign( 'ur_here', 	'编辑节日');
			$GLOBALS['oView']->assign( 'flag', 		'edit' );
			$GLOBALS['oView']->assign( 'galaid', 	$iGalaId );
			$GLOBALS['oView']->assign( 'result', 	$aResult );
			$GLOBALS['oView']->assign( 'thismonth', intval( substr($iDay,0,2) ) );
			$GLOBALS['oView']->assign( 'thisday', 	intval( substr($iDay,2,2) ) );
			$GLOBALS['oView']->assign( 'thismonth2', intval( substr($iDay2,0,2) ) );
			$GLOBALS['oView']->assign( 'thisday2', 	intval( substr($iDay2,2,2) ) );
			$GLOBALS['oView']->assign( 'month', 	$oGala->makeCalader('mon') ); 	
			$GLOBALS['oView']->assign( 'day', 		$oGala->makeCalader('day') );
        	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url('galamgr','list'), 'text'=>'节日列表' , array('orderby'=>$_GET['orderby']) ) );
        	$GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url('galamgr','add'), 'text'=>'添加节日' ) );
			$GLOBALS['oView']->display('galamgr_add.html');
			unset($oGala);
			exit;
		}
	}
	
	/**
	 * 设置状态
	 */
	function actionSet()
	{
		$aLink =  array(
				//0=>array('text' => '添加节日', 'href' => url('galamgr','add')),
				0=>array('text' => '节日列表', 'href' => url('galamgr','list', array('orderby'=>$_GET['orderby']) ))
				);
				
			$iGalaId = intval($_GET['id']);
			if ( $iGalaId < 1 || ($_GET['flag'] !='active' && $_GET['flag'] !='unactive' ) )
			{
				sysMessage('请求数据不足', 1 , $aLink	);
				exit;
			}
			
			$oGala = new model_gala();
			$oGala->Id = $iGalaId;
			$oGala->Status = $_GET['flag'] == 'active' ? 1 : 0;
			$sMsg =  $_GET['flag'] == 'active' ? '启用' : '禁用';
			
			if ( $oGala->set() === TRUE )
			{
				$aTmp = $oGala->getGalaById();
				sysMessage('成功:'.$sMsg.'节日 '.$aTmp['gala'], 0, $aLink);
				unset($oGala,$aTmp);
				exit;
			}
			else 
			{
				$aTmp = $oGala->getGalaById();
				sysMessage('失败:'.$sMsg.'节日 '.$aTmp['gala'].' ('.$oGala->ErrorInfo.')', 1, $aLink);
				unset($oGala,$aTmp);
				exit;
			}
	}
	
	/**
	 * 删除记录
	 *
	 */
	function actionDel()
	{
		$aLink =  array(
				0=>array('text' => '节日列表', 'href' => url('galamgr','list', array('orderby'=>$_GET['orderby']) ))
				);
				
			$iGalaId = intval($_GET['id']);
			if ( $iGalaId < 1 )
			{
				sysMessage('请求数据不足', 1 , $aLink	);
				exit;
			}
			
			$oGala 		= new model_gala();
			$oGala->Id 	= $iGalaId;
			$aGala 		= $oGala->getGalaById();
			
			if ( $oGala->del() === TRUE )
			{
				sysMessage('成功:删除节日 '.$aGala['gala'], 0, $aLink);
				unset($oGala,$aTmp);
				exit;
			}
			else 
			{
				sysMessage('失败:删除节日 '.$aGala['gala'].' ('.$oGala->ErrorInfo.')', 1, $aLink);
				unset($oGala,$aTmp);
				exit;
			}
	}
}
?>