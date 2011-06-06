<?php
/**
 * 用户分组数据模型
 *
 * 功能：
 *      用户分组的一些常用操作的封装
 *      CRUD
 *      --insert                增加一个用户组
 *      --delete                删除用户组
 *      --update                修改用户组
 *      --getById               根据ID读取用户组信息
 *      --getOne                根据自定义条件取一条记录
 *      --getList               根据自定义条件取用户组列表
 *
 *      --getGroupID            根据用户ID，获取其组的团队下面的总代组ID，一代组ID，普代组ID，会员组ID
 *      --getGroupByUser        根据用户ID，获取属于他的组列表
 * 
 * @author  james
 * @version 1.1.0
 * @package passport
 */

class model_usergroup extends basemodel 
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
     * @param   int     $iTeamId        //所数团队ID
     * @param   string  $sMenustr       //权限菜单字符串
     * @param   int     $iIsSpecial     //是否为特别权限组
     * @param   int     $iIsDisabled    //是否关闭
     * @return  mixed   //成功返回insert id ，失败返回FALSE
     */
    public function insertUserGroup( $sGroupName, $iTeamId = 0, $sMenustr = '', $iIsSpecial = 0, $iIsDisabled = 0 )
    {
        //数据检查
        if( empty($sGroupName) )
        {
            return FALSE;
        }
        $aData = array(
                        'groupname'  => $sGroupName,
                        'teamid'     => intval($iTeamId),
                        'menustrs'   => $sMenustr,
                        'isspecial'  => intval($iIsSpecial),
                        'isdisabled' => intval($iIsDisabled)	
                    );
        $this->oDB->doTransaction();
        $iUserGroupId = $this->oDB->insert( 'usergroup', $aData );
        if( $iUserGroupId<=0 )
        {
        	$this->oDB->doRollback();
        	return false;
        }
        //接下来对用户特殊权限复制
        $aUserGroup = $this->oDB->getAll("SELECT DISTINCT `teamid` from `usergroup` where `isspecial`>0");
        foreach($aUserGroup as $iUserGroup)
        {
        	$aData["teamid"] = $iUserGroup["teamid"];
        	$aData["isspecial"] = $iUserGroupId;
        	if($this->oDB->insert( 'usergroup', $aData )===FALSE)
        	{
        		$this->oDB->doRollback();
                return false;
        	}
        }
        $this->oDB->doCommit();
        return TRUE;
    }



    /**
     * 删除用户组
     * 
     * @access  public
     * @author  james
     * @param   string  $sWhereSql  //删除条件，默认删除所有
     * @return  mixed   //成功返回影响的行数，失败返回FALSE
     */
    public function delete( $sWhereSql = '1' )
    {
    	$sqlUserGroup = "select * from `userchannel` AS UC"
    	." LEFT JOIN `users` AS U on (UC.`userid`=U.`userid`) where U.`usertype`<'2'"
    	."and UC.`groupid` in( SELECT `groupid` from `usergroup` where ".$sWhereSql.")";
    	$aUserTeam = $this->oDB->getOne($sqlUserGroup);
    	if( !empty($aUserTeam) )
    	{
    		return FALSE;
    	}
        return $this->oDB->delete( 'usergroup', $sWhereSql );
    }



    /**
     * 修改用户组
     * 
     * @access  public
     * @author  james
     * @param   array   $aGroupInfo //要修改的组信息，键名和数据库字段对应
     * @param   string  $sWhereSql  //修改条件，默认全部修改
     * @return  mixed   //失败返回FALSE，成功返回影响的行数
     */
    public function update( $aGroupInfo, $sWhereSql = '1' )
    {
        if( !is_array($aGroupInfo) || empty($aGroupInfo) )
        {
            return FALSE;
        }
        return $this->oDB->update( 'usergroup', $aGroupInfo, $sWhereSql );
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
                $v = '`'.$v.'`';
            }
            $sSql = "SELECT " . implode(',',$aGroupInfo);
        }
        else
        {
            $sSql = "SELECT * ";
        }
        $sSql .= " FROM `usergroup` WHERE `groupid`='". intval($iGroupId) ."'";
        return $this->oDB->getOne( $sSql );
    }



/**
 * 根据自定义条件取一条记录
 * 
 * @access  public
 * @author  james
 * @param   array   $aGroupInfo //要取的字段数组
 * @param   string  $sWhereSql  //自定义条件 WHERE后面的条件
 * @return  mixed   //成功返回取得的记录集，失败返回FALSE
 */
public function getOne( $aGroupInfo = array(), $sWhereSql = '' )
{
    if( is_array($aGroupInfo) && !empty($aGroupInfo) )
    {//自定义要取的字段信息
        foreach( $aGroupInfo as &$v )
        {
            $v = '`'.$v.'`';
        }
        $sFields = implode(',',$aGroupInfo);
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
    $sSql = "SELECT ". $sFields ." FROM `usergroup` ". $sWhereSql;
    return $this->oDB->getOne( $sSql );
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
            $sFields = implode( ',', $aGroupInfo );
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
        $sSql = "SELECT ". $sFields ." FROM `usergroup` ". $sWhereSql;
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 根据用户ID，获取其组的团队下面的总代组ID，一代组ID，普代组ID，会员组ID，这里不获取总代管理员组的
     * 
     * @access  public
     * @author  james
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
        $sSql = " SELECT g.`groupid`,g.`teamid` 
                FROM `usergroup` AS g LEFT JOIN `userchannel` AS uc ON g.`groupid`=uc.`groupid` 
                WHERE uc.`userid`='".intval($iUserId)."' AND uc.`channelid`='0' ";
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



    /**
     * 根据用户ID，获取属于他的组列表
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   array   $aGroupInfo //要获取的组信息，支持*及全部信息
     * @param   boolean $bIsDisabled//是否读取未启用的，TRUE：读取，FALSE：不读取
     * @return  array   //用户组信息，失败返回FALSE
     */
    public function getGroupByUser( $iUserId, $aGroupInfo = array('groupid', 'groupname'), $bIsDisabled = FALSE )
    {
        if( empty($iUserId) )
        {
            return FALSE;
        }
        //获取用户的组信息
        $sSql = " SELECT g.`groupid`,g.`teamid` 
                FROM `usergroup` AS g LEFT JOIN `userchannel` AS uc ON g.`groupid`=uc.`groupid` 
                WHERE uc.`userid`='".intval($iUserId)."' AND uc.`channelid`='0' ";
        $aResult = $this->oDB->getOne( $sSql );
        if( empty($aResult) )
        {
            return FALSE;
        }
        $sWhere = '';
        if( $aResult['teamid'] == 0 )
        {//默认组
            $sWhere = " teamid='0' AND `groupid`>='".$aResult['groupid']."' ";
        }
        else
        {//特殊组+默认组
            $sWhere = " (teamid='0' OR teamid='".intval($aResult['teamid'])."') 
                        AND `groupid`>='".$aResult['isspecial']."' ";
        }
        if( empty($aGroupInfo) || !is_array($aGroupInfo) )
        {
            $sFields = ' * ';
        }
        else 
        {
            foreach( $aGroupInfo as &$v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode( ',', $aGroupInfo );
        }
        if( !(bool)$bIsDisabled )
        {
            $sWhere .= " AND `isdisabled`='0' "; 
        }
        $sSql = "SELECT ".$sFields." FROM `usergroup` WHERE ".$sWhere;
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 复制用户组别
     *
     * @param integer $iGroupId
     */
    function usergroupcopy( $iGroupId )
    {
        if(!is_numeric($iGroupId)||$iGroupId==0)
        {
        	return false;
        }
        else
        {
        	$aGroup = $this->getById($iGroupId,"");
        	if(empty($aGroup))
        	{
        		return false;
        	}
        	$aTmpGroup = $this->oDB->getOne("SELECT max(`teamid`) AS `TOM_COUNT` FROM `usergroup`");
        	$iUserGroupId = $aTmpGroup["TOM_COUNT"] + 1;
        	if( $aGroup["isspecial"]>0 )
        	{
        	   return $this->oDB->query("insert into `usergroup` (`groupname`,`teamid`,`menustrs`,`isspecial`,`isdisabled`)"
        	   ." select `groupname`,".$iUserGroupId.",`menustrs`,`isspecial`,`isdisabled` from `usergroup`"
        	   ." where `teamid`='".$aGroup["teamid"]."'");
        	}
        	else
        	{
        		return $this->oDB->query("insert into `usergroup` (`groupname`,`teamid`,`menustrs`,`isspecial`,`isdisabled`)"
               ." select `groupname`,".$iUserGroupId.",`menustrs`,`groupid`,`isdisabled` from `usergroup`"
               ." where `teamid`='".$aGroup["teamid"]."'");
            }        	
        }
    }
}
?>