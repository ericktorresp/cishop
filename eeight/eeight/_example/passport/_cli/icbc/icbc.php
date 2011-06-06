<?php
// 注意：
// 1.不一定需要http 1.1和Keep-Alive
// 2.23位的session有时候会发生异常

require_once "mod.php";
require_once "wget2.class.php";
define("LOG_PATH", dirname(__FILE__)."/../../_tmp/logs/icbc"); // 日志路径
define("REPEAT_TIMES", 3);
define("RETRY_TIMES", 3); 				// 重试次数
define("DATE_ERROR", 23); 				// 日期错误	
define("ACCNUM_ERROR", 21);				// 获取账卡失败
define("PAGEID_ERROR", 22);				// 页码错误
define("DETAIL_ERROR", 32);				// 获取详情失败
define("GET_ERROR", 11);				// 抓取失败
define("RETRY_STATUS", 16);				// 抓取重试
define("SESSION_EXPIRED", 12);			// session过期
define("NO_MORE_TRANSFER", 31);			// 无新记录
define("ADD_FAILED", 15);				// 写入抓取记录失败
class ICBC {

    protected $cardNum;
    protected $acctNum;
    protected $dse_sessionId;
    protected $cookie;
    protected $fetchMode;
    protected $interval;
    protected $startTime;
    protected $endTime;
    protected $wget;
    protected $vmId;

    private $_currentPageId;
    private $_proxyIp;
    
    public function __construct($cardNum, $dse_sessionId, $cookie, $vmId)
    {
        $this->cardNum = $cardNum;
        $this->dse_sessionId = $dse_sessionId;
        $this->cookie = $cookie;
        $this->fetchMode = 'SOCKET';
        $this->vmId = $vmId;
        if (intval($iRepeat) <= 0){
        	$iRepeat = 3;
        }
        $this->wget->retry = $iRepeat;
        $this->wget = new wget2('gbk', 'utf-8');
        $this->wget->setHttpVersion('1.0')->setPort(443)->setCookie($this->cookie)->setConnectTimeOut(10);
    }
    
    public function getTransferList($pageNo = 0)
    {
        echo "start getTransferList \n";
        //$content = file_get_contents('log/1283498124_29087.txt');
        if (!$pageNo) {
            $content = self::getTransferListContent();
        }
        else {
            $content = self::getNextTransferListContent($pageNo);
        }

        //$pattern = '`收款卡\(账\)号(.*)</table>`Uims';
        $pattern = '`\<table\s+width="100%"\s+border="0"\s+cellspacing="1"\s+cellpadding="0"\s+bgcolor="#FFFFFF"\s+align="center">(.*)</table>`Uims';
        preg_match($pattern, $content, $match);
        $pattern = '`<tr>\s*<td[^>]*>(.*)</td>\s*<td[^>]*>(.*)</td>\s*<td[^>]*>\s*(\d[\d,]*\.\d+)(?:人民币|RMB)\s*</td>\s*<td[^>]+>.*</td>\s*<td[^>]+>(\d[\d,]*\.\d+)(?:元)?</td>\s*<td[^>]+>(.+)</td>\s*<td[^>]+><font\s+color="green"><a\s*href="javascript:on_sub\(\'thisform\',\'currserialNo\',\'(\w+)\'\)">.*</tr>`Uims';
        preg_match_all($pattern, $match[1], $matches);
        $result = array();
        foreach ($matches[1] as $k => $v) {
            $isOK = trim($matches[5][$k]);
            if ($isOK !== '支付成功,已经清算' && $isOK !== 'Payment Succeeded, Already Settled') {
                continue;
            }
            $name = trim($matches[2][$k]);
            $amount = trim(str_replace(',', '', $matches[3][$k]));
            $pay_date = str_replace(array('年', '月', '日'), array('-', '-',''), trim($matches[1][$k]));
            if ($pay_date < '2010-01-01 00:00:00') {
                return self::failed(DATE_ERROR, 'error date');
            }
            $fee = trim($matches[4][$k]);
            $serialNo = trim($matches[6][$k]);
            $result[] = array(
                'name' => $name,
                'amount' => $amount,
                'pay_date' => $pay_date,
                'fee' => $fee,
                'serialNo' => $serialNo,
            );
        }
        echo "end getTransferList \n";
        return $result;
    }

    public function getTransferListContent()
    {
        echo "start getTransferListContent \n";
        $referer = "https://mybank.icbc.com.cn/icbc/newperbank/includes/leftframejs.jsp?dse_sessionId={$this->dse_sessionId}";
        $url = "https://".$this->_proxyIp."/icbc/newperbank/includes/leftframejs.jsp?dse_sessionId={$this->dse_sessionId}";
        $responseBody = self::getPage($url, $referer);
        $referer = "https://mybank.icbc.com.cn/icbc/newperbank/includes/leftframejs.jsp?dse_sessionId={$this->dse_sessionId}";
        $url = "https://".$this->_proxyIp."/servlet/ICBCINBSCenterServlet?id=0602&dse_sessionId={$this->dse_sessionId}";
        $responseBody = self::getPage($url, $referer);
        if (!preg_match('`acctList\.add\(new\s+Account\(true,"'.$this->cardNum.'","`Uims', $responseBody, $match)) {
            $this->createDir(LOG_PATH);
            $this->putFileContent(LOG_PATH."/".ACCNUM_ERRO."_".time().".log", $responseBody);
            return self::failed(ACCNUM_ERRO, 'get acctNum faild');
            }
        $this->acctNum = $match[1];
        if (!preg_match('`<input\s+type="hidden"\s+name="dse_pageId"\s+value="(\d+)"\s*/>`Uims', $responseBody, $match)) {
            $this->createDir(LOG_PATH);
            $this->putFileContent(LOG_PATH."/".PAGEID_ERROR."_".time().".log", $responseBody);
            return self::failed(PAGEID_ERROR, 'get dse_pageId faild');
            }
        $this->_currentPageId = isset($match[1]) ? $match[1] : 5;
        //https://mybank.icbc.com.cn/servlet/ICBCINBSReqServlet
        $referer = "https://mybank.icbc.com.cn/servlet/ICBCINBSCenterServlet?id=0602&dse_sessionId={$this->dse_sessionId}";
        $url = "https://".$this->_proxyIp."/servlet/ICBCINBSReqServlet";
        $postData = "dse_sessionId={$this->dse_sessionId}&dse_applicationId=-1&dse_operationName=per_RemitExcQueryICBCHistoryOp&dse_pageId={$this->_currentPageId}".
            "&Tran_flag=0&cardNum={$this->cardNum}&acctNum={$this->acctNum}&qryMode=1&Begin_pos=-1"."&begDate=".date('Y-m-d', strtotime('-30 days'))."&endDate=".date('Y-m-d', time());
        $responseBody = self::getPage($url, $referer, $postData);
        $this->_currentPageId++;
        echo "end getTransferListContent \n";
        return $responseBody;
    }
    
    public function getNextTransferListContent($pageNo)
    {
        echo "start getNextTransferListContent \n";
        $referer = "https://mybank.icbc.com.cn/servlet/ICBCINBSReqServlet";
        $startPos = $pageNo * 10;
        $url = "https://".$this->_proxyIp."/servlet/ICBCINBSReqServlet?head_top_num=1&Tran_flag=3&Begin_pos={$startPos}&flag={$pageNo}&qryMode=1&".
            "begDate=".date('Y-m-d', strtotime('-30 days'))."&endDate=".date('Y-m-d', time())."&cardNum={$this->cardNum}&acctNum=&showNum=10&".
            "dse_sessionId={$this->dse_sessionId}&dse_applicationId=-1&dse_operationName=per_RemitExcQueryICBCHistoryOp&dse_pageId={$this->_currentPageId}";
        $responseBody = self::getPage($url, $referer);
        $this->_currentPageId++;
        echo "end getNextTransferListContent \n";
        return $responseBody;
    }

    public function getTransferDetail($serialNo)
    {
        echo "start getTransferDetail \n";
        $content = self::getTransferDetailContent($serialNo);
        // only for zh, change here for en support
        //$pattern = '`详细信息如下(.*)</table>`Uims';
        $pattern = '`\<TABLE\s+CELLSPACING=0\s+CELLPADDING=0\s+WIDTH="97%"\s+ALIGN=center\s+BORDER=0>(.*)</table>`Uims';
        preg_match($pattern, $content, $match);
        //$pattern = '`付款人.*<td[^>]+>\s*(\S+)\s*</td>.*收款人.*<td[^>]+>\s*(\S+)\s*</td>.*付款卡\(账\)号.*<td[^>]+>\s*(\S+)\s*</td>.*收款人卡\(账\)号.*<td[^>]+>\s*(\S+)\s*</td>.*付款地区.*<td[^>]+>\s*(\S+)\s*</td>.*收款地区.*<td[^>]+>\s*(\S+)\s*</td>.*付款金额.*<td[^>]+>\s*(\d[\d,]*\.\d+)\s+.*附言.*<td[^>]+>\s*(\S+)?\s*</td>.*'.'交易日期.*<td[^>]+>\s*(\S+)\s*</td>.*交易时间.*<td[^>]+>\s*(\S+)\s*</td>.*`Uims';
//        $pattern = '`(?:付款人|Payer).*<td[^>]+>\s*(.*)\s*</td>.*(?:收款人|Payee).*<td[^>]+>\s*(.*)\s*</td>.*(?:付款卡\(账\)号|Transfer\s+from).*<td[^>]+>\s*(\S+)?\s*</td>.*(?:收款人卡\(账\)号|Transfer\s+to).*<td[^>]+>\s*(\S+)\s*</td>.*(?:付款地区|From).*<td[^>]+>\s*(\S+)\s*</td>.*(?:收款地区|To).*<td[^>]+>\s*(\S+)\s*</td>.*(?:付款金额|Amount).*<td[^>]+>\s*(\d[\d,]*\.\d+)\s+.*(?:附言|Notes).*<td[^>]+>\s*(.*)\s*</td>.*'.'(?:交易日期|Transaction\s+Date).*<td[^>]+>\s*(\S+)\s*</td>.*(?:交易时间|Transaction\s+Time).*<td[^>]+>\s*(\S+)\s*</td>.*`Uims';
        $pattern = '`(?:付款人|Payer).*<td[^>]+>\s*(.*)\s*</td>.*(?:收款人|Payee).*<td[^>]+>\s*(.*)\s*</td>.*(?:付款卡\(账\)号|Transfer\s+from).*<td[^>]+>\s*(.*)\s*</td>.*(?:收款人卡\(账\)号|Transfer\s+to).*<td[^>]+>\s*(.*)\s*</td>.*(?:付款地区|From).*<td[^>]+>\s*(.*)\s*</td>.*(?:收款地区|To).*<td[^>]+>\s*(.*)\s*</td>.*(?:付款金额|Amount).*<td[^>]+>\s*(\d[\d,]*\.\d+)\s+.*(?:附言|Notes).*<td[^>]+>\s*(.*)\s*</td>.*'.'(?:交易日期|Transaction\s+Date).*<td[^>]+>\s*(.*)\s*</td>.*(?:交易时间|Transaction\s+Time).*<td[^>]+>\s*(.*)\s*</td>.*`Uims';
        $result = array();
        if (preg_match($pattern, $match[1], $match2)) {
            $pay_date = str_replace(array('年', '月', '日'), array('-', '-',''), trim($match2[9])).' '.
                str_replace(array('时', '分', '秒'), array(':', ':',''), trim($match2[10]));
            $result = array('name'=>trim($match2[1]), 'card_num'=>trim($match2[3]), 'area'=>trim($match2[5]), 'amount'=>trim(str_replace(',', '', $match2[7])), 'notes'=>trim($match2[8]),
                'accept_name'=>trim($match2[2]), 'accept_card_num'=>trim($match2[4]), 'accept_area'=>trim($match2[6]), 'pay_date'=>$pay_date);
        }
        else {
            $this->createDir(LOG_PATH);
            $this->putFileContent(LOG_PATH."/".DETAIL_ERROR."_".time().".log", $content);
            return self::failed(DETAIL_ERROR, 'Cant find detail info.');
            }
        echo "end getTransferDetail \n";
        return $result;
    }
    
    // dse_pageId?
    public function getTransferDetailContent($serialNo)
    {
        echo "start getTransferDetailContent \n";
        $referer = "https://mybank.icbc.com.cn/servlet/ICBCINBSReqServlet";
        $url = "https://".$this->_proxyIp."/servlet/ICBCINBSReqServlet?Tran_flag=1&currserialNo={$serialNo}&showNum=10&dse_sessionId={$this->dse_sessionId}&dse_applicationId=-1&dse_operationName=per_RemitExcQueryICBCHistoryOp&dse_pageId={$this->_currentPageId}";
        $responseBody = self::getPage($url, $referer);
        $this->_currentPageId++;
        echo "end getTransferDetailContent \n";
        return $responseBody;
    }

    /**
     * 创建目录
     *
     * @param string $sPath			// 路径
     */
    public function createDir( $sPath ){
    	if (!file_exists($sPath)) {
            mkdir($sPath, 0777, true);
            chdir($sPath, 0777);
        }
    }
    
    /**
     * 读取文件内容
     *
     * @param string $sPath			// 文件路径
     * @return source
     */
    public function getFileContent($sPath){
        if (!file_exists($sPath)){
            return 0;
        }
    	return file_get_contents($sPath);
    }
    
    /**
     * 向文件中写入内容
     *
     * @param string $sPath			// 文件路径
     * @param source $sContent		// 写入内容
     * @return mix
     */
    public function putFileContent($sPath, $sContent){
        $sDir = dirname($sPath);
        if (!file_exists($sDir)){
            mkdir($sDir,0777, true);
            chmod($sDir, 0777);
        }
    	return file_put_contents($sPath, $sContent);
    }

    private function getPage($url, $referer, $postData = '')
    {
        echo "start getPage \n";
        // for anti-spam
        sleep(rand(2, 5));
        $this->wget->setReferer($referer);
        $method = 'GET';
        if ($postData) {
            $this->wget->setPostData($postData)->setContentType('application/x-www-form-urlencoded');
            $method = 'POST';
        }

        echo "GET $url...\n";
        $t1 = microtime(true);
        echo "start wget->getContents \n";
        if (!$flag = $this->wget->getContents($this->fetchMode, $method, $url)) {
			// 首先去取重试次数
            $iCount = $this->getFileContent(LOG_PATH . "/" . $this->vmId . "-" . $this->cardNum);
            $iTempCount = intval(trim($iCount));
            if ($iTempCount === RETRY_TIMES){
            	$iTemp = 0;
            } else {
            	$iTemp = $iTempCount + 1;
            }
            $this->putFileContent(LOG_PATH . "/" . $this->vmId . "-" . $this->cardNum, $iTemp);
            // 重试3次后将状态置为11
            if ($iTempCount >= RETRY_TIMES){ // 出错状态
            	$iTempErrno = GET_ERROR;
            } else { // 重试状态
            	$iTempErrno = RETRY_STATUS;
            }
            return self::failed($iTempErrno, "errno:".$this->wget->errno().",errstr:".$this->wget->errstr());
        } else{
            // 先将重试抓取的文件归零
            $iCount = $this->getFileContent(LOG_PATH . "/" . $this->vmId . "-" . $this->cardNum);
            if (intval($iCount) !== 0){
                $this->putFileContent(LOG_PATH . "/" . $this->vmId . "-" . $this->cardNum, 0);
            }
        }
        echo "end wget->getContents \n";
        $t2 = microtime(true);

        $requestHeader = trim($this->wget->getRequestHeaderStream(), "\r\n");
        $responseHeader = $this->wget->getResponseHeaderStream();
        $responseBody = $this->wget->getResponseBody();
        echo "info:".number_format(strlen($responseBody))." Bytes, ".round($t2 - $t1, 3)."s\n";
        //echo "$responseBody\n";
        //file_put_contents("log/".time()."_".strlen($responseBody).".txt", $responseBody);
        if (strlen($responseBody) < 1000) {
            $this->putFileContent(LOG_PATH . "/expired-" . $this->vmId . "-" . $this->cardNum . "-" . date("Y-m-d H:i:s", time()), $responseBody);
            ICBC_log($this->cardNum, 2);
            return self::failed(SESSION_EXPIRED, 'The session has expired!');
        }
        echo "end getPage \n";
        return $responseBody;
    }
    
    public function setProxy($ip)
    {
        $this->_proxyIp = $ip;
    }

    static public function failed($errno, $errstr)
    {
        switch (substr($errno, 0, 1))
        {
            case 1:// error
                throw new exception("$errstr", $errno);
                break;
            case 2:
                // warning
                throw new exception("$errstr", $errno);
                break;
            case 3:
                //return array('errno' => $errno, 'errstr' => $errstr);
                throw new exception("$errstr", $errno);
                break;
            default:
                throw new exception("unknown error", 10);
                break;
        }
    }
}

function unlock()
{
    global $lockFile, $unlinkFlag;
    if ($unlinkFlag) {
        @unlink($lockFile);
    }
    return true;
}

// entry
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);
$params = parse_cmdline_argv_to_var();
$vm_id = $params['vm_id'];
if (empty($vm_id)) {
    echo 'Please specify vm_id';
    die();
}

if (!getConfigs('maildeposit_turnauto')) {
    die();
}

$t1 = time();
echo "\nStart at ".date('Y-m-d H:i:s')."...\n";
$lockFile = dirname(__FILE__)."/icbc_$vm_id.lock";
$unlinkFlag = 1;
if (file_exists($lockFile)) {
//    $stat = stat($lockFile);
//    // 防止进程死锁
//    if ((time() - $stat['mtime']) > 120) {
//        @unlink($lockFile);
//    }
//    else {
//        $unlinkFlag = 0;
//        die("file lock has exists!\n");
//    }
    $unlinkFlag = 0;
    die("file lock has exists!\n");
}
touch($lockFile);
register_shutdown_function('unlock');

if (!$vmInfo = getVmInfo($vm_id, 1)) {
    echo 'Non-exists vm!';
    die();
}
echo "Get VmInfo!\n";

if ($vmInfo['errno'] > 0 && $vmInfo['errno'] != NO_MORE_TRANSFER && $vmInfo['errno'] != RETRY_STATUS) {
    echo 'The logon info is expired! (' . $vmInfo['errno'] . ')';
    die();
}

// for debug
//$vmInfo = array(
//    'card_num' => '6222021001047824039',  //6222032010000924154
//    'dse_session_id' => 'utztxT24IN8ZmDNWNH1X4IY',
//    'cookie' => 'JSESSIONID=0000utztxT24IN8ZmDNWNH1X4IY:-1',
//    'ip' => 'mybank.icbc.com.cn',
//    );
// 随机延迟
sleep(rand(10,30));


//$icbc->getTransferDetail('HQH000000000001275433425');die();
//$startTime = strtotime($vmInfo['start_time']);
//$endTime = strtotime($vmInfo['end_time']);
$startTime = getConfigs('maildeposit_starttime');
$endTime = getConfigs('maildeposit_stoptime');

// 获取充值延迟周期
$sCycle = getConfigs('maildeposit_cycletime');
if (!empty($sCycle)){
	$aCycle = explode("|", $sCycle);
	if (in_array(date("l"), $aCycle)){
		$startTime = date("H:i",  strtotime($startTime) + intval(getConfigs('maildeposit_delaytime')) * 60);
	}
}

if (!$delayOffTime = getConfigs('maildeposit_eachtime')) {
    $delayOffTime = 30;
}
$endTime = date('H:i',strtotime($endTime) + $delayOffTime * 60);
$curTime = date('H:i');
// 04:00-23:00
if ($endTime > $startTime) {
    if ($curTime < $startTime || $curTime > $endTime) {
        die();
    }
}
else {  // 04:00-02:00(1,3) 22:00-10:00
    if ($curTime < $startTime && $curTime > $endTime) {
        die();
    }
}

if (!$icbc_scan_interval = getConfigs('ICBC_SCAN_INTERVAL')) {
    $icbc_scan_interval = 40;
}

$icbc = new ICBC($vmInfo['card_num'], $vmInfo['dse_session_id'], $vmInfo['cookie'], $vm_id, REPEAT_TIMES);
$icbc->setProxy($vmInfo['ip']);

try {
    if (!$transferList = $icbc->getTransferList()) {
        ICBC::failed(NO_MORE_TRANSFER, 'No any transfers');
    }

    if ($lastTransfer = ICBC_GetLastTransfer($vmInfo['card_num'])) {
        echo "Get ccb info\n";
        for ($j = 0; $j < count($transferList); $j++) {
            if ($transferList[$j]['name'] == $lastTransfer['name'] && $transferList[$j]['amount'] == $lastTransfer['amount'] && $transferList[$j]['pay_date'] == $lastTransfer['pay_date']) {
                break;
            }
        }

        if ($j < count($transferList)) {
            $transferList = array_slice($transferList, 0, $j);
        }
        else {
            $maxPage = getConfigs('ICBC_MAX_CARE_PAGE_NUMBER');
            if (!$maxPage) {
                $maxPage = 2;
            }
            for ($k = 1; $k < $maxPage; $k++) {
                $tmp = $icbc->getTransferList($k);
                for ($m = 0; $m < count($tmp); $m++) {
                    if ($tmp[$m]['name'] == $lastTransfer['name'] && $tmp[$m]['amount'] == $lastTransfer['amount'] && $tmp[$m]['pay_date'] == $lastTransfer['pay_date']) {
                        break;
                    }
                }
                $tmp = array_slice($tmp, 0, $m);
                $transferList = array_merge($transferList, $tmp);
                if ($m < count($tmp)) {
                    break;
                }
            }
        }
    }

    $transferList = array_reverse($transferList);
    $minInterval = getConfigs('ICBC_MIN_TRANSFER_INTERVAL');
    $pattern = "/(\s+)/i";
    $sName = "";
    $sAcceptName = "";
    $iSkip = 0;// 跳过的条数
    $iError = 0;
    foreach ($transferList as $k => $v) {
        // addons: check time
        if (!$minInterval) {
            $minInterval = 10;
        }
        if (time() - strtotime($v['pay_date']) < $minInterval) {
            unset($transferList[$k]);
            continue;
        }

        echo "Getting {$v['serialNo']} ...";
        $detail = $icbc->getTransferDetail($v['serialNo']);
        echo "result: {$detail['name']}, {$detail['card_num']}, {$detail['amount']}, {$v['fee']}, {$detail['notes']}, {$detail['pay_date']}\n";
        $sName = preg_replace($pattern,"", $detail['name']);
        $sAcceptName = preg_replace($pattern,"", $detail['accept_name']);
//        if (!ICBC_AddTransfer($sName, $detail['card_num'], $detail['area'], $detail['amount'], $v['fee'], $detail['notes'], $sAcceptName,
//                $detail['accept_card_num'], $detail['accept_area'], $detail['pay_date'], 0, 0, date('Y-m-d H:i:s'))) {
//            ICBC::failed(ADD_FAILED, 'Add transfer failed!');
//        }
        $iResult = 0;
        $iResult = ICBC_AddTransfer($sName, $detail['card_num'], $detail['area'], $detail['amount'], $v['fee'], $detail['notes'], $sAcceptName,
                $detail['accept_card_num'], $detail['accept_area'], $detail['pay_date'], 0, 0, date('Y-m-d H:i:s'));
        if ($iResult === -1){ // 自动录入未重复，跳过
            $iSkip++;
        } else if ($iResult === 0){ // 自动录入重复，停止
            $iError++;
            continue;
        }
        $sName = "";
        $sAcceptName = "";
    }
    
    if ($transferList) {
        echo "OK, total ".count($transferList) - $iSkip - $iError." records has been added!\n";
        if ($iSkip > 0){
            echo "total ".$iSkip." records has been skiped!\n";
        }
    }
    else {
        echo "No more new transfers.\n";
    }
}
catch (Exception $e) {
    switch(substr($e->getCode(), 0, 1)) {
        case 1:
            echo "Error exception(errcode=".$e->getCode()."):".$e->getMessage(). " [".date('Y-m-d H:i:s')."]";
            break;
        case 2:
            echo "Warning exception(errcode=".$e->getCode()."):".$e->getMessage(). " [".date('Y-m-d H:i:s')."]";
            break;
        case 3:
            echo "Notice exception(errcode=".$e->getCode()."):".$e->getMessage(). " [".date('Y-m-d H:i:s')."]";
            break;
        default:
            echo "Unknown exception(errcode=".$e->getCode()."):".$e->getMessage(). " [".date('Y-m-d H:i:s')."]";
            break;
    }
    updateErrno($vmInfo['vm_id'], $e->getCode());
}

$t3 = time();
echo "Total perform ".($i+1)." times, start at ".date('Y-m-d H:i:s', intval($t1)).", end at ".date('Y-m-d H:i:s', $t3).", total waste ".($t3-$t1)." s\n\n";

?>