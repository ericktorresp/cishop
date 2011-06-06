<?php
/**
 * 路径: /library/includes/class/dispather.php
 * 功能: 程序调度器 ( 可是基类,也可以派生 )
 * 
 *     1, 根据当前 URL $_REQUEST 来初始化控制器名,与动作方法名.
 *        例:  http://www.xxx.com/?controller=default&action=list
 *             控制器名为: default   ( $_controller_name )
 *             动作方法为: list      ( $_action_name )
 *     
 *     2, 进行 控制器/动作方法 名称检查        (只能小写英文)
 *     3, 进行 控制器/动作方法 的使用权限判断
 *     4, 程序交付给指定控制器的指定方法 
 * 
 * 可以通过类的继承, 重写 authCheck() 和 halt() 方法.
 * 
 * @author   Tom  090523
 * @version  1.2.0
 * @package  Core
 */

class dispatcher
{
    protected static $_controllerName       = 'default';     // 默认控制器名
    protected static $_actionName           = 'index';       // 默认动作方法名
    protected static $_controllerNamePrefix = 'controller_'; // 控制器类前缀
    protected static $_actionNamePrefix     = 'action';      // 控制器类前缀

    /**
     * 基类构造函数
     * @author Tom 090511
     */
    function __construct()
    {
        self::$_controllerName = self::getControllerName();
        self::$_actionName     = self::getActionName();
        $this->authCheck(); // 控制器使用权限检查 (多态)
        self::loadController();
    }


    /**
     * 获取控制器名字, 并对控制器名的字符,进行初步安检
     * @author Tom 090511
     * @return string
     */
    public function getControllerName()
    {
        self::$_controllerName = isset( $_REQUEST[ A::getIni('apple.default.controller')] ) 
                    ? $_REQUEST[ A::getIni('apple.default.controller')] : self::$_controllerName;
        self::$_controllerName =  self::checkName(self::$_controllerName);
        return self::$_controllerName;
    }


    /**
     * 获取动作方法名字, 并对取动作方法的字符,进行初步安检
     * @author Tom 090511
     * @return string
     */
    public function getActionName()
    {
        self::$_actionName = isset( $_REQUEST[ A::getIni('apple.default.action')] ) 
                    ? $_REQUEST[ A::getIni('apple.default.action')] : self::$_actionName;
        self::$_actionName =  self::checkName(self::$_actionName);
        return self::$_actionName;
    }


    /**
     * 通过正则表达式,检查控制器&动作方法的有效性, 规则: 至少一个小写英文字符
     * @author Tom 090511
     * @param string $sName
     * @return string|error
     */
    protected function checkName( $sName )
    {
        if ( preg_match('/^[a-z\d]+$/',$sName) )
        {
            return strtolower($sName);
        }
        // 控制器&动作方法名无效
        $this->halt( -4, 'Dispatcher.Error_Controller_Name : '.$sName);
        return FALSE;
    }


    /**
     * 返回指定控制器对应的类名称
     * @author Tom 090511
     * @param string $sControllerName
     * @return string
     */
    protected function getControllerClass( $sControllerName )
    {
        $sControllerClassName = self::$_controllerNamePrefix;
        $sControllerClassName .= strtolower( $sControllerName );
        return $sControllerClassName;
    }


    /**
     * 控制器,动作方法的权限检查, 用于继承
     * @author Tom 090511
     */
    protected function authCheck()
    {
        // 默认不进行权限检查, 主要用于继承的多态
    }


    /**
     * 调用控制器的指定方法
     * @author Tom 090511
     */
    protected function loadController()
    {
        // 检查类文件存在
        $sClassName = self::getControllerClass( self::$_controllerName );
        $sLoadedClassName = A::loadClass( $sClassName, '', TRUE );
        if( $sClassName != $sLoadedClassName ) // 控制器类文件载入失败
        {
            switch ( $sLoadedClassName )
            {
                case -1 : // 控制器文件名不符合规则
                    $this->halt( -1, 'Dispater.returnCode: -1, Controller_FileName_Error : '.$sClassName);
                    break;
                case -2 : // 控制器类文件中, 不包含制定的类定义
                    $this->halt( -2, 'Dispater.returnCode: -2, Controller_Class_Not_defined');
                    break;
            }
            exit;
        }
        // 初始化控制器
        $oController = new $sClassName();
        $sActionMethod = self::$_actionNamePrefix . ucfirst(self::$_actionName);
        if ( !method_exists($oController, $sActionMethod) )
        { // 控制器中指定的方法不存在
            $this->halt( -3, 'Dispater.returnCode: -3, Controller_Method_Not_Found : '.self::$_actionName );
        }
        // 执行控制器的指定方法
        $oController->{$sActionMethod}();
    }


    /**
     * 出错处理, 可继承后重写
     * @author Tom 090511
     * 基类错误参数: 
     *       -4  = 控制器&动作方法名无效.   checkname->halt()
     *       -3  = 控制中的指定方法不存在.  loadController->halt()
     *       -2  = 控制器类文件中, 不包含制定的类定义. loadController->halt()
     *       -1  = 控制器文件名不符合规则. loadController->halt()
     */
    protected function halt( $iCode, $sMessage='' )
    {
        die( "From Dispatcher : iCode=$iCode , sMessage=$sMessage <br/>" );
    }
}
?>