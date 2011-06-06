<?php
/**
 * 总代管理员分组数据模型
 *
 * 功能：
 * 		总代管理员分组的一些常用操作的封装
 *          CRUD
 * 		-- proxyGroupInsert	           增加一个用户组
 *      -- getById              根据ID读取用户组信息
 * 		-- proxyGroupGetList    根据自定义条件取用户组列表
 *      -- getListByUser        根据总代ID获取属于他自己的所有分组
 * 		-- updateByUser			总代修改分组
 * 		-- deleteByUser		           总代删除自己的分组
 * 
 * @author     james
 * @version    1.1.0
 * @package	passport
 * @since      2009/05/3
 */

class model_proxygroup extends basemodel 
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
	}



	/**
	 * 增加一个用户组
	 * 
	 * @access 	public
	 * @author 	james	09/08/02
	 * @param	string	$sGroupName		//名称
	 * @param 	int		$iOwnerId		//所数总代ID
	 * @param 	string	$sMenustr		//权限菜单字符串
	 * @param 	int		$iIsSpecial		//是否为特别权限组
	 * @param 	int		$iIsDisabled	//是否关闭
	 * @return 	mixed	//成功返回insert id ，失败返回FALSE
	 */
	public function proxyGroupInsert( $sGroupName, $iOwnerId=0, $sMenustr='', $iIsSpecial=0, $iIsDisabled=0 )
	{
		//数据检查
		if( empty($sGroupName) )
		{
			return FALSE;
		}
		$aData = array(
						'groupname'	=> daddslashes($sGroupName),
						'ownerid'	=> intval($iOwnerId),
						'menustrs'	=> $sMenustr,
						'isspecial'	=> intval($iIsSpecial),
						'isdisabled'=> intval($iIsDisabled)	
					);
		return $this->oDB->insert( 'proxygroup', $aData );
	}

	
    /**
     * 判断一个用户组或者多个用户组是否为指定总代的 [权限检查]
     */
    public function isPermitProxy( $mGroupId, $iUserId )
    {
        $iUserId = intval($iUserId);
        if( empty($mGroupId) )
        {
            return FALSE;
        }
        if( is_array($mGroupId) )
        {
            $mResult = $this->proxyGroupGetList( array('ownerid'), "`groupid` IN(".implode(",",$mGroupId).")" );
            if( empty($mResult) || count($mResult) != count($mGroupId) )
            {
                return FALSE;
            }
            foreach( $mResult as $v )
            {
                if( $v['ownerid'] != 0 && $v['ownerid'] != $iUserId )
                {
                    return FALSE;
                }
            }
            return TRUE;
        }
        $mGroupId = intval($mGroupId);
//  temp_louis      $mResult = $this->getById( $mGroupId, array('ownerid') );
        $mResult = $this->getById( $mGroupId, array('p.ownerid') );
        if( empty($mResult) )
        {
            return FALSE;
        }
        if( $mResult['ownerid'] == 0 || $mResult['ownerid'] == $iUserId )
        {
            return TRUE;
        }
        return FALSE;
    }


	/**
	 * 根据ID读取用户组信息
	 * 
	 * @access 	public
	 * @author 	james	09/08/02
	 * @param 	int		$iGroupId	//用户组ID
	 * @param 	array	$aGroupInfo	//要读取的用户信息，默认读取所有字段信息
	 * @return 	mixed	//成功返回用户组信息一维数组，失败返回FALSE
	 */
	public function getById( $iGroupId, $aGroupInfo=array() )
	{
		if( empty($iGroupId) )
		{
			return FALSE;
		}
		//构造SQL
		if( is_array($aGroupInfo) && !empty($aGroupInfo) )
		{
			foreach( $aGroupInfo as &$v )
			{
//	temp_louis			$v = '`'.$v.'`';
				$v = explode('.', $v);
                $v = $v[0] . '.' . '`'.$v[1].'`';
			}
			$sSql = "SELECT " . implode(',', $aGroupInfo);
		}
		else
		{
//	temp_louis		$sSql = "SELECT * ";
			$sSql = "SELECT p.groupid,p.groupname,p.ownerid,p.issales,p.viewrights,p.isspecial,p.isdisabled,a.menustrs ";
		}
//	temp_louis	$sSql .= " FROM `proxygroup` WHERE `groupid`='". intval($iGroupId) ."'";
		$sSql .= " FROM `proxygroup` as p LEFT JOIN admin_proxy_menu as a ON p.groupid = a.groupid WHERE p.`groupid`='". intval($iGroupId) ."'";
		return $this->oDB->getOne( $sSql );
	}



    /**
     * 根据自定义条件取用户组列表
     * 
     * @access  public
     * @author  james   09/08/02
     * @param   array   $aGroupInfo //要取的字段数组
     * @param   string  $sWhereSql  //自定义条件 WHERE后面的条件
     * @return  mixed   //成功返回取得的记录集[二维数组]，失败返回FALSE
     */
    public function proxyGroupGetList( $aGroupInfo=array(), $sWhereSql='' )
    {
        if( is_array($aGroupInfo) && !empty($aGroupInfo) )
        {//自定义要取的字段信息
            foreach( $aGroupInfo as &$v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode(',', $aGroupInfo);
        }
        else
        {
            $sFields = "*";
        }
        if( !empty($sWhereSql) )
        {
            $sWhereSql = ' WHERE '.$sWhereSql;
        }
        else
        {
            $sWhereSql = '';
        }
        $sSql = "SELECT ". $sFields ." FROM `proxygroup` ". $sWhereSql;
        return $this->oDB->getAll( $sSql );
    }



	/**
	 * 根据总代ID获取属于他自己的所有分组
	 * 
	 * @access 	public
	 * @author 	james	09/08/02
	 * @param 	int		$iUserId	//总代ID
	 * @return 	mixed	//成功返回组别列表，失败返回FALSE
	 */
	public function & getListByUser( $iUserId )
	{
		$aData = array();
		if( empty($iUserId) || !is_numeric($iUserId) )
		{
			return $aData;
		}
		$sWhereSql = " `isdisabled`='0' AND (`ownerid`='".intval($iUserId)."' OR `ownerid`='0') ORDER BY `ownerid`";
		$aData = $this->proxyGroupGetList( array('groupid','groupname','ownerid','isspecial','issales'), $sWhereSql );
		if( empty($aData) )
		{
			return $aData;
		}
		//如果用户在默认分组上面做了 修改，则只显示用户修改的
		$temp_aDefault = array();
		$temp_aOwner   = array();
		foreach( $aData as $k=>$v )
		{
			if( $v['isspecial'] > 0 )
			{
				$temp_aOwner[$v['isspecial']] = $v['groupid'];
			}
			else 
			{
				$temp_aDefault[$k] = $v['groupid'];
			}
		}
		foreach( $temp_aDefault as $k=>$v )
		{
			if( array_key_exists($v,$temp_aOwner) )
			{
				unset( $aData[$k] );
			}
		}
		unset($temp_aDefault);
		unset($temp_aOwner);
		return $aData;
	}



	/**
	 * 总代修改分组
	 * 
	 * @access 	public
	 * @author 	james	09/08/02
	 * @param 	int		$iUserId	//总代ID
	 * @param 	int		$iGroupId	//组ID
	 * @param 	array	$aGroupInfo	//修改的信息
	 */
	public function updateByUser( $iUserId, $iGroupId, $aGroupInfo )
	{
		//数据安全检测
		if( empty($iUserId) || empty($iGroupId) || empty($aGroupInfo) )
		{
			return FALSE;
		}
		if( !is_numeric($iUserId) || !is_numeric($iGroupId) || !is_array($aGroupInfo) || $iUserId <= 0 || $iGroupId <= 0 )
		{
			return FALSE;
		}
		//数据安全修复
		if( !empty($aGroupInfo['menustrs']) )
		{
			$aGroupInfo['menustrs'] = daddslashes($aGroupInfo['menustrs']);
		}
		if( !empty($aGroupInfo['groupname']) )
		{
			$aGroupInfo['groupname'] = daddslashes($aGroupInfo['groupname']);
		}
		//首先判断是否为默认组
//	temp_louis	$temp_aData = $this->getById( $iGroupId, array('isspecial','ownerid','menustrs','groupname','isdisabled') );
		$temp_aData = $this->getById( $iGroupId, array('p.isspecial','p.ownerid','a.menustrs','p.groupname','p.isdisabled','p.issales') );
		if( empty($temp_aData) )
		{
			return FALSE;
		}
		//如果为默认用户组则复制一个并更改相应信息
		if( $temp_aData['isspecial'] == 0 )
		{
			$temp_aData['ownerid']   = $iUserId;
			$temp_aData['isspecial'] = $iGroupId;
			if( !empty($aGroupInfo['menustrs']) )
			{
				$temp_aData['menustrs'] = $aGroupInfo['menustrs'];
			}
			if( !empty($aGroupInfo['groupname']) )
			{
				$temp_aData['groupname'] = daddslashes($aGroupInfo['groupname']);
			}
			if( isset($aGroupInfo['isdisabled']) )
			{
				$temp_aData['isdisabled'] = (bool)$aGroupInfo['isdisabled'] ? 1 : 0;
			}
			/*if( isset($aGroupInfo['issales']) )
			{
				$temp_aData['issales'] = (bool)$aGroupInfo['issales'] ? 1 : 0;
			}*/
			if( isset($aGroupInfo['viewrights']) && is_numeric($aGroupInfo['viewrights']) )
			{
				$temp_aData['viewrights'] = intval($aGroupInfo['viewrights']);
			}
			
			 /** temp_louis **/
            $aAdminProxyMenu = array();
            $aAdminProxyMenu['menustrs'] = $aGroupInfo['menustrs'];
            /** temp_louis **/
			
			//启用事务
			$this->oDB->doTransaction();
			/*$iGid = $this->oDB->insert( 'proxygroup', $temp_aData );
			if( empty($iGid) )
			{//插入新组失败
				$this->oDB->doRollback();
				return FALSE;
			}*/
			
			/** temp_louis **/
            // 调用银行大厅api，完成组别修改
            $aTempData = array();
            $aTempData['userid'] = $iUserId;
            $aTempData['groupid'] = $iGroupId;
            $aTempData['groupname'] = $temp_aData['groupname'];
            $aTempData['issales'] = $temp_aData['issales'];
            $oChannelApi = new channelapi( 0, 'syncAdminProxyMenu', FALSE );
            $oChannelApi->setTimeOut(10);            // 设置读取超时时间
            $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
            $oChannelApi->sendRequest( $aTempData );    // 发送结果集
            $aResult = $oChannelApi->getDatas();
            if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
            {//调用API获取结果失败，可能资金帐户不存在
               $this->oDB->doRollback(); // 回滚事务
               return FALSE;
            }
            
            $aAdminProxyMenu['groupid'] = $aResult['data'];
            $aAdminProxyMenu['ownerid'] = $iUserId;
            $this->oDB->insert( 'admin_proxy_menu', $aAdminProxyMenu );
            if( $this->oDB->ar() == 0 )
            {//插入新组权限失败
                $this->oDB->doRollback();
                return FALSE;
            }
            
            // 调用其它平台api,向admin_proxy_menu表中写入一条记录
            $aOtherData = array(); // 其它平台信息
            $aOtherData['groupid'] = $iGroupId; // 继承组id,如果为空，表明新增一个组别
            $aOtherData['newgroupid'] = $aResult['data'];
            $aOtherData['userid'] = $iUserId;
            $oChannel = A::singleton("model_userchannel");
	        $aChannel = $oChannel->getUserChannelList( $iUserId );
	        if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) ){
	        	foreach( $aChannel[0] as $v ){
	        		if ($v['id'] == SYS_CHANNELID){
	        			continue;
	        		} else {
	        			$oChannelApi = new channelapi( $v['id'], 'syncAdminProxyMenu', FALSE );
		                $oChannelApi->setTimeOut(10);            // 设置读取超时时间
		                $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
		                $oChannelApi->sendRequest( $aOtherData );    // 发送结果集
		                $aResult = $oChannelApi->getDatas();
	        		}
	        	}
	        }
            /** temp_louis **/
			
			//更新该总代下属于该组的用户的组ID
			$sSql = " SELECT ut.`userid` 
					FROM `usertree` AS ut LEFT JOIN `usergroupset` AS ugs ON ut.`userid`=ugs.`userid`
					WHERE  ut.`usertype`='2' AND FIND_IN_SET('".$iUserId."',`parenttree`) 
					AND ugs.`groupid`='".$iGroupId."' AND ut.`isdeleted`='0' ";
			$aData = $this->oDB->getAll( $sSql );
			if( empty($aData) )
			{//没有找到数据，则不用更新
				$this->oDB->doCommit();
				return TRUE;
			}
			foreach( $aData as $v )
			{
				$temp_users[] = $v['userid'];
			}
			$sSql = "UPDATE `usergroupset` SET `groupid`='".$iGid."' 
					WHERE `userid` IN (".implode(',', $temp_users).") ";
			$this->oDB->query( $sSql );
			if( $this->oDB->ar() <1 )
			{// 更新失败
	  			$this->oDB->doRollback();
	  			return FALSE;
			}
			$this->oDB->doCommit();
			return TRUE;
		}
		elseif( $temp_aData['ownerid'] == $iUserId )
		{//不为默认组而且为自己的组则直接修改
			/* temp_louis return $this->oDB->update( 'proxygroup', $aGroupInfo, 
			                           " `groupid`='".$iGroupId."' AND `ownerid`='".$iUserId."'" );*/
			/** temp_louis **/
			$aAdminProxyMenu = array();
			//启用事务
            $this->oDB->doTransaction();
            if (!empty($aGroupInfo['menustrs'])){
            	$aAdminProxyMenu['menustrs'] = $aGroupInfo['menustrs'];
            	// 首先检查admin_proxy_menu表中是否有权限记录，如果没有则增加一条，如果有则直接修改
            	$sSql = "SELECT * FROM admin_proxy_menu WHERE groupid = {$iGroupId}";
            	$aResult = $this->oDB->getOne($sSql);
            	if (empty($aResult)){
            		$aAdminProxyMenu['groupid'] = $iGroupId;
            		$aAdminProxyMenu['ownerid'] = $iUserId;
            		$this->oDB->insert("admin_proxy_menu", $aAdminProxyMenu);
            		if ($this->oDB->errno() > 0){
		            	$this->oDB->doRollback(); // 事务回滚
		            	return false;
		            }
            	} else {
	        		$this->oDB->update('admin_proxy_menu', $aAdminProxyMenu, "groupid = {$iGroupId}");
		            if ($this->oDB->errno() > 0){
		            	$this->oDB->doRollback(); // 事务回滚
		            	return false;
		            }
            	}
            }
            
            unset($aGroupInfo['menustrs']);
            if (!empty($aGroupInfo)){
            	$this->oDB->update( 'proxygroup', $aGroupInfo, " `groupid`='".$iGroupId."' AND `ownerid`='".$iUserId."'" );
	            if ($this->oDB->errno() > 0){
	            	$this->oDB->doRollback(); // 事务回滚
	            	return false;
	            }
            }
            $this->oDB->doCommit();
			/** temp_louis **/
		}
		return TRUE;
	}



	/**
	 * 总代删除自己的分组
	 * 
	 * @access 	public
	 * @author 	james	09/08/02
	 * @param 	int		$iUserId	//总代ID
	 * @param 	string	$sGroupId	//组ID,多个ID用,隔开,如：1,2,3,4,5
	 */
	public function deleteByUser( $iUserId, $sGroupId )
	{
		//数据安全检测
		if( empty($iUserId) || empty($sGroupId) || !is_numeric($iUserId) || $iUserId <= 0 )
		{
			return FALSE;
		}
		$aGroupId = explode(',', $sGroupId);
		if( count($aGroupId) < 1 )
		{
			return FALSE;
		}
		elseif( count($aGroupId) == 1 )
		{//单个修改
			//检测该组下是否有用户，如果有则不能删除
			$sSql = "SELECT ut.`userid` FROM `usertree` AS ut 
			         LEFT JOIN `usergroupset` AS ugs ON ut.`userid`=ugs.`userid`
					 WHERE ut.`isdeleted`='0' AND ut.`usertype`='2' AND ut.`parentid`='".$iUserId."' 
					 AND ugs.`groupid`='".intval($sGroupId)."' ";
			$this->oDB->query($sSql);
			if( $this->oDB->ar() > 0 )
			{
				return -1;
			}
			unset( $sSql );
			//直接删除，如果失败可能组为默认组，不允许删除
			$sWhereSql = " `groupid`='".intval($sGroupId)."' AND `ownerid`='".$iUserId."' AND `isspecial`>'0'";
			return $this->oDB->delete( 'proxygroup', $sWhereSql );
		}
		else
		{//多个删除
			foreach( $aGroupId as &$v )
			{
				$v = intval($v);
			}
			//检测这些组下是否有用户，如果有则不能删除
			$sSql = " SELECT ut.`userid` FROM `usertree` AS ut 
			          LEFT JOIN `usergroupset` AS ugs ON ut.`userid`=ugs.`userid`
					  WHERE ut.`isdeleted`='0' AND ut.`usertype`='2' AND ut.`parentid`='".$iUserId."' 
					  AND ugs.`groupid` in (".implode(',', $aGroupId).") ";
			$this->oDB->query( $sSql );
			if( $this->oDB->ar() > 0 )
			{
				return -1;
			}
			unset( $sSql );
			$sWhereSql = " `groupid` in (".implode(',', $aGroupId).") AND `ownerid`='".$iUserId."' 
			                AND `isspecial`>'0' ";
			return $this->oDB->delete( 'proxygroup', $sWhereSql );
		}
	}
	
	
	/**
     * 同步总代管理员权限
     * 将原有proxygroup,拆分成为两个表，proxygroup 和 admin_proxy_menu,将原有总代管理员的权限同步到新表中
     * 
     * @version 	v1.0	2010-05-21
     * @author 		louis
     */
    function syncAdminProxyMenu(){
    	$sSql = "SELECT groupid,menustrs,ownerid FROM proxygroup";
    	$aResult = $this->oDB->getAll($sSql);
    	if (empty($aResult))	return 'null';
    	// 事务开始
    	$iSuccess = 0; // 成功条数
    	$this->oDB->doTransaction();
    	foreach ($aResult as $k => $v){
    		$sSql = "INSERT INTO admin_proxy_menu(groupid,menustrs,ownerid) VALUES({$v['groupid']},'{$v['menustrs']}',{$v['ownerid']})";
    		$this->oDB->query($sSql);
    		if ($this->oDB->ar() > 0 ){
    			$iSuccess++;
    		} else {
    			$this->oDB->doRollback(); // 事务回滚
    			return false;
    		}
    	}
    	if ($iSuccess == count($aResult)){
    		$this->oDB->doCommit(); // 事务提交
    		return true;
    	}
    }
    
    
    /**
     * 将proxygroup表中已经删除的记录，在admin_proxy_menu表中也删除
     *
     * @param 		int 	$userId			// 用户id
     * @version 	v1.0	2010-05-24
     * @author 		louis
     */
    function deleteMenu($userId){
    	if (!is_numeric($userId) || $userId <= 0) return false;
    	$this->oDB->delete("admin_proxy_menu", " groupid NOT IN (SELECT groupid FROM proxygroup WHERE ownerid= " .intval($userId)." OR ownerid=0) AND ownerid= " .intval($userId));
    	if ($this->oDB->errno() > 0){
    		return false;
    	} else {
    		return true;
    	}
    }
    
    
    
    
    
    /**
     * 根据用户ID，获取其组的团队下面的总代组ID，一代组ID，普代组ID，会员组ID，这里不获取总代管理员组的
     * 
     * @access  public
     * @since 	2010-08-26
     * @author  louis			copy from passport
     * @param   int     $iUserId    //用户ID
     * @return  array   //组ID数组 array('1','2','3','4'),[0]总代组ID，[1]一代组ID，[2]普代组ID，[3]会员组ID
     */
    public function getGroupID( $iUserId )
    {
        if( empty($iUserId) )
        {
            return FALSE;
        }
        //获取用户的组信息
        $sSql = " SELECT ugs.`groupid`,ug.`teamid` 
                FROM `usergroupset` AS ugs LEFT JOIN `usergroup` AS ug ON ugs.`groupid`=ug.`groupid` 
                WHERE ugs.`userid`='".intval($iUserId)."'";
        $aResult = $this->oDB->getOne( $sSql );
        if( empty($aResult) )
        {
            return FALSE;
        }
        if( $aResult['teamid'] == 0 )
        {//默认组
            return array('1','2','3','4');
        }
        else
        {//特殊组
            $sSql = " SELECT `groupid`,`isspecial` FROM `usergroup` 
                    WHERE `teamid`='".$aResult['teamid']."' AND `isdisabled`='0' ";
            $aResult = $this->oDB->getAll( $sSql );
            if( empty($aResult) )
            {//
                return array('1','2','3','4');
            }
            $aTempData = array();
            foreach( $aResult as $v )
            {
                $aTempData[$v['isspecial']] = $v['groupid'];
            }
            $aResult = array();
            $aResult[0] = isset($aTempData['1']) ? $aTempData['1'] : 1;
            $aResult[1] = isset($aTempData['2']) ? $aTempData['2'] : 2;
            $aResult[2] = isset($aTempData['3']) ? $aTempData['3'] : 3;
            $aResult[3] = isset($aTempData['4']) ? $aTempData['4'] : 4;
            unset($aTempData);
            return $aResult;
        }
    }
}