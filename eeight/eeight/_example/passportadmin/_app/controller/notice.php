<?php
/**
 * 文件 : /_app/controller/notice.php
 * 功能 : 控制器 - 公告管理
 * 
 *    - actionList()          公告列表
 *    +-- actionAdd()         发布公告(前台)
 *    +-- actionSave()        发布公告    (处理)
 *    +-- actionView()        查看公告(前台)  - 审核员
 *    +-- actionEdit()        修改公告(前台)
 *    +-- actionUpdate()      修改公告    (处理)
 *    +-- actionCannel()      取消审核    (处理)
 *    +-- actionCheck()       审核公告    (处理)
 *    +-- actionView()        公告查看    (处理)  [ for 审核员 ]
 * 
 * @author    Tom
 * @version   1.2.0
 * @package   passportadmin
 */

class controller_notice extends basecontroller
{
    /**
     * 公告列表
     * URL = ./?controller=notice&action=list
     * @author Tom 090520
     */
    function actionList()
    {
        // 01, 搜索条件整理
        $aSearch['isdel']   = isset($_GET['isdel']) && $_GET['isdel'] ? $_GET['isdel'] : 0; // 默认显示未删
        $aSearch['ischeck'] = isset($_GET['ischeck']) && is_numeric($_GET['ischeck']) ? intval($_GET['ischeck']) : ""; // 受限菜单id
        $aSearch['cid']     = isset($_GET['cid']) && is_numeric($_GET['cid']) ? intval($_GET['cid']) : ""; // 规则状态, 0=启用,1=禁用
        $oChannel           = new model_channels();
        $aSearch['channelopts'] = $oChannel->getDistintChannelNames( FALSE, $aSearch['cid'], TRUE );
        unset($oChannel);
        
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aSearch['isdel'] != -1 )
        { // 已删标记
            $sWhere .= " AND `isdel` = '". intval($aSearch['isdel']) ."' ";
        }
        if( $aSearch['ischeck'] === 0 )
        { // 未审核
            $sWhere .= " AND `checkid` = 0 ";
        }
        if( $aSearch['ischeck'] === 1 )
        { // 已审核
            $sWhere .= " AND `checkid` > 0 ";
        }
        if( $aSearch['cid'] !== '' )
        { // 频道ID
            $sWhere .= " AND `channelid` = '".$aSearch['cid']."' ";
        }
        $oNotice = new model_notices();
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aSearch['pn'] = $GLOBALS['SysPageSizeMax'] > 1 && $pn > $GLOBALS['SysPageSizeMax'] 
						? $GLOBALS['SysPageSizeMax'] 
						: $pn = $pn > 5 ? $pn : 25; 
						
        $aResult = $oNotice->getNoticeList('*', $sWhere, $aSearch['pn'] , $p, ' ORDER BY `sendtime` DESC '); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $aSearch['pn'], 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages', $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList', $aResult['results'] ); // 数据分配
    	$GLOBALS['oView']->assign( "s", $aSearch );
    	$GLOBALS['oView']->assign( "ur_here",  "公告列表" );
    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","list"), 'text'=>'清空过滤条件' ) );
    	$GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("notice","add"), 'text'=>'增加公告' ) );
    	$oNotice->assignSysInfo();
    	$GLOBALS['oView']->display( "notice_list.html" );
    	EXIT;
    }



	/**
	 * 发布公告 (前台)
	 * URL = ./?controller=notice&action=list
	 * @author Tom 090511
	 * 对MYSQL中的 HTML 录入部分, 需要在 smarty 模板文件中使用 
	 *    {$inputValue|escape:html} => 即:  htmlspecialchars()
	 */
	function actionAdd()
	{ // 使用 fckeditor
	    require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
	    $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = '';
        $FCKeditor = $editor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        /* @var $oChannel model_channels */
		$oChannel = A::singleton('model_channels');
	    $aSearch['channelopts'] = $oChannel->getDistintChannelNames( FALSE, '', TRUE );
	    unset( $oChannel, $editor );
		$GLOBALS['oView']->assign( "s", $aSearch );
		$GLOBALS['oView']->assign( "form_action", 'save' );
		$GLOBALS['oView']->assign( "ur_here", "增加公告" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","list"), 'text'=>'公告列表' ) );
		$GLOBALS['oView']->display( "notice_info.html" );
		EXIT;
	}


    
    /**
     * 发布公告 (处理)
     * URL = ./?controller=notice&action=save
     * @author Tom 090511
     */
    function actionSave()
    {
        $aLocation = array(0=>array("text" => "公告列表","href" => url("notice","list")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        /* @var $oNotices model_notices */
        $oNotices = A::singleton('model_notices');
        $iFlag = $oNotices->NoticeInsert( $_POST );
        if( $iFlag > 0 )
        {
            sysMessage("操作成功", 0, $aLocation);
        }
        else
        {
            sysMessage("操作失败", 1, $aLocation);
        }
    }



	/**
	 * 修改公告(前台)
	 * URL = ./?controller=notice&action=edit
	 * @author Tom 090520
	 */
	function actionEdit()
	{
	    $aLocation = array(0=>array("text" => "公告列表","href" => url("notice","list")));
	    if( !empty($_POST['form_action']) && trim($_POST['form_action'])=='bat_delete' )
	    {
            if( !isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
            {
                sysMessage("未选择数据", 1, $aLocation);
            }
            /* @var $oNotice model_notices */
            $oNotice  = A::singleton('model_notices');
            if( $oNotice->delNoitce( $_POST['checkboxes'], 1 ) )
            {
                sysMessage("操作成功",0, $aLocation);
            }
            else
            {
                sysMessage("操作失败",1, $aLocation);
            }
	    }

	    $iNoticeId = isset($_GET["noticeid"])&&is_numeric($_GET["noticeid"]) ? intval($_GET["noticeid"]) : 0;
		if( $iNoticeId==0 )
		{
		    sysMessage("公告ID错误", 1, $aLocation);
		}
		/* @var $oNotice model_notices */
		$oNotice = A::singleton('model_notices');
		$aNotices = $oNotice->notice($iNoticeId);
	    if( $aNotices == -1 )
		{
		    sysMessage("公告ID不存在", 1, $aLocation);
		}
	    // 使用 fckeditor
	    require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
	    $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = $aNotices['content'];
        $FCKeditor = $editor->CreateHtml();
        //die($editor->Value);
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
		$oChannel = new model_channels();
	    $aNotices['channelopts'] = $oChannel->getDistintChannelNames( FALSE, $aNotices['channelid'], TRUE );
	    unset( $oChannel,$editor );
	    $GLOBALS['oView']->assign( "form_action", 'update' );
		$GLOBALS['oView']->assign( "s", $aNotices );
		$GLOBALS['oView']->assign( "ur_here", "修改公告" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","list"), 'text'=>'公告列表' ) );
		$GLOBALS['oView']->display( "notice_info.html" );
		EXIT;
	}



    /**
     * 修改公告(执行)
     * URL = ./?controller=notice&action=update
     * @author Tom 090511
     */
    function actionupdate()
    {
        $aLocation = array(0=>array("text" => "公告列表","href" => url("notice","list")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iNoticeId = is_numeric($_POST["noticeid"]) ? intval($_POST["noticeid"]) : 0;
        $oNotice   = new model_notices();
        $aNotices  = $oNotice->notice($iNoticeId);
        if( $aNotices == -1 )
        {
            sysMessage("无效公告ID [$iNoticeId]", 1, $aLocation);
        }
        $iFlag = $oNotice->updateNoticeInfo( $iNoticeId, $_POST );
        if( $iFlag > 0 )
        {
            sysMessage("更新成功", 0, $aLocation);
        }
        else
        {
            sysMessage("更新失败.", 1, $aLocation);
        }
    }


    
    /**
     * 取消审核公告 (执行)
     * URL = ./?controller=notice&action=Cannel&noticeid=1
     * @author Tom 090511
     */
    function actionCannel()
    {
        $aLocation = array(0=>array("text" => "公告列表","href" => url("notice","list")));
        $iNoticeId = (isset($_GET["noticeid"])&&is_numeric($_GET["noticeid"])) ? intval($_GET["noticeid"]) : 0;
        /* @var $oNotice model_notices */
        $oNotice   = A::singleton('model_notices');
        $iFlag     = $oNotice->noticeVerify( $iNoticeId, FALSE );
        if( $iFlag > 0 )
        {
            sysMessage("更新成功", 0, $aLocation);
        }
        elseif( $iFlag == -2 )
        {
            sysMessage("更新失败, 公告发布者无权对公告进行审核", 1, $aLocation);
        }
        else
        {
            sysMessage("更新失败", 1, $aLocation);
        }
    }



    /**
     * 审核公告 (执行)
     * URL = ./?controller=notice&action=Check&noticeid=1
     * @author Tom 090511
     */
    function actionCheck()
    {
        $aLocation = array(0=>array("text" => "公告列表","href" => url("notice","list")));
        $iNoticeId = (isset($_GET["noticeid"])&&is_numeric($_GET["noticeid"])) ? intval($_GET["noticeid"]) : 0;
        /* @var $oNotice model_notices */
        $oNotice = A::singleton('model_notices');
        $iFlag = $oNotice->noticeVerify( $iNoticeId, TRUE );
        if( $iFlag > 0 )
        {
            sysMessage("更新成功", 0, $aLocation);
        }
        elseif( $iFlag == -2 )
        {
            sysMessage("更新失败, 公告发布者无权对公告进行审核", 1, $aLocation);
        }
        else
        {
            sysMessage("更新失败", 1, $aLocation);
        }
    }


    
    /**
     * 公告查看
     * URL = ./controller=notice&action=view
     * @author Tom 090511
     * 完成: 100%
     */
    function actionView()
    {
        $aLocation  = array(0=>array("text" => "公告列表","href" => url("notice","list")));
        /* @var $oNotice model_notices */
        $oNotice    = A::singleton('model_notices');
        $iNoticeId  = isset($_GET["noticeid"])&&is_numeric($_GET["noticeid"]) ? intval($_GET["noticeid"]) : 0;
        $aNotice    = $oNotice->notice( $iNoticeId );
        if( $aNotice == -1 )
        {
            sysMessage("无效公告ID [$iNoticeId]", 1, $aLocation);
        }
        /* @var $oChannels model_channels */
        $oChannels = A::singleton('model_channels');
        $aChannel  = $oChannels->channelGet( $aNotice['channelid'] );
        $sTitle    = $aChannel['channel'];
        $GLOBALS['oView']->assign( 'notice', $aNotice);
        $GLOBALS['oView']->assign( 'title', $sTitle);
        $GLOBALS['oView']->assign( "ur_here","查看公告");
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","list"), 'text'=>'公告列表' ) );
        $GLOBALS['oView']->display( "notice_view.html");
        EXIT;
    }



	/**
	 * 公告置顶
	 * URL = ./controller=notice&action=top
	 * @author SAUL 090703
	 */  
	function actionTop()
	{
		$aLocation[0] = array("text"=>'公告列表',"href"=>url('notice','list')); 
		$iNoticeId = isset($_GET["noticeid"])&&is_numeric($_GET["noticeid"]) ? intval($_GET["noticeid"]) : 0 ;
		if( $iNoticeId==0 )
		{
			sysMessage( '参数错误', 1);
		}
		/* @var $oNotice model_notices */
		$oNotice = A::singleton('model_notices');
		$iResult = $oNotice->topNotice( $iNoticeId, 1 );
		switch( $iResult )
		{
			case -1:
				sysMessage( '参数错误', 1, $aLocation );
				break;
			case -2:
				sysMessage( '公告已经删除', 1, $aLocation );
				break;
			case -3:
				sysMessage( '公告没有审核', 1, $aLocation );
				break;
			case 0:
				sysMessage( '操作失败', 1, $aLocation );
				break;
			default:
				sysMessage( '操作成功', 0, $aLocation );
				break;
		}
	}



	/**
	 * 公告取消置顶
	 * URL = ./controller=notice&action=canneltop
	 * @author SAUL 090703
	 */  
	function actionCanneltop()
	{
		$aLocation[0] = array("text"=>'公告列表',"href"=>url('notice','list')); 
		$iNoticeId = isset($_GET["noticeid"])&&is_numeric($_GET["noticeid"]) ? intval($_GET["noticeid"]) : 0 ;
		if( $iNoticeId==0 )
		{
			sysMessage( '参数错误', 1);
		}
		/* @var $oNotice model_notices */
		$oNotice = A::singleton('model_notices');
		$iResult = $oNotice->topNotice( $iNoticeId, 0 );
		switch( $iResult )
		{
			case -1:
				sysMessage( '参数错误', 1, $aLocation );
				break;
			case -2:
				sysMessage( '公告已经删除', 1, $aLocation );
				break;
			case -3:
				sysMessage( '公告没有审核', 1, $aLocation );
				break;
			case 0:
				sysMessage( '操作失败', 1, $aLocation );
				break;
			default:
				sysMessage( '操作成功', 0, $aLocation );
				break;
		}
	}




    /**
     * 银行说明管理
     * URL = ./?controller=notice&action=commentlist
     * @author Tom 090817 13:21
     */
    function actionCommentList()
    {
        // 01, 搜索条件整理
        $aSearch['isdel']   = isset($_GET['isdel']) && $_GET['isdel'] ? $_GET['isdel'] : 0; // 默认显示未删
    
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aSearch['isdel'] != -1 )
        { // 已删标记
            $sWhere .= " AND `isdel` = '". intval($aSearch['isdel']) ."' ";
        }
    
        $oHelps = new model_helps('comment');
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oHelps->getHelpsList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages', $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList', $aResult['results'] ); // 数据分配
    	$GLOBALS['oView']->assign( "s", $aSearch );
    	$GLOBALS['oView']->assign( "ur_here",  "银行说明列表" );
    	$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","commentlist"), 'text'=>'清空过滤条件' ) );
    	$GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("notice","commentadd"), 'text'=>'增加银行说明页签' ) );
    	$oHelps->assignSysInfo();
    	$GLOBALS['oView']->display( "notice_commentlist.html" );
    	EXIT;
    }


	/**
	 * 增加银行说明页签
	 * URL = ./?controller=notice&action=commentadd
	 * @author Tom 090817 13:22
	 */
	function actionCommentAdd()
	{ // 使用 fckeditor
	    require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
	    $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = '';
        $FCKeditor = $editor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
	    unset( $editor );
		$GLOBALS['oView']->assign( "form_action", 'commentsave' );
		$GLOBALS['oView']->assign( "ur_here", "增加银行说明页签" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","commentlist"), 'text'=>'银行说明列表' ) );
		$GLOBALS['oView']->display( "notice_commentinfo.html" );
		EXIT;
	}



	/**
	 * 保存银行说明页签
	 * URL = ./?controller=notice&action=commentsave
	 * @author Tom 090817 13:23
	 */
	function actionCommentSave()
	{
	    $aLocation = array(0=>array("text" => "银行说明列表","href" => url("notice","commentlist")));
	    if( !isset($_POST) )
	    {
	        sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
	    }
	    $oHelps = new model_helps('comment');
		$iFlag = $oHelps->helpsInsert( $_POST );
	    if( $iFlag > 0 )
		{
		    sysMessage("操作成功", 0, $aLocation);
		}
		else
		{
		    sysMessage("操作失败", 1, $aLocation);
		}
	}


	/**
	 * 修改银行说明页签 (显示)
	 * URL = ./?controller=notice&action=commentedit
	 * @author Tom 090817 13:23
	 */
	function actionCommentEdit()
	{
	    $aLocation = array(0=>array("text" => "银行说明列表","href" => url("notice","commentlist")));
	    $iId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
		if( $iId==0 )
		{
		    sysMessage("编号错误", 1, $aLocation);
		}
		$oHelps = new model_helps('comment');
		$aHelps = $oHelps->getOne($iId);
	    if( $aHelps == -1 )
		{
		    sysMessage("ID不存在", 1, $aLocation);
		}
	    // 使用 fckeditor
	    require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
	    $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = $aHelps['content'];
        $FCKeditor = $editor->CreateHtml();
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
	    unset( $editor );
	    $GLOBALS['oView']->assign( "form_action", 'commentupdate' );
		$GLOBALS['oView']->assign( "s", $aHelps );
		$GLOBALS['oView']->assign( "ur_here", "修改银行说明页签" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","commentlist"), 'text'=>'银行说明列表' ) );
		$GLOBALS['oView']->display( "notice_commentinfo.html" );
		EXIT;
	}


	/**
	 * 修改银行说明页签 (执行)
	 * URL = ./?controller=notice&action=commentupdate
	 * @author Tom 090817 13:27
	 */
	function actionCommentUpdate()
	{
	    $aLocation = array(0=>array("text" => "银行说明列表","href" => url("notice","commentlist")));
	    if( !isset($_POST) )
	    {
	        sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
	    }
	    $iHelpsId = is_numeric($_POST["helpsid"]) ? intval($_POST["helpsid"]) : 0;
	    $oHelps   = new model_helps('comment');
		$aHelps   = $oHelps->getOne( $iHelpsId );
		if( $aHelps == -1 )
		{
		    sysMessage("无效ID [$iHelpsId]", 1, $aLocation);
		}
		$iFlag = $oHelps->helpsUpdate( $iHelpsId, $_POST );
	    if( $iFlag > 0 )
		{
		    sysMessage("更新成功", 0, $aLocation);
		}
		else
		{
		    sysMessage("更新失败.", 1, $aLocation);
		}
	}



    /**
     * 删除银行说明页签
     * URL = ./?controller=message&action=commentdel
	 * @author Tom 090817 13:24
     */
	function actionCommentDel()
	{
	    $aLocation = array(0=>array("text" => "银行说明列表","href" => url("notice","commentlist")));
	    if( isset($_GET['id']) && is_numeric($_GET['id']) )
	    { // 单个删除
	        $aDelArray[] = intval($_GET['id']);
	        $oHelps = new model_helps('comment');
	        if( $oHelps->helpDel( $aDelArray ) )
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


    // --------------------------------------------------------------------------------------
	/**
	 * 常见问题管理
	 * URL = ./?controller=notice&action=faqlist
	 * @author Tom 090817 13:25
	 */
	function actionFaqList()
	{
	    // 01, 搜索条件整理
	    $aSearch['isdel']   = isset($_GET['isdel']) && $_GET['isdel'] ? $_GET['isdel'] : 0; // 默认显示未删
   
	    // 02, WHERE 语句拼接
	    $sWhere = ' 1 '; // WHERE 条件变量声明
	    if( $aSearch['isdel'] != -1 )
	    { // 已删标记
	        $sWhere .= " AND `isdel` = '". intval($aSearch['isdel']) ."' ";
	    }

	    $oHelps = new model_helps('faq');
	    $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
	    $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
	    $aResult = $oHelps->getHelpsList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
	    $GLOBALS['oView']->assign( 'pages', $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList', $aResult['results'] ); // 数据分配
		$GLOBALS['oView']->assign( "s", $aSearch );
		$GLOBALS['oView']->assign( "ur_here",  "常见问题列表" );
		$GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","faqlist"), 'text'=>'清空过滤条件' ) );
		$GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("notice","faqadd"), 'text'=>'增加常见问题页签' ) );
		$oHelps->assignSysInfo();
		$GLOBALS['oView']->display( "notice_faqlist.html" );
		EXIT;
    }


    /**
     * 增加常见问题页签
     * URL = ./?controller=notice&action=faqadd
     * @author Tom 090817 13:26
     */
    function actionFaqAdd()
    { // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = '';
        $FCKeditor = $editor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        unset( $editor );
        $GLOBALS['oView']->assign( "form_action", 'faqsave' );
        $GLOBALS['oView']->assign( "ur_here", "增加常见问题页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","faqlist"), 'text'=>'常见问题列表' ) );
        $GLOBALS['oView']->display( "notice_faqinfo.html" );
        EXIT;
    }


    /**
     * 保存常见问题页签
     * URL = ./?controller=notice&action=faqsave
     * @author Tom 090817 13:27
     */
    function actionFaqSave()
    {
        $aLocation = array(0=>array("text" => "常见问题列表","href" => url("notice","faqlist")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $oHelps = new model_helps('faq');
        $iFlag = $oHelps->helpsInsert( $_POST );
        if( $iFlag > 0 )
        {
            sysMessage("操作成功", 0, $aLocation);
        }
        else
        {
            sysMessage("操作失败", 1, $aLocation);
        }
    }


    /**
     * 修改银行说明页签 (显示)
     * URL = ./?controller=notice&action=faqedit
     * @author Tom 090817 13:27
     */
    function actionFaqEdit()
    {
        $aLocation = array(0=>array("text" => "常见问题列表","href" => url("notice","faqlist")));
        $iId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iId==0 )
        {
            sysMessage("编号错误", 1, $aLocation);
        }
        $oHelps = new model_helps('faq');
        $aHelps = $oHelps->getOne($iId);
        if( $aHelps == -1 )
        {
            sysMessage("ID不存在", 1, $aLocation);
        }
        // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = $aHelps['content'];
        $FCKeditor = $editor->CreateHtml();
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
        unset( $editor );
        $GLOBALS['oView']->assign( "form_action", 'faqupdate' );
        $GLOBALS['oView']->assign( "s", $aHelps );
        $GLOBALS['oView']->assign( "ur_here", "修改常见问题页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","faqlist"), 'text'=>'常见问题列表' ) );
        $GLOBALS['oView']->display( "notice_faqinfo.html" );
        EXIT;
    }


    /**
     * 修改常见问题页签 (执行)
     * URL = ./?controller=notice&action=faqupdate
     * @author Tom 090817 13:27
     */
    function actionFaqUpdate()
    {
        $aLocation = array(0=>array("text" => "常见问题列表","href" => url("notice","faqlist")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iHelpsId = is_numeric($_POST["helpsid"]) ? intval($_POST["helpsid"]) : 0;
        $oHelps   = new model_helps('faq');
        $aHelps   = $oHelps->getOne( $iHelpsId );
        if( $aHelps == -1 )
        {
            sysMessage("无效ID [$iHelpsId]", 1, $aLocation);
        }
        $iFlag = $oHelps->helpsUpdate( $iHelpsId, $_POST );
        if( $iFlag > 0 )
        {
            sysMessage("更新成功", 0, $aLocation);
        }
        else
        {
            sysMessage("更新失败.", 1, $aLocation);
        }
    }



    /**
     * 删除常见问题页签
     * URL = ./?controller=message&action=faqdel
     * @author Tom 090817 13:28
     */
    function actionFaqDel()
    {
        $aLocation = array(0=>array("text" => "常见问题列表","href" => url("notice","faqlist")));
        if( isset($_GET['id']) && is_numeric($_GET['id']) )
        { // 单个删除
            $aDelArray[] = intval($_GET['id']);
            $oHelps = new model_helps('faq');
            if( $oHelps->helpDel( $aDelArray ) )
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
    
    
    
    //--- add 4/6/2010  version infomartion  ------------------------------
    /**
     * 列表
     */
	function actionVerInfoList()
	{
	    // 01, 搜索条件整理
	    $aSearch['isdel']   = isset($_GET['isdel']) && $_GET['isdel'] ? $_GET['isdel'] : 0; // 默认显示未删
   
	    // 02, WHERE 语句拼接
	    $sWhere = ' 1 '; // WHERE 条件变量声明
	    if( $aSearch['isdel'] != -1 )
	    { // 已删标记
	        $sWhere .= " AND `isdel` = '". intval($aSearch['isdel']) ."' ";
	    }

	    $oHelps = new model_helps('verinfo');
	    $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
	    $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
	    $aResult = $oHelps->getHelpsList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
	    $GLOBALS['oView']->assign( 'pages', $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList', $aResult['results'] ); // 数据分配
		$GLOBALS['oView']->assign( "s", $aSearch );
		$GLOBALS['oView']->assign( "ur_here",  "版本信息列表" );
		
		$GLOBALS['oView']->assign( 'actionlink3', array( 'href'=>url("notice","verinfoadd"), 'text'=>'增加版本信息' ) );
		$oHelps->assignSysInfo();
		$GLOBALS['oView']->display( "notice_verinfolist.html" );
		EXIT;
    }


    /**
     * 增加
     * URL = ./?controller=notice&action=verinfoadd
     */
    function actionVerInfoAdd()
    { // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = '';
        $FCKeditor = $editor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        unset( $editor );
        $GLOBALS['oView']->assign( "form_action", 'verinfosave' );
        $GLOBALS['oView']->assign( "ur_here", "增加版本信息" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","verinfolist"), 'text'=>'版本信息列表' ) );
        $GLOBALS['oView']->display( "notice_verinfo.html" );
        EXIT;
    }


    /**
     * 保存 
     * URL = ./?controller=notice&action=verinfosave
     */
    function actionVerInfoSave()
    {
        $aLocation = array(0=>array("text" => "版本信息列表","href" => url("notice","verinfolist")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $oHelps = new model_helps('verinfo');
        $iFlag = $oHelps->helpsInsert( $_POST );
        if( $iFlag > 0 )
        {
            sysMessage("操作成功", 0, $aLocation);
        }
        else
        {
            sysMessage("操作失败", 1, $aLocation);
        }
    }


    /**
     * 修改 show 
     * URL = ./?controller=notice&action=verinfoedit
     */
    function actionVerInfoEdit()
    {
        $aLocation = array(0=>array("text" => "版本信息列表","href" => url("notice","verinfolist")));
        $iId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iId==0 )
        {
            sysMessage("编号错误", 1, $aLocation);
        }
        $oHelps = new model_helps('verinfo');
        $aHelps = $oHelps->getOne($iId);
        if( $aHelps == -1 )
        {
            sysMessage("ID不存在", 1, $aLocation);
        }
        // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = $aHelps['content'];
        $FCKeditor = $editor->CreateHtml();
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
        unset( $editor );
        $GLOBALS['oView']->assign( "form_action", 'verinfoupdate' );
        $GLOBALS['oView']->assign( "s", $aHelps );
        $GLOBALS['oView']->assign( "ur_here", "修改版本信息" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","verinfolist"), 'text'=>'版本信息列表' ) );
        $GLOBALS['oView']->display( "notice_verinfo.html" );
        EXIT;
    }


    /**
     * 修改 action
     * URL = ./?controller=notice&action=verinfoupdate
     */
    function actionVerInfoUpdate()
    {
        $aLocation = array(0=>array("text" => "版本信息列表","href" => url("notice","verinfolist")));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iVersionId = is_numeric($_POST["verinfoid"]) ? intval($_POST["verinfoid"]) : 0;
        $oHelps   = new model_helps('verinfo');
        $aHelps   = $oHelps->getOne( $iVersionId );
        if( $aHelps == -1 )
        {
            sysMessage("无效ID [$iVersionId]", 1, $aLocation);
        }
        $iFlag = $oHelps->helpsUpdate( $iVersionId, $_POST );
        if( $iFlag > 0 )
        {
            sysMessage("更新成功", 0, $aLocation);
        }
        else
        {
            sysMessage("更新失败.", 1, $aLocation);
        }
    }


    /**
     * 删除
     * URL = ./?controller=message&action=verinfodel
     */
    function actionVerInfoDel()
    {
        $aLocation = array(0=>array("text" => "版本信息列表","href" => url("notice","verinfolist")));
        if( isset($_GET['id']) && is_numeric($_GET['id']) )
        { // 单个删除
            $aDelArray[] = intval($_GET['id']);
            $oHelps = new model_helps('verinfo');
            if( $oHelps->helpDel( $aDelArray ) )
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
    
}
?>