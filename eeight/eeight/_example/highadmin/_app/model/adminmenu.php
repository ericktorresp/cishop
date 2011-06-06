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
 * @since    2009-06-15
 */
class model_adminmenu extends basemodel 
{
	/**
	 * 增加管理员菜单
	 * @author mark
	 * @param string $sMenuName
	 * @param int $iPid
	 * @param string $sDescr
	 * @param string $sController
	 * @param string $sActioner
	 * @param int $iIsMenu
	 * @param int $iIsLink
	 * @return int
	 */
	public function adminMenuAdd($sMenuName, $iPid, $sDescr, $sController, $sActioner, $iIsMenu, $iIsLink, $iSort=0 )
	{
		if( empty($sMenuName) || empty($sController) )
		{
			return -1;
		}
		if( ($iPid >0) &&empty($sActioner) )
		{
			return -1;
		}
		$sAdminParentStr = '';
		if( $iPid > 0 )
		{
			// 检测adminMenu 是否存在
			$aAdminMenu = $this->adminMenu( $iPid );
			if( $aAdminMenu == -1 ) 
			{
				return -2;
			}
			$sAdminParentStr = $aAdminMenu["parentstr"].(empty($aAdminMenu["parentstr"])?"":",").$aAdminMenu["menuid"];
		}
		else
		{
			$iIsMenu = 1;
			$iIsLink = 0;
		}
		if(empty($sActioner)&&($iPid > 0))
		{
			return -3;
		}
		if( empty($sDescr) )
		{
			$sDescr = "";
		}
		$this->oDB->query("INSERT INTO `adminmenu` (`parentid`, `parentstr`, `title`, `description`, ".
			" `controller`, `actioner`, `ismenu`, `islink`, `sort`, `isdisabled` ) VALUES ".
			" ('".$iPid."','".$sAdminParentStr."','".$sMenuName."','".$sDescr."', ".
			" '".$sController."','".$sActioner."','".$iIsMenu."','".$iIsLink."','".$iSort."','0')");
		if($this->oDB->errno()>0)
		{
			return -4;
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
	public function adminMenu( $iMenuId = 0 )
	{
	    $iMenuId = intval($iMenuId);
		if( $iMenuId == 0 )
		{
			return -1;
		}
		$this->oDB->query("SELECT * FROM `adminmenu` WHERE `menuid`='" .$iMenuId. "'");
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
	 * 更改菜单状态
	 *
	 * @param int $iMenuId 菜单ID
	 * @param int $iStatus 菜单状态
	 * @author mark
	 */
	public function adminMenuEnable( $iMenuId , $iStatus )
	{
	    $iMenuId = intval($iMenuId);
	    $iStatus = intval($iStatus);
		$mAdminMenu = $this->adminMenu($iMenuId);
		if( $mAdminMenu == -1 )
		{
			return FALSE;
		}
		if( $mAdminMenu['isdisabled'] == $iStatus )
		{
			return TRUE;
		}
		if( $iStatus ==1 ) //禁用
		{
			$this->oDB->query("UPDATE `adminmenu` SET `isdisabled`='1' WHERE `menuid`='".$iMenuId."' ".
							 " OR FIND_IN_SET('".$iMenuId."',`parentstr`) ");//自身ID 以及所有下级
			if($this->oDB->errno()>0)
			{
				return FALSE;
			}
			return TRUE;
		}
		elseif( $iStatus ==0 )
		{
			if( $mAdminMenu['parentstr']!='' )
			{
				$this->oDB->query("select * FROM `adminmenu` WHERE `menuid` ". 
								" IN (".$mAdminMenu["parentstr"].") AND `isdisabled`='1'");//查询所有上级的状态
				if($this->oDB->errno()>0)
				{
					return FALSE;
				}
				if($this->oDB->numRows()>0)
				{
					return FALSE;
				}
			}
			$this->oDB->query("UPDATE `adminmenu` SET `isdisabled`='0' WHERE `menuid`='".$iMenuId."'");
			if($this->oDB->errno()>0)
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
	 * @return BOOL
	 * UPDATE:增加在删除菜单时候，对管理员以及管理组中拥有该菜单的权限进行剔除
	 */
	public function adminMenuDel( $iMenuId )
	{
	    $iMenuId = intval($iMenuId);
		$aMenuChild = $this->adminMenuChild($iMenuId,true);
		$iCount = count($aMenuChild);
		unset($aMenuChild);
		if( $iCount>0 )
		{			
			return FALSE;
		}
		else
		{ //对管理员特殊权限 进行菜单剔除遍历
			$this->oDB->doTransaction();
			$aAdmin = $this->oDB->getAll("SELECT `adminid`,`menustrs` FROM `adminuser` WHERE FIND_IN_SET(".$iMenuId.",`menustrs`)");
			foreach( $aAdmin as $admin )
			{
				$aAdminMenus = explode(",",$admin["menustrs"]);
				foreach( $aAdminMenus as $key =>$value )
				{
					if( $value == $iMenuId )
					{
						unset($aAdminMenus[$key]);
					}
				}
				$sAdminMenu = join(",", $aAdminMenus);
				unset($aAdminMenus);
				$this->oDB->query("UPDATE `adminuser` SET `menustrs`='".$sAdminMenu."' WHERE `adminid`='".$admin["adminid"]."'");
				if($this->oDB->errno()>0)
				{
					$this->oDB->doRollback();
					return false;
				}
			}
			//对管理员组进行菜单剔除遍历
			$aTeam = $this->oDB->getAll("SELECT `groupid`,`menustrs` FROM `admingroup` WHERE FIND_IN_SET(".$iMenuId.",`menustrs`)");
			foreach( $aTeam as $team )
			{
				$aAdminMenus = explode( ",", $team["menustrs"] );
				foreach( $aAdminMenus as $key =>$value )
				{
					if($value == $iMenuId)
					{
						unset($aAdminMenus[$key]);
					}
				}
				$sAdminMenu = join( ",", $aAdminMenus );
				unset($aAdminMenus);
				$this->oDB->query("UPDATE `admingroup` SET `menustrs`='".$sAdminMenu."' WHERE `groupid`='".$team["groupid"]."'");
				if($this->oDB->errno()>0)
				{
					$this->oDB->doRollback();
					return FALSE;
				}
			}
			$this->oDB->query("DELETE FROM `adminmenu` WHERE `menuid`='".$iMenuId."'");
			if($this->oDB->errno()>0)
			{
				$this->oDB->doRollback();
				return FALSE;				
			}
			$this->oDB->doCommit();
			return TRUE;
		}
	}



	/**
	 * 查询某个菜单的下级
	 * @author mark
	 * @param  int $iMenuId	菜单ID
	 * @param  bool $bAll	是否查询所有下级
	 * @param  string $sOrderBy 排序   [ 10/7/2010 delete for SortType change]
	 * @return array
	 */
	public function adminMenuChild($iMenuId = 0 , $bAll = FALSE )
	{
	    $iMenuId = intval($iMenuId);
		if( $bAll )
		{
			if( $iMenuId==0 )
			{
				return $this->oDB->getAll("SELECT * FROM `adminmenu` ORDER BY `sort` ASC, `menuid` ASC");
			}
			else
			{
				return $this->oDB->getAll("SELECT * FROM `adminmenu` WHERE FIND_IN_SET('".$iMenuId."',`parentstr`) ORDER BY `sort` ASC, `menuid` ASC");
			}
		}
		else
		{
			return $this->oDB->getAll("SELECT * FROM `adminmenu` WHERE `parentid`='".$iMenuId."' ORDER BY `sort` ASC, `menuid` ASC");
		}
	}



	/**
	 * 获取带层级关系的菜单数组
	 * @author Tom 090430
	 * @param  int $iSelectId
	 * @return string
	 */
	public function getAdminMenuOptions( $iSelectId = '' )
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
	 * @author mark
	 * @param int    $iMenuId
	 * @param string $sMenuName
	 * @param string $sDescr
	 * @param string $sController
	 * @param string $sActioner
	 * @param int    $iIsMenu 
	 * @param int    $iIsLink
	 * @param int    $iActionLog
	 */
	public function adminMenuUpdate($iMenuId, $sMenuName, $sDescr, $sController ,$sActioner,$iIsMenu ,$iIsLink ,$iActionLog ) 
	{
	    $iMenuId = intval($iMenuId);
		$mMenu   = $this->adminMenu($iMenuId);
		if($mMenu == -1)
		{
			return FALSE;
		}
		$this->oDB->query("UPDATE `adminmenu` SET `title`='" .daddslashes($sMenuName). "',"
			. "`description`='" .daddslashes($sDescr). "',`actioner`='" .daddslashes($sActioner). "',"
			. "`controller`='" .daddslashes($sController). "',`ismenu`='" . intval($iIsMenu). "',"
			. "`islink`='" .intval($iIsLink). "',`actionlog`='" .intval($iActionLog). "'" 
			. "WHERE `menuid`='" .$iMenuId. "'");
		return($this->oDB->errno()==0);	
	}



	/**
	 * 菜单排序
	 * @author mark 
	 * @param int $iMenuId
	 * @param array $aMenu
	 */
	public function adminMenuSort($iMenuId, $aMenu)
	{
	    $iMenuId = intval($iMenuId);
		$aMenuChild = $this->adminMenuChild( $iMenuId, FALSE );
		$this->oDB->doTransaction();
		foreach( $aMenuChild as $menu )
		{
			if( $aMenu[$menu["menuid"]] != $menu["sort"] )
			{
				if(empty($aMenu[$menu["menuid"]])) $aMenu[$menu["menuid"]] = 0;
				$this->oDB->query("UPDATE `adminmenu` SET `sort`='"
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
	 * 获取管理员用户菜单
	 * @author mark
	 * @param int $userid
	 * @return mixed
	 */
	public function getUserMenu( $iUserId )
	{
	    $iUserId = intval($iUserId);
		$aUser = $this->oDB->getOne("SELECT `menustrs`,`groupid`,`islocked` FROM `adminuser` WHERE `adminid`='".$iUserId."'");
		$aGroup = $this->oDB->getOne("SELECT `menustrs`,`isdisabled` FROM `admingroup` WHERE `groupid`='".$aUser["groupid"]."'");
		if( $aUser['islocked'] ) 
		{
			return FALSE;
		}
		if( trim($aUser["menustrs"]) == "" )
		{
			$aUserMenu = array();
		}
		else
		{
			$aUserMenu = explode( ",", $aUser["menustrs"] );
		}
		if( $aGroup['isdisabled'] )
		{
			return FALSE;
		}
		else
		{
			$aGroupMenu = explode( ",", $aGroup["menustrs"] );
		}
		foreach ($aGroupMenu as $value)
		{
			$aUserMenu[] = $value;
		}
		$aMenu = array_unique($aUserMenu);
		$sMenu = implode( ",", $aMenu );
		if( $sMenu == "" )
		{
			return NULL;
		}
		else 
		{
			return $this->oDB->getAll("SELECT * FROM `adminmenu` WHERE `isdisabled`='0' AND `ismenu`='1' AND  `menuid` IN(".$sMenu.") ORDER BY `parentid` ASC,`sort` ASC");
		}
	}



	/**
	 * 全部更改控制器行为器是否记录日志
	 * @author mark
	 * @param  int  $iStatus
	 * @return BOOL
	 */
	function setLogStatus( $iStatus )
	{
	    $iStatus = intval($iStatus);
		$this->oDB->query("UPDATE `adminmenu` SET `actionlog`='$iStatus' ");
		return ($this->oDB->errno()==0);
	}



	/**
	 * 全面启用菜单
	 * @author mark
	 * @return BOOL
	 */
	function enableAll()
	{
		$this->oDB->query("UPDATE `adminmenu` SET `isdisabled`='0'");
		return ($this->oDB->errno()==0);
	}
}
