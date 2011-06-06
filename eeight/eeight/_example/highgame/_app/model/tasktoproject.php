<?php
/**
 * 文件 : /_app/model/tasktoproject.php
 * 功能 : 数据模型 - 追号单转注单模型
 * 
 *  在游戏销售期间对当前期内的追号单进行转注单操作.
 * 
 * - traceToProject         所有追号单转注单[CLI调用]
 * - traceToProjectData     追号单转注单数据处理流程[非事务]
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highgame  
 */

class model_tasktoproject extends basemodel
{
    /**
     * 所有追号单转注单[CLI调用]
     * CLI 调用流程
     *   1, traceToProject() -> 根据彩种ID循环执行本方法 traceToProject()
     *   
     */
    public function traceToProject( $iLotteryId, $bIsLoop = FALSE, $iRunTimes = 1 )
    {
    	$bIsLoop = $bIsLoop === TRUE ? TRUE : FALSE;
    	$iAffectedSuccess = 0;   //本次操作成功所影响的数据
    	$iAffectedFailed  = 0;   //本次操作失败所影响的数据
    	$sMsg    = "";
    	
    	//01: 验证数据彩种号是否正确
    	if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
    	{
    		return "lotteryid is wrong\r\n";
    	}
    	$sSql = "SELECT * FROM `lottery` WHERE `lotteryid` = '" . $iLotteryId ."' LIMIT 1";
    	$aLottery = $this->oDB->getOne($sSql);
    	if( empty($aLottery) )
    	{
    	    return "lotteryid is wrong\r\n";
    	}
    	unset($aLottery);
    	unset($sSql);
    	$oIssue     = A::singleton("model_issueinfo");
    	//02: 获取需要转注单的期号
    	/**
    	 * 02 01: 获取已经完成追号单转注单的最近一期信息
    	 *    在销售期内每五分钟运行一次。
    	 *    如果未顺利执行最后一期的派奖. 会影响当期追号单转注单的生成 (没办法判断追中即停的操作)
    	 *    ** 应高度重视 **
    	 */
    	$sFileds    = " A.`issue`, A.`statuscode`, A.`statusbonus`,A.`statuscheckbonus`";
        $sCondition = " A.`lotteryid`='".$iLotteryId."' AND A.`statustasktoproject`='2'
                        ORDER BY A.`saleend` DESC";
        $aResult    = $oIssue->IssueGetOne( $sFileds, $sCondition );
        if( !empty($aResult) && $aResult['statuscheckbonus'] != 2 )
        { // 如果已经生成注单的没有完成开奖、派奖则直接返回等待开奖、派奖完成 (TODO:报警:严重问题)
        	return " wait ".$aResult['issue']." send bonus ";
        }

    	//02 02: 获取未生成注单的最近一期信息
    	//    1, 符合当前彩种ID的           $iLotteryId
    	//    2, 未完整执行追号单转注单的   statustasktoproject !=2
    	//    3, 为了按时间顺序线性执行,取最早一起符合以上要求的  ORDER BY A.`saleend` ASC
    	//    4, 当期在销售期间 A.`salestart`<'".date("Y-m-d H:i:s",time()) ."'
    	$sFileds    = " A.`issue`,A.`issueid`,A.`statustasktoproject` ";
        $sCondition = " A.`lotteryid`='".$iLotteryId."' AND A.`statustasktoproject`!='2' AND A.`salestart`<'".date("Y-m-d H:i:s",time()) ."'
                        ORDER BY A.`saleend` ASC";
        $aResult    = $oIssue->IssueGetOne( $sFileds, $sCondition );
        if( empty($aResult) )
        {//没有需要转注单的期号则 直接退出返回
        	return "ALL DONE (All Issue)\r\n";
        }
        
        $sIssue   = $aResult['issue'];
        $iIssueId = $aResult['issueid'];
        //03: 更新状态为正在执行
        if( $aResult['statustasktoproject'] == 0 )
        {
        	$sSql = "UPDATE `issueinfo` SET `statustasktoproject`='1' WHERE `issueid`='".$iIssueId."'";
        	$this->oDB->query($sSql);
        	if( $this->oDB->errno() > 0 )
        	{
        		return " update ".$aResult['issue']." [statustasktoproject] error \r\n" ;
        	}
        }
    	//04： 读取需要转注单的追号单信息
    	$sSql   = " SELECT t.*,td.`multiple`,td.`issue`
    	            FROM `tasks` AS t LEFT JOIN `taskdetails` AS td ON t.`taskid`=td.`taskid`
    	            WHERE td.`projectid`='0' AND td.`issue`='".$sIssue."' AND td.`status`='0'
    	            AND t.`lotteryid`='".$iLotteryId."' AND t.`status`='0' ";
    	$aTasks = $this->oDB->getAll($sSql);

    	if( empty($aTasks) )
    	{//没有要转的追号单，返回成功
    	    //05: 修改奖期表状态
	        $sSql = "UPDATE `issueinfo` SET `statustasktoproject`='2' WHERE `issueid`='".$iIssueId."'";
	        $this->oDB->query($sSql);
	        if( $this->oDB->errno() > 0 )
	        {
	            return " update ".$sIssue." [statustasktoproject] error \r\n" ;
	        }
	    	if( $bIsLoop == TRUE )
	        {
	            $sResult = $this->traceToProject($iLotteryId, $bIsLoop);
	            return $sMsg.$sResult;
	        }
	        else
	        {
	            return $sMsg.$sIssue." success [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]";
	        }
    	}
    	
    	$oUserFund   = A::singleton('model_userfund');   //用户资金模型
    	//06: 循环对每一个追号单进行转注单
    	$aTempData = array();//每单要处理的数据组合
    	foreach( $aTasks as $aTask )
    	{
    		//06 01: 生成数据
    		$bIsFinish = FALSE;//是否已完成
    		$iWinCount = 0;   //赢的期数
    		if( $aTask['issuecount'] == $aTask['finishedcount'] + 1 + $aTask['cancelcount'] )
    		{//总期数 == 已完成期数（前面+本次）+取消期数
    			$bIsFinish = TRUE;
    		}
    		//06 02: 获取该追号单赢的期数
    		$sSql = " SELECT COUNT(`projectid`) AS wincount FROM `projects` WHERE `taskid`='".$aTask['taskid']."'
    		          AND `isgetprize`='1' AND `iscancel`='0' LIMIT 1";
    		$aResult = $this->oDB->getOne($sSql);
    		if( $this->oDB->errno() > 0 )
    		{
    			$sMsg .= $aTask['issue']." get wincount [taskid:".$aTask['taskid']."]  error\r\n";
    			$iAffectedFailed += 1;
                continue; //继续下一个单处理
    		}
    		if( $aTask['wincount'] != $aResult['wincount'] )
    		{//是否需要更新赢的期数
    			$iWinCount = $aResult['wincount'];
    		}
    		if( ($aResult['wincount'] > 0 || $aTask['wincount'] > 0) && $aTask['stoponwin'] )
    		{//如果是追中停止并且已经有中奖的
    			//06 02 01: 对该追号单进行未生成注单的撤单
    			$oGame   = A::singleton("model_gamemanage");
    			$aResult = $oGame->cancelTask( $aTask['userid'], $aTask['taskid'], 0, 0, TRUE, $aResult['wincount'] );
    			if( $aResult !== TRUE )
    			{
    				$sMsg .= $aTask['issue']." stop task and cancel taskdetail[taskid:".$aTask['taskid']."]  error\r\n";
    				$iAffectedFailed += 1;
    			}
    			else
    			{
    				$iAffectedSuccess +=1;
    			}
                continue;
    		}
    		$aTask['prize']            = unserialize($aTask['prize']);
    		$aTask['userdiffpoints']   = unserialize($aTask['userdiffpoints']);
    		$aTempData['aCreateData']  = array(//加入游戏帐变
								    		'iLotteryId'      => $aTask['lotteryid'],
								    		'iMethodId'       => $aTask['methodid'],
								    		'iTaskId'         => $aTask['taskid'],
								    		'iFromUserId'     => $aTask['userid'],
								    		'iOrderType'      => 7,
								    		'fMoney'          => $aTask['singleprice'] * $aTask['multiple'],
								    		'sDescription'    => "当期追号返款",
								    		'iModesId'        => $aTask['modes'],
								    		'iChannelID'      => SYS_CHANNELID
    		                              );
    	    $aTempData['aProjectData'] = array(//方案表数据
                                            'userid'        => $aTask['userid'],
                                            'taskid'        => $aTask['taskid'],
                                            'lotteryid'     => $aTask['lotteryid'],
                                            'methodid'      => $aTask['methodid'],
                                            'packageid'     => $aTask['packageid'],
                                            'codetype'      => $aTask['codetype'],
                                            'issue'         => $aTask['issue'],
                                            'code'          => $aTask['codes'],
                                            'singleprice'   => $aTask['singleprice'],
    	                                    'multiple'      => $aTask['multiple'],
                                            'totalprice'    => $aTask['singleprice'] * $aTask['multiple'],
                                            'lvtopid'       => $aTask['lvtopid'],
                                            'lvtoppoint'    => $aTask['lvtoppoint'],
                                            'lvproxyid'     => $aTask['lvproxyid'],
    	                                    'userip'        => '127.0.0.1',
    	                                    'cdnip'         => '127.0.0.1',
    	                                    'modes'         => $aTask['modes'],
    	                                    'sqlnum'        => 1
                                          );
           $aTempData['aJoinData']     = array(//加入游戏帐变
                                            'iLotteryId'      => $aTask['lotteryid'],
                                            'iMethodId'       => $aTask['methodid'],
                                            'iProjectId'      => 0,
                                            'iTaskId'         => $aTask['taskid'],
                                            'iFromUserId'     => $aTask['userid'],
                                            'iOrderType'      => 3,
                                            'fMoney'          => $aTask['singleprice'] * $aTask['multiple'],
                                            'sDescription'    => "加入游戏",
                                            'iModesId'        => $aTask['modes'],
                                            'iChannelID'      => SYS_CHANNELID
                                          );
           $aTempData['aBackData']      = array(); //本人销售返点，只有返点大于0才有数据，默认为空，不插入数据
           $aTempData['aExpandData']    = $aTask['prize']['base'];
           foreach( $aTempData['aExpandData'] as & $vv )
           {
           	   $vv['prize'] = $vv['prize'] * $aTask['multiple'];
           }
           $aTempData['aDiffData']      = $aTask['userdiffpoints']['base'];
           foreach( $aTempData['aDiffData'] as $k=>$v )
           {
           	   if( $v['userid'] == $aTask['userid'] )
           	   {
           	   	   $aTempData['aBackData'] = array(//本人销售返点
                                            'iLotteryId'      => $aTask['lotteryid'],
                                            'iMethodId'       => $aTask['methodid'],
                                            'iProjectId'      => 0,
                                            'iTaskId'         => $aTask['taskid'],
                                            'iFromUserId'     => $aTask['userid'],
                                            'iOrderType'      => 4,
                                            'fMoney'          => $v['diffmoney'] * $aTask['multiple'], 
                                            'sDescription'    => "追号返点",
                                            'iModesId'        => $aTask['modes'],
                                            'iChannelID'      => SYS_CHANNELID
                                          );
           	   	   $aTempData['aDiffData'][$k]['status'] = 1; 
           	   }
           	   $aTempData['aDiffData'][$k]['diffmoney']  = $aTempData['aDiffData'][$k]['diffmoney'] * $aTask['multiple'];
           }
           //06 02: 开始事务写入数据
           //0602 01: 锁用户资金表[开始锁资金事务处理]---------------
            if( FALSE == $this->oDB->doTransaction() )
            {//事务处理失败
            	$iAffectedFailed += 1;
                return $aTask['issue']." doTransaction error #5011 
                       [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]\r\n";
            }
            if( intval($oUserFund->switchLock($aTask['userid'], SYS_CHANNELID, TRUE)) != 1 )
            {
            	$iAffectedFailed += 1;
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return $aTask['issue']." doRollback error #5012 
                            [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]\r\n";
                }
                $sMsg .= $aTask['issue']." lock userfund[".$aTask['userid']."]  error\r\n";
                $iAffectedFailed += 1;
                continue; //继续下一个单处理
            }
            if( FALSE == $this->oDB->doCommit() )
            {//事务提交失败
            	$iAffectedFailed += 1;
            	return $aTask['issue']." doCommit error #5013 
            	        [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]\r\n";
            }
            
            //0602 02: [开始数据流程事务处理]------------
            if( FALSE == $this->oDB->doTransaction() )
            {//事务处理失败
            	$iAffectedFailed += 1;
                $oUserFund->switchLock( $aTask['userid'], SYS_CHANNELID, FALSE );//解锁资金表
                return $aTask['issue']." doTransaction error #5011 
                         [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]\r\n";
            }
            $mResult = $this->traceToProjectData( $aTask['taskid'], $aTempData, $bIsFinish, $iWinCount );
            if( $mResult !== TRUE )
            {
            	$iAffectedFailed += 1;
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return $aTask['issue']." doRollback error #5012
                            [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]\r\n";
                }
                $oUserFund->switchLock( $aTask['userid'], SYS_CHANNELID, FALSE );//解锁资金表
                switch( $mResult )
                {
                	case 0  : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   param error #".$mResult."\r\n";
                	          break;
                	case -1 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   createdata error #".$mResult."\r\n";
                	          break;
                	case -2 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   insert project error #".$mResult."\r\n";
                	          break;
                	case -3 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   insert orders error #".$mResult."\r\n";
                	          break;
                	case -33 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
               	                        no money error #".$mResult."\r\n";
                	           break;
                	case -4 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   backdata error #".$mResult."\r\n";
                	          break;
                	case -5 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   expanddata error #".$mResult."\r\n";
                	          break;
                	case -6 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   diffdata error #".$mResult."\r\n";
                	          break;
                	case -7 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   update task error #".$mResult."\r\n";
                	          break;
                	case -8 : $sMsg .= $aTask['issue']." traceToProjectData(fun)[taskid:".$aTask['taskid']."] 
                	                   update detail error #".$mResult."\r\n";
                	          break;
                    default : return $sMsg."traceToProjectData(fun)[taskid:".$aTask['taskid']."] Unknow error "; break;
                }
                continue; //继续下一个单处理
            }
            //0602 03: 提交数据流程事务处理[结束] -----------------------
            if( FALSE == $this->oDB->doCommit() )
            {//事务提交失败
            	$iAffectedFailed += 1;
                return $aTask['issue']." doCommit error #5013 
                        [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]\r\n";
            }
            //0602 04: 解锁资金表 -------------------------------------
            $oUserFund->switchLock( $aTask['userid'], SYS_CHANNELID, FALSE );
            $iAffectedSuccess += 1;
    	}
    	
    	//06: 如果没有失败的则修改奖期表状态
    	if( $iAffectedFailed == 0 )
    	{
	    	$sSql = "UPDATE `issueinfo` SET `statustasktoproject`='2' WHERE `issueid`='".$iIssueId."'";
	        $this->oDB->query($sSql);
	        if( $this->oDB->errno() > 0 )
	        {
	            return " update ".$sIssue." [statustasktoproject] error 
	                    [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]\r\n" ;
	        }
    	}
        
        
    	//07: 执行成功返回TRUE
    	if( $bIsLoop == TRUE || --$iRunTimes > 0)
    	{
    		$sResult = $this->traceToProject($iLotteryId, $bIsLoop, $iRunTimes);
    		return $sMsg.$sResult;
    	}
    	else
    	{
    		return $sMsg.$sIssue." success [success:".$iAffectedSuccess.", failed:".$iAffectedFailed."]";
    	}
    }



     /**
     * 追号单转注单数据处理流程[非事务]
     *
     * @author  mark
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
        
        //01 ：写入方案表-----------------------------------------------------------
        $aDataArr['aProjectData']['taskid'] = $iTaskId;
        $iProjectId = $oProjects->projectsInsert( $aDataArr['aProjectData'] );
        if( $iProjectId <= 0 )
        {//写入方案失败
            return -2;
        }
        
        //02：写追号当期返款帐变-----------------------------------
        $aDataArr['aCreateData']['iTaskId'] = $iTaskId;
        $aDataArr['aCreateData']['iProjectId'] = $iProjectId;
        if( TRUE !== $oOrders->addOrders($aDataArr['aCreateData']) )
        {//帐变错误
            return -1;
        }
        
        //03：写加入游戏帐变以及用户资金扣钱-----------------------------------------
        $aDataArr['aJoinData']['iTaskId']    = $iTaskId;
        $aDataArr['aJoinData']['iProjectId'] = $iProjectId;
        $aDataArr['aJoinData']['bIgnoreMinus'] = TRUE;
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
        $sSql = " UPDATE `tasks` SET `updatetime`='".date("Y-m-d H:i:s")."',`finishedcount`=`finishedcount`+1,
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