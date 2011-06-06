<?php
/**
 * 用户频道关系数据模型
 *
 * 功能：
 *      对用户和频道之间的关系的操作进行封装
 *      CRUD
 *      --insert                增加用户和频道的对应关系
 *      --deleteByUserId        删除用户和某频道的对应关系
 *      --delete                根据条件删除筛选的对应关系
 *      --update                修改用户和频道的对应关系
 *      --getOne                根据自定义条件查询一条记录
 *      --getList               根据自定义条件查询列表
 *      --isExists              检测用户和一个频道是否已存在对应关系
 * 
 * @author	james
 * @version 1.0.0
 * @package	passport
 * @since 	2009/04/30
 */

class model_userchannel extends basemodel 
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
     * 增加用户和频道的对应关系
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId        //用户ID
     * @param   int     $iChannelId     //频道ID 
     * @param   int     $iGroupId       //用户组ID
     * @param   string  $sExtendMenustr //用户扩展权限菜单
     * @param   int     $iStatus        //状态
     * @param   bool    $bIsSync        //是否同步开通总代管理员
     * @return  mixed   //成功返回insert id，失败返回FALSE,已存在对应关系返回 -1
     */
    public function insert( $iUserId, $iChannelId = 0, $iGroupId = 0, $sExtendMenustr = '', $iStatus = 0, $bIsSync = false )
    {
        /*数据检测和自动填充*/
        if( empty($iUserId) )
        {
            return FALSE;
        }
        //检测是否在给定的频道了开通帐户
        if( $this->isExists($iUserId, $iChannelId) )
        {
            return -1;
        }
        $aData           = array();
        $aData['userid'] = intval( $iUserId );
        if( intval($iChannelId) < 0 )
        {
            $iChannelId = 0;
        }
        $aData['channelid']     = intval( $iChannelId );
        $aData['groupid']       = intval( $iGroupId );
        $aData['extendmenustr'] = $sExtendMenustr;
        if( (bool)$iStatus )
        {
            $aData['isdisabled']    = 1;
        }
        else
        {
            $aData['isdisabled']    = 0;
        }
//  temp_louis      return $this->oDB->insert( 'userchannel', $aData );
		/** temp_louis **/
        if ($bIsSync === false){
        	return $this->oDB->insert( 'userchannel', $aData );
        } else {
        	$this->oDB->doTransaction(); // 事务开始
        	$mResult = $this->oDB->insert( 'userchannel', $aData );
        	if ($mResult === false){
        		$this->oDB->doRollback(); // 事务回滚
        		return false;
        	}
        	// 循环开通总代下的总代管理员
        	$oUser = A::singleton('model_user');
        	$aResult = $oUser->getAdminList($iUserId);
        	$aTmpData = array();
        	if (!empty($aResult)){
        		foreach ($aResult as $k => $v){
        			$aTmpData['userid'] = $v['userid'];
        			$aTmpData['channelid'] = intval( $iChannelId );
        			$aTmpData['groupid'] = 0; // 总代管理员的groupid默认为会员groupid
        			$aTmpData['isdisabled'] = $aData['isdisabled'];
        			if ($this->oDB->insert( 'userchannel', $aTmpData ) === false){
        				$this->oDB->doRollback(); // 事务回滚
        				return false;
        			}
        		}
        	}
        	$this->oDB->doCommit(); // 事务提交
        	return $mResult;
        }
        /** temp_louis **/
    }



    /**
     * 删除用户和某频道的对应关系
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //要删除帐户的ID
     * @param   int     $iChannelId //频道ID
     * @return  mixed   //成功返回所影响所影响的行数，失败返回FALSE
     */
    public function deleteByUserId( $iUserId, $iChannelId = 0 )
    {
        if( empty($iUserId) )
        {
            return FALSE;
        }
        if( (bool)$iChannelId )
        {
            $iChannelId = intval($iChannelId);
        }
        else
        {
            $iChannelId = 0;
        }
        return $this->oDB->delete( 'userchannel', " `userid`='". intval($iUserId) ."'
                                     AND `channelid`='". intval($iChannelId) ."'" );
    }



    /**
     * 根据条件删除筛选的对应关系
     * 
     * @access  public
     * @author  james
     * @param   string  $sWhereSql  //删除条件,默认全部删除，清空所有的对应关系
     * @return  mixed   //成功返回所影响所影响的行数，失败返回FALSE
     */
    public function delete( $sWhereSql = '1' )
    {
        return $this->oDB->delete( 'userchannel', $sWhereSql );
    }



    /**
     * 修改用户和频道的对应关系
     * 
     * @access  public
     * @author  james
     * @param   array   $aInfo      //要修改的信息
     * @param   string  $sWhereSql  //要修改的筛选条件[不包含where关键字]，默认为全部修改
     * @return  mixed   //成功返回所影响所影响的行数，失败返回FALSE	
     */
    public function update( $aInfo = array(), $sWhereSql = '1' )
    {
        if( !is_array($aInfo) || empty($aInfo) )
        {
            return FALSE;
        }
        if( isset($aInfo['isdisabled']) )
        {
            $aInfo['isdisabled'] = (bool)$aInfo['isdisabled'] ? 1 : 0;
        }
        return $this->oDB->update( 'userchannel', $aInfo, $sWhereSql );
    }

    
    /**
     * 启用/禁用总代频道
     *
     * @access  public
     * @param   int       $iUserId      用户Id
     * @param   int       $iChannelId   频道Id
     * @param   boolean   $bIsOpen      是否启用
     * @param   boolean   $bIsSync      是否同步启/禁用总代管理员
     * @return  boolean
     */
    public function openCloseTopProxyUserChannel( $iUserId, $iChannelId, $bIsOpen = FALSE, $bIsSync = false )
    {
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {
            return FALSE;
        }
        /** temp_louis **/
        if ($bIsSync === true){
        	$iIdList = "";
        	// 取出总代管理员列表
        	$oUser = A::singleton('model_user');
        	$aResult = $oUser->getAdminList($iUserId);
        	if (!empty($aResult)){
        		foreach ($aResult as $k => $v){
        			$iIdList .= $v['userid'] . ',';
        		}
        	}
        	$iUserId = $iUserId . ',' . $iIdList;
        	$iUserId = substr($iIdList, 0, -1);
        } else {
        	$iUserId = intval($iUserId);
        }
        /** temp_louis **/
        
// temp_louis        $iUserId = intval($iUserId);
        if( empty($iChannelId) || !is_numeric($iChannelId) || $iChannelId <= 0 )
        {
            return FALSE;
        }
        $iChannelId = intval($iChannelId);
        $bIsOpen    = $bIsOpen == TRUE ? 1 : 0;
        /* temp_louis $sSql = " UPDATE `userchannel` AS u, `usertree` AS ut SET u.`isdisabled`='".$bIsOpen."' 
                  WHERE u.`isdisabled`='".($bIsOpen == 1 ? 0 : 1)."' AND u.`channelid`='".$iChannelId."'
                  AND u.`userid`=ut.`userid` 
                  AND (ut.`userid`='".$iUserId."' OR ut.`lvtopid`='".$iUserId."' OR ut.`parentid`='".$iUserId."') ";*/
        $sSql = " UPDATE `userchannel` AS u, `usertree` AS ut SET u.`isdisabled`='".$bIsOpen."' 
                  WHERE u.`isdisabled`='".($bIsOpen == 1 ? 0 : 1)."' AND u.`channelid`='".$iChannelId."'
                  AND u.`userid`=ut.`userid` 
                  AND (ut.`userid` in (".$iUserId.") OR ut.`lvtopid`='".$iUserId."' OR ut.`parentid`='".$iUserId."') ";
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 || $this->oDB->ar() < 1 )
        {
            return FALSE;
        }
        return TRUE;
    }


    /**
     * 根据自定义条件查询一条记录
     * 
     * @access  public
     * @author  james
     * @param   array   $aInfo      //要取的对应关系信息中的字段数组
     * @param   string  $sWhereSql  //Where 条件[不包括where]
     * @return  mixed   //成功返回一个对应信息，失败返回FALSE
     */
    public function getOne( $aInfo = array(), $sWhereSql = '' )
    {
        if( is_array($aInfo) && !empty($aInfo) )
        {//自定义要取的字段信息
            foreach( $aInfo as &$v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode( ',', $aInfo );
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
        $sSql = "SELECT ". $sFields ." FROM `userchannel` ". $sWhereSql;
        return $this->oDB->getOne( $sSql );
    }



    /**
     * 根据自定义条件查询列表
     * 
     * @access  public
     * @author  james
     * @param   array   $aInfo      //要取的对应关系信息中的字段数组
     * @param   string  $sWhereSql  //Where 条件[不包括where]
     * @return  mixed   //成功返回对应信息列表，失败返回FALSE
     */
    public function getList( $aInfo = array(), $sWhereSql = '' )
    {
        if( is_array($aInfo) && !empty($aInfo) )
        {//自定义要取的字段信息
            foreach( $aInfo as &$v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode(',', $aInfo);
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
        $sSql = "SELECT ". $sFields ." FROM `userchannel` ". $sWhereSql;
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 检测用户和一个频道是否已存在对应关系
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   int     $iChannelId //频道ID
     * @return  //存在返回TRUE，不存在返回FALSE
     */
    public function isExists( $iUserId, $iChannelId = 0 )
    {
        if( empty($iUserId) )
        {
            return FALSE;
        }
        $iUserId = intval( $iUserId );
        if( (bool)$iChannelId )
        {
            $iChannelId = intval( $iChannelId );
        }
        else
        {
            $iChannelId = 0;
        }
        $sSql = "SELECT `entry` FROM `userchannel` WHERE `userid`='".$iUserId."' AND `channelid`='".$iChannelId."' ";
        $this->oDB->query( $sSql );
        unset($sSql);
        if( $this->oDB->ar() > 0 )
        {//存在记录集
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 获取用户可以玩的频道列表
     */
    public function & getUserChannelList( $iUserId )
    {
        $aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return $aResult;
        }
        $sSql = " SELECT c.`path`,c.`channel`,c.`id`,c.`usergroups` FROM `channels` AS c "
                 . " LEFT JOIN `userchannel` AS uc ON uc.`channelid`=c.`id` " 
                 . " WHERE uc.`isdisabled`='0' AND c.`isdisabled`='0' AND uc.`userid`='" .$iUserId. "'";
        $aResult = $this->oDB->getAll( $sSql );
        if( empty($aResult) )
        {
            return $aResult;
        }
        $aTempArr = array();
        $aTempId  = array();
        foreach( $aResult as $v )
        {
            $aTempArr[0][$v['id']] = $v;
            $aTempId[] = $v['id'];
        }
        if( empty($aTempId) )
        { // 对空数组的处理, 防止 implode 出错
            $aTempId = array('0');
        }
        $sSql = "SELECT `path`,`channel`,`id`,`pid`,`usergroups` FROM `channels` "
                 ." WHERE `isdisabled`='0' AND `pid` IN(".implode(",",$aTempId).") ";
        $aResult = $this->oDB->getAll( $sSql );
        foreach( $aResult as $v )
        {
            $aTempArr[$v['pid']][$v['id']] = $v;
        }
        $aResult = $aTempArr;
        unset($aTempArr);
        return $aResult;
    }
    
    
    /**
     * TODO _a高频、低频并行前期临时程序[获取高频可用域名]
     *
     * @param int    $iUserId
     */
    /*public function getGPUserDomain( $iUserId )
    {
    	$aResult = array( "yx"=>"", "dl"=>"" );
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return $aResult;
        }
        $iUserId = intval($iUserId);
        $sSql = "SELECT tum.`dpuserdomain`,tum.`gpuserdomain` 
                FROM `tempusermap` AS tum LEFT JOIN `usertree` AS ut ON tum.`dpuserid`=ut.`lvtopid` 
                WHERE ut.`userid`='".$iUserId."' AND ut.`usertype`!='2' AND ut.`isdeleted`='0' LIMIT 1";
        $aTempResult = $this->oDB->getOne( $sSql );
        if( empty($aTempResult) )
        {
        	return $aResult;
        }
        $aTemp = explode( ",", $aTempResult['dpuserdomain'] );
        $aResult['yx'] = $aTemp[0];
        $aTemp = explode( ",", $aTempResult['gpuserdomain'] );
        $aResult['dl'] = $aTemp[0];
        return $aResult;
    }*/


    /**
     * API使用 : 其他频道调用passport.model.userchannel.apiInsert 插入用户频道数据
     * 用于 passport/_api/activeUserChannel.php
     * @param int $iUserId
     * @param int $iChannelId
     * @param int $iGroupId
     * @param string $sExtendMenustr
     * @param int $iStatus
     * @return bool   插入成功或已存在数据, 返回TRUE
     *                 插入失败, 返回 FALSE
     */
    public function apiInsert( $iUserId, $iChannelId = 0, $iGroupId = 0, $sExtendMenustr = '', $iStatus = 0 )
    {
        $oUser = new model_user();
        $aUserArr = $oUser->getUserInfo( $iUserId );
        if( empty($aUserArr) )
        {
            return FALSE; // 用户ID在 PASSPORT 中不存在
        }

        $mFlag = $this->insert( $iUserId, $iChannelId, $iGroupId, $sExtendMenustr, $iStatus = 0 );
        if( $mFlag == -1 )
        {
            return TRUE; // 用户ID在 passport.userchannel 表中已有相应数据, 无需增加
        }
        
        if( $mFlag == FALSE )
        {
            return FALSE; // 数据插入失败 
        }
        return TRUE;
    }
}
?>