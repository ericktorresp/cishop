<?php
/**
 * 银行列表模型
 * 
 * 功能：
 * -- bankAdd           增加一个银行
 * -- bankNameExists    检测银行名称是否存在
 * -- bank              通过一个ID实例化一个银行
 * -- bankOpen          开放指定ID的银行
 * -- bankClose         关闭指定ID的银行
 * -- bankDel           删除指定ID的银行
 * -- bankList          获取银行列表
 * -- bankUpdate        更新银行信息
 * 
 * @author    saul
 * @version   1.1.0
 * @package   passport
 */

class model_bankinfo extends basemodel 
{
    /**
     * 增加一个银行
     * @author SAUL
     * @param string $sBankName 银行名称
     * @return int  -1: 参数不全
     *              0:银行名称已经存在
     *              其他:新加银行的ID
     */
    public function bankAdd( $sBankName )
    {
        if( empty($sBankName) )
        {
            return -1; // 参数不全
        }
        $sBankName = daddslashes($sBankName); // 安全过滤
        if( $this->bankNameExists($sBankName) )
        { // 检测银行是否存在
            return 0;
        }
        else
        { // 执行insert
            $this->oDB->query( "INSERT INTO `bankinfo` (`bankname`,`status`) VALUES('" .$sBankName. "','0');" );
            return $this->oDB->insertId();
        }
    }



    /**
     * 检测银行名称是否存在
     * @author  SAUL
     * @param string $sBankName 银行名称
     * @param int $iBankId  银行Id
     * @return BOOL
     */
    public function bankNameExists( $sBankName, $iBankId = 0)
    {
        $iBankId = intval( $iBankId );
        $this->oDB->query("SELECT `bankid` FROM `bankinfo` WHERE 
                            `bankname`='" .daddslashes($sBankName). "' AND `bankid` != '" .$iBankId. "'");
        return ( $this->oDB->ar() > 0 );
    }



    /**
     * 通过一个ID实例化一个银行
     * @author SAUL
     * @param int $iBankId  银行ID
     * @return [fixd]   -1:不存在
     *                  数组:银行
     */
    public function bank( $iBankId )
    {
        $iBankId = intval( $iBankId );
        $this->oDB->query( "SELECT `bankid`,`bankname`,`status` FROM `bankinfo` WHERE `bankid`='" .$iBankId. "'" );
        if( $this->oDB->ar() == 0 )
        {
            return -1;
        }
        else
        {
            return $this->oDB->fetchArray();
        }
    }



    /**
     * 开放指定ID的银行
     * @author  SAUL
     * @param int $iBankId  银行编号
     * @return BOOL
     */
    public function bankOpen( $iBankId )
    {
        $iBankId = intval( $iBankId );
        $mBank   = $this->bank( $iBankId );
        if( $mBank == -1 )
        {
            return FALSE;
        }
        else
        {
            if( $mBank["stauts"] == 1 )
            {
                $this->oDB->query("UPDATE `bankinfo` SET `status`='0' WHERE `bankid`='".$iBankId."'");
            }
            return TRUE;
        }
    }



    /**
     * 关闭指定ID的银行
     * @author SAUL
     * @param int $iBankId  银行编号
     * @return BOOL
     */
    public function bankClose( $iBankId )
    {
        $iBankId = intval( $iBankId );
        $mBank   = $this->bank( $iBankId );
        if( $mBank == -1 )
        {
            return FALSE;
        }
        else
        {
            if( $mBank["stauts"] == 0 )
            {
                $this->oDB->query("UPDATE `bankinfo` SET `status`='1' WHERE `bankid`='" .$iBankId. "'");
            }
            return TRUE;
        }
    }



    /**
     * 删除指定ID的银行
     *
     * @param int $iBankId  银行编号
     * @return BOOL
     * @author SAUL 090517
     */
    public function bankDel( $iBankId )
    {
        $iBankId = intval( $iBankId );
        $mBank   = $this->bank( $iBankId );
        if( $mBank == -1 )
        {
            return FALSE;
        }
        else
        {
            $this->oDB->query( "DELETE FROM `bankinfo` WHERE `bankid`='" .$iBankId. "'" );
            return TRUE;
        }
    }



    /**
     * 获取银行列表
     * @author SAUL
     * @param  int    $iStatus 银行的开放状态
     * @return Array  银行列表
     */
    public function bankList( $iStatus = -1 )
    {
        if( !is_numeric($iStatus) )
        {
            $iStatus = -1;
        }
        if( !in_array($iStatus, array(0,1)) )
        { // 控制$status为0或者1
            $iStatus = -1;
        }
        if( $iStatus == -1 )
        {
            $sSql = "SELECT `bankid`,`bankname`,`status` FROM `bankinfo`";
        }
        else
        {
            $sSql = "SELECT `bankid`,`bankname`,`status` FROM `bankinfo` WHERE `status`='".$iStatus."'";
        }
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 更新银行信息
     * @author SAUL
     * @param int $iBankId  银行ID
     * @param string $sBankName 银行名称
     */
    public function bankUpdate( $iBankId, $sBankName )
    {
        if( empty($iBankId) || empty($sBankName) )
        { // 参数不全
            return FALSE;
        }
        else 
        {
            $iBankId = intval( $iBankId );
            $mBank   = $this->bank( $iBankId ); // 银行通过ID实例化
            if( $mBank == -1 )
            { // 不存在
                return FALSE;
            }
            else
            {
                $sBankName =daddslashes( $sBankName ); // 安全过滤
                if( $mBank["bankname"] != $sBankName )
                { // 不等于当前名称
                    if( $this->bankNameExists( $sBankName, $iBankId) )
                    { // 银行名称在其他ID上面存在，保证不重复
                        return FALSE;
                    }
                    else 
                    {
                        $this->oDB->query("UPDATE `bankinfo` SET `bankname`='" .$sBankName. "'
                                             WHERE `bankid`='" .$iBankId. "'");
                            return TRUE;
                    }
                }
                return TRUE;
            }
        }
    }
}
?>