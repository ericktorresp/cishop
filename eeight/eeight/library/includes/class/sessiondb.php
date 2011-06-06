<?php
/**
 * sessiondb 数据库 SESSION 类
 * 
 * Sessions 表结构 :
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  CREATE TABLE `sessions` (
 * `entry` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'SESSION ID',
 *   `sesskey` varchar(32) NOT NULL COMMENT '实际中的SESSIONkey',
 *  `userid` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
 *  `sdata` longtext COMMENT 'SESSION内容',
 *  `expiry` int(10) unsigned NOT NULL COMMENT '过期时间',
 *  `clientip` char(15) NOT NULL DEFAULT '' COMMENT '客户端IP',
 *  `proxyip` char(15) NOT NULL DEFAULT '' COMMENT 'CDNIP',
 *  `isadmin` tinyint(1) unsigned DEFAULT '0' COMMENT '是否为管理员',
 *  PRIMARY KEY (`entry`),
 *  UNIQUE KEY `sesskey` (`sesskey`,`isadmin`),
 *  KEY `idx_del` (`expiry`),
 *  KEY `idx_search` (`sesskey`,`clientip`,`expiry`,`isadmin`)
 *  ) ENGINE=MyISAM AUTO_INCREMENT=47025 DEFAULT CHARSET=utf8;
 *
 * @author   Tom
 * @version  1.2.0
 * @package  Core
 */
class sessiondb
{
    /**
     * db 类对象
     *
     * @var db
     */
    private $oDB                = '';
    private $sSessionTableName  = 'sessions';   // MYSQL 中 SESSION 表名
    private $sSessionName       = 'CGISESSID';  // 浏览器收到的 SESSION 值的 COOKIE 变量名 
    private $iMaxLifeTime       = 45;           // 单位(分钟)  数据库增加时间 && COOKIE过期时间
    
    private $iNowTime           = 0;            // 类初始时间戳
    private $sSessionId         = '';
    private $sClientIp          = '';

    //private $sSessionCookiePath   = '/';
    //private $sSessionCookieDomain = 'mylocalhost';
    //private $sSessionCookieSecure = FALSE;

    
    /**
     * 构造函数
     *
     * @param array $aSessConfig
     *    $aSessConfig = array(
     *       $aSess = array( 'sSessionTableName'=>'xxx', 'sSessionDataTable'=>'xxx' ), 
     * 	     $aDBO  = array( 
     *                       'DBHOST' => 'localhost',
     *                       'DBPORT' => '3306',
     *                       'DBUSER' => 'root',
     *                       'DBPASS' => '121212',
     *                       'DBNAME' => 'passport',
     *                       'DBCHAR' => 'UTF8',
	 *                      ), 
     *          );
     */
    function __construct( $aSessConfig = array() )
    {
        // 初始化 sessiondb 类数据
        if( is_array($aSessConfig) && !empty($aSessConfig) )
        {
            // 
            $this->sSessionTableName = !empty( $aSessConfig['aSess']['sSessionTableName'] ) ? $aSessConfig['aSess']['sSessionTableName'] : $this->sSessionTableName;
            $this->sSessionName      = !empty( $aSessConfig['aSess']['sSessionName'] ) ? $aSessConfig['aSess']['sSessionName'] : $this->sSessionName;
            $this->iMaxLifeTime      = !empty( $aSessConfig['aSess']['iMaxLifeTime'] ) ? $aSessConfig['aSess']['iMaxLifeTime'] : $this->iMaxLifeTime;
            //$this->sSessionCookieDomain= !empty( $aSessConfig['aSess']['sSessionCookieDomain'] ) ? $aSessConfig['aSess']['sSessionCookieDomain'] : $this->sSessionCookieDomain;
            //$this->sSessionCookiePath= !empty( $aSessConfig['aSess']['sSessionCookiePath'] ) ? $aSessConfig['aSess']['sSessionCookiePath'] : $this->sSessionCookiePath;
            //$this->sSessionCookieSecure= !empty( $aSessConfig['aSess']['sSessionCookieSecure'] ) ? $aSessConfig['aSess']['sSessionCookieSecure'] : $this->sSessionCookieSecure;
        }
        
        // TODO _a高频、低频并行前期临时程序
        $this->iMaxLifeTime = 480; // 临时将 SESSION 时间改为8小时
        
        // 初始化 mysqli db 类
        /* @var $oDB db */
        if( is_array($aSessConfig) && !empty($aSessConfig['aDBO']) )
        {
            $this->oDB = A::singleton('db', $aSessConfig['aDBO']);
        }
        else
        {
            $this->oDB = A::singleton('db');
        }
        $this->iNowTime = time();


        // http://cn.php.net/manual/en/function.session-set-save-handler.php
        session_cache_limiter('private, must-revalidate');
        session_cache_expire($this->iMaxLifeTime);         // 以分钟数指定缓冲的会话页面的存活期，此设定对 nocache 缓冲控制方法无效, 默认 180
        @ini_set( 'session.name',$this->sSessionName );    // 设置SESSION_ID名  只能由字母数字组成, 默认为 PHPSESSID
        @ini_set( 'session.cookie_lifetime', 0 );          // 以秒数指定了发送到浏览器的 cookie 的生命周期, 0 表示"直到关闭浏览器"
        @ini_set( 'session.cookie_httponly', TRUE );       // 只允许 http 协议
        @ini_set( 'session.use_cookies', 1 );              // 指定是否在客户端用 cookie 来存放会话 ID。默认启用
        @ini_set( 'session.use_only_cookies', 1 );         // 启用此设定可以防止有关通过 URL 传递会话 ID 的攻击
        @ini_set( 'session.use_trans_sid', 0 );            // 指定是否启用透明 SID 支持. 默认为 0(禁用)
        @ini_set( 'session.gc_probability', 1 );           // ini 默认值=1
        @ini_set( 'session.gc_divisor', 300 );             // ini 默认值=1000
        @ini_set( 'session.gc_maxlifetime', $this->iMaxLifeTime * 60  ); // After this number of seconds, stored data will be seen as 'garbage' and ...
        //@ini_set('session.cookie_domain', 'aaa.aa.aa');

        session_set_save_handler
        (
            array(&$this, 'sessOpen' ),  
            array(&$this, 'sessClose'),
            array(&$this, 'sessGet'),
            array(&$this, 'sessSet'),
            array(&$this, 'sessDestroy'),
            array(&$this, 'sessExpired')
        );
        $this->sClientIp = getRealIP();
        session_start();
    }


    function sessOpen( $sSavePath, $sSessionId )
    {
    	return TRUE;
    }

    function sessClose()
    {
        return TRUE;
    }


    // 浏览器获取 SESSION
    function sessGet( $sSessionKey )
    {
        $sSessionKey  =  $this->genSessionKey($sSessionKey);
        $clientip     =  $this->sClientIp;
        $isAdmin      =   !empty($GLOBALS['isadmin']) && $GLOBALS['isadmin']==1 ? 1 : 0;

        $sSql = "SELECT `sdata` FROM `" . $this->sSessionTableName .
        		"` WHERE `sesskey`='$sSessionKey' AND `clientip`='$clientip' AND `expiry` > $this->iNowTime AND `isadmin`='$isAdmin' ";
        $rs = $this->oDB->getOne( $sSql );
        return isset($rs['sdata']) ? $rs['sdata'] : $rs;
    }


    // 向 SESSION 写入数据
	function sessSet( $sSessionKey, $sSessionData )
	{
	    $sSessionKey  =   $this->genSessionKey($sSessionKey);
	    $sSessionData =   $this->oDB->real_escape_string( $sSessionData );
	    $userid       =   !empty($_SESSION['userid']) ? $_SESSION['userid'] : 0;
	    $expiry       =   time() + $this->iMaxLifeTime * 60;
	    $clientip     =   $this->sClientIp;
	    $proxyip      =   $_SERVER['REMOTE_ADDR'];
	    $isAdmin      =   !empty($GLOBALS['isadmin']) && $GLOBALS['isadmin']==1 ? 1 : 0;

	    // 判断是否已有记录
	    $sSql = "SELECT 1 FROM `".$this->sSessionTableName . 
	    		"` WHERE `sesskey`='$sSessionKey' AND `clientip`='$clientip' AND `expiry` > $this->iNowTime AND `isadmin`='$isAdmin' ";
	    $rs = $this->oDB->getOne($sSql);

	    if( isset($rs[1]) && $rs[1] != 0 )
	    { // 非空, 表示 SESSION 中已有记录
	        $sSql = "UPDATE `".$this->sSessionTableName .
	        		"` SET `sdata`='$sSessionData',`expiry`='$expiry', `userid`='".$userid."', `isadmin`='".$isAdmin."' ".
	                " WHERE `sesskey`='$sSessionKey' AND `clientip`='$clientip' AND `expiry` > $this->iNowTime AND `isadmin`='$isAdmin' LIMIT 1";
	        $this->oDB->query( $sSql );
	    }
	    else 
	    { // 为空则插入数据
    	    $sSql = "REPLACE INTO `" . $this->sSessionTableName .
    	    		"`( `sesskey`,`userid`,`sdata`,`expiry`,`clientip`,`proxyip`,`isadmin` ) VALUES ".
    	            " ('$sSessionKey','$userid','$sSessionData','$expiry','$clientip','$proxyip','$isAdmin')";
    	    $this->oDB->query( $sSql );
	    }
		return $this->oDB->ar() ? TRUE : FALSE;
	}


	function sessDestroy( $sSessionKey )
	{
	    $sSessionKey  =   $this->genSessionKey($sSessionKey);
	    $clientip     =   $this->sClientIp;
	    $isAdmin      =   !empty($GLOBALS['isadmin']) && $GLOBALS['isadmin']==1 ? 1 : 0;
	    $sSql = "DELETE FROM `".$this->sSessionTableName .
	    		"` WHERE `sesskey`='$sSessionKey' AND `clientip`='$clientip' AND `expiry` > $this->iNowTime AND `isadmin`='$isAdmin' ";
	    $this->oDB->query( $sSql );
	    return $this->oDB->ar() ? TRUE : FALSE;
	}


	function sessExpired( $iExpiredTime )
	{
        $tmpTimes = time() - $iExpiredTime; // 删除早于 45*60 秒前,无活动的记录
        $sSql = "DELETE FROM `".$this->sSessionTableName ."` WHERE `expiry` < $tmpTimes ";
        $this->oDB->query( $sSql );
        return $this->oDB->ar() ? TRUE : FALSE;
	}


    function genSessionId()
    {
        $this->sSessionId = md5(uniqid(mt_rand(), true));
        return $this->sSessionId;
    }
    
    
    function genSessionKey( $sSessionId )
    {
        return genSessionKey( $sSessionId );
    }
}

?>