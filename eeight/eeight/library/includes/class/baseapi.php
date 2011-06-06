<?php
/**
 * API模式程序基类
 * 路径: /library/includes/class/baseapi.php
 * 用途: 用于动态继承其具体实现方法 _runProcess() 方法
 * 
 * 使用: 在实际项目里，每个API 项目派生于此类. 负责收集运行信息,和日志记录等操作
 * 范例:
 * 			class api_active extends baseapi
 * 			{
 * 				.....
 * 				.....
 *              $this->mResultData = array(...); // 返回 数据结果集
 * 	            return TRUE;                     // 成功处理完成
 * 			}
 * 
 * 1, 数据传输, 完整效验, 加密解密
 * 2, 判断返回结果集是否超过指定大小 count() ? 超过1兆禁止返回?
 * 
 * 
 * 
 * Sender 请求发起方, 执行代码范例:
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *    $oApi = new baseapi(TRUE);             // 初始化对象
 *    $oApi->setTimeOut(10);                 // 设置读取超时时间
 *    $oApi->setResultType('serial');        // 设置返回数据类型 json | serial
 *    $oApi->setApiFullPath( 'http://www.baidu.com/index.html' )  // 完整路径,包括文件名
 *    $oApi->sendRequest( array(1,2,3,4,5,array('a'),6,7,8) );    // 发送结果集
 *    $a = $oApi->getDatas();             // 获取远程Receiver 方API返回的结果集
 *	  print_rr($a);
 * 
 * 
 * 
 * 
 * Receive 请求接受方, 执行代码范例:
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *    1, 定义新类, 继承 baseapi:    class api_activeUserFund extends baseapi { ... }
 *    2, 重写 _runProcess() 方法    
 *         public function _runProcess()
 *         {
 *              // do Something ...
 *              $this->mResultData = $data;
 *              return TRUE;
 *         }
 *    3, 运行 API 中的 runApi() 方法, 注意: 不是上面重写的 _runProcess()
 *         $oApi = new api_activeUserFund();  // 初始化 API 类
 *         $oApi->runApi();      // 运行多态 runapi 方法, 注意: 此处不是调用 _runProcess()
 *         $oApi->showDatas();   // 回显数据至 HTML, 使主控方Sender可以获取结果集
 *         EXIT;
 * 
 * 
 * @author 	    tom  090921
 * @version	1.2.0
 * @package	core
 */


class baseapi
{
	protected $bEnableDebug   = FALSE;      // 开启 DEBUG 模式
	protected $aSysInfo       = array();    // 系统信息数组(运行时间) stime,etime,htime

	protected $sIdentifier    = '';         // API 程序标识符名, 未做 addslashes() 处理
    protected $bFinished      = FALSE;      // API 程序是否完整执行成功 : TRUE=是
    protected $sProcessStatus = '';         // _runProcess() 返回值, 全等于TRUE为成功,其他都为失败信息    
    protected $aArgv          = array();    // sendRequest() 传递的 POST 数据 (用于POST数据传输)

    protected $sResultType    = 'serial';   // 支持 serial 或 json
    protected $mResultData    = '';         // 从远程服务器获取的 API 返回结果集
    protected $sApiFullPath   = '';         // API 发送的完整路径

    protected $sLastError     = '';         // 最后错误信息, 未加 addslashes()

    protected $iTimeOut       = 5;          // 默认超时时间
    protected $iProcessSql    = 0;          // 处理的 SQL 数量
    protected $bDoPostCheck   = TRUE;       // 是否进行 POST 安全检查 (单机调试用)

    protected $sHandsValue    = 'HaNds';              // 握手验证常量 (可改)
    protected $sHandsKey      = '__apitransfer__';    // POST[$sHandsKey] 键值
    protected $sHandsReturnT  = '__apireturntype__';  // POST[$sHandsReturnT] API数据的返回值类型 
    protected $sHandsCLI      = '__cliapi__';         //专用于CLI的API调用



	/**
	 * 构造函数(初始化数据库连接对象)
	 */
	public function __construct( $bEnableDebug=FALSE )
	{
		$_REQUEST = array_merge( $_GET, $_POST );//自定义$_REQUEST接收数据类型
	    $this->sResultType = 'serial';   // json | serial
	    $this->_initSysInfo();           // 初始化 系统信息
	    $this->sIdentifier = str_replace( DIRECTORY_SEPARATOR, '_', trim(__FILE__) );
	    if( $bEnableDebug === TRUE )
	    {
	        $this->bEnableDebug = TRUE;
	    }
	    else
	    {
	        $this->bEnableDebug = FALSE;
	    }
	    if( !function_exists('curl_init') || !function_exists('json_encode') )
	    {
	        $this->halt( 'function curl,json_encode not exists.' );
	    }
	}


	/**
	 * 析构函数, API 模式完成时的内存释放, 日志记录
	 */
	public function __destruct()
	{
	    $this->_doCleanUp();
	    $this->saveLog('api_runtime');
	    if( $this->bFinished == TRUE )
	    { // 程序正常运行成功
            return TRUE;
	    }
	    else
	    { // _apiRun() 未完整 执行导致的 程序异常中断
	        return FALSE;
	    }
	}

	/**
	 * 系统挂起前的清理
	 */
	protected function _doCleanUp()
	{
	    // 对结束时间, 消耗时间进行赋值
	    if( TRUE == $this->bEnableDebug )
	    {
            $this->aSysInfo['etime'] = getMicroTime();
	        $this->aSysInfo['htime'] = getTimeDiff( $this->aSysInfo['etime'] - $this->aSysInfo['stime'] );
	    }
	}

	/**
	 * 设置 CURL 远程读取超时时间
	 * @param int $iSecond  单位:秒
	 */
    public function setTimeOut( $iSecond = 5 )
    {
        $this->iTimeOut = intval($iSecond);
    }


    /**
     * 初始化系统信息
     */
	protected function _initSysInfo()
	{
	    if( FALSE == $this->bEnableDebug )
	    { // 未开启 DEBUG 模式则不进行记录
	        $this->aSysInfo = array(
	        'stime' => NULL,           // API 开始执行时间 .毫秒
	        'etime' => NULL,           // API 结束执行时间 .毫秒
	        'htime' => NULL,           // API 总执行时间   .毫秒
	        );
	        return ;
	    }
	    $this->aSysInfo = array(
	        'stime' => getMicroTime(), // API 开始执行时间 .毫秒
	        'etime' => NULL,           // API 结束执行时间 .毫秒
	        'htime' => NULL,           // API 总执行时间   .毫秒
	    );
	}


	/**
	 * 设置 API 执行成功标记
	 * 在派生的 _runProcess() 中最后调用, 用于判断 API 是否完整执行成功
	 */
	protected function setFinished()
	{
	    $this->bFinished = TRUE;
	}

	/**
	 * 运行 API 程序, 被将继承重写的 runApi() 的返回值写入 $this->sProcessStatus
	 * (注意) Receiver 方执行函数
	 */
	public function runApi()
	{
	    if( $this->bDoPostCheck == TRUE ) 
	    {
	        $this->checkVerify();
	    }
	    $this->sProcessStatus = $this->_runProcess();
	    if( TRUE === $this->sProcessStatus )
	    { // 只有全等于 TRUE 时, 才认为 API 程序执行成功
	        $this->setFinished();
	    }
	}

    public function setResultType( $sType = 'serial' )
    {
        $this->sResultType = $sType;
    }

	/**
	 * 处理业务逻辑, 并将处理返回值写入类属性 $this->sProcessStatus
	 * _runProcess 方法主要用于继承后的重写
	 * @return bool | string
     *     - 执行失败, 则返回 : '错误字符串'
     *     - 执行成功, 则返回全等于的 TRUE 类型
     * 即: 不全等于的任何字符|数字, 都认为业务流程执行失败
	 */
	protected function _runProcess()
	{ // do something like..
	    //$this->mResultData = 'test';
	    //return TRUE;
	}

	// 计算 Receiver 方 API 执行中的 SQL 执行数量..(暂时未用)
	protected function _setSqlCount( $iCount = 0 )
	{
	    $this->iProcessSql = $iCount;
	}


	// 主控方 Sender 调用 Receiver 方的完整 URL 路径
	// [ 2009-10-29 16:07 Tom ] 由于采用 API 独立WEB, 取消API路径检查
	protected function setApiFullPath( $sFullPath = '', $bSkipCheck=TRUE )
	{
	    //if( $bSkipChcek == FALSE && !preg_match('/^'.str_replace( '/', '\\/', getUrl(FALSE,FALSE)).'.*$/', $sFullPath) )
        //{
        //    $this->halt('Error in sTargetApiFullPath REGEXP');
        //}
	    $this->sApiFullPath = $sFullPath;
	}


	/**
	 * (握手)传输验证
	 */
	protected function genTransferKey( $sAction = 'ENCODE' )
	{
	    if( $sAction == 'ENCODE' )
	    {
	        return md5( $_SERVER['SERVER_ADDR']. $this->sHandsValue );
	    }
	    elseif( $sAction == 'CLI' )
	    {//CLI特殊调用
	    	return md5( "CLI_SPECIAL". $this->sHandsValue );
	    }
	    else
	    {
	        return md5( getRealIP(). $this->sHandsValue );
	    }
	}


    /**
     * 数据获取 CURL ( 超时处理 )
     *   - 0, 整理需 POST 的数据, 生成POST字符串, 加入握手信息
     *   - 1, CURL 发送请求
     *   - 2, 读取返回结果集
     *   - 3, 数据完整性效验, 安全检测
     *   - 4, 调用时的信息记录 $_REQUEST, $_SESSION 用户ID等
     */
    public function sendRequest( $aArgv=array(), $bIsCli=FALSE )
    {
        $this->aArgv = & $aArgv;
        if( '' != $this->sResultType )
        {
            $this->aArgv[ $this->sHandsReturnT ] = $this->sResultType;
        }

        if( $this->sApiFullPath == '' )
        {
            $this->halt('not init sApiFullPath');
        }

        $rCH = curl_init();
        curl_setopt( $rCH, CURLOPT_TIMEOUT, $this->iTimeOut );
        curl_setopt( $rCH, CURLOPT_URL, $this->sApiFullPath );  // 访问的 URL 地址
        curl_setopt( $rCH, CURLOPT_POST, 1 );                   // 发送 POST数据
        // 组装 POST 数据
        $sPostData = $this->sHandsKey.'='.$this->genTransferKey( $bIsCli === TRUE ? 'CLI' : 'ENCODE') .'&';
        $sPostData .= $this->sHandsReturnT.'='.$this->sResultType .'&';  // 数据格式: serial | json
        $sPostData .= $bIsCli === TRUE ? $this->sHandsCLI."=TRUE&" : "";
        $sPostData .= 'argv='. base64_encode( serialize( $this->aArgv ));
        curl_setopt( $rCH, CURLOPT_POSTFIELDS, $sPostData );
        curl_setopt( $rCH, CURLOPT_RETURNTRANSFER, 1);  // 1=返回结果集读入程序 0=显示
        $mData = curl_exec( $rCH );  // 发送
        $aInfo = curl_getinfo($rCH); // 获取传输信息
        curl_close($rCH);
        //$iHttpCode = curl_getinfo( $rCH, CURLINFO_HTTP_CODE);
        //print_rr( $aInfo );EXIT;
        $sErrorMessage = '';
        switch( $aInfo['http_code'] )
        {
            case 200:
                $sErrorMessage = "200: Success";
                BREAK;
            default:
                $sErrorMessage = "NetWork error: " . $aInfo['http_code'];
                BREAK;
        }
        if( 200 != $aInfo['http_code'] )
        { // 若返回 HTTP 传输状态错误, 则直接返回
            $this->mResultData = '';
            $this->sLastError = $sErrorMessage;
            return ;
        }

        //print_rr($mData);EXIT;
        //print_rr($aInfo);EXIT;
        /**
         *   [url] => http://mylocalhost/aframe/_example/low/_api/activeUserFund.php
         *   [content_type] => text/html; charset=UTF-8
         *   [http_code] => 200
         *   [header_size] => 147
         *   [request_size] => 212
         *   [filetime] => -1
         *   [ssl_verify_result] => 0
         *   [redirect_count] => 0
         *   [total_time] => 0.023
         *   [namelookup_time] => 0
         *   [connect_time] => 0
         *   [pretransfer_time] => 0
         *   [size_upload] => 0
         *   [size_download] => 175
         *   [speed_download] => 7608
         *   [speed_upload] => 0
         *   [download_content_length] => 175
         *   [upload_content_length] => 0
         *   [starttransfer_time] => 0.023
         *   [redirect_time] => 0
         */

        // TODO : 对远程读取数据的完整性&密文效验
        $bVerify = TRUE;

        // 类属性赋值 
        if( TRUE == $bVerify )
        {
            $this->mResultData = $mData;
        }
        else
        {
            $this->mResultData = '';
        }

        if( empty($mData) )
        {
            $this->mResultData = '';
        }
    }


    /**
     * 用于 Receiver 方, 向HTML回显数据
     * @return string
     */
    public function showDatas()
    {
        if( empty($this->mResultData) )
        {
            return '';
        }
        if( $this->sResultType == 'serial' )
        {
            echo serialize($this->mResultData);
        }
        if( $this->sResultType == 'json' )
        {
            echo json_encode($this->mResultData);
        }
        return '';
    }


    /**
     * 用于 Sender 获取 Receiver 返回的结果集
     * @return mix
     */
    public function getDatas()
    {
        //print_rr($this->mResultData); //EXIT; // #tom
        if( '' != $this->sLastError )
        {
            $this->makeApiResponse(FALSE, $this->sLastError );
            return $this->mResultData;
        }
        
        if( empty($this->mResultData) )
        {
            return '';
        }

        if( $this->sResultType == 'serial' )
        {
            $a = @unserialize($this->mResultData);
            if( FALSE == $a )
            {
                $this->halt('unserialize failed!');
                return '';
            }
            else
            {
                return $a;
            }
        }

        if( $this->sResultType == 'json' )
        {
            $a = @json_decode($this->mResultData);
            if( FALSE == $a )
            {
                $this->halt('json_decode failed!');
                return '';
            }
            else 
            {
                return $a;
            }
        }
        return '';
    }


    /**
     * 调试时使用的, 忽略 POST 安全检查开关
     */
    public function ignorePostCheck()
    {
        $this->bDoPostCheck = FALSE;
    }


	/**
	 * 根据主动调用方的参数, 效验数据真实性
	 * Passport (a, 请求发起方, Sender ) 
	 *    |
	 *    +-------> Low (b, 请求接收方, Receiver )
	 * 
	 * b.api/activeUserFund.php
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~
	 *    1, 收到请求, 判断请求 IP 是否允许
	 */
	public function checkVerify()
	{
	    // 1, Sender 白规则列表..
	    if( FALSE == $this->checkSenderIp() )
	    {
	        $this->halt('ip not allowed.');
	    }

	    // 2, 检查 Sender 发送信息的安全性
		if( empty($_POST) || empty($_POST[ $this->sHandsKey ]) )
	    {
	        if( TRUE === $this->bEnableDebug )
	        {
	            $this->halt('verified failed.');
	        }
	        header("HTTP/1.0 404 Not Found");
	        EXIT;
	    }

	    // 3, 禁止存在 GET 数据
	    if( !empty($_GET) )
	    {
	        if( TRUE === $this->bEnableDebug )
	        {
                $this->halt('wrong params');
	        }
	        header("HTTP/1.0 404 Not Found");
	        EXIT;
	    }

	    // 握手效验失败
	    if( $_POST[ $this->sHandsKey ] != $this->genTransferKey( !empty($_POST[$this->sHandsCLI])?"CLI":'DECODE') )
	    {
	        if( TRUE === $this->bEnableDebug )
	        {
                $this->halt('data error.');
	        }
	        header("HTTP/1.0 404 Not Found");
	        EXIT;
	    }

	    // 数据初始化
	    $this->aArgv = '';
	    $tmp = '';
	    if( FALSE != ($tmp = @base64_decode($_POST['argv'])))
	    {
	        if( FALSE != ($tmp = @unserialize($tmp) ))
	        {
	            $this->aArgv = $tmp;
	        }
	    }
	    unset($tmp);

	    if( !is_array($this->aArgv) )
	    {
	        $this->halt( 'unserial $POST data failed.' );
	        $this->aArgv = array();
	    }

	    if( $_POST[ $this->sHandsReturnT ] )
	    {
	        $this->sResultType = $_POST[ $this->sHandsReturnT ];
	    }
	    else
	    {
	        $this->sResultType = 'serial'; // 默认 serial
	    }
	}


	/**
	 * 获取检查发送者 IP 是否在规则列表中
	 * @return BOOL
	 *        TRUE  =  效验成功, 允许访问
	 *       FALSE  =  效验失败, 拒绝访问
	 */
	private function checkSenderIp()
	{
	    if( !isset($GLOBALS['aApi']['verifySenderIp']) || !isset($GLOBALS['aApi']['allowSenderIp']) )
        { // 如果配置文件中没有此开关, 则直接跳过, 返回 TRUE
            return TRUE;
        }

        if( $GLOBALS['aApi']['verifySenderIp']==TRUE 
            && is_array($GLOBALS['aApi']['allowSenderIp'])
            && !empty($GLOBALS['aApi']['allowSenderIp']) )
        {
            return in_array( getRealIP(), $GLOBALS['aApi']['allowSenderIp'] );
        }
        // TODO: 记录被拒绝访问的 IP.
        return FALSE;
	}


	/**
	 * 对信息包装返回
	 * @param bool $bSuccessed
	 * @param mix  $mData
	 */
    public function makeApiResponse( $bSuccessed=FALSE, $mMessageData )
    {
        $aReturnArray = array( 'status'=>'error', 'data'=>'' );
        if( $bSuccessed==TRUE )
        {
            $aReturnArray['status'] = 'ok';
        }
        $aReturnArray['data'] = $mMessageData;
        $this->mResultData = $aReturnArray;
        return $bSuccessed;
    }


	/**
     * 错误处理
     * @param string $sMessage
     */
    protected function halt( $sMessage = '', $bShowDebugTrace = FALSE )
    {
        $this->_doCleanUp();
        $this->sLastError =  'Error class.baseapi : '.$sMessage;
        // 执行日志记录操作. 不中断程序的执行
        if( TRUE == $this->bEnableDebug )
        {
            echo '<font color=red>Error : '.$this->sLastError ."</font><br/>";
            $this->saveLog();
        }
        if( TRUE == $bShowDebugTrace )
        {
            print_r( A::getDebugTrace(FALSE) );
        }
        EXIT;
    }

    /**
     * 将错误日志写入文本文件
     * @param string $sMessage
     */
    private function saveLog( $sDirName='api_error', $sType = 'txt' )
    {
        //echo '<br/> saveLog() : '.__FILE__.' . sDirName='.$sDirName .'<br/> ';
        if( FALSE == $this->bEnableDebug )
        {
            return '';
        }
        $aLogs = array(
            'htime'      => $this->aSysInfo['htime'],   // API 消耗时间
            'stime'      => $this->aSysInfo['stime'],   // API 开始时间
            'etime'      => $this->aSysInfo['etime'],   // API 结束时间
            'finished'   => intval($this->bFinished),   // API 程序是否完整执行成功
            'Finishstat' => $this->sProcessStatus,
            'lasterrmsg' => $this->sLastError,
            'sqlcount'   => intval($this->iProcessSql),
        );

        if( $sType == 'txt' )
        {
            $aLogs['argv']    = $this->aArgv;
            $aLogs['request'] = empty($_REQUEST) ? '' : $_REQUEST;
            $sMessage =  $this->sLastError . "\n  \$sSerial = ". serialize($aLogs);
            $oLog = new logs();
            $oLog->addDebug( $sMessage, $sDirName );
        }
        if( $sType == 'db' )
        {
            // TODO: 将日志写入数据库
        }
    }
}
?>