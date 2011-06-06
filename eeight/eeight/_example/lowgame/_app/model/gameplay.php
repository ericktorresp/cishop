<?php
/**
 * 文件 : /_app/model/gameplay.php
 * 功能 : 数据模型 - 用户投单处理模型继承游戏基础模型
 *
 * - gameBuy                    用户投单处理
 * - traceInsertData            用户追号数据处理流程[非事务]
 * - getExpandCode              获取号码扩展展开表数据[不变价展开][原复式或者多倍计算]
 * - getAdjustExpand            根据原始号码扩展表数据，获取变价后的扩展号码扩展表数据
 * - compareAdjustedCodes       把这次变价和已往变价的做比较，如果在已往基础上还有变价的则往则这次变价的数据集里写入
 * - getSearchLocksCondition    根据玩法和购买的号码获取封锁表查询条件
 * - checkMaxLostByNo           检查按最大奖金查询有限制号码时根据不同奖金进行实际每个号码检查是否达到封锁
 * - getExpandCodeZX            直选展开[包括直选、直选和值、通选]算多倍号码
 * - checkCode                  投注号码检测[按相应玩法规则]
 * 
 * @author     james    090915
 * @version    1.2.0
 * @package    lowgame    
 */

class model_gameplay extends model_gamebase
{
	//私有变量
	private $_aAdjustedCodes  = array();     //已提示用户变过价的号码以及价格['123'=>7.7,'012'=>1700]
	private $_aAdjustingCodes = array();     //本次变价号码以及价格['123'=>7.7,'012'=>1700]
	
	                   
	
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    
    /**
     * 用户投单处理
     *
     * @author  james    090810
     * @access  public
     * @param   int      $iUserId       //用户ID
     * @param   int      $iLotteryId    //彩种ID
     * @param   int      $iMethodId     //玩法ID[比如直选和值]
     * @param   array    $aGameData     //投注信息
     * ---------------------------------------------------
     * @param   string   $aGameData['sNums']         //投注号码
     * @param   string   $aGameData['sIssue']        //投注当前期号
     * @param   int      $aGameData['iTotalNum']     //投注总注数
     * @param   int      $aGameData['iTotalAmount']  //投注总金额[追号总金额]
     * @param   boolean  $aGameData['bIsTrace']      //是否追号[TRUE：追号，FALSE：非追号]
     * @param   int      $aGameData['iTimes']        //非追号倍数
     * @param   array    $aGameData['aTraceIssue']   //追号详情
     * @param   boolean  $aGameData['bIsTraceStop']  //追号的时候是否追中停止
     * @return  mixed   //出错返回错误信息字符串，成功返回TRUE
     */
    public function gameBuy( $iUserId, $iLotteryId, $iMethodId, $aGameData=array() )
    {
    	/**
    	 * 01: 数据完整性检测=========================================================
    	 */
    	//echo date("Y-m-d H:i:s")."[start]\r\n";
        $iSinglePrice  = 2; //单注单倍投注金额
        $iAdjustChoice = 0; //是否需要变价提示[默认是需要]
        if( empty($aGameData) || !is_array($aGameData) )
        {//投注信息
        	return ajaxMsg("error", "操作错误");
        }
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户ID
            return ajaxMsg("error", "操作错误");
        }
        $iUserId = intval($iUserId);
        if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
        {//彩种ID
            return ajaxMsg("error", "操作错误");
        }
        $iLotteryId = intval($iLotteryId);
        if( empty($iMethodId) || !is_numeric($iMethodId) || $iMethodId <= 0 )
        {//玩法ID
            return ajaxMsg("error", "操作错误");
        }
        $iMethodId = intval($iMethodId);
        if( !isset($aGameData['sNums']) || $aGameData['sNums'] == "" )
        {//购买号码
            return ajaxMsg("error", "请选择投注号码");
        }
        $sNums = $aGameData['sNums'];
        if( empty($aGameData['iTotalNum']) || !is_numeric($aGameData['iTotalNum']) || $aGameData['iTotalNum'] <= 0 )
        {//购买注数
            return ajaxMsg("error", "操作错误");
        }
        $iTotalNum = intval($aGameData['iTotalNum']);
        if( FALSE === $this->checkCode( $iMethodId, $sNums, $iTotalNum ) )
        {//购买号码规则检测
        	return ajaxMsg("error", "投注号码错误");
        }
        if( empty($aGameData['sIssue']) )
        {//当期期号
            return ajaxMsg("error", "操作错误");
        }
        $sIssue = $aGameData['sIssue'];
        if( empty($aGameData['iTotalAmount']) || intval($aGameData['iTotalAmount']) <= 0 )
        {//购买总金额[追号总金额]
            return ajaxMsg("error", "操作错误");
        }
        $iTotalAmount = intval($aGameData['iTotalAmount']);
        if( !isset($aGameData['bIsTrace']) || $aGameData['bIsTrace'] == FALSE )
        {//非追号形式获取信息
        	$bIsTrace = FALSE;
            if( empty($aGameData['iTimes']) || !is_numeric($aGameData['iTimes']) || $aGameData['iTimes'] <= 0 )
            {//购买倍数
                return ajaxMsg("error", "请填写购买倍数");
            }
            $iTimes = intval($aGameData['iTimes']);
            if( $iTotalAmount != ($iTotalNum * $iTimes * $iSinglePrice) )
            {//总金额 = 总注数 * 倍数 * 单注单倍价格
            	return ajaxMsg("error", "操作错误");
            }
        }
        else 
        {//追号获取追号信息
        	$bIsTrace = TRUE;
            if( empty($aGameData['aTraceIssue']) || !is_array($aGameData['aTraceIssue']) )
            {//追号期号
                return ajaxMsg("error", "请选择追号期数");
            }
            $aTraceIssue = $aGameData['aTraceIssue'];
            if( $iTotalAmount != ($iTotalNum * (array_sum($aTraceIssue)) * $iSinglePrice) )
            {//总金额 = 总注数 * 总倍数 * 单注单倍价格
                return ajaxMsg("error", "操作错误");
            }
            if( !isset($aGameData['bIsTraceStop']) || $aGameData['bIsTraceStop'] == FALSE  )
            {//是否追中即止
            	$bIsTraceStop = FALSE;
            }
            else 
            {
            	$bIsTraceStop = TRUE;
            }
        }
        if( !empty($aGameData['aAdjustedCodes']) )
        {//如果有已变价号码提示，则把提示过的号码保存
        	try 
        	{
        		$this->_aAdjustedCodes = @unserialize( stripslashes_deep($aGameData['aAdjustedCodes']) );
        	}catch( Exception $e )
        	{
        		return ajaxMsg("error", "操作错误");
        	}
        	if( !is_array($this->_aAdjustedCodes) )
        	{
        		return ajaxMsg("error", "操作错误".$aGameData['aAdjustedCodes']);
        	}
        	if( empty($aGameData['iAdjustchoice']) || 
        	   ($aGameData['iAdjustchoice'] != 1 && $aGameData['iAdjustchoice'] != 2) )
        	{//变价选择，1：强买，2：再次提示
        		return ajaxMsg("error", "操作错误");
        	}
        	$iAdjustChoice = intval($aGameData['iAdjustchoice']);
        	
        }
        unset($aGameData);
        
        
        /**
         * 02: 判断玩法和奖金权限========================================================
         */
        //0201:读取玩法信息
        /* @var $oMethod model_method */
        $oMethod     = A::singleton('model_method');
        $sFields     = "m.`lotteryid`,m.`methodid`,m.`methodname`,m.`pid`,m.`isprizedynamic`,m.`locksid`,
                        m.`nocount`,m.`level`,m.`totalmoney`,l.`lockname`,l.`maxlost`,ltt.`cnname`,
                        ltt.`adjustminprofit`,ltt.`adjustmaxpercent`";
        $sCondition  = " m.`methodid`='".$iMethodId."' AND m.`lotteryid`='".$iLotteryId."' AND m.`isclose`='0' ";
        $sLeftJoin   = " LEFT JOIN `locksname` AS l ON m.`locksid`=l.`locksid`
                         LEFT JOIN `lottery` AS ltt ON m.`lotteryid`=ltt.`lotteryid` ";
        $aMethodData = $oMethod->methodGetInfo( $sFields, $sCondition, $sLeftJoin );
        if( empty($aMethodData) )
        {//无数据[玩法不存在或者已关闭]
            return ajaxMsg("error", "游戏已关闭");
        }
        $aMethodData            = $aMethodData[0];
        $aMethodData['nocount'] = unserialize($aMethodData['nocount']);
        //0202:读取奖金组信息以及用户返点和总代返点
        /* @var $oUserMethod model_usermethodset */
        $oUserMethod = A::singleton('model_usermethodset');
        $sFields     = "m.`methodid`,upl.`level`,upl.`prize`,ums.`userpoint`,upl.`userpoint` AS `topuserpoint`";
        $sCondition  = " AND m.`methodid`='".$aMethodData['pid']."' ";
        $aPrizeData  = $oUserMethod->getUserMethodPrize( $iUserId, $sFields, $sCondition, FALSE );
        if( empty($aPrizeData) )
        {//无奖金信息
            return ajaxMsg("error", "没有权限");
        }
        $aPrizeLevel = array();//奖金级别情况[1，2，3等奖]
        foreach( $aMethodData['nocount'] as $k=>$v )
        {
            if( isset($v['use']) && $v['use'] == 1 )
            {//使用了该奖级
                    $aPrizeLevel[] = intval($k);
            }
        }
        if( empty($aPrizeLevel) )
        {//没有奖金情况
        	return ajaxMsg("error", "没有权限");
        }
        sort($aPrizeLevel);
        foreach( $aPrizeData as $v )
        {
            if( $v['methodid'] == $aMethodData['pid'] && in_array($v['level'],$aPrizeLevel) )
            {//获取该玩法所拥有的奖金情况和返点
                $aMethodData['prize'][$v['level']] = $v['prize'];
            }
        }
        if( empty($aMethodData['prize']) )
        {//实际获取奖金失败
        	return ajaxMsg("error", "没有权限");
        }
        $aMethodData['userpoint']    = $aPrizeData[0]['userpoint'];     //用户返点[可能为0]
        $aMethodData['topuserpoint'] = $aPrizeData[0]['topuserpoint'];  //总代返点[可能为0]
        unset($aPrizeData);
        /**
         * 03: 获取单倍最高奖金和系统同单的最高奖金限制====================================
         */
        //$fMaxPrize    = $aMethodData['prize'][$aPrizeLevel[0]] ;//最大奖金，多奖级按一等奖赋值
        $fSysMaxPrize = intval(getConfigValue( 'limitbonus', 100000 )); //系统同单投注最高奖金限制
        
        
        /**
         * 04: 获取当前期信息=============================================================
         */
        $iNowTime   = time();     //设置当前时间，避免后面多次调用
        /* @var $oIssue model_issueinfo */
        $oIssue     = A::singleton('model_issueinfo');
        $sFields    = "A.`issueid`,A.`issue`,A.`salestart`,A.`saleend`,A.`dynamicprizestart`,A.`dynamicprizeend`";
        $sCondition = "A.`issue`='".daddslashes($sIssue)."' AND A.`lotteryid`='".$iLotteryId."'";
        $aIssueInfo = $oIssue->IssueGetOne( $sFields, $sCondition );
        if( empty($aIssueInfo) )
        {//0401: 判断当前期是否存在
            return ajaxMsg("error", "操作错误");
        }
        if( strtotime($aIssueInfo['saleend']) < $iNowTime )
        {//0402:检测是否已停止销售
            return ajaxMsg("error", "第[".$sIssue."]期已停止销售");
        }
        if( strtotime($aIssueInfo['salestart']) > $iNowTime )
        {//0403:检测是否还未开始销售
            return ajaxMsg("error", "第[".$sIssue."]期未到销售时间");
        }
        
        //如果是不支持动态调价并且又到了动态调价时间，则禁止购买
        if( $aMethodData['isprizedynamic'] == 0 && strtotime($aIssueInfo['dynamicprizestart']) < $iNowTime
                && strtotime($aIssueInfo['dynamicprizeend']) > $iNowTime )
        {
        	return ajaxMsg("error", "当期通选玩法销售已经截止，请于下期购买。通选销售时间为每日05：00-14：00");
        }
        
        $oConfig = new model_config();
        $iStartTime = strtotime($oConfig->getConfigs("stop_buy_start"));
        $iEndTime = strtotime($oConfig->getConfigs("stop_buy_end"));
        if ($iStartTime > 0 && $iEndTime > 0 && $iEndTime >= $iStartTime){
            $iNow = time();
            if ($iNow >= $iStartTime && $iNow <= $iEndTime){
                sysMsg( "未到销售时间", 2, $aLocation );
            }
        }
        
        /**
         * 05 : 获取所有上级返点并计算返点差=====================================================
         */
        //先判断是否为总代的直接下级
        /* @var $oUser model_user */
        $oUser      = A::singleton('model_user');
        $aUserPoint = array();
        $iTopProxyId= 0;    //总代ID初始化
        $iLvProxyId = 0;    //一代ID初始化
        if( TRUE === $oUser->isLvProxy( $iUserId ) )
        {//如果为直接下级则不用再获取上级返点
        	$iTopProxyId = $oUser->getTopProxyId( $iUserId );  //获取总代ID
        	$iLvProxyId  = $iUserId;                           //获取一代ID
        	if( empty($iTopProxyId) )
        	{
        		return ajaxMsg("error", "操作错误");
        	}
        	$aUserPoint[$iTopProxyId] = $aMethodData['topuserpoint'];
        	$aUserPoint[$iUserId]     = $aMethodData['userpoint'];
        }
        else
        {//获取上级返点
	        $aParentPoints = $oUserMethod->getParentPoint( $iUserId, $aMethodData['pid'] );
	        if( empty($aParentPoints) )
	        {
	            return ajaxMsg("error", "没有权限");
	        }
	        foreach( $aParentPoints as $v )
	        {
	        	if( $v['isclose'] == 1 )
	        	{//上级玩法关闭
	        		return ajaxMsg("error", "没有权限");
	        	}
	        	$aUserPoint[$v['userid']] = $v['userpoint'];
	        }
	        $iTopProxyId                  = $aParentPoints[0]['lvtopid'];  //获取总代ID
            $iLvProxyId                   = $aParentPoints[0]['lvproxyid'];  //获取一代ID
	        $aUserPoint[$iTopProxyId]     = $aMethodData['topuserpoint'];
            $aUserPoint[$iUserId]         = $aMethodData['userpoint'];
            unset($aParentPoints);
        }
        //ksort($aUserPoint); //按用户ID排序[总代->一代->.....->用户]
        $aUserDiffPoints = array(); //所有用户返点差记录
        $iLastId = 0;
        $iNowId  = 0;
        //获取用户的层级关系[总代->一代->.....->用户]
        $mTempUserLeverSet = $oUser->getParentId( $iUserId, TRUE );
        if( empty($mTempUserLeverSet) )
        {
        	return ajaxMsg("error", "没有权限");
        }
        $mTempUserLeverSet = explode( ",", $mTempUserLeverSet.",".$iUserId );
        for( $i=0; $i<count($mTempUserLeverSet); $i++ )
        {//计算返点差
        	if( $iLastId == 0 )
        	{
        		$iLastId = intval($mTempUserLeverSet[$i]); //用户ID
        	}
        	else
        	{
        		$iNowId               = intval($mTempUserLeverSet[$i]);
        		$aUserPoint[$iLastId] = isset($aUserPoint[$iLastId]) ? $aUserPoint[$iLastId] : 0;
        		$aUserPoint[$iNowId]  = isset($aUserPoint[$iNowId])  ? $aUserPoint[$iNowId]  : 0;
        		$aUserDiffPoints[$iLastId]['userid']    = $iLastId;
                $aUserDiffPoints[$iLastId]['diffpoint'] = round(floatval($aUserPoint[$iLastId] - $aUserPoint[$iNowId]),4);
                if( $aUserDiffPoints[$iLastId]['diffpoint'] < 0 )
                {//返点差出现负数，即返点设置有错误
                    return ajaxMsg("error", "操作错误");
                }
                $iLastId = $iNowId;
        	}
        }
        //最后一个用户即自身
        $aUserDiffPoints[$iLastId]['userid']    = $iLastId;
        $aUserDiffPoints[$iLastId]['diffpoint'] = $aUserPoint[$iLastId];
        unset( $iLastId, $iNowId, $aUserPoint, $mTempUserLeverSet );   //释放内存
        
        /**
         * 06: 根据是否为追号单做不同处理========================================
         */
        $fMaxLostMoney = $aMethodData['maxlost'];
        //06 00获取该彩种的调价方案，不变价时最大封锁为初始调价线[亏损值]
        $sSql = "SELECT `groupid`,`winline`,`loseline` FROM `adjustprice` 
                 WHERE `lotteryid`='".$iLotteryId."' AND `isverify`='1' AND `isactive`='1' LIMIT 1 ";
        $aAdjustprice = $this->oDB->getOne( $sSql );
        if( !empty($aAdjustprice) )
        {
        	$fMaxLostMoney = floatval($aAdjustprice['loseline']);
        }
        /* @var $oUserFund model_userfund */
        /* @var $oProjects model_projects */
        $oUserFund    = A::singleton('model_userfund'); //用户资金模型
        $oProjects    = A::singleton('model_projects'); //方案模型
        if( !isset($this->_aMethod_Config[$aMethodData['methodid']]) )
        {//没有相应玩法
        	return ajaxMsg("error", "玩法已关闭");
        }
        $sMethodeName = $this->_aMethod_Config[$aMethodData['methodid']]; //玩法对应的表达式
        if( $bIsTrace == FALSE )
        {//非追号
        	/**
        	 * 0600 ： 先购建基本的数据
        	 */
        	//0600 01：封锁数据 [根据具体情况变动]------------------------------
        	$aLocksData   = array(); //默认为空,根据情况填充
        	//0600 02: 方案表插入数据[固定] ------------------------
        	$aProjectData = array(
                               'userid'       => $iUserId,
                               'lotteryid'    => $aMethodData['lotteryid'],
                               'methodid'     => $aMethodData['methodid'],
                               'issue'        => $aIssueInfo['issue'],
                               'code'         => $sNums,
                               'singleprice'  => $iTotalNum * $iSinglePrice,
                               'multiple'     => $iTimes,
                               'totalprice'   => $iTotalAmount,
                               'lvtopid'      => $iTopProxyId,
                               'lvtoppoint'   => $aMethodData['topuserpoint'],
                               'lvproxyid'    => $iLvProxyId
                             );
            //0600 03: 加入游戏帐变插入数据[固定] --------------------------
            $aJoinData   = array(
                             'iLotteryId'   => $aMethodData['lotteryid'],
                             'iMethodId'    => $aMethodData['methodid'],
                             'iProjectId'   => 0,//等待方案插入ID
                             'iFromUserId'  => $iUserId,
                             'iOrderType'   => 3, //加入游戏帐变类型
                             'fMoney'       => $iTotalAmount,   //产生帐变金额为总投注金额
                             'sDescription' => '加入游戏',      //帐变描述
                             'iChannelID'   => SYS_CHANNELID
                             );
            //0600 04: 本人销售返点数据 [固定]-------------------------------
            $aBackData   = array(); //默认为空
            if( $aMethodData['userpoint'] > 0 )
            {//如果本人返点大于0则获取返点数据
                $aBackData = array( //销售返点帐变插入数据
                             'iLotteryId'   => $aMethodData['lotteryid'],
                             'iMethodId'    => $aMethodData['methodid'],
                             'iProjectId'   => 0,//等待方案插入ID
                             'iFromUserId'  => $iUserId,
                             'iOrderType'   => 4, //销售返点帐变类型
                             'fMoney'       => $iTotalAmount * $aMethodData['userpoint'], //投注总金额*返点
                             'sDescription' => '销售返点',//帐变描述
                             'iChannelID'   => SYS_CHANNELID
                            );
            }
            //0600 05: 号码扩展基本数据[变价时奖金做相应调整][不变价扩展表号码都写原复式]--------------------
            $aExpandData    = array();  //号码扩展数据
            $aTempArr       = $this->getExpandCode( $aMethodData['methodid'], $sNums );
            $fMaxTotalPrize = 0; //在此单中的最高奖金，默认为0
            $fMinTotalPrize = 0; //在此单中的最低奖金，默认为0
            foreach( $aTempArr as $k=>$v )//k:几倍，v:该倍的所有号码数组
            {//不同倍不同奖金
               foreach( $aMethodData['prize'] as $kk=>$vv )//kk:奖级，vv:奖金
               {//每个奖级同倍不同奖金
                   $aTemp = array();
                   $aTemp['projectid'] = 0; //等待方案表插入ID
                   $aTemp['isspecial'] = 0; //只有在动态调价才为1
                   $aTemp['level']     = $kk; //奖级
                   $aTemp['codetimes'] = $k * $iTimes;    //号码倍数*方案倍数
                   $aTemp['prize']     = floatval($vv) * $iTimes * intval($k); //原始奖金*方案倍数*号码倍数
                   $aTemp['expandcode']= implode("|",$v);//大小单双特殊形式以|分隔多单，每单内以#分隔号码
                   $fMaxTotalPrize     = $fMaxTotalPrize < $aTemp['prize'] ? $aTemp['prize'] : $fMaxTotalPrize;
                   $fMinTotalPrize     = ($fMinTotalPrize == 0 || $fMinTotalPrize > $aTemp['prize']) ?
                                            $aTemp['prize'] : $fMinTotalPrize;
                   $aExpandData[]      = $aTemp;
               }
            }
            if( empty($aExpandData) )
            {//扩展表数据出错
                return ajaxMsg("error", "操作错误");
            }
            //0600 06: 返点差表插入数据[固定] ---------------------------------
            $aDiffData = array();
            foreach( $aUserDiffPoints as $v )
            {
                if( $v['diffpoint'] > 0 )
                {//返点为0不加入返点表
                    $v['projectid']    = 0; //等待方案插入ID
                    $v['diffmoney']    = $iTotalAmount * $v['diffpoint']; //投注总金额*返点
                    $v['status']       = $iUserId == $v['userid'] ? 1 : 0;//自身返点改为已返
                    $aDiffData[]       = $v;
                }
            }
            //0600 07: 销量表数据[根据是否封锁做局部调整]    ---------------------------------------
            $aSaleData = array(
                        'issue'     => $aIssueInfo['issue'],
                        'lotteryid' => $aMethodData['lotteryid'],
                        'TFWLname'  => '', //默认不进入封锁，进入封锁则写封锁表名称
                        'moneys'    => $iTotalAmount * (1-$aMethodData['topuserpoint']) //全部价格 - 返点总金额
                         );
            //0600 08：获取用户相同单已拥有的最高奖金   ----------------------------------------------
            $aUserHadPrizes = $oProjects->getUserPrizesBySameCode( $iUserId, $aMethodData['lotteryid'], 
                                        $aMethodData['methodid'], $aIssueInfo['issue'], $sNums  );
            $fUserHadPrizes = 0; //用户同单已拥有的奖金
            $aTempUser_Prize= array();
            if( FALSE === $aUserHadPrizes )
            {//数据错误
            	return ajaxMsg("error", "操作错误");
            }
            elseif( !empty($aUserHadPrizes) )
            {//有相同号码的单[计算每单的最高奖金的和值]
            	foreach( $aUserHadPrizes as $v )
            	{
            		$aTempUser_Prize[$v['projectid']][] = $v['prize'];
            	}
            	foreach( $aTempUser_Prize as $v )
            	{
            		$fUserHadPrizes += max($v);
            	}
            }
            $fSysMaxPrize -= $fUserHadPrizes;   //这笔投单的最高奖金限制额=系统-已有的
            unset($aUserHadPrizes, $fUserHadPrizes, $aTempUser_Prize);
            //0600 09: 获取封锁表查询条件
            $sSearchCondition = $this->getSearchLocksCondition( $aMethodData['methodid'], $sNums );
            if( empty($sSearchCondition) )
            {//获取条件错误
            	return ajaxMsg("error", "操作错误");
            }
            
            //0601 10: 获取当前销售额
            /* @var $oLocks model_locks */
            $oLocks     = A::singleton('model_locks');
            $fSaleMoney = $oLocks->salesGetMoneys( $aMethodData['lotteryid'], $aIssueInfo['issue'], 
                                                   $aMethodData['lockname'] );
            if( $fSaleMoney === FALSE )
            {
                return ajaxMsg("error", "投注失败，请重试");
            }
            
        	//0601： 先判断是否为可变价,并且是否在变价时间内[做相应数据调整][通选不参与变价]
        	if( $aMethodData['isprizedynamic'] == 1 && strtotime($aIssueInfo['dynamicprizestart']) < $iNowTime
        	    && strtotime($aIssueInfo['dynamicprizeend']) > $iNowTime && $sMethodeName != 'TX' )
        	{//变价
        	   //0601 00: 获取开始变价的盈亏值
               if( !empty($aAdjustprice) )
               {//有调价方案
               	  $aTempAdjust   = array(); //变价数据集合
               	  $fMaxLostMoney = $aMethodData['maxlost']; //最大亏损调整为彩种最大亏损
               	  //0601 01: 构建查询达到奖金往上调的或者奖金往下调的号码列表的SQL[只获取下调的]
               	  $sSql     = " SELECT SUM(`prizes`) AS sumprizes,`code`,`stamp` FROM `".$aMethodData['lockname']."` 
	                            WHERE `issue`='".$aIssueInfo['issue']."' AND ".$sSearchCondition." 
	                            GROUP BY `code` 
	                            HAVING sumprizes>".floatval($fSaleMoney + $aAdjustprice['loseline']);
               	                //sumprizes<".floatval($fSaleMoney - $aAdjustprice['winline'])."
               	  $aTempAdjust['aCodes'] = $this->oDB->getAll($sSql);
               	  if( !empty($aTempAdjust['aCodes']) )
               	  {//如果有调价的号码
               	  	  //0601 02: 获取变价方案里的所有下调变价线[线从小到大排列]
               	  	  $sSql = " SELECT `uplimit`,`percent`,`isup` FROM `adjustprizedetail`
               	  	            WHERE `groupid`='".$aAdjustprice['groupid']."' AND `isup`='0' ORDER BY `uplimit` ASC ";
               	  	  $aTempAdjust['aDetail'] = $this->oDB->getAll($sSql);
               	  	  if( !empty($aTempAdjust['aDetail']) )
               	  	  {//如果有调价线
               	  	  	  //0601 03：扩展号码扩展表数据
               	  	  	  $aTempAdjust['totalmoney']       = $aMethodData['totalmoney'];//全包金额
               	  	  	  $aTempAdjust['topuserpoint']     = $aMethodData['topuserpoint'];//总代返点
               	  	  	  $aTempAdjust['adjustminprofit']  = $aMethodData['adjustminprofit'];//极限公司留水
               	  	  	  $aTempAdjust['adjustmaxpercent'] = $aMethodData['adjustmaxpercent'];//极限返奖率
               	  	  	  $aTempAdjust['loseline']         = $aAdjustprice['loseline'];//下调开始线
               	  	  	  $aTempAdjust['winline']          = $aAdjustprice['winline']; //上调开始线
               	  	  	  $aTempAdjust['fSaleMoney']       = $fSaleMoney;//当前销售量
               	  	  	  $aTempAdjust['fMoney']           = $aSaleData['moneys'];//本单价格
               	  	  	  $aTempAdjust['aPrizeCount']   = array(); //每个奖级对应的转直注数
               	  	  	  foreach( $aPrizeLevel as $ll )
               	  	  	  {
               	  	  	  	  $aTempAdjust['aPrizeCount'][$ll] = intval($aMethodData['nocount'][$ll]['count']);
               	  	  	  }
               	  	  	  $aTempAdjust['aPrizes'] = $aMethodData['prize'];    //奖级
               	  	  	  $mResult = $this->getAdjustExpand( $iMethodId, $aExpandData, $aTempAdjust );
               	  	  	  if( $mResult === -99 )
               	  	  	  {
               	  	  	  	  return ajaxMsg("error", "跨了两个调价线");
               	  	  	  }
               	  	  	  elseif( $mResult !== TRUE )
               	  	  	  {
               	  	  	  	  return ajaxMsg("error", "操作错误");
               	  	  	  }
               	  	  	  if( !empty($this->_aAdjustingCodes) && ($iAdjustChoice == 0 || $iAdjustChoice == 2) )
               	  	  	  {//如果有变价号码并且需要提示则返回提示
               	  	  	  	  $aTemp = array( "serialdata"  => serialize($this->_aAdjustingCodes),
                                              "codedata"    => $this->_aAdjustingCodes );
                              return ajaxMsg("adjust",$aTemp);
               	  	  	  }
               	  	  }
               	  }
               	  unset($aTempAdjust);   //释放内存
               }
        	}
    		//0603: 判断玩法是否有封锁，如果有做封锁处理，没有则跳过封锁
    		if( $aMethodData['locksid'] <= 0 || empty($aMethodData['lockname']) )
    		{//0603 01:不做封锁处理则封锁数据写入为空
    			$aLocksData   = array();
	    		//0602 01: 如果此单中的最高奖金超过最大奖金限额
	            if( $fMaxTotalPrize > $fSysMaxPrize )
	            {
	                return ajaxMsg("error", "第 ".$aIssueInfo['issue']."期  购买的号码超过奖金限额");
	            }
    		}
    		else
    		{//0603 02:要做封锁处理
    			$aLocksData            = array();
    			//0603 02 00: 预处理需要用到的数据
    			$aSaleData['TFWLname'] = $aMethodData['lockname'];   //销量表中封锁表名称
    			//091216 add 查询封锁表是否正确存在的是当期的
    			$sSql= "SELECT `issue` FROM `".$aMethodData['lockname']."` WHERE `issue`='".$aIssueInfo['issue']."'
    			        LIMIT 1";
    			$this->oDB->query( $sSql );
    			if( $this->oDB->ar() == 0 )
    			{//封锁表未生成，错误编号#3010
    				return ajaxMsg( "error", "系统错误，请联系管理员，错误编号#3010" );
    			}
    		    //0603 02 01: 构建封锁表更新数据[SQL语句]
                $aTempPrizeLevel = array();
                foreach( $aPrizeLevel as $v )
                {
                    $aTempPrizeLevel[] = $aMethodData['prize'][$v];
                }
                $iThreadId  = intval($this->oDB->getThreadId()) % 5;//五个线程
                //获取所有需要更新的条件和内容
                $aUpdateArr = $this->getUpdateLocksConditions( $aMethodData['methodid'], $aExpandData );
                if( empty($aUpdateArr) )
                {
                    return  ajaxMsg("error", "投注失败，请重试");
                }
                foreach( $aUpdateArr as $v )
                {//循环获取更新SQL语句
                	$fMaxTotalPrize = $fMaxTotalPrize < $v['prizes'] ? $v['prizes'] : $fMaxTotalPrize;
                    $fMinTotalPrize = ($fMinTotalPrize == 0 || $fMinTotalPrize > $v['prizes']) ?
                                      $v['prizes'] : $fMinTotalPrize;
                    $aLocksData[]   = "UPDATE `".$aMethodData['lockname']."` SET `prizes`=`prizes`+".$v['prizes']." 
                                       WHERE `issue`='".$aIssueInfo['issue']."' AND ".$v['condition']." 
                                       AND `threadid`='".$iThreadId."' ";
                }
                //0602 01: 如果此单中的最高奖金超过最大奖金限额
	    		if( $fMaxTotalPrize > $fSysMaxPrize )
	            {
	                return ajaxMsg("error", "第 ".$aIssueInfo['issue']."期  购买的号码超过奖金限额");
	            }
                $fSaleMoney += $aSaleData['moneys'];//销量要加上本单的金额
    			//0603 02 03 01: 构建SQL第一条语句[按最低奖金是否有超过封锁的号码，有则直接中断]
    			$sMinSql = " SELECT SUM(`prizes`) AS sumprizes,`code` FROM `".$aMethodData['lockname']."` 
    			             WHERE `issue`='".$aIssueInfo['issue']."' AND ".$sSearchCondition." 
    			             GROUP BY `code` 
    			             HAVING sumprizes>".( floatval($fMaxLostMoney)+$fSaleMoney-$fMinTotalPrize )." 
    			             LIMIT 1 ";
    		    //0603 02 03 02： 构建第二条语句[读出按最高奖金查询查询出会超过封锁的所有号码]
    		    if( $fMaxTotalPrize > $fMinTotalPrize )
    		    {//[如果最高奖金=最低奖金则跳过 第二条检测语句]
    		    	$sMaxSql = " SELECT SUM(`prizes`) AS sumprizes,`code`,`stamp` FROM `".$aMethodData['lockname']."` 
                                 WHERE `issue`='".$aIssueInfo['issue']."' AND ".$sSearchCondition." 
                                 GROUP BY `code` 
                                 HAVING sumprizes>".( floatval($fMaxLostMoney)+$fSaleMoney-$fMaxTotalPrize ) ;
    		    }
    		    $aResult = $this->oDB->getOne($sMinSql);
    		    if( !empty($aResult) )
    		    {//0603 02 04: 按最低奖金都有限号，则直接中断
    		    	$sMsg = "第 ".$aIssueInfo['issue']." 中 "." 号码 ".$aResult['code']." 已限号, 不能购买";
    		    	if( time() < strtotime($aIssueInfo['dynamicprizestart']))
    		    	{
    		    	    $aDynamicPrize = explode( " ", $aIssueInfo['dynamicprizestart'] );
    		    	    $sMsg .= "<br>请在". $aDynamicPrize[1]."点变价开始后再进行购买.";
    		    	}
    		    	return ajaxMsg("error", $sMsg);
    		    }
    		    else 
    		    {//0603 02 05: 按最低奖金没有限号的
    		    	if( $fMaxTotalPrize > $fMinTotalPrize && isset($sMaxSql) )
    		    	{//如果存在不同奖金则检查各个奖金的号码是否有限号的,如果有则中断，没有继续
    		    		$aResult = $this->oDB->getAll($sMaxSql);
    		    		if( !empty($aResult) )
    		    		{//如果存在按最高奖金购买会产生限号的数据，则根据实际每个号的奖金进行判断,否则直接跳过
    		    			$mResult = $this->checkMaxLostByNo( $aMethodData['methodid'], $aResult, 
    		    			                           $aExpandData, $fSaleMoney, $fMaxLostMoney );
    		    			if( FALSE === $mResult )
    		    			{//有错误产生
    		    				return ajaxMsg("error", "投注失败，请重试");
    		    			}
    		    			elseif( TRUE !== $mResult )
    		    			{//如果有限号，则返回限号
    		    				$sMsg = "第 ".$aIssueInfo['issue']." 中 "." 号码 ".$mResult." 已限号, 不能购买";
    		    				if( time() < strtotime($aIssueInfo['dynamicprizestart']))
    		    				{
    		    				    $aDynamicPrize = explode( " ", $aIssueInfo['dynamicprizestart'] );
    		    				    $sMsg .= "<br>请在". $aDynamicPrize[1]."点变价开始后再进行购买.";
    		    				}
    		    				return ajaxMsg("error", $sMsg);
    		    			}
    		    		}
    		    	}
    		    }
    		}
    		/**
    		 * 0604: 开始写入数据 ========================================================
    		 */
    		//0604 01: 锁用户资金表[开始锁资金事务处理]---------------
            if( FALSE == $this->oDB->doTransaction() )
            {//事务处理失败
                return ajaxMsg("error", "系统错误：错误编号:#5011");
            }
            if( intval($oUserFund->switchLock($iUserId, SYS_CHANNELID, TRUE)) != 1 )
            {
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                }
                return ajaxMsg("error", "你的资金帐户因为其他操作被锁定，请稍后重试");
            }
            if( FALSE == $this->oDB->doCommit() )
            {//事务提交失败
                return ajaxMsg("error", "系统错误：错误编号:#5013");
            }
            
            //0604 02: [开始投单流程事务处理]------------
            if( FALSE == $this->oDB->doTransaction() )
            {//事务处理失败
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "系统错误：错误编号:#5011");
            }
            $mResult = $this->playInsertData( $aLocksData, $aProjectData, $aJoinData, $aBackData, 
                                              $aExpandData, $aDiffData, $aSaleData );
            if( $mResult === -11 )
            {//封锁表未生成，错误编号#3010
            	if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                }
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "系统错误：错误编号:#3010");
            }
            elseif( $mResult === -33 )
            {//资金不够
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                }
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "余额不足");
            }
            elseif( $mResult !== TRUE )
            {
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                }
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "投注失败，请重试");
            }
            //0604 03: 提交投单流程事务处理[结束] -----------------------
            if( FALSE == $this->oDB->doCommit() )
            {//事务提交失败
                return ajaxMsg("error", "系统错误：错误编号:#5013");
            }
            //0604 04: 解锁资金表 -------------------------------------
            $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );
            return TRUE;
        }
        else 
        {//追号
        	/**
        	 * 0600：构建基本数据 =========================================================
        	 */
        	//0600 01: 判断是否要做封锁处理，减少后面多次运算
            $bIsLocks           = ($aMethodData['locksid'] <= 0 || empty($aMethodData['lockname'])) ? FALSE : TRUE;
        	//0600 02: 追号封锁表数据 [根据是否封锁变动][更新的SQL语句集合] --------------------
        	$aTraceLocks        = array();
        	//0600 03: 追号表数据 [固定] -----------------------------------------------------
        	$aTraceData = array(
        	                    'userid'          => $iUserId,
					        	'lotteryid'       => $aMethodData['lotteryid'],
					        	'methodid'        => $aMethodData['methodid'],
					        	'title'           => $aMethodData['cnname']." ".$aMethodData['methodname'].
					        	                     " 追号".count($aTraceIssue)."期",
					        	'codes'           => $sNums,  //追号号码
					        	'issuecount'      => count($aTraceIssue), //追号总期数
					        	'finishedcount'   => 0,   //已完成期数
					        	'singleprice'     => $iTotalNum * $iSinglePrice,  //每期的单倍价格
					        	'taskprice'       => $iTotalAmount,   //追号总金额
					        	'beginissue'      => "",  //开始期数，默认为空
					        	'prize'           => "",  //奖金[号码扩展表数据]序列化
					        	'userdiffpoints'  => "",  //返点[用户返点差表数据]序列化
        	                    'lvtopid'         => $iTopProxyId, //总代ID
                                'lvtoppoint'      => $aMethodData['topuserpoint'], //总代返点
                                'lvproxyid'       => $iLvProxyId,   //一代ID
					        	'stoponwin'       => $bIsTraceStop==TRUE ? 1 : 0  //是否追中停止
        	               );
        	//0600 04:每期号码扩展表基本数据[没有*方案倍数][固定] ----------------------------------------
        	$aTraceBasePrizes = array();
        	$aTempArr         = $this->getExpandCode( $aMethodData['methodid'], $sNums );
            $fMaxTotalPrize   = 0; //在此单中的最高奖金，默认为0[没有*方案倍数]
            $fMinTotalPrize   = 0; //在此单中的最低奖金，默认为0[没有*方案倍数]
            foreach( $aTempArr as $k=>$v )//k:几倍，v:该倍的所有号码数组
            {//不同倍不同奖金
               foreach( $aMethodData['prize'] as $kk=>$vv )//kk:奖级，vv:奖金
               {//每个奖级同倍不同奖金
                   $aTemp = array();
                   $aTemp['projectid'] = 0; //等待方案表插入ID
                   $aTemp['isspecial'] = 0; //只有在动态调价才为1
                   $aTemp['level']     = $kk;   //奖级
                   $aTemp['codetimes'] = $k;    //号码倍数
                   $aTemp['prize']     = floatval($vv) * intval($k); //原始奖金*号码倍数
                   $aTemp['expandcode']= implode( "|", $v );//大小单双特殊形式以|分隔多单，每单内以#分隔号码
                   $fMaxTotalPrize = $fMaxTotalPrize < $aTemp['prize'] ? $aTemp['prize'] : $fMaxTotalPrize;
                   $fMinTotalPrize = ($fMinTotalPrize == 0 || $fMinTotalPrize > $aTemp['prize']) ?
                                      $aTemp['prize'] : $fMinTotalPrize;
                   $aTraceBasePrizes[] = $aTemp;
               }
            }
            //0600 05:用户返点差表基本数据 [固定] -------------------------------------------------
            $aTraceBaseDiffPoints  = array();
            foreach( $aUserDiffPoints as $v )
            {
                if( $v['diffpoint'] > 0 )
                {//返点为0不加入返点表
                    $v['projectid']         = 0; //等待方案插入ID
                    $v['diffmoney']         = $aTraceData['singleprice'] * $v['diffpoint']; //后面*方案倍数
                    $v['status']            = $iUserId == $v['userid'] ? 1 : 0; //自身返点默认为已返
                    $aTraceBaseDiffPoints[] = $v;
                }
            }
        	//0600 06:追号详情表数据[固定] --------------------------------------------------------
        	$aTraceDetailData = array();    //追号详情表数据
        	$aTraceIssueNo    = array();    //追号的期数数据
        	foreach( $aTraceIssue as $k=>$v )
        	{
        		$aTraceDetailData[$k] = array(
	        		                   'taskid'    => 0,              //追号ID，默认为0，等待追号表生成
	        		                   'projectid' => 0,              //默认为0，生成当期时更新
	        		                   'multiple'  => intval($v),     //当期方案倍数
	        		                   'issue'     => $k,             //追号期数
	        		                   'status'    => 0               //状态默认为0，生成当期或者取消当期时更新
        		                   );
        		 $aTraceIssueNo[]     = $k;//期号
        	}
        	//0600 07: 销量表基本数据[没有*方案倍数] -----------------------------------------------------
        	$aTraceBaseSales = array(
						        	'issue'        => "",
						        	'lotteryid'    => $aMethodData['lotteryid'],
						        	'TFWLname'     => $bIsLocks ? $aMethodData['lockname'] : "",//封锁表名称
						        	'moneys'       => $aTraceData['singleprice'] * (1-$aMethodData['topuserpoint'])
        	                       );
        	//0600 08：追号帐变数据
        	$aTraceOrderData   = array(
	                             'iLotteryId'   => $aMethodData['lotteryid'],
	                             'iMethodId'    => $aMethodData['methodid'],
	                             'iTaskId'      => 0,//等待追号表插入ID
	                             'iFromUserId'  => $iUserId,
	                             'iOrderType'   => 6, //追号扣款帐变类型
	                             'fMoney'       => $iTotalAmount,   //产生帐变金额为总投注金额
	                             'sDescription' => '追号扣款',      //帐变描述
	                             'iChannelID'   => SYS_CHANNELID
                                );

            //0600 09：追号中生成当期的一些信息 ----------------------------------------------------------------
            $bIsHaveCurrentIssue = FALSE; //当前期是否在追号期中
            $aProjectData        = array(); //方案数据
            $aCreateData         = array(); //当期追号返款帐变
            $aJoinData           = array(); //加入游戏帐变 
            $aBackData           = array(); //本人销售返点帐变
            $aExpandData         = array(); //号码扩展表数据
            $aDiffData           = array(); //返点差表数据
        	
        	/*
        	 * 0601: 对追号的所有奖期进行处理 =========================================
        	 */
        	$sFields    = "i.`issueid`,i.`issue`,i.`salestart`,i.`saleend`";
            $sCondition = "i.`issue` IN(".daddslashes(implode(",",$aTraceIssueNo)).") 
                           AND i.`lotteryid`='".$iLotteryId."'";
            $aTempTrace = $oIssue->issueMutilTableGetList( $sFields, $sCondition );
            if( empty($aTempTrace) || count($aTempTrace) != count($aTraceIssueNo) )
            {//如果追号期中有错误的期号则返回并中断 ----------------------------------
            	return ajaxMsg("error", "追号错误");
            }
            //0601 00: 获取用户在以往的追号单中，相同号码在每期所拥有的最高奖金之和---------------
            /* @var $oTask model_task */
            $oTask            = A::singleton('model_task'); //追号模型
            $aTemp_PastPrizes = $oTask->getUserPrizeBySame( $iUserId, $aMethodData['lotteryid'], 
                                                     $aMethodData['methodid'], $aTraceIssueNo, $sNums );
            $aPastPrizes = array();//所有追号期的最高奖金和
            if( FALSE === $aTemp_PastPrizes )
            {//数据错误
                return ajaxMsg("error", "操作错误");
            }
            elseif( !empty($aTemp_PastPrizes) )
            {//如果存在相同号码的追号[计算每单的最高奖金的和值]
                foreach( $aTemp_PastPrizes as $v )
                {
                	$aTemp_ExpandArr = unserialize($v['prize']);
                	$aTemp_PastEach  =  array();//每期的所有奖金
                	$iTemp_PastTimes = 1;
                	if( empty($aTemp_ExpandArr[$v['issue']]) )
                	{
                		$aTemp_ExpandArr[$v['issue']] = $aTemp_ExpandArr['base'];
                		$iTemp_PastTimes              = intval($v['multiple']);
                	}
                	foreach( $aTemp_ExpandArr[$v['issue']] as $p )
                	{
                		$aTemp_PastEach[] = $p['prize'] * $iTemp_PastTimes;
                	}
                	$aPastPrizes[$v['issue']]  = isset($aPastPrizes[$v['issue']]) ? $aPastPrizes[$v['issue']] : 0;
                	$aPastPrizes[$v['issue']] += max($aTemp_PastEach);//每期中的最高奖金
                }
            }
            unset($aTemp_ExpandArr, $aTemp_PastEach, $iTemp_PastTimes);
            //0601 01: 获取所有期的销量值 --------------------------------------------------------
            $aTraceOldSales   = array();          //每期已有的销量数据
            /* @var $oLocks model_locks */
            $oLocks           = A::singleton('model_locks');
            $aTemp_PastPrizes = $oLocks->salesGetMoneys( $aMethodData['lotteryid'], $aTraceIssueNo, 
                                                             $aTraceBaseSales['TFWLname'] );
            if( empty($aTemp_PastPrizes) || count($aTemp_PastPrizes) != count($aTraceIssueNo) )
            {//如果销量表有错，或者有某期销量表未生成
            	return ajaxMsg("error", "系统错误：错误编号:#3011");
            }
            foreach( $aTemp_PastPrizes as $v )
            {
            	$aTraceOldSales[$v['issue']] = $v['salemoney'];
            }
            unset($aTemp_PastPrizes);
            //0601 02: 构造并初始化后面要用到的数据   ------------------------------------------
            $aTraceIssueNo         = array();     //重设追号的期数数据
            $aTracePrizes          = array();     //号码扩展表数据
            $aTraceDiffPoints      = array();     //返点差表数据
            $aTraceSales           = array();     //销量表数据
            $bIsGetFutureCondition = FALSE;       //是否已计算未来追好期每期更新的基本条件语句，防止多次计算
            $sSearchCondition = $this->getSearchLocksCondition( $aMethodData['methodid'], $sNums );//获取封锁表查询条件
            if( empty($sSearchCondition) )
            {//获取条件错误
                return ajaxMsg("error", "操作错误");
            }
        	//0601 03: 对所有追号奖期进行循环判断处理
        	foreach( $aTempTrace as $aTrace )
        	{
	        	if( strtotime($aTrace['saleend']) < $iNowTime )
		        {//0601 03 00:检测是否已停止销售
		            return ajaxMsg("error", "第[".$aTrace['issue']."]期已停止销售");
		        }
        		$aTraceIssueNo[$aTrace['issueid']] = $aTrace['issue'];
        		//0601 03 01:获取本期方案倍数
        		$iTemp_Times = isset($aTraceDetailData[$aTrace['issue']]) ?
        		               intval($aTraceDetailData[$aTrace['issue']]['multiple']) : 0;
        		if( $iTemp_Times <= 0 )
        		{
        			return ajaxMsg("error", "追号中第[".$aTrace['issue']."]期倍数错误");
        		}
        		//0601 03 02: 获取本期的号码扩展表数据
                $aTracePrizes[$aTrace['issue']] = $aTraceBasePrizes;
                foreach( $aTracePrizes[$aTrace['issue']] as & $vm )
                {//奖金依次*本期方案倍数
                    $vm['prize'] = $vm['prize'] * $iTemp_Times;
                }
                //0601 03 03: 获取本期的返点差表数据
                $aTraceDiffPoints[$aTrace['issue']] = $aTraceBaseDiffPoints;
                foreach( $aTraceDiffPoints[$aTrace['issue']] as & $vl )
                {//返点依次*本期方案倍数
                    $vl['diffmoney'] = $vl['diffmoney'] * $iTemp_Times;
                }
                //0601 03 04: 本期销量表数据
                $aTraceSales[$aTrace['issue']]           = $aTraceBaseSales;
                $aTraceSales[$aTrace['issue']]['issue']  = $aTrace['issue'];
                $aTraceSales[$aTrace['issue']]['moneys'] = $aTraceSales[$aTrace['issue']]['moneys'] * $iTemp_Times;
                
        		//0601 03 05: 如果为当前期做当前期特别处理 ---------------------------
        		if( $aTrace['issueid'] == $aIssueInfo['issueid'] )
        		{
        			//0601 03 05 00: 设置当前期生成注单的一些信息
        			$bIsHaveCurrentIssue = TRUE; //包含当前期的追号为TRUE
        			$aProjectData = array(    //方案表数据
       			                           'userid'       => $iUserId,
       			                           'taskid'       => 0, //等待追号表插入ID
			                               'lotteryid'    => $aMethodData['lotteryid'],
			                               'methodid'     => $aMethodData['methodid'],
			                               'issue'        => $aTrace['issue'],
			                               'code'         => $sNums,
			                               'singleprice'  => $aTraceData['singleprice'],
			                               'multiple'     => $iTemp_Times,
			                               'totalprice'   => $aTraceData['singleprice'] * $iTemp_Times,
			                               'lvtopid'      => $iTopProxyId,
			                               'lvtoppoint'   => $aMethodData['topuserpoint'],
			                               'lvproxyid'    => $iLvProxyId
        			                         );
        		    
        			$aCreateData  = array( //当期追号返款帐变
       			                           'iLotteryId'   => $aMethodData['lotteryid'],
			                               'iMethodId'    => $aMethodData['methodid'],
			                               'iTaskId'      => 0,//等待追号表插入ID
			                               'iFromUserId'  => $iUserId,
			                               'iOrderType'   => 7, //追号扣款帐变类型
			                               'fMoney'       => $aProjectData['totalprice'],
			                               'sDescription' => '当期追号返款',      //帐变描述
			                               'iChannelID'   => SYS_CHANNELID
        			                         );
        			$aJoinData    = array(//加入游戏帐变 
       			                           'iLotteryId'   => $aMethodData['lotteryid'],
				                           'iMethodId'    => $aMethodData['methodid'],
				                           'iProjectId'   => 0,//等待方案插入ID
       			                           'iTaskId'      => 0,//等待追号表插入ID
				                           'iFromUserId'  => $iUserId,
				                           'iOrderType'   => 3, //加入游戏帐变类型
				                           'fMoney'       => $aProjectData['totalprice'],
				                           'sDescription' => '加入游戏',      //帐变描述
				                           'iChannelID'   => SYS_CHANNELID
        			                         );
        			if( $aMethodData['userpoint'] > 0 )
        			{//返点大于0才写入
        				$aBackData = array(//本人销售返点帐变
                                           'iLotteryId'   => $aMethodData['lotteryid'],
                                           'iMethodId'    => $aMethodData['methodid'],
                                           'iProjectId'   => 0,//等待方案插入ID
                                           'iTaskId'      => 0,//等待追号表插入ID
                                           'iFromUserId'  => $iUserId,
                                           'iOrderType'   => 4, //销售返点帐变类型
                                           'fMoney'       => $aProjectData['totalprice'] * $aMethodData['userpoint'],
                                           'sDescription' => '销售返点',//帐变描述
                                           'iChannelID'   => SYS_CHANNELID
                                             );
        			}
        			$aDiffData    = $aTraceDiffPoints[$aTrace['issue']]; //返点差表数据
        			
        			
	        		//0601 03 05 01：获取用户相同单在当前期已拥有的最高奖金   ------------------------------------
		            $aUserHadPrizes = $oProjects->getUserPrizesBySameCode( $iUserId, $aMethodData['lotteryid'], 
		                                        $aMethodData['methodid'], $aIssueInfo['issue'], $sNums  );
		            $fUserHadPrizes = 0; //用户同单已拥有的奖金
		            $aTempUser_Prize= array();
		            if( FALSE === $aUserHadPrizes )
		            {//数据错误
		                return ajaxMsg("error", "操作错误");
		            }
		            elseif( !empty($aUserHadPrizes) )
		            {//有相同号码的单[计算每单的最高奖金的和值]
		                foreach( $aUserHadPrizes as $v )
		                {
		                    $aTempUser_Prize[$v['projectid']][] = $v['prize'];
		                }
		                foreach( $aTempUser_Prize as $v )
		                {
		                    $fUserHadPrizes += max($v);
		                }
		            }
		            unset($aUserHadPrizes,$aTempUser_Prize);
		            $fCurrentMaxLostMoney = $fMaxLostMoney;
		            //0601 03 05 02: 根据是否变价做不同处理[通选不参与变价]
		            if( $aMethodData['isprizedynamic'] == 1 && strtotime($aIssueInfo['dynamicprizestart']) < $iNowTime
                        && strtotime($aIssueInfo['dynamicprizeend']) > $iNowTime && $sMethodeName != 'TX'  )
		            {//变价
		               //0601 03 05 02 00: 获取开始变价的盈亏值[前面已获取]
		               if( !empty($aAdjustprice) )
		               {//有调价方案
		               	  $fCurrentMaxLostMoney = $aMethodData['maxlost'];   //如果有变价则把最大亏损调整到彩种最大亏损
		               	  $aTempAdjust  = array(); //变价数据集合
		                  //0601 03 05 02 01: 构建查询达到奖金往上调的或者奖金往下调的号码列表的SQL[只获取下调的]
		                  $sSql     = " SELECT SUM(`prizes`) AS sumprizes,`code`,`stamp`
		                                FROM `".$aMethodData['lockname']."` 
		                                WHERE `issue`='".$aTrace['issue']."' AND ".$sSearchCondition." 
		                                GROUP BY `code` HAVING sumprizes>".
		                                floatval($aTraceOldSales[$aTrace['issue']] + $aAdjustprice['loseline']);
		                                //sumprizes<".floatval($fSaleMoney - $aAdjustprice['winline'])."
		                  $aTempAdjust['aCodes'] = $this->oDB->getAll($sSql);
		                  if( !empty($aTempAdjust['aCodes']) )
		                  {//如果有调价的号码
		                      //0601 03 05 02 02: 获取变价方案里的所有下调变价线[线从小到大排列]
		                      $sSql = " SELECT `uplimit`,`percent`,`isup` FROM `adjustprizedetail`
		                                WHERE `groupid`='".$aAdjustprice['groupid']."' AND `isup`='0' 
		                                ORDER BY `uplimit` ASC ";
		                      $aTempAdjust['aDetail'] = $this->oDB->getAll($sSql);
		                      if( !empty($aTempAdjust['aDetail']) )
		                      {//如果有调价线
		                          //0601 03 05 02 03：扩展号码扩展表数据
		                          $aTempAdjust['totalmoney']       = $aMethodData['totalmoney'];//全包金额
		                          $aTempAdjust['topuserpoint']     = $aMethodData['topuserpoint'];//总代返点
		                          $aTempAdjust['adjustminprofit']  = $aMethodData['adjustminprofit'];//极限公司留水
		                          $aTempAdjust['adjustmaxpercent'] = $aMethodData['adjustmaxpercent'];//极限返奖率
		                          $aTempAdjust['loseline']         = $aAdjustprice['loseline'];//下调开始线
		                          $aTempAdjust['winline']          = $aAdjustprice['winline']; //上调开始线
		                          $aTempAdjust['fSaleMoney']       = $aTraceOldSales[$aTrace['issue']];//当前销售量
		                          $aTempAdjust['fMoney']           = $aTraceSales[$aTrace['issue']]['moneys'];//本单价格
		                          $aTempAdjust['aPrizeCount']   = array(); //每个奖级对应的转直注数
		                          foreach( $aPrizeLevel as $ll )
		                          {
		                              $aTempAdjust['aPrizeCount'][$ll] = intval($aMethodData['nocount'][$ll]['count']);
		                          }
		                          $aTempAdjust['aPrizes'] = $aMethodData['prize'];    //奖级
		                          $mResult = $this->getAdjustExpand( $iMethodId, $aTracePrizes[$aTrace['issue']], 
		                                                              $aTempAdjust );
		                          if( $mResult === -99 )
		                          {
		                              return ajaxMsg("error", "跨了两个调价线");
		                          }
		                          elseif( $mResult !== TRUE )
		                          {
		                              return ajaxMsg("error", "操作错误");
		                          }
			                      if( !empty($this->_aAdjustingCodes) && ($iAdjustChoice == 0 || $iAdjustChoice == 2) )
		                          {//如果有变价号码并且需要提示则返回提示
		                          	  $aTemp = array("serialdata"  => serialize($this->_aAdjustingCodes),
		                          	                 "codedata"    => $this->_aAdjustingCodes);
		                              return ajaxMsg("adjust",$aTemp);
		                          }
		                      }
		                  }
		                  unset($aTempAdjust);   //释放内存
		               }
		            }
		            $aExpandData  = $aTracePrizes[$aTrace['issue']]; //号码扩展表数据
		            //0602: 计算此单中的最高奖金和最低奖金
		            $fCurrentMaxTotalPrize = $fMaxTotalPrize * $iTemp_Times;
		            $fCurrentMinTotalPrize = $fMinTotalPrize * $iTemp_Times;
		            if( $fCurrentMaxTotalPrize > ($fSysMaxPrize - $fUserHadPrizes) )
	                {//0601 03 05 03: 如果此单中的最高奖金超过最大奖金限额
	                    return ajaxMsg("error", "第 ".$aTrace['issue']."期  购买的号码超过奖金限额");
	                }
	                //0601 03 05 04: 需要做封锁则判断封锁,进入封锁表[不封锁则不判断也不进入封锁表]
	                if( $bIsLocks == TRUE )
	                {
	                    //091216 add 查询封锁表是否正确存在的是当期的
		                $sSql= "SELECT `issue` FROM `".$aMethodData['lockname']."` 
		                        WHERE `issue`='".$aTrace['issue']."' LIMIT 1";
		                $this->oDB->query( $sSql );
		                if( $this->oDB->ar() == 0 )
		                {//封锁表未生成，错误编号#3010
		                    return ajaxMsg( "error", "系统错误，请联系管理员，错误编号#3010" );
		                }
		                //0601 03 05 05 00: 构建当期封锁表更新数据[SQL语句]
                        $aTempPrizeLevel = array();
                        foreach( $aPrizeLevel as $v )
                        {
                            $aTempPrizeLevel[] = $aMethodData['prize'][$v];
                        }
                        $iThreadId  = intval($this->oDB->getThreadId()) % 5;//五个线程
                        //0601 03 05 05 01:获取所有需要更新的条件和内容
                        $aUpdateArr = $this->getUpdateLocksConditions( $aMethodData['methodid'], 
                                                                       $aTracePrizes[$aTrace['issue']] );
                        if( empty($aUpdateArr) )
                        {
                            return  ajaxMsg("error", "投注失败，请重试");
                        }
                        //0601 03 05 05 02:循环获取更新SQL语句
                        foreach( $aUpdateArr as $v )
                        {
                        	$fCurrentMaxTotalPrize = $fCurrentMaxTotalPrize < $v['prizes'] ? $v['prizes'] : 
                        	                         $fCurrentMaxTotalPrize;
                            $fCurrentMinTotalPrize = ($fCurrentMinTotalPrize==0 || $fCurrentMinTotalPrize>$v['prizes']) ?
                                                     $v['prizes'] : $fCurrentMinTotalPrize;
                            $aTraceLocks[]= " UPDATE `".$aMethodData['lockname']."` 
                                              SET `prizes`=`prizes`+".$v['prizes']." 
                                              WHERE `issue`='".$aTrace['issue']."' AND ".$v['condition']." 
                                              AND `threadid`='".$iThreadId."' ";
                        }
		                if( $fCurrentMaxTotalPrize > ($fSysMaxPrize - $fUserHadPrizes) )
	                    {//0601 03 05 03: 如果此单中的最高奖金超过最大奖金限额
	                        return ajaxMsg("error", "第 ".$aTrace['issue']."期  购买的号码超过奖金限额");
	                    }
                        //销量要加上本单的金额
                        $fSaleMoney    = $aTraceOldSales[$aTrace['issue']]+$aTraceSales[$aTrace['issue']]['moneys'];
                        //已存在的最高封锁为：公司封锁+销量-本单最小奖金
                        $fTemp_MinLost = ( floatval($fCurrentMaxLostMoney)+ $fSaleMoney - $fCurrentMinTotalPrize );
                        //已存在的最低封锁为：公司封锁+销量-本单最大奖金
                        $fTemp_MaxLost = ( floatval($fCurrentMaxLostMoney) + $fSaleMoney - $fCurrentMaxTotalPrize );
                        //0601 03 05 05 03:构建SQL第一条语句[按最低奖金是否有超过封锁的号码，有则直接中断]
                        $sMinSql = " SELECT SUM(`prizes`) AS sumprizes,`code` FROM `".$aMethodData['lockname']."` 
                                   WHERE `issue`='".$aTrace['issue']."' AND ".$sSearchCondition." 
                                   GROUP BY `code` HAVING sumprizes>".$fTemp_MinLost." LIMIT 1 ";
                        //0601 03 05 05 04:构建第二条语句[读出按最高奖金查询查询出会超过封锁的所有号码]
                        if( $fCurrentMaxTotalPrize > $fCurrentMinTotalPrize )
                        {//[如果最高奖金=最低奖金则跳过 第二条检测语句]
                            $sMaxSql=" SELECT SUM(`prizes`) AS sumprizes,`code` FROM `".$aMethodData['lockname']."` 
                                       WHERE `issue`='".$aTrace['issue']."' AND ".$sSearchCondition." 
                                       GROUP BY `code` HAVING sumprizes>".$fTemp_MaxLost ;
                        }
                        $aResult = $this->oDB->getOne($sMinSql);
                        if( !empty($aResult) )
                        {//0601 03 05 05 05:按最低奖金都有限号，则直接中断
                        	$sMsg = "第 ".$aTrace['issue']." 中 "." 号码 ".$aResult['code']." 已限号, 不能购买";
                        	if( time() < strtotime($aIssueInfo['dynamicprizestart']))
                        	{
                        	    $aDynamicPrize = explode( " ", $aIssueInfo['dynamicprizestart'] );
                        	    $sMsg .= "<br>请在". $aDynamicPrize[1]."点变价开始后再进行购买.";
                        	}
                            return ajaxMsg("error", $sMsg);
                        }
                        else 
                        {//0601 03 05 05 06:按最低奖金没有限号的
                            if( $fCurrentMaxTotalPrize > $fCurrentMinTotalPrize && isset($sMaxSql) )
                            {//如果存在不同奖金则检查各个奖金的号码是否有限号的,如果有则中断，没有继续
                                $aResult = $this->oDB->getAll($sMaxSql);
                                if( !empty($aResult) )
                                {//如果存在按最高奖金购买会产生限号的数据，则根据实际每个号的奖金进行判断,否则直接跳过
                                    $mResult = $this->checkMaxLostByNo( $aMethodData['methodid'], $aResult, 
                                                  $aTracePrizes[$aTrace['issue']], $fSaleMoney, $fCurrentMaxLostMoney );
                                    if( FALSE === $mResult )
                                    {//有错误产生
                                        return ajaxMsg("error", "投注失败，请重试");
                                    }
                                    elseif( TRUE !== $mResult )
                                    {//如果有限号，则返回限号
                                    	$sMsg = "第 ".$aTrace['issue']." 中 "." 号码 ".$mResult." 已限号, 不能购买";
                                    	if( time() < strtotime($aIssueInfo['dynamicprizestart']))
                                    	{
                                    	    $aDynamicPrize = explode( " ", $aIssueInfo['dynamicprizestart'] );
                                    	    $sMsg .= "<br>请在". $aDynamicPrize[1]."点变价开始后再进行购买.";
                                    	}
                                        return ajaxMsg("error", $sMsg);
                                    }
                                }
                            }
                        }
	                }
		            //本期结束
        		}
        		else 
        		{//0601 03 06: 如果不为当前期做普通处理[都没有变价]
        			//0602: 计算此单中的最高奖金和最低奖金
                    $fCurrentMaxTotalPrize = $fMaxTotalPrize * $iTemp_Times;
                    $fCurrentMinTotalPrize = $fMinTotalPrize * $iTemp_Times;
                    //0601 03 03 03: 本期是否超过奖金限额
                    $aPastPrizes[$aTrace['issue']] = isset($aPastPrizes[$aTrace['issue']]) ? 
                                                     $aPastPrizes[$aTrace['issue']] : 0;
                    if( $fCurrentMaxTotalPrize > ($fSysMaxPrize - $aPastPrizes[$aTrace['issue']]) )
                    {//如果本期追号的所有奖金和大于系统设置的最高奖金限额则返回并退出
                    	return ajaxMsg("error", "第 ".$aTrace['issue']."期  超过奖金限额");
                    }
                    //0601 03 03 04: 需要做封锁则判断封锁,进入封锁表[不封锁则不判断也不进入封锁表]
	        		if( $bIsLocks == TRUE )
		            {
		                //091216 add 查询封锁表是否正确存在的是当期的
                        $sSql= "SELECT `issue` FROM `".$aMethodData['lockname']."future` 
                                WHERE `issue`='".$aTrace['issue']."' LIMIT 1";
                        $this->oDB->query( $sSql );
                        if( $this->oDB->ar() == 0 )
                        {//封锁表未生成，错误编号#3010
                            return ajaxMsg( "error", "系统错误，请联系管理员，错误编号#3010" );
                        }
			            //0601 03 03 04 00: 构建本期封锁表更新数据[SQL语句]
	                    $aTempPrizeLevel = array();
	                    foreach( $aPrizeLevel as $v )
	                    {
	                        $aTempPrizeLevel[] = $aMethodData['prize'][$v];
	                    }
	                    $iThreadId  = intval($this->oDB->getThreadId()) % 5;//五个线程
	                    //0601 03 03 04 01:获取所有需要更新的条件和内容
	                    if( $bIsGetFutureCondition == FALSE )
	                    {//只获取一次后面每次都是*不同方案倍数
	                    	$aFutureUpdateArr = $this->getUpdateLocksConditions( $aMethodData['methodid'], 
                                                                             $aTraceBasePrizes );
                            $bIsGetFutureCondition = TRUE;
	                    }
	                    if( empty($aFutureUpdateArr) )
	                    {
	                        return  ajaxMsg("error", "投注失败，请重试");
	                    }
	                    //0601 03 03 04 02:循环获取更新SQL语句[封锁表为追号封锁表future结尾的]
	                    foreach( $aFutureUpdateArr as $v )
	                    {
		                    //获取未来追号期中的每期的最高和最低奖金
		                    $fCurrentMaxTotalPrize = $fCurrentMaxTotalPrize < ($v['prizes']*$iTemp_Times) ? 
		                                               ($v['prizes'] * $iTemp_Times) : $fCurrentMaxTotalPrize;
                            $fCurrentMinTotalPrize = ($fCurrentMinTotalPrize==0 || 
                                                      $fCurrentMinTotalPrize>($v['prizes']*$iTemp_Times)) ? 
                                                      ($v['prizes']*$iTemp_Times) : $fCurrentMinTotalPrize;
	                        $aTraceLocks[]= " UPDATE `".$aMethodData['lockname']."future` 
	                                          SET `prizes`=`prizes`+".($v['prizes']*$iTemp_Times)." 
	                                          WHERE `issue`='".$aTrace['issue']."' AND ".$v['condition']." 
	                                        AND `threadid`='".$iThreadId."' ";
	                    }
			            if( $fCurrentMaxTotalPrize > ($fSysMaxPrize - $aPastPrizes[$aTrace['issue']]) )
	                    {//如果本期追号的所有奖金和大于系统设置的最高奖金限额则返回并退出
	                        return ajaxMsg("error", "第 ".$aTrace['issue']."期  超过奖金限额");
	                    }
	                    //销量要加上本单的金额
	                    $fSaleMoney    = $aTraceOldSales[$aTrace['issue']]+ $aTraceSales[$aTrace['issue']]['moneys'];
	                    //已存在的最高封锁为：公司封锁+销量-本单最小奖金
	                    $fTemp_MinLost = ( floatval($fMaxLostMoney)+ $fSaleMoney - $fCurrentMinTotalPrize );
                        //已存在的最低封锁为：公司封锁+当前销售额+本单销售额-本单最大奖金
                        $fTemp_MaxLost = ( floatval($fMaxLostMoney) + $fSaleMoney - $fCurrentMaxTotalPrize );
			            //0601 03 03 04 03:构建SQL第一条语句[按最低奖金是否有超过封锁的号码，有则直接中断]
	                    $sMinSql = " SELECT SUM(`prizes`) AS sumprizes,`code` FROM `".$aMethodData['lockname']."future` 
	                                 WHERE `issue`='".$aTrace['issue']."' AND ".$sSearchCondition." 
	                                 GROUP BY `code` HAVING sumprizes>".$fTemp_MinLost." LIMIT 1 ";
	                    //0601 03 03 04 04:构建第二条语句[读出按最高奖金查询查询出会超过封锁的所有号码]
	                    if( $fCurrentMaxTotalPrize > $fCurrentMinTotalPrize )
	                    {//[如果最高奖金=最低奖金则跳过 第二条检测语句]
	                        $sMaxSql = " SELECT SUM(`prizes`) AS sumprizes,`code`,`stamp` 
	                                     FROM `".$aMethodData['lockname']."future` 
	                                     WHERE `issue`='".$aTrace['issue']."' AND ".$sSearchCondition." 
	                                     GROUP BY `code` HAVING sumprizes>".$fTemp_MaxLost ;
	                    }
	                    $aResult = $this->oDB->getOne($sMinSql);
	                    if( !empty($aResult) )
	                    {//0601 03 03 04 05:按最低奖金都有限号，则直接中断
	                    	$sMsg = "第 ".$aTrace['issue']." 中 "." 号码 ".$aResult['code']." 已限号, 不能购买";
	                    	if( time() < strtotime($aIssueInfo['dynamicprizestart']))
	                    	{
	                    	    $aDynamicPrize = explode( " ", $aIssueInfo['dynamicprizestart'] );
	                    	    $sMsg .= "<br>请在". $aDynamicPrize[1]."点变价开始后再进行购买.";
	                    	}
	                        return ajaxMsg("error", $sMsg);
	                    }
	                    else 
	                    {//0601 03 03 04 06:: 按最低奖金没有限号的
	                        if( $fCurrentMaxTotalPrize > $fCurrentMinTotalPrize && isset($sMaxSql) )
	                        {//如果存在不同奖金则检查各个奖金的号码是否有限号的,如果有则中断，没有继续
	                            $aResult = $this->oDB->getAll($sMaxSql);
	                            if( !empty($aResult) )
	                            {//如果存在按最高奖金购买会产生限号的数据，则根据实际每个号的奖金进行判断,否则直接跳过
	                                $mResult = $this->checkMaxLostByNo( $aMethodData['methodid'], $aResult, 
	                                            $aTracePrizes[$aTrace['issue']], $fSaleMoney, $fMaxLostMoney );
	                                if( FALSE === $mResult )
	                                {//有错误产生
	                                    return ajaxMsg("error", "投注失败，请重试");
	                                }
	                                elseif( TRUE !== $mResult )
	                                {//如果有限号，则返回限号
	                                	$sMsg = "第 ".$aTrace['issue']." 中 "." 号码 ".$mResult." 已限号, 不能购买";
	                                	if( time() < strtotime($aIssueInfo['dynamicprizestart']))
	                                	{
	                                	    $aDynamicPrize = explode( " ", $aIssueInfo['dynamicprizestart'] );
	                                	    $sMsg .= "<br>请在". $aDynamicPrize[1]."点变价开始后再进行购买.";
	                                	}
	                                    return ajaxMsg("error", $sMsg);
	                                }
	                            }
	                        }
	                    }
		            }//$bIsLocks over
		            $aTracePrizes[$aTrace['issue']]     = "";
        		}
        		$aTraceDiffPoints[$aTrace['issue']] = "";
        		//data over
        	}
        	sort($aTraceIssueNo);
        	$aTracePrizes['base']         = $aTraceBasePrizes; //号码扩展表基本数据[防止数据过长]
        	$aTraceDiffPoints['base']     = $aTraceBaseDiffPoints; //返点差基本数据表[防止数据过长]
            $aTraceData['beginissue']     = $aTraceIssueNo[0]; //开始期数为issue ID 最小的那个
            $aTraceData['prize']          = serialize($aTracePrizes);     //号码扩展表序列化
            $aTraceData['userdiffpoints'] = serialize($aTraceDiffPoints); //返点差表序列化
        	/**
        	 * 0602: 开始写入数据    ===================================================
        	 */
        	//0602 00: 组合数据
        	$aDataArr = array(
        	               'aTraceLocks'       => $aTraceLocks, 
        	               'aTraceData'        => $aTraceData, 
        	               'aTraceOrderData'   => $aTraceOrderData, 
                           'aTraceDetailData'  => $aTraceDetailData, 
                           'aTraceSales'       => $aTraceSales,
        	               'bIsCurrentIssue'   => $bIsHaveCurrentIssue,
        	               'aProjectData'      => $aProjectData,
        	               'aCreateData'       => $aCreateData,
        	               'aJoinData'         => $aJoinData,
        	               'aBackData'         => $aBackData,
        	               'aExpandData'       => $aExpandData,
        	               'aDiffData'         => $aDiffData
        	            );
            //0602 01: 锁用户资金表[开始锁资金事务处理]---------------
            if( FALSE == $this->oDB->doTransaction() )
            {//事务处理失败
                return ajaxMsg("error", "系统错误：错误编号:#5011");
            }
            if( intval($oUserFund->switchLock($iUserId, SYS_CHANNELID, TRUE)) != 1 )
            {
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                }
                return ajaxMsg("error", "你的资金帐户因为其他操作被锁定，请稍后重试");
            }
            if( FALSE == $this->oDB->doCommit() )
            {//事务提交失败
                return ajaxMsg("error", "系统错误：错误编号:#5013");
            }
            
            //0602 02: [开始追号流程事务处理]------------
            if( FALSE == $this->oDB->doTransaction() )
            {//事务处理失败
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "系统错误：错误编号:#5011");
            }
            $mResult = $this->traceInsertData( $aDataArr );
            if( $mResult === -11 )
            {//封锁表未生成，错误编号#3010
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                }
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "系统错误：错误编号:#3010");
            }
            elseif( $mResult === -33 )
            {//资金不够
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                }
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "余额不足");
            }
            elseif( $mResult !== TRUE )
            {
                if( FALSE == $this->oDB->doRollback() )
                {//回滚事务
                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                }
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "投注失败，请重试".$mResult);
            }
            //0602 03: 提交投单流程事务处理[结束] -----------------------
            if( FALSE == $this->oDB->doCommit() )
            {//事务提交失败
                return ajaxMsg("error", "系统错误：错误编号:#5013");
            }
            //0602 04: 解锁资金表 -------------------------------------
            $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );
            return TRUE;
        }
    }
    
    
    
    /**
     * 用户追号数据处理流程[非事务]
     *
     * @author  james    090815
     * @access  private
     * @param   array    $aTraceLocks       //封锁表数据[传入更新值的SQL语句数组]
     * @param   array    $aTraceData        //追号表数据[必须]
     * @param   array    $aTraceOrderData   //追号帐变数据[必须]
     * @param   array    $aTraceDetailData  //追号详情表数据[必须]
     * @param   array    $aTraceSales       //追号销量表数据[必须]
     * @return  mixed    小于0为错误，全等于TRUE为成功
     */
    private function traceInsertData( $aDataArr=array() )
    {
        //00:必要参数判断
        if( empty($aDataArr) || !is_array($aDataArr) )
        {//无任何数据
        	return 0;
        }
        if( empty($aDataArr['aTraceData']) || !is_array($aDataArr['aTraceData']) )
        {//追号表记录必须插入
            return 0;
        }
        if( empty($aDataArr['aTraceOrderData']) || !is_array($aDataArr['aTraceOrderData']) )
        {//追号帐变记录必须插入
            return 0;
        }
        if( empty($aDataArr['aTraceDetailData']) || !is_array($aDataArr['aTraceDetailData']) )
        {//追号详情表必须写入
            return 0;
        }
        if( empty($aDataArr['aTraceSales']) || !is_array($aDataArr['aTraceSales']) )
        {//销量表记录必须插入
            return 0;
        }
        /* @var $oOrders model_orders */
        /* @var $oLocks model_locks */
        /* @var $oTask model_task */
        $oOrders   = A::singleton('model_orders');   //帐变模型
        $oLocks    = A::singleton('model_locks');    //封锁模型
        $oTask     = A::singleton('model_task');     //追号模型
        
        
        //01: 写入封锁表[在要封锁的时候才执行]--------------------------------------
        if( !empty($aDataArr['aTraceLocks']) && is_array($aDataArr['aTraceLocks']) )
        {//执行传入的SQL数组
            foreach( $aDataArr['aTraceLocks'] as $v )
            {
                $this->oDB->query($v);
                if( $this->oDB->errno() > 0 )
                {//执行失败
                    return -1;
                }
                /*if( $this->oDB->ar() == 0 )
                {//没有找到号码[可能当期封锁表数据未生成]
                    return -11;
                }*/
            }
        }
        
        
        //02 ：写追号表-----------------------------------------------------------
        $iTaskId = $oTask->taskInsert( $aDataArr['aTraceData'] );
        if( $iTaskId <= 0 )
        {//写入追号表失败
            return -2;
        }
        
        
        //03 : 写入追号帐变表-----------------------------------------------------
        $aDataArr['aTraceOrderData']['iTaskId'] = $iTaskId;
        $mResult = $oOrders->addOrders( $aDataArr['aTraceOrderData'] );
        if( $mResult === -1009  )
        {//资金不够
            return -33;
        }
        elseif( $mResult !== TRUE )
        {//其他帐变错误
            return -3;
        }
        
        
        //04 : 写入追号详情表-----------------------------------------------------
        foreach( $aDataArr['aTraceDetailData'] as & $v )
        {
        	$v['taskid'] = $iTaskId;
        }
        if(  TRUE !== $oTask->taskDetailInsert( $aDataArr['aTraceDetailData'] ) )
        {//写入追号详情表失败
             return -3;
        }
        
        //05 ：写入销量表---------------------------------------------------------
        foreach( $aDataArr['aTraceSales'] as $v )
        {
	        if( TRUE !== $oLocks->salesUpdate( $v ) )
	        {//写入销量表失败
	            return -7;
	        }
        }
        
        //06: 如果追号中有当期则把当期转为注单
        if( isset($aDataArr['bIsCurrentIssue']) && $aDataArr['bIsCurrentIssue'] === TRUE )
        {
        	$bIsFinish = $aDataArr['aTraceData']['issuecount'] == 1 ? TRUE : FALSE;
        	$aNewData = array();
        	$aNewData['aCreateData']   = isset($aDataArr['aCreateData'])  ? $aDataArr['aCreateData']  : "";
        	$aNewData['aProjectData']  = isset($aDataArr['aProjectData']) ? $aDataArr['aProjectData'] : "";
        	$aNewData['aJoinData']     = isset($aDataArr['aJoinData'])    ? $aDataArr['aJoinData']    : "";
        	$aNewData['aBackData']     = isset($aDataArr['aBackData'])    ? $aDataArr['aBackData']    : "";
        	$aNewData['aExpandData']   = isset($aDataArr['aExpandData'])  ? $aDataArr['aExpandData']  : "";
        	$aNewData['aDiffData']     = isset($aDataArr['aDiffData'])    ? $aDataArr['aDiffData']    : "";
      	    if( TRUE !==  $oTask->traceToProjectData($iTaskId, $aNewData, $bIsFinish) )
  	        {//生成注单失败
  	       	    return -8;
  	        }
        }
        
        //07：完成[返回TRUE]--------------------------------------------------------
        return TRUE;
    }
    
    
    /**
     * 获取号码扩展展开表数据[不变价展开][原复式或者多倍计算]
     *
     * @author  james    090812
     * @access  protected
     * @param   int      $iMethodId     //玩法ID
     * @param   string   $sNums         //原始号码[123|222]形式
     */
    private function & getExpandCode( $iMethodId, $sNums )
    {
        $aResult = array();
        if( empty($iMethodId) || !isset($this->_aMethod_Config[$iMethodId]) )
        {
            return $aResult;
        }
        $sMethod = $this->_aMethod_Config[$iMethodId];
        if( $sNums == "" )
        {
            return $aResult;
        }
        switch( $sMethod )
        {
            case 'ZX'      : //同下
            case 'ZXHZ'    : $aTempArr = $this->getExpandCodeZX($sNums);
                             foreach( $aTempArr as $v )
                             {
                                $aResult[$v['times']][] = implode( "|", $v['nums'] );
                             }
                             return $aResult; break;
            case 'TX'      : //同下
            case 'ZS'      : //同下
            case 'ZL'      : $aResult[1][] = $sNums; 
                             return $aResult; break;
            case 'HHZX'    : $aTempArr = $this->getExpandCodeZX($sNums);
                             foreach( $aTempArr as $v )
                             {
                                $aResult[$v['times']][] = implode( "|", $v['nums'] );
                             }
                             return $aResult; break;
            case 'ZUXHZ'   : //同下
            case 'YMBDW'   : //同下
            case 'EMBDW'   : //同下
            case 'QEZX'    : //同下
            case 'HEZX'    : //同下
            case 'QEZUX'   : //同下
            case 'P5HEZX'  : //同下
            case 'P5HEZUX' : //同下
            case 'HEZUX'   : //同下
            case 'P5DWW'   : //同下
            case 'P5DQW'   : //同下
            case 'P5DBW'   : //同下
            case 'P5DSW'   : //同下
            case 'P5DGW'   : //同下
            case 'DBW'     : //同下
            case 'DSW'     : //同下
            case 'DGW'     : $aResult[1][] = $sNums; 
                             return $aResult; break;
            case 'QEDXDS'  : //同下
            case 'P5HEDXDS': //同下
            case 'HEDXDS'  : $aResult = $this->getExpandDXDS( $sNums ); //转成特殊型
                             return $aResult; break;
            default        : return $aResult; break;
        }
    }
    
    
    /**
     * 根据原始号码扩展表数据，变价号码，调价线，全包最低奖金，所有奖金，每个奖级对应转直注数扩展号码扩展表数据
     *
     * @author james 090818
     * @access private
     * @param   int      $iMethodId     //玩法ID
     * @param   array    $aExpandData   //号码扩展表原始数据[未变价得到的数据]
     * @param   array    $aArr          //详细参数如下：
     * ----------------------------------------------------
     * @param   array    $aArr['aCodes']            //变价号码[从数据库查出来的原始值]
     * @param   array    $aArr['aDetail']           //变价线[从数据库里查出来的原始值]
     * @param   float    $aArr['totalmoney']        //全包金额
     * @param   float    $aArr['topuserpoint']      //总代返点
     * @param   float    $aArr['adjustminprofit']   //公司极限上调留水
     * @param   float    $aArr['adjustmaxpercent']  //极限下调返奖率
     * @param   float    $aArr['loseline']          //下调开始线
     * @param   float    $aArr['winline']           //上调开始线
     * @param   float    $aArr['fSaleMoney']        //当前销售量
     * @param   float    $aArr['fMoney']            //本单总价格
     * @param   array    $aArr['aPrizes']           //奖级信息[对应奖金]
     * @param   array    $aArr['aPrizeCount']       //奖级对应转直注数
     * -----------------------------------------------------
     * @return  mixed    成功返回TRUE，失败返回小于0的数字
     * @abstract 计算变价价格公式：
     *              1：降价=> 单倍最终奖金 = 单倍奖金  - (单倍奖金-(全包金额*极限反奖率)/转直注数)*调价线的调价比例
     *              2：升价=> 单倍最终奖金 = 单倍奖金  + ((全包金额*(1-总代返点-公司极限留水)/转直注数)-单倍奖金)*调价比例
     */
    private function getAdjustExpand( $iMethodId, &$aExpandData, $aArr=array() )
    {
    	//00 : 数据简单检查
    	if( empty($iMethodId) || !isset($this->_aMethod_Config[$iMethodId]) )
        {
            return 0;
        }
        $sMethod = $this->_aMethod_Config[$iMethodId];
        if( empty($aArr) || !is_array($aArr) )
        {
        	return 0;
        }
    	if( empty($aExpandData) || empty($aArr['aCodes']) || empty($aArr['aDetail']) )
    	{//如果没有需要转换的数据直接返回TRUE
    		return TRUE;
    	}
        if( !is_array($aExpandData) || !is_array($aArr['aCodes']) || !is_array($aArr['aDetail']) )
        {//如果数据格式错误返回0
            return 0;
        }
        if( empty($aArr['totalmoney']) || !is_numeric($aArr['totalmoney']) || $aArr['totalmoney']<0 )
        {//全包金额
        	return 0;
        }
        if( !isset($aArr['topuserpoint']) || !is_numeric($aArr['topuserpoint']) || $aArr['topuserpoint']<0 )
        {//总代返点
            return 0;
        }
        if( !isset($aArr['adjustminprofit']) || !is_numeric($aArr['adjustminprofit']) )
        {//公司极限留水
            return 0;
        }
        if( !isset($aArr['adjustmaxpercent']) || !is_numeric($aArr['adjustmaxpercent']) )
        {//极限返奖率
            return 0;
        }
        if( !isset($aArr['loseline']) || !is_numeric($aArr['loseline']) || $aArr['loseline']<0 )
        {//下调开始线
            return 0;
        }
        if( !isset($aArr['winline']) || !is_numeric($aArr['winline']) || $aArr['winline']<0 )
        {//上调开始线
            return 0;
        }
        if( !isset($aArr['fSaleMoney']) || !is_numeric($aArr['fSaleMoney']) || $aArr['fSaleMoney']<0 )
        {//当前销量
            return 0;
        }
        if( !isset($aArr['fMoney']) || !is_numeric($aArr['fMoney']) || $aArr['fMoney']<0 )
        {//本单总价格
            return 0;
        }
    	if( empty($aArr['aPrizes']) || !is_array($aArr['aPrizes']) 
    	    || empty($aArr['aPrizeCount']) || !is_array($aArr['aPrizeCount']) )
    	{//奖金级和对应的转直注数
    		return 0;
    	}
    	
    	//01: 前期数据整理
    	//01 00：变价号码整理
    	$aTempCodePrize     = array(); //号码和已有奖金值对应关系数据
    	$aTempCodes         = array(); //所有变价号码单独组成的数据
    	$aTempCodeZS        = array(); //组三 号码
    	$aTempCodeZL        = array(); //组六号码
    	foreach( $aArr['aCodes'] as $v )
    	{
    		$aTempCodes[]       = $v['code'];
    		if( $v['stamp'] == 1 )
    		{//组三
    			$aTempCodeZS[]  = $v['code'];
    		}
    		if( $v['stamp'] == 2 )
    		{//组六
    			$aTempCodeZL[]  = $v['code'];
    		}
    		$aTempCodePrize[$v['code']] = floatval($v['sumprizes']);
    	}
    	//01 01：变价线整理[变价线从小到大排列]
    	$aUpDetail   = array();    //上调变价线
    	$aDownDetail = array();    //下调变价线
    	foreach( $aArr['aDetail'] as $v )
    	{
    		if( $v['isup'] == 1 )
    		{//上调变价线
    			$aUpDetail[]   = $v;
    		}
    		else 
    		{//下调变价线
    			$aDownDetail[] = $v; 
    		}
    	}
    	//01 02：变价金额整理
    	//最低奖金[全包金额(2000)*极限反奖率]
    	$fMinPrize      =  floatval($aArr['totalmoney'] * $aArr['adjustmaxpercent']);
    	//最高奖金[全包金额*(1-总代返点-公司极限留水)]
        $fMaxPrize      =  floatval($aArr['totalmoney'] * (1-$aArr['topuserpoint']-$aArr['adjustminprofit']));
        
        //01 03：常用金额整理
        $fSaleMoney    = floatval($aArr['fSaleMoney']); //当前销售量
        $fEndSaleMoney = $fSaleMoney + floatval($aArr['fMoney']);//买后的销售量
        $fLoseLine     = floatval($aArr['loseline']);
        $fWinLine      = floatval($aArr['winline']);
        
        
        //01 04: 奖金信息整理
        $aPrizes        = $aArr['aPrizes'];                //奖级信息
        $aPrizeCount    = $aArr['aPrizeCount'];            //奖级对应转直注数
    	
    	//02: 对变价号码根据号码展开式进行处理
    	$aTempCodesLastPrize = array();    //所有变价号码的最终奖金和号码对应关系数据
    	foreach( $aExpandData as $iEid=>$aEarr )
    	{
    		$aTempCodesLastPrize[$iEid] = array(); //该扩展组里变价号码的最终奖金和号码对应关系数据
    		//02 01: 获取该扩展组里的单号转直注数
            $iTempCodeCount = intval($aPrizeCount[$aEarr['level']]);
            //02 02: 获取该扩展组里的单倍奖金
            $iTempPrize     = floatval($aPrizes[$aEarr['level']]);
            //02 03: 获取该扩展组里的 号码倍数*方案倍数
            $iTempTotalTime = $aEarr['prize']/$iTempPrize;
            //02 04: 获取该扩展组里能降价的最大价格[单倍奖金-(全包金额*返奖率/转直注数)]
            $fTempDownMax   = $iTempPrize - round(($fMinPrize/$iTempCodeCount),2);
            //02 05: 获取该扩展组里能升价的最大价格[全包金额*(1-总代返点-公司极限留水)/转直注数 -单倍奖金]
            $fTempUpMax     = round(($fMaxPrize/$iTempCodeCount),2) - $iTempPrize;
            $aTempAdjust    = array();
    		switch( $sMethod )
    		{
    			case 'TX'      : return 0; break;//暂时不支持变价
    			case 'ZX'      : //同下
	            case 'ZXHZ'    : //同下
	            case 'HHZX'    : //同下
	            case 'ZUXHZ'   : //同下
	            case 'ZS'      : //同下
	            case 'ZL'      : //同下
	            case 'YMBDW'   : //同下 
                case 'EMBDW'   : //同下
	            case 'QEZX'    : //同下 
	            case 'HEZX'    : //同下 
	            case 'QEZUX'   : //同下
	            case 'HEZUX'   : //同下
	            case 'P5HEZX'  : //同下
	            case 'P5HEZUX' : //同下
	            case 'DBW'     : //同下
	            case 'DSW'     : //同下
	            case 'DGW'     : //同下
	            case 'P5DWW'   : //同下
	            case 'P5DQW'   : //同下
	            case 'P5DBW'   : //同下
	            case 'P5DSW'   : //同下
	            case 'P5DGW'   : $aResult = array();
                                 if( $sMethod == 'ZX' || $sMethod == 'ZXHZ' )
                                 {
                                    //获取该扩展组里的号码
                                    $aTempNum = explode( "|", $aEarr['expandcode'] );
                                    $aResult  = array_intersect( $aTempCodes, $aTempNum ); //交集
                                 }
                                 elseif( $sMethod == 'HHZX' )
                                 {
                                    //获取该扩展组里的号码并转直
                                    $aTempNum = $this->getExtendCodeHHZX( $aEarr['expandcode'] );
                                    //第二步做检测[1等奖做组三检测，二等奖做组六检测]
                                    $aTempNum = $aEarr['level'] == 1 ? $aTempNum['ZS'] : $aTempNum['ZL'];
                                    $aResult  = array_intersect( $aTempCodes, $aTempNum ); //交集
                                 }
                                 elseif( $sMethod == 'ZUXHZ' )
                                 {//组选和值
                                    //[1等奖做组三检测，二等奖做组六检测]
                                    $aResult  = $aEarr['level'] == 1 ? $aTempCodeZS : $aTempCodeZL;
                                 }
                                 else
                                 {//其他[不包括一码不定位和二码不定位]所有变价号码只有号码同倍情况
                                    $aResult  = $aTempCodes;
                                 }
                                 foreach( $aResult as $v )
                                 {
                                    //02 04: 获取该号码的开始调价线和加上自己奖金后达到的调价线[检测不能跨线]
                                    $fLastPrize = 0; //调价后的最终奖金
                                    $iBeginLine = 0; //开始调价线
                                    if( $aTempCodePrize[$v] > ($fSaleMoney + $fLoseLine) )
                                    {//下调奖金
                                        foreach( $aDownDetail as $kk=>$vv )
                                        {//02 04 00：获取符合最终的变价线
                                            if( $aTempCodePrize[$v] > ($fSaleMoney + $vv['uplimit']) )
                                            {
                                                $iBeginLine = $kk;
                                            }
                                        }
                                        //02 04 01：算出最终该号码的单倍奖金
                                        $fLastPrize = $iTempPrize - 
                                                      round(($fTempDownMax * $aDownDetail[$iBeginLine]['percent']),2);
                                        //02 04 02: 比较变价号码
                                        $this->compareAdjustedCodes( $v, $fLastPrize );
                                        //02 04 03: 算该号码*方案倍数*号码倍数的最终奖金
                                        $fLastPrize = $fLastPrize * $iTempTotalTime;
                                        //02 04 04: 获取开出该号码后的最后奖金[会中多单处理]
                                        $fSendPrize = $this->getCodeSendPrize( $iMethodId, $aEarr['expandcode'], 
                                                                               $v, array(1=>$fLastPrize) );
                                        //02 04 05: 检测是否跨了两条线
                                        if( isset($aDownDetail[$iBeginLine+2]) && ($aTempCodePrize[$v]+$fSendPrize) > 
                                            ($fEndSaleMoney + $aDownDetail[$iBeginLine+2]['uplimit']) 
                                        )
                                        {//跨了两条线
                                            return -99;
                                        }
                                    }
                                    elseif( $aTempCodePrize[$v] < ($fSaleMoney - $fWinLine) )
                                    {//上调奖金
                                        foreach( $aUpDetail as $kk=>$vv )
                                        {//02 04 00：获取符合最终的变价线
                                            if( $aTempCodePrize[$v] < ($fSaleMoney - $vv['uplimit']) )
                                            {
                                                $iBeginLine = $kk;
                                            }
                                        }
                                        //02 04 01：算出最终该号码的单倍奖金
                                        $fLastPrize = $iTempPrize + 
                                                      round(($fTempUpMax * $aUpDetail[$iBeginLine]['percent']),2);
                                        //02 04 02: 比较变价号码
                                        $this->compareAdjustedCodes( $v, $fLastPrize );
                                        //02 04 03: 算该号码*方案倍数*号码倍数的最终奖金
                                        $fLastPrize = $fLastPrize * $iTempTotalTime;
                                        //02 04 04: 获取开出该号码后的最后奖金[会中多单处理]
                                        $fSendPrize = $this->getCodeSendPrize( $iMethodId, $aEarr['expandcode'], 
                                                                               $v, array(1=>$fLastPrize) );
                                        //02 04 05: 检测是否跨了两条线
                                        if( isset($aUpDetail[$iBeginLine+2]) && ($aTempCodePrize[$v]+$fSendPrize) < 
                                            ($fEndSaleMoney - $aUpDetail[$iBeginLine+2]['uplimit']) 
                                        )
                                        {//跨了两条线
                                            return -99;
                                        }
                                    }
                                    if( isset($aTempAdjust["".$fLastPrize]) )
                                    {
                                        $aTempAdjust["".$fLastPrize] = $aTempAdjust["".$fLastPrize]."|".$v;
                                    }
                                    else
                                    {
                                        $aTempAdjust["".$fLastPrize] = $v;
                                    }
                                 }
                                 //把已处理过的号码从原号码中踢出
                                 if( $sMethod == 'ZX' || $sMethod == 'ZXHZ' )
                                 {
                                    $aTempCodes = array_diff( $aTempCodes, $aResult );
                                    $aTempNum   = array_diff( $aTempNum, $aResult );
                                    $aExpandData[$iEid]['expandcode'] = implode("|",$aTempNum);
                                 }
                                 elseif( $sMethod == 'HHZX' )
                                 {
                                    $aTempCodes = array_diff( $aTempCodes, $aResult );
                                 }
                                 $aTempCodesLastPrize[$iEid] = $aTempAdjust;
                                 break;
                                 
	            case 'QEDXDS'  : //同下
	            case 'P5HEDXDS': //同下
	            case 'HEDXDS'  : //获取该扩展组里的号码[首先获取检测条件]
                                 $aTempNum    = explode( "|", $aEarr['expandcode'] );
    		                     $aTemp_Seach = array();
                                 if( count($aTempNum) > 1 )
                                 {//多于一组 
                                    foreach( $aTempNum as $order )
                                    {//每一组相当于一单
                                        $aTemp_nums    = explode( "#", $order );
                                        $aTemp_Seach[] = "([".$aTemp_nums[0]."][".$aTemp_nums[1]."])";
                                    }
                                 }
                                 else 
                                 {//只有一组
                                    $aTemp_nums    = explode( "#", $aTempNum[0] );
                                    $aTemp_Seach[] = "[".$aTemp_nums[0]."][".$aTemp_nums[1]."]";
                                 }
                                 $sPregString = count($aTemp_Seach) > 1 ? "(".implode("|",$aTemp_Seach).")"
                                                : $aTemp_Seach[0];
                                 if( $sMethod == "HEDXDS" )
                                 {//后二取后两位
                                    $sPregString = "/".$sPregString."$/";
                                 }
                                 else
                                 {//取前两位
                                    $sPregString = "/^".$sPregString."/";
                                 }
                                 $aTemp_Seach = array();
                                 foreach( $aTempCodes as $v )
                                 {
                                 	if( preg_match($sPregString, $v) )
                                 	{//如果是在该扩展组中
	                                 	//02 04: 获取该号码的开始调价线和加上自己奖金后达到的调价线[检测不能跨线]
	                                    $fLastPrize = 0; //调价后的最终奖金
	                                    $iBeginLine = 0; //开始调价线
	                                    if( $aTempCodePrize[$v] > ($fSaleMoney + $fLoseLine) )
	                                    {//下调奖金
	                                        foreach( $aDownDetail as $kk=>$vv )
	                                        {//02 04 00：获取符合最终的变价线
	                                            if( $aTempCodePrize[$v] > ($fSaleMoney + $vv['uplimit']) )
	                                            {
	                                                $iBeginLine = $kk;
	                                            }
	                                        }
	                                        //02 04 01：算出最终该号码的单倍奖金
	                                        $fLastPrize = $iTempPrize - 
	                                                  round(($fTempDownMax * $aDownDetail[$iBeginLine]['percent']),2);
	                                        //02 04 02: 比较变价号码
	                                        $this->compareAdjustedCodes( $v, $fLastPrize );
	                                        //02 04 03: 算该号码*方案倍数*号码倍数的最终奖金
	                                        $fLastPrize = $fLastPrize * $iTempTotalTime;
	                                        //02 04 04: 检测是否跨了两条线
	                                        if( isset($aDownDetail[$iBeginLine+2]) && 
	                                            ($aTempCodePrize[$v]+$fLastPrize) > 
	                                            ($fEndSaleMoney + $aDownDetail[$iBeginLine+2]['uplimit']) 
	                                        )
	                                        {//跨了两条线
	                                            return -99;
	                                        }
	                                    }
	                                    elseif( $aTempCodePrize[$v] < ($fSaleMoney - $fWinLine) )
	                                    {//上调奖金
	                                        foreach( $aUpDetail as $kk=>$vv )
	                                        {//02 04 00：获取符合最终的变价线
	                                            if( $aTempCodePrize[$v] < ($fSaleMoney - $vv['uplimit']) )
	                                            {
	                                                $iBeginLine = $kk;
	                                            }
	                                        }
	                                        //02 04 01：算出最终该号码的单倍奖金
	                                        $fLastPrize = $iTempPrize + 
	                                                      round(($fTempUpMax * $aUpDetail[$iBeginLine]['percent']),2);
	                                        //02 04 02: 比较变价号码
                                            $this->compareAdjustedCodes( $v, $fLastPrize );
                                            //02 04 03: 算该号码*方案倍数*号码倍数的最终奖金
	                                        $fLastPrize = $fLastPrize * $iTempTotalTime;
	                                        //02 04 04: 检测是否跨了两条线
	                                        if( isset($aUpDetail[$iBeginLine+2]) && 
	                                            ($aTempCodePrize[$v]+$fLastPrize) < 
	                                            ($fEndSaleMoney - $aUpDetail[$iBeginLine+2]['uplimit']) 
	                                        )
	                                        {//跨了两条线
	                                            return -99;
	                                        }
	                                    }
	                                    if( isset($aTempAdjust["".$fLastPrize]) )
	                                    {
	                                        $aTempAdjust["".$fLastPrize] = $aTempAdjust["".$fLastPrize]."|".$v;
	                                    }
	                                    else
	                                    {
	                                        $aTempAdjust["".$fLastPrize] = $v;
	                                    }
                                 		$aTemp_Seach[] = $v; //保存匹配的
                                 	}
                                 }
                                 $aTempCodes = array_diff( $aTempCodes, $aTemp_Seach );
                                 $aTempCodesLastPrize[$iEid] = $aTempAdjust;
                                 break;
	            default        : return 0; break;
    		}
    	}
    	foreach( $aTempCodesLastPrize as $k=>$v )
    	{//$k: 原扩展表ID，$v变价号码以及奖金值
    		foreach( $v as $kk=>$vv )
    		{//$kk:奖金，$vv: 号码
    			$aExpandData[] = array(
    			                      'projectid'    => 0,
    			                      'isspecial'    => 1,
    			                      'level'        => $aExpandData[$k]['level'],
    			                      'codetimes'    => $aExpandData[$k]['codetimes'],
    			                      'prize'        => floatval($kk),
    			                      'expandcode'   => $vv
    			                     );
    		}
    	}
    	foreach( $aExpandData as $k=>$v )
    	{
    		if( $v['expandcode'] == "" )
    		{
    			unset($aExpandData[$k]);
    		}
    	}
    	return TRUE;
    }
    
    
    /**
     * 把这次变价和已往变价的做比较，如果在已往基础上还有变价的则往则这次变价的数据集里写入
     */
    private function compareAdjustedCodes( $sCode, $fPrice )
    {
    	if( empty($sCode) || empty($fPrice) )
    	{
    		return FALSE;
    	}
    	if( array_key_exists($sCode, $this->_aAdjustedCodes) )
    	{
    		if( $this->_aAdjustedCodes[$sCode] != $fPrice )
    		{
    			$this->_aAdjustingCodes[$sCode] = $fPrice;
    		}
    	}
    	else
    	{
    		$this->_aAdjustingCodes[$sCode] = $fPrice;
    	}
    	return TRUE;
    }
    
    
    /**
     * 根据玩法和购买的号码获取封锁表查询条件
     *
     * @author  james    090812
     * @access  private 
     * @param   int      $iMethodId     //玩法ID
     * @param   string   $sNums         //原始号码[123|222]形式
     * @return  string
     */
    private function & getSearchLocksCondition( $iMethodId, $sNums )
    {
        $sResult = "";
        if( empty($iMethodId) || !isset($this->_aMethod_Config[$iMethodId]) )
        {
            return $sResult;
        }
        $sMethod = $this->_aMethod_Config[$iMethodId];
        if( $sNums == "" )
        {
            return $sResult;
        }
        switch( $sMethod )
        {
            case 'ZX'      : //同下
            case 'ZXHZ'    : $aNumArr = explode( "|", $sNums );
                             $aNumArr = array_unique($aNumArr); //移除重复的值
                             $sResult = " `code` IN(" . implode( ",", $aNumArr ) . ") ";
                             return $sResult; break;
            case 'TX'      : $aNumArr = explode( "|", $sNums );
                             $aNumArr = array_unique($aNumArr); //移除重复的值
                             $aTempArr= array( 'BW'=>array(), 'SW'=>array(), 'GW'=>array() );
                             foreach( $aNumArr as $v )
                             {
                             	$aTempArr['BW'][] = $v[0];
                             	$aTempArr['SW'][] = $v[1];
                             	$aTempArr['GW'][] = $v[2];
                             }
                             $aTempArr['BW'] = array_unique($aTempArr['BW']);
                             $aTempArr['SW'] = array_unique($aTempArr['SW']);
                             $aTempArr['GW'] = array_unique($aTempArr['GW']);
                             $sResult = " `code` REGEXP '(^[".implode("",$aTempArr['BW'])."])|".
                                                        "(^[0-9][".implode("",$aTempArr['SW'])."])|".
                                                        "([".implode("",$aTempArr['GW'])."]$)' ";
                             return $sResult; break;
                             
            case 'ZS'      : $aNumArr = $this->getStampCode( 'ZS', $sNums );//特征值+特征码判断
                             $sResult = " `stamp`='1' AND `stampvalue` REGEXP '(".implode("|",$aNumArr).")' ";
                             return $sResult; break;
                             
            case 'ZL'      : $aNumArr = $this->getStampCode( 'ZL', $sNums );//特征值+特征码判断
                             $sResult = " `stamp`='2' AND `stampvalue` REGEXP '(".implode("|",$aNumArr).")' ";
                             return $sResult; break;
                             
            case 'HHZX'    : $aNumArr = $this->getExtendCodeHHZX( $sNums );//转直选
                             $aNumArr = array_merge( $aNumArr['ZS'], $aNumArr['ZL'] );
                             $aNumArr = array_unique($aNumArr); //移除重复的值
                             $sResult = " `code` IN(" . implode( ",", $aNumArr ) . ") ";
                             return $sResult; break;
                             
            case 'ZUXHZ'   : $sResult = " `stamp`!='0' AND `addvalue` REGEXP '^(" . $sNums . ")$' ";//和值定位
                             return $sResult; break;
                             
            case 'YMBDW'   : $aNumArr = explode( "|",$sNums );
                             $sResult = " `code` REGEXP '[" . implode( "", $aNumArr ) . "]' ";
                             return $sResult; break;
                             
            case 'EMBDW'   : $aNumArr = $this->getStampCode( 'EMBDW', $sNums );//特征码判断
                             $sResult = " `m2value` REGEXP '(". implode( "|", $aNumArr ) .")' ";
                             return $sResult; break;
                             
            case 'QEZX'    : $aNumArr = explode( "|", $sNums );
                             if( count($aNumArr) != 2 )
                             {
                                return $sResult;
                             }
                             $sResult = " `code` REGEXP '^[".$aNumArr[0]."][".$aNumArr[1]."]' ";
                             return $sResult; break;
                             
            case 'HEZX'    : $aNumArr = explode( "|", $sNums );
                             if( count($aNumArr) != 2 )
                             {
                                return $sResult;
                             }
                             $sResult = " `code` REGEXP '[".$aNumArr[0]."][".$aNumArr[1]."]$' ";
                             return $sResult; break;
                             
            case 'QEZUX'   : $aNumArr = $this->getStampCode( 'QEZUX', $sNums );//特征码判断
                             $sResult = " `q2value` REGEXP '(". implode( "|", $aNumArr ) .")' ";
                             return $sResult; break;
                             
            case 'HEZUX'   : $aNumArr = $this->getStampCode( 'HEZUX', $sNums );//特征码判断
                             $sResult = " `h2value` REGEXP '(". implode( "|", $aNumArr ) .")' ";
                             return $sResult; break;
                             
            case 'P5HEZX'  : $aNumArr = explode( "|", $sNums );
                             if( count($aNumArr) != 2 )
                             {
                                return $sResult;
                             }
                             $sResult = " `code` REGEXP '^[".$aNumArr[0]."][".$aNumArr[1]."]' ";
                             return $sResult; break;
                             
            case 'P5HEZUX' : $aNumArr = $this->getStampCode( 'P5HEZUX', $sNums );//特征码判断
                             $sResult = " `h2value` REGEXP '(". implode( "|", $aNumArr ) .")' ";
                             return $sResult; break;
                             
            case 'DBW'     : $aNumArr = explode( "|", $sNums );
                             $sResult = " `code` REGEXP '^[". implode( "", $aNumArr ) ."]' ";
                             return $sResult; break;
                             
            case 'DSW'     : $aNumArr = explode( "|", $sNums );
                             $sResult = " `code` REGEXP '^[0-9][". implode( "", $aNumArr ) ."]' ";
                             return $sResult; break;
                             
            case 'DGW'     : $aNumArr = explode( "|", $sNums );
                             $sResult = " `code` REGEXP '[". implode( "", $aNumArr ) ."]$' ";
                             return $sResult; break;
                             
            case 'P5DWW'   : $aNumArr = explode( "|", $sNums );
                             $sResult = " `code` REGEXP '^[". implode( "", $aNumArr ) ."]' ";
                             return $sResult; break;
                             
            case 'P5DQW'   : $aNumArr = explode( "|", $sNums );
                             $sResult = " `code` REGEXP '^[0-9][". implode( "", $aNumArr ) ."]' ";
                             return $sResult; break;
                             
            case 'P5DBW'   : $aNumArr = explode( "|", $sNums );
                             $sResult = " `code` REGEXP '[". implode( "", $aNumArr ) ."]$' ";
                             return $sResult; break;
                             
            case 'P5DSW'   : $aNumArr = explode( "|", $sNums );
                             $sResult = " `code` REGEXP '^[". implode( "", $aNumArr ) ."]' ";
                             return $sResult; break;
                             
            case 'P5DGW'   : $aNumArr = explode( "|", $sNums );
                             $sResult = " `code` REGEXP '[". implode( "", $aNumArr ) ."]$' ";
                             return $sResult; break;
                             
            case 'QEDXDS'  : //同下
            case 'P5HEDXDS': $aNumArr = explode( "|", $sNums );
                             $aFisrt  = array();
                             $aSecond = array();
                             for( $i=0; $i<strlen($aNumArr[0]); $i++ )
                             {
                                $aFisrt = array_merge( $aFisrt, $this->_aBSAD[$aNumArr[0][$i]] );
                             }
                             for( $i=0; $i<strlen($aNumArr[1]); $i++ )
                             {
                                $aSecond = array_merge( $aSecond, $this->_aBSAD[$aNumArr[1][$i]] );
                             }
                             $aFisrt  = array_unique($aFisrt);
                             $aSecond = array_unique($aSecond);
                             $sResult = " `code` REGEXP '^[".implode("",$aFisrt)."][".implode("",$aSecond)."]' ";
                             return $sResult; break;
            case 'HEDXDS'  : $aNumArr = explode( "|", $sNums );
                             $aFisrt  = array();
                             $aSecond = array();
                             for( $i=0; $i<strlen($aNumArr[0]); $i++ )
                             {
                                $aFisrt = array_merge( $aFisrt, $this->_aBSAD[$aNumArr[0][$i]] );
                             }
                             for( $i=0; $i<strlen($aNumArr[1]); $i++ )
                             {
                                $aSecond = array_merge( $aSecond, $this->_aBSAD[$aNumArr[1][$i]] );
                             }
                             $aFisrt  = array_unique($aFisrt);
                             $aSecond = array_unique($aSecond);
                             $sResult = " `code` REGEXP '[".implode("",$aFisrt)."][".implode("",$aSecond)."]$' ";
                             return $sResult; break;
            default        : return $sResult; break;
        }
    }
    
    
    
    /**
     * 检查按最大奖金查询有限制号码时根据不同奖金进行实际每个号码检查是否达到封锁
     *
     * @author  james    090812
     * @access  private
     * @param   int      $iMethodId     //玩法ID
     * @param   array    $aLocksData    //从封锁表查出来的数据
     * @param   array    $aExpandData   //号码扩展里的数据
     * @param   float    $fSalesMoney   //当前销量
     * @param   float    $fMaxLost      //最大封锁值
     * @return  没有限号直接返回TRUE，有限号返回限制号码，错误返回FALSE
     */
    private function checkMaxLostByNo( $iMethodId, $aLocksData, $aExpandData, $fSalesMoney, $fMaxLost )
    {
        if( empty($iMethodId) || !isset($this->_aMethod_Config[$iMethodId]) )
        {
            return FALSE;
        }
        $sMethod = $this->_aMethod_Config[$iMethodId];
        if( empty($aLocksData) || !is_array($aLocksData) )
        {//如果没有要检查的限号直接返回TRUE
            return TRUE;
        }
        if( empty($aExpandData) || !is_array($aExpandData) )
        {//如果没有购买的号码则返回FALSE
            return FALSE;
        }
        if( !isset($fSalesMoney) || !is_numeric($fSalesMoney) || $fSalesMoney < 0 )
        {//如果当前销量不正确直接返回FALSE
            return FALSE;
        }
        $fSalesMoney = floatval($fSalesMoney);
        if( !isset($fMaxLost) || !is_numeric($fMaxLost) )
        {//如果最大封锁值不正确直接返回FALSE
            return FALSE;
        }
        $fMaxLost    = floatval($fMaxLost);
        //预处理限号号码
        $aLimitNum   = array(); //限制的号码
        $aLimitValue = array(); //已达到的封锁
        $aLimitNumZS = array(); //组三号码
        $aLimitNumZL = array(); //组六号码
        foreach( $aLocksData as $v )
        {
            $aLimitNum[]             = $v['code'];
            if( $v['stamp'] == 1 )
            {
            	$aLimitNumZS[]       = $v['code'];
            }
            elseif( $v['stamp'] == 2 )
            {
            	$aLimitNumZL[]       = $v['code'];
            }
            $aLimitValue[$v['code']] = $v['sumprizes'];
        }
        $aTempExpandData = array( 'isspecial'=>array(), 'normal'=>array() );
        $aPrizes         = array(); //奖金情况[用于通选特殊计算]
        $sAllNums        = "";      //购买的原式号码
        foreach( $aExpandData as $v )
        {//排序[变价的排前面]先检查变价的，再检查不变价的
            if( $v['isspecial'] == 1 )
            {
                $aTempExpandData['isspecial'][] = $v;
            }
            else
            {
                $aTempExpandData['normal'][]    = $v;
                $aPrizes[$v['level']]           = $v['prize'];
                $sAllNums                       = $v['expandcode'];  
            }
        }
        $aExpandData = array_merge( $aTempExpandData['isspecial'], $aTempExpandData['normal'] );
        foreach( $aExpandData as $v )
        {//根据不同奖金组进行检测
            $aTempNums = explode( "|", $v['expandcode'] ); //该组奖金里面的原式号码集
            switch( $sMethod )
            {//根据不同玩法做不同检测
                case 'ZX'      : //同下
                case 'ZXHZ'    : if( TRUE !== ($aResult = $this->checkMaxLostByNo_ExpandNo($aLimitNum, 
                                 $aLimitValue, $aTempNums, $v['prize'], $fMaxLost, $fSalesMoney)) )
                                 {//如果有限制号直接中断返回号码
                                     return $aResult;
                                     break;
                                 }
                                 break;
                case 'TX'      : if( $v['level'] == 1 )
                                 {//只检查一等奖[二、三等奖的检测方式一样]
                                 	 if( empty($aLimitNum) )
                                 	 {//没有要检查的限制号码则直接退出循环
                                 	 	return TRUE; break;
                                 	 }
                                     foreach( $aLimitValue as $num=>$fLost )
                                     {
                                        $fLastPrize = $this->getCodeSendPrize($iMethodId, $sAllNums, $num, $aPrizes );
                                        if( ($fLost + $fLastPrize) > ($fMaxLost + $fSalesMoney) )
                                        {
                                            return $num;
                                            break;
                                        }
                                     }
                                 }
                                 
                                 break;
                case 'ZS'      : //同下
                case 'ZL'      : if( $v['isspecial'] == 1 )
                                 {//如果为变价的号码则为展开式[全展开]
                                     if( TRUE !== ($aResult = $this->checkMaxLostByNo_ExpandNo($aLimitNum, 
                                     $aLimitValue, $aTempNums, $v['prize'], $fMaxLost, $fSalesMoney)) )
                                     {//如果有限制号直接中断返回号码
                                         return $aResult;
                                         break;
                                     }
                                 }
                                 else
                                 {//不是变价则直接存原复式只会有一组号码[不存在重复号码则所有限号都在该范围内]
                                    foreach( $aLimitNum as $num )
                                    {
                                        if( ($aLimitValue[$num] + $v['prize']) > ($fMaxLost + $fSalesMoney) )
                                        {
                                            return $num;
                                            break;
                                        }
                                    }
                                 }
                                 break;
                case 'HHZX'    : if( $v['isspecial'] == 1 )
                                 {//如果为变价的号码则为展开式[全展开]
                                     if( TRUE !== ($aResult = $this->checkMaxLostByNo_ExpandNo($aLimitNum, 
                                     $aLimitValue, $aTempNums, $v['prize'], $fMaxLost, $fSalesMoney)) )
                                     {//如果有限制号直接中断返回号码
                                         return $aResult;
                                         break;
                                     }
                                 }
                                 else
                                 {//把所有号码转直['ZS'=>,'ZL']
                                    $aTempNums = $this->getExtendCodeHHZX( $v['expandcode'] );
                                    if( empty($aTempNums) )
                                    {//展开号码有误
                                        return FALSE;
                                        break;
                                    }
                                    //第二部做检测[1等奖做组三检测，二等奖做组六检测]
                                    $aTempNums = $v['level'] == 1 ? $aTempNums['ZS'] : $aTempNums['ZL'];
                                    $aResult = array_intersect($aLimitNum, $aTempNums); //交集
                                    if( empty($aResult) )
                                    {//不存在交集，则在该组奖金里面有没有查出来的号码
                                       break;
                                    }
                                    else
                                    {//存在交集，则在该组中有查出来的号码
                                       foreach( $aResult as $num )
                                       {
                                           if( ($aLimitValue[$num] + $v['prize']) > ($fMaxLost + $fSalesMoney) )
                                           {//如果超过了封锁：当前封锁+该组奖金 > 最大封锁 + 销量
                                               return $num;
                                               break;
                                           }
                                       }
                                       //把已检查过的号码从限号中踢出
                                       $aLimitNum = array_diff( $aLimitNum, $aResult ); 
                                    }
                                 }
                                 break;
                case 'ZUXHZ'   : if( $v['isspecial'] == 1 )
                                 {//如果为变价的号码则为展开式[全展开]
                                 	 
                                     if( TRUE !== ($aResult = $this->checkMaxLostByNo_ExpandNo($aLimitNum, 
                                     $aLimitValue, $aTempNums, $v['prize'], $fMaxLost, $fSalesMoney)) )
                                     {//如果有限制号直接中断返回号码
                                         return $aResult;
                                         break;
                                     }
                                     $aLimitNumZS = array_intersect( $aLimitNumZS, $aLimitNum );
                                     $aLimitNumZL = array_intersect( $aLimitNumZL, $aLimitNum );
                                 }
                                 else
                                 {//分类做检测[1等奖做组三检测，二等奖做组六检测]
                                    $aTempNums = $v['level'] == 1 ? $aLimitNumZS : $aLimitNumZL;
                                    foreach( $aTempNums as $num )
                                    {
                                    	if( ($aLimitValue[$num] + $v['prize']) > ($fMaxLost + $fSalesMoney) )
                                    	{
                                    		return $num;
                                    		break;
                                    	}
                                    }
                                 }
                                 break;
                case 'YMBDW'   : //同下
                case 'EMBDW'   : if( $v['isspecial'] == 1 )
                                 {//如果为变价的号码则为展开式[全展开]
                                     $aResult = array_intersect($aLimitNum, $aTempNums); //交集
						             foreach( $aResult as $num )
						             {
						             	$fLastPrize = $this->getCodeSendPrize($iMethodId, $sAllNums, $num, 
						             	              array(1=>$v['prize']) );
						                 if( ($aLimitValue[$num] + $fLastPrize) > ($fMaxLost + $fSalesMoney) )
						                 {//如果超过了封锁：当前封锁+该组奖金 > 最大封锁 + 销量
						                     return $num;
						                     break;
						                 }
						             }
						             $aLimitNum = array_diff( $aLimitNum, $aResult ); //把已检查过的号码从限号中踢出
                                 }
                                 else
                                 {//不是变价则直接存原复式只会有一组号码[不存在重复号码则所有限号都在该范围内]
                                    foreach( $aLimitNum as $num )
                                    {
                                    	$fLastPrize = $this->getCodeSendPrize($iMethodId, $sAllNums, $num, 
                                                      array(1=>$v['prize']) );
                                        if( ($aLimitValue[$num] + $fLastPrize) > ($fMaxLost + $fSalesMoney) )
                                        {
                                            return $num;
                                            break;
                                        }
                                    }
                                 }
                                 break;
                
                case 'QEZX'    : //同下
                case 'HEZX'    : //同下
                case 'QEZUX'   : //同下
                case 'HEZUX'   : //同下
                case 'P5HEZX'  : //同下
                case 'P5HEZUX' : //同下
                case 'DBW'     : //同下
                case 'P5DWW'   : //同下
                case 'DSW'     : //同下
                case 'P5DQW'   : //同下
                case 'DGW'     : //同下
                case 'P5DBW'   : //同下
                case 'P5DSW'   : //同下
                case 'P5DGW'   : if( $v['isspecial'] == 1 )
                                 {//如果为变价的号码则为展开式[全展开]
                                     if( TRUE !== ($aResult = $this->checkMaxLostByNo_ExpandNo($aLimitNum, 
                                     $aLimitValue, $aTempNums, $v['prize'], $fMaxLost, $fSalesMoney)) )
                                     {//如果有限制号直接中断返回号码
                                         return $aResult;
                                         break;
                                     }
                                 }
                                 else
                                 {//不是变价则直接存原复式只会有一组号码[不存在重复号码则所有限号都在该范围内]
                                    foreach( $aLimitNum as $num )
                                    {
                                        if( ($aLimitValue[$num] + $v['prize']) > ($fMaxLost + $fSalesMoney) )
                                        {
                                            return $num;
                                            break;
                                        }
                                    }
                                 }
                                 break;
                case 'QEDXDS'  : //同下
                case 'P5HEDXDS': //同下
                case 'HEDXDS'  : if( $v['isspecial'] == 1 )
                                 {//如果为变价的号码则为展开式[全展开]
                                     if( TRUE !== ($aResult = $this->checkMaxLostByNo_ExpandNo($aLimitNum, 
                                     $aLimitValue, $aTempNums, $v['prize'], $fMaxLost, $fSalesMoney)) )
                                     {//如果有限制号直接中断返回号码
                                         return $aResult;
                                         break;
                                     }
                                 }
                                 else
                                 {//不是变价则则会存多单形式[123#234|123#234]
                                     $aTemp_Seach = array();
                                     if( count($aTempNums) > 1 )
                                     {//多于一组 
                                        foreach( $aTempNums as $order )
                                        {//每一组相当于一单
                                            $aTemp_nums = explode("#",$order);
                                            $aTemp_Seach[] = "([".$aTemp_nums[0]."][".$aTemp_nums[1]."])";
                                        }
                                     }
                                     else 
                                     {//只有一组
                                        $aTemp_nums    = explode("#",$aTempNums[0]);
                                        $aTemp_Seach[] = "[".$aTemp_nums[0]."][".$aTemp_nums[1]."]";
                                     }
                                     $sPregString = count($aTemp_Seach) > 1 ? "(".implode("|",$aTemp_Seach).")"
                                                    : $aTemp_Seach[0];
                                     if( $sMethod == "HEDXDS" )
                                     {//后二取后两位
                                        $sPregString = "/".$sPregString."$/";
                                     }
                                     else
                                     {//取前两位
                                        $sPregString = "/^".$sPregString."/";
                                     }
                                     $aTemp_Seach = array();
                                     foreach( $aLimitNum as $num )
                                     {
                                        if( preg_match($sPregString, $num) )
                                        {//如果号码在该组，则判断是否超线
                                           if( ($aLimitValue[$num] + $v['prize']) > ($fMaxLost + $fSalesMoney) )
                                           {//如果超过了封锁：当前封锁+该组奖金 > 最大封锁 + 销量
                                               return $num;
                                               break;
                                           }
                                           $aTemp_Seach[] =$num; //保存匹配的
                                        }
                                     }
                                     //把已检查过的号码从限号中踢出
                                     $aLimitNum = array_diff( $aLimitNum, $aTemp_Seach );
                                 }
                                 break;
                default        : return FALSE; break;
            }
        }
        return TRUE;
    }
    private function checkMaxLostByNo_ExpandNo( &$aLimitNum, $aLimitValue, $aExpandNo, $fPrize, $fMaxLost, $fSales )
    {//内部调用，跳过数据完整性检测[没有超过封锁的则返回TRUE，有则返回号码]
        $aResult = array_intersect($aLimitNum, $aExpandNo); //交集
        if( empty($aResult) )
        {//不存在交集，则在该组奖金里面有没有查出来的号码
           return TRUE;
        }
        else
        {//存在交集，则在该组中有查出来的号码
           foreach( $aResult as $num )
           {
               if( ($aLimitValue[$num] + $fPrize) > ($fMaxLost + $fSales) )
               {//如果超过了封锁：当前封锁+该组奖金 > 最大封锁 + 销量
                   return $num;
                   break;
               }
           }
           $aLimitNum = array_diff( $aLimitNum, $aResult ); //把已检查过的号码从限号中踢出
           return TRUE; //所有检查完以后如果没有超过封锁的则返回TRUE
        }
    }
    
   
    //直选展开[包括直选、直选和值、通选]算多倍号码
    private function & getExpandCodeZX( $sNums )
    {
        $aResult = array();
        if( empty($sNums) )
        {
            return $aResult;
        }
        $aNumArr = explode( "|", $sNums );
        if( empty($aNumArr) )
        {
            return $aResult;
        }
        $aNumArr = array_count_values($aNumArr);//算出重复的
        foreach( $aNumArr as $k=>$v )
        {
            $aNumArr[$k]            = array();
            $aNumArr[$k]['times']   = $v;
            $aNumArr[$k]['nums'][]  = $k; 
        }
        return $aNumArr;
    }
    
    
    /**
     * 投注号码检测[按相应玩法规则]
     *
     * @author  james    090814
     * @access  private
     * @param   int      $iMethodId
     * @param   string   $sNums
     * @param   int   	 $iTotalNum
     * @return  boolean TRUE,FALSE
     */
    private function checkCode( $iMethodId, & $sNums, $iTotalNum )
    {
        if( empty($iMethodId) || !isset($this->_aMethod_Config[$iMethodId]) )
        {
            return FALSE;
        }
        $sMethod = $this->_aMethod_Config[$iMethodId];
        if( $sNums == "" )
        {
            return FALSE;
        }
        $iTotalCountNum = 0;
        switch( $sMethod )
        {
            case 'ZX'      : //同下
            case 'ZXHZ'    : //同下
            case 'TX'      : if( !preg_match("/^([0-9]{3}\|)*[0-9]{3}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             // 检查注数
                             $aNums = explode('|', $sNums);
                             $iTotalCountNum = count($aNums);
                             break;
            case 'ZS'      : if( !preg_match("/^([0-9]){2,10}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $aNums = str_split($sNums);
                             $iCodeCount = count(array_unique($aNums));
                             if (count($aNums) != $iCodeCount){
                             	return FALSE;
                             }
                             if($iCodeCount < 2)
		                     {
		                        return FALSE;
		                     }
                             $iTotalCountNum = $iCodeCount * ($iCodeCount - 1);
                             break;
            case 'ZL'      : if( !preg_match("/^([0-9]){3,10}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $aNums = str_split($sNums);
                             $iCodeCount = count(array_unique($aNums));
                             if (count($aNums) != $iCodeCount){
                             	return FALSE;
                             }
                             if($iCodeCount < 3)
		                     {
		                        return FALSE;
		                     }
                             $iTotalCountNum = $iCodeCount * ($iCodeCount - 1) * ($iCodeCount - 2) / 6;
                             break;
            case 'HHZX'    : if( !preg_match("/^([0-9]{3}\|)*[0-9]{3}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $aNums = explode('|', $sNums);
                             $iTotalCountNum = count($aNums);
                             $aBaoZiCode3 = array('000','111','222','333','444','555','666','777','888','999');
		                     if( count(array_intersect($aBaoZiCode3,$aNums)) > 0 )
		                     {
		                        return FALSE;
		                     }
                             break;
            case 'ZUXHZ'   : if( !preg_match("/^([0-9]{1,2}\|)*[0-9]{1,2}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $aNums = explode('|', $sNums);
                             $iNums = 0;
                             $aTemp = array(1=>1,2=>2,3=>2,4=>4,5=>5,6=>6,7=>8,8=>10,9=>11,10=>13,11=>14,12=>14,13=>15,14=>15,15=>14,16=>14,17=>13,18=>11,19=>10,20=>8,21=>6,22=>5,23=>4,24=>2,25=>2,26=>1);
                             foreach ($aNums as $sCode)
		                    {
		                        if( !isset($aTemp[$sCode]) )
		                        {
		                            return FALSE;
		                        }
		                        $iNums += $aTemp[$sCode];
		                    }
                             $iTotalCountNum = $iNums;
                             break;
            case 'YMBDW'   : if( !preg_match("/^([0-9]{1}\|){0,9}[0-9]{1}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $aNums = explode('|', $sNums);
                             $aTemp = array_unique($aNums);
                             if (count($aNums) != count($aTemp)){
                             	return FALSE;
                             }
                             $iTotalCountNum = count($aTemp);
                             if($iTotalCountNum < 1)
		                     {
		                        return FALSE;
		                     }
//                             $iTotalCountNum = count($aNums);
                             break;
            case 'EMBDW'   : if( !preg_match("/^([0-9]){2,10}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $aNums = str_split($sNums);
                             $iCodeCount = count(array_unique($aNums));
                             if (count($aNums) != $iCodeCount){
                             	return FALSE;
                             }
                             if($iCodeCount < 2)
		                     {
		                        return FALSE;
		                     }
                             $iTotalCountNum = $iCodeCount * ($iCodeCount - 1) / 2;
                             break;
            case 'QEZX'    : //同下
            case 'HEZX'    : //同下
            case 'P5HEZX'  : if( !preg_match("/^([0-9]){1,10}\|([0-9]){1,10}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $iTempCount = 1;
                             $aNums = explode('|', $sNums);
                             foreach ($aNums as $k => $v){
                             	$iCodeCount = count(array_unique(str_split($v)));
                             	if($iCodeCount < 1)
			                     {
			                        return FALSE;
			                     }
			                    $iTempCount *=  $iCodeCount;
                             }
                             $iTotalCountNum = $iTempCount;
                             break;
            case 'QEZUX'   : //同下
            case 'P5HEZUX' : //同下
            case 'HEZUX'   : if( !preg_match("/^([0-9]){2,10}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $aNums = str_split($sNums);
                             $iCodeCount = count(array_unique($aNums));
                             if (count($aNums) != $iCodeCount){
                             	return FALSE;
                             }
                             $iTotalCountNum = $iCodeCount * ($iCodeCount - 1) / 2;
                             break;
            case 'P5DWW'   : //同下
            case 'P5DQW'   : //同下
            case 'P5DBW'   : //同下
            case 'P5DSW'   : //同下
            case 'P5DGW'   : //同下
            case 'DBW'     : //同下
            case 'DSW'     : //同下
            case 'DGW'     : if( !preg_match("/^([0-9]{1}\|)*[0-9]{1}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $aNums = explode('|', $sNums);
                             $iTotalCountNum = count($aNums);
                             break;
            case 'QEDXDS'  : //同下
            case 'P5HEDXDS': //同下
            case 'HEDXDS'  : $sNums = str_replace(array("大","小","单","双"), array("B","S","A","D"), $sNums);
                             if( !preg_match("/^([BSAD]){1,4}\|([BSAD]){1,4}$/", $sNums) )
                             {
                                 return FALSE;
                             }
                             $iTempCount = 1;
                             $aNums = explode('|', $sNums);
                             foreach ($aNums as $k => $v){
                             	$aTemp = str_split($v);
                             	$iCodeCount = count(array_unique($aTemp));
                             	if (count($aTemp) != $iCodeCount){
                             		return FALSE;
                             	}
                             	if($iCodeCount < 1)
			                     {
			                        return FALSE;
			                     }
			                    $iTempCount *=  $iCodeCount;
                             }
                             $iTotalCountNum = $iTempCount;
                             break;
            default        : return FALSE; break;
        }
        if ($iTotalCountNum !== intval($iTotalNum)){
        	return FALSE;
        }
        return TRUE;
    }
    
}
?>