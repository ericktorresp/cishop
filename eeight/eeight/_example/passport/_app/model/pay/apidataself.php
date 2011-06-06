<?php
/**
 *  
 *  全人工处理 数据封装 (虚拟)
 * 
 * send()					发送请求
 * sendForm					生成一个提交表单(HTML)并发送
 * receive()				解析接收数据
 * paymentInfo()			整理返回结果
 * saveOptLogs()			保存操作日志 online_load_logs
 * _decompositionSOAP()		解析SOAP格式返回数据
 * _toDoValidation()		1toPAY二次验证
 * _decompositionXML()		解析XML格式返回数据
 * _decompositionHTTP()		解析HTTP格式返回数据
 * _decompoHTTPPaymentP2P() 解析1.0版本HTTP格式返回
 * _decompoHTTPpayment()	解析1.1版本HTTP格式返回
 * _decompoHTTPQuery()		解析HTTP格式返回的查询
 * _decompoHTTPWithdraw()	解析HTTP格式返回的提现
 * _buildPayment()			组装一个支付请求
 * _buildWithdraw()			组装一个提现请求
 * _buildWithdrawquery()
 * _buildQuery()			组装一个查询请求
 * _curl_post()				CURL发送数据并接收返回
 * 
 * @name ApiDataSelf.php
 * @package payport
 * @version 0.1
 * @since 4/16/2010
 * @author Jim
 * 
 * 
 */

class model_pay_apidataself extends model_pay_payaccountinfo
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
	 * @var unknown_type
	 */
	private $InputData;
	
	/**
	 * 类流控关键字限制数组
	 * 	(用于限定正确的流控关键字,仅当数组内存在的关键才有效）
	 * @var array
	 */
	private $ModelValidParam = array('query','payment','withdraw','decomp','withdrawquery','decompquery');	
	
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
		
		//检查流控关键字，是否限定字符内, 否则不予实例数据
		if ( array_search($sModel,$this->ModelValidParam) ){
			$this->Model = $sModel;
			$this->getAccountDataObj();
			// 优先使用分账户的回传地址与货币单位
			if ( strlen($this->AccReceiveHost) > 5 ) $this->ReceiveHost = $this->AccReceiveHost;
			if ( $this->AccCurrency ) $this->Currency = $this->AccCurrency;
		}else{
			return false;
		}
	}
	
	
	/**
	 * 发送请求
	 * 
	 */
	public function send(){
		return false;
	}
	
	
	/**
	 * 接收数据
	 * @return 处理接收到的数据，并返回格式化后的数据
	 */
	public function receive(){
		$this->FinishData = false;
	}

	
	/**
	 *  数据接口信息返回
	 *
	 * 	loadstatus: 根据各种情况，归纳只返回三种 1:success 2:废单 3:未成功(处理中)
	 *  实际交易单状态(
	 * 0:客户请求(仅客户提交在线充值时原始状态)  
	 * 1:支付平台操作完成且成功，资金操作成功 
	 * 2:得到拒付返回信息，废单处理 
	 * 3:支付平台操作完成但资金操作失败,支付平台操作中 等各类不确定情况 (掉单) 
	 */
	public function paymentInfo(){
		
        $this->PaymentStatus['PaymentType'] = false; 
        $this->PaymentStatus['PaymentId'] 	= false;
        $this->PaymentStatus['Amount'] 		= false;
		$this->PaymentStatus['Status'] 		= false;
		$this->PaymentStatus['Code'] 		= false;
		$this->PaymentStatus['Valid'] 		= false;
		
		$this->PaymentStatus['Valid'] = ($this->PaymentStatus['Valid'] == '|') ? '' : $this->PaymentStatus['Valid'];
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
	 * 生成HTML格式的提交表单，并 onload.submit 发送 
	 * (完全模拟1topay DEMO示范 )
	 */
	private function sendForm(){
		return false;
	}
	
	
	/**
	 * 数据解析
	 * 	当验证 Result VERIFIED 时，认定支付成功
	 */
	private function _decompositionSOAP(){
		return -1;
	}
	
	
	/**
	 * 传输效验 二次验证
	 *	返回 $this->FinishData 属性数组 'Info' 与 'Code'
	 * 	当 Code = VERIFIED 验证成功， Info 存放 Error与Debug信息;
	 */
	private function _toDoValidation(){
		return -1;
	}
	
	/**
	 * 返回 XML数据解析 
	 *@return array()
	 */
	private function _decompositionXML(){
		return -1;
	}
	
	/**
	 * HTTP数据解析  
	 *@return array()
	 */
	private function _decompositionHTTP(){

		return false;
		
	}
	
	
	/**
	 * 解析支付返回 回调URL使用 
	 * 1topay 1.0接口
	 *
	 */
	private function _decompoHTTPPaymentP2P(){
		$this->FinishData['RefID'] = false;
		$this->FinishData['Curr'] = false;
		$this->FinishData['CustID'] = false;
		$this->FinishData['Amount'] = false;
		 
		$this->FinishData['TransID'] = false;
		$this->FinishData['ValidationKey'] = false;
		$this->FinishData['EncryptText'] = false;
		//$this->FinishData['PaymentTime'] = false;
		$this->FinishData['Status'] = false;
	}
	
	
	/**
	 * 解析支付返回 返回URL使用 
	 * 1topay 1.1接口
	 */
	private function _decompoHTTPPayment(){
		$this->FinishData['RefID'] = false;
		$this->FinishData['Curr'] = false;			//
		$this->FinishData['CustID'] = false;	//使用 商户扩展信息保存本站UID
		$this->FinishData['Amount'] = false;
		$this->FinishData['TransID'] = false;	//使用  1toPAY交易流水号
		$this->FinishData['ValidationKey'] = false;
		$this->FinishData['EncryptText'] = false;	//返回的 效验签名
		//$this->FinishData['PaymentTime'] = false;
		$this->FinishData['Status'] = false;
	}
	
	
	/**
	 * 解析查询返回
	 */
	private function _decompoHTTPQuery(){
		return -1;
	}
	
	
	/**
	 * 解析提现返回
	 */
	private function _decompoHTTPWithdraw(){
		return -1;
	}
	
	
	/**   
	 * 创建一个1TOPAY支付的数据结构
	 * @return array(url=完整的路径,data=按格式组装好的requeset串)
	 */
	private function _buildPayment(){
		return false;
	}
	

	/**
	 * 创建一个提现的数据结构
	 * @return array(url=完整的路径,data=按格式组装好的requeset串)
	 */
	private function _buildWithdraw(){
		return false;
	}
	
	
	/**
	 * 创建一个提现的数据结构
	 * @return array(url=完整的路径,data=按格式组装好的requeset串)
	 */
	private function _buildWithdrawquery(){
		return false;
	}
	
	
	/**
	 * 创建一个查询的数据结构
	 *@return array()
	 */
	private function _buildQuery(){
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
	

	/* end class */
}