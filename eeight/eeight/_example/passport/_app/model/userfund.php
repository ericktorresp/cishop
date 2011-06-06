<?php
/**
 * 用户资金数据模型
 *
 * 功能：
 *      对用户资金变动进行操作
 *      CRUD
 *      --update                                 修改用户的帐户信息[谨慎操作]
 * 
 *      -- getFundByUser                          根据用户ID和频道ID读取用户的帐户信息
 *      -- isExists                                      判断用户是否拥有在某个频道的帐户
 *      -- switchLock                             对用户帐户执行锁定/解锁 (非常重要)
 *      -- saveUp                                 用户充值
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
     * @access  public
     * @return  void
     */
    function __construct( $aDBO = array() )
    {
        parent::__construct( $aDBO );
    }



    /************************ James 部分 ***************************************/
    /**
     * 修改用户的帐户信息[谨慎操作]
     * 
     * @access  public  
     * @author  james   09/05/17
     * @param   array   $aFundInfo  //要修改的信息
     * @param   string  $sWhereSql  //要修改的筛选条件[不包含where关键字]，默认为全部修改
     * @return  mixed   //成功返回所影响所影响的行数，失败返回FALSE	
     */
    public function update( $aFundInfo = array(), $sWhereSql = '1' )
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
            $aFundInfo['channelbalance'] = round( floatval($aFundInfo['channelbalance']), 2 );
        }
        if( isset($aFundInfo['availablebalance']) )
        {
            $aFundInfo['availablebalance'] = round( floatval($aFundInfo['availablebalance']), 2 );
        }
        if( isset($aFundInfo['holdbalance']) )
        {
            $aFundInfo['holdbalance'] = round( floatval($aFundInfo['holdbalance']), 2 );
        }
        if( isset($aFundInfo['islocked']) )
        {
            $aFundInfo['islocked'] = (bool)$aFundInfo['islocked'] ? 1 : 0;
        }
        if( !isset($aFundInfo['lastupdatetime']) || empty($aFundInfo['lastupdatetime']) )
        {
            $aFundInfo['lastupdatetime'] = date("Y-m-d H:i:s", time());
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
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   int     $iChannelId //频道ID
     * @param   string  $sFields//要查询的内容，表别名:user=>u,userfund=>uf
     * @param   boolean $bCheckLock //是否检测用户是否被锁，TRUE表示被锁的将不能读
     * @return  mixed   //成功返回帐户信息，失败返回FALSE
     */
    public function & getFundByUser( $iUserId, $sFields = '', $iChannelId = 0, $bCheckLock = TRUE, $bCredit = FALSE )
    {
        $aResult = array();
        if( empty($iUserId) )
        {
            return $aResult;
        }
        $iUserId    = intval( $iUserId );
        $iChannelId = intval($iChannelId) > 0 ? intval($iChannelId) : 0;
        if( empty($sFields) )
        { // 默认要取的字段信息
            $sFields = " u.`userid`,u.`username`,uf.`availablebalance` ";
        }
        $sSql = "SELECT ".$sFields." FROM `userfund` AS uf 
                 LEFT JOIN `users` AS u ON uf.`userid`=u.`userid`
                 LEFT JOIN `usertree` AS ut ON (uf.`userid`=ut.`userid` AND ut.`isdeleted`='0') 
                 WHERE uf.`userid`='".$iUserId."' AND uf.`channelid`='".$iChannelId."'  ";
        if( (bool)$bCheckLock )
        {
            $sSql .= " AND uf.`islocked`='0' ";
        }
        unset( $sFields );
        $aResult = $this->oDB->getOne( $sSql );
        if( $bCredit )
        {
            //判断用户是否有信用
            $sSql = "SELECT `proxyvalue` FROM `topproxyset` WHERE `proxykey`='credit' AND `userid`='".$iUserId."'";
            $aTempRresult = $this->oDB->getOne($sSql);
            if( empty($aTempRresult) || $aTempRresult['proxyvalue'] == 0 )
            {//没信用则直接返回自身可用余额
                return $aResult;
            }
            else 
            {//有信用则判断团队金额
                //获取团队余额
                $oUser                       = new model_user();
                $iTeamAvailable              = $oUser->getTeamBank($iUserId);
                $aResult['availablebalance'] = min( $aResult['availablebalance'], 
                                                    ($iTeamAvailable - floatval($aTempRresult['proxyvalue'])) );
                return $aResult;
            }
        }
        else
        {
            return $aResult;
        }
    }



    /**
     * 判断用户是否拥有在某个频道的帐户
     * 
     * @access  public
     * @author  jamesS
     * @param   int     $iUserId    //用户ID
     * @param   int     $iChannelId //频道ID
     * @return  boolean //存在返回TRUE，不存在返回FALSE
     */
    public function isExists( $iUserId, $iChannelId = 0 )
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
        if( $this->oDB->numRows() > 0 )
        {//存在记录集
            return TRUE;
        }
        return FALSE;
    }



    /**
     * 对用户帐户执行锁定/解锁 (非常重要)
     * 
     * @access  public
     * @author  james
     * @param   string     $sUserId      // 用户ID字符串，多个用户用,分隔
     * @param   int        $iChannelId   // 要锁定或者解锁的频道
     * @param   boolean     $bIsLocked    // TRUE:锁定, FALSE:解锁
     * @return  boolean     // 成功返回影响行数, 失败返回 FALSE
     */
    public function switchLock( $sUserId, $iChannelId = 0, $bIsLocked = TRUE )
    {
        if( empty($sUserId) )
        {
            return FALSE;
        }
        $aUsers = explode( ',', $sUserId );
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
        $iChannelId = intval($iChannelId) > 0 ? intval($iChannelId) : 0;
        if( count($aUsers) > 1 )
        {
            $sCondition = " `userid` IN ( ".implode(',', $aUsers)." ) ";
        }
        else
        {
            $sCondition = " `userid` = '".$aUsers[0]."' ";
        }
        $sCondition .= " AND `channelid` = '".$iChannelId."' ";
        if( (bool)$bIsLocked )
        {
            $sSql = " UPDATE `userfund` SET `islocked`='1', `lastupdatetime`='".date('Y-m-d H:i:s')."' WHERE `islocked`=0 ";
        }
        else
        {
            $sSql = " UPDATE `userfund` SET `islocked`='0', `lastupdatetime`='".date('Y-m-d H:i:s')."' WHERE `islocked`=1 ";
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
     * 用户充值
     * 
     * @access  public
     * @author  james   09/05/17
     * @param   int     $iParentId  //充值用户ID
     * @param   int     $iUserId    //被充值用户ID
     * @param   float   $fMoney     //充值金额
     * @param   int     $iAgentId   //总代管理员ID，如果不为总代管理员操作则为0
     * @param   int     $iChannelId //频道ID
     * @return  mixed   //   0: 其他失败
     *                      -1: 锁定资金帐户失败(即可能资金表被其他占用)
     *                      -2：获取用户信息失败
     *                      -3: 完成后对资金解锁失败
     *                      -1001: 用户ID错误
     *                      ......参考orders
     *                      TRUE:成功
     */
    function saveUp( $iParentId, $iUserId, $fMoney, $iAgentId = 0, $iChannelId = 0 )
    {
        if( empty($iParentId) || empty($iUserId) || empty($fMoney) || !is_numeric($fMoney) )
        {
            return FALSE;
        }
        $iAgentId   = intval($iAgentId) > 0 ? intval($iAgentId) : 0;
        $iChannelId = intval($iChannelId) > 0 ? intval($iChannelId) : 0;
        
        //锁定两个帐户
        $this->oDB->doTransaction();    //开始事务
        if( intval($this->switchLock( $iParentId.','.$iUserId, $iChannelId, TRUE)) < 2 )
        {
            $this->oDB->doRollback(); //回滚事务
            return -1;
        }
        $this->oDB->commit();   //提交事务
        $fMoney = round( floatval($fMoney), 2 );
        //获取被充值人信息
        $sSql = "SELECT `userid`,`username`,`parentid` FROM `usertree` 
                 WHERE `userid`='".$iUserId."' AND `isdeleted`='0' ";
        $aUserData = $this->oDB->getOne( $sSql );
        if( empty($aUserData) )
        {
            $this->switchLock( $iParentId.','.$iUserId, $iChannelId, FALSE ); //解锁资金表
            return -2;
        }
        $oOrder = new model_orders();
        ////扣钱原子操作参数设置
        $iOrderType_r   = ORDER_TYPE_CZKF;  //充值扣费
        $sDescription_r = "为用户：".$aUserData['username']."充值";
        //加钱原子操作参数设置
        if( $aUserData['parentid'] == $iParentId )
        {//是直接上级
            $iOrderType_a   = ORDER_TYPE_SJCZ;  //上级充值
            
        }
        else
        {
            $iOrderType_a   = ORDER_TYPE_KJCZ;  //跨级充值
        }
        $sActionTime = date("Y-m-d H:i:s");	//动作时间
        /**************************开始进行充值 ********************/
        //开始事务
        $this->oDB->doTransaction();
        //对充值用户进行帐变，扣钱原子操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $iParentId;
        $aOrders['iToUserId']    = $iUserId;
        $aOrders['iOrderType']   = $iOrderType_r;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription_r;
        $aOrders['iAgentId']     = $iAgentId;
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iParentId.','.$iUserId, $iChannelId, FALSE );	//解锁资金表
            return $result;
        }
        
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iToUserId']    = $iParentId;
        $aOrders['iOrderType']   = $iOrderType_a;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['iAgentId']     = $iAgentId;
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iParentId.','.$iUserId, $iChannelId, FALSE );	//解锁资金表
            return $result;
        }
        //提交事务
        $this->oDB->doCommit();
        /**************************开始进行充值 ********************/
        //为两个资金帐户解锁
        if( FALSE == $this->switchLock( $iParentId.','.$iUserId, $iChannelId, FALSE) )
        {
            return -3;
        }
        return TRUE;
    }



    /**
     * 用户提现（下级向上级提现）
     * 
     * @access  public
     * @author  james
     * @param   int     $iParentId  //提现用户ID
     * @param   int     $iUserId    //提现上级用户ID
     * @param   float   $fMoney //提现金额
     * @param   int     $iType  //提现类型，1：上级操作，2：本人操作
     * @param   int     $iAgentId   //总代管理员ID，如果不为总代管理员操作则为0
     * @param   int     $iChannelId //频道ID
     * @return  mixed   //   0: 其他失败
     *                      -1: 锁定资金帐户失败(即可能资金表被其他占用)
     *                      -2：获取用户信息失败
     *                      -3: 完成后对资金解锁失败
     *                      -1001: 用户ID错误
     *                      ......参考orders
     *                      TRUE:成功
     */
    public function withdrawToUp( $iUserId, $iParentId, $fMoney, $iType = 1, $iAgentId = 0, $iChannelId = 0 )
    {
        if( empty($iParentId) || empty($iUserId) || empty($fMoney) || !is_numeric($fMoney) || empty($iType) )
        {
            return FALSE;
        }
        $iAgentId   = intval($iAgentId) > 0 ? intval($iAgentId) : 0;
        $iChannelId = intval($iChannelId) > 0 ? intval($iChannelId) : 0;
        $iType = intval($iType) == 1 ? 1 : 2;
        //锁定两个帐户
        //开始事务
        $this->oDB->doTransaction();
        if( intval($this->switchLock($iParentId.','.$iUserId, $iChannelId, TRUE)) < 2 )
        {
            $this->oDB->doRollback(); //回滚事务
            return -1;
        }
        $this->oDB->doCommit();//提交事务，锁定两帐户
        $fMoney = round( floatval($fMoney), 2 );
        //获取提现用户的帐户信息
        $sSql = "SELECT `userid`,`username` FROM `usertree` WHERE `userid`='".$iUserId."' AND `isdeleted`='0' ";
        $aUserData = $this->oDB->getOne($sSql);
        if( empty($aUserData) )
        {
            $this->switchLock($iParentId.','.$iUserId, $iChannelId, FALSE);	//解锁资金表
            return -2;
        }
        $oOrder = new model_orders();
        //原子操作参数设置
        if( $iType == 1 )
        {//上级操作
            $iOrderType_r   = ORDER_TYPE_BRTX;
            $sDescription_r = "上级操作提现";
            $iOrderType_a   = ORDER_TYPE_XJTX;
            $sDescription_a = "为下级用户: ".$aUserData['username']." 提现";
        }
        else 
        {//本人发起提现
            $iOrderType_r   = ORDER_TYPE_BRFQTX;
            $sDescription_r = "本人操作提现";
            $iOrderType_a   = ORDER_TYPE_XJFQTX;
            $sDescription_a = "下级用户: ".$aUserData['username']." 发起提现";
        }
        $sActionTime = date("Y-m-d H:i:s");	//动作时间
        /**************************开始进行提现 ********************/
        //开始事务
        $this->oDB->doTransaction();
        //对提现用户进行帐变，扣钱原子操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iToUserId']    = $iParentId;
        $aOrders['iOrderType']   = $iOrderType_r;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription_r;
        $aOrders['iAgentId']     = $iAgentId;
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            $this->oDB->doRollback();
            $this->switchLock($iParentId.','.$iUserId, $iChannelId, FALSE);	//解锁资金表
            return $result;
        }
        //对被提现用户（提现用户上级）进行帐变，加钱原子操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $iParentId;
        $aOrders['iToUserId']    = $iUserId;
        $aOrders['iOrderType']   = $iOrderType_a;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription_a;
        $aOrders['iAgentId']     = $iAgentId;
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iParentId.','.$iUserId, $iChannelId, FALSE );	//解锁资金表
            return $result;
        }
        //提交事务
        $this->oDB->doCommit();
        /**************************开始进行充值 ********************/
        //为两个资金帐户解锁
        if( FALSE == $this->switchLock($iParentId.','.$iUserId, $iChannelId, FALSE) )
        {
            return -3;
        }
        return TRUE;
    }



    /**
     * 帐间互转
     * 
     * @access  public
     * @author  james
     * @param   int     $iFromUser  //转出帐号
     * @param   int     $iToUser    //转入帐号
     * @param   float   $fMoney     //转出金额
     * @param   int     $iAgentId   //总代管理员ID，如果不为总代管理员操作则为0
     * @param   int     $iChannelId //频道ID
     * @return  boolean     FALSE: 失败
     *                          -1: 锁定资金帐户失败(即可能资金表被其他占用)
     *                          -2：获取用户信息失败
     *                          -3: 完成后对资金解锁失败
     *                          -1001: 用户ID错误
     *                          ......参考orders
     *                          TRUE:成功
     */
    public function transferByUnite( $iFromUser, $iToUser, $fMoney, $iAgentId, $iChannelId = 0 )
    {
        if( empty($iFromUser) || empty($iToUser) || empty($fMoney)
            || !is_numeric($iFromUser) || !is_numeric($iToUser) || !is_numeric($fMoney) )
        {
            return FALSE;
        }
        $iFromUser  = intval($iFromUser);
        $iToUser    = intval($iToUser);
        $fMoney     = round( floatval($fMoney), 2 );
        $iAgentId   = intval($iAgentId) > 0 ? intval($iAgentId) : 0;
        $iChannelId = intval($iChannelId) > 0 ? intval($iChannelId) : 0;
        //锁定两个帐户
        //开始事务
        $this->oDB->doTransaction();
        if( intval($this->switchLock($iFromUser.','.$iToUser, $iChannelId, TRUE)) < 2 )
        {
            $this->oDB->doRollback(); //回滚事务
            return -1;
        }
        $this->oDB->doCommit();//提交事务，锁定两帐户
        //获取转出用户的帐户信息
        $sSql = "SELECT `username` FROM `usertree` WHERE `userid`='".$iFromUser."' AND `isdeleted`='0' ";
        $aFromData = $this->oDB->getOne($sSql);
        if( empty($aFromData) )
        {
            $this->switchLock( $iFromUser.','.$iToUser, $iChannelId, FALSE ); //解锁资金表
            return -2;
        }
        //获取转入者帐户信息
        $sSql    = " SELECT `username` FROM `usertree` WHERE `userid`='".$iToUser."' AND `isdeleted`='0' ";
        $aToData = $this->oDB->getOne($sSql);
        if( empty($aToData) )
        {
            $this->switchLock( $iFromUser.','.$iToUser, $iChannelId, FALSE ); //解锁资金表
            return -2;
        }
        $oOrder         = new model_orders();
        //原子操作参数设置
        $iOrderType_r   = ORDER_TYPE_ZZZC;  //转帐转出
        $sDescription_r = "转帐转出到".$aToData['username'];
        $iOrderType_a   = ORDER_TYPE_ZZZR;  //转帐转入
        $sDescription_a = $aFromData['username']."转帐转入";
        $sActionTime    = date("Y-m-d H:i:s");  //动作时间
        /**************************开始转帐 ********************/
        //开始事务
        $this->oDB->doTransaction();
        //写帐变，扣钱原子操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $iFromUser;
        $aOrders['iToUserId']    = $iToUser;
        $aOrders['iOrderType']   = $iOrderType_r;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription_r;
        $aOrders['iAgentId']     = $iAgentId;
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iFromUser.','.$iToUser, $iChannelId, FALSE );	//解锁资金表
            return $result;
        }
        //写帐变，加钱原子操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $iToUser;
        $aOrders['iToUserId']    = $iFromUser;
        $aOrders['iOrderType']   = $iOrderType_a;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription_a;
        $aOrders['iAgentId']     = $iAgentId;
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iFromUser.','.$iToUser, $iChannelId, FALSE );	//解锁资金表
            return $result;
        }
        //提交事务
        $this->oDB->doCommit();
        /**************************结束转帐 ********************/
        //为两个资金帐户解锁
        if( FALSE == $this->switchLock($iFromUser.','.$iToUser, $iChannelId, FALSE) )
        {
            return -3;
        }
        return TRUE;
    }


    /**
     * 不活跃用户清理时，把钱转到上级
     *
     * @access public
     * @author james
     * @param  int       $iUserId
     * @param  int       $iParentId
     * @param  float     $fMoney
     * @return mixed
     */
    public function clearUpToParent( $iUserId, $iParentId, $fMoney )
    {
        if( empty($iParentId) || empty($iUserId) || empty($fMoney) || !is_numeric($fMoney) )
        {
            return FALSE;
        }
        $iChannelId = SYS_CHANNELID;
        //锁定两个帐户
        //开始事务
        if( FALSE == $this->oDB->doTransaction() )
        {
            return -5011;
        }
        if( intval($this->switchLock($iParentId.','.$iUserId, $iChannelId, TRUE)) != 2 )
        {
            if( FALSE == $this->oDB->doRollback() ) //回滚事务
            {
                return -5012;
            }
            return -1;
        }
        if( FALSE == $this->oDB->doCommit() )//提交事务，锁定两帐户
        {
            return -5013;
        }
        $fMoney = round( floatval($fMoney), 4 );
        $oOrder = new model_orders();
        //原子操作参数设置
        $iOrderType_r   = ORDER_TYPE_XEKC;
        $sDescription_r = "小额扣除";
        $iOrderType_a   = ORDER_TYPE_XEJS;  
        $sDescription_a = "小额接收";   
        $sActionTime    = date("Y-m-d H:i:s"); //动作时间
        /**************************开始进行清理钱到上级********************/
        //开始事务
        if( FALSE == $this->oDB->doTransaction() )
        {
            return -5011;
        }
        //对清理用户进行帐变，扣钱原子操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iToUserId']    = $iParentId;
        $aOrders['iOrderType']   = $iOrderType_r;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription_r;
        $result = $oOrder->addOrders( $aOrders );
        if( TRUE !== $result )
        {
            if( FALSE == $this->oDB->doRollback() ) //回滚事务
            {
                return -5012;
            }
            $this->switchLock( $iParentId.','.$iUserId, $iChannelId, FALSE ); //解锁资金表
            return $result;
        }
        //对被提现用户（提现用户上级）进行帐变，加钱原子操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $iParentId;
        $aOrders['iToUserId']    = $iUserId;
        $aOrders['iOrderType']   = $iOrderType_a;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription_a;
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            if( FALSE == $this->oDB->doRollback() ) //回滚事务
            {
                return -5012;
            }
            $this->switchLock( $iParentId.','.$iUserId, $iChannelId, FALSE ); //解锁资金表
            return $result;
        }
        //提交事务
        if( FALSE == $this->oDB->doCommit() )
        {
            return -5013;
        }
        /**************************开始进行充值 ********************/
        //为两个资金帐户解锁
        if( FALSE == $this->switchLock($iParentId.','.$iUserId, $iChannelId, FALSE) )
        {
            return -3;
        }
        return TRUE;
    }

    /**
     * 在线支付 充值  系统给用户加钱
     *	掉单处理加钱，同样使用系统ID
     * 
     * @param int 		$iAdminId	充值者ID，以 ID0表 system
     * @param int 		$iUserId	接收方
     * @param floor 	$fMoney		充值费用
     * @param string 	$sDescription	充值描述
     * @param floor		$fFee		手续费
     * @param string 	$sDescriptionFee 手续费描述
     * @return int
     */
	public function systemOnlineLoadforUser( $iAdminId=0, $iUserId, $fMoney, $sDescription = "",$fFee="0",$sDescriptionFee = "" )
    {
        if( (!is_numeric($iAdminId))||(!is_numeric($iUserId)) )
        {
            return -1;	//数据不全
        }
        if( $sDescription != "" )
        {
            $sDescription = ":".daddslashes($sDescription);
        }
        
        $fMoney = round( floatval($fMoney), 2 );	//资金格式化
        $fFee = round( floatval($fFee), 2 );		//资金格式化
        if( FALSE == $this->switchLock($iUserId, 0, TRUE) )
        {//帐户被锁
            return -2;
        }

        $oOrder = new model_orders();

        $iOrderType   = ORDER_TYPE_ZXCZ;
        $sDescription = "在线充值".$sDescription;

        /***(开始进行充值帐变)***/
        //加钱
        $sActionTime = date('Y-m-d H:i:s'); //动作时间
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        //$aOrders['iToUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAdminId']     = $iAdminId;
        $mResult = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        
        if ($fFee > 0){
        	//加手续费  手续费为0时不增加手续费帐变
        $iOrderType   = ORDER_TYPE_ZXCZSF;
        $sDescription = "充值手续费".$sDescription;
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        //$aOrders['iToUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fFee;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescriptionFee;
        $aOrders['iAdminId']     = $iAdminId;
        $mResultFee = $oOrder->addOrders( $aOrders );
        	unset($aOrders);
        }else{
        	$mResultFee = TRUE;
        }
        
        if( (TRUE !== $mResult ) || (TRUE !== $mResultFee) )
        {
            // 解锁资金表
            if (FALSE == $this->switchLock($iUserId, 0, FALSE ) ){
            	return -4;
            }
            //return $mResult;	// 相关的返回值(参考orders)
            return -5;
        }
        // 提交事务
        if( FALSE == $this->switchLock($iUserId, 0, FALSE) ){
            return -3;
        }
        return true;
    }
    
	public function BAKsystemOnlineLoadforUser( $iAdminId=0, $iUserId, $fMoney, $sDescription = "",$fFee="0",$sDescriptionFee = "" )
    {
        if( (!is_numeric($iAdminId))||(!is_numeric($iUserId)) )
        {
            return -1;	//数据不全
        }
        if( $sDescription != "" )
        {
            $sDescription = ":".daddslashes($sDescription);
        }
        
        $fMoney = round( floatval($fMoney), 2 );	//资金格式化
        $fFee = round( floatval($fFee), 2 );		//资金格式化
        $this->oDB->doTransaction();
        if( FALSE == $this->switchLock($iUserId, 0, TRUE) )
        {//帐户被锁
            $this->oDB->doRollback();
            return -2;
        }
        //执行帐户被锁
        $this->oDB->doCommit();

        $oOrder = new model_orders();

        $iOrderType   = ORDER_TYPE_ZXCZ;
        $sDescription = "在线充值".$sDescription;

        /***(开始进行充值帐变)***/
        //加钱
        $sActionTime = date('Y-m-d H:i:s'); //动作时间
        $this->oDB->doTransaction();
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        //$aOrders['iToUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAdminId']     = $iAdminId;
        $mResult = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        
        if ($fFee > 0){
        	//加手续费  手续费为0时不增加手续费帐变
        $iOrderType   = ORDER_TYPE_ZXCZSF;
        $sDescription = "充值手续费".$sDescription;
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        //$aOrders['iToUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fFee;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescriptionFee;
        $aOrders['iAdminId']     = $iAdminId;
        $mResultFee = $oOrder->addOrders( $aOrders );
        	unset($aOrders);
        }else{
        	$mResultFee = TRUE;
        }
        
        if( (TRUE !== $mResult ) || (TRUE !== $mResultFee) )
        {
            $this->oDB->doRollback();
            // 解锁资金表
            if (FALSE == $this->switchLock($iUserId, 0, FALSE ) ){
            	return -4;
            }
            //return $mResult;	// 相关的返回值(参考orders)
            return -5;
        }
        $this->oDB->doCommit();
        // 提交事务
        if( FALSE == $this->switchLock($iUserId, 0, FALSE) ){
            return -3;
        }
        return true;
    }
    


    /************************ Saul 部分 ***************************************/
    /**
     * 管理员给用户充值
     *
     * @param int $adminid
     * @param int $iUserId
     * @param money $fMoney
     * @param string $description
     */
    function adminToUserSaveUp( $iAdminId, $iUserId, $fMoney, $sDescription = "" )
    {
        if( (!is_numeric($iAdminId))||(!is_numeric($iUserId)) )
        {
            return -1;//数据不全
        }
        if( $sDescription != "" )
        {
            $sDescription = ":".daddslashes($sDescription);
        }
        $fMoney = round( floatval($fMoney), 2 );//资金格式化
        $this->oDB->doTransaction();
        if( FALSE == $this->switchLock($iUserId, 0, TRUE) )
        {//帐户被锁
            $this->oDB->doRollback();
            return -2;
        }
        //执行帐户被锁
        $this->oDB->doCommit();
        /***(构建参数)***/
        $sSql   = "SELECT * FROM `usertree` WHERE `userid`='".$iUserId."' AND `parentid`='0' AND `isdeleted`='0' ";
        $aUsers = $this->oDB->getone( $sSql );
        $oOrder = new model_orders();
        if( empty($aUsers) )
        {//非总代
            $iOrderType   = ORDER_TYPE_KJCZ;
            $sDescription = "跨级充值".$sDescription;
        }
        else 
        {//总代
            $iOrderType   = ORDER_TYPE_SJCZ;
            $sDescription = "上级充值".$sDescription;
        }
        /***(开始进行充值帐变)***/
        $sActionTime = date("Y-m-d H:i:s"); //动作时间
        $this->oDB->doTransaction();
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAdminId']     = $iAdminId;
        $mResult = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $mResult )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iUserId, 0, FALSE );  //解锁资金表
            return $mResult;//相关的返回值(参考orders)
        }
        $this->oDB->doCommit();
        //提交事务
        if( FALSE == $this->switchLock($iUserId, 0, FALSE) )
        {
            return -3;
        }
        return 1;
    }



    /**
     * 管理员给用户理赔
     *
     * @param int $adminid
     * @param int $iUserId
     * @param money $fMoney
     * @param string $description
     * @param int $iChannelId
     * @return int
     */
    function adminPayMent($iAdminId, $iUserId, $fMoney, $sDescription = "", $iChannelId = 0 )
    {
        if( (!is_numeric($iAdminId)) || (!is_numeric($iUserId)) )
        {
            return -1;//数据不全
        }
        if( $sDescription != "" )
        {
            $sDescription = ":".daddslashes($sDescription);
        }
        $fMoney = round( floatval($fMoney), 2 );//资金格式化
        $this->oDB->doTransaction();
        if( FALSE == $this->switchLock($iUserId, 0, TRUE) )
        {
            $this->oDB->doRollback();
            return -2;//帐户被锁
        }
        //执行帐户被锁
        $this->oDB->doCommit();
        /***(构建参数)***/
        $sSql   = "SELECT * FROM `usertree` WHERE `userid`='".$iUserId."' AND `parentid`='0' AND `isdeleted`='0' ";
        $aUsers = $this->oDB->getone( $sSql );
        $oOrder = new model_orders();
        if( empty($aUsers) )
        {//非总代
            $iOrderType   = ORDER_TYPE_LPCZ;
            $sDescription = "理赔充值".$sDescription;
        }
        else 
        {//总代
            $iOrderType   = ORDER_TYPE_LPCZ;
            $sDescription = "理赔充值".$sDescription;
        }
        $sActionTime = date("Y-m-d H:i:s"); //动作时间
        /***(开始进行充值帐变)***/
        $this->oDB->doTransaction();
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAdminId']     = $iAdminId;
        $aOrders['iChannelID']   = $iChannelId;
        $mResult = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $mResult )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iUserId, 0, FALSE );  //解锁资金表
            return $mResult;//相关的返回值(参考orders)
        }
        $this->oDB->doCommit();
        //提交事务
        if( FALSE == $this->switchLock($iUserId, 0, FALSE) )
        {
            return -3;
        }
        return 1;
    } 



    /**
     * 获取用户的信用资金
     *
     * @param int $uid
     * @return array
     */
    function getUserCredit( $iUid )
    {
        $iUid = intval( $iUid );
        return $this->oDB->getOne("SELECT * FROM `topproxyset` WHERE `userid`='" .$iUid. "' AND `proxykey`='credit'");
    }



    /**
     * 管理员给用户提现
     *
     * @param int $iAdminId
     * @param int $iUserId
     * @param money $fMoney
     * @param string $sDescription
     * @param int $iChannelId
     * @return int
     */
    function admintoUserWithDraw( $iAdminId, $iUserId, $fMoney, $sDescription = "" )
    {
        if( (!is_numeric($iAdminId))||(!is_numeric($iUserId)) )
        {//数据不全
            return -1;
        }
        if( $sDescription != "" )
        {
            $sDescription = ":".$sDescription;
        }
        $fMoney = round( floatval($fMoney), 2 );//资金格式化
        $this->oDB->doTransaction();
        if( FALSE == $this->switchLock($iUserId, 0, TRUE) )
        {//帐户被锁
            $this->oDB->doRollback();
            return -2;
        }
        $this->oDB->doCommit();
        $oOrder    = new model_orders();
        $oUser     = new model_user();
        $aUserFund = $this->getFundByUser( $iUserId, "", 0, FALSE );
        if( $oUser->isTopProxy($iUserId) )
        {//总代部分
            $fUserteam   = $oUser->getTeamBank( $iUserId );//团队资金
            $aUserCredit = $this->getUserCredit( $iUserId );//信用资金
            $fUserc      = (!empty($aUserCredit)) ? $aUserCredit['proxyvalue'] : 0;
            $fUserMax    = min(($fUserteam - $fUserc), $aUserFund['availablebalance']);
            if( $fUserMax < $fMoney )
            {
                $this->switchLock( $iUserId, 0, FALSE );  //解锁资金表
                return -3;//用户资金不足
            }
            $iOrderType   = ORDER_TYPE_BRTX;
            $sDescription = "上级操作提现".$sDescription;
        }
        else 
        {//非总代
            $fUserMax = $aUserFund['availablebalance'];
            if( $fUserMax < $fMoney )
            {
                $this->switchLock( $iUserId, 0, FALSE );  //解锁资金表
                return -3;//用户资金不足
            }
            $iOrderType   = ORDER_TYPE_KJTX;
            $sDescription = "上级操作提现".$sDescription;
        }
        $sActionTime = date("Y-m-d H:i:s"); //动作时间
        $this->oDB->doTransaction();
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAdminId']     = $iAdminId;
        $mResult = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $mResult )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iUserId, 0, FALSE );  //解锁资金表
            return $mResult;//相关的返回值(参考orders)
        }
        $this->oDB->doCommit();
        //提交事务
        if( FALSE == $this->switchLock( $iUserId, 0, FALSE ) )
        {
            return -4;
        }
        return 1;
    }



    /**
     * 管理员扣减
     *
     * @param int $iAdminId
     * @param int $iUserId
     * @param money $fMoney
     * @param string $sDescription
     * @param int $iChannelId
     * @return int
     * 
     */
    function admintoUserPayWithDraw( $iAdminId, $iUserId, $fMoney, $sDescription = "", $iChannelId )
    {
        if( (!is_numeric($iAdminId))||(!is_numeric($iUserId)) )
        {
            return -1;//数据不全
        }
        if( $sDescription != "" )
        {
            $sDescription = ":".daddslashes($sDescription);
        }
        $fMoney = round( floatval($fMoney), 2 );//资金格式化
        $this->oDB->doTransaction();
        if( FALSE == $this->switchLock($iUserId, 0, TRUE) )
        {//帐户被锁
            $this->oDB->doRollback();
            return -2;
        }
        $this->oDB->doCommit();
        $oOrder    = new model_orders();
        $oUser     = new model_user();
        $aUserfund = $this->getFundByUser( $iUserId, "", 0, FALSE );
        if( $oUser->isTopProxy($iUserId) )
        {//总代部分
            $fUserTeam   = $oUser->getTeamBank($iUserId);//团队资金
            $aUserCredit = $this->getUserCredit($iUserId);//信用资金
            $fUserc      = (!empty($aUserCredit)) ? $aUserCredit['proxyvalue']:0;
            $fUserMax    = min( ($fUserTeam - $fUserc), $aUserfund['availablebalance'] );
            if( $fUserMax < $fMoney )
            {
                $this->switchLock( $iUserId, 0, FALSE );  //解锁资金表
                return -3;//用户资金不足 
            }
            $iOrderType   = ORDER_TYPE_GLYKJ;
            $sDescription = "管理员扣减".$sDescription;
        }
        else 
        {//非总代
            $fUserMax = $aUserfund['availablebalance'];
            if( $fUserMax < $fMoney )
            {
                $this->switchLock( $iUserId, 0, FALSE );//解锁资金表
                return -3;//用户资金不足
            }
            $iOrderType   = ORDER_TYPE_GLYKJ;
            $sDescription = "管理员扣减".$sDescription;
        }
        $this->oDB->doTransaction();
        $sActionTime = date("Y-m-d H:i:s");//动作时间
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAdminId']     = $iAdminId;
        $aOrders['iChannelID']   = $iChannelId;
        $mResult = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $mResult )
        {
            $this->oDB->doRollback();
            $this->switchLock( $iUserId, 0, FALSE );//解锁资金表
            return $mResult;//相关的返回值(参考orders)
        }
        $this->oDB->doCommit();
        //提交事务
        if( FALSE == $this->switchLock($iUserId, 0, FALSE) )
        {
            return -4;
        }
        return 1;
    }



    /**
     * 信用处理
     *
     * @param int $iadminid
     * @param int $iUserid
     * @param money $fMoney
     * @param string $saction
     */
    function changeUserCredit( $iAdminId, $iUserId ,$fMoney, $sAction )
    {
        $iAdminId = intval( $iAdminId );
        $iUserId  = intval( $iUserId );
        if( !in_array($sAction, array('add','sub')) )
        {//非法参数
            return 0;
        }
        $aIsTop = $this->oDB->getOne("select `userid` from `usertree` where `userid`='".$iUserId."' and `parentid`='0'");
        if( empty($aIsTop) )
        {//没有信用
            return -1;
        }
        unset( $aIsTop );
        if( $fMoney == 0 )
        {//参数忽略
            return -2;
        }
        $fMoney = round( floatval($fMoney), 2 );
        //锁帐户
        $this->oDB->doTransaction();
        if( !$this->switchLock($iUserId, 0, TRUE) )
        {
            $this->oDB->doRollback();
            return -3;//帐户锁定中..
        }
        $this->oDB->doCommit();
        //查询个人的资金
        $userData = $this->getFundByUser( $iUserId, "", 0, FALSE );
        if( count($userData) < 1 )
        {
            $this->switchLock( $iUserId, 0, FALSE );
            return -4;//帐户出现问题
        }
        $aCredit = $this->getUserCredit($iUserId);
        $oOrder  = new model_orders();
        if( $sAction === "add" )
        {
            $iOrderType   = ORDER_TYPE_XYCZ;
            $sDescription = "信用充值";
            $fMoneyAdd    = $fMoney;
        }
        else
        {
            if( $fMoney>min($aCredit['proxyvalue'],$userData['availablebalance']) )
            {
                $this->switchLock($iUserId, 0, FALSE);
                return -5; //用户不够扣减信用
            }
            $iOrderType   = ORDER_TYPE_XYKJ;
            $sDescription = "信用扣减"; 
            $fMoneyAdd    = -$fMoney;
        }
        $sActionTime = date("Y-m-d H:i:s"); //动作时间
        $this->oDB->doTransaction();
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fMoney;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAdminId']     = $iAdminId;
        $mResult = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $mResult )
        {
            $this->oDB->doRollback();
            $this->switchLock($iUserId, 0, FALSE);	//解锁资金表
            return $mResult;//相关的返回值(参考orders)
        }
        $sSql = "update `topproxyset` set `proxyvalue`='".(floatval($aCredit['proxyvalue']) + $fMoneyAdd)."'"
            ." where `proxykey`='credit' and `userid`='".$iUserId."'";
        $this->oDB->query($sSql);
        if( $this->oDB->ar() < 1 )
        {
            $this->oDB->doRollback();
            $this->switchLock($iUserId,0,FALSE);    //解锁资金表
            return -8;//信用出错
        }
        $this->oDB->doCommit();
        if( FALSE == $this->switchLock($iUserId,0,FALSE) )
        {
            return -9;//为资金帐户解锁
        }
        return 1;
    }



    /**
     * 获取N秒前还在锁着的用户
     *
     * @param int $times
     * @return Array
     * @author SAUL
     */
    public function fundUnlockList( $iTime = 300 )
    {
        $iTime = intval( $iTime );
        return $this->oDB->getAll( "SELECT `entry`,`username`,`lastupdatetime` FROM `userfund` LEFT JOIN "
        ." `usertree` ON (`usertree`.`userid`=`userfund`.`userid`) WHERE `userfund`.`islocked`='1' AND "
        ."`userfund`.`lastupdatetime`<'".date("Y-m-d H:i:s",strtotime("-".$iTime." seconds"))."'" );
    }



    /**
     * 更新N秒前还在锁着的用户
     *
     * @param array $aUser
     * @param int $times
     * @return mixed
     * @author SAUL 090518
     */
    public function fundUnlock( $aUser, $iTime = 300 )
    {
        if( empty($aUser) || !is_array($aUser) )
        {
            return -1;
        }
        $iTime = intval($iTime);
        foreach( $aUser as $key => $value )
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
        return $this->oDB->query( "UPDATE `userfund` SET `islocked`='0',`lastupdatetime`='".date('Y-m-d H:i:s')."' WHERE `entry` IN "
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
            . "`topproxyset`.proxyvalue ,`userfund`.userid,d.`username` AS `topname` FROM `userfund` "
            . "LEFT JOIN `topproxyset` ON(`topproxyset`.`proxykey`='credit' AND "
            . "`userfund`.`userid`=`topproxyset`.`userid`) "
            . " LEFT JOIN `usertree` as c ON(c.`userid`=`userfund`.`userid`) "
            . " LEFT JOIN `usertree` as d ON(c.`lvtopid`=d.`userid`) "
            . " WHERE (`userfund`.`availablebalance`+userfund.holdbalance != userfund.channelbalance ) "
            . " OR (`userfund`.`cashbalance`+`topproxyset`.`proxyvalue` != userfund.channelbalance)" );	
        foreach($aArr as $iKey => $arr)
        {
            if( intval(number_format($arr["availablebalance"],2,".","")*100) + 
                intval(number_format($arr["holdbalance"],2,".","")*100) == 
                intval(number_format($arr["channelbalance"],2,".","")*100) )
            {
                if( intval(number_format($arr["cashbalance"],2,".","")*100) + 
                    intval(number_format($arr["proxyvalue"],2,".","")*100) == 
                    intval(number_format($arr["channelbalance"],2,".","")*100) )
                {
                    unset($aArr[$iKey]);
                }
            }
        }
        return $aArr;
    }
    
    
    /**************************** Tom 部分 *****************************/
    /**
     * 查看帐变，可以自定义查询条件[带分页效果]
     * 
     * @access  public
     * @author  Tom
     * @param   string  $sFields      // 要查询的内容，表别名:usertree=>ut,orders=>o,ordertype=>ot
     * @param   string  $sCondition   // 附加的查询条件，以AND 开始
     * @param   int     $iPageRecords // 每页显示的条数
     * @param   int     $iCurrPage    // 当前页
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
     * @access  public
     * @author  Tom
     * @param   int     $iUserId      // 用户ID, 对应 users.userid
     * @param   string  $sWhere       // 附加的查询条件
     * @param   string  $sHaving      // 附加的查询条件
     * @return array
     */
    public function & getProxyTeamFundList( $iUserId = 0, $sWhere = "1", $sHaving = "" )
    {
        $sTableName = "`usertree` ut force index( idx_usertree ) LEFT JOIN `userfund` uf ".
                      " ON ( uf.`userid` = ut.`userid` AND ut.`isdeleted`=0 AND ut.`usertype` < 2 and uf.`channelid`=0 ) ". 
                      " LEFT JOIN `usertree` u on ut.`lvtopid` = u.`userid` ".
                      " LEFT JOIN `topproxyset` t ON ( ut.`lvtopid` = t.`userid` AND t.`proxykey` = 'credit' ) ";
        $mFields    = "uf.`userid`,u.`username` as topuname,u.`usertype` as toputype,u.`parentid` as topuparentid, ".
                   " ut.`username`,ut.`usertype`,ut.`parentid`, ".
                   " t.`proxyvalue` AS TeamCredit, ".
                   " sum( uf.availablebalance ) as TeamAvailBalance, ".
                   " sum( uf.cashbalance ) as TeamCashBalance, ".
                   " sum( uf.holdbalance ) as TeamHoldBalance, ".
                   " sum( uf.channelbalance ) as TeamChannelBalance, ".
                   " ut.lvtopid";
        $sSql = '';
        if( $iUserId != 0 )
        { # 需求1: 指定 $iUserId 则只统计指定用户ID的所有团队资金情况
            $sHaving = $sHaving=='' ? '' : 'HAVING '.daddslashes($sHaving);
            $sWhere  = $sWhere.' AND u.`isdeleted` = 0 AND ( uf.`userid` = '.intval($iUserId).' or find_in_set( '.intval($iUserId).', ut.parenttree ) ) ORDER BY ut.username ';
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
                      " u.`isdeleted` = 0 AND ut.`lvtopid` != 0 AND ( FIND_IN_SET( ut.`parentid`, ut.`parenttree` ) OR ut.`parentid` = 0 ) ".
                      " GROUP BY ut.`lvtopid` $sHaving ORDER BY ut.username";
            $aResult = $this->oDB->getAll($sSql);
            return $aResult;
        }
    }



    /**
     * 查看游戏币明细，可以自定义查询条件, 不需要分页
     *   - 只获取总代自身资金
     * @access  public
     * @param   int     $iUserId      // 用户ID, 对应 users.userid
     * @param   string  $sWhere       // 附加的查询条件
     * @param   string  $sHaving      // 附加的查询条件
     * @return array
     * @author Tom 090609 13:06
     */
    public function & getProxyFundList( $iUserId = 0, $sWhere = "1" )
    {
        $sTableName = "`usertree` ut force index( idx_usertree ) LEFT JOIN `userfund` uf ".
                      " ON ( uf.`userid` = ut.`userid` AND ut.`isdeleted`=0 AND ut.`usertype` < 2 and uf.`channelid`=0 ) ". 
                      " LEFT JOIN `topproxyset` t ON ( ut.`userid` = t.`userid` AND t.`proxykey` = 'credit' ) ";
        $mFields = "uf.`userid`,ut.`username`,ut.`usertype`,ut.`parentid`, ".
                   " t.`proxyvalue` AS TeamCredit, ".
                   " uf.availablebalance AS TeamAvailBalance, ".
                   " uf.cashbalance AS TeamCashBalance, ".
                   " uf.holdbalance AS TeamHoldBalance, ".
                   " uf.channelbalance AS TeamChannelBalance ";
        $sSql = '';
        if( $iUserId != 0 )
        { # 需求1: 指定 $iUserId 则只统计指定用户ID的所有团队资金情况
            $sWhere = $sWhere.' AND uf.`userid` = '.intval($iUserId).' ORDER BY ut.username ';
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
            $sSql   = "SELECT $mFields FROM $sTableName WHERE $sWhere AND ut.`isdeleted` = 0 AND ut.`parentid` = 0 ORDER BY ut.username  ";
            $aResult = $this->oDB->getAll($sSql);
            return $aResult;
        }
    }





    /**
     * 频道转账
     * 从 passport/_api/channelTransition.php   apiFundTransition() 函数发起调用
     * @author  tom     090922 13:33
     * @param   string  $aDatas[sMethod]         // 转账方法
     * @param   string  $aDatas[sType]           // 转账类型 reduce | plus
     * @param   int     $aDatas[iUserId]         // 用户ID
     * @param   int     $aDatas[iFromChannelId]  // 资金转出频道 ( 扣款 )
     * @param   int     $aDatas[iToChannelId]    // 资金转入频道 ( 加钱 )
     * @param   float   $aDatas[fMoney]          // 转账金额
     * @param   string  $aDatas[sUnique]         // 唯一值
     * @param   string  $aDatas[iAdminId]        // 管理员ID
     * @return  mix     全等于TRUE 为成功; 其他均为失败信息
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
            return 'model.userfund.apiFundTransition() : Error #5002';
        }

        if( $aDatas['sType'] == 'reduce' && SYS_CHANNELID != $aDatas['iFromChannelId'] )
        {
             return 'model.userfund.apiFundTransition() : Error #5003';
        }

        if( $aDatas['sType'] == 'plus' && SYS_CHANNELID != $aDatas['iToChannelId'] )
        {
             return 'model.userfund.apiFundTransition() : Error #5004';
        }

        if(  $aDatas['sType'] == 'plus' && !array_key_exists('iOrderEntry', $aDatas) )
        { // 扣钱方账变ID
            return 'model.userfund.apiFundTransition() : Error #5005';
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
                return 'model.userfund.apiFundTransition() : Channel is not available #5006_1';
            }
            $aAvailChannels['99'] = '高频';
        }
        else 
        {
            // 正常流程开始
            if( count($aAvailChannels)!=2 )
            {
                return 'model.userfund.apiFundTransition() : Channel is not available #5006_2';
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
        $iTargetChannelid         = ($aDatas['sType'] == 'reduce') ? $aDatas['iToChannelId'] : $aDatas['iFromChannelId'];
        $iAdminId                 = isset($aDatas['iAdminId']) ? intval($aDatas['iAdminId']) : 0;
        $sAdminName               = isset($aDatas['sAdminName']) ? daddslashes($aDatas['sAdminName']) : '';


        // STEP: 04 转账业务流程 ----------------------------------------------------------
        // 4.1 试图锁定用户资金 userFund (行锁)
        if( FALSE == $this->oDB->doTransaction()){ return 'model.userfund.apiFundTransition() : #5010 Transaction Failed'; }
        if( intval($this->switchLock($aDatas['iUserId'], SYS_CHANNELID ,TRUE)) != 1 )
        {
            if( FALSE == $this->oDB->doRollback()){ return 'model.userfund.apiFundTransition() : #5011 Transaction Failed'; }
            return 'model.userfund.apiFundTransition() : #5012 Lock UserFund Failed';
        }
        if( FALSE == $this->oDB->doCommit()){ return 'model.userfund.apiFundTransition() : #5013 Transaction Failed'; }

        // 4.2 获取涉及用户的帐户信息 (usertree)
        $aFromData = $this->oDB->getOne("SELECT `username` FROM `usertree` WHERE `userid`='".
                                    $aDatas['iUserId']."' AND `isdeleted`='0' ");
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
        {
            case 'SYS_SMALL' : 
            {
                $iOrderType   = ORDER_TYPE_PDXEZR; // 账变类型: 频道小额转入
                $sDescription = $aAvailChannels[ $aDatas['iFromChannelId'] ] 
                            . " 转入: " . $aAvailChannels[ $aDatas['iToChannelId'] ];
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
                    $iOrderType   = ORDER_TYPE_YHZC; // 账变类型: 银行转出
                    $sDescription = $aAvailChannels[ $aDatas['iFromChannelId'] ] ." 转出至: " . $aAvailChannels[ $aDatas['iToChannelId'] ];
                }
                else
                {
                    $iOrderType   = ORDER_TYPE_ZRYH; // 账变类型: 转入银行
                    $sDescription = $aAvailChannels[ $aDatas['iFromChannelId'] ] . " 转入: " . $aAvailChannels[ $aDatas['iToChannelId'] ];
                }
                BREAK;
            }
        }

        $sActionTime    = date("Y-m-d H:i:s");	// 动作时间
        $aOrders = array();
        $aOrders['iFromUserId']        = $aDatas['iUserId'];
        $aOrders['iOrderType']         = $iOrderType;
        $aOrders['fMoney']             = $aDatas['fMoney'];
        $aOrders['sActionTime']        = $sActionTime;
        $aOrders['sDescription']       = $sDescription;
        $aOrders['iChannelId']         = SYS_CHANNELID;
        $aOrders['sUniqueKey']         = $aDatas['sUnique'];
        $aOrders['iTransferUserid']    = $aDatas['iUserId'];
        $aOrders['iTransferChannelid'] = $iTargetChannelid;
        // 如果是转账的加钱行为, 写入扣钱方账变ID, 转账状态直接写2 表示:成功
        $aOrders['iTransferOrderid']   = $aDatas['sType'] == 'plus' ? intval($aDatas['iOrderEntry']) : 0;
        $aOrders['iTransferStatus']    = $aDatas['sType'] == 'plus' ? 2 : 1;
        $aOrders['iAdminId']           = $iAdminId;
        $aOrders['sAdminName']         = $sAdminName;
        $aOrders['bIgnoreMinus']       = in_array( $aDatas['sMethod'], array('USER_TRAN','SYS_ZERO','SYS_SMALL') ) ? TRUE : FALSE;

        // 由用户自主发起的频道转账, 设置 '帐变忽略负数(bIgnoreMinus)' 的参数为关闭
        // 即: 用户转账时, 转出平台扣钱帐变的产生禁止负数, 若为负数则禁止转账并返回帐变相关的错误信息
        if( 'USER_TRAN'==$aDatas['sMethod'] && $aDatas['sType']=='reduce' )
        {
            $aOrders['bIgnoreMinus'] = FALSE;
        }

        // 开始事务
        if( FALSE == $this->oDB->doTransaction()){ return 'model.userfund.apiFundTransition() : #5015 Transaction Failed'; }
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            if( FALSE == $this->oDB->doRollback()){ return 'model.userfund.apiFundTransition() : #5016 Transaction Failed'; }
            $this->switchLock($aDatas['iUserId'], SYS_CHANNELID, FALSE);    //解锁资金表
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
     * @author  tom
     * @param string  $sUnique  唯一值 
     * @param int     $iUserId  用户ID
     * @param float   $fMoney
     * @return  mix  整型自然数, 或全等于的 FALSE
     */
    public function getOrderEntryByTranferData( $sUnique = '', $iUserId = 0, $fMoney = 0.00 )
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