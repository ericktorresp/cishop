<?php
/**
 * 封锁信息管理
 * 
 * 功能:
 * --getLotteryTotalLockList    获取彩种总封锁设值
 * --getMethodLockList          获取玩法封锁列表
 * --getAllLockTable            获取指定彩种所有封锁表
 * --getAllLockPrize            获取指定彩种所有封锁表中的当期开奖号码封锁值
 * --getMethodLock              获取玩法封锁值
 * --getMethodGroupList         获取指定封锁表中的玩法组列表
 * --getLockById                获取指定封锁表
 * --updateLock                 更新封锁值
 * --addLockTable               增加封锁表
 * --CheckLockTableExist        检测封锁表名是否已经存在
 * --deleteLock                 删除封锁表
 * --getLockTableMethod         获取封锁表相中的关玩法
 * --getLockData                获取封锁表数据用于查看封锁
 * --salesGetMoneys             查询某一期或某几期的销售额
 * --lockTableTransfer          获取封锁表中数据、与奖期关联，用于封锁表的转换
 * 
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 * 
 */

class model_locks extends basemodel
{
    /**
     * 获取各彩种封锁总值
     * 
     * @author mark
     * @return array
     */
    public function getLotteryTotalLockList()
    {
        $sSql = " SELECT l.`lotteryid`,l.`cnname`,SUM(ltn.`maxlost`) AS totallock" . 
                " FROM `locktablename` AS ltn LEFT JOIN `lottery` AS l ON(l.`lotteryid` = ltn.`lotteryid`) " . 
                " GROUP BY l.`lotteryid` ";
        return $this->oDB->getAll($sSql);
    }
    
    
    /**
     * 获取玩法封锁列表
     *
     * @param int $iLotteryId 指定彩种ID
     * @author mark
     * @return array
     * 
     */
    public function getMethodLockList( $iLotteryId = 0 )
    {
        $aResult = array();
        $iLotteryId = isset($iLotteryId) && $iLotteryId != '' ? intval($iLotteryId) : 0;
        if( $iLotteryId == 0 )
        {
            return $aResult;
        }
        $sSql = " SELECT ltn.`locktableid`,l.`lotteryid`,l.`cnname`,ltn.`locktablecnname`,ltn.`locktablename`,ltn.`maxlost`,m.`methodname`,m.`pid`,mc.`crowdname`,mc.`crowdid`" . 
                " FROM `locktablename` AS ltn LEFT JOIN `lottery` AS l ON(l.`lotteryid` = ltn.`lotteryid`) ".
                " LEFT JOIN `method` AS m ON(ltn.`locktablename` = m.`lockname`) " . 
                " LEFT JOIN `method_crowd` AS mc ON(mc.`crowdid` = m.`crowdid`) " . 
                " WHERE l.`lotteryid` = '" . $iLotteryId ."'";
        return $this->oDB->getAll($sSql);
    }
    
    
    /**
     * 获取指定彩种所有封锁表
     * @author mark
     * @return array
     */
    public function getAllLockTable( $iLotteryId = 0 )
    {
        $iLotteryId = isset($iLotteryId) && $iLotteryId != '' ? intval($iLotteryId) : 0;
        if( $iLotteryId == 0 )
        {
            return $this->oDB->getAll("SELECT * FROM `locktablename` ");
        }
        return $this->oDB->getAll(" SELECT * FROM `locktablename` WHERE `lotteryid` ='" . $iLotteryId ."'");
    }
    
    
    /**
     * 获取指定彩种所有封锁表中的当期开奖号码封锁值
     * @param string $sLockTableName 封锁表名
     * @param string $sIssue         指定奖期
     * @author mark
     * @return array
     */
    public function getAllLockPrize(  $sLockTableName = '', $sIssue = '' )
    {
        $sLockTableName = isset($sLockTableName) && $sLockTableName != '' ? $sLockTableName : '';
        $sIssue         = isset($sIssue) && $sIssue != '' ? daddslashes($sIssue) : '';
        if( $sLockTableName == '' || $sIssue == '')
        {
            return array();
        }
        $sSql = " SELECT * FROM `" . $sLockTableName . "` WHERE `issue` = '" . $sIssue . "'" . ' LIMIT 1';
        $aResult = $this->oDB->getOne($sSql);
        if( empty($aResult) )
        {//如果在封锁表中找不到指定期，则到历史封锁表中进行查询
            $sLockTableName = 'history_'.$sLockTableName;
        }
        unset($aResult);
        $aData = array();        
        $aData = $this->oDB->getOne(" SELECT SUM(`prizes`) AS totlaprize FROM `". $sLockTableName . "` 
                    WHERE `issue` ='" . $sIssue ."' AND `isbonuscode` = '1' " . ' LIMIT 1');
        if( empty($aData) )
        {//在封锁表中没有找到对应期的数据
            $aData['totlaprize'] = 0;
        }
        return $aData;
    }
    
    
    /**
     * 获取玩法封锁
     *
     * @param int  $iLotteryId 指定彩种ID
     * @param int  $iMethodId  玩法ID
     * @author mark
     * @return array
     * 
     */
    public function getMethodLock( $iLotteryId = 0, $iMethodId = 0 )
    {
        $aResult = array();
        $iLotteryId = isset($iLotteryId) && $iLotteryId != '' ? intval($iLotteryId) : 0;
        if( $iLotteryId == 0 )
        {
            return $aResult;
        }
        $iMethodId = isset($iMethodId) && $iMethodId != '' ? intval($iMethodId) : 0;
        if( $iMethodId == 0 )
        {
            return $aResult;
        }
        $sSql = " SELECT * FROM `method`  WHERE `lotteryid` = '" . $iLotteryId ."' AND `methodid` = '".$iMethodId."'" . ' LIMIT 1';
        return $this->oDB->getOne($sSql);
    }
    
    /**
     * 获取玩法组列表
     *
     * @param int $iLotteryId 指定彩种ID
     * @author mark
     * @return array
     * 
     */
    public function getMethodGroupList( $iLotteryId = 0, $iCrowdId = 0 )
    {
        $aResult = array();
        $iLotteryId = isset($iLotteryId) && $iLotteryId != '' ? intval($iLotteryId) : 0;
        if( $iLotteryId == 0 )
        {
            return $aResult;
        }
        $sCondition = '';
        if($iCrowdId == 0)
        {
            $sCondition = "WHERE m.`lotteryid` = '" . $iLotteryId ."' AND m.`pid` != 0 AND m.`lockname` != ''";
        }
        else 
        {
            $sCondition = "WHERE m.`lotteryid` = '" . $iLotteryId ."' AND m.`crowdid` = '" . $iCrowdId ."' AND m.`pid` != 0 AND m.`lockname` != ''";
        }
        $sSql = " SELECT m.*,ltn.`locktablecnname` FROM `method` AS m 
                  LEFT JOIN `locktablename` AS ltn ON(ltn.`lotteryid` = m.`lotteryid` AND ltn.`locktablename` = m.`lockname`)
                  ".$sCondition."
                  GROUP BY `lockname` ORDER BY `methodid`";
        return $this->oDB->getAll($sSql);
    }
    
    
    /**
     * 获取指定封锁表
     *
     * @param  int   $iLotteryId 封锁表ID
     * @author mark
     * @return array
     * 
     */
    public function getLockById( $iLockTableId = 0 )
    {
        $aResult = array();
        $iLockTableId = isset($iLockTableId) && $iLockTableId != '' ? intval($iLockTableId) : 0;
        if( $iLockTableId == 0 )
        {
            return $aResult;
        }
        $sSql = " SELECT ltn.*,l.cnname ".
                " FROM `locktablename` AS ltn LEFT JOIN `lottery` AS l ON(l.`lotteryid`=ltn.`lotteryid`) ".
                " WHERE `locktableid` = '" .$iLockTableId."'" . ' LIMIT 1';
        return $this->oDB->getOne($sSql);
    }
    
    
    /**
     * 更新封锁值
     *
     * @param  int   $iLotteryId 封锁表ID
     * @param  float $fMaxLost   更新的封锁值
     * @return mixed
     * @author mark
     * 
     */
    public function updateLock( $iLockTableId = 0, $fMaxLost = 0.00 )
    {
        $iLockTableId = isset($iLockTableId) && $iLockTableId != '' ? intval($iLockTableId) : 0;
        if( $iLockTableId == 0 )
        {
            return -1;
        }  
        $fMaxLost = isset($fMaxLost) && $fMaxLost != '' ? floatval($fMaxLost) : 0.00;
        $aData = array();
        $aData['maxlost'] = $fMaxLost;
        $this->oDB->update('locktablename', $aData, "`locktableid` = '" .$iLockTableId ."'");
        if($this->oDB->ar() != 1 )
        {
            return -2;
        }
        return TRUE;
    }
    
    
    /**
     * 增加封锁表
     *
     * @param  int    $iLotteryId        彩种ID
     * @param  float  $fMaxLost          更新的封锁值
     * @param  string $sLockTableName    封锁表名
     * @param  string $sLockTableCnname  封锁表名
     * @param  string $sLockCodeFuntion  封锁表中奖号码确定函数
     * @return mixed
     * @author mark
     * 
     */
    public function addLockTable( $sLockTableName = '', $sLockTableCnname = '', $iLotteryId = 0, $fMaxLost = 0.00, $sLockCodeFuntion = '')
    {
        $iLotteryId = isset($iLotteryId) && $iLotteryId != '' ? intval($iLotteryId) : 0;
        if( $iLotteryId == 0 )
        {
            return -1;
        }
        if( $sLockTableName == '' )
        {
            return -2;
        }
        if( $sLockCodeFuntion == '' )
        {
            return -3;
        }
        $bIsTableExist = $this->CheckLockTableExist($sLockTableName);
        if($bIsTableExist)
        {
            return -4;
        }
        $fMaxLost = isset($fMaxLost) && $fMaxLost != '' ? floatval($fMaxLost) : 0.00;
        $aData = array();
        $aData['locktablename']     = $sLockTableName;
        $aData['locktablecnname']   = $sLockTableCnname;
        $aData['lotteryid']         = $iLotteryId;
        $aData['maxlost']           = $fMaxLost;
        $aData['codefunction']      = $sLockCodeFuntion;
        $this->oDB->insert('locktablename', $aData);
        if($this->oDB->ar() != 1 )
        {
            return -5;
        }
        return TRUE;
    }
    
    
    /**
     * 检测封锁表名是否已经存在
     *
     * @param string  $sTableName 封锁表名
     * @author mark
     * @return boolean
     */
    private  function CheckLockTableExist( $sTableName = '')
    {
        $sSql = " SELECT * FROM `locktablename` WHERE `locktablename` = '" .$sTableName."'" . ' LIMIT 1';
        $aResult = $this->oDB->getOne($sSql);
        if(empty($aResult))
        {
            return FALSE;
        }
        else 
        {
            return TRUE;
        }
    }
    
    
    /**
     * 删除封锁表
     *
     * @param  int   $iLotteryId 封锁表ID
     * @return mixed
     * @author mark
     * 
     */
    public function deleteLock( $iLockTableId = 0 )
    {
        $iLockTableId = isset($iLockTableId) && $iLockTableId != '' ? intval($iLockTableId) : 0;
        if( $iLockTableId == 0 )
        {
            return -1;
        }
        $this->oDB->delete('locktablename', "`locktableid` = '" .$iLockTableId ."'");
        if($this->oDB->ar() != 1 )
        {
            return -2;
        }
        return TRUE;
    }
    
    
    /**
     * 获取封锁表相关玩法
     * @param string $sLockTableName 封锁表名称
     * @param string $sIssue 奖期
     * @return array
     * 
     * @author mark
     */
    public function getLockTableMethod( $sLockTableName = '', $sIssue = '', $iLotteryId = 0)
    {
        //获取历史封锁表数据
        $sSql = " SELECT * FROM `issueinfo` WHERE `issue` = '" .$sIssue."' AND `lotteryid` = '".$iLotteryId."'" . ' LIMIT 1';
        $aIssue = $this->oDB->getOne($sSql);
        if($aIssue['saleend'] < date("Y-m-d 00:00:00",time()))
        {
            $sLockTableName = "history_".$sLockTableName;
        }
        //获取号码封锁值
        $sSql = " SELECT `methodid`,`methodname`
                  FROM `method` WHERE `lotteryid` = '" . $iLotteryId . "' 
                  AND `lockname` ='". $sLockTableName . "' AND `initlockfunc` != '' ";
        return $this->oDB->getAll($sSql);
    }
    
    /**
     * 获取封锁表数据用于查看封锁
     * 
     * @param string $sLockTableName 封锁表名称
     * @param string $sIssue 奖期
     * @param int    $iLotteryId 彩种
     * @param int    $sMethodId 玩法ID
     * @param int    $sSeltype  查询类型，默认为总计，指定按玩法查询
     * @return array
     * 
     * @author mark
     */
    public function getLockData( $sLockTableName = '', $sIssue = '', $iLotteryId = 0, $sMethodId = '', $sSeltype = 'total' )
    {
        $aResult = array();
        $sLockTableName = isset($sLockTableName) && $sLockTableName != '' ? $sLockTableName : '';
        $sIssue         = isset($sIssue) && $sIssue != '' ? $sIssue : '';
        $iLotteryId     = isset($iLotteryId) && $iLotteryId != 0 ? intval($iLotteryId) : 0;
        $sMethodId      = isset($sMethodId) && $sMethodId != '' ? $sMethodId : '';
        $sSeltype       = isset($sSeltype) && $sSeltype != '' ? $sSeltype : '';
        if($sLockTableName == '' || $sIssue == '' || $iLotteryId == 0 || $sMethodId == ''
                     || $sSeltype == '' || !in_array($sSeltype,array('method','total')) )
        {
            return $aResult;
        }
        $sSql = " SELECT * FROM `issueinfo` WHERE `issue` = '" .$sIssue."' AND `lotteryid` = '".$iLotteryId."'" . ' LIMIT 1';
        $aIssue = $this->oDB->getOne($sSql);//查询指定期数是否销售结束
        if( empty($aIssue) )
        {
            $aResult['error'] = 1;
            return $aResult;
        }
        if($aIssue['saleend'] < date("Y-m-d 00:00:00",time()))
        {
            $sLockTableName = "history_".$sLockTableName;//从历史封锁表中读取数据
        }
        $aResult['error'] = 0;
        //获取号码封锁值
        if( $sSeltype == 'total' )//总计
        {
            $sSql = " SELECT `issue`,`code`,SUM(`prizes`) AS totalLocks
                  FROM `". $sLockTableName ."` 
                  WHERE `issue` = '" . $sIssue ."' GROUP BY `code`";
        }
        else //按玩法查询
        {
            $sSql = " SELECT `issue`,`code`,SUM(`prizes`) AS totalLocks
                  FROM `". $sLockTableName ."` 
                  WHERE `issue` = '" . $sIssue ."' AND `methodid` = '".$sMethodId."' GROUP BY `code`";
        }
        $aResult['lose'] = $this->oDB->getAll($sSql);
        if( empty($aResult['lose']) )
        {
            $aResult['error'] = 1;
            return $aResult;
        }
        //从销量表里查销售量
        $sSql = " SELECT SUM(`moneys`) AS totalmoney,SUM(`pointmoney`) AS totalpoint FROM `salesbase` ".
                " WHERE `issue` = '".$sIssue."' AND `lotteryid` = '".$iLotteryId."' AND `lockname` = '".$sLockTableName. "'".
                " GROUP BY `issue`" . ' LIMIT 1';
        $aResult['win'] = $this->oDB->getOne($sSql);
        if( empty($aResult['win']) )
        {
            $aResult['error'] = 1;
            return $aResult;
        }
        $aResult['sales']['sum_sales'] = $aResult['win']['totalmoney'];
        if( $this->oDB->errno() > 0 )
        {
            $aResult['error'] = 1;
        }
        return $aResult;
    }
    
    
    /**
     * 查询某一期或某几期的销售额
     *
     * @author james   090812
     * @access public  
     * @param  int      $iLotteryId        //彩种ID
     * @param  string   $sIssue            //期号[或者期号数组]
     * @param  string   $sLockTableName    //封锁表名称
     */
    public function salesGetMoneys( $iLotteryId, $mIssue, $sLockTableName='' )
    {
        if( empty($iLotteryId) || !is_numeric($iLotteryId) || $iLotteryId < 1 )
        {
            return FALSE;
        }
        $iLotteryId = intval($iLotteryId);
        if( empty($mIssue) )
        {//期号错误
            return FALSE;
        }
        $sLockTableName = empty($sLockTableName) ? "" : daddslashes($sLockTableName);
        if( !is_array($mIssue) )
        {//只是单期
            $mIssue  = daddslashes($mIssue);
            $sSql    = " SELECT SUM(`moneys`) AS `salemoney`, SUM(`pointmoney`) AS `pointmoney` ".
                       " FROM `salesbase` WHERE `issue`='".$mIssue."' AND `lotteryid`='".
                       $iLotteryId."' AND `lockname`='".$sLockTableName."' " . ' LIMIT 1';
            $aResult = $this->oDB->getOne($sSql);
            if( empty($aResult) )
            {
                return FALSE;
            }
            return $aResult;
        }
        else
        {//多期
            $sSql    = " SELECT SUM(`moneys`) AS `salemoney`, SUM(`pointmoney`) AS `pointmoney`,`issue` ".
                       " FROM `salesbase` WHERE `issue` IN ('".implode("','",$mIssue)."') ".
                       " AND `lotteryid`='".$iLotteryId."' AND `lockname`='".$sLockTableName."' GROUP BY `issue` ";
            $aResult = $this->oDB->getAll($sSql);
            if( empty($aResult) )
            {
                return FALSE;
            }
            return $aResult;
        }
    }
    
    /**
     * 获取封锁表中数据、与奖期关联，用于封锁表的转换
     * @param int $sTableName          封锁表名称
     * @param int $aSecondDayIssue     第二天的奖期
     * @param boolean
     * 
     * @author mark
     */
    public function lockTableTransfer( $sTableName = '', $aSecondDayIssue = array() )
    {
        $sTableName = isset($sTableName) && $sTableName != '' ? $sTableName : '';
        /*表名不正确*/
        if( $sTableName == '' )
        {
            return FALSE;
        }
        /*检测封锁表是否已经存在*/
        $sCheckTableSql = " SHOW TABLES LIKE '" . $sTableName . "'";
        $aCheckLockResult = $this->oDB->getOne($sCheckTableSql);
        if( empty($aCheckLockResult) )
        {
            return FALSE;
        }
        /*检测历史封锁表是否已经存在*/
        $sHistoryTableName = "history_".$sTableName;
        $sCheckTableSql = " SHOW TABLES LIKE '" . $sHistoryTableName . "'";
        $aCheckResult = $this->oDB->getOne($sCheckTableSql);
        if( empty($aCheckResult) )
        {
            $sTransferSql = " CREATE TABLE $sHistoryTableName LIKE $sTableName";
            $this->oDB->query($sTransferSql);
            if( $this->oDB->errno() > 0)
            {
                return FALSE;
            }
        }
        $sHistoryTime = date("Y-m-d", time());
        //获取需要转换的数据中奖期最大的期号,在封锁表中小于这个期号的都转换
        $sSql = " SELECT DISTINCT(A.`issue`) FROM `" . $sTableName . "` AS A LEFT JOIN `issueinfo` AS B ON(A.`issue`=B.`issue`)";
        $sSql .= " WHERE B.`belongdate`< '" .$sHistoryTime. "' ORDER BY `issue` DESC LIMIT 1";
        $aIssue = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0)
        {
            return FALSE;
        }
        if( !empty($aIssue) )
        {
            //转存封锁表数据到历史封锁表当中
            $sInsertSql = "INSERT IGNORE `" . $sHistoryTableName ."` SELECT * FROM `" . $sTableName . "` WHERE ";
            $sTempSql = $sInsertSql . " `issue` <= '" . $aIssue['issue'] . "'";
            $this->oDB->query($sTempSql);
            if( $this->oDB->errno() > 0)
            {
                return FALSE;
            }
            //更新过期数据为第二天的数据
            /**
             * 如果：2010-04-01天执行此程序，在这个过程中将2010-03-31的封锁数值置0，奖期更新为相应的2010-04-02的奖期
             */
            //获取过期的奖期
            $sUpdateSql = " SELECT DISTINCT(`issue`) FROM `" . $sTableName . "` FORCE INDEX (`idx_issue`) 
                        WHERE `issue` <= '" . $aIssue['issue'] . "'";
            $aUpdateIssue = $this->oDB->getAll($sUpdateSql);
            if( $this->oDB->errno() > 0)
            {
                return FALSE;
            }
            //奖期规则发生变化、重新生成所有数据
            if( count($aUpdateIssue) != count($aSecondDayIssue) )
            {
                $sDeleteSql = "DELETE FROM `" . $sTableName . "` WHERE `issue` <= '" . $aIssue['issue'] . "'";
                $this->oDB->query($sDeleteSql);
                return FALSE;
            }
            $this->oDB->doTransaction();
            foreach ( $aUpdateIssue as $iKey => $aIssueValue )
            {
                $sIssue = $aSecondDayIssue[$iKey]['issue'];//更新为第二天的奖期
                $aData = array( 'issue'=>$sIssue, 'prizes'=>0, 'isbonuscode'=>0 );
                $sWhere = " `issue` = '".$aIssueValue['issue']."' ";
                $this->oDB->update($sTableName, $aData, $sWhere);
                if( $this->oDB->errno() > 0)
                {
                    $this->oDB->rollback();
                    return FALSE;
                }
            }
            $this->oDB->doCommit();
        }
        return TRUE;
    }
    
    
    /**
     * 检测当天封锁表数据是否完整的存在
     *
     * @param  string $sTableName 封锁表名
     * @param  int    $iMethodId  玩法ID
     * @author mark
     */
    public function checkCurrentDayLockData( $sTableName = '', $iMethodId = 0 )
    {
        if( $sTableName == '' || $iMethodId == 0 )
        {
            return FALSE;
        }
        /*检测封锁表是否已经存在*/
        $sCheckTableSql = " SHOW TABLES LIKE '" . $sTableName . "'";
        $aCheckLockResult = $this->oDB->getOne($sCheckTableSql);
        if( empty($aCheckLockResult) )
        {
            return FALSE;
        }
        $iMethodId = intval($iMethodId);
        $sCurrentDay = date("Y-m-d", time());
        $sSecondDay  = date("Y-m-d", time() + 86400);
        $sSql = " SELECT COUNT(DISTINCT(A.`issue`)) AS `issuetotal` FROM `" . $sTableName . "` AS A LEFT JOIN `issueinfo` AS B ON(A.`issue`=B.`issue`)";
        $sSql .= " WHERE A.`methodid` = '" . $iMethodId . "' AND ( B.`belongdate` BETWEEN '" .$sCurrentDay. "' AND '".$sSecondDay."') LIMIT 1";
        $aIssue = $this->oDB->getOne($sSql);
        if( $this->oDB->errno() > 0)
        {
            return FALSE;
        }
        if( !empty($aIssue) && is_array($aIssue) )
        {
            return $aIssue['issuetotal'];
        }
        return FALSE;
    }
}