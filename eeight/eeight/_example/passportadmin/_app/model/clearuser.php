<?php
/**
 * 文件 : /_app/model/clearuser.php
 * 功能 : 数据模型 - 用户清理报表统计
 * 
 * @author	   mark
 * @version    1.0.0
 * @package    passportadmin
 * @since      2010-05-21
 * 
 */
define("ORDER_TYPE_PDXEZR",    18);   // 频道小额转入     pid=0   + 游戏币
define("ORDER_TYPE_XEKC",      19);   // 小额扣除        pid=0   - 游戏币
define("ORDER_TYPE_XEJS",      20);   // 小额接收        pid=0   + 游戏币
class model_clearuser extends basemodel
{
    /**
     * 获取清理用户报表数据
     *
     * @param string $sCondition 查询条件
     * @return array
     * 
     * @author mark
     * 
     */
    public function getReportData( $sCondition = '' )
    {
        $aResult = array();
        //频道小额转入
        $sSql = " SELECT SUM(o.`amount`) AS totalmoney,ut.`lvtopid`,o.`transferchannelid` ";
        $sSql .= " FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) ";
        $sSql .= " WHERE o.`ordertypeid` = '".ORDER_TYPE_PDXEZR."'" . $sCondition ;
        $sSql .= " GROUP BY ut.`lvtopid`,o.`transferchannelid` ";
        $aTransferData = $this->oDB->getAll($sSql);
        foreach ( $aTransferData as $aData )
        {
            $aResult[$aData['lvtopid']]['tranfer'][$aData['transferchannelid']] = $aData['totalmoney'];
        }
        // 小额接收
        $sSql = " SELECT SUM(o.`amount`) AS totalmoney,ut.`lvtopid` ";
        $sSql .= " FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) ";
        $sSql .= " WHERE o.`ordertypeid` = '".ORDER_TYPE_XEJS."'" . $sCondition;
        $sSql .= " GROUP BY ut.`lvtopid` ";
        $aSmallCashInData = $this->oDB->getAll($sSql);
        foreach ( $aSmallCashInData as $aData )
        {
            $aResult[$aData['lvtopid']]['smallcashin'] = $aData['totalmoney'];
        }
        // 小额扣除
        $sSql = " SELECT SUM(o.`amount`) AS totalmoney,ut.`lvtopid` ";
        $sSql .= " FROM `orders` AS o LEFT JOIN `usertree` AS ut ON(o.`fromuserid` = ut.`userid`) ";
        $sSql .= " WHERE o.`ordertypeid` = '".ORDER_TYPE_XEKC."'" . $sCondition;
        $sSql .= " GROUP BY ut.`lvtopid` ";
        $aSmallCashOutData = $this->oDB->getAll($sSql);
        foreach ( $aSmallCashOutData as $aData )
        {
            $aResult[$aData['lvtopid']]['smallcashout'] = $aData['totalmoney'];
        }
        return $aResult;
    }
}
?>