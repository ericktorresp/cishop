<?php
/**
 * 域名名称定义模型
 * 
 * 功能:
 * -- groupAdd          增加一个名称
 * -- groupCheck        检测名称是否存在
 * -- groupList			列表某名称下的所有域名, (带状态列表1 所有已分配的， 所有备用)
 * -- groupListbyUsr	
 * -- groupRename       域名改名
 * -- groupDel			删除一个名称
 * @author     Jim
 * 1/5/2011
 * @package    passportadmin
 */
class model_domaingroup extends basemodel 
{
	
	/*
	 * 系统参数  array[0]='sgroup' [1]'sdomain'
	 * 
     * @var array $aSysParam	
     */
    public $SysParam = array(0,0);
	
	/**
	 * 所有数据表名
	 *
	 * @var string
	 */
	private $TableName = 'domaingroup';

	
    /**
     * 增加一个域名组名称
     *
     * @param string $sGroupName 域名组名称
     * @return bool
     */
    public function groupAdd( $sGroupName ) 
    {
        if( empty($sGroupName) )
        {
            return FALSE;
        }
        else
        {
            if( $this->groupCheck( $sGroupName ) )
            {
                return FALSE;
            }
            else
            {
                $this->oDB->query("INSERT INTO `$this->TableName` (`bygroup`,`status`) VALUES ('".$sGroupName."','1')");
                return $this->oDB->insertId();
            }
            
        }
        
    }



    /**
     * 检测域名分组名是否存在
     *
     * @param  string $sDomainName  域名
     * @return BOOL   
     */
    public function groupCheck( $sGroupName )
    {
        $aResult = $this->oDB->getOne("SELECT `id` FROM `$this->TableName` WHERE `bygroup`='".$sGroupName."'" );
        if ( $aResult['id'] > 0 )
        {
        	return TRUE;
        }
        else 
        {
        	return FALSE;
        }
        
    }
    
    /**
     * 分配某个域名 $iDomain 为某个组$iDomainGroup
     *
     * @param array $aDomain
     * @param array $aDomainGroup
     * @param bool 	$bTransa 	FALSE 不开启事务, TRUE开启事务
     */
    public function groupBind($aDomain, $aDomainGroup, $bTransa=FALSE)
    {
    	if ( !is_array($aDomain) || !is_array($aDomainGroup) ) return FALSE;
    	if ($bTransa) $this->oDB->doTransaction();
    	
		foreach ( $aDomain AS $iDomain )
		{    	
			$iDomain = intval( $iDomain );
			$iDomainGroup = intval( $aDomainGroup[$iDomain] );
    		if ( !is_int($iDomain) || intval($iDomain) < 1 
    			|| !is_int($iDomainGroup) 
    			||  intval($iDomainGroup) < 1 ) 
    		{
    			if ($bTransa) $this->oDB->doRollback();
				return FALSE;
    		}
    		$sSql = "UPDATE `domains` set `bygroupid`=$iDomainGroup WHERE `id`=$iDomain";
    		$aResult = $this->oDB->query($sSql);
    		
    		if ( $aResult === FALSE || $this->oDB->errno() > 0)
    		{
    			if ($bTransa) $this->oDB->doRollback();
    			return FALSE;	
    		}
    	
		}
		
		if ($bTransa) $this->oDB->doCommit();
    	
		return TRUE;
    	 
    }


    /**
     * 根据域名组ID获取所辖的域名列表, 如果不提供域名组ID，则列表域名组
     *
     * @param array 	$aGroup
     * @param string 	$sType		第一参数的属性， 域名ID或域名组ID	 ()
     * @return array
     */
    public function groupList( $aGroup=array(), $sType='domain' )
    {
        if( is_array($aGroup) )
        {
            foreach( $aGroup as $iKey => $iDG )
            {
                if( !is_numeric($iDG) )
                {
                    unset($aGroup[$iKey]);
                }
            }
            
            if( count($aGroup) > 0 )
            {
            	if ( $sType == 'group')
            	{
            		$sSql = "SELECT `id`,`domain`,`bygroupid`,`status` FROM `domains` WHERE `bygroupid` IN (".join(',', $aGroup).")";
            	}
            	else 
            	{
            		$sSql = "SELECT `id`,`domain`,`bygroupid`,`status` FROM `domains` WHERE `id` IN (".join(',', $aGroup).")";
            	}
            	return $this->oDB->getAll($sSql);
            }
            else 
            {
                return $this->oDB->getAll("SELECT `id`,`bygroup`,`status` FROM `$this->TableName`");
            }
            
        }
        else 
        {
            return array();
        }
    }

    
    /**
     * 获取域名所在的分组ID
     *			-- 通常用法：传入一个数组形式域名ID，返回一个数组形式的分组ID
     * @param int/array	$iDomain		请求的域名ID/或域名ID组
     * @param array 	$aDomainGroup	供搜索的分组列表(对单一域名进行查找的时候可不使用该参数)
     * @return int
     */
   	public function GetGroupbyDomain( $iDomain, $aDomainGroup=array() )
    {
    	if ( is_array($iDomain) )
    	{
    		if ( count($iDomain) < 1 ) return FALSE;
    		$aDomainGroup = empty($aDomainGroup) ? $this->groupList( $iDomain ) : '';
    	}
    	else 
    	{
    		$iDomain = intval($iDomain);
    		if ( $iDomain < 1 ) return FALSE;
    		$aDomainGroup = empty($aDomainGroup) ?  $this->groupList( array($iDomain) ) : '';
    	}
    	
    	$aReturn = array();
    	foreach ( $aDomainGroup AS $aDG)
    	{
    		if ( array_search($aDG['id'], $iDomain) !== FALSE )
    			$aReturn[] = $aDG['bygroupid'];
    	}
    	
    	return array_unique($aReturn);
    	
    }
    
    /**
     * 整理搜索数组
     * 
     * @param array 	$aArray	需整理的数组
     * @param string	$sKey	整理的KEY名
     *
     * @return array
     */
    public function getGroupArray( $aArray , $sKey='bygroupid' )
    {
    	if ( !is_array($aArray) || count($aArray) == 0 ) return  FALSE;
    	
    	$aReturn = array();
    	
    	foreach ( $aArray AS $aA )
    	{
    		$aReturn[] = $aA[$sKey];	
    	}
    	
    	return array_unique($aReturn);

    }
    
    
    /**
     * 数组对比, 查询 array1 是否在 array2 中, 全等或子集
     *	
     * @param array $aArray1 	(一维数组)
     * @param array $aArray2	(二维数组)
     * @param int	$iKey1		$aArray1 的keyID
     * @param array	$aSearchArray 附加数组
     * 
     * @return bool 全等或子集情况下返回 冲突的总代ID数组, 否则返回有
     */
    public function diffArray($aArray1, $aArray2, $iKey1, $aSearchArray=array() )
    {
    	// 引用系统参数变量 是否打开此项对比
    		// sysparam:同组域名不能绑给同一总代
		//  $sSysGroup 	= $this->SysParam[0];
    		// sysparam:总代绑定的域名不能互为子集
    	if ( $this->SysParam[1] == 0) return FALSE;
    	
    	$aReturn = array();
    	foreach ( $aArray2 as $iKey => $aA2) {
    			// 排除与自己比较
    			if ( $iKey1 == $iKey ) continue;
    			// 是否类似
    			if ( array_values($aA2) == array_values($aArray1) ) 
    			{
    				$aReturn[] = $aSearchArray[$iKey];
    			}
    			// 差集
    			$aTempDiff = array_diff($aArray1, $aA2);
    			// 差集必须在 $aArray1 中，否则array1是array2的子集
    			if ( ! array_intersect($aTempDiff, $aArray1)  ) 
    			{
    				$aReturn[] = $aSearchArray[$iKey];
    			}
    	}
    	
    	if ( count($aReturn) == 0 ) return FALSE;
    	
    	return $aReturn;
    }
    
    /**
     * 全部总代ID的数组
     *
     */
    public function _allAgentList()
    {
    	$sSql = "select `userid`,`username` FROM  `usertree`  WHERE `usertype`=1 and `parentid`=0";
    	$aResult = $this->oDB->getAll( $sSql );
    	if ( $this->oDB->errno() > 0 ) return FALSE;
    	
    	$aReturn = array();
    	foreach ($aResult AS $aR)
    	{
    		$aReturn[$aR['userid']]=$aR['username'];	
    	}
    	if ( count($aReturn) > 0) 
    	{
    		return $aReturn;
    	}
    	else
    	{
    		return FALSE;
    	}
    }
    
    

    /**
     * 删除一个域名 (清除已分配的域名组信息,物理删除一个域名组名称)
     * @param array $aGroupId
     * @return BOOL
     */
    public function groupDel( $aGroupId )
    {
        if( empty($aGroupId) || !is_array($aGroupId) ) return FALSE;
        
        /*foreach( $aGroupId as $iKey => $iDomainId )
        {
            if( !is_numeric($iGroupId) ) unset($aGroupId[$iKey]);
            
        }*/
        
        if( count($aGroupId) > 0 ) 
        {
        	$this->oDB->doTransaction();
            $this->oDB->query("UPDATE `domains` set `bygroupid`=0 WHERE `bygroupid` IN (".join(',', $aGroupId).")");
        	if ( $this->oDB->errno() > 0 )
            {
            	$this->oDB->doRollback();
            	return FALSE;
            }
            $this->oDB->query("DELETE FROM `$this->TableName` WHERE `id` IN (".join(',', $aGroupId).")");
        	if ( $this->oDB->errno() > 0 )
            {
            	$this->oDB->doRollback();
            	return FALSE;
            }
            $this->oDB->doCommit();			
            return TRUE;
        }
        
        return FALSE;
    }


    /**
     * 域名组名称 改名
     *
     * @param  int    $iGroupId 域名ID
     * @param  string $sGroupName  域名
     * @return BOOL
     */
    public function groupRename( $iGroupId , $sGroupName )
    {
        if( $this->groupCheck( $sGroupName ) )
        { 
            $this->oDB->query("UPDATE `$this->TableName` SET `bygroup` = '".daddslashes($sGroupName)."' WHERE `id`='".intval($iGroupId)."' LIMIT 1");
            if ( $this->oDB->errno() > 0 )
            {
            	return FALSE;
            }
            else 
            {
            	return TRUE;
            }
            
        }
        else
        {
        	return FALSE;
        }
        
    }


}
?>