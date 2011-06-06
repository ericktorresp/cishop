<?php
/**
 * CLI API 模型 (继承于 baseapi )
 * 
 * 功能:
 *    -- 用于CLI执行时调用API 
 * 
 * 范例:
 * ~~~~~~~~~~~~~~~~~~~~~~~
 *    $oCliApi = new cliapi( 1, 'activeUserFund', TRUE, $oDBO=array() );
 *    $oCliApi->setTimeOut(15);                 // 设置读取超时时间
 *    $oCliApi->setResultType('serial');        // 设置返回数据类型 json | serial
 *    $oCliApi->sendRequest( array(1,2,3,4,5,array('a'),6,7,8) );    // 发送结果集
 *    $a = $oCliApi->getDatas();               // 获取远程Receiver 方API返回的结果集
 *    print_rr($a);
 * 
 * @author    james 
 * @version   1.1.0
 * @package   passport
 */

class cliapi extends baseapi
{
    /* @var $oDB db */
    protected $oDB  = '';   //数据库连接对象实例

    /**
     * 频道数组
     * array(
     *    0 => '/passport/',       // id => path
     *    1 => '/low/',            ...
     * )
     * @var array
     */
    protected $aChannels = array(); // 频道数组
    protected $sChannelApiDir = '/_api/';
    protected $_iChannelId = 0;    //频道ID
    protected $_sApiName   = '';   //API名字



    /**
     * 构造
     *
     * @param int $iChannelId     频道ID
     * @param string $sApiName    API文件名(不含后缀)
     * @param bool $bEnableDebug  是否开启调试
     * @param array $aDBO         使用的DB数组
     */
    public function __construct( $iChannelId = 0, $sApiName = '', $bEnableDebug = FALSE, $aDBO = array()  )
    {
        parent::__construct( $bEnableDebug );
        if( empty($sApiName) )
        {
             A::halt('sApiName is empty!');
        }

        if( empty($aDBO) )
        {
            if( empty($GLOBALS['aSysDbServer']['master']) )
            {
                A::halt('$GLOBALS[\'aSysDbServer\'][\'master\'] is empty!');
                exit;
            }
            $aDBO = $GLOBALS['aSysDbServer']['master'];
        }
        
        // 初始化数据库
        $this->oDB = &A::singleton( 'db', $aDBO );

        // 初始化频道信息(数据库)
        $this->initChannelInfo();

        // 初始化完整路径
        $this->initChannelPath( $iChannelId, $sApiName );
        
        //保存channelid和api 名字
        $this->_iChannelId = intval($iChannelId);
        $this->_sApiName   = daddslashes($sApiName);
    }

    
    /**
     * 初始化 API 完整地址
     * @param int $iChannelId
     * @param string $sApiName
     */
    private function initChannelPath( $iChannelId=0, $sApiName='', $bSkipChcek=TRUE )
    {
        // 数据检查并初始化
        $iChannelId = intval($iChannelId);
        if( !array_key_exists($iChannelId, $this->aChannels) )
        {
            A::halt('$iChannelId not Exist Value = '. $iChannelId );
        }

        if( !preg_match('/^[a-z_]+$/i',$sApiName) )
        {
            A::halt('$sApiName not legal Value = '. $sApiName );
        }
        if( !empty( $GLOBALS['aApiConfig']['sAddress'][$iChannelId] ) || 
                !empty( $GLOBALS['aApiConfig']['iPort'][$iChannelId] ) 
            )
        {
            $sApiFullPath = 'http://'.$GLOBALS['aApiConfig']['sAddress'][$iChannelId]
                            . ':' .$GLOBALS['aApiConfig']['iPort'][$iChannelId]
                            . $this->sChannelApiDir . $sApiName . '.php';
            $this->setApiFullPath($sApiFullPath);
            return ;
        }
        else
        {
            A::halt('model.channelapi.php : get Api Address from cid failed.');
        }
    }


    public function sendRequest( $aData )
    {
        parent::sendRequest( $aData, TRUE );
    }


    /**
     * 初始化频道数据
     */
    private function initChannelInfo( $iCacheTime = 600 )
    {
        $aData = $this->oDB->getDataCached('SELECT `id`,`path` FROM `channels` WHERE `isdisabled`=0 ', 
                                                    intval($iCacheTime) );
        if( empty($aData) )
        {
            A::halt('model.channelapi.php : getDataCached failed.');
        }
        $this->aChannels[0] = '/';  // for passport api path
        foreach( $aData AS $v )
        {
            $this->aChannels[ $v['id'] ] = $v['path']; 
        }
    }
}
?>