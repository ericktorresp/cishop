<?php
/**
 * 路径: /_api/channelTransition.php
 * 用途: 频道转账API (请求接收处理方)
 * 
 * 注意: 此文件(channelTransition.php) 在每个平台中都存在
 *       应使资金调用API方法统一 : $oUserFund->apiFundTransition()
 *       并且此文件更新, 要对每个平台的 /_api/channelTransition.php 进行同步更新
 *
 * 
 * 转账调度器 API 接收参数:
 *    $this->aArgv['sMethod']     =  转账方法:  定义参见 passport/_api/channelTransition.php
 *                                    - SYS_SMALL = 系统用: 小余额转入转出(不活跃用户清理) 
 *                                    - SYS_ZERO  = 系统用: 负余额清零
 *                                    - USER_TRAN = 用户用: 频道间转账
 *    $this->aArgv['sType']       =  转账的类型:    转出(reduce) | 转入(plus)
 *    $this->aArgv['iUserId']     =  转账的用户ID
 *    $this->aArgv['fMoney']      =  转账金额, 精度2位  12345.67
 *    $this->aArgv['iFromCid']    =  转出频道
 *    $this->aArgv['iToCid']      =  转入频道
 *    $this->aArgv['sUnique']     =  账变唯一编号
 *    $this->aArgv['iAdminId']    =  管理员ID
 *    $this->aArgv['sAdminName']  =  管理员ID
 * 
 * @author 	    tom  090911 11:57
 * @version	1.2.0
 * @package	*** All Channel ***
 */

// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件



class api_channelTransition extends baseapi
{
    /**
     * 所有派生的类, 请保留以下注释:
     * 重写基类 _runProcess() 方法, 最后需返回运行成功或失败
     * @return $this->makeApiResponse( TRUE|FALSE, 'STRING DATA' )
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     * 
     * 对转账的资金变动及账变进行原子操作
     *    1, 调用自身平台的方法 : $oUserFund->apiFundTransition()
     *    +++ 操作成功 : baseapi->makeApiResponse( TRUE )
     *    *** 操作失败 : 返回错误信息 baseapi->makeApiResponse(FALSE)
     * 
     * 需使用自身平台ID宏 : SYS_CHANNELID 来判断账变类型
     *    if( iFromChannelid == SYS_CHANNELID )    为转出平台. 执行扣钱操作
     *    if( iToChannelid == SYS_CHANNELID )      为转入平台. 执行加钱操作
     */
    public function _runProcess()
    {
        // -------------------- 部分01: 更新转出频道, 转入频道 '转账账变' 的状态值 ------------------
        if( !empty($this->aArgv['sAction']) && $this->aArgv['sAction']=='updateStatus'
        	 && !empty($this->aArgv['iOrderEntry']) && is_numeric( $this->aArgv['iOrderEntry'])
        	 && !empty($this->aArgv['iRelationOrderEntry']) && is_numeric( $this->aArgv['iRelationOrderEntry'])
        	 && !empty($this->aArgv['sUniqueKey']) && strlen($this->aArgv['sUniqueKey']) == 32
        )
        {
            $oOrder = new model_orders();
            if( TRUE === $oOrder->UpdateApiTranferStatus( 
                    $this->aArgv['iOrderEntry'], $this->aArgv['sUniqueKey'], $this->aArgv['iRelationOrderEntry'] ) )
            {
                return $this->makeApiResponse( TRUE, 'successed!' );
            }
            else
            {
                return $this->makeApiResponse( FALSE, 'Error' );
            }
        }


        // ------------------------ 部分02: 转账操作 -------------------------------------------------
        // STEP: 01 整理 API 提交数据, 完整性&有效性判断
        if( !isset($this->aArgv['sMethod']) 
            || !in_array( $this->aArgv['sMethod'], array('SYS_SMALL','SYS_ZERO','USER_TRAN') )
            || empty($this->aArgv['iUserId']) || empty($this->aArgv['fMoney']) 
            || empty($this->aArgv['sUnique']) || strlen($this->aArgv['sUnique']) != 32
            || !is_numeric($this->aArgv['iUserId']) || !is_numeric($this->aArgv['fMoney'])
            || !is_numeric($this->aArgv['iFromCid']) || !is_numeric($this->aArgv['iToCid'])
            || ( SYS_CHANNELID!=$this->aArgv['iFromCid'] && SYS_CHANNELID!=$this->aArgv['iToCid'] )
         )
        { // 数据完整性检查失败
            return $this->makeApiResponse( FALSE, '_api/channelTransition.php : Wrong init Data #3001' );
        }
        if( !in_array( $this->aArgv['sType'], array('reduce','plus')) )
        { // 加钱,扣钱ID错误
            return $this->makeApiResponse( FALSE, '_api/channelTransition.php : Wrong init Data #3002' );
        }
        if( $this->aArgv['sType']=='reduce' && SYS_CHANNELID != $this->aArgv['iFromCid'] )
        { // 扣钱时, 频道ID错误
            return $this->makeApiResponse( FALSE, '_api/channelTransition.php : Wrong init Data #3003' );
        }
        if( $this->aArgv['sType']=='plus' && SYS_CHANNELID != $this->aArgv['iToCid'] )
        { // 加钱时, 频道ID错误
            return $this->makeApiResponse( FALSE, '_api/channelTransition.php : Wrong init Data #3004' );
        }
        if( $this->aArgv['sType']=='plus' && !array_key_exists('iOrderEntry', $this->aArgv) )
        { // 扣钱方账变ID
            return $this->makeApiResponse( FALSE, '_api/channelTransition.php : Wrong init Data #3005' );
        }

        $aData = array();
        $aData['sMethod']        = $this->aArgv['sMethod'];             // 方法
        $aData['sType']          = $this->aArgv['sType'];               // 类型  reduce|plus
        $aData['iUserId']        = intval( $this->aArgv['iUserId'] );   // 用户ID
        $aData['iFromChannelId'] = intval( $this->aArgv['iFromCid'] );  // 扣款方频道ID
        $aData['iToChannelId']   = intval( $this->aArgv['iToCid'] );    // 收款方频道ID
        $aData['fMoney']         = floatval( $this->aArgv['fMoney'] );  // 资金
        $aData['sUnique']        = $this->aArgv['sUnique'];
        $aData['iAdminId']       = isset($this->aArgv['iAdminId']) ? intval($this->aArgv['iAdminId']) : 0;
        $aData['sAdminName']     = isset($this->aArgv['sAdminName']) ? $this->aArgv['sAdminName'] : '';
        $aData['bIsTemp']        = isset($this->aArgv['bIsTemp']) ? 1 : 0; // TODO _a高频、低频并行前期临时程序
        if( $aData['sType'] == 'plus' )
        { // 只有在加钱方时, 同时处理增加转账账变及更新转账状态值
            $aData['iOrderEntry']= isset($this->aArgv['iOrderEntry']) ? intval($this->aArgv['iOrderEntry']) : 0;
        }

        // STEP: 02 业务流程操作, 调用用户资金模型. 执行转账操作
        $oUserFund = new model_userfund();
        $iFlag = $oUserFund->apiFundTransition($aData);
        if( TRUE === $iFlag )
        {
            // 获取插入的账变ID  db.table.orders.entry
            $iOrderEntry = $oUserFund->getOrderEntryByTranferData( 
                    $aData['sUnique'], $aData['iUserId'], $aData['fMoney'] );
            if( $iOrderEntry === FALSE )
            { // 无法获取新插入的转账账变ID编号
                return $this->makeApiResponse( FALSE, '_api/channelTransition.php : Cant got order.entry #3005' );
            }
            return $this->makeApiResponse( TRUE, $iOrderEntry );
        }
        else
        {
            return $this->makeApiResponse( FALSE, '_api/channelTransition.php : apiFundTransition() Err => '. $iFlag );
        }
    }
}


// 2, 为调用程序返回 '结果集'
$oApi = new api_channelTransition(TRUE);
$oApi->runApi();
$oApi->showDatas();
EXIT;
?>