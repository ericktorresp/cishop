<?php
/**
 * 总代管理员与一级代理销售关系数据模型
 *
 * 功能：
 * 		CRUD总代管理员和一级代理的关系
 *      CRUD
 * 		--insert			insert增加对应关系
 * 		--deleteById		根据ID删除对应关系
 * 		--delete			根据传入的自定义条件删除
 * 		--getByUserId		根据总代管理员ID，读取所有数据列表
 * 		--getAdminId		根据一代ID，获取总代的ID
 * 		--getList			自定义条件获取列表
 * 
 * 		--isExists		           判断一个总代管理员和某个一代是否存在了对应关系
 *      --getAdminSaleList  获取总代的销售管理员列表
 *      --getAdminProxyList 获取总代的所有一代及对应销售管理员信息
 * 
 * @author	james
 * @version 1.0.0
 * @package	passport
 * @since 	2009/04/29
 */

class model_useradminproxy extends basemodel 
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
	 * 增加总代管理员和销售之间的对应关系
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param	int		$iAdminId	//总代管理员ID
	 * @param 	mixed	$mPorxyId	//一代理ID,多个ID，用数组如array(1,2,3,4)
	 * @return 	mixed		//失败返回FALSE，成功返回TRUE
	 */
	public function insert( $iAdminId, $mPorxyId )
	{
		if( !is_numeric($iAdminId) || empty($mPorxyId) )
		{
			return FALSE;
		}
		$iAdminId = intval( $iAdminId );
		$sValues = array();
		if( is_array($mPorxyId) )
		{//多个代理ID
			foreach( $mPorxyId as $v )
			{
				$sValues[] ="('".$iAdminId."','".intval($v)."')";
			}
			$sValues = implode(',', $sValues);
		}
		else 
		{
			$sValues ="('".$iAdminId."','".intval($mPorxyId)."')";
		}
		$sSql ="REPLACE INTO `useradminproxy`(`adminid`,`topproxyid`) VALUES ".$sValues;
		$this->oDB->query($sSql);
		if( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		return TRUE;
	}



	/**
	 * 根据ID删除对应关系
	 * 	如果未传入ID，或者ID为0则返回FALSE
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param	int		$iId		//传入的用户ID，或者自增ID
	 * @param 	int		$sField		//ID的字段名,包括：entry,adminid,topproxyid,默认为entry
	 * @return	mixed	//成功返回受影响的行数，失败返回FALSE
	 */
	public function deleteById( $iId=0, $sField='entry' )
	{
		$sSql = '';
		$iId  = intval($iId);
		if( $iId == 0 )
		{ //如果未传入则返回
			return FALSE;	
		}
		switch( strtolower($sField) )
		{
			case 'adminid':
					$sSql = " `adminid`='". $iId ."'";
					break;
			case 'topproxyid':
					$sSql = " `topproxyid`='". $iId ."'";
					break;
			default:
					$sSql = " `entry`='". $iId ."'";
					break;
		}
		return $this->oDB->delete( 'useradminproxy', $sSql );
	}



	/**
	 * 根据传入的自定义条件删除[谨慎使用，避免删除重要数据]
	 * 	自定义条件即为定义SQL语句的WHERE 后面的条件
	 * 	例子：删除一些总代管理员的所有对应关系 $sWhereSql = ' `admin` in (1,2,3,4,5)'
	 * 	如果要清空所有数据$sWhereSql 为空就可以了 
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	string	$sWhereSql	//SQL条件语句
	 * @return 	mixed	//成功返回受影响的行数，失败返回FALSE
	 */
	public function delete( $sWhereSql='1' )
	{
		return $this->oDB->delete( 'useradminproxy', $sWhereSql );
	}



	/**
	 * 根据总代管理员ID，读取所有数据列表
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//总代管理员ID
	 * @return 	mixed		//成功返回二维数组列表，失败返回FALSE
	 */
	public function getByUserId( $iUserId=0 )
	{
		if( empty($iUserId) )
		{
			return FALSE;
		}
		$sSql = " SELECT `entry`,`topproxyid` FROM `useradminproxy` WHERE `adminid`='". intval($iUserId) ."'";
		return $this->oDB->getAll( $sSql );
	}



	/**
	 * 根据一代ID，获取总代的ID[一个一代在这个表里只有一条记录]
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iProxyId	//一代ID
	 * @return 	int		//失败返回0[FALSE]，成功返回总代ID
	 */
	public function getAdminId( $iProxyId=0 )
	{
		if( empty($iProxyId) )
		{
			return FALSE;
		}
		$sSql = "  SELECT `entry`,`adminid` FROM `useradminproxy` WHERE `topproxyid`='". intval($iProxyId) ."' LIMIT 1";
		return $this->oDB->getOne( $sSql );
	}



	/**
	 * 自定义条件获取列表
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	array	$aFileds	//要查寻的字段
	 * @param 	string	$sWhereSql	//自定义查询条件,默认获取所有的
	 * @return  mixed	//失败返回FALSE，成功返回二维数组列表
	 */
	public function getList( $aFileds=array(), $sWhereSql='1' )
	{
		if( is_array($aFileds) && !empty($aFileds) )
		{
			foreach( $aFileds as &$v )
			{
				$v = '`'.$v.'`';
			}
			$sSql = "SELECT ".implode(',',$aFileds) ." FROM `useradminproxy` WHERE ". $sWhereSql;
		}
		else 
		{
			$sSql = "SELECT * FROM `useradminproxy` WHERE ". $sWhereSql;
		}
		return $this->oDB->getAll( $sSql );
	}



	/**
	 * 判断一个总代管理员和某个一代是否存在了对应关系
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iAdminId 	//管理员ID
	 * @param 	int		$iProxyId	//代理ID
	 * @return 	boolean	//存在返回TRUE，不存在返回FALSE
	 */
	public function isExists( $iAdminId, $iProxyId )
	{
		if( empty($iAdminId) || empty($iProxyId) )
		{
			return FALSE;
		}
		$sSql = "SELECT `entry` FROM `useradminproxy` WHERE `adminid`='".intval($iAdminId)."' AND
				 `topproxyid`='". $iProxyId ."'";
		$this->oDB->query( $sSql );
		unset($sSql);
		if( $this->oDB->numRows() <1 )
		{
			return FALSE;
		}
		return TRUE;
	}



	/**
	 * 获取总代的销售管理员列表
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//总代ID
	 * @param 	string	$sField		//要获取的字段信息
	 * @param 	string 	$sAndWhere	//附加搜索条件
	 * @return 	//成功返回销售管理员列表，失败返回FALSE
	 */
	public function getAdminSaleList( $iUserId, $sField='', $sAndWhere='' )
	{
		if( empty($iUserId) || !is_numeric($iUserId) )
		{
			return FALSE;
		}
		$iUserId = intval( $iUserId );
		$sFields = " ut.`userid`,ut.`username` ";
		if( !empty($sField) )
		{
			$sFields .= $sField;
		}
		$sSql = "SELECT ".$sFields." FROM `usertree` AS ut 
		         LEFT JOIN `userchannel` AS ugs ON ut.`userid`=ugs.`userid` 
				 LEFT JOIN `proxygroup` AS pg ON ugs.`groupid`=pg.`groupid` 
				 WHERE ut.`parentid`='".$iUserId."' AND ut.`usertype`='2' AND pg.`issales`='1' AND ut.`isdeleted`='0' 
				 AND pg.`isdisabled`='0' ".$sAndWhere;
		$this->oDB->query($sSql);
		if( $this->oDB->numRows() < 1 )
		{
			return FALSE;
		}
		$aResult   = array();
		$aTemp_row = array();
		while( FALSE != ($aTemp_row=$this->oDB->fetchArray()) )
		{
			$aResult[$aTemp_row['userid']] = $aTemp_row;
		}
		return $aResult;
	}



	/**
	 * 获取总代的所有一代及对应销售管理员信息
	 * 
	 * @access 	public
	 * @author 	james	09/05/17
	 * @param 	int		$iUserId	//总代ID
	 * @param 	string	$sField		//要获取的字段信息
	 * @param 	string 	$sAndWhere	//附加搜索条件
	 * @return 	//成功返回一代列表，失败返回FALSE
	 */
	public function getAdminProxyList( $iUserId, $sField='', $sAndWhere='' )
	{
		if( empty($iUserId) || !is_numeric($iUserId) )
		{
			return FALSE;
		}
		$sFields = " ut.`userid`,ut.`username`,uap.`adminid` ";
		if( !empty($sField) )
		{
			$sFields .= $sField;
		}
		$sSql = "SELECT ".$sFields." FROM `usertree` AS ut 
				 LEFT JOIN `useradminproxy` AS uap ON ut.`userid`=uap.`topproxyid` 
				 WHERE ut.`parentid`='".$iUserId."' AND ut.`usertype`<'2' AND ut.`isdeleted`='0' ".$sAndWhere; 
		return $this->oDB->getAll($sSql);
	}
}
?>