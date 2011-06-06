<?php
/**
 * 路径: /_api/channelTransitionErrorDispatcher.php
 * 用途: API 频道转账异常临时用于高频与银行间处理的调度器
 * 
 * TODO _a高频、低频并行前期临时程序, 实现业务逻辑与 channelTransitionDispatcher.php 基本一致
 * 
 * @author     Mark  090926
 * @version	1.0
 * @package	passport
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件


class api_channelTransitionErrorDispatcher extends baseapi
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
            // 异常处理必须传递管理员ID, 管理员登录名
            || !isset($this->aArgv['iAdminId']) || !isset($this->aArgv['sAdminName'])
            || $this->aArgv['fMoney'] <= 0 
         )
        {
            return $this->makeApiResponse( FALSE, '数据初始错误 #1001' );
        }
        $this->aArgv['sMethod'] = 'USER_TRAN';  // 用户转账行为
        

        // STEP: 02 效验系统是否允许转账 ( SYSTEM 级 ) 或转账时间段 ------------------------
        if( FALSE === $this->_nowIsAllowTranfer() )
        {
            return $this->makeApiResponse( FALSE, '当前时间禁止转账,请稍后 #1002' );
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
            return $this->makeApiResponse( FALSE, '转账相关平台未开启 #1002' );
        }
        unset($oChannel,$aAvailChannels);


        // STEP: 04 效验用户 '相关转账平台' 资金账户是否激活 -------------------------------
        $oUserChannel = new model_userchannel();
        $aUserChannel = $oUserChannel->getList( array('entry'), 
            " `userid`='".$this->aArgv['iUserId']."'  AND `isdisabled`=0 AND `channelid` = '$iAframeChannelId' " );
        if( count($aUserChannel)!=1 )
        {
            return $this->makeApiResponse( FALSE, '转账相关账户未激活 #1003' );
        }
        unset($oUserChannel,$aUserChannel);


        // STEP: 05 检查目标平台用户资金是否被锁
        $oChannelApi = new channelapi( $this->aArgv['iToChannelId'], 'checkUserFundLock', TRUE );
	    $oChannelApi->setTimeOut(10); // 超时时间, 可能需要微调
	    $oChannelApi->sendRequest( array( 'iUserId'  => $this->aArgv['iUserId'] ));
	    $mAnswers = $oChannelApi->getDatas(); // 获取 转出频道 '扣款操作' 的返回值
	    if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
	    {
	    	return $this->makeApiResponse( FALSE, '转账目标平台资金账户被锁 #1004' ); 
	    }
	    unset($oChannelApi,$mAnswers);


        // STEP: 06 转账异常处理请求分发 ------------------------------------------------------------
        $iAdminId   = intval($this->aArgv['iAdminId']);
        $sAdminName = $this->aArgv['sAdminName'];
        $iFromChannelOrderEntry = $this->aArgv['iOrderId'];

        // 6.1 转入频道异常账变处理
        // 执行: 如果本地存在对应转出方的账变，则不进行处理，否则执行账变的添加，资金的添加操作.
        $oChannelApi = new channelapi( $this->aArgv['iToChannelId'], 'channelErrorTransition', TRUE );
        $oChannelApi->setTimeOut(10);
        $oChannelApi->sendRequest( array(
            'iAdminId'     => $iAdminId,                       // 管理员ID
            'sAdminName'   => $sAdminName,                     // 管理员名
            'sMethod'      => $this->aArgv['sMethod'],         // 转账方法
            'sType'        => 'plus',                          // 加钱
            'sAction'      => 'checkandadd',                   // 检测是否存在对应账变
            'iUserId'      => $this->aArgv['iUserId'],         // 用户ID
            'fMoney'       => $this->aArgv['fMoney'],          // 涉及金额
            'iFromCid'     => $this->aArgv['iFromChannelId'],  // 发起频道
            'iToCid'       => $this->aArgv['iToChannelId'],    // 目标频道
        	'iOrderEntry'  => $iFromChannelOrderEntry,         // 关联ID  
            'sUniqueKey'   => $this->aArgv['sUniqueKey'],      // 转账唯一KEY
            'bIsTemp'      => $this->aArgv['bIsTemp']
        ));
        $mAnswers = $oChannelApi->getDatas(); // 获取 转入频道 '加款操作' 的返回值
        if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
        {
            return $this->makeApiResponse( FALSE, '异常账变处理失败,错误代码 #2001' );
        }
        $iToChannelOrderEntry = intval($mAnswers['data']);
        if( $iToChannelOrderEntry <= 0 )
        {
            return $this->makeApiResponse( FALSE, '异常账变处理失败,错误代码 #2001' );
        }
        unset($oChannelApi,$mAnswers);

        // 6.2更新转出频道: 状态值, 关联账变ID --------------------------------
        $oChannelApi = new channelapi( $this->aArgv['iFromChannelId'], 'channelErrorTransition', TRUE );
	    $oChannelApi->setTimeOut(10); 
        $oChannelApi->sendRequest( array( 
                    'iAdminId'             => $iAdminId,     // 管理员ID
                    'sAdminName'           => $sAdminName,   // 管理员名
	    	        'sAction'              => 'updateStatus',
	    	        'iUserId'              => $this->aArgv['iUserId'],
                    'iOrderEntry'          => $iFromChannelOrderEntry,
        			'iRelationOrderEntry'  => $iToChannelOrderEntry,  
	    	        'sUniqueKey'           => $this->aArgv['sUniqueKey']
	    ));
	    $mAnswers = $oChannelApi->getDatas();
	    if( empty($mAnswers) || !is_array($mAnswers) || $mAnswers['status'] == 'error' )
	    {
	        return $this->makeApiResponse( FALSE, '异常账变处理失败,错误代码 #2002' );  
	    }
	    return $this->makeApiResponse( TRUE, 'SUCCESSED!' );  
    }




    /**
     * 检查当前时间是否可以进行转账 (SYS_CONFIG 设置每天转账关闭时间)
     *   依赖参数: MYSQL.CONFIG. configkey = zz_forbid_time
     * @return BOOL  TRUE=允许转账,  FALSE=禁止转账
     */
    private function _nowIsAllowTranfer()
    {
        // 1, 如果为系统级的转账, 任何时间都允许
        if( in_array($this->aArgv['sMethod'], array('SYS_ZERO','USER_TRAN')) )
        {
            return TRUE;
        }

        $oConfig = new model_config();
        $aConfigValue = $oConfig->getConfigs( array('zz_forbid_time') ); // 系统禁止转账时间段
        $aTime = @explode( '-', $aConfigValue['zz_forbid_time'] );
        list( $iBeginHour, $iBeginMinute ) = @explode(":", $aTime[0]);
        list( $iEndHour, $iEndMinute ) = @explode(":", $aTime[1]);

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
$oApi = new api_channelTransitionErrorDispatcher(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>