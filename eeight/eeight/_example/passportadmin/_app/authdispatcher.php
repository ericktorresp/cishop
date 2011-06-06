<?php
/**
 * 路径: /_app/authdispatcher.php
 * 功能: MVC调度器 + 身份验证 
 * 
 *     1, 根据当前 URL $_REQUEST 来初始化控制器名,与动作方法名.
 *        例:  http://www.xxx.com/?controller=default&action=list
 *             控制器名为: default   ( $_controller_name )
 *             动作方法为: list      ( $_action_name )
 *     
 *     2, 进行 控制器/动作方法 名称检查
 *     3, 进行 控制器/动作方法 的使用权限判断
 *     4, 程序交付给指定控制器的制定方法 
 * @author     Tom  090523
 * @version    1.2.0
 * @package    passportadmin
 */

if( !defined('IN_APPLE') || IN_APPLE!==TRUE ) die('Error code: 0x1000');
class authDispatcher extends dispatcher
{   
    /**
     * 通过多态性, 重写调度器验证方法
     * @author Tom 090511
     * @return bool
     */
    protected function authCheck()
    {
        $sCurrentController = & self::getControllerName();
        $sCurrentAction     = & self::getActionName();
        // 排除不需要进行验证的行为
	//edit by jack 101210
        if(($sCurrentController =="getwithdrawinfo"))
    	{
    		return TRUE;
    	}
    	//end
    	if(($sCurrentController =="default") && (in_array($sCurrentAction, 
    	        array("index","image","exit","start","center","top","menu","main","drag"))))
    	{
    		return TRUE;
    	}
    	else 
    	{	if( empty($_SESSION) )
	       	{
	       		redirect(url('default','index'));
	       	}
	       	$oSession = new model_usersession();
	    	if( $oSession->isEdgeOut(TRUE) )
	        {
	        	echo "<script>alert('你的帐户已从另外一个地方登陆了！');
	        				  top.location='./index.php?controller=default';
	        	      </script>";
	        	exit;
	        }
	       	$oAdminuser = new model_adminuser();
			$iFlag = $oAdminuser->adminAccess( $_SESSION["admin"], $sCurrentController, $sCurrentAction );
			if( $iFlag == -2 )
	   		{
	   			$this->halt( -100, '访问的管理员菜单不存在, 或未启用');
	   		}
			elseif( $iFlag == -1 )
			{
				$this->halt( -101, '管理员(组) 权限不足' );
			}
	   		elseif( $iFlag == 0 )
	   		{
	   			$this->halt( -102, '权限不足' );	
	   		}
	   		else 
	   		{
	 			return TRUE;
	   		}
    	}
    	return FALSE;
    }


    /**
     * 多态的 halt 方法
     * @author Tom 090511
     */
    protected function halt( $iCode, $sMessage='' )
    {
        switch ( $iCode )
        {
            case -4 : 
                die('控制器&动作方法名无效');
            case -1 :
                die('控制器文件名不符合规则');
            case -2 :
                die('控制器类文件中, 不包含制定的类定义');
            case -3 :
                die('控制器中指定的方法不存在 : ( ' .self::$_actionName .' ) ');
            case -100 :
                die( $sMessage );
            case -101 :
                die( $sMessage );
            case -102 :
                die( $sMessage );
        }
        exit;
    }
}
?>
