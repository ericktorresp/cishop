<?php
/**
 *  
 * 充值数据收发类
 * 	--单条信息 增 删 改 显示
 *
 * set()					更新一个充值记录表单 by ID
 * add()					增加一个充值记录历史表单 by ID
 * setStatus()				设置充值表单状态
 * _getStatus()				TODO:(掉单处理)
 * _getLostStatus()			TODO:(掉单处理)
 * _Error()
 *
 * @name Loaddata.php
 * @package payport_class
 * @version 0.1 5/24/2010
 * @author Jim
 * 
 * 

 CREATE TABLE IF NOT EXISTS `online_load_data` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(8) NOT NULL COMMENT '支付单ID',
  `save_data` text NOT NULL COMMENT '完整数据包',
  `act_type` int(1) NOT NULL COMMENT '接收0或发送1',
  `utime` datetime NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='充值收发数据历史表' ;

**/


class model_pay_loaddata extends model_pay_base_info 
{
	public $Id;				// ID
	public $PaymentId;		// 充值支付单ID
	public $PaymentIdStr;	// 充值支付单字串
	public $SData;			// 充值分账户
	public $AType;			// 数据方式,接收或发送

	 
	protected $TableName='online_load_data';
	
	
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
			$sFields = 'id,payment_id,payment_id_str,save_data,act_type,utime';
			$sSqlWhere = ' `payment_id` = '.$iId;
			
			$aTmpResult = $this->FindAll($sSqlWhere,$sFields,$aParam['Page'],'id',true);
			
		}
	}
	
	
	/**
	 * 记录日志
	 * @return bool or int
	 */
	public function record($aData=false){
		if ( is_array($aData) ){
			$aTempData = $aData;
		}else{
			$aTempData = array( 'payment_id'=> intval($this->PaymentId),
							'payment_id_str'=> mysql_escape_string($this->PaymentIdStr),
							'save_data' =>  mysql_escape_string( $this->SData ),
                            'act_type'  => intval($this->AType),
                            'utime'		=> date('Y-m-d H:i:s')
                         );
		}
		
        $this->oDB->insert( $this->TableName, $aTempData );
       
        if( $this->oDB->affectedRows() < 1 ){
              return false;
        }
        else
        {
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