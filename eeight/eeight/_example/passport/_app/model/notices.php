<?php
/**
 * 公告列表模型
 * 
 * 功能：
 * -- notice            获取一个公告
 * -- noticeUpdate      更新一个公
 * -- noticeVerify      设置公告的 '审核状态'
 * -- getNoticeList     获取公告列表
 * -- getOne            根据ID和频道ID读取一条公告内容(前台用)
 * -- NoticeInsert      插入新公告
 * -- updateNoticeInfo  更新公告信息
 * -- delNoitce         删除公告
 * 
 * @author    saul, Tom
 * @version   1.1.0
 * @package   passportadmin
 */

class model_notices extends basemodel 
{
    /**
     * 获取一个公告
     *
     * @access public
     * @param  int $inoticeId
     * @return [fix] -1:不存在的ID
     *              Array:公告
     */
    public function notice( $iNoticeId )
    {
        $this->oDB->query("SELECT `id`,`subject`,`content`,`sendtime`,`sendid`,`checkid`, ".
                     " `channelid`,`isdel`,`istop`,c.adminname AS sendername, d.adminname AS checkername ".
                     " FROM `notices` a LEFT JOIN `adminuser` c on a.`sendid`=c.`adminid` ".
                     " LEFT JOIN `adminuser` d on a.`checkid`=d.`adminid` ".
                     " WHERE `id`='" .$iNoticeId. "' ");
        if( $this->oDB->ar() < 1 )
        {
            return -1;
        }
        else
        {
            return $this->oDB->fetchArray();
        }
    }



    /**
     * 更新一个公告
     * 
     * @access public
     * @param  int    $iNoticeId    公告编号
     * @param  string $sSubject     公告标题
     * @param  string $sContent     公告内容
     * @param  int    $iadminId     修改的管理员的ID
     * @param  int    $ichannelId   公告所属板块ID
     * @return BOOL
     */
    public function noticeUpdate( $iNoticeId, $sSubject, $sContent, $iAdminId, $iChannelId = 0 )
    {
        if( empty($iAdminId) || empty($sSubject) || empty($sContent) || empty ($iAdminId) )
        {//参数不全
            return FALSE;
        }
        else 
        {
            $mNotice = $this->notice( $iAdminId );
            if( $mNotice == -1 )
            {//公告不存在
                return FALSE;
            }
            else
            {
                if( $mNotice['isdel'] == 0 )
                { //安全过滤
                    $sSubject = daddslashes($sSubject);
                    $sContent = daddslashes($sContent);
                    if( ($mNotice['subject'] != $sSubject) || ($mNotice['content'] != $sContent) )
                    {
                        $this->oDB->query("UPDATE `notices` SET `subject`='".$sSubject."',
                        `content`='".$sContent."',`sendtime`= '" . date("Y-m-d H:i:s", time()) . "',`sendid`='".$iAdminId."',
                        `checkid`='0',`channelid`='".$iChannelId."',`isdel`='0' 
                        WHERE `id`='".$iNoticeId."'");
                    }
                    return TRUE;
                }
                else
                {//删除模式下不允许修改
                    return FALSE;
                }
            }
        }
    }



    /**
     * 设置公告的 '审核状态'
     *    - 设为已审时, checkid = 审核管理员的 ID
     *    - 设为未审时, checkid = 0
     * 
     * @param int $iNoticeId    公告编号
     * @param int $bVifityFlags FALSE或TRUE,   0时直接覆盖, 1时写入管理员ID
     */
    public function noticeVerify( $iNoticeId, $bVifityFlags )
    {
        $aNotice = $this->notice( $iNoticeId );
        if( $aNotice == -1 )
        {
            return -1;
        }
        if( $aNotice['sendid'] == $_SESSION['admin'] )
        { // 公告的发布者, 禁止对公告进行审核
            return -2;
        }
        $bVifityFlags = intval($bVifityFlags) == 0 ? 0 : intval($_SESSION['admin']);
        $this->oDB->query("UPDATE `notices` SET `checkid`='".$bVifityFlags."', `checktime`='".date('Y-m-d H:i:s')."' ,`istop`='0' WHERE `id`='".$iNoticeId."' LIMIT 1");
        return $this->oDB->ar();
    }



    /**
     * 获取公告列表
     */
    public function & getNoticeList( $sFields = "*" , $sCondition = "1", $iPageRecords = 25 , $iCurrPage = 1, $sOrderby = ' ORDER BY `checktime` DESC ')
    {
        $sTableName = ' `notices` a LEFT JOIN `channels` b ON a.`channelid`=b.`id` 
                        LEFT JOIN `adminuser` c ON a.`sendid`=c.`adminid` ';
        $sFields    = ' a.`id`,b.channel as channelname,`subject`,`sendtime`,`sendid`,`checkid`,
                        `channelid`,`isdel`,`istop`,c.adminname AS sendername ';
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, 
                                          $iCurrPage, $sOrderby );
    }



    /**
     * 根据频道读取公告列表(前台用)
     * 
     * @access  public
     * @author  james
     * @param   string  $sFields    //要读取的公告字段内容
     * @param   string  $sCondition //附加搜索条件和排序条件
     * @param   int     $iChannelId //频道ID
     * @return  array   //公告列表，失败返回空数组
     */
    public function & getList( $sFields = '*', $sCondition = '', $iChannelId = 0 )
    {
        $sFields    = empty($sFields) ? '*' : $sFields;
        $iChannelId = (empty($iChannelId) || !is_numeric($iChannelId)) ? 0 : intval($iChannelId);
        $sSql = " SELECT ".$sFields." FROM `notices` ".
                " WHERE `channelid`='".$iChannelId."' AND `checkid`>'0' AND `isdel`='0' ".$sCondition
        		." ORDER BY `checktime` DESC";
//        		print_rr ( $sSql,1,1);
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 根据ID和频道ID读取一条公告内容(前台用)
     * 
     * @access  public
     * @author  james
     * @param   int     $iNoticeId  //公告ID,为0则读取最新的一条
     * @param   string  $sFields    //要读取的字段信息
     * @param   int     $iChannelId	//频道ID
     * @return  array   //公告内容信息，失败返回空数组
     */
    public function & getOne( $iNoticeId = 0, $sFields = '*', $iChannelId = 0 )
    {
        $iNoticeId  = (empty($iNoticeId) || !is_numeric($iNoticeId)) ? 0 : intval($iNoticeId);
        $sFields    = empty($sFields) ? '*' : $sFields;
        $iChannelId = (empty($iChannelId) || !is_numeric($iChannelId)) ? 0 : intval($iChannelId);
        $sCondition = '';
        if( $iNoticeId == 0 )
        {
            $sCondition .= "AND `istop`='1' ORDER BY `id` DESC LIMIT 1 ";
        }
        else 
        {
            $sCondition .= " AND `id`='".$iNoticeId."' ";
        }
        $sSql = " SELECT ".$sFields." FROM `notices` 
                WHERE `channelid`='".$iChannelId."' AND `checkid`>'0' AND `isdel`='0' ".$sCondition;
        return $this->oDB->getOne( $sSql );
    }



    /**
     * 插入新公告
     */
    public function NoticeInsert( $aPostArr )
    {
        // 数据整理
        $aArr['subject']    = isset($aPostArr['subject']) ? daddslashes($aPostArr['subject']) : '';
        $aArr['channelid']  = isset($aPostArr['channelid']) ? intval($aPostArr['channelid']) : 0;
        $aArr['content']    = isset($aPostArr['FCKeditor1']) ? daddslashes(trim($aPostArr['FCKeditor1'])) : '';
        $aArr['sendtime']   = date('Y-m-d H:i:s');
        $aArr['sendid']     = intval($_SESSION['admin']);
        $aArr['checkid']    = 0;
        $aArr['isdel']      = 0;
        $aArr['istop']      = 0;
        return $this->oDB->insert( 'notices', $aArr );
    }



    /**
     * 更新公告信息
     */
    public function updateNoticeInfo( $iNoticeId, $aPostArr )
    {
        $iNoticeId = is_numeric($iNoticeId) && $iNoticeId > 0 ? intval($iNoticeId) : 0;
        if( $iNoticeId == 0 )
        {
            return -1; // 数据初始错误
        }
        $aArr['subject']    = isset($aPostArr['subject']) ? daddslashes($aPostArr['subject']) : '';
        $aArr['channelid']  = isset($aPostArr['channelid']) ? intval($aPostArr['channelid']) : 0;
        $aArr['content']    = isset($aPostArr['FCKeditor1']) ? daddslashes(trim($aPostArr['FCKeditor1'])) : '';
        $aArr['isdel']      = isset($aPostArr['isdel']) ? intval($aPostArr['isdel']) : 0;
        $aArr['sendtime']   = date('Y-m-d H:i:s');
        $aArr['sendid']     = intval($_SESSION['admin']);
        $aArr['istop']      = 0;
        $aArr['checkid']    = 0;
        return $this->oDB->update( 'notices', $aArr, " `id`= '$iNoticeId' LIMIT 1 " );
    }



    /**
     * 删除公告
     * @author tom
     * @param array $aHtmlCheckBox
     * @return int
     */
    public function delNoitce( $aHtmlCheckBox = array() )
    {
        $sWhere         = '';
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
        $sWhere .= " AND `id` IN ($sDelMessageIds) ";
        $this->oDB->query( "UPDATE `notices` SET `isdel`='1' WHERE 1 $sWhere" );
        return $this->oDB->ar();
    }



    /**
     * 置顶(取消置顶)公告
     * @author saul
     * @param  integer $iNoticeId
     * @param  integer $iIsTop
     * @return int 
     */ 
    function topNotice( $iNoticeId , $iIsTop )
    {
        if(!is_numeric($iNoticeId) && !in_array( $iIsTop, array(0,1) ))
        {
            return -1;
        }
        $mNotice = $this->notice( $iNoticeId );
        if( $mNotice == -1 )
        {
            return -1;
        }
        if( $mNotice["istop"] == $iIsTop )
        {
            return 0;
        }
        if( $mNotice['isdel'] == 1 )
        {
            return -2;
        }
        if( $iIsTop==1 && $mNotice["checkid"]==0 )
        {
            return -3;
        }
        if( $iIsTop==1 )
        {
            $this->oDB->query("UPDATE `notices` SET `istop`='0' WHERE `channelid`='".$mNotice["channelid"]."'");
        }
        $this->oDB->query("UPDATE `notices` SET `istop`='".$iIsTop."' WHERE `id`='".$iNoticeId."'");
        return $this->oDB->ar();
    }
}
?>
