<?php
/**
 * 管理员菜单
 * 
 * 功能：
 *    1、 增加管理员菜单(adminMenuAdd)
 *    2、 通过ID实例化一个管理员菜单(adminMenu)
 *    3、 启用或者禁用一个管理员菜单(adminMenuEnable)
 *    4、 删除管理员菜单(adminMenuDel)
 *    5、 获取管理员菜单的下级(adminMenuChild)
 *    6、 更新管理员菜单名称和描述(adminMenuUpdate)
 *    9、 更新管理员菜单排序(adminMenuSort)
 *   10、 根据控制器和行为器获取管理员菜单ID(IdByControlAction)
 * 
 * @author	  mark
 * @version  1.0.0
 * @package  highadmin
 * @since    2010-01-20
 */
class model_usermenu extends basemodel 
{
	/**
	 * 增加管理员菜单
	 * @author mark 
	 * @param  array  $aMenuData
	 * @return int
	 */
	public function userMenuAdd( $aMenuData = array() )
	{
		if( empty($aMenuData) || !is_array($aMenuData) )
		{//参数错误
			return -1;
		}
		if( empty($aMenuData['title']) || empty($aMenuData['controller']) )
		{//必要参数没有传递
			return -1;
		}
		$aMenuData['title']      = daddslashes($aMenuData['title']);
		$aMenuData['controller'] = strtolower(daddslashes($aMenuData['controller']));
		if( empty($aMenuData['lotteryid']) || !is_numeric($aMenuData['lotteryid']) )
        {//彩种ID
            $aMenuData['lotteryid'] = 0;
        }
        $aMenuData['lotteryid'] = intval($aMenuData['lotteryid']);
		if( empty($aMenuData['methodid']) || !is_numeric($aMenuData['methodid']) )
        {//玩法ID
            $aMenuData['methodid'] = 0;
        }
        $aMenuData['methodid']    = intval($aMenuData['methodid']);
        $aMenuData['description'] = empty($aMenuData['description']) ? "" : daddslashes($aMenuData['description']);
        $aMenuData['actioner']    = empty($aMenuData['actioner']) ? "" : strtolower(daddslashes($aMenuData['actioner']));
        if( !isset($aMenuData['ismenu']) || !is_numeric($aMenuData['ismenu']) )
        {//是否为左侧菜单
        	$aMenuData['ismenu'] = 0;
        }
		$aMenuData['ismenu'] = intval($aMenuData['ismenu']) > 0 ? 1 : 0;
		if( !isset($aMenuData['islink']) || !is_numeric($aMenuData['islink']) )
        {//是否为连接
            $aMenuData['islink'] = 0;
        }
        $aMenuData['islink'] = intval($aMenuData['islink']) > 0 ? 1 : 0;
        if( !isset($aMenuData['islabel']) || !is_numeric($aMenuData['islabel']) )
        {//是否为标签
            $aMenuData['islabel'] = 0;
        }
        if( !isset($aMenuData['faceparameter']) )
        {//是否为标签
            $aMenuData['faceparameter'] = '';
        }
        $aMenuData['faceparameter'] = base64_encode(stripslashes_deep($aMenuData['faceparameter']));
        $aMenuData['islabel']    = intval($aMenuData['islabel']) > 0 ? 1 : 0;
        $aMenuData['sort']       = isset($aMenuData['sort']) ? intval($aMenuData['sort']) : 0;
        $aMenuData['isdisabled'] = 0;    //默认启用菜单
        if( !isset($aMenuData['actionlog']) || !is_numeric($aMenuData['actionlog']) )
        {//是否开启日志记录
            $aMenuData['actionlog'] = 0;
        }
        $aMenuData['actionlog']    = intval($aMenuData['actionlog']) > 0 ? 1 : 0; 
        if( empty($aMenuData['parentid']) || !is_numeric($aMenuData['parentid']) || intval($aMenuData['parentid']) < 0 )
        {//是否为标签
            $aMenuData['parentid'] = 0;
        }
        $aMenuData['parentid']     = intval($aMenuData['parentid']);
        $aMenuData['parentstr'] = "";
        if( $aMenuData['parentid'] )
        {
        	// 检测adminMenu 是否存在
            $aUserMenu = $this->userMenu( $aMenuData['parentid'] );
            if( empty($aUserMenu) ) 
            {
                return -2;
            }
            $aMenuData['parentstr'] = $aUserMenu["parentstr"].
                                (empty($aUserMenu["parentstr"]) ? "" : ",") . $aUserMenu["menuid"];
        }
		$this->oDB->insert( 'usermenu', $aMenuData );
		if( $this->oDB->errno() > 0 )
		{
			return -3;
		}
		else
		{
			return $this->oDB->insertid();
		}
	}



	/**
	 * 通过ID 实例化一个菜单
	 * @author mark 
	 * @param int $iMenuId 菜单ID
	 * @return mixed  -1:菜单ID不存在
	 * 				Array:菜单
	 */
	public function & userMenu( $iMenuId = 0, $sField = "" )
	{
		$aResult = array();
	    $iMenuId = intval($iMenuId);
		if( $iMenuId <= 0 )
		{
			return $aResult;
		}
		$sField = empty($sField) ? "*" : daddslashes($sField); 
		$aResult = $this->oDB->getOne("SELECT ".$sField." FROM `usermenu` WHERE `menuid`='" .$iMenuId. "'" . ' LIMIT 1');
		return $aResult;
	}
	
	
	/**
	 * 获取菜单列表
	 * @author james 2010/02/09
	 * @param string $sField
	 * @param string $sCondition
	 * @param string $sLeft
	 */
	public function & menuList( $sField='', $sCondition='', $sLeft='' )
	{
	    $sField = empty($sField) ? "*" : daddslashes($sField);
	    return $this->oDB->getAll( "SELECT ".$sField." FROM `usermenu` AS um ".$sLeft." WHERE 1 ".$sCondition );
	}



	/**
	 * 更改菜单状态
	 *
	 * @param int $iMenuId 菜单ID
	 * @param int $iStatus 菜单状态
	 * @author mark 
	 */
	public function userMenuEnable( $iMenuId = 0, $iStatus )
	{
	    $iMenuId    = intval($iMenuId);
	    if( $iMenuId <= 0 )
	    {
	        return FALSE;//没有指定菜单
	    }
	    $iStatus    = intval($iStatus);
	    if(!in_array($iStatus,array(0,1)))
	    {
	        return FALSE;//指定状态不正确
	    }
		$mUserMenu  = $this->userMenu($iMenuId);
		if( empty($mUserMenu) )
		{
			return FALSE;//指定菜单不存在
		}
		if( $mUserMenu['isdisabled'] == $iStatus )
		{
			return TRUE;//指定状态与菜单原来状态一样
		}
		if( $iStatus == 1 ) //禁用
		{
			$this->oDB->query("UPDATE `usermenu` SET `isdisabled`='1' WHERE `menuid`='".$iMenuId."' ".
							 " OR FIND_IN_SET('".$iMenuId."',`parentstr`) ");//自身ID 以及所有下级
			if($this->oDB->errno() > 0)
			{
				return FALSE;
			}
			return TRUE;
		}
		elseif( $iStatus == 0 )//启用
		{
			if( $mUserMenu['parentstr'] != '' )
			{
				$this->oDB->query("SELECT * FROM `usermenu` WHERE `menuid` ". 
								" IN (".$mUserMenu["parentstr"].") AND `isdisabled`='1'");//查询所有上级的状态
				if($this->oDB->errno() > 0)
				{
					return FALSE;
				}
				if($this->oDB->numRows() > 0)
				{
					return FALSE;//如果上级被禁用，下级不能启用
				}
			}
			$this->oDB->query("UPDATE `usermenu` SET `isdisabled`='0' WHERE `menuid`='".$iMenuId."'");
			if($this->oDB->errno() > 0)
			{
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * 删除某个菜单
	 * @author mark 
	 * @param int $iMenuId 菜单ID
	 * @return Mixed
	 * UPDATE:增加在删除菜单时候，对管理员以及管理组中拥有该菜单的权限进行剔除
	 */
	public function userMenuDel( $iMenuId )
	{
	    $iMenuId    = intval($iMenuId);
	    if( $iMenuId <= 0 )
	    {
	        return -3;//没有指定菜单
	    }
		$aMenuChild = $this->userMenuChild( $iMenuId, TRUE );
		$iCount = count($aMenuChild);
		unset($aMenuChild);
		if( $iCount > 0 )
		{
			return -1;//有子菜单不允许删除
		}
		else
		{ 
			$this->oDB->query("DELETE FROM `usermenu` WHERE `menuid`='".$iMenuId."'");
			if( $this->oDB->errno() > 0 )
			{
				return -2;//删除失败
			}
			return TRUE;
		}
	}



	/**
	 * 查询某个菜单的下级
	 * @author mark 
	 * @param  int $iMenuId	菜单ID
	 * @param  bool $bAll	是否查询所有下级
	 * @param  string  $sFields 查询内容
	 * @param  string  $sCondition //附加查询条件
	 * @return array
	 */
	public function & userMenuChild( $iMenuId , $bAll, $sFields = "", $sCondition = "" )
	{
	    $iMenuId = intval($iMenuId);
	    $sFields = empty($sFields) ? "*" : daddslashes($sFields);
		if( $bAll )
		{
			if( $iMenuId == 0 )
			{ 
				return $this->oDB->getAll("SELECT ".$sFields." FROM `usermenu` um LEFT JOIN `method` m ON um.`methodid`=m.`methodid` LEFT JOIN  `method_crowd` mc ON m.`crowdid`=mc.`crowdid` WHERE 1 ".$sCondition." ORDER BY um.`sort` ASC, um.`menuid` ASC");
				
			}
			else
			{
				return $this->oDB->getAll("SELECT ".$sFields." FROM `usermenu` um LEFT JOIN `method` m ON um.`methodid`=m.`methodid` LEFT JOIN  `method_crowd` mc ON m.`crowdid`=mc.`crowdid` WHERE FIND_IN_SET('".$iMenuId."',um.`parentstr`) ".$sCondition." ORDER BY um.`sort` ASC, um.`menuid` ASC");
			}
		}
		else
		{
			return $this->oDB->getAll("SELECT ".$sFields." FROM `usermenu` um LEFT JOIN `method` m ON um.`methodid`=m.`methodid` LEFT JOIN  `method_crowd` mc ON m.`crowdid`=mc.`crowdid` WHERE um.`parentid`='".$iMenuId."' ".$sCondition." ORDER BY um.`sort` ASC, um.`menuid` ASC");
		}
	}
	

	/**
	 * 更新某个菜单的名称以及描述
	 * @author mark 
	 * @param  array $aMenuData 菜单数据数组
	 * @return array
	 */
	public function userMenuUpdate( $iMenuId = 0, $aMenuData = array() ) 
	{
		if( empty($iMenuId) || !is_numeric($iMenuId) || $iMenuId <= 0 )
		{
			return FALSE;
		}
	    $iMenuId = intval($iMenuId);
	    if( empty($aMenuData) || !is_array($aMenuData) )
        {//参数错误
            return FALSE;
        }
        if( isset($aMenuData['title']) )
        {
        	$aMenuData['title'] = daddslashes($aMenuData['title']);
        }
        if( isset($aMenuData['controller']) )
        {
        	$aMenuData['controller'] = strtolower(daddslashes($aMenuData['controller']));
        }
	    if( isset($aMenuData['actioner']) )
        {
            $aMenuData['actioner'] = strtolower(daddslashes($aMenuData['actioner']));
        }
        if( isset($aMenuData['lotteryid']) && is_numeric($aMenuData['lotteryid']) )
        {//彩种ID
            $aMenuData['lotteryid'] = intval($aMenuData['lotteryid']);
        }
        if( isset($aMenuData['methodid']) || is_numeric($aMenuData['methodid']) )
        {//玩法ID
            $aMenuData['methodid']   = intval($aMenuData['methodid']);
        }
        if( isset($aMenuData['description']) )
        {//菜单描述
        	$aMenuData['description'] = empty($aMenuData['description']) ? "" : daddslashes($aMenuData['description']);
        }
        if( isset($aMenuData['ismenu']) && is_numeric($aMenuData['ismenu']) )
        {//是否为左侧菜单
            $aMenuData['ismenu'] = intval($aMenuData['ismenu']) > 0 ? 1 : 0;
        }
        if( isset($aMenuData['islink']) && is_numeric($aMenuData['islink']) )
        {//是否为连接
            $aMenuData['islink'] = intval($aMenuData['islink']) > 0 ? 1 : 0;
        }
        if( isset($aMenuData['islabel']) && is_numeric($aMenuData['islabel']) )
        {//是否为标签
            $aMenuData['islabel'] = intval($aMenuData['islabel']) > 0 ? 1 : 0;
        }
        if( isset($aMenuData['faceparameter']) && $aMenuData['faceparameter'] != '')
        {//前台界面生成参数
            if($aMenuData['lotteryid'] != 0)
            {
                $aMenuData['faceparameter'] = $aMenuData['faceparameter'] != '' ? 
                                              base64_encode(stripslashes_deep($aMenuData['faceparameter'])) : '';
            }
            else 
            {
                $aMenuData['faceparameter'] = '';
            }
        }  
	    if( isset($aMenuData['sort']) && is_numeric($aMenuData['sort']) )
        {//排序
            $aMenuData['sort'] = intval($aMenuData['sort']) < 0 ? 0 : intval($aMenuData['sort']);
        }
        if( isset($aMenuData['isdisabled']) )
        {//启用和禁用菜单不允许在这里修改
        	unset($aMenuData['isdisabled']);
        }
        if( isset($aMenuData['actionlog']) && is_numeric($aMenuData['actionlog']) )
        {//是否开启日志记录
            $aMenuData['actionlog'] = intval($aMenuData['actionlog']) > 0 ? 1 : 0; 
        }
        if( isset($aMenuData['parentid']) )
        {//不允许修改层级
            unset($aMenuData['parentid']);
        }
	    if( isset($aMenuData['parentstr']) )
        {//不允许修改层级
            unset($aMenuData['parentstr']);
        }
        return $this->oDB->update( 'usermenu', $aMenuData, " `menuid`='" .$iMenuId. "' " );
	}



	/**
	 * 菜单排序
	 * @author mark  
	 * @param int $iMenuId
	 * @param array $aSort 菜单排序数组
	 * @param array $aMenu
	 */
	public function userMenuSort($iMenuId, $aSort)
	{
	    $iMenuId    = intval($iMenuId);
		$aMenuChild = $this->userMenuChild( $iMenuId, FALSE );
		$this->oDB->doTransaction();
		foreach( $aMenuChild as $aMenu )
		{
			if( $aSort[$aMenu["menuid"]] != $aMenu["sort"] )
			{
				if( empty($aSort[$aMenu["menuid"]]) )
				{
					$aSort[$aMenu["menuid"]] = 0;
				}
				$sSql = "UPDATE `usermenu` SET `sort`='"
				         . $aSort[$aMenu["menuid"]]."' WHERE `menuid`='".$aMenu["menuid"]."'";
				$this->oDB->query($sSql);
				if( $this->oDB->errno() > 0 )
				{
					$this->oDB->doRollback();
					return FALSE;
				}
			}
		}
		$this->oDB->doCommit();
		return TRUE;
	}



	/**
	 * 全部更改控制器行为器是否记录日志
	 * @author mark 
	 * @param  int  $iStatus
	 * @return BOOL
	 */
	function setLogStatus( $iStatus )
	{
		if( !isset($iStatus) || !is_numeric($iStatus) || !in_array($iStatus,array(0,1)) )
		{
			return FALSE;
		}
	    $iStatus = intval($iStatus) > 0 ? 1 : 0;
		$this->oDB->query("UPDATE `usermenu` SET `actionlog`='$iStatus' ");
		$bResult = $this->oDB->errno() == 0 ? TRUE : FALSE;
		return $bResult;
	}



	/**
	 * 启用全部菜单
	 * @author mark 
	 * @return BOOL
	 */
	function enableAll()
	{
		$this->oDB->query("UPDATE `usermenu` SET `isdisabled`='0' WHERE `isdisabled`='1'");
		$bResult = $this->oDB->errno() == 0 ? TRUE : FALSE;
		return $bResult;
	}
	/**
	 * 获取用户权限菜单
	 *
	 * @author james 090807
	 * @access public
	 * @param  int      $iUserId
	 * @param  int      $iUserType
	 * @param  int      $iMenuType
	 * @param  string   $sAndWhere
	 * @return array
	 */
	public function & getUserMenus( $iUserId, $iUserType=0, $iMenuType=0, $sAndWhere=''  )
	{
	    $aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId < 0 )
        {
            return $aResult;
        }
        $iUserId = intval($iUserId);
        if( empty($iUserType) || !is_numeric($iUserType) )
        {
            $iUserType = 0;
        }
	    if( empty($iMenuType) || !is_numeric($iMenuType) )
        {
            $iMenuType = 0;
        }
        $iMenuType = intval($iMenuType) > 0 ? 1 : 0;
        if( $iUserType == 2 )
        {//总代管理员[特殊表]
        	$sSql = "SELECT uc.`extendmenustr`, apm.`menustrs`,pg.`viewrights` 
                FROM `userchannel` AS uc LEFT JOIN `proxygroup` AS pg ON uc.`groupid`=pg.`groupid` 
                LEFT JOIN `admin_proxy_menu` AS apm ON pg.`groupid` = apm.`groupid`
                WHERE uc.`userid`='".$iUserId."' AND pg.`isdisabled`='0'";
        }
        else 
        {
        	$sSql = "SELECT ugs.`extendmenustr`, ug.`menustrs` 
                FROM `usergroupset` AS ugs LEFT JOIN `usergroup` AS ug ON ugs.`groupid`=ug.`groupid` 
                WHERE ugs.`userid`='".$iUserId."' AND ug.`isdisabled`='0'";
        }
	    $aData = $this->oDB->getDataCached( $sSql );
        if( !isset($aData[0]) || empty($aData[0]) )
        {//没找到数据，返回失败
            return $aResult;
        }
        //不为总代管理员则可全部查看
        $aResult['viewrights'] = isset($aData[0]['viewrights']) ? intval($aData[0]['viewrights']) : 3;
        $aResult['menus']      = array();
        $sMenus                = $aData[0]['menustrs'];
        if( !empty($aData[0]['extendmenustr']) )
        {//存在特殊权限
            $sMenus .= (empty($sMenus) ? '' : ',').$aData[0]['extendmenustr'];
        }
        if( empty($sMenus) )
        {
            return $aResult;
        }
		$sCondition  = '';
        $sCondition .= $sAndWhere;
        //读取菜单数据
        $sSql = "SELECT `menuid`,`parentid`,`parentstr`,`title`,`description`,`lotteryid`,`methodid`,
                `controller`,`actioner`,`ismenu`,`islink`,`islabel` 
                FROM `usermenu` 
                WHERE  `isdisabled`='0' AND `menuid` in (".$sMenus.") ".$sCondition." ORDER BY `sort` ASC, `menuid` ASC";
        $aData = $this->oDB->getDataCached( $sSql );
        if( empty($aData) )
        {//没找到数据，返回失败
        	$aResult['menus'] = array();
            return $aResult;
        }
        foreach( $aData as $v )
        {
        	$aResult['menus'][$v['parentid']][$v['menuid']] = $v;
        }
        return $aResult;
	}

	public function getCrowd( $iLotteryId )
	{
		$sSql = 'SELECT crowdname, crowdid FROM `method_crowd` WHERE lotteryid = ' . $iLotteryId;
	    $aData = $this->oDB->getDataCached( $sSql );
        if( empty($aData) )
        {//没找到数据，返回失败
            return array();
        }
        foreach( $aData as $v )
        {
        	$aResult[$v['crowdid']] = $v['crowdname'];
        }
        return $aResult;
	}
}
