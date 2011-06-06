<?php
/**
 * 功能: 程序控制器 (基类)
 * 路径: /library/includes/class/basecontroller.php
 * 用途: 
 *       用于项目控制器的派生, 默认位于 _app\controller\ 目录
 *       例:  default.php => class controller_default extends basecontroller{...}
 * 
 *     根据当前 URL $_REQUEST 来初始化'控制器名'与'动作方法名'
 *       例:  http://www.xxx.com/?controller=default&action=index
 *             控制器名为: default   ( $_controller_name )
 *             动作方法为: list      ( $_action_name )
 * 
 * @author   Tom  090523
 * @version  1.2.0
 * @package  Core
 */

class basecontroller
{
    /**
	 * @var oView view
	 */
	/* @var $oView view */

    private $_controllerName = 'default';     // 默认控制器名
    private $_actionName     = 'index';       // 默认动作方法名
    
    /**
     * 用于返回给 sysMessage(,,array()) 使用的第三个默认参数(数组)
     *
     * @var array $aThisLocalionArray
     */
    public $aThisLocalionArray = array();


    /**
     * 基类构造函数
     * @author Tom 090511
     */
    function __construct()
    {
        $this->_controllerName = dispatcher::getControllerName(); // controller
        $this->_actionName     = dispatcher::getActionName(); // action
        $this->aThisLocalionArray = array( 0 => array(
        	"text" => "返回前一页", "href" => url( $this->_controllerName , $this->_actionName )));
    }


    /**
     * 获取 '控制器名'
     *   http://www.xx.com/?controller=aaa&action=bbb
     *   return = aaa
     * @author Tom 090511
     * @return string
     */
    protected function getControllerName()
    {
        return $this->_controllerName;
    }


    /**
     * 获取 '动作名'
     *   http://www.xx.com/?controller=aaa&action=bbb
     *   return = bbb
     * @author Tom 090511
     * @return string
     */
    protected function getActionName()
    {
        return $this->_actionName;
    }


    /**
     * 检查指定的动作方法是否存在
     * @author Tom 090511
     * @param string $sActionName
     * @return boolean
     */
    public function isExistsAction( $sActionName )
    {
        $sActionMethod = "action{$sActionName}";
        return method_exists( $this, $sActionMethod );
    }
}
?>