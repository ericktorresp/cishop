<?php
/**
 * 文件 : /_app/controller/help.php
 * 功能 : 帮助中心
 *
 * 功能:
 * + actionChannels     频道说明
 * + actionPlayInfo     玩法介绍
 * + actionAnswer       常见问题
 *
 *  
 * @author    Floyd
 * @version   1.0.0
 * @package   highgame
 */

class controller_help extends basecontroller 
{
    /**
     * 公告列表
     */
    function actionNoticeList()
    {
        $oNotice = new model_notices();
        $aResult = $oNotice->noticesgetList( " `id`,`subject`,DATE(`sendtime`) AS sendday ", "", SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'notices', $aResult );
        $GLOBALS['oView']->assign( 'ur_here',   '公告列表' );
        $GLOBALS['oView']->display( "help_noticelist.html" );
        EXIT;
    }



    /**
     * 公告内容 
     */
    function actionNotice()
    {
        if( empty($_GET['nid']) || !is_numeric($_GET['nid']) )
        {
            sysMsg("非法操作",2);
        }
        $iNid    = intval($_GET['nid']);
        $oNotice = new model_notices();
        // 读取公告内容
        $aResult = $oNotice->noticesgetOne( $iNid, " `id`,`subject`,`content`,`sendtime` ", SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'notice', $aResult );
        $GLOBALS['oView']->assign( 'ur_here',   '系统公告' );
        $GLOBALS['oView']->display( "help_notice.html" );
        EXIT;
    }
    


    /**
     * 频道说明
     */
    function actionChannels()
    {
        $oHelps = new model_helps('comment');
        $aHelps = $oHelps->getAllTag( SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $GLOBALS['oView']->display("help_faq.html");
        EXIT;
    }
    
    
    
    /**
     * 玩法介绍
     */
    function actionPlayInfo()
    {
        $oHelps = new model_helps('intro');
        $aHelps = $oHelps->getAllTag( SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $GLOBALS['oView']->display("help_faq.html");
        EXIT;
    }

    
    
    /**
     * 常见问题
     */
    function actionAnswer()
    {
        $oHelps = new model_helps('faq');
        $aHelps = $oHelps->getAllTag( SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $GLOBALS['oView']->display("help_faq.html");
        EXIT;
    }
    
    /**
     *版本信息
     */
    function actionVersion()
    {
        $oHelps = new model_helps('version');
        $aHelps = $oHelps->getAllTag( SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $GLOBALS['oView']->display("help_faq.html");
        EXIT;
    }
}
?>