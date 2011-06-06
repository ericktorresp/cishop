<?php
/**
 *  Live Customer Model;
 * 
 *  在线客服系统相关model
 * 
 *  整理用户数据、完成效验、字串加解密、初始化CHAT连接
 * 
 */

class model_livecustomer extends basemodel 
{
	
    /**
     * 获取用户信息(总代名，上级代理名),已知userid,数字型认为是UserID, 字符型认为是username
     * @param  int/string	userid/username
     * 
     * @return array 
     */
	function getUserinfo($iParam)
	{
		$oUser = new model_user();
		
	 	if ( is_string($iParam) && intval($iParam) == 0)
	 	{
	 	
	 		$iParam = $oUser->getUseridByUsername($iParam);
	 	
	 	}
	 	
	 	if ( is_numeric($iParam) && $iParam > 0 )
	 	{
	 		$sFiled = ' u.`username` AS username, u.`userid` AS userid, ut.`usertype` AS usertype, ut.`parentid` AS parentid, ut.`lvtopid` AS lvtopid ';
	 		$sWhere = ' AND u.`userid`='.$iParam;
	 		
	 		$aUserinfo 	= $oUser->getUsersProfile($sFiled,'', $sWhere, false);
	 		
	 		//获取总代名 1/10/2011 
	 		if ( $aUserinfo['usertype'] == 2 )
	 		{
	 			// 总代管理员时
	 			$sWhere2 	= ' AND u.`userid`='.$aUserinfo['parentid'];
	 		}
	 		else 
	 		{
	 			$sWhere2 	= ' AND u.`userid`='.$aUserinfo['lvtopid'];
	 		}
	 		$aTopUser 	= $oUser->getUsersProfile($sFiled,'', $sWhere2, false);
	 		$aUserinfo['topagentname'] 	= $aTopUser['username'];
	 		$aUserinfo['topagentid'] 	= $aTopUser['userid'];
	 		
	 		if ( count($aUserinfo) > 0 )
	 		{
	 			unset( $oUser );
	 			return $aUserinfo;
	 		}
	 		else 
	 		{
	 			unset( $oUser );
	 			return FALSE;
	 		}
	 		
	 	}
	 	else 
	 	{
	 		unset( $oUser );
	 		return FALSE;
	 	}
	 	
	}
	/**
	 * 读取客户在线信息 (当前登录IP，SESSION_ID)
	 *
	 * @param int $iUserId	用户ID
	 * 
	 * @return array(ip,sessionid)
	 * 
	 */
	function getUserOnlineInfo($iUserId, $sess){
		if ( intval($iUserId) <= 0 || empty($sess) ) return FALSE;
		// 获取用户最近一个SESSION存储数据
		$sSql = 'SELECT `sesskey`,`clientip`,`expiry` FROM `sessions` WHERE `userid`='.$iUserId.' and `sesskey`=\''.$sess.'\' ORDER BY `entry` DESC LIMIT 1';
		$aResult = $this->oDB->getOne($sSql);
		if ($this->oDB->errno() > 0 || empty($aResult)  )
		{
			return FALSE;
		}
		else 
		{
			return array('ip'=>$aResult['clientip'], 'sess'=>$aResult['sesskey'], 'expiry'=>$aResult['expiry'] );
		}
		
	}
	
	/**
	 * 检查用户信息的有效性
	 *
	 * @param array $aUser  (可能检查的项目,username userid ipaddr domain,是否匹配)
	 * 
	 * @return bool true/false
	 * 
	 */
	function checkUserinfo($aUser) 
	{
		if (!is_array($aUser) || empty($aUser['username']) || empty($aUser['userid']) 
		|| empty($aUser['sess'])) 
		{
			return FALSE;
		}
		// 效验SESSION ID与IP，确认客户登录了平台
		$aValidcode = $this->getUserOnlineInfo( intval($aUser['userid']), $aUser['sess'] );

		if (  intval( $aValidcode['expiry'] ) < intval( date('U') )  )
		{
			return FALSE;
		}
		// 检查在线客服获取的客户IP地址与银行大厅登录的IP地址相同，在同一SESSION中
		if ( $aUser['ipaddr'] != $aValidcode['ip'] )
		{
			return FALSE;
		}
		
		$aStand = $this->getUserinfo( intval($aUser['userid']) );
		
		if ($aStand['username'] != $aUser['username']) 	return FALSE;
		if ($aStand['userid'] 	!= $aUser['userid']) 	return FALSE;
		
		return TRUE;
	}
	
	
	/**
	 * 初始URL, 由用户在平台触发,生成URL (可供页面跳转或客户点击)
	 * @param $username 用户名
	 * @param $userid	用户ID
	 * 
	 * @return string URL
	 */
	function startChat($username,$userid)
	{
		$username = !empty($username) 	? $username : $_SESSION['username'];
		$userid	  = !empty($userid) 	? $userid 	: $_SESSION['userid'];
		$domain	  = $this->getHost();
		if (empty($username) || empty($userid) || empty($domain) )
		{
			return FALSE;
		}
		
		$oConfig  = new model_config();
		$sLiveUrl = $oConfig->getConfigs('livechat_url');
		// 目录转发式访问
		if ( $sLiveUrl == 'dirs' )
		{
			$sLiveUrl = $this->_createdomain('dirs');
		}
		$sCurrentSessionId = genSessionKey();
		$sCurrentIP = getRealIP();
		$sKey	  = $oConfig->getConfigs('livechat_key');
		$aValidcode = $this->getUserOnlineInfo( $userid , $sCurrentSessionId );
		$sMD5 	  = $this->getMD5( array($username, $userid, $sCurrentIP, $aValidcode['expiry'],$sCurrentSessionId) );
		
		/*echo 'sess:'.$sCurrentSessionId;
		print_rr($aValidcode,1,1);*/
		//$sValidcode = $this->getMD5( array($aValidcode['ip'], $aValidcode['expiry']) );
		$sGetVar  = 'username='.$username.'&userid='.$userid.
				'&ipaddr='.$sCurrentIP.'&expiry='.$aValidcode['expiry'].
				'&sess='.$sCurrentSessionId.'&md5='.$sMD5;
		$sEncodeVar  = $this->Sha1code($sGetVar,true, $sKey);
		unset($sCurrentSessionId,$sCurrentIP,$sMD5);
		if ( !empty($sLiveUrl) )
		{
			// for Mibew Messenger 接驳其他在线客服系统修改下述URL组建方式  array(系统URL,系统图标,图标远程测试)
			// return 'http://'.$sLiveUrl.'/client.php?locale=zh-cn&'.$sGetVar;
			return  array('http://'.$sLiveUrl.'/client.php?chatting='.$sEncodeVar,
			'http://'.$sLiveUrl.'/button.php',
			'http://'.$sLiveUrl.'/showbutton.php' );
		}
		
	}
	
	/**
	 * 检查客户是否有权限显示在线客服按钮
	 * 
	 * (左边主菜单屏蔽显示在线客服，顶部单独显示)
	 * 
	 * @return bool
	 * 
	 * 
	 */
	function checkMenuPermisson()
	{
		//检查权限
		$oConfig  = new model_config();
		//$iMenuid  = $oConfig->getConfigs('livechat_menuid');
		$bLiveChatPower = $oConfig->getConfigs('livechat_power'); //LIVECHAT系统总开关
		
		// 读取用户菜单  (不能在多域名混合条件下使用 即平台高低频使用了不同的域名 依靠SESSION值 )   用户权限管理
		/*$oMenus     = A::singleton('model_usermenu');
        $aMenuData  = $oMenus->getUserMenus( $_SESSION['userid'], $_SESSION['usertype'] );
        //抽取menuid
        $aMenuid = array();
        foreach ( $aMenuData AS $av)
        {
        	$aMenuid[] = $av['menuid'];
        }*/
        
		$oUserMenu 		= new model_usersession();
        $bCanUseOCS 	= $oUserMenu->checkMenuAccess( $_SESSION['userid'], 'help', 'livecustom' );
		
        //读取用户个人设置 ocs_status  用户单个控制开关
        $oUser = new model_user();
        $bOCSStatus = $oUser->getOCSStatus();
        
        //搜索 是否分配了在线客服菜单 在线客服总开关  个人设置是否打开在线客服功能
        //if ( array_search($iMenuid, $aMenuid) !== FALSE  && $bLiveChatPower == 1 && $bOCSStatus == 1)
        if ( $bCanUseOCS==1  && $bLiveChatPower==1 && $bOCSStatus==1 )
        {
        	return TRUE;
        }
        else 
        {
        	return FALSE;
        }
		
	}
	
	/**
	 * 获取主机域名
	 *
	 * @return unknown
	 */
	function getHost()
	{
		 return $_SERVER['HTTP_HOST'];
	}
	
	/**
	 * 获取用户所用在线客服域名 
	 * 		(用于多域名在线客服系统部署时,获取某总代的在线客服系统域名 byagent方式 -- 功能未写)
	 * 		可按现有总代域名分配表， 或用户现用域名实现,  添加系统参数用以程序判断使用什么方式生成域名
	 * 
	 * @param int 	$iId	总代的ID
	 * 
	 * @return string Domain
	 */
	function getDomain($iId=0)
	{
		//if ( $iId == 0 ) return $this->getHost();
		//if ( !is_numeric($iId) ) return FALSE;
		
		$oConfig = new model_config();
		$sType = $oConfig->getConfigs('livechat_domain_type');
		
		/* source extent byagent */

		switch ($sType)
		{
			case 'source':
				return $this->_createdomain('source');
				break;
			case 'extent':
				return $this->_createdomain('extent');
				break;
			case 'byagent':
				return $this->_createdomain('byagent');
				break;
			case 'dirs':
				return $this->_createdomain('dirs');
				break;
			default:
				return $this->_createdomain('dirs');
				break;
		}
		
		return $this->_createdomain('source');
		
	}
	
	/**
	 * 创建域名
	 *
	 * @param 创建方式 $sType	  
	 * 				extent 继承大厅域名  eg: 大厅 game.8e8e.net 在线客服  livechat.8e8e.net
	 * 				byagent 由总代ID得到 (完全新分配域名 新建表单存储)
	 * 
	 *  	不验证域名的有效性
	 * 
	 * return string  可用域名
	 */
	function _createdomain($sType)
	{
		if ( $sType == 'extent' )
		{
			// extent
			$aSource = explode( '.', $this->getHost() );
			$sLiveUrl = 'livechat';
			$i=0;
			foreach ($aSource AS $aStr)
			{
				if ($i > 0) $sLiveUrl .= '.'.$aStr;
				++$i;
			}
			unset($i);
			
			return $sLiveUrl;
		}
		else if ( $sType == 'dirs')
		{
			// nginx DIR rewrite
			$sDomain = $this->getHost();
			
			if ( strlen( $sDomain ) > 3 )
			{
				return $sDomain.'/livechat';
			}
			else 
			{
				return $sDomain;
			}
		}
		else 
		{
			//source 
			$oConfig = new model_config();
			return $oConfig->getConfigs('livechat_url');
		}
	}
	
	/*
 	* 效验md5码
 	*/
	function checkMD5($username, $userid, $domain, $md5){
		if ( empty($username) || empty($userid) || empty($domain) || empty($md5) ) return FALSE;
		$sNewMd5 = $this->getMD5( array($username,$userid,$domain) );
		if ($sNewMd5 == $md5)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	
	}


	/**
 	* 计算md5
 	* @param array $aArray	需要md5的变量,讲究顺序
 	* @return string
 	*/
	function getMD5($aArray)
	{
		$sStr = implode(',',$aArray);
		return  md5('FengHuang'.$sStr.'2010');
	}
	
	/**
 	* 字串加解密 
 	* (与在线客服端使用相同算法)
 	* 
 	* 采用SHA1算法，超过300个字符使用ZLIB压缩
 	* 
 	* @param string 	$string		明文 或 密文
 	* @param bool 		$isEncrypt	是否加密
 	* @param string 	$key		密匙  
 	* 
 	* @return string  密文 或 明文
 	*/
	function Sha1code($string, $isEncrypt = true, $key = KEY_SPACE){
		if (!isset($string{0}) || !isset($key{0})) {  
        	return false;  
    	}  
      
    	$dynKey = $isEncrypt ? hash('sha1', microtime(true)) : substr($string, 0, 40);  
    	$fixedKey = hash('sha1', $key);  
      
    	$dynKeyPart1 = substr($dynKey, 0, 20);  
    	$dynKeyPart2 = substr($dynKey, 20);  
    	$fixedKeyPart1 = substr($fixedKey, 0, 20);  
    	$fixedKeyPart2 = substr($fixedKey, 20);  
    	$key = hash('sha1', $dynKeyPart1 . $fixedKeyPart1 . $dynKeyPart2 . $fixedKeyPart2);  
      
    	$string = $isEncrypt ? $fixedKeyPart1 . $string . $dynKeyPart2 : (isset($string{339}) ? gzuncompress(base64_decode(substr($string, 40))) : base64_decode(substr($string, 40)));  
      
    	$n = 0;  
    	$result = '';  
    	$len = strlen($string);  
      
    	for ($n = 0; $n < $len; $n++) {  
        	$result .= chr(ord($string{$n}) ^ ord($key{$n % 40}));  
    	}
    
    	return $isEncrypt ? $dynKey . str_replace('=', '', base64_encode($n > 299 ? gzcompress($result) : $result)) : substr($result, 20, -20);
    
	}

	
}
?>