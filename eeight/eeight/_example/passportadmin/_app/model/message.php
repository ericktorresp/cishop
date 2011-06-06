<?php
/**
 * 文件 : /_app/model/message.php
 * 功能 : 模型 - 消息管理
 * 
 * 完成度: 99% 
 * 
 * @author	    Tom
 * @version    1.0.0
 * @package    passportadmin
 * @since      2009-05-14 13:06
 */

// TODO: 重要, 必须与数据库中 adminmenu.title=阅读全部消息 的ID匹配
define( 'MESSAGE_CAN_READ_ALL', 155 );

class model_message extends basemodel 
{
	/**
	 * 构造函数
	 * @return	void
	 */
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}



	/**
	 * 根据管理员身份, 获取可以读取的消息类型
	 * @author tom 090519 07:29
	 * @param string $sReturn    'opts' | 'array'  获取结果集类型
	 * @param string $sSelect    html.select.options 选中的结果
	 * @return mix
	 */
	public function getMessageTypeByAdminMenus( $sReturn='opts', $sSelect = '' )
	{
	    // 1, 获取全部消息类型
	    $aAllMsgType = $this->getAllMessageType();
	    $aTypeArray = array();
	    foreach( $aAllMsgType AS $v )
	    {
	        $aTypeArray[] = $v['menuid'];
	    }
	    
	    // 2, 获取管理员菜单权限
	    $oAdminUser = new model_adminuser();
	    $aAdminMenus = $oAdminUser->getAdminUserMenus($_SESSION['admin']);
	    
	    // 3, 计算权限交集, 超管则可以阅读所有消息类型, 重要!
	    if( !in_array(MESSAGE_CAN_READ_ALL, $aAdminMenus) ) 
	    { // 非超级管理员, 只生成其可读的消息类型 options [数组交集]
	        $aTypeArray = array_intersect( $aAdminMenus, $aTypeArray );
	    }
	    $aTypeArray = array_unique($aTypeArray); // 唯一值过滤
	    if( $sReturn != 'opts' )
	    { // 如果需要数组, 则直接返回
	        return $aTypeArray;
	    }
	    $sReturn = '';
	    
	    //4, 生成 options 字符
	    $aAllMsgTypeNew = array();
	    foreach( $aAllMsgType AS $v )
	    {
	        $aAllMsgTypeNew[ $v['menuid'] ] = $v['title'];
	    }
	    unset($aAllMsgType); // 就近释放
	    foreach( $aTypeArray AS $v )
	    {
	        if( $sSelect != $v )
	        {
	            $sReturn .= '<OPTION VALUE="'.$v.'">'. $aAllMsgTypeNew[$v] .'</OPTION>';
	        }
	        else 
	        {
	            $sReturn .= '<OPTION SELECTED VALUE="'.$v.'">'. $aAllMsgTypeNew[$v] .'</OPTION>';
	        }
	    }
	    return $sReturn;
	}
	// -------------------------------------------------------------------------



	/**
	 * 返回全部的消息类型
	 * @author tom 090519 07:32
	 * @return array
	 */
	public function getAllMessageType()
	{
	    return $this->oDB->getAll("SELECT * FROM `msgtype` ");
	}
	// -------------------------------------------------------------------------



	/**
	 * 获取消息列表
	 * @author tom 090519 07:32
	 * @param string $sFields
	 * @param string $sCondition
	 * @param int    $iPageRecords
	 * @param int    $iCurrPage
	 * @return array
	 */
	public function & getMessageList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1)
	{
	    $sTableName = ' `msglist` l LEFT JOIN `msgcontent` c ON l.`msgid`=c.`id` LEFT JOIN `msgtype` t ON t.`id`=c.`msgtypeid` '.
	    				' LEFT JOIN `users` ua ON c.`senderid`=ua.`userid` LEFT JOIN `users` ub ON l.`receiverid`=ub.`userid` ';
	    $sFields    = ' *, ua.username as sendername, ub.username as receivename ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, ' ORDER BY `entry` desc ' );
	}
	// -------------------------------------------------------------------------



	/**
	 * 获取用户消息列表[前台调用]
	 * @access 	public
	 * @author 	james	09/06/03
	 * @param 	int		$iUserId	//用户ID
	 * @param 	string	$sFields	//要读取的字段信息
	 * @param 	string	$sAndWhere	//附加搜索条件
	 * @param 	int		$iChannelId	//频道ID
	 * @return 	array	//返回消息列表
	 */
	public function & getUserMessageList( $iUserId, $sFields="", $sAndWhere="", $iChannelId=0 )
	{
		$aResult = array();
		if( empty($iUserId) || !is_numeric($iUserId) || !is_numeric($iChannelId) )
		{
			return $aResult;
		}
		$iUserId	= intval( $iUserId );
		$iChannelId = intval( $iChannelId );
		$sFields = empty($sFields) ? " l.`entry`,c.`subject`,t.`title`,c.`sendtime` " : $sFields;
		$sCondition = " l.`receiverid`='".$iUserId."' AND l.`receivergroup`='0' AND c.`channelid`='".$iChannelId."' ".$sAndWhere;
		$sSql = " SELECT ".$sFields." FROM `msglist` AS l LEFT JOIN `msgcontent` AS c ON l.`msgid`=c.`id` 
				  LEFT JOIN `msgtype` AS t ON c.`msgtypeid`=t.`id` WHERE ".$sCondition;
		return $this->oDB->getAll( $sSql );
	}



	/**
	 * 读取一条消息[前台调用]
	 * @author 	james	09/06/03
	 */
	public function & getOneUserMessage( $iMsgId, $iUserId, $sFields="", $sAndWhere="", $iChannelId=0 )
	{
		$aResult = array();
		if( empty($iMsgId) || !is_numeric($iMsgId) || empty($iUserId) || !is_numeric($iUserId) || !is_numeric($iChannelId) )
		{
			return $aResult;
		}
		$iMsgId		= intval( $iMsgId );
		$iUserId	= intval( $iUserId );
		$iChannelId = intval( $iChannelId );
		$sFields = empty($sFields) ? " l.`entry`,c.`subject`,c.`content`,t.`title`,c.`sendtime` " : $sFields;
		$sCondition = " l.`entry`='".$iMsgId."' AND l.`receiverid`='".$iUserId."' AND l.`receivergroup`='0' 
						AND c.`channelid`='".$iChannelId."' ".$sAndWhere;
		$sSql = " SELECT ".$sFields." FROM `msglist` AS l LEFT JOIN `msgcontent` AS c ON l.`msgid`=c.`id` 
				  LEFT JOIN `msgtype` AS t ON c.`msgtypeid`=t.`id` WHERE ".$sCondition;
		$aResult = $this->oDB->getOne( $sSql );
		return $aResult;
	}



	/**
	 * 删除管理员的消息 (需判断管理员可操作的消息类型,进行删除)
	 * @author tom 090519 07:33
	 * @param array $aHtmlCheckBox
	 * @return int
	 */
	public function delAdminMessage( $aHtmlCheckBox = array() )
	{
	    $sWhere = '';
        // 1, 整理被删除数据ID
        $sDelMessageIds = '';
	    if( !is_array($aHtmlCheckBox) || empty($aHtmlCheckBox) )
        {
            return FALSE;
        }
        foreach( $aHtmlCheckBox as $v )
        {
            if( is_numeric($v) )
            {
                $sDelMessageIds .= $v.",";
            }
        }
        if( substr($sDelMessageIds, -1, 1) == ',' )
        {
            $sDelMessageIds = substr( $sDelMessageIds, 0, -1 );
        }
        if( $sDelMessageIds == '' )
        { // 消息ID数组为空, 直接返回错误.不进行更新操作
            return FALSE;
        }
        $sWhere .= " AND `entry` IN ($sDelMessageIds) ";
        //2, 判断当前管理员可以操作的消息类型权限
        $aAdminMenus = $this->getMessageTypeByAdminMenus( 'arr' );
        $sAdminMenus = '';
        if( is_array($aAdminMenus) && !empty($aAdminMenus) )
        {
            foreach( $aAdminMenus AS $v )
            {
                if( is_numeric($v) )
                {
                    $sAdminMenus .= $v.',';
                }
            }
        }
        if( substr($sAdminMenus, -1, 1) == ',' )
        {
            $sAdminMenus = substr( $sAdminMenus, 0, -1 );
        }
        if( $sAdminMenus == '' )
        {
            $sWhere .= " AND 0 ";
        }
        else
        {
            $sWhere .= " AND t.`menuid` IN ($sAdminMenus) ";
        }
        $sNowTime = date('Y-m-d H:i:s');
        $this->oDB->query( "UPDATE `msglist` l LEFT JOIN `msgcontent` c ON l.`msgid`=c.`id` ".
	   					" LEFT JOIN `msgtype` t ON t.`id`=c.`msgtypeid` SET l.`deltime`='$sNowTime' WHERE 1 $sWhere" );
        return $this->oDB->ar();
	}
	// -------------------------------------------------------------------------



	/**
	 * 根据ID号/管理员阅读权限, 获取一条管理员消息
	 * @author tom 090519 07:36
	 * @param  int  $iMessageId
	 * @return array
	 */
    public function getOneAdminMessage( $iMessageId = 0 )
	{
	    $iMessageId = intval($iMessageId);
	    if( $iMessageId == 0 )
	    {
	        return -1;
	    }
        $sWhere = " AND `entry` = '$iMessageId' ";
        //2, 判断当前管理员可以操作的消息类型权限
        $aAdminMenus = $this->getMessageTypeByAdminMenus( 'arr' );
        $sAdminMenus = '';
        if( is_array($aAdminMenus) && !empty($aAdminMenus) )
        {
            foreach( $aAdminMenus AS $v )
            {
                if( is_numeric($v) )
                {
                    $sAdminMenus .= $v.',';
                }
            }
        }
        if( substr($sAdminMenus, -1, 1) == ',' )
        {
            $sAdminMenus = substr( $sAdminMenus, 0, -1 );
        }
        if( $sAdminMenus == '' )
        {
            $sWhere .= " AND 0 ";
        }
        else
        {
            $sWhere .= " AND t.`menuid` in ($sAdminMenus) ";
        }
        $res = $this->oDB->getOne( "SELECT l.*,c.*,t.*,ua.username as sendername, ub.username as receivename ".
                        " FROM `msglist` l LEFT JOIN `msgcontent` c ON l.`msgid`=c.`id` ".
	   					" LEFT JOIN `msgtype` t ON t.`id`=c.`msgtypeid` ".
                        " LEFT JOIN `users` ua ON c.`senderid`=ua.`userid` ".
                        " LEFT JOIN `users` ub ON l.`receiverid`=ub.`userid` " .
	   					" WHERE 1 $sWhere" );
        if( empty($res) )
        {
            return -1;
        }
        if( isset($res['readtime']) && $res['readtime'] == 0 && 
            isset($res['receivergroup']) && $res['receivergroup']==1 )
        { // 如果未被阅读, 并且消息发送给管理组, 则更新阅读时间
            $this->setIsReaded($res['entry']);
            $res['readtime'] = date('Y-m-d H:i:s');
        }
        return $res;
	}
	// -------------------------------------------------------------------------



	/**
	 * 设置消息的阅读时间
	 * @author tom 090519 07:37
	 * @param  int $iMessageId
	 * @return int 受影响行数
	 */
	public function setIsReaded( $iMessageId, $iCurrentTime='' )
	{
	    $iCurrentTime = $iCurrentTime=='' ? date('Y-m-d H:i:s') : daddslashes($iCurrentTime);
	    $this->oDB->query( "UPDATE `msglist` SET `readtime` = '$iCurrentTime' WHERE `entry`='$iMessageId' LIMIT 1 " );
	    return $this->oDB->ar();
	}
	// -------------------------------------------------------------------------



	/**
	 * 获取用户可见的消息类型 (主要用于管理员后台,发布消息时,读取消息类型)
	 * @author tom 090519 07:38
	 * @param  string $sReturn
	 * @param  string $sSelect
	 * @return mix
	 */
	public function getUserCanReadMessageType( $sReturn='opts', $sSelect = '' )
	{
	    //1, 获取全部消息类型
	    $aAllMsgType = $this->oDB->getAll("SELECT * FROM `msgtype` WHERE `msgtypegid` = 0 ");
	    if( $sReturn != 'opts' )
	    { // 如果需要数组, 则直接返回
	        return $aAllMsgType;
	    }
	    $sReturn = '';
	    
	    //2, 生成 options 字符
	    foreach( $aAllMsgType AS $v )
	    {
	        if( $sSelect != $v['id'] )
	        {
	            $sReturn .= '<OPTION VALUE="'.$v['id'].'">'. $v['title'] .'</OPTION>';
	        }
	        else 
	        {
	            $sReturn .= '<OPTION SELECTED VALUE="'.$v['id'].'">'. $v['title'] .'</OPTION>';
	        }
	    }
	    return $sReturn;
	}



	/**
	 * 管理员发送消息至用户
	 * 	 $aDatas = array(
     *       [mt] => 1            // 消息类型
     *       [subject] => aaaa    // 消息标题
     *       [username] => bbb    // 接收用户名  (可多个,逗号分隔)
     *       [type] => Array      // 接收用户组
     *           (
     *               [0] => 1     // 本人不接收
     *               [1] => 2     // 所有下级
     *               [2] => 3     // 直接下级
     *               [3] => 4     // 所有上级
     *           )
     *       [content] => cccc    // 消息详细内容
     *       [send] => 发送消息
     *   )
     * @author tom 090519 07:36
	 * @param array $aDatas
	 * @return int 
	 */
	public function InsertMessageFromAdmin( $aDatas )
	{
	    if( isset($aDatas['type']) && in_array(1,$aDatas['type']) && !in_array(2,$aDatas['type'])
	        && !in_array(3, $aDatas['type']) && !in_array(4,$aDatas['type']) )
	    {
	        return -1; // 群发选项错误
	    }
	    //1, 数据整理
	    $aMsgcontent['msgtypeid']   = intval($aDatas['mt']);  // 消息类型
	    $aMsgcontent['senderid']    = $_SESSION['admin'];      // 发送者ID
	    $aMsgcontent['sendergroup'] = 1;                    // 发送者所属组 0=用户组, 1=管理组
	    $aMsgcontent['subject']     = daddslashes($aDatas['subject']); // 消息标题
	    $aMsgcontent['content']     = daddslashes($aDatas['content']); // 消息内容
	    $aMsgcontent['channelid']   = 0;
	    $aMsgcontent['sendtime']    = date('Y-m-d H:i:s');
	    $iInsertId = 0; // init
	    // 执行插入, 获取消息内容ID号
	    if( 0 == ($iInsertId = $this->oDB->insert( 'msgcontent', $aMsgcontent )) )
	    {
	        return -2; // 消息内容插入失败
	    }

	    //2, 获取接收者ID
	    $sSep = array( " ", "　", ",", "，" ); // 半角,全角逗号, 空格
	    $aUserNameArray = explode( ',', trim( str_replace($sSep, ',', $aDatas['username']) ) );
	    unset($sSep);
	    foreach( $aUserNameArray as $k => $v )
	    {
	        if( trim($v) == '' )
	        {
	            unset($aUserNameArray[$k]);
	        }
	        else 
	        {
	            $aUserNameArray[$k] = "'".daddslashes(trim($v))."'";
	        }
	    }
	    if( count($aUserNameArray) == 0 )
	    {
	        return -3; // 无法获取接收消息的用户
	    }
	    $sUserSelfIds = join( ',', $aUserNameArray );
	    // 获取匹配用户名的 userid, parenttree
	    $aUserSelfarr = $this->oDB->getAll("SELECT a.`userid`,b.`parenttree` FROM `users` a ".
	                " LEFT JOIN `usertree` b ON a.`userid`=b.`userid` WHERE a.`username` IN ($sUserSelfIds) ");
	    if( $this->oDB->ar() == 0 )
	    {
	        return -3;
	    }
	    $sUserSelfIds = '';
	    $sUserSelfparents = '';
	    foreach( $aUserSelfarr as $v )
	    {
	        $sUserSelfIds     .= $v['userid'].',';
	        $sUserSelfparents .= $v['parenttree']==''?'':$v['parenttree'].',';
	    }
	    if( substr($sUserSelfIds,-1,1)==',' )
	    {
	        $sUserSelfIds     = substr($sUserSelfIds,0,-1);
	        $sUserSelfparents = substr($sUserSelfparents,0,-1);
	    }
	    $sMessageToUserIds = $sUserSelfIds.','; // init

	    if( isset($aDatas['type']) && in_array( 2, $aDatas['type'] ) )
	    { // 所有下级
	        $sTemp = '';
	        foreach( $aUserSelfarr as $v )
	        {
	            $sTemp .= " `parenttree` REGEXP '^". $v['userid'] .",|,". $v['userid'] .",|,". $v['userid'] ."$|^". $v['userid'] ."\$' OR";
	        }
	        if( substr($sTemp,-2,2) == 'OR' )
	        {
	            $sTemp = substr( $sTemp, 0, -2 );
	        }
	        if( $sTemp != '' )
	        {
	            $aRes = $this->oDB->getAll(" SELECT `userid` FROM `usertree` WHERE 1 AND $sTemp ");
	            if( $this->oDB->ar() )
	            {
    	            foreach( $aRes as $v )
    	            {
    	                $sMessageToUserIds .= $v['userid'].',';
    	            }
	            }
	            unset($aRes);
	        }
	    }

	    if( isset($aDatas['type']) && in_array( 3, $aDatas['type'] ) )
	    { // 直接下级
	        $aRes = $this->oDB->getAll(" SELECT `userid` FROM `usertree` WHERE 1 AND `parentid` IN ($sUserSelfIds) ");
	        if( $this->oDB->ar() )
            {
	            foreach( $aRes as $v )
	            {
	                $sMessageToUserIds .= $v['userid'].',';
	            }
            }
            unset($aRes);
	    }
	    
	    if( isset($aDatas['type']) && in_array( 4, $aDatas['type'] ) )
	    { // 所有上级
            $sMessageToUserIds .= $sUserSelfparents;
	    }

	    $aMessageToUserIds = array_unique( explode(',', $sMessageToUserIds)); // 要发送消息的用户id数组(唯一ID)
	    if( isset($aDatas['type']) && in_array( 1, $aDatas['type'] ) )
	    { // 本人不接收
	        $aUserSelfarr = explode(',', $sUserSelfIds);
	        if( is_array($aMessageToUserIds) && !empty($aUserSelfarr) )
	        {
	            foreach($aMessageToUserIds as $k => $v)
	            {
	                if( in_array( $v, $aUserSelfarr ) || trim($v)=='' )
	                {
	                    unset( $aMessageToUserIds[$k] );
	                }
	            }
	        }
	    }

	    // 发送消息
	    $sSqlInsert = '';
	    if( !empty($aMessageToUserIds) )
	    {
	        $sSqlInsert .= "INSERT INTO `msglist`(`msgid`,`receiverid`,`receivergroup`) VALUES ";
    	    foreach( $aMessageToUserIds as $v )
    	    {
    	        if( trim($v) != '' && is_numeric($v) )
    	        {
    	            $sSqlInsert .= " ('$iInsertId','$v','0'),";
    	        }
    	    }
    	    if( substr($sSqlInsert,-1,1)==',' )
    	    {
    	        $sSqlInsert = substr($sSqlInsert,0,-1).';';
    	    }
    	    return $this->oDB->query($sSqlInsert);
	    }
	    return -10;
	}



    /**
     * 由管理组发送一条消息, 给用户接收
     *   $aData = Array(
     *     ['msgtypeid']   =>  消息类型ID, 对应 `msgtype`.id  (注:msgtypegid=0 才是用户组可接收的消息类型)
     *     ['senderid']    =>  发送管理员的ID, 对应 `adminuser`.adminid
     *     ['sendergroup'] =>  发送者所属管理组, 0=用户组,  1=管理组
     *     ['subject']     =>  消息标题        `msgcontent`.subject
     *     ['content']     =>  消息内容        `msgcontent`.content
     *     ['channelid']   =>  消息所属频道ID  `msgcontent`.channelid
     *     ['sendtime']    =>  消息的发送时间  `msgcontent`.sendtime
     *     ---------------
     *     ['msgid']       =>  msglist.msgid 表消息内容ID. 即: 新插入的 msgcontent.id
     *     ['receiverid']  =>  msglist.receiverid , 接收消息用户ID
     *     ['receivergroup']=> msglist.receivergroup, 接收消息用户组, 0=用户组, 1=管理组
     *     ['readtime']    =>  接收用户的 消息读取时间, 默认 NULL
     *     ['deltime']     =>  接收用户的 消息删除时间, 默认 NULL
     * )
     * @author tom 090519 07:36
     * @param  array $aData
     * @return int
     */
    public function sendMessageToUser( $aData = array() )
    {
        // 数据检查, 先插入 msgcontent, 再插入 msglist
        $aNewSendData['msgtypeid']   = isset($aData['msgtypeid']) ? intval($aData['msgtypeid']) : 1;
        $aNewSendData['senderid']    = isset($_SESSION['admin']) ? intval($_SESSION['admin']) : 0;
        $aNewSendData['sendergroup'] = 1; // 管理组
        $aNewSendData['subject']     = isset($aData['subject']) ? daddslashes($aData['subject']) : '';
        $aNewSendData['content']     = isset($aData['content']) ? daddslashes($aData['content']) : '';
        $aNewSendData['channelid']   = isset($aData['channelid']) ? intval($aData['channelid']) : 0;
        $aNewSendData['sendtime']    = isset($aData['sendtime']) ? daddslashes($aData['sendtime']) : date('Y-m-d H:i:s');
        $iInsertId = $this->oDB->insert( 'msgcontent', $aNewSendData );
        unset($aNewSendData);
        if( !$iInsertId )
        { // msgcontent 消息内容表数据写入失败
            return -1;
        }
        $aNewSendData['msgid']          = intval($iInsertId);
        $aNewSendData['receiverid']     = isset($aData['receiverid']) ? intval($aData['receiverid']) : 0;
        $aNewSendData['receivergroup']  = 0;
        $aNewSendData['readtime']       = 'NULL';
        $aNewSendData['deltime']        = 'NULL';
        if( $aNewSendData['receiverid'] == 0 )
        {
            return -2;
        }
        return $this->oDB->insert( 'msglist', $aNewSendData );
    }



    /**
     * 用户发消息给管理组接收
     *   $aData = Array(
     *     ['msgtypeid']   =>  消息类型ID, 对应 `msgtype`.id  (注:msgtypegid=0 才是用户组可接收的消息类型)
     *     ['senderid']    =>  发送用户的ID, 对应 `users`.userid
     *     ['sendergroup'] =>  发送者所属管理组, 0=用户组,  1=管理组
     *     ['subject']     =>  消息标题        `msgcontent`.subject
     *     ['content']     =>  消息内容        `msgcontent`.content
     *     ['channelid']   =>  消息所属频道ID  `msgcontent`.channelid
     *     ['sendtime']    =>  消息的发送时间  `msgcontent`.sendtime
     *     ---------------
     *     ['msgid']       =>  msglist.msgid 表消息内容ID. 即: 新插入的 msgcontent.id
     *     ['receiverid']  =>  msglist.receiverid , 接收消息管理员ID，默认为0
     *     ['receivergroup']=> msglist.receivergroup, 接收消息用户组, 0=用户组, 1=管理组
     *     ['readtime']    =>  接收用户的 消息读取时间, 默认0
     *     ['deltime']     =>  接收用户的 消息删除时间, 默认0
     * )
     * 
     * @access 	public
     * @author 	james	09/05/19
     * @param	array   $aData
     * @return 	BOOL    //成功返回TURE，失败返回FALSE
     */
    public function sendMsgToAdmin( $aData=array() )
    {
    	//数据检查
    	if( empty($aData['subject']) || empty($aData['content']) )
    	{
    		return FALSE;	//消息内容和标题不能为空
    	}
    	$aNewData = array();
    	$aNewData['msgtypeid']  = isset($aData['msgtypeid']) ? intval($aData['msgtypeid']) : 1;
    	$aNewData['senderid']	= isset($aData['senderid']) ? intval($aData['senderid']) : intval($_SESSION['userid']);
    	$aNewData['sendergroup']= isset($aData['sendergroup']) ? intval($aData['sendergroup']) : 0;
    	$aNewData['subject']	= daddslashes( $aData['subject'] );
    	$aNewData['content']	= daddslashes( $aData['content'] );
    	$aNewData['channelid']	= isset($aData['channelid']) ? intval($aData['channelid']) : 0;
    	$aNewData['sendtime']	= isset($aData['sendtime']) ? daddslashes($aData['sendtime']) : date("Y-m-d H:i:s", time());
    	//---------------------------------------
    	$aNewSendData = array();
        $aNewSendData['receiverid']  =  isset($aData['receiverid']) ? intval($aData['receiverid']) : 0;
        $aNewSendData['receivergroup']= 1;
        $this->oDB->doTransaction();	//事务开始
   	 	$iInsertId = $this->oDB->insert( 'msgcontent', $aNewData );
   	 	unset($aNewData);
        if( FALSE == (bool)$iInsertId )
        { // msgcontent 消息内容表数据写入失败
        	$this->oDB->doRollback();
            return FALSE;
        }
        $aNewSendData['msgid']  =  intval($iInsertId);
        $this->oDB->insert( 'msglist', $aNewSendData );
        unset($aNewSendData);
        if( $this->oDB->affectedRows() < 1 )
        {
        	$this->oDB->doRollback();
            return FALSE;
        }
        $this->oDB->doCommit();
        return TRUE;
    }
    
    
	/**
	 * 保存与删除
	 * @param integer $iDay
     * @param string  $sPath
     * @return bool
     * 
     * 涉及表 msgcontent
     * 
     * 6/22/2010
	 */
	public function backandclearData($iDay,$sPath)
	{
		if( !is_numeric($iDay) ) 
		{
			return FALSE;
		}
		$iDay = intval($iDay);
		
		if( $iDay < 5 )
		{
			$iDay = 5;
		}
		$sDay = date("Ymd");
		
    	$aNumCodes = $this->oDB->getAll("SELECT id FROM `msgcontent` "
		                        ." WHERE `sendtime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		$num   = count($aNumCodes);
		
		if ($num <= 0 ) return FALSE;
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_msgcontent.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `msgcontent` "
		                                ." WHERE `sendtime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))
		                                ."' LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `msgcontent` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `msgcontent` WHERE `sendtime`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay." days"))."'");
		
		
		
		// 查询需删除的 mgslist
		$sNum = '';
		foreach ($aNumCodes AS $aNum){
			$sNum .= $aNum['id'].',';
		}
		$sNum = substr($sNum,0,-1);
		$sSql = "SELECT COUNT(entry) AS `numCodes` FROM `msglist` WHERE `msgid` IN (".$sNum.")";
		$numCodes = $this->oDB->getOne($sSql);
		$num   = $numCodes['numCodes'];
		$size  = 50000;
		$pages = ceil( $num/$size );
		$sFile = $sPath.DS.$sDay."_msglist.gz";
		makeDir(dirname($sFile));
		$gz    = gzopen($sFile,'w9');
		for( $page =0 ; $page < $pages; $page++ )
		{
			$FileContent = "";
			$aSales = $this->oDB->getAll("SELECT * FROM `msglist` "
		                                ." WHERE `msgid` IN (".$sNum.")"
		                                ." LIMIT ".($page*$size).",".$size);		
			foreach( $aSales as $aSale )
			{
				$keys   = array();
				$values = array();
				foreach( $aSale as $key=>$value )
				{
					$keys[] = "`".$key."`";
					if( is_null($value) )
					{
						$values[] = 'NULL';
					}
					else 
					{
						$values[] = "'".$this->oDB->es($value)."'";	
					}
				}
				$sql = "INSERT INTO `msglist` (".join(",",$keys).") VALUES (".join(",",$values).");";
				unset($keys);
				unset($values);
				$FileContent .= $sql."\n";
			}
			gzwrite($gz, $FileContent);
		}
		gzclose($gz);
		//删除
		$this->oDB->query("DELETE FROM `msglist` WHERE `msgid` IN (".$sNum.")");
		
		
	}
	

}
?>