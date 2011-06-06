<?php
/**
 * 用户session记录数据模型，普通用户和管理员通用
 *
 * 功能：
 *      CRUD
 * -----------------------------------------------
 * 		--update				--修改用户的sesionkey值
 * 
 * 		--isEdgeOut				--用于判断用户是否被挤下线
 * 		--loginOut				--强制踢掉某个用户,清除他的session值
 * 		--checkMenuAccess		--根据用户 ID, 检查用户权限
 * 
 * @author      Floyd
 * @version     1.0.0
 * @package     highgame
 * @since 	    2010/01/26
 */

class model_usersession extends basemodel 
{
	/**
	 * 构造函数
	 * 
	 * @access	public
	 * @return	void
	 */
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
		//初始化跨服务器session连接
		$this->oSessionDB = & A::singleton( 'db', $GLOBALS['aSysDbServer']['session'] );
        /* @var $oSessionDB db */
	}



	/**
	 * 修改用户的sesionkey值
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId		//用户ID
	 * @param	string	$sSessionKey	//用户的sessionKey值
	 * @param 	boolean	$bIsAdmin		//是否为管理员
	 * @return 	mixed	//成功返回所影响的行数，失败返回FALSE
	 */
	public function update( $iUserId, $sSessionKey='', $bIsAdmin=FALSE )
	{
		if( empty($iUserId) )
		{
			return FALSE;
		}
		$iUserId     = intval( $iUserId );
		$sSessionKey = daddslashes( $sSessionKey );
		if( (bool)$bIsAdmin )
		{
			$bIsAdmin = 1;
		}
		else 
		{
			$bIsAdmin = 0;
		}
		return $this->oSessionDB->update( 'usersession', array('sessionkey' => $sSessionKey), 
									" `userid`='".$iUserId."' AND `isadmin`='".$bIsAdmin."'" );
	}



	/**
	 * 判断当前用户浏览器的session key和数据库里的session key 是否相同
	 * 用于判断用户是否被挤下线，如果被挤下线，则把用户的session清掉
	 * @deprecated 调用在登陆以后的操作
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	boolean	$bIsAdmin	//是否为管理员
	 * @return 	boolean		//成功返回TRUE，失败返回FALSE
	 */
	public function isEdgeOut( $bIsAdmin=FALSE )
	{
		if( (bool)$bIsAdmin )
		{
			$bIsAdmin   = 1;
			$iUserId 	= empty($_SESSION['admin']) ? 0 : $_SESSION['admin'];
		}
		else 
		{
			$bIsAdmin   = 0;
			$iUserId 	= empty($_SESSION['userid']) ? 0 : $_SESSION['userid'];
		}
		if( empty($iUserId) )
		{
			return TRUE;
		}
		//浏览器的session key
		$sessionKey = genSessionKey();
		$sSql       = "SELECT 1 FROM `usersession` WHERE 
				        `userid`='".$iUserId."' AND `sessionkey`='".$sessionKey."' AND `isadmin`='".$bIsAdmin."'";
		$this->oSessionDB->query( $sSql );
		$iNums = $this->oSessionDB->ar();
		if( $iNums > 0 )
		{ // 没有被踢掉
			return FALSE;
		}
		// 清除当前的session
		session_destroy();
		return TRUE;
	}



	/**
	 * 强制踢掉某个用户,清除他的 session 值
	 * 
	 * @access	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//要踢掉的用户ID
	 * @return 	boolean	//成功返回TRUE，失败返回FALSE
	 */
	public function loginOut( $iUserId )
	{
		if( empty($iUserId) || !is_numeric($iUserId) )
		{
			return FALSE;
		}
		return $this->oSessionDB->delete( 'sessions', " userid='".$iUserId."'" );
	}



	/**
	 * 根据用户 ID, 检查用户权限.
	 * 
	 * @access 	public
	 * @param 	int		$iUserId	//用户ID
	 * @param 	string	$sControlName	//控制器名
	 * @param 	string 	$sActionName	//动作名
	 * @return 	int		0 : 没有相应权限
	 * 					-1：菜单没有被注册或者被禁用
	 * 					-2：未登陆用户
	 * 					-3：被踢下线的用户
	 * 					-4：用户在该银行频道中没有活跃帐户
	 * 					-5：只可登陆，显示主体框架，其他操作均没权限
	 * 					-6：可登陆，另外只能充提
	 * 					-7：用户组被禁用或者不存在
	 * 					-8：
	 *
	 */
	public function checkMenuAccess( $iUserId = 0, $sControlName = '', $sActionName = '' )
	{
		$iUserId = intval( $iUserId );
	    //01:是否登陆做检测
        if( empty($_SESSION['userid']) || !is_numeric($_SESSION['userid']) )
        { // 未登陆
            return -1;
        }
        $sSysOneOline = getConfigValue( "sysoneonline", "yes" );
        if( strtolower($sSysOneOline) == "yes" )
        {
	        //02: 是否被后面登陆的用户挤下线
	        if( $this->isEdgeOut() )
	        {//被后面登陆的用户挤下线
	            return -2;
	        }
        }
        
	    //04：检测用户是否开通了此频道//TODO:频道下面的入口状态检测
        if( $iUserId > 0 )
        {
           $sSql    = " SELECT uc.`entry` FROM `userchannel` AS uc 
                        LEFT JOIN `channels` AS c ON uc.`channelid`=c.`id`
                        WHERE uc.`userid`='".$iUserId."' AND uc.`channelid`='".SYS_CHANNELID."' 
                        AND uc.`isdisabled`='0' AND c.`isdisabled`='0' ";
           $aResult = $this->oDB->getDataCached( $sSql, 10 );
           if( empty($aResult) )
           {
              return -3;
           }
        }
	    //08:获取用户信息
		if($_SESSION['usertype'] == 2)
		{
			$sSql = " SELECT ut.`userid`,ut.`username`,ut.`usertype`,uc.`groupid`,uc.`extendmenustr` 
					  FROM `userchannel` AS uc LEFT JOIN `usertree` AS ut ON (ut.`userid`=uc.`userid` AND uc.`channelid`=".SYS_CHANNELID.") WHERE ut.`isdeleted`='0' AND ut.`userid`='".$iUserId."' ";
		}
		else
		{
			$sSql = " SELECT ut.`userid`,ut.`username`,ut.`usertype`,ugs.`groupid`,ugs.`extendmenustr` 
					  FROM `usergroupset` AS ugs LEFT JOIN `usertree` AS ut ON ut.`userid`=ugs.`userid` WHERE ut.`isdeleted`='0' AND ut.`userid`='".$iUserId."' ";
		}
        $aUserData = $this->oDB->getDataCached($sSql,10);
        if( empty($aUserData) )
        {
            return -6;  // 用户在该频道中没有活跃帐户
        }
        $aUserData = $aUserData[0];
        
        //05: 检测用户是否被冻结
	    if( $_SESSION['isfrozen'] > 0 )
        {//用户被冻结
//            if( $aMenus['actionlog'] > 0 ) //记录日志
//            {
//                $this->writeLog($iUserId, $sControlName, $sActionName,
//                                "冻结用户访问\"".$aMenus['title']."\"失败","访问页面失败:权限不够");
//            }
            return -4;
        }
        
		//05：检测菜单是否注册
		$sSql  = "SELECT `title`,`menuid`,`actionlog`,`ismenu`,`islink`,`islabel` FROM `usermenu` " 
				  . " WHERE `controller`='" . daddslashes($sControlName)
				  . "' AND `actioner`='" . daddslashes($sActionName) . "' AND `isdisabled`='0' ";
		$aMenus = $this->oDB->getDataCached( $sSql );
		if( empty($aMenus) )
		{ // 菜单没有注册(或者被禁用)
			return -5;	
		}
		if( count($aMenus)>1 )
		{//如果菜单有多个
			if( !empty($_REQUEST['curmid']) && is_numeric($_REQUEST['curmid']) && intval($_REQUEST['curmid']) > 0 )
			{//需要通过附加参数指定所进入的菜单[mgmid表示菜单ID]
				$iMid     = intval($_REQUEST['curmid']);
	            $aTempMenu = array();
	            foreach( $aMenus as $v )
	            {
	                if( $iMid == $v['menuid'] )
	                {
	                    $aTempMenu = $v;
	                    break;
	                }
	            }
	            if( empty($aTempMenu) )
	            {//没有找到对应的菜单
	            	return -5;
	            }
	            $aMenus = $aTempMenu;
			}
			else 
			{//没有指定具体菜单
				return -5;  
			}
		}
		else
		{
			$aMenus = $aMenus[0];
		}
		
	    
		/*******************************06:防火墙应用 *************************************************/
		$sSql = "SELECT r.`rangetype`,r.`userip`,r.`userid`,r.`message`,a.`menustr`,a.`msgtouser`,a.`msgtoadmin`,
				 a.`messagetouser`,a.`messagetoadmin`,a.`functionname`,a.`functionargs`,a.`isexit` 
				 FROM `firewallrules` AS r LEFT JOIN `firewallaction` AS a ON r.`actionid`=a.`id` 
				 WHERE r.`isdisabled`='0' AND a.`isdisabled`='0' AND (`rangetype`='2' OR `userid`='".$iUserId."') ";
		$aFireData = $this->oDB->getDataCached( $sSql );
		if( FALSE ==  empty($aFireData) )
		{//如果有防火墙规则，则启用(函数调用第一个参数都是menuid,后面可以自定义)
			foreach( $aFireData as $fire )
			{
				$aArgs = array();
				if( empty($fire['functionargs']) )
				{
					$aArgs = $aMenus['menuid'];
				}
				else
				{
					$aArgs[]   = $aMenus['menuid'];
					$temp_args = explode(',',$fire['functionargs']);
					for( $i=0; $i<count($temp_args); $i++ )
					{
						$aArgs[] = $fire[$temp_args[$i]];
					}
					$aArgs = implode(',',$aArgs);
				}
				$isInFire = FALSE;
				if( empty($fire['functionname']) || !method_exists($this,$fire['functionname']) )
				{//默认执行函数
					//TODO:默认函数  default($args);
				}
				else 
				{//执行自定义函数(所有函数都在本类中定义)
					eval("\$isInFire = \$this->\$fire['functionname']($aArgs);");
				}
				if( FALSE == (bool)$isInFire )
				{//不符合防火墙范围
					continue;
				}
				else 
				{//符合防火墙范围(被限制的)
					/******发送消息*********/
					if( $fire['msgtouser'] == 1 && !empty($fire['messagetouser']) 
						&& ( !empty($fire['userid']) || !empty($_SESSION['userid'])  )
					)
					{//给用户发消息
						$aMsgData = array();
						$aMsgData['msgtypeid']	= MESSAGE_TYPE_FIREWALL;
						$aMsgData['senderid']	= 0;
						$aMsgData['subject']	= "系统消息";
						$aMsgData['sendergroup']= 2;
						$aMsgData['content']	= $fire['messagetouser'];
						$aMsgData['sendtime'] 	= date("Y-m-d H:i:s");
						$this->oDB->doTransaction();
						$iResult = $this->oDB->insert('msgcontent', $aMsgData);
						if( empty($iResult) )
						{//写入消息失败
							$this->oDB->doRollback();
						}
						$aMsgData = array( 
									'msgid'        =>$iResult,
									'receiverid'   =>empty($fire['userid']) ? $_SESSION['userid'] : $fire['userid'], 
									'receivergroup'=>0 );
						$this->oDB->insert('msglist', $aMsgData);
						if( $this->oDB->affectedRows() < 1 )
						{
							$this->oDB->doRollback();
						}
						$this->oDB->commit();
					}
					if( $fire['msgtoadmin'] == 1 && !empty($fire['messagetoadmin']) )
					{//给管理员发消息
						$aMsgData = array();
						$aMsgData['msgtypeid']	= MESSAGE_TYPE_FIREWALL;
						$aMsgData['senderid']	= 0;
						$aMsgData['subject']	= "防火墙消息";
						$aMsgData['sendergroup']= 2;
						$aMsgData['content']	= $fire['messagetoadmin'];
						$aMsgData['sendtime'] 	= date("Y-m-d H:i:s");
						$this->oDB->doTransaction();
						$iResult = $this->oDB->insert('msgcontent', $aMsgData);
						if( empty($iResult) )
						{//写入消息失败
							$this->oDB->doRollback();
						}
						$aMsgData = array( 
									'msgid'        =>$iResult,
									'receiverid'   =>0, 
									'receivergroup'=>1 );
						$this->oDB->insert('msglist', $aMsgData);
						if( $this->oDB->affectedRows() < 1 )
						{
							$this->oDB->doRollback();
						}
						$this->oDB->commit();
					}
					/*************是否终止程序***********/
					if( $fire['isexit'] == 1 )
					{
						exit;
					}
				}
			}//end foreach
		}
		/******************************************防火墙检测完毕*****************************************/
		
		//07: 跳过一些不需要做权限检测的功能
		if( $aMenus['ismenu'] == 0 && $aMenus['islink'] == 0 && $aMenus['islabel'] == 0 )
		{
			return 1;
		}
		//09:获取用户分组信息
		if( $aUserData['usertype'] < 2 )
		{
			$sSql = "SELECT `menustrs` FROM `usergroup` 
					 WHERE `groupid`='".$aUserData['groupid']."' AND `isdisabled`='0' ";
		}
		else 
		{
			$sSql = "SELECT apm.`menustrs` FROM `proxygroup` pg,`admin_proxy_menu` apm 
					 WHERE pg.`groupid`='".$aUserData['groupid']."' AND pg.`isdisabled`='0' AND pg.groupid=apm.groupid";
		}
		$mGroupMenu = $this->oDB->getDataCached($sSql);
		if( empty($mGroupMenu) )
		{
			return -7;	//用户组被禁用或者不存在
		}
		$mGroupMenu = $mGroupMenu[0]['menustrs'];
		if( !empty($aUserData['extendmenustr']) )
		{
			$mGroupMenu .= (empty($mGroupMenu) ? "" : ',').$aUserData['extendmenustr'];
		}
		$mGroupMenu = explode(',', trim($mGroupMenu));
		//10:检测是否有菜单权限
		if( FALSE == (in_array($aMenus['menuid'], $mGroupMenu)) )
		{
			if( $aMenus['actionlog'] > 0 )//记录日志
			{
				$this->writeLog($iUserId, $sControlName, $sActionName,
								"访问\"".$aMenus['title']."\"失败", "访问页面失败:权限不够");
			}
			return -8;
		}
		else 
		{
			if( $aMenus['actionlog'] > 0 )//记录日志
			{
				$this->writeLog($iUserId, $sControlName, $sActionName, 
				                "访问\"".$aMenus['title']."\"成功", "成功访问页面");
			}
			return 1;	//通过权限检查
		}	
	}



	/**
	 * 记录日志(内部调用)
	 * @author 	james	09/05/17
	 */
	private function writeLog( $iUserId, $sController='', $sAction='', $sTitle='', $sContent='' )
	{
		$aLogdata = array(
						'userid'	=> $iUserId,
						'clientip'	=> getRealIP(),
						'proxyip'	=> $_SERVER['REMOTE_ADDR'],
						'times'		=> date("Y-m-d H:i:s"),
						'querystring'=> getUrl(),
						'controller'=> $sController,
						'actioner'	=> $sAction,
						'title'		=> $sTitle,
						'content'	=> $sContent,
						'requeststring'=>addslashes(serialize($_REQUEST))
					);
		return $this->oDB->insert( 'userlog',$aLogdata );
	}
}
?>