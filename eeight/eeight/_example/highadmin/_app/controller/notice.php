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
 *
 * @author      Mark, Tom
 * @version     1.2.0 
 * @package     highadmin
 */

class controller_notice extends basecontroller
{
    /**
     * 频道说明管理
     * URL = ./?controller=notice&action=commentlist
     * @author Mark, Tom
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
     * @author Mark, Tom
     */
    function actionCommentAdd()
    { // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $oEditor = new FCKeditor( 'FCKeditor1' );
        $oEditor->BasePath   = './js/fckeditor/';
        $oEditor->Width      = '100%';
        $oEditor->Height     = '420';
        $oEditor->Value      = '';
        $FCKeditor          = $oEditor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        unset( $oEditor );
        $GLOBALS['oView']->assign( "form_action", 'commentsave' );
        $GLOBALS['oView']->assign( "ur_here", "增加频道说明页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","commentlist"), 'text'=>'频道说明列表' ) );
        $GLOBALS['oView']->display( "notice_commentinfo.html" );
        EXIT;
    }


    /**
     * 保存频道说明页签
     * URL = ./index.php?controller=notice&action=commentsave
     * @author Mark, Tom
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
     * @author Mark, Tom
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
        $oEditor = new FCKeditor( 'FCKeditor1' );
        $oEditor->BasePath   = './js/fckeditor/';
        $oEditor->Width      = '100%';
        $oEditor->Height     = '420';
        $oEditor->Value      = $aHelps['content'];
        $FCKeditor = $oEditor->CreateHtml();
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
        unset( $oEditor );
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
     * @author Mark, Tom
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
     * @author Mark, Tom
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
     * @author Mark, Tom
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
     * @author Mark, Tom
     */
    function actionFaqAdd()
    { // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $oEditor = new FCKeditor( 'FCKeditor1' );
        $oEditor->BasePath   = './js/fckeditor/';
        $oEditor->Width      = '100%';
        $oEditor->Height     = '420';
        $oEditor->Value      = '';
        $FCKeditor = $oEditor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        unset( $oEditor );
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
     * @author Mark, Tom
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
     * @author Mark, Tom
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
        $oEditor = new FCKeditor( 'FCKeditor1' );
        $oEditor->BasePath   = './js/fckeditor/';
        $oEditor->Width      = '100%';
        $oEditor->Height     = '420';
        $oEditor->Value      = $aHelps['content'];
        $FCKeditor = $oEditor->CreateHtml();
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
        unset( $oEditor );
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
     * @author Mark, Tom
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
     * @author Mark, Tom
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
     * @author Mark, Tom
     */
    function actionIntroAdd()
    { // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $oEditor = new FCKeditor( 'FCKeditor1' );
        $oEditor->BasePath   = './js/fckeditor/';
        $oEditor->Width      = '100%';
        $oEditor->Height     = '420';
        $oEditor->Value      = '';
        $FCKeditor = $oEditor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        unset( $oEditor );
        $GLOBALS['oView']->assign( "form_action", 'introsave' );
        $GLOBALS['oView']->assign( "ur_here", "增加玩法介绍页签" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","introlist"), 'text'=>'玩法介绍列表' ) );
        $GLOBALS['oView']->display( "notice_introinfo.html" );
        EXIT;
    }



    /**
     * 保存玩法介绍页签
     * URL = ./index.php?controller=notice&action=introsave
     * @author Mark, Tom
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
     * 修改玩法介绍页签 (显示)
     * URL = ./index.php?controller=notice&action=introedit
     * @author Mark, Tom
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
        $oEditor = new FCKeditor( 'FCKeditor1' );
        $oEditor->BasePath   = './js/fckeditor/';
        $oEditor->Width      = '100%';
        $oEditor->Height     = '420';
        $oEditor->Value      = $aHelps['content'];
        $FCKeditor = $oEditor->CreateHtml();
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
        unset( $oEditor );
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
     * @author Mark, Tom
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
     * @author Mark, Tom
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
     * 版本信息列表
     * URL = ./index.php?controller=notice&action=versionlist
     * @author Mark
     */
    function actionVersionList()
    {
        $sWhere = ' 1 '; // WHERE 条件变量声明
        $oHelps = new model_helps('version');
        $p  = isset($_GET['p'])  ? intval($_GET['p'])  : 0;    // 分页用1
        $pn = isset($_GET['pn']) ? intval($_GET['pn']) : 25;   // 分页用2
        $aResult = $oHelps->getHelpsList('*', $sWhere, $pn , $p); // 获取数据结果集
        $oPager = new pages( $aResult['affects'], $pn, 10);    // 分页用3
        $GLOBALS['oView']->assign( 'pages',     $oPager->show() ); // 分页用4
        $GLOBALS['oView']->assign( 'aList',     $aResult['results'] ); // 数据分配
        $GLOBALS['oView']->assign( "ur_here",   "版本信息列表" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","versionadd"), 'text'=>'增加版本信息' ) );
        $oHelps->assignSysInfo();
        $GLOBALS['oView']->display( "notice_versionlist.html" );
        EXIT;
    }



    /**
     * 增加版本信息
     * URL = ./index.php?controller=notice&action=versionadd
     * @author Mark
     */
    function actionVersionAdd()
    { // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $oEditor = new FCKeditor( 'FCKeditor1' );
        $oEditor->BasePath   = './js/fckeditor/';
        $oEditor->Width      = '100%';
        $oEditor->Height     = '420';
        $oEditor->Value      = '';
        $FCKeditor = $oEditor->CreateHtml();
        $GLOBALS['oView']->assign( 'FCKeditor_1', $FCKeditor );
        unset( $oEditor );
        $GLOBALS['oView']->assign( "form_action", 'versionsave' );
        $GLOBALS['oView']->assign( "ur_here", "增加版本信息" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","versionlist"), 'text'=>'版本信息列表' ) );
        $GLOBALS['oView']->display( "notice_versioninfo.html" );
        EXIT;
    }



    /**
     * 保存版本信息
     * URL = ./index.php?controller=notice&action=versionsave
     * @author Mark
     */
    function actionVersionSave()
    {
        $aLocation[0] = array("text" => "返回版本信息列表","href" => url("notice","versionlist"));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $oHelps = new model_helps('version');
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
     * 修改版本信息 (显示)
     * URL = ./index.php?controller=notice&action=versionedit
     * @author Mark
     */
    function actionVersionEdit()
    {
        $aLocation[0] = array("text" => "返回版本信息列表","href" => url("notice","versionlist"));
        $iId = isset($_GET["id"])&&is_numeric($_GET["id"]) ? intval($_GET["id"]) : 0;
        if( $iId == 0 )
        {
            sysMessage("编号错误", 1, $aLocation);
        }
        $oHelps = new model_helps('version');
        $aHelps = $oHelps->getOne( $iId );
        if( $aHelps == -1 || empty($aHelps) )
        {
            sysMessage("ID不存在", 1, $aLocation);
        }
        // 使用 fckeditor
        require_once PDIR.DS.'js'.DS.'fckeditor'.DS.'fckeditor.php';
        $oEditor = new FCKeditor( 'FCKeditor1' );
        $oEditor->BasePath   = './js/fckeditor/';
        $oEditor->Width      = '100%';
        $oEditor->Height     = '420';
        $oEditor->Value      = $aHelps['content'];
        $FCKeditor = $oEditor->CreateHtml();
        $GLOBALS['oView']->assign('FCKeditor_1', $FCKeditor);
        unset( $oEditor );
        $GLOBALS['oView']->assign( "form_action", 'versionupdate' );
        $GLOBALS['oView']->assign( "s", $aHelps );
        $GLOBALS['oView']->assign( "ur_here", "修改版本信息" );
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("notice","versionlist"), 'text'=>'版本信息列表' ) );
        $GLOBALS['oView']->display( "notice_versioninfo.html" );
        EXIT;
    }



    /**
     * 修改版本信息 (执行)
     * URL = ./index.php?controller=notice&action=versionpdate
     * @author Mark
     */
    function actionVersionUpdate()
    {
        $aLocation[0] = array("text" => "返回版本信息列表","href" => url("notice","versionlist"));
        if( !isset($_POST) )
        {
            sysMessage("提交数据有缺失,请重新检查", 1, $aLocation);
        }
        $iHelpsId = is_numeric($_POST["helpsid"]) ? intval($_POST["helpsid"]) : 0;
        $oHelps   = new model_helps('version');
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
     * 删除版本信息
     * URL = ./index.php?controller=message&action=versiondel
     * @author Mark
     */
    function actionVersionDel()
    {
        $aLocation[0] = array("text" => "返回版本信息列表","href" => url("notice","versionlist"));
        if( isset($_GET['id']) && is_numeric($_GET['id']) )
        { // 单个删除
            $aDelArray[] = intval($_GET['id']);
            $oHelps = new model_helps('version');
            if( $oHelps->helpDel( $aDelArray, TRUE ) )
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