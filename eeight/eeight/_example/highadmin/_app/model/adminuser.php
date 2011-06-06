<?php
/**
 * 管理员用户模型
 * 
 * 功能: 
 * 
 * @author	  mark, Tom
 * @version  1.0.0
 * @package  highadmin
 */
//定义高频组别权限名宏
define("USERISCANUSEHIGHADMIN",'highadmin');

class model_adminuser extends basemodel
{
	/**
	 * 增加一个管理员
	 * @param string $sAdminName
	 * @param string $sAdminNick
	 * @param int    $iAdminGrpId
	 * @param string $sAdminPass
	 * @author mark 
	 */
	public function addAdmin( $sAdminName, $sAdminNick, $iAdminGrpId, $sAdminPass, $sAdminlang="utf8-zhcn" )
	{
		if( empty($sAdminName) || empty($sAdminNick)|| empty($sAdminPass) )
		{ // 参数不全
			return -1;
		}
		$oAdminTeam = new model_admingroup();
		$aTeam      = $oAdminTeam->admingroup( $iAdminGrpId );
		if( $aTeam ==-1 )
		{ // 非法的管理员组
			return -2;
		}
		if( $this->adminNameIsExists($sAdminName) )
		{ // 管理员名称已经存在
			return -3;
		}
		if( $this->adminNickIsExists($sAdminNick) )
		{ // 管理员昵称存在
			return -4;
		}
	    if( !$this->checkAdminName($sAdminName) )
        { //管理员帐号不合符规则
            return -5;
        }
        if( !$this->checkAdminPass($sAdminPass))
        { //管理员密码不符合规则
        	return -6;
        }
		$aAdminUser['adminname'] = daddslashes($sAdminName);
		$aAdminUser['adminnick'] = daddslashes($sAdminNick);
		$aAdminUser['adminpass'] = md5($sAdminPass);
		$aAdminUser['adminlang'] = daddslashes($sAdminlang);
		$aAdminUser['groupid']   = intval($iAdminGrpId);
		$aAdminUser['islocked']  = '0';
		$aAdminUser['menustrs']  = '';
		$this->oDB->insert( 'adminuser', $aAdminUser );
		return $this->oDB->insertId();
	}

    /**
     * 检测用户名是否合法
     * @access static
     * @author  james
     * @return 合法返回TRUE，不合法返回FALSE
     */
    static function checkAdminName( $sUserName )
    {
        if( preg_match( "/^[0-9a-zA-Z]{3,16}$/i", $sUserName ) )
        {
            return TRUE;
        }
        else 
        {
            return FALSE;
        }
    }
    

    /**
     * 检查管理员的密码
     *
     * @param string $sUserPass
     * @return bool
     */
    static function checkAdminPass( $sUserPass )
    {
        if( !preg_match("/^[0-9a-zA-Z]{6,16}$/i",$sUserPass) || preg_match("/^[0-9]+$/",$sUserPass)
                || preg_match("/^[a-zA-Z]+$/i",$sUserPass) || preg_match("/(.)\\1{2,}/i",$sUserPass)
            )
        {
            return FALSE;
        }
        else 
        {
            return TRUE;
        }
    }

	/**
	 * 检测管理员名称是否存在
	 * @param string $sNickName
	 * @return BOOL
	 * @author mark 
	 */
	public function adminNameIsExists( $sNickName )
	{
		$this->oDB->query("SELECT `adminid` FROM `adminuser` WHERE `adminname`='".daddslashes($sNickName)."'");
		return ( $this->oDB->numRows() > 0 );
	}



	/**
	 * 检测管理员昵称是否存在
	 * @param string $sNickName
	 * @return BOOL
	 * @author mark 
	 */
	public function adminNickIsExists($sNickName)
	{
		$this->oDB->query("SELECT `adminid` FROM `adminuser` WHERE `adminnick`='".daddslashes($sNickName)."'");
		return ( $this->oDB->numRows() > 0 );
	}



	/**
	 * 修改自己的密码
	 * @param int    $iAdminid
	 * @param string $sAdminPass 
	 * @param string $sNewAdminPass
	 * @author mark 
	 */
	public function changeSelfpass( $iAdminid, $sAdminPass, $sNewAdminPass )
	{
	    $iAdminid = intval($iAdminid);
	    if( $iAdminid <= 0 )
	    { // 数据安检
	        return FALSE;
	    }
		if( $sAdminPass != $sNewAdminPass )
		{
			$this->oDB->query("UPDATE `adminuser` SET `adminpass`='".md5($sNewAdminPass)."' 
						WHERE `adminid`='".$iAdminid."' AND `adminpass`='".md5($sAdminPass)."' LIMIT 1");
			return $this->oDB->ar();
		}
		else
		{
			return FALSE;
		}
	}



	/**
	 * 管理员登陆
	 * @param string $adminName  管理员登陆名
	 * @param string $adminPass  管理员登陆密码(未md5加密)
	 * @return [mixed]
	 * @author mark 
	 * 完成: 100%
	 */
	public function adminlogin( $sAdminName, $sAdminPass )
	{
	    $sAdminName = daddslashes($sAdminName);
		if( empty($sAdminName) || empty($sAdminPass) )
		{
			return -1; // 登陆数据错误
		}
		/*$aAdminuser = $this->oDB->getOne("SELECT * FROM `adminuser` WHERE `adminname`='$sAdminName' AND `adminPass`='"
		                                . md5($sAdminPass)."' " . ' LIMIT 1');*/
		$aAdminuser = $this->oDB->getOne("SELECT * FROM `adminuser` WHERE `adminname`='$sAdminName' LIMIT 1");
		if( $this->oDB->ar() == 0 )
		{
			return -2; // 管理员用户不存在
		}
		//新密码效验 5/31/2010
		if ( md5( md5(strtoupper($_SESSION["validateCode"])) . $aAdminuser['adminpass'] ) != $sAdminPass){
			return -2;
		}
		if( $aAdminuser["islocked"] != 0 )
		{
			return -3; // 管理员账户被锁定
		}
		$oAdminGrp = new model_admingroup();
		$aAdminGrps = $oAdminGrp->admingroup( $aAdminuser["groupid"] );
		if( $aAdminGrps == -1 )
		{
			return -4; // 用户组不存在 
		}
		if( $aAdminGrps["isdisabled"] ==1 )
		{
			return -5; // 用户组被禁用
		}
		unset($oAdminGrp,$aAdminGrps);
		//检测是否登陆过
		$sSql = "SELECT `userid` FROM `usersession` WHERE `userid`='".$aAdminuser['adminid']."' AND `isadmin`='1'";
		$this->oDB->query( $sSql );
		if( $this->oDB->ar() > 0 )
		{ //更新用户session 表里的session key值
			$this->oDB->update( 'usersession', array('sessionkey'=>genSessionKey(),'lasttime'=>date("Y-m-d H:i:s")), 
							"userid='". intval($aAdminuser['adminid'])."' AND `isadmin`='1'" );
		}
		else 
		{ //插入
			$this->oDB->insert( 'usersession', array('sessionkey'=>genSessionKey(),'lasttime'=>date("Y-m-d H:i:s"),
								'userid'=>$aAdminuser['adminid'],'isadmin'=>1) );
		}
		if( $this->oDB->affectedRows() < 1 )
		{ //更新用户session key值失败
			return -6;
		}
		return $aAdminuser;
	}



	/**
	 * 检测管理员是否有权限
	 * @param int     $iAdminId
	 * @param string  $sController
	 * @param string  $sActioner
	 * @return int    0=无权限  1=有权限
	 * @author mark 
	 */
	public function adminAccess( $iAdminId , $sController, $sActioner )
	{
	    $iAdminId    = intval($iAdminId);
	    $sController = daddslashes($sController);
	    $sActioner   = daddslashes($sActioner);
		$aAdminAllMenus = $this->getAdminUserMenus( $iAdminId );
		if( empty($aAdminAllMenus) )
		{ // 管理员不存在, 或管理员被锁定, 或所属组不存在, 或所属组被禁用
			return -1;
		}
		$aMenu = $this->oDB->getOne("SELECT `title`,`menuid`,`actionlog` FROM `adminmenu` WHERE `controller`='"
		                        .$sController."' AND `actioner`='".$sActioner."' AND `isdisabled`='0'" . ' LIMIT 1');
		if( $this->oDB->ar()==0 )
		{ //菜单不存在, 或未启用
			return -2;
		}

		$iMenuId = $aMenu["menuid"]; // 当前菜单ID
		$oAdminLog = new model_adminlog();
		if( !in_array( $iMenuId, $aAdminAllMenus ) )
		{
		    $oAdminLog->insert( '试图访问 ['.$aMenu["title"].'] 失败', '权限不足', $sController, $sActioner,1);
			return 0;
		}
		else
		{
		    $oAdminLog->insert( '试图访问 ['.$aMenu["title"].'] 成功', '访问功能', $sController, $sActioner,1);
			return 1;
		}
	}

    /**
     * 检查用户多个权限
     * @param <int> $iAdminId
     * @param <array> $aPrivilegeList
     * @return <mix> 
     * @author Rojer
     */
    public function checkAdminPrivilege( $iAdminId , $aPrivilegeList)
	{
        if (!is_array($aPrivilegeList))
        {
            return false;
        }
	    $iAdminId    = intval($iAdminId);
		$aAdminAllMenus = $this->getAdminUserMenus( $iAdminId );
		if( empty($aAdminAllMenus) )
		{ // 管理员不存在, 或管理员被锁定, 或所属组不存在, 或所属组被禁用
			return false;
		}
        $tmp = '';
        foreach ($aPrivilegeList as $v)
        {
            if (!isset($v['controller']) || !isset($v['actioner']))
            {
                return false;
            }
            $tmp[] = "(controller = '{$v['controller']}' AND actioner='{$v['actioner']}')";
        }

		if (!$aMenu = $this->oDB->getAll("SELECT `menuid`,`title`,`controller`,`actioner` FROM `adminmenu` WHERE ".implode(' OR ', $tmp)." AND `isdisabled`='0'"))
		{
			return false;
		}

        foreach ($aMenu as $k => $v)
        {
            if (!isset($aAdminAllMenus[$v['menuid']]))
            {
                unset($aMenu[$k]);
            }
        }

        return $aMenu;
	}

	/**
	 * 实例化一个admin
	 * @param int  $iAdminId
	 * @return -1 or Array
	 * @author mark 
	 */
	public function admin( $iAdminId )
	{
		$this->oDB->query("SELECT * FROM `adminuser` WHERE `adminid`='".intval($iAdminId)."' LIMIT 1");
		if( $this->oDB->ar()==0 )
		{
			return -1;
		}
		else
		{
			return $this->oDB->fetchArray();
		}
	}



	/**
	 * 获取管理员分页
	 *
	 * @param string $sFields
	 * @param string $sCondition
	 * @param integer $iPageRecords
	 * @param integer $iCurrPage
	 * @return array
	 */
	public function & getAdminList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
	    $sTableName = ' `adminuser` a left join `admingroup` b on a.groupid = b.groupid ';
	    $sFields    = ' a.adminid, a.adminname, a.adminnick, a.adminlang, a.islocked, b.groupname ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, " ORDER BY a.`adminname`" );
	}



	/**
	 * 获取销售管理员列表
	 * @return array
	 * @author mark 
	 */
	public function getSale()
	{
		return $this->oDB->getAll("SELECT * FROM `adminuser` WHERE `groupid` IN "
					."(SELECT `groupid` FROM `admingroup` WHERE `issales`='1')");
	}



	/**
	 * 删除一个管理员
	 * 完成: 100% 
	 * @param int $iUserId
	 * @return BOOL
	 */
	public function userdel( $iUserId )
	{
	    $iUserId = intval($iUserId);
	    if( $this->admin($iUserId) == -1 )
		{
			return -1; // 管理员ID不存在
		}
		$iFlag1 = $iFlag2 = 0;
		// 001, adminuser 表删除记录
		$iFlag1 = $this->oDB->query("DELETE FROM `adminuser` WHERE `adminid`='".$iUserId."' LIMIT 1");
		// 002, 删除管理员与总代对应关系
		if( SYS_CHANNELID == 0 )
		{
		    $iFlag2 = $this->oDB->query("DELETE FROM `adminproxy` WHERE `adminid`='".$iUserId."' ");
		}
		else 
		{
		    $iFlag2 = TRUE;
		}
		return ( $iFlag1 && $iFlag2 );
	}



	/**
	 * 批量执行更新用户状态
	 * @author Tom 090511
	 * @param Array $aUser    用户数组
	 * @param int   $iStatus  用户状态
	 * @return BOOL
	 */ 
	public function batchStatusSet( $aUser, $iStatus )
	{
		if( is_array($aUser) )
		{
			$sUserstr = implode( ",", $aUser );
			return $this->oDB->query("UPDATE `adminuser` SET `islocked`='".intval($iStatus)
			                    . "' WHERE `adminid` IN (".$sUserstr.")");
		}
		else
		{
			return FALSE;
		}
	}



	/**
	 *  更新管理员信息
	 *  完成:100%
	 *   - 允许密码为空, 即: 不修改管理员密码
	 * @param int    $iAdminId
	 * @param array  $aUser    array => groupid,adminname,adminpass,adminnick,adminlang
	 * @param array  $aMenu    HTML.checkbox.id[]
	 * @author Tom 090511
	 */
	public function adminUpdate( $iAdminId, $aUser, $aMenu=array() )
	{
	    // 01, 数据过滤
		if( empty($iAdminId) || ($iAdminId==0) )
		{
			return -1;
		}
		$aOldAdminArr = $this->admin( $iAdminId );
		if( $aOldAdminArr == -1 )
		{
			return -2;
		}
		if( $aUser["adminname"] == "" )
		{
			return -3;
		}
		// 02, 数据整理
		$aNewUserArr['adminname'] = daddslashes($aUser['adminname']);
		$aNewUserArr['adminnick'] = daddslashes($aUser['adminnick']);
		$aNewUserArr['adminlang'] = daddslashes($aUser['adminlang']);
		$aNewUserArr['menustrs']  = $aMenu; // 管理员提交的,全部菜单ID (逗号分隔)
		$aNewUserArr['islocked']  = intval($aUser['islocked']);
		$aNewUserArr['groupid']   = intval($aUser['groupid']); // 新组别 ID

		if( !empty($aUser['adminpass']) && strlen($aUser['adminpass']) > 0 )
		{ // 只有密码符合6位,并非空情况,才视为更新密码
		    $aNewUserArr['adminpass'] = md5( $aUser['adminpass'] );
		}

	    // 获取旧管理员分组ID, 并查询组别菜单ID
	    // 如果更改了组别ID, 则使用新组别strs 作为差异比较对象.
	    // 如果未更改组别ID, 则使用旧组别strs 作为差异比较对象.
	    $oAdminGrp = new model_admingroup();
	    $aAdminGrps = $oAdminGrp->getMenuStringByGrpId( 
	                                ($aOldAdminArr['groupid']!=$aNewUserArr['groupid']) 
	                                ? $aNewUserArr['groupid'] : $aOldAdminArr['groupid'] );
	    if( FALSE == $aAdminGrps )
	    {
	        return -100;
	    }
	    else
	    {
	        // 对组别中存储的 menustrs 进行过滤
	        $sAdminMenuStrDiff = explode( ',' , $aAdminGrps['menustrs'] );
		    foreach( $sAdminMenuStrDiff as $k => $v )
		    {
		        if( !is_numeric($v) || empty($v) || trim($v)=='' )
		        {
		            unset($sAdminMenuStrDiff[$k]);
		        }
		    }
	    }

	    // 对用户提交的 html.checkbox.id[] 进行过滤
	    // 用户的新 '特殊权限' = (用户HTML提交 CHECKBOX.ID) - [新|旧]组别菜单IDs
	    foreach( $aMenu as $k => $v )
	    {
	        if( !is_numeric($v) || empty($v) || trim($v)=='' )
	        {
	            unset($aMenu[$k]);
	        }
	    }
	    $aNewUserArr['menustrs'] = array_unique(array_diff( $aMenu, $sAdminMenuStrDiff ));
	    $aNewUserArr['menustrs'] = join( ',', $aNewUserArr['menustrs'] );
		return $this->oDB->update("adminuser", $aNewUserArr, " `adminid` = '".$iAdminId."' " );
	}
	
	
	/**
	 * 获取管理员所有的权限
	 * @return array 菜单数组
	 * @author Tom 090511
	 */
	public function & getAdminUserMenus( $iAdminId = '' )
	{
	    if( $iAdminId == '' )
	    {
	        $iAdminId = daddslashes($_SESSION['admin']);
	    }
	    $aReturn = array();
	    if( $iAdminId == 0 )
	    {
	        return $aReturn;
	    }
	    $aGroupMenus = $this->oDB->getOne("SELECT CONCAT(a.`menustrs`,',',b.`menustrs`) AS ALLMENUS ".
	            " FROM `adminuser` a LEFT JOIN `admingroup` b ".
	            " ON a.`groupid`=b.`groupid` WHERE a.`adminid`='$iAdminId' AND a.islocked=0 AND b.`isdisabled`='0'" . ' LIMIT 1');
	    if( !$this->oDB->ar() || strlen($aGroupMenus['ALLMENUS']) <= 1 ) // 计算逗号
	    {
	        return $aReturn;
	    }
	    $aAdminMenus = explode( ',', $aGroupMenus['ALLMENUS'] );
	    foreach( $aAdminMenus as $v )
	    {
	        if( !empty($v) && is_numeric($v) )
	        {
	            $aReturn[$v] = $v; // 过滤重复
	        }
	    }
	    return $aReturn;
	}
	
	
	/**
	 * 检测管理员是否有高频用户组别权限
	 *
	 * @param int  $iAdminId 管理员ID
	 * @return boolean
	 * @author mark
	 */
	public function checkUserIsCanUseHighadmin( $iAdminId = '' )
	{
	    if( $iAdminId == '' )
	    {
	        $iAdminId = daddslashes($_SESSION['admin']);
	    }
	    $aReturn = array();
	    if( $iAdminId == 0 )
	    {
	        return FALSE;
	    }
	    $aUserMenu = $this->getAdminUserMenus();
	    $sSql = " SELECT * FROM `adminmenu` WHERE `controller` = '" . USERISCANUSEHIGHADMIN ."'" . ' LIMIT 1';
	    $aResult = $this->oDB->getOne($sSql);
	    if(empty($aResult))
	    {
	        return TRUE;
	    }
	    else 
	    {
	        if( !in_array( $aResult['menuid'], $aUserMenu ) )
	        {
	            return FALSE;
	        }
	    }
	    return TRUE;
	}

}
?>