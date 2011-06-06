<?php
/**
 * 数据模型: 玩法
 * 
 * @author     Rojer, james
 * @version    1.0.0
 * @package    highadmin
 * Tom 效验通过于 0208 14:19
 */

class model_method extends basemodel
{
    function __construct( $aDBO = array())
    {
        parent::__construct( $aDBO );
    }



    /**
     * 根据玩法ID, 获取玩法信息
     * @param <int> $itemId
     * @return <array> $result
     * @author Rojer
     */
    public function getItem($itemId)
    {
        $sSql = "SELECT * FROM `method` WHERE methodid=" . intval($itemId) . ' LIMIT 1';
        $result = $this->oDB->getOne($sSql);
        if( $result )
        {
            $result['nocount']  = unserialize($result['nocount']);
            $result["areatype"] = unserialize(base64_decode($result["areatype"]));
        }
        return $result;
    }



    /**
     * 根据彩种ID, 获取玩法信息
     * @param <int> $parentId
     * @return <array>
     * @author Rojer
     */
    function getItems($lotteryId, $parentId = NULL)
    {
        if( $lotteryId <= 0 )
        {
            return array();
        }
        $sql = "SELECT * FROM `method` WHERE lotteryid = $lotteryId";
        if ($parentId !== NULL)
        {
            $sql .= ' AND pid='.intval($parentId);
        }

        $result = $this->oDB->getAll($sql);
        foreach ($result as &$v)
        {
            $v["nocount"] = unserialize($v["nocount"]);
        }
        return $result;
    }



    /**
     * 获取某个玩法的信息
     * @param string $sFields
     * @param string $sCondition
     * @return array
     * @author Rojer
     */
    function methodGetOne( $sFields='', $sCondition='' )
    {
        if(empty($sFields))
        {
            $sFields = "*";
        }
        if(empty($sCondition))
        {
            $sCondition = "1";
        }
        return $this->oDB->getOne("SELECT ".$sFields." FROM `method` WHERE ".$sCondition . ' LIMIT 1');
    }



    /**
     * 根玩法获取相应的信息
     * @param  string   $sFields   //字段，
     * @param  string   $sCondition //条件
     * @param  string   $sLeftJoin 左关联表
     * @author Rojer   100125
     * @return array
     */
    public function & methodGetInfo( $sFields='', $sCondition='', $sLeftJoin= '' )
    {
        $sFields    = empty($sFields) ? '*' : daddslashes($sFields);
        $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
        $sTable     = " `method` AS m ".$sLeftJoin;
        $sSql       = " SELECT ".$sFields." FROM ".$sTable." ".$sCondition;
        $aResult    = $this->oDB->getDataCached($sSql);
        return $aResult;
    }


    /**
     * 获取游戏玩法列表
     * @param <type> $sFields
     * @param <type> $sCondition
     * @param <type> $sOrderBy
     * @param <type> $iPageRecord
     * @param <type> $iCurrentPage
     * @return <type>
     * @author Rojer
     */
    function methodGetList( $sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
    {
        if( empty($sFields) )
        { // 默认字段
            $sFields = "*";
        }
        if( empty($sCondition) )
        {
            $sCondition = "1";
        }
        $iPageRecord  = intval( $iPageRecord );
        $sTableName ="`method` as a LEFT JOIN `lottery` as b ON (b.`lotteryid`=a.`lotteryid`)";
        if( $iPageRecord == 0 )
        {
            return $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition);
        }
        $iCurrentPage = intval( $iCurrentPage );
        if( $iCurrentPage == 0 )
        {
            $iCurrentPage = 1;
        }
        return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecord,
        $iCurrentPage, $sOrderBy );
    }



    /**
     * 添加玩法组
     * @param array $data
     * @return mix
     * @author Rojer
     */
    public function addPlayGroup($data)
    {
        if (empty($data['lotteryid']) || empty($data['methodname']) || empty($data['level']))
        {
            return FALSE;
        }
        $data["nocount"] =array();
        for($i = 1;$i<=$data["level"];$i++)
        {
            if(!is_numeric($data["count"][$i]["count"]))
            {
                return -6;
            }
            $data["nocount"][$i]["count"] = $data["count"][$i]["count"];
            $data["nocount"][$i]["name"] = $data["count"][$i]["name"];
            $data["nocount"][$i]["use"] = isset($data["count"][$i]["use"]) ? 1:0;
        }

        if(isset($data["count"]["type"])&&($data["count"]["type"]==1))
        {
            $data["nocount"]["type"] = 1;
        }
        else
        {
            $data["nocount"]["type"] = 0;
        }

        if(isset($data["count"]["isdesc"])&&($data["count"]["isdesc"]))
        {
            $data["nocount"]["isdesc"] = 1;
        }
        else
        {
            $data["nocount"]["isdesc"] = 0;
        }
        $data["nocount"] = serialize($data["nocount"]);
        unset($data["count"]);
        return $this->oDB->insert( 'method', $data );
    }



    /**
     * 增加游戏玩法
     * @author rojer
     */
    function addPlay( $aOldMethod )
    {
        if( !isset($aOldMethod) || empty($aOldMethod) )
        {
            return 0;
        }
        if(!is_numeric($aOldMethod["lotteryid"]))
        { //彩种类型错误
            return -1;
        }
        $aMethod["lotteryid"] = intval($aOldMethod["lotteryid"]);
        if( $aMethod["lotteryid"] <=0 )
        { //彩种类型错误
            return -1;
        }
        if( empty($aOldMethod["methodname"]) )
        { //彩种名称为空
            return -2;
        }
        $aMethod["methodname"] = daddslashes( $aOldMethod["methodname"] );
        $aMethod["code"] = daddslashes( $aOldMethod["code"] );
        $aMethod["jscode"] = daddslashes( $aOldMethod["jscode"] );
        $aMethod["addslastype"] = intval( $aOldMethod["addslastype"] );
        if(isset($aOldMethod["modes"]) && !is_array($aOldMethod["modes"]))
        {
            sysMessage('模式错误', 1);
        }
        $aMethod["modes"] = implode(',', $aOldMethod["modes"]);

        /*
        if( empty($aOldMethod["functionname"]) )
        { //彩种的中奖函数名称为空
        return -3;
        }
        */
        $aMethod["initlockfunc"] = daddslashes($aOldMethod["initlockfunc"]);
        /*
        if(empty($aOldMethod["initlockfunc"]))
        {
        return -7;
        }
        */
        $aMethod["pid"]          = $aOldMethod["pid"];
        $aMethod["functionname"] = daddslashes($aOldMethod["functionname"]);
        $aMethod["functionrule"] = daddslashes( $aOldMethod["functionrule"] );
        //todo:高频没有动态调整
        //$aMethod["isprizedynamic"] = isset($aOldMethod["isprizedynamic"]) ? 1: 0;
        $aMethod["islock"]   = isset($aOldMethod["islock"]) ? intval($aOldMethod["islock"]) : 0;
        $aMethod["maxlost"]  = isset($aOldMethod["maxlost"]) ? intval($aOldMethod["maxlost"]) : 0; // 最大封锁值
        $aMethod["lockname"] = daddslashes($aOldMethod["lockname"]);
        if( empty($aOldMethod["level"]) || !is_numeric($aOldMethod["level"]) )
        { // 奖级个数错误
            return -5;
        }
        $aMethod["level"] = intval( $aOldMethod["level"] );
        if($aMethod["level"]<=0)
        {
            return -5;
        }

        $aMethod["count"] =array();
        for($i = 1;$i<=$aMethod["level"];$i++)
        {
            if(!is_numeric($aOldMethod["count"][$i]["count"]))
            {
                return -6;
            }
            $aMethod["count"][$i]["count"] = intval($aOldMethod["count"][$i]["count"]);
            $aMethod["count"][$i]["name"] = $aOldMethod["count"][$i]["name"];
            $aMethod["count"]["type"] = isset($aOldMethod["count"]["type"])&&is_numeric($aOldMethod["count"]["type"])?$aOldMethod["count"]["type"]:0;
            $aMethod["count"][$i]["use"] = isset($aOldMethod["count"][$i]["use"])&&is_numeric($aOldMethod["count"][$i]["use"]) ? 1:0;
        }

        if(isset($aOldMethod["count"]["type"])&&($aOldMethod["count"]["type"]==1))
        {
            $aMethod["count"]["type"] = 1;
        }
        else
        {
            $aMethod["count"]["type"] = 0;
        }

        if(isset($aOldMethod["count"]["isdesc"])&&($aOldMethod["count"]["isdesc"]))
        {
            $aMethod["count"]["isdesc"] = 1;
        }
        else
        {
            $aMethod["count"]["isdesc"] = 0;
        }
        $aMethod["nocount"] = serialize($aMethod["count"]);
        unset($aMethod["count"]);
        $aMethod["description"] = daddslashes( $aOldMethod["description"] );

        if(!is_numeric($aOldMethod['totalmoney']))
        {
            return -7;
        }
        $aMethod['totalmoney'] = number_format($aOldMethod['totalmoney'],2,'.','');
        return $this->oDB->insert( 'method', $aMethod );
    }



    /**
     * 设置玩法销售状态，如果是玩法组，则批量设置其下的所有玩法
     * @author Rojer 100201
     * @param  integer $iMethodid
     * @param  integer $iStatus
     * @return bool
     */
    function setMethodStatus( $iMethodid, $iStatus )
    {
        $iMethodid = is_numeric( $iMethodid ) ? intval( $iMethodid ): 0;
        if( $iMethodid==0 )
        {
            return FALSE;
        }
        if( !in_array($iStatus, array( 0, 1 )) )
        {
            return FALSE;
        }

        $this->oDB->query("UPDATE `method` SET `isclose`='".$iStatus."' WHERE `methodid`='".$iMethodid."'");
        $iAffectedRows = $this->oDB->ar();
        foreach (self::getItems($iMethodid) as $v)
        {
            $this->oDB->query("UPDATE `method` SET `isclose`='".$iStatus."' WHERE `methodid`='".$v['methodid']."'");
            $iAffectedRows += $this->oDB->ar();
        }

        return $iAffectedRows;
    }



    /**
     * 获取玩法列表
     *
     * @author james   090808
     * @access public
     * @param  string   $sFields
     * @param  string   $sCondition
     * @param  string   $sOrderBy
     * @param  int      $iPageRecord
     * @param  int      $iCurrentPage
     * @return array
     */
    function & methodOneGetList( $sFields='', $sCondition='', $sOrderBy='', $iPageRecord=0, $iCurrentPage=0 )
    {
        $sFields    = empty($sFields) ? "*" : daddslashes($sFields);
        if( $iPageRecord == 0 )
        {//不分页显示
            $sCondition = empty($sCondition) ? "" : " WHERE ".$sCondition;
            $sSql       = "SELECT ".$sFields." FROM `method` ".$sCondition." ".$sOrderBy;
            return $this->oDB->getAll( $sSql );
        }
        else
        {
            $sCondition = empty($sCondition) ? "1" : $sCondition;
            return $this->oDB->getPageResult( 'method', $sFields, $sCondition, $iPageRecord, $iCurrentPage,
            $sOrderBy );
        }
    }



    /**
     * 玩法更新
     * @param array $aOldMethod
     * @param string $sCondition
     * @author Rojer
     */
    function methodUpdate( $aOldMethod, $sCondition ="1=0" )
    {
        if( !isset($aOldMethod) || empty($aOldMethod) )
        {
            return -1;
        }
        if(isset($aOldMethod["lotteryid"]))
        {
            if(!is_numeric($aOldMethod["lotteryid"]))
            { //彩种类型错误
                return -2;
            }
            $aMethod["lotteryid"] = intval($aOldMethod["lotteryid"]);
            if( $aMethod["lotteryid"] <=0 )
            { //彩种类型错误
                return -2;
            }
        }

        if(isset($aOldMethod["modes"]) && !is_array($aOldMethod["modes"]))
        {
            sysMessage('模式错误', 1);
        }
        if( isset($aOldMethod["modes"]) && is_array($aOldMethod["modes"]))
        {
            $aMethod["modes"] = implode(',', $aOldMethod["modes"]);
        }

        if( isset($aOldMethod["methodname"]) )
        {
            if( empty($aOldMethod["methodname"]) )
            { //彩种名称为空
                return -3;
            }
            $aMethod["methodname"] = daddslashes( $aOldMethod["methodname"] );
        }
        if(isset($aOldMethod["code"]))
        {
            $aMethod["code"] = daddslashes( $aOldMethod["code"] );
        }
        if(isset($aOldMethod["jscode"]))
        {
            $aMethod["jscode"] = daddslashes( $aOldMethod["jscode"] );
        }
        if(isset($aOldMethod["addslastype"]))
        {
            $aMethod["addslastype"] = intval( $aOldMethod["addslastype"] );
        }
        if(isset($aOldMethod["pid"]))
        {
            $aMethod["pid"] = intval( $aOldMethod["pid"] );
        }

        if(isset($aOldMethod["functionname"]))
        {
            $aMethod["functionname"] = daddslashes( $aOldMethod["functionname"] );
        }
        if(isset($aOldMethod["functionrule"]))
        {
            $aMethod["functionrule"] = daddslashes( $aOldMethod["functionrule"] );
        }
        if(isset($aOldMethod["initlockfunc"]))
        {
            $aMethod["initlockfunc"] = daddslashes( $aOldMethod["initlockfunc"] );
        }

        if(isset($aOldMethod["lockname"]))
        {
            $aMethod["lockname"] = daddslashes( $aOldMethod["lockname"] );
        }
        if(isset($aOldMethod["maxlost"]))
        {
            $aMethod["maxlost"] = intval( $aOldMethod["maxlost"] );
        }
        if(isset($aOldMethod["isclose"]))
        {
            $aMethod["isclose"] = intval( $aOldMethod["isclose"] );
        }
        if(isset($aOldMethod["islock"]))
        {
            $aMethod["islock"] = intval( $aOldMethod["islock"] );
        }
        if(isset($aOldMethod["maxcodecount"]))
        {
            $aMethod["maxcodecount"] = intval( $aOldMethod["maxcodecount"] );
        }
        if(isset($aOldMethod["level"]))
        {
            if( empty($aOldMethod["level"]) || !is_numeric($aOldMethod["level"]) )
            { // 奖级个数错误
                return -6;
            }
            $aMethod["level"] = intval( $aOldMethod["level"] );
            if($aMethod["level"]<=0)
            {
                return -6;
            }
            $aMethod["count"] =array();
            for($i = 1;$i<=$aMethod["level"];$i++)
            {
                if(!is_numeric($aOldMethod["count"][$i]["count"]))
                { // 转直注数错误
                    return -7;
                }
                $aMethod["count"][$i]["count"] = $aOldMethod["count"][$i]["count"];
                $aMethod["count"][$i]["name"] = $aOldMethod["count"][$i]["name"];
                if(isset($aOldMethod["count"][$i]["use"]))
                {
                    $aMethod["count"][$i]["use"] = 1;
                }
                else
                {
                    $aMethod["count"][$i]["use"] = 0;
                }
            }
            $aMethod["count"]["type"] = isset($aOldMethod["count"]["type"])&&is_numeric($aOldMethod["count"]["type"])?$aOldMethod["count"]["type"]:0;
            $aMethod["count"]["isdesc"] = isset($aOldMethod["count"]["isdesc"])&&is_numeric($aOldMethod["count"]["isdesc"])?1:0;
            $aMethod["nocount"] = serialize($aMethod["count"]);
            unset($aMethod["count"]);
        }

        if(isset($aOldMethod["description"]))
        {
            $aMethod["description"] = daddslashes( $aOldMethod["description"] );
        }

        if(isset($aOldMethod["areatype"]))
        {
            $aMethod["areatype"] = base64_encode( serialize(stripslashes_deep($aOldMethod["areatype"])) );
        }

        if(isset($aOldMethod['totalmoney']))
        {
            $aMethod['totalmoney'] = number_format($aOldMethod['totalmoney'],2,'.','');
        }
        return $this->oDB->update( 'method', $aMethod, $sCondition );
    }


    /**
     * 获取按玩法群分组的游戏玩法列表[只支持指定游戏的分组]
     * @param  String $sFields
     * @param  String $sCondition
     * @return array
     * @author mark
     */
    function methodGetListByCrowd( $sFields='', $sCondition='')
    {
        if( empty($sFields) )
        { // 默认字段
            $sFields = "*";
        }
        if( empty($sCondition) )
        {
            $sCondition = "1";
        }
        $sTableName ="`method` AS M LEFT JOIN `lottery` AS L ON (M.`lotteryid`=L.`lotteryid`) ";
        $sTableName .=" LEFT JOIN `method_crowd` AS MC ON (M.`crowdid` =  MC.`crowdid`)";
        $aMethod = $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition);
        $aMethodData = array();
        foreach ($aMethod as $aTempMethod)
        {
            if(!isset($aMethodData[$aTempMethod['crowdid']]['crowdname']))
            {
                $aMethodData[$aTempMethod['crowdid']]['crowdname'] =
                $aTempMethod['crowdname'] != '' ? $aTempMethod['crowdname'] : "默认玩法群";
            }
            if(!isset($aMethodData[$aTempMethod['crowdid']]['cnname']))
            {
                $aMethodData[$aTempMethod['crowdid']]['cnname'] = $aTempMethod['cnname'];
            }
            if(!isset($aMethodData[$aTempMethod['crowdid']]['count']))
            {
                $aMethodData[$aTempMethod['crowdid']]['count'] = 1;
            }
            else
            {
                $aMethodData[$aTempMethod['crowdid']]['count']++;
            }
            $aTempMethod["nocount"] = @unserialize($aTempMethod["nocount"]);
            if( isset($aTempMethod["nocount"]["type"]) )
            {
                $aTempMethod["type"] = $aTempMethod["nocount"]["type"];
                unset( $aTempMethod["nocount"]["type"] );
            }
            $aTempMethod["isdesc"] = $aTempMethod["nocount"]["isdesc"];
            unset($aTempMethod["nocount"]["isdesc"]);
            $aMethodData[$aTempMethod['crowdid']]['method'][] = $aTempMethod;
        }
        ksort($aMethodData);
        return $aMethodData;
    }
    
    
    /**
     * 获取按玩法群分组的游戏玩法列表
     * @param  String $sFields
     * @param  String $sCondition
     * @return array
     * @author mark
     */
    function methodGetAllListByCrowd( $sFields='', $sCondition='')
    {
        if( empty($sFields) )
        { // 默认字段
            $sFields = "L.`lotteryid`,M.`crowdid`,M.`pid`,M.`methodid`,M.`methodname`,MC.`crowdname`,L.`cnname`";
        }
        if( empty($sCondition) )
        {
            $sCondition = "1";
        }
        $aReSult = array();//返回结果
        $sTableName  = "`method` AS M LEFT JOIN `lottery` AS L ON (M.`lotteryid`=L.`lotteryid`)";
        $sTableName .= " LEFT JOIN `method_crowd` AS MC ON (M.`crowdid` = MC.`crowdid`)";
        $aData = $this->oDB->getAll("SELECT ".$sFields." FROM ".$sTableName." WHERE ".$sCondition);
        if(!empty($aData))
        {
            foreach ($aData as $aMethod)
            {
                $aReSult[$aMethod['lotteryid']]['cnname'] =  $aMethod['cnname'];
                $aReSult[$aMethod['lotteryid']]['crowd'][$aMethod['crowdid']]['crowdname'] =  $aMethod['crowdname'];
                $aReSult[$aMethod['lotteryid']]['crowd'][$aMethod['crowdid']]['crowdid'] =  $aMethod['crowdid'];
                if($aMethod['pid'] == 0)
                {
                    $aReSult[$aMethod['lotteryid']]['crowd'][$aMethod['crowdid']]['group'][$aMethod['methodid']]['groupname'] =  $aMethod['methodname'];
                    $aReSult[$aMethod['lotteryid']]['crowd'][$aMethod['crowdid']]['group'][$aMethod['methodid']]['groupid'] =  $aMethod['methodid'];
                }
                else
                {
                    $aReSult[$aMethod['lotteryid']]['crowd'][$aMethod['crowdid']]['group'][$aMethod['pid']]['method'][] =  $aMethod;
                }
                ksort($aReSult[$aMethod['lotteryid']]['crowd']);
            }
        }
        return $aReSult;
    }
}
?>