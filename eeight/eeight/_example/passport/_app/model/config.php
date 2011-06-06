<?php
/**
 * 系统配置config
 * 
 * 功能
 * -- addConfig       增加一个配置项
 * -- checkConfig     检查配置项的键值是否存在
 * -- getConfigByPid  根据分组获取配置项
 * -- config          获取一个配置项的相关属性
 * -- updateConfig    更新配置项
 * -- configDel       删除一个配置
 * -- setStatus       更新配置项状态
 * -- update          设置配置
 * -- getConfigs      获取配置项
 * -- getConfigFile   获取所有配置项并生成缓存文件
 * -- updateConfigs   批量更新配置项
 * 
 * @author     saul
 * @version    1.1.0
 * @package    passport
 */

class model_config extends basemodel
{
    /**
     * 增加一个配置项
     *
     * @param array $aConfig
     * @return integer -1: 增加失败
     *                 -2: 配置项英文名称重复或者为空
     *                  other: 增加成功
     * @author saul
     */
    function addConfig( $aConfig )
    {
        if( !is_array($aConfig) || empty($aConfig) )
        {
            return -1;
        }
        elseif( !$this->checkConfig($aConfig['configkey'], 0) )
        { // 检查配置项的键值是否存在
            return -2;
        }
        else
        {
            return $this->oDB->insert( "config", $aConfig );
        }
    }



    /**
     * 检查配置项的键值是否存在
     *
     * @param string  $sConfigkey
     * @param int     $iConfigid
     * @return BOOL
     * @author saul
     */
    function checkConfig( $sConfigKey, $iConfigId = 0 )
    {
        if( $sConfigKey == "" )
        {
            return FALSE;
        }
        if( !is_numeric($iConfigId) )
        {
            $iConfigId = 0;
        }
        $sConfigKey   = daddslashes( $sConfigKey );
        $iConfigId    = intval( $iConfigId );
        $this->oDB->query("SELECT * FROM `config` WHERE `configkey`='".$sConfigKey."'"
                ." AND `configid` != '".$iConfigId."' ");
        return ( $this->oDB->ar() == 0 );
    }



    /**
     * 根据分组获取配置项
     *
     * @param  int   $iPid
     * @return Array
     * @author SAUL
     */
    function getConfigByPid( $iPid )
    {
        if( $iPid < 0 )
        {
            return FALSE;
        }
        else
        {
            $iPid = intval( $iPid );
            return $this->oDB->getAll( "SELECT * FROM `config` WHERE `parentid`='".$iPid."'" );
        }
    }



    /**
     * 获取一个配置项的相关属性
     * @author SAUL
     * @param  int   $id
     * @return Array or FALSE
     */
    function config( $iId )
    {
        if( !is_numeric($iId) || ($iId <= 0) )
        {
            return NULL;
        }
        else
        {
            return $this->oDB->getOne( "SELECT * FROM `config` WHERE `configid`='".intval($iId)."'" );
        }
    }



    /**
     * 更新配置项
     * @author SAUL
     * @param  int    $iConfigId
     * @param  string $sConfigValue
     * @return BOOL
     */
    function updateConfig( $iConfigId, $sConfigValue )
    {
        $iConfigId = intval( $iConfigId );
        $aConfig   = $this->config( $iConfigId );
        if( is_array($aConfig) && !empty($aConfig) )
        {
            $this->oDB->query("UPDATE `config` SET `configvalue`='".daddslashes($sConfigValue)."'" 
                    ."WHERE `configid`='".$iConfigId."'");
            return ($this->oDB->errno() == 0);
        }
        else
        {
            return FALSE;
        }
    }



    /**
     * 删除一个配置
     * @author SAUL
     * @param int $iConfigId
     * @return int -1:配置不存在 0:失败 1:成功
     */
    function configDel( $iConfigId )
    {
        $iConfigId = intval( $iConfigId );
        $aConfig   = $this->config( $iConfigId );
        if( is_array($aConfig) && !empty($aConfig) )
        {
            $this->oDB->query("SELECT * FROM `config` WHERE `parentid`='".$iConfigId."'");
            if( $this->oDB->ar() > 0 )
            {
                return 0;
            }
            else
            {
                $this->oDB->query( "DELETE FROM `config` WHERE `configid`='".$iConfigId."'" );
                return 1;
            }
        }
        else
        {
            return -1;
        }
    }



    /**
     * 更新配置项状态
     * @author SAUL
     * @param int $iConfigId
     * @param int $iStatus
     * @return BOOL
     */
    function setStatus( $iConfigId , $iStatus )
    {
        $iConfigId = intval($iConfigId);
        $iStatus   = intval( $iStatus );
        $aConfig   = $this->config($iConfigId);
        if( is_array($aConfig) && !empty($aConfig) )
        {
            $this->oDB->query("UPDATE `config` SET `isdisabled`='".$iStatus."'"
            ."WHERE `configid`='".$iConfigId."'");
            return ($this->oDB->errno() == 0);
        }
        else 
        {
            return FALSE;
        }
    }



    /**
     * 设置配置
     * @author SAUL
     * @param  array   $aConfig
     * @param  integer $iConfigId
     * @return BOOL
     */
    function update( $aConfig , $iConfigId )
    {
        if( empty($aConfig) || !is_array($aConfig) || !is_numeric($iConfigId) )
        {
            return 0;
        }
        $iConfigId = intval( $iConfigId );
        if( $this->checkconfig( $aConfig["configkey"] , $iConfigId ) )
        {
            return $this->oDB->update( 'config', $aConfig, "`configid`='".$iConfigId."'" );
        }
        else
        {
            return 0;
        }
    }



    /**
     * 获取配置项
     * @author SAUL
     * @param mixed $keys
     * @return array
     */
    function getConfigs( $mKeys )
    {
        $aConfigs = array();
        if( empty($mKeys) )
        {
            return $aConfigs;
        }
        if( is_array($mKeys) )
        {
            $aConfig = $this->oDB->getAll("SELECT * FROM `config` WHERE `configkey` IN ('".join("','", $mKeys)."')");
        }
        else 
        {
            $aConfig = $this->oDB->getOne("SELECT * FROM `config` WHERE `configkey` ='" .$mKeys. "'");
        }
        if( !empty($aConfig) )
        {
            if( is_array($mKeys) )
            {
                foreach( $aConfig as $value )
                {
                    $aConfigs[$value["configkey"]] = $value["configvalue"];
                }
            }
            else 
            {
                $aConfigs = $aConfig['configvalue'];
            }
        }
        return $aConfigs;
    }






    /**
     * 获取所有配置项并生成缓存文件
     * @author  james
     * @param   string  $sPath
     * @param   int     $iChannelid
     * @return 	 BOOL   TRUE or FALSE
     */
    function getConfigFile( $sPath, $iChannelId = 0 )
    {
        $iChannelId = intval($iChannelId);
        $sFileName  = $sPath .DS. "global_config.php";
        $sSql       = "SELECT `configkey`,`configvalue`,`defaultvalue` FROM `config` ".
                      "WHERE `channelid`='".$iChannelId."' AND `isdisabled`='0'";
        $aResult    = $this->oDB->getDataCached( $sSql, 10 );
        $aConfigs   = array();
        foreach( $aResult as $v )
        {
            $aConfigs[$v['configkey']] = $v;
        }
        $bIsWrite   = TRUE;
        if( file_exists($sFileName) )
        {
            require_once($sFileName);
            if( $configData == serialize($aConfigs) )
            {
                $bIsWrite = FALSE;
            }
        }
        if( $bIsWrite )
        {
            makeDir( $sPath );
            $sContent  = "<?php\r\n\$configData='".serialize($aConfigs)."';\r\n";
            $sContent .= "\$GLOBALS['sys_config']=unserialize(\$configData);\r\n?>";
            return file_put_contents( $sFileName, $sContent );
        }
        return TRUE;
    }



    /**
     * 批量更新配置项
     * @author SAUL
     * @param  array  $aConfig
     * @return BOOL
     */
    function updateConfigs( $aConfig )
    {
        if( empty($aConfig) || !is_array($aConfig) )
        {
            return TRUE;
        }
        $this->oDB->doTransaction();
        foreach( $aConfig as $key => $value )
        {
            $this->oDB->query("UPDATE `config` SET `configvalue`='".$value."' WHERE `configkey`='".$key."'");
            if( $this->oDB->errno() > 0 )
            {
                $this->oDB->doRollback();
                return FALSE;
            }
        }
        $this->oDB->doCommit();
        return TRUE;
    }
}
?>