<?php
/**
 * 文件 : /_app/model/gamespecial.php
 * 功能 : 数据模型 - 用户投单处理模型
 *
 * - dynamicBuy()       购买靓号区号码
 * - getDynamicCode()   获取所有号码的奖金情况
 * - sortTableRow()     对数据进行竖排表格格式化
 * 
 * @author     james    090915
 * @version    1.2.0
 * @package    lowgame     
 */

class model_gamespecial extends model_gamebase
{
	/* 私有变量定义[允许玩靓号的玩法ID]
     * @var array
     */
    private $_aPermitMethodId  = array(9,10,37,38);
	
    /**
     * 构造函数
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    /**
     * 购买靓号区号码
     *
     * @author  james 090827
     * @access  public
     * @param   int      $iUserId   //用户ID
     * @param   int      $iLotteryId //彩种ID
     * @param   int      $iMethodId //玩法ID
     * @param   string   $sIssue    //奖期
     * @param   array    $aCodes    //购买号码与倍数关系数组
     * @param   int      $iChoice   //如果有下调的是否继续购买 1停止，2继续[只买涨价的]
     * @return  mixed
     */
    public function dynamicBuy( $iUserId, $iLotteryId, $iMethodId, $sIssue, $aCodes, $iChoice )
    {
    	$iSinglePrice  = 2; //单注单倍投注金额
    	/**
         * 01: 数据完整性检测=========================================================
         */
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
        if( !in_array($iMethodId, $this->_aPermitMethodId) )
        {//不允许玩靓号
            return ajaxMsg("error", "温馨提示：靓号尚未开售，请稍后再试。");
        }
        if( empty($sIssue) )
        {//奖期
        	return ajaxMsg("error", "操作错误");
        }
        if( empty($aCodes) || !is_array($aCodes) )
        {//购买号码
        	return ajaxMsg("error", "请选择投注号码");
        }
        if( $iChoice != 1 && $iChoice != 2 )
        {
        	return ajaxMsg("error", "操作错误");
        }
        
        /**
         * 02: 对购买号码以及倍数进行分类处理
         */
        $aCodeAll     = array(); //所有购买号码
        $aCodeNew     = array(); //把所有相同倍数的号码整合在一起
        $iTotalAmount = 0;       //总投注金额
        foreach( $aCodes as $k=>$v )
        {
        	$aCodeAll[]             = $k;
        	$aCodeNew[intval($v)][] = $k;
        	$iTotalAmount          += ($iSinglePrice * intval($v));
        }
        $aCodeAll = array_unique($aCodeAll);
        
        /**
         * 03: 判断玩法和奖金权限========================================================
         */
        //0301:读取玩法信息
        $oMethod     = A::singleton('model_method');
        $sFields     = "m.`lotteryid`,m.`methodid`,m.`methodname`,m.`pid`,m.`isprizedynamic`,
                        m.`nocount`,m.`totalmoney`,l.`lockname`,l.`maxlost`,ltt.`cnname`,
                        ltt.`adjustminprofit`,ltt.`adjustmaxpercent`";
        $sCondition  = " m.`methodid`='".$iMethodId."' AND m.`lotteryid`='".$iLotteryId."' AND m.`isclose`='0' ";
        $sLeftJoin   = " LEFT JOIN `locksname` AS l ON m.`locksid`=l.`locksid`
                         LEFT JOIN `lottery` AS ltt ON m.`lotteryid`=ltt.`lotteryid` ";
        $aMethodData = $oMethod->methodGetInfo( $sFields, $sCondition, $sLeftJoin );
        if( empty($aMethodData) )
        {//无数据[玩法不存在或者已关闭]
            return ajaxMsg("error", "没有权限");
        }
        $aMethodData = $aMethodData[0];
        if( $aMethodData['isprizedynamic'] != 1 || empty($aMethodData['lockname']) )
        {//没有启用动态调价或者没有封锁
            return ajaxMsg("error", "温馨提示：靓号尚未开售，请稍后再试。");
        }
        $aMethodData['nocount'] = unserialize($aMethodData['nocount']);
        
        //0302:读取奖金组信息以及用户返点和总代返点
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
        $fMaxPrize    = $aMethodData['prize'][$aPrizeLevel[0]] ;//最大奖金
        $iNumCount    = $aMethodData['nocount'][$aPrizeLevel[0]]['count'];//转直注数
        if( $iNumCount != 1 || count($aPrizeLevel) != 1 )
        {//确定只有直选才能使用
            return ajaxMsg("error", "温馨提示：靓号尚未开售，请稍后再试。");
        }
        
        /**
         * 03: 获取当前期信息=============================================================
         */
        $iNowTime   = time();     //设置当前时间，避免后面多次调用
        $oIssue     = A::singleton('model_issueinfo');
        $sFields    = "A.`issueid`,A.`issue`,A.`salestart`,A.`saleend`,A.`dynamicprizestart`,A.`dynamicprizeend`";
        $sCondition = "A.`issue`='".daddslashes($sIssue)."' AND A.`lotteryid`='".$iLotteryId."'";
        $aIssueInfo = $oIssue->IssueGetOne( $sFields, $sCondition );
        if( empty($aIssueInfo) )
        {//0301: 判断当前期是否存在
            return ajaxMsg("error", "操作错误");
        }
        if( strtotime($aIssueInfo['saleend']) < $iNowTime )
        {//0302:检测是否已停止销售
            return ajaxMsg("error", "第[".$sIssue."]期已停止销售");
        }
        if( strtotime($aIssueInfo['salestart']) > $iNowTime )
        {//0303:检测是否还未开始销售
            return ajaxMsg("error", "第[".$sIssue."]期未到销售时间");
        }
        if( strtotime($aIssueInfo['dynamicprizestart']) > $iNowTime 
            || strtotime($aIssueInfo['dynamicprizeend']) < $iNowTime )
        {//0304:检测是否到调价时间
            return ajaxMsg("error", "温馨提示：靓号尚未开售，请稍后再试。");
        }
        
        /**
         * 03: 获取本单的最高奖金限额====================================
         */
        $fSysMaxPrize = intval(getConfigValue( 'limitbonus', 100000 )); //系统同单投注最高奖金限制
        //03 01：获取用户相同单已拥有的最高奖金   ----------------------------------------------
        $oProjects    = A::singleton('model_projects'); //方案模型
        $aUserHadPrizes = $oProjects->getUserPrizesBySameCode( $iUserId, $aMethodData['lotteryid'], 
                                     $aMethodData['methodid'], $aIssueInfo['issue'], implode("|",$aCodeAll) );
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
        unset($aUserHadPrizes,$fUserHadPrizes,$aTempUser_Prize);
        
        
        /**
         * 05 : 获取所有上级返点并计算返点差=====================================================
         */
        //先判断是否为总代的直接下级
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
        ksort($aUserPoint); //按用户ID排序[总代->一代->.....->用户]
        $aUserDiffPoints = array(); //所有用户返点差记录
        $iTempLastUserId = 0;
        foreach( $aUserPoint as $k=>$v )
        {//计算返点差
            if( $iTempLastUserId == 0 )
            {
                $iTempLastUserId = $k;
            }
            else
            {
                $aUserDiffPoints[$iTempLastUserId]['userid']    = $iTempLastUserId;
                $aUserDiffPoints[$iTempLastUserId]['diffpoint'] = round(floatval($aUserPoint[$iTempLastUserId] - $v),4);
                if( $aUserDiffPoints[$iTempLastUserId]['diffpoint'] < 0 )
                {//返点差出现负数，即返点设置有错误
                    return ajaxMsg("error", "操作错误");
                }
                $iTempLastUserId = $k;
            }
        }
        //最后一个用户即自身
        $aUserDiffPoints[$iTempLastUserId]['userid']    = $iTempLastUserId;
        $aUserDiffPoints[$iTempLastUserId]['diffpoint'] = $aUserPoint[$iTempLastUserId];
        unset($iTempLastUserId, $aUserPoint);   //释放内存
        
        /**
         * 06: 计算每个号码的最终奖金，以及封锁判断等========================================
         */
        // 06 01：获取调价方案
        $sSql = " SELECT `groupid`,`winline`,`loseline` FROM `adjustprice` 
                  WHERE `lotteryid`='".$iLotteryId."' AND `isverify`='1' AND `isactive`='1' LIMIT 1 ";
        $aAdjustprice = $this->oDB->getOne($sSql);
        if( empty($aAdjustprice) )
        {//没有调价方案
            return ajaxMsg("error", "温馨提示：靓号尚未开售，请稍后再试。");
        }
        //06 02: 获取调价线[只获取上调奖金的]
        $sSql = " SELECT `uplimit`,`percent`,`isup` FROM `adjustprizedetail`
                  WHERE `groupid`='".$aAdjustprice['groupid']."' AND `isup`='1' ORDER BY `uplimit` ASC ";
        $aUpDetail = $this->oDB->getAll($sSql);
        if( empty($aUpDetail) )
        {//没有具体调价线
            return ajaxMsg("error", "温馨提示：靓号尚未开售，请稍后再试。");
        }
        //06 03： 获取当前期的销售额
        $oLocks     = A::singleton('model_locks');
        $fSaleMoney = $oLocks->salesGetMoneys( $iLotteryId, $aIssueInfo['issue'], $aMethodData['lockname'] );
        if( $fSaleMoney === FALSE )
        {
            return ajaxMsg("error", "操作错误");
        }
        //06 04: 获取购买号码的当前封锁值
        $sSql = " SELECT SUM(`prizes`) AS sumprizes,`code` FROM `".$aMethodData['lockname']."` 
                  WHERE `issue`='".$aIssueInfo['issue']."' AND `code` IN(".implode(",",$aCodeAll).") GROUP BY `code`";
        $aCodeLocks = $this->oDB->getAll($sSql);
        if( empty($aCodeLocks) || count($aCodeLocks) != count($aCodeAll) )
        {
        	return ajaxMsg("error", "操作错误");
        }
        $aNoUpCode  = array(); //奖金不变动或者下调的号码
        $aCodePrize = array(); //号码和最终奖金且已分组的数据
        //06 05: 算号码最终奖金并组建号码扩展表数据 [只算上调奖金的]
        //升价：单倍最终奖金 = 单倍奖金  + ((全包金额*(1-总代返点-公司极限留水)/转直注数)-单倍奖金)*调价比例
        $fTempUpMax   = (($aMethodData['totalmoney']*(1-$aMethodData['topuserpoint']-$aMethodData['adjustminprofit']))
                        /$iNumCount) - $fMaxPrize;
        foreach( $aCodeLocks as $v )
        {
           $fLastPrize = 0; //调价后的最终奖金
           $iBeginLine = 0; //开始调价线
           if( $v['sumprizes'] < ($fSaleMoney - $aAdjustprice['winline']) )
           {//上调奖金
               foreach( $aUpDetail as $kk=>$vv )
               {//06 05 00: 获取符合最终的变价线
                   if( $v['sumprizes'] < ($fSaleMoney - $vv['uplimit']) )
                   {
                       $iBeginLine = $kk;
                   }
               }
               //06 05 01: 算出最终该号码的单倍奖金
               $fLastPrize = $fMaxPrize + round(($fTempUpMax * $aUpDetail[$iBeginLine]['percent']),2);
               //06 05 02: 加上倍数的最终奖金
               $fLastPrize = $fLastPrize * (isset($aCodes[$v['code']]) ? intval($aCodes[$v['code']]) : 0);
               //06 05 03: 检测是否超过封锁
               if( ($v['sumprizes'] + $fLastPrize) > ($fSaleMoney + $aMethodData['maxlost']) )
               {//超过封锁[倍数太大造成]
               	   return ajaxMsg("error", "号码: ".$v['code']."  购买倍数太多");
               }
               //06 05 04: 是否跨了两条线
               if( isset($aUpDetail[$iBeginLine+2]) && 
                   ($v['sumprizes']+$fLastPrize) < ($fSaleMoney + $iTotalAmount - $aUpDetail[$iBeginLine+2]['uplimit']) )
               {//跨了两条线
                   return ajaxMsg("error", "号码: ".$v['code']."  购买倍数太多");
               }
               //06 05 05: 保存该号码
               $aCodePrize["".$fLastPrize][] = $v['code'];
           }
           else 
           {//奖金不变动或者下调
               $aNoUpCode[] = $v['code'];
           }
        }
        //06 06: 检测是否有奖金不是上调的，如果有根据条件返回或继续
        if( !empty($aNoUpCode) && $iChoice == 2 )
        {//如果有并且选择的取消购买则返回号码
        	return ajaxMsg("error", "已取消购买<br /><br />以下号码奖金小于或等于不变价时的奖金[".$fMaxPrize."]
        	                         <br /><br />".implode(",",$aNoUpCode));
        }
        //06 07: 检测是否还有可以买的号码
        if( empty($aCodePrize) )
        {
        	return ajaxMsg("error", "没有上调奖金的号码");
        }
        
        /**
        * 07 ： 构建基本的数据
        */
        //07 01：封锁数据 [根据具体情况变动]------------------------------
        $aLocksData   = array(); //默认为空,根据情况填充
        //07 02: 方案表插入数据[固定] ------------------------
        $aProjectData = array(
                          'userid'       => $iUserId,
                          'lotteryid'    => $aMethodData['lotteryid'],
                          'methodid'     => $aMethodData['methodid'],
                          'issue'        => $aIssueInfo['issue'],
                          'isdynamicprize'=> 1, //动态调价不允许用户撤单
                          'code'         => implode("|",$aCodeAll),
                          'singleprice'  => $iTotalAmount,
                          'multiple'     => 1,//[按每个号码一倍，有重复号码计算]
                          'totalprice'   => $iTotalAmount,
                          'lvtopid'      => $iTopProxyId,
                          'lvtoppoint'   => $aMethodData['topuserpoint'],
                          'lvproxyid'    => $iLvProxyId
                        );
        //07 03: 加入游戏帐变插入数据[固定] --------------------------
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
        //07 04: 本人销售返点数据 [固定]-------------------------------
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
        //07 05: 号码扩展基本数据[同时完善封锁表数据以及判断是否超过奖金限额]--------------------
        $aExpandData    = array();  //号码扩展数据
        $fMaxTotalPrize = 0; //在此单中的最高奖金，默认为0
        $iThreadId      = intval($this->oDB->getThreadId()) % 5;//五个线程
        foreach( $aCodePrize as $k=>$v )
        {
        	$aTemp = array();
            $aTemp['projectid'] = 0; //等待方案表插入ID
            $aTemp['isspecial'] = 1; //只有在动态调价才为1
            $aTemp['level']     = $aPrizeLevel[0]; //奖级
            $aTemp['codetimes'] = isset($aCodes[$v[0]]) ? $aCodes[$v[0]] : 1;    //号码倍数
            $aTemp['prize']     = floatval($k); //最终奖金
            $aTemp['expandcode']= implode("|",$v);//以|分隔多单
            $aExpandData[]      = $aTemp;
            $fMaxTotalPrize     = $fMaxTotalPrize < $aTemp['prize'] ? $aTemp['prize'] : $fMaxTotalPrize;
            $aLocksData[]       = " UPDATE `".$aMethodData['lockname']."` SET `prizes`=`prizes`+".$aTemp['prize']." 
                                    WHERE `issue`='".$aIssueInfo['issue']."' AND `code` IN(".implode(",",$v).")
                                    AND `threadid`='".$iThreadId."' ";
        }
        if( $fMaxTotalPrize > $fSysMaxPrize )
        {//超过奖金限额
        	return ajaxMsg("error", "购买的号码超过奖金限额");
        }
        //07 06: 返点差表插入数据---------------------------------
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
        //07 07: 销量表数据[根据是否封锁做局部调整]    ---------------------------------------
        $aSaleData = array(
                   'issue'     => $aIssueInfo['issue'],
                   'lotteryid' => $aMethodData['lotteryid'],
                   'TFWLname'  => $aMethodData['lockname'], //封锁表名称
                   'moneys'    => $iTotalAmount * (1-$aMethodData['topuserpoint']) //全部价格 - 返点总金额
                    );
                    
        /**
         * 08: 开始写入数据 ========================================================
         */
        $oUserFund    = A::singleton('model_userfund'); //用户资金模型
        //08 01: 锁用户资金表[开始锁资金事务处理]---------------
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
        
        //08 02: [开始投单流程事务处理]------------
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
        //08 03: 提交投单流程事务处理[结束] -----------------------
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return ajaxMsg("error", "系统错误：错误编号:#5013");
        }
        //08 04: 解锁资金表 -------------------------------------
        $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );
        $aBuyCode = array_diff( $aCodeAll, $aNoUpCode );
        return ajaxMsg("success", "购买成功, 购买号码如下：<br /><br />".implode(",",$aBuyCode));
        //处理完成
    }
    
    
    
    /**
     * 获取所有号码的奖金情况
     *
     * @author  james    090821
     * @param   int      $iUserId
     * @param   int      $iLotteryId
     * @param   int      $iMethodId
     * @param   string   $sIssue
     * @return  mixed
     * -------------------------------------------------------
     * 0:参数错误，-1：没有权限，-2：该功能未启用，-3：系统错误，array号码对应奖金数组
     */
    public function getDynamicCode( $iUserId, $iLotteryId, $iMethodId, $bGroup=TRUE )
    {
    	/**
         * 01: 数据完整性检测=========================================================
         */
    	if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
        {//用户ID
            return 0;
        }
        $iUserId = intval($iUserId);
        if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId <= 0 )
        {//彩种ID
            return 0;
        }
        $iLotteryId = intval($iLotteryId);
        if( empty($iMethodId) || !is_numeric($iMethodId) || $iMethodId <= 0 )
        {//玩法ID
            return 0;
        }
        $iMethodId = intval($iMethodId);
        if( !in_array($iMethodId, $this->_aPermitMethodId) )
        {
        	return -2;
        }
        $bGroup    = $bGroup === TRUE ? TRUE : FALSE;//是否分组
    	/**
         * 02: 判断玩法和奖金权限========================================================
         */
        //0201:读取玩法信息
        /* @var $oMethod model_method */
        $oMethod     = A::singleton('model_method');
        $sFields     = "m.`lotteryid`,m.`methodid`,m.`methodname`,m.`pid`,m.`isprizedynamic`,
                        m.`nocount`,m.`totalmoney`,l.`lockname`,l.`maxlost`,ltt.`cnname`,
                        ltt.`adjustminprofit`,ltt.`adjustmaxpercent`";
        $sCondition  = " m.`methodid`='".$iMethodId."' AND m.`lotteryid`='".$iLotteryId."' AND m.`isclose`='0' ";
        $sLeftJoin   = " LEFT JOIN `locksname` AS l ON m.`locksid`=l.`locksid`
                         LEFT JOIN `lottery` AS ltt ON m.`lotteryid`=ltt.`lotteryid` ";
        $aMethodData = $oMethod->methodGetInfo( $sFields, $sCondition, $sLeftJoin );
        if( empty($aMethodData) )
        {//无数据[玩法不存在或者已关闭]
            return -1;
        }
        $aMethodData = $aMethodData[0];
        if( $aMethodData['isprizedynamic'] != 1 || empty($aMethodData['lockname']) )
        {//没有启用动态调价或者没有封锁
        	return -2;
        }
        $aMethodData['nocount'] = unserialize($aMethodData['nocount']);
        
        //0202:读取奖金组信息以及用户返点和总代返点
        /* @var $oUserMethod model_usermethodset */
        $oUserMethod = A::singleton('model_usermethodset');
        $sFields     = "m.`methodid`,upl.`level`,upl.`prize`,ums.`userpoint`,upl.`userpoint` AS `topuserpoint`";
        $sCondition  = " AND m.`methodid`='".$aMethodData['pid']."' ";
        $aPrizeData  = $oUserMethod->getUserMethodPrize( $iUserId, $sFields, $sCondition, FALSE );
        if( empty($aPrizeData) )
        {//无奖金信息
            return -1;
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
            return -1;
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
            return -1;
        }
        $aMethodData['topuserpoint'] = $aPrizeData[0]['topuserpoint'];  //总代返点[可能为0]
        unset($aPrizeData);
        
        
        /**
         * 03: 获取单倍最高奖金[已此奖金显示奖金值]和转直注数====================================
         */
        $fMaxPrize    = $aMethodData['prize'][$aPrizeLevel[0]] ;//最大奖金
        $iNumCount    = $aMethodData['nocount'][$aPrizeLevel[0]]['count'];
        if( $iNumCount != 1 || count($aPrizeLevel) != 1 )
        {//确定只有直选才能使用
        	return -1;
        }
        
        /**
         * 04: 获取当前期信息=============================================================
         */
        $iNowTime   = time();     //设置当前时间，避免后面多次调用
        /* @var $oIssue model_issueinfo */
        $oIssue     = A::singleton('model_issueinfo');
        $aIssueInfo = $oIssue->getCurrentIssue( $aMethodData['lotteryid'] );
        if( empty($aIssueInfo) )
        {//0401: 判断当前期是否存在
            return -2;
        }
        if( strtotime($aIssueInfo['dynamicprizestart']) > $iNowTime 
            || strtotime($aIssueInfo['dynamicprizeend']) < $iNowTime )
        {//0402:检测是否到调价时间
            return -2;
        }
        
        // 05：获取调价方案
        $sSql = " SELECT `groupid`,`winline`,`loseline` FROM `adjustprice` 
                  WHERE `lotteryid`='".$iLotteryId."' AND `isverify`='1' AND `isactive`='1' LIMIT 1 ";
        $aAdjustprice = $this->oDB->getDataCached( $sSql, 10 );
        if( empty($aAdjustprice) )
        {//没有调价方案
        	return -2;
        }
        $aAdjustprice = $aAdjustprice[0];
        //06: 获取调价线
        $sSql = " SELECT `uplimit`,`percent`,`isup` FROM `adjustprizedetail`
                  WHERE `groupid`='".$aAdjustprice['groupid']."' ORDER BY `uplimit` ASC ";
        $aDetail = $this->oDB->getDataCached( $sSql, 10 );
        if( empty($aDetail) )
        {//没有具体调价线
            return -2;
        }
        
        //07： 获取当前期的销售额
        /* @var $oLocks model_locks */
        $oLocks     = A::singleton('model_locks');
        $fSaleMoney = $oLocks->salesGetMoneys( $iLotteryId, $aIssueInfo['issue'], $aMethodData['lockname'] );
        if( $fSaleMoney === FALSE )
        {
            return -3;
        }
        
        //08: 获取所有号码的当前封锁值[靓号区只获取上条奖金的号码]
        if( $bGroup == TRUE )
        {
        	$sSql  = " SELECT SUM(`prizes`) AS sumprizes,`code` FROM `".$aMethodData['lockname']."` 
                       WHERE `issue`='".$aIssueInfo['issue']."' GROUP BY `code` HAVING sumprizes<"
        	           .floatval($fSaleMoney - $aAdjustprice['winline']);
        }
        else
        {
        	$sSql  = " SELECT SUM(`prizes`) AS sumprizes,`code` FROM `".$aMethodData['lockname']."` 
                   WHERE `issue`='".$aIssueInfo['issue']."' GROUP BY `code` ";
        }
        $aCodes = $this->oDB->getDataCached( $sSql, 10 );
        if( empty($aCodes) )
        {//封锁表出错
           return array();
        }
        
        //09: 算每个号码的变动后的奖金
        //$aResult = array( 'upcode'=>array(), 'normalcode'=>array(), 'downcode'=>array(), 'fMaxPrize'=>$fMaxPrize );
        $aResult     = array( 'codes'=>array(), 'fMaxPrize'=>$fMaxPrize, 'issue'=>$aIssueInfo['issue'] );
        //09 01: 把调价线分为上调和下调
        $aUpDetail   = array();    //上调变价线
        $aDownDetail = array();    //下调变价线
        foreach( $aDetail as $v )
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
        //降价：单倍最终奖金 = 单倍奖金  - (单倍奖金-(全包金额*极限反奖率)/转直注数)*调价线的调价比例
        //升价：单倍最终奖金 = 单倍奖金  + ((全包金额*(1-总代返点-公司极限留水)/转直注数)-单倍奖金)*调价比例
        $fTempDownMax = $fMaxPrize - (($aMethodData['totalmoney'] * $aMethodData['adjustmaxpercent'])/$iNumCount);
        $fTempUpMax   = (($aMethodData['totalmoney']*(1-$aMethodData['topuserpoint']-$aMethodData['adjustminprofit']))
                        /$iNumCount) - $fMaxPrize;
        foreach( $aCodes as $v )
        {
           $fLastPrize = 0; //调价后的最终奖金
           $iBeginLine = 0; //开始调价线
           if( $v['sumprizes'] > ($fSaleMoney + $aAdjustprice['loseline']) )
           {//下调奖金
               foreach( $aDownDetail as $kk=>$vv )
               {//获取符合最终的变价线
                   if( $v['sumprizes'] > ($fSaleMoney + $vv['uplimit']) )
                   {
                       $iBeginLine = $kk;
                   }
               }
               //算出最终该号码的单倍奖金
               $fLastPrize = $fMaxPrize - round(($fTempDownMax * $aDownDetail[$iBeginLine]['percent']),2);
           }
           elseif( $v['sumprizes'] < ($fSaleMoney - $aAdjustprice['winline']) )
           {//上调奖金
           	   foreach( $aUpDetail as $kk=>$vv )
               {//获取符合最终的变价线
                   if( $v['sumprizes'] < ($fSaleMoney - $vv['uplimit']) )
                   {
                       $iBeginLine = $kk;
                   }
               }
               //算出最终该号码的单倍奖金
               $fLastPrize = $fMaxPrize + round(($fTempUpMax * $aUpDetail[$iBeginLine]['percent']),2);
           }
           else 
           {//奖金不变动
           	   $fLastPrize = $fMaxPrize;
           }
           if( $bGroup == TRUE )
           {//分组
           	   $aResult['codegroup']["".$fLastPrize][] = $v['code'];
           }
           else 
           {
           	   $aResult['codes'][] = array('code'=>$v['code'], 'prize'=>$fLastPrize);
           }
           
        }
        if( $bGroup == TRUE )
        {
        	krsort($aResult['codegroup']);
        }
        else
        {
        	$aResult['codes'] = $this->sortTableRow( $aResult['codes'], 5 );
        }
        return $aResult;
    }
    
    //对数据进行竖排表格格式化
    private function sortTableRow( $aCode, $iCols )
    {
    	$aResult = array();
    	if( empty($aCode) || empty($iCols) )
    	{
    		return $aResult;
    	}
    	//01:算出多少行
    	$iRows = ceil( count($aCode)/$iCols );
    	for( $i=0; $i<$iRows; $i++ )
    	{
    		for( $j=0; $j<$iCols; $j++ )
    		{
    			$aResult[] = $aCode[($iRows*$j)+$i];
    		}
    	}
    	return $aResult;
    }
    
}
?>