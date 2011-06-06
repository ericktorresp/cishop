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
 * TODO: 生产版本需使用 SMART 静态缓存.
 *  
 * @author    Tom
 * @version   1.2.0
 * @package   lowproxy
 */

class controller_help extends basecontroller 
{
    /**
     * 公告列表
     */
    function actionNoticeList()
    {
        $oNotice = new model_notices();
        $aResult = $oNotice->noticesgetList( " `id`,`subject`,`sendtime`,`checktime` ", "", SYS_CHANNELID );
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
        $aResult = $oNotice->noticesgetOne( $iNid, " `id`,`subject`,`content`,`sendtime`,`checktime` ", SYS_CHANNELID );
        $GLOBALS['oView']->assign( 'notice', $aResult );
        $GLOBALS['oView']->assign( 'ur_here',   '系统公告' );
        $GLOBALS['oView']->display( "help_notice.html" );
        EXIT;
    }
    


    /**
     * 频道说明
     * URL:./index.php?controller=help&action=channels
     * @author TOM
     */
    function actionChannels()
    {
        $oHelps = new model_helps('comment');
        $aHelps = $oHelps->getAllTag(2);
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $GLOBALS['oView']->display("help_comment.html");
        EXIT;
    }
    
    
    
    /**
     * 玩法介绍
     * URL:./index.php?controller=help&action=playinfo
     * @author TOM
     */
    function actionPlayInfo()
    {
        $oHelps = new model_helps('intro');
        $aHelps = $oHelps->getAllTag();
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $GLOBALS['oView']->display("help_intro.html");
        EXIT;
    }

    
    
    /**
     * 常见问题
     * URL:./index.php?controller=help&action=answer
     * @author TOM
     */
    function actionAnswer()
    {
        $oHelps = new model_helps('faq');
        $aHelps = $oHelps->getAllTag(2);
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $GLOBALS['oView']->display("help_faq.html");
        EXIT;
    }
    
    /**
     * 版本信息
     * 4/6/2010
     */
	function actionVersion()
    {

        $oVersion = new model_helps('verinfo');
        $aVersion = $oVersion->getAllTag();
        $GLOBALS['oView']->assign( 'data', $aVersion );
        $GLOBALS['oView']->display("help_version.html");
        EXIT;
    }
}
?>