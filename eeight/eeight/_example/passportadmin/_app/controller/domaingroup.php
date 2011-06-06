<?php
/**
 * 文件 : /_app/controller/domaingroup.php
 * 功能 : 控制器 - 域名组名称 管理
 * 
 * 功能:
 *     - actionAdd()       增加
 *     - actionAssign()    关联域名
 *     - actionDel()		删除
 *     - actionEdit()		修改域名(域名总代列表)
 *     - actionList()		用户域名列表
 *     - actionSave()		保存
 *     - actionUpdate()    更新域名
 * 
 * @author	  jIM
 *  1/5/2011
 * @package   passportadmin
 * 
 */

class controller_domaingroup extends basecontroller
{
    /**
     * 关联域名
     * URL = ./?controller=domaingroup&action=assign
     */
    function actionAssign()
    {
        $aLocation = array( 0=>array('text' => '域名状态','href' => url( 'domaingroup', 'list' ) ) );
    	$aDomian  = isset($_POST["domains"]) && is_array($_POST["domains"]) ? daddslashes($_POST["domains"]) :array();
    	$aUser[0] = isset($_POST["userid"]) && is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
    	if( empty($aDomian) || $aUser[0]==0 )
    	{
    		sysMessage('操作失败:提交数据不全', 1, $aLocation );	
    	}
    	$oAgent  = A::singleton('model_agent');
    	foreach( $aDomian as $iDomain )
    	{
    		$oAgent->userDomainAdd( $iDomain, $aUser );
    	}
 
    	sysMessage('操作成功', 0, $aLocation );
    }


    /**
     * 增加域名
     * URL = ./?controller=domaingroup&action=add
     */
    function actionAdd()
    {
    	if ( $_POST )
    	{
    		$oDomainGroup = A::singleton('model_domaingroup');
    		$aLocation = array(
    				0 => array( 'text' => '返回: 增加域名组', 'href' => url('domaingroup', 'add') ),
        			1 => array( 'text' => '返回: 域名状态', 'href' => url('domaingroup', 'list') )
    			);

    		if ( isset($_POST['domaingroup']) && $oDomainGroup->groupAdd( daddslashes($_POST['domaingroup']) ) > 0 )
    		{
    			sysMessage('成功', 0, $aLocation);
    		}
    		else 
    		{
    			sysMessage('失败: 名称已存在或提交了空值', 1, $aLocation);
    		}
    		
    	}
    	else 
    	{
    		$oDomainGroup = new model_domaingroup();
    		$aGroupList   = $oDomainGroup->groupList();
    		
    		$GLOBALS['oView']->assign( 'domaingroup', $aGroupList );
    		$GLOBALS["oView"]->assign( 'ur_here',    '增加域名组');
    		$GLOBALS['oView']->assign( 'actionlink', array('text'=>'域名状态','href'=>url('domaingroup','list')));
    		$GLOBALS['oView']->assign( 'actionlink2', array('text'=>'分配域名组','href'=>url('domaingroup','set') ) );
    		$GLOBALS["oView"]->display( 'domaingroup_add.html' );
    		EXIT;
    	}
    	
    }
	


    /**
     * 域名组名称列表  (域名状态)
     * URL = ./?controller=domaingroup&action=list
     */
    function actionList()
    {
    	$oDomainGroup = new model_domaingroup();
    	$aGroupList   = $oDomainGroup->groupList();
    	
		$aTempArray =  array();
    	foreach ( $aGroupList AS &$aGl)
    	{
    		//$aDomainList[ $aGl['id'] ] = $oDomainGroup->groupList( array($aGl['id']) );
    		$aGl['domains'] = $oDomainGroup->groupList( array($aGl['id']) ,'group');
    		foreach ( $aGl['domains']  AS $aSinDomain)
    		{
    			$aTempArray[] = $aSinDomain['domain'];
    		}
    	}
    	
    	$oConfig = new model_config();
    	
		$GLOBALS['oView']->assign( 'domainlist', json_encode($aTempArray) );
    	$GLOBALS['oView']->assign( 'domaingroup', $aGroupList );
    	$GLOBALS['oView']->assign( 'domainimageurl', $oConfig->getConfigs('domainbind_checkimage') );
    	$GLOBALS['oView']->assign( 'actionlink', array('text'=>'增加域名组','href'=>url('domaingroup','add') ) );
    	$GLOBALS['oView']->assign( 'actionlink2', array('text'=>'分配域名组','href'=>url('domaingroup','set') ) );
    	$GLOBALS['oView']->assign( 'ur_here', '域名状态');
    	$oDomainGroup->assignSysInfo();
    	$GLOBALS['oView']->display("domaingroup_list.html");
    	EXIT;
    }


    
	/**
     * 分配域名组
     * URL = ./?controller=domaingroup&action=set
     */
    function actionSet()
    {
    	$aLocation = array(
    				0 => array( 'text' => '返回: 分配域名组', 'href' => url('domaingroup', 'set') ),
    				1 => array( 'text' => '返回: 增加域名组', 'href' => url('domaingroup', 'add') ),
        			2 => array( 'text' => '返回: 域名状态', 'href' => url('domaingroup', 'list') )
    			);
    			
    	$oDomainGroup = new model_domaingroup();
    	
    	if ( $_POST['domain'] && $_POST['domaingroup'] )
    	{
    		
    		if ( $oDomainGroup->groupBind($_POST['domain'], $_POST['domaingroup'] , TRUE) )
    		{
    			sysMessage( '成功:分配域名组',0,$aLocation);
    			exit;	
    		}
    		else 
    		{
    			sysMessage('失败:分配域名组', 1, $aLocation);
    			exit;
    		}
    		
    	}
    	
    	$aGroupList   = $oDomainGroup->groupList();
		$oDomain = new model_domains();
		$aDomains = $oDomain->domainList(1);
		
    	$GLOBALS['oView']->assign( 'domaingroup', $aGroupList);
    	$GLOBALS['oView']->assign( 'domains', $aDomains);
    	$GLOBALS['oView']->assign( 'actionlink', array('text'=>'增加域名组','href'=>url('domaingroup','add') ) );
    	$GLOBALS['oView']->assign( 'actionlink2', array('text'=>'域名状态','href'=>url('domaingroup','list') ) );
    	$GLOBALS['oView']->assign( 'ur_here', '分配域名组');
		    $oDomainGroup->assignSysInfo();
    	$GLOBALS['oView']->display("domaingroup_set.html");
    	EXIT;
    }
    
    /**
     * 删除域名
     * URL = ./?controller=domain&action=del
     */
    function actionDel()
    {
    	$aLocation[0] = array("text" =>"增加域名组","href"=>url('domaingroup','add'));
    	$iDomainGroupid = $_GET['id'] ? intval($_GET['id']) : 0;
    	if( $iDomainGroupid == 0 )
    	{
    		sysMessage( '失败: 提交了空数据', 1, $aLocation );
    		exit;
    	}
    	
    	$oDomainGroup = A::singleton( 'model_domaingroup' );
    	if ( $oDomainGroup->groupDel( array($iDomainGroupid) ) )
    	{
    		sysMessage( '成功:删除域名组', 0, $aLocation );
    		exit;
    	}
    	else 
    	{
    		sysMessage( '失败:删除域名组', 1, $aLocation );
    		exit;
    	}
    }


}
?>