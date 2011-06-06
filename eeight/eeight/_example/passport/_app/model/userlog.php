<?php
/**
 * 用户日志数据模型
 *
 * 功能：
 *      记录用户的操作日志
 *            CRUD
 * -----------------------------------------------
 *      --insert            写入用户日志
 *      --delete            删除日志
 * 
 *      --bakLog             数据备份(分页机制)
 *      --clearLog           清除日志
 * 
 * @author  james
 * @version 1.1.0
 * @package passport
 */

class model_userlog extends basemodel 
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
     * 写入用户日志
     * 
     * @access  public
     * @author  james
     * @param   array   $aLogInfo   //用户日志信息数组(与数据库字段对应)
     * @return  mixed   //成功返回insert id，失败返回FALSE
     */
    public function insert( $aLogInfo = array() )
    {
        if( !is_array($aLogInfo) || empty($aLogInfo) )
        {//如果数据为空，则直接返回
            return FALSE;
        }
        /*检测并修复数据完整性*/
        if( !isset($aLogInfo['userid']) || empty($aLogInfo['userid']) )
        {//用户名不能为空
            return FALSE;
        }
        if( !isset($aLogInfo['controller']) || empty($aLogInfo['controller']) )
        {//控制器名
            return FALSE;
        }
        if( !isset($aLogInfo['actioner']) || empty($aLogInfo['actioner']) )
        {//动作名
            return FALSE;
        }
        if( !isset($aLogInfo['title']) || empty($aLogInfo['title']) )
        {//日志标题
            return FALSE;
        }
        if( !isset($aLogInfo['content']) || empty($aLogInfo['content']) )
        {//日志描述
            $aLogInfo['content'] = '';
        }
        if( !isset($aLogInfo['querystring']) || empty($aLogInfo['querystring']) )
        {//URL地址
            $aLogInfo['querystring'] = getUrl();
        }
        if( !isset($aLogInfo['requeststring']) || empty($aLogInfo['requeststring']) )
        {//序列化的_REQUEST[]数组
            $aLogInfo['requeststring'] = serialize( $_REQUEST );
        }
        if( !isset($aLogInfo['clientip']) || empty($aLogInfo['clientip']) )
        {//检测客户端IP
            $aLogInfo['clientip'] = getRealIP();
        }
        if( !isset($aLogInfo['proxyip']) || empty($aLogInfo['proxyip']) )
        {//检测CDNIP
            $aLogInfo['proxyip'] = $_SERVER['REMOTE_ADDR'];
        }
        if( !isset($aLogInfo['times']) || empty($aLogInfo['times']) )
        {//检测时间
            $aLogInfo['times'] = date("Y-m-d H:i:s", time());
        }
        return $this->oDB->insert( 'userlog', $aLogInfo );
    }



    /**
     * 删除日志
     * 
     * @access  public
     * @author  james
     * @param   string  $sWhereSql  //删除条件,默认删除所有，即清空
     * @return  mixed   //成功返回所影响的行数，失败返回FALSE
     */
    public function delete( $sWhereSql = '1' )
    {
        return $this->oDB->delete( 'userlog', $sWhereSql );
    }



    /**
     * 数据备份(分页机制)
     *
     * @param integer $iDay
     * @param string $sFile
     */
    function bakLog( $iDay, $sFile )
    {
        if( !is_numeric($iDay) ) 
        {
            return FALSE;
        }
        $aNumLog = $this->oDB->getOne("SELECT count(*) AS `count_log` FROM `userlog` 
                                       WHERE `times`<'".date("Y-m-d 00:00:00",strtotime("-".$iDay."days"))."'");
        $iNum    = $aNumLog['count_log'];
        $iSize   = 50000;
        $iPages  = ceil( $iNum/$iSize );
        $oGzopen = gzopen( $sFile, 'w9' );
        for( $iPage = 0; $iPage < $iPages; $iPage++ )
        {
            $sFileContent = "";
            $sSql        = "SELECT * FROM `userlog` WHERE `times`<'".
                            date("Y-m-d 00:00:00",strtotime("-".$iDay."days"))."' LIMIT ".($iPage*$iSize).",".$iSize;
            $aLogs       = $this->oDB->getAll( $sSql );
            foreach( $aLogs as $log )
            {
                $keys   = array();
                $values = array();
                foreach( $log as $key => $value )
                {
                    $keys[] = "`".$key."`";
                    if( is_null($value) )
                    {
                        $values[] = 'NULL';
                    }
                    else 
                    {
                        $values[] = "'".$this->oDB->es($value)."'";	
                    }
                }
                $sSql = "INSERT INTO `userlog` (".join(",", $keys).") VALUES (".join(",", $values).");";
                unset($keys,$values);
                $sFileContent .= $sSql."\n";
            }
            gzwrite( $oGzopen, $sFileContent );
        }
        gzclose($oGzopen);
        unset($sFileContent);
        $this->clearLog( $iDay );
        return TRUE;
    }



    /**
     * 清除日志
     *
     * @param int $day
     * @return bool
     * @author Saul
     */
    function clearLog( $iDay )
    {
        if( !is_numeric($iDay) )
        {
            return FALSE;
        }
        return $this->oDB->query("DELETE FROM `userlog` WHERE `times`<'".
                                  date("Y-m-d 00:00:00",strtotime("-".$iDay."days"))."'");
    }
}
?>