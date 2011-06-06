<?php
/**
 * 文件 : /_app/model/agent.php
 * 功能 : 模型 - 总代模型
 * 
 *  功能：
 *    - agentGetByAdmin		获取指定销售管理员所有的总代
 *    - agentList       	根据管理员身份[普通|销售管理员]获取总代列表
 *    - delDomain           根据总代和域名之间绑定的ID数组删除总代和域名的关系
 *    - domianList          获取域名分页
 *    - domianUpdate		更新域名和总代的关系
 *    - getAgentByDomain   	根据域名获取相关的总代
 *    - isAgent				判断用户是不是总代
 *    - removeProxy			解除销售管理员和总代的关系
 *    - setProxy			关联销售管理员和总代的关系
 *    - updateCreateAccount	更新总代开户权限
 *    - userChannel			获取总代和频道之间的关系
 *    - userCreateAccount	获取总代的开户权限
 *    - userDomainAdd		绑定总代和域名
 * 
 * @author   saul
 * @version  1.0.0
 * @package  passportadmin
 * @since    2009-06-16
 */
class model_agent extends basemodel
{
	/**
	 * 获取所有总代
	 * @author	SAUL	090517
	 * @return	array
	 */
	function & agentList( $iNums='', $iPage='')
	{
		
		$iAdminId = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? intval($_SESSION["admin"]): 0;
		if( $iAdminId ==0 )
		{
			$aTemp = array();
			return $aTemp;
		}
		$this->oDB->query("SELECT * FROM `admingroup` WHERE `issales`='1'  AND "
	            ."`groupid`=(SELECT `groupid` FROM `adminuser` WHERE `adminid`='".$iAdminId."' ORDER BY adminname)");
		if( $this->oDB->ar()==1 )
		{ // 如果管理员属于销售组
			return $this->oDB->getAll("SELECT `userid`,`username` FROM `usertree` WHERE `parentid`='0' AND `isdeleted` = 0"
			." AND `userid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdminId."') ORDER BY username");
		}
		else
		{
			return $this->oDB->getAll("SELECT `userid`,`username` FROM `usertree` WHERE `parentid`='0' AND `isdeleted` = 0 ORDER BY username");
		}
	}



	/**
	 * 获取销售管理员和总代的关系
	 * @author	SAUL	090517
	 * @param	int		$iAdminId
	 * @return	array
	 */
	function agentGetByAdmin( $iAdminId = 0 )
	{
		$iAdminId = is_numeric($iAdminId) ? intval($iAdminId) : 0;
		if( $iAdminId==0 )
		{
			return $this->oDB->getAll("SELECT `usertree`.`username`,`usertree`.`userid`,`adminproxy`.`entry`,"
			."`adminuser`.`adminname` FROM `usertree` LEFT JOIN `adminproxy` ON (`usertree`.`userid`=" 
			."`adminproxy`.`topproxyid`) LEFT JOIN `adminuser` ON (`adminproxy`.`adminid`=`adminuser`."
			."`adminid`) WHERE (`usertree`.`isdeleted` = 0 AND `usertree`.`parentid`='0') ORDER BY usertree.username");
		}
		else
		{
			return $this->oDB->getAll("SELECT `usertree`.`username`,`usertree`.`userid`,`adminproxy`."
			."`entry`,`adminuser`.`adminname` FROM `usertree` LEFT JOIN `adminproxy` ON (`usertree`.`userid`"
			."= `adminproxy`.`topproxyid`) LEFT JOIN `adminuser` ON (`adminproxy`.`adminid`="
			."`adminuser`.`adminid`) WHERE (`usertree`.`isdeleted` = 0 AND `usertree`.`parentid`='0') AND "
			."`adminproxy`.`adminid`='$iAdminId' ORDER BY usertree.username");
		}
	}



	/**
	 * 解除总代和销售关系
	 * @author	SAUL	090517
	 * @param	array	$aUser
	 * @return	BOOL
	 */
	function removeProxy( $aUser )
	{
		foreach( $aUser as $iKey => $iValue )
		{
			if( !is_numeric($iValue) )
			{
				unset($aUser[$iKey]);
			}
		}
		if( count($aUser)>0 )
		{
			$this->oDB->query("DELETE FROM `adminproxy` WHERE `topproxyid` IN(" 
			            . implode(',', $aUser) . ")" );
			return ($this->oDB->errno()==0);
		}
		else
		{
			return FALSE;
		}
	}



	/**
	 * 设置总代和销售之间的关系
	 * @author	SAUL	090517
	 * @param	array	$aUser
	 * @param	int		$iAdminId
	 * @return	BOOL
	 */
	function setProxy( $aUser, $iAdminId )
	{
		$iAdminId = is_numeric($iAdminId) ? intval($iAdminId) : 0;
		if ($iAdminId <= 0 )
		{
			return FALSE;
		}
		$this->oDB->query("SELECT `adminid` FROM `adminuser` WHERE `adminid`='".$iAdminId."' AND " 
						."`groupid` IN (SELECT `groupid` FROM `admingroup` WHERE `issales`='1')");
		if( $this->oDB->ar()==0 )
		{
			return FALSE;
		}
		foreach( $aUser as $iKey => $iValue )
		{
			if( !is_numeric($iValue) )
			{
				unset($aUser[$iKey]);//非数字
			}
			else
			{
				$this->oDB->query("SELECT `adminid` FROM `adminproxy` WHERE `topproxyid`='".$iValue."'");
				if( $this->oDB->ar()>0 )
				{
					if( $this->isAgent($iValue) )
					{
						$this->oDB->query("UPDATE `adminproxy` SET `adminid`='".$iAdminId."' WHERE" 
									."`topproxyid` ='".$iValue."'"); //更新
					}
					else
					{
						$this->oDB->query("DELETE FROM `adminproxy` WHERE `topproxyid` ='".$iValue."'");
					}
				}
				else
				{
					if( $this->isAgent($iValue) )
					{
						$this->oDB->query("INSERT INTO `adminproxy` (`adminid`,`topproxyid`) VALUES" 
								."('".$iAdminId."','".$iValue."')");//插入更新总代关系
					}
				}
			}
		}
		return TRUE;
	}



	/**
	 * 更新总代的开户权限
	 * @author	SAUL	090607
	 * @param	array	$aUser
	 * @param	array	$aOpenlevel
	 * @param	array	$aNumadd
	 * @param   array   $aNumSub
	 * @param	array	$aAllow
	 * @return	int
	 */
	function updateCreateAccount( $aUser ,$aOpenlevel ,$aNumadd ,$aNumSub, $aAllow  )
	{ //检查数据完整性
		if( is_array($aUser) )
		{
			foreach($aUser as $iKey => $aValue )
			{
				if( !(is_numeric($aOpenlevel[$aValue]) && is_numeric($aNumadd[$aValue]) && is_numeric($aAllow[$aValue]) &&is_numeric($aNumSub[$aValue])) )
				{
					unset($aUser[$iKey]);
				}
				if( $aNumadd[$aValue]*$aNumSub[$aValue]!=0)
				{ //保证两者中至少有一个为0
					unset($aUser[$iKey]);
				}
			}
		}
		if ( !empty($aUser) )
		{
			$this->oDB->doTransaction();
			foreach ($aUser as $user)
			{
				if( $aAllow[$user]==1 )
				{
					if( $aOpenlevel[$user]==0 )
					{
						$result = $this->oDB->query("UPDATE `users` LEFT JOIN `usertree` ON "
						."(`users`.`userid`=`usertree`.`userid`) SET `users`.`authadd`='1'"
						." WHERE `usertree`.`lvtopid`='".$user."'"); //更新用户authadd
					}
					else
					{
						$result = $this->oDB->query("UPDATE `users` LEFT JOIN `usertree` ON "
						."(`users`.`userid`=`usertree`.`userid`) SET `users`.`authadd`="
						."(substring_index(`usertree`.`parenttree`,',',".($aOpenlevel[$user]-1).")"
						."<>`usertree`.`parenttree`) WHERE `usertree`.`lvtopid`='".$user."'");
					}
					if ( $result === FALSE )
					{
						$this->oDB->doRollback();
						return -1001;
					}
				}
				else
				{
					$result = $this->oDB->query("UPDATE `users` LEFT JOIN `usertree` ON "
					."(`users`.`userid`=`usertree`.`userid`) SET `users`.`authadd`='0'"
					." WHERE `usertree`.`lvtopid`='".$user."'");
					if ($result === FALSE)
					{
						$this->oDB->doRollback();
						return -1002;
					}
				}
				$result = $this->oDB->query("UPDATE `topproxyset` SET `proxyvalue`='"
							.$aOpenlevel[$user]."' WHERE `proxykey`='open_level' AND `userid`='"
							.$user."'"); //更新开户级别
				if ($result === FALSE)
				{
					$this->oDB->doRollback();
					return -1003;//更新开户权限失败
				}
				//更新用户的能否开户
				$result = $this->oDB->query("UPDATE `topproxyset` SET `proxyvalue`='"
					.$aAllow[$user]."' WHERE `proxykey`='can_create' AND `userid`='".$user."'");
				if ($result === FALSE)
				{ //更新开户权限失败
					$this->oDB->doRollback();
					return -1004;
				}
				if ($aNumadd[$user]>0)
				{ //开户数额	
					$this->oDB->query("UPDATE `users` SET `addcount`=addcount+".$aNumadd[$user]." WHERE `userid`='".$user."'");
					if ($this->oDB->ar()<>1)//更新某个总代开户数额时候失败
					{
						$this->oDB->doRollback();
						return -1005;
					}
				}
				else
				{
				    $this->oDB->query("UPDATE `users` SET `addcount`=addcount-".abs($aNumSub[$user])." WHERE `userid`='".$user."'");
                    if ($this->oDB->errno()>0)//更新某个总代开户数额时候失败
                    {
                        $this->oDB->doRollback();
                        return -1005;
                    }
				}	
			}
			//对开户个数小于0的情况进行更新
		    $this->oDB->query("UPDATE `users` SET `addcount`='0' WHERE `addcount`<'0'");
            if ($this->oDB->errno()>0)//更新某个总代开户数额小于0时候失败
            {
                $this->oDB->doRollback();
                return -1005;
            }
			$this->oDB->doCommit();
			return 1;//完成
		}
		else
		{
			return 0;//没有数据提交
		}
	}



	/**
	 * 获取总代和频道之间的关系
	 * @author	SAUL	090517
	 * @param	array	$aUser
	 * @param	array	$aChannel
	 * @return	array
	 */
	function userChannel( $aUser, $aChannel )
	{
		if( !(empty($aUser) || empty($aChannel)) )
		{
			return $this->oDB->getAll("SELECT * FROM `userchannel` WHERE `userid` IN (".join(",",$aUser).")" 
				."AND `channelid` IN (".join(",",$aChannel).")");
		}
		else
		{
			return array();
		}
	}



	/**
	 * 获取总代的开户权限
	 * @author	SAUL	090517
	 * @param 	string	$sWhere
	 * @return	array
	 */
	function userCreateAccount( $sWhere =" and 1" )
	{
		if( empty($sWhere) )
		{
			$sWhere = " and 1";
		}
		$sIdList = "";
		// 查询用户列表
		$sSql = "SELECT * FROM `usertree` WHERE `isdeleted` = 0 AND `usertype` = 1 AND `parentid` = 0";
		$aUsers = $this->oDB->getAll( $sSql );
		foreach ($aUsers as $v){
			$sIdList .= $v['userid'] . ',';
		}
		$sIdList = substr($sIdList, 0, -1);
		$sSql = "SELECT a.`userid`,b.`proxyvalue` AS `open_level`,a.`proxyvalue` AS `can_create`,"
                        . "c.`username`,c.`addcount`,(sum(d.`addcount`)-c.`addcount`) AS `sumcount` "
                        . "FROM `topproxyset` AS a, `topproxyset` AS b LEFT JOIN `users` AS c ON (b.`userid`=c.`userid`)"
                        . "LEFT JOIN `usertree` AS ut ON (b.`userid` = ut.`lvtopid`)"
                        . "LEFT JOIN `users` AS d ON (ut.`userid`=d.`userid` )"
                        . " WHERE a.`userid`=b.`userid` AND  a.`proxykey`='can_create' AND b.`proxykey`='open_level' AND a.userid IN ({$sIdList}) ".$sWhere
                        . "GROUP BY ut.`lvtopid` ORDER BY c.username";
		return $this->oDB->getAll( $sSql );
	}



	/**
	 * 绑定总代和域名
	 * @author	SAUL	090517
	 * @param	int		$iDomainId
	 * @param	array	$aUser
	 * @return	BOOL
	 */
	function userDomainAdd( $iDomainId, $aUser )
	{
		$iDomainId = is_numeric($iDomainId) ? intval($iDomainId) : 0;
		if( $iDomainId <=0 )
		{
			return FALSE;
		}
		if( is_array($aUser) )
		{
			foreach($aUser as $iValue)
			{
				if( is_numeric($iValue) )
				{
					if( $this->isAgent($iValue) )
					{
						$this->oDB->query("SELECT * FROM `userdomain` WHERE `userid`='".$iValue."' AND `domainid`='".$iDomainId."'");
						if( $this->oDB->ar()==0 )
						{
							$this->oDB->query("INSERT INTO `userdomain` (`userid`,`domainid`) VALUES ('".$iValue."','".$iDomainId."')");
						}
					}
				}
			}
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}



	/**
	 * 更新域名和总代的关系
	 * @author	SAUL
	 * @param	int		$iDomainId
	 * @param	array	$aUserId
	 * @return	bool
	 */
	function domianUpdate( $iDomainId, $aUserId )
	{
		$iDomainId = is_numeric($iDomainId) ? intval($iDomainId):0;
		if ( $iDomainId <= 0 )
		{
			return FALSE;
		}
		$aTemp  = $this->oDB->getAll(" SELECT `userid` FROM `userdomain` where `domainid`='".$iDomainId."'");
		$aUsers = array();
		foreach ($aTemp as $aUser)
		{
			if (is_numeric($aUser["userid"]))
			$aUsers[] = intval($aUser["userid"]);
		}
		unset($aTemp);
		foreach ($aUserId as $iKey => $iValue)
		{
			if ( !is_numeric( $iValue ) )
			{
				unset( $aUserId[$iKey] );
			}
			else 
			{
				$aUserId[$iKey] = intval($iValue);
			}
		}
		$aUserDel = array_diff( $aUsers, $aUserId );
		$aUserAdd = array_diff( $aUserId, $aUsers );
		if (!empty($aUserDel))
		{
			$this->oDB->query("DELETE FROM `userdomain` WHERE `domainid`='".$iDomainId."'"
				." and `userid` in (".join(",",$aUserDel).")");
		}
		if (!empty($aUserAdd))
		{
			$this->userDomainAdd( $iDomainId , $aUserAdd);
		}		
		return TRUE;
	}



	/**
	 * 检测是不是总代
	 * @author	SAUL	090517
	 * @param	int		$iUserId
	 * @return	BOOL
	 */
	function isAgent( $iUserId )
	{
		$iUserId = is_numeric($iUserId) ? intval($iUserId) : 0;
		if( $iUserId <=0 )
		{
			return FALSE;
		}
		$this->oDB->query("SELECT * FROM `usertree` WHERE `userid`='".$iUserId."' AND `parentid`='0'");
		return ($this->oDB->ar()>0);
	}



	/**
	 * 获取域名分页
	 * @author 	SAUL	090517
	 * @param	String	$sWhere
	 * @param	int		$iNums
	 * @param	int		$iPage
	 * @param 	bool	$bGetAllAgent 获取所有总代ID	1/11/2011
	 * @return	array
	 */
	function domianList( $sWhere ='1', &$iNums , $iPage ,$bGetAllAgent=false)
	{	//获取总代ID
		if ( $bGetAllAgent )
		{
			//获取全部总代
			$sSql = "SELECT * FROM `userdomain`";
			$aAgent['results'] = $this->oDB->getAll( $sSql );
		}
		else 
		{
			$sSqlCount = 'SELECT count(DISTINCT `userid`) AS TOMCOUNT FROM `userdomain` WHERE '.$sWhere;
			$aAgent = $this->oDB->getPageResult( 'userdomain', 'DISTINCT `userid`', $sWhere, 15, $iPage, '', '', $sSqlCount );
			
		}
		
		//根据总代分配
		$aUserId = array();
		foreach( $aAgent['results'] as $aResult )
		{
			$aUserId[] = $aResult["userid"];
		}
		$iNums = $aAgent['affects'];
			
		unset($aAgent);
		if ( !empty($aUserId) )
		{	//获取总代下面的域名
			$sSql = "SELECT * FROM `userdomain` AS ud "
			        . "LEFT JOIN `domains` AS d ON (ud.`domainid`= d.`id`) "
					."LEFT JOIN `usertree` AS ut ON (ut.`userid` = ud.`userid`) "
					. "WHERE ut.`isdeleted` = 0 AND ud.`userid` IN (".join(",",$aUserId).")";
			$aDomainL = $this->oDB->getAll($sSql);
			$aDomains = array();
			foreach ($aDomainL as $aDom) 
			{
				$aDomains[$aDom["userid"]][$aDom["domainid"]] = array(
					"entry" => $aDom["entry"],	"domain" => $aDom["domain"], "bygroupid" => $aDom["bygroupid"]
				);
			}
			return $aDomains;
		}
		else 
		{
			return array();
		}
	}



	/**
	 * 根据Id删除域名和管理员之间的关系
	 * @author	SAUL	090517
	 * @param	array	$aId
	 * @return	BOOL
	 */
	function delDomain( $aId )
	{
		if( is_array($aId) )
		{
			foreach( $aId as $ikey => $iId )
			{
				if ( !is_numeric($iId) )
				{
					unset($aId[$ikey]);
				}
			}
			if ( !empty($aId) )
			{
				$this->oDB->query("DELETE FROM `userdomain` WHERE `entry` in (".join(",",$aId).")");
				return ($this->oDB->errno()==0);
			}
		}
		return FALSE;
	}



	/**
	 * 根据域名获取总代用户ID
	 * @author	SAUL
	 * @param	mixed	$mDomain
	 * @return	array
	 */
	function getAgentByDomain( $mDomain )
	{
		if( !is_array($mDomain) )
		{
			$aTemp = array();
			if( !is_numeric($mDomain) || $mDomain<=0 )
			{
				return $aTemp;
			}
			else 
			{
				return $this->oDB->getAll("SELECT `userid`,`domainid` FROM `userdomain` WHERE `domainid`='".intval($mDomain)."'");
			}
		}
		else 
		{
			foreach( $mDomain as $iKey=>$iDomain )
			{
				if(!is_numeric($iDomain))
				{
					unset($mDomain[$iKey]);
				}
			}
			if( count($mDomain)>0 )
			{
				return $this->oDB->getAll("SELECT `userid`,`domainid` FROM `userdomain` WHERE `domainid` IN (".join(",",$mDomain).")");
			}
			else 
			{
				return array();
			}
		}
	}



	/**
	 * 域名同步
	 * @param $agpDBO array 高频数据库的连接参数
	 * TODO _a高频、低频并行前期临时程序
	 */
	/*function synUrl( $agpDBO )
	{
		$aUrl = $this->oDB->getAll("SELECT U.`userid`,D.`domain` FROM `usertree` AS U"
		." LEFT JOIN `userdomain` AS UD ON(U.`userid`=UD.`userid`) LEFT JOIN `domains` AS D on(UD.`domainid`=D.`id`)"
		." WHERE U.`parentid`='0'");
		$aUrls = array();
		foreach($aUrl as $url)
		{
			$aUrls[$url["userid"]][] = $url["domain"];
		}
		unset($aUrl);
		$oDB = new db( $agpDBO );
		foreach($aUrls as $iUserId=>$aUrl)
		{
			$sUrl = join(",",$aUrl);
			$oDB->query("UPDATE `tempusermap` set `dpuserdomain`='".$sUrl."' where `dpuserid`='".$iUserId."'");
		}
		unset( $oDB );
	}*/



	/**
	 * 获取总代和总代关系组之间的关系
	 *
	 * @return unknown
	 */
	function agentUserTeamget()
	{
		return $this->oDB->getAll("SELECT UT.`userid`,UT.`username`,UG.`groupname`,UG.`teamid`,"
		."UG.`groupid`,UG.`isspecial` FROM `userchannel` AS UC LEFT JOIN `usergroup` AS UG "
		."ON( UC.`groupid` = UG.`groupid`) LEFT JOIN `usertree` AS UT on( UT.`userid` = UC.`userid`)"
		." WHERE UT.`isdeleted` = 0 AND UT.`parentid`='0' AND UC.`channelid`='0' ORDER BY username");
	}
	
	
	
	/**
	 * 获取 在线充提许可列表
	 *
	 * @param str $sWhere
	 * @return array DB database
	 * 3/15/2010
	 */
	public function userAuthPayment( $aWhere = array() )
	{

		if ( ($aWhere[0] < 0) && ($aWhere[1] < 0) ) unset($aWhere);
		// 单表查询 topproxyset 表  返回
		$sSql = "SELECT userid,proxykey,proxyvalue FROM topproxyset WHERE proxykey='can_payment' OR proxykey='payment_level'";
		$aSet = $this->oDB->getAll( $sSql );
		foreach ( $aSet AS $aS){
			$aSeted[$aS['userid']]['userid2'] = $aS['userid'];
			if ($aS['proxykey'] == 'can_payment') $aSeted[$aS['userid']]['can_payment'] = $aS['proxyvalue'];
			if ($aS['proxykey'] == 'payment_level') $aSeted[$aS['userid']]['payment_level'] = $aS['proxyvalue'];
		}
		// 不包括总代管理员 usertype ONLY 1
		$sSql2 = "SELECT userid,username,usertype FROM usertree WHERE `isdeleted` = 0 AND lvproxyid=0 AND usertype=1";
		$aUsers = $this->oDB->getAll( $sSql2 );

		// 组装返回数组 根据传入条件筛选
		foreach ($aUsers AS $aU){
			//过滤
			unset($bCheckA,$bCheckB,$bCheckC);
			if (isset($aWhere) && is_array($aWhere) ){
				if (  ($aWhere[0] >= 0) && ($aSeted[$aU['userid']]['payment_level'] == $aWhere[0]) ){
					$bCheckA = true;
				}else{
					$bCheckA = false;
				}
				
				if ( ($aWhere[1] >= 0) && ($aSeted[$aU['userid']]['can_payment'] == $aWhere[1]) ) {
					$bCheckB = true;
				}else{
					$bCheckB = false;
				}
				
				if (  ($aWhere[0] >= 0) && ($aWhere[1] >= 0) ){
					$bCheckC = $bCheckA && $bCheckB;
				}else{
					$bCheckC = $bCheckA || $bCheckB;
				}
				
			}else{
				$bCheckC = true;
			}
				
			if ($bCheckC === true){
				
				$aNewReturn[$aU['userid']]['userid'] = $aSeted[$aU['userid']]['userid2'] ? $aSeted[$aU['userid']]['userid2'] : $aU['userid'];
				$aNewReturn[$aU['userid']]['username'] = $aU['username'];
				$aNewReturn[$aU['userid']]['usertype'] = $aU['usertype'];
				$aNewReturn[$aU['userid']]['can_payment'] = $aSeted[$aU['userid']]['can_payment'] ? $aSeted[$aU['userid']]['can_payment'] : 0;
				$aNewReturn[$aU['userid']]['payment_level'] = $aSeted[$aU['userid']]['payment_level'] ? $aSeted[$aU['userid']]['payment_level'] : 0;
			}
		}
		
		return $aNewReturn;
	}
	
	
	/**
	 * 更新总代在线支付权限
	 *
	 * @param array $aUser			用户ID数组
	 * @param array $aPaymentLevel	做运算的 代理级别		
	 * @param array $aAllow			是否许可
	 * @return bool
	 * 3/15/2010
	 */
	public function updateAuthPayment( $aUser, $aPaymentLevel, $aAllow  )
	{ //检查数据完整性
		
		if( is_array($aUser) ){
			foreach($aUser as $iKey => $aValue )
			{
				if( !is_numeric($aPaymentLevel[$aValue])  )
				{
					unset($aUser[$iKey]);
				}
				
			}
		}
		if ( !empty($aUser) ){
			$this->oDB->doTransaction();
			foreach ($aUser as $user)
			{
				$sCSql = "SELECT userid FROM `topproxyset` WHERE (`proxykey`='can_payment' OR `proxykey`='payment_level') AND `userid`=".$user;
				$aChk = $this->oDB->getOne($sCSql);
				
				if ( count($aChk) > 0){
					$sSql = "UPDATE `topproxyset` SET `proxyvalue`='".$aPaymentLevel[$user]."' WHERE `proxykey`='payment_level' AND `userid`=".$user;
					$result = $this->oDB->query($sSql);
					
					if ($result === FALSE)
					{
						$this->oDB->doRollback();
						return -1;
					}
					
					$iValue = isset($aAllow[$user])&&($aAllow[$user] == 1) ? 1 : 0;
					$sSql2 =  "UPDATE `topproxyset` SET `proxyvalue`='".$iValue."' WHERE `proxykey`='can_payment' AND `userid`=".$user;
					$result = $this->oDB->query($sSql2);
				
					if ($result === FALSE)
					{
						$this->oDB->doRollback();
						return -1;
					}
				}else{
					$sSql = "INSERT INTO `topproxyset` (`userid`,`proxykey`, `proxyvalue`) VALUES ($user,'payment_level',".$aPaymentLevel[$user].")";
					$result = $this->oDB->query($sSql);
					if ($result === FALSE)
					{
						$this->oDB->doRollback();
						return -1;//更新payment权限失败
					}
					unset($iValue);
					$iValue = ($aAllow[$user] == 1) ? 1 : 0;
					$sSql2 = "INSERT INTO `topproxyset` (`userid`,`proxykey`, `proxyvalue`) VALUES ($user,'can_payment',".$iValue.")";
					$result = $this->oDB->query($sSql2);
				
					if ($result === FALSE)
					{ 
						$this->oDB->doRollback();
						return -1;
					}
				} //end 
				
			}

			$this->oDB->doCommit();
			return 1;//完成
		}
		else{
			return 0;//没有数据提交
		}
	}
	
	
	/**
	 * 获取 快速银行充值 权限层级
	 *
	 * @param str $sWhere		
	 * @param int	$iBankId		涉及银行ID
	 * 
	 * @return array DB database
	 * 3/15/2010
	 */
	public function userAuthDepositLv( $aWhere = array(), $iBankId )
	{

		if ( ($aWhere[0] < 0) && ($aWhere[1] < 0) ) unset($aWhere);
		
		// 银行ID 检查
		$oDeposit		= new model_deposit_depositlist(array(),'','array');
    	$aDepositList 	= $oDeposit->getDepositArray('all');
		$aBankIdArray 	= $aDepositList[0];
		
		$iBankId = intval($iBankId);
		if (  !is_numeric($iBankId) || $iBankId <= 0 
			|| array_search( $iBankId, $aBankIdArray) === FALSE )
		{
			return FALSE;
		}
		
		$sProxykey1 = 'can_deposit_'.$iBankId;
		$sProxykey2 = 'deposit_level_'.$iBankId;
		// 单表查询 topproxyset 表  返回
		$sSql = "SELECT userid,proxykey,proxyvalue FROM topproxyset WHERE proxykey='".$sProxykey1."' OR proxykey='".$sProxykey2."'";
		$aSet = $this->oDB->getAll( $sSql );
		foreach ( $aSet AS $aS){
			$aSeted[$aS['userid']]['userid2'] = $aS['userid'];
			if ($aS['proxykey'] == $sProxykey1) $aSeted[$aS['userid']]['can_payment'] = $aS['proxyvalue'];
			if ($aS['proxykey'] == $sProxykey2) $aSeted[$aS['userid']]['payment_level'] = $aS['proxyvalue'];
		}
		// 不包括总代管理员 usertype ONLY 1
		$sSql2 = "SELECT userid,username,usertype FROM usertree WHERE `isdeleted` = 0 AND lvproxyid=0 AND usertype=1";
		$aUsers = $this->oDB->getAll( $sSql2 );

		// 组装返回数组 根据传入条件筛选
		foreach ($aUsers AS $aU){
			//过滤
			unset($bCheckA,$bCheckB,$bCheckC);
			if (isset($aWhere) && is_array($aWhere) ){
				if (  ($aWhere[0] >= 0) && ($aSeted[$aU['userid']]['payment_level'] == $aWhere[0]) ){
					$bCheckA = true;
				}else{
					$bCheckA = false;
				}
				
				if ( ($aWhere[1] >= 0) && ($aSeted[$aU['userid']]['can_payment'] == $aWhere[1]) ) {
					$bCheckB = true;
				}else{
					$bCheckB = false;
				}
				
				if (  ($aWhere[0] >= 0) && ($aWhere[1] >= 0) ){
					$bCheckC = $bCheckA && $bCheckB;
				}else{
					$bCheckC = $bCheckA || $bCheckB;
				}
				
			}else{
				$bCheckC = true;
			}
				
			if ($bCheckC === true){
				
				$aNewReturn[$aU['userid']]['userid'] = $aSeted[$aU['userid']]['userid2'] ? $aSeted[$aU['userid']]['userid2'] : $aU['userid'];
				$aNewReturn[$aU['userid']]['username'] = $aU['username'];
				$aNewReturn[$aU['userid']]['usertype'] = $aU['usertype'];
				$aNewReturn[$aU['userid']]['can_payment'] = $aSeted[$aU['userid']]['can_payment'] ? $aSeted[$aU['userid']]['can_payment'] : 0;
				$aNewReturn[$aU['userid']]['payment_level'] = $aSeted[$aU['userid']]['payment_level'] ? $aSeted[$aU['userid']]['payment_level'] : 0;
			}
		}
		
		return $aNewReturn;
	}
	
	
	/**
	 * 更新总代快速充值权限层级
	 *
	 * @param array $aUser			用户ID数组
	 * @param array $aPaymentLevel	做运算的 代理级别		
	 * @param array $aAllow			是否许可
	 * @param int	$iBankId		涉及银行ID
	 * @return bool
	 * 11/17/2010
	 */
	public function updateAuthDepositLv( $aUser, $aPaymentLevel, $aAllow, $iBankId  )
	{ 	
		// 银行ID 检查
		$oDeposit		= new model_deposit_depositlist(array(),'','array');
    	$aDepositList 	= $oDeposit->getDepositArray('all');
		$aBankIdArray 	= $aDepositList[0];
		
		$iBankId = intval($iBankId);
		if (  !is_numeric($iBankId) || $iBankId <= 0 
			|| array_search( $iBankId, $aBankIdArray) === FALSE )
		{
			return FALSE;
		}
		
		$sProxykey1 = 'can_deposit_'.$iBankId;
		$sProxykey2 = 'deposit_level_'.$iBankId;
		
		//检查数据完整性
		
		if( is_array($aUser) ){
			foreach($aUser as $iKey => $aValue )
			{
				if( !is_numeric($aPaymentLevel[$aValue])  )
				{
					unset($aUser[$iKey]);
				}
				
			}
		}
		if ( !empty($aUser) ){
			$this->oDB->doTransaction();
			foreach ($aUser as $user)
			{
				$sCSql = "SELECT userid FROM `topproxyset` WHERE (`proxykey`='".$sProxykey1."' OR `proxykey`='".$sProxykey2."') AND `userid`=".$user;
				$aChk = $this->oDB->getOne($sCSql);
				
				if ( count($aChk) > 0){
					$sSql = "UPDATE `topproxyset` SET `proxyvalue`='".$aPaymentLevel[$user]."' WHERE `proxykey`='".$sProxykey2."' AND `userid`=".$user;
					$result = $this->oDB->query($sSql);
					
					if ($result === FALSE)
					{
						$this->oDB->doRollback();
						return -1;
					}
					
					$iValue = isset($aAllow[$user])&&($aAllow[$user] == 1) ? 1 : 0;
					$sSql2 =  "UPDATE `topproxyset` SET `proxyvalue`='".$iValue."' WHERE `proxykey`='".$sProxykey1."' AND `userid`=".$user;
					$result = $this->oDB->query($sSql2);
				
					if ($result === FALSE)
					{
						$this->oDB->doRollback();
						return -1;
					}
				}else{
					$sSql = "INSERT INTO `topproxyset` (`userid`,`proxykey`, `proxyvalue`) VALUES ($user,'".$sProxykey2."',".$aPaymentLevel[$user].")";
					$result = $this->oDB->query($sSql);
					if ($result === FALSE)
					{
						$this->oDB->doRollback();
						return -1;//更新payment权限失败
					}
					unset($iValue);
					$iValue = ($aAllow[$user] == 1) ? 1 : 0;
					$sSql2 = "INSERT INTO `topproxyset` (`userid`,`proxykey`, `proxyvalue`) VALUES ($user,'".$sProxykey1."',".$iValue.")";
					$result = $this->oDB->query($sSql2);
				
					if ($result === FALSE)
					{ 
						$this->oDB->doRollback();
						return -1;
					}
				} //end 
				
			}

			$this->oDB->doCommit();
			return 1;//完成
		}
		else{
			return 0;//没有数据提交
		}
	}
	
}
?>