<?php
/**
 *  不活跃用户清理
 * 
 * 功能: 
 *  根据设置的条件每天清理不活跃的用户[只依据帐变]
 *  
 * 
 * @author  james 
 * @version 1.1.0
 * @package passort
 */

class model_inactionuserclear extends basemodel
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
    
    
    public function clearuser()
    {
        $sMsg     = " Clear User: \n ";
        $iNowTime = time();    //程序启动时间，用于重复调用，不必再获取
        //01: 先获取配置内容
        $aConfig = array();
        //不活跃多少天的 int
        $aConfig['clearday'] = intval( getConfigValue( 'unactiveclearday', 0 ) );
        //是否运行 int 0:不运行，1:运行
        $aConfig['isrun']    = intval( getConfigValue( 'unactiveclearrun', 0 ) );
        //最小余额 float 0.00
        $aConfig['mincash']  = floatval( getConfigValue( 'unactiveclearmincash', 0.00 ) );
        //最大余额 float 0.00
        $aConfig['maxcash']  = floatval( getConfigValue( 'unactiveclearmaxcash', 0.00 ) );
        //开始清理时间 string 00:00:00
        $aConfig['start']    = getFilterDate( getConfigValue( 'unactiveclearstart', "00:00:00" ), "H:i:s" );
        //结束清理时间 string 00:00:00
        $aConfig['end']      = getFilterDate( getConfigValue( 'unactiveclearend', "00:00:00" ), "H:i:s" );
        //清理动作 string delete:删除,freeze:冻结
        $aConfig['action']   = daddslashes( getConfigValue( 'unactiveclearaction', '' ) );
        
        //02: 检测是否需要运行，如果不运行，则直接退出
        if( $aConfig['isrun'] != 1 )
        {
            return $sMsg." config set stop run \n";
        }
        
        
        //03: 检测设置参数是否支持运行，如果不支持则退出
        /*  1. 天数必须大于0
         *  2. 最大余额必须大于0
         *  3. 开始清理时间和结束整理时间不能完全一样，即不能在一个时刻
         *  4. 清理动作只能是 delete或者freeze
         */
        if( $aConfig['clearday'] <= 0 || $aConfig['maxcash'] <= 0 || $aConfig['start'] == $aConfig['end']
            || !in_array( $aConfig['action'], array('delete','freeze') ) )
        {
            return $sMsg." some of  config params set wrong value \n";
        }
        
        //04: 整理检测条件
        $dLastUpdateTime  = date( "Y-m-d H:i:s", strtotime("-".$aConfig['clearday']." day") );
        $aConfig['start'] = strtotime( date( "Y-m-d ".$aConfig['start'] ) );
        $aConfig['end']   = strtotime( date( "Y-m-d ".$aConfig['end'] ) );
        if( $aConfig['start'] > $aConfig['end'] )
        {//跨天，比如晚上11点第二天2点
            $aConfig['start'] -= 86400;
        }
        
        //05: 检测是否在允许运行的时间范围内
        if( $iNowTime < $aConfig['start'] || $iNowTime > $aConfig['end'] )
        {//不在允许运行的时间范围内
            return $sMsg." Now not in run time \n";
        }
        
        //06: 获取在银行大厅符合条件的不活跃用户
        /*  1. 多少天没有活跃的
         *  2. 余额大于或等于最小余额
         *  3. 余额小于或等于最大余额
         *  4. 没有下级(不包括测试帐户和总代管理员以及被删除的)[不包括总代]
         *  5. 没有冻结金额
         */
       $sSql = " SELECT A.`userid`,A.`usertype`,A.`parentid`,COUNT(B.`userid`) AS childid, U.`channelbalance` 
                  FROM `usertree` AS A
                  LEFT JOIN `usertree` AS B ON (B.`parentid`=A.`userid` AND B.`isdeleted`='0' 
                  AND B.`istester`='0' AND B.`usertype`!='2' 
                  ".( $aConfig['action']=='freeze' ? " AND B.`frozentype`='0' " : "" )." )
                  LEFT JOIN `userfund` AS U ON A.`userid`=U.`userid`
                  WHERE A.`isdeleted`='0' AND A.`istester`='0' AND A.`usertype`!='2' AND A.`parentid`!='0'
                  ".( $aConfig['action']=='freeze' ? " AND A.`frozentype`='0' " : "" )."
                  AND U.`lastactivetime`<'".$dLastUpdateTime."' 
                  AND U.`channelbalance`>='".$aConfig['mincash']."'
                  AND U.`channelbalance`<='".$aConfig['maxcash']."'
                  AND U.`holdbalance`='0'
                  GROUP BY A.`userid` HAVING childid<=0 ";
        $aUsers = $this->oDB->getAll( $sSql );
        if( empty($aUsers) )
        {//没有需要清理的用户则直接返回成功
            return $sMsg." passport no inaction user \n";
        }
        $aUserIds = array(); //用户ID集合
        $aTempArr = array(); //临时数组
        foreach( $aUsers as $aUser )
        {
            $aTempArr[$aUser['userid']] = $aUser;
            $aUserIds[]                 = $aUser['userid'];
        }
        $aUsers = $aTempArr;
        
        //07: 过滤在银行大厅不活跃但在其他频道是活跃的用户[调用API返回其他频道也不活跃的用户以及资金]
        $sSql      = " SELECT * FROM `channels` WHERE `pid`='0' AND `isdisabled`='0' ";
        $aChannels = $this->oDB->getAll( $sSql );
        if( !empty($aChannels) )
        {//有活跃频道则检测所有频道
            //组合每个频道需要传入的数据
            $aTempArr = array(
                              'lastupdatetime' => $dLastUpdateTime, //天数
                              'mincash'        => $aConfig['mincash'], //最小金额
                              'maxcash'        => $aConfig['maxcash'], //最大金额
                              'action'         => $aConfig['action']  //执行动作
                        );
            //07 03: 循环检测每个频道
            foreach( $aChannels as $aChannel )
            {
                $aTempArr['users'] = $aUserIds;
                $oCliApi           = new cliapi( $aChannel['id'], 'checkInActionUser', TRUE );
                $oCliApi->setResultType('serial');
                $oCliApi->sendRequest( $aTempArr );
                $aResult = $oCliApi->getDatas();
                if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
                {
                    return $sMsg." checkInActionUser API wrong channelID:".$aChannel['id'].
                           ",path:".$aChannel['path']."\n";
                }
                $aResult  = $aResult['data'];
                if( empty($aResult) )
                {//如果已经过滤完，则直接返回
                    return $sMsg." no user need clear \n";
                }
                $aUserIds = array(); 
                foreach( $aUsers as $k=>$v )
                {
                    //如果已经过滤掉则删除
                    if( !isset($aResult[$k]) )
                    {
                        unset($aUsers[$k]);
                    }
                    elseif( ($v['channelbalance'] + $aResult[$k]['channelbalance']) > $aConfig['maxcash'] )
                    {//如果平台之间加起来大于最大金额，则过滤掉
                        unset($aUsers[$k]);
                    }
                    else 
                    {//否则金额加起来
                        $aUsers[$k]['channelbalance'] = $v['channelbalance'] + $aResult[$k]['channelbalance'];
                        $aUserIds[] = $k;
                    }
                }
                if( empty($aUsers) )
                {//如果已经过滤完，则直接返回
                    return $sMsg." no user need clear \n";
                }
            }
        }
        
        if( empty($aUsers) )
        {//如果已经过滤完，则直接返回
            return $sMsg." no user need clear \n";
        }
                
        //08: 把其他频道的不为0的钱都转到银行
        if( !empty($aChannels) )
        {
            //08 01: 循环把所有频道的钱都集中到银行[返回转钱失败的用户]
            foreach( $aChannels as $aChannel )
            {
                $aUserIdGroups = array_chunk($aUserIds,2000);
                foreach ($aUserIdGroups as $aUserTmpIds)
                {
                    $oCliApi = new cliapi( $aChannel['id'], 'clearTransition', TRUE );
                    $oCliApi->setResultType('serial');
                    $oCliApi->setTimeOut(200);
                    $oCliApi->sendRequest( array('users'=>$aUserTmpIds) );
                    $aResult = $oCliApi->getDatas();
                    if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
                    {
                        return $sMsg." clearTransition API wrong channelID:".$aChannel['id'].
                           ",path:".$aChannel['path']." \n";
                    }
                    //0802: 把转帐失败的用户清理掉，等待下次执行
                    $aFilterUsers = $aResult['data'];
                    $aUserIds     = array_diff( $aUserIds, $aFilterUsers );
                    foreach( $aFilterUsers as $v )
                    {
                        if( !is_numeric($v) )
                        {
                            return $sMsg." The clearTransition API back data error \n";
                        }
                        if( isset($aUsers[$v]) )
                        {
                            unset($aUsers[$v]);
                        }
                    }
                }
            }
        }
        
        
        //09: 把符合条件的用户的钱转到上级[只转有剩余余额的并且余额小于等于 清理条件的最大余额]
        //09 01: 获取所有用户银行的现在的可用余额
        $sSql = " SELECT `userid`,`availablebalance` FROM `userfund` WHERE `holdbalance`='0' 
                  AND `availablebalance`>'0' AND `userid` IN(" . implode(",", $aUserIds) . ") 
                  AND `availablebalance`<='".$aConfig['maxcash']."'";
        $aResult = $this->oDB->getAll( $sSql );
        if( $this->oDB->errno() > 0 )
        {//错误
            return $sMsg." get userfund availablebalance error in step 9 \n";
        }
        $oUserFund = new model_userfund();
        $aTempArr  = array();
        if( !empty($aResult) )
        {//如果有需要转的用户则循环对每个用户进行操作
            foreach( $aResult as $v )
            {
                if( !isset($aUsers[$v['userid']]) )
                {//获取数据错误
                    return $sMsg." get user data wrong \n";
                }
                $mResult = $oUserFund->clearUpToParent( $v['userid'], $aUsers[$v['userid']]['parentid'], 
                                                        $v['availablebalance'] );
                if( $mResult !== TRUE )
                {//如果转钱到上级出现问题，则把该用户踢掉等到下一次起来执行
                    unset($aUsers[$v['userid']]);
                    $aTempArr[] = $v['userid'];
                }
            }
        }
        
        
        //10: 对最后的用户执行清理[冻结或者删除]
        //过滤已经踢掉的用户
        $aUserIds = array_diff( $aUserIds, $aTempArr );
        if(empty($aUserIds))
        {
            return " Clear  user[".implode(",",$aTempArr)."] transfer money to parent fail \n";
        }
        /* 1. 如果是冻结则，更改为公司冻结，只可登陆,否则逻辑删除set isdeleted=1
         * 2. 未删除的并且不是测试帐户
         * 3. 最后活跃时间小于清理条件的最后活跃时间，A、C、D、E四个金额必须都为0
         * 4. 包含在最后过滤掉的用户条件里面
         */
        /*$sSql     = " UPDATE `usertree` AS ut LEFT JOIN `userfund` AS uf ON ut.`userid`=uf.`userid` 
                      SET ".( $aConfig['action']=='freeze' ? " ut.`isfrozen`='2',ut.`frozentype`='2' " : 
                      " ut.`isdeleted`='1' " )." WHERE ut.`isdeleted`='0' AND ut.`istester`='0' ".
                      ( $aConfig['action']=='freeze' ? " AND ut.`frozentype`='0' " : "" ).
                      " AND uf.`lastactivetime`<'".$dLastUpdateTime."' AND uf.`availablebalance`='0' 
                      AND uf.`holdbalance`='0' AND uf.`channelbalance`='0' AND uf.`cashbalance`='0' 
                      AND ut.`userid` IN(".implode( ",", $aUserIds ).")";
        $mResult = $this->oDB->query( $sSql );*/
        // 首先查询满足条件的记录
        $sSql = "SELECT `userid` FROM `userfund`"
        		. " WHERE `lastactivetime`<'".$dLastUpdateTime."' AND `availablebalance`='0' AND `holdbalance`='0'"
        		. " AND `channelbalance`='0' AND `cashbalance`='0' AND `userid` IN(".implode( ",", $aUserIds ).")";
        $aResult = $this->oDB->getAll($sSql);
        if( empty($aResult) )
        {//没有需要清理的用户则直接返回成功
            return $sMsg." passport no inaction user \n";
        }
        $sUserId = "";
        foreach ($aResult as $k => $v){
        	$sUserId .= $v['userid'] . ',';
        }
        $sUserId = substr($sUserId, 0, -1);
        // 启用事务 1/21/2011
        if ( $aConfig['action'] == 'delete' ) $this->oDB->doTransaction();
        // 更新用户数据状态
        $sUpd = "UPDATE `usertree` SET ".( $aConfig['action']=='freeze' ? " `isfrozen`='2',`frozentype`='2' " :
        		 " `isdeleted`='1' " )." WHERE `userid` IN ({$sUserId})"
        		 . " AND `isdeleted`='0' AND `istester`='0' "
        		 . ( $aConfig['action']=='freeze' ? " AND `frozentype`='0' " : "" );
        $mResult = $this->oDB->query( $sUpd );
        if( $this->oDB->errno() > 0 )
        {
        	// 1/21/2011
        	if ( $aConfig['action'] == 'delete' ) $this->oDB->doRollback();
            return $sMsg." UPDATE users status[clear] error \n";
        }
        
        // 增加对用户绑定的银行卡逻辑删除 start 1/21/2011
        if ( $aConfig['action'] == 'delete' )
        {
        	$sLdelbankc = "UPDATE `user_bank_info` SET `status`='2' WHERE `user_id` IN ({$sUserId})";
        	$aRe = $this->oDB->query( $sLdelbankc );
        	if( $this->oDB->errno() > 0 )
        	{
        		$this->oDB->doRollback();
            	return $sMsg." UPDATE user bankcard status[delete] error \n";
        	}
        }

        if ( $aConfig['action'] == 'delete' ) $this->oDB->doCommit();
        //end 1/21/2011
        
        //执行成功
        return $sMsg." Clear $mResult user[".$sUserId."] by ".$aConfig['action']." success \n";
    }
    
}
?>
