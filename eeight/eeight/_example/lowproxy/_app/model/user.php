<?php
/**
 * 用户数据模型
 *
 * 功能：
 * 		用于在控制器里根据业务逻辑调用相关的数据模型实现用户的一些操作
 *
 * 		--loginOut           用户注销登陆
 *      --getUsersProfile    读取用户基本信息[usertree]
 * 		--getParentId        获取上级ID
 *      --getParent          获取指定用户的上级列表
 *      --getChildrenId      获取指定用户的直接下级或者所有下级
 *      --getChildListID     根据用户ID获取其下的直接下级或者所有下级用户列表（只获取用户名和ID，不分页，不获取总代管理员）
 *      --getChildList       根据用户ID获取其下的直接下级或者所有下级用户列表
 *      --IsAdminSale        判断是否为销售管理员
 *      --isInAdminSale      判断一个用户是否在某销售管理员团队下
 *      --getAdminProxyByUserId 根据总代管理员ID，读取所有分配的一代ID
 *      --getTeamBank        获取指定用户的团队余额
 *      --isTopProxy         判断一个用户是否为总代
 *      --getTopProxyId      获取指定用户的总代ID
 *      --isParent           判断一个用户是否为另外一个用户的上级
 *      --getAdminList       根据总代ID，获取总代管理员列表信息
 *      --getUserExtentdInfo 根据用户ID，获取基本信息，组别等信息
 *      --frozenUser         根据用户ID冻结用户
 *      --unFrozenUser       根据ID解冻用户
 *      --checkSecurityPass  检测资金密码是否正确
 *      --getUserLeftInfo    获取用户登陆后显示在左侧的信息
 *      --checkUserName      检测用户名是否合法
 *      --checkUserPass      检测登陆密码是否合法
 *      --checkNickName      检测呢称是否合法
 *      --getUseridByUsername    根据用户名, 获取用户ID
 *      --getUseridByUsernameArr 根据用户名数组, 获取用户ID
 *      --getActiveUserCount 获取活跃用户数
 *      --getAllUserCount    获取全部用户数
 *      --adminUpdateUserInfo后台管理员更新用户信息
 *      --updateUserRank     更新用户星级
 *      --checkAdminForUser  检测用户和管理员之间的关系
 *      --getRank            根据用户以及需要统计的星级构成数组
 *      --getunActiveUser    获取不活跃用户列表
 *      --getUnderZeroUser   获取负余额用户
 * 
 * @author    james
 * @version   1.1.0
 * @package   passport
 * @since 	   2009/05/5 - 2009/06/16
 */
class model_user extends basemodel
{
	/* @var $oSession memsession */
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
	 * 用户注销登陆
	 * 	注意：注销登陆会往用户客户端写入cookie，所以，在这之前必须没有任何输出，否则会导致系统错误
	 * 
	 * @access	public
	 * @author 	james	09/05/17
	 * @return	boolean 成功返回TRUE，失败返回FALSE
	 */
	public function loginOut()
	{
		$_SESSION = array();
		session_destroy();
		return TRUE;
	}



	/**
	 * 读取用户基本信息[usertree]
	 * 
	 * @access 	public
	 * @author 	james	09/06/07
	 * @param 	string	$sFiled		//要读取的内容
	 * @param 	string	$sLeftJoin	//要增加的管理表
	 * @param 	string	$sAndWhere	//搜索条件 
	 * @param 	boolean	$bIsMore	//是否为列表搜索 FALSE[默认]：只读一条记录，TRUE：读取多条记录
	 */
	public function & getUsersProfile( $sFiled='', $sLeftJoin='', $sAndWhere='', $bIsMore=FALSE )
	{
		$sFiled	= empty($sFiled) ? '*' : $sFiled;
		$sSql   = " SELECT ".$sFiled." FROM `usertree` AS ut ";
		$sSql  .= $sLeftJoin." WHERE 1 ".$sAndWhere;
		if( $bIsMore )
		{
			return $this->oDB->getAll( $sSql );
		}
		return $this->oDB->getOne( $sSql );
	}



	/**
	 * 获取指定用户的上级ID
	 * 
	 * @access 	public	
	 * @author 	james	09/05/21
	 * @param 	int		$iUserId		//用户ID
	 * @param 	boolean	$bAllParentId	//TRUE：获取所有上级，FALSE：直接上级，默认为FALSE
	 * @return 	mixd	//直接上级直接返回ID，所有上级返回ID字符串（1,2,3,4,5），失败返回FALSE
	 */
	public function getParentId( $iUserId, $bAllParentId=FALSE )
	{
		if( intval($iUserId) < 1 )
		{
			return FALSE;
		}
		$sSql = "";
		if( $bAllParentId )
		{//获取所有上级
			$sSql = "SELECT `parenttree` AS `parents` FROM `usertree` 
						WHERE `userid`='".$iUserId."' AND `isdeleted`='0'";
		}
		else 
		{
			$sSql = "SELECT `parentid` AS `parents` FROM `usertree` 
						WHERE `userid`='".$iUserId."' AND `isdeleted`='0'";
		}
		$aData = $this->oDB->getOne( $sSql );
		if( empty($aData) )
		{//获取失败
			return FALSE;
		}
		return $aData['parents'];
	}



	/**
	 * 获取指定用户的上级列表
	 * 
	 * @access 	public	
	 * @author 	james	09/05/21
	 * @param 	int		$iUserId		//用户ID
	 * @param 	boolean	$bAllParentId	//TRUE：获取所有上级，FALSE：直接上级，默认为FALSE
	 * @return 	mixd	//成功返回上级用户信息列表，失败返回空数组
	 */
	public function & getParent( $iUserId, $bAllParentId=FALSE )
	{
		$aData = $this->getParentId( $iUserId, $bAllParentId );
		if( empty($aData) )
		{ // 获取失败
			return array();
		}
		else
		{
			return $this->oDB->getAll("SELECT `username`,`userid` FROM `usertree` 
						WHERE `userid` IN (".$aData.") AND `isdeleted`='0' ORDER BY `userid` ASC");
		}
	}



	/**
	 * 获取指定用户的直接下级或者所有下级
	 * 
	 * @access 	public
	 * @author 	james	09/05/22
	 * @param 	int		$iUserId		//用户ID
	 * @param 	boolean	$bAllChildren	//TRUE：所有下级，FALSE：直接下级，默认为FALSE
	 * @param 	boolean	$bIsAdmin		//是否获取总代管理员 TURE:包括，FALSE：不包括
	 * @return 	mixd	////直接下级直接返回ID，所有下级返回ID字符串(1,2,3,4,5)，失败返回FALSE
	 */
	public function getChildrenId( $iUserId, $bAllChildren=FALSE, $bIsAdmin=FALSE )
	{
		if( intval($iUserId) < 1 )
		{
			return FALSE;
		}
		$sSql = " AND `isdeleted`='0' ";
		if( !(bool)$bIsAdmin )
		{
			$sAndSql = " AND `usertype`<2 ";
		}
		else 
		{
			$sAndSql = "";
		}
		if( $bAllChildren )
		{//获取所有下级
			$sSql = "SELECT `userid` FROM `usertree` WHERE FIND_IN_SET('".$iUserId."', `parenttree`) ".$sAndSql;
		}
		else 
		{//直接下级
			$sSql = "SELECT `userid` FROM `usertree` WHERE `parentid`='".$iUserId."' ".$sAndSql;
		}
		$this->oDB->query( $sSql );
		$aTempRow	= array();
		$aData		= array();
		while( FALSE != ($aTempRow = $this->oDB->fetchArray()) )
		{
			$aData[] = $aTempRow['userid'];
		}
		unset($aTempRow);
		if( empty($aData) )
		{//获取失败或者没有下级
			return '';
		}
		return implode(',', $aData);
	}

	
	/**
	 * 统计用户的直接下级或者所有下级个数
	 *
	 * @author james   090829
	 * @access public
	 * @param  array    $aUserIds      //用户，多用户的ID
	 * @param  boolean  $bAllChildren  //是否获取所有下级
	 * @param  boolean  $bOnlyActive   //是否只获取活动用户
	 * @return array
	 */
	public function & getChildCount( $aUserIds, $bAllChildren=FALSE, $bOnlyActive=TRUE )
	{
		$aResult = array();
		if(  empty($aUserIds) || !is_array($aUserIds) )
		{
			return $aResult;
		}
		if( $bAllChildren )
		{//获取所有的下级[多用户统计的时候最好不用，效率比较低]
			$aTempArr = array();
			foreach( $aUserIds as $v )
			{
				$aTempArr[] = " FIND_IN_SET('".$v."', ut.`parenttree`) ";
			}
			$sAndWhere = " (".implode(" OR ", $aTempArr).") ";
		}
		else
		{
			$sAndWhere = " ut.`parentid` IN(".implode(",",$aUserIds).") ";
		}
		if( $bOnlyActive )
		{//只获取开通的活跃用户统计
			$sSql = " SELECT COUNT(ut.`userid`) AS childcount, ut.`parentid` FROM `usertree` AS ut
			          LEFT JOIN `userchannel` AS uc ON uc.`userid`=ut.`userid` 
			          WHERE ut.`usertype`<'2' AND ut.`isdeleted`='0' AND ".$sAndWhere."
			          AND uc.`channelid`='".SYS_CHANNELID."' AND uc.`isdisabled`='0' GROUP BY ut.`parentid` ";
		}
		else
		{
			$sSql = " SELECT COUNT(ut.`userid`) AS childcount, ut.`parentid` FROM `usertree` AS ut
                      WHERE ut.`usertype`<'2' AND ut.`isdeleted`='0' AND ".$sAndWhere."  GROUP BY ut.`parentid` ";
		}
		$aTempArr = $this->oDB->getAll($sSql);
		if( empty($aTempArr) )
		{
			return $aResult;
		}
		foreach( $aTempArr as $v )
		{
			$aResult[$v['parentid']] = $v['childcount'];
		}
		return $aResult;
	}


	/**
	 * 根据用户ID获取其下的直接下级或者所有下级用户列表（只获取用户名和ID，不分页，不获取总代管理员）[不获取未开通用户]
	 * 
	 * @access 	public
	 * @author 	james	09/05/21
	 * @param 	int		$iUserId	//用户ID
	 * @param 	boolean	$bAllChildren	//TRUE：所有下级，FALSE：直接下级，默认为TRUE
	 * @param 	string	$sAndWhere		//附加搜索条件
	 * @param 	return	//成功返回用户列表，失败返回FALSE
	 */
	public function & getChildrenListID( $iUserId, $bAllChildren=TRUE, $sAndWhere='', $bIsCount=FALSE )
	{
		$aResult = array();
		if( intval($iUserId) < 0 )
		{
			return $aResult;
		}
		$sSql = "";
		if( $bAllChildren )
        {//获取所有下级
            $sSql = "SELECT ut.`userid`,ut.`username`,ut.`usertype` FROM `userchannel` AS uc
                    LEFT JOIN `usertree` AS ut ON uc.`userid`=ut.`userid` 
                    WHERE FIND_IN_SET('".$iUserId."', ut.`parenttree`) 
                    AND ut.`usertype`<'2' AND ut.`isdeleted`='0' ".$sAndWhere." 
                    AND uc.`channelid`='".SYS_CHANNELID."' AND uc.`isdisabled`='0' ";
            $aResult =  $this->oDB->getAll( $sSql );
            
        }
        else 
        {//直接下级
            $sSql = "SELECT a.`userid`,a.`username`,a.`usertype` FROM `userchannel` AS uc
                     LEFT JOIN `usertree` AS a ON uc.`userid`=a.`userid`
                     WHERE a.`parentid`='".$iUserId."' AND a.`usertype`<'2' AND a.`isdeleted`='0'
                     AND uc.`channelid`='".SYS_CHANNELID."' AND uc.`isdisabled`='0'
                     ".$sAndWhere." GROUP BY a.`username` ORDER BY a.`username` ";
            $aResult =  $this->oDB->getAll( $sSql );
	        if( $bIsCount == TRUE )
	        {
		        $aTempArr= array();
	            foreach( $aResult as $v )
	            {
	                $aTempArr[] = $v['userid'];
	            }
	            //获取下级用户的直接下级用户统计[只获取开通了的用户的统计]
	            $aActiveUser = $this->getChildCount( $aTempArr );
	            foreach( $aResult as & $v )
	            {
	                if( isset($aActiveUser[$v['userid']]) )
	                {
	                    $v['childcount'] = $aActiveUser[$v['userid']];
	                }
	                else
	                {
	                    $v['childcount'] = 0;
	                }
	            }
	        }
        }
        return $aResult;
	}
	
	
	
	/**
     * 根据用户ID获取其下的直接下级用户列表（只获取用户名和ID，不分页，不获取总代管理员）[获取未开通用户]
     * 
     * @access  public
     * @author  james   09/05/21
     * @param   int     $iUserId    //用户ID
     * @param   string  $sAndWhere      //附加搜索条件
     * @param   boolean $bIsCount       //是否统计下级
     * @param   return  //成功返回用户列表，失败返回FALSE
     */
	public function & getChildListID( $iUserId, $sAndWhere='', $bIsCount=FALSE )
	{
		$aResult = array();
        if( intval($iUserId) < 0 )
        {
            return $aResult;
        }
        //再获取所有直接下级[未开通的一起]
        $sSql = " SELECT a.`userid`,a.`username`,a.`usertype` FROM `usertree` AS a WHERE a.`parentid`='".$iUserId."'
                  AND a.`usertype`<'2' AND a.`isdeleted`='0' ".$sAndWhere;    
        $aResult = $this->oDB->getAll( $sSql );
        if( $bIsCount == FALSE )
        {
        	return $aResult;
        }
        $aTempArr= array();
        foreach( $aResult as $v )
        {
        	$aTempArr[] = $v['userid'];
        }
        //获取下级用户的直接下级用户统计[只获取开通了的用户的统计]
        $aActiveUser = $this->getChildCount( $aTempArr );
        foreach( $aResult as & $v )
        {
        	if( isset($aActiveUser[$v['userid']]) )
        	{
        		$v['childcount'] = $aActiveUser[$v['userid']];
        	}
        	else
        	{
        		$v['childcount'] = 0;
        	}
        }
        return $aResult;
	}



	/**
	 * 根据用户ID获取其下的直接下级或者所有下级用户列表（获取详细信息，分页，不获取总代管理员）[只获取开通用户]
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//用户ID
	 * @param 	string	$sField//要查询的内容，表别名:user=>u,usertree=>ut,userchannel=>uc,usergroup=>ug,userfund=>uf
	 * @param 	string	$sAndWhere	//附加的查询条件，以AND 开始
	 * @param 	string	$sOrderBy	//排序条件，默认按照用户ID排序
	 * @param 	int		$iPageRecords //每页显示的条数
	 * @param 	int		$iCurrPage	//当前页
	 * @param 	boolean	$bAllChildren	//TRUE：所有下级，FALSE：直接下级，默认为TRUE
	 * @param 	boolean	$bIsSelf		//是否要获取自己的信息 TRUE:获取，FALSE：不获取
	 * @param 	int		$iCurrentId		//当前查看用户ID
	 * @param 	return	//成功返回用户列表array('affects'=>总记录数,'results'=>结果集合)，失败返回FALSE
	 * 
	 * UPDATE SAUL 20090520
	 * 当$iUserId =0以及$bAllChildren = TRUE时候的修正
	 */
	public function & getChildrenList( $iUserId, $sField='', $sAndWhere='', $sOrderBy='', $iPageRecords=20, 
									$iCurrPage=1, $bAllChildren=TRUE, $bIsSelf=FALSE, $iCurrentId=0 )
	{
		$result	= array();
		if( !is_numeric($iUserId) )
		{//获取失败或者没有下级
			return $result;
		}
		$sTableName = " `usertree` AS ut  
				 		LEFT JOIN `userfund` AS uf ON ut.`userid`=uf.`userid`
				 		LEFT JOIN `userchannel` AS uc ON ut.`userid`=uc.`userid` ";
		$sFields	= " ut.`userid`,ut.`username`,ut.`usertype`,ut.`parentid`,ut.`parenttree`,uf.`availablebalance` ";
		$sCondition = " ut.`isdeleted`='0' AND ut.`usertype`<'2' AND uf.`channelid`='".SYS_CHANNELID."' 
		                AND uc.`channelid`='".SYS_CHANNELID."' AND uc.`isdisabled`='0' ";
		if( (bool)$bAllChildren )
		{
			if ($iUserId > 0)
			{
				$sAndWhere .= " AND FIND_IN_SET('".$iUserId."',ut.`parenttree`) ";
			}
		}
		else 
		{
			$sAndWhere .= " AND ut.`parentid`='".$iUserId."' ";
		}
		if( !empty($sField) )
		{
			$sFields = $sField;
		}
		if( empty($sOrderBy) )
		{//默认没有排序
			//$sCondition .= " ORDER BY u.`userid` ASC ";
		}
		else 
		{
			$sOrderBy = " ORDER BY ".$sOrderBy;
		}
		$sCondition .= $sAndWhere;
		$result = $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, 
											 $sOrderBy );
		if( (bool)$bIsSelf )
		{//获取自己的信息
			//获取指定用户的父ID
			$iParentId = intval( $this->getParentId($iUserId) );
			if( $iParentId == $iCurrentId )
			{//查看用户的直接下级
				$sSql = "SELECT ".$sFields." FROM `usertree` AS ut  
                      LEFT JOIN `userfund` AS uf ON (ut.`userid`=uf.`userid` AND uf.`channelid`='".SYS_CHANNELID."')
                      WHERE ut.`isdeleted`='0' AND ut.`userid`='".$iUserId."' ";
			}
			else 
			{
				$sSql = "SELECT ".$sFields." FROM ".$sTableName." WHERE ut.`isdeleted`='0' 
                         AND uf.`channelid`='".SYS_CHANNELID."' AND ut.`userid`='".$iUserId."' 
                         AND uc.`channelid`='".SYS_CHANNELID."' AND uc.`isdisabled`='0' ";
			}
			$this->oDB->query( $sSql );
			unset( $sSql );
			if( $this->oDB->numRows() > 0 )
			{
				$result['self'] = $this->oDB->fetchArray();
			}
			if( !empty($iCurrentId) )
			{
				$temp_parenttree = preg_replace("/^[\\d,]*".$iCurrentId."[,]?/i",
				                                    "",$result['self']['parenttree'],1);
			}
			//获取导航
			if( !empty($temp_parenttree) )
			{
				$sSql = "SELECT `userid`,`username` FROM `usertree` 
				         WHERE `userid` IN(".$temp_parenttree.")";
				$result['self']['bannners'] = $this->oDB->getAll($sSql);
			}
		}
		return $result;
	}
	
	
	
    /**
     * 获取当前用户的下级用户列表（获取详细信息，分页，不获取总代管理员）[获取所有直接下级用户]
     * 
     * @access  public
     * @author  james   09/05/17
     * @param   int     $iUserId    //用户ID
     * @param   string  $sField//要查询的内容，表别名:user=>u,usertree=>ut,userchannel=>uc,usergroup=>ug,userfund=>uf
     * @param   string  $sAndWhere  //附加的查询条件，以AND 开始
     * @param   string  $sOrderBy   //排序条件，默认按照用户ID排序
     * @param   int     $iPageRecords //每页显示的条数
     * @param   int     $iCurrPage  //当前页
     * @param   boolean $bAllChildren   //TRUE：所有下级，FALSE：直接下级，默认为TRUE
     * @param   boolean $bIsSelf        //是否要获取自己的信息 TRUE:获取，FALSE：不获取
     * @param   int     $iCurrentId     //当前查看用户ID
     * @param   return  //成功返回用户列表array('affects'=>总记录数,'results'=>结果集合)，失败返回FALSE
     * 
     * UPDATE SAUL 20090520
     * 当$iUserId =0以及$bAllChildren = TRUE时候的修正
     */
    public function & getChildList( $iUserId, $sField='', $sAndWhere='', $sOrderBy='', $iPageRecords=20, 
                                    $iCurrPage=1 )
    {
        $result = array();
        if( !is_numeric($iUserId) )
        {//获取失败或者没有下级
            return $result;
        }
        $sTableName = " `usertree` AS ut  LEFT JOIN `userfund` AS uf ON 
                        (ut.`userid`=uf.`userid` AND uf.`channelid`='".SYS_CHANNELID."') ";
        $sFields    = " ut.`userid`,ut.`username`,ut.`usertype`,ut.`parentid`,ut.`parenttree`,uf.`availablebalance` ";
        $sCondition = " ut.`isdeleted`='0' AND ut.`usertype`<'2' ";
        
        $sAndWhere .= " AND ut.`parentid`='".$iUserId."' ";
        if( !empty($sField) )
        {
            $sFields = $sField;
        }
        if( empty($sOrderBy) )
        {//默认没有排序
            //$sCondition .= " ORDER BY u.`userid` ASC ";
        }
        else 
        {
            $sOrderBy = " ORDER BY ".$sOrderBy;
        }
        $sCondition .= $sAndWhere;
        $result = $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, 
                                             $sOrderBy );
        return $result;
    }



	/**
	 * 判断是否为销售管理员
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//管理员ID
	 * @return 	//是返回TRUE，不是返回FALSE
	 */
	public function IsAdminSale( $iUserId )
	{
		if( empty($iUserId) || !is_numeric($iUserId) )
		{//获取失败或者没有下级
			return FALSE;
		}
		/* temp_louis $sSql = " SELECT 1 FROM `usergroupset` AS ugs LEFT JOIN `proxygroup` AS pg ON ugs.`groupid`=pg.`groupid` 
				WHERE ugs.`userid`='".$iUserId."' AND pg.`issales`='1' ";*/
		$sSql = " SELECT 1 FROM `userchannel` AS uc LEFT JOIN `proxygroup` AS pg ON uc.`groupid`=pg.`groupid` 
				LEFT JOIN `admin_proxy_menu` AS apm ON pg.`groupid` = apm.`groupid`
				WHERE uc.`userid`='".$iUserId."' AND pg.`issales`='1' AND uc.`channelid` = " . SYS_CHANNELID;
		$this->oDB->query($sSql);
		if( $this->oDB->ar() > 0 )
		{//是销售管理员
			return TRUE;
		}
		return FALSE;
	}



	/**
	 * 判断一个用户是否在某销售管理员团队下
	 * 
	 * @access 	public
	 * @author 	james	09/05/22
	 * @param 	int		$iUserId	//用户ID
	 * @param 	int		$iAdminId	//销售管理员ID
	 * @param 	return	//在团队下返回TRUE，不在返回FALSE
	 */
	public function isInAdminSale( $iUserId, $iAdminId )
	{
		if( !is_numeric($iUserId) || !is_numeric($iAdminId) )
		{//获取失败或者没有下级
			return FALSE;
		}
		$sSql = "SELECT ut.`userid` FROM `usertree` AS ut 
				LEFT JOIN `useradminproxy` AS uap ON ut.`lvproxyid`=uap.`topproxyid` 
				 WHERE uap.`adminid`='".$iAdminId."' AND ut.`userid`='".$iUserId."' AND ut.`isdeleted`='0' ";
		//读取分配给销售管理员的一代的ID
		$this->oDB->query($sSql);
		unset($sSql);
		if( $this->oDB->numRows() < 1 )
		{//没有分配
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}



	/**
	 * 根据总代管理员ID，读取所有分配的一代ID
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//总代管理员ID
	 * @return 	mixed		//成功返回ID数组，失败返回FALSE
	 */
	public function getAdminProxyByUserId( $iUserId )
	{
		if( empty($iUserId) )
		{
			return FALSE;
		}
		$sSql = " SELECT `entry`,`topproxyid` FROM `useradminproxy` WHERE `adminid`='". intval($iUserId) ."'";
		$this->oDB->query($sSql);
		if( $this->oDB->ar() < 1 )
		{
			return FALSE;
		}
		$aResult    = array();
		$aTempRow   = array();
		while( FALSE != ($aTempRow=$this->oDB->fetchArray()) )
		{
			$aResult[] = $aTempRow['topproxyid'];
		}
		return $aResult;
	}



	/**
	 * 获取指定用户的团队余额
	 * 
	 * @access 	public
	 * @author 	james	09/05/22
	 * @param 	int		$iUserid	//用户ID
	 * @param 	string	$sAndWhere	//附加搜索条件
	 * @return 	flaot	//团队余额
	 */
	public function getTeamBank( $iUserId, $sAndWhere='' )
	{
		if( !is_numeric($iUserId) )
		{
			return 0.0000;
		}
		$sSql = "SELECT SUM(uf.`availablebalance`) AS JAMESCOUNT FROM `userfund` AS uf 
				LEFT JOIN `usertree` AS ut ON (uf.`userid`=ut.`userid` AND ut.`isdeleted`='0') 
				WHERE uf.`channelid`='".SYS_CHANNELID."' ";
		if( is_numeric($iUserId) && $iUserId >0 )
		{//如果是单个用户
			$sSql .= " AND (ut.`userid`='".$iUserId."' OR FIND_IN_SET('".$iUserId."', `parenttree`)) ";
		}
		$sSql .= $sAndWhere;
		$this->oDB->query( $sSql );
		if( $this->oDB->numRows() <1 )
		{
			return 0.0000;
		}
		$aData = $this->oDB->fetchArray();
		return $aData['JAMESCOUNT'];
			
	}
	/**
	 * 判断一个用户是否为总代
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId 	//用户ID
	 * @return 	boolean	//如果是则返回TRUE，不是则返回FALSE
	 */
	public function isTopProxy( $iUserId )
	{
		$iPid = $this->getParentId( $iUserId );
		if( $iPid === FALSE )
		{
			return FALSE;
		}
		if( $iPid == 0 )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	
	/**
	 * 判断一个用户是否为总代的直接下级[不包括总代管理员]
	 *
	 * @author james 090810
	 * @access public
	 * @param  int  $iUserId
	 * @return boolean TRUE or FALSE
	 */
	public function isLvProxy( $iUserId )
	{
	    if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户信息错误
            return FALSE;
        }
        $sSql = " SELECT 1 FROM `usertree` WHERE `userid`='".$iUserId."' AND `lvproxyid`='".$iUserId."'
                  AND `usertype`<>'2' ";
        $this->oDB->query($sSql);
        if( $this->oDB->ar() > 0 )
        {
        	return TRUE;
        }
        return FALSE;
	}



	/**
	 * 获取指定用户的总代ID
	 * 
	 * @access	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//用户ID
	 * @param 	boolean	$bUserInfo	//是否要获取总代其他信息
	 * @return 	int	//总代ID，本身为总代时返回自身ID，失败返回FALSE
	 */
	public function getTopProxyId( $iUserId, $bUserInfo=FALSE )
	{
		if( FALSE === ($aData=$this->getParentId($iUserId, TRUE)) )
		{
			return FALSE;
		}
		if( empty($aData) )
		{//本身即为总代ID
			$iPrentId = $iUserId;
		}
		else
		{
			//返回父亲树下面的第一个ID即为总代ID
			$aTempData = explode( ',', $aData );
			unset($aData);
			$iPrentId = $aTempData[0];
		}
		if( FALSE == $bUserInfo )
		{
			return $iPrentId; 
		}
		$sSql = "SELECT `userid`,`username` FROM `usertree` WHERE `userid`='".$iPrentId."' AND `isdeleted`='0' ";
		$aResult = $this->oDB->getOne($sSql);
		if( empty($aResult) )
		{
			return FALSE;
		}
		return $aResult;
		
	}



	/**
	 * 判断一个用户是否为另外一个用户的上级
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//下级用户ID
	 * @param 	int		$iParentId	//上级ID
	 * @return 	//是上级返回TRUE，不是返回FALSE
	 */
	public function isParent( $iUserId, $iParentId )
	{
		if( empty($iUserId) || empty($iParentId) || !is_numeric($iUserId) || !is_numeric($iParentId) )
		{
			return FALSE;
		}
		$sSql = "SELECT `userid` FROM `usertree` WHERE FIND_IN_SET('"
		            . $iParentId . "', `parenttree`) AND `userid`='".$iUserId."'";
		$this->oDB->query($sSql);
		if( $this->oDB->ar() >0 )
		{
			return TRUE;
		}
		return FALSE;
	}



	/**
	 * 根据总代ID，获取总代管理员列表信息(用户ID，用户登陆名，用户呢称，组ID，组名，)
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//总代ID
	 * @return 	mixed	//成功返回列表信息，失败返回FALSE
	 */
	public function getAdminList( $iUserId, $sOrderby='' )
	{
		if( empty($iUserId) || !is_numeric($iUserId) )
		{
			return FALSE;
		}
		/** temp_louis **/
		$sSql = "SELECT ut.`userid`,ut.`nickname`,ut.`username`,pg.`groupid`,pg.`groupname`,
		                pg.`isspecial`,pg.`issales`
				FROM `usertree` AS ut 
				LEFT JOIN `userchannel` AS uc ON ut.`userid`=uc.`userid` 
				LEFT JOIN `proxygroup` AS pg ON uc.`groupid`=pg.`groupid` 
				WHERE 
				uc.channelid = " . SYS_CHANNELID . " AND ut.`parentid`='".intval($iUserId)."' AND ut.`isdeleted`='0' AND ut.`usertype`='2' ".$sOrderby;
		return $this->oDB->getAll( $sSql );
	}



	/**
	 * 根据用户ID，获取基本信息，组别等信息(userid,username,nickname,groupname,groupid)
	 * 
	 * @access 	public
	 * @author 	james	09/05/22
	 * @param 	int		$iUserId 	//用户ID
	 * @param 	int		$iUserType	//用户类型，0用户，1代理，2总代管理员
	 * @return 	mixed	//失败返回FALSE，成功返回用户信息
	 */
	public function getUserExtentdInfo( $iUserId, $iUserType=0 )
	{
		if( empty($iUserId) || !is_numeric($iUserType) )
		{
			return FALSE;
		}
		if( $iUserType == 2 )
		{
			$sSql = "SELECT ut.`userid`,ut.`username`,ut.`nickname`,g.`groupname`,g.`groupid`,
			                g.`ownerid`,g.`issales` 
					FROM `usertree` AS ut
					LEFT JOIN `usergroupset` AS ugs ON ut.`userid`=ugs.`userid` 
					LEFT JOIN `proxygroup` AS g ON ugs.`groupid`=g.`groupid` 
					WHERE ut.`isdeleted`='0' AND ut.`userid`='".$iUserId."' ";
		}
		else 
		{
			$sSql = "SELECT ut.`userid`,ut.`username`,ut.`nickname`,g.`groupname`,g.`groupid`,
			                g.`isspecial`, g.`teamid` 
					FROM `usertree` AS ut
					LEFT JOIN `usergroupset` AS ugs ON ut.`userid`=ugs.`userid` 
					LEFT JOIN `usergroup` AS g ON ugs.`groupid`=g.`groupid` 
					WHERE ut.`isdeleted`='0' AND ut.`userid`='".$iUserId."' ";
		}
		$aData = $this->oDB->getOne($sSql);
		if( empty($aData) )
		{
			return FALSE;
		}
		return $aData;
	}



	/**
	 * 获取用户登陆后显示在左侧的信息
	 * 
	 * @access 	public
	 * @author 	james	09/05/30
	 * @param 	int		$iUserId	//用户ID
	 * @return 	array	//返回用户银行可用余额，未读短消息数量，星级，可开户数额
	 */
	public function & getUserLeftInfo( $iUserId, $iUserType=1, $sFields="" )
	{
		$aResult = array();
		if( empty($iUserId) || !is_numeric($iUserId) )
		{
			return $aResult;
		}
		if( $iUserType == 2 )
		{//如果为总代管理员则可用余额，星级 调整为总代的
			$iUserId = $this->getTopProxyId( $iUserId );
		}
		$sFields = empty($sFields) ? " ut.`userid`,ut.`username`,ut.`userrank`,uf.`availablebalance` " 
		                         : daddslashes($sFields);
		$sSql   = "SELECT ".$sFields." FROM `usertree` AS ut
				   LEFT JOIN `userfund` AS uf ON ut.`userid`=uf.`userid`
				   WHERE ut.`userid`='".$iUserId."' AND ut.`isdeleted`='0' AND uf.`channelid`='".SYS_CHANNELID."'";
		$aResult = $this->oDB->getOne( $sSql );
		if( empty($aResult) )
		{
			return $aResult;
		}
	    // 获取上级的星级
        if( $aResult['parentid'] > 0 )
        {
            $sSql = " SELECT `userrank` FROM `usertree` WHERE `isdeleted`='0' 
                      AND `userid`='".$aResult['parentid']."'";
            $aTemp = $this->oDB->getOne( $sSql );
            if( !empty($aTemp) )
            {
                $aResult['parentrank'] = $aTemp['userrank'];
            }
        }
		return $aResult;	
	}



	/**
	 * 检测用户名是否合法
	 * @access static
	 * @author 	james	09/05/17
	 * @return 合法返回TRUE，不合法返回FALSE
	 */
	static function checkUserName( $sUserName )
	{
		if( preg_match( "/^[0-9a-zA-Z]{6,16}$/i", $sUserName ) )
		{
			return TRUE;
		}
		else 
		{
			return FALSE;
		}
	}



	/**
	 * 检测登陆密码是否合法
	 * @access static
	 * @author 	james	09/05/17
	 * @return 合法返回TRUE，不合法返回FALSE
	 */
	static function checkUserPass( $sUserPass )
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
	 * 检测呢称是否合法[2-6个任意字符，中文和全角算一个]
	 * @access static
	 * @author 	james	09/05/26
	 * @return 合法返回TRUE，不合法返回FALSE
	 */
	static function checkNickName( $sName )
	{
		if( mb_strlen($sName,"UTF-8")>=2 && mb_strlen($sName,"UTF-8")<=6 )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}



	/**
	 * 根据用户名, 获取用户ID
	 *
	 * @param string $sUsername
	 * @return int userid or 0
	 */
	public function getUseridByUsername( $sUsername = '' )
	{
	    $sUsername = daddslashes( trim($sUsername) );
	    if( !strstr($sUsername,'*') )
	    {
    	    $aResult = $this->oDB->getOne( "SELECT `userid` FROM `usertree` WHERE `username`='$sUsername' LIMIT 1" );
    	    if( $this->oDB->ar() )
    	    {
    	        return $aResult['userid'];
    	    }
    	    else 
    	    {
    	        return 0;
    	    }
	    }
	    else
	    {
	        $aResult = $this->oDB->getAll( "SELECT `userid` FROM `usertree` WHERE `username` LIKE '".
	                   str_replace( '*', '%', $sUsername ) ."' " );
    	    if( $this->oDB->ar() )
    	    {
    	        return $aResult;
    	    }
    	    else 
    	    {
    	        return 0;
    	    }
	    }
	}



	/**
	 * 根据用户名数组, 获取用户ID
	 *   支持模糊搜索, 例:  tom,james* 将*替换成%
	 * @param string $sUsername
	 * @return int userid or 0
	 */
	public function getUseridByUsernameArr( $aUsername = array() )
	{
	    if( !is_array($aUsername) || empty($aUsername) )
	    {
	        return '';
	    }	    
	    $sWhere = ' 0 ';
	    foreach( $aUsername as $v )
	    {
	        if( trim($v) == '' )
	        {
	            continue;
	        }
	        if( !strstr( $v, '*' ) )
	        {
	            $sWhere .= " OR `username` = '" . daddslashes($v) . "' ";
	        }
	        else 
	        {
	            $sWhere .= " OR `username` LIKE '" . daddslashes( str_replace( '*', '%', $v) ) . "' ";
	        }
	    } 
	    $aResult = $this->oDB->getAll( "SELECT `userid` FROM `usertree` WHERE $sWhere ");
	    $aReturn = array();
	    if( $this->oDB->ar() )
	    {
	        foreach( $aResult as $v )
	        {
	            if( is_numeric($v['userid']) )
	            {
	                $aReturn[] = $v['userid'];
	            }
	        }
	    }
	    return $aReturn;
	}



	/**
	 * 获取活跃用户数 (for cli charts)
	 * TODO: 活跃用户的定义: 14天无账变, 账户资金小于2元的
	 * @param string $sDate   Y-m-d H:i:s 
	 * @return mix
	 */
	public function getActiveUserCount( $sDate = '' )
	{
	    $sDate   = empty($sDate) ? date('Y-m-d 00:00:00') : daddslashes($sDate);
	    $aResult = $this->oDB->getOne( "SELECT count(`userid`) AS TOMCOUNT from `users` WHERE `lasttime` >= '$sDate' " );
	    return ($this->oDB->ar()>0) ? $aResult['TOMCOUNT'] : 0;
	}



	/**
	 * 获取全部用户数 (for cli charts)
	 * @return mix
	 */
	public function getAllUserCount()
	{
	    $aResult = $this->oDB->getOne( "SELECT count(ut.`userid`) AS TOMCOUNT from `usertree` AS ut LEFT JOIN `userchannel` AS uc ON (ut.`userid` = uc.`userid`) where uc.`channelid`='".SYS_CHANNELID."' AND ut.`isdeleted` = 0" );
	    return ($this->oDB->ar()>0) ? $aResult['TOMCOUNT'] : 0;
	}



	/**
	 * 后台管理员更新用户信息
	 *
	 * @param int $iUserid 用户ID
	 * @param int $usertypechange 是否进行用户类型转化
	 * @param string $sUsernick(用户昵称)
	 * @author SAUL
	 */
	function adminUpdateUserInfo( $iUserId ,$sUsernick, $bUserTypeChange = FALSE)
	{
		$aUserinfo = $this->getUserExtentdInfo($iUserId);
		if( empty($aUserinfo) )
		{//用户不存在或者是管理员
			return 0;
		}
		if( !$this->checkNickName($sUsernick) )
		{//验证昵称
			return -1;//用户昵称不正确
		}
		if( $aUserinfo['nickname']!=$sUsernick )
		{//更新昵称
			$this->oDB->query("UPDATE `users` SET `nickname`='".daddslashes($sUsernick)."' 
			                     WHERE `userid`='".$iUserId."'");
			if( $this->oDB->errno() > 0 )
			{//更新用户昵称失败
				return -2;
			}
		}
		//检测能不能进行转化
		if( $bUserTypeChange )
		{
			//检测自身的有没有下级
			$mIds = $this->getChildListID( $iUserId );
			//原始位置的判断(预测位置)
			if( $mIds=== FALSE )
			{		
				$aUserPostion = $this->oDB->getone("SELECT `parentid`,`parenttree` FROM `usertree` 
				                                       WHERE `userid`='".$iUserId."'");
				if( $aUserPostion['parentid']==0 )
				{
					$iPostion = 1;//总代
					$iUserTop = $iUserId;//自身的TOP
				}
				elseif( $aUserPostion['parentid'] == $aUserPostion['parenttree'] )
				{
					$iPostion = 2;//一代
					$iUserTop = $aUserPostion['parentid'];
				}
				else 
				{
					$iPostion  = 3;//其他
					$aPostions = explode(',',$aUserPostion['parenttree']);
					$iUserTop  = $aPostions[0];
					unset($aPostions);
				}
				unset($aUserPostion);
				//允许修改
				//原始组信息
				$iUserType =( $aUserinfo['isspecial'] == 0)? $aUserinfo["groupid"] : $aUserinfo["isspecial"];
				if( $iUserType == 1 )//原始组为总代
				{
					return -3;//总代暂时不能转化为会员，系统不支持
				}				
				if( $iUserType ==4 )
				{
					//用户转化为代理,需要查询自身所处的位置以及是否有特殊组
					//查询特殊组存不存在
					$aUserTeam =$this->oDB->getone("SELECT * FROM `usergroup` WHERE `teamid`='".$iUserTop."'"
									." AND `isspecial`='".$iPostion."'");
					if( empty($aUserTeam) )
					{
						$iTeam = $iPostion;
					}								
					else 
					{
						$iTeam = $aUserTeam["groupid"];
					}
					//开始事务
					$this->oDB->doTransaction();
					//用户users 转化
					$this->oDB->query( "UPDATE `users` SET `usertype`='1' WHERE `userid`='".$iUserId."'" );
					if( $this->oDB->ar() < 1 )
					{////更新用户表时候失败,事务取消
						$this->oDB->doRollback();
						return -4;
					}					
					//usertree 转化
					$this->oDB->query( "UPDATE `usertree` SET `usertype`='1' WHERE `userid`='".$iUserId."'" );
					if( $this->oDB->ar() < 1 )
					{//更新用户树失败,//事务取消
						$this->oDB->doRollback();
						return -5;
					}	
					//userchannel 转化
					$this->oDB->query( "UPDATE `userchannel` SET `groupid`='".$iTeam."' WHERE "
							."`userid`='".$iUserId."' AND `channelid`='0'" );
					if( $this->oDB->ar() < 1 )
					{//事务取消
						$this->oDB->doRollback();
						return -6;
					}
					//事务提交
					$this->oDB->doCommit();
					return 1;
				}
				else
				{
					//代理转化为用户,需要查询有无特殊组
					$aUserTeam =$this->oDB->getone("SELECT * FROM `usergroup` WHERE `teamid`='4'"
									." AND `isspecial`='".$iPostion."'");
					if( empty($aUserTeam) )
					{
						$iTeam = 4;
					}								
					else 
					{
						$iTeam = $aUserTeam["groupid"];
					}
					$this->oDB->doTransaction();
					//用户users 转化
					$this->oDB->query( "UPDATE `users` SET `usertype`='0' WHERE `userid`='".$iUserId."' and `userrank`='0'" );
					if( $this->oDB->ar() < 1 )
					{//事务取消
						$this->oDB->doRollback();
						return -4;
					}					
					//usertree 转化
					$this->oDB->query("UPDATE `usertree` SET `usertype`='0' WHERE `userid`='".$iUserId."'");
					if( $this->oDB->ar() < 1 )
					{//事务取消
						$this->oDB->doRollback();
						return -5;
					}
					//userchannel 转化
					$this->oDB->query("UPDATE `userchannel` SET `groupid`='".$iTeam."' WHERE ".
					                  "`userid`='".$iUserId."' AND `channelid`='0'");
					if( $this->oDB->ar() < 1 )
					{//事务取消
						$this->oDB->doRollback();
						return -6;
					}
					//事务提交
					$this->oDB->doCommit();
					return 1;					
				}//转化完毕
			}
			else 
			{//不允许修改
				return -7;//用户昵称转化成功，但是用户有下级不能进行转化
			}
		}
		else
		{//转化成功
			return 1;
		}
	}



	/**
	 * 更新用户星级
	 *
	 * @param int $iUserId
	 * @param int $iUserRank
	 * @return int
	 * @author SAUL 09/06/11
	 */
	public function updateUserRank( $iUserId , $iUserRank )
	{
		$iUserId   = is_numeric($iUserId) ? intval($iUserId) :0;
		$iUserRank = is_numeric($iUserRank) ? intval($iUserRank):0;
		if( $iUserId == 0 )
		{
			return -1;
		}
		//查找直接上级
		$iUserPid    = $this->getParentId( $iUserId, FALSE );
		//查找所有上级
		$sUserAllPid = $this->getParentId( $iUserId, TRUE );
		if( $iUserPid == 0 )
		{
			return -2;//总代不能授权
		}
		if( $iUserPid == intval($sUserAllPid) )
		{ //一级代理
			$iMaxStar = 5;
		}
		else 
		{ //直接上级
			$aUserTop = $this->oDB->getOne( "SELECT `userrank` FROM `users` WHERE `userid`='".$iUserPid."'" );
			if( empty($aUserTop) )
			{
				$iMaxStar = 0;
			}
			else 
			{
				$iMaxStar = $aUserTop["userrank"];
			}
		}
		if( $iMaxStar < $iUserRank )
		{
			return -3;//不能大于上级星级
		}
		//获取所有的下级的最大值
		$aArr = array();
		$aArr = $this->oDB->getone("SELECT `userrank` FROM `users` WHERE `userid` IN "
				."(SELECT `userid` FROM `usertree` WHERE `parentid`='".$iUserId."') order by `userrank` DESC");
		if( empty($aArr) )
		{
			$iMinStar = 0;
		}
		else 
		{
			$iMinStar = $aArr["userrank"];
		}
		if( $iMinStar > $iUserRank )
		{//不能小于下级的最大星级，走的是快捷路径，直接读他的所有直接下级的最大
			return -4;
		}
		//更新建立时间
		$this->oDB->query("UPDATE `users` SET `rankcreatetime`= '" . date("Y-m-d H:i:s", time()) . "'"
		." WHERE `userid`='".$iUserId."' AND `rankcreatetime` is NULL");
		$this->oDB->query("UPDATE `users` SET `rankupdate`= '" . date("Y-m-d H:i:s", time()) . "',`userrank`='".$iUserRank."'" 
		."WHERE `userid`='".$iUserId."'");
		return 1;
	}



	/**
	 * 检测用户和管理员之间的关系
	 *
	 * @param int $iAdminid
	 * @param int $iUserId
	 * @return BOOL
	 * @author SAUL 20090520
	 */
	public function checkAdminForUser( $iAdminId , $iUserId )
	{
		if( ($iAdminId <= 0) || ($iUserId < 0) )
		{
			return FALSE;
		}
		//查询是不是销售管理员
		$this->oDB->query("SELECT `groupid` FROM `admingroup` WHERE `groupid` =("
		." SELECT `groupid` FROM `adminuser` WHERE `adminid`='".$iAdminId."') AND `issales`='0'");
		if( $this->oDB->ar() == 1 )
		{
			return TRUE;
		}
		else 
		{
			if( $iUserId == 0 )
			{//直接获取关系树
				return FALSE;
			}
			else 
			{//查询用户的总代和销售管理员之间的关系
				$this->oDB->query("SELECT `userid` FROM `usertree` WHERE `userid`='".$iUserId."' AND "
				 ."`lvtopid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdminId."')");
				if( $this->oDB->ar() == 1 )
				{
					return TRUE;
				}
				else 
				{
					return FALSE;
				}
			}
		}
	}



	/**
	 * 根据用户以及需要统计的星级构成数组
	 *
	 * @param array $aUser
	 * @param array $aType
	 */
	public function & getRank( $aUser, $aType )
	{
		$aResult = array();
		if( !is_array($aUser) || !is_array($aType) )
		{
			return $aResult;
		}
		//用户整理
		foreach($aUser as $key=>$value )
		{
			if( !is_numeric($value) )
			{
				unset( $aUser[$key] );
			}
		}
		//用户星级整理
		foreach( $aType as $key =>$value )
		{
			if( !in_array($value, array(1,2,3,4,5)) )
			{
				unset( $aType[$key] );
			}
		}
		if( empty($aUser) || empty($aType) )
		{
			return $aResult;
		}
		return $this->oDB->getAll("SELECT u.`userid`,u.`username`,ut.`parentid`,u.`userrank` FROM `users` "
				."as u LEFT JOIN `usertree` AS ut ON (u.`userid`=ut.`userid`) WHERE ut.`parentid` IN "
				."(".join(",", $aUser).") AND u.`userrank` IN (".join(",", $aType).")");
		 
	}


	/**
	 * 获取负余额用户
	 * @author SAUL 090610
	 */
	function getUnderZeroUser( $aUserId = array() )
	{
	    if( is_array($aUserId) && !empty($aUserId) )
	    {
	        $sWhere = $this->oDB->CreateIn( $aUserId, ' ut.`userid` ' );
	    }
	    else 
	    {
	        $sWhere = ' uf.`channelbalance`<0 OR uf.`holdbalance`<0 OR (uf.`cashbalance`<0 AND ut.`parentid`!=0) ';
	    }
		$sSql = "SELECT uf.`cashbalance`,uf.`channelbalance`,uf.`availablebalance`, uf.`holdbalance`,
		         ut.`userid`,ut.`username`,ut.`isfrozen`, ut2.`username` AS `topname`
		         FROM `userfund` AS uf LEFT JOIN `usertree` as ut on (uf.`userid` = ut.`userid`)
                 LEFT JOIN `usertree` as ut2 on (ut.`lvtopid`=ut2.`userid`) 
                 WHERE $sWhere ORDER BY ut.`lvtopid`";
		return $this->oDB->getAll( $sSql );
	}
	
	
	
	/**
	 * 新增用户，用户继承直接上级的奖金组信息，（一代除外）
	 *
	 * @param int			$iUserId		// 新开户id
	 * @param int			$iPid			// 直接上级id
	 * 
	 * @version 	v1.0		2010-08-23
	 * @author 		louis
	 * @return 		mix				-1	// 用户为总代管理员或一代用户
	 * 								-2	// 写入usergroupset表失败
	 * 								-3	// usergroupset表中已经存在记录，但是用户所属不符
	 * 								-4	// 资金表中存在要添加的用户
	 * 								-5	// 写入userfund表失败
	 * 								-6	// 基础数据检查未通过
	 * 								-7 	// 写入usermethodset表失败
	 * 								-8	// 调用银行api写入userchannel表操作失败
	 * 
	 */
	public function syncPrizeGroup( $iUserId, $iPid ){
		// 数据检查
		if (!is_numeric($iUserId) || $iUserId <= 0 || !is_numeric($iPid) || $iPid <= 0){
			return -6;
		}
		/// 只有普通代理和用户才会采用同步奖金组操作
        $oChannelApi = new channelapi( 0, 'getUserInfo', TRUE );
        $oChannelApi->setTimeOut(10);            // 设置读取超时时间
        $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
        $oChannelApi->sendRequest( array("iUserId"=>$iUserId) );    // 发送结果集
        $aResult = $oChannelApi->getDatas();
        if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
        {//调用API失败
            return -1;
        }
        if (empty($aResult))							return -1;
        // 总代管理员或一代用户不采用此机制
        if ($aResult['data']['usertype'] == 2 || ($aResult['data']['usertype'] == 1 && $aResult['data']['lvproxyid'] == $iUserId))			return -1;
        // 用户组别信息数组
        $oGroup = new model_proxygroup();
        $aGroup = $oGroup->getGroupID($iPid);
        $aGroupInfo = array();
        if ($aResult['data']['usertype'] == 0){
        	$aGroupInfo['groupid'] = $aGroup[3];			// 用户
        }
        if ($aResult['data']['usertype'] == 1 && $aResult['data']['lvproxyid'] != $iUserId){
        	$aGroupInfo['groupid'] = $aGroup[2];			// 普通代理用户
        }
        
        $this->oDB->doTransaction(); // 事务开始
        // 首先检查usergroupset表中是否存在要写入的记录
        $sSql = "SELECT * FROM `usergroupset` WHERE `userid` = {$iUserId}";
        $aResult = $this->oDB->getOne( $sSql );
       	if (empty($aResult)){
       		// 写入usergroupset表
	        $aTempData = array('userid'=>$iUserId, 'groupid'=>$aGroupInfo['groupid']);
	        $aResult = $this->oDB->insert( 'usergroupset', $aTempData );
	        if( $aResult === false )
	        {//插入失败
	            $this->oDB->doRollback(); // 事务回滚
	            return -2;
	        }
       	} else {
       		if ($aGroupInfo['groupid'] != $aResult['groupid']){ // 存在记录，但是用户组信息与此次操作不符
       			$this->oDB->doRollback(); // 事务回滚
	            return -3;
       		}
       	}
        
        // 写入userfund表
       	// step 01   检查资金表中是否有要增加的用户，如果有回滚事务
       	$sSql = "SELECT COUNT(`entry`) AS `num` FROM `userfund` WHERE `userid` = {$iUserId}";
       	$iResult = $this->oDB->getOne( $sSql );
       	if ($iResult['num'] != 0){
       		$this->oDB->doRollback(); // 事务回滚
	        return -4;
       	}
       	
       	// step 02 写入userfund表
       	$aUserFund = array();
       	$aUserFund['userid'] = $iUserId;
       	$aUserFund['channelid'] = SYS_CHANNELID;
       	$aUserFund['cashbalance'] = 0;
       	$aUserFund['channelbalance'] = 0;
       	$aUserFund['availablebalance'] = 0;
       	$aUserFund['holdbalance'] = 0;
       	$aUserFund['islocked'] = 0;
       	$aUserFund['lastactivetime'] = date("Y-m-d H:i:s", time());
       	$aFundResult = $this->oDB->insert( 'userfund', $aUserFund );
        if( $aFundResult === false )
        {//插入失败
            $this->oDB->doRollback(); // 事务回滚
            return -5;
        }
        
        // 复制上级的奖金组信息，写入到usermthodset表
        // step 01 首先获取上级用户的奖金组信息
        $sSql = "SELECT * FROM `usermethodset` WHERE `userid` = {$iPid}";
        $aMethodSet = $this->oDB->getAll($sSql);
        if (!empty($aMethodSet)){ // 如果上级用户设置了奖金组信息，新用户则直接复制上级的奖金组信息
        	$sMethodSql = "INSERT INTO `usermethodset`(`userid`,`methodid`,`prizegroupid`,`userpoint`,`limitbonus`,`isclose`) VALUES";
        	foreach ($aMethodSet as $k => $v){
        		$sMethodSql .= "({$iUserId}, {$v['methodid']}, {$v['prizegroupid']}, 0, {$v['limitbonus']}, {$v['isclose']}),";
        	}
        	$sMethodSql = substr($sMethodSql, 0, -1);
        	// step 02 写入usermethodset表
        	$this->oDB->query($sMethodSql);
        	if ($this->oDB->errno() > 0){ // 写入失败
        		$this->oDB->doRollback(); // 事务回滚
            	return -7;
        	}
        }
        
        // 最后调用银行大厅api,向userchannel表中写入频道信息
        $oChannelApi = new channelapi( 0, 'activeUserChannel', TRUE );
        $oChannelApi->setTimeOut(10);            // 设置读取超时时间
        $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
        $oChannelApi->sendRequest( array("userid"=>$iUserId,"channelid"=>SYS_CHANNELID) );    // 发送结果集
        $aResult = $oChannelApi->getDatas();
        if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
        {//调用API激活失败
            $this->oDB->doRollback();	// 事务回滚
            return -8;
        } else {
        	$this->oDB->doCommit();		// 事务提交
        	return true;
        }
	}
}
?>