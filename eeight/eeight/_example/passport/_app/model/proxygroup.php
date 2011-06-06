<?php
/**
 * 总代管理员分组数据模型
 *
 * 功能：
 *      总代管理员分组的一些常用操作的封装
 *          CRUD
 *      -- insert               增加一个用户组
 *      -- getById              根据ID读取用户组信息
 *      -- getList              根据自定义条件取用户组列表
 *      -- getListByUser        根据总代ID获取属于他自己的所有分组
 *      -- updateByUser         总代修改分组
 *      -- deleteByUser         总代删除自己的分组
 * 
 * @author     james
 * @version    1.1.0
 * @package passport
 */

class model_proxygroup extends basemodel 
{
    /**
     * 构造函数
     * 
     * @access  public
     * @return  void
     */
    function __construct( $aDBO = array() )
    {
        parent::__construct( $aDBO );
    }



    /**
     * 增加一个用户组
     * 
     * @access  public
     * @author  james
     * @param   string  $sGroupName     //名称
     * @param   int     $iOwnerId       //所数总代ID
     * @param   string  $sMenustr       //权限菜单字符串
     * @param   int     $iIsSpecial     //是否为特别权限组
     * @param   int     $iIsDisabled    //是否关闭
     * @param   boolean $bIsSync    	//是否同步其它平台
     * @return  mixed   //成功返回insert id ，失败返回FALSE
     */
    public function insert( $sGroupName, $iOwnerId = 0, $sMenustr = '', $iIsSpecial = 0, $iIsDisabled = 0, $bIsSync = false )
    {
        //数据检查
        if( empty($sGroupName) )
        {
            return FALSE;
        }
        $aData = array(
            'groupname'	 => daddslashes($sGroupName),
            'ownerid'	 => intval($iOwnerId),
            'menustrs'	 => $sMenustr,
            'isspecial'	 => intval($iIsSpecial),
            'isdisabled' => intval($iIsDisabled)	
        );
//   temp_louis     return $this->oDB->insert( 'proxygroup', $aData );
        if ($bIsSync === false){
        	return $this->oDB->insert( 'proxygroup', $aData );
        } else {
        	$this->oDB->doTransaction(); // 事务开始
        	$mResult = $this->oDB->insert( 'proxygroup', $aData );
        	if ($mResult === false){
        		$this->oDB->doRollback(); // 事务回滚
        		return false;
        	}
        	// 向admin_proxy_admin表中写入一条menustrs为空的记录
        	if (!is_numeric($mResult) || $mResult <= 0 || $iOwnerId <= 0 || !is_numeric($iOwnerId)){
        		$this->oDB->doRollback(); // 事务回滚
        		return false;
        	}
        	$aData1['groupid'] = $mResult;
        	$aData1['ownerid'] = intval($iOwnerId);
        	if ($this->oDB->insert( 'admin_proxy_menu', $aData1 ) === false){
        		$this->oDB->doRollback(); // 事务回滚
        		return false;
        	}
        	$this->oDB->doCommit();
        	$aOtherData = array(); // 其它平台信息
            $aOtherData['newgroupid'] = $mResult;
            $aOtherData['userid'] = intval($iOwnerId);
        	$oChannel = A::singleton("model_userchannel");
	        $aChannel = $oChannel->getUserChannelList( intval($iOwnerId) );
	        if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) )
	        {//如果有其他频道
	            foreach( $aChannel[0] as $v ){
	            	$oChannelApi = new channelapi( $v['id'], 'syncAdminProxyMenu', FALSE );
	                $oChannelApi->setTimeOut(10);            // 设置读取超时时间
	                $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
	                $oChannelApi->sendRequest( $aOtherData );    // 发送结果集
	                $aResult = $oChannelApi->getDatas();
	            }
	        }
	        return $mResult;
        }
    }



    /**
     * 根据ID读取用户组信息
     * 
     * @access  public
     * @author  james
     * @param   int     $iGroupId   //用户组ID
     * @param   array   $aGroupInfo //要读取的用户信息，默认读取所有字段信息
     * @return  mixed   //成功返回用户组信息一维数组，失败返回FALSE
     */
    public function getById( $iGroupId, $aGroupInfo = array() )
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
// temp_louis           	$v = '`'.$v[1].'`';
            	$v = explode('.', $v);
                $v = $v[0] . '.' . '`'.$v[1].'`';
            }
            $sSql = "SELECT " . implode(',', $aGroupInfo);
        }
        else
        {
//  temp_louis          $sSql = "SELECT * ";
            $sSql = "SELECT p.groupid,p.groupname,p.ownerid,p.issales,p.viewrights,p.isspecial,p.isdisabled,a.menustrs ";
        }
//  temp_louis      $sSql .= " FROM `proxygroup` WHERE p.`groupid`='". intval($iGroupId) ."'";
        $sSql .= " FROM `proxygroup` as p,admin_proxy_menu as a WHERE p.`groupid`='". intval($iGroupId) ."' AND p.groupid = a.groupid";
        return $this->oDB->getOne( $sSql );
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
    		$mResult = $this->getList( array('ownerid'), "`groupid` IN(".implode(",",$mGroupId).")" );
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
//  temp_louis  	$mResult = $this->getById( $mGroupId, array('ownerid') );
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
     * 根据自定义条件取用户组列表
     * 
     * @access  public
     * @author  james
     * @param   array   $aGroupInfo //要取的字段数组
     * @param   string  $sWhereSql  //自定义条件 WHERE后面的条件
     * @return  mixed   //成功返回取得的记录集[二维数组]，失败返回FALSE
     */
    public function getList( $aGroupInfo = array(), $sWhereSql = '' )
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
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代ID
     * @return  mixed   //成功返回组别列表，失败返回FALSE
     */
    public function getListByUser( $iUserId )
    {
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $sWhereSql = " `isdisabled`='0' AND (`ownerid`='".intval($iUserId)."' OR `ownerid`='0') ORDER BY `ownerid`";
        $aData = $this->getList( array('groupid', 'groupname', 'ownerid', 'isspecial', 'issales'), $sWhereSql  );
        if( empty($aData) )
        {
            return FALSE;
        }
        //如果用户在默认分组上面做了 修改，则只显示用户修改的
        $temp_aDefault = array();
        $temp_aOwner   = array();
        foreach( $aData as $k => $v )
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
        foreach( $temp_aDefault as $k => $v )
        {
            if( array_key_exists($v, $temp_aOwner) )
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
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代ID
     * @param   int     $iGroupId   //组ID
     * @param   array   $aGroupInfo //修改的信息
     */
    public function updateByUser( $iUserId, $iGroupId, $aGroupInfo )
    {
        //数据安全检测
        if( empty($iUserId) || empty($iGroupId) || empty($aGroupInfo) )
        {
            return FALSE;
        }
        if( !is_numeric($iUserId) || !is_numeric($iUserId) || !is_array($aGroupInfo) )
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
// temp_louis       $temp_aData = $this->getById( $iGroupId, array('isspecial','ownerid','menustrs','groupname','isdisabled') );
        $temp_aData = $this->getById( $iGroupId, array('p.isspecial','p.ownerid','a.menustrs','p.groupname','p.isdisabled') );
        if( empty($temp_aData) )
        {
            return FALSE;
        }
        //如果为默认用户组则复制一个并更改相应信息
        if( $temp_aData['isspecial'] == 0 )
        {
            $temp_aData['ownerid']   = $iUserId;
            $temp_aData['isspecial'] = $iGroupId;
            /* temp_lousi if( !empty($aGroupInfo['menustrs']) )
            {
                $temp_aData['menustrs'] = $aGroupInfo['menustrs'];
            }*/
            if( !empty($aGroupInfo['groupname']) )
            {
                $temp_aData['groupname'] = daddslashes($aGroupInfo['groupname']);
            }
            if( isset($aGroupInfo['isdisabled']) )
            {
                $temp_aData['isdisabled'] = (bool)$aGroupInfo['isdisabled'] ? 1 : 0;
            }
            if( isset($aGroupInfo['issales']) )
            {
                $temp_aData['issales'] = (bool)$aGroupInfo['issales'] ? 1 : 0;
            }
            if( isset($aGroupInfo['viewrights']) && is_numeric($aGroupInfo['viewrights']) )
            {
                $temp_aData['viewrights'] = intval($aGroupInfo['viewrights']);
            }
            
            /** temp_louis **/
            $aAdminProxyMenu = array();
            if (!empty($aGroupInfo['menustrs'])){
            	$aAdminProxyMenu['menustrs'] = $aGroupInfo['menustrs'];
            } else {
            	$aAdminProxyMenu['menustrs'] = $temp_aData['menustrs'];
            }
            unset($temp_aData['menustrs']);
            /** temp_louis **/
            
            //启用事务
            $this->oDB->doTransaction();
            $iGid = $this->oDB->insert( 'proxygroup', $temp_aData );
            if( empty($iGid) )
            {//插入新组失败
                $this->oDB->doRollback();
                return FALSE;
            }
            
            /** temp_louis **/
            $aAdminProxyMenu['groupid'] = $iGid;
            $aAdminProxyMenu['ownerid'] = $iUserId;
            if (!is_numeric($iGid) || $iGid <= 0 || !is_numeric($iUserId) || $iUserId <= 0){
            	$this->oDB->doRollback();
                return FALSE;
            }
            $this->oDB->insert( 'admin_proxy_menu', $aAdminProxyMenu );
            if( $this->oDB->ar() == 0 )
            {//插入新组权限失败
                $this->oDB->doRollback();
                return FALSE;
            }
            /** temp_louis **/
            
            //更新该总代下属于该组的用户的组ID
            $sSql = " SELECT u.`userid` 
                    FROM `usertree` AS u LEFT JOIN `userchannel` AS uc ON u.`userid`=uc.`userid`
                    WHERE  u.`usertype`='2' AND FIND_IN_SET('".$iUserId."',`parenttree`) 
                    AND uc.`groupid`='".$iGroupId."' AND u.`isdeleted`='0' ";
            $aData = $this->oDB->getAll( $sSql );
            /*if( empty($aData) )
            {//没有找到数据，则不用更新
                $this->oDB->doCommit();
                return TRUE;
            }*/
            if (!empty($aData)){
            	foreach( $aData as $v )
	            {
	                $temp_users[] = $v['userid'];
	            }
	            $sSql = "UPDATE `userchannel` SET `groupid`='".$iGid."' 
	                    WHERE `userid` IN (".implode(',', $temp_users).")";
	            $this->oDB->query( $sSql );
	            if( $this->oDB->ar() < 1 )
	            {// 更新失败
	                $this->oDB->doRollback();
	                return FALSE;
	            }
            }
            $this->oDB->doCommit();
            
            /** temp_louis **/
            // 调用其它平台api,向admin_proxy_menu表中写入一条记录
            $aOtherData = array(); // 其它平台信息
            $aOtherData['groupid'] = $iGroupId; // 继承组id,如果为空，表明新增一个组别
            $aOtherData['newgroupid'] = $iGid;
            $aOtherData['userid'] = $iUserId;
            $oChannel = A::singleton("model_userchannel");
	        $aChannel = $oChannel->getUserChannelList( $iUserId );
	        if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) ){
	        	foreach( $aChannel[0] as $v ){
        			$oChannelApi = new channelapi( $v['id'], 'syncAdminProxyMenu', FALSE );
	                $oChannelApi->setTimeOut(10);            // 设置读取超时时间
	                $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
	                $oChannelApi->sendRequest( $aOtherData );    // 发送结果集
	                $aResult = $oChannelApi->getDatas();
	        	}
	        }
	        /** temp_louis **/
            return TRUE;
        }
        elseif( $temp_aData['ownerid'] == $iUserId )
        {//不为默认组而且为自己的组则直接修改
//   temp_louis         return $this->oDB->update( 'proxygroup', $aGroupInfo, " `groupid`='".$iGroupId."' AND `ownerid`='".$iUserId."'" );
			/** temp_louis **/
			$aAdminProxyMenu = array();
			if (!empty($aGroupInfo['menustrs'])){
            	$aAdminProxyMenu['menustrs'] = $aGroupInfo['menustrs'];
			}
			
			//启用事务
            $this->oDB->doTransaction();
			if (!empty($aGroupInfo['menustrs'])){
	            $this->oDB->update('admin_proxy_menu', $aAdminProxyMenu, "groupid = {$iGroupId}");
	            if ($this->oDB->errno() > 0){
	            	$this->oDB->doRollback(); // 事务回滚
	            	return false;
	            }
            }
			
			unset($aGroupInfo['menustrs']);
            $this->oDB->update( 'proxygroup', $aGroupInfo, " `groupid`='".$iGroupId."' AND `ownerid`='".$iUserId."'" );
            if ($this->oDB->errno() > 0){
            	$this->oDB->doRollback(); // 事务回滚
            	return false;
            }
            
            $this->oDB->doCommit();
			/** temp_louis **/
        }
        return TRUE;
    }



    /**
     * 总代删除自己的分组
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代ID
     * @param   string  $sGroupId   //组ID,多个ID用,隔开,如：1,2,3,4,5
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
            $sSql = "SELECT ut.`userid` FROM `usertree` AS ut LEFT JOIN `userchannel` AS uc ON ut.`userid`=uc.`userid`
                     WHERE ut.`isdeleted`='0' AND ut.`usertype`='2' AND ut.`parentid`='".$iUserId."' 
                     AND uc.`groupid`='".intval($sGroupId)."' ";
            $this->oDB->query($sSql);
            if( $this->oDB->ar() > 0 )
            {
                return -1;
            }
            unset( $sSql );
            //直接删除，如果失败可能组为默认组，不允许删除
            $this->oDB->doTransaction(); // 事务开始
            // 删除proxygroup表中的记录
            $sWhereSql = " `groupid`='".intval($sGroupId)."' AND `ownerid`='".$iUserId."' AND `isspecial`>'0'";
            if ($this->oDB->delete( 'proxygroup', $sWhereSql ) === false){
            	$this->oDB->doRollback(); // 事务回滚
            	return false;
            }
            // 删除admin_proxy_menu表中的记录
            if ($this->oDB->delete( 'admin_proxy_menu',  " `groupid`='".intval($sGroupId)."' AND `ownerid`='".$iUserId."'") === false){
            	$this->oDB->doRollback(); // 事务回滚
            	return false;
            }
            $this->oDB->doCommit(); // 事务提交
            return true;
        }
        else
        {//多个删除
            foreach( $aGroupId as &$v )
            {
                $v = intval($v);
            }
            //检测这些组下是否有用户，如果有则不能删除
            $sSql = " SELECT ut.`userid` FROM `usertree` AS ut LEFT JOIN `userchannel` AS uc ON ut.`userid`=uc.`userid`
                     WHERE ut.`isdeleted`='0' AND ut.`usertype`='2' AND ut.`parentid`='".$iUserId."' 
                     AND uc.`groupid` in (".implode(',', $aGroupId).") ";
            $this->oDB->query( $sSql );
            if( $this->oDB->ar() > 0 )
            {
                return -1;
            }
            unset( $sSql );
            //直接删除，如果失败可能组为默认组，不允许删除
            $this->oDB->doTransaction(); // 事务开始
            // 删除proxygroup表中的记录
            $sWhereSql = " `groupid` in (".implode(',', $aGroupId).") AND `ownerid`='".$iUserId."' AND `isspecial`>'0'";
            if ($this->oDB->delete( 'proxygroup', $sWhereSql ) === false){
            	$this->oDB->doRollback(); // 事务回滚
            	return false;
            }
            // 删除admin_proxy_menu表中的记录
            if ($this->oDB->delete( 'admin_proxy_menu',  " `groupid` in (".implode(',', $aGroupId).") AND `ownerid`='".$iUserId."'") === false){
            	$this->oDB->doRollback(); // 事务回滚
            	return false;
            }
            $this->oDB->doCommit(); // 事务提交
            return true;
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
    		$sSql = "INSERT INTO admin_proxy_menu(groupid,ownerid,menustrs) VALUES({$v['groupid']},{$v['ownerid']},'{$v['menustrs']}')";
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
	public function proxyGroupInsert( $sGroupName, $iOwnerId=0, $sMenustr='', $iIsSpecial=0, $iIsDisabled=0, $iIsSales = 0 )
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
			'isdisabled'=> intval($iIsDisabled),	
			'issales'	=> intval($iIsSales)
		);
		return $this->oDB->insert( 'proxygroup', $aData );
	}
}
?>