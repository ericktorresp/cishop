<?php
/**
 *  
 * 充值详情类
 * 	--单条信息 增 删 改 显示
 *
 * set()					更新一个充值记录表单 by ID
 * add()					增加一个充值记录历史表单 by ID
 * setStatus()				设置充值表单状态
 * _Error()
 *
 * @name	LoadInfo.php
 * @package payport_class
 * @version 0.1 3/9/2010
 * @author	Jim
 * 
 * 
**/

class model_pay_loadinfo extends model_pay_base_info
{
	/**
	 * 记录ID
	 * @var int
	 */
	public $Id;
	/**
	 * 特殊ID串
	 * @var string  9 lenght letters + substr(topproxy name,0,3)
	 */
	public $SpecName;
	/**
	 * 用户ID
	 * @var Int
	 */
	public $UserId;
	/**
	 * 用户名称
	 * @var string
	 */
	public $UserName;
	/**
	 * 充值方式 (支付接口ID)
	 * @var int
	 */
	public $LoadType;
	/**
	 *  充值金额
	 * @var float
	 */
	public $LoadAmount;
	/**
	 * 充值手续费
	 * @var float
	 */
	public $LoadFee;
	/**
	 * 手续费计算方式
	 *
	 * @var int  0/1  (0使用外扣法、1使用内扣法)
	 */
	public $FeeType;
	
	/**
	 * 充值金额Currency
	 * @var string
	 */
	public $LoadCurrency;
	/**
	 *  当前KEY码
	 *   当第三方支付具备使用自定义KEY码的情况 用以保存不同时间使用的KEY
	 * 			以保证可以任何时间更换KEY码，而更换前后的支付单不受影响
	 *	
	 * @var string
	 */
	public $TransKey;
	/**
	 * 送发SEND时间
	 * @var date
	 */
	public $TransTime;
	/**
	 * 接收到第三方平台返回的时间
	 * @var date
	 */
	public $RebackTime;
	/**
	 * 接收到的第三方平台返回信息
	 * @var string
	 */
	public $RebackNote;
	/**
	 * 充值交易状态
	 * @var int
	 */
	public $LoadStatus;
	/**
	 * 掉单处理者ID
	 * @var int
	 */
	public $LostTodo;
	/**
	 * 掉单处理者名称
	 * @var string
	 */
	public $LostTodoUser;
	/**
	 * 掉单处理时间
	 * @var date
	 */
	public $LostTodoTime;
	/**
	 * 掉单处理方式 (处理之前充值单的状态)
	 * @var int
	 */
	public $LostStatus;
	/**
	 * payment请求二次效验ID
	 *  提供二次效验的平台使用 eg:支付请求提交之后Ecapay返回的效验ID  
	 * @var string
	 */
	public $TransID;
	/**
	 * payment请求二次效验KEY
	 *  提供二次效验的平台使用 eg:支付请求提交之后Ecapay返回的效验KEY  
	 * @var string
	 */
	public $ValidationKey;
	/**
	 * 操作逻辑锁 0未锁 1已锁
	 *	(阻止多进程操作同一行数据)
	 * @var int
	 */
	public $IsLock;
	/**
	 * 支付接口名称 (冗余数据)
	 * @var string
	 */
	public $PayName;
	/**
	 * 支付接口功能限定(后台管理程序使用)
	 * @var int 
	 */
	public $PayAttr; 
	
	//附加 由 PatAttr 运算得出以下四项
	/* 充值 */
	public $PayAttrLoad;
	/* 提现 */		
	public $PayAttrDraw;
	/* 批量提现 */
	public $PayAttrDrawlist;
	/* 查询 */
	public $PayAttrQues;
	/* 人工提现 */
	public $PayAttrDrawhand;
	/**
	 * 支付接口分账户
	 * @var unknown_type
	 */
	public $AccId;
	/**
	 * 支付接口分账户名称(财务使用)
	 * @var string
	 */
	public $AccName;
	/**
	 * 类配置项： 
	 * 使用文件锁方式时必须为一个有效的目录
	 *  (存放文件方式LOCK的目录,路径后面必须有/结尾)
	 * @var string(DIR)
	 */
	public $LockDir='./../../../_data/loadlock/';
	
	/**
	 * 配置型 
	 *
	 * @var bool 是否打开文件日志追踪记录
	 */
	public $DEBUG;
	/**
	 * 配置型:
	 *	记录文本形日志的目录
	 * @var string(DIR)
	 */
	public $LogDir;
	/**
	 * 自定义md5效验码
	 *   md5(userid + amount + payportid + payaccountid + transtime)
	 * @var md5string
	 */
	public $Md5Code;
	/**
	 * 保存的充值发起时的SITEID 
	 *	(1topay 同一账户具备不同的SITEID, 为新旧都可使用,充值发起时保存这一数据)
	 * @var string   
	 */
	public $SaveSiteId;
	/**
	 * 表名
	 * @var string
	 */
	protected $TableName='online_load';
	
	/**
	 * 存档表名
	 *  (今日)
	 * @var string
	 * 
	 */
	protected $HistoryTable;
	
	/**
	 * 归档日期方式间隔设置,日 d、星期 w、月 m
	 * @var string
	 */
	public $PigeonholeSet;
	
	/**
	 * 获取单条信息
	 * 
	 * @param int 		$iKey		关键字
	 * @param string 	$iKeyMark	字段名
	 * @param int		$iStatus	状态值
	 * 
	 * @return array()
	 */
	public function __construct($iId=null,$iStatus=1){
		parent::__construct();
		
		if ( is_numeric($iId) && ($iId >= 0) ){
			$sSqlAddWhere = ' 1';
			
			if ($iId) {
				$this->Id = $iId;
				$sSqlAddWhere .= ' AND `id`='.$this->Id;
			}
			
			if ($iStatus != '-1') $sSqlAddWhere .= ' AND `load_status`='.$iStatus;
			
			$sSql = 'SELECT * FROM `'.$this->TableName.'` WHERE '.$sSqlAddWhere;
			$aTmpData = $this->oDB->getOne($sSql);
			// if( empty($aTmpData) ) die ('Error: Run stop');
			if ( isset($aTmpData['id']) ) $this->Id = $aTmpData['id'];
			if ( isset($aTmpData['spec_name']) ) $this->SpecName = $aTmpData['spec_name'];
			if ( isset($aTmpData['user_id']) ) $this->UserId 	= $aTmpData['user_id'];
			if ( isset($aTmpData['user_name']) ) $this->UserName = $aTmpData['user_name'];
			if ( isset($aTmpData['load_type']) ) $this->LoadType = $aTmpData['load_type'];
			if ( isset($aTmpData['load_amount']) ) $this->LoadAmount 	= $aTmpData['load_amount'];
			if ( isset($aTmpData['load_fee']) ) $this->LoadFee 		= $aTmpData['load_fee'];
			if ( isset($aTmpData['fee_type']) ) $this->FeeType 		= $aTmpData['fee_type'];
			if ( isset($aTmpData['load_currency']) ) $this->LoadCurrency = $aTmpData['load_currency'];
			if ( isset($aTmpData['trans_key']) ) $this->TransKey 	= $aTmpData['trans_key'];
			if ( isset($aTmpData['trans_time']) ) $this->TransTime 	= $aTmpData['trans_time'];
			if ( isset($aTmpData['reback_time']) ) $this->RebackTime 	= $aTmpData['reback_time'];
			if ( isset($aTmpData['load_status']) ) $this->LoadStatus 	= $aTmpData['load_status'];
			if ( isset($aTmpData['lost_todo']) ) $this->LostTodo = $aTmpData['lost_todo'];
			if ( isset($aTmpData['lost_todo_user']) ) $this->LostTodoUser = $aTmpData['lost_todo_user'];
			if ( isset($aTmpData['lost_todo_time']) ) $this->LostTodoTime = $aTmpData['lost_todo_time'];
			if ( isset($aTmpData['lost_status']) ) $this->LostStatus 	= $aTmpData['lost_status'];
			if ( isset($aTmpData['trans_id']) ) $this->TransID 		= $aTmpData['trans_id'];
			if ( isset($aTmpData['validation_key']) ) $this->ValidationKey = $aTmpData['validation_key'];
			if ( isset($aTmpData['islock']) ) $this->IsLock 	= ($aTmpData['lock_markstr'] > 0) ? true : false;
			if ( isset($aTmpData['pay_name']) ) $this->PayName 	= $aTmpData['pay_name'];
			if ( isset($aTmpData['pay_attr']) ) $this->PayAttr 	= $aTmpData['pay_attr'];
			if ( isset($aTmpData['acc_aid']) ) $this->AccId 	= $aTmpData['acc_aid'];
			if ( isset($aTmpData['acc_name']) ) $this->AccName 	= $aTmpData['acc_name'];
			if ( isset($aTmpData['md5code']) ) $this->Md5Code		= $aTmpData['md5code'];
			if ( isset($aTmpData['save_siteid']) ) $this->SaveSiteId	= $aTmpData['save_siteid'];
			
		}
		else
		{
			$this->HistoryTable = $this->TableName.'_history_'.date('Ymd');
		}
		
	}
	
	/**
	 * 根据传入string参数,获取支付单的唯一ID
	 * @param string  替代ID的字符串
	 * @return int 	uniqueID
	 */
	public function getIdBySpecName($sStr){
		if ( empty($sStr) ) return false;
		if ( !eregi("[0-9a-zA-Z]{12}",$sStr) ) return false;
		
		$sSql = "SELECT `id` FROM `$this->TableName` WHERE `spec_name`='$sStr' ORDER BY `id` LIMIT 1";
		
		$aResult = $this->oDB->getOne($sSql);
		if ($this->oDB->errno() > 0){
			return false;
			
		}else{
			return $aResult['id']; 
		}
		
	}
	/**
	 * 设置,更新
	 * @return bool
	 */
	public function set(){
		
		if ( $this->RebackNote == '|' ) $this->RebackNote = '';
		if ($this->RebackTime) $aTmpDate['reback_time'] = $this->RebackTime;
		if ($this->RebackNote) $aTmpDate['reback_note'] = $this->RebackNote;
		if ($this->LoadStatus) $aTmpDate['load_status'] = intval($this->LoadStatus);
		if ($this->LostTodo) $aTmpDate['lost_todo'] = intval($this->LostTodo);
		if ($this->LostTodoUser) $aTmpDate['lost_todo_user'] = intval($this->LostTodoUser);
		if ($this->LostTodoTime) $aTmpDate['lost_todo_time'] = $this->LostTodoTime;
		if ($this->LostStatus) $aTmpDate['lost_status'] = intval($this->LostStatus);
		$sCond = '';
		$sCond = " `id`=$this->Id AND `load_status`=0";
		
		return $this->_update($aTmpDate,$sCond);
	}
	
	
	/**
	 * 插入初期数据 在线充值表
	 * @param  $sSiteId 	1topay生成的SiteID
	 * @return last insert id 
	 */
	public function initLoadRecord($sSiteId=NULL){
		//自定义md5效验码 md5(userid + amount + payportid + payaccountid + transtime);
		//$p10_note.$p03_payamount.$oOL->LoadType.$oOL->AccId.$oOL->TransTime
		
		$iCodeMount = number_format($this->LoadAmount,2,'.','');
		$sMd5code = md5(intval($this->UserId).'A'.$iCodeMount.'B'.intval($this->LoadType).'C'.intval($this->AccId).'D'.$this->TransTime);
		if ( empty($this->UserId) || empty($iCodeMount) || empty($this->LoadType) 
			|| empty($this->AccId) || empty($this->TransTime) )
		{
			$sMd5code = '';
		}
		$aTempData = array( 'spec_name' => $this->SpecName,
							'user_id'  => intval($this->UserId),
                            'user_name'  => $this->UserName,
                            'load_type'  => intval($this->LoadType),
                            'load_amount'=> $this->LoadAmount,
							'load_fee'=> $this->LoadFee,
							'fee_type' => $this->FeeType,
							'load_currency'=> $this->LoadCurrency,
                            'trans_time' => $this->TransTime,
                            'load_status'=> intval('0'),
							'pay_name' => $this->PayName,
							'pay_attr' => $this->PayAttr,
							'acc_aid' => intval($this->AccId),
							'acc_name' => $this->AccName,
							'md5code' => $sMd5code,
							'save_siteid' => ($sSiteId != NULL) ? $sSiteId : '',
							'utime' => date('Y-m-d H:i:s')
                         );
        if ( $this->_chkValue( $aTempData, array('load_fee','load_status','save_siteid','fee_type') ) )
        {
            return $this->_insert($aTempData);
        }
        else
        {
        	return false;
        }
        
	}
	
	
	/**
	 * 新增,
	 * @return bool or int
	 */
	public function add(){
		$aTempData = array( 'spec_name' => $this->SpecName,
							'user_id'    => intval($this->UserId), 
                            'user_name'  => $this->UserName, 
                            'load_type'  => intval($this->LoadType),
                            'load_amount' => $this->LoadAmount,
							'load_fee'=> $this->LoadFee,
							'fee_type' => $this->FeeType,
							'load_currency' => $this->LoadCurrency,
                            'trans_key'  => $this->TransKey,
                            'trans_time'  => $this->TransTime,
							'reback_time' => $this->RebackTime,
							'reback_note' => $this->RebackNote,
							'load_status' => intval($this->LoadStatus),
							'lost_todo'   => intval($this->LostTodo),
							'lost_todo_user'   => $this->LostTodoUser,
							'lost_todo_time' => $this->LostTodoTime,
							'lost_status' => intval($this->LostStatus),
							'pay_name' => $this->PayName,
							'pay_attr' => intval($this->PayAttr),
							'acc_aid' => intval($this->AccId),
							'acc_name' => $this->AccName,
							'utime' => date('Y-m-d H:i:s')
                         );
         
		if ( $this->_chkValue( $aTempData, 
		array('load_status','lost_todo','lost_todo_user','lost_todo_time','lost_status','trans_key','trans_time') 
		) )
		{
            return $this->_insert($aTempData);
        }
        else
        {
        	return false;
        } 
        
    }
	
	
	/**
	 * 更改 LoadStatus (暂未使用)
	 *@return bool
	 */
	public function setStatus(){
		
		$aTmpDate = array('lost_status' => intval($this->LostStatus),'utime' => date('Y-m-d H:i:s'));
		
		//记录日志
		return $this->_update($aTmpDate);
	}
	
	
	/**
	 * 删除记录 
	 * 	物理删除小于给定ID或时间点的记录
	 * @return bool
	 */
	public function delete(){
		return true;
	}
	
	/**
	 *  冗余数据更新
	 */
	public function redundance($aArr=array()){
		
		switch ($aArr[0]){
			case 'pp_name':
				$aTmpDate['pay_name'] = $aArr[2];
				$sCond = " pay_name LIKE '".$aArr[1]."'";
			break;
			case 'acc_name':
				$aTmpDate['acc_name'] = $aArr[2];
				$sCond = " acc_name LIKE '".$aArr[1]."'";
				break;
			default:
				return false;
		}
		
		
		$aTmpDate['utime'] = date('Y-m-d H:i:s');
		
		return $this->_update($aTmpDate,$sCond);
	}
	
	
	/**
	 * 执行 update SQL
	 *
	 * @param array $aTmpDate  更新的数据数组
	 * @return bool
	 */
	private function _update($aTmpDate,$sCond=false){
		
		if ($sCond === false)$sCond = ' id = '.$this->Id;
		
		if ( $this->oDB->update($this->TableName,$aTmpDate,$sCond) === 1 )
		{
            return true;
        }
        else
        {
        	return false;
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
	 * 获取用户SESSION data
	 *
	 * @param  int $iUserId
	 * @return string
	 */
	public function getUserSessionData(){
		if (empty($this->UserId) || !is_numeric($this->UserId)) return false;
		$sSql = 'SELECT `sdata` FROM `sessions` WHERE userid='.$this->UserId;
		$aData = $this->oDB->getOne($sSql);
		return $aData['sdata'];
	}
	
	
	/**
 	* 分解 sessions.表得到的sdata数据
 	*
 	* @param string $s   		sdata 字串
 	* @param string $getKey	指定获得的单项键值,
 	* @return string
 	*/
	public function getUserCurrentDomian($s,$getKey = 'domain'){
		$s1 = explode(';',$s);
		foreach ($s1 as $k)
		{
			$s2 = explode('|',$k);
			if ($s2[0] == $getKey)
			{
				$s3 = explode(':',$s2[1]);
				list($type,$lenght,$string) = $s3;
				break;		
			}
		}
		
		$string = substr($string,1,-1);
		return $string;	
	}
	
	/**
 	* 	结束并输出 
 	* 		当 _SERVER[query_string] 有值时，认为是浏览器查看，需给出提示
 	* 		否则只输出1000，返回给1topay回调程序识别
 	* @param string $str
 	* @bGoto false => 程序继续运行
 	*/
	public function stopPageRun( $sStr = '1000' , $bGoto=true){
		$aCloseLink = array( 0 => array('url' => 'close'),1 => array('url' => 'index.php?controller=report&action=bankreport') );
		
		$sAutoClose = "<script language=javascript>
					<!--
					function clock()
					{
						i--
						document.title='本窗口将在' + i + '秒后自动关闭!';
						if(i>0) setTimeout('clock();',1000);
						else self.close();
					}
					var i=6
					clock();
					//-->
					</script>";
		if ( ($_SERVER ['QUERY_STRING']) && (!$bGoto) )
		{
			$sUserSession = $this->getUserSessionData();
			if (empty ( $sUserSession )) 
			{
				$this->saveLogs ( $this->Id .':'.$this->SpecName.':读取用户session记录失败' );
				
			}
			else
			{
				
				$sUserDomian = $this->getUserCurrentDomian($sUserSession);
				if ( strlen($sUserDomian) > 6 ){
					$sUrl = 'http://'.$sUserDomian.'/index.php?controller=security&action=onlineload&ReturnData=' 
						.base64_encode($sStr) . '&PPN=' . base64_encode(base64_encode($this->PayName))
						. '&msg=' . base64_encode($sStr) . '&RST=' . base64_encode($this->LoadStatus);
					//有用户浏览,且安全,跳回框架回显用户
					header ( 'Location: ' . $sUrl  );
					exit;
				}else{
					echo $sAutoClose;
					echo "<p style='text-align:center;font-size:15px;color:blue'>$sStr</p>";
					
					exit;
				}
		 		
			}
			
		}
		elseif ($_SERVER ['QUERY_STRING'])
		{
			echo $sAutoClose;
			//sysMsg($sStr,0,$aCloseLink);
			echo "<p style='text-align:center;font-size:15px;color:blue'>$sStr</p>";
			exit;
		}
		else
		{
			echo 1000;
			exit;
		}
		
	
	}
	
	/**
 	* 数组转字串
 	*
 	* @param array $arr
 	* @return str
 	*/
	public function arraytostr(&$arr){
		$sStr = '';
		foreach ($arr AS $sKey => $sValue)
		{
			$sStr .= $sKey.'='.$sValue;
			$sStr .= '&';
		}
		return substr($sStr,0,-1);
	}
	
	
	/**
	 * 检测数组变量，每个键值有效
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
	 * 逻辑锁操作
	 *
	 * @param string $action 	lock 加锁 unlock 解锁 (status 查看锁状态/未用)
	 * @param string $iThread	MYSQL线程ID
	 * @param string $sType 	锁方式,db 数据表行逻辑锁, file 使用文件锁 (passport/_tmp/loadlock)
	 * 
	 * @return bool
	 */
	public function Lock($action='lock',$sType='db'){
		if ($sType == 'db')
		{
			return $this->_payDbLock($action);
		}
		else
		{
			return $this->_payFileLock($action);
		}
		
	}
	
	/**
	 * 获取锁状态
	 * @param int	id支付单
	 * @return bool	1 Lock, 0 unLock 
	 */
	private function _getIsLock(){
		$re['islock'] = '';
		$sSql = "SELECT `islock` FROM `$this->TableName` WHERE `id`=$this->Id";
		$re = $this->oDB->getOne($sSql);
		return ($re['islock'] > 0) ? true : false;
	}
	
	
	/**
	 * 数据表方式逻辑锁
	 * @param string 	锁操作指令
	 */
	private function _payDbLock($lock='lock'){
				
		$iCurrTreadId = $this->oDB->getThreadId();
		if ( $iCurrTreadId <= 0 ) return false;
	
		if ($lock == 'lock')
		{
			$aTmpDate['islock'] = $iCurrTreadId;
			$sCond = " `id`=$this->Id AND `islock`=0";
		}
		elseif($lock == 'unlock')
		{
			$aTmpDate['islock'] = 0;
			$sCond = " `id`=$this->Id AND `islock`=$iCurrTreadId";
		}
		else
		{
			return false;
			//return $this->_getIsLock();
		}
		$aTmpDate['utime'] = date('Y-m-d H:i:s');
		$this->oDB->update($this->TableName,$aTmpDate,$sCond);
		if( intval($this->oDB->affectedRows()) == intval(1) ) 
		{
			$this->IsLock = 1;
			return true;	
			
		}
		else
		{
			$this->IsLock = 0;
			return false;
		}
		
	}
	
	
	/**
 	* 文件方式逻辑锁  (未使用)
 	*
 	* @param string $loadid 文件锁名称,使用支付表单ID
 	* @param string $lock	 操作状态，lock锁定 unlock解开 其他值:查询是否锁
 	* @return bool
 	*/
	private function _payFileLock($lock='lock'){
		$loadid = $this->Id;
		$lockdir = $this->LockDir;
		if ( !file_exists($lockdir) )
		{
			if ( !mkdir($lockdir,0777) )
			{
				return false;
			}
		}
		
		if ( empty($loadid) ) return false;
		$lockfile =  $lockdir.$loadid;
		$locker = $this->LockMarkStr; 
		if ($lock == 'lock')
		{
			//获取锁状态，以得到运行权
			if (file_exists($lockfile))
			{
				return false;
			}
			else
			{
				//写入锁
				file_put_contents($lockfile,$locker);
				return true;
			}
			
		}
		elseif($lock == 'unlock')
		{
			//解锁,只有自己加的锁才能解开
			if (file_exists($lockfile))
			{
				return false;
			}
			else
			{
				$oldlocker = file_get_contents($lockfile);
				if ($oldlocker == $lockfile)
				{
					//可以解锁
					return unlink($lockfile);
				}
				else
				{
					return false;
				}
				
			}
		
		}
		else
		{
				//仅检查锁状态，无锁为真
			if (file_exists($lockfile))
			{
				return false;
			}
			else
			{
				return true;
			}
		
		}
	
	}
	
	/**
	 * 获取安全锁 标记字串 (MYSQL线程ID)
	 *
	 * @return string  char(8)
	 */
	public function getMarkStr(){
		return $this->oDB->getThreadId();
    } 
    
	/**
 	* 写DEBUG日志
 	*/
	function saveLogs($sStr){
		if (eregi(':',$sStr))
		{
			$aL = explode(':',$sStr);
			$iL = $aL[0];
			$sLstr = $aL[1];
			$sStr = $aL[2]; //.' '.date('Y-m-d H:i:s'); 
		}
		else
		{
			$sStr = $sStr;
		}

		$oLogs =  new model_pay_loadlogs();
		$oLogs->PaymentType	 = $this->LoadType;
		$oLogs->PaymentAccId = $this->AccId;
		$oLogs->PaymentId = $iL ? intval($iL) : intval($this->Id);
		$oLogs->PaymentIdStr = $sLstr ? daddslashes($sLstr) : $this->SpecName;	
		$oLogs->LogInfo = $sStr.'|'.$this->LoadAmount.' '.$this->LoadCurrency.'|status:'.$this->LoadStatus;
		$oLogs->LogTime = date('Y-m-d H:i:s');
		$oLogs->record();
		
		// DEBUG模式下同时写一份记录到文件
		if ( $this->DEBUG )
		{
			$logs = $this->LogDir;
			$sStr = "\r\n\r\n".date('Y-m-d H:i:s')." ".$sStr;
			file_put_contents($logs,$sStr,FILE_APPEND);
		}
		
	}
	

	/**
 	* 将回调接收数据写入文本文件存储
 	*/
	function saveReceiveDate($str){
		if (eregi(':',$str))
		{
			$aL = explode(':',$str);
			$iL = $aL[0];
			$sLstr = $aL[1];
			$sStr = $aL[2].' '.date('Y-m-d H:i:s'); 
		}
		else
		{
			$sStr = $str;
		}
		
		// 按天分目录, 分为10个文件保存
		/*
		if ($iL){
			$iln = intval( $iL % 10 );
		}else{
			$iln = '000';
		}
		$logname = $iL ? 'RD'.$iln.'.log' : 'RD'.$iln.'.log';
		$logs = $this->LogDir.date('Ymd');
		$logfile = $logs .'/'. $logname;
		*/
		// 按天记录到不同文件
		$logs = $this->LogDir;
		$logfile = $logs . date('Ymd') . '.log';
		
		if ( !file_exists($logs) ) 
		{
			if ( !mkdir($logs, 0777, true) OR  !chmod($logs, 0777) ) return -3;
		}
		
		$re = @file_put_contents($logfile, "\r\n\n $iL $sLstr ".date('Y-m-d H:i:s')."\n$sStr", FILE_APPEND);
		
		return $re>10 ? true : -2;
		
	}
	
	
	/**
	 *  数据归档
	 * 		将现有数据按日期条件转存到归档数据表, 并删除现表中已被转存数据
	 */
	public function pigeonholeData(){
		
		// debug 数据文件名
		$sDebugFile = realpath ( dirname ( __FILE__ ) . '/../../../' ) . '/_tmp/data_receive/sql_.logs';
		
		
		// 检查是否今日是否已操作
		if ( $this->_checkSaveasalready() )  return false;
		
		// 创建归档表
		if ( $this->_createHistroyTable() === false)
		{
			return false;
		}
		
		// 开启事务
		$this->oDB->doTransaction();
		
		// 选择数据
		$sStartTime = date('Y-m-d 02:20:00', strtotime('-1 day'));
		$sStopTime = date('Y-m-d 02:19:59', strtotime('now'));
		
		$sSql = 'SELECT * FROM `'.$this->TableName.'` WHERE `trans_time` BETWEEN \''.$sStartTime.'\' AND \''.$sStopTime.'\'';
		$aResult = $this->oDB->getAll($sSql);
		
		if ( count($aResult) <= 0 ) 
		{
			return false;
		}
		
		// 插入
		
		$bCheck = false;
		$x=0;
		$ttl=3;
		$sKeystr = $sValuestrrows = '';
		$sSql0 = 'INSERT INTO '.$this->HistoryTable;

		foreach ( $aResult AS $aS)
		{
			$i = 0;
			$sValuestr = '';

			while (list($key, $val) = each($aS)) 
			{
				if ($x == 0) $sKeystr .= $key.',';
				if ($i == 0)
				{
					$sValuestr = 'NULL';
				}
				else
				{
					$sValuestr .= is_numeric($val) ? $val : '\''.$val.'\'';
				}
				$sValuestr .= ',';
				
				$i++;
			}
			
			$sValuestrrows .= '(' . substr($sValuestr,0,-1) . ')';
			if ($x < $ttl) $sValuestrrows .= ',';

			if ($x == $ttl) 
			{
				$x = 0;
				$sInsert = $sSql0.' (' . substr($sKeystr,0,-1) . ') VALUES '.$sValuestrrows;
				$this->oDB->query($sInsert);
				// DEBUG,将所有插入INSERT写入文件
				if ($this->DEBUG) file_put_contents($sDebugFile, "\r\n ".$sInsert, FILE_APPEND);
				
				if ($this->oDB->errno() > 0)
				{
					$this->_dropHistoryTable();
					$this->oDB->doRollback();
					return false;
				}
				$sValuestrrows = '';
			}
			$bCheck = true;
			$x++;
		}
		
		if ($x > 0)
		{
			
			$this->oDB->query($sInsert);
			
			if ($this->oDB->errno() > 0)
			{
				$this->_dropHistoryTable();
				$this->oDB->doRollback();
				return false;
			}
			$bCheck = true;
		}
		
		if ($bCheck === true)
		{
			$this->oDB->doCommit();
			return true;
		}
		else
		{
			$this->_dropHistoryTable();
			$this->oDB->doRollback();
			return false;
		}
		
		// 删除主表五天前数据
		
	}
	
	/**
	 * 生成一个发往支付平台的随机字串(唯一ID的替代)
	 * @param  $sTpn 	string 	topproxy name
	 * @return string 	12 lenght
	 *  6/3/2010
	 */
	public function makeSpecName($sTpn=''){
		if ( empty($sTpn) ) {
			$sTpn = 'AAA';
		}else{
			$sTpn = substr($sTpn,0,3);
		}
		
		return $this->_makeRandString().strtoupper($sTpn);
	}
	
	/**
	 * 生成随机字串
	 * @param int lenght limit
	 * @return string
	 * 6/3/2010
	 */
	private function _makeRandString($iLen = 9){
		$sTmpStr = '0123456789qAwBeCrDtEyFuGiHoIpJlKkLjMhNgOfPdQsRaSzTxUcVvWbXnYmZ';
		$aTmp = str_split($sTmpStr);
		$sNew = '';
		$aKey = array_rand($aTmp,9);
		foreach ($aKey AS $aA => $iVal){
			$sNew .= $aTmp[$iVal];
		}
		
		$iCheck = $iLen - strlen($sNew);
		
		if ( $iCheck > 0 ){
			
			for ($i=0; $i<$iCheck; $i++){
				$sNew .= $aTmp[$iCheck];
			}
			return $sNew;
			
		}elseif( $iCheck < 0 ){
			return substr($sNew,0,9);
			
		}else{
			return $sNew;
		}
	}
	
	/**
	 * 创建充值记录归档表 (建立新表  onlineload_history_YYYYMMDD )
	 */
	private function _createHistroyTable(){
		
		// 获取表结构 (需数据库访问账户具备SHOW CREATE权限)
		$sSql = 'SHOW CREATE TABLE `'.$this->TableName.'`';
		$sResult = $this->oDB->getOne($sSql);
		if ($this->oDB->errno() > 0 ) return false;
		 
		if ($sResult)
		{
			// 删除已创建的
			/*$this->oDB->query("DROP TABLE IF EXISTS $this->HistoryTable");
			if ($this->oDB->errno() > 0) return false;*/

			// 创建新表
			$sCreateSql = str_replace('`'.$this->TableName.'`', '`'.$this->HistoryTable.'`', $sResult['Create Table']);
			$sCreateSql = preg_replace("/AUTO_INCREMENT=[0-9]{1,}/",'AUTO_INCREMENT=1', $sCreateSql);
			
			$this->oDB->query($sCreateSql);
			
			return ($this->oDB->errno() > 0) ? false : true;
			
		}
		else
		{
			return false;
		}
		
	}
	
	
	/**
	 * 删除刚创建的历史存档表
	 *   -- 用于事务回滚时
	 */
	private function _dropHistoryTable(){
		$sSql = 'DROP DATABASE '.$this->HistoryTable;
		$this->oDB->query($sSql);
		
		return ($this->oDB->error() > 0) ? false : true;
		
	}
	
	
	/**
	 * 检查今日存档数据表是否已生成过
	 */
	private function _checkSaveasalready(){
		$sSql = 'SHOW TABLES LIKE '.$this->HistoryTable;
		$aRe = $this->oDB->query($sSql);
		
		return ( count($aRe) >= 1 ) ? true : false;
		
	}
	
	
	/*  class end  */
}