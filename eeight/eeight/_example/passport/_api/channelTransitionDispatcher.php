<?php
/**
 * 路径: /_api/channelTransitionDispatcher.php
 * 用途: API 频道转账调度器
 * 
 * 注意: 此文件仅在 passport 平台存在. 负责功能如下:
 *   1, 整理及效验提交请求中的数据 [API argv]
 *       1.1  $this->aArgv['iUserId']         转账用户ID
 *       1.2  $this->aArgv['iFromChannelId']  转出频道ID
 *       1.3  $this->aArgv['iToChannelId']    转入频道ID
 *       1.4  $this->aArgv['fMoney']          转账金额
 *       1.5  $this->aArgv['sMethod']         转账方法
 *                                          SYS_SMALL = 系统用: 小余额转入转出(不活跃用户清理) 
 *                                          SYS_ZERO  = 系统用: 负余额清零
 *                                          USER_TRAN = 用户用: 频道间转账
 *       1.6  $this->aArgv['iAdminId']       管理员ID
 *       1.7  $this->aArgv['sAdminName']     管理员名
 * 
 *   2, 如果为用户级转账, 效验系统是否允许转账或转账时间段   * 查询本地PASSPORT主DB
 *   3, 效验参与转账的频道是否临时关闭                       * 查询本地PASSPORT主DB
 *   4, 资金账户是否激活                                     * 查询本地PASSPORT主DB
 *   5, 转账请求分发                                         * 查询本地PASSPORT主DB
 *      5.1  发起方扣钱 -100.00
 *      5.2  接收方加钱 +100.00   ----------------\  同一个事务
 *      5.3  接收方更新发起方账变ID及转账状态-----/  同一个事务  
 *      5.4  接收方更新状态状态...
 *   6, 跟踪整个转账过程所需的时间
 * 
 * 
 * 范例调用流程:
 * ~~~~~~~~~~~~~~~
 *                            +-------------+
 *                 /------=>  | D::足球游戏 |
 *     +-------------+        +-------------+
 *     | A::Passport | 
 *     +-------------+        +-------------+
 *                 \------=>  | C::低频游戏 |
 *                            +-------------+
 * 
 *  (1) 用户在 A|C|D 平台发起平台转账.   例: C低频 转至 A银行 100元
 *  (2) C 通过其本地平台PHP程序, 以API形式调用本程序(channelTransitionDispatcher.php) 并等待返回
 *  (3) 本程序收集转账信息. 用户ID,来源频道,目标频道等...并将转账请求进行分发
 *  (4) 本程序返回成功或错误信息给发起方 C 的PHP程序
 * 
 * @author 	    tom  090922 12:51
 * @version	1.2.0
 * @package	passport
 */


/*   -=[ 转账调度器错误代码 ]=-
 * 
 *   ErrCode         ErrorMsg
 * 
 *    -1000          转账失败 #1        初始化失败
 *    -1001          转账失败 #2        转账相关频道未开启
 *    -1002          转账失败 #3        获取 转出频道 '扣款操作' 的返回值 => 失败
 *    -1003          转账失败 #4        获取 转出频道 账变 ID => 失败
 *    -1004          转账失败 #5        转出频道已扣款, 但转入频道加款可能未成功 (无法获取正确响应) [同-1002] 
 *    -1005          转账失败 #6        获取 转入频道 账变 ID => 失败 [同-1003]
 *
 *    -1006          转账失败 #10       更新 转出频道 - 账变标记失败
 *    -1007          转账失败 #11       更新 转入频道 - 账变标记失败
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
            || !isset($this->aArgv['sMethod']) 
            || !in_array( $this->aArgv['sMethod'], array('SYS_SMALL','SYS_ZERO','USER_TRAN') )
            || $this->aArgv['fMoney'] <= 0 
         )
        {
            return $this->makeApiResponse( FALSE, '数据初始错误 #1001' );
        }

        // STEP: 02 效验系统是否允许转账 ( SYSTEM 级 ) 或转账时间段 ------------------------
        if( FALSE === $this->_nowIsAllowTranfer() )
        {
            return $this->makeApiResponse( FALSE, '当前时间禁止转账,请稍后 #1002' );
        }


        // STEP: 03 效验参与转账的频道是否临时关闭 -----------------------------------------
        $oChannel = new model_channels();
        $aAvailChannels = $oChannel->getAvailableChannel( TRUE, 
        		" AND `id` IN ( '".$this->aArgv['iFromChannelId']."','".$this->aArgv['iToChannelId']."' ) " );
        if( count($aAvailChannels)!=2 )
        {
            return $this->makeApiResponse( FALSE, '转账相关平台未开启 #1003' );
        }
        unset($oChannel,$aAvailChannels);


        // STEP: 04 效验用户 '相关转账平台' 资金账户是否激活 -------------------------------
        $oUserChannel = new model_userchannel();
        $aUserChannel = $oUserChannel->getList( array('entry'), 
        		" `userid`='".$this->aArgv['iUserId'].
        		"'  AND `isdisabled`=0 AND `channelid` IN ( '".
                $this->aArgv['iFromChannelId']."','".$this->aArgv['iToChannelId']."' ) " );
        if( count($aUserChannel)!=2 )
        {
            return $this->makeApiResponse( FALSE, '转账相关账户未激活 #1004' );
        }
        unset($oUserChannel,$aUserChannel);


        // STEP: 05 检查目标平台用户资金是否被锁
        $oChannelApi = new channelapi( $this->aArgv['iToChannelId'], 'checkUserFundLock', TRUE );
	    $oChannelApi->setTimeOut(10); // 超时时间, 可能需要微调
	    $oChannelApi->sendRequest( array( 'iUserId'  => $this->aArgv['iUserId'] ));
	    $mAnswers = $oChannelApi->getDatas(); // 获取 转出频道 '扣款操作' 的返回值
	    if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
	    {
	    	return $this->makeApiResponse( FALSE, '转账目标平台资金账户被锁 #2006' );
	    }
	    unset($oChannelApi,$mAnswers);


        // STEP: 06 转账请求分发 ------------------------------------------------------------
        $sUniqueKey = $this->getUniqueKey(); //  转账唯一KEY, 用于识别一组转账行为
        $iAdminId   = isset($this->aArgv['iAdminId']) ? intval($this->aArgv['iAdminId']) : 0;
        $sAdminName = isset($this->aArgv['sAdminName']) ? $this->aArgv['sAdminName'] : '';

	    // 6.1 实例化转出频道 转账API (扣钱方), 执行: 基于事务的扣款操作,账变等.
	    $oChannelApi = new channelapi( $this->aArgv['iFromChannelId'], 'channelTransition', TRUE );
	    $oChannelApi->setTimeOut(10); // 超时时间, 可能需要微调
	    $oChannelApi->sendRequest( array( 
	                'iAdminId'  => $iAdminId,                       // 管理员ID
	                'sAdminName'=> $sAdminName,                     // 管理员名
	                'sMethod'   => $this->aArgv['sMethod'],         // 转账方法
	                'sType'     => 'reduce',                        // 加钱 | 扣钱
	    			'iUserId'   => $this->aArgv['iUserId'],         // 用户ID
	                'fMoney'    => $this->aArgv['fMoney'],          // 涉及金额
	                'iFromCid'  => $this->aArgv['iFromChannelId'],  // 发起频道
	                'iToCid'    => $this->aArgv['iToChannelId'],    // 目标频道
	                'sUnique'   => $sUniqueKey,                     // 转账唯一KEY
	    ));

	    $mAnswers = $oChannelApi->getDatas(); // 获取 转出频道 '扣款操作' 的返回值
	    if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
	    {
	        // TODO: (日志)分析并记录返回的错误.
	        //$sErrorMsg = isset($mAnswers['data']) ? $mAnswers['data'] : '';
	    	return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2000' ); 
	    }
	    $iFromChannelOrderEntry = intval($mAnswers['data']); // 获取转出频道生成的 '转账账变' orders.entry
	    if( $iFromChannelOrderEntry <= 0 )
	    {
	        return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2001' ); 
	    }
        unset($oChannelApi,$mAnswers);


        // -------------------------------------------------------------------------------------
        // 6.2 实例化转入频道 转账API (加钱方), 执行: 基于事务的扣款操作,账变等.
	    $oChannelApi = new channelapi( $this->aArgv['iToChannelId'], 'channelTransition', TRUE );
	    $oChannelApi->setTimeOut(10); // 超时时间, 可能需要微调
        $oChannelApi->sendRequest( array( 
                    'iAdminId'  => $iAdminId,                       // 管理员ID
                    'sAdminName'=> $sAdminName,                     // 管理员名
                    'sMethod'   => $this->aArgv['sMethod'],         // 转账方法
	    	        'sType'     => 'plus',                          // 加钱 | 扣钱
                    'iUserId'   => $this->aArgv['iUserId'],         // 用户ID
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
	        return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2002' ); 
	    }
	    $iToChannelOrderEntry = intval($mAnswers['data']);
	    if( $iToChannelOrderEntry <= 0 )
	    {
	        return $this->makeApiResponse( FALSE, '转账失败,错误代码 #2003' ); 
	    }
        unset($oChannelApi,$mAnswers);


        // 更新转出频道: 状态值, 关联账变ID --------------------------------
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

	    // 7.0 以上所有 oChannelApi(共执行3次) 均执行成功, 则转账成功, 否则记录转账错误日志
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