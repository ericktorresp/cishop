<?php
/**
 *  
 * 充值操作日志记录类
 * 	--单条信息 增 删 改 显示
 *
 * set()					更新一个充值记录表单 by ID
 * add()					增加一个充值记录历史表单 by ID
 * setStatus()				设置充值表单状态
 * _getStatus()				TODO:(掉单处理)
 * _getLostStatus()			TODO:(掉单处理)
 * _Error()
 *
 * @name Loadlogs.php
 * @package payport_class
 * @version 0.1 3/15/2010
 * @author Jim
 * 
 * 

 CREATE TABLE `passport`.`online_load_logs` (
`id` BIGINT( 8 ) NOT NULL AUTO_INCREMENT ,
`payment_type` INT( 1 ) NOT NULL COMMENT '支付接口ID',
`payment_id` BIGINT( 8 ) NOT NULL COMMENT '交易单唯一ID',
`log_info` TEXT NOT NULL COMMENT '返回信息',
`log_time` DATETIME NOT NULL COMMENT '记录时间',
PRIMARY KEY ( `id` )
) ENGINE = MYISAM COMMENT = '充值操作日志表';

**/


class model_pay_loadlogs extends model_pay_base_info 
{
	public $Id;				// ID
	public $PaymentType;	// 充值方式
	public $PaymentAccId;	// 充值分账户
	public $PaymentId;		// 充值表ID
	public $PaymentIdStr;	// 发往第三方平台ID(替代真实本站ID)
	public $LogInfo;		// 返回信息
	public $LogTime;		// 日志时间

	 
	protected $TableName='online_load_logs';
	
	
	/**
	 * 获取单个交易单相关所有日志信息
	 * 
	 * @param int 		$iKey		关键字
	 * @param string 	$iKeyMark	字段名
	 * @param int		$iStatus	状态值
	 * 
	 * @return array()
	 */
	public function __construct($iId=null,$aParam=array()){
		parent::__construct();
		if ( is_numeric($iId) && ($iId >= 0) ){

			$aParam['Page'] = 0;
			$sFields = 'id,payment_type,payment_id,payment_id_str,log_info,log_time';
			$sSqlWhere = ' `payment_id` = '.$iId;
			
			$aTmpResult = $this->FindAll($sSqlWhere,$sFields,$aParam['Page'],'id',true);
			/*$aTmpData = $this->oDB->getAll($sSql);
			$this->Id = $aTmpData['id'];
			$this->PaymentType = $aTmpData['payment_type'];
			$this->PaymentId = $aTmpData['payment_id'];
			$this->LogInfo = $aTmpData['log_info'];
			$this->LogTime = $aTmpData['log_time'];*/
			
		}
	}
	
	
	/**
	 * 记录日志
	 * @return bool or int
	 */
	public function record(){
		
		$aTempData = array( 'payment_type'	=> intval($this->PaymentType),
							'payment_acc_id' => intval($this->PaymentAccId),
                            'payment_id'  	=> intval($this->PaymentId),
							'payment_id_str' => $this->PaymentIdStr,
                            'log_info'		=> $this->LogInfo ? $this->LogInfo : '',
                            'log_time'		=> date('Y-m-d H:i:s')
                         );
        
        $this->oDB->insert( $this->TableName, $aTempData );
        if( $this->oDB->affectedRows() < 1 ){
              return -1;
        }
        else{
        	//返回last插入ID
        	return true;
        }

	}
	
	/**
	 *  数据归档
	 * 		将现有数据按日期条件转存到归档数据表, 并删除现表中已被转存数据
	 */
	public function finishData(){
		// 创建归档表
		
		// 选择数据
		
		// 插入
		
		// 删除现表的数据
	}
	
	public function createHistroyTable(){
		
		// 获取表结构
		
		// 执行创建新表
	}
	
}