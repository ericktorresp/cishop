<?php
/**
 * 文件 : /_app/model/task.php
 * 功能 : 数据模型 - 追号模型
 * 
 * - taskInsert             插入追号表记录
 * - taskDetailInsert       写入追号详情[多条记录一起写]
 * - getUserPrizeBySame     查询用户某一期某种玩法同一个投注号码在追号中拥有的奖金[用于奖金限额]
 * - taskgetList            追号列表查询
 * - taskdetailGetList      追号详情记录
 * 
 * @author     james    090916
 * @version    1.2.0
 * @package    lowgame  
 */

class model_task extends basemodel
{
	/**
	 * 插入追号表记录
	 *
	 * @author james 090815
	 * @access public
	 * @param  array    $aArr
	 * @return int     //失败返回0以下，成功返回插入的ID
	 */
	public function taskInsert( $aArr=array() )
	{
		if( empty($aArr) || !is_array($aArr) )
		{
			return 0;
		}
	    if( empty($aArr['userid']) || !is_numeric($aArr['userid']) || $aArr['userid'] <= 0 )
        {//用户ID
            return -1;
        }
        $aArr['userid'] = intval($aArr['userid']);
        if( empty($aArr['lotteryid']) || !is_numeric($aArr['lotteryid']) || $aArr['lotteryid'] <= 0 )
        {//彩种ID
            return -2;
        }
        $aArr['lotteryid'] = intval($aArr['lotteryid']);
        if( empty($aArr['methodid']) || !is_numeric($aArr['methodid']) || $aArr['methodid'] <= 0 )
        {//玩法ID
            return -3;
        }
        $aArr['methodid'] = intval($aArr['methodid']);
        if( empty($aArr['packageid']) || !is_numeric($aArr['packageid']) || $aArr['packageid'] <= 0 )
        {//定单ID
            return -3;
        }
        $aArr['packageid'] = intval($aArr['packageid']);
        $aArr['title']    = empty($aArr['title']) ? "" : daddslashes($aArr['title']);   //标题
        if( !isset($aArr['codes']) || $aArr['codes'] == "" )
        {//购买的号码原复式
            return -4;
        }
        $aArr['codes'] = daddslashes($aArr['codes']);
        if( empty($aArr['issuecount']) || !is_numeric($aArr['issuecount']) || $aArr['issuecount'] <= 0 )
        {//追号总期数
            return -5;
        }
        $aArr['issuecount'] = intval($aArr['issuecount']);
        if( isset($aArr['finishedcount']) && is_numeric($aArr['finishedcount']) && $aArr['finishedcount'] > 0 )
        {//完成期数
        	$aArr['finishedcount'] = intval($aArr['finishedcount']);
        }
        else
        {
        	$aArr['finishedcount'] = 0;
        }
        $aArr['cancelcount'] = 0; //取消期数默认为0
        if( empty($aArr['taskprice']) || !is_numeric($aArr['taskprice']) || $aArr['taskprice'] <= 0 )
        {//追号总金额
            return -6;
        }
        $aArr['taskprice']   = number_format(floatval($aArr['taskprice']),4, '.', '');
        $aArr['finishprice'] = 0;       //完成的总金额为0
        $aArr['cancelprice'] = 0;       //取消的总金额默认为0
        if( empty($aArr['singleprice']) || !is_numeric($aArr['singleprice']) || $aArr['singleprice'] <= 0 )
        {//每期的单倍价格
            return -7;
        }
        $aArr['singleprice'] = number_format(floatval($aArr['singleprice']),4, '.', '');
        $aArr['begintime']   = date("Y-m-d H:i:s"); //追号开始时间为当前时间
        if( empty($aArr['beginissue']) )
        {//开始的期数
            return -8;
        }
        $aArr['beginissue'] = daddslashes($aArr['beginissue']);
        $aArr['wincount']   = 0; //赢的期数默认为0
        if( empty($aArr['prize']) )
        {//奖金序列化
            return -9;
        }
        $aArr['prize'] = daddslashes($aArr['prize']);
        if( empty($aArr['userdiffpoints']) )
        {//返点序列化
            return -10;
        }
        $aArr['userdiffpoints'] = daddslashes($aArr['userdiffpoints']);
        if( empty($aArr['lvtopid']) || !is_numeric($aArr['lvtopid']) || $aArr['lvtopid'] <= 0 )
        {//总代ID
            return -11;
        }
        $aArr['lvtopid'] = intval($aArr['lvtopid']);
        if( !isset($aArr['lvtoppoint']) || !is_numeric($aArr['lvtoppoint']) || $aArr['lvtoppoint'] < 0 )
        {//总代返点
            return -12;
        }
        $aArr['lvtoppoint'] = number_format(floatval($aArr['lvtoppoint']),3, '.', '');
        if( empty($aArr['lvproxyid']) || !is_numeric($aArr['lvproxyid']) || $aArr['lvproxyid'] <= 0 )
        {//一代ID
            return -13;
        }
        $aArr['lvproxyid']      = intval($aArr['lvproxyid']);
        if( empty($aArr['modes']) || !is_numeric($aArr['modes']) || $aArr['modes'] <= 0 )
        {//模式ID
            return -14;
        }
        $aArr['modes']          = intval($aArr['modes']);
        $aArr['status']         = 0; //状态默认为0
        $aArr['stoponwin']      = (isset($aArr['stoponwin']) && intval($aArr['stoponwin'])>0) ? 1 : 0;//追中即停
        $aArr['userip']         = getRealIP();
        $aArr['cdnip']          = $_SERVER['REMOTE_ADDR'];
        $aArr['updatetime']     = date("Y-m-d H:i:s");
        $iResult                = $this->oDB->insert( 'tasks', $aArr );
        if( empty($iResult) )
        {//操作数据库失败
            return -14;
        }
        return $iResult;
	}
	
	
	
	/**
     * 写入追号详情[多条记录一起写]
     *
     * @author james    090815
     * @access public
     * @param  array    $aArrData  二维数组
     * @return int     成功返回TRUE，失败返回FALSE
     */
	public function taskDetailInsert( $aArrData =array() )
	{
		//01:数据检查
        if( empty($aArrData) || !is_array($aArrData) )
        {
            return FALSE;
        }
        $aValues = array();
        foreach( $aArrData as $aArr )
        {
            if( empty($aArr['taskid']) || !is_numeric($aArr['taskid']) || $aArr['taskid'] <= 0 )
            {//追号方案ID
                return FALSE;
            }
            $aArr['taskid']    = intval($aArr['taskid']);
	        if( isset($aArr['projectid']) && is_numeric($aArr['projectid']) && $aArr['projectid'] > 0 )
	        {//生成的方案ID
	            $aArr['projectid'] = intval($aArr['projectid']);
	        }
	        else
	        {
	            $aArr['projectid'] = 0;
	        }
            if( empty($aArr['multiple']) || !is_numeric($aArr['multiple']) || $aArr['multiple'] <= 0 )
            {//倍数
                return FALSE;
            }
            $aArr['multiple'] = intval($aArr['multiple']);
            if( empty($aArr['issue']) )
	        {//期号
	            return FALSE;
	        }
	        $aArr['issue']  = daddslashes($aArr['issue']);
	        $aArr['status'] = (isset($aArr['status']) && intval($aArr['status'])>0) ? 1 : 0;//状态
	        
            $aValues[]       = "('".$aArr['taskid']."','".$aArr['projectid']."','".$aArr['multiple']."',
                                 '".$aArr['issue']."','".$aArr['status']."')";
        }
        //构造SQL语句
        $sSql = " INSERT INTO `taskdetails`(`taskid`,`projectid`,`multiple`,`issue`,`status`) 
                  VALUES".implode(",",$aValues);
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 )
        {//操作数据库失败
            return FALSE;
        }
        return TRUE;
	}

	
	
	/**
     * 查询用户某一期某种玩法同一个投注号码在追号中拥有的奖金[用于奖金限额]
     *
     * @author james   090814
     * @access public
     * @param  int      $iUserId
     * @param  array    $aIssue //所有的追号期
     * @param  int      $iLotteryId
     * @param  int      $iMethodId
     * @param  string   $sCode
     * @return mixed   失败返回FALSE，成功返回查询结果集
     */
	public function getUserPrizeBySame( $iUserId, $iLotteryId, $iMethodId, $aIssue, $sCode )
	{
		//01：数据检查
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户ID
            return FALSE;
        }
        $iUserId = intval($iUserId);
        if( empty($aIssue) || !is_array($aIssue) )
        {//奖期
            return FALSE;
        }
        if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
        {//彩种ID
            return FALSE;
        }
        $iLotteryId = intval($iLotteryId);
        if( empty($iMethodId) || !is_numeric($iMethodId) || $iMethodId <= 0 )
        {//玩法ID
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        if( !isset($sCode) || $sCode == "" )
        {//购买的原式号码
            return FALSE;
        }
        $sCode = daddslashes($sCode);
        $sSql  = " SELECT t.`prize`,t.`taskid`,td.`issue`,td.`multiple`
                   FROM `tasks` AS t LEFT JOIN `taskdetails` AS td ON t.`taskid`=td.`taskid` 
                   WHERE t.`userid`='".$iUserId."' AND t.`lotteryid`='".$iLotteryId."' 
                   AND t.`methodid`='".$iMethodId."' AND t.`codes`='".$sCode."'
                   AND t.`status`='0' AND td.`issue` IN ('".implode("','",$aIssue)."') AND td.`status`='0' ";
        $aResult = $this->oDB->getAll( $sSql );
        if( $this->oDB->errno() > 0 )
        {//数据库错误
            return FALSE;
        }
        return $aResult;
	}


	/**
	 * 追号列表查询
	 *
	 * @param integer $iUserId
	 * @param bool $bAllChild
	 * @param string $sField
	 * @param string $sCondtion
	 * @param string $sOrderBy
	 * @param integer $iPageRecord
	 * @param integer $iCurrPage
	 * @return array
	 */
	function taskgetList( $iUserId, $bAllChild = TRUE, $sField ="", $sCondtion="", $sOrderBy="",
	                          $iPageRecord=25, $iCurrPage=1 )
	{
		$aArr       = array();
    	$sTableName = "`tasks` AS T "
    				 ."LEFT JOIN `usertree` AS UT ON (T.`userid`=UT.`userid`) "
    				 ."LEFT JOIN `method` AS M ON (T.`methodid`=M.`methodid`) "
    				 ."LEFT JOIN `lottery` AS L ON (L.`lotteryid`=T.`lotteryid`) ";
    	if( empty($iUserId) && !is_numeric($iUserId) )
    	{
    		return $aArr;
    	}
    	$iUserId = intval($iUserId);
    	$sWhere  = " 1 ";
		if( $bAllChild )
    	{
    		if( $iUserId > 0 )
    		{
    			$sWhere .=" AND (FIND_IN_SET(".intval($iUserId).",UT.`parenttree`) OR (UT.`userid`='".$iUserId."'))";
    		}
    	}
    	else
    	{
    		if( $iUserId > 0 )
    		{
    			$sWhere .=" AND T.`userid`='".$iUserId."'";
    		}
    	}
    	if( empty($sField) )
    	{
    		$sField = "T.*,L.`cnname`,M.`methodname`,UT.`username`";	
    	}
    	else
    	{
    		$sField =daddslashes($sField);
    	}
    	if( !empty($sCondtion) )
    	{
    		$sWhere .= $sCondtion;
    	}
    	$iPageRecord = isset($iPageRecord) && is_numeric($iPageRecord) ? intval($iPageRecord): 0;
    	$sOrderBy    = empty($sOrderBy) ? " " :" Order BY ".$sOrderBy;
    	if( $iPageRecord==0 )
    	{
    		return $this->oDB->getAll("SELECT ".$sField." FROM ".$sTableName." where ".$sWhere.$sOrderBy);
    	}
    	$iCurrPage = isset($iCurrPage) && is_numeric($iCurrPage) ? intval($iCurrPage) : 1;
    	//获取总数SQL
    	$sCountTableName = "`tasks` AS T LEFT JOIN `usertree` AS UT ON (T.`userid`=UT.`userid`) ";
    	if(strpos($sWhere,"M.") !== FALSE)
    	{
    	    $sCountTableName .= " LEFT JOIN `method` AS M ON (T.`methodid`=M.`methodid`) ";
    	}
    	$sCountSql = " SELECT COUNT(*) AS TOMCOUNT FROM ".$sCountTableName." WHERE ".$sWhere;
    	return $this->oDB->getPageResult( $sTableName, $sField, $sWhere, $iPageRecord, $iCurrPage, $sOrderBy );
	}



    /**
     * 追号详情记录
     *
     * @param integer $iTaskId
     */
    function taskdetailGetList( $iTaskId, $iLotteryId )
    {
    	if( !is_numeric($iTaskId) &&!is_numeric($iLotteryId) )
    	{
    		$aResult = array();
    		return $aResult;
    	}
    	return $this->oDB->getAll("SELECT * FROM `taskdetails` AS TD "
    	."Left Join `issueinfo` AS II ON( TD.`issue`=II.`issue` and II.`lotteryid`='".$iLotteryId."')"
    	." where `taskid`='".$iTaskId."'");
    }
    
    /**
     * 追号单转注单数据处理流程[非事务]
     *
     * @author  james
     * @access  public  
     * @param   int      $iTaskId   //追号表ID
     * @param   array    $aDataArr  //数据数组
     * ----------------------------------------------
     * $aDataArr['aCreateData']     //追号返款帐变数据
     * $aDataArr['aProjectData']    //方案表数据
     * $aDataArr['aJoinData']       //加入游戏帐变数据
     * $aDataArr['aBackData']       //本人销售返点帐变数据
     * $aDataArr['aExpandData']     //号码扩展表数据
     * $aDataArr['aDiffData']       //用户返点差数据
     * @param   boolean  $bIsFinish //是否已完成[完成的修改完成状态]
     * @return  mixed    小于0为错误，全等于TRUE为成功
     */
    public function traceToProjectData( $iTaskId, $aDataArr, $bIsFinish = FALSE, $iWinCount = 0 )
    {
        //00:必要参数判断
        if( empty($aDataArr) || !is_array($aDataArr) )
        {//无任何数据
            return 0;
        }
        if( empty($iTaskId) || !is_numeric($iTaskId) || $iTaskId <= 0 )
        {//追号表ID
            return 0;
        }
        $iTaskId = intval($iTaskId);
        if( empty($aDataArr['aCreateData']) || !is_array($aDataArr['aCreateData']) )
        {//追号返款帐变记录必须插入
            return 0;
        }
        if( empty($aDataArr['aProjectData']) || !is_array($aDataArr['aProjectData']) )
        {//方案表记录必须插入
            return 0;
        }
        if( empty($aDataArr['aJoinData']) || !is_array($aDataArr['aJoinData']) )
        {//加入游戏帐变必须写入
            return 0;
        }
        if( empty($aDataArr['aExpandData']) || !is_array($aDataArr['aExpandData']) )
        {//号码扩展必须写入
            return 0;
        }
        /* @var $oProjects model_projects */
        /* @var $oOrders model_orders */
        $oProjects = A::singleton('model_projects'); //方案模型
        $oOrders   = A::singleton('model_orders');   //帐变模型
        
        
        //02 ：写入方案表-----------------------------------------------------------
        $aDataArr['aProjectData']['taskid'] = $iTaskId;
        $iProjectId = $oProjects->projectsInsert( $aDataArr['aProjectData'] );
        if( $iProjectId <= 0 )
        {//写入方案失败
            return -2;
        }
        
        //01：写追号当期返款帐变-----------------------------------
        $aDataArr['aCreateData']['iTaskId']    = $iTaskId;
        $aDataArr['aCreateData']['iProjectId'] = $iProjectId;
        if( TRUE !== $oOrders->addOrders($aDataArr['aCreateData']) )
        {//帐变错误
            return -1;
        }
        
        //03：写加入游戏帐变以及用户资金扣钱-----------------------------------------
        $aDataArr['aJoinData']['iTaskId']    = $iTaskId;
        $aDataArr['aJoinData']['iProjectId'] = $iProjectId;
        $mResult = $oOrders->addOrders( $aDataArr['aJoinData'] );
        if( $mResult === -1009  )
        {//资金不够
            return -33;
        }
        elseif( $mResult !== TRUE )
        {//其他帐变错误
            return -3;
        }
        
        //04：写本人返点帐变以及加钱[在本人返点大于0的情况才执行]---------------------
        if( !empty($aDataArr['aBackData']) && is_array($aDataArr['aBackData']) )
        {
           $aDataArr['aBackData']['iTaskId']    = $iTaskId;
           $aDataArr['aBackData']['iProjectId'] = $iProjectId;
           $mResult = $oOrders->addOrders( $aDataArr['aBackData'] );
           if( $mResult !== TRUE )
           {//帐变错误
               return -4;
           }
        }
        
        //05：写入号码扩展表--------------------------------------------------------
        foreach( $aDataArr['aExpandData'] as &$vv )
        {
            $vv['projectid'] = $iProjectId;
            $vv['codetimes'] = (isset($vv['codetimes']) && $vv['codetimes'] > 0) 
                        ? $vv['codetimes'] * $aDataArr['aProjectData']['multiple'] // 号码倍数*方案倍数 
                        : 1; 
        }
        if(  TRUE !== $oProjects->expandCodeInsert( $aDataArr['aExpandData'] ) )
        {//写入号码扩展失败
             return -5;
        }
        
        //06：写入返点差表[有数据则写入]---------------------------------------------
        if( !empty($aDataArr['aDiffData']) && is_array($aDataArr['aDiffData']) )
        {
            foreach( $aDataArr['aDiffData'] as &$v )
            {
                $v['projectid'] = $iProjectId;
            }
            if(  TRUE !== $oProjects->userDiffPointInsert( $aDataArr['aDiffData'] ) )
            {//写入返点失败
                return -6;
            }
        }
        
        //07: 更新追号表完成期数+1,完成金额+本单金额
        $sSql = " UPDATE `tasks` SET `finishedcount`=`finishedcount`+1,`updatetime`='".date("Y-m-d H:i:s")."',
                  `finishprice`=`finishprice`+".$aDataArr['aProjectData']['totalprice'].
                  ($bIsFinish == TRUE ? ",`status`='2'" : "").($iWinCount > 0 ? ",`wincount`='".$iWinCount."'" : "")." 
                  WHERE `taskid`='".$iTaskId."' ";
        $this->oDB->query($sSql);
        if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
        {//更新追号表失败
            return -7;
        }
        
        //08: 更新追号详情表状态
        $sSql = " UPDATE `taskdetails` SET `projectid`='".$iProjectId."',`status`='1' 
                  WHERE `taskid`='".$iTaskId."' AND `issue`='".$aDataArr['aProjectData']['issue']."' AND `status`='0' ";
        $this->oDB->query($sSql);
        if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
        {//更新追号表失败
            return -8;
        }
        
        //08：完成[返回TRUE]--------------------------------------------------------
        return TRUE;
    }
}
?>
