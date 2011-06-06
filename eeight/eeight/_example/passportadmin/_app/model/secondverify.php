<?php
/**
 * 文件 : /_app/model/secondverify.php
 * 功能 : 模型 - 管理员分组
 * 
 * 功能：
 * 	 -	index()						二次审核总调度程序，根据传入值查询采用何种方法
 * 	 -  _firstChangePass()			第一次修改密码操作，未真实修改，等待管理员审核
 *   -  _checkData()				检查数据是否符合规定
 * 	 -  getVerifyList()				获取二次审核记录列表
 *   -  getOne()					获取指定ID记录信息
 * 	 -  verifyPass()				根据状态判断是否真实修改用户密码,如果审核通过则真实修改并且修改记录状态，如果审核未通过则只修改记录状态
 * 	 -  _funExists()				检查函数是否存在
 * 	 -  _firstLoad()				第一次充值操作，未真实操作，等待管理员审核
 * 	 -  verifyLoad()				根据状态判断是否真实充值,如果审核通过则真实充值并且修改记录状态，如果审核未通过则只修改记录状态
 * 	 -  _firstWithdraw()			第一次提现操作，未真实操作，先冻结金额，等待管理员审核
 * 	 -  verifyWithdraw()			根据状态判断是否真实扣款,如果审核通过则真实扣款并且修改记录状态，如果审核未通过则只修改记录状态
 * 
 * @author		louis
 * @version   	1.0		2010-06-08
 * @package		passportadmin
 */
class model_secondverify extends basemodel
{
	
	/**
	 * 关系id
	 *
	 * @var int
	 */
	public $Id;
	
	/**
	 * 操作别名
	 *
	 * @var string
	 */
	public $NickName;
	
	/**
	 * 待操作数据
	 *
	 * @var array
	 * 		aValue['userid']		// 用户id
	 * 		aValue['loginpwd']		// 登录密码
	 * 		aValue['securitypwd']	// 资金密码
	 */
	public $Value;
	
	/**
	 * 待操作数据
	 *
	 * @var array
	 * 		aFee['userid']			// 用户id
	 * 		aFee['fmoney']			// 金额
	 */
	public $Fee;
	
	/**
	 * 函数名称
	 *
	 * @var string
	 */
	public $FunName;
	
	/**
	 * 检查数据条件
	 *
	 * @var array
	 */
	public $Condition;
	
	/**
	 * 操作对象id串
	 *
	 * @var int or string
	 */
	public $UserId;
	
	/**
	 * 操作管理员id
	 *
	 * @var int
	 */
	public $Admin;
	
	/**
	 * 操作管理员
	 *
	 * @var string
	 */
	public $AdminName;
	
	/**
	 * 状态,0为待审核，1为审核通过，2为审核未通过
	 *
	 * @var int
	 */
	public $Status;
	
	/**
	 * 查询开始时间
	 *
	 * @var datetime
	 */
	public $StartTime;
	
	/**
	 * 查询结束时间
	 *
	 * @var datetime
	 */
	public $EndTime;
	
	/**
	 * 审核管理员id
	 *
	 * @var int
	 */
	public $VerifyId;
	
	/**
	 * 审核管理员
	 *
	 * @var string
	 */
	public $VerifyName;
	
	
	/**
	 * 二次审核总调度程序，根据传入值查询采用何种方法
	 *
	 * 
	 * @version 	v1.0	2010-06-09
	 * @author 		louis
	 * 
	 * @return 		mix		-1					// 操作别名为空
	 * 						-2 					// 查询数据不存在
	 * 						-3 					// 操作数据为空
	 */
	public function index(){
		// 别名检查
		if (empty($this->NickName))	return -1;
		$sSql = "SELECT `id`,`function_name` FROM `second_verify_relation` WHERE `nick_name` = '{$this->NickName}'";
		$aResult = $this->oDB->getOne($sSql);
		// 是否已注册了此操作
		if (empty($aResult))	return -2;
		// 是否传入了操作数据
		if (empty($this->Value))		return -3;
		$this->Id 			= $aResult['id'];
		$this->FunName 		= $aResult['function_name'];
		if (empty($this->Fee)){
			return $this->$aResult['function_name']();
		} else { // 人工充值与手续费，事务处理
			// 事务开始
			$this->oDB->doTransaction();
			if ($this->$aResult['function_name']() <= 0){
				$this->oDB->doRollback(); // 事务回滚
				return false;
			}
			// 手续费操作
			$this->Value = $this->Fee;
			$iLastId = 0;
			$iLastId = $this->$aResult['function_name']();
			if ($iLastId <= 0){
				$this->oDB->doRollback(); // 事务回滚
				return false;
			}
			$this->oDB->doCommit(); // 事务提交
			return $iLastId;
		}
	}
	/**
	 * 第一次修改密码操作，未真实修改，等待管理员审核
	 *
	 * @param int		$id							// 关系id
	 * @param string	$sFunName					// 函数名称
	 * @param array 	$aValue						// 数据集
	 * 					$aValue['userid']			// 用户id
	 * 					$aValue['loginpwd']			// 登录密码
	 * 					$aValue['securitypwd']		// 资金密码
	 * 
	 * @version 	v1.0		2010-06-08
	 * @author 		louis
	 * @return 		mix			-4					// 关系表id不正确
	 * 							-5					// 数据数组为空
	 * 							-6					// 函数名称为空
	 * 							-7					// 函数不存在
	 * 							-14					// 数据检查未通过
	 * 							-15 				// 修改后的登录密码与资金密码相同
	 * 							-16 				// 修改后的资金密码与登录密码相同
	 * 							-17 				// 用户不存在
	 * 
	 * @return 		
	 */
	public function _firstChangePass(){
		// 关系表id不正确
		if (!is_numeric($this->Id) || $this->Id <= 0)		return -4;
		// 函数名称为空
		if (empty($this->FunName))							return -6;
		// 数据数组为空
		if (empty($this->Value))							return -5;
		
		$mResult = $this->_funExists();
		if ($mResult === false)								return -7;
		
		// 数据检查
		$aCondition = unserialize($mResult['data']);
		$this->Condition = $aCondition;
		$bResult = $this->_checkData();
		if ($bResult === false)								return -14;
		
		// 向审核记录表中写入数据，不真实修改，等待管理员审核
		$aData = array(); // 需要写入的数据
		$aTemp = array(); // 序列化后的数组数据
		$oUser = new model_user();
		$aUserInfo = $oUser->getUserExtentdInfo($this->Value['user_id']);
		if (empty($aUserInfo))								return -17;
		$aData['user_id'] 		= $this->Value['user_id'];
		$aData['user_name'] 	= $aUserInfo['username'];
		$aData['type_id'] 		= $this->Id;
		$aTemp['user_id'] 		= $this->Value['user_id'];
		$aTemp['loginpwd']		= !empty($this->Value['loginpwd']) ? md5($this->Value['loginpwd']) : '';
		$aTemp['securitypwd'] 	= !empty($this->Value['securitypwd']) ? md5($this->Value['securitypwd']) : '';
		$aData['data']			= serialize($aTemp);
		$aData['admin_id']		= $_SESSION['admin'];
		$aData['admin_name']	= $_SESSION['adminname'];
		$aData['atime'] = $aData['utime'] = date("Y-m-d H:i:s", time());
		
		// 登录密码不能与旧资金密码相同
		if (!empty($aTemp['loginpwd'])){
			$sSql = "SELECT `userid` FROM `users` WHERE `securitypwd`='".$aTemp['loginpwd']."' AND userid = {$this->Value['user_id']}";
			$this->oDB->query( $sSql );
            if( $this->oDB->ar() > 0 )
            {
                return -15;
            }
            
		}
		// 资金密码不能与旧登录密码相同
		if (!empty($aTemp['securitypwd'])){
			$sSql = "SELECT `userid` FROM `users` WHERE `loginpwd`='".$aTemp['securitypwd']."' AND userid = {$this->Value['user_id']}";
			$this->oDB->query( $sSql );
            if( $this->oDB->ar() > 0 )
            {
                return -16;
            }
		}
		return $this->oDB->insert("second_verify_detail", $aData);
	}
	
	
	
	/**
	 * 检查数据是否符合规定
	 * 
	 * @version v1.0	2010-06-09
	 * @author 	louis
	 * 
	 * @return 	mix			-8				// 必须键值不存在
	 * 						-9				// 数据类型不符合
	 * 						-10 			// 数据超出最小值
	 * 						-11 			// 数据超出最大值
	 * 						-12				// 数据规则为空
	 * 						-13				// 受检查的数据为空
	 */
	private  function _checkData(){
		// 基本数据检查
		if (empty($this->Condition))	return -12;
		if (empty($this->Value))		return -13;
		foreach ($this->Condition as $k => $v){
			if ($v['must'] == 1){ // 参数为必须参数
				// 是否存在
				if (!array_key_exists($v['property'], $this->Value))		return -8;
				// 数据类型是否正确
				if (!$v['type']($this->Value[$v['property']]))			return -9;
				// 检查数据范围
				switch ($v['type']){
					case "is_int" :
						if (!empty($v['range']['min'])){
							if ($this->Value[$v['property']] < $v['range']['min']) return -10;
						}
						if (!empty($v['range']['min'])){
							if ($this->Value[$v['property']] > $v['range']['max']) return -11;
						}
					break;
					case "is_string" :
						if (!empty($v['range']['min'])){
							if (strlen($this->Value[$v['property']]) < $v['range']['min']) return -10;
						}
						if (!empty($v['range']['min'])){
							if (strlen($this->Value[$v['property']]) > $v['range']['max']) return -11;
						}
					break;
					case "is_float" :
						if (!empty($v['range']['min'])){
							if (strlen($this->Value[$v['property']]) <= $v['range']['min']) return -10;
						}
						if (!empty($v['range']['min'])){
							if (strlen($this->Value[$v['property']]) >= $v['range']['max']) return -11;
						}
					break;
				}
			} else { // 不必要参数，但是如果存在，则将必须符合规则
				// 如果存在则要检查
				if (array_key_exists($v['property'], $this->Value)){
					// 数据类型是否正确
					if (!$v['type']($this->Value[$v['property']]))			return -9;
					// 检查数据范围
					switch ($v['type']){
						case "is_int" :
							if (!empty($v['range']['min'])){
								if ($this->Value[$v['property']] < $v['range']['min']) return -10;
							}
							if (!empty($v['range']['min'])){
								if ($this->Value[$v['property']] > $v['range']['max']) return -11;
							}
						break;
						case "is_string" :
							if (!empty($v['range']['min'])){
								if (strlen($this->Value[$v['property']]) < $v['range']['min']) return -10;
							}
							if (!empty($v['range']['min'])){
								if (strlen($this->Value[$v['property']]) > $v['range']['max']) return -11;
							}
						break;
						case "is_float" :
							if (!empty($v['range']['min'])){
								if (strlen($this->Value[$v['property']]) <= $v['range']['min']) return -10;
							}
							if (!empty($v['range']['min'])){
								if (strlen($this->Value[$v['property']]) >= $v['range']['max']) return -11;
							}
						break;
					}
				}
			}
		}
		return false;
	}
	
	
	/**
	 * 获取二次审核记录列表
	 *
	 * @version 	v1.0	2010-06-09
	 * @author 		louis
	 */
	public function getVerifyList(){
		$aResult = array(); // 结果集
		$sWhere = ""; // 查询条件
		if (is_numeric($this->UserId) && $this->UserId > 0)		$sWhere .= " AND `user_id` = $this->UserId";
		if (is_numeric($this->Id) && $this->Id > 0)				$sWhere .= " AND `type_id` = $this->Id";
		if (is_numeric($this->Admin) && $this->Admin > 0)		$sWhere .= " AND `admin_id` = $this->Admin";
		if (!empty($this->AdminName))							$sWhere .= " AND `admin_name` LIKE  '%$this->Admin%'";
		if (is_numeric($this->VerifyId) && $this->VerifyId > 0)	$sWhere .= " AND `verify_id` LIKE  '%$this->VerifyId%'";
		if (!empty($this->VerifyName))							$sWhere .= " AND `verify_name` LIKE  '%$this->VerifyName%'";
		if (is_numeric($this->Status) && $this->Status >= 0)	$sWhere .= " AND `status` = $this->Status";
		if (!empty($this->StartTime) && empty($this->EndTime))	$sWhere .= " AND `utime` > '$this->StartTime'";
		if (empty($this->StartTime) && !empty($this->EndTime))	$sWhere .= " AND `utime` < '$this->EndTime'";
		if (!empty($this->StartTime) && !empty($this->EndTime))	$sWhere .= " AND `utime` BETWEEN '$this->StartTime' AND " .
		 																	"'$this->EndTime'";
		$sSql = "SELECT * FROM `second_verify_detail` WHERE 1 " . $sWhere;
		$aResult = $this->oDB->getAll($sSql);
		return $aResult;
	}
	
	
	/**
	 * 查询用户的密码是否是待审核状态
	 * 
	 * @version 	v1.0	2010-06-09
	 * @author 		louis
	 * 
	 * @return 		bool	待审核返回true,否返回false
	 */
	public function isVerify(){
		// 用户id不正确，则直接返回true, 不能修改
		if (!is_numeric($this->UserId) || $this->UserId <= 0)	return true;
		$sSql = "SELECT id FROM second_verify_detail WHERE user_id = {$this->UserId} AND type_id = 1 AND status = 0";
		$aResult = $this->oDB->getOne($sSql);
		return empty($aResult) ? false : true;
	}
	
	
	/**
	 * 获取指定ID记录信息
	 *
	 * @version 	v1.0	2010-06-10
	 * @author 		louis
	 * 
	 * @return 		mix 	成功返回记录结果集,参数错误返回false,否则返回空记录
	 */
	public function getOne(){
		if (!is_numeric($this->Id) || $this->Id <= 0)		return false;
		$sSql = "SELECT * FROM `second_verify_detail` WHERE `id` = {$this->Id}";
		$aResult = $this->oDB->getOne($sSql);
		return $aResult;
	}
	
	
	/**
	 * 根据状态判断是否真实修改用户密码,如果审核通过则真实修改并且修改记录状态，如果审核未通过则只修改记录状态
	 *
	 * @version 	v1.0	2010-6-10
	 * @author 		louis
	 * 
	 * @return 		mix		-1				记录id错误
	 * 						-2 				审核状态错误
	 * 						-3				操作记录不存在
	 * 						-4				用户id不正确
	 * 						-5				修改资金密码失败
	 * 						-6				修改资金密码失败
	 * 						-7 				修改记录状态失败(审核通过)
	 * 						-8 				修改记录状态失败(审核未通过)
	 * 						-9 				用户不存在
	 */
	public function verifyPass(){
		// 检查记录id
		if (!is_numeric($this->Id) || $this->Id <= 0)		return -1;
		// 审核状态
		if (!is_numeric($this->Status) || $this->Status < 1 || $this->Status > 2)		return -2;
		
		// 获取预操作记录信息
		$aResult = $this->getOne();
		if (empty($aResult))								return -3;
		$aData = unserialize($aResult['data']);
		if ($this->Status == 1){ // 审核通过，修改用户密码，修改记录状态为审核通过
			// 检查用户id
			if (!is_numeric($aData['user_id']) || $aData['user_id'] <= 0 )	return -4;
			$oUser = new model_user();
			// 用户是否存在
			$aUserInfo = $oUser->getUserExtentdInfo($aData['user_id']);
			if (empty($aUserInfo))							return -9;
			$aTemp = array(); // 修改数据
			$sTempWhereSql = ""; // 执行条件
			$this->oDB->doTransaction(); // 事务开始
			if (!empty($aData['loginpwd'])){ // 修改登录密码
				$aTemp['loginpwd'] = $aData['loginpwd'];
				$sTempWhereSql = " `userid` = {$aData['user_id']} AND `securitypwd` != '{$aData['loginpwd']}' LIMIT 1";
				$iResult = $this->oDB->update( 'users', $aTemp, $sTempWhereSql );
				if( $this->oDB->errno() > 0)
		        {//更新低频密码失败
		            $this->oDB->doRollback(); // 事务回滚
		            return -5;
		        }
		        unset($aTemp);
		        $sTempWhereSql = "";
			}
			if (!empty($aData['securitypwd'])){ // 修改资金密码
				$aTemp['securitypwd'] = $aData['securitypwd'];
				$sTempWhereSql = " `userid` = {$aData['user_id']} AND `loginpwd` != '{$aData['securitypwd']}' LIMIT 1";
				$iResult = $this->oDB->update( 'users', $aTemp, $sTempWhereSql );
				if( $this->oDB->errno() > 0)
		        {//更新低频密码失败
		            $this->oDB->doRollback(); // 事务回滚
		            return -6;
		        }
		        unset($aTemp);
		        $sTempWhereSql = "";
			}
			// 修改记录状态
			$aTemp['status'] 		= $this->Status;
			$aTemp['verify_id'] 	= $_SESSION['admin'];
			$aTemp['verify_name'] 	= $_SESSION['adminname'];
			$aTemp['utime'] = date('Y-m-d H:i:s');
			$sTempWhereSql = " `id` = {$this->Id} AND `user_id` = {$aData['user_id']} AND `status` = 0 LIMIT 1";
			$iResult = $this->oDB->update( 'second_verify_detail', $aTemp, $sTempWhereSql );
			if( $this->oDB->errno() > 0)
	        {// 更新修改密码记录失败
	            $this->oDB->doRollback(); // 事务回滚
	            return -7;
	        }
	        if( $iResult !== 1)
	        {// 无修改密码记录可更改
	            $this->oDB->doRollback(); // 事务回滚
	            return -7;
	        }
	        $this->oDB->doCommit(); // 事务提交
	        return $iResult;
		} else { // 审核未通过，修改记录状态为审核未通过
			// 修改记录状态
			$aTemp['status'] 		= $this->Status;
			$aTemp['verify_id'] 	= $_SESSION['admin'];
			$aTemp['verify_name'] 	= $_SESSION['adminname'];
			$aTemp['utime'] = date('Y-m-d H:i:s');
			$sTempWhereSql = " `id` = {$this->Id} AND `user_id` = {$aData['user_id']} AND `status` = 0 LIMIT 1";
			$iResult = $this->oDB->update( 'second_verify_detail', $aTemp, $sTempWhereSql );
			if( $this->oDB->errno() > 0)
	        {// 更新修改密码记录失败
	            return -8;
	        }
	        if( $iResult !== 1)
	        {// 无修改密码记录可更改
	            return -8;
	        }
	        return $iResult;
		}
	}
	
	
	/**
	 * 第一次充值操作，未真实操作，等待管理员审核
	 *
	 * @param int		$this->Id					// 关系id
	 * @param string	$this->FunName				// 函数名称
	 * @param array 	$this->Value				// 数据集
	 * 					$this->Value['user_id']		// 用户id
	 * 					$this->Value['fmoney']		// 资金
	 * 					$this->Value['order_type']	// 账变类型
	 * 
	 * @version 	v1.0		2010-06-10
	 * @author 		louis
	 * @return 		mix			-4					// 关系表id不正确
	 * 							-5					// 数据数组为空
	 * 							-6					// 函数名称为空
	 * 							-7					// 函数不存在
	 * 							-14					// 数据检查未通过
	 * 							-15 				// 用户不存在
	 * 
	 * @return 		
	 */
	private function _firstLoad(){
		// 关系表id不正确
		if (!is_numeric($this->Id) || $this->Id <= 0)		return -4;
		// 函数名称为空
		if (empty($this->FunName))							return -6;
		// 数据数组为空
		if (empty($this->Value))							return -5;
		
		// 检查函数是否存在
		$mResult = $this->_funExists();
		if ($mResult === false)								return -7;
		
		// 数据检查
		$aCondition = unserialize($mResult['data']);
		$this->Condition = $aCondition;
		$bResult = $this->_checkData();
		if ($bResult === false)								return -14;
		
		// 向审核记录表中写入数据，不真实修改，等待管理员审核
		$aData = array(); // 需要写入的数据
		$aTemp = array(); // 序列化后的数组数据
		$oUser = new model_user();
		$aUserInfo = $oUser->getUserExtentdInfo($this->Value['user_id']);
		if (empty($aUserInfo))								return -15;
		$aData['user_id'] 		= $this->Value['user_id'];
		$aData['user_name'] 	= $aUserInfo['username'];
		$aData['type_id'] 		= $this->Id;
		$aTemp['user_id'] 		= $this->Value['user_id'];
		$aTemp['fmoney']		= strval($this->Value['fmoney']);// 如果使用floatval后，序列化后的数据，不准确 eg 1.69 => 1.68123123
		$aTemp['order_type'] 	= $this->Value['order_type'];
		$aTemp['description'] 	= $this->Value['description'];
		$aData['data']			= serialize($aTemp);
		$aData['admin_id']		= $_SESSION['admin'];
		$aData['admin_name']	= $_SESSION['adminname'];
		$aData['atime'] = $aData['utime'] = date("Y-m-d H:i:s", time());
		return $this->oDB->insert("second_verify_detail", $aData);
	}
	
	
	/**
	 * 检查函数是否存在
	 *
	 * @version 	v1.0	2010-06-10
	 * @author 		louis
	 * 
	 * @return 		bool	存在返回true,否则返回false
	 */
	private function _funExists(){
		// 关系表id不正确
		if (!is_numeric($this->Id) || $this->Id <= 0)		return false;
		// 函数名称为空
		if (empty($this->FunName))							return false;
		$sSql = "SELECT `id`,`data` FROM `second_verify_function` WHERE `relation_id` = '{$this->Id}' AND `function_name` = '{$this->FunName}'";
		$aResult = $this->oDB->getOne($sSql);
		return empty($aResult) ? false : $aResult;
	}
	
	
	/**
	 * 根据状态判断是否真实充值,如果审核通过则真实充值并且修改记录状态，如果审核未通过则只修改记录状态
	 *
	 * @version 	v1.0	2010-06-11
	 * @author 		louis
	 * 
	 * @return 		mix		-1				记录id错误
	 * 						-2 				审核状态错误
	 * 						-3				操作记录不存在
	 * 						-4				用户id不正确
	 * 						-5 				用户不存在
	 * 						-6 				锁资金账户失败
	 * 						-7				写入账变失败
	 * 						-8				修改记录状态失败(审核通过)
	 * 						-9				修改记录状态失败(审核未通过)
	 * 						-10				无充值记录可更改(例：重复执行)
	 */
	public function verifyLoad(){
		if (!is_numeric($this->Id) || $this->Id <= 0)		return -1;
		if (!is_numeric($this->Status) || $this->Status < 1 || $this->Status > 2)		return -2;
		
		// 获取预操作记录信息
		$aResult = $this->getOne();
		if (empty($aResult))								return -3;
		$aData = unserialize($aResult['data']);
		
		if ($this->Status == 1){ // 审核通过，充值，修改记录状态为审核通过
			// 检查用户id
			if (!is_numeric($aData['user_id']) || $aData['user_id'] <= 0 )	return -4;
			$oUser = new model_user();
			$aUserInfo = $oUser->getUserExtentdInfo($aData['user_id']);
			if (empty($aUserInfo))							return -5;
			$aTemp = array(); // 修改数据
			$sTempWhereSql = ""; // 执行条件
			$oUserFund = new model_userfund();
			if ($oUserFund->switchLock($aData['user_id'],0,TRUE) === false){ // 锁用户资金账户
				return -6;
			}
			$this->oDB->doTransaction(); // 事务开始
			// 向用户充值
			$oOrder = new model_orders();
			$aOrders = array();
	        $aOrders['iFromUserId']  = $aData['user_id'];
	        $aOrders['iOrderType']   = $aData['order_type'];
	        $aOrders['fMoney']       = floatval($aData['fmoney']);
	        $aOrders['sActionTime']  = date("Y-m-d H:i:s",time());
	        $aOrders['sDescription'] = $aData['description'];
	        $aOrders['iAdminId']     = $_SESSION['admin'];
	        $aOrders['iChannelID']   = 0; // 银行大厅
	        $mResult = $oOrder->addOrders( $aOrders );
	        if( TRUE !== $mResult )
	        {
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -7;
	        }
			// 修改记录状态
			$aTemp['status'] 		= $this->Status;
			$aTemp['verify_id'] 	= $_SESSION['admin'];
			$aTemp['verify_name'] 	= $_SESSION['adminname'];
			$aTemp['utime'] = date('Y-m-d H:i:s');
			$sTempWhereSql = " `id` = {$this->Id} AND `user_id` = {$aData['user_id']} AND `status` = 0 LIMIT 1";
			$iResult = $this->oDB->update( 'second_verify_detail', $aTemp, $sTempWhereSql );
			if( $this->oDB->errno() > 0)
	        {//更新充值状态失败
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -8;
	        }
	        if( $iResult !== 1)
	        {
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -10;
	        }
	        $this->oDB->doCommit(); // 事务提交
	        $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	        return $iResult;
		} else { // 审核未通过，修改记录状态为审核未通过
			// 修改记录状态
			$aTemp['status'] 		= $this->Status;
			$aTemp['verify_id'] 	= $_SESSION['admin'];
			$aTemp['verify_name'] 	= $_SESSION['adminname'];
			$aTemp['utime'] = date('Y-m-d H:i:s');
			$sTempWhereSql = " `id` = {$this->Id} AND `user_id` = {$aData['user_id']} AND `status` = 0 LIMIT 1";
			$iResult = $this->oDB->update( 'second_verify_detail', $aTemp, $sTempWhereSql );
			if( $this->oDB->errno() > 0)
	        {//更新充值状态失败
	            return -9;
	        }
	        if( $iResult !== 1)
	        {
	            return -10;
	        }
	        return $iResult;
		}
	}
	
	
	
	/**
	 * 第一次提现操作，未真实操作，先冻结金额，等待管理员审核
	 *
	 * @param int		$this->Id					// 关系id
	 * @param string	$this->FunName				// 函数名称
	 * @param array 	$this->Value				// 数据集
	 * 					$this->Value['user_id']		// 用户id
	 * 					$this->Value['fmoney']		// 资金
	 * 					$this->Value['order_type']	// 账变类型
	 * 
	 * @version 	v1.0		2010-06-10
	 * @author 		louis
	 * @return 		mix			-4					// 关系表id不正确
	 * 							-5					// 数据数组为空
	 * 							-6					// 函数名称为空
	 * 							-7					// 函数不存在
	 * 							-14					// 数据检查未通过
	 * 							-15 				// 用户不存在
	 * 							-16					// 用户加锁失败
	 * 							-17					// 获取其它平台资金余额失败
	 * 							-18					// 其它平台资金余额有负余额
	 * 							-19					// 获取总代信用余额失败
	 * 							-20					// 超出最大可提现金额
	 * 							-21					// 冻结用户提现资金失败
	 * 							-22					// 写入人工提现请求失败
	 * 
	 * @return 		
	 */
	private function _firstWithdraw(){
		// 关系表id不正确
		if (!is_numeric($this->Id) || $this->Id <= 0)		return -4;
		// 函数名称为空
		if (empty($this->FunName))							return -6;
		// 数据数组为空
		if (empty($this->Value))							return -5;
		// 用户是否存在
		$oUser = new model_user();
		$aUserInfo = $oUser->getUserExtentdInfo($this->Value['user_id']);
		if (empty($aUserInfo))								return -15;
		
		// 检查函数是否存在
		$mResult = $this->_funExists();
		if ($mResult === false)								return -7;
		
		// 数据检查
		$aCondition = unserialize($mResult['data']);
		$this->Condition = $aCondition;
		$bResult = $this->_checkData();
		if ($bResult === false)								return -14;
		
    	$oUserFund = new model_userfund();
    	// 锁定用户资金
    	if( FALSE == $oUserFund->switchLock($this->Value['user_id'], 0, TRUE) )
        {
            return -16;
        }
		
		$this->oDB->doTransaction(); // 事务开始
		// 检查提现金额是否合理，不能大于用户的可用余额，总代用户要加上信用余额
		$fMaxMoney = 0.00;// 用户最大可提现金额
		if ($oUser->isTopProxy($this->Value['user_id']) === true){ // 总代
			$oWithdraw = new model_withdrawel();
			$aUserAcc = $oUserFund->getProxyFundList($this->Value['user_id']);
	        $fMaxMoney = $oWithdraw->getCreditUserMaxMoney( $this->Value['user_id'], $this->Value['fmoney'] );
	        if ($fMaxMoney == "error"){ // 获取总代信用资金
	        	$this->oDB->doRollback(); // 回滚事务
            	$oUserFund->switchLock($this->Value['user_id'], 0, false); // 用户解锁
	        	return -19;
	        }
		} else { // 非总代
	        $sFields   = " uf.`availablebalance`";
	        $aUserinfo = $oUserFund->getFundByUser( $this->Value['user_id'], '', 0, false );
	        $fMaxMoney = $aUserinfo['availablebalance'];
		}
		
		if ($this->Value['fmoney'] > $fMaxMoney){ // 超出最大可提金额
			return -20;
		}
    		
		// 检查用户开通的频道是否有负余额
		$oChannel = A::singleton("model_userchannel");
        $aChannel = $oChannel->getUserChannelList( $this->Value['user_id'] );
        if( !empty($aChannel) && isset($aChannel[0]) && is_array($aChannel[0]) )
        {//如果有其他频道
            foreach( $aChannel[0] as $v )
            {//依次获取频道余额
                $oChannelApi = new channelapi( $v['id'], 'getUserCash', FALSE );
                $oChannelApi->setTimeOut(10);            // 设置读取超时时间
                $oChannelApi->setResultType('serial');   // 设置返回数据类型 json | serial
                $oChannelApi->sendRequest( array("iUserId" => $this->Value['user_id']) );    // 发送结果集
                $aResult = $oChannelApi->getDatas();
                if( empty($aResult) || !is_array($aResult) || $aResult['status'] == 'error' )
                {//调用API获取结果失败，可能资金帐户不存在
                   $this->oDB->doRollback(); // 回滚事务
                   $oUserFund->switchLock($this->Value['user_id'], 0, false); // 用户解锁
                   return -17;
                }
                if( floatval($aResult['data']) < 0 )
                {//余额小于0
                	$this->oDB->doRollback(); // 回滚事务
                	$oUserFund->switchLock($this->Value['user_id'], 0, false); // 用户解锁
                    return -18;
                }
            }
        }
        
        // 冻结用户提现金额，等待管理员审核
        $oOrder = new model_orders();
		$aOrders = array();
        $aOrders['iFromUserId']  = $this->Value['user_id'];
        $aOrders['iOrderType']   = ORDER_TYPE_RGTXDJ;
        $aOrders['fMoney']       = floatval($this->Value['fmoney']);
        $aOrders['sActionTime']  = date("Y-m-d H:i:s",time());
        $aOrders['iAdminId']     = $_SESSION['admin'];
        $aOrders['iChannelID']   = 0; // 银行大厅
        $mResult = $oOrder->addOrders( $aOrders );
        if( TRUE !== $mResult )
        { // 冻结用户提现金额失败
            $this->oDB->doRollback(); // 事务回滚
            $oUserFund->switchLock( $this->Value['user_id'], 0, FALSE );  // 资金解锁
            return -21;
        }
		
		// 向审核记录表中写入数据，不真实修改，等待管理员审核
		$aData = array(); // 需要写入的数据
		$aTemp = array(); // 序列化后的数组数据
		$aData['user_id'] 		= $this->Value['user_id'];
		$aData['user_name'] 	= $aUserInfo['username'];
		$aData['type_id'] 		= $this->Id;
		$aTemp['user_id'] 		= $this->Value['user_id'];
		$aTemp['fmoney']		= strval($this->Value['fmoney']);// 如果使用floatval后，序列化后的数据，不准确 eg 1.69 => 1.68123123
		$aTemp['freeze'] 		= $this->Value['freeze'];
		$aTemp['order_type'] 	= $this->Value['order_type'];
		$aTemp['description'] 	= $this->Value['description'];
		$aData['data']			= serialize($aTemp);
		$aData['admin_id']		= $_SESSION['admin'];
		$aData['admin_name']	= $_SESSION['adminname'];
		$aData['atime'] = $aData['utime'] = date("Y-m-d H:i:s", time());
		$mLastId = $this->oDB->insert("second_verify_detail", $aData);
		if ($mLastId === false ){ // 写入人工提现请求失败
			$this->oDB->doRollback(); // 事务回滚
            $oUserFund->switchLock( $this->Value['user_id'], 0, FALSE );  // 资金解锁
            return -22;
		}
		$this->oDB->doCommit(); // 事务提交
		$oUserFund->switchLock( $this->Value['user_id'], 0, FALSE );  // 资金解锁
		return $mLastId;
	}
	
	
	
	/**
	 * 根据状态判断是否真实扣款,如果审核通过则真实扣款并且修改记录状态，如果审核未通过则只修改记录状态
	 *
	 * @version 	v1.0	2010-06-11
	 * @author 		louis
	 * 
	 * @return 		mix		-1				记录id错误
	 * 						-2 				审核状态错误
	 * 						-3				操作记录不存在
	 * 						-4				用户id不正确
	 * 						-5 				用户不存在
	 * 						-6 				锁资金账户失败
	 * 						-7				写入账变失败
	 * 						-8				修改记录状态失败(审核通过)
	 * 						-9				修改记录状态失败(审核未通过)
	 * 						-10				提现资金解冻失败
	 */
	public function verifyWithdraw(){
		if (!is_numeric($this->Id) || $this->Id <= 0)		return -1;
		if (!is_numeric($this->Status) || $this->Status < 1 || $this->Status > 2)		return -2;
		
		// 获取预操作记录信息
		$aResult = $this->getOne();
		if (empty($aResult))								return -3;
		$aData = unserialize($aResult['data']);
		// 检查用户id
		if (!is_numeric($aData['user_id']) || $aData['user_id'] <= 0 )	return -4;
		$oUser = new model_user();
		$aUserInfo = $oUser->getUserExtentdInfo($aData['user_id']);
		if (empty($aUserInfo))							return -5;
		$oUserFund = new model_userfund();
		
		if ($this->Status == 1){ // 审核通过，提现，修改记录状态为审核通过
			$aTemp = array(); // 修改数据
			$sTempWhereSql = ""; // 执行条件
			
			if ($oUserFund->switchLock($aData['user_id'],0,TRUE) === false){ // 锁用户资金账户
				return -6;
			}
			$this->oDB->doTransaction(); // 事务开始
			$oOrder = new model_orders();
			// 提现资金解冻
			$aFreeze = array();
	        $aFreeze['iFromUserId']  = $aData['user_id'];
	        $aFreeze['iOrderType']   = $aData['freeze'];
	        $aFreeze['fMoney']       = floatval($aData['fmoney']);
	        $aFreeze['sActionTime']  = date("Y-m-d H:i:s",time());
	        $aFreeze['iAdminId']     = $_SESSION['admin'];
	        $aFreeze['iChannelID']   = 0; // 银行大厅
	        $mFreeze = $oOrder->addOrders( $aFreeze );
	        if( TRUE !== $mFreeze )
	        {
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -10;
	        }
			// 扣款
			$aOrders = array();
	        $aOrders['iFromUserId']  = $aData['user_id'];
	        $aOrders['iOrderType']   = $aData['order_type'];
	        $aOrders['fMoney']       = floatval($aData['fmoney']);
	        $aOrders['sActionTime']  = date("Y-m-d H:i:s",time());
	        $aOrders['sDescription'] = $aData['description'];
	        $aOrders['iAdminId']     = $_SESSION['admin'];
	        $aOrders['iChannelID']   = 0; // 银行大厅
	        $mResult = $oOrder->addOrders( $aOrders );
	        if( TRUE !== $mResult )
	        {
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -7;
	        }
			// 修改记录状态
			$aTemp['status'] 		= $this->Status;
			$aTemp['verify_id'] 	= $_SESSION['admin'];
			$aTemp['verify_name'] 	= $_SESSION['adminname'];
			$aTemp['utime'] = date('Y-m-d H:i:s');
			$sTempWhereSql = " `id` = {$this->Id} AND `user_id` = {$aData['user_id']} AND `status` = 0 LIMIT 1";
			$iResult = $this->oDB->update( 'second_verify_detail', $aTemp, $sTempWhereSql );
			if( $this->oDB->errno() > 0)
	        {//更新记录状态失败
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -8;
	        }
	        if( $iResult !== 1)
	        {// 无提现记录可更改
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -8;
	        }
	        $this->oDB->doCommit(); // 事务提交
	        $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	        return $iResult;
		} else { // 审核未通过，修改记录状态为审核未通过
			if ($oUserFund->switchLock($aData['user_id'],0,TRUE) === false){ // 锁用户资金账户
				return -6;
			}
			$this->oDB->doTransaction(); // 事务开始
			$oOrder = new model_orders();
			// 提现资金解冻
			$aFreeze = array();
	        $aFreeze['iFromUserId']  = $aData['user_id'];
	        $aFreeze['iOrderType']   = $aData['freeze'];
	        $aFreeze['fMoney']       = floatval($aData['fmoney']);
	        $aFreeze['sActionTime']  = date("Y-m-d H:i:s",time());
	        $aFreeze['iAdminId']     = $_SESSION['admin'];
	        $aFreeze['iChannelID']   = 0; // 银行大厅
	        $mFreeze = $oOrder->addOrders( $aFreeze );
	        if( TRUE !== $mFreeze )
	        {
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -10;
	        }
			// 修改记录状态
			$aTemp['status'] 		= $this->Status;
			$aTemp['verify_id'] 	= $_SESSION['admin'];
			$aTemp['verify_name'] 	= $_SESSION['adminname'];
			$aTemp['utime'] = date('Y-m-d H:i:s');
			$sTempWhereSql = " `id` = {$this->Id} AND `user_id` = {$aData['user_id']} AND `status` = 0 LIMIT 1";
			$iResult = $this->oDB->update( 'second_verify_detail', $aTemp, $sTempWhereSql );
			if( $this->oDB->errno() > 0)
	        {//更新记录状态失败
	        	$this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -9;
	        }
	        if( $iResult !== 1)
	        {// 无提现记录可更改
	            $this->oDB->doRollback(); // 事务回滚
	            $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	            return -9;
	        }
	        $this->oDB->doCommit(); // 事务提交
	        $oUserFund->switchLock( $aData['user_id'], 0, FALSE );  // 资金解锁
	        return $iResult;
		}
	}
}