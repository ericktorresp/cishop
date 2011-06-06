<?php
/**
 *  
 * EMAIL充值 资金进出记录类 
 * 	--单条信息 增 删 改 显示
 *
 * add()					增加一个记录 by ID
 * _Error()
 *
 * @name	RecordInfo.php
 * @package mailDeposit
 * @version 0.1 9/2/2010
 * @author	Jim
 * 
 * 
**/

class model_deposit_recordinfo extends model_pay_base_info
{
	/**
	 * 记录ID
	 * @var int
	 */
	public $Id;
	/**
	 * 所属支付接口ID
	 * @var int
	 */
	public $PpId;
	/**
	 * 所属分账户ID
	 * @var int
	 */
	public $AccId;
	/**
	 * 用户ID
	 * @var Int
	 */
	public $UserId;
	/**
	 * 转入金额
	 * @var float
	 */
	public $InAmount;
	/**
	 * 转出金额
	 * @var float
	 */
	public $OutAmount;
	/**
	 *  转出银行手续费
	 * @var float
	 */
	public $BackCharge;
	/**
	 * 备注
	 * @var text
	 */
	public $ReMark;
	/**
	 * 是否管理后台操作
	 * @var int  0/1  (0否、1是)
	 */
	public $IsOP;
	/**
	 * 管理后台操作者名称
	 * @var string
	 */
	public $OpUser;
	/**
	 * 表名
	 * @var string
	 */
	protected $TableName='deposit_trans_record';
	
	
	public function __construct(){
		parent::__construct();
	}
	
	
	/**
	 * 新增记录数据
	 * @return last insert id 
	 */
	public function add(){
		
		if ( empty($this->InAmount) && empty($this->OutAmount) )
		{
			return FALSE;
		}
		if ( !empty($this->OpUser) && ($this->InAmount == 0) && ($this->OutAmount == 0) )
		{
			return FALSE;
		}
		$aTempData = array( 'ppid'		=> intval($this->PpId),
							'accid' 	=> intval($this->AccId),
                            'userid'  	=> intval($this->UserId),
                            'inamount'  => floatval($this->InAmount),
                            'outamount'	=> floatval($this->OutAmount),
							'bankcharge'=> floatval($this->BackCharge),
							'remark'	=> $this->ReMark,
							'isop'		=> intval($this->IsOP),
                            'opuser'	=> $this->OpUser,
							'utime' 	=> date('Y-m-d H:i:s')
                         );
     	
        return $this->_insert($aTempData);
        
	}
	
	/**
	 * 获取分账户,支付接口合计值,sum()
	 * @param $sCate	[in][out][charge]
	 * @return int 结果值
	 */
	public function getTotal($sCate='all')
	{
		if ( !$this->PpId && !$this->AccId ) return FALSE;
		
		switch ($sCate)
		{
			case 'in':
				$sField =' SUM(`inamount`) AS totalval ';
				break;
			case 'out':
				$sField =' SUM(`outamount`) AS totalval ';
				break;
			case 'charge':
				$sField =' SUM(`bankcharge`) AS totalval ';
				break;
			default:
				$sField = ' SUM(`inamount`) AS totalin, SUM(`outamount`) AS totalout, SUM(`bankcharge`) AS totalcharge ';
				//, SUM(`bankcharge`) AS totalcharge
			break;
		}
		
		$sWhere = ($this->AccId > 0)  ?  ' `accid`='.$this->AccId  :  ' `ppid`='.$this->PpId;
		
		$sSql = "SELECT $sField FROM $this->TableName WHERE $sWhere";
		$aTmp = $this->oDB->getOne($sSql);
		
		if ( ($sCate == 'in') || ($sCate == 'out') || ($sCate == 'charge') ) 
		{
			return floatval($aTmp['totalval']);
		}
		elseif ( count($aTmp) > 0 )
		{
			return $aTmp;
		}
		else 
		{
			return NULL;
		}
		
		
	}
	
	/**
	 * 执行 insert SQL
	 *
	 * @param array $aTempData  插入的数据数组
	 * @return bool/int
	 */
	public function _insert($aTempData){
		$this->oDB->insert( $this->TableName, $aTempData );
		
        if( $this->oDB->affectedRows() < 1 )
        {
			return false;
        }
        return $this->oDB->insertId();
	}
	
	
	/**
	 * 检测数组变量，每个键值有效性
	 * 	
	 * @param array $aArray 被检查的数组
	 * @param array	$aZero  本次检查中允许为空、为0的键名数组
	 */
	private function _chkValue($aArray,$aZero){
       			
		if ( empty($aArray) && empty($aZero) ) return false;
		
		foreach ($aArray AS $aKey => $aVal)
		{
        	if (  empty($aVal) && ( array_search($aKey,$aZero) === false) )
        	{
        		return false;
        		break;
        	}
        		
        }
       
        return true;
	}

	/**
	 * 列表
	 */
	public function & getList( $sFields = "*" , $sCondition = "1", $iPageRecords = 20 , $iCurrPage = 1)
	{
	    $sTableName = $this->TableName;
	    $sFields    = ' * ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, ' ORDER BY `id` DESC ' );
	}
	
	/*  class end  */
}