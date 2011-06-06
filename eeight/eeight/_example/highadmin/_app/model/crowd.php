<?php
/**
 * 数据模型: 玩法群
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 */

class model_crowd extends basemodel
{
    /**
     * 构造函数
     *
     * @param array $aDBO
     */
    function __construct( $aDBO = array())
    {
        parent::__construct( $aDBO );
    }
    
    
    /**
     * 获取玩法群列表
     * 
     * @param int $iLotteryId 游戏ID
     * 
     * @return array
     *
     */
    public function crowdGetList( $iLotteryId = 1 )
    {
        $aResult = array();
        if(!isset($iLotteryId) || $iLotteryId == 0 )
        {
            return $aResult;//基本参数不正确
        }
        $iLotteryId = intval($iLotteryId);
        $sSql = " SELECT * FROM `lottery` WHERE `lotteryid` = '".$iLotteryId."'";
        $aLotteryReult = $this->oDB->getOne($sSql);
        if($this->oDB->errno > 0 || empty($aLotteryReult))
        {
            return $aResult;//游戏不存在
        }
        $sSql = " SELECT l.`cnname`,c.* FROM `method_crowd` AS c LEFT JOIN `lottery` AS l ON(c.`lotteryid`=l.`lotteryid`)";
        $sSql .= " WHERE l.`lotteryid` = '".$iLotteryId."' ORDER BY c.`crowdid`";
        $aResult = $this->oDB->getAll($sSql);
        return $aResult;
    }
    
    
    /**
     * 获取指定ID的玩法群
     * 
     * @param int $iCrowdId 玩法群ID
     * 
     * @return array
     *
     */
    public function crowdGetItem( $iCrowdId = 1 )
    {
        $aResult = array();
        if(!isset($iCrowdId) || $iCrowdId == 0 )
        {
            return $aResult;//基本参数不正确
        }
        $iCrowdId = intval($iCrowdId);
        $sSql = " SELECT l.`cnname`,c.* FROM `method_crowd` AS c LEFT JOIN `lottery` AS l ON(c.`lotteryid`=l.`lotteryid`)";
        $sSql .= " WHERE c.`crowdid` = '".$iCrowdId."'";
        $aResult = $this->oDB->getOne($sSql);
        return $aResult;
    }
    
    
    /**
     * 修改玩法群
     * 
     * @param $sCrowdName string 玩法群名称
     * @param $iCrowdId   int    玩法群ID
     * @param $aGroupMethodId array 相关玩法组ID
     * 
     * @return mixed
     *
     */
    public function editCrowdAndMethod($sCrowdName = '', $iCrowdId = 0, $aGroupMethodId = array())
    {
        if($sCrowdName == '' || $iCrowdId == 0 || !is_array($aGroupMethodId) )
        {
            return -1;//参数不正确
        }
        $iCrowdId = intval($iCrowdId);
        $sSql = " SELECT * FROM `method_crowd` WHERE `crowdid` = '".$iCrowdId."'";
        $aGetCrowd = $this->oDB->getOne($sSql);
        if( $this->oDB->errno > 0 || empty($aGetCrowd) )
        {
            return -2;//玩法群不存在
        }
        foreach ($aGroupMethodId as $iKey => $iMethodId )
        {
            if(!is_numeric($iMethodId))
            {
                unset($aGroupMethodId[$iKey]);//过滤玩法组
            }
        }
        if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            return -30;
        }
        $this->oDB->update('method_crowd', array('crowdname'=>daddslashes($sCrowdName)), "`crowdid`='".$iCrowdId."'");//更新玩法群名称
        if($this->oDB->errno > 0)
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return -31;
            }
            return -4;
        }
        //获取指定玩法组下的所有玩法
        $aAllMethodId = $aGroupMethodId;
        $oMethod = new model_method();
        foreach ($aGroupMethodId as $iKey => $iMethodId )
        {
            $aGetMethodId = $oMethod->getItems($aGetCrowd['lotteryid'],$iMethodId);
            foreach ($aGetMethodId as $aMethodId)
            {
                $aAllMethodId[] = $aMethodId['methodid'];
            }
        }
        $sMethodId = implode(",",$aAllMethodId);
        if($sMethodId != '')
        {
            $this->oDB->update('method', array('crowdid'=>$iCrowdId), "`methodid` IN (".$sMethodId.")");//更新玩法的群ID
            $this->oDB->update('method', array('crowdid'=>0), "`methodid` NOT IN (".$sMethodId.") AND `crowdid`='".$iCrowdId."'");//更新玩法的群ID
        }
        else 
        {
            $this->oDB->update('method', array('crowdid'=>0), "`crowdid`='".$iCrowdId."'");//更新玩法的群ID
        }
        if($this->oDB->errno > 0)
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return -31;
            }
            return -5;
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return -32;
        }
        return TRUE;
    }
    
    
    /**
     * 添加玩法群
     * 
     * @param $sCrowdName string 玩法群名称
     * @param $iCrowdId   int    彩种ID
     * @param $aGroupMethodId array 相关玩法组ID
     * 
     * @return mixed
     *
     */
    public function insertCrowdAndMethod($sCrowdName = '', $iLotteryId = 0, $aGroupMethodId = array())
    {
        if($sCrowdName == '' || $iLotteryId == 0 || !is_array($aGroupMethodId) )
        {
            return -1;//参数不正确
        }
        $iLotteryId = intval($iLotteryId);
        $sCrowdName = daddslashes($sCrowdName);
        $sSql = " SELECT * FROM `method_crowd` WHERE `crowdname` = '".$sCrowdName."' AND `lotteryid`='".$iLotteryId."'";
        $aGetCrowd = $this->oDB->getOne($sSql);
        if( !empty($aGetCrowd) )
        {
            return -2;//玩法群已经存在
        }
        $sSql = " SELECT * FROM `lottery` WHERE `lotteryid` = '".$iLotteryId."'";
        $aGetLottery = $this->oDB->getOne($sSql);
        if( $this->oDB->errno > 0 || empty($aGetLottery) )
        {
            return -3;//彩种不存在
        }
        foreach ($aGroupMethodId as $iKey => $iMethodId )
        {
            if(!is_numeric($iMethodId))
            {
                unset($aGroupMethodId[$iKey]);//过滤玩法组
            }
        }
        if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            return -40;
        }
        $iCrowdId = $this->oDB->insert('method_crowd', array('crowdname'=>daddslashes($sCrowdName), 'lotteryid'=>$iLotteryId));//添加玩法群
        if($this->oDB->errno > 0 || intval($iCrowdId) == 0 )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return -41;
            }
            return -5;
        }
        //获取指定玩法组下的所有玩法
        $aAllMethodId = $aGroupMethodId;
        $oMethod = new model_method();
        foreach ($aGroupMethodId as $iKey => $iMethodId )
        {
            $aGetMethodId = $oMethod->getItems($iLotteryId,$iMethodId);
            foreach ($aGetMethodId as $aMethodId)
            {
                $aAllMethodId[] = $aMethodId['methodid'];
            }
        }
        $sMethodId = implode(",", $aAllMethodId);
        if($sMethodId != '')
        {
            $this->oDB->update('method', array('crowdid'=>$iCrowdId), "`methodid` IN (".$sMethodId.")");//更新玩法的群ID
        }
        if($this->oDB->errno > 0)
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return -41;
            }
            return -6;
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return -42;
        }
        return TRUE;
    }
    
    
    /**
     * 删除玩法群
     *
     * @param int $iCrowdId 玩法群ID
     * 
     * @return mixed
     */
    public function deleteCrowdAndMethod( $iCrowdId = 0 )
    {
        if($iCrowdId == 0)
        {
            return -1;//参数不正确
        }
        $iCrowdId = intval($iCrowdId);
        $sSql = " SELECT * FROM `method_crowd` WHERE `crowdid` = '".$iCrowdId."'";
        $aGetCrowd = $this->oDB->getOne($sSql);
        if( empty($aGetCrowd) )
        {
            return -2;//玩法群不存在
        }
        if( FALSE == $this->oDB->doTransaction() )
        {//事务处理失败
            return -30;
        }
        $iResult = $this->oDB->delete('method_crowd', "`crowdid`='".$iCrowdId."'");//删除玩法群
        if( intval($iResult) == 0 || $this->oDB->errno > 0 )
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return -31;
            }
            return -4;
        }
        $this->oDB->update('method', array('crowdid'=>0), "`crowdid` = '".$iCrowdId."'");//更新玩法的群ID
        if($this->oDB->errno > 0)
        {
            if( FALSE == $this->oDB->doRollback() )
            {//回滚事务
                return -31;
            }
            return -5;
        }
        if( FALSE == $this->oDB->doCommit() )
        {//事务提交失败
            return -32;
        }
        return TRUE;
    }
}