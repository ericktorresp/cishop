<?php
    /**
    * 用户提现模型
    * 
    * 功能:
    *    -- getWithDrawelById      获取一个一条提现消息
    *    -- getWithDrawelList      获取提现申请表数据
    *    -- delWithdrawelByArr     删除管理员的消息 (需判断管理员可操作的消息类型,进行删除)
    *    -- setWithdrawStatus      设置提现申请状态 (管理员操作)
    *    -- withdrawelAdd          用户发起平台提现
    *    -- getBankList            获取支持的银行列表
    *    -- getCreditUserMaxMoney  获取信用用户能不能提一个金额
    * 	 -- dealWithdraw		   出纳处理操作
    * 	 -- lockRecord			   出纳员处理时，将记录锁住，防止重复操作
    *    -- unLock				   解锁操作
    * 
    * @author    James, Tom
    * @version   1.0.0
    * @package   passport
    * 
    */

class model_withdrawel extends basemodel 
{
    /**
     * 获取一个一条提现消息
     * @access   public
     * @param    int     $inoticeId
     * @return   array()  =>  空结果集
     *                    =>  正常查询结果
     */
    public function getWithDrawelById( $iWithDrawelId = 0 )
    {
        $iWithDrawelId = intval( $iWithDrawelId );
        return $this->oDB->getOne('SELECT a.*, b.adminname as opname, c.username as sendername, d.username as proxyname '.
                        ' FROM `withdrawel` a LEFT JOIN `adminuser` b ON a.`adminid`=b.`adminid` '.
                        ' LEFT JOIN `users` c ON a.`userid`=c.`userid` '.
                        ' LEFT JOIN `users` d ON a.`topproxyid`=d.`userid` '.
                        " WHERE `entry`='$iWithDrawelId' LIMIT 1 ");
    }
    
    
    /**
     * 获取一个一条提现消息(总代用)
     * @access   public
     * @param    int     $inoticeId
     * @return   array()  =>  空结果集
     *                    =>  正常查询结果
     */
    public function getUserWithDrawelById( $iWithDrawelId = 0, $iUserId = 0 )
    {
        $iWithDrawelId = intval( $iWithDrawelId );
        $iUserId       = intval( $iUserId );
        return $this->oDB->getOne('SELECT a.*, b.username as opname, c.username as sendername, d.username as proxyname '.
                        ' FROM `withdrawel` a LEFT JOIN `users` b ON a.`adminid`=b.`userid` '.
                        ' LEFT JOIN `users` c ON a.`userid`=c.`userid` '.
                        ' LEFT JOIN `users` d ON a.`topproxyid`=d.`userid` '.
                        " WHERE `entry`='$iWithDrawelId' AND a.`topproxyid`='".$iUserId."' LIMIT 1 ");
    }



    /**
     * 获取提现申请表数据
     * @access public
     * @author tom
     * @param  string    $sFields
     * @param  string    $sCondition
     * @param  int       $iPageRecords
     * @param  int       $iCurrPage
     * @return array
     */
    public function & getWithDrawelList( $sFields = "" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1 )
    {
        $sTableName = ' `withdrawel` a LEFT JOIN `adminuser` b ON a.`adminid`=b.`adminid` '.
                        ' LEFT JOIN `users` c ON a.`userid`=c.`userid` '.
                        ' LEFT JOIN `users` d ON a.`topproxyid`=d.`userid` ';
        $sFields    = !empty($sFields) ? $sFields : 
                       ' a.*, b.adminname as opname, c.username as sendername, d.username as proxyname ';
        $sOrderBy   = " ORDER BY `entry` DESC ";
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, $sOrderBy );
    }
    //获取提现申请表数据(总代用)
    public function & getUserWithDrawelList( $sFields = "" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1 )
    {
        $sTableName = ' `withdrawel` a LEFT JOIN `users` b ON a.`adminid`=b.`userid` '.
                        ' LEFT JOIN `users` c ON a.`userid`=c.`userid` '.
                        ' LEFT JOIN `users` d ON a.`topproxyid`=d.`userid` ';
        $sFields    = !empty($sFields) ? $sFields : 
                       ' a.*, b.username as opname, c.username as sendername, d.username as proxyname ';
        $sOrderBy   = " ORDER BY `entry` DESC ";
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, $sOrderBy );
    }



    /**
     * 删除管理员的消息 (需判断管理员可操作的消息类型,进行删除)
     *
     * @access public
     * @param  array    $aHtmlCheckBox
     * @return int
     */
    public function delWithdrawelByArr( $aHtmlCheckBox = array() )
    {
        $sWhere  = '';
        //1, 整理被删除数据ID
        $sDelIds = '';
        if( !is_array($aHtmlCheckBox) || empty($aHtmlCheckBox) )
        {
            return FALSE;
        }
        foreach( $aHtmlCheckBox as $v )
        {
            if( is_numeric($v) )
            {
                $sDelIds .= $v.",";
            }
        }
        if( substr($sDelIds, -1, 1) == ',' )
        {
            $sDelIds = substr( $sDelIds, 0, -1 );
        }
        if( $sDelIds == '' )
        {
            return FALSE;
        }
        $sWhere  .= " AND `entry` IN ($sDelIds) ";
        $sNowTime = date('Y-m-d H:i:s');
        $iAdminId = intval($_SESSION['admin']);
        if( $iAdminId == 0 )
        {
            return FALSE;
        }
        $this->oDB->query( "UPDATE `withdrawel` SET ".
                           " `adminid`='$iAdminId', `isdel`='1', `finishtime`='$sNowTime' WHERE `isdel`=0 $sWhere" );
        return $this->oDB->ar();
    }



    /**
     * 设置提现申请状态 (管理员操作)
     * 
     * 成功 =>  账变类型为: 平台提现成功    ORDER_TYPE_SWTXCG 
     * 失败 =>  账变类型为: 平台提现失败    ORDER_TYPE_SWTXSB
     * 
     * @access  public
     * @author  Tom
     * @param   int      $iWithDrawId   // 提现申请ID, 对应 `withdrawel`.entry
     * @param   int      $sType         // 提现操作类型:  SUCCESSED | FAILED
     * @param   string   $sMessage       // 提现成功=银行流水号,  提现失败=返回给用户的消息内容
     * @return  mixed   //   0: 其他失败
     *                      -1: 锁定资金帐户失败(即可能资金表被其他占用)
     *                      -2：获取用户信息失败
     *                      -3: 完成后对资金解锁失败
     *                      -1001: 用户ID错误
     *                      -1000+ ...... 参考orders
     *                      TRUE:成功
     */
    public function setWithdrawStatus( $iWithDrawId = 0, $sType = 'SUCCESSED', $sMessage = '' )
    {
        // STEP 01: 数据整理 (安全过滤)
        $iWithDrawId = intval( $iWithDrawId );
        if( $iWithDrawId == 0 || ( $sType != 'SUCCESSED' && $sType != 'FAILED' ) )
        { // 数据初始化错误
            return -1; 
        }
        $aWithDrawel = $this->getWithDrawelById($iWithDrawId); // 获取提现申请记录
        if( empty($aWithDrawel) )
        { // 无法获取结果集
            return -2;
        }
        
        $oUser = new model_user();
        $iIsTop= 0; //是否为总代申请
        if( $aWithDrawel['topproxyid']==$aWithDrawel['userid'] && $oUser->isTopProxy($aWithDrawel['userid']) )
        {
            $iIsTop = 1;
        }
        /*if( $aWithDrawel['isforcompany'] != 1 //|| $aWithDrawel['topproxyid']!=$aWithDrawel['userid']
            //|| !$oUser->isTopProxy($aWithDrawel['userid']) )
            )
        { // 如果非公司处理
        	return -4;
        }*/
        unset($oUser);

        if($aWithDrawel['paycard_id']>0 && $aWithDrawel['status']== 3 && $aWithDrawel['isdel']==0 )
        {
            $bOperCanUpdate = TRUE;
        }
        else
        {
            $bOperCanUpdate = FALSE; // 设置提现申请可修改状态为 FALSE
            if( $aWithDrawel['adminid']==0 && $aWithDrawel['status']== 3 
                && $aWithDrawel['finishtime']==0 && $aWithDrawel['isdel']==0 )
            {
                $bOperCanUpdate = TRUE;
            }
            if( $bOperCanUpdate == FALSE )
            { // 提现申请已处理, 不允许再次修改
                return -3;
            }
        }

        // STEP 02: 审核提现申请(增加账变)
        $oUserFund = new model_userfund();
        $this->oDB->doTransaction(); // 1, 第1次事务开始, 对用户账户试图加锁
        if( 1 != $oUserFund->switchLock( $aWithDrawel['userid'], 0, TRUE ) )
        {
            $this->oDB->doRollback(); // 锁定失败,则第1次事务回滚,程序中断
            return -10; // 消息返回: 账户资金临时被锁, 请稍后再试
        }
        $this->oDB->doCommit(); // 第1次事务提交 (锁定账户语句提交)

        // STEP 03: 开始账变操作
        $oOrder     = new model_orders(); // 1, 生成账变对象,包括初始类中的账变类型宏声明 define
        $iOrderType = 0; // 初始化账变类型
        if( $sType == 'SUCCESSED' )
        { // 设置为 平台提现成功
            $iOrderType   = $iIsTop == 1 ? ORDER_TYPE_SWTXCG : ORDER_TYPE_PTTXCG;
            $sDescription = $aWithDrawel['sendername'] . ($iIsTop == 1 ? ' 商务提现成功' : "平台提现成功");
        }
        else 
        { // 设置为 平台提现失败
            $iOrderType   = $iIsTop == 1 ? ORDER_TYPE_SWTXSB : ORDER_TYPE_PTTXSB;
            $sDescription = $aWithDrawel['sendername'] . ($iIsTop == 1 ? ' 商务提现失败' : "平台提现失败");
        }
        $sActionTime = date("Y-m-d H:i:s");	//动作时间
        $this->oDB->doTransaction();  // 2, 开始事务 (第2次)
        $aOrders = array();
        $aOrders['iFromUserId']  = $aWithDrawel['userid'];
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $aWithDrawel['amount'];
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAdminId']     = intval( $_SESSION['admin'] );
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        { // 账变发生异常, 事务回滚
            $this->oDB->doRollback();
            $oUserFund->switchLock( $aWithDrawel['userid'], 0, FALSE );
            return $result;
        }
        // STEP 04, 更新提现记录状态
        $aNewWithDrawel['adminid']    = intval($_SESSION['admin']);
        $aNewWithDrawel['verify_name']= $_SESSION['adminname'];
        $aNewWithDrawel['verify_time']= date("Y-m-d H:i:s", time());
        $aNewWithDrawel['status']     = $sType=='SUCCESSED' ? 2 : 1; //1=失败, 2=成功
        $aNewWithDrawel['finishtime'] = date('Y-m-d H:i:s');
        if( $sType == 'SUCCESSED' )
        {// 提现成功, 则更新银行流水号
            //$aNewWithDrawel['bankcode'] = daddslashes(trim($sMessage));
        }
        $this->oDB->update( 'withdrawel', $aNewWithDrawel, " `entry`='$iWithDrawId' AND `status`=3 LIMIT 1" );
        if( !$this->oDB->ar() || $this->oDB->ar() != 1 )
        { // 更新提现申请记录信息
        	$this->oDB->doRollback();
            $oUserFund->switchLock( $aWithDrawel['userid'], 0, FALSE );
            return -12;
        }
        unset($aNewWithDrawel);

        // STEP 05, 站内短消息,通知用户提现申请已处理完成
        $oMessage = new model_message();
        if( $iOrderType == ORDER_TYPE_SWTXCG || $iOrderType == ORDER_TYPE_PTTXCG )
        {
            $aNewMsg['subject'] = '提现成功';
            $aNewMsg['content'] = '提现申请成功。';
        }
        else
        {
            $aNewMsg['subject'] = '提现失败';
            $aNewMsg['content'] = '提现申请失败，请注意查看您的帐变信息。';
            $aNewMsg['content'] .= "\n<br>提现失败原因： ".$sMessage;
        }
        $iWithDrawelUserId = intval( $aWithDrawel['userid'] ); // for 消息发送
        $aNewMsg['receiverid']  = $iWithDrawelUserId;
        $iFlag = $oMessage->sendMessageToUser($aNewMsg);
        
        if ($iFlag <= 0){
        	$this->oDB->doRollback();
            $oUserFund->switchLock( $aWithDrawel['userid'], 0, FALSE );
            return -13;
        }
        
        $this->oDB->doCommit(); //  第2次事务提交

        // 账户解锁
        if( 1 != $oUserFund->switchLock( $aWithDrawel['userid'], 0, FALSE ) )
        {
            return -11; // 资金账户解锁失败
        }
        
        unset($aWithDrawel);

        return $iFlag > 0 ? TRUE : FALSE;
    }
    
    
    /**
     * 设置提现申请状态 (总代操作)
     * 
     * 成功 =>  账变类型为: 平台提现成功    ORDER_TYPE_SWTXCG 
     * 失败 =>  账变类型为: 平台提现失败    ORDER_TYPE_SWTXSB
     * 
     * @access  public
     * @author  Tom
     * @param   int      $iWithDrawId   // 提现申请ID, 对应 `withdrawel`.entry
     * @param   int      $sType         // 提现操作类型:  SUCCESSED | FAILED
     * @param   string   $sMessage       // 提现成功=银行流水号,  提现失败=返回给用户的消息内容
     * @return  mixed   //   0: 其他失败
     *                      -1: 锁定资金帐户失败(即可能资金表被其他占用)
     *                      -2：获取用户信息失败
     *                      -3: 完成后对资金解锁失败
     *                      -1001: 用户ID错误
     *                      -1000+ ...... 参考orders
     *                      TRUE:成功
     */
    public function setWithdrawStatusByUser( $iWithDrawId = 0, $sType = 'SUCCESSED', $sMessage = '', $iUserId=0 )
    {
        // STEP 01: 数据整理 (安全过滤)
        $iWithDrawId = intval( $iWithDrawId );
        $iUserId     = intval( $iUserId );
        if( $iWithDrawId == 0 || ( $sType != 'SUCCESSED' && $sType != 'FAILED' ) )
        { // 数据初始化错误
            return -1; 
        }
        $aWithDrawel = $this->getUserWithDrawelById( $iWithDrawId, $iUserId ); // 获取提现申请记录
        if( empty($aWithDrawel) )
        { // 无法获取结果集
            return -2;
        }
        
        $oUser = new model_user();
        if( $aWithDrawel['isforcompany'] !=0 || $aWithDrawel['topproxyid']==$aWithDrawel['userid']
            || $oUser->isTopProxy($aWithDrawel['userid']) )
        { // 如果是总代发起的提现, 禁止总代进行处理
            return -4;
        }
        unset($oUser);

        $bOperCanUpdate = FALSE; // 设置提现申请可修改状态为 FALSE
        if( $aWithDrawel['adminid']==0 && $aWithDrawel['status']==0 
            && $aWithDrawel['finishtime']==0 && $aWithDrawel['isdel']==0 )
        {
            $bOperCanUpdate = TRUE;
        }
        if( $bOperCanUpdate == FALSE )
        { // 提现申请已处理, 不允许再次修改
            return -3;
        }

        // STEP 02: 审核提现申请(增加账变)
        $oUserFund = new model_userfund();
        $sUserIds  = $aWithDrawel['userid'].",".$aWithDrawel['topproxyid'];
        $this->oDB->doTransaction(); // 1, 第1次事务开始, 对用户账户试图加锁
        if( 2 != $oUserFund->switchLock( $sUserIds, 0, TRUE ) )
        {
            $this->oDB->doRollback(); // 锁定失败,则第1次事务回滚,程序中断
            return -10; // 消息返回: 账户资金临时被锁, 请稍后再试
        }
        $this->oDB->doCommit(); // 第1次事务提交 (锁定账户语句提交)

        // STEP 03: 开始账变操作
        $oOrder     = new model_orders(); // 1, 生成账变对象,包括初始类中的账变类型宏声明 define
        $iOrderType = 0; // 初始化账变类型
        if( $sType == 'SUCCESSED' )
        { // 设置为 平台提现成功
            $iOrderType   = ORDER_TYPE_PTTXCG;
            $sDescription = $aWithDrawel['sendername'] . ' 平台提现成功';
        }
        else 
        { // 设置为 平台提现失败
            $iOrderType   = ORDER_TYPE_PTTXSB;
            $sDescription = $aWithDrawel['sendername'] . ' 平台提现失败';
        }
        $sActionTime = date("Y-m-d H:i:s"); //动作时间
        $this->oDB->doTransaction();  // 2, 开始事务 (第2次)
        //对申请用户执行扣钱操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $aWithDrawel['userid'];
        $aOrders['iToUserId']    = $aWithDrawel['topproxyid'];
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $aWithDrawel['amount'];
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['sDescription'] = $sDescription;
        $aOrders['iAgentId']     = $_SESSION['usertype'] == 2 ? intval( $_SESSION['userid'] ) : 0;
        $result = $oOrder->addOrders( $aOrders );
        if( TRUE !== $result )
        { // 账变发生异常, 事务回滚
            $this->oDB->doRollback();
            $oUserFund->switchLock( $sUserIds, 0, FALSE );
            return $result;
        }
        //如果是成功则对总代执行加钱操作
        if( $iOrderType == ORDER_TYPE_PTTXCG )
        {
        	$aOrders = array();
	        $aOrders['iFromUserId']  = $aWithDrawel['topproxyid'];
            $aOrders['iToUserId']    = $aWithDrawel['userid'];
	        $aOrders['iOrderType']   = ORDER_TYPE_ZDTXCG;
	        $aOrders['fMoney']       = $aWithDrawel['amount'];
	        $aOrders['sActionTime']  = $sActionTime;
	        $aOrders['sDescription'] = $sDescription;
	        $aOrders['iAgentId']     = $_SESSION['usertype'] == 2 ? intval( $_SESSION['userid'] ) : 0;
	        $result = $oOrder->addOrders( $aOrders );
	        if( TRUE !== $result )
	        { // 账变发生异常, 事务回滚
	            $this->oDB->doRollback();
	            $oUserFund->switchLock( $sUserIds, 0, FALSE );
	            return $result;
	        }
        }
        unset($aOrders);
        // STEP 04, 更新提现记录状态
        $aNewWithDrawel['adminid']    = intval($_SESSION['userid']);
        $aNewWithDrawel['status']     = $sType=='SUCCESSED' ? 2 : 1; //1=失败, 2=成功
        $aNewWithDrawel['finishtime'] = date('Y-m-d H:i:s');
        if( $sType == 'SUCCESSED' )
        {// 提现成功, 则更新银行流水号
            //$aNewWithDrawel['bankcode'] = daddslashes(trim($sMessage));
        }
        $this->oDB->update( 'withdrawel', $aNewWithDrawel, " `entry`='$iWithDrawId' AND `status`=0 LIMIT 1" );
        if( !$this->oDB->ar() || $this->oDB->ar() != 1 )
        { // 更新提现申请记录信息
            $this->oDB->doRollback();
            $oUserFund->switchLock( $sUserIds, 0, FALSE );
            return -12;
        }
        unset($aNewWithDrawel);
        $this->oDB->doCommit(); //  第2次事务提交

        // 账户解锁
        if( 2 != $oUserFund->switchLock( $sUserIds, 0, FALSE ) )
        {
            return -11; // 资金账户解锁失败
        }
        $iWithDrawelUserId = intval( $aWithDrawel['userid'] ); // for 消息发送
        unset($aWithDrawel);

        // STEP 05, 站内短消息,通知用户提现申请已处理完成
        $oMessage = new model_message();
        if( $iOrderType == ORDER_TYPE_PTTXCG )
        {
            $aNewMsg['subject'] = '提现成功';
            $aNewMsg['content'] = '提现申请成功。';
        }
        else
        {
            $aNewMsg['subject'] = '提现失败';
            $aNewMsg['content'] = '提现申请失败，请注意查看您的帐变信息。';
            $aNewMsg['content'] .= "\n<br>提现失败原因： ".$sMessage;
        }
        $aNewMsg['receiverid']  = $iWithDrawelUserId;
        $iFlag = $oMessage->sendMessageToUser($aNewMsg);
        return $iFlag > 0 ? TRUE : FALSE;
    }



    /**
     * 用户发起平台提现
     * 
     * @access  public
     * @author  james
     * @param   int     $itopproxyId    总代ID
     * @param   int     $iuserId        用户ID
     * @param   string  $sbankName      银行名称
     * @param   string  $sbankCard      银行卡号
     * @param   string  $sprovince      省份
     * @param   string  $sCity      	城市
     * @param   string  $srealName      用户真实姓名
     * @param   float   $faMount        提现金额
     * @param   string  $sdescription   提现描述
     * @param   int     $iAgentId       总代管理员操作ID
     * @param   int     $iIsForCompany  是否为公司处理
     * @return  int -1:没有指定总代和用户ID
     *              -2:银行信息提供不全
     *              0：资金不对
     *              -3:锁定用户资金帐户失败，表示用户资金表被其他占用
     *              -4:写入提现申请表失败
     *              -5:解除用户资金帐户失败
     *              -6:调用API获取结果失败，可能资金帐户不存在
     *              -7:频道余额小于0
     *              -11:用户ID错误
     *              ......参考orders
     *                  TRUE:提现整个过程成功
     */
    
    public function withdrawelAdd( $iTopProxyId, $iUserId, $BankId, $sBankName, $sBankCard, $sProvince, $sCity,
                                   $sRealName, $fAmount, $sDescription, $iAgentId = 0, $iIsForCompany = 0 )
    {
        if( !( is_numeric($iTopProxyId) && is_numeric($iUserId) ) )
        {//没有指定总代 和用户的ID
            return -1;
        }
        if( empty($sBankName) || empty($sBankCard) || empty($sProvince) || empty($sRealName) || empty($sCity))
        {//银行信息提供不全
            return -2;
        }
        
        $sBankName = daddslashes($sBankName);
        $iBankId = daddslashes($BankId);
        $sBankCard = daddslashes($sBankCard);
        $sProvince = daddslashes($sProvince);
        $sCity 	   = daddslashes($sCity);
        $sRealName = daddslashes($sRealName);
        if( !is_numeric($fAmount) || floatval($fAmount) <= 0 )
        {//资金错误
            return 0;
        }
        $fAmount      = round( floatval($fAmount), 2 );
        $sDescription = empty($sDescription) ? '' : $sDescription;
        $sDescription = daddslashes($sDescription);
        $iAgentId     = intval($iAgentId)>0 ? intval($iAgentId) : 0;
        $iIsForCompany= intval($iIsForCompany) == 1 ? 1 : 0; //是否为公司处理
        //调用API检测其他频道是否为负可用余额
        //获取用户所有可用频道信息
        /* @var $oChannel model_userchannel */
        $oChannel = A::singleton("model_userchannel");
        $aChannel = $oChannel->getUserChannelList( $iUserId );
        if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) )
        {//如果有其他频道
            foreach( $aChannel[0] as $v )
            {//依次获取频道余额
                $oChannelApi = new channelapi( $v['id'], 'getUserCash', FALSE );
                $oChannelApi->setTimeOut(10);            // 设置读取超时时间
                $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
                $oChannelApi->sendRequest( array("iUserId" => $iUserId) );    // 发送结果集
                $aResult = $oChannelApi->getDatas();
                if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
                {//调用API获取结果失败，可能资金帐户不存在
                    return -6;
                }
                if( floatval($aResult['data']) < 0 )
                {//余额小于0
                    return -7;
                }
            }
        }
        
        // 查询用户的类型，普通用户，VIP,黑名单
        $aUserIdentity = array();
        $oCompanyCard = new model_deposit_companycard();
        $oCompanyCard->UserId = $iUserId;
        $aUserIdentity = $oCompanyCard->getCard('get'); // 1为普通用户,2为VIP,3为黑名单
        
        $iIdentiry = 0;
        if (intval($aUserIdentity['isblack']) === 1){ // 黑名单用户
        	$iIdentiry = 3;
        }else if (intval($aUserIdentity['isblack']) === 0 && intval($aUserIdentity['isvip']) === 1 && 
        	$aUserIdentity['vip_expriy'] > date("Y-m-d H:i:s", time())){ // VIP
        	$iIdentiry = 2;
        } else { // 普通用户
        	$iIdentiry = 1;
        }
        
        
        //锁定用户的帐户
        $oUserFund = A::singleton("model_userfund");
        if( intval($oUserFund->switchLock($iUserId, SYS_CHANNELID, TRUE)) != 1 )
        {//锁定用户资金帐户失败
            return -3;
        }
        if( $iTopProxyId == $iUserId )
        {//如果为总代提现，则判断是否能提
            $iLimitMoney = $this->getCreditUserMaxMoney( $iUserId, $fAmount );
            if( $iLimitMoney < $fAmount )
            {//提现金额大于可提现金额(+1000)，保证为大于0的正数
                $oUserFund->switchLock($iUserId, SYS_CHANNELID, FALSE);
                return $iLimitMoney + 1000;
            }
            $iIsForCompany = 1;
        }
        /**************************开始进行提现 ********************/
        $oOrder       = new model_orders();
        $iOrderType   = ORDER_TYPE_PTTXSQ;      //平台提现申请
        $sActionTime  = date("Y-m-d H:i:s");    //动作时间
        //开始事务
        $this->oDB->doTransaction();
        //写帐变，扣钱原子操作
        $aOrders = array();
        $aOrders['iFromUserId']  = $iUserId;
        $aOrders['iToUserId']    = $iTopProxyId;
        if( $iTopProxyId == $iUserId )
        {//总代的商务提现
        	$iOrderType            = ORDER_TYPE_SWTXSQ;      //商务提现申请
        	$aOrders['iToUserId']  = 0;
        }
        $aOrders['iOrderType']   = $iOrderType;
        $aOrders['fMoney']       = $fAmount;
        $aOrders['sActionTime']  = $sActionTime;
        $aOrders['iAgentId']     = $iAgentId;
        $result = $oOrder->addOrders( $aOrders );
        unset($aOrders);
        if( TRUE !== $result )
        {
            $this->oDB->doRollback();
            $oUserFund->switchLock($iUserId, SYS_CHANNELID, FALSE);
            return $result;
        }
        //写入提现申请表
        $sSql = "INSERT INTO `withdrawel` (`topproxyid`,`userid`,`bank_id`,`bankname`,
        `bankcard`,`province`,`city`,`realname`,`amount`,`accepttime`,`bankcode`,`clientip`,
        `proxyip`,`description`,`isforcompany`,`identity`) VALUES('".$iTopProxyId."','".$iUserId."','".$iBankId."','".$sBankName."',
        '".$sBankCard."','".$sProvince."','".$sCity."','".$sRealName."','".$fAmount."','" . date("Y-m-d H:i:s", time()) . "','','".getRealIP()."','".$_SERVER['REMOTE_ADDR']."','".$sDescription."','".$iIsForCompany."',$iIdentiry);";
        $this->oDB->query( $sSql );
        if( $this->oDB->affectedRows() < 1 )
        {//写入提现申请表失败
            $this->oDB->doRollback();
            //解锁资金表
	        if( intval($oUserFund->switchLock($iUserId, SYS_CHANNELID, FALSE)) != 1 )
	        { //解锁用户资金帐户失败
	            return -41;
	        }
            return -4;
        }
        //提交事务
        $this->oDB->doCommit();
        /**************************结束平台提现 ********************/
        if( FALSE == $oUserFund->switchLock($iUserId, SYS_CHANNELID, FALSE) )
        {
            return -5;
        }
        // STEP 04, 站内短消息,通知管理员有人发起提现
        $oMessage = new model_message();
        $aNewMsg['subject'] = '提现申请';
        $aNewMsg['content'] = '有新的提现申请，请处理';
        if( $iIsForCompany == 1 )
        {//商务提现发消息给管理员
        	$oMessage->sendMsgToAdmin($aNewMsg);
        }
        else 
        {//平台提现发消息给总代
        	$aNewMsg['receiverid'] = $iTopProxyId;
        	$oMessage->sendMessageToUser($aNewMsg);
        }
        return TRUE;
    }



    /**
     * 获取支持的银行列表
     * 
     * @access  public
     * @author  james   09/05/17
     * @param   boolean     $isDisabled     //是否获取已被停用的
     * @return  array       //失败返回FALSE，成功返回银行列表
     */
    public function getBankList( $isDisabled = FALSE )
    {
        if( !(bool)$isDisabled )
        {
            $sWhere = " WHERE `status`='0' ";
        }
        else
        {
            $sWhere = "";
        }
        $sSql = " SELECT `bankid`,`bankname` FROM `bankinfo` ".$sWhere;
        $data = $this->oDB->getDataCached( $sSql );
        if( empty($data) )
        {
            return FALSE;
        }
        return $data;
    }



    /**
     * 获取信用用户能不能提一个金额，如果能则返回提现金额，不能则返回最大提现金额
     * 
     * @access  public
     * @author  james   
     * @param   int     $iUserId    //信用用户ID
     * @param   float   $fMoney     //用户提现基准金额
     * @return  float               //返回用户能提的最大金额
     * patch 2/9/2010 
     */
    public function getCreditUserMaxMoney( $iUserId, $fMoney )
    {
        if( empty($iUserId) || empty($fMoney) || !is_numeric($iUserId) )
        {
            return 0;
        }
        $fMoney = floatval( $fMoney );
        //获取用户的可用金额和信用金额
        $sSql = "SELECT uf.`availablebalance`,tps.`proxykey`,tps.`proxyvalue` as credit 
                 FROM `userfund` AS uf LEFT JOIN `topproxyset` AS tps 
                 ON (uf.`userid`=tps.`userid` AND tps.`proxykey`='credit') WHERE uf.`userid`='".$iUserId."'";
        $aTempUserData = $this->oDB->getOne( $sSql );
        if( empty($aTempUserData) )
        {//用户资金帐户不存在
            return 0;
        }
        $aUserData              = array();
        $aUserData['available'] = $aTempUserData['availablebalance'];
        $aUserData['credit']    = empty($aTempUserData['credit']) ? 0 : floatval($aTempUserData['credit']);
        if( empty($aUserData['credit']) )
        {//没有信用资金,则最大是提自己身上的
            return $aUserData['available'];
        }
        /*if( floatval($aUserData['available'] - $aUserData['credit']) >= $fMoney )
        {//如果自身的钱就够，就返回给定金额
            return $fMoney;
        }*/
        //获取团队余额
       
        $oUser = new model_user();
        $aUserData['teamavailable'] = $oUser->getTeamBank( $iUserId );
//		更改开始 2/8/2010;
//		解决2010-2-5总代商务提现,无法提到正常的可提最大金额 问题
// 		获取用户所有频道可用余额(除银行)
		$oAcc = new model_accInfo();
		$aAccResult = $oAcc->getTotalInfo( $iUserId );
		if ($aAccResult == "error"){
			return 'error';
		}
		
		$aUserData['teamavailable'] += $aAccResult['team_abalance'];
//		更改结束
		if( empty($aUserData['teamavailable']) )
        {//团队余额为空
            return 0;
        }
        /*if( floatval($aUserData['teamavailable'] - $aUserData['credit']) >= $fMoney )
        {//团队-信用余额大于给定金额
            return min( $aUserData['available'], $fMoney );
        }
        else 
        {*/
        	$iLimitMoney = min( $aUserData['available'], floatval($aUserData['teamavailable'] - $aUserData['credit']) );
            return $iLimitMoney >= 0 ? $iLimitMoney : 0 ;
        /*}*/
    }
    
    
    
    
    
    
    /**
     * 出纳处理操作
     *
     * @param 		int			$id				记录id
     * @param 		array		$aData			数据
     * 
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-10-12
     * @package 	passportadmin
     * 
     * @return 		mix				false 失败， id	成功
     * 
     */
    public function dealWithdraw( $id, $aData ){
    	// 数据检查
    	if ( empty($aData) ){
    		return false;
}
    	if (!is_numeric($aData['cashier_id']) || $aData['cashier_id'] <= 0 || empty($aData['cashier']) ||
    		 empty($aData['notes']) || !is_numeric($aData['status'])){
    		return false;
    	}
    	
    	$this->oDB->update('withdrawel', $aData, " `entry` = {$id} AND `cashier_id` = 0 AND `status` != {$aData['status']} ");
    	
    	if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
    		return false;
    	} else {
    		return $this->oDB->affectedRows();
    	}
    }
    
    
    
    
    
    /**
     * 出纳员处理时，将记录锁住，防止重复操作
     * 
     * @param 		int			$id					记录id
     * @param 		int			$UserId				出纳员id
     * @param 		string		$UserName			出纳员
     * 
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-10-12
     * @package 	passportadmin
     *
     * @return 		boolean
     * 
     */
    public function lockRecord( $id, $UserId, $UserName ){
    	// 数据检查
    	if (!is_numeric( $id ) || $id <= 0 || !is_numeric( $UserId ) || $UserId <= 0 || empty($UserName)){
    		return false;
    	}
    	
    	$aData = array();
    	$aData['dealing_user_id'] = $UserId;
    	$aData['dealing_user_name'] = $UserName;
    	
    	$this->oDB->update('withdrawel', $aData, " `entry` = {$id} AND (`status` = 0 or `status` = 5 ) AND `dealing_user_id` = 0 ");
    	
    	if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
    		return false;
    	} else {
    		return true;
    	}
    }
    
    
    
    /**
     * 解锁操作
     * 
     * @param 		int			$id					记录id
     * @param 		int			$UserId				出纳员id
     * @param 		string		$UserName			出纳员
     * 
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-10-12
     * @package 	passportadmin
     *
     * @return 		boolean
     * 
     */
    public function unLock( $id, $UserId, $UserName ){
    	// 数据检查
    	if ( !is_numeric( $id ) || $id <= 0 ){
    		return false;
    	}
    	
    	$aData = array();
    	$aData['dealing_user_id'] = 0;
    	$aData['dealing_user_name'] = "";
    	
    	$this->oDB->update('withdrawel', $aData, " `entry` = {$id} AND `status` = 3 AND `dealing_user_id` = {$UserId} ");
    	
    	if ($this->oDB->errno() > 0 || $this->oDB->affectedRows() <= 0){
    		return false;
    	} else {
    		return true;
    	}
    }
    
    
    
    
    
    
    /**
     * 获取用户当天向平台提现的次数
     *
     * @param int $iUserId
     * 
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-10-14
     * @package 	passport
     * 
     * @return 		int
     * 
     */
    public function getCountByUser( $iUserId ){
    	// 数据检查
    	if (!is_numeric($iUserId) || $iUserId <= 0){
    		return 0;
    	}
    	
    	$sSql = "SELECT count(`entry`) as num FROM `withdrawel` WHERE `userid` = {$iUserId} AND `accepttime` BETWEEN '" . date("Y-m-d", time()) . " 00:00:00' AND '" . date("Y-m-d", time()) . " 23:59:59'";
    	$aResult = $this->oDB->getOne($sSql);
    	return $aResult['num'];
    }
}
?>