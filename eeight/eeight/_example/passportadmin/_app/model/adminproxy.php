<?php
/**
 * 文件 : /_app/model/adminproxy.php
 * 功能 : 数据模型 - 销售管理员
 * 
 * @author     saul
 * @version    1.0.0
 * @package    passportadmin
 * @since      2009-05-04
 */

class model_adminproxy extends basemodel
{
	/**
	 * 增加 管理员 <=> 总代 关系表
	 * @param int  $iAdminId
	 * @param int  $iProxyId
	 * @return BOOL
	 * @author SAUL 090517
	 */
	function add( $iAdminId, $iProxyId )
	{
	    $iAdminId = intval($iAdminId);
	    $iProxyId = intval($iProxyId);
		if( $this->getAdminByProxy($iProxyId) ==0 )
		{
			$this->oDB->query("INSERT INTO `adminproxy` (`adminid`,`topproxyid`) VALUES ('"
			            . $iAdminId . "','" . $iProxyId . "')" );
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}


	
	/**
	 * 通过总代获取销售管理员ID
	 * @param  int $iProxyId
	 * @return int
	 * @author SAUL 090517
	 */
	function getAdminByProxy($iProxyId)
	{
	    $iProxyId = intval($iProxyId);
		$this->oDB->query("SELECT `adminid` FROM `adminproxy` WHERE `topproxyid`='$iProxyId' ");
		if( $this->oDB->ar()>0 )
		{
			$aAdmin = $this->oDB->fetchArray();
			return $aAdmin["adminid"];
		}
		else
		{
			return 0;
		}
	}



	/**
	 * 判断给定的 $iSaleAdminId 是否是销售管理员
	 * @author Tom 090521 4:57
	 * @param int $iSaleAdminId
	 * @return bool  TRUE | FALSE
	 */
	function isSaleAdmin( $iSaleAdminId = 0 )
	{
	    $iSaleAdminId = intval($iSaleAdminId);
	    if( $iSaleAdminId == 0 )
	    {
	        return FALSE;
	    }
	    $aRes = $this->oDB->getOne("SELECT b.`issales` FROM `adminuser` a LEFT JOIN `admingroup` b ".
	                        " ON a.`groupid`=b.`groupid` WHERE a.`adminid`='$iSaleAdminId' LIMIT 1 ");
	    return isset($aRes['issales']) && $aRes['issales']==1 ? TRUE : FALSE;
	}
	

	/**
	 * 获取销售管理员的总代结果集
	 * @param int    $iSaleAdminId  销售管理员ID
	 * @param string $sReturn       返回类型 array | string
	 * @return 
	 *         array   array( 0=>'1' )   key => 总代ID
	 *         string  1,2,3,4,5         总代ID 字符串
	 */
	function getSaleAdminUsers( $iSaleAdminId = 0, $sReturn = 'string' )
	{
	    $iSaleAdminId = intval( $iSaleAdminId );
		if( $iSaleAdminId == 0 )
		{
		    return -1;
		}
		$aRes = $this->oDB->getAll("SELECT `topproxyid` FROM `adminproxy` ".
	                        " WHERE `adminid`='$iSaleAdminId' ");
		if( $sReturn == 'array' )
		{
		    return $aRes;
		}
		$sReturn = '';
		if( !empty($aRes) )
		{
		    foreach( $aRes as $v )
		    {
		        $sReturn .= $v['topproxyid'].',';
		    }
		    if( substr($sReturn,-1,1)==',' )
		    {
		        $sReturn = substr( $sReturn, 0, -1 );
		    }
		    if( $sReturn == '' )
		    {
		        return -1;
		    }
		    return $sReturn;
		}
		else
		{
		    return -1;
		}
	}	
}
?>