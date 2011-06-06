<?php
/**
 * 总代域名关系数据模型
 *
 * 功能：
 *      对总代和域名之间的关系的操作进行封装
 *      CRUD
 *      --insert                增加总代和域名的对应关系
 *      --deleteByUserId        删除总代和某域名的对应关系
 *      --delete                根据条件删除筛选的对应关系
 *      --update                修改用户和域名的对应关系
 *      --getOne                根据自定义条件查询一条记录
 *      --getList               根据自定义条件查询列表
 *      --isExists              检测总代和一个域名是否已存在对应关系
 * 
 * @author  james
 * @version 1.1.0
 * @package passport
 */

class model_userdomain extends basemodel 
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
     * 删除总代和某域名的对应关系
     * 
     * @access  public
     * @param   int     $iUserId    //要删除的总代的ID
     * @param   int     $iDomainId  //域名的ID
     * @return  mixed   //成功返回所影响所影响的行数，失败返回FALSE
     */
    public function deleteByUserId( $iUserId, $iDomainId )
    {
        if( empty($iUserId) || !is_numeric($iUserId) || empty($iDomainId) || !is_numeric($iDomainId) )
        {
            return FALSE;
        }
        return $this->oDB->delete( 'userdomain', " `userid`='". intval($iUserId) ."'
                                     AND `domainid`='". intval($iDomainId) ."'" );
    }



    /**
     * 根据条件删除筛选的对应关系
     * 
     * @access  public
     * @author  james
     * @param   string  $sWhereSql  //删除条件,默认全部删除，清空所有的对应关系
     * @return  mixed   //成功返回所影响所影响的行数，失败返回FALSE
     */
    public function delete( $sWhereSql = '1' )
    {
        return $this->oDB->delete( 'userdomain', $sWhereSql );
    }



    /**
     * 修改总代和域名的对应关系
     * 
     * @access  public
     * @author  james
     * @param   array   $aInfo      //要修改的信息
     * @param   string  $sWhereSql  //要修改的筛选条件[不包含where关键字]，默认为全部修改
     * @return  mixed   //成功返回所影响所影响的行数，失败返回FALSE   
     */
    public function update( $aInfo = array(), $sWhereSql = '1' )
    {
        if( !is_array($aInfo) || empty($aInfo) )
        {
            return FALSE;
        }
        return $this->oDB->update( 'userdomain', $aInfo, $sWhereSql );
    }



    /**
     * 根据自定义条件查询一条记录
     * 
     * @access  public
     * @author  james
     * @param   array   $aInfo      //要取的对应关系信息中的字段数组
     * @param   string  $sWhereSql  //Where 条件[不包括where]
     * @return  mixed   //成功返回一个对应信息，失败返回FALSE
     */
    public function getOne( $aInfo = array(), $sWhereSql = '' )
    {
        if( is_array($aInfo) && !empty($aInfo) )
        {//自定义要取的字段信息
            foreach( $aInfo as &$v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode(',', $aInfo);
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
        $sSql = "SELECT ". $sFields ." FROM `userdomain` ". $sWhereSql;
        return $this->oDB->getOne( $sSql );
    }



    /**
     * 根据自定义条件查询列表
     * 
     * @access  public
     * @author  james
     * @param   array   $aInfo      //要取的对应关系信息中的字段数组
     * @param   string  $sWhereSql  //Where 条件[不包括where]
     * @return  mixed   //成功返回对应信息列表，失败返回FALSE
     */
    public function getList( $aInfo = array(), $sWhereSql = '' )
    {
        if( is_array($aInfo) && !empty($aInfo) )
        {//自定义要取的字段信息
            foreach( $aInfo as &$v )
            {
                $v = '`'.$v.'`';
            }
            $sFields = implode(',', $aInfo);
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
        $sSql = "SELECT ". $sFields ." FROM `userdomain` ". $sWhereSql;
        return $this->oDB->getAll( $sSql );
    }



    /**
     * 检测总代和一个域名是否已存在对应关系
     * 
     * @access  public
     * @author  james
     * @param   int     $iUserId    //总代ID
     * @param   int     $iDomainId  //域名ID
     * @return  //存在返回TRUE，不存在返回FALSE
     */
    public function isExists( $iUserId, $iDomainId )
    {
        if( empty($iUserId) || !is_numeric($iUserId) || empty($iDomainId) || !is_numeric($iDomainId) )
        {
            return FALSE;
        }
        $iUserId   = intval( $iUserId );
        $iDomainId = intval( $iDomainId );
        $sSql      = " SELECT `entry` FROM `userdomain` WHERE `userid`='".$iUserId."' AND `domainid`='".$iDomainId."' ";
        $this->oDB->query( $sSql );
        unset($sSql);
        if( $this->oDB->numRows() > 0 )
        {//存在记录集
            return TRUE;
        }
        return FALSE;
    }
}
?>