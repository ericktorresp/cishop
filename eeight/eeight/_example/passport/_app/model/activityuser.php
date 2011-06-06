<?php
/**
 * 答题用户模型
 * 
 * 功能
 * 
 * - activityUserDel()      删除用户
 * - acticityUserGetList()  获取用户列表
 * - activityUserInsert()   增加用户
 * - getuserActivitys()     获取用户可以参加的活动
 * 
 * @package passport
 * @since      090429 - 090618
 */
class model_activityuser extends basemodel
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



    /**
     * 增加用户或者重新增加用户(是否删除原来的用户)
     * @author SAUL
     * @param  integer $iActivityId 活动ID
     * @param  array   $aUserId     用户ID数组
     * @param  bool    $bAppend     是否追加
     * @return integer
     */
    function activityUserInsert( $iActivityId, $aUserId, $bAppend = FALSE )
    {
        $iActivityId = is_numeric($iActivityId) ? intval($iActivityId) : 0;
        if( $iActivityId<=0 )
        { // ID 错误
            return -1;
        }
        $aActivity = $this->oDB->getOne("SELECT * FROM `activity` WHERE `activityid`='".$iActivityId."'");
        if( empty($aActivity) )
        { // 活动不存在
            return -2;
        }
        if( $aActivity["isdel"] == 1 )
        { // 活动被删除
            return -3;
        }
        if( $aActivity["isverify"] >= 1 )
        { // 活动被验证或者被取消
            return -4;
        }
        if( !is_array($aUserId) )
        { //传入的参数不是数组
            return -5;
        }
        foreach( $aUserId as $iKey=>$iUserId )
        {
            if( !is_numeric($iUserId) )
            {
                unset($aUserId[$iKey]);
            }
        }
        $aUserId = array_unique($aUserId);
        if( empty($aUserId) )
        { //用户Id为空
            return -6;
        }
        $this->oDB->doTransaction();
        $bAppend = !empty($bAppend) ? $bAppend : FALSE;
        if( !$bAppend )
        { // 非追加模式
            $bResult = $this->oDB->query("DELETE FROM `activityuser` WHERE `activityId`='".$iActivityId."'");
            if ( !$bResult )
            { // 执行删除时候失败 
                $this->oDB->doRollback();
                return -7;
            }
            $aUsers = $this->oDB->getAll("SELECT `userid` FROM `users` WHERE `userid` IN (".join(',',$aUserId).")");
            foreach($aUsers as $aUser)
            {
                $this->oDB->query("INSERT INTO `activityuser` (`activityid`,`userid`,`bonusstatus`,"
                ."`bonustime`,`answertime`,`score`,`ip`,`cdnip`)VALUES('".$iActivityId."','".$aUser['userid']
                ."','0',NULL,NULL,0,'','');");
                if ( $this->oDB->ar() == 0 )
                {
                    $this->oDB->doRollback();
                    return -8;
                }
            }
        }
        else
        { // 追加模式
            $aUsers = $this->oDB->getAll("SELECT `userid` FROM `users` WHERE `userid` NOT IN (SELECT `userid` FROM `activityuser` WHERE `userid`"
                    ." IN (".join(',',$aUserId).") AND `activityid` ='".$iActivityId."') AND `userid` IN (".join(',',$aUserId).")");
            foreach ( $aUsers as $aUser )
            {
                $this->oDB->query("INSERT INTO `activityuser` (`activityid`,`userid`,`bonusstatus`,"
                ."`bonustime`,`answertime`,`score`,`ip`,`cdnip`)VALUES('".$iActivityId."','".$aUser['userid']
                ."','0',NULL,NULL,0,'','');");
                if ( $this->oDB->ar() == 0 )
                {
                    $this->oDB->doRollback();
                    return -8;
                }
            }
        }
        $this->oDB->doCommit();
        return 1;
    }



    /**
     * 获取一个答题用户列表
     *
     * @param string $sFields
     * @param string $sCondition
     * @param string $sOrderBy
     * @param integer $iPageRecord
     * @param integer $iCurrentPage
     * @return array
     */
    function activityUserGetList( $sFields='*', $sCondition='1', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
    {
        $sTableName = "`activityuser` LEFT JOIN `usertree` ON (`activityuser`.`userid`=`usertree`.`userid`)";
        if( empty($sFields) )
        {
            $sFields ="*";
        }
        if( empty($sCondition) )
        {
            $sCondition = "1";
        }
        if( empty($sOrderBy) )
        {
            $sOrderBy = "";
        }
        $iPageRecord  = isset($iPageRecord)&&is_numeric($iPageRecord) ? intval($iPageRecord) : 0; 
        $iCurrentPage = isset($iCurrentPage)&&is_numeric($iCurrentPage) ? intval($iCurrentPage) : 1;
        if( $iPageRecord <= 0 )
        {
            return $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition." ".$sOrderBy);	
        }
        else
        {
            return $this->oDB->getPageResult($sTableName , $sFields , $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy);
        }
    }



    /**
     * 删除一组活动用户
     * @author SAUL
     * @param  array   $aUserId
     * @param  integer $iActivityId
     */
    function activityUserDel( $aUserId, $iActivityId )
    {
        if( !is_array($aUserId) )
        {
            return -1;
        }
        if( !is_numeric($iActivityId) )
        {
            return -2;
        }
        foreach( $aUserId as $iKey=>$iUserId )
        {
            if( !is_numeric($iUserId) )
            {
                unset($aUserId[$iKey]);
            }
        }
        if( empty($aUserId) )
        {
            return -3;
        }
        $aActivity = $this->oDB->getOne("SELECT * FROM `activity` WHERE `activityId` ='".$iActivityId."'");
        if( empty($aActivity) )
        { // 活动不存在
            return -4;
        }
        if( $aActivity['isverify'] >= 1 )
        { // 活动被验证,不允许删除用户或者取消
            return -5;
        }
        $this->oDB->query( "DELETE FROM `activityuser` WHERE `activityid`='".$iActivityId."' AND `userid` IN (".join(',',$aUserId).")" );
        return $this->oDB->ar();
    }


    /**
     * 根据用户获取其活动列表
     * @author james
     * @access public
     * @param  int     $iUserId    //用户ID
     * 
     */
    public function & getUserActivitys( $iUserId, $sFields = '' )
    {
        $aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return $aResult;
        }
        $iUserId = intval($iUserId);
        if( empty($sFields) )
        {
            $sFields = " a.`activityid`,a.`title`,a.`description`,a.`feedback`,a.`starttime`,a.`endtime`,a.`type`,
                         a.`prize`";
        }
        $sFields = daddslashes( $sFields );
        $sSql = " SELECT " .$sFields. " FROM `activity` AS a
                  LEFT JOIN `activityuser` AS au ON a.`activityid`=au.`activityid` 
                  WHERE au.`userid`='".$iUserId."' AND au.`answertime` is null AND au.ip='' AND au.bonusstatus=0
                  AND a.starttime <= '".date('Y-m-d H:i:s')."' AND a.endtime >'".date('Y-m-d H:i:s')."' 
                  AND a.isdel = '0' AND a.isverify='1'";
        return $this->oDB->getAll( $sSql );
    }
}
?>