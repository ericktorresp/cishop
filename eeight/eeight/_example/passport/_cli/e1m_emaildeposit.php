<?php

/**
 * 
 * 路径：/_cli/e1m_emaildeposit.php
 * 读取email充值记录表，自动完成用户充值
 * 
 * 说明：
 * 	  读取自动充值表中的未处理记录，然后在充值记录表中找到相应信息，对用户实施充值操作，其它记录不做任何处理
 * 
 * 命令行可接受参数: 空
 * 
 * @author		louis
 * @version 	v1.0
 * @since 		2010-09-02
 * @package 	passport
 * 
 */

@ini_set( "display_errors", TRUE);
error_reporting(E_ALL);
set_time_limit(10000);
define('DONT_USE_APPLE_FRAME_MVC', TRUE);
define("ICBC", "icbc");				// 工行接口名称
define("CCB", 	"ccb");					// 建行接口名称
require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php');



class cli_emaildeposit extends basecli{
	
	private $bDoUnLock  = FALSE;   // 是否允许释放 LOCK 文件
	
	/**
     * 析构
     */
    public function __destruct()
    {
        parent::__destruct();
        if( $this->bDoUnLock == TRUE )
        {
            @unlink( $this->_getBaseFileName() . '.locks' );
        }
    }
    
    
    /**
     * 重写基类 _runCli() 方法
     */
    protected function _runCli()
    {
    	// 检查参数
    	$sBank = isset($this->aArgv[1]) ? $this->aArgv[1] : "";
    	if (empty($sBank)){
    		die("have no bank info");
    	}
    	$sPrefix =  "";
    	switch ($sBank){
    		case ICBC:
    			$sPrefix = "mail";
    		break;
    		case CCB:
    			$sPrefix = "ccb";
    		break;
    		default:
    			die("have no bank info");
    			
    	}
    	// 检查充值时间
    	$oConfigd = new model_config();
		$sStartTime = strtotime($oConfigd->getConfigs($sPrefix . 'deposit_starttime')); // 充值开始时间
		$sEndTime = strtotime($oConfigd->getConfigs($sPrefix . 'deposit_stoptime')) + ($oConfigd->getConfigs($sPrefix . 'deposit_eachtime') + 5) * 60;
		$sRunNow = strtotime(date('G:i')); // 当前时间
		
		if ($sStartTime > $sEndTime){ // 开始时间大于结束时间，说明已跨天
			if ($sRunNow >= $sEndTime && $sRunNow <= $sStartTime){
				die("This time is forbidden");
			}
		} else {
			if ($sRunNow <= $sStartTime || $sRunNow >= $sEndTime){
				die("This time is forbidden");
			}
		}
		
		
		/*if (intval($oConfigd->getConfigs('maildeposit_turnauto')) === 0){
			die("This time is forbidden");
		}*/
		
    	// Step: 01 检查是否已有相同CLI在运行中
        $sLocksFileName = $this->_getBaseFileName() . '.locks';
        if( file_exists( $sLocksFileName ) )
        { // 如果有运行的就终止本个CLI
            die("Error : The CLI is running\n" );
        }
        file_put_contents( $sLocksFileName ,"running", LOCK_EX ); // CLI 独占锁
		
        /*// 首先查询支付银行列表
        $oDeposit     = new model_deposit_depositlist(array(),'','array');
    	$aDepositList = $oDeposit->getDepositArray('all');*/
		
        $aRecord = array();
		switch ($sBank){
			case ICBC:
		$oEmailDeposit = new model_deposit_emaildeposit();
			break;
			case CCB:
				$oEmailDeposit = new model_deposit_ccbdeposit();
			break;
			default:
				die("have no bank info");
		}
		
		$oEmailDeposit->BankStatus = 0; // 只获取未处理的记录
		$aRecord = $oEmailDeposit->getBankRecord();
		
		if ( empty($aRecord) ){
			$this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
			die( "no more record\n" );
		}
		
		$iError = 0;
		// 遍历数据集
		foreach ( $aRecord as $k => $v ){
			switch ($sBank){
				case ICBC:
			// 获取充值唯一key
			$oEmailDeposit->Note = $v['notes'];
			$sKey = "";
			$sKey = $oEmailDeposit->getKey();
			// 查询相应记录
			$oEmailDeposit->Key = $sKey;
				break;
				case CCB:
					$oEmailDeposit->PayDate = $v['pay_date'];
					$oEmailDeposit->Amount = $v['amount'];
					$oEmailDeposit->Account = $v['full_account'];
					$oEmailDeposit->HiddenAccount = $v['hidden_account'];
					$oEmailDeposit->AccName = $v['acc_name'];
					$oEmailDeposit->AcceptName = $v['accept_name'];
					$oEmailDeposit->AcceptCard = $v['accept_card'];
				break;
				default:
					die("have no bank info");
			}
			
			$aResult = array();
			$aResult = $oEmailDeposit->getRecord();
			$iTransferId = 0;
			$sCard = "";
			$iPPid = 0; // 分账户id
			switch ($sBank){
	        	case ICBC:
	        		$iTransferId = $v['transfer_id'];
                    $sCard = $v['accept_card_num'];
//                    $iPPid = $aResult[0]['account_id'];
	        	break;
	        	case CCB:
	        		$iTransferId = $v['id'];
                    $sCard = $v['accept_card'];
//                    $iPPid = $aResult[0]['payacc_id'];
	        	break;
	        	default:
	        		die("have no bank info");
	        }
			if (empty($aResult)){ // 修改银行抓取表记录为挂起状态
				$oEmailDeposit->BankRecordId = $iTransferId;
				$oEmailDeposit->BankStatus = 2; // 挂起
				$aError = array();
				$aError = $oEmailDeposit->createParam($v, 1, 1, 1);
				/*$aError['pay_time'] = $v['pay_date'];
				$aError['pay_card'] = $v['accept_card_num'];
				$aError['pay_amount'] = $v['amount'];
				$aError['pay_fee'] = $v['fee'];
				$aError['pay_key'] = $v['notes'];
				$aError['transfer_id'] = $v['transfer_id'];
				$aError['error_type'] = 1;
				$aError['status'] = 1;
				$aError['created'] = date("Y-m-d H:i:s", time());*/
				$oEmailDeposit->Account = $sCard;
				$bBankResult = $oEmailDeposit->updateBankAndInsert( $aError );
				if ($bBankResult === false){
					$iError++;
				}
				continue;
			}
			
            switch ($sBank){
	        	case ICBC:
                    $iPPid = $aResult[0]['account_id'];
	        	break;
	        	case CCB:
                    $iPPid = $aResult[0]['payacc_id'];
	        	break;
	        	default:
	        		die("have no bank info");
	        }
			/**
			 * 如果查询的记录存在1条以上，则将记录设置为挂起，等待管理员手工处理
			 * 如果相同的记录中，有已处理过的记录，则略过已处理的记录，将未处理的记录全部设置成挂起
			 */
			
			/*if ( count($aResult ) > 1 ){ // 多条记录
				$oEmailDeposit->BankRecordId = $v['transfer_id'];
				$oEmailDeposit->BankStatus = 2; // 挂起
				$aError = array();
				$aError['pay_time'] = $v['pay_date'];
				$aError['pay_card'] = $v['accept_card_num'];
				$aError['pay_amount'] = $v['amount'];
				$aError['pay_key'] = $v['notes'];
				$aError['transfer_id'] = $v['transfer_id'];
				$aError['error_type'] = 1;
				$aError['status'] = 1;
				$aError['created'] = date("Y-m-d H:i:s", time());
				$oEmailDeposit->Account = $v['accept_card_num'];
				$bResult = $oEmailDeposit->updateBankAndInsert( $aError );
				if ($bResult === false){
					$iError++;
				}
				continue;
			} else {*/ // 正式充值开始
				// 如果平台记录信息不为待处理状态，则修改银行抓取记录为挂起,用户自行取消，却去银行汇款
				// 附言相同的记录，在平台的记录中已经成功，表明财务已手工处理，将银行抓取信息改为跳过，不进入异常表，同时不更新分账户余额
				switch ($sBank){
					case ICBC:
                    if (intval($aResult[0]['status']) !== 0){
                        $oEmailDeposit->BankRecordId = $iTransferId;
                        $oEmailDeposit->Key          = $sKey;
                        $aHave = $oEmailDeposit->getBankRecordByKey();
                        if (empty($aHave)){ // 在银行抓取记录中找不到对应记录
                            $oEmailDeposit->BankStatus = 3;  // 跳过
                            $bBankResult1 = $oEmailDeposit->updateBankStatus();
                            var_dump($bBankResult1);
                            if ($bBankResult1 === false){
                                $iError++;
                            }
                            break 2;
                        }
                        $bHave = false;
                        foreach ($aHave as $ek => $ev){
                            if ($v['pay_date'] == $ev['pay_date']){
                                $bHave = true;
                            }
                        }
                        if ($bHave === true){
                            $oEmailDeposit->BankStatus = 3; // 跳过
                            $bBankResult2 = $oEmailDeposit->updateBankStatus();
                            if ($bBankResult2 === false){
                                $iError++;
                            }
                            break 2;
                        } else {
                            $oEmailDeposit->BankStatus = 2; // 异常
                            $aError = array();
                            $aError['pay_time'] = $v['pay_date'];
                            $aError['pay_card'] = $v['accept_card_num'];
                            $aError['pay_amount'] = $v['amount'];
                            $aError['pay_key'] = $v['notes'];
                            $aError['transfer_id'] = $v['transfer_id'];
                            $aError['error_type'] = 1;
                            $aError['status'] = 1;
                            $aError['created'] = date("Y-m-d H:i:s", time());
                            $oEmailDeposit->Account = $v['accept_card_num'];
                            $bResult = $oEmailDeposit->updateBankAndInsert( $aError );
                            if ($bResult === false){
                                $iError++;
                            }
                            break 2;
                        }
                    }
					break;
				}
				
				/*if (intval($aResult[0]['status']) === 1){ 
					$oEmailDeposit->BankRecordId = $v['transfer_id'];
					$oEmailDeposit->BankStatus = 2; // 挂起
					$bBankResult = $oEmailDeposit->updateBankAndInsert( $aError );
					continue;
				}*/
				
				$iErrorType = 0;
				/*// 时间是否违规
				if ($v['pay_date']  > $aResult[0]['over_time']){ // 时间违规
					$iErrorType = 2;
				}
				if (trim( $v['accept_card_num'] ) !== trim( $aResult[0]['account'] )) { // 账号违规
					$iErrorType = 3;
				}
				if (floatval( $v['amount'] ) != floatval( $aResult[0]['money'] )){ // 金额违规
					$iErrorType = 4;
				}*/
				$iErrorType = $oEmailDeposit->getWrondStyle($v, $aResult[0]['id']);
				if ($iErrorType === false){
					$iError++;
					continue;
				}
				if ( $iErrorType > 0){
					if (intval($aResult[0]['status']) === 0){ // 修改充值记录表和银行抓取表中的记录为挂起状态
						$oEmailDeposit->Id = $aResult[0]['id'];
						$oEmailDeposit->Status = 2; // 挂起
						$oEmailDeposit->BankRecordId = $iTransferId;
						$oEmailDeposit->BankStatus = 2; // 挂起
						$oEmailDeposit->ErrorType = $iErrorType;
						$aError = array();
						$aError = $oEmailDeposit->createParam($v, 2, $iErrorType, 1, $aResult[0]);
						$oEmailDeposit->Account = $sCard;
						$bResult = $oEmailDeposit->unionUpdate( $aError );
						if ($bResult === false){
							$iError++;
						}
					} else {	// 修改银行抓取表记录为挂起状态
						$oEmailDeposit->BankRecordId = $iTransferId;
						$oEmailDeposit->BankStatus = 2; // 挂起
						$aError = array();
						$aError = $oEmailDeposit->createParam($v, 1, $iErrorType, 1);
						$oEmailDeposit->Account = $sCard;
						$bBankResult = $oEmailDeposit->updateBankAndInsert( $aError );
						if ($bBankResult === false){
							$iError++;
						}
					}
					continue;
				}
                $sDescrition = ""; // 账变描述
                switch ($sBank){
                    case ICBC:
                        $sDescrition = "工行";
                    break;
                    case CCB:
                        $sDescrition = "建行";
                    break;
                    default:
                        $sDescrition = "email充值";
                    break;
                }
				// 充值开始
				$oOrder = new model_orders();
				$aOrders = array();
		    	$aOrders['iFromUserId'] 	= intval($aResult[0]['user_id']); // (发起人) 用户id
		    	$aOrders['iToUserId'] 		= intval($aResult[0]['user_id']); // (关联人) 用户id
		    	$aOrders['fMoney'] 			= floatval($aResult[0]['money']); // 账变的金额变动情况
		    	$aOrders['iChannelID'] 		= 0; // 发生帐变的频道ID
		    	$aOrders['iAdminId'] 		= 0; // 管理员id
		        $aOrders['iOrderType'] 		= ORDER_TYPE_ZXCZ; // 账变类型
		        $aOrders['sDescription'] 	= $sDescrition; // 账变的描述
                
		        // 手续费
		        $aFee = array();
		    	$aFee['iFromUserId'] 		= intval($aResult[0]['user_id']); // (发起人) 用户id
		    	$aFee['iToUserId'] 			= intval($aResult[0]['user_id']); // (关联人) 用户id
		    	$aFee['fMoney'] 			= floatval($v['fee']); // 账变的金额变动情况
		    	$aFee['iChannelID'] 		= 0; // 发生帐变的频道ID
		    	$aFee['iAdminId'] 			= 0; // 管理员id
		        $aFee['iOrderType'] 		= ORDER_TYPE_SXFFH; // 账变类型
		        $aFee['sDescription'] 		= $sDescrition . '-手续费返还'; // 账变的描述
		        
		        $oEmailDeposit->Id = intval($aResult[0]['id']);
		        $bResult = $oEmailDeposit->realLoad( $aOrders, $aFee, $iTransferId, $iPPid );
		        if ($bResult === false){
		        	$iError++;
		        	continue;
		        }
//			}depositaccountinfo
		}
		echo "have " . $iError . "failed!\n";
        $this->bDoUnLock = TRUE; // 允许程序结束后, 对 LOCK 文件解锁
        return TRUE;
    }
}

$oCli = new cli_emaildeposit(TRUE);
EXIT;