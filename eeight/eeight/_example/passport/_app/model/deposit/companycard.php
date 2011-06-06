<?php
/*
 * EMAIL充值 公司卡信息类
 * 
 * 	完成对用户＆卡表的信息管理 (批量绑定，单个绑定，单个取值，VIP名单更新，黑名单更新)
 * 	
 * @name CompanyCard.php
 * @package mailDeposit
 * @version 0.1
 * @since 9/1/2010
 * @author Jim
 * 
 * 
 * 
 * add 				添加名单(黑名单/VIP白名单/普通用户)
 * addUser			插入用户基本数据 (初始化)
 * addSingleUser  	添加用户
 * userValidCheck 	关系表中是否有该用户的有效性检查
 * del 				删除名单(清空相关列数据)
 * disable 			禁用用户
 * enable 			启用用户
 * fillarray
 * getRelationList 	获取列表（A frame Controller层调用模式）
 * getList			获取列表
 * getUserList		从用户表获取的用户列表，作为基准列表使用
 * getCard 			获取用户对应卡信息
 * reduces 			冗余操作、状态
 * reduceUpdate 	冗余兼容，管理后台修改 卡户名、EMAIL、财务用名时
 * syncUser			与USER表同步
 * _differTime 		时间差比较
 * _setActive 		操作关系状态(禁启用方法调用)
 * _delUser			删除用户&卡表中的某多余用户信息 (物理删除)
 *  
*/

class model_deposit_companycard extends model_pay_base_info
{
	/*
	 * 受付银行ID
	 */
	public $BankId;
	/*
	 * 用户ID(调用时)
	 */
	public $UserId;
	/*
	 * 用户登录名(调用时)
	 */
	public $UserName;
	/*
	 * 公司卡户名 普通卡
	 */
	public $NormalDepositName;
	/*
	 * 公司卡EMAIL 普通卡
	 */
	public $NormalDepositMail;
	/*
	 * 公司卡ID （分账户ID） 普通卡
	 */
	public $PayAccId;
	/*
	 * 是否VIP标记 1:是 0:不是
	 */
	public $IsVip;
	/*
	 *  VIP卡开始时间
	 */
	public $VipStartTime;
	/*
	 * VIP卡到期日
	 */
	public $VipExpriy;
	/*
	 * 公司卡户名 VIP卡
	 */
	public $VipDepositName;
	/*
	 * 公司卡EMAIL VIP卡
	 */
	public $VipDepositMail;
	/*
	 * 公司卡ID （分账户ID） VIP卡
	 */
	public $VipPayAccId;
	/*
	 * 是否黑名单 1:是
	 */
	public $IsBlack;
	/*
	 * 公司卡户名 黑名单
	 */
	public $BlackDepositName;
	/*
	 * 公司卡EMAIL 黑名单
	 */
	public $BlackDepositMail;
	/*
	 * 公司卡ID （分账户ID） 黑名单
	 */
	public $BlackPayAccId;

	/* 以下属性在运行中生成,默认为空 */
	
	/**
	 * 用户有效的财务用名 (应当与后面两个值对应)
	 */
	public $AccName='';
	/*
	 * 用户有效的户名
	 */
	public $DepositName='';
	/*
	 * 用户有效的EMAIL
	 */
	public $DepositMail='';
	/*
	 * 用户有效的卡ID  （支付接口分账户ID）
	 */
	public $PayCardId='';
	/*
	 * 用户类型 (0普通 1黑名单 2VIP )  [非数据表字段存储]
	 */
	public $UserType='';
	
	

	/*
	 * 用户＆卡对应关系表
	 */
	private $TableName = 'user_deposit_card';
	
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	/**
	 * 添加黑名单 或 VIP白名单 或 普通用户
	 * 	(更新 失效时间 以及标记位为1 )
	 * 
	 * @param string	$sLogo		opblack/opvip	finblack/finvip/finnormal (标记, 更新的是黑名单或VIP,部分权限区分; fin管理银行卡,op管理用户类型)
	 * @param array		$aUser		经controller整理之后的名单列表数组
	 * @param datetime	$fExpriy	计算好的到期时间戳
	 * lvproxyid
	 * @return bool  	true,false 成功/失败
	 * 
	 */
	public function add($sLogodef='', $aUser=array(), $fExpriy=NULL )
	{
		if ( array_search($sLogo, array('opblack','opvip','finblack','finvip','finnormal','')) === FALSE ) return FALSE;
		if ( count($aUser) < 1 ) return FALSE;
		if ( is_null($fExpriy) ) $fExpriy = date('Y-m-d 02:20:00', strtotime('+15 day') );
		$fStartTime =  date('Y-m-d 02:20:00');
		
		$this->oDB->doTransaction();
		$sSql = '';
		foreach ($aUser AS $aU)
		{
			$sLogo = empty($sLogodef) ? $aU['logo'] : $sLogodef;  

			if ( is_string($aU) )
			{
				$sUser = daddslashes($aU);
				$iUserId = intval($aU);
			}
			else 
			{
				if ( empty($aU['logo']) && empty($aU['black_deposit_name']) 
					&& empty($aU['vip_deposit_name']) && empty($aU['deposit_name']) 
					&& empty($aU['username']) && empty($aU['userid']) )
				{
					$this->oDB->doRollback();
					return FALSE;
				}
				$sUser 			= daddslashes($aU['username']);
				$iUserId 		= intval($aU['userid']);
				$iDepositBankId = intval( $aU['depositbankid'] );
				if ($sLogo == 'finblack')
				{
					$sBlackAccName 		= daddslashes($aU['black_accname']);
					$sBlackDepositName 	= daddslashes($aU['black_deposit_name']);
					$sBlackDepositMail 	= daddslashes($aU['black_deposit_mail']);
					$iBlackPayAccId 	= intval($aU['black_payacc_id']);
				}
				if ($sLogo == 'finvip')
				{
					$sVipAccName 		= daddslashes($aU['vip_accname']);
					$sVipDepositName 	= daddslashes($aU['vip_deposit_name']);
					$sVipDepositMail 	= daddslashes($aU['vip_deposit_mail']);
					$iVipPayAccId 		= intval($aU['vip_payacc_id']);
				}
				if ($sLogo == 'finnormal')
				{
					$sAccName 		= daddslashes($aU['accname']);
					$sDepositName 	= daddslashes($aU['deposit_name']);
					$sDepositMail 	= daddslashes($aU['deposit_mail']);
					$iPayAccId 		= intval($aU['payacc_id']);
				}
				
			}
				// 区分部门管理权限  添加账户不涉及银行卡信息,管理银行卡不涉及用户类型
				switch ($sLogo)
				{
					// 运营、客服权限 (账户)
					case 'opblack':
						$sSql = "UPDATE `$this->TableName` SET `isblack`=1,`black_starttime`='$fStartTime'  WHERE `username`='".daddslashes($sUser)."'";
						break;
					case 'opvip':
						$sSql = "UPDATE `$this->TableName` SET `isvip`=1, `vip_starttime`='$fStartTime', `vip_expriy`='$fExpriy' WHERE `username`='".daddslashes($sUser)."'";
						break;
					// 财务权限  银行卡 (从卡关系管理中调用)
					case 'finblack':
						$sSql = "UPDATE `$this->TableName` SET `black_accname`='$sBlackAccName',`black_deposit_name`='$sBlackDepositName', `black_deposit_mail`='$sBlackDepositMail', `black_payacc_id`=$iBlackPayAccId WHERE `userid`=$iUserId AND `bankid`=$iDepositBankId";
						break;
					case 'finvip':
						$sSql = "UPDATE `$this->TableName` SET `vip_accname`='$sVipAccName',`vip_deposit_name`='$sVipDepositName', `vip_deposit_mail`='$sVipDepositMail', `vip_payacc_id`=$iVipPayAccId WHERE `userid`=$iUserId AND `bankid`=$iDepositBankId";
						break;
					case 'finnormal':
						$sSql = "UPDATE `$this->TableName` SET `accname`='$sAccName', `deposit_name`='$sDepositName', `deposit_mail`='$sDepositMail', `payacc_id`=$iPayAccId WHERE ( FIND_IN_SET ('".$iUserId."',`user_tree`) > 0 OR `userid`=$iUserId ) AND `bankid`=$iDepositBankId";
						break;
					default:
						$sSql = FALSE;
						break;
						
				}
				
	 			if ($sSql === FALSE) 
	 			{
	 				$this->oDB->doRollback();
	 				return FALSE;
	 			}

	 			$this->oDB->query($sSql);
				unset($sLogo);	
				if ($this->oDB->errno() > 0)
				{
					$this->oDB->doRollback();
					return FALSE;
				}
			
		}

		$this->oDB->doCommit();
		return TRUE;
	}
	
	
	/**
	 * 插入用户基本数据 (初始化)
	 *
	 * @param array	需要新插入进用户＆卡关系表的用户列表 array(userid,username,usertree,userlevel,usertype,topagentid,agentid)
	 * 	
	 * @return  -1 		提交的用户列表无效
	 * 			-2 		有无效数据
	 * 			FALSE 	插入数据发生失败
	 * 			TRUE	成功同步基本数据(用户＆卡关系表 与 用户表 一致)
	 * 
	 * 	(提交的数组数据中任意一个用户的信息不满足必须字段条件要求，整次添加均无效)
	 */
	public function addUser($aUser = array() )
	{
		
		if ( count($aUser) < 1 ) return -1;
		
		$this->oDB->doTransaction();
		
		foreach ($aUser AS $aLU)
		{
			if ( !is_numeric($aLU['userid']) 
				|| !is_string($aLU['username']) 
				|| ($aLU['userlevel'] < 0)
				|| !is_numeric($aLU['usertype'])
				|| !is_numeric($aLU['topagentid']) 
				|| !is_numeric($aLU['agentid'])  )
				{
					$this->oDB->doRollback();
					return -2;
				}
				
			$aData = array(
				'userid' 	=> intval($aLU['userid']), 
				'username' 	=> daddslashes($aLU['username']),
				'user_tree' => daddslashes($aLU['usertree']),
				'user_level'=> intval($aLU['userlevel']),
				'user_type'	=> intval($aLU['usertype']),
				'topagentid'=> intval($aLU['topagentid']),  
				'agentid'	=> intval($aLU['agentid']),  
				'isactive' 	=> 0
			);
			// 支持批量增加时同时赋值普通卡信息
			if ( !empty($aLU['depositname']) && !empty($aLU['depositmail']) && !empty($aLU['accname']) )
			{
				$aData['accname'] = daddslashes($aLU['accname']);
				$aData['deposit_name'] = daddslashes($aLU['depositname']);
				$aData['deposit_mail'] = daddslashes($aLU['depositmail']);
			}

			$this->oDB->insert($this->TableName, $aData);
			
			if ( $this->oDB->errno() > 0 )
			{
				$this->oDB->doRollback();
				return FALSE;	
			}
			
		}
		
		$this->oDB->doCommit();
		return TRUE;
	}
	

	
	/**
	 * 添加用户 (单个)
	 * 	 添加用户到EMAIL充值 用户＆卡关系表
	 * 
	 *@param array	$aParam 用户的基本信息
	 * 		数组中提交以下信息:
	 * 	array(
	 * 		bankid		受付银行ID				int
	 * 		userid		用户ID					int
	 * 		username	用户登录名				string
	 * 		usertree	用户树分支				string
	 * 		userlevel	用户层级 					int
	 * 		usertype	用户类型					int
	 * 		topagentid	用户所属总代ID				int
	 * 		agentid		用户上级代理ID				int
	 * 		depositname	普通用卡户名				string (用户的上级配置的普通卡,继承)
	 * 		depositmail 普通用卡MAIL名			string (同上)
	 * 		payaccid	普通用卡ID(支付接口分账户ID) int
	 * 		isactive	关系状态					int 0/1 (继承上级,并参考授权在线充提开放至级别的限定决定 状态)
	 * 		)
	 * 
	 *@return bool	false/ integer>0
	 * 
	 */
	public function addSingleUser($aParam)
	{
		if ( !is_array($aParam) || (count($aParam) < 3) ) return FALSE;
		
		//single user info
		$aData = array();
		
		if ( !is_numeric($aParam['bankid']) )
		{
			return FALSE;
		}
		else 
		{
			$aData['bankid'] = intval($aParam['bankid']);
		}
		
		if ( !is_numeric($aParam['userid']) )
		{
			return FALSE;
		}
		else 
		{
			$aData['userid'] = intval($aParam['userid']);
		}
		
		if ( !is_string($aParam['username']) )
		{
			return FALSE;
		}
		else
		{
			$aData['username'] = daddslashes($aParam['username']);
		}
		
		if ( ($aParam['usertree'] != '') && !is_string($aParam['usertree']) && !is_numeric($aParam['usertree']) ) 
			
		{
			return FALSE;
		}
		else
		{
			$aData['user_tree'] = daddslashes($aParam['usertree']);
		}
		
		if ( !is_numeric($aParam['userlevel']) )
		{
			return FALSE;
		}
		else
		{
			$aData['user_level'] = intval($aParam['userlevel']);
		}
		
		if ( !is_numeric($aParam['usertype']) )
		{
			return FALSE;
		}
		else
		{
			$aData['user_type'] = intval($aParam['usertype']);
		}
		
		if ( !is_numeric($aParam['topagentid']) )
		{
			return FALSE;
		}
		else
		{
			$aData['topagentid'] = intval($aParam['topagentid']);
		}
		
		if ( !is_numeric($aParam['agentid']) )
		{
			return FALSE;
		}
		else
		{
			$aData['agentid'] = intval($aParam['agentid']);
		}
		//普通用卡财务别名
		$aData['accname'] = daddslashes($aParam['accname']);
		
		//普通用卡户名
		$aData['deposit_name'] = daddslashes($aParam['depositname']);
		
		//普通用卡EMAIL
		$aData['deposit_mail'] = daddslashes($aParam['depositmail']);
		
		//普通用卡 卡ID (支付接口 分账户ID)
		if ( !empty($aParam['payaccid']) && !is_numeric($aParam['payaccid']) )
		{
			return FALSE;
		}
		else
		{
			$aData['payacc_id'] = intval($aParam['payaccid']);
		}
		
		//是否激活(禁用或启用)
		if ( !empty($aParam['isactive']) && !is_numeric($aParam['isactive']) )
		{
			return FALSE;
		}
		else
		{
			$aData['isactive'] = intval($aParam['isactive']);
		}
		
		$aData['vip_expriy'] 	= $aData['vip_starttime'] 	= $aData['black_starttime']  = '0000-00-00 00:00:00';
		$aData['vip_payacc_id'] = $aData['black_payacc_id'] = 0;
		$aData['utime'] 		= date('Y-m-d H:i:s');

		return $this->oDB->insert($this->TableName, $aData);
		
	}
	

	
	/**
	 *  用户有效性检查 
	 * 		主要用于功能性前置检查、防止滥删除、添加;
	 * 		检查提交的用户名数组中是否所有用户名均在用户＆卡表中有数据
	 * 		
	 *
	 * @param array	 		$aUser 	用户名数组
	 * @param string/array 	$sWhere	附加查询限制语句 (按需要补充完整的WHERE语句) 
	 * 								如传入数组，则作为封装信息处理, 参考 switch 了解可用信息
	 * @param string		$sKey	标记传入的数组是 username 还是 userid 
	 * 
	 * @return array 找到的数组(有效的名称)
	 */
	public function userValidCheck( $aUser=array(), $sWhere='', $sKey = 'username')
	{

		if ( ($sKey != 'username') && ($sKey != 'userid') ) return FALSE;
		if ( !is_array($aUser) ) return $aUser;
		// 如是数组，认为是封装信息传入,预处理sql where子句
		if ( is_array($sWhere) )
		{
			switch ( $sWhere[0] )
			{
				case 'vip':
					$sRunWhere = ' `bankid`='.$this->BankId.' AND `isvip`='.$sWhere[1];
					break;
				case 'black':
					$sRunWhere = ' `bankid`='.$this->BankId.' AND `isblack`='.$sWhere[1];
					break;
				case 'normal':
					$sRunWhere = ' `bankid`='.$this->BankId.' AND `isactive`='.$sWhere[1];
					break;
				default:
					return FALSE;
					break;
			}
		}
		else 
		{
			$sRunWhere = daddslashes($sWhere);
			if ($this->BankId) $sRunWhere .= ' AND `bankid`='.$this->BankId;
		}
		
		$aNewUser = array();
		foreach ($aUser AS $aU)
		{
			if ( is_array($aU) )
			{
				if ( !isset($aU['userid']) )
				{
					$aNewUser[] = $aU;
				}
				else 
				{
					$aNewUser[] = $aU['userid'];
				}
			}
			else 
			{
				if ( !empty($aU) ) $aNewUser[] = $aU;
			} 	
		}
		unset($aUser);

		$sSql = "SELECT `bankid`,`userid`,`username`,`deposit_name`,`isvip`,`vip_expriy`,`isblack` FROM `$this->TableName` WHERE `".daddslashes($sKey)."` IN ('".implode("','", $aNewUser)."') $sRunWhere";
		$aRusult = $this->oDB->getAll($sSql);

		if ($this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		else
		{
			$aUser = array();
			
			foreach ($aRusult AS $aR)
			{
				
				if ( intval($aR['isblack']) === 1 )
				{
					$aUser[$aR[$sKey]]['usertype'] 	= 2;
				}
				elseif ( ( intval($aR['isvip']) === 1 ) && ( $this->_differTime($aR['vip_expriy']) < 0 ) )
				{
					$aUser[$aR[$sKey]]['usertype'] 	= 1;
				}
				else
				{
					$aUser[$aR[$sKey]]['usertype'] 	= 0;
				}
				
				$aUser[$aR[$sKey]]['bankid'] 		= $aR['bankid'];
				$aUser[$aR[$sKey]]['userid'] 		= $aR['userid'];
				$aUser[$aR[$sKey]]['username'] 		= $aR['username'];
				$aUser[$aR[$sKey]]['deposit_name'] 	= $aR['deposit_name'];
				
			}
			
			return $aUser;
		}
		
	}
	
	

	
	/**
	 * 批量 删除黑名单 或 VIP白名单
	 * 	(更新 标记位为0, 清空对应卡信息'缓存' )
	 * 
	 * @param string	$sLogo		opblack/opvip finblack/finvip	(标记, 更新的是黑名单或VIP)
	 * @param array		$aUser		经controller整理之后的名单列表数组
	 * 
	 * @return bool  	true,false 成功/失败
	 * 
	 */
	public function del($sLogo='', $aUser=array() )
	{
		if ( array_search($sLogo, array('opblack','opvip','finblack','finvip','clidelvip')) === FALSE ) return FALSE;
		if ( count($aUser) < 1 ) return FALSE;
		
		$this->oDB->doTransaction();
		$sSql = '';
		foreach ($aUser AS $aU)
		{
			$sLogo = empty($sLogo) ? $aU['logo'] : $sLogo;
			$sSql = "UPDATE `$this->TableName` SET ";
				switch ($sLogo)
				{
					// 独立用卡操作以名称/ID 操作多行数据
					case 'opblack':
						$sSql .= "`isblack`=0,`black_starttime`='0000-00-00 00:00:00' WHERE `userid`='".daddslashes($aU['userid'])."'";
						//$sSql .= "`isblack`=0,`black_starttime`='0000-00-00 00:00:00' WHERE `username`='".daddslashes($aU['username'])."'";
						break;
					case 'opvip':
						$sSql .= "`isvip`=0, `vip_starttime`='0000-00-00 00:00:00', `vip_expriy`='0000-00-00 00:00:00' WHERE `userid`='".daddslashes($aU['userid'])."'";
						//$sSql .= "`isvip`=0, `vip_starttime`='0000-00-00 00:00:00', `vip_expriy`='0000-00-00 00:00:00' WHERE `username`='".daddslashes($aU['username'])."'";
						break;
					/***
					 * 财务角度暂无删除(清空)绑定需求
					case 'finblack':
						$sSql .= "`black_accname`='', `black_deposit_name`='', `black_deposit_mail`='', `black_payacc_id`=0 WHERE `userid`='".daddslashes($aU['userid'])."'";
						break;
					case 'finvip':
						$sSql .= "`vip_accname`='', `vip_deposit_name`='', `vip_deposit_mail`='', `vip_payacc_id`=0 WHERE `userid`='".daddslashes($aU['userid'])."'";
						break;
					***/
					case 'clidelvip':
						$sSql .= "`isvip`=0, `vip_starttime`='0000-00-00 00:00:00', `vip_expriy`='0000-00-00 00:00:00',`vip_accname`='', `vip_deposit_name`='', `vip_deposit_mail`='', `vip_payacc_id`=0 WHERE `userid`='".intval($aU['userid'])."'";
						break;
					
					default:
						$sSql = FALSE;
						break;
				}

	 			if ($sSql === FALSE) 
	 			{
	 				$this->oDB->doRollback();
	 				return FALSE;
	 			}
	 			
				$this->oDB->query($sSql);
				
				if ($this->oDB->errno() > 0)
				{
					$this->oDB->doRollback();
					return FALSE;
				}
				
				unset($sLogo);
			
		}

		$this->oDB->doCommit();
		return TRUE;
		
	}
	
	/**
	 * 禁用用户
	 * 
	 * @param array $aUser	需改变状态的用户列表,或单个用户名
	 */
	public function disable($aUser)
	{
		return $this->_setActive($aUser,0);
	}
	

	/**
	 * 启用用户
	 * 
	 * @param array $aUser	需改变状态的用户列表,或单个用户名
	 */
	public function enable($aUser)
	{
		return $this->_setActive($aUser,1);
	}
	
	
	/**
	 *  获取总代用户名，用于列表显示
	 * 	@param array	填充的目标数组
	 *  @param array 	参照数组，最好是一个只含有需要值与关联KEY做键名的单数集数组, array(key1=>val1,key2=>val2)
	 * 	@param string	填充的新键名
	 *  @param string	用于在参照数组中搜索的键名,即两个数组关联关系的KEY名
	 * 
	 * @return array 返回合成之后的数组
	 */
	public function fillarray($aArray1,$aArray2,$sKey1,$sKey2)
	{
		foreach ($aArray1 AS $aRr)
		{
			$aRr[$sKey1] = $aArray2[$aRr[$sKey2]];
			$aNewArray[] = $aRr;
			
		}
		
		return $aNewArray;
	}
	
	

	/**
	 * 获取关系列表
	 * 		遵循A frame Controller层调用模式的List方法
	 * 
	 * @param $sFields		返回字段限定
	 * @param $sCondition	WHERE子句
	 * @param $iPageRecords	每页显示记录数
	 * @param $iCurrPage	当前页码
	 * @param $sOrderby		ORDER BY子句，根据WHERE子句提供两种形式排列，按VIP到期日 / 按黑名单名称
	 * 
	 * @return array	查询结果
	 */
	public function & getRelationList( $sFields = "", $sCondition = "1", $iPageRecords = 20, $iCurrPage = 1, $sOrderby='')
	{
	    $sTableName = $this->TableName;
	    $sFields    = empty($sFields) ? ' * ' : $sFields;
	    if (eregi('isvip',$sCondition) && empty($sOrderby) )
	    {
	    	$sOrderby = ' ORDER BY `vip_expriy` DESC ';
	    }
	    elseif (eregi('isblack',$sCondition) && empty($sOrderby) )
	    {
	    	$sOrderby = ' ORDER BY `username` ASC ';
	    }
	    else 
	    {
	    	$sOrderby = daddslashes($sOrderby);
	    }
	    $sCondition .= ' AND `bankid`='.$this->BankId.' ';
	    return $this->oDB->getPageResult( $sTableName, $sFields, $sCondition, $iPageRecords, $iCurrPage, $sOrderby );
	}
	

	
	/**
	 * 获取 用户＆卡表 列表
	 * 
	 * @param array 	$aParam 查询条件
	 * @param string	$sFiled	返回字段
	 * @param string	$sCate	预设搜索(分类查找 有效VIP:vip 有效黑名单:black 普通用户:normal)
	 * 
	 * @return array  
	 */
	public function getList($aParam=array(),$sFiled=null,$sCate=FALSE)
	{
		$sWhere = '1';
		// 查询参数
		if ( is_array($aParam) && !empty($aParam) )
		{
			foreach ($aParam as $sKey => $sVal) {
					$sWhere .= " AND $sKey='$sVal'";
			}
			
		}
		
		// 返回字段
		if ( is_null($sFiled) )
		{
			$sFiled = '`bankid`,`userid`,`username`,`user_tree`,`user_level`,`user_type`,`topagentid`,`agentid`';
		}
		else 
		{
			$sFiled = ($sFiled == 'all') ? '*' : $sFiled;
		}
		
		if ($sCate)
		{
			$fNow = date('Y-m-d H:i:s');
			switch ($sCate)
			{
				case 'normal':
					$sWhere = ' `isblack`=0 AND `isvip`=0';
					break;
				case 'black':
					$sWhere = ' `isblack`=1';
					break;
				case 'vip':
					$sWhere = ' `isblack`=0 AND `isvip`=1 AND `vip_expriy` > \''.$fNow.'\'';
					break;
				default:
					return FALSE;
					break;	
			}
		}
		$sSql = "SELECT $sFiled FROM `$this->TableName` WHERE $sWhere";
		$aResult = $this->oDB->getAll($sSql);
		if ( ($this->oDB->errno() > 0) || ( count($aResult) < 1) )
		{
			return FALSE;
		}
		else
		{
			if (!is_array($aParam))
			{
				foreach ($aResult AS $aRe)
				{
					$i = $aRe['userid'];
					$aReturn[$i]['bankid'] 	 	= $aRe['bankid'];
					$aReturn[$i]['userid'] 	 	= $aRe['userid'];
					$aReturn[$i]['username'] 	= $aRe['username'];
					$aReturn[$i]['usertree'] 	= $aRe['user_tree'];
					$aReturn[$i]['userlevel']	= $aRe['user_level'];
					$aReturn[$i]['usertype']	= $aRe['user_type'];
					$aReturn[$i]['topagentid']	= $aRe['topagentid'];
					$aReturn[$i]['agentid']  	= $aRe['agentid'];
				}
			
				unset($aResult,$aRe);
				ksort($aReturn);
				return $aReturn;
			
			}
			else 
			{
				return $aResult;
			}
			
		}
		
	}
	
	
	/**
	 * 从用户表获取的用户列表，作为基准列表使用
	 * 
	 */
	protected function getUserList()
	{
		$oUser = new model_user();
		$sFiled = 'u.`userid` , u.`username` ,ut.`usertype`, ut.`parentid`, ut.`lvtopid`, ut.`lvproxyid`, ut.`parenttree`';
		//排除总代管理员
		$sAndWhere = ' AND ut.`lvtopid`>0 ';
		$aUserList = $oUser->getUsersProfile($sFiled, '', $sAndWhere, true);

		foreach ($aUserList AS $aUser)
		{
			$i = $aUser['userid'];
			$aNewList[$i]['userid']   = $aUser['userid'];
			$aNewList[$i]['username'] = $aUser['username'];
			$aNewList[$i]['usertree'] = $aUser['parenttree']; 
			$aNewList[$i]['usertype'] = $aUser['usertype']; 
			if ( $aUser['parentid'] == 0)
			{
				$aNewList[$i]['userlevel'] = 0;
			}
			elseif ( $aUser['parentid'] == $aUser['parenttree'] )
			{
				$aNewList[$i]['userlevel'] = 1;
			}
			else 
			{
				$aNewList[$i]['userlevel'] = substr_count( $aUser['parenttree'],',') + 1;
			}
			$aNewList[$i]['topagentid'] = $aUser['lvtopid'];
			$aNewList[$i]['agentid'] = $aUser['parentid'];
			
		}
		unset($aUserList, $aUser);
		ksort($aNewList);
		return $aNewList;
	}
	
	
	
	/**
	 * 获取用户对应卡信息
	 * 
	 * 调用之前赋值 UserId 或 UserName 
	 *  失败:用户无绑定 return FALSE 
	 * 	正常:在对象实例中以下属性有值 DepositName(卡户名) DepositMail(卡EMAIL) UserType(普通0/黑名单2/VIP1)  PayCardId(对应分账户ID)
	 * 
	 */
	public function getCard($sType='check')
	{
		if ( is_numeric($this->UserId) || is_string($this->UserName) )
		{
			
			$sWhere = ($this->UserName) ? "username='$this->UserName'" : '';
			$sWhere = ($this->UserId) ? "userid=$this->UserId" : '';
			$sWhere .= !empty($this->BankId) ? " AND bankid=$this->BankId " : '';
			
			$sSql = "SELECT * FROM `$this->TableName` WHERE $sWhere";
			$aTmp = $this->oDB->getOne($sSql);
			if ( ($sType == 'get') && (count($aTmp) > 0 ) )
			{
				return $aTmp;
			}
			elseif ( ($sType == 'check') && (count($aTmp) > 0 ) )
			{
				if ( intval($aTmp['isactive']) !== 1 ) return FALSE;
				$iDiffTime = 0;
				$iDiffTime = $this->_differTime($aTmp['vip_expriy']);
				// 判断是否VIP,是否处于黑名单
				if ( intval($aTmp['isblack']) === 1) 
				{
					// 当用户处于黑名单,但未配黑名单卡时, 禁用
					if ( strlen($aTmp['black_deposit_mail']) < 5 ) return FALSE;
					$this->AccName		= $aTmp['black_accname'];
					$this->DepositName 	= $aTmp['black_deposit_name'];
					$this->DepositMail 	= $aTmp['black_deposit_mail'];
					$this->UserType 	= 2;
					$this->PayCardId 	= $aTmp['black_payacc_id'];
				}
				elseif ( ( intval($aTmp['isvip']) === 1) && ( $iDiffTime < 0 )
						&& ( strlen($aTmp['vip_deposit_mail']) > 5 ) )
				{
					// 用户未配VIP卡,沿用去普通卡
					$this->AccName		= $aTmp['vip_accname'];
					$this->DepositName 	= $aTmp['vip_deposit_name'];
					$this->DepositMail 	= $aTmp['vip_deposit_mail'];
					$this->UserType 	= 1;
					$this->PayCardId 	= $aTmp['vip_payacc_id'];
				}
				else
				{
					$this->AccName		= $aTmp['accname'];
					$this->DepositName 	= $aTmp['deposit_name'];
					$this->DepositMail 	= $aTmp['deposit_mail'];
					$this->UserType 	= 0;
					$this->PayCardId 	= $aTmp['payacc_id'];
				}
				
			}
			else 
			{
				return FALSE;
			}
			
		}
		else
		{
			return FALSE;
		}
	}
	
	
	
	/**
	 * 冗余操作， 由分账户管理调用,处理对分账户的禁用 启用
	 *  (在其他事务中调用，此方法不使用事务)
	 * 分账户禁用，则禁用旗下所有已激活的 暂置为9
	 * 	启用,则打开旗下本是激活的 9置回1
	 * 
	 * @param $iPayAccId 	int 	分账户ID
	 * @param $iStatus		int		操作状态 0/1
	 * 
	 * @return bool 
	 */
	public function reduces($iPayAccId,$sStatus,$sKey=NULL)
	{
		if ( !is_numeric($iPayAccId) ) 	return FALSE;
		if ( !is_numeric($sStatus) ) 	return FALSE;
		
		if ( $sStatus == 1)
		{
			//启用
			$sSql0 = "UPDATE `{$this->TableName}` SET `isactive`=1 ";
			$sSql2 = ' AND `isactive`=9';
		}
		else 
		{
			//禁用
			$sSql0 = "UPDATE `{$this->TableName}` SET `isactive`=9 ";
			$sSql2 = ' AND `isactive`=1';
		}
		
		
		switch ($sKey)
		{
			case 'normal':
				$sSql1 = " WHERE `isblack`=0 AND `isvip`=0 AND `payacc_id`=".$iPayAccId;
				break;
			case 'black':
				$sSql1 = " WHERE `isblack`=1 AND `isvip`=0 AND `black_payacc_id`=".$iPayAccId;
				break;
			case 'vip':
				$sSql1 = " WHERE `vip_expriy`> '".date('Y-m-d H:i:s')."' AND `isvip`=1 AND `isblack`=0 AND `vip_payacc_id`=".$iPayAccId;
				break;
			default:
				return FALSE;
				break;
		}
			
		$sSql = $sSql0.$sSql1.$sSql2;
		$this->oDB->query($sSql);
		if ($this->oDB->errno() > 0)
		{
			return FALSE;
		}
		else 
		{
			return TRUE;
		}
	}
	
	
	/**
	 * 冗余兼容，管理后台修改 卡户名、EMAIL、财务用名时
	 * 	(在其他事务中调用，此方法不使用事务)
	 * 
	 * @param string	$sKey	操作方式代码, 选择相应SQL
	 * @param array  	array('accid(分账户ID)','name(卡户名)','mail(卡MAIL)')
	 * 		按accid，搜索修改
	 * 
	 * @return bool
	 */
	public function reduceUpdate($sKey, $aArray=array())
	{
		
		if ( !is_array($aArray) || empty($aArray) || empty($sKey) )
		{
			return FALSE;
		}
		//删除分账户时不要求提供下述参数
		if (  ( empty($aArray['accname']) || empty($aArray['name']) || empty($aArray['mail']) )
				&& !eregi('del',$sKey)	)
		{
			return FALSE;
		}
		if (  !is_numeric($aArray['accid'])  )  return FALSE;
		
		$sSql = "UPDATE {$this->TableName} SET ";
		switch ($sKey)
		{
			case 'upnormal':
				$sSql .= "`accname`='".$aArray['accname']."', `deposit_name`='".$aArray['name']."', `deposit_mail`='".$aArray['mail']."' WHERE `payacc_id`=".$aArray['accid'];
				break;
			case 'upblack':
				$sSql .= "`black_accname`='".$aArray['accname']."', `black_deposit_name`='".$aArray['name']."', `black_deposit_mail`='".$aArray['mail']."' WHERE `black_payacc_id`=".$aArray['accid'];
				break;
			case 'upvip':
				$sSql .= "`vip_accname`='".$aArray['accname']."', `vip_deposit_name`='".$aArray['name']."', `vip_deposit_mail`='".$aArray['mail']."' WHERE `vip_payacc_id`=".$aArray['accid'];
				break;
			case 'delnormal':
				$sSql .= "`accname`='', `deposit_name`='', `deposit_mail`='' WHERE `payacc_id`=".$aArray['accid'];
				break;
			case 'delblack':
				$sSql .= "`black_accname`='', `black_deposit_name`='', `black_deposit_mail`='' WHERE `black_payacc_id`=".$aArray['accid'];
				break;
			case 'delvip':
				$sSql .= "`vip_accname`='', `vip_deposit_name`='', `vip_deposit_mail`='' WHERE `vip_payacc_id`=".$aArray['accid'];
				break;
			default:
				return FALSE;
				break;		
		}
		
		$this->oDB->query($sSql);
		if ($this->oDB->errno() > 0)
		{
			return FALSE;
		}
		else 
		{
			return TRUE;
		}
		
	}
	
	

	
	
	/**
	 * 与USER表同步
	 * 	将用户＆卡表与USER表保持同步,不修改用户＆卡表中已配置非用户基本信息的内容
	 * 	不自动删除用户＆卡表中多于用户表的用户信息
	 * 	(可用于初始化 用户＆卡表 或后期的有效用户维护)
	 * @param 	bool	
	 * @return  -1 		无需更新
	 * 			-2 		有无效数据(提交的用户名中有问题或原始的USER表中有不合法数据)
	 * 			FALSE 	插入数据发生失败
	 * 			TRUE	成功同步基本数据(用户＆卡关系表 与 用户表 一致)
	 */
	public function syncUser()
	{
		$aUserListAll 	= $this->getUserList();
		$aDepUserListAll= $this->getList();
		
		if ( empty($aDepUserListAll) ) 
		{
			echo 'isEmpty';
			// 用户＆卡关系表为空时，认为是首次初始化、无须做比较直接转储所有有效用户信息
			$aLeftUser = $aUserListAll;
		}
		else 
		{
			//如用户＆卡表是空，避免后续函数出错
			if ( empty($aDepUserListAll) ) $aDepUserListAll = array('xxxxxxxxxx');
			
			$iCountUser 	= count($aUserListAll);
			$iCountDepUser 	= count($aDepUserListAll);
		
			// 为数组分片计算起始游标位，从左起
			$iDiff = $iCountUser - $iCountDepUser;
			if ( $iDiff > 0 )
			{
				$iOffset	= $iCountUser - $iDiff;
				//  分片之后比较，只能处理位于队尾的差异，TODO: 不能解决队前、队中的差异
				$aUserList 	= array_slice($aUserListAll,$iOffset,null,TRUE);
				//  比较差异，排除user表多出部分是否已存在于用户&卡表
				$aLeftUser 	= array_diff_key( $aUserList, $aDepUserListAll );
			}
			elseif ( $iDiff < 0 )
			{
				$iOffset 	= $iCountDepUser - $iDiff;
				$aDepUserList = array_slice( $aDepUserListAll,$iOffset,null,TRUE );
				$aLeftUser 	= array_diff_key( $aUserListAll, $aDepUserList );
			}
			else 
			{
				return -1;
			}
			
		}
		
		if ( count($aLeftUser) > 0 ) 
		{
			return  $this->addUser($aLeftUser);
		}
		else 
		{
			$aRightUser = array_diff_key( $aDepUserList, $aUserList );
			if ( count($aRightUser) > 0 )
			{
				return $this->_delUser($aRightUser);
			}
			else 
			{
				return -1;
			}
		}
		
	}
	

	
	/**
	 * 时间差比较
	 * 		比较传入时间值与当前时间的差值
	 * 
	 * @param datetime 时间戳 (任意形式)
	 * 
	 * @return int 差值
	 */
	public function _differTime($sTime)
	{
		if ( empty($sTime) || !$sTime ) return 0;
		return strtotime('NOW') - strtotime($sTime);
	}
	
	
	/**
	 * 设置用户激活或未激活状态
	 * 	可批量,提供有效的用户名数组
	 * 
	 * @param array $aUser	需改变状态的用户列表,或单个用户名
	 * @param int	$sStatus	状态代码 0:禁用 1:激活
	 */
	private function _setActive($aUser,$sStatus)
	{
		if ( is_array($aUser) && empty($aUser) )				return FALSE;
		if ( !is_array($aUser) && !eregi('[a-z0-9]+',$aUser) )	return FALSE;
		$sWhere 	= ($sStatus == 1)	?	' AND `isactive`!=1'	 :	' AND `isactive`=1';
		
		$aValidUser = $this->userValidCheck($aUser, $sWhere, 'userid');
		//print_rr($aValidUser);
		if ( empty($aValidUser) )	return FALSE;
		
		$this->oDB->doTransaction();
		
		unset($aZeroAtName);
		foreach ($aValidUser AS $aV)
		{
			//排除 VIP 黑名单，以及不同卡配置的下级用户
			if ( $aV['usertype'] == 2)
			{
				$sSqlWhere = ' AND `isvip`=0 AND `isblack`=1';
			}
			elseif ( $aV['usertype'] == 1)
			{
				$sSqlWhere = ' AND `isvip`=1 AND `isblack`=0';
			}
			else 
			{
				$sSqlWhere = ' AND `isvip`=0 AND `isblack`=0';
			}
			if ( intval($aV['bankid']) == 0 )
			{
				$this->oDB->doRollback();
				return FALSE;
			}
			$sSql = "UPDATE `$this->TableName` SET `isactive`=".intval($sStatus)." WHERE ( FIND_IN_SET('".intval($aV['userid'])."',`user_tree`) OR `userid`=".intval($aV['userid'])." ) ".$sSqlWhere." AND `bankid`=".intval($aV['bankid'])." AND `deposit_name`='".daddslashes($aV['deposit_name'])."' ".$sWhere;
			$this->oDB->query($sSql);

			if ( $this->oDB->ar() == 0)
			{
				$aZeroAtName[] = $aV['username'];
			}
			
			if ( $this->oDB->errno() > 0 )
			{
				$this->oDB->doRollback();
				return FALSE;
			}
			
		}

		$this->oDB->doCommit();
		if ( empty($sZeroAtName) )
		{
			return TRUE;
		}
		else 
		{
			// 将SQL影响行数为0的username序列化后输出
			return serialize($aZeroAtName);
		}
	}
	
	
	
	/**
	 * 删除用户&卡表中的某多余用户信息 (物理删除)
	 *
	 * @param array	需要删除的用户＆卡关系表的用户列表 array(userid)
	 * 
	 * @return  -1 		提交的用户列表无效
	 * 			-2 		有无效数据
	 * 			FALSE 	SQL发生失败
	 * 			TRUE	成功同步基本数据(用户＆卡关系表 与 用户表 一致)
	 */
	private function _delUser( $aRightUser=array() )
	{
		if ( count($aRightUser) < 1 ) return -1;
		
		$this->oDB->doTransaction();
		
		foreach ($aRightUser AS $aRU)
		{
			if ( !is_int($aRU['userid']) )
			{
				$this->oDB->doRollback();
				return -2;
			}
				
			$sCond = " `userid`=".$aRU['userid'];
			$this->oDB->delete($this->TableName, $sCond);
			
			if ( $this->oDB->errno() > 0 )
			{
				$this->oDB->doRollback();
				return FALSE;	
			}
			
		}
		
		$this->oDB->doCommit();
		return TRUE;
	}
	
	
	
	/**
     * 根据用户ID获取其下的直接下级或者所有下级用户列表（只获取用户名和ID，不分页，不获取总代管理员）
     * 
     * @access  public
     * @author  louis
     * @param   int     $iUserId    //用户ID
     * @param   boolean $bAllChildren   //TRUE：所有下级，FALSE：直接下级，默认为TRUE
     * @param   string  $sAndWhere      //附加搜索条件
     * @param   return  //成功返回用户列表，失败返回FALSE
     */
    public function getChildListID( $iUserId, $bAllChildren=TRUE, $sAndWhere='', $sOrderby='' )
    {
        if( intval($iUserId) < 0 )
        {
            return FALSE;
        }
        $sSql = "";
        if( $bAllChildren )
        {//获取所有下级
            $sSql = "SELECT `userid`,`username`,`user_level` FROM {$this->TableName} 
                    WHERE FIND_IN_SET('".$iUserId."', `user_tree`) 
                    ".$sAndWhere."  ORDER BY `userid` ASC,`username` ASC ".$sOrderby;
        }
        else 
        {//直接下级
            $sSql = "SELECT a.`userid`,a.`username`,a.`user_type`,count(b.userid) as childcount FROM {$this->TableName} AS a 
                    LEFT JOIN {$this->TableName} AS b ON (b.`agentid`=a.`userid` and b.`user_type`<2 ) 
                    WHERE a.`agentid`='".$iUserId."' AND a.`user_type`<'2'
                    ".$sAndWhere." GROUP BY a.`userid` ".$sOrderby;
        }
        $aData = $this->oDB->getAll( $sSql );
        if( empty($aData) )
        {
            return FALSE;
        }
        return $aData;
    }
    
    
	/**
	 * 新增受付银行时，完成用户数据新增 (不含事务，由新增受付银行时调用环节 事务包装)
	 * 
	 * @param int	新增的deposit的ID号
	 * 
	 * @return bool 	true 成功完成  FLASE失败
	 * 
	 */
	public function copyUserbyDeposit($iDepositid)
	{
		$iDepositid = intval($iDepositid);
		
		if ( !is_numeric($iDepositid) || $iDepositid <= 0 ) return FALSE;
		
		 /***
		  	create table `temp111` like `user_deposit_card`;
			insert `temp111` select * from `user_deposit_card` where `bankid`=$iBankId;
			-- 888 为可能的新
			update `temp111` set `bankid` = 888;
			-- 将用户基本数据进行复制
			INSERT INTO `user_deposit_card` (`bankid` ,`userid` ,`username` ,`user_tree` ,`user_level` ,`user_type` ,`topagentid` ,`agentid`) select `bankid` ,`userid` ,`username` ,`user_tree` ,`user_level` ,`user_type` ,`topagentid` ,`agentid`  from `temp111`;
			-- 删除临时表
			DROP TABLE `temp111`;
		***/
		// 找出含有用户最多的BankID
    	$sSql = "SELECT count(id) AS ttl,bankid FROM {$this->TableName} WHERE 1 GROUP BY `bankid` ORDER BY count(id) DESC";
    	$aCount = $this->oDB->getAll($sSql);
    	//return $aCount;
    	if ( $this->oDB->errno() > 0 )
    	{
    		return FALSE;
    	}
    	// TODO 检查数据是否没有问题, 各个受付银行用户数据是否完整(与用户表一致性)
    	// 最多用户数的受付银行 
    	$iTagId = $aCount[0]['bankid'];
    	
		// 创建临时表
		$sTempTabName = 'udc_'.$iDepositid.'_temp_'.date('YmdHi');
		$sSql = 'create table `'.$sTempTabName.'` like `user_deposit_card`';
		$aResult = $this->oDB->query($sSql);
		if ( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		
		//删除临时表索引	
		$sSql = 'ALTER TABLE `'.$sTempTabName.'` DROP INDEX `forsearch`';
		$aResult = $this->oDB->query($sSql);
		if ( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		
		$sSql = 'ALTER TABLE `'.$sTempTabName.'` DROP INDEX `userdata`';
		$aResult = $this->oDB->query($sSql);
		if ( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
	

		// 为临时表插入数据,根据目标 bankid 提取
		$sSql = 'insert `'.$sTempTabName.'` select * from `user_deposit_card` where `bankid`='.$iTagId;
		$aResult = $this->oDB->query($sSql);
		if ( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		
		// 更新临时表数据,整理插入数据
		$sSql = 'update `'.$sTempTabName.'` set `bankid`='.$iDepositid;
		$aResult = $this->oDB->query($sSql);
		if ( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		
		
		// 复制数据
		$sSql = 'INSERT INTO `'. $this->TableName .'` (`bankid` ,`userid` ,`username` ,`user_tree` ,`user_level` ,`user_type` ,`topagentid` ,`agentid`) select `bankid` ,`userid` ,`username` ,`user_tree` ,`user_level` ,`user_type` ,`topagentid` ,`agentid`  from `'.$sTempTabName.'`';
		// TODO 大数据量必须关闭索引刷新功能
		//$sSql = 'INSERT INTO `'.$this->TableName.'` (`bankid` ,`userid` ,`username` ,`user_tree` ,`user_level` ,`user_type` ,`topagentid` ,`agentid`) select '.$iDepositid.',`userid` ,`username` ,`user_tree` ,`user_level` ,`user_type` ,`topagentid` ,`agentid`  from `'.$this->TableName.'` where `bankid`='.$iTagId;
		$aResult = $this->oDB->query($sSql);
		if ( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		
	
		// 删除临时表
		$sSql = 'DROP TABLE `'.$sTempTabName.'`';
		$aResult = $this->oDB->query($sSql);
		if ( $this->oDB->errno() > 0 )
		{
			return FALSE;
		}
		
		return TRUE;
		
	}
	
	
    
    
    /**
     * 批量替换卡前的检查
     * 
     * @author      louis
     * @version     v1.0
     * @since       2011-01-18
     * 
     * @return      array
     */
    public function initInsteadCard(){
        // 数据检查
        if (intval($this->BankId) <= 0 || intval($this->PayAccId) <= 0){
            return false;
        }
        
        $aResult = array();
        $aBlack = array();
        $aVip = array();
        // black
        $sSql = "SELECT udc.`username`,udc.`user_level`,udc.`user_type`,ut.`username` AS topproxy FROM `user_deposit_card`  AS udc LEFT JOIN `usertree` AS ut ON (udc.`topagentid` = ut.`userid`) WHERE udc.`bankid` = '{$this->BankId}' AND udc.`black_payacc_id` = '{$this->PayAccId}' AND udc.`isblack` = 1";
        $aBlack = $this->oDB->getAll($sSql);
        if (!empty($aBlack)){
            foreach ($aBlack as $k => $v){
                $aBlack[$k]['identy'] = 'black';
            }
        }
        
        // vip
        $sSql = "SELECT udc.`username`,udc.`user_level`,udc.`user_type`,ut.`username` AS topproxy FROM `user_deposit_card`  AS udc LEFT JOIN `usertree` AS ut ON (udc.`topagentid` = ut.`userid`) WHERE udc.`bankid` = '{$this->BankId}' AND udc.`vip_payacc_id` = '{$this->PayAccId}' AND udc.`isvip` = 1 AND udc.`vip_expriy` >= '" . date("Y-m-d H:i:s", time()) . "'";
        $aVip = $this->oDB->getAll($sSql);
        if (!empty($aVip)){
            foreach ($aVip as $k => $v){
                $aVip[$k]['identy'] = 'vip';
            }
        }
        
        // normal
        $sSql = "SELECT udc.`username`,udc.`user_level`,udc.`user_type`,ut.`username` AS topproxy FROM `user_deposit_card`  AS udc LEFT JOIN `usertree` AS ut ON (udc.`topagentid` = ut.`userid`) WHERE udc.`payacc_id` = '{$this->PayAccId}' AND udc.`bankid` = '{$this->BankId}' GROUP BY udc.`topagentid` ORDER BY udc.`user_level` DESC";        
        $aResult = $this->oDB->getAll($sSql);
        if (!empty($aResult)){
            foreach ($aResult as $k => $v){
                $aResult[$k]['identy'] = 'normal';
            }
        }
        
        $aResult = array_merge($aResult, $aBlack);
        $aResult = array_merge($aResult, $aVip);
        return $aResult;
    }
    
    
    
    
    /**
     * 批量替换卡操作
     * 
     * @author      louis
     * @version     v1.0
     * @since       2011-01-21
     * 
     * @return      mix
     * 
     */
    public function batchChangeCard($iOldCard, $iNewCard, $iDepositBankId){
        // 数据检查
        if (intval($iOldCard) <= 0 || intval($iNewCard) <= 0 || intval($iDepositBankId) <= 0){
            return false;
        }
        
        // 查询新卡的相关信息
        $oDeposit = new model_deposit_depositaccountinfo($iNewCard);
        if (intval($oDeposit->AId) <= 0){
            return false;
        }
        $oDeposit->getAccountDataObj();
        
        if (empty($oDeposit->AccName) || empty($oDeposit->AccIdent) || empty($oDeposit->AccMail)){
            return false;
        }
        
        // 开始事务
        if ($this->oDB->doTransaction() === false){
            return false;
        }
        
        // 批量替换普通卡
        $sSql1 = "UPDATE `{$this->TableName}` SET `accname` = '{$oDeposit->AccName}',`deposit_name` = '{$oDeposit->AccIdent}',`deposit_mail` = '{$oDeposit->AccMail}', `payacc_id` = {$iNewCard} WHERE `payacc_id` = {$iOldCard} AND `bankid` = {$iDepositBankId}";
        
        $this->oDB->query($sSql1);
        if ($this->oDB->errno > 0){
            if ($this->oDB->doRollback() === false){
                return -1;
            }
            return false;
        }
        
        // 批量替换VIP卡
        $sSql2 = "UPDATE `{$this->TableName}` SET `vip_accname` = '{$oDeposit->AccName}',`vip_deposit_name` = '{$oDeposit->AccIdent}',`vip_deposit_mail` = '{$oDeposit->AccMail}', `vip_payacc_id` = {$iNewCard} WHERE `vip_payacc_id` = {$iOldCard} AND `bankid` = {$iDepositBankId}";
        
        $this->oDB->query($sSql2);
        if ($this->oDB->errno > 0){
            if ($this->oDB->doRollback() === false){
                return -1;
            }
            return false;
        }
        
        // 批量替换黑名单卡
        $sSql3 = "UPDATE `{$this->TableName}` SET `black_accname` = '{$oDeposit->AccName}',`black_deposit_name` = '{$oDeposit->AccIdent}',`black_deposit_mail` = '{$oDeposit->AccMail}', `black_payacc_id` = {$iNewCard} WHERE `black_payacc_id` = {$iOldCard} AND `bankid` = {$iDepositBankId}";
        
        $this->oDB->query($sSql3);
        if ($this->oDB->errno > 0){
            if ($this->oDB->doRollback() === false){
                return -1;
            }
            return false;
        }
        
        if ($this->oDB->doCommit() === false){
            return -1;
        }
        
        return true;
    }
}
?>