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
    protected $_iLocksSqlNum = 0; //一个方案执行封锁表相关的SQL数目
    protected $_iSinglePrice = 2; //单注单倍投注金额
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
     * @param   array    $aGameData     //投注信息
     * ---------------------------------------------------
     * @param   string   $aGameData['sIssue']        //投注当前期号
     * @param   int      $aGameData['iTotalNum']     //投注总注数
     * @param   int      $aGameData['iTotalAmount']  //投注总金额[追号总金额]
     * @param   int      $aGameData['aProject']      //投注方案
     *                   [type] => digital|input|dxds|dds
                         [methodid] => 2//玩法ID
                         [codes] => 0&1&2&3&4&5&6&7&8&9|0&1&2&3&4&5&6&7&8&9|0&1&2&3&4&5&6&7&8&9//投注号码
                         [nums] => 1000 //投注注数
                         [times] => 1   //倍数
                         [money] => 2000 //金额
     *
     * @param   boolean  $aGameData['bIsTrace']      //是否追号[TRUE：追号，FALSE：非追号]
     * @param   boolean  $aGameData['bIsTraceStop']  //追号的时候是否追中停止
     * @param   array    $aGameData['aTraceIssue']   //追号详情
     * @return  mixed   //出错返回错误信息字符串，成功返回TRUE
     */
    public function gameBuy( $iUserId, $iLotteryId, $aGameData=array() )
    {
    	/**
    	 * 01: 数据完整性检测=========================================================
    	 */
        $sStartTime    = time(); //整个投注过程的开始时间，用于计算整个过程的执行时间
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
        if( empty($aGameData['sIssue']) )
        {//当期期号
            return ajaxMsg("error", "操作错误");
        }
        $sIssue = $aGameData['sIssue'];
        if( empty($aGameData['iTotalNum']) || !is_numeric($aGameData['iTotalNum']) || $aGameData['iTotalNum'] <= 0 )
        {//购买注数
            return ajaxMsg("error", "操作错误");
        }
        $iTotalNum = intval($aGameData['iTotalNum']);
        if( empty($aGameData['iTotalAmount']) || floatval($aGameData['iTotalAmount']) <= 0 )
        {//购买总金额[追号总金额]
            return ajaxMsg("error", "操作错误");
        }
        $iTotalAmount = floatval($aGameData['iTotalAmount']);
        if( empty($aGameData['aProject']) || !is_array($aGameData['aProject']) )
        {
            return ajaxMsg("error", "投注内容错误");
        }
        $aProjects= $aGameData['aProject'];
        $aMethods = array();
        $aTempArr = array();
        foreach( $aProjects as $v )
        {
            if( strlen($v['codes']) == 0 || empty($v['type']) || empty($v['methodid']) || empty($v['nums'])
                || empty($v['times']) || intval($v['times']) != $v['times'] || empty($v['money']) || empty($v['desc']) || empty($v['mode']) )
            {//初步检测投注内容
                return ajaxMsg("error", "投注内容错误(errcode=1)");
            }
            $v['methodid'] = intval($v['methodid']);
            $v['nums']     = intval( $v['nums'] );
            $v['times']    = intval( $v['times'] );
            $v['money']    = floatval( $v['money'] );
            $v['mode']     = intval( $v['mode'] );
            $oMethod     = A::singleton('model_method');
            $aMethodInfo = $oMethod->methodGetOne('maxcodecount',"`methodid`='".$v['methodid']."'");
            $v['maxcodecount'] = empty($aMethodInfo['maxcodecount']) ? 0 : $aMethodInfo['maxcodecount'];
            if( !isset($GLOBALS['config']['modes'][$v['mode']]) )
            {
                return ajaxMsg("error", "投注内容错误(errcode=2)");
            }
            if( $this->checkproject($v) === FALSE )
            {
                return ajaxMsg("error", "投注内容错误(errcode=3)");
            }
            $v['rate']     = $GLOBALS['config']['modes'][$v['mode']]['rate'];
            if( !isset($this->_aMethod_Config[$v['methodid']]) )
            {//没有相应玩法
                return ajaxMsg("error", "投注内容错误(errcode=4)");
            }
            $sMethodeName = $this->_aMethod_Config[$v['methodid']]; //玩法对应的表达式
            if( !in_array($v['type'], array('input','digital','dxds','dds')) )
            {
                return ajaxMsg("error", "投注内容错误(errcode=5)");
            }
            if( $v['type'] == 'input' )
            {//输入型
                $v['codes'] = preg_replace( "/&/", "|", $v['codes'] );
            }
            elseif( $v['type'] == 'digital' || $v['type'] == 'dxds' || $v['type'] == 'dds' )
            {
                switch( $sMethodeName )
                {
					case 'ZX5' :
					case 'ZH5' :
					case 'TX5' :
					case 'ZX4' :
					case 'ZH4' :
					case 'TX4' :
					case 'TX3' :
					case 'WXZU60' :
					case 'WXZU30' :
					case 'WXZU20' :
					case 'WXZU10' :
					case 'WXZU5' :
					case 'SXZU12' :
					case 'SXZU4' :
                    case 'QZX3' :
                    case 'HZX3' :
					case 'ZH3' :
                    case 'QZX2' :
                    case 'HZX2' : $v['codes'] = preg_replace( "/&/", "", $v['codes'] ); break;
                    case 'QZXHZ':
                    case 'HZXHZ':
                    case 'QZUS' :
                    case 'QZUL' :
                    case 'QHHZX':
                    case 'QZUHZ':
                    case 'HZUS' :
                    case 'HZUL' :
                    case 'HHHZX':
                    case 'HZUHZ':
                    case 'HBDW1':
					case 'BDW1':
					case 'HSCS':
					case 'SXBX':
					case 'SJFC':
                    case 'HBDW2':
					case '4BDW2': 
					case '5BDW2':
					case '5BDW3':
                    case 'QZU2' :
					case 'WXZU120' :
					case 'SXZU24' :
					case 'SXZU6' :
					case 'HZWS' : 
					case 'ZXHZ2':
					case 'ZUHZ2': 
					case 'ZXKD':
					case 'ZXKD2':
					case 'HZU2' : $v['codes'] = preg_replace( "/&/", "|", $v['codes'] );break;
                    case 'DWD'  : break;//后面特殊处理
                    case 'DWD3' : break;//后面特殊处理
                    case 'QDXDS':
					case '3DXDS':
                    case 'HDXDS': $v['codes'] = preg_replace( array('/大/','/小/','/单/','/双/'), array(0,1,2,3), $v['codes'] );
                                  $v['codes'] = preg_replace( "/&/", "", $v['codes'] ); break;
                    //山东十一运
                    case 'SDZX3': $v['codes'] = preg_replace( "/&/", " ", $v['codes'] ); break;
                    case 'SDZU3': $v['codes'] = preg_replace( "/&/", "|", $v['codes'] ); break;
                    case 'SDZX2': $v['codes'] = preg_replace( "/&/", " ", $v['codes'] ); break;
                    case 'SDZU2': $v['codes'] = preg_replace( "/&/", "|", $v['codes'] ); break;
                    case 'SDBDW': $v['codes'] = preg_replace( "/&/", "|", $v['codes'] ); break;
                    case 'SDDWD': break;//后面特殊处理
                    case 'SDDDS': $v['codes'] = preg_replace( array('/5单0双/','/4单1双/','/3单2双/','/2单3双/',
                                                '/1单4双/','/0单5双/'), array(5,4,3,2,1,0), $v['codes'] );
                                  $v['codes'] = preg_replace( "/&/", "|", $v['codes'] ); break;
                    case 'SDCZW': $v['codes'] = preg_replace( array('/3/','/4/','/5/','/6/','/7/','/8/','/9/'),
                                                array('03','04','05','06','07','08','09'), $v['codes'] );
                                  $v['codes'] = preg_replace( "/&/", "|", $v['codes'] ); break;
					case 'BJRX1':
					case 'BJRX2':
					case 'BJRX3':
					case 'BJRX4':
					case 'BJRX5':
					case 'BJRX6':
					case 'BJRX7':
                    case 'SDRX1':
                    case 'SDRX2':
                    case 'SDRX3':
                    case 'SDRX4':
                    case 'SDRX5':
                    case 'SDRX6':
                    case 'SDRX7':
                    case 'SDRX8': $v['codes'] = preg_replace( "/&/", "|", $v['codes'] );break;
                    case 'BJHZDX':
					case 'BJHZDS':
					case 'BJSXP':
					case 'BJJOP':
						$v['codes'] = preg_replace( array('/大/','/小/','/单/','/双/','/和/','/上/','/下/','/奇/','/偶/','/中/'), array(0,1,0,1,2,0,1,0,1,2), $v['codes'] );
                        $v['codes'] = preg_replace( "/&/", "|", $v['codes'] ); break;
                       break;
					case 'TSH3':
						$v['codes'] = preg_replace( array('/豹子/','/顺子/','/对子/'), array(0,1,2), $v['codes'] );
						$v['codes'] = preg_replace( "/&/", "|", $v['codes'] );
						break;
					case '5QW':
					case '4QW':
					case '3QW':
						$v['codes'] = preg_replace( array('/小号/','/大号/'), array(0,1), $v['codes'] );
						$v['codes'] = preg_replace( "/&/", "", $v['codes'] );
						break;
					case '5QJ':
					case '4QJ':
					case '3QJ':
						$v['codes'] = preg_replace( array('/一区/','/二区/','/三区/','/四区/','/五区/'), array(0,1,2,3,4), $v['codes'] );
						$v['codes'] = preg_replace( "/&/", "", $v['codes'] );
						break;
					default     : break;
                }
            }
            if( $sMethodeName == 'DWD' )
            {//如果是时时彩的定位胆，则把一单拆成多单
                $aTempCode = explode("|",$v['codes']);
                $iTempMID  = intval($v['methodid']);
                $iTempLen  = count($aTempCode);
                if( $iTempLen < 5 )
                {
                    return ajaxMsg("error", "投注内容错误");
                }
                for( $i=0; $i<$iTempLen; $i++ )
                {
                    if( strlen($aTempCode[$i]) > 0 )
                    {//如果在此位上有进行选择
                        $aTempTemp   = explode("&",$aTempCode[$i]);
                        $sTempStr    = "";
                        switch($i)
                        {
                            case 0: $sTempStr="(万位)";break;
                            case 1: $sTempStr="(千位)";break;
                            case 2: $sTempStr="(百位)";break;
                            case 3: $sTempStr="(十位)";break;
                            case 4: $sTempStr="(个位)";break;
                        }
                        $aTempArr[($iTempMID+$i)][] = array(
                                           'type'       => $v['type'],
                                           'methodid'   => ($iTempMID+$i),
                                           'codes'      => implode("|",$aTempTemp),
                                           'nums'       => count($aTempTemp),
                                           'times'      => $v['times'],
                                           'money'      => (count($aTempTemp) * $v['times'] * $this->_iSinglePrice * $v['rate']),
                                           'mode'       => $v['mode'],
                                           'rate'       => $v['rate'],
                                           'desc'       => $v['desc'].$sTempStr
                                       );
                        $aMethods[] = ($iTempMID+$i);
                    }
                }
            }
            elseif( $sMethodeName == 'DWD3' || $sMethodeName == 'SDDWD' )
            {//3位的定位胆
                $aTempCode = explode("|",$v['codes']);
                $iTempMID  = intval($v['methodid']);
                $iTempLen  = count($aTempCode);
                if( $iTempLen < 3 )
                {
                    return ajaxMsg("error", "投注内容错误");
                }
                for( $i=0; $i<$iTempLen; $i++ )
                {
                    if( strlen($aTempCode[$i]) > 0 )
                    {//如果在此位上有进行选择
                        $aTempTemp   = explode("&",$aTempCode[$i]);
                        $sTempStr    = "";
                        if( $sMethodeName == 'SDDWD' )
                        {
                            switch($i)
                            {
                                case 0: $sTempStr="(第一位)";break;
                                case 1: $sTempStr="(第二位)";break;
                                case 2: $sTempStr="(第三位)";break;
                            }
                        }
                        else
                        {
                            switch($i)
                            {
                                case 0: $sTempStr="(百位)";break;
                                case 1: $sTempStr="(十位)";break;
                                case 2: $sTempStr="(个位)";break;
                            }
                        }
                        $aTempArr[($iTempMID+$i)][] = array(
                                           'type'       => $v['type'],
                                           'methodid'   => ($iTempMID+$i),
                                           'codes'      => implode("|",$aTempTemp),
                                           'nums'       => count($aTempTemp),
                                           'times'      => $v['times'],
                                           'money'      => (count($aTempTemp) * $v['times'] * $this->_iSinglePrice * $v['rate']),
                                           'mode'       => $v['mode'],
                                           'rate'       => $v['rate'],
                                           'desc'       => $v['desc'].$sTempStr
                                       );
                        $aMethods[] = ($iTempMID+$i);
                    }
                }
            }
            else
            {
                $aTempArr[$v['methodid']][] = $v;
                $aMethods[] = $v['methodid'];
            }
        }
        if( empty($aMethods) )
        {
            return ajaxMsg("error", "投注内容错误");
        }
        $aProjects = $aTempArr;
        $bIsTrace = (isset($aGameData['bIsTrace']) && $aGameData['bIsTrace'] == TRUE) ? TRUE : FALSE;
        $bIsTraceStop = (isset($aGameData['bIsTraceStop']) && $aGameData['bIsTraceStop'] == TRUE) ? TRUE : FALSE;
        if( $bIsTrace )
        {//如果是追号则获取追号数据
            if( empty($aGameData['aTraceIssue']) || !is_array($aGameData['aTraceIssue']) )
            {//追号期号
                return ajaxMsg("error", "请选择追号期数");
            }
            $aTrace = $aGameData['aTraceIssue'];
            /*if( $iTotalAmount != ($iTotalNum * (array_sum($aTrace)) * $this->_iSinglePrice) )
            {//总金额 = 总注数 * 总倍数 * 单注单倍价格
                return ajaxMsg("error", "操作错误");
            }*/
        }
        unset($aGameData);
        
        /**
         * 02: 获取玩法以及封锁表信息========================================================
         */
        $aMethods    = array_unique( $aMethods );
        //$oMethod     = A::singleton('model_method');
        $sFields     = "m.`methodid`,m.`pid`,m.`lotteryid`,m.`methodname`,m.`level`,m.`nocount`,".
                       "m.`description`,m.`islock`,m.`totalmoney`,m.`lockname`,lt.`maxlost`";
        $sCondition = " m.`methodid` IN(".implode(",",$aMethods).") AND m.`lotteryid`='".$iLotteryId.
                      "' AND m.`isclose`='0' ";
        $sLeftJoin  = " LEFT JOIN `locktablename` AS lt ON m.`lockname`=lt.`locktablename` ";
        $aTempArr   = $oMethod->methodGetInfo( $sFields, $sCondition, $sLeftJoin );
        if( empty($aTempArr) || count($aTempArr) != count($aMethods) )
        {//无数据[玩法不存在或者已关闭]
            //查询已经关闭的游戏
            $sFields    = "m.`description`";
            $sCondition = " m.`methodid` IN(".implode(",",$aMethods).") AND m.`lotteryid`='".$iLotteryId.
                          "' AND m.`isclose`='1' ";
            $aMethodData= $oMethod->methodGetInfo( $sFields, $sCondition );
            $aTempArr   = array();
            foreach( $aMethodData as $v )
            {
                $aTempArr[] = $v['description'];
            }
            return ajaxMsg("error", "[".implode(",",$aTempArr)."]游戏已关闭");
        }
        //整理玩法数据
        $aMethodData = array();
        $aMethodGroup= array();
        foreach( $aTempArr as $v )
        {
            $v['nocount'] = unserialize($v['nocount']);
            $aTemp = array();
            foreach( $v['nocount'] as $kk=>$vv )
            {
                if( isset($vv['use']) && $vv['use'] == 1 )
                {//使用了该奖级
                        $aTemp[] = intval($kk);
                }
            }
            sort($aTemp);
            $v['nocount'] = $aTemp;
            $aMethodData[$v['methodid']] = $v;
            $aMethodGroup[] = $v['pid'];
        }
        $aMethodGroup = array_unique($aMethodGroup);
        
        /**
         * 03: 获取奖金组信息以及用户返点和总代返点=================================================
         */
        $oUserMethod = A::singleton('model_usermethodset');
        $sFields     = "m.`methodid`,upl.`level`,upl.`prize`,ums.`userpoint`,upl.`userpoint` AS `topuserpoint`";
        $sCondition  = " AND m.`methodid` IN(".implode(",",$aMethodGroup).") ";
        $aTempArr    = $oUserMethod->getUserMethodPrize( $iUserId, $sFields, $sCondition, FALSE );
        $aPrizeData  = array();
        foreach( $aTempArr as $v )
        {
            $aPrizeData[$v['methodid']][] = $v;
        }
        if( empty($aPrizeData) || count($aPrizeData) != count($aMethodGroup) )
        {//无数据[玩法组没有设置返点或者已关闭]
            $aTemp = array();
            foreach( $aTempArr as $v )
            {
                $aTemp[] = $v['methodid'];
            }
            $aTempArr = array_diff( $aMethodGroup, $aTemp );
            //查询没有权限的游戏组
            $sFields    = "m.`methodname`";
            $sCondition = " m.`methodid` IN(".implode(",",$aTempArr).") AND m.`lotteryid`='".$iLotteryId."' ";
            $aMethodData= $oMethod->methodGetInfo( $sFields, $sCondition );
            $aTempArr   = array();
            foreach( $aMethodData as $v )
            {
                $aTempArr[] = $v['methodname'];
            }
            return ajaxMsg("error", "[".implode(",",$aTempArr)."]没有相应权限");
        }
        //把奖金组信息和返点信息整理进入到每个玩法里
        foreach( $aMethodData as $k=>$v )
        {
            if( isset($aPrizeData[$v['pid']]) )
            {
                foreach( $aPrizeData[$v['pid']] as $vv )
                {
                    if( in_array($vv['level'], $v['nocount']) )
                    {
                        $aMethodData[$k]['prize'][$vv['level']] = $vv['prize'];
                    }
                }
                $aMethodData[$k]['userpoint']    = $aPrizeData[$v['pid']][0]['userpoint'];   //用户返点[可能为0]
                $aMethodData[$k]['topuserpoint'] = $aPrizeData[$v['pid']][0]['topuserpoint'];//总代返点[可能为0]
            }
            else
            {
                return ajaxMsg("error", "[".$v['description']."]没有相应权限");
                break;
            }
        }
        //print_r($aMethodData);

        /**
         * 04 : 获取所有上级返点并计算返点差            =====================================================
         */
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
            foreach( $aMethodGroup as $v )
            {
                $aUserPoint[$v][$iTopProxyId] = $aPrizeData[$v][0]['topuserpoint'];
                $aUserPoint[$v][$iUserId]     = $aPrizeData[$v][0]['userpoint'];
            }
        }
        else
        {//获取上级返点
            $sCondition = " AND ums.`methodid` IN(".implode(",",$aMethodGroup).") ";
            $aParentPoints = $oUserMethod->getParentPoint( $iUserId, '', $sCondition );
            if( empty($aParentPoints) )
            {
                return ajaxMsg("error", "没有权限");
            }
            foreach( $aParentPoints as $v )
            {
                if( $v['isclose'] == 1 )
                {//上级玩法关闭
                    return ajaxMsg("error", "没有权限");
                    break;
                }
                $aUserPoint[$v['methodid']][$v['userid']] = $v['userpoint'];
            }
            $iTopProxyId = $aParentPoints[0]['lvtopid'];  //获取总代ID
            $iLvProxyId  = $aParentPoints[0]['lvproxyid'];  //获取一代ID
            foreach( $aMethodGroup as $v )
            {
                $aUserPoint[$v][$iTopProxyId] = $aPrizeData[$v][0]['topuserpoint'];
                $aUserPoint[$v][$iUserId]     = $aPrizeData[$v][0]['userpoint'];
            }
            unset($aParentPoints);
        }
        //获取用户的层级关系[总代->一代->.....->用户]
        $mTempUserLeverSet = $oUser->getParentId( $iUserId, TRUE );
        if( empty($mTempUserLeverSet) )
        {
            return ajaxMsg("error", "没有权限");
        }
        $mTempUserLeverSet = explode( ",", $mTempUserLeverSet.",".$iUserId );
        $aUserDiffPoints = array(); //所有用户返点差记录
        $iLastId = 0;
        $iNowId  = 0;
        $iTreeLen= count($mTempUserLeverSet);
        for( $i=0; $i<$iTreeLen; $i++ )
        {
            if( $iLastId == 0 )
            {
                $iLastId = intval($mTempUserLeverSet[$i]); //用户ID
            }
            else
            {
                $iNowId = intval($mTempUserLeverSet[$i]);
                foreach( $aUserPoint as $k=>$v )
                {
                    $v[$iLastId] = isset($v[$iLastId]) ? $v[$iLastId] : 0;
                    $v[$iNowId]  = isset($v[$iNowId])  ? $v[$iNowId]  : 0;
                    $aUserDiffPoints[$k][$iLastId]['userid']    = $iLastId;
                    $aUserDiffPoints[$k][$iLastId]['diffpoint'] = round(floatval($v[$iLastId] - $v[$iNowId]),4);
                    if( $aUserDiffPoints[$k][$iLastId]['diffpoint'] < 0 )
                    {//返点差出现负数，即返点设置有错误
                        return ajaxMsg("error", "操作错误");
                    }
                }
                $iLastId = $iNowId;
            }
        }
        //最后一个用户即自身
        foreach( $aUserPoint as $k=>$v )
        {
            $aUserDiffPoints[$k][$iLastId]['userid']    = $iLastId;
            $aUserDiffPoints[$k][$iLastId]['diffpoint'] = $v[$iLastId];
        }
        //print_r($aUserDiffPoints);
        //print_rr($aUserPoint);
        unset( $iLastId, $iNowId, $aUserPoint, $mTempUserLeverSet );   //释放内存
        
        /**
         * 05: 获取购买期信息=============================================================
         */
        $oIssue = A::singleton('model_issueinfo');
        //判断当前是否处于销售时间内
        $aCurrentIssue = $oIssue->getCurrentIssue( $iLotteryId );
        if( empty($aCurrentIssue) )
        {
            return ajaxMsg("error", "已停止销售");
        }
        $iNowTime   = time();     //设置当前时间，避免后面多次调用
        $aIssueInfo = $oIssue->getItem( 0, daddslashes($sIssue), $iLotteryId );
        if( empty($aIssueInfo) )
        {//0401: 判断当前期是否存在
            return ajaxMsg("error", "操作错误");
        }
        if( strtotime($aIssueInfo['saleend']) < $iNowTime )
        {//0402:检测是否已停止销售
            return ajaxMsg("error", "第[".$sIssue."]期已停止销售");
        }
        if( $aIssueInfo['statuscode'] > 0 )
        {
            return ajaxMsg("error", "第[".$sIssue."]期已停止销售");
        }
        
        //获取单倍最高奖金和系统同单的最高奖金限制====================================
        $fSysMaxPrize = intval(getConfigValue( 'limitbonus', 100000 )); //系统同单投注最高奖金限制
        
        
        /**
         * 06: 根据是否为追号单做不同处理========================================
         */
        /* @var $oUserFund model_userfund */
        /* @var $oProjects model_projects */
        $oUserFund    = A::singleton('model_userfund'); //用户资金模型
        $aPackage = array(  //定单数据信息
                           'userid'     => $iUserId,
                           'lvtopid'    => $iTopProxyId,
                           'lvproxyid'  => $iLvProxyId,
                           'writetime'  => date("Y-m-d H:i:s"),
                           'processtime'=> ''
                    );
        //06 01: 写入定单
        $iPackId = $this->oDB->insert( "package", $aPackage );
        if( $iPackId <= 0 )
        {//写入订单失败
            return ajaxMsg("error", "投注失败，请稍后重试");
        }
        $aUserInfo = array(//用户信息[后面调用]
                           'userid'     => $iUserId,
                           'lvtopid'    => $iTopProxyId,
                           'lvproxyid'  => $iLvProxyId,
                           'packid'     => $iPackId,
                           'taskid'     => 0,
                           'currentissue'=> $aCurrentIssue['issue'],
                           'istracestop'=> $bIsTraceStop
                    );
        $iSuccessCount = 0; //执行成功的方案个数
        $iFailCount    = 0; //执行错误的方案个数
        $aErrorArr     = array();//执行错误的收集数组
        //06 00 01开始事务
        if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            return ajaxMsg("error", "系统错误：错误编号:#5011");
        }
        //06 00 02锁定用户资金
        if( intval($oUserFund->switchLock($iUserId, SYS_CHANNELID, TRUE)) != 1 )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return ajaxMsg("error", "系统错误：错误编号:#5012");
            }
            if( $iSuccessCount == 0 )
            {//如果成功执行的方案为0则删除订单
                $this->oDB->delete( "package", " `packageid`='".$iPackId."' " );
            }
            return ajaxMsg("error", "您的资金账户因为其它操作被锁定，请稍后重试");
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return ajaxMsg("error", "系统错误：错误编号:#5013");
        }
        if( $bIsTrace == FALSE )
        {/******************************非追号处理*****************************/
            //06 01 01 循环处理每个方案
            foreach( $aProjects as $p )
            {//同一个玩法里面的所有方案数据
                foreach( $p as $aProject )
                {//具体的每个方案数据
                    //06 01 01 00：判断是否超过最高奖金限额
                    $mResult = $this->_checkLimitBonus( $iUserId, $sIssue, $aProject,
                                                    $aMethodData[$aProject['methodid']], $fSysMaxPrize );
                    if( $mResult !== TRUE )
                    {
                        $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;超过奖金限额";
                        $iFailCount++;
                        continue;
                    }
                    //06 01: [开始投单流程事务处理]------------
                    if( FALSE == $this->oDB->doTransaction() )
                    {//事务处理失败
                        return ajaxMsg("error", "系统错误：错误编号:#5011");
                    }
                    //06 01 01 02: 判断封锁,并写入封锁表
                    if( $aMethodData[$aProject['methodid']]['islock'] > 0
                        && !empty($aMethodData[$aProject['methodid']]['lockname']) )
                    {//如果有需要封锁，则执行
                        //查询封锁表是否正确存在的是当期的
                        $sSql= "SELECT `issue` FROM `".$aMethodData[$aProject['methodid']]['lockname'].
                               "` WHERE `issue`='".$sIssue."' LIMIT 1";
                        $this->oDB->query( $sSql );
                        if( $this->oDB->ar() == 0 )
                        {//封锁表未生成，错误编号#3010
                            if( FALSE == $this->oDB->doRollback() )
                            {//回滚事务
                                return ajaxMsg("error", "系统错误：错误编号:#5012");
                            }
                            $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                            if( $iSuccessCount == 0 )
                            {//如果成功执行的方案为0则删除订单
                                $this->oDB->delete( "package", " `packageid`='".$iPackId."' " );
                            }
                            return ajaxMsg( "error", "系统错误，请联系管理员，错误编号#3010" );
                        }
                        $mResult = $this->_locksDeal( $sIssue, $aProject, $aMethodData[$aProject['methodid']] );
                        if( $mResult === FALSE )
                        {
                            if( FALSE == $this->oDB->doRollback() )
                            {//回滚事务
                                return ajaxMsg("error", "系统错误：错误编号:#5012");
                            }
                            $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;投注失败";
                            $iFailCount++;
                            continue;
                        }
                        if( $mResult !== TRUE )
                        {//有限号
                            if( FALSE == $this->oDB->doRollback() )
                            {//回滚事务
                                return ajaxMsg("error", "系统错误：错误编号:#5012");
                            }
                            $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;号码".$mResult."已限号";
                            $iFailCount++;
                            continue;
                        }
                    }
					elseif( in_array( $aProject['methodid'], array(373, 375, 377, 379, 381, 383) ) )
					{
						//小于最大奖金
						//$fSysMaxPrize = 200000;
						$fMaxTimes = floor($fSysMaxPrize/$aMethodData[$aProject['methodid']]['prize'][1]);

						//按注数, $aProject['times'] & $fMaxTimes
						if( $aProject['times'] * $aProject['nums'] > $fMaxTimes )
						{
						    if( FALSE == $this->oDB->doRollback() )
                            {//回滚事务
                                return ajaxMsg("error", "系统错误：错误编号:#5012");
                            }
							$aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;超过注数限额";
							$iFailCount++;
							continue;
						}
						//按玩法, 当期当前用户该玩法总投注数，不得大于 $fMaxTimes
					}
                    //写入方案、号码扩展表、返点差表、加入游戏帐变、本人返点帐变
                    $mResult = $this->_playInsertData( $aUserInfo, $sIssue, $aProject,
                                                       $aMethodData[$aProject['methodid']],
                                                       $aUserDiffPoints[$aMethodData[$aProject['methodid']]['pid']] );
                    if( $mResult === -44 )
                    {//金额不足
                        if( FALSE == $this->oDB->doRollback() )
                        {//回滚事务
                            return ajaxMsg("error", "系统错误：错误编号:#5012");
                        }
                        $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;余额不足";
                        $iFailCount++;
                        continue;
                    }
                    elseif( $mResult !== TRUE )
                    {//其他错误
                        if( FALSE == $this->oDB->doRollback() )
                        {//回滚事务
                            return ajaxMsg("error", "系统错误：错误编号:#5012");
                        }
                        $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;投注失败";
                        $iFailCount++;
                        continue;
                    }
                    if( FALSE == $this->oDB->doCommit() )
                    {//事务提交失败
                        return ajaxMsg("error", "系统错误：错误编号:#5013");
                    }
                    $iSuccessCount++;
                }
            }
        }
        else
        {/******************************************追号处理*****************************/
            $aTraceIssueNo = array_keys($aTrace);//所有奖期
            //0602 00对追号的所有奖期进行处理
            $sFields    = " A.`issueid`,A.`issue`,A.`salestart`,A.`saleend` ";
            $sCondition = " A.`issue` IN('".implode("','",$aTraceIssueNo)."') ".
                          " AND A.`lotteryid`='".$iLotteryId."' AND A.`saleend`>'".date("Y-m-d H:i:s",$iNowTime)."'";
            $aTraceIssue = $oIssue->issueGetList( $sFields, $sCondition );
            if( empty($aTraceIssue) || count($aTraceIssue) != count($aTraceIssueNo) )
            {//如果追号期中有错误的期号则返回并中断 ----------------------------------
                if( $iSuccessCount == 0 )
                {//如果成功执行的方案为0则删除订单
                    $this->oDB->delete( "package", " `packageid`='".$iPackId."' " );
                }
                $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
                return ajaxMsg("error", "追号奖期错误");
            }
            $aUserInfo['beginissue'] = $aTraceIssue[0]['issue'];
            //0602 01对每个方案进行循环处理
            foreach( $aProjects as $p )
            {//同一个玩法里面的所有方案数据
                foreach( $p as $aProject )
                {//具体的每个方案数据
                    $_tmpParam =  TRUE;
                    //0602 01 00 判断每期是否超过封锁
                    $mResult = $this->_traceCheckLimitBonus( $iUserId, $aTraceIssueNo, $aTrace, $aProject,
                                                             $aMethodData[$aProject['methodid']], $fSysMaxPrize );
                    if( $mResult !== TRUE )
                    {//有错误或者超过奖金限额
                        $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;".$mResult;
                        $iFailCount++;
                        continue;
                    }
                    //0602 01 01: [开始投单流程事务处理]------------
                    if( FALSE == $this->oDB->doTransaction() )
                    {//事务处理失败
                        return ajaxMsg("error", "系统错误：错误编号:#5011");
                    }
                    //0602 01 02: 判断封锁,并写入封锁表
                    if( $aMethodData[$aProject['methodid']]['islock'] > 0
                        && !empty($aMethodData[$aProject['methodid']]['lockname']) )
                    {//如果有需要封锁，则执行
                        foreach( $aTrace as $kk=>$vv )
                        {
                            $mResult = $this->_locksDeal( $kk, $aProject, $aMethodData[$aProject['methodid']],
                                                          $vv, ($aProject['nums']*$vv*$this->_iSinglePrice) );
                            if( $mResult === FALSE )
                            {
                                if( FALSE == $this->oDB->doRollback() )
                                {//回滚事务
                                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                                }
                                $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;追号失败";
                                $iFailCount++;
                                $_tmpParam =  FALSE;
                                break;
                            }
                            if( $mResult !== TRUE )
                            {//有限号
                                if( FALSE == $this->oDB->doRollback() )
                                {//回滚事务
                                    return ajaxMsg("error", "系统错误：错误编号:#5012");
                                }
                                $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;第[".$kk."]期中，号码".$mResult."已限号";
                                $iFailCount++;
                                $_tmpParam =  FALSE;
                                break;
                            }
                        }
                    }
 					elseif( in_array( $aProject['methodid'], array(373, 375, 377, 379, 381, 383) ) )
					{
						//小于最大奖金
						//$fSysMaxPrize = 200000;
						$fMaxTimes = floor($fSysMaxPrize/$aMethodData[$aProject['methodid']]['prize'][1]);
                        foreach( $aTrace as $kk=>$vv )
                        {
							//按注数, $aProject['times'] & $fMaxTimes
							if( $vv * $aProject['nums'] > $fMaxTimes )
							{
								if( FALSE == $this->oDB->doRollback() )
								{//回滚事务
									return ajaxMsg("error", "系统错误：错误编号:#5012");
								}
                                $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;第[".$kk."]期超过注数限额";
								$iFailCount++;
                                $_tmpParam =  FALSE;
                                break;
							}
							//按玩法, 当期当前用户该玩法总投注数，不得大于 $fMaxTimes
						}
				    }
                   if( $_tmpParam ==  FALSE )
                    {
                        continue;
                    }
                    //0602 01 03 写入追号方案，帐变
                    $mResult = $this->_TraceInsertData( $aUserInfo, $aTrace, $aProject,
                                                       $aMethodData[$aProject['methodid']],
                                                       $aUserDiffPoints[$aMethodData[$aProject['methodid']]['pid']] );
                    if( $mResult === -33 )
                    {//金额不足
                        if( FALSE == $this->oDB->doRollback() )
                        {//回滚事务
                            return ajaxMsg("error", "系统错误：错误编号:#5012");
                        }
                        $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;余额不足";
                        $iFailCount++;
                        continue;
                    }
                    elseif( $mResult !== TRUE )
                    {//其他错误
                        if( FALSE == $this->oDB->doRollback() )
                        {//回滚事务
                            return ajaxMsg("error", "系统错误：错误编号:#5012");
                        }
                        $aErrorArr[] = $aProject['desc']." &nbsp;&nbsp;追号失败";
                        $iFailCount++;
                        continue;
                    }
                    if( FALSE == $this->oDB->doCommit() )
                    {//事务提交失败
                        return ajaxMsg("error", "系统错误：错误编号:#5013");
                    }
                    $iSuccessCount++;
                }
            }
        }
        $oUserFund->switchLock( $iUserId, SYS_CHANNELID, FALSE );//解锁资金表
        if( $iSuccessCount == 0 )
        {//如果没有一个成功的则删除订单
            $this->oDB->delete( "package", " `packageid`='".$iPackId."' " );
        }
        //更新整个定单执行时间
        $iProccessTime = time() - $sStartTime;
        $this->oDB->update( 'package', array('processtime'=>$iProccessTime,'writetime'=>date("Y-m-d H:i:s")), " `packageid`='".$iPackId."'" );
        if( $iFailCount > 0 && !empty($aErrorArr) )
        {//有未购买成功的方案
            return ajaxMsg("fail", array('success'=>$iSuccessCount,'fail'=>$iFailCount,'content'=>$aErrorArr));
        }
        return TRUE;
    }
    
    
    /**
     * 判断是否超过奖金限额
     *
     * @param int $iUserId
     * @param string $sIssue
     * @param array $aProject
     * @param array $aMethod
     * @param float $fSysMaxPrize
     * @return true or false
     */
    private function _checkLimitBonus( $iUserId, $sIssue, $aProject, $aMethod, $fSysMaxPrize )
    {
        $oProjects      = A::singleton('model_projects'); //方案模型
        $fProjectPrize  = max($aMethod['prize']);
        $fProjectPrize *= intval($aProject['times']);
        $fModeRate      = $aProject['rate'];
        $fProjectPrize *= $fModeRate;
        //获取用户相同单已拥有的最高奖金
        $aUserHadPrizes = $oProjects->getUserPrizesBySameCode( $iUserId, $aMethod['lotteryid'],
                            $aProject['methodid'], $sIssue, $aProject['codes']  );
        $fUserHadPrizes = 0; //用户同单已拥有的奖金
        $aTempUser_Prize= array();
        if( FALSE === $aUserHadPrizes )
        {//数据错误
            return FALSE;
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
        //判断是否超过限额
        if( ($fProjectPrize + $fUserHadPrizes) > $fSysMaxPrize )
        {
            return FALSE;
        }
        unset($aUserHadPrizes, $fUserHadPrizes, $aTempUser_Prize);
        return TRUE;
    }
    
    
    /**
     * 追号的判断所有追号期是否超过奖金限额
     *
     * @param int $iUserId          用户ID
     * @param array $aIssueNos      奖期
     * @param array $aTrace         追号奖期
     * @param array $aProject       方案数据
     * @param array $aMethod        玩法数据
     * @param float $fSysMaxPrize   系统奖金限额
     * @return mixed                未限制返回TRUE，超过返回消息
     */
    private function _traceCheckLimitBonus( $iUserId, $aIssueNos, $aTrace, $aProject, $aMethod, $fSysMaxPrize )
    {
        $aResult = TRUE; //返回的错误消息
        $oTask = A::singleton('model_task'); //追号模型
        $fProjectPrize  = max($aMethod['prize']);
        $fModeRate      = $aProject['rate'];
        $fProjectPrize *= $fModeRate;
        //获取用户在以往的追号单中，相同号码在每期所拥有的最高奖金之和
        $aTemp_PastPrizes = $oTask->getUserPrizeBySame( $iUserId, $aMethod['lotteryid'], $aProject['methodid'],
                                                        $aIssueNos, $aProject['codes'] );
        $aPastPrizes = array();//所有追号期的最高奖金和
        if( FALSE === $aTemp_PastPrizes )
        {//数据错误
            $aResult = "系统错误";
            return $aResult;
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
        //获取用户直接投单中，相同号码在每期所拥有的最高奖金之和
        $oProjects        = A::singleton('model_projects'); //方案模型
        $aTemp_PastPrizes = $oProjects->getUserPrizesBySameCode( $iUserId, $aMethod['lotteryid'], $aProject['methodid'],
                                                                 $aIssueNos, $aProject['codes']  );
        if( FALSE === $aTemp_PastPrizes )
        {//数据错误
            $aResult = "系统错误";
            return $aResult;
        }
        elseif( !empty($aTemp_PastPrizes) )
        {
            $aTemp  =  array();//每期的所有奖金
            foreach( $aTemp_PastPrizes as $v )
            {
                if( isset($aTemp[$v['issue']][$v['projectid']]) )
                {//如果一个方案有多个奖金级别，则选择最大的一个
                    $aTemp[$v['issue']][$v['projectid']] = $aTemp[$v['issue']][$v['projectid']] > $v['prize'] ?
                                                           $aTemp[$v['issue']][$v['projectid']] : $v['prize'];
                }
                else
                {
                    $aTemp[$v['issue']][$v['projectid']] = $v['prize'];
                }
            }
            foreach( $aTemp as $k=>$v )
            {
                $aPastPrizes[$k] = isset($aPastPrizes[$k]) ? ($aPastPrizes[$k] + array_sum($v)) : array_sum($v);
            }
        }
        unset( $aTemp_PastPrizes, $aTemp );
        //对每一期进行是否超过奖金限额判断
        foreach( $aTrace as $k=>$v )
        {
            $fTemp  = $fProjectPrize * $v;//基本奖金*倍数=实际奖金
            $fTemp += isset($aPastPrizes[$k]) ?  $aPastPrizes[$k] : 0;
            if( $fTemp > $fSysMaxPrize )
            {
                $aResult = "第[".$k."]期超过奖金限额";
                return $aResult;
            }
        }
        return $aResult;
    }
    
    
    
    /**
     * 处理一个方案的封锁，包括封锁判断和写封锁以及写入销量[非事物]
     *
     * @param string $sIssue  涉及的奖期
     * @param array $aProject 单个方案
     * @param array $aMethod  该方案所涉及的玩法信息
     */
    private function _locksDeal( $sIssue, $aProject, $aMethod, $iTimes=0, $fMoney=0 )
    {
        if( empty($aProject) || empty($aMethod) || !is_array($aProject) || !is_array($aMethod) )
        {
            return FALSE;
        }
        if( empty($sIssue) || !is_numeric($iTimes) || !is_numeric($fMoney) )
        {
            return FALSE;
        }
        $fModeRate    = $aProject['rate'];
        $aProject['times'] = $iTimes > 0 ? $iTimes : $aProject['times'];//追号的就算追号的倍数
        $aProject['money'] = $aProject['nums'] * $aProject['times'] * $this->_iSinglePrice * $fModeRate;//注*倍*单价*模式
        //$aProject['money'] = $fMoney > 0 ? $fMoney : $aProject['money'];//追号的就算追号的金额
        $sMethodeName = $this->_aMethod_Config[$aProject['methodid']]; //玩法对应的表达式
        $aCondition = $this->getLocksCondition( $aProject['methodid'], $aProject['type'], $aProject['codes'] );
		//var_dump($aCondition);die;
        if( empty($aCondition) )
        {
            return FALSE;
        }
        //根据不同玩法写入封锁表
        $aLocksData = array();
        $fMaxPrize  = max($aMethod['prize']);//无多奖级则直接使用唯一的一个
        $aTempCondition = array();
        foreach( $aCondition as $k=>$v )
        {
            switch( $sMethodeName )
            {
                case 'QHHZX' :
                case 'HHHZX' ://混合组选
                case 'QZUHZ' :
                case 'HZUHZ' ://组选和值
				case 'ZU3BD' : //组三包胆
                             $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                             ($aMethod['prize'][1] * $aProject['times'] * $fModeRate * $v['times']).
                                             " WHERE `issue`='".$sIssue."' AND `stamp`='1' AND ".$v['condition'];//组三的号码
                             $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                             ($aMethod['prize'][2] * $aProject['times'] * $fModeRate * $v['times']).
                                             " WHERE `issue`='".$sIssue."' AND `stamp`='2' AND ".$v['condition'];//组六的号码
                             break;
                case 'SDDDS' : //定单双
                             $aCode =  explode("|",$aProject['codes']);
                             if( in_array(0,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][1] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".$sIssue."' AND `code`='0' AND ".$v['condition'];//一等奖
                             }
                             if( in_array(5,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][2] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".$sIssue."' AND `code`='5' AND ".$v['condition'];//二等奖
                             }
                             if( in_array(1,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][3] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".$sIssue."' AND `code`='1' AND ".$v['condition'];//三等奖
                             }
                             if( in_array(4,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][4] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".$sIssue."' AND `code`='4' AND ".$v['condition'];//四等奖
                             }
                             if( in_array(2,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][5] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".$sIssue."' AND `code`='2' AND ".$v['condition'];//五等奖
                             }
                             if( in_array(3,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][6] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".$sIssue."' AND `code`='3' AND ".$v['condition'];//六等奖
                             }
                             break;
                case 'SDCZW' : //猜中位
                             $aCode =  explode("|",$aProject['codes']);
                             if( in_array('03',$aCode) || in_array('09',$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][1] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND (`code`='03' OR `code`='09') AND ".$v['condition'];//一等奖
                             }
                             if( in_array('04',$aCode) || in_array('08',$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][2] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND (`code`='04' OR `code`='08') AND ".$v['condition'];//二等奖
                             }
                             if( in_array('05',$aCode) || in_array('07',$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][3] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND (`code`='05' OR `code`='07') AND ".$v['condition'];//三等奖
                             }
                             if( in_array('06',$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][4] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND `code`='06' AND ".$v['condition'];//四等奖
                             }
                             break;
				case 'BJHZDS':
                             $aCode =  explode("|",$aProject['codes']);
                             if( in_array(0,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][1] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND `code`=0 AND ".$v['condition'];//一等奖
                             }
                             if( in_array(1,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][2] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND `code`=1 AND ".$v['condition'];//二等奖
                             }
							 break;
				case 'BJHZDX':
				case 'BJSXP':
				case 'BJJOP':
                             $aCode =  explode("|",$aProject['codes']);
                             if( in_array(2,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][1] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND `code`=2 AND ".$v['condition'];//一等奖
                             }
                             if( in_array(0,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][2] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND `code`=0 AND ".$v['condition'];//二等奖
                             }
                             if( in_array(1,$aCode) )
                             {
                                 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
                                                 ($aMethod['prize'][3] * $aProject['times'] * $fModeRate * $v['times']).
                                                 " WHERE `issue`='".
                                                 $sIssue."' AND `code`=1 AND ".$v['condition'];//三等奖
                             }
					break;
				case 'TSH3': 
                    $aCode =  explode("|",$aProject['codes']);
					 if( in_array(0,$aCode) )
					 {
						 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
										 ($aMethod['prize'][1] * $aProject['times'] * $fModeRate * $v['times']).
										 " WHERE `issue`='".$sIssue."' AND `code`='0' AND ".$v['condition'];//豹子
					 }
					 if( in_array(1,$aCode) )
					 {
						 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
										 ($aMethod['prize'][2] * $aProject['times'] * $fModeRate * $v['times']).
										 " WHERE `issue`='".$sIssue."' AND `code`='1' AND ".$v['condition'];//顺子
					 }
					 if( in_array(2,$aCode) )
					 {
						 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
										 ($aMethod['prize'][3] * $aProject['times'] * $fModeRate * $v['times']).
										 " WHERE `issue`='".$sIssue."' AND `code`='2' AND ".$v['condition'];//对子
					 }
					break;
				case 'ZH3':
                    $aCode =  explode("|",$aProject['codes']);
					 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
									 ($aMethod['prize'][$k+1] * ($k==0 ? 1 : ($k==1 ? strlen($aCode[0]) : strlen($aCode[1]) * strlen($aCode[0]) )) * $aProject['times'] * $fModeRate * $v['times']).
									 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
					break;
				case '3QW':
				case '3QJ':
						$aCode =  explode("|",$aProject['codes']);
						if($k == 0 )
						{
							$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
										 (($aMethod['prize'][1] + $aMethod['prize'][2] * (strlen($aCode[0])-1)) * $aProject['times'] * $fModeRate * $v['times']).
										 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];//一等奖
						}
						else if($k == 1)
						{
								$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
											 (($aMethod['prize'][2] * strlen($aCode[0])) * $aProject['times'] * $fModeRate * $v['times']).
											 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];//二等奖
						}
					break;
				case 'TX3':
						$aCode =  explode("|",$aProject['codes']);
						$n1 = strlen($aCode[0]);
						$n2 = strlen($aCode[1]);
						$n3 = strlen($aCode[2]);
						if($k == 0 )	//一等奖
						{
							$prize = $aMethod['prize'][1] + $aMethod['prize'][2] * ($n1-1+$n2-1+$n3-1) + (($n1-1)*($n2-1)+($n1-1)*($n3-1)+($n2-1)*($n3-1)) * $aMethod['prize'][3];
							$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
										 ($prize * $aProject['times'] * $fModeRate * $v['times']).
										 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
						}
						else if($k == 1) //二等奖-前2
						{
							$prize = $n3 * $aMethod['prize'][2] + ($n1-1+$n2-1) * $n3 * $aMethod['prize'][3];
							$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
											 ($prize * $aProject['times'] * $fModeRate * $v['times']).
											 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
						}
						else if($k == 2) //二等奖-1/3
						{
							$prize = $n2 * $aMethod['prize'][2] + ($n1-1+$n3-1) * $n2 * $aMethod['prize'][3];
							$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
											 ($prize * $aProject['times'] * $fModeRate * $v['times']).
											 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
						}
						else if($k == 3) //二等奖-后2
						{
							$prize = $n1 * $aMethod['prize'][2] + ($n2-1+$n3-1) * $n1 * $aMethod['prize'][3];
							$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
											 ($prize * $aProject['times'] * $fModeRate * $v['times']).
											 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
						}
						else if($k == 4) //三等奖-1
						{
							$prize = $n2 * $n3 * $aMethod['prize'][3];
							$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
											 ($prize * $aProject['times'] * $fModeRate * $v['times']).
											 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
						}
						else if($k == 5) //三等奖-2
						{
							$prize = $n1 * $n3 * $aMethod['prize'][3];
							$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
											 ($prize * $aProject['times'] * $fModeRate * $v['times']).
											 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
						}
						else if($k == 6) //三等奖-3
						{
							$prize = $n1 * $n2 * $aMethod['prize'][3];
							$aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
											 ($prize * $aProject['times'] * $fModeRate * $v['times']).
											 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
						}
					break;
				default      :
					 $aLocksData[] = "UPDATE `".$aMethod['lockname']."` SET `prizes`=`prizes`+".
									 ($fMaxPrize * $aProject['times'] * $fModeRate * $v['times']).
									 " WHERE `issue`='".$sIssue."' AND ".$v['condition'];
					 break;
            }
			//file_put_contents('./log.txt', print_r($aLocksData, true));
            $aTempCondition[] = "(".$v['condition'].")";
        }
        $this->_iLocksSqlNum = count($aLocksData);
        $iLocksAffectNum = 0;
        foreach( $aLocksData as $v )
        {
            $this->oDB->query($v);
            if( $this->oDB->errno() > 0 )
            {//执行失败
                return FALSE;
            }
            $iLocksAffectNum += $this->oDB->ar();
        }
        if( $iLocksAffectNum <= 0 )
        {
            return FALSE;
        }
        //写入销量
        $sSql = " UPDATE `salesbase` SET `moneys`=`moneys`+".$aProject['money'].",`pointmoney`=`pointmoney`+".
                ($aProject['money']*$aMethod['topuserpoint'])." WHERE `issue`='".$sIssue."' ".
                " AND `lotteryid`='".$aMethod['lotteryid']."' AND `lockname`='".$aMethod['lockname']."' ".
                " AND `threadid`='".(intval($this->oDB->getThreadId()) % 3)."'";
        $this->oDB->query( $sSql );
        if( $this->oDB->ar() != 1 )
        {
            return FALSE;
        }
        //查询销量
        $oLocks     = A::singleton('model_locks');
        $aSaleMoney = $oLocks->salesGetMoneys( $aMethod['lotteryid'], $sIssue, $aMethod['lockname'] );
        if( $aSaleMoney === FALSE )
        {
            return FALSE;
        }
        if( !is_numeric($aSaleMoney['salemoney']) || !is_numeric($aSaleMoney['pointmoney']) )
        {
            return FALSE;
        }
        $fSaleMoney  = $aSaleMoney['salemoney'] - $aSaleMoney['pointmoney'];//总销量-总返点
        //查询判断是否有号码超过封锁
        $sSql = " SELECT `code` FROM `".$aMethod['lockname']."` WHERE `prizes`>".($aMethod['maxlost']+$fSaleMoney).
                " AND `issue`='".$sIssue."' AND (".implode(" OR ",$aTempCondition).") LIMIT 1 ";
        $aResult = $this->oDB->getOne( $sSql );
        if( $this->oDB->errno() > 0 )
        {//查询失败
            return FALSE;
        }
        if( !empty( $aResult ) )
        {
            $aResult['code'] = model_projects::AddslasCode( $aResult['code'], $aProject['methodid'] );
            return $aResult['code'];
        }
        return TRUE;
    }
    
    
    
    /**
     * 写入方案、号码扩展表、返点差表、加入游戏帐变、本人返点帐变
     *
     * @param array $aUserInfo  用户信息，包括ID，总代ID，一代ID，订单
     * @param string $sIssue    奖期
     * @param array $aProject   方案
     * @param array $aMethod    玩法信息
     * @param array $aDiffPoints返点差
     * @return mixed
     */
    private function _playInsertData( $aUserInfo, $sIssue, $aProject, $aMethod, $aDiffPoints )
    {
        $oProjects = A::singleton('model_projects'); //方案模型
        $oOrders   = A::singleton('model_orders');   //帐变模型
        $fModeRate = $aProject['rate'];
        //01:=========================生成方案数据===================
        $aProjectData = array(
                            'userid'        => $aUserInfo['userid'],
                            'packageid'     => $aUserInfo['packid'],
                            'taskid'        => $aUserInfo['taskid'],
                            'lotteryid'     => $aMethod['lotteryid'],
                            'methodid'      => $aProject['methodid'],
                            'issue'         => $sIssue,
                            'code'          => $aProject['codes'],
                            'codetype'      => $aProject['type'],
                            'singleprice'   => $aProject['nums'] * $this->_iSinglePrice * $fModeRate,
                            'multiple'      => $aProject['times'],
                            'totalprice'    => $aProject['money'],
                            'lvtopid'       => $aUserInfo['lvtopid'],
                            'lvtoppoint'    => $aMethod['topuserpoint'],
                            'lvproxyid'     => $aUserInfo['lvproxyid'],
                            'modes'         => $aProject['mode'],
                            'sqlnum'        => $this->_iLocksSqlNum
                        );
         $iProjectId = $oProjects->projectsInsert( $aProjectData );
         if( $iProjectId <= 0 )
         {//写入方案失败
             return -1;
         }
         //02:======================号码扩展表数据===================
         $aExpandData = $this->_getExpandData( $aProject, $aMethod['prize'], $fModeRate );
         if( empty($aExpandData) )
         {
             return -2;
         }
         foreach( $aExpandData as $k=>$v )
         {
             $aExpandData[$k]['projectid'] = $iProjectId;
             $aExpandData[$k]['codetimes'] = $aProject['times'];
             $aExpandData[$k]['prize']    *= $aProject['times'];
         }
         if(  TRUE !== $oProjects->expandCodeInsert( $aExpandData ) )
         {//写入号码扩展失败
             return -2;
         }
         //03：======================返点差表数据=====================
         $aDiffData = array();
         foreach( $aDiffPoints as $v )
         {
             if( $v['diffpoint'] > 0 )
             {//返点为0不加入返点表
                 $v['projectid']    = $iProjectId; //等待方案插入ID
                 $v['diffmoney']    = $aProject['money'] * $v['diffpoint']; //投注总金额*返点
                 $v['status']       = $aUserInfo['userid'] == $v['userid'] ? 1 : 0;//自身返点改为已返
                 $v['modes']        = $aProject['mode'];
                 if( $aUserInfo['userid'] == $v['userid'] )
                 {//返点的时间
                     $v['sendtime'] = date("Y-m-d H:i:s");
                 }
                 $aDiffData[]       = $v;
             }
         }
         if( !empty($aDiffData) )
         {
             if(  TRUE !== $oProjects->userDiffPointInsert( $aDiffData ) )
             {//写入返点失败
                 return -3;
             }
         }
         //04：======================加入游戏帐变=====================
         $aJoinData   = array(
                             'iLotteryId'   => $aMethod['lotteryid'],
                             'iMethodId'    => $aProject['methodid'],
                             'iPackageId'   => $aUserInfo['packid'],
                             'iModesId'     => $aProject['mode'],
                             'iTaskId'      => $aUserInfo['taskid'],
                             'iProjectId'   => $iProjectId,//等待方案插入ID
                             'iFromUserId'  => $aUserInfo['userid'],
                             'iOrderType'   => ORDER_TYPE_JRYX, //加入游戏帐变类型
                             'fMoney'       => $aProject['money'],   //产生帐变金额为总投注金额
                             'sDescription' => '加入游戏',      //帐变描述
                             'iChannelID'   => SYS_CHANNELID
                         );
         $mResult = $oOrders->addOrders( $aJoinData );
         if( $mResult === -1009  )
         {//资金不够
             return -44;
         }
         elseif( $mResult !== TRUE )
         {//其他帐变错误
             return -4;
         }
         //05：========================本人返点帐变======================
         if( $aMethod['userpoint'] > 0 )
         {
             $aBackData = array( //销售返点帐变插入数据
                             'iLotteryId'   => $aMethod['lotteryid'],
                             'iMethodId'    => $aProject['methodid'],
                             'iPackageId'   => $aUserInfo['packid'],
                             'iModesId'     => $aProject['mode'],
                             'iTaskId'      => $aUserInfo['taskid'],
                             'iProjectId'   => $iProjectId,//等待方案插入ID
                             'iFromUserId'  => $aUserInfo['userid'],
                             'iOrderType'   => ORDER_TYPE_XSFD, //销售返点帐变类型
                             'fMoney'       => $aProject['money'] * $aMethod['userpoint'], //投注总金额*返点
                             'sDescription' => '销售返点',//帐变描述
                             'iChannelID'   => SYS_CHANNELID
                            );
             $mResult = $oOrders->addOrders( $aBackData );
             if( $mResult !== TRUE )
             {//帐变错误
                 return -5;
             }
         }
         return TRUE;
    }
    
    
    
    /**
     * 写入追号数据、追号详情数据、帐变数据，如果有当期则把当期转换为方案
     *
     * @param array $aUserInfo      用户数据
     * @param array $aTrace         追号数据，奖期对应倍数
     * @param array $aProject       方案数据
     * @param array $aMethod        玩法数据
     * @param array $aDiffPoints    返点差数据
     * @return mixed
     */
    private function _TraceInsertData( $aUserInfo, $aTrace, $aProject, $aMethod, $aDiffPoints )
    {
        $oTask = A::singleton('model_task'); //追号模型
        $fModeRate  = $aProject['rate'];
        //01: 追号表数据
        $aTraceData = array(
                        'userid'          => $aUserInfo['userid'],
                        'lotteryid'       => $aMethod['lotteryid'],
                        'methodid'        => $aProject['methodid'],
                        'packageid'       => $aUserInfo['packid'],
                        'title'           => $aMethod['description']." 追号".count($aTrace)."期",
                        'codes'           => $aProject['codes'],  //追号号码
                        'codetype'        => $aProject['type'], //号码选号方式
                        'issuecount'      => count($aTrace), //追号总期数
                        'finishedcount'   => 0,   //已完成期数
                        'singleprice'     => $aProject['nums'] * $this->_iSinglePrice * $fModeRate,  //每期的单倍价格
                        'taskprice'       => $aProject['nums'] * $this->_iSinglePrice * array_sum($aTrace) * $fModeRate,//追号总金额
                        'beginissue'      => $aUserInfo['beginissue'],  //开始期数，默认为空
                        'prize'           => "",  //奖金[号码扩展表数据]序列化
                        'userdiffpoints'  => "",  //返点[用户返点差表数据]序列化
                        'lvtopid'         => $aUserInfo['lvtopid'], //总代ID
                        'lvtoppoint'      => $aMethod['topuserpoint'], //总代返点
                        'lvproxyid'       => $aUserInfo['lvproxyid'],   //一代ID
                        'modes'           => $aProject['mode'],
                        'stoponwin'       => $aUserInfo['istracestop']==TRUE ? 1 : 0  //是否追中停止
                   );
        //02: 号码扩展表基本数据
        $aExpandData = $this->_getExpandData( $aProject, $aMethod['prize'], $fModeRate );
        if( empty($aExpandData) )
        {
            return -1;
        }
        //03：返点差表基本数据
        $aDiffData = array();
        foreach( $aDiffPoints as $v )
        {
            if( $v['diffpoint'] > 0 )
            {//返点为0不加入返点表
                 $v['projectid']    = 0; //等待方案插入ID
                 $v['diffmoney']    = $aProject['nums'] * $this->_iSinglePrice * $v['diffpoint'] * $fModeRate; //投注总金额*返点
                 $v['status']       = $aUserInfo['userid'] == $v['userid'] ? 1 : 0;//自身返点改为已返
                 $v['modes']        = $aProject['mode'];
                 if( $aUserInfo['userid'] == $v['userid'] )
                 {//返点的时间
                     $v['sendtime'] = date("Y-m-d H:i:s");
                 }
                 $aDiffData[]       = $v;
            }
        }
        $aTraceData['prize']['base'] = $aExpandData;
        $aTraceData['prize'] = serialize($aTraceData['prize']);
        $aTraceData['userdiffpoints']['base'] = $aDiffData;
        $aTraceData['userdiffpoints'] = serialize($aTraceData['userdiffpoints']);
        $iTaskId = $oTask->taskInsert( $aTraceData );
        if( $iTaskId <= 0 )
        {//写入追号表失败
            return -1;
        }
        //04：追号详情表数据
        $aTraceDetailData = array();    //追号详情表数据
        foreach( $aTrace as $k=>$v )
        {
            $aTraceDetailData[$k] = array(
                                   'taskid'    => $iTaskId,       //追号ID，
                                   'projectid' => 0,              //默认为0，生成当期时更新
                                   'multiple'  => intval($v),     //当期方案倍数
                                   'issue'     => $k,             //追号期数
                                   'status'    => 0               //状态默认为0，生成当期或者取消当期时更新
                               );
        }
        if(  TRUE !== $oTask->taskDetailInsert( $aTraceDetailData ) )
        {//写入追号详情表失败
             return -2;
        }
        $oOrders   = A::singleton('model_orders');   //帐变模型
        //05：追号帐变数据
        $aTraceOrderData   = array(
                                 'iLotteryId'   => $aMethod['lotteryid'],
                                 'iMethodId'    => $aProject['methodid'],
                                 'iPackageId'   => $aUserInfo['packid'],
                                 'iTaskId'      => $iTaskId,//等待追号表插入ID
                                 'iModesId'     => $aProject['mode'],
                                 'iFromUserId'  => $aUserInfo['userid'],
                                 'iOrderType'   => ORDER_TYPE_ZHKK, //追号扣款帐变类型
                                 'fMoney'       => $aTraceData['taskprice'],   //产生帐变金额为总投注金额
                                 'sDescription' => '追号扣款',      //帐变描述
                                 'iChannelID'   => SYS_CHANNELID
                                );
        $mResult = $oOrders->addOrders( $aTraceOrderData );
        if( $mResult === -1009  )
        {//资金不够
            return -33;
        }
        elseif( $mResult !== TRUE )
        {//其他帐变错误
            return -3;
        }
        //06：判断是否在追号中包含当前期，如果包含当前期，则把当前期的追号转为方案
        if( isset($aTrace[$aUserInfo['currentissue']]) )
        {//需要转换
            $bIsFinish = $aTraceData['issuecount'] == 1 ? TRUE : FALSE;
            $iTimes    = $aTrace[$aUserInfo['currentissue']];
            $aNewData  = array();
            $aNewData['aCreateData']   = array( //当期追号返款帐变
                                           'iLotteryId'   => $aMethod['lotteryid'],
                                           'iMethodId'    => $aProject['methodid'],
                                           'iPackageId'   => $aUserInfo['packid'],
                                           'iTaskId'      => $iTaskId,//等待追号表插入ID
                                           'iModesId'     => $aProject['mode'],
                                           'iFromUserId'  => $aUserInfo['userid'],
                                           'iOrderType'   => ORDER_TYPE_DQZHFK, //追号扣款帐变类型
                                           'fMoney'       => $aTraceData['singleprice']*$iTimes,
                                           'sDescription' => '当期追号返款',      //帐变描述
                                           'iChannelID'   => SYS_CHANNELID
                                             );
            $aNewData['aProjectData']  = array(//方案数据
                                           'userid'        => $aUserInfo['userid'],
                                           'packageid'     => $aUserInfo['packid'],
                                           'taskid'        => $iTaskId,
                                           'lotteryid'     => $aMethod['lotteryid'],
                                           'methodid'      => $aProject['methodid'],
                                           'issue'         => $aUserInfo['currentissue'],
                                           'code'          => $aProject['codes'],
                                           'codetype'      => $aProject['type'],
                                           'singleprice'   => $aTraceData['singleprice'],
                                           'multiple'      => $iTimes,
                                           'totalprice'    => $aNewData['aCreateData']['fMoney'],
                                           'lvtopid'       => $aUserInfo['lvtopid'],
                                           'lvtoppoint'    => $aMethod['topuserpoint'],
                                           'lvproxyid'     => $aUserInfo['lvproxyid'],
                                           'modes'         => $aProject['mode'],
                                           'sqlnum'        => 1//无法计算，默认1
                                        );
            $aNewData['aJoinData']     = array(//加入游戏帐变
                                            'iLotteryId'   => $aMethod['lotteryid'],
                                            'iMethodId'    => $aProject['methodid'],
                                            'iPackageId'   => $aUserInfo['packid'],
                                            'iTaskId'      => $iTaskId,
                                            'iProjectId'   => 0,//等待方案插入ID
                                            'iModesId'     => $aProject['mode'],
                                            'iFromUserId'  => $aUserInfo['userid'],
                                            'iOrderType'   => ORDER_TYPE_JRYX, //加入游戏帐变类型
                                            'fMoney'       => $aNewData['aCreateData']['fMoney'],//产生帐变金额为总投注金额
                                            'sDescription' => '加入游戏',      //帐变描述
                                            'iChannelID'   => SYS_CHANNELID
                                         );
            if( $aMethod['userpoint'] > 0 )
            {
                $aNewData['aBackData'] = array( //销售返点帐变插入数据
                                            'iLotteryId'   => $aMethod['lotteryid'],
                                            'iMethodId'    => $aProject['methodid'],
                                            'iPackageId'   => $aUserInfo['packid'],
                                            'iTaskId'      => $iTaskId,
                                            'iProjectId'   => 0,//等待方案插入ID
                                            'iModesId'     => $aProject['mode'],
                                            'iFromUserId'  => $aUserInfo['userid'],
                                            'iOrderType'   => ORDER_TYPE_XSFD, //销售返点帐变类型
                                            'fMoney'       => $aNewData['aCreateData']['fMoney'] * $aMethod['userpoint'],
                                            'sDescription' => '销售返点',//帐变描述
                                            'iChannelID'   => SYS_CHANNELID
                                            );
            }
            $aNewData['aExpandData']   = $aExpandData;//号码扩展表
            foreach( $aNewData['aExpandData'] as & $ed1 )
            {
                $ed1['prize'] *= $iTimes;
            }
            $aNewData['aDiffData']     = $aDiffData;//返点差表
            foreach( $aNewData['aDiffData'] as & $ed2 )
            {
                $ed2['diffmoney'] *= $iTimes;
            }
            if( TRUE !==  $oTask->traceToProjectData($iTaskId, $aNewData, $bIsFinish) )
            {//生成注单失败
                return -4;
            }
        }
        return TRUE;
    }
    
    
    
    /**
     * 获取号码扩展表基本数据
     *
     * @param array $aProject //方案
     * @param array $aPrize   //奖金组
     * @param float $fRate    //模式比例
     * @return array          扩展表基本数据数组
     */
    private function _getExpandData( $aProject, $aPrize, $fRate )
    {
         $sMethodeName = $this->_aMethod_Config[$aProject['methodid']]; //玩法对应的表达式
         foreach( $aPrize as $k=>$v )
         {//奖金都按模式比例从新计算
             $aPrize[$k] = $v * $fRate;
         }
         $aExpandData = array();
         switch( $sMethodeName )
         {
             case 'SDDDS'://定单双
                         $aCode =  explode("|",$aProject['codes']);
                         if( !isset($aPrize[1]) || !isset($aPrize[2]) || !isset($aPrize[3])|| !isset($aPrize[4])
                             || !isset($aPrize[5]) || !isset($aPrize[6]) )
                         {
                              break;
                         }
                         if( in_array(0,$aCode) )
                         {//一等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 1,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[1],
                                                    'expandcode'=> '0'
                                             );
                         }
                         if( in_array(5,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 2,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[2],
                                                    'expandcode'=> '5'
                                             );
                         }
                         if( in_array(1,$aCode) )
                         {//三等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 3,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[3],
                                                    'expandcode'=> '1'
                                             );
                         }
                         if( in_array(4,$aCode) )
                         {//四等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 4,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[4],
                                                    'expandcode'=> '4'
                                             );
                         }
                         if( in_array(2,$aCode) )
                         {//五等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 5,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[5],
                                                    'expandcode'=> '2'
                                             );
                         }
                         if( in_array(3,$aCode) )
                         {//六等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 6,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[6],
                                                    'expandcode'=> '3'
                                             );
                         }
                         break;
             case 'SDCZW'://猜中位
                         $aCode =  explode("|",$aProject['codes']);
                         if( !isset($aPrize[1]) || !isset($aPrize[2]) || !isset($aPrize[3])|| !isset($aPrize[4]) )
                         {
                              break;
                         }
                         if( in_array('03',$aCode) )
                         {//一等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 1,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[1],
                                                    'expandcode'=> '03'
                                             );
                         }
                         if( in_array('09',$aCode) )
                         {//一等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 1,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[1],
                                                    'expandcode'=> '09'
                                             );
                         }
                         if( in_array('04',$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 2,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[2],
                                                    'expandcode'=> '04'
                                             );
                         }
                         if( in_array('08',$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 2,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[2],
                                                    'expandcode'=> '08'
                                             );
                         }
                         if( in_array('05',$aCode) )
                         {//三等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 3,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[3],
                                                    'expandcode'=> '05'
                                             );
                         }
                         if( in_array('07',$aCode) )
                         {//三等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 3,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[3],
                                                    'expandcode'=> '07'
                                             );
                         }
                         if( in_array('06',$aCode) )
                         {//四等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 4,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[4],
                                                    'expandcode'=> '06'
                                             );
                         }
                         break;
			 case 'BJHZDS':
                         $aCode =  explode("|",$aProject['codes']);
                         if( in_array(0,$aCode) )
                         {//一等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 1,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[1],
                                                    'expandcode'=> '0'
                                             );
                         }
                         if( in_array(1,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 2,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[2],
                                                    'expandcode'=> '1'
                                             );
                         }
				 break;
			 case 'BJHZDX':
                         $aCode =  explode("|",$aProject['codes']);
                         if( !isset($aPrize[1]) || !isset($aPrize[2]) || !isset($aPrize[3]) )
                         {
                              break;
                         }
                         if( in_array(2,$aCode) )
                         {//一等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 1,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[1],
                                                    'expandcode'=> '2'
                                             );
                         }
                         if( in_array(0,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 2,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[2],
                                                    'expandcode'=> '0'
                                             );
                         }
                         if( in_array(1,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 3,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[3],
                                                    'expandcode'=> '1'
                                             );
                         }
				 break;
			 case 'BJSXP':
                         $aCode =  explode("|",$aProject['codes']);
                         if( !isset($aPrize[1]) || !isset($aPrize[2]) || !isset($aPrize[3]) )
                         {
                              break;
                         }
                         if( in_array(2,$aCode) )
                         {//一等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 1,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[1],
                                                    'expandcode'=> '2'
                                             );
                         }
                         if( in_array(0,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 2,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[2],
                                                    'expandcode'=> '0'
                                             );
                         }
                         if( in_array(1,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 3,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[3],
                                                    'expandcode'=> '1'
                                             );
                         }
				 break;
			 case 'BJJOP':
                         $aCode =  explode("|",$aProject['codes']);
                         if( !isset($aPrize[1]) || !isset($aPrize[2]) || !isset($aPrize[3]) )
                         {
                              break;
                         }
                         if( in_array(2,$aCode) )
                         {//一等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 1,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[1],
                                                    'expandcode'=> '2'
                                             );
                         }
                         if( in_array(0,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 2,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[2],
                                                    'expandcode'=> '0'
                                             );
                         }
                         if( in_array(1,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 3,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[3],
                                                    'expandcode'=> '1'
                                             );
                         }
				 break;
             case 'TSH3'://三位特殊号
                         $aCode =  explode("|",$aProject['codes']);
                         if( !isset($aPrize[1]) || !isset($aPrize[2]) || !isset($aPrize[3]) )
                         {
                              break;
                         }
                         if( in_array(0,$aCode) )
                         {//一等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 1,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[1],
                                                    'expandcode'=> '0'
                                             );
                         }
                         if( in_array(1,$aCode) )
                         {//二等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 2,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[2],
                                                    'expandcode'=> '1'
                                             );
                         }
                         if( in_array(2,$aCode) )
                         {//三等奖
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => 3,
                                                    'codetimes' => 1,
                                                    'prize'     => $aPrize[3],
                                                    'expandcode'=> '2'
                                             );
                         }
                         break;
			 default     :
                         foreach( $aPrize as $k=>$v )
                         {
                             $aExpandData[] = array(
                                                    'projectid' => 0,
                                                    'level'     => $k,
                                                    'codetimes' => 1,
                                                    'prize'     => $v,
                                                    'expandcode'=> $aProject['codes']
                                             );
                         }
                         break;
         }
         return $aExpandData;
    }
    
    private function checkproject( $aProjetcData )
    {
        if( strlen($aProjetcData['codes']) == 0 || empty($aProjetcData['type']) || empty($aProjetcData['methodid'])
        || empty($aProjetcData['nums'])|| empty($aProjetcData['times']) || empty($aProjetcData['money'])
        || empty($aProjetcData['desc']) || empty($aProjetcData['mode']) )
        {//初步检测投注内容
            return FALSE;
        }
        $sMethodeName = $this->_aMethod_Config[$aProjetcData['methodid']]; //玩法对应的表达式
        $aSDYBaseCode = array('01','02','03','04','05','06','07','08','09','10','11');
        if( $aProjetcData['type'] == 'input' )
        {//输入型
            $aCode = explode("&", $aProjetcData['codes']);
            switch ( $sMethodeName )
            {
                case 'QZX3':
                case 'HZX3':
                    if( !preg_match("/^(([0-9]{3}&)*[0-9]{3})$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    break;
                case 'ZX5':
                    if( !preg_match("/^(([0-9]{5}&)*[0-9]{5})$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    break;
                case 'ZX4':
                    if( !preg_match("/^(([0-9]{4}&)*[0-9]{4})$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    break;
                case 'QZX2':
                case 'HZX2':
                    if( !preg_match("/^(([0-9]{2}&)*[0-9]{2})$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    break;
                case 'QZU2':
                case 'HZU2':
                    if( !preg_match("/^(([0-9]{2}&)*[0-9]{2})$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $aBaoZiCode2 = array('00','11','22','33','44','55','66','77','88','99');
                    if( count(array_intersect($aBaoZiCode2,$aCode)) > 0 )
                    {
                        return FALSE;
                    }
                    break;
                case 'QHHZX':
                case 'HHHZX':
                    if( !preg_match("/^(([0-9]{3}&)*[0-9]{3})$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $aBaoZiCode3 = array('000','111','222','333','444','555','666','777','888','999');
                    if( count(array_intersect($aBaoZiCode3,$aCode)) > 0 )
                    {
                        return FALSE;
                    }
                    break;
              case 'SDZX3':
              case 'SDZU3':
              case 'SDZX2':
              case 'SDZU2':
              case 'SDRX1':
              case 'SDRX2':
              case 'SDRX3':
              case 'SDRX4':
              case 'SDRX5':
              case 'SDRX6':
              case 'SDRX7':
              case 'SDRX8':
                  foreach ( $aCode as $sTmpCode )
                  {
                      $aTmpCode = explode(" ",$sTmpCode);
                      if( count($aTmpCode) != intval(substr($sMethodeName,-1)) )
                      {
                          return FALSE;
                      }
                      if( count($aTmpCode) != count(array_unique($aTmpCode)) )
                      {//单注号码间不能重复
                          return FALSE;
                      }
                      foreach ($aTmpCode as $sCodeDetail)
                      {
                          if( !in_array($sCodeDetail,$aSDYBaseCode) )
                          {
                              return FALSE;
                          }
                      }
                  }
                  break;
              default:
                  break;
            }
            $aCode = array_unique($aCode);
            $iNums = count($aCode);
        }
        else
        {
            switch ( $sMethodeName )
            {
				case 'ZX5'://5星直选
				case 'ZH5'://5星直选组合
				case 'TX5'://5星直选通选
				case 'ZX4'://4星直选
				case 'ZH4'://4星直选组合
				case 'TX4'://4星直选通选
				case 'QZX3'://前3直选
                case 'HZX3'://后3直选
				case 'ZH3' :
                case 'QZX2'://前2直选
                case 'HZX2'://后2直选
                case 'QDXDS':
                case '3DXDS':
                case 'HDXDS':
				case 'TX3' :
				case 'TSH3' : 
                    switch ($sMethodeName )
                    {
						case 'ZX5':
						case 'ZH5':
						case 'TX5':
                             if( !preg_match("/^(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
						case 'ZX4':
						case 'ZH4':
						case 'TX4':
                             if( !preg_match("/^(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/",$aProjetcData['codes'])
							)
                            {
                                return FALSE;
                            }
                            break;
                       case 'QZX3':
                        case 'HZX3':
						case 'ZH3' :
						case 'TX3' :
                            if( !preg_match("/^(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
                        case 'QZX2':
                        case 'HZX2':
                            if( !preg_match("/^(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
                        case 'QDXDS':
                        case 'HDXDS':
                            $aProjetcData['codes'] = str_replace(array('大','小','单','双'),array(0,1,2,3),$aProjetcData['codes']);
                            if( !preg_match("/^(([0-3]&){0,3}[0-3])\|(([0-3]&){0,3}[0-3])$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
						case 'TSH3' : 
                            $aProjetcData['codes'] = str_replace(array('豹子','顺子','对子'),array(0,1,2),$aProjetcData['codes']);
                            if( !preg_match("/^(([0-2]&){0,2}[0-2])$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
							break;
						case '3DXDS':
							$aProjetcData['codes'] = str_replace(array('大','小','单','双'),array(0,1,2,3),$aProjetcData['codes']);
							if( !preg_match("/^(([0-3]&){0,3}[0-3])\|(([0-3]&){0,3}[0-3])\|(([0-3]&){0,3}[0-3])$/",$aProjetcData['codes']) )
							{
								return FALSE;
							}
						break;
                       default:
                            return FALSE;
                            break;
                    }
                    $iNums = $sMethodeName == 'ZH5' ? 5 : ($sMethodeName == 'ZH4' ? 4 : ($sMethodeName == 'ZH3' ? 3 : 1));
                    $aCode = explode("|",$aProjetcData['codes']);
                    foreach ($aCode as $sCode)
                    {
                        $iUniqueCount = count(array_unique(explode("&", $sCode )));
                        if($iUniqueCount != count(explode("&", $sCode )))
                        {
                            return FALSE;
                        }
                        $iNums *= $iUniqueCount;
                    }
                    break;
                case 'DWD' ://正确号码格式:1&2&3&4|1&2&3&4|1&2&3&4|1&2&3&4|1&2&3&4,正确号码0-9
                case 'DWD3'://正确号码格式:1&2&3&4|1&2&3&4|1&2&3&4,正确号码0-9
                    switch ($sMethodeName )
                    {
                        case 'DWD' :
                            if( !preg_match("/^((([0-9]&){0,9}([0-9])?)\|){4}(([0-9]&){0,9}([0-9])?)$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            elseif( preg_match("/((([0-9]&)+)\|)/", $aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
                        case 'DWD3':
                            if( !preg_match("/^((([0-9]&){0,9}([0-9])?)\|){2}(([0-9]&){0,9}([0-9])?)$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            elseif( preg_match("/((([0-9]&)+)\|)/", $aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
                        default:
                            return FALSE;
                            break;
                    }
                    $iNums = 0;
                    $aCode = explode("|",$aProjetcData['codes']);
                    foreach ($aCode as $sCode)
                    {
                        if( strpos($sCode,'&') !== FALSE  || strlen($sCode) > 0)
                        {
                            $iUniqueCount = count(array_unique(explode("&", $sCode )));
                            if($iUniqueCount != count(explode("&", $sCode )))
                            {
                                return FALSE;
                            }
                            $iNums += $iUniqueCount;
                        }
                    }
                    break;
                case 'QZUS':
                case 'HZUS'://正确号码格式:1&2&3&4,正确号码0-9
                    if( !preg_match("/^(([0-9]&){1,9}[0-9])$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $iCodeCount = count(array_unique(explode("&", $aProjetcData['codes'])));
                    if($iCodeCount != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if($iCodeCount < 2)
                    {
                        return FALSE;
                    }
                    $iNums = $iCodeCount * ($iCodeCount - 1 );
                    break;
                case 'QZUL':
                case 'HZUL'://正确号码格式:1&2&3&4,正确号码0-9
                    if( !preg_match("/^(([0-9]&){2,9}[0-9])$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $iCodeCount = count(array_unique(explode("&", $aProjetcData['codes'])));
                    if($iCodeCount != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if($iCodeCount < 3)
                    {
                        return FALSE;
                    }
                    $iNums = $iCodeCount*($iCodeCount - 1)*($iCodeCount - 2)/6;
                    break;
                case 'HZXHZ'://直选和值特殊算法
                case 'QZXHZ':
                    $iNums = 0;
                    $aTempArray = array(0=>1,1=>3,2=>6,3=>10,4=>15,5=>21,6=>28,7=>36,8=>45,9=>55,10=>63,11=>69,12=>73,13=>75,14=>75,15=>73,16=>69,17=>63,18=>55,19=>45,20=>36,21=>28,22=>21,23=>15,24=>10,25=>6,26=>3,27=>1);
                    $aTmpCode = explode("&", $aProjetcData['codes']);
                    foreach ($aTmpCode as $sCode)
                    {
                        if( !isset($aTempArray[$sCode]) )
                        {
                            return FALSE;
                        }
                        $iNums += $aTempArray[$sCode];
                    }
                    break;
				case 'ZXKD' : //3位直选跨度
                    $iNums = 0;
                    $aTempArray = array(0=>10,1=>54,2=>96,3=>126,4=>144,5=>150,6=>144,7=>126,8=>96,9=>54);
                    $aTmpCode = explode("&", $aProjetcData['codes']);
                    foreach ($aTmpCode as $sCode)
                    {
                        if( !isset($aTempArray[$sCode]) )
                        {
                            return FALSE;
                        }
                        $iNums += $aTempArray[$sCode];
                    }
                    break;
				case 'ZXKD2' : //2位直选跨度
                    $iNums = 0;
                    $aTempArray = array(0=>10,1=>18,2=>16,3=>14,4=>12,5=>10,6=>8,7=>6,8=>4,9=>2);
                    $aTmpCode = explode("&", $aProjetcData['codes']);
                    foreach ($aTmpCode as $sCode)
                    {
                        if( !isset($aTempArray[$sCode]) )
                        {
                            return FALSE;
                        }
                        $iNums += $aTempArray[$sCode];
                    }
                    break;
                case 'QZUHZ':
                case 'HZUHZ':
                    $iNums = 0;
                    $aTempArray = array(1=>1,2=>2,3=>2,4=>4,5=>5,6=>6,7=>8,8=>10,9=>11,10=>13,11=>14,12=>14,13=>15,14=>15,15=>14,16=>14,17=>13,18=>11,19=>10,20=>8,21=>6,22=>5,23=>4,24=>2,25=>2,26=>1);
                    $aTmpCode = explode("&", $aProjetcData['codes']);
                    foreach ($aTmpCode as $sCode)
                    {
                        if( !isset($aTempArray[$sCode]) )
                        {
                            return FALSE;
                        }
                        $iNums += $aTempArray[$sCode];
                    }
                    break;
				case 'ZUHZ2':
                    $iNums = 0;
                    $aTempArray = array(0=>0,1=>1,2=>1,3=>2,4=>2,5=>3,6=>3,7=>4,8=>4,9=>5,10=>4,11=>4,12=>3,13=>3,14=>2,15=>2,16=>1,17=>1,18=>0);
					//var_dump($aProjetcData['codes']);die;
                    $aTmpCode = explode("&", $aProjetcData['codes']);
                    foreach ($aTmpCode as $sCode)
                    {
                        if( !isset($aTempArray[$sCode]) )
                        {
                            return FALSE;
                        }
                        $iNums += $aTempArray[$sCode];
                    }
					break;
                case 'QZU2':
                case 'HZU2':
				case '4BDW2' : 
				case '5BDW2': 
                case 'HBDW2'://正确号码格式:1&2&3&4,正确号码0-9
                    if( !preg_match("/^(([0-9]&){1,9}[0-9])$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $iCodeCount = count(array_unique(explode("&", $aProjetcData['codes'])));
                    if($iCodeCount != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if($iCodeCount < 2)
                    {
                        return FALSE;
                    }
                    $iNums = $iCodeCount * ($iCodeCount - 1 )/2;
                    break;
                case 'HBDW1'://正确号码格式:1&2&3&4,正确号码0-9
				case 'BDW1':
                    if( !preg_match("/^(([0-9]&){0,9}[0-9])$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = count(array_unique(explode("&", $aProjetcData['codes'])));
                    if($iNums != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    break;
                case 'SDZX3'://正确号码格式:01&10&08&09|01&10&08&09|01&10&08&09,正确号码01-11
                    if( !preg_match("/^(((0[1-9]&)|(1[01]&)){0,10}((0[1-9])|(1[01]))\|){2}(((0[1-9]&)|(1[01]&)){0,10}((0[1-9])|(1[01])))$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $aNums = array();
                    $aCode = explode("|",$aProjetcData['codes']);
                    foreach ($aCode as $sCode)
                    {
                        $iUniqueCount = count(array_unique(explode("&", $sCode )));
                        if($iUniqueCount != count(explode("&", $sCode )))
                        {
                            return FALSE;
                        }
                        $aNums[] = array_unique(explode("&", $sCode ));
                    }
                    if( count($aNums[0]) > 0 && count($aNums[1]) > 0 && count($aNums[2]) > 0 ){
                        for( $i=0;$i <count($aNums[0]); $i++ ){
                            for( $j=0; $j<count($aNums[1]); $j++ ){
                                for( $k=0; $k<count($aNums[2]); $k++ ){
                                    if( $aNums[0][$i] != $aNums[1][$j] && $aNums[0][$i] != $aNums[2][$k] && $aNums[1][$j] != $aNums[2][$k] ){
                                        $iNums++;
                                    }
                                }
                            }
                        }
                    }
                    break;
                case 'SDZU3'://正确号码格式:01&10&08&09,正确号码01-11
                    if( !preg_match("/^((0[1-9]&)|(1[01]&)){2,10}((0[1-9])|(1[01]))$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $iCodeCount = count(array_unique(explode("&", $aProjetcData['codes'])));
                    if($iCodeCount != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if($iCodeCount < 3)
                    {
                        return FALSE;
                    }
                    $iNums = $iCodeCount*($iCodeCount - 1)*($iCodeCount - 2)/6;
                    break;
                case 'SDZX2'://正确号码格式:01&10&08&09|01&10&08&09,正确号码01-11
                    if( !preg_match("/^(((0[1-9]&)|(1[01]&)){0,10}((0[1-9])|(1[01]))\|){1}(((0[1-9]&)|(1[01]&)){0,10}((0[1-9])|(1[01])))$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $aNums = array();
                    $aCode = explode("|",$aProjetcData['codes']);
                    foreach ($aCode as $sCode)
                    {
                        $iUniqueCount = count(array_unique(explode("&", $sCode )));
                        if($iUniqueCount != count(explode("&", $sCode )))
                        {
                            return FALSE;
                        }
                        $aNums[] = array_unique(explode("&", $sCode ));
                    }
                    if( count($aNums[0]) > 0 && count($aNums[1]) > 0 ){
                        for( $i=0; $i<count($aNums[0]); $i++ ){
                            for( $j=0; $j<count($aNums[1]); $j++ ){
                                if( $aNums[0][$i] != $aNums[1][$j]){
                                    $iNums++;
                                }
                            }
                        }
                    }
                    break;
                case 'SDZU2'://正确号码格式:01&10&08&09,正确号码01-11
                    if( !preg_match("/^((0[1-9]&)|(1[01]&)){1,10}((0[1-9])|(1[01]))$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $iCodeCount = count(array_unique(explode("&", $aProjetcData['codes'])));
                    if($iCodeCount != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if($iCodeCount < 2)
                    {
                        return FALSE;
                    }
                    $iNums = $iCodeCount*($iCodeCount - 1)/2;
                    break;
                case 'SDDWD'://正确号码格式:01&10&08&09|01&10&08&09|01&10&08&09,正确号码01-11
                    if( !preg_match("/^(((0[1-9]&)|(1[01]&)){0,10}((0[1-9])|(1[01]))?\|){2}((0[1-9]&)|(1[01]&)){0,10}((0[1-9])|(1[01]))?$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    elseif( preg_match("/((0[1-9]&)|(1[01]&))+\|/", $aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $aCode = explode("|",$aProjetcData['codes']);
                    foreach ($aCode as $sCode)
                    {
                        if( strpos($sCode,'&') !== FALSE || strlen($sCode) > 0)
                        {
                            $iUniqueCount = count(array_unique(explode("&", $sCode )));
                            if($iUniqueCount != count(explode("&", $sCode )))
                            {
                                return FALSE;
                            }
                            $iNums += $iUniqueCount;
                        }
                    }
                    break;
                case 'SDBDW':
                case 'SDDDS':
                case 'SDCZW':
                case 'SDRX1': //任选1中1
                    switch ($sMethodeName)
                    {
                        case 'SDDDS'://正确号码格式:0单5双&2单3双&3单2双&5单0双
                            $aProjetcData['codes'] = str_replace(array('0单5双','1单4双','2单3双','3单2双','4单1双','5单0双'),array(0,1,2,3,4,5),$aProjetcData['codes']);
                            if( !preg_match("/^([0-5]&){0,5}[0-5]$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
                        case 'SDCZW'://正确号码格式:3&4&5&6,正确号码3-9
                            if( !preg_match("/^([3-9]&){0,6}[3-9]$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
                        case 'SDBDW':
                        case 'SDRX1'://正确号码格式:01&10&08&09,正确号码01-11
                            if( !preg_match("/^((0[1-9]&)|(1[01]&)){0,10}((0[1-9])|(1[01]))$/",$aProjetcData['codes']) )
                            {
                                return FALSE;
                            }
                            break;
                        default:
                            break;
                    }
                    $iNums = 0;
                    $aTmpCode = array_unique(explode("&", $aProjetcData['codes']));
                    if(count($aTmpCode) != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if(count($aTmpCode) < 1)
                    {
                        return FALSE;
                    }
                    $iNums = $this->_GetCombinCount(count($aTmpCode),1);
                    break;
                case 'SDRX2':
                case 'SDRX3':
                case 'SDRX4':
                case 'SDRX5':
                case 'SDRX6':
                case 'SDRX7':
                case 'SDRX8':
                    $iSelNum = intval(substr($sMethodeName,-1));//选择号码个数
                    $iRegNum = $iSelNum - 1;//正则匹配所需,正确号码格式:01&10&08&09,正确号码01-11
                    if( !preg_match("/^((0[1-9]&)|(1[01]&)){".$iRegNum.",10}((0[1-9])|(1[01]))$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $aTmpCode = array_unique(explode("&", $aProjetcData['codes']));
                    if(count($aTmpCode) != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if(count($aTmpCode) < $iSelNum)
                    {
                        return FALSE;
                    }
                    $iNums = $this->_GetCombinCount(count($aTmpCode),$iSelNum);
                    break;
				case 'BJRX1': //正确号码格式:01&10&08&09,正确号码01-80
				    $iMaxCodeCount = $aProjetcData['maxcodecount'] > 0 ? $aProjetcData['maxcodecount'] - 1 : 79;//最大选择号码个数
					if( !preg_match("/^((0[1-9]&)|([1-7][0-9]&)){0,".$iMaxCodeCount."}((0[1-9])|([1-7][0-9])|(80))$/",$aProjetcData['codes']) )
					{
						return FALSE;
					}
                    $iNums = 0;
                    $aTmpCode = array_unique(explode("&", $aProjetcData['codes']));
                    if(count($aTmpCode) != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if(count($aTmpCode) < 1)
                    {
                        return FALSE;
                    }
                    $iNums = $this->_GetCombinCount(count($aTmpCode),1);
					break;
                case 'BJRX2':
                case 'BJRX3':
                case 'BJRX4':
                case 'BJRX5':
                case 'BJRX6':
                case 'BJRX7':
                    $iSelNum = intval(substr($sMethodeName,-1));//选择号码个数
                    $iRegNum = $iSelNum - 1;//正则匹配所需,正确号码格式:01&10&08&09,正确号码01-11
                    $iMaxCodeCount = $aProjetcData['maxcodecount'] - 1;//最大选择号码个数
                    if( !preg_match("/^((0[1-9]&)|([1-7][0-9]&)){".$iRegNum.",".$iMaxCodeCount."}((0[1-9])|([1-7][0-9])|(80))$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $aTmpCode = array_unique(explode("&", $aProjetcData['codes']));
                    if(count($aTmpCode) != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if(count($aTmpCode) < $iSelNum)
                    {
                        return FALSE;
                    }
                    $iNums = $this->_GetCombinCount(count($aTmpCode),$iSelNum);
                    break;
				case 'BJHZDX':
				case 'BJHZDS':
				case 'BJSXP':
				case 'BJJOP':
					$aProjetcData['codes'] = str_replace(array('大','小','单','双','和','上','下','奇','偶','中'), array(0,1,0,1,2,0,1,0,1,2),$aProjetcData['codes']);
					if( !preg_match("/^([0-2]&){0,2}([0-2])$/",$aProjetcData['codes']) )
					{
						return FALSE;
					}
                    $iNums = 1;
                    $aCode = explode("&",$aProjetcData['codes']);
                    $iNums = count($aCode);
					break;
				case 'WXZU120':
 				case 'SXZU24' :
				case 'SXZU6' :
                   if( !preg_match("/^(([0-9]&){1,9}[0-9])$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
					if($sMethodeName == 'WXZU120')
					{
						$min_chosen = 5;
					}
					elseif($sMethodeName == 'SXZU24')
					{
						$min_chosen = 4;
					}
					elseif($sMethodeName == 'SXZU6')
					{
						$min_chosen = 2;
					}
                    $iNums = 0;
                    $iCodeCount = count(array_unique(explode("&", $aProjetcData['codes'])));
                    if($iCodeCount != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if($iCodeCount < $min_chosen)
                    {
                        return FALSE;
                    }
                    $iNums = $this->_GetCombinCount($iCodeCount, $min_chosen);
                    break;
				case 'SXZU12' :
				case 'SXZU4' :
				case 'WXZU60' : //1二重号+3单号
				case 'WXZU30' : //2二重号+1单号
				case 'WXZU20' : //1三重号+2单号
				case 'WXZU10' : //1三重号+1二重号
				case 'WXZU5' : //1三重号+1二重号
					if( !preg_match("/^(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/",$aProjetcData['codes']) )
					{
						return FALSE;
					}
					if( $sMethodeName == 'WXZU60' )
					{
						$minRepeat = 1;
						$minSingle = 3;
					}
					elseif($sMethodeName == 'WXZU30')
					{
						$minRepeat = 2;
						$minSingle = 1;
					}
					elseif($sMethodeName == 'WXZU20' || $sMethodeName == 'SXZU12')
					{
						$minRepeat = 1;
						$minSingle = 2;
					}
					elseif($sMethodeName == 'WXZU10' || $sMethodeName == 'WXZU5' || $sMethodeName == 'SXZU4')
					{
						$minRepeat = 1;
						$minSingle = 1;
					}
                    $aCode = explode("|",$aProjetcData['codes']);
					$aRepeat = explode("&", $aCode[0]);
					$aSingle = explode("&", $aCode[1]);
					if(sizeof($aRepeat) < $minRepeat || sizeof($aSingle) < $minSingle)
					{
						return FALSE;
					}
					$iNums = $this->_GetCombinCount( sizeof($aRepeat), $minRepeat ) * $this->_GetCombinCount( sizeof($aSingle), $minSingle );
					$intersect = array_intersect($aRepeat, $aSingle);
					if( sizeof($intersect) > 0 )
					{
						if($sMethodeName == 'WXZU60')
						{
							$iNums -= $this->_GetCombinCount( sizeof($intersect),1 ) * $this->_GetCombinCount( sizeof($aSingle)-1,2 );
						}
						elseif($sMethodeName == 'WXZU30')
						{
							$iNums -= $this->_GetCombinCount( sizeof($intersect),2 ) * $this->_GetCombinCount( 2, 1 );
							if(sizeof($aRepeat)-sizeof($intersect) > 0)
							{
								$iNums -= $this->_GetCombinCount(sizeof($intersect),1) * $this->_GetCombinCount(sizeof($aRepeat)-sizeof($intersect),1);
							}
						}
						elseif($sMethodeName == 'WXZU20')
						{
							$iNums -= $this->_GetCombinCount(sizeof($intersect),1) * $this->_GetCombinCount(sizeof($aSingle)-1,1);
						}
						elseif($sMethodeName == 'WXZU10' || $sMethodeName == 'WXZU5' || $sMethodeName == 'SXZU4')
						{
							$iNums -= $this->_GetCombinCount(sizeof($intersect),1);
						}
						elseif($sMethodeName == 'SXZU12')
						{
							$iNums -= $this->_GetCombinCount( sizeof($intersect),1 ) * $this->_GetCombinCount( sizeof($aSingle)-1,1 );
						}
					}
					break;
				case 'ZU3BD' : 
                   if( !preg_match("/^[0-9]$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
					$iNums = 54;
					break;
				case 'ZU2BD' : 
                   if( !preg_match("/^[0-9]$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
					$iNums = 9;
					break;
				case 'HZWS' : 
                    if( !preg_match("/^(([0-9]&){1,9}[0-9])$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = count(array_unique(explode("&", $aProjetcData['codes'])));
					break;
				case 'ZXHZ2' : 
                    $iNums = 0;
                    $aTempArray = array(
						0=>1,
						1=>2,
						2=>3,
						3=>4,
						4=>5,
						5=>6,
						6=>7,
						7=>8,
						8=>9,
						9=>10,
						10=>9,
						11=>8,
						12=>7,
						13=>6,
						14=>5,
						15=>4,
						16=>3,
						17=>2,
						18=>1);
                    $aTmpCode = explode("&", $aProjetcData['codes']);
                    foreach ($aTmpCode as $sCode)
                    {
                        if( !isset($aTempArray[$sCode]) )
                        {
                            return FALSE;
                        }
                        $iNums += $aTempArray[$sCode];
                    }
					break;
				case '5BDW3': 
                    if( !preg_match("/^(([0-9]&){1,9}[0-9])$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 0;
                    $iCodeCount = count(array_unique(explode("&", $aProjetcData['codes'])));
                    if($iCodeCount != count(explode("&", $aProjetcData['codes'] )))
                    {
                        return FALSE;
                    }
                    if($iCodeCount < 2)
                    {
                        return FALSE;
                    }
                    $iNums = $this->_GetCombinCount($iCodeCount, 3);
					break;
				case '5QW':
				case '4QW':
				case '3QW':
					$aProjetcData['codes'] = str_replace(array('小号','大号'),array(0,1),$aProjetcData['codes']);
					if($sMethodeName == '5QW')
					{
						$pattern = '/^(([01]&){0,1}[01])\|(([01]&){0,1}[01])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/';
					}
					else if($sMethodeName == '4QW')
					{
						$pattern = '/^(([01]&){0,1}[01])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/';
					}
					else
					{
						$pattern = '/^(([01]&){0,1}[01])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/';
					}
					if( !preg_match($pattern,$aProjetcData['codes']) )
					{
						return FALSE;
					}
					$iNums = 1;
                    $aCode = explode("|",$aProjetcData['codes']);
                    foreach ($aCode as $sCode)
                    {
                        $iUniqueCount = count(array_unique(explode("&", $sCode )));
                        if($iUniqueCount != count(explode("&", $sCode )))
                        {
                            return FALSE;
                        }
                        $iNums *= $iUniqueCount;
                    }
					break;
				case '5QJ':
				case '4QJ':
				case '3QJ':
					$aProjetcData['codes'] = str_replace(array('一区','二区','三区','四区','五区'),array(0,1,2,3,4),$aProjetcData['codes']);
					if($sMethodeName == '5QJ')
					{
						$pattern = '/^(([0-4]&){0,4}[0-4])\|(([0-4]&){0,4}[0-4])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/';
					}
					else if($sMethodeName == '4QJ')
					{
						$pattern = '/^(([0-4]&){0,4}[0-4])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/';
					}
					else
					{
						$pattern = '/^(([0-4]&){0,4}[0-4])\|(([0-9]&){0,9}[0-9])\|(([0-9]&){0,9}[0-9])$/';
					}
					if( !preg_match($pattern,$aProjetcData['codes']) )
					{
						return FALSE;
					}
					$iNums = 1;
                    $aCode = explode("|",$aProjetcData['codes']);
                    foreach ($aCode as $sCode)
                    {
                        $iUniqueCount = count(array_unique(explode("&", $sCode )));
                        if($iUniqueCount != count(explode("&", $sCode )))
                        {
                            return FALSE;
                        }
                        $iNums *= $iUniqueCount;
                    }
					break;
				case 'HSCS':
				case 'SXBX':
				case 'SJFC':
                    if( !preg_match("/^(([0-9]&){0,9}[0-9])$/",$aProjetcData['codes']) )
                    {
                        return FALSE;
                    }
                    $iNums = 1;
                    $aCode = explode("|",$aProjetcData['codes']);
                    foreach ($aCode as $sCode)
                    {
                        $iUniqueCount = count(array_unique(explode("&", $sCode )));
                        if($iUniqueCount != count(explode("&", $sCode )))
                        {
                            return FALSE;
                        }
                        $iNums *= $iUniqueCount;
                    }
					break;
				default:
                    $iNums = 0;
                    break;
            }
        }
        $iRate = $GLOBALS['config']['modes'][$aProjetcData['mode']]['rate'];
        if( $iNums != $aProjetcData['nums']
        || abs($iNums * $aProjetcData['times'] * $iRate * 2 - $aProjetcData['money']) > 0.00001 )
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
}
?>