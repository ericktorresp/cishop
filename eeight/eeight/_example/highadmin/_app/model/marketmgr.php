<?php
/**
 * 功能:市场管理－用户、公司盈亏排名等
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 */

class model_marketmgr extends basemodel
{
    /**
     * 试构造函数
     *
     * @param array $aDBO
     */
    public function __construct( $aDBO=array() )
    {
        parent::__construct($GLOBALS['aSysDbServer']['report']);
    }
    
    /**
     * 获取用户输赢排名
     *
     * @param  string  $sWhere          查询条件
     * @param  string  $sOrderBy        排序字段
     * @param  string  $sOrderByType    排序方式
     * @param  string  $iNumOrder       参与排名个数
     * @return array
     * 
     * @author mark
     * 
     */
    public function getUserWinOrder( $sWhere = '1', $sOrderBy = 'totalprice', $sOrderByType = 'DESC', $iNumOrder = 10 )
    {
        $sSql = " SELECT ut.`userid`,ut.`username`,SUM(p.`totalprice`) AS totalprice,".
                "  (SELECT `userip` FROM `projects` WHERE " . $sWhere . 
                "  AND `userid` = ut.`userid` ORDER BY `projectid` DESC LIMIT 1) AS userip,".
                "  SUM( if( udp.`diffmoney` IS NULL , 0, udp.`diffmoney` ) ) AS totalreturn,SUM(p.`bonus`) AS totalbonus,".
                "  SUM( p.`totalprice` -  p.`bonus` - if( udp.`diffmoney` IS NULL , 0, udp.`diffmoney` ) ) AS totallose".
                " FROM `projects` AS p ".
                " LEFT JOIN `userdiffpoints`  AS udp ON(p.`userid` = udp.`userid` AND p.`projectid` = udp.`projectid`)". 
                " LEFT JOIN `usertree` AS ut ON(p.`userid` = ut.`userid`)". 
                " WHERE p.`iscancel` = '0' AND " . $sWhere .
                " GROUP BY p.`userid` ".
                " ORDER BY " . $sOrderBy . ' ' . $sOrderByType .
                " LIMIT 0, " . $iNumOrder;
        return $this->oDB->getAll($sSql);
    }
    
    
    /**
     * 获取用户输赢详情
     *
     * @param  string  $sWhere          查询条件
     * @return array
     * 
     * @author mark
     * 
     */
    public function getUserWinDetail( $sWhere = '1', $iPageRecords = 25, $iCurrPage = 1)
    {
        $sSql = " SELECT l.`cnname`,p.*,u.`username`,m.`methodname`
                  FROM `projects` AS p 
                  LEFT JOIN `usertree`  AS u ON(u.`userid`=p.`userid`)
                  LEFT JOIN `lottery` AS l ON(l.`lotteryid`=p.`lotteryid`)
                  LEFT JOIN `method` AS m ON(p.`methodid`=m.`methodid`)
                  WHERE p.`iscancel` = '0' AND " .$sWhere;
        $sTableName = "`projects` AS p 
                        LEFT JOIN `usertree`  AS u ON(u.`userid`=p.`userid`)
                        LEFT JOIN `lottery` AS l ON(l.`lotteryid`=p.`lotteryid`)
                        LEFT JOIN `method` AS m ON(p.`methodid`=m.`methodid`)";
        $sFields = "l.`cnname`,p.*,u.`username`,m.`methodname`";
        $sCondition = "p.`iscancel` = '0' AND " .$sWhere;
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage);
    }
    
    
     /**
     * 获取参与人数排序数据
     *
     * @param  string  $sFiled          查询字段
     * @param  string  $sWhere          查询条件
     * @param  string  $sGroupBy        分组方式
     * @param  string  $sOrderBy        排序字段
     * @param  string  $sOrderByType    排序方式
     * @param  string  $iNumOrder       参与排名个数
     * @return array
     * 
     * @author mark
     * 
     */
    public function getPlayUserOrder( $sFiled = '*', $sWhere = '1', $sOrderBy = 'totalprice', 
                            $sGroupBy = 'p.`lotteryid`', $sOrderByType = 'DESC', $iNumOrder = 10 )
    {
        $sSql = " SELECT ". $sFiled . " FROM `projects` AS p ". 
                " LEFT JOIN `lottery` AS l ON(l.`lotteryid` = p.`lotteryid`)".
                " LEFT JOIN `method` AS m ON(m.`methodid` = p.`methodid`)".
                " WHERE p.`iscancel` = '0' AND " . $sWhere .
                " GROUP BY " . $sGroupBy .
                " ORDER BY " . $sOrderBy . ' ' . $sOrderByType .
                " LIMIT 0, " . $iNumOrder;
        return $this->oDB->getAll($sSql);
    }
    
    
     /**
     * 获取公司盈亏排序
     *
     * @param  string  $sWhere          查询条件
     * @param  string  $sOrderBy        排序字段
     * @param  string  $sOrderByType    排序方式
     * @param  string  $iNumOrder       参与排名个数
     * @return array
     * 
     * @author mark
     * 
     */
    public function getCompanyWinOrder( $sWhere = '1', $sOrderBy = 'totalprice', $sOrderByType = 'DESC', $iNumOrder = 10 )
    {
        $sSql = " SELECT p.`issue`,l.`cnname`,SUM(p.`totalprice`) AS totalprice,p.`userip`,".
                " SUM(p.`lvtoppoint`*p.`totalprice`) AS totalreturn,SUM(p.`bonus`) AS totalbonus,".
                " SUM(p.`totalprice`-p.`bonus`-p.`lvtoppoint`*p.`totalprice`) AS totallose".
                " FROM `projects` AS p ".
                " LEFT JOIN `lottery` AS l ON(p.`lotteryid` = l.`lotteryid`)". 
                " WHERE p.`iscancel` = '0' AND " . $sWhere .
                " GROUP BY p.`lotteryid`,p.`issue` ".
                " ORDER BY " . $sOrderBy . ' ' . $sOrderByType .
                " LIMIT 0, " . $iNumOrder;
        return $this->oDB->getAll($sSql);
    }
}