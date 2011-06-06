<?php
/**
 * logs 日志类
 * 
 * 实现功能:
 * ~~~~~~~~~~
 *   - 日志分类型记录, 例: 
 *      1, 调用函数 A::halt() 会产生 'syserror' 类型的日志
 *      2, $GLOBALS['oLogs']->addDebug( '测试的啦', 'message' );  // 使用自定义的 message 类型名(英文+数字)
 *      3, db 类, 如果开启日志功能, 可以记录所有执行 SQL , 类型为 allsql
 * 
 * 
 * TODO: 日志文件 => MYSQL 表的程序
 *   - 对序列化 $this->bUseSerialize 的支持, 方便 MYSQL 导入分析
 *   - 单个页面的效率总体统计 sql + html + php = total 
 *   - memory_get_usage 内存调试 lib_common.php
 *   - 用户级错误处理
 * 
 * 
 * @author  Tom,James
 * @version 1.0.0
 * @package Core
 */

if( isset($_SERVER['REQUEST_URI']) )
{
    define( 'A_REQUEST_URI', $_SERVER['REQUEST_URI'] );
}
elseif( empty($_REQUEST) && isset($argv) )
{
    define( 'A_REQUEST_URI', $_SERVER['PHP_SELF'].join('|',$argv) ); // for cli mode
}
else 
{
    define( 'A_REQUEST_URI', $_SERVER['PHP_SELF'] );
}


class logs
{
	/**
	 * 日志记录方式 : 
	 * 
	 *   ->   0   以一个文件记录所有 LOG
	 *   ->   1   SQL,ERROR,LOG 文件分开记录
	 *   ->   2   以脚名记录日志
	 *
	 * @var int
	 */
	private $iLogType = 1;
	
	/**
	 * 日志存放文件的基本路径, 默认 A_DIR . DS . 'tmp' . DS . 'logs'
	 *
	 * @var string
	 */
	private $sBasePath = "";
	
	/**
	 * 是否开启日志记录功能
	 *
	 * @var bool
	 */
	private $bIsDebug = FALSE; 
	
	/**
	 * 是否开启序列化存储功能
	 *
	 * @param bool $aConfig
	 */
	private $bUseSerialize = FALSE; 

	/**
	 * 日志文件最大字节数 
	 * 默认: 1024*1024*5 = 5242880 
	 *
	 * @var int
	 */
	private $iMaxLogFileSize = 5242880;
	
	
	/**
	 * 构造函数
	 *    $aConfig[sBasePath] = A_DIR.DS.'tmp'.DS.'logs'.DS;
	 *    $aConfig[iLogType]  = 1;
	 *    $aConfig[iMaxLogFileSize]  = 5242880;
	 */
	function __construct( $aConfig = array() )
	{
	    $this->bIsDebug = (bool)(intval(A::getIni('error/trigger_error')) & APPLE_ON_ERROR_LOG);
	    if( !$this->bIsDebug )
	    { // 如果未开日志总开关, 直接返回
	        return ;
	    }
	    $this->inits( $aConfig ); // 初始化日志目录
		if( !file_exists( $this->sBasePath ) )
		{ // 目录不存在则调用全局函数建立
            makeDir( $this->sBasePath );
        }
        if( !isset($GLOBALS['config_logs']["iStartTime"]) )
        { // 初始化对象构造的时间
            $GLOBALS['config_logs']["iStartTime"] = $this->getTime();
        }
	}

	
	/**
	 * 初始化日志目录
	 *
	 * @param array $aConfig
	 */
	private function inits( $aConfig )
	{
	    // 01, 初始化目录
	    if( isset($aConfig["sBasePath"]) && @is_dir($aConfig["sBasePath"]) )
	    { // 优先使用参数中的 $aConfig["sBasePath"] 作为日志路径
	        $this->sBasePath = $aConfig["sBasePath"];
	    }
	    elseif( FALSE != ($tempPath = A::getIni('class.logs.sBasePath')) )
	    { // 使用全局 ini 中的设置
	        $this->sBasePath = $tempPath;
	        unset($tempPath);
	    }
	    else 
	    { // 使用系统默认
	        $this->sBasePath = A_DIR.DS.'tmp'.DS.'logs'.DS;
	    }
	    
	    // 02, 初始化类型, 默认 =1 分开记录
	    $this->iLogType  = isset($aConfig["iLogType"]) ? (int)$aConfig["iLogType"] : 1 ;
	    
	    // 03, 初始化日志尺寸
	    if( isset($aConfig["iMaxLogFileSize"]) && is_numeric($aConfig["iMaxLogFileSize"]) )
	    { 
	        $this->iMaxLogFileSize = $aConfig["iMaxLogFileSize"];
	    }
	    elseif( FALSE != ($temp = A::getIni('class.logs.iMaxLogFileSize')) )
	    { 
	        $this->iMaxLogFileSize = $temp;
	        unset($temp);
	    }
	    else 
	    { 
	        $this->iMaxLogFileSize = 5242880; // 默认5兆
	    }
	}
	
	
	
	/**
	 * 析构函数, 将全局数组中的 DEBUG 信息, 根据 $this->iLogType 写入本地文件
	 *     (array) $GLOBALS['config_logs']['message'][ strtolower($type) ] = Array(
	 *          syserror => array( 
	 * 					    0 => array( '2009-02-23 12:34:56', $message, '消耗时间' ), 
	 *                      1 => array( '2009-02-23 12:34:56', $message, '消耗时间' ), 
	 *                      ....
	 * 					 ),
	 *          message => array ( ... )
	 *          allsql => array( ... )
	 *      );
	 * 
	 * 重要: 需要考虑到日志输出后, 导入 MYSQL 进行分析的问题, 所以注意格式规则
	 * ~~~~~
	 *       字段间分割符使用 [空格]
	 */
	function __destruct()
	{
	    //A::print_rr( $GLOBALS['config_logs'], TRUE );
	    // echo 'a=' . $this->iLogType .'<br/>';
	    if( empty($GLOBALS['config_logs']['message']) )
	    {
	        return ;
	    }
	    switch ($this->iLogType)
	    {
	    	case 0 : 
	    	        // 0 以一个文件记录所有 LOG
	    	        // 目录规则:    类构造时基础路径 + onefile + [D]当前日期 + all_*.txt 
	    	        // 文件名规则:  如上目录名 + all_$i.txt  每个文件不超过 $this->iMaxLogFileSize 字节
	    	        $sLogDir = $this->sBasePath . DS . 'onefile';
	    	        if( !file_exists( $sLogDir )) makeDir( $sLogDir ); 
	    	        $sLogDir .= DS . date('Ymd');
	    	        if( !file_exists( $sLogDir )) makeDir( $sLogDir );
                    
	    	        // 处理日志消息, 两个横线开头的为注释
	    	        //A::print_rr( $_SERVER,TRUE );
	    	        $files = A_REQUEST_URI;
	                $files .= !empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
	    	        $sLogMessage = '-- #file : '.$files. " -------------\n";
	    	        //A::print_rr($GLOBALS['config_logs']['message'],TRUE);
	    	        foreach( $GLOBALS['config_logs']['message'] as $k => $aDataArr )
	    	        { // date, time, spend, type, file
	    	            $sLogMessage .= $aDataArr['date'] ." " .
	    	                            $aDataArr['time'] ." " .
	    	                            $aDataArr['spend'] ." " .
	    	                            '['.$aDataArr['type'] ."] " .
	    	                            $aDataArr['file'] ." " .
	    	                            $aDataArr['mesg'] . "\n"; 
	    	            //echo $sLogMessage;exit;
	    	        }
	    	        $sLogMessage .= "\n";
            	    $sBaseFileName = 'all';  // 文件名
                    $sExtendName = 'txt';    // 扩展名
                    $bCanWrite = FALSE;      // 可以写入文件的标记
                    for( $i=0;  ; $i++ ) 
                    { // 无限循环, 直到变量 $bCanWrite = TRUE
                        $sLogsFileName = $sLogDir.DS.$sBaseFileName. '_' . $i . '.' . $sExtendName;
                        if( !is_file( $sLogsFileName ) )
                        { // 建立日志文件
                            $bCanWrite = TRUE;
                        }
                        else
                        { // 循环, 确定文件的大小
                            $fp = @fopen( $sLogsFileName , "r");
                            $fstat = @fstat($fp);
                            fclose($fp);
                            if( $fstat[7] < $this->iMaxLogFileSize )
                            {
                                $bCanWrite = TRUE;
                            }
                        }
                        if( $bCanWrite )
                        {
                            $this->write( $sLogsFileName, $sLogMessage );
                            /* TODO 对 $this->bUseSerialize 序列化的支持 
                            if($this->bUseSerialize) 
                            { // 如果开启序列化, 则同样写入
                                $this->write( $sLogsFileName.'.serial.txt', serialize($GLOBALS['config_logs']['message'])."\n", TRUE );
                            }
							*/
                            break;
                        }
                    } // end of for()
	    	        break;
	    	case 1 : 
					/** 
					 * 按类型 GLOBALS['config_logs'][ $key ] 分开记录, 目录规则:
	    	         *    $logdir ./group/syserror
	    	         *    $logdir ./group/message
	    	         *    $logdir ./group/allsql
	    	         * 
	    	         *  日志类型分目录存放, 目录不存在则创建
	    	         *  文件名规则使用日期, 一天一个  090223.syserror.txt 日期在前,方便排序
					 */
	                $sLogDir = $this->sBasePath . DS . 'group';
	    	        if( !file_exists( $sLogDir )) makeDir( $sLogDir ); 
                    
	    	        //A::print_rr($GLOBALS['config_logs']['message'],TRUE);
	    	        foreach( $GLOBALS['config_logs']['message'] as $type => $aDataArr )
	    	        { 
	    	            $sLogMessage = '';
	    	            $sLogDir = $this->sBasePath . DS . 'group'. DS . $type; // 如果日志类型目录不存在, 则创建
	    	            if( !file_exists( $sLogDir )) makeDir( $sLogDir );
	    	            $sLogsFileName = $sLogDir . DS. date('ymd') . '.' . $type . '.txt';
	    	            foreach ( $aDataArr as $k => $arr )
	    	            {
    	    	            // date, time, spend, type, file
    	    	            $sLogMessage .= $arr['date'] ." " .
    	    	                            $arr['time'] ." " .
    	    	                            $arr['spend'] ." " .
    	    	                            '['.$arr['type'] ."] " .
    	    	                            $arr['file'] ." " .
    	    	                            $arr['mesg'] . "\n"; 
    	    	            //echo $sLogMessage;
	    	            }
	    	            $sLogMessage .= "\n";
	    	            $this->write( $sLogsFileName, $sLogMessage ); // 写入
	    	            //exit;
	    	        }
	    	        break;
	    	case 2 : 
	    	        // 以脚名记录日志
	    	        // $GLOBALS['config_logs']['message'][] 进行解析
	    	        // 脚本名 /frame_apple/net_manage/index.php 将斜线换成下划线
	    	        // 存储于 $logdir./scriptname/
	    	        // 文件名规则 :   日期 + 转义脚本名 .txt
	    	        
	    	        $sLogDir = $this->sBasePath . DS . 'scriptname';
	    	        if( !file_exists( $sLogDir )) makeDir( $sLogDir ); 
	    	        $files = preg_replace( "|[\\/]|", '_', A_REQUEST_URI);
	    	        $sLogsFileName = $sLogDir . DS. date('ymd') . '_' . $files . '.txt';
	    	        //A::print_rr($GLOBALS['config_logs']['message']);
	    	        //echo '$sLogsFileName = '.$sLogsFileName . '<br/>';exit;
	    	        $sLogMessage = '';
	                foreach( $GLOBALS['config_logs']['message'] as $k => $aDataArr )
	    	        { 
    	    	        // date, time, spend, type, file
    	    	        $sLogMessage .= $aDataArr['date'] ." " .
    	    	                        $aDataArr['time'] ." " .
    	    	                        $aDataArr['spend'] ." " .
    	    	                        '['.$aDataArr['type'] ."] " .
    	    	                        $aDataArr['file'] ." " .
    	    	                        $aDataArr['mesg'] . "\n"; 
    	    	        //echo $sLogMessage.'<br/>';
	    	        }
	    	        $sLogMessage .= "\n";
	    	        $this->write( $sLogsFileName, $sLogMessage ); // 写入
	    	        break;
	    	default:
	    	        break;
	    	exit;
	    }
	} // end of __destruct()



	/**
	 * 将日志内容写入硬盘
	 *
	 * @param String $sfilePathName
	 * @param String $sContent
	 * @param bool $bContentIsSerial
	 */
    function write( $sFilePathName, $sContent="", $bContentIsSerial = FALSE )
    {
        //var_dump($sContent);exit;
        //die('write() : '.$sFilePathName);
        if( (bool)$fp = @fopen($sFilePathName, "a+") )
        {
            @fwrite($fp, $sContent );
            @fclose($fp);
        }
        else
        {
            die( "class.logs.write().failed" );
        }
    }
	
    
	// 取得程序当前时间, 1235333403.7178
	function getTime()
	{
		$iTemp = microtime(); //返回目前时间的百万分之一秒戳记值
        $iTemp = explode(" ",$iTemp);
        return doubleval( $iTemp[1])+doubleval($iTemp[0] );
	}
	
	
	// 初始化时间
	function initTimer()
	{
		$GLOBALS['config_logs']["iStartTime"] = $this->getTime();
	}
	
	
	function setSerial( $bFlag = FALSE )
	{
	    $this->bUseSerialize = $bFlag;
	}
	
	
	/**
	 * 将 Debug 信息写入数组
	 *
	 * @param String $sType  error,sql,notice
	 * @param String $sMessage  错误消息
	 */
	function addDebug( $sMessage = '', $sType='error' )
	{
	    //echo '$sMessage=' . $sMessage."<br/>\n";
	    if ( !preg_match('/^[a-z0-9_]+$/i',$sType) )
        {
            A::halt('logs::addDebug().sType name('.$sType.') Error');
        }

	    $iTime = $this->getTime() - doubleval( $GLOBALS['config_logs']["iStartTime"]);
	    $iTime = number_format( $iTime, 8, '.', ''); // 小数精度8位
	    $files = A_REQUEST_URI;
	    //die('11='.$files);
	    //$files = !empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
	    //die($files);
	    
	    if( $this->iLogType == 1 ) 
	    { // SQL,ERROR,LOG 文件分开记录模式, 数组分离
	        $GLOBALS['config_logs']['message'][ strtolower($sType) ][] = array( 
    		        'time' => $GLOBALS['config_logs']["iStartTime"], 
    		        'date' => date("Y-m-d H:i:s"), 
    		        'mesg' => $sMessage, 
    		        'spend' => $iTime,
    		        'file' => $files,
	        		'type' => strtolower($sType),
    		);
	    }
	    else 
	    { // 其他方式, 则直接按时间顺序写入 $GLOBALS['config_logs']['message']
    		$GLOBALS['config_logs']['message'][] = array( 
    		        'time' => $GLOBALS['config_logs']["iStartTime"], 
    		        'date' => date("Y-m-d H:i:s"), 
    		        'mesg' => $sMessage, 
    		        'spend' => $iTime,
    		        'file' => $files,
    		        'type' => strtolower($sType),
    		);
	    }
	    //echo "<pre>";print_r( $GLOBALS['config_logs']['message'][ strtolower($sType) ]);//exit;
	}



} // end of class



/*
register_shutdown_function("unLoads");
//程序运行结束，及出错中断处理
function unLoads()
{
	global $debug;

	switch(connection_status()){
	case 1:
		$debug->setError("用户中断！");
		$debug->__destruct();
		break;
	case 2:
		$this->setError(0,"超时中断！");
		$debug->__destruct();
	}
	//处理程序结束处理函数
//	if(method_exists(CONTROL_MODE . "_class","unLoad"))
//	{
//		$command = new ReflectionMethod(CONTROL_MODE . "_class","unLoad");///映射
//		$command->invoke( NULL );
//	}
	//未读取的有改变的表缓存
	//$aCache = array(date("Y-m-d H:i:s"), $GLOBALS["aNewCache"]);
//	print_r($aCache);
	@file_put_contents($GLOBALS['Cache_FileName'], serialize($aCache));
}
*/

?>