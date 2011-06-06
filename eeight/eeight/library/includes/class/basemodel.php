<?php
/**
 * 数据模型基础类
 * 路径: /library/includes/class/basemodel.php
 * 用途：项目数据模型(model)的派生
 * 
 * 使用：在实际项目里，每个模型需要建立一个构造函数实现基础类的数据模型
 * 范例:
 * 			class users
 * 			{
 * 				function __construct( $aDBO=array() )
 * 				{
 * 					parent::__construct( $aDBO );
 * 				}
 * 				.....
 * 				.....
 * 			}
 * 
 * @author 	    james,tom
 * @version	1.1.0
 * @package	core
 */

class basemodel
{
    /**
     * db 类对象
     * @var db
     */
	protected $oDB	= '';	//数据库连接对象实例
	
	/**
	 * 构造函数(初始化数据库连接对象)
	 * @access public
	 * @return void
	 */
	function __construct( $aDBO=array() )
	{
	    if( empty($aDBO) )
	    {
	        if( empty($GLOBALS['aSysDbServer']['master']) )
	        {
	            A::halt('$GLOBALS[\'aSysDbServer\'][\'master\'] is empty!');
	            exit;
	        }
	        $aDBO = $GLOBALS['aSysDbServer']['master'];
	    }
		$this->oDB = &A::singleton( 'db', $aDBO );
		/* @var $oDB db */
	}


	/**
	 * 获取全局数据库连接对象实例
	 * @access	public
	 * @return	object
	 */
	public function &getDB()
	{
		return $this->oDB;
	}
	
	

    /**
     * 获取系统执行信息
     * @access  public
     * @return  void
     */
    public function assignSysInfo()
    {
        // TODO : 生产版本时, 根据权限或开关控制此函数的返回
        $sSystemMessages = "SQL Info: ".$this->oDB->getProcessCount()." in ".$this->oDB->getProcessTime() .' Sec, ';
        if(  function_exists('memory_get_usage') )
        {
            $sSystemMessages .= "Memory Info: ". number_format(memory_get_usage()/1048576, 3) .' MB, ';
        }
        $iTimes = getTimeDiff( getMicrotime() - $GLOBALS['G_APPLE_LOADED_TIME'] );
        $sSystemMessages .= "System Spend : $iTimes Sec ";
        $GLOBALS['oView']->assign('sSystemMessagesByTom', $sSystemMessages);//exit;
        unset($sSystemMessages,$iTimes);
    }
	
}
?>