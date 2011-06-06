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
    // 通过多态性, 重写控制器验证方法
    protected function authCheck()
    {
        $sCurrentController = self::getControllerName();
        $sCurrentAction     = self::getActionName();
        $iUserId = empty($_SESSION['userid']) ? 0 : $_SESSION['userid'];
        
        /*********************权限检测****************/
        $oUser = new model_usersession();
        $result = $oUser->checkMenuAccess( $iUserId, $sCurrentController, $sCurrentAction );
        //echo $result;
        if( $result === -1 )
        {//菜单未注册
            sysMsg( "你所要访问的页面没有找到或者已删除", 2 );
        }
        elseif( $result === -2 )
        {//未登陆
        	//tom091203 $sMsg = "你的认证已过期，或者还没有通过认证，请先登陆";
        	$sMsg = "由于您长时间未操作，请重新登录";
        	$aLinks = array( array('url'=>'?controller=default&action=login') );
        	sysMsg( $sMsg, 0, $aLinks, 'top' );
        }
        elseif( $result === -3 )
        {//被后面登陆的用户挤下线
        	$sMsg = "你的帐户已从另外一个地方登陆了！";
        	$aLinks = array( array('url'=>'?controller=default&action=login') );
        	sysMsg( $sMsg, 0, $aLinks, 'top' );
        }
        elseif( $result === -4 )
        {//用户不存在或者被删除或者对应频道被关闭
        	if( !empty($_SESSION['userid']) )
        	{//如果存在session则销毁
        		session_destroy();
        	}
        	$sMsg = "个人信息错误或者连接超时，请重新登陆！";
        	$aLinks = array( array('url'=>'?controller=default&action=login') );
        	sysMsg( $sMsg, 0, $aLinks, 'top' );
        }
        elseif( $result === -7 )
        {
            sysMsg( "用户组被禁用或者不存在<br />请与管理员联系", 2 );
        }
    	elseif( $result === 1 )
        {
        	return TRUE;	// TODO: 开启此注释, 让程序在此处返回TRUE,之后代码不被执行
        }
        else 
        {
            sysMsg( "没有操作权限", 2 );
        }
        return TRUE; 
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
        die('authDispatcher #8001');
    }
    
    
}
?>