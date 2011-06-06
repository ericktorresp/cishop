<?php
/**
 * 数据模型: 统计各个用户最喜欢玩法的模型
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 */

class model_userfavorite extends basemodel
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
     * 根据方案表统计用户最喜欢的玩法数据
     *
     */
    public function getUserFavorite()
    {
        $sSql = " SELECT `userid` ,`lotteryid`,`methodid` FROM `projects` ";
        $sSql .= " WHERE `writetime` BETWEEN '".date("Y-m-d 00:00:00",time())."' AND '".date("Y-m-d 23:59:59",time())."'";
        $sSql .= " GROUP BY `userid` ,`lotteryid`,`methodid` ";
        $aResult = $this->oDB->getAll($sSql);
        return $aResult;
    }
    
    
    
    /**
     * 获取用户原有的最喜欢的玩法数据
     *
     */
    public function getUserOriginalFavorite( $iUserId = 0 )
    {
        $sSql = " SELECT * FROM `user_favorite` WHERE `userid` = '$iUserId'";
        $aResult = $this->oDB->getOne($sSql);
        return $aResult;
    }
    
    
    
    /**
     * 添加用户最喜欢的玩法数据
     *
     */
    public function insertUserFavorite()
    {
        $aResult = array();
        $aUserData = $this->getUserFavorite();
        if(empty($aUserData))
        {
            return FALSE;
        }
        foreach ($aUserData as $aData)
        {
            if(isset($aResult[$aData['userid']][$aData['lotteryid']]))
            {
                $aResult[$aData['userid']][$aData['lotteryid']] .= ','.$aData['methodid'];
            }
            else 
            {
                $aResult[$aData['userid']][$aData['lotteryid']] = $aData['methodid'];
            }
        }
        $iUser_Favorite = intval(getConfigValue('user_favorite',9));
        foreach ($aResult as $iUserId => $aUser)
        {
            $sNewMethodStr = '';
            $aUserOriginalFavorite = $this->getUserOriginalFavorite($iUserId);
            if(isset($aUserOriginalFavorite['methodstr']))
            {
                $aAllOriginalData = unserialize($aUserOriginalFavorite['methodstr']);
            }
            else 
            {
                $aAllOriginalData = array();
            }
            $aNewMethodStr = array();
            foreach ($aUser as $iLotteryId => $sUserMethodId)
            {
                if(!empty($aAllOriginalData))
                {
                    $aOriginalData = explode("," , $aAllOriginalData[$iLotteryId]);
                }
                else 
                {
                    $aOriginalData = array();
                }
                $aNewData = explode(",", $sUserMethodId);
                if(count($aNewData) > $iUser_Favorite)
                {
                    $aNewData = array_splice($aNewData,0,$iUser_Favorite);
                    $aNewMethodStr[$iLotteryId] = implode(",",$aNewData);
                }
                else 
                {
                    $aAllData = array_merge($aNewData,$aOriginalData);
                    $aAllData = array_unique($aNewData);
                    if(count($aAllData) > $iUser_Favorite)
                    {
                        $aAllData = array_splice($aAllData,0,$iUser_Favorite);
                    }
                    $aNewMethodStr[$iLotteryId] = implode(",",$aAllData);
                }
            }
            if(empty($aUserOriginalFavorite))
            {
                $this->oDB->insert('user_favorite',array('methodstr'=>serialize($aNewMethodStr),'userid'=>$iUserId,'updatetime'=>date("Y-m-d H:i:s")));
            }
            else 
            {
                $this->oDB->update('user_favorite',array('methodstr'=>serialize($aNewMethodStr),'updatetime'=>date("Y-m-d H:i:s")),"`userid`='".$iUserId."'");
            }
        }
        return TRUE;
    }
}