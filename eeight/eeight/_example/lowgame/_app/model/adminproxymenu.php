<?php/** * 总代管理员组权限模型 * * 功能： *		-- get		读取组的权限字段 * 		-- insert		增加权限 *  * @author	Floyd * @version 1.0.0 * @package	highgame * @since 	2010/05/24 */class model_adminproxymenu extends basemodel {	/**	 * 构造函数	 * 	 * @access	public	 * @return	void	 */	function __construct( $aDBO=array() )	{		parent::__construct( $aDBO );	}	/**	 * 增加总代管理员组权限	 * 	 * @access 	public	 * @param	int		$iGroupId	//要继承的组ID	 * @param	array		$aValue	//字段值	 * @return 	mixed		//失败返回FALSE，成功返回TRUE	 */	public function insert( $aValue = array() )	{		if(!is_array($aValue))		{			return FALSE;		}		if(!isset($aValue['groupid']) || !isset($aValue['menustrs']) || !isset($aValue['ownerid']))		{			return FALSE;		}		$this->oDB->insert('admin_proxy_menu', $aValue);		if( $this->oDB->errno() > 0 )		{			return FALSE;		}		return TRUE;	}	public function get($iGroupId)	{		if( empty($iGroupId) || !is_numeric($iGroupId) )		{			return FALSE;		}		$sSql = " SELECT `menustrs` FROM `admin_proxy_menu` WHERE `groupid`='". intval($iGroupId) ."'";		return $this->oDB->getOne( $sSql );	}}?>