<?php
/**
 *  活动模型
 * 
 * 功能: 
 *  CRUD -  activityInsert()    增加一个活动
 *  CRUD -  activityGetOne()    获取一个活动
 *  
 *  activityGetList()   活动列表
 *  activityUpdate()    更新活动
 *  activityDelete()    删除活动
 *  
 * 
 * @author  SAUL
 * @version 1.1.0
 * @package passportadmin
 */
class model_activity extends basemodel
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



    /************************************ CRUD *************************************/
    /**
     * 发起一个活动
     * @author saul  090622
     * @param  array $aActivity
     * @return int
     */
    public function activityInsert( $aActivity = array() )
    {
        if( !is_array( $aActivity ) || empty( $aActivity ) )
        { // 参数错误
            return 0; 
        }
        if( $aActivity["title"] == "" )
        { // 活动标题为空
            return -1;
        }
        $aActivity["title"]       = daddslashes( $aActivity["title"] );
        $aActivity["description"] = isset($aActivity["description"]) ? daddslashes($aActivity["description"]) : "";
        if( empty($aActivity["starttime"]) || empty($aActivity["endtime"]) )
        {//没有指定开始和结束时间
            return -2;
        }
        $aActivity["starttime"] = getFilterDate( $aActivity["starttime"] );
        $aActivity["endtime"]   = getFilterDate( $aActivity["endtime"] );
        if( strtotime($aActivity["starttime"]) <= time() )
        { // 答题开始时间不能比现在早
            return -3;
        }
        if( strtotime($aActivity["starttime"]) >= strtotime( $aActivity["endtime"] ) )
        { // 答题结束时间不能早于答题开始时间
            return -4;
        }
        if( !isset($aActivity["type"]) || !in_array($aActivity["type"], array(0,1)) )
        { // 答题类型不正确
            return -5;
        }
        $aActivity["type"] = intval($aActivity["type"]);
        if( $aActivity["type"] == 0 )
        {
            $aActivity["minscore"] = 0;
        }
        else 
        {
            $aActivity["minscore"]  = ( isset($aActivity["minscore"]) && is_numeric($aActivity["minscore"]) ) ? 
                                        intval($aActivity["minscore"]): 0;
        }
        $aActivity["prize"] = ( isset($aActivity["prize"]) && is_numeric($aActivity["prize"]) ) ? 
                              number_format($aActivity["prize"], 2) : 0.00;	
        $aActivity["isdel"]        = 0;
        $aActivity["isverify"]     = 0;
        $aActivity["sendtime"]     = date("Y-m-d H:i:s", time());
        $aActivity["bonusstatus"]  = 0;
        if( empty($aActivity["sendid"]) )
        {
            $aActivity["sendid"] = ( isset($_SESSION["admin"]) && is_numeric($_SESSION["admin"]) ) ? 
                                   intval($_SESSION["admin"]) : 0;
        }
        if( $aActivity["sendid"] == 0 )
        { //管理员非法
            return -6;
        }
        $aActivity["checkid"] = 0;
        return $this->oDB->insert( "activity", $aActivity );
    }



    /**
     * 获取一个活动
     * @author saul
     * @param  integer $iActivityId
     * @return array
     */
    public function activityGetOne( $sFields = '', $sCondition = '' )
    {
        $sFields    = empty($sFields) ? '*' : daddslashes($sFields);
        $sCondition = empty($sCondition) ? '1' : $sCondition;
        $sSql       = "SELECT ".$sFields." FROM `activity` WHERE ".$sCondition;
        return $this->oDB->getOne( $sSql );
    }



    /**
     * 获取活动列表
     * @author saul 
     * @param  string  $sFields
     * @param  string  $sCondition
     * @param  string  $sOrderBy
     * @param  int     $iPageRecord
     * @param  int     $iCurrentPage
     * @return array
     */
    public function activityGetList( $sFields = '', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0 )
    {
        if( empty($sFields) )
        { // 默认字段
            $sFields = "a.`type`, a.`title`, a.`starttime`, a.`endtime`, a.`activityid`, "
                ."u.`adminname`, a.`isdel`, a.`isverify`,a.`prize`";
        }
        if( empty($sCondition) )
        {
            $sCondition = "1";
        }
        $iPageRecord  = intval( $iPageRecord );
        if( $iPageRecord == 0 )
        {
            $iPageRecord = 20;
        }
        $iCurrentPage = intval( $iCurrentPage );
        if( $iCurrentPage == 0 )
        {
            $iCurrentPage = 1;
        }
        $sTableName ="`activity` AS a LEFT JOIN `adminuser` AS u ON (a.`sendid`=u.`adminid`)";
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord,
             $iCurrentPage, $sOrderBy );
    }



    /**
     * 更新活动
     * @author saul
     * @param  array   $aActivity
     * @param  string  $sWhere
     */
    public function activityUpdate( $aActivity, $iActivityId )
    {
        //step 1.先处理参数
        $iActivityId = is_numeric($iActivityId) ? intval($iActivityId) : 0;
        $aTemp       = array();
        if( $iActivityId <= 0 )
        { // 非法ID
            return -7;
        }
        if( isset($aActivity["type"]) )
        {//不允许修改类型
            unset( $aActivity["type"] );
        }
        if( isset($aActivity["title"]) )
        {
            if( empty($aActivity["title"]) )
            { // 标题为空
                return -1;
            }
            $aTemp["title"] = daddslashes($aActivity["title"]);
        }
        if( isset($aActivity["description"]) )
        {
            $aTemp["description"] = daddslashes($aActivity["description"]);
        }
        if( isset($aActivity["feedback"]) )
        {
            $aTemp["feedback"] = daddslashes($aActivity["feedback"]);
        }
        if( isset($aActivity["starttime"]) && isset($aActivity["endtime"]) )
        {
            $aTemp["starttime"] = getFilterDate( $aActivity["starttime"] );
            $aTemp["endtime"]   = getFilterDate( $aActivity["endtime"] );
            if( strtotime($aTemp["starttime"]) <= time() )
            { // 答题开始时间不能比现在早
                return -3;
            }
            if( strtotime($aTemp["starttime"]) >= strtotime($aTemp["endtime"]) )
            { // 答题结束时间不能早于答题开始时间
                return -4;
            }
        }
        $aTemp["prize"] = ( isset($aActivity["prize"]) && is_numeric($aActivity["prize"]) ) ? 
                           number_format($aActivity["prize"], 2) : 0.00;
        
        $aOldActivity = $this->activityGetOne( '*', " `activityid`='".$iActivityId."' " );
        if( empty($aOldActivity) )
        { // 非法ID
            return -7;
        }
        if( $aOldActivity["isdel"] == 1 )
        { // 活动被删除
            return -5;
        }
        if( $aOldActivity["isverify"] >= 1 && strtotime($aOldActivity['starttime']) <= time() )
        {//活动已开始
            return -6;
        }
        if( isset($aActivity["isdel"]) )
        {//不提供删除
            unset($aActivity["isdel"]);
        }
        if( isset($aActivity["bonusstatus"]) )
        {//不提供奖金状态修改
            unset($aActivity["bonusstatus"]);
        }
        if( isset($aActivity["isverify"]) )
        {//不提供审核功能
            unset($aActivity["isverify"]);
        }
        $sWhere = " `activityid`='".$iActivityId."'";
        return $this->oDB->update( "activity", $aActivity, $sWhere );
    }



    /**
     * 删除活动
     * @author SAUL
     * @param  integer $iActivityId
     * @return int
     */
    public function activityDelete( $iActivityId )
    {
        $iActivityId = is_numeric( $iActivityId ) ? intval( $iActivityId ) : 0;
        if( $iActivityId <= 0 )
        { // 参数错误
            return 0;
        }
        $aActivity = $this->activityGetOne( '*', "`activityid`='".$iActivityId."'" );
        if( empty($aActivity) )
        { // 非法ID
            return -1;
        }
        if( $aActivity["isdel"] >= 1 )
        { // 已经是删除状态
            return -2;
        }
        if( $aActivity["isverify"] == 0 )
        { // 可以删除
            return $this->oDB->update( "activity", "`isdel`='1'", "activityid=".$iActivityId );
        }
        elseif( $aActivity["isverify"] == 1 )
        { // 已经被验证的活动
            if( date("Y-m-d H:i:s",strtotime($aActivity["endtime"])) >= date("Y-m-d H:i:s") )
            { //时间没有过
                return -3;
            }
            if( $aActivity["bonusstatus"] != 2 )
            { // 奖金没有派送 (-4:没有派奖, -5:已经派奖部分)
                return -4-$aActivity["bonusstatus"];
            }
            return $this->oDB->update( "activity", "`isdel`='1'", "`activityid`=".$iActivityId );
        }
        elseif( $aActivity["isverify"] == 2 )
        {
            if( $aActivity["bonusstatus"] != 2 )
            { // 奖金没有派送 (-4:没有派奖, -5:已经派奖部分)
                return -4-$aActivity["bonusstatus"];
            }
            return $this->oDB->update( "activity", "`isdel`='1'", "`activityid`=".$iActivityId );
        } //状态错误
        return 1;
    }



    /**业务逻辑**/
    /**(JAMES部分)**/

    /**(SAUL部分)**/
    /**
     * 对活动进行审核或者取消审核
     * @author SAUL    090625
     * @param  integer $iActivityId
     * @param  integer $iStatus
     * @return integer
     */
    public function activitysetStatus( $iActivityId, $iStatus )
    {
        $iActivityId = isset($iActivityId)&&is_numeric($iActivityId) ? intval($iActivityId) : 0;
        $iStatus = in_array($iStatus, array(0,1,2)) ? intval($iStatus) : -1;
        if( $iStatus < 0 )
        { // 状态错误
            return -1;
        }
        if($iActivityId <= 0)
        { // 参数错误
            return -1;
        }
        $aActivity = $this->activityGetOne( '*', "`activityid`='".$iActivityId."'" );
        if( empty($aActivity) )
        { // 活动不存在
            return -1;
        }
        if( $aActivity["isverify"] == $iStatus )
        { // 状态已经为目标状态
            return 0;
        }
        if( $aActivity["isdel"] == 1 )
        { // 被删除
            return -2;
        }
        if( $iStatus==1 )
        { // 验证
            if( strtotime($aActivity["starttime"])<=time() )
            { // 时间已过
                
                return -3;
            }
            $this->oDB->query("SELECT * FROM `activityinfo` WHERE `activityid`='".$iActivityId."'");
            if( $this->oDB->ar()==0 )
            { // 检验是否有题目
                return -4;
            }
            $this->oDB->query("SELECT * FROM `activityuser` WHERE `activityid`='".$iActivityId."'");
            if( $this->oDB->ar()==0 )
            { // 检验是否有用户
                return -5;
            }
            $iCheckId = isset($_SESSION["admin"])&&is_numeric($_SESSION["admin"]) ? $_SESSION["admin"] : 0;
            if( $iCheckId == 0 )
            { // 检查人员ID 不正确
                return -6;
            }
            if( $iCheckId == $aActivity["sendid"])
            { // 检查人员和审核人员不能为同一个人
                return -7;
            }
        }
        elseif( $iStatus ==2 )
        { // 强制关闭
            if( $aActivity["isverify"] == 0 )
            { // 状态不正确
                return -8;
            }
            if( strtotime($aActivity["starttime"])>=time() )
            { // 时间未到
                return -9;
            }
            if( strtotime($aActivity["endtime"])<=time() )
            { // 时间已过
                return -10;
            }
            $iCheckId = $aActivity["checkid"];
        }
        else
        {
            $iCheckId = 0;
        }   //确认无误提交
        $this->oDB->query("UPDATE `activity` SET `isverify`='".$iStatus."',`checkid`='".$iCheckId."' 
                           WHERE `activityid`='".$iActivityId."'");
        return $this->oDB->ar();
    }



    /**
     * 批量删除活动
     * @author SAUL 
     * @param  array $aActivityId
     * @return array
     */
    public function batDel( $aActivityId )
    {
        if( !is_array($aActivityId) )
        {
            return -1;
        }
        foreach( $aActivityId as $iKey => $iActivityId )
        {
            if(!is_numeric($iActivityId))
            {
                unset($aActivityId[$iKey]);
            }
        }
        //批量查询
        if(empty($aActivityId))
        {
            return -1;
        }
        $aActivitys = $this->oDB->getAll("SELECT * FROM `activity` WHERE `activityid` IN (".join(',',$aActivityId).")");
        if(empty($aActivitys))
        {
            return -1;
        }
        $aTemp = array();
        foreach ($aActivitys as $aActivity)
        {
            if( $aActivity["isdel"] >= 1 )
            { // 已经是删除状态
                break;
            }
            if( $aActivity["isverify"] >= 1 )
            { // 已经被验证的活动或者为强制退出的活动
                if( strtotime($aActivity["endtime"]) >= time() )
                { //时间没有过
                    return -3;
                }
                if( $aActivity["bonusstatus"] != 2 )
                { // 奖金没有派送 (-4:没有派奖, -5:已经派奖部分)
                    return -4-$aActivity["bonusstatus"];
                }
            } //状态错误
            $aTemp[] = $aActivity['activityid'];
        }
        //全部数组
        if(empty($aTemp))
        {
            return 0;
        }
        return $this->oDB->update('activity',array('isdel'=>1),"`activityid` in (".join(',',$aTemp).")");
    }
    /**(TOM部分)**/

}
?>