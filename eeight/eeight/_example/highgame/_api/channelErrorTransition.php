<?php
/**
 * 路径: /_api/channelErrorTransition.php
 * 用途: 异常处理频道转账API (请求接收处理方)
 * 
 * @author   Mark  090926
 * @version  1.0
 */
// 1, 包含项目头文件, index.php 忽略 MVC
define('DONT_USE_APPLE_FRAME_MVC', TRUE);       // 跳过 MVC 流程
define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);   // 跳过配置文件检测
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php'); // 引入项目入口文件

class api_channelErrorTransition extends baseapi
{
     public function _runProcess()
     {
         $oOrder = new model_orders();
         switch ( $this->aArgv['sAction'] )
         {
             // 改变账变状态值
             case 'updateStatus':
             {
                // STEP: 01 整理 API 提交数据, 完整性&有效性判断
                if( empty($this->aArgv['iAdminId']) || empty($this->aArgv['sAdminName'])
                     || empty($this->aArgv['iRelationOrderEntry']) || !is_numeric($this->aArgv['iRelationOrderEntry'])
                     || empty($this->aArgv['iOrderEntry']) || !is_numeric($this->aArgv['iOrderEntry'])
                     || empty($this->aArgv['sUniqueKey']) || strlen($this->aArgv['sUniqueKey']) != 32
                 )
                 {
                     return $this->makeApiResponse( FALSE, '_api/channelErrorTransition.php : Wrong init Data #3001' );
                 }
                 
                 // step: 02 更新账变状态值
                 $aResult = $oOrder->getOrderList( 'transferstatus', " `entry`='".$this->aArgv['iOrderEntry']
                            ."' AND `uniquekey` = '" . $this->aArgv['sUniqueKey'] . "'" );
                 if( $aResult['transferstatus'] != 2 )
                 {
                     if( TRUE === $oOrder->UpdateApiTranferStatus(
                            $this->aArgv['iOrderEntry'], 
                            $this->aArgv['sUniqueKey'], 
                            $this->aArgv['iRelationOrderEntry'],
                            2,
                            $this->aArgv['iAdminId'],
                            $this->aArgv['sAdminName'])
                     )
                     {
                         return $this->makeApiResponse( TRUE, 'successed!' );
                     }
                     else
                     {
                         return $this->makeApiResponse( FALSE, 'Error' );
                     }
                 }
                 else
                 {
                     return $this->makeApiResponse( TRUE, 'successed!' );
                 }
                 break;
             }

             // 检测转入方是否存在对就的账变, 如果存在刚返回账变ID, 不存在则添加账变和加钱
             case 'checkandadd':
             {
                // STEP: 01 整理 API 提交数据, 完整性&有效性判断
                if( !isset($this->aArgv['sMethod'])
                || !isset($this->aArgv['iOrderEntry']) || !is_numeric($this->aArgv['iOrderEntry'])
                || !in_array( $this->aArgv['sMethod'], array('SYS_SMALL','SYS_ZERO','USER_TRAN') )
                || empty($this->aArgv['iUserId']) || empty($this->aArgv['fMoney'])
                || empty($this->aArgv['sUniqueKey']) || strlen($this->aArgv['sUniqueKey']) != 32
                || !is_numeric($this->aArgv['iUserId']) || !is_numeric($this->aArgv['fMoney'])
                || !is_numeric($this->aArgv['iFromCid']) || !is_numeric($this->aArgv['iToCid'])
                || $this->aArgv['sType'] != 'plus'  // 只检查加钱方状态
                || SYS_CHANNELID != $this->aArgv['iToCid']  // 频道ID不为加钱方就报错
                )
                {
                    return $this->makeApiResponse( FALSE, '_api/channelErrorTransition.php : Wrong init Data #3001' );
                }
                 $aResult = $oOrder->getOrderList( 'entry', "`uniquekey` = '" . $this->aArgv['sUniqueKey'] . 
                                  "' AND `transferorderid`='".$this->aArgv['iOrderEntry'].
                                  "' AND `fromuserid` = '" . $this->aArgv['iUserId'] . "'");
                 if( !empty($aResult['entry']) )
                 {
                     return $this->makeApiResponse( TRUE,  $aResult['entry'] );
                 }
                 else
                 {
                     $aData = array();
                     $aData['sMethod']         = $this->aArgv['sMethod'];             // 方法
                     $aData['sType']           = $this->aArgv['sType'];               // 类型  reduce|plus
                     $aData['iUserId']         = intval( $this->aArgv['iUserId'] );   // 用户ID
                     $aData['iFromChannelId']  = intval( $this->aArgv['iFromCid'] );  // 扣款方频道ID
                     $aData['iToChannelId']    = intval( $this->aArgv['iToCid'] );    // 收款方频道ID
                     $aData['fMoney']          = floatval( $this->aArgv['fMoney'] );  // 资金
                     $aData['sUnique']         = $this->aArgv['sUniqueKey'];
                     $aData['iAdminId']        = isset($this->aArgv['iAdminId']) ? intval($this->aArgv['iAdminId']) : 0;
                     $aData['sAdminName']      = isset($this->aArgv['sAdminName']) ? $this->aArgv['sAdminName'] : '';
                     $aData['bIsTemp']         = isset($this->aArgv['bIsTemp']) ? 1 : 0; // TODO _a高频、低频并行前期临时程序
                     if( $aData['sType'] == 'plus' )
                     { // 只有在加钱方时, 同时处理增加转账账变及更新转账状态值
                         $aData['iOrderEntry']= isset($this->aArgv['iOrderEntry']) ? intval($this->aArgv['iOrderEntry']) : 0;
                     }

                     // step: 02 业务流程操作, 调用用户资金模型. 执行转账操作
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
                         return $this->makeApiResponse( FALSE, 
                                            '_api/channelTransition.php : apiFundTransition() Err => '. $iFlag );
                     }
                 }
                 break;
             }
             default:
             {
                 return $this->makeApiResponse( FALSE, '_api/channelTransition.php : Wrong init Data #3006' );
                 break;
             }
         }
     }
}
// 2, 为调用程序返回 '结果集'
$oApi = new api_channelErrorTransition( TRUE );
$oApi->runApi();
$oApi->showDatas();
EXIT;