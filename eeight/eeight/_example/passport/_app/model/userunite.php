<?php
/**
 * 用户合帐设置数据模型
 *
 * 功能：
 *      总代对多个一级代理进行合帐设置
 * 
 *      CRUD
 * -----------------------------------------------
 *      insert              --增加一个合帐设置
 *      delete              --删除合帐设置
 *      deleteById          --根据总代和合帐名删除合帐设置
 *      update              --修改合帐设置
 *      getOne              --根据条件读取一个合帐信息
 *      getUserUnite        --根据用户ID，获取其合帐内的其他帐号信息
 *      getListById         --根据总代ID读取合帐信息列表
 *      isInUnite           --查询某个代理或者某组代理是否已经被设置到一个合帐中
 *      isExists            --检测一个总代下是否已存在某个合帐设置
 *      getProxyList        --根据总代获取其下还没有被设置到合帐中的一代信息
 *      isUnite             --检查两个代理是否在同一个合帐设置中
 * 
 * @author  james
 * @version 1.0.0
 * @package passport
 */

class model_userunite extends basemodel 
{
    /**
     * 构造函数
     * 
     * @access  public
     * @return  void
     */
    function __construct( $aDBO=array() )
    {
        parent::__construct( $aDBO );
    }



    /**
     * CRUD->增加一个合帐设置
     * @access  public
     * @author  james
     * @param   int       $iUserId    //总代ID
     * @param   int       $iAdminId   //公司管理员ID
     * @param   string    $sAliasname //合帐别名
     * @param   string    $aUnite     //合帐的代理ID集合，ID数组
     * @return  boolean  //成功返回TRUE，失败返回FALSE -1:已存在同名合帐,-2:某些代理已在其他合帐中
     */
    public function insert( $iUserId, $sAliasname, $aUnite, $iAdminId = 0 )
    {
        //数据检测(全部必填)
        if( empty($iUserId) || empty($sAliasname) || empty($aUnite) || !is_numeric($iUserId) || !is_array($aUnite) )
        {
            return FALSE;
        }
        $iUserId    = intval( $iUserId );
        $iAdminId   = intval($iAdminId) > 0 ? intval($iAdminId) : 0;
        if( TRUE == $this->isExists($iUserId, $sAliasname) )
        {
            return -1;
        }
        if( TRUE == $this->isInUnite(implode(',', $aUnite)) )
        {
            return -2;
        }
        $sAliasname   = daddslashes($sAliasname);
        $sSql  = " INSERT INTO `userunite`(`proxyid`,`adminid`,`userid`,`aliasname`) VALUES";
        $aMore = array();
        foreach( $aUnite as $v )
        {
            if( empty($v) )
            {
                continue;
            }
            $aMore[] = " ('".$iUserId."','".$iAdminId."','".intval($v)."','".$sAliasname."') ";
        }
        $this->oDB->query( $sSql.implode(',', $aMore) );
        if( $this->oDB->affectedRows() < 1 )
        {
            return FALSE;
        }
        return TRUE;
    }



    /**
     * CRUD->删除合帐设置
     * 
     * @access  public   
     * @author  james
     * @param   string  $sWhereSql  //删除条件
     * @return  mixed   //成功返回所影响的行数，失败返回FALSE
     */
    public function delete( $sWhereSql = '1' )
    {
        return $this->oDB->delete( 'userunite', $sWhereSql );
    }



    /**
     * CRUD->根据总代和合帐名删除合帐设置
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代ID
     * @param   string  $sAliasname //合帐名,如果为空则删除总代的所有合帐设置
     * @return  boolean  //成功返回TRUE，失败返回FALSE
     */
    public function deleteById( $iUserId, $sAliasname = '' )
    {
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $iUserId = intval( $iUserId );
        $sWhere  = "";
        if( !empty($sAliasname) )
        {
            $sWhere = " AND `aliasname`='".daddslashes($sAliasname)."' ";
        }
        $sSql = "DELETE FROM `userunite` WHERE `proxyid`='".$iUserId."' ".$sWhere;
        $this->oDB->query( $sSql );
        if( $this->oDB->ar() < 1 )
        {
            return FALSE;
        }
        return TRUE;
    }


    
    /**
     * 根据条件读取一个合帐信息
     * 
     * @access  public
     * @author  james
     * @param   array   $aUniteInfo//要读取的合帐
     * @param   string  $sWhereSql  //搜索的条件，默认为无条件
     * @return  mixed   //成功返回一条合帐信息，失败返回FALSE
     */
    public function getOne( $aUniteInfo = array(), $sWhereSql = '' )
    {
        if( is_array($aUniteInfo) && !empty($aUniteInfo) )
        {//自定义要取的字段信息
            foreach( $aUniteInfo as & $v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode( ',', $aUniteInfo );
        }
        else
        {
            $sFields = "*";
        }
        if( !empty($sWhereSql) )
        {
            $sWhereSql = ' WHERE '.$sWhereSql;
        }
        else
        {
            $sWhereSql = '';
        }
        $sSql = "SELECT ". $sFields ." FROM `userunite` ". $sWhereSql;
        return $this->oDB->getOne( $sSql );
    }



    /**
     * 根据用户ID，获取其合帐内的其他帐号信息
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId
     * @return  mixed   //成功返回信息列表，失败返回FALSE
     */
    public function getUserUnite( $iUserId )
    {
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $iUserId = intval( $iUserId );
        $sSql = "SELECT ut.`userid`,ut.`username` FROM `userunite` AS un
                 LEFT JOIN `userunite` AS unn ON  (un.`aliasname`=unn.`aliasname` AND un.`proxyid` = unn.`proxyid`) 
                 LEFT JOIN `usertree` AS ut ON (ut.`userid`=un.`userid` AND ut.`isdeleted`='0') 
                 WHERE un.`userid`<>'" .$iUserId. "' AND unn.`userid`='" .$iUserId. "'";
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 根据总代ID读取合帐信息列表
     * 
     * @access public
     * @author james
     * @param   int   $iUserId //用户id
     * @param   bool  $bIsAdmin  //是否需要返回总代销售管理员id
     * @return  mixed   //成功返回合帐信息列表，失败返回FALSE
     */
    public function getListById( $iUserId, $bIsAdmin = false )
    {
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $iUserId = intval($iUserId);
        $sFields = " un.`userid`,un.`aliasname`,un.`adminid`,ut.`username` ";
        $sSql    = "SELECT ". $sFields ." FROM `userunite` AS un 
                    LEFT JOIN `usertree` AS ut ON (un.`userid`=ut.`userid` AND ut.`isdeleted`='0') 
                    WHERE `proxyid`='".$iUserId."' ORDER BY aliasname";
        $this->oDB->query($sSql);
        $aTempRow = array();
        $aResult  = array();
        while( FALSE != ($aTempRow=$this->oDB->fetchArray()) )
        {
        	if ($bIsAdmin === true){
        		$aResult[$aTempRow['aliasname']]['user'][] = $aTempRow['username']; 
            	$aResult[$aTempRow['aliasname']]['adminid'] = $aTempRow['adminid'];
        	} else {
        		$aResult[$aTempRow['aliasname']]['user'][] = $aTempRow['username']; 
        		$aResult[$aTempRow['aliasname']]['delrights'] = 1;
        	}
        }
        if( empty($aResult) )
        {
            return FALSE;
        }
        if ($bIsAdmin === false){
        	foreach( $aResult as &$v )
	        {
	            if( is_array($v) )
	            {
	                $v['user'] = implode(', ', $v['user']);
	            }
	        }
        }
        return $aResult;
    }



    /**
     * 查询某个代理或者某组代理是否已经被设置到一个合帐中
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID,多个代理用,分隔
     * @return  boolean //存在返回TRUE，不存在返回FALSE
     */
    public function isInUnite( $iUserId )
    {
        if( empty($iUserId) )
        {
            return FALSE;
        }
        $users = explode( ',', $iUserId );
        if( count($users) == 1 )
        {
            $sSql = "SELECT `entry` FROM `userunite` WHERE `userid`='".intval($iUserId)."'";
        }
        else 
        {
            $sSql = "SELECT `entry` FROM `userunite` WHERE `userid` IN (".$iUserId.")";
        }
        $this->oDB->query( $sSql );
        unset( $sSql );
        if( $this->oDB->numRows() > 0 )
        {
            return TRUE;
        }
        return FALSE;
    }



    /**
     * 检测一个总代下是否已存在某个合帐设置
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代ID
     * @param   string  $sAliasName //合帐名
     * @return  boolean //存在返回TRUE，不存在返回FALSE
     */
    public function isExists( $iUserId, $sAliasName )
    {
        if( empty($iUserId) || empty($sAliasName) )
        {
            return FALSE;
        }
        $sAliasName   = daddslashes($sAliasName);
        $sSql = "SELECT `entry` FROM `userunite` 
                WHERE `proxyid`='".intval($iUserId)."' AND `aliasname`='".$sAliasName."'";
        $this->oDB->query( $sSql );
        unset( $sSql );
        $iNums = $this->oDB->ar();
        if( $iNums > 0 )
        {
            return TRUE;
        }
        return FALSE;
    }



    /**
     * 根据总代获取其下还没有被设置到合帐中的一代信息
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代ID
     * @return  mixed   //成功返回用户信息列表，失败返回FALSE
     */
    public function getProxyList( $iUserId )
    {
        if( empty($iUserId) || !is_numeric($iUserId) )
        {
            return FALSE;
        }
        $iUserId = intval( $iUserId );
        //先获取已经被设置了的
        $sSql      = "SELECT `userid` FROM `userunite` WHERE `proxyid`='".$iUserId."' ";
        $this->oDB->query( $sSql );
        $aUsers    = array();
        $aTempRow  = array();
        while( FALSE != ($aTempRow = $this->oDB->fetchArray()) )
        {
            $aUsers[] = $aTempRow['userid'];
        }
        unset($aTempRow);
        if( empty($aUsers) )
        {
            $sSql = " SELECT `userid`,`username` FROM `usertree` 
                     WHERE `parentid`='".$iUserId."' AND `usertype`='1' AND `isdeleted`='0' ORDER BY username";
        }
        else
        {
            $sSql = " SELECT `userid`,`username` FROM `usertree` 
                    WHERE `parentid`='".$iUserId."' AND `usertype`='1' AND `isdeleted`='0' 
                    AND `userid` NOT IN(".implode(',', $aUsers).") ORDER BY username";
        }
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 检查两个代理是否在同一个合帐设置中
     * 
     * @access  public
     * @author  james
     * @param   int $iUserId    //一个代理ID
     * @param   int $iOtherUserId   //另外一个代理ID
     * @return  boolean //在一个合帐中返回TURE，否则返回FALSE
     */
    public function isUnite( $iUserId, $iOtherUserId )
    {
        if( empty($iUserId) || !is_numeric($iUserId) || empty($iOtherUserId) || !is_numeric($iOtherUserId) )
        {
            return FALSE;
        }
        $iUserId      = intval( $iUserId );
        $iOtherUserId = intval( $iOtherUserId );
        $sSql = "SELECT un.`userid` FROM `userunite` AS un LEFT JOIN `userunite` AS unn ON un.`aliasname` = unn.`aliasname` AND unn.`userid`='".$iUserId."' AND un.`proxyid` = unn.`proxyid`";
        $this->oDB->query( $sSql );
        if( $this->oDB->ar() > 0 )
        {
            return TRUE;
        }
        return FALSE;
    }
}
?>