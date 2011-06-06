<?php
/**
 * 文件 : /_app/model/iplimit.php
 * 功能 : 模型 - ip限制管理
 * 
 * 功能：
 *    - getIPlist           获取信任IP列表
 *    - update              修改信任IP
 *    - insert              增加信任IP
 *    - delete              删除信任IP
 *    - checkIP             检测是否是信任IP
 * 
 * @author	  mark
 * @version   1.0
 * @package   passportadmin
 */
class model_iplimit extends basemodel
{
    /**
     * 获取信任IP列表
     * @author mark
     * @param  int $pn 分页参数
     * @param  int $p 分页参数
     * @param  string $sContion 查询条件
     * @return array
     */
    public function getIPlist( $pn = 0 , $p = 0,  $sContion = "1" )
    {
        $pn = isset($pn) ? intval($pn) : 0;
        $sContion = isset($sContion) ? $sContion : "1";
        if( $sContion == '1' )
        {
            if( $pn == 0 )
            {
                $sSql = " SELECT * FROM `iplimit`";
                return $this->oDB->getAll($sSql);
            }
            else
            {
                return $this->oDB->getPageResult( "iplimit", "*", "1", $pn, $p );
            }
        }
        else 
        {
            $sSql = " SELECT * FROM `iplimit` WHERE " . $sContion;
            return $this->oDB->getOne($sSql);
        }
    }
    
    
    /**
     * 修改信任IP
     * @author  mark
     * @param  array  $aData        更新内容
     * @param  string $sConditon    更新条件
     * @return  int
     */
    public function update( $aData = array(), $sConditon = "1" )
    {
        if( empty($aData) || !is_array($aData) )
        {
            return -1;//数据不完整
        }
        if( $aData['limitip'] == '' )
        {
            return -1;//数据不完整
        }
        $sConditon = isset($sConditon) ? $sConditon : '1';
        if( $sConditon == '1' )
        {
            return -1;//数据不完整
        }
        $aData['datetime'] = date('Y-m-d H:i:s');
        
        return $this->oDB->update( 'iplimit', $aData, $sConditon );
    }
    
    
    /**
     * 增加信任IP
     * @author mark
     * @param array $aData 插入的数据
     * @return int
     */
    public function insert( $aData = array() )
    {
        if( empty($aData) || !is_array($aData) )
        {
            return -1;//数据不完整
        }
        if( $aData['limitip'] == '' )
        {
            return -1;//数据不完整
        }
        $sSql = " SELECT * FROM `iplimit` WHERE `limitip` = '" . $aData['limitip'] ."'";
        $aResult = $this->oDB->getAll( $sSql );
        if( !empty($aResult) )
        {
            return -2;//数据重复
        }
        $aData['datetime'] = date('Y-m-d H:i:s');
        
        return $this->oDB->insert( 'iplimit', $aData );
    }
    
    
    /**
     * 删除信任IP
     * @author mark
     * @param  string  $sCondtion  删除条件
     * @return int
     */
    public function delete( $sCondtion = "1" )
    {
        $sCondtion = isset($sCondtion) ? $sCondtion : "1";
        if( $sCondtion == '1' )
        {
            return -1;//数据不完整
        }
        return $this->oDB->delete( 'iplimit', $sCondtion );
    }
    
    
    /**
     * 检测是否是信任IP
     * @author mark
     * @param  string  $sIpString  需要检测的IP
     * @return boolean
     */
    public function checkIP( $sIpString = '' )
    {
        $aIPList = $this->getIPlist();
        if( empty($aIPList) )
        {
            return FALSE;
        }
        foreach ( $aIPList as $aIP )
        {
            $aTempIP = explode('.',$aIP['limitip']);
            if( count($aTempIP) == 4 )//完整IP
            {
                if( preg_match('/^'.$aIP['limitip'].'$/', $sIpString) == 1 )
                {
                    return TRUE;
                }
            }
            else//IP段
            {
                if( preg_match('/^'.$aIP['limitip'].'\./', $sIpString) == 1 )
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
}