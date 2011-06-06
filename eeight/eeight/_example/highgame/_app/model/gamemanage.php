<?php
/**
 * 文件 : /_app/model/gamemanage.php
 * 功能 : 数据模型 - 游戏信息管理模型[撤单等]
 * 
 * - cancelProject()        撤消注单
 * - cancelTask()           追号单撤单
 * - getHistoryBounsCode()  根据游戏ID获取历史中奖号码&号码遗漏数据
 * - getHotCode()  根据游戏ID获取冷热号码
 * 
 * @author     floyd    100210
 * @version    1.0.0
 * @package    highgame
 */

class model_gamemanage extends model_gamebase
{
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    /**
     * 撤消注单
     *
     * @param int $iUserId      //用户ID
     * @param int $iProjectId   //方案ID
     * @param int $iAdminId     //管理员ID
     * @return mixed    //成功返回TRUE，失败返回失败消息
     */
    public function cancelProject( $iUserId, $iProjectId, $iAdminId=0 )
    {
    	if( empty($iUserId) || empty($iProjectId) || !is_numeric($iUserId) || !is_numeric($iProjectId) )
    	{//参数错误
    		return "参数不对";
    	}
    	$iUserId    = intval( $iUserId );
    	$iProjectId = intval( $iProjectId );
    	$iAdminId   = intval( $iAdminId );
    	/**
    	 * 01: 先判断是否有权限撤单[管理员和用户有所不同]
    	 */
    	//01 01: 获取方案和玩法以及奖期信息
    	$sSql = " SELECT p.*,m.`islock`,m.`lockname`,i.`canneldeadline`,i.`salestart`,i.`saleend`,i.`writetime`
    	          FROM `projects` AS p 
    	          LEFT JOIN `method` AS m ON (p.`lotteryid`=m.`lotteryid` AND p.`methodid`=m.`methodid`)
    	          LEFT JOIN `issueinfo` AS i ON (p.`lotteryid`=i.`lotteryid` AND p.`issue`=i.`issue` 
    	          AND m.`lotteryid`=i.`lotteryid`)
    	          WHERE p.`userid`='".$iUserId."' AND p.`projectid`='".$iProjectId."' LIMIT 1";
    	$aGameInfo = $this->oDB->getOne($sSql);
    	if( empty($aGameInfo) )
    	{//没有权限
    		return "没有权限";
    	}
    	//01 02: 如果已经撤单则返回TRUE
    	if( $aGameInfo['iscancel'] != 0 )
    	{
    		return TRUE;
    	}
    	//01 03: 已经派奖的不能撤单(必须先撤销)
    	if( $aGameInfo['prizestatus'] != 0 )
    	{
    		return "系统已派奖，不能撤单";
    	}
        $iNowTime   = time();     //设置当前时间，避免后面多次调用
        //01 03[m]: 判断时间是否过了系统设置的允许撤单时间范围
        $aGameInfo['saleend'] = strtotime( $aGameInfo['saleend'] );
		/* @var $oConfig model_config */
		$oConfig = A::singleton("model_config");
		$iTempLimitMinute = $oConfig->getConfigs( "admincancellimit" );
		$iTempLimitMinute = empty($iTempLimitMinute) ? 30 : intval($iTempLimitMinute);
		if( $iNowTime > ($aGameInfo['saleend'] + ($iTempLimitMinute*60)) )
		{//时间已过
			return "撤单时间已过，不能撤单";
		}
    	//01 04: 用户附加条件限制
    	if( $iAdminId == 0 )
    	{
	        //01 04 02: 是否过了撤单时间
	        if( strtotime($aGameInfo['canneldeadline']) < $iNowTime )
	        {//过了撤单时间
	            return "撤单时间已过，不能撤单";
	        }
    	}

    	/**
    	 * 02: 构建撤单数据
    	 */
    	$aLocksData = array();  //封锁表更新数据
    	$aSaleData  = array();  //销量表数据
    	$aOrdersData= array();  //资金、帐变数据
    	$aStatusData= array();  //更改状态数据
    	//02 01: 更新封锁表数据
    	if( $aGameInfo['islock'] > 0 && !empty($aGameInfo['lockname']) )
    	{
    	    //如果有封锁则构建更新封锁表数据
	    	//02 01 00: 获取号码扩展表数据
	        $sSql = "SELECT `prize`,`expandcode`,`isspecial`,`level`, `codetimes` FROM `expandcode` 
	                 WHERE `projectid`='".$iProjectId."' ORDER BY level ASC";
	        $aExpandData = $this->oDB->getAll($sSql);
	        if( !$aExpandData )
	        {//数据错误
	            return "操作错误";
	        }
	        //02 01 01: 判断该使用历史封锁表还是当前封锁表
	        $sLockTable = $aGameInfo['lockname'];
	        if( strtotime($aGameInfo['saleend']) < $iNowTime )
	        {//销售已截止
	            //02 01 02 00: 检测封锁数据是否已到了历史表
	            $sSql = " SELECT `issue` FROM `history_".$sLockTable."` 
	                      WHERE `issue`='".$aGameInfo['issue']."' LIMIT 1";
	            $this->oDB->query($sSql);
	            if( $this->oDB->errno() > 0 )
	            {//数据错误
	            	return "操作错误";
	            }
	            if( $this->oDB->ar() > 0 )
	            {//已到了历史表
	            	$sLockTable = 'history_' . $sLockTable;
	            }
	        }
	        // 02 01 03: 获取封锁表更新条件
	        $aLocksData = array();

			foreach($aExpandData as $j=>$aE)
	        {
    	        //02 01 01: 根据号码扩展获取封锁值更新条件
    	        $aCondition = $this->getLocksCondition( $aGameInfo['methodid'], 
    	                                                $aGameInfo['codetype'], 
    	                                                $aE['expandcode'] );
    	        if( !($aCondition) )
    	        {//数据错误
    	            return "操作错误";
    	        }
				//file_put_contents('./log.txt', print_r($aCondition, true));
    	        foreach($aCondition AS $k=>$c)
    	        {
	                switch( $this->_aMethod_Config[$aGameInfo['methodid']] )
	                {
	                    case 'QHHZX' :
	                    case 'HHHZX' ://混合组选
	                    case 'QZUHZ' :
	                    case 'HZUHZ' ://组选和值
						case 'ZU3BD' : //组三包胆
							$aLocksData[] = "UPDATE `" . $sLockTable . "` SET `prizes`=`prizes`-" . $aE['prize']*$c['times'] . 
									" WHERE `issue`='" . $aGameInfo['issue'] . 
									"' AND `stamp`='".$aE['level']."' AND " . $c['condition'];//组三的号码
	                        break;
						case 'TSH3': 
							 if( $aE['expandcode'] == 0 )
							 {
								 $aLocksData[] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$c['times'].
									 " WHERE `issue`='".$aGameInfo['issue']."' AND `code`='0' AND ".$c['condition'];//豹子
							 }
							 if( $aE['expandcode'] == 1 )
							 {
								 $aLocksData[] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$c['times'].
									 " WHERE `issue`='".$aGameInfo['issue']."' AND `code`='1' AND ".$c['condition'];//顺子
							 }
							 if( $aE['expandcode'] == 2 )
							 {
								 $aLocksData[] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$c['times'].
									 " WHERE `issue`='".$aGameInfo['issue']."' AND `code`='2' AND ".$c['condition'];//对子
							 }
							break;
						case 'ZH3':
								$aCode =  explode("|",$aE['expandcode']);
								if($j == 0)
								{
									 $aLocksData[0] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize'] * $c['times']. " WHERE `issue`='".$aGameInfo['issue']."' AND ".$aCondition[0]['condition'];
								}
								if($j == 1)
								{
									 $aLocksData[1] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize'] * strlen($aCode[0]) * $c['times']. " WHERE `issue`='".$aGameInfo['issue']."' AND ".$aCondition[1]['condition'];
								}
								if($j == 2)
								{
									 $aLocksData[2] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize'] * strlen($aCode[1]) * strlen($aCode[0]) * $c['times']. " WHERE `issue`='".$aGameInfo['issue']."' AND ".$aCondition[2]['condition'];
								}
							break;
						case '3QW':
						case '3QJ':
								$aCode =  explode("|",$aE['expandcode']);
								if($j == 0 )
								{
									$aLocksData[0] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
												 (($aExpandData[0]['prize'] + $aExpandData[1]['prize'] * (strlen($aCode[0])-1))* $c['times']).
												 " WHERE `issue`='".$aGameInfo['issue']."' AND ".$aCondition[0]['condition'];//一等奖
								}
								
								if($j == 1)
								{
										$aLocksData[1] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
													 (($aExpandData[1]['prize'] * strlen($aCode[0])) * $c['times']).
													 " WHERE `issue`='".$aGameInfo['issue']."' AND ".$aCondition[1]['condition'];//二等奖
								}
							break;
						case 'TX3':
								$aCode =  explode("|",$aE['expandcode']);
								$n1 = strlen($aCode[0]);
								$n2 = strlen($aCode[1]);
								$n3 = strlen($aCode[2]);
								if($k == 0 )	//一等奖
								{
									$prize = $aExpandData[0]['prize'] + $aExpandData[1]['prize'] * ($n1-1+$n2-1+$n3-1) + (($n1-1)*($n2-1)+($n1-1)*($n3-1)+($n2-1)*($n3-1)) * $aExpandData[2]['prize'];

									$aLocksData[0] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".($prize * $c['times'])." WHERE `issue`='".$aGameInfo['issue']."' AND ".$aCondition[0]['condition'];
								}
								else if($k == 1) //二等奖-前2
								{
									$prize = $n3 * $aExpandData[1]['prize'] + ($n1-1+$n2-1) * $n3 * $aExpandData[2]['prize'];
									$aLocksData[1] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
													 ($prize * $c['times'])." WHERE `issue`='".$aGameInfo['issue']."' AND ".$c['condition'];
								}
								else if($k == 2) //二等奖-1/3
								{
									$prize = $n2 * $aExpandData[1]['prize'] + ($n1-1+$n3-1) * $n2 * $aExpandData[2]['prize'];
									$aLocksData[2] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".($prize * $c['times'])." WHERE `issue`='".$aGameInfo['issue']."' AND ".$c['condition'];
								}
								else if($k == 3) //二等奖-后2
								{
									$prize = $n1 * $aExpandData[1]['prize'] + ($n2-1+$n3-1) * $n1 * $aExpandData[2]['prize'];
									$aLocksData[3] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
													 ($prize * $c['times']).
													 " WHERE `issue`='".$aGameInfo['issue']."' AND ".$c['condition'];
								}
								else if($k == 4) //三等奖-1
								{
									$prize = $n2 * $n3 * $aExpandData[2]['prize'];
									$aLocksData[4] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
													 ($prize * $c['times']).
													 " WHERE `issue`='".$aGameInfo['issue']."' AND ".$c['condition'];
								}
								else if($k == 5) //三等奖-2
								{
									$prize = $n1 * $n3 * $aExpandData[2]['prize'];
									$aLocksData[5] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
													 ($prize * $c['times']).
													 " WHERE `issue`='".$aGameInfo['issue']."' AND ".$c['condition'];
								}
								else if($k == 6) //三等奖-3
								{
									$prize = $n1 * $n2 * $aExpandData[2]['prize'];
									$aLocksData[6] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
													 ($prize * $c['times']).
													 " WHERE `issue`='".$aGameInfo['issue']."' AND ".$c['condition'];
								}
							break;
	                    default      :
	                        $aLocksData[] = "UPDATE `" . $sLockTable . "` SET `prizes`=`prizes`-" . $aE['prize']*$c['times'] . 
	                             " WHERE `issue`='" . $aGameInfo['issue'] . 
	                             "' AND " . $c['condition'];
	                        break;
	                }
    	        }
				//file_put_contents('./log.txt', print_r($aLocksData, true));
	        }
    	}
    	//02 02： 更新销量表数据
    	$aSaleData[] = array(
			                   'issue'     => $aGameInfo['issue'],
			                   'lotteryid' => $aGameInfo['lotteryid'],
			                   'lockname'  => $aGameInfo['lockname'], //封锁表名称
			                   'moneys'    => (-1) * ($aGameInfo['totalprice']),
                               'pointmoney'=> (-1) * ($aGameInfo['totalprice'] * $aGameInfo['lvtoppoint'])
		                    );
	    //02 03: 资金、帐变数据
        //02 03 01: 撤单返款数据
        if( $aGameInfo['isdeduct'] == 1 )
        {//已完成真实扣款
        	$aOrdersData['fk'] = array(
                                        'iLotteryId'   => $aGameInfo['lotteryid'],
                                        'iMethodId'    => $aGameInfo['methodid'],
                                        'iTaskId'      => $aGameInfo['taskid'],
                                        'iProjectId'   => $aGameInfo['projectid'],
                                        'iFromUserId'  => $aGameInfo['userid'],
                                        'iOrderType'   => 99, //撤单返款[特殊的已完成真实扣款的]
                                        'fMoney'       => $aGameInfo['totalprice'],
                                        'sDescription' => '撤单返款',      //帐变描述
                                        'iAdminId'     => $iAdminId,
                                        'iChannelID'   => SYS_CHANNELID,
                                        'iModesId'       => $aGameInfo['modes']
                                    );
            //更新方案表数据
            $aStatusData[] = array(
                                    'sql'      => "UPDATE `projects` SET `updatetime`='".date("Y-m-d H:i:s")."',`iscancel`='".($iAdminId>0 ? 2 : 1)."',`canceltime`='".date("Y-m-d H:i:s")."' 
                                                  WHERE `isdeduct`='1' AND `iscancel`='0' AND `prizestatus`='0'
                                                  AND `projectid`='".$iProjectId."' AND `userid`='".$iUserId."' ",
                                    'affected' => 1   );
        }
        else
        {//未完成真实扣款
        	$aOrdersData['fk'] = array(
        	                                   'iLotteryId'   => $aGameInfo['lotteryid'],
					                           'iMethodId'    => $aGameInfo['methodid'],
        	                                   'iTaskId'      => $aGameInfo['taskid'],
					                           'iProjectId'   => $aGameInfo['projectid'],
					                           'iFromUserId'  => $aGameInfo['userid'],
					                           'iOrderType'   => 9, //撤单返款
					                           'fMoney'       => $aGameInfo['totalprice'],
					                           'sDescription' => '撤单返款',      //帐变描述
        	                                   'iAdminId'     => $iAdminId,
					                           'iChannelID'   => SYS_CHANNELID,
                                               'iModesId'       => $aGameInfo['modes']
        	                             );
        	//更新方案表数据
        	$aStatusData[] = array(
                                    'sql'      => "UPDATE `projects` SET `updatetime`='".date("Y-m-d H:i:s")."',`iscancel`='".($iAdminId>0 ? 2 : 1)."',`canceltime`='".date("Y-m-d H:i:s")."' 
                                                   WHERE `isdeduct`='0' AND `iscancel`='0' AND `prizestatus`='0'
                                                   AND `projectid`='".$iProjectId."' AND `userid`='".$iUserId."' ",
                                    'affected' => 1   );
        }
        //02 03 02: 撤单手续费数据
        if( $iAdminId == 0 && $aGameInfo['totalprice'] >= intval(getConfigValue( 'bigordercancel', 10000 )) )
        {//如果是用户撤单并且达到收取撤单手续费的界限
        	$fTempSXF = $aGameInfo['totalprice'] * floatval(getConfigValue( 'bigordercancelpre', 0.01 ));
        	$aOrdersData['sxf'] = array(
                                               'iLotteryId'   => $aGameInfo['lotteryid'],
                                               'iMethodId'    => $aGameInfo['methodid'],
                                               'iTaskId'      => $aGameInfo['taskid'],
                                               'iProjectId'   => $aGameInfo['projectid'],
                                               'iFromUserId'  => $aGameInfo['userid'],
                                               'iOrderType'   => 10, //撤单手续费
                                               'fMoney'       => $fTempSXF,
                                               'sDescription' => '撤单手续费',      //帐变描述
                                               'iAdminId'     => $iAdminId,
                                               'iChannelID'   => SYS_CHANNELID,
                                               'iModesId'       => $aGameInfo['modes']
                                        );
        }
        //02 03 03: 获取该方案的所有返点数据
        $sSql       = " SELECT `userid`,`diffmoney`,`diffpoint` FROM `userdiffpoints` WHERE 
                        `projectid`='".$iProjectId."' AND `status`='1' AND `cancelstatus`='0' ";
        $aDiffPoint = $this->oDB->getAll($sSql);
        if( $this->oDB->errno() > 0 )
        {
        	return "操作错误";
        }
        $aLockFundUsers   = array();
        $aLockFundUsers[] = $iUserId;
        if( !empty($aDiffPoint) )
        {//如果有返点数据
        	foreach( $aDiffPoint as $v )
        	{
        		$aLockFundUsers[] = $v['userid'];
        		$aOrdersData[]    = array(
                                    'iLotteryId'   => $aGameInfo['lotteryid'],
                                    'iMethodId'    => $aGameInfo['methodid'],
                                    'iTaskId'      => $aGameInfo['taskid'],
                                    'iProjectId'   => $aGameInfo['projectid'],
                                    'iFromUserId'  => $v['userid'],
                                    'iToUserId'    => $v['userid'] == $aGameInfo['userid'] ? 0 : $aGameInfo['userid'],
                                    'iOrderType'   => 11, //撤销返点
                                    'fMoney'       => $v['diffmoney'],
                                    'sDescription' => '撤销返点',      //帐变描述
                                    'iAdminId'     => $iAdminId,
                                    'iChannelID'   => SYS_CHANNELID,
                                    'iModesId'       => $aGameInfo['modes'],
									'bIgnoreMinus'	=> $v['userid'] == $aGameInfo['userid'] ? TRUE : FALSE,
                                           );
        	}
        }
        //02 03 04: 更改状态数据
        //02 03 04 01: 追号表数据更改
        if( $aGameInfo['taskid'] > 0 )
        {//
        	$aStatusData[] = array(//追号表
                   'sql'      => "UPDATE `tasks` SET `updatetime`='".date("Y-m-d H:i:s")."',`finishedcount`=`finishedcount`-1,
                                 `cancelcount`=`cancelcount`+1,`finishprice`=`finishprice`-".$aGameInfo['totalprice'].",
                                 `cancelprice`=`cancelprice`+".$aGameInfo['totalprice']." 
                                  WHERE `taskid`='".$aGameInfo['taskid']."' AND `userid`='".$iUserId."' ",
                   'affected' => 1   );
            $aStatusData[] = array(//追号详情表
                   'sql'      => "UPDATE `taskdetails` SET `status`='2' WHERE `taskid`='".$aGameInfo['taskid']."' 
                                  AND `projectid`='".$iProjectId."' AND `status`='1'",
                   'affected' => 1   );
        }
        //02 03 04 02: 返点表数据修改
        $aStatusData[] = array(
                          'sql' => "UPDATE `userdiffpoints` SET `status`='2', 
        `cancelstatus`='".($iAdminId>0 ? 3 : 1)."' WHERE `projectid`='".$iProjectId."' AND `status`!='2'
        AND `cancelstatus`='0'" );
        /**
         * 03: 开始写入数据 ========================================================
         */
        /* @var $oUserFund model_userfund */
        $oUserFund      = A::singleton('model_userfund'); //用户资金模型
        $aLockFundUsers = array_unique($aLockFundUsers);
        $sLockFundUsers = implode( ",", $aLockFundUsers );
        //03 01: 锁用户资金表[开始锁资金事务处理]---------------
        if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            return "系统错误：错误编号:#5011";
        }

        if( intval($oUserFund->switchLock($sLockFundUsers, SYS_CHANNELID, TRUE)) != count($aLockFundUsers) )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return "系统错误：错误编号:#5012";
            }
            return "资金帐户因为其他操作被锁定，请稍后重试";
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return "系统错误：错误编号:#5013";
        }
        //03 02: [开始投单流程事务处理]------------
        if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            $oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );//解锁资金表
            return "系统错误：错误编号:#5011";
        }
        $mResult = $this->cancelUpdateData( $aSaleData, $aOrdersData, $aStatusData, $aLocksData );
        if( $mResult === -33 )
        {//资金不够
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return "系统错误：错误编号:#5012";
            }
            $oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );//解锁资金表
            return "余额不足";
        }
        elseif( $mResult !== TRUE )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return "系统错误：错误编号:#5012";
            }
            $oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );//解锁资金表
            return "撤单失败，请重试(errno=$mResult)";
        }
        //03 03: 提交投单流程事务处理[结束] -----------------------
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return "系统错误：错误编号:#5013";
        }
        //03 04: 解锁资金表 -------------------------------------
        $oUserFund->switchLock( $sLockFundUsers, SYS_CHANNELID, FALSE );

        //处理完成
        return TRUE;
    }
    
    /**
     * 追号单撤单
     *
     * @param int    $iUserId   //用户ID
     * @param int    $iTaskId   //追号单ID
     * @param mixed    $mDetailId //追号详情ID[单期撤单传入]
     * @param int    $iAdminId  //管理员ID
     * @param boolean $bCheckLocks  //是否检测封锁表位置
     * @param int    $iWinCount //赢的期数（用于追号单转注单CLI调用[追中停止撤单]）
     * @return mixed    //成功返回TRUE，失败返回失败消息
     */
    public function cancelTask( $iUserId, $iTaskId, $mDetailId=0, $iAdminId=0, $bCheckLocks= FALSE, $iWinCount=0 )
    {
    	if( empty($iUserId) || empty($iTaskId) || !is_numeric($iUserId) || !is_numeric($iTaskId) )
        {//参数错误
            return "没有权限";
        }
        $iUserId    = intval($iUserId);
        $iTaskId    = intval($iTaskId);
        if( is_array($mDetailId) )
        { //数组处理
        	foreach( $mDetailId as &$v )
        	{
        		if( !is_numeric($v) )
        		{
        			unset($v);
        		}
        	}
        	if( empty($mDetailId) )
        	{
        		return "操作失败";
        	}
        }
        else
        {
            $mDetailId  = intval($mDetailId);
        }
        $iAdminId   = intval($iAdminId);
        $bCheckLocks= $bCheckLocks === TRUE ? TRUE : FALSE;
        $iWinCount  = intval($iWinCount);
        
        //01: 先获取追号表数据
        $sSql = "SELECT t.*,m.`lockname`,l.`locktablename` FROM `tasks` AS t 
                 LEFT JOIN `method` AS m ON (t.`lotteryid`=m.`lotteryid` AND t.`methodid`=m.`methodid`)
                 LEFT JOIN `locktablename` AS l ON m.`lockname`=l.`locktablename`
                 WHERE `taskid`='".$iTaskId."' AND `userid`='".$iUserId."' LIMIT 1";
        $aTaskData = $this->oDB->getOne($sSql);
        if( empty($aTaskData) )
        {//没有找到数据
        	return "没有权限";
        }
        if( $aTaskData['status'] != 0 )
        {//已完成或者已撤消
        	return "操作失败";
        }
        if( $aTaskData['issuecount'] == ($aTaskData['finishedcount'] + $aTaskData['cancelcount']) ||
            $aTaskData['taskprice']  == ($aTaskData['finishprice'] + $aTaskData['cancelprice']) )
        {//已完成
        	return "操作失败";
        }
        
        //02： 根据是否撤消单期或者整个追号单读取不同数据
        if( is_array($mDetailId) )
        {
        	//第一步,先进行撤单
        	$sSqlProject = "SELECT `entry`,`issue`,`multiple`,`projectid` FROM `taskdetails` WHERE `taskid`='".$iTaskId."'"
                      ."AND `projectid`>'0' AND `status`='1' AND `entry` in (".join(",",$mDetailId).") LIMIT 1";
            $aProject = $this->oDB->getOne( $sSqlProject );
            if(!empty($aProject))
            {
            	$mResult = $this->cancelProject($iUserId, $aProject["projectid"], $iAdminId);
            	if( $mResult!==TRUE )
            	{
            		return $mResult;
            	}
            }
        	$sSql = " SELECT `entry`,`issue`,`multiple` FROM `taskdetails` WHERE `taskid`='".$iTaskId."'"
                      ."AND `projectid`='0' AND `status`='0' AND `entry` in (".join(",",$mDetailId).") "; 
            $aTaskDetails = $this->oDB->getAll( $sSql );
            if( empty($aTaskDetails) )
            {//没有可取消的期
            	if( !empty($aProject) )
            	{
                    return $mResult;
            	}
            	else
            	{
                    return "操作错误";
            	}
            }
        }
        elseif( $mDetailId <= 0 )
        {//如果是终止追号则读取所有未生成注单的信息
        	$sSql = " SELECT `entry`,`issue`,`multiple` FROM `taskdetails` WHERE `taskid`='".$iTaskId."'
        	          AND `projectid`='0' AND `status`='0'";
        	$aTaskDetails = $this->oDB->getAll( $sSql );
        	if( empty($aTaskDetails) )
        	{//没有可取消的期
        		return "操作错误";
        	}
        }
        else
        {//指定某期的话直接读取一条记录
            $sSql = " SELECT `entry`,`issue`,`multiple` FROM `taskdetails` WHERE `taskid`='".$iTaskId."'
                      AND `projectid`='0' AND `status`='0' AND `entry`='".$mDetailId."' LIMIT 1";
            $aTaskDetails = $this->oDB->getOne( $sSql );
            if( empty($aTaskDetails) )
            {//没有可取消的期
                return "操作错误";
            }
            $aTaskDetails = array( $aTaskDetails );
        }
        
        /**
         * 03: 构建更新数据
         */
        $aLocksData         = array();  //封锁表更新数据
        $aSaleData          = array();  //销量表数据
        $aOrdersData        = array();  //资金、帐变数据
        $aStatusData        = array();  //更改状态数据
        $fCancelMoney       = 0;        //取消的总金额
        $aTaskData['prize'] = unserialize($aTaskData['prize']); //每期的号码扩展表数据
        $aTempIssue         = array();   //取消的期数[存detail的自增ID]
        //03 01： 循环取消的每期构建封锁表和销量表数据
        foreach( $aTaskDetails as $v )
        {
        	//03 01 01: 封锁表数据
        	if( !empty($aTaskData['lockname']) )
        	{
        		$sLockTable = $aTaskData['lockname'];
        		if( $bCheckLocks == TRUE )
        		{//如果需要检测封锁表位置
	        		//03 01 01 00: 检测封锁数据是否已到了历史表
	                $sSql = " SELECT `issue` FROM `history_".$aTaskData['lockname']."` 
	                          WHERE `issue`='".$v['issue']."' LIMIT 1";
	                $this->oDB->query($sSql);
	                if( $this->oDB->errno() > 0 )
	                {//数据错误
	                    return "操作错误";
	                }
	                if( $this->oDB->ar() > 0 )
	                {//已到了历史表
	                    $sLockTable = 'history_' . $aTaskData['lockname'];
	                }
	                //03 01 01 01: 检测封锁表数据是否到了当期封锁表
        		    $sSql = " SELECT `issue` FROM `".$aTaskData['lockname']."` 
        		              WHERE `issue`='".$v['issue']."' LIMIT 1";
                    $this->oDB->query($sSql);
                    if( $this->oDB->errno() > 0 )
                    {//数据错误
                        return "操作错误";
                    }
                    if( $this->oDB->ar() > 0 )
                    {//已到了当期封锁表
                		$sLockTable = $aTaskData['lockname'];
                    }
        		}
    	        foreach($aTaskData['prize']['base'] as $j=>$aE)
    	        {
        	        //02 01 01: 根据号码扩展获取封锁值更新条件
        	        $aCondition = $this->getLocksCondition( $aTaskData['methodid'], $aTaskData['codetype'], $aE['expandcode'] );
        	        if( !($aCondition) )
        	        {//数据错误
        	            return "操作错误";
        	        }
        	        foreach($aCondition AS $k=>$c)
        	        {
	        	        switch( $this->_aMethod_Config[$aTaskData['methodid']] )
	                    {
	                        case 'QHHZX' :
	                        case 'HHHZX' ://混合组选
	                        case 'QZUHZ' :
	                        case 'HZUHZ' ://组选和值
							case 'ZU3BD' : //组三包胆
								$aLocksData[] = "UPDATE `" . $sLockTable . "` SET `prizes`=`prizes`-" . 
										($aE['prize']*$v['multiple']*$c['times']) . " WHERE `issue`='" . 
										$v['issue'] . "' AND `stamp`='".$aE['level']."' AND " . $c['condition'];//组三的号码
									break;
							case 'TSH3': 
								 if( $aE['expandcode'] == 0 )
								 {
									 $aLocksData[] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$v['multiple']*$c['times'].
										 " WHERE `issue`='".$v['issue']."' AND `code`='0' AND ".$c['condition'];//豹子
								 }
								 if( $aE['expandcode'] == 1 )
								 {
									 $aLocksData[] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$v['multiple']*$c['times'].
										 " WHERE `issue`='".$v['issue']."' AND `code`='1' AND ".$c['condition'];//顺子
								 }
								 if( $aE['expandcode'] == 2 )
								 {
									 $aLocksData[] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$v['multiple']*$c['times'].
										 " WHERE `issue`='".$v['issue']."' AND `code`='2' AND ".$c['condition'];//对子
								 }
								break;
							case 'ZH3':
									$aCode =  explode("|",$aE['expandcode']);
									if($j == 0)
									{
										 $aLocksData[0] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$v['multiple'] * $c['times']. " WHERE `issue`='".$v['issue']."' AND ".$aCondition[0]['condition'];
									}
									if($j == 1)
									{
										 $aLocksData[1] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$v['multiple'] * strlen($aCode[0]) * $c['times']. " WHERE `issue`='".$v['issue']."' AND ".$aCondition[1]['condition'];
									}
									if($j == 2)
									{
										 $aLocksData[2] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".$aE['prize']*$v['multiple'] * strlen($aCode[1]) * strlen($aCode[0]) * $c['times']. " WHERE `issue`='".$v['issue']."' AND ".$aCondition[2]['condition'];
									}
								break;
							case '3QW':
							case '3QJ':
									$aCode =  explode("|",$aE['expandcode']);
									if($j == 0 )
									{
										$aLocksData[0] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
													 (($aTaskData['prize']['base'][0]['prize'] + $aTaskData['prize']['base'][1]['prize'] * (strlen($aCode[0])-1))*$v['multiple']* $c['times']).
													 " WHERE `issue`='".$v['issue']."' AND ".$aCondition[0]['condition'];//一等奖
									}
									
									if($j == 1)
									{
											$aLocksData[1] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
														 (($aTaskData['prize']['base'][1]['prize'] * strlen($aCode[0]))*$v['multiple'] * $c['times']).
														 " WHERE `issue`='".$v['issue']."' AND ".$aCondition[1]['condition'];//二等奖
									}
								break;
							case 'TX3':
									$aCode =  explode("|",$aE['expandcode']);
									$n1 = strlen($aCode[0]);
									$n2 = strlen($aCode[1]);
									$n3 = strlen($aCode[2]);
									if($k == 0 )	//一等奖
									{
										$prize = $aTaskData['prize']['base'][0]['prize'] + $aTaskData['prize']['base'][1]['prize'] * ($n1-1+$n2-1+$n3-1) + (($n1-1)*($n2-1)+($n1-1)*($n3-1)+($n2-1)*($n3-1)) * $aTaskData['prize']['base'][2]['prize'];

										$aLocksData[0] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".($prize*$v['multiple'] * $c['times'])." WHERE `issue`='".$v['issue']."' AND ".$aCondition[0]['condition'];
									}
									else if($k == 1) //二等奖-前2
									{
										$prize = $n3 * $aTaskData['prize']['base'][1]['prize'] + ($n1-1+$n2-1) * $n3 * $aTaskData['prize']['base'][2]['prize'];
										$aLocksData[1] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
														 ($prize*$v['multiple'] * $c['times'])." WHERE `issue`='".$v['issue']."' AND ".$c['condition'];
									}
									else if($k == 2) //二等奖-1/3
									{
										$prize = $n2 * $aTaskData['prize']['base'][1]['prize'] + ($n1-1+$n3-1) * $n2 * $aTaskData['prize']['base'][2]['prize'];
										$aLocksData[2] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".($prize*$v['multiple'] * $c['times'])." WHERE `issue`='".$v['issue']."' AND ".$c['condition'];
									}
									else if($k == 3) //二等奖-后2
									{
										$prize = $n1 * $aTaskData['prize']['base'][1]['prize'] + ($n2-1+$n3-1) * $n1 * $aTaskData['prize']['base'][2]['prize'];
										$aLocksData[3] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
														 ($prize*$v['multiple'] * $c['times']).
														 " WHERE `issue`='".$v['issue']."' AND ".$c['condition'];
									}
									else if($k == 4) //三等奖-1
									{
										$prize = $n2 * $n3 * $aTaskData['prize']['base'][2]['prize'];
										$aLocksData[4] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
														 ($prize*$v['multiple'] * $c['times']).
														 " WHERE `issue`='".$v['issue']."' AND ".$c['condition'];
									}
									else if($k == 5) //三等奖-2
									{
										$prize = $n1 * $n3 * $aTaskData['prize']['base'][2]['prize'];
										$aLocksData[5] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
														 ($prize*$v['multiple'] * $c['times']).
														 " WHERE `issue`='".$v['issue']."' AND ".$c['condition'];
									}
									else if($k == 6) //三等奖-3
									{
										$prize = $n1 * $n2 * $aTaskData['prize']['base'][2]['prize'];
										$aLocksData[6] = "UPDATE `".$sLockTable."` SET `prizes`=`prizes`-".
														 ($prize*$v['multiple'] * $c['times']).
														 " WHERE `issue`='".$v['issue']."' AND ".$c['condition'];
									}
								break;

								default      :
									$aLocksData[] = "UPDATE `" . $sLockTable . "` SET `prizes`=`prizes`-" . 
										 ($aE['prize']*$v['multiple']*$c['times']). " WHERE `issue`='" . 
										 $v['issue'] . "' AND " . $c['condition'];
									break;
	                    }
        	        }
    	        }
        	}
        	//03 01 02: 销量表数据
        	$aSaleData[] = array(
                      'issue'     => $v['issue'],
                      'lotteryid' => $aTaskData['lotteryid'],
                      'lockname'  => $aTaskData['lockname'], //封锁表名称
                      'moneys'    => (-1) * ($aTaskData['singleprice'] * $v['multiple']),
                      'pointmoney'=> (-1) * ($aTaskData['singleprice'] * $v['multiple']) * $aTaskData['lvtoppoint']
                   );
            //03 01 03: 取消的总金额
            $fCancelMoney +=  ($aTaskData['singleprice'] * $v['multiple']);
            $aTempIssue[]  = $v['entry'];
        }
        
        //03 02: 撤单返款帐变
        $aOrdersData['fk'] = array(
                                    'iLotteryId'   => $aTaskData['lotteryid'],
                                    'iMethodId'    => $aTaskData['methodid'],
                                    'iTaskId'      => $aTaskData['taskid'],
                                    'iProjectId'   => 0,
                                    'iFromUserId'  => $aTaskData['userid'],
                                    'iOrderType'   => 9, //撤单返款
                                    'fMoney'       => $fCancelMoney,
                                    'sDescription' => '撤单返款',      //帐变描述
                                    'iAdminId'     => $iAdminId,
                                    'iChannelID'   => SYS_CHANNELID,
                                    'iModesId'     => $aTaskData['modes']
                                );
        //03 03: 更改状态数据
        $iTaskStatus = 0;
        if( is_numeric($mDetailId)&&($mDetailId <= 0) )
        {//终止追号
        	$iTaskStatus = $iWinCount > 0 ? 2 : 1;
        }
        elseif( $aTaskData['issuecount'] == ($aTaskData['finishedcount']+$aTaskData['cancelcount']+count($aTempIssue)) )
        {//如果做了该次取消后，追号再没有是数据则更改状态为完成
        	$iTaskStatus = 2;
        }
        $aStatusData[] = array(//追号表
                      'sql'      => " UPDATE `tasks` SET `updatetime`='".date("Y-m-d H:i:s")."',`cancelcount`=`cancelcount`+".count($aTempIssue).",
                                      `cancelprice`=`cancelprice`+".$fCancelMoney.",`status`='".$iTaskStatus."'
                                      ".($iWinCount > 0 ? ",`wincount`='".$iWinCount."'" : "")." 
                                      WHERE `taskid`='".$iTaskId."' AND `userid`='".$iUserId."' AND `status`='0' ",
                      'affected' => 1   );
        $aStatusData[] = array(//追号详情表
                      'sql'      => " UPDATE `taskdetails` SET `status`='2' 
                                      WHERE `taskid`='".$iTaskId."' AND `projectid`='0' AND `status`='0' 
                                      AND `entry` IN(".implode(",",$aTempIssue).")  ",
                      'affected' => count($aTempIssue)   );
        /**
         * 04: 开始写入数据 ========================================================
         */
        $oUserFund      = A::singleton('model_userfund'); //用户资金模型
        //04 01: 锁用户资金表[开始锁资金事务处理]---------------
        if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            return "系统错误：错误编号:#5011";
        }
        if( intval($oUserFund->switchLock($iUserId, SYS_CHANNELID, TRUE)) != 1 )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return "系统错误：错误编号:#5012";
            }
            return "资金帐户因为其他操作被锁定，请稍后重试";
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return "系统错误：错误编号:#5013";
        }
        //04 02: [开始投单流程事务处理]------------
        if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
            return "系统错误：错误编号:#5011";
        }
        $mResult = $this->cancelUpdateData( $aSaleData, $aOrdersData, $aStatusData, $aLocksData );
        
        if( $mResult === -33 )
        {//资金不够
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return "系统错误：错误编号:#5012";
            }
            $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
            return "余额不足";
        }
        elseif( $mResult !== TRUE )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return "系统错误：错误编号:#5012";
            }
            $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
            return "操作失败，请重试";
        }
        //04 03: 提交投单流程事务处理[结束] -----------------------
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return "系统错误：错误编号:#5013";
        }
        //04 04: 解锁资金表 -------------------------------------
        $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );
        //处理完成
        return TRUE;
    }
    
    
    /**
     *根据游戏ID获取历史中奖号码
     *
     * @param int		$iLotteryId		游戏id
     * @param int		$iIssueCount	查询条数
     * @param string 	$sWhere			查询条件
     * @return array
     * 
     * @author  mark
     */
    function getHistoryBounsCode( $iLotteryId, $iIssueCount, $sWhere )
    {
    	if( !isset($iLotteryId) || !is_numeric($iLotteryId) || !is_numeric($iIssueCount) )
    	{
    		return array();
    	}
    	$iLotteryId = intval($iLotteryId);
    	$sSql = " SELECT * FROM `issuehistory` WHERE `lotteryid` = '$iLotteryId' AND $sWhere";
    	$sSql .= " ORDER BY `issueid` DESC ";//奖期倒序排列
    	if( $iIssueCount != 0 && $sWhere === 1 )
    	{
    		$iIssueCount = intval($iIssueCount);
    		$sLimit      = " LIMIT 0,$iIssueCount " ;
    		$sSql       .= $sLimit;
    	}
		//die($sSql);
    	return $this->oDB->getDataCached( $sSql, '120' );
    }

    /**
     *根据游戏ID获取冷热号码
     *
     * @param int		$iLotteryId		游戏id
     * @param int		$iIssueCount	查询条数
     * @param string 	$sWhere			查询条件
     * @param int       $iLen           号码长度
     * @return array
     * 
     * @author  floyd
     */
    function getHotCode( $iLotteryId, $iIssueCount = 0, $sWhere, $iLen=3, $aNum=array() )
    {
    	if( !isset($iLotteryId) || !is_numeric($iLotteryId) || !is_numeric($iIssueCount) )
    	{
    		return array();
    	}
    	$iLotteryId = intval($iLotteryId);
    	$aSql = array();
		if(!$aNum)
		{
			$aNum = range(0, 9);
		}
		$iIssueCount = intval($iIssueCount);
		$sLimit = $iIssueCount <> 0 ? " LIMIT 0,$iIssueCount" : '';
				
		if( $iLotteryId != 9 )
		{
			for( $i = 1; $i <= $iLen; $i++ )
			{
				$tmpSql = array();
				$aSql[$i] = "(SELECT ";
				foreach( $aNum AS $num )
				{
					if($iLotteryId == 5 || $iLotteryId == 7 || $iLotteryId == 8 || $iLotteryId == 10)
					{
						$num = sprintf('%02d',$num);
						$tmpSql[$num] = "SUM(IF(FIND_IN_SET('$num',code)=$i,1,0)) AS `$num`";
					}
					else
					{
						$tmpSql[$num] = "SUM(IF(LOCATE('$num',code,$i)=$i,1,0)) AS `$num`";
					}
				}
				$aSql[$i] .= implode( ', ',$tmpSql );
				$aSql[$i] .= " FROM ";
				$aSql[$i] .= "(SELECT REPLACE(code, ' ', ',') AS code FROM `issuehistory` WHERE `lotteryid` = $iLotteryId AND $sWhere ";
				$aSql[$i] .= "ORDER BY issueid DESC $sLimit) AS subquery";
				$aSql[$i] .= ")";
			}
			$sSql = implode( ' UNION ALL ', $aSql );
		}
		else
		{
			foreach( $aNum AS $num )
			{
				$num = sprintf('%02d',$num);
				$tmpSql[$num] = "SUM(IF(FIND_IN_SET('$num',code)>0,1,0)) AS `$num`";
			}
			$sSql = 'SELECT ';
			$sSql .= implode( ', ',$tmpSql );
			$sSql .= " FROM ((SELECT REPLACE(code, ' ', ',') AS code FROM `issuehistory` WHERE `lotteryid` = $iLotteryId AND $sWhere ";
			$sSql .= "ORDER BY issueid DESC $sLimit) AS subquery";
			$sSql .= ")";
		}
    	return $this->oDB->getDataCached( $sSql, '120' );
    }
    
    
    
    /**
     * 获取历史开奖号码[生成缓存文件，用户触发式生成]
     *
     * @param int $iLotteryId  彩种ID
     * @param int $iIssueCount 要获取的奖期数
     * @param int $iTime       缓存时间
     * @return array
     */
    public function getHistoryCodeFile( $iLotteryId, $iIssueCount=10, $iTime=0 )
    {
        if( !isset($iLotteryId) || !is_numeric($iLotteryId) || !is_numeric($iIssueCount) )
        {
            return array();
        }
        $iLotteryId = intval($iLotteryId);
        switch( $iLotteryId )
        {
            case 4 : $iTime = 900;break;
            case 5 : $iTime = 360; break;
            case 9:  $iTime = 100;break;
            default: $iTime = 300;break;
        }
        $sSql = " SELECT `code`,`issue`,`statuscode` FROM `issueinfo` WHERE `lotteryid` = '$iLotteryId' ".
                " AND `statuscode`>1 ORDER BY `issueid` DESC LIMIT 0,".$iIssueCount;
        return $this->oDB->getDataCached( $sSql, $iTime );
    }
}
?>