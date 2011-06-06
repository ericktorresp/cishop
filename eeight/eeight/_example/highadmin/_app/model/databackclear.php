<?php
/**
 * 数据清理与备份
 * 
 * 功能：数据备份和清理
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 */

class model_databackclear extends basemodel
{
    
    
    /**
     * 数据备份
     * 
     * @param string $sTable            备份表名
     * @param string $sWhere            查询条件
     * @param string $sFileName         备份文件名
     * @param string $sRalationTable    多表关联查询
     * @return boolean
     * 
     * @author mark
     *
     */
    public function backData( $sTable = '', $sWhere = '1', $sFileName = "", $sRalationTable = '' )
    {
        if( $sTable == '' || $sFileName == '')
        {
            return FALSE;
        }
        if( file_exists($sFileName) )
        {
            return FALSE;//已经备份完成
        }
        if( $sRalationTable != '' )
        {
            $aCount = $this->oDB->getOne("SELECT COUNT(*) AS datacount FROM " . $sRalationTable . " WHERE " . $sWhere . ' LIMIT 1');
        }
        else 
        {
            $aCount = $this->oDB->getOne("SELECT COUNT(*) AS datacount FROM `" . $sTable . "` WHERE " . $sWhere . ' LIMIT 1');
        }
        $iCount = $aCount['datacount'];
        $iSize = 50000;
        $iPages = ceil($iCount/$iSize);

        $gz = gzopen($sFileName, 'w9');
        for( $iPage = 0; $iPage < $iPages; $iPage++ )
        {
            $sFileContent = "";
            if( $sRalationTable != '' )
            {
                $aData = $this->oDB->getAll("SELECT `$sTable`.* FROM " . $sRalationTable . " WHERE " . $sWhere . " LIMIT ".($iPage * $iSize).",".$iSize);
            }
            else
            {
                $aData = $this->oDB->getAll("SELECT * FROM `" . $sTable . "` WHERE " . $sWhere . " LIMIT ".($iPage * $iSize).",".$iSize);
            }
            foreach($aData as $aDataDetail)
            {
                $aKeys = array();
                $aValues = array();
                foreach( $aDataDetail as $key => $value )
                {
                    $aKeys[] = "`".$key."`";
                    if(is_null($value))
                    {
                        $values[] = 'NULL';
                    }
                    else
                    {
                        $aValues[] = "'".$this->oDB->es($value)."'";
                    }
                }
                $sSql = "INSERT INTO `" . $sTable . "` (".join(",",$aKeys).") VALUES (".join(",",$aValues).");";
                unset($aKeys);
                unset($aValues);
                $sFileContent .= $sSql."\n";
            }
            gzwrite($gz, $sFileContent);
        }
        gzclose($gz);
        unset($sFileContent);
        return TRUE;
    }
    
    
    /**
     * 数据清理
     * 
     * @param string $sTable         清理表名
     * @param string $sWhere         清理条件
     * @param string $sRalationTable 多表查询
     * @return boolean
     * 
     * @author mark
     * 
     * 
     */
    public function clearData( $sTable = '', $sWhere = '1', $sRalationTable = '' )
    {
        if( $sTable == '' )
        {
            return FALSE;
        }
        if( $sRalationTable != '' )
        {//多表查询删除
            $this->oDB->query("DELETE $sTable FROM " .$sRalationTable. " WHERE" . $sWhere);
        }
        else 
        {//单表删除 
            $this->oDB->query("DELETE FROM `" .$sTable. "` WHERE" . $sWhere);
        }
		return ( $this->oDB->errno() == 0 );
    }
    
    
    /**
     * 平台相关数据的备份和清理操作
     * 
     * 删除在指定时间以前的相关数据
     * 
     * $iClearId:清理类型
     * 
     * 所有的操作以及影响的表如下:
     *      1、管理员日志清理:管理员日志表[adminlog]
     *      2、用户日志清理:用户日志表[userlog]
     *      3、帐变清理:帐变表[orders]
     *      4、奖期清理:销量表[salebase]、追号详情表[taskdetail]、追号表[task]、奖期表[issueinfo].
     *      5、方案清理:扩展号码表[expandcode]、用户返点差表[userdiffpoints]、方案表[projects]
     *      6、历史封锁表清理:所有历史封锁表
     * 
     * @param int $iDay         清理天数
     * @param string $sFileName 备份文件名
     * @param int $iClearId     清理类型
     * @return boolean
     * 
     * @author mark
     *
     */
    public function backAndClear( $iDay = 0, $sFileName = '', $iClearId = 0)
    {
        if( $sFileName == ''|| $iClearId == 0 || !is_numeric($iDay) )
        {
            return FALSE;
        }
        $iDay = intval($iDay);
        if( $iDay <= 0 )
        {
            return FALSE;
        }
        switch ($iClearId)
        {
            case 1://管理员日志清理和备份
                $sTable = "adminlog";
                $sWhere = " `times`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'";
                $this->backData( $sTable, $sWhere, $sFileName );
                $this->clearData($sTable, $sWhere);
                break;
            case 2://用户日志清理和备份
                $sTable = "userlog";
                $sWhere = " `times`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'";
                $this->backData( $sTable, $sWhere, $sFileName );
                $this->clearData($sTable, $sWhere);
                break;
            case 3://帐变清理和备份
                $sTable = "orders";
                $sWhere = " `times`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'";
                $this->backData( $sTable, $sWhere, $sFileName );
                $this->clearData($sTable, $sWhere);
                break;
            case 4://奖期清理和备份:追号详情表[taskdetail]、追号表[task]、奖期表[issueinfo]. 
                // step 01追号详情表的处理开始--------------------------------------------------------------
                if( FALSE == $this->oDB->doTransaction() )
                {
                    return FALSE;
                }
                $sTable = "taskdetails";
                $sRalationTable = " `$sTable` LEFT JOIN `tasks` ON(`$sTable`.`taskid`=`tasks`.`taskid` ) 
                    LEFT JOIN `issueinfo` ON (`issueinfo`.`issue`=`$sTable`.`issue` AND `tasks`.`lotteryid`=`issueinfo`.`lotteryid`) ";
                $sWhere = " `issueinfo`.`saleend`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'";
                $sTmpFileName = substr($sFileName,0,strlen($sFileName) - 3 )."_".$sTable.".gz";
                $this->backData( $sTable, $sWhere, $sTmpFileName, $sRalationTable );
                $this->clearData($sTable, $sWhere, $sRalationTable);
                if($this->oDB->errno() > 0)
                {
                    $this->oDB->doRollback();
                    return FALSE;
                }
                unset($sTmpFileName);
                // step 01追号详情表的处理结束--------------------------------------------------------------
                
                // step 02追号表的处理开始--------------------------------------------------------------
                $sTable = "tasks";
                $sRalationTable = " `$sTable` LEFT JOIN `issueinfo` ON(`$sTable`.`beginissue`=`issueinfo`.`issue` AND `$sTable`.`lotteryid`=`issueinfo`.`lotteryid`) ";
                $sWhere = " `issueinfo`.`saleend`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'";
                $sTmpFileName = substr($sFileName,0,strlen($sFileName) - 3 )."_".$sTable.".gz";
                $this->backData( $sTable, $sWhere, $sTmpFileName, $sRalationTable );
                $this->clearData($sTable, $sWhere, $sRalationTable);
                if($this->oDB->errno() > 0)
                {
                    $this->oDB->doRollback();
                    return FALSE;
                }
                unset($sTmpFileName);
                // step 02追号表的处理结束--------------------------------------------------------------
                
                // step 03奖期表的处理开始--------------------------------------------------------------
                $sTable = "issueinfo";
                $sWhere = " `saleend`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'";
                $sTmpFileName = substr($sFileName,0,strlen($sFileName) - 3 )."_".$sTable.".gz";
                $this->backData( $sTable, $sWhere, $sFileName );
                $this->clearData($sTable, $sWhere);
                if($this->oDB->errno() > 0)
                {
                    $this->oDB->doRollback();
                    return FALSE;
                }
                unset($sTmpFileName);
                // step 03奖期表的处理结束--------------------------------------------------------------
                if( FALSE == $this->oDB->doCommit() )
                {//事务提交失败
                    return FALSE;
                }
                break;
            case 5://方案清理和备份:扩展号码表[expandcode]、用户返点差表[userdiffpoints]、方案表[projects]
                //获取需要清理的数据中方案ID最大的IDk号,在封锁表中小于这个ID号的都清理
                $sProjectSql = " SELECT `projectid` FROM `projects` 
                        WHERE `projects`.`writetime`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'  
                        ORDER BY `projectid` DESC LIMIT 1";
                $aProject = $this->oDB->getOne($sProjectSql);
                if( FALSE == $this->oDB->doTransaction() )
                {
                    return FALSE;
                }
                //方案已经清理完成
                if( !empty($aProject) )
                {
                    // step 01扩展号码表的处理开始--------------------------------------------------------------
                    $sTable = "expandcode";
                    $sWhere = " `projectid` <= '" .$aProject['projectid']."'";
                    $sTmpFileName = substr($sFileName,0,strlen($sFileName) - 3 )."_".$sTable.".gz";
                    $this->backData( $sTable, $sWhere, $sTmpFileName );
                    $this->clearData($sTable, $sWhere);
                    if($this->oDB->errno() > 0)
                    {
                        $this->oDB->doRollback();
                        return FALSE;
                    }
                    unset($sTmpFileName);
                    // step 01扩展号码表的处理结束--------------------------------------------------------------

                    // step 02用户返点差表的处理开始--------------------------------------------------------------
                    $sTable = "userdiffpoints";
                    $sWhere = " `projectid` <= '" .$aProject['projectid']."'";
                    $sTmpFileName = substr($sFileName,0,strlen($sFileName) - 3 )."_".$sTable.".gz";
                    $this->backData( $sTable, $sWhere, $sTmpFileName );
                    $this->clearData($sTable, $sWhere );
                    if($this->oDB->errno() > 0)
                    {
                        $this->oDB->doRollback();
                        return FALSE;
                    }
                    unset($sTmpFileName);
                    // step 02用户返点差表的处理结束---------------------------------------------------------
                }
                // step 03方案表的处理开始--------------------------------------------------------------
                $sTable = "projects";
                $sWhere = " `writetime`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'";
                $sTmpFileName = substr($sFileName,0,strlen($sFileName) - 3 )."_".$sTable.".gz";
                $this->backData( $sTable, $sWhere, $sTmpFileName );
                $this->clearData($sTable, $sWhere);
                if($this->oDB->errno() > 0)
                {
                    $this->oDB->doRollback();
                    return FALSE;
                }
                unset($sTmpFileName);
                // step 03方案表的处理结束--------------------------------------------------------------
                
                // step 04定单表的处理开始--------------------------------------------------------------
                $sTable = "package";
                $sWhere = " `writetime`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "'";
                $sTmpFileName = substr($sFileName,0,strlen($sFileName) - 3 )."_".$sTable.".gz";
                $this->backData( $sTable, $sWhere, $sTmpFileName );
                $this->clearData($sTable, $sWhere);
                if($this->oDB->errno() > 0)
                {
                    $this->oDB->doRollback();
                    return FALSE;
                }
                unset($sTmpFileName);
                // step 04定单表的处理结束--------------------------------------------------------------
                if( FALSE == $this->oDB->doCommit() )
                {//事务提交失败
                    return FALSE;
                }
                break;
            case 6://历史封锁表、销量表的清理、不做备份
                $oLock = new model_locks();
                $aLockTable = $oLock->getAllLockTable();
                foreach ( $aLockTable as $aTable )
                {
                    /*表名不正确*/
                    if( $aTable['locktablename'] == '' )
                    {
                        continue;
                    }
                    /*检测表是否不存在*/
                    $sCheckTableSql = " SHOW TABLES LIKE 'history_" . $aTable['locktablename'] . "'";
                    $aCheckResult = $this->oDB->getOne($sCheckTableSql);
                    if( empty($aCheckResult) )
                    {
                        continue;
                    } 
                    //获取需要清理的数据中奖期最大的期号,在封锁表中小于这个期号的都清理
                    $sIsssueSql = " SELECT `issue` FROM `issueinfo` 
                        WHERE `lotteryid` = '".$aTable['lotteryid'] ."'
                        AND `salestart`<'" . date("Y-m-d 00:00:00",strtotime("-".$iDay." days")) . "' 
                        ORDER BY `issue` DESC LIMIT 1";
                    $aIssue = $this->oDB->getOne($sIsssueSql);
                    //如果奖期已经清理,取最近一期奖期进行历史封锁表的清理
                    if( empty($aIssue) )
                    {
                        $sIsssueSql = " SELECT `issue` FROM `issueinfo` WHERE `lotteryid` = '".$aTable['lotteryid'] ."' LIMIT 1 ";
                        $aIssue = $this->oDB->getOne($sIsssueSql);
                    }
                    //历史封锁表的清理
                    $sTable = "history_" . $aTable['locktablename'];
                    $sWhere = " `issue` <= '" . $aIssue['issue'] . "'";
                    $this->clearData($sTable, $sWhere);
                    // 销量表的处理
                    $sTable = "salesbase";
                    $sWhere = " `lotteryid` = '".$aTable['lotteryid'] ."' AND `issue` <= '" . $aIssue['issue'] . "' AND `lockname` = '".$aTable['locktablename']."'";
                    $this->clearData($sTable, $sWhere);
                }
                break;
            case 7://最近投注清理和备份
                $sTable = "recentbuy";
                $sWhere = " `date`<'" . date("Y-m-d",strtotime("-".$iDay." days")) . "'";
                $this->backData( $sTable, $sWhere, $sFileName );
                $this->clearData($sTable, $sWhere);
                break;
            case 8://单期盈亏清理和备份
                $sTable = "singlesale";
                $sWhere = " `joindate`<'" . date("Y-m-d",strtotime("-".$iDay." days")) . "'";
                $this->backData( $sTable, $sWhere, $sFileName );
                $this->clearData($sTable, $sWhere);
                break;
            case 9://历史奖期清理和备份
                $sTable = "issuehistory";
                $sWhere = " `belongdate`<'" . date("Y-m-d",strtotime("-".$iDay." days")) . "'";
                $this->backData( $sTable, $sWhere, $sFileName );
                $this->clearData($sTable, $sWhere);
                break;
            default:
                break;
        }
        return TRUE;
    }
    
}