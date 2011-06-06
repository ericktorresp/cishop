<?php
/**
 *  用户答案模型
 * 
 * 功能: 
 * 
 * @author  SAUL
 * @version 1.1.0
 * @package passportadmin
 * @since   090626 - 
 */
class model_activityanswer extends basemodel
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
     * 增加问题
     *
     * @param integer $iActivityId
     * @param integer $iUserId
     * @param array   $aAnswers
     */
    function activityAnswerInsert( $iActivityId, $iUserId, $aAnswers )
    {
        $iActivityId = is_numeric($iActivityId) ? intval($iActivityId) : 0;
        $iUserId     = is_numeric($iUserId) ? intval($iUserId) : 0;
        $aAnswers    = is_array($aAnswers) ? $aAnswers : array();
        if( $iActivityId <=0 || $iUserId<=0 || empty($aAnswers) )
        { // 参数错误
            return -1;
        }
        $sSql = "SELECT * FROM `activity` WHERE `activityid`='".$iActivityId."' AND `isdel`='0'";
        $aActivity = $this->oDB->getOne( $sSql );
        if( empty($aActivity) )
        { // 没有这个活动
            return -2;
        }
        if( $aActivity["isverify"] != 1 )
        { // 活动未验证或者已强制停止
            return -3;
        }
        if( strtotime($aActivity["starttime"]) > time() )
        { //活动未开始
            return -4;
        }
        if( strtotime($aActivity["endtime"]) < time() )
        { // 活动已结束
            return -5;
        }
        $sSql = "SELECT * FROM `activityuser` WHERE `activityid`='".$iActivityId."' AND `userid`='".$iUserId."'";
        $aActivityUser = $this->oDB->getOne( $sSql );
        if( empty($aActivityUser))
        { // 用户是否需要答题
            return -6;
        }
        if( !empty($aActivityUser["answertime"]) || !empty($aActivityUser["ip"]) )
        { // 检验用户是否答题
            return -7;	
        }
        $aTempData     = array(); //用户答案进入数据库数据
        $iTempScore    = 0;   //用户答题分数
        $sSql          = " SELECT * FROM `activityinfo` WHERE `activityid`='".$iActivityId."' ";
        $aActivityInfo = $this->oDB->getAll( $sSql );
        foreach( $aActivityInfo as $v )
        {
            if( $v['isrequire'] == 1 && empty( $aAnswers['answer_'.$v['infoid']] ) )
            {//必答题未答
                return -8;
            }
            $aTempArr = array();
            $aTempArr['infoid']    = $v['infoid'];
            $aTempArr['userid']    = $iUserId;
            $aTempArr['answerid']  = "";
            $aTempArr['answermsg'] = "";
            $mTemp_Answer          = isset($aAnswers['answer_'.$v['infoid']]) ? 
                                       $aAnswers['answer_'.$v['infoid']] : "";
            $mTemp_Other           = isset($aAnswers['other_answer_'.$v['infoid']]) ?
                                       $aAnswers['other_answer_'.$v['infoid']] : "";
            if( $v['type'] == 0 )
            {//单选
                $aTempArr['answerid'] = daddslashes( $mTemp_Answer );
                if( $mTemp_Answer == 'other' )
                {
                    $aTempArr['answermsg'] = daddslashes($mTemp_Other);
                }
                if( $aActivity['type'] == 1 && !empty($v['answer']) && $aTempArr['answerid'] == $v['answer'] )
                {
                    $iTempScore += intval( $v['score'] );
                }
            }
            elseif( $v['type'] == 1 )
            {//多选
                $aTempArr['answerid'] = is_array($mTemp_Answer) ? daddslashes(implode(",",$mTemp_Answer))
                                         : daddslashes($mTemp_Answer);
                if( preg_match("/other/i", $aTempArr['answerid']) )
                {
                    $aTempArr['answermsg'] = daddslashes($mTemp_Other);
                }
                if( $aActivity['type'] == 1 && !empty($v['answer']) )
                {
                    $aTempA = explode( ",", $v['answer'] );
                    $aTempB = explode( ",", $aTempArr['answerid'] );
                    $aNewTempAB = array_intersect( $aTempB, $aTempA );
                    if( $v['minright'] != 0 && count($aNewTempAB) >= $v['minright'] )
                    {
                        $iTempScore += intval( $v['score'] );
                    }
                    else if( count($aNewTempAB) == count($aTempA) )
                    {
                        $iTempScore += intval( $v['score'] );
                    }
                }
            }
            else
            {
                $aTempArr['answermsg'] = daddslashes($mTemp_Answer);
            }
            $aTempData[] = $aTempArr;
            
        }
        $this->oDB->doTransaction(); //事物开始
        foreach( $aTempData as $v )
        {
            $iResult = $this->oDB->insert( "activityanswer", $v );
            if( $iResult <= 0 )
            {//插入答案失败
                $this->oDB->doRollback();
                return -9;
            }
        }
        $aData = array(
                          'answertime'  => date("Y-m-d H:i:s"),
                          'score'       => $iTempScore,
                          'bonusstatus' => 0,
                          'ip'          => getRealIP(),
                          'cdnip'       => $_SERVER['REMOTE_ADDR'],
                       );
        if( $aActivity['type'] == 1 )
        {//有奖竟猜需要判断分数
            if( $iTempScore >= $aActivity['minscore'] )
            {//获奖
                $aData['bonusstatus'] = 1;
            }
            else 
            {
                $aData['bonusstatus'] = 2;
            }
        }
        else 
        {//直接派奖
            $aData['bonusstatus'] = 1;
        }
        $sCondition = " `userid`='".intval($iUserId)."' AND `activityid`='".intval($iActivityId)."' ";
        $iResult    = $this->oDB->update( 'activityuser', $aData, $sCondition );
        if( $iResult < 1 )
        { // 更新用户是否答题失败
            $this->oDB->doRollback();
            return -10;
        }
        $this->oDB->doCommit(); //事物结束
        return 1;
    }



    function activityAnswerGetList( $sFields = '', $sCondition = '', $sOrderBy = '', $iPageRecord = 0, $iCurrentPage = 0 )
    {
        if(empty($sFields))
        {
            $sFields ="*";
        }
        else
        {
            $sFields = $sFields;
        }
        if(empty($sCondition))
        {
            $sCondition = "1";
        }
        if(empty($sOrderBy))
        {
            $sOrderBy = "";
        }
        $iPageRecord = is_numeric($iPageRecord)&&$iPageRecord > 0 ? intval($iPageRecord) : 0;
        if( $iPageRecord <= 0 )
        { // 返回全部的结果集
            return $this->oDB->getAll("SELECT ".$sFields." FROM `activityanswer` WHERE ".$sCondition." ".$sOrderBy);
        }
        else
        { // 分页显示
            $iCurrentPage = isset($iCurrentPage)&&is_numeric($iCurrentPage) ? intval($iCurrentPage) : 1;
            if( $iCurrentPage <= 0 )
            {
                $iCurrentPage = 1;
            }
            return $this->oDB->getPageResult("activityanwer", $sFields, $sCondition, $iPageRecord, $iCurrentPage, $sOrderBy);
        }
    }
}
?>