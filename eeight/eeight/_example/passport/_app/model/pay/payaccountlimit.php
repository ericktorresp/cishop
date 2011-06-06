<?php
/**
 * 文件 : _app/model/payportaccountset.php
 * 功能 : 支付接口管理之分账户&用户权限 管理功能 模型
 * 
 *  用户在登录时，以SESSION保存用户的总代ID以及一代ID，以备使用
 * 
 * @name 	payportaccountset.php
 * @package	payport
 * @version	0.1 4/1/2010
 * @author	Jim
 */

class model_pay_payaccountlimit extends model_pay_payaccountinfo
{
	/**
	 * 用户ID (甲)
	 * @var int
	 */
	public $UserId;
	/**
	 * 用户标记 (为总代值为0，否则为总代ID)
	 *
	 * @var int
	 */
	public $UserLevel;
	/**
	 * 分账户ID (甲)
	 * @var int
	 */
	public $AccId;
	
	/**
	 * 用户ID数组 (乙)
	 * @var array('id'=>'name')
	 */
	public $UserIdArray;
	
	/**
	 * 分账户数组 (乙)
	 * @var array('id'=>'finance name')
	 */
	public $AccIdArray;
	
	/**
	 * 批量功能传入的数组数据
	 * @var array('userid','username','ppid','ppname','accid','accname','isactive')
	 */
	public $AccSetData;
	/**
	 * 是否管理调用
	 * @var bool
	 */
	public $MGR;
	
	private $TableName='user_payport_limit';
	
	
	public function __construct($iPayAccid=0){
		parent::__construct($iPayAccid=0);
	}
	
	
	/**
	 * 列表用户（总代）
	 *
	 * @return array(0 => 'userid','username')
	 */
	public function allTopUserList(){
		//获取所有总代列表
		$sSql = 'SELECT userid,username FROM usertree WHERE `isdeleted` = 0 AND userid=lvtopid Order by username ASC';
		return $this->oDB->getAll($sSql);		
	}
	

	/**
	 * 列表用户（一代）
	 *
	 * @return array( 0 => 'lvtopid','userid','username')
	 */
	public function allProxyUserList($iTopUser){
		//获取一代列表
		$sSql = 'SELECT lvtopid,userid,username FROM usertree WHERE userid=lvproxyid AND lvtopid = '.$iTopUser.' Order by username ASC';
		return $this->oDB->getAll($sSql);
	}
	
	
	/**
	 * 列表可使用的分账户
	 *   有用户ID情况下区分前后台操作
	 *   无用户ID则列出所有分账户
	 *@param $bRelax  仅用户关系绑定时为真,为了取得总代没有使用到的账户
	 * @return array('accid'=>'accname')
	 */
	public function validAccList($bRelax=false){
		$oPayAccList = new model_pay_payaccountlist();
		$bBi = $this->MGR ? 0 : 1;
		//提取所有状态激活的分账户
		$aAll = $oPayAccList->allList($bBi);
		$iUserId = $this->UserId;

		if ( !empty($iUserId) ){
			// 提取总代使用的分账户
			// MGR=true 管理模式下,无论状态 isactive
			$sFixstr =  $this->MGR ? '' : ' AND `isactive`=1';
			
			// 满足一代未绑定,从总代继承分账户绑定关系 array(一代ID，总代ID)
			if ( is_array($iUserId) ){ 
				$iUserIdL1 = intval($iUserId[0]);
				$iUserIdL2 = intval($iUserId[1]);
				
			}else{
				$iUserIdL1 = intval($iUserId);
				$iUserIdL2 = 0;
				
			}
			// 判断为总代时,第一次不查询
			if ($iUserIdL1 > 0){
				$sSql = "SELECT `pp_acc_id`,`ppid` FROM `".$this->TableName."` WHERE `user_id`=".$iUserIdL1.$sFixstr;
				$aVaild = $this->oDB->getAll($sSql);
			}else{
				$aVaild = array();
			}
			
			if ( (count($aVaild) <= 0) && ($iUserIdL2 > 0) ) {
				// 一代ID没有被设置，查询总代ID的
				
				$sSql = "SELECT `pp_acc_id`,`ppid` FROM `".$this->TableName."` WHERE `user_id`=".$iUserIdL2.$sFixstr;
				$aVaild = $this->oDB->getAll($sSql);
			}
			
			/*}
			else{
				// 管理后台使用
				$sSql = "SELECT `pp_acc_id`,`ppid` FROM `".$this->TableName."` WHERE `user_id`=".$iUserId.$sFixstr;
				$aVaild = $this->oDB->getAll($sSql);
			}*/
			
			// 确认为前台调用,直接返回该用户可用的
			if (($this->MGR !== TRUE) && ($bRelax !== TRUE)){
				return $aVaild;
			}
			
			// 匹配返回剩余可用的(未设置的) 后台配置关系使用
			if ( is_array($aVaild) && (count($aVaild) > 0) ){
				$aNewValid = $aTmp = $aPay = array();
				foreach ($aVaild AS $aA ) $aNewValid[] = $aA['pp_acc_id'];
				//返回该总代未用的PayAccountID
				$aTmp = array();
				foreach ($aAll AS $aPay){
						if(array_search($aPay['aid'],$aNewValid) === false) array_push($aTmp,$aPay);	
				}
				return $aTmp;
			}else{
				return $aAll;
			}
		
		}else{
			
			return $aAll;
			
		}
	}
	
	/**
	 * 列举对用户无效的accid 
	 */
	public function valiableList($iUserid,$ilv){
		if ( !is_numeric($iUserid) || !is_numeric($ilv) ) return false;
		
		if ($ilv > 0){
			//正在设置一代,获取其总代已设的 accid
			$sSql = "SELECT `pp_acc_id` FROM `".$this->TableName."` WHERE `user_id`=".$ilv;
			
		}else{
			//正在设置总代,获取其下一代已设的accid
			$sSql = "SELECT `pp_acc_id` FROM `".$this->TableName."` WHERE `user_level`=".$iUserid;
			
		}

		$aVaild = $this->oDB->getAll($sSql);
		if ( count($aVaild) > 0 ){
			foreach ($aVaild AS $aR){
				$aRe[ $aR['pp_acc_id'] ] = $aR['pp_acc_id'];
			}
			return $aRe;
		}else{
			return array();
		}		
	}
	/**
	 * 提取总代的一代设置过的账户
	 *  PHP整理显示数组时去除掉该总代不能使用的分账户
	 * @return array()
	 */
	public function getProxySeted(){
			$sSql = "SELECT `ppid`,`user_level`,`pp_acc_id`,`user_id`,`isactive` FROM ".$this->TableName." WHERE `user_level`>0";
			
			return $this->oDB->getAll($sSql);
	}
	
	/**
	 * 提取一代的总代设置的账户
	 *
	 * @return array
	 */
	public function getTopSeted($iTopUser){
		if ($iTopUser <= 0) return false;
		if ($this->MGR === true){
			$sFiled = '*';
		}else{
			$sFiled = '`ppid`,`user_level`,`pp_acc_id`,`user_id`,`isactive`';
		}
		$sSql = "SELECT ".$sFiled." FROM ".$this->TableName." WHERE `user_id`=".$iTopUser." Order by user_name ASC";
		return $this->oDB->getAll($sSql);
	}
	
	/**
	 * 获取关系表，已设置的记录，依总代、一代分别提取 (包含未激活的)
	 *@param $iLv 标记0表总代 或总代ID表一代
	 */
	public function getSetedList($iLv=0){
		if ($this->MGR === true){
			$sFiled = '*';
		}else{
			$sFiled = '`ppid`,`user_level`,`pp_acc_id`,`user_id`,`isactive`';
		}
		if ($iLv == 0){
			$sWhere = ' `user_level`='.$iLv;
		}else{
			$sWhere = ' `user_level`='.$iLv;
		}
		$sSql = "SELECT ".$sFiled." FROM ".$this->TableName." WHERE ".$sWhere." Order by user_name ASC";
		//print_rr($sSql,1,1);
		return $this->oDB->getAll($sSql);
	}
	
	
	/**
	 * 有分账户ID情况下，列表该账户与哪些用户ID关联 (暂不用)
	 *@return array('userid'=>'username');
	 */
	public function validUserList(){
		if ($this->AccId){
			$sSql = "SELECT user_level,user_id,user_name,isactive FROM ".$this->TableName." WHERE pp_acc_id='".$this->AccId."' Order by user_level,user_name ASC";
			return $this->oDB->getAll($sSql);
		}else{
			return -1;
		}
	}
	
	
	/**
	 * 保存配置 (批量保存)
	 *@param  $iLv  标记操作什么类型的用户，默认操作0所有总代，有ID传入则只操作某一个总代
	 * @param $sName 用户名(配置一代时会用到)
	 */
	public function setLimitList($iLv=0,$sName=''){
	
		if ( !$this->AccSetData  ||  !is_array($this->AccSetData) ) return false;
		
		$sSql =  $sTotalSql = '';
		$sSql0 = "REPLACE INTO `".$this->TableName."` (`ppid`,`pp_name`,`pp_acc_id`,`pp_acc_alias`,`user_level`,`user_id`,`user_name`,`isactive`,`utime`) VALUES";
			
		$ii = 1;
		$iC = 0;
		$aAvalibleAid = array();
		foreach ($this->AccSetData AS $aAccArray){
			!$iLv or $aAvalibleAid[$aAccArray['accid']] = $aAccArray['accname'];
			$suser_level = $iLv;
			$suser_id = $aAccArray['userid'];
			$suser_name = $aAccArray['username'];
			$sppid = $aAccArray['ppid'];
			$spp_name = $aAccArray['ppname'];
			$spp_acc_id    = $aAccArray['accid'];
			$spp_acc_alias = $aAccArray['accname'];
			$sisactive 	= !empty($aAccArray['isactive']) ? $aAccArray['isactive'] : '0';
			$sutime = date('Y-m-d H:i:s');

			if ($ii == 1)	$sSql = $sSql0;
			$sSql .= " ($sppid,'$spp_name',$spp_acc_id,'$spp_acc_alias',$suser_level,$suser_id,'$suser_name',$sisactive,'$sutime'),";
	
			if ($ii == 30){
				$sSql = substr($sSql,0,-1);
				$aTotalSql[] = $sSql;
				$ii = 0;
				$sSql = '';
			}
			
			$ii++;
		}
		// add 剩余的sql
		if ($ii > 1){
			$sSql = substr($sSql,0,-1);
			$aTotalSql[] = $sSql;
		}
		
		// 加装事务 
		$this->oDB->doTransaction();
		foreach ($aTotalSql AS $sl){
			$result = $this->oDB->query($sl);
			if ($result === FALSE){
				$this->oDB->doRollback();
				return false;
			}
		}
		$this->oDB->commit();
		return true;
		
	}
	
	/**
	 * 删除激活关系
	 *
	 * @param array $aArray
	 * @return bool
	 */
	public function deleteLimit($aArray){
	
		if (!is_array($aArray)){
			return false;
		}
		
		$iC = 0;
		$aAvalibleAid = array();
		foreach ($aArray AS $aArr){
			$sSql = '';
			if (  (is_numeric( intval($aArr['ppid']) ) && is_numeric( intval($aArr['userid']) ) ) ){
				$sSql = 'DELETE FROM '.$this->TableName.' WHERE `ppid`='.$aArr['ppid'].' AND `user_id`='.$aArr['userid'];
				$this->oDB->query($sSql);
				if( $this->oDB->ar() && $this->oDB->errno() <= 0 ){
						$iC ++;
					}else{
						$iC --;
				}
			
			}
		}
				
		return $iC;
	}
	
	
	/**
	 * 用于实现支付接口 禁用 按钮的全局功能
	 *@param $iPPid
	 *@param $bCall  false=直接对关系的操作  true=由其他调用的关联处理
	 * @return bool
	 */
	public function disable($iPPid){
		//将已激活的关系置为 9
		$aTmp = array('isactive' => 9, 'utime' => date('Y-m-d H:i:s') );
		$sCondo = "ppid=".$iPPid." AND isactive=1";
		
		return $this->oDB->update($this->TableName, $aTmp, $sCondo);
	}
	
		
	/**
	 * 检查用户使用限制
	 *
	 * @param int $iUserId 用户ID
	 * @param int $iAccId 分账户ID
	 * 
	 * @return bool
	 */
	public function status($iUserId=false,$iAccId=false){
		if (!is_numeric($iUserId) || !is_numeric($iAccId) ) return false;
		$sSql = "SELECT `isactive` FROM ".$this->TableName." WHERE `pp_acc_id`='".$iUserId."' AND `user_id`='".$iAccId."'";
		$aStatus = $this->oDB->getOne($sSql);
		if ($aStatus['isactive'] == 0){
			return false;
		}else{
			return true;
		}
	}
	
	
	/**
	 * 冗余数据,关联数据 更新处理
	 *
	 * @param array $aArray array(处理关键字,旧字符,update操作数组)
	 * @param string $sType 直接执行或只返回SQL
	 */
	public function rudundance($aArr=array(),$sType=NULL){
		if (!is_array($aArr)) return false;
		$sSql = "UPDATE `".$this->TableName."` SET ";
		$sWhere = '';
		$sCurrTimex = date('Y-m-d H:i:s');
		switch ($aArr[0]){
			case 'pp_name':
				$sSql .= "`pay_name` = '".$aArr[2]."', `utime`='".$sCurrTimex."'";
				$sWhere .=  " `pp_name` = '".$aArr[1]."' ";	//接口类调用，修改接口类名
			break;
			case 'pp_acc_alias':
				$sSql .= "`pp_acc_alias` = '".$aArr[2]."', `utime`='".$sCurrTimex."'";
				$sWhere .=  " `pp_acc_alias` = '".$aArr[1]."' ";	//分账户类调用，修改分账户财务名
				break;
			case 'pp_acc_id':
				$sSql .= "`isactive` = ".$aArr[2].", `utime`='".$sCurrTimex."'";
				$sWhere .=  " `pp_acc_id`=".$aArr[1];
				break;
			case 'ppid':
				$sSql .= "`isactive` = ".$aArr[2].", `utime`='".$sCurrTimex."'";
				$sWhere .=  " `ppid`=".$aArr[1];
				break;
			case 'del_pp_acc_id':
				$sSql = "DELETE FROM `".$this->TableName."`";
				$sWhere .=  " `pp_acc_id`=".$aArr[1];	 //分账户类调用，删除分账户涉及的所有关系
				break;
			case 'del_ppid':
				$sSql = "DELETE FROM `".$this->TableName."`";
				$sWhere .=  " `ppid`=".$aArr[1];		//支付接口类调用，删除支付接口涉及的所有关系
				break;
			case 'save_pp_acc_id':
				$sSql .= "`isactive`=9, `utime`='".$sCurrTimex."'";
			case 'save_ppid':
				$sSql .= "`isactive`=9, `utime`='".$sCurrTimex."'";
				$sWhere .=  " `ppid`=".$aArr[1]." AND `isactive`=1";	//支付接口类调用，保留相关的已激活关系
				break;
			case 're_pp_acc_id':
				$sSql .= "`isactive`=1, `utime`='".$sCurrTimex."'";
				$sWhere .=  " `pp_acc_id`=".$aArr[1]." AND `isactive`=9";	//分账户类调用 重新自动激活
				break;
			case 're_ppid':
				$sSql .= "`isactive`=1, `utime`='".$sCurrTimex."'";
				$sWhere .=  " `ppid`=".$aArr[1]." AND `isactive`=9";	//支付接口类调用  重新自动激活
				break;
			default:
				return false;
		}
		
		$sSql = $sSql." WHERE ".$sWhere;
		if ($sType !== NULL){
			return $sSql;
		}else{
			$this->oDB->query($sSql);
			if( $this->oDB->error() ){
            	return false;
        	}else{
        		return true;
        	}
		}
		
	}
	
	
	/**
	 * 删除某一用户的限制关联数据 
	 * @param int $iUserId
	 */ 
//	public function earseByUser(){
//		return $this->_earse('userid');
//	}
	
	
	/**
	 * 删除某一接口的关联数据 
	 * @param int $iAccId
	 */ 
//	public function earseBySlot(){
//		return $this->_earse('accountid');
//	}
	

	/**
	 * 删除关联数据 
	 * @param string $sType  userid/accountid
	 */ 
	/*private function _earse($sType){
		if (!$sType) return false;
		
		$sSql = "DELETE FROM ".$this->TableName;
		if ( $sType == 'uid' ){
			$sSql .= " WHERE `user_id`='".$this->UserId."'";
		}else{
			$sSql .= " WHERE `pp_id`='".$this->PaySlotId."'";
		}
		
		$this->oDB->query($sSql);
		if( $this->oDB->ar() && $this->oDB->errno() <= 0 )
		{
			return true;
		}else{
			return false;
		}
	}*/
	
	/*  class end  */
}
?>