<?php
/**
 * 文件 : /_app/controller/message.php
 * 功能 : 控制器 - 消息管理
 * 
 * @author	   Tom    090914
 * @version   1.2.0
 * @package   passportadmin
 */
class controller_message extends basecontroller
{
	/**
	 * 查看消息列表
	 * URL = ./?controller=message&action=list
	 * @author Tom 090515
	 */
	function actionList()
	{
	    // 整理搜索条件
	    $aSearch['mt']         = isset($_GET['mt']) && is_numeric($_GET['mt']) ? intval($_GET['mt']) : "";
	    $aSearch['isread']     = isset($_GET['isread']) && is_numeric($_GET['isread']) ? $_GET['isread'] : 0;
	    $aSearch['isdel']      = isset($_GET['isdel']) && is_numeric($_GET['isdel']) ? $_GET['isdel'] : 0;
	    $aSearch['subject']    = isset($_GET['subject']) ? daddslashes(trim($_GET['subject'])) : "";
	    $aSearch['sdate']      = isset($_GET['sdate']) ? trim($_GET['sdate']) : date('Y-m-d 00:00', strtotime('-1 month') ); // 最近一个月
	    $aSearch['edate']      = isset($_GET['edate']) ? trim($_GET['edate']) : "";
	    $aSearch['sdate']      = getFilterDate( $aSearch['sdate'] );
	    $aSearch['edate']      = getFilterDate( $aSearch['edate'] );
	    $aSearch['sendergrp']  = isset($_GET['sendergrp']) && is_numeric($_GET['sendergrp']) ? intval($_GET['sendergrp']) : "";
	    $aSearch['receivegrp'] = isset($_GET['receivegrp']) && is_numeric($_GET['receivegrp']) ? intval($_GET['receivegrp']) : "";
        $aSearch['sendername'] = isset($_GET['sendername']) ? daddslashes(trim($_GET['sendername'])) : "";
        $aSearch['receivename']= isset($_GET['receivename']) ? daddslashes(trim($_GET['receivename'])) : "";
        $aHtml['sendername'] =  h(stripslashes_deep($aSearch['sendername']));
	    $aHtml['receivename']=  h(stripslashes_deep($aSearch['receivename']));
	    // 02, WHERE 语句拼接
	    $sWhere = ' 1 '; // WHERE 条件变量声明
	    if( $aSearch['isdel'] == 1 )
	    { // 已删标记
	        $sWhere .= " AND `deltime` IS NOT NULL AND `deltime` > 0 ";
	    }
	    if( $aSearch['isdel'] == 0 )
	    {
	        $sWhere .= " AND ( `deltime` IS NULL OR `deltime` = 0 ) ";
	    }
	    
	    if( $aSearch['isread'] == 1 )
	    { // 已读标记
	        $sWhere .= " AND `readtime` IS NOT NULL AND `readtime` > 0 ";
	    }
	    if( $aSearch['isread'] == 0 )
	    {
	        $sWhere .= " AND ( `readtime` IS NULL OR `readtime` = 0 )  ";
	    }

	    if( $aSearch['sendergrp'] === 0 )
	    { // 发送者为用户组, 可继续模糊搜索用户名
	        $sWhere .= " AND `sendergroup` = 0 ";
	    }
	    if( $aSearch['sendergrp'] === 1 )
	    {
	        $sWhere .= " AND `sendergroup` = 1 ";
	    }
	    if( $aSearch['sendergrp']===0 && $aSearch['sendername']!='' )
	    { // 发送组为用户组, 存在用户名..
	        $oUsers = new model_user();
	        $aUserids = $oUsers->getUseridByUsername( $aSearch['sendername'] );
	        if( !is_array($aUserids) || empty($aUserids) )
	        {
	            $aHtml['sendername'] = '';
	        }
	        else 
	        {
	            $sTmpString = '';
    	        foreach( $aUserids AS $v )
	            {
	                if( isset($v['userid']) && is_numeric($v['userid']) )
	                {
	                    $sTmpString .= $v['userid'].',';
	                }
	            }
    	        if( substr($sTmpString, -1, 1) == ',' )
    	        {
    	            $sTmpString = substr( $sTmpString, 0, -1 );
    	        }
    	        if( $sTmpString != '' )
    	        {
    	            $sWhere .= " AND `senderid` in ($sTmpString) ";
    	        }
    	        unset($sTmpString,$aUserids);
	        }
	    }
	    else 
	    {
	        $aHtml['sendername'] = '';
	    }
	    
	    if( $aSearch['receivegrp'] === 0 )
	    { // 接收为用户组, 可继续模糊搜索用户名
	        $sWhere .= " AND `receivergroup` = 0 ";
	    }
	    if( $aSearch['receivegrp'] === 1 )
	    {
	        $sWhere .= " AND `receivergroup` = 1 ";
	    }
	    if( $aSearch['receivegrp']===0 && $aSearch['receivename']!='' )
	    { // 接收组为用户组, 存在用户名..
	        $oUsers = new model_user();
	        $aUserids = $oUsers->getUseridByUsername( $aSearch['receivename'] );
	        if( empty($aUserids) )
	        {
	            $aHtml['receivename'] = '';
	        }
	        else 
	        {
	            $sTmpString = '';
	            if( is_numeric($aUserids) )
	            {
	            	$sWhere .= " AND `receiverid` = ".intval($aUserids)." ";
	            }
	            else
	            {
	    	        foreach( $aUserids AS $v )
		            {
		                if( isset($v['userid']) && is_numeric($v['userid']) )
		                {
		                    $sTmpString .= $v['userid'].',';
		                }
		            }
	    	        if( substr($sTmpString, -1, 1) == ',' )
	    	        {
	    	            $sTmpString = substr( $sTmpString, 0, -1 );
	    	        }
		            if( $sTmpString != '' )
	                {
	                    $sWhere .= " AND `receiverid` IN ($sTmpString) ";
	                }
	            }
    	        unset($sTmpString,$aUserids);
	        }
	    }
	    else 
	    {
	        $aHtml['receivename'] = '';
	    }

	    if( $aSearch['sdate'] != '' )
	    {
	        $sWhere .= " AND ( `sendtime` >= '".daddslashes($aSearch['sdate'])."' ) ";
	        $aSearch['sdate']  =  stripslashes_deep($aSearch['sdate']);
	    }
		if( $aSearch['edate'] != '' )
	    {
	        $sWhere .= " AND ( `sendtime` <= '".daddslashes($aSearch['edate'])."' ) ";
	        $aSearch['edate']  =  stripslashes_deep($aSearch['edate']);
	    }
	    
	    if( $aSearch['subject'] != '' )
	    {
	        if( strstr($aSearch['subject'],'*') )
	        {
	            $sWhere .= " AND `subject` LIKE '". str_replace( '*', '%', $aSearch['subject'] ) ."' ";
	        }
	        else 
	        {
	            $sWhere .= " AND `subject` = '".$aSearch['subject']."' ";
	        }
	        $aHtml['subject'] = h(stripslashes_deep($aSearch['subject']));
	    }
	    
	    $oMessage = new model_message();
	    if( $aSearch['mt'] === '' )
	    { // 全部消息列表
	        $aAdminMenus = $oMessage->getMessageTypeByAdminMenus( 'arr' );
	        $sAdminMenus = '';
	        if( is_array($aAdminMenus) && !empty($aAdminMenus) )
	        {
	            foreach( $aAdminMenus AS $v )
	            {
	                if( is_numeric($v) )
	                {
	                    $sAdminMenus .= $v.',';
	                }
	            }
	        }
	        if( substr($sAdminMenus, -1, 1) == ',' )
	        {
	            $sAdminMenus = substr( $sAdminMenus, 0, -1 );
	        }
	        if( $sAdminMenus == '' )
	        {
	            $sWhere .= " AND 0 ";
	        }
	        else 
	        {
	            $sWhere .= " AND t.`menuid` in ($sAdminMenus) ";
	        }
	    }
	    if( $aSearch['mt'] !== '' )
	    { // 频道ID
	        $sWhere .= " AND t.`menuid` = '".$aSearch['mt']."' ";
	    }
	    $aHtml['mt']          = $aSearch['mt'] !== '' ? $aSearch['mt'] : -1;
	    $aHtml['isread']      = $aSearch['isread'] == -1 ? -1 : $aSearch['isread'];
	    $aHtml['isdel']       = $aSearch['isdel'] == -1 ? -1 : $aSearch['isdel'];
	    $aHtml['sdate']       = getFilterDate($aSearch['sdate'],"Y-m-d H:i");
	    $aHtml['edate']       = getFilterDate($aSearch['edate'],"Y-m-d H:i");
	    $aHtml['sendergrp']   = $aSearch['sendergrp'] !== '' ? $aSearch['sendergrp'] : -1;
	    $aHtml['receivegrp']  = $aSearch['receivegrp'] !== '' ? $aSearch['receivegrp'] : -1;

	    // 根据管理员权限, 解析 '消息类型'
	    $aHtml['mtoptions'] = $oMessage->getMessageTypeByAdminMenus( 'opts', $aSearch['mt'] );
	    $p = isset($_GET['p']) ? intval($_GET['p']) : 0;
	    $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;
	    $aHtml['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25; 
		
        $aResult = $oMessage->getMessageList('*', $sWhere, $aHtml['pn'] , $p);
        $oPager = new pages( $aResult['affects'], $aHtml['pn'], 10);
        $GLOBALS['oView']->assign( 'pages', $oPager->show() );
        $GLOBALS['oView']->assign( 'list', $aResult['results'] ); // 数据分配
		$GLOBALS['oView']->assign("ur_here","消息列表");
		$GLOBALS['oView']->assign("s", $aHtml);
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("message","list"), 'text'=>'清空过滤条件' ) );
		$GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("message","add"), 'text'=>'增加消息' ) );
		$oMessage->assignSysInfo();
		$GLOBALS['oView']->display("message_list.html");
		EXIT;
	}



    /**
     * 删除消息
     * URL = ./?controller=message&action=del
	 * @author Tom 090515
     */
	function actionDel()
	{
	    $aLocation = array(0=>array("text" => "消息列表","href" => url("message","list")));
	    if( isset($_POST['form_action']) && $_POST['form_action']=='del' )
	    { // 处理批量删除
	        if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) || empty($_POST['checkboxes']) )
            {
                sysMessage("未选择数据", 1, $aLocation);
            }
	        $oMessage = new model_message();
	        if( $oMessage->delAdminMessage( $_POST['checkboxes']) )
	        {
	            sysMessage("操作成功", 0, $aLocation);
	        }
	        else 
	        {
	            sysMessage("操作失败", 1, $aLocation);
	        }
	    }
	    elseif( isset($_GET['action']) && $_GET['action']=='del' && isset($_GET['id']) )
	    { // 单个删除
	        $aDelArray[] = intval($_GET['id']);
	        $oMessage = new model_message();
	        if( $oMessage->delAdminMessage($aDelArray) )
	        {
	            sysMessage("操作成功", 0, $aLocation);
	        }
	        else 
	        {
	            sysMessage("操作失败", 1, $aLocation);
	        }
	    }
	    else
	    {
	        sysMessage("无效的操作", 1, $aLocation);
	    }
	}



	/**
	 * 增加消息
	 * URL = ./?controller=message&action=add
	 * @author Tom 090515
	 */
	function actionAdd()
	{
	    /* @var $oMessage model_message */
	    $oMessage = A::singleton('model_message');
	    $aHtml['mtoptions'] = $oMessage->getUserCanReadMessageType('opts');
	    $GLOBALS['oView']->assign( "info", $aHtml);
		$GLOBALS['oView']->assign( "ur_here", "增加消息");
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("message","list"), 'text'=>'消息列表' ) );
		$GLOBALS['oView']->display( "message_add.html");
		EXIT;
	}



	/**
	 * 保存消息
	 * URL = ./?controller=message&action=save
	 * @author Tom 090515
	 */
	function actionSave()
	{
	    $aLocation = array(0=>array("text" => "消息列表","href" => url("message","list")));
	    //1, 数据检查
	    if( !isset($_POST['mt']) || !is_numeric($_POST['mt']) || !isset($_POST['subject'])
	        || !isset($_POST['username']) || !isset($_POST['content']) || !isset($_POST['send']) )
	    {
	        sysMessage("提交数据有缺失,请重新检查", 1, $aLocation );
	    }
	    $oMessage = new model_message();
		$iFlag = $oMessage->InsertMessageFromAdmin( $_POST );
	    if( $iFlag > 0 )
		{
		    sysMessage("操作成功, 消息已发送. ", 0, $aLocation);
		}
		elseif( $iFlag == -1 )
		{
		    sysMessage("群发选项无效", 1, $aLocation);
		}
	    elseif( $iFlag == -2 )
		{
		    sysMessage("消息内容插入失败,请与技术部联系", 1, $aLocation);
		}
	    elseif( $iFlag == -3 )
		{
		    sysMessage("接收消息用户名不存在,请检查", 1, $aLocation);
		}
	    elseif( $iFlag == -10 )
		{
		    sysMessage("消息插入失败,请与技术部联系", 1, $aLocation);
		}
		else
		{
		    sysMessage("操作失败", 1, $aLocation);
		}
	}



	/**
	 * 查看消息
	 * URL = ./?controller=message&action=view&id={id}
	 * @author Tom 090515
	 */
	function actionView()
	{
	    $aLocation = array(0=>array("text" => "消息列表","href" => url("message","list")));
	    if( !isset($_GET['id']) || !is_numeric($_GET['id']) )
	    {
	        sysMessage("无效消息ID", 1, $aLocation);
	    }
	    /* @var $oMessage model_message */
	    $oMessage = A::singleton('model_message');
	    $aResMessage = $oMessage->getOneAdminMessage( $_GET['id'] );
	    if( $aResMessage == -1 )
	    {
	        sysMessage("消息读取失败", 1, $aLocation);
	    }
	    $aResMessage['content'] = nl2br( h($aResMessage['content']) );
	    /**
	     * [entry] => 8
         * [msgid] => 8
         * [receiverid] => 151
         * [receivergroup] => 1
         * [readtime] => 0000-00-00 00:00:00
         * [deltime] => 0000-00-00 00:00:00
         * [id] => 10
         * [msgtypeid] => 10
         * [senderid] => 20
         * [sendergroup] => 1
         * [subject] => 监控消息: 用户 tom 登陆 5.05
         * [content] => 用户登陆于 5.05
         * [channelid] => 0
         * [sendtime] => 2009-05-05 10:03:53
         * [title] => 监控用户消息
         * [description] => 监控用户消息
         * [menuid] => 152 
	     */
	    $GLOBALS['oView']->assign( 'info', $aResMessage );
		$GLOBALS['oView']->assign("ur_here", "查看消息详情");
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("message","list"), 'text'=>'消息列表' ) );
		$GLOBALS['oView']->display("message_view.html");
		EXIT;
	}
}
?>