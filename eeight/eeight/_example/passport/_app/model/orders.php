<?php
/**
 * 文件 : /_app/model/orders.php
 * 功能 : 数据模型 - 用户帐变
 * 
 *    01.  addOrders           改变账户资金的同时增加账变(事务)
 *    02.  getAdminOrderList   查看账变列表 (后台用)
 *    03.  getUserOrderList    查看账变列表 (前台用)
 *    04.  getOrderType        获取全部的账变类型
 *    05.  getMoneyRealIn      获取当天充值总额 (用于后台市场管理-财务信息图表) 
 *    06.  getMoneyRealOut     获取当天提现总额 (用于后台市场管理-财务信息图表)
 *    07.  getOrdersCount      获取账变表,记录总数量 (用于后台市场管理-日志信息图表)
 *    08.  getMoneyInCount     获取账变表,充值记录数量 (用于后台市场管理-日志信息图表)
 *    09.  getMoneyOutCount    获取账变表,提现记录数量 (用于后台市场管理-日志信息图表)
 *    10.  orderEnCode         账变订单号 加密&解密 函数
 *    11.  getErrorOrder       检测异常账变数据
 * 
 * @author     james,Tom,mark
 * @version    1.1.0
 * @package    passport
 */


/*****************************[ 宏定义帐变ID对应类型关系 ]**********************/
define("ORDER_TYPE_SJCZ",       1);   // 上级充值        pid=0   + 游戏币
define("ORDER_TYPE_KJCZ",       2);   // 跨级充值        pid=0   + 游戏币
define("ORDER_TYPE_XYCZ",       3);   // 信用充值        pid=0   + 游戏币
define("ORDER_TYPE_CZKF",       4);   // 充值扣费        pid=0   - 游戏币
define("ORDER_TYPE_BRTX",       5);   // 本人提现        pid=0   - 游戏币
define("ORDER_TYPE_KJTX",       6);   // 跨级提现        pid=0   - 游戏币
define("ORDER_TYPE_XJTX",       7);   // 下级提现        pid=0   + 游戏币
define("ORDER_TYPE_BRFQTX",     8);   // 本人发起提现    pid=0   - 游戏币
define("ORDER_TYPE_XJFQTX",     9);   // 下级发起提现    pid=0   + 游戏币
define("ORDER_TYPE_SWTXSQ",    10);   // 商务提现申请    pid=0   - 游戏币 for 总代 091125
define("ORDER_TYPE_SWTXSB",    11);   // 商务提现失败    pid=0   + 游戏币 for 总代 091125
define("ORDER_TYPE_XYKJ",      12);   // 信用扣减        pid=0   - 游戏币
define("ORDER_TYPE_SWTXCG",    13);   // 商务提现成功    pid=0   - 游戏币 for 总代 091125
define("ORDER_TYPE_YHZC",      14);   // 银行转出        pid=0   - 游戏币
define("ORDER_TYPE_ZRYH",      15);   // 转入银行        pid=0   + 游戏币
define("ORDER_TYPE_ZZZC",      16);   // 转账转出        pid=0   - 游戏币
define("ORDER_TYPE_ZZZR",      17);   // 转账转入        pid=0   + 游戏币
define("ORDER_TYPE_PDXEZR",    18);   // 频道小额转入    pid=0   + 游戏币
define("ORDER_TYPE_XEKC",      19);   // 小额扣除        pid=0   - 游戏币
define("ORDER_TYPE_XEJS",      20);   // 小额接收        pid=0   + 游戏币
define("ORDER_TYPE_TSJEQL",    21);   // 特殊金额清理    pid=0   - 游戏币
define("ORDER_TYPE_TSJEZL",    22);   // 特殊金额整理    pid=0   + 游戏币
define("ORDER_TYPE_LPCZ",      23);   // 理赔充值        pid=0   + 游戏币
define("ORDER_TYPE_GLYKJ",     24);   // 管理员扣减      pid=0   - 游戏币
define("ORDER_TYPE_ZZLP",      25);   // 转账理赔        pid=0   + 游戏币
define("ORDER_TYPE_PTTXSQ",    26);   // 平台提现申请    pid=0   - 游戏币
define("ORDER_TYPE_PTTXSB",    27);   // 平台提现失败    pid=0   + 游戏币
define("ORDER_TYPE_PTTXCG",    28);   // 平台提现成功    pid=0   - 游戏币
define("ORDER_TYPE_ZDTXCG",    29);   // 平台提现成功    pid=0   + 游戏币(总代加钱)
define("ORDER_TYPE_ZXTX",      30);   // 在线提现申请    pid=0   - 游戏币(冻结用户资金)
define("ORDER_TYPE_ZXCZ",      31);   // 在线充值    	   pid=0   + 游戏币(用户加钱)
define("ORDER_TYPE_ZXTXJD",    32);   // 在线提现解冻    pid=0   + 游戏币(解冻用户资金)
define("ORDER_TYPE_ZXTXKK",    33);   // 在线提现扣款    pid=0   - 游戏币(扣减用户资金)
define("ORDER_TYPE_ZXCZSF",   34);   // 在线充值手续费  pid=0   - 游戏币(用户减钱)
define("ORDER_TYPE_ZXTXSF",   35);   // 在线提现手续费   pid=0   - 游戏币(用户减钱)
define("ORDER_TYPE_RGTXDJ",   36);   // 人工提现冻结(二次审核)   pid=0   - 游戏币(用户减钱)
define("ORDER_TYPE_RGTXJD",   37);   // 人工提现解冻(二次审核)   pid=0   + 游戏币(用户加钱)
define("ORDER_TYPE_RGCZ",     38);   // 人工充值   			pid=0   + 游戏币(用户加钱)
define("ORDER_TYPE_SXFFH",    39);  // 人工充值手续费返还   	pid=0   + 游戏币(用户加钱)

class model_orders extends basemodel
{
    /**
     * 构造函数
     * @access  public
     * @return  void
     */
    function __construct( $aDBO = array() )
    {
        parent::__construct( $aDBO );
    }



    /**
     * 对用户账户资金额进行修改, 同时写入账变
     * 
     * 模拟原子操作: 用户账户金额变化的同时, 必须有一条相应的账变与之对应
     * 中间不出现任何事务操作, 成功返回全等于的 === TRUE, 失败返回错误码 (负1000以上)
     * 此函数将被嵌套在控制层的事务中, 所以尽量避免过多操作, 要求执行效率高
     * 
     * @param int    $aOrders['iLotteryId']     彩种ID
     * @param int    $aOrders['iMethodId']      玩法ID
     * @param int    $aOrders['iTaskId']        追号任务ID
     * @param int    $aOrders['iProjectId']     方案ID
     * 
     * @param int    $aOrders['iFromUserId']    (发起人) 用户id, 对应 `users`.id
     * @param int    $aOrders['iToUserId']      (关联人) 用户id, 对应 `users`.id
     * @param int    $aOrders['iOrderType']     账变类型(define定义的宏), 需要根据账变类型, 判断此条账变金额的增减关系
     * @param float  $aOrders['fMoney']         账变的金额变动情况, 4位精度 round( floatval($fMoney), 4);
     * @param int    $aOrders['sDescription']   账变的描述, 例如: 充值扣费(为xxxx)充值, 银行转出到xxxx
     *                                            为空则默认为账变类型 ordertype.cntitle
     * @param int    $aOrders['iAdminId']       管理员ID
     * @param string $aOrders['sAdminName']     管理员名
     * @param int    $aOrders['iChannelID']     发生帐变的频道ID
     * 
     * @param int    $aOrders['iTransferUserid']      目标平台账户 USERID
     * @param int    $aOrders['iTransferChannelid']   转账相关频道 ID (即:资金转出频道ID,或资金转入频道ID)
     * @param int    $aOrders['iTransferOrderid']     目标频道的对应账变ID
     * @param int    $aOrders['iTransferStatus']      转账状态:  1:请求(钱已扣); 2:成功; 3:失败
     * @param int    $aOrders['bIgnoreMinus']    忽略负数(仅用于撤销派奖)
     * @author tom   
    */
    function addOrders( $aOrders = array() )
    {
        // 01, 数据检查
        $iFromUserId = isset($aOrders['iFromUserId']) ? intval($aOrders['iFromUserId']) : 0; // 发起人ID
        $iToUserId   = isset($aOrders['iToUserId']) ? intval($aOrders['iToUserId']) : 0; // 关联人ID
        $iOrderType  = isset($aOrders['iOrderType']) ? intval($aOrders['iOrderType']) : 0; // 账变类型
        $fMoney      = isset($aOrders['fMoney']) ? round(floatval($aOrders['fMoney']),4) : 0; // 账变金额
        $sActionTime = isset($aOrders['sActionTime']) ? date("Y-m-d H:i:s",strtotime($aOrders['sActionTime'])) : date("Y-m-d H:i:s");
        $sTitle      = ''; // 根据账变类型的中文名
        $sDescription= ''; 
        $iAgentId    = isset($aOrders['iAgentId']) ? intval($aOrders['iAgentId']) : 0;
        $iAdminId    = isset($aOrders['iAdminId']) ? intval($aOrders['iAdminId']) : 0;
        $sAdminName  = isset($_SESSION['adminname']) ? daddslashes($_SESSION['adminname']) : 
                            ( isset($aOrders['sAdminName']) ? daddslashes($aOrders['sAdminName']) : '' );
        $iChannelId  = isset($aOrders['iChannelId']) ? intval($aOrders['iChannelId']) : SYS_CHANNELID;
        $bIgnoreMinus= isset($aOrders['bIgnoreMinus']) ? (bool)$aOrders['bIgnoreMinus'] : FALSE;
        
        if( $iFromUserId == 0 || !is_numeric($iFromUserId) )
        { // 用户ID错误
            return -1001;
        }
        if( $iOrderType < 1 || !is_numeric($iOrderType) )
        { // 账变类型ID错误, 账变类型ID已数字1开始编号, 防止传递未定义(define)的账变类型
            return -1002;
        }
        if( $fMoney < 0 )
        { // 账变金额错误, 不允许负数
            return -1003;
        }

        // 02, 检查账户锁定状态, 如果未锁, 直接返回 FALSE, 确保在控制层已将金额锁定
        $aRes = $this->oDB->getOne("SELECT * FROM `userfund` WHERE `userid`='$iFromUserId' AND `channelid`=".SYS_CHANNELID);
        if( $this->oDB->ar() != 1 || empty($aRes) )
        { // 获取用户频道资金数据失败
            return -1004;
        }
        if( $aRes['islocked'] != 1 )
        { // 用户资金账户未被锁, 禁止对其进行任何资金变化操作
            return -1005;
        }

        // 03, 金额增减操作, 需根据账变类型ID, 判断金额的增减情况
        $iNewCashBalance      = $aRes['cashbalance'];        // 发生帐变后 频道现金余额
        $iNewChannelBalance   = $aRes['channelbalance'];     // 发生账变后 频道账户余额
        $iNewHoldBalance      = $aRes['holdbalance'];        // 发生账变后 频道冻结金额 
        $iNewAvailableBalance = $aRes['availablebalance'];   // 发生账变后 频道可用余额
        switch( $iOrderType )
        {
            case ORDER_TYPE_SJCZ : // 1,上级充值
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '上级充值';
                break;
            case ORDER_TYPE_KJCZ : // 2,跨级充值
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '跨级充值';
                break;
            case ORDER_TYPE_XYCZ : // 3,信用充值
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                                                      // 信用欠款 B+ (增加写在 topproxyset 表)
                $sTitle = '信用充值';
                break;
            case ORDER_TYPE_CZKF : // 4,充值扣费
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '充值扣费';
                break;
            case ORDER_TYPE_BRTX : // 5,本人提现
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '本人提现';
                break;
            case ORDER_TYPE_KJTX : // 6,跨级提现
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '跨级提现';
                break;
            case ORDER_TYPE_XJTX : // 7,下级提现
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '下级提现';
                break;
            case ORDER_TYPE_BRFQTX : // 8,本人发起提现
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '本人发起提现';
                break;
            case ORDER_TYPE_XJFQTX : // 9, 下级发起提现
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '下级发起提现';
                break;
            case ORDER_TYPE_PTTXSQ : // 10, 平台提现申请
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $iNewHoldBalance      += $fMoney;     // 冻结金额 D+
                $sTitle = '平台提现申请';
                break;
            case ORDER_TYPE_PTTXSB : // 11,平台提现失败
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $iNewHoldBalance      -= $fMoney;     // 冻结金额 D-
                $sTitle = '平台提现失败';
                break;
            case ORDER_TYPE_XYKJ : // 12,信用扣减
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                                                      // 信用欠款 B- (增加写在 topproxyset 表)
                $sTitle = '信用扣减';
                break;
            case ORDER_TYPE_PTTXCG : // 13,平台提现成功
                $iNewCashBalance    -= $fMoney;       // 现金余额 A-
                $iNewChannelBalance -= $fMoney;       // 账户余额 C-
                $iNewHoldBalance    -= $fMoney;       // 冻结金额 D-
                $sTitle = '平台提现成功';
                break;
            case ORDER_TYPE_YHZC : // 14,银行转出
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '银行转出';
                break;
            case ORDER_TYPE_ZRYH : // 15,转入银行
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '转入银行';
                break;
            case ORDER_TYPE_ZZZC : // 16,转账转出
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '转账转出';
                break;
            case ORDER_TYPE_ZZZR : // 17,转账转入
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '转账转入';
                break;
            case ORDER_TYPE_PDXEZR : // 18,频道小额转入
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '频道小额转入';
                break;
            case ORDER_TYPE_XEKC : // 19,小额扣除
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '小额扣除';
                break;
            case ORDER_TYPE_XEJS : // 20, 小额接收
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '小额接收';
                break;
            case ORDER_TYPE_TSJEQL : // 21,特殊金额清理
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '特殊金额清理';
                break;
            case ORDER_TYPE_TSJEZL : // 22,特殊金额整理
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '特殊金额整理';
                break;
            case ORDER_TYPE_LPCZ : //23,理赔充值
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '理赔充值';
                break;
            case ORDER_TYPE_GLYKJ : //24,管理员扣减
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '管理员扣减';
                break;
            case ORDER_TYPE_SWTXSQ : // 26, 商务提现申请
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $iNewHoldBalance      += $fMoney;     // 冻结金额 D+
                $sTitle = '商务提现申请';
                break;
            case ORDER_TYPE_SWTXSB : // 27, 商务提现失败
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $iNewHoldBalance      -= $fMoney;     // 冻结金额 D-
                $sTitle = '商务提现失败';
                break;
            case ORDER_TYPE_SWTXCG : // 28, 商务提现成功
                $iNewCashBalance    -= $fMoney;       // 现金余额 A-
                $iNewChannelBalance -= $fMoney;       // 账户余额 C-
                $iNewHoldBalance    -= $fMoney;       // 冻结金额 D-
                $sTitle = '商务提现成功';
                break;
            case ORDER_TYPE_ZDTXCG : // 29,平台提现成功 (总代处理下级)
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '平台提现成功';
                break;
            case ORDER_TYPE_ZXCZ : // 31,在线充值
            	$iNewCashBalance      += $fMoney;     // 现金余额 A+
	            $iNewChannelBalance   += $fMoney;     // 账户余额 C+
	            $iNewAvailableBalance += $fMoney;     // 可用余额 E+
	            $sTitle = '在线充值';
	            break; 
            case ORDER_TYPE_ZXTX : 	// 30,在线提现申请
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $iNewHoldBalance      += $fMoney;     // 冻结金额 D+
                $sTitle = '在线提现申请成功';
                break;
            case ORDER_TYPE_ZXTXJD : 	// 32,在线提现解冻
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $iNewHoldBalance      -= $fMoney;     // 冻结金额 D-
                $sTitle = '在线提现申请解冻';
                break;
            case ORDER_TYPE_ZXTXKK : 	// 33,在线提现扣款
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '在线提现申请扣款';
                break;
            case ORDER_TYPE_ZXCZSF : // 34,在线充值
            	$iNewCashBalance      -= $fMoney;     // 现金余额 A-
	            $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '充值手续费';
	            break; 
	        case ORDER_TYPE_ZXTXSF : 	// 35,在线提现手续费
                $iNewCashBalance      -= $fMoney;     // 现金余额 A-
                $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $sTitle = '在线提现手续费';
                break;
            case ORDER_TYPE_RGTXDJ : 		// 36,人工提现冻结(二次审核)
                $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
                $iNewHoldBalance      += $fMoney;     // 冻结金额 D+
                $sTitle = '人工提现冻结';
                break;
            case ORDER_TYPE_RGTXJD : 		// 37,人工提现解冻(二次审核)
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $iNewHoldBalance      -= $fMoney;     // 冻结金额 D-
                $sTitle = '人工提现解冻';
                break;
            case ORDER_TYPE_RGCZ : //38，人工充值
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '人工充值';
                break;
            case ORDER_TYPE_SXFFH : //39，手续费返还
                $iNewCashBalance      += $fMoney;     // 现金余额 A+
                $iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '手续费返还';
                break;
            default:
                return -1006; // 账变类型错误,未被程序枚举处理
        }

        // 在 '撤销派奖' 功能中, 允许账户余额为负数
        if( $bIgnoreMinus==FALSE && ($iNewChannelBalance<0 || $iNewHoldBalance<0 || $iNewAvailableBalance<0) )
        { // 金额不正确
            return -1009;
        }

        if( !empty($aOrders['sDescription']) )
        {
            $sDescription = daddslashes( $aOrders['sDescription'] );
        }
        else
        { // 如果账变描述为空, 则使用账变类型
            $sDescription = $sTitle;
        }

        // 04, 账变写入
        $aOrderDatas = array();
        $aOrderDatas['fromuserid']       =   $iFromUserId;
        $aOrderDatas['touserid']         =   $iToUserId;
        $aOrderDatas['ordertypeid']      =   $iOrderType;
        $aOrderDatas['title']            =   $sTitle;
        $aOrderDatas['amount']           =   round(floatval($fMoney),4);
        $aOrderDatas['description']      =   $sDescription;
        $aOrderDatas['precash']          =   round(floatval($aRes['cashbalance']),4);
        $aOrderDatas['prebalance']       =   round(floatval($aRes['channelbalance']),4);
        $aOrderDatas['prehold']          =   round(floatval($aRes['holdbalance']),4);
        $aOrderDatas['preavailable']     =   round(floatval($aRes['availablebalance']),4);
        $aOrderDatas['cashbalance']      =   round(floatval($iNewCashBalance),4);
        $aOrderDatas['channelbalance']   =   round(floatval($iNewChannelBalance),4);
        $aOrderDatas['holdbalance']      =   round(floatval($iNewHoldBalance),4);
        $aOrderDatas['availablebalance'] =   round(floatval($iNewAvailableBalance),4);
        $aOrderDatas['agentid']          =   $iAgentId;
        $aOrderDatas['adminid']          =   $iAdminId;
        $aOrderDatas['adminname']        =   $sAdminName;
        $aOrderDatas['clientip']         =   getRealIP();
        $aOrderDatas['proxyip']          =   isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $aOrderDatas['times']            =   date("Y-m-d H:i:s", time());
        $aOrderDatas['actiontime']       =   $sActionTime;
        $aOrderDatas['channelid']        =   $iChannelId;
        // 转账相关
        $aOrderDatas['uniquekey']         = isset($aOrders['sUniqueKey']) ? addslashes($aOrders['sUniqueKey']) : '';
        $aOrderDatas['transferuserid']    = isset($aOrders['iTransferUserid']) ? intval($aOrders['iTransferUserid']) : 0;    // 转账目标账户USERID
        $aOrderDatas['transferchannelid'] = isset($aOrders['iTransferChannelid']) ? intval($aOrders['iTransferChannelid']) : 0; // 转账目标频道ID
        $aOrderDatas['transferorderid']   = isset($aOrders['iTransferOrderid']) ? intval($aOrders['iTransferOrderid']) : 0;   // 目标频道账变ID
        $aOrderDatas['transferstatus']    = isset($aOrders['iTransferStatus']) ? intval($aOrders['iTransferStatus']) : 0;    // 转账状态 1:请求;2:成功;3:失败
        // 定义全局账变id，在写入账变记录时记录下账变id
        if (!isset($_iOrderEntryOE)){
    		global $_iOrderEntryOE;
    	}
        $iResult = $this->oDB->insert( 'orders', $aOrderDatas );
        // 将定入的账变id返回使用,$_iOrderEntryOE为全局变量
        if (!isset($_iOrderEntryOE)){
        	$_iOrderEntryOE = $iResult;
        }
        if( !$iResult )
        { // 账变记录插入失败
            return -1007;
        }

        // 05, 更新用户账户资金
        $aUserFund = array();
        $aUserFund['cashbalance']       =  round(floatval($iNewCashBalance),4);
        $aUserFund['channelbalance']    =  $aOrderDatas['channelbalance'];
        $aUserFund['availablebalance']  =  $aOrderDatas['availablebalance'];
        $aUserFund['holdbalance']       =  $aOrderDatas['holdbalance'];
        $aUserFund['lastupdatetime'] = date('Y-m-d H:i:s');
        if( $iOrderType != ORDER_TYPE_XEKC && $iOrderType != ORDER_TYPE_XEJS && $iOrderType != ORDER_TYPE_PDXEZR
            && $iOrderType != ORDER_TYPE_TSJEQL && $iOrderType != ORDER_TYPE_TSJEZL )
        {//特殊帐变不算入用户活跃
            $aUserFund['lastactivetime']    =  $aOrderDatas['times']; // 使用发生账变的时间
        }
        $this->oDB->update( 'userfund', $aUserFund, 
            " `userid`='$iFromUserId' AND `channelid`=". SYS_CHANNELID ." AND `islocked`=1 LIMIT 1");
        unset($aOrderDatas);
        if( $this->oDB->errno() > 0 || $this->oDB->ar() != 1 )
        { // 账户金额更新失败
            return -1008; 
        }
        return TRUE;
    }



    /**
     * 查看帐变，可以自定义查询条件[带分页效果][后台调用]
     * 
     * @access  public
     * @author  Tom
     * @param   string  $sFields      // 要查询的内容，表别名:usertree=>ut,orders=>o,ordertype=>ot
     * @param   string  $sCondition   // 附加的查询条件，以AND 开始
     * @param   int     $iPageRecords // 每页显示的条数
     * @param   int     $iCurrPage    // 当前页
     * @return  array
     */
    public function & getAdminOrderList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
    {
        $sTableName = "`orders` AS o LEFT JOIN `ordertype` AS ot ON o.`ordertypeid`=ot.`id` ".
                      " LEFT JOIN `usertree` AS ut ON ut.`userid`=o.`fromuserid` ";
        $sFields = "ut.`userid`,ut.`username`,o.`entry`,o.`title`,o.`amount`,o.`preavailable`,o.`availablebalance`, ".
                   " o.`times`,o.`description`,o.`ordertypeid`,o.`uniquekey`,o.`transferstatus`,ot.`cntitle`,ot.`entitle`, o.`adminname`, `operations` AS signamount,o.`clientip` ";
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage,' ORDER BY o.`entry` DESC '); //times
    }



    /**
     * 账变列表的最后一页,进行数据总体结算
     * 
     * @access  public
     * @author  Tom
     * @param   string  $sCondition      // 要查询的内容
     * @return  array
     */
    public function & getAdminOrderStat( $sCondition = "" )
    {
        $sTableName = "`orders` AS o LEFT JOIN `ordertype` AS ot ON o.`ordertypeid`=ot.`id` ".
                      " LEFT JOIN `usertree` AS ut ON ut.`userid`=o.`fromuserid` ";
        $sFields    = " SUM(o.`amount`) AS amounts, `operations` ";
        $sWhere     = "SELECT $sFields FROM  $sTableName WHERE $sCondition GROUP BY `operations` ";
        return $this->oDB->getAll( $sWhere );
    }



    /**
     * 查看帐变，可以自定义查询条件[带分页效果][前台调用]
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID,[多个用户用数组]
     * @param   string  $sField     //要查询的内容，表别名:usertree=>ut,orders=>o,ordertype=>ot
     * @param   string  $sAndWhere  //附加的查询条件，以AND 开始
     * @param   string  $sOrderBy   //排序条件，默认按照帐变ID排序
     * @param   int     $iPageRecords //每页显示的条数
     * @param   int     $iCurrPage  //当前页
     * @param   int     $iAllChildren   
     *  //0:自己，1:自己和直接下级，2:自己和所有下级，3:只包括直接下级，4:只包括所有下级，5:销售管理员特殊组
     * @param   return  //成功返回帐变列表array('affects'=>总记录数,'results'=>结果集合)，失败返回FALSE
     * @author james
     */
    public function & getUserOrderList( $iUserId, $sFields = '', $sAndWhere = '', $sOrderBy = '', 
                                    $iResultCount = 0, $iPageRecords = 20, $iCurrPage = 1, $iAllChildren = 0 )
    {
        $aResult = array(
                            'affects' => 0,
                            'results' => array(),
                            'icount'  => array( 'in' => 0, 'out' => 0, 'left' => 0 )
                        );//初始结果集
        if( empty($iUserId) || !is_numeric($iResultCount) )
        {//失败
            return $aResult;
        }
        $sCondition = "";
        if( is_numeric($iUserId) )
        {//只传了一个用户
            if( $iAllChildren == 1 )
            { // 包括自己和直接下级
                $sCondition .= " AND ( ut.`userid`='".$iUserId."' OR ut.`parentid`='".$iUserId."' ) ";
            }
            elseif( $iAllChildren == 2 )
            { // 自己和所有下级
                $sCondition .= " AND ( ut.`userid`='".$iUserId."' OR FIND_IN_SET('".$iUserId."',ut.`parenttree`) ) ";
            }
            elseif( $iAllChildren == 3 )
            {//只包括直接下级
                $sCondition .= " AND ut.`parentid`='".$iUserId."' ";
            }
            elseif( $iAllChildren == 4 )
            {//只包括所有下级
                $sCondition .= " AND FIND_IN_SET('".$iUserId."',ut.`parenttree`) ";
            }
            else 
            {//只包括自己
                $sCondition .= " AND ut.`userid`='".$iUserId."' ";
            }
        }
        if( is_array($iUserId) )
        {//传了一组用户
            if( $iAllChildren == 5 )
            {
                $sCondition .= " AND ut.`lvproxyid` IN(".implode(',',$iUserId).") ";
            }
            else 
            {
                $sCondition .= " AND ut.`userid` IN(".implode(',',$iUserId).") ";
            }
        }
        $sCondition .= $sAndWhere;
        //获取统计记录
        if( empty($iResultCount) || $iCurrPage < 2 )
        {
            $sSql = " SELECT COUNT(o.`entry`) AS JAMESCOUNT FROM `usertree` AS ut 
                  LEFT JOIN `orders` AS o ON o.`fromuserid`=ut.`userid`
                  LEFT JOIN `ordertype` AS ot ON o.`ordertypeid`=ot.`id` 
                  WHERE ut.`usertype`<2 AND ut.`isdeleted`='0' ".$sCondition;
            $this->oDB->query($sSql);
            $iResultCount = $this->oDB->fetchArray();
            $iResultCount = $iResultCount['JAMESCOUNT'];
        }
        $aResult['affects'] = intval($iResultCount);
        if( $aResult['affects'] == 0 )
        {//如果结果为空直接返回
            return $aResult;
        }
        //分页对当前页的判断
        $iCurrPage    = (is_numeric($iCurrPage) && $iCurrPage>0) ? intval($iCurrPage) : 1; // 默认第一页
        $iPageRecords =(is_numeric($iPageRecords) && $iPageRecords>0 && $iPageRecords<100 ) 
                            ? intval($iPageRecords) : 25;
        $sFields = empty($sFields) ? "ut.`username`,o.`fromuserid`,o.`title`,o.`preavailable`,
                  o.`availablebalance`,o.`transferstatus`,ot.`cntitle`,ot.`entitle`" : $sFields;
        //以下为一些必查字段
        $sFields .= ",o.`entry`,o.`times`,ot.`operations`,o.`ordertypeid`,o.`amount`,o.`description`,o.`uniquekey`";
        $sSql = " SELECT ".$sFields." FROM `usertree` AS ut 
                  LEFT JOIN `orders` AS o ON o.`fromuserid`=ut.`userid`
                  LEFT JOIN `ordertype` AS ot ON o.`ordertypeid`=ot.`id` 
                  WHERE ut.`usertype`<2 AND ut.`isdeleted`='0' ".$sCondition;
        if( !empty($sOrderBy) )
        {
            $sSql .= " ORDER BY ".$sOrderBy;
        }
        $sSql .= " LIMIT ".(($iCurrPage - 1) * $iPageRecords).",".$iPageRecords;
        $this->oDB->query( $sSql );
        $iCountIn   = 0;    //收入统计
        $iCountOut  = 0;    //支出统计
        while( FALSE != ($tempData = $this->oDB->fetchArray()) )
        {
            $tempData['orderno']    = $this->orderEnCode(
                                        date("Ymd",strtotime($tempData['times']))."-".$tempData['entry'],
                                        "ENCODE");      //加密编号
            if( $tempData['operations'] == 0 )
            {
                $iCountOut += $tempData['amount'];
            }
            else 
            {
                $iCountIn  += $tempData['amount'];
            }
            if( ($tempData['uniquekey'] != '') || in_array($tempData['ordertypeid'],array(ORDER_TYPE_CZKF,ORDER_TYPE_XJTX,ORDER_TYPE_XJFQTX,ORDER_TYPE_ZDTXCG)) )
            {//允许显示备注
                $tempData['allowdec'] = 1;
            }
            $aResult['results'][] = $tempData;
        }
        $aResult['icount'] = array(
                                    'in'   => $iCountIn,
                                    'out'  => $iCountOut,
                                    'left' => ($iCountIn-$iCountOut)
                                   );
        unset($tempData);
        return $aResult;
    }



    /**
     * 查询所有的帐变类型
     * 
     * @author   james
     * @access   public
     * @param    $sReturnType   arr | opts
     * @param    $mSelected     arr | int | string
     * @param     $sAndWhere     string
     * @return   mix // 返回结果集数组,或 html.select.options
     */
    public function getOrderType( $sReturnType = 'arr', $mSelected = '', $sAndWhere = '' )
    {
        $sSql    = "SELECT * FROM `ordertype` WHERE 1 ";
        $sSql   .= $sAndWhere; 
        $aReturn = $this->oDB->getDataCached( $sSql );
        unset( $sSql );
        unset( $sAndWhere );
        if( $sReturnType == 'arr' )
        {
            return $aReturn;
        }
        // 返回 html.select.options
        $sReturn = '';
        $aSelect = array();
        if( is_int($mSelected) && $aSelect != -1 )
        {
            foreach( $aReturn as $v )
            {
                $sSel     = $mSelected==$v['id'] ? 'SELECTED' : '';
                $sReturn .= "<OPTION $sSel value=\"".$v['id']."\">".$v['cntitle']."</OPTION>";
            }
            return $sReturn;
        }
        if( is_string($mSelected) )
        {
            if( strstr($mSelected,',') )
            {
                $aSelect = explode(',',$mSelected);
            }
            else 
            {
                $aSelect[0] = intval($mSelected);
            }
        }
        if( is_array($mSelected) )
        {
            $aSelect = $mSelected;
        }
        
        foreach( $aReturn as $v )
        {
            $sSel = in_array($v['id'], $aSelect) ? 'SELECTED' : '';
            $sReturn .= "<OPTION $sSel value=\"".$v['id']."\">".$v['cntitle']."</OPTION>";
        }
        return $sReturn;
    }



    /**
     * 获取账变表中, 当天充值总额 (用于后台市场管理-财务信息图表)  
     */
    public function getMoneyRealIn( $sDate = '' )
    {
        $aArray = array(
            ORDER_TYPE_SJCZ,   // 上级充值
            ORDER_TYPE_KJCZ,   // 跨级充值
            ORDER_TYPE_XYCZ,   // 信用充值
            ORDER_TYPE_XEJS,   // 小额接收
            ORDER_TYPE_TSJEZL, // 特殊金额整理
            ORDER_TYPE_LPCZ,   // 理赔充值
            ORDER_TYPE_ZXCZ,	//在线充值 6/9/2010
            /* louis */
            ORDER_TYPE_RGCZ,
            /* louis */
        );
        $sOrderTypeStr  = '';
        $sOrderTypeStr  = join( ',', $aArray );
        $sDate   = empty($sDate) ? date('Y-m-d 00:00:00') : daddslashes($sDate);
        $aResult = $this->oDB->getOne( "SELECT SUM(`amount`) AS TOMSUM FROM `orders` WHERE ".
                    " `adminid` != 0 AND `times` >= '$sDate' AND `ordertypeid` IN ( $sOrderTypeStr ) " );
        return ($this->oDB->ar()>0) ? $aResult['TOMSUM'] : 0;
    }



    /**
     * 获取账变表中, 当天提现总额 (用于后台市场管理-财务信息图表) 
     * 
     *    ordertypeid = 5 本人提现
     *    ordertypeid = 6 跨级提现
     *    ordertypeid = 13 大额提现
     * 
     *    充值总额 = orders.adminid !=0 && ordertypeid in ( 5,6,13 ) 
     */
    public function getMoneyRealOut( $sDate = '' )
    {
        $aArray = array(
                ORDER_TYPE_BRTX,    // 本人提现
                ORDER_TYPE_PTTXCG,  // 平台提现成功 (大额提现)
                ORDER_TYPE_XYKJ,    // 信用扣减
                ORDER_TYPE_XEKC,    // 小额扣除
                ORDER_TYPE_TSJEQL,  // 特殊金额清理
                ORDER_TYPE_GLYKJ,   // 管理员扣减
                ORDER_TYPE_ZXTXKK,	//在线提现扣款 6/9/2010
        );
        $sOrderTypeStr  = '';
        $sOrderTypeStr  = join( ',', $aArray );
        $sDate   = empty($sDate) ? date('Y-m-d 00:00:00') : daddslashes($sDate);
        $aResult = $this->oDB->getOne( "SELECT SUM(`amount`) AS TOMSUM FROM `orders` WHERE ".
                    " `adminid` != 0 AND `times` >= '$sDate' AND `ordertypeid` IN ( $sOrderTypeStr ) " );
        return ($this->oDB->ar() > 0) ? $aResult['TOMSUM'] : 0;
    }



    /**
     * 获取账变表,记录总数量 (用于后台市场管理-日志信息图表) 
     */
    public function getOrdersCount( $sDate = '' )
    {
        $sDate   = empty($sDate) ? date('Y-m-d 00:00:00') : daddslashes($sDate);
        $aResult = $this->oDB->getOne( "SELECT count(`entry`) AS TOMCOUNT FROM `orders` WHERE `times` >= '$sDate' " );
        return ($this->oDB->ar() > 0) ? $aResult['TOMCOUNT'] : 0;
    }

    /**
     * 获取账变表,充值记录数量 (用于后台市场管理-日志信息图表)
     *   ordertypeid 参考 ordertype 表
     *   与充值记录相关 id = 1,2,3,4
     */
    public function getMoneyInCount( $sDate = '' )
    {
        $sDate   = empty($sDate) ? date('Y-m-d 00:00:00') : daddslashes($sDate);
        $aResult = $this->oDB->getOne( "SELECT count(`entry`) AS TOMCOUNT FROM `orders` WHERE ".
                    " `times` >= '$sDate' AND `ordertypeid` in (1,2,3,4) " );
        return ($this->oDB->ar() > 0) ? $aResult['TOMCOUNT'] : 0;
    }

    /**
     * 获取账变表,提现记录数量 (用于后台市场管理-日志信息图表)
     *   ordertypeid 参考 ordertype 表
     *   与提现记录相关 id = 5,6,7,8,9,10,11,13
     */
    public function getMoneyOutCount( $sDate = '' )
    {
        $sDate   = empty($sDate) ? date('Y-m-d 00:00:00') : daddslashes($sDate);
        $aResult = $this->oDB->getOne( "SELECT count(`entry`) AS TOMCOUNT FROM `orders` WHERE ".
                    " `times` >= '$sDate' AND `ordertypeid` in (5,6,7,8,9,10,11,13) " );
        return ($this->oDB->ar() > 0) ? $aResult['TOMCOUNT'] : 0;
    }


    /**
     * 简单加密解密处理
     * @access  static
     * @author  james
     * @param   string  $string     //要加密解密的字符串
     * @param   string  $option     //加密解密选项  DECODE:解密   ENCODE:加密
     */
    static function orderEnCode( $sString, $sOption = "DECODE" )
    {
        if( $sOption == "DECODE" )
        {//解密
            $aData = explode( "V", $sString );
            if( !isset($aData[1]) )
            {
                return 0;
            }
            for( $i=0; $i<strlen($aData[1]); $i++ )
            {
                $ascii = ord($aData[1][$i]);
                if( $ascii > 74 )
                {
                    $ascii = 42;
                }
                else
                {
                    $ascii -= 17;
                }
                $aData[1][$i] = chr($ascii);
            }
            $aData[1] = str_replace( "*", "", $aData[1] );
            return is_numeric($aData[1]) ? $aData[1] : 0;
        }
        else 
        {//加密
            $aData = explode( "-", $sString );
            if( !isset($aData[1]) )
            {
                return $aData;
            }
            for( $i=strlen($aData[1]); $i<=8; $i++ )
            {
                $aData[1] .= "*";
            }
            $zero = 0;
            for( $i=0; $i<strlen($aData[1]); $i++ )
            {
                $ascii = ord($aData[1][$i]);
                if( $ascii == 42 )
                {
                    $ascii += $zero + 33;
                    $zero++;
                }
                else
                {
                    $ascii += 17;
                }
                $aData[1][$i] = chr($ascii);
            }
            return $aData[0]."V".$aData[1];
        }
    }



    /**
     * 获取总代资金流入流出结果集
     * @param datetime   $tBeginTime
     * @param datetime   $tEndTime
     * @param string     $sCondition    // 公司销售管理员,对应总代的SQL条件 in( 1,2,3) | 非销售管理员则为空
     * @return array     $aReturn
     * @author Tom
     */
    public function & getTopProxyCashInOut( $tBeginTime = 0, $tEndTime = 0, $sCondition = '',
                                            $iIsTester = -1, $iIsFrozen = -1 )
    {
        $aReturn = array();

        // 01, 初始化总代结果集数组
        $sWhere = ' 1 ' . $sCondition;
        if( is_numeric($iIsTester) && $iIsTester != -1)//测试账户
        {
            $iIsTester = intval($iIsTester);
            $sWhere     .= " AND `istester` = '$iIsTester' ";
            $sCondition .= " AND ut.`istester` = '$iIsTester' ";
        }
        $sConditionChild = $sCondition;
        if( is_numeric($iIsFrozen) && $iIsFrozen != -1 )//冻结账户
        {
            $iIsFrozen = intval($iIsFrozen);
            $iIsFrozen == 0 ? $sWhere .= " AND `isfrozen` = '$iIsFrozen' " : $sWhere .= " AND `isfrozen` >= '$iIsFrozen' ";
            $iIsFrozen == 0 ? $sCondition .= " AND ut.`isfrozen` = '$iIsFrozen' " : $sCondition .= " AND ut.`isfrozen` >= '$iIsFrozen' ";
        }
        $aProxy = $this->oDB->getAll("SELECT `userid`,`username`,`usertype` FROM `usertree` WHERE $sWhere AND `isdeleted` = 0 AND `parentid`=0 ORDER BY username");
        foreach( $aProxy AS $v )
        {
            $aReturn[ $v['userid'] ]['username']  = $v['username'];
            $aReturn[ $v['userid'] ]['usertype']  = $v['usertype'];
            $aReturn[ $v['userid'] ]['handcashin']    = 0; // 人工资金流入
            $aReturn[ $v['userid'] ]['handcashout']   = 0; // 人工资金流出
            /* louis */
            $aReturn[ $v['userid'] ]['emailhandcashin']   = 0; // 人工资金流入for email deposit
            /* louis */
            $aReturn[ $v['userid'] ]['cashin']    = 0; // 资金流入
            $aReturn[ $v['userid'] ]['cashout']   = 0; // 资金流出
            $aReturn[ $v['userid'] ]['cashdiff']  = 0; // 充提结余
            $aReturn[ $v['userid'] ]['creditin']  = 0; // 信用充值
            $aReturn[ $v['userid'] ]['creditout'] = 0; // 信用扣减
            $aReturn[ $v['userid'] ]['cashlpin']  = 0; // 理赔充值
            $aReturn[ $v['userid'] ]['cashlpout'] = 0; // 管理员扣减
            // 4/14/2010 add
            $aReturn[ $v['userid'] ]['cashpaymentin'] = 0; // 在线充值
            $aReturn[ $v['userid'] ]['cashpaymentout'] = 0; // 在线提现
            $aReturn[ $v['userid'] ]['cashpaymentfeein'] = 0; // 在线充值手续费
            $aReturn[ $v['userid'] ]['cashemailandhandfeein'] = 0; // email充值和人工充值手续费
            $aReturn[ $v['userid'] ]['cashpaymentfeeout'] = 0; // 在线提现手续费
            
        }
        
        // 4/14/2010 add 

        // 14, 获取人工总代资金流入(充值金额
        $aHandCashIn = $this->getTopProxyHandCash( $tBeginTime, $tEndTime, 'handin', $sCondition );
        foreach( $aHandCashIn AS $v )
        {
            if( !empty($v['lvtopid']) )
            {
                $aReturn[ $v['lvtopid'] ]['handcashin'] += $v['TOMSUM'];
            }
        }
        unset( $aHandCashIn );
        
       	/* louis */
        // 16, 获取人工总代资金流入(充值金额 for email deposit)
        $aEmailHandCashIn = $this->getTopProxyHandCash( $tBeginTime, $tEndTime, 'emailhandin', $sCondition );
        foreach( $aEmailHandCashIn AS $v )
        {
            if( !empty($v['lvtopid']) )
            {
                $aReturn[ $v['lvtopid'] ]['emailhandcashin'] += $v['TOMSUM'];
            }
        }
        unset( $aEmailHandCashIn );
        /* louis */
		
        // 15, 获取人工总代资金流出(提现金额
        $aHandCashOut = $this->getTopProxyHandCash( $tBeginTime, $tEndTime, 'handout', $sCondition );
        foreach( $aHandCashOut AS $v )
        {
            if( !empty($v['lvtopid']) )
            {
                $aReturn[ $v['lvtopid'] ]['handcashout'] += $v['TOMSUM'];
            }
        }
        unset( $aHandCashOut );
        
        
        // 02, 获取总代本人的资金流入(充值金额) DONE
        $aCashIn = $this->getTopProxyCash( $tBeginTime, $tEndTime, 'allin', $sCondition );
        foreach( $aCashIn AS $v )
        {
            if( !empty($v['fromuserid']) )
            {
                $aReturn[ $v['fromuserid'] ]['cashin'] = $v['TOMSUM'];
            }
        }
        unset( $aCashIn );
		
        // 03, 获取总代本人的资金流出(提现金额) DONE
        $aCashOut = $this->getTopProxyCash( $tBeginTime, $tEndTime, 'allout', $sCondition );
        foreach( $aCashOut AS $v )
        {
            if( !empty($v['fromuserid']) )
            {
                $aReturn[ $v['fromuserid'] ]['cashout'] = $v['TOMSUM'];
            }
        }
        unset( $aCashOut );
		
        // 04, 获取总代本人的信用充值 DONE
        $aCreditIn = $this->getTopProxyCash( $tBeginTime, $tEndTime, 'creditin', $sCondition );
        foreach( $aCreditIn AS $v )
        {
            if( !empty($v['fromuserid']) )
            {
                $aReturn[ $v['fromuserid'] ]['creditin'] = $v['TOMSUM'];
            }
        }
        unset( $aCreditIn );
		
        // 05, 获取总代本人的信用扣减 DONE
        $aCreditOut = $this->getTopProxyCash( $tBeginTime, $tEndTime, 'creditout', $sCondition );
        foreach( $aCreditOut AS $v )
        {
            if( !empty($v['fromuserid']) )
            {
                $aReturn[ $v['fromuserid'] ]['creditout'] = $v['TOMSUM'];
            }
        }
        unset( $aCreditOut );
		
        // 06, 获取总代下级资金跨级转入 DONE
        $aChildCashIn = $this->getTopProxyChildCash( $tBeginTime, $tEndTime, 'in', $sConditionChild ); //in
        foreach( $aChildCashIn AS $v )
        {
            if( !empty($v['lvtopid']) && isset($aReturn[$v['lvtopid']]))
            {
                $aReturn[ $v['lvtopid'] ]['cashin'] += $v['TOMSUM'];
                
            }
        	
        }
        unset( $aChildCashIn );
		
        // 07, 获取总代下级资金跨级转出 DONE
        $aChildCashOut = $this->getTopProxyChildCash( $tBeginTime, $tEndTime, 'out', $sConditionChild );
        foreach( $aChildCashOut AS $v )
        {
            if( !empty($v['lvtopid'])  && isset($aReturn[$v['lvtopid']]))
            {
                $aReturn[ $v['lvtopid'] ]['cashout'] += $v['TOMSUM'];
            }
        }
        unset( $aChildCashOut );

        // 08, 获取总代及其下级理赔充值
        $aCashLpIn = $this->getTopProxyLpCash( $tBeginTime, $tEndTime, 'in', $sConditionChild );
        
        foreach( $aCashLpIn AS $v )
        {
            if( !empty($v['lvtopid'])  && isset($aReturn[$v['lvtopid']]))
            {
            	
                $aReturn[ $v['lvtopid'] ]['cashlpin'] += $v['TOMSUM'];
                
            }
        }
        unset( $aCashLpIn );
        
        // 09, 获取总代及下级 "管理员扣减" 
        $aCashLpOut = $this->getTopProxyLpCash( $tBeginTime, $tEndTime, 'out', $sConditionChild);
        foreach( $aCashLpOut AS $v )
        {
            if( !empty($v['lvtopid'])  && isset($aReturn[$v['lvtopid']]))
            {
                $aReturn[ $v['lvtopid'] ]['cashlpout'] += $v['TOMSUM'];
            }
        }
        unset( $aCashLpOut );
        
        // 10, 获取总代及下级在线充值金额
        $aPaymentCashIn = $this->getTopProxyCashPayment( $tBeginTime, $tEndTime, 'in', $sCondition );
        foreach( $aPaymentCashIn AS $v )
        {
            if( !empty($v['lvtopid']) )
            {
                $aReturn[ $v['lvtopid'] ]['cashpaymentin'] += $v['TOMSUM'];
                $aReturn[ $v['lvtopid'] ]['cashin'] += $v['TOMSUM'];
            }
        }
        unset( $aPaymentCashIn );

        // 11, 获取总代及下级在线提现金额)
        $aPaymentCashOut = $this->getTopProxyCashPayment( $tBeginTime, $tEndTime, 'out', $sCondition );
        foreach( $aPaymentCashOut AS $v )
        {
            if( !empty($v['lvtopid']) )
            {
                $aReturn[ $v['lvtopid'] ]['cashpaymentout'] += $v['TOMSUM'];
                $aReturn[ $v['lvtopid'] ]['cashout'] += $v['TOMSUM'];
            }
        }
        
    	// 12, 获取总代及下级在线充值手续费金额
        $aPaymentCashFeeIn = $this->getTopProxyCashPayment( $tBeginTime, $tEndTime, 'feein', $sCondition );
        foreach( $aPaymentCashFeeIn AS $v )
        {
            if( !empty($v['lvtopid']) )
            {
                $aReturn[ $v['lvtopid'] ]['cashpaymentfeein'] += $v['TOMSUM'];
                $aReturn[ $v['lvtopid'] ]['cashin'] -= $v['TOMSUM'];
                
            }
        }
        unset( $aPaymentCashFeeIn );
        
        /* louis */
        // 12, 获取总代及下级email充值和人工充值手续费金额
        $aEmailAndHandFeeIn = $this->getTopProxyCashPayment( $tBeginTime, $tEndTime, 'emailandhandfeein', $sCondition );
        foreach( $aEmailAndHandFeeIn AS $v )
        {
            if( !empty($v['lvtopid']) )
            {
                $aReturn[ $v['lvtopid'] ]['cashpaymentfeein'] -= $v['TOMSUM'];
                $aReturn[ $v['lvtopid'] ]['cashin'] += $v['TOMSUM'];
                
            }
        }
        unset( $aPaymentCashFeeIn );
        /* louis */

        // 13, 获取总代及下级在线提现手续费金额
        $aPaymentCashFeeOut = $this->getTopProxyCashPayment( $tBeginTime, $tEndTime, 'feeout', $sCondition );
        foreach( $aPaymentCashFeeOut AS $v )
        {
            if( !empty($v['lvtopid']) )
            {
                $aReturn[ $v['lvtopid'] ]['cashpaymentfeeout'] += $v['TOMSUM'];
                $aReturn[ $v['lvtopid'] ]['cashout'] += $v['TOMSUM'];
            }
        }
        unset( $aPaymentCashFeeOut );
        
        return $aReturn;
    }


    
	/**
     * 获取总代(本人+下级) 人工操作的资金
     * 
     * @param datetime $tBeginTime
     * @param datetime $tEndTime
     * @param string   $sAction
     * @return array
     * jim  5/5/2010
     */
    public function & getTopProxyHandCash( $tBeginTime = 0, $tEndTime = 0, $sAction = 'handin', $sCondition = '' )
    {
    	// 要求只包括涉及人工操作的进出，且不包含用户间的资金来往
    	if( $sAction == 'handin' )
        {
            $aArray = array(
                ORDER_TYPE_SJCZ,   // 上级充值
                ORDER_TYPE_KJCZ,   // 跨级充值 (特殊理赔)
                ORDER_TYPE_XEJS,   // 小额接收
                ORDER_TYPE_TSJEZL, // 特殊金额整理
//                ORDER_TYPE_ZXCZ,	// 在线充值
            );
        }
        
        /* louis */
        // 要求只包括涉及人工操作的进出，且不包含用户间的资金来往 for email deposit
    	if( $sAction == 'emailhandin' )
        {
            $aArray = array(
                ORDER_TYPE_RGCZ,   // 人工充值 for email deposit
            );
        }
        /* louis */
        
        if( $sAction == 'handout' )
        {
            $aArray = array(
            	ORDER_TYPE_PTTXCG,	//平台提现成功
                ORDER_TYPE_BRTX,    // 本人提现
                ORDER_TYPE_SWTXCG,  // 商务提现成功 (大额提现)
                ORDER_TYPE_KJTX,    // 跨级提现
                ORDER_TYPE_XEKC,    // 小额扣除
                ORDER_TYPE_TSJEQL,  // 特殊金额清理
//                ORDER_TYPE_ZXTXKK,	// 在线提现扣款
            );
        }
        $sWhere = '';
        $sSql   = '';
        if( $tBeginTime!=0 )
        {
            $sWhere .= " AND `times` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime!=0 )
        {
            $sWhere .= " AND `times` <= '".daddslashes($tEndTime)."' ";
        }
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sWhere = " AND `times` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }
        $sOrderTypeStr  = '';
        $sOrderTypeStr  = join( ',', $aArray );
        $sWhere        .= $sCondition;
        $sSql = "SELECT ut.lvtopid, SUM(`amount`) AS TOMSUM FROM `usertree` ut LEFT JOIN `orders` o FORCE INDEX (idx_search) ".
                " ON ( o.`fromuserid` = ut.`userid` ) WHERE `adminid`!=0 AND `ordertypeid` IN ( $sOrderTypeStr ) ".
                $sWhere . " GROUP BY ut.`lvtopid`";
        return $this->oDB->getAll( $sSql );
        
    }
    

    /**
     * 获取总代(本人+下级) 理赔资金
     *    ordertypeid=23    理赔充值     ORDER_TYPE_LPCZ
     *    ordertypeid=24    管理员扣减   ORDER_TYPE_GLYKJ
     *    并且由公司管理员操作 adminid != 0
     *
     * @param datetime $tBeginTime
     * @param datetime $tEndTime
     * @param string   $sAction
     * @return array
     * @author Tom 090608
     */
    public function & getTopProxyLpCash( $tBeginTime = 0, $tEndTime = 0, $sAction = 'in', $sCondition = '' )
    {
        if( $sAction == 'in' )
        { // 资金流入 - 账变类型数组
            $aArray = array(
                ORDER_TYPE_LPCZ, // 理赔充值
            );
        }
        else
        { // 资金流出 - 账变类型数组
             $aArray = array(
                ORDER_TYPE_GLYKJ,  // 管理员扣减
            );
        }
        $sWhere = '';
        $sSql   = '';
        if( $tBeginTime!=0 )
        {
            $sWhere .= " AND `times` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime!=0 )
        {
            $sWhere .= " AND `times` <= '".daddslashes($tEndTime)."' ";
        }
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sWhere = " AND `times` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }
        $sOrderTypeStr  = '';
        $sOrderTypeStr  = join( ',', $aArray );
        $sWhere        .= $sCondition;
        $sSql = "SELECT ut.lvtopid, SUM(`amount`) AS TOMSUM FROM `usertree` ut LEFT JOIN `orders` o FORCE INDEX (idx_search) ".
                " ON ( o.`fromuserid` = ut.`userid` ) WHERE `adminid`!=0 AND `ordertypeid` IN ( $sOrderTypeStr ) ".
                $sWhere . " GROUP BY ut.`lvtopid`";
        return $this->oDB->getAll( $sSql );
    }




    /**
     * 获取总代(不含本人,所有下级)的:  资金流入|流出
     *    并且由公司管理员操作 adminid != 0
     *
     * @param datetime $tBeginTime
     * @param datetime $tEndTime
     * @param string   $sAction
     * @param string   $sCondition
     * @return array
     * @author Tom
     */
    public function & getTopProxyChildCash( $tBeginTime = 0, $tEndTime = 0, $sAction = 'in', $sCondition = '' )
    {
        if( $sAction == 'in' )
        { // 资金流入 - 账变类型数组
            $aArray = array(
                ORDER_TYPE_KJCZ,   // 跨级充值 (特殊理赔)
                ORDER_TYPE_XEJS,   // 小额接收
                ORDER_TYPE_TSJEZL, // 特殊金额整理
                ORDER_TYPE_LPCZ,   // 理赔充值
                /* louis */
                ORDER_TYPE_RGCZ,   // 人工充值 for email deposit
                /* louis */
            );
        }
        if( $sAction == 'out' )
        { // 资金流出 - 账变类型数组
             $aArray = array( 
                ORDER_TYPE_PTTXCG,  // 平台提现成功
                ORDER_TYPE_XEKC,    // 小额扣除
                ORDER_TYPE_TSJEQL,  // 特殊金额清理
                ORDER_TYPE_KJTX,    // 跨级提现
                ORDER_TYPE_GLYKJ,   // 管理员扣减
            );
        }
        $sWhere = '';
        $sSql   = '';
        if( $tBeginTime != 0 )
        {
            $sWhere .= " AND `times` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime != 0 )
        {
            $sWhere .= " AND `times` <= '".daddslashes($tEndTime)."' ";
        }
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sWhere = " AND `times` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }
        $sOrderTypeStr  = '';
        $sOrderTypeStr  = join( ',', $aArray );
        $sWhere        .= $sCondition;
        $sSql = "SELECT ut.lvtopid, SUM(`amount`) AS TOMSUM FROM `usertree` ut LEFT JOIN `orders` o FORCE INDEX (idx_search) ".
                " ON ( o.`fromuserid` = ut.`userid` ) WHERE ut.`parentid`!=0 AND `adminid`!=0 AND `ordertypeid` IN ( $sOrderTypeStr ) ".
                $sWhere . " GROUP BY ut.`lvtopid`";
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 获取总代(本人)的:   资金流入|资金流出|信用充值|信用扣减
     * 1, 流入账变类型:  
     * 2, 流出账变类型: 
     * 3, 管理员ID != 0
     *  
     * @param datetime   $tBeginTime
     * @param datetime   $tEndTime
     * @param string     $sAction   in|out|creditin|creditout|allin|allout
     * @param string     $sCondition
     * @return array
     * @author Tom
     */
    public function & getTopProxyCash( $tBeginTime = 0, $tEndTime = 0, $sAction = 'in', $sCondition = '' )
    {
        if( $sAction == 'allin' )
        {
            $aArray = array(
                ORDER_TYPE_SJCZ,   // 上级充值
                ORDER_TYPE_KJCZ,   // 跨级充值 (特殊理赔)
                ORDER_TYPE_XEJS,   // 小额接收
                ORDER_TYPE_TSJEZL, // 特殊金额整理
                ORDER_TYPE_LPCZ,   // 理赔充值
                /* louis */
                ORDER_TYPE_RGCZ,   // 人工充值 for email deposit
                /* louis */
            );
    
        }
        if( $sAction == 'allout' )
        {
            $aArray = array(
                ORDER_TYPE_BRTX,    // 本人提现
                ORDER_TYPE_SWTXCG,  // 商务提现成功 (大额提现)
                ORDER_TYPE_KJTX,    // 跨级提现
                ORDER_TYPE_XEKC,    // 小额扣除
                ORDER_TYPE_TSJEQL,  // 特殊金额清理
                ORDER_TYPE_GLYKJ,   // 管理员扣减
            );
        }
    	
        if( $sAction == 'in' )
        { // 资金流入 - 账变类型数组
            $aArray = array(
                ORDER_TYPE_SJCZ, // 上级充值
                ORDER_TYPE_KJCZ, // 跨级充值
            );
        }
        if( $sAction == 'out' )
        { // 资金流出 - 账变类型数组
             $aArray = array(
                ORDER_TYPE_BRTX,    // 本人提现
                ORDER_TYPE_SWTXCG,  // 平台提现成功 (大额提现)
            );
        }
        if( $sAction == 'creditin' )
        {
             $aArray = array(
                ORDER_TYPE_XYCZ,  // 信用充值
            );
        }
        if( $sAction == 'creditout' )
        {
             $aArray = array(
                ORDER_TYPE_XYKJ,  // 信用扣减
            );
        }
        $sWhere = '';
        $sSql   = '';
        if( $tBeginTime!=0 )
        {
            $sWhere .= " AND `times` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime!=0 )
        {
            $sWhere .= " AND `times` <= '".daddslashes($tEndTime)."' ";
        }
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sWhere = " AND `times` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }
        $sOrderTypeStr  = '';
        $sOrderTypeStr  = join( ',', $aArray );
        $sWhere        .= $sCondition;
        $sSql = "SELECT `fromuserid`, SUM(`amount`) AS TOMSUM FROM `usertree` ut LEFT JOIN `orders` o FORCE INDEX (idx_search) ".
                " ON ( o.`fromuserid` = ut.`userid` AND ut.`parentid`=0 ) WHERE `adminid`!=0 AND `ordertypeid` IN ( $sOrderTypeStr ) ".
                $sWhere . " GROUP BY `fromuserid`";
        return $this->oDB->getAll( $sSql );
    }

    
 /**
     * 获取总代(本人)的:   在线充值｜在线提现｜在线充值手续费|在线提现手续费
     *  
     * @param datetime   $tBeginTime
     * @param datetime   $tEndTime
     * @param string     $sAction   in|out|feein|feeout
     * @param string     $sCondition
     * @return array
     * @date 4/14/2010 add
     *  
     */
    public function & getTopProxyCashPayment( $tBeginTime = 0, $tEndTime = 0, $sAction = 'in', $sCondition = '' )
    {
        if( $sAction == 'feein' )
        {
            $aArray = array(
                ORDER_TYPE_ZXCZSF,   // 在线充值手续费
            );
        }
        /* louis */
        if( $sAction == 'emailandhandfeein' )
        {
            $aArray = array(
                ORDER_TYPE_SXFFH,   // email充值和人工充值手续费返还
            );
        }
        /* louis */
        if( $sAction == 'feeout' )
        {
            $aArray = array(
                ORDER_TYPE_ZXTXSF,    // 在线提现手续费
            );
        }
        if( $sAction == 'in' )
        { 
            $aArray = array(
                ORDER_TYPE_ZXCZ,   // 在线充值
            );
        }
        if( $sAction == 'out' )
        { 
             $aArray = array(
                ORDER_TYPE_ZXTXKK,    // 在线提现扣款
            );
        }
       
        $sWhere = '';
        $sSql   = '';
        
        if( $tBeginTime!=0 )
        {
            $sWhere .= " AND `times` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime!=0 )
        {
            $sWhere .= " AND `times` <= '".daddslashes($tEndTime)."' ";
        }
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { 
            $sWhere = " AND `times` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }
        $sOrderTypeStr  = '';
        $sOrderTypeStr  = join( ',', $aArray );
        $sWhere        .= $sCondition;
        $sSql = "SELECT ut.lvtopid, SUM(`amount`) AS TOMSUM FROM `usertree` ut LEFT JOIN `orders` o FORCE INDEX (idx_search) ".
                " ON ( o.`fromuserid` = ut.`userid` ) WHERE `ordertypeid` IN ( $sOrderTypeStr ) ".
                $sWhere . " GROUP BY ut.`lvtopid`";
 
        return $this->oDB->getAll($sSql);
        
    }


    /**
     * 根据用户查看自己所有下级经过自己的资金流入流出统计结果[前台，暂时为只有总代可以查看]
     * 
     * @access  public 
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   string  $sBeginTime //开始时间
     * @param   string  $sEndTime   //结束时间
     * @param   string  $sAndWhere  //附加搜索条件
     * @return  array   统计结果 $aResult=>$aResult['count']综合统计,$aResult['result']根据直接下级统计
     */
    public function & getCashCountByUser( $iUserId, $sBeginTime = 0, $sEndTime = 0, $sAndWhere = '' )
    {
        $aResult = array( 
                            'count'  => array( 'incount'=>0, 'outcount'=>0, 'left'=>0 ),
                            'result' => array() 
                         );
        $iUserId = intval($iUserId);
        if( $iUserId <= 0 )
        {
            return $aResult;
        }
        $aCashIn    = $this->getCashInOutByUser( $iUserId, $sBeginTime, $sEndTime, 'out' ); //相对下级为进入
        $aCashOut   = $this->getCashInOutByUser( $iUserId, $sBeginTime, $sEndTime, 'in' );	//相对下级为流出
        //获取所有直接下级
        $sSql = " SELECT `userid`,`username` FROM `usertree` WHERE `isdeleted`='0' 
                    AND `usertype`<'2' AND `parentid`='".$iUserId."' ".$sAndWhere;
        $this->oDB->query( $sSql );
        $aTemp_result   = array();
        $iTemp_in       = 0;
        $iTemp_out      = 0;
        while( FALSE != ($aTemp_result=$this->oDB->fetchArray()) )
        {
            $iTemp_in = isset( $aCashIn[$aTemp_result['userid']] ) ? 
                                                    floatval($aCashIn[$aTemp_result['userid']]['JAMESCOUNT']) : 0;
            $iTemp_out = isset( $aCashOut[$aTemp_result['userid']] ) ? 
                                                    floatval($aCashOut[$aTemp_result['userid']]['JAMESCOUNT']) : 0;
            $aResult['result'][] = array(
                                'userid'    => $aTemp_result['userid'],
                                'username'  => $aTemp_result['username'],
                                'cashin'    => $iTemp_in,
                                'cashout'   => $iTemp_out,
                                'left'      => $iTemp_in - $iTemp_out,
                            );
            $aResult['count']['incount'] += $iTemp_in;
            $aResult['count']['outcount'] += $iTemp_out;
            $aResult['count']['left'] += ($iTemp_in - $iTemp_out);
        }
        unset( $aTemp_result, $iTemp_in, $iTemp_out );
        return $aResult;
    }



    /**
     * 根据用户ID统计经过自己的对下级操作的资金流出情况[不包括自己的|CLI]
     *
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   string  $sBeginTime //开始时间
     * @param   string  $sEndTime   //结束时间
     * @param   string  $sAction    //in|out in表示流入部分,out表示流出部分
     * @return  array   统计结果
     */
    public function & getCashInOutByUser( $iUserId, $sBeginTime = 0, $sEndTime = 0, $sAction = 'in' )
    {
        $aResult = array();
        $iUserId = intval($iUserId);
        if( $iUserId <= 0 )
        {
            return $aResult;
        }
        $aArray = array();
        if( $sAction == 'out' )
        {//流出统计
            $aArray = array(
                ORDER_TYPE_CZKF,    //充值扣费
            );
        }
        elseif( $sAction == 'in' )
        {
            $aArray = array(
                ORDER_TYPE_BRTX,    //本人提现
                ORDER_TYPE_XJFQTX,  //下级发起提现
                ORDER_TYPE_XJTX,    //下级提现
                ORDER_TYPE_ZDTXCG   //平台提现
            );
        }
        else
        {
            return $aResult;
        }
        $sCondition = " AND o.`fromuserid`='".$iUserId."' ";
        if( count($aArray) == 1 )
        {//流出统计
            $aArray = implode( ',', $aArray );
            $sCondition .= " AND o.`ordertypeid`='".$aArray."' ";
        }
        else
        {
            $aArray = implode( ',', $aArray );
            $sCondition .= " AND o.`ordertypeid` IN(".$aArray.") ";
        }
        if( $sBeginTime!=0 && $sEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sCondition .= " AND o.`times` BETWEEN '".daddslashes($sBeginTime)."' AND '".daddslashes($sEndTime)."' ";
        }
        elseif( $sBeginTime != 0 )
        {
            $sCondition .= " AND o.`times` >= '".daddslashes($sBeginTime)."' ";
        }
        elseif( $sEndTime != 0 )
        {
            $sCondition .= " AND o.`times` <= '".daddslashes($sEndTime)."' ";
        }
        $sCondition .= " GROUP BY ut.`lvproxyid` ";
        $sSql = "SELECT SUM(o.`amount`) AS JAMESCOUNT,ut.`username`,ut.`userid`,ut.`lvproxyid` FROM `usertree` AS ut
                 LEFT JOIN `orders` AS o ON ut.`userid`=o.`touserid` WHERE ut.`isdeleted`='0' ".$sCondition;
        $this->oDB->query( $sSql );
        $aTemp_arr = array();
        while( FALSE != ($aTemp_arr=$this->oDB->fetchArray()) )
        {
            $aResult[$aTemp_arr['lvproxyid']] = $aTemp_arr;
        }
        unset( $aTemp_arr );
        return $aResult;
    }



    /**
     * 获取总代 '频道理赔' 结果集
     * @param datetime   $tBeginTime
     * @param datetime   $tEndTime
     * @param string     $sCondition    // 公司销售管理员,对应总代的SQL条件 in( 1,2,3) | 非销售管理员则为空
     * @return array     $aReturn
     * @author Tom 090608
     */
    public function & getTopProxyAmends( $tBeginTime=0, $tEndTime=0, $sCondition='' )
    {
        $aReturn = array();
        $sCondition = daddslashes($sCondition);
        // 01, 初始化总代结果集数组
        $aProxy = $this->oDB->getAll("SELECT `userid`,`username`,`usertype` FROM `usertree` WHERE `isdeleted` = 0 AND `parentid`=0 $sCondition ");
        foreach( $aProxy AS $v )
        {
            $aReturn[ $v['userid'] ]['username'] = $v['username'];
            $aReturn[ $v['userid'] ]['usertype'] = $v['usertype'];
            $aReturn[ $v['userid'] ]['channel']  = array();
            $aReturn[ $v['userid'] ]['cash']     = 0; // 某个总代, 所有频道的理赔金额
            // for example:  array = $v[34][channel][0] = 34.00 元
            // channelid = 0  表示为银行频道的理赔
        }

        if( $tBeginTime!=0 )
        {
            $sCondition .= " AND `times` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime!=0 )
        {
            $sCondition .= " AND `times` <= '".daddslashes($tEndTime)."' ";
        }
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sCondition = " AND `times` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }
        // 02, 获取总代本人的资金流入(充值金额) DONE
        $aResult = array();
        $aResult = $this->oDB->getAll(
                "SELECT ut.`lvtopid`, o.`channelid`, SUM(`amount`) AS TOMSUM FROM `usertree` ut ". 
                " LEFT JOIN `orders` o FORCE INDEX (idx_search) ON ( o.`fromuserid` = ut.`userid` ) ". 
                " WHERE `adminid`!=0 AND `ordertypeid`=". ORDER_TYPE_LPCZ .
                " $sCondition GROUP BY ut.`lvtopid`, o.channelid ");
        if( 0 == $this->oDB->ar() )
        {
            $aResult = array();
            return $aResult;
        }

        foreach( $aResult AS $v )
        {
            if( !isset($aReturn[$v['lvtopid']]) )
            {
                continue;//过滤用户
            }
            if( !isset($aReturn[ $v['lvtopid'] ]['channel'][ $v['channelid'] ]) )
            {
                $aReturn[ $v['lvtopid'] ]['channel'][ $v['channelid'] ] = 0;
            }
            $aReturn[ $v['lvtopid'] ]['channel'][ $v['channelid'] ] += $v['TOMSUM'];
            $aReturn[ $v['lvtopid'] ]['cash'] += $v['TOMSUM'];
        }
        return $aReturn;
    }



    /**
     * 获取总代(本人+下级) 频道转账结果集
     *
     * @param datetime $tBeginTime
     * @param datetime $tEndTime
     * @param string   $sAction
     * @return array
     * @author Tom
     */
    public function & getTopProxyTransitionResult( $tBeginTime=0, $tEndTime=0, $sAction='in', $sCondition='' )
    {
        if( $sAction == 'in' )
        {
            $aArray = array(
                ORDER_TYPE_ZRYH, // 转入银行
            );
        }
        if( $sAction == 'out' )
        {
             $aArray = array(
                ORDER_TYPE_YHZC,  // 银行转出
            );
        }
        $sWhere = '';
        $sSql   = '';
        if( $tBeginTime!=0 )
        {
            $sWhere .= " AND `times` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime!=0 )
        {
            $sWhere .= " AND `times` <= '".daddslashes($tEndTime)."' ";
        }
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sWhere = " AND `times` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }
        $sOrderTypeStr  = '';
        $sOrderTypeStr  = join( ',', $aArray );
        $sWhere        .= $sCondition;
        $sSql = "SELECT ut.lvtopid, SUM(`amount`) AS TOMSUM FROM `usertree` ut LEFT JOIN `orders` o FORCE INDEX (idx_search) ".
                " ON ( o.`fromuserid` = ut.`userid` ) WHERE `ordertypeid` IN ( $sOrderTypeStr ) ".
                $sWhere . " GROUP BY ut.`lvtopid`";
        return $this->oDB->getAll($sSql);
    }


    /**
     * 获取总代频道转账结果集
     * @param datetime   $tBeginTime
     * @param datetime   $tEndTime
     * @param string     $sCondition  // 转账的状态(成功失败)
     *                                 AND 公司销售管理员,对应总代的SQL条件 in( 1,2,3) | 非销售管理员则为空
     * @return array     $aReturn
     * @author Tom 090814
     */
    public function & getTopProxyTransition( $tBeginTime=0, $tEndTime=0, $sCondition='', $sTranStatusSql='' )
    {
        $aReturn = array();
        $sCondition = daddslashes($sCondition);
        $sTranStatusSql = daddslashes($sTranStatusSql);
        // 01, 获取枚举的频道数组  0->1 0->2 1->0 2->0
        $oChannel = new model_channels();
        $aChannels = $oChannel->channelGetAll( ' `id`,`channel` ', ' `pid`=0 ' );
        $aChannel = array();
        foreach( $aChannels AS $v )
        {
            $aChannel[] = $v['id'];
        }

        // TODO _a高频、低频并行前期临时程序
        //print_rr($aChannel);exit;
//        $aChannel[] = 99;
        // 临时代码结束

        unset($oChannel,$aChannels);
        $iCountChannel = count($aChannel);
        //print_rr($aChannel);exit;

        // 02, 初始化总代结果集数组
        /**
         * 总代ID => array(
         *  (
         *       [username] => zdkent
         *       [usertype] => 1
         *       [0_8] => 0      银行  => 频道1
         *       [8_0] => 0      频道1 => 银行
         *       [0_1] => 0      银行  => 频道2
         *       [1_0] => 0      频道2 => 银行
         *       [0_9] => 0      银行  => 频道3
         *       [9_0] => 0      频道3 => 银行
         *  );
         */
        $aProxy = $this->oDB->getAll("SELECT `userid`,`username`,`usertype` FROM `usertree` WHERE `isdeleted` = 0 AND `parentid`=0 $sCondition");
        foreach( $aProxy AS $v )
        {
            $aReturn[ $v['userid'] ]['username'] = $v['username'];
            $aReturn[ $v['userid'] ]['usertype'] = $v['usertype'];
            $aReturn[ $v['userid'] ]['total']    = 0.00; // 转账结余
            // 初始化转账频道组合  各频道=>银行 1_0, 2_0,  银行至各频道 0_1, 0_2
            $aReturn[ $v['userid'] ]['channel']  = array();
            for( $i=0; $i<$iCountChannel;$i++ )
            {
                $aReturn[ $v['userid'] ]['channel']['0_' . $aChannel[$i] ] = 0.00;
                $aReturn[ $v['userid'] ]['channel'][$aChannel[$i] . '_0' ] = 0.00;
            }
        }
        //print_rr($aReturn);exit;

        // 03, 整理 SQL 条件
        if( $tBeginTime!=0 )
        {
            $sCondition .= " AND `actiontime` >= '".daddslashes($tBeginTime)."' ";
        }
        if( $tEndTime!=0 )
        {
            $sCondition .= " AND `actiontime` <= '".daddslashes($tEndTime)."' ";
        }
        if( $tBeginTime!=0 && $tEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sCondition = " AND `times` BETWEEN '".daddslashes($tBeginTime)."' AND '".daddslashes($tEndTime)."' ";
        }

        // 04.a 获取平台资金转入, 账变类型为: ORDER_TYPE_ZRYH  (转入银行)
        $aResultIn = array(); 
        $aResultIn = $this->oDB->getAll(
                "SELECT ut.`lvtopid`, o.`transferchannelid`, SUM(`amount`) AS TOMSUM FROM `usertree` ut ". 
                " LEFT JOIN `orders` o FORCE INDEX (idx_search) ON ( o.`fromuserid` = ut.`userid` ) ". 
                " WHERE ut.`isdeleted` = 0 AND o.`ordertypeid`=". ORDER_TYPE_ZRYH .
                " $sCondition $sTranStatusSql GROUP BY ut.`lvtopid`, o.transferchannelid ");

        // 04.b 获取平台资金转出, 账变类型为: ORDER_TYPE_YHZC  (银行转出)
        $aResultOut = array(); 
        $aResultOut = $this->oDB->getAll(
                "SELECT ut.`lvtopid`, o.`transferchannelid`, SUM(`amount`) AS TOMSUM FROM `usertree` ut ". 
                " LEFT JOIN `orders` o FORCE INDEX (idx_search) ON ( o.`fromuserid` = ut.`userid` ) ". 
                " WHERE ut.`isdeleted` = 0 AND o.`ordertypeid`=". ORDER_TYPE_YHZC .
                " $sCondition $sTranStatusSql GROUP BY ut.`lvtopid`, o.transferchannelid ");

        if( !empty( $aResultIn ) )
        {
           foreach( $aResultIn AS $v )
           {
               if( !isset($aReturn[$v['lvtopid']]) )
               {
                   continue;//过滤用户
               }
               if( in_array($v['transferchannelid'], $aChannel) )
               {
                   $aReturn[ $v['lvtopid'] ]['channel'][ $v['transferchannelid'] . '_0' ] = $v['TOMSUM'];
               }
           }
        }
        
        if( !empty( $aResultOut ) )
        {
           foreach( $aResultOut AS $v )
           {
               if( !isset($aReturn[$v['lvtopid']]) )
               {
                   continue;//过滤用户
               }
               if( in_array($v['transferchannelid'], $aChannel) )
               {
                   $aReturn[ $v['lvtopid'] ]['channel'][ '0_' . $v['transferchannelid']] = $v['TOMSUM'];
               }
           }
        }

        // 计算转账结余
        //print_rr($aReturn);exit;
        foreach( $aReturn AS $k => &$v )
        {
            foreach( $aChannel AS $iChannelId )
            { // 计算 转账结余 = 所有频道转入银行 - 银行转出所有频道
                $v['total'] += ( $v['channel'][ $iChannelId . '_0' ] - $v['channel']['0_' . $iChannelId ] );
            }
        }
        return $aReturn;
    }






    /**
     *	根据总代ID统计自身的资金流入流出统计
     */
    public function & getCashInOutByTopUser( $iUserId, $sBeginTime=0, $sEndTime=0, $sAction='in' )
    {
        $aResult = array();
        $aArray = array();
        if( $sAction == 'in' )
        {//充值
            $aArray = array(
                ORDER_TYPE_SJCZ,    // 上级充值
                ORDER_TYPE_XEJS,    // 小额接收
                ORDER_TYPE_LPCZ,    // 理赔充值
                ORDER_TYPE_TSJEZL,  // 特殊金额整理
                ORDER_TYPE_ZXCZ,	// 在线充值
            );
        }
        elseif( $sAction == 'out' )
        {
            $aArray = array(
                ORDER_TYPE_XJTX,    // 下级提现
                ORDER_TYPE_SWTXCG,  // 商务提现成功
                ORDER_TYPE_GLYKJ,   // 管理员扣减
                ORDER_TYPE_XEKC,    // 小额扣除
                ORDER_TYPE_TSJEQL,  // 特殊金额清理
                ORDER_TYPE_GLYKJ,   // 管理员扣减
                ORDER_TYPE_ZXTXKK,	// 在线提现扣款
            );
        }
        else
        {
            return $aResult;
        }
        $iUserId = intval($iUserId);
        if( $iUserId > 0 )
        {
            $sCondition = " AND o.`fromuserid`='".$iUserId."' ";
            $sGroupBy = "";
        }
        else 
        {
            $sCondition = " AND ut.`parentid`='0' ";
            $sGroupBy   = " GROUP BY ut.`userid` ";
        }
        if( count($aArray) == 1 )
        {//流出统计
            $aArray = implode( ',', $aArray );
            $sCondition .= " AND o.`ordertypeid`='".$aArray."' ";
        }
        else
        {
            $aArray = implode( ',', $aArray );
            $sCondition .= " AND o.`ordertypeid` IN (".$aArray.") ";
        }
        if( $sBeginTime!=0 && $sEndTime!=0 )
        { // 如果同时存在2个时间, 则使用 BETWEEN (效率更高些)
            $sCondition .= " AND o.`times` BETWEEN '".daddslashes($sBeginTime)."' AND '".daddslashes($sEndTime)."' ";
        }
        elseif( $sBeginTime!=0 )
        {
            $sCondition .= " AND o.`times` >= '".daddslashes($sBeginTime)."' ";
        }
        elseif( $sEndTime!=0 )
        {
            $sCondition .= " AND o.`times` <= '".daddslashes($sEndTime)."' ";
        }
        $sCondition .= $sGroupBy;
        $sSql = "SELECT SUM(o.`amount`) AS JAMESCOUNT,ut.`username`,ut.`userid` FROM `usertree` AS ut
                 LEFT JOIN `orders` AS o ON ut.`userid`=o.`fromuserid` WHERE ut.`isdeleted`='0' ".$sCondition;
        $this->oDB->query( $sSql );
        $aTemp_arr = array();
        while( FALSE != ($aTemp_arr=$this->oDB->fetchArray()) )
        {
            $aResult[$aTemp_arr['userid']] = $aTemp_arr;
        }
        unset( $aTemp_arr );
        return $aResult;
    }


    /**
     * 清除日志
     *
     * @param int $day
     * @return unknown
     * @author Saul 090604
     */
    function clearLog( $iDay )
    {
        if( !is_numeric($iDay) )
        {
            return FALSE;
        }
        return $this->oDB->query("DELETE FROM `orders` WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay."days"))."'");
    }



    /**
     * 备份日志(分页机制)
     *
     * @param int $iDay
     * @param string $sFile
     * @author SAUL
     */
    function bakLog( $iDay, $sFile )
    {
        if( !is_numeric($iDay) ) 
        {
            return FALSE;
        }
        $sSql    = "SELECT COUNT(*) AS `count_orders` FROM `orders` 
                    WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay."days"))."'";
        $aNumLog = $this->oDB->getOne( $sSql );
        $iNum    = $aNumLog['count_orders'];
        $iSize   = 50000;
        $iPages  = ceil( $iNum/$iSize );
        $oGzopen = gzopen( $sFile, 'w9' );
        for( $page = 0 ; $page < $iPages; $page++ )
        {
            $sFileContent = "";
            $sSql         = "SELECT * FROM `orders` 
                             WHERE `times`<'".date("Y-m-d 00:00:00", strtotime("-".$iDay."days"))."' 
                             LIMIT " .($page * $iSize). ",".$iSize;
            $aLogs = $this->oDB->getAll( $sSql );
            foreach( $aLogs as $log )
            {
                $aKeys   = array();
                $aValues = array();
                foreach( $log as $key => $value )
                {
                    $aKeys[] = "`".$key."`";
                    if( is_null($value) )
                    {
                        $aValues[] = 'NULL';
                    }
                    else 
                    {
                        $aValues[] = "'".$this->oDB->es($value)."'";
                    }
                }
                $sSql = "INSERT INTO `orders` (".join(",", $aKeys).") VALUES (".join(",", $aValues).");";
                $sFileContent .= $sSql."\n";
            }
            gzwrite( $oGzopen, $sFileContent );
        }
        gzclose($oGzopen);
        unset($sFileContent);
        $this->clearLog( $iDay );
        return TRUE;
    }



    /**
     * 根据账变ID, 更新 '转账账变记录' 的状态值
     * @author  Tom 
     * @param   int     $iOrderEntry  账变ID编号, 对应 orders.entry
     * @param   string  $sUniqueKey   转账唯一值
     * @param   int     $iRelationOrderEntry  关联转账的 ORDERS.ENTRY
     * @param   int     $iFlag        帐变状态,1:请求;2:成功;3:失败
     * @param   int     $iAdminId     管理员ID
     * @param   string  $sAdminName   管理员名字
     * @return  BOOL
     */
    public function UpdateApiTranferStatus( $iOrderEntry=0, $sUniqueKey='', $iRelationOrderEntry=0, $iFlag=2, $iAdminId=0, $sAdminName='' ) 
    {
        $iOrderEntry                = intval($iOrderEntry);
        $sUniqueKey                 = daddslashes( $sUniqueKey );
        $aUpdate['transferorderid'] = intval($iRelationOrderEntry);
        $aUpdate['transferstatus']  = intval($iFlag);
        $aUpdate['adminid']         = intval($iAdminId);
        $aUpdate['adminname']       = daddslashes($sAdminName);
        $sWhere = " `entry`='$iOrderEntry' AND `uniquekey`='$sUniqueKey' AND `transferstatus`!=2 ORDER BY `times` DESC LIMIT 1 ";
        if( FALSE === $this->oDB->update( 'orders', $aUpdate, $sWhere ) )
        {
            return FALSE;
        }
        else 
        {
            return TRUE;
        }
    }


    /**
     * 检查账变异常
     * 返回账变异常数据
     * @access public
     * @param  array     $aCondition     查询条件
     * @param  int       $iPageRecord    数据记录数
     * @param  int       $iCurrentPage   当前页
     * @return array()
     * 
     * @author mark
     */
    public function getErrorOrder( $aCondition = array(), $iPageRecord = 0, $iCurrentPage = 0 )
    {
        $iOrderTypeOne      = ORDER_TYPE_YHZC;
        $iOrderTypeTwo      = ORDER_TYPE_ZRYH;
        $iOrderTypeThree    = ORDER_TYPE_PDXEZR;
        $iOrderTypeId       = intval( $aCondition['ordertypeid'] );
        $iUserId            = intval( $aCondition['userid'] );
        $sStarttime         = $aCondition['starttime'];
        $sEndtime           = $aCondition['endtime'];
        $iPageRecord        = intval( $iPageRecord );
        $iCurrentPage       = intval( $iCurrentPage );
        $sWhere = " 1 ";
        if( $iUserId )
        {
            $sWhere  .= " AND o.`fromuserid` = '$iUserId' ";
        }
        if( $iOrderTypeId )
        {
            $sWhere  .= " AND o.`ordertypeid` = '$iOrderTypeId' ";
        }
        else
        {
            $sWhere .= " AND ( o.`ordertypeid` = '$iOrderTypeOne' OR o.`ordertypeid` = '$iOrderTypeTwo'
                         OR o.`ordertypeid` = '$iOrderTypeThree' ) ";
        }
        if( $sStarttime )
        {
            $sWhere  .= " AND o.`times` >= '$sStarttime' ";
        }
        if( $sEndtime )
        {
            $sWhere  .= " AND o.`times` <= '$sEndtime' ";
        }
        $sTable = " `orders` AS o LEFT JOIN `channels` AS c1 ON ( o.`channelid` = c1.`id`) 
                    LEFT JOIN `channels` AS c2 ON ( o.`transferchannelid` = c2.`id`)
                    LEFT JOIN `usertree` AS ut ON ( o.`fromuserid` = ut.`userid`) ";
        $sFields = " o.*,c1.`channel` AS channelname,c2.`channel` AS transferchannelname,ut.`username` AS username ";
        $sCondition = " $sWhere AND o.`transferstatus` != 2 ";
        $sOrderBy = " ORDER BY o.`times` DESC ";
        if( $iPageRecord == 0 )
        {
            return $this->oDB->getAll( "SELECT $sFields FROM $sTable WHERE $sCondition" );
        }
        else
        {
            return $this->oDB->getPageResult( $sTable, $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy );
        }
    }
    
    /**
     * 获取指定条件账变记录
     *
     * @param string   $sFiled      查询字段
     * @param string   $sCondition  查询条件
     * @param boolean  $bGetOne     是否是获取一条记录
     * 
     * @return array
     */
    public function getOrderList( $sFiled = '*', $sCondition = '1', $bGetOne = TRUE )
    {
        if( $bGetOne )
        {
            return $this->oDB->getOne( " SELECT $sFiled FROM `orders` WHERE $sCondition " );
        }
        else
        {
            return $this->oDB->getAll( " SELECT $sFiled FROM `orders` WHERE $sCondition " );
        }
        
    }
}
?>