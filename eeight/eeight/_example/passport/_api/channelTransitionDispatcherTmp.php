<?php
/**
 * 路径: /_api/channelTransitionDispatcherTmp.php
 * 用途: API 频道转账调度器
 * 
 * TODO _a高频、低频并行前期临时程序, 实现业务逻辑与 channelTransitionDispatcher.php 基本一致
 * 
 * 区别在于:
 *   1,  转账类型只限为 "用户转账"   $this->aArgv['sMethod'] = USER_TRAN
 *   2,  效验参与转账频道, 增加 "高频" ChannelId = 99
 * 
 * @author    Tom  091015  13:14
 * @version   1.2.1
 * @package   passport
 */


/*   -=[ 转账调度器错误代码 (为各平台的'转账程序' 返回的消息内容) ]=-
 * 
 *   ErrCode         ErrorMsg
 * 
 *    1001          转账数据初始错误.
 *    1002          当前时间禁止转账,请稍后.
 *    1003          转账相关平台未开启.
 *    1004          当前账户禁止转账.
 *    1005          转账相关账户未激活.
 *    1006          转账目标平台资金账户被锁. (转账扣钱,加钱前的检测)
 *    1007          
 * 
 * 
 * 
 * 
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class api_channelTransitionDispatcher extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return bool | string
     *     - 执行失败, 则返回 : '错误字符串'
     *     - 执行成功, 则返回全等于的 TRUE 类型
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     */
    public function _runProcess()
    {
        // STEP: 01 整理及效验提交请求中的数据 [API argv] ------------------------------
        if( empty($this->aArgv['iUserId']) || !is_numeric($this->aArgv['iUserId'])
            || empty($this->aArgv['fMoney']) || !is_numeric($this->aArgv['fMoney'])
            || !isset($this->aArgv['iFromChannelId']) || !is_numeric($this->aArgv['iFromChannelId'])
            || !isset($this->aArgv['iToChannelId']) || !is_numeric($this->aArgv['iToChannelId'])
            || ( $this->aArgv['iFromChannelId']!=99 && $this->aArgv['iToChannelId']!=99 ) // 与高频转账
            || $this->aArgv['fMoney'] <= 0 
         )
        {
            return $this->makeApiResponse( FALSE, '转账数据初始错误.' );
        }
        $this->aArgv['sMethod'] = 'USER_TRAN';  // 用户转账行为


        // STEP: 02 效验系统是否允许转账 ( SYSTEM 级 ) 或转账时间段 ------------------------
        if( FALSE === $this->_nowIsAllowTranfer() )
        {
            return $this->makeApiResponse( FALSE, '当前时间禁止转账,请稍后.' );
        }

        // 调用此 API 的应用程序, 必有一方的 channelid = 99 (高频)
        // 在 aframe 框架开发的架构中, 只需检查不等于 99 的频道属性即可. 例: 资金激活状态,频道开启状态
        $iAframeChannelId = $this->aArgv['iFromChannelId']!=99 ? 
                    intval($this->aArgv['iFromChannelId']) : intval($this->aArgv['iToChannelId']);


        // STEP: 03 效验参与转账的频道是否临时关闭 -----------------------------------------
        $oChannel = new model_channels();
        $aAvailChannels = $oChannel->getAvailableChannel( TRUE, " AND `id` = '$iAframeChannelId' " );
        if( count($aAvailChannels)!=1 )
        {
            return $this->makeApiResponse( FALSE, '转账相关平台未开启.' );
        }
        unset($aAvailChannels);


        // STEP: 04 如果转账用户是总代, 则判断此总代是否有权限转账
        /* @var $oDb Db */
        // **  2/25/2010  如为总代不进行ID一致性检查
        $oUser  = new model_user();
    	// 如为总代、获取正确的高频主账户 ID,, 赋值正确的 From/To UserID
    	// 2/21/2010
    	if ($this->aArgv['iFromChannelId'] == 99){
    		$iMaybeTopproxyId = $oUser->getDPmainUID($this->aArgv['iUserId']);
    	}else{
    		$iMaybeTopproxyId = $this->aArgv['iUserId'];
    	}
        
		if($oUser->isTopProxy($iMaybeTopproxyId) === TRUE){
			if ($this->aArgv['iFromChannelId'] == 99) {
				$iFromUserId = $oUser->getGPmainUID($this->aArgv['iUserId']);
				$iToUserId = $oUser->getDPmainUID($this->aArgv['iUserId']);
			}else{
				$iFromUserId = $oUser->getDPmainUID($this->aArgv['iUserId']);
				$iToUserId = $oUser->getGPmainUID($this->aArgv['iUserId']);
			}
		}else{
			$iFromUserId = $iToUserId = $this->aArgv['iUserId'];
		}
		
        if(!$oUser->isTopProxy($iMaybeTopproxyId)){
        	$oDb = $oChannel->getDB();
        	$aRows = $oDb->getOne("SELECT `dpuserid` FROM `tempusermap` WHERE `status`=1 AND `gpuserid`='".
        	$this->aArgv['iUserId']."' ");
        	if( $oDb->ar() )
        	{
        		if( $aRows['dpuserid'] != $this->aArgv['iUserId'] )
        		{
        			return $this->makeApiResponse( FALSE, '当前账户禁止转账.' );
        		}
        	}
        	unset($oChannel,$oDb,$aRows);
        }

        // STEP: 05 效验用户 '相关转账平台' 资金账户是否激活 -------------------------------
        $oUserChannel = new model_userchannel();
        $aUserChannel = $oUserChannel->getList( array('entry'), 
            " `userid`='".$iMaybeTopproxyId."'  AND `isdisabled`=0 AND `channelid` = '$iAframeChannelId' " );
       
        if( count($aUserChannel)!=1 )
        {
            return $this->makeApiResponse( FALSE, '转账相关账户未激活.' );
        }
        unset($oUserChannel,$aUserChannel);

    	
		// end patch
        // STEP: 06 检查目标平台用户资金是否被锁
        $oChannelApi = new channelapi( $this->aArgv['iToChannelId'], 'checkUserFundLock', TRUE );
        $oChannelApi->setTimeOut(10); // 超时时间, 可能需要微调
        $oChannelApi->sendRequest( array( 'iUserId'  => $iToUserId ));
        $mAnswers = $oChannelApi->getDatas(); // 获取 转出频道 '扣款操作' 的返回值
        
        if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
        {
//        	return $this->makeApiResponse( FALSE, '错误信息: '.$iToUserId.'-'. $sss  .$mAnswers['data'] ); // 调试错误信息
            return $this->makeApiResponse( FALSE, '转账目标平台资金账户被锁.' );
        }
        unset($oChannelApi,$mAnswers);


        // STEP: 07 转账请求分发 ------------------------------------------------------------
        $sUniqueKey = $this->getUniqueKey(); //  转账唯一KEY, 用于识别一组转账行为
        $iAdminId   = isset($this->aArgv['iAdminId']) ? intval($this->aArgv['iAdminId']) : 0;
        $sAdminName = isset($this->aArgv['sAdminName']) ? $this->aArgv['sAdminName'] : '';
		
        // 7.1 实例化转出频道 转账API (扣钱方), 执行: 基于事务的扣款操作,账变等.
        $oChannelApi = new channelapi( $this->aArgv['iFromChannelId'], 'channelTransition', TRUE );
        $oChannelApi->setTimeOut(10); // 超时时间, 可能需要微调
        $oChannelApi->sendRequest( array( 
                    'bIsTemp'   => TRUE,                            // 临时代码. TODO _a高频、低频并行前期临时程序
                    'iAdminId'  => $iAdminId,                       // 管理员ID
                    'sAdminName'=> $sAdminName,                     // 管理员名
                    'sMethod'   => $this->aArgv['sMethod'],         // 转账方法
                    'sType'     => 'reduce',                        // 加钱 | 扣钱
                    'iUserId'   => $iFromUserId,         			// 用户ID
                    'fMoney'    => $this->aArgv['fMoney'],          // 涉及金额
                    'iFromCid'  => $this->aArgv['iFromChannelId'],  // 发起频道
                    'iToCid'    => $this->aArgv['iToChannelId'],    // 目标频道
                    'sUnique'   => $sUniqueKey,                     // 转账唯一KEY
        ));
        $mAnswers = $oChannelApi->getDatas(); // 获取 转出频道 '扣款操作' 的返回值
        if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
        {
            // TODO: (日志)分析并记录返回的错误.
            $sErrorMsg = isset($mAnswers['data']) ? $mAnswers['data'] : '';
            return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2000'.$sErrorMsg ); 
        }
        $iFromChannelOrderEntry = intval($mAnswers['data']); // 获取转出频道生成的 '转账账变' orders.entry
        if( $iFromChannelOrderEntry <= 0 )
        {
            return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2001' ); 
        }
        unset($oChannelApi,$mAnswers);

        // -------------------------------------------------------------------------------------
        // 7.2 实例化转入频道 转账API (加钱方), 执行: 基于事务的扣款操作,账变等.
        $oChannelApi = new channelapi( $this->aArgv['iToChannelId'], 'channelTransition', TRUE );
        $oChannelApi->setTimeOut(10); // 超时时间, 可能需要微调
        $oChannelApi->sendRequest( array( 
                    'bIsTemp'   => TRUE,                            // 临时代码. TODO _a高频、低频并行前期临时程序
                    'iAdminId'  => $iAdminId,                       // 管理员ID
                    'sAdminName'=> $sAdminName,                     // 管理员名
                    'sMethod'   => $this->aArgv['sMethod'],         // 转账方法
                    'sType'     => 'plus',                          // 加钱 | 扣钱
                    'iUserId'   => $iToUserId,         				// 用户ID
                    'fMoney'    => $this->aArgv['fMoney'],          // 涉及金额
                    'iFromCid'  => $this->aArgv['iFromChannelId'],  // 发起频道
                    'iToCid'    => $this->aArgv['iToChannelId'],    // 目标频道
                    'sUnique'   => $sUniqueKey,                     // 转账唯一KEY
                    'iOrderEntry' => $iFromChannelOrderEntry,       // 扣钱方的账变ID (加钱方特有)
        ));
        $mAnswers = $oChannelApi->getDatas(); // 获取 转入频道 '加款操作' 的返回值
        if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
        {
            //print_rr($mAnswers);exit; // FOR DEBUG
            // TODO: 重要: 需要进行日志记录, 成功执行第一个API事务(扣钱), 执行第二个失败
            $sTmpData = print_r( $mAnswers, TRUE );
            $sTmpData = "\n-------------- [ CTDT.#2002 Debug START ] -----------------------\n" . $sTmpData;
            $sTmpData .= "\n-------------- [ END ] ----------------------------------------\n\n";
            /* @var $GLOBALS['oLogs'] logs */
            if( !isset( $GLOBALS['oLogs'] ) )
            {
                $GLOBALS['oLogs'] = A::singleton('logs');
            }
            $GLOBALS['oLogs']->addDebug( $sTmpData, 'api_debug' );
            return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2002<br/>DEBUG:('.$sTmpData .')' );
        }
        $iToChannelOrderEntry = intval($mAnswers['data']);
        if( $iToChannelOrderEntry <= 0 )
        {
            return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2003' ); 
        }
        unset($oChannelApi,$mAnswers);


        // STEP: 08 更新转出频道: 状态值, 关联账变ID --------------------------------
        $oChannelApi = new channelapi( $this->aArgv['iFromChannelId'], 'channelTransition', TRUE );
        $oChannelApi->setTimeOut(10); // TODO: 超时时间, 可能需要微调 
        $oChannelApi->sendRequest( array( 
                    'iAdminId'             => $iAdminId,     // 管理员ID
                    'sAdminName'           => $sAdminName,   // 管理员名
                    'sAction'              => 'updateStatus',
                    'iOrderEntry'          => $iFromChannelOrderEntry,
                    'iRelationOrderEntry'  => $iToChannelOrderEntry,  
                    'sUniqueKey'           => $sUniqueKey,
        ));
        $mAnswers = $oChannelApi->getDatas();
        if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
        {
            // TODO: 需要进行日志记录, 成功执行第一个API事务, 执行第二个失败
            return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2004' );  
        }

        // 9.0 以上所有 oChannelApi(共执行3次) 均执行成功, 则转账成功, 否则记录转账错误日志
        //    可能的错误为:
        //    1. 第一个API事务成功执行后, 第二个API 执行失败, 无法回滚. 使用日志和 CLI 来判断
        //    2. ....
        return $this->makeApiResponse( TRUE, 'SUCCESSED!' );  
    }
    
    // 注意: 返回引用型的数据! (此方法请勿修改) by Tom 090911 13:21
    private function & getUniqueKey()
    {
        $sString = md5(uniqid(rand(), true));
        return $sString;
    }
    
    
    /**
     * 检查当前时间是否可以进行转账 (SYS_CONFIG 设置每天转账关闭时间)
     *   依赖参数: MYSQL.CONFIG. configkey = zz_forbid_time
     * @return BOOL  TRUE=允许转账,  FALSE=禁止转账
     */
    private function _nowIsAllowTranfer()
    {
        // 1, 如果为系统级的转账, 任何时间都允许
        if( in_array($this->aArgv['sMethod'], array('SYS_SMALL','SYS_ZERO')) )
        {
            return TRUE;
        }

        $oConfig = new model_config();
        $aConfigValue = $oConfig->getConfigs( array('zz_forbid_time') ); // 系统禁止转账时间段
        $aTime = @explode( '-', $aConfigValue['zz_forbid_time'] );
        list( $iBeginHour, $iBeginMinute ) = @explode(":", $aTime[0]);
        list( $iEndHour, $iEndMinute ) = @explode(":", $aTime[1]);

        //echo "$iBeginHour - $iBeginMinute - $iEndHour - $iEndMinute <br/>";
        if( !is_numeric($iBeginHour) || !is_numeric($iBeginMinute) 
            || !is_numeric($iEndHour) || !is_numeric($iEndMinute) )
        {
            return FALSE;
        }

        /* 算法:  开始的小时数,大于结束的小时数,则按跨天处理
         *    例1:   4:00-13:00   正常不跨天, 每天 早上4点至下午1点
         *    例2:   23:00-4:00   跨天处理, 每天凌晨 23:00 至第二天凌晨4点
         */ 

        $iNowTime  = time(); // 当前时间点的 时间戳
        if( $iBeginHour > $iEndHour )
        { // 处理跨天
            //echo '跨天<br/>';
            $iNowTime =  date("Hi"); // 00:21 表示为 0021
            if( intval($iNowTime) > intval($iBeginHour.$iBeginMinute)
                ||  $iNowTime < $iEndHour.$iEndMinute
            )
            {
                return FALSE;
            }
            else 
            {
                return TRUE;
            }
        }
        else 
        { // 处理不跨天
            //echo '不跨天<br/>';
            $iNowTime =  date("Hi"); // 00:21 表示为 0021
            if( intval($iNowTime) > intval($iBeginHour.$iBeginMinute)
                &&  $iNowTime < $iEndHour.$iEndMinute  )
            {
                return FALSE;
            }
            else 
            {
                return TRUE;
            }
        }
    }
    
    
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_channelTransitionDispatcher(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>