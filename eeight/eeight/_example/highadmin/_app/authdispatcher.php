<?php
/**
 * 功能: 继承于程序调度器 Dispatcher + 项目权限检测
 * 路径: /_app/dispather.php
 * 
 *     1, 根据当前 URL $_REQUEST 来初始化控制器名,与动作方法名.
 *        例:  http://www.xxx.com/?controller=default&action=list
 *             控制器名为: default   ($_controller_name)
 *             动作方法为: list      ($_action_name)
 *     
 *     2, 进行 控制器/动作方法 名称检查        (只能小写英文)
 *     3, 进行 控制器/动作方法 的使用权限判断
 *     4, 程序交付给指定控制器的指定方法 
 * 
 * 可以通过类的继承, 重写 authCheck() 和 halt() 方法.
 * 
 * @author   Tom
 * @version  1.1.0
 * @package  Core
 */

if (!defined('IN_APPLE') || IN_APPLE!==TRUE) die('Error code: 0x1000');

class authdispatcher extends dispatcher
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
    	if(($sCurrentController == "default") && (in_array($sCurrentAction,
    	        array("index","image","exit","start","center","top","menu","main","drag"))) || $sCurrentController == "nei3chei6voh7n" )
    	{
    		return TRUE;
    	}
    	else 
    	{
    		if( empty($_SESSION) )
	       	{
	       		redirect(url('default','index'));
	       	}
	       	$oSession = new model_usersession( );	       	
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
     * 出错处理, 可继承后重写
     * 参数: 
     *       -4  = 控制器&动作方法名无效.   checkname->halt()
     *       -3  = 控制中的指定方法不存在.  loadController->halt()
     *       -2  = 控制器类文件中, 不包含制定的类定义. loadController->halt()
     *       -1  = 控制器文件名不符合规则. loadController->halt()
     */
    protected function halt( $iCode, $sMessage )
    {
        //die( "From Class=".get_class($this)." : iCode=$iCode , sMessage=$sMessage <br/>" );
        $sMessage = $sMessage == "" ? "权限不足" : $sMessage;
        sysMessage( $sMessage, 1 );
    	//die('authDispatcher #8001');
    }
    
    
}
?>