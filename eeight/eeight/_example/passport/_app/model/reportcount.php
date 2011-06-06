<?php
/**
 * 文件 : /_app/model/reportcount.php
 * 
 * 功能 : 数据模型 - 图表数据
 * 
 * -- getSaveAndDrawCountByUser     根据总代ID查询给所有下级的充提统计情况[按一代用户查看]
 * -- getSaveAndDrawCountByDay      获取充值提现统计[按时间查看]
 * -- getTopProxyInOutCount         更新所有总代以及所有一代某一天的充提金额统计
 * 
 * @author    James
 * @version   1.1.0
 * @package   passport
 */

class model_reportcount extends basemodel 
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


    /**
     * 根据总代ID查询给所有下级的充提统计情况[按一代用户查看]
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代用户ID
     * @param   string  $sAndWhere  //附加条件
     * @param   boolean $bIsPage    //是否采用分页
     * @param   int     $iPageRecords //每页显示的条数
     * @param   int     $iCurrPage  //当前页
     * @param   return  //成功返回统计列表array('affects'=>总记录数,'results'=>结果集合)，失败返回空数组
     */
    public function & getSaveAndDrawCountByUser( $sAndWhere = '', $bIsPage = FALSE, $iPageRecords = 20, $iCurrPage = 1 )
    {
        $sTableName  = " `usertree` AS ut LEFT JOIN `reportcount` AS rt ON ut.`userid`=rt.`userid` ";
        $sFields     = " SUM(rt.`savevalue`) AS savecount,SUM(rt.`withdrawvalue`) AS withdrawcount,
                     ut.`username`,ut.`userid` ";
        $sCondition  = " 1 ";
        $sCondition .= $sAndWhere." GROUP BY ut.`userid` ";
        
        if( (bool)$bIsPage )
        {//采用分页
            return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage );
        }
        else
        {
            $sSql = "SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition;
            return $this->oDB->getAll( $sSql );
        }
    }



    /**
     * 获取充值提现统计[按时间查看]
     */
    public function & getSaveAndDrawCountByDay( $sAndWhere = '',$bIsPage = FALSE,$iPageRecords = 20,$iCurrPage = 1 )
    {
        $sTableName  = " `reportcount` AS rt LEFT JOIN `usertree` AS ut ON rt.`userid`=ut.`userid` ";
        $sFields     = " SUM(rt.`savevalue`) AS savecount,SUM(rt.`withdrawvalue`) AS withdrawcount,date(`days`) as day";
        $sCondition  = " 1 ";
        $sCondition .= $sAndWhere." GROUP BY rt.`days` ";
        if( (bool)$bIsPage )
        {//采用分页
            return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage );
        }
        else
        {
            $sSql = "SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition;
            return $this->oDB->getAll( $sSql );
        }
    }



    /**
     * 更新所有总代以及所有一代某一天的充提金额统计
     */
    public function  getTopProxyInOutCount( $sDate = '', $iStep = 0 )
    {
        $sDate          = empty($sDate) ? date("Y-m-d H:i:s") : $sDate;	//默认时间
        $iStep          = intval( $iStep );
        $sCurrentDate   = date( 'ymd', strtotime($sDate) );  //当前时间天
        $sStartTime     = date( "Y-m-d 00:00:00", strtotime($sDate) );//一天的开始时间 2009-06-08 00:00:00
        $sEndTime       = date( "Y-m-d H:i:s", (strtotime($sStartTime) + 86400 - 1) );//一天的结束时间2009-06-08 23:59:59
        $bIsFirst       = FALSE;    //是否为某一天的第一次更新
        $sCurrentTime   = date("Y-m-d H:i:s");  //当前时间点
        if( strtotime($sCurrentTime) > (strtotime($sStartTime) + 86400) )
        {
            $sCurrentTime = date( "Y-m-d H:i:s", (strtotime($sStartTime) + 86400) );
        }
        //获取所有总代ID
        $sSql = " SELECT `userid` FROM `usertree` WHERE `parentid`='0' AND `usertype`='1' AND `isdeleted`='0' ";
        $aTopUserData = $this->oDB->getAll( $sSql );
        if( empty($aTopUserData) )
        {
            return FALSE;
        }
        if( $iStep == 0 )
        {//如果为最上层运行则检测
            //检测是否为某一天的第一次更新[检查所有的总代一代是否有数据]
            $sSql = " SELECT r.`userid`,ut.`parentid`,ut.`parenttree` FROM `reportcount` AS r 
                        LEFT JOIN `usertree` AS ut ON r.`userid`=ut.`userid`
                        WHERE (ut.`parentid`='0' OR ut.`parenttree` REGEXP '^[0-9]+$') AND ut.`usertype`='1' 
                        AND r.`days`='".$sCurrentDate."' ";
            $this->oDB->query( $sSql );
            if( $this->oDB->ar() == 0 )
            {//没有数据则为第一次更新
                $bIsFirst = TRUE;
            }
        }
        if( $bIsFirst == TRUE && $iStep == 0 )
        {//如果为第一层的第一次更新，做重新更新早一天的数据
            $this->getTopProxyInOutCount( date('Y-m-d H:i:s',(strtotime($sDate) - 86400)), ($iStep + 1) );
        }
        //获取所有总代自身的充值提现统计
        $oOrder             = new model_orders();
        $aTopInCountData    = $oOrder->getCashInOutByTopUser( 0, $sStartTime, $sEndTime, 'in' );    //充值统计
        $aTopOutCountData   = $oOrder->getCashInOutByTopUser( 0, $sStartTime, $sEndTime, 'out' );   //提现统计
        foreach( $aTopUserData as $v )
        {
            /***************************************更新总代统计**********************************/
            $iTemp_In   = isset($aTopInCountData[$v['userid']]) ? $aTopInCountData[$v['userid']]['JAMESCOUNT'] : 0;
            $iTemp_Out  = isset($aTopOutCountData[$v['userid']]) ? $aTopOutCountData[$v['userid']]['JAMESCOUNT'] : 0;
            //检测是否在reportcount有数据
            $sSql = " SELECT `entry` FROM `reportcount` WHERE `userid`='".$v['userid']."' AND days='".$sCurrentDate."' ";
            $aTemp_Result = $this->oDB->getOne( $sSql );
            if( $this->oDB->numRows() > 0 )
            {//更新
                $aTemp_Data      = array(
                                            'savevalue'         => $iTemp_In, 
                                            'withdrawvalue'     => $iTemp_Out, 
                                            'times'             => $sCurrentTime
                                         );
                $sTemp_Condition = " `entry`='".$aTemp_Result['entry']."' LIMIT 1 ";
                $this->oDB->update( 'reportcount', $aTemp_Data, $sTemp_Condition );
            }
            else
            {//插入
                $aTemp_Data = array(
                                        'userid'        => $v['userid'],
                                        'savevalue'     => $iTemp_In,
                                        'withdrawvalue' => $iTemp_Out,
                                        'days'          => $sCurrentDate,
                                        'times'         => $sCurrentTime
                                );
                $this->oDB->insert( 'reportcount', $aTemp_Data );
            }
            /**************************************更新一代统计[下级统计]**********************************/
            $aChildCountData = $oOrder->getCashCountByUser( $v['userid'], $sStartTime, $sEndTime );
            $aChildCountData = $aChildCountData['result'];
            foreach( $aChildCountData as $k )
            {
                //检测是否在reportcount有数据
                $sSql = " SELECT `entry` FROM `reportcount` 
                          WHERE `userid`='".$k['userid']."' AND days='".$sCurrentDate."' ";
                $aTemp_Result = $this->oDB->getOne( $sSql );
                if( $this->oDB->numRows() > 0 )
                {//更新
                    $aTemp_Data = array(
                                            'savevalue'         =>  $k['cashin'], 
                                            'withdrawvalue'     =>  $k['cashout'], 
                                            'times'             =>  $sCurrentTime
                                      );
                    $sTemp_Condition = " `entry`='".$aTemp_Result['entry']."' LIMIT 1 ";
                    $this->oDB->update( 'reportcount', $aTemp_Data, $sTemp_Condition );
                }
                else
                {//插入
                    $aTemp_Data = array(
                                            'userid'        => $k['userid'],
                                            'savevalue'     => $k['cashin'],
                                            'withdrawvalue' => $k['cashout'],
                                            'days'          => $sCurrentDate,
                                            'times'         => $sCurrentTime
                                    );
                    $this->oDB->insert( 'reportcount', $aTemp_Data );
                }
            }
        }
        return TRUE;
    }
}
?>