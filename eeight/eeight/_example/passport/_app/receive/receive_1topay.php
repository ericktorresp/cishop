<?php
/**
 *  接收 1TOPAY 返回数据 
 *  
 *  支持 1topay 1.1版本接口 
 * 		1,专用的 receive_1topay.php 处理分析、效验1topay数据并格式化回显客户
 * 		2,调用通用 model: receivepayment.php 处理具体帐变等数据更新工作 当前只在确认成功时进行调用
 * 
 * @name receive_1topay.php
 * @package payment
 * @version 0.2 5/18/2010
 * @author Jim
 * 
 */
@ob_start ();
// 依据实际运行可以进行设置的定义常量
define ( 'DEBUGME', false ); 			// 故障调试true,将操作日志同时记录到文件,默认已记入数据表
define ( 'RECORDDATA', true ); 			// 将接收数据记录到文件  _tmp/data_receive/YMD.log

// 不可更改常量
define ( 'DONT_USE_APPLE_FRAME_MVC', true ); 		// 跳过 MVC 流程
define ( 'DONT_TRY_LOAD_SYSCONFIG_FILE', true ); 	// 跳过配置文件检测
require (realpath ( dirname ( __FILE__ ) . '/../../' ) . '/index.php'); //引入项目入口文件


	//$iPaymentId支付单在本站id  $iPaymentId_str在第三方平台ID
$iPaymentId = 0;
$iPaymentId_str = '';

if ( isset($_REQUEST['p02_out_ordercode']) )	$iPaymentId_str = $_REQUEST ['p02_out_ordercode'];

if ( empty ( $iPaymentId_str ) ) 
{
	model_pay_loadinfo::stopPageRun ( '充值失败 -3000' );
	exit;
}

// 兼容旧ID方式
if (  is_numeric( substr($iPaymentId_str,1) )  )
{

	$iPaymentId = intval( eregi_replace ( "[A-Z]", "", strtoupper ( $iPaymentId_str ) ) );
	if ( !is_numeric($iPaymentId) )
	{
		model_pay_loadinfo::stopPageRun ('充值失败 -3100');
		exit;
	}
	
}
else
{
	// 新的ID方式: 	由传回的字串ID，查询真实的数字ID
	$oFrist = new model_pay_loadinfo();
	$iPaymentId = $oFrist->getIdBySpecName($iPaymentId_str);
	//$iPaymentId = model_pay_loadinfo::getIdBySpecName($iPaymentId_str);

	if ($iPaymentId === false)
	{
		model_pay_loadinfo::stopPageRun ('充值失败 -3102');
		exit;
	}

}

if ($iPaymentId < 0){
	model_pay_loadinfo::stopPageRun ('充值失败 -3103');
	exit;
}

	// 读取状态为“支付中”的支付单资料 状态0; 
$oOL = new model_pay_loadinfo ( $iPaymentId, 0 );
//echo $oOL->UserId;
if (empty ( $oOL->UserId ) || empty ( $oOL->LoadAmount ) || empty ( $oOL->LoadType ) || empty ( $oOL->AccId )) 
{
	
	// 检查支付单是否已充值成功
	$oOlCheck = new model_pay_loadinfo( $iPaymentId, 1);  
	if ( ( $oOlCheck->UserId ) && ( $oOlCheck->LoadAmount ) && ( $oOlCheck->LoadType ) && ( $oOlCheck->AccId ) ) 
	{
		$oOlCheck->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':充值已成功' );
		$oOlCheck->stopPageRun ( '充值'.substr($oOlCheck->LoadAmount,0,-2).', 已成功' );
		exit;
	}
	else
	{
		$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':支付单ID无效或状态已改变' );
		$oOL->stopPageRun ( '充值失败 -3002' );
		exit;
	}
	
}

	// 加锁支付单
if ( $oOL->Lock() === false ) 
{
	$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':加锁失败 -3004' );
	$oOL->stopPageRun ( '充值失败 -3004' );
	exit;
}

$sRData = $sReturnData = '';
$sRData =  $oOL->arraytostr ( $_REQUEST );

parse_str($sRData);

// 保存接受的数据到数据日志表
$aRecordReceiveData = array(
				'payment_id'=> intval($iPaymentId),
				'payment_id_str'=> mysql_escape_string($iPaymentId_str),
				'save_data' => mysql_escape_string($sRData),
                'act_type'  => 0,
                'utime'	 => date('Y-m-d H:i:s')
                 );
$oRecordRD = new model_pay_loaddata();
$bRecordReceiveData = $oRecordRD->record($aRecordReceiveData);

if (!$bRecordReceiveData){
	 $oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':存储接收数据失败' );
}
	// 是否DEBUG
$oOL->DEBUG = (DEBUGME) ? true : false;
	// 文件形式DEBUG日志记录位置
$oOL->LogDir = realpath ( dirname ( __FILE__ ) . '/../../' ) . '/_tmp/data_receive/';

// 保存接收到的回调数据到 LogDir 目录
if (RECORDDATA) 
{
	$iRee = $oOL->saveReceiveDate ( $iPaymentId . ':' . $iPaymentId_str . ':' . $sRData );
	if ($iRee === -1) 
	{
		$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':存档目录不存在' );
	} 
	elseif ($iRee === -2) 
	{
		$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':写数据存档错误' );
	} 
	elseif ($iRee === -3) 
	{
		$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':创建存档目录失败' );
	} 

}
	
//setp 01 支付单信息 ------------------------------------------------------------------------
	// 获取接口基本信息
$oPayport = new model_pay_payaccountinfo ( $oOL->AccId );
$oPayport->GetType = true; //以后台模式提取分账户数据;  防止在接口关闭、禁用时依然有未结束的充值单,如无须则改为false前台模式
$oPayport->OptType = 'onlineload';
$oPayport->getAccountDataObj();


	// 根据支付接口的编码要求转换为本站utf8编码
$p05_subject = $oPayport->myIconv ( $p05_subject, 'in' );
$p06_body = $oPayport->myIconv ( $p06_body, 'in' );

/*if (($p04_sitecode != $oOL->SaveSiteId) && ! empty ( $oOL->SaveSiteId )) 
	$p04_sitecode = $oOL->SaveSiteId;
*/
$strtext = 'p01_service=interface_pay&p02_out_ordercode=' . $p02_out_ordercode . '&p03_payamount=' . $p03_payamount 
			. '&p04_sitecode=' . $p04_sitecode . '&p05_subject=' . $p05_subject . '&p06_body=' . $p06_body 
			. '&p07_price=' . $p07_price . '&p08_quantity=' . $p08_quantity . '&p10_note=' . $p10_note 
			. '&p11_status=' . $p11_status . '&p12_ordercode=' . $p12_ordercode 
			. '&merchantcode=' . $oPayport->AccIdent . '&merchantkey=' . $oPayport->AccKey;
			
if ( strtolower ( md5 ( $strtext ) ) != $sign )
{
	$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':签名效验失败' );
	if (! $oOL->Lock ( 'unlock' ) ) 
	{
		$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':解锁失败 -3005' );
	}
	
	$oOL->stopPageRun('充值失败 -3005');
	exit;
}
	
switch ($p11_status) 
{
	case 'success' :
		$iProcessDataResult = true;
	break;

	default :
		$iProcessDataResult = false;
}
	
//效验自定义md5
	//	换算数据表中存储的发起原值金额,保证MD5的计算正确性,
$iCodeMount = ( intval ( $oOL->FeeType ) > 0 )  ?  floatval ( $p03_payamount ) : floatval ( $p03_payamount - $oOL->LoadFee ); 
$iCodeMount = number_format ( $iCodeMount, 2, '.', '' );
	
$sTmpMd5code = md5 ( floatval ( $p10_note ) . 'A' . $iCodeMount . 'B' . intval ( $oOL->LoadType ) 
				. 'C' . intval ( $oOL->AccId ) . 'D' . $oOL->TransTime );
if (!empty ( $p10_note ) && !empty ( $iCodeMount ) && !empty ( $oOL->LoadType ) 
		&& !empty ( $oOL->AccId ) && !empty ( $oOL->TransTime )) 
{
	
	if ( $sTmpMd5code != $oOL->Md5Code ) 
	{
		$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':自定义md5效验失败' );
		if (! $oOL->Lock ( 'unlock' )) 
		{
			$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':解锁失败 -3006' );
		}
		
		$oOL->stopPageRun ( '充值失败 -3006' );
		exit;
	}
	
}


if ($iProcessDataResult === true)
{
	//调用事务包装model  (只对成功进行处理)
	$oPrecess = new model_pay_receivepayment();
	$oPrecess->LoadId = $oOL->Id;
	$aReceiveData = array(
		'loadid' => $p02_out_ordercode,
		'platid' => $p12_ordercode,
		'userid' => intval($p10_note),
		'amount' => floatval($p03_payamount),
		'status' => $p11_status
	);
		
	$oPrecess->ReceiveData = $aReceiveData;
	$rReProc = $oPrecess->runProcess();
		
	if ($rReProc === true)
	{
		/****
		 * 成功单不再解锁
		if (! $oOL->Lock ( 'unlock' )) 
		{
			$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':解锁失败 -3006' );
		}
		****/
		// 充值所有涉及数据表更新新增处理完成,跳转回框架,回显文字提示
		$oOL->stopPageRun('充值成功',false);
		
	}
	else
	{
		$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':充值失败 '.$rReProc );
		
		if (! $oOL->Lock ( 'unlock' )) 
		{
			$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':解锁失败 '.$rReProc );
		}
		
		$oOL->stopPageRun('充值失败 -3999');
		exit;
	}
	
}
else
{
	//TODO 根据$p11_status 哪些情况将支付单置为废单 状态改为2
	
	$oOL->saveLogs ( $iPaymentId . ':' . $iPaymentId_str . ':充值失败 ('.$p11_status.') -3007' );
	
	// 支付单未成功、未处理 一定要解锁
	$iLoop = 1;	
	while ($iLoop < 3)
	{
		if ($oOL->Lock ( 'unlock' )) 
		{
			break;
		}
		else
		{
			$oOL->saveLogs ($iPaymentId .':'.$iPaymentId_str. ':解锁失败 1000.'.$iLoop );
			usleep(500);
		}
		$iLoop++;
	}
						
	$oOL->stopPageRun('充值失败 -3008');
	exit;
	
}