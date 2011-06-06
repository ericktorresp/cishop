<?php
/**
 * 用户组别模型
 * 
 * 功能：
 *      1.获取用户组列表[getUserGroupList]
 *      2.分配用户组[assignUserGroup]
 *      3.根据用户ID获取用户组别信息[getUserGroup]
 *      4.添加用户组别信息[addGroup]
 *      5.修改用户组[update]
 *      6.复制用户组别[usergroupcopy]
 *      7.删除用户组别[delete]
 * @author	  mark
 * @version  1.0.0
 * @package  highadmin
 * @since    2010-01-20
 */
class model_usergroup extends basemodel 
{
    /**
     * 获取用户组别列表
     * @param  string  $sCondition 查询条件
     * @param  string  $sFiled     查询字段
     * @param  string  $sGroupBy   分组
     * @param  string  $sOrderBy   排序
     * @return array
     * @author mark
     */
    public function getUserGroupList( $sFiled = '*', $sCondition = '1', $sGroupBy = '', $sOrderBy = '')
    {
        $sCondition = isset($sCondition) && $sCondition != '' ? $sCondition : '1';
        $sFiled     = isset($sFiled) && $sFiled != '' ? $sFiled : '*';
        $sGroupBy   = isset($sGroupBy) && $sGroupBy != '' ? $sGroupBy : '';
        $sOrderBy   = isset($sOrderBy) && $sOrderBy != '' ? $sOrderBy : '';
        $sSql = " SELECT ". $sFiled . " FROM `usergroup` WHERE " . $sCondition . $sGroupBy . $sOrderBy;
        return $this->oDB->getAll( $sSql );
    }
    
    /**
     * 分配用户组
     * @author mark
     * @param $iTeamId 分配的用户组ID
     * @param $iUserId 需要分配的用户ID
     * @return boolean
     */
    public function assignUserGroup( $iUserId = 0, $sGroupId = 0)
    {
        $iUserId = isset($iUserId) && $iUserId != '' ? intval($iUserId) : 0;
        if( $iUserId <= 0 )
        {
            return FALSE;
        }
        if (empty($sGroupId)){
        	return FALSE;
        }
        $aTemp = explode('#', $sGroupId);
        $iGroupId = intval($aTemp[0]);
        $iTeamId = intval($aTemp[1]);
        $iGroupId = isset($iGroupId) && $iGroupId != '' ? intval($iGroupId) : 0;
        $sSql = "SELECT * FROM `usergroupset` WHERE `userid` = " . $iUserId . ' LIMIT 1';
        $aUser = $this->oDB->getOne($sSql);
        if(empty($aUser))
        {
            $aUserGroupData = array(
                                    "userid"  => $iUserId,
                                    "groupid" => $iGroupId
                              );
            $this->oDB->insert( 'usergroupset', $aUserGroupData );
        }
        else 
        {
            /*$sSql = "UPDATE `usergroupset` SET `groupid` = " . $iGroupId . " WHERE `userid` = " . $iUserId;
            $this->oDB->query($sSql);*/
            /** louis 修改总代组别后，总代下级也应跟随总代做相应改变 **/
            $aUserCheck = $this->oDB->getOne("SELECT ugs.`groupid`,ug.`teamid` FROM `usergroupset` AS ugs "
	        ."left join `usertree` AS ut on (ut.`userid`=ugs.`userid`)"
	        ."left join `usergroup` AS ug on (ug.`groupid`=ugs.`groupid`)"
	        ." WHERE ugs.`userid`='".$iUserId."' AND ut.`parentid`='0'");
	        if(empty($aUserCheck))
	        { //旧的总代用户信息
	            return false;
	        }
	        $aTeamCheck = $this->oDB->getOne("SELECT * FROM `usergroup` "
	        ."where `teamid`='".$iTeamId."'and (`isspecial`='1' or `groupid`='1')");
	        if(empty($aTeamCheck))
	        {
	            return false;
	        }
	        if( $aUserCheck["groupid"] == $aTeamCheck["groupid"] )
	        { //原本就相等
	            return TRUE;
	        }
	        //获取原来所有的相关Old Key=> New Key 
	        $aTeam = $this->oDB->getAll("SELECT groupid,teamid,isspecial FROM `usergroup`"
	        ." where `teamid` IN (SELECT `teamid` FROM `usergroup` where `groupid`"
	        ." in (".$aTeamCheck["groupid"].",".$aUserCheck["groupid"]."))");
	        //数据整理
	        $aTranTeam = array();
	        foreach( $aTeam as $team )
	        {
	            $i = ($team["isspecial"]==0)?$team["groupid"]:$team["isspecial"];
	            $aTranTeam[$team["teamid"]][$i] = $team["groupid"];
	        }
	        $this->oDB->doTransaction();
	        foreach($aTranTeam[$aUserCheck["teamid"]] as $i=>$v)
	        {
	            $this->oDB->query("UPDATE `usergroupset` AS ugs "
	            ."LEFT JOIN `usertree` AS ut ON (ut.`userid`=ugs.`userid`)"
	            ." SET ugs.`groupid`='".$aTranTeam[$iTeamId][$i]."'"
	            ." WHERE ugs.`groupid`='".$v."' and ut.`usertype`<'2'"
	            ." AND ut.`lvtopid`='".$iUserId."'");
	            if($this->oDB->errno()>0)
	            {
	                $this->oDB->doRollback();
	                return false;
	            }
	        }
	        if( $this->oDB->doCommit() )
	        {
	            return true;
	        }
	        else
	        {
	            return false;
	        }
        }
        if( $this->oDB->error() > 0 )
        {
            return FALSE;
        }
        if ($this->oDB->ar() == 0 ) {
            return FALSE;
        }
        return TRUE;
    }
    
    
    /**
     * 根据用户ID获取用户组别信息
     * @author mark
     * @param int $iUseId 用户ID
     * @return array
     */
    public function getUserGroup( $iUserId = 0 )
    {
        $iUserId = isset($iUserId) && $iUserId != '' ? intval($iUserId) : 0;
        if( $iUserId <= 0 )
        {
            return FALSE;
        }
        $sSql = "SELECT ugs.*,ug.`groupname`,ug.`isspecial`
                 FROM `usergroupset` AS ugs
                 LEFT JOIN `usergroup` AS ug ON (ugs.`groupid` = ug.`groupid`)  
                 WHERE ugs.`userid` = " . $iUserId . ' LIMIT 1';
        return $this->oDB->getOne($sSql);
    }
    
    
    /**
     * 添加用户组别信息
     * @author mark
     * @param  array $aGroupData 用户组别信息
     * @return mixed
     */
    public function addGroup( $aGroupData = array() )
    {
        if( !is_array($aGroupData) || empty($aGroupData) )
        {
            return -1;
        }
        if( $aGroupData['groupname'] == '' )
        {
            return -2;
        }
        $aInsertData = array();
        $aInsertData['groupname'] = daddslashes($aGroupData['groupname']);
        if( !is_array($aGroupData['menu']) )
        {
            return  -3;
        }
        $aInsertData['menustrs'] = implode( ',', $aGroupData['menu'] );
        $sSql = " SELECT * FROM `usergroup` WHERE `groupname` = '" . $aGroupData['groupname'] . 
                  "' OR `menustrs` = '" .$aInsertData['menustrs']. "'";
        $aResult = $this->oDB->getAll($sSql);
        if(!empty($aResult))
        {
            return -4;
        }
        $this->oDB->doTransaction();
        $iUserGroupId = $this->oDB->insert( 'usergroup', $aInsertData );
        if( $iUserGroupId <= 0 )
        {
            $this->oDB->doRollback();
            return -5;
        }
        //接下来对用户特殊权限复制
        $aUserGroup = $this->oDB->getAll("SELECT DISTINCT `teamid` FROM `usergroup` WHERE `isspecial`>0");
        foreach( $aUserGroup as $aGroupTeam )
        {
            $aInsertData["teamid"]    = $aGroupTeam["teamid"];
            $aInsertData["isspecial"] = $iUserGroupId;
            if($this->oDB->insert( 'usergroup', $aInsertData ) === FALSE)
            {
                $this->oDB->doRollback();
                return -6;
            }
        }
        $this->oDB->doCommit();
        return TRUE;
    }
    
    
    /**
     * 修改用户组
     * 
     * @access  public
     * @author  mark
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
     * 复制用户组别
     * @author mark
     * @param integer $iGroupId
     * @return boolean
     */
    function usergroupcopy( $iGroupId )
    {
        if( !is_numeric($iGroupId) || $iGroupId == 0)
        {
        	return FALSE;
        }
        else
        {
        	$aGroup = $this->getUserGroupList( "*", "`groupid` = '" . $iGroupId ."'");
        	if( empty($aGroup) )
        	{
        		return FALSE;
        	}
        	$aTmpGroup = $this->oDB->getOne("SELECT max(`teamid`) AS `TOM_COUNT` FROM `usergroup` LIMIT 1");
        	$iUserGroupId = $aTmpGroup["TOM_COUNT"] + 1;
        	if( $aGroup[0]["isspecial"] > 0 )
        	{
        	   return $this->oDB->query("INSERT INTO `usergroup` (`groupname`,`teamid`,`menustrs`,`isspecial`,`isdisabled`)"
        	   ." SELECT `groupname`,".$iUserGroupId.",`menustrs`,`isspecial`,`isdisabled` FROM `usergroup`"
        	   ." WHERE `teamid`='".$aGroup[0]["teamid"]."'");
        	}
        	else
        	{
        		return $this->oDB->query("INSERT INTO `usergroup` (`groupname`,`teamid`,`menustrs`,`isspecial`,`isdisabled`)"
               ." SELECT `groupname`,".$iUserGroupId.",`menustrs`,`groupid`,`isdisabled` FROM `usergroup`"
               ." WHERE `teamid`='".$aGroup[0]["teamid"]."'");
            }        	
        }
    }
    
    
    /**
     * 删除用户组别
     * 
     * @access  public
     * @author  mark
     * @param   string  $sWhereSql  //删除条件，默认删除所有
     * @return  mixed   //成功返回影响的行数，失败返回FALSE
     */
    public function delete( $sWhereSql = '1' )
    {
        $sWhereSql = isset($sWhereSql) && $sWhereSql != '' ? $sWhereSql : "1";
        return $this->oDB->delete( 'usergroup', $sWhereSql );
    }
}
?>