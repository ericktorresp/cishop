<?php
/**
 * 利用memcache实现多服务器共享session机制的封装。
 * 
 * 功能：
 * ~~~~~~~~~~~~
 * 		-自主实现基于memcache的session存储功能
 * 		-根据全局设置，记录错误日志
 * 		-在线用户统计，更新
 * 		-销毁指定session ID的所有session值（踢出用户）
 * 		-获取在线用户的基本信息（ID，用户名，IP，session ID，）
 * 
 * 这个类就是实现Session的功能, 基本上是通过设置客户端的Cookie来保存SessionID,
 * 然后把用户的数据保存在服务器端的memcache服务器中,最后通过Cookie中的Session ID来确定一个数据是否是用户的, 
 * 然后进行相应的数据操作
 * 
 * 注意: 本类必须要求PHP安装了Memcache扩展, 获取Memcache扩展请访问: http://pecl.php.net
 * 		 必须在任何输出之前实例化本类
 * 
 * TODO: -垃圾自动回收清理机制, memcache多服务器分发机制
 * 
 * @author   James
 * @version  1.0.0
 * @package  Core
 */


class memsession
{
	/*
	 * Session基本配置
	 */
	private $sSessionID		= '';		//session 唯一标志
	private $sSessionPrefix	= 'sess_';	//session 标志前缀 
	private $iExpireTime	= 1800;	//session 缓存过期时间，单位秒，默认30分钟
	private $sCookieName	= '_sessionHandler';	//SESSION 的Cookie名称
	
	/*
	 * Cookie 基本设置
	 */
	private $sCookieDomain		= '';					//Cookie 域名信息
	private $sCookiePath		= '/';					//Cookie 文件位置
	private $iCookieExpireTime	= 0;					//SESSION 的Cookie过期时间
	private $bCookieSecure		= FALSE;				//是否可以通过HTTPS安全传递cookie
	/*
	 * Memcache 配置
	 */
	private $aMemcacheConfig	= array();	//memcache 主机配置
	public $oMemcache = NULL;	//memcache 实例
	
	/*
	 * 在线用户
	 */
	private $aOnlineUser	= array();	//在线用户列表
	private $sOnlineMD5		= '';		//在线用户MD5值，用于判断在线用户是否改变
	private $aUserInfo		= array(	//session共享时统一的用户信息变量命名
									'id'	=> 'userid',
									'name'	=> 'username',
								);	
	
	/*
	 * 安全机制设置
	 */
	private $sIp = '';		//客户端IP限制 
	
	/*
	 * 其他调试参数
	 */
	private $bDevelopMode = FALSE;		//开发模式，用于记录和显示相关错误信息
	
	/**
	 * 构造函数（是否实例化的时候启动session)
	 * 
	 * @access	public
	 * @param	string	$sSessionID		-Session ID,缺省是空，新创建一个Session ID。
	 * @param	int		$iExpireTime	- Session失效时间,缺省是0,当浏览器关闭的时候失效, 该值单位是秒
	 */
	function __construct( $iExpireTime=0, $sSessionID='' )
	{
		//载入全局session配置
		if( FALSE != ($temp = A::getIni("class.memsession.sessionConfig")) )
		{
			if( is_array($temp) )
			{
				$this->sSessionPrefix	= isset($temp['sessionPrefix']) ? $temp['sessionPrefix'] : 'sess_';
				$this->iExpireTime		= isset($temp['sessionExpireTime']) ?
											intval($temp['sessionExpireTime']) : 1800;
				$this->sCookieName		= isset($temp['sessionCookieName']) ?
											$temp['sessionCookieName'] : '_sessionHandler';
				$this->aMemcacheConfig	= isset($temp['memcacheConfig']) ? (array)$temp['memcacheConfig'] : array();
				
			}
			else
			{
				$this->iExpireTime		= 1800;
				$this->sSessionPrefix	= 'sess_';
				$this->sCookieName		= '_sessionHandler';
				$this->aMemcacheConfig	= array();
			}
			unset( $temp );
		}
		else
		{
			$this->iExpireTime		= 1800;
			$this->sSessionPrefix	= 'sess_';
			$this->sCookieName		= '_sessionHandler';
			$this->aMemcacheConfig	= array();
		}
		
		//载入全局cookie设置
		if( FALSE != ($temp = A::getIni("cookieConfig")) )
		{
			if( is_array($temp) )
			{
				if( isset($temp['expire']) && (bool)intval($temp['expire']) )
				{
					$this->iCookieExpireTime = CURRENT_TIMESTAMP+intval($temp['expire']);
				}
				else 
				{
					$this->iCookieExpireTime = 0;
				}
				$this->sCookieDomain		= isset($temp['domain']) ? $temp['domain'] : '';
				$this->sCookiePath			= isset($temp['path']) ? $temp['path'] : '/';
				$this->bCookieSecure		= isset($temp['secure']) ? (bool)$temp['secure'] : FALSE;
			}
			else
			{
				$this->sCookieDomain	= '';
				$this->sCookiePath		= '/';
				$this->iCookieExpireTime= 0;
				$this->bCookieSecure	= FALSE;
			}
			unset( $temp );
		}
		else
		{
			$this->sCookieDomain	= '';
			$this->sCookiePath		= '/';
			$this->iCookieExpireTime= 0;
			$this->bCookieSecure	= FALSE;
		}
		$this->iCookieExpireTime= intval($iExpireTime)>0 ? 
									CURRENT_TIMESTAMP+intval($iExpireTime) : $this->iCookieExpireTime;

		$this->sIp = getRealIP();
		//从cookie中读取sessionID值或者指定Session ID值
		if( $sSessionID == '' && !empty($_COOKIE[$this->sCookieName]) )
		{
			$this->sSessionID = $_COOKIE[$this->sCookieName];
		}
		else
		{
			$this->sSessionID = $sSessionID;
		}
		//如果从cookie里获取或者指定了Session ID，则验证session ID的合法性
		if( $this->sSessionID )
		{
			$temp_sessionID = substr( $this->sSessionID, 0, 32 );
			if( $this->getValidCode($temp_sessionID) == substr($this->sSessionID,32) )
			{
				$this->sSessionID = $temp_sessionID;
			}
			else
			{
				$this->sSessionID = '';
			}
		}
		//如果存在指定 Session ID的值，则读取该ID下保存的Session数组集合
		if( $this->sSessionID )
		{
			$this->getSession();
		}
		else 
		{//如果不存在则新开始一个session
			$this->createSessionID();
			setcookie( $this->sCookieName, $this->sSessionID.$this->getValidCode($this->sSessionID),
					   $this->iCookieExpireTime, $this->sCookiePath, 
					   $this->sCookieDomain, $this->bCookieSecure );
			$GLOBALS['_SESSION'] = array();
			$this->setSession();
		}
		
		//给一直在线活跃的用户延长session过期时间
		register_shutdown_function(array(&$this, 'closeSession'));
	}
	
	/*
	 * 析构函数
	 */
	function __destruct()
	{
		if( $this->oMemcache )
		{//如果memcache连接实例存在则注销
			$this->oMemcache = NULL;
		}
		unset($this->aMemcacheConfig);
	}
	
	/**
	 * 注册一个session变量
	 * 
	 * @access	public
	 * @param	String	$sName 变量名
	 * @param	String	$sValue 变量值
	 * @return	boolean	如果变量已经存在且值相同则返回false，值不相同则替换，成功返回true
	 */
	public function register( $sName, $sValue )
	{
		if( isset($GLOBALS['_SESSION'][$sName]) && $GLOBALS['_SESSION'][$sName] == $sValue )
		{//变量已经存在而且值相同
			return FALSE;
		}
		$GLOBALS['_SESSION'][$sName] = $sValue;
		$this->setSession();
		return TRUE;
	}
	
	/**
	 * 销毁一个已经注册的变量
	 * 
	 * @access	public
	 * @param	String	$sName 要销毁的变量名
	 * @return	boolean	成功返回true，失败返回false
	 */
	public function unregister( $sName )
	{
		if( isset($GLOBALS['_SESSION'][$sName]) )
		{
			unset( $GLOBALS['_SESSION'][$sName] );
			$this->setSession();
		}
		return TRUE;
	}
	
	/**
	 * 获取一个已经注册的变量
	 * 
	 * @access	public
	 * @param	String	$sName 要获取的变量名,如果为空则获取所有的
	 * @return	unknow	获取的变量值
	 */
	public function get( $sName='' )
	{
		if( $sName=='' )
		{
			return $GLOBALS['_SESSION'];
		}
		else
		{
			if( !isset($GLOBALS['_SESSION'][$sName]) )
			{
				return FALSE;
			}
			return $GLOBALS['_SESSION'][$sName];
		}
	}
	
	/**
	 * 获取所有在线用户列表
	 * 
	 * @access public
	 * @return array	在线用户列表
	 */
	public function getOnlineUsers()
	{
		if( empty($this->sOnlineMD5) )
		{//如果未读出所有在线用户列表，则先读出用户列表
			$this->_getAllOnlineUser();
		}
		return $this->aOnlineUser;
	}
	
	/**
	 * 根据用户ID判断某个用户是否在线，如果不在返回FALSE，在线返回TRUE
	 * 
	 * @access 	public
	 * @param 	int	$iUserID	//用户ID
	 * @return	boolean			//不在返回FALSE，在线返回TRUE
	 */
	public function isOnline( $iUserID )
	{
		$iUserID = intval( $iUserID );
		if( empty($this->sOnlineMD5) )
		{//如果未读出所有在线用户列表，则先读出用户列表
			$this->_getAllOnlineUser();
		}
		if( empty($this->aOnlineUser) )
		{//如果在线用户列表为空，则直接返回TRUE,跳过数组搜索，提高效率
			return FALSE;
		}
		if( !array_key_exists($iUserID,$this->aOnlineUser) )
		{//如果不存在给定用户
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * 根据用户ID获取在线信息
	 * 
	 * @access 	public
	 * @param 	int		$iUserID	//用户ID
	 * @return 	[mixd]	//如果存在则返回用户信息数组，否则返回FALSE；
	 */
	public function getOnlineInfo( $iUserID )
	{
		if( !$this->isOnline($iUserID) )
		{
			return FALSE;
		}
		return $this->aOnlineUser[$iUserID];
	}
	
	/**
	 * 根据用户ID，把该用户从线上踢掉
	 * 
	 * @access	public
	 * @param 	int		$iUserID	//用户ID
	 * @return	boolean	//成功返回TRUE，失败返回FALSE
	 */
	public function outOnline( $iUserID )
	{
		if( FALSE != ($temp_info = $this->getOnlineInfo($iUserID)) )
		{
			return $this->destroy( $temp_info['sessionID'],$temp_info[$this->aUserInfo['id']] );
		}
		return TRUE;
	}
	
	/**
	 * 检查memcache的实例是否存在，如果不存在则创建
	 * 
	 * @access	private
	 * @return	boolean	成功或者失败
	 */
	private function checkMemcache()
	{
		//如果不支持memcache扩展，或者没有安装memcache扩展则报错
		if( !class_exists('memcachedb') )
		{
			$temp_message = "Failed: Memcache extension not install";
			//日志记录操作，只有打开日志功能才记录
			if( (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG) )
			{
				$GLOBALS['oLogs']->addDebug( $temp_message, "memsessionError" );
			}
			//调试模式检查，只有开启了开发模式的调试才能打印
			if( $this->bDevelopMode )
			{
				A::halt( " From Class.memcacheSession.Exception: [" . $temp_message . "]" );
			}
			return FALSE;
		}
		//如果实例已经存在则直接返回
		if( $this->oMemcache && is_object($this->oMemcache) )
		{
			return TRUE;
		}
		
		//新建实例
		$this->oMemcache = new memcachedb( $this->aMemcacheConfig );
		return TRUE;
	}
	
	/**
	 *	获取Session ID
	 * 
	 * @access	public
	 * @return	Sting
	 */
	public function getSessionID()
	{
		return	$this->sSessionID;
	}
	
	/**
	 * 生成一个Session ID
	 * 
	 * @access	private
	 * @return	String 
	 */
	private function createSessionID()
	{
		$this->sSessionID = md5( uniqid(mt_rand(), TRUE) );
		return $this->sSessionID;
	}
	
	/**
	 * 通过客户端IP段和浏览器信息加强session安全性。
	 * 
	 * @access	private
	 * @param	String	$sSessionID 指定的session ID
	 * @return	String	返回检验码
	 */
	private function getValidCode( $sSessionID )
	{
		static $ip='';
		if( $ip == '' )
		{
			$ip = substr( $this->sIp, 0, strrpos($this->sIp,'.') );
		}
		$temp_string = !empty( $_SERVER['HTTP_USER_AGENT'] ) ? 
						$_SERVER['HTTP_USER_AGENT'] . $ip . $sSessionID : $ip . $sSessionID;
		return sprintf( "%08x", crc32($temp_string) );
	}
	
	/**
	 * 获取已经存储在memcache上面的session Key
	 * 
	 * @access	private
	 * @param	String	$sessionID	指定一个session ID
	 * @return	String	$sessionKey
	 */
	private function getSessionKey( $sSessionID='' )
	{
		$sessionKey = $sSessionID == '' ? $this->sSessionPrefix . $this->sSessionID : $sSessionID;
		return $sessionKey;
	}
	
	/**
	 * 获取所有在线用户列表
	 * 
	 * @access public
	 * @return boolean
	 */
	private function _getAllOnlineUser()
	{
		if( !empty($this->sOnlineMD5) )
		{//如果已经读出了所有在线用户列表，则直接返回
			return TRUE;
		}
		$this->checkMemcache();
		$temp_onlineUser = $this->oMemcache->getOne( '__OnlineUser' );
		if( !is_array($temp_onlineUser) || empty($temp_onlineUser) )
		{
			$this->aOnlineUser = array();
			$this->sOnlineMD5 = md5( serialize($this->aOnlineUser) );
		}
		else
		{
			$this->aOnlineUser = $temp_onlineUser;
			$this->sOnlineMD5 = md5( serialize($this->aOnlineUser) );
			//对过期的用户进行清理
			foreach( $this->aOnlineUser as $key=>$val )
			{
				$temp_expireTime = intval($val['time']) + intval($val['expire']);
				if( $temp_expireTime < CURRENT_TIMESTAMP )
				{//如果过期则删除
					unset( $this->aOnlineUser[$key] );
				}
			}
			unset( $temp_expireTime );
			if( $this->sOnlineMD5 != md5(serialize($this->aOnlineUser)) )
			{//如果数据发生了改变，则回存
				$this->sOnlineMD5 = md5( serialize($this->aOnlineUser) );
				$this->_saveOnlineUser();
			}
		}
		unset($temp_onlineUser);
		return $this->aOnlineUser;
	}
	
	/**
	 * 读取session的值
	 * 
	 * @access	private
	 * @return	void
	 */
	private function getSession()
	{
		$this->checkMemcache();
		$sessionKey = $this->getSessionKey();
		$session = $this->oMemcache->getOne( $sessionKey );
		//如果指定的session不存在则创建
		if( !is_array($session) || empty($session) )
		{
			$GLOBALS['_SESSION'] = array();
			$this->setSession();
		}
		else
		{
			$GLOBALS['_SESSION'] = $session;
		}
		unset( $session );
		
	}
	
	
	/**
	 * 保存当前的session值到memcache
	 * 
	 * @access	private
	 * @param	String	$sSessionID
	 * @return	boolean	成功或者失败 
	 */
	private function setSession()
	{
		$this->checkMemcache();
		$sessionKey = $this->getSessionKey();
		if( empty($GLOBALS['_SESSION']) )
		{//如果全局函数中的session值为空，则在memcache中重新建立或者设置为空
			$temp_ret = $this->oMemcache->insert( $sessionKey, $GLOBALS['_SESSION'], $this->iExpireTime );
		}
		else 
		{//如果全局函数中session的值不为空，则替换memcache中的值
			$temp_ret = $this->oMemcache->update( $sessionKey, $GLOBALS['_SESSION'], $this->iExpireTime );
			
			/*
			 * 如果存在用户登陆信息，则更新用户在线列表
			 */
			$id 	= $this->aUserInfo['id'];
			$name 	= $this->aUserInfo['name'];
			if( isset( $GLOBALS['_SESSION'][$id] ) )
			{
				$GLOBALS['_SESSION'][$name] = isset($GLOBALS['_SESSION'][$name]) ? $GLOBALS['_SESSION'][$name] : '';
				//如果在线用户列表存在该用户信息
				if( FALSE != ($temp_info = $this->getOnlineInfo($GLOBALS['_SESSION'][$id])) )
				{
					if( $temp_info[$name] != $GLOBALS['_SESSION'][$name] ||
						$temp_info['sessionID'] != $this->sSessionID ||
						$temp_info['ip'] != $this->sIp ||
						$temp_info['expire'] != $this->iExpireTime ||
						(intval($temp_info['time'])+10) < CURRENT_TIMESTAMP )
					{//如果用户信息改变或者两次保存间隔时间大于10秒，则重新保存
						$this->aOnlineUser[$GLOBALS['_SESSION'][$id]][$name]		= $GLOBALS['_SESSION'][$name];
						$this->aOnlineUser[$GLOBALS['_SESSION'][$id]]['sessionID']	= $this->sSessionID;
						$this->aOnlineUser[$GLOBALS['_SESSION'][$id]]['ip']			= $this->sIp;
						$this->aOnlineUser[$GLOBALS['_SESSION'][$id]]['expire']		= $this->iExpireTime;
						$this->aOnlineUser[$GLOBALS['_SESSION'][$id]]['time'] 		= CURRENT_TIMESTAMP;
						$this->_saveOnlineUser();
					}
					unset( $temp_info );
				}
				else 
				{
					//重组用户信息
					$temp_userInfo = array();
					$temp_userInfo[$id]		= $GLOBALS['_SESSION'][$id];
					$temp_userInfo[$name]	= $GLOBALS['_SESSION'][$name];
					$temp_userInfo['sessionID']					= $this->sSessionID;
					$temp_userInfo['ip']						= $this->sIp;
					$temp_userInfo['time']						= CURRENT_TIMESTAMP;
					$temp_userInfo['expire']					= $this->iExpireTime;
					//加入在线列表
					$this->aOnlineUser[$GLOBALS['_SESSION'][$id]] = $temp_userInfo;
					unset( $temp_userInfo );
					$this->_saveOnlineUser();
				}
			}//更新用户在线列表结束
			unset( $id );
			unset( $name );
		}
		//如果保存session失败则根据相关设置写入日志或者打印调试信息
		if( !$temp_ret )
		{
			$temp_message = "Failed: Save sessiont data failed, please check memcache server";
			//日志记录操作，只有打开日志功能才记录
			if( (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG) )
			{
				$GLOBALS['oLogs']->addDebug( $temp_message, "memsessionError" );
			}
			//调试模式检查，只有开启了开发模式的调试才能打印
			if( $this->bDevelopMode )
			{
				A::halt( " From Class.memcacheSession.Exception: [" . $temp_message . "]" );
			}
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * 回调函数，用于对一直在线的用户自动延长过期时间
	 * 
	 * @access	public
	 * @return boolean 成功与否
	 */
	public function closeSession()
	{
		return $this->setSession();
	}
	
	/**
	 * 销毁某个sessionID下所有已注册的seesion变量
	 * 
	 * @access	public
	 * @param	string	$sSessionID //要注销的session ID
	 * @param 	int		$iUserID	//要销毁的user ID，如果有的话则从在线列表里删除
	 * @return	void
	 */
	public function destroy( $sSessionID='', $iUserID=0 )
	{
		//从memcache删除已经注册的seesion值
		$this->checkMemcache();
		$sessionKey = $this->getSessionKey( $sSessionID );
		$this->oMemcache->delete( $sessionKey );
		$iUserID = intval($iUserID);
		//如果注销自己的则清空全局session值,并设置cookie过期
		if( $sSessionID == '' || $sSessionID == $this->sSessionID )
		{
			if( isset($GLOBALS['_SESSION'][$this->aUserInfo['id']]) )
			{
				$iUserID = intval($GLOBALS['_SESSION'][$this->aUserInfo['id']]);
			}
			else
			{
				$iUserID = 0;
			}
			$GLOBALS['_SESSION'] = array();
			//设置cookie过期
			setcookie( $this->sCookieName, $this->sSessionID, 1, 
					$this->sCookiePath, $this->sCookieDomain, $this->bCookieSecure );
		}
		//把用户从在线列表里删除
		$this->_deleteOnline( $iUserID );
	}
	
	/**
	 * 根据用户的 ID 把用户从在线列表里删除
	 * 
	 * @access	private
	 * @param 	int	$iUserID	//用户 ID
	 * @return	boolean 	成功返回TRUE，失败返回FALSE
	 */
	private function _deleteOnline( $iUserID=0 )
	{
		$iUserID = intval( $iUserID );
		if( $this->isOnline($iUserID) )
		{
			unset($this->aOnlineUser[$iUserID]);
		}
		if( $this->_saveOnlineUser() )
		{
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 回存用户在线列表
	 * 
	 * @access private
	 * @return boolean	成功返回TRUE，失败返回FALSE
	 */
	private function _saveOnlineUser()
	{
		$this->checkMemcache();
		if( $this->sOnlineMD5 == md5(serialize($this->aOnlineUser)) )
		{//如果数据没有发生改变则直接返回
			return TRUE;
		}
		$temp_ret = $this->oMemcache->insert( '__OnlineUser', $this->aOnlineUser );
		//如果保存session失败则根据相关设置写入日志或者打印调试信息
		if( !$temp_ret )
		{
			$temp_message = "Failed: Save Onlineuser data failed, please check memcache server";
			//日志记录操作，只有打开日志功能才记录
			if( (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG) )
			{
				$GLOBALS['oLogs']->addDebug( $temp_message, "memsessionError" );
			}
			//调试模式检查，只有开启了开发模式的调试才能打印
			if( $this->bDevelopMode )
			{
				A::halt( " From Class.memcacheSession.Exception: [" . $temp_message . "]" );
			}
			return FALSE;
		}
		unset( $temp_ret );
		return TRUE;
	}

}

?>