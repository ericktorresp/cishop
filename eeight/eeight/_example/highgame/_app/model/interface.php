<?php
/**
 * API 涉及事务的处理类 (方法封装)
 *
 * - api_activeUserFund()   激活用户频道资金账户
 * 
 * @author     Tom   090915
 * @version    1.2.0
 * @package    highgame
 */

class model_interface extends basemodel 
{
	/**
	 * 构造函数
	 * 
	 * @access	public
	 * @return	void
	 */
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}

	
	/**
	 * 激活用户频道资金账户
	 * 根据POST传递的总代ID, 初始化用户在低频频道中的 userFund 等相关数据
	 * 对应 API : /low/_api/activeUserFund.php
	 * @return BOOL
	 */
	public function api_activeUserFund( $iTopProxyId = 0 )
	{
	    $iTopProxyId = intval($iTopProxyId);
	    
	    // 1, 检查 ID 是否是总代
	    $oUser = new model_user();
	    if( FALSE == $oUser->isTopProxy($iTopProxyId) )
	    { // 非总代直接中断
	        return FALSE;
	    }
	    unset($oUser);

	    // 2, 判断是否已经激活
	    $this->oDB->query( "SELECT 1 FROM `usergroupset` WHERE `userid`='$iTopProxyId' " );
	    $iFlagUGS = $this->oDB->ar();
	    
	    $this->oDB->query( "SELECT 1 FROM `userfund` WHERE `userid`='$iTopProxyId' " );
	    $iFlagUF = $this->oDB->ar();
	    
	    if( $iFlagUGS>0 && $iFlagUF>0 )
	    { // 如果相关数据都存在, 则不需要继续激活, 中断
	        return TRUE;
	    }

	    // 事务处理  ( 插入 userfund, usergroupset )
	    $aInsertArr1 = array(
	        'userid' => $iTopProxyId,
	        'groupid' => 1,
	    );
	    $aInsertArr2 = array(
	        'userid' => $iTopProxyId,
	        'channelid' => SYS_CHANNELID,
	        'lastupdatetime' => date("Y-m-d H:i:s"),
	        'lastactivetime' => date("Y-m-d H:i:s"),
	    );

	    $this->oDB->doTransaction(); // 开始事务
	    if( $iFlagUGS == 0 )
	    {
	        if( FALSE == $this->oDB->insert( 'usergroupset', $aInsertArr1 ) )
	        {
	            $this->oDB->doRollback();
	            return FALSE; // 激活失败
	        }
	    }
	    
	    if( $iFlagUF == 0 )
	    {
	        if( FALSE == $this->oDB->insert( 'userfund', $aInsertArr2 ) )
	        {
	            $this->oDB->doRollback();
	            return FALSE; // 激活失败
	        }
	    }

	    $this->oDB->doCommit(); // 事务提交
	    return TRUE;
	}
	
	
	
	
	
}
?>