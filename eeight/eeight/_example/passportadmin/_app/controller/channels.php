<?php
/**
 * 文件 : /_app/controller/channels.php
 * 功能 : 控制器 - 频道管理
 * 
 * 功能:
 *    - actionAdd()                增加频道
 *    - actionAssign()             给总代分配频道
 *    - actionDel()                删除频道
 *    - actionDisable()            禁用频道
 * 	  - actionDisableall()         禁用所有频道
 *    - actionEdit()               修改频道
 * 	  - actionEnable()             启用频道
 *    - actionEnableall()          启用所有频道
 *    - actionList()               频道列表
 *    - actionSaveuserchannel()    频道激活
 * 
 * @author     SAUL, James, Tom   090914
 * @version    1.2.0
 * @package    passportadmin
 */
class controller_channels extends basecontroller 
{
	/**
	 * 频道列表
	 * URL = ./?controller=channels&action=list
	 * @author SAUL
	 */
    function actionList()
    {
        /* @var $oChannels model_channels */
    	$oChannels = A::singleton('model_channels');
    	$aChannels = $oChannels->channelList();
    	$GLOBALS['oView']->assign( "channels",   $aChannels );
    	$GLOBALS['oView']->assign( "ur_here",    "频道列表" );
    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("channels","add"), 'text'=>'增加频道' ) );
    	$oChannels->assignSysInfo();
    	$GLOBALS['oView']->display( "channels_list.html" );
    	EXIT;
    }


    
    /**
     * 禁用频道
     * URL = ./?controller=channels&action=list
     * @author SAUL
     */
    function actionDisable()
    {
        $aLocation  = array( 0=>array( "text" => "返回: 频道列表", "href" => url( "channels" , "list" )));
    	$iChannelId = isset($_GET["id"])&&(is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
    	if( $iChannelId == 0 )
    	{
    		redirect( url("channels","list") );
    	}
    	/* @var $oChannels model_channels */
    	$oChannels = A::singleton('model_channels');
    	if( $oChannels->channelClose($iChannelId) )
    	{
    		sysMessage('操作成功', 0, $aLocation );
    	}
    	else
    	{
    		sysMessage('操作失败', 1, $aLocation );
    	}
    }


    
    /**
     * 启用频道
     * URL = ./?controller=channels&action=Enable
     * @author SAUL
     */
    function actionEnable()
    {
        $aLocation  = array( 0=>array( "text" => "返回: 频道列表","href" => url( "channels" , "list" )));
    	$iChannelId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
    	if( $iChannelId == 0 )
    	{
    		redirect(url("channels","list"));
    	}
    	/* @var $oChannels model_channels */
        $oChannels = A::singleton('model_channels');
    	if( $oChannels->channelOpen($iChannelId) )
    	{
    		sysMessage('操作成功', 0, $aLocation);
    	}
    	else
    	{
    		sysMessage('操作失败',1, $aLocation);
    	}
    }


    
    /**
     * 修改频道
     * URL = ./?controller=channels&action=edit
     * @author SAUL
     */
    function actionEdit()
    {
    	if( isset($_POST) && !empty($_POST) )
    	{
    		$aLocation  = array( 0=>array( "text" => "返回: 频道列表" ,"href" => url( "channels" , "list" )));
    		$iChannelId = ( isset($_POST["channelid"]) && is_numeric($_POST["channelid"]) )?intval( $_POST["channelid"]) : 0;
    		if( $iChannelId == 0 )
    		{
    			redirect( url( "channels" , "list" ) );
    		}
    		/* @var $oChannels model_channels */
            $oChannels = A::singleton('model_channels');
    		$iResult = $oChannels->channelUpdate( $_POST, "`id`='".$iChannelId."'");
    		switch ($iResult)
    		{
    			case -1:
    				sysMessage( '操作失败:数据错误.', 1, $aLocation );
    				break;
    			case -2:
    				sysMessage( '操作失败:频道名称为空.', 1, $aLocation );
    				break;
    			case -3:
    				sysMessage( '操作失败:频道组ID错误.', 1, $aLocation );
    				break;
    			case -4:
    				sysMessage( '操作失败:频道组不存在.', 1, $aLocation );
    				break;
    			case -5:
    				sysMessage( '操作失败:频道路径错误.', 1, $aLocation );
    				break;
    			case -6:
    				sysMessage( '操作失败:修改失败.', 1, $aLocation );
    				break;
    			default:
    				sysMessage( '操作成功', 0, $aLocation);
    				break;			
    		}
    	}
    	else
    	{
    		$iChannelId = isset($_GET["id"]) && is_numeric($_GET["id"] ) ? intval($_GET["id"]) : 0;
    		/* @var $oChannels model_channels */
            $oChannels = A::singleton('model_channels');
    		$mChannel   = $oChannels->channelGet( $iChannelId );
    		if( $mChannel == -1 )
    		{
    			redirect( url( "channels" , "list" ) );
    		}
    		$aChannels = $oChannels->channelGetAll('*',"`pid`='0'");
    		$GLOBALS['oView']->assign( "channel", $aChannels );
    		$GLOBALS['oView']->assign( "channels", $mChannel );
    		$GLOBALS['oView']->assign( "ur_here",  "修改频道" );
    		$GLOBALS['oView']->assign( "action",   "edit");
    		$GLOBALS['oView']->assign( "actionlink", array( "href" => url( 'channels', 'list' ), "text" =>'频道列表') );
    		$oChannels->assignSysInfo();
    		$GLOBALS['oView']->display( "channels_info.html" );
    		EXIT;
    	}
    }


    
    /**
     * 删除一个频道
     * URL = ./?controller=channels&action=del
     * @author  SAUL
     */
    function actionDel()
    {
        $aLocation  = array( 0 => array( "text" => "返回: 频道列表" , "href" => url( "channels" , "list" )));
    	$iChannelId = (isset($_GET["id"])&&is_numeric($_GET["id"])) ? intval($_GET["id"]) : 0;
    	if( $iChannelId == 0 )
    	{
    		redirect(url("channels","list"));
    	}
    	/* @var $oChannels model_channels */
        $oChannels = A::singleton('model_channels');
    	if( $oChannels->channelDel( $iChannelId ) )
    	{
    		sysMessage( '操作成功', 0, $aLocation );
    	}
    	else
    	{
    		sysMessage( '操作失败', 1, $aLocation );
    	}	
    }


    
    /**
     * 增加频道
     * URL = ./?controller=channels&action=add
     * @author SAUL
     */
    function actionAdd()
    {
    	$aLocation[0] = array( "text" => "频道列表", "href" => url( "channels" , "list" ));
    	if(isset($_POST)&&!empty($_POST))
    	{
    	    /* @var $oChannels model_channels */
            $oChannels = A::singleton('model_channels');
    		$iFlag	   = $oChannels->channelAdd( $_POST );
    		$aLocation[1] = array( "text"=>'增加频道列表', "href"=>url("channels","add"));
    		switch ( $iFlag ) 
    		{
    			case 0:
    				sysMessage('操作失败:增加失败。', 1, $aLocation);
    			    break;
    			case -1:
    				sysMessage('操作失败:错误错误。', 1, $aLocation);
    			    break;
    			case -2:
    				sysMessage('操作失败:栏目名称错误。', 1, $aLocation );
    			    break;
    			case -3:
    				sysMessage('操作失败:栏目名称已经存在.', 1, $aLocation);
    				break;
    			case -4:
    				sysMessage('操作失败:栏目路径错误.', 1, $aLocation);
    				break;
    			case -5:
    				sysMessage('操作失败:栏目路径已经存在.', 1, $aLocation);
    				break;
    			case -6:
    				sysMessage('操作失败:栏目组ID 错误.', 1, $aLocation);
    				break;	
    			default:
    				sysMessage('操作成功', 0, $aLocation);
    			    break;
    		}
    	}
    	else
    	{
    	    /* @var $oChannels model_channels */
            $oChannels = A::singleton('model_channels');
    		$aChannels = $oChannels->channelGetAll('*',"`pid`='0'");
    		$GLOBALS["oView"]->assign("channel",$aChannels);
    		$GLOBALS['oView']->assign( "ur_here",    "增加频道");
    		$GLOBALS['oView']->assign( "actionlink",  $aLocation[0] );
    		$GLOBALS["oView"]->assign(	"action","add");
    		$GLOBALS['oView']->display("channels_info.html");
    		EXIT;
    	}
    }


    
    /**
     * 启用所有频道
     * URL = ./?controller=channels&action=enableall
     * @author SAUL
     */
    function actionEnableall()
    {
        /* @var $oChannels model_channels */
        $oChannels = A::singleton('model_channels');
    	if($oChannels->channelAllopen())
    	{
    		sysMessage( '操作成功', 0 );
    	}
    	else
    	{
    		sysMessage( '操作失败', 1 );
    	}
    }


    
    /**
     * 禁用所有频道
     * URL = ./?controller=channels&action=disableall
     * @author SAUL
     */
    function actionDisableall()
    {
        /* @var $oChannels model_channels */
        $oChannels = A::singleton('model_channels');
    	if( $oChannels->channelAllclose() )
    	{
    		sysMessage( '操作成功' , 0 );
    	}
    	else
    	{
    		sysMessage( '操作失败' , 1 );
    	}
    }


    
    /**
     * 总代分配频道(查看)
     * URL = ./?controller=channels&action=assign
     * @author SAUL
     */
    function actionAssign()
    {
        /* @var $oAgent model_agent */
    	$oAgent    = A::singleton('model_agent');
    	$aAgents   = $oAgent->agentList();
    	/* @var $oChannels model_channels */
        $oChannels = A::singleton('model_channels');
    	$aChannels = $oChannels->channelGetAll( '*', ' `pid`=0 AND `isdisabled`=0 ' );
    	$aUsers    = array();
    	foreach( $aAgents as $agent )
    	{
    		$aUsers[] = $agent["userid"];
    	}
    	$aChannel  = array();
    	foreach( $aChannels as $channel )
    	{
    		$aChannel[] = $channel["id"];
    	} //用户总代的数组
     	$aUserChannel = $oAgent->userChannel( $aUsers , $aChannel );
     	if( !empty($aUserChannel) )
     	{
     		$aUserchannels=array();
    	 	foreach( $aUserChannel as $value )
    	 	{
    	 		$aUserchannels[$value["userid"]][$value["channelid"]] = $value["isdisabled"];
    	 	}
    	 	$GLOBALS['oView']->assign("Userchannel", $aUserchannels);
     	}
    	$GLOBALS['oView']->assign("ur_here",  "分配频道");
    	$GLOBALS['oView']->assign("agents",   $aAgents);
    	$GLOBALS['oView']->assign("channels", $aChannels);
    	$GLOBALS['oView']->assign("count",    count($aChannels));
    	$oAgent->assignSysInfo();
    	$GLOBALS['oView']->display("channels_assign.html");
    	EXIT;
    }
    
    
    
    /**
     * 频道激活
     * URL = ./?controller=channels&action=saveuserchannel
     * @author James, Tom
     */
    function actionSaveuserchannel()
    {
        // 1, 存在 GET 方式的访问, 则激活某个总代的某个频道资金账户
        if( !empty($_GET['ajax']) )
        {
            if( empty($_GET['uid']) || empty($_GET['cid']) 
                    || !is_numeric($_GET['uid']) || !is_numeric($_GET['cid']) )
            {
                die('error');
            }
            $iTopProxyId = intval($_GET['uid']);
            $iChannelid  = intval($_GET['cid']);
    
            // 1, 判断 ID 是否为总代
            /* @var $oUser model_user */
            $oUser = A::singleton('model_user');
            if( FALSE == $oUser->isTopProxy( $iTopProxyId ) )
            { // 非总代则中断
                die('error');
            }
            unset($oUser);
            // 2, 发送 API 请求, 返回处理结果
            /* @var $oDomain model_domains */
            $oDomain     = A::singleton('model_domains');
            $aResult     = $oDomain->domainGetOne( 'domain', " `status`='1' LIMIT 1 " );
            if( empty($aResult) )
            {
            	die('error');
            }
            $oChannelApi = new channelapi( $iChannelid, 'activeUserFund', TRUE );
    	    $oChannelApi->setResultType('serial');
    	    $oChannelApi->setBaseDomain( $aResult['domain'] );
    	    $oChannelApi->sendRequest( array('iTopProxyId'=>$iTopProxyId ) );
    	    $a = $oChannelApi->getDatas();
    	    if( empty($a) || !is_array($a) || $a['status'] == 'error' )
    	    {
    	    	die("error".serialize($a));
    	    }
    	    
    	    $oUserChannel = new model_userchannel();
    	    $mResult      = $oUserChannel->insert( $iTopProxyId, $iChannelid, 1, '', '', true );
    	    if( $mResult == FALSE )
    	    {
    	    	die("error");
    	    }
    	    die("ok");
        }
        // 2, GET方式开启频道
        elseif( !empty($_GET) && $_GET['flag'] == 'open' )
        {
            if( empty($_GET['uid']) || empty($_GET['cid']) 
                    || !is_numeric($_GET['uid']) || !is_numeric($_GET['cid']) )
            {
                die('error');
            }
    
            $iTopProxyId  = intval($_GET['uid']);
            $iChannelid   = intval($_GET['cid']);
            /* @var $oUserChannel model_userchannel */
            $oUserChannel = A::singleton('model_userchannel');
            $bResult      = $oUserChannel->openCloseTopProxyUserChannel( $iTopProxyId, $iChannelid, FALSE );
            if( $bResult == FALSE )
            {
            	die("error");
            }
            die("ok");
        }
        // 3, GET方式禁用频道
        elseif( !empty($_GET) && $_GET['flag'] == 'close' )
        {
            if( empty($_GET['uid']) || empty($_GET['cid']) 
                    || !is_numeric($_GET['uid']) || !is_numeric($_GET['cid']) )
            {
                die('error');
            }
            
            $iTopProxyId  = intval($_GET['uid']);
            $iChannelid   = intval($_GET['cid']);
            /* @var $oUserChannel model_userchannel */
            $oUserChannel = A::singleton('model_userchannel');
            $bResult      = $oUserChannel->openCloseTopProxyUserChannel( $iTopProxyId, $iChannelid, TRUE );
            if( $bResult == FALSE )
            {
                die("error");
            }
            die("ok");
        }
    	die('error #1015');	
    }
}
?>