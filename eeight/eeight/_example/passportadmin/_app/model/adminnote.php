<?php
/**
 * 文件 : /_app/model/adminnote.php
 * 功能 : 模型 - 管理员记事本
 * 
 * @author	    Tom
 * @version    1.0.0
 * @package    passportadmin
 * @since      090516
 */
class model_adminnote extends basemodel 
{   
    /**
     * 获取管理员记事本内容
     * @author Tom
     * @param int $iAdminid
     * @return -1 | string
     */
    function getAdminNote( $iAdminid ) 
    {
        $iAdminid = intval($iAdminid);
    	$aTmp = $this->oDB->getOne( "SELECT `notes` FROM `adminnote` WHERE `adminid`='$iAdminid' LIMIT 1 " );
    	if( 0 == $this->oDB->ar() )
    	{
    	    return -1;
    	}
    	else
    	{
    	    return htmlspecialchars( stripslashes_deep($aTmp['notes']) );
    	}
    }



    /**
     * 设置管理员记事本内容
     * @author Tom
     * @param int $iAdminid
     * @param string $sNotes
     * @return bool
     */
    function setAdminNote( $iAdminid, $sNotes )
    {
        $iAdminid = intval($iAdminid);
        $sNotes = daddslashes( $sNotes );
        $this->oDB->query( "REPLACE `adminnote`(`adminid`,`notes`) VALUES ( '$iAdminid', '$sNotes' )" );
        if( $this->oDB->ar() > 0 )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}
?>