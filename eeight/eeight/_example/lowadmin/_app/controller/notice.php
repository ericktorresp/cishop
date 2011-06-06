<?php
/**
 * 文件 : /_app/controller/notice.php
 * 功能 : 控制器 - 网站管理 { 频道说明comment | 玩法介绍intro | 常见问题faq }
 * 
 * 功能:
 * 
 *  + actionCommentList         频道说明管理
 *  + actionCommentAdd          增加频道说明页签
 *  + actionCommentSave         保存频道说明页签
 *  + actionCommentEdit         编辑频道说明页签(查看)
 *  + actionCommentUpdate       更新频道说明页签 (执行)
 *  + actionCommentDel          删除频道说明页签
 *  + actionFaqList             常见问题管理
 *  + actionFaqAdd              增加常见问题页签
 *  + actionFaqSave             保存常见问题页签
 *  + actionFaqEdit             编辑常见问题页签(查看)
 *  + actionFaqUpdate           更新常见问题页签(执行)
 *  + actionFaqDel              删除常见问题页签
 *  + actionIntroList           玩法介绍管理
 *  + actionIntroAdd            增加玩法介绍页签
 *  + actionIntroSave           保存玩法介绍页签
 *  + actionIntroEdit           编辑玩法介绍页签
 *  + actionIntroUpdate         更新玩法介绍页签
 *  + actionIntroDel            删除玩法介绍页签
 *  + actionVerinfoList             版本信息管理 (add 4/6/2010)
 *  + actionVerinfoAdd              增加版本信息页签
 *  + actionVerinfoSave             保存版本信息页签
 *  + actionVerinfoEdit             编辑版本信息页签(查看)
 *  + actionVerinfoUpdate           更新版本信息页签(执行)
 *  + actionVerinfoDel              删除版本信息页签
 *
 * @author      Tom
 * @version     1.2.0 
 * @package     lowadmin
 */

class controller_notice extends basecontroller
{
    /**
     * 频道说明管理
     * URL = ./?controller=notice&action=commentlist
     * @author Tom
     */
    function actionCommentList()
    {
        // 01, 搜索条件整理
        $aSearch['isdel']   = isset($_GET['isdel']) && $_GET['isdel'] ? $_GET['isdel'] : 0; // 默认显示未删
        $aSearch['cid']     = isset($_GET['cid']) && $_GET['cid'] ? $_GET['cid'] : -1; // 默认显示所有频道
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aSearch['isdel'] != -1 )
        { // 已删标记
            $sWhere .= " AND `isdel` = '". intval($aSearch['isdel']) ."' ";
        }
        
        if( $aSearch['cid'] != -1 )
        { // 已删标记
            $sWhere .= " AND `channelid` = '". intval($aSearch['cid']) ."' ";
        }

        $oHelps = new model_helps('comment');
        $p      = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn     = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oHelps->getHelpsList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages',     $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList',     $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign( "s",         $aSearch );
        $GLOBALS['oView']->assign( "ur_here",   "频道说明列表" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","commentlist"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("notice","commentadd"), 'text'=>'增加频道说明页签' ) );
        $oHelps->assignSysInfo();
        $GLOBALS['oView']->display( "notice_commentlist.html" );
        EXIT;
    }


    /**
     * 增加频道说明页签
     * URL = ./index.php?controller=notice&action=commentadd
     * @author Tom
     */
    function actionCommentAdd()
    { // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = '';
        $FCKeditor          = $editor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        unset( $editor );
        $s['channelid']     = isset($_GET['cid']) && in_array( $_GET['cid'], array(1,2) ) ? intval($_GET['cid']) : 1 ;
        $GLOBALS['oView']->assign( "s", $s );
        $GLOBALS['oView']->assign( "form_action", 'commentsave' );
        $GLOBALS['oView']->assign( "ur_here", "增加频道说明页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","commentlist"), 'text'=>'频道说明列表' ) );
        $GLOBALS['oView']->display( "notice_commentinfo.html" );
        EXIT;
    }


    /**
     * 保存频道说明页签
     * URL = ./index.php?controller=notice&action=commentsave
     * @author Tom
     */
    function actionCommentSave()
    {
        $aLocation[0] = array("text" => "返回频道说明列表","href" => url("notice","commentlist"));
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
     * 修改频道说明页签 (显示)
     * URL = ./index.php?controller=notice&action=commentedit
     * @author Tom
     */
    function actionCommentEdit()
    {
        $aLocation[0] = array("text" => "返回频道说明列表","href" => url("notice","commentlist"));
        $iId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iId==0 )
        {
            sysMessage("编号错误", 1, $aLocation);
        }
        $oHelps = new model_helps('comment');
        $aHelps = $oHelps->getOne($iId);
        if( $aHelps == -1 || empty($aHelps) )
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
        $oHelps->assignSysInfo();
        $GLOBALS['oView']->assign( "form_action", 'commentupdate' );
        $GLOBALS['oView']->assign( "s", $aHelps );
        $GLOBALS['oView']->assign( "ur_here", "修改频道说明页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","commentlist"), 'text'=>'频道说明列表' ) );
        $GLOBALS['oView']->display( "notice_commentinfo.html" );
        EXIT;
    }


    /**
     * 修改频道说明页签 (执行)
     * URL = ./index.php?controller=notice&action=commentupdate
     * @author Tom
     */
    function actionCommentUpdate()
    {
        $aLocation[0] = array("text" => "返回频道说明列表","href" => url("notice","commentlist"));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iHelpsId = is_numeric($_POST["helpsid"]) ? intval($_POST["helpsid"]) : 0;
        $oHelps   = new model_helps('comment');
        $aHelps   = $oHelps->getOne( $iHelpsId );
        if( $aHelps == -1 || empty($aHelps) )
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
     * 删除频道说明页签
     * URL = ./index.php?controller=message&action=commentdel
     * @author Tom
     */
    function actionCommentDel()
    {
        $aLocation[0] = array("text" => "返回频道说明列表","href" => url("notice","commentlist"));
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



    /**
     * 常见问题管理
     * URL = ./index.php?controller=notice&action=faqlist
     * @author Tom
     */
    function actionFaqList()
    {
        // 01, 搜索条件整理
        $aSearch['isdel']   = isset($_GET['isdel']) && $_GET['isdel'] ? $_GET['isdel'] : 0; // 默认显示未删
        $aSearch['cid']     = isset($_GET['cid']) && $_GET['cid'] ? $_GET['cid'] : -1; // 默认显示所有栏目
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aSearch['isdel'] != -1 )
        { // 已删标记
            $sWhere .= " AND `isdel` = '". intval($aSearch['isdel']) ."' ";
        }
        if( $aSearch['cid'] != -1 )
        { // 已删标记
            $sWhere .= " AND `channelid` = '". intval($aSearch['cid']) ."' ";
        }
        $oHelps = new model_helps('faq');
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oHelps->getHelpsList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages',     $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList',     $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign( "s",         $aSearch );
        $GLOBALS['oView']->assign( "ur_here",   "常见问题列表" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","faqlist"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("notice","faqadd"), 'text'=>'增加常见问题页签' ) );
        $oHelps->assignSysInfo();
        $GLOBALS['oView']->display( "notice_faqlist.html" );
        EXIT;
    }



    /**
     * 增加常见问题页签
     * URL = ./index.php?controller=notice&action=faqadd
     * @author Tom
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
        $s['channelid'] = isset($_GET['cid']) && in_array( $_GET['cid'], array(1,2) ) ? intval($_GET['cid']) : 1 ;
        $GLOBALS['oView']->assign( "s",             $s );
        $GLOBALS['oView']->assign( "form_action",   'faqsave' );
        $GLOBALS['oView']->assign( "ur_here",       "增加常见问题页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","faqlist"), 'text'=>'常见问题列表' ) );
        $GLOBALS['oView']->display( "notice_faqinfo.html" );
        EXIT;
    }



    /**
     * 保存常见问题页签
     * URL = ./index.php?controller=notice&action=faqsave
     * @author Tom
     */
    function actionFaqSave()
    {
        $aLocation[0] = array("text" => "返回常见问题列表","href" => url("notice","faqlist"));
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
     * 修改频道说明页签 (显示)
     * URL = ./index.php?controller=notice&action=faqedit
     * @author Tom
     */
    function actionFaqEdit()
    {
        $aLocation[0] = array("text" => "返回常见问题列表","href" => url("notice","faqlist"));
        $iId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iId==0 )
        {
            sysMessage("编号错误", 1, $aLocation);
        }
        $oHelps = new model_helps('faq');
        $aHelps = $oHelps->getOne($iId);
        if( $aHelps == -1 || empty($aHelps) )
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
        $oHelps->assignSysInfo();
        $GLOBALS['oView']->assign( "form_action",   'faqupdate' );
        $GLOBALS['oView']->assign( "s",             $aHelps );
        $GLOBALS['oView']->assign( "ur_here",       "修改常见问题页签" );
        $GLOBALS['oView']->assign( 'actionlink',    array( 'href'=>url("notice","faqlist"), 'text'=>'常见问题列表' ) );
        $GLOBALS['oView']->display( "notice_faqinfo.html" );
        EXIT;
    }



    /**
     * 修改常见问题页签 (执行)
     * URL = ./index.php?controller=notice&action=faqupdate
     * @author Tom
     */
    function actionFaqUpdate()
    {
        $aLocation[0] = array("text" => "返回常见问题列表","href" => url("notice","faqlist"));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iHelpsId = is_numeric($_POST["helpsid"]) ? intval($_POST["helpsid"]) : 0;
        $oHelps   = new model_helps('faq');
        $aHelps   = $oHelps->getOne( $iHelpsId );
        if( $aHelps == -1 || empty($aHelps) )
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
     * URL = ./index.php?controller=message&action=faqdel
     * @author Tom
     */
    function actionFaqDel()
    {
        $aLocation[0] = array("text" => "返回常见问题列表","href" => url("notice","faqlist"));
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



    /**
     * 玩法介绍列表
     * URL = ./index.php?controller=notice&action=introlist
     * @author Tom
     */
    function actionIntroList()
    {
        // 01, 搜索条件整理
        $aSearch['isdel']   = isset($_GET['isdel']) && $_GET['isdel'] ? $_GET['isdel'] : 0; // 默认显示未删
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aSearch['isdel'] != -1 )
        { // 已删标记
            $sWhere .= " AND `isdel` = '". intval($aSearch['isdel']) ."' ";
        }
        $oHelps = new model_helps('intro');
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oHelps->getHelpsList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages',     $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList',     $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign( "s",         $aSearch );
        $GLOBALS['oView']->assign( "ur_here",   "玩法介绍列表" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","introlist"), 'text'=>'清空过滤条件' ) );
        $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("notice","introadd"), 'text'=>'增加玩法介绍页签' ) );
        $oHelps->assignSysInfo();
        $GLOBALS['oView']->display( "notice_introlist.html" );
        EXIT;
    }



    /**
     * 增加玩法介绍页签
     * URL = ./index.php?controller=notice&action=introadd
     * @author Tom
     */
    function actionIntroAdd()
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
        $GLOBALS['oView']->assign( "form_action", 'introsave' );
        $GLOBALS['oView']->assign( "ur_here", "增加玩法介绍页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","introlist"), 'text'=>'玩法介绍列表' ) );
        $GLOBALS['oView']->display( "notice_introinfo.html" );
        EXIT;
    }



    /**
     * 保存玩法介绍页签
     * URL = ./index.php?controller=notice&action=introsave
     * @author Tom
     */
    function actionIntroSave()
    {
        $aLocation[0] = array("text" => "返回玩法介绍列表","href" => url("notice","introlist"));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $oHelps = new model_helps('intro');
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
     * 修改频道说明页签 (显示)
     * URL = ./index.php?controller=notice&action=introedit
     * @author Tom
     */
    function actionIntroEdit()
    {
        $aLocation[0] = array("text" => "返回玩法介绍列表","href" => url("notice","introlist"));
        $iId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iId==0 )
        {
            sysMessage("编号错误", 1, $aLocation);
        }
        $oHelps = new model_helps('intro');
        $aHelps = $oHelps->getOne($iId);
        if( $aHelps == -1 || empty($aHelps) )
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
        $GLOBALS['oView']->assign( "form_action", 'introupdate' );
        $GLOBALS['oView']->assign( "s", $aHelps );
        $GLOBALS['oView']->assign( "ur_here", "修改玩法介绍页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","introlist"), 'text'=>'玩法介绍列表' ) );
        $GLOBALS['oView']->display( "notice_introinfo.html" );
        EXIT;
    }



    /**
     * 修改玩法介绍页签 (执行)
     * URL = ./index.php?controller=notice&action=introupdate
     * @author Tom
     */
    function actionIntroUpdate()
    {
        $aLocation[0] = array("text" => "返回玩法介绍列表","href" => url("notice","introlist"));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iHelpsId = is_numeric($_POST["helpsid"]) ? intval($_POST["helpsid"]) : 0;
        $oHelps   = new model_helps('intro');
        $aHelps   = $oHelps->getOne( $iHelpsId );
        if( $aHelps == -1 || empty($aHelps) )
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
     * 删除玩法介绍页签
     * URL = ./index.php?controller=message&action=introdel
     * @author Tom
     */
    function actionIntroDel()
    {
        $aLocation[0] = array("text" => "返回玩法介绍列表","href" => url("notice","introlist"));
        if( isset($_GET['id']) && is_numeric($_GET['id']) )
        { // 单个删除
            $aDelArray[] = intval($_GET['id']);
            $oHelps = new model_helps('intro');
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
    
    
    
 	/**
     * 版本信息管理   (add 4/6/2010)
     * URL = ./index.php?controller=notice&action=verinfolist
     */
    function actionVerinfoList()
    {
        // 01, 搜索条件整理
        $aSearch['isdel']   = isset($_GET['isdel']) && $_GET['isdel'] ? $_GET['isdel'] : 0; // 默认显示未删
        $aSearch['cid']     = isset($_GET['cid']) && $_GET['cid'] ? $_GET['cid'] : -1; // 默认显示所有栏目
        // 02, WHERE 语句拼接
        $sWhere = ' 1 '; // WHERE 条件变量声明
        if( $aSearch['isdel'] != -1 )
        { // 已删标记
            $sWhere .= " AND `isdel` = '". intval($aSearch['isdel']) ."' ";
        }
        if( $aSearch['cid'] != -1 )
        { // 已删标记
            $sWhere .= " AND `channelid` = '". intval($aSearch['cid']) ."' ";
        }
        $oVersion = new model_helps('verinfo');
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oVersion->getHelpsList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages',     $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList',     $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign( "s",         $aSearch );
        $GLOBALS['oView']->assign( "ur_here",   "版本信息列表" );
        $GLOBALS['oView']->assign( 'actionlink2', array( 'href'=>url("notice","verinfoadd"), 'text'=>'增加版本信息' ) );
        $oVersion->assignSysInfo();
        $GLOBALS['oView']->display( "notice_verinfolist.html" );
        EXIT;
    }



    /**
     * 增加版本信息页签
     * URL = ./index.php?controller=notice&action=verinfoadd
     */
    function actionVerinfoAdd()
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
        $s['channelid'] = isset($_GET['cid']) && in_array( $_GET['cid'], array(1,2) ) ? intval($_GET['cid']) : 1 ;
        $GLOBALS['oView']->assign( "s",             $s );
        $GLOBALS['oView']->assign( "form_action",   'verinfosave' );
        $GLOBALS['oView']->assign( "ur_here",       "增加版本信息页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","verinfolist"), 'text'=>'版本信息列表' ) );
        $GLOBALS['oView']->display( "notice_verinfo.html" );
        EXIT;
    }



    /**
     * 保存版本信息页签
     * URL = ./index.php?controller=notice&action=verinfosave
     */
    function actionVerinfoSave()
    {
        $aLocation[0] = array("text" => "返回版本信息列表","href" => url("notice","verinfolist"));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $oVersion = new model_helps('verinfo');
        $iFlag = $oVersion->helpsInsert( $_POST );
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
     * 修改版本信息页签 (显示)
     * URL = ./index.php?controller=notice&action=verinfoedit
     */
    function actionVerinfoEdit()
    {
        $aLocation[0] = array("text" => "返回版本信息列表","href" => url("notice","verinfolist"));
        $iId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iId==0 )
        {
            sysMessage("编号错误", 1, $aLocation);
        }
        $oVersion = new model_helps('verinfo');
        $aVersion = $oVersion->getOne($iId);
        if( $aVersion == -1 || empty($aVersion) )
        {
            sysMessage("ID不存在", 1, $aLocation);
        }
        // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $editor = new FCKeditor( 'FCKeditor1' );
        $editor->BasePath   = './js/fckeditor/';
        $editor->Width      = '100%';
        $editor->Height     = '420';
        $editor->Value      = $aVersion['content'];
        $FCKeditor = $editor->CreateHtml();
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
        unset( $editor );
        $oVersion->assignSysInfo();
        $GLOBALS['oView']->assign( "form_action",   'verinfoupdate' );
        $GLOBALS['oView']->assign( "s",             $aVersion );
        $GLOBALS['oView']->assign( "ur_here",       "修改版本信息页签" );
        $GLOBALS['oView']->assign( 'actionlink',    array( 'href'=>url("notice","verinfolist"), 'text'=>'版本信息列表' ) );
        $GLOBALS['oView']->display( "notice_verinfo.html" );
        EXIT;
    }



    /**
     * 修改版本信息页签 (执行)
     * URL = ./index.php?controller=notice&action=verinfoupdate
     */
    function actionVerinfoUpdate()
    {
        $aLocation[0] = array("text" => "返回版本信息列表","href" => url("notice","verinfolist"));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iVersionId = is_numeric($_POST["verinfoid"]) ? intval($_POST["verinfoid"]) : 0;
        $oVersion   = new model_helps('verinfo');
        $aVersion   = $oVersion->getOne( $iVersionId );
        if( $aVersion == -1 || empty($aVersion) )
        {
            sysMessage("无效ID [$iVersionId]", 1, $aLocation);
        }
        $iFlag = $oVersion->helpsUpdate( $iVersionId, $_POST );
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
     * 删除版本信息页签
     * URL = ./index.php?controller=message&action=faqdel
     * @author Tom
     */
    function actionVerinfoDel()
    {
       $aLocation[0] = array("text" => "返回版本信息列表","href" => url("notice","verinfolist"));
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