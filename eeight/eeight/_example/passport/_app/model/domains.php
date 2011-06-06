<?php
/**
 * 域名列表模型
 * 
 * 功能:
 * -- domainAdd          增加一个域名(状态:直接启用)
 * -- domainCheck        检测域名是否存在
 * -- domain             通过ID实例化的domain
 * -- domainUserList     根据域名ID组获取相关的域名用户列表
 * -- domainSetStatus    更新域名使用状态
 * -- domainDel          删除一个域名
 * -- domainList         获取指定状态的域名列表
 * -- domainRename       域名改名
 *  
 * @author     saul
 * @version    1.1.0
 * @package    passport
 */
class model_domains extends basemodel 
{

    /**
     * 增加一个域名(状态:直接启用)
     *
     * @param string $sDomainName 域名名称
     * @return int
     *          -1:参数不全
     *          0：域名已经存在
     *          other>0:域名新加的ID
     * @author  saul
     */
    public function domainAdd( $sDomainName ) 
    {
        if( empty($sDomainName) )
        {
            return -1;
        }
        else
        {
            $sDomainName = daddslashes($sDomainName);
            if( $this->domainCheck($sDomainName) )
            {
                return 0;
            }
            else
            {
                $this->oDB->query("INSERT INTO `domains` (`domain`,`status`) VALUES('".$sDomainName."','1')");
                return $this->oDB->insertId();
            }
        }
    }



    /**
     * 检测域名是否存在
     *
     * @param  string $sDomainName  域名
     * @param  int    $iDomainId    域名ID
     * @return BOOL   如果域名不是域名ID对应的实例对象则为真 
     * @author saul
     */
    public function domainCheck( $sDomainName , $iDomainId = 0 )
    {
        $sDomainName = daddslashes( $sDomainName );
        $iDomainId   = intval( $iDomainId );
        $this->oDB->query("SELECT `id` FROM `domains` WHERE `domain`='" .$sDomainName. "'" 
                            ." AND `id`<>'".$iDomainId."'");
        return ($this->oDB->ar()>0);
    }



    /**
     * 通过ID实例化的domain
     *
     * @param  int $iDomainId   域名ID
     * @return mixed array:域名数组
     *                 -1 :失败
     *  @author	saul
     */
    public function domain( $iDomainId )
    {
        if( empty($iDomainId) || !is_numeric($iDomainId) )
        {
            return -1;
        }
        $iDomainId = intval( $iDomainId );
        $this->oDB->query("SELECT `id`,`domain`,`status` FROM `domains` WHERE `id`='".$iDomainId."'");
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
     * 根据域名ID组获取相关的域名用户列表
     *
     * @param array $aDomian
     * @return array
     */
    function domainUserList( $aDomian )
    {
        if( is_array($aDomian) )
        {
            foreach( $aDomian as $iKey => $iDomian )
            {
                if( !is_numeric($iDomian) )
                {
                    unset($aDomian[$iKey]);
                }
            }
            if( count($aDomian)>0 )
            {
                return $this->oDB->getAll("SELECT `id`,`domain`,`status` FROM `domains` 
                                            WHERE `id` IN (".join(',', $aDomian).")");
            }
            else 
            {
                return array();
            }
        }
        else 
        {
            return array();
        }
    }



    /**
     * 更新域名使用状态
     *
     * @param  int  $idomainId
     * @param  int  $iStatus
     * @return BOOL
     * @author saul
     */
    public function domainSetStatus( $iDomainId , $iStatus )
    {
        $iDomainId = intval( $iDomainId );
        $iStatus   = intval( $iStatus );
        $mDomain   = $this->domain($iDomainId);//实例化域名
        if( $mDomain == -1 )
        {//不存在
            return FALSE;
        }
        else 
        {
            if( $mDomain["status"] != $iStatus )
            {//和当前的域名状态不相等
                $this->oDB->query("UPDATE `domains` SET `status`='".$iStatus."' WHERE `id`='".$iDomainId."'");
            }
            return TRUE;
        }
    }



    /**
     * 删除一个域名
     *
     * @param array $aDomainId
     * @return BOOL
     * @author saul
     */
    public function domainDel( $aDomainId )
    {
        if( empty($aDomainId) || !is_array($aDomainId) )
        {
            return FALSE;
        }
        foreach( $aDomainId as $iKey => $iDomainId )
        {
            if( !is_numeric($iDomainId) )
            {
                unset($aDomainId[$iKey]);
            }
        }
        if( count($aDomainId) > 0 ) 
        {
            $this->oDB->query("DELETE FROM `userdomain` WHERE `domainid` IN (".join(',', $aDomainId).")");
            $this->oDB->query("DELETE FROM `domains` WHERE `id` IN (".join(',', $aDomainId).")");			
            return TRUE;
        }
        return FALSE;
    }



    /**
     * 获取指定状态的域名列表
     *
     * @param int $iStatus  指定状态,为空时候获取全部域名
     * @return Array:域名数组
     * @author  saul
     */
    public function domainList( $iStatus = -1 )
    {
        $iStatus = intval( $iStatus );
        if( empty($iStatus) ) 
        { // 默认值
            $iStatus = -1;
        }
        if( !in_array($iStatus, array(0,1,2)) )
        { // 保证iStatus
            $iStatus = -1;
        }
        if( $iStatus == -1 )
        {
            $sSql= "SELECT `id`,`domain`,`bygroupid`,`status` FROM `domains`";
        }
        else
        { 
            $sSql = "SELECT `id`,`domain`,`bygroupid`,`status` FROM `domains` WHERE `status`='".$iStatus."'";
        }
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 域名改名
     *
     * @param  int    $iDomainId 域名ID
     * @param  string $sDomainName  域名
     * @return BOOL
     * @author  saul
     */
    public function domainRename( $iDomainId , $sDomainName )
    {
        $iDomainId    = intval( $iDomainId );
        $mDomain      = $this->domain( $iDomainId ); // 实例化域名
        if( $mDomain == -1 )
        { // 不存在，返回
            return  FALSE;
        }
        else
        {
            $sDomainName = daddslashes($sDomainName); // 安全过滤
            if( $mDomain['domain'] != $sDomainName )  // 域名名称不等于当前域名名称
            {
                if( $this->domainCheck( $sDomainName, $iDomainId) )
                { // 检测域名是否是其他ID的域名
                    return FALSE;
                }
                else
                {
                    $this->oDB->query("UPDATE `domains` SET `domain` = '".$sDomainName."' WHERE `id`='".$iDomainId."' LIMIT 1");//更新
                    return TRUE;
                }
            }
            return TRUE;
        }
    }


    /**
     * 根据条件获取一个域名
     *
     * @author james
     * @access public
     * @param  string   $sField
     * @param  string   $sCondition
     * @return array
     */
    public function domainGetOne( $sField = "", $sCondition = "" )
    {
        $sField       = empty($sField) ? "*" : daddslashes($sField);
        $sCondition   = empty($sCondition) ? "" : " WHERE ".$sCondition;
        $sSql   = " SELECT ".$sField." FROM `domains` ".$sCondition;
        return $this->oDB->getOne( $sSql );
    }
}
?>