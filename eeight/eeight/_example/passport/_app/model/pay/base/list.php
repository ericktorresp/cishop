<?php
/**
 * 列表类底层类
 * 
 * 此类适用于一切信息列表类的基类,基于新的basemodel类
 * 
 * @author Frank
 * @version 1.6 utf8 2010-03-10 10:00
 */
class model_pay_base_List extends model_pay_base_common {
	/**
	 * 数据对象类型
	 *
	 * @var string			可接受 array 和 object
	 */
	protected $InfoType = 'array';
	/**
	 * 数组对象类名称
	 *
	 * @var string 			InfoType 为 object 时有效;
	 */
	protected $InfoClsName;
	/**
	 * 是否将信息对象的主键值作为Data数组的键名
	 *
	 * @var boolean;
	 */
	protected $SetKey = false;
	/**
	 * 页尺寸
	 *
	 * @var integer
	 */
	public $PageSize;
	/**
	 * 创建数据对象时的附加参数
	 *
	 * @var mixed
	 */
	protected $ExtraInfo = '';
	/**
	 * 当前页
	 *
	 * @var integer
	 */
	public $Page;
	/**
	 * 总页数
	 *
	 * @var integer
	 */
	public $PageCount;
	/**
	 * 信息总数
	 *
	 * @var integer
	 */
	public $TotalCount;
	/**
	 * 当前页信息数
	 *
	 * @var integer
	 */
	public $Count;
	/**
	 * 条件字符串
	 *
	 * @var string
	 */
	protected $Condition;
	/**
	 * 排序字符串
	 *
	 * @var string
	 */
	protected $Order;
	/**
	 * 主键字段名
	 *
	 * @var string
	 */
	protected $KeyField;
	/**
	 * 数据表名称
	 *
	 * @var unknown_type
	 */
	protected  $Table;
	/**
	 * 数据表别名
	 *
	 * @var string
	 */
	private $AialsOfTable;
	/**
	 * SQL FROM 子句
	 * 
	 * @var string
	 */
	protected $FromString;
	/**
	 * 信息数组
	 *
	 * @var array
	 */
	public $Data = array();
	/**
	 * 是否使用扩展模式
	 * 
	 * @var boolean
	 */
	protected $ExtendMode = FALSE;
	
	/**
	 * 构造函数
	 * 
	 * 设置Table,KeyField,PageSize等值
	 *
	 * @param unknown_type $sTable
	 * @param unknown_type $sKeyField
	 * @param unknown_type $iPageSize
	 */
	function __construct($sTable,$sKeyField = 'id',$iPageSize = DEFAULT_PAGESIZE,$sAliasOfTable = ''){
		parent::__construct();
		$this->PageSize = $iPageSize;
		$this->KeyField = $sKeyField or $this->KeyField = 'id';
		$this->Table = $this->oDB->TbPrefix . $sTable;
		$this->AialsOfTable = $sAliasOfTable;
		
		$this->SetError(-1,'未进行搜索');
	}
	
	/**
	 * 按条件搜索，并生成Data数组
	 * 此方法不应在其他地方被调用
	 *
	 * @param string $sCondition
	 * @param string $sField
	 * @param integer $iPage
	 * @param string $sOrderField
	 * @param boolean $bDesc
	 * @return mixed
	 */
	protected function & FindAll($sCondition,$sField,$iPage = 1,$sOrderField = '',$bDesc = false,$iSkipCount = 0){
		$this->Condition = $sCondition;
		$this->FromString or $this->FromString = "$this->Table $this->AialsOfTable";
		
		if(!defined('__DONOT_PASES')){
			$sSqlCount = "SELECT count($this->KeyField) AS tcount FROM $this->FromString WHERE $this->Condition";
			$aResult = $this->oDB->getOne($sSqlCount);
			$this->TotalCount = $aResult['tcount'];
		}else{
			$this->TotalCount = 0;
		}
		
		if ($this->Page = $iPage){
			if($this->InfoType == 'object' && !$this->ExtendMode ) $sField = $this->KeyField;
			$sSqlList = "SELECT $sField FROM $this->FromString WHERE $this->Condition";
			if ($sOrderField){
				$this->Order = $sOrderField;
				$sDesc = $bDesc ? 'Desc' : 'Asc';
				$sSqlList .= " ORDER BY $this->Order $sDesc";
			}
			if ($iPage < 0)
				!$iSkipCount or $sSqlList .= " LIMIT $iSkipCount";
			else
				$sSqlList .= " LIMIT " . (intval($this->Page - 1) * $this->PageSize + $iSkipCount) . ",$this->PageSize";
			$aResult = $this->oDB->getAll($sSqlList);
			if ($this->InfoType == 'array'){
				$this->Data = $aResult;
			}				
			else{
				if ($this->InfoClsName == ''){
					return $this->SetError($GLOBALS['iSysErrObjectNameNoSet']);
				}
				else{
					LoadClass($this->InfoClsName);
					
					if(!$this->ExtendMode ) //原方法处理
					{
						foreach($aResult as $aData){
							if ($this->SetKey)
								$this->Data[$aData[$this->KeyField]] = new $this->InfoClsName($aData[$this->KeyField],$this->ExtraInfo);
							else
								$this->Data[] = new $this->InfoClsName($aData[$this->KeyField],$this->ExtraInfo);
						}
					}
					else ///新增处理方法
					{
						foreach($aResult as $aData){
							if ($this->SetKey)
							    $this->Data[$aData[$this->KeyField]] = new $this->InfoClsName($aData,$this->ExtraInfo);
							else
							    $this->Data[] = new $this->InfoClsName($aData,$this->ExtraInfo );	
						}
					}
				}
			}
		}
		$this->PageCount = ceil($this->TotalCount / $this->PageSize);
		$this->Count = count($this->Data);
		$this->SetError(0);
		return $this->Data;
	}
	
	/**
	 * 设置表连接
	 *
	 * @param string $sJoinTable
	 * @param string $sJoinAlias
	 * @param string $sCondition
	 */
	function setJoin($sJoinTable,$sJoinAlias,$sCondition){
		$this->FromString = "$this->Table $this->AialsOfTable join $sJoinTable $sJoinAlias on $sCondition";
	}
}
