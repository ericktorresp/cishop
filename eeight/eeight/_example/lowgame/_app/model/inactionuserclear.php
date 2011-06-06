<?php
/**
 *  不活跃用户清理[仅供API调用]
 * 
 * 功能: 
 *  1. 根据银行大厅提交过来的不活跃用户，过滤本频道不符合条件的用户[即在本频道是活跃的用户]，
 *     并返回过滤后的用户在本频道的余额
 * 
 * - filterUsers()          过滤本频道活跃的用户
 * - transitionToPassport() 把符合条件的用户所有的钱都转到passport
 * 
 * @author  james   090915
 * @version 1.2.0
 * @package passort
 */

class model_inactionuserclear extends basemodel
{
    /**
     * 构造函数
     * @access  public
     * @return  void
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }
    
    
    /**
     * 过滤本频道活跃的用户
     *
     * @author  james   090910
     * @param   array    $aData
     * ------------------------------
     * $aData['lastupdatetime'] string  //最后活跃时间
     * $aData['mincash']        float   //最小金额
     * $aData['maxcash']        float   //最大金额
     * $aData['action']         string  //清理动作(delete|freeze)
     * $aData['users']          array   //在银行大厅过滤了以后的用户ID组
     */
    public function filterUsers( $aData=array() )
    {
    	//01: 参数检查
    	if( empty($aData) || !is_array($aData) )
    	{//参数
    		return FALSE;
    	}
    	$aData['lastupdatetime'] = isset($aData['lastupdatetime']) ? getFilterDate($aData['lastupdatetime']) : "";
    	if( empty($aData['lastupdatetime']) )
   	    {//最后活跃时间必须正确
   	    	return FALSE;
   	    }
        if( !isset($aData['mincash']) || !is_numeric($aData['mincash']) )
        {//最小金额
            return FALSE;
        }
        $aData['mincash'] = floatval($aData['mincash']);
        if( !isset($aData['maxcash']) || !is_numeric($aData['maxcash']) || $aData['maxcash'] <= 0 )
        {//最大金额
            return FALSE;
        }
        $aData['maxcash'] = floatval($aData['maxcash']);
        if( !isset($aData['action']) || !in_array( $aData['action'], array('delete','freeze') ) )
        {//执行动作
            return FALSE;
        }
        $aData['action'] = daddslashes($aData['action']);
        if( !isset($aData['users']) || !is_array($aData['users']) )
        {//在银行大厅过滤了以后的用户ID组
            return FALSE;
        }
        $sTempStr = implode( "", $aData['users'] );
        if( preg_match( "/[^0-9]/", $sTempStr ) )
        {//检测用户组是否正确[只包含数字]
        	return FALSE;
        }
        
        //02: 过滤在该频道的活跃用户
        /*  1. 最后活跃时间大于或等于给定的最后活跃时间
         *  2. 余额小于最小余额
         *  3. 余额大于最大余额
         *  4. 是总代，已删除，是测试帐户，总代管理员或者（如果是冻结操作则已冻结的则踢出）
         *  5. 在银行大厅过滤后的ID数组里面
         *  6. 已激活的,有冻结金额的
         */
        $sSql = " SELECT A.`userid` FROM `usertree` AS A LEFT JOIN `userfund` AS U ON A.`userid`=U.`userid`
                  WHERE (A.`isdeleted`!='0' OR A.`istester`!='0' OR A.`usertype`='2' OR A.`parentid`='0'
                  ".( $aData['action']=='freeze' ? " OR A.`frozentype`!='0' " : "" )."
                  OR U.`lastactivetime`>='".$aData['lastupdatetime']."' 
                  OR U.`channelbalance`<'".$aData['mincash']."'
                  OR U.`channelbalance`>'".$aData['maxcash']."'
                  OR U.`holdbalance`!='0')
                  AND A.`userid` IN (".implode(",",$aData['users']).") ";
        $aFilterUser = $this->oDB->getAll( $sSql );
        if( $this->oDB->errno() > 0 )
        {
        	return FALSE;
        }
        $aTempArr = array();
        if( !empty($aFilterUser) )
        {//如果有在本频道活跃的用户，则从原用户组里过滤掉这些用户
        	foreach( $aFilterUser as $v )
        	{
        		$aTempArr[] = $v['userid'];
        	}
        	$aData['users'] = array_diff( $aData['users'], $aTempArr );
        }
        
        /*
         * 03: 获取过滤以后的用户，即在本频道也不活跃的用户的资金情况
         */
        $aResult  = array(); //最终返回的结果集合
        
        //如果全部过滤掉，则直接返回空数组
        if( empty($aData['users']) )
        {
        	return $aResult;
        }
        //获取用户资金
        $sSql = " SELECT `userid`,`channelbalance` FROM `userfund` 
                  WHERE `userid` IN(".implode(",",$aData['users']).") AND `holdbalance`='0' ";
        $aResult = $this->oDB->getAll( $sSql );
        if( $this->oDB->errno() > 0 )
        {
            return FALSE;
        }
        $aTempArr = array();
        foreach( $aResult as $v )
        {
        	$aTempArr[$v['userid']] = $v['channelbalance'];
        }
        $aResult  = array();
        foreach( $aData['users'] as $v )
        {
        	$aResult[$v] = array( 'userid'=>$v, 'channelbalance'=> isset($aTempArr[$v]) ? $aTempArr[$v] : '0.00' );
        }
        return $aResult;
    }
    
    
    
    /**
     * 把符合条件的用户所有的钱都转到passport
     *
     * @author  james   090910
     * @param   arraty  $aUsers
     * @return  boolean TRUE/FALSE
     */
    public function transitionToPassport( $aUsers=array() )
    {
        //01: 参数检查
        if( empty($aUsers) || !is_array($aUsers) )
        {//参数
            return FALSE;
        }
        $sTempStr = implode( "", $aUsers );
        if( preg_match( "/[^0-9]/", $sTempStr ) )
        {//检测用户组是否正确[只包含数字]
            return FALSE;
        }
        
        $aBackData = array();   //处理失败的用户ID数组，用于返回后剔除
        //02: 获取用户在该频道的可以转的频道余额[可用余额必须大于0]
        $sSql = " SELECT `userid`,`availablebalance` FROM `userfund` 
                  WHERE `userid` IN(" . implode(",", $aUsers) . ") AND `holdbalance`='0' AND `availablebalance`>'0' ";
        $aResult = $this->oDB->getAll( $sSql );
        if( $this->oDB->errno() > 0 )
        {//错误
        	return FALSE;
        }
        if( empty($aResult) )
        {//没有需要转的用户则直接返回TRUE
        	return $aBackData;
        }
        
        //03: 循环每个需要转钱到passport的用户，调用API把钱转到银行
        foreach( $aResult as $aUser )
        {
        	//调用转帐调度器实行转帐
        	$aTranfer['iUserId']         = intval( $aUser['userid'] );
            $aTranfer['iFromChannelId']  = SYS_CHANNELID;
            $aTranfer['iToChannelId']    = 0; //银行默认是0
            $aTranfer['fMoney']          = floatval( $aUser['availablebalance'] );
            $aTranfer['sMethod']         = 'SYS_SMALL';
            
            $oChannelApi = new channelapi( 0, 'channelTransitionDispatcher', TRUE );
            $oChannelApi->setResultType('serial');
            $oChannelApi->sendRequest( $aTranfer );
            $aResult = $oChannelApi->getDatas();
            if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
            {//转帐失败
                $aBackData[] = $aUser['userid'];
            }
        }
        return $aBackData;
    }
}
?>