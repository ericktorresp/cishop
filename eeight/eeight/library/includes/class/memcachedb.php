<?php
/**
 * memcache 数据库操作类
 * 参考：http://cn.php.net/manual/en/book.memcache.php
 * 
 * 注意：此类继承memcache类，请确认安装了memcahce并打开了memcache扩展
 * 
 * 依赖全局：
 * 			A::$_aIni -> class.bDevelopMode		(全局开发模式), 记录显示调试信息
 * 			A::$_aIni -> ... APPLE_ON_ERROR_LOG	出错时是否把错误信息写入文件
 * 
 * 功能描述
 * ~~~~~~~~~~~~~~~~~~~~~~
 * 		--允许通过全局开关 A::$_aIni -> bDevelopMode 来显示错误信息
 * 		--允许通过全局开关 A::$_aIni -> ... APPLE_ON_ERROR_LOG 把错误信息并记录文件
 * 		--模拟数据库操作进行CRUD操作，$this->insert(), $this->update(), $this->delete(), $this->getOne(), $this->getAll()
 * 
 * @author     James
 * @version    1.0.0
 * @package    Core
 */



//如果不支持memcache扩展，或者没有安装memcache扩展则报错
if( !class_exists('Memcache') || !function_exists('memcache_connect') )
{
	$temp_message = "Failed: Memcache extension not install";
	A::halt( " From Class.memcacheSession.Exception: [" . $temp_message . "]" );
	exit;
}

	
class memcachedb extends Memcache
{
	//默认配置参数
	private $sHost		= 'localhost';	//连接主机地址
	private $iPort		= 11211;		//连接主机端口
	
	//相关调试参数
	private $bDevelopMode	= FALSE;	//开发模式,记录显示错误
	private $bIsConnect		= FALSE;	//连接状态（用于析构时关闭连接）
	
	/**
	 * 构造函数
	 * 
	 * /---code php
	 *    $aMemDBO = array( "MDBHOST" => 'localhost', 
     *                   "MDBPORT" => 11211, 
     *                );
     *    $oDb = new memcachedb($aDBO);
     * \---
	 * 
	 * @access	public
	 * @param	array	$aMemDBO	memcache连接相关设置函数
	 * 
	 */
	function __construct( $aMemDBO = array() )
	{
		//初始memcache服务器 设置
		if( FALSE != ($temp = A::getIni("class.memcachedb.config")) )
		{//先根据全局设置初始化
			$this->sHost = isset($temp['MDBHOST']) ? $temp['MDBHOST'] : 'localhost';
			$this->iPort = isset($temp['MDBPORT']) ? intval($temp['MDBPORT']) : 11211;
			unset( $temp );
		}
		$this->sHost	= isset($aMemDBO['MDBHOST']) ? $aMemDBO['MDBHOST'] : $this->sHost;
		$this->iPort	= isset($aMemDBO['MDBPORT']) ? intval($aMemDBO['MDBPORT']) : $this->iPort;
		$this->bDevelopMode = (bool)A::getIni('class.bDevelopMode');
		
		if( FALSE == $this->connect( $this->sHost, $this->iPort ) )
		{
			$this->halt( "memcache.connect Failed : Host[".$this->sHost."] Port[".$this->iPort."]" );
			exit();
		}
		$this->bIsConnect = TRUE;

	}
	
	/*
	 * 析构函数
	 */
	function __destruct()
	{
		if( $this->bIsConnect == TRUE )
		{//如果成功连接则关闭连接
			$this->close();
		}
		unset( $this->sHost );
		unset( $this->iPort );
	}
	
	/**************************************************************
	 * CRUD 模式
	 *    [C]  -  create   (insert)
	 *    [R]  -  read     (select)
	 *    [U]  -  update
	 *    [D]  -  delete
	 * ************************************************************
	 */
	/**
	 * 插入数据
	 * 
	 * @access	public
	 * @param	String	$sName	数据键名
	 * @param	[mixed]	$mValue	插入的数据值，混合类型
	 * @param	int		$iExpire 数据过期时间，默认为0，假性用不过期(实际随memecache自动清理机制过期)秒
	 * @param 	int		$iFlag	是否使用 zlib 压缩 (0不采用，1采用,默认为0)
	 * @return	boolean	成功返回 TRUE 失败返回FALSE
	 */
	public function insert( $sName, $mValue, $iExpire=0, $iFlag=0 )
	{
		return $this->set( $sName, $mValue, $iFlag, $iExpire );
	}
	
	/**
	 * 读取一条数据
	 * 
	 * @access	public
	 * @param	String	$sName	要读取的数据键名
	 * @param 	int		$iFlag	是否使用 zlib 压缩  这里和insert里必须设置一致
	 * @return [mixed]	返回数据 失败则返回FALSE
	 */
	public function getOne( $sName, $iFlag=0 )
	{
		return $this->get( $sName, $iFlag );
	}
	
	/**
	 * 读取多条数据
	 * 
	 * @access 	public
	 * @param 	array	$aNames	要读取的数据键名集合
	 * @param	array	$aFlags	是否使用 zlib 压缩  这里和insert里必须设置一致   可省略
	 */
	public function getAll( $aNames, $aFlags=array() )
	{
		if( empty($aFlags) )
		{
			return $this->get( $aNames );
		}
		return $this->get( $aNames, $aFlags );
	}
	
	/**
	 * 修改数据
	 * 
	 * @access	public
	 * @param	String	$sName	数据键名
	 * @param	[mixed]	$mValue	插入的数据值，混合类型
	 * @param	int		$iExpire 数据过期时间，默认为0，假性用不过期(实际随memecache自动清理机制过期)秒
	 * @param 	int		$iFlag	是否使用 zlib 压缩 (0不采用，1采用,默认为0)
	 * @return	boolean	成功返回 TRUE 失败返回FALSE
	 */
	public function update( $sName, $mValue, $iExpire=0, $iFlag=0 )
	{
		return $this->replace( $sName, $mValue, $iFlag, $iExpire );
	}
	
	/**
	 * 删除一条数据
	 * 
	 * @access	public
	 * @param 	String	$sName	要删除数据的键名
	 * @param	int		$iTimeOut	多久以后执行删除，0为立即删除，默认0。秒
	 * @return 	boolean	成功返回TRUE，失败返回FALSE
	 */
	public function delete( $sName, $iTimeOut=0 )
	{
		return parent::delete( $sName, $iTimeOut );
	}
	
	/**
	 * 删除所有数据
	 * 
	 * @access 	public
	 * @return 	boolean	成功返回TRUE，失败返回FALSE
	 */
	public function deleteAll()
	{
		return $this->flush();
	}
	
	/**
	 * 依赖全局设置自定义出错消息处理
	 * 
	 * @access	private
	 * @param	string	$sMessage	出错消息
	 * @return	void
	 */
	private function halt( $sMessage='' )
	{
		//是否写入日志文件
		if( (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG) )
		{
			$GLOBALS['oLogs']->addDebug( $sMessage, 'MemcacheError' );
		}
		if( TRUE == (bool)$this->bDevelopMode )
		{
			A::halt( "From class.memcachedb.Error: " . $sMessage );
		}
	}
}

?>