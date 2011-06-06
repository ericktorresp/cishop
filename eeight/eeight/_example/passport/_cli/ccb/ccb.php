<?php
// 注意：
// 1.不一定需要http 1.1和Keep-Alive
// 2.23位的session有时候会发生异常


require_once "mod.php";
require_once "wget2.class.php";
include_once 'definebank.inc.php';

define("PARAM_WRONG",               5);                 //　参数错误
define("GET_CONTENT_FAILED",        15);                //　正则匹配失败
define("GET_ACCOUNT_FAILED",        17);                //　获取账号失败
define("GET_ASTR_FAILED",           19);                //　获取下一页页码失败
define("ANALYSIS_ASTR_FAILED",      13);                //　分析下一页页码失败
define("UPDATE_LAST_PAGE_FAILED",   10);                //　换卡后，将抓取最后一页的页码置为1失败
define("LOGIN_EXPIRED",             12);                //　cookie过期
define("NO_MORE_TRANSFER",          31);                //　没有数据
define("UPDATE_COOKIE_FAILED",      33);                //　写入cookie失败
define("HIDDEN_LENGTH",             5);                 // 卡号隐藏5位
define("LEAVE_LENGTH",              6);                 // 隐藏卡号后还剩6位
define("GET_ERROR",                 11);                // 抓取失败
define("RETRY_STATUS",              16);                // 抓取重试
define("ANALYSIS_ERROR",            14);                // 分析抓取数据错误
define("RETRY_ANALYSIS_STATUS",     18);                // 分析抓取数据重试状态
define("RETRY_TIMES",               3);                 // 抓取重试次数
define("RETRY_ANALYSIS_TIMES",      3);                 // 重试次数
define("GET_BRANCHID_FAILED",       21);                // 获取支行id失败
define("ACCOUNT_ILLEGAL",           41);                // 无可操作的账号，或账户信息非法(币种或账户类型错误)
define("LONG_TIME_NO_ACTION",       42);                //您已较长时间未进行操作，为保护您的资金安全，重新登录。
define("SYSTEM_BUSY",               43);                // 系统繁忙，请重新登录或拨打95533咨询
define("INTERVAL_SHORT_TIME",       51);                // 您连续提交交易时间间隔太短，请稍后提交。
define("HAVE_NEW_NOTICE",           50);                // 建行有提示类型
define("NO_MORE_TRANSFER_CODE",     "0010ZXXE3360");    // 没有数据(建行信息码)
define("INTERVAL_SHORT_TIME_CODE",  "0130Z1108913");    // 抓取间隔时间太短(建行信息码)
define("ACCOUNT_ILLEGAL_CODE",      "0130Z1108902");    // 无可操作账户(建行信息码)
define("LONG_TIME_NO_ACTION_CODE",  "0130Z1108912");    // 长时间无操作(建行信息码)
define("SYSTEM_BUSY_CODE",          "0130Z1108007");    // 系统繁忙(建行信息码)
define("FERERER_TXCODE",            "310201");          // 建行TXCODE,referer
define("POST_TXCODE",               "310200");          // 建行TXCODE,postdata
define("ACC_SIGN",                  "E0000000000010");  // 建行ACC_SIGN
define("PDT_CODE",                  "0101");            // 建行PDT_CODE
define("ACCTYPE2",                  "12");              // 建行ACCTYPE2
define("LUGANGTONG",                "0");               // 建行LUGANGTONG
define("LOG_PATH", dirname(__FILE__)."/../../_tmp/logs/ccb"); // 日志路径
define("CODE_PATH", dirname(__FILE__)."/../../_tmp/codes/ccb"); // 记录返回信息码路径

define("JOIN_STR", "+");				// 页面搜索串的连接符
define("REGULAR_CODE", 243);			// 页面搜索串的固定码
class CCB {

	/**
	 * 卡号
	 *
	 * @var string
	 */
    protected $cardNum;
    
    /**
     * 账户名
     *
     * @var string
     */
    protected $acctNum;
    
    /**
     * sessionid
     *
     * @var string
     */
    protected $SKEY;
    
    /**
     * cookie
     *
     * @var string
     */
    protected $cookie;
    
    /**
     * 获取方式
     *
     * @var string
     */
    protected $fetchMode;
    
    /**
     * 抓取间隔时间
     *
     * @var int
     */
    protected $interval;
    
    /**
     * 抓取记录起始时间
     *
     * @var date
     */
    protected $startTime;
    
    /**
     * 抓取记录截止时间
     *
     * @var date
     */
    protected $endTime;
    
    /**
     * 抓取类
     *
     * @var object
     */
    protected $wget;
    
    /**
     * 证件号/昵称
     *
     * @var string
     */
    protected $LoginName;
    
    /**
     * 卡号隐藏位数
     *
     * @var int
     */
    protected $HiddenLength = 5;
    
    /**
     * 卡号隐藏后剩余的位数
     *
     * @var int
     */
    protected $LastLength = 4;

    /**
     * vpnip
     *
     * @var string
     */
    private $_proxyIp;
    protected $vmId;
    
    private $aAllow = array("转帐存入");
    
    private $aNotice = array(
        NO_MORE_TRANSFER_CODE       => NO_MORE_TRANSFER,      // 没有新记录
        INTERVAL_SHORT_TIME_CODE    => INTERVAL_SHORT_TIME,   // 间隔时间太短
        ACCOUNT_ILLEGAL_CODE        => ACCOUNT_ILLEGAL,       // 无可操作账户
        LONG_TIME_NO_ACTION_CODE    => LONG_TIME_NO_ACTION,   // 长时间无操作
        SYSTEM_BUSY_CODE            => SYSTEM_BUSY            // 系统繁忙
        );
    
    private $aNeedReturn = array(
        NO_MORE_TRANSFER,
        ACCOUNT_ILLEGAL,
        LONG_TIME_NO_ACTION,
        SYSTEM_BUSY
    );
    
    /**
     * 文件搜索串
     *
     * @var string
     */
    protected $FileSearchStr;
    
    /**
     * 响应的头信息
     *
     * @var string
     */
    protected $ResponseHeader;
    
    /**
     * 当前页码
     *
     * @var int
     */
    protected $CurrentPage;
    
    /**
     * 所有页面数
     *
     * @var int
     */
    protected $AllPages;
    
    /**
     * 上一次抓取的页面md5码
     *
     * @var string
     */
    protected $LastPageMd5;
    
    /**
     * 
     * 银行id
     * @var int
     */
    protected $BankId;
    
    /**
     * 建行抓取页面中的A_STR
     */
    protected $ASTR;
    
    /**
     * 支行id
     * @var int 
     */
    protected $BranchId;
    
    public function __construct($cardNum, $banchId, $dse_sessionId, $cookie, $login_name, $vmId, $iRepeat, $iBankId)
    {
        $this->cardNum = trim($cardNum);
        $this->LoginName = trim($login_name);
        $this->SKEY = trim($dse_sessionId);
//        $this->cookie = $cookie;
        $this->fetchMode = 'SOCKET';
        $this->vmId = $vmId;
        $this->startTime = date("Ymd", strtotime("-1 day"));
        $this->endTime = date("Ymd", time());
        $this->wget = new wget2('gbk', 'utf-8');
        $this->BankId = intval($iBankId);
        $this->BranchId = intval($banchId);
        if (intval($iRepeat) <= 0){
        	$iRepeat = 3;
        }
        $this->wget->retry = $iRepeat;
//        $this->wget->setHttpVersion('1.0')->setPort(443)->setCookie($this->cookie)->setConnectTimeOut(30);
    }
    
    
    
    
    /**
     * 抓取建行转账信息
     *
     * @return array
    	[1] => Array
        (
            [0] => 2010/11/08
            [1] => 440270001省分行营运管理部
            [2] => &nbsp;
            [3] => FormatAmt('9.80')
            [4] => FormatAmt('491.80')
            [5] => 6227003324620120000
            [6] => 袁金林
            [7] => 人民币
            [8] => 转帐存入
        )
    */
    public function getAllTransferList($retry = false, $retryPage = 0){
    	// 数据检查
        
    	if (empty($this->cardNum) || empty($this->LoginName) || empty($this->SKEY) || empty($this->startTime) || empty($this->endTime)){
    		return PARAM_WRONG;
    	}
    	$aResult = array();
        if ($retry === false){
            // 设置下一页的cookie值
            $aVMInfo = getVmInfo($this->vmId);
            if (empty($aVMInfo)){
                return $aResult;
            }
            $this->LastPageMd5 = $aVMInfo['get_content_key'];
            
            // 检查银行卡验证串是否相同，不相同则表示已换卡
            $sTemp = md5($this->vmId . $this->cardNum);
            $iPage = 1;
            if ($sTemp === $aVMInfo['card_info_key']){
                $iPage = $aVMInfo['last_page'];
            } else {
                updateCardInfoKey($this->vmId, $sTemp);
                if (updatePage($this->vmId) === false ){
                    return UPDATE_LAST_PAGE_FAILED;
                }
            }
        }
        
        
//        if ($retry === false){
        if (intval($iPage) !== 1){ // 抓取第一页，以便获取页码中的页码标志串
          $mWillDrop = $this->getOnePageTransfer(1);
          if (!is_array($mWillDrop) && intval($mWillDrop) > 0 && intval($mWillDrop) !== RETRY_STATUS && intval($mWillDrop) !== RETRY_ANALYSIS_STATUS){
            updateErrno($this->vmId, $mWillDrop);
            logs($this->cardNum, 2);
            die("1956");
          }
        } else {
            $iPage = 1;
            $this->CurrentPage = 1;
            $this->AllPages = 1;
        }
//        } else {
//            if (intval($retryPage) <= 0){
//                die("page error!\n");
//            }
//            $iPage = $retryPage;
//        }
        
        if ($iPage > $this->AllPages){
            $iPage = $this->AllPages; // 再抓取一次最后一页
        }
		
//        $mResult = $this->getOnePageTransfer($iPage);
//    	if (intval($mResult) === GET_ERROR || intval($mResult) === RETRY_STATUS){ // 抓取出错或重试状态
//    		updateErrno($this->vmId, $mResult);
//    		return $mResult;
//    	}
//    	if (intval($mResult) === RETRY_ANALYSIS_STATUS || intval($mResult) === ANALYSIS_ERROR){ // 分析抓取重试状态
//    		$this->getAllTransferList(true, $iPage);
//    	}
//    	// 如果出错
//    	if (!is_array($mResult) && intval($mResult) > 0){
//    		updateErrno($this->vmId, $mResult);
//    		return $mResult;
//    	}
//    	// 数据为空
//    	if (is_array($mResult) && empty($mResult)){
//    		updateErrno($this->vmId, NO_MORE_TRANSFER);
//    		return NO_MORE_TRANSFER;
//    	}
//    	if ($mResult !== -1){ // 如果验证串不相同，则赋值
//    		foreach ($mResult as $k => $v){
//    			// 首先获取指定摘要的数据
//				if (in_array($v[8], $this->aAllow)){
//					// 将日期格式转换
//					$sData = str_replace('/', '-', $v[0]);
//					
//					$aTemp = array();
//					$aTemp[$k]['pay_date'] = $sData;
//					$aTemp[$k]['address'] = $v[1];
//					
//					// 提取金额与账户余额
//					$pattern = "/[^\d.]/";
//					$aTemp[$k]['amount'] = preg_replace($pattern, "", $v[3]);
//					$aTemp[$k]['balance'] = preg_replace($pattern, "", $v[4]);
//					
//					$aTemp[$k]['full_account'] = $v[5];
//					$aTemp[$k]['hidden_account'] = $this->hidAccount($v[5], 1);
//					$aTemp[$k]['acc_name'] = $v[6];
//					$aTemp[$k]['currency'] = $v[7];
//					$aTemp[$k]['summary'] = $v[8];
//					$aTemp[$k]['accept_account'] = $v['accept_account'];
//					
//					//　获取验证串并在数据库中检查是否已存在
//					$aTemp[$k]['encode_key'] = $this->getKey($aTemp[$k]['pay_date'], $aTemp[$k]['amount'], $aTemp[$k]['balance'], $aTemp[$k]['hidden_account'], $aTemp[$k]['acc_name']);
//				}
//				if (!empty($aTemp)){
//					$aResult = array_merge($aResult, $aTemp);
//				}
//                unset($aTemp);
//                if (intval($iPage) >= intval($this->AllPages)){
//                    updateLastPage($this->vmId, $iPage); // 写入抓取的最后一页页码
//                    $sResult = $this->transferInsert($aResult);
//                    return $sResult;
//                }
//    		}
//    	}
        
        
        // 开始循环抓取
    	if (intval($this->CurrentPage) > 0 && intval($this->AllPages) >0 && intval($this->CurrentPage) <= intval($this->AllPages)){
    		for($i=$iPage; $i<= $this->AllPages;$i++){
    			$mInfo = $this->getOnePageTransfer($i);
    			if (intval($mInfo) > 0 && !is_array($mInfo)){
                    $sResult = "";
    				if (is_array($aResult) && !empty($aResult)){
    					$sResult = $this->transferInsert($aResult);
    				};
    				updateErrno($this->vmId, $mInfo);
                    if ($sResult == ""){
                        // 将页码退一，然后写入数据库中
                        updateLastPage($this->vmId, $i - 1); // 写入抓取的最后一页页码
                        return $mInfo;
                    } else {
                        // 检查写入成功的记录条数，如果有大于等于一条的记录写入成功了，则写入最后抓取的页码
                        $aTemp = explode("#", $sResult);
                        if (intval($aTemp[0]) > 0){
                            updateLastPage($this->vmId, $i - 1); // 写入抓取的最后一页页码
                        }
                        unset($aTemp);
                        return $sResult . "#" . $mInfo;
                    }
    			}
    			if ($mInfo === -1){ // 如果验证串相同，则执行下一循环
    				continue;
    			}
    			if (is_array($mInfo) && !empty($mInfo)){
    				foreach ($mInfo as $k => $v){
    					// 首先获取指定摘要的数据
	    				if (!in_array($v[8], $this->aAllow)){
                            continue;
//	    					// 如果最后一页也成功了，则直接写入后返回
//		    				if ($i === intval($this->AllPages)){
//		    					if ($sTemp !== $aVMInfo['card_info_key']){ // 如果银行卡验证串不相同，则写入新验证串
//		    						updateCardInfoKey($this->vmId, $sTemp);
//		    					}
//		    					
//		    					$sTemp = $this->transferInsert($aResult);
//                                if ($sTemp !== false){
//                                    updateLastPage($this->vmId, $i); // 写入抓取的最后一页页码
//                                }
//                                return $sTemp;
//		    				} else {
//		    					continue;
//		    				}
	    				}
	    				
	    				// 将日期格式转换
	    				$sData = str_replace('/', '-', $v[0]);
	    				
	    				$aTemp = array();
	    				$aTemp[$k]['pay_date'] = $sData;
	    				$aTemp[$k]['address'] = $v[1];
	    				
	    				// 提取金额与账户余额
	    				$pattern = "/[^\d.]/";
	    				$aTemp[$k]['amount'] = preg_replace($pattern, "", $v[3]);
	    				$aTemp[$k]['balance'] = preg_replace($pattern, "", $v[4]);
	    				
	    				$aTemp[$k]['full_account'] = $v[5];
	    				$aTemp[$k]['hidden_account'] = $this->hidAccount($v[5], 1);
	    				$aTemp[$k]['acc_name'] = $v[6];
	    				$aTemp[$k]['currency'] = $v[7];
	    				$aTemp[$k]['summary'] = $v[8];
	    				$aTemp[$k]['accept_account'] = $v['accept_account'];
	    				
	    				//　获取验证串并在数据库中检查是否已存在
	    				$aTemp[$k]['encode_key'] = $this->getKey($aTemp[$k]['pay_date'], $aTemp[$k]['amount'], $aTemp[$k]['balance'], $aTemp[$k]['hidden_account'], $aTemp[$k]['acc_name'], $v['accept_account']);
	    				
	    				// 分析数据不为空，则组合抓取信息
	    				if (!empty($aTemp)){
	    					$aResult = array_merge($aResult, $aTemp);
	    				}
    				}
    				
    				// 如果最后一页也成功了，则直接写入后返回
    				if (intval($i) === intval($this->AllPages)){
    					if ($sTemp !== $aVMInfo['card_info_key']){ // 如果银行卡验证串不相同，则写入新验证串
    						updateCardInfoKey($this->vmId, $sTemp);
    					}
                        $sTemp = $this->transferInsert($aResult, $i);
                        $aTemp = explode("#", $sTemp);
                        if (intval($aTemp[0]) > 0){
                            updateLastPage($this->vmId, $i); // 写入抓取的最后一页页码
                        }
                        unset($aTemp);
    					return $sTemp;
    				}
    			} else {
    				if ($sTemp !== $aVMInfo['card_info_key']){ // 如果银行卡验证串不相同，则写入新验证串
						updateCardInfoKey($this->vmId, $sTemp);
					}
    				$sResult = $this->transferInsert($aResult);
                    $aTemp = explode("#", $sResult);
                    if (intval($aTemp[0]) > 1){
                        // 写入数据，然后返回错误码
                        updateLastPage($this->vmId, $i); // 写入抓取的最后一页页码
                    }
                    unset($aTemp);
    				return $sResult . "#" . $mInfo;
    			}
    		}
    	} else {
            if (!empty($aResult)){
                $sResult = $this->transferInsert($aResult);
                $aTemp = explode("#", $sResult);
                if (intval($aTemp[0]) > 1){
                    updateLastPage($this->vmId, $this->CurrentPage - 1); // 写入抓取的最后一页页码
                }
                unset($aTemp);
    			return $sResult;
            } else {
                updateErrno($this->vmId, NO_MORE_TRANSFER);
                updateLastPage($this->vmId, $this->CurrentPage - 1); // 写入抓取的最后一页页码
    			return NO_MORE_TRANSFER;
            }
        }
    }
    

    
    
    
    /**
     * 写入抓取数据
     * 
     * @param 		array		$aData			// 抓取数据
     *
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-12-06
     * 
     * @return 		string		//返回成功与失败的记录条数
     * 
     */
    private function transferInsert($aData, $iLastPage = 1){
//        print_r($aData);die;
    	if (intval($this->BankId) <= 0){
    		die("The BankId is wrong!\n");
    	}
    	// 将数组再正序排列
		$aTransfer = array_reverse($aData);
		
		$iSuccess = 0; // 成功写入记录条数
		$iFailed = 0; // 失败的记录条数
		foreach ($aTransfer as $k => $v){
			// 首先检查收款账号是否相同
			if ($v['accept_account'] !== $this->cardNum || empty($this->cardNum)){
				die("The company account is wrong");
			}
			// 首先检查此记录是否已写入数据库中，如果写入，此次操作立即停止
			$v['encode_key'] = isset($v['encode_key']) ? $v['encode_key'] : "";
			if (empty($v['encode_key'])){
				$iFailed++;
				continue;
			}
			if (isExistTransfer($v['encode_key'], $this->cardNum) === false){
                updateLastPage($this->vmId, $iLastPage); // 写入抓取的最后一页页码
				die("OK, total " . $iSuccess . " records has been added!\n");
			}
			
			$vmInfo = getVmInfo($this->vmId);
			if (empty($vmInfo)){
				die("get vmInfo failed!\n");
			}
			
			// 写入数据
			$aData = array();
			$aData['pay_date'] = $v['pay_date'];
			$aData['area'] = $v['address'];
			$aData['amount'] = $v['amount'];
			$aData['balance'] = $v['balance'];
			$aData['full_account'] = $v['full_account'];
			$aData['hidden_account'] = $v['hidden_account'];
			$aData['acc_name'] = $v['acc_name'];
			$aData['currency'] = $v['currency'];
			$aData['summary'] = $v['summary'];
			$aData['encode_key'] = $v['encode_key'];
			$aData['nickname'] = $vmInfo['nickname'];
			$aData['accept_name'] = $vmInfo['accept_name'];
			$aData['accept_card'] = $this->cardNum;
			
			$mResult = AddTransfer($aData, $this->BankId);
			if ($mResult > 0){
				$iSuccess++;
			} else {
                $iFailed++;
			}
		}
		return $iSuccess . "#" . $iFailed;
    }
    
    
    /**
     * 分析响应的头信息并写入数据库
     *
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-11-26
     * 
     */
    private function analysisAndInsert(){
    	if (empty($this->ResponseHeader))			return false;
    	$aMatches = array();
    	$sPattern = "!.*Set-Cookie:(.*)\n.*!Uis";
    	preg_match_all($sPattern, $this->ResponseHeader, $aMatches,PREG_SET_ORDER);
    	$aMatches2 = array();
    	foreach($aMatches as $aMatch){
		    array_shift($aMatch);
		    $aTmp = array_map('strip_tags',array_map('trim',$aMatch));
		    $aMatches2[] = $aTmp;
		}
		
		if (empty($aMatches2))						return false;
		
		$sNextCooie = "";
		$sSign = "; ";
		$sNextCooie .= trim($aMatches2[0][0]) . $sSign;
		$aTemp = explode(";", $aMatches2[1][0]);
		if (empty($aTemp))				return false;
		$sNextCooie .= trim($aTemp[0]) . $sSign;
		unset($aTemp);
		/*$aTemp = explode(";", $aMatches2[3][0]);
		if (empty($aTemp))				return false;
		$sNextCooie .= trim($aTemp[0]) . $sSign;
		unset($aTemp);*/
		$aTemp = explode(";", $aMatches2[2][0]);
		if (empty($aTemp))				return false;
		$sNextCooie .= trim($aTemp[0]) . $sSign;
		unset($aTemp);
		/*$aTemp = explode(";", $aMatches2[4][0]);
		if (empty($aTemp))				return false;
		$sNextCooie .= trim($aTemp[0]) . $sSign;
		unset($aTemp);*/
		
		if (empty($sNextCooie))			return false;		
		return updateCookie($sNextCooie, $this->vmId);
    }
    
    
    
    // 获取一页的转账记录
    public function getOnePageTransfer( $iPage = 1 ){
    	if ($iPage <= 0){
            echo "page:" . $iPage,"\n";
    		return PARAM_WRONG;
    	}
        if ($iPage > 1){
            sleep(25);
        }
        // 设置下一页的cookie值
		$aVMInfo = getVmInfo($this->vmId);
		if (empty($aVMInfo))	return $aResult;
		$this->wget->setHttpVersion('1.0')->setPort(443)->setCookie($aVMInfo['cookie'])->setConnectTimeOut(20);
    	echo "page:" . $iPage,"\n";
    	// 组合抓取所需的信息
    	$referer="https://ibsbjstar.ccb.com.cn/app/B2CMainB1L1?CCB_IBSVersion=V5&SERVLET_NAME=B2CMainB1L1&SKEY={$this->SKEY}&USERID={$this->LoginName}&BRANCHID={$this->BranchId}&TXCODE=" . FERERER_TXCODE . "&PAGE=1&ACC_NO={$this->cardNum}&ACC_SIGN=" . ACC_SIGN . "&PDT_CODE=" . PDT_CODE . "&STR_USERID={$this->LoginName}&SEND_USERID=&TXTYPE=1";
    	$url = "https://" . $this->_proxyIp . "/app/B2CMainB1L1?CCB_IBSVersion=V5&SERVLET_NAME=B2CMainB1L1";
//        $url = "https://ibsbjstar.ccb.com.cn/app/B2CMainB1L1?CCB_IBSVersion=V5&SERVLET_NAME=B2CMainB1L1";
    	$iCurrentPage = 0;
    	if (intval($iPage) === 1){
    		$iCurrentPage = 1;
    	} else {
    		$iCurrentPage = $iPage - 1;
    	}
    	
    	// 获取每页标志串
        if (intval($iPage) === 1){
            $sResult = "";
        } else {
            $sResult = $this->_getFileSearchStr($this->ASTR);
        }
    	$postData = "ACC_NO={$this->cardNum}&START_DATE={$this->startTime}&END_DATE={$this->endTime}&TXCODE=" . POST_TXCODE . "&SKEY={$this->SKEY}&USERID={$this->LoginName}&STR_USERID={$this->LoginName}&BRANCHID={$this->BranchId}&PAGE={$iPage}&CURRENT_PAGE={$iCurrentPage}&PDT_CODE=" . PDT_CODE . "&A_STR={$sResult}&v_acc={$this->cardNum}&ACCTYPE2=" . ACCTYPE2 . "&LUGANGTONG=" . LUGANGTONG . "&v_acc2={$this->cardNum}&ACC_SIGN=" . ACC_SIGN . "&FILESEARCHSTR={$this->FileSearchStr}&l_acc_no={$this->cardNum}&l_acc_no_u={$this->cardNum}&l_branchcode={$this->BranchId}&l_acc_sign={$this->cardNum}&l_userid={$this->LoginName}";
        
    	// 抓取
    	$mWholePage = self::getPage($url, $referer, $postData);
    	if (intval($mWholePage) === GET_ERROR || intval($mWholePage) === RETRY_STATUS){ // 出错或重试状态
    		return $mWholePage;
    	}
    	// 将抓取来的信息进行分析，提取有效的数据
    	return $this->analysis($mWholePage);
    }
    
    
    /**
     * 根据页码返回每页标志串
     *
     * @param int $sASTR		// A_STR
     */
    private function _getFileSearchStr($sASTR){
//    	if (intval($iPage) <= 0)			return "";
//    	if (intval($iPage) === 1){
//    		return "";
//    	} else {
//    		return $this->FileSearchStr . urlencode(JOIN_STR . REGULAR_CODE . JOIN_STR . intval(($iPage - 2 ) * 10 + 1) . JOIN_STR . intval(($iPage - 1) * 10) . JOIN_STR);
//    	}
        if (empty($sASTR))			return "";
        return urlencode($sASTR);
    }
    
    
    
    /**
     * 查询是否含有有次记录
     * 
     * @author      louis
     * @version     v1.0
     * @since       2010-01-05
     * 
     * @return      string
     * 
     */
    private function _getCode($mSource){
        if (empty($mSource)){
            return false;
        }
        $sPattern = '!<tr class=\'text_big\'>.*<td>&nbsp;</td>.*<td>&nbsp;&nbsp;参考代码：(.*)</td>.*<td width="18%" align="right" nowrap>.*!Uis';

        preg_match($sPattern, $mSource, $aMatches);
        
        $aMatches[1] = isset($aMatches[1]) ? trim($aMatches[1]) : "";
        
        return $aMatches[1];
    }
    
    
    
    
    /**
     * 分析提示信息码
     * 
     * @author      louis
     * @version     v1.0
     * @since       2010-01-06
     * 
     * @return      
     *  
     */
    private function _analysisCode($sCode){
        // 数据检查
        if (empty($sCode) || empty($this->aNotice)){
            return false;
        }
        
        return isset($this->aNotice[$sCode]) ? $this->aNotice[$sCode] : "";
    }
    
    
    // 分析抓取数据，提取有效数据
    public function analysis( $mSource ){
//        $this->putFileContent(LOG_PATH."/test", $mSource);
    	if (empty($mSource))			return false;
        
        // 写入下一页的cookie值
		$this->analysisAndInsert();
    	
    	$sPattern = '!<tr onmouseover="this.className=\'table_select_bg\'" onmouseout="this.className=\'\'">.*<td width="11%" class="table_content text_center" style="white-space:nowrap">(.*)</td>.*<td width="14%" class="table_content text_left" title="([\d|\W|a-z|A-Z]+)">.*<div id="L" style="overflow:hidden;text\-overflow:ellipsis">.*</div>.*</td>.*<td width="11%" class="table_content text_center">(.*)</td>.*<td width="11%" class="table_content text_center">(.*)</td>.*<td width="11%" class="table_content text_center">(.*)</td>.*<\!\-\- <td width="11%" class="table_content text_left" title="(\d*)"> \-\->.*<td width="7%" class="table_content text_center" title="(.*)">.*<div id="W" style="overflow:hidden;text\-overflow:ellipsis">.*</div>.*</td>.*<td width="6%" class="table_content text_left" style="white\-space:nowrap">(.*)</td>.*<td width="17%" class="table_content text_left" title="(.*)">.*<div id="S" style="overflow:hidden;text\-overflow:ellipsis">.*</div>.*</td>.*</tr>!Uis';
    	
//    	$mSource = iconv('gbk', 'utf-8', $mSource);
	$aContent = array();
	$mContent = preg_match_all($sPattern, $mSource, $aMatches,PREG_SET_ORDER);
    	if($mContent === false){
            if (!file_exists(LOG_PATH)) {
                mkdir(LOG_PATH, 0777, true);
                chmod(LOG_PATH, 0777);
            }
            $this->putFileContent(LOG_PATH."/" . $this->vmId . '-' . GET_CONTENT_FAILED."_".date("Y-m-d-H",time()).".log", "\n\nstart" . date("Y-m-d H:i:s",time()) . $mSource, true);
            return GET_CONTENT_FAILED;
    	} else if($mContent === 0){ // 无数据
            // 获取提示信息码
            $sNoticeCode = $this->_getCode($mSource);
            
            // 分析提示信息码
            $iStateCode = $this->_analysisCode($sNoticeCode);
            
            // 如果获取信息码失败,则直接写入文件中
            if (empty($sNoticeCode)){
                $this->putFileContent(CODE_PATH."/noticeCode" . $this->vmId . "-" . $this->cardNum."-". date("Y-m-d-h:i:s", time()) .".log", $mSource, false, false);
                echo "\nGET NEW NOTICE FAILED!---filename:" .CODE_PATH."/noticeCode" . $this->vmId . "-" . $this->cardNum."-". date("Y-m-d-h:i:s", time()) .".log\n";
            }
            
            // 如果返回的信息码不在已知信息码中，则写入文件中
            if (intval($iStateCode) <= 0 && !empty($sNoticeCode)){
                $this->putFileContent(CODE_PATH."/noticeCode" . $this->vmId . "-" . $this->cardNum."-". $sNoticeCode .".log", $mSource, false, false);
                echo "\n" . HAVE_NEW_NOTICE . ":NEW NOTICE!---filename:" .CODE_PATH."/noticeCode" . $this->vmId . "-" . $this->cardNum."-". $sNoticeCode .".log\n";
            }
            
            if (in_array(intval($iStateCode), $this->aNeedReturn) === true){
                $this->putFileContent(LOG_PATH."/needreturn-" . $this->vmId . "-" . $this->cardNum."-". $sNoticeCode . '-' . date("Y-m-d-H",time()) .".log", "\n\nneedreturn" . date("Y-m-d H:i:s",time()) . $mSource, true, false);
                return $iStateCode;
            }
            
    		$iTempErrno = $this->retryGet(LOG_PATH . "/analysis-" . $this->vmId . "-" . $this->cardNum, RETRY_ANALYSIS_TIMES, ANALYSIS_ERROR, RETRY_ANALYSIS_STATUS);
    		if (intval($iTempErrno) === ANALYSIS_ERROR){ // 只记录下抓取的文件信息
    			$this->putFileContent(LOG_PATH."/" . $this->vmId . "-" . $this->cardNum . "-" . ANALYSIS_ERROR."_".date("Y-m-d-H",time()).".log", "\n\nstart" . date("Y-m-d H:i:s",time()) . $mSource, true, false);
    		} else {
                $this->putFileContent(LOG_PATH."/unknow-" . $this->vmId . "-" . $this->cardNum . "-" . date("Y-m-d-H",time()).".log", "\n\nunknow" . date("Y-m-d H:i:s",time()) . $mSource, true, false);
            }
    		return $iTempErrno;
    	}
    	
    	// 更新页面的md5码
    	$sMd5Temp = md5($mSource);
    	updateContentKey($this->vmId, $sMd5Temp);
    	if ($sMd5Temp === $this->LastPageMd5){ // 如果验证串相同，执行下一循环
    		return -1;
    	}
    	
    	// 获取下一页的标志码,获取查询结果的页数，当前页码
    	$sPattern2 = "!window\.parent\.document\.getElementById\(\"FILESEARCHSTR\"\)\.value = \"(.*)\".*SplitPage2\('(\d*)','(\d*)'.*parent\.document\.getElementById\(\"A_STR\"\).value='(.*)'.*<span class=\"text_bold\">账号：</span>(.*)\n!Uis";
    	$mStr = preg_match($sPattern2, $mSource, $aStr);
    	$aPageInfo = array();
    	if($mStr === false){
            if (!file_exists(LOG_PATH)) {
                mkdir(LOG_PATH, 0777, true);
                chmod(LOG_PATH, 0777);
            }
            $this->putFileContent(LOG_PATH."/" . $this->vmId . "-" . GET_ASTR_FAILED."_".date("Y-m-d-H",time()).".log", "\n\nstart" . date("Y-m-d H:i:s",time()) . $mSource, true);
            return GET_ASTR_FAILED;
    	} else if ($mStr === 0){
    		$this->putFileContent(LOG_PATH."/" . $this->vmId . "-" . ANALYSIS_ASTR_FAILED."_".date("Y-m-d-H",time()).".log", "\n\nstart" . date("Y-m-d H:i:s",time()) . $mSource, true);
    		return ANALYSIS_ASTR_FAILED;
    	} 
//        else {
//    		foreach($aStr as $v){
//			    array_shift($v);
//			    $aTmp = array_map('strip_tags',array_map('trim',$v));
//			    $aPageInfo[] = $aTmp;
//			}
//    	}
    	
    	$this->FileSearchStr = trim($aStr[1]);
    	$this->AllPages = intval($aStr[2]);
    	$this->CurrentPage = intval($aStr[3]);
        $this->ASTR = trim($aStr[4]);
//        $aContent[] = $aStr[5];
        
    	
    	if ($this->analysisAndInsert() === false){
    		updateErrno($this->vmId, UPDATE_COOKIE_FAILED);
    	}
    	
    	// 去除金额两边的html代码
    	foreach($aMatches as $aMatch){
		    array_shift($aMatch);
		    $aTmp = array_map('strip_tags',array_map('trim',$aMatch));
		    $aTmp['accept_account'] = trim($aStr[5]);
		    $aContent[] = $aTmp;
		}
		
//		print_r($aContent);die;
    	return empty($aContent) ? false : $aContent;
    }
    

    /**
	 * 模仿建行，隐藏５位卡号
	 *
	 * @param string $account				// 账号
	 * @param int	 $style					// 1为返回隐藏后的卡号，2为返回拆开后的数组
	 * @return string						// 隐藏后的账号
	 * 
	 * @author 		louis
	 * @version 	v1.0
	 * @since 		2010-11-22
	 * @package 	passport
	 * 
	 */
    public function hidAccount( $account, $style = 1){
    	$sFront = substr($account, 0, strlen($account) - HIDDEN_LENGTH - LEAVE_LENGTH);
    	$sLast = substr($account, 0 - LEAVE_LENGTH);
    	if ($style === 1){
    		return $sFront . str_repeat("*", $this->HiddenLength) . $sLast;
    	} else if ($style === 2){
    		$aResult = array($sFront, $sLast);
    		return $aResult;
    	} else {
    		return false;
    	}
    }
    
    
    // 获得验证串
    public function getKey( $sDate, $fAmount, $fBalance, $sAccount, $sAccName, $sAcceptAccount){
    	return md5($sDate . $fAmount . $fBalance . $sAccount . $sAccName . $sAcceptAccount);
    }
    
    
    /**
     * 向文件中写入内容
     *
     * @param string $sPath			// 文件路径
     * @param source $sContent		// 写入内容
     * @return mix
     */
    public function putFileContent($sPath, $sContent, $append = false, $bReWrite = true){
        $sDir = dirname($sPath);
        if (!file_exists($sDir)){
            mkdir($sDir,0777, true);
            chmod($sDir, 0777);
        }
        
        if ($bReWrite === false){
            if (file_exists($sPath)){
                return true;
            }
        }
        
        if ($append === true){
            return file_put_contents($sPath, $sContent, FILE_APPEND);
        }
    	return file_put_contents($sPath, $sContent);
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
     * 重试抓取
     *
     * @param string $sDir				// 记录已重复执行的次数的文件
     * 
     * @author 		louis
     * @version 	v1.0
     * @since 		2010-12-2
     * 
     * @return 		int		$iErrno		// 错误码
     * 
     */
    public function retryGet($sDir, $iTimes, $iErrno, $iTryno){
    	// 首先去取重试次数
        $iCount = $this->getFileContent($sDir);
        $iTempCount = intval(trim($iCount));
        if ($iTempCount === $iTimes){
        	$iTemp = 0;
        } else {
        	$iTemp = $iTempCount + 1;
        }
        $s = $this->putFileContent($sDir, $iTemp);
        // 重试3次后将状态置为11
        $iTempErrno = 0;
        if ($iTempCount >= $iTimes){ // 出错状态
        	$iTempErrno = $iErrno;
        } else { // 重试状态
        	$iTempErrno = $iTryno;
        }
        return $iTempErrno;
    }
    

    private function getPage($url, $referer, $postData = '')
    {
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
        if (!$flag = $this->wget->getContents($this->fetchMode, $method, $url)) {
        	$iTempErrno = $this->retryGet(LOG_PATH . "/" . $this->vmId . "-" . $this->cardNum, RETRY_TIMES, GET_ERROR, RETRY_STATUS);
//            return self::failed($iTempErrno, "errno:".$this->wget->errno().",errstr:".$this->wget->errstr());
			  return $iTempErrno;
        } else {
            // 先将重试抓取的文件归零
            $iCount = $this->getFileContent(LOG_PATH . "/" . $this->vmId . "-" . $this->cardNum);
            if (intval($iCount) !== 0){
                $this->putFileContent(LOG_PATH . "/" . $this->vmId . "-" . $this->cardNum, 0);
            }
        }
        $t2 = microtime(true);

        $requestHeader = trim($this->wget->getRequestHeaderStream(), "\r\n");
        $this->ResponseHeader = $this->wget->getResponseHeaderStream();
        $responseBody = $this->wget->getResponseBody();
        echo "info:".number_format(strlen($responseBody))." Bytes, ".round($t2 - $t1, 3)."s\n";
        //echo "$responseBody\n";
        //file_put_contents("log/".time()."_".strlen($responseBody).".txt", $responseBody);
        /*if (strlen($responseBody) < 1000) {
            logs($this->cardNum, 2);
            return self::failed(LOGIN_EXPIRED, 'The session has expired!');
        }*/

        return $responseBody;
    }
    
    public function setProxy($ip)
    {
        $this->_proxyIp = $ip;
    }

//    static public function failed($errno, $errstr)
//    {
//        switch (substr($errno, 0, 1))
//        {
//            case 1:// error
//                throw new exception("$errstr", $errno);
//                break;
//            case 2:
//                // warning
//                throw new exception("$errstr", $errno);
//                break;
//            case 3:
//                //return array('errno' => $errno, 'errstr' => $errstr);
//                throw new exception("$errstr", $errno);
//                break;
//            default:
//                throw new exception("unknown error", 10);
//                break;
//        }
//    }
}

function unlock()
{
    global $lockFile;
    @unlink($lockFile);
    return true;
}
// entry
error_reporting(E_ALL);
set_time_limit(0);
$params = parse_cmdline_argv_to_var();
$vm_id = $params['vm_id'];
if (empty($vm_id)) {
    echo 'Please specify vm_id\n';
    die();
}
if (!getConfigs('ccbdeposit_turnauto')) {
    die();
}

$t1 = time();
echo "\nStart at ".date('Y-m-d H:i:s')."...\n";
$lockFile = dirname(__FILE__)."/ccb_$vm_id.lock";
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
    die("file lock has exists!\n");
}
touch($lockFile);
register_shutdown_function('unlock');

if (!$vmInfo = getVmInfo($vm_id, 1)) {
    echo 'Non-exists vm!';
    die();
}
if ($vmInfo['errno'] > 0 && intval($vmInfo['errno']) !== NO_MORE_TRANSFER && intval($vmInfo['errno']) !== RETRY_STATUS && intval($vmInfo['errno']) !== RETRY_ANALYSIS_STATUS) {
    echo 'The logon info is expired! (' . $vmInfo['errno'] . ')';
    die();
}


if (empty($vmInfo['card_num']) || empty($vmInfo['dse_session_id']) || empty($vmInfo['cookie']) || empty($vmInfo['login_name']) || intval($vm_id) <= 0 || intval($vmInfo['branch_id']) <= 0){
	die("param is wrong\n");
}
echo "Last_page:" . $vmInfo['last_page'],"\n";
//$startTime = strtotime($vmInfo['start_time']);
//$endTime = strtotime($vmInfo['end_time']);
$startTime = getConfigs('ccbdeposit_starttime');
$endTime = getConfigs('ccbdeposit_stoptime');

// 获取充值延迟周期
$sCycle = getConfigs('ccbdeposit_cycletime');
if (!empty($sCycle)){
	$aCycle = explode("|", $sCycle);
	if (in_array(date("l"), $aCycle)){
		$startTime = date("H:i",  strtotime($startTime) + intval(getConfigs('ccbdeposit_delaytime')) * 60);
	}
}
if (!$delayOffTime = getConfigs('ccbdeposit_eachtime')) {
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
$ccb = new CCB($vmInfo['card_num'], $vmInfo['branch_id'], $vmInfo['dse_session_id'], $vmInfo['cookie'], $vmInfo['login_name'], $vm_id, getConfigs('ccbdeposit_repeat'), $aDefineBank['CCB']);
$ccb->setProxy($vmInfo['ip']);
$aTransfer = array();
$sTransfer = $ccb->getAllTransferList();
$iErrno = 0;
if (strpos($sTransfer, "#") === false){
	$iErrno = intval($sTransfer);
} else {
	$aTransfer = explode("#", $sTransfer);
    $aTransfer[2] = isset($aTransfer[2]) ? $aTransfer[2] : "";
	if (intval($aTransfer[2]) > 0){
		$iErrno = intval($aTransfer[2]);
	}
	echo "OK, total ".$aTransfer[0]." records has been added! total" . $aTransfer[1] . "records were failed!\n";
}

if ($iErrno > 0){
    if ($iErrno !== RETRY_STATUS && $iErrno !== RETRY_ANALYSIS_STATUS){
        logs($vmInfo['card_num'], 2);
    }
	switch ($iErrno){
        case NO_MORE_TRANSFER:
            die("No more transfers!\n");
        break;
        case PARAM_WRONG:
            die("The params are wrong!\n");
        break;
		case GET_CONTENT_FAILED:
			die("The Regular Expression is wrong!\n");
		break;
		case GET_ACCOUNT_FAILED:
			die("Get account was failed!\n");
		break;
		case GET_ASTR_FAILED;
			die("Get next page string was failed!\n");
		break;
		case ANALYSIS_ASTR_FAILED:
			die("Analysis next page string was failed!\n");
		break;
		case UPDATE_COOKIE_FAILED:
			die("Update cookie was failed!\n");
		break;
		case GET_ERROR:
			die("Get transfers was failed!\n");
		break;
		case ANALYSIS_ERROR:
			die("Analysis transfers was failed!\n");
		break;
		case RETRY_STATUS:
			die("Retry get!\n");
		break;
        case RETRY_ANALYSIS_STATUS:
            die("Retry analysis transfers!\n");
        break;
        case ACCOUNT_ILLEGAL:
            die("Bank Attention: account illegal!\n");
        break;
        case UPDATE_LAST_PAGE_FAILED:
            die("Update the last page was failed!\n");
        break;
        default:
            die("Unknow error!\n");
        break;
	}
}

$t3 = time();
echo "start at ".date('Y-m-d H:i:s', intval($t1)).", end at ".date('Y-m-d H:i:s', $t3).", total waste ".($t3-$t1)." s\n\n";
?>

