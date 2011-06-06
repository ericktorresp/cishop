<?php
/**
 * filecaches 文件缓存类
 * 
 * 依赖全局:
 *    全局宏 PAPPNAME 表示应用名. 不同的应用名归属于不同的缓存目录 (底层行为限制)
 *       - passport
 *       - passportadmin
 *       - gamelow
 *       - gamelowadmin
 *       - gamelowproxy
 * 
 * 
 * 实现功能 : 
 * ~~~~~~~~~~~
 *     - 统一的缓存读取, 写入接口  readCache(), writeCache()
 *     - 不同应用(APP)平台, 缓存文件夹相对独立
 *     - 根据不同的缓存标记 $sCacheTagName, HASH 计算其存储路径,及内容HASH值(并做效验)
 *     - 独占式缓存文件写入  file_put_contents(,,, LOCK_EX)
 * 
 * 
 * TODO : $this->halt 结合日志类, 做错误记录, 缓存数据写入跟踪
 *     
 * 
 * @author   Tom   090723
 * @version  1.0.0
 * @package  Core
 */

class filecaches
{
    private $sBasePath        = '';
    private $iCacheDirDepth   = 3;           // 缓存文件夹深度
    private $aCacheHash       = array();     // array( 'dirhash' => 'md5()', 'contenthash' => 'md5()' )


    /**
     * 构造函数
     * @author Tom 090722
     */
    public function __construct()
    {
        //echo '<font color=red><b>1001</b></font> class.filecaches.construct() Do Init..<br/>';
        if( defined( 'PAPPNAME' ) )
        {
            $this->sBasePath = A_DIR.DS.'tmp'.DS.'caches'.DS.PAPPNAME.DS;
        }
        else
        {
            $this->sBasePath = A_DIR.DS.'tmp'.DS.'caches'.DS.'etc'.DS;
        }
        $this->aCacheHash['dirhash']     = '';
        $this->aCacheHash['contenthash'] = '';
    }



    /**
     * 根据参数 dir|etc 获取缓存 HASH 值
     *     检查返回 $this->aCacheHash['dirhash'] or $this->aCacheHash['contenthash']
     * @param string $sType dir|etc
     */
    public function getCacheHashValue( $sType = 'dir' )
    {
        if( $sType == 'dir' )
        {
            if( empty($this->aCacheHash['dirhash']) )
            {
                $this->halt("filecache.getCacheHashValue() : dirhash is empty");
            }
            else
            {
                return $this->aCacheHash['dirhash'];
            }
        }
        else
        {
            if( empty($this->aCacheHash['contenthash']) )
            {
                $this->halt("filecache.getCacheHashValue() : contenthash is empty");
            }
            else
            {
                return $this->aCacheHash['contenthash'];
            }
        }
    }



    /**
     * 根据传递进的唯一值, 计算 hash 值
     *   dirhash     = 目录存储HASH = md5($sInfo);
     *   contenthash = 文件内容HASH = 位数 + half_md5_hash + crc32
     *   此函数在 readCache() 中被调用 
     * @param string $sCacheTagName
     */
    public function setCacheHash( $sCacheTagName = '' )
    {
        if( empty($sCacheTagName) )
        {
            $this->halt("filecache.setCacheFullHash() : empty sCacheTagName");
        }
        $sCacheTagName = trim($sCacheTagName);
        $iCount = strlen($sCacheTagName);
        $this->aCacheHash['dirhash']      = md5($sCacheTagName) . '_' . $iCount;
        $this->aCacheHash['contenthash']  = $iCount . '_'
                                            . md5(substr($sCacheTagName, 0, ceil(strlen($sCacheTagName)/2))).'_'
                                            . sprintf('%u', crc32($sCacheTagName));
        //echo 'dirhash='.$this->aCacheHash['dirhash'] .' || contenthash '. $this->aCacheHash['contenthash'].'<br/>';
        unset($iCount,$sCacheTagName);
    }


    /**
     * 根据参数, 计算 HASH 值
     *    1, 并读取缓存文件内容, 并根据内置规则判断是否过期, 并 RETURN FALSE
     *    2, 负责效验文件内容HASH, 判断文件内容是否正确
     *    3, 如果缓存文件读取成功, 则返回反序列化后的缓存内容
     *    - 如果 $iTimerSec = 0 , 则根据 MYSQL 的触发器规则进行缓存(缓存文件永不过期, 过期判断的规则由DB类实现)
     * 
     * @param string $sCacheTagName
     * @package int $iTimerSec
     * @return bool|array
     */
    public function readCache( $sCacheTagName = '', $iTimerSec = 60 )
    {
        $iTimerSec = intval($iTimerSec);
        $this->setCacheHash( $sCacheTagName.$iTimerSec ); // 初始化缓存HASH标记(路径,内容效验)
        // 1, 根据 HASH 值, 获取缓存文件完整路径
        if( '' == ($sCacheFileHash = $this->getCacheHashValue('dir') ) )
        {
            $this->halt('class.filecache.getCacheByHash() : Path load error!!');
        }

        // 2, 根据HASH 计算文件存放路径, 判断缓存文件是否存在
        if( !file_exists( $sCacheFileFullPath = $this->getDirFullPathHash(FALSE) ) )
        {
            return FALSE;
        }

        // 3, 根据文件(系统)最后修改时间, 判断文件缓存是否过期
        $aFileStat = @stat($sCacheFileFullPath);
        if( $iTimerSec!=0 && $aFileStat['mtime']+$iTimerSec < time() )
        {
            return FALSE;
        }

        // 4, 引入缓存文件
        $mResult = array(); // the variable of cache
        @include realpath($sCacheFileFullPath); // important! dont use include_once. shit!

        // 4, 文件内容判断
        if( empty($mResult)
            || empty($mResult['info'])
            || empty($mResult['info']['create_time'])
            || empty($mResult['info']['content_hash'])
            || empty($mResult['data'])
        )
        {
            // TODO: 跟踪文件内容错误的日志
            return FALSE;
        }

        if( $this->aCacheHash['contenthash'] != $mResult['info']['content_hash'] )
        {
            // TODO: 跟踪缓存 '内容值效验' 失败的日志
            return FALSE;
        }

        if( FALSE == ($mResult['data']=@unserialize($mResult['data'])) )
        { // 缓存内容反序列化失败
            // TODO: 跟踪 '缓存内容' 反序列化失败的日志
            return FALSE;
        }
        //echo "TOMD : 成功返回缓存!<br/>";
        // 5, 将缓存返回
        return $mResult['data'];
    }



    /**
     * 写入缓存文件, 并同时写入:
     *    规则: 以PHP变量形式存储至 php 文件中, 仅用于PHP程序 include (定义 IN_APPLE )
     *          if( !defined('IN_APPLE') || IN_APPLE!==TRUE ) { die( 'error.frame.noAccess.caches' ); }
     *    以数组形式存储
     *       $mResult = array(
     *            'info' => array( 'create_time' => '2009-09-09 12:12'  ) ...
     *            'data' => serialize( array( CachesDatas ) )
     * 		);
     * 
     *    1, WEB 服务器当前时间
     *    2, 序列化后的缓存文件内容
     *
     * @param mix $mDatas
     * @return bool
     */
    public function writeCache( $aDatas )
    {
        $this->checkHashName( $this->getCacheHashValue('dir') );
        if( '' == ($sCacheFileFullPath = $this->getDirFullPathHash(TRUE) ))
        { // 如果缓存路径计算失败, 则退出
            return FALSE;
        }

        if( !is_dir( $sDirName = dirname( $sCacheFileFullPath )) )
        { // 如果缓存文件写入的目标文件夹不存在. 则退出
            return FALSE;
        }

        // 缓存数据整理
        $mResult  = array();
        $iNowTime = time();
        $mResult['info']['create_time']  = $iNowTime;   // 缓存生成时间 (相对WEB服务器)
        $mResult['info']['content_hash'] = $this->aCacheHash['contenthash']; // 文件内容效验字符串
        $mResult['data'] = serialize($aDatas);   // 将变量值序列化后, 写入缓存文件 (允许data字符串或数组)

        // 写入缓存文件
        $sWriteContent = "<?php if( !defined('IN_APPLE') || IN_APPLE!==TRUE ) { die( 'error.frame.noAccess.caches' ); }\n";
        $sWriteContent .= "\$mResult = ". var_export($mResult, TRUE) . ";\n?>";
        $iWriteCountChars = file_put_contents( $sCacheFileFullPath, $sWriteContent, LOCK_EX );
        if( $iWriteCountChars < 1 )
        { // TODO : 写文件失败, 做日志跟踪
            //die('写入文件失败<br/>');
            return FALSE;
        }
        //echo 'TOMD 写入缓存<br/>';
        //clearstatcache();
        //echo '======数据写入成功, 目标文件夹 => '.$sDirName.'<br/>';
        return true;
    }



    public function checkHashName( $sHashName )
    {
        if( preg_match('/[^a-z0-9\-_]/i', $sHashName) )
        {
            $this->halt( "checkHashName(), Illegal HashName: $sHashName" );
        }
    }

    /**
     * 根据 $this->aCacheHash['dirhash'] 值, 和 $this->iCacheDirDepth 目录深度设置
     *    计算缓存文件完整路径并返回 
     *
     * @param string $sHash
     * @return string
     */
    public function getDirFullPathHash( $bAutoCreateCacheDir = TRUE )
    {
        if( empty($this->aCacheHash['dirhash']) )
        {
            $this->halt('filecaches.getDirFullPathHash() : dirhash is empty');
        }
        $sRootDir = $this->sBasePath;
        @makeDir( $sRootDir );
        if( $this->iCacheDirDepth > 0 )
        { // 目录深度大于0
            for( $i=1; $i<=$this->iCacheDirDepth; $i++ )
            {
                $sRootDir .= substr( $this->aCacheHash['dirhash'], 0, $i ) . DS;
                if( TRUE == $bAutoCreateCacheDir )
                {
                    @makeDir( $sRootDir );
                }
            }
            return $sRootDir . $this->aCacheHash['dirhash'] . '.cache';
        }
        else
        {
            return $this->sBasePath . $this->aCacheHash['dirhash'] . '.cache';
        }
        return '';
    }


    
    
    /**
     * 错误处理
     *   Just return false, dont halt the program.
     * @param string $sMessage
     */
    private function halt( $sMessage = '' )
    {
        // 执行日志记录操作. 不中断程序的执行
        $this->saveErrorLog($sMessage);
        return FALSE;
    }



    /**
     * 将错误日志写入文本文件
     *
     * @param string $sMessage
     */
    private function saveErrorLog( $sMessage = '' )
    {
        //TODO: 将错误记录至日志文件
        echo $sMessage.'<br/>';
    }
}
?>