<?php
/**
 * 用户返点设置表[不包括总代]
 * 
 * 功能：
 * --userMethodSetInert     //新增
 * 
 * @author    saul
 * @version  1.0.0
 * @package  passportadmin
 * @since    2009-07-30
 */
class model_usermethodset extends basemodel 
{
    /**
     * 构造函数
     * 
     * @access  public
     * @return  void
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    
    /**
     * 新增一个用户与玩法之间的返点及奖金信息
     *
     * @author  james   090806
     * @access  public
     * @param   array   $aArr
     * @return  int //相关信息参照以下具体说明
     */
    public function userMethodSetInert( $aArr=array() )
    {
    	if( empty($aArr) || !is_array($aArr) )
    	{//参数错误
    		return -1;
    	}
    	if( empty($aArr['userid']) || !is_numeric($aArr['userid']) || $aArr['userid'] <= 0 )
    	{//用户信息错误
    		return -2;
    	}
    	$aArr['userid'] = intval($aArr['userid']);
    	if( empty($aArr['methodid']) || !is_numeric($aArr['methodid']) || $aArr['methodid'] <= 0 )
    	{//玩法信息错误
    		return -3;
    	}
    	$aArr['methodid'] = intval($aArr['methodid']);
    	if( empty($aArr['prizegroupid']) || !is_numeric($aArr['prizegroupid']) || $aArr['prizegroupid'] <= 0 )
        {//奖金组信息错误
            return -4;
        }
        $aArr['prizegroupid'] = intval($aArr['prizegroupid']);
        if( !isset($aArr['userpoint']) || !is_numeric($aArr['userpoint']) || $aArr['userpoint'] < 0 )
        {//返点设置错误
            return -5;
        }
        $aArr['userpoint'] = round(floatval($aArr['userpoint']),3);
        $aArr['isclose']   = (isset($aArr['isclose']) && intval($aArr['isclose']) > 0) ? 1 : 0;
        $mResult = $this->oDB->insert( 'usermethodset', $aArr );
        if( empty($mResult) )
        {//插入数据出错
        	return -6;
        }
        return $mResult;
    }
    
    
    
    /**
     * 删除返点设置信息
     *
     * @author  james   090806
     * @access  public
     * @param unknown_type $sCondition
     * @return unknown
     */
    public function userMethodSetDelete( $sCondition="1<0" )
    {
    	if( empty($sCondition) )
        {
            $sCondition = "1<0";
        }
        return $this->oDB->delete( 'usermethodset',$sCondition );
    }
    
    
    
    /**
     * 修改返点设置
     *
     * @author  james   090806
     * @access  public
     * @param   array   $aArr
     * @param   string  $sCondition
     * @return  
     */
    public function userMethodSetUpdate( $aArr=array(), $sCondition="" )
    {
    	if( empty($aArr) || !is_array($aArr) )
        {//参数错误
            return -1;
        }
        if( isset($aArr['userid']) )
        {
        	if( !is_numeric($aArr['userid']) || $aArr['userid'] <= 0 )
        	{//用户信息错误
        		return -2;
        	}
            $aArr['userid'] = intval($aArr['userid']);
        }
        if( isset($aArr['methodid']) )
        {
        	if( !is_numeric($aArr['methodid']) || $aArr['methodid'] <= 0 )
        	{//玩法信息错误
        		return -3;
        	}
            $aArr['methodid'] = intval($aArr['methodid']);
        }
        if( isset($aArr['prizegroupid']) )
        {
        	if( !is_numeric($aArr['prizegroupid']) || $aArr['prizegroupid'] <= 0 )
        	{//奖金组信息错误
        		return -4;
        	}
            $aArr['prizegroupid'] = intval($aArr['prizegroupid']);
        }
        if( isset($aArr['userpoint']) )
        {
        	if( !is_numeric($aArr['userpoint']) || $aArr['userpoint'] < 0 )
        	{//返点设置错误
        		return -5;
        	}
            $aArr['userpoint'] = round(floatval($aArr['userpoint']),3);
        }
        if( isset($aArr['isclose']) )
        {
        	$aArr['isclose']   = intval($aArr['isclose']) > 0 ? 1 : 0;
        }
        if( !empty($sCondition) )
        {
            $sCondition = " WHERE ".$sCondition;
        }
        $mResult = $this->oDB->update( 'usermethodset', $aArr, $sCondition );
        if( $mResult == FALSE )
        {//修改数据出错
            return -6;
        }
        return $mResult;
    }
    
    
    
    /**
     * 获取用户的一个返点设置
     *
     * @author  james   090806
     * @access  public
     * @param   string  $sFields
     * @param   string  $sCondition
     * @return  array   //结果集
     */
    public function & userMethodSetGetOne( $sFields='', $sCondition='' )
    {
    	$sFields    = empty($sFields) ? '*' : daddslashes($sFields);
    	$sCondition = empty($sCondition) ? "" : "WHERE ".$sCondition;
    	$sSql       = "SELECT ".$sFields." FROM `usermethodset` ".$sCondition; 
    	return $this->oDB->getOne( $sSql );
    }
    
    
    
    /**
     * 获取返点设置列表
     *
     * @author  james   090806
     * @access  public
     * @param   string  $sFields
     * @param   string  $sCondition
     * @param   string  $sOrderBy
     * @param   int     $iPageRecord
     * @param   int     $iCurrentPage
     * @return  array //返回结果集
     */
    public function &userMethodSetGetList($sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0)
    {
        $sFields = empty($sFields) ? "*" : daddslashes($sFields);
        if( $iPageRecord == 0 )
        {//不分页显示
            $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
            $sSql       = "SELECT ".$sFields." FROM `usermethodset` ".$sCondition." ".$sOrderBy;
            return $this->oDB->getAll( $sSql );
        }
        else 
        {
            $sCondition = empty($sCondition) ? "1" : $sCondition;
            return $this->oDB->getPageResult( 'usermethodset', $sFields, $sCondition, $iPageRecord, $iCurrentPage, 
                                               $sOrderBy );
        }
    }
    
    
    
    /**
     * 获取一个用户的返点设置
     *
     * @author  james   090806
     * @access  public
     * @param   int      $iUserId
     * @param   int      $iLotteryId
     * @param   string   $sFields
     * @param   string   $sCondition
     * @return  array //返回结果集
     */
    public function & getUserSet( $iUserId, $iLotteryId, $sFields='', $sCondition=''  )
    {
    	$aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户信息错误
            return $aResult;
        }
        $iUserId = intval($iUserId);
        if( !isset($iLotteryId) || !is_numeric($iLotteryId) )
        {//彩种信息错误
            return $aResult;
        }
        $iLotteryId = intval($iLotteryId) > 0 ? intval($iLotteryId) : 0;
        $sFields    = empty($sFields) ? '*' : daddslashes($sFields);
        $sWhere     = " ums.`userid`='".$iUserId."' ";
        $sWhere    .= $iLotteryId > 0 ? " AND upg.`lotteryid`='".$iLotteryId."' " : "";
        $sWhere    .= empty($sCondition) ? "" : $sCondition;
        $sSql       = " SELECT ".$sFields." FROM `usermethodset` AS ums 
                        LEFT JOIN `userprizegroup` AS upg ON ums.`prizegroupid`=upg.`userpgid`
                        WHERE ".$sWhere;
        return $this->oDB->getAll($sSql);
    }
    
    
    
    /**
     * 根据用户和彩种获取其直接下级每个玩法的最大返点设置
     *
     * @author  james 090821
     * @access  public
     * @param   int      $iUserId
     * @param   int      $iLotteryId
     * @return  array    //结果集
     */
    public function & getUserChildMaxSet( $iUserId, $iLotteryId )
    {
    	$aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户信息错误
            return $aResult;
        }
        $iUserId = intval($iUserId);
        if( !isset($iLotteryId) || !is_numeric($iLotteryId) )
        {//彩种信息错误
            return $aResult;
        }
        $iLotteryId = intval($iLotteryId) > 0 ? intval($iLotteryId) : 0;
        $sSql       = " SELECT MAX(ums.`userpoint`) AS maxuserpoint,ums.`methodid` FROM `usermethodset` AS ums 
                        LEFT JOIN `usertree` AS ut ON ums.`userid`=ut.`userid`
                        LEFT JOIN `userprizegroup` AS upg ON ums.`prizegroupid`=upg.`userpgid`
                        WHERE ut.`parentid`='".$iUserId."' AND ut.`isdeleted`='0' AND ums.`isclose`='0'
                        AND upg.`lotteryid`='".$iLotteryId."' group by ums.`methodid` ";
        return $this->oDB->getAll($sSql);
    }
    
    
    
    /**
     * 设置用户返点
     *
     * @author james 090808
     * @access public
     * @param  int   $iUserId
     * @param  int   $iLotteryId
     * @param  array $aArr
     * @return boolean TRUE/FALSE -11:设置不合理
     */
    public function setUserPoint( $iUserId, $iLotteryId, $aArr=array() )
    {
    	if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户信息错误
            return FALSE;
        }
        $iUserId = intval($iUserId);
        if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
        {//彩种信息错误
            return FALSE;
        }
        $iLotteryId = intval($iLotteryId);
        if( !empty($aArr) && !is_array($aArr) )
        {//更新数据内容出错
        	return FALSE;
        }
        $iPrizeGroupid = 0;
        $aMethodIds    = array();   //为用户开通的玩法ID集合
        if( !empty($aArr) && is_array($aArr) )
        {//检测数据安全和完整性
        	foreach( $aArr as &$v )
        	{
        		if( empty($v['methodid']) || !is_numeric($v['methodid']) || intval($v['methodid']) <= 0 )
        		{//玩法设置错误
        			return FALSE;
        		}
        		$v['methodid'] = intval($v['methodid']);
        	    if( empty($v['prizegroupid']) || !is_numeric($v['prizegroupid']) || intval($v['prizegroupid']) <= 0 )
                {//奖金组设置错误
                    return FALSE;
                }
                $v['prizegroupid'] = intval($v['prizegroupid']);
                $iPrizeGroupid     = $v['prizegroupid'];
        	    if( !isset($v['userpoint']) || !is_numeric($v['userpoint']) || $v['userpoint'] < 0 )
                {//返点设置错误
                    return FALSE;
                }
                $aMethodIds[]      = $v['methodid'];
                $v['userpoint']    = round(floatval($v['userpoint']), 3);
                $v['limitbonus']   = (isset($v['limitbonus']) && intval($v['limitbonus'])>=0) ? $v['limitbonus'] : 0;
                $v['isclose']      = 0; //修改的都为开启
        	}
        }
        //获取用户在该彩种下的已经存在设置
        $sSql = " SELECT upg.`userpgid`,l.`mincommissiongap` FROM `usermethodset` AS ums 
                  LEFT JOIN `userprizegroup` AS upg ON ums.`prizegroupid`=upg.`userpgid`
                  LEFT JOIN `lottery` AS l ON upg.`lotteryid`=l.`lotteryid`
                  WHERE ums.`userid`='".$iUserId."' AND upg.`lotteryid`='".$iLotteryId."'";
        $aMethodResult = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0 )
        {//数据库操作错误
            return FALSE;
        }
        if( empty($aMethodResult) && empty($aArr) )
        {//如果既没有设置过，也没有数据可以设置，则直接返回true
        	return TRUE;
        }
        //$this->oDB->doTransaction();
        if( empty($aMethodResult) && !empty($aArr) && is_array($aArr) )
        {//如果没有设置过并且有数据可以设置，则检测用户是否激活
        	$this->oDB->query("SELECT 1 FROM `userchannel` WHERE `userid`='$iUserId' 
                                AND `channelid`='".SYS_CHANNELID."' ");
            if( $this->oDB->errno() >0 )
            {//失败
            	return FALSE;
            }
            $iFlagUC  = $this->oDB->ar();
	        $this->oDB->query( "SELECT 1 FROM `usergroupset` WHERE `userid`='$iUserId' " );
            if( $this->oDB->errno() >0 )
            {//失败
                return FALSE;
            }
	        $iFlagUGS = $this->oDB->ar();
	        $this->oDB->query( "SELECT 1 FROM `userfund` WHERE `userid`='$iUserId' 
	                            AND `channelid`='".SYS_CHANNELID."' " );
            if( $this->oDB->errno() >0 )
            {//失败
                return FALSE;
            }
	        $iFlagUF = $this->oDB->ar();
	        $this->oDB->doTransaction();//开始激活事务
            if( $iFlagUC <= 0 )
            {//如果未激活则通过API激活
                //调用API向passport上的userchannel表写入数据
                $oChannelApi = new channelapi( 0, 'activeUserChannel', TRUE );
                $oChannelApi->setTimeOut(10);            // 设置读取超时时间
                $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
                $oChannelApi->sendRequest( array("userid"=>$iUserId,"channelid"=>SYS_CHANNELID) );    // 发送结果集
                $aResult = $oChannelApi->getDatas();
                if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
                {//调用API激活失败
                    $this->oDB->doRollback();
                    return FALSE;
                }
            }
            if( $iFlagUGS <= 0 )
            {//如果没有数据则插入
                $aTempData = array('userid'=>$iUserId, 'groupid'=>4);
                //判断是否为一代
                $sSql = " SELECT `usertype`,`lvproxyid` FROM `usertree` WHERE `userid`='".$iUserId."'";
                $aResult = $this->oDB->getOne($sSql);
                if( empty($aResult) || $aResult['usertype'] == 2 )
                {//用户不存在或者为总代管理员
                    $this->oDB->doRollback();
                    return FALSE;
                }
                $oUser = new model_user();
            	$iParentId = $oUser->getParentId($iUserId);
            	// 获取上级用户组
            	$oGroup = new model_proxygroup();
    			$aGroup = $oGroup->getGroupID($iParentId);
                if( $aResult['usertype'] == 1 )
                {//如果为代理则判断是一代还是普代
                    $aTempData['groupid'] = $aResult['lvproxyid'] == $iUserId ? $aGroup[1] : $aGroup[2];
                }
                if ($aResult['usertype'] == 0){ // 用户
                	$aTempData['groupid'] = $aGroup[3];
                }
                //插入数据
                $aResult = $this->oDB->insert( 'usergroupset', $aTempData );
                if( empty($aResult) )
                {//插入失败
                    $this->oDB->doRollback();
                    return FALSE;
                }
            }
	        if( $iFlagUF <= 0 )
	        {//如果没有数据则插入
	        	$aTempData = array('userid'=>$iUserId, 'channelid'=>SYS_CHANNELID, 'lastupdatetime'=>date('Y-m-d H:i:s') );
	            //插入数据
                $aResult = $this->oDB->insert( 'userfund', $aTempData );
                if( empty($aResult) )
                {//插入失败
                    $this->oDB->doRollback();
                    return FALSE;
                }
	        }
	        $this->oDB->doCommit();		//提交激活事务
        }
        $this->oDB->doTransaction();	//开始数据更新事务
        $fMinCommissiongap = 0; 		//最小点差
        if( !empty($aMethodResult) && ( empty($aArr) || !is_array($aArr) ) )
        {//如果是全关闭则本身和所有下级的返点都设置为0，状态为关闭
            $sSql = " UPDATE `usermethodset` AS ums,`usertree` AS ut 
                      SET ums.`isclose`='1',ums.`userpoint`='0' WHERE ut.`userid`=ums.`userid`
                      AND (FIND_IN_SET('".$iUserId."', ut.`parenttree`) OR ut.`userid`='".$iUserId."')
                      AND ums.`prizegroupid`='".$aMethodResult['userpgid']."' ";
            $this->oDB->query($sSql);
            if( $this->oDB->errno() > 0 )
            {//数据库操作错误
                $this->oDB->doRollback();
                return FALSE;
            }
        }
        else if( !empty($aMethodResult) && !empty($aArr) && is_array($aArr) )
        {//可能只关闭一部分则设置本身和所有下级对关闭部分的返点都设置为0，状态为关闭
            $sSql = " UPDATE `usermethodset` AS ums,`usertree` AS ut 
                      SET ums.`isclose`='1',ums.`userpoint`='0' WHERE ut.`userid`=ums.`userid`
                      AND (FIND_IN_SET('".$iUserId."', ut.`parenttree`) OR ut.`userid`='".$iUserId."')
                      AND ums.`prizegroupid`='".$aMethodResult['userpgid']."'
                      AND ums.`methodid` NOT IN(".implode(",",$aMethodIds).") ";
            $this->oDB->query($sSql);
            if( $this->oDB->errno() > 0 )
            {//数据库操作错误
                $this->oDB->doRollback();
                return FALSE;
            }
        }
        if( empty($aArr) || !is_array($aArr) )
        {//如果没有更新数据则提交事务直接返回TRUE
        	$this->oDB->doCommit();
        	return TRUE;
        }
        //如果奖金组进行了修改[修改所有下级的奖金组]
        if( !empty($aMethodResult) && $iPrizeGroupid !=0 && $aMethodResult['userpgid'] != $iPrizeGroupid )
        {//
            $sSql = " UPDATE `usermethodset` AS ums,`usertree` AS ut 
                      SET ums.`prizegroupid`='".$iPrizeGroupid."' WHERE ut.`userid`=ums.`userid`
                      AND FIND_IN_SET('".$iUserId."', ut.`parenttree`)
                      AND ums.`prizegroupid`='".$aMethodResult['userpgid']."' ";
            $this->oDB->query($sSql);
            if( $this->oDB->errno() > 0 )
            {//数据库操作错误
                $this->oDB->doRollback();
                return FALSE;
            }
        }
        //更新相关数据
        foreach( $aArr as &$vv )
        {
        	//检测是否存在已有的玩法
        	$sSql = " SELECT `entry`,`userpoint`,`prizegroupid` FROM `usermethodset` 
        	           WHERE `userid`='".$iUserId."' AND `methodid`='".$vv['methodid']."' ";
        	$aResult = $this->oDB->getOne($sSql);
        	if( $this->oDB->errno() > 0 )
        	{//数据库操作错误
        		$this->oDB->doRollback();
        		return FALSE;
        	}
        	if( empty($aResult) )
        	{//没有相应玩法则插入
        		$vv['userid'] = $iUserId;
        		$this->oDB->insert( 'usermethodset', $vv );
        		if( $this->oDB->errno() > 0 )
        		{//插入失败
        			$this->oDB->doRollback();
                    return FALSE;
        		}
        	}
        	else 
        	{//有则修改
        		//判断当前设置返点是否小于原来的设置返点
        		if( $vv['userpoint'] < $aResult['userpoint'] )
        		{//如果更改后的返点小于原来的返点则检测被修改用户下级的该玩法的最大返点
        			$sSql = " SELECT MAX(ums.`userpoint`) AS usermaxpoint FROM `usermethodset` AS ums
        			          LEFT JOIN `usertree` AS ut ON ums.`userid`=ut.`userid`
        			          WHERE ut.`parentid`='".$iUserId."' AND ut.`isdeleted`='0'
        			          AND `methodid`='".$vv['methodid']."' ";
        			$aTempResult = $this->oDB->getOne($sSql);
        			if( !empty($aTempResult) && $vv['userpoint'] < ($aTempResult['usermaxpoint']+$fMinCommissiongap)
        			    && $aTempResult['usermaxpoint'] > 0 )
        			{//如果当前返点小于其下级最大返点+返点差则设置不合理
        				return -11;
        			}
        		}
        		$this->oDB->update( 'usermethodset', $vv, " `entry`='".$aResult['entry']."' " );
        	    if( $this->oDB->errno() > 0 )
                {//修改失败
                    $this->oDB->doRollback();
                    return FALSE;
                }
        	}
        }
        $this->oDB->doCommit();//所有成功以后提交事务
        return TRUE;
    }



    /**
     * TODO _a高频、低频并行前期临时程序
     * 同步用户激活低频帐户
     */
    public function gdSetUserPoint( $iUserId )
    {
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户信息错误
            return 0;
        }
        $iUserId = intval( $iUserId );
        //01: 检查用户是否存在并且只能是从二代开始
        $sSql = " SELECT `usertype`,`lvtopid`,`lvproxyid`,`parenttree`,`parentid` 
                  FROM `usertree` WHERE `userid`='".$iUserId."' AND `isdeleted`='0' ";
        $aSelfData = $this->oDB->getOne( $sSql );
        if( empty($aSelfData) )
        {//用户不存在或者已删除
        	return -1;
        }
        if( $aSelfData['parentid'] == 0 || $aSelfData['usertype'] == 2 || $aSelfData['lvproxyid'] == $iUserId )
        {//不能为总代，总代管理，一代
        	return -2;
        }
        //02: 检查上级是否设置了返点以及奖金组信息
        $sSql = "SELECT count(`entry`) AS allcount FROM `usermethodset` WHERE `userid`='".$aSelfData['parentid']."'";
        $aTempData    = $this->oDB->getOne( $sSql );
        $iParentCount = empty($aTempData) ? 0 : $aTempData['allcount']; 
        if( $iParentCount <= 0 )
        {//上级没有设置奖金组情况
        	//return -3;
        }
        //检查用户自己的奖金组情况
        $sSql      = "SELECT count(`entry`) AS allcount FROM `usermethodset` WHERE `userid`='".$iUserId."'";
        $aTempData = $this->oDB->getOne( $sSql );
        if( $this->oDB->errno() > 0 )
        {
        	return -4;
        }
        $iSelfCount = empty($aTempData) ? 0 : $aTempData['allcount']; 
        //03: 激活用户平台帐户
        $this->oDB->query("SELECT 1 FROM `userchannel` WHERE `userid`='$iUserId' 
                                AND `channelid`='".SYS_CHANNELID."' ");
        if( $this->oDB->errno() >0 )
        {//失败
            return -4;
        }
        $iFlagUC  = $this->oDB->ar();
        $this->oDB->query( "SELECT 1 FROM `usergroupset` WHERE `userid`='$iUserId' " );
        if( $this->oDB->errno() >0 )
        {//失败
            return -4;
        }
        $iFlagUGS = $this->oDB->ar();
        $this->oDB->query( "SELECT 1 FROM `userfund` WHERE `userid`='$iUserId' 
                            AND `channelid`='".SYS_CHANNELID."' " );
        if( $this->oDB->errno() >0 )
        {//失败
            return -4;
        }
        $iFlagUF = $this->oDB->ar();
        $this->oDB->doTransaction();//开始激活事务
        if( $iFlagUC <= 0 )
        {//如果未激活则通过API激活
            //调用API向passport上的userchannel表写入数据
            $oChannelApi = new channelapi( 0, 'activeUserChannel', TRUE );
            $oChannelApi->setTimeOut(10);            // 设置读取超时时间
            $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
            $oChannelApi->sendRequest( array("userid"=>$iUserId,"channelid"=>SYS_CHANNELID) );    // 发送结果集
            $aResult = $oChannelApi->getDatas();
            if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
            {//调用API激活失败
                $this->oDB->doRollback();
                return -5;
            }
        }
        if( $iFlagUGS <= 0 )
        {//如果没有数据则插入
            $aTempData = array('userid'=>$iUserId, 'groupid'=>4);
            if( $aSelfData['usertype'] == 1 )
            {//如果为代理则判断是一代还是普代
                $aTempData['groupid'] = $aSelfData['lvproxyid'] == $iUserId ? 2 : 3;
            }
            //插入数据
            $aResult = $this->oDB->insert( 'usergroupset', $aTempData );
            if( empty($aResult) )
            {//插入失败
                $this->oDB->doRollback();
                return -6;
            }
        }
        if( $iFlagUF <= 0 )
        {//如果没有数据则插入
            $aTempData = array('userid'=>$iUserId, 'channelid'=>SYS_CHANNELID, 'lastupdatetime'=>date('Y-m-d H:i:s') );
            //插入数据
            $aResult = $this->oDB->insert( 'userfund', $aTempData );
            if( empty($aResult) )
            {//插入失败
                $this->oDB->doRollback();
                return -7;
            }
        }
        $sSql = " SELECT * FROM `usermethodset` WHERE `userid` = '" . $aSelfData['parentid'] . "'";
        $aParentPrizeData = $this->oDB->getAll($sSql);
        if( empty($aParentPrizeData) )
        {
            $this->oDB->commit();
            return TRUE;
        }
        if( $iSelfCount != $iParentCount )
        {//如果数据不完整或者没有数据
	        //04: 继承上级奖金组信息
	        if( $iSelfCount > 0 )
	        {//删除已有的
	            $sSql = "DELETE FROM `usermethodset` WHERE `userid`='".$iUserId."'";
	            $this->oDB->query( $sSql );
	            if( $this->oDB->errno() > 0 )
	            {
	                $this->oDB->doRollback();
	                return -8;
	            }
	        }
	        $sSql = " INSERT INTO `usermethodset`(`userid`,`methodid`,`prizegroupid`,`userpoint`,`limitbonus`,`isclose`)
	                  SELECT '".$iUserId."',`methodid`,`prizegroupid`,'0',`limitbonus`,`isclose` FROM `usermethodset`
	                  WHERE `userid`='".$aSelfData['parentid']."' ";
	        $this->oDB->query( $sSql );
	        if( $this->oDB->ar() <= 0 )
	        {
	            $this->oDB->doRollback();
	            return -9;
	        }
        }
        $this->oDB->doCommit();//提交激活事务
        return TRUE;
    }



    /**
     * 根据用户ID获取其自己的奖金情况以及返点情况
     *
     * @author james   090808
     * @access public
     * @param   int      $iUserId
     * @param   string   $sFields
     * @param   string   $sCondition
     * @param   boolean  $bGetOne
     * @return  array
     */
    public function & getUserMethodPrize( $iUserId, $sFields="", $sCondition="", $bGetOne=TRUE )
    {
    	$aResult = array();
    	if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户信息错误
            return $aResult;
        }
        $iUserId = intval($iUserId);
        $sFields = empty($sFields) ? "m.`methodid`,upl.`level`,upl.`prize`" : daddslashes($sFields);
        $sSql = " SELECT ".$sFields." FROM `method` AS m 
                  LEFT JOIN `usermethodset` AS ums ON ums.`methodid`=m.`methodid`
                  LEFT JOIN `userprizelevel` AS upl ON 
                  (upl.`userpgid`=ums.`prizegroupid` AND upl.`methodid`=ums.`methodid`)
                  WHERE ums.`userid`='".$iUserId."' AND m.`isclose`='0' AND m.`pid`='0' 
                  AND ums.`isclose`='0' AND upl.`isclose`='0' ".$sCondition;
        if( $bGetOne )
        {//只获取一条
       	    return $this->oDB->getOne($sSql." limit 1 ");
        }
        else 
        {//获取多列数据
       	    return $this->oDB->getAll($sSql);
        }
    }
    
    
    /**
     * 根据用户ID和玩法组ID获取所有上级的返点（不包括总代）
     *
     * @author  james    090810
     * @access  public   
     * @param   int      $iUserId       //用户ID
     * @param   int      $iMethodId     //玩法组ID
     */
    public function & getParentPoint( $iUserId, $iMethodId, $sFields='', $sCondition='' )
    {
    	$aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户信息错误
            return $aResult;
        }
        $iUserId = intval($iUserId);
        if( empty($iMethodId) || !is_numeric($iMethodId) || $iMethodId <= 0 )
        {//玩法组信息错误
            return $aResult;
        }
        $iMethodId = intval($iMethodId);
        //先查用户所有上级ID
        $oUser     = A::singleton('model_user');
        $sParents  = $oUser->getParentId( $iUserId, TRUE );
        if( empty($sParents) )
        {
        	return $aResult;
        }
        $sFields   = empty($sFields) ? 'ut.`userid`,ut.`lvtopid`,ut.`lvproxyid`,ums.`userpoint`,ums.`isclose`' : 
                                        daddslashes($sFields);
        $sSql      = " SELECT ".$sFields."
                       FROM `usertree` AS ut
                       LEFT JOIN `usermethodset` AS ums ON ut.`userid`=ums.`userid` 
                       WHERE ut.`isdeleted`='0' AND ut.`userid` IN(".$sParents.") 
                       AND ut.`parentid`<>'0' AND ums.`methodid`='".$iMethodId."' ".$sCondition;
        $aResult = $this->oDB->getDataCached( $sSql );
        return $aResult;
    }




    /**
     * 获取非总代用户的返点奖金(admin使用)
     * @author SAUL 090810
     * @param integer $iUserId
     * @param string $sFields
     * @param string $sCondition
     * @return array
     */
    function &getUserMethodPoint($iUserId, $sFields="", $sCondition ="")
    {
    	$iUserId = intval($iUserId);
        $sFields = empty($sFields) ? "m.`methodid`,upl.`level`,upl.`prize`" : daddslashes($sFields);
    	if(empty($sCondition))
    	{
    		$sCondition =" AND 1";
    	}
        $sSql = " SELECT ".$sFields." FROM `method` AS m 
                  LEFT JOIN `usermethodset` AS ums ON ums.`methodid`=m.`methodid`
                  LEFT JOIN `userprizelevel` AS upl ON 
                  (upl.`userpgid`=ums.`prizegroupid` AND upl.`methodid`=ums.`methodid`)
                  LEFT JOIN `userprizegroup` AS UPG ON
                  (upl.`userpgid`=UPG.`userpgid`)
                  WHERE ums.`userid`='".$iUserId."' AND m.`isclose`='0' AND m.`pid`='0' 
                  AND upl.`isclose`='0' ".$sCondition;
    	return $this->oDB->getAll($sSql);
    }
}
