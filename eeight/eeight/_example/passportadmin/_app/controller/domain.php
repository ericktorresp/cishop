<?php
/**
 * 文件 : /_app/controller/domain.php
 * 功能 : 控制器 - 域名管理
 * 
 * 功能:
 *     - actionAdd()       增加域名
 *     - actionAssign()    关联域名
 *     - actionDel()		删除域名
 *     - actionEdit()		修改域名(域名总代列表)
 *     - actionList()		用户域名列表
 *     - actionSave()		保存域名
 *     - actionUnset()		回收域名
 *     - actionUpdate()    更新域名
 * 
 * @author	   Saul
 * @version   1.2.0
 * @package   passportadmin
 */

class controller_domain extends basecontroller
{
    /**
     * 关联域名
     * URL = ./?controller=domain&action=assign
     * @author SAUL
     */
    function actionAssign()
    {
        $aLocation = array( 0=>array('text' => '分配域名列表','href' => url( 'domain', 'list' ) ) );
    	$aDomian  = isset($_POST["domains"]) && is_array($_POST["domains"]) ? daddslashes($_POST["domains"]) :array();
    	$aUser[0] = isset($_POST["userid"]) && is_numeric($_POST["userid"]) ? intval($_POST["userid"]) : 0;
    	if( empty($aDomian) || $aUser[0]==0 )
    	{
    		sysMessage('操作失败:提交数据不全', 1, $aLocation );	
    	}
    	/* @var $oAgent model_agent */
    	$oAgent  = A::singleton('model_agent');
    	foreach( $aDomian as $iDomain )
    	{
    		$oAgent->userDomainAdd( $iDomain, $aUser );
    	}
    	//TODO _a高频、低频并行前期临时程序
		/*if( isset($GLOBALS['aSysDbServer']['gaopin']) )
		{
		    $oAgent->synUrl( $GLOBALS['aSysDbServer']['gaopin'] );
		}*/
    	sysMessage('操作成功', 0, $aLocation );
    }



    /**
     * 增加域名
     * URL = ./?controller=domain&action=add
     * @author SAUL
     */
    function actionAdd()
    {
    	$GLOBALS["oView"]->assign( "ur_here",    "增加域名");
    	$GLOBALS['oView']->assign( "actionlink", array('text'=>'分配域名','href'=>url('domain','list')));
    	$GLOBALS["oView"]->display("domain_info.html");
    	EXIT;
    }
	

    /**
     * 保存域名
     * URL = ./?controller=domain&action=save
     * 
     * 1/6/2011 增加批量新增功能
     */
    function actionSave()
    {
    	$sDomain = isset($_POST["domain"]) ? strtolower(daddslashes( $_POST["domain"])) : "";
    	$sPDomain = isset($_POST["domainlist"]) ? strtolower(daddslashes( $_POST["domainlist"])) : "";
    	
    	$aLocation = array(
    		0=>array( 'text' => '返回: 增加域名',	'href' => url('domain','add')),
        	1=>array( 'text' => '返回: 域名管理列表', 'href' => url('domain','list'))
    	);
    	
    	/* @var $oDomain model_domains */
    	$oDomain = A::singleton('model_domains');
    	
    	if ( $sDomain == ''  && $sPDomain )
    	{
    		
	    	$aDomain = explode(',', $sPDomain);
	    	$sReturnMsg = '';
	    	foreach ( $aDomain AS $sDomain )
	    	{
	    		if ( preg_match("/\\s+/", $sDomain) )
	    		{
	    			$sDomain = preg_replace("/\\s+/", '', $sDomain);
	    		}
	    		$sDomain = strtolower( trim( $sDomain ) );
	    		$iFlag   = $oDomain->domainAdd( $sDomain );
	    		switch ($iFlag)
    			{
    				case -1:
    		    		$sReturnMsg .= ' ('.$sDomain.' 失败:参数不全)';
    		    		break;
    				case 0:
    		    		$sReturnMsg .= ' ('.$sDomain.' 失败:域名已经存在)';
    					break;
    				default:
    					$sReturnMsg .= ' ('.$sDomain.' 添加成功)';
    					break;
    			}
    			
	    	}
	    	
	    	sysMessage($sReturnMsg, 0, $aLocation);
    		exit;
    			
    	}
    	else 
    	{
    		$iFlag   = $oDomain->domainAdd( $sDomain );
    		switch ($iFlag)
    		{
    			case -1:
    		    	sysMessage('操作失败: 参数不全', 1, $aLocation);
    		    	break;
    			case 0:
    		    	sysMessage('操作失败: 域名已经存在。', 1, $aLocation);
    				break;			
    			default:
    				sysMessage('操作成功', 0, $aLocation);
    				break;
    		}
    	}
    	
    	
    }



    /**
     * 用户域名列表
     * URL = ./?controller=domain&action=list
     * @author SAUL
     */
    function actionList()
    {
    	$iAgentId  = isset($_GET["userid"])&&is_numeric($_GET["userid"])      ? intval($_GET["userid"])   : 0;
    	$iDomainId = isset($_GET["domainid"])&&is_numeric($_GET["domainid"])  ? intval($_GET["domainid"]) : 0;
    	$iPage     = isset($_GET["p"])&&(is_numeric($_GET["p"]))              ? intval($_GET["p"])        : 1;
    	$sWhere    = " 1 ";
    	if( $iAgentId >0 ) 
    	{
    		$sWhere .=" AND `userdomain`.`userid`='".$iAgentId."'";
    	}
    	elseif( $iDomainId >0 )
    	{
    		$sWhere .= " AND `userdomain`.`domainid`='".$iDomainId."'";
    	}
    	$oAgent   = new model_agent();
    	$oUser    = new model_user();
    	$iAdmin   = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]) : 0;
    	if( !$oUser->checkAdminForUser($iAdmin,0) )
    	{
    		$sWhere .= " AND `userdomain`.`userid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdmin."')";
    	}
    	$agents   = $oAgent->agentList();//所有总代列表
    	$aAgent   = array();
    	foreach($agents as $agent)
    	{
    		$aAgent[$agent["userid"]] = $agent["username"];
    	}
    	$oDomain  = new model_domains();	
    	$aDomains = $oDomain->domainList();//所有的域名列表
    	$iNums    = 0;
    	$aDomainL = $oAgent->domianList($sWhere, $iNums ,$iPage);
    	$oPager   = new pages($iNums, 15, 15);
    	
    	//  分组机制检查开始 1/4/2011
    	// 读取系统参数
    	$oConfig = new model_config();
    	$iSysParam1 = $oConfig->getConfigs('domainbind_seemgroup');
    	$iSysParam2 = $oConfig->getConfigs('domainbind_seemdomain'); 
    	
    	// 提取域名ID，获取域名分组情况
    	$oDomainGroup = new model_domaingroup();
    	$oDomainGroup->SysParam = array($iSysParam1, $iSysParam2);
		$aSearchParam = array();
		//提取所有总代分组索引，准备搜索组
		$aAllDomainList = $oAgent->domianList($sWhere, $iNums ,$iPage, true);
		foreach ($aAllDomainList AS $sKey => $aADL)
		{
			$aSearchParam[$sKey] = $oDomainGroup->getGroupArray( $aADL );
		}
		// 所有总代ID
		$aAgentLists = $oDomainGroup->_allAgentList();
		
    	foreach ( $aDomainL AS $iKey => &$aSin)
    	{
    		$aTemp = $oDomainGroup->GetGroupbyDomain( array_values( array_keys($aSin) ) );
			//已分配域名存在同组情况
			$aSin['checkseemgroup'] = ( count($aSin) != count($aTemp)  && $iSysParam1 == 1) ? 1 : 0;
    		//与其他总代存在子集或类同
    		$aCheckSeem = $oDomainGroup->diffArray($aTemp, $aSearchParam, $iKey, $aAgentLists);
    		$aSin['checkseemdomain'] = $aCheckSeem ? 1 : 0;
    		// 与其重复的总代列表
    		$aSin['seemagentlist'] = $aCheckSeem ? array_unique($aCheckSeem) : '';
    	}
    	unset($oConfig,$oDomain,$oDomainGroup,$aSearchParam,$aAgentLists);
    	// end:1/4/2011
    	
    	$GLOBALS['oView']->assign( 'pageinfo',   $oPager->show() );
    	$GLOBALS['oView']->assign( 'agents',     $aAgent);
    	$GLOBALS['oView']->assign( 'domains',    $aDomains);
    	$GLOBALS['oView']->assign( 'userdomain', $aDomainL);
    	$GLOBALS['oView']->assign( 'agentid',    $iAgentId);
    	$GLOBALS['oView']->assign( 'actionlink', array('text'=>'增加域名','href'=>url('domain','add')));
    	$GLOBALS['oView']->assign( 'actionlink2', array('text'=>'域名状态','href'=>url('domaingroup','list')));
    	$GLOBALS['oView']->assign( 'domainid',   $iDomainId);
    	$GLOBALS['oView']->assign( 'ur_here',    "分配域名");
    	$oAgent->assignSysInfo();
    	$GLOBALS['oView']->display("domain_list.html");
    	EXIT;
    }



    /**
     * 回收域名
     * URL = ./?controller=domain&action=unset
     * @author SAUL
     */
    function actionUnset()
    {
    	$aEntry = isset($_POST["entrys"])&&is_array($_POST["entrys"]) ? daddslashes($_POST["entrys"]) :array();
    	$aLocation = array( 0=>array('text' => '返回: 分配域名列表','href' => url( 'domain', 'list' ) ) );
    	if( empty($aEntry) )
    	{
    		sysMessage('操作失败: 没有提交数据', 1, $aLocation);
    	}
    	/* @var $oAgent model_agent */
    	$oAgent = A::singleton('model_agent');
    	if($oAgent->delDomain($aEntry))
    	{
    		//TODO _a高频、低频并行前期临时程序
    		/*if(isset($GLOBALS['aSysDbServer']['gaopin']))
    		{
    			$oAgent->synUrl( $GLOBALS['aSysDbServer']['gaopin'] );
    		}*/
    		sysmessage( "操作成功", 0, $aLocation );
    	}
    	else 
    	{
    	   //TODO _a高频、低频并行前期临时程序
           /* if(isset($GLOBALS['aSysDbServer']['gaopin']))
            {
                $oAgent->synUrl( $GLOBALS['aSysDbServer']['gaopin'] );
            }*/
    		sysMessage( '操作失败', 1, $aLocation );
    	}
    }



    /**
     * 删除域名
     * URL = ./?controller=domain&action=del
     * @author SAUL
     */
    function actionDel()
    {
    	$aLocation[0] = array("text" =>"域名列表","href"=>url('domain','list'));
    	$aDomainId = isset($_POST["domains"]) && is_array($_POST["domains"]) ? daddslashes($_POST["domains"]) : 0;
    	if( empty($aDomainId) )
    	{
    		sysMessage( '操作失败: 没有域名需要删除', 1, $aLocation );
    	}
    	/* @var $oDomain model_domains */
    	$oDomain = A::singleton('model_domains');
    	$oDomain->domainDel( $aDomainId );
    	//TODO _a高频、低频并行前期临时程序
       /* if( isset($GLOBALS['aSysDbServer']['gaopin']) )
        {*/
        	/* @var $oAgent model_agent */
           /*  $oAgent  = A::singleton("model_agent");
            $oAgent->synUrl( $GLOBALS['aSysDbServer']['gaopin'] );
        }*/
    	sysMessage('操作成功', 0, $aLocation);
    }


    
    /**
     * 修改域名(域名总代列表)
     * URL = ./?controller=domian&action=edit
     * @author SAUL 090616
     * 完成度:100%
     */
    function actionEdit()
    {		
    	$aDomains = isset($_POST["domains"])&&is_array($_POST["domains"]) ? daddslashes($_POST["domains"]) :array();
    	if( empty($aDomains) )
    	{
    		sysMessage('没有选择域名', 1 );
    	}
    	/* @var $oDomain model_domains */
    	$oDomain = A::singleton('model_domains');
    	$aDomain = $oDomain->domainUserList($aDomains);
    	$aDomainList = array();
    	foreach( $aDomain as $domain )
    	{
    		$aDomainList[$domain["id"]] = $domain["domain"];
    	}
    	/* @var $oAgent model_agent */
    	$oAgent     = A::singleton('model_agent');
    	$aAgents    = $oAgent->agentList();
    	$aAgentList = array();
    	foreach( $aAgents as $agent )
    	{
    		$aAgentList[$agent["userid"]]["username"] = $agent["username"];
    	}
    	unset($aAgents);
    	$aAgents = $oAgent->getAgentByDomain($aDomains);
    	foreach( $aAgents as $agent ) 
    	{
    		if( isset($aAgentList[$agent['userid']]) )
    		{
    			$aAgentList[$agent['userid']]["domian"][$agent["domainid"]] =1;
    		}
    	}
    	
    	
    	//  分组机制检查开始 1/4/2011
    	// 同组域名不能应用于分配
    	// 读取系统参数
    	$oConfig = new model_config();
    	// 提取域名ID，获取域名分组情况
    	if ( $oConfig->getConfigs('domainbind_seemgroup') == 1 && count($aDomainList)>1 )
    	{
    		$oDomainGroup = new model_domaingroup();
			$aNowDomainGroup = $oDomainGroup->GetGroupbyDomain( array_keys($aDomainList) );
    		if ( count($aNowDomainGroup) !=  count($aDomainList)  
    			|| count($aNowDomainGroup) == 1 )
    		{
    			sysMessage( '失败:系统限制同组域名不能使用于分配', 0);
    			exit;	
    		}
    		//print_rr($aNowDomainGroup );
    	}
    	
    	$GLOBALS['oView']->assign("domain",     $aDomainList);		
    	$GLOBALS['oView']->assign("agents",     $aAgentList);
    	$GLOBALS['oView']->assign('actionlink', array('text'=>'分配域名','href'=>url('domain','list')));
    	$GLOBALS['oView']->assign("ur_here",    "修改域名");
    	$oDomain->assignSysInfo();
    	$GLOBALS['oView']->display("domain_edit.html");
    	EXIT;
    }



    /**
     * 更新域名
     * url = ./?controller=domain&action=update
     * @author SAUL
     */
    function actionUpdate()
    {
    	$aDomians = isset($_POST["domains"])&&is_array($_POST["domains"]) ? daddslashes($_POST["domains"]) : array();
    	$aDomian   = isset($_POST["domain"])&&is_array($_POST["domain"]) ? daddslashes($_POST["domain"]) :array();
    	$aLocation = array( 0=>array('text' => '返回: 分配域名列表','href' => url( 'domain', 'list' ) ) );
    	if( empty($aDomians) )
    	{
    		sysMessage('操作失败:没有提交数据', 1, $aLocation);
    	}
    	/* @var $oAgent model_agent */
    	$oAgent = A::singleton('model_agent');
    	$bSuccess = TRUE;
    	foreach( $aDomians as $iDomainId )
    	{		
    		$aDomian[$iDomainId] = isset($aDomian[$iDomainId]) ? $aDomian[$iDomainId] :array();			
    		$bResult = $oAgent->domianUpdate( $iDomainId, $aDomian[$iDomainId] );
    		$bSuccess = $bSuccess && $bResult;
    	}
    	//TODO _a高频、低频并行前期临时程序
        /*if( isset($GLOBALS['aSysDbServer']['gaopin']) )
        {
            $oAgent->synUrl( $GLOBALS['aSysDbServer']['gaopin'] );
        }*/
    	if( $bSuccess )
    	{
    		sysMessage('操作成功', 0, $aLocation);
    	}
    	else
    	{
    		sysMessage('操作失败', 1, $aLocation);
    	}	
    }
}
?>