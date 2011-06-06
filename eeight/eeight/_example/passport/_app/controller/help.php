<?php
/**
 * 文件 : /_app/controller/help.php
 * 功能 : 报表中心的所有操作
 *  
 * 类中所有的以 action 开头+首字母大写的英文, 为 "动作方法"
 * 例如 URL 访问: 
 *     http://www.xxx.com/?controller=default&action=abc
 *     default 是控制器名
 *     abc     是动作方法
 *     定义动作方法函数的命名, 规则为 action+首字母大写的全英文字符串
 *        例: 为实现上例的 /?controller=default&action=abc 中的 abc 方法
 *            需要在类中定义 actionIndex() 函数
 *
 *  功能:
 *      -- actionNoticeList     公告列表
 *      -- actionNotice         公告内容 [默认获取最新的一条公告]
 *      -- actionBank           银行说明
 *      -- actionQuestion       常见问题
 * 		-- actionVersion		版本信息 (add 4/6/2010)
 * 
 * @author    james,Tom
 * @version   1.2.0
 * @package   passport
 * 
 */

class controller_help extends basecontroller 
{
    /**
     * 公告列表
     */
    function actionNoticeList()
    {
        $oNotice = new model_notices();
        $aResult = $oNotice->getList(" `id`,`subject`, `sendtime`, `checktime`");
        $GLOBALS['oView']->assign( 'notices', $aResult );
        $GLOBALS['oView']->assign( 'ur_here','公告列表');
        $oNotice->assignSysInfo();
        $GLOBALS['oView']->display( "help_noticelist.html" );
        EXIT;
    }



    /**
     * 公告内容 [默认获取最新的一条公告]
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
        $aResult = $oNotice->getOne( $iNid, " `id`,`subject`,`content`,`sendtime`,`checktime` " );
        $GLOBALS['oView']->assign( 'notice', $aResult );
        $GLOBALS['oView']->assign( 'ur_here','公告内容');
        $oNotice->assignSysInfo();
        $GLOBALS['oView']->display( "help_notice.html" );
        EXIT;
    }



    /**
     * 银行说明
     * 生产版本需使用 SMART 静态缓存.
     */
    function actionBank()
    {
        $oHelps = new model_helps('comment');
        $aHelps = $oHelps->getAllTag();
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $GLOBALS['oView']->assign( 'ur_here','银行说明');
        $oHelps->assignSysInfo();
        $GLOBALS['oView']->display( "help_comment.html" );
        EXIT;
    }


    /**
     * 常见问题
     * 生产版本需使用 SMART 静态缓存.
     */
    function actionQuestion()
    {
        $oHelps = new model_helps('faq');
        $aHelps = $oHelps->getAllTag();
        $GLOBALS['oView']->assign( 'data', $aHelps );
        $iTagId = isset($_GET['tag_id']) ? $_GET['tag_id'] : -1;
        $GLOBALS['oView']->assign( 'tid', $iTagId );
        $GLOBALS['oView']->assign( 'ur_here','常见问题');
        $oHelps->assignSysInfo();
        $GLOBALS['oView']->display( "help_faq.html" );
        EXIT;
    }
    
    /**
     * 版本信息
     *  4/6/2010 jim
     */
    function actionVersion(){
    	$oVersion = new model_helps('verinfo');
        $aVersion = $oVersion->getAllTag();
        $GLOBALS['oView']->assign( 'data', $aVersion );
        $GLOBALS['oView']->assign( 'ur_here','版本信息');
        $oVersion->assignSysInfo();
        $GLOBALS['oView']->display( "help_version.html" );
        exit;
    }
    
        
    /**
     * 在线客服 JS显示
     * 10/30/2010 Jim
     * 
     * 输入格式 JavaScript
     */
    function actionLiveCustom(){
    	
    	//LIVECHAT用户组别权限
    	$oLiveChat = new model_livecustomer();
		$bShowOCSMenu = $oLiveChat->checkMenuPermisson();
		if ( $bShowOCSMenu === TRUE ) {
    		$aLiveChatUrl = $oLiveChat->startChat($_SESSION['username'], $_SESSION['userid']);
    		if ( $_GET['flag'] == 'open')
    		{
    			header('location: '.$aLiveChatUrl[0]);
    		}
    		else 
    		{
    			$aLiveChatUrl[0] = '/index.php?controller=help&action=livecustom&flag=open';
    		}
    		//测试是否有客服在线, 没有在线直接不显示按钮
    		//$sVar = file_get_contents($aLiveChatUrl[2]);
    		//if ( $sVar == 'on' ){
    		echo "document.write('<a class=\"dock-item\" id=\"livechatbutton\" href=javascript:OCSWindow(\"".$aLiveChatUrl[0]."\"); /><img src=\"".$aLiveChatUrl[1]."\" border=0 alt=\"在线客服\"><span>在线客服</span></a>');";
echo <<<THISOCSW
function OCSWindow(url)
{
   subWindow = window.open(url, "LiveChatOCS", "dependent,toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,width=765,height=550,resizable=1");
}

function closeOCSWin()
{
	if ( subWindow != null && subWindow.open)
	{
		subWindow.close();
	}
}
THISOCSW;
    		//}
		}
		else 
		{
    		echo 'document.write("")';
		}
		
    }
    
}
?>