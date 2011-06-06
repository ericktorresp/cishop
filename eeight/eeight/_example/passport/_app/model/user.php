<?php
/**
 * 用户数据模型
 *
 * 功能：
 *      用于在控制器里根据业务逻辑调用相关的数据模型实现用户的一些操作
 * 
 *      --userLogin          用户登陆
 *      --loginOut           用户注销登陆
 *      --changePassWord     修改登陆密码,资金密码
 *      --isExists           检测用户名是否存在
 *      --insertUser         增加用户
 *      --updateUser         修改用户
 *      --updateUserLevel    修改用户级别
 *      --deleteUser         删除用户[逻辑]
 *      --delete             删除用户[物理]
 *      --getUserInfo        读取用户信息
 *      --getUsersProfile    读取用户基本信息[users+usertree]
 *      --distributeUser     分配用户数额
 *      --getParentId        获取上级ID
 *      --getParent          获取指定用户的上级列表
 *      --getChildrenId      获取指定用户的直接下级或者所有下级
 *      --getChildListID     根据用户ID获取其下的直接下级或者所有下级用户列表（只获取用户名和ID，不分页，不获取总代管理员）
 *      --getChildList       根据用户ID获取其下的直接下级或者所有下级用户列表
 *      --IsAdminSale        判断是否为销售管理员
 *      --isInAdminSale      判断一个用户是否在某销售管理员团队下
 *      --getAdminProxyByUserId 根据总代管理员ID，读取所有分配的一代ID
 *      --getTeamBank        获取指定用户的团队余额
 *      --isTopProxy         判断一个用户是否为总代
 *      --getTopProxyId      获取指定用户的总代ID
 *      --isParent           判断一个用户是否为另外一个用户的上级
 *      --getAdminList       根据总代ID，获取总代管理员列表信息
 *      --getUserExtentdInfo 根据用户ID，获取基本信息，组别等信息
 *      --frozenUser         根据用户ID冻结用户
 *      --unFrozenUser       根据ID解冻用户
 *      --checkSecurityPass  检测资金密码是否正确
 *      --getUserLeftInfo    获取用户登陆后显示在左侧的信息
 *      --checkUserName      检测用户名是否合法
 *      --checkUserPass      检测登陆密码是否合法
 *      --checkNickName      检测呢称是否合法
 *      --getUseridByUsername    根据用户名, 获取用户ID
 *      --getUseridByUsernameArr 根据用户名数组, 获取用户ID
 *      --getActiveUserCount 获取活跃用户数
 *      --getAllUserCount    获取全部用户数
 *      --adminUpdateUserInfo后台管理员更新用户信息
 *      --updateUserRank     更新用户星级
 *      --checkAdminForUser  检测用户和管理员之间的关系
 *      --getRank            根据用户以及需要统计的星级构成数组
 *      --getunActiveUser    获取不活跃用户列表
 *      --getUnderZeroUser   获取负余额用户
 *      --checkUserDomain    根据用户名, 检测其使用的域名是否属于自身总代
 * 		--getGPmainUID 		 获取高频主账户ID
 * 		--checkAuthUserPayment 检查某用户是否有权使用在线充值、提现功能 (仅功能性限制，不检测用户是否能使用某一接口账户) 
 * 		--getAdminTeam 根据总代销售管理员id, 获取总代分配给总代销售管理员的用户团队
 * 
 *  @author    james
 *  @version   1.1.0
 *  @package   passport
 */
class model_user extends basemodel
{
    /* @var $oSession memsession */
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



    /**
     * 用户登陆
     * 
     * @access  public
     * @author  james
     * @param   string  $sUserName  //用户登陆帐号
     * @param   string  $sLoginPWD  //用户登陆密码
     * @return  [mixed] 0:  数据不完整
     *                  -1: 用户名密码不匹配或者不存在该用户
     *                  -2: 用户被逻辑删除
     *                  -3: 用户被冻结并限制登陆
     *                  -4: 更新登陆时间失败
     *                  -5: 更新用户session key值失败
     *                  other:成功登陆,返回用户信息
     */
    public function userLogin( $sUserName, $sUserPass )
    {
        //如果数据不完整则直接返回FALSE
        if( empty($sUserName) || empty($sUserPass) )
        {
            return FALSE;
        }
        // 数据安全处理
        $sUserName     = daddslashes( $sUserName );
        // 构造SQL查询语句 (允许账户密码或资金密码登陆)
        // 4/1/2010 add  ut.`lvtopid`, ut.`lvproxyid`,
        // 5/31/2010 修改新密码验证法
        /*
        $aData = $this->oDB->getOne( "SELECT u.`userid`,u.`username`,u.`usertype`,u.`nickname`,u.`language`, "
                  . "u.`email`,u.`loginpwd`,u.`securitypwd`,ut.`istester`, ut.`lvtopid`, ut.`lvproxyid`, " 
                  . "ut.`isfrozen`, ut.`frozentype`, ut.`isdeleted`,u.`userrank`,u.`lastip`,u.`lasttime` " 
                  . "FROM `users` AS u LEFT JOIN `usertree` AS ut ON u.`userid`=ut.`userid` "
                  . "WHERE u.`username`='". $sUserName ."' " ." AND ( u.`loginpwd`='"
                  . md5($sUserPass) ."' OR u.`securitypwd`='". md5($sUserPass) ."')" );
        */
        $aData = $this->oDB->getOne( "SELECT u.`userid`,u.`username`,u.`usertype`,u.`nickname`,u.`language`, "
                  . "u.`email`,u.`loginpwd`,u.`securitypwd`,ut.`istester`, ut.`lvtopid`, ut.`lvproxyid`, " 
                  . "ut.`isfrozen`, ut.`frozentype`, ut.`isdeleted`,u.`userrank`,u.`lastip`,u.`lasttime` " 
                  . "FROM `users` AS u LEFT JOIN `usertree` AS ut ON u.`userid`=ut.`userid` "
                  . "WHERE u.`username`='". $sUserName ."' LIMIT 1" );
    	
		if( empty($aData) )
        { // 用户不存在
            return -1;
        }
        
        //新计算法密码效验
        if ( 
        (md5(md5($_SESSION['validateCode']).$aData['loginpwd']) != $sUserPass)
        &&
        (md5( md5($_SESSION['validateCode']) . $aData['securitypwd'] ) != $sUserPass)
        )
        {
        	// 用户与密码不匹配
        	return -1;
        }
        
        if( (bool)$aData['isdeleted'] )
        { // 用户是否已经逻辑删除
            return -2;
        }
        if( (bool)$aData['isfrozen'] && intval($aData['frozentype']) == 1  )
        { // 用户已被冻结，并且限制为不能登陆
            return -3;
        }
        // 开始事务
        $this->oDB->doTransaction();
        // 更新登陆IP和时间
        $this->oDB->update( 'users', array('lastip' => getRealIP(), 'lasttime' => date("Y-m-d H:i:s", time())),
                             "userid='".$aData['userid']."'" );
        if( $this->oDB->ar() < 1 )
        { // 更新登陆时间失败
            $this->oDB->doRollback();
            return -4;
        }
        // 更新用户session 表里的 session key 值
        $this->oDB->update( 'usersession', array('sessionkey'=>genSessionKey(),'lasttime'=>date("Y-m-d H:i:s", time())), 
                            "userid='".$aData['userid']."' AND `isadmin`=0" );
        if( $this->oDB->ar() < 1 )
        { // 更新用户session key值失败[没有数据则插入]
            $this->oDB->query( "REPLACE  INTO `usersession` SET `userid`='".$aData['userid']."',
                                `sessionkey`='".genSessionKey()."',`lasttime`='" . date("Y-m-d H:i:s", time()) . "'" );
            if( $this->oDB->ar() < 1 )
            {
                $this->oDB->doRollback();
                return -5;
            }
        }
        $this->oDB->doCommit();
        // 一定几率对 userSession 表进行冗余数据清理
        if( 1 == rand(1,100) )
        {
            $this->oDB->query( "DELETE FROM `usersession` WHERE `lasttime` IS NULL OR `lasttime`<'"
                               .date("Y-m-d H:i:s", strtotime("-15 day"))."' " );
        }
        //把基本信息写入session里
        $_SESSION['userid']     = $aData['userid'];
        $_SESSION['username']   = $aData['username'];
        $_SESSION['usertype']   = $aData['usertype'];
        $_SESSION['nickname']   = $aData['nickname'];
        $_SESSION['language']   = $aData['language'];
        $_SESSION['userrank']   = $aData['userrank'];
        $_SESSION['frozentype'] = $aData['frozentype'];
        $_SESSION['isfrozen']   = $aData['isfrozen'];
        $_SESSION['istester']   = $aData['istester'];
        // 4/1/2010 add 
        $_SESSION['lvtopid']   = $aData['lvtopid'];
        $_SESSION['lvproxyid'] = $aData['lvproxyid'];
        
        if( empty($aData['securitypwd']) )
        {//如果资金密码没有设置，则在安全中心通知用户进行资金密码设置
            $_SESSION['setsecurity'] = "yes";
        }
        return $aData;
    }



    /**
     * 用户注销登陆
     * 	注意：注销登陆会往用户客户端写入cookie，所以，在这之前必须没有任何输出，否则会导致系统错误
     * 
     * @access  public
     * @author  james
     * @return  boolean 成功返回TRUE，失败返回FALSE
     */
    public function loginOut()
    {
        $_SESSION = array();
        session_destroy();
        return TRUE;
    }



    /**
     * 修改登陆密码,资金密码
     * 
     * @access  public
     * @author  james
     * @param   mixed   $mUserId        //要修改的用户ID,或者用户ID数组(批量修改)
     * @param   string  $sLoginPWD      //用户新的登陆密码
     * @param   string  $sSecurityPWD   //用户新的资金密码
     * @return  boolean //成功返回TRUE，失败返回FALSE,密码冲突(登陆密码和资金密码不能相同)返回-1
     */
    public function changePassWord( $mUserId = 0, $sLoginPWD = '', $sSecurityPWD = '' )
    {
        if( empty($sLoginPWD) && empty($sSecurityPWD) )
        {//如果数据不完整则直接返回FALSE
            return FALSE;
        }
        if( empty($mUserId) )
        {//如果用户ID取不到值则直接返回FALSE
            return FALSE;
        }
        if( is_array($mUserId) )
        {//如果为批量修改
            foreach( $mUserId as $k => $v )
            {
                if( empty($v) )
                {//过滤无效数据
                    unset( $mUserId[$k] );
                }
                else
                {
                    $mUserId[$k] = intval($v);
                }
            }
            $sTempWhereSql = " userid IN (". implode(',',$mUserId) .")" ;
        }
        else
        {
            $sTempWhereSql = " userid='". intval( $mUserId ) ."'" ;
        }
        //修改登陆密码和资金密码
        $aData = array();
        if( !empty($sLoginPWD) )
        {
            $aData['loginpwd'] = md5($sLoginPWD);
            //检测登陆密码是否和资金密码一样，如果一样则退出，不能修改
            $sSql = "SELECT `userid` FROM `users` WHERE `securitypwd`='".$aData['loginpwd']."' AND ".$sTempWhereSql;
            $this->oDB->query( $sSql );
            $iNums = $this->oDB->ar();
            if( $iNums > 0 )
            {
                return -1;
            }
        }
        if( !empty($sSecurityPWD) )
        {
            $aData['securitypwd'] = md5($sSecurityPWD);
            //检测登陆密码是否和资金密码一样，如果一样则退出，不能修改
            $sSql = "SELECT `userid` FROM `users` WHERE `loginpwd`='".$aData['securitypwd']."' AND ".$sTempWhereSql;
            $this->oDB->query( $sSql );
            $iNums = $this->oDB->ar();
            if( $iNums > 0 )
            {
                return -1;
            }
        }
        $this->oDB->doTransaction();
        $iResult = $this->oDB->update( 'users', $aData, $sTempWhereSql );
        if( $this->oDB->errno() > 0)
        {//更新低频密码失败
            $this->oDB->doRollback();
            return -2;
        }
        //临时同步更新高频用户信息代码开始
        /*if( is_array($mUserId) )
        {//如果为批量修改
            foreach( $mUserId as $k => $v )
            {
                if( empty($v) )
                {//过滤无效数据
                    unset( $mUserId[$k] );
                }
                else
                {
                    $mUserId[$k] = intval($v);
                }
            }
            $mUserId = implode(',',$mUserId);
        }
        else
        {
            $mUserId = intval($mUserId);
        }
        if( is_array($mUserId) )
        {
            $sSql = " SELECT `gpuserid` FROM `tempusermap` WHERE `dpuserid` IN (" . $mUserId .")";
        }
        else
        {
            $sSql = " SELECT `gpuserid` FROM `tempusermap` WHERE `dpuserid` = '" . $mUserId ."'";
        }
        $aTempUserMap = $this->oDB->getAll( $sSql );
        if( empty($aTempUserMap) )
        {
            if( is_array($mUserId) )
            {
                $sCondtion = "`id` IN (" . $mUserId. ")";
            }
            else
            {
                $sCondtion = "`id` = '" . $mUserId. "'";
            }
        }
        else
        {
            $aGpUserId = array();
            $sUserId = '';
            foreach ($aTempUserMap as $aUserId )
            {
                $aGpUserId[] = $aUserId['gpuserid'];
            }
            $sUserId = implode(',',$aGpUserId);
            $sCondtion = "`id` IN (" . $sUserId. ")";
        }
        if( isset($aData['loginpwd']) && !empty($aData['loginpwd']) )
        {
            $sGpSql = "UPDATE `users` SET `pwd` = '" .$aData['loginpwd'] . "' WHERE " . $sCondtion;
        }
        elseif( isset($aData['securitypwd']) && !empty($aData['securitypwd']) ) 
        {
            $sGpSql = "UPDATE `users` SET `fpwd` = '" .$aData['securitypwd'] . "' WHERE " . $sCondtion;
        }
        $oDB = new db($GLOBALS['aSysDbServer']['gaopin']);
        $oDB->query( $sGpSql );
        if( $oDB->errno() > 0 )
        {//更新高频密码失败
            $this->oDB->doRollback();
            return -2;
        }*/
        //临时同步更新高频用户信息代码结束
        $this->oDB->commit();
        return $iResult;
    }



    /**
     * 检测用户名是否存在
     * 
     * @access  public
     * @author  james
     * @param   string  $sUserName  //被检测的用户名
     * @return  boolean //存在返回TRUE，不存在返回FALSE
     */
    public function isExists( $sUserName )
    {
        if( empty($sUserName) )
        {//数据为空则返回存在
            return TRUE;
        }
        $sLimitUserName = getConfigValue("usernamelimit","");
        if( !empty($sLimitUserName) )
        {
	        $aLimitUserName = explode(",",$sLimitUserName);
	        foreach ( $aLimitUserName as $sLimitUser )
	        {
	            if( ereg($sLimitUser, $sUserName) )//检测受限制的用户名规则
	            {
	                return TRUE;
	            }
	        }
        }
        $sSql = "SELECT `userid` FROM `users` WHERE `username`='". daddslashes($sUserName) ."'";
        $this->oDB->query( $sSql );
        $iNums = $this->oDB->ar();
        if( $iNums > 0 )
        {//存在用户
            return TRUE;
        }
        return FALSE;
    }



    /**
     * 增加用户
     * 
     * @access  public
     * @author  james
     * @param   array   $aUserInfo  //用户信息数组，键名与数据库字段名对应
     * @param   int     $iGroupId   //用户组ID
     * @param   int     $iParentId  //用户上级ID
     * @param   boolean $bAddFund   //增加用户的同时，是否需要向资金表里增加记录，默认为TRUE
     * @return  mixd    //成功返回插入的用户ID，失败返回FALSE，同名帐户存在返回-1
     */
    public function insertUser( $aUserInfo, $iGroupId, $iParentId = 0, $bAddFund = TRUE, $iIsTester=0 )
    {
        //数据合法性检测
        if( empty($aUserInfo) && !is_array($aUserInfo) || empty($iGroupId) 
            || !is_numeric($iGroupId) || !is_numeric($iParentId) )
        {//基本数据检查
            return FALSE;
        }
        //必要数据检测
        if( empty($aUserInfo['username']) || empty($aUserInfo['loginpwd']) )
        {
            return FALSE;
        }
        $aUserInfo['loginpwd'] = md5($aUserInfo['loginpwd']);
        if( !isset($aUserInfo["usertype"]) || !is_numeric($aUserInfo["usertype"]) )
        {
            return FALSE;
        }
        $aUserInfo['usertype'] = intval($aUserInfo['usertype']);
        //数据修复以及填充
        if( !empty($aUserInfo['nickname']) )
        {
            $aUserInfo['nickname'] = daddslashes($aUserInfo['nickname']);
        }
        else 
        {
        	$aUserInfo['nickname'] = daddslashes($aUserInfo['username']);
        }
        if( !isset($aUserInfo['authadd']) || $aUserInfo['authadd'] != 1 || $aUserInfo['authadd'] != 0 )
        {//默认有开户权限
            $aUserInfo['authadd']   = 1;
        }
        $aUserInfo['securitypwd']   = empty($aUserInfo['securitypwd']) ? '' : md5($aUserInfo['securitypwd']);
        $aUserInfo['lastip']        = empty($aUserInfo['lastip']) ? '0.0.0.0' : $aUserInfo['lastip'];
        $aUserInfo['lasttime']      = empty($aUserInfo['lasttime']) ? '1970-01-01 00:00:00' : $aUserInfo['lasttime'];
        $aUserInfo['registerip']    = empty($aUserInfo['registerip']) ? getRealIP() : $aUserInfo['registerip'];
        $aUserInfo['registertime']  = date("Y-m-d H:i:s", time());
        //检测用户名是否已经存在
        if( $this->isExists($aUserInfo['username']) )
        {
            return -1;
        }
        $aUserInfo['username'] = daddslashes($aUserInfo['username']);
        $iGroupId   = intval($iGroupId);
        $iParentId  = intval( $iParentId );
        $iLvTopId   = 0;	//初始化总代ID
        $iLvProxyId = 0;	//初始化一代ID
        $iIsTester  = intval($iIsTester) > 0 ? 1 : 0;    //测试帐户
        if( $iParentId != 0 )
        {//获取上级的父亲树
            $aTempData = $this->oDB->getOne( "SELECT `parenttree`,`istester` FROM `usertree` 
                                              WHERE `userid`='".$iParentId."'" );
            if( empty($aTempData) )
            {
                return FALSE;
            }
            $iIsTester = intval($aTempData['istester']);
            if( $aTempData['parenttree'] == '' )
            {
                $sTempTree = $iParentId;
            }
            else 
            {
                $sTempTree = $aTempData['parenttree'].','.$iParentId;
            }
            $temp_aArr  = explode(",",$sTempTree);
            $iLvTopId   = empty($temp_aArr[0]) ? 0 : intval($temp_aArr[0]);
            $iLvProxyId = empty($temp_aArr[1]) ? 0 : intval($temp_aArr[1]);
            unset($temp_aArr);
        }
        else
        {
            $sTempTree = '';
        }
        //启用事务往user表和usertree表里以及userchannel表写数据
        $this->oDB->doTransaction();
        if( $iParentId != 0 && $aUserInfo["usertype"] != 2 )
        {//如果不为开设总代并且不为开设总代管理员则扣减用户开户数额
            $sSql = "UPDATE `users` SET `addcount`=`addcount`-1 WHERE `userid`='".$iParentId."' 
                     AND `addcount`>'0'";
            $this->oDB->query( $sSql );
            if( $this->oDB->affectedRows() < 1 )
            {//扣减用户开户数额失败
                $this->oDB->doRollback();
                return FALSE;
            }
        }
        if( FALSE == ($iTempUid = $this->oDB->insert('users', $aUserInfo)) )
        {//增加失败
            $this->oDB->doRollback();
            return FALSE;
        }
        if( $iLvTopId == 0 )
        {
            $iLvTopId = $iTempUid;
        }
        elseif( $iLvProxyId == 0 )
        {
            $iLvProxyId = $iTempUid;
        }
        if( $aUserInfo["usertype"] == 2 )
        {//如果为总代管理员则总代ID和一代ID都记录0
            $iLvTopId   = 0;
            $iLvProxyId = 0;
        }
        $aTempData = array( 
                     "userid"    => $iTempUid, 
                     "username"  => $aUserInfo['username'],
                     "nickname"  => empty($aUserInfo['nickname']) ? $aUserInfo['username'] : $aUserInfo['nickname'],
                     "usertype"  => $aUserInfo["usertype"],
                     "lvtopid"   => $iLvTopId,
                     "lvproxyid" => $iLvProxyId,
                     "parentid"  => $iParentId,
                     "parenttree"=> $sTempTree,
                     "userrank"  => 0,
                     "istester"  => $iIsTester
                         );
        $this->oDB->insert( 'usertree', $aTempData );
        if( $this->oDB->affectedRows() < 1 )
        {//写入usertree表失败
            $this->oDB->doRollback();
            return FALSE;
        }
        //往用户session key表里写入记录
        $aData = array( 'userid' => $iTempUid, 'sessionkey' => '', 'isadmin' => 0 );
        $this->oDB->insert( 'usersession', $aData );
        if( $this->oDB->affectedRows() < 1 )
        {//写入session key表失败
            $this->oDB->doRollback();
            return FALSE;
        }
        /** temp_louis **/
        // 如果是总代管理员新增用户，那么新用户则默认分配给此总代销售管理员
        if( $_SESSION['usertype'] == 2 && TRUE == $this->IsAdminSale($_SESSION['userid'])){
        	$oUserAdmin = new model_useradminproxy();
            if( FALSE == ($oUserAdmin->insert($_SESSION['userid'], $iTempUid)) )
            {
                $this->oDB->doRollback();
            	return FALSE;
            }
        }
        /** temp_louis **/
        //往userchannel表里写入数据
        $aData = array( 'userid' => $iTempUid, 'channelid' => 0, 'groupid' => $iGroupId );
        $this->oDB->insert( 'userchannel', $aData );
        if( $this->oDB->affectedRows() < 1 )
        {//写入userchannel表失败
            $this->oDB->doRollback();
            return FALSE;
        }
        /** temp_louis **/
        if( $aUserInfo["usertype"] == 2 )
        {//如果为总代管理员则向userchannel表中写入所有平台的记录
        	$sSql = "SELECT channelid,isdisabled FROM userchannel WHERE userid = {$iParentId}";
        	$aResult = $this->oDB->getAll($sSql);
	        if( !empty($aResult))
	        {//如果有其他频道
	            foreach( $aResult as $v ){
	            	if ($v['channelid'] == SYS_CHANNELID) continue;
	            	$aData = array( 'userid' => $iTempUid, 'channelid' => $v['channelid'],'isdisabled' => $v['isdisabled'], 'groupid' => $iGroupId );
	            	$this->oDB->insert( 'userchannel', $aData );
	            	
			        if( $this->oDB->affectedRows() < 1 )
			        {//写入userchannel表失败
			            $this->oDB->doRollback();
			            return FALSE;
			        }
	            }
	        }
        }
        /** temp_louis **/
        if( (bool)$bAddFund )
        {//往资金记录表里增加记录
            $aData = array( 'userid' => $iTempUid, 'channelid' => 0, 'lastactivetime' => date("Y-m-d H:i:s", time()), 'lastupdatetime' => date('Y-m-d H:i:s')  );
            $this->oDB->insert( 'userfund', $aData );
            if( $this->oDB->affectedRows() < 1 )
            {//往userfund表里写记录失败
                $this->oDB->doRollback();
                return FALSE;
            }
        }
        if( $iParentId == 0 )
        {//如果为总代，则往总代设置表里增加初始数据[topproxyset]
            $sSql = "INSERT INTO `topproxyset`(`userid`,`proxykey`,`proxyvalue`)"
                     ."values('".$iTempUid."','credit','0.00'),('".$iTempUid."','open_level','0'),"
                     ."('".$iTempUid."','can_create','1')";
            $this->oDB->query( $sSql );
            if( $this->oDB->affectedRows() < 3 )
            {//往topproxyset表里写记录失败
                $this->oDB->doRollback();
                return FALSE;
            }
        }
        
        /***  银行充值(EMAIL充值) 所需部分 开始  ***/
        // 获取系统可用的所有受付银行ID
        $oDeposit = new model_deposit_depositlist(array(),'','array');
    	$aBankId  = $oDeposit->getDepositArray();
//    	$this->oDB->doRollback();
    	
        $oDepositUser = new model_deposit_companycard();
        
    	//根据受付银行个数，循环插入全部对应的数据
    	// 基本信息写入 user_deposit_card
        foreach ($aBankId as $iKey => $iValueId)
        {
        	//除 总代管理员 开始写 用户＆卡关系表
        	if( $aUserInfo["usertype"] != 2 )
        	{
        		//声明受付银行ID
          	$oDepositUser->BankId = $iValueId;
          	$aPoroxyInfo = array();
	        // 获取上级用户的基本配置卡,当有上级用户时
    	    if ( $iLvProxyId > 0 )
        	{
        		$oDepositUser->UserId 	= ($iParentId != '') ? $iParentId : $iLvProxyId;
        		$aPoroxyInfo 			= $oDepositUser->getCard('get');
        	}
        	else 
        	{
        		// 新开总代:预设值为空,默认禁用
        		$aPoroxyInfo['user_level'] 	= -1;
        		$aPoroxyInfo['deposit_name']= $aPoroxyInfo['deposit_mail'] = '';
        		$aPoroxyInfo['payacc_id'] 	= $aPoroxyInfo['isactive'] = 0;
        	}
        
	        // 判断新增用户 用户＆卡关系 激活项的默认设置  ( 在“授权在线充提”许可层级内 则 “继承上级”)
    	    $iNewUserLevel 	= $aPoroxyInfo['user_level']+1;
        	$iAllowLevel 	= $this->getDepositAllowLv($iLvTopId, $iValueId);
        	
        	if ( $iNewUserLevel > $iAllowLevel )
        	{
        		$iIsActiveh = 0;
        	}
        	else 
        	{
        		$iIsActiveh = $aPoroxyInfo['isactive'];
        	}
        	
        	// 赋值新用户所需各项
        	$aNewDepositUserData =  array();
			$aNewDepositUserData = array(
        		'bankid'		=> $iValueId,						//受付银行ID
	 			'userid' 		=> $iTempUid,						//用户ID
	 			'username' 		=> $aUserInfo['username'],			//用户登录名
	 			'usertree' 		=> $sTempTree,						//用户树
	 			'userlevel' 	=> $iNewUserLevel,					//用户层级
        		'usertype' 		=> $aUserInfo["usertype"],			//用户类型
        		'topagentid' 	=> $iLvTopId,						//用户所属总代ID
	 			'agentid' 	 	=> $iParentId,						//用户上级代理ID
        		'accname'		=> $aPoroxyInfo['accname'],			//普通卡财务名
	 			'depositname'	=> $aPoroxyInfo['deposit_name'],	//普通用卡户名
	 			'depositmail'	=> $aPoroxyInfo['deposit_mail'],	//普通用卡MAIL名
	 			'payaccid'		=> $aPoroxyInfo['payacc_id'],		//普通用卡ID(支付接口分账户ID)
	 			'isactive' 		=> $iIsActiveh						//状态
	  			);
//	  		print_rr( $aNewDepositUserData,1,1);
        	$aRusultDep = $oDepositUser->addSingleUser($aNewDepositUserData);
        	if ( $aRusultDep === FALSE )
        	{
        		$this->oDB->doRollback();
        		return FALSE;
        	}
        	
        	} //end 总代管理员判断
        	
    	} //end foreach $aBankId;
    	/***  受付银行涉及 银行充值 快速充值 所需部分 结束  ***/
    	
         //临时低频开户与高频同步代码
        /*if( $iParentId == 0 )
        {
            $aAddTmpUserData = array(
                'gpuserid'      => $iTempUid,
                'gpusername'    => $aUserInfo['username'],
                'dpuserid'      => $iTempUid,
                'dpusername'    => $aUserInfo['username'],
                'dpuserdomain'  => '',
                'gpuserdomain'  => '',
                'status'        => 1
            );
            $this->oDB->insert('tempusermap',$aAddTmpUserData);
            if( $this->oDB->affectedRows() < 1 )
            {//往临时表里写记录失败
                $this->oDB->doRollback();
                return FALSE;
            }
        }*/
         //临时低频开户与高频同步代码结束
        $this->oDB->doCommit();	//提交事务
        //临时低频开户与高频同步代码
        /*if( $aUserInfo["usertype"] == 2 )
        {
            return $iTempUid;
        }
        if( $iLvProxyId == $iTempUid )//如果为一代,处理父ID可能是合并总代的ID
        {
            $sSql = " SELECT `gpuserid` FROM  `tempusermap` WHERE `dpuserid` = '" . $iParentId . "' AND `status` = 1";
            $aTmpUserMap = $this->oDB->getOne( $sSql );
            if( !empty($aTmpUserMap) )
            {
                $iParentId = $aTmpUserMap['gpuserid'];
            }
        }
        $aUserSyncData = array(
                         'iUserId'       => $iTempUid,
                         'sUserName'     => $aUserInfo['username'],
                         'sNickName'     => empty($aUserInfo['nickname']) ? $aUserInfo['username'] : urlencode($aUserInfo['nickname']),
                         'sPassword'     => $aUserInfo['loginpwd'],
                         'iParentId'     => $iParentId,
                         'sFundPassword' => $aUserInfo['securitypwd'],
                         'iUserType'     => $aUserInfo['usertype'],
                         'iBLimit'       => 0
        );
        $oChannelApi = new channelapi( 99, 'interfaceuser', TRUE );
        $oChannelApi->setTimeOut(15);            // 设置读取超时时间
        $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
        $oChannelApi->sendRequest( $aUserSyncData );    // 发送结果集
        $aResult = $oChannelApi->getDatas();
        if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
        {//同步开户失败,记录同步失败的用户
            $aErrorUserData = array( 'userid' => $iTempUid, 'username' => $aUserSyncData['sUserName'] );
            $this->oDB->insert( 'tmperroruser', $aErrorUserData );
            if( $aResult['data'] == '1956' )
            {
                return -1002;
            }
            return -1001;
        }*/
        //临时低频开户与高频同步代码结束
        return $iTempUid;
    }



    /**
     * 增加用户 TODO _a高频、低频并行前期临时程序 
     *
     * @param int    $iUserId
     * @param int    $iParentId
     * @param string $sUserName
     * @param string $sPassword
     * @param string $sFundPassword
     * @param int    $iUserType
     * @return unknown
     */
    /*public function gdInsertUser( $iUserId, $iParentId, $sUserName, $sPassword, $sFundPassword, $iUserType, $sNickName )
    {
    	//01: 检测并初始化数据
    	$aUsers = array();
    	if( empty($iUserId) || !is_numeric($iUserId) || $iUserId <= 0 )
    	{
    		return -1;
    	}
    	$aUsers['userid'] = intval($iUserId);
        if( !is_numeric($iParentId) || $iParentId < 0 )
        {
            return -1;
        }
        $iParentId = intval($iParentId);
        if( empty($sUserName) || empty($sPassword) )//|| empty($sFundPassword)
        {
        	return -1;
        }
        $aUsers['username']     = daddslashes($sUserName);
        $aUsers['loginpwd']     = daddslashes($sPassword);
        $aUsers['securitypwd']  = daddslashes($sFundPassword);
        if( empty($iUserType) || !is_numeric($iUserType) || !in_array($iUserType, array(4,5)) )
        {
            return -1;
        }
        $aUsers['usertype']      = intval($iUserType) == 4 ? 1 : 0;
        $aUsers['nickname']      = daddslashes($sNickName);
        $aUsers['lastip']        = '0.0.0.0';
        $aUsers['lasttime']      = '1970-01-01 00:00:00';
        $aUsers['registerip']    = getRealIP();
        $aUsers['registertime']  = 'now()';
        //02: 检测用户名是否已经存在
        if( $this->isExists($sUserName) )
        {
        	$sSql = "SELECT `userid`,`usertype`,`lvtopid`,`lvproxyid`,`parentid` 
                     FROM `usertree` WHERE `username`='". daddslashes($sUserName) ."' ";
        	$aSelfData = $this->oDB->getOne( $sSql );
        	if( empty($aSelfData) || $aSelfData['userid'] != $iUserId )
        	{//用户不匹配
        		return -2;
        	}
        	if( $aSelfData['parentid'] != 0 && $aSelfData['usertype'] != 2 
        	     && $aSelfData['lvproxyid'] > 0 && $aSelfData['lvproxyid'] != $iUserId )
        	{//符合条件则同步激活低频
	        	//调用API激活低频
	            $oChannelApi = new channelapi( 1, 'activeUserChannel', TRUE );
	            $oChannelApi->setTimeOut(10);            // 设置读取超时时间
	            $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
	            $oChannelApi->sendRequest( array("userid"=>$iUserId) );    // 发送结果集
	            $aResult = $oChannelApi->getDatas();
	            if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
	            {//调用API激活失败
	                return -11;
	            }
        	}
        	return TRUE;
        }
        //03: 获取上级的父亲树
        $iGroupId   = 0;    //初始化用户组
        $iLvTopId   = 0;    //初始化总代ID
        $iLvProxyId = 0;    //初始化一代ID
        $iIsTester  = 0;    //测试帐户
        if( $iParentId != 0 )
        {//如果不为增加总代，获取上级的父亲树
        	//03 01: 查询父ID是否为总代ID
        	$aTempData = $this->oDB->getOne("SELECT `dpuserid` FROM `tempusermap` WHERE `gpuserid`='".$iParentId."'");
        	if( $this->oDB->errno() > 0 )
        	{
        		return -5000;
        	}
        	if( !empty($aTempData) )
        	{//如果为总代,父ID调整到低频的真实父ID
        		$iParentId = $aTempData['dpuserid'];
        	}
            $aTempData = $this->oDB->getOne( "SELECT `parenttree`,`istester` FROM `usertree` 
                                              WHERE `userid`='".$iParentId."'" );
            if( empty($aTempData) )
            {
                return -3;
            }
            $iIsTester = intval($aTempData['istester']);
            if( $aTempData['parenttree'] == '' )
            {
                $sTempTree = $iParentId;
            }
            else 
            {
                $sTempTree = $aTempData['parenttree'].','.$iParentId;
            }
            $temp_aArr  = explode(",",$sTempTree);
            $iLvTopId   = empty($temp_aArr[0]) ? 0 : intval($temp_aArr[0]);
            $iLvProxyId = empty($temp_aArr[1]) ? 0 : intval($temp_aArr[1]);
            unset($temp_aArr);
        }
        else
        {
            $sTempTree = '';
        }
        //启用事务往user表和usertree表里以及userchannel表写数据
        $this->oDB->doTransaction();
        if( FALSE == ($iTempUid = $this->oDB->insert('users', $aUsers)) )
        {//增加失败
            $this->oDB->doRollback();
            return -4;
        }
        if( $iLvTopId == 0 )
        {//增加总代
            $iLvTopId = $iTempUid;
            $iGroupId = 1;
            
            $aData = array(
                            "gpuserid"      => $iTempUid,
                            "gpusername"    => $aUsers['username'],
                            "dpuserid"      => $iTempUid,
                            "dpusername"    => $aUsers['username'],
                            "status"        => 1,
                            "dpuserdomain"  => "",
                            "gpuserdomain"  => ""
                     );
	        if( FALSE == $this->oDB->insert('tempusermap', $aData) )
	        {//增加失败
	            $this->oDB->doRollback();
	            return -10;
	        }
        }
        elseif( $iLvProxyId == 0 )
        {//增加一代或者用户
            $iLvProxyId = $iTempUid;
            $iGroupId   = $aUsers["usertype"] == 1 ? 2 : 4;
        }
        else
        {//普代或者用户
        	$iGroupId   = $aUsers["usertype"] == 1 ? 3 : 4;
        }
        if( $aUsers["usertype"] == 2 )
        {//如果为总代管理员则总代ID和一代ID都记录0
            $iLvTopId   = 0;
            $iLvProxyId = 0;
        }
        $aTempData = array( "userid"    => $iTempUid, 
                            "username"  => $aUsers['username'],
                            "nickname"  => $aUsers['nickname'],
                            "usertype"  => $aUsers["usertype"],
                            "lvtopid"   => $iLvTopId,
                            "lvproxyid" => $iLvProxyId,
                            "parentid"  => $iParentId,
                            "parenttree"=> $sTempTree,
                            "userrank"  => 0,
                            "istester"  => $iIsTester
                         );
        $this->oDB->insert( 'usertree', $aTempData );
        if( $this->oDB->affectedRows() < 1 )
        {//写入usertree表失败
            $this->oDB->doRollback();
            return -5;
        }
        //往用户session key表里写入记录
        $aData = array( 'userid' => $iTempUid, 'sessionkey' => '', 'isadmin' => 0 );
        $this->oDB->insert( 'usersession', $aData );
        if( $this->oDB->affectedRows() < 1 )
        {//写入session key表失败
            $this->oDB->doRollback();
            return -6;
        }
        //往userchannel表里写入数据
        $aData = array( 'userid' => $iTempUid, 'channelid' => 0, 'groupid' => $iGroupId );
        $this->oDB->insert( 'userchannel', $aData );
        if( $this->oDB->affectedRows() < 1 )
        {//写入userchannel表失败
            $this->oDB->doRollback();
            return -7;
        }
        $aData = array( 'userid' => $iTempUid, 'channelid' => 0, 'lastactivetime' => "now()" );
        $this->oDB->insert( 'userfund', $aData );
        if( $this->oDB->affectedRows() < 1 )
        {//往userfund表里写记录失败
            $this->oDB->doRollback();
            return -8;
        }
        if( $iParentId == 0 )
        {//如果为总代，则往总代设置表里增加初始数据[topproxyset]
            $sSql = "INSERT INTO `topproxyset`(`userid`,`proxykey`,`proxyvalue`)"
                     ."values('".$iTempUid."','credit','0.00'),('".$iTempUid."','open_level','0'),"
                     ."('".$iTempUid."','can_create','1')";
            $this->oDB->query( $sSql );
            if( $this->oDB->affectedRows() < 3 )
            {//往topproxyset表里写记录失败
                $this->oDB->doRollback();
                return -9;
            }
        }
        $this->oDB->doCommit(); //提交事务
        if( $aTempData['parentid'] != 0 && $aTempData['usertype'] != 2 
            && $aTempData['lvproxyid'] > 0 && $aTempData['lvproxyid'] != $iUserId )
        {//符合条件则同步激活低频
            //调用API激活低频
            $oChannelApi = new channelapi( 1, 'activeUserChannel', TRUE );
            $oChannelApi->setTimeOut(10);            // 设置读取超时时间
            $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
            $oChannelApi->sendRequest( array("userid"=>$iUserId) );    // 发送结果集
            $aResult = $oChannelApi->getDatas();
            if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
            {//调用API激活失败
                return -11;
            }
        }
        return TRUE;
    }*/



    /**
     * 用户登陆  TODO _a高频、低频并行前期临时程序 
     *
     * @param int    $iUserId
     * @param string $sUserName
     * @param string $sPassword
     * @return mixed
     */
    /*public function gdUserLogin( $iUserId, $sUserName, $sPassword )
    {
    	//00: 检测数据
        if( empty($iUserId) || !is_numeric($iUserId) || empty($sUserName) || empty($sPassword) )
        {
            return 0;
        }
        $iUserId   = intval($iUserId);
        $sUserName = daddslashes($sUserName);
        $sPassword = daddslashes($sPassword);
        //00:检查是否为总代，如果为总代则自动转换到低频想对应的用户名
        $sSql = " SELECT * FROM `tempusermap` WHERE `gpuserid`='".$iUserId."' AND `gpusername`='".$sUserName."' LIMIT 1 ";
        $aTemp = $this->oDB->getOne( $sSql );
        if( !empty($aTemp) )
        {
        	$iUserId = intval($aTemp['dpuserid']);
        	$sUserName = daddslashes($aTemp['dpusername']);
        }
        //01: 检查域名
        if( FALSE === $this->checkUserDomain($sUserName, isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']) )
        {//非法域名
            return -6;
        }
        //02: // 构造SQL查询语句 (允许账户密码或资金密码登陆)
        $aData = $this->oDB->getOne( "SELECT u.`userid`,u.`username`,u.`usertype`,u.`nickname`,u.`language`, "
                  . "u.`email`,u.`loginpwd`,u.`securitypwd`,ut.`istester`, " 
                  . "ut.`isfrozen`, ut.`frozentype`, ut.`isdeleted`,u.`userrank`,u.`lastip`,u.`lasttime` " 
                  . "FROM `users` AS u LEFT JOIN `usertree` AS ut ON u.`userid`=ut.`userid` "
                  . "WHERE u.`username`='". $sUserName ."' AND u.`userid`='".$iUserId."' AND ( u.`loginpwd`='"
                  . $sPassword ."' OR u.`securitypwd`='". $sPassword ."')" );
        if( empty($aData) )
        { // 用户与密码不匹配，或者用户不存在
            return -1;
        }
        if( (bool)$aData['isdeleted'] )
        { // 用户是否已经逻辑删除
            return -2;
        }
        if( (bool)$aData['isfrozen'] && intval($aData['frozentype']) == 1  )
        { // 用户已被冻结，并且限制为不能登陆
            return -3;
        }
        // 开始事务
        $this->oDB->doTransaction();
        // 更新登陆IP和时间
        $this->oDB->update( 'users', array('lastip' => getRealIP(), 'lasttime' => 'now()'),
                             "userid='".$aData['userid']."'" );
        if( $this->oDB->ar() < 1 )
        { // 更新登陆时间失败
            $this->oDB->doRollback();
            return -4;
        }
        // 更新用户session 表里的 session key 值
        $this->oDB->update( 'usersession', array('sessionkey'=>genSessionKey(),'lasttime'=>'now()'), 
                            "userid='".$aData['userid']."' AND `isadmin`=0" );
        if( $this->oDB->ar() < 1 )
        { // 更新用户session key值失败[没有数据则插入]
            $this->oDB->query( "REPLACE  INTO `usersession` SET `userid`='".$aData['userid']."',
                                `sessionkey`='".genSessionKey()."',`lasttime`=now()" );
            if( $this->oDB->ar() < 1 )
            {
                $this->oDB->doRollback();
                return -5;
            }
        }
        $this->oDB->doCommit();
        // 一定几率对 userSession 表进行冗余数据清理
        if( 1 == rand(1,100) )
        {
            $this->oDB->query( "DELETE FROM `usersession` WHERE `lasttime` IS NULL OR `lasttime`<'"
                               .date("Y-m-d H:i:s", strtotime("-15 day"))."' " );
        }
        //把基本信息写入session里
        $_SESSION['userid']     = $aData['userid'];
        $_SESSION['username']   = $aData['username'];
        $_SESSION['usertype']   = $aData['usertype'];
        $_SESSION['nickname']   = $aData['nickname'];
        $_SESSION['language']   = $aData['language'];
        $_SESSION['userrank']   = $aData['userrank'];
        $_SESSION['frozentype'] = $aData['frozentype'];
        $_SESSION['isfrozen']   = $aData['isfrozen'];
        $_SESSION['istester']   = $aData['istester'];
        if( empty($aData['securitypwd']) )
        {//如果资金密码没有设置，则在安全中心通知用户进行资金密码设置
            $_SESSION['setsecurity'] = "yes";
        }
        // 写登陆日志
        // @var $oUserLog model_userlog 
        $oUserLog = A::singleton('model_userlog');
        $aLogdata = array(
                        'userid'        => $aData['userid'],
                        'controller'    => 'user',
                        'actioner'      => 'login',
                        'title'         => '用户 ['.$aData['username'].'] 成功登陆',
                        'content'       => '用户 ['.$aData['username'].'] 成功登陆'
        );
        $oUserLog->insert( $aLogdata );
        unset($oUserLog);
        //检查是什么方式登陆[正常登陆，资金密码登陆]
        if( $aData['loginpwd'] == $sPassword )
        {//正常登陆
            $_SESSION['logintype'] = 'normal';
        }
        else
        {//资金密码登陆
            $_SESSION['logintype'] = 'security';
        }
        // @var $oUserSkin model_userskins 
        $oUserSkin         = A::singleton('model_userskins');
        $_SESSION['skins'] = $oUserSkin->getSkinByUserId( $aData['userid'], $aData['usertype']==2 ? TRUE : FALSE );
        unset($oUserSkin);
        return TRUE;
    }*/



    /**
     * 用户登出 TODO _a高频、低频并行前期临时程序 
     *
     * @param int $iUserId
     * @return boolean
     */
    /*public function gdUserLogout( )
    {
    	$_SESSION = array();
        session_destroy();
        return TRUE;
    }*/

    
    /**
     * 查询tempusermap表 TODO _a高频、低频并行前期临时程序 
     */
    /*public function gdGetTempUserMap( $sFileds='', $sCondition='', $bIsMore=FALSE )
    {
    	$sFileds = empty($sFileds) ? '*' : $sFileds;
    	$sSql    = " SELECT ".$sFileds." FROM `tempusermap` WHERE 1 ".$sCondition;
    	if( $bIsMore )
        {
            return $this->oDB->getAll( $sSql );
        }
        return $this->oDB->getOne( $sSql );
    }*/





    /**
     * 修改用户
     * 
     * @access  public
     * @author  james
     * @param   mixed   $mUserId    //要修改的用户ID,或者用户ID数组(批量修改)
     * @param   array   $aUserInfo  //要修改的用户信息数组，键名与数据库字段名对应
     * @return  mixd    //失败返回FALSE，成功返回影响的行数(自动更新usertree表)
     */
    public function updateUser( $mUserId, $aUserInfo )
    {
        //数据安全检测
        if( empty($mUserId) && empty($aUserInfo) && !is_array($aUserInfo) )
        {
            return FALSE;
        }
        if( is_array($mUserId) )
        {//如果为批量修改
            foreach( $mUserId as $k => $v )
            {
                if( empty($v) )
                {//过滤无效数据
                    unset( $mUserId[$k] );
                }
                else
                {
                    $mUserId[$k] = intval($v);
                }
            }
            $sTempWhereSql = " `userid` IN (". implode(',',$mUserId) .")" ;
        }
        else
        {
            $sTempWhereSql = " `userid` = '". intval( $mUserId ) ."'" ;
        }
        if( isset($aUserInfo['nickname']) )
        {
            $aUserInfo['nickname'] = daddslashes($aUserInfo['nickname']);
        }
        if( isset($aUserInfo['userrank']) )
        {
            if( is_numeric($aUserInfo['userrank']) )
            {
                $aUserInfo['userrank'] = intval($aUserInfo['userrank']);
            }
            else 
            {
                unset($aUserInfo['userrank']);
            }
        }
        if( !empty($aUserInfo['username']) || isset($aUserInfo['usertype']) 
            || isset($aUserInfo['nickname']) || isset($aUserInfo['userrank']) )
        {//如果涉及到usertree表的更新，则启用事务更新
            $aData = array();
            if( !empty($aUserInfo['username']) )
            {
                $aUserInfo['username'] = daddslashes($aUserInfo['username']);
                $aData['username']     = $aUserInfo['username'];
            }
            if( isset($aUserInfo['usertype']) && is_numeric($aUserInfo['usertype']) )
            {
                $aUserInfo['usertype'] = intval($aUserInfo['usertype']);
                $aData['usertype']     = $aUserInfo['usertype'];
            }
            if( isset($aUserInfo['nickname']) )
            {
                $aData['nickname']     = $aUserInfo['nickname'];
            }
            if( isset($aUserInfo['userrank']) )
            {
                $aData['userrank']     = $aUserInfo['userrank'];
            }
            //开始事务
            $this->oDB->doTransaction();
            $temp_numrows = $this->oDB->update( 'usertree', $aData, $sTempWhereSql );
            if( $temp_numrows < 1 )
            {//更新usertree表失败，则回滚事务，返回失败
                $this->oDB->doRollback();
                return FALSE;
            }
            $temp_numrows = $this->oDB->update( 'users', $aUserInfo, $sTempWhereSql );
            if( $temp_numrows < 1 )
            {//更新usertree表失败，则回滚事务，返回失败
                $this->oDB->doRollback();
                return FALSE;
            }
            /*if( isset($aData['nickname']) )
            {
                //临时同步更新高频用户信息代码开始
                if( is_array($mUserId) )
                {//如果为批量修改
                    foreach( $mUserId as $k => $v )
                    {
                        if( empty($v) )
                        {//过滤无效数据
                            unset( $mUserId[$k] );
                        }
                        else
                        {
                            $mUserId[$k] = intval($v);
                        }
                    }
                    $mUserId = implode(',',$mUserId);
                }
                else
                {
                    $mUserId = intval($mUserId);
                }
                if( is_array($mUserId) )
                {
                    $sSql = " SELECT `gpuserid` FROM `tempusermap` WHERE `dpuserid` IN (" . $mUserId .")";
                }
                else 
                {
                    $sSql = " SELECT `gpuserid` FROM `tempusermap` WHERE `dpuserid` = '" . $mUserId ."'";
                }
                $aTempUserMap = $this->oDB->getAll( $sSql );
                if( empty($aTempUserMap) )
                {
                    if( is_array($mUserId) )
                    {
                        $sGpSql = "UPDATE `users` SET `nickname` = '" .$aData['nickname'] . "' WHERE `id` IN (" . $mUserId. ")";
                    }
                    else 
                    {
                        $sGpSql = "UPDATE `users` SET `nickname` = '" .$aData['nickname'] . "' WHERE `id` = '" . $mUserId. "'";
                    }
                }
                else
                {
                    $aGpUserId = array();
                    $sUserId = '';
                    foreach ($aTempUserMap as $aUserId )
                    {
                        $aGpUserId[] = $aUserId['gpuserid'];
                    }
                    $sUserId = implode(',',$aGpUserId);
                    $sGpSql = "UPDATE `users` SET `nickname` = '" .$aData['nickname'] . "' WHERE `id` IN (" . $sUserId. ")";
                }
                $oDB = new db($GLOBALS['aSysDbServer']['gaopin']);
                $oDB->query( $sGpSql );
                if( $oDB->errno() > 0 )
                {//更新用户昵称失败
                    $this->oDB->doRollback();
                    return -2;
                }
                //临时同步更新高频用户信息代码结束
            }*/
            $this->oDB->doCommit();
            return $temp_numrows;
        }
        return $this->oDB->update( 'users', $aUserInfo, $sTempWhereSql );
    }



    /**
     * 修改用户级别
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   int     $iUserType  //用户类型ID，0:用户  1:代理  2:总代管理员
     * @param   int     $iGroupId   //用户组ID
     * @return  boolean //成功返回TRUE，失败返回FALSE, 如果是有下级不能调整返回-1
     */
    public function updateUserLevel( $iUserId, $iUserType, $iGroupId )
    {
        if( empty($iUserId) || empty($iGroupId) || !is_numeric($iUserId) 
            || !is_numeric($iUserType) || !is_numeric($iGroupId) )
        {
            return FALSE;
        }
        if( $iUserType == 2 )
        {//不能从用户或者代理调整到总代管理员
            return FALSE;
        }
        if( $iUserType == 0 )
        {//如果修改为用户，则判断是否用户原来下面是否有下级
            $sSql = "SELECT `userid` FROM `usertree` WHERE FIND_IN_SET('".$iUserId."', `parenttree`) AND `isdeleted`='0' ";
            $this->oDB->query( $sSql );
            if( $this->oDB->ar() > 0 )
            {
                return -1;
            }
        }
        $this->oDB->doTransaction(); //启用事务
        //修改users表
        $sSql = " UPDATE `users` SET `usertype`='".$iUserType."' WHERE `userid`='". $iUserId ."'  ";
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 )
        { // 修改失败
            $this->oDB->doRollback();
            return FALSE;
        }
        // 修改usertree表
        $sSql = "UPDATE `usertree` SET `usertype`='".$iUserType."' WHERE `userid`='". $iUserId ."' AND `isdeleted`='0'";
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 )
        {//修改失败
            $this->oDB->doRollback();
            return FALSE;
        }
        //修改userchannel表
        $sSql = " UPDATE `userchannel` SET `groupid`='".$iGroupId."' WHERE `userid`='". $iUserId ."' ";
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 )
        {//修改失败
            $this->oDB->doRollback();
            return FALSE;
        }
        $this->oDB->doCommit();
        return TRUE;
    }



    /**
     * 删除用户
     * 
     * @access  public
     * @author  james
     * @param   mixed   $mUserId    //要删除的用户ID,或者用户ID数组(批量删除)
     * @return  mixd    //失败返回FALSE，成功返回影响行数
     */
    public function delete( $mUserId )
    {
        //数据安全检测
        if( empty($mUserId) )
        {
            return FALSE;
        }
        if( is_array($mUserId) )
        {//如果为批量修改
            foreach( $mUserId as $k => $v )
            {
                if( empty($v) )
                {//过滤无效数据
                    unset( $mUserId[$k] );
                }
                else
                {
                    $mUserId[$k] = intval($v);
                }
            }
            $sTempWhereSql = " `userid` IN (". implode(',',$mUserId) .")" ;
        }
        else
        {
            $sTempWhereSql = " `userid` = '". intval( $mUserId ) ."'" ;
        }
        return $this->oDB->delete( 'users', $sTempWhereSql );
    }



    /**
     * 逻辑删除用户
     * @access  public
     * @author  james
     * @param   mixed   $mUserId    //要删除的用户ID,或者用户ID数组(批量删除)
     * @param   int     $iUserType  //用户类型，1用户，2总代管理员
     * @return  mixd    //失败返回FALSE，成功返回影响行数 , -1 有下级用户，-2：帐户资金上有钱
     */
    public function deleteUser( $mUserId, $iUserType = 1 )
    {
        //数据安全检测
        if( empty($mUserId) )
        {
            return FALSE;
        }
        if( is_array($mUserId) )
        {//如果为批量修改
            foreach( $mUserId as $k=>$v )
            {//过滤无效数据
                if( empty($v) )
                {
                    unset( $mUserId[$k] );
                }
                else
                {
                    $mUserId[$k] = intval($v);
                }
            }
            $sTempWhereSql  = " `userid` IN (". implode(',', $mUserId) .") " ;
            $temp_where     = " AND `parentid` IN (". implode(',', $mUserId) .") ";
        }
        else
        {
            $sTempWhereSql  = " `userid` = '". intval( $mUserId ) ."' " ;
            $temp_where     = " AND `parentid` = '". intval( $mUserId ) ."' ";
        }
        if( $iUserType == 1 )
        {//普通用户才检测下级和资金检测
            //检测是否含有下级
            $sSql = "SELECT `userid` FROM `usertree` WHERE `isdeleted`='0' ".$temp_where;
            $this->oDB->query( $sSql );
            if( $this->oDB->ar() > 0 )
            {//存在下级
                return -1;
            }
            //检测资金帐户上是否有钱，有钱不能删除
            $sSql = "SELECT `userid` FROM `userfund` WHERE `channelbalance` > 0 AND ".$sTempWhereSql;
            $this->oDB->query( $sSql );
            if( $this->oDB->ar() > 0 )
            {//资金帐户上还有钱，不允许删除
                return -2;
            }
        }
        return $this->oDB->update( 'usertree', array('isdeleted' => 1), $sTempWhereSql );
    }



    /**
     * 读取用户信息 ( 一个 )
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId        //要读取的用户ID 
     * @param   array   $aUserInfo      //要读取的用户的信息(字段名)
     * @param   string  $sAndWhereSql   //附加的搜索条件，以 'and' 开始，例如：' AND `isfrozen`=0'
     * @return  mixed   //失败返回FALSE，成功则返回用户信息数组
     */
    public function getUserInfo( $iUserId = 0, $aUserInfo = array(), $sAndWhereSql = '' )
    {
        //如果用户ID取不到值则直接返回FALSE
        if( empty($iUserId) )
        {
            return FALSE;
        }
        $sTempWhereSql = " `userid` = '". intval($iUserId) ."' ";
        if( !empty($sAndWhereSql) )
        {
            $sTempWhereSql .= $sAndWhereSql;
        }
        if( is_array($aUserInfo) && !empty($aUserInfo) )
        {//如果指定了要读取的内容
            //格式化字段名
            foreach( $aUserInfo as &$v )
            {
                $v = "`".$v."`";
            }
            $sSql = "SELECT ". implode(',',$aUserInfo) ." FROM `users` WHERE ". $sTempWhereSql;
        }
        else
        {
            $sSql = "SELECT * FROM `users` WHERE ". $sTempWhereSql;
        }
        unset($sTempWhereSql);
        return $this->oDB->getOne( $sSql );
    }


    /**
     * 统计用户的所有下级可开户数额
     *
     * @author james
     * @access public
     * @param  array    $aUserIds  //用户，多用户的ID
     * @return array
     */
    public function & getChildrenAddCount( $aUserIds )
    {
        $aResult = array();
        if( empty($aUserIds) || !is_array($aUserIds) )
        {
            return $aResult;
        }
        $aTempArr = array();
        foreach( $aUserIds as $v )
        {
            $aTempArr[] = " FIND_IN_SET('".$v."', ut.`parenttree`) ";
        }
        $sAndWhere = " (".implode(" OR ", $aTempArr).") ";
        $sSql = " SELECT SUM(u.`addcount`) AS addcount,ut.`parentid` FROM `usertree` AS ut 
                  LEFT JOIN `users` AS u ON u.`userid`=ut.`userid`
                  WHERE ut.`isdeleted`='0' AND ".$sAndWhere." GROUP BY ut.`parentid` ";
        $aTempArr = $this->oDB->getAll($sSql);
        if( empty($aTempArr) )
        {
            return $aResult;
        }
        foreach( $aTempArr as $v )
        {
            $aResult[$v['parentid']] = $v['addcount'];
        }
        return $aResult;
    }


    /**
     * 读取用户基本信息[users+usertree]
     * 
     * @access  public
     * @author  james
     * @param   string  $sFiled     //要读取的内容
     * @param   string  $sLeftJoin  //要增加的管理表
     * @param   string  $sAndWhere  //搜索条件 
     * @param   boolean $bIsMore    //是否为列表搜索 FALSE[默认]：只读一条记录，TRUE：读取多条记录
     */
    public function & getUsersProfile( $sFiled = '', $sLeftJoin = '', $sAndWhere = '', $bIsMore = FALSE )
    {
        $sFiled	= empty($sFiled) ? '*' : $sFiled;
        $sSql   = " SELECT ".$sFiled." FROM `usertree` AS ut LEFT JOIN `users` AS u ON ut.`userid`=u.`userid` ";
        $sSql  .= $sLeftJoin." WHERE 1 ".$sAndWhere;
        if( $bIsMore )
        {
            return $this->oDB->getAll( $sSql );
        }
        return $this->oDB->getOne( $sSql );
    }



    /**
     * 分配用户数额
     */
    public function distributeUser( $iUserId, $aData, $iCount )
    {
        if( empty($iUserId) || !is_numeric($iUserId) || empty($aData) || !is_array($aData) 
                   || empty($iCount) || !is_numeric($iCount) || $iCount < 0 )
        {
            return -1;
        }
        $iUserId    = intval($iUserId);
        $iCount     = 0;
        //读取用户剩余可分配开户数额
        $sWhere     = "AND ut.`isdeleted`='0' AND ut.`userid`='".$iUserId."' AND ut.`usertype`='1'";
        $aSelf      = $this->getUsersProfile('u.`addcount`', "", $sWhere);
        if( empty($aSelf) )
        {
            return -2;
        }
        $this->oDB->doTransaction();    //开始事务
        foreach( $aData as $v )
        {
            if( $v['addnumber'] > 0 )
            {
                $iCount += intval($v['addnumber']);
                if( $iCount > $aSelf['addcount'] )
                {
                    $this->oDB->doRollback();
                    return -3;
                }
                $sSql = " UPDATE `users` SET `addcount`=`addcount`+".intval($v['addnumber'])." 
                                WHERE `userid`='".$v['userid']."' ";
                $this->oDB->query( $sSql );
                if( $this->oDB->errno() > 0 )
                {
                    $this->oDB->doRollback();
                    return FALSE;
                }
            }
        }
        //扣减自己的用户数额
        $sSql = " UPDATE `users` SET `addcount`=`addcount`-".$iCount.
                " WHERE `addcount`>=".$iCount." AND `userid`='".$iUserId."' ";
        $this->oDB->query( $sSql );
        if( $this->oDB->ar() == 0 )
        {
            $this->oDB->doRollback();
            return FALSE;
        }
        $this->oDB->doCommit();
        return TRUE;
    }



    /**
     * 获取指定用户的上级ID
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId        //用户ID
     * @param   boolean $bAllParentId   //TRUE：获取所有上级，FALSE：直接上级，默认为FALSE
     * @return  mixd    //直接上级直接返回ID，所有上级返回ID字符串（1,2,3,4,5），失败返回FALSE
     */
    public function getParentId( $iUserId, $bAllParentId = FALSE )
    {
        if( intval($iUserId) < 1 )
        {
            return FALSE;
        }
        $sSql = "";
        if( $bAllParentId )
        {//获取所有上级
            $sSql = "SELECT `parenttree` AS `parents` FROM `usertree` 
                        WHERE `userid`='".$iUserId."' AND `isdeleted`='0'";
        }
        else 
        {
            $sSql = "SELECT `parentid` AS `parents` FROM `usertree` 
                        WHERE `userid`='".$iUserId."' AND `isdeleted`='0'";
        }
        $aData = $this->oDB->getOne( $sSql );
        if( empty($aData) )
        {//获取失败
            return FALSE;
        }
        return $aData['parents'];
    }



    /**
     * 获取指定用户的上级列表
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId        //用户ID
     * @param   boolean $bAllParentId   //TRUE：获取所有上级，FALSE：直接上级，默认为FALSE
     * @return  mixd    //成功返回上级用户信息列表，失败返回空数组
     */
    public function getParent( $iUserId, $bAllParentId = FALSE )
    {
        $aData = $this->getParentId( $iUserId, $bAllParentId );
        if( empty($aData) )
        { // 获取失败
            return array();
        }
        else
        {
            return $this->oDB->getAll("SELECT `username`,`userid` FROM `usertree` 
                        WHERE `userid` IN (".$aData.") AND `isdeleted`='0' ORDER BY `userid` ASC");
        }
    }



    /**
     * 获取指定用户的直接下级或者所有下级
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId        //用户ID
     * @param   boolean $bAllChildren   //TRUE：所有下级，FALSE：直接下级，默认为FALSE
     * @param   boolean $bIsAdmin       //是否获取总代管理员 TURE:包括，FALSE：不包括
     * @return  mixd    ////直接下级直接返回ID，所有下级返回ID字符串(1,2,3,4,5)，失败返回FALSE
     */
    public function getChildrenId( $iUserId, $bAllChildren = FALSE, $bIsAdmin = FALSE )
    {
        if( intval($iUserId) < 1 )
        {
            return FALSE;
        }
        $sSql = " AND `isdeleted`='0' ";
        if( !(bool)$bIsAdmin )
        {
            $sAndSql = " AND `usertype`<2 ";
        }
        else 
        {
            $sAndSql = "";
        }
        if( $bAllChildren )
        {//获取所有下级
            $sSql = "SELECT `userid` FROM `usertree` WHERE FIND_IN_SET('".$iUserId."', `parenttree`) ".$sAndSql;
        }
        else 
        {//直接下级
            $sSql = "SELECT `userid` FROM `usertree` WHERE `parentid`='".$iUserId."' ".$sAndSql;
        }
        $this->oDB->query( $sSql );
        $aTempRow   = array();
        $aData      = array();
        while( FALSE != ($aTempRow = $this->oDB->fetchArray()) )
        {
            $aData[] = $aTempRow['userid'];
        }
        unset($aTempRow);
        if( empty($aData) )
        {//获取失败或者没有下级
            return '';
        }
        return implode(',', $aData);
    }



    /**
     * 根据用户ID获取其下的直接下级或者所有下级用户列表（只获取用户名和ID，不分页，不获取总代管理员）
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   boolean $bAllChildren   //TRUE：所有下级，FALSE：直接下级，默认为TRUE
     * @param   string  $sAndWhere      //附加搜索条件
     * @param   return  //成功返回用户列表，失败返回FALSE
     */
    public function getChildListID( $iUserId, $bAllChildren=TRUE, $sAndWhere='', $sOrderby='' )
    {
        if( intval($iUserId) < 0 )
        {
            return FALSE;
        }
        $sSql = "";
        if( $bAllChildren )
        {//获取所有下级
            $sSql = "SELECT `userid`,`username`,`usertype` FROM `usertree` 
                    WHERE FIND_IN_SET('".$iUserId."', `parenttree`) 
                    AND `usertype`<'2' AND `isdeleted`='0' ".$sAndWhere." 
                    ORDER BY `parentid` ASC,`parenttree` ASC ".$sOrderby;
        }
        else 
        {//直接下级
            $sSql = "SELECT a.`userid`,a.`username`,a.`usertype`,count(b.userid) as childcount FROM `usertree` AS a 
                    LEFT JOIN `usertree` AS b ON (b.`parentid`=a.`userid` and b.`usertype`<2 AND b.`isdeleted`='0' ) 
                    WHERE a.`parentid`='".$iUserId."' AND a.`usertype`<'2' AND a.`isdeleted`='0'
                    ".$sAndWhere." GROUP BY a.`userid` ".$sOrderby;
        }
        $aData = $this->oDB->getAll( $sSql );
        if( empty($aData) )
        {
            return FALSE;
        }
        return $aData;
    }



    /**
     * 根据用户ID获取其下的直接下级或者所有下级用户列表（获取详细信息，分页，不获取总代管理员）
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   string  $sField//要查询的内容，表别名:user=>u,usertree=>ut,userchannel=>uc,usergroup=>ug,userfund=>uf
     * @param   string  $sAndWhere  //附加的查询条件，以AND 开始
     * @param   string  $sOrderBy   //排序条件，默认按照用户ID排序
     * @param   int     $iPageRecords //每页显示的条数
     * @param   int     $iCurrPage  //当前页
     * @param   boolean $bAllChildren   //TRUE：所有下级，FALSE：直接下级，默认为TRUE
     * @param   boolean $bIsSelf        //是否要获取自己的信息 TRUE:获取，FALSE：不获取
     * @param   int     $iCurrentId     //当前查看用户ID
     * @param   return  //成功返回用户列表array('affects'=>总记录数,'results'=>结果集合)，失败返回FALSE
     * 
     */
    public function & getChildList( $iUserId, $sField = '', $sAndWhere = '', $sOrderBy = '', $iPageRecords = 20, 
                                    $iCurrPage = 1, $bAllChildren = TRUE, $bIsSelf = FALSE, $iCurrentId = 0 )
    {
        $result	= array();
        if( !is_numeric($iUserId) )
        {//获取失败或者没有下级
            return $result;
        }
        $sTableName = " `usertree` AS ut  
                        LEFT JOIN `users` AS u ON u.`userid` = ut.`userid` 
                        LEFT JOIN `userchannel` AS uc ON ut.`userid`=uc.`userid` 
                        LEFT JOIN `usergroup` AS ug ON uc.`groupid`=ug.`groupid` 
                        LEFT JOIN `userfund` AS uf ON ut.`userid`=uf.`userid` ";
        $sFields    = " ut.`userid`,ut.`usertype`,ut.`username`,u.`registertime`,u.`authtoparent`,ut.`isfrozen`,
                        u.`authadd`,ut.`parentid`,ut.`parenttree`,ut.`ocs_status`,ug.`groupname`,uf.`availablebalance` ";
        $sCondition = " ut.`isdeleted`='0' AND ut.`usertype`<'2' 
                        AND uc.`channelid`='0' AND uf.`channelid`='0' ";
        if( (bool)$bAllChildren )
        {
            if ( $iUserId > 0 )
            {
                $sAndWhere .= " AND FIND_IN_SET('".$iUserId."',ut.`parenttree`) ";
            }
        }
        else 
        {
            $sAndWhere .= " AND ut.`parentid`='".$iUserId."' ";
        }
        if( !empty($sField) )
        {
            $sFields = $sField;
        }
        if( empty($sOrderBy) )
        {//默认没有排序
            //$sCondition .= " ORDER BY u.`userid` ASC ";
        }
        else 
        {
            $sOrderBy = " ORDER BY ".$sOrderBy;
        }
        $sCountSql = "SELECT COUNT(ut.`userid`) AS TOMCOUNT FROM `usertree` AS ut
                        LEFT JOIN `userchannel` AS uc ON ut.`userid`=uc.`userid` 
                        LEFT JOIN `users` AS u ON ut.`userid` = u.`userid` 
                        LEFT JOIN `userfund` AS uf ON ut.`userid`=uf.`userid` 
                        WHERE ut.`isdeleted`='0' AND ut.`usertype`<'2' AND uc.`channelid`='0' 
                        AND uf.`channelid`='0' ".$sAndWhere;
        $sCondition .= $sAndWhere;
        $result = $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, 
                                            $sOrderBy, '', $sCountSql );
        if( (bool)$bIsSelf )
        {//获取自己的信息
            $sSql = "SELECT ".$sFields." FROM ".$sTableName." WHERE ut.`isdeleted`='0' 
                            AND uc.`channelid`='0' AND uf.`channelid`='0' AND ut.`userid`='".$iUserId."' ";
            $this->oDB->query( $sSql );
            unset( $sSql );
            if( $this->oDB->numRows() > 0 )
            {
                $result['self'] = $this->oDB->fetchArray();
            }
            if( !empty($iCurrentId) )
            {
                $temp_parenttree = preg_replace("/^[\\d,]*".$iCurrentId."[,]?/i",
                                                    "",$result['self']['parenttree'],1);
            }
            //获取导航
            if( !empty($temp_parenttree) )
            {
                $sSql = "SELECT `userid`,`username` FROM `usertree` WHERE `userid` IN(".$temp_parenttree.")";
                $result['self']['bannners'] = $this->oDB->getAll($sSql);
            }
        }
        return $result;
    }



    /**
     * 判断是否为销售管理员
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //管理员ID
     * @return  //是返回TRUE，不是返回FALSE
     */
    public function IsAdminSale( $iUserId )
    {
        if( empty($iUserId) || !is_numeric($iUserId) )
        {//获取失败或者没有下级
            return FALSE;
        }
        $sSql = " SELECT 1 FROM `userchannel` AS uc LEFT JOIN `proxygroup` AS pg ON uc.`groupid`=pg.`groupid` 
                WHERE uc.`userid`='".$iUserId."' AND pg.`issales`='1' ";
        $this->oDB->query( $sSql );
        if( $this->oDB->ar() > 0 )
        {//是销售管理员
            return TRUE;
        }
        return FALSE;
    }



    /**
     * 判断一个用户是否在某销售管理员团队下
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   int     $iAdminId   //销售管理员ID
     * @param   return  //在团队下返回TRUE，不在返回FALSE
     */
    public function isInAdminSale( $iUserId, $iAdminId )
    {
        if( !is_numeric($iUserId) || !is_numeric($iAdminId) )
        {//获取失败或者没有下级
            return FALSE;
        }
        $sSql = "SELECT ut.`userid` FROM `usertree` AS ut 
                LEFT JOIN `useradminproxy` AS uap ON ut.`lvproxyid`=uap.`topproxyid` 
                 WHERE uap.`adminid`='".$iAdminId."' AND ut.`userid`='".$iUserId."' AND ut.`isdeleted`='0' ";
        //读取分配给销售管理员的一代的ID
        $this->oDB->query( $sSql );
        unset($sSql);
        if( $this->oDB->numRows() < 1 )
        {//没有分配
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }



    /**
     * 根据总代管理员ID，读取所有分配的一代ID
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代管理员ID
     * @return  mixed       //成功返回ID数组，失败返回FALSE
     */
    public function getAdminProxyByUserId( $iUserId )
    {
        if( empty($iUserId) )
        {
            return FALSE;
        }
        $sSql = " SELECT `entry`,`topproxyid` FROM `useradminproxy` WHERE `adminid`='". intval($iUserId) ."'";
        $this->oDB->query($sSql);
        if( $this->oDB->ar() < 1 )
        {
            return FALSE;
        }
        $aResult    = array();
        $aTempRow   = array();
        while( FALSE != ($aTempRow = $this->oDB->fetchArray()) )
        {
            $aResult[] = $aTempRow['topproxyid'];
        }
        return $aResult;
    }



    /**
     * 获取指定用户的团队余额
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserid    //用户ID
     * @param   string  $sAndWhere  //附加搜索条件
     * @return  float   //团队余额
     */
    public function getTeamBank( $iUserId, $sAndWhere = '' )
    {
        if( !is_numeric($iUserId) )
        {
            return 0.0000;
        }
        $sSql = "SELECT SUM(uf.`availablebalance`) AS JAMESCOUNT FROM `userfund` AS uf 
                LEFT JOIN `usertree` AS ut ON (uf.`userid`=ut.`userid` AND ut.`isdeleted`='0') 
                WHERE uf.`channelid`='0' ";
        if( is_numeric($iUserId) && $iUserId > 0 )
        {//如果是单个用户
            $sSql .= " AND (ut.`userid`='".$iUserId."' OR FIND_IN_SET('".$iUserId."', `parenttree`)) ";
        }
        $sSql .= $sAndWhere;
        $this->oDB->query( $sSql );
        if( $this->oDB->numRows() < 1 )
        {
            return 0.0000;
        }
        $aData = $this->oDB->fetchArray();
        return $aData['JAMESCOUNT'];
            
    }



    /**
     * 判断一个用户是否为总代
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @return  boolean //如果是则返回TRUE，不是则返回FALSE
     */
    public function isTopProxy( $iUserId )
    {
        $iPid = $this->getParentId( $iUserId );
        if( $iPid === FALSE )
        {
            return FALSE;
        }
        if( $iPid == 0 )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }



    /**
     * 获取指定用户的总代ID
     * 
     * @access  public
     * @author  james
     * @param   int $iUserId    //用户ID
     * @param   boolean $bUserInfo  //是否要获取总代其他信息
     * @return  int //总代ID，本身为总代时返回自身ID，失败返回FALSE
     */
    public function getTopProxyId( $iUserId, $bUserInfo = FALSE )
    {
        if( FALSE === ($aData = $this->getParentId($iUserId, TRUE)) )
        {
            return FALSE;
        }
        if( empty($aData) )
        {//本身即为总代ID
            $iPrentId = $iUserId;
        }
        else
        {
            //返回父亲树下面的第一个ID即为总代ID
            $aTempData = explode( ',', $aData );
            unset($aData);
            $iPrentId = $aTempData[0];
        }
        if( FALSE == $bUserInfo )
        {
            return $iPrentId; 
        }
        $sSql = "SELECT `userid`,`username` FROM `usertree` WHERE `userid`='".$iPrentId."' AND `isdeleted`='0' ";
        $aResult = $this->oDB->getOne( $sSql );
        if( empty($aResult) )
        {
            return FALSE;
        }
        return $aResult;
        
    }



    /**
     * 判断一个用户是否为另外一个用户的上级
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //下级用户ID
     * @param   int     $iParentId  //上级ID
     * @return  //是上级返回TRUE，不是返回FALSE
     */
    public function isParent( $iUserId, $iParentId )
    {
        if( empty($iUserId) || empty($iParentId) || !is_numeric($iUserId) || !is_numeric($iParentId) )
        {
            return FALSE;
        }
        $sSql = "SELECT `userid` FROM `usertree` WHERE FIND_IN_SET('"
                    . $iParentId . "', `parenttree`) AND `userid`='".$iUserId."'";
        $this->oDB->query( $sSql );
        if( $this->oDB->ar() > 0 )
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     *  判断一个用户是否为另外一个用户的直接上级
     * 
     * @param   int     $iUserId    用户ID
     * @param   int     $iParentId  需测试的上级ID
     * 
     * @return  TRUE:是 FALSE:不是
     */
    public function isFatherParent( $iUserId, $iParentId )
    {
    	
    	$sSql = "SELECT `userid`,`parentid` FROM `usertree` WHERE `userid`=".$iUserId." AND `parentid`=".$iParentId;
    	$this->oDB->query($sSql);
    	if ( $this->oDB->ar() > 0  && $this->oDB->errno() == 0)
    	{
    		return TRUE;
    	}
    	return FALSE;
    }



    /**
     * 根据总代ID，获取总代管理员列表信息(用户ID，用户登陆名，用户呢称，组ID，组名，)
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代ID
     * @return  mixed   //成功返回列表信息，失败返回FALSE
     */
    public function getAdminList( $iUserId, $sOrderby='' )
    {
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $sSql = "SELECT u.`userid`,u.`nickname`,u.`username`,pg.`groupid`,pg.`groupname`,pg.`isspecial`,pg.`issales`
                FROM `users` AS u 
                LEFT JOIN `usertree` AS ut ON u.`userid`=ut.`userid` 
                LEFT JOIN `userchannel` AS uc ON u.`userid`=uc.`userid` 
                LEFT JOIN `proxygroup` AS pg ON uc.`groupid`=pg.`groupid` 
                WHERE 
                ut.`parentid`='".intval($iUserId)."' AND ut.`isdeleted`='0' AND uc.`channelid`='0' 
                AND u.`usertype`='2' ".$sOrderby;
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 根据用户ID，获取基本信息，组别等信息(userid,username,nickname,groupname,groupid)
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   int     $iUserType  //用户类型，0用户，1代理，2总代管理员
     * @return  mixed   //失败返回FALSE，成功返回用户信息
     */
    public function getUserExtentdInfo( $iUserId, $iUserType = 0 )
    {
        if( empty($iUserId) || !is_numeric($iUserType) )
        {
            return FALSE;
        }
        if( $iUserType == 2 )
        {
            $sSql = "SELECT u.`userid`,u.`username`,u.`nickname`,g.`groupname`,g.`groupid`,g.`ownerid`,g.`issales` 
                    FROM `users` AS u
                    LEFT JOIN `usertree` AS ut ON (u.`userid`=ut.`userid` AND ut.`isdeleted`='0') 
                    LEFT JOIN `userchannel` AS uc ON u.`userid`=uc.`userid` 
                    LEFT JOIN `proxygroup` AS g ON uc.`groupid`=g.`groupid` 
                    WHERE 
                    u.`userid`='".$iUserId."' AND uc.`channelid`='0' ";
            $aData = $this->oDB->getOne($sSql);
        }
        else 
        {
            $sSql = "SELECT u.`userid`,u.`username`,u.`nickname`,g.`groupname`,g.`groupid`,g.`isspecial`, g.`teamid` 
                    FROM `users` AS u
                    LEFT JOIN `usertree` AS ut ON (u.`userid`=ut.`userid` AND ut.`isdeleted`='0') 
                    LEFT JOIN `userchannel` AS uc ON u.`userid`=uc.`userid` 
                    LEFT JOIN `usergroup` AS g ON uc.`groupid`=g.`groupid` 
                    WHERE 
                    u.`userid`='".$iUserId."' AND uc.`channelid`='0' ";
            $aData = $this->oDB->getOne($sSql);
            if ($aData['isspecial'] > 0){
            	$sSql = "SELECT groupname FROM usergroup WHERE groupid = {$aData['isspecial']}";
            	$aResult = $this->oDB->getOne($sSql);
            	if (!empty($aResult)){
            		$aData['groupname'] = $aResult['groupname'];
            	}
            }
        }
        if( empty($aData) )
        {
            return FALSE;
        }
        return $aData;
    }



    /**
     * 根据用户名, 所使用的域名, 检测使用的域名是否为用户总代所有
     * @author Tom
     * @param string $sUsername
     * @param string $sDomain
     * @return BOOL
     */
    public function checkUserDomain( $sUsername = '', $sDomain = '' )
    {
        // 初步检测
        if( empty($sUsername) || empty($sDomain)  )
        {
            return FALSE;
        }
        $sUsername = daddslashes($sUsername);
        $sDomain   = daddslashes($sDomain);
        $this->oDB->getOne("SELECT * FROM `usertree` ut LEFT JOIN `userdomain` ud ON ut.`lvtopid`=ud.`userid` "
                . " LEFT JOIN `domains` d ON (ud.`domainid`=d.`id` ) "
                . " WHERE ut.username='$sUsername' AND d.domain='$sDomain' AND d.`status`=1 ");
        if( $this->oDB->ar() )
        { // 非总代管理员则直接成功返回
            return TRUE; 
        }
        $this->oDB->getOne("SELECT * FROM `usertree` ut LEFT JOIN `userdomain` ud ON "
                . " ( ut.`usertype`=2 AND ut.`parentid`=ud.`userid` )"
                . " LEFT JOIN `domains` d ON (ud.`domainid`=d.`id` ) "
                . " WHERE ut.username='$sUsername' AND d.domain='$sDomain' AND d.`status`=1 ");
        // 总代管理员的判断
        return $this->oDB->ar() ? TRUE : FALSE;
    }



    /**
     * 根据用户ID冻结用户
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId        //用户ID
     * @param   int     $iParentId      //执行冻结的用户ID
     * @param   int     $iUserType      //执行冻结的用户类型，1用户，2公司管理员
     * @param   int     $iFrozenType    //冻结类型  1:完全冻结 2:只可登陆  3:可登陆，可充提
     * @param   boolean $bIsAll         //是否冻结所有下级
     * @return  boolean //  FALSE:失败
     *                      -1：没有权限
     *                      -2：踢用户没有成功
     *                      TRUE: 成功
     */
    public function frozenUser( $iUserId, $iParentId, $iUserType = 1, $iFrozenType = 1, $bIsAll = FALSE )
    {
        if( empty($iUserId) || empty($iParentId) || empty($iUserType) || empty($iFrozenType) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $iUserId    = intval($iUserId);
        $iParentId  = intval($iParentId);
        $iUserType  = intval($iUserType) < 0 ? 1 : intval($iUserType);
        $iFrozenType= intval($iFrozenType) < 0 ? 1 : intval($iFrozenType);
        if( $iUserType == 1 )
        {//如果是用户冻结，检测是否有冻结权限
            if( !$this->isParent($iUserId, $iParentId) )
            {
                return -1;
            }
        }
        $sIds = '';
        if( (bool)$bIsAll )
        {//冻结所有
             //获取所有下级ID
             $sIds = $this->getChildrenId( $iUserId, TRUE );
        }
        $sAdminProxyIds = "";
        if ($this->isTopProxy($iUserId) === true){ // 如果是总代则冻结所有总代管理员
        	$aAdminproxy = $this->getAdminList($iUserId);
        	if (!empty($aAdminproxy)){
        		foreach ($aAdminproxy as $k => $v){
        			$sAdminProxyIds .= $v['userid'] . ',';
        		}
        	}
        }
        $sAdminProxyIds = !empty($sAdminProxyIds) ? "," . substr($sAdminProxyIds, 0, -1) : "";
        if( empty($sIds) )
        {//没有下级获取不包括下级
            $sCondition = " `userid` IN ({$iUserId}{$sAdminProxyIds})";
        }
        else
        {
            $sCondition = " `userid` IN (". $iUserId . ',' . $sIds . $sAdminProxyIds . ")";
        }
        $sSql = "UPDATE `usertree` SET `isfrozen`='".$iUserType."', `frozentype`='".$iFrozenType."' WHERE ".$sCondition;
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 )
        {
            return FALSE;
        }
        //执行踢用户
        $this->oDB->query("delete from `sessions` where `isadmin`='0' and ".$sCondition);
        if( $this->oDB->errno() > 0 )
        {
            return -2;//踢用户没有成功
        }
        return TRUE;
    }



    /**
     * 根据ID解冻用户
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId        //用户ID
     * @param   int     $iParentId      //执行解冻的用户ID
     * @param   int     $iUserType      //执行解冻的用户类型，1用户，2公司管理员
     * @param   boolean $bIsAll         //是否解冻所有下级
     * @return  boolean //  FALSE:失败
     *                      -1：没有权限
     *                      TRUE: 成功
     */
    public function unFrozenUser( $iUserId, $iParentId, $iUserType = 1, $bIsAll = FALSE )
    {
        if( empty($iUserId) || empty($iParentId) || empty($iUserType) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $iUserId    = intval($iUserId);
        $iParentId  = intval($iParentId);
        $iUserType  = intval($iUserType) < 0 ? 1 : intval($iUserType);
        if( $iUserType == 1 )
        {//如果是用户解冻，检测是否有解冻权限
            if( !$this->isParent($iUserId, $iParentId) )
            {
//            	print_rr($iUserId."/".$iParentId,1,1);
                return -1;
            }
        }
        $sIds = '';
        if( (bool)$bIsAll )
        {//解冻所有
             //获取所有下级ID
             $sIds = $this->getChildrenId( $iUserId, TRUE );
        }
        if ($this->isTopProxy($iUserId) === true){ // 如果是总代则冻结所有总代管理员
        	$aAdminproxy = $this->getAdminList($iUserId);
        	if (!empty($aAdminproxy)){
        		foreach ($aAdminproxy as $k => $v){
        			$sAdminProxyIds .= $v['userid'] . ',';
        		}
        	}
        }
        $sAdminProxyIds = !empty($sAdminProxyIds) ? "," . substr($sAdminProxyIds, 0, -1) : ""; 
        if( empty($sIds) )
        {//没有下级获取不包括下级
            $sCondition = " `userid` IN ({$iUserId}{$sAdminProxyIds})";
        }
        else
        {
            $sCondition = " `userid` IN (". $iUserId . ',' . $sIds . $sAdminProxyIds . ") ";
        }
        if( $iUserType == 1 )
        {//如果是用户解冻，则不能解冻公司管理员冻结的用户
            $sCondition .= " AND `isfrozen`='". $iUserType ."' ";
        }
        $sSql = "UPDATE `usertree` SET `isfrozen`='0', `frozentype`='0' WHERE ".$sCondition;
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0  )
        {
            return FALSE;
        }
        
        return TRUE;
    }

    
	/**
     * 根据用户ID开启或关闭在线客服 OCSOpen = 1,OCSClose=0
     * 
     * @access  public
     * @param   int     $iUserId        //被操作的用户ID
     * @param   int     $iParentId      //执行操作的用户ID
     * @param 	int 	$iUserType		//执行操作的用户类型 1:用户 2:后台管理员
     * @param 	int 	$iStatus		//执行的目标状态，开启或关闭
     * @param   boolean $bIsAll         //是否包含所有下级
     * @return  boolean //  FALSE:失败
     *                      -1：没有权限
     *                      TRUE: 成功
     */
    public function OCSStatus( $iUserId, $iParentId, $iUserType = 1, $iStatus = 1, $bIsAll = FALSE )
    {
        if( empty($iUserId) || !is_numeric($iParentId) || $iStatus > 2 || $iStatus < 0 || !is_numeric($iUserId) )
        {
            return FALSE;
        }

        $iUserId    = intval($iUserId);
        $iParentId  = intval($iParentId);
        $iStatus	= intval($iStatus) < 0 ? 1 : intval($iStatus);
        $sAddConds  = '';
        if( $iUserType == 1 )
        {	
        	//如果是用户操作
        	//检查操作者自己是否有权限
        	if ( $this->getOCSStatus() !== TRUE )
        	{
        		return -1;
        	}
        	//检测被操作者是否是直接下级, 且禁用用户开启功能
            if( !$this->isFatherParent($iUserId, $iParentId) )
            {
                return -1;
            }
            //用户操作带预置检查
           	$sAddConds =  ($iStatus == 1) ? ' AND `ocs_status`=0' : ' AND `ocs_status`=1';
        }
        $sIds = '';
        if( (bool)$bIsAll )
        {//操作所有
             //获取所有下级ID
             $sIds = $this->getChildrenId( $iUserId, TRUE );
        }
        
        $sAdminProxyIds = "";
        if ($this->isTopProxy($iUserId) === true){ // 如果是总代则操作包含所有总代管理员
        	$aAdminproxy = $this->getAdminList($iUserId);
        	if (!empty($aAdminproxy))
        	{
        		foreach ($aAdminproxy as $k => $v)
        		{
        			$sAdminProxyIds .= $v['userid'] . ',';
        		}
        	}
        }
        $sAdminProxyIds = !empty($sAdminProxyIds) ? "," . substr($sAdminProxyIds, 0, -1) : "";
        if( empty($sIds) )
        {//没有下级获取不包括下级
            $sCondition = " `userid` IN ({$iUserId}{$sAdminProxyIds})";
        }
        else
        {
            $sCondition = " `userid` IN (". $iUserId . ',' . $sIds . $sAdminProxyIds . ")";
        }
        $sSql = "UPDATE `usertree` SET `ocs_status`='".$iStatus."' WHERE ".$sCondition.$sAddConds;
        $this->oDB->query( $sSql );
        if( $this->oDB->errno() > 0 )
        {
            return FALSE;
        }
        
        return TRUE;
    }

	/**
	 * 获取用户OCS开关是否打开
	 * 
	 * db: usertree.ocs_status
	 *
	 * 
	 */
    public function getOCSStatus()
    {
    	$iUserId = $_SESSION['userid'];
    	
    	//总代管理员 转换为总代
    	if( $_SESSION['usertype'] == 2 )
        {
            $iUserId = $this->getTopProxyId( $_SESSION['userid'] );
            if( empty($iUserId) )
            {
               return FALSE;
            }
        }
    	
    	if ( !is_numeric($iUserId) )
    	{
    		 return FALSE;
    	}
    	$sSql = "SELECT `ocs_status` FROM `usertree` WHERE `userid`=".$iUserId;
    	$aData = $this->oDB->getOne($sSql);
    	if ( $this->oDB->errno() > 0)
    	{
    		return FALSE;
    	}
    	else
    	{
    		 return $aData['ocs_status'] == 1 ? TRUE : FALSE;
    	}
    	
    }
    
    /**
     * 检测资金密码是否正确
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId        //用户ID
     * @param   string  $sSecurityPass  //资金密码
     * @return  boolean //正确返回TRUE，错误或者失败返回FALSE
     */
    public function checkSecurityPass( $iUserId, $sSecurityPass )
    {
        if( empty($iUserId) || empty($sSecurityPass) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $sSql = " SELECT `userid` FROM `users` WHERE `userid`='".$iUserId."' 
                    AND `securitypwd`='".md5($sSecurityPass)."' ";
        $this->oDB->query( $sSql );
        if( $this->oDB->ar() > 0 )
        {
            return TRUE;
        }
        return FALSE;
    }



    /**
     * 获取用户登陆后显示在左侧的信息
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @return  array   //返回用户银行可用余额，未读短消息数量，星级，可开户数额
     */
    public function & getUserLeftInfo( $iUserId, $iUserType = 1 )
    {
        $aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return $aResult;
        }
        //短消息数量
        $sSql = "SELECT count(`entry`) as msgcount FROM `msglist` WHERE `receiverid`='".$iUserId."' 
                AND `receivergroup`='0' AND `readtime` IS NULL  AND `deltime` IS NULL ";
        $aMsgCount = $this->oDB->getOne($sSql);
        if( $iUserType == 2 )
        {//如果为总代管理员则可用余额，星级，可开用户数额调整为总代的
            $iUserId = $this->getTopProxyId( $iUserId );
        }
        $sSql = "SELECT ut.`userid`,ut.`parentid`,ut.`username`,u.`userrank`,u.`authadd`,u.`addcount`,
                 uf.`availablebalance` FROM `usertree` AS ut LEFT JOIN `users` AS u ON u.`userid`=ut.`userid` "
                ." LEFT JOIN `userfund` AS uf ON (ut.`userid`=uf.`userid` AND uf.`channelid`='0') "
                ." WHERE ut.`userid`='".$iUserId."'";
        $aResult = $this->oDB->getOne( $sSql );
        if( empty($aResult) )
        {
            return $aResult;
        }
        $aResult['msgcount'] = empty($aMsgCount) ? 0 : $aMsgCount['msgcount'];
        //获取上级的星级
        if( $aResult['parentid'] > 0 )
        {
        	$sSql = " SELECT `userrank` FROM `usertree` WHERE `isdeleted`='0' 
        	          AND `userid`='".$aResult['parentid']."'";
        	$aTemp = $this->oDB->getOne( $sSql );
        	if( !empty($aTemp) )
        	{
        		$aResult['parentrank'] = $aTemp['userrank'];
        	}
        }
        return $aResult;
    }



    /**
     * 检测用户名是否合法
     * @access static
     * @author  james
     * @return 合法返回TRUE，不合法返回FALSE
     */
    static function checkUserName( $sUserName )
    {
        if( preg_match( "/^[0-9a-zA-Z]{6,16}$/i", $sUserName ) )
        {
            return TRUE;
        }
        else 
        {
            return FALSE;
        }
    }



    /**
     * 检测登陆密码是否合法
     * @access static
     * @author  james
     * @return 合法返回TRUE，不合法返回FALSE
     */
    static function checkUserPass( $sUserPass )
    {
        if( !preg_match("/^[0-9a-zA-Z]{6,16}$/i",$sUserPass) || preg_match("/^[0-9]+$/",$sUserPass)
                || preg_match("/^[a-zA-Z]+$/i",$sUserPass) || preg_match("/(.)\\1{2,}/i",$sUserPass)
            )
        {
            return FALSE;
        }
        else 
        {
            return TRUE;
        }
    }



    /**
     * 检测呢称是否合法[2-6个任意字符，中文和全角算一个]
     * @access static
     * @author  james
     * @return 合法返回TRUE，不合法返回FALSE
     */
    static function checkNickName( $sName )
    {
        if( mb_strlen($sName,"UTF-8") >= 2 && mb_strlen($sName,"UTF-8") <= 6 )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }



    /**
     * 根据用户名, 获取用户ID
     *
     * @param string $sUsername
     * @return int userid or 0
     */
    public function getUseridByUsername( $sUsername = '' )
    {
        $sUsername = daddslashes( trim($sUsername) );
        if( FALSE === strpos($sUsername,'*') )
        {
            $aResult = $this->oDB->getOne( "SELECT `userid` FROM `users` WHERE `username`='$sUsername' LIMIT 1" );
            if( $this->oDB->ar() )
            {
                return $aResult['userid'];
            }
            else 
            {
                return 0;
            }
        }
        else
        {
            $aResult = $this->oDB->getAll( "SELECT `userid` FROM `users` WHERE `username` LIKE '".
                       str_replace( '*', '%', $sUsername ) ."' " );
            if( $this->oDB->ar() )
            {
                return $aResult;
            }
            else 
            {
                return 0;
            }
        }
    }



    /**
     * 根据用户名数组, 获取用户ID
     *   支持模糊搜索, 例:  tom,james* 将*替换成%
     * @param string $sUsername
     * @return int userid or 0
     */
    public function getUseridByUsernameArr( $aUsername = array() )
    {
        if( !is_array($aUsername) || empty($aUsername) )
        {
            return '';
        }
        $sWhere = ' 0 ';
        foreach( $aUsername as $v )
        {
            if( trim($v) == '' )
            {
                continue;
            }
            if( !strstr( $v, '*' ) )
            {
                $sWhere .= " OR `username` = '" . daddslashes($v) . "' ";
            }
            else 
            {
                $sWhere .= " OR `username` LIKE '" . daddslashes( str_replace( '*', '%', $v) ) . "' ";
            }
        } 
        $aResult = $this->oDB->getAll( "SELECT `userid` FROM `users` WHERE $sWhere ");
        $aReturn = array();
        if( $this->oDB->ar() )
        {
            foreach( $aResult as $v )
            {
                if( is_numeric($v['userid']) )
                {
                    $aReturn[] = $v['userid'];
                }
            }
        }
        return $aReturn;
    }



    /**
     * 获取活跃用户数 (for cli charts)
     * 活跃用户的定义: 14天无账变, 账户资金小于2元的
     * @param string $sDate   Y-m-d H:i:s 
     * @return mix
     */
    public function getActiveUserCount( $sDate = '' )
    {
        $sDate   = empty($sDate) ? date('Y-m-d 00:00:00') : daddslashes($sDate);
        $aResult = $this->oDB->getOne( "SELECT COUNT(ut.`userid`) AS TOMCOUNT FROM `usertree` AS ut LEFT JOIN `users` AS u ON (u.`userid` = ut.`userid`) WHERE u.`lasttime` >= '$sDate' AND ut.`isdeleted` = 0" );
        return ($this->oDB->ar()>0) ? $aResult['TOMCOUNT'] : 0;
    }



    /**
     * 获取全部用户数 (for cli charts)
     * @return mix
     */
    public function getAllUserCount()
    {
        $aResult = $this->oDB->getOne( " SELECT COUNT(ut.`userid`) AS TOMCOUNT FROM `usertree` AS ut LEFT JOIN `users` AS u ON (u.`userid` = ut.`userid`) WHERE ut.`isdeleted` = 0" );
        return ($this->oDB->ar()>0) ? $aResult['TOMCOUNT'] : 0;
    }



    /**
     * 后台管理员更新用户信息
     *
     * @param int $iUserid 用户ID
     * @param int $usertypechange 是否进行用户类型转化
     * @param string $sUsernick(用户昵称)
     * @author SAUL
     */
    function adminUpdateUserInfo( $iUserId ,$sUsernick, $bUserTypeChange = FALSE)
    {
        $aUserinfo = $this->getUserExtentdInfo($iUserId);
        if( empty($aUserinfo) )
        {//用户不存在或者是管理员
            return 0;
        }
        if( !$this->checkNickName($sUsernick) )
        {//验证昵称
            return -1;//用户昵称不正确
        }
        if( $aUserinfo['nickname'] != $sUsernick )
        {//更新昵称
            $this->oDB->doTransaction();
            $this->oDB->query("UPDATE `users` SET `nickname`='".daddslashes($sUsernick)."' 
                                 WHERE `userid`='".$iUserId."'");
            if( $this->oDB->errno() > 0 )
            {//更新用户昵称失败
                $this->oDB->doRollback();
                return -2;
            }
            $this->oDB->query("UPDATE `usertree` SET `nickname`='".daddslashes($sUsernick)."' 
                                 WHERE `userid`='".$iUserId."'");
            if( $this->oDB->errno() > 0 )
            {//更新用户昵称失败
                $this->oDB->doRollback();
                return -2;
            }
            
            //临时同步更新高频用户信息代码开始
            /*$sSql = " SELECT `gpuserid` FROM `tempusermap` WHERE `dpuserid` = '" . $iUserId ."'";
            $aTempUserMap = $this->oDB->getAll( $sSql );
            if( empty($aTempUserMap) )
            {
                $sGpSql = "UPDATE `users` SET `nickname` = '" .daddslashes($sUsernick) . "' WHERE `id` = '" . $iUserId. "'";
            }
            else
            {
                $aGpUserId = array();
                $sUserId = '';
                foreach ($aTempUserMap as $aUserId )
                {
                    $aGpUserId[] = $aUserId['gpuserid'];
                }
                $sUserId = implode(',',$aGpUserId);
                $sGpSql = "UPDATE `users` SET `nickname` = '" .daddslashes($sUsernick) . "' WHERE `id` IN (" . $sUserId. ")";
            }
            $oDB = new db($GLOBALS['aSysDbServer']['gaopin']);
            $oDB->query( $sGpSql );
            if( $oDB->errno() > 0 )
            {//更新用户昵称失败
                $this->oDB->doRollback();
                return -2;
            }*/
            //临时同步更新高频用户信息代码结束
            
            $this->oDB->doCommit();
        }
        //检测能不能进行转化
        if( $bUserTypeChange )
        {
            //检测自身的有没有下级
            $mIds = $this->getChildListID( $iUserId );
            //原始位置的判断(预测位置)
            if( $mIds=== FALSE )
            {
                $aUserPostion = $this->oDB->getone("SELECT `parentid`,`parenttree` FROM `usertree` 
                                                       WHERE `userid`='".$iUserId."'");
                if( $aUserPostion['parentid'] == 0 )
                {
                    $iPostion = 1;//总代
                    $iUserTop = $iUserId;//自身的TOP
                }
                elseif( $aUserPostion['parentid'] == $aUserPostion['parenttree'] )
                {
                    $iPostion = 2;//一代
                    $iUserTop = $aUserPostion['parentid'];
                }
                else 
                {
                    $iPostion  = 3;//其他
                    $aPostions = explode(',',$aUserPostion['parenttree']);
                    $iUserTop  = $aPostions[0];
                    unset($aPostions);
                }
                unset($aUserPostion);
                //允许修改
                //原始组信息
                $iUserType =( $aUserinfo['isspecial'] == 0)? $aUserinfo["groupid"] : $aUserinfo["isspecial"];
                if( $iUserType == 1 )//原始组为总代
                {
                    return -3;//总代暂时不能转化为会员，系统不支持
                }
                if( $iUserType == 4 )
                {
                    //用户转化为代理,需要查询自身所处的位置以及是否有特殊组
                    //查询特殊组存不存在
                    $aUserTeam =$this->oDB->getone("SELECT * FROM `usergroup` WHERE `teamid`='".$iUserTop."'"
                                    ." AND `isspecial`='".$iPostion."'");
                    if( empty($aUserTeam) )
                    {
                        $iTeam = $iPostion;
                    }
                    else 
                    {
                        $iTeam = $aUserTeam["groupid"];
                    }
                    //开始事务
                    $this->oDB->doTransaction();
                    //用户users 转化
                    $this->oDB->query( "UPDATE `users` SET `usertype`='1' WHERE `userid`='".$iUserId."'" );
                    if( $this->oDB->ar() < 1 )
                    {////更新用户表时候失败,事务取消
                        $this->oDB->doRollback();
                        return -4;
                    }
                    //usertree 转化
                    $this->oDB->query( "UPDATE `usertree` SET `usertype`='1' WHERE `userid`='".$iUserId."'" );
                    if( $this->oDB->ar() < 1 )
                    {//更新用户树失败,//事务取消
                        $this->oDB->doRollback();
                        return -5;
                    }
                    //userchannel 转化
                    $this->oDB->query( "UPDATE `userchannel` SET `groupid`='".$iTeam."' WHERE "
                            ."`userid`='".$iUserId."' AND `channelid`='0'" );
                    if( $this->oDB->ar() < 1 )
                    {//事务取消
                        $this->oDB->doRollback();
                        return -6;
                    }
                    //事务提交
                    $this->oDB->doCommit();
                    return 1;
                }
                else
                {
                    //代理转化为用户,需要查询有无特殊组
                    $aUserTeam =$this->oDB->getone("SELECT * FROM `usergroup` WHERE `teamid`='4'"
                                    ." AND `isspecial`='".$iPostion."'");
                    if( empty($aUserTeam) )
                    {
                        $iTeam = 4;
                    }
                    else 
                    {
                        $iTeam = $aUserTeam["groupid"];
                    }
                    $this->oDB->doTransaction();
                    //用户users 转化
                    $this->oDB->query( "UPDATE `users` SET `usertype`='0' WHERE `userid`='".$iUserId."' AND `userrank`='0'" );
                    if( $this->oDB->ar() < 1 )
                    {//事务取消
                        $this->oDB->doRollback();
                        return -4;
                    }
                    //usertree 转化
                    $this->oDB->query("UPDATE `usertree` SET `usertype`='0' WHERE `userid`='".$iUserId."'");
                    if( $this->oDB->ar() < 1 )
                    {//事务取消
                        $this->oDB->doRollback();
                        return -5;
                    }
                    //userchannel 转化
                    $this->oDB->query("UPDATE `userchannel` SET `groupid`='".$iTeam."' WHERE ".
                                      "`userid`='".$iUserId."' AND `channelid`='0'");
                    if( $this->oDB->ar() < 1 )
                    {//事务取消
                        $this->oDB->doRollback();
                        return -6;
                    }
                    //事务提交
                    $this->oDB->doCommit();
                    return 1;
                }//转化完毕
            }
            else 
            {//不允许修改
                return -7;//用户昵称转化成功，但是用户有下级不能进行转化
            }
        }
        else
        {//转化成功
            return 1;
        }
    }



    /**
     * 更新用户星级
     *
     * @param int $iUserId
     * @param int $iUserRank
     * @return int
     * @author SAUL 09/06/11
     */
    public function updateUserRank( $iUserId , $iUserRank )
    {
        $iUserId   = is_numeric($iUserId) ? intval($iUserId) : 0;
        $iUserRank = is_numeric($iUserRank) ? intval($iUserRank) : 0;
        if( $iUserId == 0 )
        {
            return -1;
        }
        //查找直接上级
        $iUserPid    = $this->getParentId( $iUserId, FALSE );
        //查找所有上级
        $sUserAllPid = $this->getParentId( $iUserId, TRUE );
        if( $iUserPid == 0 )
        {
            return -2;//总代不能授权
        }
        if( $iUserPid == intval($sUserAllPid) )
        { //一级代理
            $iMaxStar = 5;
        }
        else 
        { //直接上级
            $aUserTop = $this->oDB->getOne( "SELECT `userrank` FROM `users` WHERE `userid`='".$iUserPid."'" );
            if( empty($aUserTop) )
            {
                $iMaxStar = 0;
            }
            else 
            {
                $iMaxStar = $aUserTop["userrank"];
            }
        }
        if( $iMaxStar < $iUserRank )
        {
            return -3;//不能大于上级星级
        }
        //获取所有的下级的最大值
        $aArr = array();
        $aArr = $this->oDB->getone("SELECT `userrank` FROM `users` WHERE `userid` IN "
                ."(SELECT `userid` FROM `usertree` WHERE `parentid`='".$iUserId."') order by `userrank` DESC");
        if( empty($aArr) )
        {
            $iMinStar = 0;
        }
        else 
        {
            $iMinStar = $aArr["userrank"];
        }
        if( $iMinStar > $iUserRank )
        {//不能小于下级的最大星级，走的是快捷路径，直接读他的所有直接下级的最大
            return -4;
        }
        //更新建立时间
        $this->oDB->doTransaction();
        $this->oDB->query("UPDATE `users` SET `rankcreatetime`='" . date("Y-m-d H:i:s", time()) . "'"
        ." WHERE `userid`='".$iUserId."' AND `rankcreatetime` is NULL");
        if( $this->oDB->errno() > 0 )
        {
            $this->oDB->doRollback();
            return 0;
        }
        $this->oDB->query("UPDATE `users` SET `rankupdate`='" . date("Y-m-d H:i:s", time()) . "',`userrank`='".$iUserRank."'" 
        ."WHERE `userid`='".$iUserId."'");
        if( $this->oDB->errno() > 0 )
        {
            $this->oDB->doRollback();
            return 0;
        }
        $this->oDB->query("UPDATE `usertree` SET `userrank`='".$iUserRank."' WHERE `userid`='".$iUserId."'");
        if( $this->oDB->errno() > 0 )
        {
            $this->oDB->doRollback();
            return 0;
        }
        $this->oDB->doCommit();
        return 1;
    }



    /**
     * 检测用户和管理员之间的关系
     *
     * @param int $iAdminid
     * @param int $iUserId
     * @return BOOL
     * @author SAUL 20090520
     */
    public function checkAdminForUser( $iAdminId , $iUserId )
    {
        if( ($iAdminId <= 0) || ($iUserId < 0) )
        {
            return FALSE;
        }
        //查询是不是销售管理员
        $this->oDB->query("SELECT `groupid` FROM `admingroup` WHERE `groupid` =("
        ." SELECT `groupid` FROM `adminuser` WHERE `adminid`='".$iAdminId."') AND `issales`='0'");
        if( $this->oDB->ar() == 1 )
        {
            return TRUE;
        }
        else 
        {
            if( $iUserId == 0 )
            {//直接获取关系树
                return FALSE;
            }
            else 
            {//查询用户的总代和销售管理员之间的关系
                $this->oDB->query("SELECT `userid` FROM `usertree` WHERE `userid`='".$iUserId."' AND "
                 ."`lvtopid` IN (SELECT `topproxyid` FROM `adminproxy` WHERE `adminid`='".$iAdminId."')");
                if( $this->oDB->ar() == 1 )
                {
                    return TRUE;
                }
                else 
                {
                    return FALSE;
                }
            }
        }
    }



    /**
     * 根据用户以及需要统计的星级构成数组
     *
     * @param array $aUser
     * @param array $aType
     */
    public function & getRank( $aUser, $aType )
    {
        $aResult = array();
        if( !is_array($aUser) || !is_array($aType) )
        {
            return $aResult;
        }
        //用户整理
        foreach($aUser as $key => $value )
        {
            if( !is_numeric($value) )
            {
                unset( $aUser[$key] );
            }
        }
        //用户星级整理
        foreach( $aType as $key => $value )
        {
            if( !in_array($value, array(1,2,3,4,5)) )
            {
                unset( $aType[$key] );
            }
        }
        if( empty($aUser) || empty($aType) )
        {
            return $aResult;
        }
        return $this->oDB->getAll("SELECT u.`userid`,u.`username`,ut.`parentid`,u.`userrank` FROM `users` "
                ."as u LEFT JOIN `usertree` AS ut ON (u.`userid`=ut.`userid`) WHERE ut.`parentid` IN "
                ."(".join(",", $aUser).") AND u.`userrank` IN (".join(",", $aType).")");
         
    }



    /**
     * 获取不活跃用户列表
     *
     * @param integer $iDay
     * @param float $fMinMoney
     * @param float $fMaxMoney
     * 
     * @return Array
     */
    function getunActiveUser( $iDay , $fMinMoney , $fMaxMoney )
    {
        set_time_limit(0);
        $sSql = "SELECT u.`userid`,u.`username`,u.`lasttime`, uf.`cashbalance`,uf.`channelbalance`,"
        ."uf.`availablebalance`,uf.`holdbalance`,uf.`lastupdatetime`,d.`username` as topname from"
        ." `users` as u left join `userfund` as uf on (u.`userid`=uf.`userid`)"
        ." left join `usertree` as c on (u.`userid`=c.`userid`) left join `usertree` as d"
        ." on (c.`lvtopid`=d.`userid`) where u.`usertype`<'2'";
        //首先用户lasttime <指定日期
        if( !is_numeric($iDay) )
        {
            $iDay = 14;
        }
        $sDay  = date( "Y-m-d 00:00:00", strtotime('-'.$iDay."days") );
        $sSql .= " and u.`lasttime`<'".$sDay."'";
        //用户的帐变小于$fMaxMoney,大于$fMinMoney
        if( is_numeric($fMinMoney) && $fMaxMoney > 0.00 )
        {
            $fMinMoney = floatval($fMinMoney);
            $sSql     .= " and uf.`channelbalance`>='".$fMinMoney."'";
        }
        if( is_numeric($fMaxMoney) && $fMaxMoney > 0.00 )
        {
            $fMaxMoney = floatval($fMaxMoney);
            $sSql     .= " and uf.`channelbalance`<'".$fMaxMoney."'";
        }
        return $this->oDB->getAll($sSql);
    }

    
    
    /**
     * 对负余额用户资金进行清零 (认赔) 操作 [事务]
     *    - 账变类型 : 特殊金额整理 ORDER_TYPE_TSJEZL = 22
     * @author Tom
     * @return mix
     */
    function doFixZeroUser( $aData = array() )
    {
        // 1, 数据检查
        if( !is_array($aData) || empty($aData) )
        {
            return -1000; // 数据不正确
        }
        $iSuccessedCount = 0; // 成功处理数量
        foreach ( $aData AS $v )
        {
            if( !is_numeric($v) )
            { // 非数字用户ID进行忽略
                continue;
            }
            // 1, 锁用户资金
            if( TRUE !== $this->doLockUserFund( $v, TRUE ) )
            {
                continue;
            }
            // 2, 事务开始
            if( FALSE == $this->oDB->doTransaction() ) 
            {
                $this->doLockUserFund( $v, FALSE ); // 解锁
                return -2001; // 事务开始. 失败
            }

            if( TRUE !== $this->doFixZeroUserByUid($v) ) // 业务流程
            {
                $this->doLockUserFund( $v, FALSE ); // 解锁
                if( FALSE == $this->oDB->doRollback() )
                { 
                    return -2002; // 事务回滚. 失败
                }
                continue; // 继续处理下一条
            }
            else 
            {
                $this->doLockUserFund( $v, FALSE ); // 解锁
                if( FALSE == $this->oDB->doCommit() )
                { // 事务提交发生失败. 
                    return -2003;
                }
                $iSuccessedCount++;
            }
        }
        return $iSuccessedCount;
    }

    
    /**
     * 对传递的用户ID执行清零(认赔操作,嵌套在事务中)
     * @param int $iUserId
     * @return TRUE | 账变 addOrders 产生的负数
     */
    private function doFixZeroUserByUid( $iUserId = 0 )
    {
        // 获取用户负余额金额 (频道余额)
        $oUserFund = A::singleton('model_userfund');
        $aRows = $oUserFund->getFundByUser( $iUserId, 'channelbalance', SYS_CHANNELID, FALSE );
        $fMoney = floatval( $aRows['channelbalance'] );
        if( $fMoney >= 0 )
        {
            return -1002; // 频道资金不为负, 无需执行清理
        }

        // 加账变
        /* @var $oOrders model_orders */
        $oOrders = A::singleton('model_orders');
        $aOrders = array();
        $aOrders['iFromUserId'] = $iUserId;
        $aOrders['iOrderType']  = ORDER_TYPE_TSJEZL;
        $aOrders['fMoney']      = abs($fMoney);
        $aOrders['iAdminId']    = $_SESSION['admin'];
        $mFlag = $oOrders->addOrders( $aOrders );
        return $mFlag;
    }


    /**
     * 锁定/解锁用用户资金
     *
     * @access private
     * @param  int       $iUserId      用户Id
     * @param  boolean   $bIsLocked    是否锁定
     * @return boolean
     * 
     */
    private function doLockUserFund( $iUserId, $bIsLocked = TRUE )
    {
        if( FALSE == $this->oDB->doTransaction() )
        { // 事务处理失败
            return FALSE;
        }
        /* @var $oUserFund model_userfund */
        $oUserFund = A::singleton('model_userfund');
        if( intval($oUserFund->switchLock( $iUserId , SYS_CHANNELID, (BOOL)$bIsLocked)) != 1 )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return FALSE;
            }
            return FALSE;
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 获取负余额用户
     * @author SAUL
    */
    function getUnderZeroUser()
    {
        $sSql = "SELECT uf.`cashbalance`,uf.`channelbalance`,uf.`availablebalance`, uf.`holdbalance`,
             ut.`userid`,ut.`username`,ut.`isfrozen`, ut2.`username` AS `topname`
             FROM `userfund` AS uf LEFT JOIN `usertree` as ut on (uf.`userid` = ut.`userid`)
             LEFT JOIN `usertree` as ut2 on (ut.`lvtopid`=ut2.`userid`) 
             WHERE uf.`channelbalance`<0 OR uf.`holdbalance`<0 OR (uf.`cashbalance`<0 AND ut.`parentid`!=0)
             ORDER BY ut.`lvtopid`";
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 更新用户级别
     *
     * @param integer $iUserId
     * @param integer $iTeamId
     */
    function updateUserTeam( $iUserId, $iTeamId )
    {
        if(!is_numeric($iUserId)||!is_numeric($iTeamId))
        {
            return false;
        }
        $aUserCheck = $this->oDB->getOne("SELECT UC.`groupid`,UG.`teamid` FROM `userchannel` AS UC "
        ."left join `usertree` AS UT on (UT.`userid`=UC.`userid`)"
        ."left join `usergroup` AS UG on (UG.`groupid`=UC.`groupid`)"
        ." WHERE UC.`userid`='".$iUserId."' AND UC.`channelid`='0' AND UT.`parentid`='0'");
        if(empty($aUserCheck))
        { //旧的总代用户信息
            return false;
        }
        $aTeamCheck = $this->oDB->getOne("SELECT * FROM `usergroup` "
        ."where `teamid`='".$iTeamId."'and (`isspecial`='1' or `groupid`='1')");
        if(empty($aTeamCheck))
        {
            return false;
        }
        if( $aUserCheck["groupid"] == $aTeamCheck["groupid"] )
        { //原本就相等
            return TRUE;
        }
        //获取原来所有的相关Old Key=> New Key 
        $aTeam = $this->oDB->getAll("SELECT groupid,teamid,isspecial FROM `usergroup`"
        ." where `teamid` IN (SELECT `teamid` FROM `usergroup` where `groupid`"
        ." in (".$aTeamCheck["groupid"].",".$aUserCheck["groupid"]."))");
        //数据整理
        $aTranTeam = array();
        foreach( $aTeam as $team )
        {
            $i = ($team["isspecial"]==0)?$team["groupid"]:$team["isspecial"];
            $aTranTeam[$team["teamid"]][$i] = $team["groupid"];
        }
        $this->oDB->doTransaction();
        foreach($aTranTeam[$aUserCheck["teamid"]] as $i=>$v)
        {
            $this->oDB->query("UPDATE `userchannel` AS UC "
            ."LEFT JOIN `usertree` AS UT ON (UT.`userid`=UC.`userid`)"
            ." SET UC.`groupid`='".$aTranTeam[$iTeamId][$i]."'"
            ." WHERE UC.`groupid`='".$v."' and UT.`usertype`<'2'"
            ." AND UT.`lvtopid`='".$iUserId."' AND UC.`channelid`='0'");
            if($this->oDB->errno()>0)
            {
                $this->oDB->doRollback();
                return false;
            }
        }
        if( $this->oDB->doCommit() )
        {
            return true;
        }
        else
        {
            return false;
        }
    }



    /** 
     * 增加用户虚拟数据，（20个总代，5级，单条数一级5个用户，共78100个用户）
     */
    public function addTestUsers()
    {
        //下增加20个总代
        for( $i=0; $i<20; $i++ )
        {
            $data = array();
            $data['username']   = 'tjames'.$i;
            $data['loginpwd']   = '123456';
            $data['usertype']   = 1;    //分组
            $data['nickname']   = 'tjames'.$i;
            $data['addcount']   = 10;
            $groupID            = 1;    //用户组：总代组
            $parentID           = 0;
            $tempID1 = $this->insertUser( $data, $groupID, $parentID );
            if( empty($tempID1) || $tempID1 < 0 )
            {//写入数据失败
                    echo "写入总代用户第".$i."个失败";
                    exit;
            }
            /****写入一代****/
            for( $j=0; $j<5; $j++ )
            {
                $data = array();
                $data['username']   = 'tjames'.$i."f".$j;
                $data['loginpwd']   = '123456';
                $data['usertype']   = 1;    //分组
                $data['nickname']   = 'tjames'.$i."f".$j;
                $data['addcount']   = 10;
                $groupID            = 2;    //用户组：一代组
                $parentID           = $tempID1;
                $tempID2 = $this->insertUser( $data, $groupID, $parentID );
                if( empty($tempID2) || $tempID2 < 0 )
                {//写入数据失败
                        echo "写入一代用户第".$i."_".$j."个失败";
                        exit;
                }
                //增加二级代理
                for( $k=0; $k<5; $k++ )
                {
                    $data = array();
                    $data['username']   = 'tjames'.$i."f".$j.$k;
                    $data['loginpwd']   = '123456q';
                    $data['usertype']   = 1;    //分组
                    $data['nickname']   = 'tjames'.$i."f".$j.$k;
                    $groupID            = 3;    //用户组：普通组
                    $data['addcount']   = 10;
                    $parentID           = $tempID2;
                    $tempID3 = $this->insertUser( $data, $groupID, $parentID );
                    if( empty($tempID3) || $tempID3 < 0 )
                    {//写入数据失败
                            echo "写入二代用户第".$i."_".$j."_".$k."个失败";
                            exit;
                    }
                    //增加三级代理
                    for( $l=0; $l<5; $l++ )
                    {
                        $data = array();
                        $data['username']   = 'tjames'.$i."f".$j.$k.$l;
                        $data['loginpwd']   = '123456q';
                        $data['usertype']   = 1;    //分组
                        $data['nickname']   = 'tjames'.$i."f".$j.$k.$l;
                        $groupID            = 3;    //用户组：普通组
                        $data['addcount']   = 10;
                        $parentID           = $tempID3;
                        $tempID4 = $this->insertUser( $data, $groupID, $parentID );
                        if( empty($tempID4) || $tempID4 < 0 )
                        {//写入数据失败
                                echo "写入三代用户第".$i."_".$j."_".$k."_".$l."个失败";
                                exit;
                        }
                        //增加四级代理
                        for( $m=0; $m<5; $m++ )
                        {
                            $data = array();
                            $data['username']   = 'tjames'.$i."f".$j.$k.$l.$m;
                            $data['loginpwd']   = '123456q';
                            $data['usertype']   = 1;    //分组
                            $data['nickname']   = 'tjames'.$i."f".$j.$k.$l.$m;
                            $data['addcount']   = 10;
                            $groupID            = 3;    //用户组：普通组
                            $parentID           = $tempID4;
                            $tempID5 = $this->insertUser( $data, $groupID, $parentID );
                            if( empty($tempID5) || $tempID5 < 0 )
                            {//写入数据失败
                                    echo "写四代用户第".$i."_".$j."_".$k."_".$l."_".$m."个失败";
                                    exit;
                            }
                            //增加五级代理
                            for( $n=0; $n<5; $n++ )
                            {
                                $data = array();
                                $data['username']   = 'tjames'.$i."f".$j.$k.$l.$m.$n;
                                $data['loginpwd']   = '123456q';
                                $data['usertype']   = 1;    //分组
                                $data['nickname']   = 'tjames'.$i."f".$j.$k.$l.$m.$n;
                                $data['addcount']   = 10;
                                $groupID            = 3;    //用户组：普通组
                                $parentID           = $tempID5;
                                $tempID6 = $this->insertUser( $data, $groupID, $parentID );
                                if( empty($tempID6) || $tempID6 < 0 )
                                {//写入数据失败
                                        echo "写五代用户第".$i."_".$j."_".$k."_".$l."_".$m."_".$n."个失败";
                                        exit;
                                }
                                //增加六级代理
                                for( $o=0; $o<5; $o++ )
                                {
                                    $data = array();
                                    $data['username']   = 'tjames'.$i."f".$j.$k.$l.$m.$n.$o;
                                    $data['loginpwd']   = '123456q';
                                    $data['usertype']   = 1;    //分组
                                    $data['nickname']   = 'tjames'.$i."f".$j.$k.$l.$m.$n.$o;
                                    $data['addcount']   = 10;
                                    $groupID            = 3;    //用户组：普通组
                                    $parentID           = $tempID6;
                                    $tempID7 = $this->insertUser( $data, $groupID, $parentID );
                                    if( empty($tempID7) || $tempID7 < 0 )
                                    {//写入数据失败
                                            echo "写六代用户第".$i."_".$j."_".$k."_".$l."_".$m."_".$n."_".$o."个失败";
                                            exit;
                                    }
                                    //增加用户
                                    for( $p=0; $p<3; $p++ )
                                    {
                                        $data = array();
                                        $data['username']   = 'tjames'.$i."f".$j.$k.$l.$m.$n.$o.$p;
                                        $data['loginpwd']   = '123456q';
                                        $data['usertype']   = 0;    //分组
                                        $data['nickname']   = 'tjames'.$i."f".$j.$k.$l.$m.$n.$o.$p;
                                        $data['addcount']   = 1;
                                        $groupID            = 4;    //用户组：普通组
                                        $parentID           = $tempID7;
                                        $tempID8 = $this->insertUser( $data, $groupID, $parentID );
                                        if( empty($tempID8) || $tempID8 < 0 )
                                        {//写入数据失败
                                                echo "写用户第".$i."_".$j."_".$k."_".$l."_".$m."_".$n."_".$o.$p."个失败";
                                                exit;
                                        }       
                                    }       
                                }       
                            }       
                        }
                    }
                }
            }
        }
    }
    /**
     * 高低频并行期间临时程序
     * 
     * 获取高频总代在低频上对应的用户ID
     * int $iUserId  高频总代ID
     * @author mark
     */
    /*public function getGpTopporyidOndp( $iUserId = 0 )
    {
        if( !isset( $iUserId ) )
        {
            return array();
        }
        $iUserId = intval($iUserId);
        $sSql = " SELECT dpuserid FROM `tempusermap` WHERE `gpuserid`='".$iUserId."' LIMIT 1 ";
        return $this->oDB->getOne( $sSql );
    }*/
    
    
    /**
     * 获取低频开户成功，高频开启失败的用户信息
     * int  $iPageRecord    分页参数
     * int  $iCurrentPage   分页参数
     * int  $sCondition     查询条件
     * @author mark
     */
    /*public function getErrorUser( $iPageRecord = 0, $iCurrentPage = 0, $sCondition = '1' )
    {
        $sFields = isset($sFields) ? $sFields : "*";
        $sTable = "tmperroruser";
        $sCondition = isset($sCondition) ? $sCondition : " 1 ";
        if( $iPageRecord == 0 )
        {
	       $sSql = " SELECT u.`userid`,u.`username`,u.`nickname`,u.`loginpwd`,u.`securitypwd`,u.`usertype`,ut.`parentid`,ut.`lvproxyid`   
	                 FROM `users` AS u LEFT JOIN `usertree` AS ut ON (u.`userid` = ut.`userid`) 
	                 WHERE " . $sCondition;
           $aUserInfo = $this->oDB->getOne( $sSql );
           if( $aUserInfo['userid'] == $aUserInfo['lvproxyid'] )//如果为一代,处理父ID可能是合并总代的ID
           {
               $sSql = " SELECT `gpuserid` FROM  `tempusermap` 
                        WHERE `dpuserid` = '" . $aUserInfo['parentid'] . "' AND `status` = 1";
               $aTmpUserMap = $this->oDB->getOne( $sSql );
               if( !empty($aTmpUserMap) )
               {
                   $aUserInfo['parentid'] = $aTmpUserMap['gpuserid'];
               }
           }
           return $aUserInfo;
        }
        else
        {
            return $this->oDB->getPageResult( $sTable, $sFields, $sCondition, $iPageRecord, $iCurrentPage);
        }
    }*/
    
    
    /**
     * 更新同步失败用户信息
     * int $iUserId     用户ID
     * string $sAction 更新类别
     * @author maark
     */
   /* public function updateErrorUser( $iUserId = 0, $sAction = 'changestaus' )
    {
        if ( empty($iUserId) || !is_numeric($iUserId) || $iUserId == 0 )
        {
            return -1;//参数不正确
        }
        $iUserId = intval($iUserId);
        if( !in_array($sAction,array('changestaus','changeoperatimes')) || empty($sAction) )
        {
            return -1;//参数不正确
        }
        if( $sAction == 'changestaus' )//更新状态和操作次数
        {
            $sSql =  " UPDATE `tmperroruser` SET `status` = 1,`operatimes` = `operatimes` + 1 
                        WHERE `userid` = '" . $iUserId. "'";
        }
        elseif ( $sAction == 'changeoperatimes' )//更新操作次数
        {
            $sSql =  " UPDATE `tmperroruser` SET `operatimes` = `operatimes` + 1 
                        WHERE `userid` = '" . $iUserId. "'";
        }
        return $this->oDB->query( $sSql );
    }*/
    
    /**
     * 获取高频主账户ID
     *
     * @param int $iUserId	低频、银行边用户ID
     * @return int $UserId 高频主账户ID
     * 
     *  2/21/2010
     */
    /*public function getGPmainUID($iUserId){
    	$sSql = " SELECT `gpuserid` FROM `tempusermap` WHERE `dpuserid` = '" . $iUserId ."' and status = 1";
        $aTempUserMap = $this->oDB->getOne( $sSql );
        if( !empty($aTempUserMap) )
        {
            return $aTempUserMap['gpuserid']; 
        }
        else
        {
         	return $iUserId;
        }
    }*/
    
    /**
     * 获取低频账户ID
     *
     * @param int $iUserId	高频API传入操作用户ID
     * @return int $UserId  对应低频账户ID
     * 
     *  2/25/2010
     */
	/*public function getDPmainUID($iUserId){
    	$sSql = " SELECT `dpuserid` FROM `tempusermap` WHERE `gpuserid` = '" . $iUserId ."' and status = 1";
        $aTempUserMap = $this->oDB->getOne( $sSql );
        if( !empty($aTempUserMap) )
        {
            return $aTempUserMap['dpuserid']; 
        }
        else
        {
         	return $iUserId;
        }
    }*/
    
    
    /**
     * 检查用户是否有权限使用在线充值 提现功能,
     *
     * @param int $iUserId  用户ID
     * @return bool
     * 
     */
    public function checkAuthUserPayment($iUserId){
    	if ( !is_numeric($iUserId) || ($iUserId < 0) ) return false;
    	
    	$sSql = "SELECT `userid`,`lvtopid`,`parenttree` FROM `usertree` WHERE `userid`=".$iUserId;
    	$aRe = $this->oDB->getOne($sSql);
    	$iTopId = $aRe['lvtopid'] ? $aRe['lvtopid'] : false;
    	if($iTopId === false) return false;
    	
    	$iLevel = substr_count($aRe['parenttree'],',')+1;
    	$iLevel = ($iTopId == $aRe['userid']) ? ($iLevel = 0) : $iLevel;
    	
    	$sSql2 = "SELECT `proxykey`,`proxyvalue` FROM `topproxyset` WHERE  (`proxykey`='can_payment' OR `proxykey`='payment_level') AND `userid`=".$iTopId;
    	$aRebAll = $this->oDB->getAll($sSql2);

    	$bA = $bB = false;
    	foreach ($aRebAll AS $aReb ){
    		
    		if( $aReb['proxykey'] == 'can_payment'){
    			if ( $aReb['proxyvalue'] == '1' ) {
    				$bA = true;
    			}else{
    				$bA = false;	
    			}
    			
    		}elseif ($aReb['proxykey'] == 'payment_level') {
    			if( $aReb['proxyvalue'] >= $iLevel ){
    				$bB = true;
    			}else{
    				$bB = false;
    			}
    			
    		}else{
    			return false;
    		}
    		
    	}
    	return $bA && $bB;
    }
    
    /**
     * 测试用户是否被许可某银行EMAIL 快速银行充值
     * 	 (总代层级权限设置)
     *   Email充值/银行快速充值
     * 
     * @param int	$iUserId  	用户ID
     * @param int 	$iBankId	银行ID
     * 
     * @return bool 
     */
    
	public function checkDepositAllow($iUserId, $iBankId){
    	if ( !is_numeric($iUserId) || ($iUserId < 0) || !is_numeric($iBankId) || ($iBankId < 0) ) return false;
    	
    	$sSql = "SELECT `userid`,`lvtopid`,`parenttree` FROM `usertree` WHERE `userid`=".$iUserId;
    	$aRe = $this->oDB->getOne($sSql);
    	$iTopId = $aRe['lvtopid'] ? $aRe['lvtopid'] : false;
    	if($iTopId === false) return false;
    	
    	$iLevel = substr_count($aRe['parenttree'],',')+1;
    	$iLevel = ($iTopId == $aRe['userid']) ? ($iLevel = 0) : $iLevel;
    	$sKey1 = 'can_deposit_'.$iBankId;
    	$sKey2 = 'deposit_level_'.$iBankId;
    	$sSql2 = "SELECT `proxykey`,`proxyvalue` FROM `topproxyset` WHERE  (`proxykey`='".$sKey1."' OR `proxykey`='".$sKey2."') AND `userid`=".$iTopId;
    	$aRebAll = $this->oDB->getAll($sSql2);

    	$bA = $bB = false;
    	foreach ($aRebAll AS $aReb ){
    		
    		if( $aReb['proxykey'] == $sKey1){
    			if ( $aReb['proxyvalue'] == '1' ) 
    			{
    				$bA = true;
    			}else{
    				$bA = false;	
    			}
    			
    		}elseif ($aReb['proxykey'] == $sKey2) {
    			if( $aReb['proxyvalue'] >= $iLevel ){
    				$bB = true;
    			}else{
    				$bB = false;
    			}
    			
    		}else{
    			return false;
    		}
    		
    	}
    	return $bA && $bB;
    }
    
    
	/**
     * 获取用户所属总代，对应银行，被许可的快速银行充值层级
     * 	Email充值/银行快速充值
     * 
     * @param $iTopid	用户所属总代ID
     * @param $iBankId	受付银行ID
     * 
     * @return 		int/bool 可使用在线充值的层级数, FALSE总代也不可以使用
     */
    public function getDepositAllowLv($iTopId, $iBankId)
    {
    	//提取系统中使用的受付银行列表, 检查银行ID
		$oDeposit	= new model_deposit_depositlist(array(),'','array');
    	$aBankId 	= $oDeposit->getDepositArray();
		
		$iBankId = intval($iBankId);
		if (  !is_numeric($iBankId) || $iBankId <= 0 || $iTopId <= 0 
			|| array_search( $iBankId, $aBankId) === FALSE )
		{
			return FALSE;
			EXIT;
		}
		
    	$sProxykey1 = 'can_deposit_'.$iBankId;
		$sProxykey2 = 'deposit_level_'.$iBankId;
		
    	$sSql2 = "SELECT `proxykey`,`proxyvalue` FROM `topproxyset` WHERE  (`proxykey`='".$sProxykey1."' OR `proxykey`='".$sProxykey2."') AND `userid`=".$iTopId;
    	$aAll = $this->oDB->getAll($sSql2);
    	foreach ($aAll AS $aReb ){
    		
    		if( $aReb['proxykey'] == $sProxykey1 )
    		{
    			if ( $aReb['proxyvalue'] != '1' )	return FALSE;
    			
    		}
    		elseif ($aReb['proxykey'] == $sProxykey2 ) 
    		{
    			return $aReb['proxyvalue'];
    			
    		}else{
    			return FALSE;
    		}
    		
    	}
    }
    
    
    /**
     * 获取许可的在线充值级别
     * 
     * @param $iTopid	用户所属总代ID
     * 
     * @return 		int/bool 可使用在线充值的层级数, FALSE总代也不可以使用
     */
    public function getAuthOnlineLoadLevel($iTopId)
    {
    	$sSql2 = "SELECT `proxykey`,`proxyvalue` FROM `topproxyset` WHERE  (`proxykey`='can_payment' OR `proxykey`='payment_level') AND `userid`=".$iTopId;
    	$aAll = $this->oDB->getAll($sSql2);
    	foreach ($aAll AS $aReb ){
    		
    		if( $aReb['proxykey'] == 'can_payment')
    		{
    			if ( $aReb['proxyvalue'] != '1' )	return FALSE;
    			
    		}
    		elseif ($aReb['proxykey'] == 'payment_level') 
    		{
    			return $aReb['proxyvalue'];
    			
    		}else{
    			return FALSE;
    		}
    		
    	}
    }
    
    /**
     * 检查用户与支付分账户绑定关系
     *
     * @param int $iPayAccId  支付接口分账户ID
     * @param  int $iUserId	  用户所属总代，一代 array()
     * @return bool
     */
	public function checkUserPayportAccount($iPayAccId,$aUserId){
		if ( !is_numeric($iPayAccId) || !is_array($aUserId) ) return false;
		$iUserIdL1 = intval($aUserId[0]);
		$iUserIdL2 = intval($aUserId[1]);
		
		// 判断总代使用时，iUserIdL1为0; 不查询第一次
		if ($iUserIdL1 > 0){
			$sSql = 'SELECT `isactive` FROM `user_payport_limit` WHERE `pp_acc_id`='.$iPayAccId.' AND `user_id`='.$iUserIdL1;
    		$re = $this->oDB->getOne($sSql);
    		
		}else{
			$re = NULL;
		}

		if  ( !empty($re) ) {
    		return $tmp = ($re['isactive'] == 1) ? true : false;
		}else{
			
			$sSql = 'SELECT `isactive` FROM `user_payport_limit` WHERE `pp_acc_id`='.$iPayAccId.' AND `user_id`='.$iUserIdL2;
    		$re2 = $this->oDB->getOne($sSql);
    		return $tmp = ($re2['isactive'] == 1) ? true : false;
		}
		
    }
    
    
    /**
     * 获取总代分配给总代销售管理员的用户团队
     *
     * @version 	v1.0	2010-06-02
     * @author 		louis
     * 
     * @param 		int		$iAdminId		// 总代销售管理员id
     * @return 		array	array			// 用户团队
     */
    public function getAdminTeam( $iAdminId ){
    	$aAdminTeam = array();
    	// 数据检查
    	if (!is_numeric($iAdminId) || $iAdminId <= 0 ) return $aAdminTeam;
    	
    	// 检查是否是销售管理员
    	if (false == $this->IsAdminSale($iAdminId)) return $aAdminTeam;
    	
    	
    	$sSql = "";
    	$sSql .= "SELECT u.`username` ";
    	$sSql .= " FROM `useradminproxy` AS uap LEFT JOIN `users`  AS u ON uap.`topproxyid` = u.`userid` ";
    	$sSql .= " WHERE uap.`adminid` = '{$iAdminId}'";
    	$aAdminTeam = $this->oDB->getAll($sSql);
    	$aResult = array();
    	if (!empty($aAdminTeam)){
    		foreach ($aAdminTeam as $v){
    			$aResult[] = $v['username'];
    		}
    	}
    	return $aResult;
    }
   
    /** 
     * 更新用户设置的模板风格
     * @author jack
     * @param int $iUserId  用户id
     * @param string $sData 需要更新的字段
     * @return mix  用户设置的模板风格
     */
    public function updateUserView( $iUserId, $sData )
    {
    	if(!is_numeric($iUserId) || $iUserId <= 0)
    	{
    		return FALSE;
    	}
    	$sSql = "";
    	$sSql .= "UPDATE `users` SET `skin` = '".$sData."' WHERE `userid` = ".$iUserId;
    	$this->oDB->query($sSql);
    	if($this->oDB->errno() >0)
    	{
    		return FALSE;
    	}
    	return TRUE;
    }
    
    /**
     * 获取用户设置的模板风格
     * @author jack
     * @param int $iUserId  用户id
     * @return array array  用户设置的模板风格
     */
    public function getUserView( $iUserId )
    {
    	if(!is_numeric($iUserId) || $iUserId <= 0)
    	{
    		return -1;
    	}
    	$sSql = "";
    	$sSql = "SELECT `skin` FROM `users` WHERE `userid` =  ".$iUserId;
    	$aResult = $this->oDB->getOne($sSql);
    	if($this->oDB->errno() > 0 )
    	{
    	    return FALSE;
    	}
    	return $aResult;
    }
    
    
    /**
     * 获取所有总代 (无WHERE条件)
     * 
     * @time 9/7/2010 
     * @return array 总代用户名,ID对应数组
     */
    public function getTopUserArray()
    {
    	$sSql = "SELECT userid,username FROM usertree WHERE parentid=0";
    	$aResult = $this->oDB->getAll($sSql);
    	if($this->oDB->errno() > 0 )
    	{
    	    return FALSE;
    	}
    	else 
    	{
    		$aReturn = array();
    		foreach ($aResult AS $aRe)
    		{
    			$aReturn[$aRe['userid']] = $aRe['username'];
    		}
    		
    		return $aReturn;
    	}
    	
    	
    }
    
    /**
     * 批量取用户名
     */
    function getUsersById($ids)
    {
        if (!is_array($ids))
        {
            return false;
        }
        if (is_array($ids) && empty($ids))
        {
            return array();
        }
        $sSql = "SELECT userid,username FROM usertree WHERE userid IN (".implode(',', $ids).")";
        $result = array();
        foreach ($this->oDB->getAll($sSql) as $v)
        {
            $result[$v['userid']] = $v['username'];
        }
        
        return $result;
    }
}
?>