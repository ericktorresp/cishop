<?php
/**
 * 用户资金数据模型
 *
 * 功能：
 * 		对用户资金变动进行操作
 *      CRUD
 * 		--update			                     修改用户的帐户信息[谨慎操作]
 * 
 * 		-- getFundByUser	                      根据用户ID和频道ID读取用户的帐户信息
 * 		-- isExists		                                 判断用户是否拥有在某个频道的帐户
 * 		-- switchLock		                      对用户帐户执行锁定/解锁 (非常重要)
 * 		-- saveUp			                      用户充值
 *      -- withdrawToUp             用户提现
 *      -- transferByUnite          帐间互转
 *      -- adminToUserSaveUp        管理员给用户充值
 *      -- adminPayMent             管理员给用户理赔
 *      -- getUserCredit            获取用户的信用资金
 *      -- admintoUserWithDraw      管理员给用户提现
 *      -- admintoUserPayWithDraw   管理员给用户理赔提现
 *      -- changeUserCredit         信用处理
 *      -- fundUnlockList           获取N秒前还在锁着的用户
 *      -- fundUnlock               更新N秒前还在锁着的用户
 *      -- getErrorFund             获取用户帐户出现差额的列表
 *      -- resetUserFundToZero      负余额清零操作
 *      -- getAdminOrderList        查看帐变，可以自定义查询条件[带分页效果]
 *      -- getProxyTeamFundList     查看游戏币明细，可以自定义查询条件, 不需要分页[获取总代团队资金]
 *      -- getProxyFundList         查看游戏币明细，可以自定义查询条件, 不需要分页[只获取总代自身资金]
 *      -- 
 * 
 * 
 * `userfund` 表结构:
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *     entry             =>  自动编号
 *     userid            =>  用户ID, 对应 users.userid
 *     channelid         =>  频道ID, 对应 channels.id
 *     channelbalance    =>  用户在频道中的总资金 (可用+冻结)
 *     availablebalance  =>  用户在频道中的 可用资金
 *     holdbalance       =>  用户在频道中的 冻结资金
 *     islocked          =>  资金被锁,  0=正常, 1=被锁
 *     lastupdatetime    =>  用户频道资金的最后更新时间
 *
 * 
 * @author     james,Saul,Tom
 * @version    1.1.1
 * @package    passport
 * @since      090430 - 090616
 */

class model_userfund extends basemodel 
{
	/**
	 * 构造函数
	 * 
	 * @access	public
	 * @return	void
	 */
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}



    /************************ James 部分 ***************************************/
	/**
	 * 修改用户的帐户信息[谨慎操作]
	 * 
	 * @access 	public	
	 * @author 	james	09/05/17
	 * @param 	array	$aFundInfo	//要修改的信息
	 * @param 	string	$sWhereSql	//要修改的筛选条件[不包含where关键字]，默认为全部修改
	 * @return 	mixed	//成功返回所影响所影响的行数，失败返回FALSE	
	 */
	public function update( $aFundInfo=array(), $sWhereSql='1' )
	{
		if( !is_array($aFundInfo) || empty($aFundInfo) )
		{
			return FALSE;
		}
		//数据修复
		if( isset($aFundInfo['channelid']) )
		{
			$aFundInfo['channelid'] = intval($aFundInfo['channelid']);
		}
		if( isset($aFundInfo['channelbalance']) )
		{
			$aFundInfo['channelbalance'] = round( floatval($aFundInfo['channelbalance']), 2);
		}
		if( isset($aFundInfo['availablebalance']) )
		{
			$aFundInfo['availablebalance'] = round( floatval($aFundInfo['availablebalance']), 2);
		}
		if( isset($aFundInfo['holdbalance']) )
		{
			$aFundInfo['holdbalance'] = round( floatval($aFundInfo['holdbalance']), 2);
		}
		if( isset($aFundInfo['islocked']) )
		{
			$aFundInfo['islocked'] = (bool)$aFundInfo['islocked'] ? 1 : 0;
		}
		if( !isset($aFundInfo['lastupdatetime']) || empty($aFundInfo['lastupdatetime']) )
		{
			$aFundInfo['lastupdatetime'] = date("Y-m-d H:i:s");
		}
		if( !isset($aFundInfo['lastactivetime']) || empty($aFundInfo['lastactivetime']) )
		{
			$aFundInfo['lastactivetime'] = date("Y-m-d H:i:s");
		}
		return $this->oDB->update( 'userfund', $aFundInfo, $sWhereSql );
	}

	
	
	/**
	 * 获取用户及时可用余额[缓存10秒]
	 *
	 * @param unknown_type $iUserId
	 * @return unknown
	 */
	public function getUserAvailableBalance( $iUserId )
	{
	    if( empty($iUserId) || !is_numeric($iUserId) || $iUserId < 0 )
        {
            return FALSE;
        }
        $iUserId = intval( $iUserId );
        $sSql    = " SELECT `availablebalance` FROM `userfund` WHERE `userid`='".$iUserId."' 
                     AND `channelid`='".SYS_CHANNELID."' ";
        $aResult = $this->oDB->getDataCached( $sSql, 10 );
        if( empty($aResult) )
        {
        	return FALSE;
        }
        return $aResult[0]['availablebalance'];
	}


	/**
	 * 根据用户ID和频道ID读取用户的帐户信息和个人信息
	 * 
	 * @access 	public
	 * @author 	james	09/05/29
	 * @param 	int		$iUserId	//用户ID
	 * @param 	int		$iChannelId	//频道ID
	 * @param 	string	$sFields//要查询的内容，表别名:user=>u,userfund=>uf
	 * @param 	boolean	$bCheckLock	//是否检测用户是否被锁，TRUE表示被锁的将不能读
	 * @return 	mixed	//成功返回帐户信息，失败返回FALSE
	 */
	public function & getFundByUser( $iUserId, $sFields='', $iChannelId=1, $bCheckLock=TRUE )
	{
		$aResult = array();
		if( empty($iUserId) || !is_numeric($iUserId) || $iUserId < 0 )
		{
			return $aResult;
		}
		$iUserId    = intval( $iUserId );
		$iChannelId = intval($iChannelId)>0 ? intval($iChannelId) : 0;
		if( empty($sFields) )
		{ // 默认要取的字段信息
			$sFields = " ut.`userid`,ut.`username`,uf.`availablebalance` ";
		}
		$sSql = "SELECT ".$sFields." FROM `userfund` AS uf
				 LEFT JOIN `usertree` AS ut ON uf.`userid`=ut.`userid` 
				 WHERE uf.`userid`='".$iUserId."' AND ut.`isdeleted`='0' AND uf.`channelid`='".$iChannelId."'  ";
		if( (bool)$bCheckLock )
		{
			$sSql .= " AND uf.`islocked`='0' ";
		}
		unset( $sFields );
		$aResult = $this->oDB->getOne( $sSql." LIMIT 1" );
		return $aResult;
	}



	/**
	 * 判断用户是否拥有在某个频道的帐户
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//用户ID
	 * @param 	int		$iChannelId	//频道ID
	 * @return 	boolean	//存在返回TRUE，不存在返回FALSE
	 */
	public function isExists( $iUserId, $iChannelId=0 )
	{
		if( empty($iUserId) )
		{
			return FALSE;
		}
		$iUserId = intval( $iUserId );
		if( (bool)$iChannelId )
		{
			$iChannelId = intval( $iChannelId );
		}
		else
		{
			$iChannelId = 0;
		}
		$sSql = "SELECT `entry` FROM `userfund` WHERE `userid`='".$iUserId."' AND `channelid`='".$iChannelId."' ";
		$this->oDB->query( $sSql );
		unset($sSql);
		if( $this->oDB->numRows() >0 )
		{//存在记录集
			return TRUE;
		}
		return FALSE;
	}



	/**
	 * 对用户帐户执行锁定/解锁 (非常重要)
	 * 
	 * @access  public
	 * @author  james      09/05/19
	 * @param   string     $sUserId      // 用户ID字符串，多个用户用,分隔
	 * @param   int        $iChannelId   // 要锁定或者解锁的频道
	 * @param   boolean	    $bIsLocked    // TRUE:锁定, FALSE:解锁
	 * @return  boolean	    // 成功返回影响行数, 失败返回 FALSE
	 */
	public function switchLock( $sUserId, $iChannelId=0, $bIsLocked=TRUE )
	{
		if( empty($sUserId) )
		{
			return FALSE;
		}
		$aUsers = explode(',',$sUserId);
		foreach( $aUsers as $k => &$v )
		{
			if( empty($v) || !is_numeric($v) )
			{
				unset($aUsers[$k]);
			}
			$v = intval($v);
		}
		if( empty($aUsers) )
		{
			return FALSE;
		}
		$iChannelId = intval($iChannelId)>0 ? intval($iChannelId) : 0;
		if( count($aUsers)>1 )
		{
			$sCondition = " `userid` IN ( ".implode(',', $aUsers)." ) ";
		}
		else
		{
			$sCondition = " `userid` = '".$aUsers[0]."' ";
		}
		$sCondition .= " AND `channelid` = '".$iChannelId."' ";
		$sNowTime = date("Y-m-d H:i:s");
		if( (bool)$bIsLocked )
		{
			$sSql = " UPDATE `userfund` SET `islocked`='1',`lastupdatetime`='".$sNowTime."',`lastactivetime`='".$sNowTime."' WHERE `islocked`=0 ";
		}
		else
		{
			$sSql = " UPDATE `userfund` SET `islocked`='0',`lastupdatetime`='".$sNowTime."',`lastactivetime`='".$sNowTime."' WHERE `islocked`=1 ";
		}
		$sSql .= " AND ".$sCondition;
		$this->oDB->query( $sSql );
		if( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		return $this->oDB->ar();
	}
	


	/**
	 * 获取N秒前还在锁着的用户
	 *
	 * @param int $times
	 * @return Array
	 * @author SAUL 090518
	 */
	public function fundUnlockList( $iTime =300 )
	{
		$iTime = intval( $iTime );
		return $this->oDB->getAll( "select `entry`,`username`,`lastupdatetime` from `userfund` left join"
		." `usertree` on (`usertree`.`userid`=`userfund`.`userid`) where `userfund`.`islocked`='1' and "
		."`userfund`.`lastupdatetime`<'".date("Y-m-d H:i:s",strtotime("-".$iTime." seconds"))."' ORDER BY `usertree`.`username`" );
	}



	/**
	 * 更新N秒前还在锁着的用户
	 *
	 * @param array $aUser
	 * @param int $times
	 * @return mixed
	 * @author SAUL 090518
	 */
	public function fundUnlock( $aUser, $iTime=300 )
	{
		if( empty($aUser) || !is_array($aUser) )
		{
			return -1;
		}
		$iTime = intval($iTime);
		foreach( $aUser as $key=>$value )
		{
			if( !is_numeric($value) )
			{
				unset($aUser[$key]);
			}
		}
		if( count($aUser) == 0 )
		{
			return -1;
		}
		$sNowTime = date("Y-m-d H:i:s");
		return $this->oDB->query( "UPDATE `userfund` SET `islocked`='0',`lastupdatetime`='".$sNowTime."',`lastactivetime`='".$sNowTime."' WHERE `entry` IN "
		. "(" . join(",", $aUser) . ") AND `lastupdatetime`<'"
		. date("Y-m-d H:i:s", strtotime("-".$iTime." seconds"))."'" );
	}



	/**
	 * 获取用户帐户出现差额的列表
	 *
	 * @return array
	 */
	function & getErrorFund()
	{
		$aArr = $this->oDB->getAll( "SELECT `userfund`.`availablebalance`,c.`username` AS `selfname` "
    		. ",`userfund`.cashbalance ,userfund.channelbalance ,userfund.holdbalance, "
            . " `userfund`.userid,d.`username` AS `topname` FROM `userfund` "
    		. " LEFT JOIN `usertree` AS c ON (c.`userid`=`userfund`.`userid`) "
    		. " LEFT JOIN `usertree` AS d ON (c.`lvtopid`=d.`userid`) "
    		. " WHERE (`userfund`.`availablebalance`+userfund.holdbalance != userfund.channelbalance ) ");
		foreach($aArr as $iKey=> $arr)
		{
			if(intval(number_format($arr["availablebalance"],2)*100) + intval(number_format($arr["holdbalance"],2)*100) == intval(number_format($arr["channelbalance"],2)*100))
			{
				unset($aArr[$iKey]);
			}
		}
		return $aArr;
	}





	/**************************** Tom 部分 *****************************/
	/**
	 * 查看帐变，可以自定义查询条件[带分页效果]
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
		              " LEFT JOIN `usertree` AS ut ON ut.`userid`=o.`fromuserid` ";
		$sFields    = "ut.`userid`,ut.`username`,o.`entry`,o.`title`,o.`amount`,o.`preavailable`,o.`availablebalance`, ".
				   " o.`times`,o.`transferstatus`,ot.`cntitle`,ot.`entitle`, o.`adminname`, `operations` AS signamount,o.`clientip` ";
		return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage,' ORDER BY o.`times` DESC ');
	}

	
	/**
	 * 查看游戏币明细，可以自定义查询条件, 不需要分页
	 *   - 获取总代团队资金
	 * @access 	public
	 * @author 	Tom     09/05/29
	 * @param 	int     $iUserId      // 用户ID, 对应 users.userid
	 * @param 	string	$sWhere       // 附加的查询条件
	 * @param 	string	$sHaving      // 附加的查询条件
	 * @return array
	 */
	public function & getProxyTeamFundList( $iUserId = 0, $sWhere = "1", $sHaving = "" )
	{
	    $sTableName = "`usertree` ut force index( idx_usertree ) LEFT JOIN `userfund` uf ".
		              " ON ( uf.`userid` = ut.`userid` AND ut.`isdeleted`=0 AND ut.`usertype` < 2 and uf.`channelid`='".SYS_CHANNELID."' ) ". 
		              " LEFT JOIN `usertree` u on ut.`lvtopid` = u.`userid` ";
	    $mFields    = "uf.`userid`,u.`username` as topuname,u.`usertype` as toputype,u.`parentid` as topuparentid, ".
	               " ut.`username`,ut.`usertype`,ut.`parentid`, ".
	               " sum( uf.availablebalance ) as TeamAvailBalance, ".
	               " sum( uf.holdbalance ) as TeamHoldBalance, ".
	    		   " sum( uf.channelbalance ) as TeamChannelBalance, ".
		           " ut.lvtopid";
	    $sSql = '';
	    if( $iUserId != 0 )
	    { # 需求1: 指定 $iUserId 则只统计指定用户ID的所有团队资金情况
	        $sHaving = $sHaving=='' ? '' : 'HAVING '.daddslashes($sHaving);
	        $sWhere  = $sWhere.' AND ( uf.`userid` = '.intval($iUserId).' or FIND_IN_SET( '.intval($iUserId).', ut.parenttree ) ) ';
	        $sSql    = "SELECT $mFields FROM $sTableName WHERE $sWhere $sHaving";
	        $aResult = $this->oDB->getAll($sSql);
	        if( count($aResult)==1 && empty($aResult[0]['userid']) )
	        {
	            $aResult = array();
	        }
	        return $aResult;
	    }
	    else 
	    { # 需求2: 若未指定 $iUserId, 则统计所有总代的资金情况
	        $sHaving = $sHaving=='' ? '' : 'HAVING '.daddslashes($sHaving);
	        $sSql    = "SELECT $mFields FROM $sTableName WHERE $sWhere AND ".
	                  " ut.`lvtopid` != 0 AND ( FIND_IN_SET( ut.`parentid`, ut.`parenttree` ) OR ut.`parentid` = 0 ) ".
                      " GROUP BY ut.`lvtopid` $sHaving  ORDER BY ut.`username`";
	        $aResult = $this->oDB->getAll($sSql);
	        return $aResult;
	    }
	}



	/**
	 * 查看游戏币明细，可以自定义查询条件, 不需要分页
	 *   - 只获取总代自身资金
	 * @access 	public
	 * @param 	int     $iUserId      // 用户ID, 对应 users.userid
	 * @param 	string	$sWhere       // 附加的查询条件
	 * @param 	string	$sHaving      // 附加的查询条件
	 * @return array
	 * @author Tom 090609 13:06
	 */
	public function & getProxyFundList( $iUserId = 0, $sWhere = "1" )
	{
	    $sTableName = "`usertree` ut force index( idx_usertree ) LEFT JOIN `userfund` uf ".
		              " ON ( uf.`userid` = ut.`userid` AND ut.`isdeleted`=0 AND ut.`usertype` < 2 and uf.`channelid`='".SYS_CHANNELID."' ) ";
	    $mFields = "uf.`userid`,ut.`username`,ut.`usertype`,ut.`parentid`, ".
	               " uf.availablebalance AS TeamAvailBalance, ".
	               " uf.holdbalance AS TeamHoldBalance, ".
	    		   " uf.channelbalance AS TeamChannelBalance ";
	    $sSql = '';
	    if( $iUserId != 0 )
	    { # 需求1: 指定 $iUserId 则只统计指定用户ID的所有团队资金情况
	        $sWhere = $sWhere.' AND  uf.`userid` = '.intval($iUserId).' ';
	        $sSql   = "SELECT $mFields FROM $sTableName WHERE $sWhere";
	        $aResult = $this->oDB->getAll($sSql);
	        if( count($aResult)==1 && empty($aResult[0]['userid']) )
	        {
	            $aResult = array();
	        }
	        return $aResult;
	    }
	    else 
	    { # 需求2: 若未指定 $iUserId, 则统计所有总代的资金情况
	        $sSql   = "SELECT $mFields FROM $sTableName WHERE $sWhere AND ut.`parentid` = 0  ORDER BY ut.`username`";
	        $aResult = $this->oDB->getAll($sSql);
	        return $aResult;
	    }
	}




	/**
	 * 频道转账
	 * 从 passport/_api/channelTransition.php   apiFundTransition() 函数发起调用
	 * @author 	tom	090922 14:32
	 * @param  string  $aDatas[sMethod]         // 转账方法
	 * @param  string  $aDatas[sType]           // 转账类型 reduce | plus
	 * @param 	int		$aDatas[iUserId]         // 用户ID
	 * @param 	int		$aDatas[iFromChannelId]  // 资金转出频道 ( 扣款 )
	 * @param 	int     $aDatas[iToChannelId]    // 资金转入频道 ( 加钱 )
	 * @param 	float	$aDatas[fMoney]	         // 转账金额
	 * @param  string  $aDatas[sUnique]         // 唯一值
	 * @param  string  $aDatas[iAdminId]        // 管理员ID
	 * @return	mix  全等于TRUE 为成功; 其他均为失败信息
	 */
	public function apiFundTransition( $aDatas = array() )
	{
	    // STEP: 01 基础数据效验 ---------------------------------------------
		if( !isset($aDatas['sMethod']) 
            || !in_array( $aDatas['sMethod'], array('SYS_SMALL','SYS_ZERO','USER_TRAN') )
            || empty($aDatas['iUserId']) || !is_numeric($aDatas['iUserId']) 
		    || empty($aDatas['sMethod']) || empty($aDatas['sType'])
		    || empty($aDatas['fMoney']) || empty($aDatas['sType'])
		    || empty($aDatas['sUnique']) || strlen($aDatas['sUnique']) != 32
		    || !is_numeric($aDatas['iFromChannelId']) 
		    || !is_numeric($aDatas['iToChannelId']) ) 
		{
			return 'model.userfund.apiFundTransition() : error data init';
		}

	    if( !in_array( $aDatas['sType'], array('reduce','plus')) )
        {
            return 'init Post Data Failed #1002';
        }

        if( $aDatas['sType']=='reduce' && SYS_CHANNELID != $aDatas['iFromChannelId'] )
        {
            return 'init Post Data Failed #1003';
        }

        if( $aDatas['sType']=='plus' && SYS_CHANNELID != $aDatas['iToChannelId'] )
        {
            return 'init Post Data Failed #1004';
        }
        
        if(  $aDatas['sType']=='plus' && !array_key_exists('iOrderEntry', $aDatas) )
        { // 扣钱方账变ID
            return 'init Post Data Failed #1005';
        }


        // STEP: 02 频道数据效验 ---------------------------------------------------------
        $oChannel = new model_channels();
        $aAvailChannels = $oChannel->getAvailableChannel( TRUE, 
        		" AND `id` IN ( '".$aDatas['iFromChannelId']."','".$aDatas['iToChannelId']."' ) " );
        
        // TODO _a高频、低频并行前期临时程序
        //----- 开始 -------------------------------------
        if( !empty($aDatas['bIsTemp']) )
        {
            if( count($aAvailChannels)!=1 )
            {
                return 'Channel is not available. #1006_1';
            }
            $aAvailChannels['99'] = '高频';
        }
        else 
        {
            // 正常流程开始
            if( count($aAvailChannels)!=2 )
            {
                return 'Channel is not available. #1006_2';
            }
            // 正常流程结束
        }
        //----- 结束 -------------------------------------


		// STEP: 03 数据整理 --------------------------------------------------------------
		// $aDatas['sMethod']     = $aDatas['sMethod'];          // 方法  [this line just for read]
        $aDatas['iUserId']        = intval( $aDatas['iUserId'] );        // 用户ID
        $aDatas['iFromChannelId'] = intval( $aDatas['iFromChannelId'] ); // 扣款方频道ID
        $aDatas['iToChannelId']   = intval( $aDatas['iToChannelId'] );   // 收款方频道ID
        $aDatas['fMoney']         = floatval( $aDatas['fMoney'] );       // 资金
        $iTargetChannelid         = ($aDatas['sType']=='reduce') ? $aDatas['iToChannelId'] : $aDatas['iFromChannelId'];
        $iAdminId                 = isset($aDatas['iAdminId']) ? intval($aDatas['iAdminId']) : 0;
        $sAdminName               = isset($aDatas['sAdminName']) ? daddslashes($aDatas['sAdminName']) : '';


        // STEP: 04 转账业务流程 ----------------------------------------------------------
        // 4.1 试图锁定用户资金 userFund (行锁)
		if( FALSE == $this->oDB->doTransaction()){ return 'model.userfund.apiFundTransition() : #5010 Transaction Failed '; }
		if( intval($this->switchLock($aDatas['iUserId'], SYS_CHANNELID ,TRUE)) != 1 )
		{
			if( FALSE == $this->oDB->doRollback()){ return 'model.userfund.apiFundTransition() : #5011 Transaction Failed'; }
			return 'model.userfund.apiFundTransition() : #5012 Lock UserFund Failed';
		}
		if( FALSE == $this->oDB->doCommit()){ return 'model.userfund.apiFundTransition() : #5013 Transaction Failed'; }

		// 4.2 获取涉及用户的帐户信息 (usertree)
		$sSql = "SELECT `username` FROM `usertree` WHERE `userid`='".$aDatas['iUserId']."' AND `isdeleted`='0' LIMIT 1";
		$aFromData = $this->oDB->getOne($sSql);
		if( empty($aFromData) )
		{
			$this->switchLock($aDatas['iUserId'], SYS_CHANNELID, FALSE); // 解锁资金表
			return 'model.userfund.apiFundTransition() : #5014 User Data Init Failed';
		}
		unset($aFromData);

		// STEP: 05 改写资金, 增加账变
		$oOrder = new model_orders(); // 开始转账
		$iOrderType   = 0;   // 变量声明
		$sDescription = '';  // 变量声明
		switch ( $aDatas['sMethod'] )
		{ // 'SYS_SMALL','SYS_ZERO','USER_TRAN' for 090911
		    case 'SYS_SMALL' : 
	        {
                $iOrderType   = ORDER_TYPE_PDXEZC; // 账变类型: 频道小额转出
                $sDescription = $aAvailChannels[ $aDatas['iFromChannelId'] ] 
                        ." 转出至: " . $aAvailChannels[ $aDatas['iToChannelId'] ];
                BREAK;
	        }
		    case 'SYS_ZERO' : 
            {
                if( $aDatas['sType']=='reduce' )
                {
                    $iOrderType   = ORDER_TYPE_TSJEQL; // 账变类型: 特殊金额清理
                    $sDescription = $aAvailChannels[ $aDatas['iFromChannelId'] ] 
                            ." 转出至: " . $aAvailChannels[ $aDatas['iToChannelId'] ];
                }
                else
                {
                    $iOrderType   = ORDER_TYPE_TSJEZL; // 账变类型: 特殊金额整理
                    $sDescription = $aAvailChannels[ $aDatas['iFromChannelId'] ] 
                            . " 转入: " . $aAvailChannels[ $aDatas['iToChannelId'] ];
                }
                BREAK;
            }
            case 'USER_TRAN' : 
            {
                if( $aDatas['sType']=='reduce' )
                {
                    $iOrderType   = ORDER_TYPE_PDZC; // 账变类型: 频道转出
                    $sDescription = $aAvailChannels[ $aDatas['iFromChannelId'] ] 
                            ." 转出至: " . $aAvailChannels[ $aDatas['iToChannelId'] ];
                }
                else
                {
                    $iOrderType   = ORDER_TYPE_ZRPD; // 账变类型: 转入频道
                    $sDescription = $aAvailChannels[ $aDatas['iFromChannelId'] ] 
                            . " 转入: " . $aAvailChannels[ $aDatas['iToChannelId'] ];
                }
                BREAK;
            }
		}

        $sActionTime    = date("Y-m-d H:i:s");	// 动作时间
        $aOrderData = array();
        $aOrderData['iFromUserId']        = $aDatas['iUserId'];
        $aOrderData['iOrderType']         = $iOrderType;
        $aOrderData['fMoney']             = $aDatas['fMoney'];
        $aOrderData['sActionTime']        = $sActionTime;
        $aOrderData['sDescription']       = $sDescription;
        $aOrderData['iChannelId']         = SYS_CHANNELID;
        $aOrderData['sUniqueKey']         = $aDatas['sUnique'];
        $aOrderData['iTransferUserid']    = $aDatas['iUserId'];
        $aOrderData['iTransferChannelid'] = $iTargetChannelid;
        // 如果是转账的加钱行为, 写入扣钱方账变ID, 转账状态直接写2 表示:成功
        $aOrderData['iTransferOrderid']   = $aDatas['sType'] == 'plus' ? intval($aDatas['iOrderEntry']) : 0;
        $aOrderData['iTransferStatus']    = $aDatas['sType'] == 'plus' ? 2 : 1;
        $aOrderData['iAdminId']           = $iAdminId;
        $aOrderData['sAdminName']         = $sAdminName;
        $aOrderData['bIgnoreMinus']       = in_array( $aDatas['sMethod'], array('USER_TRAN','SYS_ZERO','SYS_SMALL') ) ? TRUE : FALSE;
        
        // 由用户自主发起的频道转账, 设置 '帐变忽略负数(bIgnoreMinus)' 的参数为关闭
        // 即: 用户转账时, 转出平台扣钱帐变的产生禁止负数, 若为负数则禁止转账并返回帐变相关的错误信息
        if( 'USER_TRAN'==$aDatas['sMethod'] && $aDatas['sType']=='reduce' )
        {
        	$aOrderData['bIgnoreMinus'] = FALSE;
        }

        // 开始事务
        if( FALSE == $this->oDB->doTransaction()){ return 'model.userfund.apiFundTransition() : #5015 Transaction Failed'; }
        $result = $oOrder->addOrders( $aOrderData );
        unset($aOrderData);
		if( TRUE !== $result )
		{
		    if( FALSE == $this->oDB->doRollback()){ return 'model.userfund.apiFundTransition() : #5016 Transaction Failed'; }
			$this->switchLock($aDatas['iUserId'], SYS_CHANNELID, FALSE);	//解锁资金表
			return 'model.userfund.apiFundTransition() : #5017 Transaction Failed. addOrders().ErrCode='.$result;
		}

		// 提交事务
	    if( FALSE == $this->oDB->doCommit()){ return 'model.userfund.apiFundTransition() : #5018 Transaction Failed'; }
	    // 转账账变写入成功, 将用户资金账户解锁  
	    // 此处忽略资金账户解锁失败. 并不影响整个转账流程. 应给用户看到转账成功的消息
		$this->switchLock($aDatas['iUserId'], SYS_CHANNELID, FALSE);
		return TRUE;
	}	

	/**
	 * 根据转账信息, 获取账变ID号
	 * 从 _api/channelTransition.php 发起调用
	 * @author 	tom	090810
	 * @param string  $sUnique  唯一值 
	 * @param int     $iUserId  用户ID
	 * @param float   $fMoney
	 * @return	mix  整型自然数, 或全等于的 FALSE
	 */
	public function getOrderEntryByTranferData( $sUnique='', $iUserId=0, $fMoney=0.00 )
	{
	    $sUnique      = daddslashes($sUnique);
	    $iUserId      = intval($iUserId);
	    $fMoney       = intval($fMoney);
	    $aOrderEntry = $this->oDB->getOne("SELECT `entry` FROM `orders` WHERE `uniquekey`='$sUnique' ".
	    		" AND `transferuserid`='$iUserId' ORDER BY `entry` DESC LIMIT 1");
	    if( $this->oDB->ar() != 1 )
	    {
	        return FALSE;
	    }
	    else 
	    {
	        return $aOrderEntry['entry'];
	    }
	}
}
?>