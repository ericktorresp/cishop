<?php
/**
 *  
 *  1toPay 对口数据封装
 * 
 * send()					发送请求
 * sendForm					生成一个提交表单(HTML)并发送
 * saveOptLogs()			保存操作日志 online_load_logs
 * _buildPayment()			组装一个支付请求
 * _curl_post()				CURL发送数据并接收返回
 * checkBackSignP2P()		检查接收数据MD5码 (1topayAPI 1.0接口)
 * checkBackSign()			检查接收数据MD5码 (1topayAPI 1.1接口)
 * 
 * @name ApiData1topay.php
 * @package payport
 * @version 0.1
 * @since 3/23/2010
 * @author Jim
 * 
 * 
 * @example 
 * 	(创建一个充值支付)
 * 	$oO = new model_Pay_ApiData1topay($ppid,'id','payment',0);
 * 	$oO->Model='payment';
 * 	$oO->send();
 *  // send()不直接返回,以下方式解析
 * 
 * 
 */

class model_pay_apidata1topay extends model_pay_payaccountinfo
{
	/**
	 * 流控关键字  query查询  payment充值  withdraw提现, 决定使用哪种数据封装 decomp解析数据
	 * @var string
	 */
	public $Model='query';
	/**
	 * 发送/接收的HTTP方式
	 * @var string
	 */
	public $HttpType='GET';
	/**
	 * 接收到的数据
	 * @var array('data','encrpttext')
	 */	
	public $ReceiveData;
	/**
	 * 接收数据的格式, SOAP,HTTP,XML
	 * @var string
	 */
	public $ReceiveType;
	/**
	 * 整理之后的返回数据,仅处理接收数据时
	 *
	 * @var array()
	 */
	public $FinishData;
	/**
	 * 整个类最终数据,提供前台直接调用
	 * $PaymentStatus['status'] == 'success'成功 2废单 3挂起
	 *
	 * @var array()
	 */
	public $PaymentStatus;
	/**
	 * 输入数据， 仅从实例化时传入的第三个数组参数获取值
	 * @var array
	 */
	private $InputData;
	
	/**
	 * 类流控关键字限制数组
	 * 	(用于限定正确的流控关键字,仅当数组内存在的关键才有效）
	 * @var array
	 */
	private $ModelValidParam = array('payment');  //只解析 充值 
				//array('query','payment','withdraw','decomp','withdrawquery','decompquery');	
	
	/**
	 * 以生成HTML形式提交充值表单时所用的数据数组
	 * @var array
	 */
	private $EForm;
	
	/**
	 * 初始化，提取支付接口基本数据，等待下一步 行为 指定
	 * @param int	$iPPid		支付分账户ID
	 * @param string 	$sModel 	流控关键字  query查询  payment充值  withdraw提现, 决定使用哪种数据封装/ decomp解析数据
	 */
	public function __construct($iPPid,$sModel='query',$aInputData = array(),$iInvalids=0){
		
		if ( empty($iPPid) && !is_numeric($iPPid) ) return false;
		
		parent::__construct($iPPid);

		//获取输入数据 withdraw 必须
		if (is_array($aInputData) && (count($aInputData) > 0) ) $this->InputData = $aInputData;
		
		//检查流控关键字，是否限定字符内, 否则不予实例
		if ( array_search($sModel,$this->ModelValidParam) !== false ){
			$this->Model = $sModel;
			$this->getAccountDataObj();
			// 优先使用分账户的回传地址与货币单位
			if ( strlen($this->AccReceiveHost) > 5 ) $this->ReceiveHost = $this->AccReceiveHost;
			if ( $this->AccCurrency ) $this->Currency = $this->AccCurrency;
		}else{
			die('Error:2001 Type Deny');
		}
	}
	
		
	/**
	 * 保存操作日志
	 * $aInfo (订单ID，错误或信息)
	 */
	public function saveOptLogs($aInfo){
		$oLog = new model_pay_loadlogs();
		$oLog->PaymentType = $this->Id;
		$oLog->PaymentId = $aInfo['RefID'];
		$oLog->logTime = date('Y-m-d H:i:s');
		$oLog->LogInfo = $aInfo['Info'];
		return $oLog->record();
	}
	
	
	
	/**
	 * 发送请求
	 * 
	 */
	public function send(){
		//发送一个查询,并将结果保存
		if ($this->Model == 'payment') 
		$aSendData = $this->_buildPayment();
	
			
		if ( ($this->Model == 'payment') && !empty($aSendData['data']) ){
			//--以header跳转方式发送数据
			//header("Location: ".$aSendData['url'].'?'.$aSendData['data']);
			//exit;
				
			//--以curl方式提交数据
			//echo $this->_curl_post($aSendData['url'],$aSendData['data']);
			
			//--以生成HTML gb2312 形式提交数据  (OLD)
			$this->sendForm();
		}	
		else{
			return false; // 数据封装错误
			exit;
		}
	
	}
	
	/**
	 * 生成HTML格式的提交表单，并 onload.submit 发送 
	 * (完全模拟1topay DEMO示范 )
	 */
	private function sendForm(){
		//    onLoad="E_FORM.submit()"
		
		echo '<html>
		<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
</head>
<body  onLoad="E_FORM.submit()">
<form action="'.@$this->EForm[surl].'" method="POST" name="E_FORM" target="_self">
  <input type="hidden" name="p01_service"        value="interface_pay">
  <input type="hidden" name="p02_out_ordercode"        value="'.@$this->EForm[oid].'">
  <input type="hidden" name="p03_payamount"     value="'.@$this->EForm[amount].'">
  <input type="hidden" name="p04_sitecode"  value="'.@$this->EForm[merchantsitecode].'">
  <input type="hidden" name="p05_subject"        value="'.@$this->EForm[subject].'">
  <input type="hidden" name="p06_body"        value="'.@$this->EForm[body].'">   
  <input type="hidden" name="p07_price"    value="'.@$this->EForm[amount].'">
  <input type="hidden" name="p08_quantity"        value="'.@$this->EForm[quantity].'">
  <input type="hidden" name="p09_notify_url"        value="'.@$this->EForm[url].'">
  <input type="hidden" name="p10_note"     value="'.@$this->EForm[note].'">
  <input type="hidden" name="p13_paymode"     value="'.@$this->EForm[paymode].'">
  <input type="hidden" name="p14_paybankcode"     value="'.@$this->EForm[paybankcode].'">
  <input type="hidden" name="p15_cardnumber"     value="'.@$this->EForm[cardnumber].'">
  <input type="hidden" name="p16_cardpassword"     value="'.@$this->EForm[cardpassword].'">
  <input type="hidden" name="p17_return_url"     value="'.@$this->EForm[return_url].'">
  <input type="hidden" name="sign"  value="'.@$this->EForm[sign].'">  
  </form>
</body>
</html>';
	}
	
	/**
	 * 解析返回数据  (1topay 1.1接口)
	 * 
	 * @return bool true/ error number  (0,1,2,3);
	 */
	public function receive(){
		if ( !isset($this->ReceiveData) || !is_string($this->ReceiveData) ) return -100;
		
		//分解数据
		parse_str ( $this->ReceiveData );
		//效验sign
		if ( $this->checkBackSign($p01_service,$p02_out_ordercode,$p03_payamount,$p04_sitecode,$p05_subject,$p06_body,$p07_price,$p08_quantity,$p10_note,$p11_status,$p12_ordercode,$sign) 
		=== false )  
			return -101;
			
		//效验自定义MD5
		
		
		//返回
		switch ($p11_status) {
			case 'success' :
				return true;
				break;
			default :
				return false;
		}
	}
	
	/**   
	 * 创建一个1TOPAY支付的数据结构
	 * @return array(url=完整的路径,data=按格式组装好的requeset串)
	 */
	private function _buildPayment(){
		if ($this->Model != 'payment') return -1;
		/**
		接口名称	 		$p01_service
 		商户订单流水号		$p02_out_ordercode
		支付金额			$p03_payamount
		商户网站身份ID		$p04_sitecode
		产品名称			$p05_subject
		产品描述			$p06_body
		产品单价			$p07_price
		购买数量			$p08_quantity
		回调通知与支付平台页面返回 地址 1.1版本接口使用		$p09_notify_url
			--(商家网站处理给会员加钱)
		备注 			$p10_note
			--(重要:使用此字段保存UserID)
		支付方式			$p13_paymode
			--0 网银 12 Q币卡
		银行代码			$p14_paybankcode
  		充值卡卡号  		$p15_cardnumber
  		充值卡金额  		$p16_cardpassword
  		回调通知与支付平台页面返回 地址1.0接口使用  		$p17_return_url
			--(商家网站给用户给出提示信息)
  		**/
 
  		// 检查
  		$aTmpBulid = array(
  			'p01_service' => 'interface_pay',
  			'p02_out_ordercode' => $this->myIconv($this->InputData['uniqueidstr']),
  			'p03_payamount' => $this->myIconv($this->InputData['amount']),
  			'p04_sitecode' => $this->myIconv(trim($this->AccSiteId)),
  			'p05_subject' => $this->myIconv('servicefee'),
  			'p06_body' => $this->myIconv('getVIPservice'),
  			'p07_price' => $this->myIconv($this->InputData['amount']),
  			'p08_quantity' => $this->myIconv('1'),
  			'p09_notify_url' => $this->myIconv($this->ReceiveHost.$this->ReceiveUrl),
  			'p10_note' => $this->myIconv($this->InputData['userid']),
  			'p13_paymode' => $this->myIconv('0'),
  			'p14_paybankcode' => $this->myIconv($this->InputData['bankcode']),
  			'p15_cardnumber' => '',
  			'p16_cardpassword' => '',
  			'p17_return_url' => $this->myIconv($this->ReceiveHost.$this->ReceiveUrlKeep),
  			'merchantcode' => $this->myIconv(trim($this->AccIdent)),
  			'merchantkey' => $this->myIconv(trim($this->AccKey))
  		);
    		
  		//检查数组每个键值有效
  		if ( $this->checkArrayValue( $aTmpBulid, 
  									array('p13_paymode','p15_cardnumber','p16_cardpassword') 
  									) === false ){
  			die('Error:2002 Param Bad');
  		}
  		
		//生成1topay签名
  		$strtext = 'p01_service=interface_pay&p02_out_ordercode='.$this->myIconv($this->InputData['uniqueidstr'])
  			.'&p03_payamount='.$this->myIconv($this->InputData['amount'])
  			.'&p04_sitecode='.$this->myIconv(trim($this->AccSiteId))
  			.'&p05_subject='.$this->myIconv('servicefee')
  			.'&p06_body='.$this->myIconv('getVIPservice')
  			.'&p07_price='.$this->myIconv($this->InputData['amount'])
  			.'&p08_quantity='.$this->myIconv('1')
  			.'&p09_notify_url='.$this->myIconv($this->ReceiveHost.$this->ReceiveUrl)
  			.'&p10_note='.$this->myIconv($this->InputData['userid'])
  			.'&p13_paymode='.$this->myIconv('0')
  			.'&p14_paybankcode='.$this->myIconv($this->InputData['bankcode'])
  			.'&p15_cardnumber=&p16_cardpassword='
  			.'&p17_return_url='.$this->myIconv($this->ReceiveHost.$this->ReceiveUrlKeep)
  			.'&merchantcode='.$this->myIconv(trim($this->AccIdent)).'&merchantkey='.$this->myIconv(trim($this->AccKey)); 
  		//http_build_query($aTmpBulid);
		$aTmpBulid['sign'] = strtolower( trim( md5( $this->myIconv($strtext) ) ) )  ;

		$sPayportUrl = 'http://pay'.trim($this->AccIdent).'.1topay.com/gatewaya.aspx';
  		/*
  		return array('url' => $sPayportUrl, 'data' => $sPayportData); */
  		
  		//使用 sendForm方法时赋值 eform
		$this->EForm['surl'] 	= $sPayportUrl;
		$this->EForm['oid'] 	= $this->myIconv($this->InputData['uniqueidstr']);
		$this->EForm['amount'] 	= $this->myIconv($this->InputData['amount']);
		$this->EForm['merchantsitecode'] = $this->myIconv(trim($this->AccSiteId));
		$this->EForm['subject'] 	= $this->myIconv('servicefee');
		$this->EForm['body'] 		= $this->myIconv('getVIPservice');
		$this->EForm['amount'] 		= $this->myIconv($this->InputData['amount']);
		$this->EForm['quantity'] 	= $this->myIconv('1');
		$this->EForm['url'] 	= $this->myIconv( trim($this->ReceiveHost).trim($this->ReceiveUrl) );
		$this->EForm['note'] 	= $this->myIconv($this->InputData['userid']);
		$this->EForm['paymode'] = $this->myIconv('0');
		$this->EForm['paybankcode'] 	= $this->myIconv($this->InputData['bankcode']);
		$this->EForm['cardnumber'] 		= '';
		$this->EForm['cardpassword'] 	= '';
		$this->EForm['return_url'] 	= $this->myIconv( trim($this->ReceiveHost).trim($this->ReceiveUrlKeep) );
		$this->EForm['sign'] 		= $aTmpBulid['sign'];
		
		//记录数据历史
		//'payment_id'=> eregi_replace ( "[A-Z]", "", strtoupper ( $this->InputData['uniqueid'] ) )
		$aRecordSendData = array('payment_id'=> intval( $this->InputData['uniqueid'] ) ,
				'payment_id_str' => mysql_escape_string($this->InputData['uniqueidstr']),
				 'save_data' => mysql_escape_string($strtext),
                 'act_type'  => 1,
                 'utime'	 => date('Y-m-d H:i:s')
                 );
		$oRecordSD = new model_pay_loaddata();
		$oRecordSD->record($aRecordSendData);

			return array('url' => 1, 'data' => 1);	
	}
	


	/**
	 * 创建一个ECAPAY提现的数据结构
	 * @return array(url=完整的路径,data=按格式组装好的requeset串)
	 */
	private function _buildWithdraw(){
		return -1;
	}
	
	
	
	/**
	 * CURL POST提交
	 *
	 * @param string $url 	POST目标URL
	 * @param string $vars	POST提交数据
	 * @return string get数据
	 */
	private function _curl_post($url, $vars) { 
		$curl = curl_init();
		$vars = $this->myIconv($vars);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($curl, CURLOPT_TIMEOUT, 100 );
		curl_setopt($curl, CURLOPT_URL,$url);
		curl_setopt($curl, CURLOPT_POST, 1 );
		curl_setopt($curl, CURLOPT_POSTFIELDS, $vars);     
		$data = curl_exec($curl);
		curl_close($curl);
		if ($data){
			return $data;
		}
		else{
			return false;
		}
	}
	
	
	/**
	 * 检查返回数据MD5的有效性;  提交支付模式 1.0接口
	 *
	 * @param string $p01_service
	 * @param string $p02_out_ordercode
	 * @param string $p03_payamount
	 * @param string $p04_sitecode
	 * @param string $p11_status
	 * @param string $p12_ordercode
	 * @param string $sign
	 * @return bool
	 */
	private function checkBackSignP2P($p01_service,$p02_out_ordercode,$p03_payamount,$p04_sitecode,$p11_status,$p12_ordercode,$sign){
		$strtext='p01_service=interface_pay&p02_out_ordercode='.$p02_out_ordercode.'&p03_payamount='.$p03_payamount.'&p04_sitecode='.$p04_sitecode.'&p11_status='.$p11_status.'&p12_ordercode='.$p12_ordercode.'&merchantcode='.$this->AccIdent.'&merchantkey='.$this->AccKey;
		$newsign = strtolower(trim(md5($strtext)));
		if ($newsign == $sign){
			return true;
		}else{
			return false;
		}
	}
	
	
	/**
	 * 检查返回数据的有效性; 跳过1topay 
	 * 1.1接口
	 *
	 * @param string $p01_service
	 * @param string $p02_out_ordercode
	 * @param string $p03_payamount
	 * @param string $p04_sitecode
	 * @param string $p05_subject
	 * @param string $p06_body
	 * @param string $p07_price
	 * @param string $p08_quantity
	 * @param string $p10_note
	 * @param string $p11_status
	 * @param string $p12_ordercode
	 * @param string $sign
	 * @return bool
	 */
	private function checkBackSign($p01_service,$p02_out_ordercode,$p03_payamount,$p04_sitecode,$p05_subject,$p06_body,$p07_price,$p08_quantity,$p10_note,$p11_status,$p12_ordercode,$sign) {
		$strtext='p01_service=interface_pay&p02_out_ordercode='.$p02_out_ordercode.'&p03_payamount='.$p03_payamount.'&p04_sitecode='.$p04_sitecode.'&p05_subject='.$p05_subject.'&p06_body='.$p06_body.'&p07_price='.$p07_price.'&p08_quantity='.$p08_quantity.'&p10_note='.$p10_note.'&p11_status='.$p11_status.'&p12_ordercode='.$p12_ordercode.'&merchantcode='.$this->AccIdent.'&merchantkey='.$this->AccKey;
		$newsign = strtolower(trim(md5($strtext)));
		if ($newsign == $sign){
			return true;
		}else{
			return false;
		}
	}
	

		
	/* end class */
}