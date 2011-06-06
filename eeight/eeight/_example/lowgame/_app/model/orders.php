<?php
/**
 * 文件 : /_app/model/orders.php
 * 功能 : 数据模型 - 用户帐变
 * 
 * - addOrders              对用户账户资金额进行修改, 同时写入账变
 * - UpdateApiTranferStatus 根据账变ID, 更新 '转账账变记录' 的状态值
 * - getAdminOrderList      查看帐变，可以自定义查询条件[带分页效果][后台调用]
 * - getOrderType           查询所有的帐变类型
 * - getAdminOrderStat      账变列表的最后一页,进行数据总体结算
 * - orderEnCode            简单加密解密处理
 * - getTopProxyTransition  获取总代频道转账结果集
 * - getGameWinUserCount    中奖用户个数查询
 * - getGameMoneytotal      游戏收入总额(FOR CLI，不计算返点)
 * - getGameWinMoneytotal   游戏奖金总额(FOR CLI)
 * - getGameMoneyOut        频道转出总额(FOR CLI)
 * - getGameMoneyIn         转入频道总额(FOR CLI)
 * - getOrdersTotal         更新帐变个数
 * - clearLog               清除帐变
 * - bakLog                 备份帐变
 * - getFundTotalByUserId   获取用户的的资金总额，用户的频道全览
 * - userOrderList          用户帐变查询
 * - getProxyTotalPoint     获取指定代理自己及所有下级返点总额
 * - getTotalBonusAndPrize  获取报表的成本和奖金以及返点总和(结果集已经整理,通过 UDP来计算的)
 * - getUserBonusAndPrize   查询指定用户的因为自身获取到的相关的返点和销售总额
 * - getTotalUserBonusByMethod 代理查看一个用户的游戏详情时候调用
 * - getTopProxyData        获取总代(本人+下级) 的数据集 ( FOR 快照报表CLI )
 * - getErrorOrder          检查账变异常
 * 
 * 
 * @author     ---      090915
 * @version    1.2.0
 * @package    lowgame      
 */


/*****************************[ 宏定义帐变ID对应类型关系 ]**********************/
define("ORDER_TYPE_ZRPD",       1);   // 转入频道        pid=0   + 游戏币
define("ORDER_TYPE_PDZC",       2);   // 频道转出        pid=0   - 游戏币
define("ORDER_TYPE_JRYX",       3);   // 加入游戏        pid=0   - 游戏币
define("ORDER_TYPE_XSFD",       4);   // 销售返点        pid=0   + 游戏币
define("ORDER_TYPE_JJPS",       5);   // 奖金派送        pid=0   + 游戏币
define("ORDER_TYPE_ZHKK",       6);   // 追号扣款        pid=0   - 游戏币
define("ORDER_TYPE_DQZHFK",     7);   // 当期追号返款    pid=0   + 游戏币
define("ORDER_TYPE_YXKK",       8);   // 游戏扣款        pid=0   - 游戏币
define("ORDER_TYPE_CDFK",       9);   // 撤单返款        pid=0   + 游戏币
define("ORDER_TYPE_CDFKSP",    99);   // 撤单返款        pid=0   + 游戏币[已经真实扣款后的返款]
define("ORDER_TYPE_CDSXF",     10);   // 撤单手续费      pid=0   - 游戏币
define("ORDER_TYPE_CXFD",      11);   // 撤销返点        pid=0   - 游戏币
define("ORDER_TYPE_CXPJ",      12);   // 撤消派奖        pid=0   - 游戏币
define("ORDER_TYPE_PDXEZC",    13);   // 频道小额转出    pid=0   - 游戏币
define("ORDER_TYPE_TSJEZL",    14);   // 特殊金额整理    pid=0   + 游戏币
define("ORDER_TYPE_TSJEQL",    15);   // 特殊金额清理    pid=0   - 游戏币



class model_orders extends basemodel
{
	/**
	 * 构造函数
	 * @access	public
	 * @return	void
	 */
	function __construct( $aDBO=array() )
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
	 * @author tom   090911 15:37
	 */
	function addOrders( $aOrders = array() )
	{
	    // 01, 数据整理
	    $iLotteryId  = isset($aOrders['iLotteryId']) ? intval($aOrders['iLotteryId']) : 0; // 彩种ID
	    $iMethodId   = isset($aOrders['iMethodId']) ? intval($aOrders['iMethodId']) : 0; // 玩法ID
	    $iTaskId     = isset($aOrders['iTaskId']) ? intval($aOrders['iTaskId']) : 0; // 追号任务ID
	    $iProjectId  = isset($aOrders['iProjectId']) ? intval($aOrders['iProjectId']) : 0; // 方案ID

	    $iFromUserId = isset($aOrders['iFromUserId']) ? intval($aOrders['iFromUserId']) : 0; // 发起人ID
	    $iToUserId   = isset($aOrders['iToUserId']) ? intval($aOrders['iToUserId']) : 0; // 关联人ID
	    $iOrderType  = isset($aOrders['iOrderType']) ? intval($aOrders['iOrderType']) : 0; // 账变类型
	    $fMoney      = isset($aOrders['fMoney']) ? round(floatval($aOrders['fMoney']),4) : 0; // 账变金额
	    $sActionTime = isset($aOrders['sActionTime']) ? date("Y-m-d H:i:s",strtotime($aOrders['sActionTime'])) : date("Y-m-d H:i:s");
	    $sTitle      = '';
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
	    $iNewChannelBalance   = $aRes['channelbalance'];     // 发生账变后 频道账户余额 C
	    $iNewHoldBalance      = $aRes['holdbalance'];        // 发生账变后 频道冻结金额 D
	    $iNewAvailableBalance = $aRes['availablebalance'];   // 发生账变后 频道可用余额 E
	    switch( $iOrderType )
	    {
	        case ORDER_TYPE_ZRPD : // 1,转入频道
	            $iNewChannelBalance   += $fMoney;     // 账户余额 C+
	            $iNewAvailableBalance += $fMoney;     // 可用余额 E+
	            $sTitle = '转入频道';
	            break;
	        case ORDER_TYPE_PDZC : // 2,频道转出
	            $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '频道转出';
	            break;
	        case ORDER_TYPE_JRYX : // 3,加入游戏
	            $iNewHoldBalance      += $fMoney;     // 冻结金额 D+
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '加入游戏';
	            break;
            case ORDER_TYPE_XSFD : // 4,销售返点
	            $iNewChannelBalance   += $fMoney;     // 账户余额 C+
	            $iNewAvailableBalance += $fMoney;     // 可用余额 E+
	            $sTitle = '销售返点';
	            break;
	        case ORDER_TYPE_JJPS : // 5,奖金派送
	            $iNewChannelBalance   += $fMoney;     // 账户余额 C+
	            $iNewAvailableBalance += $fMoney;     // 可用余额 E+
	            $sTitle = '奖金派送';
	            break;
	        case ORDER_TYPE_ZHKK : // 6,追号扣款
	            $iNewHoldBalance      += $fMoney;     // 冻结金额 D+
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '追号扣款';
	            break;
	        case ORDER_TYPE_DQZHFK : // 7,当期追号返款
	            $iNewHoldBalance      -= $fMoney;     // 冻结金额D-
	            $iNewAvailableBalance += $fMoney;     // 可用余额E+
	            $sTitle = '当期追号返款';
	            break;
	        case ORDER_TYPE_YXKK : // 8,游戏扣款
	            $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
	            $iNewHoldBalance      -= $fMoney;     // 冻结金额 D-
	            $sTitle = '游戏扣款';
	            break;
	        case ORDER_TYPE_CDFK : // 9,撤单返款
	            $iNewHoldBalance      -= $fMoney;     // 冻结金额 D-
	            $iNewAvailableBalance += $fMoney;     // 可用余额 E+
	            $sTitle = '撤单返款';
	            break;
	        case ORDER_TYPE_CDFKSP : //99,真实扣款后的撤单返款
	        	$iNewChannelBalance   += $fMoney;     // 账户余额 C+
                $iNewAvailableBalance += $fMoney;     // 可用余额 E+
                $sTitle = '撤单返款';
                $iOrderType = ORDER_TYPE_CDFK;        //帐变类型重定义到 9 撤单返款
                break;
	        case ORDER_TYPE_CDSXF : // 10,撤单手续费
	            $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '撤单手续费';
	            break;
	        case ORDER_TYPE_CXFD : // 11,撤销返点
	            $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '撤销返点';
	            break;
	        case ORDER_TYPE_CXPJ : // 12,撤消派奖
	            $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '撤消派奖';
	            break;
	        case ORDER_TYPE_PDXEZC : // 13,频道小额转出
	            $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '频道小额转出';
	            break;
	        case ORDER_TYPE_TSJEZL : // 14,特殊金额整理
	            $iNewChannelBalance   += $fMoney;     // 账户余额 C+
	            $iNewAvailableBalance += $fMoney;     // 可用余额 E+
	            $sTitle = '特殊金额整理';
	            break;
	        case ORDER_TYPE_TSJEQL : // 15,特殊金额清理
	            $iNewChannelBalance   -= $fMoney;     // 账户余额 C-
	            $iNewAvailableBalance -= $fMoney;     // 可用余额 E-
	            $sTitle = '特殊金额清理';
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
	    $aOrderDatas['lotteryid']       =   $iLotteryId;
	    $aOrderDatas['methodid']        =   $iMethodId;
	    $aOrderDatas['taskid']          =   $iTaskId;
	    $aOrderDatas['projectid']       =   $iProjectId;
	    $aOrderDatas['fromuserid']      =   $iFromUserId;
	    $aOrderDatas['touserid']        =   $iToUserId;
	    $aOrderDatas['ordertypeid']     =   $iOrderType;
	    $aOrderDatas['title']           =   $sTitle;
	    $aOrderDatas['amount']          =   round(floatval($fMoney),4);
	    $aOrderDatas['description']     =   $sDescription;
	    $aOrderDatas['prebalance']      =   round(floatval($aRes['channelbalance']),4);
	    $aOrderDatas['prehold']         =   round(floatval($aRes['holdbalance']),4);
	    $aOrderDatas['preavailable']    =   round(floatval($aRes['availablebalance']),4);
	    $aOrderDatas['channelbalance']  =   round(floatval($iNewChannelBalance),4);
	    $aOrderDatas['holdbalance']     =   round(floatval($iNewHoldBalance),4);
	    $aOrderDatas['availablebalance']=   round(floatval($iNewAvailableBalance),4);
	    $aOrderDatas['agentid']         =   $iAgentId;
	    $aOrderDatas['adminid']         =   $iAdminId;
	    $aOrderDatas['adminname']       =   $sAdminName;
	    $aOrderDatas['clientip']        =   getRealIP();
	    $aOrderDatas['proxyip']         =   isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
	    $aOrderDatas['times']			=	date("Y-m-d H:i:s", time());
	    $aOrderDatas['actiontime']		=	$sActionTime;
	    $aOrderDatas['channelid']		=	$iChannelId;
	    // 转账相关
	    $aOrderDatas['uniquekey']         = isset($aOrders['sUniqueKey']) ? addslashes($aOrders['sUniqueKey']) : '';
	    $aOrderDatas['transferuserid']    = isset($aOrders['iTransferUserid']) ? intval($aOrders['iTransferUserid']) : 0;    // 转账目标账户USERID
	    $aOrderDatas['transferchannelid'] = isset($aOrders['iTransferChannelid']) ? intval($aOrders['iTransferChannelid']) : 0; // 转账目标频道ID
	    $aOrderDatas['transferorderid']   = isset($aOrders['iTransferOrderid']) ? intval($aOrders['iTransferOrderid']) : 0;   // 目标频道账变ID
	    $aOrderDatas['transferstatus']    = isset($aOrders['iTransferStatus']) ? intval($aOrders['iTransferStatus']) : 0;    // 转账状态 1:请求;2:成功;3:失败
	    if( !$this->oDB->insert( 'orders', $aOrderDatas ) )
	    { // 账变记录插入失败
	        return -1007;
	    }

	    // 05, 更新用户账户资金
	    $aUserFund = array();
	    //$aUserFund['cashbalance']		=  round(floatval($iNewCashBalance),4);
	    $aUserFund['channelbalance']    =  $aOrderDatas['channelbalance'];
	    $aUserFund['availablebalance']  =  $aOrderDatas['availablebalance'];
	    $aUserFund['holdbalance']       =  $aOrderDatas['holdbalance'];
	    if( $iOrderType != ORDER_TYPE_PDXEZC && $iOrderType != ORDER_TYPE_TSJEZL && $iOrderType != ORDER_TYPE_TSJEQL )
	    {//特殊帐变不算入用户活跃
	    	$aUserFund['lastactivetime'] =  $aOrderDatas['times']; // 使用发生账变的时间
	    }
	    $aUserFund['lastupdatetime'] = date('Y-m-d H:i:s');
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
	 * 根据账变ID, 更新 '转账账变记录' 的状态值
	 * @author Tom 090810
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
	 * 查看帐变，可以自定义查询条件[带分页效果][后台调用]
	 * 
	 * @access 	public
	 * @author 	Tom     09/05/17
	 * @param 	string	$sFields      // 要查询的内容，表别名:usertree=>ut,orders=>o,ordertype=>ot
	 * @param 	string	$sCondition   // 附加的查询条件，以AND 开始
	 * @param 	int		$iPageRecords // 每页显示的条数
	 * @param 	int		$iCurrPage	  // 当前页
	 * @return array
	 */
	public function & getAdminOrderList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
		$sTableName = "`orders` AS o LEFT JOIN `ordertype` AS ot ON o.`ordertypeid`=ot.`id` ".
		              " LEFT JOIN `usertree` AS ut ON ut.`userid`=o.`fromuserid`".
					  " LEFT JOIN `lottery` AS l ON (o.`lotteryid`=l.`lotteryid`) ".
					  " LEFT JOIN `projects` AS P ON (o.`projectid`=P.`projectid`) ".
					  " LEFT JOIN `method` AS m ON (m.`methodid`=o.`methodid`)";
		$sFields = "ut.`userid`,ut.`username`,o.`entry`,o.`title`,o.`amount`,o.`preavailable`,o.`availablebalance`,
		o.`projectid`,o.`description`,o.`uniquekey`,P.`issue`, ".
				   " o.`times`,o.`transferstatus`,ot.`cntitle`,ot.`entitle`, o.`adminname`, `operations` AS signamount,o.`clientip`,l.`cnname`,m.`methodname` ";
		return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage,' ORDER BY o.`times` DESC ');
	}



	/**
	 * 查询所有的帐变类型
	 * 
	 * @author   james    09/05/17
	 * @access   public
	 * @param    $sReturnType   arr | opts
	 * @param    $mSelected     arr | int | string
	 * @param 	  $sAndWhere     string
	 * @return   mix // 返回结果集数组,或 html.select.options
	 */
	public function getOrderType( $sReturnType = 'arr', $mSelected='', $sAndWhere='' )
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
            $sSel     = in_array($v['id'], $aSelect) ? 'SELECTED' : '';
            $sReturn .= "<OPTION $sSel value=\"".$v['id']."\">".$v['cntitle']."</OPTION>";
        }
        return $sReturn;
	}



	/**
	 * 账变列表的最后一页,进行数据总体结算
	 * 
	 * @access 	public
	 * @author 	Tom     09/06/12
	 * @param 	string	$sCondition      // 要查询的内容
	 * @return array
	 */
	public function & getAdminOrderStat( $sCondition = "" )
	{
	    $sTableName = "`orders` AS o LEFT JOIN `ordertype` AS ot ON o.`ordertypeid`=ot.`id` ".
		              " LEFT JOIN `usertree` AS ut ON ut.`userid`=o.`fromuserid` ";
		$sFields    = " SUM(o.`amount`) AS amounts, `operations` ";
		$sWhere     = "SELECT $sFields FROM  $sTableName WHERE $sCondition GROUP BY `operations` ";
		return $this->oDB->getAll($sWhere);
	}



	/**
	 * 简单加密解密处理
	 * @access 	static
	 * @author 	james	09/05/17
	 * @param 	string	$string		//要加密解密的字符串
	 * @param 	string	$option		//加密解密选项  DECODE:解密   ENCODE:加密
	 */
	static function orderEnCode( $sString, $sOption="DECODE" )
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
	 * 获取总代频道转账结果集
	 * @param datetime   $tBeginTime
	 * @param datetime   $tEndTime
	 * @param string     $sCondition  // 转账的状态(成功失败)
	 *                                 AND 公司销售管理员,对应总代的SQL条件 in( 1,2,3) | 非销售管理员则为空
	 * @return array     $aReturn
	 * @author SAUL 090814
	 */
	public function & getTopProxyTransition( $tBeginTime=0, $tEndTime=0, $sCondition='', $sTranStatusSql='' )
	{
	    $aReturn        = array();
	    $sTranStatusSql = daddslashes($sTranStatusSql);
	    $aProxy = $this->oDB->getAll("SELECT `userid`,`username`,`usertype` FROM `usertree` WHERE `isdeleted` = 0 AND `parentid`=0 $sCondition ORDER BY username");
	    foreach( $aProxy AS $v )
	    {
	        $aReturn[ $v['userid'] ]['username'] = $v['username'];
	        $aReturn[ $v['userid'] ]['usertype'] = $v['usertype'];
	        $aReturn[ $v['userid'] ]['total']    = 0.00; // 转账结余
	        // 初始化转账频道组合  各频道=>银行 1_0, 2_0,  银行至各频道 0_1, 0_2
	        $aReturn[ $v['userid'] ]['channel']  = array();
            $aReturn[ $v['userid'] ]['channel']['0_' . SYS_CHANNELID ] = 0.00;
	        $aReturn[ $v['userid'] ]['channel'][SYS_CHANNELID . '_0' ] = 0.00;
	    }
	    $sCondition = '';
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

	    // 04.a 获取平台资金转入, 账变类型为: ORDER_TYPE_ZRPD  (转入频道)
	    $aResultIn = array(); 
	    $aResultIn = $this->oDB->getAll(
	    		"SELECT ut.`lvtopid`, o.`transferchannelid`, SUM(`amount`) AS TOMSUM FROM `usertree` ut ". 
		        " LEFT JOIN `orders` o FORCE INDEX (idx_search) ON ( o.`fromuserid` = ut.`userid` ) ". 
                " WHERE `ordertypeid`=". ORDER_TYPE_ZRPD .
                " $sCondition $sTranStatusSql GROUP BY ut.`lvtopid`, o.transferchannelid ");

	    // 04.b 获取平台资金转出, 账变类型为: ORDER_TYPE_PDZC  (频道转出)
	    $aResultOut = array(); 
	    $aResultOut = $this->oDB->getAll(
	    		"SELECT ut.`lvtopid`, o.`transferchannelid`, SUM(`amount`) AS TOMSUM FROM `usertree` ut ". 
		        " LEFT JOIN `orders` o FORCE INDEX (idx_search) ON ( o.`fromuserid` = ut.`userid` ) ". 
                " WHERE `ordertypeid`=". ORDER_TYPE_PDZC .
                " $sCondition $sTranStatusSql GROUP BY ut.`lvtopid`, o.transferchannelid ");

	    if( !empty( $aResultIn ) )
	    {
	       foreach( $aResultIn AS $v )
	       {	
	           if( !isset($aReturn[$v['lvtopid']]) )
               {
                   continue;//过滤用户
               }           
	       		$aReturn[ $v['lvtopid'] ]['channel'][   '0_'.SYS_CHANNELID ] = $v['TOMSUM'];
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
	       		$aReturn[ $v['lvtopid'] ]['channel'][  SYS_CHANNELID.'_0' ] = $v['TOMSUM'];
	       }
	    }
	    foreach( $aReturn AS & $v )
	    {
 			// 计算 转账结余 = 所有银行转入频道 - 频道转出银行
 			$v['total'] += ( $v['channel'][ '0_'.SYS_CHANNELID   ] - $v['channel'][ SYS_CHANNELID.'_0' ] );
	    }
	    return $aReturn;
	}



	/**
	 * 中奖用户个数查询(FOR CLI)
	 * @author SAUL
	 * @return integer
	 */
	function getGameWinUserCount()
	{
		$aResult = $this->oDB->getOne(" SELECT COUNT(DISTINCT `fromuserid`) AS `TOMCOUNT` FROM `orders` 
		                                WHERE `ordertypeid`='".ORDER_TYPE_JJPS."' AND date(`actiontime`)='".
		                                date("Y-m-d")."'");
		return ($this->oDB->ar()>0) ? $aResult["TOMCOUNT"] : 0;
	}



	/**
	 * 游戏收入总额(FOR CLI，不计算返点)
	 *
	 */
	function getGameMoneytotal()
	{
		$aResult = $this->oDB->getOne(" SELECT SUM(`amount`) AS `TOMCOUNT` FROM `orders` 
		                     WHERE `ordertypeid`='".ORDER_TYPE_JRYX."' AND DATE(`actiontime`)='".date("Y-m-d")."'");
		return ($this->oDB->ar()>0) ? $aResult["TOMCOUNT"] : 0;
	}



	/**
	 * 游戏奖金总额(FOR CLI)
	 *
	 * @return float
	 */
	function getGameWinMoneytotal()
	{
		$aResult = $this->oDB->getOne(" SELECT SUM(`amount`) AS `TOMCOUNT` FROM `orders` WHERE 
		                          `ordertypeid`='".ORDER_TYPE_JJPS."' AND DATE(`actiontime`)='".date("Y-m-d")."'");
		return ($this->oDB->ar()>0) ? $aResult["TOMCOUNT"] : 0;
	}



	/**
	 * 频道转出总额(FOR CLI)
	 * @author  SAUL
	 */
	function getGameMoneyOut()
	{
		$aResult = $this->oDB->getOne(" SELECT SUM(`amount`) AS `TOMCOUNT` FROM `orders` WHERE 
		                          `ordertypeid`='".ORDER_TYPE_PDZC."' AND DATE(`actiontime`)='".date("Y-m-d")."'");
		return ($this->oDB->ar()>0) ?$aResult["TOMCOUNT"] : 0;
	}



	/**
	 * 转入频道总额(FOR CLI)
	 * @author  SAUL
	 */
	function getGameMoneyIn()
	{
		$aResult = $this->oDB->getOne(" SELECT SUM(`amount`) AS `TOMCOUNT` FROM `orders` WHERE 
		                          `ordertypeid`='".ORDER_TYPE_ZRPD."' AND DATE(`actiontime`)='".date("Y-m-d")."'");
		return ($this->oDB->ar()>0) ? $aResult["TOMCOUNT"] : 0;
	}



	/**
	 * 更新帐变个数
	 *
	 * @return integer
	 */
	function getOrdersTotal()
	{
		$aResult = $this->oDB->getOne(" SELECT COUNT(`entry`) AS `TOMCOUNT` FROM `orders` WHERE 
		                                DATE(`actiontime`)='".date("Y-m-d")."'");
		return ($this->oDB->ar()>0) ? $aResult["TOMCOUNT"] : 0;
	}


	/**
	 * 清除帐变
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
		$iDay = intval($iDay);
		return $this->oDB->query(" DELETE FROM `orders` WHERE `times`<'".
		                          date("Y-m-d 00:00:00",strtotime("-".$iDay."days"))."'");
	}



	/**
	 * 备份帐变(分页机制)
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
		$iDay    = intval($iDay);
		$sSql    = "SELECT COUNT(*) AS `count_orders` FROM `orders` 
		            WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay."days"))."'";
		$aNumLog = $this->oDB->getOne( $sSql );
		$iNum    = $aNumLog['count_orders'];
		$iSize   = 50000;
		$iPages  = ceil( $iNum/$iSize );
		$oGzopen = gzopen( $sFile, 'w9' );
		for( $page =0 ; $page < $iPages; $page++ )
		{
			$sFileContent = "";
			$sSql         = "SELECT * FROM `orders` 
			                 WHERE `times`<'".date("Y-m-d 00:00:00", strtotime("-".$iDay."days"))."' 
			                 limit " .($page*$iSize). ",".$iSize;
			$aLogs = $this->oDB->getAll( $sSql );
			foreach( $aLogs as $log )
			{
				$aKeys   = array();
				$aValues = array();
				foreach( $log as $key=>$value )
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
				$sSql = "insert into `orders` (".join(",", $aKeys).") values (".join(",", $aValues).");";
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
	 * 获取用户的的资金总额，用户的频道全览
	 *
	 * @param integer $iUserId
	 * @param integer $iOrderType
	 */
	function getFundTotalByUserId( $iUserId )
	{
		$iUserId = intval($iUserId);
		$aUser   = array();
		$oProjects = new model_projects();
		$aProjects = $oProjects->projectGetResult( $iUserId, FALSE, 
		              ' SUM(`totalprice`) as TOMSUM, P.`writetime` ', 
		              " AND P.`iscancel`=0 AND P.`writetime` >= '".date('Y-m-d 00:00:00')."'  ", '', 0 );
		$aUser["totalin"] = $aProjects[0]["TOMSUM"];
		unset($oProjects,$aProjects);
		// 获取 '奖金派送' 金额
		$aGet = $this->oDB->getDataCached(" SELECT SUM(amount) AS sum_amount FROM `orders` WHERE 
		                                    `fromuserid` = '".$iUserId."' AND `ordertypeid` = '".
		                                     ORDER_TYPE_JJPS."' AND DATE(times) =  DATE(now())", 10);
		if( !empty($aGet) && !empty($aGet[0]) )
		{
			$aUser["totalout"] = $aGet[0]["sum_amount"];
		}
		return $aUser;
	}



	/**
	 * 用户帐变查询
	 *
	 * @param integer $iUserid
	 * @param bool $bAllChild
	 * @param string $sField
	 * @param string $sWhere
	 * @param string $sOrder
	 * @param integer $iPageRecord
	 * @param integer $iCurrPage
	 */
	function userOrderList( $iUserid, $bAllChild, $sField, $sWhere, $sOrder, $iPageRecord, $iCurrPage = 1 )
	{
		$aResult = array( "results"=>array(), "affects"=>0 );
		if( !is_numeric( $iUserid ) )
		{
			return $aResult;
		}
		$sTableName = "`orders` AS O Left join `ordertype` AS OT on(O.`ordertypeid`=OT.`id`)" 
					. " LEFT JOIN `usertree` AS UT on (O.`fromuserid`=UT.`userid`) " 
					. " LEFT JOIN `lottery` AS L ON (O.`lotteryid`=L.`lotteryid`)" 
					. " LEFT JOIN `projects` AS P on (O.`projectid`=P.`projectid`)" 
					. " LEFT JOIN `method` AS M on (O.`methodid`= M.`methodid`)";
		if( empty( $sField ) )
		{
			$sField = "O.*,OT.`cntitle`,OT.`operations`,`UT`.`username`,L.`cnname`,M.`methodname`,P.`issue`";
		}
		$sCondition = " 1 ";
		if( !empty( $sWhere ) )
		{
			$sCondition .= $sWhere;
		} // 需要屏蔽非用户能够看到的帐变类型
		$sCondition .= " AND OT.`displayforuser`='1' ";
		if( $bAllChild )
    	{
    		if( $iUserid > 0 )
    		{
    			$sCondition .=" AND (FIND_IN_SET(".intval($iUserid).",UT.`parenttree`) OR (UT.`userid`='".$iUserid."'))";
    		}
    	}
    	else
    	{
    		if( $iUserid > 0 )
    		{
    			$sCondition .=" AND O.`fromuserid`='".$iUserid."'";
    		}
    	}
		if( $iPageRecord == 0 )
		{
			return $this->oDB->getAll( "SELECT ".$sField." FROM ".$sTableName." WHERE ".$sCondition . $sOrder );
		}
		return $this->oDB->getPageResult( $sTableName, $sField, $sCondition, $iPageRecord, $iCurrPage, $sOrder );
	}
	
	
	/**
	 * 获取指定代理自己及所有下级返点总额
	 * 
	 * @param 	integer	$iProxyId		指定总代Id
	 * @param 	string	$sCondition		查询条件
	 * @return	array					返点总额
	 * @author	Mark
	 *
	 */
	function getProxyTotalPoint( $iProxyId = 0, $sCondition = '' )
	{
		if( !isset($iProxyId) || !is_numeric($iProxyId) )//是否指定代理
		{
			$aResult = array();
			return $aResult;
		}
		$iProxyId = intval($iProxyId);
		if( !empty($sCondition) )
		{
		    $sCondition .= " AND ";
		}
		$iOrderTypeId       = ORDER_TYPE_XSFD;//选择返点账变类型
		$iOrderCancelTypeId = ORDER_TYPE_CXFD;//撤销返点类型
		$sReturnCondition = $sCondition . " `ordertypeid` = '$iOrderTypeId' AND `fromuserid` = '$iProxyId' ";
		$aReturn = $this->oDB->getOne( " SELECT SUM(`amount`) AS totalpoint FROM `orders` FORCE INDEX ( idx_search ) 
		                             WHERE $sReturnCondition " );
		$sCancelCondition  =  $sCondition . " `ordertypeid` = '$iOrderCancelTypeId' AND `fromuserid` = '$iProxyId' ";
		$aResultCancel = $this->oDB->getOne( " SELECT SUM(`amount`) AS totalpoint FROM `orders` FORCE INDEX ( idx_search ) 
		                             WHERE $sCancelCondition " );
		return array('totalpoint' => floatval($aReturn['totalpoint']) - floatval($aResultCancel['totalpoint']));
	}



	/**
	 * 获取报表的成本和奖金以及返点总和(结果集已经整理,通过 UDP来计算的)
	 * @author SAUL
	 * @param integer $iUserId
	 * @param string $sCondition
	 * @return array
	 */
	function getTotalBonusAndPrize( $iUserId, $sCondition, $sUserCondition, $haveCharge = true, $sWhere1 = "" )
	{
		$iUserId = intval($iUserId);
		if( $iUserId == 0 )
		{ // 查询总代的
			$sTableName = " `projects` AS P JOIN `usertree` AS a ";
			$sField     = " Sum(P.`bonus`) AS `SUM_BONUS`, SUM(P.`totalprice`) AS `SUM_PRIZE`, 
			                SUM(P.`totalprice`*P.`lvtoppoint`) AS `SUM_POINT`,P.`lvtopid` ";
			$sWhere     = " AND P.`iscancel` = '0' AND a.`userid`=P.`userid` ".$sCondition.$sUserCondition;			
			$aTemp      = $this->oDB->getAll( " SELECT ".$sField." FROM ".$sTableName." WHERE 1 ".$sWhere." 
			                                    GROUP BY P.`lvtopid` " );
			$aResult    = array();
			foreach( $aTemp as $Temp )
			{
				$aResult[$Temp["lvtopid"]]["bonus"] = $Temp["SUM_BONUS"];
				$aResult[$Temp["lvtopid"]]["prize"] = $Temp["SUM_PRIZE"];
				$aResult[$Temp["lvtopid"]]["point"] =  $Temp["SUM_POINT"];
			}
			if ($haveCharge === true){
				/*计算大额撤单手续费*/
				$iOrderTypeId = ORDER_TYPE_CDSXF;//撤单手续费账变类型ID
				$sTableName = "`orders` AS o LEFT JOIN `usertree` AS a ON(o.`fromuserid`=a.`userid`)";
				$sField = "SUM(o.`amount`) AS amount,a.`lvtopid`";
				$sWhere = " AND o.`ordertypeid` = '$iOrderTypeId'".$sWhere1.$sUserCondition;
				
				$aTempAmout  = $this->oDB->getAll( " SELECT ".$sField." FROM ".$sTableName." WHERE 1 ".$sWhere." 
				                                    GROUP BY a.`lvtopid` " );
				foreach ($aTempAmout as $aAmount)
				{
				    $aResult[$aAmount['lvtopid']]['prize'] += $aAmount['amount'];
				}
			}
			return $aResult;
		}
		else 
		{ // 查询非总代的
			$aUserInfo  = $this->oDB->getOne("SELECT * FROM `usertree` WHERE `userid`='".$iUserId."'");
			$aUserLevel = explode( ",", $aUserInfo["parenttree"] );
			if( empty($aUserInfo["parenttree"]) )
			{
				$iUserLevel = 2;
			}
			else
			{
				$iUserLevel = count($aUserLevel)+2;  //用户树的层级
			}
			$sTableName = " `projects` AS P JOIN `usertree` AS a ";
			$sField     = " Sum(P.`bonus`) AS `SUM_BONUS`, SUM(P.`totalprice`) AS `SUM_PRIZE`,"
			             ." SUBSTRING_INDEX(a.`parenttree`,',',".$iUserLevel.") AS `USERTREE`,"
			             ." P.`userid`,a.`username`,a.`usertype`,a.`parentid` ";
			$sWhere     = " AND P.`iscancel` = '0' AND find_in_set(".$iUserId.",a.`parenttree`) AND "
			             ." a.`userid` = P.`userid` ".$sCondition;
			$aTemp1     = $this->oDB->getAll(" SELECT ".$sField." FROM ".$sTableName." where 1 ".$sWhere.
			                                  $sUserCondition." AND P.`iscancel`='0' group by P.`userid`");	
			$aResult    = array();
			foreach( $aTemp1 as $v)
			{				
				$a = explode( ",", $v["USERTREE"] );
				if( $v["parentid"] == $iUserId )
				{
					$sStrUser = $v["userid"];
				}
				else
				{
					$sStrUser = $a[count($a)-1];
				}
				if( isset($aResult[$sStrUser]) )
				{
					$aResult[$sStrUser]["bonus"] += $v["SUM_BONUS"];
					$aResult[$sStrUser]["prize"] += $v["SUM_PRIZE"]; 
				}
				else
				{
					$aResult[$sStrUser]["bonus"] = $v["SUM_BONUS"];
					$aResult[$sStrUser]["prize"] = $v["SUM_PRIZE"]; 
					$aResult[$sStrUser]["point"] = 0; //防止返点值为0
				}
			}
			if ($haveCharge === true){
				/*计算大额撤单手续费*/
				$iOrderTypeId = ORDER_TYPE_CDSXF;//撤单手续费账变类型ID
				$sTableName = " `orders` AS o JOIN `usertree` AS a ";
				$sField     = " Sum(o.`amount`) AS `SUM_AMOUNT`, a.`userid`,"
				             ." SUBSTRING_INDEX(a.`parenttree`,',',".$iUserLevel.") AS `USERTREE`,a.`parentid`";
				$sWhere     = " AND find_in_set(".$iUserId.",a.`parenttree`) AND "
				             ." a.`userid` = o.`fromuserid` AND o.`ordertypeid` = '$iOrderTypeId' ".$sWhere1;
				$aTemp1     = $this->oDB->getAll(" SELECT ".$sField." FROM ".$sTableName." where 1 ".$sWhere.
				                                  $sUserCondition." GROUP BY o.`fromuserid`");
				foreach( $aTemp1 as $v)
				{				
					$a = explode( ",", $v["USERTREE"] );
					if( $v["parentid"] == $iUserId )
					{
						$sStrUser = $v["userid"];
					}
					else
					{
						$sStrUser = $a[count($a)-1];
					}
					if( isset($aResult[$sStrUser]) )
					{
						$aResult[$sStrUser]["prize"] += $v["SUM_AMOUNT"]; 
					}
					else
					{
						$aResult[$sStrUser]["prize"] = $v["SUM_AMOUNT"]; 
					}
				}		
				unset($aTemp1);
			}
			$sTableName = " `projects` AS P JOIN `userdiffpoints` AS UDP JOIN `usertree` AS a ";
			$sField     = " Sum(UDP.diffmoney) AS `SUM_POINT`,"
			             ." SUBSTRING_INDEX(a.`parenttree`,',',".$iUserLevel.") AS `USERTREE`,"
			             ." UDP.`userid`,a.`username`,a.`usertype`,a.`parentid` ";
			$sWhere     = " AND P.`iscancel` = '0' AND find_in_set(".$iUserId.",a.`parenttree`) AND "
			             ." UDP.`projectid`=P.`projectid` AND a.`userid` = UDP.`userid` ".$sCondition;
			$aTemp      = $this->oDB->getAll("SELECT ".$sField." FROM ".$sTableName." where 1 ".$sWhere.
			               $sUserCondition." AND UDP.`status`=1 AND P.`iscancel`='0' GROUP BY UDP.`userid`");
			//说明:如果需要及时计算，请取消 AND UDP.`status`='1'反之加上这个条件//AND UDP.`status`='1'	
			foreach( $aTemp as $v)
			{				
				$a = explode( ",", $v["USERTREE"] );
				if( $v["parentid"] == $iUserId )
				{
					$sStrUser = $v["userid"];
				}
				else
				{
					$sStrUser = $a[count($a)-1];
				}
				if( isset($aResult[$sStrUser]["point"]) )
				{
					$aResult[$sStrUser]["point"] +=  $v["SUM_POINT"];
				}
				else
				{
					$aResult[$sStrUser]["point"] =  $v["SUM_POINT"];
				}
				unset($a);
			}
			unset($aTemp);
			return $aResult;
		}
	}



	/**
	 * 查询指定用户的因为自身获取到的相关的返点和销售总额
	 *
	 * @param integer $iUserId
	 * @param string $sCondition
	 */
	function getUserBonusAndPrize( $iUserId, $sCondition, $havaCharge = true, $sWhere = "" )
	{
		$aResult = array();
		$iUserId = intval($iUserId);
		if( $iUserId <= 0 )
		{
			return $aResult;
		}
		$sSql = " SELECT SUM(P.`bonus`) AS `bonus`,SUM(UDP.`diffmoney`) AS `point`,SUM(P.`totalprice`) AS `price`,
		          UT.`username`,UT.`usertype` FROM `usertree` AS UT LEFT JOIN `projects` AS P ON 
		          (P.`userid`=UT.`userid`) LEFT JOIN `userdiffpoints` AS UDP ON 
		          (P.`userid`=UDP.`userid` AND P.`projectid`=UDP.`projectid`) WHERE 1 "
			   .$sCondition." AND P.`iscancel`='0' AND P.`userid`='".$iUserId."'";
		$aResult = $this->oDB->getOne( $sSql );
		if ($havaCharge === true){
			$iOrderTypeId = ORDER_TYPE_CDSXF;//撤单手续费账变类型ID
			//计算大额撤单手续费
			$sSqlWhere = !empty($sWhere) ? $sWhere : $sCondition;
			$sSql = "SELECT SUM(`amount`) AS amount FROM `orders` AS o
			         WHERE `fromuserid` = '$iUserId' AND o.`ordertypeid` = '$iOrderTypeId'" . $sSqlWhere;
			$aPrice = $this->oDB->getOne( $sSql );//获取大额撤单手续费
			$aResult['price'] += $aPrice['amount'];
		}
		return $aResult;
	}



	/**
	 * 代理查看一个用户的游戏详情时候调用
	 *
	 * @param integer $iUserId
	 * @param string $sCondition
	 * @return array
	 */
	function getTotalUserBonusByMethod( $iUserId, $sCondition, $needCheck = TRUE, $sWhere = "", $haveCharge = true )
	{
		if( !isset($needCheck) )
		{
			$needCheck = TRUE;
		}
		$aResult = array();
		if( $needCheck )
		{
			/* @var $oUser model_user */
			$oUser = A::singleton("model_user");
			$iUser = $_SESSION["userid"];
			if( $_SESSION["usertype"] == 2 )
			{ //总代管理员
				if( $oUser->IsAdminSale($iUser) )
				{
					if( !$oUser->isInAdminSale($iUserId, $iUser) )
					{
						return $aResult;
					}
				}
				else
				{
					if( !$oUser->isParent($iUserId, $oUser->getTopProxyId($iUser, FALSE)) )
					{
						return $aResult;
					}
				}
			}
			else
			{
				if( !$oUser->isParent($iUserId, $iUser) )
				{
					return $aResult;
				}
			}
		}
		$sSql = "SELECT P.`lotteryid`,P.`methodid`,SUM(UDP.`diffmoney`) AS `summoney`"
			." FROM `userdiffpoints` AS UDP"
			." JOIN `projects` AS P JOIN `usertree` AS UT"
			." WHERE UDP.`projectid` = P.`projectid` AND UT.`userid`=UDP.`userid`".$sCondition
			." AND (FIND_IN_SET(".$iUserId.",UT.`parenttree`) OR UT.`userid`='".$iUserId."')"
			." AND P.`iscancel`='0'"
			." GROUP BY P.`lotteryid`,P.`methodid`";
		$aResult[1] = $this->oDB->getAll( $sSql );
		$sSql = "SELECT P.`lotteryid`,P.`methodid`,SUM(P.`bonus`) AS `sumbonus`,SUM(P.`totalprice`) AS `sumprice`"
			." FROM `projects` AS P JOIN `usertree` AS UT"
			." WHERE UT.`userid`=P.`userid`".$sCondition
			." AND (FIND_IN_SET(".$iUserId.",UT.`parenttree`) OR UT.`userid`='".$iUserId."')"
			." AND P.`iscancel`='0'"
			." GROUP BY P.`lotteryid`,P.`methodid`";
		$aResult[2] =  $this->oDB->getAll($sSql);
		if ($haveCharge === true){
			$sSql = "SELECT sum(o.`amount`) AS charge,o.`lotteryid`,o.`methodid` "
					." FROM `orders` AS o "
					."LEFT JOIN `usertree` AS ut ON (o.`fromuserid` = ut.`userid`)"
					."WHERE (FIND_IN_SET(".$iUserId.",ut.`parenttree`) OR ut.`userid`='".$iUserId."') "
					." AND `ordertypeid` = " . ORDER_TYPE_CDSXF . $sWhere
					." GROUP BY o.`lotteryid`,o.`methodid`";
			$aCharge = $this->oDB->getAll($sSql);
			$aTemp = array();
			if (!empty($aCharge)){
				foreach ($aCharge as $k => $v){
					foreach ($aResult[2] as $key => $val){
						if ($v['lotteryid'] == $val['lotteryid'] && $v['methodid'] == $val['methodid']){
							$aResult[2][$key]['sumprice'] += $v['charge'];
						} else {
							$aTemp['lotteryid'] = $v['lotteryid'];
							$aTemp['methodid'] = $v['methodid'];
							$aTemp['sumbonus'] = 0.00;
							$aTemp['sumprice'] = $v['charge'];
							array_push($aResult[2], $aTemp);
							unset($aTemp);
						}
					}
				}
			}
		}
		return $aResult;
	}


   /**
     * 获取总代(本人+下级) 的数据集 ( FOR 快照报表CLI )
     *
     * @param datetime $tBeginTime
     * @param datetime $tEndTime
     * @param string   $sAction     tranferin, tranferout, cancel
     * @return array
     * @author Tom 090608
     */
    public function & getTopProxyData( $tBeginTime=0, $tEndTime=0, $sAction='tranferin', $sCondition='' )
    {
        if( $sAction == 'tranferin' )
        { // 团队转入频道
            $aArray = array(
                ORDER_TYPE_ZRPD, // 转入频道
            );
        }
        elseif( $sAction == 'tranferout' )
        { // 团队频道转出
             $aArray = array(
                ORDER_TYPE_PDZC,  // 频道转出
            );
        }
        elseif( $sAction == 'cancel' )
        { // 团队撤单手续费
        	$aArray = array(
                ORDER_TYPE_CDSXF,  // 撤单手续费
            );
        }
        else
        {
            die('Error: Orders::getTopProxyData() ');
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
     * 检查账变异常
     * 返回账变异常数据
     * @param string	$iUserId		用户Id
     * @param int		$iOrderTypeId	账变类型ID
     * @param string	$sStarttime		查询开始时间
     * @param string	$sEndtime 		查询结束时间
     * @return array()
     * 
     * @author mark
     */
    public function getErrorOrder( $aCondition = array(), $iPageRecord = 0, $iCurrentPage = 0 )
    {
    	$iOrderTypeOne		= ORDER_TYPE_PDZC;
    	$iOrderTypeTwo		= ORDER_TYPE_ZRPD;
    	$iOrderTypeThree	= ORDER_TYPE_PDXEZC;
    	$iOrderTypeId		= intval( $aCondition['ordertypeid'] );
    	$iUserId			= intval( $aCondition['userid'] );
    	$sStarttime 		= $aCondition['starttime'];
    	$sEndtime			= $aCondition['endtime'];
    	$iPageRecord		= intval($iPageRecord);
    	$iCurrentPage		= intval($iCurrentPage);
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
    /**
     * 获取指定用户自身和所有下级代购费和奖金总额和返点总额
     *
     * @param string  $sCondition   查询条件
     * @param int     $iUserId      用户ID
     * @param boolean $bIsChild     是否查询下级用户
     * @author mark
     */
    public function getReportData( $iUserId = 0, $sCondition = 'AND 1', $bIsChild = FALSE, $havaCharge = true )
    {
    	if ($havaCharge === true){
    		$iOrderTypeId = ORDER_TYPE_CDSXF;//撤单手续费账变类型ID
    	}
        $iUserId = intval($iUserId);
        if ( $iUserId == 0 )
        {
            return array();
        }
        if( $bIsChild )
        {//查询用户下级
            $aBonusAmountResult = array();
            /*计算总代购费和奖金总额*/
            $sSql = "";
            $sSql .= " SELECT p.`userid`,ut.`parentid`,ut.`parenttree`,SUM(p.`totalprice`) AS amount,SUM(p.`bonus`) AS bonus ";
            $sSql .= " FROM `projects`  AS  p ";
            $sSql .= " LEFT JOIN `usertree` AS ut ON(p.`userid` = ut.`userid`) ";
            $sSql .= " WHERE FIND_IN_SET('$iUserId',ut.`parenttree`) AND p.`iscancel`='0' ";
            $sSql .= $sCondition;
            $sSql .= " GROUP BY p.`userid` ";
            $aResult = $this->oDB->getDataCached( $sSql, 86400 );
            /*归类直接下级*/
            foreach ( $aResult as $aValue )
            {
                if( $aValue['parentid'] == $iUserId )
                {
                    $iNeedUserId = $aValue['userid'];
                }
                else 
                {
                    $aParentTree = explode( ",", $aValue['parenttree']);
                    foreach ( $aParentTree as $iKey => $aTree )
                    {
                        if( $aTree != $iUserId )
                        {
                            continue;
                        }
                        else 
                        {
                            $iNeedUserId = $aParentTree[$iKey+1];
                            break;
                        }
                    }
                }
                if( !isset($aBonusAmountResult[$iNeedUserId]['amount']) )
                {
                    $aBonusAmountResult[$iNeedUserId]['amount'] = 0.00;
                }
                if( !isset($aBonusAmountResult[$iNeedUserId]['bonus']) )
                {
                    $aBonusAmountResult[$iNeedUserId]['bonus'] = 0.00;
                }
                $aBonusAmountResult[$iNeedUserId]['amount'] += $aValue['amount'];
                $aBonusAmountResult[$iNeedUserId]['bonus']  += $aValue['bonus'];
            }
            if ($havaCharge === true){
            	/*计算大额撤单手续费*/
	            $sSql = "";
	            $sSql .= "SELECT p.`userid`,ut.`parentid`,ut.`parenttree`,SUM(o.`amount`) AS cacel_amount";
	            $sSql .= " FROM `orders` AS o JOIN `usertree` AS ut LEFT JOIN `projects` as p ON (p.`projectid` = o.`projectid`) ";
	            $sSql .= " WHERE find_in_set('$iUserId',ut.`parenttree`) AND p.`iscancel`='0' ";
	            $sSql .= " AND o.`ordertypeid` = '$iOrderTypeId' ";
	            $sSql .= $sCondition;
	            $sSql .= " GROUP BY p.`userid` ";
	            $aCancelAmount = $this->oDB->getDataCached( $sSql, 86400 );
	            /*归类直接下级*/
	            foreach ( $aCancelAmount as $aValue )
	            {
	                if( $aValue['parentid'] == $iUserId )
	                {
	                    $iNeedUserId = $aValue['userid'];
	                }
	                else 
	                {
	                    $aParentTree = explode( ",", $aValue['parenttree']);
	                    foreach ( $aParentTree as $iKey => $aTree )
	                    {
	                        if( $aTree != $iUserId )
	                        {
	                            continue;
	                        }
	                        else 
	                        {
	                            $iNeedUserId = $aParentTree[$iKey+1];
	                            break;
	                        }
	                    }
	                }
	                if( !isset($aBonusAmountResult[$iNeedUserId]['amount']) )
	                {
	                    $aBonusAmountResult[$iNeedUserId]['amount'] = 0.00;
	                }
	                $aBonusAmountResult[$iNeedUserId]['amount'] += $aValue['amount'];
	            }
            }
            /*计算返点总额*/
            $sSql = "";
            $sSql .= " SELECT udp.`userid`,ut.`parentid`,ut.`parenttree`,SUM( udp.`diffmoney`) AS point ";
            $sSql .= " FROM `projects`  AS  p ";
            $sSql .= " LEFT JOIN `usertree` AS ut ON(p.`userid` = ut.`userid`) ";
            $sSql .= " LEFT JOIN `userdiffpoints` AS udp ON(p.`projectid`= udp.`projectid`) ";
            $sSql .= " WHERE FIND_IN_SET('$iUserId',ut.`parenttree`) AND p.`iscancel`='0' ";
            $sSql .= $sCondition;
            $sSql .= " GROUP BY udp.`userid` ";
            $aPointResult = $this->oDB->getDataCached( $sSql, 86400 );
            /*归类直接下级*/
            foreach ( $aPointResult as $aValue )
            {
                if( $aValue['userid'] == $iUserId )
                {
                    continue;
                }
                if( $aValue['parentid'] == $iUserId )
                {
                    $iNeedUserId = $aValue['userid'];
                }
                else 
                {
                    $aParentTree = explode( ",", $aValue['parenttree']);
                    foreach ( $aParentTree as $iKey => $aTree )
                    {
                        if( $aTree != $iUserId )
                        {
                            continue;
                        }
                        else 
                        {
                            $iNeedUserId = $aParentTree[$iKey+1];
                            break;
                        }
                    }
                }
                if( !isset($aBonusAmountResult[$iNeedUserId]['point']) )
                {
                    $aBonusAmountResult[$iNeedUserId]['point'] = 0.00;
                }
                $aBonusAmountResult[$iNeedUserId]['point'] += $aValue['point'];
            }
            return $aBonusAmountResult;
        }
        else
        {//查询指定用户用所的下级
            /*计算总代购费和奖金总额*/
            $sSql = "";
            $sSql .= " SELECT SUM(p.`totalprice`) AS amount,SUM(p.`bonus`) AS bonus ";
            $sSql .= " FROM `projects`  AS  p ";
            $sSql .= " LEFT JOIN `usertree` AS ut ON(p.`userid` = ut.`userid`) ";
            $sSql .= " WHERE (FIND_IN_SET('$iUserId',ut.`parenttree`) or p.`userid`='$iUserId' ) AND p.`iscancel`='0' ";
            $sSql .= $sCondition;
            $aResult = $this->oDB->getDataCached( $sSql, 86400 );
            if( !isset($aResult[0]['amount']) )
            {
                $aResult[0]['amount'] = 0.00;
            }
            if( !isset($aResult[0]['bonus']) )
            {
                $aResult[0]['bonus'] = 0.00;
            }
            if ($havaCharge === true){
            	/*计算大额撤单手续费*/
	            $sSql = "";
	            $sSql .= "SELECT SUM(o.`amount`) AS cacel_amount";
	            $sSql .= " FROM `orders` AS o JOIN `usertree` AS ut LEFT JOIN `projects` as p ON (p.`projectid` = o.`projectid`) ";
	            $sSql .= " WHERE (find_in_set('$iUserId',ut.`parenttree`) or p.`userid`='$iUserId' ) AND p.`iscancel`='0' ";
	            $sSql .= " AND o.`ordertypeid` = '$iOrderTypeId' ";
	            $sSql .= $sCondition;
	            $aCancelAmount = $this->oDB->getDataCached( $sSql, 86400 );
	            if( !isset($aCancelAmount[0]['cancel_amount']) )
	            {
	                $aCancelAmount[0]['cancel_amount'] = 0.00;
	            }
	            $aResult[0]['amount'] += $aCancelAmount[0]['cancel_amount'];
            }
            /*计算返点总额*/
            $sSql = "";
            $sSql .= " SELECT SUM( udp.`diffmoney`) AS point ";
            $sSql .= " FROM `projects`  AS  p ";
            $sSql .= " LEFT JOIN `usertree` AS ut ON(p.`userid` = ut.`userid`) ";
            $sSql .= " LEFT JOIN `userdiffpoints` AS udp ON(p.`projectid`= udp.`projectid` AND udp.`userid` >= '$iUserId') ";
            $sSql .= " WHERE (FIND_IN_SET('$iUserId',ut.`parenttree`) or p.`userid`='$iUserId' ) AND p.`iscancel`='0' ";
            $sSql .= $sCondition;
            $aPointResult = $this->oDB->getDataCached( $sSql, 86400 );
            if( !isset($aPointResult[0]['point']) )
            {
                $aPointResult[0]['point'] = 0.00;
            }
            return array_merge( $aResult[0], $aPointResult[0] );
        }
        
    }
    
    
    /**
     * 根据总代管理员的id，获取总代分配给他的下级用户对总代的返点额之和
     * 
     * @param 		int     $iAdminProxyId 		// 总代管理员id
     * @param 		int		$iTopProxyId		// 总代id
     * @param 		string 	$sCondition			// 查询条件
     * @param 		bool	$bExtend			// 为true则只获取总代管理员下的返点总额
     *
     * @version 	v1.0	2010-05-28
     * @author 		louis
     *
     * @return 		array
     */
    public function getAdminProxyPoint($iAdminProxyId, $iTopProxyId, $sCondition = 'AND 1', $bExtend = false){
    	if (!is_numeric($iAdminProxyId) || $iAdminProxyId < 0 || !is_numeric($iTopProxyId) || $iTopProxyId < 0)	return false;
    	// 总代管理员的下级
    	$sSql = "SELECT topproxyid FROM useradminproxy WHERE adminid = {$iAdminProxyId}";
    	$aResult = $this->oDB->getAll($sSql);
    	$sIdList = "";
    	foreach ($aResult as $k => $v){
    		$sIdList .= $v['topproxyid'] . ',';
    	}
    	$sIdList = substr($sIdList, 0, -1);

    	$aBonusAmountResult = array();
    	// 如果没有用户，则直接返回空数组
    	if (empty($sIdList)) return $aBonusAmountResult;
    	
    	if ($bExtend){
    		// 获取总代对应分配给总代管理员的下级返点金额
	        $sSql = "";
	        $sSql .= " SELECT `userid`,SUM( `diffmoney`) AS point ";
	        $sSql .= " FROM `userdiffpoints` ";
	        $sSql .= " WHERE `userid` = {$iTopProxyId} AND `cancelstatus` = 0 ";
	        $sSql .= " AND `projectid` in ";
	        $sSql .= " (SELECT udp.`projectid` ";
	        $sSql .= " FROM `projects`  AS  p ";
	        $sSql .= " LEFT JOIN `userdiffpoints` AS udp ON(p.`projectid`= udp.`projectid`) ";
	        $sSql .= " WHERE udp.`userid` in ({$sIdList}) AND p.`iscancel`='0' ";
	        $sSql .= $sCondition . ")";
	        $aTopPointResult = $this->oDB->getDataCached( $sSql, 86400 );
	        if (!empty($aTopPointResult)){
	        	foreach ($aTopPointResult as $k => $v){
	        		if( !isset($aBonusAmountResult['totalpoint']) )
		            {
		                $aBonusAmountResult['totalpoint'] = 0.00;
		            }
		            $aBonusAmountResult['totalpoint'] += isset($v['point']) ? $v['point'] : 0;
	        	}
	        }
	        return $aBonusAmountResult;
    	}

        /*计算总代购费和奖金总额*/
        $sSql = "";
        $sSql .= " SELECT p.`userid`,SUM(p.`totalprice`) AS amount,SUM(p.`bonus`) AS bonus ";
        $sSql .= " FROM `usertree` AS ut LEFT JOIN `projects` AS p ON (ut.`userid` = p.`userid`)";
        $sSql .= " WHERE (ut.`userid` in ({$sIdList}) OR (ut.`userid` NOT IN ({$sIdList}) AND ut.`lvproxyid` IN ({$sIdList})))"; 		$sSql .= " AND p.`iscancel`='0' ";
        $sSql .= $sCondition;
        $sSql .= " GROUP BY p.`userid` ";
        $aResult = $this->oDB->getDataCached( $sSql, 86400 );
        /*归类直接下级*/
        if (!empty($aResult)){
        	foreach ( $aResult as $aValue )
	        {
	            if( !isset($aBonusAmountResult[$iTopProxyId]['amount']) )
	            {
	                $aBonusAmountResult[$iTopProxyId]['amount'] = 0.00;
	            }
	            if( !isset($aBonusAmountResult[$iTopProxyId]['bonus']) )
	            {
	                $aBonusAmountResult[$iTopProxyId]['bonus'] = 0.00;
	            }
	            $aBonusAmountResult[$iTopProxyId]['amount'] += $aValue['amount'];
	            $aBonusAmountResult[$iTopProxyId]['bonus']  += $aValue['bonus'];
	        }
        }
        
        // 获取总代管理员下级的总返点
        /*计算返点总额*/
        $aBonusAmountResult[$iTopProxyId]['point'] = isset($aBonusAmountResult[$iTopProxyId]['point']) ? $aBonusAmountResult[$iTopProxyId]['point'] : 0;
        $sSql = "";
        $sSql .= " SELECT udp.`userid`,SUM( udp.`diffmoney`) AS point ";
        $sSql .= " FROM `usertree` AS ut LEFT JOIN `projects`  AS  p ON (ut.userid = p.userid)";
        $sSql .= " LEFT JOIN `userdiffpoints` AS udp ON(p.`projectid`= udp.`projectid`) ";
        $sSql .= " WHERE (ut.`userid` in ({$sIdList}) OR (ut.`userid` NOT IN ({$sIdList}) AND ut.`lvproxyid` IN ({$sIdList})))";
        $sSql .= " AND p.`iscancel`='0' ";
        $sSql .= $sCondition;
        $sSql .= " GROUP BY udp.`userid` ";
        $aPointResult = $this->oDB->getDataCached( $sSql, 86400 );
        /*归类直接下级*/
        if (!empty($aPointResult)){
        	foreach ( $aPointResult as $aValue )
	        {
	            if( !isset($aBonusAmountResult[$iTopProxyId]['point']) )
	            {
	                $aBonusAmountResult[$iTopProxyId]['point'] = 0.00;
	            }
	            $aBonusAmountResult[$iTopProxyId]['point'] += $aValue['point'];
	        }
        }
        
        // 获取总代对应分配给总代管理员的下级返点金额
        /*$sSql = "";
        $sSql .= " SELECT `userid`,SUM( `diffmoney`) AS toppoint ";
        $sSql .= " FROM `userdiffpoints` ";
        $sSql .= " WHERE `userid` = {$iTopProxyId} AND `cancelstatus` = 0 ";
        $sSql .= " AND `projectid` in ";
        $sSql .= " (SELECT udp.`projectid` ";
        $sSql .= " FROM `projects`  AS  p ";
        $sSql .= " LEFT JOIN `userdiffpoints` AS udp ON(p.`projectid`= udp.`projectid`) ";
        $sSql .= " WHERE udp.`userid` in ({$sIdList}) AND p.`iscancel`='0' ";
        $sSql .= $sCondition . ")";
        $aTopPointResult = $this->oDB->getDataCached( $sSql, 86400 );
        if (!empty($aTopPointResult)){
        	foreach ($aTopPointResult as $k => $v){
        		if( !isset($aBonusAmountResult[$iTopProxyId]['toppoint']) )
	            {
	                $aBonusAmountResult[$iTopProxyId]['toppoint'] = 0.00;
	            }
	            $aBonusAmountResult[$iTopProxyId]['point'] += isset($v['toppoint']) ? $v['toppoint'] : 0;
        	}
        }*/
        return $aBonusAmountResult;
    }
}
?>
