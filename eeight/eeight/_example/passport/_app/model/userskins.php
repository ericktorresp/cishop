<?php
/**
 * 模板皮肤 skin 模型
 * 
 * 功能:
 * --           
 *  
 * @author     Tom
 * @version    1.1.0
 * @package    passport
 * 
 */
class model_userskins extends basemodel 
{

    private $sSkinDirBasePath = '';         // 基础路径
    private $sSkinDirDefault  = 'default';  // 默认模板
    
    public function __construct()
    {
        parent::__construct();
        // 取 PassPort 模板路径
        @$this->sSkinDirBasePath = PDIR_USER.DS.'_app'.DS.'views'.DS;
    }
    
    
    /**
     * 设置模板基础路径
     *
     * @param  string $sBasePath 模板基础路径
     * @author Tom
     */
    public function setBasePath( $sBasePath = '' )
    {
        $this->sSkinDirBasePath = $sBasePath;
    }
    
    
    /**
     * 获取模板基础路径
     *
     * @return string
     * @author Tom
     */
    public function getBashPath()
    {
        return $this->sSkinDirBasePath;
    }
    
    
    /**
     * 获取模板默认目录
     *
     * @return string
     * @author Tom
     */
    public function getDirDefault()
    {
        return $this->sSkinDirDefault;
    }


    /**
     * 检测风格是否存在
     * @param  string $sSkinName  域名
     * @return BOOL   模板目录存在, 则为真. 
     * @author Tom
     */
    public function skinsCheck( $sSkinName )
    {
        if( preg_match("/^[0-9a-z_]+$/i", $sSkinName) && is_dir( $this->sSkinDirBasePath . $sSkinName ) )
        {
            return TRUE;
        }
        else 
        {
            return FALSE;
        }
    }


    /**
     * 获取可选的,唯一的模板风格名
     *
     * @param bool $bReturnArray
     * @param string $sSelected
     * @return mix
     */
    public function getDistintSkins( $bReturnArray = TRUE, $sSelected = '' )
    {
        $aReturn       = array();
        $aDirFileList  = scandir( $this->sSkinDirBasePath );
        foreach( $aDirFileList AS $v )
        {
            if( substr( $v, 0,1 ) == '.' || is_dir($aDirFileList.$v) )
            {
                continue;
            }
            $aReturn[] = $v;
        }
        unset($aDirFileList);

        if( $bReturnArray == TRUE )
        {
            return $aReturn;
        }
        else
        {
            $sReturn = '';
            foreach( $aReturn as $v )
            {
                $sSel = $sSelected==$v ? 'SELECTED' : '';
                $sReturn .= "<OPTION $sSel value=\"".$v."\">".$v."</OPTION>";
            }
            return $sReturn;
        }
    }



    /**
     * 获取总代与模板风格对应关系
     *
     */
    public function & getTopProxyResult()
    {
        // 1, 获取所有总代
        $aAgentsNew = array();
        $oAgent     = new model_agent();
        $aAgents    = $oAgent->agentList();
        unset($oAgent);
        
        // 2, 可用模板数组
        $aAvailableSkin = $this->getDistintSkins();

        // 3, 获取总代与模板对应关系
        $aUserSkinNew = array();
        $aUserSkin = $this->oDB->getAll("SELECT * FROM `userskins` ");
        foreach( $aUserSkin AS $v )
        {
            $sTmpSkinName = in_array( $v['skins'], $aAvailableSkin ) ? $v['skins'] : $this->sSkinDirDefault;
            $aUserSkinNew[$v['userid']] = $sTmpSkinName;
        }
        unset($aUserSkin);
        
        // 3, 数据整理
        foreach( $aAgents AS $v )
        {
            $aAgentsNew[ $v['userid'] ] = array(
                'username' => $v['username'],
                'skins'    => isset( $aUserSkinNew[ $v['userid'] ] ) 
                            ? $aUserSkinNew[ $v['userid'] ] : $this->sSkinDirDefault,
            );
        }
        unset($aAgents);
        return $aAgentsNew;
    }


    /**
     * 根据数组, 更新总代与皮肤的关系
     * @param array $aDatas
     * @return int
     * $aDatas = array(
     *     'userid' => 28,
     *     'skins'  => default .. 
     * );
     */
    public function updateUserSkinRelation( $aDatas = array() )
    {
        if( empty($aDatas) )
        {
            return FALSE;
        }

        $iTimes = 0;
        foreach( $aDatas AS $v )
        {
            $aAvailableSkin = $this->getDistintSkins();
            if( in_array( $v['skins'], $aAvailableSkin ) )
            {
                $this->oDB->query("REPLACE INTO `userskins`(`userid`,`skins`) VALUES( '".
                intval($v['userid'])."','". daddslashes($v['skins']) ."' ) ");
                $iTimes += $this->oDB->ar();
            }
        }
        
        if( $iTimes > 0 )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }


    /**
     * 根据用户自己的 ID 获取总代ID并获取皮肤 Skins
     *
     * @param int  $iUserId
     * @param bool $bIsProxyOper  是否是总代管理员
     */
    public function getSkinByUserId( $iUserId, $bIsProxyOper = FALSE )
    {
        $iUserId = intval($iUserId);
        $sSql = '';
        if( $bIsProxyOper == TRUE )
        { // 是总代管理员
            $sSql = "SELECT `skins` FROM `usertree` ut LEFT JOIN `userskins` us "
                        ." ON ut.`parentid`=us.`userid` WHERE ut.`userid`='$iUserId' LIMIT 1";
        }
        else 
        {
            $sSql = "SELECT `skins` FROM `usertree` ut LEFT JOIN `userskins` us "
                        ." ON ut.`lvtopid`=us.`userid` WHERE ut.`userid`='$iUserId' LIMIT 1";
        }
        $aRes = $this->oDB->getOne($sSql);
        return !empty($aRes['skins']) ? $aRes['skins'] : 'default';
    }


}
?>