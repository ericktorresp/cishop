<?php
    /**
    * 用户菜单数据模型
    *
    * 功能：
    *       用户前台所有功能菜单的管理，主要用于权限判断，和菜单显示
    *          CRUD
    * -----------------------------------------------
    *       --insert            新增加一个菜单
    *       --delete            删除菜单
    *       --update            修改菜单信息
    *       --getOne            根据条件读取一条数据
    *       --getList           根据条件读取菜单列表
    * 
    *       --switchDisabled    菜单开启关闭开关
    *      --getUserMenus      根据用户ID，读取菜单列表
    * 
    * @author   james
    * @version  1.0.0
    * @package  passport
    */

class model_usermenu extends basemodel 
{
    /**
     * 构造函数
     * 
     * @access  public
     * @return  void
     */
    function __construct( $aDBO = array() )
    {
        parent::__construct( $aDBO );
    }



    /**
     * 新增加一个菜单
     *
     * @access  public
     * @author  james
     * @param   array   $aMenuInfo  //菜单信息,键和字段对应 
     * @return  mixed   //成功返回insert id，失败返回FALSE
     */
    public function insert( $aMenuInfo = array() )
    {
        if( !is_array($aMenuInfo) || empty($aMenuInfo) )
        { // 空数据直接返回FALSE
            return FALSE;
        }
        //检测数据并修复数据完整性
        if( !isset($aMenuInfo['controller']) || !isset($aMenuInfo['actioner']) )
        {
            return FALSE;
        }
        if( isset($aMenuInfo['parentid']) && (bool)$aMenuInfo['parentid'] )
        {
            $aMenuInfo['parentid'] = intval( $aMenuInfo['parentid'] );
        }
        if( isset($aMenuInfo['ismenu']) && (bool)$aMenuInfo['ismenu'] )
        {
            $aMenuInfo['ismenu'] = intval( $aMenuInfo['ismenu'] );
        }
        if( isset($aMenuInfo['islink']) && (bool)$aMenuInfo['islink'] )
        {
            $aMenuInfo['islink'] = intval( $aMenuInfo['islink'] );
        }
        if( isset($aMenuInfo['sort']) && (bool)$aMenuInfo['sort'] )
        {
            $aMenuInfo['sort'] = intval( $aMenuInfo['sort'] );
        }
        if( isset($aMenuInfo['isdisabled']) && (bool)$aMenuInfo['isdisabled'] )
        {
            $aMenuInfo['isdisabled'] = intval( $aMenuInfo['isdisabled'] );
        }
        return $this->oDB->insert( 'usermenu', $aMenuInfo );
    }



    /**
     * 删除菜单
     * 
     * @access  public  
     * @author  james
     * @param   string  $sWhereSql  //删除条件
     * @return  mixed   //成功返回所影响的行数，失败返回FALSE
     */
    public function delete( $sWhereSql='1' )
    {
        return $this->oDB->delete( 'usermenu', $sWhereSql );
    }



    /**
     * 修改菜单信息
     * 
     * @access  public
     * @author  james
     * @param   array   $aMenuInfo  //要修改的菜单信息
     * @param   string  $sWhereSql  //修改的条件，默认修改全部菜单
     * @return  mixed   //成功返回所影响的行数，失败返回FALSE
     */
    public function update( $aMenuInfo=array(), $sWhereSql = '1' )
    {
        if( !is_array($aMenuInfo) || empty($aMenuInfo) )
        { // 空数据直接返回FALSE
            return FALSE;
        }
        if( isset($aMenuInfo['parentid']) && (bool)$aMenuInfo['parentid'] )
        {
            $aMenuInfo['parentid'] = intval( $aMenuInfo['parentid'] );
        }
        if( isset($aMenuInfo['ismenu']) && (bool)$aMenuInfo['ismenu'] )
        {
            $aMenuInfo['ismenu'] = intval( $aMenuInfo['ismenu'] );
        }
        if( isset($aMenuInfo['islink']) && (bool)$aMenuInfo['islink'] )
        {
            $aMenuInfo['islink'] = intval( $aMenuInfo['islink'] );
        }
        if( isset($aMenuInfo['sort']) && (bool)$aMenuInfo['sort'] )
        {
            $aMenuInfo['sort'] = intval( $aMenuInfo['sort'] );
        }
        if( isset($aMenuInfo['isdisabled']) && (bool)$aMenuInfo['isdisabled'] )
        {
            $aMenuInfo['isdisabled'] = intval( $aMenuInfo['isdisabled'] );
        }
        return $this->oDB->update( 'usermenu', $aMenuInfo, $sWhereSql );
    }



    /**
     * 根据条件读取一条数据
     * 
     * @access  public
     * @author  james
     * @param   array   $aMenuInfo  //要读取的菜单信息
     * @param   string  $sWhereSql  //搜索的条件，默认为无条件
     * @return  mixed   //成功返回一条菜单信息，失败返回FALSE
     */
    public function getOne( $aMenuInfo = array(), $sWhereSql = '' )
    {
        if( is_array($aMenuInfo) && !empty($aMenuInfo) )
        {//自定义要取的字段信息
            foreach( $aMenuInfo as &$v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode( ',', $aMenuInfo );
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
        $sSql = "SELECT ". $sFields ." FROM `usermenu` ". $sWhereSql;
        return $this->oDB->getOne( $sSql );
    }



    /**
     * 根据条件读取菜单列表
     * 
     * @access  public
     * @author  james
     * @param   array   $aMenuInfo  //要读取的菜单信息
     * @param   string  $sWhereSql  //搜索的条件，默认为无条件
     * @return  mixed   //成功返回菜单列表，失败返回FALSE
     */
    public function getList( $aMenuInfo = array(), $sWhereSql = '' )
    {
        if( is_array($aMenuInfo) && !empty($aMenuInfo) )
        {//自定义要取的字段信息
            foreach( $aMenuInfo as &$v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode( ',', $aMenuInfo );
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
        $sSql = "SELECT ". $sFields ." FROM `usermenu` ". $sWhereSql ." ORDER BY sort ASC,menuid ASC";
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 菜单开启关闭开关
     * 
     * @access  public
     * @author  james
     * @param   string  $sMenuId    //要进行操作的菜单ID，或者ID集合,多个ID以,分隔，如：'1,2,3'
     * @param   boolean $bDisabled  //TRUE：关闭，FALSE：开启
     * @return  boolean //成功返回所影响的行数，失败返回FASLE
     */
    public function switchDisabled( $sMenuId, $bDisabled = FALSE )
    {
        if( empty($sMenuId) )
        {
            return FALSE;
        }
        if( (bool)$bDisabled )
        {
            $aData['isdisabled'] = 1;
        }
        else 
        {
            $aData['isdisabled'] = 0;
        }
        $aMenuId = explode(',', $sMenuId);
        if( count($aMenuId) == 1 )
        {//单条记录修改
            $sWhereSql = " menuid='".$aMenuId[0]."'";
        }
        else
        {
            $sWhereSql = " menuid in(". $sMenuId .")";
        }
        return $this->oDB->update( 'usermenu', $aData, $sWhereSql );
    }



    /**
     * 根据用户ID，读取菜单列表
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //用户ID
     * @param   int     $iUserType  //用户类型,0用户，1代理，2总代管理员
     * @param   int     $iChannelId //频道ID
     * @param   boolean $bIsMenuOnly    //是否只读菜单，TURE只读是左侧菜单的数据，FALSE，读所有权限数据
     * @return  mixed   //失败返回FALSE，成功返回菜单列表
     */
    public function & getUserMenus( $iUserId, $iUserType = 0, $iChannelId = 0, $bIsMenuOnly = TRUE )
    {
        $aResult = array();
        if( empty($iUserId) || !is_numeric($iUserId) || $iUserId < 0 )
        {
            return $aResult;
        }
        $iUserId = intval($iUserId);
        if( empty($iUserType) || !is_numeric($iUserType) )
        {
            $iUserType = 0;
        }
        if( empty($iChannelId) || !is_numeric($iChannelId) )
        {
            $iChannelId = 0;
        }
        //读取用户菜单权限
        if( $iUserType == 2 )//总代管理员读取总代管理员分组
        {
            /* temp_louis $sSql = "SELECT a.`extendmenustr`, b.`menustrs`,b.`viewrights` 
                FROM `userchannel` AS a LEFT JOIN `proxygroup` AS b ON a.`groupid`=b.`groupid` 
                WHERE 
                a.`userid`='".$iUserId."' AND a.`channelid`='".$iChannelId."' AND a.`isdisabled`='0' AND
                b.`isdisabled`='0'";*/
            $sSql = "SELECT a.`extendmenustr`, c.`menustrs`,b.`viewrights` 
                FROM `userchannel` AS a LEFT JOIN `proxygroup` AS b ON a.`groupid`=b.`groupid` LEFT JOIN `admin_proxy_menu` as c
                ON b.`groupid` = c.`groupid`
                WHERE 
                a.`userid`='".$iUserId."' AND a.`channelid`='".$iChannelId."' AND a.`isdisabled`='0' AND
                b.`isdisabled`='0'";
        }
        else
        {
            $sSql = "SELECT a.`extendmenustr`, b.`menustrs` 
                    FROM `userchannel` AS a LEFT JOIN `usergroup` AS b ON a.`groupid`=b.`groupid` 
                    WHERE 
                    a.`userid`='".$iUserId."' AND a.`channelid`='".$iChannelId."' AND a.`isdisabled`='0' AND
                    b.`isdisabled`='0'";
        }
        $aData = $this->oDB->getDataCached( $sSql );
        if( !isset($aData[0]) || empty($aData[0]) )
        {//没找到数据，返回失败
            return $aResult;
        }
        //不为总代管理员则可全部查看
        $aResult['viewrights'] = isset($aData[0]['viewrights']) ? intval($aData[0]['viewrights']) : 7;
        $aResult['viewrights'] =  $iUserType == 0 ? 3 : $aResult['viewrights'];
        $sMenus                = $aData[0]['menustrs'];
        if( !empty($aData[0]['extendmenustr']) )
        {//存在特殊权限
            $sMenus .= (empty($sMenus) ? '' : ',').$aData[0]['extendmenustr'];
        }
        if( $bIsMenuOnly == TRUE )
        {
            $ismenus = " AND `ismenu`='1' ";
        }
        else 
        {
            $ismenus = '';
        }
        if( empty($sMenus) )
        {
            return $aResult;
        }
        //读取菜单数据
        $sSql = "SELECT `menuid`,`parentid`,`parentstr`,`title`,`description`,`controller`,`actioner`,`islink` 
                FROM `usermenu` 
                WHERE  `isdisabled`='0' ".$ismenus." AND `menuid` in (".$sMenus.") ORDER BY `sort` ASC,`menuid` ASC";
        $aData = $this->oDB->getDataCached( $sSql );
        if( empty($aData) )
        {//没找到数据，返回失败
            return $aResult;
        }
        $aResult = array_merge( $aResult, $aData );
        return $aResult;
    }



    /**
     * 保存菜单排序
     *
     * @param integer   $iParentMenuId
     * @param array     $aSort
     */
    function userMenuSort( $iMenuId, $aMenu )
    {
    	$iMenuId = intval($iMenuId);
        $aMenuChild = $this->getList( array(), "`parentid`='".$iMenuId."'" );
        $this->oDB->doTransaction();
        foreach( $aMenuChild as $menu )
        {
            if( $aMenu[$menu["menuid"]] != $menu["sort"] )
            {
                if(empty($aMenu[$menu["menuid"]])) $aMenu[$menu["menuid"]] = 0;
                $this->oDB->query("UPDATE `usermenu` SET `sort`='"
                    . $aMenu[$menu["menuid"]]."' WHERE `menuid`='".$menu["menuid"]."'");
                if($this->oDB->errno()>0)
                {
                    $this->oDB->doRollback();
                    return FALSE;
                }
            }
        }
        $this->oDB->doCommit();
        return TRUE;
    }
}
?>