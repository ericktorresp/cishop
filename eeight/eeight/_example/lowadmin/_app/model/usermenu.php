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
 * @author	  saul
 * @version  1.0.0
 * @package  passportadmin
 * @since    2009-07-30
 */
class model_usermenu extends basemodel 
{
	/**
	 * 增加管理员菜单
	 * @author SAUL 090517
	 * @param string $sMenuName
	 * @param array    $aArr
	 * @return int
	 */
	public function userMenuAdd( $aArr=array() )
	{
		if( empty($aArr) || !is_array($aArr) )
		{//参数错误
			return -1;
		}
		if( empty($aArr['title']) || empty($aArr['controller']) )
		{//必要参数没有传递
			return -1;
		}
		$aArr['title']      = daddslashes($aArr['title']);
		$aArr['controller'] = strtolower(daddslashes($aArr['controller']));
		if( empty($aArr['menutype']) || !is_numeric($aArr['menutype']) )
		{//菜单类型，默认为0即游戏菜单
			$aArr['menutype'] = 0;
		}
		$aArr['menutype'] = intval($aArr['menutype']) > 0 ? 1 : 0;
		if( empty($aArr['lotteryid']) || !is_numeric($aArr['lotteryid']) )
        {//彩种ID
            $aArr['lotteryid'] = 0;
        }
        $aArr['lotteryid'] = intval($aArr['lotteryid']);
		if( empty($aArr['methodid']) || !is_numeric($aArr['methodid']) )
        {//玩法ID
            $aArr['methodid'] = 0;
        }
        $aArr['methodid']   = intval($aArr['methodid']);
        $aArr['description']= empty($aArr['description']) ? "" : daddslashes($aArr['description']);
        $aArr['actioner']   = empty($aArr['actioner']) ? "" : strtolower(daddslashes($aArr['actioner']));
        if( !isset($aArr['ismenu']) || !is_numeric($aArr['ismenu']) )
        {//是否为左侧菜单
        	$aArr['ismenu'] = 0;
        }
		$aArr['ismenu'] = intval($aArr['ismenu']) >0 ? 1 : 0;
		if( !isset($aArr['islink']) || !is_numeric($aArr['islink']) )
        {//是否为连接
            $aArr['islink'] = 0;
        }
        $aArr['islink'] = intval($aArr['islink']) >0 ? 1 : 0;
        if( !isset($aArr['islabel']) || !is_numeric($aArr['islabel']) )
        {//是否为标签
            $aArr['islabel'] = 0;
        }
        $aArr['islabel']    = intval($aArr['islabel']) >0 ? 1 : 0;
        $aArr['sort']       = isset($aArr['sort']) ? intval($aArr['sort']) : 0;
        $aArr['isdisabled'] = 0;    //默认启用菜单
        if( !isset($aArr['actionlog']) || !is_numeric($aArr['actionlog']) )
        {//是否开启日志记录
            $aArr['actionlog'] = 0;
        }
        $aArr['actionlog']    = intval($aArr['actionlog']) >0 ? 1 : 0; 
        if( empty($aArr['parentid']) || !is_numeric($aArr['parentid']) || intval($aArr['parentid']) < 0 )
        {//是否为标签
            $aArr['parentid'] = 0;
        }
        $aArr['parentid']     = intval($aArr['parentid']);
        $aArr['parentstr'] = "";
        if( $aArr['parentid'] )
        {
        	// 检测adminMenu 是否存在
            $aUserMenu = $this->userMenu( $aArr['parentid'] );
            if( empty($aUserMenu) ) 
            {
                return -2;
            }
            $aArr['menutype']  = $aUserMenu['menutype'];
            $aArr['parentstr'] = $aUserMenu["parentstr"].
                                (empty($aUserMenu["parentstr"]) ? "" : ",") . $aUserMenu["menuid"];
        }
		$this->oDB->insert( 'usermenu', $aArr );
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
	 * @author SAUL 090517
	 * @param int $iMenuId 菜单ID
	 * @return mixed  -1:菜单ID不存在
	 * 				Array:菜单
	 */
	public function & userMenu( $iMenuId = 0, $sField="" )
	{
		$aResult = array();
	    $iMenuId = intval($iMenuId);
		if( $iMenuId <= 0 )
		{
			return $aResult;
		}
		$sField = empty($sField) ? "*" : daddslashes($sField); 
		$aResult = $this->oDB->getOne("SELECT ".$sField." FROM `usermenu` WHERE `menuid`='" .$iMenuId. "'");
		return $aResult;
	}



	/**
	 * 更改菜单状态
	 *
	 * @param int $iMenuId 菜单ID
	 * @param int $iStatus 菜单状态
	 * @author SAUL 090517
	 */
	public function userMenuEnable( $iMenuId , $iStatus )
	{
	    $iMenuId    = intval($iMenuId);
	    $iStatus    = intval($iStatus);
		$mUserMenu  = $this->userMenu($iMenuId);
		if( empty($mUserMenu) )
		{
			return FALSE;
		}
		if( $mUserMenu['isdisabled'] == $iStatus )
		{
			return TRUE;
		}
		if( $iStatus ==1 ) //禁用
		{
			$this->oDB->query("UPDATE `usermenu` SET `isdisabled`='1' WHERE `menuid`='".$iMenuId."' ".
							 " OR FIND_IN_SET('".$iMenuId."',`parentstr`) ");//自身ID 以及所有下级
			if($this->oDB->errno()>0)
			{
				return FALSE;
			}
			return TRUE;
		}
		elseif( $iStatus ==0 )
		{
			if( $mUserMenu['parentstr']!='' )
			{
				$this->oDB->query("select * FROM `usermenu` WHERE `menuid` ". 
								" IN (".$mUserMenu["parentstr"].") AND `isdisabled`='1'");//查询所有上级的状态
				if($this->oDB->errno()>0)
				{
					return FALSE;
				}
				if($this->oDB->numRows()>0)
				{
					return FALSE;
				}
			}
			$this->oDB->query("UPDATE `usermenu` SET `isdisabled`='0' WHERE `menuid`='".$iMenuId."'");
			if($this->oDB->errno()>0)
			{
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * 删除某个菜单
	 * @author SAUL 090529
	 * @param int $iMenuId 菜单ID
	 * @return BOOL
	 * UPDATE:增加在删除菜单时候，对管理员以及管理组中拥有该菜单的权限进行剔除
	 */
	public function userMenuDel( $iMenuId )
	{
	    $iMenuId    = intval($iMenuId);
		$aMenuChild = $this->userMenuChild($iMenuId,true);
		$iCount = count($aMenuChild);
		unset($aMenuChild);
		if( $iCount>0 )
		{//有子菜单不允许删除
			return -1;
		}
		else
		{ 
			$this->oDB->query("DELETE FROM `usermenu` WHERE `menuid`='".$iMenuId."'");
			if( $this->oDB->errno() > 0 )
			{
				return -2;
			}
			return TRUE;
		}
	}



	/**
	 * 查询某个菜单的下级
	 * @author SAUL 090517
	 * @param  int $iMenuId	菜单ID
	 * @param  bool $bAll	是否查询所有下级
	 * @param  string  $sFields 查询内容
	 * @param  string  $sCondition //附加查询条件
	 * @return array
	 */
	public function & userMenuChild( $iMenuId , $bAll, $sFields="", $sCondition="" )
	{
	    $iMenuId = intval($iMenuId);
	    $sFields = empty($sFields) ? "*" : daddslashes($sFields);
		if( $bAll )
		{
			if( $iMenuId==0 )
			{ 
				return $this->oDB->getAll("SELECT ".$sFields." FROM `usermenu` WHERE 1 ".$sCondition." ORDER BY `sort` ASC, `menuid` ASC");
				
			}
			else
			{
				return $this->oDB->getAll("SELECT ".$sFields." FROM `usermenu` 
				    WHERE FIND_IN_SET('".$iMenuId."',`parentstr`) ".$sCondition." ORDER BY `sort` ASC, `menuid` ASC");
			}
		}
		else
		{
			return $this->oDB->getAll("SELECT ".$sFields." FROM `usermenu` 
			                             WHERE `parentid`='".$iMenuId."' ".$sCondition." ORDER BY `sort` ASC, `menuid` ASC");
		}
	}



	/**
	 * 获取带层级关系的菜单数组
	 * @author Tom 090430
	 * @param  int $iSelectId
	 * @return string
	 */
	public function getuserMenuOptions( $iSelectId = '' )
	{
	    $sSql = 'SELECT `menuid`,`parentstr`,`title` FROM `usermenu` '.
	            ' WHERE `isdisabled`=0 ORDER BY `parentid` ';
	    $aTmpArray =  $this->oDB->getAll( $sSql );
	    $aReturn   = array();
	    foreach( $aTmpArray AS $v )
	    {
	        $sTmpArrayKeyString = '';
	        $aKeysArray = explode( ',', $v['parentstr'] ); 
	        foreach( $aKeysArray as $pid )
	        {
	            if( is_numeric($pid) )
	            {
	                $sTmpArrayKeyString .= "[$pid]";
	            }
	        }
	        $sTmpArrayKeyString = '$aReturn' . $sTmpArrayKeyString .'[] = array( \'menuid\' => '
	                    .$v['menuid'] . ', \'title\' => "' . $v['title'] . '");';
	        eval( $sTmpArrayKeyString );
	    }
	    $aResult = array();
	    foreach( $aTmpArray AS $k => $v )
	    {
	        $aResult[$v['menuid']]['title'] = $v['title'];
	    }
	    unset($aTmpArray);
	    $sRetrunOptions = '';
	    foreach ( $aReturn AS $k => $v )
	    {
            if( !isset($v['menuid']) || !isset($v['title']) )
            { // 生成顶级
	            $sRetrunOptions .= "<OPTION ".($iSelectId==$k?'SELECTED':'')." VALUE='$k'>+---". $aResult[$k]['title']."</option>";
	            foreach( $v as $v1 )
	            {
	                if( !isset($v1['menuid']) || !isset($v1['title']) )
	                {
	                    foreach( $v1 as $v2 )
	                    {
	                        if( isset($v2['menuid']) && isset($v2['title']) )
	                        {
	                            $sRetrunOptions .= "<OPTION ".($iSelectId==$v2['menuid']?'SELECTED':'')." VALUE='". $v2['menuid'].
                                "'>| |---". $v2['title'] . "</option>";
	                        }
	                    }	                    
	                }
	                else 
	                { // 生成2级
        	            $sRetrunOptions .= "<OPTION ".($iSelectId==$v1['menuid']?'SELECTED':'')." VALUE='". $v1['menuid'].
                                "'>|----". $v1['title'] . "</option>";
	                }
	            }
	        }
	    }
	    unset($aResult,$aReturn);
	    return $sRetrunOptions;
	}



	/**
	 * 更新某个菜单的名称以及描述
	 * @author SAUL 090517
	 * @param int    $iMenuId
	 * @param string $sMenuName
	 * @param string $sDescr
	 * @param string $sController
	 * @param string $sActioner
	 * @param int    $iIsMenu 
	 * @param int    $iIsLink
	 * @param int    $iActionLog
	 */
	public function userMenuUpdate( $iMenuId, $aArr=array() ) 
	{
		if( empty($iMenuId) || !is_numeric($iMenuId) || $iMenuId <= 0 )
		{
			return FALSE;
		}
	    $iMenuId = intval($iMenuId);
	    if( empty($aArr) || !is_array($aArr) )
        {//参数错误
            return FALSE;
        }
        if( isset($aArr['title']) )
        {
        	$aArr['title'] = daddslashes($aArr['title']);
        }
        if( isset($aArr['controller']) )
        {
        	$aArr['controller'] = strtolower(daddslashes($aArr['controller']));
        }
	    if( isset($aArr['actioner']) )
        {
            $aArr['actioner'] = strtolower(daddslashes($aArr['actioner']));
        }
        if( isset($aArr['menutype']) )
        {//菜单类型，不允许修改
            unset($aArr['menutype']);
        }
        if( isset($aArr['lotteryid']) && is_numeric($aArr['lotteryid']) )
        {//彩种ID
            $aArr['lotteryid'] = intval($aArr['lotteryid']);
        }
        if( isset($aArr['methodid']) || is_numeric($aArr['methodid']) )
        {//玩法ID
            $aArr['methodid']   = intval($aArr['methodid']);
        }
        if( isset($aArr['description']) )
        {//菜单描述
        	$aArr['description'] = empty($aArr['description']) ? "" : daddslashes($aArr['description']);
        }
        if( isset($aArr['ismenu']) && is_numeric($aArr['ismenu']) )
        {//是否为左侧菜单
            $aArr['ismenu'] = intval($aArr['ismenu']) >0 ? 1 : 0;
        }
        if( isset($aArr['islink']) && is_numeric($aArr['islink']) )
        {//是否为连接
            $aArr['islink'] = intval($aArr['islink']) >0 ? 1 : 0;
        }
        if( isset($aArr['islabel']) && is_numeric($aArr['islabel']) )
        {//是否为标签
            $aArr['islabel'] = intval($aArr['islabel']) >0 ? 1 : 0;
        }
	    if( isset($aArr['sort']) && is_numeric($aArr['sort']) )
        {//排序
            $aArr['sort'] = intval($aArr['sort'])<0 ? 0 : intval($aArr['sort']);
        }
        if( isset($aArr['isdisabled']) )
        {//启用和禁用菜单不允许在这里修改
        	unset($aArr['isdisabled']);
        }
        if( isset($aArr['actionlog']) && is_numeric($aArr['actionlog']) )
        {//是否开启日志记录
            $aArr['actionlog'] = intval($aArr['actionlog'])>0 ? 1 : 0; 
        }
        if( isset($aArr['parentid']) )
        {//不允许修改层级
            unset($aArr['parentid']);
        }
	    if( isset($aArr['parentstr']) )
        {//不允许修改层级
            unset($aArr['parentstr']);
        }
        return $this->oDB->update( 'usermenu', $aArr, " `menuid`='" .$iMenuId. "' " );
	}



	/**
	 * 菜单排序
	 * @author SAUL 090517 
	 * @param int $iMenuId
	 * @param array $aMenu
	 */
	public function userMenuSort($iMenuId, $aMenu)
	{
	    $iMenuId    = intval($iMenuId);
		$aMenuChild = $this->userMenuChild( $iMenuId, FALSE );
		$this->oDB->doTransaction();
		foreach( $aMenuChild as $menu )
		{
			if( $aMenu[$menu["menuid"]] != $menu["sort"] )
			{
				if( empty($aMenu[$menu["menuid"]]) )
				{
					$aMenu[$menu["menuid"]] = 0;
				}
				$this->oDB->query("UPDATE `usermenu` SET `sort`='"
				    . $aMenu[$menu["menuid"]]."' WHERE `menuid`='".$menu["menuid"]."'");
				if($this->oDB->errno()>0)
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
	 * @author SAUL 090517
	 * @param  int  $iStatus
	 * @return BOOL
	 */
	function setLogStatus( $iStatus )
	{
		if( !isset($iStatus) || !is_numeric($iStatus) )
		{
			return FALSE;
		}
	    $iStatus = intval($iStatus)>0 ? 1 : 0;
		$this->oDB->query("UPDATE `usermenu` SET `actionlog`='$iStatus' ");
		return ($this->oDB->errno()==0);
	}



	/**
	 * 全面启用菜单
	 * @author SAUL 090517
	 * @return BOOL
	 */
	function enableAll()
	{
		$this->oDB->query("UPDATE `usermenu` SET `isdisabled`='0' WHERE `isdisabled`='1'");
		return ($this->oDB->errno()==0);
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
        	/* temp_louis $sSql = "SELECT ugs.`extendmenustr`, pg.`menustrs`,pg.`viewrights` 
                FROM `usergroupset` AS ugs LEFT JOIN `proxygroup` AS pg ON ugs.`groupid`=pg.`groupid` 
                WHERE ugs.`userid`='".$iUserId."' AND pg.`isdisabled`='0'";*/
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
        $sCondition  = " AND `menutype`='".$iMenuType."' ";
        $sCondition .= $sAndWhere." ORDER BY `sort`";
        //读取菜单数据
        $sSql = "SELECT `menuid`,`menutype`,`parentid`,`parentstr`,`title`,`description`,`lotteryid`,`methodid`,
                `controller`,`actioner`,`ismenu`,`islink`,`islabel`
                FROM `usermenu` 
                WHERE  `isdisabled`='0' AND `menuid` in (".$sMenus.") ".$sCondition;
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
}
