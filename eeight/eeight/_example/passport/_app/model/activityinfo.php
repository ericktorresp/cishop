<?php
/**
 *  题目模型
 * 
 * 功能: 
 *   CURD - activityInfoInsert()    插入一个题目
 *   CURD - activityInofoGetOne()   获取一个题目
 *  activityInfoGetList()           获取题目列表
 *  activityInfoDelete()            删除一个题目 
 * 
 * @author  SAUL
 * @version 1.1.0
 * @package passportadmin
 * @since   090622 - 
 */
class model_activityinfo extends basemodel
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
     * 插入一个题目
     * @author SAUL
     * @param  array   $aActivityInfo
     * @return integer
     */
    function activityInfoInsert( $aActivityInfo = array() )
    {
        if( !is_array($aActivityInfo) || empty($aActivityInfo) )
        { // 参数错误
            return -1;
        }
        if( empty($aActivityInfo["activityid"]) || !is_numeric($aActivityInfo["activityid"]) )
        { // ID错误
            return -2;
        }
        $aActivityInfo["activityid"] = intval( $aActivityInfo["activityid"] );
        if( empty($aActivityInfo["title"]) )
        {//标题不能为空
            return -3;
        }
        if( !isset($aActivityInfo["type"]) || !is_numeric($aActivityInfo["type"]) )
        {//选项类型必选
            return -4;
        }
        $aActivityInfo["title"] = daddslashes( $aActivityInfo["title"] );
        $aActivityInfo["type"]  = intval( $aActivityInfo["type"] );
        $sSql      = "SELECT `activityid`,`isdel`,`isverify`,`type` FROM `activity` 
                      WHERE `activityid`='" .$aActivityInfo["activityid"]. "'";
        $aActivity = $this->oDB->getOne( $sSql );
        if( empty($aActivity) )
        { // ID非法
            return -2;
        }
        if( $aActivity["isdel"] == 1 )
        { // 活动删除
            return -5;
        }
        if( $aActivity["isverify"] >= 1  )
        { // 活动被验证或者强制取消
            return -6;
        }
        if( $aActivity["type"] == 1 )
        { // 有奖竞猜
            if( !in_array( $aActivityInfo["type"], array( 0, 1 ) ) )
            { // 类型传参错误
                return -7;
            }
            if( $aActivityInfo["options"] == "" )
            {//选项参数为空
                return -8;
            }
            $aActivityInfo["options"] = daddslashes( $aActivityInfo["options"] );
            if( empty($aActivityInfo["answer"]) )
            { // 答案为空
                return -9;
            }
            $aActivityInfo["answer"] = daddslashes( $aActivityInfo["answer"] );
            $aActivityInfo["score"]  = isset($aActivityInfo["score"]) ? intval($aActivityInfo["score"]) : 0;
            if( $aActivityInfo["score"] < 0 )
            { // 题目指定的分数错误
                return -10;
            }
        }
        else
        { // 问卷调查
            if( !in_array( $aActivityInfo["type"], array(0,1,2,3) ) )
            { // 类型传参错误
                return -7;
            }
            if( in_array($aActivityInfo["type"], array(0,1) ) )
            {
                if( $aActivityInfo["options"] == "" )
                {//选项参数为空
                    return -8;
                }
                $aActivityInfo["options"] = daddslashes( $aActivityInfo["options"] );
            }
            else
            {
                $aActivityInfo["options"] = "";
            }
            $aActivityInfo["answer"] = "";
            $aActivityInfo["score"]  = 0;
        }
        $aActivityInfo["hint"]      = !empty($aActivityInfo["hint"]) ? daddslashes($aActivityInfo["hint"]) : "";
        $aActivityInfo["minright"]  = (isset($aActivityInfo["minright"]) && is_numeric($aActivityInfo["minright"])) ?
                                        intval($aActivityInfo["minright"])  : 0;
        $aActivityInfo["isrequire"] = (isset($aActivityInfo["isrequire"]) && is_numeric($aActivityInfo["isrequire"])) ?
                                        intval($aActivityInfo["isrequire"]) : 0;
        return $this->oDB->insert( "activityinfo", $aActivityInfo );
    }



    /**
     * 获取一个题目
     * @author SAUL
     * @param  integer $iAcitvityInfoId
     * @return array
     */
    function activifyInfoGetOne( $iAcitvityInfoId )
    {
        $iAcitvityInfoId = is_numeric( $iAcitvityInfoId ) ? intval( $iAcitvityInfoId ) :0;
        if( $iAcitvityInfoId <= 0 )
        {
            $aTemp = array();
            return $aTemp;
        }
        else
        {
            return $this->oDB->getOne("SELECT * FROM `activityinfo` WHERE `infoid`='".$iAcitvityInfoId."'");
        }
    }



    /**
     * 获取活动题目列表
     * @author saul
     * @param  string  $sFields
     * @param  string  $sCondition
     * @param  string  $sOrderBy
     * @param  int     $iPageRecord
     * @param  int     $iCurrentPage
     * @return array
     */
    public function activityInfoGetList( $sFields = '', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0 )
    {
        if( empty($sFields) )
        {
            $sFields = "*";	
        }
        $sFields = daddslashes( $sFields );
        if( empty($sCondition) )
        {
            $sCondition = "1";
        }
        $sCondition  = $sCondition;
        $sOrderBy    = daddslashes( $sOrderBy );
        $iPageRecord = (is_numeric($iPageRecord) && $iPageRecord>0) ? intval($iPageRecord) : 0;
        if ( $iPageRecord <= 0 )
        {
            $sSql = " SELECT ".$sFields." FROM `activityinfo` WHERE ".$sCondition." ".$sOrderBy;
            return $this->oDB->getAll( $sSql );
        }
        $iCurrentPage = is_numeric($iCurrentPage) ? intval($iCurrentPage) : 0;
        if ( $iCurrentPage <= 0 )
        {
            $iCurrentPage = 1;
        }
        return $this->oDB->getPageResult( 'activityinfo', $sFields, $sCondition, $iPageRecord,
                    $iCurrentPage, $sOrderBy );
    }



    /**
     * 删除一个题目
     * @author SAUL
     * @param  integer $iAcitvityInfoId
     * @return integer
     */
    function activifyInfoDelete( $iAcitvityInfoId )
    {
        if( empty($iAcitvityInfoId) || !is_numeric($iAcitvityInfoId) )
        {//参数错误
            return -1;
        }
        $bCanDel = FALSE;
        $aActivityInfo = $this->activifyInfoGetOne( $iAcitvityInfoId );
        if( !empty($aActivityInfo) )
        { // 确认存在
            $sSql      = "SELECT * FROM `activity` WHERE `activityid`='".$aActivityInfo["activityid"]."'";
            $aActivity = $this->oDB->getOne( $sSql );
            if( empty($aActivity) )
            { // 可以删除
                $bCanDel = TRUE;
            }
            elseif( $aActivity["isdel"] == 1 )
            { // 可以删除
                $bCanDel = TRUE;
            }
            else
            { // 看活动状态
                if( $aActivity["isverify"] >= 1 )
                { // 被验证或者取消的
                    $bCanDel = FALSE;
                }
                else
                { // 活动没有被验证
                    $bCanDel = TRUE;
                }
            }
        }
        if( $bCanDel )
        {
            return $this->oDB->delete('activityinfo',"`infoid`='".$iAcitvityInfoId."'");
        }
        else
        {
            return 0;
        }
    }



    /**
     * 更新一个题目
     *
     * @param array $aActivityInfo
     * @param integer $iActivityInfoId
     * @return integer
     */
    function activifyInfoUpdate( $aActivityInfo, $iActivityInfoId )
    {
       if( !is_array($aActivityInfo) || empty($aActivityInfo) || empty( $iActivityInfoId ) || !is_numeric($iActivityInfoId) )
        { // 参数错误
            return -1;
        }
        $iActivityInfoId = intval( $iActivityInfoId );
        if( empty($aActivityInfo["activityid"]) || !is_numeric($aActivityInfo["activityid"]) )
        { // ID错误
            return -2;
        }
        $aActivityInfo["activityid"] = intval( $aActivityInfo["activityid"] );
        if( isset($aActivityInfo["title"]) )
        {
            if( empty($aActivityInfo["title"]) )
            {//标题不能为空
                return -3;
            }
            $aActivityInfo["title"] = daddslashes($aActivityInfo["title"]);
        }
        if( isset($aActivityInfo["type"]) )
        {
            if( !is_numeric($aActivityInfo["type"]) || $aActivityInfo["type"] > 4 )
            {//类型选择错误
                return -4;
            }
            $aActivityInfo["type"] = intval( $aActivityInfo["type"] );
        }
        $sSql      = "SELECT `activityid`,`isdel`,`isverify`,`type`,`starttime` FROM `activity` 
                      WHERE `activityid`='" .$aActivityInfo["activityid"]. "'";
        $aActivity = $this->oDB->getOne( $sSql );
        if( empty($aActivity) )
        { // ID非法
            return -2;
        }
        if( $aActivity["isdel"] == 1 )
        { // 活动删除
            return -5;
        }
        if( $aActivity["isverify"] >= 1 && strtotime($aActivity["starttime"]) <= time() )
        { // 活动被验证或者取消并且已经开始了
            return -6;
        }
        if( $aActivity["type"] == 1 )
        { // 有奖竞猜
            if( isset($aActivityInfo["type"]) && !in_array( $aActivityInfo["type"], array( 0, 1 ) ) )
            { // 类型传参错误
                return -7;
            }
            if( isset($aActivityInfo["options"]) )
            {
                if( $aActivityInfo["options"] == "" )
                {//选项参数为空
                    return -8;
                }
                $aActivityInfo["options"] = daddslashes( $aActivityInfo["options"] );
            }
            if( isset($aActivityInfo["answer"]) )
            {
                if( $aActivityInfo["answer"] == "" )
                {// 答案为空
                    return -9;
                }
                $aActivityInfo["answer"] = daddslashes( $aActivityInfo["answer"] );
            }
            if( isset($aActivityInfo["score"]) )
            {
                if( !is_numeric($aActivityInfo["score"]) || $aActivityInfo["score"] < 0 )
                {// 题目指定的分数错误
                    return -10;
                }
                $aActivityInfo["score"] = intval( $aActivityInfo["score"] );
            }                   
        }
        else
        { // 问卷调查
            if( isset($aActivityInfo["type"]) && !in_array( $aActivityInfo["type"], array(0,1,2,3)) )
            { // 类型传参错误
                return -7;
            }
            if( isset($aActivityInfo["options"]) )
            {
                if( isset($aActivityInfo["type"]) && in_array($aActivityInfo["type"], array(0,1)) 
                    && $aActivityInfo["options"] == "" )
                {//选项参数为空
                    return -8;
                }
                $aActivityInfo["options"] = daddslashes( $aActivityInfo["options"] );
            }
            if( isset($aActivityInfo["answer"]) )
            {
                $aActivityInfo["answer"] = "";
            }
            $aActivityInfo["score"]  = 0;
        }
        if( isset($aActivityInfo["hint"]) )
        {
            $aActivityInfo["hint"] = daddslashes($aActivityInfo["hint"]);
        }
        if( isset($aActivityInfo["minright"]) )
        {
            $aActivityInfo["minright"] = intval($aActivityInfo["minright"]);
        }
        if( isset($aActivityInfo["isrequire"]) )
        {
            $aActivityInfo["isrequire"] = intval($aActivityInfo["isrequire"]);
        }
        unset($aActivityInfo["activityid"]); //不允许修改题目ID
        return $this->oDB->update( 'activityinfo', $aActivityInfo, " `infoid`='".$iActivityInfoId."' " );
    }
}
?>