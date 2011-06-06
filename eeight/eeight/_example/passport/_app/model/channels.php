<?php 
/**
 * 栏目数据模型
 *
 * 功能：
 * -- channelAdd            新建一栏目
 * -- channelGet            根据栏目编号获取一个栏目
 * -- channelClose          关闭指定栏目
 * -- channelOpen           开放指定栏目
 * -- channelList           获取栏目列表
 * -- channelDel            删除一个栏目
 * -- channelUpdate         更新一个栏目
 * -- channelNameExists     检测不等于指定ID的频道名称是否存在
 * -- channelAllclose       关闭所有频道
 * -- channelAllopen        开放所有频道
 * -- getDistintChannelNames获取频道ID和频道名
 * 
 * @author     saul
 * @version    1.1.0
 * @package    passport
 */

class model_channels extends basemodel 
{
    /**
     * 新建一栏目
     * @author SAUL
     * @param array $aOldChannel 新建栏目名称
     * @return int
     */
    public function channelAdd( $aOldChannel )
    {
        if( !isset($aOldChannel) || empty($aOldChannel) )
        {
            return -1;
        }
        if( empty($aOldChannel["channel"]) )
        { //栏目名称错误
            return -2;
        }
        if( $this->channelNameExists($aOldChannel["channel"]) > 0 )
        {//栏目名称已经存在
            return -3;
        }
        $aChannel["channel"] = daddslashes($aOldChannel["channel"]);
        if( empty($aOldChannel["path"]) )
        { //栏目路径错误
            return -4;
        }
        if( $this->channelPathExists($aOldChannel["path"]) > 0 )
        {//栏目路径已经存在
            return -5;
        }
        $aChannel["path"] = daddslashes($aOldChannel["path"]);
        $aChannel["pid"]  = isset($aOldChannel["pid"]) && is_numeric($aOldChannel["pid"]) ? intval($aOldChannel["pid"]) : 0;
        if( $aChannel["pid"] > 0 )
        {
            $aPChannel = $this->channelGet($aChannel["pid"]);
            if( $aPChannel == -1 )
            { //栏目组ID 错误
                return -6;
            }
        }
        $aChannel["usergroups"] = ""; //默认值为空
        $iResult = $this->oDB->insert( 'channels', $aChannel );
        if($iResult == FALSE)
        {
            return 0;
        }
        else
        {
            return $iResult;
        }
    }



    /**
     * 获取频道
     *
     * @param string $sField
     * @param string $sCondition
     * @return array
     */
    public function channelGetAll( $sField, $sCondition )
    {
        if( empty($sField) )
        {
            $sField = "*";
        }
        if( empty($sCondition) )
        {
            $sCondition = "1";
        }
        return $this->oDB->getAll( " SELECT ".$sField." FROM `channels` WHERE " . $sCondition );
    }



    /**
     * 根据栏目编号获取一个栏目
     * @author SAUL 
     * @param int $ichannelId 栏目编号
     * @return mixed    -1:栏目编号不存在
     *                  成功:栏目数组
     */
    public function channelGet( $iChannelId )
    {
        $iChannelId = intval( $iChannelId );
        $sSql       = "SELECT * FROM `channels` WHERE `id`='" .$iChannelId. "'";
        $this->oDB->query( $sSql );
        if( $this->oDB->ar() == 0 )
        {//栏目编号不存在
            return  -1; 
        }
        else
        {//返回数组
            return $this->oDB->fetchArray();
        }
    }



    /**
     * 关闭指定栏目
     * @author SAUL
     * @param int $ichannelId 栏目编号
     * @return BOOL FLASE   栏目不存在
     *               TRUE   成功关闭
     * 
     */
    public function channelClose( $iChannelId )
    {
        //查询栏目ID生成栏目
        $iChannelId = intval( $iChannelId );
        $mChannel   = $this->channelGet( $iChannelId );
        if( $mChannel == -1 )
        {
            return FALSE;//栏目不存在时候返回FALSE
        }
        else 
        {
            if( $mChannel['isdisabled'] == 0 )
            {//栏目开放时候进行操作
                $this->oDB->query( "UPDATE `channels` SET `isdisabled`='1' WHERE `id`='" .$iChannelId. "'" );
            }
            return TRUE;
        }
    }



    /**
     * 开放指定栏目
     * @author SAUL
     * @param int $ichannelId 栏目编号
     * @return BOOL FALSE   栏目编号不存在
     *               TRUE   成功开放
     */
    public function channelOpen( $iChannelId )
    {  //查询栏目ID生成栏目
        $iChannelId = intval($iChannelId);
        $mChannel   = $this->channelGet($iChannelId);
        if( $mChannel == -1 )
        {
            return FALSE;//栏目不存在时候返回FLASE
        }
        else 
        {
            if( $mChannel['isdisabled'] == 1 )//仅栏目关闭时候使用
            {
                $this->oDB->query( "UPDATE `channels` SET `isdisabled`='0' WHERE `id`='" . $iChannelId . "'" );
            }
            return ($this->oDB->errno() == 0);
        }
    }



    /**
     * 获取栏目列表
     * @author SAUL
     * @param int $iIsDisabled 是否启用查询板块禁用
     * @return 默认为空时,查询所有栏目，否则，返回是否禁用的栏目列表
     * 
     */
    public function channelList( $iIsDisabled = -1 )
    {
        if( is_null($iIsDisabled) )
        { // 参数初始化
            $iIsDisabled = -1; 
        }
        if( !in_array($iIsDisabled, array(0,1)) )
        { // 参数不为0,1 时候，设置默认返回所有
            $iIsDisabled = -1;
        }
        if($iIsDisabled == -1 )
        { // 查询所有的栏目
            $sSqlChannels = "SELECT `id`,`channel`,`path`,`isdisabled`,`pid` FROM `channels`";
        }
        else
        { // 查询指定条件的栏目
            $sSqlChannels = "SELECT `id`,`channel`,`path`,`isdisabled`,`pid` FROM `channels`
                             WHERE `isdisabled`='".$iIsDisabled."'";
        }
        return $this->oDB->getAll( $sSqlChannels );//返回栏目数组
    }



    /**
     * 删除一个栏目
     * @author SAUL
     * @param int   $ichannelId 栏目编号
     * @return BOOL FALSE  栏目编号不存在
     *              TRUE   栏目删除成功
     */
    public function channelDel( $iChannelId )
    { //查询栏目ID生成栏目
        $iChannelId = intval( $iChannelId );
        $mChannel   = $this->channelGet( $iChannelId );
        if( $mChannel == -1)
        { // 栏目不存在，返回FAlSE
            return FALSE;
        }
        else
        { // 执行删除操作
            $this->oDB->query("DELETE FROM `channels` WHERE `id`='" .$iChannelId. "' or `pid`='".$iChannelId."'");
            return ( $this->oDB->errno() == 0 );
        }
    }



    /**
     * 频道更新
     *
     * @param array $aOldChannel
     * @param string $sCondition
     * @return integer
     */
    function channelUpdate( $aOldChannel, $sCondition )
    {
        if( !isset($aOldChannel) || empty($aOldChannel) )
        { //数据错误
            return -1;
        }
        if( isset($aOldChannel["channel"]) )
        {
            if( empty($aOldChannel["channel"]) )
            { //名称为空
                return -2;
            }
            $aChannel["channel"] = daddslashes($aOldChannel["channel"]);
        }
        if( isset($aOldChannel["pid"]) )
        {
            if( !is_numeric($aOldChannel["pid"]) )
            { // 频道组名称为空
                return -3;
            }
            if( $aOldChannel["pid"] > 0 )
            {
                $aChnanels = $this->channelGet($aOldChannel["pid"]);
                if( $aChnanels == -1 )
                { //组频道不存在
                    return -4;
                }
            }
            $aChannel["pid"] = intval($aOldChannel["pid"]);
        }
        if( isset($aOldChannel["path"]) )
        {
            if( empty($aOldChannel["path"]) )
            { //路径错误
                return -5;
            }
            $aChannel["path"] = daddslashes($aOldChannel["path"]);
        }
        $iResult = $this->oDB->update( 'channels', $aChannel, $sCondition );
        if($iResult == FALSE)
        {
            return -6;
        }
        else
        {
            return $iResult;
        }
    }



    /**
     * 检测不等于指定ID的频道名称是否存在
     * @author SAUL
     * @param string $sChannelName	频道名称
     * @param int $id	频道ID
     * @return BOOL 
     */
    public function channelNameExists( $sChannelName, $iId = 0 )
    {
        $sChannelName = daddslashes( $sChannelName );
        $iId          = intval( $iId );
        $this->oDB->query("SELECT `id` FROM `channels` WHERE `channel`='".$sChannelName."' AND `id` != '".$iId."'");
        return ( $this->oDB->ar() > 0 );
    }



    /**
     * 检测不等于指定ID的频道路径是否存在
     * @author SAUL
     * @param  string $sPath 频道路径
     * @param  int $id  频道ID
     * @return BOOL
     */
    public function channelPathExists( $sPath, $iId = 0 )
    {
        $sPath = daddslashes( $sPath );
        $iId   = intval( $iId );
        $this->oDB->query( "SELECT `id` FROM `channels` WHERE `path`='" . $sPath . "' AND `id` != '" . $iId . "'" );
        return ( $this->oDB->ar() > 0 );
    }



    /**
     * 关闭所有频道
     * @author SAUL
     * @return BOOL
     */
    public function channelAllclose()
    {
        $this->oDB->query("UPDATE `channels` SET `isdisabled`='1' WHERE `isdisabled` != '1'");
        return ( $this->oDB->errno() == 0 );
    }



    /**
     * 开放所有频道
     * @author SAUL
     * @return BOOL
     */
    public function channelAllopen()
    {
        $this->oDB->query("UPDATE `channels` SET `isdisabled`='0' WHERE `isdisabled` != '0'");
        return ( $this->oDB->errno() == 0 );
    }



    /**
     * 获取频道ID和频道名
     * @author tom
     * @param BOOL $bReturnArray
     * @param string $sSelected
     * @param BOOL $bShowDisable
     * @return mix
     */
    public function getDistintChannelNames( $bReturnArray = TRUE, $sSelected = '', $bShowDisable = FALSE )
    {
        $sSqlAdd = " AND `isdisabled` = 1 ";
        if( $bShowDisable == TRUE )
        {
            $sSqlAdd = '';
        }
        $aTmpArray = $this->oDB->getAll("SELECT `id`,`channel` FROM `channels` WHERE `pid`=0 $sSqlAdd ");
        if( $this->oDB->ar() < 1 )
        {
            return '';
        }
        $aReturn = array();
        if( $bReturnArray == TRUE )
        {
            foreach( $aTmpArray as $k => $v )
            {
                $aReturn[$k] = $v['id'];
                $aReturn[$k] = $v['channel'];
            }
            return $aReturn;
        }
        else
        {
            foreach( $aTmpArray as $k => $v )
            {
                $sSel = $sSelected==$v['id'] ? 'SELECTED' : '';
                $aReturn .= "<OPTION $sSel VALUE=\"".$v['id']."\">".$v['channel']."</OPTION>";
            }
            return $aReturn;
        }
    }


    /**
     * 获取当前 用户可用的频道列表
     * @return array
     *        [频道id] => [频道中文名]
     */
    public function getAvailableChannel( $bIncludePassport = TRUE, $sWhereExtendCondition = '' )
    {
        $aChannels = array();
        if( !empty($sWhereExtendCondition) )
        {
            $aChannels = $this->channelGetAll( ' `id`,`channel` ', " `isdisabled`=0 $sWhereExtendCondition " );
        }
        else
        {
            $aChannels = $this->channelGetAll( ' `id`,`channel` ', " `isdisabled`=0 " );
        }

        $aAvailableChannel = array();
        foreach( $aChannels AS $v )
        {
            $aAvailableChannel[ $v['id'] ] = $v['channel'];
        }

        if( TRUE === $bIncludePassport )
        {
            if( !isset($aAvailableChannel['0']) )
            {
                $aAvailableChannel['0'] = '银行大厅';
            }
        }
        return $aAvailableChannel;
    }
}
?>