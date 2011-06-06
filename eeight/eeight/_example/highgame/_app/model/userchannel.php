<?php
/**
 * 用户频道关系数据模型
 *
 * 功能：
 * 		对用户和频道之间的关系的操作进行封装
 *      CRUD
 * 		--insert				增加用户和频道的对应关系
 * 		--deleteByUserId		删除用户和某频道的对应关系
 * 		--delete				根据条件删除筛选的对应关系
 * 		--update				修改用户和频道的对应关系
 * 		--getOne				根据自定义条件查询一条记录
 * 		--getList				根据自定义条件查询列表
 * 
 * 		--isExists			          检测用户和一个频道是否已存在对应关系
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
	 * @access	public
	 * @return	void
	 */
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}



	/**
	 * 根据自定义条件查询一条记录
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	array	$aInfo		//要取的对应关系信息中的字段数组
	 * @param 	string	$sWhereSql	//Where 条件[不包括where]
	 * @return 	mixed	//成功返回一个对应信息，失败返回FALSE
	 */
	public function getOne( $aInfo=array(), $sWhereSql='' )
	{
		if( is_array($aInfo) && !empty($aInfo) )
		{//自定义要取的字段信息
			foreach( $aInfo as &$v )
			{
				$v = '`'.$v.'`';
			}
			$sFields = implode(',',$aInfo);
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
		$sSql = "SELECT ". $sFields ." FROM `userchannel` ". $sWhereSql." LIMIT 1";
		return $this->oDB->getOne( $sSql );
	}



	/**
	 * 根据自定义条件查询列表
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	array	$aInfo		//要取的对应关系信息中的字段数组
	 * @param 	string	$sWhereSql	//Where 条件[不包括where]
	 * @return 	mixed	//成功返回对应信息列表，失败返回FALSE
	 */
	public function getList( $aInfo=array(), $sWhereSql='' )
	{
		if( is_array($aInfo) && !empty($aInfo) )
		{//自定义要取的字段信息
			foreach( $aInfo as &$v )
			{
				$v = '`'.$v.'`';
			}
			$sFields = implode(',',$aInfo);
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
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//用户ID
	 * @param 	int		$iChannelId	//频道ID
	 * @return 	//存在返回TRUE，不存在返回FALSE
	 */
	public function isExists( $iUserId, $iChannelId=0 )
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
		if( $this->oDB->numRows() >0 )
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
		$sSql = " SELECT c.`path`,c.`channel`,c.`id`,c.`usergroups` FROM `channels` AS c 
		          LEFT JOIN `userchannel` AS uc ON uc.`channelid`=c.`id` 
		          WHERE uc.`isdisabled`='0' AND c.`isdisabled`='0' AND uc.`userid`='" .$iUserId. "'";
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
		$sSql = "SELECT `path`,`channel`,`id`,`pid`,`usergroups` FROM `channels` 
		          WHERE `pid` IN(".implode(",",$aTempId).") AND `isdisabled`='0'";
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
     * 修改用户和用户组之间的关系
     * 
     * @access  public  
     * @author  james   09/08/03
     * @param   array   $aInfo      //要修改的信息
     * @param   string  $sWhereSql  //要修改的筛选条件[不包含where关键字]，默认为全部修改
     * @return  boolean   //成功返回TRUE，失败返回FALSE   
     */
    public function updateGroupSet( $iUserId, $aInfo=array() )
    {
    	if( empty($iUserId) || !is_numeric($iUserId) || intval($iUserId) <=0 )
    	{
    		return FALSE;
    	}
    	$iUserId = intval($iUserId);
        if( !is_array($aInfo) || empty($aInfo) )
        {
            return FALSE;
        }
        if( isset($aInfo['extendmenustr']) )
        {
            $aInfo['extendmenustr'] = daddslashes($aInfo['extendmenustr']);
        }
        $sCondition = " `userid`='".$iUserId."' ";
        //检测是否开通了此频道
        $sSql    = " SELECT * FROM `usergroupset` WHERE ".$sCondition." LIMIT 1";
        $aResult = $this->oDB->getOne( $sSql );
        if( $this->oDB->errno() > 0 )
        {//如果SQL出错
        	return FALSE;
        }
        if( empty($aResult) )
        {//如果没有开通频道则开通频道
        	//调用API向passport上的userchannel表写入数据
        	$oChannelApi = new channelapi( 0, 'activeUserChannel', TRUE );
        	$oChannelApi->setTimeOut(10);            // 设置读取超时时间
            $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
            $oChannelApi->sendRequest( array("userid"=>$iUserId,"channelid"=>SYS_CHANNELID) );    // 发送结果集
            $aResult = $oChannelApi->getDatas();
            if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
            {//调用API激活失败
            	return FALSE;
            }
        	$aInfo['userid'] = $iUserId;
        	$this->oDB->insert( 'usergroupset', $aInfo );
        	if( $this->oDB->errno() > 0 )
        	{
        		return FALSE;
        	}
        	return TRUE;
        }
        else 
        {
        	$this->oDB->update( 'usergroupset', $aInfo, $sCondition );
        	if( $this->oDB->errno() > 0 )
            {
                return FALSE;
            }
            return TRUE;
        }
    }
}
?>